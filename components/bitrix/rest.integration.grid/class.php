<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\Grid;
use Bitrix\Main\SystemException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\Preset\Data\Placement;
use Bitrix\Rest\Preset\Data\Webhook;
use Bitrix\Rest\Preset\IntegrationTable;
use Bitrix\Rest\Preset\Provider;
use Bitrix\Rest\Marketplace\Url;

Loc::loadMessages(__FILE__);

class RestIntegrationGridComponent extends CBitrixComponent implements Controllerable
{
	private $defaultPageSize = 10;
	/**
	 * Check required params.
	 *
	 * @throws SystemException
	 * @throws LoaderException
	 */
	protected function checkRequiredParams() : bool
	{
		if (!Loader::includeModule('rest'))
		{
			throw new SystemException('Module `rest` is not installed.');
		}

		return true;
	}

	protected function initParams() : void
	{
		$langScope = Application::getDocumentRoot() . BX_ROOT . '/modules/rest/scope.php';
		Loc::loadMessages($langScope);
		$this->arParams['FILTER_NAME'] = $this->arParams['FILTER_NAME'] ?? 'filterIntegrationListGrid';
		$this->arParams['GRID_ID'] = $this->arParams['GRID_ID'] ?? 'gridIntegrationListGrid';
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] === 'Y' : true;
		$this->arParams['DEFAULT_PAGE_SIZE'] = isset($this->arParams['DEFAULT_PAGE_SIZE']) ? (int) $this->arParams['DEFAULT_PAGE_SIZE'] : $this->defaultPageSize;
	}

	protected function getGridHeader() : array
	{
		$result = [
			[
				'id' => 'ID',
				'name' => Loc::getMessage('REST_INTEGRATION_GRID_GRID_HEADER_ID'),
				'sort' => 'ID',
				'default' => true
			],
			[
				'id' => 'USER_DATA_NAME',
				'name' => Loc::getMessage('REST_INTEGRATION_GRID_GRID_HEADER_USER_ID'),
				'sort' => 'USER_DATA_NAME',
				'default' => true
			],
			[
				'id' => 'TITLE',
				'name' => Loc::getMessage('REST_INTEGRATION_GRID_GRID_HEADER_TITLE'),
				'sort' => 'TITLE',
				'default' => true
			],
			[
				'id' => 'SCOPE',
				'name' => Loc::getMessage('REST_INTEGRATION_GRID_GRID_HEADER_SCOPE'),
				'sort' => false,
				'default' => true
			],
			[
				'id' => 'OUTGOING_EVENTS',
				'name' => Loc::getMessage('REST_INTEGRATION_GRID_GRID_HEADER_OUTGOING_EVENTS'),
				'sort' => false,
				'default' => true
			],
			[
				'id' => 'WIDGET_LIST',
				'name' => Loc::getMessage('REST_INTEGRATION_GRID_GRID_HEADER_OUTGOING_WIDGET'),
				'sort' => false,
				'default' => true
			]
		];

		return $result;
	}

	protected function getGridParams() : array
	{
		$result = [];
		$gridOption = new Grid\Options($this->arParams["GRID_ID"]);

		if ($option = $gridOption->GetOptions())
		{
			if (!empty($option['views'][$option['current_view']]['last_sort_by']))
			{
				$by = $option['views']['default']['last_sort_by'];
				$order = ($option['views']['default']['last_sort_order'] == 'asc') ? 'ASC' : 'DESC';
				$result['order'] = [
					$by => $order,
				];
			}
			if ($option['views'][$option['current_view']]['page_size'])
			{
				$result['limit'] = $option['views'][$option['current_view']]['page_size'];
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getFilter() : array
	{
		$result = [];
		$filterOption = new Options($this->arParams["FILTER_NAME"]);
		$filter = $filterOption->getFilter();

		if ($filter['ID'] > 0)
		{
			$result['=ID'] = intVal($filter['ID']);
		}

		if (!empty($filter['FIND']))
		{
			$result['%TITLE'] = $filter['FIND'];
		}

		if ($filter['USER_ID'] > 0)
		{
			$result['=USER_ID'] = intVal($filter['USER_ID']);
		}

		return $result;
	}

	protected function getGridFilterHeader() : array
	{
		$result = [];
		$isAdmin = \CRestUtil::isAdmin();

		$result[] = [
			'id' => 'ID',
			'name' => Loc::getMessage('REST_INTEGRATION_GRID_FILTER_ID'),
			'type' => 'integer',
			'default' => true
		];

		if ($isAdmin)
		{
			$result[] = [
				'id' => 'USER_ID',
				'name' => Loc::getMessage('REST_INTEGRATION_GRID_FILTER_USER'),
				'type' => 'dest_selector',
				'default' => true,
				'params' => [
					'context' => 'REST_INTEGRATION_GRID_FILTER_USER_ID',
					'multiple' => 'N',
					'contextCode' => 'U',
					'enableAll' => 'N',
					'enableSonetgroups' => 'N',
					'allowEmailInvitation' => 'N',
					'allowSearchEmailUsers' => 'N',
					'departmentSelectDisable' => 'Y',
					'isNumeric' => 'Y',
					'prefix' => 'U',
				],
			];
		}

		return $result;
	}

	/**
	 * @throws SystemException
	 */
	protected function processResultData() : void
	{
		global $USER;
		$isAdmin = \CRestUtil::isAdmin();
		$userId = $USER->GetID();
		$url = $this->arParams['PATH_TO_EDIT'];
		$paramsSidePanel = Json::encode(
			[
				'cacheable' => false,
				'requestMethod' => 'post',
				'requestParams' => [
					'needGridOpen' => false,
					'gridId' => $this->arParams['GRID_ID'],
				],
			]
		);
		$paramsGetList = [
			'order' => ['ID'],
			'filter' => $this->getFilter(),
			'select' => [
				'*',
				'USER_DATA_' => 'USER',
			],
			'count_total' => true,
		];

		if (!$isAdmin)
		{
			$paramsGetList['filter']['=USER_ID'] = $userId;
		}

		$nav = new PageNavigation($this->arParams['FILTER_NAME'] . 'nav');
		$nav->initFromUri();
		$paramsGetList['limit'] = $nav->getLimit();
		$params = $this->getGridParams();
		$paramsGetList = array_merge($paramsGetList, $params);
		$nav->setPageSize($paramsGetList['limit']);
		$paramsGetList['offset'] = $nav->getOffset();

		$res = IntegrationTable::getList($paramsGetList);

		$nav->setRecordCount($res->getCount());
		$eventList = array_column(Webhook::getList(), 'name', 'id');
		$widgetList = array_column(Placement::getList(), 'name', 'id');

		$items = [];
		while ($item = $res->Fetch())
		{
			$item['TITLE'] = htmlspecialcharsbx($item['TITLE']);
			$item['USER_DATA_NAME'] = htmlspecialcharsbx(
				implode(
					' ',
					[
						$item['USER_DATA_NAME'],
						$item['USER_DATA_LAST_NAME'],
					]
				)
			);

			$item['SCOPE'] = array_map(
				function ($value)
				{
					$result = Loc::getMessage('REST_SCOPE_' . mb_strtoupper($value));
					if (empty($result))
					{
						$result = $value;
					}
					return htmlspecialcharsbx($result);
				},
				$item['SCOPE']
			);
			$item['SCOPE'] = implode(', ', $item['SCOPE']);

			$item['OUTGOING_EVENTS'] = array_map(
				function ($value) use ($eventList)
				{
					return (!empty($eventList[$value])) ? $eventList[$value] : htmlspecialcharsbx($value);
				},
				$item['OUTGOING_EVENTS']
			);
			$item['OUTGOING_EVENTS'] = implode(', ', $item['OUTGOING_EVENTS']);

			$item['WIDGET_LIST'] = array_map(
				function ($value) use ($widgetList)
				{
					return (!empty($widgetList[$value])) ? $widgetList[$value] : htmlspecialcharsbx($value);
				},
				$item['WIDGET_LIST']
			);
			$item['WIDGET_LIST'] = implode(', ', $item['WIDGET_LIST']);

			$item['URL'] = str_replace(
				[
					'#SECTION_CODE#',
					'#ELEMENT_CODE#',
					'#ID#',
				],
				[
					$item['SECTION_CODE'],
					$item['ELEMENT_CODE'],
					$item['ID'],
				],
				$url
			);

			$actions = [];
			$actions[] = [
				'TEXT' => Loc::getMessage('REST_INTEGRATION_GRID_ACTION_EDIT'),
				'ONCLICK' => "BX.SidePanel.Instance.open('" . $item['URL'] . "', " . $paramsSidePanel . ");",
				'DEFAULT' => true,
			];
			if ($isAdmin && $item['APP_ID'] > 0)
			{
				$actions[] = [
					'TEXT' => Loc::getMessage('REST_INTEGRATION_GRID_ACTION_APP_RIGHTS'),
					'ONCLICK' => "BX.rest.Marketplace.setRights('" . $item['APP_ID'] . "');",
					'DEFAULT' => false,
				];

				if ($item['APPLICATION_ONLY_API'] === 'N')
				{
					$actions[] = [
						'TEXT' => Loc::getMessage('REST_INTEGRATION_GRID_ACTION_APP_OPEN'),
						'ONCLICK' => "BX.SidePanel.Instance.open('" . Url::getApplicationUrl($item['APP_ID']) . "', " . $paramsSidePanel . ");",
						'DEFAULT' => false,
					];
				}
			}

			if ($isAdmin || $userId == $item['ID'])
			{
				$actions[] = [
					'TEXT' => Loc::getMessage('REST_INTEGRATION_GRID_ACTION_DELETE'),
					'ONCLICK' => "BX.rest.integration.grid.delete('" . $item['ID'] . "', '" . $item['ELEMENT_CODE'] . "');",
					'DEFAULT' => false,
				];
			}

			$items[] = [
				'id' => $item['ID'],
				'columns' => $item,
				'actions' => $actions,
			];
		}

		$this->arResult['FILTER'] = $this->getGridFilterHeader();
		$this->arResult['GRID_HEADERS'] = $this->getGridHeader();
		$this->arResult['GRID_ITEMS'] = $items;
		$this->arResult['NAV_OBJECT'] = $nav;
	}

	public function executeComponent()
	{
		try
		{
			$this->initParams();
			$this->processResultData();
			$this->checkRequiredParams();
			$this->includeComponentTemplate();
		}
		catch (SystemException $e)
		{
			ShowError($e->getMessage());
		}
		catch (LoaderException $e)
		{
			ShowError($e->getMessage());
		}

		return $this->arResult;
	}

	public function deleteAction()
	{
		$result = [
			'result' => 'error',
		];

		$request = Application::getInstance()->getContext()->getRequest();
		if ($request->isAjaxRequest())
		{
			$id = intVal($request->getPost("id"));
			$result = Provider::deleteIntegration($id);
		}

		return $result;
	}

	public function configureActions()
	{
		return [
			'delete' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			]
		];
	}
}