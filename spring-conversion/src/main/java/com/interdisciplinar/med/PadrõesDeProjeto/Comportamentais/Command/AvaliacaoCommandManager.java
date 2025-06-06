package com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Command;

import org.springframework.stereotype.Component;

import java.util.Stack;

/**
 * Esta classe mantém um histórico de comandos executados e permite
 * desfazer operações na ordem inversa.
 */
@Component
public class AvaliacaoCommandManager {
    
    private final Stack<AvaliacaoCommand> historicoComandos = new Stack<>();
    public boolean executarComando(AvaliacaoCommand comando) {
        if (comando.executar()) {
            historicoComandos.push(comando);
            return true;
        }
        return false;
    }
    
    public boolean desfazerUltimoComando() {
        if (historicoComandos.isEmpty()) {
            return false;
        }
        
        AvaliacaoCommand ultimoComando = historicoComandos.pop();
        return ultimoComando.desfazer();
    }
    
    public boolean temComandosParaDesfazer() {
        return !historicoComandos.isEmpty();
    }
    
    public String getDescricaoUltimoComando() {
        if (historicoComandos.isEmpty()) {
            return null;
        }
        return historicoComandos.peek().getDescricao();
    }
} 