package com.interdisciplinar.med.controller;

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

/**
 * Controlador de perfil unificado para todos os tipos de usuários
 * (alunos, preceptores, coordenadores)
 */
@Controller
@RequestMapping({"/includes", "/spring/includes"})
public class PerfilController {

    @Autowired
    private UsuarioService usuarioService;

    @GetMapping({"/perfil", "/perfil.php"})
    public String perfil(HttpSession session, Model model, HttpServletRequest request,
                      @RequestParam(required = false) String login,
                      @RequestParam(required = false) String userid,
                      @RequestParam(required = false) String page) {
        
        // Tentar obter login do parâmetro, da sessão ou do request
        if (login == null) {
            login = (String) session.getAttribute("login");
        }
        
        // Se ainda não temos login, redirecionar para login
        if (login == null) {
            return "redirect:/login";
        }
        
        try {
            // Buscar dados do usuário no banco de dados
            Map<String, Object> userData = usuarioService.buscarUsuarioPorLogin(login);
            
            if (userData != null) {
                // Adicionar dados do usuário ao modelo
                model.addAttribute("nome", userData.get("nome"));
                model.addAttribute("email", userData.get("email"));
                model.addAttribute("telefone", userData.get("telefone"));
                model.addAttribute("login", userData.get("login"));
                model.addAttribute("idusuario", userData.get("idusuario"));
                model.addAttribute("tipo", userData.get("tipo"));
                
                // Direcionar para a página específica com base no tipo de usuário
                Integer tipo = (Integer) userData.get("tipo");
                if (tipo != null) {
                    if (tipo == 0) { // Aluno
                        return "aluno/perfil";
                    } else if (tipo == 1) { // Preceptor
                        return "preceptor/perfil";
                    } else if (tipo == 2 || tipo == 3) { // Coordenação
                        // Futuramente pode ser implementada uma página específica para coordenação
                        return "preceptor/perfil"; // Por enquanto, usa a mesma do preceptor
                    }
                }
            }
        } catch (Exception e) {
            e.printStackTrace(); // Log de erro
        }
        
        // Verificar se há mensagem na sessão
        if (session.getAttribute("msg") != null) {
            model.addAttribute("msg", session.getAttribute("msg"));
            session.removeAttribute("msg"); // Remover mensagem após exibição
        }
        
        // Tratar parâmetro de página para diferentes visões
        if (page != null && page.equals("editar")) {
            model.addAttribute("editMode", true);
        }
        
        // Fallback para uma página genérica caso não consiga determinar o tipo
        return "redirect:/login";
    }
}
