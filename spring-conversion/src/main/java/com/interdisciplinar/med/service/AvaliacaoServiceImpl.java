package com.interdisciplinar.med.service;

import com.interdisciplinar.med.config.DatabaseConnectionManager;
import com.interdisciplinar.med.model.Avaliacao;
import com.interdisciplinar.med.model.Modulo;
import com.interdisciplinar.med.model.Subgrupo;
import com.interdisciplinar.med.model.Usuario;
import com.interdisciplinar.med.model.builder.AvaliacaoBuilder;
import com.interdisciplinar.med.model.factory.UsuarioFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Implementação do serviço de avaliações utilizando os padrões de projeto criacionais:
 * - Singleton (DatabaseConnectionManager)
 * - Factory Method (UsuarioFactory)
 * - Builder (AvaliacaoBuilder)
 */
@Service
public class AvaliacaoServiceImpl implements AvaliacaoService {

    private static final Logger logger = Logger.getLogger(AvaliacaoServiceImpl.class.getName());
    
    @Autowired
    private DataSource dataSource;
    
    @Autowired
    private UsuarioFactory usuarioFactory;
    
    /**
     * Busca avaliações feitas por um preceptor específico
     * @param idPreceptor ID do preceptor
     * @return Lista de avaliações
     */
    @Override
    public List<Map<String, Object>> buscarAvaliacoesPorPreceptor(Long idPreceptor) {
        List<Map<String, Object>> avaliacoes = new ArrayList<>();
        
        // Utilizando o Singleton para obter a conexão com o banco de dados
        DatabaseConnectionManager connectionManager = DatabaseConnectionManager.getInstance(dataSource);
        Connection conn = null;
        
        try {
            conn = connectionManager.getConnection();
            
            String sql = "SELECT a.*, u.nome as nome_aluno, m.nome_modulo, sg.nome_subgrupo " +
                         "FROM avaliacoes a " +
                         "JOIN usuarios u ON a.idaluno = u.idusuario " +
                         "JOIN modulos m ON a.idmodulo = m.idmodulo " +
                         "JOIN subgrupos sg ON a.idsubgrupo = sg.idsubgrupo " +
                         "WHERE a.idpreceptor = ? " +
                         "ORDER BY a.data DESC";
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                
                while (rs.next()) {
                    Map<String, Object> avaliacao = new HashMap<>();
                    avaliacao.put("idavaliacao", rs.getLong("idavaliacao"));
                    avaliacao.put("idaluno", rs.getLong("idaluno"));
                    avaliacao.put("nome_aluno", rs.getString("nome_aluno"));
                    avaliacao.put("idmodulo", rs.getLong("idmodulo"));
                    avaliacao.put("nome_modulo", rs.getString("nome_modulo"));
                    avaliacao.put("idsubgrupo", rs.getLong("idsubgrupo"));
                    avaliacao.put("nome_subgrupo", rs.getString("nome_subgrupo"));
                    avaliacao.put("data", rs.getDate("data").toLocalDate());
                    avaliacao.put("nota_conhecimento", rs.getInt("nota_conhecimento"));
                    avaliacao.put("nota_habilidades", rs.getInt("nota_habilidades"));
                    avaliacao.put("nota_atitudes", rs.getInt("nota_atitudes"));
                    avaliacao.put("observacoes", rs.getString("observacoes"));
                    avaliacao.put("finalizada", rs.getBoolean("finalizada"));
                    
                    avaliacoes.add(avaliacao);
                }
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao buscar avaliações por preceptor", e);
        } finally {
            // Fechando a conexão de forma segura usando o Singleton
            if (conn != null) {
                connectionManager.closeConnection(conn);
            }
        }
        
        return avaliacoes;
    }
    
    /**
     * Cria uma nova avaliação utilizando o padrão Builder
     * @param idAluno ID do aluno
     * @param idPreceptor ID do preceptor
     * @param idModulo ID do módulo
     * @param idSubgrupo ID do subgrupo
     * @param notaConhecimento Nota de conhecimento
     * @param notaHabilidades Nota de habilidades
     * @param notaAtitudes Nota de atitudes
     * @param observacoes Observações
     * @return true se a avaliação foi salva com sucesso, false caso contrário
     */
    @Override
    public boolean criarAvaliacao(Long idAluno, Long idPreceptor, Long idModulo, Long idSubgrupo,
                                 Integer notaConhecimento, Integer notaHabilidades, Integer notaAtitudes,
                                 String observacoes) {
        // Criando objetos necessários
        Usuario aluno = new Usuario();
        aluno.setIdusuario(idAluno);
        
        // Utilizando o Factory Method para criar um preceptor
        Usuario preceptor = usuarioFactory.criarUsuario(UsuarioFactory.TIPO_PRECEPTOR);
        preceptor.setIdusuario(idPreceptor);
        
        Modulo modulo = new Modulo();
        modulo.setIdmodulo(idModulo);
        
        Subgrupo subgrupo = new Subgrupo();
        subgrupo.setIdsubgrupo(idSubgrupo);
        
        // Utilizando o Builder para construir a avaliação
        Avaliacao avaliacao = new AvaliacaoBuilder()
                .comAluno(aluno)
                .comPreceptor(preceptor)
                .noModulo(modulo)
                .noSubgrupo(subgrupo)
                .naData(LocalDate.now())
                .comNotas(notaConhecimento, notaHabilidades, notaAtitudes)
                .comObservacoes(observacoes)
                .finalizada()
                .construir();
        
        // Salvando a avaliação no banco de dados
        return salvarAvaliacao(avaliacao);
    }
    
    /**
     * Salva uma avaliação no banco de dados
     * @param avaliacao Objeto Avaliacao a ser salvo
     * @return true se a avaliação foi salva com sucesso, false caso contrário
     */
    private boolean salvarAvaliacao(Avaliacao avaliacao) {
        // Utilizando o Singleton para obter a conexão com o banco de dados
        DatabaseConnectionManager connectionManager = DatabaseConnectionManager.getInstance(dataSource);
        Connection conn = null;
        
        try {
            conn = connectionManager.getConnection();
            
            String sql = "INSERT INTO avaliacoes (idaluno, idpreceptor, idmodulo, idsubgrupo, data, " +
                         "nota_conhecimento, nota_habilidades, nota_atitudes, observacoes, finalizada) " +
                         "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setLong(1, avaliacao.getAluno().getIdusuario());
                stmt.setLong(2, avaliacao.getPreceptor().getIdusuario());
                stmt.setLong(3, avaliacao.getModulo().getIdmodulo());
                stmt.setLong(4, avaliacao.getSubgrupo().getIdsubgrupo());
                stmt.setObject(5, avaliacao.getData());
                stmt.setInt(6, avaliacao.getNota_conhecimento());
                stmt.setInt(7, avaliacao.getNota_habilidades());
                stmt.setInt(8, avaliacao.getNota_atitudes());
                stmt.setString(9, avaliacao.getObservacoes());
                stmt.setBoolean(10, avaliacao.getFinalizada());
                
                int rowsAffected = stmt.executeUpdate();
                return rowsAffected > 0;
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao salvar avaliação", e);
            return false;
        } finally {
            // Fechando a conexão de forma segura usando o Singleton
            if (conn != null) {
                connectionManager.closeConnection(conn);
            }
        }
    }
}
