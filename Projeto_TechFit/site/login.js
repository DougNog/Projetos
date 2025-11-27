const $ = (sel, ctx=document) => ctx.querySelector(sel);
const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

// ---------------------------
// Configuração da API
// ---------------------------
const API_BASE = 'api';

// ---------------------------
// Funções de API
// ---------------------------
async function apiCall(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE}/${endpoint}`, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Erro desconhecido' }));
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API call failed:', error);
        toast(error.message || 'Erro de conexão com o servidor', 'err');
        throw error;
    }
}

// ---------------------------
// Estado & Persistência Local (para dados em cache)
// ---------------------------
const DB = {
  seed(){
    // Dados iniciais agora vêm do MySQL
    return;
  },
  users: () => [], // Agora vem da API
  modalidades: () => [], // Agora vem da API
  turmas: () => [], // Agora vem da API
  mensagens: () => [], // Agora vem da API
  bookings: () => [], // Agora vem da API
  acessos: () => [], // Agora vem da API
  avaliacoes: () => ({}), // Agora vem da API
  set(key, val){ 
    // Para compatibilidade, mas dados principais vêm da API
    if(key.startsWith('techfit_')) {
      localStorage.setItem(key, JSON.stringify(val)); 
    }
  }
};

function save(key, val){ localStorage.setItem(key, JSON.stringify(val)) }
function load(key, fallback){ try{ return JSON.parse(localStorage.getItem(key)) ?? fallback }catch(e){ return fallback } }
function mkId(prefix='id'){ return prefix + Math.random().toString(36).slice(2,9) }
function mkTurma(modalidade, instrutor, date, ini, fim, vagas){
  return { id: mkId('t_'), modalidade, instrutor, data: ymd(date), inicio: ini, fim: fim, vagas, inscritos: [] };
}
function ymd(d){ return d.toISOString().slice(0,10) }
function fmtDatePt(dstr){
  const d = new Date(dstr+"T00:00:00"); 
  return d.toLocaleDateString('pt-BR', {weekday:'short', day:'2-digit', month:'2-digit'});
}
function toast(msg, kind='ok'){
  const t = document.createElement('div'); 
  t.className = 'toast ' + kind; 
  t.textContent = msg;
  $('#toaster').appendChild(t);
  setTimeout(()=> t.remove(), 3200);
}

// ---------------------------
// Sessão
// ---------------------------
let SESSION = { user: null };
function setSession(u){
  SESSION.user = u;
  if(u){
    $('#user-chip').textContent = `${u.nome} — ${u.perfil}`;
    $('#btn-logout').style.display = 'inline-block';
  }else{
    $('#user-chip').textContent = '';
    $('#btn-logout').style.display = 'none';
  }
}

// ---------------------------
// Rotas & Views
// ---------------------------
const routes = {
  login(){ renderTemplate('#tpl-login'); bindLogin() },
  home(){ guard(); renderTemplate('#tpl-home'); homeInit() },
  agenda(){ guard(); renderTemplate('#tpl-agenda'); agendaInit() },
  acesso(){ guard(); renderTemplate('#tpl-acesso'); acessoInit() },
  comunicacao(){ guard(); renderTemplate('#tpl-comunicacao'); comunicacaoInit() },
  avaliacao(){ guard(); renderTemplate('#tpl-avaliacao'); avaliacaoInit() },
  admin(){ guardAdmin(); renderTemplate('#tpl-admin'); adminInit() }
};

function renderTemplate(sel){
  const tpl = $(sel);
  $('#view').innerHTML = tpl.innerHTML;
}

function guard(){ if(!SESSION.user) return routes.login(); }
function guardAdmin(){ guard(); if(SESSION.user.perfil !== 'admin'){ toast('Apenas administradores.', 'warn'); return routes.home(); } }

// Router buttons
$('#top-nav').addEventListener('click', (e)=>{
  const b = e.target.closest('[data-route]'); if(!b) return;
  const route = b.dataset.route; routes[route] && routes[route]();
});
$('#btn-logout').addEventListener('click', ()=>{ setSession(null); routes.login(); });

// ---------------------------
// Login (Atualizado para MySQL)
// ---------------------------
function bindLogin(){
  $('#form-login').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const email = $('#login-email').value.trim().toLowerCase();
    const senha = $('#login-senha').value.trim();
    const adminFlag = $('#login-admin').checked;

    try {
        const result = await apiCall('login.php', {
            method: 'POST',
            body: JSON.stringify({
                email: email,
                senha: senha,
                is_admin: adminFlag
            })
        });

        if (result.user) {
            setSession(result.user);
            routes.home();
            toast('Login realizado com sucesso!', 'ok');
        }
    } catch (error) {
        // Mensagem de erro já é tratada na apiCall
    }
  });
}

// ---------------------------
// HOME (Atualizado para MySQL)
// ---------------------------
async function homeInit(){
  const user = SESSION.user;
  $('#year').textContent = new Date().getFullYear();
  
  try {
      // Buscar agendamentos do usuário
      const agendamentos = await apiCall(`agendamentos.php?usuario_id=${user.id}`);
      
      // Buscar todas as turmas
      const turmas = await apiCall('turmas.php');
      
      // Filtrar turmas agendadas
      const minhasTurmas = turmas.filter(t => 
          agendamentos.some(a => a.turma_id == t.id && a.status === 'confirmado')
      );
      
      const wrap = $('#home-minha-semana');
      wrap.innerHTML = minhasTurmas.map(t => `
          <div class="chip">
              <strong>${t.modalidade}</strong> • ${fmtDatePt(t.data)} ${t.inicio}–${t.fim} • Instr.: ${t.instrutor}
          </div>`).join('') || '<p class="muted">Sem aulas agendadas.</p>';

      // Buscar mensagens
      const mensagens = await apiCall(`mensagens.php?usuario_id=${user.id}`);
      $('#home-notificacoes').innerHTML = mensagens.slice(0, 5).map(m => `
          <li>
              <strong>${m.titulo}</strong><br>
              <span class="muted">${new Date(m.data_envio).toLocaleString()}</span>
              <p>${m.corpo}</p>
          </li>`
      ).join('') || '<li class="muted">Sem mensagens.</li>';

      // Atualizar KPIs
      $('#kpi-frequencia').textContent = String(user.checkinsMes);
      
      // Calcular ocupação média
      const ocupacao = turmas.length > 0 ? 
          turmas.reduce((acc, t) => acc + (parseInt(t.inscritos) / parseInt(t.vagas)), 0) / turmas.length : 0;
      $('#kpi-ocupacao').textContent = (ocupacao * 100).toFixed(0) + '%';
      
      $('#kpi-msgs').textContent = String(mensagens.length);
      
  } catch (error) {
      console.error('Error loading home data:', error);
      $('#home-minha-semana').innerHTML = '<p class="muted">Erro ao carregar dados.</p>';
      $('#home-notificacoes').innerHTML = '<li class="muted">Erro ao carregar mensagens.</li>';
  }
}

// ---------------------------
// Agenda (Atualizado para MySQL)
// ---------------------------
async function agendaInit(){
  try {
      const modalidadesData = await apiCall('admin.php?action=modalidades');
      const turmas = await apiCall('turmas.php');
      const meusAgendamentos = await apiCall(`agendamentos.php?usuario_id=${SESSION.user.id}`);
      
      const sel = $('#filtro-modalidade');
      sel.innerHTML = ['Todas', ...modalidadesData.map(m => m.nome)].map(m => `<option>${m}</option>`).join('');
      $('#filtro-data').value = ymd(new Date());
      
      const render = () => {
          const m = sel.value;
          const d = $('#filtro-data').value;
          const list = turmas.filter(t => 
              (m === 'Todas' || t.modalidade === m) && 
              (!d || t.data === d)
          );
          $('#lista-aulas').innerHTML = list.map(t => cardTurma(t, meusAgendamentos)).join('') || '<p class="muted">Sem turmas.</p>';
          $$('#lista-aulas .btn[data-book]').forEach(btn => 
              btn.addEventListener('click', () => toggleBooking(btn.dataset.book))
          );
      };
      
      sel.addEventListener('change', render);
      $('#filtro-data').addEventListener('change', render);
      $('#btn-rel-ocupacao').addEventListener('click', () => showRelOcupacao());
      $('#btn-exportar-ocupacao').addEventListener('click', () => exportarOcupacaoCSV());
      render();
      
  } catch (error) {
      console.error('Error loading agenda:', error);
      $('#lista-aulas').innerHTML = '<p class="muted">Erro ao carregar turmas.</p>';
  }
}

function cardTurma(t, meusAgendamentos){
  const lotada = parseInt(t.inscritos) >= parseInt(t.vagas);
  const meuAgendamento = meusAgendamentos.find(a => a.turma_id == t.id);
  const status = meuAgendamento ? 
      (meuAgendamento.status === 'confirmado' ? 'Inscrito' : 'Lista de Espera') : 
      (lotada ? 'Lotada' : 'Vagas');
  
  const cls = lotada && !meuAgendamento ? 'danger' : (meuAgendamento ? 'primary' : 'outline');
  const labelBtn = meuAgendamento ? 'Cancelar' : (lotada ? 'Entrar na Espera' : 'Agendar');
  
  return `
  <div class="card">
    <h4>${t.modalidade}</h4>
    <p class="muted">Instrutor: ${t.instrutor}</p>
    <p><strong>${fmtDatePt(t.data)} ${t.inicio}–${t.fim}</strong></p>
    <div class="inline">
      <span class="chip">${t.inscritos}/${t.vagas} ocupadas</span>
      <span class="chip">${t.espera || 0} na espera</span>
      <span class="chip">${status}</span>
    </div>
    <div class="inline" style="margin-top:10px">
      <button class="btn ${cls}" data-book="${t.id}">${labelBtn}</button>
    </div>
  </div>`;
}

async function toggleBooking(turmaId){
  const u = SESSION.user;
  
  try {
      // Verificar se já está agendado
      const meusAgendamentos = await apiCall(`agendamentos.php?usuario_id=${u.id}`);
      const agendamentoExistente = meusAgendamentos.find(a => a.turma_id == turmaId);
      
      if (agendamentoExistente) {
          // Cancelar agendamento
          await apiCall('agendamentos.php', {
              method: 'DELETE',
              body: JSON.stringify({
                  usuario_id: u.id,
                  turma_id: turmaId
              })
          });
          toast('Agendamento cancelado.', 'ok');
      } else {
          // Fazer novo agendamento
          const result = await apiCall('agendamentos.php', {
              method: 'POST',
              body: JSON.stringify({
                  usuario_id: u.id,
                  turma_id: turmaId
              })
          });
          toast(result.message, result.status === 'confirmado' ? 'ok' : 'warn');
      }
      
      // Recarregar a agenda
      routes.agenda();
      
  } catch (error) {
      console.error('Error toggling booking:', error);
  }
}

function ocupacaoMedia(turmas){
  if(!turmas.length) return 0;
  const ratios = turmas.map(t => parseInt(t.inscritos) / Math.max(1, parseInt(t.vagas)));
  return ratios.reduce((a,c) => a + c, 0) / ratios.length;
}

async function showRelOcupacao(){
  try {
      const turmas = await apiCall('turmas.php');
      const rows = turmas.map(t => {
          const occ = ((parseInt(t.inscritos) / Math.max(1, parseInt(t.vagas))) * 100).toFixed(0) + '%';
          return `<tr>
              <td>${t.modalidade}</td>
              <td>${fmtDatePt(t.data)} ${t.inicio}-${t.fim}</td>
              <td>${t.vagas}</td>
              <td>${t.inscritos}</td>
              <td>${t.espera || 0}</td>
              <td>${occ}</td>
          </tr>`;
      }).join('');
      
      const html = `<div class="card glass"><h4>Relatório de Ocupação</h4>
          <table class="table">
              <thead>
                  <tr>
                      <th>Turma</th><th>Data</th><th>Vagas</th><th>Inscritos</th><th>Espera</th><th>Ocupação</th>
                  </tr>
              </thead>
              <tbody>${rows}</tbody>
          </table>
      </div>`;
      
      const v = $('#view'); 
      const div = document.createElement('div'); 
      div.innerHTML = html; 
      v.appendChild(div);
  } catch (error) {
      console.error('Error loading occupancy report:', error);
      toast('Erro ao carregar relatório', 'err');
  }
}

function exportarOcupacaoCSV(){
  // Esta função ainda usa dados locais para exportação
  // Pode ser adaptada para usar dados da API se necessário
  toast('Funcionalidade de exportação em desenvolvimento', 'warn');
}

// ---------------------------
// Acesso com QR (Atualizado para MySQL)
// ---------------------------
let scanStream=null, scanTimer=null;

async function acessoInit(){
  $('#btn-gerar-qr').addEventListener('click', () => gerarQR());
  gerarQR();
  $('#btn-start-scan').addEventListener('click', () => startScan());
  $('#btn-stop-scan').addEventListener('click', () => stopScan());
  await renderAcessos();
}

function gerarQR(){
  const area = $('#qr-area'); 
  area.innerHTML='';
  const payload = JSON.stringify({ 
      uid: SESSION.user.id, 
      ts: Date.now(),
      nome: SESSION.user.nome 
  });
  
  // Usar a biblioteca QRCode existente
  if(typeof QRCode !== 'undefined') {
      const q = new QRCode(area, { 
          text: payload, 
          width: 220, 
          height: 220 
      });
      if(typeof q.makeCode === 'function') {
          q.makeCode(payload);
      }
  }
  toast('QR atualizado.');
}

async function startScan(){
  guardAdmin();
  const vid = $('#cam'); 
  const canvas = $('#cam-canvas'); 
  const ctx = canvas.getContext('2d');
  
  try {
      scanStream = await navigator.mediaDevices.getUserMedia({ 
          video: { facingMode: 'environment' }, 
          audio: false 
      });
      vid.srcObject = scanStream;
      $('#scan-status').textContent = 'Scanner ativo. Aponte para um QR TechFit.';
      
      // Simulação: clique no vídeo para "ler" um QR
      vid.onclick = () => simulateScan();
      
      scanTimer = setInterval(() => {
          if(!vid.videoWidth) return;
          canvas.width = vid.videoWidth; 
          canvas.height = vid.videoHeight;
          ctx.drawImage(vid, 0, 0, canvas.width, canvas.height);
      }, 400);
      
  } catch(e) {
      $('#scan-status').textContent = 'Erro ao acessar câmera: ' + e.message;
  }
}

function stopScan(){
  if(scanTimer){ 
      clearInterval(scanTimer); 
      scanTimer = null; 
  }
  if(scanStream){ 
      scanStream.getTracks().forEach(t => t.stop()); 
      scanStream = null; 
  }
  $('#scan-status').textContent = 'Câmera parada.';
}

async function simulateScan(){
  try {
      const alunos = await apiCall('admin.php?action=alunos');
      if(alunos.length > 0) {
          const u = alunos[0];
          const acao = Math.random() > 0.5 ? 'entrada' : 'saida';
          await registrarAcesso(u.id, acao);
      }
  } catch(error) {
      console.error('Error simulating scan:', error);
  }
}

async function registrarAcesso(userId, acao){
  try {
      await apiCall('acessos.php', {
          method: 'POST',
          body: JSON.stringify({
              usuario_id: userId,
              acao: acao
          })
      });
      
      const alunos = await apiCall('admin.php?action=alunos');
      const u = alunos.find(x => x.id == userId);
      if(u) {
          toast(`Acesso ${acao} registrado para ${u.nome}.`, 'ok');
      }
      await renderAcessos();
      
  } catch (error) {
      console.error('Error registering access:', error);
  }
}

async function renderAcessos(){
  try {
      const acessos = await apiCall('acessos.php');
      const tbody = $('#tabela-acessos tbody');
      const rows = acessos.map(a => `
          <tr>
              <td>${new Date(a.data_acesso).toLocaleString()}</td>
              <td>${a.nome}</td>
              <td>${a.acao}</td>
          </tr>`
      ).join('');
      tbody.innerHTML = rows || '<tr><td colspan="3" class="muted">Sem registros.</td></tr>';
  } catch (error) {
      console.error('Error loading access history:', error);
      tbody.innerHTML = '<tr><td colspan="3" class="muted">Erro ao carregar registros.</td></tr>';
  }
}

// ---------------------------
// Comunicação (Atualizado para MySQL)
// ---------------------------
async function comunicacaoInit(){
  try {
      const modalidadesData = await apiCall('admin.php?action=modalidades');
      const sel = $('#msg-modalidade');
      sel.innerHTML = ['— (todos)', ...modalidadesData.map(m => m.nome)].map(m => 
          `<option value="${m}">${m}</option>`
      ).join('');
      
      $('#form-msg').addEventListener('submit', async (e) => {
          e.preventDefault(); 
          guardAdmin();
          
          const titulo = $('#msg-titulo').value.trim();
          const corpo = $('#msg-corpo').value.trim();
          const segmentoModalidade = sel.value.startsWith('—') ? null : sel.value;
          const segmentoFrequencia = parseInt($('#msg-frequencia').value || '0', 10);
          
          try {
              await apiCall('mensagens.php', {
                  method: 'POST',
                  body: JSON.stringify({
                      titulo: titulo,
                      corpo: corpo,
                      segmento_modalidade: segmentoModalidade,
                      segmento_frequencia: segmentoFrequencia,
                      autor_id: SESSION.user.id
                  })
              });
              
              toast('Mensagem enviada.'); 
              await renderInbox(); 
              $('#form-msg').reset();
              
          } catch(error) {
              console.error('Error sending message:', error);
          }
      });
      
      await renderInbox();
      
  } catch(error) {
      console.error('Error initializing communication:', error);
  }
}

async function renderInbox(){
  try {
      const mensagens = await apiCall(`mensagens.php?usuario_id=${SESSION.user.id}`);
      $('#lista-mensagens').innerHTML = mensagens.map(m => `
          <li>
              <strong>${m.titulo}</strong><br>
              <span class="muted">${new Date(m.data_envio).toLocaleString()}</span>
              <p>${m.corpo}</p>
          </li>`
      ).join('') || '<li class="muted">Sem mensagens para você.</li>';
  } catch(error) {
      console.error('Error loading inbox:', error);
      $('#lista-mensagens').innerHTML = '<li class="muted">Erro ao carregar mensagens.</li>';
  }
}

// ---------------------------
// Avaliação Física (Atualizado para MySQL)
// ---------------------------
function bmi(peso, alturaCm){ 
    const m = alturaCm / 100; 
    return peso / (m * m); 
}

async function avaliacaoInit(){ 
    const userId = SESSION.user.id;
    
    $('#form-av').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const peso = parseFloat($('#av-peso').value);
        const alturaCm = parseFloat($('#av-altura').value);
        const gordura = parseFloat($('#av-gordura').value || '0');
        const peito = parseFloat($('#av-peito').value || '0');
        const cintura = parseFloat($('#av-cintura').value || '0');
        const quadril = parseFloat($('#av-quadril').value || '0');
        
        try {
            await apiCall('avaliacoes.php', {
                method: 'POST',
                body: JSON.stringify({
                    usuario_id: userId,
                    peso: peso,
                    altura_cm: alturaCm,
                    gordura: gordura,
                    peito: peito,
                    cintura: cintura,
                    quadril: quadril
                })
            });
            
            toast('Avaliação salva.'); 
            await avaliacaoInitCharts(); 
            await renderAlertas(); 
            $('#form-av').reset();
            
        } catch(error) {
            console.error('Error saving evaluation:', error);
        }
    });
    
    await avaliacaoInitCharts(); 
    await renderAlertas();
}

async function avaliacaoInitCharts(){
    try {
        const avaliacoes = await apiCall(`avaliacoes.php?usuario_id=${SESSION.user.id}`);
        drawLine('#chart-peso', 'Peso (kg)', getAvSeries(avaliacoes, 'peso'));
        drawLine('#chart-bmi', 'IMC', getAvSeries(avaliacoes, 'bmi'));
    } catch(error) {
        console.error('Error loading charts:', error);
    }
}

function getAvSeries(avaliacoes, field){
    return avaliacoes.map(a => ({ 
        x: new Date(a.data_avaliacao).getTime(), 
        y: field === 'bmi' ? bmi(a.peso, a.altura_cm) : (a[field] || 0) 
    }));
}

function drawLine(sel, title, data){
    const svg = $(sel);
    const W = 600, H = 240, pad = 40;
    svg.innerHTML = '';
    
    const g = (tag, attrs, parent = svg) => {
        const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
        for(const k in attrs) el.setAttribute(k, attrs[k]);
        parent.appendChild(el); 
        return el;
    };
    
    g('rect', { x:0, y:0, width:W, height:H, fill:'transparent', rx:12, ry:12, stroke:'rgba(255,255,255,.08)' });
    g('text', { x:pad, y:24, fill:'#e6eefc', 'font-size':'14' }).textContent = title;
    
    if(!data.length){ 
        g('text', { x:W/2-60, y:H/2, fill:'#99a3b8' }).textContent = 'Sem dados'; 
        return; 
    }
    
    const xs = data.map(p => p.x), ys = data.map(p => p.y);
    const minX = Math.min(...xs), maxX = Math.max(...xs);
    const minY = Math.min(...ys), maxY = Math.max(...ys);
    
    const sx = (x) => pad + ((x - minX) / (maxX - minX || 1)) * (W - 2 * pad);
    const sy = (y) => H - pad - ((y - minY) / (maxY - minY || 1)) * (H - 2 * pad);
    
    for(let i = 0; i < 5; i++){ 
        const y = pad + i * (H - 2 * pad) / 4; 
        g('line', { x1:pad, y1:y, x2:W-pad, y2:y, stroke:'rgba(255,255,255,.08)' }); 
    }
    
    let d = '';
    data.forEach((p, i) => { 
        const X = sx(p.x), Y = sy(p.y); 
        d += (i ? ' L ' : 'M ') + X + ' ' + Y; 
        g('circle', { cx:X, cy:Y, r:3, fill:'#6cf0ff' }); 
    });
    
    g('path', { d, fill:'none', stroke:'#9d6cff', 'stroke-width':'2' });
}

async function renderAlertas(){
    const list = $('#av-alertas'); 
    list.innerHTML = '';
    
    try {
        const avaliacoes = await apiCall(`avaliacoes.php?usuario_id=${SESSION.user.id}`);
        
        if(!avaliacoes.length){ 
            list.innerHTML = '<li class="muted">Sem avaliações registradas. Faça sua primeira avaliação!</li>'; 
            return; 
        }
        
        const last = avaliacoes[0]; // Mais recente primeiro
        const days = (Date.now() - new Date(last.data_avaliacao).getTime()) / 86400000;
        
        if(days > 30) {
            list.innerHTML += '<li class="toast warn">Mais de 30 dias sem avaliação. Refaça seu checkup.</li>';
        }
        
        const calcBmi = bmi(last.peso, last.altura_cm);
        list.innerHTML += `<li>IMC atual: <strong>${calcBmi.toFixed(1)}</strong></li>`;
        
    } catch(error) {
        console.error('Error loading alerts:', error);
        list.innerHTML = '<li class="muted">Erro ao carregar alertas.</li>';
    }
}

// ---------------------------
// Admin (Atualizado para MySQL)
// ---------------------------
async function adminInit(){
    $$('.tabs .tab').forEach(tab => tab.addEventListener('click', () => {
        $$('.tabs .tab').forEach(x => x.classList.remove('active'));
        tab.classList.add('active');
        const tgt = tab.dataset.tab;
        $$('.tab-pane').forEach(p => p.classList.remove('show'));
        $('#tab-'+tgt).classList.add('show');
    }));
    
    $('#tab-alunos').classList.add('show');
    
    await renderAlunos(); 
    $('#add-aluno').onclick = () => editAluno();
    
    await renderModalidades(); 
    $('#add-modalidade').onclick = () => addModalidade();
    
    await renderTurmas(); 
    $('#add-turma').onclick = () => editTurma();
    
    await renderRelatorios();
}

async function renderAlunos(){
    try {
        const alunos = await apiCall('admin.php?action=alunos');
        const tbody = $('#tbl-alunos tbody');
        tbody.innerHTML = '';

        alunos.forEach(u => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${u.nome}</td>
                <td>${u.email}</td>
                <td>${u.modalidade || '—'}</td>
                <td>${u.checkins_mes || 0}</td>
                <td>
                    <button class="btn small" data-edit="${u.id}">Editar</button>
                    <button class="btn small outline" data-del="${u.id}">Excluir</button>
                </td>`;
            tbody.appendChild(tr);
        });

        tbody.onclick = (e) => {
            const id = e.target.dataset.edit || e.target.dataset.del;
            if(!id) return;

            if(e.target.dataset.edit) editAluno(id);
            if(e.target.dataset.del){
                if(confirm('Tem certeza que deseja excluir este aluno?')){
                    delAluno(id);
                }
            }
        };

    } catch(error) {
        console.error('Error loading students:', error);
        $('#tbl-alunos tbody').innerHTML = '<tr><td colspan="5" class="muted">Erro ao carregar alunos.</td></tr>';
    }
}

