<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tokens = $user->tokens()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->format('Y-m-d H:i:s'),
                    'created_at' => $token->created_at->format('Y-m-d H:i:s'),
                    'expires_at' => $token->expires_at?->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('admin/api-tokens/Index', [
            'tokens' => $tokens,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $abilities = $validated['abilities'] ?? ['*'];
        $expiresAt = isset($validated['expires_at'])
            ? now()->parse($validated['expires_at'])
            : null;

        $token = $request->user()->createToken(
            $validated['name'],
            $abilities,
            $expiresAt
        );

        return redirect()->route('admin.api-tokens.index')
            ->with('success', 'API token created successfully.')
            ->with('newToken', $token->plainTextToken);
    }

    public function destroy(Request $request, $tokenId)
    {
        $token = $request->user()->tokens()->findOrFail($tokenId);
        $token->delete();

        return redirect()->route('admin.api-tokens.index')
            ->with('success', 'API token revoked successfully.');
    }

    public function destroyAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return redirect()->route('admin.api-tokens.index')
            ->with('success', 'All API tokens revoked successfully.');
    }
}
