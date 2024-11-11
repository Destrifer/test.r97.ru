<?php
/**
 * v. 1
 * 2020-06-16 
 */  
$config2 = [
'db_host' => 'localhost',
'db_user' => 'test_r97_ru',
'db_pass' => 'TavtvkwryTTII3fv',
'db_name' => 'test_r97_ru',
'dir_back' => '_new-codebase/back',
'dir_front' => '_new-codebase/front',
'dir_cache' => '_new-codebase/cache',
'dir_content' => '_new-codebase/content',
'dir_uploads' => '_new-codebase/uploads',
'digocean_key' => 'OJDQNR4N6ZV3EW6HT4OS',
'digocean_secret' => 'ijOkX8K4CGdO/xl16bmlKPaDZdp/0AJ3uI9u1z8H4eM',
'digocean_name' => 'harperservice',
'digocean_region' => 'fra1',
'digocean_url' => 'https://harperservice.fra1.cdn.digitaloceanspaces.com/', 
'mail_host' => 'smtp.mail.ru', 
'mail_username' => 'robot2@r97.ru', 
'mail_password' => 'G3ZbuLtpCYWcahLRJaar', 
'mail_from' => 'R97.RU',
'mail_reply_to' => 'kan@r97.ru',
'email_admin' => ''
];

if(isset($config)){
    $config = array_merge($config, $config2);
}else{
    $config = $config2;
}

unset($config2);