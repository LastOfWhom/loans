<?php

declare(strict_types=1);

namespace app\services\interfaces;

interface LoanRequestServiceInterface
{
    /**
     * Валидирует входные данные и сохраняет новую заявку на займ
     *
     * @param  array $data  Входные данные: user_id, amount, term
     * @return array{result: bool, id?: int}
     */
    public function create(array $data): array;
}