async function editAluno(id = null){
    try {
        let aluno = null;
        if (id) {
            // Buscar dados do aluno para editar
            const alunos = await apiCall('admin.php?action=alunos');
            aluno = alunos.find(a => a.id == id);
            if (!aluno) {
                toast('Aluno não encontrado', 'err');
                return;
            }
        }

        // Formulário simples para editar aluno
        const nome = prompt('Nome do aluno:', aluno ? aluno.nome : '');
        if (nome === null) return;

        const email = prompt('Email do aluno:', aluno ? aluno.email : '');
        if (email === null) return;

        const modalidade = prompt('Modalidade (opcional):', aluno ? aluno.modalidade : '');

        if (aluno) {
            // Atualizar aluno existente
            await apiCall('admin.php?action=alunos', {
                method: 'PUT',
                body: JSON.stringify({
                    id: id,
                    nome: nome.trim(),
                    email: email.trim().toLowerCase(),
                    modalidade: modalidade ? modalidade.trim() : null
                })
            });
            toast('Aluno atualizado com sucesso!', 'ok');
        } else {
            // Criar novo aluno
            const senha = prompt('Senha do aluno:');
            if (senha === null) return;

            await apiCall('admin.php?action=alunos', {
                method: 'POST',
                body: JSON.stringify({
                    nome: nome.trim(),
                    email: email.trim().toLowerCase(),
                    senha: senha,
                    modalidade: modalidade ? modalidade.trim() : null
                })
            });
            toast('Aluno criado com sucesso!', 'ok');
        }

        // Recarregar lista
        await renderAlunos();

    } catch (error) {
        console.error('Error editing student:', error);
        toast('Erro ao salvar aluno', 'err');
    }
}

