<?

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Service\RoleDealCategoryService;
use Bitrix\Sender\Connector;
use Bitrix\Sender\ContactListTable;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration\Crm;
use Bitrix\Sender\ListTable;
use Bitrix\Sender\Security;
use Bitrix\Sender\Segment;

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

class SenderSegmentEditComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/**
	 * @var Entity\Segment
	 */
	private $entitySegment;

	protected function initParams()
	{
		$request = Context::getCurrent()->getRequest();

		$this->arParams['ID'] = isset($this->arParams['ID']) ? (int) $this->arParams['ID'] : 0;
		$this->arParams['ID'] = $this->arParams['ID'] ? $this->arParams['ID'] : (int) $this->request->get('ID');

		if (!isset($this->arParams['DATA_TYPE_ID']))
		{
			$this->arParams['DATA_TYPE_ID'] = $request->get('dataTypeId');
		}

		if (!isset($this->arParams['ONLY_CONNECTOR_FILTERS']) || !is_bool($this->arParams['ONLY_CONNECTOR_FILTERS']))
		{
			$this->arParams['ONLY_CONNECTOR_FILTERS'] = true;
		}

		$this->arParams['SHOW_CONTACT_SETS'] = isset($this->arParams['SHOW_CONTACT_SETS']) ? (bool) $this->arParams['SHOW_CONTACT_SETS'] : false;
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? (bool) $this->arParams['SET_TITLE'] : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::getInstance()->canModifySegments();
		$this->arParams['CAN_VIEW_CONN_DATA'] = isset($this->arParams['CAN_VIEW_CONN_DATA'])
			?
			$this->arParams['CAN_VIEW_CONN_DATA']
			:
			$this->accessController->check(ActionDictionary::ACTION_SEGMENT_CLIENT_VIEW);
	}

	protected function preparePost()
	{
		$settings = $this->request->get('CONNECTOR_SETTING');
		if (!is_array($settings))
		{
			$settings = array();
		}

		$data = Array(
			'NAME' => trim($this->request->get('NAME')),
			'HIDDEN' => $this->request->get('HIDDEN') == 'Y' ? 'Y' : 'N',
			'ENDPOINTS' => Connector\Manager::getEndpointFromFields($settings)
		);

		foreach ($data["ENDPOINTS"] as $endpoint)
		{
			if(
				$endpoint["CODE"] === 'crm_client'
				&& !empty($endpoint["FIELDS"])
				&& !isset($endpoint["FIELDS"]["DEAL_CATEGORY_ID"])
			)
			{
				$this->errors->add(
					[
						new \Bitrix\Main\Error(Loc::getMessage('SENDER_SEGMENT_FILTER_DEAL_CATEGORY_ID_ERROR'))
					]
				);
			}

		}

		if($this->errors->isEmpty())
		{
			$this->entitySegment->mergeData($data)->save();
			$this->errors->add($this->entitySegment->getErrors());
		}

		if ($this->errors->isEmpty())
		{
			$path = str_replace('#id#', $this->entitySegment->getId(), $this->arParams['PATH_TO_EDIT']);
			$uri = new Uri($path);
			if ($this->request->get('IFRAME') == 'Y')
			{
				$uri->addParams(array('IFRAME' => 'Y'));
				$uri->addParams(array('IS_SAVED' => 'Y'));
			}
			$path = $uri->getLocator();

			LocalRedirect($path);
		}
	}

	protected function prepareResult()
	{
		if ($this->arParams['SET_TITLE'] == 'Y')
		{
			$GLOBALS['APPLICATION']->SetTitle(
				$this->arParams['ID'] > 0
					?
					Loc::getMessage('SENDER_COMP_SEGMENT_EDIT_TITLE_EDIT')
					:
					Loc::getMessage('SENDER_COMP_SEGMENT_EDIT_TITLE_ADD')
			);
		}

		if (!Security\Access::getInstance()->canViewSegments())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';
		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();

		$this->entitySegment = new Entity\Segment($this->arParams['ID']);
		$this->entitySegment->setFilterOnlyMode($this->arParams['ONLY_CONNECTOR_FILTERS']);
		$this->arResult['ROW'] = $this->entitySegment->getData();
		$this->arResult['CAN_ADD_PERSONAL_CONTACTS'] = $this->accessController->check
		(ActionDictionary::ACTION_SEGMENT_CLIENT_PERSONAL_EDIT);

		$connectors = Connector\Manager::getConnectorList();
		$initialEndpoints = [];

		foreach ($connectors as $connector)
		{
			$connector->setDataTypeId(null);

			if($connector instanceof Connector\BaseFilter)
			{
				$initialEndpoints[] = [
					'CODE' => $connector->getCode(),
					'FIELDS' => [],
					'MODULE_ID' => $connector->getModuleId()
				];
			}
		}

		$this->arResult['CONNECTOR'] = array();
		$this->prepareAvailableConnectors($connectors);

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}

		$endpoints = $this->entitySegment->get('ENDPOINTS');
		if (!is_array($endpoints) || empty($endpoints))
		{
			$endpoints = $initialEndpoints;
		}

		$filters = Connector\Manager::getFieldsFromEndpoint($endpoints);

		$this->prepareExistedConnectors($connectors, $filters);
		$this->prepareExistedContacts();


		$this->arResult['SEGMENT_TILE'] = Segment\TileView::create()->getTile($this->arParams['ID']);
		$this->arResult['IS_SAVED'] = $this->request->get('IS_SAVED') == 'Y';
		$this->arResult['HIDDEN'] = $this->request->get('hidden') === 'Y';

		return true;
	}

	/**
	 * @return void
	 */
	protected function prepareExistedContacts()
	{
		$this->arResult['CONTACTS'] = array(
			'ID' => null,
			'VALUE' => "",
			'TILE_NAME_TEMPLATE' => Loc::getMessage('SENDER_SEGMENT_EDIT_TILE_CONTACT_NAME_TEMPLATE'),
			'TILES' => array()
		);

		$contactConnectorData = null;
		foreach ($this->arResult['CONNECTOR']['EXISTED'] as $connectorData)
		{
			if ($connectorData['ID'] == 'sender_contact_list')
			{
				$contactConnectorData = $connectorData;
			}
		}

		if (!$contactConnectorData)
		{
			return;
		}

		if (!isset($contactConnectorData['FILTER_RAW']))
		{
			return;
		}

		if (!isset($contactConnectorData['FILTER_RAW']['LIST_ID']))
		{
			return;
		}

		$listId = (int) $contactConnectorData['FILTER_RAW']['LIST_ID'];
		if (!$listId)
		{
			return;
		}

		$this->arResult['CONTACTS']['ID'] = $listId;
		$this->arResult['CONTACTS']['VALUE'] = "{\"LIST_ID\":$listId}";

		$row = ListTable::getRowById($listId);
		if ($row)
		{
			$row['COUNT'] = ContactListTable::getCount(array('=LIST_ID' => $listId));
			if (!$this->arParams['SHOW_CONTACT_SETS'])
			{
				$row['NAME'] = str_replace(
					'%count%',
					$row['COUNT'],
					$this->arResult['CONTACTS']['TILE_NAME_TEMPLATE']
				);
			}
			$this->arResult['CONTACTS']['TILES'][] = array(
				'id' => $row['ID'],
				'name' => $row['NAME'],
				'data' => array()
			);
		}
	}

	/**
	 * @param Connector\Base[] $connectors Connectors.
	 * @param array $filters Filters.
	 * @return void
	 */
	protected function prepareExistedConnectors($connectors, array &$filters)
	{
		$result = array();

		$counter = 0;
		$addressCounter = 0;
		$dataCounters = array();
		foreach($connectors as $connector)
		{
			if(
				!isset($filters[$connector->getModuleId()]) ||
				$this->checkConnectorAccessDenied($connector)
			)
			{
				continue;
			}

			$filter = $filters[$connector->getModuleId()];
			if(!isset($filter[$connector->getCode()]))
			{
				continue;
			}

			$fieldValuesList = $filters[$connector->getModuleId()][$connector->getCode()];
			$existedNamedCounters = [];
			foreach($fieldValuesList as $fieldValues)
			{
				$connector->setFieldFormName('post_form');
				if (!is_array($fieldValues))
				{
					$fieldValues = array();
				}
				$connector->setFieldValues($fieldValues);

				$connectorData = $this->prepareConnectorData($connector);

				$connectorData['FILTER_ID'] = preg_replace('/--filter--([^-]+)--/', '%CONNECTOR_NUM%', $connectorData['FILTER_ID']);
				if (preg_match('/--filter--([^-]+)--/', $connectorData['FORM'], $matches))
				{
					$namedCounter = $matches[1];
					if (in_array($namedCounter, $existedNamedCounters))
					{
						$namedCounter .= $counter;
					}
					else
					{
						$existedNamedCounters[] = $namedCounter;
					}
					$namedCounter = '--filter--'.$namedCounter.'--';
					$connectorData['NUM'] = $namedCounter;
					$connectorData['FORM'] = preg_replace('/--filter--([^-]+)--/', $namedCounter, $connectorData['FORM']);
				}
				else
				{
					$connectorData['NUM'] = $counter;
				}
				$connectorData['FORM'] = str_replace('%CONNECTOR_NUM%', $connectorData['NUM'], $connectorData['FORM']);

				$addressCounter += $connectorData['COUNT']['summary'];

				$connectorData['COUNTER'] = Json::encode($connectorData['COUNT']);
				$connectorData['COUNT'] = $connectorData['COUNT']['summary'];

				$counter++;
				$dataCounters[] = $connectorData['DATA_COUNTER'];
				unset($connectorData['DATA_COUNTER']);
				$result[] = $connectorData;
			}
		}

		$this->arResult['CONNECTOR']['EXISTED'] = $result;
		$this->arResult['CONNECTOR']['EXISTED_ADDRESS_COUNT'] = $addressCounter;
		Entity\Segment::updateAddressCounters($this->entitySegment->getId(), $dataCounters);
	}

	/**
	 * @param Connector\Base[] $connectors Connectors.
	 * @return void
	 */
	protected function prepareAvailableConnectors($connectors)
	{
		$result = array();
		foreach($connectors as $connector)
		{
			if ($this->arParams['ONLY_CONNECTOR_FILTERS'] && !($connector instanceof Connector\BaseFilter) ||
				$this->checkConnectorAccessDenied($connector))
			{
				continue;
			}

			/** @var Connector\Base $connector */
			$connector->setFieldPrefix('CONNECTOR_SETTING');
			$result[$connector->getId()] = $this->prepareConnectorData($connector, false);
		}

		$this->arResult['CONNECTOR']['AVAILABLE'] = $result;
	}

	private function checkConnectorAccessDenied($connector)
	{
		return
			($connector->getCode() === 'crm_lead'
			 && !$this->accessController->check(ActionDictionary::ACTION_SEGMENT_LEAD_EDIT))
			||
			($connector->getCode() === 'crm_client'
			 && !$this->accessController->check(ActionDictionary::ACTION_SEGMENT_CLIENT_EDIT)
			)
			||
			($connector->getCode() === 'contact_list'
			 && !$this->accessController->check(ActionDictionary::ACTION_SEGMENT_CLIENT_PERSONAL_EDIT)
			);

	}
	protected function prepareConnectorData(Connector\Base $connector, $calcCount = true)
	{
		$filters = $connector instanceof Connector\BaseFilter ? $connector::getUiFilterFields() : [];
		$fieldValues = $connector->getFieldValues();

		if($connector instanceof Crm\Connectors\Client)
		{
			$this->filterAbleDealCategories($filters);
		}

		$connector->setFieldValues($fieldValues);

		$dataCounter = $calcCount ? $connector->getDataCounter() : new Connector\DataCounter([]);
		$dataCounterCloned = clone $dataCounter;

		$connectorData = array(
			'ID' => $connector->getId(),
			'NAME' => $connector->getName(),
			'CODE' => $connector->getCode(),
			'MODULE_ID' => $connector->getModuleId(),
			'FORM' => method_exists($connector, 'getCustomForm')
				? $connector->getCustomForm(['filter' => $filters])
				: $connector->getForm(),
			'COUNT' => $dataCounterCloned->leave($this->arParams['DATA_TYPE_ID'])->getArray(),
			'DATA_COUNTER' => $dataCounter,
			'IS_FILTER' => $connector instanceof Connector\BaseFilter,
			'IS_RESULT_VIEWABLE' => $connector->isResultViewable() ? 'Y' : 'N',
			'FILTER_ID' => '',
			'FILTER_RAW' => $fieldValues,
			'FILTER' => Json::encode(
				$this->prepareFieldValues(
					$fieldValues,
					(
						$filters
					)
				)
			),
		);

		if ($connector instanceof Connector\BaseFilter)
		{
			$connectorData['FILTER_ID'] = $connector->getUiFilterId();
		}

		$hiddenName = $connector->getFieldName('bx_aux_hidden_field');
		$connectorData['FORM'] .= '<input type="hidden" name="'	. $hiddenName . '" value="0">';

		return $connectorData;
	}

	private function filterAbleDealCategories(&$filters)
	{
		$defaultCategory = [];
		foreach ($filters as $key => $filter)
		{
			if($filter['id'] === Crm\Connectors\Client::DEAL_CATEGORY_ID)
			{
				$dealCategories = (new RoleDealCategoryService())
					->getFilteredDealCategories($this->userId, $filter['items']);

				$filters[$key]['items'] = $dealCategories;
				if(!empty($dealCategories))
				{
					$defaultCategory[Crm\Connectors\Client::DEAL_CATEGORY_ID] = array_keys($dealCategories)[0];
				}
				continue;
			}
			if($filter['id'] === 'DEAL_STAGE_ID')
			{
				$dealCategories = (new RoleDealCategoryService())
					->getFilteredDealCategories($this->userId, Crm\Connectors\Client::getDealCategoryList());

				$currentItems = $filters[$key]['items'];
				$items = [];
				foreach ($currentItems as $itemCode => $item)
				{
					$data = explode(":", $itemCode);
					if(count($data) > 1)
					{
						$dealCategoryId = (int)substr($data[0], 1);
						if(isset($dealCategories[$dealCategoryId]))
						{
							$items[$itemCode] = $item;
						}

						continue;
					}

					$items[$itemCode] = $item;
				}

				$filters[$key]['items'] = $items;
			}
		}

		return $defaultCategory;
	}

	protected function prepareFieldValues(array $fieldValues, array $fields)
	{
		if (empty($fields))
		{
			return $fieldValues;
		}

		$fields = array_combine(
			array_column($fields, 'id'),
			array_values($fields)
			);

		foreach ($fieldValues as $key => $values)
		{
			if (empty($fields[$key]) || empty($fields[$key]['selector']))
			{
				continue;
			}
			if ($fields[$key]['type'] != 'custom_entity' || $fields[$key]['selector']['TYPE'] != 'user')
			{
				continue;
			}

			if (empty($values))
			{
				continue;
			}

			$labelKey = $key . '_label';
			if (!empty($fieldValues[$labelKey]))
			{
				continue;
			}

			$users = \Bitrix\Main\UserTable::getList([
				'select' => ['ID', 'NAME', 'LAST_NAME', 'LOGIN', 'SECOND_NAME'],
				'filter' => ['=ID' => $values]
			])->fetchAll();
			$users = array_combine(array_column($users, 'ID'), $users);

			$labelValues = [];
			foreach ($values as $value)
			{
				if (empty($users[$value]))
				{
					$labelValues[] = 'Unknown user';
					continue;
				}

				$labelValues[] = \CUser::FormatName(
					$this->arParams['NAME_TEMPLATE'],
					$users[$value], true, false
				);
			}
			$fieldValues[$labelKey] = $labelValues;
		}

		return $fieldValues;
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
		parent::prepareResultAndTemplate();
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_SEGMENT_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_SEGMENT_VIEW;
	}
}