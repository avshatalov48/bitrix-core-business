<?php
/** @global CMain $APPLICATION */
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."sale_discount_coupons.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$subWindow = defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1;
$prefix = ($subWindow ? 'COUPON_' : '');

$saleModulePermissions = $APPLICATION->GetGroupRight('sale');
$readOnly = ($saleModulePermissions < 'W');
if ($saleModulePermissions < 'R')
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('sale');
Loc::loadMessages(__FILE__);

if ($subWindow)
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/subelement.php');
}

if (!$subWindow && $ex = $APPLICATION->GetException())
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError($ex->GetString());
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

$couponTypes = Internals\DiscountCouponTable::getCouponTypes(true);

$request = Main\Context::getCurrent()->getRequest();

$multiCoupons = false;
$discountID = 0;
if ($subWindow)
{
	$multiCoupons = (string)$request->get('MULTI') == 'Y';
	$discountID = (int)$request->get('DISCOUNT_ID');
	$discount = Internals\DiscountTable::getList(array(
		'select' => array('ID'),
		'filter' => array('=ID' => $discountID)
	))->fetch();
	if (!$discount)
	{
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
		ShowError(Loc::getMessage('BX_SALE_DISCOUNT_COUPON_ERR_DISCOUNT_ID_ABSENT'));
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
		die();
	}
	unset($discount);
}
$returnUrl = '';
if (!$subWindow)
{
	$rawReturnUrl = (string)$request->get('return_url');
	if ($rawReturnUrl != '')
	{
		$currentUrl = $APPLICATION->GetCurPage();
		if (mb_strtolower(mb_substr($rawReturnUrl, mb_strlen($currentUrl))) != mb_strtolower($currentUrl))
			$returnUrl = $rawReturnUrl;
	}
	unset($rawReturnUrl);
}

$tabList = array(
	array(
		'ICON' => 'sale',
		'DIV' => 'couponEdit01',
		'TAB' => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_EDIT_TAB_NAME_COMMON'),
		'TITLE' => (
			$multiCoupons
			? Loc::getMessage('BX_SALE_DISCOUNT_COUPON_EDIT_TAB_TITLE_MULTI_COMMON')
			: Loc::getMessage('BX_SALE_DISCOUNT_COUPON_EDIT_TAB_TITLE_COMMON')
		)
	)
);
$couponFormID = '';
if ($subWindow)
{
	$subFormListUrl = [
		'LINK' => $APPLICATION->GetCurPageParam(),
		'POST_PARAMS' => [
			'bxpublic' => 'Y',
			'DISCOUNT_ID' => $discountID,
			'sessid' => bitrix_sessid()
		]
	];
	$couponFormID = 'saleSubCouponControl';
	$control = new CAdminSubForm($couponFormID, $tabList, false, true, $subFormListUrl, false);
	unset($subFormListUrl);
}
else
{
	$couponFormID = ($multiCoupons ? 'saleMultiCouponControl' : 'saleCouponControl');
	$control = new CAdminForm($couponFormID, $tabList);
	$control->SetShowSettings(false);
}
unset($tabList);
$couponFormID .= '_form';

$errors = array();
$fields = array();
$copy = false;
$couponID = (int)$request->get('ID');
if ($couponID < 0)
	$couponID = 0;

if ($couponID > 0)
	$copy = ($request->get('action') == 'copy');

