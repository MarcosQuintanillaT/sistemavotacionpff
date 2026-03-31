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

function avatarPlaceholder(size = 64, fontSize = 13, initial = '') {
    return `<div style="width:${size}px;height:${size}px;border-radius:10px;background:linear-gradient(180deg,#1a2744 0%,#0f1c33 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;">
        <svg width="${Math.round(size * 0.4)}" height="${Math.round(size * 0.4)}" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="8" r="4" fill="rgba(212,165,32,0.4)"/>
            <path d="M4 20c0-3.314 3.582-6 8-6s8 2.686 8 6" stroke="rgba(212,165,32,0.4)" stroke-width="1.5" fill="rgba(212,165,32,0.15)"/>
        </svg>
        ${initial ? `<span style="font-size:${fontSize}px;color:var(--text-muted);font-family:Poppins,sans-serif;font-weight:500">${initial}</span>` : ''}
    </div>`;
}

function photoOrAvatar(fotoUrl, nombre, size = 40) {
    if (fotoUrl) {
        return `<img src="${fotoUrl}" alt="${nombre}" style="width:${size}px;height:${size}px;border-radius:10px;object-fit:cover;">`;
    }
    const initial = nombre.split(' ').map(n => n[0]).join('').slice(0, 2);
    return avatarPlaceholder(size, Math.round(size * 0.3), initial);
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

    const colors = ['#22c55e', '#4ade80', '#06b6d4', '#a3e635', '#16a34a', '#ef4444', '#0891b2', '#059669'];
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
            const partidoColor = c.partido_color || '#22c55e';
            const iniciales = c.nombre_candidato.split(' ').map(n => n[0]).join('').slice(0, 2);
            const fotoHTML = c.usuario_foto
                ? `<img src="${c.usuario_foto}" alt="${c.nombre_candidato}">`
                : avatarPlaceholder(64, 13, iniciales);

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
    updateStat('stat-total-votos', estadisticas.total_votos);
    updateStat('stat-participacion', estadisticas.participacion + '%');

    // Agrupar por cargo
    const porCargo = {};
    resultados.forEach(r => {
        if (!porCargo[r.cargo]) porCargo[r.cargo] = [];
        porCargo[r.cargo].push(r);
    });

    let html = '';
    const colors = ['#22c55e', '#06b6d4', '#a3e635', '#ef4444', '#4ade80', '#16a34a', '#0891b2'];

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

    const { estadisticas, votos_por_hora, participacion_grado, ultimos_votos, lideres } = result;

    // Stats
    updateStat('stat-votantes', estadisticas.total_votantes);
    updateStat('stat-votaron', estadisticas.total_votaron);
    updateStat('stat-candidatos', estadisticas.total_candidatos);
    updateStat('stat-partidos', estadisticas.total_partidos);

    // Gráfico de participación
    updateParticipationChart(estadisticas);

    // Gráfico de barras por grado
    updateGradoChart(participacion_grado);

    // Líderes
    updateLeaders(lideres);
}

