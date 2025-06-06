package com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Strategy;

import org.springframework.stereotype.Component;

/**
 * Implementação do padrão Strategy para cálculo de nota usando a maior nota obtida.
 * Utilizado em casos onde se considera apenas a melhor avaliação do aluno.
 */
@Component
public class MaiorNotaStrategy implements CalculoNotaStrategy {

    /**
     * Calcula a nota final como a maior nota entre as notas parciais
     * 
     * @param notas Array de notas parciais
     * @return A maior nota entre as notas parciais
     */
    @Override
    public double calcularNota(double[] notas) {
        if (notas == null || notas.length == 0) {
            return 0.0;
        }
        
        double maiorNota = notas[0];
        
        for (double nota : notas) {
            if (nota > maiorNota) {
                maiorNota = nota;
            }
        }
        
        return maiorNota;
    }

    /**
     * Retorna a descrição do método de cálculo
     * 
     * @return Descrição do método de cálculo
     */
    @Override
    public String getDescricao() {
        return "Maior Nota";
    }
}
