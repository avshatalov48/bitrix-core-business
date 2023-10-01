<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Preset;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class SenderAdsComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	protected function initParams()
	{
		$this->arParams['SEF_MODE'] = $this->arParams['SEF_MODE'] ?? 'Y';
		$this->arParams['SEF_FOLDER'] = $this->arParams['SEF_FOLDER'] ?? '';
		$this->arParams['ELEMENT_ID'] = $this->arParams['ELEMENT_ID'] ?? $this->request->get('id');
		$this->arParams['SHOW_CAMPAIGNS'] = isset($this->arParams['SHOW_CAMPAIGNS'])
			&& (bool)$this->arParams['SHOW_CAMPAIGNS'];

		$this->arParams['IFRAME'] = isset($this->arParams['IFRAME']) ? $this->arParams['IFRAME'] : true;
		$this->arResult['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);
		$this->arResult['PATH_TO_USER_PROFILE'] = isset($this->arParams['PATH_TO_USER_PROFILE']) ? $this->arParams['PATH_TO_USER_PROFILE'] : '/company/personal/user/#id#/';

		$this->arParams['CAN_VIEW'] = $this->arResult['CAN_VIEW'] ?? $this->accessController->check(
				$this->getViewAction()
			);

		if (!isset($this->arParams['VARIABLE_ALIASES']))
		{
			$this->arParams['VARIABLE_ALIASES'] = array(
				"list" => Array(),
				"edit" => Array(),
				"time" => Array(),
				"stat" => Array(
					'id' => 'ID'
				),
				"recipient" => Array(
					'id' => 'ID'
				),
			);
		}

		$arDefaultUrlTemplates404 = array(
			"list" => "list/",
			"add" => "edit/0/",
			"edit" => "edit/#id#/",
			"time" => "time/#id#/",
			"recipient" => "recipient/#id#/",
		);

		$componentPage = 'list';
		if ($this->arParams['SEF_MODE'] == 'Y')
		{
			$arDefaultVariableAliases404 = array();
			$arComponentVariables = array('id');
			$arVariables = array();
			$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $this->arParams['SEF_URL_TEMPLATES'] ?? '');
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $this->arParams['VARIABLE_ALIASES'] ?? '');
			$componentPage = CComponentEngine::parseComponentPath($this->arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables);

			if (!(is_string($componentPage) && isset($componentPage[0]) && isset($arDefaultUrlTemplates404[$componentPage])))
			{
				$componentPage = 'list';
			}

			CComponentEngine::initComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
			foreach ($arUrlTemplates as $url => $value)
			{
				$key = 'PATH_TO_'.mb_strtoupper($url);
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
			elseif (isset($_REQUEST['stat']))
			{
				$componentPage = 'stat';
			}
			elseif (isset($_REQUEST['time']))
			{
				$componentPage = 'time';
			}
			elseif (isset($_REQUEST['recipient']))
			{
				$componentPage = 'recipient';
			}

			/**	@var CMain $APPLICATION */
			global $APPLICATION;
			foreach ($arDefaultUrlTemplates404 as $url => $value)
			{
				$key = 'PATH_TO_'.mb_strtoupper($url);
				$value = mb_substr($value, 0, -1);
				$value = str_replace('/', '&ID=', $value);
				$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : null;
				$this->arResult[$key] = $APPLICATION->GetCurPage() . "?$value" . ($lang ? "&lang=$lang" : '');
			}
		}

		$this->arResult['PATH_TO_RECIPIENT'] .= (mb_strpos($this->arResult['PATH_TO_RECIPIENT'], '?')? '&' : '?') . 'clear_filter=Y&apply_filter=Y';
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
		parent::executeComponent();
		parent::prepareResultAndTemplate($this->arResult['COMPONENT_PAGE']);
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_ADS_VIEW;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_ADS_VIEW;
	}
}