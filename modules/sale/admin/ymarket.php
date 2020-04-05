<?

use \Bitrix\Sale\Services\PaySystem\Restrictions;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule("sale");
$bSaved = false;

if(isset($_POST["YANDEX_MARKET_ON"]))
	CSaleYMHandler::setActivity(true);
elseif(isset($_POST["YANDEX_MARKET_OFF"]))
	CSaleYMHandler::setActivity(false);

$siteList = array();
$defaultSite = "";
$rsSites = CSite::GetList($by = "sort", $order = "asc", Array());

while($arRes = $rsSites->Fetch())
{
	$siteList[$arRes['ID']] = $arRes['NAME'];

	if($arRes["DEF"] == "Y")
		$defaultSite = $arRes['ID'];
}

if(isset($_REQUEST["SITE_ID"]) && array_key_exists($_REQUEST["SITE_ID"], $siteList))
	$SITE_ID = $_REQUEST["SITE_ID"];
else
	$SITE_ID = $defaultSite;

if (isset($_REQUEST["https_check"]) && $_REQUEST["https_check"] == "Y" && check_bitrix_sessid())
{
	$ob = new CHTTP();
	$ob->http_timeout = 10;


	if (!@$ob->Get("https://".$_SERVER["SERVER_NAME"].$APPLICATION->GetCurPage()))
	{
		$res = "error";
		$text = GetMessage("SALE_YM_CHECK_HTTPS_ERROR");
	}
	else
	{
		$res = "ok";
		$text = GetMessage("SALE_YM_CHECK_HTTPS_SUCCESS");
	}

	header("Content-Type: application/x-javascript; charset=".LANG_CHARSET);
	echo CUtil::PhpToJSObject(array("status" => $res, "text" => $text));
	die();
}
else if($REQUEST_METHOD == "POST" && check_bitrix_sessid())
{
	$site = !empty($_POST["SITE_ID_INITIAL"]) && $SITE_ID == $_POST["SITE_ID_INITIAL"] ? $SITE_ID : $_POST["SITE_ID_INITIAL"];

	if(isset($_POST["YMSETTINGS"]) && is_array($_POST["YMSETTINGS"]) && !empty($_POST["YMSETTINGS"]))
	{
		$settings = CSaleYMHandler::getSettings(false);

		if(!is_array($settings['SETTINGS']))
			$settings['SETTINGS'] = array();

		if(!is_array($settings['SETTINGS'][$site]))
			$settings['SETTINGS'][$site] = array();

		$settings['SETTINGS'][$site] = array_merge($settings['SETTINGS'][$site], $_POST["YMSETTINGS"]);

		CSaleYMHandler::saveSettings($settings['SETTINGS']);
		$bSaved = true;
	}
}

$arTabs = array(
	array(
		"DIV" => "sale_ymarket_main",
		"TAB" => GetMessage("SALE_YM_TAB_MAIN"),
		"TITLE" => GetMessage("SALE_YM_TAB_MAIN_TITLE"),
	),
	array(
		"DIV" => "sale_ymarket_pay",
		"TAB" => GetMessage("SALE_YM_TAB_PAY"),
		"TITLE" =>GetMessage("SALE_YM_TAB_PAY_TITLE"),
	),
	array(
		"DIV" => "sale_ymarket_dlv",
		"TAB" => GetMessage("SALE_YM_TAB_DLV"),
		"TITLE" =>GetMessage("SALE_YM_TAB_DLV_TITLE"),
	),
	array(
		"DIV" => "sale_ymarket_status",
		"TAB" => GetMessage("SALE_YM_TAB_STATUS"),
		"TITLE" =>GetMessage("SALE_YM_TAB_STATUS_TITLE"),
	),
	array(
		"DIV" => "sale_ymarket_props",
		"TAB" => GetMessage("SALE_YM_TAB_PROPS"),
		"TITLE" =>GetMessage("SALE_YM_TAB_PROPS_TITLE"),
	)
);

