<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $amount
 * @property int    $term
 * @property string $status   One of: pending, approved, declined
 * @property string $created_at
 * @property string $updated_at
 */
class LoanRequest extends ActiveRecord
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';

    public static function tableName(): string
    {
        return 'loan_requests';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term'], 'integer'],
            ['user_id', 'integer', 'min' => 1],
            ['amount',  'integer', 'min' => 1],
            ['term',    'integer', 'min' => 1],
            [
                'status',
                'in',
                'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_DECLINED],
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'user_id'    => 'User ID',
            'amount'     => 'Loan Amount',
            'term'       => 'Loan Term (days)',
            'status'     => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
