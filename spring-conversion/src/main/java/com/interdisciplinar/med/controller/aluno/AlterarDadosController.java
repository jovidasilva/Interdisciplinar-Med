package com.interdisciplinar.med.controller.aluno;

import com.interdisciplinar.med.model.Usuario;
import com.interdisciplinar.med.repository.UsuarioRepository;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.security.crypto.bcrypt.BCrypt;
import org.springframework.stereotype.Controller;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import jakarta.servlet.http.HttpSession;

@Controller
@RequestMapping({"/includes", "/spring/includes"})
public class AlterarDadosController {

    @Autowired
    private UsuarioRepository usuarioRepository;

    @PostMapping("/alterar-dados")
    @Transactional
    public String alterarDados(
            @RequestParam("action") String action,
            @RequestParam("idusuario") Long idUsuario,
            @RequestParam(value = "email", required = false) String email,
            @RequestParam(value = "telefone", required = false) String telefone,
            @RequestParam(value = "login_antigo", required = false) String loginAntigo,
            @RequestParam(value = "login_novo", required = false) String loginNovo,
            @RequestParam(value = "senha_antiga", required = false) String senhaAntiga,
            @RequestParam(value = "senha_nova", required = false) String senhaNova,
            @RequestParam(value = "senha_nova_conf", required = false) String senhaNovaConf,
            HttpSession session,
            RedirectAttributes redirectAttributes) {
        
        String mensagem = "";
        
        try {
            Usuario usuario = usuarioRepository.findById(idUsuario).orElse(null);
            
            if (usuario == null) {
                mensagem = "Usuário não encontrado.";
                session.setAttribute("msg", mensagem);
                return "redirect:/includes/perfil";
            }
            
            switch (action) {
                case "alterar_dados_contato":
                    if (email != null && telefone != null) {
                        // Atualizar dados de contato no banco de dados
                        usuario.setEmail(email);
                        usuario.setTelefone(telefone);
                        usuarioRepository.save(usuario);
                        
                        session.setAttribute("email", email);
                        session.setAttribute("telefone", telefone);
                        
                        mensagem = "Dados de contato atualizados com sucesso!";
                    } else {
                        mensagem = "Erro ao atualizar dados de contato. Verifique os campos.";
                    }
                    break;
                    
                case "alterar_login":
                    if (loginAntigo != null && loginNovo != null) {
                        // Verificar se o login antigo está correto
                        if (usuario.getLogin().equals(loginAntigo)) {
                            Usuario usuarioExistente = usuarioRepository.findByLogin(loginNovo);
                            if (usuarioExistente != null && !usuarioExistente.getIdusuario().equals(idUsuario)) {
                                mensagem = "Este login já está sendo usado por outro usuário.";
                            } else {
                                usuario.setLogin(loginNovo);
                                usuarioRepository.save(usuario);
                                
                                session.setAttribute("login", loginNovo);
                                
                                mensagem = "Login alterado com sucesso!";
                            }
                        } else {
                            mensagem = "Login atual incorreto.";
                        }
                    } else {
                        mensagem = "Erro ao alterar login. Verifique os campos.";
                    }
                    break;
                    
                case "alterar_senha":
                    if (senhaAntiga != null && senhaNova != null && senhaNovaConf != null) {
                        boolean senhaCorreta = false;
                        if (usuario.getSenha() != null) {
                            if (usuario.getSenha().startsWith("$2a$") || usuario.getSenha().startsWith("$2b$") || usuario.getSenha().startsWith("$2y$")) {
                                senhaCorreta = BCrypt.checkpw(senhaAntiga, usuario.getSenha());
                            } else {
                                senhaCorreta = usuario.getSenha().trim().equals(senhaAntiga.trim());
                            }
                        }
                        
                        if (senhaCorreta) {
                            if (senhaNova.equals(senhaNovaConf)) {
                                String senhaHash = BCrypt.hashpw(senhaNova, BCrypt.gensalt(12));
                                
                                usuario.setSenha(senhaHash);
                                usuarioRepository.save(usuario);
                                usuarioRepository.flush();
                                
                                mensagem = "Senha alterada com sucesso!";
                            } else {
                                mensagem = "As senhas novas não coincidem.";
                            }
                        } else {
                            mensagem = "Senha atual incorreta.";
                        }
                    } else {
                        mensagem = "Erro ao alterar senha. Verifique os campos.";
                    }
                    break;
                    
                default:
                    mensagem = "Ação desconhecida.";
                    break;
            }
        } catch (Exception e) {
            mensagem = "Erro ao processar a solicitação: " + e.getMessage();
            e.printStackTrace();
        }
        
        session.setAttribute("msg", mensagem);
        return "redirect:/includes/perfil";
    }
}