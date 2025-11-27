-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS techfit_academia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techfit_academia;

-- Tabela de alunos
CREATE TABLE alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    modalidade VARCHAR(50),
    checkins_mes INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de funcionários (administradores)
CREATE TABLE funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
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
    modalidade VARCHAR(50) NOT NULL,
    instrutor VARCHAR(100) NOT NULL,
    instrutor_id INT NULL,
    data DATE NOT NULL,
    inicio TIME NOT NULL,
    fim TIME NOT NULL,
    vagas INT NOT NULL,
    inscritos INT DEFAULT 0,
    espera INT DEFAULT 0,
    FOREIGN KEY (instrutor_id) REFERENCES funcionarios(id)
);

-- Tabela de agendamentos
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    turma_id INT NOT NULL,
    status ENUM('confirmado', 'espera') DEFAULT 'confirmado',
    data_agendamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE
);

-- Tabela de acessos
CREATE TABLE acessos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    acao ENUM('entrada', 'saida') NOT NULL,
    data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES alunos(id) ON DELETE CASCADE
);

-- Tabela de mensagens
CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    corpo TEXT NOT NULL,
    segmento_modalidade VARCHAR(50) NULL,
    segmento_frequencia INT DEFAULT 0,
    autor_id INT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES funcionarios(id) ON DELETE CASCADE
);

-- Tabela de avaliações físicas
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    peso DECIMAL(5,2),
    altura_cm INT,
    gordura DECIMAL(4,2),
    peito DECIMAL(5,2),
    cintura DECIMAL(5,2),
    quadril DECIMAL(5,2),
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES alunos(id) ON DELETE CASCADE
);

-- Inserir dados iniciais de alunos
INSERT INTO alunos (nome, email, senha, modalidade, checkins_mes) VALUES
('João Silva', 'aluno@techfit.com', '123456', 'Musculação', 8),
('Maria Souza', 'maria@techfit.com', '123456', 'Cross', 12),
('Pedro Santos', 'pedro@techfit.com', '123456', 'Yoga', 6);

-- Inserir dados iniciais de funcionários
INSERT INTO funcionarios (nome, email, senha) VALUES
('Admin TechFit', 'admin@techfit.com', '123456'),
('Carlos Instrutor', 'carlos@techfit.com', '123456');

-- Inserir modalidades
INSERT INTO modalidades (nome, descricao) VALUES
('Musculação', 'Treinamento com pesos para ganho de massa muscular'),
('Cross', 'Treinamento funcional de alta intensidade'),
('Yoga', 'Prática de alongamento e meditação'),
('Pilates', 'Exercícios para fortalecimento do core'),
('Spinning', 'Aula de bicicleta coletiva'),
('Zumba', 'Dança fitness aeróbica');

-- Inserir turmas de exemplo
INSERT INTO turmas (modalidade, instrutor, instrutor_id, data, inicio, fim, vagas) VALUES
('Musculação', 'Carlos', 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '07:00', '08:00', 12),
('Yoga', 'Ana', NULL, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00', '19:00', 15),
('Spinning', 'Rafa', NULL, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00', '20:00', 10),
('Cross', 'Carlos', 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '07:00', '08:00', 8),
('Pilates', 'Julia', NULL, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '18:30', '19:30', 12);

-- Inserir alguns agendamentos
INSERT INTO agendamentos (usuario_id, turma_id, status) VALUES
(1, 1, 'confirmado'),
(2, 2, 'confirmado'),
(3, 3, 'confirmado');

-- Atualizar contadores de inscritos
UPDATE turmas SET inscritos = 1 WHERE id IN (1, 2, 3);

-- Inserir algumas mensagens
INSERT INTO mensagens (titulo, corpo, autor_id) VALUES
('Bem-vindo à TechFit!', 'Seja bem-vindo à nossa academia inteligente. Use o QR code para acessar as instalações.', 1),
('Manutenção programada', 'Atenção: amanhã teremos manutenção no sistema das 2h às 4h.', 1);

-- Inserir algumas avaliações
INSERT INTO avaliacoes (usuario_id, peso, altura_cm, gordura) VALUES
(1, 75.5, 175, 12.5),
(2, 62.0, 168, 18.2);
