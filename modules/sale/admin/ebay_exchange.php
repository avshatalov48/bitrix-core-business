<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\TradingPlatform\Helper;
use Bitrix\Sale\TradingPlatform\Xml2Array;

Loc::loadMessages(__FILE__);

/** @var CMain $APPLICATION */

if ($APPLICATION->GetGroupRight("sale") < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"));

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

	$_POST["EBAY_SETTINGS"]["FEEDS"] = \Bitrix\Sale\TradingPlatform\Ebay\Agent::update($site, $_POST["EBAY_SETTINGS"]["FEEDS"]);

	if(is_array($settings[$site]))
	{
		$settings[$site] = array_merge($settings[$site], $_POST["EBAY_SETTINGS"]);
		$bSaved = $ebay->saveSettings($settings);
	}
	else
	{
		$errorMsg .= Loc::getMessage(
			'SALE_EBAY_SETTINGS_SAVING_SITE_ERROR',
			array(
				'#A1#' => '<a href="/bitrix/admin/sale_ebay_wizard.php?lang='.LANGUAGE_ID.'&STEP=1&SITE_ID='.$site.'">',
				'#A2#' => '</a>',
				'#S#' => $siteList[$site]
			)
		);
	}
}

$siteSettings = $settings[$SITE_ID];
unset ($settings);

$ebayCategoriesCount = 0;
$ebayCategoriesUpdateDate = "";

$res = \Bitrix\Sale\TradingPlatform\Ebay\CategoryTable::getList(array(
	"select" => array("CNT", "LAST_UPDATE"),
	"runtime" => array(
		new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'),
		new \Bitrix\Main\Entity\ExpressionField('LAST_UPDATE', 'MAX(LAST_UPDATE)')
	)
));

if($cat = $res->fetch())
{
	if(!empty($cat["CNT"]))
		$ebayCategoriesCount = $cat["CNT"];

	if(!empty($cat["LAST_UPDATE"]))
		$ebayCategoriesUpdateDate = $cat["LAST_UPDATE"]->toString();
}

$ebayCategoriesVars = 0;

$res = \Bitrix\Sale\TradingPlatform\Ebay\CategoryVariationTable::getList(array(
	"select" => array("CNT"),
	"runtime" => array(
		new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'),
	)
));

if($var = $res->fetch())
	$ebayCategoriesVars = $var["CNT"];

$defaultFeedIntervals = \Bitrix\Sale\TradingPlatform\Helper::getDefaultFeedIntervals();

$res = \Bitrix\Sale\TradingPlatform\Ebay\Feed\ResultsTable::getList(array(
	"select" => array("FEED_TYPE", "MAX_UPLOAD_TIME"),
	"group" => array("FEED_TYPE"),
	"runtime" => array(
		new \Bitrix\Main\Entity\ExpressionField('MAX_UPLOAD_TIME', 'MAX(UPLOAD_TIME)'),
	)
));

$results = array();

while($lastUpdates = $res->fetch())
	$results[$lastUpdates["FEED_TYPE"]] = $lastUpdates["MAX_UPLOAD_TIME"];

$filter = array("LOGIC" => "OR");

foreach($results as $feedType => $uploadTime)
{
	$filter[] = array(
		"FEED_TYPE" => $feedType,
		"UPLOAD_TIME" => $uploadTime
	);
}

$res = \Bitrix\Sale\TradingPlatform\Ebay\Feed\ResultsTable::getList(array(
	'filter' => $filter
));

$results = array();

while($lastUpdates = $res->fetch())
	$results[$lastUpdates["FEED_TYPE"]] = $lastUpdates;

$arTabs = array(
	array(
		"DIV" => "ebay_exchange",
		"TAB" => Loc::getMessage("SALE_EBAY_TAB_EXCHANGE"),
		"TITLE" => Loc::getMessage("SALE_EBAY_TAB_EXCHANGE_DESCR")
	),
	array(
		"DIV" => "ebay_meta",
		"TAB" => Loc::getMessage("SALE_EBAY_TAB_META"),
		"TITLE" => Loc::getMessage("SALE_EBAY_TAB_META_DESCR")
	)
);

