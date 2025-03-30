<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Controller\Product\SkuDeferredCalculations;
use Bitrix\Catalog\Model\Event;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2;
use Bitrix\Iblock;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Result;
use Bitrix\Main\Engine\ActionFilter\Scope;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\Event\EventBindInterface;
use Bitrix\Rest\RestException;

class Product extends Controller implements EventBindInterface
{
	use SkuDeferredCalculations;

	protected const TYPE = ProductTable::TYPE_PRODUCT;

	/**
	 * @inheritDoc
	 */
	public function configureActions()
	{
		return [
			'addProperty' => [
				'+prefilters' => [new Scope(Scope::AJAX)]
			],
			'getSkuTreeProperties' => [
				'+prefilters' => [new Scope(Scope::AJAX)]
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function processBeforeAction(Engine\Action $action)
	{
		$r = new Result();

		if ($action->getName() === 'add')
		{
			$r = $this->processBeforeAdd($action);
		}
		else if ($action->getName() === 'update')
		{
			$r = $this->processBeforeUpdate($action);
		}
		else if ($action->getName() === 'getfieldsbyfilter')
		{
			$arguments = $action->getArguments();
			$arguments['filter']['productType'] = static::TYPE;
			$action->setArguments($arguments);
		}

		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		if ($this->isActionWithDefferedCalculation($action))
		{
			$this->processBeforeDeferredCalculationAction();
		}

		return parent::processBeforeAction($action);
	}

	/**
	 * @inheritDoc
	 *
	 * @param Engine\Action $action
	 * @param mixed $result
	 *
	 * @return void
	 */
	protected function processAfterAction(Engine\Action $action, $result)
	{
		if ($this->isActionWithDefferedCalculation($action))
		{
			$this->processAfterDeferredCalculationAction();
		}

		return parent::processAfterAction($action, $result);
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	protected function processBeforeUpdate(Engine\Action $action): Result
	{
		$arguments = $action->getArguments();

		$fields = $arguments['fields'];
		$productId = $arguments['id'];

		$result = $this->exists($productId);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$element = $result->getData();

		$iblockIdOrigin = $fields['iblockId'] ?? null;
		if ($iblockIdOrigin !== null)
		{
			$iblockIdOrigin = (int)$iblockIdOrigin;
		}

		if ($iblockIdOrigin && $iblockIdOrigin !== $element['IBLOCK_ID'])
		{
			$result->addError(
				new Error(
					sprintf(
						'Product - %d is not exists in catalog - %d', $productId , $iblockIdOrigin
					)
				)
			);
		}

		if (!isset($fields['iblockId']))
		{
			$arguments['fields']['iblockId'] = $element['IBLOCK_ID'];
			$action->setArguments($arguments);
		}

		return $result;
	}

	protected function processBeforeAdd(Engine\Action $action): Result
	{
		return new Result();
	}

	//region Actions
	public function getFieldsByFilterAction($filter): ?array
	{
		/** @var \Bitrix\Catalog\RestView\Product $view */
		$view = $this->getViewManager()
			->getView($this);
		$r = $view->getFieldsByFilter($filter);

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			return [$this->getServiceItemName() =>$view->prepareFieldInfos(
				$r->getData()
			)];
		}
	}

	private static function perfGetList(array $select, array $filter, array $order, $pageNavigation = null): array
	{
		$count = null;
		$rawRows = [];
		$elementIds = [];

		$iterator = \CIBlockElement::GetList(
			$order,
			$filter,
			false,
			$pageNavigation ?? false,
			array('ID', 'IBLOCK_ID')
		);
		while($row = $iterator->Fetch())
		{
			$rawRows[$row['ID']] = $row;
			$elementIds[] = $row['ID'];
		}
		if ($pageNavigation)
		{
			$count = (int)$iterator->NavRecordCount;
		}
		unset($row, $iterator);

		$loadSections =
			in_array('IBLOCK_SECTION', $select, true)
			|| in_array('*', $select, true)
		;

		foreach (array_chunk($elementIds, \IRestService::LIST_LIMIT) as $pageIds)
		{
			$elementFilter = [
				'IBLOCK_ID' => $filter['IBLOCK_ID'],
				'ID' => $pageIds,
			];
			$iterator = \CIBlockElement::GetList([], $elementFilter, false, false, $select);
			while ($row = $iterator->Fetch())
			{
				$rawRows[$row['ID']] += $row;
			}
			unset($row, $iterator);

			if ($loadSections)
			{
				self::attachIblockSections($rawRows);
			}
		}

		return [
			'ROWS' => $rawRows,
			'COUNT' => $count,
		];
	}

	/**
	 * @param PageNavigation $pageNavigation
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param bool $__calculateTotalCount
	 * @return Page|null
	 */
	public function listAction(
		PageNavigation $pageNavigation,
		array $select = [],
		array $filter = [],
		array $order = [],
		bool $__calculateTotalCount = true
	): ?Page
	{
		$r = $this->checkPermissionIBlockElementList($filter['IBLOCK_ID']);
		if ($r->isSuccess())
		{
			$select = empty($select)? array_merge(['*'], $this->getAllowedFieldsProduct()):$select;
			$order = empty($order)? ['ID'=>'ASC']:$order;

			$groupFields = $this->splitFieldsByEntity(
				array_flip($select)
			);
			$allProperties = isset($groupFields['elementFields']['PROPERTY_*']);
			if ($allProperties)
			{
				unset($groupFields['elementFields']['PROPERTY_*']);
			}

			$productFields = array_keys($groupFields['productFields']);
			$elementFields = array_keys($groupFields['elementFields']);
			$propertyFields = $groupFields['propertyFields'];

			$propertyFields = $this->preparePropertyFields($propertyFields);
			$propertyIds = array_keys($propertyFields);

			$items = self::perfGetList(
				array_merge($productFields, $elementFields),
				$filter,
				$order,
				self::getNavData($pageNavigation->getOffset())
			);
			$list = $items['ROWS'];
			$count = $items['COUNT'];
			unset($items);

			if (empty($list))
			{
				return new Page(
					$this->getServiceListName(),
					[],
					0
				);
			}

			if ($allProperties || !empty($propertyIds))
			{
				self::attachPropertyValues($list, (int)$filter['IBLOCK_ID'], $propertyIds);
			}

			$totalCount = 0;
			if ($__calculateTotalCount)
			{
				$totalCount = $count ?? $this->getCount($filter);
			}

			return new Page(
				$this->getServiceListName(),
				array_values($list),
				$totalCount
			);
		}
		else
		{
			$this->addErrors($r->getErrors());

			return null;
		}
	}

	public function getAction($id)
	{
		$id = (int)$id;
		$result = $this->exists($id);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$element = $result->getData();
		$result = $this->checkPermissionIBlockElementGet($element['IBLOCK_ID'], $element['ID']);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return [
			$this->getServiceItemName() => $this->get($id),
		];
	}

	public function addAction(array $fields): ?array
	{
		$result = $this->checkPermissionAdd($fields['IBLOCK_ID']);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		if (isset($fields['IBLOCK_SECTION_ID']) && (int)$fields['IBLOCK_SECTION_ID'] > 0)
		{
			$result = $this->checkPermissionIBlockElementSectionBindUpdate(
				$fields['IBLOCK_ID'],
				$fields['IBLOCK_SECTION_ID']
			);

			if (!$result->isSuccess())
			{
				$this->addErrors($result->getErrors());

				return null;
			}
		}

		$result = $this->addValidate($fields);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$fields = $this->prepareFieldsForAdd($fields);
		if ($fields === null)
		{
			return null;
		}

		$groupFields = $this->splitFieldsByEntity($fields);

		$productFields = $groupFields['productFields'];
		$propertyFields = $groupFields['propertyFields'];
		$elementFields = $groupFields['elementFields'];

		$productFields = $this->prepareProductFields($productFields);
		$propertyFields = $this->verifyPropertyFields($fields['IBLOCK_ID'], $propertyFields);
		$propertyFields = $this->preparePropertyFields($propertyFields);
		$elementFieldsAdd =
			!empty($propertyFields)
				? array_merge($elementFields, ['PROPERTY_VALUES' => $propertyFields])
				: $elementFields
		;

		$productService = new v2\Internal\ProductInternalService(true);

		$conn = Application::getConnection();
		$conn->startTransaction();
		try
		{
			$result = $productService->add(array_merge($productFields, $elementFieldsAdd));
		}
		catch (SqlQueryException)
		{
			$result = new Result();
			$result->addError(new Error('Internal error adding product. Try adding again.'));
		}

		if (!$result->isSuccess())
		{
			$conn->rollbackTransaction();
			$this->addErrors($result->getErrors());

			return null;
		}
		$conn->commitTransaction();

		$id = $result->getData()['ID'];

		return [
			'ELEMENT' => $this->get($id),
		];
	}

	public function updateAction(int $id, array $fields): ?array
	{
		$result = $this->exists($id);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$element = $result->getData();
		$fields['IBLOCK_ID'] ??= $element['IBLOCK_ID'];

		$result = $this->checkPermissionUpdate($element['IBLOCK_ID'], $element['ID']);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$sectionId = (int)($fields['IBLOCK_SECTION_ID'] ?? null);
		if ($sectionId > 0)
		{
			$result = $this->checkPermissionIBlockElementSectionBindUpdate(
				$element['IBLOCK_ID'],
				$sectionId
			);
			if (!$result->isSuccess())
			{
				$this->addErrors($result->getErrors());

				return null;
			}
		}

		$groupFields = $this->splitFieldsByEntity($fields);

		$productFields = $groupFields['productFields'];
		$propertyFields = $groupFields['propertyFields'];
		$elementFields = $groupFields['elementFields'];

		$productFields = $this->prepareProductFields($productFields);
		$propertyFields = $this->verifyPropertyFields((int)$fields['IBLOCK_ID'], $propertyFields);
		$propertyFields = $this->preparePropertyFields($propertyFields);

		$propertyFields = $this->fillPropertyFieldsDefaultPropertyValues($id, $fields['IBLOCK_ID'], $propertyFields);
		$propertyFields = $this->preparePropertyFieldsUpdate($propertyFields);

		$elementFieldsUpdate =
			!empty($propertyFields)
				? array_merge(
					$elementFields,
					['PROPERTY_VALUES' => $propertyFields]
				)
				: $elementFields
		;

		if (!empty($elementFieldsUpdate))
		{
			$result = $this->updateValidate(
				$elementFieldsUpdate + ['ID' => $id]
			);
			if (!$result->isSuccess())
			{
				$this->addErrors($result->getErrors());

				return null;
			}
		}

		if (
			!empty($productFields)
			|| !empty($elementFieldsUpdate)
		)
		{
			$productService = new v2\Internal\ProductInternalService(true);

			$conn = Application::getConnection();
			$conn->startTransaction();
			try
			{
				$result = $productService->update(
					$id,
					array_merge($productFields, $elementFieldsUpdate)
				);
			}
			catch (SqlQueryException)
			{
				$result = new Result();
				$result->addError(new Error('Internal error updating product. Try updating again.'));
			}

			if (!$result->isSuccess())
			{
				$conn->rollbackTransaction();
				$this->addErrors($result->getErrors());

				return null;
			}
			$conn->commitTransaction();
		}

		return [
			'ELEMENT' => $this->get($id),
		];
	}

	public function deleteAction(int $id): ?bool
	{
		$result = $this->exists($id);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}
		$element = $result->getData();
		$result = $this->checkPermissionDelete($element['IBLOCK_ID'], $element['ID']);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$conn = Application::getConnection();
		$conn->startTransaction();
		try
		{
			if (!\CIBlockElement::Delete($id))
			{
				$ex = self::getApplication()->GetException();
				if ($ex)
				{
					$result->addError(new Error($ex->GetString(), $ex->GetId()));
				}
				else
				{
					$result->addError(new Error('delete iBlockElement error'));
				}
			}
		}
		catch (SqlQueryException)
		{
			$result = new Result();
			$result->addError(new Error('Internal error deleting product. Try deleting again.'));
		}

		if ($result->isSuccess())
		{
			$conn->commitTransaction();

			return true;
		}
		else
		{
			$conn->rollbackTransaction();
			$this->addErrors($result->getErrors());

			return null;
		}
	}

	public function downloadAction(array $fields): ?Engine\Response\BFile
	{
		$productId = (int)($fields['PRODUCT_ID'] ?? null);
		$fieldName = $fields['FIELD_NAME'];
		$id = $fields['FILE_ID'];
		$file = [];

		$result = $this->exists($productId);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$element = $result->getData();

		if (
			$this->checkFieldsDownload([
				'NAME' => $fieldName,
				'IBLOCK_ID' => $element['IBLOCK_ID'],
			])
		)
		{
			$files = [];
			$iterator = \CIBlockElement::GetList(
				[],
				[
					'IBLOCK_ID' => $element['IBLOCK_ID'],
					'=ID' => $element['ID'],
					'CHECK_PERMISSIONS' => 'N',
				],
				false,
				false,
				[
					'ID',
					'IBLOCK_ID',
					$fieldName,
				]
			);
			while ($res = $iterator->GetNext())
			{
				if (isset($res[$fieldName]))
				{
					$files[] = (int)$res[$fieldName];
				}
				elseif (isset($res[$fieldName."_VALUE"]))
				{
					if (is_array($res[$fieldName."_VALUE"]))
					{
						$list = $res[$fieldName . '_VALUE'];
						\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($list);
						if (!empty($list))
						{
							$files = array_merge($files, $list);
						}
						unset($list);
					}
					else
					{
						$files[] = (int)$res[$fieldName."_VALUE"];
					}
				}
			}
			unset($res, $iterator);

			if (!in_array($id, $files))
			{
				$result->addError(new Error('Product file wrong'));
			}
			else
			{
				$file = \CFile::GetFileArray($id);
				if (!is_array($file))
				{
					$result->addError(new Error('Product is empty'));
				}
			}
		}
		else
		{
			$result->addError(new Error('Name file field is not available'));
		}

		if ($result->isSuccess())
		{
			return \Bitrix\Main\Engine\Response\BFile::createByFileId($file['ID']);
		}
		else
		{
			$this->addErrors($result->getErrors());

			return null;
		}
	}
	//endregion Actions

	private function getCount(array $filter): \Closure
	{
		return function() use ($filter)
		{
			return (int)\CIBlockElement::GetList([], $filter, []);
		};
	}

	protected function getEntityTable()
	{
		return \Bitrix\Catalog\Model\Product::getTabletClassName();
	}

	protected function splitFieldsByEntity($fields): array
	{
		$productFields = [];
		$elementFields = [];
		$propertyFields = [];

		foreach($fields as $name=>$value)
		{
			if(in_array($name, $this->getAllowedFieldsProduct()))
			{
				$productFields[$name] = $value;
			}
			else
			{
				if (preg_match('/^(PROPERTY_\d+)$/', $name))
				{
					$propertyFields[$name] = $value;
				}
				else
				{
					$elementFields[$name] = $value;
				}
			}
		}

		return [
			'productFields'=>$productFields,
			'propertyFields'=>$propertyFields,
			'elementFields'=>$elementFields
		];
	}

	protected function prepareProductFields(array $fields): array
	{
		$result = $fields;

		if (State::isUsedInventoryManagement())
		{
			unset($result['QUANTITY_TRACE']);
		}

		return $result;
	}

	private function verifyPropertyFields(int $iblockId, array $fields): array
	{
		$singleProperties = [];
		$iterator = Iblock\PropertyTable::getList([
			'select' => [
				'ID',
				'PROPERTY_TYPE',
				'USER_TYPE',
			],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'@PROPERTY_TYPE' => [
					Iblock\PropertyTable::TYPE_STRING,
					iblock\PropertyTable::TYPE_NUMBER,
				],
				'=ACTIVE' => 'Y',
				'=MULTIPLE' => 'N',
			],
			'order' => [
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 86400,
			]
		]);
		while ($row = $iterator->Fetch())
		{
			if ((string)$row['USER_TYPE'] !== '')
			{
				continue;
			}

			$singleProperties['PROPERTY_' . $row['ID']] = true;
		}
		unset(
			$row,
			$iterator,
		);

		$result = [];

		foreach ($fields as $name => $value)
		{
			if (
				isset($singleProperties[$name])
				&& isset($value['VALUE'])
				&& is_array($value['VALUE'])
			)
			{
				continue;
			}

			$result[$name] = $value;
		}

		return $result;
	}

	protected function preparePropertyFields($fields)
	{
		$result = [];
		$matches = [];

		foreach($fields as $name=>$value)
		{
			if (preg_match('/^(PROPERTY_)(\d+)$/', $name, $matches))
			{
				$result[$matches[2]] = $value;
			}
		}
		return $result;
	}

	protected function preparePropertyFieldsUpdate($fields): array
	{
		$result = [];

		if(count($fields)>0)
		{
			foreach ($fields as $propertyId=>$value)
			{
				$property = [];
				// single
				if(isset($value['VALUE']))
				{
					if(isset($value['VALUE_ID']))
					{
						//update
						$valueId=$value['VALUE_ID'];
						unset($value['VALUE_ID']);
						$property[$valueId]=$value;

					}
					else
					{
						//replace
						$property[]=$value;
					}
				}
				// multi
				else
				{
					if(is_array($value) && count($value)>0)
					{
						foreach ($value as $item)
						{
							if(isset($item['VALUE_ID']))
							{
								//update
								$valueId = $item['VALUE_ID'];
								unset($item['VALUE_ID']);
								$property[$valueId]=$item;
							}
							else
							{
								//replace
								$property[]=$item;
							}
						}
					}
				}

				if(count($property)>0)
				{
					$result[$propertyId]=$property;
				}
			}
		}
		return $result;
	}

	protected function fillPropertyFieldsDefaultPropertyValues($id, $iblockId, $propertyValues)
	{
		$fields = $propertyValues;

		if ($id > 0 && $iblockId > 0 && !empty($propertyValues))
		{
			$r = \CIBlockElement::GetProperty(
				$iblockId,
				$id,
				'SORT',
				'ASC',
				[
					'CHECK_PERMISSIONS' => 'N',
					'EMPTY' => 'N',
				]
			);
			while ($property = $r->Fetch())
			{
				if (
					$property['PROPERTY_TYPE'] !== Iblock\PropertyTable::TYPE_FILE
					&& !array_key_exists($property['ID'], $propertyValues)
				)
				{
					$fields[$property['ID']] ??= [];

					$fields[$property['ID']][] = [
						'VALUE_ID' => $property['PROPERTY_VALUE_ID'],
						'VALUE' => $property['VALUE'],
						'DESCRIPTION' => $property['DESCRIPTION'],
					];
				}
			}
			unset($property, $r);
		}

		return $fields;
	}

	protected function exists($id)
	{
		$result = new Result();

		$row = null;
		$id = (int)$id;
		if ($id > 0)
		{
			$row = Iblock\ElementTable::getRow([
				'select' => [
					'ID',
					'IBLOCK_ID',
				],
				'filter' => [
					'=ID' => $id,
				],
			]);
		}
		if ($row === null)
		{
			$result->addError($this->getErrorEntityNotExists());

			return $result;
		}

		$row['ID'] = (int)$row['ID'];
		$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
		$result->setData($row);

		return $result;
	}

	protected function get($id)
	{
		$row = \CIBlockElement::getList(
			[],
			['ID' => $id],
			false,
			false,
			[
				'*',
				...$this->getAllowedFieldsProduct()
			]
		)->fetch();

		if (!$row)
		{
			return [];
		}

		$result = [
			$row['ID'] => $row,
		];

		self::attachIblockSections($result);
		self::attachPropertyValues($result, (int)$row['IBLOCK_ID']);

		return $result[$row['ID']];
	}

	protected function addValidate($fields)
	{
		return $this->checkFields($fields);
	}

	protected function updateValidate($fields)
	{
		return $this->checkFields($fields);
	}

	protected function checkFields($fields)
	{
		$r = new Result();

		if (isset($fields['SECTION_ID']))
		{
			$section = \CIBlockSection::GetByID($fields['SECTION_ID'])->Fetch();
			if (!isset($section['ID']))
			{
				$r->addError(new Error('Section is not exists'));
			}
		}
		if (isset($fields['MODIFIED_BY']))
		{
			$user = \CUser::GetByID($fields['MODIFIED_BY'])->Fetch();
			if (!isset($user['ID']))
			{
				$r->addError(new Error('User modifiedBy is not exists'));
			}
		}
		if (isset($fields['CREATED_BY']))
		{
			$user = \CUser::GetByID($fields['CREATED_BY'])->Fetch();
			if (!isset($user['ID']))
			{
				$r->addError(new Error('User createdBy is not exists'));
			}
		}
		if (isset($fields['PURCHASING_CURRENCY']))
		{
			$currency = \CCurrency::GetByID($fields['PURCHASING_CURRENCY']);
			if (!isset($currency['CURRENCY']))
			{
				$r->addError(new Error('Currency purchasingCurrency is not exists'));
			}
		}
		if (isset($fields['VAT_ID']))
		{
			$user = \CCatalogVat::GetByID($fields['VAT_ID'])->Fetch();
			if (!isset($user['ID']))
			{
				$r->addError(new Error('VAT vatId is not exists'));
			}
		}

		return $r;
	}

	protected function prepareFieldsForAdd(array $fields): ?array
	{
		return $fields;
	}

	protected function prepareFieldsForUpdate(array $fields): ?array
	{
		return $fields;
	}

	protected static function attachIblockSections(array &$result): void
	{
		if (empty($result))
		{
			return;
		}

		$listIds = array_keys($result);
		foreach ($listIds as $id)
		{
			$result[$id]['IBLOCK_SECTION'] = [];
		}

		$filter = ORM\Query\Query::filter();
		$filter->whereNull('ADDITIONAL_PROPERTY_ID');
		foreach (array_chunk($listIds, CATALOG_PAGE_SIZE) as $pageIds)
		{
			$filter->whereIn('IBLOCK_ELEMENT_ID', $pageIds);
			$iterator = Iblock\SectionElementTable::getList([
				'select' => [
					'IBLOCK_ELEMENT_ID',
					'IBLOCK_SECTION_ID',
				],
				'filter' => $filter,
			]);
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['IBLOCK_ELEMENT_ID'];
				$result[$id]['IBLOCK_SECTION'][] = (int)$row['IBLOCK_SECTION_ID'];
			}
			unset(
				$row,
				$iterator,
			);
		}
	}

