<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\TradingPlatform\Helper;

Loc::loadMessages(__FILE__);

/** @var CMain $APPLICATION */

if ($APPLICATION->GetGroupRight("sale") < "W")
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));

if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
	$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"));

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = Loc::getMessage("SALE_MODULE_NOT_INSTALLED");

$ebay = \Bitrix\Sale\TradingPlatform\Ebay\Ebay::getInstance();

if(!$ebay->isActive())
	LocalRedirect("/bitrix/admin/sale_ebay_general.php?lang=".LANG."&back_url=".urlencode($APPLICATION->GetCurPageParam()));

$errorMsg = "";
$bSaved = false;

$siteList = array();
$defaultSite = "";
$rsSites = CSite::GetList("sort", "asc", Array("ACTIVE"=> "Y"));


while($arRes = $rsSites->Fetch())
{
	$siteList[$arRes['ID']] = $arRes['NAME'];

	if($arRes["DEF"] == "Y")
		$defaultSite = $arRes['ID'];
}

if(isset($_POST["SITE_ID"]) && array_key_exists($_POST["SITE_ID"], $siteList))
	$SITE_ID = $_POST["SITE_ID"];
else
	$SITE_ID = $defaultSite;

$settings = $ebay->getSettings();

if(isset($_POST["EBAY_SETTINGS"]) && is_array($_POST["EBAY_SETTINGS"]))
{
	$site = !empty($_POST["SITE_ID_INITIAL"]) && $SITE_ID == $_POST["SITE_ID_INITIAL"] ? $SITE_ID : $_POST["SITE_ID_INITIAL"];

	if(!is_array($settings[$site]))
		$settings[$site] = array();

	$settings[$site] = array_merge($settings[$site], $_POST["EBAY_SETTINGS"]);
	$bSaved = $ebay->saveSettings($settings);
}

if(!isset($settings[$SITE_ID]))
	LocalRedirect("/bitrix/admin/sale_ebay_general.php?lang=".LANG."&SITE_ID=".$SITE_ID."&back_url=".urlencode($APPLICATION->GetCurPageParam()));

$siteSettings = $settings[$SITE_ID];
$details = new \Bitrix\Sale\TradingPlatform\Ebay\Api\Details($SITE_ID);
unset ($settings);

$arDeliveryList = Helper::getDeliveryList($SITE_ID);

$arTabs = array(
	array(
		"DIV" => "policy_default",
		"TAB" => Loc::getMessage("SALE_EBAY_TAB_DEFAULT"),
		"TITLE" => Loc::getMessage("SALE_EBAY_TAB_DEFAULT_TITLE")
	),
	array(
		"DIV" => "policy_payment",
		"TAB" => Loc::getMessage("SALE_EBAY_TAB_PAYMENT"),
		"TITLE" => Loc::getMessage("SALE_EBAY_TAB_PAYMENT_TITLE")
	),
	array(
		"DIV" => "policy_shipping",
		"TAB" => Loc::getMessage("SALE_EBAY_TAB_SHIPPING"),
		"TITLE" => Loc::getMessage("SALE_EBAY_TAB_SHIPPING_TITLE")
	)
);

$tabControl = new CAdminTabControl("tabControl", $arTabs);
$policy = null;

if(isset($siteSettings["API"]["AUTH_TOKEN"]) && $siteSettings["API"]["AUTH_TOKEN"] <> '')
	$policy = new \Bitrix\Sale\TradingPlatform\Ebay\Policy($siteSettings["API"]["AUTH_TOKEN"], $SITE_ID);
elseif(!isset($siteSettings["API"]["AUTH_TOKEN"]) || $siteSettings["API"]["AUTH_TOKEN"] == '')
	$errorMsg = "You must set API token first!\n";

$APPLICATION->SetTitle(Loc::getMessage("SALE_EBAY_TITLE"));

require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");

if($errorMsg <> '')
	CAdminMessage::ShowMessage(array("MESSAGE"=>$errorMsg, "TYPE"=>"ERROR"));

if($bSaved)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("SALE_EBAY_SETTINGS_SAVED"), "TYPE"=>"OK"));

?>
<form method="post" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>" name="ebay_policysettings_form">
<?=bitrix_sessid_post();?>
<input type="hidden" name="SITE_ID_INITIAL" value="<?=$SITE_ID?>">
<table width="100%">
	<tr>
		<td align="left">
			<?=Loc::getMessage("SALE_EBAY_SITE")?>: <?=CLang::SelectBox("SITE_ID", $SITE_ID, "", "this.form.submit();")?>
		</td>
		<td align="right">
			<img alt="eBay logo" src="/bitrix/images/sale/ebay/logo.png" style="width: 100px; height: 67px;">
		</td>
	</tr>
</table>
<?