$tabControl = new CAdminTabControl("tabControl", $arTabs);
$APPLICATION->SetTitle(GetMessage("SALE_YM_TITLE"));

$checkStyle = '
	<style type="text/css">
		.https_check_success {
			font-weight: bold;
			color: green;
		}

		.https_check_fail {
			font-weight: bold;
			color: red;
		}
	</style>';

$statuses = array(
	"CANCELED" => GetMessage("SALE_YM_F_CANCELED"),
	"ALLOW_DELIVERY" => GetMessage("SALE_YM_F_DELIVERY"),
	"PAYED" => GetMessage("SALE_YM_F_PAY"),
	"DEDUCTED" => GetMessage("SALE_YM_F_OUT"),
);

$saleStatusIterator = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID), false, false, Array("ID", "NAME", "SORT"));

while ($row = $saleStatusIterator->GetNext())
{
	$statuses[$row["ID"]] = "{$row["NAME"]} [{$row['ID']}]";
}

$outYandexStatuses = array(
	"DELIVERY" => GetMessage("SALE_YM_Y_STATUS_DELIVERY")." [DELIVERY]",
	"CANCELLED" => GetMessage("SALE_YM_Y_STATUS_CANCELLED")." [CANCELLED]",
	"PICKUP" => GetMessage("SALE_YM_Y_STATUS_PICKUP")." [PICKUP]",
	"DELIVERED" => GetMessage("SALE_YM_Y_STATUS_DELIVERED")." [DELIVERED]",
);

$APPLICATION->AddHeadString($checkStyle, true, true);

$requiredOrderProperties = array(
	"FIO",
	"EMAIL",
	"PHONE",
	"ZIP",
	"CITY",
	"LOCATION",
	"ADDRESS"
);

require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");

if($bSaved)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("SALE_YM_SETTINGS_SAVED"), "TYPE"=>"OK"));

?>
<form method="post" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>" name="ymform">
<?

