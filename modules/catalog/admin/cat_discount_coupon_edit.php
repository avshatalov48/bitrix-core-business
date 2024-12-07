<?
/** @global CDatabase $DB
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

Main\Loader::includeModule('catalog');

$accessController = AccessController::getCurrent();
if (!($accessController->check(ActionDictionary::ACTION_CATALOG_READ) || $accessController->check(ActionDictionary::ACTION_PRODUCT_DISCOUNT_SET)))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$bReadOnly = !$accessController->check(ActionDictionary::ACTION_PRODUCT_DISCOUNT_SET);

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($ex->GetString());
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);
$returnUrl = '';
if (!empty($_REQUEST['return_url']))
{
	$currentUrl = $APPLICATION->GetCurPage();
	if (mb_strtolower(mb_substr($_REQUEST['return_url'], mb_strlen($currentUrl))) != mb_strtolower($currentUrl))
	{
		$returnUrl = $_REQUEST['return_url'];
	}
	unset($currentUrl);
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("CDEN_TAB_DISCOUNT"), "ICON" => "catalog", "TITLE" => GetMessage("CDEN_TAB_DISCOUNT_DESCR")),
);

$tabControl = new CAdminForm('catalogCouponEdit', $aTabs);
$tabControl->SetShowSettings(false);

$errorMessage = '';
$bVarsFromForm = false;

$ID = 0;
if (isset($_REQUEST['ID']))
{
	$ID = (int)$_REQUEST['ID'];
	if ($ID < 0)
		$ID = 0;
}
$arFields = array();

if (!$bReadOnly && $_SERVER['REQUEST_METHOD']=="POST" && !empty($_POST['Update']) && check_bitrix_sessid())
{
	$DB->StartTransaction();

	$arFields = array(
		"ACTIVE" => (isset($_POST['ACTIVE']) && 'Y' == $_POST['ACTIVE'] ? 'Y' : 'N'),
		"COUPON" => (isset($_POST["COUPON"]) ? $_POST["COUPON"] : ''),
		"DATE_APPLY" => (isset($_POST['DATE_APPLY']) ? $_POST['DATE_APPLY'] : ''),
		"ONE_TIME" => (isset($_POST['ONE_TIME']) ? $_POST['ONE_TIME'] : ''),
		"DESCRIPTION" => (isset($_POST['DESCRIPTION']) ? $_POST['DESCRIPTION'] : ''),
	);

	if ($ID > 0)
	{
		$res = CCatalogDiscountCoupon::Update($ID, $arFields);
	}
	else
	{
		$arFields['DISCOUNT_ID'] = (isset($_POST['DISCOUNT_ID']) ? $_POST['DISCOUNT_ID'] : 0);
		$ID = CCatalogDiscountCoupon::Add($arFields);
		$res = ($ID>0);
	}

	if (!$res)
	{
		if ($ex = $APPLICATION->GetException())
			$errorMessage .= $ex->GetString()."<br>";
		else
			$errorMessage .= (0 < $ID ? str_replace('#ID#', $ID, GetMessage('DSC_CPN_ERR_UPDATE')) : GetMessage('DSC_CPN_ERR_ADD'))."<br>";
		$bVarsFromForm = true;
		$DB->Rollback();
	}
	else
	{
		$DB->Commit();
		if (empty($_POST['apply']))
			LocalRedirect("/bitrix/admin/cat_discount_coupon.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
		else
			LocalRedirect("/bitrix/admin/cat_discount_coupon_edit.php?lang=".LANGUAGE_ID."&ID=".$ID.GetFilterParams("filter_", false));
	}
}

if ($ID > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("DSC_TITLE_UPDATE")));
else
	$APPLICATION->SetTitle(GetMessage("DSC_TITLE_ADD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arDefaultValues = array(
	'DISCOUNT_ID' => '',
	'ACTIVE' => 'Y',
	'ONE_TIME' => Catalog\DiscountCouponTable::TYPE_ONE_ORDER,
	'COUPON' => '',
	'DATE_APPLY' => '',
	'DESCRIPTION' => '',
);

$arSelect = array_merge(array('ID'), array_keys($arDefaultValues));

$arCoupon = $arDefaultValues;

if ($ID > 0)
{
	$rsCoupons = CCatalogDiscountCoupon::GetList(array(), array("ID" => $ID), false, false, $arSelect);
	if (!($arCoupon = $rsCoupons->Fetch()))
	{
		$ID = 0;
		$arCoupon = $arDefaultValues;
	}
}

if ($bVarsFromForm)
{
	if ($ID > 0)
	{
		$intDiscountID = $arCoupon['DISCOUNT_ID'];
		$arCoupon = $arFields;
		$arCoupon['DISCOUNT_ID'] = $intDiscountID;
	}
	else
	{
		$arCoupon = $arFields;
	}
}

$aMenu = array(
	array(
		"TEXT" => GetMessage("DSC_TO_LIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/cat_discount_coupon.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false)
	)
);

if ($ID > 0 && !$bReadOnly)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("CDEN_NEW_DISCOUNT"),
		"ICON" => "btn_new",
		"LINK" => "/bitrix/admin/cat_discount_coupon_edit.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false)
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("CDEN_DELETE_DISCOUNT"),
		"ICON" => "btn_delete",
		"LINK" => "javascript:if(confirm('".GetMessage("CDEN_DELETE_DISCOUNT_CONFIRM")."')) window.location='/bitrix/admin/cat_discount_coupon.php?action=delete&ID[]=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."#tb';",
		"WARNING" => "Y"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
if ($errorMessage !== '')
	CAdminMessage::ShowMessage($errorMessage);

$filterDiscount = array();
if ($ID > 0)
	$filterDiscount = array('ID' => $arCoupon['DISCOUNT_ID']);
$arDiscountList = array();
$rsDiscounts = CCatalogDiscount::GetList(
	array("NAME" => "ASC"),
	$filterDiscount,
	false,
	false,
	array("ID", "SITE_ID", "NAME")
);
while ($arDiscount = $rsDiscounts->Fetch())
{
	$arDiscountList[$arDiscount['ID']] = "[".$arDiscount["ID"]."] ".$arDiscount["NAME"]." (".$arDiscount["SITE_ID"].")";
}
$arTypeList = Catalog\DiscountCouponTable::getCouponTypes(true);

$tabControl->BeginPrologContent();

$tabControl->EndPrologContent();

$tabControl->BeginEpilogContent();
echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>">
<input type="hidden" name="ID" value="<? echo $ID; ?>">
<? echo bitrix_sessid_post();
if (!empty($returnUrl))
{
	?><input type="hidden" name="return_url" value="<? echo htmlspecialcharsbx($returnUrl); ?>"><?
}
$tabControl->EndEpilogContent();
$tabControl->Begin(array(
	"FORM_ACTION" => '/bitrix/admin/cat_discount_coupon_edit.php?lang='.LANGUAGE_ID,
));

$tabControl->BeginNextFormTab();
	if ($ID > 0)
		$tabControl->AddViewField('ID','ID:',$ID,false);
	if (!empty($arDiscountList))
	{
		if (0 < $ID)
		{
			$tabControl->BeginCustomField("DISCOUNT_ID", GetMessage('DSC_CPN_DISC').':', false);
			?><tr id="tr_DISCOUNT_ID" class="adm-detail-required-field">
			<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
			<td width="60%"><?
				if (isset($arDiscountList[$arCoupon['DISCOUNT_ID']]))
				{
					echo htmlspecialcharsbx($arDiscountList[$arCoupon['DISCOUNT_ID']]);
				}
			?></td>
			</tr><?
			$tabControl->EndCustomField('DISCOUNT_ID');
		}
		else
		{
			$tabControl->AddDropDownField("DISCOUNT_ID", GetMessage('DSC_CPN_DISC').':', true, $arDiscountList, $arCoupon['DISCOUNT_ID']);
		}
	}
	else
	{
		$tabControl->BeginCustomField("DISCOUNT_ID", GetMessage('DSC_CPN_DISC').':', true);
		?><tr id="tr_DISCOUNT_ID" class="adm-detail-required-field">
			<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
			<td width="60%">&nbsp;<a href="/bitrix/admin/cat_discount_edit.php?lang=<? echo LANGUAGE_ID; ?>&return_url=<? echo urlencode($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID); ?>"><? echo GetMessage('DSC_ADD_DISCOUNT'); ?></a></td>
		</tr><?
		$tabControl->EndCustomField('DISCOUNT_ID');
	}
	$tabControl->AddCheckBoxField("ACTIVE", GetMessage("DSC_ACTIVE").":", false, "Y", $arCoupon['ACTIVE'] == "Y");
	$tabControl->BeginCustomField('ONE_TIME', GetMessage('DSC_COUPON_TYPE').':', true);
	?><tr id="tr_ONE_TIME" class="adm-detail-required-field">
		<td width="40%" style="vertical-align: top;"><? echo $tabControl->GetCustomLabelHTML(); ?> <span class="required" style="vertical-align: super; font-size: smaller;">1</span></td>
		<td width="60%" id="td_ONE_TIME_VALUE">
			<select name="ONE_TIME" size="3">
			<?
			foreach ($arTypeList as $typeID => $typeName)
			{
				?><option value="<? echo $typeID; ?>"<? echo ($typeID == $arCoupon['ONE_TIME'] ? ' selected' : ''); ?>><? echo $typeName; ?></option><?
			}
			?>
			</select>
		</td>
	</tr><?
	$tabControl->EndCustomField('ONE_TIME',
		'<input type="hidden" name="ONE_TIME" value="'.htmlspecialcharsbx($arCoupon['ONE_TIME']).'">'
	);
	$tabControl->BeginCustomField('COUPON', GetMessage("DSC_CPN_CODE").':', true);
	?><tr id="tr_COUPON" class="adm-detail-required-field">
		<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
		<td width="60%" id="td_COUPON_VALUE">
			<input type="text" id="COUPON" name="COUPON" size="32" maxlength="32" value="<? echo htmlspecialcharsbx($arCoupon['COUPON']); ?>" />&nbsp;
			<input type="button" value="<? echo GetMessage("DSC_CPN_GEN") ?>" id="COUPON_GENERATE">
		</td>
	</tr><?
	$tabControl->EndCustomField('COUPON',
		'<input type="hidden" name="COUPON" value="'.htmlspecialcharsbx($arCoupon['COUPON']).'">'
	);
	$tabControl->AddCalendarField('DATE_APPLY', GetMessage("DDSC_CPN_DATE").':', $arCoupon['DATE_APPLY']);
	$tabControl->AddTextField("DESCRIPTION", GetMessage("DSC_CPN_DESCRIPTION").':', htmlspecialcharsbx($arCoupon['DESCRIPTION']), array("cols" => 50, 'rows' => 6));

	$arButtonsParams = array(
		"disabled" => $bReadOnly,
		"back_url" => "/bitrix/admin/cat_discount_coupon.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false)
	);

$tabControl->Buttons($arButtonsParams);

$tabControl->Show();

echo BeginNote();
?><span class="required" style="vertical-align: super; font-size: smaller;">1</span> <? echo GetMessage('DSC_CPN_ONE_ORDER_NOTE');
echo EndNote();
?><script>
BX.ready(function(){
	var obCouponValue = BX('COUPON'),
		obCouponBtn = BX('COUPON_GENERATE');
	if (!!obCouponValue && !!obCouponBtn)
	{
		BX.bind(obCouponBtn, 'click', function(){
			var url,
				data;
			BX.showWait();
			url = '/bitrix/tools/catalog/generate_coupon.php';
			data = {
				lang: BX.message('LANGUAGE_ID'),
				sessid: BX.bitrix_sessid()
			};
			BX.ajax.loadJSON(
				url,
				data,
				function(data){
				var boolFlag = true,
					strErr = '',
					obCouponErr,
					obCouponCell;

				BX.closeWait();
				if (BX.type.isString(data))
				{
					boolFlag = false;
					strErr = data;
				}
				else
				{
					if ('OK' != data.STATUS)
					{
						boolFlag = false;
						strErr = data.MESSAGE;
					}
				}
				obCouponErr = BX('COUPON_GENERATE_ERR');
				if (boolFlag)
				{
					obCouponValue.value = data.RESULT;
					if (!!obCouponErr)
						obCouponErr = BX.remove(obCouponErr);
				}
				else
				{
					if (!obCouponErr)
					{
						obCouponCell = BX('td_COUPON_VALUE');
						if (!!obCouponCell)
						{
							obCouponErr = obCouponCell.insertBefore(BX.create(
								'IMG',
								{
									props: {
										id: 'COUPON_GENERATE_ERR',
										src: '/bitrix/panel/main/images_old/icon_warn.gif'
									},
									style: {
										marginRight: '10px',
										verticalAlign: 'middle'
									}
								}
							), obCouponBtn);
						}
					}
					if (!!obCouponErr)
						BX.adjust(obCouponErr, {props: { title: strErr }});
				}
			});
		});
	}
});
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>