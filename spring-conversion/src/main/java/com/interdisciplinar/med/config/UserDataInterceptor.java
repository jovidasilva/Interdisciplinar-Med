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
        
        // Só processamos se houver um ModelAndView
        if (modelAndView == null) {
            return;
        }
        
        // Log para debug
        System.out.println("✓ UserDataInterceptor processando: " + request.getRequestURI());
        
        // Sempre copiar atributos da sessão para o modelo
        HttpSession session = request.getSession(false);
        if (session != null) {
            // Copiar tipo para o modelo para garantir redirecionamento correto
            if (session.getAttribute("tipo") != null) {
                Integer tipo = (Integer) session.getAttribute("tipo");
                modelAndView.addObject("tipo", tipo);
                System.out.println("✓ Tipo do usuário na sessão: " + tipo);
            }
            
            // Copiar nome se estiver na sessão
            if (session.getAttribute("nome") != null) {
                String nome = (String) session.getAttribute("nome");
                modelAndView.addObject("nome", nome);
                System.out.println("✓ Nome do usuário na sessão: " + nome);
            }
        }
        
        // Verificar se já existe um nome no modelo
        boolean nomeJaDefinido = modelAndView.getModel().containsKey("nome") && 
                                modelAndView.getModel().get("nome") != null && 
                                !modelAndView.getModel().get("nome").toString().isEmpty() &&
                                !modelAndView.getModel().get("nome").toString().equals("preceptor");
        
        if (nomeJaDefinido) {
            System.out.println("✓ Nome já definido no modelo: " + modelAndView.getModel().get("nome"));
        }
        
        if (nomeJaDefinido) {
            // Nome já foi definido, não precisamos buscá-lo do banco
            return;
        }
        
        // Verificar parâmetros de URL primeiro (prioridade)
        String login = request.getParameter("login");
        if (login != null && !login.isEmpty()) {
            buscarDadosUsuario(login, modelAndView);
            return;
        }
        
        // Verificar na sessão se tem login mas ainda não tem nome
        if (session != null && session.getAttribute("login") != null) {
            login = (String) session.getAttribute("login");
            buscarDadosUsuario(login, modelAndView);
            return;
        }
        
        // Nenhum login encontrado, usar string vazia para o nome
        modelAndView.addObject("nome", "");
    }
    
    private void buscarDadosUsuario(String login, ModelAndView modelAndView) {
        try {
            // Obter a requisição atual
            HttpServletRequest request = ((ServletRequestAttributes) RequestContextHolder.getRequestAttributes()).getRequest();
            HttpSession session = request.getSession(true);
            
            // Buscar dados do usuário do banco de dados
            Map<String, Object> userData = usuarioService.buscarUsuarioPorLogin(login);
            if (userData != null && userData.containsKey("nome")) {
                String nome = (String) userData.get("nome");
                
                // Log para debug
                System.out.println("✓ Dados do usuário carregados do banco para login: " + login);
                System.out.println("✓ Nome do usuário no banco: " + nome);
                
                // Verificar se o nome é válido
                if (nome == null || nome.trim().isEmpty() || nome.equalsIgnoreCase("preceptor") || 
                    nome.equalsIgnoreCase("aluno") || nome.equalsIgnoreCase("coordenador")) {
                    System.out.println("❗ Nome inválido ou genérico encontrado: " + nome);
                    
                    // Tentar obter o nome completo do banco
                    if (userData.containsKey("nome_completo") && userData.get("nome_completo") != null && 
                        !((String)userData.get("nome_completo")).trim().isEmpty()) {
                        nome = (String) userData.get("nome_completo");
                        System.out.println("✓ Usando nome_completo alternativo: " + nome);
                    } else {
                        // Se ainda não temos um nome válido, usar o login como nome
                        nome = login;
                        System.out.println("✓ Usando login como nome alternativo: " + nome);
                    }
                }
                
                // Adicionar ao modelo E à sessão para persistência
                modelAndView.addObject("nome", nome);
                session.setAttribute("nome", nome);
                System.out.println("✓ Nome definido no modelo e sessão: " + nome);
                
                // Adicionar outros dados importantes à sessão e ao modelo
                if (userData.containsKey("tipo")) {
                    Integer tipo = (Integer) userData.get("tipo");
                    modelAndView.addObject("tipo", tipo);
                    session.setAttribute("tipo", tipo);
                    System.out.println("✓ Tipo definido no modelo e sessão: " + tipo);
                }
                
                if (userData.containsKey("idusuario")) {
                    modelAndView.addObject("idusuario", userData.get("idusuario"));
                    session.setAttribute("idusuario", userData.get("idusuario"));
                }
                
                // Salvar o login na sessão
                session.setAttribute("login", login);
                
                System.out.println("✓ Usuário encontrado no banco de dados: " + nome);
            } else {
                modelAndView.addObject("nome", "");
            }
        } catch (Exception e) {
            // Em caso de erro, usar nome vazio
            modelAndView.addObject("nome", "");
            System.out.println("Erro ao buscar dados do usuário: " + e.getMessage());
        }
    }
}
