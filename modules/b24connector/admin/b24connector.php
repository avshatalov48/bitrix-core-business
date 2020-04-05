<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/b24connector/admin/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\B24Connector\Connection;

Loc::loadMessages(__FILE__);

if (isset($_REQUEST["action"]) && $moduleAccess > "R" && check_bitrix_sessid())
{
	if($_REQUEST["action"] == "delete_connection")
	{
		\Bitrix\B24Connector\Connection::delete();
	}

	LocalRedirect("b24connector_b24connector.php?lang=".LANGUAGE_ID);
}

$APPLICATION->SetTitle(Loc::getMessage('B24C_B24C_TITLE'));

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
		<p class="connector-title"><?=Loc::getMessage('B24C_B24C_COMM')?></p>

		<div class="connector-video-container">
			<div class="connector-video-block">
				<iframe class="connector-video" src="https://www.youtube.com/embed/bhKF6cq2E2M?rel=0" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
		<p class="connector-description"><?=Loc::getMessage('B24C_B24C_D1')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_B24C_D2')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_B24C_D3')?></p>
		<p class="connector-description"><?=Loc::getMessage('B24C_B24C_D4')?></p>
		<div class="connector-step">
			<div class="connector-step-item connector-step-item-1"><?=Loc::getMessage('B24C_B24C_S1')?></div>
			<div class="connector-step-item connector-step-item-arrow"></div>
			<div class="connector-step-item connector-step-item-2"><?=Loc::getMessage('B24C_B24C_S2')?></div>
			<div class="connector-step-item connector-step-item-arrow"></div>
			<div class="connector-step-item connector-step-item-3"><?=Loc::getMessage('B24C_B24C_S3')?></div>
		</div>
		<div class="connector-newbtx">
			<div class="connector-nebtx-inner">
				<?if(Connection::isExist()):?>
					<p class="connector-newbtx-text"><?=Loc::getMessage('B24C_B24C_CONN')?></p>
					<div class="connector-newbtx-inputlink"><?=Connection::getDomain()?></div>
					<?if($moduleAccess > "R"):?>
						<a href="javascript:void(0)" onclick="if(confirm('<?=Loc::getMessage('B24C_B24C_DEL_CONFIRM')?>')) window.location.href='?lang=<?=LANGUAGE_ID?>&action=delete_connection&<?=bitrix_sessid_get()?>';" class="connector-newbtx-link"><?=Loc::getMessage('B24C_B24C_DEL')?></a>
					<?endif;?>
				<?else:?>
					<a href="https://www.bitrix24.<? if (LANGUAGE_ID == "ru") echo "ru"; elseif (LANGUAGE_ID == "de") echo "de"; else echo "com"; ?>/" class="connector-btn-green"><?=Loc::getMessage('B24C_B24C_CREATE')?></a>
					<p class="connector-newbtx-text"><?=Loc::getMessage('B24C_B24C_OR')?></p>
					<?=Connection::getButtonHtml()?>
				<?endif?>
			</div>
			<div class="connector-newbtx__item connector-newbtx__item-1"><span><?=Loc::getMessage('B24C_B24C_ITEM1')?></span></div>
			<div class="connector-newbtx__item connector-newbtx__item-2"><span><?=Loc::getMessage('B24C_B24C_ITEM2')?></span></div>
			<div class="connector-newbtx__item connector-newbtx__item-3"><span><?=Loc::getMessage('B24C_B24C_ITEM3')?></span></div>
			<div class="connector-newbtx__item connector-newbtx__item-4"><span><?=Loc::getMessage('B24C_B24C_ITEM4')?></span></div>
			<div class="connector-newbtx__item connector-newbtx__item-5"><span><?=Loc::getMessage('B24C_B24C_ITEM5')?></span></div>
			<div class="connector-newbtx__item connector-newbtx__item-6"><span><?=Loc::getMessage('B24C_B24C_ITEM6')?></span></div>
			<div class="connector-newbtx__item connector-newbtx__item-7"><span><?=Loc::getMessage('B24C_B24C_ITEM7')?></span></div>
		</div>
	</div>
</div>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");