package com.interdisciplinar.med.controller.preceptor;

import jakarta.servlet.http.HttpSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.*;
import javax.sql.DataSource;

/**
 * Controlador para gerenciamento de alunos por parte dos preceptores
 */
@Controller
@RequestMapping({"/pages/preceptor", "/spring/pages/preceptor"})
public class PreceptorAlunosController {

    @Autowired
    private DataSource dataSource;
    
    @GetMapping({"/listar-aluno", "/listar-aluno.php"})
    public String listarAlunos(HttpSession session, Model model) {
        Long idpreceptor = (Long) session.getAttribute("idusuario");
        if (idpreceptor == null) {
            return "redirect:/";
        }
        
        List<Map<String, Object>> alunos = new ArrayList<>();
        Set<String> modulosFiltro = new TreeSet<>();
        Set<String> subgruposFiltro = new TreeSet<>();
        
        try (Connection conn = dataSource.getConnection()) {
            
            String sql = "SELECT u.nome, u.idusuario, u.registro, sg.nome_subgrupo, m.nome_modulo, m.idmodulo " +
                          "FROM usuarios u " +
                          "JOIN alunos_subgrupos alsg ON u.idusuario = alsg.idusuario " +
                          "JOIN subgrupos sg ON alsg.idsubgrupo = sg.idsubgrupo " +
                          "JOIN horarios h ON sg.idsubgrupo = h.idsubgrupo " +
                          "JOIN modulos m ON h.idmodulo = m.idmodulo " +
                          "WHERE h.idpreceptor = ? AND u.tipo = 0 " +
                          "ORDER BY u.nome";
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idpreceptor);
                ResultSet rs = stmt.executeQuery();
                
                Map<Long, Map<String, Object>> alunosMap = new HashMap<>();
                
                while (rs.next()) {
                    Long idUsuario = rs.getLong("idusuario");
                    Long idModulo = rs.getLong("idmodulo");
                    
                    Map<String, Object> aluno;
                    if (alunosMap.containsKey(idUsuario)) {
                        aluno = alunosMap.get(idUsuario);
                    } else {
                        aluno = new java.util.HashMap<>();
                        aluno.put("nome", rs.getString("nome"));
                        aluno.put("registro", rs.getString("registro"));
                        aluno.put("idusuario", idUsuario);
                        aluno.put("nomeSubgrupo", rs.getString("nome_subgrupo"));
                        // lista para armazenar representações módulo(nota)
                        aluno.put("modulosLista", new java.util.ArrayList<String>());
                        alunosMap.put(idUsuario, aluno);
                    }
                    
                    // Atualizar subgrupos caso novo
                    String subgruposAtuais = (String) aluno.get("nomeSubgrupo");
                    String novoSubgrupo = rs.getString("nome_subgrupo");
                    if (!subgruposAtuais.contains(novoSubgrupo)) {
                        aluno.put("nomeSubgrupo", subgruposAtuais + ", " + novoSubgrupo);
                    }
                    subgruposFiltro.add(novoSubgrupo);
                    
                    // Buscar nota mais recente para esse módulo
                    String notaStr = null;
                    try {
                        String sqlNota = "SELECT nota FROM avaliacoes WHERE idaluno = ? AND idpreceptor = ? AND idmodulo = ? ORDER BY data_avaliacao DESC LIMIT 1";
                        try (PreparedStatement stmtNota = conn.prepareStatement(sqlNota)) {
                            stmtNota.setLong(1, idUsuario);
                            stmtNota.setLong(2, idpreceptor);
                            stmtNota.setLong(3, idModulo);
                            ResultSet rsNota = stmtNota.executeQuery();
                            if (rsNota.next()) {
                                notaStr = rsNota.getString("nota");
                            }
                        }
                    } catch (SQLException ignore) {}
                    
                    String moduloNome = rs.getString("nome_modulo");
                    modulosFiltro.add(moduloNome);
                    String representacao = notaStr != null ? moduloNome + " (" + notaStr + ")" : moduloNome + " (Sem nota)";
                    @SuppressWarnings("unchecked")
                    List<String> listaModulos = (List<String>) aluno.get("modulosLista");
                    if (!listaModulos.contains(representacao)) {
                        listaModulos.add(representacao);
                    }
                }
                
                // Verificar módulos provenientes de avaliações para complementar lista
                for (Map<String,Object> alunoObj : alunosMap.values()) {
                    Long alunoId = (Long) alunoObj.get("idusuario");
                    // Buscar módulos avaliados
                    String sqlEval = "SELECT m.nome_modulo, m.idmodulo, (SELECT nota FROM avaliacoes a2 WHERE a2.idaluno = ? AND a2.idpreceptor = ? AND a2.idmodulo = m.idmodulo ORDER BY data_avaliacao DESC LIMIT 1) AS notaUltima " +
                                    "FROM modulos m JOIN avaliacoes a ON a.idmodulo = m.idmodulo " +
                                    "WHERE a.idaluno = ? AND a.idpreceptor = ? GROUP BY m.idmodulo, m.nome_modulo";
                    try (PreparedStatement stmtEval = conn.prepareStatement(sqlEval)) {
                        stmtEval.setLong(1, alunoId);
                        stmtEval.setLong(2, idpreceptor);
                        stmtEval.setLong(3, alunoId);
                        stmtEval.setLong(4, idpreceptor);
                        ResultSet rsE = stmtEval.executeQuery();
                        @SuppressWarnings("unchecked") List<String> listaMod = (List<String>) alunoObj.get("modulosLista");
                        if (listaMod == null) {
                            listaMod = new ArrayList<>();
                            alunoObj.put("modulosLista", listaMod);
                        }
                        while (rsE.next()) {
                            String mn = rsE.getString("nome_modulo");
                            String notaU = rsE.getString("notaUltima");
                            String repr = notaU != null ? mn + " (" + notaU + ")" : mn + " (Sem nota)";
                            if (!listaMod.contains(repr)) {
                                listaMod.add(repr);
                                modulosFiltro.add(mn);
                            }
                        }
                    }
                    
                    // ---- Complementar com módulos em horários que possam não ter nota ainda ----
                    String sqlHorMods = "SELECT DISTINCT m.nome_modulo, m.idmodulo FROM horarios h " +
                                         "JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo " +
                                         "JOIN alunos_subgrupos alsg ON alsg.idsubgrupo = sg.idsubgrupo " +
                                         "JOIN modulos m ON m.idmodulo = h.idmodulo " +
                                         "WHERE alsg.idusuario = ? AND h.idpreceptor = ?";
                    try (PreparedStatement stmtHor = conn.prepareStatement(sqlHorMods)) {
                        stmtHor.setLong(1, alunoId);
                        stmtHor.setLong(2, idpreceptor);
                        ResultSet rsHor = stmtHor.executeQuery();
                        @SuppressWarnings("unchecked") List<String> listaMod = (List<String>) alunoObj.get("modulosLista");
                        while (rsHor.next()) {
                            String mn = rsHor.getString("nome_modulo");
                            String repr = mn + " (Sem nota)";
                            if (!listaMod.contains(repr) && listaMod.stream().noneMatch(s -> s.startsWith(mn + " ("))) {
                                // Só adiciona se ainda não existe entrada para esse módulo
                                listaMod.add(repr);
                                modulosFiltro.add(mn);
                            }
                        }
                    }
                }

                for (Map<String,Object> alunoObj : alunosMap.values()) {
                    @SuppressWarnings("unchecked") List<String> listaMod = (List<String>) alunoObj.remove("modulosLista");
                    alunoObj.put("modulosTexto", String.join(", ", listaMod));
                    alunoObj.put("modulosHtml", String.join("<br/>", listaMod));
                    alunos.add(alunoObj);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
            model.addAttribute("erro", "Erro ao carregar a lista de alunos: " + e.getMessage());
        }
        
        model.addAttribute("alunos", alunos);
        model.addAttribute("modulosFiltro", modulosFiltro);
        model.addAttribute("subgruposFiltro", subgruposFiltro);
        
        return "preceptor/listar-aluno";
    }
}
