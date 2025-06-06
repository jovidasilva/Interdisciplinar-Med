package com.interdisciplinar.med.PadrõesDeProjeto.Criacionais.Builder;

import com.interdisciplinar.med.model.Avaliacao;
import com.interdisciplinar.med.model.Modulo;
import com.interdisciplinar.med.model.Subgrupo;
import com.interdisciplinar.med.model.Usuario;

import java.time.LocalDate;
import java.util.Objects;

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
     * @throws IllegalArgumentException se o aluno for inválido
     */
    public AvaliacaoBuilder comAluno(Usuario aluno) {
        Objects.requireNonNull(aluno, "Aluno não pode ser nulo");
        if (aluno.getTipo() != 0) { // 0 = TIPO_ALUNO
            throw new IllegalArgumentException("Usuário fornecido não é um aluno");
        }
        this.avaliacao.setAluno(aluno);
        return this;
    }
    
    /**
     * Define o preceptor que está realizando a avaliação
     * @param preceptor Objeto Usuario do preceptor
     * @return AvaliacaoBuilder para encadeamento de métodos
     * @throws IllegalArgumentException se o preceptor for inválido
     */
    public AvaliacaoBuilder comPreceptor(Usuario preceptor) {
        Objects.requireNonNull(preceptor, "Preceptor não pode ser nulo");
        if (preceptor.getTipo() != 1) { // 1 = TIPO_PRECEPTOR
            throw new IllegalArgumentException("Usuário fornecido não é um preceptor");
        }
        this.avaliacao.setPreceptor(preceptor);
        return this;
    }
    
    /**
     * Define o módulo da avaliação
     * @param modulo Objeto Modulo
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder noModulo(Modulo modulo) {
        this.avaliacao.setModulo(Objects.requireNonNull(modulo, "Módulo não pode ser nulo"));
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
     * @throws IllegalArgumentException se a data for inválida
     */
    public AvaliacaoBuilder naData(LocalDate data) {
        Objects.requireNonNull(data, "Data não pode ser nula");
        if (data.isAfter(LocalDate.now())) {
            throw new IllegalArgumentException("Data não pode ser futura");
        }
        this.avaliacao.setData(data);
        return this;
    }
    
    /**
     * Define as notas da avaliação (0-10)
     * @param conhecimento Nota de conhecimento
     * @param habilidades Nota de habilidades
     * @param atitudes Nota de atitudes
     * @return AvaliacaoBuilder para encadeamento de métodos
     */
    public AvaliacaoBuilder comNotas(Integer conhecimento, Integer habilidades, Integer atitudes) {
        this.avaliacao.setNota_conhecimento(validarNota(conhecimento, "conhecimento"));
        this.avaliacao.setNota_habilidades(validarNota(habilidades, "habilidades"));
        this.avaliacao.setNota_atitudes(validarNota(atitudes, "atitudes"));
        return this;
    }
    
    /**
     * Valida se uma nota está no intervalo válido (0-10)
     */
    private Integer validarNota(Integer nota, String tipo) {
        Objects.requireNonNull(nota, "Nota de " + tipo + " não pode ser nula");
        if (nota < 0 || nota > 10) {
            throw new IllegalArgumentException("Nota de " + tipo + " deve estar entre 0 e 10");
        }
        return nota;
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
     * @throws IllegalStateException se campos obrigatórios faltarem
     */
    public Avaliacao construir() {
        validarAvaliacaoCompleta();
        return this.avaliacao;
    }
    
    /**
     * Valida se todos os campos obrigatórios foram preenchidos
     */
    private void validarAvaliacaoCompleta() {
        if (avaliacao.getAluno() == null) {
            throw new IllegalStateException("Aluno não foi definido");
        }
        if (avaliacao.getPreceptor() == null) {
            throw new IllegalStateException("Preceptor não foi definido");
        }
        if (avaliacao.getModulo() == null) {
            throw new IllegalStateException("Módulo não foi definido");
        }
        if (avaliacao.getNota_conhecimento() == null || 
            avaliacao.getNota_habilidades() == null || 
            avaliacao.getNota_atitudes() == null) {
            throw new IllegalStateException("Todas as notas devem ser definidas");
        }
    }
}
