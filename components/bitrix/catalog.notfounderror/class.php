<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @deprecated
 * @see \InfoError (bitrix:ui.info.error)
 */
class CatalogNotFoundError extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->fillResult();
		$this->includeComponentTemplate();
	}

	private function fillResult(): void
	{
		$this->arResult['TITLE'] = $this->arParams['~ERROR_MESSAGE'] ?? null;
	}
}
