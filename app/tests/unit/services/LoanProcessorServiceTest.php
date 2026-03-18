<?php

declare(strict_types=1);

namespace tests\unit\services;

use app\models\LoanRequest;
use app\repositories\interfaces\LoanRequestRepositoryInterface;
use app\services\LoanProcessorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yii;

/**
 * Тесты для LoanProcessorService.
 *
 * Проверяемые сценарии:
 *  - Нет заявок в статусе «pending» — репозиторий не вызывается.
 *  - Заявка заблокирована другим воркером (SKIP LOCKED) — пропускается.
 *  - Пользователь уже имеет одобренную заявку — статус всегда «declined».
 *  - Несколько заявок — updateStatus вызывается для каждой.
 *
 * Логика random_int (10 % одобрение) не тестируется — это одна строка
 * со встроенной функцией PHP, не содержащей бизнес-логики.
 */
class LoanProcessorServiceTest extends TestCase
{
    private LoanRequestRepositoryInterface&MockObject $repository;
    private LoanProcessorService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LoanRequestRepositoryInterface::class);
        $this->service    = new LoanProcessorService($this->repository);

        /*
         * Транзакции (beginTransaction/commit/rollBack) работают на SQLite без SQL-запросов,
         * поскольку все обращения к данным перехватываются моком репозитория.
         */
        Yii::$app->set('db', Yii::$app->db);
    }

    /**
     * Нет заявок — findAndLockPending никогда не вызывается.
     */
    public function testProcessAllDoesNothingWhenNoPendingRequests(): void
    {
        $this->repository->method('getPendingIds')->willReturn([]);
        $this->repository->expects($this->never())->method('findAndLockPending');

        $this->service->processAll(0);
    }

    /**
     * Строка заблокирована другим воркером (SKIP LOCKED вернул null) — updateStatus не вызывается.
     */
    public function testProcessAllSkipsAlreadyLockedRequest(): void
    {
        $this->repository->method('getPendingIds')->willReturn([1]);
        $this->repository->method('findAndLockPending')->with(1)->willReturn(null);
        $this->repository->expects($this->never())->method('updateStatus');

        $this->service->processAll(0);
    }

    /**
     * Пользователь уже имеет одобренную заявку — статус всегда «declined»,
     * независимо от результата random_int.
     */
    public function testProcessAllDeclinesWhenUserAlreadyHasApprovedRequest(): void
    {
        $request = $this->makeRequest(1, userId: 42);

        $this->repository->method('getPendingIds')->willReturn([1]);
        $this->repository->method('findAndLockPending')->with(1)->willReturn($request);
        $this->repository->method('hasApprovedRequest')->with(42)->willReturn(true);

        $this->repository->expects($this->once())
            ->method('updateStatus')
            ->with($request, LoanRequest::STATUS_DECLINED);

        $this->service->processAll(0);
    }

    /**
     * Несколько заявок — updateStatus вызывается ровно для каждой.
     */
    public function testProcessAllHandlesMultipleRequests(): void
    {
        $request1 = $this->makeRequest(1, userId: 1);
        $request2 = $this->makeRequest(2, userId: 2);

        $this->repository->method('getPendingIds')->willReturn([1, 2]);
        $this->repository->method('findAndLockPending')->willReturnMap([
            [1, $request1],
            [2, $request2],
        ]);
        $this->repository->method('hasApprovedRequest')->willReturn(false);

        $this->repository->expects($this->exactly(2))->method('updateStatus');

        $this->service->processAll(0);
    }

    private function makeRequest(int $id, int $userId): LoanRequest
    {
        $model = new LoanRequest();
        $model->setAttribute('id', $id);
        $model->setAttribute('user_id', $userId);
        $model->setAttribute('amount', 1000);
        $model->setAttribute('term', 30);
        $model->setAttribute('status', LoanRequest::STATUS_PENDING);
        return $model;
    }
}
