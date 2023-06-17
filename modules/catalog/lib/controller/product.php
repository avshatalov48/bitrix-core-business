<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Component\UseStore;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Model\Event;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Result;
use Bitrix\Main\Engine\ActionFilter\Scope;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Rest\Event\EventBindInterface;
use Bitrix\Rest\RestException;

class Product extends Controller implements EventBindInterface
{
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

		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		return parent::processBeforeAction($action);
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	protected function processBeforeUpdate(Engine\Action $action): Result
	{
		$r = new Result();

		$arguments = $action->getArguments();

		$fields = $arguments['fields'];
		$productId = $arguments['id'];

		$iblockId = $this->getProductIblockId($productId);
		$iblockIdOrigin = $fields['iblockId'] ?? null;
		if ($iblockIdOrigin !== null)
		{
			$iblockIdOrigin = (int)$iblockIdOrigin;
		}

		if ($iblockIdOrigin && $iblockIdOrigin !== $iblockId)
		{
			$r->addError(
				new Error(
					sprintf(
						'Product - %d is not exists in catalog - %d', $productId , $iblockIdOrigin
					)
				)
			);
		}

		return $r;
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

	static protected function perfGetList(array $select, array $filter, array $order, $pageNavigation = null): array
	{
		$rawRows = [];
		$elementIds = [];

		$rsData = \CIBlockElement::GetList(
			$order,
			$filter,
			false,
			$pageNavigation ?? false,
			array('ID', 'IBLOCK_ID')
		);
		while($row = $rsData->Fetch())
		{
			$rawRows[$row['ID']] = $row;
			$elementIds[] = $row['ID'];
		}

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
		}

		return $rawRows;
	}

