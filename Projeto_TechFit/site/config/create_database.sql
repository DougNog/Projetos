-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS techfit_academia;
USE techfit_academia;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('aluno', 'admin') DEFAULT 'aluno',
    modalidade VARCHAR(50),
    checkins_mes INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de modalidades
CREATE TABLE modalidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) UNIQUE NOT NULL,
    descricao TEXT,
    ativa BOOLEAN DEFAULT TRUE
);

-- Tabela de turmas
CREATE TABLE turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modalidade_id INT,
    instrutor VARCHAR(100) NOT NULL,
    data DATE NOT NULL,
    inicio TIME NOT NULL,
    fim TIME NOT NULL,
    vagas INT NOT NULL,
    FOREIGN KEY (modalidade_id) REFERENCES modalidades(id)
);

-- Tabela de agendamentos
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    turma_id INT,
    status ENUM('confirmado', 'espera') DEFAULT 'confirmado',
    data_agendamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (turma_id) REFERENCES turmas(id)
);

-- Tabela de acessos
CREATE TABLE acessos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao ENUM('entrada', 'saida') NOT NULL,
    data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de mensagens
CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    corpo TEXT NOT NULL,
    segmento_modalidade VARCHAR(50) NULL,
    segmento_frequencia INT DEFAULT 0,
    autor_id INT,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id)
);

-- Tabela de avaliações físicas
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    peso DECIMAL(5,2),
    altura_cm INT,
    gordura DECIMAL(4,2),
    peito DECIMAL(5,2),
    cintura DECIMAL(5,2),
    quadril DECIMAL(5,2),
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Inserir dados iniciais
INSERT INTO usuarios (nome, email, senha, perfil, modalidade, checkins_mes) VALUES 
('João Silva', 'aluno@techfit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', 'Musculação', 8),
('Admin', 'admin@techfit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 0),
('Maria Souza', 'maria@techfit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', 'Cross', 12);

INSERT INTO modalidades (nome) VALUES 
('Musculação'), ('Cross'), ('Yoga'), ('Pilates'), ('Spinning');

-- Inserir algumas turmas para a próxima semana
INSERT INTO turmas (modalidade_id, instrutor, data, inicio, fim, vagas) VALUES 
(1, 'Carlos', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '07:00', '08:00', 12),
(3, 'Ana', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00', '19:00', 15),
(5, 'Rafa', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00', '20:00', 10);
