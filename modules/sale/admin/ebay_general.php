<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\TradingPlatform\Helper;
use \Bitrix\Sale\TradingPlatform\Logger;
use \Bitrix\Sale\TradingPlatform\Ebay\Ebay;

Loc::loadMessages(__FILE__);

/** @var CMain $APPLICATION */

if ($APPLICATION->GetGroupRight("sale") < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"));

if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
	$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"));

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = Loc::getMessage("SALE_MODULE_NOT_INSTALLED");

$ebay = Ebay::getInstance();
$errorMessages = array();

if(!$ebay->isInstalled())
	$ebay->install();

$bSaved = false;
$backUrl = !empty($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : "";

if(isset($_POST["EBAY_ON"]))
{
	$ebay->setActive();

	if(strlen($backUrl) > 0)
		LocalRedirect($backUrl);
}
elseif(isset($_POST["EBAY_OFF"]))
{
	$ebay->unsetActive();
}

if($ebay->isActive())
{
	$siteList = array();
	$defaultSite = "";
	$rsSites = CSite::GetList($by = "sort", $order = "asc", Array("ACTIVE"=> "Y"));

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

	$settings = $ebay->getSettings();

	if(isset($_POST["EBAY_SETTINGS"]) && is_array($_POST["EBAY_SETTINGS"]))
	{
		foreach($_POST["EBAY_SETTINGS"]["IBLOCK_ID"] as $key => $iblockId)
			if(strlen($iblockId) <= 0)
				unset($_POST["EBAY_SETTINGS"]["IBLOCK_ID"][$key]);

		$site = !empty($_POST["SITE_ID_INITIAL"]) && $SITE_ID == $_POST["SITE_ID_INITIAL"] ? $SITE_ID : $_POST["SITE_ID_INITIAL"];

		if(!is_array($settings[$site]))
			$settings[$site] = array();

		$settings[$site] = array_merge($settings[$site], $_POST["EBAY_SETTINGS"]);
		$bSaved = $ebay->saveSettings($settings);
	}

	$siteSettings = $settings[$SITE_ID];
	unset ($settings);

	if(!\Bitrix\Main\Loader::includeModule('catalog'))
		$arResult["ERROR"] = Loc::getMessage("CATALOG_MODULE_NOT_INSTALLED");

	if(!is_array($siteSettings["IBLOCK_ID"]) || !isset($siteSettings["IBLOCK_ID"]))
		$siteSettings["IBLOCK_ID"] = array();

	$siteSettings["IBLOCK_ID"][] = "";

	$arPersonTypes = Helper::getPersonTypesList($SITE_ID);

	if(!empty($arPersonTypes))
	{
		if(isset($siteSettings["PERSON_TYPE"]) && array_key_exists($siteSettings["PERSON_TYPE"], $arPersonTypes))
		{
			$personTypeId= $siteSettings["PERSON_TYPE"];
		}
		else
		{
			reset($arPersonTypes);
			$personTypeId = $siteSettings["PERSON_TYPE"] = key($arPersonTypes);
		}

		$orderPropsList = Helper::getOrderPropsList($personTypeId);
		$requiredOrderProperties = Helper::getRequiredOrderProps();
		$bitrixStatuses = Helper::getBitrixStatuses($SITE_ID);

		$arTabs = array(
			array(
				"DIV" => "sale_ebay_main",
				"TAB" => Loc::getMessage("SALE_EBAY_TAB_MAIN"),
				"TITLE" => Loc::getMessage("SALE_EBAY_TAB_MAIN_TITLE"),
			),
			array(
				"DIV" => "sale_ebay_orderprops",
				"TAB" => Loc::getMessage("SALE_EBAY_TAB_MATCH"),
				"TITLE" =>Loc::getMessage("SALE_EBAY_TAB_MATCH_TITLE"),
			),
			array(
				"DIV" => "sale_ebay_connect",
				"TAB" => Loc::getMessage("SALE_EBAY_TAB_CONNECT"),
				"TITLE" => Loc::getMessage("SALE_EBAY_TAB_CONNECT_TITLE"),
			),
			array(
				"DIV" => "sale_ebay_categories",
				"TAB" => Loc::getMessage("SALE_EBAY_TAB_CATEGORIES"),
				"TITLE" => Loc::getMessage("SALE_EBAY_TAB_CATEGORIES_TITLE"),
			)
		);

		$tabControl = new CAdminTabControl("tabControl", $arTabs);
	}
	else
	{
		$errorMessages[] = Loc::getMessage('SALE_EBAY_TP_EMPTY_ERROR');
	}
}

$APPLICATION->SetTitle(GetMessage("SALE_EBAY_TITLE"));
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/ebay_admin.js", true);

require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");

if($bSaved)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("SALE_EBAY_SETTINGS_SAVED"), "TYPE"=>"OK"));

