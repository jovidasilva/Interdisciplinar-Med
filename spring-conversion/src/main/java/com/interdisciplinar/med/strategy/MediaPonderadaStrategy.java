package com.interdisciplinar.med.strategy;

import org.springframework.stereotype.Component;

/**
 * Implementação do padrão Strategy para cálculo de nota usando média ponderada.
 */
@Component
public class MediaPonderadaStrategy implements CalculoNotaStrategy {

    private final double[] pesos;
    
    /**
     * Construtor que define os pesos padrão para as notas
     */
    public MediaPonderadaStrategy() {
        // Pesos padrão: primeira nota (30%), segunda nota (30%), terceira nota (40%)
        this.pesos = new double[] {0.3, 0.3, 0.4};
    }
    
    /**
     * Construtor que permite definir pesos personalizados
     * @param pesos Array de pesos para as notas
     */
    public MediaPonderadaStrategy(double[] pesos) {
        this.pesos = pesos;
    }

    /**
     * Calcula a nota final como a média ponderada das notas parciais
     * 
     * @param notas Array de notas parciais
     * @return A média ponderada das notas
     */
    @Override
    public double calcularNota(double[] notas) {
        if (notas == null || notas.length == 0) {
            return 0.0;
        }
        
        // Determina o número de pesos a usar (mínimo entre tamanho de notas e pesos)
        int tamanho = Math.min(notas.length, pesos.length);
        
        double somaPonderada = 0.0;
        double somaPesos = 0.0;
        
        for (int i = 0; i < tamanho; i++) {
            somaPonderada += notas[i] * pesos[i];
            somaPesos += pesos[i];
        }
        
        // Evita divisão por zero
        if (somaPesos == 0.0) {
            return 0.0;
        }
        
        return somaPonderada / somaPesos;
    }

    /**
     * Retorna a descrição do método de cálculo
     * 
     * @return Descrição do método de cálculo
     */
    @Override
    public String getDescricao() {
        return "Média Ponderada";
    }
}
