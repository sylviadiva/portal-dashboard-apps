<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccessRuleSeeder extends Seeder
{
    public function run(): void
    {
        $app = fn (string $name) => Application::where('name', $name)->first();
        $dept = fn (string $code) => Department::where('code', $code)->first();
        $role = fn (string $code) => Role::where('code', $code)->first();
        $user = fn (string $email) => User::where('email', $email)->first();

        // --- Assign by Departemen ---
        $dept('IT')->applications()->attach([
            $app('Server Monitoring')->id,
            $app('Ticketing System')->id,
            $app('Email Corporate')->id,
        ]);

        $dept('FIN')->applications()->attach([
            $app('Accounting System')->id,
            $app('Payroll System')->id,
            $app('Email Corporate')->id,
        ]);

        $dept('MKT')->applications()->attach([
            $app('CRM Tool')->id,
            $app('Ads Manager')->id,
            $app('Email Corporate')->id,
        ]);

        // --- Assign by Role ---
        $role('MGR')->applications()->attach([
            $app('HR Portal')->id,
            $app('Payroll System')->id,
        ]);

        $role('HEAD')->applications()->attach([
            $app('HR Portal')->id,
            $app('Server Monitoring')->id,
        ]);

        $role('SPV')->applications()->attach([
            $app('CRM Tool')->id,
        ]);

        // --- Assign by Specific User (sengaja overlap untuk test dedup) ---

        // Adhika: dept IT + role HEAD, ditambah akses langsung ke Slack
        $user('adhika@company.com')->specificApplications()->attach([
            $app('Slack')->id,
        ]);

        // Citra: dept FIN + role MGR, tapi dapat akses langsung lintas kategori
        $user('citra@company.com')->specificApplications()->attach([
            $app('Slack')->id,
            $app('Server Monitoring')->id,
        ]);

        // Nayyara: dept IT + role MGR (sudah dapat Payroll dari role MGR),
        // sengaja ditambah lagi specific ke Payroll System -> overlap ganda
        $user('nayyara@company.com')->specificApplications()->attach([
            $app('Payroll System')->id,
        ]);
    }
}