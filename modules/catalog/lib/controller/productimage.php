<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\FileTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

final class ProductImage extends Controller
{
	use CheckExists; // default implementation of existence check

	//region Actions
	/**
	 * REST method catalog.productImage.getFiles
	 *
	 * @param int $productId
	 * @param array $select
	 * @param \CRestServer|null $restServer
	 *
	 * @return Page|null
	 */
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * REST method catalog.productImage.list
	 *
	 * @param int $productId
	 * @param array $select
	 * @param \CRestServer|null $restServer
	 *
	 * @return Page|null
	 */
	public function listAction(int $productId, array $select = [], \CRestServer $restServer = null): ?Page
	{
		if ($productId <= 0)
		{
			$this->addError(new Error('Empty productID'));

			return null;
		}

		$product = $this->getProduct($productId);
		if (!$product)
		{
			$this->addError(new Error('Product was not found'));

			return null;
		}

		$r = $this->checkPermissionProductRead($product);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		$imageIds = [];
		if ($product['PREVIEW_PICTURE'])
		{
			$imageIds[] = $product['PREVIEW_PICTURE'];
		}
		if ($product['DETAIL_PICTURE'])
		{
			$imageIds[] = $product['DETAIL_PICTURE'];
		}
		if ($this->getMorePhotoPropertyId((int)$product['IBLOCK_ID']))
		{
			$imageIds = [...$imageIds, ...$this->getMorePhotoPropertyValues($product)];
		}

		$result = [];
		$fileTableResult = FileTable::getList(['filter' => ['=ID' => $imageIds]]);
		while ($image = $fileTableResult->fetch())
		{
			if ($product['PREVIEW_PICTURE'] === $image['ID'])
			{
				$type = 'PREVIEW_PICTURE';
			}
			elseif ($product['DETAIL_PICTURE'] === $image['ID'])
			{
				$type = 'DETAIL_PICTURE';
			}
			else
			{
				$type = 'MORE_PHOTO';
			}
			$image['TYPE'] = $type;
			$image['PRODUCT_ID'] = $product['ID'];

			$result[] = $this->prepareFileStructure($image, $restServer, $select);
		}

		return new Page(
			'PRODUCT_IMAGES',
			$result,
			count($result)
		);
	}

	/**
	 * REST method catalog.productImage.get
	 *
	 * @param int $id
	 * @param int $productId
	 * @param \CRestServer|null $restServer
	 *
	 * @return array[]|null
	 */
	public function getAction(int $id, int $productId, \CRestServer $restServer = null): ?array
	{
		$product = $this->getProduct($productId);
		if (!$product)
		{
			$this->addError(new Error('Product was not found'));

			return null;
		}

		$r = $this->checkPermissionProductRead($product);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		$image = $this->getImageById($id, $product);
		if (!$image)
		{
			$this->addError($this->getErrorEntityNotExists());

			return null;
		}

		return [$this->getServiceItemName() => $this->prepareFileStructure($image, $restServer)];
	}

	/**
	 * REST method catalog.productImage.add
	 *
	 * @param array $fields
	 * @param array $fileContent
	 * @param \CRestServer|null $restServer
	 *
	 * @return void
	 */
	public function addAction(array $fields, array $fileContent, \CRestServer $restServer = null): ?array
	{
		if (!Loader::includeModule('rest'))
		{
			return null;
		}

		$product = $this->getProduct((int)$fields['PRODUCT_ID']);
		if (!$product)
		{
			$this->addError(new Error('Product was not found'));
			return null;
		}

		$r = $this->checkPermissionProductWrite($product);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		$fileData = \CRestUtil::saveFile($fileContent);
		if (!$fileData)
		{
			$this->addError(new Error('Could not save image.'));
			return null;
		}

		$checkPictureResult = \CFile::CheckFile($fileData, 0 ,false, \CFile::GetImageExtensions());
		if ($checkPictureResult !== '')
		{
			$this->addError(new Error($checkPictureResult));
			return null;
		}

		if ($fields['TYPE'] === 'DETAIL_PICTURE' || $fields['TYPE'] === 'PREVIEW_PICTURE')
		{
			$updateFields = [$fields['TYPE'] => $fileData];
		}
		else
		{
			$fields['TYPE'] = 'MORE_PHOTO';
			$morePhotoPropertyId = $this->getMorePhotoPropertyId((int)$product['IBLOCK_ID']);
			if (!$morePhotoPropertyId)
			{
				$this->addError(new Error(
					'Image product property does not exists. Create MORE_PHOTO property'
				));

				return null;
			}

			$updateFields = [
				'n0' => [
					'VALUE' => $fileData,
				],
			];
		}

		$connection = Application::getConnection();
		$connection->startTransaction();
		try
		{
			if ($fields['TYPE'] === 'DETAIL_PICTURE' || $fields['TYPE'] === 'PREVIEW_PICTURE')
			{
				$error = $this->updateProductImage((int)$product['ID'], $updateFields);
			}
			else
			{
				$error = $this->updateProductMorePhoto((int)$product['ID'], (int)$product['IBLOCK_ID'], $updateFields);
			}
		}
		catch (SqlQueryException)
		{
			$error = 'Internal error adding product image. Try adding again.';
		}
		if ($error !== '')
		{
			$connection->rollbackTransaction();
			$this->addError(new Error($error));

			return null;
		}
		$connection->commitTransaction();

		if ($fields['TYPE'] === 'DETAIL_PICTURE' || $fields['TYPE'] === 'PREVIEW_PICTURE')
		{
			$product = $this->getProduct((int)$fields['PRODUCT_ID'], [$fields['TYPE']]);
			$imageId = $product[$fields['TYPE']];
		}
		else
		{
			$morePhotoIds = $this->getMorePhotoPropertyValues($product);
			if (!$morePhotoIds)
			{
				$this->addError(new Error('Empty image.'));

				return null;
			}
			$imageId = end($morePhotoIds);
		}
		$image = FileTable::getRowById($imageId);
		if (!$image)
		{
			$this->addError($this->getErrorEntityNotExists());

			return null;
		}
		$image['TYPE'] = $fields['TYPE'];
		$image['PRODUCT_ID'] = $fields['PRODUCT_ID'];

		return [$this->getServiceItemName() => $this->prepareFileStructure($image, $restServer)];
	}

