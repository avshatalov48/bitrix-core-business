<?php

/* ORMENTITYANNOTATION:Bitrix\Pull\Model\ChannelTable:pull/lib/model/channel.php:4159d0049048c0d71fa35b73f3d81d6d */
namespace Bitrix\Pull\Model {
	/**
	 * EO_Channel
	 * @see \Bitrix\Pull\Model\ChannelTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Pull\Model\EO_Channel setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Pull\Model\EO_Channel setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Pull\Model\EO_Channel resetUserId()
	 * @method \Bitrix\Pull\Model\EO_Channel unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getChannelType()
	 * @method \Bitrix\Pull\Model\EO_Channel setChannelType(\string|\Bitrix\Main\DB\SqlExpression $channelType)
	 * @method bool hasChannelType()
	 * @method bool isChannelTypeFilled()
	 * @method bool isChannelTypeChanged()
	 * @method \string remindActualChannelType()
	 * @method \string requireChannelType()
	 * @method \Bitrix\Pull\Model\EO_Channel resetChannelType()
	 * @method \Bitrix\Pull\Model\EO_Channel unsetChannelType()
	 * @method \string fillChannelType()
	 * @method \string getChannelId()
	 * @method \Bitrix\Pull\Model\EO_Channel setChannelId(\string|\Bitrix\Main\DB\SqlExpression $channelId)
	 * @method bool hasChannelId()
	 * @method bool isChannelIdFilled()
	 * @method bool isChannelIdChanged()
	 * @method \string remindActualChannelId()
	 * @method \string requireChannelId()
	 * @method \Bitrix\Pull\Model\EO_Channel resetChannelId()
	 * @method \Bitrix\Pull\Model\EO_Channel unsetChannelId()
	 * @method \string fillChannelId()
	 * @method \string getChannelPublicId()
	 * @method \Bitrix\Pull\Model\EO_Channel setChannelPublicId(\string|\Bitrix\Main\DB\SqlExpression $channelPublicId)
	 * @method bool hasChannelPublicId()
	 * @method bool isChannelPublicIdFilled()
	 * @method bool isChannelPublicIdChanged()
	 * @method \string remindActualChannelPublicId()
	 * @method \string requireChannelPublicId()
	 * @method \Bitrix\Pull\Model\EO_Channel resetChannelPublicId()
	 * @method \Bitrix\Pull\Model\EO_Channel unsetChannelPublicId()
	 * @method \string fillChannelPublicId()
	 * @method \int getLastId()
	 * @method \Bitrix\Pull\Model\EO_Channel setLastId(\int|\Bitrix\Main\DB\SqlExpression $lastId)
	 * @method bool hasLastId()
	 * @method bool isLastIdFilled()
	 * @method bool isLastIdChanged()
	 * @method \int remindActualLastId()
	 * @method \int requireLastId()
	 * @method \Bitrix\Pull\Model\EO_Channel resetLastId()
	 * @method \Bitrix\Pull\Model\EO_Channel unsetLastId()
	 * @method \int fillLastId()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Pull\Model\EO_Channel setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Pull\Model\EO_Channel resetDateCreate()
	 * @method \Bitrix\Pull\Model\EO_Channel unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Pull\Model\EO_Channel setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Pull\Model\EO_Channel resetUser()
	 * @method \Bitrix\Pull\Model\EO_Channel unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
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
	 * @method \Bitrix\Pull\Model\EO_Channel set($fieldName, $value)
	 * @method \Bitrix\Pull\Model\EO_Channel reset($fieldName)
	 * @method \Bitrix\Pull\Model\EO_Channel unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Pull\Model\EO_Channel wakeUp($data)
	 */
	class EO_Channel {
		/* @var \Bitrix\Pull\Model\ChannelTable */
		static public $dataClass = '\Bitrix\Pull\Model\ChannelTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Pull\Model {
	/**
	 * EO_Channel_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getChannelTypeList()
	 * @method \string[] fillChannelType()
	 * @method \string[] getChannelIdList()
	 * @method \string[] fillChannelId()
	 * @method \string[] getChannelPublicIdList()
	 * @method \string[] fillChannelPublicId()
	 * @method \int[] getLastIdList()
	 * @method \int[] fillLastId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Pull\Model\EO_Channel_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Pull\Model\EO_Channel $object)
	 * @method bool has(\Bitrix\Pull\Model\EO_Channel $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Pull\Model\EO_Channel getByPrimary($primary)
	 * @method \Bitrix\Pull\Model\EO_Channel[] getAll()
	 * @method bool remove(\Bitrix\Pull\Model\EO_Channel $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Pull\Model\EO_Channel_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Pull\Model\EO_Channel current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Channel_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Pull\Model\ChannelTable */
		static public $dataClass = '\Bitrix\Pull\Model\ChannelTable';
	}
}
namespace Bitrix\Pull\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Channel_Result exec()
	 * @method \Bitrix\Pull\Model\EO_Channel fetchObject()
	 * @method \Bitrix\Pull\Model\EO_Channel_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Channel_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Pull\Model\EO_Channel fetchObject()
	 * @method \Bitrix\Pull\Model\EO_Channel_Collection fetchCollection()
	 */
	class EO_Channel_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Pull\Model\EO_Channel createObject($setDefaultValues = true)
	 * @method \Bitrix\Pull\Model\EO_Channel_Collection createCollection()
	 * @method \Bitrix\Pull\Model\EO_Channel wakeUpObject($row)
	 * @method \Bitrix\Pull\Model\EO_Channel_Collection wakeUpCollection($rows)
	 */
	class EO_Channel_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Pull\Model\PushTable:pull/lib/model/push.php:53f33844b4a0f9f234fa112dd4052482 */
