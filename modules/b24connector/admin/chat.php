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
		'TITLE' => Loc::getMessage('B24C_CHAT_BUTT_GET_B24'),
		'URL_METHOD' => '\Bitrix\B24Connector\Connection::getWidgetsConfigUrl'
	)
);

$APPLICATION->SetTitle(Loc::getMessage("B24C_CHAT_TITLE"));

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
		<p class="connector-title"><?=Loc::getMessage('B24C_CHAT_TITLE')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_CHAT_P1')?></p>

		<?php if (LANGUAGE_ID === 'ru'): ?>
			<img src="/bitrix/images/b24connector/img-1.png" alt="" class="connector-img">
		<?php endif; ?>

		<p class="connector-description"><?=Loc::getMessage('B24C_CHAT_P2')?></p>
		<ul class="connector-description-ul-green">
			<li><?=Loc::getMessage('B24C_CHAT_LI1')?></li>
			<li><?=Loc::getMessage('B24C_CHAT_LI2')?></li>
			<li><?=Loc::getMessage('B24C_CHAT_LI3')?></li>
		</ul>
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