	/**
	 * REST method catalog.productImage.delete
	 *
	 * @param int $id
	 * @param int $productId
	 *
	 * @return void
	 */
	public function deleteAction(int $id, int $productId): ?bool
	{
		if (!$this->exists($id))
		{
			$this->addError($this->getErrorEntityNotExists());

			return null;
		}

		$product = $this->getProduct($productId);
		if (!$product)
		{
			$this->addError(new Error('Product was not found'));

			return null;
		}

		$r = $this->checkPermissionProductWrite($product);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		if ($id === (int)$product['PREVIEW_PICTURE'])
		{
			$updateFields = ['PREVIEW_PICTURE' => \CIBlock::makeFileArray(null, true)];
		}
		elseif ($id === (int)$product['DETAIL_PICTURE'])
		{
			$updateFields = ['DETAIL_PICTURE' => \CIBlock::makeFileArray(null, true)];
		}
		else
		{
			$morePhotoPropertyValueId = $this->getMorePhotoPropertyValueId($product, $id);
			if (!$morePhotoPropertyValueId)
			{
				$this->addError($this->getErrorEntityNotExists());

				return null;
			}
			$updateFields = [$morePhotoPropertyValueId => \CIBlock::makeFileArray(null, true)];
		}

		$connection = Application::getConnection();
		$connection->startTransaction();
		try
		{
			if ($id === (int)$product['PREVIEW_PICTURE'] || $id === (int)$product['DETAIL_PICTURE'])
			{
				$error = $this->updateProductImage((int)$product['ID'], $updateFields);
			}
			else
			{
				$error = $this->updateProductMorePhoto((int)$product['ID'], (int)$product['IBLOCK_ID'], $updateFields);
			}
		}
		catch (SqlQueryException)
		{
			$error = 'Internal error deleting product image. Try deleting again.';
		}
		if ($error !== '')
		{
			$connection->rollbackTransaction();
			$this->addError(new Error($error));

			return null;
		}
		$connection->commitTransaction();

		return true;
	}
	//endregion

	private function prepareFileStructure(
		array $image,
		\CRestServer $restServer = null,
		array $selectedFields = null
	): array
	{
		$result = [];
		if (!$selectedFields)
		{
			$selectedFields = array_keys($this->getViewManager()->getView($this)->getFields());
		}

		foreach ($selectedFields as $name)
		{
			if ($name === 'ID')
			{
				$result[$name] = (int)$image['ID'];
			}
			if ($name === 'NAME')
			{
				$result[$name] = $image['FILE_NAME'];
			}
			elseif ($name === 'DETAIL_URL')
			{
				$result[$name] = \CFile::getFileSRC($image);
			}
			elseif ($name === 'DOWNLOAD_URL')
			{
				$result[$name] =
					$restServer
						? \CRestUtil::getDownloadUrl(['id' => $image['ID']], $restServer)
						: \CFile::getFileSRC($image)
				;
			}
			elseif ($name === 'CREATE_TIME')
			{
				$result[$name] = $image['TIMESTAMP_X'];
			}
			elseif ($name === 'PRODUCT_ID')
			{
				$result[$name] = (int)$image['PRODUCT_ID'];
			}
			elseif ($name === 'TYPE')
			{
				$result[$name] = $image['TYPE'];
			}
		}

		return $result;
	}

	protected function getEntityTable()
	{
		return new FileTable();
	}

