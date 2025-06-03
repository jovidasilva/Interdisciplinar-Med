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
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Controlador para visualização de horários pelos preceptores
 */
@Controller
@RequestMapping({"/pages/preceptor", "/spring/pages/preceptor"})
public class PreceptorHorariosController {

    @Autowired
    private DataSource dataSource;

    @GetMapping({"/horarios", "/horarios.php"})
    public String horarios(HttpSession session, Model model) {
        // A verificação de login e tipo de usuário é feita pelo UserDataInterceptor
        
        // Obter o ID do preceptor da sessão
        Long idpreceptor = (Long) session.getAttribute("idusuario");
        if (idpreceptor == null) {
            return "redirect:/";
        }
        
        // Buscar os horários do preceptor no banco de dados
        List<Map<String, Object>> horarios = new ArrayList<>();
        
        try (Connection conn = dataSource.getConnection()) {
            // SQL para buscar os horários do preceptor
            String sql = "SELECT h.*, u.nome_unidade, m.nome_modulo, sg.nome_subgrupo " +
                          "FROM horarios h " +
                          "JOIN unidades u ON h.idunidade = u.idunidade " +
                          "JOIN modulos m ON h.idmodulo = m.idmodulo " +
                          "JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo " +
                          "WHERE h.idpreceptor = ? " +
                          "ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio";
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idpreceptor);
                ResultSet rs = stmt.executeQuery();
                
                DateTimeFormatter timeFormatter = DateTimeFormatter.ofPattern("HH:mm");
                
                while (rs.next()) {
                    Map<String, Object> horario = new HashMap<>();
                    horario.put("idhorario", rs.getLong("idhorario"));
                    horario.put("diaSemana", rs.getString("dia_semana"));
                    horario.put("horaInicio", rs.getTime("hora_inicio").toLocalTime().format(timeFormatter));
                    horario.put("horaFim", rs.getTime("hora_fim").toLocalTime().format(timeFormatter));
                    horario.put("local", rs.getString("local"));
                    horario.put("nomeUnidade", rs.getString("nome_unidade"));
                    horario.put("nomeModulo", rs.getString("nome_modulo"));
                    horario.put("nomeSubgrupo", rs.getString("nome_subgrupo"));
                    
                    horarios.add(horario);
                }
            }
            
            // Buscar os subgrupos aos quais o preceptor dará aula
            List<Map<String, Object>> subgruposPreceptor = new ArrayList<>();
            String sqlSubgrupos = "SELECT DISTINCT sg.idsubgrupo, sg.nome_subgrupo, g.nome_grupo " +
                                 "FROM subgrupos sg " +
                                 "JOIN grupos g ON sg.idgrupo = g.idgrupo " +
                                 "JOIN horarios h ON sg.idsubgrupo = h.idsubgrupo " +
                                 "WHERE h.idpreceptor = ? " +
                                 "ORDER BY g.nome_grupo, sg.nome_subgrupo";
            
            try (PreparedStatement stmt = conn.prepareStatement(sqlSubgrupos)) {
                stmt.setLong(1, idpreceptor);
                ResultSet rs = stmt.executeQuery();
                
                while (rs.next()) {
                    Map<String, Object> subgrupo = new HashMap<>();
                    subgrupo.put("idsubgrupo", rs.getLong("idsubgrupo"));
                    subgrupo.put("nomeSubgrupo", rs.getString("nome_subgrupo"));
                    subgrupo.put("nomeGrupo", rs.getString("nome_grupo"));
                    
                    subgruposPreceptor.add(subgrupo);
                }
            }
            
            model.addAttribute("horarios", horarios);
            model.addAttribute("subgruposPreceptor", subgruposPreceptor);
            
        } catch (SQLException e) {
            e.printStackTrace();
            model.addAttribute("erro", "Erro ao buscar horários: " + e.getMessage());
        }
        
        return "preceptor/horarios";
    }
}
