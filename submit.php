<?php

require 'autoload.php';

function dd($x) {
    echo '<pre>';
    var_dump($x);
    echo '</pre>';
}

use Dotenv\Dotenv;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use League\OAuth2\Client\Token\AccessTokenInterface;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection; // Исправлено
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Конфигурация интеграции
$clientId = $_ENV['CLIENT_ID'];
$clientSecret = $_ENV['CLIENT_SECRET'];
$redirectUri = $_ENV['REDIRECT_URI'];
$accessToken = $_ENV['ACCESS_TOKEN'];

$longLivedAccessToken = new LongLivedAccessToken($accessToken);

// Инициализация клиента AmoCRM
$apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);

$apiClient->setAccessToken($longLivedAccessToken)
    ->setAccountBaseDomain('kdovlet777.amocrm.ru');

// Проверяем наличие данных формы
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $price = $_POST['price'] ?? '';
    
    // Создание сделки
    $lead = new LeadModel();
    $lead->setName('Новая заявка с сайта')
        ->setPrice((int)$price);

    // Убедимся, что существует CustomFieldsValuesCollection для сделки
    if (!$lead->getCustomFieldsValues()) {
        $lead->setCustomFieldsValues(new CustomFieldsValuesCollection());
    }

    // Проверка на время, проведённое пользователем на сайте
    $timeSpent = isset($_POST['userTime']) && (int)$_POST['userTime'];
    $lead->getCustomFieldsValues()
        ->add((new MultitextCustomFieldValuesModel())->setFieldCode('USER_TIME')
            ->setValues((new MultitextCustomFieldValueCollection())
                ->add((new MultitextCustomFieldValueModel())->setValue($timeSpent)))
    );

    $leadsCollection = new LeadsCollection();
    $leadsCollection->add($lead);

    try {
        $leadsService = $apiClient->leads();
        $lead = $leadsService->addOne($lead);
        
        
        // Создание контакта и прикрепление к сделке
        $contact = new ContactModel();
        $contact->setName($name);
        $contact->getCustomFieldsValues()
            ->add((new MultitextCustomFieldValuesModel())->setFieldCode('EMAIL')
                ->setValues((new CustomFieldValueCollection())
                    ->add((new MultitextCustomFieldValueModel())->setValue($email)))
            )
            ->add((new MultitextCustomFieldValuesModel())->setFieldCode('PHONE')
                ->setValues((new CustomFieldValueCollection())
                    ->add((new MultitextCustomFieldValueModel())->setValue($phone)))
            );
        dd($contact);
        $contactsCollection = new ContactsCollection();
        $contactsCollection->add($contact);
        $contact = $apiClient->contacts()->addOne($contact);

        // Привязка контакта к сделке
        $lead->setContacts($contactsCollection);
        $leadsService->updateOne($lead);
        
        echo "Заявка успешно отправлена в amoCRM!";
    } catch (Exception $e) {
        echo "Ошибка при отправке заявки: " . $e->getMessage();
    }
} else {
    echo "Некорректный метод запроса.";
}