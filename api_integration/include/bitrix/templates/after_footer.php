<?php
/*
 * Часть кода футера для подгрузки ядра АПИ подсистемы
 */
?>

<script type="text/javascript" src="/api_integration/assets_min/libs.js"></script>
<script type="text/javascript" src="/api_integration/assets_min/core.js"></script>
<script type="text/javascript" src="/api_integration/assets_min/config.js"></script>
<script type="text/javascript" src="/api_integration/assets_min/template.js"></script>

<script type="text/javascript">
    window.city = "<?
$APPLICATION->IncludeFile(SITE_DIR . "include/city.php", Array(), Array(
    "MODE" => "text",
    "SHOW_BORDER" => false
));
?>";
</script>

