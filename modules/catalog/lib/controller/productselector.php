<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Config\Option;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Component\ImageInput;
use Bitrix\Catalog\MeasureTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreBarcodeTable;
use Bitrix\Catalog\UI\PropertyProduct;
use Bitrix\Catalog\v2\Barcode\Barcode;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Image\DetailImage;
use Bitrix\Catalog\v2\Image\MorePhotoImage;
use Bitrix\Catalog\v2\Image\PreviewImage;
use Bitrix\Catalog\v2\Integration\JS\ProductForm\BasketBuilder;
use Bitrix\Catalog\UI\FileUploader\ProductController;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\FileUploader\PendingFile;
use Bitrix\UI\FileUploader\PendingFileCollection;
use Bitrix\UI\FileUploader\Uploader;

class ProductSelector extends JsonController
{
	private ?Uploader $uploader = null;
	private ?PendingFileCollection $pendingFileCollection = null;
	private AccessController $accessController;

	/**
	 * @inheritDoc
	 */
	protected function init()
	{
		parent::init();

		$this->accessController = AccessController::getCurrent();
	}

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
		if ($action->getName() === 'getSkuTreeProperties')
		{
			return true;
		}

		if (
			$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			|| $this->accessController->check(ActionDictionary::ACTION_CATALOG_VIEW)
		)
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

	public function getProductIdByBarcodeAction($barcode): ?int
	{
		$iterator = \CIBlockElement::GetList(
			[],
			[
				'=PRODUCT_BARCODE' => $barcode,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			],
			false,
			false,
			['ID']
		);

		if ($product = $iterator->Fetch())
		{
			return (int)$product['ID'];
		}

		return null;
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

		return $product->getSkuCollection()->getFirst([$this, 'isActiveSku']);
	}

	/**
	 * Filter for select first active offer.
	 *
	 * @param BaseSku $sku
	 * @return bool
	 */
	public function isActiveSku(BaseSku $sku): bool
	{
		return $sku->isActive();
	}

	private function prepareResponse(BaseSku $sku, array $options = []): ?array
	{
		$builder = new BasketBuilder();
		$basketItem = $builder->createItem();
		$basketItem->setSku($sku);

		$priceId = (int)($options['priceId'] ?? 0);
		if ($priceId > 0)
		{
			$basketItem->setPriceGroupId($priceId);
		}

		if (!empty($options['urlBuilder']))
		{
			$basketItem->setDetailUrlManagerType($options['urlBuilder']);
		}

		$formFields = $basketItem->getFields();

		$price = null;
		$basePrice = null;
		$currency = '';
		$isCustomized = 'N';
		if ($basketItem->getPriceItem() && $basketItem->getPriceItem()->hasPrice())
		{
			$basePrice = $basketItem->getPriceItem()->getPrice();
			$price = $basketItem->getPriceItem()->getPrice();
			$currency = $basketItem->getPriceItem()->getCurrency();
			if (!empty($options['currency']) && $options['currency'] !== $currency)
			{
				$basePrice = \CCurrencyRates::ConvertCurrency($price, $currency, $options['currency']);
				$currencyFormat = \CCurrencyLang::GetCurrencyFormat($options['currency']);
				$decimals = $currencyFormat['DECIMALS'] ?? 2;
				$basePrice = round($basePrice, $decimals);
				$price = \CCurrencyLang::CurrencyFormat($basePrice, $options['currency'], false);
				$isCustomized = 'Y';
				$currency = $options['currency'];
			}
		}

		/** @var Barcode $barcode */
		$barcode = $sku->getBarcodeCollection()->getFirst();

		$purchasingPrice = $sku->getField('PURCHASING_PRICE');
		$purchasingCurrency = $sku->getField('PURCHASING_CURRENCY');
		if ($purchasingCurrency !== $options['currency'])
		{
			$purchasingPrice = \CCurrencyRates::ConvertCurrency(
				$purchasingPrice,
				$purchasingCurrency,
				$options['currency']
			);
			$purchasingCurrency = $options['currency'];
		}

		$productProps = $this->getProductProperties($sku);

		$fields = [
			'TYPE' => $sku->getType(),
			'ID' => $formFields['skuId'],
			'SKU_ID' => $formFields['skuId'],
			'PRODUCT_ID' => $formFields['productId'],
			'CUSTOMIZED' => $isCustomized,
			'NAME' => $formFields['name'],
			'MEASURE_CODE' => (string)$formFields['measureCode'],
			'MEASURE_RATIO' => $formFields['measureRatio'],
			'MEASURE_NAME' => $formFields['measureName'],
			'PURCHASING_PRICE' => $purchasingPrice,
			'PURCHASING_CURRENCY' => $purchasingCurrency,
			'BARCODE' => $barcode ? $barcode->getBarcode() : '',
			'COMMON_STORE_AMOUNT' => $sku->getField('QUANTITY'),
			'COMMON_STORE_RESERVED' => $sku->getField('QUANTITY_RESERVED'),
			'PRICE' => $price,
			'BASE_PRICE' => $basePrice,
			'CURRENCY_ID' => $currency,
			'PROPERTIES' => $formFields['properties'],
			'VAT_ID' => $formFields['taxId'],
			'VAT_INCLUDED' => $formFields['taxIncluded'],
			'TAX_RATE' => $formFields['taxRate'],
			'TAX_RATE_FORMATTED' => $this->formatTaxRate($formFields['taxRate']),
			'TAX_INCLUDED' => $formFields['taxIncluded'],
			'TAX_INCLUDED_FORMATTED' => $this->formatTaxIncluded($formFields['taxIncluded']),
			'BRANDS' => $this->getProductBrand($sku),
			'WEIGHT' => $formFields['weight'],
			'DIMENSIONS' => $formFields['dimensions'],
			'PRODUCT_PROPERTIES' => $productProps,
		];

		$fields = array_merge($fields, $productProps);

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

		$fields['DETAIL_URL'] = $formResult['detailUrl'];
		$fields['IMAGE_INFO'] = $formResult['image'];
		$fields['SKU_TREE'] = $formResult['skuTree'];
		if (isset($options['resetSku']))
		{
			$response['skuTree'] =
				($formResult['skuTree'] !== '')
					? Json::decode($formResult['skuTree'])
					: ''
			;
		}

		$response['fields'] = $fields;
		$response['formFields'] = $formFields;

		return $response;
	}

