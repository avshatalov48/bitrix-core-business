<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;

Loc::loadMessages(__FILE__);

class CIBlockPropertyTools
{
	public const CODE_MORE_PHOTO = 'MORE_PHOTO';
	public const CODE_SKU_LINK = 'CML2_LINK';
	public const CODE_BLOG_POST = 'BLOG_POST_ID';
	public const CODE_BLOG_COMMENTS_COUNT = 'BLOG_COMMENTS_CNT';
	public const CODE_FORUM_TOPIC = 'FORUM_TOPIC_ID';
	public const CODE_FORUM_MESSAGES_COUNT = 'FORUM_MESSAGE_CNT';
	public const CODE_VOTE_COUNT = 'VOTE_COUNT';
	public const CODE_VOTE_COUNT_OLD = 'vote_count';
	public const CODE_VOTE_SUMM = 'VOTE_SUM';
	public const CODE_VOTE_SUMM_OLD = 'vote_sum';
	public const CODE_VOTE_RATING = 'RATING';
	public const CODE_VOTE_RATING_OLD = 'rating';
	public const CODE_ARTNUMBER = 'ARTNUMBER';
	public const CODE_BACKGROUND_IMAGE = 'BACKGROUND_IMAGE';
	public const CODE_BRAND_FOR_FACEBOOK = 'BRAND_FOR_FACEBOOK';

	public const XML_MORE_PHOTO = 'CML2_PICTURES';
	public const XML_SKU_LINK = 'CML2_LINK';
	public const XML_BLOG_POST = 'BLOG_POST_ID';
	public const XML_BLOG_COMMENTS_COUNT = 'BLOG_COMMENTS_CNT';
	public const XML_ARTNUMBER = 'CML2_ARTICLE';
	public const XML_BACKGROUND_IMAGE = 'BACKGROUND_IMAGE';
	public const XML_BRAND_FOR_FACEBOOK = 'BRAND_FOR_FACEBOOK';

	/** @deprecated use CIBlockPropertySKU::USER_TYPE */
	public const USER_TYPE_SKU_LINK = CIBlockPropertySKU::USER_TYPE;

	protected static $errors = [];

	/**
	 * Return error list.
	 *
	 * @return array
	 */
	public static function getErrors(): array
	{
		return self::$errors;
	}

	/**
	 * Clear error list
	 *
	 * @return void
	 */
	public static function clearErrors()
	{
		self::$errors = [];
	}

	/**
	 * Create property.
	 *
	 * @param int $iblockId Iblock id.
	 * @param string $propertyCode Property code.
	 * @param array $propertyParams Property params.
	 * @return bool|int
	 */
	public static function createProperty(int $iblockId, string $propertyCode, array $propertyParams = [])
	{
		static::clearErrors();

		if ($iblockId <= 0 || $propertyCode === '')
		{
			return false;
		}

		$iblock = Iblock\IblockTable::getList([
			'select' => ['ID'],
			'filter' => ['=ID' => $iblockId],
		])->fetch();
		if (empty($iblock))
		{
			return false;
		}

		$propertyParams['IBLOCK_ID'] = $iblockId;
		$propertyDescription = static::getPropertyDescription($propertyCode, $propertyParams);
		if ($propertyDescription === false)
		{
			return false;
		}

		$propertyDescription['IBLOCK_ID'] = $iblockId;
		if (!static::validatePropertyDescription($propertyDescription))
		{
			return false;
		}

		$propertyId = 0;
		$getListParams = [
			'select' => ['ID'],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'=CODE' => $propertyCode,
				'=ACTIVE' => 'Y'
			],
		];
		static::modifyGetListParams($getListParams, $propertyCode, $propertyDescription);
		$property = Iblock\PropertyTable::getList($getListParams)->fetch();
		if (!empty($property))
		{
			if (static::validateExistProperty($propertyCode, $property))
			{
				$propertyId = (int)$property['ID'];
			}
		}
		unset($property);
		if (!empty(self::$errors))
		{
			return false;
		}
		if ($propertyId > 0)
		{
			return $propertyId;
		}