	private function getProduct(int $productId, ?array $select = null): ?array
	{
		return \Bitrix\Iblock\ElementTable::getRow([
			'select' => $select ?: ['ID', 'IBLOCK_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
			'filter' => ['=ID' => $productId],
		]);
	}

	private function getImageById(int $id, array $product): ?array
	{
		if ((int)$product['PREVIEW_PICTURE'] === $id)
		{
			$type = 'PREVIEW_PICTURE';
		}
		elseif ((int)$product['DETAIL_PICTURE'] === $id)
		{
			$type = 'DETAIL_PICTURE';
		}
		else
		{
			$morePhotoIds = $this->getMorePhotoPropertyValues($product);
			if (!in_array($id, $morePhotoIds))
			{
				return null;
			}

			$type = 'MORE_PHOTO';
		}

		$image = FileTable::getRowById($id);
		if (!$image)
		{
			return null;
		}

		$image['TYPE'] = $type;
		$image['PRODUCT_ID'] = $product['ID'];

		return $image;
	}

	private function getMorePhotoPropertyValueId(array $product, int $value): ?int
	{
		$morePhotoPropertyId = $this->getMorePhotoPropertyId((int)$product['IBLOCK_ID']);
		if (!$morePhotoPropertyId)
		{
			return null;
		}

		$propertyValuesResult = $this->getPropertyValues($product, $morePhotoPropertyId, true);
		$morePhotoPropertyIds = $propertyValuesResult[$morePhotoPropertyId];
		if (!$morePhotoPropertyIds)
		{
			return null;
		}

		$valueIndex = array_search($value, $morePhotoPropertyIds);
		if ($valueIndex === false)
		{
			return null;
		}

		return (int)$propertyValuesResult['PROPERTY_VALUE_ID'][$morePhotoPropertyId][$valueIndex] ?? null;
	}

	private function getMorePhotoPropertyValues(array $product): array
	{
		$morePhotoPropertyId = $this->getMorePhotoPropertyId((int)$product['IBLOCK_ID']);
		if (!$morePhotoPropertyId)
		{
			return [];
		}
		$propertyValuesResult = $this->getPropertyValues($product, $morePhotoPropertyId);

		return $propertyValuesResult[$morePhotoPropertyId] ?? [];
	}

	private function getPropertyValues(array $product, int $propertyId, bool $extMode = false): array
	{
		return \CIBlockElement::getPropertyValues(
			$product['IBLOCK_ID'],
			[
				'ID' => $product['ID'],
				'IBLOCK_ID' => $product['IBLOCK_ID'],
			],
			$extMode,
			[
				'ID' => $propertyId,
			],
		)->Fetch() ?: [];
	}

	private function getMorePhotoPropertyId(int $iblockId): ?int
	{
		return \Bitrix\Iblock\PropertyTable::getRow([
			'select' => ['ID'],
			'filter' => ['=CODE' => 'MORE_PHOTO', '=IBLOCK_ID' => $iblockId],
			'cache' => ['ttl' => 86400],
		])['ID'] ?? null;
	}

	private function updateProductImage(int $productId, array $updateFields): string
	{
		$iblockElement = new \CIBlockElement();
		$iblockElement->update($productId, $updateFields);

		return $iblockElement->getLastError();
	}

	private function updateProductMorePhoto(int $productId, int $iblockId, array $updateFields): string
	{
		\CIBlockElement::SetPropertyValues(
			$productId,
			$iblockId,
			$updateFields,
			'MORE_PHOTO',
		);
		$exception = self::getApplication()->GetException();
		if ($exception)
		{
			return $exception->GetString();
		}

		return '';
	}

	private function checkPermissionProductRead(array $product): Result
	{
		$r = $this->checkReadPermissionEntity();
		if (!$r->isSuccess())
		{
			return $r;
		}

		return $this->checkPermissionProduct($product, self::IBLOCK_ELEMENT_READ, $this->getErrorCodeReadAccessDenied());
	}

	private function checkPermissionProductWrite(array $product): Result
	{
		$r = $this->checkModifyPermissionEntity();
		if (!$r->isSuccess())
		{
			return $r;
		}

		return $this->checkPermissionProduct($product, self::IBLOCK_ELEMENT_EDIT, $this->getErrorCodeModifyAccessDenied());
	}

	private function checkPermissionProduct(array $product, string $permission, int $errorCode): Result
	{
		$r = new Result();
		if(!\CIBlockElementRights::UserHasRightTo($product['IBLOCK_ID'], $product['ID'], $permission))
		{
			$r->addError(new Error('Access Denied', $errorCode));
		}

		return $r;
	}

	protected function checkModifyPermissionEntity(): Result
	{
		$r = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_CATALOG_VIEW))
		{
			$r->addError($this->getErrorModifyAccessDenied());
		}

		return $r;
	}

	protected function checkReadPermissionEntity(): Result
	{
		$r = new Result();

		if (
			!$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			&& !$this->accessController->check(ActionDictionary::ACTION_CATALOG_VIEW)
		)
		{
			$r->addError($this->getErrorReadAccessDenied());
		}

		return $r;
	}
}
