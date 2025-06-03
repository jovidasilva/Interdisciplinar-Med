package com.interdisciplinar.med.model;

import jakarta.persistence.*;
import lombok.Data;

import java.util.List;

@Data
@Entity
@Table(name = "subgrupos")
public class Subgrupo {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long idsubgrupo;

    @ManyToOne
    @JoinColumn(name = "idgrupo")
    private Grupo grupo;

    @Column(name = "nome_subgrupo")
    private String nomeSubgrupo;

    @OneToMany(mappedBy = "subgrupo")
    private List<AlunoSubgrupo> alunosSubgrupos;
}
