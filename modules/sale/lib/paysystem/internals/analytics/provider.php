<?php
namespace Bitrix\Sale\PaySystem\Internals\Analytics;

use Bitrix\Sale\Internals\Analytics,
	Bitrix\Sale\PaySystem\Manager,
	Bitrix\Sale\Registry,
	Bitrix\Main\Loader,
	Bitrix\Main\Type\DateTime;

/**
 * Class PaySystem
 * @package Bitrix\Sale\PaySystem\Internals\Analytics
 */
final class Provider extends Analytics\Provider
{
	private const TYPE = 'paysystem';

	/**
	 * @return string
	 */
	public static function getCode(): string
	{
		return self::TYPE;
	}

	/**
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function getProviderData(DateTime $dateFrom, DateTime $dateTo): array
	{
		$result = [];
		foreach ($this->getPaySystemHandlers() as $paySystemHandler)
		{
			$transactions = array_column($this->getPayment($paySystemHandler, $dateFrom, $dateTo), 'PS_INVOICE_ID');
			if ($transactions)
			{
				$result[] = [
					'pay_system' => Manager::getFolderFromClassName($paySystemHandler),
					'transactions' => $transactions,
				];
			}
		}

		return $result;
	}

	/**
	 * @return string[]
	 */
	private function getPaySystemHandlers(): array
	{
		return [
			\Sale\Handlers\PaySystem\YandexCheckoutHandler::class,
		];
	}

	/**
	 * @param string $paySystemHandler
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function getPayment(string $paySystemHandler, DateTime $dateFrom, DateTime $dateTo): array
	{
		$paySystemIdList = $this->getPaySystemIdList($paySystemHandler);
		if (!$paySystemIdList)
		{
			return [];
		}

		$result = [];

		$filter = [
			'PAY_SYSTEM_ID' => $paySystemIdList,
			'PAID' => 'Y',
			'!=PS_INVOICE_ID' => null,
			'>=PS_RESPONSE_DATE' => $dateFrom,
			'<=PS_RESPONSE_DATE' => $dateTo,
		];

		$registries[] = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		if (Loader::includeModule('crm'))
		{
			$registries[] = Registry::getInstance(REGISTRY_TYPE_CRM_INVOICE);
		}

		foreach ($registries as $registry)
		{
			$paymentClassName = $registry->getPaymentClassName();
			$paymentResult = $paymentClassName::getList([
				'select' => ['PS_INVOICE_ID'],
				'filter' => $filter,
			]);
			while ($paymentData = $paymentResult->fetch())
			{
				$result[] = $paymentData;
			}
		}

		return $result;
	}

	/**
	 * @param string $paySystemHandler
	 * @return int[]
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getPaySystemIdList(string $paySystemHandler): array
	{
		$result = [];

		$actionFile = Manager::getFolderFromClassName($paySystemHandler);
		$paySystemList = Manager::getList([
			'select' => ['ID'],
			'filter' => [
				'ACTION_FILE' => $actionFile,
			],
		])->fetchAll();

		if ($paySystemList)
		{
			$result = array_column($paySystemList, 'ID');
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected function getHash(array $data): string
	{
		return md5(serialize($data));
	}
}
