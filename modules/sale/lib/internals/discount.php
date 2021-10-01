<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main,
	Bitrix\Main\Application,
	Bitrix\Main\Config,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Discount\Actions,
	Bitrix\Sale\Discount\Gift,
	Bitrix\Sale\Discount\Index,
	Bitrix\Sale\Discount\Analyzer;

Loc::loadMessages(__FILE__);

/**
 * Class DiscountTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> XML_ID string(255) optional
 * <li> LID string(2) mandatory
 * <li> NAME string(255) optional
 * <li> PRICE_FROM float optional
 * <li> PRICE_TO float optional
 * <li> CURRENCY string(3) optional
 * <li> DISCOUNT_VALUE float mandatory
 * <li> DISCOUNT_TYPE string(1) mandatory default 'P'
 * <li> ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 100
 * <li> ACTIVE_FROM datetime optional
 * <li> ACTIVE_TO datetime optional
 * <li> TIMESTAMP_X datetime optional
 * <li> MODIFIED_BY int optional
 * <li> DATE_CREATE datetime optional
 * <li> CREATED_BY int optional
 * <li> PRIORITY int optional default 1
 * <li> LAST_DISCOUNT bool optional default 'Y'
 * <li> VERSION int optional default 3
 * <li> CONDITIONS text optional
 * <li> CONDITIONS_LIST text optional
 * <li> UNPACK text optional
 * <li> ACTIONS text optional
 * <li> ACTIONS_LIST text optional
 * <li> APPLICATION text optional
 * <li> PREDICTION_TEXT text optional
 * <li> PREDICTIONS text optional
 * <li> PREDICTIONS_APP text optional
 * <li> USE_COUPONS bool optional default 'N'
 * <li> USE_INDEX bool optional default 'N'
 * <li> PRESET_ID string optional
 * <li> EXECUTE_MODULE string(50) mandatory default 'all'
 * <li> EXECUTE_MODE int default 0
 * <li> CREATED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * <li> MODIFIED_BY_USER reference to {@link \Bitrix\Main\UserTable}
 * </ul>
 *
 * @package Bitrix\Sale\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Discount_Query query()
 * @method static EO_Discount_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Discount_Result getById($id)
 * @method static EO_Discount_Result getList(array $parameters = array())
 * @method static EO_Discount_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_Discount createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_Discount_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_Discount wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_Discount_Collection wakeUpCollection($rows)
 */
class DiscountTable extends Main\Entity\DataManager
{
	const VERSION_OLD = 0x0001;
	const VERSION_NEW = 0x0002;
	const VERSION_15 = 0x0003;

	const EXECUTE_MODE_GENERAL = 0;
	const EXECUTE_MODE_SEPARATELY = 2;

