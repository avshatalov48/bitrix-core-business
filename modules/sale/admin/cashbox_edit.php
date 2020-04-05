<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Page;
use Bitrix\Main\Config;
use Bitrix\Sale\Cashbox;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();
$server = $context->getServer();
$lang = $context->getLanguage();
$documentRoot = Application::getDocumentRoot();

\Bitrix\Sale\Cashbox\Cashbox::init();

$id = (int)$request->get('ID');

$cashbox = array();
$errorMessage = '';

if ($server->getRequestMethod() == "POST"
	&& ($request->get('save') !== null || $request->get('apply') !== null)
	&& $saleModulePermissions == "W"
	&& check_bitrix_sessid()
)
{
	$cashbox = array(
		'NAME' => $request->get('NAME'),
		'HANDLER' => $request->getPost('HANDLER'),
		'OFD' => $request->getPost('OFD'),
		'NUMBER_KKM' => $request->getPost('NUMBER_KKM'),
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

	$cashbox['SETTINGS'] = $handler::extractSettingsFromRequest($request);

	$result = $handler::validateFields($cashbox);
	if (!$result->isSuccess())
	{
		foreach ($result->getErrors() as $error)
			$errorMessage .= $error->getMessage()."<br>\n";
	}

	/** @var Cashbox\Ofd $ofd */
	$ofd = $cashbox['OFD'];
	if ($ofd)
	{
		$ofdList = Cashbox\Ofd::getHandlerList();
		if (class_exists($ofd) && isset($ofdList[$cashbox['OFD']]))
		{
			$result = $ofd::validateSettings($cashbox['OFD_SETTINGS']);
			if (!$result->isSuccess())
			{
				foreach ($result->getErrors() as $error)
					$errorMessage .= $error->getMessage()."<br>\n";
			}
		}
		else
		{
			$errorMessage .= GetMessage('ERROR_NO_OFD_EXIST')."<br>\n";
		}
	}

	if ($errorMessage === '')
	{
		if ($id > 0)
		{
			$result = Cashbox\Manager::update($id, $cashbox);
		}
		else
		{
			$cashbox['ENABLED'] = 'Y';
			$result = Cashbox\Manager::add($cashbox);
			$id = $result->getId();
		}

		if ($result->isSuccess())
		{
			if (strlen($request->getPost("apply")) == 0)
				LocalRedirect("/bitrix/admin/sale_cashbox_list.php?lang=".$lang."&".GetFilterParams("filter_", false));
			else
				LocalRedirect("/bitrix/admin/sale_cashbox_edit.php?lang=".$lang."&ID=".$id."&".GetFilterParams("filter_", false));
		}
		else
		{
			$errorMessage .= implode("\n", $result->getErrorMessages());
		}
	}
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

if ($id > 0)
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

if ($id > 0)
{
	ob_start();
	require_once($documentRoot."/bitrix/modules/sale/admin/cashbox_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();
}

$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("SALE_CASHBOX_2FLIST"),
		"LINK" => "/bitrix/admin/sale_cashbox_list.php?lang=".$context->getLanguage().GetFilterParams("filter_"),
		"ICON" => "btn_list"
	)
);

if ($id > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => Loc::getMessage("SALE_DELETE_CASHBOX"),
			"LINK" => "javascript:if(confirm('".Loc::getMessage("SPSN_DELETE_CASHBOX_CONFIRM")."')) window.location='/bitrix/admin/sale_cashbox_list.php?action=delete&ID[]=".$id."&lang=".$context->getLanguage()."&".bitrix_sessid_get()."#tb';",
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
$tabControl->Begin(array("FORM_ACTION" => $APPLICATION->GetCurPage()."?ID=".$id."&lang=".$lang));
$tabControl->BeginNextFormTab();
if ($id > 0)
	$tabControl->AddViewField("ID", "ID:", $id);

$active = isset($cashbox['ACTIVE']) ? $cashbox['ACTIVE'] : 'Y';
$tabControl->AddCheckBoxField("ACTIVE", GetMessage("SALE_CASHBOX_ACTIVE").':', false, 'Y', $active === 'Y');

$tabControl->BeginCustomField('HANDLER', GetMessage("SALE_CASHBOX_HANDLER"));
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=Loc::getMessage("SALE_CASHBOX_HANDLER");?>:</td>
		<td width="60%" valign="top">
			<?
				$disabled = '';
				if (Cashbox\Cashbox1C::getId() > 0 && $id == Cashbox\Cashbox1C::getId())
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
				<?
				foreach ($handlerList as $handler => $path)
				{
					if ($handler === '\Bitrix\Sale\Cashbox\Cashbox1C' && $cashbox['ID'] != Cashbox\Cashbox1C::getId())
						continue;

					if (class_exists($handler))
					{
						$selected = ($handler === $cashbox['HANDLER']) ? 'selected' : '';
						echo '<option value="'.$handler.'" '.$selected.'>'.$handler::getName().'</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
<?
$tabControl->EndCustomField('HANDLER', '');

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
					echo '<option value="'.$handler.'" '.$selected.'>'.$name.'</option>';
				}

				$selected = ($cashbox['OFD'] == '') ? 'selected' : '';
				?>
				<option value="" <?=$selected;?>><?=Loc::getMessage("SALE_CASHBOX_OTHER_HANDLER");?></option>
			</select>
		</td>
	</tr>

<?
$tabControl->EndCustomField('OFD', '');

$name = $request->get('NAME') ? $request->get('NAME') : $cashbox['NAME'];
$tabControl->AddEditField('NAME', Loc::getMessage("SALE_CASHBOX_NAME").':', true, array('SIZE' => 40), $name);


$tabControl->BeginCustomField('KKM_ID', GetMessage("SALE_CASHBOX_KKM_ID"));
?>
	<tbody id="sale-cashbox-models-container">
		<?if ($cashbox['HANDLER']):?>
			<?
			$kkmList = $cashbox['HANDLER']::getSupportedKkmModels();
			if ($kkmList):
			?>
			<tr id="tr_KKM_ID">
				<td width="40%">
					<span <?=(isset($requireFields['KKM_ID']) ? 'class="adm-required-field"' : '')?>><?=Loc::getMessage("SALE_CASHBOX_KKM_ID");?>:</span>
				</td>
				<td width="60%">
					<select name="KKM_ID" id="KKM_ID" onchange="BX.Sale.Cashbox.reloadSettings()">
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
	<script>
		BX.hint_replace(BX('hint_NUMBER_KKM'), '<?=Loc::getMessage('SALE_CASHBOX_EXTERNAL_UUID_HINT_V2');?>');
	</script>
<?
$tabControl->EndCustomField('NUMBER_KKM', '');

$isOffline = isset($cashbox['USE_OFFLINE']) ? $cashbox['USE_OFFLINE'] : 'N';
$tabControl->AddCheckBoxField("USE_OFFLINE", GetMessage("SALE_CASHBOX_USE_OFFLINE").':', false, 'Y', $isOffline === 'Y');

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

$tabControl->BeginNextFormTab();

ob_start();
require_once($documentRoot."/bitrix/modules/sale/admin/cashbox_ofd_settings.php");
$cashboxOfdSettings = ob_get_contents();
ob_end_clean();

$tabControl->BeginCustomField('OFD_SETTINGS', GetMessage("CASHBOX_OFD_SETTINGS"));?>
	<tbody id="sale-cashbox-ofd-settings-container"><?=$cashboxOfdSettings?></tbody>
<?$tabControl->EndCustomField('OFD_SETTINGS');

$tabControl->Buttons(
	array(
		"disabled" => ($saleModulePermissions < "W"),
		"back_url" => "/bitrix/admin/sale_cashbox_list.php?lang=".$context->getLanguage().GetFilterParams("filter_")
	)
);

$tabControl->Show();
?>
<script language="JavaScript">

	BX.message({
		SALE_RDL_RESTRICTION: '<?=Loc::getMessage("SALE_CASHBOX_RDL_RESTRICTION")?>',
		SALE_RDL_SAVE: '<?=Loc::getMessage("SALE_CASHBOX_RDL_SAVE")?>'
	});
</script>
<?
require($documentRoot."/bitrix/modules/main/include/epilog_admin.php");
?>
