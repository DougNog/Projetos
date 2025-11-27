<?php
require_once __DIR__ . '/../../core/BaseModel.php';

class Acesso extends BaseModel {
    protected $table = 'acessos';

    public function doUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE usuario_id = :uid ORDER BY data_hora_entrada DESC");
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function todos() {
        $sql = "SELECT a.*, u.nome 
                FROM acessos a
                JOIN usuarios u ON a.usuario_id = u.id
                ORDER BY a.data_hora_entrada DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function registrarAcesso($usuarioId, $tipoIdentificacao = 'qrcode', $codigo = null) {
        return $this->create([
            'usuario_id' => $usuarioId,
            'tipo_identificacao' => $tipoIdentificacao,
            'codigo' => $codigo,
            'data_hora_entrada' => date('Y-m-d H:i:s')
        ]);
    }

    public function registrarSaida($acessoId) {
        $sql = "UPDATE {$this->table} SET data_hora_saida = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $acessoId]);
    }

    public function relatorioUtilizacao($dataInicio = null, $dataFim = null) {
        $sql = "SELECT 
                    DATE(a.data_hora_entrada) AS data,
                    COUNT(DISTINCT a.usuario_id) AS usuarios_unicos,
                    COUNT(*) AS total_acessos,
                    AVG(TIMESTAMPDIFF(MINUTE, a.data_hora_entrada, COALESCE(a.data_hora_saida, NOW()))) AS tempo_medio_minutos
                FROM acessos a
                WHERE 1=1";
        
        $params = [];
        if ($dataInicio) {
            $sql .= " AND DATE(a.data_hora_entrada) >= :data_inicio";
            $params['data_inicio'] = $dataInicio;
        }
        if ($dataFim) {
            $sql .= " AND DATE(a.data_hora_entrada) <= :data_fim";
            $params['data_fim'] = $dataFim;
        }
        
        $sql .= " GROUP BY DATE(a.data_hora_entrada)
                  ORDER BY data DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function frequenciaPorUsuario($usuarioId, $mes = null, $ano = null) {
        $sql = "SELECT 
                    DATE(data_hora_entrada) AS data,
                    COUNT(*) AS acessos_dia
                FROM {$this->table}
                WHERE usuario_id = :usuario_id";
        
        $params = ['usuario_id' => $usuarioId];
        
        if ($mes && $ano) {
            $sql .= " AND MONTH(data_hora_entrada) = :mes AND YEAR(data_hora_entrada) = :ano";
            $params['mes'] = $mes;
            $params['ano'] = $ano;
        }
        
        $sql .= " GROUP BY DATE(data_hora_entrada)
                  ORDER BY data DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
