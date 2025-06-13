package com.interdisciplinar.med.controller.preceptor;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;

import jakarta.servlet.http.HttpSession;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import com.interdisciplinar.med.PadrõesDeProjeto.Estruturais.Facade.PreceptorFacade;

/**
 * Controlador para gerenciamento de grupos pelos preceptores
 */
@Controller
@RequestMapping({"/pages/preceptor", "/spring/pages/preceptor"})
public class PreceptorGruposController {

    @Autowired
    private DataSource dataSource;

    @Autowired
    private PreceptorFacade preceptorFacade;

    @GetMapping({"/grupos", "/grupos.php"})
    public String grupos(HttpSession session, Model model) {
        Long idpreceptor = (Long) session.getAttribute("idusuario");
        if (idpreceptor == null) {
            return "redirect:/";
        }
        
        List<Map<String, Object>> subgruposPreceptor = new ArrayList<>();
        
        try (Connection conn = dataSource.getConnection()) {
            String sql = "SELECT DISTINCT sg.idsubgrupo, sg.nome_subgrupo, g.idgrupo, g.nome_grupo " +
                         "FROM subgrupos sg " +
                         "JOIN grupos g ON sg.idgrupo = g.idgrupo " +
                         "JOIN horarios h ON sg.idsubgrupo = h.idsubgrupo " +
                         "WHERE h.idpreceptor = ? " +
                         "ORDER BY g.nome_grupo, sg.nome_subgrupo";
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idpreceptor);
                ResultSet rs = stmt.executeQuery();
                
                boolean encontrouSubgrupos = false;
                while (rs.next()) {
                    encontrouSubgrupos = true;
                    Map<String, Object> subgrupo = new HashMap<>();
                    subgrupo.put("idsubgrupo", rs.getLong("idsubgrupo"));
                    subgrupo.put("nomeSubgrupo", rs.getString("nome_subgrupo"));
                    subgrupo.put("idgrupo", rs.getLong("idgrupo"));
                    subgrupo.put("nomeGrupo", rs.getString("nome_grupo"));
                    
                    String sqlCount = "SELECT COUNT(*) AS total FROM alunos_subgrupos WHERE idsubgrupo = ?";
                    try (PreparedStatement stmtCount = conn.prepareStatement(sqlCount)) {
                        stmtCount.setLong(1, rs.getLong("idsubgrupo"));
                        ResultSet rsCount = stmtCount.executeQuery();
                        if (rsCount.next()) {
                            subgrupo.put("totalAlunos", rsCount.getInt("total"));
                        } else {
                            subgrupo.put("totalAlunos", 0);
                        }
                    }
                    
                    subgruposPreceptor.add(subgrupo);
                }
                
                if (!encontrouSubgrupos) {
                    String sqlAlternativo = "SELECT DISTINCT sg.idsubgrupo, sg.nome_subgrupo, g.idgrupo, g.nome_grupo " +
                                           "FROM subgrupos sg " +
                                           "JOIN grupos g ON sg.idgrupo = g.idgrupo " +
                                           "WHERE EXISTS (SELECT 1 FROM horarios h WHERE h.idpreceptor = ? AND h.idmodulo IN " +
                                           "  (SELECT h2.idmodulo FROM horarios h2 WHERE h2.idsubgrupo = sg.idsubgrupo)) " +
                                           "ORDER BY g.nome_grupo, sg.nome_subgrupo";
                    
                    try (PreparedStatement stmtAlt = conn.prepareStatement(sqlAlternativo)) {
                        stmtAlt.setLong(1, idpreceptor);
                        ResultSet rsAlt = stmtAlt.executeQuery();
                        
                        while (rsAlt.next()) {
                            Map<String, Object> subgrupo = new HashMap<>();
                            subgrupo.put("idsubgrupo", rsAlt.getLong("idsubgrupo"));
                            subgrupo.put("nomeSubgrupo", rsAlt.getString("nome_subgrupo"));
                            subgrupo.put("idgrupo", rsAlt.getLong("idgrupo"));
                            subgrupo.put("nomeGrupo", rsAlt.getString("nome_grupo"));
                            
                            String sqlCount = "SELECT COUNT(*) AS total FROM alunos_subgrupos WHERE idsubgrupo = ?";
                            try (PreparedStatement stmtCount = conn.prepareStatement(sqlCount)) {
                                stmtCount.setLong(1, rsAlt.getLong("idsubgrupo"));
                                ResultSet rsCount = stmtCount.executeQuery();
                                if (rsCount.next()) {
                                    subgrupo.put("totalAlunos", rsCount.getInt("total"));
                                } else {
                                    subgrupo.put("totalAlunos", 0);
                                }
                            }
                            
                            subgruposPreceptor.add(subgrupo);
                        }
                    }
                }
                
                if (subgruposPreceptor.isEmpty()) {
                    String sqlExemplo = "SELECT sg.idsubgrupo, sg.nome_subgrupo, g.idgrupo, g.nome_grupo " +
                                       "FROM subgrupos sg " +
                                       "JOIN grupos g ON sg.idgrupo = g.idgrupo " +
                                       "LIMIT 5";
                    
                    try (PreparedStatement stmtEx = conn.prepareStatement(sqlExemplo)) {
                        ResultSet rsEx = stmtEx.executeQuery();
                        
                        while (rsEx.next()) {
                            Map<String, Object> subgrupo = new HashMap<>();
                            subgrupo.put("idsubgrupo", rsEx.getLong("idsubgrupo"));
                            subgrupo.put("nomeSubgrupo", rsEx.getString("nome_subgrupo") + " (Exemplo)");
                            subgrupo.put("idgrupo", rsEx.getLong("idgrupo"));
                            subgrupo.put("nomeGrupo", rsEx.getString("nome_grupo") + " (Exemplo)");
                            subgrupo.put("totalAlunos", 5);
                            
                            subgruposPreceptor.add(subgrupo);
                        }
                    }
                }
            }
            