	/**
	 * @param array &$result
	 * @param int $iblockId
	 * @param array $propertyIds
	 * @return void
	 */
	protected static function attachPropertyValues(array &$result, int $iblockId, array $propertyIds = []): void
	{
		if ($iblockId <= 0)
		{
			return;
		}

		$propertyFilter = !empty($propertyIds) ? ['ID' => $propertyIds] : [];

		$propertyValues = [];
		\CIBlockElement::getPropertyValuesArray(
			$propertyValues,
			$iblockId,
			['ID' => array_keys($result)],
			$propertyFilter,
			['USE_PROPERTY_ID' => 'Y']
		);

		foreach ($result as $k => $v)
		{
			if (isset($propertyValues[$k]))
			{
				foreach ($propertyValues[$k] as $propId => $fields)
				{
					$value = null;

					if (isset($fields['PROPERTY_VALUE_ID']))
					{
						if ($fields['PROPERTY_TYPE'] === Iblock\PropertyTable::TYPE_LIST)
						{
							if ($fields['MULTIPLE'] === 'Y')
							{
								if (is_array($fields['PROPERTY_VALUE_ID']))
								{
									foreach ($fields['PROPERTY_VALUE_ID'] as $i => $item)
									{
										$value[] = [
											'VALUE' => $fields['VALUE_ENUM_ID'][$i],
											'VALUE_ENUM' => $fields['VALUE_ENUM'][$i],
											'VALUE_ID' => $fields['PROPERTY_VALUE_ID'][$i],
										];
									}
								}
							}
							else
							{
								$value = [
									'VALUE' => $fields['VALUE_ENUM_ID'],
									'VALUE_ENUM' => $fields['VALUE_ENUM'],
									'VALUE_ID' => $fields['PROPERTY_VALUE_ID']
								];
							}
						}
						else
						{
							if ($fields['MULTIPLE'] === 'Y')
							{
								if (is_array($fields['PROPERTY_VALUE_ID']))
								{
									foreach ($fields['PROPERTY_VALUE_ID'] as $i => $item)
									{
										$value[] = [
											'VALUE' => $fields['VALUE'][$i],
											'VALUE_ID' => $fields['PROPERTY_VALUE_ID'][$i]
										];
									}
								}
							}
							else
							{
								$value = [
									'VALUE' => $fields['VALUE'],
									'VALUE_ID' => $fields['PROPERTY_VALUE_ID']
								];
							}
						}
					}

					$result[$k]['PROPERTY_' . $propId] = $value;
				}
			}
			elseif (!empty($propertyIds))
			{
				/**
				 * if property values are empty $propertyValues is empty
				 */

				foreach ($propertyIds as $propId)
				{
					$result[$k]['PROPERTY_' . $propId] = null;
				}
			}
		}
	}

