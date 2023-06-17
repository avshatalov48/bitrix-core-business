<?php
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\ExternalServiceHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

/** @global CMain $APPLICATION */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule('sale');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

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

$returnUrl = trim((string)$request->get('return_url'));
$externalReturnUrl = $returnUrl !== '';
$nameToDisplay = '';
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
		$redirectUrl = false;

		try
		{
			$DB->StartTransaction();

			if ($saveAsId) // existed, updating
			{

				$res = Helper::update($saveAsId, $element);

				if ($res['success']) // on successfull update ...
				{
					if ($actionSave)
					{
						$redirectUrl = $returnUrl ?: Helper::getListUrl(); // go to the page of just created item
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
						$redirectUrl = $returnUrl ?: Helper::getListUrl(); // go to the list page
					}

					if ($actionApply)
					{
						$redirectUrl = $returnUrl ?: Helper::getEditUrl(['id' => $res['id']]); // go to the page of just created item
					}
				}
			}

			// no matter we updated or added a new item - we go to blank page on $actionSaveAndAdd
			if ($res['success'] && $actionSaveAndAdd)
			{
				$redirectUrl = Helper::getEditUrl(); // go to the blank page
			}

			// on failure just show sad message
			if (!$res['success'])
			{
				throw new Main\SystemException(implode('<br />', $res['errors']));
			}

			$DB->Commit();

			if ($redirectUrl)
			{
				LocalRedirect($redirectUrl);
			}
		}
		catch(Main\SystemException $e)
		{
			$actionFailure = true;

			$code = $e->getCode();
			$message = $e->getMessage() . (!empty($code) ? ' (' . $code . ')' : '');

			$actionFailureMessage =
				Loc::getMessage('SALE_LOCATION_E_CANNOT_' . ($saveAsId ? 'UPDATE' : 'SAVE') . '_ITEM')
				. ($message <> '' ? ': <br /><br />' . $message : '')
			;

			$DB->Rollback();
		}
	}

	if (!$returnUrl)
	{
		$returnUrl = Helper::getListUrl(); // default return page for "cancel" action
	}

	// read data to display
	$readAsId = $id ?: $copyId;

	if ($formSubmitted && $actionFailure) // if form were submitted, but form action (add or update) failed
	{
		// load from request
		$formData = $element;

		if ($readAsId)
		{
			$nameToDisplay = (string)$readAsId;
		}
	}
	else
	{
		if ($readAsId)
		{
			// load from database
			$formData = Helper::getFormData($readAsId);
			$nameToDisplay = (string)$readAsId;
		}
		else
		{
			// load blank form, optionally with parent id filled up
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
			'#ITEM_NAME#' => '#'.htmlspecialcharsbx($nameToDisplay),
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
if (!CSaleLocation::locationProCheckEnabled())
{
	require($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_admin.php");
}

SearchHelper::checkIndexesValid();

if ($fatalFailure):
	CAdminMessage::ShowMessage([
		'MESSAGE' => $fatalFailureMessage,
		'type' => 'ERROR',
	]);
else:
	if ($actionFailure):
		CAdminMessage::ShowMessage([
			'MESSAGE' => $actionFailureMessage,
			'type' => 'ERROR',
		]);
	endif;

	$topMenu = new CAdminContextMenu([
		[
			'TEXT' => GetMessage('SALE_LOCATION_E_GO_BACK'),
			'LINK' => Helper::getListUrl([]),
			'ICON' => 'btn_list',
		]
	]);
	$topMenu->Show();

	$tabControl = new CAdminForm(
		'tabcntrl_external_service_edit',
		[
			[
				'DIV' => 'main',
				'TAB' => Loc::getMessage('SALE_LOCATION_E_MAIN_TAB'),
				'TITLE' =>  Loc::getMessage('SALE_LOCATION_E_MAIN_TAB_TITLE')
			]
		]
	);
	$tabControl->BeginPrologContent();
	$tabControl->EndPrologContent();
	$tabControl->BeginEpilogContent();

	if ($externalReturnUrl):
		?>
		<input type="hidden" name="return_url" value="<?= htmlspecialcharsbx($returnUrl) ?>">
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

	$tabControl->Begin([
		'FORM_ACTION' => Helper::getEditUrl($args), // generally, it is not safe to leave action empty
	]);
	$tabControl->BeginNextFormTab();

	$requiredFld = ' class="adm-detail-required-field"';

	$columns = Helper::getColumns('detail');
	foreach($columns as $code => $field):
		$field['required'] ??= false;
		if ($code == 'ID' && !$id)
		{
			continue; // new node or copied
		}

		$value = Helper::makeSafeDisplay($formData[$code], $code);

		$tabControl->BeginCustomField($code, $field['title']);
			?>
			<tr<?=($field['required'] || $code == 'ID' ? $requiredFld : '')?>>
				<td width="40%"><?=$field['title']?>:</td>
				<td width="60%">
					<?php
					if($code === 'ID'):
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

	$tabControl->Buttons([
		"disabled" => !$userIsAdmin,
		"btnSaveAndAdd" => true,
		"btnApply" => true,
		"btnCancel" => true,
		"back_url" => $returnUrl,
	]);

	$tabControl->Show();
endif;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
