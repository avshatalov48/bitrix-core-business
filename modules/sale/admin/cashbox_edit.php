<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Page;
use Bitrix\Main\Config;
use Bitrix\Sale\Cashbox;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."sale_cashbox_list.php?lang=" . $lang;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load('ui.buttons.icons');
\Bitrix\Main\UI\Extension::load('ui.forms');

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();
$server = $context->getServer();
$lang = $context->getLanguage();
$documentRoot = Application::getDocumentRoot();

$isCloud = Loader::includeModule("bitrix24");
$zone = '';
if (!$isCloud && Loader::includeModule('intranet'))
{
	$zone = \CIntranetUtils::getPortalZone();
}

\Bitrix\Sale\Cashbox\Cashbox::init();

$id = (int)$request->get('ID');

$cashboxObject = null;
$cashbox = array();
$errorMessage = '';

if ($server->getRequestMethod() == "POST"
	&& ($request->get('save') !== null || $request->get('apply') !== null)
	&& $saleModulePermissions == "W"
	&& check_bitrix_sessid()
)
{
	$adminSidePanelHelper->decodeUriComponent($request);

	$cashbox = array(
		'NAME' => $request->get('NAME'),
		'HANDLER' => $request->getPost('HANDLER'),
		'OFD' => $request->getPost('OFD'),
		'EMAIL' => $request->getPost('EMAIL'),
		'NUMBER_KKM' => $request->getPost('NUMBER_KKM') ?: '',
		'KKM_ID' => $request->get('KKM_ID') ?: '',
		'ACTIVE' => ($request->get('ACTIVE') == 'Y') ? 'Y' : 'N',
		'USE_OFFLINE' => ($request->get('USE_OFFLINE') == 'Y') ? 'Y' : 'N',
		'SORT' => $request->getPost('SORT') ?: 100,
		'OFD_SETTINGS' => $request->getPost('OFD_SETTINGS') ?: array(),
	);

	/** @var Cashbox\Cashbox $handler */
	$handler = $cashbox['HANDLER'];
	if (empty($handler))
	{
		$errorMessage .= GetMessage('ERROR_NO_HANDLER')."<br>\n";
	}
	else
	{
		$handlerList = Cashbox\Cashbox::getHandlerList();
		if (!isset($handlerList[$cashbox['HANDLER']]))
		{
			$errorMessage .= GetMessage('ERROR_NO_HANDLER_EXIST')."<br>\n";
		}
	}

	if ($errorMessage)
	{
		$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
	}

	if (class_exists($handler))
	{
		$cashbox['SETTINGS'] = $handler::extractSettingsFromRequest($request);
	}

	$cashboxObject = Cashbox\Cashbox::create($cashbox);
	$result = $cashboxObject->validate();
	if (!$result->isSuccess())
	{
		foreach ($result->getErrors() as $error)
		{
			$errorMessage .= $error->getMessage()."<br>\n";
		}
	}

	if ($errorMessage === '')
	{
		if ($id > 0)
		{
			$result = Cashbox\Manager::update($id, $cashbox);
			if ($result->isSuccess())
			{
				$service = Cashbox\Manager::getObjectById($id);
				AddEventToStatFile('sale', 'updateCashbox', $id, $service::getCode());
			}
		}
		else
		{
			$cashbox['ENABLED'] = 'Y';
			$result = Cashbox\Manager::add($cashbox);
			$id = $result->getId();

			if ($result->isSuccess())
			{
				$service = Cashbox\Manager::getObjectById($id);
				AddEventToStatFile('sale', 'addCashbox', $id, $service::getCode());
			}
		}

		if ($result->isSuccess())
		{
			if ($adminSidePanelHelper->isAjaxRequest())
			{
				$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $id));
			}
			else
			{
				if ($request->getPost("apply") == '')
				{
					$adminSidePanelHelper->localRedirect($listUrl);
					LocalRedirect($listUrl);
				}
				else
				{
					$applyUrl = $selfFolderUrl."sale_cashbox_edit.php?lang=".$lang."&ID=".$id;
					$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
					LocalRedirect($applyUrl);
				}
			}
		}
		else
		{
			$errorMessage .= implode("\n", $result->getErrorMessages());
		}
	}
	else
	{
		$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
	}
}
elseif ($id > 0)
{
	$cashboxObject = Cashbox\Manager::getObjectById($id);
}

require($documentRoot."/bitrix/modules/main/include/prolog_admin_after.php");
Page\Asset::getInstance()->addJs("/bitrix/js/sale/cashbox.js");

