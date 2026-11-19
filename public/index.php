<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../src/Model/Category.php';
require_once __DIR__ . '/../src/Model/Product.php';
require_once __DIR__ . '/../src/Service/CatalogService.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$catalog = new CatalogService(
    __DIR__ . '/../data/categories.json',
    __DIR__ . '/../data/products.json'
);

// Параметры фильтрации из GET
$selectedCategoryId = isset($_GET['category_id']) &&$_GET['category_id'] !== ''
    ? (int)$_GET['category_id']
    : 0;
$onlyActive = isset($_GET['only_active']) &&$_GET['only_active'] === '1';

$categories = $catalog->getCategories();$products = $catalog->filterByCategory($selectedCategoryId > 0 ? $selectedCategoryId : null,$onlyActive);

// Агрегированные показатели для текущей выборки
$totalCount = $catalog->countProducts($products);
$availableCount =$catalog->countAvailableProducts($products);$averagePrice = $catalog->calculateAveragePrice($products);

// Демонстрация проверки некорректного конструктора (шаг 10)
$constructorTestMessage = '';
try {
    new Product(99, 'Некорректный товар', -150.0, $categories[0]); } catch (InvalidArgumentException$exception) {
    $constructorTestMessage = 'Перехвачено исключение конструктора: ' . $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Практическая работа №5 — Каталог товаров (ООП)</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #f4f6f9;
            color: #1e293b;
            margin: 0;
        }
        .topbar {
            background-color: #0f172a;
            color: #ffffff;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        main {
            max-width: 960px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px;
        }
        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2563eb;
            margin-top: 6px;
        }
        .filter-form {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        select, button {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
        }
        button {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #1d4ed8;
        }
        .reset-link {
            color: #2563eb;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        }
        .product-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .badge-category {
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        .badge-active {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-inactive {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .price {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 10px 0 4px;
        }
        .discount-note {
            font-size: 0.85rem;
            color: #64748b;
        }
        .notice-box {
            background-color: #fefce8;
            border: 1px solid #fef08a;
            color: #854d0e;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>Практическая работа 5</strong>
        <span>ТРПО</span>
    </header>

    <main>
        <section class="card">
            <h1>PHP ООП. Классы, конструкторы и модели предметной области</h1>
            <p>Каталог товаров сформирован из файлов <code>products.json</code> и <code>categories.json</code> с использованием моделей <code>Product</code>, <code>Category</code> и сервиса <code>CatalogService</code>.</p>
        </section>

        <section class="stats-grid">
            <div class="stat-box">
                <div>Товаров в выборке</div>
                <div class="stat-value"><?= e((string)$totalCount) ?> шт.</div>
            </div>
            <div class="stat-box">
                <div>Доступно к заказу</div>
                <div class="stat-value"><?= e((string)$availableCount) ?> шт.</div>
            </div>
            <div class="stat-box">
                <div>Средняя цена</div>
                <div class="stat-value"><?= e(number_format($averagePrice, 2, '.', ' ')) ?> ₽</div>
            </div>
        </section>

        <section class="card">
            <form method="GET" class="filter-form">
                <label for="category_id"><strong>Категория:</strong></label>
                <select name="category_id" id="category_id">
                    <option value="">Все категории</option>
                    <?php foreach ($categories as$category): ?>
                        <option value="<?= e((string)$category->getId()) ?>" <?= $selectedCategoryId ===$category->getId() ? 'selected' : '' ?>>
                            <?= e($category->getName()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>
                    <input type="checkbox" name="only_active" value="1" <?= $onlyActive ? 'checked' : '' ?>>
                    Только в наличии
                </label>

                <button type="submit">Применить</button>
                <a href="index.php" class="reset-link">Сбросить</a>
            </form>
        </section>

        <?php if (empty($products)): ?>
            <section class="card">
                <p>По выбранным параметрам товары не найдены.</p>
            </section>
        <?php else: ?>
            <section class="products-grid">
                <?php foreach ($products as$product): ?>
                    <article class="product-card">
                        <div>
                            <span class="badge badge-category"><?= e($product->getCategory()->getName()) ?></span>
                            <span class="badge <?= $product->isAvailableForOrder() ? 'badge-active' : 'badge-inactive' ?>">
                                <?= e($product->getAvailabilityLabel()) ?>
                            </span>
                            <h3><?= e($product->getName()) ?></h3>
                        </div>
                        <div>
                            <div class="price"><?= e($product->getFormattedPrice()) ?></div>
                            <div class="discount-note">
                                Со скидкой 10%: <?= e(number_format($product->getDiscountedPrice(10), 2, '.', ' ')) ?> ₽
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if ($constructorTestMessage !== ''): ?>
            <section class="card" style="margin-top: 20px;">
                <strong>Проверка граничных значений конструктора:</strong>
                <p class="notice-box"><?= e($constructorTestMessage) ?></p>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>