	private function formatTaxRate(?float $rate): string
	{
		if ($rate === null)
		{
			return Loc::getMessage('PRODUCT_SELECTOR_PRODUCT_NOT_TAX');
		}

		return $rate . ' %';
	}

	private function formatTaxIncluded(string $taxIncluded): string
	{
		return ($taxIncluded === 'Y')
			? Loc::getMessage('PRODUCT_SELECTOR_PRODUCT_TAX_INCLUDED')
			: Loc::getMessage('PRODUCT_SELECTOR_PRODUCT_TAX_NOT_INCLUDED');
	}

	private function getProductIdByBarcode(string $barcode): ?int
	{
		$barcodeRaw = StoreBarcodeTable::getList([
			'filter' => ['=BARCODE' => $barcode],
			'select' => ['PRODUCT_ID'],
			'limit' => 1
		]);

		if ($barcode = $barcodeRaw->fetch())
		{
			return $barcode['PRODUCT_ID'];
		}

		return null;
	}

	private function getProductBrand($sku): ?array
	{
		$product = $sku->getParent();
		if (!$product)
		{
			return null;
		}

		$brand = $product->getPropertyCollection()->findByCode('BRAND_FOR_FACEBOOK');
		if (!$brand)
		{
			return null;
		}

		$userType = \CIBlockProperty::GetUserType($brand->getUserType());
		$userTypeMethod = $userType['GetUIEntityEditorProperty'];
		$propertySettings = $brand->getSettings();
		$propertyValues = $brand->getPropertyValueCollection()->getValues();
		$description = $userTypeMethod($propertySettings, $propertyValues);
		$propertyBrandItems = $description['data']['items'];

		$selectedBrandItems = [];

		foreach ($propertyBrandItems as $propertyBrandItem)
		{
			if (in_array($propertyBrandItem['VALUE'], $propertyValues, true))
			{
				$selectedBrandItems[] = $propertyBrandItem;
			}
		}

		return $selectedBrandItems;
	}

