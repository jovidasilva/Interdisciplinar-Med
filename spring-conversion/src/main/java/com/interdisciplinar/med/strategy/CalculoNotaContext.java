package com.interdisciplinar.med.strategy;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

/**
 * Classe de contexto para o padrão Strategy que gerencia a estratégia de cálculo de nota a ser utilizada.
 * Esta classe permite trocar dinamicamente entre diferentes estratégias de cálculo.
 */
@Component
public class CalculoNotaContext {

    // Estratégia padrão (pode ser alterada em tempo de execução)
    private CalculoNotaStrategy estrategia;
    
    /**
     * Construtor que injeta a estratégia padrão (média aritmética)
     * @param mediaAritmeticaStrategy Estratégia padrão de cálculo
     */
    @Autowired
    public CalculoNotaContext(MediaAritmeticaStrategy mediaAritmeticaStrategy) {
        this.estrategia = mediaAritmeticaStrategy;
    }
    
    /**
     * Define a estratégia de cálculo a ser utilizada
     * @param estrategia A estratégia de cálculo
     */
    public void setEstrategia(CalculoNotaStrategy estrategia) {
        this.estrategia = estrategia;
    }
    
    /**
     * Executa o cálculo da nota usando a estratégia atual
     * @param notas Array de notas parciais
     * @return A nota final calculada
     */
    public double calcularNota(double[] notas) {
        return estrategia.calcularNota(notas);
    }
    
    /**
     * Retorna a descrição da estratégia atual
     * @return Descrição da estratégia
     */
    public String getDescricaoEstrategia() {
        return estrategia.getDescricao();
    }
}
