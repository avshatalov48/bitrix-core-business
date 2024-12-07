<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions <= "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

if(!CBXFeatures::IsFeatureEnabled('SaleReports'))
{
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

	// <editor-fold desc="--------- Server processing ---------">
ClearVars();
$errorMessage = '';
$errAdmMessage = null;
$fCriticalError = false;

// Using report module
if (!CModule::IncludeModule('report'))
{
	$errorMessage .= GetMessage("REPORT_MODULE_NOT_INSTALLED").'<br>';
	$fCriticalError = true;
}

// Using catalog module
if (!CModule::IncludeModule('catalog'))
{
	$errorMessage .= GetMessage("CATALOG_MODULE_NOT_INSTALLED").'<br>';
	$fCriticalError = true;
}

// Using iblock module
if (!CModule::IncludeModule('iblock'))
{
	$errorMessage .= GetMessage("IBLOCK_MODULE_NOT_INSTALLED").'<br>';
	$fCriticalError = true;
}

// If exists $copyID parameter and it more than 0, then will creating a copy of existing report.
$copyID = (int)$_REQUEST['copyID'];
$fCopyMode = ($copyID > 0) ? true : false;

// If exists $ID parameter and it more than 0, then will creating a new report.
$ID = (int)($_REQUEST['ID'] ?? 0);
$fEditMode = ($ID > 0) ? true : false;

// If editing report that exists, then beforehand we gets its parameters.
$arRepParams = array();
if ($fEditMode || $fCopyMode)
{
	if ($fEditMode) $repID = $ID;
	else if ($fCopyMode) $repID = $copyID;

	if (!($arRepParams = Bitrix\Report\ReportTable::getById($repID)->fetch()))
	{
		$errorMessage .= GetMessage("SALE_REPORT_CONSTRUCT_ERROR_EDIT_REPORT_ON_GET_PARAMS").'<br>';
		$fCriticalError = true;
	}
}

if ($fEditMode && isset($arRepParams['MARK_DEFAULT']) && intval($arRepParams['MARK_DEFAULT']) > 0)
{
	$errorMessage .= GetMessage("SALE_REPORT_DEFAULT_CAN_NOT_BE_EDITED").'<br>';
	$fCriticalError = true;
}

CBaseSaleReportHelper::init();

//<editor-fold defaultstate='collapsed' desc="Forming parameters of component report.construct">
$arParams = array(
	'ACTION' => 'create',
	'TITLE' => GetMessage('SALE_REPORT_CONSTRUCT_NEW_REPORT_TAB'),
	'PATH_TO_REPORT_LIST' => '/bitrix/admin/sale_report.php?lang='.LANG,
	'PATH_TO_REPORT_CONSTRUCT' => '/bitrix/admin/sale_report_construct.php',
	'PATH_TO_REPORT_VIEW' => '/bitrix/admin/sale_report_view.php?lang=' . LANGUAGE_ID . '&ID=#report_id#',
	'USE_CHART' => true
);

// check helper selection
$fSelectHelperMode = false;
$rep_owner = '';
if ($rep_owner = ($_REQUEST['rep_owner'] ?? ''))
{
	try
	{
		// filter rep_owner value
		$matches = array();
		$rep_owner = mb_substr($rep_owner, 0, 50);
		if (preg_match('/^[A-Z_][A-Z0-9_-]*[A-Z0-9_]$/i', $rep_owner, $matches)) $rep_owner = $matches[0];
		else $rep_owner = '';

		if (!$rep_owner || !in_array($rep_owner, CBaseSaleReportHelper::getOwners()))
			throw new Exception(GetMessage('REPORT_UNKNOWN_ERROR'));

		if (!$fCriticalError)
		{
			// set owner id
			$arParams['OWNER_ID'] = $rep_owner;
			// get helper name
			$arParams['REPORT_HELPER_CLASS'] = CBaseSaleReportHelper::getHelperByOwner($rep_owner);
		}
	}
	catch (Exception $e)
	{
		$errorMessage .= $e->getMessage().'<br>';
		$fCriticalError = true;
	}
}

if ($fEditMode)
{
	$arParams['report'] = $arRepParams;
	$arParams['ACTION'] = 'edit';
	$arParams['TITLE'] = $arRepParams['TITLE'];
	$arParams['REPORT_ID'] = $ID;
	$arParams['REPORT_HELPER_CLASS'] = CBaseSaleReportHelper::getHelperByOwner($arRepParams['OWNER_ID']);
	$rep_owner = $arRepParams['OWNER_ID'];
}

elseif ($fCopyMode)
{
	$arParams['report'] = $arRepParams;
	$arParams['ACTION'] = 'copy';
	$arParams['TITLE'] = $arRepParams['TITLE'];
	$arParams['REPORT_ID'] = $copyID;
	$arParams['REPORT_HELPER_CLASS'] = CBaseSaleReportHelper::getHelperByOwner($arRepParams['OWNER_ID']);
	$rep_owner = $arRepParams['OWNER_ID'];
}

if ($arParams['ACTION'] == 'create' && !$arParams['REPORT_HELPER_CLASS']) $fSelectHelperMode = true;
//</editor-fold>

// <editor-fold defaultstate="collapsed" desc="POST action">
if ($_REQUEST['cancel'] ?? false)
{
	if (!empty($_POST['rep_referer']))
	{
		$url = $_POST['rep_referer'];
	}
	else
	{
		$url = $_SERVER['HTTP_REFERER'];
	}
	/*else
	{
		$url = $fEditMode ? str_replace('#report_id#', $ID, $arParams['PATH_TO_REPORT_VIEW']) : $arParams['PATH_TO_REPORT_LIST'];
	}*/
	LocalRedirect($url);
}

$siteList = CBaseSaleReportHelper::getSiteList();

if (isset($_REQUEST['F_SALE_SITE']))
{
	$siteId = mb_substr($_REQUEST['F_SALE_SITE'], 0, 2);
	if (array_key_exists($siteId, $siteList))
	{
		$siteCookieId = CBaseSaleReportHelper::getSiteCookieId();
		setcookie($siteCookieId, $siteId, time()+365*24*3600);
	}
	$arParams['F_SALE_SITE'] = $siteId;
	CBaseSaleReportHelper::setDefaultSiteId($siteId);
	unset($siteId);
}
else
{
	$siteCookieId = CBaseSaleReportHelper::getSiteCookieId();
	if (isset($_COOKIE[$siteCookieId]))
	{
		$siteId = mb_substr($_COOKIE[$siteCookieId], 0, 2);
		if (array_key_exists($siteId, $siteList))
		{
			$arParams['F_SALE_SITE'] = $siteId;
			CBaseSaleReportHelper::setDefaultSiteId($siteId);
		}
		unset($siteId);
	}
}

if (($_REQUEST['REPORT_AJAX'] ?? '') === 'Y')
{
	$arResponse = array();
	if (is_array($_REQUEST['filterTypes']))
	{
		$result = CBaseSaleReportHelper::getAjaxResponse($_REQUEST['filterTypes']);
		if (is_array($result)) $arResponse = $result;
	}
	header("Content-Type: application/x-javascript; charset=".LANG_CHARSET);
	echo CUtil::PhpToJSObject($arResponse);
	exit;
}
// </editor-fold>

if (!isset($arParams['F_SALE_SITE']))
{
	$arParams['F_SALE_SITE'] = CBaseSaleReportHelper::getDefaultSiteId();
}
	// </editor-fold>



// Page header
$rep_title = ($fEditMode) ? GetMessage("SALE_REPORT_EDIT_TITLE") : GetMessage("SALE_REPORT_CONSTRUCT_TITLE");
if (isset($arParams['TITLE']) && !empty($arParams['TITLE'])) $rep_title .= ' "'.$arParams['TITLE'].'"';
$APPLICATION->SetTitle($rep_title);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");



	// <editor-fold desc="--------- Page output ---------">

if( $errorMessage )
{
	$errAdmMessage = new CAdminMessage(
		array(
			"DETAILS"=>$errorMessage,
			"TYPE"=>"ERROR",
			"MESSAGE"=>(
				($fEditMode) ? GetMessage('SALE_REPORT_CONSTRUCT_ERROR_EDIT_REPORT')
					: GetMessage('SALE_REPORT_CONSTRUCT_ERROR_ADD_REPORT')
			),
			"HTML"=>true
		)
	);
	echo $errAdmMessage->Show();
}

if (!$fCriticalError)
{
		// <editor-fold desc="------------ Form output ------------">
	?>

<?php
	$aMenu = array(
		array(
			"TEXT" => GetMessage("REPORT_RETURN_TO_LIST"),
			"LINK" => $arParams["PATH_TO_REPORT_LIST"],
			"ICON"=>"btn_list",
		)
	);
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
?>

<div class="adm-detail-content-wrap">
	<div class="adm-detail-content">
		<div class="adm-detail-content-item-block">
			<form method="POST" name="task-filter-form" id="task-filter-form" action="<?=POST_FORM_ACTION_URI?>">
			<input type="hidden" name="ID" value="<?=$ID?>" />
			<?php if (!$fSelectHelperMode && $arParams['REPORT_HELPER_CLASS']) : ?>
			<input type="hidden" name="rep_owner" value="<?=$rep_owner?>" />
			<input type="hidden" name="rep_referer" value="<?=htmlspecialcharsbx(!empty($rep_referer) ? $rep_referer : ($_SERVER['HTTP_REFERER'] ?? ''))?>" />
			<?php else : ?>
			<input type="hidden" name="rep_referer" value="<?=htmlspecialcharsbx($_SERVER['HTTP_REFERER'])?>" />
			<?php endif; ?>

			<style type="text/css">
				table.report-table { width: 100%; }
			</style>

			<table cellspacing="0" class="report-table">
			<tr>
				<td>
					<?php if ($fSelectHelperMode) : ?>
					<span>
						<style type="text/css">
							.reports-title-label {
								color: #92907E;
								font-size: 14px;
								padding: 5px 0 6px 4px;
							}
						</style>
						<span class="reports-title-label"><?=GetMessage('SALE_REPORT_HELPER_SELECTOR_LABEL_TEXT').':'?></span>
						<select id="sale-report-helper-selector" name="rep_owner" class="filter-dropdown">
							<?php foreach (CBaseSaleReportHelper::getOwners() as $ownerId) : ?>
							<?php $ownerText = GetMessage('SALE_REPORT_HELPER_NAME_'.$ownerId); ?>
							<?php if ($ownerText) :?>
							<option value="<?=htmlspecialcharsbx($ownerId)?>"><?php echo htmlspecialcharsbx($ownerText); ?></option>
							<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</span>
					<?php endif; // if ($fSelectHelperMode) : ?>
					<?php
					if (!$fSelectHelperMode) {
						$APPLICATION->IncludeComponent(
							'bitrix:report.construct',
							'admin',
							array(
								'REPORT_ID' => $arParams['REPORT_ID'],
								'ACTION' => $arParams['ACTION'],
								'TITLE' => $arParams['TITLE'],
								'PATH_TO_REPORT_LIST' => $arParams['PATH_TO_REPORT_LIST'],
								'PATH_TO_REPORT_CONSTRUCT' => $arParams['PATH_TO_REPORT_CONSTRUCT'],
								'PATH_TO_REPORT_VIEW' => $arParams['PATH_TO_REPORT_VIEW'],
								'REPORT_HELPER_CLASS' => $arParams['REPORT_HELPER_CLASS'],
								'F_SALE_SITE' => $arParams['F_SALE_SITE'],
								'USE_CHART' => $arParams['USE_CHART']
							),
							null,
							array('HIDE_ICONS' => 'Y')
						);
					}
					?>
				</td>
			</tr>

			<?php if (!$fSelectHelperMode) : ?>
			<tr>
				</td>
					<!-- custom filter value control examples -->
					<div id="report-filter-value-control-examples-custom" style="display: none">

						<span name="report-filter-value-control-LID">
							<select class="report-filter-select" name="value">
								<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
								<? foreach(CBaseSaleReportHelper::getSiteList() as $kID => $vSiteName): ?>
								<option value="<?=htmlspecialcharsbx($kID)?>"><?=htmlspecialcharsbx($vSiteName)?></option>
								<? endforeach; ?>
							</select>
						</span>

						<span name="report-filter-value-control-PERSONAL_GENDER">
							<select class="report-filter-select" name="value">
								<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
								<? foreach(CBaseSaleReportHelper::getGenders() as $kID => $vName): ?>
								<option value="<?=htmlspecialcharsbx($kID)?>"><?=htmlspecialcharsbx($vName)?></option>
								<? endforeach; ?>
							</select>
						</span>

						<span name="report-filter-value-control-PERSON_TYPE_ID">
							<select class="report-filter-select sale-report-site-dependent" name="value" tid="PersonType">
								<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
								<? $siteId = CBaseSaleReportHelper::getDefaultSiteId(); ?>
								<? foreach(CBaseSaleReportHelper::getPersonTypes() as $kID => $v): ?>
									<? if ($v['LID'] === $siteId): ?>
									<option value="<?=htmlspecialcharsbx($kID)?>"><?=htmlspecialcharsbx($v['NAME'])?></option>
									<? endif; ?>
								<? endforeach; ?>
							</select>
						</span>

						<span name="report-filter-value-control-ORDER.PERSON_TYPE_ID">
							<select class="report-filter-select sale-report-site-dependent" name="value" tid="PersonType">
								<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
								<? $siteId = CBaseSaleReportHelper::getDefaultSiteId(); ?>
								<? foreach(CBaseSaleReportHelper::getPersonTypes() as $kID => $v): ?>
									<? if ($v['LID'] === $siteId): ?>
									<option value="<?=htmlspecialcharsbx($kID)?>"><?=htmlspecialcharsbx($v['NAME'])?></option>
									<? endif; ?>
								<? endforeach; ?>
							</select>
						</span>

						<span name="report-filter-value-control-Bitrix\Sale\Internals\Order:USER.PERSON_TYPE_ID">
							<select class="report-filter-select sale-report-site-dependent" name="value" tid="PersonType">
								<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
								<? $siteId = CBaseSaleReportHelper::getDefaultSiteId(); ?>
								<? foreach(CBaseSaleReportHelper::getPersonTypes() as $kID => $v): ?>
									<? if ($v['LID'] === $siteId): ?>
									<option value="<?=htmlspecialcharsbx($kID)?>"><?=htmlspecialcharsbx($v['NAME'])?></option>
									<? endif; ?>
								<? endforeach; ?>
							</select>
						</span>

						<span name="report-filter-value-control-\Bitrix\Sale\Internals\StatusLang">
							<select class="report-filter-select" name="value">
								<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
								<? foreach(CBaseSaleReportHelper::getStatusList() as $kID => $vStatusName): ?>
								<option value="<?=htmlspecialcharsbx($kID)?>"><?=htmlspecialcharsbx($vStatusName)?></option>
								<? endforeach; ?>
							</select>
						</span>

						<span name="report-filter-value-control-Order:USER.LID">
							<select class="report-filter-select" name="value">
								<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
								<? foreach(CBaseSaleReportHelper::getSiteList() as $kID => $vSiteName): ?>
								<option value="<?=htmlspecialcharsbx($kID)?>"><?=htmlspecialcharsbx($vSiteName)?></option>
								<? endforeach; ?>
							</select>
						</span>

						<span name="report-filter-value-control-PRODUCT.GoodsSection:PRODUCT.SECT">
							<select class="report-filter-select sale-report-site-dependent"
								name="value" tid="Section" multiple="multiple" size="5">
								<?php echo CBaseSaleReportHelper::getSectionsSelectHTMLOptions(); ?>
							</select>
						</span>

						<span name="report-filter-value-control-IBLOCK.SectionElement:IBLOCK_ELEMENT.IBLOCK_SECTION">
							<select class="report-filter-select sale-report-site-dependent"
								name="value" tid="Section" multiple="multiple" size="5">
								<?php echo CBaseSaleReportHelper::getSectionsSelectHTMLOptions(); ?>
							</select>
							<!-- Loading lists of filters dependent on a site when the filter of a site is changed -->
							<script>
								BX.ready(
									function ()
									{
										var siteSelect = BX('sale-site-filter');
										if (siteSelect)
										{
											BX.bind(siteSelect, 'change', onChangeSiteFilter);
										}
									}
								);
								function onChangeSiteFilter()
								{
									var filters, siteSelect, filterContainer;
									var filterType, arFilterTypes = [];
									var url, i;

									siteSelect = BX('sale-site-filter');
									filterContainer = BX('reports-filter-columns-container');
									if (siteSelect && siteSelect.value && filterContainer)
									{
										filters = BX.findChildren(filterContainer, {class: 'sale-report-site-dependent'}, true);
										for(i in filters)
										{
											if (filters[i].tagName == 'SELECT')
											{
												filterType = filters[i].getAttribute('tid');
												if (filterType) arFilterTypes[arFilterTypes.length] = filterType;
												filters[i].value = '';
											}
										}
										if (arFilterTypes.length > 0)
										{
											BX.showWait();
											url = '/bitrix/admin/sale_report_construct.php?<?=bitrix_sessid_get()?>'+
												'&REPORT_AJAX=Y&F_SALE_SITE='+siteSelect.value;
											BX.ajax.post(url, {'filterTypes': arFilterTypes}, fProcessAjaxResult );
										}
									}
								}
								function fProcessAjaxResult(res)
								{
									BX.closeWait();
									var i, filters, filterType, filterContainer;
									filterContainer = BX('reports-filter-columns-container');
									if (filterContainer && res)
									{
										res = eval('('+res+')');
										filters = BX.findChildren(filterContainer, {class: 'sale-report-site-dependent'}, true);
										for(i in filters)
										{
											if (filters[i].tagName == 'SELECT')
											{
												filterType = filters[i].getAttribute('tid');
												if (filterType)
												{
													fRewriteSelectFromArray(filters[i], res[filterType], '');
												}
												filters[i].value = '';
											}
										}
									}
								}
								function fRewriteSelectFromArray(select, data, value)
								{
									var opt, el, i, j;
									var setSelected = false;
									var bMultiple;

									if (!(value instanceof Array)) value = new Array(value);
									if (select)
									{
										bMultiple = !!(select.getAttribute('multiple'));
										while (opt = select.lastChild) select.removeChild(opt);
										for (i in data)
										{
											el = document.createElement("option")
											el.value = data[i]['value'];
											el.innerHTML = data[i]['text'];
											try
											{
												// for IE earlier than version 8
												select.add(el,select.options[null]);
											}
											catch (e)
											{
												el = document.createElement("option")
												el.text = data[i]['text'];
												select.add(el,null);
											}
											if (!setSelected || bMultiple)
											{
												for (j in value)
												{
													if (data[i]['value'] == value[j])
													{
														el.selected = true;
														if (!setSelected)
														{
															setSelected = true;
															select.selectedIndex = i;
														}
														break;
													}
												}
											}
										}
									}
								}
							</script>

						</span>

						<span name="report-filter-value-control-StoreProduct:SALE_PRODUCT">
							<select class="report-filter-select" name="value" tid="Section" multiple="multiple" size="5">
								<option value="" selected="selected"><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
								<? foreach(CBaseSaleReportHelper::getProductStores() as $k => $v): ?>
									<option value="<?=htmlspecialcharsbx($k)?>"><?=htmlspecialcharsbx($v)?></option>
								<? endforeach; ?>
							</select>
						</span>

						<style type="text/css">
							/* hide compares for User and Group */
							.report-filter-compare-\\Bitrix\\Main\\User {display: none;}
							.report-filter-compare-\\Bitrix\\Main\\Group {display: none;}
							.report-filter-compare-USER\.UserGroup\:USER\.GROUP {display: none;}
							.report-filter-compare-USER\.UserGroup\:USER\.GROUP {display: none;}
							.report-filter-compare-UserGroup\:USER\.GROUP {display: none;}
							.report-filter-compare-FUSER\.USER\.UserGroup\:USER\.GROUP {display: none;}
							.report-filter-value-control-Basket\:PRODUCT\.FUSER\.USER  {display: none;}
						</style>

						<span name="report-filter-value-control-\Bitrix\Main\User" callback="RTFilter_chooseSALEUSER">
							<a class="report-select-popup-link" caller="true" style="cursor: pointer;"><?=GetMessage('REPORT_CHOOSE')?></a>
							<input type="hidden" name="value" />
						</span>
						<script>
							var RTFilter_chooseSALEUSER_LAST_CALLER;
							function RTFilter_chooseSALEUSER(span)
							{
								var a = BX.findChild(span, {tag:'a'});

								BX.bind(a, 'click', RTFilter_showSALEUSERSelector);
								BX.bind(a, 'click', function(e){
									RTFilter_chooseSALEUSER_LAST_CALLER = this;
								});

							}
							function RTFilter_showSALEUSERSelector()
							{
								BX.Access.Init();
								BX.Access.SetSelected(null);
								BX.Access.ShowForm({callback: RTFilter_chooseSALEUSERCatch_fromBXAccess});
							}
							function RTFilter_chooseSALEUSERCatch_fromBXAccess(arSelected)
							{
								if (arSelected.user)
								{
									var user = null;
									for (var i in arSelected.user) { user = arSelected.user[i]; break; }
									if (user)
									{
										user.id = user.id.substr(1);
										RTFilter_chooseSALEUSERCatch(user);
									}
								}
							}
							function RTFilter_chooseSALEUSERCatch(user)
							{
								var userContainer = RTFilter_chooseSALEUSER_LAST_CALLER.parentNode;

								if (parseInt(user.id) > 0)
								{
									BX.findChild(userContainer, {tag:'a'}).innerHTML = user.name;
								}

								BX.addClass(BX.findChild(userContainer, {tag:'a'}), 'report-select-popup-link-active');
								BX.findChild(userContainer, {attr:{name:'value'}}).value = user.id;
								BX.Access.closeWait();
							}
						</script>

						<span name="report-filter-value-control-\Bitrix\Main\Group" callback="RTFilter_chooseGroup">
							<a class="report-select-popup-link" caller="true" style="cursor: pointer;"><?=GetMessage('REPORT_CHOOSE')?></a>
							<input type="hidden" name="value" />
						</span>
						<script>
							var RTFilter_chooseGroup_LAST_CALLER;
							function RTFilter_chooseGroup(span)
							{
								var a = BX.findChild(span, {tag:'a'});

								BX.bind(a, 'click', RTFilter_showGroupSelector);
								BX.bind(a, 'click', function(e){
									RTFilter_chooseGroup_LAST_CALLER = this;
								});

							}
							function RTFilter_showGroupSelector()
							{
								BX.Access.Init();
								BX.Access.SetSelected(null);
								BX.Access.ShowForm({callback: RTFilter_chooseGroupCatch_fromBXAccess});
							}
							function RTFilter_chooseGroupCatch_fromBXAccess(arSelected)
							{
								if (arSelected.group)
								{
									var group = null;
									for (var i in arSelected.group) { group = arSelected.group[i]; break; }
									if (group)
									{
										group.id = group.id.substr(1);
										RTFilter_chooseGroupCatch(group);
									}
								}
							}
							function RTFilter_chooseGroupCatch(group)
							{
								var groupContainer = RTFilter_chooseGroup_LAST_CALLER.parentNode;

								if (parseInt(group.id) > 0)
								{
									BX.findChild(groupContainer, {tag:'a'}).innerHTML = group.name;
								}

								BX.addClass(BX.findChild(groupContainer, {tag:'a'}), 'report-select-popup-link-active');
								BX.findChild(groupContainer, {attr:{name:'value'}}).value = group.id;
								BX.Access.closeWait();
							}
						</script>
					</div>
				</td>
			</tr>
			<?php endif; // if (!$fSelectHelperMode) : ?>
			</table>

			<?
			// <editor-fold defaultstate="collapsed" desc="-- Buttons --">
			?>
			<div id="sale-report-construct-buttons-block">
				<input id="report-save-button" class="adm-btn-save"
						type="submit" name="save"
						value="<?
							if ($fEditMode) echo GetMessage('SALE_REPORT_CONSTRUCT_BUTTON_SAVE_LABEL_ON_EDIT');
							elseif ($fSelectHelperMode) echo GetMessage('SALE_REPORT_CONSTRUCT_BUTTON_SAVE_LABEL_ON_SELECT_HELPER');
							else echo GetMessage('SALE_REPORT_CONSTRUCT_BUTTON_SAVE_LABEL');
						?>"
						title="<?
							if ($fEditMode) echo GetMessage('SALE_REPORT_CONSTRUCT_BUTTON_SAVE_TITLE_ON_EDIT');
							elseif ($fSelectHelperMode) echo GetMessage('SALE_REPORT_CONSTRUCT_BUTTON_SAVE_TITLE_ON_SELECT_HELPER');
							else echo GetMessage('SALE_REPORT_CONSTRUCT_BUTTON_SAVE_TITLE');
						?>" />&nbsp
				<input class="adm-btn"
						type="submit" name="cancel"
						value="<? echo GetMessage('SALE_REPORT_CONSTRUCT_BUTTON_CANCEL_LABEL'); ?>"
						title="<? echo GetMessage('SALE_REPORT_CONSTRUCT_BUTTON_CANCEL_TITLE'); ?>" />
			</div>
			<?
			// </editor-fold>
			?>

			</form>
		</div>
	</div>
	<div class="adm-detail-content-btns adm-detail-content-btns-empty"></div>
</div>
	<?
		// </editor-fold>
}// if (!$fCriticalError)

	// </editor-fold>



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>