	private function getProductProperties(BaseSku $sku): array
	{
		$emptyProps = [];
		$columns = PropertyProduct::getColumnNames();
		foreach ($columns as $columnName)
		{
			$emptyProps[$columnName] = '';
		}

		$productProps = [];
		$parent = $sku->getParent();
		if ($parent)
		{
			$productId = $parent->getId();
			$productIblockId = $sku->getIblockInfo()->getProductIblockId();
			if ($productId && $productIblockId)
			{
				$productProps = PropertyProduct::getIblockProperties($productIblockId, $productId);
			}
		}
		unset($parent);

		$skuProps = [];
		$skuId = $sku->getId();
		$skuIblockId = $sku->getIblockId();
		if ($skuId && $skuIblockId)
		{
			$skuProps = PropertyProduct::getSkuProperties($skuIblockId, $skuId);
		}

		return array_merge($emptyProps, $productProps, $skuProps);
	}

	public function createProductAction(array $fields): ?array
	{
		$iblockId = (int)$fields['IBLOCK_ID'];
		if (
			!\CIBlockSectionRights::UserHasRightTo($iblockId, 0, 'section_element_bind')
			|| !$this->accessController->check(ActionDictionary::ACTION_PRODUCT_ADD)
		)
		{
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_NO_PERMISSIONS_FOR_CREATION')));

			return null;
		}

		$productFactory = ServiceContainer::getProductFactory($iblockId);
		if (!$productFactory)
		{
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_WRONG_IBLOCK_ID')));

			return null;
		}

		$skuRepository = ServiceContainer::getSkuRepository($iblockId);
		$type = $skuRepository ? ProductTable::TYPE_SKU : ProductTable::TYPE_PRODUCT;

		/** @var BaseProduct $product */
		$product = $productFactory
			->createEntity()
			->setType($type)
		;

		$sku =
			$skuRepository
				? $product->getSkuCollection()->create()
				:  $product->getSkuCollection()->getFirst()
		;

		if (!empty($fields['BARCODE']))
		{
			$productId = $this->getProductIdByBarcode($fields['BARCODE']);

			if ($productId !== null)
			{
				$elementRaw = ElementTable::getList([
					'filter' => ['=ID' => $productId],
					'select' => ['NAME'],
					'limit' => 1
				]);

				$name = '';
				if ($element = $elementRaw->fetch())
				{
					$name = $element['NAME'];
				}

				$this->addError(
					new Error(
						Loc::getMessage(
							'PRODUCT_SELECTOR_ERROR_BARCODE_EXIST',
							[
								'#BARCODE#' => htmlspecialcharsbx($fields['BARCODE']),
								'#PRODUCT_NAME#' => htmlspecialcharsbx($name),
							]
						)
					)
				);

				return null;
			}

			if ($sku)
			{
				$sku->getBarcodeCollection()->setSimpleBarcodeValue($fields['BARCODE']);
			}
		}

		if (empty($fields['CODE']))
		{
			$productName = $fields['NAME'] ?? '';

			if ($productName !== '')
			{
				$fields['CODE'] = (new \CIBlockElement())->generateMnemonicCode($productName, $iblockId);
			}
		}

		if (isset($fields['CODE']) && \CIBlock::isUniqueElementCode($iblockId))
		{
			$elementRaw = ElementTable::getList([
				'filter' => ['=CODE' => $fields['CODE']],
				'select' => ['ID'],
				'limit' => 1
			]);

			if ($elementRaw->fetch())
			{
				$fields['CODE'] = uniqid($fields['CODE'] . '_', false);
			}
		}

		if (!empty($fields['MEASURE_CODE']))
		{
			$fields['MEASURE'] = $this->getMeasureIdByCode($fields['MEASURE_CODE']);
		}
		else
		{
			$measure = MeasureTable::getRow([
				'select' => ['ID'],
				'filter' => ['=IS_DEFAULT' => 'Y'],
			]);
			if ($measure)
			{
				$fields['MEASURE'] = $measure['ID'];
			}
		}

		if (!$this->accessController->check(ActionDictionary::ACTION_PRICE_EDIT))
		{
			unset($fields['PRICE']);
		}

		$product->setFields($fields);
		if ($fields['MEASURE'] > 0)
		{
			$sku->setField('MEASURE', $fields['MEASURE']);
		}
		if (Option::get('catalog', 'default_product_vat_included') === 'Y')
		{
			$sku->setField('VAT_INCLUDED', ProductTable::STATUS_YES);
		}

		if (isset($fields['PRICE']) && $fields['PRICE'] >= 0)
		{
			$basePrice = [
				'PRICE' => (float)$fields['PRICE'],
			];

			if (isset($fields['CURRENCY']))
			{
				$basePrice['CURRENCY'] = $fields['CURRENCY'];
			}

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
			'id' => $sku->getId(),
		];
	}

