<?php

/** @global CMain $APPLICATION */
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\LocationHelper as Helper;
use Bitrix\Sale\Location\Admin\ExternalServiceHelper;
use Bitrix\Sale\Location\Admin\SearchHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule('sale');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

$request = Context::getCurrent()->getRequest();

$id = (int)($request->get('id'));
if ($id <= 0)
{
	$id = false;
}
$copyId = (int)$request->get('copy_id');
if ($copyId <= 0)
{
	$copyId = false;
}

// the following parameter will be present visibly only when copying or creating blank with the same parent
$parentId = (int)$request->get('parent_id');
if ($parentId <= 0)
{
	$parentId = '0';
}
if (!$parentId && $id)
{
	$parentId = Helper::getParentId($id);
}

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;
$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = Helper::getListUrl($parentId);
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

if($APPLICATION->GetGroupRight("sale") < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_MODULE_ACCES_DENIED"));

$userIsAdmin = $APPLICATION->GetGroupRight("sale") >= "W";
CSaleLocation::locationProCheckEnabled(); // temporal

#####################################
#### Data prepare
#####################################

CJSCore::Init();
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_etc.js');
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_dynamiclist.js');

$returnUrl = trim((string)$request->get('return_url'));
$externalReturnUrl = $returnUrl !== '';
$nameToDisplay = '';
$actionFailureMessage = '';
$fatalFailureMessage = '';

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
		$redirectUrl = false;

		// parent id might be updated, so re-read it from request
		$requestParentId = (int)$request->get('PARENT_ID');
		if ($requestParentId > 0)
		{
			$parentId = $requestParentId;
		}

		try
		{
			$DB->StartTransaction();

			$saveUrl = '';
			$applyUrl = '';

			if($saveAsId) // existed, updating
			{
				$res = Helper::update($saveAsId, $element);
				if ($res['success']) // on successfull update ...
				{
					if ($actionSave)
					{
						$saveUrl = $returnUrl ?: $listUrl; // go to the parent page
					}

					if ($actionApply)
					{
						$applyUrl = $returnUrl ?: Helper::getEditUrl($saveAsId);
					}
				}
			}
			else // new or copyed item
			{
				$res = Helper::add($element);
				if ($res['success']) // on successfull add ...
				{
					if ($actionSave)
					{
						$saveUrl = $returnUrl ?: $listUrl; // go to the parent list page
					}

					if ($actionApply)
					{
						$applyUrl = $returnUrl ?: Helper::getEditUrl($res['id']); // go to the page of just created item
					}
				}
			}

			// no matter we updated or added a new item - we go to blank page on $actionSaveAndAdd
			if ($res['success'] && $actionSaveAndAdd)
			{
				$applyUrl = Helper::getEditUrl(false, ['parent_id' => $parentId]);  // go to the blank page with correct parent_id to create
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
			$message = $e->getMessage().(!empty($code) ? ' ('.$code.')' : '');

			$actionFailureMessage = Loc::getMessage('SALE_LOCATION_E_CANNOT_'.($saveAsId ? 'UPDATE' : 'SAVE').'_ITEM').($message <> ''? ': <br /><br />'.$message : '');

			$DB->Rollback();

			$adminSidePanelHelper->sendJsonErrorResponse($actionFailureMessage);
		}
	}

	if (!$returnUrl)
	{
		$returnUrl = Helper::getListUrl($parentId); // default return page for "cancel" action
	}

	#####################################
	#### READ FORM DATA
	#####################################

	$readAsId = $id ?: $copyId;

	if($formSubmitted && $actionFailure) // if form were submitted, but form action (add or update) failed
	{
		// load from request
		$formData = $element;

		if ($readAsId)
		{
			$nameToDisplay = Helper::getNameToDisplay($readAsId);
		}

		// cleaning up empty external data
		if (!empty($formData['EXTERNAL']) && is_array($formData['EXTERNAL']))
		{
			foreach ($formData['EXTERNAL'] as $eId => $external)
			{
				if ($external['XML_ID'] == '')
					unset($formData['EXTERNAL'][$eId]);
			}
		}
	}
	else
	{
		if($readAsId)
		{
			// load from database
			$formData = Helper::getFormData($readAsId);

			$langU = mb_strtoupper(LANGUAGE_ID);
			$nameToDisplay = trim((string)($formData['NAME_' . $langU] ?? ''));
			if ($nameToDisplay === '')
			{
				$nameToDisplay = (string)($formData['CODE'] ?? '');
			}
		}
		else
		{
			// load blank form, optionally with parent id filled up
			$formData = [];
			if ($parentId)
			{
				$formData['PARENT_ID'] = $parentId;
			}
		}
	}
}
catch (Main\SystemException $e)
{
	$fatalFailure = true;

	$code = $e->getCode();
	$fatalFailureMessage = $e->getMessage() . (!empty($code) ? ' (' . $code . ')' : '');
}

