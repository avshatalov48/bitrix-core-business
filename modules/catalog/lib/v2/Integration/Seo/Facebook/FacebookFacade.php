<?php

namespace Bitrix\Catalog\v2\Integration\Seo\Facebook;

use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\Catalog\Catalog;
use Bitrix\Seo\Catalog\Service;
use Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductQueueTable;

class FacebookFacade
{
	/** @var \Bitrix\Catalog\v2\Integration\Seo\Facebook\FacebookProductProcessor */
	private $processor;
	/** @var \Bitrix\Catalog\v2\Integration\Seo\Facebook\FacebookProductRepository */
	private $exportedProductRepository;

	public function __construct(
		FacebookProductProcessor $processor,
		FacebookProductRepository $facebookProductRepository
	)
	{
		$this->processor = $processor;
		$this->exportedProductRepository = $facebookProductRepository;

		if ($this->isExportAvailable() && $this->hasAuth())
		{
			FacebookAgent::registerCatalogFacebookAgent();
			\Bitrix\Catalog\v2\Integration\Iblock\BrandProperty::createFacebookBrandProperty();
		}
		else
		{
			FacebookAgent::unregisterCatalogFacebookAgent();
		}
	}

	public function refreshExportedProducts(array $ids): Result
	{
		$collection = $this->exportedProductRepository->loadCollection($ids);
		$exportedProductsIds = $collection->getProductIdList();

		if (!empty($exportedProductsIds))
		{
			return $this->exportProductsByIds($exportedProductsIds);
		}

		return new Result();
	}

	private function getProductEntitiesByIds(array $ids): array
	{
		if (empty($ids))
		{
			return [];
		}

		$skus = [];
		foreach ($ids as $id)
		{
			$sku = ServiceContainer::getRepositoryFacade()->loadVariation($id);
			if (!$sku)
			{
				continue;
			}

			$skus[] = $sku;
		}

		return $skus;
	}

	public function exportProductsByIds(array $ids): Result
	{
		$result = $this->checkRequirements();
		if (!$result->isSuccess())
		{
			return $result;
		}

		$skus = $this->getProductEntitiesByIds($ids);

		$result = $this->validateProducts($skus);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$preparedSku = $this->prepareProducts($skus);
		$response =
			$this
				->getCatalog()
				->batchCatalogProducts($preparedSku);

		$result = new Result();
		if ($response->isSuccess())
		{
			$responseData = $response->getData();
			$queueId = $responseData['queue_ids'][0] ?? null;
			$validationStatus = $responseData['validation_status'] ?? null;
			$productKeys = array_keys($preparedSku);

			if ($validationStatus)
			{
				$errorProducts = $this->parseValidationStatus($validationStatus);
				$result->setData(['ERROR_PRODUCTS' => $errorProducts]);
				$errorProductKeys = array_keys($errorProducts);
				$productKeys = array_diff($productKeys, $errorProductKeys);
				$this->exportedProductRepository->save($errorProducts);
			}

			if ($queueId)
			{
				\Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductQueueTable::add([
					'QUEUE_ID' => $queueId,
					'PRODUCT_IDS' => \Bitrix\Main\Web\Json::encode($productKeys),
				]);
				$result->setData(['QUEUE_ID' => $queueId]);
			}
		}
		else
		{
			$result->addErrors($response->getErrors());
		}

		return $result;
	}

