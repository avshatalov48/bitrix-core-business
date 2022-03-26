<?php

use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\UI\FileInput;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogImageInput extends \CBitrixComponent implements Errorable
{
	use ErrorableImplementation;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	protected function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}

	public function onPrepareComponentParams($params): array
	{
		$params['ENABLE_AUTO_SAVING'] = $params['ENABLE_AUTO_SAVING'] ?? true;
		$params['FILE_VALUES'] = $params['FILE_VALUES'] ?? [];
		$params['FILE_SETTINGS'] = $params['FILE_SETTINGS'] ?? [];

		return parent::onPrepareComponentParams($params);
	}

	public function executeComponent(): void
	{
		if ($this->hasErrors())
		{
			$this->showErrors();

			return;
		}

		$entity = $this->arParams['PRODUCT_ENTITY'];

		$this->arResult['JS_PARAMS'] = ['saveable' => false];

		if ($entity instanceof \Bitrix\Catalog\v2\Product\BaseProduct)
		{
			$this->arResult['JS_PARAMS'] = [
				'saveable' => $this->arParams['ENABLE_AUTO_SAVING'],
				'iblockId' => $entity->getIblockId(),
				'productId' => $entity->getId(),
				'skuId' => $entity->getId(),
			];
		}
		elseif ($entity instanceof \Bitrix\Catalog\v2\Sku\BaseSku)
		{
			$product = $entity->getParent();
			if ($product instanceof \Bitrix\Catalog\v2\BaseIblockElementEntity)
			{
				$this->arResult['JS_PARAMS'] = [
					'saveable' => $this->arParams['ENABLE_AUTO_SAVING'],
					'iblockId' => $product->getIblockId(),
					'productId' => $product->getId(),
					'skuId' => $entity->getId(),
				];
			}
		}

		$this->arResult['JS_PARAMS']['inputId'] = $this->arParams['INPUT_ID'] ?? '';
		$this->arResult['JS_PARAMS']['values'] = $this->arParams['FILE_SIGNED_VALUES'] ?? [];
		if (isset($this->arParams['FILE_SETTINGS']['maxCount']) && $this->arParams['FILE_SETTINGS']['maxCount'] <= 1)
		{
			$this->arResult['JS_PARAMS']['hideAddButton'] = true;
		}

		$uiKeys = ['FILE_VALUES', 'FILE_SETTINGS', 'LOADER_PREVIEW', 'DISABLED'];
		$this->arResult['UI_PARAMS'] = array_intersect_key($this->arParams, array_flip($uiKeys));
		$this->arResult['BLOCK_ID'] = uniqid('catalog_image_editor_', false);

		$this->includeComponentTemplate();
	}
}