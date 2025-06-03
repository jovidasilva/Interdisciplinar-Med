package com.interdisciplinar.med.config;

import org.springframework.beans.factory.annotation.Autowired;
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
public class DatabaseConnectionManager {

    private static DatabaseConnectionManager instance;
    private final DataSource dataSource;
    private static final Logger logger = Logger.getLogger(DatabaseConnectionManager.class.getName());

    @Autowired
    private DatabaseConnectionManager(DataSource dataSource) {
        this.dataSource = dataSource;
        logger.info("DatabaseConnectionManager inicializado");
    }

    /**
     * Obtém a instância única do gerenciador de conexão
     * @param dataSource DataSource para criar a instância se necessário
     * @return A instância única do DatabaseConnectionManager
     */
    public static synchronized DatabaseConnectionManager getInstance(DataSource dataSource) {
        if (instance == null) {
            instance = new DatabaseConnectionManager(dataSource);
        }
        return instance;
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
