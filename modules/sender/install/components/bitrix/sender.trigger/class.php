<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;

use Bitrix\Sender\Security;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderTriggerComponent extends \CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (!Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			return false;
		}
		return true;
	}

	protected function initParams()
	{
		$this->arParams['SEF_MODE'] = isset($this->arParams['SEF_MODE']) ? $this->arParams['SEF_MODE'] : 'Y';
		$this->arParams['SEF_FOLDER'] = isset($this->arParams['SEF_FOLDER']) ? $this->arParams['SEF_FOLDER'] : '';
		$this->arParams['ELEMENT_ID'] = isset($this->arParams['ELEMENT_ID']) ? $this->arParams['ELEMENT_ID'] : $this->request->get('id');

		$this->arParams['IFRAME'] = isset($this->arParams['IFRAME']) ? $this->arParams['IFRAME'] : true;
		$this->arResult['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? \CAllSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);
		$this->arResult['PATH_TO_USER_PROFILE'] = isset($this->arParams['PATH_TO_USER_PROFILE']) ? $this->arParams['PATH_TO_USER_PROFILE'] : '/company/personal/user/#id#/';

		if (!isset($this->arParams['VARIABLE_ALIASES']))
		{
			$this->arParams['VARIABLE_ALIASES'] = array(
				"list" => Array(),
				"add" => Array(),
				"edit" => Array(),
				"conditions" => Array(),
				"chain" => Array(),
				"stat" => Array(),
				"recipient" => Array(),
				"letter_add" => Array(),
				"letter_edit" => Array(),
			);
		}

		$arDefaultUrlTemplates404 = array(
			"list" => "list/",
			"add" => "edit/0/",
			"edit" => "edit/#id#/",
			"conditions" => "conditions/#id#/",
			"chain" => "chain/#id#/",
			"stat" => "stat/#id#/",
			"recipient" => "recipient/#id#/",
			"letter_add" => "letter/#id#/0/",
			"letter_edit" => "letter/#id#/#letter_id#/",
		);

		$componentPage = 'list';
		if ($this->arParams['SEF_MODE'] == 'Y')
		{
			$arDefaultVariableAliases404 = array();
			$arComponentVariables = array('id');
			$arVariables = array();
			$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $this->arParams['SEF_URL_TEMPLATES']);
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $this->arParams['VARIABLE_ALIASES']);
			$componentPage = CComponentEngine::parseComponentPath($this->arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables);

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
				'id' => 'ID',
				'letter_id' => 'LETTER_ID',
			);
			$arVariables = array();
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases, $this->arParams['VARIABLE_ALIASES']);
			CComponentEngine::initComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

			if (isset($_REQUEST['conditions']))
			{
				$componentPage = 'conditions';
			}
			elseif (isset($_REQUEST['add']))
			{
				$componentPage = 'add';
			}
			elseif (isset($_REQUEST['edit']))
			{
				$componentPage = 'edit';
			}
			elseif (isset($_REQUEST['chain']))
			{
				$componentPage = 'chain';
			}
			elseif (isset($_REQUEST['stat']))
			{
				$componentPage = 'stat';
			}
			elseif (isset($_REQUEST['recipient']))
			{
				$componentPage = 'recipient';
			}
			elseif (isset($_REQUEST['letter']))
			{
				$componentPage = 'letter';
			}

			/**	@var \CAllMain $APPLICATION */
			global $APPLICATION;
			foreach ($arDefaultUrlTemplates404 as $url => $value)
			{
				$key = 'PATH_TO_'.strtoupper($url);
				$value = explode('/', substr($value, 0, -1));
				foreach ($value as $index => $item)
				{
					$replaceTo = ['ID=#id#', 'LETTER_ID=#letter_id#'];
					if (strpos($url, 'letter') === 0)
					{
						$replaceTo = ['CAMPAIGN_ID=#id#', 'ID=#letter_id#'];
					}
					$value[$index] = str_replace(
						['#id#', '#letter_id#'],
						$replaceTo,
						$item
					);
				}
				$value = implode('&', $value);
				//$value = str_replace('/', '&ID=', $value);
				$this->arResult[$key] = $APPLICATION->GetCurPage() . "?$value";
			}
		}

		$this->arResult['PATH_TO_RECIPIENT'] .= (strpos($this->arResult['PATH_TO_RECIPIENT'], '?') ? '&' : '?') . 'clear_filter=Y&apply_filter=Y';
		$componentPage = $componentPage == 'list' ? 'list' : $componentPage;

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
				'LETTER_ID' => isset($arVariables['letter_id']) ? strval($arVariables['letter_id']) : '',
				'PATH_TO_USER_PROFILE' => $this->arParams['PATH_TO_USER_PROFILE']
			),
			$this->arResult
		);
	}

	protected function prepareResult()
	{
		Security\Agreement::requestFromCurrentUser();

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