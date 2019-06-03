<?php

namespace WolfpackIT\oauth\migrations;

use yii\db\Migration;

class M190603100000CreateOauthTables extends Migration
{
    public function safeUp()
    {
        //client table
        $this->createTable(
            '{{%client}}',
            [
                'id' => $this->primaryKey(),
                'identifier' => $this->string()->notNull(),
                'secret' => $this->string(), // not confidential if null
                'name' => $this->string()->notNull(),
                'status' => $this->boolean()->notNull()->defaultValue(true),
                'created_by' => $this->integer()->notNull(),
                'created_at' => $this->timestamp(),
                'updated_by' => $this->integer()->notNull(),
                'updated_at' => $this->timestamp()->defaultValue(null),
                'deleted_by' => $this->integer(),
                'deleted_at' => $this->timestamp()->defaultValue(null),
            ]
        );
        $this->createIndex('identifier', '{{%client}}', ['identifier'], true);

        //client_grant_type table
        $this->createTable(
            '{{%client_grant_type}}',
            [
                'client_id' => $this->integer()->notNull(),
                'grant_type' => $this->string()->notNull()
            ]
        );
        $this->addPrimaryKey('', '{{%client_grant_type}}', ['client_id', 'grant_type']);
        $this->addForeignKey(
            'fk-client_grant_type-client_id',
            '{{%client_grant_type}}',
            ['client_id'],
            '{{%client}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        //client_redirect table
        $this->createTable(
            '{{%client_redirect}}',
            [
                'id' => $this->primaryKey(),
                'client_id' => $this->integer()->notNull(),
                'redirect_uri' => $this->string()->notNull()
            ]
        );
        $this->addForeignKey(
            'fk-client_redirect-client_id',
            '{{%client_redirect}}',
            ['client_id'],
            '{{%client}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        //access_token table
        $this->createTable(
            '{{%access_token}}',
            [
                'id' => $this->primaryKey(),
                'client_id' => $this->integer()->notNull(),
                'user_id' => $this->integer(),
                'identifier' => $this->string(),
                'expired_at' => $this->timestamp()->null(),
                'status' => $this->tinyInteger(1)->notNull()
            ]
        );
        $this->createIndex('identifier', '{{%access_token}}', ['identifier'], true);
        $this->addForeignKey(
            'fk-access_token-client_id',
            '{{%access_token}}',
            ['client_id'],
            '{{%client}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        //scope table
        $this->createTable(
            '{{%scope}}',
            [
                'id' => $this->primaryKey(),
                'identifier' => $this->string()->notNull(),
                'name' => $this->string(),
                'user_name' => $this->string()
            ]
        );
        $this->createIndex('identifier', '{{%scope}}', ['identifier'], true);

        //client_scope table
        $this->createTable(
            '{{%client_scope}}',
            [
                'id' => $this->primaryKey(),
                'client_id' => $this->integer()->notNull(),
                'scope_id' => $this->integer()->notNull(),
                'user_id' => $this->integer(),
                'grant_type' => $this->string(),
                'is_default' => $this->boolean()->notNull()->defaultValue(false)
            ]
        );
        $this->createIndex(
            'client_id-scope-id-user_id-grant_type',
            '{{%client_scope}}',
            ['client_id', 'scope_id', 'user_id', 'grant_type'],
            true
        );
        $this->addForeignKey(
            'fk-client_scope-client_id',
            '{{%client_scope}}',
            ['client_id'],
            '{{%client}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-client_scope-scope_id',
            '{{%client_scope}}',
            ['scope_id'],
            '{{%scope}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->createIndex('grant_type', '{{%client_scope}}', ['grant_type']);
        $this->createIndex('is_default', '{{%client_scope}}', ['is_default']);

        //access_token_scope table
        $this->createTable(
            '{{%access_token_scope}}',
            [
                'access_token_id' => $this->integer()->notNull(),
                'scope_id' => $this->integer()->notNull()
            ]
        );
        $this->addPrimaryKey('', '{{%access_token_scope}}', ['access_token_id', 'scope_id']);
        $this->addForeignKey(
            'fk-access_token_scope-access_token_id',
            '{{%access_token_scope}}',
            ['access_token_id'],
            '{{%access_token}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-access_token_scope-scope_id',
            '{{%access_token_scope}}',
            ['scope_id'],
            '{{%scope}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        //refresh_token table
        $this->createTable(
            '{{%refresh_token}}',
            [
                'id' => $this->primaryKey(),
                'access_token_id' => $this->integer()->notNull(),
                'identifier' => $this->string(),
                'expired_at' => $this->timestamp()->null(),
                'status' => $this->tinyInteger()->notNull()
            ]
        );
        $this->createIndex('identifier', '{{%refresh_token}}', ['identifier'], true);
        $this->addForeignKey(
            'fk-refresh_token-access_token_id',
            '{{%refresh_token}}',
            ['access_token_id'],
            '{{%access_token}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        //auth_code table
        $this->createTable(
            '{{%auth_code}}',
            [
                'id' => $this->primaryKey(),
                'client_id' => $this->integer()->notNull(),
                'user_id' => $this->integer(),
                'identifier' => $this->string(),
                'expired_at' => $this->timestamp()->notNull(),
                'redirect_uri' => $this->string(),
                'status' => $this->tinyInteger(1)->notNull()
            ]
        );
        $this->createIndex('identifier', '{{%auth_code}}', ['identifier'], true);
        $this->addForeignKey(
            'fk-auth_code-client_id',
            '{{%auth_code}}',
            ['client_id'],
            '{{%client}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        //auth_code_scope table
        $this->createTable(
            '{{%auth_code_scope}}',
            [
                'auth_code_id' => $this->integer()->notNull(),
                'scope_id' => $this->integer()->notNull()
            ]
        );
        $this->addPrimaryKey('', '{{%auth_code_scope}}', ['auth_code_id', 'scope_id']);
        $this->addForeignKey(
            'fk-auth_code_scope-auth_code_id',
            '{{%auth_code_scope}}',
            ['auth_code_id'],
            '{{%auth_code}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-auth_code_scope-scope_id',
            '{{%auth_code_scope}}',
            ['scope_id'],
            '{{%scope}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    public function safeDown()
    {
        $this->dropTable('{{%auth_code_scope}}');
        $this->dropTable('{{%auth_code}}');
        $this->dropTable('{{%refresh_token}}');
        $this->dropTable('{{%access_token_scope}}');
        $this->dropTable('{{%client_scope}}');
        $this->dropTable('{{%scope}}');
        $this->dropTable('{{%access_token}}');
        $this->dropTable('{{%client_redirect}}');
        $this->dropTable('{{%client_grant_type}}');
        $this->dropTable('{{%client}}');

        return true;
    }
}