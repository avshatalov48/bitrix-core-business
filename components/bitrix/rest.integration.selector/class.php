<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Im\Bot;
use Bitrix\Rest\Preset\Data\Webhook;
use Bitrix\Rest\Preset\Data\Rest;
use Bitrix\Rest\Preset\Data\Placement;
use Bitrix\Rest\Engine\ScopeManager;

class RestIntegrationSelectComponent extends CBitrixComponent implements Controllerable
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function listKeysSignedParameters()
	{
		return [
			'ACTION',
			'INPUT_NAME'
		];
	}

	protected function initParams()
	{
		$this->arParams['ACTION'] = $this->arParams['ACTION'] ?? 'Method';
		$this->arParams['INPUT_NAME'] = $this->arParams['INPUT_NAME'] ?? 'METHOD';
		$this->arParams['INPUT_SCOPE_NAME'] = $this->arParams['INPUT_SCOPE_NAME'] ?? false;
		$this->arParams['READONLY'] = isset($this->arParams['READONLY']) ? (bool) $this->arParams['READONLY'] : false;
		$this->arParams['MULTIPLE'] = isset($this->arParams['MULTIPLE']) ? (bool) $this->arParams['MULTIPLE'] : false;
		$this->arParams['TITLE'] = $this->arParams['TITLE'] ?? '';
		$this->arParams['ON_CHANGE'] = $this->arParams['ON_CHANGE'] ?? '';
		$this->arParams['TITLE_BUTTON'] = $this->arParams['TITLE_BUTTON'] ?? '';
		$this->arParams['TITLE_SEARCHER_TITLE'] = $this->arParams['TITLE_SEARCHER_TITLE'] ?? '';
		$this->arParams['CAN_REMOVE_TILES'] = isset($this->arParams['CAN_REMOVE_TILES']) ? (bool) $this->arParams['CAN_REMOVE_TILES'] : true;
		$this->arParams['SHOW_BUTTON_ADD'] = isset($this->arParams['SHOW_BUTTON_ADD']) ? (bool) $this->arParams['SHOW_BUTTON_ADD'] : false;
		$this->arParams['SHOW_BUTTON_SELECT'] = isset($this->arParams['SHOW_BUTTON_SELECT']) ? (bool) $this->arParams['SHOW_BUTTON_SELECT'] : true;
		$this->arParams['DUPLICATES'] = isset($this->arParams['DUPLICATES']) ? (bool) $this->arParams['DUPLICATES'] : false;
	}

	protected function prepareResult()
	{
		$this->arResult['SUBSCRIBER_COUNT'] = '';
		$this->arResult['SITE_NAME'] = '';

		$valueList = $this->arParams['LIST'];
		if(!empty($this->arParams['LIST']) && !empty($this->arParams['ACTION']))
		{
			$valueListResult = [];
			if(!is_array($valueList))
			{
				$valueList = [
					$valueList
				];
			}
			$method ='get'.$this->arParams['ACTION'].'Action';
			if(method_exists($this,$method))
			{
				$data = $this->$method();
				$itemList = [];
				if(!empty($data['list']))
				{
					$data = array_column($data['list'],'items');
					foreach ($data as $items)
					{
						$itemList = array_merge($itemList,$items);
					}
				}
				$dataSearch = array_column($itemList,'id');
				foreach ($valueList as $value)
				{
					$key = array_search($value,$dataSearch);
					if($key !== false)
					{
						$valueListResult[] =  $itemList[$key];
					}
				}
				$valueList = $valueListResult;
				unset($valueListResult, $data, $method, $key);
			}
		}
		$this->arResult['TILES'] = $valueList;

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
		$this->errors = new ErrorCollection();
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

		$this->includeComponentTemplate();
	}

	public function getMethodAction()
	{
		$result = [];
		$data = Rest::getBaseMethod();
		if (!empty($data))
		{
			$items = [];
			foreach ($data as $methodList)
			{
				foreach ($methodList as $method)
				{
					$items[] = [
						'id' => $method,
						'name' => $method
					];
				}
			}
			$result['list'][] = [
				'id' => 'all',
				'name' => Loc::getMessage('REST_INTEGRATION_SELECTOR_SECTION_ALL'),
				'items' => $items

			];
		}

		return $result;
	}

	public function getEventAction()
	{
		$result = [];
		$items = Webhook::getList();
		$result['list'][] = [
			'id' => 'all',
			'name' => Loc::getMessage('REST_INTEGRATION_SELECTOR_SECTION_ALL'),
			'items' => $items
		];
		return $result;
	}

	public function getPlacementAction()
	{
		$result = [];
		$items = Placement::getList();
		$result['list'][] = [
			'id' => 'all',
			'name' => Loc::getMessage('REST_INTEGRATION_SELECTOR_SECTION_ALL'),
			'items' => $items
		];
		return $result;
	}

	public function getScopeAction()
	{
		$result = [];
		$data = ScopeManager::getInstance()->getList();
		if ($data)
		{
			$items = [];
			foreach ($data as $scope)
			{
				$items[] = [
					'id' => $scope['code'],
					'name' => $scope['title'],
				];
			}
			$result['list'][] = [
				'id' => 'all',
				'name' => Loc::getMessage('REST_INTEGRATION_SELECTOR_SECTION_ALL'),
				'items' => $items
			];
		}

		return $result;
	}

	public function getNeededScopeAction($code, $action)
	{
		$result = [];
		$action = mb_strtoupper($action);
		$data = Rest::getAllBasicDescription();

		if (!empty($data[$action]))
		{
			$langScope = Application::getDocumentRoot().BX_ROOT. '/modules/rest/scope.php';
			Loc::loadMessages($langScope);

			if ($action === Rest::PLACEMENT)
			{
				$scope = 'placement';
				$name = Loc::getMessage('REST_SCOPE_' . mb_strtoupper($scope));
				$result[] = [
					'id' => $scope,
					'name' => (!empty($name)) ? $name . ' (' . $scope . ')' : $scope
				];
			}

			foreach ($data[$action] as $scope => $codeList)
			{
				if (in_array($code, $codeList) && $scope != \CRestUtil::GLOBAL_SCOPE)
				{
					$name = Loc::getMessage('REST_SCOPE_' . mb_strtoupper($scope));
					$result[] = [
						'id' => $scope,
						'name' => (!empty($name)) ? $name . ' (' . $scope . ')' : $scope
					];
				}
			}
		}

		return $result;
	}

	public function getBotTypeAction()
	{
		$result = [];
		if(Loader::includeModule('im'))
		{
			$items = [
				[
					'id' => Bot::TYPE_BOT,
					'name' => Loc::getMessage('REST_INTEGRATION_SELECTOR_BOT_TYPE_BOT')
				],
				[
					'id' => Bot::TYPE_HUMAN,
					'name' => Loc::getMessage('REST_INTEGRATION_SELECTOR_BOT_TYPE_HUMAN')
				],
				[
					'id' => Bot::TYPE_OPENLINE,
					'name' => Loc::getMessage('REST_INTEGRATION_SELECTOR_BOT_TYPE_OPENLINE')
				],
				[
					'id' => Bot::TYPE_SUPERVISOR,
					'name' => Loc::getMessage('REST_INTEGRATION_SELECTOR_BOT_TYPE_SUPERVISOR')
				],
			];

			$result['list'][] = [
				'id' => 'all',
				'name' => Loc::getMessage('REST_INTEGRATION_SELECTOR_SECTION_ALL'),
				'items' => $items
			];
		}


		return $result;
	}
	public function configureActions()
	{
		return [
			'getMethod' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'getPlacement' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'getBotType' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'getEvent' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'getScope' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
			'getNeededScope' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			],
		];
	}
}