	protected static $deleteCoupons = false;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_discount';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ID_FIELD')
			)),
			'XML_ID' => new Main\Entity\StringField('XML_ID', array(
				'validation' => array(__CLASS__, 'validateXmlId'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_XML_ID_FIELD')
			)),
			'LID' => new Main\Entity\StringField('LID', array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateLid'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_LID_FIELD')
			)),
			'NAME' => new Main\Entity\StringField('NAME', array(
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_NAME_FIELD')
			)),
			'PRICE_FROM' => new Main\Entity\FloatField('PRICE_FROM', array()),
			'PRICE_TO' => new Main\Entity\FloatField('PRICE_TO', array()),
			'CURRENCY' => new Main\Entity\StringField('CURRENCY', array(
				'validation' => array(__CLASS__, 'validateCurrency'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_CURRENCY_FIELD')
			)),
			'DISCOUNT_VALUE' => new Main\Entity\FloatField('DISCOUNT_VALUE', array()),
			'DISCOUNT_TYPE' => new Main\Entity\StringField('DISCOUNT_TYPE', array(
				'default_value' => 'P',
				'validation' => array(__CLASS__, 'validateDiscountType')
			)),
			'ACTIVE' => new Main\Entity\BooleanField('ACTIVE', array(
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ACTIVE_FIELD')
			)),
			'SORT' => new Main\Entity\IntegerField('SORT', array(
				'title' => Loc::getMessage('DISCOUNT_ENTITY_SORT_FIELD')
			)),
			'ACTIVE_FROM' => new Main\Entity\DatetimeField('ACTIVE_FROM', array(
				'default_value' => null,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ACTIVE_FROM_FIELD')
			)),
			'ACTIVE_TO' => new Main\Entity\DatetimeField('ACTIVE_TO', array(
				'default_value' => null,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ACTIVE_TO_FIELD')
			)),
			'TIMESTAMP_X' => new Main\Entity\DatetimeField('TIMESTAMP_X', array(
				'default_value' => function(){ return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('DISCOUNT_ENTITY_TIMESTAMP_X_FIELD')
			)),
			'MODIFIED_BY' => new Main\Entity\IntegerField('MODIFIED_BY', array(
				'default_value' => null,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_MODIFIED_BY_FIELD')
			)),
			'DATE_CREATE' => new Main\Entity\DatetimeField('DATE_CREATE', array(
				'default_value' => function(){ return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('DISCOUNT_ENTITY_DATE_CREATE_FIELD')
			)),
			'CREATED_BY' => new Main\Entity\IntegerField('CREATED_BY', array(
				'default_value' => null,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_CREATED_BY_FIELD')
			)),
			'PRIORITY' => new Main\Entity\IntegerField('PRIORITY', array(
				'default_value' => 1,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_PRIORITY_FIELD')
			)),
			'LAST_DISCOUNT' => new Main\Entity\BooleanField('LAST_DISCOUNT', array(
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('DISCOUNT_ENTITY_LAST_DISCOUNT_FIELD')
			)),
			'LAST_LEVEL_DISCOUNT' => new Main\Entity\BooleanField('LAST_LEVEL_DISCOUNT', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			)),
			'VERSION' => new Main\Entity\EnumField('VERSION', array(
				'values' => array(self::VERSION_OLD, self::VERSION_NEW, self::VERSION_15),
				'default_value' => self::VERSION_15,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_VERSION_FIELD')
			)),
			'CONDITIONS_LIST' => new Main\Entity\TextField('CONDITIONS_LIST', array(
				'serialized' => true,
				'column_name' => 'CONDITIONS',
				'title' => Loc::getMessage('DISCOUNT_ENTITY_CONDITIONS_LIST_FIELD')
			)),
			'CONDITIONS' => new Main\Entity\ExpressionField('CONDITIONS', '%s', 'CONDITIONS_LIST'),
			'UNPACK' => new Main\Entity\TextField('UNPACK', array()),
			'ACTIONS_LIST' => new Main\Entity\TextField('ACTIONS_LIST', array(
				'serialized' => true,
				'column_name' => 'ACTIONS',
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ACTIONS_LIST_FIELD')
			)),
			'ACTIONS' => new Main\Entity\ExpressionField('ACTIONS', '%s', 'ACTIONS_LIST'),
			'APPLICATION' => new Main\Entity\TextField('APPLICATION', array()),
			'PREDICTION_TEXT' => new Main\Entity\TextField('PREDICTION_TEXT', array()),
			'PREDICTIONS_APP' => new Main\Entity\TextField('PREDICTIONS_APP', array()),
			'PREDICTIONS_LIST' => new Main\Entity\TextField('PREDICTIONS_LIST', array(
				'serialized' => true,
				'column_name' => 'PREDICTIONS',
			)),
			'PREDICTIONS' => new Main\Entity\ExpressionField('PREDICTIONS', '%s', 'PREDICTIONS_LIST'),
			'USE_COUPONS' => new Main\Entity\BooleanField('USE_COUPONS', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('DISCOUNT_ENTITY_USE_COUPONS_FIELD')
			)),
			'EXECUTE_MODULE' => new Main\Entity\StringField('EXECUTE_MODULE', array(
				'validation' => array(__CLASS__, 'validateExecuteModule'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_EXECUTE_MODULE_FIELD')
			)),
			'EXECUTE_MODE' => new Main\Entity\IntegerField('EXECUTE_MODE', array(
				'default_value' => self::EXECUTE_MODE_GENERAL,
			)),
			'HAS_INDEX' => new Main\Entity\BooleanField('HAS_INDEX', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			)),
			'PRESET_ID' => new Main\Entity\StringField('PRESET_ID', array(
				'validation' => array(__CLASS__, 'validatePresetId'),
			)),
			'SHORT_DESCRIPTION_STRUCTURE' => new Main\Entity\TextField('SHORT_DESCRIPTION_STRUCTURE', array(
				'serialized' => true,
				'column_name' => 'SHORT_DESCRIPTION',
			)),
			'SHORT_DESCRIPTION' => new Main\Entity\ExpressionField('SHORT_DESCRIPTION', '%s', 'SHORT_DESCRIPTION_STRUCTURE'),
			'CREATED_BY_USER' => new Main\Entity\ReferenceField(
				'CREATED_BY_USER',
				'Bitrix\Main\User',
				array('=this.CREATED_BY' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
			'MODIFIED_BY_USER' => new Main\Entity\ReferenceField(
				'MODIFIED_BY_USER',
				'Bitrix\Main\User',
				array('=this.MODIFIED_BY' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
			'COUPON' => new Main\Entity\ReferenceField(
				'COUPON',
				'Bitrix\Sale\Internals\DiscountCoupon',
				array('=this.ID' => 'ref.DISCOUNT_ID'),
				array('join_type' => 'LEFT')
			),
			'DISCOUNT_ENTITY' => new Main\Entity\ReferenceField(
				'DISCOUNT_ENTITY',
				'Bitrix\Sale\Internals\DiscountEntities',
				array('=this.ID' => 'ref.DISCOUNT_ID'),
				array('join_type' => 'LEFT')
			)
		);
	}
	/**
	 * Returns validators for XML_ID field.
	 *
	 * @return array
	 */
	public static function validateXmlId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for LID field.
	 *
	 * @return array
	 */
	public static function validateLid()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CURRENCY field.
	 *
	 * @return array
	 */
	public static function validateCurrency()
	{
		return array(
			new Main\Entity\Validator\Length(null, 3),
		);
	}
	/**
	 * Returns validators for DISCOUNT_TYPE field.
	 *
	 * @return array
	 */
	public static function validateDiscountType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for EXECUTE_MODULE field.
	 *
	 * @return array
	 */
	public static function validateExecuteModule()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for PRESET_ID field.
	 *
	 * @return array
	 */
	public static function validatePresetId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Default onBeforeAdd handler. Absolutely necessary.
	 *
	 * @param Main\Entity\Event $event		Event object.
	 * @return Main\Entity\EventResult
	 */
	public static function onBeforeAdd(Main\Entity\Event $event)
	{
		$result = new Main\Entity\EventResult;
		$fields = $event->getParameter('fields');

		$modifyFieldList = array(
			'DISCOUNT_VALUE' => 0,
			'DISCOUNT_TYPE' => 'P',
		);
		if (isset($fields['LID']))
			$modifyFieldList['CURRENCY'] = SiteCurrencyTable::getSiteCurrency($fields['LID']);
		self::setUserID($modifyFieldList, $fields, array('CREATED_BY', 'MODIFIED_BY'));
		self::setTimestamp($modifyFieldList, $fields, array('DATE_CREATE', 'TIMESTAMP_X'));
		self::setShortDescription($modifyFieldList, $fields);

		self::copyOldFields($modifyFieldList, $fields);
		$result->unsetField('CONDITIONS');
		$result->unsetField('ACTIONS');

		if (!empty($modifyFieldList))
			$result->modifyFields($modifyFieldList);
		unset($modifyFieldList);

		return $result;
	}

	/**
	 * Default onAfterAdd handler. Absolutely necessary.
	 *
	 * @param Main\Entity\Event $event		Event object.
	 * @return void
	 */
	public static function onAfterAdd(Main\Entity\Event $event)
	{
		$id = $event->getParameter('primary');
		$fields = $event->getParameter('fields');
		if (isset($fields['ACTIONS_LIST']))
		{
			if (!is_array($fields['ACTIONS_LIST']) && \CheckSerializedData($fields['ACTIONS_LIST']))
				$fields['ACTIONS_LIST'] = unserialize($fields['ACTIONS_LIST'], ['allowed_classes' => false]);
			if (is_array($fields['ACTIONS_LIST']))
			{
				$giftManager = Gift\Manager::getInstance();
				if ($giftManager->isContainGiftAction($fields))
				{
					if (!$giftManager->existsDiscountsWithGift())
						$giftManager->enableExistenceDiscountsWithGift();
					Gift\RelatedDataTable::fillByDiscount($fields + $id);
				}
				unset($giftManager);
			}
		}

		$specificFields = array(
			'EXECUTE_MODE' => static::resolveExecuteModeByDiscountId($id),
		);

		if (isset($fields['CONDITIONS_LIST']))
		{
			$specificFields['HAS_INDEX'] = Index\Manager::getInstance()->indexDiscount($fields + $id) ? 'Y' : 'N';
		}

		static::updateSpecificFields($id['ID'], $specificFields);
		static::updateConfigurationIfNeeded($fields, $specificFields);

		self::dropIblockCache();
	}

	/**
	 * Resolves execute mode of discount. Yes, we are getting whole discount by id. But id is necessary to know and
	 * we can do analyze only whole discount.
	 * @param $discountId
	 *
	 * @return int
	 */
	protected static function resolveExecuteModeByDiscountId($discountId)
	{
		$fields = static::getRowById($discountId);

		if (empty($fields))
		{
			return self::EXECUTE_MODE_GENERAL;
		}

		return Analyzer::getInstance()->canCalculateSeparately($fields) ?
			self::EXECUTE_MODE_SEPARATELY : self::EXECUTE_MODE_GENERAL;
	}

	/**
	 * Updates discount configuration. For example the method sets possibility of separately discount calculation.
	 *
	 * @param array $fields Fields.
	 * @param array $specificFields Specific fields which based on fields and calculated.
	 * @return void
	 */
	protected static function updateConfigurationIfNeeded(array $fields, array $specificFields)
	{
		if (
			isset($specificFields['EXECUTE_MODE']) &&
			$specificFields['EXECUTE_MODE'] == self::EXECUTE_MODE_GENERAL &&
			isset($fields['ACTIVE']) &&
			$fields['ACTIVE'] === 'Y'
		)
		{
			Config\Option::set('sale', 'discount_separately_calculation', 'N');
		}
		else
		{
			$canCalculateSeparately = Analyzer::getInstance()->canCalculateSeparatelyAllDiscount();
			Config\Option::set('sale', 'discount_separately_calculation', $canCalculateSeparately? 'Y' : 'N');
		}
	}

	/**
	 * Updates fields without ORM logic.
	 * @param $id
	 * @param array $fields
	 */
	protected static function updateSpecificFields($id, array $fields)
	{
		if (!$fields)
		{
			return;
		}

		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$tableName = static::getTableName();
		$update = $sqlHelper->prepareUpdate($tableName, $fields);

		$connection->queryExecute(
			"UPDATE {$tableName} SET {$update[0]} WHERE ID={$id}"
		);
	}

	/**
	 * Default onBeforeUpdate handler. Absolutely necessary.
	 *
	 * @param Main\Entity\Event $event		Event object.
	 * @return Main\Entity\EventResult
	 */
	public static function onBeforeUpdate(Main\Entity\Event $event)
	{
		$result = new Main\Entity\EventResult;
		$fields = $event->getParameter('fields');

		$modifyFieldList = array();
		self::setUserID($modifyFieldList, $fields, array('MODIFIED_BY'));
		self::setTimestamp($modifyFieldList, $fields, array('TIMESTAMP_X'));
		self::setShortDescription($modifyFieldList, $fields);

		self::copyOldFields($modifyFieldList, $fields);
		$result->unsetField('CONDITIONS');
		$result->unsetField('ACTIONS');

		if (!empty($modifyFieldList))
			$result->modifyFields($modifyFieldList);
		unset($modifyFieldList);

		return $result;
	}

	/**
	 * Default onAfterUpdate handler. Absolutely necessary.
	 *
	 * @param Main\Entity\Event $event		Event object.
	 * @return void
	 */
	public static function onAfterUpdate(Main\Entity\Event $event)
	{
		$id = $event->getParameter('primary');
		$fields = $event->getParameter('fields');
		if (isset($fields['ACTIVE']))
			DiscountGroupTable::changeActiveByDiscount($id['ID'], $fields['ACTIVE']);

		if (isset($fields['ACTIONS_LIST']))
		{
			if (!is_array($fields['ACTIONS_LIST']) && \CheckSerializedData($fields['ACTIONS_LIST']))
				$fields['ACTIONS_LIST'] = unserialize($fields['ACTIONS_LIST'], ['allowed_classes' => false]);
			if (is_array($fields['ACTIONS_LIST']))
			{
				Gift\RelatedDataTable::deleteByDiscount($id['ID']);
				$giftManager = Gift\Manager::getInstance();
				if ($giftManager->isContainGiftAction($fields))
				{
					if (!$giftManager->existsDiscountsWithGift())
						$giftManager->enableExistenceDiscountsWithGift();
					Gift\RelatedDataTable::fillByDiscount($fields + $id);
				}
			}
		}

		$specificFields = array(
			'EXECUTE_MODE' => static::resolveExecuteModeByDiscountId($id),
		);

		if (isset($fields['CONDITIONS_LIST']))
		{
			$specificFields['HAS_INDEX'] = Index\Manager::getInstance()->indexDiscount($fields + $id) ? 'Y' : 'N';
		}

		static::updateSpecificFields($id['ID'], $specificFields);
		static::updateConfigurationIfNeeded($fields, $specificFields);

		self::dropIblockCache();
	}

	/**
	 * Default onDelete handler. Absolutely necessary.
	 *
	 * @param Main\Entity\Event $event		Event object.
	 * @return void
	 */
	public static function onDelete(Main\Entity\Event $event)
	{
		$id = $event->getParameter('primary');
		$id = $id['ID'];
		$discount = self::getList(array(
			'select' => array('ID', 'USE_COUPONS'),
			'filter' => array('=ID' => $id)
		))->fetch();
		if (!empty($discount))
		{
			if ((string)$discount['USE_COUPONS'] === 'Y')
				self::$deleteCoupons = $discount['ID'];
		}
		unset($discount, $id);
	}

	/**
	 * Default onAfterDelete handler. Absolutely necessary.
	 *
	 * @param Main\Entity\Event $event		Event object.
	 * @return void
	 */
	public static function onAfterDelete(Main\Entity\Event $event)
	{
		$id = $event->getParameter('primary');
		$id = $id['ID'];
		DiscountEntitiesTable::deleteByDiscount($id);
		DiscountModuleTable::deleteByDiscount($id);
		DiscountGroupTable::deleteByDiscount($id);
		if (self::$deleteCoupons !== false)
		{
			DiscountCouponTable::deleteByDiscount(self::$deleteCoupons);
			self::$deleteCoupons = false;
		}
		Gift\RelatedDataTable::deleteByDiscount($id);
		Index\Manager::getInstance()->dropIndex($id);

		self::dropIblockCache();

		unset($id);
	}

	/**
	 * Set exist coupons flag for discount list.
	 *
	 * @param array $discountList			Discount ids for update.
	 * @param string $use				Value for update use coupons.
	 * @return void
	 */
	public static function setUseCoupons($discountList, $use)
	{
		if (!is_array($discountList))
			$discountList = array($discountList);
		$use = (string)$use;
		if ($use !== 'Y' && $use !== 'N')
			return;
		Main\Type\Collection::normalizeArrayValuesByInt($discountList);
		if (empty($discountList))
			return;
		$conn = Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'update '.$helper->quote(self::getTableName()).
			' set '.$helper->quote('USE_COUPONS').' = \''.$use.'\' where '.
			$helper->quote('ID').' in ('.implode(',', $discountList).')'
		);
		unset($helper, $conn);

		if($use === 'Y')
		{
			Gift\RelatedDataTable::deleteByDiscounts($discountList);
		}
	}

	/**
	 * Set exist coupons flag for all discounts.
	 *
	 * @param string $use				Value for update use coupons for all discount.
	 * @return void
	 */
	public static function setAllUseCoupons($use)
	{
		$use = (string)$use;
		if ($use !== 'Y' && $use !== 'N')
			return;
		$conn = Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'update '.$helper->quote(self::getTableName()).' set '.$helper->quote('USE_COUPONS').' = \''.$use.'\''
		);
		unset($helper, $conn);
	}

	/**
	 * Fill user id fields.
	 *
	 * @param array &$result			Modified data for add/update discount.
	 * @param array $data				Current data for add/update discount.
	 * @param array $keys				List with checked keys (userId info).
	 * @return void
	 */
	protected static function setUserID(&$result, $data, $keys)
	{
		static $currentUserID = false;
		if ($currentUserID === false)
		{
			global $USER;
			$currentUserID = (isset($USER) && $USER instanceof \CUser ? (int)$USER->getID() : null);
		}
		foreach ($keys as $oneKey)
		{
			$setField = true;
			if (array_key_exists($oneKey, $data))
				$setField = ($data[$oneKey] !== null && (int)$data[$oneKey] <= 0);

			if ($setField)
				$result[$oneKey] = $currentUserID;
		}
		unset($oneKey);
	}

	protected static function setShortDescription(&$result, array $data)
	{
		if (!empty($data['SHORT_DESCRIPTION_STRUCTURE']))
			return;
		if (empty($data['ACTIONS']) && empty($data['ACTIONS_LIST']))
			return;

		$actionConfiguration = Actions::getActionConfiguration($data);
		if (!$actionConfiguration)
			return;

		$result['SHORT_DESCRIPTION_STRUCTURE'] = $actionConfiguration;
	}

	/**
	 * Fill datetime fields.
	 *
	 * @param array &$result			Modified data for add/update discount.
	 * @param array $data				Current data for add/update discount.
	 * @param array $keys				List with checked keys (datetime info).
	 * @return void
	 */
	protected static function setTimestamp(&$result, $data, $keys)
	{
		foreach ($keys as $oneKey)
		{
			$setField = true;
			if (array_key_exists($oneKey, $data))
				$setField = ($data[$oneKey] !== null && !is_object($data[$oneKey]));

			if ($setField)
				$result[$oneKey] = new Main\Type\DateTime();
		}
		unset($oneKey);
	}

	/**
	 * Remove values from old fields conditions and actions (for compatibility with old api).
	 *
	 * @param array &$result			Modified data for add/update discount.
	 * @param array $data				Current data for add/update discount.
	 * @return void
	 */
	protected static function copyOldFields(&$result, $data)
	{
		if (!isset($data['CONDITIONS_LIST']) && isset($data['CONDITIONS']))
			$result['CONDITIONS_LIST'] = (is_array($data['CONDITIONS']) ? $data['CONDITIONS'] : unserialize($data['CONDITIONS'], ['allowed_classes' => false]));

		if (!isset($data['ACTIONS_LIST']) && isset($data['ACTIONS']))
			$result['ACTIONS_LIST'] = (is_array($data['ACTIONS']) ? $data['ACTIONS'] : unserialize($data['ACTIONS'], ['allowed_classes' => false]));
	}

	/**
	 * Temporary drop iblock cache method.
	 *
	 * @return void
	 * @throws Main\LoaderException
	 */
	private static function dropIblockCache()
	{
		if (
			!Main\ModuleManager::isModuleInstalled('bitrix24')
			|| !Main\Loader::includeModule('crm')
			|| !Main\Loader::includeModule('iblock')
		)
			return;

		$iblockId = \CCrmCatalog::GetDefaultID();
		if ($iblockId > 0)
			\CIBlock::clearIblockTagCache($iblockId);
		unset($iblockId);
	}
}