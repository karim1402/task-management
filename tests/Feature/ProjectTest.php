<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_list_only_their_own_projects(): void
    {
        $user = User::factory()->create();
        Project::factory()->count(3)->for($user)->create();
        Project::factory()->count(2)->create(); // someone else's

        Sanctum::actingAs($user);

        $this->getJson('/api/projects')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_a_user_can_create_a_project(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/projects', [
            'name' => 'New Project',
            'description' => 'Something useful',
            'status' => 'active',
        ])->assertStatus(201)->assertJsonPath('data.name', 'New Project');

        $this->assertDatabaseHas('projects', [
            'name' => 'New Project',
            'user_id' => $user->id,
        ]);
    }

    public function test_project_creation_validates_input(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/projects', ['status' => 'invalid'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status']);
    }

    public function test_a_user_cannot_view_another_users_project(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->for($owner)->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/projects/{$project->id}")->assertStatus(403);
    }

    public function test_a_user_can_update_their_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->putJson("/api/projects/{$project->id}", ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');
    }

    public function test_deleting_a_project_soft_deletes_it(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/projects/{$project->id}")->assertOk();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }
}
