<?php

/* ORMENTITYANNOTATION:Bitrix\Highloadblock\HighloadBlockTable:highloadblock\lib\highloadblocktable.php */
namespace Bitrix\Highloadblock {
	/**
	 * HighloadBlock
	 * @see \Bitrix\Highloadblock\HighloadBlockTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Highloadblock\HighloadBlock setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Highloadblock\HighloadBlock setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Highloadblock\HighloadBlock resetName()
	 * @method \Bitrix\Highloadblock\HighloadBlock unsetName()
	 * @method \string fillName()
	 * @method \string getTableName()
	 * @method \Bitrix\Highloadblock\HighloadBlock setTableName(\string|\Bitrix\Main\DB\SqlExpression $tableName)
	 * @method bool hasTableName()
	 * @method bool isTableNameFilled()
	 * @method bool isTableNameChanged()
	 * @method \string remindActualTableName()
	 * @method \string requireTableName()
	 * @method \Bitrix\Highloadblock\HighloadBlock resetTableName()
	 * @method \Bitrix\Highloadblock\HighloadBlock unsetTableName()
	 * @method \string fillTableName()
	 * @method \int getFieldsCount()
	 * @method \int remindActualFieldsCount()
	 * @method \int requireFieldsCount()
	 * @method bool hasFieldsCount()
	 * @method bool isFieldsCountFilled()
	 * @method \Bitrix\Highloadblock\HighloadBlock unsetFieldsCount()
	 * @method \int fillFieldsCount()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang getLang()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang remindActualLang()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang requireLang()
	 * @method \Bitrix\Highloadblock\HighloadBlock setLang(\Bitrix\Highloadblock\EO_HighloadBlockLang $object)
	 * @method \Bitrix\Highloadblock\HighloadBlock resetLang()
	 * @method \Bitrix\Highloadblock\HighloadBlock unsetLang()
	 * @method bool hasLang()
	 * @method bool isLangFilled()
	 * @method bool isLangChanged()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang fillLang()
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
	 * @method \Bitrix\Highloadblock\HighloadBlock set($fieldName, $value)
	 * @method \Bitrix\Highloadblock\HighloadBlock reset($fieldName)
	 * @method \Bitrix\Highloadblock\HighloadBlock unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Highloadblock\HighloadBlock wakeUp($data)
	 */
	class EO_HighloadBlock {
		/* @var \Bitrix\Highloadblock\HighloadBlockTable */
		static public $dataClass = '\Bitrix\Highloadblock\HighloadBlockTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Highloadblock {
	/**
	 * EO_HighloadBlock_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getTableNameList()
	 * @method \string[] fillTableName()
	 * @method \int[] getFieldsCountList()
	 * @method \int[] fillFieldsCount()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang[] getLangList()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlock_Collection getLangCollection()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang_Collection fillLang()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Highloadblock\HighloadBlock $object)
	 * @method bool has(\Bitrix\Highloadblock\HighloadBlock $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Highloadblock\HighloadBlock getByPrimary($primary)
	 * @method \Bitrix\Highloadblock\HighloadBlock[] getAll()
	 * @method bool remove(\Bitrix\Highloadblock\HighloadBlock $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Highloadblock\EO_HighloadBlock_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Highloadblock\HighloadBlock current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_HighloadBlock_Collection merge(?EO_HighloadBlock_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_HighloadBlock_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Highloadblock\HighloadBlockTable */
		static public $dataClass = '\Bitrix\Highloadblock\HighloadBlockTable';
	}
}
namespace Bitrix\Highloadblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_HighloadBlock_Result exec()
	 * @method \Bitrix\Highloadblock\HighloadBlock fetchObject()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlock_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_HighloadBlock_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Highloadblock\HighloadBlock fetchObject()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlock_Collection fetchCollection()
	 */
	class EO_HighloadBlock_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Highloadblock\HighloadBlock createObject($setDefaultValues = true)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlock_Collection createCollection()
	 * @method \Bitrix\Highloadblock\HighloadBlock wakeUpObject($row)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlock_Collection wakeUpCollection($rows)
	 */
	class EO_HighloadBlock_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Highloadblock\HighloadBlockLangTable:highloadblock\lib\highloadblocklangtable.php */
namespace Bitrix\Highloadblock {
	/**
	 * EO_HighloadBlockLang
	 * @see \Bitrix\Highloadblock\HighloadBlockLangTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getLid()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string getName()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang resetName()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang unsetName()
	 * @method \string fillName()
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
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang set($fieldName, $value)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang reset($fieldName)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Highloadblock\EO_HighloadBlockLang wakeUp($data)
	 */
	class EO_HighloadBlockLang {
		/* @var \Bitrix\Highloadblock\HighloadBlockLangTable */
		static public $dataClass = '\Bitrix\Highloadblock\HighloadBlockLangTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Highloadblock {
	/**
	 * EO_HighloadBlockLang_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getLidList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Highloadblock\EO_HighloadBlockLang $object)
	 * @method bool has(\Bitrix\Highloadblock\EO_HighloadBlockLang $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang getByPrimary($primary)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang[] getAll()
	 * @method bool remove(\Bitrix\Highloadblock\EO_HighloadBlockLang $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Highloadblock\EO_HighloadBlockLang_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_HighloadBlockLang_Collection merge(?EO_HighloadBlockLang_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_HighloadBlockLang_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Highloadblock\HighloadBlockLangTable */
		static public $dataClass = '\Bitrix\Highloadblock\HighloadBlockLangTable';
	}
}
namespace Bitrix\Highloadblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_HighloadBlockLang_Result exec()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang fetchObject()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_HighloadBlockLang_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang fetchObject()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang_Collection fetchCollection()
	 */
	class EO_HighloadBlockLang_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang createObject($setDefaultValues = true)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang_Collection createCollection()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang wakeUpObject($row)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockLang_Collection wakeUpCollection($rows)
	 */
	class EO_HighloadBlockLang_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Highloadblock\HighloadBlockRightsTable:highloadblock\lib\highloadblockrightstable.php */
