package com.interdisciplinar.med.controller;

import com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Command.AvaliacaoCommandManager;
import com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Command.CriarAvaliacaoCommand;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.HashMap;
import java.util.Map;

/**
 * Controller que demonstra o uso do padrão Command para operações de avaliação.
 * Este controller permite criar avaliações e desfazer operações anteriores.
 */
@RestController
@RequestMapping("/api/avaliacoes/command")
public class AvaliacaoCommandController {
    
    private final AvaliacaoCommandManager commandManager;
    private final CriarAvaliacaoCommand criarAvaliacaoCommand;
    
    @Autowired
    public AvaliacaoCommandController(
            AvaliacaoCommandManager commandManager,
            CriarAvaliacaoCommand criarAvaliacaoCommand) {
        this.commandManager = commandManager;
        this.criarAvaliacaoCommand = criarAvaliacaoCommand;
    }
    
    /**
     * Cria uma nova avaliação usando o padrão Command
     */
    @PostMapping("/criar")
    public ResponseEntity<Map<String, Object>> criarAvaliacao(
            @RequestParam Long idAluno,
            @RequestParam Long idPreceptor,
            @RequestParam Long idModulo,
            @RequestParam(required = false) Long idSubgrupo,
            @RequestParam Integer notaConhecimento,
            @RequestParam Integer notaHabilidades,
            @RequestParam Integer notaAtitudes,
            @RequestParam(required = false) String observacoes) {
        
        // Configura o comando com os parâmetros
        criarAvaliacaoCommand.comParametros(
            idAluno, idPreceptor, idModulo, idSubgrupo,
            notaConhecimento, notaHabilidades, notaAtitudes, observacoes
        );
        
        // Executa o comando
        boolean sucesso = commandManager.executarComando(criarAvaliacaoCommand);
        
        Map<String, Object> resposta = new HashMap<>();
        resposta.put("sucesso", sucesso);
        resposta.put("comando", criarAvaliacaoCommand.getDescricao());
        
        return ResponseEntity.ok(resposta);
    }
    
    /**
     * Desfaz a última operação realizada
     */
    @PostMapping("/desfazer")
    public ResponseEntity<Map<String, Object>> desfazerUltimaOperacao() {
        boolean sucesso = commandManager.desfazerUltimoComando();
        
        Map<String, Object> resposta = new HashMap<>();
        resposta.put("sucesso", sucesso);
        
        if (sucesso) {
            resposta.put("comandoDesfeito", commandManager.getDescricaoUltimoComando());
        }
        
        return ResponseEntity.ok(resposta);
    }
    
    /**
     * Verifica se existem operações para desfazer
     */
    @GetMapping("/tem-operacoes-para-desfazer")
    public ResponseEntity<Map<String, Object>> temOperacoesParaDesfazer() {
        boolean temOperacoes = commandManager.temComandosParaDesfazer();
        
        Map<String, Object> resposta = new HashMap<>();
        resposta.put("temOperacoes", temOperacoes);
        
        if (temOperacoes) {
            resposta.put("ultimoComando", commandManager.getDescricaoUltimoComando());
        }
        
        return ResponseEntity.ok(resposta);
    }
} 