<?php

namespace Bitrix\Seo\Checkout\Services;

use Bitrix\Crm\ItemIdentifier;
use Bitrix\Crm\Requisite\DefaultRequisite;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\Checkout\BaseApiObject;
use Bitrix\Seo\Checkout\Service;
use CCrmCompany;
use CCrmOwnerType;

class AccountTBankBusiness extends BaseApiObject
{
	public const TYPE_CODE = 'tbankbusiness';

	public static function getService(): Service
	{
		return Service::getInstance();
	}

	public static function registerMyCompanyOption(int $selectedCompany): void
	{
		if (!CCrmCompany::Exists($selectedCompany))
		{
			return;
		}

		$companyId = new ItemIdentifier(CCrmOwnerType::Company, $selectedCompany);
		$requisite = new DefaultRequisite($companyId);
		$requisiteInfo = $requisite->get();
		$inn = $requisiteInfo['RQ_INN'] ?? null;
		$kpp = $requisiteInfo['RQ_KPP'] ?? null;

		$companyDataToSave = [
			'ID' => $selectedCompany,
			'INN' => $inn,
		];

		if (!empty($kpp))
		{
			$companyDataToSave['KPP'] = $kpp;
		}

		Option::set(
			'sale',
			'TBANKBUSINESS_SELECTED_MY_COMPANY_JSON_DATA',
			Json::encode($companyDataToSave),
		);
	}

	public static function deleteMyCompanyOption(): void
	{
		Option::delete('sale', ['name' => 'TBANKBUSINESS_SELECTED_MY_COMPANY_JSON_DATA']);
	}

	public function getProfile(): ?array
	{
		$myCompanyDecodedInfo = Option::get('sale', 'TBANKBUSINESS_SELECTED_MY_COMPANY_JSON_DATA');

		if (empty($myCompanyDecodedInfo))
		{
			return null;
		}

		try
		{
			$myCompanyInfo = Json::decode($myCompanyDecodedInfo);
		}
		catch (\Bitrix\Main\ArgumentException)
		{
			return null;
		}

		if (
			empty($myCompanyInfo['ID'])
			|| empty($myCompanyInfo['INN'])
		)
		{
			return null;
		}

		$companyTitle = CCrmOwnerType::GetCaption(CCrmOwnerType::Company, $myCompanyInfo['ID']);

		if (empty($companyTitle))
		{
			return null;
		}

		return [
			'ID' => $myCompanyInfo['ID'],
			'TITLE' => $companyTitle,
			'INN' => $myCompanyInfo['INN'],
			'KPP' => $myCompanyInfo['KPP'] ?? '0',
		];
	}

	public static function removeAuth(): void
	{
		static::getService()->getAuthAdapter(self::TYPE_CODE)->removeAuth();
		static::deleteMyCompanyOption();
	}
}