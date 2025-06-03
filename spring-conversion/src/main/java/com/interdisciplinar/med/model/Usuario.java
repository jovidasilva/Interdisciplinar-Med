package com.interdisciplinar.med.model;

import jakarta.persistence.*;
import lombok.Data;

@Data
@Entity
@Table(name = "usuarios")
public class Usuario {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long idusuario;

    private String nome;
    private String email;
    private String telefone;
    private String login;
    private String senha;
    private Integer tipo;
    private Boolean ativo;
    private String registro;
    private Integer periodo;
}