function updateGradoChart(data) {
    const container = document.getElementById('grado-chart');
    if (!container || !data.length) return;

    const maxVal = Math.max(...data.map(d => parseInt(d.total_estudiantes)), 1);

    let html = '';
    data.forEach(d => {
        const pct = (d.votaron / d.total_estudiantes) * 100;
        const barPct = (d.votaron / maxVal) * 100;
        html += `
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span style="font-size:14px;font-weight:600;">${d.grado}</span>
                    <span style="font-size:12px;color:var(--text-muted);">${d.votaron} / ${d.total_estudiantes} (${pct.toFixed(1)}%)</span>
                </div>
                <div style="height:28px;background:rgba(255,255,255,0.05);border-radius:6px;overflow:hidden;">
                    <div style="height:100%;width:${barPct}%;background:linear-gradient(90deg,var(--primary),var(--accent));border-radius:6px;transition:width 1s;display:flex;align-items:center;padding:0 10px;font-size:12px;font-weight:600;">${d.votaron}</div>
                </div>
            </div>
        `;
    });

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
let votantesData = [];
let votantesSort = { col: null, dir: 'asc' };

async function loadVotantes() {
    const container = document.getElementById('votantes-table');
    if (!container) return;

    const result = await apiCall('usuarios.php');
    if (!result.success) return;

    votantesData = result.usuarios;
    votantesSort = { col: null, dir: 'asc' };
    renderVotantes();
}

function renderVotantes() {
    const container = document.getElementById('votantes-table');
    if (!container) return;

    const searchTerm = (document.getElementById('votantes-search')?.value || '').toLowerCase();
    const filterStatus = document.getElementById('filter-status')?.value || 'all';

    const columns = [
        { key: 'identidad', label: 'Identidad' },
        { key: 'nombre', label: 'Nombre' },
        { key: 'email', label: 'Email' },
        { key: 'grado', label: 'Grado/Sec' },
        { key: 'activo', label: 'Estado' },
        { key: 'ya_voto', label: 'Votó' }
    ];

    let data = [...votantesData];
    
    // Filtro de búsqueda
    if (searchTerm) {
        data = data.filter(u => 
            (u.nombre || '').toLowerCase().includes(searchTerm) ||
            (u.identidad || '').toLowerCase().includes(searchTerm) ||
            (u.email || '').toLowerCase().includes(searchTerm)
        );
    }
    
    // Filtro por estado
    if (filterStatus === 'voted') {
        data = data.filter(u => u.ya_voto);
    } else if (filterStatus === 'not-voted') {
        data = data.filter(u => !u.ya_voto);
    } else if (filterStatus === 'active') {
        data = data.filter(u => u.activo);
    } else if (filterStatus === 'inactive') {
        data = data.filter(u => !u.activo);
    }
    
    document.getElementById('votantes-count').textContent = `${data.length} de ${votantesData.length} votantees`;
    if (votantesSort.col !== null) {
        data.sort((a, b) => {
            let va = a[votantesSort.col] ?? '';
            let vb = b[votantesSort.col] ?? '';
            if (votantesSort.col === 'grado') {
                va = (a.grado || '') + (a.seccion || '');
                vb = (b.grado || '') + (b.seccion || '');
            }
            if (typeof va === 'number' && typeof vb === 'number') {
                return votantesSort.dir === 'asc' ? va - vb : vb - va;
            }
            va = String(va).toLowerCase();
            vb = String(vb).toLowerCase();
            return votantesSort.dir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
        });
    }

    function sortIcon(key) {
        if (votantesSort.col !== key) return ' ↕';
        return votantesSort.dir === 'asc' ? ' ↑' : ' ↓';
    }

    let html = '<div class="table-container"><table class="data-table"><thead><tr>';
    columns.forEach(c => {
        html += `<th style="cursor:pointer;user-select:none;" onclick="sortVotantes('${c.key}')">${c.label}${sortIcon(c.key)}</th>`;
    });
    html += '<th>Acciones</th></tr></thead><tbody>';
    data.forEach(u => {
        html += `<tr>
            <td><code>${u.identidad || '-'}</code></td>
            <td><strong>${u.nombre}</strong></td>
            <td>${u.email || '-'}</td>
            <td>${u.grado || '-'} / ${u.seccion || '-'}</td>
            <td><span class="status-badge ${u.activo ? 'active' : 'inactive'}">${u.activo ? 'Activo' : 'Inactivo'}</span></td>
            <td><span class="status-badge ${u.ya_voto ? 'voted' : 'not-voted'}">${u.ya_voto ? 'Sí' : 'No'}</span></td>
            <td>
                <button class="btn btn-outline btn-icon" onclick="editUser(${u.id})" title="Editar">✏️</button>
                <button class="btn btn-outline btn-icon" onclick="toggleUser(${u.id})" title="${u.activo ? 'Desactivar' : 'Activar'}">${u.activo ? '🔒' : '🔓'}</button>
            </td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function sortVotantes(col) {
    if (votantesSort.col === col) {
        votantesSort.dir = votantesSort.dir === 'asc' ? 'desc' : 'asc';
    } else {
        votantesSort.col = col;
        votantesSort.dir = 'asc';
    }
    renderVotantes();
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

async function editUser(id) {
    const result = await apiCall('usuarios.php');
    if (!result.success) return;

    const user = result.usuarios.find(u => u.id === id);
    if (!user) return;

    document.getElementById('edit-user-id').value = user.id;
    document.getElementById('edit-user-nombre').value = user.nombre;
    document.getElementById('edit-user-codigo').value = user.identidad || '';
    document.getElementById('edit-user-email').value = user.email;
    document.getElementById('edit-user-grado').value = user.grado || '';
    document.getElementById('edit-user-seccion').value = user.seccion || '';
    document.getElementById('edit-user-password').value = '';

    openModal('modal-edit-user');
}

async function updateUser(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('accion', 'editar');

    const result = await apiCall('usuarios.php', { method: 'POST', body: formData });
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        loadVotantes();
        closeModal('modal-edit-user');
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

    let html = '<div class="table-container"><table class="data-table"><thead><tr><th>Foto</th><th>#</th><th>Candidato</th><th>Cargo</th><th>Partido</th><th>Propuesta</th><th>Acciones</th></tr></thead><tbody>';
    result.candidatos.forEach(c => {
        html += `<tr>
            <td>${photoOrAvatar(c.usuario_foto, c.nombre_candidato, 40)}</td>
            <td>${c.numero_candidato || '-'}</td>
            <td><strong>${c.nombre_candidato}</strong></td>
            <td>${c.cargo}</td>
            <td><span style="display:inline-block;padding:2px 10px;border-radius:6px;background:${c.partido_color || '#22c55e'}22;color:${c.partido_color || '#22c55e'};font-size:12px;font-weight:600;">${c.partido || '-'}</span></td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${c.propuesta || '-'}</td>
            <td>
                <button class="btn btn-outline btn-icon" onclick="editCandidato(${c.id})" title="Editar">✏️</button>
                <button class="btn btn-outline btn-icon" onclick="deleteCandidato(${c.id})" title="Eliminar">🗑️</button>
            </td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    container.innerHTML = html || '<p class="text-muted">No hay candidatos registrados</p>';
}

async function editCandidato(id) {
    const result = await apiCall(`candidatos.php?id=${id}`);
    if (!result.success || !result.candidato) return;

    const c = result.candidato;
    document.getElementById('edit-candidato-id').value = c.id;
    document.getElementById('edit-candidato-nombre').value = c.nombre_candidato;
    document.getElementById('edit-candidato-cargo').value = c.cargo_id;
    document.getElementById('edit-candidato-partido').value = c.partido_id || '';
    document.getElementById('edit-candidato-numero').value = c.numero_candidato || '';
    document.getElementById('edit-candidato-propuesta').value = c.propuesta || '';

    const preview = document.getElementById('edit-candidato-foto-preview');
    if (c.usuario_foto) {
        preview.innerHTML = `<img src="${c.usuario_foto}" alt="Foto actual" style="width:60px;height:60px;border-radius:10px;object-fit:cover;border:1px solid var(--glass-border);">`;
    } else {
        preview.innerHTML = '';
    }

    openModal('modal-edit-candidato');
}

async function updateCandidato(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('accion', 'editar');

    const result = await apiCall('candidatos.php', { method: 'POST', body: formData });
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        loadCandidatosAdmin();
        closeModal('modal-edit-candidato');
    }
}

async function deleteCandidato(id) {
    if (!confirm('¿Estás seguro de eliminar este candidato?')) return;
    const result = await apiCall(`candidatos.php?id=${id}`, { method: 'DELETE' });
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) loadCandidatosAdmin();
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
// IMPORTAR EXCEL
// ==========================================
let excelData = [];

function previewExcel() {
    const fileInput = document.getElementById('excel-file');
    const file = fileInput.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const sheet = workbook.Sheets[workbook.SheetNames[0]];
            const rows = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' });

            excelData = [];

            let startRow = 0;
            if (rows.length > 0) {
                const firstRow = rows[0].map(c => String(c).toLowerCase().trim());
                if (firstRow.some(c => c.includes('nombre') || c.includes('email') || c.includes('correo') || c.includes('identidad') || c.includes('codigo'))) {
                    startRow = 1;
                }
            }

            for (let i = startRow; i < rows.length; i++) {
                const row = rows[i];
                const nombre = String(row[0] || '').trim();
                const identidad = String(row[1] || '').trim();
                const grado = String(row[2] || '').trim();
                const seccion = String(row[3] || '').trim();

                if (nombre || identidad) {
                    excelData.push({ nombre, email: '', identidad, grado, seccion });
                }
            }

            const tbody = document.querySelector('#excel-preview-table tbody');
            tbody.innerHTML = '';
            const preview = excelData.slice(0, 50);
            preview.forEach((v, i) => {
                tbody.innerHTML += `<tr><td>${i+1}</td><td>${v.nombre}</td><td>${v.identidad}</td><td>${v.grado}</td><td>${v.seccion}</td></tr>`;
            });
            if (excelData.length > 50) {
                tbody.innerHTML += `<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">... y ${excelData.length - 50} filas más</td></tr>`;
            }

            document.getElementById('excel-count').textContent = excelData.length + ' votantes encontrados';
            document.getElementById('excel-preview').style.display = 'block';
            document.getElementById('excel-result').style.display = 'none';
            document.getElementById('btn-import-excel').disabled = false;
        } catch (err) {
            showToast('Error al leer el archivo Excel', 'error');
            document.getElementById('excel-preview').style.display = 'none';
            document.getElementById('btn-import-excel').disabled = true;
        }
    };
    reader.readAsArrayBuffer(file);
}

