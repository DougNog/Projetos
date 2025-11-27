# TechFit - Sistema de Gestão de Academia

Sistema completo de gestão para academias desenvolvido em PHP com arquitetura MVC.

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado
- Extensão PDO do PHP habilitada

## Instalação

1. **Clone ou baixe o projeto** para o diretório do seu servidor web (ex: `htdocs`, `www`, etc.)

2. **Configure o banco de dados:**
   - Crie um banco de dados MySQL
   - Execute o script SQL em `config/create_database.sql`
   - Ajuste as credenciais em `config/database.php` se necessário:
     ```php
     $dsn = "mysql:host=localhost;dbname=techfit_academia;charset=utf8";
     "root",  // usuário
     "senaisp" // senha
     ```

3. **Configure o servidor web:**
   - Certifique-se de que o `DocumentRoot` aponte para a pasta `public`
   - O arquivo `.htaccess` já está configurado na pasta `public`

4. **Acesse o sistema:**
   - Abra no navegador: `http://localhost/techfit_mvc_full/public/`
   - Ou configure um Virtual Host apontando para a pasta `public`

## Credenciais Padrão

### Administrador
- Email: `admin@techfit.com`
- Senha: `password` (hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`)

### Aluno Teste
- Email: `aluno@techfit.com`
- Senha: `password` (hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`)

## Funcionalidades

### Módulo de Agendamento Online
- Visualização de horários e modalidades
- Agendamento e cancelamento de aulas
- Lista de espera automática
- Relatórios de ocupação
- Notificações de alterações

### Módulo de Controle de Acesso
- Geração de QR Code
- Registro de entrada/saída
- Relatórios de utilização
- Histórico de acessos

### Módulo de Comunicação
- Envio de mensagens personalizadas
- Envio segmentado (modalidade, frequência)
- Canal de dúvidas e sugestões
- Sistema de respostas

### Módulo de Avaliação Física
- Registro de avaliações
- Gráficos de evolução
- Sugestões de treinos personalizados
- Alertas de novas avaliações

### Painel Administrativo
- Dashboard com estatísticas
- Gerenciamento de usuários, turmas e modalidades
- Relatórios gerenciais
- Controle completo do sistema

## Estrutura do Projeto

```
techfit_mvc_full/
├── app/
│   ├── controllers/    # Controladores
│   ├── models/         # Modelos de dados
│   └── views/          # Views (templates)
├── config/             # Configurações
├── core/               # Classes base
└── public/             # Pasta pública (DocumentRoot)
    ├── assets/         # CSS, JS, imagens
    ├── .htaccess       # Configuração Apache
    └── index.php       # Ponto de entrada
```

## Tecnologias Utilizadas

- PHP 7.4+
- MySQL
- HTML5
- CSS3
- JavaScript (Vanilla)
- Chart.js (gráficos)
- QRCode.js (QR Codes)

## Suporte

Para problemas ou dúvidas, verifique:
1. Se o banco de dados está criado e configurado
2. Se as credenciais do banco estão corretas
3. Se o mod_rewrite está habilitado no Apache
4. Se os arquivos têm as permissões corretas

