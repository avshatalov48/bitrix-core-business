<?php

/* ORMENTITYANNOTATION:Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable:calendar/lib/openevents/internals/openeventcategorytable.php */
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * OpenEventCategory
	 * @see \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetName()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetName()
	 * @method \string fillName()
	 * @method \int getCreatorId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setCreatorId(\int|\Bitrix\Main\DB\SqlExpression $creatorId)
	 * @method bool hasCreatorId()
	 * @method bool isCreatorIdFilled()
	 * @method bool isCreatorIdChanged()
	 * @method \int remindActualCreatorId()
	 * @method \int requireCreatorId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetCreatorId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetCreatorId()
	 * @method \int fillCreatorId()
	 * @method \boolean getClosed()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setClosed(\boolean|\Bitrix\Main\DB\SqlExpression $closed)
	 * @method bool hasClosed()
	 * @method bool isClosedFilled()
	 * @method bool isClosedChanged()
	 * @method \boolean remindActualClosed()
	 * @method \boolean requireClosed()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetClosed()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetClosed()
	 * @method \boolean fillClosed()
	 * @method \string getDescription()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetDescription()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getAccessCodes()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setAccessCodes(\string|\Bitrix\Main\DB\SqlExpression $accessCodes)
	 * @method bool hasAccessCodes()
	 * @method bool isAccessCodesFilled()
	 * @method bool isAccessCodesChanged()
	 * @method \string remindActualAccessCodes()
	 * @method \string requireAccessCodes()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetAccessCodes()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetAccessCodes()
	 * @method \string fillAccessCodes()
	 * @method \boolean getDeleted()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setDeleted(\boolean|\Bitrix\Main\DB\SqlExpression $deleted)
	 * @method bool hasDeleted()
	 * @method bool isDeletedFilled()
	 * @method bool isDeletedChanged()
	 * @method \boolean remindActualDeleted()
	 * @method \boolean requireDeleted()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetDeleted()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetDeleted()
	 * @method \boolean fillDeleted()
	 * @method \int getChannelId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setChannelId(\int|\Bitrix\Main\DB\SqlExpression $channelId)
	 * @method bool hasChannelId()
	 * @method bool isChannelIdFilled()
	 * @method bool isChannelIdChanged()
	 * @method \int remindActualChannelId()
	 * @method \int requireChannelId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetChannelId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetChannelId()
	 * @method \int fillChannelId()
	 * @method \int getEventsCount()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setEventsCount(\int|\Bitrix\Main\DB\SqlExpression $eventsCount)
	 * @method bool hasEventsCount()
	 * @method bool isEventsCountFilled()
	 * @method bool isEventsCountChanged()
	 * @method \int remindActualEventsCount()
	 * @method \int requireEventsCount()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetEventsCount()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetEventsCount()
	 * @method \int fillEventsCount()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetDateCreate()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getLastActivity()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory setLastActivity(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastActivity)
	 * @method bool hasLastActivity()
	 * @method bool isLastActivityFilled()
	 * @method bool isLastActivityChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastActivity()
	 * @method \Bitrix\Main\Type\DateTime requireLastActivity()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory resetLastActivity()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unsetLastActivity()
	 * @method \Bitrix\Main\Type\DateTime fillLastActivity()
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
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory set($fieldName, $value)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory reset($fieldName)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory wakeUp($data)
	 */
	class EO_OpenEventCategory {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * OpenEventCategoryCollection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getCreatorIdList()
	 * @method \int[] fillCreatorId()
	 * @method \boolean[] getClosedList()
	 * @method \boolean[] fillClosed()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getAccessCodesList()
	 * @method \string[] fillAccessCodes()
	 * @method \boolean[] getDeletedList()
	 * @method \boolean[] fillDeleted()
	 * @method \int[] getChannelIdList()
	 * @method \int[] fillChannelId()
	 * @method \int[] getEventsCountList()
	 * @method \int[] fillEventsCount()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getLastActivityList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastActivity()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory $object)
	 * @method bool has(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory getByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory[] getAll()
	 * @method bool remove(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection merge(?\Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection $collection)
	 * @method bool isEmpty()
	 */
	class EO_OpenEventCategory_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable';
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_OpenEventCategory_Result exec()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_OpenEventCategory_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection fetchCollection()
	 */
	class EO_OpenEventCategory_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection createCollection()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory wakeUpObject($row)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection wakeUpCollection($rows)
	 */
	class EO_OpenEventCategory_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable:calendar/lib/openevents/internals/openeventcategoryattendeetable.php */
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * OpenEventCategoryAttendee
	 * @see \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee resetUserId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee setCategoryId(\int|\Bitrix\Main\DB\SqlExpression $categoryId)
	 * @method bool hasCategoryId()
	 * @method bool isCategoryIdFilled()
	 * @method bool isCategoryIdChanged()
	 * @method \int remindActualCategoryId()
	 * @method \int requireCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee resetCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee unsetCategoryId()
	 * @method \int fillCategoryId()
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
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee set($fieldName, $value)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee reset($fieldName)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee wakeUp($data)
	 */
	class EO_OpenEventCategoryAttendee {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * OpenEventCategoryAttendeeCollection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getCategoryIdList()
	 * @method \int[] fillCategoryId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee $object)
	 * @method bool has(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee getByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee[] getAll()
	 * @method bool remove(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection merge(?\Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection $collection)
	 * @method bool isEmpty()
	 */
	class EO_OpenEventCategoryAttendee_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable';
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_OpenEventCategoryAttendee_Result exec()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_OpenEventCategoryAttendee_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection fetchCollection()
	 */
	class EO_OpenEventCategoryAttendee_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection createCollection()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee wakeUpObject($row)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection wakeUpCollection($rows)
	 */
	class EO_OpenEventCategoryAttendee_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable:calendar/lib/openevents/internals/openeventoptiontable.php */
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * OpenEventOption
	 * @see \Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEventId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption setEventId(\int|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \int remindActualEventId()
	 * @method \int requireEventId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption resetEventId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption unsetEventId()
	 * @method \int fillEventId()
	 * @method \int getCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption setCategoryId(\int|\Bitrix\Main\DB\SqlExpression $categoryId)
	 * @method bool hasCategoryId()
	 * @method bool isCategoryIdFilled()
	 * @method bool isCategoryIdChanged()
	 * @method \int remindActualCategoryId()
	 * @method \int requireCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption resetCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption unsetCategoryId()
	 * @method \int fillCategoryId()
	 * @method \int getThreadId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption setThreadId(\int|\Bitrix\Main\DB\SqlExpression $threadId)
	 * @method bool hasThreadId()
	 * @method bool isThreadIdFilled()
	 * @method bool isThreadIdChanged()
	 * @method \int remindActualThreadId()
	 * @method \int requireThreadId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption resetThreadId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption unsetThreadId()
	 * @method \int fillThreadId()
	 * @method \string getOptions()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption setOptions(\string|\Bitrix\Main\DB\SqlExpression $options)
	 * @method bool hasOptions()
	 * @method bool isOptionsFilled()
	 * @method bool isOptionsChanged()
	 * @method \string remindActualOptions()
	 * @method \string requireOptions()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption resetOptions()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption unsetOptions()
	 * @method \string fillOptions()
	 * @method \int getAttendeesCount()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption setAttendeesCount(\int|\Bitrix\Main\DB\SqlExpression $attendeesCount)
	 * @method bool hasAttendeesCount()
	 * @method bool isAttendeesCountFilled()
	 * @method bool isAttendeesCountChanged()
	 * @method \int remindActualAttendeesCount()
	 * @method \int requireAttendeesCount()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption resetAttendeesCount()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption unsetAttendeesCount()
	 * @method \int fillAttendeesCount()
	 * @method \Bitrix\Calendar\Internals\EO_Event getEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event remindActualEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event requireEvent()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption setEvent(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption resetEvent()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption unsetEvent()
	 * @method bool hasEvent()
	 * @method bool isEventFilled()
	 * @method bool isEventChanged()
	 * @method \Bitrix\Calendar\Internals\EO_Event fillEvent()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory getCategory()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory remindActualCategory()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory requireCategory()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption setCategory(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory $object)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption resetCategory()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption unsetCategory()
	 * @method bool hasCategory()
	 * @method bool isCategoryFilled()
	 * @method bool isCategoryChanged()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory fillCategory()
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
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption set($fieldName, $value)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption reset($fieldName)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption wakeUp($data)
	 */
	class EO_OpenEventOption {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * EO_OpenEventOption_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEventIdList()
	 * @method \int[] fillEventId()
	 * @method \int[] getCategoryIdList()
	 * @method \int[] fillCategoryId()
	 * @method \int[] getThreadIdList()
	 * @method \int[] fillThreadId()
	 * @method \string[] getOptionsList()
	 * @method \string[] fillOptions()
	 * @method \int[] getAttendeesCountList()
	 * @method \int[] fillAttendeesCount()
	 * @method \Bitrix\Calendar\Internals\EO_Event[] getEventList()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection getEventCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection fillEvent()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory[] getCategoryList()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection getCategoryCollection()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection fillCategory()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption $object)
	 * @method bool has(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption getByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption[] getAll()
	 * @method bool remove(\Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection merge(?\Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_OpenEventOption_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable';
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_OpenEventOption_Result exec()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_OpenEventOption_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection fetchCollection()
	 */
	class EO_OpenEventOption_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection createCollection()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption wakeUpObject($row)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection wakeUpCollection($rows)
	 */
	class EO_OpenEventOption_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryBannedTable:calendar/lib/openevents/internals/openeventcategorybannedtable.php */
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * EO_OpenEventCategoryBanned
	 * @see \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryBannedTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned resetUserId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned setCategoryId(\int|\Bitrix\Main\DB\SqlExpression $categoryId)
	 * @method bool hasCategoryId()
	 * @method bool isCategoryIdFilled()
	 * @method bool isCategoryIdChanged()
	 * @method \int remindActualCategoryId()
	 * @method \int requireCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned resetCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned unsetCategoryId()
	 * @method \int fillCategoryId()
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
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned set($fieldName, $value)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned reset($fieldName)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned wakeUp($data)
	 */
	class EO_OpenEventCategoryBanned {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryBannedTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryBannedTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * EO_OpenEventCategoryBanned_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getCategoryIdList()
	 * @method \int[] fillCategoryId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned $object)
	 * @method bool has(\Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned getByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned[] getAll()
	 * @method bool remove(\Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned_Collection merge(?\Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_OpenEventCategoryBanned_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryBannedTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryBannedTable';
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_OpenEventCategoryBanned_Result exec()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_OpenEventCategoryBanned_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned_Collection fetchCollection()
	 */
	class EO_OpenEventCategoryBanned_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned_Collection createCollection()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned wakeUpObject($row)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned_Collection wakeUpCollection($rows)
	 */
	class EO_OpenEventCategoryBanned_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryMutedTable:calendar/lib/openevents/internals/openeventcategorymutedtable.php */
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * EO_OpenEventCategoryMuted
	 * @see \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryMutedTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted resetUserId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted setCategoryId(\int|\Bitrix\Main\DB\SqlExpression $categoryId)
	 * @method bool hasCategoryId()
	 * @method bool isCategoryIdFilled()
	 * @method bool isCategoryIdChanged()
	 * @method \int remindActualCategoryId()
	 * @method \int requireCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted resetCategoryId()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted unsetCategoryId()
	 * @method \int fillCategoryId()
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
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted set($fieldName, $value)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted reset($fieldName)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted wakeUp($data)
	 */
	class EO_OpenEventCategoryMuted {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryMutedTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryMutedTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * EO_OpenEventCategoryMuted_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getCategoryIdList()
	 * @method \int[] fillCategoryId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted $object)
	 * @method bool has(\Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted getByPrimary($primary)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted[] getAll()
	 * @method bool remove(\Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted_Collection merge(?\Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_OpenEventCategoryMuted_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryMutedTable */
		static public $dataClass = '\Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryMutedTable';
	}
}
namespace Bitrix\Calendar\OpenEvents\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_OpenEventCategoryMuted_Result exec()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_OpenEventCategoryMuted_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted fetchObject()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted_Collection fetchCollection()
	 */
	class EO_OpenEventCategoryMuted_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted_Collection createCollection()
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted wakeUpObject($row)
	 * @method \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted_Collection wakeUpCollection($rows)
	 */
	class EO_OpenEventCategoryMuted_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\SharingLinkTable:calendar/lib/internals/sharinglinktable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SharingLink
	 * @see \Bitrix\Calendar\Internals\SharingLinkTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getObjectId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setObjectId(\int|\Bitrix\Main\DB\SqlExpression $objectId)
	 * @method bool hasObjectId()
	 * @method bool isObjectIdFilled()
	 * @method bool isObjectIdChanged()
	 * @method \int remindActualObjectId()
	 * @method \int requireObjectId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetObjectId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetObjectId()
	 * @method \int fillObjectId()
	 * @method \string getObjectType()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setObjectType(\string|\Bitrix\Main\DB\SqlExpression $objectType)
	 * @method bool hasObjectType()
	 * @method bool isObjectTypeFilled()
	 * @method bool isObjectTypeChanged()
	 * @method \string remindActualObjectType()
	 * @method \string requireObjectType()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetObjectType()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetObjectType()
	 * @method \string fillObjectType()
	 * @method \string getHash()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setHash(\string|\Bitrix\Main\DB\SqlExpression $hash)
	 * @method bool hasHash()
	 * @method bool isHashFilled()
	 * @method bool isHashChanged()
	 * @method \string remindActualHash()
	 * @method \string requireHash()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetHash()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetHash()
	 * @method \string fillHash()
	 * @method \string getOptions()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setOptions(\string|\Bitrix\Main\DB\SqlExpression $options)
	 * @method bool hasOptions()
	 * @method bool isOptionsFilled()
	 * @method bool isOptionsChanged()
	 * @method \string remindActualOptions()
	 * @method \string requireOptions()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetOptions()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetOptions()
	 * @method \string fillOptions()
	 * @method \boolean getActive()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetActive()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetActive()
	 * @method \boolean fillActive()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateExpire()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setDateExpire(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateExpire)
	 * @method bool hasDateExpire()
	 * @method bool isDateExpireFilled()
	 * @method bool isDateExpireChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateExpire()
	 * @method \Bitrix\Main\Type\DateTime requireDateExpire()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetDateExpire()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetDateExpire()
	 * @method \Bitrix\Main\Type\DateTime fillDateExpire()
	 * @method \int getHostId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setHostId(\int|\Bitrix\Main\DB\SqlExpression $hostId)
	 * @method bool hasHostId()
	 * @method bool isHostIdFilled()
	 * @method bool isHostIdChanged()
	 * @method \int remindActualHostId()
	 * @method \int requireHostId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetHostId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetHostId()
	 * @method \int fillHostId()
	 * @method \int getOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setOwnerId(\int|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \int remindActualOwnerId()
	 * @method \int requireOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetOwnerId()
	 * @method \int fillOwnerId()
	 * @method \string getConferenceId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setConferenceId(\string|\Bitrix\Main\DB\SqlExpression $conferenceId)
	 * @method bool hasConferenceId()
	 * @method bool isConferenceIdFilled()
	 * @method bool isConferenceIdChanged()
	 * @method \string remindActualConferenceId()
	 * @method \string requireConferenceId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetConferenceId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetConferenceId()
	 * @method \string fillConferenceId()
	 * @method \string getParentLinkHash()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setParentLinkHash(\string|\Bitrix\Main\DB\SqlExpression $parentLinkHash)
	 * @method bool hasParentLinkHash()
	 * @method bool isParentLinkHashFilled()
	 * @method bool isParentLinkHashChanged()
	 * @method \string remindActualParentLinkHash()
	 * @method \string requireParentLinkHash()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetParentLinkHash()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetParentLinkHash()
	 * @method \string fillParentLinkHash()
	 * @method null|\int getContactId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setContactId(null|\int|\Bitrix\Main\DB\SqlExpression $contactId)
	 * @method bool hasContactId()
	 * @method bool isContactIdFilled()
	 * @method bool isContactIdChanged()
	 * @method null|\int remindActualContactId()
	 * @method null|\int requireContactId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetContactId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetContactId()
	 * @method null|\int fillContactId()
	 * @method null|\int getContactType()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setContactType(null|\int|\Bitrix\Main\DB\SqlExpression $contactType)
	 * @method bool hasContactType()
	 * @method bool isContactTypeFilled()
	 * @method bool isContactTypeChanged()
	 * @method null|\int remindActualContactType()
	 * @method null|\int requireContactType()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetContactType()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetContactType()
	 * @method null|\int fillContactType()
	 * @method \string getMembersHash()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setMembersHash(\string|\Bitrix\Main\DB\SqlExpression $membersHash)
	 * @method bool hasMembersHash()
	 * @method bool isMembersHashFilled()
	 * @method bool isMembersHashChanged()
	 * @method \string remindActualMembersHash()
	 * @method \string requireMembersHash()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetMembersHash()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetMembersHash()
	 * @method \string fillMembersHash()
	 * @method \int getFrequentUse()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink setFrequentUse(\int|\Bitrix\Main\DB\SqlExpression $frequentUse)
	 * @method bool hasFrequentUse()
	 * @method bool isFrequentUseFilled()
	 * @method bool isFrequentUseChanged()
	 * @method \int remindActualFrequentUse()
	 * @method \int requireFrequentUse()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetFrequentUse()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetFrequentUse()
	 * @method \int fillFrequentUse()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection getMembers()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection requireMembers()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection fillMembers()
	 * @method bool hasMembers()
	 * @method bool isMembersFilled()
	 * @method bool isMembersChanged()
	 * @method void addToMembers(\Bitrix\Calendar\Internals\EO_SharingLinkMember $sharingLinkMember)
	 * @method void removeFromMembers(\Bitrix\Calendar\Internals\EO_SharingLinkMember $sharingLinkMember)
	 * @method void removeAllMembers()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink resetMembers()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unsetMembers()
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
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_SharingLink wakeUp($data)
	 */
	class EO_SharingLink {
		/* @var \Bitrix\Calendar\Internals\SharingLinkTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SharingLinkTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SharingLink_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getObjectIdList()
	 * @method \int[] fillObjectId()
	 * @method \string[] getObjectTypeList()
	 * @method \string[] fillObjectType()
	 * @method \string[] getHashList()
	 * @method \string[] fillHash()
	 * @method \string[] getOptionsList()
	 * @method \string[] fillOptions()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateExpireList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateExpire()
	 * @method \int[] getHostIdList()
	 * @method \int[] fillHostId()
	 * @method \int[] getOwnerIdList()
	 * @method \int[] fillOwnerId()
	 * @method \string[] getConferenceIdList()
	 * @method \string[] fillConferenceId()
	 * @method \string[] getParentLinkHashList()
	 * @method \string[] fillParentLinkHash()
	 * @method null|\int[] getContactIdList()
	 * @method null|\int[] fillContactId()
	 * @method null|\int[] getContactTypeList()
	 * @method null|\int[] fillContactType()
	 * @method \string[] getMembersHashList()
	 * @method \string[] fillMembersHash()
	 * @method \int[] getFrequentUseList()
	 * @method \int[] fillFrequentUse()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection[] getMembersList()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection getMembersCollection()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection fillMembers()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_SharingLink $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_SharingLink $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_SharingLink $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_SharingLink_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink_Collection merge(?\Bitrix\Calendar\Internals\EO_SharingLink_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_SharingLink_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\SharingLinkTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SharingLinkTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SharingLink_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SharingLink_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink_Collection fetchCollection()
	 */
	class EO_SharingLink_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink_Collection wakeUpCollection($rows)
	 */
	class EO_SharingLink_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\EventTable:calendar/lib/internals/eventtable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Event
	 * @see \Bitrix\Calendar\Internals\EventTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetParentId()
	 * @method \int fillParentId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Calendar\Internals\EO_Event setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetActive()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getDeleted()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDeleted(\boolean|\Bitrix\Main\DB\SqlExpression $deleted)
	 * @method bool hasDeleted()
	 * @method bool isDeletedFilled()
	 * @method bool isDeletedChanged()
	 * @method \boolean remindActualDeleted()
	 * @method \boolean requireDeleted()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDeleted()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDeleted()
	 * @method \boolean fillDeleted()
	 * @method \string getCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Event setCalType(\string|\Bitrix\Main\DB\SqlExpression $calType)
	 * @method bool hasCalType()
	 * @method bool isCalTypeFilled()
	 * @method bool isCalTypeChanged()
	 * @method \string remindActualCalType()
	 * @method \string requireCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetCalType()
	 * @method \string fillCalType()
	 * @method \int getOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setOwnerId(\int|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \int remindActualOwnerId()
	 * @method \int requireOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetOwnerId()
	 * @method \int fillOwnerId()
	 * @method \string getName()
	 * @method \Bitrix\Calendar\Internals\EO_Event setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetName()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetName()
	 * @method \string fillName()
	 * @method \Bitrix\Main\Type\DateTime getDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateFrom)
	 * @method bool hasDateFrom()
	 * @method bool isDateFromFilled()
	 * @method bool isDateFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateFrom()
	 * @method \Bitrix\Main\Type\DateTime requireDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateFrom()
	 * @method \Bitrix\Main\Type\DateTime fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime getDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateTo)
	 * @method bool hasDateTo()
	 * @method bool isDateToFilled()
	 * @method bool isDateToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateTo()
	 * @method \Bitrix\Main\Type\DateTime requireDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateTo()
	 * @method \Bitrix\Main\Type\DateTime fillDateTo()
	 * @method \Bitrix\Main\Type\DateTime getOriginalDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event setOriginalDateFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $originalDateFrom)
	 * @method bool hasOriginalDateFrom()
	 * @method bool isOriginalDateFromFilled()
	 * @method bool isOriginalDateFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualOriginalDateFrom()
	 * @method \Bitrix\Main\Type\DateTime requireOriginalDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetOriginalDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetOriginalDateFrom()
	 * @method \Bitrix\Main\Type\DateTime fillOriginalDateFrom()
	 * @method \string getTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTzFrom(\string|\Bitrix\Main\DB\SqlExpression $tzFrom)
	 * @method bool hasTzFrom()
	 * @method bool isTzFromFilled()
	 * @method bool isTzFromChanged()
	 * @method \string remindActualTzFrom()
	 * @method \string requireTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTzFrom()
	 * @method \string fillTzFrom()
	 * @method \string getTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTzTo(\string|\Bitrix\Main\DB\SqlExpression $tzTo)
	 * @method bool hasTzTo()
	 * @method bool isTzToFilled()
	 * @method bool isTzToChanged()
	 * @method \string remindActualTzTo()
	 * @method \string requireTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTzTo()
	 * @method \string fillTzTo()
	 * @method \int getTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTzOffsetFrom(\int|\Bitrix\Main\DB\SqlExpression $tzOffsetFrom)
	 * @method bool hasTzOffsetFrom()
	 * @method bool isTzOffsetFromFilled()
	 * @method bool isTzOffsetFromChanged()
	 * @method \int remindActualTzOffsetFrom()
	 * @method \int requireTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTzOffsetFrom()
	 * @method \int fillTzOffsetFrom()
	 * @method \int getTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTzOffsetTo(\int|\Bitrix\Main\DB\SqlExpression $tzOffsetTo)
	 * @method bool hasTzOffsetTo()
	 * @method bool isTzOffsetToFilled()
	 * @method bool isTzOffsetToChanged()
	 * @method \int remindActualTzOffsetTo()
	 * @method \int requireTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTzOffsetTo()
	 * @method \int fillTzOffsetTo()
	 * @method \int getDateFromTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateFromTsUtc(\int|\Bitrix\Main\DB\SqlExpression $dateFromTsUtc)
	 * @method bool hasDateFromTsUtc()
	 * @method bool isDateFromTsUtcFilled()
	 * @method bool isDateFromTsUtcChanged()
	 * @method \int remindActualDateFromTsUtc()
	 * @method \int requireDateFromTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateFromTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateFromTsUtc()
	 * @method \int fillDateFromTsUtc()
	 * @method \int getDateToTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateToTsUtc(\int|\Bitrix\Main\DB\SqlExpression $dateToTsUtc)
	 * @method bool hasDateToTsUtc()
	 * @method bool isDateToTsUtcFilled()
	 * @method bool isDateToTsUtcChanged()
	 * @method \int remindActualDateToTsUtc()
	 * @method \int requireDateToTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateToTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateToTsUtc()
	 * @method \int fillDateToTsUtc()
	 * @method \boolean getDtSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDtSkipTime(\boolean|\Bitrix\Main\DB\SqlExpression $dtSkipTime)
	 * @method bool hasDtSkipTime()
	 * @method bool isDtSkipTimeFilled()
	 * @method bool isDtSkipTimeChanged()
	 * @method \boolean remindActualDtSkipTime()
	 * @method \boolean requireDtSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDtSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDtSkipTime()
	 * @method \boolean fillDtSkipTime()
	 * @method \int getDtLength()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDtLength(\int|\Bitrix\Main\DB\SqlExpression $dtLength)
	 * @method bool hasDtLength()
	 * @method bool isDtLengthFilled()
	 * @method bool isDtLengthChanged()
	 * @method \int remindActualDtLength()
	 * @method \int requireDtLength()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDtLength()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDtLength()
	 * @method \int fillDtLength()
	 * @method \string getEventType()
	 * @method \Bitrix\Calendar\Internals\EO_Event setEventType(\string|\Bitrix\Main\DB\SqlExpression $eventType)
	 * @method bool hasEventType()
	 * @method bool isEventTypeFilled()
	 * @method bool isEventTypeChanged()
	 * @method \string remindActualEventType()
	 * @method \string requireEventType()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetEventType()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetEventType()
	 * @method \string fillEventType()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Event setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDescription()
	 * @method \string fillDescription()
	 * @method \Bitrix\Main\Type\DateTime getDtFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDtFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dtFrom)
	 * @method bool hasDtFrom()
	 * @method bool isDtFromFilled()
	 * @method bool isDtFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDtFrom()
	 * @method \Bitrix\Main\Type\DateTime requireDtFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDtFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDtFrom()
	 * @method \Bitrix\Main\Type\DateTime fillDtFrom()
	 * @method \Bitrix\Main\Type\DateTime getDtTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDtTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dtTo)
	 * @method bool hasDtTo()
	 * @method bool isDtToFilled()
	 * @method bool isDtToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDtTo()
	 * @method \Bitrix\Main\Type\DateTime requireDtTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDtTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDtTo()
	 * @method \Bitrix\Main\Type\DateTime fillDtTo()
	 * @method \string getPrivateEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event setPrivateEvent(\string|\Bitrix\Main\DB\SqlExpression $privateEvent)
	 * @method bool hasPrivateEvent()
	 * @method bool isPrivateEventFilled()
	 * @method bool isPrivateEventChanged()
	 * @method \string remindActualPrivateEvent()
	 * @method \string requirePrivateEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetPrivateEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetPrivateEvent()
	 * @method \string fillPrivateEvent()
	 * @method \string getAccessibility()
	 * @method \Bitrix\Calendar\Internals\EO_Event setAccessibility(\string|\Bitrix\Main\DB\SqlExpression $accessibility)
	 * @method bool hasAccessibility()
	 * @method bool isAccessibilityFilled()
	 * @method bool isAccessibilityChanged()
	 * @method \string remindActualAccessibility()
	 * @method \string requireAccessibility()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetAccessibility()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetAccessibility()
	 * @method \string fillAccessibility()
	 * @method \string getImportance()
	 * @method \Bitrix\Calendar\Internals\EO_Event setImportance(\string|\Bitrix\Main\DB\SqlExpression $importance)
	 * @method bool hasImportance()
	 * @method bool isImportanceFilled()
	 * @method bool isImportanceChanged()
	 * @method \string remindActualImportance()
	 * @method \string requireImportance()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetImportance()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetImportance()
	 * @method \string fillImportance()
	 * @method \string getIsMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event setIsMeeting(\string|\Bitrix\Main\DB\SqlExpression $isMeeting)
	 * @method bool hasIsMeeting()
	 * @method bool isIsMeetingFilled()
	 * @method bool isIsMeetingChanged()
	 * @method \string remindActualIsMeeting()
	 * @method \string requireIsMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetIsMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetIsMeeting()
	 * @method \string fillIsMeeting()
	 * @method \string getMeetingStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Event setMeetingStatus(\string|\Bitrix\Main\DB\SqlExpression $meetingStatus)
	 * @method bool hasMeetingStatus()
	 * @method bool isMeetingStatusFilled()
	 * @method bool isMeetingStatusChanged()
	 * @method \string remindActualMeetingStatus()
	 * @method \string requireMeetingStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetMeetingStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetMeetingStatus()
	 * @method \string fillMeetingStatus()
	 * @method \int getMeetingHost()
	 * @method \Bitrix\Calendar\Internals\EO_Event setMeetingHost(\int|\Bitrix\Main\DB\SqlExpression $meetingHost)
	 * @method bool hasMeetingHost()
	 * @method bool isMeetingHostFilled()
	 * @method bool isMeetingHostChanged()
	 * @method \int remindActualMeetingHost()
	 * @method \int requireMeetingHost()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetMeetingHost()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetMeetingHost()
	 * @method \int fillMeetingHost()
	 * @method \string getMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event setMeeting(\string|\Bitrix\Main\DB\SqlExpression $meeting)
	 * @method bool hasMeeting()
	 * @method bool isMeetingFilled()
	 * @method bool isMeetingChanged()
	 * @method \string remindActualMeeting()
	 * @method \string requireMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetMeeting()
	 * @method \string fillMeeting()
	 * @method \string getLocation()
	 * @method \Bitrix\Calendar\Internals\EO_Event setLocation(\string|\Bitrix\Main\DB\SqlExpression $location)
	 * @method bool hasLocation()
	 * @method bool isLocationFilled()
	 * @method bool isLocationChanged()
	 * @method \string remindActualLocation()
	 * @method \string requireLocation()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetLocation()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetLocation()
	 * @method \string fillLocation()
	 * @method \string getRemind()
	 * @method \Bitrix\Calendar\Internals\EO_Event setRemind(\string|\Bitrix\Main\DB\SqlExpression $remind)
	 * @method bool hasRemind()
	 * @method bool isRemindFilled()
	 * @method bool isRemindChanged()
	 * @method \string remindActualRemind()
	 * @method \string requireRemind()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetRemind()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetRemind()
	 * @method \string fillRemind()
	 * @method \string getColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event setColor(\string|\Bitrix\Main\DB\SqlExpression $color)
	 * @method bool hasColor()
	 * @method bool isColorFilled()
	 * @method bool isColorChanged()
	 * @method \string remindActualColor()
	 * @method \string requireColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetColor()
	 * @method \string fillColor()
	 * @method \string getTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTextColor(\string|\Bitrix\Main\DB\SqlExpression $textColor)
	 * @method bool hasTextColor()
	 * @method bool isTextColorFilled()
	 * @method bool isTextColorChanged()
	 * @method \string remindActualTextColor()
	 * @method \string requireTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTextColor()
	 * @method \string fillTextColor()
	 * @method \string getRrule()
	 * @method \Bitrix\Calendar\Internals\EO_Event setRrule(\string|\Bitrix\Main\DB\SqlExpression $rrule)
	 * @method bool hasRrule()
	 * @method bool isRruleFilled()
	 * @method bool isRruleChanged()
	 * @method \string remindActualRrule()
	 * @method \string requireRrule()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetRrule()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetRrule()
	 * @method \string fillRrule()
	 * @method \string getExdate()
	 * @method \Bitrix\Calendar\Internals\EO_Event setExdate(\string|\Bitrix\Main\DB\SqlExpression $exdate)
	 * @method bool hasExdate()
	 * @method bool isExdateFilled()
	 * @method bool isExdateChanged()
	 * @method \string remindActualExdate()
	 * @method \string requireExdate()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetExdate()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetExdate()
	 * @method \string fillExdate()
	 * @method \string getDavXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDavXmlId(\string|\Bitrix\Main\DB\SqlExpression $davXmlId)
	 * @method bool hasDavXmlId()
	 * @method bool isDavXmlIdFilled()
	 * @method bool isDavXmlIdChanged()
	 * @method \string remindActualDavXmlId()
	 * @method \string requireDavXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDavXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDavXmlId()
	 * @method \string fillDavXmlId()
	 * @method \string getGEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setGEventId(\string|\Bitrix\Main\DB\SqlExpression $gEventId)
	 * @method bool hasGEventId()
	 * @method bool isGEventIdFilled()
	 * @method bool isGEventIdChanged()
	 * @method \string remindActualGEventId()
	 * @method \string requireGEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetGEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetGEventId()
	 * @method \string fillGEventId()
	 * @method \string getDavExchLabel()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDavExchLabel(\string|\Bitrix\Main\DB\SqlExpression $davExchLabel)
	 * @method bool hasDavExchLabel()
	 * @method bool isDavExchLabelFilled()
	 * @method bool isDavExchLabelChanged()
	 * @method \string remindActualDavExchLabel()
	 * @method \string requireDavExchLabel()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDavExchLabel()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDavExchLabel()
	 * @method \string fillDavExchLabel()
	 * @method \string getCalDavLabel()
	 * @method \Bitrix\Calendar\Internals\EO_Event setCalDavLabel(\string|\Bitrix\Main\DB\SqlExpression $calDavLabel)
	 * @method bool hasCalDavLabel()
	 * @method bool isCalDavLabelFilled()
	 * @method bool isCalDavLabelChanged()
	 * @method \string remindActualCalDavLabel()
	 * @method \string requireCalDavLabel()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetCalDavLabel()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetCalDavLabel()
	 * @method \string fillCalDavLabel()
	 * @method \string getVersion()
	 * @method \Bitrix\Calendar\Internals\EO_Event setVersion(\string|\Bitrix\Main\DB\SqlExpression $version)
	 * @method bool hasVersion()
	 * @method bool isVersionFilled()
	 * @method bool isVersionChanged()
	 * @method \string remindActualVersion()
	 * @method \string requireVersion()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetVersion()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetVersion()
	 * @method \string fillVersion()
	 * @method \string getAttendeesCodes()
	 * @method \Bitrix\Calendar\Internals\EO_Event setAttendeesCodes(\string|\Bitrix\Main\DB\SqlExpression $attendeesCodes)
	 * @method bool hasAttendeesCodes()
	 * @method bool isAttendeesCodesFilled()
	 * @method bool isAttendeesCodesChanged()
	 * @method \string remindActualAttendeesCodes()
	 * @method \string requireAttendeesCodes()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetAttendeesCodes()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetAttendeesCodes()
	 * @method \string fillAttendeesCodes()
	 * @method \int getRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setRecurrenceId(\int|\Bitrix\Main\DB\SqlExpression $recurrenceId)
	 * @method bool hasRecurrenceId()
	 * @method bool isRecurrenceIdFilled()
	 * @method bool isRecurrenceIdChanged()
	 * @method \int remindActualRecurrenceId()
	 * @method \int requireRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetRecurrenceId()
	 * @method \int fillRecurrenceId()
	 * @method \string getRelations()
	 * @method \Bitrix\Calendar\Internals\EO_Event setRelations(\string|\Bitrix\Main\DB\SqlExpression $relations)
	 * @method bool hasRelations()
	 * @method bool isRelationsFilled()
	 * @method bool isRelationsChanged()
	 * @method \string remindActualRelations()
	 * @method \string requireRelations()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetRelations()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetRelations()
	 * @method \string fillRelations()
	 * @method \string getSearchableContent()
	 * @method \Bitrix\Calendar\Internals\EO_Event setSearchableContent(\string|\Bitrix\Main\DB\SqlExpression $searchableContent)
	 * @method bool hasSearchableContent()
	 * @method bool isSearchableContentFilled()
	 * @method bool isSearchableContentChanged()
	 * @method \string remindActualSearchableContent()
	 * @method \string requireSearchableContent()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetSearchableContent()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetSearchableContent()
	 * @method \string fillSearchableContent()
	 * @method \int getSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setSectionId(\int|\Bitrix\Main\DB\SqlExpression $sectionId)
	 * @method bool hasSectionId()
	 * @method bool isSectionIdFilled()
	 * @method bool isSectionIdChanged()
	 * @method \int remindActualSectionId()
	 * @method \int requireSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetSectionId()
	 * @method \int fillSectionId()
	 * @method \string getSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Event setSyncStatus(\string|\Bitrix\Main\DB\SqlExpression $syncStatus)
	 * @method bool hasSyncStatus()
	 * @method bool isSyncStatusFilled()
	 * @method bool isSyncStatusChanged()
	 * @method \string remindActualSyncStatus()
	 * @method \string requireSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetSyncStatus()
	 * @method \string fillSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Section getSection()
	 * @method \Bitrix\Calendar\Internals\EO_Section remindActualSection()
	 * @method \Bitrix\Calendar\Internals\EO_Section requireSection()
	 * @method \Bitrix\Calendar\Internals\EO_Event setSection(\Bitrix\Calendar\Internals\EO_Section $object)
	 * @method \Bitrix\Calendar\Internals\EO_Event resetSection()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetSection()
	 * @method bool hasSection()
	 * @method bool isSectionFilled()
	 * @method bool isSectionChanged()
	 * @method \Bitrix\Calendar\Internals\EO_Section fillSection()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect getEventSect()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect remindActualEventSect()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect requireEventSect()
	 * @method \Bitrix\Calendar\Internals\EO_Event setEventSect(\Bitrix\Calendar\Internals\EO_EventSect $object)
	 * @method \Bitrix\Calendar\Internals\EO_Event resetEventSect()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetEventSect()
	 * @method bool hasEventSect()
	 * @method bool isEventSectFilled()
	 * @method bool isEventSectChanged()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect fillEventSect()
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
	 * @method \Bitrix\Calendar\Internals\EO_Event set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Event reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Event unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Event wakeUp($data)
	 */
	class EO_Event {
		/* @var \Bitrix\Calendar\Internals\EventTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Event_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getDeletedList()
	 * @method \boolean[] fillDeleted()
	 * @method \string[] getCalTypeList()
	 * @method \string[] fillCalType()
	 * @method \int[] getOwnerIdList()
	 * @method \int[] fillOwnerId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \Bitrix\Main\Type\DateTime[] getDateFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getDateToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateTo()
	 * @method \Bitrix\Main\Type\DateTime[] getOriginalDateFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillOriginalDateFrom()
	 * @method \string[] getTzFromList()
	 * @method \string[] fillTzFrom()
	 * @method \string[] getTzToList()
	 * @method \string[] fillTzTo()
	 * @method \int[] getTzOffsetFromList()
	 * @method \int[] fillTzOffsetFrom()
	 * @method \int[] getTzOffsetToList()
	 * @method \int[] fillTzOffsetTo()
	 * @method \int[] getDateFromTsUtcList()
	 * @method \int[] fillDateFromTsUtc()
	 * @method \int[] getDateToTsUtcList()
	 * @method \int[] fillDateToTsUtc()
	 * @method \boolean[] getDtSkipTimeList()
	 * @method \boolean[] fillDtSkipTime()
	 * @method \int[] getDtLengthList()
	 * @method \int[] fillDtLength()
	 * @method \string[] getEventTypeList()
	 * @method \string[] fillEventType()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \Bitrix\Main\Type\DateTime[] getDtFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDtFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getDtToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDtTo()
	 * @method \string[] getPrivateEventList()
	 * @method \string[] fillPrivateEvent()
	 * @method \string[] getAccessibilityList()
	 * @method \string[] fillAccessibility()
	 * @method \string[] getImportanceList()
	 * @method \string[] fillImportance()
	 * @method \string[] getIsMeetingList()
	 * @method \string[] fillIsMeeting()
	 * @method \string[] getMeetingStatusList()
	 * @method \string[] fillMeetingStatus()
	 * @method \int[] getMeetingHostList()
	 * @method \int[] fillMeetingHost()
	 * @method \string[] getMeetingList()
	 * @method \string[] fillMeeting()
	 * @method \string[] getLocationList()
	 * @method \string[] fillLocation()
	 * @method \string[] getRemindList()
	 * @method \string[] fillRemind()
	 * @method \string[] getColorList()
	 * @method \string[] fillColor()
	 * @method \string[] getTextColorList()
	 * @method \string[] fillTextColor()
	 * @method \string[] getRruleList()
	 * @method \string[] fillRrule()
	 * @method \string[] getExdateList()
	 * @method \string[] fillExdate()
	 * @method \string[] getDavXmlIdList()
	 * @method \string[] fillDavXmlId()
	 * @method \string[] getGEventIdList()
	 * @method \string[] fillGEventId()
	 * @method \string[] getDavExchLabelList()
	 * @method \string[] fillDavExchLabel()
	 * @method \string[] getCalDavLabelList()
	 * @method \string[] fillCalDavLabel()
	 * @method \string[] getVersionList()
	 * @method \string[] fillVersion()
	 * @method \string[] getAttendeesCodesList()
	 * @method \string[] fillAttendeesCodes()
	 * @method \int[] getRecurrenceIdList()
	 * @method \int[] fillRecurrenceId()
	 * @method \string[] getRelationsList()
	 * @method \string[] fillRelations()
	 * @method \string[] getSearchableContentList()
	 * @method \string[] fillSearchableContent()
	 * @method \int[] getSectionIdList()
	 * @method \int[] fillSectionId()
	 * @method \string[] getSyncStatusList()
	 * @method \string[] fillSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Section[] getSectionList()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection getSectionCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection fillSection()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect[] getEventSectList()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection getEventSectCollection()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect_Collection fillEventSect()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Event getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Event[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Event_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Event current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection merge(?\Bitrix\Calendar\Internals\EO_Event_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Event_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\EventTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Event_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Event fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Event_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Event fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection fetchCollection()
	 */
	class EO_Event_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Event createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Event wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection wakeUpCollection($rows)
	 */
	class EO_Event_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\EventSectTable:calendar/lib/internals/eventsecttable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_EventSect
	 * @see \Bitrix\Calendar\Internals\EventSectTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect setEventId(\int|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \int getSectId()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect setSectId(\int|\Bitrix\Main\DB\SqlExpression $sectId)
	 * @method bool hasSectId()
	 * @method bool isSectIdFilled()
	 * @method bool isSectIdChanged()
	 * @method \string getRel()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect setRel(\string|\Bitrix\Main\DB\SqlExpression $rel)
	 * @method bool hasRel()
	 * @method bool isRelFilled()
	 * @method bool isRelChanged()
	 * @method \string remindActualRel()
	 * @method \string requireRel()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect resetRel()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect unsetRel()
	 * @method \string fillRel()
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
	 * @method \Bitrix\Calendar\Internals\EO_EventSect set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_EventSect reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_EventSect unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_EventSect wakeUp($data)
	 */
	class EO_EventSect {
		/* @var \Bitrix\Calendar\Internals\EventSectTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventSectTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_EventSect_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getEventIdList()
	 * @method \int[] getSectIdList()
	 * @method \string[] getRelList()
	 * @method \string[] fillRel()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_EventSect $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_EventSect $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_EventSect getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_EventSect[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_EventSect $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_EventSect_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_EventSect current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_EventSect_Collection merge(?\Bitrix\Calendar\Internals\EO_EventSect_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_EventSect_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\EventSectTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventSectTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventSect_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventSect_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_EventSect fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect_Collection fetchCollection()
	 */
	class EO_EventSect_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_EventSect createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_EventSect_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_EventSect wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_EventSect_Collection wakeUpCollection($rows)
	 */
	class EO_EventSect_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\SharingLinkRuleTable:calendar/lib/internals/sharinglinkruletable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SharingLinkRule
	 * @see \Bitrix\Calendar\Internals\SharingLinkRuleTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getLinkId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule setLinkId(\int|\Bitrix\Main\DB\SqlExpression $linkId)
	 * @method bool hasLinkId()
	 * @method bool isLinkIdFilled()
	 * @method bool isLinkIdChanged()
	 * @method \int remindActualLinkId()
	 * @method \int requireLinkId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule resetLinkId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule unsetLinkId()
	 * @method \int fillLinkId()
	 * @method \int getSlotSize()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule setSlotSize(\int|\Bitrix\Main\DB\SqlExpression $slotSize)
	 * @method bool hasSlotSize()
	 * @method bool isSlotSizeFilled()
	 * @method bool isSlotSizeChanged()
	 * @method \int remindActualSlotSize()
	 * @method \int requireSlotSize()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule resetSlotSize()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule unsetSlotSize()
	 * @method \int fillSlotSize()
	 * @method \string getWeekdays()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule setWeekdays(\string|\Bitrix\Main\DB\SqlExpression $weekdays)
	 * @method bool hasWeekdays()
	 * @method bool isWeekdaysFilled()
	 * @method bool isWeekdaysChanged()
	 * @method \string remindActualWeekdays()
	 * @method \string requireWeekdays()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule resetWeekdays()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule unsetWeekdays()
	 * @method \string fillWeekdays()
	 * @method \int getTimeFrom()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule setTimeFrom(\int|\Bitrix\Main\DB\SqlExpression $timeFrom)
	 * @method bool hasTimeFrom()
	 * @method bool isTimeFromFilled()
	 * @method bool isTimeFromChanged()
	 * @method \int remindActualTimeFrom()
	 * @method \int requireTimeFrom()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule resetTimeFrom()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule unsetTimeFrom()
	 * @method \int fillTimeFrom()
	 * @method \int getTimeTo()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule setTimeTo(\int|\Bitrix\Main\DB\SqlExpression $timeTo)
	 * @method bool hasTimeTo()
	 * @method bool isTimeToFilled()
	 * @method bool isTimeToChanged()
	 * @method \int remindActualTimeTo()
	 * @method \int requireTimeTo()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule resetTimeTo()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule unsetTimeTo()
	 * @method \int fillTimeTo()
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
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkRule wakeUp($data)
	 */
	class EO_SharingLinkRule {
		/* @var \Bitrix\Calendar\Internals\SharingLinkRuleTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SharingLinkRuleTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SharingLinkRule_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getLinkIdList()
	 * @method \int[] fillLinkId()
	 * @method \int[] getSlotSizeList()
	 * @method \int[] fillSlotSize()
	 * @method \string[] getWeekdaysList()
	 * @method \string[] fillWeekdays()
	 * @method \int[] getTimeFromList()
	 * @method \int[] fillTimeFrom()
	 * @method \int[] getTimeToList()
	 * @method \int[] fillTimeTo()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_SharingLinkRule $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_SharingLinkRule $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_SharingLinkRule $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkRule_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule_Collection merge(?\Bitrix\Calendar\Internals\EO_SharingLinkRule_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_SharingLinkRule_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\SharingLinkRuleTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SharingLinkRuleTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SharingLinkRule_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SharingLinkRule_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule_Collection fetchCollection()
	 */
	class EO_SharingLinkRule_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkRule_Collection wakeUpCollection($rows)
	 */
	class EO_SharingLinkRule_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\SectionTable:calendar/lib/internals/sectiontable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Section
	 * @see \Bitrix\Calendar\Internals\SectionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Calendar\Internals\EO_Section setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetName()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetName()
	 * @method \string fillName()
	 * @method \string getXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \string getGapiCalendarId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setGapiCalendarId(\string|\Bitrix\Main\DB\SqlExpression $gapiCalendarId)
	 * @method bool hasGapiCalendarId()
	 * @method bool isGapiCalendarIdFilled()
	 * @method bool isGapiCalendarIdChanged()
	 * @method \string remindActualGapiCalendarId()
	 * @method \string requireGapiCalendarId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetGapiCalendarId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetGapiCalendarId()
	 * @method \string fillGapiCalendarId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Calendar\Internals\EO_Section setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetActive()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Section setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section setColor(\string|\Bitrix\Main\DB\SqlExpression $color)
	 * @method bool hasColor()
	 * @method bool isColorFilled()
	 * @method bool isColorChanged()
	 * @method \string remindActualColor()
	 * @method \string requireColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetColor()
	 * @method \string fillColor()
	 * @method \string getTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section setTextColor(\string|\Bitrix\Main\DB\SqlExpression $textColor)
	 * @method bool hasTextColor()
	 * @method bool isTextColorFilled()
	 * @method bool isTextColorChanged()
	 * @method \string remindActualTextColor()
	 * @method \string requireTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetTextColor()
	 * @method \string fillTextColor()
	 * @method \string getExport()
	 * @method \Bitrix\Calendar\Internals\EO_Section setExport(\string|\Bitrix\Main\DB\SqlExpression $export)
	 * @method bool hasExport()
	 * @method bool isExportFilled()
	 * @method bool isExportChanged()
	 * @method \string remindActualExport()
	 * @method \string requireExport()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetExport()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetExport()
	 * @method \string fillExport()
	 * @method \int getSort()
	 * @method \Bitrix\Calendar\Internals\EO_Section setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetSort()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetSort()
	 * @method \int fillSort()
	 * @method \string getCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCalType(\string|\Bitrix\Main\DB\SqlExpression $calType)
	 * @method bool hasCalType()
	 * @method bool isCalTypeFilled()
	 * @method bool isCalTypeChanged()
	 * @method \string remindActualCalType()
	 * @method \string requireCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCalType()
	 * @method \string fillCalType()
	 * @method \int getOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setOwnerId(\int|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \int remindActualOwnerId()
	 * @method \int requireOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetOwnerId()
	 * @method \int fillOwnerId()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetParentId()
	 * @method \int fillParentId()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Section setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Section setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getDavExchCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section setDavExchCal(\string|\Bitrix\Main\DB\SqlExpression $davExchCal)
	 * @method bool hasDavExchCal()
	 * @method bool isDavExchCalFilled()
	 * @method bool isDavExchCalChanged()
	 * @method \string remindActualDavExchCal()
	 * @method \string requireDavExchCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetDavExchCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetDavExchCal()
	 * @method \string fillDavExchCal()
	 * @method \string getDavExchMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section setDavExchMod(\string|\Bitrix\Main\DB\SqlExpression $davExchMod)
	 * @method bool hasDavExchMod()
	 * @method bool isDavExchModFilled()
	 * @method bool isDavExchModChanged()
	 * @method \string remindActualDavExchMod()
	 * @method \string requireDavExchMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetDavExchMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetDavExchMod()
	 * @method \string fillDavExchMod()
	 * @method \string getCalDavCon()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCalDavCon(\string|\Bitrix\Main\DB\SqlExpression $calDavCon)
	 * @method bool hasCalDavCon()
	 * @method bool isCalDavConFilled()
	 * @method bool isCalDavConChanged()
	 * @method \string remindActualCalDavCon()
	 * @method \string requireCalDavCon()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCalDavCon()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCalDavCon()
	 * @method \string fillCalDavCon()
	 * @method \string getCalDavCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCalDavCal(\string|\Bitrix\Main\DB\SqlExpression $calDavCal)
	 * @method bool hasCalDavCal()
	 * @method bool isCalDavCalFilled()
	 * @method bool isCalDavCalChanged()
	 * @method \string remindActualCalDavCal()
	 * @method \string requireCalDavCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCalDavCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCalDavCal()
	 * @method \string fillCalDavCal()
	 * @method \string getCalDavMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCalDavMod(\string|\Bitrix\Main\DB\SqlExpression $calDavMod)
	 * @method bool hasCalDavMod()
	 * @method bool isCalDavModFilled()
	 * @method bool isCalDavModChanged()
	 * @method \string remindActualCalDavMod()
	 * @method \string requireCalDavMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCalDavMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCalDavMod()
	 * @method \string fillCalDavMod()
	 * @method \string getIsExchange()
	 * @method \Bitrix\Calendar\Internals\EO_Section setIsExchange(\string|\Bitrix\Main\DB\SqlExpression $isExchange)
	 * @method bool hasIsExchange()
	 * @method bool isIsExchangeFilled()
	 * @method bool isIsExchangeChanged()
	 * @method \string remindActualIsExchange()
	 * @method \string requireIsExchange()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetIsExchange()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetIsExchange()
	 * @method \string fillIsExchange()
	 * @method \string getSyncToken()
	 * @method \Bitrix\Calendar\Internals\EO_Section setSyncToken(\string|\Bitrix\Main\DB\SqlExpression $syncToken)
	 * @method bool hasSyncToken()
	 * @method bool isSyncTokenFilled()
	 * @method bool isSyncTokenChanged()
	 * @method \string remindActualSyncToken()
	 * @method \string requireSyncToken()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetSyncToken()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetSyncToken()
	 * @method \string fillSyncToken()
	 * @method \string getPageToken()
	 * @method \Bitrix\Calendar\Internals\EO_Section setPageToken(\string|\Bitrix\Main\DB\SqlExpression $pageToken)
	 * @method bool hasPageToken()
	 * @method bool isPageTokenFilled()
	 * @method bool isPageTokenChanged()
	 * @method \string remindActualPageToken()
	 * @method \string requirePageToken()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetPageToken()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetPageToken()
	 * @method \string fillPageToken()
	 * @method \string getExternalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section setExternalType(\string|\Bitrix\Main\DB\SqlExpression $externalType)
	 * @method bool hasExternalType()
	 * @method bool isExternalTypeFilled()
	 * @method bool isExternalTypeChanged()
	 * @method \string remindActualExternalType()
	 * @method \string requireExternalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetExternalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetExternalType()
	 * @method \string fillExternalType()
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
	 * @method \Bitrix\Calendar\Internals\EO_Section set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Section reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Section unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Section wakeUp($data)
	 */
	class EO_Section {
		/* @var \Bitrix\Calendar\Internals\SectionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SectionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Section_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \string[] getGapiCalendarIdList()
	 * @method \string[] fillGapiCalendarId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getColorList()
	 * @method \string[] fillColor()
	 * @method \string[] getTextColorList()
	 * @method \string[] fillTextColor()
	 * @method \string[] getExportList()
	 * @method \string[] fillExport()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getCalTypeList()
	 * @method \string[] fillCalType()
	 * @method \int[] getOwnerIdList()
	 * @method \int[] fillOwnerId()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getDavExchCalList()
	 * @method \string[] fillDavExchCal()
	 * @method \string[] getDavExchModList()
	 * @method \string[] fillDavExchMod()
	 * @method \string[] getCalDavConList()
	 * @method \string[] fillCalDavCon()
	 * @method \string[] getCalDavCalList()
	 * @method \string[] fillCalDavCal()
	 * @method \string[] getCalDavModList()
	 * @method \string[] fillCalDavMod()
	 * @method \string[] getIsExchangeList()
	 * @method \string[] fillIsExchange()
	 * @method \string[] getSyncTokenList()
	 * @method \string[] fillSyncToken()
	 * @method \string[] getPageTokenList()
	 * @method \string[] fillPageToken()
	 * @method \string[] getExternalTypeList()
	 * @method \string[] fillExternalType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Section $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Section $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Section getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Section[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Section $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Section_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Section current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection merge(?\Bitrix\Calendar\Internals\EO_Section_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Section_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\SectionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SectionTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Section_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Section fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Section_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Section fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection fetchCollection()
	 */
	class EO_Section_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Section createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Section wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection wakeUpCollection($rows)
	 */
	class EO_Section_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\SectionConnectionTable:calendar/lib/internals/sectionconnectiontable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SectionConnection
	 * @see \Bitrix\Calendar\Internals\SectionConnectionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setSectionId(\int|\Bitrix\Main\DB\SqlExpression $sectionId)
	 * @method bool hasSectionId()
	 * @method bool isSectionIdFilled()
	 * @method bool isSectionIdChanged()
	 * @method \int remindActualSectionId()
	 * @method \int requireSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetSectionId()
	 * @method \int fillSectionId()
	 * @method \int getConnectionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setConnectionId(\int|\Bitrix\Main\DB\SqlExpression $connectionId)
	 * @method bool hasConnectionId()
	 * @method bool isConnectionIdFilled()
	 * @method bool isConnectionIdChanged()
	 * @method \int remindActualConnectionId()
	 * @method \int requireConnectionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetConnectionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetConnectionId()
	 * @method \int fillConnectionId()
	 * @method \string getVendorSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setVendorSectionId(\string|\Bitrix\Main\DB\SqlExpression $vendorSectionId)
	 * @method bool hasVendorSectionId()
	 * @method bool isVendorSectionIdFilled()
	 * @method bool isVendorSectionIdChanged()
	 * @method \string remindActualVendorSectionId()
	 * @method \string requireVendorSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetVendorSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetVendorSectionId()
	 * @method \string fillVendorSectionId()
	 * @method null|\string getSyncToken()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setSyncToken(null|\string|\Bitrix\Main\DB\SqlExpression $syncToken)
	 * @method bool hasSyncToken()
	 * @method bool isSyncTokenFilled()
	 * @method bool isSyncTokenChanged()
	 * @method null|\string remindActualSyncToken()
	 * @method null|\string requireSyncToken()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetSyncToken()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetSyncToken()
	 * @method null|\string fillSyncToken()
	 * @method null|\string getPageToken()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setPageToken(null|\string|\Bitrix\Main\DB\SqlExpression $pageToken)
	 * @method bool hasPageToken()
	 * @method bool isPageTokenFilled()
	 * @method bool isPageTokenChanged()
	 * @method null|\string remindActualPageToken()
	 * @method null|\string requirePageToken()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetPageToken()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetPageToken()
	 * @method null|\string fillPageToken()
	 * @method \boolean getActive()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetActive()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetActive()
	 * @method \boolean fillActive()
	 * @method null|\Bitrix\Main\Type\DateTime getLastSyncDate()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setLastSyncDate(null|\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastSyncDate)
	 * @method bool hasLastSyncDate()
	 * @method bool isLastSyncDateFilled()
	 * @method bool isLastSyncDateChanged()
	 * @method null|\Bitrix\Main\Type\DateTime remindActualLastSyncDate()
	 * @method null|\Bitrix\Main\Type\DateTime requireLastSyncDate()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetLastSyncDate()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetLastSyncDate()
	 * @method null|\Bitrix\Main\Type\DateTime fillLastSyncDate()
	 * @method null|\string getLastSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setLastSyncStatus(null|\string|\Bitrix\Main\DB\SqlExpression $lastSyncStatus)
	 * @method bool hasLastSyncStatus()
	 * @method bool isLastSyncStatusFilled()
	 * @method bool isLastSyncStatusChanged()
	 * @method null|\string remindActualLastSyncStatus()
	 * @method null|\string requireLastSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetLastSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetLastSyncStatus()
	 * @method null|\string fillLastSyncStatus()
	 * @method null|\string getVersionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setVersionId(null|\string|\Bitrix\Main\DB\SqlExpression $versionId)
	 * @method bool hasVersionId()
	 * @method bool isVersionIdFilled()
	 * @method bool isVersionIdChanged()
	 * @method null|\string remindActualVersionId()
	 * @method null|\string requireVersionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetVersionId()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetVersionId()
	 * @method null|\string fillVersionId()
	 * @method \Bitrix\Calendar\Internals\EO_Section getSection()
	 * @method \Bitrix\Calendar\Internals\EO_Section remindActualSection()
	 * @method \Bitrix\Calendar\Internals\EO_Section requireSection()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setSection(\Bitrix\Calendar\Internals\EO_Section $object)
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetSection()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetSection()
	 * @method bool hasSection()
	 * @method bool isSectionFilled()
	 * @method bool isSectionChanged()
	 * @method \Bitrix\Calendar\Internals\EO_Section fillSection()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection getConnection()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection remindActualConnection()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection requireConnection()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setConnection(\Bitrix\Dav\Internals\EO_DavConnection $object)
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetConnection()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetConnection()
	 * @method bool hasConnection()
	 * @method bool isConnectionFilled()
	 * @method bool isConnectionChanged()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection fillConnection()
	 * @method \boolean getIsPrimary()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection setIsPrimary(\boolean|\Bitrix\Main\DB\SqlExpression $isPrimary)
	 * @method bool hasIsPrimary()
	 * @method bool isIsPrimaryFilled()
	 * @method bool isIsPrimaryChanged()
	 * @method \boolean remindActualIsPrimary()
	 * @method \boolean requireIsPrimary()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection resetIsPrimary()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unsetIsPrimary()
	 * @method \boolean fillIsPrimary()
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
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_SectionConnection wakeUp($data)
	 */
	class EO_SectionConnection {
		/* @var \Bitrix\Calendar\Internals\SectionConnectionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SectionConnectionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SectionConnection_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getSectionIdList()
	 * @method \int[] fillSectionId()
	 * @method \int[] getConnectionIdList()
	 * @method \int[] fillConnectionId()
	 * @method \string[] getVendorSectionIdList()
	 * @method \string[] fillVendorSectionId()
	 * @method null|\string[] getSyncTokenList()
	 * @method null|\string[] fillSyncToken()
	 * @method null|\string[] getPageTokenList()
	 * @method null|\string[] fillPageToken()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method null|\Bitrix\Main\Type\DateTime[] getLastSyncDateList()
	 * @method null|\Bitrix\Main\Type\DateTime[] fillLastSyncDate()
	 * @method null|\string[] getLastSyncStatusList()
	 * @method null|\string[] fillLastSyncStatus()
	 * @method null|\string[] getVersionIdList()
	 * @method null|\string[] fillVersionId()
	 * @method \Bitrix\Calendar\Internals\EO_Section[] getSectionList()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection_Collection getSectionCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection fillSection()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection[] getConnectionList()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection_Collection getConnectionCollection()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection_Collection fillConnection()
	 * @method \boolean[] getIsPrimaryList()
	 * @method \boolean[] fillIsPrimary()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_SectionConnection $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_SectionConnection $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_SectionConnection $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_SectionConnection_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection_Collection merge(?\Bitrix\Calendar\Internals\EO_SectionConnection_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_SectionConnection_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\SectionConnectionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SectionConnectionTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SectionConnection_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SectionConnection_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection_Collection fetchCollection()
	 */
	class EO_SectionConnection_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_SectionConnection_Collection wakeUpCollection($rows)
	 */
	class EO_SectionConnection_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\LocationTable:calendar/lib/internals/locationtable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Location
	 * @see \Bitrix\Calendar\Internals\LocationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_Location setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Location setSectionId(\int|\Bitrix\Main\DB\SqlExpression $sectionId)
	 * @method bool hasSectionId()
	 * @method bool isSectionIdFilled()
	 * @method bool isSectionIdChanged()
	 * @method \int remindActualSectionId()
	 * @method \int requireSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Location resetSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Location unsetSectionId()
	 * @method \int fillSectionId()
	 * @method \boolean getNecessity()
	 * @method \Bitrix\Calendar\Internals\EO_Location setNecessity(\boolean|\Bitrix\Main\DB\SqlExpression $necessity)
	 * @method bool hasNecessity()
	 * @method bool isNecessityFilled()
	 * @method bool isNecessityChanged()
	 * @method \boolean remindActualNecessity()
	 * @method \boolean requireNecessity()
	 * @method \Bitrix\Calendar\Internals\EO_Location resetNecessity()
	 * @method \Bitrix\Calendar\Internals\EO_Location unsetNecessity()
	 * @method \boolean fillNecessity()
	 * @method \int getCapacity()
	 * @method \Bitrix\Calendar\Internals\EO_Location setCapacity(\int|\Bitrix\Main\DB\SqlExpression $capacity)
	 * @method bool hasCapacity()
	 * @method bool isCapacityFilled()
	 * @method bool isCapacityChanged()
	 * @method \int remindActualCapacity()
	 * @method \int requireCapacity()
	 * @method \Bitrix\Calendar\Internals\EO_Location resetCapacity()
	 * @method \Bitrix\Calendar\Internals\EO_Location unsetCapacity()
	 * @method \int fillCapacity()
	 * @method \int getCategoryId()
	 * @method \Bitrix\Calendar\Internals\EO_Location setCategoryId(\int|\Bitrix\Main\DB\SqlExpression $categoryId)
	 * @method bool hasCategoryId()
	 * @method bool isCategoryIdFilled()
	 * @method bool isCategoryIdChanged()
	 * @method \int remindActualCategoryId()
	 * @method \int requireCategoryId()
	 * @method \Bitrix\Calendar\Internals\EO_Location resetCategoryId()
	 * @method \Bitrix\Calendar\Internals\EO_Location unsetCategoryId()
	 * @method \int fillCategoryId()
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
	 * @method \Bitrix\Calendar\Internals\EO_Location set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Location reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Location unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Location wakeUp($data)
	 */
	class EO_Location {
		/* @var \Bitrix\Calendar\Internals\LocationTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\LocationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Location_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getSectionIdList()
	 * @method \int[] fillSectionId()
	 * @method \boolean[] getNecessityList()
	 * @method \boolean[] fillNecessity()
	 * @method \int[] getCapacityList()
	 * @method \int[] fillCapacity()
	 * @method \int[] getCategoryIdList()
	 * @method \int[] fillCategoryId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Location $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Location $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Location getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Location[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Location $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Location_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Location current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_Location_Collection merge(?\Bitrix\Calendar\Internals\EO_Location_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Location_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\LocationTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\LocationTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Location_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Location fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Location_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Location_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Location fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Location_Collection fetchCollection()
	 */
	class EO_Location_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Location createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Location_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Location wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Location_Collection wakeUpCollection($rows)
	 */
	class EO_Location_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\RoomCategoryTable:calendar/lib/internals/roomcategorytable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_RoomCategory
	 * @see \Bitrix\Calendar\Internals\RoomCategoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory resetName()
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory unsetName()
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
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_RoomCategory wakeUp($data)
	 */
	class EO_RoomCategory {
		/* @var \Bitrix\Calendar\Internals\RoomCategoryTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\RoomCategoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_RoomCategory_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_RoomCategory $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_RoomCategory $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_RoomCategory $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_RoomCategory_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory_Collection merge(?\Bitrix\Calendar\Internals\EO_RoomCategory_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_RoomCategory_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\RoomCategoryTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\RoomCategoryTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_RoomCategory_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_RoomCategory_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory_Collection fetchCollection()
	 */
	class EO_RoomCategory_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_RoomCategory_Collection wakeUpCollection($rows)
	 */
	class EO_RoomCategory_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\ResourceTable:calendar/lib/internals/resourcetable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Resource
	 * @see \Bitrix\Calendar\Internals\ResourceTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setEventId(\int|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \int remindActualEventId()
	 * @method \int requireEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetEventId()
	 * @method \int fillEventId()
	 * @method \string getCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setCalType(\string|\Bitrix\Main\DB\SqlExpression $calType)
	 * @method bool hasCalType()
	 * @method bool isCalTypeFilled()
	 * @method bool isCalTypeChanged()
	 * @method \string remindActualCalType()
	 * @method \string requireCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetCalType()
	 * @method \string fillCalType()
	 * @method \int getResourceId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setResourceId(\int|\Bitrix\Main\DB\SqlExpression $resourceId)
	 * @method bool hasResourceId()
	 * @method bool isResourceIdFilled()
	 * @method bool isResourceIdChanged()
	 * @method \int remindActualResourceId()
	 * @method \int requireResourceId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetResourceId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetResourceId()
	 * @method \int fillResourceId()
	 * @method \string getParentType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setParentType(\string|\Bitrix\Main\DB\SqlExpression $parentType)
	 * @method bool hasParentType()
	 * @method bool isParentTypeFilled()
	 * @method bool isParentTypeChanged()
	 * @method \string remindActualParentType()
	 * @method \string requireParentType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetParentType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetParentType()
	 * @method \string fillParentType()
	 * @method \int getParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetParentId()
	 * @method \int fillParentId()
	 * @method \int getUfId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setUfId(\int|\Bitrix\Main\DB\SqlExpression $ufId)
	 * @method bool hasUfId()
	 * @method bool isUfIdFilled()
	 * @method bool isUfIdChanged()
	 * @method \int remindActualUfId()
	 * @method \int requireUfId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetUfId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetUfId()
	 * @method \int fillUfId()
	 * @method \Bitrix\Main\Type\DateTime getDateFromUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateFromUtc(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateFromUtc)
	 * @method bool hasDateFromUtc()
	 * @method bool isDateFromUtcFilled()
	 * @method bool isDateFromUtcChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateFromUtc()
	 * @method \Bitrix\Main\Type\DateTime requireDateFromUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateFromUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateFromUtc()
	 * @method \Bitrix\Main\Type\DateTime fillDateFromUtc()
	 * @method \Bitrix\Main\Type\DateTime getDateToUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateToUtc(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateToUtc)
	 * @method bool hasDateToUtc()
	 * @method bool isDateToUtcFilled()
	 * @method bool isDateToUtcChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateToUtc()
	 * @method \Bitrix\Main\Type\DateTime requireDateToUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateToUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateToUtc()
	 * @method \Bitrix\Main\Type\DateTime fillDateToUtc()
	 * @method \Bitrix\Main\Type\DateTime getDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateFrom)
	 * @method bool hasDateFrom()
	 * @method bool isDateFromFilled()
	 * @method bool isDateFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateFrom()
	 * @method \Bitrix\Main\Type\DateTime requireDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateFrom()
	 * @method \Bitrix\Main\Type\DateTime fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime getDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateTo)
	 * @method bool hasDateTo()
	 * @method bool isDateToFilled()
	 * @method bool isDateToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateTo()
	 * @method \Bitrix\Main\Type\DateTime requireDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateTo()
	 * @method \Bitrix\Main\Type\DateTime fillDateTo()
	 * @method \int getDuration()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDuration(\int|\Bitrix\Main\DB\SqlExpression $duration)
	 * @method bool hasDuration()
	 * @method bool isDurationFilled()
	 * @method bool isDurationChanged()
	 * @method \int remindActualDuration()
	 * @method \int requireDuration()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDuration()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDuration()
	 * @method \int fillDuration()
	 * @method \string getSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setSkipTime(\string|\Bitrix\Main\DB\SqlExpression $skipTime)
	 * @method bool hasSkipTime()
	 * @method bool isSkipTimeFilled()
	 * @method bool isSkipTimeChanged()
	 * @method \string remindActualSkipTime()
	 * @method \string requireSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetSkipTime()
	 * @method \string fillSkipTime()
	 * @method \string getTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTzFrom(\string|\Bitrix\Main\DB\SqlExpression $tzFrom)
	 * @method bool hasTzFrom()
	 * @method bool isTzFromFilled()
	 * @method bool isTzFromChanged()
	 * @method \string remindActualTzFrom()
	 * @method \string requireTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTzFrom()
	 * @method \string fillTzFrom()
	 * @method \string getTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTzTo(\string|\Bitrix\Main\DB\SqlExpression $tzTo)
	 * @method bool hasTzTo()
	 * @method bool isTzToFilled()
	 * @method bool isTzToChanged()
	 * @method \string remindActualTzTo()
	 * @method \string requireTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTzTo()
	 * @method \string fillTzTo()
	 * @method \int getTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTzOffsetFrom(\int|\Bitrix\Main\DB\SqlExpression $tzOffsetFrom)
	 * @method bool hasTzOffsetFrom()
	 * @method bool isTzOffsetFromFilled()
	 * @method bool isTzOffsetFromChanged()
	 * @method \int remindActualTzOffsetFrom()
	 * @method \int requireTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTzOffsetFrom()
	 * @method \int fillTzOffsetFrom()
	 * @method \int getTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTzOffsetTo(\int|\Bitrix\Main\DB\SqlExpression $tzOffsetTo)
	 * @method bool hasTzOffsetTo()
	 * @method bool isTzOffsetToFilled()
	 * @method bool isTzOffsetToChanged()
	 * @method \int remindActualTzOffsetTo()
	 * @method \int requireTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTzOffsetTo()
	 * @method \int fillTzOffsetTo()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getServiceName()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setServiceName(\string|\Bitrix\Main\DB\SqlExpression $serviceName)
	 * @method bool hasServiceName()
	 * @method bool isServiceNameFilled()
	 * @method bool isServiceNameChanged()
	 * @method \string remindActualServiceName()
	 * @method \string requireServiceName()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetServiceName()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetServiceName()
	 * @method \string fillServiceName()
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
	 * @method \Bitrix\Calendar\Internals\EO_Resource set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Resource reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Resource unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Resource wakeUp($data)
	 */
	class EO_Resource {
		/* @var \Bitrix\Calendar\Internals\ResourceTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\ResourceTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Resource_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEventIdList()
	 * @method \int[] fillEventId()
	 * @method \string[] getCalTypeList()
	 * @method \string[] fillCalType()
	 * @method \int[] getResourceIdList()
	 * @method \int[] fillResourceId()
	 * @method \string[] getParentTypeList()
	 * @method \string[] fillParentType()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \int[] getUfIdList()
	 * @method \int[] fillUfId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateFromUtcList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateFromUtc()
	 * @method \Bitrix\Main\Type\DateTime[] getDateToUtcList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateToUtc()
	 * @method \Bitrix\Main\Type\DateTime[] getDateFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getDateToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateTo()
	 * @method \int[] getDurationList()
	 * @method \int[] fillDuration()
	 * @method \string[] getSkipTimeList()
	 * @method \string[] fillSkipTime()
	 * @method \string[] getTzFromList()
	 * @method \string[] fillTzFrom()
	 * @method \string[] getTzToList()
	 * @method \string[] fillTzTo()
	 * @method \int[] getTzOffsetFromList()
	 * @method \int[] fillTzOffsetFrom()
	 * @method \int[] getTzOffsetToList()
	 * @method \int[] fillTzOffsetTo()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getServiceNameList()
	 * @method \string[] fillServiceName()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Resource $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Resource $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Resource getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Resource[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Resource $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Resource_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Resource current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_Resource_Collection merge(?\Bitrix\Calendar\Internals\EO_Resource_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Resource_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\ResourceTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\ResourceTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Resource_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Resource fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Resource_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Resource_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Resource fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Resource_Collection fetchCollection()
	 */
	class EO_Resource_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Resource createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Resource_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Resource wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Resource_Collection wakeUpCollection($rows)
	 */
	class EO_Resource_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\EventAttendeeTable:calendar/lib/internals/eventattendeetable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EventAttendee
	 * @see \Bitrix\Calendar\Internals\EventAttendeeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getOwnerId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setOwnerId(\int|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \int remindActualOwnerId()
	 * @method \int requireOwnerId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetOwnerId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetOwnerId()
	 * @method \int fillOwnerId()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \string getMeetingStatus()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setMeetingStatus(\string|\Bitrix\Main\DB\SqlExpression $meetingStatus)
	 * @method bool hasMeetingStatus()
	 * @method bool isMeetingStatusFilled()
	 * @method bool isMeetingStatusChanged()
	 * @method \string remindActualMeetingStatus()
	 * @method \string requireMeetingStatus()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetMeetingStatus()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetMeetingStatus()
	 * @method \string fillMeetingStatus()
	 * @method \boolean getDeleted()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setDeleted(\boolean|\Bitrix\Main\DB\SqlExpression $deleted)
	 * @method bool hasDeleted()
	 * @method bool isDeletedFilled()
	 * @method bool isDeletedChanged()
	 * @method \boolean remindActualDeleted()
	 * @method \boolean requireDeleted()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetDeleted()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetDeleted()
	 * @method \boolean fillDeleted()
	 * @method \int getSectionId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setSectionId(\int|\Bitrix\Main\DB\SqlExpression $sectionId)
	 * @method bool hasSectionId()
	 * @method bool isSectionIdFilled()
	 * @method bool isSectionIdChanged()
	 * @method \int remindActualSectionId()
	 * @method \int requireSectionId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetSectionId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetSectionId()
	 * @method \int fillSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Section getSection()
	 * @method \Bitrix\Calendar\Internals\EO_Section remindActualSection()
	 * @method \Bitrix\Calendar\Internals\EO_Section requireSection()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setSection(\Bitrix\Calendar\Internals\EO_Section $object)
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetSection()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetSection()
	 * @method bool hasSection()
	 * @method bool isSectionFilled()
	 * @method bool isSectionChanged()
	 * @method \Bitrix\Calendar\Internals\EO_Section fillSection()
	 * @method null|\string getColor()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setColor(null|\string|\Bitrix\Main\DB\SqlExpression $color)
	 * @method bool hasColor()
	 * @method bool isColorFilled()
	 * @method bool isColorChanged()
	 * @method null|\string remindActualColor()
	 * @method null|\string requireColor()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetColor()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetColor()
	 * @method null|\string fillColor()
	 * @method \string getRemind()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setRemind(\string|\Bitrix\Main\DB\SqlExpression $remind)
	 * @method bool hasRemind()
	 * @method bool isRemindFilled()
	 * @method bool isRemindChanged()
	 * @method \string remindActualRemind()
	 * @method \string requireRemind()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetRemind()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetRemind()
	 * @method \string fillRemind()
	 * @method null|\string getDavExchLabel()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setDavExchLabel(null|\string|\Bitrix\Main\DB\SqlExpression $davExchLabel)
	 * @method bool hasDavExchLabel()
	 * @method bool isDavExchLabelFilled()
	 * @method bool isDavExchLabelChanged()
	 * @method null|\string remindActualDavExchLabel()
	 * @method null|\string requireDavExchLabel()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetDavExchLabel()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetDavExchLabel()
	 * @method null|\string fillDavExchLabel()
	 * @method \string getSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setSyncStatus(\string|\Bitrix\Main\DB\SqlExpression $syncStatus)
	 * @method bool hasSyncStatus()
	 * @method bool isSyncStatusFilled()
	 * @method bool isSyncStatusChanged()
	 * @method \string remindActualSyncStatus()
	 * @method \string requireSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetSyncStatus()
	 * @method \string fillSyncStatus()
	 * @method \int getEventId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setEventId(\int|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \int remindActualEventId()
	 * @method \int requireEventId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetEventId()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetEventId()
	 * @method \int fillEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Event getEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event remindActualEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event requireEvent()
	 * @method \Bitrix\Calendar\Internals\EventAttendee setEvent(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method \Bitrix\Calendar\Internals\EventAttendee resetEvent()
	 * @method \Bitrix\Calendar\Internals\EventAttendee unsetEvent()
	 * @method bool hasEvent()
	 * @method bool isEventFilled()
	 * @method bool isEventChanged()
	 * @method \Bitrix\Calendar\Internals\EO_Event fillEvent()
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
	 * @method \Bitrix\Calendar\Internals\EventAttendee set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EventAttendee reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EventAttendee unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EventAttendee wakeUp($data)
	 */
	class EO_EventAttendee {
		/* @var \Bitrix\Calendar\Internals\EventAttendeeTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventAttendeeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_EventAttendee_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getOwnerIdList()
	 * @method \int[] fillOwnerId()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \string[] getMeetingStatusList()
	 * @method \string[] fillMeetingStatus()
	 * @method \boolean[] getDeletedList()
	 * @method \boolean[] fillDeleted()
	 * @method \int[] getSectionIdList()
	 * @method \int[] fillSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Section[] getSectionList()
	 * @method \Bitrix\Calendar\Internals\EO_EventAttendee_Collection getSectionCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection fillSection()
	 * @method null|\string[] getColorList()
	 * @method null|\string[] fillColor()
	 * @method \string[] getRemindList()
	 * @method \string[] fillRemind()
	 * @method null|\string[] getDavExchLabelList()
	 * @method null|\string[] fillDavExchLabel()
	 * @method \string[] getSyncStatusList()
	 * @method \string[] fillSyncStatus()
	 * @method \int[] getEventIdList()
	 * @method \int[] fillEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Event[] getEventList()
	 * @method \Bitrix\Calendar\Internals\EO_EventAttendee_Collection getEventCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection fillEvent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EventAttendee $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EventAttendee $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EventAttendee getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EventAttendee[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EventAttendee $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_EventAttendee_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EventAttendee current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_EventAttendee_Collection merge(?\Bitrix\Calendar\Internals\EO_EventAttendee_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_EventAttendee_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\EventAttendeeTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventAttendeeTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventAttendee_Result exec()
	 * @method \Bitrix\Calendar\Internals\EventAttendee fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_EventAttendee_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventAttendee_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EventAttendee fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_EventAttendee_Collection fetchCollection()
	 */
	class EO_EventAttendee_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EventAttendee createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_EventAttendee_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EventAttendee wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_EventAttendee_Collection wakeUpCollection($rows)
	 */
	class EO_EventAttendee_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\QueueMessageTable:calendar/lib/internals/queuemessagetable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_QueueMessage
	 * @see \Bitrix\Calendar\Internals\QueueMessageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method array getMessage()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage setMessage(array|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method array remindActualMessage()
	 * @method array requireMessage()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage resetMessage()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage unsetMessage()
	 * @method array fillMessage()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage resetDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
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
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_QueueMessage wakeUp($data)
	 */
	class EO_QueueMessage {
		/* @var \Bitrix\Calendar\Internals\QueueMessageTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\QueueMessageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_QueueMessage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method array[] getMessageList()
	 * @method array[] fillMessage()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_QueueMessage $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_QueueMessage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_QueueMessage $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_QueueMessage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage_Collection merge(?\Bitrix\Calendar\Internals\EO_QueueMessage_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_QueueMessage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\QueueMessageTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\QueueMessageTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_QueueMessage_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_QueueMessage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage_Collection fetchCollection()
	 */
	class EO_QueueMessage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage_Collection wakeUpCollection($rows)
	 */
	class EO_QueueMessage_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\TypeTable:calendar/lib/internals/typetable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Type
	 * @see \Bitrix\Calendar\Internals\TypeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Type setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Calendar\Internals\EO_Type setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Calendar\Internals\EO_Type resetName()
	 * @method \Bitrix\Calendar\Internals\EO_Type unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Type setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Type resetDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Type unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Type setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Type resetExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Type unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Calendar\Internals\EO_Type setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Calendar\Internals\EO_Type resetActive()
	 * @method \Bitrix\Calendar\Internals\EO_Type unsetActive()
	 * @method \boolean fillActive()
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
	 * @method \Bitrix\Calendar\Internals\EO_Type set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Type reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Type unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Type wakeUp($data)
	 */
	class EO_Type {
		/* @var \Bitrix\Calendar\Internals\TypeTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\TypeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Type_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getXmlIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Type $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Type $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Type getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Type[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Type $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Type_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Type current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_Type_Collection merge(?\Bitrix\Calendar\Internals\EO_Type_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Type_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\TypeTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\TypeTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Type_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Type fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Type_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Type_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Type fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Type_Collection fetchCollection()
	 */
	class EO_Type_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Type createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Type_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Type wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Type_Collection wakeUpCollection($rows)
	 */
	class EO_Type_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\SharingObjectRuleTable:calendar/lib/internals/sharingobjectruletable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SharingObjectRule
	 * @see \Bitrix\Calendar\Internals\SharingObjectRuleTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getObjectId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule setObjectId(\int|\Bitrix\Main\DB\SqlExpression $objectId)
	 * @method bool hasObjectId()
	 * @method bool isObjectIdFilled()
	 * @method bool isObjectIdChanged()
	 * @method \int remindActualObjectId()
	 * @method \int requireObjectId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule resetObjectId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule unsetObjectId()
	 * @method \int fillObjectId()
	 * @method \string getObjectType()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule setObjectType(\string|\Bitrix\Main\DB\SqlExpression $objectType)
	 * @method bool hasObjectType()
	 * @method bool isObjectTypeFilled()
	 * @method bool isObjectTypeChanged()
	 * @method \string remindActualObjectType()
	 * @method \string requireObjectType()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule resetObjectType()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule unsetObjectType()
	 * @method \string fillObjectType()
	 * @method \int getSlotSize()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule setSlotSize(\int|\Bitrix\Main\DB\SqlExpression $slotSize)
	 * @method bool hasSlotSize()
	 * @method bool isSlotSizeFilled()
	 * @method bool isSlotSizeChanged()
	 * @method \int remindActualSlotSize()
	 * @method \int requireSlotSize()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule resetSlotSize()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule unsetSlotSize()
	 * @method \int fillSlotSize()
	 * @method \string getWeekdays()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule setWeekdays(\string|\Bitrix\Main\DB\SqlExpression $weekdays)
	 * @method bool hasWeekdays()
	 * @method bool isWeekdaysFilled()
	 * @method bool isWeekdaysChanged()
	 * @method \string remindActualWeekdays()
	 * @method \string requireWeekdays()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule resetWeekdays()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule unsetWeekdays()
	 * @method \string fillWeekdays()
	 * @method \int getTimeFrom()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule setTimeFrom(\int|\Bitrix\Main\DB\SqlExpression $timeFrom)
	 * @method bool hasTimeFrom()
	 * @method bool isTimeFromFilled()
	 * @method bool isTimeFromChanged()
	 * @method \int remindActualTimeFrom()
	 * @method \int requireTimeFrom()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule resetTimeFrom()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule unsetTimeFrom()
	 * @method \int fillTimeFrom()
	 * @method \int getTimeTo()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule setTimeTo(\int|\Bitrix\Main\DB\SqlExpression $timeTo)
	 * @method bool hasTimeTo()
	 * @method bool isTimeToFilled()
	 * @method bool isTimeToChanged()
	 * @method \int remindActualTimeTo()
	 * @method \int requireTimeTo()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule resetTimeTo()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule unsetTimeTo()
	 * @method \int fillTimeTo()
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
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_SharingObjectRule wakeUp($data)
	 */
	class EO_SharingObjectRule {
		/* @var \Bitrix\Calendar\Internals\SharingObjectRuleTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SharingObjectRuleTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SharingObjectRule_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getObjectIdList()
	 * @method \int[] fillObjectId()
	 * @method \string[] getObjectTypeList()
	 * @method \string[] fillObjectType()
	 * @method \int[] getSlotSizeList()
	 * @method \int[] fillSlotSize()
	 * @method \string[] getWeekdaysList()
	 * @method \string[] fillWeekdays()
	 * @method \int[] getTimeFromList()
	 * @method \int[] fillTimeFrom()
	 * @method \int[] getTimeToList()
	 * @method \int[] fillTimeTo()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_SharingObjectRule $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_SharingObjectRule $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_SharingObjectRule $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_SharingObjectRule_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule_Collection merge(?\Bitrix\Calendar\Internals\EO_SharingObjectRule_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_SharingObjectRule_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\SharingObjectRuleTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SharingObjectRuleTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SharingObjectRule_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SharingObjectRule_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule_Collection fetchCollection()
	 */
	class EO_SharingObjectRule_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_SharingObjectRule_Collection wakeUpCollection($rows)
	 */
	class EO_SharingObjectRule_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\EventOriginalRecursionTable:calendar/lib/internals/eventoriginalrecursiontable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_EventOriginalRecursion
	 * @see \Bitrix\Calendar\Internals\EventOriginalRecursionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getParentEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion setParentEventId(\int|\Bitrix\Main\DB\SqlExpression $parentEventId)
	 * @method bool hasParentEventId()
	 * @method bool isParentEventIdFilled()
	 * @method bool isParentEventIdChanged()
	 * @method \int getOriginalRecursionEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion setOriginalRecursionEventId(\int|\Bitrix\Main\DB\SqlExpression $originalRecursionEventId)
	 * @method bool hasOriginalRecursionEventId()
	 * @method bool isOriginalRecursionEventIdFilled()
	 * @method bool isOriginalRecursionEventIdChanged()
	 * @method \int remindActualOriginalRecursionEventId()
	 * @method \int requireOriginalRecursionEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion resetOriginalRecursionEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion unsetOriginalRecursionEventId()
	 * @method \int fillOriginalRecursionEventId()
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
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_EventOriginalRecursion wakeUp($data)
	 */
	class EO_EventOriginalRecursion {
		/* @var \Bitrix\Calendar\Internals\EventOriginalRecursionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventOriginalRecursionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_EventOriginalRecursion_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getParentEventIdList()
	 * @method \int[] getOriginalRecursionEventIdList()
	 * @method \int[] fillOriginalRecursionEventId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_EventOriginalRecursion $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_EventOriginalRecursion $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_EventOriginalRecursion $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_EventOriginalRecursion_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion_Collection merge(?\Bitrix\Calendar\Internals\EO_EventOriginalRecursion_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_EventOriginalRecursion_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\EventOriginalRecursionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventOriginalRecursionTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventOriginalRecursion_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventOriginalRecursion_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion_Collection fetchCollection()
	 */
	class EO_EventOriginalRecursion_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_EventOriginalRecursion_Collection wakeUpCollection($rows)
	 */
	class EO_EventOriginalRecursion_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\QueueHandledMessageTable:calendar/lib/internals/queuehandledmessagetable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_QueueHandledMessage
	 * @see \Bitrix\Calendar\Internals\QueueHandledMessageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMessageId()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int remindActualMessageId()
	 * @method \int requireMessageId()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage resetMessageId()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage unsetMessageId()
	 * @method \int fillMessageId()
	 * @method \int getQueueId()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage setQueueId(\int|\Bitrix\Main\DB\SqlExpression $queueId)
	 * @method bool hasQueueId()
	 * @method bool isQueueIdFilled()
	 * @method bool isQueueIdChanged()
	 * @method \int remindActualQueueId()
	 * @method \int requireQueueId()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage resetQueueId()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage unsetQueueId()
	 * @method \int fillQueueId()
	 * @method \string getHash()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage setHash(\string|\Bitrix\Main\DB\SqlExpression $hash)
	 * @method bool hasHash()
	 * @method bool isHashFilled()
	 * @method bool isHashChanged()
	 * @method \string remindActualHash()
	 * @method \string requireHash()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage resetHash()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage unsetHash()
	 * @method \string fillHash()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage resetDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage getMessage()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage remindActualMessage()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage requireMessage()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage setMessage(\Bitrix\Calendar\Internals\EO_QueueMessage $object)
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage resetMessage()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage unsetMessage()
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage fillMessage()
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
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_QueueHandledMessage wakeUp($data)
	 */
	class EO_QueueHandledMessage {
		/* @var \Bitrix\Calendar\Internals\QueueHandledMessageTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\QueueHandledMessageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_QueueHandledMessage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getMessageIdList()
	 * @method \int[] fillMessageId()
	 * @method \int[] getQueueIdList()
	 * @method \int[] fillQueueId()
	 * @method \string[] getHashList()
	 * @method \string[] fillHash()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage[] getMessageList()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection getMessageCollection()
	 * @method \Bitrix\Calendar\Internals\EO_QueueMessage_Collection fillMessage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_QueueHandledMessage $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_QueueHandledMessage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_QueueHandledMessage $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection merge(?\Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_QueueHandledMessage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\QueueHandledMessageTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\QueueHandledMessageTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_QueueHandledMessage_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_QueueHandledMessage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection fetchCollection()
	 */
	class EO_QueueHandledMessage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_QueueHandledMessage_Collection wakeUpCollection($rows)
	 */
	class EO_QueueHandledMessage_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\EventConnectionTable:calendar/lib/internals/eventconnectiontable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_EventConnection
	 * @see \Bitrix\Calendar\Internals\EventConnectionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setEventId(\int|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \int remindActualEventId()
	 * @method \int requireEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetEventId()
	 * @method \int fillEventId()
	 * @method \int getConnectionId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setConnectionId(\int|\Bitrix\Main\DB\SqlExpression $connectionId)
	 * @method bool hasConnectionId()
	 * @method bool isConnectionIdFilled()
	 * @method bool isConnectionIdChanged()
	 * @method \int remindActualConnectionId()
	 * @method \int requireConnectionId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetConnectionId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetConnectionId()
	 * @method \int fillConnectionId()
	 * @method \string getVendorEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setVendorEventId(\string|\Bitrix\Main\DB\SqlExpression $vendorEventId)
	 * @method bool hasVendorEventId()
	 * @method bool isVendorEventIdFilled()
	 * @method bool isVendorEventIdChanged()
	 * @method \string remindActualVendorEventId()
	 * @method \string requireVendorEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetVendorEventId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetVendorEventId()
	 * @method \string fillVendorEventId()
	 * @method null|\string getSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setSyncStatus(null|\string|\Bitrix\Main\DB\SqlExpression $syncStatus)
	 * @method bool hasSyncStatus()
	 * @method bool isSyncStatusFilled()
	 * @method bool isSyncStatusChanged()
	 * @method null|\string remindActualSyncStatus()
	 * @method null|\string requireSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetSyncStatus()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetSyncStatus()
	 * @method null|\string fillSyncStatus()
	 * @method \int getRetryCount()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setRetryCount(\int|\Bitrix\Main\DB\SqlExpression $retryCount)
	 * @method bool hasRetryCount()
	 * @method bool isRetryCountFilled()
	 * @method bool isRetryCountChanged()
	 * @method \int remindActualRetryCount()
	 * @method \int requireRetryCount()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetRetryCount()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetRetryCount()
	 * @method \int fillRetryCount()
	 * @method null|\string getEntityTag()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setEntityTag(null|\string|\Bitrix\Main\DB\SqlExpression $entityTag)
	 * @method bool hasEntityTag()
	 * @method bool isEntityTagFilled()
	 * @method bool isEntityTagChanged()
	 * @method null|\string remindActualEntityTag()
	 * @method null|\string requireEntityTag()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetEntityTag()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetEntityTag()
	 * @method null|\string fillEntityTag()
	 * @method null|\string getVendorVersionId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setVendorVersionId(null|\string|\Bitrix\Main\DB\SqlExpression $vendorVersionId)
	 * @method bool hasVendorVersionId()
	 * @method bool isVendorVersionIdFilled()
	 * @method bool isVendorVersionIdChanged()
	 * @method null|\string remindActualVendorVersionId()
	 * @method null|\string requireVendorVersionId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetVendorVersionId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetVendorVersionId()
	 * @method null|\string fillVendorVersionId()
	 * @method null|\string getVersion()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setVersion(null|\string|\Bitrix\Main\DB\SqlExpression $version)
	 * @method bool hasVersion()
	 * @method bool isVersionFilled()
	 * @method bool isVersionChanged()
	 * @method null|\string remindActualVersion()
	 * @method null|\string requireVersion()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetVersion()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetVersion()
	 * @method null|\string fillVersion()
	 * @method null|array getData()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setData(null|array|\Bitrix\Main\DB\SqlExpression $data)
	 * @method bool hasData()
	 * @method bool isDataFilled()
	 * @method bool isDataChanged()
	 * @method null|array remindActualData()
	 * @method null|array requireData()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetData()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetData()
	 * @method null|array fillData()
	 * @method null|\string getRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setRecurrenceId(null|\string|\Bitrix\Main\DB\SqlExpression $recurrenceId)
	 * @method bool hasRecurrenceId()
	 * @method bool isRecurrenceIdFilled()
	 * @method bool isRecurrenceIdChanged()
	 * @method null|\string remindActualRecurrenceId()
	 * @method null|\string requireRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetRecurrenceId()
	 * @method null|\string fillRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_Event getEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event remindActualEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event requireEvent()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setEvent(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetEvent()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetEvent()
	 * @method bool hasEvent()
	 * @method bool isEventFilled()
	 * @method bool isEventChanged()
	 * @method \Bitrix\Calendar\Internals\EO_Event fillEvent()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection getConnection()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection remindActualConnection()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection requireConnection()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection setConnection(\Bitrix\Dav\Internals\EO_DavConnection $object)
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection resetConnection()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unsetConnection()
	 * @method bool hasConnection()
	 * @method bool isConnectionFilled()
	 * @method bool isConnectionChanged()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection fillConnection()
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
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_EventConnection wakeUp($data)
	 */
	class EO_EventConnection {
		/* @var \Bitrix\Calendar\Internals\EventConnectionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventConnectionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_EventConnection_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEventIdList()
	 * @method \int[] fillEventId()
	 * @method \int[] getConnectionIdList()
	 * @method \int[] fillConnectionId()
	 * @method \string[] getVendorEventIdList()
	 * @method \string[] fillVendorEventId()
	 * @method null|\string[] getSyncStatusList()
	 * @method null|\string[] fillSyncStatus()
	 * @method \int[] getRetryCountList()
	 * @method \int[] fillRetryCount()
	 * @method null|\string[] getEntityTagList()
	 * @method null|\string[] fillEntityTag()
	 * @method null|\string[] getVendorVersionIdList()
	 * @method null|\string[] fillVendorVersionId()
	 * @method null|\string[] getVersionList()
	 * @method null|\string[] fillVersion()
	 * @method null|array[] getDataList()
	 * @method null|array[] fillData()
	 * @method null|\string[] getRecurrenceIdList()
	 * @method null|\string[] fillRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_Event[] getEventList()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection_Collection getEventCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection fillEvent()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection[] getConnectionList()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection_Collection getConnectionCollection()
	 * @method \Bitrix\Dav\Internals\EO_DavConnection_Collection fillConnection()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_EventConnection $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_EventConnection $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_EventConnection $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_EventConnection_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection_Collection merge(?\Bitrix\Calendar\Internals\EO_EventConnection_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_EventConnection_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\EventConnectionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventConnectionTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventConnection_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventConnection_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection_Collection fetchCollection()
	 */
	class EO_EventConnection_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_EventConnection_Collection wakeUpCollection($rows)
	 */
	class EO_EventConnection_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\AccessTable:calendar/lib/internals/accesstable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Access
	 * @see \Bitrix\Calendar\Internals\AccessTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getAccessCode()
	 * @method \Bitrix\Calendar\Internals\EO_Access setAccessCode(\string|\Bitrix\Main\DB\SqlExpression $accessCode)
	 * @method bool hasAccessCode()
	 * @method bool isAccessCodeFilled()
	 * @method bool isAccessCodeChanged()
	 * @method \int getTaskId()
	 * @method \Bitrix\Calendar\Internals\EO_Access setTaskId(\int|\Bitrix\Main\DB\SqlExpression $taskId)
	 * @method bool hasTaskId()
	 * @method bool isTaskIdFilled()
	 * @method bool isTaskIdChanged()
	 * @method \string getSectId()
	 * @method \Bitrix\Calendar\Internals\EO_Access setSectId(\string|\Bitrix\Main\DB\SqlExpression $sectId)
	 * @method bool hasSectId()
	 * @method bool isSectIdFilled()
	 * @method bool isSectIdChanged()
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
	 * @method \Bitrix\Calendar\Internals\EO_Access set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Access reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Access unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Access wakeUp($data)
	 */
	class EO_Access {
		/* @var \Bitrix\Calendar\Internals\AccessTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\AccessTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Access_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getAccessCodeList()
	 * @method \int[] getTaskIdList()
	 * @method \string[] getSectIdList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Access $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Access $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Access getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Access[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Access $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Access_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Access current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_Access_Collection merge(?\Bitrix\Calendar\Internals\EO_Access_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Access_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\AccessTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\AccessTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Access_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Access fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Access_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Access_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Access fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Access_Collection fetchCollection()
	 */
	class EO_Access_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Access createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Access_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Access wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Access_Collection wakeUpCollection($rows)
	 */
	class EO_Access_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\SharingLinkMemberTable:calendar/lib/internals/sharinglinkmembertable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SharingLinkMember
	 * @see \Bitrix\Calendar\Internals\SharingLinkMemberTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getLinkId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember setLinkId(\int|\Bitrix\Main\DB\SqlExpression $linkId)
	 * @method bool hasLinkId()
	 * @method bool isLinkIdFilled()
	 * @method bool isLinkIdChanged()
	 * @method \int remindActualLinkId()
	 * @method \int requireLinkId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember resetLinkId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember unsetLinkId()
	 * @method \int fillLinkId()
	 * @method \int getMemberId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember setMemberId(\int|\Bitrix\Main\DB\SqlExpression $memberId)
	 * @method bool hasMemberId()
	 * @method bool isMemberIdFilled()
	 * @method bool isMemberIdChanged()
	 * @method \int remindActualMemberId()
	 * @method \int requireMemberId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember resetMemberId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember unsetMemberId()
	 * @method \int fillMemberId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink getMember()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink remindActualMember()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink requireMember()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember setMember(\Bitrix\Calendar\Internals\EO_SharingLink $object)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember resetMember()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember unsetMember()
	 * @method bool hasMember()
	 * @method bool isMemberFilled()
	 * @method bool isMemberChanged()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink fillMember()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember resetUser()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Main\EO_File getImage()
	 * @method \Bitrix\Main\EO_File remindActualImage()
	 * @method \Bitrix\Main\EO_File requireImage()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember setImage(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember resetImage()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember unsetImage()
	 * @method bool hasImage()
	 * @method bool isImageFilled()
	 * @method bool isImageChanged()
	 * @method \Bitrix\Main\EO_File fillImage()
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
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkMember wakeUp($data)
	 */
	class EO_SharingLinkMember {
		/* @var \Bitrix\Calendar\Internals\SharingLinkMemberTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SharingLinkMemberTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_SharingLinkMember_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getLinkIdList()
	 * @method \int[] fillLinkId()
	 * @method \int[] getMemberIdList()
	 * @method \int[] fillMemberId()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink[] getMemberList()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection getMemberCollection()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLink_Collection fillMember()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Main\EO_File[] getImageList()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection getImageCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillImage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_SharingLinkMember $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_SharingLinkMember $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_SharingLinkMember $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection merge(?\Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_SharingLinkMember_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\SharingLinkMemberTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SharingLinkMemberTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SharingLinkMember_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SharingLinkMember_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection fetchCollection()
	 */
	class EO_SharingLinkMember_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_SharingLinkMember_Collection wakeUpCollection($rows)
	 */
	class EO_SharingLinkMember_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\Counter\CounterTable:calendar/lib/internals/counter/countertable.php */
namespace Bitrix\Calendar\Internals\Counter {
	/**
	 * EO_Counter
	 * @see \Bitrix\Calendar\Internals\Counter\CounterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter resetUserId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getEventId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter setEventId(\int|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \int remindActualEventId()
	 * @method \int requireEventId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter resetEventId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter unsetEventId()
	 * @method \int fillEventId()
	 * @method \int getParentId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter resetParentId()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter unsetParentId()
	 * @method \int fillParentId()
	 * @method \string getType()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter resetType()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter unsetType()
	 * @method \string fillType()
	 * @method \int getValue()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter setValue(\int|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \int remindActualValue()
	 * @method \int requireValue()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter resetValue()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter unsetValue()
	 * @method \int fillValue()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter resetUser()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Calendar\Internals\EO_Event getEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event remindActualEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event requireEvent()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter setEvent(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter resetEvent()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter unsetEvent()
	 * @method bool hasEvent()
	 * @method bool isEventFilled()
	 * @method bool isEventChanged()
	 * @method \Bitrix\Calendar\Internals\EO_Event fillEvent()
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
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\Counter\EO_Counter wakeUp($data)
	 */
	class EO_Counter {
		/* @var \Bitrix\Calendar\Internals\Counter\CounterTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\Counter\CounterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals\Counter {
	/**
	 * EO_Counter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getEventIdList()
	 * @method \int[] fillEventId()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \int[] getValueList()
	 * @method \int[] fillValue()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Calendar\Internals\EO_Event[] getEventList()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection getEventCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection fillEvent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\Counter\EO_Counter $object)
	 * @method bool has(\Bitrix\Calendar\Internals\Counter\EO_Counter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\Counter\EO_Counter $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection merge(?\Bitrix\Calendar\Internals\Counter\EO_Counter_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Counter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\Counter\CounterTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\Counter\CounterTable';
	}
}
namespace Bitrix\Calendar\Internals\Counter {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Counter_Result exec()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter fetchObject()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Counter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter fetchObject()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection fetchCollection()
	 */
	class EO_Counter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\Counter\EO_Counter_Collection wakeUpCollection($rows)
	 */
	class EO_Counter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\CalendarLogTable:calendar/lib/internals/calendarlogtable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_CalendarLog
	 * @see \Bitrix\Calendar\Internals\CalendarLogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog resetTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getMessage()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog resetMessage()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog unsetMessage()
	 * @method \string fillMessage()
	 * @method \string getType()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog resetType()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog unsetType()
	 * @method \string fillType()
	 * @method \string getUuid()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog setUuid(\string|\Bitrix\Main\DB\SqlExpression $uuid)
	 * @method bool hasUuid()
	 * @method bool isUuidFilled()
	 * @method bool isUuidChanged()
	 * @method \string remindActualUuid()
	 * @method \string requireUuid()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog resetUuid()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog unsetUuid()
	 * @method \string fillUuid()
	 * @method \int getUserId()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog resetUserId()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog unsetUserId()
	 * @method \int fillUserId()
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
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_CalendarLog wakeUp($data)
	 */
	class EO_CalendarLog {
		/* @var \Bitrix\Calendar\Internals\CalendarLogTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\CalendarLogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_CalendarLog_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getUuidList()
	 * @method \string[] fillUuid()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_CalendarLog $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_CalendarLog $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_CalendarLog $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_CalendarLog_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog_Collection merge(?\Bitrix\Calendar\Internals\EO_CalendarLog_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_CalendarLog_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\CalendarLogTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\CalendarLogTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CalendarLog_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CalendarLog_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog_Collection fetchCollection()
	 */
	class EO_CalendarLog_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_CalendarLog_Collection wakeUpCollection($rows)
	 */
	class EO_CalendarLog_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\PushTable:calendar/lib/internals/pushtable.php */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Push
	 * @see \Bitrix\Calendar\Internals\PushTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getEntityType()
	 * @method \Bitrix\Calendar\Internals\EO_Push setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Calendar\Internals\EO_Push setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string getChannelId()
	 * @method \Bitrix\Calendar\Internals\EO_Push setChannelId(\string|\Bitrix\Main\DB\SqlExpression $channelId)
	 * @method bool hasChannelId()
	 * @method bool isChannelIdFilled()
	 * @method bool isChannelIdChanged()
	 * @method \string remindActualChannelId()
	 * @method \string requireChannelId()
	 * @method \Bitrix\Calendar\Internals\EO_Push resetChannelId()
	 * @method \Bitrix\Calendar\Internals\EO_Push unsetChannelId()
	 * @method \string fillChannelId()
	 * @method \string getResourceId()
	 * @method \Bitrix\Calendar\Internals\EO_Push setResourceId(\string|\Bitrix\Main\DB\SqlExpression $resourceId)
	 * @method bool hasResourceId()
	 * @method bool isResourceIdFilled()
	 * @method bool isResourceIdChanged()
	 * @method \string remindActualResourceId()
	 * @method \string requireResourceId()
	 * @method \Bitrix\Calendar\Internals\EO_Push resetResourceId()
	 * @method \Bitrix\Calendar\Internals\EO_Push unsetResourceId()
	 * @method \string fillResourceId()
	 * @method \Bitrix\Main\Type\DateTime getExpires()
	 * @method \Bitrix\Calendar\Internals\EO_Push setExpires(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $expires)
	 * @method bool hasExpires()
	 * @method bool isExpiresFilled()
	 * @method bool isExpiresChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualExpires()
	 * @method \Bitrix\Main\Type\DateTime requireExpires()
	 * @method \Bitrix\Calendar\Internals\EO_Push resetExpires()
	 * @method \Bitrix\Calendar\Internals\EO_Push unsetExpires()
	 * @method \Bitrix\Main\Type\DateTime fillExpires()
	 * @method \string getNotProcessed()
	 * @method \Bitrix\Calendar\Internals\EO_Push setNotProcessed(\string|\Bitrix\Main\DB\SqlExpression $notProcessed)
	 * @method bool hasNotProcessed()
	 * @method bool isNotProcessedFilled()
	 * @method bool isNotProcessedChanged()
	 * @method \string remindActualNotProcessed()
	 * @method \string requireNotProcessed()
	 * @method \Bitrix\Calendar\Internals\EO_Push resetNotProcessed()
	 * @method \Bitrix\Calendar\Internals\EO_Push unsetNotProcessed()
	 * @method \string fillNotProcessed()
	 * @method \Bitrix\Main\Type\DateTime getFirstPushDate()
	 * @method \Bitrix\Calendar\Internals\EO_Push setFirstPushDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $firstPushDate)
	 * @method bool hasFirstPushDate()
	 * @method bool isFirstPushDateFilled()
	 * @method bool isFirstPushDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualFirstPushDate()
	 * @method \Bitrix\Main\Type\DateTime requireFirstPushDate()
	 * @method \Bitrix\Calendar\Internals\EO_Push resetFirstPushDate()
	 * @method \Bitrix\Calendar\Internals\EO_Push unsetFirstPushDate()
	 * @method \Bitrix\Main\Type\DateTime fillFirstPushDate()
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
	 * @method \Bitrix\Calendar\Internals\EO_Push set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Push reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Push unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Push wakeUp($data)
	 */
	class EO_Push {
		/* @var \Bitrix\Calendar\Internals\PushTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\PushTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Push_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getEntityTypeList()
	 * @method \int[] getEntityIdList()
	 * @method \string[] getChannelIdList()
	 * @method \string[] fillChannelId()
	 * @method \string[] getResourceIdList()
	 * @method \string[] fillResourceId()
	 * @method \Bitrix\Main\Type\DateTime[] getExpiresList()
	 * @method \Bitrix\Main\Type\DateTime[] fillExpires()
	 * @method \string[] getNotProcessedList()
	 * @method \string[] fillNotProcessed()
	 * @method \Bitrix\Main\Type\DateTime[] getFirstPushDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillFirstPushDate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Push $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Push $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Push getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Push[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Push $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Push_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Push current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\Internals\EO_Push_Collection merge(?\Bitrix\Calendar\Internals\EO_Push_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Push_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\PushTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\PushTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Push_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Push fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Push_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Push_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Push fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Push_Collection fetchCollection()
	 */
	class EO_Push_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Push createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Push_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Push wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Push_Collection wakeUpCollection($rows)
	 */
	class EO_Push_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\PushTable:calendar/lib/push.php */
namespace Bitrix\Calendar {
	/**
	 * EO_Push
	 * @see \Bitrix\Calendar\PushTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getEntityType()
	 * @method \Bitrix\Calendar\EO_Push setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Calendar\EO_Push setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string getChannelId()
	 * @method \Bitrix\Calendar\EO_Push setChannelId(\string|\Bitrix\Main\DB\SqlExpression $channelId)
	 * @method bool hasChannelId()
	 * @method bool isChannelIdFilled()
	 * @method bool isChannelIdChanged()
	 * @method \string remindActualChannelId()
	 * @method \string requireChannelId()
	 * @method \Bitrix\Calendar\EO_Push resetChannelId()
	 * @method \Bitrix\Calendar\EO_Push unsetChannelId()
	 * @method \string fillChannelId()
	 * @method \string getResourceId()
	 * @method \Bitrix\Calendar\EO_Push setResourceId(\string|\Bitrix\Main\DB\SqlExpression $resourceId)
	 * @method bool hasResourceId()
	 * @method bool isResourceIdFilled()
	 * @method bool isResourceIdChanged()
	 * @method \string remindActualResourceId()
	 * @method \string requireResourceId()
	 * @method \Bitrix\Calendar\EO_Push resetResourceId()
	 * @method \Bitrix\Calendar\EO_Push unsetResourceId()
	 * @method \string fillResourceId()
	 * @method \Bitrix\Main\Type\DateTime getExpires()
	 * @method \Bitrix\Calendar\EO_Push setExpires(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $expires)
	 * @method bool hasExpires()
	 * @method bool isExpiresFilled()
	 * @method bool isExpiresChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualExpires()
	 * @method \Bitrix\Main\Type\DateTime requireExpires()
	 * @method \Bitrix\Calendar\EO_Push resetExpires()
	 * @method \Bitrix\Calendar\EO_Push unsetExpires()
	 * @method \Bitrix\Main\Type\DateTime fillExpires()
	 * @method \string getNotProcessed()
	 * @method \Bitrix\Calendar\EO_Push setNotProcessed(\string|\Bitrix\Main\DB\SqlExpression $notProcessed)
	 * @method bool hasNotProcessed()
	 * @method bool isNotProcessedFilled()
	 * @method bool isNotProcessedChanged()
	 * @method \string remindActualNotProcessed()
	 * @method \string requireNotProcessed()
	 * @method \Bitrix\Calendar\EO_Push resetNotProcessed()
	 * @method \Bitrix\Calendar\EO_Push unsetNotProcessed()
	 * @method \string fillNotProcessed()
	 * @method \Bitrix\Main\Type\DateTime getFirstPushDate()
	 * @method \Bitrix\Calendar\EO_Push setFirstPushDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $firstPushDate)
	 * @method bool hasFirstPushDate()
	 * @method bool isFirstPushDateFilled()
	 * @method bool isFirstPushDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualFirstPushDate()
	 * @method \Bitrix\Main\Type\DateTime requireFirstPushDate()
	 * @method \Bitrix\Calendar\EO_Push resetFirstPushDate()
	 * @method \Bitrix\Calendar\EO_Push unsetFirstPushDate()
	 * @method \Bitrix\Main\Type\DateTime fillFirstPushDate()
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
	 * @method \Bitrix\Calendar\EO_Push set($fieldName, $value)
	 * @method \Bitrix\Calendar\EO_Push reset($fieldName)
	 * @method \Bitrix\Calendar\EO_Push unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method mixed fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\EO_Push wakeUp($data)
	 */
	class EO_Push {
		/* @var \Bitrix\Calendar\PushTable */
		static public $dataClass = '\Bitrix\Calendar\PushTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar {
	/**
	 * EO_Push_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getEntityTypeList()
	 * @method \int[] getEntityIdList()
	 * @method \string[] getChannelIdList()
	 * @method \string[] fillChannelId()
	 * @method \string[] getResourceIdList()
	 * @method \string[] fillResourceId()
	 * @method \Bitrix\Main\Type\DateTime[] getExpiresList()
	 * @method \Bitrix\Main\Type\DateTime[] fillExpires()
	 * @method \string[] getNotProcessedList()
	 * @method \string[] fillNotProcessed()
	 * @method \Bitrix\Main\Type\DateTime[] getFirstPushDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillFirstPushDate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\EO_Push $object)
	 * @method bool has(\Bitrix\Calendar\EO_Push $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\EO_Push getByPrimary($primary)
	 * @method \Bitrix\Calendar\EO_Push[] getAll()
	 * @method bool remove(\Bitrix\Calendar\EO_Push $object)
	 * @method void removeByPrimary($primary)
	 * @method array|\Bitrix\Main\ORM\Objectify\Collection|null fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\EO_Push_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\EO_Push current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method \Bitrix\Calendar\EO_Push_Collection merge(?\Bitrix\Calendar\EO_Push_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Push_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\PushTable */
		static public $dataClass = '\Bitrix\Calendar\PushTable';
	}
}
namespace Bitrix\Calendar {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Push_Result exec()
	 * @method \Bitrix\Calendar\EO_Push fetchObject()
	 * @method \Bitrix\Calendar\EO_Push_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Push_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\EO_Push fetchObject()
	 * @method \Bitrix\Calendar\EO_Push_Collection fetchCollection()
	 */
	class EO_Push_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\EO_Push createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\EO_Push_Collection createCollection()
	 * @method \Bitrix\Calendar\EO_Push wakeUpObject($row)
	 * @method \Bitrix\Calendar\EO_Push_Collection wakeUpCollection($rows)
	 */
	class EO_Push_Entity extends \Bitrix\Main\ORM\Entity {}
}