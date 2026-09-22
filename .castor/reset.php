<?php

declare(strict_types=1);

namespace reset;

use Castor\Attribute\AsTask;

use function Castor\io;

#[AsTask(name: 'database', description: 'Wipe the dev database and replay every migration', aliases: ['reset'])]
function database(): void
{
    io()->section('Create the database if missing');
    \exec_in(\App::Backend, ['bin/console', 'doctrine:database:create', '--if-not-exists']);

    // Postgres cannot drop the database it is connected to.
    io()->section('Drop every table');
    \exec_in(\App::Backend, ['bin/console', 'doctrine:schema:drop', '--full-database', '--force']);

    io()->section('Run the migrations');
    \exec_in(\App::Backend, ['bin/console', 'doctrine:migrations:migrate', '--no-interaction', '--all-or-nothing']);

    io()->success('The database is fresh.');
}
