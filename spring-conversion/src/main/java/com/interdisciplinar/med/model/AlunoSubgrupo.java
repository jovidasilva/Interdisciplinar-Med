package com.interdisciplinar.med.model;

import jakarta.persistence.*;
import lombok.Data;

@Data
@Entity
@Table(name = "alunos_subgrupos")
public class AlunoSubgrupo {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "idaluno_subgrupo")
    private Long idAlunoSubgrupo;

    @ManyToOne
    @JoinColumn(name = "idusuario")
    private Usuario aluno;

    @ManyToOne
    @JoinColumn(name = "idsubgrupo")
    private Subgrupo subgrupo;
}
