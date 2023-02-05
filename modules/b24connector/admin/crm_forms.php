<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/admin/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\B24Connector\Connection;

Loc::loadMessages(__FILE__);

$listParams = array(
	'FILTER' => array(
		'TYPE' => 'crmform'
	),
	'EMPTY_BUTTON' => array(
		'TITLE' => Loc::getMessage('B24C_CRMF_GET_FORMS'),
		'URL_METHOD' => '\Bitrix\B24Connector\Connection::getWebformConfigUrl'
	)
);

$APPLICATION->SetTitle(Loc::getMessage('B24C_CRMF_TITLE'));
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
		<p class="connector-title"><?=Loc::getMessage('B24C_CRMF_TITLE')?></p>

		<p class="connector-description"><?=Loc::getMessage('B24C_CRMF_DESCR1')?></p>

		<?php if (LANGUAGE_ID === 'ru'): ?>
			<div class="connector-video-container">
				<div class="connector-video-block">
					<iframe class="connector-video" src="https://www.youtube.com/embed/3qyQhyNx-xs?rel=0" frameborder="0" allowfullscreen></iframe>
				</div>
			</div>
		<?php endif; ?>

		<p class="connector-title-sm"><?=Loc::getMessage('B24C_CRMF_TYPES')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_CRMF_DESCR2')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_CRMF_DESCR3')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_CRMF_DESCR4')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_CRMF_DESCR5')?></p>
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