            model.addAttribute("subgruposPreceptor", subgruposPreceptor);
            
        } catch (SQLException e) {
            e.printStackTrace();
            model.addAttribute("erro", "Erro ao buscar subgrupos: " + e.getMessage());
        }
        
        return "preceptor/grupos";
    }
    
    @GetMapping({"/grupos/alunos", "/grupos/alunos.php"})
    public String alunosSubgrupo(@RequestParam("idsubgrupo") Long idsubgrupo, HttpSession session, Model model) {
        Long idpreceptor = (Long) session.getAttribute("idusuario");
        if (idpreceptor == null) {
            return "redirect:/";
        }
        
        boolean temAcesso = false;
        String nomeSubgrupo = "";
        
        try (Connection conn = dataSource.getConnection()) {
            String sqlVerifica = "SELECT COUNT(*) AS total, sg.nome_subgrupo FROM horarios h " +
                                "JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo " +
                                "WHERE h.idpreceptor = ? AND h.idsubgrupo = ? " +
                                "GROUP BY sg.nome_subgrupo";
            
            try (PreparedStatement stmt = conn.prepareStatement(sqlVerifica)) {
                stmt.setLong(1, idpreceptor);
                stmt.setLong(2, idsubgrupo);
                ResultSet rs = stmt.executeQuery();
                
                if (rs.next()) {
                    temAcesso = rs.getInt("total") > 0;
                    nomeSubgrupo = rs.getString("nome_subgrupo");
                }
            }
            
            if (!temAcesso) {
                model.addAttribute("erro", "Você não tem acesso a este subgrupo.");
                return "redirect:/pages/preceptor/grupos";
            }
            
            List<Map<String, Object>> alunos = new ArrayList<>();
            String sql = "SELECT u.idusuario, u.nome, u.registro " +
                        "FROM usuarios u " +
                        "JOIN alunos_subgrupos als ON u.idusuario = als.idusuario " +
                        "WHERE als.idsubgrupo = ? AND u.tipo = 0 " +
                        "ORDER BY u.nome";
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idsubgrupo);
                ResultSet rs = stmt.executeQuery();
                
                while (rs.next()) {
                    Map<String, Object> aluno = new HashMap<>();
                    Long idAluno = rs.getLong("idusuario");
                    aluno.put("idusuario", idAluno);
                    aluno.put("nome", rs.getString("nome"));
                    aluno.put("registro", rs.getString("registro"));
                    
                    // Recupera módulos compartilhados (preceptor & aluno)
                    List<Map<String, Object>> modulos = preceptorFacade.obterModulosAlunoPreceptor(idAluno, idpreceptor);
                    if (!modulos.isEmpty()) {
                        Map<String, Object> primeiro = modulos.get(0);
                        aluno.put("idModulo", primeiro.get("idmodulo"));
                        aluno.put("nomeModulo", primeiro.get("nomeModulo"));
                    }
                    
                    String sqlAvaliacao = "SELECT idavaliacao FROM avaliacoes " +
                                        "WHERE idaluno = ? AND idpreceptor = ? " +
                                        "ORDER BY data_avaliacao DESC LIMIT 1";
                    try (PreparedStatement stmtAvaliacao = conn.prepareStatement(sqlAvaliacao)) {
                        stmtAvaliacao.setLong(1, rs.getLong("idusuario"));
                        stmtAvaliacao.setLong(2, idpreceptor);
                        ResultSet rsAvaliacao = stmtAvaliacao.executeQuery();
                        
                        if (rsAvaliacao.next()) {
                            aluno.put("avaliacaoRealizada", true);
                            aluno.put("idavaliacao", rsAvaliacao.getLong("idavaliacao"));
                        } else {
                            aluno.put("avaliacaoRealizada", false);
                            aluno.put("idavaliacao", null);
                        }
                    }
                    
                    alunos.add(aluno);
                }
            }
            
            model.addAttribute("alunos", alunos);
            model.addAttribute("nomeSubgrupo", nomeSubgrupo);
            model.addAttribute("idsubgrupo", idsubgrupo);
            
        } catch (SQLException e) {
            e.printStackTrace();
            model.addAttribute("erro", "Erro ao buscar alunos do subgrupo: " + e.getMessage());
        }
        
        return "preceptor/grupos-alunos";
    }
}
