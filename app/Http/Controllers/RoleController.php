<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RoleController extends Controller
{
    // Retrieve all roles
    public function index()
    {
        try {
            $roles = Role::all();
            return response()->json($roles, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch roles.'], 500);
        }
    }

    // Retrieve a single role by ID
    public function show($id)
    {
        try {
            $role = Role::findOrFail($id);
            return response()->json($role, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Role not found.'], 404);
        }
    }

    // Create a new role
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $role = Role::create($request->all());
            Session::flash('success', 'Role created successfully.');
            return response()->json($role, 201);
        } catch (\Exception $e) {
            Session::flash('error', 'Failed to create role.');
            return response()->json(['error' => 'Failed to create role.'], 500);
        }
    }

    // Update an existing role
    public function update(Request $request, $id)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $role = Role::findOrFail($id);
            $role->update($request->all());
            Session::flash('success', 'Role updated successfully.');
            return response()->json($role, 200);
        } catch (\Exception $e) {
            Session::flash('error', 'Failed to update role.');
            return response()->json(['error' => 'Failed to update role.'], 500);
        }
    }

    // Delete a role
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();
            Session::flash('success', 'Role deleted successfully.');
            return response()->json(['message' => 'Role deleted successfully.'], 200);
        } catch (\Exception $e) {
            Session::flash('error', 'Failed to delete role.');
            return response()->json(['error' => 'Failed to delete role.'], 500);
        }
    }
}
