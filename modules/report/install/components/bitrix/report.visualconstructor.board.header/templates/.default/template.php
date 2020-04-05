<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();


$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . ' no-background no-all-paddings pagetitle-toolbar-field-view ');
$isBitrix24Template = SITE_TEMPLATE_ID === "bitrix24";
if ($isBitrix24Template)
{
	$this->SetViewTarget('inside_pagetitle');
}
?>

<? if (!$isBitrix24Template): ?>
	<div class="tasks-interface-filter-container">
<? endif ?>

	<div class="pagetitle-container<? if (!$isBitrix24Template): ?> pagetitle-container-light<? endif ?> pagetitle-flexible-space">
		<?php
		$APPLICATION->IncludeComponent(
			'bitrix:report.visualconstructor.board.filter',
			'',
			array(
				'BOARD_ID' => $arResult['BOARD_ID'],
				'REPORTS_CATEGORIES' => $arResult['REPORTS_CATEGORIES'],
				'FILTER' => $arResult['FILTER'],
			),
			$component,
			array()
		);

		$APPLICATION->IncludeComponent(
			'bitrix:report.visualconstructor.board.controls',
			'',
			array(
				'BOARD_ID' => $arResult['BOARD_ID'],
				'REPORTS_CATEGORIES' => $arResult['REPORTS_CATEGORIES']
			),
			$component,
			array()
		);
		?>
	</div>
<? if (!$isBitrix24Template): ?>
	</div>
<? endif ?>
<?php
if ($isBitrix24Template)
{
	$this->EndViewTarget();
}