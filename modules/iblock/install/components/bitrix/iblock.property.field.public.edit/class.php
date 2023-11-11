<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class IblockPropertyFieldPublicEdit extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams['NAME'] ??= '';
		$arParams['VALUE'] ??= null;

		return $arParams;
	}

	public function executeComponent(): void
	{
		if (empty($this->arParams['PROPERTY']))
		{
			return;
		}

		// general fields
		$this->arResult['MULTIPLE'] = isset($this->arParams['PROPERTY']['MULTIPLE']) && $this->arParams['PROPERTY']['MULTIPLE'] === 'Y';
		$this->arResult['DEFAULT_VALUE'] = $this->arParams['PROPERTY']['DEFAULT_VALUE'] ?? null;

		$this->includeComponentTemplate();
	}
}
