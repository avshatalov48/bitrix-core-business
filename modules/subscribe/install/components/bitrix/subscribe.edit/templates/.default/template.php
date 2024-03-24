<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CUser $USER */
/** @var CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
?>
<div class="subscribe-edit">
<?php

foreach ($arResult['MESSAGE'] as $itemValue)
{
	echo ShowMessage(['MESSAGE' => $itemValue, 'TYPE' => 'OK']);
}
foreach ($arResult['ERROR'] as $itemValue)
{
	echo ShowMessage(['MESSAGE' => $itemValue, 'TYPE' => 'ERROR']);
}

//whether to show the forms
if ($arResult['ID'] == 0 && empty($_REQUEST['action']) || CSubscription::IsAuthorized($arResult['ID']))
{
	//show confirmation form
	if ($arResult['ID'] > 0 && $arResult['SUBSCRIPTION']['CONFIRMED'] <> 'Y')
	{
		include 'confirmation.php';
	}
	//show current authorization section
	if ($USER->IsAuthorized() && ($arResult['ID'] == 0 || $arResult['SUBSCRIPTION']['USER_ID'] == 0))
	{
		include 'authorization.php';
	}
	//show authorization section for new subscription
	if ($arResult['ID'] == 0 && !$USER->IsAuthorized())
	{
		if ($arResult['ALLOW_ANONYMOUS'] == 'N' || ($arResult['ALLOW_ANONYMOUS'] == 'Y' && $arResult['SHOW_AUTH_LINKS'] == 'Y'))
		{
			include 'authorization_new.php';
		}
	}
	//setting section
	include 'setting.php';
	//status and unsubscription/activation section
	if ($arResult['ID'] > 0)
	{
		include 'status.php';
	}
	?>
	<p><span class="starrequired">*</span><?php echo GetMessage('subscr_req')?></p>
	<?php
}
else
{
	//subscription authorization form
	include 'authorization_full.php';
}
?>
</div>