$tabControl = new CAdminTabControl("tabControl", $arTabs);
$policy = null;

$APPLICATION->SetTitle(Loc::getMessage("SALE_EBAY_TITLE"));
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/ebay_admin.js", true);

require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");

if($errorMsg <> '')
	CAdminMessage::ShowMessage(array("MESSAGE"=>$errorMsg, "TYPE"=>"ERROR", "HTML" => true));

if($bSaved)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("SALE_EBAY_SETTINGS_SAVED"), "TYPE"=>"OK"));

?>
<form method="post" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>" name="ebay_exhangesettings_form">
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
	<?foreach(array("PRODUCT", "INVENTORY", "ORDER") as $feedType): //"IMAGE",?>
		<? $smallFeedType = mb_strtolower($feedType);?>
		<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_EBAY_FEED_".$feedType)?></td></tr>
		<tr>
			<td width="40%"><span><?=Loc::getMessage("SALE_EBAY_FEED_INTERVAL")?>:</span></td>
			<td width="60%">
				<input type="text" name="EBAY_SETTINGS[FEEDS][<?=$feedType?>][INTERVAL]" size="5" maxlength="255" value="<?=isset($siteSettings["FEEDS"][$feedType]["INTERVAL"]) ? htmlspecialcharsbx($siteSettings["FEEDS"][$feedType]["INTERVAL"]) : $defaultFeedIntervals[$feedType]?>">
				<input type="hidden" name="EBAY_SETTINGS[FEEDS][<?=$feedType?>][AGENT_ID]" value="<?=isset($siteSettings["FEEDS"][$feedType]["AGENT_ID"]) ? htmlspecialcharsbx($siteSettings["FEEDS"][$feedType]["AGENT_ID"]) : 0?>">
			</td>
		</tr>
		<tr>
			<td><span><?=Loc::getMessage("SALE_EBAY_FEED_LAST_EXCAHGE")?>:</span></td>
			<td>
				<?=!empty($results[$smallFeedType]["UPLOAD_TIME"]) ? $results[$smallFeedType]["UPLOAD_TIME"] : "-"?>
			</td>
		</tr>
		<tr>
			<td><?=Loc::getMessage("SALE_EBAY_FEED_LAST_EXCAHGE_RES")?>:</td>
			<td >
				<?
					$feedResMess = "";
					$feedResErrDescr = "";

					if(empty($results[$smallFeedType]["RESULTS"]))
					{
						$feedResMess = "-";
					}
					else
					{
						$tmp = Xml2Array::convert($results[$smallFeedType]["RESULTS"], false);

						if(mb_strpos($results[$smallFeedType]["RESULTS"], "<Errors>") !== false)
						{
							$feedResMess = '<span style="color: red; font-weight: bold;">'.Loc::getMessage("SALE_EBAY_RES_ERROR").'</span>';

							if(isset($tmp["ProductResult"]))
							{
								$productResults = Xml2Array::normalize($tmp["ProductResult"]);

								foreach($productResults as $productResult)
								{
									if(isset($productResult["Errors"]["Error"]))
									{
										$errors = Xml2Array::normalize($productResult["Errors"]["Error"]);

										foreach($errors as $error)
											$feedResErrDescr .= Loc::getMessage("SALE_EBAY_ERROR").": ".$error["Message"]." ".Loc::getMessage("SALE_EBAY_CODE").": ".$error["Code"]."<br>\n";
									}
								}
							}
						}

						if(mb_strpos($results[$smallFeedType]["RESULTS"], "<Warnings>") !== false)
						{
							if($feedResMess <> '')
								$feedResMess .= ", ";

							$feedResMess .= '<span style="color: orange; font-weight: bold;">'.Loc::getMessage("SALE_EBAY_RES_WARNING").'</span>';

							$feedResErrDescr .="<br>\n";

							if(isset($tmp["ProductResult"]))
							{
								$productResults = Xml2Array::normalize($tmp["ProductResult"]);

								foreach($productResults as $productResult)
								{
									if(isset($productResult["Warnings"]["Warning"]))
									{
										$warnings = Xml2Array::normalize($productResult["Warnings"]["Warning"]);

										foreach($warnings as $warning)
											$feedResErrDescr .= Loc::getMessage("SALE_EBAY_CODE").Loc::getMessage("SALE_EBAY_WARNING").": ".$warning["Message"]." ".Loc::getMessage("SALE_EBAY_CODE").": ".$warning["Code"]."<br>\n";
									}
								}
							}
						}
					}

					if($feedResMess == "")
						$feedResMess = '<span style="color: green; font-weight: bold;">'.Loc::getMessage("SALE_EBAY_RES_SUCCESS").'</span>';

					echo $feedResMess;
				?>

			</td>
		</tr>
		<?if($feedResErrDescr <> ''):?>
			<tr>
				<td><span><?=Loc::getMessage("SALE_EBAY_RES_MESSAGES")?>:</span></td>
				<td>
					<?=$feedResErrDescr?>
				</td>
			</tr>
		<?endif;?>
		<tr>
			<td><span>&nbsp;</span></td>
			<td><input type="button" value="<?=Loc::getMessage("SALE_EBAY_FEED_EXCAHGE_NOW")?>" onclick="BX.Sale.EbayAdmin.startFeed('<?=$feedType?>','<?=$SITE_ID?>');"></td>
		</tr>
	<?endforeach;?>
	<?
	$tabControl->BeginNextTab();
	?>
	<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_EBAY_META_CATEGORIES")?></td></tr>
	<tr>
		<td width="40%"><?=Loc::getMessage("SALE_EBAY_META_CAT_IMPORTED")?>:</td>
		<td width="60%"><?=$ebayCategoriesCount?></td>
	</tr>
	<tr>
		<td width="40%"><?=Loc::getMessage("SALE_EBAY_META_CAT_LAST_DATE")?>:</td>
		<td width="60%"><?=$ebayCategoriesUpdateDate?></td>
	</tr>
	<tr>
		<td width="40%">&nbsp;</td>
		<td width="60%"><input type="button" value="<?=Loc::getMessage("SALE_EBAY_REFRESH_DATA");?>" onclick="BX.Sale.EbayAdmin.refreshCategoriesData('<?=CUtil::JSEscape($SITE_ID)?>');"></td>
	</tr>
	<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_EBAY_META_CAT_PROPS")?></td></tr>
	<tr>
		<td width="40%"><?=Loc::getMessage("SALE_EBAY_META_CAT_PROPS_IMPORTED")?>:</td>
		<td width="60%"><?=$ebayCategoriesVars?></td>
	</tr>
	<tr>
		<td width="40%">&nbsp;</td>
		<td width="60%"><input type="button" value="<?=Loc::getMessage("SALE_EBAY_REFRESH_DATA");?>" onclick="BX.Sale.EbayAdmin.refreshCategoriesPropsData('<?=CUtil::JSEscape($SITE_ID)?>');"></td>
	</tr>
	<?

$tabControl->Buttons(array(
	"btnSave" => true,
	"btnApply" => false
));

$tabControl->End();

?>
</form>

<script type="text/javascript">
	BX.message({
		"SALE_EBAY_EXCHANGE_OK": "<?=\CUtil::JSEscape(Loc::getMessage("SALE_EBAY_EXCHANGE_OK"))?>",
		"SALE_EBAY_EXCHANGE_ERROR": "<?=\CUtil::JSEscape(Loc::getMessage("SALE_EBAY_EXCHANGE_ERROR"))?>"
	});
</script>
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

