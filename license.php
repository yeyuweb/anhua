<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$response =<<<RES
    try {\$("#phpcms_license").html("已授权（<a href=\"http://cncore.com/\" target=\"_blank\" style=\"color:red\">查看</a>）");$("#phpcms_sn").html("已激活（<a href='http://cncore.com/index.php?m=member&c=service&a=activation_key&tab=1&domain=innofund.org.cn' target='_blank' style='color:red'>点击查看</a>）");}catch(e){}    
RES;

print $response;