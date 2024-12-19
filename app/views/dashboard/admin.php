<?php
session_start();

include __DIR__ .'/../../core/db.php';
require_once __DIR__ .'/../../../storage/logs/error_log.php';
include __DIR__ .'/../../helpers/auth.php';
include __DIR__ .'/../../helpers/validate.php'; 

$errors = ['name' => '', 'email' => '', 'phone' => '', 'specialization' => ''];
$hasErrors = false;

// Проверка роли пользователя
if (getUserSessionRole() !== 'admin') {
    header('Location: /public/index.php');
    exit();
}

// Обработка формы добавления адвоката
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'add_lawyer') {

    $name = trim($_POST['name']);
    $name = preg_replace('/\s{2,}/', ' ', $name);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialization = $_POST['specialization'];

    $error = validateName($name);
    if ($error) {
        $errors['name'] = $error;
    }

    $error = validatePhone($phone);
    if ($error) {
        $errors['phone'] = $error;
    }
    
    $error = validateEmail($email);
    if ($error) {
        $errors['email'] = $error;
    }
    else {
        $error = validateuniqueEmail($email, $pdo);
        if ($error) {
            $errors['email'] = $error;
        }    
    }

    if (!array_filter($errors)) {
        $sql = "INSERT INTO lawyers (name, email, phone, specialization) VALUES (:name, :email, :phone, :specialization)";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'specialization' => $specialization]);
            header("Location: /app/views/dashboard/admin.php?success");
        } catch (PDOException $e) {
            logError("Ошибка добавления адвоката: " . $e->getMessage());
            echo "Ошибка добавления адвоката. Попробуйте позже.";
        }
    }
}

try {
    $stmt = $pdo->query("SELECT criterion, weight, func FROM ranking_weights");
    $weights = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Ошибка выполнения запроса: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'update_weights') {
    foreach ($_POST['weights'] as $criterion => $weight) {
        $stmt = $pdo->prepare("UPDATE ranking_weights SET weight = :weight WHERE criterion = :criterion");
        $stmt->execute(['weight' => $weight, 'criterion' => $criterion]);
    }
    header('Location: /app/views/dashboard/admin.php?success-rel');
}

// Получаем данные из формы
$dataType = $_POST['data_type'] ?? 'clients_count'; // По умолчанию показываем количество клиентов
$chartType = $_POST['chart_type'] ?? 'bar'; // По умолчанию тип диаграммы - столбчатая

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'diagrams'){

// Получаем данные для диаграммы в зависимости от выбранного параметра
try {
    if ($dataType == 'appointments_count') {
        $stmt = $pdo->query("SELECT lawyer_name, COUNT(id) AS appointment_count FROM appointments GROUP BY lawyer_name");
    } elseif ($dataType == 'clients_count') {
        $stmt = $pdo->query("SELECT lawyer_name, COUNT(id) AS appointment_count FROM appointments GROUP BY service");
    }
    $chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Массив данных для графика
    $chartSeries = [];
    foreach ($chartData as $row) {
        $chartSeries[] = [
            'name' => $row['lawyer_name'], // Имя адвоката
            'y' => (int)$row[$dataType == 'clients_count' ? 'client_count' : 'appointment_count'] // Количество клиентов или встреч
        ];
    }
} catch (PDOException $e) {
    logError("Ошибка выполнения запроса: " . $e->getMessage());
}

}

