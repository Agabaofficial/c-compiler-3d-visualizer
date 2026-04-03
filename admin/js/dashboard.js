/**
 * admin/js/dashboard.js
 * Admin dashboard – API calls, stats, user management, charts, logs.
 */

(function () {
    'use strict';

    // ------------------------------------------------------------------
    // Config
    // ------------------------------------------------------------------
    const API_BASE      = '/api/v1';
    const REFRESH_MS    = 60_000; // Auto-refresh every 60 seconds
    const MAX_LOG_LINES = 500;

    let accessToken  = sessionStorage.getItem('admin_jwt') || '';
    let currentPage  = 1;
    let userSearch   = '';
    let userRole     = '';
    let dailyChart, langChart, analyticsChart;

    // ------------------------------------------------------------------
    // Auth helpers
    // ------------------------------------------------------------------

    function authHeaders() {
        const h = { 'Content-Type': 'application/json' };
        if (accessToken) h['Authorization'] = 'Bearer ' + accessToken;
        return h;
    }

    async function apiFetch(path, opts = {}) {
        const res = await fetch(API_BASE + path, {
            headers: authHeaders(),
            ...opts,
        });
        if (res.status === 401 || res.status === 403) {
            showError('Session expired or access denied. Please log in again.');
            return null;
        }
        return res.json();
    }

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------

    function showSection(name) {
        document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

        const section = document.getElementById('section-' + name);
        if (section) section.classList.add('active');

        const nav = document.querySelector('[data-section="' + name + '"]');
        if (nav) nav.classList.add('active');

        document.getElementById('pageTitle').textContent =
            name.charAt(0).toUpperCase() + name.slice(1);

        // Load data for the activated section
        switch (name) {
            case 'dashboard': loadDashboard(); break;
            case 'users':     loadUsers();     break;
            case 'analytics': loadAnalytics(); break;
            case 'logs':      loadLogs();      break;
        }
    }

    // ------------------------------------------------------------------
    // Dashboard
    // ------------------------------------------------------------------

    async function loadDashboard() {
        const data = await apiFetch('/admin/stats');
        if (!data || !data.success) return;

        const d = data.data;
        setText('statUsers',        d.users         ?? '—');
        setText('statCompilations', d.compilations  ?? '—');
        setText('statSessions',     d.system_stats?.totals?.unique_users ?? '—');
        setText('statErrors',       countRecentErrors(d));

        renderDailyChart(d.daily || []);
        renderLanguageChart(d.session_stats?.by_language || []);
    }

    function countRecentErrors(stats) {
        // Use total error compilations as a proxy
        const byStatus = stats.session_stats?.by_status || [];
        const errRow   = byStatus.find(r => r.status === 'error');
        return errRow ? errRow.total : 0;
    }

    function renderDailyChart(rows) {
        const ctx = document.getElementById('dailyChart');
        if (!ctx) return;

        const labels = rows.map(r => r.day);
        const values = rows.map(r => r.sessions);

        if (dailyChart) dailyChart.destroy();

        dailyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Sessions',
                    data: values,
                    borderColor: '#00ff9d',
                    backgroundColor: 'rgba(0,255,157,0.1)',
                    tension: 0.4,
                    fill: true,
                }],
            },
            options: chartOptions('Sessions per Day'),
        });
    }

    function renderLanguageChart(rows) {
        const ctx = document.getElementById('languageChart');
        if (!ctx) return;

        const labels = rows.map(r => r.language);
        const values = rows.map(r => r.total);

        if (langChart) langChart.destroy();

        langChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#00ff9d', '#6c63ff', '#ff6b6b', '#ffd93d'],
                }],
            },
            options: {
                plugins: { legend: { labels: { color: '#e0e0e0' } } },
            },
        });
    }

    // ------------------------------------------------------------------
    // Users
    // ------------------------------------------------------------------

    async function loadUsers(page = 1) {
        currentPage = page;
        const params = new URLSearchParams({ page, limit: 20 });
        const data   = await apiFetch('/admin/users?' + params);
        if (!data || !data.success) return;

        const { users, total, limit } = data.data;
        const filtered = filterUsers(users);

        renderUsersTable(filtered);
        renderPagination('usersPagination', page, Math.ceil(total / limit), loadUsers);
    }

    function filterUsers(users) {
        return users.filter(u => {
            const matchSearch = !userSearch ||
                u.username.toLowerCase().includes(userSearch) ||
                u.email.toLowerCase().includes(userSearch);
            const matchRole = !userRole || u.role === userRole;
            return matchSearch && matchRole;
        });
    }

    function renderUsersTable(users) {
        const tbody = document.getElementById('usersTableBody');
        if (!tbody) return;

        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="empty-cell">No users found.</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(u => `
            <tr data-id="${u.id}">
                <td>${u.id}</td>
                <td>${esc(u.username)}</td>
                <td>${esc(u.email)}</td>
                <td><span class="badge badge-${u.role}">${u.role}</span></td>
                <td><span class="badge badge-${u.is_banned ? 'banned' : 'active'}">${u.is_banned ? 'Banned' : 'Active'}</span></td>
                <td>${formatDate(u.created_at)}</td>
                <td class="actions">
                    <button class="btn btn-xs btn-outline" onclick="toggleBan(${u.id}, ${u.is_banned})">
                        ${u.is_banned ? 'Unban' : 'Ban'}
                    </button>
                    <button class="btn btn-xs btn-danger" onclick="deleteUser(${u.id}, '${esc(u.username)}')">
                        Delete
                    </button>
                </td>
            </tr>`).join('');
    }

    window.toggleBan = async function (id, isBanned) {
        const newVal = isBanned ? 0 : 1;
        const res    = await apiFetch('/admin/users/' + id, {
            method: 'PUT',
            body:   JSON.stringify({ is_banned: newVal }),
        });
        if (res && res.success) loadUsers(currentPage);
        else showError(res?.error || 'Update failed');
    };

    window.deleteUser = async function (id, name) {
        if (!confirm(`Delete user "${name}"? This cannot be undone.`)) return;
        const res = await apiFetch('/admin/users/' + id, { method: 'DELETE' });
        if (res && res.success) loadUsers(currentPage);
        else showError(res?.error || 'Delete failed');
    };

    // ------------------------------------------------------------------
    // Analytics
    // ------------------------------------------------------------------

    async function loadAnalytics() {
        const data = await apiFetch('/admin/stats');
        if (!data || !data.success) return;

        const langs = data.data.system_stats?.language_stats || [];
        const ctx   = document.getElementById('analyticsLangChart');
        if (ctx) {
            if (analyticsChart) analyticsChart.destroy();
            analyticsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels:   langs.map(r => r.language),
                    datasets: [{
                        label: 'Total Sessions',
                        data:  langs.map(r => r.total),
                        backgroundColor: '#6c63ff',
                    }],
                },
                options: chartOptions('Sessions by Language'),
            });
        }

        const detail = document.getElementById('analyticsStats');
        if (detail) {
            const totals = data.data.system_stats?.totals || {};
            detail.innerHTML = `
                <div class="stat-row">
                    <span>Total Sessions</span><span>${totals.total_sessions ?? 0}</span>
                </div>
                <div class="stat-row">
                    <span>Unique Users</span><span>${totals.unique_users ?? 0}</span>
                </div>
                <div class="stat-row">
                    <span>Avg Duration</span><span>${totals.avg_duration ?? 0}s</span>
                </div>`;
        }
    }

    // ------------------------------------------------------------------
    // Logs
    // ------------------------------------------------------------------

    async function loadLogs() {
        const type  = document.getElementById('logTypeSelect')?.value  || 'error';
        const lines = document.getElementById('logLinesSelect')?.value || '100';
        const data  = await apiFetch(`/admin/logs?type=${type}&lines=${lines}`);
        const viewer = document.getElementById('logViewer');
        if (!viewer) return;

        if (!data || !data.success) {
            viewer.innerHTML = '<div class="log-placeholder text-error">Failed to load logs.</div>';
            return;
        }

        const entries = data.data.entries || [];
        if (entries.length === 0) {
            viewer.innerHTML = '<div class="log-placeholder">Log is empty.</div>';
            return;
        }

        viewer.innerHTML = entries.map(line => {
            const cls = line.includes('[error]') ? 'log-error'
                      : line.includes('[warning]') ? 'log-warning'
                      : 'log-info';
            return `<div class="log-line ${cls}">${esc(line)}</div>`;
        }).join('');

        viewer.scrollTop = 0;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    function chartOptions(title) {
        return {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#e0e0e0' } },
                title:  { display: false },
            },
            scales: {
                x: { ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } },
            },
        };
    }

    function renderPagination(containerId, current, total, callback) {
        const el = document.getElementById(containerId);
        if (!el || total <= 1) { if (el) el.innerHTML = ''; return; }

        let html = '';
        if (current > 1)     html += `<button onclick="(${callback})(${current - 1})">‹ Prev</button>`;
        html += `<span> Page ${current} / ${total} </span>`;
        if (current < total) html += `<button onclick="(${callback})(${current + 1})">Next ›</button>`;
        el.innerHTML = html;
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function showError(msg) {
        console.error(msg);
        // Simple banner
        const banner = document.createElement('div');
        banner.className = 'toast toast-error';
        banner.textContent = msg;
        document.body.appendChild(banner);
        setTimeout(() => banner.remove(), 5000);
    }

    // ------------------------------------------------------------------
    // Event listeners
    // ------------------------------------------------------------------

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', e => {
            e.preventDefault();
            showSection(item.dataset.section);
        });
    });

    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.toggle('collapsed');
    });

    document.getElementById('refreshBtn')?.addEventListener('click', () => {
        const active = document.querySelector('.nav-item.active')?.dataset.section || 'dashboard';
        showSection(active);
    });

    document.getElementById('userSearch')?.addEventListener('input', e => {
        userSearch = e.target.value.toLowerCase();
        loadUsers(1);
    });

    document.getElementById('userRoleFilter')?.addEventListener('change', e => {
        userRole = e.target.value;
        loadUsers(1);
    });

    document.getElementById('refreshLogsBtn')?.addEventListener('click', loadLogs);

    // ------------------------------------------------------------------
    // Boot
    // ------------------------------------------------------------------

    showSection('dashboard');

    // Auto-refresh
    setInterval(() => {
        const active = document.querySelector('.nav-item.active')?.dataset.section || 'dashboard';
        if (active === 'dashboard') loadDashboard();
    }, REFRESH_MS);

})();