$APPLICATION->SetTitle(($id > 0) ? Loc::getMessage("SALE_CASHBOX_EDIT_RECORD", array("#ID#" => $id)) : Loc::getMessage("SALE_CASHBOX_NEW_RECORD"));

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("SALE_TAB_CASHBOX"),
		"ICON" => "sale",
		"TITLE" => GetMessage("SALE_TAB_CASHBOX_DESCR"),
	)
);

if ($id > 0 && !$request->isPost())
{
	$res = \Bitrix\Sale\Cashbox\Internals\CashboxTable::getList(array('filter' => array('ID' => $id)));
	$cashbox = $res->fetch();
}

$requireFields = array();
if (class_exists($cashbox['HANDLER']))
{
	$requireFields = $cashbox['HANDLER']::getGeneralRequiredFields();
}

$isCashboxPaySystem = ($cashboxObject && Cashbox\Manager::isPaySystemCashbox($cashboxObject->getField('HANDLER')));

if ($id > 0 && $cashboxObject && !$isCashboxPaySystem)
{
	$aTabs[] = array(
		"DIV" => "edit2",
		"TAB" => GetMessage("SALE_CASHBOX_RESTRICTION"),
		"ICON" => "sale",
		"TITLE" => GetMessage("SALE_CASHBOX_RESTRICTION_DESC"),
	);
}

$aTabs[] = array(
	"DIV" => "edit3",
	"TAB" => GetMessage("SALE_CASHBOX_TAB_TITLE_SETTINGS"),
	"ICON" => "sale",
	"TITLE" => GetMessage("SALE_CASHBOX_TAB_TITLE_SETTINGS_DESC"),
);

$aTabs[] = array(
	"DIV" => "edit4",
	"TAB" => GetMessage("SALE_CASHBOX_TAB_TITLE_OFD_SETTINGS"),
	"ICON" => "sale",
	"TITLE" => GetMessage("SALE_CASHBOX_TAB_TITLE_OFD_SETTINGS_DESC"),
);
$tabControl = new CAdminForm("tabControl", $aTabs);

$restrictionsHtml = '';

if ($id > 0 && !$isCashboxPaySystem)
{
	ob_start();
	require_once($documentRoot."/bitrix/modules/sale/admin/cashbox_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();
}

$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("SALE_CASHBOX_2FLIST"),
		"LINK" => $listUrl,
		"ICON" => "btn_list"
	)
);

if ($id > 0 && $saleModulePermissions >= "W" && !$isCashboxPaySystem)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$deleteUrl = $selfFolderUrl."sale_cashbox_list.php?action=delete&ID[]=".$id."&lang=".$context->getLanguage()."&".bitrix_sessid_get()."#tb";
	$buttonAction = "LINK";
	if ($adminSidePanelHelper->isPublicFrame())
	{
		$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
		$buttonAction = "ONCLICK";
	}
	$aMenu[] = array(
			"TEXT" => Loc::getMessage("SALE_DELETE_CASHBOX"),
			$buttonAction => "javascript:if(confirm('".Loc::getMessage("SPSN_DELETE_CASHBOX_CONFIRM")."')) top.window.location.href='".$deleteUrl."';",
			"WARNING" => "Y",
			"ICON" => "btn_delete"
		);
}
$contextMenu = new CAdminContextMenu($aMenu);
$contextMenu->Show();

if ($errorMessage !== '')
	CAdminMessage::ShowMessage(array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>Loc::getMessage("SALE_CASHBOX_ERROR"), "HTML"=>true));

$valuePrecision = (int)Config\Option::get('sale', 'value_precision');
if ($valuePrecision > 2)
{
	$note = BeginNote();
	$note .= Loc::getMessage('SALE_CASHBOX_NOTE_VALUE_PRECISION');
	$note .= EndNote();
	echo $note;
}
$tabControl->BeginEpilogContent();
echo GetFilterHiddens("filter_");
echo bitrix_sessid_post();
?>

<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?=$context->getLanguage();?>">
<input type="hidden" name="ID" value="<?=$id;?>" id="ID">

<?
$tabControl->EndEpilogContent();
$actionUrl = $APPLICATION->GetCurPage()."?ID=".$id."&lang=".$lang;
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
$tabControl->Begin(array("FORM_ACTION" => $actionUrl));
$tabControl->BeginNextFormTab();
if ($id > 0)
	$tabControl->AddViewField("ID", "ID:", $id);

$isCashbox1C = (Cashbox\Cashbox1C::getId() > 0 && (int)$id === (int)Cashbox\Cashbox1C::getId());

