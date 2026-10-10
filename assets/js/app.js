/* ==========================================================================
   CONCEPT DESIGN — app.js
   Единый скрипт для всех страниц
   ========================================================================== */

(function () {
    'use strict';

    /* ============================================================
       TOAST
       ============================================================ */
    window.showToast = function (msg, type) {
        const toast = document.getElementById('toastHolder');
        if (!toast) return;

        toast.textContent = msg;
        toast.className = 'toast-holder show';
        toast.style.background = type === 'error' ? '#b91c1c' : '#111';

        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => toast.classList.remove('show'), 3500);
    };

    /* ============================================================
       ХЕДЕР — смена фона при скролле
       ============================================================ */
    function initHeader() {
        const header = document.getElementById('siteHeader') ||
                       document.querySelector('.header');
        if (!header) return;

        const onScroll = () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ============================================================
       ПЛАВНЫЙ СКРОЛЛ ПО ЯКОРЯМ
       ============================================================ */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach((a) => {
            a.addEventListener('click', (e) => {
                // Quiz opener controls a fixed modal; it must not trigger page anchor scrolling.
                if (a.hasAttribute('data-open-quiz') || a.hasAttribute('data-close-quiz')) return;

                const id = a.getAttribute('href');
                if (!id || id === '#' || id.length < 2) return;

                const target = document.querySelector(id);
                if (!target) return;

                e.preventDefault();

                const header = document.querySelector('.header');
                const offset = header ? header.offsetHeight + 20 : 80;
                const top = target.getBoundingClientRect().top + window.scrollY - offset;

                window.scrollTo({ top, behavior: 'smooth' });
            });
        });
    }

    /* ============================================================
       BACK TO TOP
       ============================================================ */
    function initBackToTop() {
        const backBtn = document.getElementById('backToTop');
        if (!backBtn) return;

        window.addEventListener('scroll', () => {
            backBtn.classList.toggle('visible', window.scrollY > 600);
        }, { passive: true });

        backBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ============================================================
       OFF-CANVAS МЕНЮ
       ============================================================ */
    function initMenu() {
        if (!window.bootstrap) return;

        document.querySelectorAll('.offcanvas').forEach((el) => {
            bootstrap.Offcanvas.getOrCreateInstance(el);
        });

        document.querySelectorAll('[data-bs-toggle="offcanvas"]').forEach((btn) => {
            btn.addEventListener('click', function () {
                const target = document.querySelector(this.dataset.bsTarget);
                if (!target) return;
                bootstrap.Offcanvas.getOrCreateInstance(target).show();
            });
        });

        const menuToggle = document.getElementById('menuToggle');
        const menuCanvas = document.getElementById('menuCanvas');

        if (menuToggle && menuCanvas) {
            menuToggle.addEventListener('click', () => {
                bootstrap.Offcanvas.getOrCreateInstance(menuCanvas).show();
            });

            menuCanvas.querySelectorAll('.menu-link').forEach((link) => {
                link.addEventListener('click', (e) => {
                    const inst = bootstrap.Offcanvas.getInstance(menuCanvas);
                    if (inst) inst.hide();

                    const href = link.getAttribute('href');
                    if (href && href.includes('#')) {
                        const hash = '#' + href.split('#')[1];
                        const target = document.querySelector(hash);
                        if (target) {
                            e.preventDefault();
                            setTimeout(() => target.scrollIntoView({ behavior: 'smooth' }), 300);
                        }
                    }
                });
            });
        }
    }

    /* ============================================================
       OWL CAROUSEL
       ============================================================ */
    function initCarousels() {
        if (!window.jQuery || !jQuery.fn.owlCarousel) return;

        jQuery(document).ready(function ($) {
            $('.projects-carousel').owlCarousel({
                loop: true,
                margin: 24,
                nav: true,
                dots: true,
                navText: [
                    '<i class="bi bi-chevron-left"></i>',
                    '<i class="bi bi-chevron-right"></i>'
                ],
                responsive: {
                    0:    { items: 1, margin: 10 },
                    768:  { items: 2, margin: 20 },
                    1200: { items: 3, margin: 24 }
                }
            });

            if ($('.work-gallery__carousel').length) {
                const $gallery = $('.work-gallery__carousel');
                const $thumbs = $('.work-gallery__thumbs');
                const $counter = $('#workGalleryCurrent');

                $gallery.owlCarousel({
                    items: 1, loop: false, rewind: false, margin: 0, nav: true, dots: false,
                    smartSpeed: 450, autoHeight: false,
                    navText: [
                        '<i class="bi bi-chevron-left" aria-hidden="true"></i>',
                        '<i class="bi bi-chevron-right" aria-hidden="true"></i>'
                    ]
                });

                const setActiveThumb = (index) => {
                    $thumbs.find('.work-gallery__thumb').each(function (i) {
                        const active = i === index;
                        $(this).toggleClass('is-active', active).attr('aria-pressed', active ? 'true' : 'false');
                        if (active && this.scrollIntoView) {
                            this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
                        }
                    });
                    if ($counter.length) $counter.text(String(index + 1).padStart(2, '0'));
                };

                $gallery.on('changed.owl.carousel', function (event) {
                    if (event.item) setActiveThumb(event.item.index);
                });
                $thumbs.on('click', '.work-gallery__thumb', function () {
                    const index = Number(this.dataset.galleryTo);
                    if (Number.isInteger(index) && index >= 0) $gallery.trigger('to.owl.carousel', [index, 450, true]);
                });
                setActiveThumb(0);
            }

            $('.partners-carousel').owlCarousel({
                loop: true,
                margin: 0,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                responsive: {
                    0:    { items: 2 },
                    576:  { items: 3 },
                    992:  { items: 4 },
                    1200: { items: 5 }
                }
            });
        });
    }

    /* ============================================================
       FANCYBOX v5
       ============================================================ */
    function initFancybox() {
        if (typeof Fancybox === 'undefined') return;

        Fancybox.bind('[data-fancybox]', {
            Toolbar: {
                display: {
                    left: [],
                    middle: [],
                    right: ['close']
                }
            },
            Thumbs: false,
            dragToClose: true
        });
    }

    /* ============================================================
       AOS
       ============================================================ */
    function initAOS() {
        if (typeof AOS === 'undefined') return;

        AOS.init({
            duration: 800,
            once: true,
            offset: 60,
            easing: 'ease-out-cubic'
        });
    }

    /* ============================================================
       МАСКА ТЕЛЕФОНА
       ============================================================ */
    function initPhoneMask() {
        document.querySelectorAll('.phone-mask').forEach((input) => {
            input.addEventListener('input', function () {
                let v = this.value.replace(/\D/g, '');
                if (v[0] === '8') v = '7' + v.slice(1);
                if (v[0] !== '7') v = '7' + v;
                v = v.slice(0, 11);

                let out = '+7';
                if (v.length > 1)  out += ' (' + v.slice(1, 4);
                if (v.length >= 4) out += ')';
                if (v.length > 4)  out += ' ' + v.slice(4, 7);
                if (v.length > 7)  out += '-' + v.slice(7, 9);
                if (v.length > 9)  out += '-' + v.slice(9, 11);

                this.value = out;
            });
        });
    }

    /* ============================================================
       AJAX-ФОРМЫ
       ============================================================ */
    function initForms() {
        document.querySelectorAll('[data-ajax-form]').forEach((form) => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const btn = form.querySelector('button[type="submit"]');
                const orig = btn ? btn.innerHTML : '';

                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = 'Отправка...';
                }

                const fd = new FormData(form);
                const data = {};
                fd.forEach((val, key) => {
                    data[key] = typeof val === 'string' ? val.trim() : val;
                });

                const modal = form.closest('.modal');
                if (modal) {
                    data.source = modal.id === 'giftModal' ? 'Сертификат' : 'Форма в модалке';
                } else if (!data.source) {
                    data.source = 'Форма на сайте';
                }

                const action = form.getAttribute('action') || 'save_lead.php';

                try {
                    const res = await fetch(action, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await res.json().catch(() => ({}));

                    if (result.success) {
                        window.showToast('✅ Заявка принята! Мы свяжемся с вами.');
                        form.reset();

                        if (modal && window.bootstrap) {
                            setTimeout(() => {
                                const inst = bootstrap.Modal.getInstance(modal);
                                if (inst) inst.hide();
                            }, 800);
                        }
                    } else {
                        window.showToast('❌ ' + (result.message || 'Ошибка отправки'), 'error');
                    }
                } catch (err) {
                    console.error('Form error:', err);
                    window.showToast('⚠️ Ошибка соединения', 'error');
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = orig;
                    }
                }
            });
        });
    }

    /* ============================================================
       КОНФИГУРАТОР
       ============================================================ */
    function initConfigurator() {
        const form = document.getElementById('configForm');
        const result = document.getElementById('configResult');
        if (!form || !result) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fd = new FormData(form);
            const data = {};
            fd.forEach((val, key) => { data[key] = val.trim(); });
            data.source = 'Конфигуратор';

            try {
                const res = await fetch('save_lead.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const out = await res.json().catch(() => ({}));

                if (out.success) {
                    result.innerHTML =
                        '<strong>✅ Запрос собран.</strong> ' +
                        (data.type || '') +
                        ' · ' + (data.size || 'размер не указан') +
                        ' · ' + (data.style || '') +
                        '. Менеджер свяжется с вами.';
                    window.showToast('✅ Первичный запрос готов!');
                    form.reset();
                } else {
                    window.showToast('❌ ' + (out.message || 'Ошибка'), 'error');
                }
            } catch (err) {
                console.error('Config error:', err);
                window.showToast('⚠️ Ошибка соединения', 'error');
            }
        });
    }

    /* ============================================================
       ЛАЙКИ ПРОЕКТА
       ============================================================ */
    function initLikes() {
        const btn = document.getElementById('likeBtn');
        if (!btn) return;

        const icon = btn.querySelector('i');
        const countEl = document.getElementById('likesCount');
        let liked = btn.dataset.liked === 'true';
        let busy = false;

        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            if (busy) return;

            busy = true;
            btn.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append('project_id', btn.dataset.projectId);
            formData.append('action', liked ? 'unlike' : 'like');

            try {
                const res = await fetch('toggle-like.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (!data.success) throw new Error(data.error || 'Ошибка');

                liked = data.liked;
                btn.dataset.liked = liked ? 'true' : 'false';

                if (icon) icon.className = 'bi bi-heart' + (liked ? '-fill' : '');
                btn.classList.toggle('liked', liked);

                if (countEl) {
                    countEl.textContent =
                        Number(data.likes).toLocaleString('ru-RU') + ' лайков';
                }

                window.showToast(liked ? '❤️ Спасибо за лайк!' : '💔 Лайк убран');
            } catch (err) {
                console.error('Like error:', err);
                window.showToast('⚠️ ' + (err.message || 'Не удалось'), 'error');
            } finally {
                busy = false;
                btn.style.pointerEvents = '';
            }
        });
    }
    /* ============================================================
   VIDEO — открытие в модалке (YouTube / Vimeo / MP4)
   ============================================================ */
