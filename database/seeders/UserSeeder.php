<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $dept = fn (string $code) => Department::where('code', $code)->first()->id;
        $role = fn (string $code) => Role::where('code', $code)->first()->id;

        $users = [
            ['name' => 'Adhika',   'email' => 'adhika@company.com',   'dept' => 'IT',  'role' => 'HEAD',  'is_admin' => true],
            ['name' => 'Axel',   'email' => 'axel@company.com',   'dept' => 'IT',  'role' => 'STAFF', 'is_admin' => false],
            ['name' => 'Citra',  'email' => 'citra@company.com',  'dept' => 'FIN', 'role' => 'MGR',   'is_admin' => false],
            ['name' => 'Jiyhan',   'email' => 'jiyhan@company.com',   'dept' => 'FIN', 'role' => 'STAFF', 'is_admin' => false],
            ['name' => 'Meilany', 'email' => 'meilany@company.com',    'dept' => 'MKT', 'role' => 'SPV',   'is_admin' => false],
            ['name' => 'Harvey',  'email' => 'harvey@company.com',  'dept' => 'MKT', 'role' => 'STAFF', 'is_admin' => false],
            ['name' => 'Nayyara',   'email' => 'nayyara@company.com',   'dept' => 'IT',  'role' => 'MGR',   'is_admin' => false],
            ['name' => 'Herman', 'email' => 'herman@company.com', 'dept' => 'FIN', 'role' => 'SPV',   'is_admin' => false],
            ['name' => 'Indah',  'email' => 'indah@company.com',  'dept' => 'MKT', 'role' => 'MGR',   'is_admin' => false],
            ['name' => 'Sylvia',   'email' => 'sylvia@company.com',   'dept' => 'IT',  'role' => 'STAFF', 'is_admin' => false],
        ];

        foreach ($users as $u) {
            User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => bcrypt('password'), // password sama semua untuk kemudahan testing
                'department_id' => $dept($u['dept']),
                'role_id' => $role($u['role']),
                'is_admin' => $u['is_admin'],
            ]);
        }
    }
}