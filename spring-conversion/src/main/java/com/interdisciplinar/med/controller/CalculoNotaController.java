package com.interdisciplinar.med.controller;

import com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Strategy.CalculoNotaContext;
import com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Strategy.MaiorNotaStrategy;
import com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Strategy.MediaAritmeticaStrategy;
import com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Strategy.MediaPonderadaStrategy;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.HashMap;
import java.util.Map;

/**
 * Controlador que demonstra o uso do padrão Strategy para cálculo de notas.
 * Este controlador permite escolher diferentes estratégias de cálculo e aplicá-las a um conjunto de notas.
 */
@RestController
@RequestMapping("/api/calculo-nota")
public class CalculoNotaController {

    private final CalculoNotaContext calculoNotaContext;
    private final MediaAritmeticaStrategy mediaAritmeticaStrategy;
    private final MaiorNotaStrategy maiorNotaStrategy;
    
    @Autowired
    public CalculoNotaController(
            CalculoNotaContext calculoNotaContext,
            MediaAritmeticaStrategy mediaAritmeticaStrategy,
            MaiorNotaStrategy maiorNotaStrategy) {
        this.calculoNotaContext = calculoNotaContext;
        this.mediaAritmeticaStrategy = mediaAritmeticaStrategy;
        this.maiorNotaStrategy = maiorNotaStrategy;
    }
    
    /**
     * Calcula a nota final usando a estratégia especificada
     * @param estrategia Nome da estratégia (media-aritmetica, media-ponderada, maior-nota)
     * @param notaConhecimento Nota de conhecimento
     * @param notaHabilidades Nota de habilidades
     * @param notaAtitudes Nota de atitudes
     * @return Resultado do cálculo
     */
    @PostMapping("/calcular")
    public ResponseEntity<Map<String, Object>> calcularNota(
            @RequestParam String estrategia,
            @RequestParam Double notaConhecimento,
            @RequestParam Double notaHabilidades,
            @RequestParam Double notaAtitudes) {
        
        double[] notas = {notaConhecimento, notaHabilidades, notaAtitudes};
        
        // Selecionar a estratégia com base no parâmetro
        switch (estrategia) {
            case "media-aritmetica":
                calculoNotaContext.setEstrategia(mediaAritmeticaStrategy);
                break;
            case "media-ponderada":
                // Conhecimento: 40%, Habilidades: 40%, Atitudes: 20%
                calculoNotaContext.setEstrategia(new MediaPonderadaStrategy(new double[]{0.4, 0.4, 0.2}));
                break;
            case "maior-nota":
                calculoNotaContext.setEstrategia(maiorNotaStrategy);
                break;
            default:
                return ResponseEntity.badRequest().body(Map.of("erro", "Estratégia inválida"));
        }
        
        // Calcular a nota usando a estratégia selecionada
        double notaFinal = calculoNotaContext.calcularNota(notas);
        
        // Preparar a resposta
        Map<String, Object> resposta = new HashMap<>();
        resposta.put("estrategia", estrategia);
        resposta.put("descricao", calculoNotaContext.getDescricaoEstrategia());
        resposta.put("nota_conhecimento", notaConhecimento);
        resposta.put("nota_habilidades", notaHabilidades);
        resposta.put("nota_atitudes", notaAtitudes);
        resposta.put("nota_final", notaFinal);
        
        return ResponseEntity.ok(resposta);
    }
    
    /**
     * Retorna as estratégias de cálculo disponíveis
     */
    @GetMapping("/estrategias")
    public ResponseEntity<Map<String, String>> listarEstrategias() {
        Map<String, String> estrategias = new HashMap<>();
        estrategias.put("media-aritmetica", "Média Aritmética das notas");
        estrategias.put("media-ponderada", "Média Ponderada (Conhecimento: 40%, Habilidades: 40%, Atitudes: 20%)");
        estrategias.put("maior-nota", "Maior nota entre as três");
        
        return ResponseEntity.ok(estrategias);
    }
} 