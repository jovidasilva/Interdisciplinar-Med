package com.interdisciplinar.med.PadrõesDeProjeto.Estruturais.Decorator;

import com.interdisciplinar.med.model.Avaliacao;
import com.interdisciplinar.med.service.AvaliacaoService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Primary;
import org.springframework.stereotype.Component;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Map;
import java.util.logging.Logger;

/**
 * Implementação do padrão Decorator para adicionar funcionalidades de log
 * ao serviço de avaliações sem modificar sua implementação original.
 */
@Component
@Primary // Garante que este decorator seja injetado em vez da implementação original
public class AvaliacaoServiceDecorator implements AvaliacaoService {

    private static final Logger logger = Logger.getLogger(AvaliacaoServiceDecorator.class.getName());
    private final AvaliacaoService avaliacaoService;
    private final DateTimeFormatter formatter = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss");

    @Autowired
    public AvaliacaoServiceDecorator(AvaliacaoService avaliacaoService) {
        // Injeta a implementação real que será decorada
        this.avaliacaoService = avaliacaoService;
    }

    @Override
    public List<Map<String, Object>> buscarAvaliacoesPorPreceptor(Long idPreceptor) {
        // Adiciona comportamento antes da chamada do mu00e9todo original
        String timestamp = LocalDateTime.now().format(formatter);
        logger.info(timestamp + " - Buscando avaliações para o preceptor ID: " + idPreceptor);
        
        // Chama o método original
        List<Map<String, Object>> avaliacoes = avaliacaoService.buscarAvaliacoesPorPreceptor(idPreceptor);
        
        // Adiciona comportamento após a chamada do método original
        logger.info(timestamp + " - Encontradas " + (avaliacoes != null ? avaliacoes.size() : 0) + 
                   " avaliações para o preceptor ID: " + idPreceptor);
        
        return avaliacoes;
    }

    @Override
    public boolean criarAvaliacao(Long idAluno, Long idPreceptor, Long idModulo, Long idSubgrupo,
                               Integer notaConhecimento, Integer notaHabilidades, Integer notaAtitudes,
                               String observacoes) {
        // Adiciona comportamento antes da chamada do mu00e9todo original
        String timestamp = LocalDateTime.now().format(formatter);
        logger.info(timestamp + " - Criando avaliação - Preceptor ID: " + idPreceptor + 
                   ", Aluno ID: " + idAluno + ", Módulo ID: " + idModulo);
        
        // Registra as notas atribuídas
        logger.info(timestamp + " - Notas atribuídas - Conhecimento: " + notaConhecimento + 
                   ", Habilidades: " + notaHabilidades + ", Atitudes: " + notaAtitudes);
        
        // Chama o método original
        boolean resultado = avaliacaoService.criarAvaliacao(
            idAluno, idPreceptor, idModulo, idSubgrupo,
            notaConhecimento, notaHabilidades, notaAtitudes, observacoes
        );
        
        // Adiciona comportamento após a chamada do método original
        if (resultado) {
            logger.info(timestamp + " - Avaliação criada com sucesso");
        } else {
            logger.warning(timestamp + " - Falha ao criar avaliação");
        }
        
        return resultado;
    }

    @Override
    public boolean excluirAvaliacao(Long idAvaliacao) {
        String timestamp = LocalDateTime.now().format(formatter);
        logger.info(timestamp + " - Excluindo avaliação ID: " + idAvaliacao);
        
        boolean resultado = avaliacaoService.excluirAvaliacao(idAvaliacao);
        
        if (resultado) {
            logger.info(timestamp + " - Avaliação excluída com sucesso");
        } else {
            logger.warning(timestamp + " - Falha ao excluir avaliação");
        }
        
        return resultado;
    }
    
    @Override
    public Avaliacao buscarUltimaAvaliacaoCriada(Long idAluno, Long idPreceptor, Long idModulo) {
        String timestamp = LocalDateTime.now().format(formatter);
        logger.info(timestamp + " - Buscando última avaliação - Aluno ID: " + idAluno + 
                   ", Preceptor ID: " + idPreceptor + ", Módulo ID: " + idModulo);
        
        Avaliacao avaliacao = avaliacaoService.buscarUltimaAvaliacaoCriada(idAluno, idPreceptor, idModulo);
        
        if (avaliacao != null) {
            logger.info(timestamp + " - Última avaliação encontrada: ID " + avaliacao.getIdavaliacao());
        } else {
            logger.warning(timestamp + " - Nenhuma avaliação encontrada");
        }
        
        return avaliacao;
    }
}
