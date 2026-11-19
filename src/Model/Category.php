<?php

declare(strict_types=1);

final class Category
{
    public function __construct(
        private int $id,
        private string $name
    ) {
        if ($this->id <= 0) {
            throw new InvalidArgumentException('ID категории должен быть положительным числом.');
        }

        $trimmedName = trim($this->name);
        if ($trimmedName === '') {
            throw new InvalidArgumentException('Название категории не может быть пустым.');
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
}