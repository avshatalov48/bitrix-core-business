<?php

/* ORMENTITYANNOTATION:Bitrix\Socialservices\ApTable:socialservices/lib/ap.php:ae9f6f6e789a03e4ee7a62d5a8271240 */
namespace Bitrix\Socialservices {
	/**
	 * EO_Ap
	 * @see \Bitrix\Socialservices\ApTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialservices\EO_Ap setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Socialservices\EO_Ap setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Socialservices\EO_Ap resetTimestampX()
	 * @method \Bitrix\Socialservices\EO_Ap unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialservices\EO_Ap setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialservices\EO_Ap resetUserId()
	 * @method \Bitrix\Socialservices\EO_Ap unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getDomain()
	 * @method \Bitrix\Socialservices\EO_Ap setDomain(\string|\Bitrix\Main\DB\SqlExpression $domain)
	 * @method bool hasDomain()
	 * @method bool isDomainFilled()
	 * @method bool isDomainChanged()
	 * @method \string remindActualDomain()
	 * @method \string requireDomain()
	 * @method \Bitrix\Socialservices\EO_Ap resetDomain()
	 * @method \Bitrix\Socialservices\EO_Ap unsetDomain()
	 * @method \string fillDomain()
	 * @method \string getEndpoint()
	 * @method \Bitrix\Socialservices\EO_Ap setEndpoint(\string|\Bitrix\Main\DB\SqlExpression $endpoint)
	 * @method bool hasEndpoint()
	 * @method bool isEndpointFilled()
	 * @method bool isEndpointChanged()
	 * @method \string remindActualEndpoint()
	 * @method \string requireEndpoint()
	 * @method \Bitrix\Socialservices\EO_Ap resetEndpoint()
	 * @method \Bitrix\Socialservices\EO_Ap unsetEndpoint()
	 * @method \string fillEndpoint()
	 * @method \string getLogin()
	 * @method \Bitrix\Socialservices\EO_Ap setLogin(\string|\Bitrix\Main\DB\SqlExpression $login)
	 * @method bool hasLogin()
	 * @method bool isLoginFilled()
	 * @method bool isLoginChanged()
	 * @method \string remindActualLogin()
	 * @method \string requireLogin()
	 * @method \Bitrix\Socialservices\EO_Ap resetLogin()
	 * @method \Bitrix\Socialservices\EO_Ap unsetLogin()
	 * @method \string fillLogin()
	 * @method \string getPassword()
	 * @method \Bitrix\Socialservices\EO_Ap setPassword(\string|\Bitrix\Main\DB\SqlExpression $password)
	 * @method bool hasPassword()
	 * @method bool isPasswordFilled()
	 * @method bool isPasswordChanged()
	 * @method \string remindActualPassword()
	 * @method \string requirePassword()
	 * @method \Bitrix\Socialservices\EO_Ap resetPassword()
	 * @method \Bitrix\Socialservices\EO_Ap unsetPassword()
	 * @method \string fillPassword()
	 * @method \Bitrix\Main\Type\DateTime getLastAuthorize()
	 * @method \Bitrix\Socialservices\EO_Ap setLastAuthorize(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastAuthorize)
	 * @method bool hasLastAuthorize()
	 * @method bool isLastAuthorizeFilled()
	 * @method bool isLastAuthorizeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastAuthorize()
	 * @method \Bitrix\Main\Type\DateTime requireLastAuthorize()
	 * @method \Bitrix\Socialservices\EO_Ap resetLastAuthorize()
	 * @method \Bitrix\Socialservices\EO_Ap unsetLastAuthorize()
	 * @method \Bitrix\Main\Type\DateTime fillLastAuthorize()
	 * @method \string getSettings()
	 * @method \Bitrix\Socialservices\EO_Ap setSettings(\string|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method \string remindActualSettings()
	 * @method \string requireSettings()
	 * @method \Bitrix\Socialservices\EO_Ap resetSettings()
	 * @method \Bitrix\Socialservices\EO_Ap unsetSettings()
	 * @method \string fillSettings()
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
	 * @method \Bitrix\Socialservices\EO_Ap set($fieldName, $value)
	 * @method \Bitrix\Socialservices\EO_Ap reset($fieldName)
	 * @method \Bitrix\Socialservices\EO_Ap unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialservices\EO_Ap wakeUp($data)
	 */
	class EO_Ap {
		/* @var \Bitrix\Socialservices\ApTable */
		static public $dataClass = '\Bitrix\Socialservices\ApTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialservices {
	/**
	 * EO_Ap_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getDomainList()
	 * @method \string[] fillDomain()
	 * @method \string[] getEndpointList()
	 * @method \string[] fillEndpoint()
	 * @method \string[] getLoginList()
	 * @method \string[] fillLogin()
	 * @method \string[] getPasswordList()
	 * @method \string[] fillPassword()
	 * @method \Bitrix\Main\Type\DateTime[] getLastAuthorizeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastAuthorize()
	 * @method \string[] getSettingsList()
	 * @method \string[] fillSettings()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialservices\EO_Ap $object)
	 * @method bool has(\Bitrix\Socialservices\EO_Ap $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_Ap getByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_Ap[] getAll()
	 * @method bool remove(\Bitrix\Socialservices\EO_Ap $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialservices\EO_Ap_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialservices\EO_Ap current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Ap_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialservices\ApTable */
		static public $dataClass = '\Bitrix\Socialservices\ApTable';
	}
}
namespace Bitrix\Socialservices {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Ap_Result exec()
	 * @method \Bitrix\Socialservices\EO_Ap fetchObject()
	 * @method \Bitrix\Socialservices\EO_Ap_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Ap_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialservices\EO_Ap fetchObject()
	 * @method \Bitrix\Socialservices\EO_Ap_Collection fetchCollection()
	 */
	class EO_Ap_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialservices\EO_Ap createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialservices\EO_Ap_Collection createCollection()
	 * @method \Bitrix\Socialservices\EO_Ap wakeUpObject($row)
	 * @method \Bitrix\Socialservices\EO_Ap_Collection wakeUpCollection($rows)
	 */
	class EO_Ap_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialservices\ContactTable:socialservices/lib/contact.php:1d696dde07bea0164d2c95866a28ab93 */
namespace Bitrix\Socialservices {
	/**
	 * EO_Contact
	 * @see \Bitrix\Socialservices\ContactTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialservices\EO_Contact setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Socialservices\EO_Contact setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Socialservices\EO_Contact resetTimestampX()
	 * @method \Bitrix\Socialservices\EO_Contact unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialservices\EO_Contact setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialservices\EO_Contact resetUserId()
	 * @method \Bitrix\Socialservices\EO_Contact unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getContactUserId()
	 * @method \Bitrix\Socialservices\EO_Contact setContactUserId(\int|\Bitrix\Main\DB\SqlExpression $contactUserId)
	 * @method bool hasContactUserId()
	 * @method bool isContactUserIdFilled()
	 * @method bool isContactUserIdChanged()
	 * @method \int remindActualContactUserId()
	 * @method \int requireContactUserId()
	 * @method \Bitrix\Socialservices\EO_Contact resetContactUserId()
	 * @method \Bitrix\Socialservices\EO_Contact unsetContactUserId()
	 * @method \int fillContactUserId()
	 * @method \int getContactXmlId()
	 * @method \Bitrix\Socialservices\EO_Contact setContactXmlId(\int|\Bitrix\Main\DB\SqlExpression $contactXmlId)
	 * @method bool hasContactXmlId()
	 * @method bool isContactXmlIdFilled()
	 * @method bool isContactXmlIdChanged()
	 * @method \int remindActualContactXmlId()
	 * @method \int requireContactXmlId()
	 * @method \Bitrix\Socialservices\EO_Contact resetContactXmlId()
	 * @method \Bitrix\Socialservices\EO_Contact unsetContactXmlId()
	 * @method \int fillContactXmlId()
	 * @method \string getContactName()
	 * @method \Bitrix\Socialservices\EO_Contact setContactName(\string|\Bitrix\Main\DB\SqlExpression $contactName)
	 * @method bool hasContactName()
	 * @method bool isContactNameFilled()
	 * @method bool isContactNameChanged()
	 * @method \string remindActualContactName()
	 * @method \string requireContactName()
	 * @method \Bitrix\Socialservices\EO_Contact resetContactName()
	 * @method \Bitrix\Socialservices\EO_Contact unsetContactName()
	 * @method \string fillContactName()
	 * @method \string getContactLastName()
	 * @method \Bitrix\Socialservices\EO_Contact setContactLastName(\string|\Bitrix\Main\DB\SqlExpression $contactLastName)
	 * @method bool hasContactLastName()
	 * @method bool isContactLastNameFilled()
	 * @method bool isContactLastNameChanged()
	 * @method \string remindActualContactLastName()
	 * @method \string requireContactLastName()
	 * @method \Bitrix\Socialservices\EO_Contact resetContactLastName()
	 * @method \Bitrix\Socialservices\EO_Contact unsetContactLastName()
	 * @method \string fillContactLastName()
	 * @method \string getContactPhoto()
	 * @method \Bitrix\Socialservices\EO_Contact setContactPhoto(\string|\Bitrix\Main\DB\SqlExpression $contactPhoto)
	 * @method bool hasContactPhoto()
	 * @method bool isContactPhotoFilled()
	 * @method bool isContactPhotoChanged()
	 * @method \string remindActualContactPhoto()
	 * @method \string requireContactPhoto()
	 * @method \Bitrix\Socialservices\EO_Contact resetContactPhoto()
	 * @method \Bitrix\Socialservices\EO_Contact unsetContactPhoto()
	 * @method \string fillContactPhoto()
	 * @method \Bitrix\Main\Type\DateTime getLastAuthorize()
	 * @method \Bitrix\Socialservices\EO_Contact setLastAuthorize(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastAuthorize)
	 * @method bool hasLastAuthorize()
	 * @method bool isLastAuthorizeFilled()
	 * @method bool isLastAuthorizeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastAuthorize()
	 * @method \Bitrix\Main\Type\DateTime requireLastAuthorize()
	 * @method \Bitrix\Socialservices\EO_Contact resetLastAuthorize()
	 * @method \Bitrix\Socialservices\EO_Contact unsetLastAuthorize()
	 * @method \Bitrix\Main\Type\DateTime fillLastAuthorize()
	 * @method \boolean getNotify()
	 * @method \Bitrix\Socialservices\EO_Contact setNotify(\boolean|\Bitrix\Main\DB\SqlExpression $notify)
	 * @method bool hasNotify()
	 * @method bool isNotifyFilled()
	 * @method bool isNotifyChanged()
	 * @method \boolean remindActualNotify()
	 * @method \boolean requireNotify()
	 * @method \Bitrix\Socialservices\EO_Contact resetNotify()
	 * @method \Bitrix\Socialservices\EO_Contact unsetNotify()
	 * @method \boolean fillNotify()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialservices\EO_Contact setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialservices\EO_Contact resetUser()
	 * @method \Bitrix\Socialservices\EO_Contact unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Main\EO_User getContactUser()
	 * @method \Bitrix\Main\EO_User remindActualContactUser()
	 * @method \Bitrix\Main\EO_User requireContactUser()
	 * @method \Bitrix\Socialservices\EO_Contact setContactUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialservices\EO_Contact resetContactUser()
	 * @method \Bitrix\Socialservices\EO_Contact unsetContactUser()
	 * @method bool hasContactUser()
	 * @method bool isContactUserFilled()
	 * @method bool isContactUserChanged()
	 * @method \Bitrix\Main\EO_User fillContactUser()
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
	 * @method \Bitrix\Socialservices\EO_Contact set($fieldName, $value)
	 * @method \Bitrix\Socialservices\EO_Contact reset($fieldName)
	 * @method \Bitrix\Socialservices\EO_Contact unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialservices\EO_Contact wakeUp($data)
	 */
	class EO_Contact {
		/* @var \Bitrix\Socialservices\ContactTable */
		static public $dataClass = '\Bitrix\Socialservices\ContactTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialservices {
	/**
	 * EO_Contact_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getContactUserIdList()
	 * @method \int[] fillContactUserId()
	 * @method \int[] getContactXmlIdList()
	 * @method \int[] fillContactXmlId()
	 * @method \string[] getContactNameList()
	 * @method \string[] fillContactName()
	 * @method \string[] getContactLastNameList()
	 * @method \string[] fillContactLastName()
	 * @method \string[] getContactPhotoList()
	 * @method \string[] fillContactPhoto()
	 * @method \Bitrix\Main\Type\DateTime[] getLastAuthorizeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastAuthorize()
	 * @method \boolean[] getNotifyList()
	 * @method \boolean[] fillNotify()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialservices\EO_Contact_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Main\EO_User[] getContactUserList()
	 * @method \Bitrix\Socialservices\EO_Contact_Collection getContactUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillContactUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialservices\EO_Contact $object)
	 * @method bool has(\Bitrix\Socialservices\EO_Contact $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_Contact getByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_Contact[] getAll()
	 * @method bool remove(\Bitrix\Socialservices\EO_Contact $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialservices\EO_Contact_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialservices\EO_Contact current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Contact_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialservices\ContactTable */
		static public $dataClass = '\Bitrix\Socialservices\ContactTable';
	}
}
namespace Bitrix\Socialservices {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Contact_Result exec()
	 * @method \Bitrix\Socialservices\EO_Contact fetchObject()
	 * @method \Bitrix\Socialservices\EO_Contact_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Contact_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialservices\EO_Contact fetchObject()
	 * @method \Bitrix\Socialservices\EO_Contact_Collection fetchCollection()
	 */
	class EO_Contact_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialservices\EO_Contact createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialservices\EO_Contact_Collection createCollection()
	 * @method \Bitrix\Socialservices\EO_Contact wakeUpObject($row)
	 * @method \Bitrix\Socialservices\EO_Contact_Collection wakeUpCollection($rows)
	 */
	class EO_Contact_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialservices\ContactConnectTable:socialservices/lib/contactconnect.php:b96123e7a76afb768053838eb0c7cb4b */
namespace Bitrix\Socialservices {
	/**
	 * EO_ContactConnect
	 * @see \Bitrix\Socialservices\ContactConnectTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Socialservices\EO_ContactConnect resetTimestampX()
	 * @method \Bitrix\Socialservices\EO_ContactConnect unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getContactId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setContactId(\int|\Bitrix\Main\DB\SqlExpression $contactId)
	 * @method bool hasContactId()
	 * @method bool isContactIdFilled()
	 * @method bool isContactIdChanged()
	 * @method \int remindActualContactId()
	 * @method \int requireContactId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect resetContactId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect unsetContactId()
	 * @method \int fillContactId()
	 * @method \int getLinkId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setLinkId(\int|\Bitrix\Main\DB\SqlExpression $linkId)
	 * @method bool hasLinkId()
	 * @method bool isLinkIdFilled()
	 * @method bool isLinkIdChanged()
	 * @method \int remindActualLinkId()
	 * @method \int requireLinkId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect resetLinkId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect unsetLinkId()
	 * @method \int fillLinkId()
	 * @method \int getContactProfileId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setContactProfileId(\int|\Bitrix\Main\DB\SqlExpression $contactProfileId)
	 * @method bool hasContactProfileId()
	 * @method bool isContactProfileIdFilled()
	 * @method bool isContactProfileIdChanged()
	 * @method \int remindActualContactProfileId()
	 * @method \int requireContactProfileId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect resetContactProfileId()
	 * @method \Bitrix\Socialservices\EO_ContactConnect unsetContactProfileId()
	 * @method \int fillContactProfileId()
	 * @method \string getContactPortal()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setContactPortal(\string|\Bitrix\Main\DB\SqlExpression $contactPortal)
	 * @method bool hasContactPortal()
	 * @method bool isContactPortalFilled()
	 * @method bool isContactPortalChanged()
	 * @method \string remindActualContactPortal()
	 * @method \string requireContactPortal()
	 * @method \Bitrix\Socialservices\EO_ContactConnect resetContactPortal()
	 * @method \Bitrix\Socialservices\EO_ContactConnect unsetContactPortal()
	 * @method \string fillContactPortal()
	 * @method \Bitrix\Main\Type\DateTime getLastAuthorize()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setLastAuthorize(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastAuthorize)
	 * @method bool hasLastAuthorize()
	 * @method bool isLastAuthorizeFilled()
	 * @method bool isLastAuthorizeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastAuthorize()
	 * @method \Bitrix\Main\Type\DateTime requireLastAuthorize()
	 * @method \Bitrix\Socialservices\EO_ContactConnect resetLastAuthorize()
	 * @method \Bitrix\Socialservices\EO_ContactConnect unsetLastAuthorize()
	 * @method \Bitrix\Main\Type\DateTime fillLastAuthorize()
	 * @method \string getConnectType()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setConnectType(\string|\Bitrix\Main\DB\SqlExpression $connectType)
	 * @method bool hasConnectType()
	 * @method bool isConnectTypeFilled()
	 * @method bool isConnectTypeChanged()
	 * @method \string remindActualConnectType()
	 * @method \string requireConnectType()
	 * @method \Bitrix\Socialservices\EO_ContactConnect resetConnectType()
	 * @method \Bitrix\Socialservices\EO_ContactConnect unsetConnectType()
	 * @method \string fillConnectType()
	 * @method \Bitrix\Socialservices\EO_Contact getContact()
	 * @method \Bitrix\Socialservices\EO_Contact remindActualContact()
	 * @method \Bitrix\Socialservices\EO_Contact requireContact()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setContact(\Bitrix\Socialservices\EO_Contact $object)
	 * @method \Bitrix\Socialservices\EO_ContactConnect resetContact()
	 * @method \Bitrix\Socialservices\EO_ContactConnect unsetContact()
	 * @method bool hasContact()
	 * @method bool isContactFilled()
	 * @method bool isContactChanged()
	 * @method \Bitrix\Socialservices\EO_Contact fillContact()
	 * @method \Bitrix\Socialservices\EO_UserLink getLink()
	 * @method \Bitrix\Socialservices\EO_UserLink remindActualLink()
	 * @method \Bitrix\Socialservices\EO_UserLink requireLink()
	 * @method \Bitrix\Socialservices\EO_ContactConnect setLink(\Bitrix\Socialservices\EO_UserLink $object)
	 * @method \Bitrix\Socialservices\EO_ContactConnect resetLink()
	 * @method \Bitrix\Socialservices\EO_ContactConnect unsetLink()
	 * @method bool hasLink()
	 * @method bool isLinkFilled()
	 * @method bool isLinkChanged()
	 * @method \Bitrix\Socialservices\EO_UserLink fillLink()
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
	 * @method \Bitrix\Socialservices\EO_ContactConnect set($fieldName, $value)
	 * @method \Bitrix\Socialservices\EO_ContactConnect reset($fieldName)
	 * @method \Bitrix\Socialservices\EO_ContactConnect unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialservices\EO_ContactConnect wakeUp($data)
	 */
	class EO_ContactConnect {
		/* @var \Bitrix\Socialservices\ContactConnectTable */
		static public $dataClass = '\Bitrix\Socialservices\ContactConnectTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialservices {
	/**
	 * EO_ContactConnect_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getContactIdList()
	 * @method \int[] fillContactId()
	 * @method \int[] getLinkIdList()
	 * @method \int[] fillLinkId()
	 * @method \int[] getContactProfileIdList()
	 * @method \int[] fillContactProfileId()
	 * @method \string[] getContactPortalList()
	 * @method \string[] fillContactPortal()
	 * @method \Bitrix\Main\Type\DateTime[] getLastAuthorizeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastAuthorize()
	 * @method \string[] getConnectTypeList()
	 * @method \string[] fillConnectType()
	 * @method \Bitrix\Socialservices\EO_Contact[] getContactList()
	 * @method \Bitrix\Socialservices\EO_ContactConnect_Collection getContactCollection()
	 * @method \Bitrix\Socialservices\EO_Contact_Collection fillContact()
	 * @method \Bitrix\Socialservices\EO_UserLink[] getLinkList()
	 * @method \Bitrix\Socialservices\EO_ContactConnect_Collection getLinkCollection()
	 * @method \Bitrix\Socialservices\EO_UserLink_Collection fillLink()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialservices\EO_ContactConnect $object)
	 * @method bool has(\Bitrix\Socialservices\EO_ContactConnect $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_ContactConnect getByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_ContactConnect[] getAll()
	 * @method bool remove(\Bitrix\Socialservices\EO_ContactConnect $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialservices\EO_ContactConnect_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialservices\EO_ContactConnect current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ContactConnect_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialservices\ContactConnectTable */
		static public $dataClass = '\Bitrix\Socialservices\ContactConnectTable';
	}
}
namespace Bitrix\Socialservices {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ContactConnect_Result exec()
	 * @method \Bitrix\Socialservices\EO_ContactConnect fetchObject()
	 * @method \Bitrix\Socialservices\EO_ContactConnect_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ContactConnect_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialservices\EO_ContactConnect fetchObject()
	 * @method \Bitrix\Socialservices\EO_ContactConnect_Collection fetchCollection()
	 */
	class EO_ContactConnect_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialservices\EO_ContactConnect createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialservices\EO_ContactConnect_Collection createCollection()
	 * @method \Bitrix\Socialservices\EO_ContactConnect wakeUpObject($row)
	 * @method \Bitrix\Socialservices\EO_ContactConnect_Collection wakeUpCollection($rows)
	 */
	class EO_ContactConnect_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialservices\UserTable:socialservices/lib/user.php:fa217c59100be3cdcf9b175fd1dc9d5a */
namespace Bitrix\Socialservices {
	/**
	 * EO_User
	 * @see \Bitrix\Socialservices\UserTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialservices\EO_User setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getLogin()
	 * @method \Bitrix\Socialservices\EO_User setLogin(\string|\Bitrix\Main\DB\SqlExpression $login)
	 * @method bool hasLogin()
	 * @method bool isLoginFilled()
	 * @method bool isLoginChanged()
	 * @method \string remindActualLogin()
	 * @method \string requireLogin()
	 * @method \Bitrix\Socialservices\EO_User resetLogin()
	 * @method \Bitrix\Socialservices\EO_User unsetLogin()
	 * @method \string fillLogin()
	 * @method \string getName()
	 * @method \Bitrix\Socialservices\EO_User setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Socialservices\EO_User resetName()
	 * @method \Bitrix\Socialservices\EO_User unsetName()
	 * @method \string fillName()
	 * @method \string getLastName()
	 * @method \Bitrix\Socialservices\EO_User setLastName(\string|\Bitrix\Main\DB\SqlExpression $lastName)
	 * @method bool hasLastName()
	 * @method bool isLastNameFilled()
	 * @method bool isLastNameChanged()
	 * @method \string remindActualLastName()
	 * @method \string requireLastName()
	 * @method \Bitrix\Socialservices\EO_User resetLastName()
	 * @method \Bitrix\Socialservices\EO_User unsetLastName()
	 * @method \string fillLastName()
	 * @method \string getEmail()
	 * @method \Bitrix\Socialservices\EO_User setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Socialservices\EO_User resetEmail()
	 * @method \Bitrix\Socialservices\EO_User unsetEmail()
	 * @method \string fillEmail()
	 * @method \string getPersonalPhoto()
	 * @method \Bitrix\Socialservices\EO_User setPersonalPhoto(\string|\Bitrix\Main\DB\SqlExpression $personalPhoto)
	 * @method bool hasPersonalPhoto()
	 * @method bool isPersonalPhotoFilled()
	 * @method bool isPersonalPhotoChanged()
	 * @method \string remindActualPersonalPhoto()
	 * @method \string requirePersonalPhoto()
	 * @method \Bitrix\Socialservices\EO_User resetPersonalPhoto()
	 * @method \Bitrix\Socialservices\EO_User unsetPersonalPhoto()
	 * @method \string fillPersonalPhoto()
	 * @method \string getExternalAuthId()
	 * @method \Bitrix\Socialservices\EO_User setExternalAuthId(\string|\Bitrix\Main\DB\SqlExpression $externalAuthId)
	 * @method bool hasExternalAuthId()
	 * @method bool isExternalAuthIdFilled()
	 * @method bool isExternalAuthIdChanged()
	 * @method \string remindActualExternalAuthId()
	 * @method \string requireExternalAuthId()
	 * @method \Bitrix\Socialservices\EO_User resetExternalAuthId()
	 * @method \Bitrix\Socialservices\EO_User unsetExternalAuthId()
	 * @method \string fillExternalAuthId()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialservices\EO_User setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialservices\EO_User resetUserId()
	 * @method \Bitrix\Socialservices\EO_User unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getXmlId()
	 * @method \Bitrix\Socialservices\EO_User setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Socialservices\EO_User resetXmlId()
	 * @method \Bitrix\Socialservices\EO_User unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \boolean getCanDelete()
	 * @method \Bitrix\Socialservices\EO_User setCanDelete(\boolean|\Bitrix\Main\DB\SqlExpression $canDelete)
	 * @method bool hasCanDelete()
	 * @method bool isCanDeleteFilled()
	 * @method bool isCanDeleteChanged()
	 * @method \boolean remindActualCanDelete()
	 * @method \boolean requireCanDelete()
	 * @method \Bitrix\Socialservices\EO_User resetCanDelete()
	 * @method \Bitrix\Socialservices\EO_User unsetCanDelete()
	 * @method \boolean fillCanDelete()
	 * @method \string getPersonalWww()
	 * @method \Bitrix\Socialservices\EO_User setPersonalWww(\string|\Bitrix\Main\DB\SqlExpression $personalWww)
	 * @method bool hasPersonalWww()
	 * @method bool isPersonalWwwFilled()
	 * @method bool isPersonalWwwChanged()
	 * @method \string remindActualPersonalWww()
	 * @method \string requirePersonalWww()
	 * @method \Bitrix\Socialservices\EO_User resetPersonalWww()
	 * @method \Bitrix\Socialservices\EO_User unsetPersonalWww()
	 * @method \string fillPersonalWww()
	 * @method \string getPermissions()
	 * @method \Bitrix\Socialservices\EO_User setPermissions(\string|\Bitrix\Main\DB\SqlExpression $permissions)
	 * @method bool hasPermissions()
	 * @method bool isPermissionsFilled()
	 * @method bool isPermissionsChanged()
	 * @method \string remindActualPermissions()
	 * @method \string requirePermissions()
	 * @method \Bitrix\Socialservices\EO_User resetPermissions()
	 * @method \Bitrix\Socialservices\EO_User unsetPermissions()
	 * @method \string fillPermissions()
	 * @method \string getOatoken()
	 * @method \Bitrix\Socialservices\EO_User setOatoken(\string|\Bitrix\Main\DB\SqlExpression $oatoken)
	 * @method bool hasOatoken()
	 * @method bool isOatokenFilled()
	 * @method bool isOatokenChanged()
	 * @method \string remindActualOatoken()
	 * @method \string requireOatoken()
	 * @method \Bitrix\Socialservices\EO_User resetOatoken()
	 * @method \Bitrix\Socialservices\EO_User unsetOatoken()
	 * @method \string fillOatoken()
	 * @method \int getOatokenExpires()
	 * @method \Bitrix\Socialservices\EO_User setOatokenExpires(\int|\Bitrix\Main\DB\SqlExpression $oatokenExpires)
	 * @method bool hasOatokenExpires()
	 * @method bool isOatokenExpiresFilled()
	 * @method bool isOatokenExpiresChanged()
	 * @method \int remindActualOatokenExpires()
	 * @method \int requireOatokenExpires()
	 * @method \Bitrix\Socialservices\EO_User resetOatokenExpires()
	 * @method \Bitrix\Socialservices\EO_User unsetOatokenExpires()
	 * @method \int fillOatokenExpires()
	 * @method \string getOasecret()
	 * @method \Bitrix\Socialservices\EO_User setOasecret(\string|\Bitrix\Main\DB\SqlExpression $oasecret)
	 * @method bool hasOasecret()
	 * @method bool isOasecretFilled()
	 * @method bool isOasecretChanged()
	 * @method \string remindActualOasecret()
	 * @method \string requireOasecret()
	 * @method \Bitrix\Socialservices\EO_User resetOasecret()
	 * @method \Bitrix\Socialservices\EO_User unsetOasecret()
	 * @method \string fillOasecret()
	 * @method \string getRefreshToken()
	 * @method \Bitrix\Socialservices\EO_User setRefreshToken(\string|\Bitrix\Main\DB\SqlExpression $refreshToken)
	 * @method bool hasRefreshToken()
	 * @method bool isRefreshTokenFilled()
	 * @method bool isRefreshTokenChanged()
	 * @method \string remindActualRefreshToken()
	 * @method \string requireRefreshToken()
	 * @method \Bitrix\Socialservices\EO_User resetRefreshToken()
	 * @method \Bitrix\Socialservices\EO_User unsetRefreshToken()
	 * @method \string fillRefreshToken()
	 * @method \boolean getSendActivity()
	 * @method \Bitrix\Socialservices\EO_User setSendActivity(\boolean|\Bitrix\Main\DB\SqlExpression $sendActivity)
	 * @method bool hasSendActivity()
	 * @method bool isSendActivityFilled()
	 * @method bool isSendActivityChanged()
	 * @method \boolean remindActualSendActivity()
	 * @method \boolean requireSendActivity()
	 * @method \Bitrix\Socialservices\EO_User resetSendActivity()
	 * @method \Bitrix\Socialservices\EO_User unsetSendActivity()
	 * @method \boolean fillSendActivity()
	 * @method \string getSiteId()
	 * @method \Bitrix\Socialservices\EO_User setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Socialservices\EO_User resetSiteId()
	 * @method \Bitrix\Socialservices\EO_User unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \boolean getInitialized()
	 * @method \Bitrix\Socialservices\EO_User setInitialized(\boolean|\Bitrix\Main\DB\SqlExpression $initialized)
	 * @method bool hasInitialized()
	 * @method bool isInitializedFilled()
	 * @method bool isInitializedChanged()
	 * @method \boolean remindActualInitialized()
	 * @method \boolean requireInitialized()
	 * @method \Bitrix\Socialservices\EO_User resetInitialized()
	 * @method \Bitrix\Socialservices\EO_User unsetInitialized()
	 * @method \boolean fillInitialized()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialservices\EO_User setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialservices\EO_User resetUser()
	 * @method \Bitrix\Socialservices\EO_User unsetUser()
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
	 * @method \Bitrix\Socialservices\EO_User set($fieldName, $value)
	 * @method \Bitrix\Socialservices\EO_User reset($fieldName)
	 * @method \Bitrix\Socialservices\EO_User unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialservices\EO_User wakeUp($data)
	 */
	class EO_User {
		/* @var \Bitrix\Socialservices\UserTable */
		static public $dataClass = '\Bitrix\Socialservices\UserTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialservices {
	/**
	 * EO_User_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getLoginList()
	 * @method \string[] fillLogin()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getLastNameList()
	 * @method \string[] fillLastName()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \string[] getPersonalPhotoList()
	 * @method \string[] fillPersonalPhoto()
	 * @method \string[] getExternalAuthIdList()
	 * @method \string[] fillExternalAuthId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \boolean[] getCanDeleteList()
	 * @method \boolean[] fillCanDelete()
	 * @method \string[] getPersonalWwwList()
	 * @method \string[] fillPersonalWww()
	 * @method \string[] getPermissionsList()
	 * @method \string[] fillPermissions()
	 * @method \string[] getOatokenList()
	 * @method \string[] fillOatoken()
	 * @method \int[] getOatokenExpiresList()
	 * @method \int[] fillOatokenExpires()
	 * @method \string[] getOasecretList()
	 * @method \string[] fillOasecret()
	 * @method \string[] getRefreshTokenList()
	 * @method \string[] fillRefreshToken()
	 * @method \boolean[] getSendActivityList()
	 * @method \boolean[] fillSendActivity()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \boolean[] getInitializedList()
	 * @method \boolean[] fillInitialized()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialservices\EO_User_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialservices\EO_User $object)
	 * @method bool has(\Bitrix\Socialservices\EO_User $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_User getByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_User[] getAll()
	 * @method bool remove(\Bitrix\Socialservices\EO_User $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialservices\EO_User_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialservices\EO_User current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_User_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialservices\UserTable */
		static public $dataClass = '\Bitrix\Socialservices\UserTable';
	}
}
namespace Bitrix\Socialservices {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_User_Result exec()
	 * @method \Bitrix\Socialservices\EO_User fetchObject()
	 * @method \Bitrix\Socialservices\EO_User_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_User_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialservices\EO_User fetchObject()
	 * @method \Bitrix\Socialservices\EO_User_Collection fetchCollection()
	 */
	class EO_User_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialservices\EO_User createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialservices\EO_User_Collection createCollection()
	 * @method \Bitrix\Socialservices\EO_User wakeUpObject($row)
	 * @method \Bitrix\Socialservices\EO_User_Collection wakeUpCollection($rows)
	 */
	class EO_User_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialservices\UserLinkTable:socialservices/lib/userlink.php:5522466787d7baf58ad57902fc58112c */
namespace Bitrix\Socialservices {
	/**
	 * EO_UserLink
	 * @see \Bitrix\Socialservices\UserLinkTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialservices\EO_UserLink setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialservices\EO_UserLink setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialservices\EO_UserLink resetUserId()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getSocservUserId()
	 * @method \Bitrix\Socialservices\EO_UserLink setSocservUserId(\int|\Bitrix\Main\DB\SqlExpression $socservUserId)
	 * @method bool hasSocservUserId()
	 * @method bool isSocservUserIdFilled()
	 * @method bool isSocservUserIdChanged()
	 * @method \int remindActualSocservUserId()
	 * @method \int requireSocservUserId()
	 * @method \Bitrix\Socialservices\EO_UserLink resetSocservUserId()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetSocservUserId()
	 * @method \int fillSocservUserId()
	 * @method \int getLinkUserId()
	 * @method \Bitrix\Socialservices\EO_UserLink setLinkUserId(\int|\Bitrix\Main\DB\SqlExpression $linkUserId)
	 * @method bool hasLinkUserId()
	 * @method bool isLinkUserIdFilled()
	 * @method bool isLinkUserIdChanged()
	 * @method \int remindActualLinkUserId()
	 * @method \int requireLinkUserId()
	 * @method \Bitrix\Socialservices\EO_UserLink resetLinkUserId()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetLinkUserId()
	 * @method \int fillLinkUserId()
	 * @method \string getLinkUid()
	 * @method \Bitrix\Socialservices\EO_UserLink setLinkUid(\string|\Bitrix\Main\DB\SqlExpression $linkUid)
	 * @method bool hasLinkUid()
	 * @method bool isLinkUidFilled()
	 * @method bool isLinkUidChanged()
	 * @method \string remindActualLinkUid()
	 * @method \string requireLinkUid()
	 * @method \Bitrix\Socialservices\EO_UserLink resetLinkUid()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetLinkUid()
	 * @method \string fillLinkUid()
	 * @method \string getLinkName()
	 * @method \Bitrix\Socialservices\EO_UserLink setLinkName(\string|\Bitrix\Main\DB\SqlExpression $linkName)
	 * @method bool hasLinkName()
	 * @method bool isLinkNameFilled()
	 * @method bool isLinkNameChanged()
	 * @method \string remindActualLinkName()
	 * @method \string requireLinkName()
	 * @method \Bitrix\Socialservices\EO_UserLink resetLinkName()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetLinkName()
	 * @method \string fillLinkName()
	 * @method \string getLinkLastName()
	 * @method \Bitrix\Socialservices\EO_UserLink setLinkLastName(\string|\Bitrix\Main\DB\SqlExpression $linkLastName)
	 * @method bool hasLinkLastName()
	 * @method bool isLinkLastNameFilled()
	 * @method bool isLinkLastNameChanged()
	 * @method \string remindActualLinkLastName()
	 * @method \string requireLinkLastName()
	 * @method \Bitrix\Socialservices\EO_UserLink resetLinkLastName()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetLinkLastName()
	 * @method \string fillLinkLastName()
	 * @method \string getLinkPicture()
	 * @method \Bitrix\Socialservices\EO_UserLink setLinkPicture(\string|\Bitrix\Main\DB\SqlExpression $linkPicture)
	 * @method bool hasLinkPicture()
	 * @method bool isLinkPictureFilled()
	 * @method bool isLinkPictureChanged()
	 * @method \string remindActualLinkPicture()
	 * @method \string requireLinkPicture()
	 * @method \Bitrix\Socialservices\EO_UserLink resetLinkPicture()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetLinkPicture()
	 * @method \string fillLinkPicture()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialservices\EO_UserLink setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialservices\EO_UserLink resetUser()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Socialservices\EO_User getSocservUser()
	 * @method \Bitrix\Socialservices\EO_User remindActualSocservUser()
	 * @method \Bitrix\Socialservices\EO_User requireSocservUser()
	 * @method \Bitrix\Socialservices\EO_UserLink setSocservUser(\Bitrix\Socialservices\EO_User $object)
	 * @method \Bitrix\Socialservices\EO_UserLink resetSocservUser()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetSocservUser()
	 * @method bool hasSocservUser()
	 * @method bool isSocservUserFilled()
	 * @method bool isSocservUserChanged()
	 * @method \Bitrix\Socialservices\EO_User fillSocservUser()
	 * @method \Bitrix\Main\EO_User getLinkUser()
	 * @method \Bitrix\Main\EO_User remindActualLinkUser()
	 * @method \Bitrix\Main\EO_User requireLinkUser()
	 * @method \Bitrix\Socialservices\EO_UserLink setLinkUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialservices\EO_UserLink resetLinkUser()
	 * @method \Bitrix\Socialservices\EO_UserLink unsetLinkUser()
	 * @method bool hasLinkUser()
	 * @method bool isLinkUserFilled()
	 * @method bool isLinkUserChanged()
	 * @method \Bitrix\Main\EO_User fillLinkUser()
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
	 * @method \Bitrix\Socialservices\EO_UserLink set($fieldName, $value)
	 * @method \Bitrix\Socialservices\EO_UserLink reset($fieldName)
	 * @method \Bitrix\Socialservices\EO_UserLink unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialservices\EO_UserLink wakeUp($data)
	 */
	class EO_UserLink {
		/* @var \Bitrix\Socialservices\UserLinkTable */
		static public $dataClass = '\Bitrix\Socialservices\UserLinkTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialservices {
	/**
	 * EO_UserLink_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getSocservUserIdList()
	 * @method \int[] fillSocservUserId()
	 * @method \int[] getLinkUserIdList()
	 * @method \int[] fillLinkUserId()
	 * @method \string[] getLinkUidList()
	 * @method \string[] fillLinkUid()
	 * @method \string[] getLinkNameList()
	 * @method \string[] fillLinkName()
	 * @method \string[] getLinkLastNameList()
	 * @method \string[] fillLinkLastName()
	 * @method \string[] getLinkPictureList()
	 * @method \string[] fillLinkPicture()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialservices\EO_UserLink_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Socialservices\EO_User[] getSocservUserList()
	 * @method \Bitrix\Socialservices\EO_UserLink_Collection getSocservUserCollection()
	 * @method \Bitrix\Socialservices\EO_User_Collection fillSocservUser()
	 * @method \Bitrix\Main\EO_User[] getLinkUserList()
	 * @method \Bitrix\Socialservices\EO_UserLink_Collection getLinkUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillLinkUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialservices\EO_UserLink $object)
	 * @method bool has(\Bitrix\Socialservices\EO_UserLink $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_UserLink getByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_UserLink[] getAll()
	 * @method bool remove(\Bitrix\Socialservices\EO_UserLink $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialservices\EO_UserLink_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialservices\EO_UserLink current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserLink_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialservices\UserLinkTable */
		static public $dataClass = '\Bitrix\Socialservices\UserLinkTable';
	}
}
namespace Bitrix\Socialservices {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserLink_Result exec()
	 * @method \Bitrix\Socialservices\EO_UserLink fetchObject()
	 * @method \Bitrix\Socialservices\EO_UserLink_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserLink_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialservices\EO_UserLink fetchObject()
	 * @method \Bitrix\Socialservices\EO_UserLink_Collection fetchCollection()
	 */
	class EO_UserLink_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialservices\EO_UserLink createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialservices\EO_UserLink_Collection createCollection()
	 * @method \Bitrix\Socialservices\EO_UserLink wakeUpObject($row)
	 * @method \Bitrix\Socialservices\EO_UserLink_Collection wakeUpCollection($rows)
	 */
	class EO_UserLink_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialservices\ZoomMeetingTable:socialservices/lib/zoommeeting.php:1d104ddfa80387369277314a914af15f */
namespace Bitrix\Socialservices {
	/**
	 * EO_ZoomMeeting
	 * @see \Bitrix\Socialservices\ZoomMeetingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEntityTypeId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setEntityTypeId(\string|\Bitrix\Main\DB\SqlExpression $entityTypeId)
	 * @method bool hasEntityTypeId()
	 * @method bool isEntityTypeIdFilled()
	 * @method bool isEntityTypeIdChanged()
	 * @method \string remindActualEntityTypeId()
	 * @method \string requireEntityTypeId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetEntityTypeId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetEntityTypeId()
	 * @method \string fillEntityTypeId()
	 * @method \int getEntityId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetEntityId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getConferenceUrl()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setConferenceUrl(\string|\Bitrix\Main\DB\SqlExpression $conferenceUrl)
	 * @method bool hasConferenceUrl()
	 * @method bool isConferenceUrlFilled()
	 * @method bool isConferenceUrlChanged()
	 * @method \string remindActualConferenceUrl()
	 * @method \string requireConferenceUrl()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetConferenceUrl()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetConferenceUrl()
	 * @method \string fillConferenceUrl()
	 * @method \int getConferenceExternalId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setConferenceExternalId(\int|\Bitrix\Main\DB\SqlExpression $conferenceExternalId)
	 * @method bool hasConferenceExternalId()
	 * @method bool isConferenceExternalIdFilled()
	 * @method bool isConferenceExternalIdChanged()
	 * @method \int remindActualConferenceExternalId()
	 * @method \int requireConferenceExternalId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetConferenceExternalId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetConferenceExternalId()
	 * @method \int fillConferenceExternalId()
	 * @method \string getConferencePassword()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setConferencePassword(\string|\Bitrix\Main\DB\SqlExpression $conferencePassword)
	 * @method bool hasConferencePassword()
	 * @method bool isConferencePasswordFilled()
	 * @method bool isConferencePasswordChanged()
	 * @method \string remindActualConferencePassword()
	 * @method \string requireConferencePassword()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetConferencePassword()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetConferencePassword()
	 * @method \string fillConferencePassword()
	 * @method \boolean getJoined()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setJoined(\boolean|\Bitrix\Main\DB\SqlExpression $joined)
	 * @method bool hasJoined()
	 * @method bool isJoinedFilled()
	 * @method bool isJoinedChanged()
	 * @method \boolean remindActualJoined()
	 * @method \boolean requireJoined()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetJoined()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetJoined()
	 * @method \boolean fillJoined()
	 * @method \Bitrix\Main\Type\DateTime getConferenceCreated()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setConferenceCreated(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $conferenceCreated)
	 * @method bool hasConferenceCreated()
	 * @method bool isConferenceCreatedFilled()
	 * @method bool isConferenceCreatedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualConferenceCreated()
	 * @method \Bitrix\Main\Type\DateTime requireConferenceCreated()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetConferenceCreated()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetConferenceCreated()
	 * @method \Bitrix\Main\Type\DateTime fillConferenceCreated()
	 * @method \Bitrix\Main\Type\DateTime getConferenceStarted()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setConferenceStarted(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $conferenceStarted)
	 * @method bool hasConferenceStarted()
	 * @method bool isConferenceStartedFilled()
	 * @method bool isConferenceStartedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualConferenceStarted()
	 * @method \Bitrix\Main\Type\DateTime requireConferenceStarted()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetConferenceStarted()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetConferenceStarted()
	 * @method \Bitrix\Main\Type\DateTime fillConferenceStarted()
	 * @method \Bitrix\Main\Type\DateTime getConferenceEnded()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setConferenceEnded(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $conferenceEnded)
	 * @method bool hasConferenceEnded()
	 * @method bool isConferenceEndedFilled()
	 * @method bool isConferenceEndedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualConferenceEnded()
	 * @method \Bitrix\Main\Type\DateTime requireConferenceEnded()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetConferenceEnded()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetConferenceEnded()
	 * @method \Bitrix\Main\Type\DateTime fillConferenceEnded()
	 * @method \boolean getHasRecording()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setHasRecording(\boolean|\Bitrix\Main\DB\SqlExpression $hasRecording)
	 * @method bool hasHasRecording()
	 * @method bool isHasRecordingFilled()
	 * @method bool isHasRecordingChanged()
	 * @method \boolean remindActualHasRecording()
	 * @method \boolean requireHasRecording()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetHasRecording()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetHasRecording()
	 * @method \boolean fillHasRecording()
	 * @method \int getDuration()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setDuration(\int|\Bitrix\Main\DB\SqlExpression $duration)
	 * @method bool hasDuration()
	 * @method bool isDurationFilled()
	 * @method bool isDurationChanged()
	 * @method \int remindActualDuration()
	 * @method \int requireDuration()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetDuration()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetDuration()
	 * @method \int fillDuration()
	 * @method \string getTitle()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetTitle()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getShortLink()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting setShortLink(\string|\Bitrix\Main\DB\SqlExpression $shortLink)
	 * @method bool hasShortLink()
	 * @method bool isShortLinkFilled()
	 * @method bool isShortLinkChanged()
	 * @method \string remindActualShortLink()
	 * @method \string requireShortLink()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting resetShortLink()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unsetShortLink()
	 * @method \string fillShortLink()
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
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting set($fieldName, $value)
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting reset($fieldName)
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialservices\EO_ZoomMeeting wakeUp($data)
	 */
	class EO_ZoomMeeting {
		/* @var \Bitrix\Socialservices\ZoomMeetingTable */
		static public $dataClass = '\Bitrix\Socialservices\ZoomMeetingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialservices {
	/**
	 * EO_ZoomMeeting_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEntityTypeIdList()
	 * @method \string[] fillEntityTypeId()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getConferenceUrlList()
	 * @method \string[] fillConferenceUrl()
	 * @method \int[] getConferenceExternalIdList()
	 * @method \int[] fillConferenceExternalId()
	 * @method \string[] getConferencePasswordList()
	 * @method \string[] fillConferencePassword()
	 * @method \boolean[] getJoinedList()
	 * @method \boolean[] fillJoined()
	 * @method \Bitrix\Main\Type\DateTime[] getConferenceCreatedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillConferenceCreated()
	 * @method \Bitrix\Main\Type\DateTime[] getConferenceStartedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillConferenceStarted()
	 * @method \Bitrix\Main\Type\DateTime[] getConferenceEndedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillConferenceEnded()
	 * @method \boolean[] getHasRecordingList()
	 * @method \boolean[] fillHasRecording()
	 * @method \int[] getDurationList()
	 * @method \int[] fillDuration()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getShortLinkList()
	 * @method \string[] fillShortLink()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialservices\EO_ZoomMeeting $object)
	 * @method bool has(\Bitrix\Socialservices\EO_ZoomMeeting $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting getByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting[] getAll()
	 * @method bool remove(\Bitrix\Socialservices\EO_ZoomMeeting $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialservices\EO_ZoomMeeting_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ZoomMeeting_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialservices\ZoomMeetingTable */
		static public $dataClass = '\Bitrix\Socialservices\ZoomMeetingTable';
	}
}
namespace Bitrix\Socialservices {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ZoomMeeting_Result exec()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting fetchObject()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ZoomMeeting_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting fetchObject()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting_Collection fetchCollection()
	 */
	class EO_ZoomMeeting_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting_Collection createCollection()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting wakeUpObject($row)
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting_Collection wakeUpCollection($rows)
	 */
	class EO_ZoomMeeting_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialservices\ZoomMeetingRecordingTable:socialservices/lib/zoommeetingrecording.php:0368edde66618646dda9dab3036045c1 */
namespace Bitrix\Socialservices {
	/**
	 * EO_ZoomMeetingRecording
	 * @see \Bitrix\Socialservices\ZoomMeetingRecordingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getExternalId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetExternalId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \int getMeetingId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setMeetingId(\int|\Bitrix\Main\DB\SqlExpression $meetingId)
	 * @method bool hasMeetingId()
	 * @method bool isMeetingIdFilled()
	 * @method bool isMeetingIdChanged()
	 * @method \int remindActualMeetingId()
	 * @method \int requireMeetingId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetMeetingId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetMeetingId()
	 * @method \int fillMeetingId()
	 * @method \Bitrix\Main\Type\DateTime getStartDate()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setStartDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $startDate)
	 * @method bool hasStartDate()
	 * @method bool isStartDateFilled()
	 * @method bool isStartDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStartDate()
	 * @method \Bitrix\Main\Type\DateTime requireStartDate()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetStartDate()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetStartDate()
	 * @method \Bitrix\Main\Type\DateTime fillStartDate()
	 * @method \Bitrix\Main\Type\DateTime getEndDate()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setEndDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $endDate)
	 * @method bool hasEndDate()
	 * @method bool isEndDateFilled()
	 * @method bool isEndDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualEndDate()
	 * @method \Bitrix\Main\Type\DateTime requireEndDate()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetEndDate()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetEndDate()
	 * @method \Bitrix\Main\Type\DateTime fillEndDate()
	 * @method \string getFileType()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setFileType(\string|\Bitrix\Main\DB\SqlExpression $fileType)
	 * @method bool hasFileType()
	 * @method bool isFileTypeFilled()
	 * @method bool isFileTypeChanged()
	 * @method \string remindActualFileType()
	 * @method \string requireFileType()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetFileType()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetFileType()
	 * @method \string fillFileType()
	 * @method \int getFileSize()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetFileSize()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \string getPlayUrl()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setPlayUrl(\string|\Bitrix\Main\DB\SqlExpression $playUrl)
	 * @method bool hasPlayUrl()
	 * @method bool isPlayUrlFilled()
	 * @method bool isPlayUrlChanged()
	 * @method \string remindActualPlayUrl()
	 * @method \string requirePlayUrl()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetPlayUrl()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetPlayUrl()
	 * @method \string fillPlayUrl()
	 * @method \string getDownloadUrl()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setDownloadUrl(\string|\Bitrix\Main\DB\SqlExpression $downloadUrl)
	 * @method bool hasDownloadUrl()
	 * @method bool isDownloadUrlFilled()
	 * @method bool isDownloadUrlChanged()
	 * @method \string remindActualDownloadUrl()
	 * @method \string requireDownloadUrl()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetDownloadUrl()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetDownloadUrl()
	 * @method \string fillDownloadUrl()
	 * @method \string getRecordingType()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setRecordingType(\string|\Bitrix\Main\DB\SqlExpression $recordingType)
	 * @method bool hasRecordingType()
	 * @method bool isRecordingTypeFilled()
	 * @method bool isRecordingTypeChanged()
	 * @method \string remindActualRecordingType()
	 * @method \string requireRecordingType()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetRecordingType()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetRecordingType()
	 * @method \string fillRecordingType()
	 * @method \string getDownloadToken()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setDownloadToken(\string|\Bitrix\Main\DB\SqlExpression $downloadToken)
	 * @method bool hasDownloadToken()
	 * @method bool isDownloadTokenFilled()
	 * @method bool isDownloadTokenChanged()
	 * @method \string remindActualDownloadToken()
	 * @method \string requireDownloadToken()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetDownloadToken()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetDownloadToken()
	 * @method \string fillDownloadToken()
	 * @method \string getPassword()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setPassword(\string|\Bitrix\Main\DB\SqlExpression $password)
	 * @method bool hasPassword()
	 * @method bool isPasswordFilled()
	 * @method bool isPasswordChanged()
	 * @method \string remindActualPassword()
	 * @method \string requirePassword()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetPassword()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetPassword()
	 * @method \string fillPassword()
	 * @method \int getFileId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetFileId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetFileId()
	 * @method \int fillFileId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting getMeeting()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting remindActualMeeting()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting requireMeeting()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording setMeeting(\Bitrix\Socialservices\EO_ZoomMeeting $object)
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording resetMeeting()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unsetMeeting()
	 * @method bool hasMeeting()
	 * @method bool isMeetingFilled()
	 * @method bool isMeetingChanged()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting fillMeeting()
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
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording set($fieldName, $value)
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording reset($fieldName)
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialservices\EO_ZoomMeetingRecording wakeUp($data)
	 */
	class EO_ZoomMeetingRecording {
		/* @var \Bitrix\Socialservices\ZoomMeetingRecordingTable */
		static public $dataClass = '\Bitrix\Socialservices\ZoomMeetingRecordingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialservices {
	/**
	 * EO_ZoomMeetingRecording_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \int[] getMeetingIdList()
	 * @method \int[] fillMeetingId()
	 * @method \Bitrix\Main\Type\DateTime[] getStartDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStartDate()
	 * @method \Bitrix\Main\Type\DateTime[] getEndDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillEndDate()
	 * @method \string[] getFileTypeList()
	 * @method \string[] fillFileType()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \string[] getPlayUrlList()
	 * @method \string[] fillPlayUrl()
	 * @method \string[] getDownloadUrlList()
	 * @method \string[] fillDownloadUrl()
	 * @method \string[] getRecordingTypeList()
	 * @method \string[] fillRecordingType()
	 * @method \string[] getDownloadTokenList()
	 * @method \string[] fillDownloadToken()
	 * @method \string[] getPasswordList()
	 * @method \string[] fillPassword()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting[] getMeetingList()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording_Collection getMeetingCollection()
	 * @method \Bitrix\Socialservices\EO_ZoomMeeting_Collection fillMeeting()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialservices\EO_ZoomMeetingRecording $object)
	 * @method bool has(\Bitrix\Socialservices\EO_ZoomMeetingRecording $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording getByPrimary($primary)
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording[] getAll()
	 * @method bool remove(\Bitrix\Socialservices\EO_ZoomMeetingRecording $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialservices\EO_ZoomMeetingRecording_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ZoomMeetingRecording_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialservices\ZoomMeetingRecordingTable */
		static public $dataClass = '\Bitrix\Socialservices\ZoomMeetingRecordingTable';
	}
}
namespace Bitrix\Socialservices {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ZoomMeetingRecording_Result exec()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording fetchObject()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ZoomMeetingRecording_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording fetchObject()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording_Collection fetchCollection()
	 */
	class EO_ZoomMeetingRecording_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording_Collection createCollection()
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording wakeUpObject($row)
	 * @method \Bitrix\Socialservices\EO_ZoomMeetingRecording_Collection wakeUpCollection($rows)
	 */
	class EO_ZoomMeetingRecording_Entity extends \Bitrix\Main\ORM\Entity {}
}