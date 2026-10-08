/* ============================================================================
   CONCEPT ADMIN — admin.js
   AJAX без SSE (без живой подгрузки заявок)
   ============================================================================ */

(function () {
    'use strict';

    const CSRF = window.__csrf || '';
    const API  = 'api.php';

    /* ============================================================
       УТИЛИТЫ
       ============================================================ */

    function notify(message, type = 'success') {
        const holder = document.getElementById('adminToast');
        if (!holder) return;

        holder.textContent = message;
        holder.className = 'admin-toast show ' + (type === 'error' ? 'error' : 'success');
        clearTimeout(holder._timer);
        holder._timer = setTimeout(() => holder.classList.remove('show'), 3200);
    }

    function updateSidebarBadge(count) {
        const badge = document.getElementById('sidebarNewBadge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count;
            badge.hidden = false;
        } else {
            badge.hidden = true;
        }
    }

    async function post(action, data = {}) {
        const fd = new FormData();
        fd.append('action', action);
        fd.append('_csrf', CSRF);

        Object.entries(data).forEach(([key, val]) => {
            if (val !== undefined && val !== null) fd.append(key, val);
        });

        const res  = await fetch(API, { method: 'POST', body: fd });
        const json = await res.json().catch(() => ({ ok: false, error: 'bad json' }));

        if (!json.ok) throw new Error(json.error || 'Ошибка');
        return json;
    }

    async function postForm(action, form) {
        const fd = new FormData(form);
        fd.set('action', action);
        fd.set('_csrf', CSRF);

        const res  = await fetch(API, { method: 'POST', body: fd });
        const json = await res.json().catch(() => ({ ok: false, error: 'bad json' }));

        if (!json.ok) throw new Error(json.error || 'Ошибка');
        return json;
    }

    /* ============================================================
       МОБИЛЬНОЕ МЕНЮ
       ============================================================ */
    (function initMobileMenu() {
        const menuBtn = document.getElementById('adminMenuToggle');
        const sidebar = document.getElementById('adminSidebar');
        if (!menuBtn || !sidebar) return;

        menuBtn.addEventListener('click', () => sidebar.classList.toggle('open'));

        document.addEventListener('click', function (e) {
            if (
                window.innerWidth <= 900 &&
                sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) &&
                !menuBtn.contains(e.target)
            ) {
                sidebar.classList.remove('open');
            }
        });
    })();

    /* ============================================================
       КАНБАН — drag & drop
       ============================================================ */
    function updateKanbanCount() {
        document.querySelectorAll('.kanban-col').forEach(function (col) {
            const body  = col.querySelector('.kanban-body');
            const count = col.querySelector('.kanban-count');
            if (body && count) {
                count.textContent = body.querySelectorAll('.kanban-card').length;
            }
        });
    }

    (function initKanban() {
        if (typeof Sortable === 'undefined') return;

        document.querySelectorAll('.kanban-body').forEach(function (col) {
            new Sortable(col, {
                group: 'kanban',
                animation: 180,
                ghostClass: 'kanban-ghost',
                onEnd: async function (evt) {
                    const card = evt.item;
                    const id   = card.dataset.id;
                    const to   = evt.to.dataset.status;

                    if (!id || !to) return;

                    try {
                        const res = await post('lead.update_status', { lead_id: id, status: to });
                        notify('Статус обновлён');
                        updateKanbanCount();
                        updateSidebarBadge(res.new_count ?? 0);
                    } catch (err) {
                        notify(err.message, 'error');
                        if (evt.from !== evt.to) evt.from.appendChild(card);
                    }
                }
            });
        });
    })();

    /* ============================================================
       СМЕНА СТАТУСА В ТАБЛИЦЕ
       ============================================================ */
    document.addEventListener('change', async function (e) {
        const select = e.target.closest('.js-lead-status');
        if (!select) return;

        try {
            const res = await post('lead.update_status', {
                lead_id: select.dataset.id,
                status:  select.value,
            });
            notify('Статус обновлён');
            updateSidebarBadge(res.new_count ?? 0);
        } catch (err) {
            notify(err.message, 'error');
        }
    });

    /* ============================================================
       УДАЛЕНИЕ ЗАЯВКИ
       ============================================================ */
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.js-lead-delete');
        if (!btn) return;

        if (!confirm('Удалить заявку?')) return;

        const id = btn.dataset.id;

        try {
            const res = await post('lead.delete', { id });
            notify('Заявка удалена');

            document.querySelectorAll(`.kanban-card[data-id="${id}"]`).forEach(el => el.remove());
            document.querySelectorAll(`tr[data-id="${id}"]`).forEach(el => el.remove());

            updateKanbanCount();
            updateSidebarBadge(res.new_count ?? 0);

            if (document.getElementById('leadForm')) {
                setTimeout(() => window.location.href = 'leads.php', 400);
            }
        } catch (err) {
            notify(err.message, 'error');
        }
    });

    /* ============================================================
       ФОРМА КАРТОЧКИ ЗАЯВКИ
       ============================================================ */
    (function initLeadForm() {
        const leadForm = document.getElementById('leadForm');
        if (!leadForm) return;

        leadForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            try {
                const res = await postForm('lead.save', leadForm);
                notify(res.message || 'Сохранено');
            } catch (err) {
                notify(err.message, 'error');
            }
        });
    })();

    /* ============================================================
       ПРОЕКТЫ — toggle / delete
       ============================================================ */
    document.addEventListener('click', async function (e) {
        const toggle = e.target.closest('.js-project-toggle');
        if (toggle) {
            e.preventDefault();
            const id = toggle.dataset.id;
            try {
                const res = await post('project.toggle_active', { id });
                toggle.classList.toggle('on', res.active === 1);
                notify(res.active ? 'Проект активирован' : 'Проект скрыт');
            } catch (err) {
                notify(err.message, 'error');
            }
            return;
        }

        const del = e.target.closest('.js-project-delete');
        if (del) {
            e.preventDefault();
            if (!confirm('Удалить проект?')) return;

            const id = del.dataset.id;
            try {
                await post('project.delete', { id });
                document.querySelector(`tr[data-id="${id}"]`)?.remove();
                notify('Проект удалён');
            } catch (err) {
                notify(err.message, 'error');
            }
        }
    });

    /* ============================================================
       РЕДАКТОР ПРОЕКТА
       ============================================================ */

    // Основная форма
    (function initProjectMainForm() {
        const form = document.getElementById('projectMainForm');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const fd = new FormData(form);
            fd.set('action', 'project.save_main');
            fd.set('_csrf', CSRF);

            try {
                const res  = await fetch(API, { method: 'POST', body: fd });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Ошибка');

                notify(json.message || 'Сохранено');

                if (json.redirect) {
                    setTimeout(() => window.location.href = json.redirect, 500);
                }
            } catch (err) {
                notify(err.message, 'error');
            }
        });
    })();

    async function saveNested(action, form, key) {
        const fd = new FormData();
        fd.append('action', action);
        fd.append('_csrf', CSRF);
        fd.append('id', form.dataset.id || 0);

        const items = [];
        form.querySelectorAll('.repeat-item').forEach(function (row) {
            const item = {};
            let hasValue = false;

            row.querySelectorAll('input, textarea, select').forEach(function (inp) {
                const m = inp.name.match(/\[([^\]]+)\]$/);
                if (!m) return;
                if (inp.value !== '' && inp.value !== undefined) hasValue = true;
                item[m[1]] = inp.value;
            });

            if (hasValue) items.push(item);
        });

        items.forEach(function (item, i) {
            Object.entries(item).forEach(([k, v]) => {
                fd.append(`${key}[${i}][${k}]`, v);
            });
        });

        try {
            const res  = await fetch(API, { method: 'POST', body: fd });
            const json = await res.json();
            if (!json.ok) throw new Error(json.error || 'Ошибка');
            notify(json.message || 'Сохранено');
        } catch (err) {
            notify(err.message, 'error');
        }
    }

    (function initMaterialsForm() {
        const form = document.getElementById('materialsForm');
        if (!form) return;
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            await saveNested('project.save_materials', form, 'materials');
        });
    })();

    (function initFurnitureForm() {
        const form = document.getElementById('furnitureForm');
        if (!form) return;
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            await saveNested('project.save_furniture', form, 'furniture');
        });
    })();

    (function initFeaturesForm() {
        const form = document.getElementById('featuresForm');
        if (!form) return;
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            await saveNested('project.save_features', form, 'features');
        });
    })();

    function renderGallery(urls) {
        const holder = document.getElementById('galleryPreview');
        if (!holder) return;
        holder.innerHTML = urls.map(u =>
            `<div class="gallery-preview__item"><img src="../${u}" alt=""></div>`
        ).join('');
    }

    (function initGalleryForm() {
        const form = document.getElementById('galleryForm');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const fd = new FormData(form);
            fd.set('action', 'project.save_gallery');
            fd.set('_csrf', CSRF);

            try {
                const res  = await fetch(API, { method: 'POST', body: fd });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Ошибка');

                notify(json.message || 'Галерея сохранена');

                if (Array.isArray(json.gallery)) {
                    renderGallery(json.gallery);
                }
            } catch (err) {
                notify(err.message, 'error');
            }
        });
    })();

    /* ============================================================
       РЕДАКТОР ИНТЕРЬЕРА
       ============================================================ */
    (function initInteriorForm() {
        const form = document.getElementById('interiorMainForm');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const fd = new FormData(form);
            fd.set('action', 'interior.save');
            fd.set('_csrf', CSRF);

            try {
                const res  = await fetch(API, { method: 'POST', body: fd });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Ошибка');

                notify(json.message || 'Сохранено');

                if (json.redirect) {
                    setTimeout(() => window.location.href = json.redirect, 500);
                }
            } catch (err) {
                notify(err.message, 'error');
            }
        });
    })();

    /* ============================================================
       ИНТЕРЬЕРЫ — toggle / delete
       ============================================================ */
    document.addEventListener('click', async function (e) {
        const toggle = e.target.closest('.js-interior-toggle');
        if (toggle) {
            e.preventDefault();
            try {
                const res = await post('interior.toggle_active', { id: toggle.dataset.id });
                toggle.classList.toggle('on', res.active === 1);
                notify(res.active ? 'Интерьер активирован' : 'Интерьер скрыт');
            } catch (err) {
                notify(err.message, 'error');
            }
            return;
        }

        const del = e.target.closest('.js-interior-delete');
        if (del) {
            e.preventDefault();
            if (!confirm('Удалить интерьер?')) return;
            try {
                await post('interior.delete', { id: del.dataset.id });
                document.querySelector(`tr[data-id="${del.dataset.id}"]`)?.remove();
                notify('Интерьер удалён');
            } catch (err) {
                notify(err.message, 'error');
            }
        }
    });
    /* ============================================================
       СМЕНА ТЕМЫ (светлая / тёмная)
       ============================================================ */
    (function initTheme() {
        const html   = document.documentElement;
        const toggle = document.getElementById('adminThemeToggle');

        function setTheme(theme) {
            html.setAttribute('data-admin-theme', theme);
            try { localStorage.setItem('admin_theme', theme); } catch (e) {}
        }

        function currentTheme() {
            return html.getAttribute('data-admin-theme') || 'dark';
        }

        if (toggle) {
            toggle.addEventListener('click', () => {
                setTheme(currentTheme() === 'dark' ? 'light' : 'dark');
            });
        }
    })();
    /* ============================================================
       СМЕНА ПАРОЛЯ
       ============================================================ */
    (function initPasswordForm() {
        const form = document.getElementById('passwordForm');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            try {
                const res = await postForm('settings.change_password', form);
                notify(res.message || 'Пароль обновлён');
                form.reset();
            } catch (err) {
                notify(err.message, 'error');
            }
        });
    })();

})();