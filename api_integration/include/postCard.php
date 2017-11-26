<?php
$orderId = $_POST['orderId'];
$order_sum = $_POST['order_sum'];
$payment_parameters = http_build_query(array(             
                         "orderid"=>$orderId,
                         "sum"=>$order_sum));
						 
     $options = array("http"=>array(
                        "method"=>"POST",
                        "header"=>"Content-type: application/x-www-form-urlencoded",
                       "content"=>$payment_parameters
                     ));
					 
					 
     $context = stream_context_create($options);
   
    echo  file_get_contents("http://159.253.21.109/order/inline/", false, $context);
	
	?>