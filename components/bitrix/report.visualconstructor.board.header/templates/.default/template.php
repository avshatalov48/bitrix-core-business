<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Report\VisualConstructor\Helper\Filter;

/** @var \Bitrix\Report\VisualConstructor\Helper\Filter $filter */
$filter = $arResult['FILTER'];

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
		<?if (!$arResult['IS_FRAME_MODE']):?>
				<div class="pagetitle-container pagetitle-flexible-space">
					<?
					if($arResult['FILTER'] instanceof Filter)
					{

						$APPLICATION->IncludeComponent(
							'bitrix:main.ui.filter',
							'',
							$filter->getFilterParameters(
							),
							$component,
							array()
						);
					}
				?></div>

				<?if ($arResult['WITH_ADD_BUTTON']):?>
				<div class="pagetitle-container pagetitle-align-right-container">
					<?
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
				<?endif;?>
		<?else:?>
			<div class="pagetitle-container pagetitle-align-right-container">
			<?php
				/** @var \Bitrix\Report\VisualConstructor\BoardButton $button */
				foreach ($arResult['BOARD_BUTTONS'] as $button)
				{
					$button->flush();
				}
			?>
			</div>
		<?endif;?>
	</div>
<? if (!$isBitrix24Template): ?>
	</div>
<? endif ?>
<?php
if ($isBitrix24Template)
{
	$this->EndViewTarget();
}


if ($arResult['FILTER'] instanceof Filter): ?>
	<? foreach ($arResult['FILTER']->getStringList() as $str): ?>
		<?= $str ?>
	<? endforeach; ?>
	<? if ($arResult['IS_FRAME_MODE']): ?>
		<div class="filter">
			<div class="pagetitle-container pagetitle-flexible-space">
			<?
			$APPLICATION->IncludeComponent(
				'bitrix:main.ui.filter',
				'',
				$filter->getFilterParameters(),
				$component,
				array()
			);
			?>
			</div>
		</div>
	<? endif ?>
<?endif; ?>

