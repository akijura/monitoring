<?php
/**
 * File SmsController.php
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
use App\Http\Resources\SmsResource;
use Validator;
use App\Laravue\Models\Servers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Laravue\Models\Sms;
use Illuminate\Pagination\Paginator;

/**
 * Class SmsController
 *
 * @package App\Http\Controllers\Api
 */
class SmsController extends Controller
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
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
       
        if (!empty($keyword)) {
            $keyword = '%'.$keyword.'%';
            $result = DB::select("select sm.phone_number,us.name as user_name,sr.name as server_name,us.id from sms sm
            left join users us on sm.user_id=us.id
            left join servers sr on sm.server_id=sr.id
            where us.name like ? or sm.phone_number like ? or sr.name like ? 
            order by sm.created_at DESC
            ",[$keyword,$keyword,$keyword]);
        }
        else
        {
            $result = DB::select("select sm.phone_number,us.name as user_name,sr.name as server_name,us.id from sms sm
            left join users us on sm.user_id=us.id
            left join servers sr on sm.server_id=sr.id
            order by sm.created_at DESC
            ");
        }
        $data = [];
        $tempPhone='';
        $tempUser='';
        $index = 0;
        foreach ($result as $res) {

            if($res->phone_number === $tempPhone && $res->user_name === $tempUser)
            {
                $data[$index - 1]['server_name'] = $data[$index - 1]['server_name'].' , '.$res->server_name;
                
            }
            else 
            {
                $row = [
                    'user_id' => $res->id,
                    'phone_number' => $res->phone_number,
                    'user_name' => $res->user_name,
                    'server_name' => $res->server_name,
                ];
                $tempPhone = $res->phone_number;
                $tempUser = $res->user_name;
                $data[] = $row;
                $index++;
            }
        }

         return response()->json(new JsonResponse(['result' => $data]));
    }
    public function editList($user,$phone)
    {
        $result = DB::select("select sm.phone_number, sm.user_id,sm.server_id,us.name as user_name from sms sm
        left join users us on sm.user_id=us.id
        where sm.user_id = ? and sm.phone_number = ? ",[$user,$phone]);
        $data = [];
        $tempPhone='';
        $tempUser='';
        $index = 0;
        $servers_id= [];
        foreach ($result as $res) {

            if($res->phone_number === $tempPhone && $res->user_id === $tempUser)
            {
                $servers_id[]=(int)$res->server_id;
                $data[$index - 1]['server_id'] = $servers_id;
                
            }
            else 
            {
                $servers_id[]=(int)$res->server_id;
                $row = [
                    'user_name' => $res->user_name,
                    'user_id' => $res->user_id,
                    'phone_number' => $res->phone_number,
                    'server_id' => $servers_id,
                ];
                $tempPhone = $res->phone_number;
                $tempUser = $res->user_id;
                $data[] = $row;
                $index++;
            }
        }
        return response()->json(new JsonResponse(['result' => $data]));
    }
    public function Servers()
    {
        $servers = Servers::all();
        $data = [];
        foreach ($servers as $server) {
            $row = [
            'id' => $server['id'],
            'name' => $server['name'],
        ];
        $data[] = $row;
    }
    return response()->json(new JsonResponse(['items' => $data]));
    }
    public function usersList()
    {
        $result = DB::select("select id,name from users");
        return response()->json(new JsonResponse(['userList' => $result]));
    }
    public function updatesms(Request $request)
    {
        $currentUser = Auth::user();
        $params = $request->all();
        $validator = Validator::make(
            $request->all(),
            array_merge(
              
                [
                    'phone_number' => 'regex:/^[a-zA-Z0-9 ]*$/',
                ]
            )
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } 
        else
        {
            if (!$currentUser->isAdmin() && !$currentUser->hasPermission(\App\Laravue\Acl::PERMISSION_USER_MANAGE)) 
            {
                return response()->json(['error' => 'Permission denied'], 403);
            }
    
            $validator = Validator::make($request->all(), $this->getValidationRules());
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 403);
            } else {
                $user_id = $params['editUserId'];
                $editPhoneNumber = $params['editPhoneNumber'];
                DB::table('sms')
                ->
                where([
                    ['user_id', $user_id],
                    ['phone_number' , $editPhoneNumber],
                    ]
                )
                ->delete();
                $servers_id = $params['editServerId'];
                foreach($servers_id as $id )
                {
                    Sms::create([
                        'phone_number' =>  $editPhoneNumber,
                        'user_id' => $user_id,
                        'server_id' => $id,
                    ]);
                }
                return response()->json(new JsonResponse(['params' => $params]));
             
            }
        }

    
      
    }
    public function delete($phone_number,$user_id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isAdmin() && !$currentUser->hasPermission(\App\Laravue\Acl::PERMISSION_USER_MANAGE)) 
        {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        DB::table('sms')
        ->where([
            ['user_id', $user_id],
            ['phone_number' , $phone_number],
            ]
        )->delete();
        return "ok";
    }
        /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules()
    {
        return [
            'editServerId' => 'required',
            'editUserId' => 'required',
            'editPhoneNumber' => 'required',
        ];
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
  
            $params = $request->all();
            $servers_id = $params['server_id'];
            $validator = Validator::make(
                $request->all(),
                array_merge(
                  
                    [
                        'phone_number' => 'regex:/^[a-zA-Z0-9 ]*$/',
                    ]
                )
            );
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 403);
            } 
            else {
                foreach($servers_id as $id )
                {
                    $user = Sms::create([
                        'phone_number' => $params['phone_number'],
                        'user_id' => $params['user_id'],
                        'server_id' => $id,
                    ]);
                }
                return $params;
            }

        
    }
}
