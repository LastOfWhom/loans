<?php

declare(strict_types=1);

namespace app\services\interfaces;

/**
 * Контракт для сценария обработки заявок на займ.
 */
interface LoanProcessorServiceInterface
{
    /**
     * Обрабатывает все заявки в статусе «pending».
     *
     * Каждая заявка получает статус «approved» (10 %) или «declined» (90 %) случайным образом.
     * У одного пользователя не может быть более одной одобренной заявки.
     *
     * @param int $delay Задержка в секундах перед принятием решения (эмуляция времени обработки).
     */
    public function processAll(int $delay): void;
}
