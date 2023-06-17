<?php
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\GroupHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

/** @global CMain $APPLICATION */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loader::includeModule('sale');

Loc::loadMessages(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = Helper::getListUrl();
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

if ($APPLICATION->GetGroupRight("sale") < "W")
{
	$APPLICATION->AuthForm(Loc::getMessage("SALE_MODULE_ACCES_DENIED"));
}

$userIsAdmin = $APPLICATION->GetGroupRight("sale") >= "W";
CSaleLocation::locationProCheckEnabled(); // temporal

#####################################
#### Data prepare
#####################################

$request = Context::getCurrent()->getRequest();

$nameToDisplay = '';
$returnUrl = trim((string)$request->get('return_url'));
$externalReturnUrl = $returnUrl !== '';
$actionFailureMessage = '';
$fatalFailureMessage = '';

$id = (int)$request->get('id');
if ($id <= 0)
{
	$id = false;
}
$copyId = (int)$request->get('copy_id');
if ($copyId <= 0)
{
	$copyId = false;
}
try
{
	$fatalFailure = false;

	#####################################
	#### ACTIONS
	#####################################

	$actionFailure = false;

	$adminSidePanelHelper->decodeUriComponent();

	$actionSave = $request->get('save') !== null;
	$actionApply = $request->get('apply') !== null;
	$actionSaveAndAdd = $request->get('save_and_add') !== null;
	$formSubmitted = ($actionSave || $actionApply || $actionSaveAndAdd) && check_bitrix_sessid();

	$element = $request->get('element');

	if (
		$userIsAdmin
		&& !empty($element)
		&& is_array($element)
		&& $formSubmitted
	) // form submitted, handling it
	{
		$saveAsId = (int)($element['ID'] ?? 0);

		global $DB;

		try
		{
			$DB->StartTransaction();

			$saveUrl = '';
			$applyUrl = '';

			if ($saveAsId) // existed, updating
			{
				$res = Helper::update($saveAsId, $element);
				if ($res['success']) // on successfull update ...
				{
					if ($actionSave)
					{
						$saveUrl = $returnUrl ?: $listUrl; // go to the page of just created item
					}
					elseif ($actionApply)
					{
						$applyUrl = $returnUrl ?: Helper::getEditUrl(['id' => $saveAsId]);
					}
					// $actionApply : do nothing
				}
			}
			else // new or copyed item
			{
				$res = Helper::add($element);
				if ($res['success']) // on successfull add ...
				{
					if ($actionSave)
					{
						$saveUrl = $returnUrl ?: $listUrl; // go to the list page
					}

					if ($actionApply)
					{
						$applyUrl = $returnUrl ?: Helper::getEditUrl(['id' => $res['id']]); // go to the page of just created item
					}
				}
			}

			// no matter we updated or added a new item - we go to blank page on $actionSaveAndAdd
			if ($res['success'] && $actionSaveAndAdd)
			{
				$applyUrl = Helper::getEditUrl(); // go to the blank page
			}

			// on failure just show sad message
			if (!$res['success'])
			{
				throw new Main\SystemException(implode('<br />', $res['errors']));
			}

			$DB->Commit();

			$baseId = ($saveAsId ?: $res['id']);
			$adminSidePanelHelper->sendSuccessResponse("base", ["element[ID]" => $baseId]);

			if ($saveUrl)
			{
				$adminSidePanelHelper->localRedirect($saveUrl);
				LocalRedirect($saveUrl);
			}
			elseif ($applyUrl)
			{
				$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
				LocalRedirect($applyUrl);
			}
			else
			{
				$adminSidePanelHelper->localRedirect($listUrl);
				LocalRedirect($listUrl);
			}
		}
		catch(Main\SystemException $e)
		{
			$actionFailure = true;

			$code = $e->getCode();
			$message = $e->getMessage() . (!empty($code) ? ' (' . $code . ')' : '');

			$actionFailureMessage =
				Loc::getMessage('SALE_LOCATION_E_CANNOT_' . ($saveAsId ? 'UPDATE' : 'SAVE') . '_ITEM')
				. ($message !== ''? ': <br /><br />'.$message : '')
			;

			$DB->Rollback();

			$adminSidePanelHelper->sendJsonErrorResponse($actionFailureMessage);
		}
	}

	if (!$returnUrl)
	{
		$returnUrl = Helper::getListUrl(); // default return page for "cancel" action
	}

	// read data to display
	$readAsId = $id ?: $copyId;

	if($formSubmitted && $actionFailure) // if form were submitted, but form action (add or update) failed
	{
		// load from request
		$formData = $element;

		if ($readAsId)
		{
			$nameToDisplay = Helper::getNameToDisplay($readAsId);
		}
	}
	else
	{
		if($readAsId)
		{
			// load from database
			$formData = Helper::getFormData($readAsId);

			$langU = mb_strtoupper(LANGUAGE_ID);
			$nameToDisplay = trim((string)($formData['NAME_'.$langU] ?? ''));
			if ($nameToDisplay === '')
			{
				$nameToDisplay = (string)($formData['CODE'] ?? '');
			}
		}
		else
		{
			// load blank form
			$formData = [];
		}
	}
}
catch(Main\SystemException $e)
{
	$fatalFailure = true;

	$code = $e->getCode();
	$fatalFailureMessage = $e->getMessage() . (!empty($code) ? ' (' . $code . ')' : '');
}

#####################################
#### PAGE INTERFACE GENERATION
#####################################

if ($nameToDisplay !== '')
{
	$pageTitle = Loc::getMessage(
		'SALE_LOCATION_E_ITEM_EDIT',
		[
			'#ITEM_NAME#' => $nameToDisplay,
		]
	);
}
else
{
	$pageTitle = Loc::getMessage('SALE_LOCATION_E_ITEM_NEW');
}
$APPLICATION->SetTitle($pageTitle);
unset($pageTitle);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


#####################################
#### Data output
#####################################


//temporal code
if(!CSaleLocation::locationProCheckEnabled())
{
	require($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_admin.php");
}

SearchHelper::checkIndexesValid();

if($fatalFailure):
	CAdminMessage::ShowMessage(['MESSAGE' => $fatalFailureMessage, 'type' => 'ERROR']);
else:
	if($actionFailure):
		CAdminMessage::ShowMessage(['MESSAGE' => $actionFailureMessage, 'type' => 'ERROR']);
	endif;

	$topMenu = new CAdminContextMenu([
		[
			'TEXT' => GetMessage('SALE_LOCATION_E_GO_BACK'),
			'LINK' => $adminSidePanelHelper->editUrlToPublicPage(Helper::getListUrl()),
			'ICON' => 'btn_list',
		]
	]);
	$topMenu->Show();

	$tabControl = new CAdminForm(
		'tabcntrl_location_group_edit',
		[
			[
				'DIV' => 'main',
				'TAB' => Loc::getMessage('SALE_LOCATION_E_MAIN_TAB'),
				'TITLE' =>  Loc::getMessage('SALE_LOCATION_E_MAIN_TAB_TITLE'),
			],
		]
	);
	$tabControl->BeginPrologContent();
	$tabControl->EndPrologContent();
	$tabControl->BeginEpilogContent();

	if ($externalReturnUrl):
		?>
		<input type="hidden" name="return_url" value="<?= htmlspecialcharsbx($returnUrl); ?>">
	<?php
	endif;
	?>
	<?= bitrix_sessid_post(); ?>
	<?php
	$tabControl->EndEpilogContent();

	$args = [];
	if ($id)
	{
		$args['id'] = $id;
	}
	$formActionUrl = Helper::getEditUrl($args); // generally, it is not safe to leave action empty
	$formActionUrl = $adminSidePanelHelper->setDefaultQueryParams($formActionUrl);
	$tabControl->Begin(array("FORM_ACTION" => $formActionUrl));
	$tabControl->BeginNextFormTab();

	$requiredFld = ' class="adm-detail-required-field"';

	$columns = Helper::getColumns('detail');
	foreach ($columns as $code => $field):
		$field['required'] ??= false;
		if ($code === 'ID' && !$id)
		{
			continue; // new node or copied
		}
		if (Helper::checkIsNameField($code))
		{
			continue;  // we`ll output names in a different manner
		}

		$value = Helper::makeSafeDisplay($formData[$code], $code);

		$tabControl->BeginCustomField($code, $field['title']);
			?>
			<tr<?=($field['required'] || $code == 'ID' ? $requiredFld : '')?>>
				<td width="40%"><?=$field['title']?>:</td>
				<td width="60%">
					<?php
					if($code == 'ID'):
						?>
						<?=$id?>
						<input type="hidden" name="element[<?=$code?>]" value="<?=$id?>" />
						<?php
					else:
						?>
						<input type="text" name="element[<?=$code?>]" value="<?=$value?>"<?= ($code === 'SORT' ? ' size="7"' : ''); ?> />
						<?php
					endif;
					?>
				</td>
			</tr>
		<?php
		$tabControl->EndCustomField($code, '');
	endforeach;

	$languages = Helper::getLanguageList();
	$nameMap = Helper::getNameMap();

	$tabControl->BeginCustomField('NAME', Loc::getMessage('SALE_LOCATION_E_HEADING_NAME_ALL'));
	foreach($languages as $langValue):
		?>
		<tr class="heading">
			<td colspan="2"><?=Loc::getMessage(
				'SALE_LOCATION_E_HEADING_NAME',
				[
					'#LANGUAGE_ID#' => htmlspecialcharsbx($langValue),
				]
			);
			?></td>
		</tr>
		<?php
		$langValue = mb_strtoupper($langValue);

		foreach($nameMap as $code => $field):
			$value = Helper::makeSafeDisplay($formData[$code.'_'.$langValue], $code);
			?>
			<tr<?=($field['required'] || $code == 'ID' ? $requiredFld : '')?>>
				<td width="40%"><?=$field['title']?></td>
				<td width="60%">
					<input type="text" name="element[<?=$code?>_<?=$langValue?>]" value="<?=$value?>" size="20" maxlength="255" />
				</td>
			</tr>
			<?php
		endforeach;
	endforeach;
	$tabControl->EndCustomField('NAME', '');

	//todo: fix this
	//$tabControl->AddSection("LOCATION", GetMessage("SALE_LOCATION_E_HEADING_LOCATION"));

	$tabControl->BeginCustomField('LOCATION', Loc::getMessage('SALE_LOCATION_E_HEADING_LOCATION'));?>

		<tr class="heading">
			<td colspan="2"><?=Loc::getMessage('SALE_LOCATION_E_HEADING_LOCATION')?></td>
		</tr>
		<tr>
			<td colspan="2">
				<?php
				$APPLICATION->IncludeComponent(
					'bitrix:sale.location.selector.system',
					'',
					[
						'ENTITY_PRIMARY' => $id,
						'LINK_ENTITY_NAME' => 'Bitrix\Sale\Location\GroupLocation',
						'INPUT_NAME' => 'element[LOC]',
						'SELECTED_IN_REQUEST' => [
							'L' => isset($element['LOC']['L']) ? explode(':', $element['LOC']['L']) : false
						],
					],
					false
				);
				?>
			</td>
		</tr>
	<?php
	$tabControl->EndCustomField('LOCATION', '');

	$tabControl->Buttons([
		'disabled' => !$userIsAdmin,
		'btnSaveAndAdd' => true,
		'btnApply' => true,
		'btnCancel' => true,
		'back_url' => $listUrl,
	]);

	$tabControl->Show();

endif;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
