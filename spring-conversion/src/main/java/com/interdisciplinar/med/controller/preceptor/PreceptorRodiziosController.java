package com.interdisciplinar.med.controller.preceptor;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;

import jakarta.servlet.http.HttpSession;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.TreeSet;

/**
 * Controlador para gerenciamento de rodízios pelos preceptores
 */
@Controller
@RequestMapping({"/pages/preceptor", "/spring/pages/preceptor"})
public class PreceptorRodiziosController {

    @Autowired
    private DataSource dataSource;

    // ### MÉTODO AUXILIAR PARA DEFINIR PRIORIDADE DO GRUPO (A -> 1, B -> 2, C -> 3, outros -> 4)
    private int obterPrioridadeGrupo(Map<String, Object> rodizio) {
        @SuppressWarnings("unchecked")
        List<String> subgrupos = (List<String>) rodizio.get("subgrupos");
        if (subgrupos != null) {
            for (String sg : subgrupos) {
                if (sg.startsWith("A")) return 1;
                if (sg.startsWith("B")) return 2;
                if (sg.startsWith("C")) return 3;
            }
        }
        return 4; // Outros grupos
    }

    @GetMapping({"/rodizios", "/rodizios.php"})
    public String rodizios(HttpSession session, Model model) {
        // A verificação de login e tipo de usuário é feita pelo UserDataInterceptor
        
        // Obter o ID do preceptor da sessão
        Long idpreceptor = (Long) session.getAttribute("idusuario");
        if (idpreceptor == null) {
            return "redirect:/";
        }
        
        System.out.println("ID do preceptor: " + idpreceptor);
        
        // Buscar os módulos do preceptor com informações de período de atendimento
        List<Map<String, Object>> rodizios = new ArrayList<>();
        
        try (Connection conn = dataSource.getConnection()) {
            // SQL para buscar os módulos do preceptor com rodízios e subgrupos
            String sql = "SELECT " +
                         "  m.idmodulo, " +
                         "  m.nome_modulo, " +
                         "  m.periodo, " +
                         "  r.inicio AS rodizio_inicio, " +
                         "  r.fim AS rodizio_fim, " +
                         "  GROUP_CONCAT(DISTINCT sg.nome_subgrupo ORDER BY sg.nome_subgrupo SEPARATOR ', ') AS subgrupos " +
                         "FROM preceptores_modulos pm " +
                         "JOIN modulos m ON pm.idmodulo = m.idmodulo " +
                         "JOIN rodizios r ON r.idmodulo = m.idmodulo " +
                         "JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio " +
                         "JOIN subgrupos sg ON sg.idsubgrupo = rs.idsubgrupo " +
                         "WHERE pm.idusuario = ? " +
                         "  AND (pm.data_inicio IS NULL OR pm.data_inicio <= r.fim) " +
                         "  AND (pm.data_fim IS NULL OR pm.data_fim >= r.inicio) " +
                         "GROUP BY m.idmodulo, m.nome_modulo, m.periodo, r.inicio, r.fim " +
                         "ORDER BY CAST(m.periodo AS UNSIGNED), m.nome_modulo, r.inicio";
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idpreceptor);
                System.out.println("Executando consulta com ID do preceptor: " + idpreceptor);
                
                // Imprimir a consulta SQL para debug
                System.out.println("Consulta SQL: " + sql.replace("?", idpreceptor.toString()));
                
                ResultSet rs = stmt.executeQuery();
                
                // Verificar se a consulta retornou resultados
                boolean hasResults = rs.isBeforeFirst(); // Verifica se há resultados sem mover o cursor
                if (!hasResults) {
                    System.out.println("A consulta não retornou nenhum resultado!");
                    System.out.println("Verifique se o ID do preceptor " + idpreceptor + " está associado a algum módulo na tabela preceptores_modulos.");
                } else {
                    System.out.println("A consulta retornou resultados!");
                }
                
                DateTimeFormatter dateFormatter = DateTimeFormatter.ofPattern("dd/MM/yyyy");
                
                while (rs.next()) {
                    Map<String, Object> rodizio = new HashMap<>();
                    rodizio.put("idmodulo", rs.getLong("idmodulo"));
                    rodizio.put("nomeModulo", rs.getString("nome_modulo"));
                    rodizio.put("periodo", rs.getString("periodo"));
                    
                    // Formatar datas de início e fim
                    java.sql.Date dataInicio = rs.getDate("rodizio_inicio");
                    java.sql.Date dataFim = rs.getDate("rodizio_fim");
                    
                    if (dataInicio != null) {
                        rodizio.put("dataInicio", dataInicio.toLocalDate().format(dateFormatter));
                    } else {
                        rodizio.put("dataInicio", "Não definida");
                    }
                    
                    if (dataFim != null) {
                        rodizio.put("dataFim", dataFim.toLocalDate().format(dateFormatter));
                    } else {
                        rodizio.put("dataFim", "Não definida");
                    }
                    
                    // Lista de subgrupos como string
                    String subgruposStr = rs.getString("subgrupos");
                    rodizio.put("subgruposTexto", subgruposStr != null ? subgruposStr : "Nenhum subgrupo");
                    
                    // Processar a string de subgrupos para uma lista
                    List<String> subgruposList = new ArrayList<>();
                    
                    if (subgruposStr != null && !subgruposStr.isEmpty()) {
                        String[] subgruposArray = subgruposStr.split(", ");
                        for (String subgrupo : subgruposArray) {
                            subgruposList.add(subgrupo);
                        }
                    }
                    
                    rodizio.put("subgrupos", subgruposList);
                    
                    rodizios.add(rodizio);
                    System.out.println("Rodizio adicionado: Módulo=" + rodizio.get("nomeModulo") + ", Período=" + rodizio.get("periodo"));
                }
            }
            