function initVideo() {
    const modalEl = document.getElementById('videoModal');
    const bodyEl = document.getElementById('videoModalBody');
    if (!modalEl || !bodyEl) return;

    const modal = new bootstrap.Modal(modalEl);

    function buildEmbed(url) {
        // YouTube
        const yt = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/);
        if (yt) {
            return '<iframe src="https://www.youtube.com/embed/' + yt[1] +
                '?autoplay=1&rel=0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>';
        }

        // Vimeo
        const vm = url.match(/vimeo\.com\/(\d+)/);
        if (vm) {
            return '<iframe src="https://player.vimeo.com/video/' + vm[1] +
                '?autoplay=1" allow="autoplay; fullscreen" allowfullscreen></iframe>';
        }

        // MP4 / WebM / OGV
        if (/\.(mp4|webm|ogv)(\?.*)?$/i.test(url)) {
            return '<video src="' + url + '" controls autoplay playsinline></video>';
        }

        // Если это уже embed-ссылка
        if (url.includes('youtube.com/embed/') || url.includes('player.vimeo.com')) {
            return '<iframe src="' + url + '" allow="autoplay; fullscreen" allowfullscreen></iframe>';
        }

        return '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#fff;">Видео недоступно</div>';
    }

    document.querySelectorAll('.video-tile').forEach((tile) => {
        tile.addEventListener('click', () => {
            const url = tile.dataset.video;
            if (!url) return;
            bodyEl.innerHTML = buildEmbed(url);
            modal.show();
        });
    });

    // Очищаем при закрытии, чтобы видео останавливалось
    modalEl.addEventListener('hidden.bs.modal', () => {
        bodyEl.innerHTML = '';
    });
}
/* ============================================================
   ФИЛЬТР ПРОЕКТОВ И ИНТЕРЬЕРОВ (projects.php + interiors.php)
   ============================================================ */
function initProjectsFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const tiles = document.querySelectorAll('.project-tile');
    const empty = document.getElementById('projectsEmpty') || document.getElementById('interiorsEmpty');

    if (!filterBtns.length || !tiles.length) return;

    filterBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter;

            filterBtns.forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            let visible = 0;

            tiles.forEach((tile) => {
                const type = tile.dataset.type || '';
                const show = filter === 'all' || type === filter;

                tile.classList.toggle('is-hidden', !show);
                if (show) visible++;
            });

            if (empty) empty.hidden = visible > 0;
        });
    });
}

/* ============================================================
   КАРУСЕЛЬ ГАЛЕРЕИ ПРОЕКТА (project.php)
   ============================================================ */
$(document).ready(function () {
    $('.project-gallery-carousel').owlCarousel({
        loop: true,
        margin: 24,
        nav: true,
        dots: true,
        navText: [
            '<i class="bi bi-chevron-left"></i>',
            '<i class="bi bi-chevron-right"></i>'
        ],
        responsive: {
            0:    { items: 1, margin: 10 },
            768:  { items: 2, margin: 20 },
            1200: { items: 3, margin: 24 }
        }
    });
});
/* ============================================================
   CONTACT FAB — раскрытие меню
   ============================================================ */
function initContactFab() {
    const fab = document.getElementById('contactFab');
    const toggle = document.getElementById('contactFabToggle');
    if (!fab || !toggle) return;

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        fab.classList.toggle('is-open');
    });

    // Закрытие при клике вне
    document.addEventListener('click', (e) => {
        if (!fab.contains(e.target)) {
            fab.classList.remove('is-open');
        }
    });

    // Закрытие по Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            fab.classList.remove('is-open');
        }
    });

    // Клик по каналу — закрываем меню
    fab.querySelectorAll('.contact-fab__item').forEach((item) => {
        item.addEventListener('click', () => {
            fab.classList.remove('is-open');
        });
    });
}
/* ============================================================
   THEME TOGGLE — день / ночь
   ============================================================ */