	public function updateSkuAction(int $id, array $updateFields, array $oldFields = []): ?array
	{
		if (empty($updateFields) || $id <= 0)
		{
			return null;
		}

		$repositoryFacade = ServiceContainer::getRepositoryFacade();
		if (!$repositoryFacade)
		{
			return null;
		}

		$sku = $repositoryFacade->loadVariation($id);
		if (!$sku)
		{
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_SKU_NOT_EXIST')));
			return null;
		}
		/** @var BaseProduct $parentProduct */
		$parentProduct = $sku->getParent();

		if (
			!$this->accessController->check(ActionDictionary::ACTION_PRODUCT_EDIT)
			|| !\CIBlockElementRights::UserHasRightTo($parentProduct->getIblockId(), $parentProduct->getId(), 'element_edit')
		)
		{
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_NO_PERMISSIONS_FOR_UPDATE')));

			return null;
		}

		if (
			!$this->accessController->check(ActionDictionary::ACTION_PRICE_EDIT)
			|| !\CIBlockElementRights::UserHasRightTo($parentProduct->getIblockId(), $parentProduct->getId(), 'element_edit_price')
		)
		{
			unset($updateFields['PRICES']);
		}

		$result = $this->saveSku($sku, $updateFields, $oldFields);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return [
			'id' => $sku->getId(),
		];
	}

	public function updateProductAction(int $id, int $iblockId, array $updateFields): ?array
	{
		return $this->updateSkuAction($id, $updateFields);
	}

