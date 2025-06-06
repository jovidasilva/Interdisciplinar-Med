package com.interdisciplinar.med.PadrõesDeProjeto.Criacionais.FactoryMethod;

import com.interdisciplinar.med.model.Usuario;
import org.springframework.stereotype.Component;

/**
 * Implementação do padrão Factory Method para criar diferentes tipos de usuários.
 * Esta classe encapsula a lógica de criação de usuários com diferentes características.
 */
@Component
public class UsuarioFactory {

    /**
     * Tipo 0 = Aluno
     * Tipo 1 = Preceptor
     * Tipo 2/3 = Coordenação
     */
    public static final int TIPO_ALUNO = 0;
    public static final int TIPO_PRECEPTOR = 1;
    public static final int TIPO_COORDENADOR = 2;
    public static final int TIPO_ADMIN = 3;

    /**
     * Cria um usuário com base no tipo especificado
     * @param tipo Tipo de usuário (0-3)
     * @return Objeto Usuario configurado
     * @throws IllegalArgumentException se o tipo for inválido
     */
    public Usuario criarUsuario(int tipo) {
        if (tipo < TIPO_ALUNO || tipo > TIPO_ADMIN) {
            throw new IllegalArgumentException("Tipo de usuário inválido: " + tipo);
        }
        
        Usuario usuario = new Usuario();
        usuario.setTipo(tipo);
        usuario.setAtivo(true);
        
        // Configurações específicas por tipo
        switch (tipo) {
            case TIPO_ALUNO:
                usuario.setPeriodo(1);
                usuario.setRegistro(null);
                break;
            case TIPO_PRECEPTOR:
                usuario.setPeriodo(null);
                // Registro profissional definido posteriormente
                break;
            case TIPO_COORDENADOR:
            case TIPO_ADMIN:
                usuario.setPeriodo(null);
                usuario.setRegistro(null);
                break;
        }
        
        return usuario;
    }
}
