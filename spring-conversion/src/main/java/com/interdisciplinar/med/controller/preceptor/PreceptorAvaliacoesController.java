package com.interdisciplinar.med.controller.preceptor;

import com.interdisciplinar.med.PadrõesDeProjeto.Estruturais.Facade.PreceptorFacade;
import jakarta.servlet.http.HttpSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Controlador para gerenciamento de avaliações dos alunos pelos preceptores
 */
@Controller
@RequestMapping({"/pages/preceptor", "/spring/pages/preceptor"})
public class PreceptorAvaliacoesController {

    private static final Logger logger = Logger.getLogger(PreceptorAvaliacoesController.class.getName());

    @Autowired
    private DataSource dataSource;
    
    @Autowired
    private PreceptorFacade preceptorFacade;

    /**
     * Página principal de avaliações
     */
    @GetMapping({"/avaliacoes", "/avaliacoes.php", "/avaliacoes/avaliacoes", "/avaliacoes/avaliacoes.php"})
    public String avaliacoes(HttpSession session, Model model) {
        Long idpreceptor = (Long) session.getAttribute("idusuario");
        if (idpreceptor == null) {
            return "redirect:/";
        }
        
        try {
            // Tentar usar o padrão Facade para obter dados do preceptor
            Map<String, Object> preceptorData = preceptorFacade.obterDadosPreceptor(idpreceptor);
            if (preceptorData != null && !preceptorData.isEmpty()) {
                model.addAttribute("preceptor", preceptorData);
            }
        } catch (Exception e) {
            logger.log(Level.WARNING, "Erro ao usar PreceptorFacade para obter dados do preceptor", e);
            // Continuar com o código existente se a fachada falhar
        }
        
        // Carregar a lista de alunos associados ao preceptor logado
        try (Connection conn = dataSource.getConnection()) {
            String sql = "SELECT u.idusuario, u.nome, u.registro, sg.nome_subgrupo, m.nome_modulo " +
                         "FROM preceptores_modulos pm " +
                         "JOIN horarios h ON h.idmodulo = pm.idmodulo AND h.idpreceptor = pm.idusuario " +
                         "JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo " +
                         "JOIN alunos_subgrupos alsg ON alsg.idsubgrupo = sg.idsubgrupo " +
                         "JOIN usuarios u ON u.idusuario = alsg.idusuario " +
                         "JOIN modulos m ON m.idmodulo = pm.idmodulo " +
                         "WHERE pm.idusuario = ? AND u.tipo = 0 " +
                         "ORDER BY u.nome";
            
            List<Map<String, Object>> alunos = new ArrayList<>();
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idpreceptor);
                ResultSet rs = stmt.executeQuery();
                
                Map<Long, Map<String, Object>> alunosMap = new HashMap<>();
                
                while (rs.next()) {
                    Long idAluno = rs.getLong("idusuario");
                    
                    // Se o aluno já foi adicionado, apenas atualiza as informações
                    if (alunosMap.containsKey(idAluno)) {
                        Map<String, Object> aluno = alunosMap.get(idAluno);
                        
                        // Adiciona o subgrupo se for diferente
                        String nomeSubgrupo = rs.getString("nome_subgrupo");
                        String subgruposAtuais = (String) aluno.get("nomeSubgrupo");
                        if (!subgruposAtuais.contains(nomeSubgrupo)) {
                            aluno.put("nomeSubgrupo", subgruposAtuais + ", " + nomeSubgrupo);
                        }
                        
                        // Adiciona o módulo se for diferente
                        String nomeModulo = rs.getString("nome_modulo");
                        String modulosAtuais = (String) aluno.get("nomeModulo");
                        if (!modulosAtuais.contains(nomeModulo)) {
                            aluno.put("nomeModulo", modulosAtuais + ", " + nomeModulo);
                        }
                    } else {
                        // Cria um novo registro para o aluno
                        Map<String, Object> aluno = new HashMap<>();
                        aluno.put("idusuario", idAluno);
                        aluno.put("nome", rs.getString("nome"));
                        aluno.put("registro", rs.getString("registro"));
                        aluno.put("nomeSubgrupo", rs.getString("nome_subgrupo"));
                        aluno.put("nomeModulo", rs.getString("nome_modulo"));
                        
                        // Verificar se já existe avaliação para este aluno
                        String sqlAvaliacao = "SELECT COUNT(*) AS total FROM avaliacoes WHERE idaluno = ? AND idpreceptor = ?";
                        try (PreparedStatement stmtAvaliacao = conn.prepareStatement(sqlAvaliacao)) {
                            stmtAvaliacao.setLong(1, idAluno);
                            stmtAvaliacao.setLong(2, idpreceptor);
                            ResultSet rsAvaliacao = stmtAvaliacao.executeQuery();
                            
                            if (rsAvaliacao.next()) {
                                aluno.put("avaliacaoRealizada", rsAvaliacao.getInt("total") > 0);
                            } else {
                                aluno.put("avaliacaoRealizada", false);
                            }
                        }
                        
                        alunosMap.put(idAluno, aluno);
                    }
                }
                
                alunos.addAll(alunosMap.values());
            }
            
