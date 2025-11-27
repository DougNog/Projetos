<?php
require_once __DIR__ . '/../../core/BaseModel.php';

class TreinoPersonalizado extends BaseModel {
    protected $table = 'treinos_personalizados';

    public function doUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE usuario_id = :uid ORDER BY data_criacao DESC");
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function gerarSugestao($usuarioId, $avaliacaoId) {
        require_once __DIR__ . '/Avaliacao.php';
        $avaliacaoModel = new Avaliacao();
        $avaliacao = $avaliacaoModel->find($avaliacaoId);
        
        if (!$avaliacao) {
            return false;
        }
        
        // Lógica básica de sugestão baseada no IMC
        $imc = $avaliacao['imc'];
        $descricao = '';
        $exercicios = '';
        
        if ($imc < 18.5) {
            $descricao = 'Treino para ganho de massa muscular';
            $exercicios = 'Supino, Agachamento, Levantamento Terra, Desenvolvimento, Remada';
        } elseif ($imc < 25) {
            $descricao = 'Treino de manutenção e definição';
            $exercicios = 'Treino ABC completo: Peito/Tríceps, Costas/Bíceps, Pernas/Ombro';
        } elseif ($imc < 30) {
            $descricao = 'Treino focado em perda de peso e condicionamento';
            $exercicios = 'Cardio + Musculação: Corrida, Bicicleta, Circuito funcional, Treino de força';
        } else {
            $descricao = 'Treino para perda de peso e saúde cardiovascular';
            $exercicios = 'Cardio moderado: Caminhada, Natação, Ciclismo, Musculação leve';
        }
        
        return $this->create([
            'usuario_id' => $usuarioId,
            'avaliacao_id' => $avaliacaoId,
            'descricao' => $descricao,
            'exercicios' => $exercicios,
            'observacoes' => "Baseado na avaliação de " . date('d/m/Y', strtotime($avaliacao['data_avaliacao']))
        ]);
    }
}


