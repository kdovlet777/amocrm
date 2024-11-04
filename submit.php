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
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;

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

    $lead = (new LeadModel())
        ->setName('Новая заявка с сайта dovlet.moscow')
        ->setPrice($price);

    // Заполнение раздела Контакты
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

    $contactsCollection = (new ContactsCollection())->add($contact);
    $lead->setContacts($contactsCollection);

    // Заполнение поля "Провел более 30сек"
    $timeSpent = isset($_POST['userTime']) ? (string)$_POST['userTime'] : '0';

    $leadCustomFieldsValues = new CustomFieldsValuesCollection();

    $textCustomFieldValuesModel = (new TextCustomFieldValuesModel())
        ->setFieldId(737457)
        ->setValues(
            (new TextCustomFieldValueCollection())
                ->add((new TextCustomFieldValueModel())->setValue($timeSpent))
        );

    $leadCustomFieldsValues->add($textCustomFieldValuesModel);
    $lead->setCustomFieldsValues($leadCustomFieldsValues);

    try {

        $responseContact = $apiClient->contacts()->addOne($contact);
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