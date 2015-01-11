<?php
/**
 *  index.php CMS 入口
 *
 * @copyright		
 * @license			
 * @lastmodify			
 */
 //PHPCMS根目录

define('PHPCMS_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

include PHPCMS_PATH.'/phpcms/base.php';

pc_base::creat_app();

?>