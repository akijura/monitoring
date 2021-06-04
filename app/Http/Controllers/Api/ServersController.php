<?php
/**
 * File ServersController.php
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Laravue\Models\Servers;
use \App\Laravue\Faker;
use \App\Laravue\JsonResponse;
use App\Laravue\Models\ServerTypes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Laravue\Models\User;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;
use GuzzleHttp\Client;
use App\Laravue\Models\Errors;
use Illuminate\Support\Facades\DB;

/**
 * Class ServersController
 *
 * @package App\Http\Controllers\Api
 */
class ServersController extends Controller
{
    public function Create()
    {

        $server = new Servers;
        $server->name = request()->name;
        $server->type = request()->type;
        $server->ip = request()->ip;
        $server->port = request()->port;
        $server->login = request()->login;
        $server->password = request()->password;
        $server->server_type = request()->shina;
        $server->endpoint = request()->endpoint;
        $server->request_type = request()->request_type;
        $server->save();
        return $server;
    }
    public function Servers(User $user)
    {
        $currentUser = Auth::user();
        $current = $currentUser->roles[0]['name'];

        $searchParams = request()->type;
        if ( $current === 'admin'){
            if (!empty($searchParams)) {
                $servers = Servers::where('server_type', 'LIKE', $searchParams)->get();
            }
            else
            {
                $servers = Servers::all();
            }
            $server_types = ServerTypes::select('id','name')->get();
        }
        else{
            $temp = [];
            $temp2 = [];
            $permissionsAll = $currentUser->getPermissionsViaRoles()->toArray();
            foreach($permissionsAll as $permission)
            {
                $temp [] = $permission['name'];
            }
            $types = ServerTypes::select('name')->whereIn('permission', $temp)->get();
            foreach($types as $type)
            {
                $temp2 [] = $type['name'];
            }
            if (!empty($searchParams)) {
                $servers = Servers::where('server_type', 'LIKE', $searchParams)->whereIn('server_type',$temp2)->get();
            }
            else
            {
                $servers = Servers::whereIn('server_type',$temp2)->get();
            }
            $server_types = ServerTypes::select('id','name')->whereIn('name',$temp2)->get();
        }
        
        $rowsNumber = 0;
       
        $data = [];
        foreach ($servers as $server) {
            $updated = date('Y-m-d H:i:s', strtotime($server['updated_at']));
            $row = [
            'id' => $server['id'],
            'name' => $server['name'],
            'type' => $server['type'],
            'endpoint' => $server['endpoint'],
            'status' => $server['status'],
            'statusText' => $server['statusText'],
            'working' => $server['working'],
            'request_type' => $server['request_type'],
            'server_type' => $server['server_type'],
            'updated' => $updated
        ];
        $rowsNumber++;
        $data[] = $row;
    }
    
    return response()->json(new JsonResponse(['items' => $data, 'total' => $rowsNumber,'server_types' => $server_types,'searchParams' => $searchParams,'user' =>$current]));
    }
    public function Edit($id)
    {
        $server = Servers::find($id);
        return response()->json(new JsonResponse (['editItems' => $server]));
    }
    public function Update()
    {
        $server = Servers::find(request()->editid);
        $server->name = request()->editname;
        $server->type = request()->edittype;
        $server->ip = request()->editip;
        $server->port = request()->editport;
        $server->login = request()->editlogin;
        $server->password = request()->editpassword;
        $server->endpoint = request()->editendpoint;
        $server->request_type = request()->editrequest_type;
        $server->server_type = request()->editserver_type;
        $server->update();
        return 'ok';
    }
    public function Delete($id) {
        $server = Servers::find($id)->delete();
        return 'ok';
      }
    public function Request($id)
    {
        // $server = Servers::find($id);
        // $ip = $server->ip;
        // $port = $server->port;
        // $errorToSuccess = Errors::where([['ip','=',$ip],['port','=',$port]])->orderBy('id', 'DESC')->first();
        // if(empty($errorToSuccess))
        // {
        //     return "null";
        // }
        // else {
        //     return "not null";
        // }
       
        $server = Servers::find($id);
        if($server->request_type != null && $server->ip != null && $server->port != null)
          {
              $ip = $server->ip;
              $port = $server->port;
              $endpoint = $server->endpoint;
              $request_type = $server->request_type;
              
              $data = [];

              $url = 'http://'.$ip.':'.$port.'/'.$endpoint;
              $data = [
                  'msgid' => '9cbe57f-61d2-4854-a635-03f17505fd235',
                  'msgcorrid' => 'fd8a7f9a-b4ad-40cc-b5df-be1b5087e2a5',
                  'msgdate' => '2019-09-29T09:54:05.125+05:00',
                  'msgmode' => 'SYNCHRONOUS',
                  'msgtype' => 'REQUEST',
                  'msgresptype' => 'JSON',
                  'msgsource' => 'ESB',
                  'msgmethod' => 'ESB.GET_STATUS',
                  'msgmethodparams' => '{
                    "msgapplication":"Test"
                  }'     
                ];
                $ch = curl_init();
                  $headers = array();
                  $headers[] = "Content-Type: application/json";
                  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                  curl_setopt($ch, CURLOPT_PROXY, null);
                  curl_setopt($ch, CURLOPT_URL, $url);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_type);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                  $result = curl_exec($ch);
                  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                  $result = json_decode($result, TRUE);
                  $text = '';
              
