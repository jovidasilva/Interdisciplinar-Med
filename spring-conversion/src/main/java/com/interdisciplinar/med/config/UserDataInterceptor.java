package com.interdisciplinar.med.config;

import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;
import org.springframework.web.context.request.RequestContextHolder;
import org.springframework.web.context.request.ServletRequestAttributes;
import org.springframework.web.servlet.HandlerInterceptor;
import org.springframework.web.servlet.ModelAndView;

import com.interdisciplinar.med.service.UsuarioService;

import java.util.Map;

/**
 * Interceptor para buscar e adicionar dados do usuário ao modelo em todas as requisições.
 * Isso garante que o nome do usuário seja exibido na navbar em todas as páginas.
 */
@Component
public class UserDataInterceptor implements HandlerInterceptor {

    @Autowired
    private UsuarioService usuarioService;

    @Override
    public void postHandle(HttpServletRequest request, HttpServletResponse response, 
                          Object handler, ModelAndView modelAndView) {
        
        if (modelAndView == null) {
            return;
        }
        
        HttpSession session = request.getSession(false);
        if (session != null) {
            if (session.getAttribute("tipo") != null) {
                Integer tipo = (Integer) session.getAttribute("tipo");
                modelAndView.addObject("tipo", tipo);
            }
            
            if (session.getAttribute("nome") != null) {
                String nome = (String) session.getAttribute("nome");
                modelAndView.addObject("nome", nome);
            }
        }
        
        boolean nomeJaDefinido = modelAndView.getModel().containsKey("nome") && 
                                modelAndView.getModel().get("nome") != null && 
                                !modelAndView.getModel().get("nome").toString().isEmpty() &&
                                !modelAndView.getModel().get("nome").toString().equals("preceptor");
        
        if (nomeJaDefinido) {
            return;
        }
        
        String login = request.getParameter("login");
        if (login != null && !login.isEmpty()) {
            buscarDadosUsuario(login, modelAndView);
            return;
        }
        
        if (session != null && session.getAttribute("login") != null) {
            login = (String) session.getAttribute("login");
            buscarDadosUsuario(login, modelAndView);
            return;
        }
        
        modelAndView.addObject("nome", "");
    }
    
    private void buscarDadosUsuario(String login, ModelAndView modelAndView) {
        try {
            HttpServletRequest request = ((ServletRequestAttributes) RequestContextHolder.getRequestAttributes()).getRequest();
            HttpSession session = request.getSession(true);
            
            Map<String, Object> userData = usuarioService.buscarUsuarioPorLogin(login);
            if (userData != null && userData.containsKey("nome")) {
                String nome = (String) userData.get("nome");
                
                if (nome == null || nome.trim().isEmpty() || nome.equalsIgnoreCase("preceptor") || 
                    nome.equalsIgnoreCase("aluno") || nome.equalsIgnoreCase("coordenador")) {
                    
                    if (userData.containsKey("nome_completo") && userData.get("nome_completo") != null && 
                        !((String)userData.get("nome_completo")).trim().isEmpty()) {
                        nome = (String) userData.get("nome_completo");
                    } else {
                        nome = login;
                    }
                }
                
                modelAndView.addObject("nome", nome);
                session.setAttribute("nome", nome);
                
                if (userData.containsKey("tipo")) {
                    Integer tipo = (Integer) userData.get("tipo");
                    modelAndView.addObject("tipo", tipo);
                    session.setAttribute("tipo", tipo);
                }
                
                if (userData.containsKey("idusuario")) {
                    modelAndView.addObject("idusuario", userData.get("idusuario"));
                    session.setAttribute("idusuario", userData.get("idusuario"));
                }
                
                session.setAttribute("login", login);
            } else {
                modelAndView.addObject("nome", "");
            }
        } catch (Exception e) {
            modelAndView.addObject("nome", "");
        }
    }
}
