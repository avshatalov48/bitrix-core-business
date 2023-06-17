<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use	Bitrix\Main\Service\GeoIp;

require_once(__DIR__."/../include/prolog_admin_before.php");

if(!$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loc::loadMessages(__FILE__);

$className = isset($_REQUEST['CLASS_NAME']) ? htmlspecialcharsbx($_REQUEST['CLASS_NAME']) : '';
$id = isset($_REQUEST["ID"]) && intval($_REQUEST["ID"]) > 0 ? intval($_REQUEST["ID"]) : 0;
$errMess = null;

$handler = GeoIp\Manager::getHandlerByClassName($className);

if(!$handler)
	LocalRedirect(!empty($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : 'geoip_handlers_list.php?lang='.LANG);

if($_SERVER['REQUEST_METHOD'] == "POST" && (!empty($_POST['save']) || !empty($_POST['apply'])) && check_bitrix_sessid())
{
	$fields = array(
		"CLASS_NAME" => $className,
		"SORT" => intval($_POST["SORT"] ?? 0),
		"ACTIVE" => isset($_POST["ACTIVE"]) && $_POST["ACTIVE"] == 'Y' ? 'Y' : 'N',
		"CONFIG" => $handler->createConfigField($_POST),
	);

	if($id > 0)
	{
		$res = GeoIp\HandlerTable::update($id, $fields);
	}
	else
	{
		$res = GeoIp\HandlerTable::add($fields);
	}

	if($res->isSuccess())
	{
		$id = $res->getId();

		if(isset($_POST['apply']))
			LocalRedirect("geoip_handler_edit.php?lang=".LANG."&ID=".$id."&CLASS_NAME=".urlencode($className));
		else
			LocalRedirect(!empty($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : 'geoip_handlers_list.php?lang='.LANG);
	}
	else
	{
		$errMess = new CAdminMessage(
			implode("\n<br>", $res->getErrorMessages()
		));
	}
}

$providingData = $handler->getProvidingData();
$APPLICATION->SetTitle(Loc::getMessage('GEOIP_EDIT_TITLE'));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$menu = array(
	array(
		"TEXT" => Loc::getMessage('GEOIP_EDIT_LIST'),
		"TITLE" => Loc::getMessage('GEOIP_EDIT_LIST_T'),
		"LINK"=>"geoip_handlers_list.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);

if($id > 0)
{
	$menu[] = array(
		"TEXT" => Loc::getMessage('GEOIP_EDIT_DELETE'),
		"TITLE" => Loc::getMessage('GEOIP_EDIT_DELETE_T'),
		"LINK" => "javascript:if(confirm('".GetMessage("GEOIP_EDIT_DELETE_CONFIRM")."')) window.location='geoip_handlers_list.php?ID=".$id."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON" => "btn_delete",
	);
}

$context = new CAdminContextMenu($menu);
$context->Show();

if($errMess)
	echo $errMess->Show();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => Loc::getMessage('GEOIP_EDIT_MAIN_SETTINGS'), "TITLE" => Loc::getMessage('GEOIP_EDIT_MAIN_SETTINGS_T')),
	array("DIV" => "edit2", "TAB" => Loc::getMessage('GEOIP_EDIT_SPECIFIC_SETTINGS'), "TITLE" => Loc::getMessage('GEOIP_EDIT_SPECIFIC_SETTINGS_T')),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>

<form method="POST" action="<?= $APPLICATION->GetCurPage()?>" name="geoip_handlers_form">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$id?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<?if($id > 0):?>
	<tr>
		<td width="40%"><?=Loc::getMessage('GEOIP_EDIT_F_ID')?>:</td>
		<td width="60%"><?=$id?></td>
	</tr>
<?endif;?>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_F_TITLE')?>:</td>
		<td><?=$handler->getTitle()?></td>
	</tr>
	<tr class="adm-detail-valign-top">
		<td><?=Loc::getMessage('GEOIP_EDIT_F_DESCRIPTION')?>:</td>
		<td><?=$handler->getDescription()?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_F_ACTIVE')?>:</td>
		<td><input type="checkbox" name="ACTIVE" value="Y"<?=$handler->isActive() ? ' checked' : ''?>></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_F_SORT')?>:</td>
		<td><input type="text" name="SORT" size="3" maxlength="10" value="<?=$handler->getSort()?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_F_CLASS')?>:</td>
		<td><input type="text" name="CLASS_NAME" size="45" maxlength="255" value="<?=$className?>" readonly></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_F_IS_INSTALLED')?>:</td>
		<td><input type="checkbox" name="IS_INSTALLED" value="Y"<?=$handler->isInstalled() ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_F_LANG')?>:</td>
		<td><?=implode(', ', $handler->getSupportedLanguages())?></td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?=Loc::getMessage('GEOIP_EDIT_PROVIDING_INFO')?>:</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("geoip_edit_continent_code") ?></td>
		<td><input type="checkbox"<?=$providingData->continentCode ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("geoip_edit_continent_name") ?></td>
		<td><input type="checkbox"<?=$providingData->continentName ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_COUNTRY_CODE')?>:</td>
		<td><input type="checkbox"<?=$providingData->countryCode ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_COUNTRY_NAME')?>:</td>
		<td><input type="checkbox"<?=$providingData->countryName ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_REGION_CODE')?>:</td>
		<td><input type="checkbox"<?=$providingData->regionCode ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_REGION_NAME')?>:</td>
		<td><input type="checkbox"<?=$providingData->regionName ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("geoip_edit_subregion_code") ?></td>
		<td><input type="checkbox"<?=$providingData->subRegionCode ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_SUBREGION_NAME')?>:</td>
		<td><input type="checkbox"<?=$providingData->subRegionName ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_CITY_NAME')?>:</td>
		<td><input type="checkbox"<?=$providingData->cityName ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("geoip_edit_geonames") ?></td>
		<td><input type="checkbox"<?=$providingData->cityGeonameId ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_ZIP')?>:</td>
		<td><input type="checkbox"<?=$providingData->zipCode ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_LAT')?>:</td>
		<td><input type="checkbox"<?=$providingData->latitude ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_LON')?>:</td>
		<td><input type="checkbox"<?=$providingData->longitude ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_TIMEZONE')?>:</td>
		<td><input type="checkbox"<?=$providingData->timezone ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_ISP')?>:</td>
		<td><input type="checkbox"<?=$providingData->ispName ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_ORG')?>:</td>
		<td><input type="checkbox"<?=$providingData->organizationName ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('GEOIP_EDIT_PI_ASN')?>:</td>
		<td><input type="checkbox"<?=$providingData->asn ? ' checked' : ''?> disabled></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("geoip_edit_asn_org") ?></td>
		<td><input type="checkbox"<?=$providingData->asnOrganizationName ? ' checked' : ''?> disabled></td>
	</tr>

<?$tabControl->BeginNextTab();?>
	<?$adminConfigHtml = GeoIp\Manager::getHandlerAdminConfigHtml($handler);?>

	<?if(!empty($adminConfigHtml)):?>
		<?=$adminConfigHtml?>
	<?else:?>
		<tr>
			<td colspan="2"><?=Loc::getMessage('GEOIP_EDIT_SPECIFIC_ABSENT')?>:</td>
		</tr>
	<?endif;?>
<?
$tabControl->Buttons(array(
	"back_url" => "geoip_handlers_list.php?lang=".LANG,
));
$tabControl->End();
?>
</form>

<?php
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
