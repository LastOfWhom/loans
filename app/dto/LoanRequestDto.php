<?php

declare(strict_types=1);

namespace app\dto;

/**
 * DTO входящих данных для создания заявки на займ.
 *
 * Отвечает только за перенос данных от HTTP-слоя к сервисному.
 * Бизнес-валидация (min, required и т.д.) остаётся в модели.
 *
 * @throws \InvalidArgumentException если обязательные поля отсутствуют в массиве.
 */
class LoanRequestDto
{
    public function __construct(
        public readonly int $userId,
        public readonly int $amount,
        public readonly int $term,
    ) {
    }

    /**
     * Создаёт DTO из сырого массива входных данных.
     * Выбрасывает исключение при отсутствии обязательных ключей.
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['user_id'], $data['amount'], $data['term'])) {
            throw new \InvalidArgumentException('Missing required fields: user_id, amount, term.');
        }

        return new self(
            userId: (int) $data['user_id'],
            amount: (int) $data['amount'],
            term:   (int) $data['term'],
        );
    }
}
