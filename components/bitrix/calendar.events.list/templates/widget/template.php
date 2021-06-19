<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
\Bitrix\Main\UI\Extension::load([
	"calendar.util"
]);

$this->setFrameMode(true);
$this->SetViewTarget("sidebar", 100);
$frame = $this->createFrame()->begin();
$this->addExternalCss(SITE_TEMPLATE_PATH."/css/sidebar.css");
?>
<div class="sidebar-widget sidebar-widget-calendar" style="display: none;">
	<div class="sidebar-widget-top">
		<div class="sidebar-widget-top-title"><?=GetMessage("WIDGET_CALENDAR_TITLE")?></div>
		<a href="<?=$arParams["DETAIL_URL"]?>?EVENT_ID=NEW" class="plus-icon"></a>
	</div>
	<div class="sidebar-widget-content calendar-events-wrap"></div>
</div>

<script>
	if (BX && BX.Calendar && BX.Calendar.NextEventList)
	{
		new BX.Calendar.NextEventList({
			'entries': <?= \Bitrix\Main\Web\Json::encode($arResult["ITEMS"])?>,
			'maxEntryAmount': <?= (int)$arParams['EVENTS_COUNT']?>
		});
	}
</script>

<?
$frame->end();
$this->EndViewTarget();
?>

