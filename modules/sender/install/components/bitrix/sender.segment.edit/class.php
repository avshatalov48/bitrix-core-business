<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;

use Bitrix\Sender\Connector;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Segment;
use Bitrix\Sender\Security;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderSegmentEditComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var Entity\Segment $entitySegment */
	protected $entitySegment;

	protected function checkRequiredParams()
	{
		return $this->errors->count() == 0;
	}

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
			Security\Access::current()->canModifySegments();
		$this->arParams['CAN_VIEW_CONN_DATA'] = isset($this->arParams['CAN_VIEW_CONN_DATA'])
			?
			$this->arParams['CAN_VIEW_CONN_DATA']
			:
			Security\Access::current()->canModifySegments();
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

		$this->entitySegment->mergeData($data)->save();
		$this->errors->add($this->entitySegment->getErrors());

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

		if (!Security\Access::current()->canViewSegments())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';
		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();

		$this->entitySegment = new Entity\Segment($this->arParams['ID']);
		$this->entitySegment->setFilterOnlyMode($this->arParams['ONLY_CONNECTOR_FILTERS']);
		$this->arResult['ROW'] = $this->entitySegment->getData();

		$connectors = Connector\Manager::getConnectorList();
		foreach ($connectors as $connector)
		{
			$connector->setDataTypeId(null);
		}

		$this->arResult['CONNECTOR'] = array();
		$this->prepareAvailableConnectors($connectors);

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}

		$endpoints = $this->entitySegment->get('ENDPOINTS');
		if (!is_array($endpoints))
		{
			$endpoints = array();
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

		$row = \Bitrix\Sender\ListTable::getRowById($listId);
		if ($row)
		{
			$row['COUNT'] = \Bitrix\Sender\ContactListTable::getCount(array('=LIST_ID' => $listId));
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
	protected function prepareExistedConnectors($connectors, array $filters)
	{
		$result = array();

		$counter = 0;
		$addressCounter = 0;
		$dataCounters = array();
		foreach($connectors as $connector)
		{
			if(!isset($filters[$connector->getModuleId()]))
			{
				continue;
			}

			$filter = $filters[$connector->getModuleId()];
			if(!isset($filter[$connector->getCode()]))
			{
				continue;
			}

			$fieldValuesList = $filters[$connector->getModuleId()][$connector->getCode()];
			foreach($fieldValuesList as $fieldValues)
			{
				$connector->setFieldFormName('post_form');
				if (!is_array($fieldValues))
				{
					$fieldValues = array();
				}
				$connector->setFieldValues($fieldValues);

				$connectorData = $this->prepareConnectorData($connector);
				$connectorData['NUM'] = $counter;
				$connectorData['FORM'] = str_replace('%CONNECTOR_NUM%', $counter, $connectorData['FORM']);

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
			if ($this->arParams['ONLY_CONNECTOR_FILTERS'] && !($connector instanceof Connector\BaseFilter))
			{
				continue;
			}

			/** @var Connector\Base $connector */
			$connector->setFieldPrefix('CONNECTOR_SETTING');
			$result[$connector->getId()] = $this->prepareConnectorData($connector);
		}

		$this->arResult['CONNECTOR']['AVAILABLE'] = $result;
	}

	protected function prepareConnectorData(Connector\Base $connector)
	{
		$dataCounter = $connector->getDataCounter();
		$dataCounterCloned = clone $dataCounter;

		$connectorData = array(
			'ID' => $connector->getId(),
			'NAME' => $connector->getName(),
			'CODE' => $connector->getCode(),
			'MODULE_ID' => $connector->getModuleId(),
			'FORM' => $connector->getForm(),
			'COUNT' => $dataCounterCloned->leave($this->arParams['DATA_TYPE_ID'])->getArray(),
			'DATA_COUNTER' => $dataCounter,
			'IS_FILTER' => $connector instanceof Connector\BaseFilter,
			'IS_RESULT_VIEWABLE' => $connector->isResultViewable() ? 'Y' : 'N',
			'FILTER_ID' => '',
			'FILTER_RAW' => $connector->getFieldValues(),
			'FILTER' => Json::encode($connector->getFieldValues()),
		);

		if ($connector instanceof Connector\BaseFilter)
		{
			$connectorData['FILTER_ID'] = $connector->getUiFilterId();
		}

		$hiddenName = $connector->getFieldName('bx_aux_hidden_field');
		$connectorData['FORM'] .= '<input type="hidden" name="'	. $hiddenName . '" value="0">';

		return $connectorData;
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

		$this->printErrors();
		$this->includeComponentTemplate();
	}
}