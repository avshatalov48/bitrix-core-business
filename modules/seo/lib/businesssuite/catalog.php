<?php

namespace Bitrix\Seo\BusinessSuite;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Installs;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Setup;
use Bitrix\Seo\Retargeting\Response;

abstract class Catalog extends AbstractBase
{
	private function getSetup(): Setup
	{
		static $setup = null;

		if ($setup === null)
		{
			$setup = Setup::load();
		}

		return $setup;
	}

	private function getBusinessId(): ?string
	{
		$setup = $this->getSetup();

		return $setup->get($setup::BUSINESS_ID);
	}

	private function getInstalls(): Installs
	{
		static $installs = null;

		if ($installs === null)
		{
			$installs = Installs::load();
		}

		return $installs;
	}

	private function getCatalogId(): ?string
	{
		return $this->getInstalls()->getCatalog();
	}

	private function createResponseWithError(string $error): Response
	{
		return Response::create(self::TYPE_CODE)->addError(new Error($error));
	}

	private function sendRequest(string $name, array $data = []): Response
	{
		$businessId = $this->getBusinessId();
		if ($businessId === null)
		{
			return $this->createResponseWithError('Empty business id.');
		}

		return
			$this
				->getRequest()
				->send([
					'methodName' => $this->getMethodName($name),
					'parameters' => array_merge(
						$data,
						[
							'fbe_external_business_id' => $businessId,
						]
					),
				])
			;
	}

	public function batchCatalogProducts(array $productData): Response
	{
		if (empty($productData))
		{
			return $this->createResponseWithError('Empty product data.');
		}

		if (!Application::getInstance()->isUtfMode())
		{
			$productData = (array)Encoding::convertEncoding($productData, 'Windows-1251', 'UTF-8');
		}

		return $this->sendRequest('catalog.products.batch', [
			'allow_upsert' => true,
			'requests' => $productData,
		]);
	}

	public function checkBatchRequestStatus(string $handle): Response
	{
		if ($handle === '')
		{
			return $this->createResponseWithError('Empty handle data.');
		}

		return $this->sendRequest('catalog.products.check.batch.status', [
			'catalog_id' => $this->getCatalogId(),
			'load_ids_of_invalid_requests' => true,
			'handle' => $handle,
		]);
	}

	public function getProductsInfo(array $retailerIds): Response
	{
		if (empty($retailerIds))
		{
			return $this->createResponseWithError('Empty retailer ids.');
		}

		return $this->sendRequest('catalog.products.get.info', [
			'catalog_id' => $this->getCatalogId(),
			'filter' => Json::encode([
				'retailer_id' => [
					'is_any' => $retailerIds,
				]
			]),
			'fields' => 'id, retailer_id, review_status, review_rejection_reasons',
		]);
	}
}