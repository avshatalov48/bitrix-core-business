<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Sale\Internals\UserPropsTable;
use Bitrix\Sale\Location\LocationTable;

Loc::loadMessages(__FILE__);

class OrderUserProperties
{
	private $profiles = array();
	private static $instance;

	function __construct()
	{

	}

	/**
	 * @return OrderUserProperties
	 */
	public static function getInstance()
	{

		if(!isset(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param array $parameters
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getList(array $parameters)
	{
		return UserPropsTable::getList($parameters);
	}

	/**
	 * @param $parameters
	 */
	public static function loadFromDB($parameters)
	{
		static::getInstance()->profiles = static::getList($parameters)->fetchAll();
	}

	/**
	 * @param $personTypeId
	 * @param $userId
	 * @return bool
	 */
	public static function getFirstId($personTypeId, $userId)
	{
		if (empty(static::getInstance()->profiles))
		{
			static::loadFromDB(array(
				'order' => array("DATE_UPDATE" => "DESC"),
				'filter' => array(
								"PERSON_TYPE_ID" => $personTypeId,
								"USER_ID" => $userId
								),
			));
		}

		if (!empty(static::getInstance()->profiles) && is_array(static::getInstance()->profiles))
		{
			$profile = reset(static::getInstance()->profiles);
			return $profile['ID'];
		}

		return false;
	}

	/**
	 * @param $profileId
	 * @param $personTypeId
	 * @param $userId
	 * @return bool
	 */
	public static function checkCorrect($profileId, $personTypeId, $userId)
	{
		if (static::getList(array(
			'filter' => array(
				"ID" => $profileId,
				"PERSON_TYPE_ID" => $personTypeId,
				"USER_ID" => $userId
			)))->fetch())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Collect profile information
	 *
	 * @param int $userId
	 * @param int|null $personTypeId
	 * @param int|null $profileId
	 *
	 * @return Result
	 */
	public static function loadProfiles($userId, $personTypeId = null, $profileId = null)
	{
		$result = new Result();
		$userId = (int)$userId;

		$resultData =
		$filter =
		$locationIdMap =
		$locationCodeList = array();

		if ($userId <= 0)
		{
			return $result->addError(new Error("EMPTY USER ID"));
		}
		else
		{
			$filter['USER_ID'] = $userId;
		}

		if ((int)$personTypeId > 0)
			$filter['PERSON_TYPE_ID'] = (int)$personTypeId;

		if ((int)$profileId > 0)
			$filter['USER_PROPS_ID'] = (int)$profileId;

		$userPropsValueData = Internals\UserPropsValueTable::getList(
			array(
				'filter' => $filter,
				'select' => array(
					'ORDER_PROPS_ID', 'USER_PROPS_ID', 'VALUE',
					'PROFILE_NAME' => 'USER_PROPERTY.NAME',
					'VALUE_ORIG' => 'VALUE',
					'USER_ID' => 'USER_PROPERTY.USER_ID',
					'PERSON_TYPE_ID' => 'USER_PROPERTY.PERSON_TYPE_ID',
					'DATE_UPDATE' => 'USER_PROPERTY.DATE_UPDATE',
					'MULTIPLE' => 'PROPERTY.MULTIPLE',
					'TYPE' => 'PROPERTY.TYPE',
				),
				'order' => array(
					'USER_PROPERTY.DATE_UPDATE' => 'DESC',
					'USER_PROPERTY.NAME' => 'ASC'
				)
			)
		);

		while ($propValue = $userPropsValueData->fetch())
		{
			if (($propValue['MULTIPLE'] === 'Y' || $propValue['TYPE'] === 'FILE')
				&& CheckSerializedData($propValue['VALUE'])
				&& ($serialisedValue = @unserialize($propValue['VALUE'], ['allowed_classes' => false])) !== false)
			{
				$propValue['VALUE'] = $serialisedValue;
			}

			if (!array_key_exists($propValue['PERSON_TYPE_ID'], $resultData))
				$resultData[$propValue['PERSON_TYPE_ID']] = array();

			$resultData[$propValue['PERSON_TYPE_ID']][$propValue['USER_PROPS_ID']]['NAME'] = $propValue['PROFILE_NAME'];

			if ($propValue['TYPE'] === 'ENUM' && $propValue['MULTIPLE'] === 'Y')
			{
				$propValue['VALUE'] = explode(',', $propValue['VALUE']);
			}
			elseif ($propValue['TYPE'] === 'FILE' && !empty($propValue['VALUE']))
			{
				$fileIds = $propValue['VALUE'];
				$propValue['VALUE'] = array();
				if (is_array($fileIds))
				{
					foreach ($fileIds as $value)
					{
						if ($fileArray = \CFile::GetFileArray($value))
						{
							$propValue['VALUE'][] = $fileArray;
						}
					}
				}
				elseif ($fileArray = \CFile::GetFileArray($fileIds))
				{
					$propValue['VALUE'] = $fileArray;
				}
			}

			$resultData[$propValue['PERSON_TYPE_ID']][$propValue['USER_PROPS_ID']]['VALUES'][$propValue['ORDER_PROPS_ID']] = $propValue['VALUE'];
			$resultData[$propValue['PERSON_TYPE_ID']][$propValue['USER_PROPS_ID']]['VALUES_ORIG'][$propValue['ORDER_PROPS_ID']] = $propValue['VALUE_ORIG'];
		}

		$result->setData($resultData);

		return $result;
	}

	/**
	 * Get customer profile values
	 *
	 * @param $profileId
	 *
	 * @return array
	 */
	public static function getProfileValues($profileId)
	{
		$result = array();
		if ((int)$profileId <= 0)
		{
			return $result;
		}

		$userPropsValueData = Internals\UserPropsTable::getList(
			array(
				'filter' => array('=ID' => (int)$profileId),
				'select' => array('ID','USER_ID', 'PERSON_TYPE_ID'),
				'limit' => 1
			)
		);
		$profile = $userPropsValueData->fetch();
		if (!$profile )
		{
			return $result;
		}
		$resultLoading = static::loadProfiles($profile['USER_ID'], $profile['PERSON_TYPE_ID'], $profile['ID']);

		if ($resultLoading->isSuccess())
		{
			$data = $resultLoading->getData();
			$resultValue = $data[$profile['PERSON_TYPE_ID']][$profile['ID']]['VALUES'];
			$result = !empty($resultValue) ? $resultValue : array();
		}

		return $result;
	}
}