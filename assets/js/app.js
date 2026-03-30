// ==========================================
// SISTEMA DE ELECCIONES ESTUDIANTILES
// JavaScript Principal
// ==========================================

// Se establece desde PHP en cada página (fallback para compatibilidad)
const API_BASE = window.API_BASE_URL || 'api';

// ==========================================
// UTILIDADES
// ==========================================
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    toast.innerHTML = `<span>${icons[type] || 'ℹ'}</span> ${message}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'toastSlide 0.3s reverse';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

async function apiCall(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE}/${endpoint}`, options);
        const data = await response.json();
        return data;
    } catch (error) {
        showToast('Error de conexión', 'error');
        return { success: false, message: 'Error de conexión' };
    }
}

function showLoading() {
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay';
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.remove();
}

// ==========================================
// CONFETTI
// ==========================================
function launchConfetti() {
    const container = document.createElement('div');
    container.className = 'confetti-container';
    document.body.appendChild(container);

    const colors = ['#d4a520', '#f0c040', '#4a9eff', '#64ffda', '#b8860b', '#ff6b6b', '#2d7dd2', '#00bfa5'];
    const shapes = ['square', 'circle'];

    for (let i = 0; i < 150; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti-piece';
        const color = colors[Math.floor(Math.random() * colors.length)];
        const shape = shapes[Math.floor(Math.random() * shapes.length)];
        const size = Math.random() * 10 + 5;
        const left = Math.random() * 100;
        const delay = Math.random() * 3;
        const duration = Math.random() * 3 + 2;

        confetti.style.cssText = `
            left: ${left}%;
            width: ${size}px;
            height: ${size}px;
            background: ${color};
            border-radius: ${shape === 'circle' ? '50%' : '2px'};
            animation: confettiFall ${duration}s ease-in ${delay}s forwards;
        `;
        container.appendChild(confetti);
    }

    setTimeout(() => container.remove(), 6000);
}

// ==========================================
// AUTH
// ==========================================
function switchAuthTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-form').forEach(f => f.classList.add('hidden'));
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
    document.getElementById(`form-${tab}`).classList.remove('hidden');
}

