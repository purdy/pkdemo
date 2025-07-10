<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SetupInitialTables extends Migration {
  public function up() {
    // Add the user table
    $this->forge->addField([
      'id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
        'auto_increment' => true,
      ],
      'guid' => [
        'type' => 'VARCHAR',
        'constraint' => 6,
        'null' => true,
      ],
      'email' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
      ],
      'created_at' => [
        'type' => 'bigint',
        'unsigned' => true,
        'null' => true,
        'default' => 0,
      ],
      'updated_at' => [
        'type' => 'bigint',
        'unsigned' => true,
        'null' => true,
        'default' => 0,
      ],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey('email');
    $this->forge->createTable('user');

    // Add the passkey table
    $this->forge->addField([
      'id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
        'auto_increment' => true,
      ],
      'user_id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
      ],
      'unique_id' => [
        'type' => 'VARCHAR',
        'constraint' => 16,
        'default' => '',
      ],
      'nickname' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
        'default' => '',
      ],
      'credential_id' => [
        'type' => 'VARCHAR',
        'constraint' => 100,
      ],
      'public_key' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
      ],
      'created_at' => [
        'type' => 'bigint',
        'unsigned' => true,
        'null' => true,
        'default' => 0,
      ],
      'updated_at' => [
        'type' => 'bigint',
        'unsigned' => true,
        'null' => true,
        'default' => 0,
      ],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('user_id', 'user', 'id', '', 'CASCADE');
    $this->forge->createTable('passkey');

    // Add the user_file table
    $this->forge->addField([
      'id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
        'auto_increment' => true,
      ],
      'user_id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
      ],
      'file_name' => [
        'type' => 'VARCHAR',
        'constraint' => 255,
      ],
      'file_size' => [
        'type' => 'BIGINT',
        'unsigned' => true,
      ],
      'created_at' => [
        'type' => 'bigint',
        'unsigned' => true,
        'null' => true,
        'default' => 0,
      ],
      'updated_at' => [
        'type' => 'bigint',
        'unsigned' => true,
        'null' => true,
        'default' => 0,
      ],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('user_id', 'user', 'id', '', 'CASCADE');
    $this->forge->createTable('user_file');
  }

  public function down() {
    // Drop the user table
    // Drop the passkey table
    // Drop the user_file table
    $this->forge->dropTable('user', true);
    $this->forge->dropTable('passkey', true);
    $this->forge->dropTable('user_file', true);
  }
}

/*
CREATE TABLE `user` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `created_at` bigint(20) unsigned NOT NULL,
  `modified_at` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `passkey` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `unique_id` varchar(16) NOT NULL DEFAULT '',
  `nickname` varchar(255) NOT NULL DEFAULT '',
  `credential_id` varchar(100) NOT NULL,
  `public_key` varchar(255) NOT NULL,
  `created_at` bigint(20) unsigned NOT NULL,
  `modified_at` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE `user_file` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `created_at` bigint(20) unsigned NOT NULL,
  `modified_at` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
);
*/
