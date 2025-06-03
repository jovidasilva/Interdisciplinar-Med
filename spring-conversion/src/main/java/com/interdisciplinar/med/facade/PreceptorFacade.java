package com.interdisciplinar.med.facade;

import com.interdisciplinar.med.config.DatabaseConnectionManager;
import com.interdisciplinar.med.service.AvaliacaoService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

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
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Implementação do padrão Facade que fornece uma interface simplificada
 * para as operações relacionadas aos preceptores.
 * 
 * Esta fachada unifica o acesso a diferentes subsistemas (avaliações, rodízios, horários)
 * em uma única interface coesa, simplificando o uso para os controllers.
 */
@Component
public class PreceptorFacade {

    private static final Logger logger = Logger.getLogger(PreceptorFacade.class.getName());
    
    @Autowired
    private DataSource dataSource;
    
    @Autowired
    private AvaliacaoService avaliacaoService;
    
    /**
     * Obtém todas as informações relevantes para o dashboard do preceptor
     * @param idPreceptor ID do preceptor
     * @return Mapa com informações consolidadas
     */
    public Map<String, Object> obterDashboardPreceptor(Long idPreceptor) {
        Map<String, Object> dashboard = new HashMap<>();
        
        // Obter informações do preceptor
        dashboard.put("preceptor", obterDadosPreceptor(idPreceptor));
        
        // Obter rodízios do preceptor
        dashboard.put("rodizios", obterRodiziosPreceptor(idPreceptor));
        
        // Obter avaliações realizadas pelo preceptor
        dashboard.put("avaliacoes", avaliacaoService.buscarAvaliacoesPorPreceptor(idPreceptor));
        
        // Obter horários do preceptor
        dashboard.put("horarios", obterHorariosPreceptor(idPreceptor));
        
        // Obter estatísticas
        dashboard.put("estatisticas", calcularEstatisticas(idPreceptor));
        
        return dashboard;
    }
    
    /**
     * Obtém os dados pessoais do preceptor
     * @param idPreceptor ID do preceptor
     * @return Mapa com dados do preceptor
     */
    public Map<String, Object> obterDadosPreceptor(Long idPreceptor) {
        Map<String, Object> preceptor = new HashMap<>();
        
        DatabaseConnectionManager connectionManager = DatabaseConnectionManager.getInstance(dataSource);
        Connection conn = null;
        
        try {
            conn = connectionManager.getConnection();
            
            String sql = "SELECT * FROM usuarios WHERE idusuario = ? AND tipo = 1";
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                
                if (rs.next()) {
                    preceptor.put("idusuario", rs.getLong("idusuario"));
                    preceptor.put("nome", rs.getString("nome"));
                    preceptor.put("email", rs.getString("email"));
                    preceptor.put("telefone", rs.getString("telefone"));
                    preceptor.put("registro", rs.getString("registro"));
                }
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter dados do preceptor", e);
        } finally {
            if (conn != null) {
                connectionManager.closeConnection(conn);
            }
        }
        
        return preceptor;
    }
    
    /**
     * Obtém os rodízios associados ao preceptor
     * @param idPreceptor ID do preceptor
     * @return Lista de rodízios
     */
    public List<Map<String, Object>> obterRodiziosPreceptor(Long idPreceptor) {
        List<Map<String, Object>> rodizios = new ArrayList<>();
        
        DatabaseConnectionManager connectionManager = DatabaseConnectionManager.getInstance(dataSource);
        Connection conn = null;
        
        try {
            conn = connectionManager.getConnection();
            
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
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                
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
                }
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter rodízios do preceptor", e);
        } finally {
            if (conn != null) {
                connectionManager.closeConnection(conn);
            }
        }
        
