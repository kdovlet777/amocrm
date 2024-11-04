<?php

require 'autoload.php';

use Dotenv\Dotenv;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\CheckboxCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\CheckboxCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\CheckboxCustomFieldValueModel;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Конфигурация интеграции
$accessToken = $_ENV['ACCESS_TOKEN'];

$longLivedAccessToken = new LongLivedAccessToken($accessToken);
$apiClient = new AmoCRMApiClient();
$apiClient->setAccessToken($longLivedAccessToken)
    ->setAccountBaseDomain('kdovlet777.amocrm.ru');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $price = (int)trim($_POST['price'] ?? '');
    $isChecked = $_POST['userTime'] == 1 ? true : false;

    // Создание сделки
    $lead = (new LeadModel())
        ->setName('Заявка с сайта dovlet.moscow')
        ->setPrice($price);

    // Создание контакта
    $contact = (new ContactModel())
        ->setName($name)
        ->setCustomFieldsValues(
            (new CustomFieldsValuesCollection())
                ->add(
                    (new MultitextCustomFieldValuesModel())
                        ->setFieldCode('EMAIL')
                        ->setValues(
                            (new MultitextCustomFieldValueCollection())
                                ->add((new MultitextCustomFieldValueModel())->setValue($email))
                        )
                )
                ->add(
                    (new MultitextCustomFieldValuesModel())
                        ->setFieldCode('PHONE')
                        ->setValues(
                            (new MultitextCustomFieldValueCollection())
                                ->add((new MultitextCustomFieldValueModel())->setValue($phone))
                        )
                )
        );

    // Настройка кастомного поля для checkbox
    $leadCustomFieldsValues = new CustomFieldsValuesCollection();

    $checkboxCustomFieldValuesModel = (new CheckboxCustomFieldValuesModel())
        ->setFieldId(737671)
        ->setValues(
            (new CheckboxCustomFieldValueCollection())
                ->add((new CheckboxCustomFieldValueModel())->setValue($isChecked ? '1' : '0'))
        );

    $leadCustomFieldsValues->add($checkboxCustomFieldValuesModel);
    $lead->setCustomFieldsValues($leadCustomFieldsValues);

    try {
        // Сначала добавляем контакт
        $responseContact = $apiClient->contacts()->addOne($contact);
        
        // Теперь устанавливаем связь с лидом
        $contactsCollection = (new ContactsCollection())->add($responseContact);
        $lead->setContacts($contactsCollection);
        
        // Добавляем сделку
        $responseLead = $apiClient->leads()->addOne($lead);

        echo "Заявка успешно отправлена в amoCRM с ценой: $price!";
    } catch (Exception $e) {
        echo "Ошибка при отправке заявки: " . $e->getMessage();
        if (method_exists($e, 'getResponse') && $e->getResponse()) {
            echo "<br>Подробности: " . $e->getResponse()->getBody();
        }
    }
} else {
    echo "Некорректный метод запроса.";
}