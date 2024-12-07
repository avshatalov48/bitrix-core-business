<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UI\FileInput;
use Bitrix\Main\Web;
use Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $APPLICATION;
global $DB;
global $USER_FIELD_MANAGER;

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."cat_store_list.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$currentUser = CurrentUser::get();

$request = Context::getCurrent()->getRequest();

$id = (int)($request->get('ID') ?? 0);
if ($id < 0)
{
	$id = 0;
}


Loader::includeModule("catalog");
$accessController = AccessController::getCurrent();
if (
	!(
		$accessController->check(ActionDictionary::ACTION_CATALOG_READ)
		|| $accessController->checkByValue(ActionDictionary::ACTION_STORE_MODIFY, $id)
	)
)
{
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$isCloud = ModuleManager::isModuleInstalled('bitrix24');

$canModify = AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_MODIFY);

if (!Catalog\Config\Feature::isMultiStoresEnabled())
{
	if (Catalog\Config\State::isExceededStoreLimit())
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		ShowError(Loc::getMessage("CAT_FEATURE_NOT_ALLOW"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}

$defaultValues = [
	'ID' => 0,
	'TITLE' => '',
	'ACTIVE' => 'Y',
	'ADDRESS' => '',
	'DESCRIPTION' => '',
	'GPS_N' => '',
	'GPS_S' => '',
	'IMAGE_ID' => '',
	'LOCATION_ID' => '',
	'DATE_MODIFY' => '',
	'DATE_CREATE' => '',
	'USER_ID' => 0,
	'MODIFIED_BY' => 0,
	'PHONE' => '',
	'SCHEDULE' => '',
	'XML_ID' => '',
	'SORT' => 100,
	'EMAIL' => '',
	'ISSUING_CENTER' => 'Y',
	'SHIPPING_CENTER' => 'N',
	'SITE_ID' => '',
	'CODE' => '',
	'IS_DEFAULT' => Catalog\StoreTable::getDefaultStoreId() === null ? 'Y' : 'N',
];

$fields = $defaultValues;
if ($id > 0)
{
	$fields = Catalog\StoreTable::getRowById($id);
	if ($fields === null)
	{
		$id = 0;
		$fields = $defaultValues;
	}
}

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => Loc::getMessage('STORE_TAB_COMMON'),
		'ICON' => 'catalog',
		'TITLE' => Loc::getMessage('STORE_TAB_COMMON_DESCR')
	],
	[
		'DIV' => 'edit2',
		'TAB' => Loc::getMessage('STORE_TAB_UF'),
		'ICON' => 'catalog',
		'TITLE' => Loc::getMessage('STORE_TAB_UF_DESCR'),
	],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$errorMessage = '';
$bVarsFromForm = false;

$userId = (int)$currentUser->getId();

$entityId = Catalog\StoreTable::getUfId();

if (
	$request->isPost()
	&& $request->getPost('Update') === 'Y'
	&& $canModify
	&& check_bitrix_sessid()
)
{
	$currentAction = '';
	if ($request->getPost('save') !== null)
	{
		$currentAction = 'save';
	}
	elseif ($request->getPost('apply') !== null)
	{
		$currentAction = 'apply';
	}
	$saveAction = $currentAction === 'save';
	$applyAction = $currentAction === 'apply';

	if ($saveAction || $applyAction)
	{
		$postFields = [
			'MODIFIED_BY' => $userId,
		];

		$stringList = [
			'TITLE',
			'ACTIVE',
			'ADDRESS',
			'DESCRIPTION',
			'PHONE',
			'SCHEDULE',
			'XML_ID',
			'EMAIL',
			'ISSUING_CENTER',
			'CODE',
		];
		if (CCatalogStoreControlUtil::isAllowShowShippingCenter())
		{
			$stringList[] = 'SHIPPING_CENTER';
		}
		if (!$isCloud)
		{
			$stringList[] = 'SITE_ID';
		}
		foreach ($stringList as $fieldId)
		{
			$value = $request->getPost($fieldId);
			if (is_string($value))
			{
				$postFields[$fieldId] = $value;
			}
		}
		if ($isCloud)
		{
			$postFields['SITE_ID'] = '';
		}

		$coordinateList = [
			'GPS_N',
			'GPS_S',
		];
		foreach ($coordinateList as $fieldId)
		{
			$value = $request->getPost($fieldId);
			if (is_string($value))
			{
				$postFields[$fieldId] = str_replace(',', '.', $value);
			}
		}

		$numberList = [
			'SORT',
		];
		foreach ($numberList as $fieldId)
		{
			$value = $request->getPost($fieldId);
			if (is_string($value))
			{
				$value = (int)$value;
				if ($value > 0)
				{
					$postFields[$fieldId] = $value;
				}
			}
		}

		$postFields['IMAGE_ID'] = CIBlock::makeFileArray(
			$request->getPost('IMAGE_ID'),
			$request->getPost('IMAGE_ID_del') === 'Y'
		);

		$USER_FIELD_MANAGER->EditFormAddFields($entityId, $postFields);

		$conn = Application::getConnection();
		$conn->startTransaction();

		if ($id > 0)
		{
			$res = CCatalogStore::Update($id, $postFields);
		}
		else
		{
			$postFields['USER_ID'] = $userId;
			$res = CCatalogStore::Add($postFields);
			if ($res)
			{
				$id = (int)$res;
			}
		}
		if (!$res)
		{
			if ($ex = $APPLICATION->GetException())
			{
				$errorMessage .= $ex->GetString() . "<br>";
			}
			else
			{
				$errorMessage .= Loc::getMessage('STORE_SAVE_ERROR') . '<br>';
			}
		}
		else
		{
			$ufUpdated = $USER_FIELD_MANAGER->Update($entityId, $id, $postFields);
		}

		if ($errorMessage == '')
		{
			$conn->commitTransaction();

			if ($saveAction)
			{
				$adminSidePanelHelper->sendSuccessResponse("base", ["ID" => $id]);
				$adminSidePanelHelper->localRedirect($listUrl);
				LocalRedirect($listUrl);
			}
			if ($applyAction)
			{
				$applyUrl = $selfFolderUrl . "cat_store_edit.php?lang=" . LANGUAGE_ID . "&ID=" . $id
					. '&' . $tabControl->ActiveTabParam()
				;
				$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
				$adminSidePanelHelper->sendSuccessResponse("apply", ["reloadUrl" => $applyUrl]);
				LocalRedirect($applyUrl);
			}
		}
		else
		{
			$bVarsFromForm = true;
			$conn->rollbackTransaction();
			$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
			$fields = $postFields;
		}
	}
}

if ($id > 0)
{
	$APPLICATION->SetTitle(str_replace("#ID#", $id, Loc::getMessage("STORE_TITLE_UPDATE")));
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage("STORE_TITLE_ADD"));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("STORE_LIST"),
		"ICON" => "btn_list",
		"LINK" => $listUrl
	)
);

