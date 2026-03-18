<?php

declare(strict_types=1);

namespace app\services;

use app\dto\LoanRequestDto;
use app\models\LoanRequest;
use app\repositories\interfaces\LoanRequestRepositoryInterface;
use app\services\interfaces\LoanRequestServiceInterface;

class LoanRequestService implements LoanRequestServiceInterface
{
    public function __construct(
        private readonly LoanRequestRepositoryInterface $repository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(LoanRequestDto $dto): array
    {
        $model          = new LoanRequest();
        $model->user_id = $dto->userId;
        $model->amount  = $dto->amount;
        $model->term    = $dto->term;

        if (!$model->validate()) {
            return ['result' => false];
        }

        if ($this->repository->hasApprovedRequest($dto->userId)) {
            return ['result' => false];
        }

        if (!$this->repository->save($model)) {
            return ['result' => false];
        }

        return [
            'result' => true,
            'id'     => $model->id,
        ];
    }
}