	/**
	 * @param $select
	 * @param $filter
	 * @param $order
	 * @param PageNavigation $pageNavigation
	 * @return Page|null
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): ?Page
	{
		$r = $this->checkPermissionIBlockElementList($filter['IBLOCK_ID']);
		if($r->isSuccess())
		{
			$result = [];

			$select = empty($select)? array_merge(['*'], $this->getAllowedFieldsProduct()):$select;
			$order = empty($order)? ['ID'=>'ASC']:$order;

			$groupFields = $this->splitFieldsByEntity(
				array_flip($select)
			);

			$productFields = array_keys($groupFields['productFields']);
			$elementFields = array_keys($groupFields['elementFields']);
			$propertyFields = $groupFields['propertyFields'];

			$propertyFields = $this->preparePropertyFields($propertyFields);
			$propertyIds = array_keys($propertyFields);
			$list = self::perfGetList(array_merge($productFields, $elementFields), $filter, $order, self::getNavData($pageNavigation->getOffset()));

			if (!empty($list))
			{
				$this->attachPropertyValues($list, (int)$filter['IBLOCK_ID'], $propertyIds);

				foreach ($list as $row)
				{
					$result[] = $row;
				}
			}

			return new Page($this->getServiceListName(), $result, function() use ($filter)
			{
				return (int)\CIBlockElement::GetList([], $filter, []);
			});
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function getAction($id)
	{
		$r = $this->checkPermissionIBlockElementGet($id);
		if($r->isSuccess())
		{
			$r = $this->exists($id);
			if($r->isSuccess())
			{
				return [$this->getServiceItemName() => $this->get($id)];
			}
		}

		if($r->isSuccess() === false)
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function addAction(array $fields): ?array
	{
		$r = $this->checkPermissionAdd($fields['IBLOCK_ID']);
		if($r->isSuccess())
		{
			if (isset($fields['IBLOCK_SECTION_ID']) && (int)$fields['IBLOCK_SECTION_ID'] > 0)
			{
				$r = $this->checkPermissionIBlockElementSectionBindUpdate($fields['IBLOCK_SECTION_ID']);
			}
		}

		if($r->isSuccess())
		{
			$id = 0;
			$element = new \CIBlockElement();

			$r = $this->addValidate($fields);
			if($r->isSuccess())
			{
				$groupFields = $this->splitFieldsByEntity($fields);

				$productFields = $groupFields['productFields'];
				$propertyFields = $groupFields['propertyFields'];
				$elementFields = $groupFields['elementFields'];

				$productFields = $this->prepareProductFields($productFields);
				$propertyFields = $this->preparePropertyFields($propertyFields);
				$elementFieldsAdd = count($propertyFields)>0 ? array_merge($elementFields, ['PROPERTY_VALUES'=>$propertyFields]):$elementFields;

				$id = $element->Add($elementFieldsAdd);
				if($element->LAST_ERROR<>'')
				{
					$r->addError(new Error($element->LAST_ERROR));
				}
				else
				{
					$productFields['ID'] = $id;

					$r = \Bitrix\Catalog\Model\Product::add($productFields);
					if($r->isSuccess() === false)
					{
						$element::Delete($id);
					}
				}
			}
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			return ['ELEMENT'=>$this->get($id)];
		}
	}

	public function updateAction(int $id, array $fields): ?array
	{
		$fields['IBLOCK_ID'] ??= $this->getProductIblockId($id);
		$r = $this->checkPermissionUpdate($id);
		if($r->isSuccess())
		{
			if (isset($fields['IBLOCK_SECTION_ID']) && (int)$fields['IBLOCK_SECTION_ID'] > 0)
			{
				$r = $this->checkPermissionIBlockElementSectionBindUpdate($fields['IBLOCK_SECTION_ID']);
			}
		}

		if($r->isSuccess())
		{
			$element = new \CIBlockElement();

			$groupFields = $this->splitFieldsByEntity($fields);

			$productFields = $groupFields['productFields'];
			$propertyFields = $groupFields['propertyFields'];
			$elementFields = $groupFields['elementFields'];

			$productFields = $this->prepareProductFields($productFields);
			$propertyFields = $this->preparePropertyFields($propertyFields);

			$propertyFields = $this->fillPropertyFieldsDefaultPropertyValues($id, $fields['IBLOCK_ID'], $propertyFields);
			$propertyFields = $this->preparePropertyFieldsUpdate($propertyFields);

			$elementFieldsUpdate = count($propertyFields)>0 ? array_merge($elementFields, ['PROPERTY_VALUES'=>$propertyFields]):$elementFields;

			$r = $this->exists($id);
			if($r->isSuccess())
			{
				$r = $this->updateValidate($elementFieldsUpdate+['ID'=>$id]);
				if($r->isSuccess())
				{
					$element->Update($id, $elementFieldsUpdate);
					if($element->LAST_ERROR<>'')
					{
						$r->addError(new Error($element->LAST_ERROR));
					}
					elseif (!empty($productFields))
					{
						$r = \Bitrix\Catalog\Model\Product::update($id, $productFields);
					}
				}
			}
		}

		if($r->isSuccess())
		{
			return ['ELEMENT'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function deleteAction(int $id): ?bool
	{
		$r = $this->checkPermissionDelete($id);
		if($r->isSuccess())
		{
			$r = $this->exists($id);
		}
		if($r->isSuccess())
		{
			if (!\CIBlockElement::Delete($id))
			{
				if ($ex = self::getApplication()->GetException())
					$r->addError(new Error($ex->GetString(), $ex->GetId()));
				else
					$r->addError(new Error('delete iBlockElement error'));
			}
		}

		if($r->isSuccess())
		{
			return true;
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function downloadAction(array $fields): ?Engine\Response\BFile
	{
		$productId = $fields['PRODUCT_ID'];
		$fieldName = $fields['FIELD_NAME'];
		$id = $fields['FILE_ID'];
		$file = [];

		$r = $this->exists($productId);
		if($r->isSuccess())
		{
			$iblockId = $this->get($productId)['IBLOCK_ID'];

			if($this->checkFieldsDownload(['NAME'=>$fieldName, 'IBLOCK_ID'=>$iblockId]) == true)
			{
				$files = [];
				$iBlock = \CIBlock::GetArrayByID($iblockId);

				if ($productId > 0)
				{
					$element = \CIBlockElement::GetList(
						array(),
						array(
							"CATALOG_ID" => $iBlock["ID"],
							"=ID" => $productId,
							"CHECK_PERMISSIONS" => "N",
						),
						false,
						false,
						array("ID", $fieldName)
					);
					while ($res = $element->GetNext())
					{
						if (isset($res[$fieldName]))
						{
							$files[] = $res[$fieldName];
						}
						elseif (isset($res[$fieldName."_VALUE"]))
						{
							if (is_array($res[$fieldName."_VALUE"]))
							{
								$files = array_merge($files, $res[$fieldName."_VALUE"]);
							}
							else
							{
								$files[] = $res[$fieldName."_VALUE"];
							}
						}
					}
				}

				if (!in_array($id, $files))
				{
					$r->addError(new Error('Product file wrong'));
				}
				else
				{
					$file = \CFile::GetFileArray($id);
					if (is_array($file) == false)
					{
						$r->addError(new Error('Product is empty'));
					}
				}
			}
			else
			{
				$r->addError(new Error('Name file field is not available'));
			}
		}

		if($r->isSuccess())
		{
			return \Bitrix\Main\Engine\Response\BFile::createByFileId($file['ID']);
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}
	//endregion Actions

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

		if (UseStore::isUsed())
		{
			unset($result['QUANTITY_TRACE']);
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

		if($id>0 && $iblockId>0)
		{
			if(count($propertyValues)>0)
			{
				$r = \CIBlockElement::GetProperty(
					$iblockId,
					$id,
					'sort', 'asc',
					array('CHECK_PERMISSIONS' => 'N')
				);
				while($property = $r->Fetch())
				{
					if($property['PROPERTY_TYPE'] !== 'F' && !array_key_exists($property['ID'], $propertyValues))
					{
						if (!array_key_exists($property['ID'], $fields))
						{
							$fields[$property['ID']] = [];
						}

						$fields[$property['ID']][] = [
							'VALUE_ID' => $property['PROPERTY_VALUE_ID'],
							'VALUE' => $property['VALUE'],
							'DESCRIPTION' => $property['DESCRIPTION']
						];
					}
				}
			}
		}

		return $fields;
	}

	protected function exists($id)
	{
		$r = new Result();
		if (isset($this->get($id)['ID']) == false)
		{
			$r->addError(new Error('Product is not exists'));
		}

		return $r;
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

		$this->attachPropertyValues($result, (int)$row['IBLOCK_ID']);

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

		if(isset($fields['SECTION_ID']))
		{
			$section = \CIBlockSection::GetByID($fields['SECTION_ID'])->Fetch();
			if(isset($section['ID']) == false)
				$r->addError(new Error('Section is not exists'));
		}
		if(isset($fields['MODIFIED_BY']))
		{
			$user = \CUser::GetByID($fields['MODIFIED_BY'])->Fetch();
			if(isset($user['ID']) == false)
				$r->addError(new Error('User modifiedBy is not exists'));
		}
		if(isset($fields['CREATED_BY']))
		{
			$user = \CUser::GetByID($fields['CREATED_BY'])->Fetch();
			if(isset($user['ID']) == false)
				$r->addError(new Error('User createdBy is not exists'));
		}
		if(isset($fields['PURCHASING_CURRENCY']))
		{
			$currency = \CCurrency::GetByID($fields['PURCHASING_CURRENCY']);
			if(isset($currency['CURRENCY']) == false)
				$r->addError(new Error('Currency purchasingCurrency is not exists'));
		}
		if(isset($fields['VAT_ID']))
		{
			$user = \CCatalogVat::GetByID($fields['VAT_ID'])->Fetch();
			if(isset($user['ID']) == false)
				$r->addError(new Error('VAT vatId is not exists'));
		}

		return $r;
	}

	/**
	 * @param array &$result
	 * @param int $iblockId
	 * @param array $propertyIds
	 * @return void
	 */
	protected function attachPropertyValues(array &$result, int $iblockId, array $propertyIds = []): void
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
						if ($fields['PROPERTY_TYPE'] === 'L')
						{
							if ($fields['MULTIPLE'] === 'Y')
							{
								if (is_array($fields['PROPERTY_VALUE_ID']))
								{
									foreach ($fields['PROPERTY_VALUE_ID'] as $i => $item)
									{
										$value[] = [
											'VALUE' => $fields['VALUE_ENUM_ID'][$i],
											'VALUE_ID' => $fields['PROPERTY_VALUE_ID'][$i]
										];
									}
								}
							}
							else
							{
								$value = [
									'VALUE' => $fields['VALUE_ENUM_ID'],
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
			return true;
		elseif ($name === "PREVIEW_PICTURE")
			return true;
		elseif ($name === "PICTURE")
			return true;
		elseif (!preg_match("/^PROPERTY_(.+)\$/", $name, $match))
			return false;
		else
		{
			$db_prop = \CIBlockProperty::GetPropertyArray($match[1], $iblockId);
			if(is_array($db_prop) && $db_prop["PROPERTY_TYPE"] === "F")
				return true;
		}
		return false;
	}

	public function addPropertyAction($fields)
	{
		$r = $this->checkPermissionIBlockModify($fields['IBLOCK_ID']);
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		$iblockProperty = new \CIBlockProperty();

		$propertyFields = array(
			'ACTIVE' => 'Y',
			'IBLOCK_ID' => $fields['IBLOCK_ID'],
			'NAME' => $fields['NAME'],
			'SORT' => $fields['SORT'] ?? 100,
			'CODE' => $fields['CODE'] ?? '',
			'MULTIPLE' => ($fields['MULTIPLE'] === 'Y') ? 'Y' : 'N',
			'IS_REQUIRED'=> ($fields['IS_REQUIRED'] === 'Y') ? 'Y' : 'N',
			'SECTION_PROPERTY'=> 'N',
		);

		$newID = (int)($iblockProperty->Add($propertyFields));
		if ($newID === 0)
		{
			$this->addError(new \Bitrix\Main\Error($iblockProperty->LAST_ERROR));
			return null;
		}

		return [
			'ID' => $newID,
			'CONTROL_ID' => 'PROPERTY_'.$newID
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
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
	//endregion checkPermissionController

	//region checkPermissionIBlock
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

	protected function checkPermissionUpdate(int $elementId): Result
	{
		$result = new Result();

		$result->addErrors(
			$this->checkPermissionIBlockElementUpdate($elementId)->getErrors()
		);
		$result->addErrors(
			$this->checkPermissionCatalogProductUpdate($elementId)->getErrors()
		);

		return $result;
	}

	protected function checkPermissionCatalogProductUpdate(int $elementId): Result
	{
		$result = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_PRODUCT_EDIT))
		{
			$result->addError(new Error('Access Denied', 200040300040));
		}

		return $result;
	}


	protected function checkPermissionIBlockElementUpdate(int $elementId)
	{
		$iblockId = \CIBlockElement::GetIBlockByID($elementId);
		return $this->checkPermissionIBlockElementModify($iblockId, $elementId);
	}

	protected function checkPermissionIBlockModify($iblockId)
	{
		$r = new Result();

		$arIBlock = \CIBlock::GetArrayByID($iblockId);
		if($arIBlock)
			$bBadBlock = !\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::IBLOCK_EDIT);
		else
			$bBadBlock = true;

		if($bBadBlock)
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}
		return $r;
	}

