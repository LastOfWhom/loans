<?php

declare(strict_types=1);

namespace app\repositories\interfaces;

use app\models\LoanRequest;


interface LoanRequestRepositoryInterface
{
    /**
     * Сохраняет новую заявку на займ в базе данных
     */
    public function save(LoanRequest $model): bool;

    /**
     * Проверяет, есть ли у пользователя уже одобренная заявка
     */
    public function hasApprovedRequest(int $userId): bool;

    /**
     * Возвращает идентификаторы всех заявок в статусе «pending»
     *
     * @return int[]
     */
    public function getPendingIds(): array;

    /**
     * Пытается захватить блокировку строки на уровне БД
     * Возвращает null, если строка уже заблокирована другим воркером или больше не в статусе «pending»
     * Должен вызываться внутри активной транзакции
     */
    public function findAndLockPending(int $id): ?LoanRequest;

    /**
     * Обновляет статус заявки и сохраняет изменения
     */
    public function updateStatus(LoanRequest $model, string $status): bool;
}
