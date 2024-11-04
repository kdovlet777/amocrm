<?php

require 'autoload.php';

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Models\LeadModel;
use League\OAuth2\Client\Token\AccessToken;
use AmoCRM\Client\LongLivedAccessToken;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Конфигурация интеграции
$clientId = $_ENV['CLIENT_ID'];
$clientSecret = $_ENV['CLIENT_SECRET'];
$redirectUri = $_ENV['REDIRECT_URI'];
$accessToken = $_ENV['ACCESS_TOKEN'];

// Инициализация клиента AmoCRM
$apiClient = new AmoCRMApiClient();
$longLivedAccessToken = new LongLivedAccessToken($accessToken);
$apiClient->setAccessToken($longLivedAccessToken)
    ->setAccountBaseDomain('kdovlet777.amocrm.ru');


// Проверяем наличие данных формы
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $price = trim($_POST['price'] ?? '');

    // Проверяем, что поле заполнено и является целым числом
    if (empty($price) || !is_numeric($price)) {
        echo "Ошибка: поле цены должно быть заполнено и содержать число.";
        return;
    }

    try {
        // Создание сделки с указанной ценой
        $lead = new LeadModel();
        $lead->setName('Тестовая сделка')
             ->setPrice((int)$price); // Преобразуем цену в целое число

        // Отправка данных о сделке
        $response = $apiClient->leads()->addOne($lead);

        // Выводим ответ для отладки
        echo "Сделка успешно отправлена в amoCRM с ценой: $price!";
        echo "<br>Ответ сервера: ";
        var_dump($response); // Выводим весь ответ от сервера
    } catch (Exception $e) {
        echo "Ошибка при отправке сделки: " . $e->getMessage();
        // Если есть возможность, выводим ответ от сервера
        if ($e->getResponse()) {
            echo "<br>Подробности: " . $e->getResponse()->getBody();
        }
    }
} else {
    echo "Некорректный метод запроса.";
}