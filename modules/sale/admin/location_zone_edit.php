<?php

/** @global CMain $APPLICATION */
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Admin\SiteLocationHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule('sale');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight("sale") < "W")
{
	$APPLICATION->AuthForm(Loc::getMessage("SALE_MODULE_ACCES_DENIED"));
}

$userIsAdmin = $APPLICATION->GetGroupRight("sale") >= "W";

$request = Context::getCurrent()->getRequest();
$actionFailureMessage = '';
$fatalFailureMessage = '';

$id = (string)($request->get('id'));
if ($id !== '')
{
	$id = Helper::tryParseSiteId($id);
}
else
{
	$id = false;
}

#####################################
#### Data prepare
#####################################

$returnUrl = trim((string)$request->get('return_url'));
$externalReturnUrl = $returnUrl !== '';
$nameToDisplay = '';

try
{
	$fatalFailure = false;

	#####################################
	#### ACTIONS: update
	#####################################

	$actionFailure = false;

	$actionSave = $request->get('save') !== null;
	$actionApply = $request->get('apply') !== null;
	$formSubmitted = ($actionSave || $actionApply) && check_bitrix_sessid();

	$element = $request->get('element');

	if(
		$userIsAdmin
		&& !empty($element)
		&& is_array($element)
		&& $formSubmitted
	) // form submitted, handling it
	{
		$saveAsId = Helper::tryParseSiteId((string)($element['ID'] ?? ''));

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
			else
			{
				throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_E_ITEM_NOT_FOUND'));
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
				Loc::getMessage('SALE_LOCATION_E_CANNOT_UPDATE_ITEM')
				. ($message !== '' ? ': <br /><br />' . $message : '')
			;

			$DB->Rollback();
		}
	}

	if (!$returnUrl)
	{
		$returnUrl = Helper::getListUrl(); // default return page for "cancel" action
	}

	#####################################
	#### READ FORM DATA
	#####################################

	if($formSubmitted && $actionFailure) // if form were submitted, but form action (add or update) failed
	{
		// load from request
		$formData = $element;

		if ($id)
		{
			$nameToDisplay = Helper::getNameToDisplay($id);
		}

		// cleaning up empty external data
		if (!empty($formData['LOCATION']) && is_array($formData['LOCATION']))
		{
			foreach ($formData['LOCATION'] as $lId => $external)
			{
				if(!intval($external['LOCATION_ID']))
					unset($formData['LOCATION'][$lId]);
			}
		}
	}
	else
	{
		if($id)
		{
			// load from database
			$formData = Helper::getFormData($id);
			$nameToDisplay = $formData['SITE_NAME'];
		}
		else
		{
			// blank page is not allowed here
			throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_E_ITEM_NOT_FOUND'));
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
			'#ITEM_NAME#' => htmlspecialcharsbx($nameToDisplay),
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
			"TEXT" => GetMessage("SALE_LOCATION_E_GO_BACK"),
			"LINK" => Helper::getListUrl(),
			"ICON" => "btn_list",
		]
	]);
	$topMenu->Show();

	$tabControl = new CAdminForm(
		"tabcntrl_zone_edit",
		[
			[
				"DIV" => "main",
				"TAB" => Loc::getMessage('SALE_LOCATION_E_MAIN_TAB'),
				"TITLE" =>  Loc::getMessage('SALE_LOCATION_E_MAIN_TAB_TITLE'),
			],
		],
		true,
		true
	);
	$tabControl->SetShowSettings(false);
	$tabControl->BeginPrologContent();
	$tabControl->EndPrologContent();
	$tabControl->BeginEpilogContent();


	if ($externalReturnUrl):
		?>
		<input type="hidden" name="return_url" value="<?= htmlspecialcharsbx($returnUrl); ?>">
	<?php
	endif;
	?>
	<?=bitrix_sessid_post(); ?>
	<?php
	$tabControl->EndEpilogContent();

	$args = [];
	if ($id)
	{
		$args['id'] = $id;
	}

	$tabControl->Begin([
		"FORM_ACTION" => Helper::getEditUrl($args) // generally, it is not safe to leave action empty
	]);
	$tabControl->BeginNextFormTab();

	$tabControl->BeginCustomField('LOCATIONS', Loc::getMessage('SALE_LOCATION_E_HEADING_LOCATIONS'));?>

			<tr class="heading">
				<td colspan="2">
					<?=GetMessage('SALE_LOCATION_E_FLD_LOCATIONS')?>
				</td>
			</tr>
		<tr>
			<td>
				<input type="hidden" name="element[ID]" value="<?=$id?>" />
				<?php
				$APPLICATION->IncludeComponent(
					"bitrix:sale.location.selector.system",
					"",
					[
						"ENTITY_PRIMARY" => $id,
						"LINK_ENTITY_NAME" => "Bitrix\Sale\Location\SiteLocation",
						"INPUT_NAME" => 'element[LOC]',
						"SELECTED_IN_REQUEST" => [
							'L' => isset($element['LOC']['L']) ? explode(':', $element['LOC']['L']) : false,
							'G' => isset($element['LOC']['G']) ? explode(':', $element['LOC']['G']) : false
						],
					],
					false
				);?>

			</td>
		</tr>
	<?php
	$tabControl->EndCustomField('LOCATIONS', '');

	$tabControl->Buttons([
		"disabled" => !$userIsAdmin,
		"btnApply" => true,
		"btnCancel" => true,
		"back_url" => $returnUrl,
	]);

	$tabControl->Show();

endif;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
