<h1><i class="fas fa-bullhorn"></i> Enviar Mensagem Segmentada</h1>

<form method="POST" action="/mensagens/segmentada/enviar" class="form">
    <div class="form-group">
        <label>Segmento:</label>
        <select name="segmento" id="segmento" required>
            <option value="todos">Todos os Alunos</option>
            <option value="modalidade">Por Modalidade</option>
            <option value="frequencia">Por Frequência</option>
        </select>
    </div>
    
    <div class="form-group" id="valor-segmento-group" style="display: none;">
        <label id="valor-label">Valor:</label>
        <select name="valor_segmento" id="valor_segmento">
            <option value="">Selecione...</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Assunto:</label>
        <input type="text" name="assunto" required>
    </div>
    
    <div class="form-group">
        <label>Mensagem:</label>
        <textarea name="corpo" rows="5" required></textarea>
    </div>
    
    <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Enviar Mensagem</button>
</form>

<script>
document.getElementById('segmento').addEventListener('change', function() {
    const grupo = document.getElementById('valor-segmento-group');
    const select = document.getElementById('valor_segmento');
    const label = document.getElementById('valor-label');
    
    if (this.value === 'todos') {
        grupo.style.display = 'none';
    } else {
        grupo.style.display = 'block';
        select.innerHTML = '<option value="">Selecione...</option>';
        
        if (this.value === 'modalidade') {
            label.textContent = 'Modalidade:';
            const modalidades = <?= json_encode(array_map(function($m) { return ['nome' => $m['nome']]; }, $modalidades)) ?>;
            modalidades.forEach(function(mod) {
                select.innerHTML += '<option value="' + mod.nome + '">' + mod.nome + '</option>';
            });
        } else if (this.value === 'frequencia') {
            label.textContent = 'Frequência:';
            select.innerHTML += '<option value="alta">Alta (15+ check-ins/mês)</option>';
            select.innerHTML += '<option value="media">Média (8-14 check-ins/mês)</option>';
            select.innerHTML += '<option value="baixa">Baixa (< 8 check-ins/mês)</option>';
        }
    }
});
</script>


