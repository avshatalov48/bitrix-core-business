<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/admin/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\B24Connector\Connection;

Loc::loadMessages(__FILE__);
$APPLICATION->SetTitle(Loc::getMessage('B24C_REC_TITLE'));

$listParams = array(
	'FILTER' => array(
		'TYPE' => 'callback'
	),
	'EMPTY_BUTTON' => array(
		'TITLE' => Loc::getMessage('B24C_REC_GET_RECALL'),
		'URL_METHOD' => '\Bitrix\B24Connector\Connection::getWebformConfigUrl'
	)
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(!empty($errorMsgs))
{
	$admMessage = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => implode("<br>\n", $errorMsgs),
		"HTML" => true
	));

	echo $admMessage->Show();
}
?>

<div class="connector">
	<div class="connector-content">
		<p class="connector-title"><?=Loc::getMessage('B24C_REC_TITLE')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_REC_DESC1')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_REC_DESC2')?></p>
		<img src="/bitrix/images/b24connector/img-4.png" alt="" class="connector-img">
		<p class="clonnector-description"><?=Loc::getMessage('B24C_REC_DESC3')?></p>
	</div>
	<?=
	$APPLICATION->IncludeComponent(
		"bitrix:b24connector.button.list",
		".default",
		$listParams,
		false
	);
	?>
</div>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");