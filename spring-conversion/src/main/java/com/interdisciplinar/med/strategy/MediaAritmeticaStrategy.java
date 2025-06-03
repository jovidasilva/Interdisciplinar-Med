package com.interdisciplinar.med.strategy;

import org.springframework.stereotype.Component;

/**
 * Implementação do padrão Strategy para cálculo de nota usando média aritmética.
 */
@Component
public class MediaAritmeticaStrategy implements CalculoNotaStrategy {

    /**
     * Calcula a nota final como a média aritmética das notas parciais
     * 
     * @param notas Array de notas parciais
     * @return A média aritmética das notas
     */
    @Override
    public double calcularNota(double[] notas) {
        if (notas == null || notas.length == 0) {
            return 0.0;
        }
        
        double soma = 0.0;
        for (double nota : notas) {
            soma += nota;
        }
        
        return soma / notas.length;
    }

    /**
     * Retorna a descrição do método de cálculo
     * 
     * @return Descrição do método de cálculo
     */
    @Override
    public String getDescricao() {
        return "Média Aritmética";
    }
}
