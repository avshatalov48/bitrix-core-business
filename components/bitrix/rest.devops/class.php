<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Buttons\JsHandler;
use Bitrix\UI\Buttons\Button;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class RestDevOpsComponent extends CBitrixComponent
{

	/**
	 * Check required params.
	 *
	 * @throws SystemException
	 * @throws LoaderException
	 */
	protected function checkRequiredParams()
	{
		if (!Loader::includeModule('rest'))
		{
			throw new SystemException('Module `rest` is not installed.');
		}

		return true;
	}

	/**
	 * Check required params.
	 *
	 * @throws SystemException
	 */
	protected function initParams()
	{
		$this->arParams['SEF_MODE'] = $this->arParams['SEF_MODE'] ?? 'Y';
		$this->arParams['SEF_FOLDER'] = $this->arParams['SEF_FOLDER'] ?? '';
		$this->arParams['ELEMENT_ID'] = $this->arParams['ELEMENT_ID'] ?? intVal($this->request->get('id'));
		$this->arParams['SECTION_CODE'] = $this->arParams['SECTION_CODE'] ?? htmlspecialcharsbx($this->request->get('SECTION_CODE'));
		if (!isset($this->arParams['VARIABLE_ALIASES']))
		{
			$this->arParams['VARIABLE_ALIASES'] = array(
				"index" => [],
				"section" => [],
				"edit" => []
			);
		}

		$arDefaultUrlTemplates404 = array(
			"index" => "",
			"section" => "section/#SECTION_CODE#/",
			"edit" => "edit/#ELEMENT_CODE#/#ID#/",
			"list" => "list/",
			"iframe" => "iframe/",
			"statistic" => "statistic/",
			"placement" => "placement/#PLACEMENT_ID#/",
		);

		if ($this->arParams['SEF_MODE'] === 'Y')
		{
			$arDefaultVariableAliases404 = array();
			$arComponentVariables = array('ID', 'SECTION_CODE','ELEMENT_CODE');
			$arVariables = array();
			$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates(
				$arDefaultUrlTemplates404,
				$this->arParams['SEF_URL_TEMPLATES']
			);
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases(
				$arDefaultVariableAliases404,
				$this->arParams['VARIABLE_ALIASES']
			);
			$componentPage = CComponentEngine::parseComponentPath(
				$this->arParams['SEF_FOLDER'],
				$arUrlTemplates,
				$arVariables
			);

			if (!(isset($componentPage[0], $arDefaultUrlTemplates404[$componentPage]) && is_string($componentPage)))
			{
				$componentPage = 'index';
			}

			CComponentEngine::initComponentVariables(
				$componentPage,
				$arComponentVariables,
				$arVariableAliases,
				$arVariables
			);
			foreach ($arUrlTemplates as $url => $value)
			{
				$key = 'PATH_TO_' . mb_strtoupper($url);
				$this->arResult[$key] = isset($this->arParams[$key][0]) ? $this->arParams[$key]
					: $this->arParams['SEF_FOLDER'] . $value;
			}
		}
		else
		{
			throw new SystemException('support only sef mode.');
		}

		if (!is_array($this->arResult))
		{
			$this->arResult = array();
		}

		$this->arResult = array_merge(
			array(
				'COMPONENT_PAGE' => $componentPage,
				'VARIABLES' => $arVariables,
				'TITLE' => Loc::getMessage('REST_DEVOPS_DEFAULT_TITLE_PAGE'),
				'ALIASES' => $this->arParams['SEF_MODE'] === 'Y' ? array() : $arVariableAliases,
				'ID' => isset($arVariables['ID']) ? (string) $arVariables['ID'] : 0,
				'SECTION_CODE' => isset($arVariables['SECTION_CODE']) ? (string) $arVariables['SECTION_CODE'] : '',
				'ELEMENT_CODE' => isset($arVariables['ELEMENT_CODE']) ? (string) $arVariables['ELEMENT_CODE'] : '',
			),
			$this->arResult
		);

		return true;
	}

	protected function prepareResult()
	{
		return true;
	}

	public function executeComponent()
	{
		try
		{
			$this->initParams();
			$this->checkRequiredParams();
			$this->prepareResult();
			$this->includeComponentTemplate($this->arResult['COMPONENT_PAGE']);
		}
		catch (SystemException $e)
		{
			ShowError($e->getMessage());
		}
		catch (LoaderException $e)
		{
			ShowError($e->getMessage());
		}
	}
}