<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Creates the `loan_requests` table with all required indexes.
 *
 * Unique partial index on (user_id) WHERE status = 'approved' enforces
 * the business rule "a user can have at most one approved request" at the
 * database level, acting as a safety net for concurrent processing.
 */
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

        // Speed up pending-status queries (processor endpoint)
        $this->createIndex(
            'idx_loan_requests_status',
            self::TABLE,
            'status'
        );

        // Speed up per-user approved-status checks
        $this->createIndex(
            'idx_loan_requests_user_id',
            self::TABLE,
            'user_id'
        );

        // Enforce "at most one approved request per user" at the database level.
        // This is a partial unique index — only rows with status = 'approved' are included.
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
