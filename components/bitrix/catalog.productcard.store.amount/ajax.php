<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Engine\Response;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Catalog\Config;
use Bitrix\Catalog\Component\StoreAmount;

class VariationGridController extends \Bitrix\Main\Engine\Controller
{
	/**
	 * @return AjaxJson|string[]
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getStoreAmountTotalAction()
	{
		if (!Loader::includeModule('catalog'))
		{
			return $this->sendErrorResponse('Could not load "catalog" module.');
		}

		if (!Config\State::isUsedInventoryManagement())
		{
			return $this->sendErrorResponse('Inventory management is not enabled');
		}

		$params = $this->getUnsignedParameters();
		$entityId = (int)($params['ENTITY_ID'] ?? 0);

		if ($entityId <= 0)
		{
			return $this->sendErrorResponse(sprintf('Wrong iblock id {%s}.', $entityId));
		}

		$storeAmount = new StoreAmount($entityId);

		return $storeAmount->getTotalData();
	}

	private function sendErrorResponse(string $message)
	{
		$errorCollection = new ErrorCollection();
		$errorCollection->setError(new Error($message));

		return Response\AjaxJson::createError($errorCollection);
	}
}