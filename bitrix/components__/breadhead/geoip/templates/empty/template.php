<script>
    phone = "<?=$_SESSION["PHONE"]?>";
    clear_phone = "<?=str_replace(array('(', ')', ' ', '-'),'',$_SESSION['PHONE'])?>";
    address = "<?=$_SESSION["ADDRESS"]?>";
    city = "<?=$_SESSION["CITY"]?>";
    hour_price = "<?=$_SESSION['HOUR_PRICE']?>";
    console.log(document.location.pathname);

    $(document).ready(function(){
        if(document.location.pathname=='/order/basket/' || document.location.pathname=='/order/')
        document.location = '/order/basket/';
    })
</script>
