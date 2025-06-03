package com.interdisciplinar.med.model;

import jakarta.persistence.*;
import lombok.Data;

import java.util.List;

@Data
@Entity
@Table(name = "grupos")
public class Grupo {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long idgrupo;

    @Column(name = "nome_grupo")
    private String nomeGrupo;

    @OneToMany(mappedBy = "grupo", cascade = CascadeType.ALL)
    private List<Subgrupo> subgrupos;
}
