<?php
/**
 * Created by PhpStorm.
 * User: hn
 */
class WebSocketAPI {

    /**
     * 升级为 WebSocket 协议
     * @param $socket
     * @param $buffer
     * @return bool
     */
    function handshake($socket,$buffer){
        $buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
        $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
        $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
        $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
        $new_message .= "Upgrade: websocket\r\n";
        $new_message .= "Sec-WebSocket-Version: 13\r\n";
        $new_message .= "Connection: Upgrade\r\n";
        $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
        socket_write($socket, $new_message, strlen($new_message));
        return true;
    }

    /**
     *  解码
     * @param $str
     * @return bool|string
     */
    function uncode($str){
        $mask = array();
        $data = '';
        $msg = unpack('H*',$str);
        $head = substr($msg[1],0,2);
        if (hexdec($head{1}) === 8) {
            $data = false;
        }else if (hexdec($head{1}) === 1){
            $mask[] = hexdec(substr($msg[1],4,2));
            $mask[] = hexdec(substr($msg[1],6,2));
            $mask[] = hexdec(substr($msg[1],8,2));
            $mask[] = hexdec(substr($msg[1],10,2));

            $s = 12;
            $e = strlen($msg[1])-2;
            $n = 0;
            for ($i=$s; $i<= $e; $i+= 2) {
                $data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));
                $n++;
            }
        }
        return $data;
    }

    /**
     * 编码
     * @param $msg
     * @return string
     */
    function code($msg){
        $msg = preg_replace(array('/\r$/','/\n$/','/\r\n$/',), '', $msg);
        $frame = array();
        $frame[0] = '81';
        $len = strlen($msg);
        $frame[1] = $len<16?'0'.dechex($len):dechex($len);
        $frame[2] = $this->ord_hex($msg);
        $data = implode('',$frame);
        return pack("H*", $data);
    }

    /**
     * @param $data
     * @return string
     */
    function ord_hex($data)  {
        $msg = '';
        $l = strlen($data);
        for ($i= 0; $i<$l; $i++) {
            $msg .= dechex(ord($data{$i}));
        }
        return $msg;
    }

}