namespace Bitrix\Highloadblock {
	/**
	 * EO_HighloadBlockRights
	 * @see \Bitrix\Highloadblock\HighloadBlockRightsTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getHlId()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights setHlId(\int|\Bitrix\Main\DB\SqlExpression $hlId)
	 * @method bool hasHlId()
	 * @method bool isHlIdFilled()
	 * @method bool isHlIdChanged()
	 * @method \int remindActualHlId()
	 * @method \int requireHlId()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights resetHlId()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights unsetHlId()
	 * @method \int fillHlId()
	 * @method \int getTaskId()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights setTaskId(\int|\Bitrix\Main\DB\SqlExpression $taskId)
	 * @method bool hasTaskId()
	 * @method bool isTaskIdFilled()
	 * @method bool isTaskIdChanged()
	 * @method \int remindActualTaskId()
	 * @method \int requireTaskId()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights resetTaskId()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights unsetTaskId()
	 * @method \int fillTaskId()
	 * @method \string getAccessCode()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights setAccessCode(\string|\Bitrix\Main\DB\SqlExpression $accessCode)
	 * @method bool hasAccessCode()
	 * @method bool isAccessCodeFilled()
	 * @method bool isAccessCodeChanged()
	 * @method \string remindActualAccessCode()
	 * @method \string requireAccessCode()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights resetAccessCode()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights unsetAccessCode()
	 * @method \string fillAccessCode()
	 * @method \Bitrix\Main\EO_UserAccess getUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess remindActualUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess requireUserAccess()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights setUserAccess(\Bitrix\Main\EO_UserAccess $object)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights resetUserAccess()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights unsetUserAccess()
	 * @method bool hasUserAccess()
	 * @method bool isUserAccessFilled()
	 * @method bool isUserAccessChanged()
	 * @method \Bitrix\Main\EO_UserAccess fillUserAccess()
	 * @method \Bitrix\Main\EO_TaskOperation getTaskOperation()
	 * @method \Bitrix\Main\EO_TaskOperation remindActualTaskOperation()
	 * @method \Bitrix\Main\EO_TaskOperation requireTaskOperation()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights setTaskOperation(\Bitrix\Main\EO_TaskOperation $object)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights resetTaskOperation()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights unsetTaskOperation()
	 * @method bool hasTaskOperation()
	 * @method bool isTaskOperationFilled()
	 * @method bool isTaskOperationChanged()
	 * @method \Bitrix\Main\EO_TaskOperation fillTaskOperation()
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
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights set($fieldName, $value)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights reset($fieldName)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Highloadblock\EO_HighloadBlockRights wakeUp($data)
	 */
	class EO_HighloadBlockRights {
		/* @var \Bitrix\Highloadblock\HighloadBlockRightsTable */
		static public $dataClass = '\Bitrix\Highloadblock\HighloadBlockRightsTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Highloadblock {
	/**
	 * EO_HighloadBlockRights_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getHlIdList()
	 * @method \int[] fillHlId()
	 * @method \int[] getTaskIdList()
	 * @method \int[] fillTaskId()
	 * @method \string[] getAccessCodeList()
	 * @method \string[] fillAccessCode()
	 * @method \Bitrix\Main\EO_UserAccess[] getUserAccessList()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights_Collection getUserAccessCollection()
	 * @method \Bitrix\Main\EO_UserAccess_Collection fillUserAccess()
	 * @method \Bitrix\Main\EO_TaskOperation[] getTaskOperationList()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights_Collection getTaskOperationCollection()
	 * @method \Bitrix\Main\EO_TaskOperation_Collection fillTaskOperation()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Highloadblock\EO_HighloadBlockRights $object)
	 * @method bool has(\Bitrix\Highloadblock\EO_HighloadBlockRights $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights getByPrimary($primary)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights[] getAll()
	 * @method bool remove(\Bitrix\Highloadblock\EO_HighloadBlockRights $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Highloadblock\EO_HighloadBlockRights_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_HighloadBlockRights_Collection merge(?EO_HighloadBlockRights_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_HighloadBlockRights_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Highloadblock\HighloadBlockRightsTable */
		static public $dataClass = '\Bitrix\Highloadblock\HighloadBlockRightsTable';
	}
}
namespace Bitrix\Highloadblock {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_HighloadBlockRights_Result exec()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights fetchObject()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_HighloadBlockRights_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights fetchObject()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights_Collection fetchCollection()
	 */
	class EO_HighloadBlockRights_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights createObject($setDefaultValues = true)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights_Collection createCollection()
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights wakeUpObject($row)
	 * @method \Bitrix\Highloadblock\EO_HighloadBlockRights_Collection wakeUpCollection($rows)
	 */
	class EO_HighloadBlockRights_Entity extends \Bitrix\Main\ORM\Entity {}
}