		$propertyObject = new \CIBlockProperty();
		$propertyId = (int)$propertyObject->Add($propertyDescription);
		if ($propertyId > 0)
		{
			return $propertyId;
		}
		else
		{
			self::$errors[] = $propertyObject->LAST_ERROR;

			return false;
		}
	}

	/**
	 * Return filled property description.
	 *
	 * @param string $code Property symbolic code.
	 * @param array $fields Property fields.
	 * @return array|null
	 */
	public static function getPropertyDescription(
		string $code,
		array $fields = []
	): ?array
	{
		switch($code)
		{
			case self::CODE_MORE_PHOTO:
				$name = Loc::getMessage('IBPT_PROP_TITLE_MORE_PHOTO');
				if (isset($fields['IBLOCK_ID']))
				{
					if (Loader::includeModule('catalog'))
					{
						$catalog = CCatalogSku::GetInfoByIBlock($fields['IBLOCK_ID']);
						if (!empty($catalog))
						{
							$name =
								$catalog['CATALOG_TYPE'] === CCatalogSku::TYPE_OFFERS
								? Loc::getMessage('IBPT_PROP_TITLE_PRODUCT_VARIATION_MORE_PHOTO')
								: Loc::getMessage('IBPT_PROP_TITLE_PRODUCT_MORE_PHOTO')
							;
						}
					}
				}
				$property = [
					'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_FILE,
					'NAME' => $name,
					'CODE' => self::CODE_MORE_PHOTO,
					'XML_ID' => self::XML_MORE_PHOTO,
					'MULTIPLE' => 'Y',
					'MULTIPLE_CNT' => 1,
					'FILE_TYPE' => 'jpg, gif, bmp, png, jpeg, webp',
					'WITH_DESCRIPTION' => 'Y',
				];
				break;
			case self::CODE_SKU_LINK:
				$property = [
					'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_ELEMENT,
					'USER_TYPE' => CIBlockPropertySKU::USER_TYPE,
					'NAME' => Loc::getMessage('IBPT_PROP_TITLE_SKU_LINK'),
					'CODE' => self::CODE_SKU_LINK,
					'XML_ID' => self::XML_SKU_LINK,
					'FILTRABLE' => 'Y',
				];
				break;
			case self::CODE_BLOG_POST:
				$property = [
					'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_NUMBER,
					'NAME' => Loc::getMessage('IBPT_PROP_TITLE_BLOG_POST'),
					'CODE' => self::CODE_BLOG_POST,
					'XML_ID' => self::XML_BLOG_POST,
				];
				break;
			case self::CODE_BLOG_COMMENTS_COUNT:
				$property = [
					'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_NUMBER,
					'NAME' => Loc::getMessage('IBPT_PROP_TITLE_BLOG_COMMENTS_COUNT'),
					'CODE' => self::CODE_BLOG_COMMENTS_COUNT,
					'XML_ID' => self::XML_BLOG_COMMENTS_COUNT,
				];
				break;
			case self::CODE_ARTNUMBER:
				$property = [
					'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
					'NAME' => Loc::getMessage('IBPT_PROP_TITLE_ARTNUMBER'),
					'CODE' => self::CODE_ARTNUMBER,
					'XML_ID' => self::XML_ARTNUMBER,
				];
				break;
			case self::CODE_BACKGROUND_IMAGE:
				$property = [
					'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
					'NAME' => Loc::getMessage('IBPT_PROP_TITLE_BACKGROUND_IMAGE'),
					'CODE' => self::CODE_BACKGROUND_IMAGE,
					'XML_ID' => self::XML_BACKGROUND_IMAGE,
				];
				break;
			case self::CODE_BRAND_FOR_FACEBOOK:
				$property = [
					'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
					'NAME' => Loc::getMessage('IBPT_PROP_TITLE_BRAND_FOR_FACEBOOK'),
					'CODE' => self::CODE_BRAND_FOR_FACEBOOK,
					'XML_ID' => self::XML_BRAND_FOR_FACEBOOK,
					'MULTIPLE' => 'Y',
					'USER_TYPE' => 'directory',
					'USER_TYPE_SETTINGS' => [
						'TABLE_NAME' => 'b_catalog_facebook_brand_reference',
					],
				];
				break;
			default:
				$property = null;
				break;
		}
		if ($property !== null)
		{
			$property += [
				'ACTIVE' => 'Y',
				'SORT' => 500,
				'MULTIPLE' => 'N',
				'IS_REQIRED' => 'N',
				'USER_TYPE' => null,
			];
			if (!empty($fields))
			{
				$property = $fields + $property;
			}
		}

		return $property;
	}

	/**
	 * Check property description before create.
	 *
	 * @param array $propertyDescription Property description.
	 * @return bool
	 */
	public static function validatePropertyDescription(array $propertyDescription): bool
	{
		if (empty($propertyDescription) || !isset($propertyDescription['CODE']))
		{
			return false;
		}
		$checkResult = true;

		switch ($propertyDescription['CODE'])
		{
			case self::CODE_SKU_LINK:
				if (
					!isset($propertyDescription['LINK_IBLOCK_ID'])
					|| $propertyDescription['LINK_IBLOCK_ID'] <= 0
					|| $propertyDescription['LINK_IBLOCK_ID'] == $propertyDescription['IBLOCK_ID']
				)
				{
					$checkResult = false;
				}
				if ($checkResult)
				{
					$iblockIterator = Iblock\IblockTable::getList([
						'select' => ['ID'],
						'filter' => ['=ID' => $propertyDescription['LINK_IBLOCK_ID']]
					]);
					if (!($iblock = $iblockIterator->fetch()))
					{
						$checkResult = false;
					}
				}
				break;
			case self::CODE_MORE_PHOTO:
			case self::CODE_BLOG_POST:
			case self::CODE_BLOG_COMMENTS_COUNT:
			case self::CODE_BRAND_FOR_FACEBOOK:
				$checkResult = true;
				break;
			default:
				$checkResult = false;
				break;
		}
		return $checkResult;
	}

	/**
	 * Returns the list of infoblock properties, values for which need to be emptied when copying infoblock element.
	 *
	 * @param int $iblockId Iblock id.
	 * @param array $propertyCodes Property codes.
	 * @return array
	 */
	public static function getClearedPropertiesID(int $iblockId, array $propertyCodes = []): array
	{
		if ($iblockId <= 0)
		{
			return [];
		}
		if (empty($propertyCodes) || !is_array($propertyCodes))
		{
			$propertyCodes = [
				self::CODE_BLOG_POST,
				self::CODE_BLOG_COMMENTS_COUNT,
				self::CODE_FORUM_TOPIC,
				self::CODE_FORUM_MESSAGES_COUNT,
				self::CODE_VOTE_COUNT,
				self::CODE_VOTE_COUNT_OLD,
				self::CODE_VOTE_SUMM,
				self::CODE_VOTE_SUMM_OLD,
				self::CODE_VOTE_RATING,
				self::CODE_VOTE_RATING_OLD,
			];
		}
		$result = [];
		$propertyIterator = Iblock\PropertyTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'@CODE' => $propertyCodes
			]
		]);
		while ($property = $propertyIterator->fetch())
		{
			$result[] = (int)$property['ID'];
		}

		return $result;
	}

	/**
	 * Return exist property list.
	 *
	 * @param int $iblockId Iblock id.
	 * @param array|string $propertyCodes Property codes.
	 * @param bool $indexCode Return codes as key.
	 * @return array|bool
	 */
	public static function getExistProperty(int $iblockId, $propertyCodes, bool $indexCode = true)
	{
		if ($iblockId <= 0)
		{
			return false;
		}
		$propertyCodes = static::clearPropertyList($propertyCodes);
		if (empty($propertyCodes))
		{
			return false;
		}

		$result = [];
		$propertyIterator = Iblock\PropertyTable::getList([
			'select' => [
				'ID',
				'CODE'
			],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'@CODE' => $propertyCodes
			]
		]);
		if ($indexCode)
		{
			while ($property = $propertyIterator->fetch())
			{
				$property['ID'] = (int)$property['ID'];
				if (!isset($result[$property['CODE']]))
				{
					$result[$property['CODE']] = $property['ID'];
				}
				else
				{
					if (!is_array($result[$property['CODE']]))
					{
						$result[$property['CODE']] = [
							$result[$property['CODE']]
						];
					}
					$result[$property['CODE']][] = $property['ID'];
				}
			}
			unset($property, $propertyIterator);
		}
		else
		{
			while ($property = $propertyIterator->fetch())
			{
				$property['ID'] = (int)$property['ID'];
				$result[$property['ID']] = $property['CODE'];
			}
			unset($property, $propertyIterator);
		}
		return $result;
	}

	/**
	 * Return property symbolic codes.
	 *
	 * @param bool $extendedMode Get codes as keys.
	 * @return array
	 */
	public static function getPropertyCodes(bool $extendedMode = false): array
	{
		$result = [
			self::CODE_MORE_PHOTO,
			self::CODE_SKU_LINK,
			self::CODE_BLOG_POST,
			self::CODE_BLOG_COMMENTS_COUNT,
			self::CODE_FORUM_TOPIC,
			self::CODE_FORUM_MESSAGES_COUNT,
			self::CODE_VOTE_COUNT,
			self::CODE_VOTE_COUNT_OLD,
			self::CODE_VOTE_SUMM,
			self::CODE_VOTE_SUMM_OLD,
			self::CODE_VOTE_RATING,
			self::CODE_VOTE_RATING_OLD
		];
		return (
			$extendedMode
			? array_fill_keys($result, true)
			: $result
		);
	}

	/**
	 * Clear property symbolic codes.
	 *
	 * @param array|string $propertyCodes
	 * @return array|string
	 */
	public static function clearPropertyList($propertyCodes)
	{
		$result = [];
		if (!is_array($propertyCodes))
		{
			$propertyCodes = [(string)$propertyCodes];
		}
		if (empty($propertyCodes))
		{
			return $result;
		}

		$currentList = static::getPropertyCodes(true);
		foreach ($propertyCodes as $code)
		{
			$code = (string)$code;
			if (isset($currentList[$code]))
			{
				$result[] = $code;
			}
		}
		unset($code);

		return $result;
	}

	/**
	 * Modify getList params for property search.
	 *
	 * @param array &$getListParams \Bitrix\Main\Entity\DataManager::getList params.
	 * @param string $propertyCode Property code.
	 * @param array $propertyDescription Property description.
	 * @return void
	 */
	protected static function modifyGetListParams(
		array &$getListParams,
		string $propertyCode,
		array$propertyDescription
	): void
	{
		switch ($propertyCode)
		{
			case self::CODE_SKU_LINK:
				$getListParams['select'][] = 'XML_ID';
				$getListParams['select'][] = 'USER_TYPE';

				$getListParams['filter']['=LINK_IBLOCK_ID'] = $propertyDescription['LINK_IBLOCK_ID'];
				$getListParams['filter']['=PROPERTY_TYPE'] = Iblock\PropertyTable::TYPE_ELEMENT;
				$getListParams['filter']['=ACTIVE'] = 'Y';
				$getListParams['filter']['=MULTIPLE'] = 'N';
				break;
		}
	}

	/**
	 * Validate and modify exist property.
	 *
	 * @param string $propertyCode Property code.
	 * @param array $property Current property data.
	 * @return bool
	 */
	protected static function validateExistProperty(string $propertyCode, array $property): bool
	{
		$result = true;
		switch ($propertyCode)
		{
			case self::CODE_SKU_LINK:
				$fields = [];
				if ($property['USER_TYPE'] != CIBlockPropertySKU::USER_TYPE)
				{
					$fields['USER_TYPE'] = CIBlockPropertySKU::USER_TYPE;
				}
				if ($property['XML_ID'] != self::XML_SKU_LINK)
				{
					$fields['XML_ID'] = self::XML_SKU_LINK;
				}
				if (!empty($fields))
				{
					$propertyResult = Iblock\PropertyTable::update($property['ID'], $fields);
					if (!$propertyResult->isSuccess())
					{
						self::$errors = $propertyResult->getErrorMessages();
						$result = false;
					}
					unset($propertyResult);
				}
				unset($fields);
				break;
		}

		return $result;
	}
}