#####################################
#### PAGE INTERFACE GENERATION
#####################################

if(!$fatalFailure) // no fatals like "module not installed, etc."
{

}

$APPLICATION->SetTitle($nameToDisplay <> ''? Loc::getMessage('SALE_LOCATION_E_ITEM_EDIT', array('#ITEM_NAME#' => $nameToDisplay)) : Loc::getMessage('SALE_LOCATION_E_ITEM_NEW'));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

#####################################
#### Data output
#####################################

//temporal code
if (!CSaleLocation::locationProCheckEnabled())
{
	require $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_admin.php";
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
			'LINK' => $listUrl,
			'ICON' => 'btn_list',
		]
	]);
	$topMenu->Show();

	$tabControl = new CAdminForm("tabcntrl_location_node_edit", array(
		array(
			"DIV" => "main",
			"TAB" => Loc::getMessage('SALE_LOCATION_E_MAIN_TAB'),
			"TITLE" =>  Loc::getMessage('SALE_LOCATION_E_MAIN_TAB_TITLE')
		),
		array(
			"DIV" => "external",
			"TAB" => Loc::getMessage('SALE_LOCATION_E_EXTERNAL_TAB'),
			"TITLE" => Loc::getMessage('SALE_LOCATION_E_EXTERNAL_TAB_TITLE')
		)
	));
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
	$argParentId = (int)$request->get('parent_id');
	if ($argParentId > 0)
	{
		$args['parent_id'] = $argParentId;
	}
	unset($argParentId);

	$formActionNodeId = (int)$request->get(Helper::URL_PARAM_ID);
	if ($formActionNodeId <= 0)
	{
		$formActionNodeId = false;
	}
	$formActionUrl = Helper::getEditUrl($formActionNodeId, $args); // generally, it is not safe to leave action empty
	$formActionUrl = $adminSidePanelHelper->setDefaultQueryParams($formActionUrl);
	$tabControl->Begin(array("FORM_ACTION" => $formActionUrl));
	$tabControl->BeginNextFormTab();

	$requiredFld = ' class="adm-detail-required-field"';

	$columns = Helper::getColumns('detail');
	$geoHeadingShown = false;
	foreach($columns as $code => $field):
		$field['required'] ??= false;

		if($code === 'ID' && !$id)
		{
			continue; // new node or copied
		}
		if(Helper::checkIsNameField($code))
		{
			continue; // we`ll output names in a different manner
		}
		$value = Helper::makeSafeDisplay($formData[$code], $code);

		$tabControl->BeginCustomField($code, $field['title']);

			if(!$geoHeadingShown && ($code == 'LATITUDE' || $code == 'LONGITUDE')):
				?>
				<tr class="heading">
					<td colspan="2"><?=Loc::getMessage('SALE_LOCATION_E_GEODATA')?></td>
				</tr>
				<?php
				$geoHeadingShown = true;
			endif?>

			<tr<?= ($field['required'] || $code === 'ID' ? $requiredFld : ''); ?>>
				<td width="40%"><?=$field['title']?>:</td>
				<td width="60%">

					<?if($code == 'ID'):?>

						<?=$id?>
						<input type="hidden" name="element[ID]" value="<?=$id?>" />

					<?elseif($code == 'TYPE_ID'):?>

						<select name="element[TYPE_ID]">
							<?foreach(Helper::getTypeList() as $tId => $type):?>
								<option value="<?=$tId?>"<?=($tId == $value ? ' selected' : '')?>><?=htmlspecialcharsbx($type)?></option>
							<?endforeach?>
						</select>

					<?elseif($code == 'PARENT_ID'):?>

						<div style="max-width: 500px">

							<?/*$APPLICATION->IncludeComponent(
								"bitrix:map.yandex.view",
								"",
							Array(),
							false
							);*/?>

							<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".Helper::getWidgetAppearance(), "", array(
								"ID" => $value,
								"CODE" => "",
								"INPUT_NAME" => "element[PARENT_ID]",
								"PROVIDE_LINK_BY" => "id",
								"SHOW_ADMIN_CONTROLS" => 'Y',
								"SELECT_WHEN_SINGLE" => 'N',
								"FILTER_BY_SITE" => 'N',
								"SHOW_DEFAULT_LOCATIONS" => 'N',
								"SEARCH_BY_PRIMARY" => 'Y',

								"EXCLUDE_SUBTREE" => $nodeId ?? null, // TODO: nodeId is not exists, need correct parameter
								),
								false
							);?>

						</div>

					<?else:?>

						<input type="text" name="element[<?=$code?>]" value="<?=$value?>" <?if($code == 'SORT'):?>size="7"<?endif?> />

					<?endif?>

				</td>
			</tr>
		<?$tabControl->EndCustomField($code, '');

	endforeach;

	$languages = Helper::getLanguageList();
	$nameMap = Helper::getNameMap();

	$tabControl->BeginCustomField('NAME', Loc::getMessage('SALE_LOCATION_E_HEADING_NAME_ALL'));
	foreach($languages as $langValue):
		?>
		<tr class="heading">
			<td colspan="2"><?=Loc::getMessage('SALE_LOCATION_E_HEADING_NAME', array('#LANGUAGE_ID#' => htmlspecialcharsbx($langValue)))?></td>
		</tr>

		<?php
		$langValue = mb_strtoupper($langValue);

		foreach($nameMap as $code => $field):
			$field['required'] ??= false;
			$value = Helper::makeSafeDisplay($formData[$code.'_'.$langValue], $code);?>
			<tr<?= ($field['required'] || $code == 'ID' ? $requiredFld : '')?>>
				<td width="40%"><?=$field['title']?></td>
				<td width="60%">
					<input type="text" name="element[<?=$code?>_<?=$langValue?>]" value="<?=$value?>" size="20" maxlength="255" />
				</td>
			</tr>
		<?php
		endforeach;
	endforeach;
	$tabControl->EndCustomField('NAME', '');

	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField('EXTERNAL', Loc::getMessage('SALE_LOCATION_E_HEADING_EXTERNAL'));

		$services = Helper::getExternalServicesList();
		$yandexMarketEsId = Helper::getYandexMarketExternalServiceId();
		$isUaPortal = (Bitrix\Sale\Delivery\Helper::getPortalZone() === 'ua');
		?>
		<tr>
			<td>

				<?if(empty($services)):?>

					<?=Loc::getMessage('SALE_LOCATION_E_NO_SERVICES')?> <a href="<?=ExternalServiceHelper::getListUrl()?>" target="blank_"><?=Loc::getMessage('SALE_LOCATION_E_NO_SERVICES_LIST_PAGE')?></a>

				<?else:?>

					<?$randTag = rand(99, 999);?>
					<?$externalMap = Helper::getExternalMap();?>
					<div id="ib_external_values_<?=$randTag?>">

						<table class="internal" style="margin: 0 auto">
							<tbody class="bx-ui-dynamiclist-container">
								<tr class="heading">
									<?foreach($externalMap as $code => $field):?>
										<td><?=$field['title']?></td>
									<?endforeach?>
									<td><?=Loc::getMessage('SALE_LOCATION_E_HEADER_EXT_REMOVE')?></td>
								</tr>

								<?if (!empty($formData['EXTERNAL']) && is_array($formData['EXTERNAL'])):

									foreach ($formData['EXTERNAL'] as $id => $ext):
										$isYandexMarketOnUaPortal = (
											$isUaPortal
											&& (int)$ext['SERVICE_ID'] === $yandexMarketEsId
										);
									?>
										<tr style="<?=($isYandexMarketOnUaPortal ? 'visibility:hidden; position:absolute;' : '')?>;">
											<?foreach($externalMap as $code => $field):?>
												<?$value = Helper::makeSafeDisplay($ext[$code], $code);?>
												<td>
													<?if($code == 'SERVICE_ID'):?>
														<select name="element[EXTERNAL][<?=$ext['ID']?>][<?=$code?>]">
															<?foreach($services as $sId => $serv):
																if (
																	$isUaPortal
																	&& !$isYandexMarketOnUaPortal
																	&& (int)$serv['ID'] === $yandexMarketEsId
																)
																{
																	continue;
																}
															?>
																<option value="<?=intval($serv['ID'])?>"<?=($serv['ID'] == $value ? ' selected' : '')?>><?=htmlspecialcharsbx($serv['CODE'])?></option>
															<?endforeach?>
														</select>
													<?elseif($code == 'ID'):?>
														<?if(intval($value)):?>
															<?=$value?>
														<?endif?>
													<?else:?>
														<input type="text" name="element[EXTERNAL][<?=$ext['ID']?>][<?=$code?>]" value="<?=$value?>" size="20" />
													<?endif?>
												</td>
											<?endforeach?>

											<td style="text-align: center">
												<?if($ext['ID']):?>
													<input type="checkbox" name="element[EXTERNAL][<?=$ext['ID']?>][REMOVE]" value="1" />
												<?endif?>
											</td>
										</tr>
									<?php
									endforeach;
								endif;
								?>
								<script type="text/html" data-template-id="bx-ui-dynamiclist-row">
									<tr>
										<td></td>
										<td>
											<select name="element[EXTERNAL][n{{column_id}}][SERVICE_ID]">
												<?php
												foreach($services as $sId => $serv):
													if ($isUaPortal && (int)$serv['ID'] === $yandexMarketEsId)
													{
														continue;
													}
													?>
													<option value="<?=intval($serv['ID'])?>"><?=htmlspecialcharsbx($serv['CODE'])?></option>
													<?php
												endforeach;
												?>
											</select>
										</td>
										<td>
											<input type="text" name="element[EXTERNAL][n{{column_id}}][XML_ID]" value="" size="20" />
										</td>
										<td></td>
									</tr>
								</script>
							</tbody>
						</table>

						<div style="width: 100%; text-align: center; margin: 10px 0;">
							<input class="adm-btn-big bx-ui-dynamiclist-addmore" type="button" value="<?=Loc::getMessage('SALE_LOCATION_E_HEADER_EXT_MORE')?>" title="<?=Loc::getMessage('SALE_LOCATION_E_HEADER_EXT_MORE')?>">
						</div>

					</div>

					<script>
						new BX.ui.dynamicList({
							scope: 'ib_external_values_<?=$randTag?>',
							initiallyAdd: 3
						});
					</script>
				<?php
				endif;
				?>
			</td>
		</tr>
	<?php
	$tabControl->EndCustomField('EXTERNAL', '');

	$tabControl->Buttons(array(
		"disabled" => !$userIsAdmin,
		"btnSaveAndAdd" => true,
		"btnApply" => true,
		"btnCancel" => true,
		"back_url" => $listUrl,
	));

	$tabControl->Show();

endif;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
