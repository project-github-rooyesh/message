<?php
namespace Rooyesh\Message;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Kavenegar\KavenegarApi;
use Rooyesh\Message\Models\Message;
use Illuminate\Support\Facades\Request;

class MessageInterface
{
    protected $user_id = null;
    protected $message;
    protected $token = null;
    protected $type = null;
    protected $list_send = null;

    public function __construct($user_id,$message,$token,$list_send)
    {
        $this->user_id = $user_id;
        $this->message = $message;
        $this->token = $token;
        $this->list_send = $list_send;
//        $this->createMessage();
    }


    public static function create($user_id,$message,$token,$list_send)
    {
        return new MessageInterface($user_id,$message,$token,$list_send);
    }



//////create
    public function createMessage(){
        $config = Arr::get(config(),'message',false);
        if ($config){
            foreach ($this->list_send as $list){
              $this->message =  str_replace("%name", $list['name'], $this->message);
                if ($config['0098'] && $config['0098']['active']){
                    $endpoint = "http://www.0098sms.com/sendsmslink.aspx";
                    Http::get($endpoint,[
                        'FROM' => $config['0098']['sender'],
                        'TO' => $list['mobile'],
                        'TEXT' => trim($this->message),
                        'USERNAME' => $config['0098']['user_name'],
                        'PASSWORD' => $config['0098']['password'],
                        'DOMAIN' => '0098',
                    ]);
                    $this->type = '0098';
                    $this->store();
                }
                elseif ($config['kavenegar'] && $config['kavenegar']['active']){
                    $api = new KavenegarApi($config['kavenegar']['api_key']);
                    $results = $api->Send($config['kavenegar']['sender'],  $list['mobile'], $this->message);
                    $this->type = 'kavenegar';
                    $this->store();
                }
            }

        }

//        return response()->json($message);
    }

    protected function store(){
        $message = Message::create([
            'customer_id' => $this->list_send['customer_id'],
            'name'        => $this->list_send['name'],
            'user_id'     => $this->user_id,
            'mobile'      => $this->mobile['name'],
            'massage'     => $this->message,
            'token'       => $this->token,
            'type'       => $this->type,
        ]);
    }




/////table
    protected function addLimit($table,$list){
        if (!isset($list['page'])){
            $list['page'] = 0;
        }
        if (!isset($list['limit'])){
            $list['limit'] = 10;
        }
        if ($list['limit']>50){
            $list['limit'] = 50;
        }
        $list['count'] = $table->count();
        $list['rows'] = $table->offset( $list['page']*$list['limit'])
            ->limit($list['limit'])
            ->get();
        $list['page'] = (int)$list['page'];
        $list['limit'] = (int)$list['limit'];
        return $list;
    }

    public function index($data){
        $message = Message::query();
        if (isset($data['filters'])){
            $this->filter($message,$data['filters']);
        }
        if (isset($data['search'])){
            $this->search($message,$data['search']);
        }
        return response()->json($this->addLimit($message,$data));

    }

    protected function filter(&$message,$filters){
        foreach ($filters as $filter){
            if ($filter['field'] == 'name'){
                $message->whereRaw('lower(`name`) like ?', ['%' . strtolower($filter['value']) . '%']);
            }
            if($filter['field'] == 'message'){
                $message->whereRaw('lower(`message`) like ?', ['%' . strtolower($filter['value']) . '%']);
            }
            if($filter['field'] == 'type'){
                $message->where('type','like','%'.$filter['value'].'%');
            }
            if($filter['field'] == 'mobile'){
                $message->where('mobile','like','%'.$filter['value'].'%');
            }
            if($filter['field'] == 'customer_id'){
                $message->where('customer_id',$filter['value']);
            }
            if($filter['field'] == 'user_id'){
                $message->where('user_id',$filter['value']);
            }
        }
    }

    protected function search(&$message,$search){
        $message->whereRaw('lower(`name`) like ?', ['%' . strtolower($search) . '%'])
            ->OrwhereRaw('lower(`message`) like ?', ['%' . strtolower($search) . '%'])
            ->Orwhere('mobile','like','%'.$search.'%');
    }

}
