<?php
/**
 * Created by PhpStorm.
 * User: hn
 */

include  __DIR__. DIRECTORY_SEPARATOR .'lib'.DIRECTORY_SEPARATOR.'socketio' .DIRECTORY_SEPARATOR .'SocketIO.class.php';

/**
 * session 开启
 */
session_start();
$_SESSION['users'] = array();

/**
 * 获取所有用户
 * @return array
 */
function getAllUsers(){
    if(!isset($_SESSION['users'])) return array();
    return $_SESSION['users'];
}

/**
 * 获取指定用户
 * @param $uid
 * @return string
 */
function getUser($uid){
    if(!isset($_SESSION['users']) || !isset( $_SESSION['users'][$uid])) return '';
    return $_SESSION['users'][$uid];
}

/**
 * 删除指定用户
 * @param $uid
 * @return bool
 */
function delUser($uid){
    if(!isset($_SESSION['users']) || !isset( $_SESSION['users'][$uid])) return true;
    unset($_SESSION['users'][$uid]);
}

/**
 * 添加用户
 * @param $uid
 * @param $name
 */
function setUser($uid,$name){
    if(!isset($_SESSION['users'])) $_SESSION['users'] = array();
    $_SESSION['users'][$uid] = $name;
}

/**
 *
 */
$io = new SocketIO('127.0.0.1',8000);

/**
 *
 */
$io->on('connect',function($ws,$uid){
//    $ws->emit('test', $uid, 'I'm system manager!');
});

/**
 *
 */
$io->on('login',  function($ws,$uid,$msg){
    $name = $msg['name'];
    setUser($uid, $name);
    echo "{$name}: login \n";
    $users = getAllUsers();
    $ws->response('login',$uid,true);
    $ws->broadcast('user', $users);
    $ws->broadcast('msg', array('name'=>$name,'content'=>'大家好我回来了！'));
});

/**
 *
 */
$io->on('msg',  function($ws,$uid,$msg){
    $name = getUser($uid);
    echo "{$name}: {$msg['content']}\n";
    $data =  array('name'=>$name,'content'=>$msg['content']);
    if(isset($msg['to']) && $msg['to']=='all'){
        $ws->broadcast('msg', $data);
    }else{
        $ws->emit('msg', $msg['to'], $data);
        $ws->emit('msg',$uid, $data);
    }
});

/**
 *
 */
$io->on('exit',  function($ws,$uid,$msg){
    $name = getUser($uid);
    detUser($uid);
    echo "{$name}: 退出\n";
    $ws->close($uid);
    $ws->broadcast('exit',array('name'=>$name,'content'=>'待会见，我很快就会回来的!'));
});

/**
 *
 */
$io->on('close',function($ws,$uid,$err){
    $name = getUser($uid);
    echo "{$uid} close socket: {$err}. ".date('Y-m-d H:i:s')."\n";
    delUser($uid);
    $users = getAllUsers();
    $ws->broadcast('user', $users);
    $ws->broadcast('msg', array('name'=>$name,'content'=>'再见!'));
});

/**
 *
 */
$io->run();
