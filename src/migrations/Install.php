<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\migrations;

use craft\db\Migration;

/**
 * Installation Migration
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->addIndexes();
        $this->addForeignKeys();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%enupalsocializer_providers}}');
        $this->dropTableIfExists('{{%enupalsocializer_tokens}}');

        return true;
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable('{{%enupalsocializer_providers}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'clientId' => $this->string(),
            'clientSecret' => $this->string(),
            'fieldMapping' => $this->text(),
            //
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%enupalsocializer_tokens}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'accessToken' => $this->text(),
            'providerId' => $this->integer(),
            //
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);
    }

    protected function addIndexes()
    {
        $this->createIndex(null, '{{%enupalsocializer_tokens}}', 'userId', false);
        $this->createIndex(null, '{{%enupalsocializer_tokens}}', 'providerId', false);
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%enupalsocializer_providers}}', 'id'
            ),
            '{{%enupalsocializer_providers}}', 'id',
            '{{%elements}}', 'id', 'CASCADE', null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%enupalsocializer_tokens}}', 'userId'
            ),
            '{{%enupalsocializer_tokens}}', 'userId',
            '{{%users}}', 'id', 'CASCADE', null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%enupalsocializer_tokens}}', 'providerId'
            ),
            '{{%enupalsocializer_tokens}}', 'providerId',
            '{{%enupalsocializer_providers}}', 'id', 'CASCADE', null
        );
    }
}