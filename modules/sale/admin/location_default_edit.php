<?php
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\DefaultSiteHelper as Helper;
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

CJSCore::Init();
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_etc.js');
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_dynamiclist.js');

$request = Context::getCurrent()->getRequest();

$returnUrl = trim((string)$request->get('return_url'));
$externalReturnUrl = $returnUrl !== '';
$nameToDisplay = '';
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

	if (
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

	if ($formSubmitted && $actionFailure) // if form were submitted, but form action (add or update) failed
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
		if ($id)
		{
			// load from database
			$formData = Helper::getFormData($id);
			$nameToDisplay = (string)$formData['SITE_NAME'];
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
	if($actionFailure):
		CAdminMessage::ShowMessage([
			'MESSAGE' => $actionFailureMessage,
			'type' => 'ERROR',
		]);
	endif;

	$topMenu = new CAdminContextMenu(array(
		array(
			"TEXT" => GetMessage("SALE_LOCATION_E_GO_BACK"),
			"LINK" => Helper::getListUrl(),
			"ICON" => "btn_list",
		)
	));
	$topMenu->Show();

	$tabControl = new CAdminForm("tabcntrl_location_default_edit", array(
		array(
			"DIV" => "main",
			"TAB" => Loc::getMessage('SALE_LOCATION_E_MAIN_TAB'),
			"TITLE" =>  Loc::getMessage('SALE_LOCATION_E_MAIN_TAB_TITLE')
		)
	));
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

	$tabControl->BeginCustomField('LOCATIONS', Loc::getMessage('SALE_LOCATION_E_HEADING_LOCATIONS'));
		?>
		<tr>
			<td>
				<?php
				$randTag = rand(99, 999);
				$appearance = Helper::getWidgetAppearance();
				$i = 0;
				?>
				<input type="hidden" name="element[ID]" value="<?=$id?>" />
				<div id="ib_external_values_<?=$randTag?>">

					<table class="internal" style="margin: 0 auto; min-width: 600px;">
						<tbody class="bx-ui-dynamiclist-container">
							<tr class="heading">
								<td width="70%"><?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_LOCATION')?></td>
								<td width="20%"><?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_SORT')?></td>
								<td width="10%"><?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_REMOVE')?></td>
							</tr>
							<?php
							if (!empty($formData['LOCATION']) && is_array($formData['LOCATION'])):
								foreach($formData['LOCATION'] as $location):
									?>
									<tr>
										<td>
											<?php
											$APPLICATION->IncludeComponent(
												'bitrix:sale.location.selector.' . Helper::getWidgetAppearance(),
												'',
												[
													'ID' => '',
													'CODE' => $location['LOCATION_CODE'],
													'INPUT_NAME' => 'element[LOCATION][' . $i . '][LOCATION_CODE]',
													'PROVIDE_LINK_BY' => 'code',
													'SHOW_ADMIN_CONTROLS' => 'Y',
													'SELECT_WHEN_SINGLE' => 'N',
													'FILTER_BY_SITE' => 'Y',
													'FILTER_SITE_ID' => $id,
													'SHOW_DEFAULT_LOCATIONS' => 'N',
													'SEARCH_BY_PRIMARY' => 'Y',
												],
												false
											);
											?>
										</td>
										<td>
											<input type="text" name="element[LOCATION][<?=$i?>][SORT]" value="<?=intval($location['SORT'])?>" size="7" />
										</td>

										<td style="text-align: center">
											<?php
											if ($location['LOCATION_CODE'] <> ''):
												?>
												<input type="checkbox" name="element[LOCATION][<?= $i ?>][REMOVE]"
													value="1" <?= ($location['REMOVE'] == 1? 'checked' : '') ?> />
												<?php
											endif;
											?>
										</td>
									</tr>
									<?php
									$i++;
								endforeach;
							endif;
							?>

							<script type="text/html" data-template-id="bx-ui-dynamiclist-row">
								<tr>
									<td></td>
									<td>
										<input type="text" name="element[LOCATION][{{column_id}}][SORT]" value="100" size="7" />
									</td>
									<td style="text-align: center"></td>
								</tr>
							</script>
						</tbody>
					</table>

					<div style="width: 100%; text-align: center; margin: 10px 0;">
						<input class="adm-btn-big bx-ui-dynamiclist-addmore" type="button" value="<?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_MORE')?>" title="<?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_MORE')?>">
					</div>

				</div>

				<div style="display: none">
					<?php
					$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".Helper::getWidgetAppearance(), "", array(
						"ID" => "",
						"CODE" => "",
						"INPUT_NAME" => "",
						"PROVIDE_LINK_BY" => "code",
						"SHOW_ADMIN_CONTROLS" => 'Y',
						"SELECT_WHEN_SINGLE" => 'N',
						"FILTER_BY_SITE" => 'Y',
						"FILTER_SITE_ID" => $id,
						"SHOW_DEFAULT_LOCATIONS" => 'N',
						"SEARCH_BY_PRIMARY" => 'Y',
						"JS_CONTROL_GLOBAL_ID" => 'defaultLocationSelector',
						"USE_JS_SPAWN" => 'Y'
						),
						false
					);?>
				</div>

				<script>
					new BX.ui.dynamicList({
						scope: 'ib_external_values_<?=$randTag?>',
						initiallyAdd: 3,
						startFrom: <?=$i?>,
						bindEvents: {
							'after-row-built': function(row){

								var children = row.childNodes;
								for(var c in children){ // find first dom node (non-text node)
									if(BX.type.isElementNode(children[c])){

										var clone = window.BX.locationSelectors['defaultLocationSelector'].spawn(
											children[c],
											function(opts, node){
												node.childNodes[0].removeAttribute('id'); // drop scope id
											}
										);

										clone.setTargetInputName('element[LOCATION]['+this.vars.idOffset+'][LOCATION_CODE]');

										break;
									}
								}
							}
						}
					});
				</script>

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
