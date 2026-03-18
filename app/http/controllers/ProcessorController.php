<?php

declare(strict_types=1);

namespace app\http\controllers;

use app\services\interfaces\LoanProcessorServiceInterface;
use Yii;
use yii\base\Module;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class ProcessorController extends Controller
{
    public $enableCsrfValidation = false;

    public function __construct(
        string $id,
        Module $module,
        private readonly LoanProcessorServiceInterface $loanProcessorService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @return array{result: bool}
     * @throws BadRequestHttpException
     */
    public function actionIndex(): array
    {
        $rawDelay = Yii::$app->request->get('delay', 0);

        if (!ctype_digit((string) $rawDelay)) {
            throw new BadRequestHttpException('Parameter "delay" must be a non-negative integer.');
        }

        $this->loanProcessorService->processAll((int) $rawDelay);

        return ['result' => true];
    }
}
