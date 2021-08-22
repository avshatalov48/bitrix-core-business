<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Component\ImageInput;
use Bitrix\Catalog\MeasureTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Image\DetailImage;
use Bitrix\Catalog\v2\Image\MorePhotoImage;
use Bitrix\Catalog\v2\Image\PreviewImage;
use Bitrix\Catalog\v2\Integration\JS\ProductForm\BasketBuilder;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\Security\Sign\Signer;

class ProductSelector extends JsonController
{
	public function configureActions()
	{
		return [
			'getSelectedSku' => [
				'+prefilters' => [
					new ActionFilter\CloseSession(),
				],
			],
			'getProduct' => [
				'+prefilters' => [
					new ActionFilter\CloseSession(),
				],
			],
		];
	}

	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
				new ActionFilter\Scope(ActionFilter\Scope::AJAX),
			]
		);
	}

	protected function processBeforeAction(Action $action)
	{
		global $USER;
		if ($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_view'))
		{
			return parent::processBeforeAction($action);
		}

		return false;
	}

	/**
	 * @param int $variationId
	 * @param array $options
	 * @return array
	 */
	public function getSelectedSkuAction(int $variationId, array $options = []): ?array
	{
		$iterator = \CIBlockElement::GetList(
			[],
			[
				'ID' => $variationId,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			],
			false,
			false,
			['ID', 'IBLOCK_ID']
		);
		$element = $iterator->Fetch();
		if (!$element)
		{
			return null;
		}
		unset($iterator);

		$skuRepository = ServiceContainer::getSkuRepository($element['IBLOCK_ID']);
		if (!$skuRepository)
		{
			return null;
		}

		/** @var BaseSku $sku */
		$sku = $skuRepository->getEntityById($variationId);

		if (!$sku)
		{
			return null;
		}

		return $this->prepareResponse($sku, $options);
	}

	/**
	 * @param int $productId
	 * @param array $options
	 * @return array|null
	 */
	public function getProductAction(int $productId, array $options = []): ?array
	{
		$iterator = \CIBlockElement::GetList(
			[],
			[
				'ID' => $productId,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			],
			false,
			false,
			['ID', 'IBLOCK_ID', 'TYPE']
		);
		$element = $iterator->Fetch();
		if (!$element)
		{
			return null;
		}

		if ((int)$element['TYPE'] === ProductTable::TYPE_OFFER)
		{
			$sku = $this->loadSkuById((int)$element['IBLOCK_ID'], (int)$element['ID']);
		}
		else
		{
			$sku = $this->loadFirstSkuForProduct((int)$element['IBLOCK_ID'], (int)$element['ID']);
		}

		if (!$sku)
		{
			return null;
		}

		$options['resetSku'] = true;

		return $this->prepareResponse($sku, $options);
	}

	private function loadSkuById(int $iblockId, int $skuId): ?BaseSku
	{
		$skuRepository = ServiceContainer::getSkuRepository($iblockId);
		if (!$skuRepository)
		{
			return null;
		}

		return $skuRepository->getEntityById($skuId);
	}

	/**
	 * @param int $iblockId
	 * @param int $productId
	 * @return \Bitrix\Catalog\v2\BaseEntity|\Bitrix\Catalog\v2\Sku\BaseSku|null
	 */
	private function loadFirstSkuForProduct(int $iblockId, int $productId): ?BaseSku
	{
		$productRepository = ServiceContainer::getProductRepository($iblockId);
		if (!$productRepository)
		{
			return null;
		}

		/** @var BaseProduct $product */
		$product = $productRepository->getEntityById($productId);
		if (!$product)
		{
			return null;
		}

		return $product->getSkuCollection()->getFirst();
	}

	private function prepareResponse(BaseSku $sku, array $options = []): ?array
	{
		$builder = new BasketBuilder();
		$basketItem = $builder->createItem();
		$basketItem->setSku($sku);
		if ($options['priceId'] && (int)$options['priceId'] > 0)
		{
			$basketItem->setPriceGroupId((int)$options['priceId']);
		}

		if ($options['urlBuilder'])
		{
			$basketItem->setDetailUrlManagerType($options['urlBuilder']);
		}

		$formFields = $basketItem->getFields();

		$price = null;
		$currency = '';
		if ($basketItem->getPriceItem() && $basketItem->getPriceItem()->hasPrice())
		{
			$price = $basketItem->getPriceItem()->getPrice();
			$currency = $basketItem->getPriceItem()->getCurrency();
		}

		$fields = [
			'TYPE' => $sku->getType(),
			'ID' => $formFields['skuId'],
			'SKU_ID' => $formFields['skuId'],
			'PRODUCT_ID' => $formFields['productId'],
			'NAME' => $formFields['name'],
			'MEASURE_CODE' => (string)$formFields['measureCode'],
			'MEASURE_RATIO' => $formFields['measureRatio'],
			'MEASURE_NAME' => $formFields['measureName'],
			'PRICE' => $price,
			'CURRENCY_ID' => $currency,
			'PROPERTIES' => $formFields['properties'],
			'VAT_ID' => $formFields['taxId'],
			'VAT_INCLUDED' => $formFields['taxIncluded'],
		];

		$previewImage = $sku->getFrontImageCollection()->getFrontImage();
		if ($previewImage)
		{
			$fields['PREVIEW_PICTURE'] = [
				'ID' => $previewImage->getId(),
				'SRC' => Tools::getImageSrc($previewImage->getFileStructure(), true),
				'WIDTH' => $previewImage->getField('WIDTH'),
				'HEIGHT' => $previewImage->getField('HEIGHT'),
			];
		}

		$formResult = $basketItem->getResult();
		$response = [
			'skuId' => $formFields['skuId'],
			'productId' => $formFields['productId'],
			'image' => $formResult['image'],
			'detailUrl' => $formResult['detailUrl'],
		];

		if (isset($options['resetSku']))
		{
			$response['skuTree'] = $this->loadSkuTree($sku);
		}
		else
		{
			unset($response['skuTree']);
		}

		$response['fields'] = $fields;
		$response['formFields'] = $formFields;

		return $response;
	}

	public function createProductAction(array $fields): ?array
	{
		global $USER;
		$iblockId = (int)$fields['IBLOCK_ID'];
		if (
			!\CIBlockSectionRights::UserHasRightTo($iblockId, 0, 'section_element_bind')
			|| !$USER->CanDoOperation('catalog_price')
		)
		{
			$this->addError(new Error("User has no permissions to create product"));

			return null;
		}

		$productFactory = ServiceContainer::getProductFactory($iblockId);
		if (!$productFactory)
		{
			$this->addError(new Error("Wrong catalog id {$iblockId}"));

			return null;
		}
		$product = $productFactory->createEntity();

		if (empty($fields['CODE']))
		{
			$productName = $fields['NAME'] ?? '';

			if ($productName !== '')
			{
				$fields['CODE'] = (new \CIBlockElement())->generateMnemonicCode($productName, $iblockId);
			}
		}

		$product->setType(ProductTable::TYPE_PRODUCT);

		$product->setFields($fields);

		if (isset($fields['PRICE']) && $fields['PRICE'] >= 0)
		{
			$basePrice = [
				'PRICE' => (float)$fields['PRICE'],
			];

			if (isset($fields['CURRENCY']))
			{
				$basePrice['CURRENCY'] = $fields['CURRENCY'];
			}

			$sku = $product->getSkuCollection()->getFirst();
			if ($sku)
			{
				$sku
					->getPriceCollection()
					->setValues([
						'BASE' => $basePrice
					])
				;
			}
		}

		$result = $product->save();

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return [
			'id' => $product->getId(),
		];
	}

	public function updateProductAction(int $id, int $iblockId, array $updateFields): ?array
	{
		if (empty($updateFields))
		{
			return null;
		}

		global $USER;
		if (
			!\CIBlockElementRights::UserHasRightTo($iblockId, $id, 'element_edit')
			|| !\CIBlockElementRights::UserHasRightTo($iblockId, $id, 'element_edit_price')
			|| !$USER->CanDoOperation('catalog_price')
		)
		{
			$this->addError(new Error("User has no permissions to update product"));

			return null;
		}

		$productRepository = ServiceContainer::getProductRepository($iblockId);
		if (!$productRepository)
		{
			$this->addError(new Error("Wrong catalog id {$iblockId}"));

			return null;
		}

		$product = $productRepository->getEntityById($id);
		if (!$product)
		{
			$this->addError(new Error("Product is not exists"));

			return null;
		}

		$result = $this->saveProduct($product, $updateFields);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return [
			'id' => $product->getId(),
		];
	}

	public function saveMorePhotoAction(int $productId, int $variationId, int $iblockId, array $imageValues): ?array
	{
		global $USER;
		if (
			!\CIBlockElementRights::UserHasRightTo($iblockId, $productId, 'element_edit')
			|| !\CIBlockElementRights::UserHasRightTo($iblockId, $productId, 'element_edit_price')
			|| !$USER->CanDoOperation('catalog_price')
		)
		{
			$this->addError(new Error("User has no permissions to update product"));

			return null;
		}

		$productRepository = ServiceContainer::getProductRepository($iblockId);
		if (!$productRepository)
		{
			$this->addError(new Error("Wrong catalog id {$iblockId}"));

			return null;
		}

		/** @var BaseProduct $product */
		$product = $productRepository->getEntityById($productId);
		if (!$product)
		{
			$this->addError(new Error("Product is not exists"));

			return null;
		}

		// use the head product - in case when a simple product was saved but it became sku product
		/** @var BaseIblockElementEntity $entity */
		if ($productId === $variationId)
		{
			$entity = $product;
		}
		else
		{
			$entity = $product->getSkuCollection()->findById($variationId);
		}

		if (!$entity)
		{
			$this->addError(new Error("Variation is not exists"));

			return null;
		}

		$values = [];

		$property = $entity->getPropertyCollection()->findByCode(MorePhotoImage::CODE);
		foreach ($imageValues as $key => $newImage)
		{
			$newImage = $this->prepareMorePhotoValue($newImage);
			if (empty($newImage))
			{
				continue;
			}

			if (!$property)
			{
				$detailPicture = $newImage;
				break;
			}

			if ($key === DetailImage::CODE)
			{
				$detailPicture = $newImage;
			}
			elseif ($key === PreviewImage::CODE)
			{
				$previewPicture = $newImage;
			}
			else
			{
				$values[$key] = $newImage;
			}
		}

		$entity->getImageCollection()->setValues($values);

		if (!empty($detailPicture))
		{
			$entity->getImageCollection()->getDetailImage()->setFileStructure($detailPicture);
		}

		if (!empty($previewPicture))
		{
			$entity->getImageCollection()->getPreviewImage()->setFileStructure($previewPicture);
		}

		$result = $product->save();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return [];
		}

		$productImageField = new ImageInput($entity);

		return $productImageField->getFormattedField();
	}

	private function prepareMorePhotoValue($imageValue)
	{
		if (empty($imageValue))
		{
			return null;
		}

		if (is_string($imageValue))
		{
			try
			{
				static $signer = null;
				if ($signer === null)
				{
					$signer = new Signer;
				}

				return (int)$signer->unsign($imageValue, ImageInput::FILE_ID_SALT);
			}
			catch (BadSignatureException $e)
			{
				return null;
			}
		}

		if (
			is_array($imageValue)
			&& !empty($imageValue['data'])
			&& is_array($imageValue['data'])
		)
		{
			return \CIBlock::makeFileArray($imageValue['data']);
		}

		return null;
	}

	private function saveProduct(BaseProduct $product, array $fields = []): Result
	{
		if ($product->isNew() && empty($fields['CODE']))
		{
			$productName = $fields['NAME'] ?? '';

			if ($productName !== '')
			{
				$fields['CODE'] = $this->prepareProductCode($productName);
			}
		}

		/** @var BaseSku $sku */
		$sku = $product->getSkuCollection()->getFirst();

		if (!empty($fields['MEASURE_CODE']))
		{
			$fields['MEASURE'] = $this->getMeasureIdByCode($fields['MEASURE_CODE']);
		}

		$sku->setFields($fields);

		if (!empty($fields['PRICES']) && is_array($fields['PRICES']))
		{
			$priceCollection = $sku->getPriceCollection();
			foreach ($fields['PRICES'] as $groupId => $price)
			{
				$priceCollection->setValues([
					$groupId => [
						'PRICE' => (float)$price['PRICE'],
						'CURRENCY' => $price['CURRENCY'] ?? null,
					],
				]);
			}
		}

		return $product->save();
	}

	private function getMeasureIdByCode(string $code): ?int
	{
		$measure = MeasureTable::getRow([
			'select' => ['ID'],
			'filter' => ['=CODE' => $code],
			'limit' => 1,
		]);
		if ($measure)
		{
			return (int) $measure['ID'];
		}

		return null;
	}

	private function getMeasureCodeById(string $id): ?string
	{
		$measure = MeasureTable::getRow([
			'select' => ['CODE'],
			'filter' => ['=ID' => $id],
			'limit' => 1,
		]);

		return $measure['CODE'] ?? null;
	}

	private function prepareProductCode($name): string
	{
		return mb_strtolower(\CUtil::translit(
				$name,
				LANGUAGE_ID,
				[
					'replace_space' => '_',
					'replace_other' => '',
				]
			)).'_'.random_int(0, 1000);
	}

	private function loadSkuTree(BaseSku $sku): array
	{
		if ($sku->isSimple())
		{
			return [];
		}

		$parentProduct = $sku->getParent();
		if ($parentProduct)
		{
			/** @var \Bitrix\Catalog\Component\SkuTree $skuTree */
			$skuTree = ServiceContainer::make('sku.tree', [
				'iblockId' => $parentProduct->getIblockId(),
			]);
			if ($skuTree)
			{
				$productId = $parentProduct->getId();
				$skuId = $sku->getId();

				$offers = $skuTree->loadWithSelectedOffers([
					$productId => $skuId,
				]);

				return $offers[$productId][$skuId] ?? [];
			}
		}

		return [];
	}

	public function getEmptyInputImageAction(int $iblockId): ?array
	{
		$productFactory = ServiceContainer::getProductFactory($iblockId);
		if (!$productFactory)
		{
			$this->addError(new Error("Wrong catalog id {$iblockId}"));

			return null;
		}

		$imageField = new ImageInput();

		return $imageField->getFormattedField();
	}

	public function getFileInputAction($iblockId): ?Response\Component
	{
		$productFactory = ServiceContainer::getProductFactory($iblockId);
		if (!$productFactory)
		{
			$this->addError(new Error("Wrong catalog id {$iblockId}"));

			return null;
		}
		$product = $productFactory->createEntity();
		$imageField = new ImageInput($product);
		$imageField->disableAutoSaving();

		return $imageField->getComponentResponse();
	}
}