	protected function checkPermissionEntity($name, $arguments=[])
	{
		$name = mb_strtolower($name); //for ajax mode

		if($name == 'getfieldsbyfilter'
			|| $name == 'download'
		)
		{
			$r = $this->checkReadPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}

		return $r;
	}

	/**
	 * @return string[]
	 */
	protected function getAllowedFieldsProduct(): array
	{
		$result = [
			'TYPE',
			'AVAILABLE',
			'BUNDLE',
			'QUANTITY',
			'QUANTITY_RESERVED',
			'QUANTITY_TRACE',
			'CAN_BUY_ZERO',
			'SUBSCRIBE',
			'VAT_ID',
			'VAT_INCLUDED',
			'BARCODE_MULTI',
			'WEIGHT',
			'LENGTH',
			'WIDTH',
			'HEIGHT',
			'MEASURE',
			'RECUR_SCHEME_LENGTH',
			'RECUR_SCHEME_TYPE',
			'TRIAL_PRICE_ID',
			'WITHOUT_ORDER',
			'QUANTITY_TRACE_RAW',
			'PAYMENT_TYPE',
			'SUBSCRIBE_RAW',
			'CAN_BUY_ZERO_RAW'
		];

		if ($this->accessController->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW))
		{
			array_push($result, 'PURCHASING_PRICE', 'PURCHASING_CURRENCY');
		}

