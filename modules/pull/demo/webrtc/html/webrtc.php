<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("WebRTC demo");
?>

<?
$APPLICATION->IncludeComponent("yourcompanyprefix:pull.webrtc", '');
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>