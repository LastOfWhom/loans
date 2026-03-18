<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\LoanRequest;
use app\repositories\interfaces\LoanRequestRepositoryInterface;
use Yii;

class LoanRequestRepository implements LoanRequestRepositoryInterface
{
    public function save(LoanRequest $model): bool
    {
        return $model->save();
    }

    public function hasApprovedRequest(int $userId): bool
    {
        return LoanRequest::find()
            ->where([
                'user_id' => $userId,
                'status'  => LoanRequest::STATUS_APPROVED,
            ])
            ->exists();
    }

    public function getPendingIds(): array
    {
        return LoanRequest::find()
            ->select('id')
            ->where(['status' => LoanRequest::STATUS_PENDING])
            ->column();
    }

    public function findAndLockPending(int $id): ?LoanRequest
    {
        return LoanRequest::findBySql(
            'SELECT * FROM loan_requests WHERE id = :id AND status = :status FOR UPDATE SKIP LOCKED',
            [':id' => $id, ':status' => LoanRequest::STATUS_PENDING]
        )->one();
    }

    public function updateStatus(LoanRequest $model, string $status): bool
    {
        $model->status = $status;
        return $model->save(false);
    }
}
