<?php

namespace App\Enums;

enum DashboardRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Member = 'member';

    public function label(): string
    {
        return __("dashboard.roles.{$this->value}");
    }

    public function subtitle(): string
    {
        return __("dashboard.subtitles.{$this->value}");
    }
}
