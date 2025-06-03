package com.interdisciplinar.med.controller.aluno;

import com.interdisciplinar.med.model.Usuario;
import com.interdisciplinar.med.repository.UsuarioRepository;
import com.interdisciplinar.med.service.UsuarioService;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.security.crypto.bcrypt.BCrypt;
import org.springframework.stereotype.Controller;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import jakarta.servlet.http.HttpSession;

import java.util.Map;

@Controller
@RequestMapping({"/includes", "/spring/includes"})
public class AlterarDadosController {

    @Autowired
    private UsuarioRepository usuarioRepository;
    
    @Autowired
    private UsuarioService usuarioService;

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
        boolean sucesso = false;
        
        try {
            // Buscar o usuário no banco de dados
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
                        
                        // Atualizar na sessão
                        session.setAttribute("email", email);
                        session.setAttribute("telefone", telefone);
                        
                        mensagem = "Dados de contato atualizados com sucesso!";
                        sucesso = true;
                    } else {
                        mensagem = "Erro ao atualizar dados de contato. Verifique os campos.";
                    }
                    break;
                    
                case "alterar_login":
                    if (loginAntigo != null && loginNovo != null) {
                        // Verificar se o login antigo está correto
                        if (usuario.getLogin().equals(loginAntigo)) {
                            // Verificar se o novo login já existe
                            Usuario usuarioExistente = usuarioRepository.findByLogin(loginNovo);
                            if (usuarioExistente != null && !usuarioExistente.getIdusuario().equals(idUsuario)) {
                                mensagem = "Este login já está sendo usado por outro usuário.";
                            } else {
                                // Atualizar login no banco de dados
                                usuario.setLogin(loginNovo);
                                usuarioRepository.save(usuario);
                                
                                // Atualizar na sessão
                                session.setAttribute("login", loginNovo);
                                
                                mensagem = "Login alterado com sucesso!";
                                sucesso = true;
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
                        // Verificar se a senha antiga está correta
                        // Imprimir informações para debug (remover em produção)
                        System.out.println("Verificando senha para usuário: " + usuario.getLogin());
                        
                        // Verificar se a senha está armazenada com hash BCrypt
                        boolean senhaCorreta = false;
                        if (usuario.getSenha() != null) {
                            if (usuario.getSenha().startsWith("$2a$") || usuario.getSenha().startsWith("$2b$") || usuario.getSenha().startsWith("$2y$")) {
                                // A senha está no formato BCrypt, usar BCrypt.checkpw
                                senhaCorreta = BCrypt.checkpw(senhaAntiga, usuario.getSenha());
                                System.out.println("Verificando senha com BCrypt: " + (senhaCorreta ? "Correta" : "Incorreta"));
                            } else {
                                // Comparação direta (para senhas antigas que não usam BCrypt)
                                senhaCorreta = usuario.getSenha().trim().equals(senhaAntiga.trim());
                                System.out.println("Verificando senha com comparação direta: " + (senhaCorreta ? "Correta" : "Incorreta"));
                            }
                        }
                        
                        if (senhaCorreta) {
                            // Verificar se as senhas novas coincidem
                            if (senhaNova.equals(senhaNovaConf)) {
                                // Gerar hash BCrypt para a nova senha
                                String senhaHash = BCrypt.hashpw(senhaNova, BCrypt.gensalt(12));
                                
                                // Atualizar senha no banco de dados com o hash BCrypt
                                usuario.setSenha(senhaHash);
                                usuarioRepository.save(usuario);
                                
                                // Verificar se a senha foi realmente salva
                                System.out.println("✓ Senha alterada para o usuário: " + usuario.getLogin());
                                System.out.println("✓ Hash da nova senha: " + senhaHash.substring(0, 10) + "...");
                                
                                // Forçar o flush para garantir que as alterações sejam persistidas imediatamente
                                usuarioRepository.flush();
                                
                                mensagem = "Senha alterada com sucesso!";
                                sucesso = true;
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
        
        // Adicionar mensagem à sessão para exibição na página de perfil
        session.setAttribute("msg", mensagem);
        
        // Redirecionar de volta para a página de perfil
        return "redirect:/includes/perfil";
    }
}