<?php

declare(strict_types=1);

final class Product
{
    public function __construct(
        private int $id,
        private string $name,
        private float $price,
        private Category $category,
        private bool $active = true
    ) {
        if ($this->id <= 0) {
            throw new InvalidArgumentException('ID товара должен быть положительным числом.');
        }

        $trimmedName = trim($this->name);
        if ($trimmedName === '') {
            throw new InvalidArgumentException('Название товара не может быть пустым.');
        }

        if ($this->price < 0) {
            throw new InvalidArgumentException('Цена товара не может быть отрицательной.');
        }

        $this->name =$trimmedName;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    // Предметный метод 1: форматированная цена для вывода
    public function getFormattedPrice(): string
    {
        return number_format($this->price, 2, '.', ' ') . ' ₽';
    }

    // Предметный метод 2: расчёт цены со скидкой
    public function getDiscountedPrice(float $discountPercent): float
    {
        if ($discountPercent < 0 || $discountPercent > 100) {
            throw new InvalidArgumentException('Скидка должна быть в диапазоне от 0 до 100%.');
        }

        return round($this->price * (1 -$discountPercent / 100), 2);
    }

    // Предметный метод 3: проверка принадлежности к категории по ID
    public function belongsToCategory(int $categoryId): bool
    {
        return $this->category->getId() ===$categoryId;
    }

    // Предметный метод 4: проверка доступности для покупки (активен и цена > 0)
    public function isAvailableForOrder(): bool
    {
        return $this->active &&$this->price > 0;
    }

    // Предметный метод 5: текстовый статус наличия
    public function getAvailabilityLabel(): string
    {
        return $this->isAvailableForOrder() ? 'В наличии' : 'Нет в наличии';
    }
}