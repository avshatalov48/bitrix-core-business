<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Push & Pull");
?>

<?
$APPLICATION->IncludeComponent("yourcompanyprefix:pull.test", '');
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>