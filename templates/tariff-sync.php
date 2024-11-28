<?php
// Подключение необходимых файлов
require_once 'core/App.php';
require_once 'models/Tariffs.php';
require_once 'helpers/ServiceHelper.php';

// Обработка формы
if (isset(core\App::$URLParams['action'])) {
    switch (core\App::$URLParams['action']) {
        case 'save-service-form':
            if (!empty($_POST['service_id']) && !empty($_POST['tariff_id'])) {
                foreach ($_POST['service_id'] as $service_id) {
                    models\Tariffs::sychTariff($service_id, $_POST['tariff_id']);
                }
            }
            header('Location: /prices/');
            exit;
            break;
    }
}

// Функция для генерации HTML списка сервисов с чекбоксами
function getServicesHTML() {
    $services = helpers\ServiceHelper::getAllServices();
    $html = '';

    foreach ($services as $service) {
        $html .= '<label>';
        $html .= '<input type="checkbox" name="service_id[]" value="' . htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8') . '">';
        $html .= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8');
        $html .= '</label><br>';
    }

    return $html;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Синхронизация тарифов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        form {
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            max-width: 600px;
            margin: 40px auto;
        }
        h3 {
            font-size: 21px;
            font-weight: 300;
        }
        label {
            display: block;
            margin-top: 8px;
            font-weight: 600;
        }
        button {
            padding: 10px 72px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<form action="?action=save-service-form" method="POST">
    <!-- Передача tariff_id через скрытое поле -->
    <input type="hidden" name="tariff_id" value="<?= htmlspecialchars($_GET['tariff_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    
    <h3>Выберите СЦ для синхронизации тарифов:</h3>
    
    <!-- Чекбокс для выделения всех -->
    <div style="margin-top: 32px; font-weight: 600">
        <label>
            <input type="checkbox" data-check-all-flags> Выделить все
        </label>
    </div>
    
    <!-- Список сервисов -->
    <div>
        <?= getServicesHTML(); ?>
    </div>

    <div style="margin-top: 32px; margin-bottom: 32px">
        <button type="submit">Синхронизировать</button>
    </div>
</form>

<script>
    // Скрипт для выделения всех чекбоксов
    document.querySelector('[data-check-all-flags]').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="service_id[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
</script>

</body>
</html>
