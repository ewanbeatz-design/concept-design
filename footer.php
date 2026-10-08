</main>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="index.php" class="logo logo--light">
    <img src="/assets/img/concept-logo.svg" alt="Concept Design" class="logo__img">
</a>
                <p>Студия авторского дизайна интерьеров и изготовления мебели под ключ в Кемерово.</p>
            </div>

            <div class="footer-col">
                <h5>Студия</h5>
                <a href="index.php#projects">Проекты</a>
                <a href="index.php#furniture">Мебель</a>
                <a href="index.php#process">Процесс</a>
            </div>

            <div class="footer-col">
                <h5>Контакты</h5>
                <a href="tel:+79832264716">+7 983 226-47-16</a>
                <a href="mailto:conceptdsign@yandex.ru">conceptdsign@yandex.ru</a>
                <p>Кемерово</p>
            </div>

            <div class="footer-col">
                <h5>Соцсети</h5>
                <a href="https://t.me/+79832264716" target="_blank">Telegram</a>
                <a href="https://wa.me/79777998927" target="_blank">WhatsApp</a>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© 2026 Concept Design</span>
            <span>Design / Furniture / Interior</span>
            <a href="/admin/" class="footer-admin-link" aria-label="Админ-панель" title="Админ-панель"><i class="bi bi-shield-lock"></i></a>
        </div>
    </div>
</footer>
<!-- ================= CONTACT FAB ================= -->
<div class="contact-fab" id="contactFab">
    <div class="contact-fab__menu">
        <a href="https://t.me/+79832264716" target="_blank" rel="noopener" class="contact-fab__item contact-fab__item--tg">
            <span>Telegram</span>
            <i class="bi bi-telegram"></i>
        </a>
        <a href="https://max.ru/u/79832264716" target="_blank" rel="noopener" class="contact-fab__item contact-fab__item--max">
            <span>MAX</span>
            <i class="bi bi-chat-dots-fill"></i>
        </a>
        <a href="https://wa.me/79777998927" target="_blank" rel="noopener" class="contact-fab__item contact-fab__item--wa">
            <span>WhatsApp</span>
            <i class="bi bi-whatsapp"></i>
        </a>
        <a href="tel:+79832264716" class="contact-fab__item contact-fab__item--phone">
            <span>Позвонить</span>
            <i class="bi bi-telephone-fill"></i>
        </a>
    </div>

    <button type="button" class="contact-fab__toggle" id="contactFabToggle" aria-label="Связаться с нами">
        <i class="bi bi-chat-fill contact-fab__icon-open"></i>
        <i class="bi bi-x-lg contact-fab__icon-close"></i>
    </button>
</div>
<!-- BACK TO TOP -->
<button id="backToTop" class="back-to-top" aria-label="Наверх">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- OFF-CANVAS МЕНЮ -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="menuCanvas">
    <div class="offcanvas-header">
    <a href="index.php" class="logo logo--light">
        <img src="/assets/img/concept-logo.svg" alt="Concept Design" class="logo__img">
    </a>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
</div>
    <div class="offcanvas-body">
        <a href="index.php#projects" class="menu-link">Проекты</a>
        <a href="index.php#furniture" class="menu-link">Мебель</a>
        <a href="index.php#interiors" class="menu-link">Интерьеры</a>
        <a href="index.php#process" class="menu-link">Процесс</a>
        <a href="index.php#configurator" class="menu-link">Рассчитать</a>
        <a href="index.php#contact" class="menu-link">Контакты</a>
    </div>
</div>

<!-- MODAL: КОНТАКТЫ -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="section-kicker">START</span>
                    <h3>Обсудить проект</h3>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form class="lead-form" data-ajax-form>
                <input name="name" placeholder="Имя" required>
                <input name="phone" class="phone-mask" placeholder="Телефон / Telegram" required>
                <textarea name="message" placeholder="Коротко о задаче"></textarea>
                <button class="btn-solid" type="submit">
    Отправить запрос
    <i class="bi bi-arrow-up-right"></i>
</button>
<small class="form-consent">
    Нажимая кнопку, вы соглашаетесь с
    <a href="privacy_policy.php" target="_blank" rel="noopener">политикой конфиденциальности</a>
    и обработкой персональных данных.
</small>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: ПОДАРОК -->
<div class="modal fade" id="giftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="section-kicker">GIFT</span>
                    <h3>Получить сертификат</h3>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form class="lead-form" data-ajax-form>
                <input name="name" placeholder="Ваше имя" required>
                <input name="phone" class="phone-mask" placeholder="Телефон" required>
                <input name="email" type="email" placeholder="Email" required>
                <button class="btn-solid" type="submit">
    Получить сертификат
    <i class="bi bi-arrow-up-right"></i>
</button>
<small class="form-consent">
    Нажимая кнопку, вы соглашаетесь с
    <a href="privacy_policy.php" target="_blank" rel="noopener">политикой конфиденциальности</a>
    и обработкой персональных данных.
</small>
            </form>
        </div>
    </div>
</div>
<!-- MODAL: ВИДЕО -->
<div class="modal fade video-modal" id="videoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            <div class="video-modal__body" id="videoModalBody">
                <!-- Сюда подставляется iframe или video -->
            </div>
        </div>
    </div>
</div>

<!-- TOAST -->
<div class="toast-holder" id="toastHolder"></div>

<!-- SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="/assets/js/app.js"></script>

</body>
</html>