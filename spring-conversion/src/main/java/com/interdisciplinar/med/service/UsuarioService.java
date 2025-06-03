package com.interdisciplinar.med.service;

import com.interdisciplinar.med.model.Usuario;
import com.interdisciplinar.med.repository.UsuarioRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.HashMap;
import java.util.Map;

/**
 * Serviço para operações relacionadas ao usuário
 * Implementado usando JPA com compatibilidade para o código existente
 */
@Service
public class UsuarioService {

    @Autowired
    private UsuarioRepository usuarioRepository;

    /**
     * Busca informações do usuário pelo login
     * @param login Login do usuário
     * @return Map com os dados do usuário ou null se não encontrado
     */
    public Map<String, Object> buscarUsuarioPorLogin(String login) {
        try {
            // Buscar o usuário usando o repositório JPA
            Usuario usuario = usuarioRepository.findByLogin(login);
            
            if (usuario != null) {
                System.out.println("✓ Usuário encontrado no banco de dados: " + login);
                return converterUsuarioParaMap(usuario);
            } else {
                System.out.println("✗ Nenhum usuário encontrado com o login: " + login);
                return null;
            }
        } catch (Exception e) {
            System.err.println("✗ ERRO ao buscar usuário por login: " + e.getMessage());
            return null;
        }
    }

    /**
     * Busca informações do usuário pelo ID
     * @param idUsuario ID do usuário
     * @return Map com os dados do usuário ou null se não encontrado
     */
    public Map<String, Object> buscarUsuarioPorId(Long idUsuario) {
        try {
            // Buscar o usuário usando o repositório JPA
            Usuario usuario = usuarioRepository.findById(idUsuario).orElse(null);
            
            if (usuario != null) {
                System.out.println("✓ Usuário encontrado no banco de dados por ID: " + idUsuario);
                return converterUsuarioParaMap(usuario);
            } else {
                System.out.println("✗ Nenhum usuário encontrado com o ID: " + idUsuario);
                return null;
            }
        } catch (Exception e) {
            System.err.println("✗ ERRO ao buscar usuário por ID: " + e.getMessage());
            return null;
        }
    }
    
    /**
     * Converte um objeto Usuario para um Map<String, Object>
     * Isso mantém compatibilidade com o código existente que espera um Map
     */
    private Map<String, Object> converterUsuarioParaMap(Usuario usuario) {
        Map<String, Object> dadosUsuario = new HashMap<>();
        
        dadosUsuario.put("idusuario", usuario.getIdusuario());
        dadosUsuario.put("nome", usuario.getNome());
        dadosUsuario.put("email", usuario.getEmail());
        dadosUsuario.put("telefone", usuario.getTelefone());
        dadosUsuario.put("login", usuario.getLogin());
        dadosUsuario.put("tipo", usuario.getTipo());
        dadosUsuario.put("ativo", usuario.getAtivo());
        dadosUsuario.put("registro", usuario.getRegistro());
        dadosUsuario.put("periodo", usuario.getPeriodo());
        
        return dadosUsuario;
    }
}