	public function saveMorePhotoAction(int $productId, int $variationId, int $iblockId, array $imageValues): ?array
	{
		if (
			!$this->accessController->check(ActionDictionary::ACTION_PRODUCT_EDIT)
			|| !\CIBlockElementRights::UserHasRightTo($iblockId, $productId, 'element_edit')
		)
		{
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_NO_PERMISSIONS_FOR_UPDATE')));

			return null;
		}

		$productRepository = ServiceContainer::getProductRepository($iblockId);
		if (!$productRepository)
		{
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_WRONG_IBLOCK_ID')));

			return null;
		}

		/** @var BaseProduct $product */
		$product = $productRepository->getEntityById($productId);
		if (!$product)
		{
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_PRODUCT_NOT_EXIST')));

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
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_SKU_NOT_EXIST')));

			return null;
		}

		$values = [];

		$property = $entity->getPropertyCollection()->findByCode(MorePhotoImage::CODE);
		foreach ($imageValues as $key => $newImage)
		{
			$newImage = $this->prepareMorePhotoValue($newImage, $entity);
			if (empty($newImage))
			{
				continue;
			}

			if (!$property || !$property->isActive())
			{
				if (empty($previewPicture))
				{
					$previewPicture = $newImage;
					continue;
				}

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

		if (isset($detailPicture) && is_array($detailPicture))
		{
			$entity->getImageCollection()->getDetailImage()->setFileStructure($detailPicture);
		}

		if (isset($previewPicture) && is_array($previewPicture))
		{
			$entity->getImageCollection()->getPreviewImage()->setFileStructure($previewPicture);
		}

		$result = $product->save();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return [];
		}

		$this->commitPendingCollection();

		return (new ImageInput($entity))->getFormattedField();
	}

	private function prepareMorePhotoValue($imageValue, BaseIblockElementEntity $entity)
	{
		if (empty($imageValue))
		{
			return null;
		}

		if (!empty($imageValue['token']))
		{
			return $this->prepareMorePhotoValueByToken($imageValue, $entity);
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

		if (
			is_array($imageValue)
			&& !empty($imageValue['base64Encoded'])
			&& is_array($imageValue['base64Encoded'])
		)
		{
			$content = (string)($imageValue['base64Encoded']['content'] ?? '');
			if ($content !== '')
			{
				$fileName = (string)($imageValue['base64Encoded']['filename'] ?? '');
				$fileInfo = \CRestUtil::saveFile($content, $fileName);

				return $fileInfo ?: null;
			}
		}

		return null;
	}

	private function prepareMorePhotoValueByToken(array $image, BaseIblockElementEntity $entity): ?array
	{
		$token = $image['token'] ?? null;
		if (empty($token))
		{
			return null;
		}

		$fileId = $this->getFileIdByToken($token, $entity);
		if ($fileId)
		{
			return \CIBlock::makeFileArray($fileId, false, null, ['allow_file_id' => true]);
		}

		return null;
	}

	private function getFileIdByToken(string $token, BaseIblockElementEntity $entity): ?int
	{
		$uploader = $this->getUploader($entity);
		$pendingFile = $uploader->getPendingFiles([$token])->get($token);

		if ($pendingFile && $pendingFile->isValid())
		{
			$this->addPendingFileToCollection($pendingFile);

			return $pendingFile->getFileId();
		}

		return null;
	}

	private function getUploader(BaseIblockElementEntity $entity): Uploader
	{
		if ($this->uploader === null)
		{
			$fileController = new ProductController([
				'productId' => $entity->getId(),
			]);

			$this->uploader = (new Uploader($fileController));
		}

		return $this->uploader;
	}

	private function addPendingFileToCollection(PendingFile $pendingFile): void
	{
		$this->getPendingFilesCollection()->add($pendingFile);
	}

	private function commitPendingCollection(): void
	{
		$this->getPendingFilesCollection()->makePersistent();
	}

	private function getPendingFilesCollection(): PendingFileCollection
	{
		if ($this->pendingFileCollection === null)
		{
			$this->pendingFileCollection = new PendingFileCollection();
		}

		return $this->pendingFileCollection;
	}

	private function saveSku(BaseSku $sku, array $fields = [], array $oldFields = []): Result
	{
		if ($sku->isNew() && empty($fields['CODE']))
		{
			$productName = $fields['NAME'] ?? '';

			if ($productName !== '')
			{
				$fields['CODE'] = $this->prepareProductCode($productName);
			}
		}

		if (!empty($fields['MEASURE_CODE']))
		{
			$fields['MEASURE'] = $this->getMeasureIdByCode($fields['MEASURE_CODE']);
		}

		$sectionId = $fields['IBLOCK_SECTION_ID'] ?? null;
		unset($fields['IBLOCK_SECTION_ID']);

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

		if (isset($fields['BARCODE']))
		{
			$skuId = $this->getProductIdByBarcode($fields['BARCODE']);
			if ($skuId !== null && $sku->getId() !== $skuId)
			{
				$result = new Result();

				$elementRaw = ElementTable::getList([
					'filter' => ['=ID' => $skuId],
					'select' => ['NAME'],
					'limit' => 1
				]);

				$name = '';
				if ($element = $elementRaw->fetch())
				{
					$name = $element['NAME'];
				}

				$result->addError(
					new Error(
						Loc::getMessage(
							'PRODUCT_SELECTOR_ERROR_BARCODE_EXIST',
							[
								'#BARCODE#' => htmlspecialcharsbx($fields['BARCODE']),
								'#PRODUCT_NAME#' => htmlspecialcharsbx($name),
							]
						)
					)
				);

				return $result;
			}

			$updateBarcodeItem = null;
			$barcodeCollection = $sku->getBarcodeCollection();
			if (isset($oldFields['BARCODE']))
			{
				$updateBarcodeItem = $barcodeCollection->getItemByBarcode($oldFields['BARCODE']);
			}

			if ($updateBarcodeItem)
			{
				if (empty($fields['BARCODE']))
				{
					$barcodeCollection->remove($updateBarcodeItem);
				}
				else
				{
					$updateBarcodeItem->setBarcode($fields['BARCODE']);
				}
			}
			else
			{
				$barcodeItem =
					$barcodeCollection
						->create()
						->setBarcode($fields['BARCODE'])
				;

				$barcodeCollection->add($barcodeItem);
			}
		}

		/** @var BaseProduct $parentProduct */
		$parentProduct = $sku->getParent();

		if (isset($fields['BRANDS']) && is_array($fields['BRANDS']))
		{
			$parentProduct->getPropertyCollection()->setValues(['BRAND_FOR_FACEBOOK' => $fields['BRANDS']]);
		}

		if (isset($sectionId))
		{
			$parentProduct->setField('IBLOCK_SECTION_ID', $sectionId);
		}

		if (
			isset($fields['NAME'])
			&& $parentProduct->getSkuCollection()->count() === 1
		)
		{
			$this->changeProductName($parentProduct, $fields['NAME']);
		}

		return $parentProduct->save();
	}

	private function changeProductName(BaseProduct $parentProduct, string $newName): void
	{
		$skuTreeEntity = ServiceContainer::make('sku.tree', [
			'iblockId' => $parentProduct->getIblockId(),
		]);
		$skuTree = $skuTreeEntity->load([$parentProduct->getId()]);
		if (empty($skuTree))
		{
			$parentProduct->setField('NAME', $newName);

			return;
		}

		$skuTreeElement = reset($skuTree);
		$existingValues = $skuTreeElement['EXISTING_VALUES'] ?? null;
		if (!$existingValues)
		{
			$parentProduct->setField('NAME', $newName);

			return;
		}

		$hasFilledProperty = false;
		foreach ($existingValues as $existingValue)
		{
			$hasFilledProperty = $existingValue[0] !== 0;
			if ($hasFilledProperty)
			{
				break;
			}
		}
		if (!$hasFilledProperty)
		{
			$parentProduct->setField('NAME', $newName);
		}
	}

	private function getMeasureIdByCode(string $code): ?int
	{
		$measure = MeasureTable::getRow([
			'select' => ['ID'],
			'filter' => ['=CODE' => $code],
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

	public function getEmptyInputImageAction(int $iblockId): ?array
	{
		$productFactory = ServiceContainer::getProductFactory($iblockId);
		if (!$productFactory)
		{
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_WRONG_IBLOCK_ID')));

			return null;
		}

		$imageField = new ImageInput();

		return $imageField->getFormattedField();
	}

	public function getFileInputAction(int $iblockId, int $skuId = null): ?Response\Component
	{
		$productFactory = ServiceContainer::getProductFactory($iblockId);
		if (!$productFactory)
		{
			$this->addError(new Error(Loc::getMessage('PRODUCT_SELECTOR_ERROR_WRONG_IBLOCK_ID')));

			return null;
		}

		$repositoryFacade = ServiceContainer::getRepositoryFacade();

		$sku = null;
		if ($repositoryFacade && $skuId !== null)
		{
			$sku = $repositoryFacade->loadVariation($skuId);
		}

		if ($sku === null)
		{
			$sku = $productFactory->createEntity();
		}

		$imageField = new ImageInput($sku);
		$imageField->disableAutoSaving();

		return $imageField->getComponentResponse();
	}

	public function getSkuTreePropertiesAction(int $iblockId): array
	{
		$skuTree = ServiceContainer::make('sku.tree', [
			'iblockId' => $iblockId,
		]);

		if ($skuTree)
		{
			return $skuTree->getTreeProperties();
		}

		return [];
	}

	public function getSkuSelectorItemAction(int $id, array $options): ?array
	{
		if (!Loader::includeModule('ui'))
		{
			return null;
		}

		$items = [
			['product', $id]
		];
		$dialogOptions = [
			[
				'id' => 'product',
				'options' => $options,
			],
		];
		$selectedItems = Dialog::getSelectedItems($items, $dialogOptions)->toArray();
		if (!isset($selectedItems[0]))
		{
			return null;
		}

		$item = $selectedItems[0];
		if (($item['hidden'] ?? null) === true)
		{
			return null;
		}

		return $item;
	}

	public function isInstalledMobileAppAction(): bool
	{
		return (bool)\CUserOptions::GetOption('mobile', 'iOsLastActivityDate')
			|| (bool)\CUserOptions::GetOption('mobile', 'AndroidLastActivityDate')
		;
	}
}
