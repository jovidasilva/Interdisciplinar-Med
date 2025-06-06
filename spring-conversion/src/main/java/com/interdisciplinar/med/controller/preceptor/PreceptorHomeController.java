package com.interdisciplinar.med.controller.preceptor;

import com.interdisciplinar.med.PadrõesDeProjeto.Estruturais.Facade.PreceptorFacade;
import com.interdisciplinar.med.service.UsuarioService;
import jakarta.servlet.http.HttpSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;

import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Controlador para a página inicial de preceptores
 */
@Controller
@RequestMapping({"/pages/preceptor", "/spring/pages/preceptor"})
public class PreceptorHomeController {

    private static final Logger logger = Logger.getLogger(PreceptorHomeController.class.getName());

    @Autowired
    private UsuarioService usuarioService;
    
    @Autowired
    private PreceptorFacade preceptorFacade;

    @GetMapping({"/home", "/home.php"})
    public String home(HttpSession session, Model model, @RequestParam(required = false) String login) {
        if (login != null) {
            session.setAttribute("login", login);
            
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
                
                // Se tivermos o ID do usuário e ele for preceptor, usar o PreceptorFacade
                if (userData.containsKey("idusuario") && tipo != null && tipo == 1) {
                    Long idPreceptor = (Long) userData.get("idusuario");
                    session.setAttribute("idusuario", idPreceptor);
                    
                    try {
                        // Obter o dashboard do preceptor usando o padrão Facade
                        Map<String, Object> dashboard = preceptorFacade.obterDashboardPreceptor(idPreceptor);
                        model.addAttribute("dashboard", dashboard);
                        
                        // Adicionar componentes individuais do dashboard ao modelo
                        if (dashboard.containsKey("rodizios")) {
                            model.addAttribute("rodizios", dashboard.get("rodizios"));
                        }
                        
                        if (dashboard.containsKey("horarios")) {
                            model.addAttribute("horarios", dashboard.get("horarios"));
                        }
                        
                        if (dashboard.containsKey("estatisticas")) {
                            model.addAttribute("estatisticas", dashboard.get("estatisticas"));
                        }
                    } catch (Exception e) {
                        logger.log(Level.WARNING, "Erro ao usar PreceptorFacade para obter dashboard", e);
                        // Continuar com o fluxo normal se a fachada falhar
                    }
                }
            }
        } else {
            login = (String) session.getAttribute("login");
            if (login != null) {
                if (session.getAttribute("nome") == null) {
                    Map<String, Object> userData = usuarioService.buscarUsuarioPorLogin(login);
                    if (userData != null) {
                        String nome = (String) userData.get("nome");
                        if (nome != null && !nome.isEmpty()) {
                            session.setAttribute("nome", nome);
                            model.addAttribute("nome", nome);
                        }
                    }
                }
                
                // Se tivermos o ID do usuário na sessão, usar o PreceptorFacade
                Long idPreceptor = (Long) session.getAttribute("idusuario");
                Integer tipo = (Integer) session.getAttribute("tipo");
                
                if (idPreceptor != null && tipo != null && tipo == 1) {
                    try {
                        // Obter o dashboard do preceptor usando o padrão Facade
                        Map<String, Object> dashboard = preceptorFacade.obterDashboardPreceptor(idPreceptor);
                        model.addAttribute("dashboard", dashboard);
                        
                        // Adicionar componentes individuais do dashboard ao modelo
                        if (dashboard.containsKey("rodizios")) {
                            model.addAttribute("rodizios", dashboard.get("rodizios"));
                        }
                        
                        if (dashboard.containsKey("horarios")) {
                            model.addAttribute("horarios", dashboard.get("horarios"));
                        }
                        
                        if (dashboard.containsKey("estatisticas")) {
                            model.addAttribute("estatisticas", dashboard.get("estatisticas"));
                        }
                    } catch (Exception e) {
                        logger.log(Level.WARNING, "Erro ao usar PreceptorFacade para obter dashboard", e);
                        // Continuar com o fluxo normal se a fachada falhar
                    }
                }
            }
        }
        
        return "preceptor/home";
    }
}
