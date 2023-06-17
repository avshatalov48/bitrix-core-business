<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @var PersonalOrderSection $component */
/** @var array $arParams */
/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_PRIVATE_PAGE'] !== 'Y' && $arParams['USE_PRIVATE_PAGE_TO_AUTH'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}

if ($arParams["MAIN_CHAIN_NAME"] !== '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_PRIVATE"));
if ($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle(Loc::getMessage("SPS_TITLE_PRIVATE"));
}

if (!$USER->IsAuthorized() || $arResult['SHOW_LOGIN_FORM'] === 'Y')
{
	if ($arParams['USE_PRIVATE_PAGE_TO_AUTH'] !== 'Y')
	{
		ob_start();
		$APPLICATION->AuthForm('', false, false, 'N', false);
		$authForm = ob_get_clean();
	}
	else
	{
		if ($arResult['SHOW_FORGOT_PASSWORD_FORM'] === 'Y')
		{
			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:main.auth.forgotpasswd',
				'.default',
				array(
					'AUTH_AUTH_URL' => $arResult['PATH_TO_PRIVATE'],
//					'AUTH_REGISTER_URL' => 'register.php',
				),
				false
			);
			$authForm = ob_get_clean();
		}
		elseif($arResult['SHOW_CHANGE_PASSWORD_FORM'] === 'Y')
		{
			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:main.auth.changepasswd',
				'.default',
				array(
					'AUTH_AUTH_URL' => $arResult['PATH_TO_PRIVATE'],
//					'AUTH_REGISTER_URL' => 'register.php',
				),
				false
			);
			$authForm = ob_get_clean();
		}
		else
		{
			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:main.auth.form',
				'.default',
				array(
					'AUTH_FORGOT_PASSWORD_URL' => $arResult['PATH_TO_PASSWORD_RESTORE'],
//					'AUTH_REGISTER_URL' => 'register.php',
					'AUTH_SUCCESS_URL' => $arResult['AUTH_SUCCESS_URL'],
					'DISABLE_SOCSERV_AUTH' => $arParams['DISABLE_SOCSERV_AUTH'],
				),
				false
			);
			$authForm = ob_get_clean();
		}
	}

	?>
	<div class="row">
		<?
		if ($arParams['USE_PRIVATE_PAGE_TO_AUTH'] !== 'Y')
		{
			?>
			<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
				<div class="alert alert-danger"><?=GetMessage("SPS_ACCESS_DENIED")?></div>
			</div>
			<?
		}
		?>
		<div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
			<?=$authForm?>
		</div>
	</div>
	<?
}
else
{
	$APPLICATION->IncludeComponent(
		"bitrix:main.profile",
		"",
		Array(
			"SET_TITLE" =>$arParams["SET_TITLE"],
			"AJAX_MODE" => $arParams['AJAX_MODE_PRIVATE'],
			"SEND_INFO" => $arParams["SEND_INFO_PRIVATE"],
			"CHECK_RIGHTS" => $arParams['CHECK_RIGHTS_PRIVATE'],
			"EDITABLE_EXTERNAL_AUTH_ID" => $arParams['EDITABLE_EXTERNAL_AUTH_ID'],
			"DISABLE_SOCSERV_AUTH" => $arParams['DISABLE_SOCSERV_AUTH']
		),
		$component
	);
}
