package com.interdisciplinar.med.PadrõesDeProjeto.Estruturais.Adapter;

import org.springframework.stereotype.Component;

import java.util.HashMap;
import java.util.Map;

/**
 * Implementação do padrão Adapter para compatibilidade com o sistema legado PHP.
 * Este adaptador converte dados entre os formatos usados pelo sistema PHP e o sistema Spring.
 */
@Component
public class LegacyDataAdapter {

    /**
     * Converte um mapa de dados do formato PHP para o formato usado no Spring
     * @param phpData Mapa de dados no formato PHP
     * @return Mapa de dados no formato Spring
     */
    public Map<String, Object> adaptFromPhpToSpring(Map<String, Object> phpData) {
        if (phpData == null) {
            return null;
        }
        
        Map<String, Object> springData = new HashMap<>();
        
        // Adaptação de nomes de campos
        // No PHP: id_usuario, no Spring: idusuario
        if (phpData.containsKey("id_usuario")) {
            springData.put("idusuario", phpData.get("id_usuario"));
        }
        
        // Adaptação de campos de usuário
        mapField(phpData, springData, "nome", "nome");
        mapField(phpData, springData, "email", "email");
        mapField(phpData, springData, "telefone", "telefone");
        mapField(phpData, springData, "login", "login");
        mapField(phpData, springData, "tipo", "tipo");
        mapField(phpData, springData, "ativo", "ativo");
        mapField(phpData, springData, "registro", "registro");
        mapField(phpData, springData, "periodo", "periodo");
        
        // Adaptação de campos de módulo
        if (phpData.containsKey("id_modulo")) {
            springData.put("idmodulo", phpData.get("id_modulo"));
        }
        mapField(phpData, springData, "nome_modulo", "nomeModulo");
        
        // Adaptação de campos de avaliação
        if (phpData.containsKey("id_avaliacao")) {
            springData.put("idavaliacao", phpData.get("id_avaliacao"));
        }
        
        // Conversão de tipos de dados
        // No PHP, valores booleanos podem vir como strings "0" ou "1"
        if (phpData.containsKey("ativo") && phpData.get("ativo") instanceof String) {
            String ativoStr = (String) phpData.get("ativo");
            springData.put("ativo", "1".equals(ativoStr));
        }
        
        return springData;
    }
    
    /**
     * Converte um mapa de dados do formato Spring para o formato usado no PHP
     * @param springData Mapa de dados no formato Spring
     * @return Mapa de dados no formato PHP
     */
    public Map<String, Object> adaptFromSpringToPhp(Map<String, Object> springData) {
        if (springData == null) {
            return null;
        }
        
        Map<String, Object> phpData = new HashMap<>();
        
        // Adaptação de nomes de campos
        // No Spring: idusuario, no PHP: id_usuario
        if (springData.containsKey("idusuario")) {
            phpData.put("id_usuario", springData.get("idusuario"));
        }
        
        // Adaptação de campos de usuário
        mapField(springData, phpData, "nome", "nome");
        mapField(springData, phpData, "email", "email");
        mapField(springData, phpData, "telefone", "telefone");
        mapField(springData, phpData, "login", "login");
        mapField(springData, phpData, "tipo", "tipo");
        mapField(springData, phpData, "ativo", "ativo");
        mapField(springData, phpData, "registro", "registro");
        mapField(springData, phpData, "periodo", "periodo");
        
        // Adaptação de campos de módulo
        if (springData.containsKey("idmodulo")) {
            phpData.put("id_modulo", springData.get("idmodulo"));
        }
        if (springData.containsKey("nomeModulo")) {
            phpData.put("nome_modulo", springData.get("nomeModulo"));
        }
        
        // Adaptação de campos de avaliação
        if (springData.containsKey("idavaliacao")) {
            phpData.put("id_avaliacao", springData.get("idavaliacao"));
        }
        
        // Conversão de tipos de dados
        // No PHP, valores booleanos são representados como strings "0" ou "1"
        if (springData.containsKey("ativo") && springData.get("ativo") instanceof Boolean) {
            Boolean ativo = (Boolean) springData.get("ativo");
            phpData.put("ativo", ativo ? "1" : "0");
        }
        
        return phpData;
    }
    
    /**
     * Método auxiliar para mapear campos entre os mapas
     */
    private void mapField(Map<String, Object> source, Map<String, Object> target, String sourceKey, String targetKey) {
        if (source.containsKey(sourceKey)) {
            target.put(targetKey, source.get(sourceKey));
        }
    }
}
