package com.interdisciplinar.med.observer;

import com.interdisciplinar.med.model.Avaliacao;
import org.springframework.stereotype.Component;

/**
 * Implementação concreta do padrão Observer para enviar notificações por email
 * quando ocorrem mudanças em avaliações.
 */
@Component
public class EmailNotificacaoObserver implements AvaliacaoObserver {

    /**
     * Envia notificação por email quando uma avaliação é criada
     * @param avaliacao A avaliação que foi criada
     */
    @Override
    public void onAvaliacaoCriada(Avaliacao avaliacao) {
        // Aqui seria implementada a lógica de envio de email
        System.out.println("Email enviado: Nova avaliação criada para o aluno ID " + 
                avaliacao.getAluno().getIdusuario());
    }

    /**
     * Envia notificação por email quando uma avaliação é atualizada
     * @param avaliacao A avaliação que foi atualizada
     */
    @Override
    public void onAvaliacaoAtualizada(Avaliacao avaliacao) {
        // Aqui seria implementada a lógica de envio de email
        System.out.println("Email enviado: Avaliação atualizada para o aluno ID " + 
                avaliacao.getAluno().getIdusuario());
    }

    /**
     * Envia notificação por email quando uma avaliação é excluída
     * @param avaliacaoId O ID da avaliação que foi excluída
     */
    @Override
    public void onAvaliacaoExcluida(Long avaliacaoId) {
        // Aqui seria implementada a lógica de envio de email
        System.out.println("Email enviado: Avaliação ID " + avaliacaoId + " foi excluída");
    }
}
