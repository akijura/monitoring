<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Laravue\Models\Servers;
use GuzzleHttp\Client;
use App\Laravue\Models\Errors;
use Illuminate\Support\Facades\DB;
class checkStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This update servers table with data which has been taken from other web servers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $servers = Servers::select('id','ip', 'port','endpoint','request_type','name','server_type','request_type')->get();
        foreach($servers as $server)
        {
            if($server->request_type != null && $server->ip != null && $server->port != null)
            {
                $ip = $server->ip;
                $server_id = $server->id;
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
                            $idSer = $server_update->id;
                            $resultSms = DB::select("select sm.phone_number from sms sm
                            where sm.server_id = ?",[$idSer]);
                            if($resultSms != null)
                            {
                                foreach($resultSms as $res)
                                {
                                    $dataSms = [];
                                    $urlSms = 'http://172.25.102.142:2916/Service1.svc/CreateDispatch';
                                    $phone_number = $res->phone_number;
                                    $dataSms = [
                                        "iPhone" => $phone_number,
                                        "iMessage"=> $server->name." has been run successfully with  ".$text.' status code '.$httpcode,
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
                            $idSer = $server_update->id;
                            $resultSms = DB::select("select sm.phone_number from sms sm
                            where sm.server_id = ?",[$idSer]);
                            if($resultSms != null)
                            {
                                foreach($resultSms as $res)
                                {
                                    $dataSms = [];
                                    $urlSms = 'http://172.25.102.142:2916/Service1.svc/CreateDispatch';
                                    $phone_number = $res->phone_number;
                                    $dataSms = [
                                        "iPhone" => $phone_number,
                                        "iMessage"=> $server->name." has been stopped with error ".$text.' status code'.$httpcode,
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
                        }
                        else {
                            $temp = $errorToSuccess->error_count;
                            $temp2 = $temp + 1;
                            $errorToSuccess->error_count = $temp2;
                            $errorToSuccess->updated_at = date('Y-m-d H:i:s');
                            $errorToSuccess->update();
                        }
              
                    $server_update->working = 0;
                    $server_update->status = $httpcode;
                    $server_update->statusText = $text;
                    if($errorToSuccess->error_count <= 5)
                    {
                        $client = new Client();
                        $res = $client->request("GET", "https://api.telegram.org/bot1836323502:AAEBlIKMip96LikCJVfUsTeDiHOorjbbuzQ/sendMessage?chat_id=@servermanitor&text=!!! Error !!! ".$server_update->name." server has error with status ".
                        $server_update->status." ".$server_update->statusText, [
                            "proxy" => "http://username:password@10.12.16.202:8081",
                        ]);  
                    }

                    }
                    
                    $server_update->updated_at = date('Y-m-d H:i:s');
                    $server_update->update();
                    curl_close($ch);
            }
            
        }
          \Log::info("Cron is working fine!");
          $this->info('Demo:Cron Cummand Run successfully!');
          return 0;
//           C:\xampp\php\php.exe
// C:\xampp\htdocs\laravue\artisan schedule:run
    }
}
