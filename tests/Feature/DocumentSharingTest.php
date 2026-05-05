<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DocumentSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_document_owner_can_share_document_with_another_user(): void
    {
        $owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
        ]);

        $recipient = User::create([
            'name' => 'Shared User',
            'email' => 'shared@example.com',
            'password' => Hash::make('password'),
        ]);

        $document = Document::create([
            'owner_id' => $owner->id,
            'title' => 'Test Document',
            'content_html' => '<p>Test content</p>',
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);

        $token = $loginResponse->json('data.token');

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/documents/{$document->id}/share", [
                'user_id' => $recipient->id,
                'permission' => 'editor',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Document shared successfully.',
            ]);

        $this->assertDatabaseHas('document_shares', [
            'document_id' => $document->id,
            'user_id' => $recipient->id,
            'permission' => 'editor',
        ]);
    }
}