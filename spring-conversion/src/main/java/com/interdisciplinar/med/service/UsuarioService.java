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
            Usuario usuario = usuarioRepository.findByLogin(login);
            
            if (usuario != null) {
                return converterUsuarioParaMap(usuario);
            } else {
                return null;
            }
        } catch (Exception e) {
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
            Usuario usuario = usuarioRepository.findById(idUsuario).orElse(null);
            
            if (usuario != null) {
                return converterUsuarioParaMap(usuario);
            } else {
                return null;
            }
        } catch (Exception e) {
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
