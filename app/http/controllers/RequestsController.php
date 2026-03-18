<?php

declare(strict_types=1);

namespace app\http\controllers;

use app\services\interfaces\LoanRequestServiceInterface;
use Yii;
use yii\base\Module;
use yii\web\Controller;

class RequestsController extends Controller
{
    public $enableCsrfValidation = false;

    public function __construct(
        string $id,
        Module $module,
        private readonly LoanRequestServiceInterface $loanRequestService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @return array{result: bool, id?: int}
     */
    public function actionCreate(): array
    {
        $data   = Yii::$app->request->bodyParams;
        $result = $this->loanRequestService->create($data);

        Yii::$app->response->statusCode = $result['result'] ? 201 : 400;

        return $result;
    }
}
