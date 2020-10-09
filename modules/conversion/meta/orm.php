<?php

/* ORMENTITYANNOTATION:Bitrix\Conversion\Internals\ContextTable:conversion/lib/internals/context.php:b9b58732d90e6d7934ed111cf87b78e9 */
namespace Bitrix\Conversion\Internals {
	/**
	 * EO_Context
	 * @see \Bitrix\Conversion\Internals\ContextTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Conversion\Internals\EO_Context setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSnapshot()
	 * @method \Bitrix\Conversion\Internals\EO_Context setSnapshot(\string|\Bitrix\Main\DB\SqlExpression $snapshot)
	 * @method bool hasSnapshot()
	 * @method bool isSnapshotFilled()
	 * @method bool isSnapshotChanged()
	 * @method \string remindActualSnapshot()
	 * @method \string requireSnapshot()
	 * @method \Bitrix\Conversion\Internals\EO_Context resetSnapshot()
	 * @method \Bitrix\Conversion\Internals\EO_Context unsetSnapshot()
	 * @method \string fillSnapshot()
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
	 * @method \Bitrix\Conversion\Internals\EO_Context set($fieldName, $value)
	 * @method \Bitrix\Conversion\Internals\EO_Context reset($fieldName)
	 * @method \Bitrix\Conversion\Internals\EO_Context unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Conversion\Internals\EO_Context wakeUp($data)
	 */
	class EO_Context {
		/* @var \Bitrix\Conversion\Internals\ContextTable */
		static public $dataClass = '\Bitrix\Conversion\Internals\ContextTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Conversion\Internals {
	/**
	 * EO_Context_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSnapshotList()
	 * @method \string[] fillSnapshot()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Conversion\Internals\EO_Context $object)
	 * @method bool has(\Bitrix\Conversion\Internals\EO_Context $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Conversion\Internals\EO_Context getByPrimary($primary)
	 * @method \Bitrix\Conversion\Internals\EO_Context[] getAll()
	 * @method bool remove(\Bitrix\Conversion\Internals\EO_Context $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Conversion\Internals\EO_Context_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Conversion\Internals\EO_Context current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Context_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Conversion\Internals\ContextTable */
		static public $dataClass = '\Bitrix\Conversion\Internals\ContextTable';
	}
}
namespace Bitrix\Conversion\Internals {
	/**
	 * @method static EO_Context_Query query()
	 * @method static EO_Context_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Context_Result getById($id)
	 * @method static EO_Context_Result getList(array $parameters = array())
	 * @method static EO_Context_Entity getEntity()
	 * @method static \Bitrix\Conversion\Internals\EO_Context createObject($setDefaultValues = true)
	 * @method static \Bitrix\Conversion\Internals\EO_Context_Collection createCollection()
	 * @method static \Bitrix\Conversion\Internals\EO_Context wakeUpObject($row)
	 * @method static \Bitrix\Conversion\Internals\EO_Context_Collection wakeUpCollection($rows)
	 */
	class ContextTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Context_Result exec()
	 * @method \Bitrix\Conversion\Internals\EO_Context fetchObject()
	 * @method \Bitrix\Conversion\Internals\EO_Context_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Context_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Conversion\Internals\EO_Context fetchObject()
	 * @method \Bitrix\Conversion\Internals\EO_Context_Collection fetchCollection()
	 */
	class EO_Context_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Conversion\Internals\EO_Context createObject($setDefaultValues = true)
	 * @method \Bitrix\Conversion\Internals\EO_Context_Collection createCollection()
	 * @method \Bitrix\Conversion\Internals\EO_Context wakeUpObject($row)
	 * @method \Bitrix\Conversion\Internals\EO_Context_Collection wakeUpCollection($rows)
	 */
	class EO_Context_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Conversion\Internals\ContextAttributeTable:conversion/lib/internals/contextattribute.php:533082027c664f7f0b10ade071746963 */
namespace Bitrix\Conversion\Internals {
	/**
	 * EO_ContextAttribute
	 * @see \Bitrix\Conversion\Internals\ContextAttributeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getContextId()
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute setContextId(\int|\Bitrix\Main\DB\SqlExpression $contextId)
	 * @method bool hasContextId()
	 * @method bool isContextIdFilled()
	 * @method bool isContextIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string getValue()
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
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
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute set($fieldName, $value)
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute reset($fieldName)
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute wakeUp($data)
	 */
	class EO_ContextAttribute {
		/* @var \Bitrix\Conversion\Internals\ContextAttributeTable */
		static public $dataClass = '\Bitrix\Conversion\Internals\ContextAttributeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Conversion\Internals {
	/**
	 * EO_ContextAttribute_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getContextIdList()
	 * @method \string[] getNameList()
	 * @method \string[] getValueList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Conversion\Internals\EO_ContextAttribute $object)
	 * @method bool has(\Bitrix\Conversion\Internals\EO_ContextAttribute $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute getByPrimary($primary)
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute[] getAll()
	 * @method bool remove(\Bitrix\Conversion\Internals\EO_ContextAttribute $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ContextAttribute_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Conversion\Internals\ContextAttributeTable */
		static public $dataClass = '\Bitrix\Conversion\Internals\ContextAttributeTable';
	}
}
namespace Bitrix\Conversion\Internals {
	/**
	 * @method static EO_ContextAttribute_Query query()
	 * @method static EO_ContextAttribute_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_ContextAttribute_Result getById($id)
	 * @method static EO_ContextAttribute_Result getList(array $parameters = array())
	 * @method static EO_ContextAttribute_Entity getEntity()
	 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute createObject($setDefaultValues = true)
	 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute_Collection createCollection()
	 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute wakeUpObject($row)
	 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute_Collection wakeUpCollection($rows)
	 */
	class ContextAttributeTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ContextAttribute_Result exec()
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute fetchObject()
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ContextAttribute_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute fetchObject()
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute_Collection fetchCollection()
	 */
	class EO_ContextAttribute_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute createObject($setDefaultValues = true)
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute_Collection createCollection()
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute wakeUpObject($row)
	 * @method \Bitrix\Conversion\Internals\EO_ContextAttribute_Collection wakeUpCollection($rows)
	 */
	class EO_ContextAttribute_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Conversion\Internals\ContextCounterDayTable:conversion/lib/internals/contextcounterday.php:aa6fe64d8291e0bdbd9ea18b33d8b1bf */
namespace Bitrix\Conversion\Internals {
	/**
	 * EO_ContextCounterDay
	 * @see \Bitrix\Conversion\Internals\ContextCounterDayTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date getDay()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay setDay(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $day)
	 * @method bool hasDay()
	 * @method bool isDayFilled()
	 * @method bool isDayChanged()
	 * @method \int getContextId()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay setContextId(\int|\Bitrix\Main\DB\SqlExpression $contextId)
	 * @method bool hasContextId()
	 * @method bool isContextIdFilled()
	 * @method bool isContextIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \float getValue()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay setValue(\float|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \float remindActualValue()
	 * @method \float requireValue()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay resetValue()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay unsetValue()
	 * @method \float fillValue()
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
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay set($fieldName, $value)
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay reset($fieldName)
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay wakeUp($data)
	 */
	class EO_ContextCounterDay {
		/* @var \Bitrix\Conversion\Internals\ContextCounterDayTable */
		static public $dataClass = '\Bitrix\Conversion\Internals\ContextCounterDayTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Conversion\Internals {
	/**
	 * EO_ContextCounterDay_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date[] getDayList()
	 * @method \int[] getContextIdList()
	 * @method \string[] getNameList()
	 * @method \float[] getValueList()
	 * @method \float[] fillValue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Conversion\Internals\EO_ContextCounterDay $object)
	 * @method bool has(\Bitrix\Conversion\Internals\EO_ContextCounterDay $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay getByPrimary($primary)
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay[] getAll()
	 * @method bool remove(\Bitrix\Conversion\Internals\EO_ContextCounterDay $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ContextCounterDay_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Conversion\Internals\ContextCounterDayTable */
		static public $dataClass = '\Bitrix\Conversion\Internals\ContextCounterDayTable';
	}
}
namespace Bitrix\Conversion\Internals {
	/**
	 * @method static EO_ContextCounterDay_Query query()
	 * @method static EO_ContextCounterDay_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_ContextCounterDay_Result getById($id)
	 * @method static EO_ContextCounterDay_Result getList(array $parameters = array())
	 * @method static EO_ContextCounterDay_Entity getEntity()
	 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay createObject($setDefaultValues = true)
	 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay_Collection createCollection()
	 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay wakeUpObject($row)
	 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay_Collection wakeUpCollection($rows)
	 */
	class ContextCounterDayTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ContextCounterDay_Result exec()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay fetchObject()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ContextCounterDay_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay fetchObject()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay_Collection fetchCollection()
	 */
	class EO_ContextCounterDay_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay createObject($setDefaultValues = true)
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay_Collection createCollection()
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay wakeUpObject($row)
	 * @method \Bitrix\Conversion\Internals\EO_ContextCounterDay_Collection wakeUpCollection($rows)
	 */
	class EO_ContextCounterDay_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Conversion\Internals\ContextEntityItemTable:conversion/lib/internals/contextentityitem.php:9e5fb95d49517c44d9682448a897aebc */
namespace Bitrix\Conversion\Internals {
	/**
	 * EO_ContextEntityItem
	 * @see \Bitrix\Conversion\Internals\ContextEntityItemTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getContextId()
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem setContextId(\int|\Bitrix\Main\DB\SqlExpression $contextId)
	 * @method bool hasContextId()
	 * @method bool isContextIdFilled()
	 * @method bool isContextIdChanged()
	 * @method \string getEntity()
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string getItem()
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem setItem(\string|\Bitrix\Main\DB\SqlExpression $item)
	 * @method bool hasItem()
	 * @method bool isItemFilled()
	 * @method bool isItemChanged()
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
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem set($fieldName, $value)
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem reset($fieldName)
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem wakeUp($data)
	 */
	class EO_ContextEntityItem {
		/* @var \Bitrix\Conversion\Internals\ContextEntityItemTable */
		static public $dataClass = '\Bitrix\Conversion\Internals\ContextEntityItemTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Conversion\Internals {
	/**
	 * EO_ContextEntityItem_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getContextIdList()
	 * @method \string[] getEntityList()
	 * @method \string[] getItemList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Conversion\Internals\EO_ContextEntityItem $object)
	 * @method bool has(\Bitrix\Conversion\Internals\EO_ContextEntityItem $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem getByPrimary($primary)
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem[] getAll()
	 * @method bool remove(\Bitrix\Conversion\Internals\EO_ContextEntityItem $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ContextEntityItem_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Conversion\Internals\ContextEntityItemTable */
		static public $dataClass = '\Bitrix\Conversion\Internals\ContextEntityItemTable';
	}
}
namespace Bitrix\Conversion\Internals {
	/**
	 * @method static EO_ContextEntityItem_Query query()
	 * @method static EO_ContextEntityItem_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_ContextEntityItem_Result getById($id)
	 * @method static EO_ContextEntityItem_Result getList(array $parameters = array())
	 * @method static EO_ContextEntityItem_Entity getEntity()
	 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem createObject($setDefaultValues = true)
	 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem_Collection createCollection()
	 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem wakeUpObject($row)
	 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem_Collection wakeUpCollection($rows)
	 */
	class ContextEntityItemTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ContextEntityItem_Result exec()
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem fetchObject()
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ContextEntityItem_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem fetchObject()
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem_Collection fetchCollection()
	 */
	class EO_ContextEntityItem_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem createObject($setDefaultValues = true)
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem_Collection createCollection()
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem wakeUpObject($row)
	 * @method \Bitrix\Conversion\Internals\EO_ContextEntityItem_Collection wakeUpCollection($rows)
	 */
	class EO_ContextEntityItem_Entity extends \Bitrix\Main\ORM\Entity {}
}