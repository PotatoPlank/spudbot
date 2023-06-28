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
final class Version20230628230305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Track user verification';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('alter table members add verified_by int(11) null;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('alter table members drop column verified_by;');
    }
}
