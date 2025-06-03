package com.interdisciplinar.med.config;

import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.config.Customizer;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;

@Configuration
@EnableWebSecurity
public class SecurityConfig {

    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder();
    }

    @Bean
    public SecurityFilterChain securityFilterChain(HttpSecurity http) throws Exception {
        // Configuração para fase de testes - permitir todos os acessos
        http
            .csrf(csrf -> csrf.disable())
            .authorizeHttpRequests(auth -> auth
                .requestMatchers("/**").permitAll()
            )
            .headers(headers -> headers.frameOptions(frameOptions -> frameOptions.disable()));
            
        return http.build();
        
        /* Configuração de segurança final (descomente depois dos testes)
        http
            .csrf(csrf -> csrf.disable())
            .authorizeHttpRequests(auth -> auth
                .requestMatchers("/pages/aluno/**").hasRole("ALUNO")
                .requestMatchers("/pages/preceptor/**").hasRole("PRECEPTOR")
                .requestMatchers("/pages/coordenacao/**").hasRole("COORDENADOR")
                .requestMatchers("/cadastro_e_login/**").permitAll()
                .requestMatchers("/index.php").permitAll()
                .anyRequest().authenticated()
            )
            .formLogin(form -> form
                .loginPage("/index.php")
                .loginProcessingUrl("/cadastro_e_login/login.php")
                .defaultSuccessUrl("/pages/aluno/home.php", true)
                .permitAll()
            )
            .logout(logout -> logout
                .logoutUrl("/logout")
                .permitAll()
            );
            
        return http.build();
        */
    }
}
