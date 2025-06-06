package com.interdisciplinar.med.controller.aluno;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;

@Controller
@RequestMapping({"/pages/aluno", "/spring/pages/aluno"})
public class RodiziosController {

    @GetMapping({"/rodizios", "/rodizios.php"})
    public String rodizios() {
        return "aluno/rodizios";
    }
}
