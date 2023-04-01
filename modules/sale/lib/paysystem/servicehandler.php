<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Services\Base\RestrictionInfo;
use Bitrix\Sale\Services\Base\RestrictionInfoCollection;
use Bitrix\Sale\Services\PaySystem\Restrictions\RestrictableServiceHandler;

abstract class ServiceHandler extends BaseServiceHandler implements RestrictableServiceHandler
{
	/**
	 * @return array
	 */
	static public function getIndicativeFields()
	{
		return array();
	}

	/**
	 * @param Request $request
	 * @param int $paySystemId
	 * @return bool
	 */
	public static function isMyResponse(Request $request, $paySystemId)
	{
		$fields = static::getIndicativeFields();

		if (!is_array($fields) || empty($fields))
			return false;

		$isAssociate = \CSaleHelper::IsAssociativeArray($fields);

		foreach ($fields as $key => $value)
		{
			if (!$isAssociate && !isset($request[$value]))
				return false;

			if ($isAssociate && (!isset($request[$key]) || is_null($value) || ($value != $request[$key])))
				return false;
		}

		return static::isMyResponseExtended($request, $paySystemId);
	}

	/**
	 * @param Request $request
	 * @param $paySystemId
	 * @return bool
	 */
	protected static function isMyResponseExtended(Request $request, $paySystemId)
	{
		return true;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return ServiceResult
	 */
	public abstract function processRequest(Payment $payment, Request $request);

	/**
	 * @param ServiceResult $result
	 * @param Request $request
	 * @return mixed
	 */
	public function sendResponse(ServiceResult $result, Request $request)
	{
		return '';
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public abstract function getPaymentIdFromRequest(Request $request);

	/**
	 * @param array $paySystemList
	 * @return array
	 */
	public static function findMyDataRefundablePage(array $paySystemList)
	{
		return array();
	}

	/**
	 * Returns list of restrictions that installed on service add
	 *
	 * @return RestrictionInfoCollection
	 */
	public function getRestrictionList(): RestrictionInfoCollection
	{
		$collection = new RestrictionInfoCollection();

		$currencyList = $this->getCurrencyList();
		if (is_array($currencyList) && !empty($currencyList))
		{
			$currencyRestrictionContainer = new RestrictionInfo('Currency', ['CURRENCY' => $currencyList]);
			$collection->add($currencyRestrictionContainer);
		}

		return $collection;
	}
}