	public function validateProducts($skus): Result
	{
		$result = new Result();

		foreach ($skus as $sku)
		{
			$res = $this->processor->validate($sku);
			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}

	private function prepareProducts($skus): array
	{
		$preparedProducts = [];

		foreach ($skus as $sku)
		{
			$result = $this->processor->prepare($sku);
			if ($result->isSuccess())
			{
				foreach ($result->getData() as $key => $value)
				{
					$preparedProducts[$key] = $value;
				}
			}
		}

		return $preparedProducts;
	}

	private function getCatalog(): Catalog
	{
		static $catalog = null;

		if ($catalog === null)
		{
			$service = Service::getInstance();
			$catalog = $service->getCatalog($service::TYPE_FACEBOOK);
		}

		return $catalog;
	}

	public function isExportAvailable(): bool
	{
		// ToDo
		// if (!ModuleManager::isModuleInstalled('bitrix24'))
		// {
		// 	return false;
		// }

		$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();
		if ($region === null || $region === 'ru')
		{
			return false;
		}

		if (Option::get('catalog', 'fb_product_export_enabled', 'N') !== 'Y')
		{
			return false;
		}

		if (!Loader::includeModule('seo'))
		{
			return false;
		}

		return true;
	}

	public function checkRequirements(): Result
	{
		if (!$this->isExportAvailable())
		{
			return (new Result())->addError(new Error('Catalog export feature is not available.'));
		}

		return $this->getAuth();
	}

	public function hasAuth(): bool
	{
		return $this->getAuth()->isSuccess();
	}

	public function getPageId(): ?string
	{
		if ($this->hasAuth())
		{
			return $this->getCatalog()->getPageId();
		}

		return null;
	}

	public function getCatalogId(): ?string
	{
		if ($this->hasAuth())
		{
			return $this->getCatalog()->getCatalogId();
		}

		return null;
	}

	private function getAuth(): Result
	{
		$result = new Result();

		if (!Loader::includeModule('seo'))
		{
			return $result->addError(new Error('The SEO module is not installed.'));
		}

		$service = Service::getInstance();

		if (!$service::getAuthAdapter($service::TYPE_FACEBOOK)->hasAuth())
		{
			return $result->addError(new Error('Facebook account with business suite is not authorized.'));
		}

		if (!$this->getCatalog())
		{
			return $result->addError(new Error('Facebook account is not authorized to use catalog features.'));
		}

		return $result;
	}

	private function parseValidationStatus(array $validationStatus): array
	{
		$parsedProductErrors = [];
		foreach ($validationStatus as $validationStatusElement)
		{
			$id = $this->processor->getProductIdByRetailerId($validationStatusElement['retailer_id']);
			$error = $validationStatusElement['errors'][0]['message'] ?? null;
			$parsedProductErrors[$id] = [
				'ID' => $id,
				'ERROR' => $error,
			];
		}

		return $parsedProductErrors;
	}

	public function getExportedProducts(array $productIds): array
	{
		return $this->exportedProductRepository->getProductsByIds($productIds);
	}

	private function getFacebookProductIds(array $productIds): array
	{
		$facebookProductIds = [];

		$preparedProductIds = [];
		foreach ($productIds as $productId)
		{
			$preparedProductIds[] = $this->processor->getEntityRetailerId($productId);
		}

		$response =
			$this
				->getCatalog()
				->getProductsInfo($preparedProductIds)
		;

		if (!$response->isSuccess())
		{
			return $facebookProductIds;
		}

		$productsInfo = $response->getData();
		foreach ($productsInfo as $productInfo)
		{
			$facebookProductIds[] = $productInfo['id'];
		}

		return $facebookProductIds;
	}

	private function processWebhook(int $queueId, array $errors): void
	{
		$queueData = ExportedProductQueueTable::getByPrimary($queueId)->fetch();
		if (!$queueData)
		{
			return;
		}
		ExportedProductQueueTable::delete($queueId);

		$preparedErrors = [];
		foreach ($errors as $error)
		{
			$preparedErrors[$this->processor->getProductIdByRetailerId($error['id'])] = $error['message'];
		}
		$productIds = \Bitrix\Main\Web\Json::decode($queueData['PRODUCT_IDS']);
		$preparedProducts = [];
		foreach ($productIds as $productId)
		{
			$preparedProducts[$productId] = [
				'ID' => $productId,
				'ERROR' => $preparedErrors[$productId],
			];
		}

		$this->exportedProductRepository->save($preparedProducts);

		$facebookProductIds = [];
		if (empty($preparedErrors))
		{
			$facebookProductIds = $this->getFacebookProductIds($productIds);
		}

		$event = new \Bitrix\Main\Event(
			'catalog',
			'onFacebookCompilationExportFinished',
			[
				'QUEUE_ID' => $queueId,
				'ERROR_PRODUCTS' => $preparedErrors,
				'FACEBOOK_PRODUCT_IDS' => $facebookProductIds,
			]
		);
		$event->send();
	}

	public static function onCatalogWebhookHandler($event): void
	{
		if (!Loader::includeModule('crm'))
		{
			return;
		}
		$crmCatalogIblockId = \CCrmCatalog::EnsureDefaultExists() ?: 0;
		$instance = ServiceContainer::get('integration.seo.facebook.facade', [
			'iblockId' => $crmCatalogIblockId,
		]);
		$queueId = $event->getParameter('payload')['id'];
		$errors = Json::decode($event->getParameter('payload')['errors']);
		$instance->processWebhook($queueId, $errors);
	}
}