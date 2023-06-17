<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$component = $this->getComponent();

if (!in_array($arParams['MODE'], ['PUB', 'LANDING']))
{
	if ($arResult['INFORMER_TARGET_ID'])
	{
		$this->SetViewTarget($arResult['INFORMER_TARGET_ID']);
	}

	$classList = [
		'feed-new-message-informer-place',
	];
	if ($arParams['LOG_ID'] > 0)
	{
		$classList[] = 'feed-new-message-informer-place-hidden';
	}

	?><div class="<?= implode(' ', $classList) ?>"><?php
	if (
		!isset($arParams["SHOW_REFRESH"])
		|| $arParams["SHOW_REFRESH"] !== "N"
	)
	{
		?><div class="feed-new-message-inform-wrap new-message-balloon-wrap" id="sonet_log_counter_2_wrap" style="visibility: hidden;"><?php
			?><div onclick="BX.Livefeed.PageInstance.refresh()" id="sonet_log_counter_2_container" class="feed-new-message-informer"><?php
				?><span class="feed-new-message-inf-text feed-new-message-inf-text-counter new-message-balloon"><?php
					?><span class="feed-new-message-icon new-message-balloon-icon"></span><?php
					?><span class="new-message-balloon-text"><?= Loc::getMessage('SONET_C30_COUNTER_TEXT_1') ?></span><?php
					?><span class="feed-new-message-informer-counter" id="sonet_log_counter_2">0</span><span class="feed-new-message-informer-counter feed-new-message-informer-counter-plus-hidden" id="sonet_log_counter_2_plus">+</span><?php
				?></span><?php
				?><span class="feed-new-message-inf-text feed-new-message-inf-text-reload new-message-balloon --hidden"><?php
					?><?= (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet') ? Loc::getMessage('SONET_C30_T_RELOAD_NEEDED2') : Loc::getMessage('SONET_C30_T_RELOAD_NEEDED')) ?><?php
				?></span><?php
			?></div><?php
		?></div><?php
	}
	else
	{
		?><div class="feed-new-message-inform-wrap"  id="sonet_log_counter_2_wrap" style="visibility: hidden;"></div><?php
	}
	?></div><?php

	if ($arResult['INFORMER_TARGET_ID'])
	{
		$this->EndViewTarget();
	}
}
