<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210116221200 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        //Tab aggregate table
        $this->addSql('
            CREATE TABLE IF NOT EXISTS snapshot (
                aggregate_root_id VARCHAR(36) NOT NULL,
                aggregate_root_version MEDIUMINT(36) UNSIGNED NOT NULL,
                aggregate_type VARCHAR (256) NOT NULL,
                time_of_recording TIMESTAMP(6),
                payload JSON NOT NULL,
                PRIMARY KEY (aggregate_root_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
