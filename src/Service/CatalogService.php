<?php

declare(strict_types=1);

final class CatalogService
{
    /** @var array<int, Category> */
    private array $categories = [];

    /** @var Product[] */
    private array $products = [];

    public function __construct(string $categoriesFilePath, string$productsFilePath)
    {
        $this->loadCategories($categoriesFilePath);
        $this->loadProducts($productsFilePath);
    }

    private function loadCategories(string $filePath): void
    {
        $rawData = $this->readJsonFile($filePath);

        foreach ($rawData as$item) {
            $id = (int)($item['id'] ?? 0);
            $name = (string)($item['name'] ?? '');
            $category = new Category($id, $name);$this->categories[$category->getId()] =$category;
        }
    }

    private function loadProducts(string $filePath): void
    {
        $rawData = $this->readJsonFile($filePath);

        foreach ($rawData as$item) {
            $id = (int)($item['id'] ?? 0);
            $name = (string)($item['name'] ?? '');
            $price = (float)($item['price'] ?? 0.0);
            $categoryId = (int)($item['category_id'] ?? 0);
            $active = (bool)($item['active'] ?? true);

            $category = $this->categories[$categoryId] ?? new Category($categoryId > 0 ?$categoryId : 999, 'Без категории');

            $this->products[] = new Product($id, $name,$price, $category,$active);
        }
    }

    private function readJsonFile(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException("Файл данных не найден: {$filePath}");
        }

        $json = file_get_contents($filePath);
        if ($json === false) {
            throw new RuntimeException("Не удалось прочитать файл: {$filePath}");
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("Некорректный формат JSON в файле: {$filePath}");
        }

        return $decoded;
    }

    /** @return Category[] */
    public function getCategories(): array
    {
        return array_values($this->categories);
    }

    /** @return Product[] */
    public function getAllProducts(): array
    {
        return $this->products;
    }

    /** @return Product[] */
    public function filterByCategory(?int $categoryId, bool$onlyActive = false): array
    {
        return array_values(array_filter($this->products, function (Product$product) use ($categoryId,$onlyActive): bool {
            if ($categoryId !== null &&$categoryId > 0 && !$product->belongsToCategory($categoryId)) {
                return false;
            }
            if ($onlyActive && !$product->isActive()) {
                return false;
            }
            return true;
        }));
    }

    /** @param Product[] $products */
    public function countProducts(array $products): int
    {
        return count($products);
    }

    /** @param Product[] $products */
    public function calculateAveragePrice(array $products): float
    {
        if (empty($products)) {
            return 0.0;
        }

        $sum = array_reduce($products,
            fn(float $acc, Product$product): float => $acc +$product->getPrice(),
            0.0
        );

        return round($sum / count($products), 2);
    }

    /** @param Product[] $products */
    public function countAvailableProducts(array $products): int
    {
        return count(array_filter(
            $products,
            fn(Product $product): bool =>$product->isAvailableForOrder()
        ));
    }
}