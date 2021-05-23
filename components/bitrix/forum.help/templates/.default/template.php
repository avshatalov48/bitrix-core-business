<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("F_TITLE")?></span></div>
</div>
<div class="forum-info-box forum-help">
	<div class="forum-info-box-inner">
		<?=$arResult["TEXT_MESSAGE"]?>
	</div>
</div>