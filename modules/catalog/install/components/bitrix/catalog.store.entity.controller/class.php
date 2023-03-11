<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Catalog\Config\Feature;

class CatalogStoreEntityController extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams['SEF_MODE'] ??= 'Y';
		$arParams['SEF_FOLDER'] ??= '/';
		$arParams['SEF_URL_TEMPLATES'] ??= [];
		$arParams['VARIABLE_ALIASES'] ??= [];

		return $arParams;
	}

	public function executeComponent()
	{
		if (Loader::includeModule('catalog'))
		{
			if (!Feature::isInventoryManagementEnabled())
			{
				LocalRedirect('/shop/');
			}
		}

		$this->initResult();

		if ($this->arParams['SEF_MODE'] === 'Y')
		{
			[$template, $variables, $variableAliases] = $this->processSefMode();
		}
		$this->arResult['VARIABLES'] = $variables;

		$this->includeComponentTemplate($template);
	}

	private static function getTemplateUrls(): array
	{
		return [
			'list' => '',
			'details' => 'details/#ID#/',
		];
	}

	private function initResult()
	{
		$this->arResult['IS_IFRAME'] =
			$this->request->get('IFRAME') === 'Y'
			&& $this->request->get('IFRAME_TYPE') === 'SIDE_SLIDER'
		;
	}

	private function processSefMode(): array
	{
		$templateUrls = CComponentEngine::MakeComponentUrlTemplates(
			self::getTemplateUrls(),
			$this->arParams['SEF_URL_TEMPLATES']
		);

		foreach ($templateUrls as $name => $url)
		{
			$this->arResult['PATH_TO'][strtoupper($name)] = $this->arParams['SEF_FOLDER'].$url;
		}

		$variableAliases = CComponentEngine::MakeComponentVariableAliases([], $this->arParams['VARIABLE_ALIASES']);

		$variables = [];
		$template = CComponentEngine::ParseComponentPath($this->arParams['SEF_FOLDER'], $templateUrls, $variables);

		if (!is_string($template) || !isset($templateUrls[$template]))
		{
			$template = key($templateUrls);
		}

		CComponentEngine::InitComponentVariables($template, [], $variableAliases, $variables);

		return [$template, $variables, $variableAliases];
	}
}
