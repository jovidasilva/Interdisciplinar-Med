package com.interdisciplinar.med.model;

import jakarta.persistence.*;
import lombok.Data;

import java.util.List;

@Data
@Entity
@Table(name = "modulos")
public class Modulo {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long idmodulo;

    @Column(name = "nome_modulo")
    private String nomeModulo;

    private Integer periodo;

    // Relacionamentos
    @OneToMany(mappedBy = "modulo")
    private List<ModuloAluno> modulosAlunos;
}