$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_EBAY_DEFAULT")?></td></tr>
<tr>
	<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_TAB_POLICY_RETURN")?>:</span></td>
	<td width="60%">
		<?$hiddenPolicyFields = "";?>
		<select name="EBAY_SETTINGS[POLICY][RETURN][DEFAULT]">
			<?if($policy):?>
				<?foreach($policy->getPoliciesNames(\Bitrix\Sale\TradingPlatform\Ebay\Policy::TYPE_RETURN) as $policyId => $policyName):?>
					<option value="<?=htmlspecialcharsbx($policyId)?>"<?=isset($siteSettings["POLICY"]["RETURN"]["DEFAULT"]) && $siteSettings["POLICY"]["RETURN"]["DEFAULT"] == $policyId ? " selected" : ""?>><?=$policyName?></option>
					<?$hiddenPolicyFields .= ' <input type="hidden" name="EBAY_SETTINGS[POLICY][RETURN][LIST]['.$policyId.']" value="'.htmlspecialcharsbx($policyName).'">'?>
				<?endforeach;?>
			<?endif;?>
		</select>
		<?=$hiddenPolicyFields?>
	</td>
</tr>
<tr>
	<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_TAB_POLICY_SHIPPING")?>:</span></td>
	<td width="60%">
		<?$hiddenPolicyFields = "";?>
		<select name="EBAY_SETTINGS[POLICY][SHIPPING][DEFAULT]">
			<?if($policy):?>
				<?foreach($policy->getPoliciesNames(\Bitrix\Sale\TradingPlatform\Ebay\Policy::TYPE_SHIPPING) as $policyId => $policyName):?>
					<option value="<?=htmlspecialcharsbx($policyId)?>"<?=isset($siteSettings["POLICY"]["SHIPPING"]["DEFAULT"]) && $siteSettings["POLICY"]["SHIPPING"]["DEFAULT"] == $policyId ? " selected" : ""?>><?=$policyName?></option>
					<?$hiddenPolicyFields .= ' <input type="hidden" name="EBAY_SETTINGS[POLICY][SHIPPING][LIST]['.$policyId.']" value="'.htmlspecialcharsbx($policyName).'">'?>
				<?endforeach;?>
			<?endif;?>
		</select>
		<?=$hiddenPolicyFields?>
	</td>
</tr>
<tr>
	<td width="40%"><span class="adm-required-field"><?=Loc::getMessage("SALE_EBAY_TAB_POLICY_PAYMENT")?>:</span></td>
	<td width="60%">
		<?$hiddenPolicyFields = "";?>
		<select name="EBAY_SETTINGS[POLICY][PAYMENT][DEFAULT]">
			<?if($policy):?>
				<?foreach($policy->getPoliciesNames(\Bitrix\Sale\TradingPlatform\Ebay\Policy::TYPE_PAYMENT) as $policyId => $policyName):?>
					<option value="<?=htmlspecialcharsbx($policyId)?>"<?=isset($siteSettings["POLICY"]["PAYMENT"]["DEFAULT"]) && $siteSettings["POLICY"]["PAYMENT"]["DEFAULT"] == $policyId ? " selected" : ""?>><?=$policyName?></option>
					<?$hiddenPolicyFields .= ' <input type="hidden" name="EBAY_SETTINGS[POLICY][PAYMENT][LIST]['.$policyId.']" value="'.htmlspecialcharsbx($policyName).'">'?>
				<?endforeach;?>
			<?endif;?>
		</select>
		<?=$hiddenPolicyFields?>
	</td>
</tr>
<?

$tabControl->BeginNextTab();
if($details)
{
	foreach($details->getListPayments() as $paymentOption =>  $paymentDescription)
	{
		if(!is_array($siteSettings["MAPS"]["PAYMENT"]))
			$siteSettings["MAPS"]["PAYMENT"] = array();
		?>
		<tr>
			<td width="40%"><?=$paymentDescription?>:</td>
			<td width="60%"><?=
				Helper::makeSelectorFromPaySystems(
					"EBAY_SETTINGS[MAPS][PAYMENT][".$paymentOption."]",
					$siteSettings["MAPS"]["PAYMENT"][$paymentOption],
					$siteSettings["PERSON_TYPE"]
				)
				?></td>
		</tr>
	<?
	}
}

$tabControl->BeginNextTab();
	if($details)
	{
		foreach($details->getListShipping() as $service =>  $serviceDescription)
		{
			?>
			<tr>
				<td width="40%"><?=$serviceDescription?>:</td>
				<td width="60%">
					<select name="EBAY_SETTINGS[MAPS][SHIPMENT][<?=$service?>]">
						<option value=""><?=Loc::getMessage("SALE_EBAY_NOT_MAPPED")?></option>
						<?foreach($arDeliveryList as $deliveryId => $deliveryName):?>
							<option value="<?=$deliveryId?>"<?=(isset($siteSettings["MAPS"]["SHIPMENT"][$service]) && $siteSettings["MAPS"]["SHIPMENT"][$service] ==  $deliveryId ? " selected" : "")?>><?=htmlspecialcharsbx($deliveryName)?></option>
						<?endforeach;?>
					</select>
				</td>
			</tr>
			<?
		}
	}

$tabControl->Buttons(array(
	"btnSave" => true,
	"btnApply" => false
));

$tabControl->End();

?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

