<?php
include __DIR__ .'/../core/db.php';
require_once __DIR__ .'/../../storage/logs/error_log.php';

$error = ''; 

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    if (strlen($search) > 255) {
        $error = "Поисковый запрос слишком длинный. Укоротите запрос и попробуйте снова.";
        $clients = []; 
    } elseif ($search) {
        $stmt = $pdo->prepare("CALL search_clients(:searchTerm)");
        $stmt->execute(['searchTerm' => $search]);
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->query("SELECT * FROM clients");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    logError("Ошибка при выполнении запроса: " . $e->getMessage());
    $error = "Произошла ошибка при загрузке данных.";
    $clients = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список клиентов</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <header>
    <nav>
        <ul>
            <li><a href="/public/index.php">Главная</a></li>
            <li><a href="#">Список клиентов</a></li>
        </ul>
    </nav>
    </header>
    <main>
        <h1>Список клиентов</h1>
        <form action="clients_list.php" method="GET" class="search-form">
        <label for="search">Поиск по параметрам:</label>
        <input type="text" id="search" name="search" placeholder="Введите имя, email, телефон или услугу" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Поиск</button>
        </form>
        <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <table class="table-type1">
            <thead>
                <tr>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Услуга</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['name']) ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['phone']); ?></td>
                        <td><?php echo htmlspecialchars($client['service'] ?? ''); ?></td>
                        <td>
                        <a href="/public/delete_client.php?id=<?php echo $client['id']; ?>" class="btn btn-delete" onclick="return confirm('Вы уверены, что хотите удалить этого клиента?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <footer>
        <p>&copy; 2024 Адвокатская компания</p>
    </footer>
</body>
</html>
