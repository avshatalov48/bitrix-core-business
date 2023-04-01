<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\ActionDictionary;

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

class ConfigRoleSenderComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	protected function initParams()
	{
		$this->arParams['SEF_MODE'] = isset($this->arParams['SEF_MODE']) ? $this->arParams['SEF_MODE'] : 'Y';
		$this->arParams['SEF_FOLDER'] = isset($this->arParams['SEF_FOLDER']) ? $this->arParams['SEF_FOLDER'] : '';

		$this->arParams['IFRAME'] = isset($this->arParams['IFRAME']) ? $this->arParams['IFRAME'] : true;
		$this->arResult['NAME_TEMPLATE'] =
			empty($this->arParams['NAME_TEMPLATE']) ?
				CSite::GetNameFormat(false) :
				str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);

		$this->arParams['ELEMENT_ID'] = isset($this->arParams['ELEMENT_ID']) ?
			$this->arParams['ELEMENT_ID'] : $this->request->get('id');
		$this->arResult['PATH_TO_USER_PROFILE'] =
			isset($this->arParams['PATH_TO_USER_PROFILE']) ?
				$this->arParams['PATH_TO_USER_PROFILE'] : '/company/personal/user/#id#/';

		if (!isset($this->arParams['VARIABLE_ALIASES']))
		{
			$this->arParams['VARIABLE_ALIASES'] = array(
				"edit" => Array(),
				"time" => Array(),
				"stat" => Array(
					'id' => 'ID'
				),
			);
		}

		$arDefaultUrlTemplates404 = array(
			"edit" => "edit/#id#/"
		);

		$componentPage = 'edit';
		if ($this->arParams['SEF_MODE'] == 'Y')
		{
			$arDefaultVariableAliases404 = array();
			$arComponentVariables = array('id');
			$arVariables = array();
			$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates(
				$arDefaultUrlTemplates404, $this->arParams['SEF_URL_TEMPLATES'] ?? ''
			);
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases(
				$arDefaultVariableAliases404, $this->arParams['VARIABLE_ALIASES'] ?? ''
			);
			$componentPage = CComponentEngine::parseComponentPath(
				$this->arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables
			);

			if (!(is_string($componentPage) && isset($componentPage[0]) && isset($arDefaultUrlTemplates404[$componentPage])))
			{
				$componentPage = 'edit';
			}

			CComponentEngine::initComponentVariables(
				$componentPage, $arComponentVariables, $arVariableAliases, $arVariables
			);
			foreach ($arUrlTemplates as $url => $value)
			{
				$key = 'PATH_TO_'.mb_strtoupper($url);
				$this->arResult[$key] = isset($this->arParams[$key][0]) ?
					$this->arParams[$key] : $this->arParams['SEF_FOLDER'] . $value;
			}

		}
		else
		{
			$arComponentVariables = array(
				isset($this->arParams['VARIABLE_ALIASES']['id']) ? $this->arParams['VARIABLE_ALIASES']['id'] : 'id'
			);

			$arDefaultVariableAliases = array(
				'id' => 'id'
			);
			$arVariables = array();
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases(
				$arDefaultVariableAliases, $this->arParams['VARIABLE_ALIASES']
			);
			CComponentEngine::initComponentVariables(
				false, $arComponentVariables, $arVariableAliases, $arVariables
			);

			if (isset($_REQUEST['edit']))
			{
				$componentPage = 'edit';
			}
		}

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

	public function executeComponent()
	{
		parent::executeComponent();
		parent::prepareResultAndTemplate($this->arResult['COMPONENT_PAGE']);
	}


	public function getEditAction()
	{
		return ActionDictionary::ACTION_SETTINGS_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_SETTINGS_EDIT;
	}

	protected function prepareResult()
	{
		return true;
	}
}