if(CSaleYMHandler::isActive())
{
	?>
		<input type="hidden" name="SITE_ID_INITIAL" value="<?=$SITE_ID?>">
		<table width="100%">
			<tr>
				<td align="left">
					<?=GetMessage("SALE_YM_SITE")?>: <?=CLang::SelectBox("SITE_ID", $SITE_ID, "", "this.form.submit();")?>
				</td>
				<td align="right">
					<img alt="eBay logo" src="/bitrix/images/sale/yandex-market-logo.png" style="width: 100px; height: 67px;">
				</td>
			</tr>
		</table>
	<?

	$activeListNames = array();

	foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList() as $id => $fields)
	{
		if(!$fields["CLASS_NAME"]::canHasProfiles())
			if(!$fields["CLASS_NAME"]::canHasChildren())
				if($delivery = \Bitrix\Sale\Delivery\Services\Manager::createObject($fields))
					$activeListNames[$id] = $delivery->getNameWithParent();
	}

	$siteSetts = CSaleYMHandler::getSettingsBySiteId($SITE_ID, false);

	$dlvFilteredIds = \Bitrix\Sale\Delivery\Services\Manager::checkServiceRestriction(
		array_keys($activeListNames),
		$SITE_ID,
		'\Bitrix\Sale\Delivery\Restrictions\BySite'
	);

	if(!is_array($dlvFilteredIds))
		$dlvFilteredIds = array();

	$arDeliveryList = array_intersect_key($activeListNames, array_flip($dlvFilteredIds));

	$dbResultList = CSalePersonType::GetList(
		"NAME",
		"ASC",
		array(
			"LID" => $SITE_ID,
			"ACTIVE" => "Y"
		)
	);

	$arPersonTypes = array();
	while ($arPT = $dbResultList->Fetch())
		$arPersonTypes[$arPT['ID']] = $arPT['NAME'];

	if(isset($siteSetts["PERSON_TYPE"]) && array_key_exists($siteSetts["PERSON_TYPE"], $arPersonTypes))
	{
		$personTypeId= $siteSetts["PERSON_TYPE"];
	}
	else
	{
		reset($arPersonTypes);
		$personTypeId = key($arPersonTypes);
	}

	$arPropFilter = array(
		"PERSON_TYPE_ID" => $personTypeId,
		"ACTIVE" => "Y"
	);

	$dbOrderProps = CSaleOrderProps::GetList(
		array("ID" => "ASC"),
		$arPropFilter,
		false,
		false,
		array("ID", "CODE", "NAME", "TYPE", "REQUIED", "IS_LOCATION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "SORT", "IS_PHONE")
	);

	$orderPropsList = array();

	while ($arOrderProps = $dbOrderProps->Fetch())
		if(strlen($arOrderProps["CODE"]) > 0)
			$orderPropsList[$arOrderProps["CODE"]] = $arOrderProps["NAME"];

	$tabControl->Begin();
	$tabControl->BeginNextTab();

	?>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><span class="adm-required-field"><?=GetMessage("SALE_YM_CAMPAIGN_ID")?>:</span></td>
			<td width="60%">
				<input type="text" onkeypress="return correctCampaignId(event, this);" name="YMSETTINGS[CAMPAIGN_ID]" size="45" maxlength="255" value="<?=isset($siteSetts["CAMPAIGN_ID"]) ? htmlspecialcharsbx($siteSetts["CAMPAIGN_ID"]) : ""?>">
				<?=BeginNote();?>
					<?=GetMessage("SALE_YM_CAMPAIGN_ID_HELP")?>
				<?=EndNote();?>
			</td>
		</tr>
		<tr>
			<td ><span class="adm-required-field"><?=GetMessage("SALE_YM_YANDEX_URL")?>:</span></td>
			<td><input type="text" name="YMSETTINGS[YANDEX_URL]" size="45" maxlength="255" value="<?=isset($siteSetts["YANDEX_URL"]) ? htmlspecialcharsbx($siteSetts["YANDEX_URL"]) : "https://api.partner.market.yandex.ru/v2/"?>"></td>
		</tr>
		<tr>
			<td  class="adm-detail-valign-top"><span class="adm-required-field"><?=GetMessage("SALE_YM_YANDEX_TOKEN")?>:</span></td>
			<td>
				<input type="text" name="YMSETTINGS[YANDEX_TOKEN]" size="45" maxlength="255" value="<?=isset($siteSetts["YANDEX_TOKEN"]) ? htmlspecialcharsbx($siteSetts["YANDEX_TOKEN"]) : ""?>">
				<br><small><?=GetMessage("SALE_YM_YANDEX_TOKEN_HELP")?></small>
			</td>
		</tr>
		<tr>
			<td  class="adm-detail-valign-top"><span class="adm-required-field"><?=GetMessage("SALE_YM_OAUTH_TOKEN")?>:</span></td>
			<td>
				<input type="text" name="YMSETTINGS[OAUTH_TOKEN]" size="45" maxlength="255" value="<?=isset($siteSetts["OAUTH_TOKEN"]) ? htmlspecialcharsbx($siteSetts["OAUTH_TOKEN"]) : ""?>">
				<br><small><?=GetMessage("SALE_YM_OAUTH_TOKEN_HELP")?></small>
			</td>
		</tr>
		<tr>
			<td  class="adm-detail-valign-top"><span class="adm-required-field"><?=GetMessage("SALE_YM_OAUTH_CLIENT_ID")?>:</span></td>
			<td>
				<input type="text" name="YMSETTINGS[OAUTH_CLIENT_ID]" size="45" maxlength="255" value="<?=isset($siteSetts["OAUTH_CLIENT_ID"]) ? htmlspecialcharsbx($siteSetts["OAUTH_CLIENT_ID"]) : ""?>">
				<br><small><?=GetMessage("SALE_YM_OAUTH_CLIENT_ID_HELP")?></small>
			</td>
		</tr>
		<tr>
			<td  class="adm-detail-valign-top"><span class="adm-required-field"><?=GetMessage("SALE_YM_OAUTH_LOGIN")?>:</span></td>
			<td>
				<input type="text" name="YMSETTINGS[OAUTH_LOGIN]" size="45" maxlength="255" value="<?=isset($siteSetts["OAUTH_LOGIN"]) ? htmlspecialcharsbx($siteSetts["OAUTH_LOGIN"]) : ""?>">
				<br><small><?=GetMessage("SALE_YM_OAUTH_LOGIN_HELP")?></small>
			</td>
		</tr>
		<tr>
			<td ><?=GetMessage("SALE_YM_PAYER_TYPE")?>:</td>
			<td>
				<select name="YMSETTINGS[PERSON_TYPE]" onchange="this.form.submit();">
					<?foreach ($arPersonTypes as $ptId => $ptName):?>
						<option value="<?=$ptId?>"<?=$personTypeId == $ptId ? " selected" : ""?>><?=htmlspecialcharsbx($ptName)?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<td  class="adm-detail-valign-top"><?=GetMessage("SALE_YM_AUTH_TYPE")?>:</td>
			<td>
				<select name="YMSETTINGS[AUTH_TYPE]">
					<option value="HEADER"<?=isset($siteSetts["AUTH_TYPE"]) && $siteSetts["AUTH_TYPE"] == "HEADER" ? " selected" : ""?>>HEADER</option>
					<option value="URL"<?=isset($siteSetts["AUTH_TYPE"]) && $siteSetts["AUTH_TYPE"] == "URL" ? " selected" : ""?>>URL</option>
				</select>
				<br><small><?=GetMessage("SALE_YM_AUTH_TYPE_HELP")?></small>
			</td>
		</tr>
		<tr>
			<td ><?=GetMessage("SALE_YM_DATA_FORMAT")?>:</td>
			<td>
				<select name="YMSETTINGS[DATA_FORMAT]" disabled>
					<option value="<?=CSaleYMHandler::JSON?>" selected>JSON</option>
					<option value="<?=CSaleYMHandler::XML?>">XML</option>
				</select>
				<br><small><?=GetMessage("SALE_YM_DATA_FORMAT_HELP")?></small>
			</td>
		</tr>
		<tr>
			<td ><?=GetMessage("SALE_YM_LOG_LEVEL")?>:</td>
			<td>
				<select name="YMSETTINGS[LOG_LEVEL]">
					<? $logLevel = isset($siteSetts["LOG_LEVEL"]) && $siteSetts["LOG_LEVEL"] ? $siteSetts["LOG_LEVEL"] : CSaleYMHandler::LOG_LEVEL_ERROR; ?>
					<option value="<?=CSaleYMHandler::LOG_LEVEL_ERROR?>"<?=$logLevel == CSaleYMHandler::LOG_LEVEL_ERROR ? " selected" : ""?>><?=GetMessage("SALE_YM_LOG_LEVEL_ERROR")?></option>
					<option value="<?=CSaleYMHandler::LOG_LEVEL_INFO?>"<?=$logLevel == CSaleYMHandler::LOG_LEVEL_INFO ? " selected" : ""?>><?=GetMessage("SALE_YM_LOG_LEVEL_INFO")?></option>
					<option value="<?=CSaleYMHandler::LOG_LEVEL_DEBUG?>"<?=$logLevel == CSaleYMHandler::LOG_LEVEL_DEBUG ? " selected" : ""?>><?=GetMessage("SALE_YM_LOG_LEVEL_DEBUG")?></option>
					<option value="<?=CSaleYMHandler::LOG_LEVEL_DISABLE?>"<?=$logLevel == CSaleYMHandler::LOG_LEVEL_DISABLE ? " selected" : ""?>><?=GetMessage("SALE_YM_LOG_LEVEL_DISABLE")?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td  class="adm-detail-valign-top"><?=GetMessage("SALE_YM_CHECK_HTTPS")?>:</td>
			<td>
				<input
					id="https_check_button"
					type="button"
					value="<?=GetMessage("SALE_YM_CHECK_HTTPS_BUT")?>"
					title="<?=GetMessage("SALE_YM_CHECK_HTTPS_TITLE")?>"
					onclick="
						var checkHTTPS = function(){
							BX.showWait();
							BX.ajax.post('<?=$APPLICATION->GetCurPage()?>', '<?=CUtil::JSEscape(bitrix_sessid_get())."&https_check=Y"?>', function (result){
								BX.closeWait();
								var res = eval( '('+result+')' );
								BX('https_check_result_<?=CUtil::JSEscape($SITE_ID)?>').innerHTML = '&nbsp;' + res['text'];

								BX.removeClass(BX('https_check_result_<?=CUtil::JSEscape($SITE_ID)?>'), 'https_check_success');
								BX.removeClass(BX('https_check_result_<?=CUtil::JSEscape($SITE_ID)?>'), 'https_check_fail');

								if (res['status'] == 'ok')
									BX.addClass(BX('https_check_result_<?=CUtil::JSEscape($SITE_ID)?>'), 'https_check_success');
								else
									BX.addClass(BX('https_check_result_<?=CUtil::JSEscape($SITE_ID)?>'), 'https_check_fail');
							});
						};
						checkHTTPS();"
					/>
				<span id="https_check_result_<?=CUtil::JSEscape($SITE_ID)?>"></span>
				<br><small><?=GetMessage("SALE_YM_CHECK_HTTPS_HELP")?></small>
			</td>
		</tr>


		<tr>
			<td  class="adm-detail-valign-top"><?echo GetMessage("SALE_YM_OUTLETS")?>:</td>
			<td id="OUTLETS_IDS_<?=htmlspecialcharsbx($SITE_ID)?>"><?
				if(isset($siteSetts["OUTLETS_IDS"]) && is_array($siteSetts["OUTLETS_IDS"]))
				{
					foreach ($siteSetts["OUTLETS_IDS"] as $outletId)
					{
						?><input type="text" name="YMSETTINGS[OUTLETS_IDS][]" size="10" value="<?=htmlspecialcharsbx($outletId)?>"><br><?
					}
				}
			?>
			<input type="text" name="YMSETTINGS[OUTLETS_IDS][]" size="10" value=""><br>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="button" value="<?=GetMessage("SALE_YM_OUTLETS_ADD_BUT")?>" onclick="addOutletIdField('YMSETTINGS[OUTLETS_IDS][]','<?=htmlspecialcharsbx($SITE_ID)?>');">
				<br><small><?=GetMessage("SALE_YM_OUTLETS_HELP")?></small>
			</td>
		</tr>
		<tr>
			<td  class="adm-detail-valign-top"><?=GetMessage("SALE_YM_ACCEPT_OLD_PRICE")?>:</td>
			<td>
				<select name="YMSETTINGS[IS_ACCEPT_OLD_PRICE]">
					<? $isAcceptOldPrice = isset($siteSetts["IS_ACCEPT_OLD_PRICE"]) ? $siteSetts["IS_ACCEPT_OLD_PRICE"] : CSaleYMHandler::NOT_ACCEPT_OLD_PRICE; ?>
					<option value="<?=CSaleYMHandler::NOT_ACCEPT_OLD_PRICE?>"<?=$isAcceptOldPrice == CSaleYMHandler::NOT_ACCEPT_OLD_PRICE ? " selected" : ""?>><?=GetMessage("SALE_YM_ACCEPT_OLD_PRICE_N")?></option>
					<option value="<?=CSaleYMHandler::ACCEPT_OLD_PRICE?>"<?=$isAcceptOldPrice == CSaleYMHandler::ACCEPT_OLD_PRICE ? " selected" : ""?>><?=GetMessage("SALE_YM_ACCEPT_OLD_PRICE_Y")?></option>
			</td>
		</tr>
		<tr>
			<td  class="adm-detail-valign-top"><?=GetMessage("SALE_YM_PERIOD")?>:</td>
			<td>
				<input type="text" name="YMSETTINGS[PERIOD_FROM]" size="10" maxlength="10" value="<?=isset($siteSetts["PERIOD_FROM"]) ? intval($siteSetts["PERIOD_FROM"]) : "7"?>">
				&nbsp;-&nbsp;
				<input type="text" name="YMSETTINGS[PERIOD_TO]" size="10" maxlength="10" value="<?=isset($siteSetts["PERIOD_TO"]) ? intval($siteSetts["PERIOD_TO"]) : "21"?>">
				<br><small><?=GetMessage("SALE_YM_PERIOD_NOTE")?></small>
			</td>
		</tr>

		<?$tabControl->BeginNextTab();?>

		<tr>
			<td width="40%"><?=GetMessage("SALE_YM_YANDEX")?>:</td>
			<td width="60%"><?=makeSelectorFromPaySystems("YMSETTINGS[PAY_SYSTEMS][YANDEX]", $siteSetts["PAY_SYSTEMS"]["YANDEX"], $personTypeId, $SITE_ID)?></td>
		</tr>
		<tr>
			<td ><?=GetMessage("SALE_YM_CASH_ON_DELIVERY")?>:</td>
			<td><?=makeSelectorFromPaySystems("YMSETTINGS[PAY_SYSTEMS][CASH_ON_DELIVERY]", $siteSetts["PAY_SYSTEMS"]["CASH_ON_DELIVERY"], $personTypeId, $SITE_ID)?></td>
		</tr>
		<tr>
			<td ><?=GetMessage("SALE_YM_CARD_ON_DELIVERY")?>:</td>
			<td><?=makeSelectorFromPaySystems("YMSETTINGS[PAY_SYSTEMS][CARD_ON_DELIVERY]", $siteSetts["PAY_SYSTEMS"]["CARD_ON_DELIVERY"], $personTypeId, $SITE_ID)?></td>
		</tr>

		<?$tabControl->BeginNextTab();?>

		<tr>
			<td colspan="2">
				<?=BeginNote();?>
				<?=GetMessage("SALE_YM_DELIVERY_NOTE")?>
				<?=EndNote();?>
			</td>
		</tr>

		<?foreach ($arDeliveryList as $deliveryId => $deliveryName):
			$selected = isset($siteSetts["DELIVERIES"][$deliveryId]) ? $siteSetts["DELIVERIES"][$deliveryId] : '';
		?>
			<tr>
				<td width="40%"><?=htmlspecialcharsbx($deliveryName)?>:</td>
				<td width="60%">
					<table>
					<tr>
						<td>
							<select name="YMSETTINGS[DELIVERIES][<?=$deliveryId?>]">
								<option value=""><?=GetMessage("SALE_YM_NOT_USE")?></option>
								<option value="DELIVERY"<?=$selected == "DELIVERY" ? " selected" : ""?>><?=GetMessage("SALE_YM_DELIVERY_DELIVERY")?></option>
								<option value="PICKUP"<?=$selected == "PICKUP" ? " selected" : ""?>><?=GetMessage("SALE_YM_DELIVERY_PICKUP")?></option>
								<option value="POST"<?=$selected == "POST" ? " selected" : ""?>><?=GetMessage("SALE_YM_DELIVERY_POST")?></option>
							</select>
						</td>
						<td>
							<table style="margin-left: 40px;">
								<?foreach(\CSaleYMHandler::getExistPaymentMethods() as $methodIdx => $method):?>
									<tr>
										<td><?=GetMessage("SALE_YM_DLV_PS_".$method)?></td><td><input type="checkbox" class="adm-sale-dlv-ps-methods" name="YMSETTINGS[DLV_PS][<?=$deliveryId?>][<?=$methodIdx?>]" value="Y"<?=$siteSetts['DLV_PS'][$deliveryId][$methodIdx] && $siteSetts['DLV_PS'][$deliveryId][$methodIdx] == 'N' ? '' : ' checked'?>></td>
									</tr>
								<?endforeach;?>
							</table>
						</td>
					</tr>
					</table>
				</td>
			</tr>
		<?endforeach;?>
		<?
			if(!isset($siteSetts["STATUS_IN"]))
			{
				$siteSetts["STATUS_IN"] = array(
					"UNPAID" => "N",
					"PROCESSING" => "N",
					"CANCELLED" => "CANCELED"
				);
			}
		?>

		<?$tabControl->BeginNextTab();?>

		<tr class="heading"><td colspan="2"><?=GetMessage("SALE_YM_STATUS_IN")?></td></tr>
		<tr><td width="40%"><?=GetMessage("SALE_YM_Y_STATUS_UNPAID")." [UNPAID]"?></td><td width="60%"><?=getSelectHtml("YMSETTINGS[STATUS_IN][UNPAID]", $statuses, $siteSetts["STATUS_IN"]["UNPAID"])?></td></tr>
		<tr><td><?=GetMessage("SALE_YM_Y_STATUS_PROCESSING")." [PROCESSING]"?></td><td><?=getSelectHtml("YMSETTINGS[STATUS_IN][PROCESSING]", $statuses, $siteSetts["STATUS_IN"]["PROCESSING"])?></td></tr>
		<tr><td><?=GetMessage("SALE_YM_Y_STATUS_CANCELLED")." [CANCELLED]"?></td><td><?=getSelectHtml("YMSETTINGS[STATUS_IN][CANCELLED]", $statuses, $siteSetts["STATUS_IN"]["CANCELLED"])?></td></tr>

		<?
		if(!isset($siteSetts["STATUS_OUT"]))
		{
			$siteSetts["STATUS_OUT"] = array(
				"CANCELED" => "CANCELLED",
				"ALLOW_DELIVERY" => "DELIVERY",
				"F" => "DELIVERED"
			);
		}
		?>
		<tr class="heading"><td colspan="2"><?=GetMessage("SALE_YM_STATUS_OUT")?></td></tr>
		<?foreach($statuses as $statusId => $statusName):?>
			<tr><td><?=$statusName?></td><td><?=getSelectHtml("YMSETTINGS[STATUS_OUT][".$statusId."]", $outYandexStatuses, $siteSetts["STATUS_OUT"][$statusId])?></td></tr>
		<?endforeach;?>

		<?$tabControl->BeginNextTab();?>

		<?foreach($requiredOrderProperties as $orderPropertyId):?>
			<tr>
				<td width="40%"><?=GetMessage("SALE_YM_ORDER_PROPS_".$orderPropertyId)?>:</td>
				<td width="60%">
					<?=getSelectHtml(
						"YMSETTINGS[ORDER_PROPS][".$orderPropertyId."]",
						$orderPropsList,
						isset($siteSetts["ORDER_PROPS"][$orderPropertyId]) ? $siteSetts["ORDER_PROPS"][$orderPropertyId] : $orderPropertyId,
						true
						)
					?>
				</td>
			</tr>
		<?endforeach;?>
	<?

	$tabControl->Buttons(array(
		"btnSave" => true,
		"btnApply" => false
	));
	echo '<input type="submit" name="YANDEX_MARKET_OFF" value="'.GetMessage("SALE_YM_OFF").'" title="'.GetMessage("SALE_YM_OFF_TITLE").'" onclick="return confirm(\''.GetMessage("SALE_YM_OFF_CONFIRM").'\')"/>';
	?>
	<?=bitrix_sessid_post();?>
	<?$tabControl->End();?>
	<script>
		function addOutletIdField(name, siteId)
		{
			BX('OUTLETS_IDS_'+siteId).appendChild(
				BX.create('input', {
					props: {
						name: name
					},
					attrs: {
						type: 'text',
						size: '10'
					}
				})
			);
			BX('OUTLETS_IDS_'+siteId).appendChild(
				BX.create('br')
			);
		}
		function correctCampaignId(e, input)
		{
			e = e || event;
			return (e.charCode <= 57 && e.charCode >= 49 && input.value.length <= 8) || e.charCode == 0
		}
	</script>
	<?
}
else //If integration with yandex market is not active
{
	echo BeginNote();
	echo GetMessage("SALE_YM_OFF_TEXT");
	echo EndNote();
	echo '<input type="submit" name="YANDEX_MARKET_ON" value="'.GetMessage("SALE_YM_ON").'" title="'.GetMessage("SALE_YM_ON_TITLE").'" onclick="return confirm(\''.GetMessage("SALE_YM_ON_CONFIRM").'\')"/>';
}
	?>
	</form>
	<?

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

