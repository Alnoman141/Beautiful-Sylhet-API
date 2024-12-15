<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2024-12-12 12:32
 */

namespace App\Http\Controllers\Api\V1\Role_Permission;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    //TODO @fa 2024-12-12 role list
    public function roleList(Request $request)
    {
        try {
            $roles = Role::query()->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            });
            return response()->json([
                'status_code' => Response::HTTP_OK,
                'data' => $roles
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => Response::HTTP_FORBIDDEN,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], Response::HTTP_FORBIDDEN);
        }
    }
    public function rolePermissionAssign(Request $request)
    {
        /*if (!Gate::allows('permission_assign')) {
            return response()->json([
                'status_code' => Response::HTTP_FORBIDDEN,
                'message' => 'Permission denied'
            ], Response::HTTP_FORBIDDEN);
        }*/
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $role = Role::findOrFail($request->id);
            $permissions = collect($request->permissions)->map(fn($permission) => (int)$permission);
            $role->syncPermissions($permissions);
            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'Role with permission sync successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => Response::HTTP_FORBIDDEN,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], Response::HTTP_FORBIDDEN);
        }
    }

    //TODO @fa 2024-12-12 get single role with permission
    public function getSingleRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:roles,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $role = Role::with('permissions:id,name')->findOrFail($request->id);
            $permissions = Permission::all()->map(function ($permission){
                return collect($permission)->except(['created_at','updated_at']);
            });
            return response()->json([
                'status_code' => 200,
                'message' => 'Role With Permission List',
                'data' => [
                    'role' => $role,
                    'permissions' => $permissions,
                ],
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    public function selfPermissions()
    {
        try {
            $user = Auth::user();
            $permissions = $user->getPermissionsViaRoles();
            $permissions = collect($permissions)->map(function ($permission) {
                return [
                    'action' => Str::after($permission->name, '-'),
                    'subject' => Str::before($permission->name, '-'),
                ];
            });
            return response()->json([
                'status_code' => 200,
                'message' => $user->name.' Permissions',
                'data' => $permissions,
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'status_code' => 403,
                'message' => 'Permissions Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
}