namespace Bitrix\Pull\Model {
	/**
	 * EO_Push
	 * @see \Bitrix\Pull\Model\PushTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Pull\Model\EO_Push setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Pull\Model\EO_Push setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Pull\Model\EO_Push resetUserId()
	 * @method \Bitrix\Pull\Model\EO_Push unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getDeviceType()
	 * @method \Bitrix\Pull\Model\EO_Push setDeviceType(\string|\Bitrix\Main\DB\SqlExpression $deviceType)
	 * @method bool hasDeviceType()
	 * @method bool isDeviceTypeFilled()
	 * @method bool isDeviceTypeChanged()
	 * @method \string remindActualDeviceType()
	 * @method \string requireDeviceType()
	 * @method \Bitrix\Pull\Model\EO_Push resetDeviceType()
	 * @method \Bitrix\Pull\Model\EO_Push unsetDeviceType()
	 * @method \string fillDeviceType()
	 * @method \string getAppId()
	 * @method \Bitrix\Pull\Model\EO_Push setAppId(\string|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \string remindActualAppId()
	 * @method \string requireAppId()
	 * @method \Bitrix\Pull\Model\EO_Push resetAppId()
	 * @method \Bitrix\Pull\Model\EO_Push unsetAppId()
	 * @method \string fillAppId()
	 * @method \string getUniqueHash()
	 * @method \Bitrix\Pull\Model\EO_Push setUniqueHash(\string|\Bitrix\Main\DB\SqlExpression $uniqueHash)
	 * @method bool hasUniqueHash()
	 * @method bool isUniqueHashFilled()
	 * @method bool isUniqueHashChanged()
	 * @method \string remindActualUniqueHash()
	 * @method \string requireUniqueHash()
	 * @method \Bitrix\Pull\Model\EO_Push resetUniqueHash()
	 * @method \Bitrix\Pull\Model\EO_Push unsetUniqueHash()
	 * @method \string fillUniqueHash()
	 * @method \string getDeviceId()
	 * @method \Bitrix\Pull\Model\EO_Push setDeviceId(\string|\Bitrix\Main\DB\SqlExpression $deviceId)
	 * @method bool hasDeviceId()
	 * @method bool isDeviceIdFilled()
	 * @method bool isDeviceIdChanged()
	 * @method \string remindActualDeviceId()
	 * @method \string requireDeviceId()
	 * @method \Bitrix\Pull\Model\EO_Push resetDeviceId()
	 * @method \Bitrix\Pull\Model\EO_Push unsetDeviceId()
	 * @method \string fillDeviceId()
	 * @method \string getDeviceName()
	 * @method \Bitrix\Pull\Model\EO_Push setDeviceName(\string|\Bitrix\Main\DB\SqlExpression $deviceName)
	 * @method bool hasDeviceName()
	 * @method bool isDeviceNameFilled()
	 * @method bool isDeviceNameChanged()
	 * @method \string remindActualDeviceName()
	 * @method \string requireDeviceName()
	 * @method \Bitrix\Pull\Model\EO_Push resetDeviceName()
	 * @method \Bitrix\Pull\Model\EO_Push unsetDeviceName()
	 * @method \string fillDeviceName()
	 * @method \string getDeviceToken()
	 * @method \Bitrix\Pull\Model\EO_Push setDeviceToken(\string|\Bitrix\Main\DB\SqlExpression $deviceToken)
	 * @method bool hasDeviceToken()
	 * @method bool isDeviceTokenFilled()
	 * @method bool isDeviceTokenChanged()
	 * @method \string remindActualDeviceToken()
	 * @method \string requireDeviceToken()
	 * @method \Bitrix\Pull\Model\EO_Push resetDeviceToken()
	 * @method \Bitrix\Pull\Model\EO_Push unsetDeviceToken()
	 * @method \string fillDeviceToken()
	 * @method \string getVoipType()
	 * @method \Bitrix\Pull\Model\EO_Push setVoipType(\string|\Bitrix\Main\DB\SqlExpression $voipType)
	 * @method bool hasVoipType()
	 * @method bool isVoipTypeFilled()
	 * @method bool isVoipTypeChanged()
	 * @method \string remindActualVoipType()
	 * @method \string requireVoipType()
	 * @method \Bitrix\Pull\Model\EO_Push resetVoipType()
	 * @method \Bitrix\Pull\Model\EO_Push unsetVoipType()
	 * @method \string fillVoipType()
	 * @method \string getVoipToken()
	 * @method \Bitrix\Pull\Model\EO_Push setVoipToken(\string|\Bitrix\Main\DB\SqlExpression $voipToken)
	 * @method bool hasVoipToken()
	 * @method bool isVoipTokenFilled()
	 * @method bool isVoipTokenChanged()
	 * @method \string remindActualVoipToken()
	 * @method \string requireVoipToken()
	 * @method \Bitrix\Pull\Model\EO_Push resetVoipToken()
	 * @method \Bitrix\Pull\Model\EO_Push unsetVoipToken()
	 * @method \string fillVoipToken()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Pull\Model\EO_Push setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Pull\Model\EO_Push resetDateCreate()
	 * @method \Bitrix\Pull\Model\EO_Push unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateAuth()
	 * @method \Bitrix\Pull\Model\EO_Push setDateAuth(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateAuth)
	 * @method bool hasDateAuth()
	 * @method bool isDateAuthFilled()
	 * @method bool isDateAuthChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateAuth()
	 * @method \Bitrix\Main\Type\DateTime requireDateAuth()
	 * @method \Bitrix\Pull\Model\EO_Push resetDateAuth()
	 * @method \Bitrix\Pull\Model\EO_Push unsetDateAuth()
	 * @method \Bitrix\Main\Type\DateTime fillDateAuth()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Pull\Model\EO_Push setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Pull\Model\EO_Push resetUser()
	 * @method \Bitrix\Pull\Model\EO_Push unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
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
	 * @method \Bitrix\Pull\Model\EO_Push set($fieldName, $value)
	 * @method \Bitrix\Pull\Model\EO_Push reset($fieldName)
	 * @method \Bitrix\Pull\Model\EO_Push unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Pull\Model\EO_Push wakeUp($data)
	 */
	class EO_Push {
		/* @var \Bitrix\Pull\Model\PushTable */
		static public $dataClass = '\Bitrix\Pull\Model\PushTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Pull\Model {
	/**
	 * EO_Push_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getDeviceTypeList()
	 * @method \string[] fillDeviceType()
	 * @method \string[] getAppIdList()
	 * @method \string[] fillAppId()
	 * @method \string[] getUniqueHashList()
	 * @method \string[] fillUniqueHash()
	 * @method \string[] getDeviceIdList()
	 * @method \string[] fillDeviceId()
	 * @method \string[] getDeviceNameList()
	 * @method \string[] fillDeviceName()
	 * @method \string[] getDeviceTokenList()
	 * @method \string[] fillDeviceToken()
	 * @method \string[] getVoipTypeList()
	 * @method \string[] fillVoipType()
	 * @method \string[] getVoipTokenList()
	 * @method \string[] fillVoipToken()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateAuthList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateAuth()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Pull\Model\EO_Push_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Pull\Model\EO_Push $object)
	 * @method bool has(\Bitrix\Pull\Model\EO_Push $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Pull\Model\EO_Push getByPrimary($primary)
	 * @method \Bitrix\Pull\Model\EO_Push[] getAll()
	 * @method bool remove(\Bitrix\Pull\Model\EO_Push $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Pull\Model\EO_Push_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Pull\Model\EO_Push current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Push_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Pull\Model\PushTable */
		static public $dataClass = '\Bitrix\Pull\Model\PushTable';
	}
}
namespace Bitrix\Pull\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Push_Result exec()
	 * @method \Bitrix\Pull\Model\EO_Push fetchObject()
	 * @method \Bitrix\Pull\Model\EO_Push_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Push_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Pull\Model\EO_Push fetchObject()
	 * @method \Bitrix\Pull\Model\EO_Push_Collection fetchCollection()
	 */
	class EO_Push_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Pull\Model\EO_Push createObject($setDefaultValues = true)
	 * @method \Bitrix\Pull\Model\EO_Push_Collection createCollection()
	 * @method \Bitrix\Pull\Model\EO_Push wakeUpObject($row)
	 * @method \Bitrix\Pull\Model\EO_Push_Collection wakeUpCollection($rows)
	 */
	class EO_Push_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Pull\Model\WatchTable:pull/lib/model/watchtable.php:39659c88e8b4f56de352feb79b7ab9f1 */
