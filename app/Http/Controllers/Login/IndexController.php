<?php

namespace App\Http\Controllers\Login;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    //注册
    public function register(){
        $redirect=$_GET['redirect'] ?? env('SHOP_URL');
        $data=[
            'redirect'=>$redirect
        ];
        return view('reg.reg',$data);
    }
    public function doRegister(Request $request){
        $name=$request->input('u_name');
        if(empty($name)){
            die("账号不能为空");
        }
        $pwd1=$request->input('u_pwd');
        if(empty($pwd1)){
            die("密码不能为空");
        }
        $pwd2=$request->input('u_pwd2');
        if(empty($pwd2)){
            die("确认密码不能为空");
        }
        if($pwd1!==$pwd2){
            die("密码不一致");
        };
        $age=$request->input('u_age');
        if(empty($age)){
            die("年龄不能为空");
        }
        $email=$request->input('u_email');
        if(empty($email)){
            die("邮箱不能为空");
        }
        $res=UserModel::where(['name'=>$name])->first();
        if($res){
            die("账号已存在");
        }
        $r=$request->input('redirect') ?? env('SHOP_URL');
        $pwd=password_hash($pwd1,PASSWORD_BCRYPT);
//	    echo $pwd;die;
//	    $pwd=password_verify($pwd1,'$2y$10$TGftIAn6wDc.mBF1Z0Mh8e8mxskkKbsOh8GCDnohgdhE2J/vujlCC');
//	    var_dump($pwd);die;
        //echo __METHOD__;
        //echo '<pre>';print_r($_POST);echo '</pre>';
        $data=[
            'name'=>$name,
            'pwd'  =>$pwd,
            'age'=>$age,
            'email'=>$email,
            'reg_time'=>time(),
        ];
        $uid=UserModel::insertGetId($data);
        //var_dump($uid);
        if($uid){
            $token=substr(md5(time().mt_rand(1,9999)),10,10);
            setcookie('token',$token,time()+86400,'/','wangby.cn',false,true);
            setcookie('uid',$uid,time()+86400,'/','wangby.cn',false,true);
            setcookie('uname',$name,time()+86400,'/','wangby.cn',false,true);
//            $request->session()->put('u_token',$token);
//            $request->session()->put('uid',$uid);
            $redis_key_token='str:u:token:'.$uid;
            Redis::del($redis_key_token);
            Redis::hset($redis_key_token,'web',$token);
            header('refresh:1;url=https://dzh.wangby.cn');
            echo "注册成功，正在跳转";
            header("refresh:1;$r");
        }else{
            echo "注册失败";
        }
    }
    //登录
    public function login()
    {
        $redirect=$_GET['redirect'] ?? env('SHOP_URL');
        $data=[
            'redirect'=>$redirect
        ];
        return view('login.login',$data);
    }

    public function doLogin(Request $request)
    {
        $u_name = $request->input('u_name');
        $u_pwd = $request->input('u_pwd');
        $url=$request->input('redirect') ?? env('SHOP_URL');
        $where = [
            'name' => $u_name,
        ];
        $res = UserModel::where($where)->first();
        if ($res) {
            if (password_verify($u_pwd, $res->pwd)){
                $token = substr(md5(time()) . mt_rand(1, 9999), 10, 10);
                setcookie('uid', $res->u_id, time() + 86400, '/', 'wangby.cn', false, true);
                setcookie('token', $token, time() + 86400, '/', 'wangby.cn', false, true);
//                $request->session()->put('u_token', $token);
//                $request->session()->put('uid', $res->u_id);
//                echo $token;die;
                $redis_key_token='str:u:token:'.$res->u_id;
                Redis::del($redis_key_token);
                Redis::hset($redis_key_token,'web',$token);
                echo "登录成功";
                header("refresh:1;$url");
            } else {
                echo "账号或密码错误";
                header('refresh:1;url=http://psp.wangby.cn/login');
            }
        }
    }
    public function receive(Request $request){
        $uname=$request->input('uname');
        $pwd=$request->input('pwd');
//        echo $uname;echo "<br>";
//        echo $pwd;die;
        $where = [
            'name' =>  $uname,
        ];
        if(empty($uname)|| empty($pwd)){
            $response=[
                'error'=>400,
                'msg'=>'账号或密码不能为空'
            ];
        }
        $res = UserModel::where($where)->first();
//        echo $res;die;
        if ($res) {
            if (password_verify($pwd, $res->pwd)){
                $token = substr(md5(time()) . mt_rand(1, 9999), 10, 10);
                setcookie('uid', $res->u_id, time() + 86400, '/', 'wangby.com', false, true);
                setcookie('token', $token, time() + 86400, '/', 'wangby.com', false, true);
//                $request->session()->put('u_token', $token);
//                $request->session()->put('uid', $res->u_id);
//                echo $token;die;
                $redis_key_token='str:u:token:'.$res->u_id;
                Redis::del($redis_key_token);
                Redis::hset($redis_key_token,'android',$token);
                $response=[
                    'error'=>0,
                    'msg'=>'登录成功',
                    'token'=>$token
                ];
            } else {
                $response=[
                    'error'=>500,
                    'msg'=>'登录失败'
                ];
            }

        }
        return json_encode($response);

    }
    public function quit(){
        setcookie('uid', null, time()-1, '/', 'wangby.com', false, true);
        setcookie('token', null, time()-1, '/', 'wangby.com', false, true);
        echo "退出成功";
        header('refresh:1;url=http://dzh.wangby.cn');
    }


}
