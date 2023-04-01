<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Integration\Crm\Connectors;

use Bitrix\Crm\Category\DealCategory;
use Bitrix\Crm\CompanyTable as CrmCompanyTable;
use Bitrix\Crm\ContactTable as CrmContactTable;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Main\Application;
use Bitrix\Main\DB\Result;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\Filter\AdditionalDateType;
use Bitrix\Sender\Connector;
use Bitrix\Sender\Connector\ResultView;
use Bitrix\Sender\Integration\Sender\Holiday;
use Bitrix\Sender\Recipient\Type;

Loc::loadMessages(__FILE__);

/**
 * Class Client
 * @package Bitrix\Sender\Integration\Crm\Connectors
 */
class Client extends Connector\BaseFilter implements Connector\IncrementallyConnector
{
	const PRODUCT_SOURCE_ORDERS_ALL = "ORDERS_ALL";
	const PRODUCT_SOURCE_ORDERS_PAID = "ORDERS_PAID";
	const PRODUCT_SOURCE_ORDERS_UNPAID = "ORDERS_UNPAID";
	const PRODUCT_SOURCE_DEALS_ALL = "DEALS_ALL";
	const PRODUCT_SOURCE_DEALS_PROCESS = "DEALS_PROCESS";
	const PRODUCT_SOURCE_DEALS_SUCCESS = "DEALS_SUCCESS";
	const PRODUCT_SOURCE_DEALS_FAILURE = "DEALS_FAILURE";
	const API_VERSION = 3;
	const YES = 'Y';
	const NO = 'N';
	const DEAL_CATEGORY_ID = "DEAL_CATEGORY_ID";

