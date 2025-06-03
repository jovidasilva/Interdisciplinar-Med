package com.interdisciplinar.med.strategy;

/**
 * Interface para o padrão Strategy (Comportamental) que define diferentes
 * estratégias de cálculo de notas.
 * 
 * Este padrão permite definir uma família de algoritmos, encapsulá-los e
 * torná-los intercambiáveis. O Strategy permite que o algoritmo varie
 * independentemente dos clientes que o utilizam.
 */
public interface CalculoNotaStrategy {
    
    /**
     * Calcula a nota final com base nas notas parciais
     * 
     * @param notas Array de notas parciais
     * @return A nota final calculada
     */
    double calcularNota(double[] notas);
    
    /**
     * Retorna a descrição do método de cálculo
     * 
     * @return Descrição do método de cálculo
     */
    String getDescricao();
}
