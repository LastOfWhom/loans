<?php

declare(strict_types=1);

use yii\db\Migration;

class m240101_000000_create_loan_requests_table extends Migration
{
    private const TABLE = 'loan_requests';

    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer()->notNull(),
            'amount'     => $this->integer()->notNull(),
            'term'       => $this->integer()->notNull(),
            'status'     => $this->string(20)->notNull()->defaultValue('pending'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->createIndex(
            'idx_loan_requests_status',
            self::TABLE,
            'status'
        );

        $this->createIndex(
            'idx_loan_requests_user_id',
            self::TABLE,
            'user_id'
        );

        $this->execute(
            "CREATE UNIQUE INDEX idx_loan_requests_user_approved
             ON loan_requests (user_id)
             WHERE status = 'approved'"
        );
    }

    public function safeDown(): void
    {
        $this->dropTable(self::TABLE);
    }
}
