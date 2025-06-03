package com.interdisciplinar.med.model;

import jakarta.persistence.*;
import lombok.Data;

import java.time.LocalDate;

@Data
@Entity
@Table(name = "avaliacoes")
public class Avaliacao {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long idavaliacao;
    
    @ManyToOne
    @JoinColumn(name = "idaluno")
    private Usuario aluno;
    
    @ManyToOne
    @JoinColumn(name = "idpreceptor")
    private Usuario preceptor;
    
    @ManyToOne
    @JoinColumn(name = "idmodulo")
    private Modulo modulo;
    
    @ManyToOne
    @JoinColumn(name = "idsubgrupo")
    private Subgrupo subgrupo;
    
    private LocalDate data;
    private Integer nota_conhecimento;
    private Integer nota_habilidades;
    private Integer nota_atitudes;
    private String observacoes;
    private Boolean finalizada;
}