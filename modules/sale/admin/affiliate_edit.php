<?php

/** @global CMain $APPLICATION */
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\SiteCurrencyTable;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$saleModulePermissions = $APPLICATION->GetGroupRight('sale');
if ($saleModulePermissions=='D')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

IncludeModuleLangFile(__FILE__);

Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$request = Context::getCurrent()->getRequest();

$errorMessage = '';
$bVarsFromForm = false;

$ID = (int)$request->get('ID');

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => GetMessage('SAE_AFF_TAB'),
		'ICON' => 'sale',
		'TITLE' => GetMessage('SAE_AFF_TAB_TITLE'),
	],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$formFields = [];

if ($request->isPost() && $request->getPost('Update') !== null && $saleModulePermissions>="W" && check_bitrix_sessid())
{
	$requiredStringList = [
		'SITE_ID' => GetMessage('SAE_NO_SITE_PLAN'),
		'DATE_CREATE' => GetMessage('SAE_NO_DATE_CREATE'),
	];
	foreach ($requiredStringList as $fieldId => $fieldError)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value) && $value !== '')
		{
			$formFields[$fieldId] = $value;
		}
		else
		{
			$errorMessage .= $fieldError . '<br>';
		}
	}
	unset($requiredStringList);
	$requiredIntList = [
		'USER_ID' => GetMessage('SAE_NO_USER'),
		'PLAN_ID' => GetMessage('SAE_NO_PLAN'),
	];
	foreach ($requiredIntList as $fieldId => $fieldError)
	{
		$correct = false;
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$value = (int)$value;
			if ($value > 0)
			{
				$correct = true;
				$formFields[$fieldId] = $value;
			}
		}
		if (!$correct)
		{
			$errorMessage .= $fieldError . '<br>';
		}
	}
	unset($requiredIntList);

	$stringList = [
		'AFF_SITE',
		'AFF_DESCRIPTION',
	];
	foreach ($stringList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$formFields[$fieldId] = $value;
		}
	}
	unset($stringList);

	$booleanList = [
		'ACTIVE',
		'FIX_PLAN',
	];
	foreach ($booleanList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if ($value === 'Y' || $value === 'N')
		{
			$formFields[$fieldId] = $value;
		}
	}
	unset($booleanList);

	$stringZeroList = [
		'LAST_CALCULATE',
	];
	foreach ($stringZeroList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$formFields[$fieldId] = $value !== '' ? $value : false;
		}
	}
	$intZeroList = [
		'AFFILIATE_ID',
	];
	foreach ($intZeroList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$value = (int)$value;
			$formFields[$fieldId] = $value > 0 ? $value : false;
		}
	}
	$floatList = [
		'PAID_SUM',
		'APPROVED_SUM',
		'PENDING_SUM',
	];
	foreach ($floatList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$formFields[$fieldId] = (float)str_replace(',', '.', $value);
		}
	}
	unset($floatList);

	if ($errorMessage === '')
	{
		$dbAffiliate = CSaleAffiliate::GetList(
			[],
			[
				"USER_ID" => $formFields['USER_ID'],
				"SITE_ID" => $formFields['SITE_ID'],
				"!ID" => $ID,
			]
		);
		if ($dbAffiliate->Fetch())
		{
			$errorMessage .=
				GetMessage(
					'SAE_AFFILIATE_ALREADY_EXISTS',
					[
						'#USER_ID#' => $formFields['USER_ID'],
						'#SITE_ID#' => $formFields['SITE_ID'],
					]
				)
				. '<br>'
			;
/*			$errorMessage .= str_replace("#USER_ID#", $USER_ID,
					str_replace("#SITE_ID#", $SITE_ID, GetMessage("SAE_AFFILIATE_ALREADY_EXISTS"))) . "<br>"; */
		}
	}

	if ($errorMessage === '')
	{
		if ($ID > 0)
		{
			if (!CSaleAffiliate::Update($ID, $formFields))
			{
				$ex = $APPLICATION->GetException();
				if ($ex)
				{
					$errorMessage .= $ex->GetString() . '<br>';
				}
				else
				{
					$errorMessage .= GetMessage('SAE_ERROR_SAVE_AFF') . '<br>';
				}
				unset($ex);
			}
		}
		else
		{
			$ID = (int)CSaleAffiliate::Add($formFields);
			if ($ID <= 0)
			{
				$ex = $APPLICATION->GetException();
				if ($ex)
				{
					$errorMessage .= $ex->GetString() . '<br>';
				}
				else
				{
					$errorMessage .= GetMessage('SAE_ERROR_SAVE_AFF') . '<br>';
				}
			}
		}
	}

	if ($errorMessage === '')
	{
		if ($request->getPost('apply') === null)
		{
			LocalRedirect('/bitrix/admin/sale_affiliate.php?lang=' . LANGUAGE_ID
				. GetFilterParams('filter_', false)
			);
		}
		else
		{
			LocalRedirect('/bitrix/admin/sale_affiliate_edit.php?lang=' . LANGUAGE_ID
				. '&ID=' . $ID . GetFilterParams('filter_', false)
			);
		}
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/prolog.php';

if ($ID > 0)
{
	$APPLICATION->SetTitle(GetMessage(
		'SAE_TITLE_UPDATE_AFF',
		[
			'#ID#' => $ID,
		]
	));
}
else
{
	$APPLICATION->SetTitle(GetMessage('SAE_TITLE_ADD_AFF'));
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$defaultValues = [
	'SITE_ID' => '',
	'USER_ID' => '',
	'AFFILIATE_ID' => '',
	'PLAN_ID' => '',
	'ACTIVE' => 'Y',
	'DATE_CREATE' => '',
	'PAID_SUM' => 0,
	'APPROVED_SUM' => 0,
	'PENDING_SUM' => '',
	'LAST_CALCULATE' => '',
	'AFF_SITE' => '',
	'AFF_DESCRIPTION' => '',
	'FIX_PLAN' => 'N',
];
$fields = null;

if ($ID > 0)
{
	$iterator = CSaleAffiliate::GetList([], ["ID" => $ID]);
	$fields = $iterator->Fetch();
	unset($iterator);
}
if (!$fields)
{
	$ID = 0;
	$fields = $defaultValues;
}
if ($bVarsFromForm)
{
	$fields = array_merge($fields, $formFields);
}

$aMenu = [
	[
		'TEXT' => GetMessage('SAE_AFF_LIST'),
		'LINK' => '/bitrix/admin/sale_affiliate.php?lang=' . LANGUAGE_ID . GetFilterParams('filter_'),
		'ICON' => 'btn_list',
	],
];

if ($ID > 0)
{
	$aMenu[] = [
		'SEPARATOR' => 'Y',
	];

	$aMenu[] = [
		'TEXT' => GetMessage('SAE_AFF_ADD'),
		'LINK' => '/bitrix/admin/sale_affiliate_edit.php?lang=' . LANGUAGE_ID . GetFilterParams('filter_'),
		'ICON' => 'btn_new',
	];

	if ($saleModulePermissions >= "W")
	{
		$aMenu[] = [
			'TEXT' => GetMessage('SAE_AFF_DELETE'),
			'LINK' =>
				"javascript:if(confirm('" . CUtil::JSEscape(GetMessage('SAE_AFF_DELETE_CONF'))
				. "')) window.location='/bitrix/admin/sale_affiliate.php?ID=" . $ID
				. '&action=delete&lang=' . LANGUAGE_ID . '&'. bitrix_sessid_get() . "#tb';"
			,
			'WARNING' => 'Y',
			'ICON' => 'btn_delete',
		];
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($errorMessage !== '')
{
	CAdminMessage::ShowMessage([
		'DETAILS'=>$errorMessage,
		'TYPE'=>'ERROR',
		'MESSAGE'=>GetMessage('SAE_ERROR_SAVE_AFF'),
		'HTML'=>true,
	]);
}
?>
<script>
var arSitesArray = [];
var arCurrenciesArray = [];
<?php
$arBaseLangCurrencies = [];
$i = -1;
$dbSiteList = CSite::GetList();
while ($arSite = $dbSiteList->Fetch())
{
	$i++;
	?>
	arSitesArray[<?= $i ?>] = '<?= CUtil::JSEscape($arSite['LID']) ?>';
	arCurrenciesArray[<?= $i ?>] = '<?= CUtil::JSEscape(SiteCurrencyTable::getSiteCurrency($arSite['LID'])) ?>';
	<?php
}
?>
</script>

<form method="POST" action="<?= $APPLICATION->GetCurPage(); ?>?" name="form1">
<?= GetFilterHiddens("filter_"); ?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?= LANGUAGE_ID; ?>">
<input type="hidden" name="ID" value="<?= $ID; ?>">
<?= bitrix_sessid_post(); ?>
<?php
$tabControl->Begin();
$tabControl->BeginNextTab();
	if ($ID > 0):
		?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
		<tr>
			<td width="40%"><?= GetMessage("SAE_DATE_UPDATE")?></td>
			<td width="60%"><?= $fields['TIMESTAMP_X']; ?></td>
		</tr>
		<?php
	endif;
	?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?= GetMessage("SAE_SITE"); ?></td>
		<td width="60%">
			<script>
			function OnChangeSite(val)
			{
				var currency = "";
				for (var i = 0; i < arSitesArray.length; i++)
				{
					if (arSitesArray[i] === val)
					{
						currency = arCurrenciesArray[i];
						break;
					}
				}

				document.getElementById('DIV_PAID_SUM_CURRENCY').innerHTML = currency;
				document.getElementById('DIV_PENDING_SUM_CURRENCY').innerHTML = currency;
			}
			</script>
			<?= CSite::SelectBox("SITE_ID", $fields['SITE_ID'], "", "OnChangeSite(this[this.selectedIndex].value)"); ?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= GetMessage("SAE_USER"); ?></td>
		<td>
			<?php
			$userName = '';
			$userId = (int)$fields['USER_ID'];
			if ($userId > 0)
			{
				$dbUser = CUser::GetByID($userId);
				if ($arUser = $dbUser->Fetch())
				{
					$userName = "[<a class=\"tablebodylink\" title=\""
						. GetMessage("SAE_PROFILE")
						. "\" href=\"/bitrix/admin/user_edit.php?lang="
						. LANGUAGE_ID
						. "&ID=" . $userId
						. "\">"
						. $userId
						. "</a>] ("
						. htmlspecialcharsex($arUser["LOGIN"])
						. ") "
						. htmlspecialcharsex($arUser["NAME"])
						. " "
						. htmlspecialcharsex($arUser["LAST_NAME"])
					;
				}
			}

			echo FindUserID("USER_ID", $fields['USER_ID'], $userName, "form1");
			?>
		</td>
	</tr>
	<tr>
		<td valign="top"><?= GetMessage("SAE_AFFILIATE_REG"); ?></td>
		<td valign="top">
			<input type="text" name="AFFILIATE_ID" value="<?= htmlspecialcharsbx($fields['AFFILIATE_ID']); ?>" size="10" maxlength="10">
			<iframe name="hiddenframe_affiliate" id="id_hiddenframe_affiliate" src="" width="0" height="0" style="width:0; height:0; border: 0"></iframe>
			<input
				type="button"
				class="button"
				name="FindAffiliate"
				onclick="window.open('/bitrix/admin/sale_affiliate_search.php?func_name=SetAffiliateID', '', 'scrollbars=yes,resizable=yes,width=600,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 400)/2-5));"
				value="..."
			>
			<span id="div_affiliate_name"></span>
			<script>
			function SetAffiliateID(id)
			{
				document.form1.AFFILIATE_ID.value = id;
				BX.fireEvent(document.form1.AFFILIATE_ID, 'change');
			}

			function SetAffiliateName(val)
			{
				if (val !== "NA")
					document.getElementById('div_affiliate_name').innerHTML = val;
				else
					document.getElementById('div_affiliate_name').innerHTML = '<?= GetMessage("SAE_NO_AFFILIATE") ?>';
			}

			var affiliateID = '';
			function ChangeAffiliateName()
			{
				if (affiliateID !== document.form1.AFFILIATE_ID.value)
				{
					affiliateID = document.form1.AFFILIATE_ID.value;
					if (affiliateID !== '' && !isNaN(parseInt(affiliateID, 10)))
					{
						document.getElementById('div_affiliate_name').innerHTML = '<i><?= GetMessage("SAE_WAIT") ?></i>';
						window.frames["hiddenframe_affiliate"].location.replace('/bitrix/admin/sale_affiliate_get.php?ID=' + affiliateID + '&func_name=SetAffiliateName');
					}
					else
						document.getElementById('div_affiliate_name').innerHTML = '';
				}
				timerID = setTimeout('ChangeAffiliateName()',2000);
			}
			ChangeAffiliateName();
			</script>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= GetMessage("SAE_PLAN"); ?></td>
		<td>
			<select name="PLAN_ID">
				<?php
				$planId = (int)$fields['PLAN_ID'];
				$dbPlan = CSaleAffiliatePlan::GetList(
					["NAME" => "ASC"],
					[],
					false,
					false,
					[
						"ID",
						"NAME",
						"SITE_ID"
					]
				);
				while ($arPlan = $dbPlan->Fetch())
				{
					$arPlan['ID'] = (int)$arPlan['ID'];
					?><option value="<?= $arPlan["ID"] ?>"<?= ($planId === $arPlan['ID'] ? ' selected' : ''); ?>><?= htmlspecialcharsbx("[".$arPlan["ID"]."] ".$arPlan["NAME"]." (".$arPlan["SITE_ID"].")") ?></option><?php
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAE_FIX_PLAN"); ?>:</td>
		<td>
			<input type="hidden" name="FIX_PLAN" value="N">
			<input type="checkbox" name="FIX_PLAN" value="Y"<?= ($fields['FIX_PLAN'] === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAE_ACTIVE"); ?></td>
		<td>
			<input type="hidden" name="ACTIVE" value="N">
			<input type="checkbox" name="ACTIVE" value="Y"<?= ($fields['ACTIVE'] === 'Y' ? 'checked' : ''); ?>>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= GetMessage("SAE_DATE_REG"); ?>:</td>
		<td>
			<?= CalendarDate("DATE_CREATE", $fields['DATE_CREATE'], "form1", "20", ""); ?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAE_PAYED_SUM"); ?></td>
		<td>
			<input type="text" name="PAID_SUM" size="10" maxlength="15" value="<?= (float)$fields['PAID_SUM']; ?>">
			<span id="DIV_PAID_SUM_CURRENCY"></span>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAE_PENDING_SUM"); ?></td>
		<td>
			<input type="text" name="PENDING_SUM" size="10" maxlength="15" value="<?= (float)$fields['PENDING_SUM']; ?>">
			<span id="DIV_PENDING_SUM_CURRENCY"></span>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAE_LAST_CALC"); ?>:</td>
		<td>
			<?= CalendarDate("LAST_CALCULATE", $fields['LAST_CALCULATE'], "form1", "20", ""); ?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SAE_AFF_SITE"); ?>:</td>
		<td>
			<input type="text" name="AFF_SITE" size="60" maxlength="200" value="<?= htmlspecialcharsbx($fields['AFF_SITE']); ?>">
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?= GetMessage("SAE_AFF_DESCRIPTION"); ?>:</td>
		<td>
			<textarea name="AFF_DESCRIPTION" rows="3" cols="50"><?= htmlspecialcharsbx($fields['AFF_DESCRIPTION']); ?></textarea>
		</td>
	</tr>
	<script>
	OnChangeSite(document.form1.SITE_ID[document.form1.SITE_ID.selectedIndex].value);
	</script>
<?php
$tabControl->EndTab();

$tabControl->Buttons([
	'disabled' => ($saleModulePermissions < 'W'),
	'back_url' => '/bitrix/admin/sale_affiliate_plan.php?lang=' . LANGUAGE_ID . GetFilterParams('filter_'),
]);

$tabControl->End();
?>
</form>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
