package com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Command;


public interface AvaliacaoCommand {
    
    /**
     
     * @return true se o comando foi executado com sucesso, false caso contrário
     */
    boolean executar();
    
    /**
     
     * @return true se o comando foi desfeito com sucesso, false caso contrário
     */
    boolean desfazer();
    
    /**
     
     * @return Descrição do comando
     */
    String getDescricao();
} 