function initTheme() {
    const html = document.documentElement;
    const toggle = document.getElementById('themeToggle');

    function setTheme(theme) {
        html.setAttribute('data-theme', theme);
        try {
            localStorage.setItem('theme', theme);
        } catch (e) {}
    }

    function currentTheme() {
        return html.getAttribute('data-theme') || 'light';
    }

    if (toggle) {
        toggle.addEventListener('click', () => {
            setTheme(currentTheme() === 'dark' ? 'light' : 'dark');
        });
    }

    // Слежение за системной темой, если пользователь ещё не выбрал вручную
    if (window.matchMedia) {
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        const handler = (e) => {
            let saved = null;
            try { saved = localStorage.getItem('theme'); } catch (err) {}
            if (!saved) {
                html.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        };
        if (mq.addEventListener) mq.addEventListener('change', handler);
        else if (mq.addListener) mq.addListener(handler);
    }
}
/* ============================================================
   PAGE TRANSITIONS — плавные переходы между страницами
   ============================================================ */
function initPageTransitions() {
    const supportsVT = 'startViewTransition' in document;

    // Если браузер не поддерживает — включаем fallback через CSS-класс
    if (!supportsVT) {
        document.documentElement.classList.add('no-view-transitions');
    }

    // Плавный уход со страницы при клике по внутренним ссылкам
    document.addEventListener('click', function (e) {
        // Игнор: клик с модификаторами, средняя кнопка, target="_blank", скачивание
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (e.button !== 0) return;

        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href) return;

        // Игнор: якоря, tel:, mailto:, внешние ссылки, target=_blank
        if (href.startsWith('#') ||
            href.startsWith('tel:') ||
            href.startsWith('mailto:') ||
            href.startsWith('javascript:')) return;
        if (link.target === '_blank') return;

        // Проверяем, что ссылка ведёт на наш сайт
        try {
            const url = new URL(href, window.location.href);
            if (url.hostname !== window.location.hostname) return;
        } catch (err) {
            return;
        }

        // Fallback: плавно гасим, потом переходим
        if (!supportsVT) {
            e.preventDefault();
            document.documentElement.classList.add('is-leaving');
            setTimeout(() => {
                window.location.href = href;
            }, 300);
        }
    });
}

