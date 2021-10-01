<?php

/* ORMENTITYANNOTATION:Bitrix\B24connector\ButtonTable:b24connector/lib/buttontable.php:74336798ecc01b6c72010172351340ad */
namespace Bitrix\B24connector {
	/**
	 * EO_Button
	 * @see \Bitrix\B24connector\ButtonTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\B24connector\EO_Button setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getAppId()
	 * @method \Bitrix\B24connector\EO_Button setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int remindActualAppId()
	 * @method \int requireAppId()
	 * @method \Bitrix\B24connector\EO_Button resetAppId()
	 * @method \Bitrix\B24connector\EO_Button unsetAppId()
	 * @method \int fillAppId()
	 * @method \Bitrix\Main\Type\DateTime getAddDate()
	 * @method \Bitrix\B24connector\EO_Button setAddDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $addDate)
	 * @method bool hasAddDate()
	 * @method bool isAddDateFilled()
	 * @method bool isAddDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualAddDate()
	 * @method \Bitrix\Main\Type\DateTime requireAddDate()
	 * @method \Bitrix\B24connector\EO_Button resetAddDate()
	 * @method \Bitrix\B24connector\EO_Button unsetAddDate()
	 * @method \Bitrix\Main\Type\DateTime fillAddDate()
	 * @method \int getAddBy()
	 * @method \Bitrix\B24connector\EO_Button setAddBy(\int|\Bitrix\Main\DB\SqlExpression $addBy)
	 * @method bool hasAddBy()
	 * @method bool isAddByFilled()
	 * @method bool isAddByChanged()
	 * @method \int remindActualAddBy()
	 * @method \int requireAddBy()
	 * @method \Bitrix\B24connector\EO_Button resetAddBy()
	 * @method \Bitrix\B24connector\EO_Button unsetAddBy()
	 * @method \int fillAddBy()
	 * @method \string getName()
	 * @method \Bitrix\B24connector\EO_Button setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\B24connector\EO_Button resetName()
	 * @method \Bitrix\B24connector\EO_Button unsetName()
	 * @method \string fillName()
	 * @method \string getScript()
	 * @method \Bitrix\B24connector\EO_Button setScript(\string|\Bitrix\Main\DB\SqlExpression $script)
	 * @method bool hasScript()
	 * @method bool isScriptFilled()
	 * @method bool isScriptChanged()
	 * @method \string remindActualScript()
	 * @method \string requireScript()
	 * @method \Bitrix\B24connector\EO_Button resetScript()
	 * @method \Bitrix\B24connector\EO_Button unsetScript()
	 * @method \string fillScript()
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
	 * @method \Bitrix\B24connector\EO_Button set($fieldName, $value)
	 * @method \Bitrix\B24connector\EO_Button reset($fieldName)
	 * @method \Bitrix\B24connector\EO_Button unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\B24connector\EO_Button wakeUp($data)
	 */
	class EO_Button {
		/* @var \Bitrix\B24connector\ButtonTable */
		static public $dataClass = '\Bitrix\B24connector\ButtonTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\B24connector {
	/**
	 * EO_Button_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getAppIdList()
	 * @method \int[] fillAppId()
	 * @method \Bitrix\Main\Type\DateTime[] getAddDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillAddDate()
	 * @method \int[] getAddByList()
	 * @method \int[] fillAddBy()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getScriptList()
	 * @method \string[] fillScript()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\B24connector\EO_Button $object)
	 * @method bool has(\Bitrix\B24connector\EO_Button $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\B24connector\EO_Button getByPrimary($primary)
	 * @method \Bitrix\B24connector\EO_Button[] getAll()
	 * @method bool remove(\Bitrix\B24connector\EO_Button $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\B24connector\EO_Button_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\B24connector\EO_Button current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Button_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\B24connector\ButtonTable */
		static public $dataClass = '\Bitrix\B24connector\ButtonTable';
	}
}
namespace Bitrix\B24connector {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Button_Result exec()
	 * @method \Bitrix\B24connector\EO_Button fetchObject()
	 * @method \Bitrix\B24connector\EO_Button_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Button_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\B24connector\EO_Button fetchObject()
	 * @method \Bitrix\B24connector\EO_Button_Collection fetchCollection()
	 */
	class EO_Button_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\B24connector\EO_Button createObject($setDefaultValues = true)
	 * @method \Bitrix\B24connector\EO_Button_Collection createCollection()
	 * @method \Bitrix\B24connector\EO_Button wakeUpObject($row)
	 * @method \Bitrix\B24connector\EO_Button_Collection wakeUpCollection($rows)
	 */
	class EO_Button_Entity extends \Bitrix\Main\ORM\Entity {}
}