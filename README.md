# php-ws
这说明以前发布在 http://www.oschina.net/p/php-ws， 今天才意识到这里太空白了，就弄了过来
实现基于Websocket 协议的 PHP类库 和 javascript类库 ， 实现事件+回调函数的架构开发
，以一个简单聊天室实例介绍其使用方法: 不同浏览器，不同电脑，不同地域， 实时通讯。实现的详细 请研究源码。

/***服务端****/

/**
 * 实例化
 */
$io = new SocketIO('127.0.0.1',8000);

/**
 * 监听连接
 */
$io->on('connect',function($ws,$uid){
$msg = "任意数据类型，结构需要和前端协议，便于通信"；
    $ws->broadcast(evet, $msg);
    //$ws->emit(evet, $uid, $msg);
});


/**
 * 任意事件，与前端协议好，// evet 由开发者定义
 */
$io->on('event',function($ws,$uid,$msg){
    $msg = "任意数据类型，结构需要和前端协议，便于通信"；
    $ws->broadcast(evet, $msg); 
});


/*
* 关闭
*/
$io->on('close',function($ws,$uid,$err){
    // evet 由开发者定义
    $msg = "任意数据类型，结构需要和前端协议，便于通信"；
    $ws->broadcast('close', $msg);
});


/**
 * 启动
 */
$io->run();
 

/*** 客户端 ***/

var io = new SocketIO('127.0.0.1',8000);
io.on('connect',function(){
     console.log('open');
     // 发出请求, event 由开发者定义
     io.emit(event, user, function(ok){
          if(ok){             
          }else{                
          }
     });
     // 收到消息, event 由开发者定义
     io.on(event, function(msg){             
          console.log(msg);
     }); 
     //关闭事件
 io.on('close', function(){
});
});




