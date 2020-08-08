<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductControllerComponent extends CBitrixComponent
{
	private const TEMPLATE_CODE = 'SECTION';

	public function onPrepareComponentParams($params): array
	{
		$params['IFRAME'] = (bool)($params['IFRAME'] ?? $this->request->get('IFRAME') === 'Y');

		return parent::onPrepareComponentParams($params);
	}

	protected function getTemplateUrls(): array
	{
		return [
			'product_details' => '#IBLOCK_ID#/product/#PRODUCT_ID#/',
			'product_copy_details' => '#IBLOCK_ID#/product/0/copy/#COPY_PRODUCT_ID#/',
			'variation_details' => '#IBLOCK_ID#/product/#PRODUCT_ID#/variation/#VARIATION_ID#/',
			'property_creator' => '#IBLOCK_ID#/create_property/#PROPERTY_TYPE#/',
			'property_modify' => '#IBLOCK_ID#/modify_property/#PROPERTY_ID#/',
			'feedback' => 'feedback/',
		];
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
			$this->arResult['PATH_TO'][ToUpper($name)] = $this->arParams['SEF_FOLDER'].$url;
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
			$this->arResult['PATH_TO'][ToUpper($template)] = $currentPage.'?'.self::TEMPLATE_CODE.'='.$template;
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

		$this->includeComponentTemplate($template);
	}
}