namespace Bitrix\Pull\Model {
	/**
	 * EO_Watch
	 * @see \Bitrix\Pull\Model\WatchTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Pull\Model\EO_Watch setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Pull\Model\EO_Watch setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Pull\Model\EO_Watch resetUserId()
	 * @method \Bitrix\Pull\Model\EO_Watch unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getChannelId()
	 * @method \Bitrix\Pull\Model\EO_Watch setChannelId(\string|\Bitrix\Main\DB\SqlExpression $channelId)
	 * @method bool hasChannelId()
	 * @method bool isChannelIdFilled()
	 * @method bool isChannelIdChanged()
	 * @method \string remindActualChannelId()
	 * @method \string requireChannelId()
	 * @method \Bitrix\Pull\Model\EO_Watch resetChannelId()
	 * @method \Bitrix\Pull\Model\EO_Watch unsetChannelId()
	 * @method \string fillChannelId()
	 * @method \string getTag()
	 * @method \Bitrix\Pull\Model\EO_Watch setTag(\string|\Bitrix\Main\DB\SqlExpression $tag)
	 * @method bool hasTag()
	 * @method bool isTagFilled()
	 * @method bool isTagChanged()
	 * @method \string remindActualTag()
	 * @method \string requireTag()
	 * @method \Bitrix\Pull\Model\EO_Watch resetTag()
	 * @method \Bitrix\Pull\Model\EO_Watch unsetTag()
	 * @method \string fillTag()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Pull\Model\EO_Watch setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Pull\Model\EO_Watch resetDateCreate()
	 * @method \Bitrix\Pull\Model\EO_Watch unsetDateCreate()
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
	 * @method \Bitrix\Pull\Model\EO_Watch set($fieldName, $value)
	 * @method \Bitrix\Pull\Model\EO_Watch reset($fieldName)
	 * @method \Bitrix\Pull\Model\EO_Watch unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Pull\Model\EO_Watch wakeUp($data)
	 */
	class EO_Watch {
		/* @var \Bitrix\Pull\Model\WatchTable */
		static public $dataClass = '\Bitrix\Pull\Model\WatchTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Pull\Model {
	/**
	 * EO_Watch_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getChannelIdList()
	 * @method \string[] fillChannelId()
	 * @method \string[] getTagList()
	 * @method \string[] fillTag()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Pull\Model\EO_Watch $object)
	 * @method bool has(\Bitrix\Pull\Model\EO_Watch $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Pull\Model\EO_Watch getByPrimary($primary)
	 * @method \Bitrix\Pull\Model\EO_Watch[] getAll()
	 * @method bool remove(\Bitrix\Pull\Model\EO_Watch $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Pull\Model\EO_Watch_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Pull\Model\EO_Watch current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Watch_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Pull\Model\WatchTable */
		static public $dataClass = '\Bitrix\Pull\Model\WatchTable';
	}
}
namespace Bitrix\Pull\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Watch_Result exec()
	 * @method \Bitrix\Pull\Model\EO_Watch fetchObject()
	 * @method \Bitrix\Pull\Model\EO_Watch_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Watch_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Pull\Model\EO_Watch fetchObject()
	 * @method \Bitrix\Pull\Model\EO_Watch_Collection fetchCollection()
	 */
	class EO_Watch_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Pull\Model\EO_Watch createObject($setDefaultValues = true)
	 * @method \Bitrix\Pull\Model\EO_Watch_Collection createCollection()
	 * @method \Bitrix\Pull\Model\EO_Watch wakeUpObject($row)
	 * @method \Bitrix\Pull\Model\EO_Watch_Collection wakeUpCollection($rows)
	 */
	class EO_Watch_Entity extends \Bitrix\Main\ORM\Entity {}
}