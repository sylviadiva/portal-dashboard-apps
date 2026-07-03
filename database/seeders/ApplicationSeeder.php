<?php

namespace Database\Seeders;

use App\Models\Application;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $applications = [
            ['name' => 'Email Corporate', 'url' => 'https://mail.google.com', 'category' => 'Communication', 'color' => '#4F46E5'],
            ['name' => 'Slack', 'url' => 'https://slack.com', 'category' => 'Communication', 'color' => '#611F69'],
            ['name' => 'Accounting System', 'url' => 'https://www.zohobooks.com', 'category' => 'Finance', 'color' => '#0F6E56'],
            ['name' => 'Payroll System', 'url' => 'https://www.gadjian.com', 'category' => 'Finance', 'color' => '#0F6E56'],
            ['name' => 'CRM Tool', 'url' => 'https://crm.zoho.com', 'category' => 'Marketing', 'color' => '#D85A30'],
            ['name' => 'Ads Manager', 'url' => 'https://ads.google.com', 'category' => 'Marketing', 'color' => '#D85A30'],
            ['name' => 'Server Monitoring', 'url' => 'https://grafana.com', 'category' => 'IT', 'color' => '#185FA5'],
            ['name' => 'Ticketing System', 'url' => 'https://www.atlassian.com/software/jira', 'category' => 'IT', 'color' => '#185FA5'],
            ['name' => 'HR Portal', 'url' => 'https://www.bamboohr.com', 'category' => 'HR', 'color' => '#993C1D'],
        ];

        foreach ($applications as $app) {
            Application::create($app);
        }
    }
}