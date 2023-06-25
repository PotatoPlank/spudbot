<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230625161018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration';
    }

    public function up(Schema $schema): void
    {
        $tableExistsSql = <<<SQL
        SELECT * 
        FROM information_schema.tables
        WHERE table_schema = ? 
            AND table_name = ?
        LIMIT 1;
SQL;

        $params = [$_ENV['DATABASE_NAME'], 'guilds'];
        $result = $this->connection->executeQuery($tableExistsSql, $params)
            ->fetchAllAssociative();

        if (count($result) > 0) {
            return;
        }

        $guildSql = <<<SQL
        CREATE TABLE `guilds` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `discord_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
          `output_channel_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
          `output_thread_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `modified_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `guilds_pk` (`discord_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
SQL;

        $this->addSql($guildSql);

        $memberSql = <<<SQL
        CREATE TABLE `members` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `discord_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
          `guild_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
          `total_comments` int(11) DEFAULT 0,
          `created_at` datetime DEFAULT NULL,
          `modified_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
SQL;
        $this->addSql($memberSql);

        $threadSql = <<<SQL
        CREATE TABLE `threads` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `discord_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
              `guild_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
              `created_at` datetime DEFAULT NULL,
              `modified_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `channel_pk` (`discord_id`,`guild_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
SQL;
        $this->addSql($threadSql);

        $eventSql = <<<SQL
        CREATE TABLE `events` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `guild_id` int(11) NOT NULL,
          `channel_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
          `name` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
          `type` varchar(256) CHARACTER SET utf8mb4 NOT NULL,
          `sesh_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
          `native_id` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
          `scheduled_at` datetime DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `modified_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `events_pk` (`sesh_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
SQL;
        $this->addSql($eventSql);

        $eventAttendanceSql = <<<SQL
        CREATE TABLE `event_attendance` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `event_id` int(11) DEFAULT NULL,
          `member_id` int(11) DEFAULT NULL,
          `status` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
          `no_show` tinyint(4) DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `modified_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
SQL;
        $this->addSql($eventAttendanceSql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
