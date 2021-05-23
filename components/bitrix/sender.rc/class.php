<?

use Bitrix\Main\ErrorCollection;
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

class SenderRcComponent extends \Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	public function executeComponent()
	{
		parent::executeComponent();
		parent::prepareResultAndTemplate($this->arResult['COMPONENT_PAGE']);
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_RC_EDIT;
	}

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['SEF_MODE']   = isset($this->arParams['SEF_MODE'])? $this->arParams['SEF_MODE'] : 'Y';
		$this->arParams['SEF_FOLDER'] = isset($this->arParams['SEF_FOLDER'])? $this->arParams['SEF_FOLDER'] : '';
		$this->arParams['ELEMENT_ID'] = isset($this->arParams['ELEMENT_ID'])? $this->arParams['ELEMENT_ID']
			: $this->request->get('id');

		$this->arParams['IFRAME']               = isset($this->arParams['IFRAME'])? $this->arParams['IFRAME'] : true;
		$this->arResult['NAME_TEMPLATE']        = empty($this->arParams['NAME_TEMPLATE'])? CSite::GetNameFormat(false)
			: str_replace(["#NOBR#", "#/NOBR#"], ["", ""], $this->arParams["NAME_TEMPLATE"]);
		$this->arResult['PATH_TO_USER_PROFILE'] = isset($this->arParams['PATH_TO_USER_PROFILE'])
			? $this->arParams['PATH_TO_USER_PROFILE'] : '/company/personal/user/#id#/';

		$this->arParams['CAN_VIEW'] = $this->arResult['CAN_VIEW'] ?? $this->accessController->check(
				$this->getViewAction()
			);

		if (!isset($this->arParams['VARIABLE_ALIASES']))
		{
			$this->arParams['VARIABLE_ALIASES'] = [
				"list" => [],
				"edit" => [],
				"time" => [],
				"stat" => [
					'id' => 'ID'
				],
			];
		}

		$arDefaultUrlTemplates404 = [
			"list" => "list/",
			"add"  => "edit/0/",
			"edit" => "edit/#id#/",
			"time" => "time/#id#/",
			"stat" => "stat/#id#/",
		];

		$componentPage = 'list';
		if ($this->arParams['SEF_MODE'] == 'Y')
		{
			$arDefaultVariableAliases404 = [];
			$arComponentVariables        = ['id'];
			$arVariables                 = [];
			$arUrlTemplates              = CComponentEngine::makeComponentUrlTemplates(
				$arDefaultUrlTemplates404,
				$this->arParams['SEF_URL_TEMPLATES']
			);
			$arVariableAliases           = CComponentEngine::makeComponentVariableAliases(
				$arDefaultVariableAliases404,
				$this->arParams['VARIABLE_ALIASES']
			);
			$componentPage               = CComponentEngine::parseComponentPath(
				$this->arParams['SEF_FOLDER'],
				$arUrlTemplates,
				$arVariables
			);

			if (!(is_string(
					$componentPage
				) && isset($componentPage[0]) && isset($arDefaultUrlTemplates404[$componentPage])))
			{
				$componentPage = 'list';
			}

			CComponentEngine::initComponentVariables(
				$componentPage,
				$arComponentVariables,
				$arVariableAliases,
				$arVariables
			);
			foreach ($arUrlTemplates as $url => $value)
			{
				$key                  = 'PATH_TO_'.mb_strtoupper($url);
				$this->arResult[$key] = isset($this->arParams[$key][0])? $this->arParams[$key]
					: $this->arParams['SEF_FOLDER'].$value;
			}

		}
		else
		{
			$arComponentVariables = [
				isset($this->arParams['VARIABLE_ALIASES']['id'])? $this->arParams['VARIABLE_ALIASES']['id'] : 'id'
			];

			$arDefaultVariableAliases = [
				'id' => 'id'
			];
			$arVariables              = [];
			$arVariableAliases        = CComponentEngine::makeComponentVariableAliases(
				$arDefaultVariableAliases,
				$this->arParams['VARIABLE_ALIASES']
			);
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
		}

		$componentPage = $componentPage == 'add'? 'edit' : $componentPage;

		if (!is_array($this->arResult))
		{
			$this->arResult = [];
		}

		$this->arResult = array_merge(
			[
				'COMPONENT_PAGE'       => $componentPage,
				'VARIABLES'            => $arVariables,
				'ALIASES'              => $this->arParams['SEF_MODE'] == 'Y'? [] : $arVariableAliases,
				'ID'                   => isset($arVariables['id'])? strval($arVariables['id']) : '',
				'PATH_TO_USER_PROFILE' => $this->arParams['PATH_TO_USER_PROFILE']
			],
			$this->arResult
		);
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_RC_VIEW;
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
}