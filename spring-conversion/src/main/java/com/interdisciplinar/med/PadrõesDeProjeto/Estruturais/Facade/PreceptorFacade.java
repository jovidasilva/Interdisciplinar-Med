package com.interdisciplinar.med.PadrõesDeProjeto.Estruturais.Facade;

import com.interdisciplinar.med.service.AvaliacaoService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

@Component
public class PreceptorFacade {

    private static final Logger logger = Logger.getLogger(PreceptorFacade.class.getName());

    @Autowired
    private AvaliacaoService avaliacaoService;

    @Autowired
    private DataSource dataSource;

    public Map<String, Object> obterDashboardPreceptor(Long idPreceptor) {
        Map<String, Object> dashboard = new HashMap<>();
        dashboard.put("preceptor", obterDadosPreceptor(idPreceptor));
        dashboard.put("rodizios", obterRodiziosPreceptor(idPreceptor));
        dashboard.put("avaliacoes", avaliacaoService.buscarAvaliacoesPorPreceptor(idPreceptor));
        dashboard.put("horarios", obterHorariosPreceptor(idPreceptor));
        // dashboard.put("estatisticas", calcularEstatisticas(idPreceptor));
        return dashboard;
    }

    public Map<String, Object> obterDadosPreceptor(Long idPreceptor) {
        Map<String, Object> preceptor = new HashMap<>();
        String sql = "SELECT * FROM usuarios WHERE id = ? AND tipo = 1";
        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, idPreceptor);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                preceptor.put("idusuario", rs.getLong("id"));
                preceptor.put("nome", rs.getString("nome"));
                preceptor.put("email", rs.getString("email"));
                preceptor.put("telefone", rs.getString("telefone"));
                preceptor.put("registro", rs.getString("registro"));
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter dados do preceptor", e);
        }
        return preceptor;
    }

    public List<Map<String, Object>> obterRodiziosPreceptor(Long idPreceptor) {
        List<Map<String, Object>> rodizios = new ArrayList<>();
        // SQL Incorreta, necessita de revisão da estrutura do banco
        return rodizios;
    }

    public Map<Long, Map<String, Object>> obterAlunosParaAvaliacao(Long idPreceptor) {
        Map<Long, Map<String, Object>> alunosMap = new LinkedHashMap<>();
        String query = "SELECT u.idusuario AS aluno_id, u.nome, u.registro, sg.nome_subgrupo, " +
                "m.idmodulo AS id_modulo, m.nome_modulo, " +
                "(SELECT COUNT(*) FROM avaliacoes a WHERE a.idaluno = u.idusuario AND a.idpreceptor = ? AND a.idmodulo = m.idmodulo) > 0 AS avaliado " +
                "FROM horarios h " +
                "JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo " +
                "JOIN alunos_subgrupos als ON als.idsubgrupo = sg.idsubgrupo " +
                "JOIN usuarios u ON u.idusuario = als.idusuario " +
                "JOIN modulos_alunos ma ON ma.idusuario = u.idusuario " +
                "JOIN preceptores_modulos pm ON pm.idusuario = h.idpreceptor AND pm.idmodulo = ma.idmodulo " +
                "JOIN modulos m ON m.idmodulo = ma.idmodulo " +
                "WHERE h.idpreceptor = ? AND u.tipo = 0 " +
                "ORDER BY u.nome, m.nome_modulo";

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setLong(1, idPreceptor);
            stmt.setLong(2, idPreceptor);
            ResultSet rs = stmt.executeQuery();

            while (rs.next()) {
                Long alunoId = rs.getLong("aluno_id");
                Map<String, Object> aluno = alunosMap.get(alunoId);

                if (aluno == null) {
                    aluno = new HashMap<>();
                    aluno.put("aluno_id", alunoId);
                    aluno.put("nome", rs.getString("nome"));
                    aluno.put("registro", rs.getString("registro"));
                    aluno.put("nomeSubgrupo", rs.getString("nome_subgrupo"));
                    aluno.put("modulos", new ArrayList<Map<String, Object>>());
                    alunosMap.put(alunoId, aluno);
                } else {
                    // agrega subgrupos distintos
                    String existente = (String) aluno.get("nomeSubgrupo");
                    String novo = rs.getString("nome_subgrupo");
                    if (!existente.contains(novo)) {
                        aluno.put("nomeSubgrupo", existente + ", " + novo);
                    }
                }

                @SuppressWarnings("unchecked")
                List<Map<String, Object>> modulos = (List<Map<String, Object>>) aluno.get("modulos");

                Map<String, Object> modulo = new HashMap<>();
                modulo.put("id_modulo", rs.getLong("id_modulo"));
                modulo.put("modulo_nome", rs.getString("nome_modulo"));
                modulo.put("avaliado", rs.getBoolean("avaliado"));
                modulos.add(modulo);
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter alunos para avaliação", e);
        }
        return alunosMap;
    }

    public void salvarAvaliacao(Long idPreceptor, Long idAluno, Long idModulo, float notaConhecimento, float notaHabilidade, float notaAtitude) {
        float notaFinal = (notaConhecimento + notaHabilidade + notaAtitude) / 3;
        String query = "INSERT INTO avaliacoes (id_aluno, id_preceptor, id_modulo, nota_conhecimento, nota_habilidade, nota_atitude, nota_final) VALUES (?, ?, ?, ?, ?, ?, ?)";
        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query)) {
            stmt.setLong(1, idAluno);
            stmt.setLong(2, idPreceptor);
            stmt.setLong(3, idModulo);
            stmt.setFloat(4, notaConhecimento);
            stmt.setFloat(5, notaHabilidade);
            stmt.setFloat(6, notaAtitude);
            stmt.setFloat(7, notaFinal);
            stmt.executeUpdate();
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao salvar avaliação", e);
        }
    }

    public List<Map<String, Object>> obterHorariosPreceptor(Long idPreceptor) {
        List<Map<String, Object>> horarios = new ArrayList<>();
        String sql = "SELECT h.*, m.nome AS nome_modulo, sg.nome AS nome_subgrupo " +
                     "FROM horarios h " +
                     "JOIN modulo m ON h.id_modulo = m.id " +
                     "JOIN subgrupo sg ON h.id_subgrupo = sg.id " +
                     "WHERE h.id_preceptor = ? " +
                     "ORDER BY h.dia_semana, h.hora_inicio";

        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, idPreceptor);
            ResultSet rs = stmt.executeQuery();

            while (rs.next()) {
                Map<String, Object> horario = new HashMap<>();
                horario.put("id_horario", rs.getLong("id"));
                horario.put("id_modulo", rs.getLong("id_modulo"));
                horario.put("nomeModulo", rs.getString("nome_modulo"));
                horario.put("id_subgrupo", rs.getLong("id_subgrupo"));
                horario.put("nomeSubgrupo", rs.getString("nome_subgrupo"));
                horario.put("diaSemana", rs.getInt("dia_semana"));
                horario.put("horaInicio", rs.getString("hora_inicio"));
                horario.put("horaFim", rs.getString("hora_fim"));
                horarios.add(horario);
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter horários do preceptor", e);
        }
        return horarios;
    }

    /**
     * Calcula estatísticas para o preceptor
     * @param idPreceptor ID do preceptor
     * @return Mapa com estatísticas
     */
    private Map<String, Object> calcularEstatisticas(Long idPreceptor) {
        Map<String, Object> estatisticas = new HashMap<>();
        // Total de avaliações realizadas
        String sqlTotalAvaliacoes = "SELECT COUNT(*) as total FROM avaliacoes WHERE id_preceptor = ?";
        // Total de alunos avaliados
        String sqlTotalAlunos = "SELECT COUNT(DISTINCT id_aluno) as total FROM avaliacoes WHERE id_preceptor = ?";
        // Médias
        String sqlMediaNotas = "SELECT AVG(nota_conhecimento) as media_conhecimento, AVG(nota_habilidade) as media_habilidade, AVG(nota_atitude) as media_atitude FROM avaliacoes WHERE id_preceptor = ?";

        try (Connection conn = dataSource.getConnection()) {
            try (PreparedStatement stmt = conn.prepareStatement(sqlTotalAvaliacoes)) {
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                if (rs.next()) {
                    estatisticas.put("totalAvaliacoes", rs.getInt("total"));
                }
            }
            try (PreparedStatement stmt = conn.prepareStatement(sqlTotalAlunos)) {
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                if (rs.next()) {
                    estatisticas.put("totalAlunosAvaliados", rs.getInt("total"));
                }
            }
            try (PreparedStatement stmt = conn.prepareStatement(sqlMediaNotas)) {
                stmt.setLong(1, idPreceptor);
                ResultSet rs = stmt.executeQuery();
                if (rs.next()) {
                    estatisticas.put("mediaConhecimento", rs.getDouble("media_conhecimento"));
                    estatisticas.put("mediaHabilidade", rs.getDouble("media_habilidade"));
                    estatisticas.put("mediaAtitude", rs.getDouble("media_atitude"));
                }
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao calcular estatísticas do preceptor", e);
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

    /**
     * Lista todas as perguntas de avaliação cadastradas no banco.
     */
    public List<Map<String, Object>> listarPerguntasAvaliacao() {
        List<Map<String, Object>> perguntas = new ArrayList<>();
        String sql = "SELECT idpergunta, titulo, descricao FROM perguntas_avaliacoes ORDER BY idpergunta";
        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {
            while (rs.next()) {
                Map<String, Object> pergunta = new HashMap<>();
                pergunta.put("idpergunta", rs.getLong("idpergunta"));
                pergunta.put("titulo", rs.getString("titulo"));
                pergunta.put("descricao", rs.getString("descricao"));
                perguntas.add(pergunta);
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao listar perguntas de avaliação", e);
        }
        return perguntas;
    }

    /**
     * Retorna os módulos em que o aluno e o preceptor possuem relação (via horários/subgrupos).
     */
    public List<Map<String, Object>> obterModulosAlunoPreceptor(Long idAluno, Long idPreceptor) {
        List<Map<String, Object>> modulos = new ArrayList<>();
        String sql = "SELECT DISTINCT m.idmodulo, m.nome_modulo " +
                "FROM horarios h " +
                "JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo " +
                "JOIN alunos_subgrupos als ON als.idsubgrupo = sg.idsubgrupo " +
                "JOIN usuarios u ON u.idusuario = als.idusuario " +
                "JOIN modulos m ON m.idmodulo = h.idmodulo " +
                "WHERE h.idpreceptor = ? AND u.idusuario = ?";
        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, idPreceptor);
            stmt.setLong(2, idAluno);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                Map<String, Object> modulo = new HashMap<>();
                modulo.put("idmodulo", rs.getLong("idmodulo"));
                modulo.put("nomeModulo", rs.getString("nome_modulo"));
                modulos.add(modulo);
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter módulos aluno/preceptor", e);
        }
        return modulos;
    }

    /**
     * Retorna todos os módulos em que o aluno está inscrito.
     */
    public List<Map<String, Object>> obterTodosModulosAluno(Long idAluno) {
        List<Map<String, Object>> modulos = new ArrayList<>();
        String sql = "SELECT m.idmodulo, m.nome_modulo FROM modulos m " +
                "JOIN modulos_alunos ma ON ma.idmodulo = m.idmodulo WHERE ma.idusuario = ?";
        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, idAluno);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                Map<String, Object> modulo = new HashMap<>();
                modulo.put("idmodulo", rs.getLong("idmodulo"));
                modulo.put("nomeModulo", rs.getString("nome_modulo"));
                modulos.add(modulo);
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter módulos do aluno", e);
        }
        return modulos;
    }

    /**
     * Salva avaliação baseada em nota média calculada a partir de perguntas.
     */
    public void salvarAvaliacaoFinal(Long idPreceptor, Long idAluno, Long idModulo, double notaMedia) {
        String sql = "INSERT INTO avaliacoes (idaluno, idpreceptor, idmodulo, nota, data_avaliacao) VALUES (?, ?, ?, ?, NOW())";
        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, idAluno);
            stmt.setLong(2, idPreceptor);
            stmt.setLong(3, idModulo);
            stmt.setDouble(4, notaMedia);
            stmt.executeUpdate();
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao salvar avaliação (nota média)", e);
        }
    }

    /**
     * Detalhes básicos de uma avaliação para visualização.
     */
    public Map<String, Object> obterDetalheAvaliacao(Long idAvaliacao) {
        Map<String, Object> detalhe = new HashMap<>();
        String sql = "SELECT a.nota, a.data_avaliacao, u.nome AS nomeAluno, m.nome_modulo AS nomeModulo " +
                "FROM avaliacoes a " +
                "JOIN usuarios u ON u.idusuario = a.idaluno " +
                "JOIN modulos m ON m.idmodulo = a.idmodulo " +
                "WHERE a.idavaliacao = ?";
        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, idAvaliacao);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                detalhe.put("nota", rs.getDouble("nota"));
                detalhe.put("dataAvaliacao", rs.getTimestamp("data_avaliacao").toLocalDateTime());
                detalhe.put("nomeAluno", rs.getString("nomeAluno"));
                detalhe.put("nomeModulo", rs.getString("nomeModulo"));
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter detalhes da avaliação", e);
        }
        return detalhe;
    }

    /**
     * Respostas individuais de cada pergunta para uma avaliação.
     */
    public List<Map<String, Object>> obterRespostasAvaliacao(Long idAvaliacao) {
        List<Map<String, Object>> respostas = new ArrayList<>();
        String sql = "SELECT p.titulo, p.descricao, r.valor_resposta " +
                "FROM respostas_avaliacoes r " +
                "JOIN perguntas_avaliacoes p ON p.idpergunta = r.idpergunta " +
                "WHERE r.idavaliacao = ?";
        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, idAvaliacao);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                Map<String, Object> resp = new HashMap<>();
                resp.put("tituloPergunta", rs.getString("titulo"));
                resp.put("descricaoPergunta", rs.getString("descricao"));
                resp.put("valorResposta", rs.getInt("valor_resposta"));
                respostas.add(resp);
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter respostas da avaliação", e);
        }
        return respostas;
    }

    /**
     * Lista todas as avaliações já realizadas para um aluno por um preceptor.
     */
    public List<Map<String, Object>> listarAvaliacoesAluno(Long idAluno, Long idPreceptor) {
        List<Map<String, Object>> avaliacoes = new ArrayList<>();
        String sql = "SELECT a.idavaliacao, a.nota, a.data_avaliacao, m.nome_modulo, m.periodo " +
                "FROM avaliacoes a " +
                "JOIN modulos m ON m.idmodulo = a.idmodulo " +
                "WHERE a.idaluno = ? AND a.idpreceptor = ? " +
                "ORDER BY a.data_avaliacao DESC";
        try (Connection conn = dataSource.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, idAluno);
            stmt.setLong(2, idPreceptor);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                Map<String, Object> av = new HashMap<>();
                av.put("idavaliacao", rs.getLong("idavaliacao"));
                av.put("nota", rs.getDouble("nota"));
                av.put("dataAvaliacao", rs.getTimestamp("data_avaliacao").toLocalDateTime());
                av.put("nomeModulo", rs.getString("nome_modulo"));
                av.put("periodo", rs.getString("periodo"));
                avaliacoes.add(av);
            }
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao listar avaliações do aluno", e);
        }
        return avaliacoes;
    }
}
