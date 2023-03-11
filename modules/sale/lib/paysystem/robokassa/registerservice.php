<?php

namespace Bitrix\Sale\PaySystem\Robokassa;

use Bitrix\Main;
use Bitrix\Sale;

final class RegisterService
{
	private const SIGN_PART = 'bitrix';
	private const CREATE_ACTION = 'rk_reg_created';

	public function processRequest(Main\Request $request): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$action = $request->get('act');
		if ($action === self::CREATE_ACTION)
		{
			$checkRequiredFieldsResult = $this->checkRequiredFields($request);
			if (!$checkRequiredFieldsResult->isSuccess())
			{
				$result->addErrors($checkRequiredFieldsResult->getErrors());
				return $result;
			}

			$signedDomain = $request->get('signed_domain') ?? '';
			if (!$this->isValidDomain($signedDomain))
			{
				$result->addError(new Main\Error('Signed domain not valid'));
				return $result;
			}

			$shopId = $request->get('shopId');
			$sign = $request->get('sign');
			$key1 = $request->get('key_1');
			$key2 = $request->get('key_2');

			if (!$this->isSignValid($shopId, $sign))
			{
				$result->addError(new Main\Error('Bad sign'));
				return $result;
			}

			try
			{
				$saveResult = self::save($shopId, $key1, $key2);
			}
			catch (\Exception $exception)
			{
				$saveResult = new Main\Result();
				$saveResult->addError(new Main\Error($exception->getMessage()));
			}

			if (!$saveResult->isSuccess())
			{
				$result->addErrors($saveResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * Saves new shop settings
	 *
	 * @param string $shopId
	 * @param string $key1
	 * @param string $key2
	 * @return Main\ORM\Data\Result
	 */
	private static function save(string $shopId, string $key1, string $key2): Main\Result
	{
		$settingsFields = self::prepareSettings($shopId, $key1, $key2);

		$shopSettings = new Sale\PaySystem\Robokassa\ShopSettings();

		$currentSettings = $shopSettings->get();
		if ($currentSettings)
		{
			return $shopSettings->update($settingsFields);
		}

		return $shopSettings->add($settingsFields);
	}

	private static function prepareSettings(string $shopId, string $key1, string $key2): array
	{
		return [
			'ROBOXCHANGE_SHOPLOGIN' => $shopId,
			'ROBOXCHANGE_SHOPPASSWORD' => $key1,
			'ROBOXCHANGE_SHOPPASSWORD2' => $key2,
		];
	}

	private function isSignValid(string $shopId, string $sign): bool
	{
		$calculatedSign = md5(sprintf('%s.%s', self::SIGN_PART, $shopId));
		return strcasecmp($calculatedSign, $sign) === 0;
	}

	private function checkRequiredFields(Main\Request $request): Main\Result
	{
		$result = new Main\Result();

		$requireFields = [
			'shopId',
			'sign',
			'key_1',
			'key_2',
			'signed_domain',
		];

		foreach ($requireFields as $field)
		{
			if (!$request->get($field))
			{
				$result->addError(new Main\Error("{$field} not found"));
			}
		}

		return $result;
	}

	public function isValidDomain(string $signedDomain): bool
	{
		$request = Main\Application::getInstance()->getContext()->getRequest();
		$protocol = $request->isHttps() ? 'https' : 'http';
		$domain = "{$protocol}://{$request->getHttpHost()}";

		return (new Sale\PaySystem\Robokassa\DomainSigner($domain))->isValidDomain($signedDomain);
	}
}
