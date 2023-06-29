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
final class Version20230628233435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $channelSql = <<<SQL
            CREATE TABLE spud_bot.directories (
                id INT UNSIGNED auto_increment NULL,
                directory_channel_id INT UNSIGNED NOT NULL,
                forum_channel_id INT UNSIGNED NOT NULL,
                embed_id varchar(256) NOT NULL,
                created_at DATETIME NULL,
                modified_at DATETIME NULL,
                CONSTRAINT directories_PK PRIMARY KEY (id)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_520_ci;
SQL;

        $this->addSql($channelSql);

        $this->addSql('alter table spud_bot.threads add channel_id int(11) null;');
        $this->addSql('alter table spud_bot.threads add tag varchar(100) null;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS spud_bot.directories;');
        $this->addSql('alter table spud_bot.threads drop column channel_id;');
        $this->addSql('alter table spud_bot.threads drop column tag;');
    }
}
