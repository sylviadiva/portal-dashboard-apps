<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Staff', 'code' => 'STAFF'],
            ['name' => 'Supervisor', 'code' => 'SPV'],
            ['name' => 'Manager', 'code' => 'MGR'],
            ['name' => 'Head', 'code' => 'HEAD'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}