if (
	check_bitrix_sessid()
	&& !$readOnly
	&& $request->isPost()
	&& (string)$request->getPost('Update') == 'Y'
)
{
	$rawData = $request->getPostList();
	if ($multiCoupons)
	{
		$fields['COUNT'] = 0;
		$fields['COUPON'] = array(
			'DISCOUNT_ID' => $discountID,
			'MAX_USE' => 0
		);

		if (!empty($rawData[$prefix.'ACTIVE_FROM']))
			$fields['COUPON']['ACTIVE_FROM'] = Main\Type\DateTime::createFromUserTime($rawData[$prefix.'ACTIVE_FROM']);
		if (!empty($rawData[$prefix.'ACTIVE_TO']))
			$fields['COUPON']['ACTIVE_TO'] = Main\Type\DateTime::createFromUserTime($rawData[$prefix.'ACTIVE_TO']);
		if (isset($rawData[$prefix.'TYPE']))
			$fields['COUPON']['TYPE'] = $rawData[$prefix.'TYPE'];
		if (isset($fields['COUPON']['TYPE']) && $fields['COUPON']['TYPE'] == Internals\DiscountCouponTable::TYPE_MULTI_ORDER)
		{
			if (isset($rawData[$prefix.'MAX_USE']))
				$fields['COUPON']['MAX_USE'] = $rawData[$prefix.'MAX_USE'];
		}
		if (isset($rawData[$prefix.'COUNT']))
			$fields['COUNT'] = (int)$rawData[$prefix.'COUNT'];

		if ($fields['COUNT'] <= 0)
			$errors[] = Loc::getMessage('BX_SALE_DISCOUNT_COUPON_ERR_COUPON_COUNT');

		$checkResult = Internals\DiscountCouponTable::checkPacket($fields['COUPON'], false);
		if (!$checkResult->isSuccess(true))
		{
			$errors = $checkResult->getErrorMessages();
		}
		else
		{
			$couponsResult = Internals\DiscountCouponTable::addPacket(
				$fields['COUPON'],
				$fields['COUNT']
			);
			if (!$couponsResult->isSuccess())
				$errors = $couponsResult->getErrorMessages();
			unset($couponsResult);
		}
		unset($checkResult);
	}
	else
	{
		if ($subWindow)
			$fields['DISCOUNT_ID'] = $discountID;
		elseif (!empty($rawData['DISCOUNT_ID']))
			$fields['DISCOUNT_ID'] = $rawData['DISCOUNT_ID'];

		if (isset($rawData['COUPON']))
			$fields['COUPON'] = $rawData['COUPON'];
		if (!empty($rawData[$prefix.'ACTIVE']))
			$fields['ACTIVE'] = $rawData[$prefix.'ACTIVE'];
		$fields['ACTIVE_FROM'] = (!empty($rawData[$prefix.'ACTIVE_FROM']) ? Main\Type\DateTime::createFromUserTime($rawData[$prefix.'ACTIVE_FROM']) : null);
		$fields['ACTIVE_TO'] = (!empty($rawData[$prefix.'ACTIVE_TO']) ? Main\Type\DateTime::createFromUserTime($rawData[$prefix.'ACTIVE_TO']) : null);
		if (isset($rawData[$prefix.'TYPE']))
			$fields['TYPE'] = $rawData[$prefix.'TYPE'];
		if (isset($fields['TYPE']) && $fields['TYPE'] == Internals\DiscountCouponTable::TYPE_MULTI_ORDER)
		{
			if (isset($rawData[$prefix.'MAX_USE']))
				$fields['MAX_USE'] = $rawData[$prefix.'MAX_USE'];
		}
		if (isset($rawData[$prefix.'USER_ID']))
			$fields['USER_ID'] = $rawData[$prefix.'USER_ID'];
		if (isset($rawData[$prefix.'DESCRIPTION']))
			$fields['DESCRIPTION'] = $rawData[$prefix.'DESCRIPTION'];

		if ($couponID == 0 || $copy)
			$result = Internals\DiscountCouponTable::add($fields);
		else
			$result = Internals\DiscountCouponTable::update($couponID, $fields);
		if (!$result->isSuccess())
		{
			$errors = $result->getErrorMessages();
		}
		else
		{
			if ($couponID == 0 || $copy)
				$couponID = $result->getId();
		}
		unset($result);
	}
	unset($rawData);

	if (empty($errors))
	{
		if ($subWindow)
		{
			?>
			<script>
				var currentWindow = top.window;
				if (top.BX.SidePanel && top.BX.SidePanel.Instance && top.BX.SidePanel.Instance.getTopSlider())
				{
					currentWindow = top.BX.SidePanel.Instance.getTopSlider().getWindow();
				}
				currentWindow.BX.closeWait();
				currentWindow.BX.WindowManager.Get().AllowClose();
				currentWindow.BX.WindowManager.Get().Close();
				currentWindow.ReloadSubList();
			</script>
			<?php
			die();
		}
		else
		{
			if ($adminSidePanelHelper->isAjaxRequest())
			{
				$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $couponID));
			}
			if ((string)$request->getPost('apply') != '')
			{
				$applyUrl = $selfFolderUrl.'sale_discount_coupon_edit.php?lang='.LANGUAGE_ID.'&ID='.$couponID.'&'.$control->ActiveTabParam();
				$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
				LocalRedirect($applyUrl);
			}
			else
			{
				$adminSidePanelHelper->localRedirect($listUrl);
				LocalRedirect($listUrl);
			}
		}
	}
	else
	{
		$adminSidePanelHelper->sendJsonErrorResponse(implode("; ", $errors));
	}
}
elseif ($subWindow)
{
	if ((string)$request->get('dontsave') != '')
	{
		?>
		<script>
			var currentWindow = top.window;
			if (top.BX.SidePanel.Instance && top.BX.SidePanel.Instance.getTopSlider())
			{
				currentWindow = top.BX.SidePanel.Instance.getTopSlider().getWindow();
			}
			currentWindow.BX.closeWait();
			currentWindow.BX.WindowManager.Get().AllowClose();
			currentWindow.BX.WindowManager.Get().Close();
		</script>
		<?php
		die();
	}
}

