<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\PaySystem;
use \Bitrix\Main\Config;

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."sale_pay_system.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$application = \Bitrix\Main\Application::getInstance();
$context = $application->getContext();
$request = $context->getRequest();

global $APPLICATION;

$id = (int)$request->get('pay_system_id');
$personTypeId = $request->getQuery("personTypeId");
$personTypeList = \Bitrix\Sale\BusinessValue::getPersonTypes();
$errorMsg = '';

$paySystemData = PaySystem\Manager::getById($id);
if (!$paySystemData || $paySystemData['ACTION_FILE'] !== 'yandexinvoice')
{
	$adminSidePanelHelper->localRedirect($listUrl);
	LocalRedirect($listUrl);
}

\CUtil::InitJSCore();

if ($request->getPost("Save") && check_bitrix_sessid())
{
	$sitesData = $request->getPost("settings");
	foreach ($sitesData as $personTypeId => $fields)
	{
		if ($fields["SETTINGS_CLEAR_ALL"])
		{
			$shopId = \Bitrix\Sale\BusinessValue::get('YANDEX_INVOICE_SHOP_ID', 'PAYSYSTEM_'.$id, $personTypeId);
			if ($shopId)
				\Bitrix\Sale\Internals\YandexSettingsTable::delete($shopId);
		}

		if ($fields["SETTINGS_CLEAR"])
		{
			$shopId = \Bitrix\Sale\BusinessValue::get('YANDEX_INVOICE_SHOP_ID', 'PAYSYSTEM_'.$id, $personTypeId);
			if ($shopId)
				\Bitrix\Sale\Internals\YandexSettingsTable::update($shopId, array('PUB_KEY' => ''));
		}

		$file = $request->getFile("YANDEX_PUB_KEY_".$personTypeId);
		if (file_exists($file['tmp_name']))
		{
			$publicKey = file_get_contents($file['tmp_name']);
			if (openssl_pkey_get_public($publicKey))
			{
				$shopId = \Bitrix\Sale\BusinessValue::get('YANDEX_INVOICE_SHOP_ID', 'PAYSYSTEM_'.$id, $personTypeId);
				if (strlen($shopId) > 0)
					\Bitrix\Sale\Internals\YandexSettingsTable::update($shopId, array('PUB_KEY' => $publicKey));
			}
			else
			{
				$errorMsg .= GetMessage('SALE_YANDEX_INVOICE_SETTINGS_ERROR_PUBLIC_KEY_LOAD');
			}
		}
	}

	if ($errorMsg === '')
	{
		$redirectUrl = $APPLICATION->GetCurPage()."?pay_system_id=".$id."&lang=".LANGUAGE_ID;
		$adminSidePanelHelper->localRedirect($redirectUrl);
		LocalRedirect($redirectUrl);
	}
}

