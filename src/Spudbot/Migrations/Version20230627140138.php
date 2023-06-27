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
final class Version20230627140138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the reminders and channels table';
    }

    public function up(Schema $schema): void
    {
        $remindersSql = <<<SQL
            CREATE TABLE spud_bot.reminders (
                id INT UNSIGNED auto_increment NULL,
                description TEXT NOT NULL,
                mention_role varchar(256) NULL,
                scheduled_at DATETIME NOT NULL,
                repeats varchar(256) NULL,
                channel_id INT UNSIGNED NOT NULL,
                guild_id INT UNSIGNED NOT NULL,
                created_at DATETIME NULL,
                modified_at DATETIME NULL,
                CONSTRAINT reminders_PK PRIMARY KEY (id)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_520_ci;
SQL;

        $channelSql = <<<SQL
            CREATE TABLE spud_bot.channels (
                id INT UNSIGNED auto_increment NULL,
                discord_id varchar(256) NOT NULL,
                guild_id INT UNSIGNED NOT NULL,
                created_at DATETIME NULL,
                modified_at DATETIME NULL,
                CONSTRAINT channels_PK PRIMARY KEY (id)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_520_ci;
SQL;

        $this->addSql($channelSql);
        $this->addSql($remindersSql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS spud_bot.channels;');
        $this->addSql('DROP TABLE IF EXISTS spud_bot.reminders;');
    }
}
