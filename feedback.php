<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'kerosinka_db';
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$status_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fio = trim($_POST['fio']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $coffee_shop = trim($_POST['coffee_shop']);
    $message = trim($_POST['message']);
    $file_name = '';

    if (empty($fio) || empty($phone) || empty($email) || empty($message)) {
        $status_message = '⚠️ Пожалуйста, заполните все обязательные поля!';
    } else {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($_FILES['file']['name']);
            $target_file = $upload_dir . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
            if (in_array($file_type, $allowed_types)) {
                move_uploaded_file($_FILES['file']['tmp_name'], $target_file);
            } else {
                $status_message = '⚠️ Неподдерживаемый формат файла!';
            }
        }

        if (empty($status_message)) {
            $stmt = $conn->prepare("INSERT INTO feedback (fio, phone, email, city, coffee_shop, message, file_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $fio, $phone, $email, $city, $coffee_shop, $message, $file_name);
            if ($stmt->execute()) {
                $status_message = '✅ Ваше сообщение успешно отправлено! Мы свяжемся с вами.';
                header("Refresh: 3; url=feedback.php");
            } else {
                $status_message = '❌ Ошибка при отправке. Попробуйте позже.';
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Обратная связь - Кофейня "Керосинка"</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <meta charset="utf-8">
        <style>
            .feedback-container {
                background: #fff;
                padding: 35px;
                border-radius: 15px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                margin: 20px 0;
            }
            .feedback-desc {
                text-align: center;
                color: #555;
                margin-bottom: 25px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                font-weight: bold;
                margin-bottom: 6px;
                color: #2c1810;
            }
            .form-group input,
            .form-group select,
            .form-group textarea {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e8d5c4;
                border-radius: 8px;
                font-size: 16px;
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }
            .form-group input:focus,
            .form-group select:focus,
            .form-group textarea:focus {
                border-color: #c49a6c;
                outline: none;
            }
            .form-checkbox {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                margin: 20px 0;
                padding: 15px;
                background: #f5f0eb;
                border-radius: 8px;
            }
            .form-checkbox input {
                width: 20px;
                height: 20px;
                margin-top: 3px;
                accent-color: #c49a6c;
            }
            .form-checkbox label {
                font-size: 13px;
                color: #555;
                line-height: 1.5;
            }
            .submit-btn {
                background: #2c1810;
                color: #f5e6d3;
                padding: 15px 50px;
                border: none;
                border-radius: 30px;
                font-size: 18px;
                font-weight: bold;
                cursor: pointer;
                width: 100%;
                max-width: 300px;
                display: block;
                margin: 10px auto 0;
                transition: 0.3s;
            }
            .submit-btn:hover {
                background: #c49a6c;
                color: #2c1810;
            }
            .status-message {
                padding: 15px 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                font-weight: bold;
                text-align: center;
            }
            .status-message.success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .status-message.error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .file-hint {
                font-size: 12px;
                color: #999;
                display: block;
                margin-top: 5px;
            }
            .feedback-top-link {
                position: absolute;
                top: 20px;
                right: 30px;
            }
            .feedback-link {
                display: inline-block;
                background: #c49a6c;
                color: #2c1810;
                padding: 10px 22px;
                border-radius: 30px;
                font-weight: bold;
                font-size: 15px;
                text-decoration: none;
                transition: 0.3s;
                box-shadow: 0 4px 12px rgba(196,154,108,0.3);
            }
            .feedback-link:hover {
                background: #dbb48b;
                transform: scale(1.05);
            }
            .header1 {
                position: relative;
                background: #2c1810;
                color: #f5e6d3;
                padding: 15px 20px;
                overflow: hidden;
                border-bottom: 5px solid #c49a6c;
            }
            .header1 h1 {
                margin: 10px 0;
                font-size: 28px;
            }
            section {
                max-width: 1000px;
                margin: 20px auto;
                padding: 0 20px;
            }
            nav {
                background: #e8d5c4;
                padding: 12px 20px;
                border-radius: 8px;
                margin-bottom: 25px;
            }
            nav li {
                display: inline;
                margin-right: 25px;
                list-style: none;
            }
            nav a {
                color: #2c1810;
                text-decoration: none;
                font-weight: bold;
                font-size: 16px;
                padding: 8px 12px;
                border-radius: 5px;
                transition: 0.3s;
            }
            nav a:hover {
                background: #2c1810;
                color: #f5e6d3;
            }
            footer {
                background: #2c1810;
                color: #f5e6d3;
                padding: 20px;
                text-align: center;
                margin-top: 30px;
                border-top: 5px solid #c49a6c;
            }
            @media (max-width: 700px) {
                .feedback-top-link {
                    position: static;
                    text-align: center;
                    margin-top: 10px;
                }
                nav li {
                    display: block;
                    margin: 5px 0;
                    text-align: center;
                }
                .header1 h1 {
                    font-size: 20px;
                    text-align: center !important;
                }
                .header1 img {
                    float: none !important;
                    display: block;
                    margin: 0 auto 10px auto;
                    width: 100px;
                }
                .feedback-container {
                    padding: 20px;
                }
                .form-checkbox {
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                }
                .submit-btn {
                    max-width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="header1">
            <a href="index.html">
                <img src="logo_coffee.jpg" width="150" border="3" align="left" alt="Логотип">
            </a>
            <h1 align="center">Кофейня "Керосинка"</h1>
            <div class="feedback-top-link">
                <a href="feedback.php" class="feedback-link">📩 Обратная связь</a>
                <a href="cart.html" class="feedback-link" style="margin-left:10px;background:#2c1810;">🛒 Корзина <span id="cart-count" style="display:none;background:#e74c3c;padding:2px 8px;border-radius:50%;">0</span></a>
            </div>
        </div>
        <section>
            <nav>
                <li><a href="index.html">Главная</a></li>
                <li><a href="coffee.html">Кофе</a></li>
                <li><a href="sandwiches.html">Сэндвичи</a></li>
                <li><a href="lemonade.html">Лимонады</a></li>
                <li><a href="contacts.html">Контакты</a></li>
                <li><a href="cart.html">🛒 Корзина</a></li>
            </nav>
            <h2>Обратная связь</h2>
            <div class="feedback-container">
                <?php if (!empty($status_message)): ?>
                    <div class="status-message <?php echo strpos($status_message, '✅') !== false ? 'success' : 'error'; ?>">
                        <?php echo $status_message; ?>
                    </div>
                <?php endif; ?>
                <p class="feedback-desc">Заполните форму ниже, и мы свяжемся с вами!</p>
                <form action="feedback.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="fio">ФИО *</label>
                        <input type="text" id="fio" name="fio" placeholder="Иванов Иван Иванович" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Телефон *</label>
                        <input type="tel" id="phone" name="phone" placeholder="+7 (___) ___-__-__" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" placeholder="example@mail.ru" required>
                    </div>
                    <div class="form-group">
                        <label for="city">Город</label>
                        <input type="text" id="city" name="city" placeholder="Москва">
                    </div>
                    <div class="form-group">
                        <label for="coffee_shop">Кофейня *</label>
                        <select id="coffee_shop" name="coffee_shop" required>
                            <option value="">Выберите кофейню...</option>
                            <option value="kerosinka_gubkin">Керосинка (Губкин)</option>
                            <option value="kerosinka_moscow">Керосинка (Москва)</option>
                            <option value="kerosinka_spb">Керосинка (Санкт-Петербург)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Сообщение *</label>
                        <textarea id="message" name="message" rows="5" placeholder="Ваше сообщение..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="file">Загрузить файл</label>
                        <input type="file" id="file" name="file">
                        <small class="file-hint">Максимальный размер файла: 10 МБ</small>
                    </div>
                    <div class="form-checkbox">
                        <input type="checkbox" id="agree" name="agree" required>
                        <label for="agree">
                            Нажимая кнопку «отправить», я даю свое согласие на обработку моих персональных данных,
                            в соответствии с Федеральным законом от 27.07.2006 года №152-ФЗ «О персональных данных»,
                            на условиях и для целей, определенных в ПОЛИТИКЕ ОБРАБОТКИ И ЗАЩИТЫ ПЕРСОНАЛЬНЫХ ДАННЫХ
                            ООО «УРБАН КОФИКС РАША»
                        </label>
                    </div>
                    <button type="submit" class="submit-btn">Отправить</button>
                </form>
            </div>
        </section>
        <footer>
            Наш адрес<br>
            119991, Москва, Ленинский пр-т., д.65
        </footer>
        <script src="cart.js"></script>
    </body>
</html>