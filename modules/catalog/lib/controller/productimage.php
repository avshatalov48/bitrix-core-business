<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Image\BaseImage;
use Bitrix\Catalog\v2\Image\DetailImage;
use Bitrix\Catalog\v2\Image\MorePhotoImage;
use Bitrix\Catalog\v2\Image\PreviewImage;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\FileTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

final class ProductImage extends Controller
{
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
		return ['PRODUCT_IMAGE' => $this->getViewFields()];
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

		$result = [];
		foreach ($product->getImageCollection() as $image)
		{
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

		$r = $this->hasImage($id, $product);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		/** @var BaseImage $image */
		$image = $product->getImageCollection()->findById($id);

		return ['PRODUCT_IMAGE' => $this->prepareFileStructure($image, $restServer)];
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

		if ($fields['TYPE'] === DetailImage::CODE)
		{
			$product->getImageCollection()->getDetailImage()->setFileStructure($fileData);
		}
		elseif ($fields['TYPE'] === PreviewImage::CODE)
		{
			$product->getImageCollection()->getPreviewImage()->setFileStructure($fileData);
		}
		else
		{
			if (!$product->getPropertyCollection()->findByCode(MorePhotoImage::CODE))
			{
				$this->addError(
					new Error(
						"Image product property does not exists. Create" . MorePhotoImage::CODE . " property"
					)
				);

				return null;
			}

			$product->getImageCollection()->addValue($fileData);
		}

		$r = $product->save();
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
		}

		if ($fields['TYPE'] === DetailImage::CODE)
		{
			$image = $product->getImageCollection()->getDetailImage();
		}
		elseif ($fields['TYPE'] === PreviewImage::CODE)
		{
			$image = $product->getImageCollection()->getPreviewImage();
		}
		else
		{
			$morePhotos = $product->getImageCollection()->getMorePhotos();
			$image = end($morePhotos);
		}

		return ['PRODUCT_IMAGE' => $this->prepareFileStructure($image, $restServer)];
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

		$r = $this->hasImage($id, $product);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		$product
			->getImageCollection()
			->findById($id)
			->remove()
		;

		$r = $product->save();
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		return true;
	}
	//endregion

	private function prepareFileStructure(
		BaseImage $baseImage,
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
				$result[$name] = $baseImage->getField('ID');
			}
			if ($name === 'NAME')
			{
				$result[$name] = $baseImage->getField('FILE_NAME');
			}
			elseif ($name === 'DETAIL_URL')
			{
				$result[$name] = $baseImage->getSource();
			}
			elseif ($name === 'DOWNLOAD_URL')
			{
				$result[$name] =
					$restServer
						? \CRestUtil::getDownloadUrl(['id' => $baseImage->getId()], $restServer)
						: $baseImage->getSource()
				;
			}
			elseif ($name === 'CREATE_TIME')
			{
				$result[$name] = $baseImage->getField('TIMESTAMP_X');
			}
			elseif ($name === 'PRODUCT_ID')
			{
				$result[$name] = $baseImage->getParent()->getId();
			}
			elseif ($name === 'TYPE')
			{
				$result[$name] = $baseImage->getCode();
			}
		}

		return $result;
	}

	protected function getEntityTable()
	{
		return new FileTable();
	}

	private function getProduct(int $productId): ?BaseEntity
	{
		$product = ServiceContainer::getRepositoryFacade()->loadProduct($productId);
		if ($product)
		{
			return $product;
		}

		return ServiceContainer::getRepositoryFacade()->loadVariation($productId);
	}

	private function hasImage(int $id, BaseEntity $product): Result
	{
		$r = $this->exists($id);
		if (!$r->isSuccess())
		{
			return $r;
		}

		$image = $product->getImageCollection()->findById($id);
		if (!$image)
		{
			$r->addError(new Error('Image does not exist'));
		}

		return $r;
	}

	protected function exists($id)
	{
		$r = new Result();
		if (!isset($this->get($id)['ID']))
		{
			$r->addError(new Error('Image does not exist'));
		}

		return $r;
	}

	private function checkPermissionProductRead(BaseEntity $product): Result
	{
		$r = $this->checkReadPermissionEntity();
		if (!$r->isSuccess())
		{
			return $r;
		}

		return $this->checkPermissionProduct($product, self::IBLOCK_ELEMENT_READ, 200040300010);
	}

	private function checkPermissionProductWrite(BaseEntity $product): Result
	{
		$r = $this->checkModifyPermissionEntity();
		if (!$r->isSuccess())
		{
			return $r;
		}

		return $this->checkPermissionProduct($product, self::IBLOCK_ELEMENT_EDIT, 200040300020);
	}

	private function checkPermissionProduct(BaseEntity $product, string $permission, int $errorCode): Result
	{
		$r = new Result();
		if(!\CIBlockElementRights::UserHasRightTo($product->getIblockId(), $product->getId(), $permission))
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
			$r->addError(new Error('Access Denied', 200040300020));
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
			$r->addError(new Error('Access Denied', 200040300010));
		}

		return $r;
	}
}
