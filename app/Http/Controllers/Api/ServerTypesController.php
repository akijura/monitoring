<?php
/**
 * File ServerTypesController.php
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Laravue\Models\ServerTypes;
use \App\Laravue\Faker;
use \App\Laravue\JsonResponse;
use App\Laravue\Models\Permission;
use App\Laravue\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Resources\ServerResource;
use Validator;
use App\Laravue\Models\Servers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class ServerTypesController
 *
 * @package App\Http\Controllers\Api
 */
class ServerTypesController extends Controller
{
    const ITEM_PER_PAGE = 15;

    /**
     * Display a listing of the user resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $userQuery = ServerTypes::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $userQuery->where('name', 'LIKE', '%' . $keyword . '%');
            $userQuery->orWhere('description', 'LIKE', '%' . $keyword . '%');
            $userQuery->orWhere('permission', 'LIKE', '%' . $keyword . '%');
        }

        return ServerResource::collection($userQuery->paginate($limit));
    }
    public function listWithCount()
    {
        $result = DB::select("select st.name,ifnull(count(el.id),0) as total from server_types st 
        left join error_logs el on st.name = el.server_type
        group by el.server_type,st.name
        order by st.id ASC
        ");
        return response()->json(new JsonResponse(['countData' => $result]));
    }

    public function show($id)
    {
        $group = ServerTypes::find($id);
        return response()->json(new JsonResponse (['group' => $group]));
    }
    public function update(Request $request, ServerTypes $type)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
              
                [
                    'editname' => 'regex:/^[a-zA-Z0-9 ]*$/',
                ]
            )
        );
        if ($type === null) {
            return response()->json(['error' => 'Group not found'], 404);
        }
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $currentUser = Auth::user();
            if (!$currentUser->isAdmin()
                && $currentUser->id !== $user->id
                && !$currentUser->hasPermission(\App\Laravue\Acl::PERMISSION_VIEW_MENU_SERVERS_GROUPS)
            ) {
                return response()->json(['error' => 'Permission denied'], 403);
            }
            
            $type->name = $request->get('editname');
            $type->description = $request->get('editdescription');
            $type->save();
            return new ServerResource($type);
        }

    }
    public function destroy(ServerTypes $type)
    {
        $group = ServerTypes::find($type);
        if(count($group) != 0)
        {
            $findServers = Servers::where('server_type', '=', $group[0]['name'] )->get();
            $findPermission = DB::table('permissions')->where('name', '=', $group[0]['permission'] )->first();
            if(count($findServers) === 0)
            {
               try {
                $type->delete();
                } catch (\Exception $id) {
                    return response()->json(['error' => $ex->getMessage()], 403);
                }
                try {
                    DB::table('role_has_permissions')
                    ->where('permission_id',$findPermission->id)
                    ->delete();
                } catch (\Exception $id) {
                        return response()->json(['error' => $ex->getMessage()], 403);
                }
                try {
                    DB::table('permissions')
                    ->where('id',$findPermission->id)
                    ->delete();
                } catch (\Exception $id) {
                        return response()->json(['error' => $ex->getMessage()], 403);
                }
            }
            else{
                return response()->json(['error' => 'Error! Please delete servers which includes '.$group[0]['name'].' group'], 403);
            }
        }
         return response()->json(null, 204);
    }
    public function Create()
    {
        $validator = Validator::make(
            request()->all(),
            array_merge(
              
                [
                    'name' => 'regex:/^[a-zA-Z0-9 ]*$/',
                ]
            )
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
        $server = new ServerTypes;
        $temp = request()->name;
        $temp2 = preg_replace("/\s+/", "", $temp);
        $server->name = strtoupper($temp2);
        $server->description = request()->desc;
        $temp = 'manage '.$server->name;
        $server->permission = $temp;
        $server->save();
       
        $new_permission = new Permission();
        $new_permission->name =  $temp;
        $new_permission->save();

        return $server;
        }
    }
    public function gettypes()
    {
        $server_types = ServerTypes::select('id','name')->get();
        return response()->json(new JsonResponse ( ['server_types' => $server_types]));
    }
}