?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Личный кабинет администратора</title>
        <link rel="stylesheet" href="/public/css/style.css">
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <style>
            .chart-form {
                background-color: #f4f7fc; 
                border-radius: 8px;
                padding: 20px;
                width: 100%;
                max-width: 400px;
                margin: 0 0;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            }

            .chart-form label {
                font-size: 1.1em;
                color: #333;
                margin-bottom: 8px;
                display: block;
                font-weight: bold;
            }

            .form-select {
                width: 100%;
                padding: 10px;
                margin: 10px 0;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 1em;
                background-color: #fff;
                color: #333;
                transition: all 0.3s ease;
            }

            .form-select:focus {
                border-color: #5f9ea0; /* Цвет при фокусе */
                outline: none;
                box-shadow: 0 0 5px rgba(95, 158, 160, 0.5); /* Легкий эффект при фокусе */
            }

            .form-submit {
                background-color: #5f9ea0; /* Цвет кнопки */
                color: #fff;
                border: none;
                padding: 12px 20px;
                border-radius: 4px;
                font-size: 1.1em;
                cursor: pointer;
                transition: background-color 0.3s ease;
                width: 100%;
            }

            .form-submit:hover {
                background-color: #468a7d; /* Цвет кнопки при наведении */
            }
            .registration-form{
                margin-bottom: auto;
            }
        </style>
    </head>
    <body>
        <header>
            <nav>
                <ul>
                    <li><a href="/public/index.php">Главная</a></li>
                    <div class="auth-buttons">
                        <button id="exitauth" class="exit-button">Выход</button>
                    </div>
                    <form id="logoutForm" action="/public/index.php?action=logout" method="POST" style="display:none;">
                        <input type="hidden" name="form_type" value="logout">
                    </form>
                </ul>
            </nav>
        </header>
        <main>
            <h1>Личный кабинет администратора</h1>
            
            <h2>Выберите данные для отображения на диаграмме</h2>
            <form method="POST" class="chart-form">
                <input type="hidden" name="form_type" value="diagrams">

                <label for="data_select">Выберите данные:</label>
                <select id="data_select" name="data_type" class="form-select">
                    <option value="appointments_count">Количество клиентов</option>
                    <!-- Добавьте другие варианты по мере необходимости -->
                </select>

                <label for="chart_type">Выберите тип диаграммы:</label>
                <select id="chart_type" name="chart_type" class="form-select">
                    <option value="bar">Столбчатая</option>
                    <option value="line">Линейная</option>
                    <option value="pie">Круговая</option>
                </select>

                <button type="submit" class="form-submit">Показать диаграмму</button>
            </form>
            <h2>График статистики</h2>
            <div id="clientsChart" style="width: 900px; height: 450px;"></div>

            <h2>Настройка весов ранжирования</h2>

            <form method="POST">
                <input type="hidden" name="form_type" value="update_weights">
                <table>
                    <thead>
                        <tr>
                            <th>Критерий</th>
                            <th>Вес</th>
                            <th>Фунция подсчёта релевантности</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($weights as $weight): ?>
                            <tr>
                                <td><?= htmlspecialchars($weight['criterion']) ?></td>
                                <td>
                                    <input type="number" name="weights[<?= htmlspecialchars($weight['criterion']) ?>]" 
                                        value="<?= htmlspecialchars($weight['weight']) ?>" style="width: 100px;"
                                        <?php if ($weight['criterion'] === "cases_won"):?>
                                            min="0" max="20" 
                                        <?php elseif($weight['criterion'] === "date_proximity"): ?>
                                            min="0" max="30"
                                        <?php else: ?>
                                            min="0" max="100"
                                        <?php endif; ?>
                                    >
                                </td>
                                <td><?= htmlspecialchars($weight['func']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Сохранить</button>
            </form>

            <?php if (isset($_GET['success-rel'])): ?>
                <p class="success-message">Весовые коэффициенты успешно обновлены!</p>
            <?php endif; ?>  
            
            <h2>Добавить нового адвоката</h2>
            <form method="POST" class="registration-form">
                <input type="hidden" name="form_type" value="add_lawyer">
                <label for="name">Имя:</label>
                <input type="text" id="name" name="name" maxlength = "200" value="<?= htmlspecialchars($name ?? '') ?>" required>
                <small class="error"><?= $errors['name'] ?></small>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                <small class="error"><?= $errors['email'] ?></small>

                <label for="phone">Телефон:</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" required>
                <small class="error"><?= $errors['phone'] ?></small>

                <label for="specialization">Специализация:</label>
                <input type="text" id="specialization" name="specialization" value="<?= htmlspecialchars($specialization ?? '') ?>" required>
                <small class="error"><?= $errors['specialization'] ?></small>

                <button type="submit">Добавить адвоката</button>
            </form>

            <?php if (isset($_GET['success'])): ?>
                <p class="success-message">Адвокат успешно добавлен!</p>
            <?php endif; ?>
        </main>
        <script src="/public/js/exit-session.js"></script>
        <script>
            // Данные для диаграммы, которые получаем из PHP
            var chartSeries = <?= json_encode($chartSeries) ?>; // Массив данных с именами адвокатов и количеством клиентов
            var chartType = "<?= $chartType ?>"; // Получаем тип диаграммы из PHP

            // Определение параметров диаграммы для Highcharts
            var chartOptions = {
                chart: {
                    type: chartType, // Тип диаграммы (bar, line, pie)
                },
                credits: {
                    enabled: false  // Отключаем подпись автора
                },
                title: {
                    text: chartType == 'pie' ? 'Распределение клиентов' : 'Количество клиентов по адвокатам'
                },
                xAxis: {
                    categories: chartSeries.map(function (item) { return item.name; }), // Имя адвокатов для оси X
                    title: {
                        text: 'Адвокаты'
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: chartType == 'pie' ? '' : 'Количество'
                    }
                },
                series: [{
                    name: 'Клиенты',
                    data: chartSeries, // Используем массив данных, полученный из PHP
                    colorByPoint: chartType == 'pie', // Для круговой диаграммы раскрасить по точкам
                }],
                tooltip: {
                    pointFormat: chartType == 'pie' ? '{point.name}: {point.y} клиентов' : '{point.y} клиентов'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true, // Включаем отображение подписей
                            format: '{point.name}: {point.y} клиента', // Формат подписи
                            style: {
                                fontWeight: 'bold', // Делаем подписи жирными
                                color: 'black', // Черный цвет текста
                                fontSize: '14px' // Размер шрифта
                            }
                        }
                    },
                    bar: {
                        dataLabels: {
                            enabled: true,
                            format: '{point.y}'
                        }
                    }
                }
            };

            // Инициализация диаграммы
            Highcharts.chart('clientsChart', chartOptions);
        </script>
    </body>
</html>