if ($isCashboxPaySystem)
{
	$tabControl->BeginCustomField('ACTIVE', '');
	echo '<input type="hidden" name="ACTIVE" id="ACTIVE" value="Y">';
	$tabControl->EndCustomField('ACTIVE', '');
}
else
{
	$active = isset($cashbox['ACTIVE']) ? $cashbox['ACTIVE'] : 'Y';
	$tabControl->AddCheckBoxField("ACTIVE", GetMessage("SALE_CASHBOX_ACTIVE").':', false, 'Y', $active === 'Y');
}

$tabControl->BeginCustomField('HANDLER', GetMessage("SALE_CASHBOX_HANDLER"));
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=Loc::getMessage("SALE_CASHBOX_HANDLER");?>:</td>
		<td width="60%" valign="top">
			<?php
				$disabled = '';

				if ($isCashbox1C || $isCashboxPaySystem)
				{
					$disabled = 'disabled';
					echo '<input type="hidden" name="HANDLER" id="HANDLER" value="'.htmlspecialcharsbx($cashbox['HANDLER']).'">';
				}
			?>
			<select name="HANDLER" id="HANDLER" onchange="BX.Sale.Cashbox.reloadSettings()" <?=$disabled;?>>
				<?
					$handlerList = Bitrix\Sale\Cashbox\Cashbox::getHandlerList();
				?>
				<option value=""><?=Loc::getMessage("SALE_CASHBOX_NO_HANDLER") ?></option>
				<?php
				foreach ($handlerList as $handler => $path)
				{
					if ($handler === '\Bitrix\Sale\Cashbox\Cashbox1C' && $cashbox['ID'] != Cashbox\Cashbox1C::getId())
					{
						continue;
					}

					if (Cashbox\Manager::isPaySystemCashbox($handler))
					{
						$skip = true;

						if (isset($cashbox['HANDLER']) && Cashbox\Manager::isPaySystemCashbox($cashbox['HANDLER']))
						{
							$paySystemCashboxList = Cashbox\Manager::getList([
								'select' => ['ID'],
								'filter' => [
									'=ACTIVE' => 'Y',
									'=HANDLER' => $cashbox['HANDLER'],
									'=KKM_ID' => $cashbox['KKM_ID'],
								],
							])->fetchAll();
							foreach ($paySystemCashboxList as $paySystemCashbox)
							{
								if ($cashbox['ID'] === $paySystemCashbox['ID'])
								{
									$skip = false;
								}
							}
						}

						if ($skip)
						{
							continue;
						}
					}

					$restHandlers = [];
					$isRestHandler = $handler === '\Bitrix\Sale\Cashbox\CashboxRest';
					if ($isRestHandler)
					{
						$restHandlers = Cashbox\Manager::getRestHandlersList();
						foreach ($restHandlers as $restHandlerCode => $restHandlerConfig)
						{
							$selected = ($restHandlerCode === $cashbox['SETTINGS']['REST']['REST_CODE']) ? 'selected' : '';
							echo '<option data-rest-code="'.htmlspecialcharsbx($restHandlerCode).'" value="'.htmlspecialcharsbx($handler).'" '.$selected.'>'.htmlspecialcharsbx($restHandlerConfig['NAME']).'</option>';
						}
					}
					elseif (class_exists($handler))
					{
						$selected = ($handler === $cashbox['HANDLER']) ? 'selected' : '';
						$handlerName = $handler::getName();
						if ($handler === '\Bitrix\Sale\Cashbox\CashboxCheckbox' && (!$isCloud && $zone !== 'ua'))
						{
							$handlerName .= ' ' . Loc::getMessage('SALE_CASHBOX_FOR_UA');
						}
						echo '<option value="'.$handler.'" '.$selected.'>'.htmlspecialcharsbx($handlerName).'</option>';
					}
				}
				?>
			</select>
			<?if ($cashboxObject instanceof Cashbox\ITestConnection):?>
				<input type="button" id="TEST_BUTTON" value="<?=Loc::getMessage('SALE_CASHBOX_CONNECTION')?>" onclick="BX.Sale.Cashbox.testConnection(<?=$id?>)">
			<?endif;?>
			<span id="hint_handler_wrapper">

				<span id="hint_HANDLER">
					<?php
					if ($cashboxObject)
					{
						$handlerHint = Loc::getMessage('SALE_CASHBOX_'.ToUpper($cashboxObject::getCode()).'_HINT');
						if ($handlerHint)
						{
						?>
							<script>
								BX.hint_replace(BX('hint_HANDLER'), "<?=$handlerHint;?>");
							</script>
						<?
						}
					}
					?>
				</span>
			</span>
		</td>
	</tr>
