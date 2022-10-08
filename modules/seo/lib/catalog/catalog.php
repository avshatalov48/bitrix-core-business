<?php

namespace Bitrix\Seo\Catalog;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\BusinessSuite\AbstractBase;
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

	public function getCatalogId(): ?string
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
							'catalog_id' => $this->getCatalogId(),
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

		return $this->sendRequest('products.batch', [
			'catalog_id' => $this->getCatalogId(),
			'allow_upsert' => true,
			'requests' => $productData,
		]);
	}

	public function checkBatchRequestStatus(string $queueId): Response
	{
		if ($queueId === '')
		{
			return $this->createResponseWithError('Empty queue id.');
		}

		return $this->sendRequest('products.check.batch.status', [
			'queue_id' => $queueId,
		]);
	}

	public function getProductsInfo(array $retailerIds): Response
	{
		if (empty($retailerIds))
		{
			return $this->createResponseWithError('Empty retailer ids.');
		}

		return $this->sendRequest('products.get.info', [
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