                  switch ($httpcode) {
                      case 100: $text = 'Continue'; break;
                      case 101: $text = 'Switching Protocols'; break;
                      case 200: $text = 'OK'; break;
                      case 201: $text = 'Created'; break;
                      case 202: $text = 'Accepted'; break;
                      case 203: $text = 'Non-Authoritative Information'; break;
                      case 204: $text = 'No Content'; break;
                      case 205: $text = 'Reset Content'; break;
                      case 206: $text = 'Partial Content'; break;
                      case 300: $text = 'Multiple Choices'; break;
                      case 301: $text = 'Moved Permanently'; break;
                      case 302: $text = 'Moved Temporarily'; break;
                      case 303: $text = 'See Other'; break;
                      case 304: $text = 'Not Modified'; break;
                      case 305: $text = 'Use Proxy'; break;
                      case 400: $text = 'Bad Request'; break;
                      case 401: $text = 'Unauthorized'; break;
                      case 402: $text = 'Payment Required'; break;
                      case 403: $text = 'Forbidden'; break;
                      case 404: $text = 'Not Found'; break;
                      case 405: $text = 'Method Not Allowed'; break;
                      case 406: $text = 'Not Acceptable'; break;
                      case 407: $text = 'Proxy Authentication Required'; break;
                      case 408: $text = 'Request Time-out'; break;
                      case 409: $text = 'Conflict'; break;
                      case 410: $text = 'Gone'; break;
                      case 411: $text = 'Length Required'; break;
                      case 412: $text = 'Precondition Failed'; break;
                      case 413: $text = 'Request Entity Too Large'; break;
                      case 414: $text = 'Request-URI Too Large'; break;
                      case 415: $text = 'Unsupported Media Type'; break;
                      case 500: $text = 'Internal Server Error'; break;
                      case 501: $text = 'Not Implemented'; break;
                      case 502: $text = 'Bad Gateway'; break;
                      case 503: $text = 'Service Unavailable'; break;
                      case 504: $text = 'Gateway Time-out'; break;
                      case 505: $text = 'HTTP Version not supported'; break;
                      default:
                          $text = 'Unknown http status code "' . htmlentities($httpcode) . '"';
                          $httpcode = 506;
                      break;
                  }
                  $server_update = Servers::find($server->id);
                  $errorToSuccess = Errors::where([['ip','=',$ip],['port','=',$port]])->orderBy('id', 'DESC')->first();
                  if($httpcode === 200 )
                  {
                    if(!empty($errorToSuccess))
                    {
                        $errorToSuccess->success = 200;
                        $errorToSuccess->updated_at = date('Y-m-d H:i:s');
                        $errorToSuccess->update();

                    }
                      if($server_update->status != 200)
                      {
                        $client = new Client();
                        $res = $client->request("GET", "https://api.telegram.org/bot1836323502:AAEBlIKMip96LikCJVfUsTeDiHOorjbbuzQ/sendMessage?chat_id=@servermanitor&text=!!! Success !!! ".$server_update->name." server has run with status ".
                        $httpcode." ".$text, [
                        "proxy" => "http://username:password@10.12.16.202:8081",
                        ]);
                       }
                  $server_update->working = 1;
                  $server_update->status = $httpcode;
                  $server_update->statusText = $text;
                    
                  }
                  else {       
                    if(empty($errorToSuccess) || $errorToSuccess->success == 200)
                    {
                        $new_error = new Errors;
                        $new_error->error_name = $text;
                        $new_error->error_status = $httpcode;
                        $new_error->name = $server->name;
                        $new_error->ip = $server->ip;
                        $new_error->port = $server->port;
                        $new_error->endpoint = $server->endpoint;
                        $new_error->server_type = $server->server_type;
                        $new_error->request_type = $server->request_type;
                        $new_error->created_at = date('Y-m-d H:i:s');
                        $new_error->save();
                    }
                    else {
                        $temp = $errorToSuccess->error_count;
                        $temp2 = $temp + 1;
                        $errorToSuccess->error_count = $temp2;
                        $errorToSuccess->updated_at = date('Y-m-d H:i:s');
                        $errorToSuccess->update();
                    }
                    $resultSms = DB::select("select sm.phone_number from sms sm
                    where sm.server_id = ?",[$id]);
                    if($resultSms != null)
                    {
                        foreach($resultSms as $res)
                        {
                            $dataSms = [];
                            $urlSms = 'http://172.25.102.142:2916/Service1.svc/CreateDispatch';
                            $phone_number = $res->phone_number;
                            $dataSms = [
                                "iPhone" => $phone_number,
                                "iMessage"=> "Test2",
                                "iFilial" => "09012",
                                "iSmsType" => '0',
                                "iTaskCode" => '6005'
                              ];
                              $chSms = curl_init();
                                $headersSms = array();
                                $headersSms[] = "Content-Type: application/json";
                                curl_setopt($chSms, CURLOPT_HTTPHEADER, $headersSms);
                                curl_setopt($chSms, CURLOPT_PROXY, null);
                                curl_setopt($chSms, CURLOPT_URL, $urlSms);
                                curl_setopt($chSms, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($chSms, CURLOPT_CUSTOMREQUEST, 'POST');
                                curl_setopt($chSms, CURLOPT_POSTFIELDS, json_encode($dataSms));
                                $resultSms = curl_exec($chSms);
                        }
                    }
                  $server_update->working = 0;
                  $server_update->status = $httpcode;
                  $server_update->statusText = $text;
                  $client = new Client();
                  $res = $client->request("GET", "https://api.telegram.org/bot1836323502:AAEBlIKMip96LikCJVfUsTeDiHOorjbbuzQ/sendMessage?chat_id=@servermanitor&text=!!! Error !!! ".$server_update->name." server has error with status ".
                  $server_update->status." ".$server_update->statusText, [
                      "proxy" => "http://username:password@10.12.16.202:8081",
                  ]);
                  }
                  
                  $server_update->updated_at = date('Y-m-d H:i:s');
                  $server_update->update();
                  curl_close($ch);
          }
          return $errorToSuccess;
    }
    public function getErrors($type)
    {
        $temp=[];
        $flag=0;
        $maxMonth=12;
        $current_year = date('Y');
        $result = DB::select("select count(*) as total, month(created_at) as mon,server_type from error_logs where server_type = ? and year(created_at) = ? group by month(created_at),server_type order by month(created_at) ASC",[$type,$current_year]);
        foreach($result as $res)
        {
            $maxMonth = $res->mon;
        }
        for($i=1;$i<=$maxMonth;$i++)
        {
            $flag = 0;
            foreach($result as $res)
            {
                if($i == $res->mon)
                {
                    $temp[] = $res->total;
                    $flag = 1;
                }
                
            }
            if($flag == 0)
            {
                $temp[] = 0;
            }

        }
        return response()->json(new JsonResponse(['expectedData' => $temp]));
    }
    //
}
