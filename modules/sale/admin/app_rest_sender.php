<?

use \Bitrix\Main\Localization\Loc,
	\Bitrix\Sale\Exchange\Integration\Rest;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

global $APPLICATION;

\Bitrix\Main\Loader::includeModule('sale');

\Bitrix\Main\UI\Extension::load('sale.b24integration');

$sender = new Rest\Sender();

$APPLICATION->SetTitle(Loc::getMessage('SALE_ORDER_REQUEST_SEND'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

\Bitrix\Main\Page\Asset::getInstance()->addJs("//api.bitrix24.com/api/v1/", true);

$r = $sender->checkFields();
if($r->isSuccess())
{
	$item = [];
	foreach($sender->getField('orderIds') as $id)
    {
		$item[$id] = [
                'SUBJECT'=>Loc::getMessage("SALE_ORDER_REQUEST_SUBJECT").$id,
                'OWNER_TYPE_ID'=>$sender->getField('entityTypeId'),
                'OWNER_ID'=>$sender->getField('entityId'),
        ];
    }
    ?>

    <script>

		BX.ready(function () {
            stepper = new BX.Sale.Stepper({ownerTypeId: <?=$sender->getField('entityTypeId')?>,ownerId:<?=$sender->getField('entityId')?>});
            stepper.progress(
				<?=json_encode($item)?>,
                <?=count($item)?>
            );
        });
    </script>
    <div id="progress"><?
		$message = new \CAdminMessage('');
		$message->ShowMessage(array(
			"TYPE" => "PROGRESS",
			"DETAILS" => '#PROGRESS_BAR#'.
				'<div class="adm-loc-ri-statusbar">'.Loc::getMessage('SALE_ORDER_REQUEST_STATUS').': <span class="bx-ui-loc-ri-loader"></span>&nbsp;<span class="bx-ui-loc-ri-status-text">'.Loc::getMessage('SALE_ORDER_REQUEST_STATUS_PROCESS').'</span></div>',
			"HTML" => true,
			"PROGRESS_TOTAL" => 100,
			"PROGRESS_VALUE" => 0,
			"PROGRESS_TEMPLATE" => '<span class="bx-ui-loc-ri-percents">#PROGRESS_VALUE#</span>%'
		));?></div>
    <div id="progress_error"></div>
    <div id="progress"></div>
    <div id="finish"></div>
	<?
}
else
{
	echo (new \CAdminMessage(
		array(
			"DETAILS" => implode('<br>', $r->getErrorMessages()),
			"TYPE" => "ERROR",
			"HTML" => true
		)
	))->Show();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");