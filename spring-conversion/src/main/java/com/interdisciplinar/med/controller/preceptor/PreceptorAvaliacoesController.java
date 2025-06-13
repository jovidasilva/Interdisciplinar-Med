package com.interdisciplinar.med.controller.preceptor;

import com.interdisciplinar.med.PadrõesDeProjeto.Estruturais.Facade.PreceptorFacade;
import jakarta.servlet.http.HttpSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

@Controller
@RequestMapping({"/pages/preceptor", "/spring/pages/preceptor"})
public class PreceptorAvaliacoesController {
    @Autowired
    private PreceptorFacade preceptorFacade;

    @GetMapping("/avaliacoes")
    public String visualizarAvaliacoes(Model model, HttpSession session) {
        Long idPreceptor = (Long) session.getAttribute("idusuario");
        if (idPreceptor == null) {
            return "redirect:/login";
        }

        try {
            Map<Long, Map<String, Object>> alunosMap = preceptorFacade.obterAlunosParaAvaliacao(idPreceptor);
            model.addAttribute("alunos", new ArrayList<>(alunosMap.values()));
        } catch (Exception e) {
            e.printStackTrace();
            model.addAttribute("errorMessage", "Erro ao carregar a lista de alunos para avaliação.");
        }

        model.addAttribute("page", "lista");
        return "preceptor/avaliacoes/avaliacoes";
    }

    @GetMapping({"/realizar-avaliacao", "/avaliacoes/realizar-avaliacao"})
    public String realizarAvaliacao(@RequestParam("id_aluno") Long idAluno,
                                  @RequestParam("id_modulo") Long idModulo,
                                  @RequestParam("aluno_nome") String alunoNome,
                                  @RequestParam("modulo_nome") String moduloNome,
                                  @RequestParam(value="idsubgrupo", required = false) Long idSubgrupo,
                                  Model model, HttpSession session) {
        Long idPreceptor = (Long) session.getAttribute("idusuario");
        if (idPreceptor == null) {
            return "redirect:/login";
        }

        // Dados básicos
        model.addAttribute("aluno_nome", alunoNome);
        model.addAttribute("modulo_nome", moduloNome);
        model.addAttribute("id_aluno", idAluno);
        model.addAttribute("id_modulo", idModulo);

        // Perguntas dinâmicas
        model.addAttribute("perguntas", preceptorFacade.listarPerguntasAvaliacao());

        // Módulo já foi escolhido antes; não há seleção aqui.

        if (idSubgrupo != null) {
            model.addAttribute("idsubgrupo", idSubgrupo);
        }

        model.addAttribute("page", "realizar-avaliacao");
        return "preceptor/avaliacoes/avaliacoes";
    }

    @PostMapping({"/processar-avaliacao", "/avaliacoes/processar-avaliacao"})
    public String processarAvaliacao(@RequestParam Map<String, String> allParams,
                                     HttpSession session,
                                     RedirectAttributes redirectAttributes) {
        Long idPreceptor = (Long) session.getAttribute("idusuario");
        if (idPreceptor == null) {
            return "redirect:/login";
        }

        try {
            Long idAluno = Long.parseLong(allParams.get("id_aluno"));
            Long idModulo = Long.parseLong(allParams.get("id_modulo"));

            double soma = 0;
            int qtd = 0;
            for (var entry : allParams.entrySet()) {
                if (entry.getKey().startsWith("pergunta_")) {
                    soma += Double.parseDouble(entry.getValue());
                    qtd++;
                }
            }

            double media = qtd > 0 ? soma / qtd : 0;

            preceptorFacade.salvarAvaliacaoFinal(idPreceptor, idAluno, idModulo, media);
            redirectAttributes.addFlashAttribute("mensagemSucesso", "Avaliação realizada com sucesso!");

        } catch (NumberFormatException e) {
            e.printStackTrace();
        }

        String idSubgrupoStr = allParams.get("idsubgrupo");
        if (idSubgrupoStr != null && !idSubgrupoStr.isBlank()) {
            return "redirect:/pages/preceptor/grupos/alunos?idsubgrupo=" + idSubgrupoStr;
        }
        return "redirect:/pages/preceptor/avaliacoes";
    }

    @GetMapping({"/visualizar-avaliacao", "/avaliacoes/visualizar-avaliacao"})
    public String visualizarAvaliacao(@RequestParam("idavaliacao") Long idAvaliacao,
                                      @RequestParam(value="idsubgrupo", required = false) Long idSubgrupo,
                                      Model model, HttpSession session) {
        Long idPreceptor = (Long) session.getAttribute("idusuario");
        if (idPreceptor == null) {
            return "redirect:/login";
        }

        Map<String, Object> avaliacao = preceptorFacade.obterDetalheAvaliacao(idAvaliacao);
        List<Map<String, Object>> respostas = preceptorFacade.obterRespostasAvaliacao(idAvaliacao);

        model.addAttribute("avaliacao", avaliacao);
        model.addAttribute("respostas", respostas);
        if (idSubgrupo != null) model.addAttribute("idsubgrupo", idSubgrupo);

        model.addAttribute("page", "visualizar-avaliacao");
        return "preceptor/avaliacoes/avaliacoes";
    }

    // Histórico de avaliações de um aluno
    @GetMapping({"/avaliacoes/historico", "/historico-avaliacoes"})
    public String historicoAvaliacoes(@RequestParam("id_aluno") Long idAluno,
                                      @RequestParam("aluno_nome") String alunoNome,
                                      @RequestParam(value="idsubgrupo", required = false) Long idSubgrupo,
                                      Model model, HttpSession session) {
        Long idPreceptor = (Long) session.getAttribute("idusuario");
        if (idPreceptor == null) {
            return "redirect:/login";
        }

        List<Map<String, Object>> historico = preceptorFacade.listarAvaliacoesAluno(idAluno, idPreceptor);
        model.addAttribute("historico", historico);
        model.addAttribute("aluno_nome", alunoNome);
        if (idSubgrupo != null) model.addAttribute("idsubgrupo", idSubgrupo);

        model.addAttribute("page", "visualizar-avaliacao");
        return "preceptor/avaliacoes/avaliacoes";
    }
}
