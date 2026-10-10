<?php
// Shared full-screen quiz modal. Included after </main> so Bootstrap's fixed modal
// is not trapped inside the main element's animation/stacking context.
?>
<section class="modal fade quiz-section quiz-modal" id="project-quiz" tabindex="-1" aria-labelledby="projectQuizTitle" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
    <div class="quiz-modal__bar">
        <a class="quiz-modal__brand" href="#" aria-label="CONCEPT Design">CONCEPT <span>DESIGN</span></a>
        <span class="quiz-modal__note">ПОДБОР ПРОЕКТА</span>
        <button class="quiz-modal__close" type="button" data-bs-dismiss="modal" aria-label="Закрыть расчёт"><span>Закрыть</span><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <div class="modal-body p-0">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">07 / Подбор под задачу</span>
                <h2 class="h-display" id="projectQuizTitle">Соберём<br><em>первичный запрос</em></h2>
            </div>
            <p class="section-head__text">
                Ответьте на несколько коротких вопросов — получим представление о задаче
                и вернёмся с ориентиром по стоимости.
            </p>
        </div>

        <div class="quiz" id="quiz">
            <div class="quiz-topline">
                <span class="quiz-topline__eyebrow">ВАШ ПРОЕКТ · CONCEPT DESIGN</span>
                <span class="quiz-topline__count" id="quizStepLabel">Выбор направления</span>
            </div>
            <div class="quiz-progress-track" aria-hidden="true"><span id="quizProgressFill"></span></div>

            <!-- ШАГ 0: выбор направления -->
            <div class="quiz-step active" data-step="0">
                <div class="quiz-step__num">01 / 07</div>
                <h3 class="quiz-step__title">Что вам нужно?</h3>
                <p class="quiz-step__desc">Выберите направление — дальше вопросы подстроятся под него.</p>

                <div class="quiz-options quiz-options--two">
                    <button type="button" class="quiz-option" data-quiz-type="furniture">
                        <i class="bi bi-house-heart"></i>
                        <strong>Мебель</strong>
                        <span>Кухня, шкаф, гардеробная</span>
                    </button>

                    <button type="button" class="quiz-option" data-quiz-type="interior">
                        <i class="bi bi-palette"></i>
                        <strong>Дизайн интерьера</strong>
                        <span>Квартира, дом под ключ</span>
                    </button>
                </div>
            </div>

            <!-- ================= ВЕТКА МЕБЕЛЬ ================= -->
            <div class="quiz-branch" data-branch="furniture">

                <div class="quiz-step" data-step="1">
                    <div class="quiz-step__num">02 / 07</div>
                    <h3 class="quiz-step__title">Что именно нужно?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="furniture_type" data-value="Кухня">Кухня</button>
                        <button type="button" class="quiz-option" data-field="furniture_type" data-value="Шкаф-купе">Шкаф-купе</button>
                        <button type="button" class="quiz-option" data-field="furniture_type" data-value="Гардеробная">Гардеробная</button>
                        <button type="button" class="quiz-option" data-field="furniture_type" data-value="Прихожая">Прихожая</button>
                        <button type="button" class="quiz-option" data-field="furniture_type" data-value="Детская">Детская</button>
                        <button type="button" class="quiz-option" data-field="furniture_type" data-value="Другое">Другое</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="2">
                    <div class="quiz-step__num">03 / 07</div>
                    <h3 class="quiz-step__title">Примерная площадь или длина?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="furniture_size" data-value="До 3 м">До 3 м</button>
                        <button type="button" class="quiz-option" data-field="furniture_size" data-value="3–5 м">3–5 м</button>
                        <button type="button" class="quiz-option" data-field="furniture_size" data-value="5–8 м">5–8 м</button>
                        <button type="button" class="quiz-option" data-field="furniture_size" data-value="8+ м">8+ м</button>
                        <button type="button" class="quiz-option" data-field="furniture_size" data-value="Не знаю">Не знаю</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="3">
                    <div class="quiz-step__num">04 / 07</div>
                    <h3 class="quiz-step__title">Материал фасадов?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="furniture_material" data-value="ЛДСП">ЛДСП</button>
                        <button type="button" class="quiz-option" data-field="furniture_material" data-value="МДФ + плёнка">МДФ + плёнка</button>
                        <button type="button" class="quiz-option" data-field="furniture_material" data-value="МДФ + эмаль">МДФ + эмаль</button>
                        <button type="button" class="quiz-option" data-field="furniture_material" data-value="Шпон">Шпон</button>
                        <button type="button" class="quiz-option" data-field="furniture_material" data-value="Не определился">Не определился</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="4">
                    <div class="quiz-step__num">05 / 07</div>
                    <h3 class="quiz-step__title">Стиль?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="furniture_style" data-value="Тёплый минимализм">Тёплый минимализм</button>
                        <button type="button" class="quiz-option" data-field="furniture_style" data-value="Современный">Современный</button>
                        <button type="button" class="quiz-option" data-field="furniture_style" data-value="Неоклассика">Неоклассика</button>
                        <button type="button" class="quiz-option" data-field="furniture_style" data-value="Лофт">Лофт</button>
                        <button type="button" class="quiz-option" data-field="furniture_style" data-value="Не определился">Не определился</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="5">
                    <div class="quiz-step__num">06 / 07</div>
                    <h3 class="quiz-step__title">Бюджет?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="furniture_budget" data-value="До 100 000 ₽">До 100 000 ₽</button>
                        <button type="button" class="quiz-option" data-field="furniture_budget" data-value="100 000 – 250 000 ₽">100 000 – 250 000 ₽</button>
                        <button type="button" class="quiz-option" data-field="furniture_budget" data-value="250 000 – 500 000 ₽">250 000 – 500 000 ₽</button>
                        <button type="button" class="quiz-option" data-field="furniture_budget" data-value="500 000+ ₽">500 000+ ₽</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="6">
                    <div class="quiz-step__num">07 / 07</div>
                    <h3 class="quiz-step__title">Когда планируете?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="furniture_timing" data-value="Срочно, 1–2 месяца">Срочно, 1–2 месяца</button>
                        <button type="button" class="quiz-option" data-field="furniture_timing" data-value="2–4 месяца">2–4 месяца</button>
                        <button type="button" class="quiz-option" data-field="furniture_timing" data-value="Более 4 месяцев">Более 4 месяцев</button>
                        <button type="button" class="quiz-option" data-field="furniture_timing" data-value="Пока планирую">Пока планирую</button>
                    </div>
                </div>

            </div>

            <!-- ================= ВЕТКА ДИЗАЙН ИНТЕРЬЕРА ================= -->
            <div class="quiz-branch" data-branch="interior">

                <div class="quiz-step" data-step="1">
                    <div class="quiz-step__num">02 / 07</div>
                    <h3 class="quiz-step__title">Какой объект?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="interior_object" data-value="Квартира">Квартира</button>
                        <button type="button" class="quiz-option" data-field="interior_object" data-value="Дом">Дом</button>
                        <button type="button" class="quiz-option" data-field="interior_object" data-value="Апартаменты">Апартаменты</button>
                        <button type="button" class="quiz-option" data-field="interior_object" data-value="Офис">Офис</button>
                        <button type="button" class="quiz-option" data-field="interior_object" data-value="Коммерция">Коммерция</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="2">
                    <div class="quiz-step__num">03 / 07</div>
                    <h3 class="quiz-step__title">Площадь?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="interior_area" data-value="До 40 м²">До 40 м²</button>
                        <button type="button" class="quiz-option" data-field="interior_area" data-value="40–80 м²">40–80 м²</button>
                        <button type="button" class="quiz-option" data-field="interior_area" data-value="80–150 м²">80–150 м²</button>
                        <button type="button" class="quiz-option" data-field="interior_area" data-value="150+ м²">150+ м²</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="3">
                    <div class="quiz-step__num">04 / 07</div>
                    <h3 class="quiz-step__title">Какой формат?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="interior_format" data-value="Эскизный проект">Эскизный проект</button>
                        <button type="button" class="quiz-option" data-field="interior_format" data-value="Полный дизайн-проект">Полный дизайн-проект</button>
                        <button type="button" class="quiz-option" data-field="interior_format" data-value="Проект + реализация">Проект + реализация</button>
                        <button type="button" class="quiz-option" data-field="interior_format" data-value="Пока не знаю">Пока не знаю</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="4">
                    <div class="quiz-step__num">05 / 07</div>
                    <h3 class="quiz-step__title">Стиль?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="interior_style" data-value="Тёплый минимализм">Тёплый минимализм</button>
                        <button type="button" class="quiz-option" data-field="interior_style" data-value="Современный">Современный</button>
                        <button type="button" class="quiz-option" data-field="interior_style" data-value="Неоклассика">Неоклассика</button>
                        <button type="button" class="quiz-option" data-field="interior_style" data-value="Джапанди">Джапанди</button>
                        <button type="button" class="quiz-option" data-field="interior_style" data-value="Не определился">Не определился</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="5">
                    <div class="quiz-step__num">06 / 07</div>
                    <h3 class="quiz-step__title">Бюджет на реализацию?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="interior_budget" data-value="До 500 000 ₽">До 500 000 ₽</button>
                        <button type="button" class="quiz-option" data-field="interior_budget" data-value="500 000 – 1 500 000 ₽">500 000 – 1 500 000 ₽</button>
                        <button type="button" class="quiz-option" data-field="interior_budget" data-value="1 500 000 – 3 000 000 ₽">1 500 000 – 3 000 000 ₽</button>
                        <button type="button" class="quiz-option" data-field="interior_budget" data-value="3 000 000+ ₽">3 000 000+ ₽</button>
                    </div>
                </div>

                <div class="quiz-step" data-step="6">
                    <div class="quiz-step__num">07 / 07</div>
                    <h3 class="quiz-step__title">Когда планируете начать?</h3>
                    <div class="quiz-options">
                        <button type="button" class="quiz-option" data-field="interior_timing" data-value="Срочно, 1–2 месяца">Срочно, 1–2 месяца</button>
                        <button type="button" class="quiz-option" data-field="interior_timing" data-value="2–4 месяца">2–4 месяца</button>
                        <button type="button" class="quiz-option" data-field="interior_timing" data-value="Более 4 месяцев">Более 4 месяцев</button>
                        <button type="button" class="quiz-option" data-field="interior_timing" data-value="Пока планирую">Пока планирую</button>
                    </div>
                </div>

            </div>

            <!-- ================= ФИНАЛЬНЫЙ ШАГ: КОНТАКТЫ ================= -->
            <div class="quiz-step quiz-step--final" data-step="7">
                <div class="quiz-step__num">— Финал</div>
                <h3 class="quiz-step__title">Куда отправить результат?</h3>
                <p class="quiz-step__desc">Оставьте контакт — пришлём подборку и ориентир по стоимости.</p>

                <form class="quiz-form lead-form" id="quizForm" data-ajax-form>
                    <input type="hidden" name="source" value="Квиз подбора">
                    <input type="hidden" name="form_type" value="quiz">
                    <input type="hidden" name="quiz_type" id="quizTypeHidden">

                    <!-- Сюда попадут ответы -->
                    <div id="quizAnswersHidden"></div>

                    <div class="quiz-form__row">
                        <input type="text" name="name" placeholder="Ваше имя" required>
                        <input type="tel" name="phone" class="phone-mask" placeholder="Телефон" required>
                    </div>

                    <button type="submit" class="btn-submit">
                        <span>Получить подборку</span>
                        <i class="bi bi-arrow-up-right"></i>
                    </button>

                    <small class="form-consent">
                        Нажимая кнопку, вы соглашаетесь с
                        <a href="/privacy">политикой конфиденциальности</a>
                        и обработкой персональных данных.
                    </small>
                </form>
            </div>

            <!-- Навигация -->
            <div class="quiz-nav">
                <button type="button" class="quiz-nav__btn" id="quizPrev" hidden>
                    <i class="bi bi-arrow-left"></i> Назад
                </button>
                <div class="quiz-progress" id="quizProgress" aria-label="Прогресс расчёта"></div>
                <button type="button" class="quiz-next" id="quizNext" disabled>
                    Далее <i class="bi bi-arrow-right"></i>
                </button>
            </div>

        </div>
    </div>
      </div>
    </div>
</section>
