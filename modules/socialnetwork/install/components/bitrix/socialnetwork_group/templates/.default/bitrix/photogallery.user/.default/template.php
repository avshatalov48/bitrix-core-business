<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

IncludeAJAX();

if (($arParams['PARENT_FATAL_ERROR'] ?? null) === 'Y')
{
	if (!empty($arParams['PARENT_ERROR_MESSAGE']))
	{
		ShowError($arParams['PARENT_ERROR_MESSAGE']);
	}
	else
	{
		ShowNote($arParams['PARENT_NOTE_MESSAGE'], 'notetext-simple');
	}
}

if (
	($arParams['PARENT_FATAL_ERROR'] ?? null) !== 'Y'
	&& ($arParams['SHOW_CONTROLS'] ?? null) === 'Y'
)
{
	?><noindex>
	<div class="photo-top-controls">
		<a rel="nofollow" href="<?= htmlspecialcharsbx($arResult['GALLERY']['LINK']['NEW']) ?>" onclick="EditAlbum('<?= CUtil::JSEscape($arResult['GALLERY']['LINK']['NEW']) ?>'); return false;"><?= Loc::getMessage('P_ADD_ALBUM') ?></a>
		<a rel="nofollow" href="<?= htmlspecialcharsbx($arResult['GALLERY']['LINK']['UPLOAD']) ?>" target="<?= (\Bitrix\Main\Context::getCurrent()->getRequest()->get('IFRAME') !== 'Y' ? '_top' : '') ?>"><?= Loc::getMessage('P_UPLOAD') ?></a>
	</div>
	</noindex>
	<?php
}

if (
	($arParams['PARENT_FATAL_ERROR'] ?? null) !== 'Y'
	&& ($arParams['PARENT_PAGE'] ?? null) === 'group_photo_section'
)
{
	$result = $APPLICATION->IncludeComponent(
		'bitrix:photogallery.section',
		'',
		$arParams['PARENT_PARAMS_SECTION'],
		false,
		[ 'HIDE_ICONS' => 'Y' ]
	);

	if ($result && (int)$result["ELEMENTS_CNT"] > 0)
	{
		// DETAIL LIST
		?>
		<div class="photo-info-box photo-info-box-photo-list">
			<div class="photo-info-box-inner">
				<?
				$componentResult = $APPLICATION->IncludeComponent(
					'bitrix:photogallery.detail.list.ex',
					'',
					$arParams['PARENT_PARAMS_DETAIL_LIST'],
					false,
					[ 'HIDE_ICONS' => 'Y' ]
				);?>
			</div>
		</div>
		<?

		if (empty($componentResult))
		{
			?>
			<style>
				div.photo-page-section div.photo-info-box-photo-list { display: none; }
			</style>
			<?
		}
	}

	if ($result && (int)$result["SECTIONS_CNT"] > 0)
	{
		?>
		<div class="photo-info-box photo-info-box-section-list">
			<div class="photo-info-box-inner">
				<div class="photo-header-big">
					<div class="photo-header-inner"><?= Loc::getMessage("P_ALBUMS") ?></div>
				</div>
				<?
				$componentResult = $APPLICATION->IncludeComponent(
					'bitrix:photogallery.section.list',
					'',
					$arParams['PARENT_PARAMS_SECTION_LIST'],
					false,
					[ 'HIDE_ICONS' => 'Y' ]
				);
				?>
			</div>
		</div>
		<?
		if (empty($componentResult["SECTIONS"]))
		{
			?>
			<style>
				div.photo-page-section div.photo-info-box-section-list { display: none; }
			</style>
			<?
		}
	}
}
