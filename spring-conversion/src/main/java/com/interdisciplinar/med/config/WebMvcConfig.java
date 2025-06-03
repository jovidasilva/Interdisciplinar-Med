package com.interdisciplinar.med.config;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Configuration;
import org.springframework.web.servlet.config.annotation.InterceptorRegistry;
import org.springframework.web.servlet.config.annotation.WebMvcConfigurer;

/**
 * Configuração do Spring MVC para registrar interceptores e outras configurações.
 */
@Configuration
public class WebMvcConfig implements WebMvcConfigurer {

    @Autowired
    private UserDataInterceptor userDataInterceptor;

    @Override
    public void addInterceptors(InterceptorRegistry registry) {
        // Registrar o interceptor para todas as URLs
        registry.addInterceptor(userDataInterceptor);
    }
}
