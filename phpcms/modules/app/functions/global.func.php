<?php

    //获得随机验证串
    function get_token() {
        $token = '';
        while (strlen($token) < 32) {
            $token .= mt_rand(0, mt_getrandmax());
        }
        $token = md5(uniqid($token, TRUE));
        return $token;
    }

?>