package com.interdisciplinar.med.controller;

import com.interdisciplinar.med.PadrõesDeProjeto.Estruturais.Adapter.LegacyDataAdapter;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.view.RedirectView;

import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpSession;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

@Controller
public class PhpForwardController {
    
    private static final Logger logger = Logger.getLogger(PhpForwardController.class.getName());
    
    @Value("${php.server.url:http://localhost}")
    private String phpServerUrl;
    
    @Autowired
    private LegacyDataAdapter legacyDataAdapter;
    
    @GetMapping("/index.php")
    public RedirectView indexPhp() {
        return new RedirectView(phpServerUrl + "/Interdisciplinar-Med/index.php");
    }
    
    @GetMapping("/pages/aluno/{page}.php")
    public RedirectView alunoPhpPage(@PathVariable String page, HttpServletRequest request, HttpSession session) {
        String queryString = request.getQueryString() != null ? "?" + request.getQueryString() : "";
        
        // Usar o adaptador para converter dados da sessão para o formato PHP se necessário
        try {
            // Obter dados da sessão que podem ser necessários para o sistema PHP
            Map<String, Object> sessionData = new HashMap<>();
            if (session.getAttribute("idusuario") != null) {
                sessionData.put("idusuario", session.getAttribute("idusuario"));
            }
            if (session.getAttribute("nome") != null) {
                sessionData.put("nome", session.getAttribute("nome"));
            }
            if (session.getAttribute("tipo") != null) {
                sessionData.put("tipo", session.getAttribute("tipo"));
            }
            if (session.getAttribute("login") != null) {
                sessionData.put("login", session.getAttribute("login"));
            }
            
            // Converter dados do formato Spring para PHP
            Map<String, Object> phpData = legacyDataAdapter.adaptFromSpringToPhp(sessionData);
            
            // Aqui você poderia fazer algo com os dados convertidos, como
            // passá-los para o sistema PHP via parâmetros de URL ou cookies
            // Por enquanto, apenas logamos para demonstrar o uso
            logger.info("Dados convertidos para formato PHP: " + phpData);
        } catch (Exception e) {
            logger.log(Level.WARNING, "Erro ao usar LegacyDataAdapter", e);
        }
        
        return new RedirectView(phpServerUrl + "/Interdisciplinar-Med/pages/aluno/" + page + ".php" + queryString);
    }
    
    @GetMapping("/pages/preceptor/{page}.php")
    public RedirectView preceptorPhpPage(@PathVariable String page, HttpServletRequest request, HttpSession session) {
        String queryString = request.getQueryString() != null ? "?" + request.getQueryString() : "";
        
        // Usar o adaptador para converter dados da sessão para o formato PHP se necessário
        try {
            // Obter dados da sessão que podem ser necessários para o sistema PHP
            Map<String, Object> sessionData = new HashMap<>();
            if (session.getAttribute("idusuario") != null) {
                sessionData.put("idusuario", session.getAttribute("idusuario"));
            }
            if (session.getAttribute("nome") != null) {
                sessionData.put("nome", session.getAttribute("nome"));
            }
            if (session.getAttribute("tipo") != null) {
                sessionData.put("tipo", session.getAttribute("tipo"));
            }
            if (session.getAttribute("login") != null) {
                sessionData.put("login", session.getAttribute("login"));
            }
            
            // Converter dados do formato Spring para PHP
            Map<String, Object> phpData = legacyDataAdapter.adaptFromSpringToPhp(sessionData);
            
            // Aqui você poderia fazer algo com os dados convertidos, como
            // passá-los para o sistema PHP via parâmetros de URL ou cookies
            logger.info("Dados convertidos para formato PHP: " + phpData);
        } catch (Exception e) {
            logger.log(Level.WARNING, "Erro ao usar LegacyDataAdapter", e);
        }
        
        return new RedirectView(phpServerUrl + "/Interdisciplinar-Med/pages/preceptor/" + page + ".php" + queryString);
    }
    
    @GetMapping("/cadastro_e_login/{page}.php")
    public RedirectView loginPhpPage(@PathVariable String page, HttpServletRequest request) {
        String queryString = request.getQueryString() != null ? "?" + request.getQueryString() : "";
        return new RedirectView(phpServerUrl + "/Interdisciplinar-Med/cadastro_e_login/" + page + ".php" + queryString);
    }
    
    /**
     * Método para receber dados do PHP e convertê-los para o formato Spring
     */
    @PostMapping("/api/php-data")
    public String receivePhpData(@RequestParam Map<String, String> allParams, HttpSession session) {
        try {
            // Converter os parâmetros para um mapa de objetos
            Map<String, Object> phpData = new HashMap<>();
            for (Map.Entry<String, String> entry : allParams.entrySet()) {
                phpData.put(entry.getKey(), entry.getValue());
            }
            
            // Usar o adaptador para converter dados do formato PHP para Spring
            Map<String, Object> springData = legacyDataAdapter.adaptFromPhpToSpring(phpData);
            
            // Armazenar os dados convertidos na sessão ou fazer outras operações
            if (springData.containsKey("idusuario")) {
                session.setAttribute("idusuario", springData.get("idusuario"));
            }
            if (springData.containsKey("nome")) {
                session.setAttribute("nome", springData.get("nome"));
            }
            if (springData.containsKey("tipo")) {
                session.setAttribute("tipo", springData.get("tipo"));
            }
            
            logger.info("Dados convertidos do formato PHP para Spring: " + springData);
            
            // Redirecionar com base no tipo de usuário
            if (springData.containsKey("tipo")) {
                Integer tipo = (Integer) springData.get("tipo");
                if (tipo == 0) {
                    return "redirect:/pages/aluno/home";
                } else if (tipo == 1) {
                    return "redirect:/pages/preceptor/home";
                }
            }
            
            return "redirect:/";
        } catch (Exception e) {
            logger.log(Level.WARNING, "Erro ao processar dados do PHP", e);
            return "redirect:/";
        }
    }
}
