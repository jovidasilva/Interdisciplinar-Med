package com.interdisciplinar.med.controller.aluno;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;

import com.interdisciplinar.med.service.UsuarioService;

import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpSession;

import java.util.Map;

@Controller
@RequestMapping({"/pages/aluno", "/spring/pages/aluno"})
public class HomeController {
    
    @Autowired
    private UsuarioService usuarioService;
    
    @GetMapping({"/home", "/home.php"})
    public String home(Model model, HttpServletRequest request,
                      @RequestParam(required = false) String login,
                      @RequestParam(required = false) String userid) {
        
        HttpSession session = request.getSession(true);
        if (login != null) {
            session.setAttribute("login", login);
            
            // Carregar dados do usuário do banco de dados
            Map<String, Object> userData = usuarioService.buscarUsuarioPorLogin(login);
            if (userData != null) {
                String nome = (String) userData.get("nome");
                if (nome != null && !nome.isEmpty()) {
                    session.setAttribute("nome", nome);
                    model.addAttribute("nome", nome);
                }
                
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
                if (session.getAttribute("nome") == null) {
                    // Carregar dados do usuário do banco de dados
                    Map<String, Object> userData = usuarioService.buscarUsuarioPorLogin(login);
                    if (userData != null) {
                        String nome = (String) userData.get("nome");
                        if (nome != null && !nome.isEmpty()) {
                            session.setAttribute("nome", nome);
                            model.addAttribute("nome", nome);
                        }
                    }
                }
            }
        }
        
        return "aluno/home";
    }
}
