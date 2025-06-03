package com.interdisciplinar.med.controller.aluno;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;

@Controller
@RequestMapping({"/pages/aluno", "/spring/pages/aluno"})
public class RodiziosController {

    @GetMapping({"/rodizios", "/rodizios.php"})
    public String rodizios() {
        // Não precisamos mais adicionar os atributos da sessão manualmente
        // O SessionAttributesInterceptor fará isso automaticamente
        return "aluno/rodizios";
    }
}
