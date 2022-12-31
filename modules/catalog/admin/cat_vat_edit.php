<?php
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use \Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/catalog/prolog.php');

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

CModule::IncludeModule("catalog");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl . 'cat_vat_admin.php?lang=' . LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

Loc::loadMessages(__FILE__);
$accessController = AccessController::getCurrent();
if (!($accessController->check(ActionDictionary::ACTION_CATALOG_READ) || $accessController->check(ActionDictionary::ACTION_VAT_EDIT)))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$bReadOnly = !$accessController->check(ActionDictionary::ACTION_VAT_EDIT);

$request = Context::getCurrent()->getRequest();
if ($request->isAjaxRequest())
{
	$request->addFilter(new Main\Web\PostDecodeFilter());
}

$errorMessage = '';
$bVarsFromForm = false;

$ID = (int)$request->get('ID');
if ($ID < 0)
{
	$ID = 0;
}

$defaultValues = [
	'ID' => 0,
	'NAME' => '',
	'SORT' => 100,
	'RATE' => '',
	'ACTIVE' => 'Y',
	'EXCLUDE_VAT' => 'N',
	'XML_ID' => '',
];

$fields = $defaultValues;
if ($ID > 0)
{
	$fields = Catalog\VatTable::getRowById($ID);
	if ($fields === null)
	{
		$ID = 0;
		$fields = $defaultValues;
	}
}

$excludeVatId = Catalog\VatTable::getExcludeVatId();

$formFields = [];
if (
	$request->isPost()
	&& $request->getPost('Update') === 'Y'
	&& !$bReadOnly
	&& check_bitrix_sessid()
)
{
	$currentAction = '';
	if ($request->getPost('apply') !== null)
	{
		$currentAction = 'apply';
	}
	elseif ($request->getPost('save') !== null)
	{
		$currentAction = 'save';
	}
	$saveAction = $currentAction === 'save';
	$applyAction = $currentAction === 'apply';

	if ($saveAction || $applyAction)
	{
		$value = $request->getPost('SORT');
		if (is_string($value))
		{
			$value = (int)$value;
			if ($value > 0)
			{
				$formFields['SORT'] = $value;
			}
		}

		$stringFields = [
			'NAME',
			'XML_ID',
			'ACTIVE',
		];
		foreach ($stringFields as $fieldName)
		{
			$value = $request->getPost($fieldName);
			if (is_string($value))
			{
				$formFields[$fieldName] = $value;
			}
		}
		unset($fieldName);

		if ($ID !== $excludeVatId)
		{
			$value = $request->getPost('EXCLUDE_VAT');
			if (is_string($value))
			{
				$formFields['EXCLUDE_VAT'] = $value;
			}
			$value = $request->getPost('RATE');
			if (is_string($value))
			{
				$value = (float)$value;
				if ($value >= 0)
				{
					$formFields['RATE'] = $value;
				}
			}
		}
		else
		{
			$formFields['EXCLUDE_VAT'] = 'Y';
		}
		unset($value);

		if (!empty($formFields))
		{
			$conn = Main\Application::getConnection();
			$conn->startTransaction();
			if ($ID > 0)
			{
				$result = Catalog\Model\Vat::update($ID, $formFields);
			}
			else
			{
				$result = Catalog\Model\Vat::add($formFields);
				if ($result->isSuccess())
				{
					$ID = (int)$result->getId();
				}
			}
			if ($result->isSuccess())
			{
				$conn->commitTransaction();
				$adminSidePanelHelper->sendSuccessResponse(
					'base',
					[
						'ID' => $ID,
					]
				);
				if ($applyAction)
				{
					$applyUrl = $selfFolderUrl . 'cat_vat_edit.php?lang=' . LANGUAGE_ID . '&ID=' . $ID;
					$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
					LocalRedirect($applyUrl);
				}
				else
				{
					$adminSidePanelHelper->localRedirect($listUrl);
					LocalRedirect($listUrl);
				}
			}
			else
			{
				$conn->rollbackTransaction();
				$bVarsFromForm = true;

				$fields = array_merge(
					$fields,
					$formFields
				);

				$errorMessage = implode(' ', $result->getErrorMessages());
				if ($errorMessage === '')
				{
					$errorMessage = $ID > 0
						? Loc::getMessage(
							'CVAT_ERR_UPDATE',
							[
								'#ID#' => $ID,
							]
						)
						: Loc::getMessage('CVAT_ERR_ADD')
					;
				}

				$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
			}
		}
	}
}

if ($ID > 0)
{
	$APPLICATION->SetTitle(Loc::getMessage(
		'CVAT_TITLE_UPDATE',
		[
			'#ID#' => $ID,
		]
	));
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage('CVAT_TITLE_ADD'));
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

$isExcludeVat = $fields['EXCLUDE_VAT'] === 'Y';

$aMenu = [
	[
		'TEXT' => Loc::getMessage('CVAT_LIST'),
		'ICON' => 'btn_list',
		'LINK' => $listUrl,
	]
];