?>
<form method="post" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>" name="ebay_settings_form">
<?=bitrix_sessid_post();?>
<?

if(!empty($errorMessages))
{
	$adminMessage = new CAdminMessage(
		array("MESSAGE" => implode("<br>\n", $errorMessages), "TYPE" => "ERROR")
	);
	echo $adminMessage->Show();
}
elseif($ebay->isActive())
{
	?>
		<input type="hidden" name="SITE_ID_INITIAL" value="<?=$SITE_ID?>">
		<table width="100%"><tr>
			<td align="left">
				<?=Loc::getMessage("SALE_EBAY_SITE")?>: <?=CLang::SelectBox("SITE_ID", $SITE_ID, "", "this.form.submit();")?>
			</td>
			<td align="right">
				<img alt="eBay logo" src="/bitrix/images/sale/ebay/logo.png" style="width: 100px; height: 67px;">
			</td>
		</tr></table>
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_DOMAIN_NAME")?></span>:</td>
		<td width="60%"><input type="text" name="EBAY_SETTINGS[DOMAIN_NAME]" value="<?echo (strlen($siteSettings["DOMAIN_NAME"])>0) ? htmlspecialcharsbx($siteSettings["DOMAIN_NAME"]) : '' ?>" size="50" /> <input type="button" onclick="this.form['EBAY_SETTINGS[DOMAIN_NAME]'].value = window.location.host;" value="<?=Loc::getMessage("SALE_EBAY_DOMAIN_NAME_CURRENT")?>" /></td>
	</tr>
	<tr>
		<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_PAYER_TYPE")?>:</span></td>
		<td width="60%">
			<select name="EBAY_SETTINGS[PERSON_TYPE]" onchange="this.form.submit();">
				<?foreach ($arPersonTypes as $ptId => $ptName):?>
					<option value="<?=$ptId?>"<?=$personTypeId == $ptId ? " selected" : ""?>><?=htmlspecialcharsbx($ptName)?></option>
				<?endforeach;?>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage("SALE_EBAY_LOG_LEVEL")?>:</td>
		<td width="60%">
			<select name="EBAY_SETTINGS[LOG_LEVEL]">
				<? $logLevel = isset($siteSettings["LOG_LEVEL"]) && $siteSettings["LOG_LEVEL"] ? $siteSettings["LOG_LEVEL"] : Logger::LOG_LEVEL_ERROR; ?>
				<option value="<?=Logger::LOG_LEVEL_ERROR?>"<?=$logLevel == Logger::LOG_LEVEL_ERROR ? " selected" : ""?>><?=Loc::getMessage("SALE_EBAY_LOG_LEVEL_ERRORS")?></option>
				<option value="<?=Logger::LOG_LEVEL_INFO?>"<?=$logLevel == Logger::LOG_LEVEL_INFO ? " selected" : ""?>><?=Loc::getMessage("SALE_EBAY_LOG_LEVEL_INFO")?></option>
				<option value="<?=Logger::LOG_LEVEL_DEBUG?>"<?=$logLevel == Logger::LOG_LEVEL_DEBUG ? " selected" : ""?>><?=Loc::getMessage("SALE_EBAY_LOG_LEVEL_DEBUG")?></option>
				<option value="<?=Logger::LOG_LEVEL_DISABLE?>"<?=$logLevel == Logger::LOG_LEVEL_DISABLE ? " selected" : ""?>><?=Loc::getMessage("SALE_EBAY_LOG_LEVEL_DISABLE")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage("SALE_EBAY_EMAIL_ERRORS")?>:</td>
		<td width="60%"><input type="text" name="EBAY_SETTINGS[EMAIL_ERRORS]" size="45" maxlength="255" value="<?=isset($siteSettings["EMAIL_ERRORS"]) ? htmlspecialcharsbx($siteSettings["EMAIL_ERRORS"]) : ""?>"></td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage("SALE_EBAY_MAX_PRODUCT_QUANTITY")?>:</td>
		<td width="60%"><input type="text" name="EBAY_SETTINGS[MAX_PRODUCT_QUANTITY]" size="10" maxlength="10" value="<?=isset($siteSettings["MAX_PRODUCT_QUANTITY"]) ? intval($siteSettings["MAX_PRODUCT_QUANTITY"]) : "0"?>"></td>
	</tr>
	<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_EBAY_IBLOCK");?></td></tr>
	<tr>
		<td width="40%">&nbsp;</td>
		<td width="60%"><b><?=Loc::getMessage("SALE_EBAY_IBLOCK_HEADER")?>:</b></td>
	</tr>
	<tr>
		<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_IBLOCK_SELECT");?>:</span></td>
		<td width="60%">
			<div id="SALE_EBAY_IBLOCK_CHOOSE">
				<?for($i = 0; $i < count($siteSettings["IBLOCK_ID"]); $i++):?>
					<div  style="padding-top: 10px;">
						<?=GetIBlockDropDownListEx(
							$siteSettings["IBLOCK_ID"][$i],
							'EBAY_SETTINGS[IBLOCK_TYPE_ID]['.$i.']',
							'EBAY_SETTINGS[IBLOCK_ID]['.$i.']',
							array(
								'ID' => array_keys(
									Helper::getIblocksIds()
								),
								'ACTIVE' => 'Y',
								'CHECK_PERMISSIONS' => 'Y',
								'MIN_PERMISSION' => 'W',
								'SITE_ID' => $SITE_ID
							),
							'',
							'this.form.submit();'
						);
						?>&nbsp;
						<?=Helper::getBitrixCategoryPropsHtml("EBAY_SETTINGS[MORE_PHOTO_PROP][".$siteSettings["IBLOCK_ID"][$i]."]", $siteSettings["IBLOCK_ID"][$i],0, $siteSettings["MORE_PHOTO_PROP"][$siteSettings["IBLOCK_ID"][$i]])?>
					</div>
				<?endfor;?>
			</div>
			<input type="button" value="<?=Loc::getMessage("SALE_EBAY_IBLOCK_ADD");?>" onclick='BX.Sale.EbayAdmin.addIblockSelect();' style="margin-top: 10px;">
		</td>
	</tr>
	<?
	$tabControl->BeginNextTab();
	?>
	<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_EBAY_ORDERPROPS")?></td></tr>
	<?foreach($requiredOrderProperties as $orderPropertyCode):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_EBAY_ORDER_PROPS_".$orderPropertyCode)?>:</td>
			<td width="60%">
				<select name="EBAY_SETTINGS[ORDER_PROPS][<?=$orderPropertyCode?>]">
				<?
					$propIdForCode = 0;
					if(isset($siteSettings["ORDER_PROPS"][$orderPropertyCode]))
					{
						$propIdForCode = $siteSettings["ORDER_PROPS"][$orderPropertyCode];
					}
					else
					{
						foreach($orderPropsList as $propParams)
						{
							if($propParams["CODE"] == $orderPropertyCode)
							{
								$propIdForCode = $propParams["ID"];
								break;
							}
						}
					}
				?>
					<option value="0"><?=Loc::getMessage("SALE_EBAY_NOT_USE")?></option>
					<?foreach($orderPropsList as $propParams):?>
						<option	value="<?=$propParams["ID"]?>"<?=$propIdForCode > 0 && $propIdForCode == $propParams["ID"] ? " selected" : ""?>><?=htmlspecialcharsbx($propParams["NAME"])?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
	<?endforeach;?>

	<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_EBAY_STATUSES")?></td></tr>
	<tr>
		<td width="40%"><b><?=Loc::getMessage("SALE_EBAY_STATUSES_EBAY")?>:</b></td>
		<td width="60%"><b><?=Loc::getMessage("SALE_EBAY_STATUSES_BITRIX")?>:</b></td>
	</tr>
	<?foreach(\Bitrix\Sale\TradingPlatform\Ebay\Helper::getEbayOrderStatuses() as $ebayStatus):?>
		<tr>
			<td width="40%">[<?=$ebayStatus?>]:</td>
			<td width="60%"><?=Helper::getSelectHtml("EBAY_SETTINGS[STATUS_MAP][".$ebayStatus."]", $bitrixStatuses, $siteSettings["STATUS_MAP"][$ebayStatus])?></td>
		</tr>
	<?endforeach;?>
	<tr>
		<td width="40%" class = "adm-detail-valign-top"><?=Loc::getMessage("SALE_EBAY_ORDER_IS_READY")?>:</td>
		<td width="60%"><?=Helper::getSelectHtml("EBAY_SETTINGS[ORDER_READY_MAP]", $bitrixStatuses, $siteSettings["ORDER_READY_MAP"])?>
		<br><small><?=Loc::getMessage("SALE_EBAY_PARAMS_COMBINATION")?>.<br>
			CheckoutStatus.Status == "Complete" && CheckoutStatus.eBayPaymentStatus == "NoPaymentFailure"	&& PaymentClearedTime != null/empty
			</small>
		</td>
	</tr>
	<?
	$tabControl->BeginNextTab();
	?>
	<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_EBAY_API")?></td></tr>
	<tr>
		<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_API_AUTH_TOKEN")?>:</span></td>
		<td width="60%">
			<textarea id="SALE_EBAY_SETTINGS_API_TOKEN" name="EBAY_SETTINGS[API][AUTH_TOKEN]" cols="45" rows="7"><?=isset($siteSettings["API"]["AUTH_TOKEN"]) ? htmlspecialcharsbx($siteSettings["API"]["AUTH_TOKEN"]) : ""?></textarea>
		</td>
	</tr>
	<tr>
		<td width="40%"><span><?=Loc::getMessage("SALE_EBAY_API_AUTH_TOKEN_EXP")?>:</span></td>
		<td width="60%">
			<input id="SALE_EBAY_SETTINGS_API_TOKEN_EXP" type="text" name="EBAY_SETTINGS[API][AUTH_TOKEN_EXP]" size="20" value="<?=isset($siteSettings["API"]["AUTH_TOKEN_EXP"]) ? htmlspecialcharsbx($siteSettings["API"]["AUTH_TOKEN_EXP"]) : ""?>" readonly>
			<input type="hidden" name="EBAY_SETTINGS[API][SITE_ID]" value="215">
		</td>
	</tr>
	<tr>
		<td width="40%"><span>&nbsp;</span></td>
		<td width="60%">
			<input type="button" value="<?=Loc::getMessage("SALE_EBAY_GET_TOKEN")?>" onclick="window.open('<?=Ebay::getApiTokenUrl()?>', 'gettingToken');">
			<script>BX.Sale.EbayAdmin.addApiTokenListener({
					messageOk: "<?=Loc::getMessage('SALE_EBAY_GET_API_TOKEN_OK')?>",
					messageError: "<?=Loc::getMessage('SALE_EBAY_GET_API_TOKEN_ERROR')?>"
				});
			</script>

		</td>
	</tr>
	<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_EBAY_SFTP")?></td></tr>
	<tr>
		<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_SFTP_HOST_PORT");?></span></td>
		<td width="60%">
			<input type="text" name="EBAY_SETTINGS[SFTP_HOST]" size="30" maxlength="255" value="<?=isset($siteSettings["SFTP_HOST"]) ? htmlspecialcharsbx($siteSettings["SFTP_HOST"]) : "mip.ebay.com"?>">&nbsp:&nbsp;
			<input type="text" name="EBAY_SETTINGS[SFTP_PORT]" size="10" maxlength="255" value="<?=isset($siteSettings["SFTP_PORT"]) ? htmlspecialcharsbx($siteSettings["SFTP_PORT"]) : "22"?>">
		</td>
	</tr>
	<tr>
		<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_SFTP_HOST_FINGERPRINT");?>:</span></td>
		<td width="60%"><input type="text" name="EBAY_SETTINGS[SFTP_HOST_FINGERPRINT]" size="45" maxlength="255" value="<?=isset($siteSettings["SFTP_HOST_FINGERPRINT"]) ? htmlspecialcharsbx($siteSettings["SFTP_HOST_FINGERPRINT"]) : "DD1FEE728C2E1FF2AACC2724929C3CF1"?>"></td>
	</tr>
	<tr>
		<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_SFTP_LOGIN");?>:</span></td>
		<td width="60%"><input id="SALE_EBAY_SETTINGS_SFTP_USER_NAME" type="text" name="EBAY_SETTINGS[SFTP_LOGIN]" size="45" maxlength="255" value="<?=isset($siteSettings["SFTP_LOGIN"]) ? htmlspecialcharsbx($siteSettings["SFTP_LOGIN"]) : ""?>"></td>
	</tr>
	<tr>
		<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_SFTP_PASS")?>:</span></td>
		<td width="60%"><textarea id="SALE_EBAY_SETTINGS_SFTP_TOKEN" name="EBAY_SETTINGS[SFTP_PASS]" cols="45" rows="4"><?=isset($siteSettings["SFTP_PASS"]) ? htmlspecialcharsbx($siteSettings["SFTP_PASS"]) : ""?></textarea></td>
	</tr>
	<tr>
		<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_SFTP_PASS_EXP")?>:</span></td>
		<td width="60%"><input id="SALE_EBAY_SETTINGS_SFTP_TOKEN_EXP" name="EBAY_SETTINGS[SFTP_TOKEN_EXP]" value="<?=isset($siteSettings["SFTP_TOKEN_EXP"]) ? htmlspecialcharsbx($siteSettings["SFTP_TOKEN_EXP"]) : ""?>" readonly size="30"></td>
	</tr>
	<tr>
		<td width="40%"><span>&nbsp;</span></td>
		<td width="60%">
			<input type="button" value="<?=Loc::getMessage("SALE_EBAY_GET_SFTP_TOKEN")?>" onclick="window.open('<?=$ebay->getSftpTokenUrl($siteSettings["SFTP_LOGIN"])?>', 'gettingOAuthToken');">
			<script>BX.Sale.EbayAdmin.addSftpTokenEventListener({
						messageOk: "<?=Loc::getMessage('SALE_EBAY_GET_SFTP_OK')?>",
						messageError: "<?=Loc::getMessage('SALE_EBAY_GET_SFTP_ERROR')?>",
						submit: false
					});
			</script>
		</td>
	</tr>
	<?

	$tabControl->BeginNextTab();

	$res = \Bitrix\Catalog\CatalogIblockTable::getList(array(
		'select' => array('IBLOCK_ID', 'SITE_ID' => 'IBLOCK_SITE.SITE_ID'),
		'filter' => array('SITE_ID' => $SITE_ID),
		'runtime' => array(
			'IBLOCK_SITE' => array(
				'data_type' => 'Bitrix\Iblock\IblockSiteTable',
				'reference' => array(
					'ref.IBLOCK_ID' => 'this.IBLOCK_ID',
				),
				'join_type' => 'inner'
			)
		)
	));

	$maps = array();

	while($ib = $res->fetch())
	{
		$entityId = \Bitrix\Sale\TradingPlatform\Ebay\MapHelper::getCategoryEntityId($ib["IBLOCK_ID"]);

		$mapRes = \Bitrix\Sale\TradingPlatform\MapTable::getList(array(
			'select' => array(
				'VALUE_EXTERNAL', 'VALUE_INTERNAL',
				'CATEGORY_EBAY_NAME' => 'CATEGORY_EBAY.NAME',
				'CATEGORY_BITRIX_NAME' => 'CATEGORY_BITRIX.NAME',
				'IBLOCK_ID' => 'CATEGORY_BITRIX.IBLOCK_ID'
			),
			'filter' => array(
				'=ENTITY_ID' => $entityId
			),
			'order' => array(
				'CATEGORY_BITRIX_NAME' => 'ASC',
				'CATEGORY_EBAY_NAME' => 'ASC'
			),
			'runtime' => array(
				new \Bitrix\Main\Entity\ReferenceField('CATEGORY_EBAY', '\Bitrix\Sale\TradingPlatform\Ebay\CategoryTable',
					array('=this.VALUE_EXTERNAL' => 'ref.CATEGORY_ID')
				),
				new \Bitrix\Main\Entity\ReferenceField('CATEGORY_BITRIX', '\Bitrix\Iblock\SectionTable',
					array(
						'=this.VALUE_INTERNAL' => 'ref.ID',
					)
				)
			)
		));

		$maps = array_merge($maps, $mapRes->fetchAll());
	}

	?>
	<tr><td colspan="2" align="center">
	<table class="adm-detail-content-table edit-table" style="opacity: 1;">
	<tr><td colspan="2" align="center">

	<table border="0" cellpadding="0" cellspacing="0" class="internal" style="width:80%;">
	<tr class="heading"><td><?=Loc::getMessage("SALE_EBAY_CAT_BITRIX_NAME")?></td><td><?=Loc::getMessage("SALE_EBAY_CAT_EBAY_NAME")?></td></tr>
	<?
	if(!empty($maps))
	{
		foreach($maps as $map)
		{
			?><tr>
			<td>
				<a
					href="/bitrix/admin/cat_section_edit.php?IBLOCK_ID=<?=$map["IBLOCK_ID"]?>&type=catalog&ID=<?=$map["VALUE_INTERNAL"]?>&lang=<?=LANGUAGE_ID?>&find_section_section=0&form_section_2_active_tab=SALE_TRADING_PLATFORM_edit_trading_platforms"
					title="<?=Loc::getMessage("SALE_EBAY_CAT_SETT_EDIT")?>"
				>
					<?=htmlspecialcharsbx($map["CATEGORY_BITRIX_NAME"])?></a> [<?=htmlspecialcharsbx($map["VALUE_INTERNAL"])?>]
			</td>
			<td>
				<?=htmlspecialcharsbx($map["CATEGORY_EBAY_NAME"])?> [<?=htmlspecialcharsbx($map["VALUE_EXTERNAL"])?>]
			</td>
			</tr><?
		}
	}
	else
	{
		?><tr><td colspan="2"><?=Loc::getMessage("SALE_EBAY_CAT_MAP_EMPTY")?></td></tr><?
	}
	?>
	</table>
	</td></tr>

	</table>
	</td></tr>
	<?

	$tabControl->Buttons(array(
		"btnSave" => true,
		"btnApply" => false
	));

	echo '<input type="submit" name="EBAY_OFF" value="'.GetMessage("SALE_EBAY_OFF").'" title="'.GetMessage("SALE_EBAY_OFF_TITLE").'" onclick="return confirm(\''.GetMessage("SALE_EBAY_OFF_CONFIRM").'\')"/>';
	$tabControl->End();
}
else //If integration with ebay is not active
{
	$res = Bitrix\Sale\TradingPlatform\Ebay\Helper::checkEnveronment();

	if($res->isSuccess())
	{
		echo BeginNote();
		echo GetMessage("SALE_EBAY_OFF_TEXT");
		echo EndNote();
		echo '<input type="submit" name="EBAY_ON" value="'.GetMessage("SALE_EBAY_ON").'" title="'.GetMessage("SALE_EBAY_ON_TITLE").'" onclick="return confirm(\''.GetMessage("SALE_EBAY_ON_CONFIRM").'\')"/>';

		if(strlen($backUrl) > 0)
			echo '<input type="hidden" name="back_url" value="'.htmlspecialcharsbx($backUrl).'">';
	}
	else
	{
		foreach($res->getErrors() as $error)
			CAdminMessage::ShowMessage(array("MESSAGE"=>$error->getMessage(), "TYPE"=>"ERROR"));
	}
}
?>
</form>
<?

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

