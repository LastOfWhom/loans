<?php

declare(strict_types=1);

namespace tests\unit\services;

use app\dto\LoanRequestDto;
use app\models\LoanRequest;
use app\repositories\interfaces\LoanRequestRepositoryInterface;
use app\services\LoanRequestService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoanRequestServiceTest extends TestCase
{
    private LoanRequestRepositoryInterface&MockObject $repository;
    private LoanRequestService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LoanRequestRepositoryInterface::class);
        $this->service    = new LoanRequestService($this->repository);
    }

    /**
     * Успешное создание
     */
    public function testCreateReturnsSuccessWithId(): void
    {
        $this->repository->method('hasApprovedRequest')->willReturn(false);
        $this->repository
            ->method('save')
            ->willReturnCallback(static function (LoanRequest $model): bool {
                $model->setAttribute('id', 42);
                return true;
            });

        $result = $this->service->create(new LoanRequestDto(userId: 1, amount: 3000, term: 30));

        $this->assertTrue($result['result']);
        $this->assertSame(42, $result['id']);
    }

    /**
     * user_id = 0 не проходит проверку min => 1
     */
    public function testCreateFailsOnZeroUserId(): void
    {
        $this->repository->expects($this->never())->method('save');

        $result = $this->service->create(new LoanRequestDto(userId: 0, amount: 3000, term: 30));

        $this->assertFalse($result['result']);
    }

    /**
     * Отрицательная сумма займа не проходит валидацию
     */
    public function testCreateFailsOnNegativeAmount(): void
    {
        $this->repository->expects($this->never())->method('save');

        $result = $this->service->create(new LoanRequestDto(userId: 1, amount: -100, term: 30));

        $this->assertFalse($result['result']);
    }

    /**
     * Если у пользователя уже есть одобренная заявка — создание запрещено
     */
    public function testCreateFailsWhenUserAlreadyHasApprovedRequest(): void
    {
        $this->repository->method('hasApprovedRequest')->with(1)->willReturn(true);
        $this->repository->expects($this->never())->method('save');

        $result = $this->service->create(new LoanRequestDto(userId: 1, amount: 3000, term: 30));

        $this->assertFalse($result['result']);
        $this->assertArrayNotHasKey('id', $result);
    }

    /**
     * Ошибка сохранения в репозитории — возвращаем false
     */
    public function testCreateFailsWhenRepositorySaveReturnsFalse(): void
    {
        $this->repository->method('hasApprovedRequest')->willReturn(false);
        $this->repository->method('save')->willReturn(false);

        $result = $this->service->create(new LoanRequestDto(userId: 1, amount: 3000, term: 30));

        $this->assertFalse($result['result']);
        $this->assertArrayNotHasKey('id', $result);
    }

    /**
     * При успешном создании ключ 'id' присутствует в ответе
     */
    public function testCreateSuccessResponseContainsId(): void
    {
        $this->repository->method('hasApprovedRequest')->willReturn(false);
        $this->repository
            ->method('save')
            ->willReturnCallback(static function (LoanRequest $model): bool {
                $model->setAttribute('id', 99);
                return true;
            });

        $result = $this->service->create(new LoanRequestDto(userId: 5, amount: 500, term: 7));

        $this->assertArrayHasKey('id', $result);
        $this->assertSame(99, $result['id']);
    }
}
