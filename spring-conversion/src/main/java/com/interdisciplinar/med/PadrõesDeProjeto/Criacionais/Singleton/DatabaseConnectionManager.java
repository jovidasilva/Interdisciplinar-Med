package com.interdisciplinar.med.PadrõesDeProjeto.Criacionais.Singleton;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Scope;
import org.springframework.stereotype.Component;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Implementação do padrão Singleton para gerenciar conexões com o banco de dados.
 * Esta classe garante que apenas uma instância seja criada e fornece um ponto
 * global de acesso a ela.
 */
@Component
@Scope("singleton")
public class DatabaseConnectionManager {

    private static final Logger logger = Logger.getLogger(DatabaseConnectionManager.class.getName());
    private final DataSource dataSource;

    @Autowired
    public DatabaseConnectionManager(DataSource dataSource) {
        this.dataSource = dataSource;
        logger.info("DatabaseConnectionManager inicializado");
    }

    /**
     * Obtém uma conexão com o banco de dados
     * @return Connection - conexão com o banco de dados
     * @throws SQLException se ocorrer um erro ao obter a conexão
     */
    public Connection getConnection() throws SQLException {
        try {
            Connection connection = dataSource.getConnection();
            logger.info("Conexão obtida com sucesso");
            return connection;
        } catch (SQLException e) {
            logger.log(Level.SEVERE, "Erro ao obter conexão com o banco de dados", e);
            throw e;
        }
    }

    /**
     * Fecha uma conexão com o banco de dados de forma segura
     * @param connection Conexão a ser fechada
     */
    public void closeConnection(Connection connection) {
        if (connection != null) {
            try {
                connection.close();
                logger.info("Conexão fechada com sucesso");
            } catch (SQLException e) {
                logger.log(Level.WARNING, "Erro ao fechar conexão com o banco de dados", e);
            }
        }
    }
}
