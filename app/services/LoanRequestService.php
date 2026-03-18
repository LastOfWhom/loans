<?php

declare(strict_types=1);

namespace app\services;

use app\models\LoanRequest;
use app\repositories\interfaces\LoanRequestRepositoryInterface;
use app\services\interfaces\LoanRequestServiceInterface;

/**
 * Реализует сценарий подачи заявки на займ
 *
 * Бизнес-правила:
 *  - Поля user_id, amount, term обязательны и должны быть положительными целыми числами.
 *  - У пользователя не должно быть уже одобренной заявки.
 */
class LoanRequestService implements LoanRequestServiceInterface
{
    public function __construct(
        private readonly LoanRequestRepositoryInterface $repository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): array
    {
        $model = new LoanRequest();
        $model->setAttributes($data);

        if (!$model->validate()) {
            return ['result' => false];
        }

        if ($this->repository->hasApprovedRequest((int) $model->user_id)) {
            return ['result' => false];
        }

        if (!$this->repository->save($model)) {
            return ['result' => false];
        }

        return [
            'result' => true,
            'id'     => (int) $model->id,
        ];
    }
}
