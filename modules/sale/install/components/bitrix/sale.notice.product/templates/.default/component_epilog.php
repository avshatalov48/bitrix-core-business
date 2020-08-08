<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
__IncludeLang($_SERVER["DOCUMENT_ROOT"].$templateFolder."/lang/".LANGUAGE_ID."/template.php");

$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
$arNotify = array();
if ($notifyOption <> '')
	$arNotify = unserialize($notifyOption);

if (is_array($arNotify[SITE_ID]) &&
		$arNotify[SITE_ID]['use'] == 'Y' &&
		$USER->IsAuthorized() &&
		is_array($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()]) &&
		!empty($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()]))
{
	echo '<script type="text/javascript">';
	foreach ($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()] as $val)
	{
		echo 'if (BX("url_notify_'.$val.'"))';
		echo 'BX("url_notify_'.$val.'").innerHTML = \''.GetMessageJS("MAIN_NOTIFY_MESSAGE").'\';';
	}
	echo '</script>';
}
echo bitrix_sessid_post();
?>