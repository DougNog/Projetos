<h1><i class="fas fa-users-cog"></i> Gerenciar Usuários</h1>

<section class="admin-actions">
  <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
  <div class="action-buttons">
    <a href="/avaliacoes/nova" class="btn-primary"><i class="fas fa-clipboard-check"></i> Nova Avaliação</a>
    <a href="/mensagens/nova" class="btn-primary"><i class="fas fa-envelope"></i> Nova Mensagem</a>
  </div>
</section>

<section>
    <h2><i class="fas fa-user-plus"></i> Novo Usuário</h2>
    <form method="POST" action="/admin/usuarios/salvar" class="form">
        <input type="hidden" name="id" id="usuario_id">
        <div class="form-group">
            <label>Nome:</label>
            <input type="text" name="nome" id="usuario_nome" required>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" id="usuario_email" required>
        </div>
        <div class="form-group">
            <label>Senha:</label>
            <input type="password" name="senha" id="usuario_senha" placeholder="Deixe em branco para manter a atual">
        </div>
        <div class="form-group">
            <label>Perfil:</label>
            <select name="perfil" id="usuario_perfil" required>
                <option value="aluno">Aluno</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label>Modalidade:</label>
            <input type="text" name="modalidade" id="usuario_modalidade">
        </div>
        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar</button>
        <button type="button" class="btn-secondary" onclick="limparForm()"><i class="fas fa-times"></i> Cancelar</button>
    </form>
</section>

<section>
    <h2><i class="fas fa-list"></i> Lista de Usuários</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Perfil</th>
                <th>Modalidade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nome']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                  <?php if ($u['perfil'] === 'admin'): ?>
                    <span class="badge-unread"><i class="fas fa-user-shield"></i> Admin</span>
                  <?php else: ?>
                    <span class="badge-read"><i class="fas fa-user"></i> Aluno</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($u['modalidade'] ?? '') ?></td>
                <td>
                    <button onclick="editarUsuario(<?= htmlspecialchars(json_encode($u)) ?>)" class="btn-secondary"><i class="fas fa-edit"></i> Editar</button>
                    <form method="POST" action="/admin/usuarios/excluir" class="inline-form" onsubmit="return confirm('Tem certeza?')">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn-danger"><i class="fas fa-trash"></i> Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<script>
function editarUsuario(usuario) {
    document.getElementById('usuario_id').value = usuario.id;
    document.getElementById('usuario_nome').value = usuario.nome;
    document.getElementById('usuario_email').value = usuario.email;
    document.getElementById('usuario_perfil').value = usuario.perfil;
    document.getElementById('usuario_modalidade').value = usuario.modalidade || '';
    document.getElementById('usuario_senha').placeholder = 'Deixe em branco para manter a atual';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function limparForm() {
    document.getElementById('usuario_id').value = '';
    document.getElementById('usuario_nome').value = '';
    document.getElementById('usuario_email').value = '';
    document.getElementById('usuario_senha').value = '';
    document.getElementById('usuario_perfil').value = 'aluno';
    document.getElementById('usuario_modalidade').value = '';
    document.getElementById('usuario_senha').placeholder = '';
}
</script>