	private $crmEntityFilter = null;

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_NAME');
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return "crm_client";
	}

	/**
	 * Get queries.
	 *
	 * @return Entity\Query[]
	 */
	public function getQueries($selectList = [])
	{
		$queries = array();
		$clientType = $this->getFieldValue('CLIENT_TYPE');

		if (!$clientType || $clientType === \CCrmOwnerType::ContactName)
		{
			$queryCollection = $this->prepareQueryCollection($this->getContactQuery());
			$queries = array_merge($queries, $queryCollection);
		}
		if (!$clientType || $clientType === \CCrmOwnerType::CompanyName)
		{
			$queryCollection = $this->prepareQueryCollection($this->getCompanyQuery());
			$queries = array_merge($queries, $queryCollection);
		}
		return $queries;
	}

	/**
	 * Get queries.
	 *
	 * @param int $offset
	 * @param int $limit
	 * @param string|null $excludeType
	 *
	 * @return Entity\Query[]
	 */
	public function getLimitedQueries(int $offset, int $limit, string $excludeType = null): array
	{
		$queries = array();
		$clientType = $this->getFieldValue('CLIENT_TYPE');

		if (!$clientType || $clientType === \CCrmOwnerType::ContactName)
		{
			if($excludeType !== \CCrmOwnerType::ContactName)
			{
				$this->prepareQueryForType($this->prepareQueryCollection($this->getContactQuery())[0], $offset, $limit,
					$queries);
			}
		}
		if (!$clientType || $clientType === \CCrmOwnerType::CompanyName)
		{
			if($excludeType !== \CCrmOwnerType::CompanyName)
			{
				$this->prepareQueryForType($this->prepareQueryCollection($this->getCompanyQuery())[0], $offset, $limit, $queries);
			}
		}

		return $queries;
	}

	public function getEntityLimitInfo(): array
	{
		$lastContact = \CCrmContact::GetListEx(
			['ID' => 'DESC'],
			[
				'CHECK_PERMISSIONS' => 'N',
				'@CATEGORY_ID' => 0,
			],
			false,
			['nTopCount' => '1'],
			['ID'],
			['limit' => 1]
		)->Fetch();

		$lastCompany = \CCrmCompany::GetListEx(
			['ID' => 'DESC'],
			[
				'CHECK_PERMISSIONS' => 'N',
				'@CATEGORY_ID' => 0,
			],
			false,
			['nTopCount' => '1'],
			['ID']
		)->Fetch();

		$lastContactId = $lastContact['ID'] ?? 0;
		$lastCompanyId = $lastCompany['ID'] ?? 0;

		return [
			'lastContactId' => $lastContactId,
			'lastCompanyId' => $lastCompanyId,
			'lastId' => max($lastCompanyId, $lastContactId),
		];
	}

	public function getLimitedData(int $offset, int $limit): ?Result
	{
		$entityInfo = $this->getEntityLimitInfo();
		$excludedClass = $offset > $entityInfo['lastContactId'] ? \CCrmOwnerType::ContactName : null;
		$excludedClass = $offset > $entityInfo['lastCompanyId'] ? \CCrmOwnerType::CompanyName : $excludedClass;

		$query = QueryData::getUnionizedQuery($this->getLimitedQueries($offset, $limit, $excludedClass));
		return !$query ? null : QueryData::getUnionizedData($query);
	}

	protected function prepareQueryForType(Query $query, int $from, int $to, array &$queries)
	{
		$query->whereBetween('ID', $from, $to);
		$queryCollection = $this->prepareQueryCollection($query);
		$queries = array_merge($queries, $queryCollection);
	}

	protected function getContactQuery()
	{
		$query = CrmContactTable::query();
		$query->setFilter($this->getCrmEntityFilter(\CCrmOwnerType::ContactName));
		if ($query->getEntity()->hasField('CATEGORY_ID'))
		{
			$query->where('CATEGORY_ID', 0);
		}
		$this->addCrmEntityReferences($query);
		$query->registerRuntimeField(new Entity\ExpressionField('CRM_ENTITY_TYPE_ID', \CCrmOwnerType::Contact));
		$query->registerRuntimeField(new Entity\ExpressionField('CRM_ENTITY_TYPE', '\''.\CCrmOwnerType::ContactName.'\''));
		$query->registerRuntimeField(new Entity\ExpressionField('CRM_COMPANY_ID', 0));
		$query->registerRuntimeField(new Entity\ExpressionField('CONTACT_ID', '%s', ['ID']));
		$query->registerRuntimeField(Helper::createExpressionMultiField(\CCrmOwnerType::ContactName, 'EMAIL'));
		$query->registerRuntimeField(Helper::createExpressionMultiField(\CCrmOwnerType::ContactName, 'PHONE'));
		$query->setSelect(
				[
					'CRM_ENTITY_ID'  => 'ID',
					'NAME',
					'CRM_ENTITY_TYPE_ID',
					'CRM_ENTITY_TYPE',
					'CRM_CONTACT_ID' => 'CONTACT_ID',
					'CRM_COMPANY_ID',
					'HAS_EMAIL',
					'HAS_PHONE',
					'HAS_IMOL',
				]
		);

		return $query;
	}

	protected function getCompanyQuery()
	{
		$query = CrmCompanyTable::query();
		$query->setFilter($this->getCrmEntityFilter(\CCrmOwnerType::CompanyName));
		if ($query->getEntity()->hasField('CATEGORY_ID'))
		{
			$query->where('CATEGORY_ID', 0);
		}
		$this->addCrmEntityReferences($query);
		$query->registerRuntimeField(new Entity\ExpressionField('CRM_ENTITY_TYPE_ID', \CCrmOwnerType::Company));
		$query->registerRuntimeField(new Entity\ExpressionField('CRM_ENTITY_TYPE', '\''.\CCrmOwnerType::CompanyName.'\''));
		$query->registerRuntimeField(new Entity\ExpressionField('CONTACT_ID', 0));
		$query->registerRuntimeField(new Entity\ExpressionField('COMPANY_ID', '%s', ['ID']));
		$query->registerRuntimeField(Helper::createExpressionMultiField(\CCrmOwnerType::CompanyName, 'EMAIL'));
		$query->registerRuntimeField(Helper::createExpressionMultiField(\CCrmOwnerType::CompanyName, 'PHONE'));
		$query->setSelect(
			[
				'CRM_ENTITY_ID'  => 'ID',
				'NAME'           => 'TITLE',
				'CRM_ENTITY_TYPE_ID',
				'CRM_ENTITY_TYPE',
				'CRM_CONTACT_ID' => 'CONTACT_ID',
				'CRM_COMPANY_ID' => 'COMPANY_ID',
				'HAS_EMAIL',
				'HAS_PHONE',
				'HAS_IMOL',
			]
		);

		return $query;
	}

	protected function addCrmEntityReferences(Entity\Query $query)
	{
		$docTypes = array();
		$docType = $this->getFieldValue('DOC_TYPE');
		if ($docType)
		{
			$docTypes[] = $docType;
		}
		else
		{
			foreach (array_keys(self::getCrmDocumentTypes()) as $entityTypeName)
			{
				$filter = $this->getCrmReferencedEntityFilter($entityTypeName);
				if (count($filter) === 0)
				{
					continue;
				}

				$docTypes[] = $entityTypeName;
			}
		}

		foreach ($docTypes as $docType)
		{
			$refClassName = "\\Bitrix\\Crm\\" . ucfirst(mb_strtolower($docType)) . "Table";
			if (!class_exists($refClassName))
			{
				continue;
			}

			if ($query->getEntity()->getName() === 'Contact')
			{
				$ref = array('=this.ID' => 'ref.CONTACT_ID');
			}
			elseif ($query->getEntity()->getName() === 'Company')
			{
				$ref = array('=this.ID' => 'ref.COMPANY_ID');
			}
			else
			{
				continue;
			}

			$runtimeFieldName = "SGT_$docType";
			$filter = $this->getCrmReferencedEntityFilter($docType);
			$joinType = $filter[$docType]['JOIN_TYPE']??'INNER';
			unset($filter[$docType]['JOIN_TYPE']);

			$query->registerRuntimeField(null, new Entity\ReferenceField(
				$runtimeFieldName,
				$refClassName,
				$ref,
				array('join_type' => $joinType)
			));

			foreach ($filter as $key => $value)
			{
				$pattern = "/^[\W]{0,2}$docType\./";
				if (preg_match($pattern, $key))
				{
					$key = str_replace("$docType.", "$runtimeFieldName.", $key);
				}

				$query->addFilter($key, $value);
			}

			$runtime = Helper::getRuntimeByEntity($docType);
			foreach ($runtime as $item)
			{
				$item = new Entity\ExpressionField(
					$item['name'],
					str_replace("$docType.", "$runtimeFieldName.", $item['expression']),
					array_map(
						function ($from) use ($docType, $runtimeFieldName)
						{
							return str_replace("$docType.", "$runtimeFieldName.", $from);
						},
						$item['buildFrom']
					)
				);
				$query->registerRuntimeField($item);
			}
		}

		$entityTypeName = mb_strtoupper($query->getEntity()->getName());
		$runtime = Helper::getRuntimeByEntity($entityTypeName);
		foreach ($runtime as $item)
		{
			$item = new Entity\ExpressionField(
				$item['name'],
				$item['expression'],
				array_map(
					function ($from) use ($entityTypeName)
					{
						return str_replace("$entityTypeName.", "", $from);
					},
					$item['buildFrom']
				)
			);
			$query->registerRuntimeField($item);
		}

		$filterFields = $query->getFilter();
		if (array_key_exists('NO_PURCHASES', $filterFields))
		{
			$noPurchasesFilter = $filterFields['NO_PURCHASES'];
			$productSource = $filterFields['PRODUCT_SOURCE'];

			unset($filterFields['NO_PURCHASES']);
			$query->setFilter($filterFields);

			$this->addNoPurchasesFilter($query, $noPurchasesFilter, $productSource);
		}
		if (array_key_exists('DEAL', $filterFields))
		{
			$query->where(\Bitrix\Main\Entity\Query::filter()
				->logic('or')
				->where($filterFields['DEAL'])
			);
			unset($filterFields['DEAL']);
			$query->setFilter($filterFields);
		}
	}

	/**
	 * Add filter to exclude contacts/companies who has deals/orders in $filterValue period
	 * @param Entity\Query $query Modifying query.
	 * @param array $filterValue No purchases period.
	 * @param array $productSource Purchases source (deal, order, etc).
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function addNoPurchasesFilter($query, $filterValue, $productSource)
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();

		$dealQuery = \Bitrix\Crm\DealTable::query();

		if (is_array($productSource))
		{
			$semantics = [];
			if (in_array(self::PRODUCT_SOURCE_DEALS_PROCESS, $productSource))
			{
				$semantics[] = \Bitrix\Crm\PhaseSemantics::PROCESS;
			}
			if (in_array(self::PRODUCT_SOURCE_DEALS_SUCCESS, $productSource))
			{
				$semantics[] = \Bitrix\Crm\PhaseSemantics::SUCCESS;
			}
			if (in_array(self::PRODUCT_SOURCE_DEALS_FAILURE, $productSource))
			{
				$semantics[] = \Bitrix\Crm\PhaseSemantics::FAILURE;
			}

			if ($semantics && count($semantics) < 3)
			{
				$dealQuery->whereIn('STAGE_SEMANTIC_ID', $semantics);
			}
		}

		$dealsFilter = [];
		foreach ($filterValue as $filterCode => $date)
		{
			$dealsFilter[str_replace('%PURCHASE_DATE%', 'DATE_CREATE', $filterCode)] =
				new SqlExpression($sqlHelper->convertToDbDateTime(new DateTime($date)));
		}
		$dealQuery->setFilter($dealsFilter);

		$orderQuery = null;
		if (Helper::isCrmSaleEnabled())
		{
			$orderQuery = \Bitrix\Crm\Binding\OrderContactCompanyTable::query();
			$orderQuery->addSelect('ENTITY_ID', 'EID');

			if (is_array($productSource))
			{
				if (in_array(self::PRODUCT_SOURCE_ORDERS_PAID, $productSource) &&
					!in_array(self::PRODUCT_SOURCE_ORDERS_UNPAID, $productSource))
				{
					$orderQuery->where('ORDER.PAYED', true);
				}
				if (!in_array(self::PRODUCT_SOURCE_ORDERS_PAID, $productSource) &&
					in_array(self::PRODUCT_SOURCE_ORDERS_UNPAID, $productSource))
				{
					$orderQuery->where('ORDER.PAYED', false);
				}
			}
			$orderQuery->whereNotNull('ENTITY_ID');

			$ordersFilter = [];
			foreach ($filterValue as $filterCode => $date)
			{
				$ordersFilter[str_replace('%PURCHASE_DATE%', 'ORDER.DATE_INSERT', $filterCode)] =
					new SqlExpression($sqlHelper->convertToDbDateTime(new DateTime($date)));
			}
			$orderQuery->setFilter($ordersFilter);
		}

		if ($query->getEntity()->getName() === 'Contact')
		{
			$dealQuery->addSelect('CONTACT_ID', 'EID');
			$dealQuery->whereNotNull('CONTACT_ID');
			if ($orderQuery)
			{
				$orderQuery->where('ENTITY_TYPE_ID', \CCrmOwnerType::Contact);
			}
		}
		elseif ($query->getEntity()->getName() === 'Company')
		{
			$dealQuery->addSelect('COMPANY_ID', 'EID');
			$dealQuery->whereNotNull('COMPANY_ID');
			if ($orderQuery)
			{
				$orderQuery->where('ENTITY_TYPE_ID', \CCrmOwnerType::Company);
			}
		}

		$dealsAreRequired = empty($productSource) ||
			array_intersect($productSource, [self::PRODUCT_SOURCE_DEALS_PROCESS, self::PRODUCT_SOURCE_DEALS_SUCCESS, self::PRODUCT_SOURCE_DEALS_FAILURE]);
		$ordersAreRequired = empty($productSource) ||
			array_intersect($productSource, [self::PRODUCT_SOURCE_ORDERS_PAID, self::PRODUCT_SOURCE_ORDERS_UNPAID]);

		$idSubQuery = false;
		if ($orderQuery && $dealsAreRequired && $ordersAreRequired)
		{
			$idSubQuery = new SqlExpression($dealQuery->getQuery() . ' UNION ALL ' . $orderQuery->getQuery());
		}
		elseif ($orderQuery && $ordersAreRequired)
		{
			$idSubQuery = $orderQuery;
		}
		elseif ($dealsAreRequired)
		{
			$idSubQuery = $dealQuery;
		}
		if ($idSubQuery)
		{
			$query->whereNotIn('ID', $idSubQuery);
		}
	}

	protected function prepareQueryCollection(Entity\Query $query)
	{
		$result = [$query];

		$filterFields = $query->getFilter();
		$productSource = $filterFields['PRODUCT_SOURCE'] ?? '';
		unset($filterFields['PRODUCT_SOURCE']);
		$query->setFilter($filterFields);

		$productFilterKey = '=PRODUCT_ID';
		if (array_key_exists($productFilterKey, $filterFields))
		{
			$productIds = $filterFields[$productFilterKey];

			unset($filterFields[$productFilterKey]);
			$query->setFilter($filterFields);

			$productIds = array_merge($productIds, $this->getProductSkuIds($productIds));
			if (empty($productIds))
			{
				return $result;
			}

			$result = $this->getQueryCollectionForProductsFilter($query, $productIds, $productSource);
		}

		return $result;
	}

	protected function getQueryCollectionForProductsFilter(Entity\Query $query, $productIds, $productSource)
	{
		$orderRef = [
			'=this.ID' => 'ref.ENTITY_ID',
		];
		$dealRef = [];

		$entityName = $query->getEntity()->getName();
		if ($entityName === 'Contact')
		{
			$orderRef['ref.ENTITY_TYPE_ID'] = new SqlExpression('?i', \CCrmOwnerType::Contact);
			$dealRef['=this.ID'] = 'ref.CONTACT_ID';
			$extraQuery = $this->getContactQuery();
		}
		elseif ($entityName === 'Company')
		{
			$orderRef['ref.ENTITY_TYPE_ID'] = new SqlExpression('?i', \CCrmOwnerType::Company);
			$dealRef['=this.ID'] = 'ref.COMPANY_ID';
			$extraQuery = $this->getCompanyQuery();
		}
		else
		{
			return [$query];
		}

		$query->whereIn('SGT_DEAL.PRODUCT_ROW.PRODUCT_ID', $productIds);
		$semantics = [];

		if (is_array($productSource))
		{
			if (in_array(self::PRODUCT_SOURCE_DEALS_PROCESS, $productSource))
			{
				$semantics[] = \Bitrix\Crm\PhaseSemantics::PROCESS;
			}
			if (in_array(self::PRODUCT_SOURCE_DEALS_SUCCESS, $productSource))
			{
				$semantics[] = \Bitrix\Crm\PhaseSemantics::SUCCESS;
			}
			if (in_array(self::PRODUCT_SOURCE_DEALS_FAILURE, $productSource))
			{
				$semantics[] = \Bitrix\Crm\PhaseSemantics::FAILURE;
			}
		}

		switch (count($semantics))
		{
			case 1:
				$dealRef['ref.STAGE_SEMANTIC_ID'] = new SqlExpression('?', $semantics[0]);
				break;
			case 2:
				$dealRef['@ref.STAGE_SEMANTIC_ID'] = new SqlExpression('?, ?', $semantics[0], $semantics[1]);
				break;
		}

		$query->registerRuntimeField(new Entity\ReferenceField(
			'SGT_DEAL',
			'\Bitrix\Crm\DealTable',
			$dealRef,
			array('join_type' => 'LEFT')
		));

		$query->addSelect("SGT_DEAL.ID", "SGT_DEAL_ID");
		$extraQuery->setFilter($query->getFilter()); // apply actual user filter

		$extraQuery->registerRuntimeField(new Entity\ReferenceField(
			'PROD_CRM_ORDER',
			'\Bitrix\Crm\Binding\OrderContactCompanyTable',
			$orderRef,
			array('join_type' => 'LEFT')
		));
		$extraQuery->addSelect("PROD_CRM_ORDER.ID", "PROD_CRM_ORDER_ID");

		$extraQuery->registerRuntimeField(new Entity\ReferenceField(
			'PROD_CRM_ORDER_PRODUCT',
			'\Bitrix\Sale\Internals\BasketTable',
			[
				'=this.PROD_CRM_ORDER.ORDER_ID' => 'ref.ORDER_ID'
			],
			array('join_type' => 'LEFT')
		));

		$extraQuery->whereIn('PROD_CRM_ORDER_PRODUCT.PRODUCT_ID', $productIds);

		if (is_array($productSource))
		{
			if (in_array(self::PRODUCT_SOURCE_ORDERS_PAID, $productSource) &&
				!in_array(self::PRODUCT_SOURCE_ORDERS_UNPAID, $productSource))
			{
				$extraQuery->where('PROD_CRM_ORDER.ORDER.PAYED', true);
			}
			if (!in_array(self::PRODUCT_SOURCE_ORDERS_PAID, $productSource) &&
				in_array(self::PRODUCT_SOURCE_ORDERS_UNPAID, $productSource))
			{
				$extraQuery->where('PROD_CRM_ORDER.ORDER.PAYED', false);
			}
		}

		$result = [];
		$dealsAreRequired = empty($productSource) ||
			array_intersect($productSource, [self::PRODUCT_SOURCE_DEALS_PROCESS, self::PRODUCT_SOURCE_DEALS_SUCCESS, self::PRODUCT_SOURCE_DEALS_FAILURE]);
		$ordersAreRequired = empty($productSource) ||
			array_intersect($productSource, [self::PRODUCT_SOURCE_ORDERS_PAID, self::PRODUCT_SOURCE_ORDERS_UNPAID]);

		$dataTypeId = $this->getDataTypeId();
		if ($dataTypeId == Type::CRM_ORDER_PRODUCT_CONTACT_ID && $ordersAreRequired)
		{
			if ($entityName === 'Contact')
			{
				$result[] = $extraQuery;
			}
		}
		elseif ($dataTypeId == Type::CRM_ORDER_PRODUCT_COMPANY_ID && $ordersAreRequired)
		{
			if ($entityName === 'Company')
			{
				$result[] = $extraQuery;
			}
		}
		elseif ($dataTypeId == Type::CRM_DEAL_PRODUCT_CONTACT_ID && $dealsAreRequired)
		{
			if ($entityName === 'Contact')
			{
				$result[] = $query;
			}
		}
		elseif ($dataTypeId == Type::CRM_DEAL_PRODUCT_COMPANY_ID && $dealsAreRequired)
		{
			if ($entityName === 'Company')
			{
				$result[] = $query;
			}
		}
		else
		{
			if ($dealsAreRequired)
			{
				$result[] = $query;
			}
			if ($ordersAreRequired)
			{
				$result[] = $extraQuery;
			}
		}
		return $result;
	}

	protected function getCrmReferencedEntityFilter($entityTypeName)
	{
		return $this->getCrmEntityFilter($entityTypeName, true);
	}

	protected function getCrmEntityFilter($entityTypeName, $isReferenced = false)
	{
		if ($this->crmEntityFilter === null)
		{
			$this->crmEntityFilter = Helper::getFilterByEntity(
				self::getUiFilterFields(),
				$this->getFieldValues(),
				array_keys(self::getCrmDocumentTypes())
			);
		}

		if (isset($this->crmEntityFilter[$entityTypeName]))
		{
			$filter = $this->crmEntityFilter[$entityTypeName];
		}
		else
		{
			$filter = array();
		}

		if ($isReferenced && count($filter) === 0)
		{
			return $filter;
		}

		$commonNames = ['ASSIGNED_BY_ID', 'EMAIL', 'PHONE', 'NAME'];
		if ($isReferenced)
		{
			$commonNames = ['ASSIGNED_BY_ID'];
		}
		foreach ($commonNames as $commonName)
		{
			$value = $this->getFieldValue($commonName);
			if (!$value)
			{
				continue;
			}

			if (in_array($commonName, ['EMAIL', 'PHONE', 'NAME']))
			{
				$commonName = "%$entityTypeName.$commonName";
			}
			else
			{
				$commonName = "=$entityTypeName.$commonName";
			}
			$filter[$commonName] = $value;
		}

		if ($isReferenced)
		{
			return $filter;
		}

		foreach ($filter as $key => $value)
		{
			$pattern = "/^([\W]{0,2})$entityTypeName\./";
			if (!preg_match($pattern, $key))
			{
				continue;
			}

			unset($filter[$key]);
			$key = preg_replace($pattern, '$1', $key);
			$filter[$key] = $value;
		}

		return $filter;
	}

	protected static function getCrmDocumentTypes()
	{
		$types = array(\CCrmOwnerType::Deal);

		$list = array();
		foreach ($types as $typeId)
		{
			$typeName = \CCrmOwnerType::resolveName($typeId);
			$typeCaption = \CCrmOwnerType::getDescription($typeId);
			$list[$typeName] = $typeCaption;
		}

		return $list;
	}

	/**
	 * Get data count by type.
	 *
	 * @return array
	 */
	protected function getDataCountByType()
	{
		if (!$this->hasFieldValues())
		{
			return array();
		}

		return QueryCount::getUnionizedCount($this->getQueries(), $this->getDataTypeId());
	}

	/**
	 * Get data.
	 *
	 * @return array|\Bitrix\Main\DB\Result
	 */
	public function getData()
	{
		if (!$this->hasFieldValues())
		{
			return array();
		}

		$query = QueryData::getUnionizedQuery(
			$this->getQueries(),
			$this->getDataTypeId(),
			$this->getResultView()->getNav()
		);

		return QueryData::getUnionizedData($query);
	}

	/**
	 * Get personalize field list.
	 *
	 * @return array
	 */
	public static function getPersonalizeList()
	{
		return Loader::includeModule('crm') ? array_merge(
			Helper::getPersonalizeList(),
			Helper::buildPersonalizeList(\CCrmOwnerType::ContactName),
			Helper::buildPersonalizeList(\CCrmOwnerType::CompanyName)
		) : Helper::getPersonalizeList();
	}

	public static function getDealCategoryList()
	{
		return Loader::includeModule('crm') ? DealCategory::getSelectListItems(true) : [];
	}

	public static function getDealCategoryStageList()
	{
		return Loader::includeModule('crm') ? DealCategory::getFullStageList() : [];
	}

	/**
	 * Get filter fields.
	 *
	 * @return array
	 */
	public static function getUiFilterFields()
	{
		$list = [
			[
				'id' => 'EMAIL',
				'type' => 'string',
				'sender_segment_filter' => '%EMAIL',
				'sender_internal' => true
			],
			[
				'id' => 'PHONE',
				'type' => 'string',
				'sender_segment_filter' => '%PHONE',
				'sender_internal' => true
			],
			[
				'id' => 'NAME',
				'type' => 'string',
				'sender_segment_filter' => '%NAME',
				'sender_internal' => true
			],
			[
				'id' => 'CLIENT_ID',
				'type' => 'string',
				'params' => array('hidden' => self::YES),
				"name" => 'ID',
				"default" => true,
				'filter_callback' => ['\Bitrix\Sender\Integration\Crm\Connectors\Helper', 'getIdFilter']
			],
		];

		$list[] = array(
			"id" => "DOC_TYPE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DOC_TYPE'),
			"type" => "list",
			"items" => self::getCrmDocumentTypes(),
			"sender_segment_filter" => false,
			"default" => true,
		);

		$list[] = array(
			"id" => "CLIENT_TYPE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CLIENT_TYPE'),
			"type" => "list",
			"items" => array(
				"" => Loc::getMessage(
					'SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CLIENT_TYPE_NOT_SET',
					[
						'%default%' => \CCrmOwnerType::getDescription(\CCrmOwnerType::Contact) . ", " . \CCrmOwnerType::getDescription(\CCrmOwnerType::Company),
					]
				),
				\CCrmOwnerType::ContactName => \CCrmOwnerType::getDescription(\CCrmOwnerType::Contact),
				\CCrmOwnerType::CompanyName => \CCrmOwnerType::getDescription(\CCrmOwnerType::Company),
			),
			"sender_segment_filter" => false,
			"default" => true
		);

		$list[] = array(
			"id" => "DEAL_DATE_CREATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_DATE_CREATE'),
			"type" => "date",
			"include" => [
				AdditionalDateType::CUSTOM_DATE,
				AdditionalDateType::PREV_DAY,
				AdditionalDateType::NEXT_DAY,
				AdditionalDateType::MORE_THAN_DAYS_AGO,
				AdditionalDateType::AFTER_DAYS,
			],
			"allow_years_switcher" => true,
			"default" => true
		);

		$stageList = self::getDealCategoryStageList();
		$list[] = array(
			"id" => "DEAL_STAGE_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_STATUS_ID'),
			"type" => "list",
			'params' => array('multiple' => self::YES),
			"items" => $stageList,
			"default" => true
		);

		$list[] = array(
			"id" => "CONTACT_SOURCE_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CONTACT_SOURCE_ID'),
			"type" => "list",
			'params' => array('multiple' => self::YES),
			"items" => \CCrmStatus::GetStatusList('SOURCE'),
			"default" => true
		);

		$list[] = array(
			'id' => 'CLIENT_COMMUNICATION_TYPE',
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_COMMUNICATION_TYPE'),
			'params' => array('multiple' => self::YES),
			'default' => true,
			'type' => 'list',
			'items' => \CCrmFieldMulti::PrepareListItems(array(
				\CCrmFieldMulti::PHONE,
				\CCrmFieldMulti::EMAIL,
				\CCrmFieldMulti::IM
			)),
			'filter_callback' => ['\Bitrix\Sender\Integration\Crm\Connectors\Helper', 'getCommunicationTypeFilter']
		);

		$list[] = array(
			"id" => "CLIENT_NO_PURCHASES_DATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_NO_PURCHASES_DATE'),
			"type" => "date",
			"exclude" => [
				\Bitrix\Main\UI\Filter\DateType::TOMORROW,
				\Bitrix\Main\UI\Filter\DateType::NEXT_DAYS,
				\Bitrix\Main\UI\Filter\DateType::NEXT_WEEK,
				\Bitrix\Main\UI\Filter\DateType::NEXT_MONTH,
			],
			"default" => true,
			'messages' => [
				'MAIN_UI_FILTER_FIELD_SUBTYPE_NONE' => ''
			],
			'filter_callback' => ['\Bitrix\Sender\Integration\Crm\Connectors\Helper', 'getNoPurchasesFilter']
		);

		if (Helper::isCrmSaleEnabled())
		{
			$list[] = array(
				'id' => 'CLIENT_PRODUCT_ID',
				"name" => Loc::getMessage("SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_PRODUCT_ID"),
				'default' => true,
				'type' => 'dest_selector',
				'partial' => true,
				'params' => array(
					'multiple' => self::YES,
					'apiVersion' => self::API_VERSION,
					'context' => 'SENDER_FILTER_PRODUCT_ID',
					'contextCode' => 'CRM',
					'useClientDatabase' => self::NO,
					'enableAll' => self::NO,
					'enableDepartments' => self::NO,
					'enableUsers' => self::NO,
					'enableSonetgroups' => self::NO,
					'allowEmailInvitation' => self::NO,
					'allowSearchEmailUsers' => self::NO,
					'departmentSelectDisable' => self::YES,
					'addTabCrmProducts' => self::YES,
					'enableCrm' => self::YES,
					'enableCrmProducts' => self::YES,
					'convertJson' => self::YES
				),
			);

			$list[] = array(
				'id' => 'CLIENT_PRODUCT_SOURCE',
				"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_PRODUCT_SOURCE'),
				'default' => true,
				'type' => 'list',
				'params' => array(
					'multiple' => self::YES,
				),
				'items' => [
					"" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_PRODUCT_SOURCE_ANY'),
					self::PRODUCT_SOURCE_ORDERS_PAID => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_PRODUCT_SOURCE_ORDERS_PAID'),
					self::PRODUCT_SOURCE_ORDERS_UNPAID => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_PRODUCT_SOURCE_ORDERS_UNPAID'),
					self::PRODUCT_SOURCE_DEALS_PROCESS => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_PRODUCT_SOURCE_DEALS_PROCESS'),
					self::PRODUCT_SOURCE_DEALS_SUCCESS => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_PRODUCT_SOURCE_DEALS_SUCCESS'),
					self::PRODUCT_SOURCE_DEALS_FAILURE => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_PRODUCT_SOURCE_DEALS_FAILURE'),
				],
				'filter_callback' => ['\Bitrix\Sender\Integration\Crm\Connectors\Helper', 'productSourceFilter']
			);
		}
		else
		{
			$list[] = array(
				'id' => 'DEAL_PRODUCT_ROW.PRODUCT_ID',
				"name" => Loc::getMessage("SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_PRODUCT_ID"),
				'default' => true,
				'type' => 'dest_selector',
				'partial' => true,
				'params' => array(
					'multiple' => self::YES,
					'apiVersion' => self::API_VERSION,
					'context' => 'SENDER_FILTER_PRODUCT_ID',
					'contextCode' => 'CRM',
					'useClientDatabase' => self::NO,
					'enableAll' => self::NO,
					'enableDepartments' => self::NO,
					'enableUsers' => self::NO,
					'enableSonetgroups' => self::NO,
					'allowEmailInvitation' => self::NO,
					'allowSearchEmailUsers' => self::NO,
					'departmentSelectDisable' => self::YES,
					'addTabCrmProducts' => self::YES,
					'enableCrm' => self::YES,
					'enableCrmProducts' => self::YES,
					'convertJson' => self::YES
				)
			);
		}


		$list[] = PhaseSemantics::getListFilterInfo(
			\CCrmOwnerType::Deal,
			array(
				'id' => 'DEAL_STAGE_SEMANTIC_ID',
				"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_STATUS_SEMANTIC_ID'),
				'default' => true,
				'params' => array('multiple' => self::YES)
			),
			true
		);

		$list[] = array(
			"id" => "CONTACT_POST",
			'type' => 'string',
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CONTACT_POST'),
			'params' => array('multiple' => self::YES),
			"default" => false
		);

		$list[] = array(
			"id" => "ASSIGNED_BY_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_ASSIGNED_BY_ID'),
			'type' => 'dest_selector',
			'params' => array(
				'context' => 'SENDER_FILTER_ASSIGNED_BY_ID',
				'multiple' => self::YES,
				'contextCode' => 'U',
				'enableAll' => self::NO,
				'enableSonetgroups' => self::NO,
				'allowEmailInvitation' => self::NO,
				'allowSearchEmailUsers' => self::NO,
				'departmentSelectDisable' => self::YES,
				'isNumeric' => self::YES,
				'prefix' => 'U'
			),
			"sender_segment_filter" => false,
			"default" => false
		);

		foreach ([\CCrmOwnerType::Company, \CCrmOwnerType::Contact, \CCrmOwnerType::Deal] as $entityTypeId)
		{
			$entityTypeCaption = \CCrmOwnerType::getDescription($entityTypeId);
			$entityTypeName = \CCrmOwnerType::resolveName($entityTypeId);
			$fieldId = "{$entityTypeName}_ASSIGNED_BY_ID";
			$list[] = array(
				"id" => $fieldId,
				"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_ASSIGNED_BY_ID') . " ($entityTypeCaption)",
				'type' => 'dest_selector',
				'params' => array(
					'context' => 'SENDER_FILTER_ASSIGNED_BY_ID',
					'multiple' => self::YES,
					'contextCode' => 'U',
					'enableAll' => self::NO,
					'enableSonetgroups' => self::NO,
					'allowEmailInvitation' => self::NO,
					'allowSearchEmailUsers' => self::NO,
					'departmentSelectDisable' => self::YES,
					'isNumeric' => self::YES,
					'prefix' => 'U'
				),
				//"sender_segment_filter" => false,
				"default" => false
			);
		}

		$list[] = array(
			"id" => "CONTACT_BIRTHDATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CONTACT_BIRTHDATE'),
			'type' => 'date',
			"include" => [
				AdditionalDateType::CUSTOM_DATE,
				AdditionalDateType::PREV_DAY,
				AdditionalDateType::NEXT_DAY,
				AdditionalDateType::MORE_THAN_DAYS_AGO,
				AdditionalDateType::AFTER_DAYS,
			],
			"allow_years_switcher" => true,
			"default" => false,
		);

		//we need to filter able deals
		$list[] = array(
			'id' => self::DEAL_CATEGORY_ID,
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_CATEGORY_ID'),
			'params' => array('multiple' => self::YES),
			'default' => true,
			'type' => 'list',
			'required' => true,
			'valueRequired' => true,
			'items' => self::getDealCategoryList(),
			'filter_callback' => ['\Bitrix\Sender\Integration\Crm\Connectors\Helper', 'getDealCategoryFilter']
		);

		$list[] = array(
			"id" => "DEAL_TYPE_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_TYPE_ID'),
			"type" => "list",
			'params' => array('multiple' => self::YES),
			"items" => \CCrmStatus::GetStatusList('DEAL_TYPE'),
			"default" => false
		);

		$list[] = array(
			"id" => "DEAL_OPPORTUNITY",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_OPPORTUNITY'),
			"type" => "number",
			"default" => false
		);

		$list[] = array(
			"id" => "DEAL_CLOSEDATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_CLOSEDATE'),
			"type" => "date",
			"include" => [
				AdditionalDateType::CUSTOM_DATE,
				AdditionalDateType::PREV_DAY,
				AdditionalDateType::NEXT_DAY,
				AdditionalDateType::MORE_THAN_DAYS_AGO,
				AdditionalDateType::AFTER_DAYS,
			],
			"allow_years_switcher" => true,
			"default" => false
		);

		$list[] = array(
			'id' => 'COMPANY_COMPANY_TYPE',
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_COMPANY_TYPE'),
			'params' => array('multiple' => self::YES),
			'default' => false,
			'type' => 'list',
			'items' => \CCrmStatus::GetStatusList('COMPANY_TYPE'),
		);

		$list[] = array(
			'id' => 'CONTACT_TYPE_ID',
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CONTACT_TYPE'),
			'params' => array('multiple' => self::YES),
			'default' => false,
			'type' => 'list',
			'items' => \CCrmStatus::GetStatusList('CONTACT_TYPE'),
		);

		$list[] = array(
			'id' => 'CONTACT_HONORIFIC',
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CONTACT_HONORIFIC'),
			'params' => array('multiple' => self::YES),
			'default' => false,
			'type' => 'list',
			'items' => \CCrmStatus::GetStatusList('HONORIFIC'),
		);

		$list[] = array(
			'id' => 'COMPANY_INDUSTRY',
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_COMPANY_INDUSTRY'),
			'params' => array('multiple' => self::YES),
			'default' => false,
			'type' => 'list',
			'items' => \CCrmStatus::GetStatusList('INDUSTRY'),
		);


		$entityTypes = array_merge(
			array(
				\CCrmOwnerType::ContactName,
				\CCrmOwnerType::CompanyName,
			),
			array_keys(self::getCrmDocumentTypes())
		);
		foreach ($entityTypes as $entityTypeName)
		{
			$entityTypeId = \CCrmOwnerType::resolveId($entityTypeName);
			$entityTypeCaption = \CCrmOwnerType::getDescription($entityTypeId);
			$ufList = Helper::getFilterUserFields($entityTypeId);
			foreach ($ufList as $item)
			{
				if (isset($item['name']))
				{
					$item['name'] .= " ($entityTypeCaption)";
				}
				elseif (isset($item['NAME']))
				{
					$item['NAME'] .= " ($entityTypeCaption)";
				}

				if (isset($item['id']))
				{
					$item['id'] = $entityTypeName . "_" . $item['id'];
				}
				elseif (isset($item['ID']))
				{
					$item['ID'] = $entityTypeName . "_" . $item['ID'];
				}

				$list[] = $item;
			}
		}

		return $list;
	}

	/**
	 * Get filter presets.
	 *
	 * @return array
	 */
	public static function getUiFilterPresets()
	{
		$list = array(
			'crm_client_all' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_ALL'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_ALL'),
				'fields' => array(
					self::FIELD_FOR_PRESET_ALL => self::YES,
				)
			),
			'crm_client_deal_in_work' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_DEAL_INW'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_DEAL_INW'),
				'fields' => array(
					'DEAL_STAGE_SEMANTIC_ID' => array(PhaseSemantics::PROCESS),
				)
			),
			'crm_client_deal_won' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_DEAL_WON'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_DEAL_WON'),
				'fields' => array(
					'DEAL_STAGE_SEMANTIC_ID' => array(PhaseSemantics::SUCCESS),
				)
			),
			'crm_client_deal_loose' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_DEAL_LOOSE'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_DEAL_LOOSE'),
				'fields' => array(
					'DEAL_STAGE_SEMANTIC_ID' => array(PhaseSemantics::FAILURE),
				)
			),
			'crm_client_birthday' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_BIRTH'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_BIRTH'),
				'sender_segment_business_case' => true,
				'fields' => array(
					'CONTACT_BIRTHDATE_datesel' => 'NEXT_DAY',
					'CONTACT_BIRTHDATE_days' => '5',
					'CONTACT_BIRTHDATE_allow_year' => '0',
					'CLIENT_TYPE' => \CCrmOwnerType::ContactName
				)
			),
			'crm_client_aft_deal_clo' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_AFTER_CLOSE_DEAL'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_AFTER_CLOSE_DEAL'),
				'sender_segment_business_case' => true,
				'fields' => array(
					'DEAL_CLOSEDATE_datesel' => 'PREV_DAY',
					'DEAL_CLOSEDATE_days' => "30",
					'DEAL_CLOSEDATE_allow_year' => '1',
				)
			),
		);

		foreach (Holiday::getList() as $holiday)
		{
			$code = $holiday->getCode();
			$name = $holiday->getName(
				Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_HOLIDAY'),
				'%holiday_name%'
			);

			$list["crm_client_$code"] = [
				'name' => $name,
				'sender_segment_name' => $name,
				'sender_segment_business_case' => true,
				'fields' => [
					'DEAL_DATE_CREATE_datesel' => 'RANGE',
					'DEAL_DATE_CREATE_from' => $holiday->getDateFrom()->toString(),
					'DEAL_DATE_CREATE_to' => $holiday->getDateTo()->toString(),
					'DEAL_DATE_CREATE_allow_year' => '0',
				]
			];
		}

		return $list;
	}

	/**
	 * Return true if support view of result.
	 *
	 * @return bool
	 */
	public function isResultViewable()
	{
		return true;
	}

	protected function onInitResultView()
	{
		$this->getResultView()
			->setCallback(
				ResultView::ColumnModifier,
				function ()
				{
					Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
				}
			)
			->setCallback(
				ResultView::Draw,
				function (array &$row)
				{
					(new Helper())->onResultViewDraw($row);
				}
			);
	}

	protected function getProductSkuIds($productIds)
	{
		if (!Loader::includeModule("catalog"))
			return [];

		return
			array_reduce(
				\CCatalogSKU::getOffersList($productIds),
				function($ids, $items)
				{
					$ids = array_merge(
						$ids,
						array_map(
							function($item)
							{
								return $item['ID'];
							},
						$items)
					);
					return $ids;
				}, []);
	}

	public function getUiFilterId()
	{
		$code = str_replace('_', '', $this->getCode());
		return $this->getId()   . '_--filter--'.$code.'--';
	}

	/**
	 * Get fields for statistic
	 * @return array
	 */
	public function getStatFields()
	{
		return ['CLIENT_PRODUCT_ID', 'CLIENT_NO_PURCHASES_DATE'];
	}
}
