<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->select(['id', 'name', 'email', 'role', 'is_active', 'created_at'])
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data             = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user             = User::create($data);

        return response()->json($user->only('id', 'name', 'email', 'role', 'is_active'), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->only('id', 'name', 'email', 'role', 'is_active', 'created_at'));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user->only('id', 'name', 'email', 'role', 'is_active'));
    }

    public function destroy(User $user): JsonResponse
    {
        abort_if($user->id === request()->user()->id, 422, 'Cannot delete your own account.');
        $user->delete();
        return response()->json(null, 204);
    }
}