function makeSelectorFromPaySystems($psTypeYandex, $psIdValue, $personTypeId, $siteId)
{
	static $paySystems = array();
	static $allPaySystems = null;

	if($allPaySystems === null)
	{
		$allPaySystems = array();

		$dbRes = \Bitrix\Sale\PaySystem\Manager::getList(array(
			'filter' => array('ACTIVE' => 'Y'),
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC'),
			'select' => array('ID', 'NAME')
		));

		while($ps = $dbRes->fetch())
			$allPaySystems[$ps['ID']] = htmlspecialcharsbx($ps['NAME']);
	}

	if(!isset($paySystems[$siteId]))
	{
		$paySystems[$siteId] = array();

		$dbRes = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
			'filter' => array(
				'=SERVICE_ID' => array_keys($allPaySystems),
				'=SERVICE_TYPE' => Restrictions\Manager::SERVICE_TYPE_PAYMENT,
				'=CLASS_NAME' => array(
					'\Bitrix\Sale\Services\PaySystem\Restrictions\Site',
					'\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType'
				)
			)
		));

		/** @var \Bitrix\Sale\Services\Base\Restriction $restriction */
		$rstParams = array();

		while($rstr = $dbRes->fetch())
			if(!empty($rstr["PARAMS"]) && is_array($rstr["PARAMS"]))
				$rstParams[$rstr['SERVICE_ID']][$rstr['CLASS_NAME']] = $rstr["PARAMS"];

		foreach($allPaySystems as $psId => $psName)
		{
			if(!empty($rstParams[$psId]['\Bitrix\Sale\Services\PaySystem\Restrictions\Site']['SITE_ID']))
				if(!in_array($siteId, $rstParams[$psId]['\Bitrix\Sale\Services\PaySystem\Restrictions\Site']['SITE_ID']))
					continue;

			if(!empty($rstParams[$psId]['\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType']['PERSON_TYPE_ID']))
				if(!in_array($personTypeId, $rstParams[$psId]['\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType']['PERSON_TYPE_ID']))
					continue;

			$paySystems[$siteId][] = $psId;
		}
	}

	return getSelectHtml(
		$psTypeYandex,
		array_intersect_key(
			$allPaySystems,
			array_flip($paySystems[$siteId])
		),
		$psIdValue
	);
}

function getSelectHtml($name, array $data, $selected = "", $bShowNotUse = true)
{
	if(!is_array($data) || empty($data))
		return "";

	$result = '<select name="'.htmlspecialcharsbx($name).'">';

	if($bShowNotUse)
		$result .= '<option value="">'.GetMessage("SALE_YM_NOT_USE").'</option>';

	foreach($data as $value => $title)
		$result .= '<option value="'.$value.'"'.($selected == $value ? " selected" : "").'>'.$title.'</option>';

	$result .= '</select>';

	return $result;
}
?>