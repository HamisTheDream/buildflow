<?php

namespace App\Enums;

enum Role: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case PROJECT_MANAGER = 'project_manager';
    case SALES_AGENT = 'sales_agent';
    case ACCOUNTANT = 'accountant';
    case HR_MANAGER = 'hr_manager';
    case MEMBER = 'member';
}
