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
        return view('reg.reg');
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
            setcookie('token',$token,time()+86400,'/','dongzhiheng.com',false,true);
            setcookie('uid',$uid,time()+86400,'/','dongzhiheng.com',false,true);
            setcookie('uname',$name,time()+86400,'/','dongzhiheng.com',false,true);
            $request->session()->put('u_token',$token);
            $request->session()->put('uid',$uid);
            $redis_key_token='str:u:token:web:'.$uid;
            Redis::set($redis_key_token,$token);
            Redis::expire($redis_key_token,60*60*24*7);
            header('refresh:1;url=http://www.dongzhiheng.com');
            echo "注册成功，正在跳转";
        }else{
            echo "注册失败";
        }
    }
    //登录
    public function login()
    {
        return view('login.login');
    }

    public function doLogin(Request $request)
    {
        $u_name = $request->input('u_name');
        $u_pwd = $request->input('u_pwd');
        $where = [
            'name' => $u_name,
        ];
        $res = UserModel::where($where)->first();
        if ($res) {
            if (password_verify($u_pwd, $res->pwd)){
                $token = substr(md5(time()) . mt_rand(1, 9999), 10, 10);
                setcookie('uid', $res->u_id, time() + 86400, '/', 'dongzhiheng.com', false, true);
                setcookie('token', $token, time() + 86400, '/', 'dongzhiheng.com', false, true);
//                $request->session()->put('u_token', $token);
//                $request->session()->put('uid', $res->u_id);
//                echo $token;die;
                $redis_key_token='str:u:token:web:'.$res->u_id;
                Redis::set($redis_key_token,$token);
                Redis::expire($redis_key_token,60*60*24*7);
                echo "登录成功";
                header('refresh:1;url=http://www.dongzhiheng.com');
            } else {
                echo "账号或密码错误";
                header('refresh:1;url=/login');
            }
        }
    }
    public function receive(){
        echo "<pre>";print_r($_POST);echo "</pre>";
    }


}
