package com.interdisciplinar.med.model.prototype;

import com.interdisciplinar.med.model.Modulo;
import org.springframework.stereotype.Component;

import java.util.HashMap;
import java.util.Map;

/**
 * Implementação do padrão Prototype para criação de módulos por clonagem.
 * Este padrão permite criar novos objetos duplicando objetos existentes (prototipos),
 * evitando a necessidade de criar objetos do zero e melhorando o desempenho.
 */
@Component
public class ModuloPrototype {

    // Registro de prototipos de módulos
    private final Map<String, Modulo> prototipos = new HashMap<>();
    
    /**
     * Construtor que inicializa os prototipos padrão
     */
    public ModuloPrototype() {
        // Inicializa alguns prototipos comuns
        Modulo moduloClinica = new Modulo();
        moduloClinica.setNomeModulo("Clínica Médica");
        
        Modulo moduloPediatria = new Modulo();
        moduloPediatria.setNomeModulo("Pediatria");
        
        Modulo moduloGinecologia = new Modulo();
        moduloGinecologia.setNomeModulo("Ginecologia e Obstetrícia");
        
        Modulo moduloCirurgia = new Modulo();
        moduloCirurgia.setNomeModulo("Cirurgia Geral");
        
        // Registra os prototipos
        prototipos.put("clinica", moduloClinica);
        prototipos.put("pediatria", moduloPediatria);
        prototipos.put("ginecologia", moduloGinecologia);
        prototipos.put("cirurgia", moduloCirurgia);
    }
    
    /**
     * Registra um novo prototipo de módulo
     * @param chave Chave para identificar o prototipo
     * @param modulo Objeto Modulo a ser usado como prototipo
     */
    public void registrarPrototipo(String chave, Modulo modulo) {
        prototipos.put(chave, modulo);
    }
    
    /**
     * Cria um novo módulo clonando um prototipo existente
     * @param chave Chave do prototipo a ser clonado
     * @return Novo objeto Modulo clonado do prototipo
     */
    public Modulo criarModulo(String chave) {
        Modulo prototipo = prototipos.get(chave);
        
        if (prototipo == null) {
            throw new IllegalArgumentException("Prototipo de módulo não encontrado: " + chave);
        }
        
        // Clona o prototipo
        return clonarModulo(prototipo);
    }
    
    /**
     * Clona um módulo existente
     * @param original Modulo original a ser clonado
     * @return Novo objeto Modulo clonado
     */
    public Modulo clonarModulo(Modulo original) {
        // Cria um novo objeto Modulo com os mesmos atributos
        Modulo clone = new Modulo();
        
        // Copia os atributos do original para o clone
        // Não copiamos o ID, pois o clone é um novo objeto
        clone.setNomeModulo(original.getNomeModulo());
        clone.setPeriodo(original.getPeriodo());
        
        return clone;
    }
    
    /**
     * Cria um módulo personalizado baseado em um prototipo
     * @param chave Chave do prototipo base
     * @param periodo Período do módulo
     * @param sufixoNome Sufixo a ser adicionado ao nome do módulo
     * @return Novo objeto Modulo personalizado
     */
    public Modulo criarModuloPersonalizado(String chave, Integer periodo, String sufixoNome) {
        Modulo modulo = criarModulo(chave);
        
        // Personaliza o módulo
        modulo.setPeriodo(periodo);
        
        if (sufixoNome != null && !sufixoNome.isEmpty()) {
            modulo.setNomeModulo(modulo.getNomeModulo() + " - " + sufixoNome);
        }
        
        return modulo;
    }
}