        return rodizios;
    }
    
    /**
     * Obtém os horários do preceptor
     * @param idPreceptor ID do preceptor
     * @return Lista de horários
     */
    public List<Map<String, Object>> obterHorariosPreceptor(Long idPreceptor) {
        List<Map<String, Object>> horarios = new ArrayList<>();
        
        DatabaseConnectionManager connectionManager = DatabaseConnectionManager.getInstance(dataSource);
        Connection conn = null;
        
        try {
            conn = connectionManager.getConnection();
            
            String sql = "SELECT h.*, m.nome_modulo, sg.nome_subgrupo " +
                         "FROM horarios h " +
                         "JOIN modulos m ON h.idmodulo = m.idmodulo " +
                         "JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo " +
                         "WHERE h.idpreceptor = ? " +
                         "ORDER BY h.dia_semana, h.hora_inicio";
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                
                while (rs.next()) {
                    Map<String, Object> horario = new HashMap<>();
                    horario.put("idhorario", rs.getLong("idhorario"));
                    horario.put("idmodulo", rs.getLong("idmodulo"));
                    horario.put("nomeModulo", rs.getString("nome_modulo"));
                    horario.put("idsubgrupo", rs.getLong("idsubgrupo"));
                    horario.put("nomeSubgrupo", rs.getString("nome_subgrupo"));
                    horario.put("diaSemana", rs.getInt("dia_semana"));
                    horario.put("horaInicio", rs.getString("hora_inicio"));
                    horario.put("horaFim", rs.getString("hora_fim"));
                    
                    horarios.add(horario);
                }
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter horários do preceptor", e);
        } finally {
            if (conn != null) {
                connectionManager.closeConnection(conn);
            }
        }
        
        return horarios;
    }
    
    /**
     * Calcula estatu00edsticas para o preceptor
     * @param idPreceptor ID do preceptor
     * @return Mapa com estatísticas
     */
    private Map<String, Object> calcularEstatisticas(Long idPreceptor) {
        Map<String, Object> estatisticas = new HashMap<>();
        
        DatabaseConnectionManager connectionManager = DatabaseConnectionManager.getInstance(dataSource);
        Connection conn = null;
        
        try {
            conn = connectionManager.getConnection();
            
            // Total de avaliações realizadas
            String sqlTotalAvaliacoes = "SELECT COUNT(*) as total FROM avaliacoes WHERE idpreceptor = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sqlTotalAvaliacoes)) {
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                if (rs.next()) {
                    estatisticas.put("totalAvaliacoes", rs.getInt("total"));
                }
            }
            
            // Total de alunos avaliados (distintos)
            String sqlTotalAlunos = "SELECT COUNT(DISTINCT idaluno) as total FROM avaliacoes WHERE idpreceptor = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sqlTotalAlunos)) {
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                if (rs.next()) {
                    estatisticas.put("totalAlunosAvaliados", rs.getInt("total"));
                }
            }
            
            // Média das notas atribuídas
            String sqlMediaNotas = "SELECT " +
                                  "  AVG(nota_conhecimento) as media_conhecimento, " +
                                  "  AVG(nota_habilidades) as media_habilidades, " +
                                  "  AVG(nota_atitudes) as media_atitudes " +
                                  "FROM avaliacoes WHERE idpreceptor = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sqlMediaNotas)) {
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                if (rs.next()) {
                    estatisticas.put("mediaConhecimento", rs.getDouble("media_conhecimento"));
                    estatisticas.put("mediaHabilidades", rs.getDouble("media_habilidades"));
                    estatisticas.put("mediaAtitudes", rs.getDouble("media_atitudes"));
                }
            }
            
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao calcular estatísticas do preceptor", e);
        } finally {
            if (conn != null) {
                connectionManager.closeConnection(conn);
            }
        }
        
        return estatisticas;
    }
    
    /**
     * Cria uma avaliação completa para um aluno
     * @param idAluno ID do aluno
     * @param idPreceptor ID do preceptor
     * @param idModulo ID do módulo
     * @param idSubgrupo ID do subgrupo
     * @param notaConhecimento Nota de conhecimento
     * @param notaHabilidades Nota de habilidades
     * @param notaAtitudes Nota de atitudes
     * @param observacoes Observações
     * @return true se a avaliação foi criada com sucesso, false caso contrário
     */
    public boolean criarAvaliacaoCompleta(Long idAluno, Long idPreceptor, Long idModulo, Long idSubgrupo,
                                        Integer notaConhecimento, Integer notaHabilidades, Integer notaAtitudes,
                                        String observacoes) {
        // Delega para o serviço de avaliação
        return avaliacaoService.criarAvaliacao(
            idAluno, idPreceptor, idModulo, idSubgrupo,
            notaConhecimento, notaHabilidades, notaAtitudes, observacoes
        );
    }
}
