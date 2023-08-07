<?php

namespace Bitrix\Catalog\v2;

use Bitrix\Catalog\Model\Product;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;

/**
 * Class BaseIblockElementRepository
 *
 * @package Bitrix\Catalog\v2
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseIblockElementRepository implements IblockElementRepositoryContract
{
	/** @var \Bitrix\Catalog\v2\BaseIblockElementFactory */
	protected $factory;
	/** @var \Bitrix\Catalog\v2\Iblock\IblockInfo */
	protected $iblockInfo;

	private ?string $detailUrlTemplate = null;

	private bool $allowedDetailUrl;

	/**
	 * BaseIblockElementRepository constructor.
	 *
	 * @param \Bitrix\Catalog\v2\BaseIblockElementFactory $factory
	 * @param \Bitrix\Catalog\v2\Iblock\IblockInfo $iblockInfo
	 */
	public function __construct(BaseIblockElementFactory $factory, IblockInfo $iblockInfo)
	{
		$this->factory = $factory;
		$this->iblockInfo = $iblockInfo;
		$this->setAutoloadDetailUrl(false);
	}

	public function getEntityById(int $id): ?BaseIblockElementEntity
	{
		if ($id <= 0)
		{
			throw new \OutOfRangeException($id);
		}

		$entities = $this->getEntitiesBy([
			'filter' => [
				'=ID' => $id,
			],
		]);

		return reset($entities) ?: null;
	}

	public function getEntitiesBy($params): array
	{
		$entities = [];

		foreach ($this->getList((array)$params) as $item)
		{
			$entities[] = $this->createEntity($item);
		}

		return $entities;
	}

	public function save(BaseEntity ...$entities): Result
	{
		$result = new Result();

		$savedIds = [];

		foreach ($entities as $entity)
		{
			$entityId = $entity->getId();
			if ($entityId !== null)
			{
				$res = $this->updateInternal($entityId, $entity->getChangedFields());

				if ($res->isSuccess())
				{
					$savedIds[] = $entityId;
				}
				else
				{
					$result->addErrors($res->getErrors());
				}
			}
			else
			{
				$res = $this->addInternal($entity->getFields());

				if ($res->isSuccess())
				{
					$id = $res->getData()['ID'];
					$entity->setId($id);
					$savedIds[] = $id;
				}
				else
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		// re-initialize original fields from database after save (DETAIL_PICTURE, etc)
		if (!empty($savedIds))
		{
			$fields = $this->getList([
				'filter' => [
					'ID' => $savedIds,
				],
			]);

			foreach ($entities as $entity)
			{
				$entityFields = $fields[$entity->getId()] ?? null;
				if (!is_array($entityFields))
				{
					AddMessage2Log('Cannot load product ' . $entity->getId(), 'catalog');
					continue;
				}
				$entityFields = array_diff_key($entityFields, ['TYPE' => true]);

				if ($entityFields)
				{
					$entity->initFields($entityFields);
				}
			}
		}

		return $result;
	}

	public function delete(BaseEntity ...$entities): Result
	{
		$result = new Result();

		foreach ($entities as $entity)
		{
			if ($entityId = $entity->getId())
			{
				$res = $this->deleteInternal($entityId);

				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	public function setAutoloadDetailUrl(bool $state): self
	{
		$this->allowedDetailUrl = $state;

		return $this;
	}

	public function checkAutoloadDetailUrl(): bool
	{
		return $this->allowedDetailUrl;
	}

	public function setDetailUrlTemplate(?string $template): self
	{
		$this->detailUrlTemplate = $template;

		$this->setAutoloadDetailUrl($template !== null);

		return $this;
	}

	public function getDetailUrlTemplate(): ?string
	{
		return $this->detailUrlTemplate;
	}

	protected function getDefaultElementSelect(): array
	{
		$result = [
			'ID',
			'TIMESTAMP_X',
			'MODIFIED_BY',
			'DATE_CREATE',
			'CREATED_BY',
			'IBLOCK_ID',
			'IBLOCK_SECTION_ID',
			'ACTIVE',
			'ACTIVE_FROM',
			'ACTIVE_TO',
			'SORT',
			'NAME',
			'PREVIEW_PICTURE',
			'PREVIEW_TEXT',
			'PREVIEW_TEXT_TYPE',
			'DETAIL_PICTURE',
			'DETAIL_TEXT',
			'DETAIL_TEXT_TYPE',
			'WF_STATUS_ID',
			'WF_PARENT_ELEMENT_ID',
			'WF_NEW',
			'IN_SECTIONS',
			'SHOW_COUNTER',
			'SHOW_COUNTER_START',
			'CODE',
			'TAGS',
			'XML_ID',
			'TMP_ID',
		];
		if ($this->checkAutoloadDetailUrl())
		{
			$result[] = 'DETAIL_PAGE_URL';
		}

		return $result;
	}

	protected function getList(array $params): array
	{
		$filter = $params['filter'] ?? [];
		$order = $params['order'] ?? [];
		$nav = $params['nav'] ?? false;

		$iblockElements = [];
		$listIds = [];

		$iterator = \CIBlockElement::GetList(
			$order,
			array_merge(
				$filter,
				$this->getAdditionalFilter(),
				$this->getAdditionalProductFilter(),
			),
			false,
			$nav,
			[
				'ID',
				'IBLOCK_ID',
			],
		);
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['ID'];
			$iblockElements[$id] = $row;
			$listIds[] = $id;
		}
		unset($iterator);

		if (empty($iblockElements))
		{
			return [];
		}

		$elementSelect = $this->getDefaultElementSelect();
		$detailUrlTemplate = $this->checkAutoloadDetailUrl() ? $this->getDetailUrlTemplate() : null;
		$specificFields = [
			'QUANTITY_TRACE' => 'QUANTITY_TRACE_ORIG',
			'CAN_BUY_ZERO' => 'CAN_BUY_ZERO_ORIG',
			'SUBSCRIBE' => 'SUBSCRIBE_ORIG',
		];
		$productSelect = array_merge(['*', 'UF_*'], array_values($specificFields));

		foreach (array_chunk($listIds, CATALOG_PAGE_SIZE) as $pageIds)
		{
			$elementsIterator = \CIBlockElement::GetList(
				[],
				[
					'ID' => $pageIds,
					'CHECK_PERMISSIONS' => 'N',
					'SHOW_NEW' => 'Y',
				],
				false,
				false,
				$elementSelect,
			);
			if ($detailUrlTemplate)
			{
				$elementsIterator->SetUrlTemplates($detailUrlTemplate);
			}
			while ($element = $elementsIterator->getNext())
			{
				$id = (int)$element['ID'];
				$iblockElements[$id] += $this->replaceRawFromTilda($element);
			}
			unset($elementsIterator);

			$productIterator = ProductTable::getList([
				'select' => $productSelect,
				'filter' => [
					'@ID' => $pageIds,
				],
			]);
			while ($product = $productIterator->fetch())
			{
				$id = (int)$product['ID'];
				unset($product['ID']);
				foreach ($specificFields as $field => $originalField)
				{
					$product[$field] = $product[$originalField];
					unset($product[$originalField]);
				}
				$iblockElements[$id] += $product;
			}
			unset($productIterator);
		}

		return $iblockElements;
	}

	protected function getAdditionalFilter(): array
	{
		return [
			'CHECK_PERMISSIONS' => 'N',
			'MIN_PERMISSION' => 'R',
		];
	}

	protected function getAdditionalProductFilter(): array
	{
		return [];
	}

	protected function createEntity(array $fields = []): BaseIblockElementEntity
	{
		$entity = $this->makeEntity($fields);

		$entity->initFields($fields);

		return $entity;
	}

	abstract protected function makeEntity(array $fields = []): BaseIblockElementEntity;

	protected function addInternal(array $fields): Result
	{
		$result = new Result();

		$elementFields = $this->prepareElementFields($fields);

		if (!empty($elementFields))
		{
			$element = new \CIBlockElement();
			$id = $element->add($elementFields);

			if ($id)
			{
				$result->setData(['ID' => $id]);
			}
			else
			{
				$result->addError(new Error($element->LAST_ERROR));
			}
		}

		if ($result->isSuccess())
		{
			$productFields = $this->prepareProductFields($fields);

			if (!empty($productFields))
			{
				$productFields['ID'] = $result->getData()['ID'];
				$res = Product::add([
					'fields' => $productFields,
					'external_fields' => [
						'IBLOCK_ID' => $elementFields['IBLOCK_ID'],
					],
				]);

				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	protected function updateInternal(int $id, array $fields): Result
	{
		$result = new Result();

		$elementFields = $this->prepareElementFields($fields);

		if (!empty($elementFields))
		{
			$element = new \CIBlockElement();
			$res = $element->update($id, $elementFields);

			if (!$res)
			{
				$result->addError(new Error($element->LAST_ERROR));
			}
		}

		if ($result->isSuccess())
		{
			$productFields = $this->prepareProductFields($fields);

			if (!empty($productFields))
			{
				$res = Product::update($id, $productFields);

				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	protected function deleteInternal(int $id): Result
	{
		$result = new Result();

		$res = \CIBlockElement::delete($id);

		if ($res)
		{
			$res = Product::delete($id);

			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}
		else
		{
			global $APPLICATION;
			$exception = $APPLICATION->GetException();

			if ($exception && $exception->GetString())
			{
				$errorMessage = $exception->GetString();
			}
			else
			{
				$errorMessage = "Delete operation for entity with id {$id} failed.";
			}

			$result->addError(new Error($errorMessage));
		}

		return $result;
	}

	protected function prepareElementFields(array $fields): array
	{
		if (array_key_exists('ACTIVE_FROM', $fields) && $fields['ACTIVE_FROM'] === null)
		{
			$fields['ACTIVE_FROM'] = false;
		}

		if (array_key_exists('ACTIVE_TO', $fields) && $fields['ACTIVE_TO'] === null)
		{
			$fields['ACTIVE_TO'] = false;
		}

		if (!array_key_exists('MODIFIED_BY', $fields))
		{
			global $USER;
			if (isset($USER) && $USER instanceof \CUser)
			{
				$fields['MODIFIED_BY'] = $USER->getID();
			}
		}

		return array_intersect_key($fields, ElementTable::getMap());
	}

	protected function prepareProductFields(array $fields): array
	{
		$catalogFields = array_intersect_key(
			$fields,
			array_fill_keys(
				Product::getTabletFieldNames(Product::FIELDS_ALL),
				true
			)
		);

		if (isset($catalogFields['TIMESTAMP_X']))
		{
			$catalogFields['TIMESTAMP_X'] = new DateTime($catalogFields['TIMESTAMP_X']);
		}

		if (isset($catalogFields['TYPE']))
		{
			$catalogFields['TYPE'] = (int)$catalogFields['TYPE'];
		}

		return $catalogFields;
	}

	private function replaceRawFromTilda(array $element): array
	{
		$newElement = [];

		foreach ($element as $key => $value)
		{
			$tildaKey = "~{$key}";
			if (isset($element[$tildaKey]))
			{
				$newElement[$key] = $element[$tildaKey];
			}
		}

		return $newElement;
	}
}
