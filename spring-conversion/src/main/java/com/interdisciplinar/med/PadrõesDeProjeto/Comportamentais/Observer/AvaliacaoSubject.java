package com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Observer;

import com.interdisciplinar.med.model.Avaliacao;
import org.springframework.stereotype.Component;

import java.util.ArrayList;
import java.util.List;

/**
 * Implementação do padrão Observer (Comportamental) que gerencia observadores
 * interessados em mudanças nas avaliações.
 * 
 * Este padrão permite que objetos se inscrevam para receber notificações
 * quando ocorrem mudanças em avaliações, sem criar acoplamento forte entre
 * os componentes do sistema.
 */
@Component
public class AvaliacaoSubject {
    
    private final List<AvaliacaoObserver> observadores = new ArrayList<>();
    
    /**
     * Registra um novo observador para receber notificações
     * @param observador O observador a ser registrado
     */
    public void registrarObservador(AvaliacaoObserver observador) {
        if (!observadores.contains(observador)) {
            observadores.add(observador);
        }
    }
    
    /**
     * Remove um observador registrado
     * @param observador O observador a ser removido
     */
    public void removerObservador(AvaliacaoObserver observador) {
        observadores.remove(observador);
    }
    
    /**
     * Notifica todos os observadores registrados sobre a criação de uma avaliação
     * @param avaliacao A avaliação criada
     */
    public void notificarAvaliacaoCriada(Avaliacao avaliacao) {
        for (AvaliacaoObserver observador : observadores) {
            observador.onAvaliacaoCriada(avaliacao);
        }
    }
    
    /**
     * Notifica todos os observadores registrados sobre a atualização de uma avaliação
     * @param avaliacao A avaliação atualizada
     */
    public void notificarAvaliacaoAtualizada(Avaliacao avaliacao) {
        for (AvaliacaoObserver observador : observadores) {
            observador.onAvaliacaoAtualizada(avaliacao);
        }
    }
    
    /**
     * Notifica todos os observadores registrados sobre a exclusão de uma avaliação
     * @param avaliacaoId O ID da avaliação excluída
     */
    public void notificarAvaliacaoExcluida(Long avaliacaoId) {
        for (AvaliacaoObserver observador : observadores) {
            observador.onAvaliacaoExcluida(avaliacaoId);
        }
    }
}