async function importExcel() {
    if (excelData.length === 0) {
        showToast('No hay datos para importar', 'error');
        return;
    }

    const btn = document.getElementById('btn-import-excel');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="width:20px;height:20px;margin:0;border-width:2px;"></div> Importando...';

    const result = await apiCall('importar_votantes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ votantes: excelData })
    });

    btn.disabled = false;
    btn.innerHTML = '📥 Importar Votantes';

    const resultDiv = document.getElementById('excel-result');
    resultDiv.style.display = 'block';

    if (result.success) {
        resultDiv.innerHTML = `
            <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.2);border-radius:10px;padding:14px;">
                <p style="font-weight:700;color:var(--primary-light);margin-bottom:6px;">✅ ${result.message}</p>
                ${result.errores && result.errores.length > 0 ? `<p style="font-size:12px;color:var(--text-muted);margin-top:8px;">Detalle de omitidos:</p><ul style="font-size:11px;color:var(--text-dim);margin:4px 0 0 16px;">${result.errores.map(e => '<li>'+e+'</li>').join('')}</ul>` : ''}
            </div>`;
        showToast(result.message, 'success');
        loadVotantes();
        closeModal('modal-import-excel');
    } else {
        resultDiv.innerHTML = `
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:14px;">
                <p style="font-weight:700;color:var(--danger);">❌ ${result.message}</p>
            </div>`;
        showToast(result.message, 'error');
    }
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

    initMobileMenu();

    startAutoRefresh();
});