/* ============================================================
   PREFETCH — предзагрузка следующей страницы при наведении
   ============================================================ */
function initPrefetch() {
    if (!('requestIdleCallback' in window)) return;

    const seen = new WeakSet();

    function prefetch(link) {
        if (seen.has(link)) return;
        seen.add(link);

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#')) return;

        try {
            const url = new URL(href, window.location.href);
            if (url.hostname !== window.location.hostname) return;
        } catch (err) {
            return;
        }

        const linkEl = document.createElement('link');
        linkEl.rel = 'prefetch';
        linkEl.href = href;
        document.head.appendChild(linkEl);
    }

    function bind() {
        document.querySelectorAll('a[href]').forEach((link) => {
            link.addEventListener('mouseenter', () => prefetch(link), { once: true });
            link.addEventListener('touchstart', () => prefetch(link), { once: true, passive: true });
        });
    }

    bind();

    // Перепривязка при добавлении новых ссылок (например, в offcanvas)
    const observer = new MutationObserver(bind);
    observer.observe(document.body, { childList: true, subtree: true });
}

    /* ============================================================
       QUIZ — пошаговый расчёт проекта
       ============================================================ */
    (function initQuiz() {
        const quiz = document.getElementById('quiz');
        if (!quiz) return;

        const steps = quiz.querySelectorAll('.quiz-step');
        const prevBtn = document.getElementById('quizPrev');
        const nextBtn = document.getElementById('quizNext');
        const progressEl = document.getElementById('quizProgress');
        const progressFill = document.getElementById('quizProgressFill');
        const stepLabel = document.getElementById('quizStepLabel');
        const typeHidden = document.getElementById('quizTypeHidden');
        const answersHidden = document.getElementById('quizAnswersHidden');
        const nav = quiz.querySelector('.quiz-nav');

        // Full-screen quiz modal (Marquiz-style).
        const modal = document.getElementById('project-quiz');
        const openTriggers = document.querySelectorAll('[data-open-quiz]');
        const closeTriggers = modal ? modal.querySelectorAll('[data-close-quiz]') : [];
        let previousFocus = null;
        let previousBodyOverflow = '';

        function openQuizModal(event) {
            if (event) event.preventDefault();
            if (!modal) return;
            previousFocus = document.activeElement;
            previousBodyOverflow = document.body.style.overflow;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('quiz-modal-open');
            document.body.style.overflow = 'hidden';
            const closeButton = modal.querySelector('[data-close-quiz]');
            if (closeButton) closeButton.focus();
        }

        function closeQuizModal() {
            if (!modal || !modal.classList.contains('is-open')) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('quiz-modal-open');
            document.body.style.overflow = previousBodyOverflow;
            if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
        }

        openTriggers.forEach(trigger => trigger.addEventListener('click', openQuizModal));
        closeTriggers.forEach(trigger => trigger.addEventListener('click', closeQuizModal));
        if (modal) {
            modal.addEventListener('click', event => {
                if (event.target === modal) closeQuizModal();
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') closeQuizModal();
            });
            const brand = modal.querySelector('.quiz-modal__brand');
            if (brand) brand.addEventListener('click', event => event.preventDefault());
        }

        if (!prevBtn || !nextBtn || !progressEl || !progressFill || !stepLabel) return;

        let quizType = null;
        let currentStep = 0;
        const totalSteps = 7;
        const answers = {};

        function activeStepElement(step) {
            if (step === 0) return quiz.querySelector('.quiz-step[data-step="0"]');
            if (step === 7) return quiz.querySelector('.quiz-step--final');
            const branch = quiz.querySelector('.quiz-branch[data-branch="' + quizType + '"]');
            return branch ? branch.querySelector('.quiz-step[data-step="' + step + '"]') : null;
        }

        function hasAnswer(step) {
            if (step === 0) return Boolean(quizType);
            const active = activeStepElement(step);
            const option = active && active.querySelector('[data-field]');
            return Boolean(option && answers[option.dataset.field]);
        }

        function renderProgress() {
            progressEl.innerHTML = '';
            for (let i = 0; i < totalSteps; i++) {
                const bar = document.createElement('i');
                if (currentStep === 7 || i < currentStep) bar.classList.add('done');
                if (i === currentStep && currentStep < 7) bar.classList.add('active');
                progressEl.appendChild(bar);
            }
            const percent = currentStep === 7 ? 100 : Math.round(((currentStep + 1) / totalSteps) * 100);
            progressFill.style.width = percent + '%';
            stepLabel.textContent = currentStep === 7 ? 'Последний шаг — контакты' : 'Шаг ' + (currentStep + 1) + ' из ' + totalSteps;
        }

        function buildAnswersHidden() {
            answersHidden.innerHTML = '';
            Object.entries(answers).forEach(([field, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = field;
                input.value = value;
                answersHidden.appendChild(input);
            });
        }

        function showStep(step) {
            currentStep = step;
            steps.forEach(el => el.classList.remove('active'));
            const target = activeStepElement(step);
            if (target) target.classList.add('active');
            prevBtn.hidden = step === 0;
            nextBtn.hidden = step === 7;
            nextBtn.disabled = step === 7 || !hasAnswer(step);
            if (nav) nav.classList.toggle('quiz-nav--final', step === 7);
            if (step === 7) buildAnswersHidden();
            renderProgress();
        }

        quiz.querySelectorAll('[data-quiz-type]').forEach(btn => {
            btn.addEventListener('click', () => {
                quizType = btn.dataset.quizType;
                typeHidden.value = quizType === 'furniture' ? 'Мебель' : 'Дизайн интерьера';
                answers.quiz_type = typeHidden.value;
                quiz.querySelectorAll('[data-quiz-type]').forEach(item => item.classList.toggle('selected', item === btn));
                nextBtn.disabled = false;
            });
        });

        quiz.querySelectorAll('.quiz-option[data-field]').forEach(btn => {
            btn.addEventListener('click', () => {
                const field = btn.dataset.field;
                const parent = btn.closest('.quiz-options');
                if (parent) parent.querySelectorAll('.quiz-option').forEach(item => item.classList.toggle('selected', item === btn));
                answers[field] = btn.dataset.value;
                nextBtn.disabled = false;
            });
        });

        nextBtn.addEventListener('click', () => {
            if (!hasAnswer(currentStep)) return;
            if (currentStep < 6) showStep(currentStep + 1);
            else showStep(7);
            const active = quiz.querySelector('.quiz-step.active');
            if (active) active.setAttribute('tabindex', '-1');
        });

        prevBtn.addEventListener('click', () => {
            if (currentStep > 0) showStep(currentStep - 1);
        });

        showStep(0);
    })();
    /* ============================================================
   ФИЛЬТР ИНТЕРЬЕРОВ (interiors.php)
   ============================================================ */
function initInteriorsFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const tiles = document.querySelectorAll('.interior-tile');
    const empty = document.getElementById('interiorsEmpty');

    if (!filterBtns.length || !tiles.length) return;

    filterBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter;

            filterBtns.forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            let visible = 0;

            tiles.forEach((tile) => {
                const type = tile.dataset.type || '';
                const show = filter === 'all' || type === filter;

                tile.classList.toggle('is-hidden', !show);
                if (show) visible++;
            });

            if (empty) empty.hidden = visible > 0;
        });
    });
    
}
    /* ============================================================
       ИНИЦИАЛИЗАЦИЯ
       ============================================================ */
function initAll() {
    initProjectsFilter();
    initInteriorsFilter();
    initPageTransitions();
    initPrefetch();
    initTheme();             // ← добавить первым
    initHeader();
    initSmoothScroll();
    initBackToTop();
    initMenu();
    initCarousels();
    initFancybox();
    initAOS();
    initPhoneMask();
    initForms();
    initConfigurator();
    initLikes();
    initVideo();
    initProjectsFilter();
    initContactFab();

    console.log('🚀 CONCEPT DESIGN — 2026');
}

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

})();
