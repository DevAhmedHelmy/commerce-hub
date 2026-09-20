<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

/**
 * Safely provision the first (or an additional) super_admin (prompt 40 §10). No hard-coded or
 * default credentials: the password is read from --password or prompted (hidden). Ensures the
 * RBAC roles exist first, then creates/updates the user and assigns super_admin.
 *
 * Usage: php artisan app:create-super-admin "Name" email@example.com [--password=...]
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'app:create-super-admin {name} {email} {--password=}';

    protected $description = 'Create or promote a dashboard user to super_admin (RBAC).';

    public function handle(): int
    {
        (new RolesAndPermissionsSeeder())->run();

        $name = (string) $this->argument('name');
        $email = (string) $this->argument('email');
        $password = (string) ($this->option('password') ?: $this->secret('Password (min 8 chars)'));

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', Password::min(8)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password), 'is_active' => true],
        );

        $user->assignRole(RolesAndPermissionsSeeder::ROLE_SUPER_ADMIN);

        $this->info("Super admin ready: {$email}");

        return self::SUCCESS;
    }
}
