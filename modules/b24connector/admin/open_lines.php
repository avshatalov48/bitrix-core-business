<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/admin/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\B24Connector\Connection;

Loc::loadMessages(__FILE__);

$listParams = array(
	'FILTER' => array(
		'TYPE' => 'openline'
	),
	'EMPTY_BUTTON' => array(
		'TITLE' => Loc::getMessage('B24C_OL_GET_OL'),
		'URL_METHOD' => '\Bitrix\B24Connector\Connection::getOpenLinesConfigUrl'
	)
);

$APPLICATION->SetTitle(Loc::getMessage('B24C_OL_TITLE'));
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
		<p class="connector-title"><?=Loc::getMessage('B24C_OL_TITLE')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_OL_DESCR1')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_OL_DESCR21')?></p>
		<p class="connector-description">
			<div style="text-align: center;">
				<img src="/bitrix/images/b24connector/fb.png" alt="Facebook" title="Facebook" style="margin: 0 25px 0 0;">&nbsp;&nbsp;
				<img src="/bitrix/images/b24connector/vk.png" alt="Vkontakte" title="Vkontakte" style="margin: 0 25px 0 0;">&nbsp;&nbsp;
				<img src="/bitrix/images/b24connector/telegram.png" alt="Telegram" title="Telegram" style="margin: 0 25px 0 0;">&nbsp;&nbsp;
				<img src="/bitrix/images/b24connector/skype.png" alt="Skype" title="Skype" style="margin: 0 25px 0 0;">&nbsp;&nbsp;
				<span><?=Loc::getMessage('B24C_OL_OTHERS')?></span>
			</div>
		</p>
		<p class="connector-description"><?=Loc::getMessage('B24C_OL_DESCR23')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_OL_DESCR3')?></p>
		<img src="/bitrix/images/b24connector/img-5.png" alt="" class="connector-img">
		<p class="connector-description"><?=Loc::getMessage('B24C_OL_DESCR4')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_OL_DESCR5')?></p>
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
