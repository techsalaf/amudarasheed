<?php

namespace Botble\ACL\Commands;

use Illuminate\Console\Command;

class UserPasswordCommand extends Command
{
    protected $signature = 'cms:user:password {--email=} {--password=}';

    protected $description = 'Update a CMS user password (placeholder implementation)';

    public function handle(): int
    {
        $this->warn('UserPasswordCommand is a placeholder in this repository.');
        $this->line('Provide real implementation if needed for CLI usage.');

        return self::SUCCESS;
    }
}