async function delAluno(id){
    try {
        await apiCall(`admin.php?action=alunos&id=${id}`, {
            method: 'DELETE'
        });
        toast('Aluno excluído com sucesso!', 'ok');
        await renderAlunos();
    } catch (error) {
        console.error('Error deleting student:', error);
        toast('Erro ao excluir aluno', 'err');
    }
}

async function renderModalidades(){
    try {
        const modalidades = await apiCall('admin.php?action=modalidades');
        const list = $('#lista-modalidades'); 
        list.innerHTML = '';
        
        modalidades.forEach(m => {
            const el = document.createElement('div'); 
            el.className = 'chip';
            el.innerHTML = `<span>${m.nome}</span><button class="btn small outline" data-del="${m.id}">x</button>`;
            list.appendChild(el);
        });
        
        list.onclick = (e) => {
            const id = e.target.dataset.del;
            if(!id) return;
            if(confirm('Excluir modalidade?')) {
                delModalidade(id);
            }
        };
        
    } catch(error) {
        console.error('Error loading modalities:', error);
        $('#lista-modalidades').innerHTML = '<div class="muted">Erro ao carregar modalidades.</div>';
    }
}

async function addModalidade(){
    try {
        const nome = prompt('Nova modalidade:');
        if (!nome || !nome.trim()) return;

        await apiCall('admin.php?action=modalidades', {
            method: 'POST',
            body: JSON.stringify({
                nome: nome.trim()
            })
        });

        toast('Modalidade criada com sucesso!', 'ok');
        await renderModalidades();

    } catch (error) {
        console.error('Error adding modality:', error);
        toast('Erro ao criar modalidade', 'err');
    }
}

