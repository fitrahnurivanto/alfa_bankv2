<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Project Expense Categories
    |--------------------------------------------------------------------------
    |
    | Define available expense categories for projects
    |
    */
    'expense_categories' => [
        'honor' => 'Honor/Gaji',
        'tools' => 'Tools & Software',
        'advertising' => 'Advertising/Iklan',
        'freelancer' => 'Freelancer',
        'operational' => 'Operasional',
        'material' => 'Material',
        'other' => 'Lain-lain',
    ],

    /*
    |--------------------------------------------------------------------------
    | Expense Category Colors
    |--------------------------------------------------------------------------
    |
    | Color mappings for different expense categories
    |
    */
    'expense_colors' => [
        'honor' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800'],
        'tools' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
        'advertising' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
        'freelancer' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
        'operational' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
        'material' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
        'other' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Team Member Roles
    |--------------------------------------------------------------------------
    |
    | Available roles for team members in a project
    |
    */
    'team_roles' => [
        'pic' => 'PIC',
        'project_manager' => 'Project Manager',
        'content_creator' => 'Content Creator',
        'developer' => 'Developer',
        'designer' => 'Designer',
        'marketing' => 'Marketing',
        'seo_specialist' => 'SEO Specialist',
        'other' => 'Other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Status Configuration
    |--------------------------------------------------------------------------
    |
    | Status types and their display properties
    |
    */
    'statuses' => [
        'pending' => ['label' => 'Pending', 'color' => 'yellow'],
        'in_progress' => ['label' => 'In Progress', 'color' => 'blue'],
        'completed' => ['label' => 'Completed', 'color' => 'green'],

        'cancelled' => ['label' => 'Cancelled', 'color' => 'red'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Budget Thresholds
    |--------------------------------------------------------------------------
    |
    | Percentage thresholds for budget usage warnings
    |
    */
    'budget_thresholds' => [
        'safe' => 50,      // < 50% = green
        'warning' => 80,   // 50-80% = yellow/orange
        'danger' => 100,   // > 100% = red
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat Refresh Interval
    |--------------------------------------------------------------------------
    |
    | Interval in milliseconds for auto-refreshing chat messages
    |
    */
    'chat_refresh_interval' => 5000, // 5 seconds
];