<?
$tabControl->EndCustomField('HANDLER', '');

$zone = 'ru';
if (Loader::includeModule("bitrix24"))
{
	$zone = \CBitrix24::getLicensePrefix();
}
elseif (Loader::includeModule('intranet'))
{
	$zone = \CIntranetUtils::getPortalZone();
}

$needOfdSettings = !$isCashboxPaySystem && $zone === 'ru';

if ($needOfdSettings)
{
	$tabControl->BeginCustomField('OFD', GetMessage("SALE_CASHBOX_OFD"));
	?>
	<tr id="tr_OFD">
		<td width="40%">
			<span <?=(isset($requireFields['OFD']) ? 'class="adm-required-field"' : '')?>><?=Loc::getMessage("SALE_CASHBOX_OFD");?>:</span>
		</td>
		<td width="60%">
			<select name="OFD" id="OFD" onchange="BX.Sale.Cashbox.reloadOfdSettings()">
				<?
				$ofdList = Bitrix\Sale\Cashbox\Ofd::getHandlerList();
				foreach ($ofdList as $handler => $name)
				{
					$selected = ($handler === $cashbox['OFD']) ? 'selected' : '';
					echo '<option value="'.$handler.'" '.$selected.'>'.htmlspecialcharsbx($name).'</option>';
				}

				$selected = ($cashbox['OFD'] == '') ? 'selected' : '';
				?>
				<option value="" <?=$selected;?>><?=Loc::getMessage("SALE_CASHBOX_OTHER_HANDLER");?></option>
			</select>
		</td>
	</tr>
	<?php
	$tabControl->EndCustomField('OFD', '');
}

$name = $request->get('NAME') ? $request->get('NAME') : $cashbox['NAME'];
$tabControl->AddEditField('NAME', Loc::getMessage("SALE_CASHBOX_NAME").':', true, array('SIZE' => 40), $name);

$tabControl->BeginCustomField('KKM_ID', GetMessage("SALE_CASHBOX_KKM_ID"));
?>
	<tbody id="sale-cashbox-models-container">
		<?if ($cashbox['HANDLER'] && class_exists($cashbox['HANDLER'])):?>
			<?
			$kkmList = $cashbox['HANDLER']::getSupportedKkmModels();
			if ($kkmList):
			?>
			<tr id="tr_KKM_ID">
				<td width="40%">
					<span <?=(isset($requireFields['KKM_ID']) ? 'class="adm-required-field"' : '')?>><?=Loc::getMessage("SALE_CASHBOX_KKM_ID");?>:</span>
				</td>
				<td width="60%">
					<?php
					$disabled = '';

					if ($isCashboxPaySystem)
					{
						$disabled = 'disabled';
						echo '<input type="hidden" name="KKM_ID" id="KKM_ID" value="'.$cashbox['KKM_ID'].'">';
					}
					?>
					<select name="KKM_ID" id="KKM_ID" onchange="BX.Sale.Cashbox.reloadSettings()" <?=$disabled?>>
						<option value=""><?=Loc::getMessage('SALE_CASHBOX_KKM_NO_CHOOSE')?></option>
						<?
							foreach ($kkmList as $code => $kkm)
							{
								$selected = ($code === $cashbox['KKM_ID']) ? 'selected' : '';
								echo '<option value="'.$code.'" '.$selected.'>'.htmlspecialcharsbx($kkm['NAME']).'</option>';
							}
						?>
					</select>
				</td>
			</tr>
			<?endif;?>
		<?endif;?>
	</tbody>
<?
$tabControl->EndCustomField('KKM_ID', '');

