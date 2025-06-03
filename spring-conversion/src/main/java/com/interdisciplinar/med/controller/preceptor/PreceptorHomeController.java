package com.interdisciplinar.med.controller.preceptor;

import com.interdisciplinar.med.service.UsuarioService;
import jakarta.servlet.http.HttpSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;

import java.util.Map;

/**
 * Controlador para a página inicial de preceptores
 */
@Controller
@RequestMapping({"/pages/preceptor", "/spring/pages/preceptor"})
public class PreceptorHomeController {

    @Autowired
    private UsuarioService usuarioService;

    @GetMapping({"/home", "/home.php"})
    public String home(HttpSession session, Model model, @RequestParam(required = false) String login) {
        // Verificar se temos login disponível
        if (login != null) {
            session.setAttribute("login", login);
            
            // Carregar dados do usuário do banco de dados
            Map<String, Object> userData = usuarioService.buscarUsuarioPorLogin(login);
            if (userData != null) {
                // Definir o nome do usuário na sessão e no modelo
                String nome = (String) userData.get("nome");
                if (nome != null && !nome.isEmpty()) {
                    session.setAttribute("nome", nome);
                    model.addAttribute("nome", nome);
                    System.out.println("✓ Nome do usuário definido na home do preceptor: " + nome);
                }
                
                // Definir o tipo do usuário na sessão e no modelo
                Integer tipo = (Integer) userData.get("tipo");
                if (tipo != null) {
                    session.setAttribute("tipo", tipo);
                    model.addAttribute("tipo", tipo);
                }
            }
        } else {
            // Verificar se já temos login na sessão
            login = (String) session.getAttribute("login");
            if (login != null) {
                // Verificar se o nome já está na sessão
                if (session.getAttribute("nome") == null) {
                    // Carregar dados do usuário do banco de dados
                    Map<String, Object> userData = usuarioService.buscarUsuarioPorLogin(login);
                    if (userData != null) {
                        String nome = (String) userData.get("nome");
                        if (nome != null && !nome.isEmpty()) {
                            session.setAttribute("nome", nome);
                            model.addAttribute("nome", nome);
                            System.out.println("✓ Nome do usuário definido na home do preceptor (da sessão): " + nome);
                        }
                    }
                }
            }
        }
        
        System.out.println("***** PRECEPTOR HOME CONTROLLER CONCLUÍDO *****");
        return "preceptor/home";
    }
}
