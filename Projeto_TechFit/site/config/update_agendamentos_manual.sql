-- Script MANUAL para atualizar a tabela agendamentos
-- Execute este script no seu banco de dados MySQL/MariaDB

USE techfit_academia;

-- Passo 1: Tornar turma_id opcional
ALTER TABLE agendamentos MODIFY COLUMN turma_id INT NULL;

-- Passo 2: Adicionar novas colunas (execute um por vez se der erro)
ALTER TABLE agendamentos ADD COLUMN data DATE NULL AFTER turma_id;
ALTER TABLE agendamentos ADD COLUMN horario_inicio TIME NULL AFTER data;
ALTER TABLE agendamentos ADD COLUMN horario_fim TIME NULL AFTER horario_inicio;
ALTER TABLE agendamentos ADD COLUMN modalidade VARCHAR(100) NULL AFTER horario_fim;
ALTER TABLE agendamentos ADD COLUMN observacoes TEXT NULL AFTER modalidade;

-- Passo 3: Atualizar o enum de status
ALTER TABLE agendamentos MODIFY COLUMN status ENUM('confirmado', 'espera', 'pendente') DEFAULT 'pendente';

