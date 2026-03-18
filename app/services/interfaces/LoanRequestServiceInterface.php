<?php

declare(strict_types=1);

namespace app\services\interfaces;

use app\dto\LoanRequestDto;

interface LoanRequestServiceInterface
{
    /**
     * Валидирует данные и сохраняет новую заявку на займ.
     *
     * @return array{result: bool, id?: int}
     */
    public function create(LoanRequestDto $dto): array;
}
