<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $is_unread */

$component = $this->getComponent();

if (!in_array($arParams["MODE"], ['PUB', 'LANDING']))
{
	if ($arResult["INFORMER_TARGET_ID"])
	{
		$this->SetViewTarget($arResult["INFORMER_TARGET_ID"]);
	}

	?><div class="feed-new-message-informer-place<?=($arParams["LOG_ID"] > 0 ? " feed-new-message-informer-place-hidden" : "")?>"><?
	if ($arParams["SHOW_REFRESH"] != "N")
	{
		?><div class="feed-new-message-inform-wrap new-message-balloon-wrap" id="sonet_log_counter_2_wrap" style="visibility: hidden;"><?
			?><div onclick="oLF.refresh()" id="sonet_log_counter_2_container" class="feed-new-message-informer"><?
				?><span class="feed-new-message-inf-text feed-new-message-inf-text-counter new-message-balloon"><?
					?><span class="feed-new-message-icon new-message-balloon-icon"></span><?
					?><span class="new-message-balloon-text"><?=GetMessage("SONET_C30_COUNTER_TEXT_1")?></span><?
					?><span class="feed-new-message-informer-counter" id="sonet_log_counter_2">0</span><span class="feed-new-message-informer-counter feed-new-message-informer-counter-plus-hidden" id="sonet_log_counter_2_plus">+</span><?
				?></span><?
				?><span class="feed-new-message-inf-text feed-new-message-inf-text-reload new-message-balloon" style="display: none;"><?
					?><?=GetMessage(\Bitrix\Main\ModuleManager::isModuleInstalled('intranet') ? "SONET_C30_T_RELOAD_NEEDED2" : "SONET_C30_T_RELOAD_NEEDED")?><?
				?></span><?
			?></div><?
		?></div><?
	}
	else
	{
		?><div class="feed-new-message-inform-wrap"  id="sonet_log_counter_2_wrap" style="visibility: hidden;"></div><?
	}
	?></div><?

	if ($arResult["INFORMER_TARGET_ID"])
	{
		$this->EndViewTarget();
	}
}