package com.interdisciplinar.med.observer;

import com.interdisciplinar.med.model.Avaliacao;

/**
 * Interface para o padrão Observer que define o contrato para observadores
 * que desejam ser notificados sobre mudanças em avaliações.
 */
public interface AvaliacaoObserver {
    
    /**
     * Método chamado quando uma avaliação é criada
     * @param avaliacao A avaliação que foi criada
     */
    void onAvaliacaoCriada(Avaliacao avaliacao);
    
    /**
     * Método chamado quando uma avaliação é atualizada
     * @param avaliacao A avaliação que foi atualizada
     */
    void onAvaliacaoAtualizada(Avaliacao avaliacao);
    
    /**
     * Método chamado quando uma avaliação é excluída
     * @param avaliacaoId O ID da avaliação que foi excluída
     */
    void onAvaliacaoExcluida(Long avaliacaoId);
}