$APPLICATION->SetTitle(
	$couponID == 0
	? (
		!$multiCoupons
		? Loc::getMessage('BX_SALE_DISCOUNT_COUPON_EDIT_TITLE_ADD')
		: Loc::getMessage('BX_SALE_DISCOUNT_COUPON_EDIT_TITLE_MULTI_ADD')
	)
	: (
		!$copy
		? Loc::getMessage('BX_SALE_DISCOUNT_COUPON_EDIT_TITLE_UPDATE', array('#ID#' => $couponID))
		: Loc::getMessage('BX_SALE_DISCOUNT_COUPON_EDIT_TITLE_COPY', array('#ID#' => $couponID))
	)
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$contextMenuItems = array(
	array(
		'ICON' => 'btn_list',
		'TEXT' => Loc::getMessage('BX_SALE_DISCOUNT_COUPONT_CONTEXT_COUPON_LIST'),
		'LINK' => $listUrl
	)
);

if (!$subWindow && !$readOnly && $couponID > 0)
{
	if (!$copy)
	{
		$addUrl = $selfFolderUrl."sale_discount_coupon_edit.php?lang=".LANGUAGE_ID;
		$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
		if (!$adminSidePanelHelper->isPublicFrame())
			$addUrl = $adminSidePanelHelper->setDefaultQueryParams($addUrl);
		$contextMenuItems[] = array(
			'ICON' => 'btn_new',
			'TEXT' => Loc::getMessage('BX_SALE_DISCOUNT_COUPONT_CONTEXT_NEW'),
			'LINK' => $addUrl
		);
		$copyUrl = $selfFolderUrl."sale_discount_coupon_edit.php?lang=".LANGUAGE_ID."&ID=".$discountID."&action=copy";
		$copyUrl = $adminSidePanelHelper->editUrlToPublicPage($copyUrl);
		if (!$adminSidePanelHelper->isPublicFrame())
			$copyUrl = $adminSidePanelHelper->setDefaultQueryParams($copyUrl);
		$contextMenuItems[] = array(
			'ICON' => 'btn_copy',
			'TEXT' => Loc::getMessage('BX_SALE_DISCOUNT_COUPONT_CONTEXT_COPY'),
			'LINK' => $copyUrl
		);
		$deleteUrl = $selfFolderUrl."sale_discount_coupons.php?lang=".LANGUAGE_ID."&ID=".$couponID."&action=delete&".bitrix_sessid_get();
		$buttonAction = "LINK";
		if ($adminSidePanelHelper->isPublicFrame())
		{
			$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
			$buttonAction = "ONCLICK";
		}
		$contextMenuItems[] = array(
			'ICON' => 'btn_delete',
			'TEXT' => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_CONTEXT_DELETE'),
			$buttonAction => "javascript:if(confirm('".CUtil::JSEscape(Loc::getMessage('BX_SALE_DISCOUNT_COUPON_CONTEXT_DELETE_CONFIRM'))."')) top.window.location.href='".$deleteUrl."';",
			'WARNING' => 'Y',
		);
	}
}

$contextMenu = new CAdminContextMenu($contextMenuItems);
$contextMenu->Show();
unset($contextMenu, $contextMenuItems);

if (!empty($errors))
{
	$errorMessage = new CAdminMessage(
		array(
			'DETAILS' => implode('<br>', $errors),
			'TYPE' => 'ERROR',
			'MESSAGE' => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_ERR_SAVE'),
			'HTML' => true
		)
	);
	echo $errorMessage->Show();
	unset($errorMessage);
}

$selectFields = array();
if (!$multiCoupons)
{
	$defaultValues = array(
		'DISCOUNT_ID' => '',
		'COUPON' => '',
		'ACTIVE' => 'Y',
		'ACTIVE_FROM' => null,
		'ACTIVE_TO' => null,
		'TYPE' => Internals\DiscountCouponTable::TYPE_ONE_ORDER,
		'MAX_USE' => 0,
		'USE_COUNT' => 0,
		'USER_ID' => 0,
		'DESCRIPTION' => '',
		'DATE_APPLY' => null
	);
	$selectFields = array('ID', 'DISCOUNT_NAME' => 'DISCOUNT.NAME');
	$selectFields = array_merge($selectFields, array_keys($defaultValues));
}
else
{
	$defaultValues = array(
		'COUNT' => '',
		'COUPON' => array(
			'DISCOUNT_ID' => '',
			'ACTIVE_FROM' => null,
			'ACTIVE_TO' => null,
			'TYPE' => Internals\DiscountCouponTable::TYPE_ONE_ORDER,
			'MAX_USE' => 0,
		)
	);
}

$coupon = array();
if (!$multiCoupons && $couponID > 0)
{
	$coupon = Internals\DiscountCouponTable::getList(array(
		'select' => $selectFields,
		'filter' => array('=ID' => $couponID)
	))->fetch();
	if (!$coupon)
		$couponID = 0;
}
if ($couponID == 0)
	$coupon = $defaultValues;

if (!empty($coupon))
{
	if (!$multiCoupons)
	{
		$coupon['DISCOUNT_NAME'] = (string)($coupon['DISCOUNT_NAME'] ?? '');
		$coupon['DISCOUNT_ID'] = (int)($coupon['DISCOUNT_ID'] ?? 0);
		$coupon['TYPE'] = (int)($coupon['TYPE'] ?? 0);
		$coupon['USE_COUNT'] = (int)($coupon['USE_COUNT'] ?? 0);
		$coupon['MAX_USE'] = (int)($coupon['MAX_USE'] ?? 0);
		$coupon['USER_ID'] = (int)($coupon['USER_ID'] ?? 0);
		$coupon['DESCRIPTION'] = (string)($coupon['DESCRIPTION'] ?? '');
	}
	else
	{
		$coupon['COUNT'] = (int)($coupon['COUNT'] ?? 0);
		$coupon['COUPON']['DISCOUNT_ID'] = (int)($coupon['COUPON']['DISCOUNT_ID'] ?? 0);
		$coupon['COUPON']['TYPE'] = (int)($coupon['COUPON']['TYPE'] ?? 0);
		$coupon['COUPON']['MAX_USE'] = (int)($coupon['COUPON']['MAX_USE'] ?? 0);
	}
}

if (!empty($errors))
	$coupon = array_merge($coupon, $fields);

$control->BeginPrologContent();
CJSCore::Init(array('date'));
$control->EndPrologContent();
$control->BeginEpilogContent();
echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
<input type="hidden" name="ID" value="<?= $couponID ?>">
<?php
if ($subWindow)
{
	?><input type="hidden" name="DISCOUNT_ID" value="<?= $discountID ?>">
	<input type="hidden" name="MULTI" value="<?= ($multiCoupons ? 'Y' : 'N') ?>"><?php
}
if ($copy)
{
	?><input type="hidden" name="action" value="copy"><?php
}
if (!empty($returnUrl))
{
	?><input type="hidden" name="return_url" value="<?= htmlspecialcharsbx($returnUrl) ?>"><?php
}
echo bitrix_sessid_post();
$control->EndEpilogContent();
$formActionUrl = $selfFolderUrl.'sale_discount_coupon_edit.php?lang='.LANGUAGE_ID;
$formActionUrl = $adminSidePanelHelper->setDefaultQueryParams($formActionUrl);
$control->Begin(array('FORM_ACTION' => $formActionUrl));
$control->BeginNextFormTab();
if ($multiCoupons)
{
	$control->AddEditField($prefix.'COUNT', Loc::getMessage('BX_SALE_DISCOUNT_COUPON_COUNT'), true, array(), ($coupon['COUNT'] > 0 ? $coupon['COUNT'] : ''));
	$control->BeginCustomField($prefix.'PERIOD', Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_PERIOD'), false);
	?><tr id="tr_COUPON_PERIOD">
	<td width="40%"><?= $control->GetCustomLabelHTML() ?></td>
	<td width="60%"><?php
		$periodValue = '';
		CTimeZone::Disable();
		$activeFrom = ($coupon['COUPON']['ACTIVE_FROM'] instanceof Main\Type\DateTime ? $coupon['COUPON']['ACTIVE_FROM']->toString() : '');
		$activeTo = ($coupon['COUPON']['ACTIVE_TO'] instanceof Main\Type\DateTime ? $coupon['COUPON']['ACTIVE_TO']->toString() : '');
		CTimeZone::Enable();
		if ($activeFrom != '' || $activeTo != '')
			$periodValue = CAdminCalendar::PERIOD_INTERVAL;

		$calendar = new CAdminCalendar;
		echo $calendar->CalendarPeriodCustom(
			$prefix.'ACTIVE_FROM', $prefix.'ACTIVE_TO',
			$activeFrom, $activeTo,
			true, 19, true,
			array(
				CAdminCalendar::PERIOD_EMPTY => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_PERIOD_EMPTY'),
				CAdminCalendar::PERIOD_INTERVAL => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_PERIOD_INTERVAL')
			),
			$periodValue
		);
		unset($calendar, $activeTo, $activeFrom, $periodValue);
		?></td>
	</tr><?php
	$control->EndCustomField($prefix.'PERIOD');
	$control->AddDropDownField(
		$prefix.'TYPE',
		Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_TYPE'),
		true,
		$couponTypes,
		$coupon['COUPON']['TYPE'],
		array('id="'.$prefix.'TYPE'.'"', 'size="3"')
	);
	$control->AddEditField(
		$prefix.'MAX_USE',
		Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_MAX_USE'),
		false,
		array('id' => $prefix.'MAX_USE'),
		($coupon['COUPON']['MAX_USE'] > 0 ? $coupon['COUPON']['MAX_USE'] : '')
	);
	$control->Buttons(false, '');
	$control->Show();
?>
<script>
	var couponType = BX('<?=$prefix.'TYPE'; ?>'),
		maxUse = BX('<?=$prefix.'MAX_USE'; ?>'),
		rowMaxUse;

	rowMaxUse = BX.findParent(maxUse, { 'tagName': 'tr' });

	BX.ready(function(){
		BX.style(
			rowMaxUse,
			'display',
			(couponType.value == '<?=Internals\DiscountCouponTable::TYPE_MULTI_ORDER; ?>' ? 'table-row' : 'none')
		);
		BX.bind(couponType, 'change', function ()
		{
			BX.style(
				rowMaxUse,
				'display',
				(couponType.value == '<?=Internals\DiscountCouponTable::TYPE_MULTI_ORDER; ?>' ? 'table-row' : 'none')
			);
			if (top.BX.WindowManager.Get())
			{
				top.BX.WindowManager.Get().adjustSizeEx();
			}
			else
			{
				BX.WindowManager.Get().adjustSizeEx();
			}
		});
		if (top.BX.WindowManager.Get())
		{
			top.BX.WindowManager.Get().adjustSizeEx();
		}
		else
		{
			BX.WindowManager.Get().adjustSizeEx();
		}
	});
</script>
<?php
}
else
{
	if ($couponID > 0 && !$copy)
		$control->AddViewField($prefix.'ID', Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_ID'), $couponID, false);
	$control->AddCheckBoxField($prefix.'ACTIVE', Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_ACTIVE'), true, array('Y', 'N'), $coupon['ACTIVE'] == 'Y');
	if ($couponID > 0)
	{
		$discountEditUrl = $selfFolderUrl."sale_discount_edit.php?lang=".LANGUAGE_ID."&ID=".$coupon['DISCOUNT_ID'];
		$discountEditUrl = $adminSidePanelHelper->editUrlToPublicPage($discountEditUrl);
		$discountName = '<a href="'.$discountEditUrl.'">['.$coupon['DISCOUNT_ID'].']</a>';
		if ($coupon['DISCOUNT_NAME'] !== '')
			$discountName .= ' '.htmlspecialcharsbx($coupon['DISCOUNT_NAME']);
		$discountName .= '<input type="hidden" name="DISCOUNT_ID" value="'.$coupon['DISCOUNT_ID'].'">';
		$control->AddViewField('DISCOUNT_ID', Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_DISCOUNT'), $discountName, true);
	}
	elseif (!$subWindow)
	{
		$discountList = array();
		$discountIterator = Internals\DiscountTable::getList(array(
			'select' => array('ID', 'NAME'),
			'filter' => array('=ACTIVE' => 'Y'),
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
		));
		while ($discount = $discountIterator->fetch())
		{
			$discount['ID'] = (int)$discount['ID'];
			$discount['NAME'] = (string)$discount['NAME'];
			$discountList[$discount['ID']] = '['.$discount['ID'].']'.($discount['NAME'] !== '' ? ' '.$discount['NAME'] : '');
		}
		unset($discount, $discountIterator);
		if (!empty($discountList))
		{
			$control->AddDropDownField(
				'DISCOUNT_ID',
				Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_DISCOUNT'),
				true,
				$discountList,
				$coupon['DISCOUNT_ID']
			);
		}
		else
		{
			$control->BeginCustomField('DISCOUNT_ID', Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_DISCOUNT'), true);
			$discountEditPath = $selfFolderUrl.'sale_discount_edit.php?lang='.LANGUAGE_ID.'&return_url='.urlencode($APPLICATION->GetCurPageParam());
			$discountEditPath = $adminSidePanelHelper->editUrlToPublicPage($discountEditPath);
			?><tr id="tr_DISCOUNT_ID">
			<td width="40%"><?= $control->GetCustomLabelHTML() ?></td>
			<td width="60%">
				<?= Loc::getMessage('BX_SALE_DISCOUNT_COUPON_MESS_DISCOUNT_ABSENT') ?> <a href="<?= $discountEditPath ?>"><?php
				echo Loc::getMessage('BX_SALE_DISCOUNT_COUPON_MESS_DISCOUNT_ADD'); ?></a></td>
			</tr><?php
			unset($discountEditPath);
			$control->EndCustomField('DISCOUNT_ID');
		}
		unset($discountList);
	}
	$control->BeginCustomField('COUPON', Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_COUPON'), true);
	?><tr id="tr_COUPON" class="adm-detail-required-field">
		<td width="40%"><?= $control->GetCustomLabelHTML() ?></td>
		<td width="60%" id="td_COUPON_VALUE">
			<input type="text" id="COUPON" name="COUPON" size="32" maxlength="32" value="<?= htmlspecialcharsbx($coupon['COUPON']); ?>" />&nbsp;
			<input type="button" value="<?= htmlspecialcharsbx(Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_COUPON_GENERATE')) ?>" id="COUPON_GENERATE">
		</td>
	</tr><?php
	$control->EndCustomField('COUPON',
		'<input type="hidden" name="COUPON" value="'.htmlspecialcharsbx($coupon['COUPON']).'">'
	);
	$showTypeSelect = (
		$couponID == 0
		|| !isset($couponTypes[$coupon['TYPE']])
		|| $coupon['DATE_APPLY'] == null
	);
	if ($showTypeSelect)
	{
		$control->AddDropDownField(
			$prefix.'TYPE',
			Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_TYPE'),
			true,
			$couponTypes,
			$coupon['TYPE'],
			array('id="'.$prefix.'TYPE'.'"', 'size="3"')
		);
	}
	else
	{
		$control->AddViewField(
			$prefix.'TYPE',
			Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_TYPE'),
			$couponTypes[$coupon['TYPE']].'<input type="hidden" name="'.htmlspecialcharsbx($prefix.'TYPE').'" value="'.htmlspecialcharsbx($coupon['TYPE']).'">',
			true
		);
	}
	$control->BeginCustomField($prefix.'PERIOD', Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_PERIOD'), false);
	?><tr id="tr_COUPON_PERIOD">
		<td width="40%"><?= $control->GetCustomLabelHTML() ?></td>
		<td width="60%"><?php
		$periodValue = '';
		CTimeZone::Disable();
		$activeFrom = ($coupon['ACTIVE_FROM'] instanceof Main\Type\DateTime ? $coupon['ACTIVE_FROM']->toString() : '');
		$activeTo = ($coupon['ACTIVE_TO'] instanceof Main\Type\DateTime ? $coupon['ACTIVE_TO']->toString() : '');
		CTimeZone::Enable();
		if ($activeFrom != '' || $activeTo != '')
			$periodValue = CAdminCalendar::PERIOD_INTERVAL;

		$calendar = new CAdminCalendar;
		echo $calendar->CalendarPeriodCustom(
			$prefix.'ACTIVE_FROM', $prefix.'ACTIVE_TO',
			$activeFrom, $activeTo,
			true, 19, true,
			array(
				CAdminCalendar::PERIOD_EMPTY => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_PERIOD_EMPTY'),
				CAdminCalendar::PERIOD_INTERVAL => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_PERIOD_INTERVAL')
			),
			$periodValue
		);
			unset($activeTo, $activeFrom, $periodValue);
		?></td>
	</tr><?php
	$control->EndCustomField($prefix.'PERIOD');
	$control->BeginCustomField($prefix.'USER_ID', Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_USER_ID'), false);
	?><tr id="tr_USER_ID">
		<td width="40%"><?= $control->GetCustomLabelHTML() ?></td>
		<td width="60%"><?php
			echo FindUserID(
				$prefix.'USER_ID',
				($coupon['USER_ID'] > 0 ? $coupon['USER_ID'] : ''),
				'',
				$couponFormID
			);
		?></td>
	</tr><?php
	$control->EndCustomField($prefix.'USER_ID');
	if ($showTypeSelect || ($couponID > 0 && $coupon['TYPE'] == Internals\DiscountCouponTable::TYPE_MULTI_ORDER))
		$control->AddEditField(
			$prefix.'MAX_USE',
			Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_MAX_USE'),
			false,
			array('id' => $prefix.'MAX_USE'),
			($coupon['MAX_USE'] > 0 ? $coupon['MAX_USE'] : '')
		);
	if ($couponID > 0 && $coupon['TYPE'] == Internals\DiscountCouponTable::TYPE_MULTI_ORDER && $coupon['USE_COUNT'] > 0)
		$control->AddViewField(
			$prefix.'USE_COUNT',
			Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_USE_COUNT'),
			$coupon['USE_COUNT'],
			false
		);
	$control->AddTextField(
		$prefix.'DESCRIPTION',
		Loc::getMessage('BX_SALE_DISCOUNT_COUPON_FIELD_DESCRIPTION'),
		$coupon['DESCRIPTION'],
		array(),
		false
	);
	if ($subWindow)
	{
		$control->Buttons(false, '');
	}
	else
	{
		$control->Buttons(array('disabled' => $readOnly, 'back_url' => $listUrl));
	}
	$control->Show();
?>
<script>
BX.ready(function(){
	var obCouponValue = BX('COUPON'),
		obCouponBtn = BX('COUPON_GENERATE'),
		couponType = BX('<?=$prefix.'TYPE'; ?>'),
		maxUse,
		rowMaxUse;

	if (!!obCouponValue && !!obCouponBtn)
	{
		BX.bind(obCouponBtn, 'click', function(){
			var url,
				data;

			BX.showWait();
			url = '/bitrix/tools/sale/generate_coupon.php';
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
					if (BX.type.isString(data))
					{
						boolFlag = false;
						strErr = data;
					}
					else
					{
						if (data.STATUS != 'OK')
						{
							boolFlag = false;
							strErr = data.MESSAGE;
						}
					}
					obCouponErr = BX('COUPON_GENERATE_ERR');
					if (boolFlag)
					{
						obCouponValue.value = data.COUPON;
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
						BX.adjust(obCouponErr, {props: { title: strErr }});
					}
					BX.closeWait();
				});
		});
	}
	if (!!couponType)
	{
		maxUse = BX('<?=$prefix.'MAX_USE'; ?>');
		rowMaxUse = BX.findParent(maxUse, { 'tagName': 'tr' });

		BX.style(
			rowMaxUse,
			'display',
			(couponType.value == '<?=Internals\DiscountCouponTable::TYPE_MULTI_ORDER; ?>' ? 'table-row' : 'none')
		);
		BX.bind(couponType, 'change', function ()
		{
			BX.style(
				rowMaxUse,
				'display',
				(couponType.value == '<?= Internals\DiscountCouponTable::TYPE_MULTI_ORDER ?>' ? 'table-row' : 'none')
			);
			<?php
			if ($subWindow)
			{
			?>if (top.BX.WindowManager.Get())
			{
				top.BX.WindowManager.Get().adjustSizeEx();
			}
			else
			{
				BX.WindowManager.Get().adjustSizeEx();
			}
			<?php
			}
			?>
		});
	}
});
<?php
if ($subWindow)
{
?>if (top.BX.WindowManager.Get())
{
	top.BX.WindowManager.Get().adjustSizeEx();
}
else
{
	BX.WindowManager.Get().adjustSizeEx();
}
<?php
}
?></script><?php
}
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