// ==========================================
// MENÚ MÓVIL
// ==========================================
function initMobileMenu() {
    const navbar = document.querySelector('.navbar');
    const overlay = document.querySelector('.navbar-overlay');
    const menuBtn = document.querySelector('.mobile-menu-btn');
    
    if (!navbar) return;
    
    // Crear botón de menú si no existe
    if (!menuBtn) {
        const btn = document.createElement('button');
        btn.className = 'mobile-menu-btn';
        btn.innerHTML = '☰';
        btn.setAttribute('aria-label', 'Abrir menú');
        document.body.appendChild(btn);
    }
    
    // Crear overlay si no existe
    if (!overlay) {
        const ov = document.createElement('div');
        ov.className = 'navbar-overlay';
        document.body.appendChild(ov);
    }
    
    const newMenuBtn = document.querySelector('.mobile-menu-btn');
    const newOverlay = document.querySelector('.navbar-overlay');
    
    function toggleMenu() {
        navbar.classList.toggle('active');
        newOverlay.classList.toggle('active');
        newMenuBtn.innerHTML = navbar.classList.contains('active') ? '✕' : '☰';
    }
    
    function closeMenu() {
        navbar.classList.remove('active');
        newOverlay.classList.remove('active');
        newMenuBtn.innerHTML = '☰';
    }
    
    newMenuBtn.addEventListener('click', toggleMenu);
    newOverlay.addEventListener('click', closeMenu);
    
    // Cerrar menú al cambiar tamaño
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) closeMenu();
    });
}
