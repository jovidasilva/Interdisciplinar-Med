package com.interdisciplinar.med.config;

import com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Observer.AvaliacaoObserver;
import com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Observer.AvaliacaoSubject;
import com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Observer.EmailNotificacaoObserver;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

/**
 * Configuração para o padrão Observer.
 * Esta classe registra os observadores no subject.
 */
@Configuration
public class ObserverConfig {

    @Autowired
    private AvaliacaoSubject avaliacaoSubject;
    
    /**
     * Cria e registra o observador de e-mail
     */
    @Bean
    public AvaliacaoObserver emailNotificacaoObserver() {
        EmailNotificacaoObserver observer = new EmailNotificacaoObserver();
        avaliacaoSubject.registrarObservador(observer);
        return observer;
    }
} 