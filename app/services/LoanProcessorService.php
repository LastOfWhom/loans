<?php

declare(strict_types=1);

namespace app\services;

use app\models\LoanRequest;
use app\repositories\interfaces\LoanRequestRepositoryInterface;
use app\services\interfaces\LoanProcessorServiceInterface;
use Yii;
use yii\db\Exception as DbException;
use yii\db\IntegrityException;

class LoanProcessorService implements LoanProcessorServiceInterface
{
    private const APPROVE_PROBABILITY = 10; // процентов из 100

    public function __construct(
        private readonly LoanRequestRepositoryInterface $repository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function processAll(int $delay): void
    {
        foreach ($this->repository->getPendingIds() as $id) {
            $this->processOne((int) $id, $delay);
        }
    }

    /**
     * Обрабатывает одну заявку в рамках отдельной транзакции
     */
    private function processOne(int $id, int $delay): void
    {
        $db          = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            // Захватываем эксклюзивную блокировку; null — строка уже заблокирована другим воркером.
            $request = $this->repository->findAndLockPending($id);

            if ($request === null) {
                $transaction->commit();
                return;
            }

            // Удерживаем блокировку во время эмуляции принятия решения.
            sleep($delay);

            $status = $this->decideStatus($request->user_id);

            $this->repository->updateStatus($request, $status);
            $transaction->commit();
        } catch (IntegrityException) {
            $transaction->rollBack();
            $this->forceDecline($id);
        } catch (DbException $e) {
            $transaction->rollBack();
            Yii::error('Ошибка БД при обработке заявки #' . $id . ': ' . $e->getMessage(), __METHOD__);
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Ошибка при обработке заявки #' . $id . ': ' . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * Определяет итоговый статус заявки
     */
    private function decideStatus(int $userId): string
    {
        if ($this->repository->hasApprovedRequest($userId)) {
            return LoanRequest::STATUS_DECLINED;
        }

        return $this->randomlyApproves()
            ? LoanRequest::STATUS_APPROVED
            : LoanRequest::STATUS_DECLINED;
    }

    /**
     * Возвращает true с вероятностью 10 %
     */
    private function randomlyApproves(): bool
    {
        return random_int(1, 100) <= self::APPROVE_PROBABILITY;
    }

    /**
     * Принудительно переводит заявку в «declined» в новой транзакции
     * Вызывается как запасной вариант после IntegrityException
     */
    private function forceDecline(int $id): void
    {
        $db          = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $request = $this->repository->findAndLockPending($id);

            if ($request !== null) {
                $this->repository->updateStatus($request, LoanRequest::STATUS_DECLINED);
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Ошибка forceDecline для заявки #' . $id . ': ' . $e->getMessage(), __METHOD__);
        }
    }
}
