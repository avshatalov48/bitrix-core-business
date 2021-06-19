<?php

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

?>
<div class="sonet-entity-error">
	<div class="sonet-entity-error-inner">
		<div class="sonet-entity-error-title"><?= $arResult['TITLE'] ?></div>
		<div class="sonet-entity-error-subtitle"><?= $arResult['DESCRIPTION'] ?></div>
		<div class="sonet-entity-error-img">
			<div class="sonet-entity-error-img-inner"></div>
		</div>
	</div>
</div>
