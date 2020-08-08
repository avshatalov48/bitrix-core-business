<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

use Bitrix\Crm\Requisite;
use Bitrix\Crm\EntityRequisite;
use Bitrix\Crm\Format;
use Bitrix\Crm\EntityAddress;

Loc::loadMessages(__FILE__);

/**
 * Class CompanyCard
 * @package Bitrix\Sender\Integration\Crm
 */
class CompanyCard
{
	const DATA_PROVIDER_CODE = 'crm/requisites';

	/**
	 * Get array.
	 *
	 * @return array
	 */
	public static function getArray()
	{
		Loader::includeModule('crm');
		$reqData = self::getRequisites();
		if (!is_array($reqData))
		{
			$reqData = array();
		}

		$data = array();
		$data['COMPANY_NAME'] = $reqData['RQ_COMPANY_NAME'];
		if (!$data['COMPANY_NAME'])
		{
			$data['COMPANY_NAME'] = $reqData['RQ_NAME'];
		}
		$data['COMPANY_NAME'] = str_replace(array("'", '"'), '', $data['COMPANY_NAME']);

		$data['PHONE'] = $reqData['RQ_PHONE'];
		$data['ADDRESS'] = $reqData['COMPANY_ADDRESS'];

		return array(
			'CODE' => self::DATA_PROVIDER_CODE,
			'EDIT_URL' => '/crm/configs/mycompany/',
			'DATA' => $data
		);
	}

	/**
	 * Get requisites.
	 *
	 * @return array|null
	 */
	public static function getRequisites()
	{
		// get my company id
		$myCompanyId = Requisite\EntityLink::getDefaultMyCompanyId();
		if (!$myCompanyId)
		{
			return null;
		}

		// get requisites
		$req = new EntityRequisite;
		$res = $req->getList(array(
			'filter' => array(
				'=ENTITY_TYPE_ID' => \CCrmOwnerType::Company,
				'=ENTITY_ID' => $myCompanyId
			)
		));
		$data = $res->fetch();
		if (!$data)
		{
			return null;
		}

		// prepare requisites
		$result = array();
		foreach ($data as $key => $value)
		{
			if (mb_substr($key, 0, 3) == 'RQ_')
			{
				$result[$key] = $value;
			}
		}

		// format person name
		$result[EntityRequisite::PERSON_FULL_NAME] = \CUser::formatName(
			Format\PersonNameFormatter::getFormat(),
			array(
				'NAME' => $result[EntityRequisite::PERSON_FIRST_NAME],
				'LAST_NAME' => $result[EntityRequisite::PERSON_LAST_NAME],
				'SECOND_NAME' => $result[EntityRequisite::PERSON_SECOND_NAME],
			)
		);

		// get address requisites
		$addresses = EntityRequisite::getAddresses($data['ID']);
		$addressTypes = array(
			EntityAddress::Registered
		);

		$address = null;
		foreach ($addressTypes as $addressType)
		{
			if (isset($addresses[$addressType]))
			{
				$address = $addresses[$addressType];
			}
		}

		if (!$address && count($addresses) > 0)
		{
			$address = current($addresses);
		}

		if ($address && is_array($address))
		{
			$address = Format\EntityAddressFormatter::format($address, array(
				'SEPARATOR' => Format\AddressSeparator::Comma
			));
		}
		else
		{
			// get address from entity fields
			$address = \CCrmCompany::getByID($myCompanyId, false);
			if (!is_array($address))
			{
				$address = array();
			}
			if ($address['REG_ADDRESS'])
			{
				$addressTypeId =  EntityAddress::Registered;
			}
			else
			{
				$addressTypeId =  EntityAddress::Primary;
			}

			$address = Format\CompanyAddressFormatter::format($address, array(
				'SEPARATOR' => Format\AddressSeparator::Comma,
				'TYPE_ID' => $addressTypeId
			));
		}

		$result['COMPANY_ADDRESS'] = $address;

		return $result;
	}
}