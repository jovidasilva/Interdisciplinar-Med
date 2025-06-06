package com.interdisciplinar.med.PadrõesDeProjeto.Comportamentais.Command;

import com.interdisciplinar.med.model.Avaliacao;
import com.interdisciplinar.med.service.AvaliacaoService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

/**
 * Este comando encapsula a lógica de criação de uma avaliação e permite
 * desfazer a operação se necessário.
 */
@Component
public class CriarAvaliacaoCommand implements AvaliacaoCommand {
    
    private final AvaliacaoService avaliacaoService;
    private Long idAluno;
    private Long idPreceptor;
    private Long idModulo;
    private Long idSubgrupo;
    private Integer notaConhecimento;
    private Integer notaHabilidades;
    private Integer notaAtitudes;
    private String observacoes;
    
    private Long idAvaliacaoCriada;
    
    @Autowired
    public CriarAvaliacaoCommand(AvaliacaoService avaliacaoService) {
        this.avaliacaoService = avaliacaoService;
        // Inicialização padrão dos campos
        this.idAluno = null;
        this.idPreceptor = null;
        this.idModulo = null;
        this.idSubgrupo = null;
        this.notaConhecimento = null;
        this.notaHabilidades = null;
        this.notaAtitudes = null;
        this.observacoes = null;
    }
    
    /**
     * Configura os parâmetros do comando
     */
    public CriarAvaliacaoCommand comParametros(
            Long idAluno, Long idPreceptor, Long idModulo, Long idSubgrupo,
            Integer notaConhecimento, Integer notaHabilidades, Integer notaAtitudes,
            String observacoes) {
        this.idAluno = idAluno;
        this.idPreceptor = idPreceptor;
        this.idModulo = idModulo;
        this.idSubgrupo = idSubgrupo;
        this.notaConhecimento = notaConhecimento;
        this.notaHabilidades = notaHabilidades;
        this.notaAtitudes = notaAtitudes;
        this.observacoes = observacoes;
        return this;
    }
    
    @Override
    public boolean executar() {
        if (!validarParametros()) {
            return false;
        }
        
        boolean sucesso = avaliacaoService.criarAvaliacao(
            idAluno, idPreceptor, idModulo, idSubgrupo,
            notaConhecimento, notaHabilidades, notaAtitudes, observacoes
        );
        
        if (sucesso) {
            // Obtém o ID da avaliação recém-criada
            Avaliacao ultimaAvaliacao = avaliacaoService.buscarUltimaAvaliacaoCriada(
                idAluno, idPreceptor, idModulo
            );
            
            if (ultimaAvaliacao != null) {
                this.idAvaliacaoCriada = ultimaAvaliacao.getIdavaliacao();
            }
        }
        
        return sucesso;
    }
    
    @Override
    public boolean desfazer() {
        if (idAvaliacaoCriada == null) {
            return false;
        }
        
        // Exclui a avaliação criada
        return avaliacaoService.excluirAvaliacao(idAvaliacaoCriada);
    }
    
    @Override
    public String getDescricao() {
        return "Criar Avaliação";
    }
    
    private boolean validarParametros() {
        return idAluno != null && idPreceptor != null && idModulo != null &&
               notaConhecimento != null && notaHabilidades != null && notaAtitudes != null;
    }
} 