            System.out.println("Total de rodízios encontrados: " + rodizios.size());
            
            // Extrair todos os períodos disponíveis para o filtro
            Set<String> periodos = new TreeSet<>((p1, p2) -> {
                try {
                    int periodo1 = Integer.parseInt(p1);
                    int periodo2 = Integer.parseInt(p2);
                    return Integer.compare(periodo1, periodo2);
                } catch (NumberFormatException e) {
                    return p1.compareTo(p2);
                }
            });
            
            for (Map<String, Object> rodizio : rodizios) {
                periodos.add((String) rodizio.get("periodo"));
            }
            
            // Agrupar os rodízios por nome do módulo para exibição horizontal
            Map<String, List<Map<String, Object>>> rodiziosPorModulo = new LinkedHashMap<>();
            
            // Ordenar os módulos por período numérico
            List<Map<String, Object>> rodiziosOrdenados = new ArrayList<>(rodizios);
            Collections.sort(rodiziosOrdenados, (r1, r2) -> {
                String periodo1 = (String) r1.get("periodo");
                String periodo2 = (String) r2.get("periodo");
                try {
                    int p1 = Integer.parseInt(periodo1);
                    int p2 = Integer.parseInt(periodo2);
                    if (p1 != p2) {
                        return Integer.compare(p1, p2);
                    }
                } catch (NumberFormatException e) {
                    // Se não for possível converter para número, compara como string
                    int periodoComp = periodo1.compareTo(periodo2);
                    if (periodoComp != 0) {
                        return periodoComp;
                    }
                }
                
                // Se os períodos forem iguais, ordena por nome do módulo
                String nome1 = (String) r1.get("nomeModulo");
                String nome2 = (String) r2.get("nomeModulo");
                return nome1.compareTo(nome2);
            });
            
            // Agrupar por módulo mantendo a ordem
            for (Map<String, Object> rodizio : rodiziosOrdenados) {
                String nomeModulo = (String) rodizio.get("nomeModulo");
                
                if (!rodiziosPorModulo.containsKey(nomeModulo)) {
                    rodiziosPorModulo.put(nomeModulo, new ArrayList<>());
                }
                
                rodiziosPorModulo.get(nomeModulo).add(rodizio);
            }
            
            // Após preencher rodiziosPorModulo, ordenar cada lista para garantir A, depois B, depois C
            for (List<Map<String, Object>> lista : rodiziosPorModulo.values()) {
                Collections.sort(lista, (r1, r2) -> Integer.compare(obterPrioridadeGrupo(r1), obterPrioridadeGrupo(r2)));
            }
            
            model.addAttribute("periodos", periodos);
            model.addAttribute("rodiziosPorModulo", rodiziosPorModulo);
            model.addAttribute("rodizios", rodizios);
            
        } catch (SQLException e) {
            e.printStackTrace();
        }
        
        return "preceptor/rodizios";
    }
}