async function handleLogin(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    showLoading();

    const result = await apiCall('login.php', { method: 'POST', body: formData });
    hideLoading();

    if (result.success) {
        showToast('¡Bienvenido!', 'success');
        setTimeout(() => window.location.href = result.redirect, 800);
    } else {
        showToast(result.message, 'error');
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    showLoading();

    const result = await apiCall('registro.php', { method: 'POST', body: formData });
    hideLoading();

    if (result.success) {
        showToast(result.message, 'success');
        e.target.reset();
        switchAuthTab('login');
    } else {
        showToast(result.message, 'error');
    }
}

// ==========================================
// VOTACIÓN
// ==========================================
let selectedCandidates = {};

async function cargarVotacion() {
    const container = document.getElementById('votacion-container');
    if (!container) return;

    const result = await apiCall('cargos.php');
    if (!result.success) return;

    // Verificar votos existentes
    const resultadosResult = await apiCall('resultados.php');
    const yaVotoCargos = new Set();

    if (resultadosResult.success && resultadosResult.resultados) {
        // Obtener candidatos por los que ya voté
        for (const r of resultadosResult.resultados) {
            // Verificamos más adelante
        }
    }

    let html = '';
    for (const cargo of result.cargos) {
        const candidatosResult = await apiCall(`candidatos.php?cargo=${cargo.id}`);
        if (!candidatosResult.success) continue;

        const candidatos = candidatosResult.candidatos;
        if (candidatos.length === 0) continue;

        html += `
            <div class="cargos-section slide-up" data-cargo="${cargo.id}">
                <div class="cargo-header">
                    <h2>${getCargoIcon(cargo.nombre)} ${cargo.nombre}</h2>
                    <span class="badge">${candidatos.length} candidato${candidatos.length > 1 ? 's' : ''}</span>
                </div>
                <div class="candidatos-grid">
        `;

        for (const c of candidatos) {
            const partidoColor = c.partido_color || '#d4a520';
            const iniciales = c.nombre_candidato.split(' ').map(n => n[0]).join('').slice(0, 2);
            const fotoHTML = c.usuario_foto
                ? `<img src="${c.usuario_foto}" alt="${c.nombre_candidato}">`
                : `<div style="width:100%;height:100%;background:linear-gradient(135deg,${partidoColor},${partidoColor}88);display:flex;align-items:center;justify-content:center;font-size:48px;font-weight:700">${iniciales}</div>`;

            html += `
                <div class="candidato-card glass" onclick="selectCandidate(${c.id}, ${cargo.id}, '${c.nombre_candidato}')" data-candidato="${c.id}">
                    <div class="candidato-foto">
                        ${fotoHTML}
                        ${c.numero_candidato ? `<div class="candidato-numero">#${c.numero_candidato}</div>` : ''}
                        ${c.partido ? `<div class="partido-badge" style="background:${partidoColor}">${c.partido}</div>` : ''}
                        <div class="vote-indicator">✓</div>
                    </div>
                    <div class="candidato-info">
                        <h3>${c.nombre_candidato}</h3>
                        <div class="cargo-tag">${c.grado || ''} ${c.seccion || ''}</div>
                        <div class="propuesta">${c.propuesta || 'Sin propuesta registrada'}</div>
                    </div>
                </div>
            `;
        }

        html += '</div></div>';
    }

    // Verificar votos ya emitidos
    const misVotosResult = await apiCall('mis_votos.php');
    if (misVotosResult.success && misVotosResult.votos) {
        for (const voto of misVotosResult.votos) {
            const card = document.querySelector(`[data-candidato="${voto.candidato_id}"]`);
            if (card) {
                card.classList.add('voted');
                selectedCandidates[voto.cargo_id] = voto.candidato_id;
            }
        }
    }

    container.innerHTML = html || '<div class="glass" style="padding:40px;text-align:center"><p>No hay candidatos registrados aún</p></div>';
}

function selectCandidate(candidatoId, cargoId, nombre) {
    // Deseleccionar otros del mismo cargo
    document.querySelectorAll(`[data-cargo="${cargoId}"] .candidato-card`).forEach(card => {
        card.classList.remove('selected');
    });

    // Seleccionar este
    const card = document.querySelector(`[data-candidato="${candidatoId}"]`);
    if (card) {
        card.classList.add('selected');
        selectedCandidates[cargoId] = candidatoId;

        // Animación
        card.style.transform = 'scale(1.02)';
        setTimeout(() => card.style.transform = '', 200);
    }
}

async function emitirVotos() {
    const votos = Object.entries(selectedCandidates);
    if (votos.length === 0) {
        showToast('Selecciona al menos un candidato', 'error');
        return;
    }

    showLoading();
    let successCount = 0;
    let errors = [];

    for (const [cargoId, candidatoId] of votos) {
        const result = await apiCall('votar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ candidato_id: candidatoId })
        });

        if (result.success) {
            successCount++;
        } else {
            errors.push(result.message);
        }
    }

    hideLoading();

    if (successCount > 0) {
        // Mostrar overlay de éxito
        showVoteSuccess(successCount);
    } else {
        showToast(errors[0] || 'Error al votar', 'error');
    }
}

function showVoteSuccess(count) {
    const overlay = document.getElementById('vote-success-overlay');
    if (overlay) {
        overlay.classList.add('active');
        launchConfetti();
        setTimeout(() => {
            overlay.classList.remove('active');
            cargarVotacion();
        }, 4000);
    } else {
        launchConfetti();
        showToast(`¡${count} voto(s) registrado(s) exitosamente!`, 'success');
        setTimeout(() => cargarVotacion(), 2000);
    }
}

function getCargoIcon(nombre) {
    const icons = {
        'Presidente/a': '👑',
        'Vicepresidente/a': '⭐',
        'Secretario/a General': '📝',
        'Tesorero/a': '💰',
        'Vocal 1': '🎭',
        'Vocal 2': '⚽'
    };
    return icons[nombre] || '🏛️';
}

// ==========================================
// RESULTADOS
// ==========================================
async function cargarResultados() {
    const container = document.getElementById('resultados-container');
    if (!container) return;

    const result = await apiCall('resultados.php');
    if (!result.success) return;

    const { resultados, estadisticas } = result;

    // Actualizar stats
    updateStat('stat-votantes', estadisticas.total_votantes);
    updateStat('stat-votaron', estadisticas.total_votaron);
    updateStat('stat-participacion', estadisticas.participacion + '%');

    // Agrupar por cargo
    const porCargo = {};
    resultados.forEach(r => {
        if (!porCargo[r.cargo]) porCargo[r.cargo] = [];
        porCargo[r.cargo].push(r);
    });

    let html = '';
    const colors = ['#d4a520', '#4a9eff', '#64ffda', '#ff6b6b', '#f0c040', '#b8860b', '#2d7dd2'];

    for (const [cargo, candidatos] of Object.entries(porCargo)) {
        const maxVotos = Math.max(...candidatos.map(c => parseInt(c.total_votos)), 1);

        html += `
            <div class="chart-container glass slide-up mb-30">
                <h3>${getCargoIcon(cargo)} ${cargo}</h3>
                <div style="padding-top:8px">
        `;

        candidatos.forEach((c, i) => {
            const pct = maxVotos > 0 ? ((parseInt(c.total_votos) / estadisticas.total_votaron) * 100) : 0;
            const color = c.partido_color || colors[i % colors.length];
            const isLeading = i === 0 && c.total_votos > 0;

            html += `
                <div class="resultado-bar">
                    <div class="resultado-info">
                        <span class="resultado-nombre" style="${isLeading ? 'color:' + color : ''}">
                            ${isLeading ? '🏆 ' : ''}${c.nombre_candidato}
                            ${c.partido ? `<span class="text-muted" style="font-size:12px"> — ${c.partido}</span>` : ''}
                        </span>
                        <span class="resultado-votos">${c.total_votos} votos (${pct.toFixed(1)}%)</span>
                    </div>
                    <div class="resultado-progress">
                        <div class="resultado-fill ${isLeading ? 'animated' : ''}" style="width:${pct}%;background:linear-gradient(90deg,${color},${color}88);"></div>
                    </div>
                </div>
            `;
        });

        html += '</div></div>';
    }

    container.innerHTML = html || '<div class="glass" style="padding:40px;text-align:center"><p>No hay resultados aún</p></div>';

    // Actualizar gráfico de participación
    updateParticipationChart(estadisticas);
}

function updateStat(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function updateParticipationChart(stats) {
    const ring = document.getElementById('participation-ring');
    if (!ring) return;

    const circumference = 2 * Math.PI * 52;
    const offset = circumference - (stats.participacion / 100) * circumference;

    const fill = ring.querySelector('.progress-fill');
    if (fill) {
        fill.style.strokeDasharray = circumference;
        fill.style.strokeDashoffset = offset;
    }

    const value = ring.querySelector('.progress-value');
    if (value) value.textContent = stats.participacion + '%';
}

// ==========================================
// ADMIN DASHBOARD
// ==========================================
async function loadAdminDashboard() {
    const statsContainer = document.getElementById('admin-stats');
    if (!statsContainer) return;

    const result = await apiCall('estadisticas.php');
    if (!result.success) return;

    const { estadisticas, votos_por_hora, ultimos_votos, lideres } = result;

    // Stats
    updateStat('stat-votantes', estadisticas.total_votantes);
    updateStat('stat-votaron', estadisticas.total_votaron);
    updateStat('stat-candidatos', estadisticas.total_candidatos);
    updateStat('stat-partidos', estadisticas.total_partidos);

    // Gráfico de participación
    updateParticipationChart(estadisticas);

    // Gráfico de barras por hora
    updateHourlyChart(votos_por_hora);

    // Últimos votos
    updateRecentVotes(ultimos_votos);

    // Líderes
    updateLeaders(lideres);
}

function updateHourlyChart(data) {
    const container = document.getElementById('hourly-chart');
    if (!container || !data.length) return;

    const maxVal = Math.max(...data.map(d => parseInt(d.cantidad)), 1);

    let html = '';
    data.forEach(d => {
        const pct = (d.cantidad / maxVal) * 100;
        html += `
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                <span style="width:50px;font-size:12px;color:var(--text-muted)">${d.hora}</span>
                <div style="flex:1;height:24px;background:rgba(255,255,255,0.05);border-radius:6px;overflow:hidden;">
                    <div style="height:100%;width:${pct}%;background:linear-gradient(90deg,var(--primary),var(--accent));border-radius:6px;transition:width 1s;display:flex;align-items:center;padding:0 8px;font-size:11px;font-weight:600;">${d.cantidad}</div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function updateRecentVotes(data) {
    const container = document.getElementById('recent-votes');
    if (!container) return;

    if (!data.length) {
        container.innerHTML = '<p class="text-muted text-center" style="padding:20px">No hay votos registrados aún</p>';
        return;
    }

    let html = '<div class="table-container"><table class="data-table"><thead><tr><th>Hora</th><th>Votante</th><th>Cargo</th><th>Candidato</th></tr></thead><tbody>';
    data.forEach(v => {
        html += `<tr><td>${v.hora}</td><td>${v.votante}</td><td>${v.cargo}</td><td>${v.candidato_votado}</td></tr>`;
    });
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function updateLeaders(data) {
    const container = document.getElementById('leaders-container');
    if (!container || !data.length) return;

    const porCargo = {};
    data.forEach(l => {
        if (!porCargo[l.cargo]) porCargo[l.cargo] = [];
        porCargo[l.cargo].push(l);
    });

    let html = '';
    for (const [cargo, candidatos] of Object.entries(porCargo)) {
        candidatos.sort((a, b) => parseInt(b.votos) - parseInt(a.votos));
        const lider = candidatos[0];
        html += `
            <div style="padding:16px;border-bottom:1px solid rgba(255,255,255,0.05);display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">${cargo}</div>
                    <div style="font-weight:600;margin-top:4px;">${lider.votos > 0 ? '🏆 ' : ''}${lider.lider}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:20px;font-weight:800;color:${lider.color || 'var(--primary-light)'}">${lider.votos}</div>
                    <div style="font-size:11px;color:var(--text-muted)">votos</div>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
}

// ==========================================
// ADMIN: VOTANTES
// ==========================================
async function loadVotantes() {
    const container = document.getElementById('votantes-table');
    if (!container) return;

    const result = await apiCall('usuarios.php');
    if (!result.success) return;

    let html = '<div class="table-container"><table class="data-table"><thead><tr><th>Código</th><th>Nombre</th><th>Email</th><th>Grado/Sec</th><th>Estado</th><th>Votó</th><th>Acciones</th></tr></thead><tbody>';
    result.usuarios.forEach(u => {
        html += `<tr>
            <td><code>${u.codigo_estudiantil || '-'}</code></td>
            <td><strong>${u.nombre}</strong></td>
            <td>${u.email}</td>
            <td>${u.grado || '-'} / ${u.seccion || '-'}</td>
            <td><span class="status-badge ${u.activo ? 'active' : 'inactive'}">${u.activo ? 'Activo' : 'Inactivo'}</span></td>
            <td><span class="status-badge ${u.ya_voto ? 'voted' : 'not-voted'}">${u.ya_voto ? 'Sí' : 'No'}</span></td>
            <td>
                <button class="btn btn-outline btn-icon" onclick="toggleUser(${u.id})" title="${u.activo ? 'Desactivar' : 'Activar'}">${u.activo ? '🔒' : '🔓'}</button>
            </td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

async function toggleUser(id) {
    const formData = new FormData();
    formData.append('accion', 'toggle');
    formData.append('id', id);

    const result = await apiCall('usuarios.php', { method: 'POST', body: formData });
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) loadVotantes();
}

async function addUser(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    const result = await apiCall('usuarios.php', { method: 'POST', body: formData });
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        e.target.reset();
        loadVotantes();
        closeModal('modal-add-user');
    }
}

// ==========================================
// ADMIN: CANDIDATOS
// ==========================================
async function loadCandidatosAdmin() {
    const container = document.getElementById('candidatos-admin-table');
    if (!container) return;

    const result = await apiCall('candidatos.php');
    if (!result.success) return;

    let html = '<div class="table-container"><table class="data-table"><thead><tr><th>#</th><th>Candidato</th><th>Cargo</th><th>Partido</th><th>Propuesta</th></tr></thead><tbody>';
    result.candidatos.forEach(c => {
        html += `<tr>
            <td>${c.numero_candidato || '-'}</td>
            <td><strong>${c.nombre_candidato}</strong></td>
            <td>${c.cargo}</td>
            <td><span style="display:inline-block;padding:2px 10px;border-radius:6px;background:${c.partido_color || '#d4a520'}22;color:${c.partido_color || '#d4a520'};font-size:12px;font-weight:600;">${c.partido || '-'}</span></td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${c.propuesta || '-'}</td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    container.innerHTML = html || '<p class="text-muted">No hay candidatos registrados</p>';
}

// ==========================================
// MODALS
// ==========================================
function openModal(id) {
    document.getElementById(id)?.classList.add('active');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('active');
}

// ==========================================
// AUTO REFRESH
// ==========================================
function startAutoRefresh() {
    setInterval(() => {
        if (document.getElementById('resultados-container')) cargarResultados();
        if (document.getElementById('admin-stats')) loadAdminDashboard();
    }, 10000); // cada 10 segundos
}

// ==========================================
// INICIALIZACIÓN
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    createToastContainer();

    // Inicializar según la página
    if (document.getElementById('votacion-container')) cargarVotacion();
    if (document.getElementById('resultados-container')) cargarResultados();
    if (document.getElementById('admin-stats')) loadAdminDashboard();
    if (document.getElementById('votantes-table')) loadVotantes();
    if (document.getElementById('candidatos-admin-table')) loadCandidatosAdmin();

    startAutoRefresh();
});