	protected function checkPermissionIBlockElementModify($iblockId, $elementId)
	{
		$r = new Result();

		$arIBlock = \CIBlock::GetArrayByID($iblockId);
		if($arIBlock)
		{
			if ($elementId > 0)
			{
				$bBadBlock = !\CIBlockElementRights::UserHasRightTo($iblockId, $elementId, self::IBLOCK_ELEMENT_EDIT); //access edit
			}
			else
			{
				$bBadBlock = !\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::IBLOCK_ELEMENT_EDIT);
			}
		}
		else
		{
			$bBadBlock = true;
		}

		if($bBadBlock)
		{
			$r->addError(new Error('Access Denied', 200040300043));
		}
		return $r;
	}

	protected function checkPermissionDelete(int $elementId): Result
	{
		$result = new Result();

		$result->addErrors(
			$this->checkPermissionIBlockElementDelete($elementId)->getErrors()
		);
		$result->addErrors(
			$this->checkPermissionCatalogProductDelete($elementId)->getErrors()
		);

		return $result;
	}

	protected function checkPermissionCatalogProductDelete(int $elementId): Result
	{
		$result = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_PRODUCT_DELETE))
		{
			$result->addError(new Error('Access Denied', 200040300040));
		}

		return $result;
	}

	protected function checkPermissionIBlockElementDelete(int $elementId): Result
	{
		$r = new Result();

		$iblockId = \CIBlockElement::GetIBlockByID($elementId);
		$arIBlock = \CIBlock::GetArrayByID($iblockId);
		if($arIBlock)
			$bBadBlock = !\CIBlockElementRights::UserHasRightTo($iblockId, $elementId, self::IBLOCK_ELEMENT_DELETE); //access delete
		else
			$bBadBlock = true;

		if($bBadBlock)
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}
		return $r;
	}

	protected function checkPermissionIBlockElementGet($elementId)
	{
		$r = new Result();

		$iblockId = \CIBlockElement::GetIBlockByID($elementId);
		$arIBlock = \CIBlock::GetArrayByID($iblockId);
		if($arIBlock)
			$bBadBlock = !\CIBlockElementRights::UserHasRightTo($iblockId, $elementId, self::IBLOCK_ELEMENT_READ); //access read
		else
			$bBadBlock = true;

		if($bBadBlock)
		{
			$r->addError(new Error('Access Denied', 200040300040));
		}
		return $r;
	}

	protected function checkPermissionIBlockElementList($iblockId)
	{
		$r = new Result();

		$arIBlock = \CIBlock::GetArrayByID($iblockId);
		if($arIBlock)
			$bBadBlock = !\CIBlockRights::UserHasRightTo($iblockId, $iblockId, self::IBLOCK_READ);
		else
			$bBadBlock = true;

		if($bBadBlock)
		{
			$r->addError(new Error('Access Denied', 200040300030));
		}
		return $r;
	}

	protected function checkPermissionIBlockElementSectionBindModify($iblockId, $iblockSectionId)
	{
		$r = new Result();

		$arIBlock = \CIBlock::GetArrayByID($iblockId);
		if($arIBlock)
			$bBadBlock = !\CIBlockSectionRights::UserHasRightTo($iblockId, $iblockSectionId, self::IBLOCK_ELEMENT_SECTION_BIND); //access update
		else
			$bBadBlock = true;

		if($bBadBlock)
		{
			$r->addError(new Error('Access Denied', 200040300050));
		}
		return $r;
	}

	protected function checkPermissionIBlockElementSectionBindUpdate($iblockSectionId)
	{
		$iblockId = $this->getIBlockBySectionId($iblockSectionId);
		return $this->checkPermissionIBlockElementSectionBindModify($iblockId, $iblockSectionId);
	}

	protected function getIBlockBySectionId($id)
	{
		$iblockId = 0;

		$section = \CIBlockSection::GetByID($id);
		if ($res = $section->GetNext())
		{
			$iblockId = $res["IBLOCK_ID"];
		}

		return $iblockId;
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

	public static function processItemEvent(array $arParams, array $arHandler): array
	{
		$id = null;
		$event = $arParams[0] ?? null;

		if (!$event)
		{
			throw new RestException('event object not found trying to process event');
		}

		if($event instanceof Event) // update, add
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

		$product = \Bitrix\Catalog\Model\Product::getCacheItem($id);

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

		return [
			Event::makeEventName($model,DataManager::EVENT_ON_AFTER_ADD) => $entity->getModule().'.'.$entity->getName().'.on.add',
			Event::makeEventName($model,DataManager::EVENT_ON_AFTER_UPDATE) => $entity->getModule().'.'.$entity->getName().'.on.update',
			Event::makeEventName($class,DataManager::EVENT_ON_DELETE) => $entity->getModule().'.'.$entity->getName().'.on.delete',
		];
	}
	// endregion

	// region Internal tools

	/**
	 * Returns iblock id for product, if exists.
	 *
	 * @param int $productId
	 * @return int|null
	 */
	protected static function getProductIblockId(int $productId): ?int
	{
		$iblockId = \CIBlockElement::GetIBlockByID($productId);

		return
			$iblockId === false
				? null
				: $iblockId
		;
	}

	// endRegion
}
