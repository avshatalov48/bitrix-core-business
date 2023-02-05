<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/admin/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\B24Connector\Connection;

Loc::loadMessages(__FILE__);

$listParams = array(
	'EMPTY_BUTTON' => array(
		'TITLE' => Loc::getMessage('B24C_BUTT_EMPTY'),
		'URL_METHOD' => '\Bitrix\B24Connector\Connection::getWidgetsConfigUrl'
	)
);

$APPLICATION->SetTitle(Loc::getMessage('B24C_BUTT_TITLE'));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<div class="connector">

	<div class="connector-content">
		<p class="connector-title"><?=Loc::getMessage('B24C_BUTT_TITLE')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_BL_OL_U')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_BL_ALL_C')?></p>

		<ul class="connector-description-ul-green">
			<li><?=Loc::getMessage('B24C_BL_AC_C')?></li>
			<li><?=Loc::getMessage('B24C_BL_AC_C2')?></li>
			<li><?=Loc::getMessage('B24C_BL_AC_C3')?></li>
			<li><?=Loc::getMessage('B24C_BL_AC_C4')?></li>
			<li><?=Loc::getMessage('B24C_BL_AC_C5')?></li>
		</ul>

		<?php if (LANGUAGE_ID === 'ru'): ?>
			<img src="/bitrix/images/b24connector/img-3.png" alt="" class="connector-img">
		<?php endif; ?>

	<?=
		$APPLICATION->IncludeComponent(
			"bitrix:b24connector.button.list",
			".default",
			$listParams,
			false
		);
	?>
	</div>
</div>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>