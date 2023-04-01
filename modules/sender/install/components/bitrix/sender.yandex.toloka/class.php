<?

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Preset;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderYandexTolokaComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['SEF_MODE'] = isset($this->arParams['SEF_MODE']) ? $this->arParams['SEF_MODE'] : 'Y';

		$this->arParams['SEF_FOLDER'] = isset($this->arParams['SEF_FOLDER']) ? $this->arParams['SEF_FOLDER'] : '';

		$this->arResult['TYPE'] = isset($this->arParams['TYPE']) ?? 'PROJECT';

		$this->arParams['ELEMENT_ID'] = isset($this->arParams['ELEMENT_ID']) ? $this->arParams['ELEMENT_ID'] : $this->request->get('id');

		$this->arParams['SHOW_CAMPAIGNS'] = isset($this->arParams['SHOW_CAMPAIGNS']) ? (bool) $this->arParams['SHOW_CAMPAIGNS'] : false;

		$this->arParams['IFRAME'] = isset($this->arParams['IFRAME']) ? $this->arParams['IFRAME'] : true;
		$this->arResult['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);

		if (!isset($this->arParams['VARIABLE_ALIASES']))
		{
			$this->arParams['VARIABLE_ALIASES'] = array(
				"list" => Array(),
				"edit" => Array(),
			);
		}

		$arDefaultUrlTemplates404 = array(
			"list" => "list/",
			"add" => "edit/0/",
			"uiconfigure" => "configure/#id#/",
			"edit" => "edit/#id#/"
		);

		$componentPage = 'list';
		if ($this->arParams['SEF_MODE'] == 'Y')
		{
			$arDefaultVariableAliases404 = array();
			$arComponentVariables = array('id');
			$arVariables = array();
			$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $this->arParams['SEF_URL_TEMPLATES'] ?? '');
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $this->arParams['VARIABLE_ALIASES'] ?? '');
			$componentPage = CComponentEngine::parseComponentPath($this->arParams['SEF_FOLDER'] ?? '', $arUrlTemplates, $arVariables);

			if (!(is_string($componentPage) && isset($componentPage[0]) && isset($arDefaultUrlTemplates404[$componentPage])))
			{
				$componentPage = 'list';
			}

			CComponentEngine::initComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
			foreach ($arUrlTemplates as $url => $value)
			{
				$key = 'PATH_TO_'.strtoupper($url);
				$this->arResult[$key] = isset($this->arParams[$key][0]) ? $this->arParams[$key] : $this->arParams['SEF_FOLDER'] . $value;
			}

		}
		else
		{
			$arComponentVariables = array(
				isset($this->arParams['VARIABLE_ALIASES']['id']) ? $this->arParams['VARIABLE_ALIASES']['id'] : 'id'
			);

			$arDefaultVariableAliases = array(
				'id' => 'ID'
			);
			$arVariables = array();
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases, $this->arParams['VARIABLE_ALIASES']);
			CComponentEngine::initComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

			if (isset($_REQUEST['edit']))
			{
				$componentPage = 'edit';
			}

			/**	@var \CAllMain $APPLICATION */
			global $APPLICATION;
			foreach ($arDefaultUrlTemplates404 as $url => $value)
			{
				$key = 'PATH_TO_'.strtoupper($url);
				$value = substr($value, 0, -1);
				$value = str_replace('/', '&ID=', $value);
				$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : null;
				$this->arResult[$key] = $APPLICATION->GetCurPage() . "?$value" . ($lang ? "&lang=$lang" : '');
			}
		}

		$componentPage = $componentPage == 'add' ? 'edit' : $componentPage;

		if (!is_array($this->arResult))
		{
			$this->arResult = array();
		}

		$this->arResult = array_merge(
			array(
				'COMPONENT_PAGE' => $componentPage,
				'VARIABLES' => $arVariables,
				'ALIASES' => $this->arParams['SEF_MODE'] == 'Y' ? array(): $arVariableAliases,
				'ID' => isset($arVariables['id']) ? strval($arVariables['id']) : '',
				'PATH_TO_USER_PROFILE' => $this->arParams['PATH_TO_USER_PROFILE'] ?? ''
			),
			$this->arResult
		);
	}

	protected function prepareResult()
	{
		Preset\Installation\Installer::installNewest();

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			$this->printErrors();
			return;
		}

		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate($this->arResult['COMPONENT_PAGE']);
	}
}