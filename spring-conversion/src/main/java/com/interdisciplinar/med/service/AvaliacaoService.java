package com.interdisciplinar.med.service;

import java.util.List;
import java.util.Map;

/**
 * Interface para o serviço de avaliações
 */
public interface AvaliacaoService {
    
    /**
     * Busca avaliações feitas por um preceptor específico
     * @param idPreceptor ID do preceptor
     * @return Lista de avaliações
     */
    List<Map<String, Object>> buscarAvaliacoesPorPreceptor(Long idPreceptor);
    
    /**
     * Cria uma nova avaliação
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
    boolean criarAvaliacao(Long idAluno, Long idPreceptor, Long idModulo, Long idSubgrupo,
                         Integer notaConhecimento, Integer notaHabilidades, Integer notaAtitudes,
                         String observacoes);
}