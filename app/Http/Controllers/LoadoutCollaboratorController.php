<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoadoutCollaboratorRequest;
use App\Models\Loadout;
use App\Models\LoadoutCollaborator;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoadoutCollaboratorController extends Controller
{
    public function index(Request $request, Loadout $loadout): Response
    {
        $this->authorizeOwner($request, $loadout);

        $loadout->load(['collaborators.user' => fn ($query) => $query->select('id', 'name', 'email')]);

        return Inertia::render('loadouts/collaborators', [
            'loadout' => [
                'id' => $loadout->id,
                'title' => $loadout->title,
                'slug' => $loadout->slug,
            ],
            'collaborators' => $loadout->collaborators
                ->map(fn (LoadoutCollaborator $collaborator): array => [
                    'id' => $collaborator->id,
                    'role' => $collaborator->role,
                    'user' => [
                        'id' => $collaborator->user->id,
                        'name' => $collaborator->user->name,
                        'email' => $collaborator->user->email,
                    ],
                ])
                ->values(),
        ]);
    }

    public function store(StoreLoadoutCollaboratorRequest $request, Loadout $loadout): RedirectResponse
    {
        $this->authorizeOwner($request, $loadout);

        $data = $request->collaboratorData();
        $collaborator = User::query()->where('email', $data['email'])->firstOrFail();

        abort_if($collaborator->id === $loadout->user_id, 422, 'The owner is already responsible for this loadout.');

        LoadoutCollaborator::query()->updateOrCreate(
            [
                'loadout_id' => $loadout->id,
                'user_id' => $collaborator->id,
            ],
            ['role' => $data['role']],
        );

        return back();
    }

    public function destroy(Request $request, Loadout $loadout, LoadoutCollaborator $collaborator): RedirectResponse
    {
        $this->authorizeOwner($request, $loadout);

        abort_unless($collaborator->loadout_id === $loadout->id, 404);

        $collaborator->delete();

        return back();
    }

    private function authorizeOwner(Request $request, Loadout $loadout): void
    {
        $user = $request->user();

        abort_unless($user instanceof User && $loadout->user_id === $user->id, 404);
    }
}
