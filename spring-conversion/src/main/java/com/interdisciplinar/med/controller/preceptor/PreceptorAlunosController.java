package com.interdisciplinar.med.controller.preceptor;

import com.interdisciplinar.med.service.UsuarioService;
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
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import javax.sql.DataSource;

/**
 * Controlador para gerenciamento de alunos por parte dos preceptores
 */
@Controller
@RequestMapping({"/pages/preceptor", "/spring/pages/preceptor"})
public class PreceptorAlunosController {

    @Autowired
    private UsuarioService usuarioService;

    @Autowired
    private DataSource dataSource;
    
    @GetMapping({"/listar-aluno", "/listar-aluno.php"})
    public String listarAlunos(HttpSession session, Model model) {
        // A verificação de login e tipo de usuário é feita pelo UserDataInterceptor
        
        // Obter o ID do preceptor da sessão
        Long idpreceptor = (Long) session.getAttribute("idusuario");
        if (idpreceptor == null) {
            return "redirect:/";
        }
        
        // Buscar todos os alunos que o preceptor gerencia (associados aos seus subgrupos)
        List<Map<String, Object>> alunos = new ArrayList<>();
        
        try (Connection conn = dataSource.getConnection()) {
            // SQL para buscar alunos gerenciados pelo preceptor logado
            // Baseado na consulta fornecida como guia
            String sql = "SELECT u.nome, u.idusuario, u.registro, sg.nome_subgrupo, sg.idsubgrupo, m.nome_modulo, m.idmodulo " +
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
                
                // Criar um Map para armazenar os alunos únicos (para evitar duplicatas)
                Map<Long, Map<String, Object>> alunosMap = new HashMap<>();
                
                while (rs.next()) {
                    Long idUsuario = rs.getLong("idusuario");
                    Long idSubgrupo = rs.getLong("idsubgrupo");
                    Long idModulo = rs.getLong("idmodulo");
                    
                    // Se este aluno já foi adicionado, apenas atualizar informações se necessário
                    if (alunosMap.containsKey(idUsuario)) {
                        Map<String, Object> alunoExistente = alunosMap.get(idUsuario);
                        
                        // Adicionar subgrupo se for diferente
                        String subgruposAtuais = (String) alunoExistente.get("nomeSubgrupo");
                        String novoSubgrupo = rs.getString("nome_subgrupo");
                        if (!subgruposAtuais.contains(novoSubgrupo)) {
                            alunoExistente.put("nomeSubgrupo", subgruposAtuais + ", " + novoSubgrupo);
                        }
                        continue;
                    }
                    
                    Map<String, Object> aluno = new HashMap<>();
                    aluno.put("nome", rs.getString("nome"));
                    aluno.put("registro", rs.getString("registro"));
                    aluno.put("idusuario", idUsuario);
                    aluno.put("idsubgrupo", idSubgrupo);
                    aluno.put("idmodulo", idModulo);
                    aluno.put("nomeModulo", rs.getString("nome_modulo"));
                    aluno.put("nomeSubgrupo", rs.getString("nome_subgrupo"));
                    
                    // Buscar nota do aluno (se existir)
                    try {
                        String sqlNota = "SELECT nota, data_avaliacao FROM avaliacoes " +
                                         "WHERE idaluno = ? AND idpreceptor = ? " +
                                         "ORDER BY data_avaliacao DESC LIMIT 1";
                        try (PreparedStatement stmtNota = conn.prepareStatement(sqlNota)) {
                            stmtNota.setLong(1, idUsuario);
                            stmtNota.setLong(2, idpreceptor);
                            ResultSet rsNota = stmtNota.executeQuery();
                            
                            if (rsNota.next()) {
                                aluno.put("nota", rsNota.getString("nota"));
                                aluno.put("dataAvaliacao", rsNota.getDate("data_avaliacao"));
                            } else {
                                aluno.put("nota", null);
                                aluno.put("dataAvaliacao", null);
                            }
                        }
                    } catch (SQLException e) {
                        aluno.put("nota", null);
                        aluno.put("dataAvaliacao", null);
                    }
                    
                    alunosMap.put(idUsuario, aluno);
                }
                
                // Converter o Map para uma List
                alunos.addAll(alunosMap.values());
            }
        } catch (SQLException e) {
            e.printStackTrace();
            model.addAttribute("erro", "Erro ao carregar a lista de alunos: " + e.getMessage());
        }
        
        model.addAttribute("alunos", alunos);
        
        return "preceptor/listar-aluno";
    }
}
