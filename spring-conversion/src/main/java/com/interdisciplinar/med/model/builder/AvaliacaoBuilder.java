package com.interdisciplinar.med.model.builder;

import com.interdisciplinar.med.model.Avaliacao;
import com.interdisciplinar.med.model.Modulo;
import com.interdisciplinar.med.model.Subgrupo;
import com.interdisciplinar.med.model.Usuario;

import java.time.LocalDate;

/**
 * Implementação do padrão Builder para a criação de avaliações.
 * Este padrão facilita a criação de objetos complexos separando o processo de construção
 * da representação final do objeto.
 */
public class AvaliacaoBuilder {
    private final Avaliacao avaliacao;
    
    /**
     * Construtor que inicia a construção de uma avaliação
     */
    public AvaliacaoBuilder() {
        this.avaliacao = new Avaliacao();
        // Valores padrão
        this.avaliacao.setData(LocalDate.now());
        this.avaliacao.setFinalizada(false);
    }
    
    /**
     * Define o aluno que está sendo avaliado
     * @param aluno Objeto Usuario do aluno
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder comAluno(Usuario aluno) {
        this.avaliacao.setAluno(aluno);
        return this;
    }
    
    /**
     * Define o preceptor que está realizando a avaliação
     * @param preceptor Objeto Usuario do preceptor
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder comPreceptor(Usuario preceptor) {
        this.avaliacao.setPreceptor(preceptor);
        return this;
    }
    
    /**
     * Define o módulo da avaliação
     * @param modulo Objeto Modulo
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder noModulo(Modulo modulo) {
        this.avaliacao.setModulo(modulo);
        return this;
    }
    
    /**
     * Define o subgrupo da avaliação
     * @param subgrupo Objeto Subgrupo
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder noSubgrupo(Subgrupo subgrupo) {
        this.avaliacao.setSubgrupo(subgrupo);
        return this;
    }
    
    /**
     * Define a data da avaliação
     * @param data Data da avaliação
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder naData(LocalDate data) {
        this.avaliacao.setData(data);
        return this;
    }
    
    /**
     * Define as notas da avaliação
     * @param conhecimento Nota de conhecimento (0-10)
     * @param habilidades Nota de habilidades (0-10)
     * @param atitudes Nota de atitudes (0-10)
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder comNotas(Integer conhecimento, Integer habilidades, Integer atitudes) {
        this.avaliacao.setNota_conhecimento(conhecimento);
        this.avaliacao.setNota_habilidades(habilidades);
        this.avaliacao.setNota_atitudes(atitudes);
        return this;
    }
    
    /**
     * Define as observações da avaliação
     * @param observacoes Texto com observações
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder comObservacoes(String observacoes) {
        this.avaliacao.setObservacoes(observacoes);
        return this;
    }
    
    /**
     * Marca a avaliação como finalizada
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder finalizada() {
        this.avaliacao.setFinalizada(true);
        return this;
    }
    
    /**
     * Constrói e retorna o objeto Avaliacao
     * @return Objeto Avaliacao construído
     */
    public Avaliacao construir() {
        return this.avaliacao;
    }
}
