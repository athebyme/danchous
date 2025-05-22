<?php
// autoparts/includes/footer.php
?>
    <!-- Конец основного контента страницы -->
</main> <!-- .site-main .container -->

<footer class="site-footer">
    <div class="container">
        <div class="footer-widgets">
            <!-- Здесь можно разместить виджеты: контакты, ссылки, карта сайта и т.д. -->
            <div class="footer-widget">
                <h4>О Компании</h4>
                <ul>
                    <li><a href="#">О нас</a></li>
                    <li><a href="#">Вакансии</a></li>
                    <li><a href="#">Политика конфиденциальности</a></li>
                </ul>
            </div>
            <div class="footer-widget">
                <h4>Клиентам</h4>
                <ul>
                    <li><a href="#">Как сделать заказ</a></li>
                    <li><a href="#">Доставка и оплата</a></li>
                    <li><a href="#">Возврат товара</a></li>
                </ul>
            </div>
            <div class="footer-widget">
                <h4>Контакты</h4>
                <p>Телефон: +7 (000) 000-00-00</p>
                <p>Email: info@autoparts.example</p>
                <p>Адрес: г. Город, ул. Улица, д. Дом</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?php echo date('Y'); ?> "АвтоДетали". Все права защищены.</p>
            <!-- Социальные иконки, если нужны -->
        </div>
    </div>
</footer>

<script src="<?php echo JS_URL; ?>/main.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo JS_URL; ?>/cart_ajax.js?v=<?php echo time(); ?>"></script>
<!-- Подключение других JS файлов, если они есть -->
<?php
// Если есть сообщения для пользователя (например, после действия), можно их здесь вывести
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    echo "<script>alert('" . addslashes($message['text']) . "');</script>"; // Простой alert, лучше сделать красивее
}
?>
</body>
</html>