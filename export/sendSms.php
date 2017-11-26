<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
?>


<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"> 

    <script type="text/javascript">
    $(document).ready( function(){
        $(".sendSmsForm").on( "submit", function(e){
            e.preventDefault();
            var formPlace=$(this);
            $.ajax({
                type: "POST", 
                url: "/export/sendSmsAjax.php",  
                data: $(this).serialize(),  
                success: function(html){  
                    formPlace.html(html);  
                }
            });
        }); 
    });
    </script>
    <style>
        h1{
            text-align:center;
            font-family: 'Open Sans', sans-serif;
            font-size:25px;
        }        

        p{
            text-align:center;
            font-family: 'Open Sans', sans-serif;
            font-size:14px;
            margin:0 0 10px;
        }

        form{
            margin:0 auto;
            width:400px;
        }

        textarea{
            width:400px;
            height:200px;
            resize:none;
            border:1px solid gray;
            margin-bottom:10px;
            padding:10px;
            font-family: 'Open Sans', sans-serif;
            font-size:13px;
            display:block;
        }        
        input[type="text"]{
            border:1px solid gray;
            font-family: 'Open Sans', sans-serif;
            font-size:13px;
            width:400px;
            height:30px; 
            padding:0 10px;
            margin-bottom:10px; 
            display:block; 
        }       
        input[type="submit"]{
            border:1px solid gray;
            cursor: pointer;
            font-family: 'Open Sans', sans-serif;
            font-size:13px; 
            margin:0 auto; 
            display:block;
            width:150px; 
        }
    </style>    
</head>
<body>

    <h1>Отправка смс в ручном режиме</h1>

    <form action="" method="post" class="sendSmsForm">
        <input type="text" name="phone" placeholder="номер телефона (формат: 89111234567)">
        <textarea name="text" placeholder="текст сообщения"></textarea>
        <input type="submit" name="submit" value="Отправить смс">
    </form>
</body>
</html>


<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>