<?php

/* ORMENTITYANNOTATION:Bitrix\ABTest\ABTestTable:abtest/lib/abtest.php:480ec6ffdf806883a95b7b23b3d46899 */
namespace Bitrix\ABTest {
	/**
	 * EO_ABTest
	 * @see \Bitrix\ABTest\ABTestTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\ABTest\EO_ABTest setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\ABTest\EO_ABTest setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\ABTest\EO_ABTest resetSiteId()
	 * @method \Bitrix\ABTest\EO_ABTest unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \boolean getActive()
	 * @method \Bitrix\ABTest\EO_ABTest setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\ABTest\EO_ABTest resetActive()
	 * @method \Bitrix\ABTest\EO_ABTest unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getEnabled()
	 * @method \Bitrix\ABTest\EO_ABTest setEnabled(\string|\Bitrix\Main\DB\SqlExpression $enabled)
	 * @method bool hasEnabled()
	 * @method bool isEnabledFilled()
	 * @method bool isEnabledChanged()
	 * @method \string remindActualEnabled()
	 * @method \string requireEnabled()
	 * @method \Bitrix\ABTest\EO_ABTest resetEnabled()
	 * @method \Bitrix\ABTest\EO_ABTest unsetEnabled()
	 * @method \string fillEnabled()
	 * @method \string getName()
	 * @method \Bitrix\ABTest\EO_ABTest setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\ABTest\EO_ABTest resetName()
	 * @method \Bitrix\ABTest\EO_ABTest unsetName()
	 * @method \string fillName()
	 * @method \string getDescr()
	 * @method \Bitrix\ABTest\EO_ABTest setDescr(\string|\Bitrix\Main\DB\SqlExpression $descr)
	 * @method bool hasDescr()
	 * @method bool isDescrFilled()
	 * @method bool isDescrChanged()
	 * @method \string remindActualDescr()
	 * @method \string requireDescr()
	 * @method \Bitrix\ABTest\EO_ABTest resetDescr()
	 * @method \Bitrix\ABTest\EO_ABTest unsetDescr()
	 * @method \string fillDescr()
	 * @method \string getTestData()
	 * @method \Bitrix\ABTest\EO_ABTest setTestData(\string|\Bitrix\Main\DB\SqlExpression $testData)
	 * @method bool hasTestData()
	 * @method bool isTestDataFilled()
	 * @method bool isTestDataChanged()
	 * @method \string remindActualTestData()
	 * @method \string requireTestData()
	 * @method \Bitrix\ABTest\EO_ABTest resetTestData()
	 * @method \Bitrix\ABTest\EO_ABTest unsetTestData()
	 * @method \string fillTestData()
	 * @method \Bitrix\Main\Type\DateTime getStartDate()
	 * @method \Bitrix\ABTest\EO_ABTest setStartDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $startDate)
	 * @method bool hasStartDate()
	 * @method bool isStartDateFilled()
	 * @method bool isStartDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStartDate()
	 * @method \Bitrix\Main\Type\DateTime requireStartDate()
	 * @method \Bitrix\ABTest\EO_ABTest resetStartDate()
	 * @method \Bitrix\ABTest\EO_ABTest unsetStartDate()
	 * @method \Bitrix\Main\Type\DateTime fillStartDate()
	 * @method \Bitrix\Main\Type\DateTime getStopDate()
	 * @method \Bitrix\ABTest\EO_ABTest setStopDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $stopDate)
	 * @method bool hasStopDate()
	 * @method bool isStopDateFilled()
	 * @method bool isStopDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStopDate()
	 * @method \Bitrix\Main\Type\DateTime requireStopDate()
	 * @method \Bitrix\ABTest\EO_ABTest resetStopDate()
	 * @method \Bitrix\ABTest\EO_ABTest unsetStopDate()
	 * @method \Bitrix\Main\Type\DateTime fillStopDate()
	 * @method \int getDuration()
	 * @method \Bitrix\ABTest\EO_ABTest setDuration(\int|\Bitrix\Main\DB\SqlExpression $duration)
	 * @method bool hasDuration()
	 * @method bool isDurationFilled()
	 * @method bool isDurationChanged()
	 * @method \int remindActualDuration()
	 * @method \int requireDuration()
	 * @method \Bitrix\ABTest\EO_ABTest resetDuration()
	 * @method \Bitrix\ABTest\EO_ABTest unsetDuration()
	 * @method \int fillDuration()
	 * @method \int getPortion()
	 * @method \Bitrix\ABTest\EO_ABTest setPortion(\int|\Bitrix\Main\DB\SqlExpression $portion)
	 * @method bool hasPortion()
	 * @method bool isPortionFilled()
	 * @method bool isPortionChanged()
	 * @method \int remindActualPortion()
	 * @method \int requirePortion()
	 * @method \Bitrix\ABTest\EO_ABTest resetPortion()
	 * @method \Bitrix\ABTest\EO_ABTest unsetPortion()
	 * @method \int fillPortion()
	 * @method \int getMinAmount()
	 * @method \Bitrix\ABTest\EO_ABTest setMinAmount(\int|\Bitrix\Main\DB\SqlExpression $minAmount)
	 * @method bool hasMinAmount()
	 * @method bool isMinAmountFilled()
	 * @method bool isMinAmountChanged()
	 * @method \int remindActualMinAmount()
	 * @method \int requireMinAmount()
	 * @method \Bitrix\ABTest\EO_ABTest resetMinAmount()
	 * @method \Bitrix\ABTest\EO_ABTest unsetMinAmount()
	 * @method \int fillMinAmount()
	 * @method \int getUserId()
	 * @method \Bitrix\ABTest\EO_ABTest setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\ABTest\EO_ABTest resetUserId()
	 * @method \Bitrix\ABTest\EO_ABTest unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\ABTest\EO_ABTest setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\ABTest\EO_ABTest resetUser()
	 * @method \Bitrix\ABTest\EO_ABTest unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \int getSort()
	 * @method \Bitrix\ABTest\EO_ABTest setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\ABTest\EO_ABTest resetSort()
	 * @method \Bitrix\ABTest\EO_ABTest unsetSort()
	 * @method \int fillSort()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\ABTest\EO_ABTest set($fieldName, $value)
	 * @method \Bitrix\ABTest\EO_ABTest reset($fieldName)
	 * @method \Bitrix\ABTest\EO_ABTest unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\ABTest\EO_ABTest wakeUp($data)
	 */
	class EO_ABTest {
		/* @var \Bitrix\ABTest\ABTestTable */
		static public $dataClass = '\Bitrix\ABTest\ABTestTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\ABTest {
	/**
	 * EO_ABTest_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getEnabledList()
	 * @method \string[] fillEnabled()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescrList()
	 * @method \string[] fillDescr()
	 * @method \string[] getTestDataList()
	 * @method \string[] fillTestData()
	 * @method \Bitrix\Main\Type\DateTime[] getStartDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStartDate()
	 * @method \Bitrix\Main\Type\DateTime[] getStopDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStopDate()
	 * @method \int[] getDurationList()
	 * @method \int[] fillDuration()
	 * @method \int[] getPortionList()
	 * @method \int[] fillPortion()
	 * @method \int[] getMinAmountList()
	 * @method \int[] fillMinAmount()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\ABTest\EO_ABTest_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\ABTest\EO_ABTest $object)
	 * @method bool has(\Bitrix\ABTest\EO_ABTest $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\ABTest\EO_ABTest getByPrimary($primary)
	 * @method \Bitrix\ABTest\EO_ABTest[] getAll()
	 * @method bool remove(\Bitrix\ABTest\EO_ABTest $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\ABTest\EO_ABTest_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\ABTest\EO_ABTest current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ABTest_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\ABTest\ABTestTable */
		static public $dataClass = '\Bitrix\ABTest\ABTestTable';
	}
}
namespace Bitrix\ABTest {
	/**
	 * @method static EO_ABTest_Query query()
	 * @method static EO_ABTest_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_ABTest_Result getById($id)
	 * @method static EO_ABTest_Result getList(array $parameters = array())
	 * @method static EO_ABTest_Entity getEntity()
	 * @method static \Bitrix\ABTest\EO_ABTest createObject($setDefaultValues = true)
	 * @method static \Bitrix\ABTest\EO_ABTest_Collection createCollection()
	 * @method static \Bitrix\ABTest\EO_ABTest wakeUpObject($row)
	 * @method static \Bitrix\ABTest\EO_ABTest_Collection wakeUpCollection($rows)
	 */
	class ABTestTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ABTest_Result exec()
	 * @method \Bitrix\ABTest\EO_ABTest fetchObject()
	 * @method \Bitrix\ABTest\EO_ABTest_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ABTest_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\ABTest\EO_ABTest fetchObject()
	 * @method \Bitrix\ABTest\EO_ABTest_Collection fetchCollection()
	 */
	class EO_ABTest_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\ABTest\EO_ABTest createObject($setDefaultValues = true)
	 * @method \Bitrix\ABTest\EO_ABTest_Collection createCollection()
	 * @method \Bitrix\ABTest\EO_ABTest wakeUpObject($row)
	 * @method \Bitrix\ABTest\EO_ABTest_Collection wakeUpCollection($rows)
	 */
	class EO_ABTest_Entity extends \Bitrix\Main\ORM\Entity {}
}