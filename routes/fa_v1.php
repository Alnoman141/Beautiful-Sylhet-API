<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2024-12-12 12:28
 */

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum','prefix' => 'v1'],function (){
    Route::post('role-list', [\App\Http\Controllers\Api\V1\Role_Permission\RolePermissionController::class, 'roleList']);
    Route::post('get-single-role', [\App\Http\Controllers\Api\V1\Role_Permission\RolePermissionController::class, 'getSingleRole']);
    //TODO @fa 2022-09-29 Role Permission Assign
    Route::post('role-permission-assign', [\App\Http\Controllers\Api\V1\Role_Permission\RolePermissionController::class, 'rolePermissionAssign']);
    //TODO @fa 2024-12-12 self permissions
    Route::post('self-permissions', [\App\Http\Controllers\Api\V1\Role_Permission\RolePermissionController::class, 'selfPermissions']);
});
