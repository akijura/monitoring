<?php
/**
 * File RoleController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */
namespace App\Http\Controllers\Api;

use App\Http\Resources\PermissionResource;
use App\Laravue\Models\Permission;
use Illuminate\Http\Request;
use App\Laravue\Models\Role;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\DB;
use Validator;
/**
 * Class RoleController
 *
 * @package App\Http\Controllers\Api
 */
class RoleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return RoleResource::collection(Role::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles|max:255',
        ]);
        if ($validated == false) {
            return response()->json(['errors' => $validated->errors()], 403);
        } else {
            $params = $request->all();
            $role = Role::create([
                'name' => $params['name'],
            ]);
            return new RoleResource($role);
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  Role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }
    public function Create()
    {
        $role = new Role;
        $role->name = request()->name;
        $role->guard_name = request()->guard_name;
        $role->save();
        return $role;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Role $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        if ($role === null || $role->isAdmin()) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $permissionIds = $request->get('permissions', []);
        $permissions = Permission::allowed()->whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);
        $role->save();
        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Get permissions from role
     *
     * @param  Role $role
     * @return \Illuminate\Http\Response
     */
    public function permissions(Role $role)
    {
        return PermissionResource::collection($role->permissions);
    }
    public function Delete($id) {
        $role = Role::find($id)->delete();
        DB::table('role_has_permissions')
        ->where('role_id',$id)
        ->delete();
        return 'ok';
      }
      private function getValidationRules($isNew = true)
      {
          return [
              'name' => $isNew ? 'required|name|unique:roles' : 'required|name',
          ];
      }

}
