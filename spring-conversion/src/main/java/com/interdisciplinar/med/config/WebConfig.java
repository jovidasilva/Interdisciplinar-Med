package com.interdisciplinar.med.config;

import org.springframework.context.annotation.Configuration;
import org.springframework.web.servlet.config.annotation.ResourceHandlerRegistry;
import org.springframework.web.servlet.config.annotation.WebMvcConfigurer;

/**
 * Configuração para recursos estáticos (CSS, JS, imagens)
 */
@Configuration
public class WebConfig implements WebMvcConfigurer {

    @Override
    public void addResourceHandlers(ResourceHandlerRegistry registry) {
        // Adicionar mapeamento para arquivos CSS
        registry.addResourceHandler("/css/**")
                .addResourceLocations("classpath:/static/css/");
        
        // Adicionar mapeamento para arquivos JS
        registry.addResourceHandler("/js/**")
                .addResourceLocations("classpath:/static/js/");
                
        // Adicionar mapeamento para imagens
        registry.addResourceHandler("/img/**")
                .addResourceLocations("classpath:/static/img/");
    }
}
