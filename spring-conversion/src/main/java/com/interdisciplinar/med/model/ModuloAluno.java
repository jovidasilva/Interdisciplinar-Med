package com.interdisciplinar.med.model;

import jakarta.persistence.*;
import lombok.Data;

@Data
@Entity
@Table(name = "modulos_alunos")
public class ModuloAluno {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id")
    private Long id;

    @ManyToOne
    @JoinColumn(name = "idmodulo")
    private Modulo modulo;

    @ManyToOne
    @JoinColumn(name = "idusuario")
    private Usuario aluno;
}
