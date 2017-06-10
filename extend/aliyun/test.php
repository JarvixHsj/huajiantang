<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai'); 

    $c = new TopClient;
	$c->appkey = '23779232';
	$c->secretKey = 'a3cca4fef607615383ae91d663026933';
	$req = new AlibabaAliqinFcSmsNumSendRequest;
	

?>