async function delModalidade(id){
    try {
        await apiCall(`admin.php?action=modalidades&id=${id}`, {
            method: 'DELETE'
        });
        toast('Modalidade excluída com sucesso!', 'ok');
        await renderModalidades();
    } catch (error) {
        console.error('Error deleting modality:', error);
        toast('Erro ao excluir modalidade', 'err');
    }
}

async function renderTurmas(){
    try {
        const turmas = await apiCall('turmas.php');
        const tbody = $('#tbl-turmas tbody'); 
        tbody.innerHTML = '';
        
        turmas.forEach(t => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${t.modalidade}</td>
                <td>${t.instrutor}</td>
                <td>${fmtDatePt(t.data)}</td>
                <td>${t.inicio}</td>
                <td>${t.fim}</td>
                <td>${t.vagas}</td>
                <td>${t.inscritos}</td>
                <td>
                    <button class="btn small" data-edit="${t.id}">Editar</button>
                    <button class="btn small outline" data-del="${t.id}">Excluir</button>
                </td>`;
            tbody.appendChild(tr);
        });
        
        tbody.onclick = (e) => {
            const id = e.target.dataset.edit || e.target.dataset.del;
            if(!id) return;

            if(e.target.dataset.edit) editTurma(id);
            if(e.target.dataset.del) {
                if(confirm('Excluir turma?')) {
                    delTurma(id);
                }
            }
        };
        
    } catch(error) {
        console.error('Error loading classes:', error);
        $('#tbl-turmas tbody').innerHTML = '<tr><td colspan="8" class="muted">Erro ao carregar turmas.</td></tr>';
    }
}

async function editTurma(id = null){
    try {
        let turma = null;
        if (id) {
            // Buscar dados da turma para editar
            const turmas = await apiCall('turmas.php');
            turma = turmas.find(t => t.id == id);
            if (!turma) {
                toast('Turma não encontrada', 'err');
                return;
            }
        }

        // Buscar modalidades para o select
        const modalidades = await apiCall('admin.php?action=modalidades');

        // Formulário simples para editar turma
        const modalidade = prompt('Modalidade:', turma ? turma.modalidade : modalidades[0]?.nome || '');
        if (modalidade === null) return;

        const instrutor = prompt('Instrutor:', turma ? turma.instrutor : '');
        if (instrutor === null) return;

        const data = prompt('Data (YYYY-MM-DD):', turma ? turma.data : ymd(new Date()));
        if (data === null) return;

        const inicio = prompt('Horário início (HH:MM):', turma ? turma.inicio : '08:00');
        if (inicio === null) return;

        const fim = prompt('Horário fim (HH:MM):', turma ? turma.fim : '09:00');
        if (fim === null) return;

        const vagas = prompt('Vagas:', turma ? turma.vagas : '20');
        if (vagas === null) return;

        if (turma) {
            // Atualizar turma existente
            await apiCall('turmas.php', {
                method: 'PUT',
                body: JSON.stringify({
                    id: id,
                    modalidade: modalidade.trim(),
                    instrutor: instrutor.trim(),
                    data: data.trim(),
                    inicio: inicio.trim(),
                    fim: fim.trim(),
                    vagas: parseInt(vagas)
                })
            });
            toast('Turma atualizada com sucesso!', 'ok');
        } else {
            // Criar nova turma
            await apiCall('turmas.php', {
                method: 'POST',
                body: JSON.stringify({
                    modalidade: modalidade.trim(),
                    instrutor: instrutor.trim(),
                    data: data.trim(),
                    inicio: inicio.trim(),
                    fim: fim.trim(),
                    vagas: parseInt(vagas)
                })
            });
            toast('Turma criada com sucesso!', 'ok');
        }

        // Recarregar lista
        await renderTurmas();

    } catch (error) {
        console.error('Error editing class:', error);
        toast('Erro ao salvar turma', 'err');
    }
}

async function renderRelatorios(){
    try {
        const relatorios = await apiCall('admin.php?action=relatorios');
        
        // Relatório de ocupação
        const containerOcupacao = $('#rel-ocupacao');
        containerOcupacao.innerHTML = relatorios.ocupacao.slice(0, 12).map(t => {
            const ratio = parseInt(t.inscritos) / Math.max(1, parseInt(t.vagas));
            const pct = Math.round(ratio * 100);
            return `
            <div class="card" style="padding:10px">
                <div class="inline justify-between">
                    <strong>${t.modalidade}</strong>
                    <span class="muted">${fmtDatePt(t.data)}</span>
                </div>
                <div class="bar" style="height:10px; background:rgba(255,255,255,.08); border-radius:8px; overflow:hidden">
                    <div style="height:10px; width:${pct}%; background:linear-gradient(90deg, var(--primary), var(--accent));"></div>
                </div>
                <small>${t.inscritos}/${t.vagas} — ${pct}%</small>
            </div>`;
        }).join('');
        
        // Relatório de frequência
        const containerFrequencia = $('#rel-frequencia');
        containerFrequencia.innerHTML = relatorios.frequencia.map(u => {
            const pct = Math.min(100, (u.checkins_mes || 0) * 5);
            return `
            <div class="card" style="padding:10px">
                <div class="inline justify-between">
                    <strong>${u.nome}</strong>
                    <span class="muted">${u.checkins_mes || 0} check-ins</span>
                </div>
                <div class="bar" style="height:10px; background:rgba(255,255,255,.08); border-radius:8px; overflow:hidden">
                    <div style="height:10px; width:${pct}%; background:linear-gradient(90deg, var(--success), var(--primary));"></div>
                </div>
            </div>`;
        }).join('');
        
    } catch(error) {
        console.error('Error loading reports:', error);
        $('#rel-ocupacao').innerHTML = '<div class="muted">Erro ao carregar relatórios.</div>';
        $('#rel-frequencia').innerHTML = '<div class="muted">Erro ao carregar relatórios.</div>';
    }
}

// ---------------------------
// Inicialização
// ---------------------------
(function init(){
    // DB.seed(); // Não é mais necessário com MySQL
    setSession(null);
    routes.login();
})();