if ($request->get('generate') === 'Y')
{
	$personTypeId = $request->get('person_type_id');

	$shopId = \Bitrix\Sale\BusinessValue::get('YANDEX_INVOICE_SHOP_ID', 'PAYSYSTEM_'.$id, $personTypeId);
	if (strlen($shopId) > 0)
	{
		$dbRes = \Bitrix\Sale\Internals\YandexSettingsTable::getById($shopId);
		$yandexSettings = $dbRes->fetch();
		if (!$yandexSettings || !$yandexSettings['PKEY'])
		{
			$command = 'openssl ecparam -name prime256v1 -genkey | openssl pkcs8 -topk8 -nocrypt';
			$descriptorSpec = array(1 => array("pipe", "w"));
			$process = proc_open($command, $descriptorSpec, $pipes);
			$privateKey = stream_get_contents($pipes[1]);
			$return_value = proc_close($process);

			$dbRes = \Bitrix\Sale\Internals\YandexSettingsTable::getById($shopId);
			if ($dbRes->fetch())
				\Bitrix\Sale\Internals\YandexSettingsTable::update($shopId, array('PKEY' => $privateKey));
			else
				\Bitrix\Sale\Internals\YandexSettingsTable::add(array('SHOP_ID' => $shopId, 'PKEY' => $privateKey));
		}
		else
		{
			$errorMsg = Loc::getMessage('SALE_YANDEX_INVOICE_SETTINGS_ALREADY_CONFIGURED');
		}

		if ($errorMsg === '')
		{
			$redirectUrl = $APPLICATION->GetCurPage()."?pay_system_id=".$id."&lang=".LANGUAGE_ID;
			$adminSidePanelHelper->localRedirect($redirectUrl);
			LocalRedirect($redirectUrl);
		}
	}
}
else if ($request->get('download') === 'Y')
{
	$personTypeId = $request->get('person_type_id');
	$shopId = \Bitrix\Sale\BusinessValue::get('YANDEX_INVOICE_SHOP_ID', 'PAYSYSTEM_'.$id, $personTypeId);

	$APPLICATION->RestartBuffer();

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=public_key.pem');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$dbRes = \Bitrix\Sale\Internals\YandexSettingsTable::getById($shopId);
	$yandexSettings = $dbRes->fetch();
	if ($yandexSettings)
	{
		$pkeyRes = openssl_get_privatekey($yandexSettings['PKEY']);
		$pkeyDetail = openssl_pkey_get_details($pkeyRes);
		echo $pkeyDetail['key'];
		die();
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(Loc::getMessage('SALE_YANDEX_INVOICE_SETTINGS_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($errorMsg !== '')
	CAdminMessage::ShowMessage(array("DETAILS"=>$errorMsg, "TYPE"=>"ERROR", "HTML"=>true));

$personTypeTabs = array();
$personTypeTabs[] = array(
	"PERSON_TYPE" => 0,
	"DIV" => 0,
	"TAB" => Loc::getMessage('SALE_YANDEX_INVOICE_SETTINGS_BY_DEFAULT'),
	"TITLE" => Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_PT").": ".Loc::getMessage('SALE_YANDEX_INVOICE_SETTINGS_BY_DEFAULT')
);

foreach ($personTypeList as $personTypeId)
{
	$personTypeTabs[] = array(
		"PERSON_TYPE" => $personTypeId["ID"],
		"DIV" => $personTypeId["ID"],
		"TAB" => $personTypeId["NAME"]." (".$personTypeId['LID'].")",
		"TITLE" => Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_PT").": ".$personTypeId["NAME"]
	);
}

$tabRControl = new \CAdminTabControl("tabRControl", $personTypeTabs);
$showButton = false;
?>

<?
$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("SPSN_2FLIST"),
		"LINK" => $adminSidePanelHelper->editUrlToPublicPage($selfFolderUrl."sale_pay_system_edit.php?ID=".$id."&lang=".$context->getLanguage()),
		"ICON" => "btn_list"
	)
);

$contextMenu = new CAdminContextMenu($aMenu);
$contextMenu->Show();
?>
<?$tabRControl->Begin();?>
<?
$actionUrl = $APPLICATION->GetCurPage()."?pay_system_id=".$id."&lang=".LANGUAGE_ID;
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
?>
<form method="POST" enctype="multipart/form-data" action="<?=$actionUrl?>" id="<?=$personTypeId?>_form-upload">
	<?=bitrix_sessid_post();?>
	<?foreach($personTypeTabs as $tab) :?>
	<?
		$personTypeId = $tab["PERSON_TYPE"];
		$shopId = \Bitrix\Sale\BusinessValue::get('YANDEX_INVOICE_SHOP_ID', 'PAYSYSTEM_'.$id, $personTypeId);

		$tabRControl->BeginNextTab();
	?>
		<input type="hidden" name="settings[<?=$personTypeId;?>][PERSON_TYPE_ID]" value="<?=$personTypeId;?>">
		<?if ($shopId):?>
			<?
			$showButton = true;

			$dbRes = \Bitrix\Sale\Internals\YandexSettingsTable::getById($shopId);
			$yandexSettings = $dbRes->fetch();

			?>
			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage('SALE_YANDEX_INVOICE_SETTINGS_SUBTITLE');?></td>
			</tr>
			<?if ($yandexSettings):?>
				<tr>
					<td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_PUB_KEY")?>:</td>
					<td width="50%" class="adm-detail-content-cell-r">
						<a href="<?=$APPLICATION->GetCurPage();?>?pay_system_id=<?=$id?>&person_type_id=<?=$personTypeId;?>&download=Y"><?=Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_DOWNLOAD");?></a>
					</td>
				</tr>
				<?if ($yandexSettings['PUB_KEY']):?>
					<tr>
						<td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_PUB_KEY_YA")?>:</td>
						<td width="50%" class="adm-detail-content-cell-r">
							<?=Loc::getMessage('SALE_YANDEX_INVOICE_SETTINGS_PUBLIC_KEY_OK')?><br>
							<input type="checkbox" name="settings[<?=$personTypeId;?>][SETTINGS_CLEAR]"> <?=Loc::getMessage('SALE_YANDEX_INVOICE_SETTINGS_DEL');?>
						</td>
					</tr>
				<?else:?>
					<tr>
						<td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_PUB_KEY_YA")?>:</td>
						<td width="50%" class="adm-detail-content-cell-r">
							<input type="file" name="YANDEX_PUB_KEY_<?=$personTypeId;?>">
						</td>
					</tr>
				<?endif;?>
				<tr class="heading">
					<td colspan="2"><?=Loc::getMessage('SALE_YANDEX_INVOICE_SETTINGS_RESET_TITLE');?></td>
				</tr>
				<tr>
					<td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_RESET")?>:</td>
					<td width="50%" class="adm-detail-content-cell-r">
						<input type="checkbox" name="settings[<?=$personTypeId;?>][SETTINGS_CLEAR_ALL]">
					</td>
				</tr>
			<?else:?>
				<tr>
					<td width="50%" class="adm-detail-content-cell-l"><?=Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_PKEY")?>:</td>

					<td width="50%" class="adm-detail-content-cell-r">
						<a href="<?=$APPLICATION->GetCurPage();?>?pay_system_id=<?=$id?>&person_type_id=<?=$personTypeId;?>&generate=Y"><?=Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_PKEY_GENERATE");?></a>
					</td>
				</tr>
			<?endif;?>
		<?else:?>
			<tr>
				<td colspan="2">
					<?
						CAdminMessage::ShowMessage(array("DETAILS" => Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_ERROR_SHOP_ID"), "TYPE"=>"ERROR", "HTML"=>true));
					?>
				</td>
			</tr>
		<?endif;?>
		<?$tabRControl->EndTab();?>

	<? endforeach; ?>

	<?if ($showButton):?>
		<?$tabRControl->Buttons();?>
		<input type="submit" name="Save" value="<?=Loc::getMessage("SALE_YANDEX_INVOICE_SETTINGS_SAVE")?>">
		<input type="hidden" name="Save" value="Y">
	<?endif;?>
</form>

<?$tabRControl->End();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");