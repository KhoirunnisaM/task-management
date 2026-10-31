<?php

namespace Tests\Feature\Database;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use Tests\TestCase;

class EnumTest extends TestCase
{
    public function test_enum_values_match_the_database_contract(): void
    {
        $this->assertSame('admin', UserRole::Admin->value);
        $this->assertSame('manager', UserRole::Manager->value);
        $this->assertSame('member', UserRole::Member->value);

        $this->assertSame('manager', ProjectRole::Manager->value);
        $this->assertSame('member', ProjectRole::Member->value);

        $this->assertSame('planning', ProjectStatus::Planning->value);
        $this->assertSame('active', ProjectStatus::Active->value);
        $this->assertSame('on_hold', ProjectStatus::OnHold->value);
        $this->assertSame('completed', ProjectStatus::Completed->value);
        $this->assertSame('archived', ProjectStatus::Archived->value);

        $this->assertSame('todo', TaskStatus::Todo->value);
        $this->assertSame('in_progress', TaskStatus::InProgress->value);
        $this->assertSame('in_review', TaskStatus::InReview->value);
        $this->assertSame('completed', TaskStatus::Completed->value);

        $this->assertSame('low', TaskPriority::Low->value);
        $this->assertSame('medium', TaskPriority::Medium->value);
        $this->assertSame('high', TaskPriority::High->value);
        $this->assertSame('urgent', TaskPriority::Urgent->value);
    }

    public function test_enum_labels_use_indonesian_translations(): void
    {
        $this->assertSame('Admin', UserRole::Admin->label());
        $this->assertSame('Manajer', ProjectRole::Manager->label());
        $this->assertSame('Aktif', ProjectStatus::Active->label());
        $this->assertSame('Dikerjakan', TaskStatus::InProgress->label());
        $this->assertSame('Mendesak', TaskPriority::Urgent->label());
    }
}
