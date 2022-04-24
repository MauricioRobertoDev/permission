<?php

namespace MrDev\Permission\Commands;

use Illuminate\Console\Command;

class PermissionCommand extends Command
{
    public $signature = 'permission';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
