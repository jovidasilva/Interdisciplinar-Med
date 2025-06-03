package com.interdisciplinar.med.controller.aluno;

import jakarta.servlet.http.HttpSession;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;

@Controller
@RequestMapping({"/pages/aluno", "/spring/pages/aluno"})
public class HorariosController {

    @GetMapping({"/horarios", "/horarios.php"})
    public String horarios(HttpSession session, Model model) {
        
        // Sem dados de exemplo, serão implementados posteriormente
        
        return "aluno/horarios";
    }
}
