<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Organization;

use App\Models\Project;

class UsageService
{
    public function getUsage(Organization $org): array
    {
        $projectsCount = Project::where('organization_id', $org->id)->count();
        // Members: distinct users who have accepted (in pivot)
        $membersCount = $org->users()->count();

        // Storage: Attachments + ProjectMedia
        $bytesAttachments = (int) Attachment::where('organization_id', $org->id)->sum('size');
        // ProjectMedia belongs to Project, not Org directly
        $bytesMedia = (int) \App\Models\ProjectMedia::whereIn('project_id', $org->projects()->select('id'))->sum('size');

        $totalBytes = $bytesAttachments + $bytesMedia;
        $storageMb = (int) round($totalBytes / 1024 / 1024);

        return [
            'projects' => $projectsCount,
            'members' => $membersCount,
            'storage_mb' => $storageMb,
        ];
    }

    public function withinLimits(Organization $org): array
    {
        $plan = $org->effectivePlan();
        $usage = $this->getUsage($org);

        if (!$plan) {
            return [
                'usage' => $usage,
                'limits' => ['max_projects' => 1, 'max_members' => 3, 'max_storage_mb' => 200],
                'can' => [
                    'create_project' => $usage['projects'] < 1,
                    'invite_member' => $usage['members'] < 3,
                    'upload' => $usage['storage_mb'] < 200,
                    'password_protect_reports' => false,
                ],
            ];
        }

        $active = $org->isActiveAccess();

        if (!$active) {
            return [
                'usage' => $usage,
                'limits' => [
                    'max_projects' => 0,
                    'max_members' => 0,
                    'max_storage_mb' => 0,
                ],
                'can' => [
                    'create_project' => false,
                    'invite_member' => false,
                    'upload' => false,
                    'password_protect_reports' => false,
                ],
            ];
        }

        return [
            'usage' => $usage,
            'limits' => [
                'max_projects' => $plan->max_projects,
                'max_members' => $plan->max_members,
                'max_storage_mb' => $plan->max_storage_mb,
            ],
            'can' => [
                'create_project' => $usage['projects'] < $plan->max_projects,
                'invite_member' => $usage['members'] < $plan->max_members,
                'upload' => $usage['storage_mb'] < $plan->max_storage_mb,
                'password_protect_reports' => (bool)$plan->can_password_protect_reports,
            ],
        ];
    }
}