            model.addAttribute("alunos", alunos);
            model.addAttribute("page", "lista");
            
            return "preceptor/avaliacoes/avaliacoes";
            
        } catch (SQLException e) {
            e.printStackTrace();
            model.addAttribute("alunos", new ArrayList<>());
            model.addAttribute("erro", "Erro ao carregar os alunos: " + e.getMessage());
            return "preceptor/avaliacoes/avaliacoes";
        }
    }
    
    /**
     * Formulário para realizar uma avaliação
     */
    @GetMapping({"avaliacoes/realizar-avaliacao", "/avaliacoes/realizar-avaliacao.php"})
    public String realizarAvaliacao(
            @RequestParam("idaluno") Long idaluno, 
            @RequestParam(value = "idsubgrupo", required = false) Long idsubgrupo,
            HttpSession session, 
            Model model) {
        Long idpreceptor = (Long) session.getAttribute("idusuario");
        if (idpreceptor == null) {
            return "redirect:/";
        }
        
        try (Connection conn = dataSource.getConnection()) {
            String sqlAluno = "SELECT * FROM usuarios WHERE idusuario = ?";
            Map<String, Object> aluno = new HashMap<>();
            
            try (PreparedStatement stmt = conn.prepareStatement(sqlAluno)) {
                stmt.setLong(1, idaluno);
                ResultSet rs = stmt.executeQuery();
                
                if (rs.next()) {
                    aluno.put("idusuario", rs.getLong("idusuario"));
                    aluno.put("nome", rs.getString("nome"));
                    aluno.put("registro", rs.getString("registro"));
                } else {
                    model.addAttribute("erro", "Aluno não encontrado");
                    return "redirect:/pages/preceptor/avaliacoes";
                }
            }
            
            String sqlModulos = "SELECT m.idmodulo, m.nome_modulo " +
                              "FROM modulos m " +
                              "INNER JOIN modulos_alunos ma ON m.idmodulo = ma.idmodulo " +
                              "INNER JOIN preceptores_modulos pm ON m.idmodulo = pm.idmodulo " +
                              "WHERE ma.idusuario = ? " +
                              "AND pm.idusuario = ? "; 
            
            List<Map<String, Object>> modulos = new ArrayList<>();
            
            try (PreparedStatement stmt = conn.prepareStatement(sqlModulos)) {
                stmt.setLong(1, idaluno);
                stmt.setLong(2, idpreceptor);
                ResultSet rs = stmt.executeQuery();
                
                while (rs.next()) {
                    Map<String, Object> modulo = new HashMap<>();
                    modulo.put("idmodulo", rs.getLong("idmodulo"));
                    modulo.put("nomeModulo", rs.getString("nome_modulo"));
                    modulos.add(modulo);
                }
            }
            
            // Se não encontrou módulos comuns, buscar todos os módulos do aluno para informar
            if (modulos.isEmpty()) {
                String sqlTodosModulos = "SELECT m.idmodulo, m.nome_modulo FROM modulos m " +
                                      "JOIN modulos_alunos ma ON m.idmodulo = ma.idmodulo " +
                                      "WHERE ma.idusuario = ?";
                
                List<Map<String, Object>> todosModulosAluno = new ArrayList<>();
                
                try (PreparedStatement stmt = conn.prepareStatement(sqlTodosModulos)) {
                    stmt.setLong(1, idaluno);
                    ResultSet rs = stmt.executeQuery();
                    
                    while (rs.next()) {
                        Map<String, Object> modulo = new HashMap<>();
                        modulo.put("idmodulo", rs.getLong("idmodulo"));
                        modulo.put("nomeModulo", rs.getString("nome_modulo"));
                        todosModulosAluno.add(modulo);
                    }
                }
                
                model.addAttribute("todosModulosAluno", todosModulosAluno);
                model.addAttribute("erro", "O aluno e o preceptor não possuem módulos em comum.");
            }
            
            String sqlPerguntas = "SELECT * FROM perguntas_avaliacoes";
            List<Map<String, Object>> perguntas = new ArrayList<>();
            
            try (PreparedStatement stmt = conn.prepareStatement(sqlPerguntas);
                 ResultSet rs = stmt.executeQuery()) {
                
                while (rs.next()) {
                    Map<String, Object> pergunta = new HashMap<>();
                    pergunta.put("idpergunta", rs.getLong("idpergunta"));
                    pergunta.put("titulo", rs.getString("titulo"));
                    pergunta.put("descricao", rs.getString("descricao"));
                    perguntas.add(pergunta);
                }
            }
            
          
            
            model.addAttribute("aluno", aluno);
            model.addAttribute("modulos", modulos);
            model.addAttribute("perguntas", perguntas);
            model.addAttribute("idaluno", idaluno);
            model.addAttribute("page", "realizar-avaliacao");
            
            if (idsubgrupo != null) {
                model.addAttribute("idsubgrupo", idsubgrupo);
            }
            
            return "preceptor/avaliacoes/avaliacoes";
            
        } catch (SQLException e) {
            e.printStackTrace();
            model.addAttribute("erro", "Erro ao carregar os dados para avaliação: " + e.getMessage());
            return "redirect:/pages/preceptor/avaliacoes";
        }
    }
    
    /**
     * Processar o envio de uma avaliação
     */
    @PostMapping({"avaliacoes/processar-avaliacao", "/avaliacoes/processar-avaliacao.php"})
    public String processarAvaliacao(
            @RequestParam("idaluno") Long idaluno,
            @RequestParam("modulo") Long idmodulo,
            @RequestParam(value = "idsubgrupo", required = false) Long idsubgrupo,
            @RequestParam Map<String, String> allParams,
            HttpSession session,
            RedirectAttributes redirectAttributes) {
        
        Long idpreceptor = (Long) session.getAttribute("idusuario");
        if (idpreceptor == null) {
            return "redirect:/";
        }
        
        try (Connection conn = dataSource.getConnection()) {
            double notaTotal = 0;
            int numPerguntas = 0;
            
            for (String key : allParams.keySet()) {
                if (key.startsWith("pergunta_")) {
                    try {
                        double valor = Double.parseDouble(allParams.get(key));
                        notaTotal += valor;
                        numPerguntas++;
                    } catch (NumberFormatException e) {
                        // Ignorar valores não numéricos
                    }
                }
            }
            
            double notaMedia = numPerguntas > 0 ? notaTotal / numPerguntas : 0;
            
            String sql = "INSERT INTO avaliacoes (idaluno, idpreceptor, idmodulo, nota, data_avaliacao) VALUES (?, ?, ?, ?, NOW())";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idaluno);
                stmt.setLong(2, idpreceptor);
                stmt.setLong(3, idmodulo);
                stmt.setDouble(4, notaMedia);
                
                int result = stmt.executeUpdate();
                
                if (result > 0) {
                    redirectAttributes.addFlashAttribute("mensagem", "Avaliação realizada com sucesso!");
                } else {
                    redirectAttributes.addFlashAttribute("erro", "Erro ao salvar a avaliação.");
                }
            }
            
        } catch (SQLException e) {
            e.printStackTrace();
            redirectAttributes.addFlashAttribute("erro", "Erro ao processar a avaliação: " + e.getMessage());
        }
        
        // Se tiver um idsubgrupo, redirecionar de volta para a página do subgrupo
        if (idsubgrupo != null) {
            return "redirect:/pages/preceptor/grupos/alunos?idsubgrupo=" + idsubgrupo;
        } else {
            return "redirect:/pages/preceptor/avaliacoes";
        }
    }
    
    /**
     * Visualizar detalhes de uma avaliação
     */
    @GetMapping({"/avaliacoes/visualizar-avaliacao", "/avaliacoes/visualizar-avaliacao.php"})
    public String visualizarAvaliacao(
            @RequestParam("idavaliacao") Long idavaliacao, 
            @RequestParam(value = "idsubgrupo", required = false) Long idsubgrupo,
            Model model) {
        // Método para visualizar detalhes de uma avaliação específica
        try (Connection conn = dataSource.getConnection()) {
            String checkSql = "SELECT COUNT(*) FROM avaliacoes WHERE idavaliacao = ?";
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setLong(1, idavaliacao);
                ResultSet checkRs = checkStmt.executeQuery();
                if (checkRs.next() && checkRs.getInt(1) == 0) {
                    model.addAttribute("erro", "Avaliação não encontrada.");
                    return "redirect:/pages/preceptor/avaliacoes";
                }
            }
            
            String sql = "SELECT a.*, u.nome as nome_aluno, m.nome_modulo " +
                       "FROM avaliacoes a " +
                       "JOIN usuarios u ON a.idaluno = u.idusuario " +
                       "JOIN modulos m ON a.idmodulo = m.idmodulo " +
                       "WHERE a.idavaliacao = ?";
            
            Map<String, Object> avaliacao = new HashMap<>();
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idavaliacao);
                ResultSet rs = stmt.executeQuery();
                
                if (rs.next()) {
                    avaliacao.put("idavaliacao", rs.getLong("idavaliacao"));
                    avaliacao.put("idaluno", rs.getLong("idaluno"));
                    avaliacao.put("idpreceptor", rs.getLong("idpreceptor"));
                    avaliacao.put("nomeAluno", rs.getString("nome_aluno"));
                    avaliacao.put("nomeModulo", rs.getString("nome_modulo"));
                    avaliacao.put("nota", rs.getDouble("nota"));
                    avaliacao.put("dataAvaliacao", rs.getDate("data_avaliacao").toLocalDate());
                } else {
                    model.addAttribute("erro", "Avaliação não encontrada.");
                    return "redirect:/pages/preceptor/avaliacoes";
                }
            }
            
            model.addAttribute("respostas", new ArrayList<>());
            model.addAttribute("avaliacao", avaliacao);
            model.addAttribute("page", "visualizar-avaliacao");
            
            if (idsubgrupo != null) {
                model.addAttribute("idsubgrupo", idsubgrupo);
            }
            
            return "preceptor/avaliacoes/avaliacoes";
            
        } catch (SQLException e) {
            e.printStackTrace();
            model.addAttribute("erro", "Erro ao carregar os detalhes da avaliação: " + e.getMessage());
            return "redirect:/pages/preceptor/avaliacoes";
        }
    }
}
