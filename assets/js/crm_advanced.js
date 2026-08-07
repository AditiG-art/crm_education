/* ─────────────────────────────────────────────────
   Smart Campus CRM — Advanced JS Toolkit v3.0
───────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', () => {
    initMobileSidebar();
    initDropdowns();
    initGlobalSearch();
    initCounterAnimation();
});

/* ── Mobile Sidebar ─────────────────────────── */
function initMobileSidebar() {
    // Ensure overlay exists
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    function openSidebar() {
        document.querySelectorAll('.sidebar, #mainSidebar').forEach(s => s.classList.add('open'));
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        document.querySelectorAll('.sidebar, #mainSidebar').forEach(s => s.classList.remove('open'));
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Toggle and Close Click Listeners
    document.addEventListener('click', e => {
        const toggleBtn = e.target.closest('#mobileSidebarToggle, .mobile-sidebar-toggle');
        const closeBtn  = e.target.closest('#mobileSidebarClose, .mobile-sidebar-close');

        if (toggleBtn) {
            e.stopPropagation();
            e.preventDefault();
            const anyOpen = document.querySelector('.sidebar.open, #mainSidebar.open');
            if (anyOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        } else if (closeBtn) {
            closeSidebar();
        } else if (e.target === overlay) {
            closeSidebar();
        }
    });

    // Close mobile menu on link click
    document.querySelectorAll('.sidebar a, #mainSidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 992) {
                closeSidebar();
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) {
            closeSidebar();
        }
    });
}

/* ── Dropdowns ──────────────────────────────── */
function initDropdowns() {
    document.querySelectorAll('.notification-trigger, .profile-trigger').forEach(trigger => {
        trigger.addEventListener('click', e => {
            e.stopPropagation();
            const container = trigger.closest('.crm-dropdown-container');
            const menu = container?.querySelector('.crm-dropdown-menu');
            if (!menu) return;
            // Close all others
            document.querySelectorAll('.crm-dropdown-menu.open').forEach(m => {
                if (m !== menu) m.classList.remove('open');
            });
            menu.classList.toggle('open');
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.crm-dropdown-menu.open').forEach(m => m.classList.remove('open'));
    });
}

/* ── Global Quick Search ─────────────────────── */
function initGlobalSearch() {
    const input    = document.getElementById('globalSearchInput');
    const dropdown = document.getElementById('searchResultsDropdown');
    if (!input || !dropdown) return;

    const pages = [
        { label: 'Dashboard',       icon: 'fa-chart-pie',          url: '../admin/dashboard.php' },
        { label: 'Students',        icon: 'fa-user-graduate',       url: '../admin/students.php' },
        { label: 'Add Student',     icon: 'fa-user-plus',           url: '../admin/add_student.php' },
        { label: 'Teachers',        icon: 'fa-chalkboard-user',     url: '../admin/teachers.php' },
        { label: 'Courses',         icon: 'fa-book-open',           url: '../admin/courses.php' },
        { label: 'Attendance',      icon: 'fa-calendar-check',      url: '../admin/attendance.php' },
        { label: 'Mark Attendance', icon: 'fa-calendar-plus',       url: '../admin/mark_attendance.php' },
        { label: 'Results',         icon: 'fa-chart-bar',           url: '../admin/results.php' },
        { label: 'Achievements',    icon: 'fa-trophy',              url: '../admin/achievements.php' },
        { label: 'Announcements',   icon: 'fa-bullhorn',            url: '../admin/announcements.php' },
        { label: 'Timetable',       icon: 'fa-table-cells',         url: '../teacher/timetable.php' },
        { label: 'Marks Schedule',  icon: 'fa-calendar-days',       url: '../teacher/marks_schedule.php' },
    ];

    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        if (q.length < 1) { dropdown.classList.remove('active'); return; }
        const results = pages.filter(p => p.label.toLowerCase().includes(q));
        if (results.length === 0) { dropdown.classList.remove('active'); return; }
        dropdown.innerHTML = results.slice(0, 6).map(p =>
            `<div class="search-result-item" onclick="location.href='${p.url}'">
                <i class="fa-solid ${p.icon}" style="color:var(--primary);width:16px;"></i>
                <span>${p.label}</span>
            </div>`
        ).join('');
        dropdown.classList.add('active');
    });
    document.addEventListener('click', e => {
        if (!input.contains(e.target)) dropdown.classList.remove('active');
    });
}

/* ── Counter Animation ───────────────────────── */
function initCounterAnimation() {
    document.querySelectorAll('.counter').forEach(el => {
        const target = +el.getAttribute('data-target');
        if (!target) { el.textContent = target; return; }
        const speed = Math.max(1, Math.ceil(target / 50));
        let count = 0;
        (function tick() {
            count += speed;
            if (count >= target) { el.textContent = target; return; }
            el.textContent = count;
            setTimeout(tick, 25);
        })();
    });
}

/* ── CSV Export ──────────────────────────────── */
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    const rows = [...table.querySelectorAll('tr')];
    const csv  = rows.map(r =>
        [...r.querySelectorAll('th,td')]
            .map(c => '"' + c.innerText.trim().replace(/"/g, '""') + '"')
            .join(',')
    ).join('\n');
    const a = Object.assign(document.createElement('a'), {
        href: 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv),
        download: filename
    });
    a.click();
}

/* ── Print Helper ────────────────────────────── */
function printPage() { window.print(); }

/* ── Toast Notifications ─────────────────────── */
function showToast(message, type = 'info', duration = 3500) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = Object.assign(document.createElement('div'), { id: 'toastContainer' });
        document.body.appendChild(container);
    }
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const colors = { success: 'var(--success)', error: 'var(--danger)', warning: 'var(--warning)', info: 'var(--primary)' };
    const toast = document.createElement('div');
    toast.className = `crm-toast toast-${type}`;
    toast.innerHTML = `
        <i class="fa-solid ${icons[type] || icons.info}" style="color:${colors[type]};font-size:18px;"></i>
        <span class="toast-text">${message}</span>
        <span class="toast-close" onclick="this.parentElement.remove()">✕</span>
    `;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), duration);
}

/* ── Quick View Modal ─────────────────────────── */
function showQuickViewModal(title, contentHtml) {
    let overlay = document.getElementById('crmQuickViewOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'crmQuickViewOverlay';
        overlay.className = 'crm-modal-overlay';
        overlay.innerHTML = `
            <div class="crm-modal">
                <div class="crm-modal-header">
                    <h5 id="qvTitle"><i class="fa-solid fa-eye"></i> Quick View</h5>
                    <button class="crm-modal-close" onclick="document.getElementById('crmQuickViewOverlay').classList.remove('open')">✕</button>
                </div>
                <div class="crm-modal-body" id="qvBody"></div>
                <div class="crm-modal-footer">
                    <button class="btn-crm-print" onclick="document.getElementById('crmQuickViewOverlay').classList.remove('open')">Close</button>
                </div>
            </div>`;
        document.body.appendChild(overlay);
    }
    document.getElementById('qvTitle').innerHTML = `<i class="fa-solid fa-eye"></i> ${title}`;
    document.getElementById('qvBody').innerHTML = contentHtml;
    overlay.classList.add('open');
}
