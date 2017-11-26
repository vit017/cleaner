<?
global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen("/index.php"));
IncludeModuleLangFile($PathInstall."/install.php");

Class ims_payture extends CModule
{
    var $MODULE_ID = "ims.payture";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "Y";
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function ims_payture()
    {
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("IMS.PAYTURE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("IMS.PAYTURE_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = GetMessage("IMS.PAYTURE_PARTNER_NAME");
        $this->PARTNER_URI = "http://www.imedianet.ru";
    }

    function DoInstall()
    {
        global $APPLICATION;

       
            $this->InstallFiles();
            $this->InstallDB();            
            RegisterModule("ims.payture");         
        
    }

    function DoUninstall()
    {
        global $APPLICATION;

        
            $this->UnInstallFiles();
            $this->UnInstallDB();           
			 UnRegisterModule("ims.payture"); 
    }

    function InstallDB()
    {
            global $APPLICATION;
           
            
            return true;
    }

    function UnInstallDB($arParams = array())
    {
            global $APPLICATION;
           
                     
            return true;
    }


    function InstallFiles()
    {
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ims.payture/install/payment/",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_payment/payture/");
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ims.payture/install/tools/",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/payture/");
            return true;
    }

    function UnInstallFiles()
    {
            DeleteDirFilesEx("/bitrix/php_interface/include/sale_payment/payture/");
            DeleteDirFilesEx("/bitrix/tools/payture/");
            return true;
    }
}