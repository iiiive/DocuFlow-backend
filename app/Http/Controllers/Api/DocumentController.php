<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentShare;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $ownedDocuments = Document::where('owner_id', $user->id)
            ->latest()
            ->get();

        $sharedDocuments = Document::whereHas('shares', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('owner:id,name,email')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Documents retrieved successfully.',
            'data' => [
                'owned' => $ownedDocuments,
                'shared' => $sharedDocuments,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content_html' => ['nullable', 'string'],
        ]);

        $document = Document::create([
            'owner_id' => $user->id,
            'title' => $validated['title'] ?? 'Untitled Document',
            'content_html' => $validated['content_html'] ?? '',
        ]);

        return response()->json([
            'message' => 'Document created successfully.',
            'data' => $document,
        ], 201);
    }

    public function show(Request $request, Document $document)
    {
        $user = $request->user();

        $isOwner = $document->owner_id === $user->id;

        $share = DocumentShare::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$isOwner && !$share) {
            return response()->json([
                'message' => 'You do not have access to this document.',
            ], 403);
        }

        return response()->json([
            'message' => 'Document retrieved successfully.',
            'data' => $document->load('owner:id,name,email', 'sharedUsers:id,name,email'),
            'access' => [
                'is_owner' => $isOwner,
                'permission' => $isOwner ? 'owner' : $share->permission,
                'can_edit' => $isOwner || in_array($share?->permission, ['editor', 'admin']),
                'can_share' => $isOwner || $share?->permission === 'admin',
            ],
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content_html' => ['nullable', 'string'],
        ]);

        $isOwner = $document->owner_id === $user->id;

        $canEditAsSharedUser = DocumentShare::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->whereIn('permission', ['editor', 'admin'])
            ->exists();

        if (!$isOwner && !$canEditAsSharedUser) {
            return response()->json([
                'message' => 'You do not have permission to update this document.',
            ], 403);
        }

        $document->update([
            'title' => $validated['title'],
            'content_html' => $validated['content_html'] ?? '',
        ]);

        return response()->json([
            'message' => 'Document updated successfully.',
            'data' => $document->load('owner:id,name,email', 'sharedUsers:id,name,email'),
        ]);
    }

    public function destroy(Request $request, Document $document)
    {
        $user = $request->user();

        if ($document->owner_id !== $user->id) {
            return response()->json([
                'message' => 'Only the document owner can delete this document.',
            ], 403);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully.',
        ]);
    }

    public function import(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:txt,md', 'max:2048'],
        ]);

        $file = $validated['file'];

        $content = file_get_contents($file->getRealPath());

        $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $document = Document::create([
            'owner_id' => $user->id,
            'title' => $title ?: 'Imported Document',
            'content_html' => '<p>' . nl2br(e($content)) . '</p>',
        ]);

        return response()->json([
            'message' => 'File imported successfully.',
            'data' => $document,
        ], 201);
    }

    public function share(Request $request, Document $document)
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'permission' => ['required', 'in:viewer,editor,admin'],
        ]);

        $isOwner = $document->owner_id === $user->id;

        $isAdminSharedUser = DocumentShare::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->where('permission', 'admin')
            ->exists();

        if (!$isOwner && !$isAdminSharedUser) {
            return response()->json([
                'message' => 'Only the owner or an admin can share this document.',
            ], 403);
        }

        if ($document->owner_id === (int) $validated['user_id']) {
            return response()->json([
                'message' => 'The owner already has full access to this document.',
            ], 422);
        }

        if ((int) $validated['user_id'] === $user->id) {
            return response()->json([
                'message' => 'You cannot share the document with yourself.',
            ], 422);
        }

        $share = DocumentShare::updateOrCreate(
            [
                'document_id' => $document->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'permission' => $validated['permission'],
            ]
        );

        return response()->json([
            'message' => 'Document shared successfully.',
            'data' => $share,
        ], 201);
    }
}