if ($id > 0 && $canModify)
{
	$aMenu[] = ["SEPARATOR" => "Y"];

	if (Catalog\Config\State::isAllowedNewStore())
	{
		$aMenu[] = [
			"TEXT" => Loc::getMessage("STORE_NEW"),
			"ICON" => "btn_new",
			"LINK" => $adminSidePanelHelper->editUrlToPublicPage(
				$selfFolderUrl . "cat_store_edit.php?lang=" . LANGUAGE_ID
			),
		];
	}
	else
	{
		$helpLink = Catalog\Config\Feature::getMultiStoresHelpLink();
		if (!empty($helpLink))
		{
			$aMenu[] = [
				"TEXT" => Loc::getMessage("STORE_NEW"),
				"ICON" => "btn_lock",
				$helpLink['TYPE'] => $helpLink['LINK'],
			];
		}
		unset($helpLink);
	}
	if ($fields['IS_DEFAULT'] !== 'Y')
	{
		$deleteUrl = $selfFolderUrl
			. "cat_store_list.php?action=delete&ID[]=" . $id
			. "&lang=" . LANGUAGE_ID
			. "&" . bitrix_sessid_get() . "#tb"
		;
		if ($adminSidePanelHelper->isPublicFrame())
		{
			$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
		}
		$aMenu[] = [
			"TEXT" => Loc::getMessage("STORE_DELETE"),
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('"
				. CUtil::JSEscape(Loc::getMessage("STORE_DELETE_CONFIRM"))
				. "')) top.window.location='"
				. $deleteUrl
				. "';",
			"WARNING" => "Y"
		];
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
$arSitesShop = array();
$arSitesTmp = array();
$rsSites = CSite::GetList("id", "asc", Array("ACTIVE" => "Y"));
while($arSite = $rsSites->GetNext())
{
	$site = COption::GetOptionString("sale", "SHOP_SITE_".$arSite["ID"], "");
	if ($arSite["ID"] == $site)
	{
		$arSitesShop[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
	}
	$arSitesTmp[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
}

if (empty($arSitesShop))
{
	$arSitesShop = $arSitesTmp;
}

$defaultStore = ($id > 0 && $fields['IS_DEFAULT'] === 'Y');
if ($errorMessage !== '')
{
	CAdminMessage::ShowMessage($errorMessage);
}

$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);

$userFieldUrl = $selfFolderUrl."userfield_edit.php?lang=".LANGUAGE_ID."&ENTITY_ID=".$entityId;
$userFieldUrl = $adminSidePanelHelper->editUrlToPublicPage($userFieldUrl);
$userFieldUrl .= "&back_url=".urlencode($APPLICATION->GetCurPageParam('', array('bxpublic'))."&tabControl_active_tab=user_fields_tab");
?>
<form enctype="multipart/form-data" method="POST" action="<?=$actionUrl?>" name="store_edit">
	<?php
	echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?php
	echo LANGUAGE_ID; ?>">
	<input type="hidden" name="ID" value="<?php
	echo $id ?>">
	<?=bitrix_sessid_post()?><?php
	$tabControl->Begin();

	$tabControl->BeginNextTab();
	if ($id > 0): ?>
	<tr>
		<td>ID:</td>
		<td><?= $id ?></td>
	</tr>
	<?php
	endif;
	if ($id > 0): ?>
	<tr>
		<td style="width: 40%;"><?= Loc::getMessage("STORE_FIELD_IS_DEFAULT") ?>:</td>
		<td>
			<?php echo ($defaultStore ? Loc::getMessage('STORE_MESS_YES') : Loc::getMessage('STORE_MESS_NO')); ?>
		</td>
	</tr>
	<?php
	endif;
	?>
	<tr>
		<td width="40%"><?= Loc::getMessage("STORE_ACTIVE") ?>:</td>
		<td width="60%"><?php
			if ($defaultStore):
				echo ($fields['ACTIVE'] === 'Y' ? Loc::getMessage('STORE_MESS_YES') : Loc::getMessage('STORE_MESS_NO'));
				?>
				<input type="hidden" name="ACTIVE" value="Y">
				<?php
			else:
				?>
				<input type="hidden" name="ACTIVE" value="N">
				<input type="checkbox" name="ACTIVE" value="Y"<?=($fields['ACTIVE'] === 'Y' ? ' checked' : ''); ?>>
				<?php
			endif;
			?>
		</td>
	</tr>
	<tr>
		<td width="40%"><?= Loc::getMessage("ISSUING_CENTER") ?>:</td>
		<td width="60%">
			<input type="hidden" name="ISSUING_CENTER" value="N">
			<input type="checkbox" name="ISSUING_CENTER" value="Y"<?=($fields['ISSUING_CENTER'] === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<?php
	if (CCatalogStoreControlUtil::isAllowShowShippingCenter()):
	?>
	<tr>
		<td width="40%"><?= Loc::getMessage("SHIPPING_CENTER") ?>:</td>
		<td width="60%">
			<input type="hidden" name="SHIPPING_CENTER" value="N">
			<input type="checkbox" name="SHIPPING_CENTER" value="Y"<?=($fields['SHIPPING_CENTER'] === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<?php
	endif;
	if ($isCloud):
		if ((string)$fields['SITE_ID'] !== ''):
		?>
		<tr>
			<td><?= Loc::getMessage("STORE_SITE_ID") ?>:</td>
			<td><?php
				echo Loc::getMessage('STORE_ERR_DEFAULT_STORE_WITH_SITE');
				?><input name="SITE_ID" value="">
			</td>
		</tr>
		<?php
		endif;
	else:
		if (!$defaultStore || (string)$fields['SITE_ID'] !== ''):
		?>
		<tr>
			<td><?= Loc::getMessage("STORE_SITE_ID") ?>:</td>
			<td><?php
				if (!$defaultStore):
				?>
				<select id="SITE_ID" style="max-width: 300px; width: 300px;" name="SITE_ID">
				<option value=""><?=Loc::getMessage("STORE_FOR_ALL_SITES")?></option>
				<?php
				foreach($arSitesShop as $key => $val)
				{
					$selected = ($val['ID'] === $fields['SITE_ID']) ? 'selected' : '';
					echo "<option ".$selected." value=".htmlspecialcharsbx($val['ID']).">".htmlspecialcharsbx($val["NAME"]." (".$val["ID"].")")."</option>";
				}
				?>
				</select>
				<?php
				else:
					echo Loc::getMessage('STORE_ERR_DEFAULT_STORE_WITH_SITE');
					?>
					<input name="SITE_ID" value="">
					<?php
				endif;
				?>
			</td>
		</tr>
		<?php
		endif;
	endif;
	?>
	<tr>
		<td><?= Loc::getMessage("STORE_TITLE") ?>:</td>
		<td>
			<input type="text" style="width:300px" name="TITLE" value="<?=htmlspecialcharsbx((string)$fields['TITLE']);?>">
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("STORE_CODE") ?>:</td>
		<td>
			<input type="text" style="width:300px" name="CODE" value="<?=htmlspecialcharsbx((string)$fields['CODE']);?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?= Loc::getMessage("STORE_ADDRESS") ?>:</td>
		<td>
			<textarea cols="35" rows="3" class="typearea" name="ADDRESS"><?=htmlspecialcharsEx((string)$fields['ADDRESS']); ?></textarea>
		</td>
	</tr>
	<tr>
		<td  class="adm-detail-valign-top"><?= Loc::getMessage("STORE_DESCR") ?>:</td>
		<td>
			<textarea cols="35" rows="3" class="typearea" name="DESCRIPTION"><?=htmlspecialcharsEx((string)$fields['DESCRIPTION']); ?></textarea>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("STORE_PHONE") ?>:</td>
		<td>
			<input type="text" style="width:300px" name="PHONE" value="<?=htmlspecialcharsbx($fields['PHONE']); ?>">
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("STORE_SCHEDULE") ?>:</td>
		<td>
			<input type="text" style="width:300px" name="SCHEDULE" value="<?=htmlspecialcharsbx($fields['SCHEDULE']); ?>">
		</td>
	</tr>
	<tr>
		<td><?= "Email" ?>:</td>
		<td>
			<input type="text" style="width:300px" name="EMAIL" value="<?=htmlspecialcharsbx($fields['EMAIL']); ?>">
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("STORE_GPS_N") ?>:</td>
		<td><input type="text" name="GPS_N" value="<?=htmlspecialcharsbx($fields['GPS_N']); ?>" size="15">
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("STORE_GPS_S") ?>:</td>
		<td><input type="text" name="GPS_S" value="<?=htmlspecialcharsbx($fields['GPS_S']); ?>" size="15">
		</td>

	</tr>
	<tr>
		<td><?= Loc::getMessage("STORE_XML_ID") ?>:</td>
		<td><input type="text" name="XML_ID" value="<?=htmlspecialcharsbx($fields['XML_ID']); ?>">
		</td>
	</tr>
	<tr>
		<td width="40%"><?= Loc::getMessage("CSTORE_SORT") ?>:</td>
		<td width="60%">
			<input type="text" name="SORT" value="<?=htmlspecialcharsbx($fields['SORT']); ?>" size="5">
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage("STORE_IMAGE")?>:</td>
		<td>
			<?php
			$fileConfig = [
				'name' => 'IMAGE_ID',
				'description' => false,
				'allowUpload' => FileInput::UPLOAD_IMAGES,
				'allowUploadExt' => '',
				'maxCount' => 1,
				'upload' => $canModify,
				'medialib' => false,
				'fileDialog' => $canModify,
				'cloud' => false,
				'delete' => $canModify,
			];

			$fileInput = FileInput::createInstance($fileConfig);
			$showFiles = ['IMAGE_ID' => $fields['IMAGE_ID']];

			echo $fileInput->show($showFiles, $bVarsFromForm);
			?>
		</td>
	</tr>
	<?php
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td align="left" colspan="2">
			<a href="<?=$userFieldUrl?>"><?=Loc::getMessage("STORE_USER_FIELDS_ADD");?></a>
		</td>
	</tr>
	<?php
		$arUserFields = $USER_FIELD_MANAGER->GetUserFields($entityId, $id, LANGUAGE_ID);
		foreach($arUserFields as $FIELD_NAME => $arUserField)
		{
			$arUserField["VALUE_ID"] = $id;
			$strLabel = $arUserField["EDIT_FORM_LABEL"]?: $arUserField["FIELD_NAME"];
			$arUserField["EDIT_FORM_LABEL"] = $strLabel;

			$form_value = $GLOBALS[$FIELD_NAME] ?? null;

			echo $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $form_value, $arUserField);
		}

	$tabControl->EndTab();
	$tabControl->Buttons([
		"disabled" => !$canModify,
		"back_url" => $listUrl
	]);
	$tabControl->End();
	?>
</form>
<?php
Catalog\Config\Feature::initUiHelpScope();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
