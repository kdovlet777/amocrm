<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отправка заявки</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script>
        let userTime = false;
        setTimeout(() => { userTime = true; }, 30000);
    </script>
</head>
<body>
    <form action="submit.php" method="POST">
        <div class="page-title">
            <img src="assets/img/roistat.webp" alt="roistat">
            <h1>Оставьте заявку</h1>
        </div>
        <label>Имя:</label><input type="text" name="name" required><br>
        <label>Email:</label><input type="email" name="email" required><br>
        <label>Телефон:</label> <input type="tel" name="phone" required><br>
        <label>Цена:</label> <input type="number" name="price" required><br>
        <input type="hidden" name="userTime" id="userTime" value="0">
        <button type="submit">Отправить заявку</button>
    </form>
    <script>
        document.querySelector('form').onsubmit = function() {
            document.getElementById('userTime').value = userTime ? '1' : '0';
        }
    </script>
</body>
</html>