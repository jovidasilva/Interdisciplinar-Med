package com.interdisciplinar.med.controller;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.servlet.view.RedirectView;

import jakarta.servlet.http.HttpServletRequest;

@Controller
public class PhpForwardController {
    
    @Value("${php.server.url:http://localhost}")
    private String phpServerUrl;
    
    @GetMapping("/index.php")
    public RedirectView indexPhp() {
        return new RedirectView(phpServerUrl + "/Interdisciplinar-Med/index.php");
    }
    
    @GetMapping("/pages/aluno/{page}.php")
    public RedirectView alunoPhpPage(@PathVariable String page, HttpServletRequest request) {
        String queryString = request.getQueryString() != null ? "?" + request.getQueryString() : "";
        return new RedirectView(phpServerUrl + "/Interdisciplinar-Med/pages/aluno/" + page + ".php" + queryString);
    }
    
    @GetMapping("/pages/preceptor/{page}.php")
    public RedirectView preceptorPhpPage(@PathVariable String page, HttpServletRequest request) {
        String queryString = request.getQueryString() != null ? "?" + request.getQueryString() : "";
        return new RedirectView(phpServerUrl + "/Interdisciplinar-Med/pages/preceptor/" + page + ".php" + queryString);
    }
    
    @GetMapping("/cadastro_e_login/{page}.php")
    public RedirectView loginPhpPage(@PathVariable String page, HttpServletRequest request) {
        String queryString = request.getQueryString() != null ? "?" + request.getQueryString() : "";
        return new RedirectView(phpServerUrl + "/Interdisciplinar-Med/cadastro_e_login/" + page + ".php" + queryString);
    }
}
