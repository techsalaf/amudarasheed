<?php

namespace Botble\ACL\Commands;

use Illuminate\Console\Command;

class UserCreateCommand extends Command
{
    protected $signature = 'cms:user:create {--name=} {--email=} {--password=}';

    protected $description = 'Create a CMS user (placeholder implementation)';

    public function handle(): int
    {
        $this->warn('UserCreateCommand is a placeholder in this repository.');
        $this->line('Provide real implementation if needed for CLI usage.');

        return self::SUCCESS;
    }
}
