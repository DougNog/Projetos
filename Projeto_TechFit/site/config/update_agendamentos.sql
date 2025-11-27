-- Script para atualizar a tabela agendamentos para suportar agendamentos personalizados
-- Execute este script no banco de dados se já tiver dados existentes

USE techfit_academia;

-- Verificar se as colunas já existem antes de adicionar
-- Alterar a tabela agendamentos para permitir agendamentos personalizados

-- Tornar turma_id opcional (NULL)
ALTER TABLE agendamentos MODIFY COLUMN turma_id INT NULL;

-- Adicionar novas colunas se não existirem
ALTER TABLE agendamentos 
ADD COLUMN IF NOT EXISTS data DATE NULL AFTER turma_id,
ADD COLUMN IF NOT EXISTS horario_inicio TIME NULL AFTER data,
ADD COLUMN IF NOT EXISTS horario_fim TIME NULL AFTER horario_inicio,
ADD COLUMN IF NOT EXISTS modalidade VARCHAR(100) NULL AFTER horario_fim,
ADD COLUMN IF NOT EXISTS observacoes TEXT NULL AFTER modalidade;

-- Atualizar o enum de status para incluir 'pendente'
ALTER TABLE agendamentos 
MODIFY COLUMN status ENUM('confirmado', 'espera', 'pendente') DEFAULT 'pendente';