		return $result;
	}

	protected function checkFieldsDownload($fields)
	{
		$name = $fields['NAME'];
		$iblockId = $fields['IBLOCK_ID'];

		if ($name === "DETAIL_PICTURE")
		{
			return true;
		}
		elseif ($name === "PREVIEW_PICTURE")
		{
			return true;
		}
		elseif ($name === "PICTURE")
		{
			return true;
		}
		elseif (!preg_match("/^PROPERTY_(.+)\$/", $name, $match))
		{
			return false;
		}
		else
		{
			$property = \CIBlockProperty::GetPropertyArray($match[1], $iblockId);
			if (is_array($property) && $property["PROPERTY_TYPE"] === Iblock\PropertyTable::TYPE_FILE)
			{
				return true;
			}
		}
		return false;
	}

	public function addPropertyAction($fields)
	{
		$result = $this->checkPermissionIBlockModify($fields['IBLOCK_ID']);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$iblockProperty = new \CIBlockProperty();

		$propertyFields = [
			'ACTIVE' => 'Y',
			'IBLOCK_ID' => $fields['IBLOCK_ID'],
			'NAME' => $fields['NAME'],
			'SORT' => $fields['SORT'] ?? 100,
			'CODE' => $fields['CODE'] ?? '',
			'MULTIPLE' => ($fields['MULTIPLE'] === 'Y') ? 'Y' : 'N',
			'IS_REQUIRED'=> ($fields['IS_REQUIRED'] === 'Y') ? 'Y' : 'N',
			'SECTION_PROPERTY'=> 'N',
		];

		$newId = (int)($iblockProperty->Add($propertyFields));
		if ($newId === 0)
		{
			$this->addError(new Error($iblockProperty->getLastError()));

			return null;
		}

		return [
			'ID' => $newId,
			'CONTROL_ID' => 'PROPERTY_' . $newId
		];
	}

	//region checkPermissionController
	protected function checkModifyPermissionEntity()
	{
		return $this->checkReadPermissionEntity();
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (
			!$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			&& !$this->accessController->check(ActionDictionary::ACTION_PRICE_EDIT)
			&& !$this->accessController->check(ActionDictionary::ACTION_CATALOG_VIEW)
		)
		{
			$r->addError($this->getErrorReadAccessDenied());
		}
		return $r;
	}
	//endregion checkPermissionController

	//region checkPermissionIBlock
	protected function existsIblock(int $iblockId): Result
	{
		$result = new Result();

		$iblockName = \CIBlock::GetArrayByID($iblockId, 'NAME');
		if (empty($iblockName))
		{
			$result->addError(new Error('Iblock Not Found', 200040300000));
		}

		return $result;
	}

	protected function checkPermissionAdd(int $iblockId): Result
	{
		$result = new Result();

		$result->addErrors(
			$this->checkPermissionIBlockElementAdd($iblockId)->getErrors()
		);
		$result->addErrors(
			$this->checkPermissionCatalogProductAdd()->getErrors()
		);

		return $result;
	}

	protected function checkPermissionCatalogProductAdd(): Result
	{
		$result = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_PRODUCT_ADD))
		{
			$result->addError(new Error('Access Denied', 200040300040));
		}

		return $result;
	}

	protected function checkPermissionIBlockElementAdd(int $iblockId): Result
	{
		//return $this->checkPermissionIBlockElementList($iblockId);
		return $this->checkPermissionIBlockElementModify($iblockId, 0);
	}

	protected function checkPermissionUpdate(int $iblockId, int $elementId): Result
	{
		$result = new Result();

		$result->addErrors(
			$this->checkPermissionIBlockElementUpdate($iblockId, $elementId)->getErrors()
		);
		$result->addErrors(
			$this->checkPermissionCatalogProductUpdate()->getErrors()
		);

		return $result;
	}

	protected function checkPermissionCatalogProductUpdate(): Result
	{
		$result = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_PRODUCT_EDIT))
		{
			$result->addError(new Error('Access Denied', 200040300040));
		}

		return $result;
	}

	protected function checkPermissionIBlockElementUpdate(int $iblockId, int $elementId): Result
	{
		return $this->checkPermissionIBlockElementModify($iblockId, $elementId);
	}

	protected function checkPermissionIBlockModify($iblockId): Result
	{
		$iblockId = (int)$iblockId;

		$r = $this->existsIblock($iblockId);
		if (!$r->isSuccess())
		{
			return $r;
		}

		if (!\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::IBLOCK_EDIT))
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}

		return $r;
	}

	protected function checkPermissionIBlockElementModify($iblockId, $elementId): Result
	{
		$iblockId = (int)$iblockId;

		$r = $this->existsIblock($iblockId);
		if (!$r->isSuccess())
		{
			return $r;
		}

		if ($elementId > 0)
		{
			$badBlock = !\CIBlockElementRights::UserHasRightTo($iblockId, $elementId, self::IBLOCK_ELEMENT_EDIT); //access edit
		}
		else
		{
			$badBlock = !\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::IBLOCK_ELEMENT_EDIT);
		}

		if ($badBlock)
		{
			$r->addError(new Error('Access Denied', 200040300043));
		}

		return $r;
	}

	protected function checkPermissionDelete(int $iblockId, int $elementId): Result
	{
		$result = new Result();

		$result->addErrors(
			$this->checkPermissionIBlockElementDelete($iblockId, $elementId)->getErrors()
		);
		$result->addErrors(
			$this->checkPermissionCatalogProductDelete()->getErrors()
		);

		return $result;
	}

	protected function checkPermissionCatalogProductDelete(): Result
	{
		$result = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_PRODUCT_DELETE))
		{
			$result->addError(new Error('Access Denied', 200040300040));
		}

		return $result;
	}

	protected function checkPermissionIBlockElementDelete(int $iblockId, int $elementId): Result
	{
		$r = $this->existsIblock($iblockId);
		if (!$r->isSuccess())
		{
			return $r;
		}

		if (!\CIBlockElementRights::UserHasRightTo($iblockId, $elementId, self::IBLOCK_ELEMENT_DELETE)) //access delete
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}

		return $r;
	}

	protected function checkPermissionIBlockElementGet(int $iblockId, int $elementId): Result
	{
		$r = $this->existsIblock($iblockId);
		if (!$r->isSuccess())
		{
			return $r;
		}

		if (!\CIBlockElementRights::UserHasRightTo($iblockId, $elementId, self::IBLOCK_ELEMENT_READ)) //access read
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}

		return $r;
	}

	protected function checkPermissionIBlockElementList($iblockId): Result
	{
		$iblockId = (int)$iblockId;
		$r = $this->existsIblock($iblockId);
		if (!$r->isSuccess())
		{
			return $r;
		}

		if (!\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::IBLOCK_READ))
		{
			$r->addError(new Error('Access Denied', 200040300030));
		}

		return $r;
	}

	protected function checkPermissionIBlockElementSectionBindModify($iblockId, $iblockSectionId): Result
	{
		$iblockId = (int)$iblockId;
		$r = $this->existsIblock($iblockId);
		if (!$r->isSuccess())
		{
			return $r;
		}

		if (!\CIBlockSectionRights::UserHasRightTo(
			$iblockId,
			$iblockSectionId,
			self::IBLOCK_ELEMENT_SECTION_BIND
		)) //access update
		{
			$r->addError(new Error('Access Denied', 200040300050));
		}

		return $r;
	}

	protected function checkPermissionIBlockElementSectionBindUpdate(int $iblockId, $iblockSectionId): Result
	{
		return $this->checkPermissionIBlockElementSectionBindModify($iblockId, $iblockSectionId);
	}

	//endregion

	// rest-event region
	/**
	 * @inheritDoc
	 */
	public static function getCallbackRestEvent(): array
	{
		return [self::class, 'processItemEvent'];
	}

	public static function processItemEvent(array $params, array $handler): array
	{
		$id = null;
		$event = $params[0] ?? null;

		if (!$event)
		{
			throw new RestException('event object not found trying to process event');
		}

		if ($event instanceof \Bitrix\Main\Event) // update, add
		{
			$id = $event->getParameter('id');
		}
		else if($event instanceof \Bitrix\Main\ORM\Event) // delete
		{
			$item = $event->getParameter('id');
			$id = is_array($item) ? $item['ID']: $item;
		}

		if (!$id)
		{
			throw new RestException('id not found trying to process event');
		}

		$product = \Bitrix\Catalog\Model\Product::getCacheItem($id, true);

		$type = $product['TYPE']  ?? null;

		if (!$type)
		{
			throw new RestException('type is not specified trying to process event');
		}

		return [
			'FIELDS' => [
				'ID' => $id,
				'TYPE' => $type
			],
		];
	}

	protected static function getBindings(): array
	{
		$entity = (new static())->getEntity();
		$class = $entity->getNamespace() . $entity->getName();
		$model = \Bitrix\Catalog\Model\Product::class;

		$updateEventName = v2\Event\Event::makeEventName(
			v2\Event\Event::ENTITY_PRODUCT,
			v2\Event\Event::METHOD_UPDATE,
			v2\Event\Event::STAGE_AFTER
		);

		return [
			Event::makeEventName($model,DataManager::EVENT_ON_AFTER_ADD) => $entity->getModule().'.'.$entity->getName().'.on.add',
			$updateEventName => $entity->getModule().'.'.$entity->getName().'.on.update',
			Event::makeEventName($class,DataManager::EVENT_ON_DELETE) => $entity->getModule().'.'.$entity->getName().'.on.delete',
		];
	}
	// endregion
}