if ($ID > 0 && !$bReadOnly)
{
	$aMenu[] = [
		'SEPARATOR' => 'Y',
	];
	$addUrl = $selfFolderUrl . 'cat_vat_edit.php?lang=' . LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aMenu[] = [
		'TEXT' => Loc::getMessage('CVAT_NEW'),
		'ICON' => 'btn_new',
		'LINK' => $addUrl,
	];

	$deleteUrl = $selfFolderUrl . 'cat_vat_admin.php?action=delete&ID[]=' . $ID
		. '&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get() . '#tb'
	;
	$buttonAction = 'LINK';
	if ($adminSidePanelHelper->isPublicFrame())
	{
		$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
		$buttonAction = 'ONCLICK';
	}

	$aMenu[] = [
		'TEXT' => Loc::getMessage('CVAT_DELETE'),
		'ICON' => 'btn_delete',
		$buttonAction => "javascript:if(confirm('"
			. CUtil::JSEscape(Loc::getMessage('CVAT_DELETE_CONFIRM'))
			. "')) top.window.location.href='"
			. $deleteUrl
			. "';",
		'WARNING' => 'Y',
	];
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($errorMessage !== '')
{
	CAdminMessage::ShowMessage($errorMessage);
}

$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
?>
<form method="POST" action="<?=$actionUrl?>" name="vat_edit">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID; ?>">
<input type="hidden" name="ID" value="<?=$ID; ?>">
<?=bitrix_sessid_post()?>
<?php
$aTabs = [];
$aTabs[] = [
	'DIV' => 'edit1',
	'TAB' => Loc::getMessage('CVAT_TAB'),
	'ICON' => 'catalog',
	'TITLE' => Loc::getMessage('CVAT_TAB_DESCR'),
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
	if ($ID > 0):
	?>
		<tr>
			<td style="width: 40%;">ID:</td>
			<td style="width: 60%;"><?= $ID ?></td>
		</tr>
	<?php
	endif;
	?>
	<tr class="adm-detail-required-field">
		<td style="width: 40%;"><?= Loc::getMessage("CVAT_NAME") ?>:</td>
		<td style="width: 60%;">
			<input type="text" name="NAME" value="<?= htmlspecialcharsbx($fields['NAME']); ?>" size="30" <?=($bReadOnly) ? " disabled" : ""?>>
		</td>
	</tr>
	<tr>
		<td style="width: 40%;"><?= Loc::getMessage("CVAT_EDIT_FORM_FIELD_XML_ID") ?></td>
		<td style="width: 60%;">
			<input type="text" name="XML_ID" maxlength="255" value="<?= htmlspecialcharsbx($fields['XML_ID']); ?>" size="50" <?=($bReadOnly) ? " disabled" : ""?>>
		</td>
	</tr>
	<tr>
		<td style="width: 40%;"><?= Loc::getMessage("CVAT_ACTIVE") ?>:</td>
		<td style="width: 60%;">
			<input type="hidden" name="ACTIVE" value="N">
			<input type="checkbox" name="ACTIVE" value="Y"<?= ($fields['ACTIVE'] === 'Y' ? ' checked' : ''); ?> <?=($bReadOnly) ? " disabled" : ""?>>
		</td>
	</tr>
	<?php
	$changeExcludeVat = $excludeVatId === null;
	if ($changeExcludeVat):
	?>
		<td style="width: 40%;"><?= Loc::getMessage('CVAT_EDIT_FORM_FIELD_EXCLUDE_VAT'); ?></td>
		<td style="width: 60%;">
			<input type="hidden" name="EXCLUDE_VAT" value="N">
			<input type="checkbox" id="EXCLUDE_VAT" name="EXCLUDE_VAT" value="Y"<?= ($isExcludeVat ? ' checked' : ''); ?> <?=($bReadOnly) ? " disabled" : ""?>><br>
		</td>
	<?php
	endif;
	$displayRate = ($changeExcludeVat && $isExcludeVat ? 'none' : 'table-row');
	?>
	<tr class="adm-detail-required-field" id="tr_RATE" style="display: <?= $displayRate; ?>;">
		<td style="width: 40%;"><?= Loc::getMessage("CVAT_RATE") ?>:</td>
		<td style="width: 60%;"><?php
			if ($isExcludeVat):
			?>
				<?= Loc::getMessage('CVAT_EDIT_FORM_MESS_EXCLUDE_VAT'); ?>
			<?php
			else:
			?>
				<input type="text" name="RATE" value="<?=htmlspecialcharsbx((string)$fields['RATE']); ?>" size="10" <?=($bReadOnly) ? " disabled" : ""?>>&nbsp;%
			<?php
			endif;
			?>
		</td>
	</tr>
	<tr>
		<td style="width: 40%;"><?= Loc::getMessage("CVAT_SORT") ?>:</td>
		<td style="width: 60%;">
			<input type="text" name="C_SORT" value="<?=htmlspecialcharsbx($fields['SORT']); ?>" size="5" <?=($bReadOnly) ? " disabled" : ""?>>
		</td>
	</tr>
<?php
$tabControl->EndTab();

if (!$bReadOnly)
{
	$tabControl->Buttons([
		'disabled' => $bReadOnly,
		'back_url' => $listUrl,
	]);
}

$tabControl->End();
?>
</form>
<?php
if ($changeExcludeVat):
?>
<script>
	function handlerClickExcludeVat()
	{
		let excludeVat = BX('EXCLUDE_VAT'),
			rateBlock = BX('tr_RATE');

		if (BX.type.isElementNode(excludeVat) && BX.type.isElementNode(rateBlock))
		{
			rateBlock.style.display = excludeVat.checked ? 'none' : 'table-row';
		}
	}
	BX.ready(function(){
		let excludeVat = BX('EXCLUDE_VAT');
		if (BX.type.isElementNode(excludeVat))
		{
			BX.bind(excludeVat, 'click', handlerClickExcludeVat);
		}
	});
</script>
<?php
endif;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
