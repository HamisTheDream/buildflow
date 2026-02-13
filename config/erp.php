<?php

return [
    'roles' => [
        'owner' => [
            'label' => 'Director / Owner',
            'modules' => ['projects', 'crm', 'finance', 'hr', 'settings'],
        ],
        'admin' => [
            'label' => 'Administrator',
            'modules' => ['projects', 'crm', 'finance', 'hr', 'settings'],
        ],
        'project_manager' => [
            'label' => 'Project Manager',
            'modules' => ['projects'],
        ],
        'sales_agent' => [
            'label' => 'Sales Agent',
            'modules' => ['crm'],
        ],
        'accountant' => [
            'label' => 'Accountant',
            'modules' => ['finance'],
        ],
        'hr_manager' => [
            'label' => 'HR Manager',
            'modules' => ['hr'],
        ],
        'member' => [
            'label' => 'Team Member',
            'modules' => ['projects'], // Standard members see projects they are assigned to
        ],
    ],

    'modules' => [
        'projects' => [
            'label' => 'Construction',
            'icon' => 'hard-hat',
            'route' => 'dashboard',
        ],
        'crm' => [
            'label' => 'Sales & CRM',
            'icon' => 'building-office',
            'route' => 'crm.dashboard',
        ],
        'finance' => [
            'label' => 'Finance',
            'icon' => 'banknotes',
            'route' => 'finance.dashboard',
        ],
        'hr' => [
            'label' => 'Human Resources',
            'icon' => 'users',
            'route' => 'hr.dashboard',
        ],
    ],
];
