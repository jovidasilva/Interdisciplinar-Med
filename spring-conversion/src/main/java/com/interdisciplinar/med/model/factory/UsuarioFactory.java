package com.interdisciplinar.med.model.factory;

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
     * @param tipo Tipo de usuário a ser criado
     * @return Objeto Usuario configurado de acordo com o tipo
     */
    public Usuario criarUsuario(int tipo) {
        Usuario usuario = new Usuario();
        usuario.setTipo(tipo);
        usuario.setAtivo(true);
        
        // Configurações específicas para cada tipo de usuário
        switch (tipo) {
            case TIPO_ALUNO:
                configurarAluno(usuario);
                break;
            case TIPO_PRECEPTOR:
                configurarPreceptor(usuario);
                break;
            case TIPO_COORDENADOR:
            case TIPO_ADMIN:
                configurarCoordenador(usuario);
                break;
            default:
                throw new IllegalArgumentException("Tipo de usuário inválido: " + tipo);
        }
        
        return usuario;
    }
    
    /**
     * Configura um usuário como aluno
     * @param usuario Objeto Usuario a ser configurado
     */
    private void configurarAluno(Usuario usuario) {
        // Configurações específicas para alunos
        // Por exemplo, alunos precisam ter um período definido
    }
    
    /**
     * Configura um usuário como preceptor
     * @param usuario Objeto Usuario a ser configurado
     */
    private void configurarPreceptor(Usuario usuario) {
        // Configurações específicas para preceptores
        // Por exemplo, preceptores precisam ter um registro profissional
    }
    
    /**
     * Configura um usuário como coordenador ou administrador
     * @param usuario Objeto Usuario a ser configurado
     */
    private void configurarCoordenador(Usuario usuario) {
        // Configurações específicas para coordenadores/administradores
    }
}
