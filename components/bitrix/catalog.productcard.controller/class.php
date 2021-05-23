<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductControllerComponent extends CBitrixComponent
{
	private const TEMPLATE_CODE = 'SECTION';

	private const URL_TEMPLATE_PRODUCT = 'product_details';
	private const URL_TEMPLATE_COPY_PRODUCT = 'product_copy_details';
	private const URL_TEMPLATE_VARIATION = 'variation_details';
	private const URL_TEMPLATE_CREATE_PROPERTY = 'property_creator';
	private const URL_TEMPLATE_MODIFY_PROPERTY = 'property_modify';
	private const URL_TEMPLATE_FEEDBACK = 'feedback';

	public function onPrepareComponentParams($params): array
	{
		$params['IFRAME'] = (bool)($params['IFRAME'] ?? $this->request->get('IFRAME') === 'Y');

		return parent::onPrepareComponentParams($params);
	}

	public static function getTemplateUrls(): array
	{
		return [
			self::URL_TEMPLATE_PRODUCT => '#IBLOCK_ID#/product/#PRODUCT_ID#/',
			self::URL_TEMPLATE_COPY_PRODUCT => '#IBLOCK_ID#/product/0/copy/#COPY_PRODUCT_ID#/',
			self::URL_TEMPLATE_VARIATION => '#IBLOCK_ID#/product/#PRODUCT_ID#/variation/#VARIATION_ID#/',
			self::URL_TEMPLATE_CREATE_PROPERTY => '#IBLOCK_ID#/create_property/#PROPERTY_TYPE#/',
			self::URL_TEMPLATE_MODIFY_PROPERTY => '#IBLOCK_ID#/modify_property/#PROPERTY_ID#/',
			self::URL_TEMPLATE_FEEDBACK => 'feedback/',
		];
	}

	public static function hasUrlTemplateId(string $templateId): bool
	{
		$templates = self::getTemplateUrls();

		return isset($templates[$templateId]);
	}

	protected function checkModules(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			ShowError(Loc::getMessage('CATALOG_MODULE_IS_NOT_INSTALLED'));

			return false;
		}

		return true;
	}

	protected function checkFeature(): bool
	{
		if (!\Bitrix\Catalog\Config\Feature::isCommonProductProcessingEnabled())
		{
			ShowError(Loc::getMessage('CATALOG_FEATURE_IS_DISABLED'));

			return false;
		}

		return true;
	}

	protected function processSefMode($templateUrls): array
	{
		$templateUrls = CComponentEngine::MakeComponentUrlTemplates($templateUrls, $this->arParams['SEF_URL_TEMPLATES']);

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

	protected function processRegularMode($templateUrls): array
	{
		$variableAliases = CComponentEngine::MakeComponentVariableAliases([], $this->arParams['VARIABLE_ALIASES']);

		$variables = [];
		CComponentEngine::InitComponentVariables(false, [], $variableAliases, $variables);

		$currentPage = $this->request->getRequestedPage();
		$templates = array_keys($templateUrls);

		foreach ($templates as $template)
		{
			$this->arResult['PATH_TO'][strtoupper($template)] = $currentPage.'?'.self::TEMPLATE_CODE.'='.$template;
		}

		$template = $this->request->get(self::TEMPLATE_CODE);

		if ($template === null || !in_array($template, $templates, true))
		{
			$template = key($templateUrls);
		}

		return [$template, $variables, $variableAliases];
	}

	public function executeComponent()
	{
		if (!$this->checkModules() || !$this->checkFeature())
		{
			return;
		}

		$templateUrls = $this->getTemplateUrls();

		if ($this->arParams['SEF_MODE'] === 'Y')
		{
			[$template, $variables, $variableAliases] = $this->processSefMode($templateUrls);
		}
		else
		{
			[$template, $variables, $variableAliases] = $this->processRegularMode($templateUrls);
		}

		$this->arResult['PATH_TO']['USER_PROFILE'] = '/company/personal/user/#user_id#/';

		$this->arResult = array_merge(
			[
				'VARIABLES' => $variables,
				'ALIASES' => $variableAliases,
			],
			$this->arResult
		);

		if (
			\Bitrix\Main\Context::getCurrent()->getRequest()->get('IFRAME') === 'Y'
			|| \Bitrix\Main\Context::getCurrent()->getRequest()->get('mode') === 'dev'
		)
		{
			$this->includeComponentTemplate($template);
		}
		else
		{
			$urlManager = \Bitrix\Iblock\Url\AdminPage\BuilderManager::getInstance();
			$urlBuilder = $urlManager->getBuilder();
			$urlBuilder->setIblockId($variables['IBLOCK_ID'] ?? 0);
			$url = $urlBuilder->getElementListUrl(-1);

			$sliderPath = '';
			if ($template === self::URL_TEMPLATE_PRODUCT)
			{
				$sliderPath = str_replace(
					['#IBLOCK_ID#', '#PRODUCT_ID#'],
					[$variables['IBLOCK_ID'], $variables['PRODUCT_ID']],
					$this->arResult['PATH_TO']['PRODUCT_DETAILS']
				);
			}
			elseif ($template === self::URL_TEMPLATE_VARIATION)
			{
				$sliderPath = str_replace(
					['#IBLOCK_ID#', '#PRODUCT_ID#', '#VARIATION_ID#'],
					[$variables['IBLOCK_ID'], $variables['PRODUCT_ID'], $variables['VARIATION_ID']],
					$this->arResult['PATH_TO']['VARIATION_DETAILS']
				);
			}
			$uri = new \Bitrix\Main\Web\Uri($url);
			$uri->addParams(['slider_path' => $sliderPath]);
			\LocalRedirect($uri->getLocator());
		}
	}
}
