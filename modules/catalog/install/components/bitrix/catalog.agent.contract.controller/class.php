<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogAgentContractControllerComponent extends \CBitrixComponent
{
	private const URL_TEMPLATE_AGENT_CONTRACTOR_DETAIL = 'detail';
	private const URL_TEMPLATE_AGENT_CONTRACTOR_LIST = 'list';

	private bool $isIframe = false;

	public function onPrepareComponentParams($arParams): array
	{
		if (!is_array($arParams))
		{
			$arParams = [];
		}

		$arParams['SEF_URL_TEMPLATES'] = $arParams['SEF_URL_TEMPLATES'] ?? [];
		$arParams['VARIABLE_ALIASES'] = $arParams['VARIABLE_ALIASES'] ?? [];
		$arParams['SEF'] = $arParams['SEF'] ?? 'Y';

		$arParams['BACK_URL'] = $params['BACK_URL'] ?? '/';

		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		$this->initConfig();

		$templateUrls = $this->getTemplateUrls();

		$template = '';
		$variables = [];

		$adminSection = $this->request->isAdminSection();
		if ($adminSection)
		{
			[$template, $variables] = $this->processRegularMode($templateUrls);
		}
		elseif ($this->arParams['SEF_MODE'] === 'Y')
		{
			[$template, $variables] = $this->processSefMode($templateUrls);
		}

		$this->arResult['VARIABLES'] = $variables;

		$this->includeComponentTemplate($template);
	}

	public function isIframeMode(): bool
	{
		return $this->isIframe;
	}

	protected function initConfig(): void
	{
		$this->isIframe = $this->request->get('IFRAME') === 'Y' && $this->request->get('IFRAME_TYPE') === 'SIDE_SLIDER';
	}

	private function getTemplateUrls(): array
	{
		$adminSection = $this->request->isAdminSection();
		if ($adminSection)
		{
			return [
				self::URL_TEMPLATE_AGENT_CONTRACTOR_LIST => '/bitrix/admin/cat_agent_contract.php',
				self::URL_TEMPLATE_AGENT_CONTRACTOR_DETAIL => '/bitrix/admin/cat_agent_contract.php?ID=#AGENT_CONTRACT_ID#',
			];
		}

		return [
			self::URL_TEMPLATE_AGENT_CONTRACTOR_LIST => '',
			self::URL_TEMPLATE_AGENT_CONTRACTOR_DETAIL => 'details/#AGENT_CONTRACT_ID#/',
		];
	}

	private function processSefMode($templateUrls): array
	{
		$templateUrls = CComponentEngine::MakeComponentUrlTemplates($templateUrls, $this->arParams['SEF_URL_TEMPLATES']);

		foreach ($templateUrls as $name => $url)
		{
			$this->arResult['PATH_TO'][strtoupper($name)] = $this->arParams['SEF_FOLDER'] . $url;
		}

		$variableAliases = CComponentEngine::MakeComponentVariableAliases([], $this->arParams['VARIABLE_ALIASES']);

		$variables = [];
		$template = CComponentEngine::ParseComponentPath($this->arParams['SEF_FOLDER'], $templateUrls, $variables);

		if (!is_string($template) || !isset($templateUrls[$template]))
		{
			$template = key($templateUrls);
		}

		CComponentEngine::InitComponentVariables($template, [], $variableAliases, $variables);

		return [$template, $variables];
	}

	protected function processRegularMode($templateUrls): array
	{
		$arComponentVariables = [
			'ID',
		];

		$arDefaultVariableAliases = [
			'AGENT_CONTRACT_ID' => 'ID',
		];

		$variables = [];
		$variableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases, $this->arParams['VARIABLE_ALIASES']);
		CComponentEngine::initComponentVariables(false, $arComponentVariables, $variableAliases, $variables);

		$templateUrls = CComponentEngine::MakeComponentUrlTemplates($templateUrls, $this->arParams['SEF_URL_TEMPLATES']);
		foreach ($templateUrls as $name => $url)
		{
			$this->arResult['PATH_TO'][strtoupper($name)] = $url;
		}

		if (isset($variables['AGENT_CONTRACT_ID']))
		{
			$template = self::URL_TEMPLATE_AGENT_CONTRACTOR_DETAIL;
		}
		else
		{
			$template = key($templateUrls);
		}

		return [$template, $variables];
	}
}