if (!$isCashboxPaySystem)
{
	$numberKkm = $request->get('NUMBER_KKM') ? $request->get('NUMBER_KKM') : $cashbox['NUMBER_KKM'];
	$tabControl->BeginCustomField('NUMBER_KKM', GetMessage("SALE_CASHBOX_EXTERNAL_UUID"));
	?>
	<tr id="tr_NUMBER_KKM">
		<td width="40%"><span <?=(isset($requireFields['NUMBER_KKM']) ? 'class="adm-required-field"' : '')?>><?=Loc::getMessage("SALE_CASHBOX_EXTERNAL_UUID");?>:</span></td>
		<td width="60%">
			<input type="text" ID="NUMBER_KKM" name="NUMBER_KKM" value="<?=htmlspecialcharsbx($numberKkm);?>">
			<span id="hint_NUMBER_KKM"></span>

		</td>
	</tr>
	<?php if ($zone !== 'ua'): ?>
	<script>
		BX.hint_replace(BX('hint_NUMBER_KKM'), '<?=Loc::getMessage('SALE_CASHBOX_EXTERNAL_UUID_HINT_V2');?>');
	</script>
<?php endif; ?>
	<?
	$tabControl->EndCustomField('NUMBER_KKM', '');

	$isOffline = isset($cashbox['USE_OFFLINE']) ? $cashbox['USE_OFFLINE'] : 'N';
	$tabControl->AddCheckBoxField("USE_OFFLINE", GetMessage("SALE_CASHBOX_USE_OFFLINE").':', false, 'Y', $isOffline === 'Y');
}

$tabControl->BeginCustomField('EMAIL', GetMessage("SALE_CASHBOX_EMAIL"));
$email = $request->get('EMAIL') ? $request->get('EMAIL') : $cashbox['EMAIL'];
?>
	<tr id="tr_EMAIL">
		<td width="40%">
			<span class="adm-required-field">
				<?=Loc::getMessage("SALE_CASHBOX_EMAIL");?>:
			</span>
		</td>
		<td width="60%">
			<input type="text" ID="EMAIL" name="EMAIL" value="<?=htmlspecialcharsbx($email);?>">
			<span id="hint_EMAIL"></span>

		</td>
	</tr>
	<script>
		BX.hint_replace(BX('hint_EMAIL'), '<?=Loc::getMessage('SALE_CASHBOX_EMAIL_HINT');?>');
	</script>
<?
$tabControl->EndCustomField('EMAIL');

if ($restrictionsHtml !== ''):
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField('CASHBOX_RULES', GetMessage("CASHBOX_RULES"));
?>
	<tr><td id="sale-cashbox-restriction-container"><?=$restrictionsHtml?></td></tr>
	<?$tabControl->EndCustomField('CASHBOX_RULES');
endif;


$tabControl->BeginNextFormTab();

ob_start();
require_once($documentRoot."/bitrix/modules/sale/admin/cashbox_settings.php");
$cashboxSettings = ob_get_contents();
ob_end_clean();

$tabControl->BeginCustomField('CASHBOX_SETTINGS', GetMessage("CASHBOX_SETTINGS"));?>
	<tbody id="sale-cashbox-settings-container"><?=$cashboxSettings?></tbody>
<?$tabControl->EndCustomField('CASHBOX_SETTINGS');

if ($needOfdSettings)
{
	$tabControl->BeginNextFormTab();

	ob_start();
	require_once($documentRoot."/bitrix/modules/sale/admin/cashbox_ofd_settings.php");
	$cashboxOfdSettings = ob_get_contents();
	ob_end_clean();

	$tabControl->BeginCustomField('OFD_SETTINGS', GetMessage("CASHBOX_OFD_SETTINGS"));
	?>
	<tbody id="sale-cashbox-ofd-settings-container"><?=$cashboxOfdSettings?></tbody>
	<?php
	$tabControl->EndCustomField('OFD_SETTINGS');
}

$tabControl->Buttons(array("disabled" => ($saleModulePermissions < "W"), "back_url" => $listUrl));

$tabControl->Show();
?>
<script language="JavaScript">

	BX.message({
		CASHBOX_CHECK_CONNECTION_TITLE: '<?=Loc::getMessage("CASHBOX_CHECK_CONNECTION_TITLE")?>',
		CASHBOX_CHECK_CONNECTION_TITLE_POPUP_CLOSE: '<?=Loc::getMessage("CASHBOX_CHECK_CONNECTION_TITLE_POPUP_CLOSE")?>',
		SALE_RDL_RESTRICTION: '<?=Loc::getMessage("SALE_CASHBOX_RDL_RESTRICTION")?>',
		SALE_RDL_SAVE: '<?=Loc::getMessage("SALE_CASHBOX_RDL_SAVE")?>',
		SALE_CASHBOX_CASHBOXCHECKBOX_HINT: '<?=Loc::getMessage("SALE_CASHBOX_CASHBOXCHECKBOX_HINT")?>',
		SALE_CASHBOX_CASHBOXBUSINESSRU_HINT: '<?=GetMessageJS("SALE_CASHBOX_CASHBOXBUSINESSRU_HINT")?>'
	});
</script>
<?
require($documentRoot."/bitrix/modules/main/include/epilog_admin.php");
?>
