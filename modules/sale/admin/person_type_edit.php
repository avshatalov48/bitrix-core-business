<?php

/** @global CMain $APPLICATION */
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."sale_person_type.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('sale');

IncludeModuleLangFile(__FILE__);

$request = Context::getCurrent()->getRequest();

ClearVars();

$errorMessage = "";
$bVarsFromForm = false;

$ID = (int)$request->get('ID');

if ($request->isPost() && $request->getPost('Update') !== null && $saleModulePermissions>="W" && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();

	if ($ACTIVE != "Y")
	{
		$ACTIVE = "N";
	}

	if ($CODE !== '')
	{
		$dbRes = CSalePersonType::GetList([], ['CODE' => $CODE, '!ID' => $ID]);
		if ($dbRes->Fetch())
		{
			$errorMessage .= GetMessage("SPTEN_ERROR_PERSON_TYPE_EXISTS")."<br>";
		}
	}

	if ($errorMessage === '')
	{
		$arFields = array(
			"LID" => $LID,
			"NAME" => $NAME,
			"CODE" => $CODE,
			"SORT" => $SORT,
			"ACTIVE" => $ACTIVE,
			"ENTITY_REGISTRY_TYPE" => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER,
			"XML_ID" => $XML_ID ?: \Bitrix\Sale\PersonType::generateXmlId(),
		);

		if ($ID > 0)
		{
			if (!CSalePersonType::Update($ID, $arFields))
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString()."<br>";
				else
					$errorMessage .= GetMessage("SPTEN_ERROR_SAVING_PERSON_TYPE")."<br>";
			}
		}
		else
		{
			$ID = CSalePersonType::Add($arFields);
			$ID = intval($ID);
			if ($ID > 0)
			{
				$propsGroupId = CSaleOrderPropsGroup::Add([
						'PERSON_TYPE_ID' => $ID,
						'NAME' => GetMessage('PROPS_GROUP_DEFAULT_NAME'),
						'SORT' => 0,
				]);

				if ((int)$propsGroupId <= 0)
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString().". ";
					else
						$errorMessage .= GetMessage("SOPGEN_ERROR_SAVING_PROPS_GRP").". ";
				}
			}
			else
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString()."<br>";
				else
					$errorMessage .= GetMessage("SPTEN_ERROR_SAVING_PERSON_TYPE")."<br>";
			}
		}

		\Bitrix\Sale\Internals\BusinessValuePersonDomainTable::deleteByPersonTypeId($ID);

		if ($BUSVAL_DOMAIN !== '')
		{
			\Bitrix\Sale\Internals\BusinessValuePersonDomainTable::add(array(
				'PERSON_TYPE_ID' => $ID,
				'DOMAIN' => $BUSVAL_DOMAIN,
			));
		}
	}

	if ($errorMessage == '')
	{
		$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $ID));
		if ($apply == '')
		{
			$adminSidePanelHelper->localRedirect($listUrl);
			LocalRedirect($listUrl);
		}
		else
		{
			$applyUrl = $selfFolderUrl."sale_person_type_edit.php?lang=".LANGUAGE_ID."&ID=".$ID;
			$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
			LocalRedirect($applyUrl);
		}
	}
	else
	{
		$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(GetMessage("SPTEN_UPDATING"));
else
	$APPLICATION->SetTitle(GetMessage("SPTEN_ADDING"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($saleModulePermissions < "W")
	$errorMessage .= GetMessage("SPTEN_NO_PERMS2ADD").".<br>";

if(intval($ID) > 0)
{
	$personType = \Bitrix\Sale\Internals\PersonTypeTable::getRowById($ID);

	$dbRes = \Bitrix\Sale\Internals\PersonTypeSiteTable::getList([
		'filter' => [
			'=PERSON_TYPE_ID' => $personType['ID']
		]
	]);
	while ($data = $dbRes->fetch())
	{
		$personType['LIDS'][] = $data['SITE_ID'];
	}
}
else
{
	$ID = 0;
}

?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("SPTEN_2FLIST"),
		"LINK" => $listUrl,
		"ICON" => "btn_list"
	)
);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");
	$addUrl = $selfFolderUrl."sale_person_type_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aMenu[] = array(
		"TEXT" => GetMessage("SPTEN_NEW_PERSON_TYPE"),
		"LINK" => $addUrl,
		"ICON" => "btn_new"
	);

	if ($personType['CODE'] !== 'CRM_COMPANY' && $personType['CODE'] !== 'CRM_CONTACT')
	{
		$deleteUrl = $selfFolderUrl."sale_person_type.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."#tb";
		$buttonAction = "LINK";
		if ($adminSidePanelHelper->isPublicFrame())
		{
			$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
			$buttonAction = "ONCLICK";
		}
		$aMenu[] = array(
			"TEXT" => GetMessage("SPTEN_DELETE_PERSON_TYPE"),
			$buttonAction => "javascript:if(confirm('".GetMessage("SPTEN_DELETE_PERSON_TYPE_CONFIRM")."')) top.window.location.href='".$deleteUrl."';",
			"WARNING" => "Y",
			"ICON" => "btn_delete"
		);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?if($errorMessage <> '')
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SPTEN_ERROR"), "HTML"=>true));?>
<?
$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
?>
<form method="POST" action="<?=$actionUrl?>" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(array("DIV" => "edit1", "TAB" => GetMessage("SPTEN_TAB_PERSON_TYPE"), "ICON" => "sale", "TITLE" => GetMessage("SPTEN_TAB_PERSON_TYPE_DESCR")));

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?echo GetMessage("F_ACTIVE");?>:</td>
		<td width="60%">
			<input type="checkbox" name="ACTIVE" value="Y" <?if ($personType['ACTIVE']=="Y") echo "checked"?>>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%" valign="top"><?echo GetMessage("SPTEN_SITE")?>:</td>
		<td width="60%">
			<?echo CSite::SelectBoxMulti("LID", $personType['LIDS']);?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("SPTEN_NAME")?>:</td>
		<td width="60%">
			<input type="text" name="NAME" size="30" maxlength="100" value="<?= htmlspecialcharsbx($personType['NAME']);?>">
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("SPTEN_CODE")?>:</td>
		<td width="60%">
			<?if ($personType['CODE'] === 'CRM_COMPANY' || $personType['CODE'] === 'CRM_CONTACT'):?>
				<?=$personType['CODE'];?>
				<input type="hidden" name="CODE" size="30" maxlength="100" value="<?= htmlspecialcharsbx($personType['CODE']);?>">
			<?else:?>
				<input type="text" name="CODE" size="30" maxlength="100" value="<?= htmlspecialcharsbx($personType['CODE']) ?>">
			<?endif;?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SPTEN_SORT")?>:</td>
		<td>
			<input type="text" name="SORT" value="<?= intval($personType['SORT']) ?>">
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("SPTEN_XML_ID")?>:</td>
		<td width="60%">
			<input type="text" name="XML_ID" size="30" value="<?= $personType['XML_ID'] ? htmlspecialcharsbx($personType['XML_ID']): \Bitrix\Sale\PersonType::generateXmlId() ?>">
		</td>
	</tr>
	<?
		$dbRes = \Bitrix\Sale\Internals\BusinessValuePersonDomainTable::getList([
			'filter' => ['=PERSON_TYPE_ID' => $ID]
		]);

		$domain = '';
		$data = $dbRes->fetch();
		if (isset($data['DOMAIN']))
		{
			$domain = $data['DOMAIN'];
		}
	?>
	<tr>
		<td><?echo GetMessage("SPTEN_DOMAIN_P_TYPE")?>:</td>
		<td>
			<select name="BUSVAL_DOMAIN">
				<option value=""><?echo GetMessage("SPTEN_DOMAIN_P_TYPE_NONE")?></option>
				<option value="I" <?=($domain === 'I' ? 'selected' : '');?>><?echo GetMessage("SPTEN_DOMAIN_P_TYPE_I")?></option>
				<option value="E" <?=($domain === 'E' ? 'selected' : '');?>><?echo GetMessage("SPTEN_DOMAIN_P_TYPE_E")?></option>
			</select>
		</td>
	</tr>

<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled" => ($saleModulePermissions < "W"), "back_url" => $listUrl));
$tabControl->End();
?>

</form>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
