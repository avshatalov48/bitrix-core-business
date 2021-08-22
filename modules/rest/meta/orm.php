<?php

/* ORMENTITYANNOTATION:Bitrix\Rest\APAuth\PasswordTable:rest/lib/apauth/password.php:d66354a5bac1d0b399f17220d43b3c66 */
namespace Bitrix\Rest\APAuth {
	/**
	 * EO_Password
	 * @see \Bitrix\Rest\APAuth\PasswordTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\APAuth\EO_Password setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Rest\APAuth\EO_Password setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Rest\APAuth\EO_Password resetUserId()
	 * @method \Bitrix\Rest\APAuth\EO_Password unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getPassword()
	 * @method \Bitrix\Rest\APAuth\EO_Password setPassword(\string|\Bitrix\Main\DB\SqlExpression $password)
	 * @method bool hasPassword()
	 * @method bool isPasswordFilled()
	 * @method bool isPasswordChanged()
	 * @method \string remindActualPassword()
	 * @method \string requirePassword()
	 * @method \Bitrix\Rest\APAuth\EO_Password resetPassword()
	 * @method \Bitrix\Rest\APAuth\EO_Password unsetPassword()
	 * @method \string fillPassword()
	 * @method \boolean getActive()
	 * @method \Bitrix\Rest\APAuth\EO_Password setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Rest\APAuth\EO_Password resetActive()
	 * @method \Bitrix\Rest\APAuth\EO_Password unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getTitle()
	 * @method \Bitrix\Rest\APAuth\EO_Password setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Rest\APAuth\EO_Password resetTitle()
	 * @method \Bitrix\Rest\APAuth\EO_Password unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getComment()
	 * @method \Bitrix\Rest\APAuth\EO_Password setComment(\string|\Bitrix\Main\DB\SqlExpression $comment)
	 * @method bool hasComment()
	 * @method bool isCommentFilled()
	 * @method bool isCommentChanged()
	 * @method \string remindActualComment()
	 * @method \string requireComment()
	 * @method \Bitrix\Rest\APAuth\EO_Password resetComment()
	 * @method \Bitrix\Rest\APAuth\EO_Password unsetComment()
	 * @method \string fillComment()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Rest\APAuth\EO_Password setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Rest\APAuth\EO_Password resetDateCreate()
	 * @method \Bitrix\Rest\APAuth\EO_Password unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateLogin()
	 * @method \Bitrix\Rest\APAuth\EO_Password setDateLogin(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateLogin)
	 * @method bool hasDateLogin()
	 * @method bool isDateLoginFilled()
	 * @method bool isDateLoginChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateLogin()
	 * @method \Bitrix\Main\Type\DateTime requireDateLogin()
	 * @method \Bitrix\Rest\APAuth\EO_Password resetDateLogin()
	 * @method \Bitrix\Rest\APAuth\EO_Password unsetDateLogin()
	 * @method \Bitrix\Main\Type\DateTime fillDateLogin()
	 * @method \string getLastIp()
	 * @method \Bitrix\Rest\APAuth\EO_Password setLastIp(\string|\Bitrix\Main\DB\SqlExpression $lastIp)
	 * @method bool hasLastIp()
	 * @method bool isLastIpFilled()
	 * @method bool isLastIpChanged()
	 * @method \string remindActualLastIp()
	 * @method \string requireLastIp()
	 * @method \Bitrix\Rest\APAuth\EO_Password resetLastIp()
	 * @method \Bitrix\Rest\APAuth\EO_Password unsetLastIp()
	 * @method \string fillLastIp()
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
	 * @method \Bitrix\Rest\APAuth\EO_Password set($fieldName, $value)
	 * @method \Bitrix\Rest\APAuth\EO_Password reset($fieldName)
	 * @method \Bitrix\Rest\APAuth\EO_Password unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\APAuth\EO_Password wakeUp($data)
	 */
	class EO_Password {
		/* @var \Bitrix\Rest\APAuth\PasswordTable */
		static public $dataClass = '\Bitrix\Rest\APAuth\PasswordTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest\APAuth {
	/**
	 * EO_Password_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getPasswordList()
	 * @method \string[] fillPassword()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getCommentList()
	 * @method \string[] fillComment()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateLoginList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateLogin()
	 * @method \string[] getLastIpList()
	 * @method \string[] fillLastIp()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\APAuth\EO_Password $object)
	 * @method bool has(\Bitrix\Rest\APAuth\EO_Password $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\APAuth\EO_Password getByPrimary($primary)
	 * @method \Bitrix\Rest\APAuth\EO_Password[] getAll()
	 * @method bool remove(\Bitrix\Rest\APAuth\EO_Password $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\APAuth\EO_Password_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\APAuth\EO_Password current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Password_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\APAuth\PasswordTable */
		static public $dataClass = '\Bitrix\Rest\APAuth\PasswordTable';
	}
}
namespace Bitrix\Rest\APAuth {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Password_Result exec()
	 * @method \Bitrix\Rest\APAuth\EO_Password fetchObject()
	 * @method \Bitrix\Rest\APAuth\EO_Password_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Password_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\APAuth\EO_Password fetchObject()
	 * @method \Bitrix\Rest\APAuth\EO_Password_Collection fetchCollection()
	 */
	class EO_Password_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\APAuth\EO_Password createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\APAuth\EO_Password_Collection createCollection()
	 * @method \Bitrix\Rest\APAuth\EO_Password wakeUpObject($row)
	 * @method \Bitrix\Rest\APAuth\EO_Password_Collection wakeUpCollection($rows)
	 */
	class EO_Password_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\APAuth\PermissionTable:rest/lib/apauth/permission.php:9f36311bab2258f091d2ad7fe59b7958 */
namespace Bitrix\Rest\APAuth {
	/**
	 * EO_Permission
	 * @see \Bitrix\Rest\APAuth\PermissionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\APAuth\EO_Permission setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPasswordId()
	 * @method \Bitrix\Rest\APAuth\EO_Permission setPasswordId(\int|\Bitrix\Main\DB\SqlExpression $passwordId)
	 * @method bool hasPasswordId()
	 * @method bool isPasswordIdFilled()
	 * @method bool isPasswordIdChanged()
	 * @method \int remindActualPasswordId()
	 * @method \int requirePasswordId()
	 * @method \Bitrix\Rest\APAuth\EO_Permission resetPasswordId()
	 * @method \Bitrix\Rest\APAuth\EO_Permission unsetPasswordId()
	 * @method \int fillPasswordId()
	 * @method \string getPerm()
	 * @method \Bitrix\Rest\APAuth\EO_Permission setPerm(\string|\Bitrix\Main\DB\SqlExpression $perm)
	 * @method bool hasPerm()
	 * @method bool isPermFilled()
	 * @method bool isPermChanged()
	 * @method \string remindActualPerm()
	 * @method \string requirePerm()
	 * @method \Bitrix\Rest\APAuth\EO_Permission resetPerm()
	 * @method \Bitrix\Rest\APAuth\EO_Permission unsetPerm()
	 * @method \string fillPerm()
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
	 * @method \Bitrix\Rest\APAuth\EO_Permission set($fieldName, $value)
	 * @method \Bitrix\Rest\APAuth\EO_Permission reset($fieldName)
	 * @method \Bitrix\Rest\APAuth\EO_Permission unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\APAuth\EO_Permission wakeUp($data)
	 */
	class EO_Permission {
		/* @var \Bitrix\Rest\APAuth\PermissionTable */
		static public $dataClass = '\Bitrix\Rest\APAuth\PermissionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest\APAuth {
	/**
	 * EO_Permission_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPasswordIdList()
	 * @method \int[] fillPasswordId()
	 * @method \string[] getPermList()
	 * @method \string[] fillPerm()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\APAuth\EO_Permission $object)
	 * @method bool has(\Bitrix\Rest\APAuth\EO_Permission $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\APAuth\EO_Permission getByPrimary($primary)
	 * @method \Bitrix\Rest\APAuth\EO_Permission[] getAll()
	 * @method bool remove(\Bitrix\Rest\APAuth\EO_Permission $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\APAuth\EO_Permission_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\APAuth\EO_Permission current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Permission_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\APAuth\PermissionTable */
		static public $dataClass = '\Bitrix\Rest\APAuth\PermissionTable';
	}
}
namespace Bitrix\Rest\APAuth {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Permission_Result exec()
	 * @method \Bitrix\Rest\APAuth\EO_Permission fetchObject()
	 * @method \Bitrix\Rest\APAuth\EO_Permission_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Permission_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\APAuth\EO_Permission fetchObject()
	 * @method \Bitrix\Rest\APAuth\EO_Permission_Collection fetchCollection()
	 */
	class EO_Permission_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\APAuth\EO_Permission createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\APAuth\EO_Permission_Collection createCollection()
	 * @method \Bitrix\Rest\APAuth\EO_Permission wakeUpObject($row)
	 * @method \Bitrix\Rest\APAuth\EO_Permission_Collection wakeUpCollection($rows)
	 */
	class EO_Permission_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\AppTable:rest/lib/app.php:12fd3fca385d44ac5a31f831845b270f */
namespace Bitrix\Rest {
	/**
	 * EO_App
	 * @see \Bitrix\Rest\AppTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_App setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getClientId()
	 * @method \Bitrix\Rest\EO_App setClientId(\string|\Bitrix\Main\DB\SqlExpression $clientId)
	 * @method bool hasClientId()
	 * @method bool isClientIdFilled()
	 * @method bool isClientIdChanged()
	 * @method \string remindActualClientId()
	 * @method \string requireClientId()
	 * @method \Bitrix\Rest\EO_App resetClientId()
	 * @method \Bitrix\Rest\EO_App unsetClientId()
	 * @method \string fillClientId()
	 * @method \string getCode()
	 * @method \Bitrix\Rest\EO_App setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Rest\EO_App resetCode()
	 * @method \Bitrix\Rest\EO_App unsetCode()
	 * @method \string fillCode()
	 * @method \boolean getActive()
	 * @method \Bitrix\Rest\EO_App setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Rest\EO_App resetActive()
	 * @method \Bitrix\Rest\EO_App unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getInstalled()
	 * @method \Bitrix\Rest\EO_App setInstalled(\boolean|\Bitrix\Main\DB\SqlExpression $installed)
	 * @method bool hasInstalled()
	 * @method bool isInstalledFilled()
	 * @method bool isInstalledChanged()
	 * @method \boolean remindActualInstalled()
	 * @method \boolean requireInstalled()
	 * @method \Bitrix\Rest\EO_App resetInstalled()
	 * @method \Bitrix\Rest\EO_App unsetInstalled()
	 * @method \boolean fillInstalled()
	 * @method \string getUrl()
	 * @method \Bitrix\Rest\EO_App setUrl(\string|\Bitrix\Main\DB\SqlExpression $url)
	 * @method bool hasUrl()
	 * @method bool isUrlFilled()
	 * @method bool isUrlChanged()
	 * @method \string remindActualUrl()
	 * @method \string requireUrl()
	 * @method \Bitrix\Rest\EO_App resetUrl()
	 * @method \Bitrix\Rest\EO_App unsetUrl()
	 * @method \string fillUrl()
	 * @method \string getUrlDemo()
	 * @method \Bitrix\Rest\EO_App setUrlDemo(\string|\Bitrix\Main\DB\SqlExpression $urlDemo)
	 * @method bool hasUrlDemo()
	 * @method bool isUrlDemoFilled()
	 * @method bool isUrlDemoChanged()
	 * @method \string remindActualUrlDemo()
	 * @method \string requireUrlDemo()
	 * @method \Bitrix\Rest\EO_App resetUrlDemo()
	 * @method \Bitrix\Rest\EO_App unsetUrlDemo()
	 * @method \string fillUrlDemo()
	 * @method \string getUrlInstall()
	 * @method \Bitrix\Rest\EO_App setUrlInstall(\string|\Bitrix\Main\DB\SqlExpression $urlInstall)
	 * @method bool hasUrlInstall()
	 * @method bool isUrlInstallFilled()
	 * @method bool isUrlInstallChanged()
	 * @method \string remindActualUrlInstall()
	 * @method \string requireUrlInstall()
	 * @method \Bitrix\Rest\EO_App resetUrlInstall()
	 * @method \Bitrix\Rest\EO_App unsetUrlInstall()
	 * @method \string fillUrlInstall()
	 * @method \string getVersion()
	 * @method \Bitrix\Rest\EO_App setVersion(\string|\Bitrix\Main\DB\SqlExpression $version)
	 * @method bool hasVersion()
	 * @method bool isVersionFilled()
	 * @method bool isVersionChanged()
	 * @method \string remindActualVersion()
	 * @method \string requireVersion()
	 * @method \Bitrix\Rest\EO_App resetVersion()
	 * @method \Bitrix\Rest\EO_App unsetVersion()
	 * @method \string fillVersion()
	 * @method \string getScope()
	 * @method \Bitrix\Rest\EO_App setScope(\string|\Bitrix\Main\DB\SqlExpression $scope)
	 * @method bool hasScope()
	 * @method bool isScopeFilled()
	 * @method bool isScopeChanged()
	 * @method \string remindActualScope()
	 * @method \string requireScope()
	 * @method \Bitrix\Rest\EO_App resetScope()
	 * @method \Bitrix\Rest\EO_App unsetScope()
	 * @method \string fillScope()
	 * @method \string getStatus()
	 * @method \Bitrix\Rest\EO_App setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Rest\EO_App resetStatus()
	 * @method \Bitrix\Rest\EO_App unsetStatus()
	 * @method \string fillStatus()
	 * @method \Bitrix\Main\Type\Date getDateFinish()
	 * @method \Bitrix\Rest\EO_App setDateFinish(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $dateFinish)
	 * @method bool hasDateFinish()
	 * @method bool isDateFinishFilled()
	 * @method bool isDateFinishChanged()
	 * @method \Bitrix\Main\Type\Date remindActualDateFinish()
	 * @method \Bitrix\Main\Type\Date requireDateFinish()
	 * @method \Bitrix\Rest\EO_App resetDateFinish()
	 * @method \Bitrix\Rest\EO_App unsetDateFinish()
	 * @method \Bitrix\Main\Type\Date fillDateFinish()
	 * @method \boolean getIsTrialed()
	 * @method \Bitrix\Rest\EO_App setIsTrialed(\boolean|\Bitrix\Main\DB\SqlExpression $isTrialed)
	 * @method bool hasIsTrialed()
	 * @method bool isIsTrialedFilled()
	 * @method bool isIsTrialedChanged()
	 * @method \boolean remindActualIsTrialed()
	 * @method \boolean requireIsTrialed()
	 * @method \Bitrix\Rest\EO_App resetIsTrialed()
	 * @method \Bitrix\Rest\EO_App unsetIsTrialed()
	 * @method \boolean fillIsTrialed()
	 * @method \string getSharedKey()
	 * @method \Bitrix\Rest\EO_App setSharedKey(\string|\Bitrix\Main\DB\SqlExpression $sharedKey)
	 * @method bool hasSharedKey()
	 * @method bool isSharedKeyFilled()
	 * @method bool isSharedKeyChanged()
	 * @method \string remindActualSharedKey()
	 * @method \string requireSharedKey()
	 * @method \Bitrix\Rest\EO_App resetSharedKey()
	 * @method \Bitrix\Rest\EO_App unsetSharedKey()
	 * @method \string fillSharedKey()
	 * @method \string getClientSecret()
	 * @method \Bitrix\Rest\EO_App setClientSecret(\string|\Bitrix\Main\DB\SqlExpression $clientSecret)
	 * @method bool hasClientSecret()
	 * @method bool isClientSecretFilled()
	 * @method bool isClientSecretChanged()
	 * @method \string remindActualClientSecret()
	 * @method \string requireClientSecret()
	 * @method \Bitrix\Rest\EO_App resetClientSecret()
	 * @method \Bitrix\Rest\EO_App unsetClientSecret()
	 * @method \string fillClientSecret()
	 * @method \string getAppName()
	 * @method \Bitrix\Rest\EO_App setAppName(\string|\Bitrix\Main\DB\SqlExpression $appName)
	 * @method bool hasAppName()
	 * @method bool isAppNameFilled()
	 * @method bool isAppNameChanged()
	 * @method \string remindActualAppName()
	 * @method \string requireAppName()
	 * @method \Bitrix\Rest\EO_App resetAppName()
	 * @method \Bitrix\Rest\EO_App unsetAppName()
	 * @method \string fillAppName()
	 * @method \string getAccess()
	 * @method \Bitrix\Rest\EO_App setAccess(\string|\Bitrix\Main\DB\SqlExpression $access)
	 * @method bool hasAccess()
	 * @method bool isAccessFilled()
	 * @method bool isAccessChanged()
	 * @method \string remindActualAccess()
	 * @method \string requireAccess()
	 * @method \Bitrix\Rest\EO_App resetAccess()
	 * @method \Bitrix\Rest\EO_App unsetAccess()
	 * @method \string fillAccess()
	 * @method \string getApplicationToken()
	 * @method \Bitrix\Rest\EO_App setApplicationToken(\string|\Bitrix\Main\DB\SqlExpression $applicationToken)
	 * @method bool hasApplicationToken()
	 * @method bool isApplicationTokenFilled()
	 * @method bool isApplicationTokenChanged()
	 * @method \string remindActualApplicationToken()
	 * @method \string requireApplicationToken()
	 * @method \Bitrix\Rest\EO_App resetApplicationToken()
	 * @method \Bitrix\Rest\EO_App unsetApplicationToken()
	 * @method \string fillApplicationToken()
	 * @method \boolean getMobile()
	 * @method \Bitrix\Rest\EO_App setMobile(\boolean|\Bitrix\Main\DB\SqlExpression $mobile)
	 * @method bool hasMobile()
	 * @method bool isMobileFilled()
	 * @method bool isMobileChanged()
	 * @method \boolean remindActualMobile()
	 * @method \boolean requireMobile()
	 * @method \Bitrix\Rest\EO_App resetMobile()
	 * @method \Bitrix\Rest\EO_App unsetMobile()
	 * @method \boolean fillMobile()
	 * @method \boolean getUserInstall()
	 * @method \Bitrix\Rest\EO_App setUserInstall(\boolean|\Bitrix\Main\DB\SqlExpression $userInstall)
	 * @method bool hasUserInstall()
	 * @method bool isUserInstallFilled()
	 * @method bool isUserInstallChanged()
	 * @method \boolean remindActualUserInstall()
	 * @method \boolean requireUserInstall()
	 * @method \Bitrix\Rest\EO_App resetUserInstall()
	 * @method \Bitrix\Rest\EO_App unsetUserInstall()
	 * @method \boolean fillUserInstall()
	 * @method \Bitrix\Rest\EO_AppLang getLang()
	 * @method \Bitrix\Rest\EO_AppLang remindActualLang()
	 * @method \Bitrix\Rest\EO_AppLang requireLang()
	 * @method \Bitrix\Rest\EO_App setLang(\Bitrix\Rest\EO_AppLang $object)
	 * @method \Bitrix\Rest\EO_App resetLang()
	 * @method \Bitrix\Rest\EO_App unsetLang()
	 * @method bool hasLang()
	 * @method bool isLangFilled()
	 * @method bool isLangChanged()
	 * @method \Bitrix\Rest\EO_AppLang fillLang()
	 * @method \Bitrix\Rest\EO_AppLang getLangDefault()
	 * @method \Bitrix\Rest\EO_AppLang remindActualLangDefault()
	 * @method \Bitrix\Rest\EO_AppLang requireLangDefault()
	 * @method \Bitrix\Rest\EO_App setLangDefault(\Bitrix\Rest\EO_AppLang $object)
	 * @method \Bitrix\Rest\EO_App resetLangDefault()
	 * @method \Bitrix\Rest\EO_App unsetLangDefault()
	 * @method bool hasLangDefault()
	 * @method bool isLangDefaultFilled()
	 * @method bool isLangDefaultChanged()
	 * @method \Bitrix\Rest\EO_AppLang fillLangDefault()
	 * @method \Bitrix\Rest\EO_AppLang getLangLicense()
	 * @method \Bitrix\Rest\EO_AppLang remindActualLangLicense()
	 * @method \Bitrix\Rest\EO_AppLang requireLangLicense()
	 * @method \Bitrix\Rest\EO_App setLangLicense(\Bitrix\Rest\EO_AppLang $object)
	 * @method \Bitrix\Rest\EO_App resetLangLicense()
	 * @method \Bitrix\Rest\EO_App unsetLangLicense()
	 * @method bool hasLangLicense()
	 * @method bool isLangLicenseFilled()
	 * @method bool isLangLicenseChanged()
	 * @method \Bitrix\Rest\EO_AppLang fillLangLicense()
	 * @method \Bitrix\Rest\EO_AppLang_Collection getLangAll()
	 * @method \Bitrix\Rest\EO_AppLang_Collection requireLangAll()
	 * @method \Bitrix\Rest\EO_AppLang_Collection fillLangAll()
	 * @method bool hasLangAll()
	 * @method bool isLangAllFilled()
	 * @method bool isLangAllChanged()
	 * @method void addToLangAll(\Bitrix\Rest\EO_AppLang $appLang)
	 * @method void removeFromLangAll(\Bitrix\Rest\EO_AppLang $appLang)
	 * @method void removeAllLangAll()
	 * @method \Bitrix\Rest\EO_App resetLangAll()
	 * @method \Bitrix\Rest\EO_App unsetLangAll()
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
	 * @method \Bitrix\Rest\EO_App set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_App reset($fieldName)
	 * @method \Bitrix\Rest\EO_App unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_App wakeUp($data)
	 */
	class EO_App {
		/* @var \Bitrix\Rest\AppTable */
		static public $dataClass = '\Bitrix\Rest\AppTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_App_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getClientIdList()
	 * @method \string[] fillClientId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getInstalledList()
	 * @method \boolean[] fillInstalled()
	 * @method \string[] getUrlList()
	 * @method \string[] fillUrl()
	 * @method \string[] getUrlDemoList()
	 * @method \string[] fillUrlDemo()
	 * @method \string[] getUrlInstallList()
	 * @method \string[] fillUrlInstall()
	 * @method \string[] getVersionList()
	 * @method \string[] fillVersion()
	 * @method \string[] getScopeList()
	 * @method \string[] fillScope()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 * @method \Bitrix\Main\Type\Date[] getDateFinishList()
	 * @method \Bitrix\Main\Type\Date[] fillDateFinish()
	 * @method \boolean[] getIsTrialedList()
	 * @method \boolean[] fillIsTrialed()
	 * @method \string[] getSharedKeyList()
	 * @method \string[] fillSharedKey()
	 * @method \string[] getClientSecretList()
	 * @method \string[] fillClientSecret()
	 * @method \string[] getAppNameList()
	 * @method \string[] fillAppName()
	 * @method \string[] getAccessList()
	 * @method \string[] fillAccess()
	 * @method \string[] getApplicationTokenList()
	 * @method \string[] fillApplicationToken()
	 * @method \boolean[] getMobileList()
	 * @method \boolean[] fillMobile()
	 * @method \boolean[] getUserInstallList()
	 * @method \boolean[] fillUserInstall()
	 * @method \Bitrix\Rest\EO_AppLang[] getLangList()
	 * @method \Bitrix\Rest\EO_App_Collection getLangCollection()
	 * @method \Bitrix\Rest\EO_AppLang_Collection fillLang()
	 * @method \Bitrix\Rest\EO_AppLang[] getLangDefaultList()
	 * @method \Bitrix\Rest\EO_App_Collection getLangDefaultCollection()
	 * @method \Bitrix\Rest\EO_AppLang_Collection fillLangDefault()
	 * @method \Bitrix\Rest\EO_AppLang[] getLangLicenseList()
	 * @method \Bitrix\Rest\EO_App_Collection getLangLicenseCollection()
	 * @method \Bitrix\Rest\EO_AppLang_Collection fillLangLicense()
	 * @method \Bitrix\Rest\EO_AppLang_Collection[] getLangAllList()
	 * @method \Bitrix\Rest\EO_AppLang_Collection getLangAllCollection()
	 * @method \Bitrix\Rest\EO_AppLang_Collection fillLangAll()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_App $object)
	 * @method bool has(\Bitrix\Rest\EO_App $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_App getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_App[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_App $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_App_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_App current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_App_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\AppTable */
		static public $dataClass = '\Bitrix\Rest\AppTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_App_Result exec()
	 * @method \Bitrix\Rest\EO_App fetchObject()
	 * @method \Bitrix\Rest\EO_App_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_App_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_App fetchObject()
	 * @method \Bitrix\Rest\EO_App_Collection fetchCollection()
	 */
	class EO_App_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_App createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_App_Collection createCollection()
	 * @method \Bitrix\Rest\EO_App wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_App_Collection wakeUpCollection($rows)
	 */
	class EO_App_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\AppLangTable:rest/lib/applang.php:3de3142056f701aa43d1f230cf907993 */
namespace Bitrix\Rest {
	/**
	 * EO_AppLang
	 * @see \Bitrix\Rest\AppLangTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_AppLang setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getAppId()
	 * @method \Bitrix\Rest\EO_AppLang setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int remindActualAppId()
	 * @method \int requireAppId()
	 * @method \Bitrix\Rest\EO_AppLang resetAppId()
	 * @method \Bitrix\Rest\EO_AppLang unsetAppId()
	 * @method \int fillAppId()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Rest\EO_AppLang setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\Rest\EO_AppLang resetLanguageId()
	 * @method \Bitrix\Rest\EO_AppLang unsetLanguageId()
	 * @method \string fillLanguageId()
	 * @method \string getMenuName()
	 * @method \Bitrix\Rest\EO_AppLang setMenuName(\string|\Bitrix\Main\DB\SqlExpression $menuName)
	 * @method bool hasMenuName()
	 * @method bool isMenuNameFilled()
	 * @method bool isMenuNameChanged()
	 * @method \string remindActualMenuName()
	 * @method \string requireMenuName()
	 * @method \Bitrix\Rest\EO_AppLang resetMenuName()
	 * @method \Bitrix\Rest\EO_AppLang unsetMenuName()
	 * @method \string fillMenuName()
	 * @method \Bitrix\Rest\EO_App getApp()
	 * @method \Bitrix\Rest\EO_App remindActualApp()
	 * @method \Bitrix\Rest\EO_App requireApp()
	 * @method \Bitrix\Rest\EO_AppLang setApp(\Bitrix\Rest\EO_App $object)
	 * @method \Bitrix\Rest\EO_AppLang resetApp()
	 * @method \Bitrix\Rest\EO_AppLang unsetApp()
	 * @method bool hasApp()
	 * @method bool isAppFilled()
	 * @method bool isAppChanged()
	 * @method \Bitrix\Rest\EO_App fillApp()
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
	 * @method \Bitrix\Rest\EO_AppLang set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_AppLang reset($fieldName)
	 * @method \Bitrix\Rest\EO_AppLang unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_AppLang wakeUp($data)
	 */
	class EO_AppLang {
		/* @var \Bitrix\Rest\AppLangTable */
		static public $dataClass = '\Bitrix\Rest\AppLangTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_AppLang_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getAppIdList()
	 * @method \int[] fillAppId()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 * @method \string[] getMenuNameList()
	 * @method \string[] fillMenuName()
	 * @method \Bitrix\Rest\EO_App[] getAppList()
	 * @method \Bitrix\Rest\EO_AppLang_Collection getAppCollection()
	 * @method \Bitrix\Rest\EO_App_Collection fillApp()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_AppLang $object)
	 * @method bool has(\Bitrix\Rest\EO_AppLang $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_AppLang getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_AppLang[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_AppLang $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_AppLang_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_AppLang current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_AppLang_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\AppLangTable */
		static public $dataClass = '\Bitrix\Rest\AppLangTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_AppLang_Result exec()
	 * @method \Bitrix\Rest\EO_AppLang fetchObject()
	 * @method \Bitrix\Rest\EO_AppLang_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_AppLang_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_AppLang fetchObject()
	 * @method \Bitrix\Rest\EO_AppLang_Collection fetchCollection()
	 */
	class EO_AppLang_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_AppLang createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_AppLang_Collection createCollection()
	 * @method \Bitrix\Rest\EO_AppLang wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_AppLang_Collection wakeUpCollection($rows)
	 */
	class EO_AppLang_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\AppLogTable:rest/lib/applog.php:c2deea38fd070ddba021ba59b1bf5f70 */
namespace Bitrix\Rest {
	/**
	 * EO_AppLog
	 * @see \Bitrix\Rest\AppLogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_AppLog setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Rest\EO_AppLog setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Rest\EO_AppLog resetTimestampX()
	 * @method \Bitrix\Rest\EO_AppLog unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getAppId()
	 * @method \Bitrix\Rest\EO_AppLog setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int remindActualAppId()
	 * @method \int requireAppId()
	 * @method \Bitrix\Rest\EO_AppLog resetAppId()
	 * @method \Bitrix\Rest\EO_AppLog unsetAppId()
	 * @method \int fillAppId()
	 * @method \string getActionType()
	 * @method \Bitrix\Rest\EO_AppLog setActionType(\string|\Bitrix\Main\DB\SqlExpression $actionType)
	 * @method bool hasActionType()
	 * @method bool isActionTypeFilled()
	 * @method bool isActionTypeChanged()
	 * @method \string remindActualActionType()
	 * @method \string requireActionType()
	 * @method \Bitrix\Rest\EO_AppLog resetActionType()
	 * @method \Bitrix\Rest\EO_AppLog unsetActionType()
	 * @method \string fillActionType()
	 * @method \int getUserId()
	 * @method \Bitrix\Rest\EO_AppLog setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Rest\EO_AppLog resetUserId()
	 * @method \Bitrix\Rest\EO_AppLog unsetUserId()
	 * @method \int fillUserId()
	 * @method \boolean getUserAdmin()
	 * @method \Bitrix\Rest\EO_AppLog setUserAdmin(\boolean|\Bitrix\Main\DB\SqlExpression $userAdmin)
	 * @method bool hasUserAdmin()
	 * @method bool isUserAdminFilled()
	 * @method bool isUserAdminChanged()
	 * @method \boolean remindActualUserAdmin()
	 * @method \boolean requireUserAdmin()
	 * @method \Bitrix\Rest\EO_AppLog resetUserAdmin()
	 * @method \Bitrix\Rest\EO_AppLog unsetUserAdmin()
	 * @method \boolean fillUserAdmin()
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
	 * @method \Bitrix\Rest\EO_AppLog set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_AppLog reset($fieldName)
	 * @method \Bitrix\Rest\EO_AppLog unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_AppLog wakeUp($data)
	 */
	class EO_AppLog {
		/* @var \Bitrix\Rest\AppLogTable */
		static public $dataClass = '\Bitrix\Rest\AppLogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_AppLog_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getAppIdList()
	 * @method \int[] fillAppId()
	 * @method \string[] getActionTypeList()
	 * @method \string[] fillActionType()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \boolean[] getUserAdminList()
	 * @method \boolean[] fillUserAdmin()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_AppLog $object)
	 * @method bool has(\Bitrix\Rest\EO_AppLog $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_AppLog getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_AppLog[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_AppLog $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_AppLog_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_AppLog current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_AppLog_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\AppLogTable */
		static public $dataClass = '\Bitrix\Rest\AppLogTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_AppLog_Result exec()
	 * @method \Bitrix\Rest\EO_AppLog fetchObject()
	 * @method \Bitrix\Rest\EO_AppLog_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_AppLog_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_AppLog fetchObject()
	 * @method \Bitrix\Rest\EO_AppLog_Collection fetchCollection()
	 */
	class EO_AppLog_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_AppLog createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_AppLog_Collection createCollection()
	 * @method \Bitrix\Rest\EO_AppLog wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_AppLog_Collection wakeUpCollection($rows)
	 */
	class EO_AppLog_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\Configuration\OwnerEntityTable:rest/lib/configuration/ownerentity.php:eef3c8187c9fa98270bfc4e488d7916d */
namespace Bitrix\Rest\Configuration {
	/**
	 * EO_OwnerEntity
	 * @see \Bitrix\Rest\Configuration\OwnerEntityTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getOwnerType()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity setOwnerType(\string|\Bitrix\Main\DB\SqlExpression $ownerType)
	 * @method bool hasOwnerType()
	 * @method bool isOwnerTypeFilled()
	 * @method bool isOwnerTypeChanged()
	 * @method \string remindActualOwnerType()
	 * @method \string requireOwnerType()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity resetOwnerType()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity unsetOwnerType()
	 * @method \string fillOwnerType()
	 * @method \string getOwner()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity setOwner(\string|\Bitrix\Main\DB\SqlExpression $owner)
	 * @method bool hasOwner()
	 * @method bool isOwnerFilled()
	 * @method bool isOwnerChanged()
	 * @method \string remindActualOwner()
	 * @method \string requireOwner()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity resetOwner()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity unsetOwner()
	 * @method \string fillOwner()
	 * @method \string getEntityType()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity resetEntityType()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \string getEntity()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity resetEntity()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity unsetEntity()
	 * @method \string fillEntity()
	 * @method \Bitrix\Rest\EO_App getDataApp()
	 * @method \Bitrix\Rest\EO_App remindActualDataApp()
	 * @method \Bitrix\Rest\EO_App requireDataApp()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity setDataApp(\Bitrix\Rest\EO_App $object)
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity resetDataApp()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity unsetDataApp()
	 * @method bool hasDataApp()
	 * @method bool isDataAppFilled()
	 * @method bool isDataAppChanged()
	 * @method \Bitrix\Rest\EO_App fillDataApp()
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
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity set($fieldName, $value)
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity reset($fieldName)
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\Configuration\EO_OwnerEntity wakeUp($data)
	 */
	class EO_OwnerEntity {
		/* @var \Bitrix\Rest\Configuration\OwnerEntityTable */
		static public $dataClass = '\Bitrix\Rest\Configuration\OwnerEntityTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest\Configuration {
	/**
	 * EO_OwnerEntity_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getOwnerTypeList()
	 * @method \string[] fillOwnerType()
	 * @method \string[] getOwnerList()
	 * @method \string[] fillOwner()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \Bitrix\Rest\EO_App[] getDataAppList()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity_Collection getDataAppCollection()
	 * @method \Bitrix\Rest\EO_App_Collection fillDataApp()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\Configuration\EO_OwnerEntity $object)
	 * @method bool has(\Bitrix\Rest\Configuration\EO_OwnerEntity $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity getByPrimary($primary)
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity[] getAll()
	 * @method bool remove(\Bitrix\Rest\Configuration\EO_OwnerEntity $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\Configuration\EO_OwnerEntity_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_OwnerEntity_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\Configuration\OwnerEntityTable */
		static public $dataClass = '\Bitrix\Rest\Configuration\OwnerEntityTable';
	}
}
namespace Bitrix\Rest\Configuration {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_OwnerEntity_Result exec()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity fetchObject()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_OwnerEntity_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity fetchObject()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity_Collection fetchCollection()
	 */
	class EO_OwnerEntity_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity_Collection createCollection()
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity wakeUpObject($row)
	 * @method \Bitrix\Rest\Configuration\EO_OwnerEntity_Collection wakeUpCollection($rows)
	 */
	class EO_OwnerEntity_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\Configuration\StorageTable:rest/lib/configuration/storage.php:4630dd6c62590272b3c343f107ad3b66 */
namespace Bitrix\Rest\Configuration {
	/**
	 * EO_Storage
	 * @see \Bitrix\Rest\Configuration\StorageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\Configuration\EO_Storage setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getCreateTime()
	 * @method \Bitrix\Rest\Configuration\EO_Storage setCreateTime(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createTime)
	 * @method bool hasCreateTime()
	 * @method bool isCreateTimeFilled()
	 * @method bool isCreateTimeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreateTime()
	 * @method \Bitrix\Main\Type\DateTime requireCreateTime()
	 * @method \Bitrix\Rest\Configuration\EO_Storage resetCreateTime()
	 * @method \Bitrix\Rest\Configuration\EO_Storage unsetCreateTime()
	 * @method \Bitrix\Main\Type\DateTime fillCreateTime()
	 * @method \string getContext()
	 * @method \Bitrix\Rest\Configuration\EO_Storage setContext(\string|\Bitrix\Main\DB\SqlExpression $context)
	 * @method bool hasContext()
	 * @method bool isContextFilled()
	 * @method bool isContextChanged()
	 * @method \string remindActualContext()
	 * @method \string requireContext()
	 * @method \Bitrix\Rest\Configuration\EO_Storage resetContext()
	 * @method \Bitrix\Rest\Configuration\EO_Storage unsetContext()
	 * @method \string fillContext()
	 * @method \string getCode()
	 * @method \Bitrix\Rest\Configuration\EO_Storage setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Rest\Configuration\EO_Storage resetCode()
	 * @method \Bitrix\Rest\Configuration\EO_Storage unsetCode()
	 * @method \string fillCode()
	 * @method array getData()
	 * @method \Bitrix\Rest\Configuration\EO_Storage setData(array|\Bitrix\Main\DB\SqlExpression $data)
	 * @method bool hasData()
	 * @method bool isDataFilled()
	 * @method bool isDataChanged()
	 * @method array remindActualData()
	 * @method array requireData()
	 * @method \Bitrix\Rest\Configuration\EO_Storage resetData()
	 * @method \Bitrix\Rest\Configuration\EO_Storage unsetData()
	 * @method array fillData()
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
	 * @method \Bitrix\Rest\Configuration\EO_Storage set($fieldName, $value)
	 * @method \Bitrix\Rest\Configuration\EO_Storage reset($fieldName)
	 * @method \Bitrix\Rest\Configuration\EO_Storage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\Configuration\EO_Storage wakeUp($data)
	 */
	class EO_Storage {
		/* @var \Bitrix\Rest\Configuration\StorageTable */
		static public $dataClass = '\Bitrix\Rest\Configuration\StorageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest\Configuration {
	/**
	 * EO_Storage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getCreateTimeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreateTime()
	 * @method \string[] getContextList()
	 * @method \string[] fillContext()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method array[] getDataList()
	 * @method array[] fillData()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\Configuration\EO_Storage $object)
	 * @method bool has(\Bitrix\Rest\Configuration\EO_Storage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\Configuration\EO_Storage getByPrimary($primary)
	 * @method \Bitrix\Rest\Configuration\EO_Storage[] getAll()
	 * @method bool remove(\Bitrix\Rest\Configuration\EO_Storage $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\Configuration\EO_Storage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\Configuration\EO_Storage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Storage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\Configuration\StorageTable */
		static public $dataClass = '\Bitrix\Rest\Configuration\StorageTable';
	}
}
namespace Bitrix\Rest\Configuration {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Storage_Result exec()
	 * @method \Bitrix\Rest\Configuration\EO_Storage fetchObject()
	 * @method \Bitrix\Rest\Configuration\EO_Storage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Storage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\Configuration\EO_Storage fetchObject()
	 * @method \Bitrix\Rest\Configuration\EO_Storage_Collection fetchCollection()
	 */
	class EO_Storage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\Configuration\EO_Storage createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\Configuration\EO_Storage_Collection createCollection()
	 * @method \Bitrix\Rest\Configuration\EO_Storage wakeUpObject($row)
	 * @method \Bitrix\Rest\Configuration\EO_Storage_Collection wakeUpCollection($rows)
	 */
	class EO_Storage_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\EventTable:rest/lib/event.php:6d150f7d0ef0c58f291cd9e597a39eaf */
namespace Bitrix\Rest {
	/**
	 * EO_Event
	 * @see \Bitrix\Rest\EventTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_Event setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getAppId()
	 * @method \Bitrix\Rest\EO_Event setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int remindActualAppId()
	 * @method \int requireAppId()
	 * @method \Bitrix\Rest\EO_Event resetAppId()
	 * @method \Bitrix\Rest\EO_Event unsetAppId()
	 * @method \int fillAppId()
	 * @method \string getEventName()
	 * @method \Bitrix\Rest\EO_Event setEventName(\string|\Bitrix\Main\DB\SqlExpression $eventName)
	 * @method bool hasEventName()
	 * @method bool isEventNameFilled()
	 * @method bool isEventNameChanged()
	 * @method \string remindActualEventName()
	 * @method \string requireEventName()
	 * @method \Bitrix\Rest\EO_Event resetEventName()
	 * @method \Bitrix\Rest\EO_Event unsetEventName()
	 * @method \string fillEventName()
	 * @method \string getEventHandler()
	 * @method \Bitrix\Rest\EO_Event setEventHandler(\string|\Bitrix\Main\DB\SqlExpression $eventHandler)
	 * @method bool hasEventHandler()
	 * @method bool isEventHandlerFilled()
	 * @method bool isEventHandlerChanged()
	 * @method \string remindActualEventHandler()
	 * @method \string requireEventHandler()
	 * @method \Bitrix\Rest\EO_Event resetEventHandler()
	 * @method \Bitrix\Rest\EO_Event unsetEventHandler()
	 * @method \string fillEventHandler()
	 * @method \int getUserId()
	 * @method \Bitrix\Rest\EO_Event setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Rest\EO_Event resetUserId()
	 * @method \Bitrix\Rest\EO_Event unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getTitle()
	 * @method \Bitrix\Rest\EO_Event setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Rest\EO_Event resetTitle()
	 * @method \Bitrix\Rest\EO_Event unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getComment()
	 * @method \Bitrix\Rest\EO_Event setComment(\string|\Bitrix\Main\DB\SqlExpression $comment)
	 * @method bool hasComment()
	 * @method bool isCommentFilled()
	 * @method bool isCommentChanged()
	 * @method \string remindActualComment()
	 * @method \string requireComment()
	 * @method \Bitrix\Rest\EO_Event resetComment()
	 * @method \Bitrix\Rest\EO_Event unsetComment()
	 * @method \string fillComment()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Rest\EO_Event setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Rest\EO_Event resetDateCreate()
	 * @method \Bitrix\Rest\EO_Event unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \string getApplicationToken()
	 * @method \Bitrix\Rest\EO_Event setApplicationToken(\string|\Bitrix\Main\DB\SqlExpression $applicationToken)
	 * @method bool hasApplicationToken()
	 * @method bool isApplicationTokenFilled()
	 * @method bool isApplicationTokenChanged()
	 * @method \string remindActualApplicationToken()
	 * @method \string requireApplicationToken()
	 * @method \Bitrix\Rest\EO_Event resetApplicationToken()
	 * @method \Bitrix\Rest\EO_Event unsetApplicationToken()
	 * @method \string fillApplicationToken()
	 * @method \string getConnectorId()
	 * @method \Bitrix\Rest\EO_Event setConnectorId(\string|\Bitrix\Main\DB\SqlExpression $connectorId)
	 * @method bool hasConnectorId()
	 * @method bool isConnectorIdFilled()
	 * @method bool isConnectorIdChanged()
	 * @method \string remindActualConnectorId()
	 * @method \string requireConnectorId()
	 * @method \Bitrix\Rest\EO_Event resetConnectorId()
	 * @method \Bitrix\Rest\EO_Event unsetConnectorId()
	 * @method \string fillConnectorId()
	 * @method \int getIntegrationId()
	 * @method \Bitrix\Rest\EO_Event setIntegrationId(\int|\Bitrix\Main\DB\SqlExpression $integrationId)
	 * @method bool hasIntegrationId()
	 * @method bool isIntegrationIdFilled()
	 * @method bool isIntegrationIdChanged()
	 * @method \int remindActualIntegrationId()
	 * @method \int requireIntegrationId()
	 * @method \Bitrix\Rest\EO_Event resetIntegrationId()
	 * @method \Bitrix\Rest\EO_Event unsetIntegrationId()
	 * @method \int fillIntegrationId()
	 * @method array getOptions()
	 * @method \Bitrix\Rest\EO_Event setOptions(array|\Bitrix\Main\DB\SqlExpression $options)
	 * @method bool hasOptions()
	 * @method bool isOptionsFilled()
	 * @method bool isOptionsChanged()
	 * @method array remindActualOptions()
	 * @method array requireOptions()
	 * @method \Bitrix\Rest\EO_Event resetOptions()
	 * @method \Bitrix\Rest\EO_Event unsetOptions()
	 * @method array fillOptions()
	 * @method \Bitrix\Rest\EO_App getRestApp()
	 * @method \Bitrix\Rest\EO_App remindActualRestApp()
	 * @method \Bitrix\Rest\EO_App requireRestApp()
	 * @method \Bitrix\Rest\EO_Event setRestApp(\Bitrix\Rest\EO_App $object)
	 * @method \Bitrix\Rest\EO_Event resetRestApp()
	 * @method \Bitrix\Rest\EO_Event unsetRestApp()
	 * @method bool hasRestApp()
	 * @method bool isRestAppFilled()
	 * @method bool isRestAppChanged()
	 * @method \Bitrix\Rest\EO_App fillRestApp()
	 * @method \Bitrix\Bitrix24\EO_Apps getApp()
	 * @method \Bitrix\Bitrix24\EO_Apps remindActualApp()
	 * @method \Bitrix\Bitrix24\EO_Apps requireApp()
	 * @method \Bitrix\Rest\EO_Event setApp(\Bitrix\Bitrix24\EO_Apps $object)
	 * @method \Bitrix\Rest\EO_Event resetApp()
	 * @method \Bitrix\Rest\EO_Event unsetApp()
	 * @method bool hasApp()
	 * @method bool isAppFilled()
	 * @method bool isAppChanged()
	 * @method \Bitrix\Bitrix24\EO_Apps fillApp()
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
	 * @method \Bitrix\Rest\EO_Event set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_Event reset($fieldName)
	 * @method \Bitrix\Rest\EO_Event unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_Event wakeUp($data)
	 */
	class EO_Event {
		/* @var \Bitrix\Rest\EventTable */
		static public $dataClass = '\Bitrix\Rest\EventTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_Event_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getAppIdList()
	 * @method \int[] fillAppId()
	 * @method \string[] getEventNameList()
	 * @method \string[] fillEventName()
	 * @method \string[] getEventHandlerList()
	 * @method \string[] fillEventHandler()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getCommentList()
	 * @method \string[] fillComment()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \string[] getApplicationTokenList()
	 * @method \string[] fillApplicationToken()
	 * @method \string[] getConnectorIdList()
	 * @method \string[] fillConnectorId()
	 * @method \int[] getIntegrationIdList()
	 * @method \int[] fillIntegrationId()
	 * @method array[] getOptionsList()
	 * @method array[] fillOptions()
	 * @method \Bitrix\Rest\EO_App[] getRestAppList()
	 * @method \Bitrix\Rest\EO_Event_Collection getRestAppCollection()
	 * @method \Bitrix\Rest\EO_App_Collection fillRestApp()
	 * @method \Bitrix\Bitrix24\EO_Apps[] getAppList()
	 * @method \Bitrix\Rest\EO_Event_Collection getAppCollection()
	 * @method \Bitrix\Bitrix24\EO_Apps_Collection fillApp()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_Event $object)
	 * @method bool has(\Bitrix\Rest\EO_Event $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_Event getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_Event[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_Event $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_Event_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_Event current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Event_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\EventTable */
		static public $dataClass = '\Bitrix\Rest\EventTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Event_Result exec()
	 * @method \Bitrix\Rest\EO_Event fetchObject()
	 * @method \Bitrix\Rest\EO_Event_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Event_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_Event fetchObject()
	 * @method \Bitrix\Rest\EO_Event_Collection fetchCollection()
	 */
	class EO_Event_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_Event createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_Event_Collection createCollection()
	 * @method \Bitrix\Rest\EO_Event wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_Event_Collection wakeUpCollection($rows)
	 */
	class EO_Event_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\EventOfflineTable:rest/lib/eventoffline.php:5d551050bb67bb53f0e50e0cc648a608 */
namespace Bitrix\Rest {
	/**
	 * EO_EventOffline
	 * @see \Bitrix\Rest\EventOfflineTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_EventOffline setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Rest\EO_EventOffline setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Rest\EO_EventOffline resetTimestampX()
	 * @method \Bitrix\Rest\EO_EventOffline unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getMessageId()
	 * @method \Bitrix\Rest\EO_EventOffline setMessageId(\string|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \string remindActualMessageId()
	 * @method \string requireMessageId()
	 * @method \Bitrix\Rest\EO_EventOffline resetMessageId()
	 * @method \Bitrix\Rest\EO_EventOffline unsetMessageId()
	 * @method \string fillMessageId()
	 * @method \int getAppId()
	 * @method \Bitrix\Rest\EO_EventOffline setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int remindActualAppId()
	 * @method \int requireAppId()
	 * @method \Bitrix\Rest\EO_EventOffline resetAppId()
	 * @method \Bitrix\Rest\EO_EventOffline unsetAppId()
	 * @method \int fillAppId()
	 * @method \string getEventName()
	 * @method \Bitrix\Rest\EO_EventOffline setEventName(\string|\Bitrix\Main\DB\SqlExpression $eventName)
	 * @method bool hasEventName()
	 * @method bool isEventNameFilled()
	 * @method bool isEventNameChanged()
	 * @method \string remindActualEventName()
	 * @method \string requireEventName()
	 * @method \Bitrix\Rest\EO_EventOffline resetEventName()
	 * @method \Bitrix\Rest\EO_EventOffline unsetEventName()
	 * @method \string fillEventName()
	 * @method \string getEventData()
	 * @method \Bitrix\Rest\EO_EventOffline setEventData(\string|\Bitrix\Main\DB\SqlExpression $eventData)
	 * @method bool hasEventData()
	 * @method bool isEventDataFilled()
	 * @method bool isEventDataChanged()
	 * @method \string remindActualEventData()
	 * @method \string requireEventData()
	 * @method \Bitrix\Rest\EO_EventOffline resetEventData()
	 * @method \Bitrix\Rest\EO_EventOffline unsetEventData()
	 * @method \string fillEventData()
	 * @method \string getEventAdditional()
	 * @method \Bitrix\Rest\EO_EventOffline setEventAdditional(\string|\Bitrix\Main\DB\SqlExpression $eventAdditional)
	 * @method bool hasEventAdditional()
	 * @method bool isEventAdditionalFilled()
	 * @method bool isEventAdditionalChanged()
	 * @method \string remindActualEventAdditional()
	 * @method \string requireEventAdditional()
	 * @method \Bitrix\Rest\EO_EventOffline resetEventAdditional()
	 * @method \Bitrix\Rest\EO_EventOffline unsetEventAdditional()
	 * @method \string fillEventAdditional()
	 * @method \string getProcessId()
	 * @method \Bitrix\Rest\EO_EventOffline setProcessId(\string|\Bitrix\Main\DB\SqlExpression $processId)
	 * @method bool hasProcessId()
	 * @method bool isProcessIdFilled()
	 * @method bool isProcessIdChanged()
	 * @method \string remindActualProcessId()
	 * @method \string requireProcessId()
	 * @method \Bitrix\Rest\EO_EventOffline resetProcessId()
	 * @method \Bitrix\Rest\EO_EventOffline unsetProcessId()
	 * @method \string fillProcessId()
	 * @method \string getConnectorId()
	 * @method \Bitrix\Rest\EO_EventOffline setConnectorId(\string|\Bitrix\Main\DB\SqlExpression $connectorId)
	 * @method bool hasConnectorId()
	 * @method bool isConnectorIdFilled()
	 * @method bool isConnectorIdChanged()
	 * @method \string remindActualConnectorId()
	 * @method \string requireConnectorId()
	 * @method \Bitrix\Rest\EO_EventOffline resetConnectorId()
	 * @method \Bitrix\Rest\EO_EventOffline unsetConnectorId()
	 * @method \string fillConnectorId()
	 * @method \int getError()
	 * @method \Bitrix\Rest\EO_EventOffline setError(\int|\Bitrix\Main\DB\SqlExpression $error)
	 * @method bool hasError()
	 * @method bool isErrorFilled()
	 * @method bool isErrorChanged()
	 * @method \int remindActualError()
	 * @method \int requireError()
	 * @method \Bitrix\Rest\EO_EventOffline resetError()
	 * @method \Bitrix\Rest\EO_EventOffline unsetError()
	 * @method \int fillError()
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
	 * @method \Bitrix\Rest\EO_EventOffline set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_EventOffline reset($fieldName)
	 * @method \Bitrix\Rest\EO_EventOffline unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_EventOffline wakeUp($data)
	 */
	class EO_EventOffline {
		/* @var \Bitrix\Rest\EventOfflineTable */
		static public $dataClass = '\Bitrix\Rest\EventOfflineTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_EventOffline_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getMessageIdList()
	 * @method \string[] fillMessageId()
	 * @method \int[] getAppIdList()
	 * @method \int[] fillAppId()
	 * @method \string[] getEventNameList()
	 * @method \string[] fillEventName()
	 * @method \string[] getEventDataList()
	 * @method \string[] fillEventData()
	 * @method \string[] getEventAdditionalList()
	 * @method \string[] fillEventAdditional()
	 * @method \string[] getProcessIdList()
	 * @method \string[] fillProcessId()
	 * @method \string[] getConnectorIdList()
	 * @method \string[] fillConnectorId()
	 * @method \int[] getErrorList()
	 * @method \int[] fillError()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_EventOffline $object)
	 * @method bool has(\Bitrix\Rest\EO_EventOffline $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_EventOffline getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_EventOffline[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_EventOffline $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_EventOffline_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_EventOffline current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EventOffline_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\EventOfflineTable */
		static public $dataClass = '\Bitrix\Rest\EventOfflineTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventOffline_Result exec()
	 * @method \Bitrix\Rest\EO_EventOffline fetchObject()
	 * @method \Bitrix\Rest\EO_EventOffline_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventOffline_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_EventOffline fetchObject()
	 * @method \Bitrix\Rest\EO_EventOffline_Collection fetchCollection()
	 */
	class EO_EventOffline_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_EventOffline createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_EventOffline_Collection createCollection()
	 * @method \Bitrix\Rest\EO_EventOffline wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_EventOffline_Collection wakeUpCollection($rows)
	 */
	class EO_EventOffline_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\LogTable:rest/lib/log.php:95ea101677b274711810d5e12ab4522c */
namespace Bitrix\Rest {
	/**
	 * EO_Log
	 * @see \Bitrix\Rest\LogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_Log setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Rest\EO_Log setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Rest\EO_Log resetTimestampX()
	 * @method \Bitrix\Rest\EO_Log unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getClientId()
	 * @method \Bitrix\Rest\EO_Log setClientId(\string|\Bitrix\Main\DB\SqlExpression $clientId)
	 * @method bool hasClientId()
	 * @method bool isClientIdFilled()
	 * @method bool isClientIdChanged()
	 * @method \string remindActualClientId()
	 * @method \string requireClientId()
	 * @method \Bitrix\Rest\EO_Log resetClientId()
	 * @method \Bitrix\Rest\EO_Log unsetClientId()
	 * @method \string fillClientId()
	 * @method \int getPasswordId()
	 * @method \Bitrix\Rest\EO_Log setPasswordId(\int|\Bitrix\Main\DB\SqlExpression $passwordId)
	 * @method bool hasPasswordId()
	 * @method bool isPasswordIdFilled()
	 * @method bool isPasswordIdChanged()
	 * @method \int remindActualPasswordId()
	 * @method \int requirePasswordId()
	 * @method \Bitrix\Rest\EO_Log resetPasswordId()
	 * @method \Bitrix\Rest\EO_Log unsetPasswordId()
	 * @method \int fillPasswordId()
	 * @method \string getScope()
	 * @method \Bitrix\Rest\EO_Log setScope(\string|\Bitrix\Main\DB\SqlExpression $scope)
	 * @method bool hasScope()
	 * @method bool isScopeFilled()
	 * @method bool isScopeChanged()
	 * @method \string remindActualScope()
	 * @method \string requireScope()
	 * @method \Bitrix\Rest\EO_Log resetScope()
	 * @method \Bitrix\Rest\EO_Log unsetScope()
	 * @method \string fillScope()
	 * @method \string getMethod()
	 * @method \Bitrix\Rest\EO_Log setMethod(\string|\Bitrix\Main\DB\SqlExpression $method)
	 * @method bool hasMethod()
	 * @method bool isMethodFilled()
	 * @method bool isMethodChanged()
	 * @method \string remindActualMethod()
	 * @method \string requireMethod()
	 * @method \Bitrix\Rest\EO_Log resetMethod()
	 * @method \Bitrix\Rest\EO_Log unsetMethod()
	 * @method \string fillMethod()
	 * @method \string getRequestMethod()
	 * @method \Bitrix\Rest\EO_Log setRequestMethod(\string|\Bitrix\Main\DB\SqlExpression $requestMethod)
	 * @method bool hasRequestMethod()
	 * @method bool isRequestMethodFilled()
	 * @method bool isRequestMethodChanged()
	 * @method \string remindActualRequestMethod()
	 * @method \string requireRequestMethod()
	 * @method \Bitrix\Rest\EO_Log resetRequestMethod()
	 * @method \Bitrix\Rest\EO_Log unsetRequestMethod()
	 * @method \string fillRequestMethod()
	 * @method \string getRequestUri()
	 * @method \Bitrix\Rest\EO_Log setRequestUri(\string|\Bitrix\Main\DB\SqlExpression $requestUri)
	 * @method bool hasRequestUri()
	 * @method bool isRequestUriFilled()
	 * @method bool isRequestUriChanged()
	 * @method \string remindActualRequestUri()
	 * @method \string requireRequestUri()
	 * @method \Bitrix\Rest\EO_Log resetRequestUri()
	 * @method \Bitrix\Rest\EO_Log unsetRequestUri()
	 * @method \string fillRequestUri()
	 * @method \string getRequestAuth()
	 * @method \Bitrix\Rest\EO_Log setRequestAuth(\string|\Bitrix\Main\DB\SqlExpression $requestAuth)
	 * @method bool hasRequestAuth()
	 * @method bool isRequestAuthFilled()
	 * @method bool isRequestAuthChanged()
	 * @method \string remindActualRequestAuth()
	 * @method \string requireRequestAuth()
	 * @method \Bitrix\Rest\EO_Log resetRequestAuth()
	 * @method \Bitrix\Rest\EO_Log unsetRequestAuth()
	 * @method \string fillRequestAuth()
	 * @method \string getRequestData()
	 * @method \Bitrix\Rest\EO_Log setRequestData(\string|\Bitrix\Main\DB\SqlExpression $requestData)
	 * @method bool hasRequestData()
	 * @method bool isRequestDataFilled()
	 * @method bool isRequestDataChanged()
	 * @method \string remindActualRequestData()
	 * @method \string requireRequestData()
	 * @method \Bitrix\Rest\EO_Log resetRequestData()
	 * @method \Bitrix\Rest\EO_Log unsetRequestData()
	 * @method \string fillRequestData()
	 * @method \string getResponseStatus()
	 * @method \Bitrix\Rest\EO_Log setResponseStatus(\string|\Bitrix\Main\DB\SqlExpression $responseStatus)
	 * @method bool hasResponseStatus()
	 * @method bool isResponseStatusFilled()
	 * @method bool isResponseStatusChanged()
	 * @method \string remindActualResponseStatus()
	 * @method \string requireResponseStatus()
	 * @method \Bitrix\Rest\EO_Log resetResponseStatus()
	 * @method \Bitrix\Rest\EO_Log unsetResponseStatus()
	 * @method \string fillResponseStatus()
	 * @method \string getResponseData()
	 * @method \Bitrix\Rest\EO_Log setResponseData(\string|\Bitrix\Main\DB\SqlExpression $responseData)
	 * @method bool hasResponseData()
	 * @method bool isResponseDataFilled()
	 * @method bool isResponseDataChanged()
	 * @method \string remindActualResponseData()
	 * @method \string requireResponseData()
	 * @method \Bitrix\Rest\EO_Log resetResponseData()
	 * @method \Bitrix\Rest\EO_Log unsetResponseData()
	 * @method \string fillResponseData()
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
	 * @method \Bitrix\Rest\EO_Log set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_Log reset($fieldName)
	 * @method \Bitrix\Rest\EO_Log unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_Log wakeUp($data)
	 */
	class EO_Log {
		/* @var \Bitrix\Rest\LogTable */
		static public $dataClass = '\Bitrix\Rest\LogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_Log_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getClientIdList()
	 * @method \string[] fillClientId()
	 * @method \int[] getPasswordIdList()
	 * @method \int[] fillPasswordId()
	 * @method \string[] getScopeList()
	 * @method \string[] fillScope()
	 * @method \string[] getMethodList()
	 * @method \string[] fillMethod()
	 * @method \string[] getRequestMethodList()
	 * @method \string[] fillRequestMethod()
	 * @method \string[] getRequestUriList()
	 * @method \string[] fillRequestUri()
	 * @method \string[] getRequestAuthList()
	 * @method \string[] fillRequestAuth()
	 * @method \string[] getRequestDataList()
	 * @method \string[] fillRequestData()
	 * @method \string[] getResponseStatusList()
	 * @method \string[] fillResponseStatus()
	 * @method \string[] getResponseDataList()
	 * @method \string[] fillResponseData()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_Log $object)
	 * @method bool has(\Bitrix\Rest\EO_Log $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_Log getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_Log[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_Log $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_Log_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_Log current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Log_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\LogTable */
		static public $dataClass = '\Bitrix\Rest\LogTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Log_Result exec()
	 * @method \Bitrix\Rest\EO_Log fetchObject()
	 * @method \Bitrix\Rest\EO_Log_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Log_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_Log fetchObject()
	 * @method \Bitrix\Rest\EO_Log_Collection fetchCollection()
	 */
	class EO_Log_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_Log createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_Log_Collection createCollection()
	 * @method \Bitrix\Rest\EO_Log wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_Log_Collection wakeUpCollection($rows)
	 */
	class EO_Log_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\PlacementTable:rest/lib/placement.php:69a814c3e5eacd2650e2640e7e2d521e */
namespace Bitrix\Rest {
	/**
	 * EO_Placement
	 * @see \Bitrix\Rest\PlacementTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_Placement setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getAppId()
	 * @method \Bitrix\Rest\EO_Placement setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int remindActualAppId()
	 * @method \int requireAppId()
	 * @method \Bitrix\Rest\EO_Placement resetAppId()
	 * @method \Bitrix\Rest\EO_Placement unsetAppId()
	 * @method \int fillAppId()
	 * @method \string getPlacement()
	 * @method \Bitrix\Rest\EO_Placement setPlacement(\string|\Bitrix\Main\DB\SqlExpression $placement)
	 * @method bool hasPlacement()
	 * @method bool isPlacementFilled()
	 * @method bool isPlacementChanged()
	 * @method \string remindActualPlacement()
	 * @method \string requirePlacement()
	 * @method \Bitrix\Rest\EO_Placement resetPlacement()
	 * @method \Bitrix\Rest\EO_Placement unsetPlacement()
	 * @method \string fillPlacement()
	 * @method \string getPlacementHandler()
	 * @method \Bitrix\Rest\EO_Placement setPlacementHandler(\string|\Bitrix\Main\DB\SqlExpression $placementHandler)
	 * @method bool hasPlacementHandler()
	 * @method bool isPlacementHandlerFilled()
	 * @method bool isPlacementHandlerChanged()
	 * @method \string remindActualPlacementHandler()
	 * @method \string requirePlacementHandler()
	 * @method \Bitrix\Rest\EO_Placement resetPlacementHandler()
	 * @method \Bitrix\Rest\EO_Placement unsetPlacementHandler()
	 * @method \string fillPlacementHandler()
	 * @method \string getGroupName()
	 * @method \Bitrix\Rest\EO_Placement setGroupName(\string|\Bitrix\Main\DB\SqlExpression $groupName)
	 * @method bool hasGroupName()
	 * @method bool isGroupNameFilled()
	 * @method bool isGroupNameChanged()
	 * @method \string remindActualGroupName()
	 * @method \string requireGroupName()
	 * @method \Bitrix\Rest\EO_Placement resetGroupName()
	 * @method \Bitrix\Rest\EO_Placement unsetGroupName()
	 * @method \string fillGroupName()
	 * @method \int getIconId()
	 * @method \Bitrix\Rest\EO_Placement setIconId(\int|\Bitrix\Main\DB\SqlExpression $iconId)
	 * @method bool hasIconId()
	 * @method bool isIconIdFilled()
	 * @method bool isIconIdChanged()
	 * @method \int remindActualIconId()
	 * @method \int requireIconId()
	 * @method \Bitrix\Rest\EO_Placement resetIconId()
	 * @method \Bitrix\Rest\EO_Placement unsetIconId()
	 * @method \int fillIconId()
	 * @method \string getTitle()
	 * @method \Bitrix\Rest\EO_Placement setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Rest\EO_Placement resetTitle()
	 * @method \Bitrix\Rest\EO_Placement unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getComment()
	 * @method \Bitrix\Rest\EO_Placement setComment(\string|\Bitrix\Main\DB\SqlExpression $comment)
	 * @method bool hasComment()
	 * @method bool isCommentFilled()
	 * @method bool isCommentChanged()
	 * @method \string remindActualComment()
	 * @method \string requireComment()
	 * @method \Bitrix\Rest\EO_Placement resetComment()
	 * @method \Bitrix\Rest\EO_Placement unsetComment()
	 * @method \string fillComment()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Rest\EO_Placement setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Rest\EO_Placement resetDateCreate()
	 * @method \Bitrix\Rest\EO_Placement unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \string getAdditional()
	 * @method \Bitrix\Rest\EO_Placement setAdditional(\string|\Bitrix\Main\DB\SqlExpression $additional)
	 * @method bool hasAdditional()
	 * @method bool isAdditionalFilled()
	 * @method bool isAdditionalChanged()
	 * @method \string remindActualAdditional()
	 * @method \string requireAdditional()
	 * @method \Bitrix\Rest\EO_Placement resetAdditional()
	 * @method \Bitrix\Rest\EO_Placement unsetAdditional()
	 * @method \string fillAdditional()
	 * @method array getOptions()
	 * @method \Bitrix\Rest\EO_Placement setOptions(array|\Bitrix\Main\DB\SqlExpression $options)
	 * @method bool hasOptions()
	 * @method bool isOptionsFilled()
	 * @method bool isOptionsChanged()
	 * @method array remindActualOptions()
	 * @method array requireOptions()
	 * @method \Bitrix\Rest\EO_Placement resetOptions()
	 * @method \Bitrix\Rest\EO_Placement unsetOptions()
	 * @method array fillOptions()
	 * @method \Bitrix\Rest\EO_App getRestApp()
	 * @method \Bitrix\Rest\EO_App remindActualRestApp()
	 * @method \Bitrix\Rest\EO_App requireRestApp()
	 * @method \Bitrix\Rest\EO_Placement setRestApp(\Bitrix\Rest\EO_App $object)
	 * @method \Bitrix\Rest\EO_Placement resetRestApp()
	 * @method \Bitrix\Rest\EO_Placement unsetRestApp()
	 * @method bool hasRestApp()
	 * @method bool isRestAppFilled()
	 * @method bool isRestAppChanged()
	 * @method \Bitrix\Rest\EO_App fillRestApp()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection getLangAll()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection requireLangAll()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection fillLangAll()
	 * @method bool hasLangAll()
	 * @method bool isLangAllFilled()
	 * @method bool isLangAllChanged()
	 * @method void addToLangAll(\Bitrix\Rest\EO_PlacementLang $placementLang)
	 * @method void removeFromLangAll(\Bitrix\Rest\EO_PlacementLang $placementLang)
	 * @method void removeAllLangAll()
	 * @method \Bitrix\Rest\EO_Placement resetLangAll()
	 * @method \Bitrix\Rest\EO_Placement unsetLangAll()
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
	 * @method \Bitrix\Rest\EO_Placement set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_Placement reset($fieldName)
	 * @method \Bitrix\Rest\EO_Placement unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_Placement wakeUp($data)
	 */
	class EO_Placement {
		/* @var \Bitrix\Rest\PlacementTable */
		static public $dataClass = '\Bitrix\Rest\PlacementTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_Placement_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getAppIdList()
	 * @method \int[] fillAppId()
	 * @method \string[] getPlacementList()
	 * @method \string[] fillPlacement()
	 * @method \string[] getPlacementHandlerList()
	 * @method \string[] fillPlacementHandler()
	 * @method \string[] getGroupNameList()
	 * @method \string[] fillGroupName()
	 * @method \int[] getIconIdList()
	 * @method \int[] fillIconId()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getCommentList()
	 * @method \string[] fillComment()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \string[] getAdditionalList()
	 * @method \string[] fillAdditional()
	 * @method array[] getOptionsList()
	 * @method array[] fillOptions()
	 * @method \Bitrix\Rest\EO_App[] getRestAppList()
	 * @method \Bitrix\Rest\EO_Placement_Collection getRestAppCollection()
	 * @method \Bitrix\Rest\EO_App_Collection fillRestApp()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection[] getLangAllList()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection getLangAllCollection()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection fillLangAll()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_Placement $object)
	 * @method bool has(\Bitrix\Rest\EO_Placement $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_Placement getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_Placement[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_Placement $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_Placement_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_Placement current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Placement_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\PlacementTable */
		static public $dataClass = '\Bitrix\Rest\PlacementTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Placement_Result exec()
	 * @method \Bitrix\Rest\EO_Placement fetchObject()
	 * @method \Bitrix\Rest\EO_Placement_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Placement_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_Placement fetchObject()
	 * @method \Bitrix\Rest\EO_Placement_Collection fetchCollection()
	 */
	class EO_Placement_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_Placement createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_Placement_Collection createCollection()
	 * @method \Bitrix\Rest\EO_Placement wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_Placement_Collection wakeUpCollection($rows)
	 */
	class EO_Placement_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\PlacementLangTable:rest/lib/placementlang.php:67b2b69d7f0f8cdd376efa7860f11913 */
namespace Bitrix\Rest {
	/**
	 * EO_PlacementLang
	 * @see \Bitrix\Rest\PlacementLangTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_PlacementLang setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPlacementId()
	 * @method \Bitrix\Rest\EO_PlacementLang setPlacementId(\int|\Bitrix\Main\DB\SqlExpression $placementId)
	 * @method bool hasPlacementId()
	 * @method bool isPlacementIdFilled()
	 * @method bool isPlacementIdChanged()
	 * @method \int remindActualPlacementId()
	 * @method \int requirePlacementId()
	 * @method \Bitrix\Rest\EO_PlacementLang resetPlacementId()
	 * @method \Bitrix\Rest\EO_PlacementLang unsetPlacementId()
	 * @method \int fillPlacementId()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Rest\EO_PlacementLang setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\Rest\EO_PlacementLang resetLanguageId()
	 * @method \Bitrix\Rest\EO_PlacementLang unsetLanguageId()
	 * @method \string fillLanguageId()
	 * @method \string getTitle()
	 * @method \Bitrix\Rest\EO_PlacementLang setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Rest\EO_PlacementLang resetTitle()
	 * @method \Bitrix\Rest\EO_PlacementLang unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getDescription()
	 * @method \Bitrix\Rest\EO_PlacementLang setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Rest\EO_PlacementLang resetDescription()
	 * @method \Bitrix\Rest\EO_PlacementLang unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getGroupName()
	 * @method \Bitrix\Rest\EO_PlacementLang setGroupName(\string|\Bitrix\Main\DB\SqlExpression $groupName)
	 * @method bool hasGroupName()
	 * @method bool isGroupNameFilled()
	 * @method bool isGroupNameChanged()
	 * @method \string remindActualGroupName()
	 * @method \string requireGroupName()
	 * @method \Bitrix\Rest\EO_PlacementLang resetGroupName()
	 * @method \Bitrix\Rest\EO_PlacementLang unsetGroupName()
	 * @method \string fillGroupName()
	 * @method \Bitrix\Rest\EO_PlacementLang getPlacement()
	 * @method \Bitrix\Rest\EO_PlacementLang remindActualPlacement()
	 * @method \Bitrix\Rest\EO_PlacementLang requirePlacement()
	 * @method \Bitrix\Rest\EO_PlacementLang setPlacement(\Bitrix\Rest\EO_PlacementLang $object)
	 * @method \Bitrix\Rest\EO_PlacementLang resetPlacement()
	 * @method \Bitrix\Rest\EO_PlacementLang unsetPlacement()
	 * @method bool hasPlacement()
	 * @method bool isPlacementFilled()
	 * @method bool isPlacementChanged()
	 * @method \Bitrix\Rest\EO_PlacementLang fillPlacement()
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
	 * @method \Bitrix\Rest\EO_PlacementLang set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_PlacementLang reset($fieldName)
	 * @method \Bitrix\Rest\EO_PlacementLang unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_PlacementLang wakeUp($data)
	 */
	class EO_PlacementLang {
		/* @var \Bitrix\Rest\PlacementLangTable */
		static public $dataClass = '\Bitrix\Rest\PlacementLangTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_PlacementLang_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPlacementIdList()
	 * @method \int[] fillPlacementId()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getGroupNameList()
	 * @method \string[] fillGroupName()
	 * @method \Bitrix\Rest\EO_PlacementLang[] getPlacementList()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection getPlacementCollection()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection fillPlacement()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_PlacementLang $object)
	 * @method bool has(\Bitrix\Rest\EO_PlacementLang $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_PlacementLang getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_PlacementLang[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_PlacementLang $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_PlacementLang_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_PlacementLang current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PlacementLang_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\PlacementLangTable */
		static public $dataClass = '\Bitrix\Rest\PlacementLangTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PlacementLang_Result exec()
	 * @method \Bitrix\Rest\EO_PlacementLang fetchObject()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PlacementLang_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_PlacementLang fetchObject()
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection fetchCollection()
	 */
	class EO_PlacementLang_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_PlacementLang createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection createCollection()
	 * @method \Bitrix\Rest\EO_PlacementLang wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_PlacementLang_Collection wakeUpCollection($rows)
	 */
	class EO_PlacementLang_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\Preset\IntegrationTable:rest/lib/preset/integration.php:a89e67980b3f3f0446acf47020f020a1 */
namespace Bitrix\Rest\Preset {
	/**
	 * EO_Integration
	 * @see \Bitrix\Rest\Preset\IntegrationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\Preset\EO_Integration setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Rest\Preset\EO_Integration setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetUserId()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getElementCode()
	 * @method \Bitrix\Rest\Preset\EO_Integration setElementCode(\string|\Bitrix\Main\DB\SqlExpression $elementCode)
	 * @method bool hasElementCode()
	 * @method bool isElementCodeFilled()
	 * @method bool isElementCodeChanged()
	 * @method \string remindActualElementCode()
	 * @method \string requireElementCode()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetElementCode()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetElementCode()
	 * @method \string fillElementCode()
	 * @method \string getTitle()
	 * @method \Bitrix\Rest\Preset\EO_Integration setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetTitle()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetTitle()
	 * @method \string fillTitle()
	 * @method \int getPasswordId()
	 * @method \Bitrix\Rest\Preset\EO_Integration setPasswordId(\int|\Bitrix\Main\DB\SqlExpression $passwordId)
	 * @method bool hasPasswordId()
	 * @method bool isPasswordIdFilled()
	 * @method bool isPasswordIdChanged()
	 * @method \int remindActualPasswordId()
	 * @method \int requirePasswordId()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetPasswordId()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetPasswordId()
	 * @method \int fillPasswordId()
	 * @method \int getAppId()
	 * @method \Bitrix\Rest\Preset\EO_Integration setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int remindActualAppId()
	 * @method \int requireAppId()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetAppId()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetAppId()
	 * @method \int fillAppId()
	 * @method \string getScope()
	 * @method \Bitrix\Rest\Preset\EO_Integration setScope(\string|\Bitrix\Main\DB\SqlExpression $scope)
	 * @method bool hasScope()
	 * @method bool isScopeFilled()
	 * @method bool isScopeChanged()
	 * @method \string remindActualScope()
	 * @method \string requireScope()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetScope()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetScope()
	 * @method \string fillScope()
	 * @method \string getQuery()
	 * @method \Bitrix\Rest\Preset\EO_Integration setQuery(\string|\Bitrix\Main\DB\SqlExpression $query)
	 * @method bool hasQuery()
	 * @method bool isQueryFilled()
	 * @method bool isQueryChanged()
	 * @method \string remindActualQuery()
	 * @method \string requireQuery()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetQuery()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetQuery()
	 * @method \string fillQuery()
	 * @method \string getOutgoingEvents()
	 * @method \Bitrix\Rest\Preset\EO_Integration setOutgoingEvents(\string|\Bitrix\Main\DB\SqlExpression $outgoingEvents)
	 * @method bool hasOutgoingEvents()
	 * @method bool isOutgoingEventsFilled()
	 * @method bool isOutgoingEventsChanged()
	 * @method \string remindActualOutgoingEvents()
	 * @method \string requireOutgoingEvents()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetOutgoingEvents()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetOutgoingEvents()
	 * @method \string fillOutgoingEvents()
	 * @method \string getOutgoingNeeded()
	 * @method \Bitrix\Rest\Preset\EO_Integration setOutgoingNeeded(\string|\Bitrix\Main\DB\SqlExpression $outgoingNeeded)
	 * @method bool hasOutgoingNeeded()
	 * @method bool isOutgoingNeededFilled()
	 * @method bool isOutgoingNeededChanged()
	 * @method \string remindActualOutgoingNeeded()
	 * @method \string requireOutgoingNeeded()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetOutgoingNeeded()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetOutgoingNeeded()
	 * @method \string fillOutgoingNeeded()
	 * @method \string getOutgoingHandlerUrl()
	 * @method \Bitrix\Rest\Preset\EO_Integration setOutgoingHandlerUrl(\string|\Bitrix\Main\DB\SqlExpression $outgoingHandlerUrl)
	 * @method bool hasOutgoingHandlerUrl()
	 * @method bool isOutgoingHandlerUrlFilled()
	 * @method bool isOutgoingHandlerUrlChanged()
	 * @method \string remindActualOutgoingHandlerUrl()
	 * @method \string requireOutgoingHandlerUrl()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetOutgoingHandlerUrl()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetOutgoingHandlerUrl()
	 * @method \string fillOutgoingHandlerUrl()
	 * @method \string getWidgetNeeded()
	 * @method \Bitrix\Rest\Preset\EO_Integration setWidgetNeeded(\string|\Bitrix\Main\DB\SqlExpression $widgetNeeded)
	 * @method bool hasWidgetNeeded()
	 * @method bool isWidgetNeededFilled()
	 * @method bool isWidgetNeededChanged()
	 * @method \string remindActualWidgetNeeded()
	 * @method \string requireWidgetNeeded()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetWidgetNeeded()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetWidgetNeeded()
	 * @method \string fillWidgetNeeded()
	 * @method \string getWidgetHandlerUrl()
	 * @method \Bitrix\Rest\Preset\EO_Integration setWidgetHandlerUrl(\string|\Bitrix\Main\DB\SqlExpression $widgetHandlerUrl)
	 * @method bool hasWidgetHandlerUrl()
	 * @method bool isWidgetHandlerUrlFilled()
	 * @method bool isWidgetHandlerUrlChanged()
	 * @method \string remindActualWidgetHandlerUrl()
	 * @method \string requireWidgetHandlerUrl()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetWidgetHandlerUrl()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetWidgetHandlerUrl()
	 * @method \string fillWidgetHandlerUrl()
	 * @method \string getWidgetList()
	 * @method \Bitrix\Rest\Preset\EO_Integration setWidgetList(\string|\Bitrix\Main\DB\SqlExpression $widgetList)
	 * @method bool hasWidgetList()
	 * @method bool isWidgetListFilled()
	 * @method bool isWidgetListChanged()
	 * @method \string remindActualWidgetList()
	 * @method \string requireWidgetList()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetWidgetList()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetWidgetList()
	 * @method \string fillWidgetList()
	 * @method \string getApplicationToken()
	 * @method \Bitrix\Rest\Preset\EO_Integration setApplicationToken(\string|\Bitrix\Main\DB\SqlExpression $applicationToken)
	 * @method bool hasApplicationToken()
	 * @method bool isApplicationTokenFilled()
	 * @method bool isApplicationTokenChanged()
	 * @method \string remindActualApplicationToken()
	 * @method \string requireApplicationToken()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetApplicationToken()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetApplicationToken()
	 * @method \string fillApplicationToken()
	 * @method \string getApplicationNeeded()
	 * @method \Bitrix\Rest\Preset\EO_Integration setApplicationNeeded(\string|\Bitrix\Main\DB\SqlExpression $applicationNeeded)
	 * @method bool hasApplicationNeeded()
	 * @method bool isApplicationNeededFilled()
	 * @method bool isApplicationNeededChanged()
	 * @method \string remindActualApplicationNeeded()
	 * @method \string requireApplicationNeeded()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetApplicationNeeded()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetApplicationNeeded()
	 * @method \string fillApplicationNeeded()
	 * @method \string getApplicationOnlyApi()
	 * @method \Bitrix\Rest\Preset\EO_Integration setApplicationOnlyApi(\string|\Bitrix\Main\DB\SqlExpression $applicationOnlyApi)
	 * @method bool hasApplicationOnlyApi()
	 * @method bool isApplicationOnlyApiFilled()
	 * @method bool isApplicationOnlyApiChanged()
	 * @method \string remindActualApplicationOnlyApi()
	 * @method \string requireApplicationOnlyApi()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetApplicationOnlyApi()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetApplicationOnlyApi()
	 * @method \string fillApplicationOnlyApi()
	 * @method \int getBotId()
	 * @method \Bitrix\Rest\Preset\EO_Integration setBotId(\int|\Bitrix\Main\DB\SqlExpression $botId)
	 * @method bool hasBotId()
	 * @method bool isBotIdFilled()
	 * @method bool isBotIdChanged()
	 * @method \int remindActualBotId()
	 * @method \int requireBotId()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetBotId()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetBotId()
	 * @method \int fillBotId()
	 * @method \string getBotHandlerUrl()
	 * @method \Bitrix\Rest\Preset\EO_Integration setBotHandlerUrl(\string|\Bitrix\Main\DB\SqlExpression $botHandlerUrl)
	 * @method bool hasBotHandlerUrl()
	 * @method bool isBotHandlerUrlFilled()
	 * @method bool isBotHandlerUrlChanged()
	 * @method \string remindActualBotHandlerUrl()
	 * @method \string requireBotHandlerUrl()
	 * @method \Bitrix\Rest\Preset\EO_Integration resetBotHandlerUrl()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetBotHandlerUrl()
	 * @method \string fillBotHandlerUrl()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Rest\Preset\EO_Integration setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Rest\Preset\EO_Integration resetUser()
	 * @method \Bitrix\Rest\Preset\EO_Integration unsetUser()
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
	 * @method \Bitrix\Rest\Preset\EO_Integration set($fieldName, $value)
	 * @method \Bitrix\Rest\Preset\EO_Integration reset($fieldName)
	 * @method \Bitrix\Rest\Preset\EO_Integration unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\Preset\EO_Integration wakeUp($data)
	 */
	class EO_Integration {
		/* @var \Bitrix\Rest\Preset\IntegrationTable */
		static public $dataClass = '\Bitrix\Rest\Preset\IntegrationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest\Preset {
	/**
	 * EO_Integration_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getElementCodeList()
	 * @method \string[] fillElementCode()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \int[] getPasswordIdList()
	 * @method \int[] fillPasswordId()
	 * @method \int[] getAppIdList()
	 * @method \int[] fillAppId()
	 * @method \string[] getScopeList()
	 * @method \string[] fillScope()
	 * @method \string[] getQueryList()
	 * @method \string[] fillQuery()
	 * @method \string[] getOutgoingEventsList()
	 * @method \string[] fillOutgoingEvents()
	 * @method \string[] getOutgoingNeededList()
	 * @method \string[] fillOutgoingNeeded()
	 * @method \string[] getOutgoingHandlerUrlList()
	 * @method \string[] fillOutgoingHandlerUrl()
	 * @method \string[] getWidgetNeededList()
	 * @method \string[] fillWidgetNeeded()
	 * @method \string[] getWidgetHandlerUrlList()
	 * @method \string[] fillWidgetHandlerUrl()
	 * @method \string[] getWidgetListList()
	 * @method \string[] fillWidgetList()
	 * @method \string[] getApplicationTokenList()
	 * @method \string[] fillApplicationToken()
	 * @method \string[] getApplicationNeededList()
	 * @method \string[] fillApplicationNeeded()
	 * @method \string[] getApplicationOnlyApiList()
	 * @method \string[] fillApplicationOnlyApi()
	 * @method \int[] getBotIdList()
	 * @method \int[] fillBotId()
	 * @method \string[] getBotHandlerUrlList()
	 * @method \string[] fillBotHandlerUrl()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Rest\Preset\EO_Integration_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\Preset\EO_Integration $object)
	 * @method bool has(\Bitrix\Rest\Preset\EO_Integration $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\Preset\EO_Integration getByPrimary($primary)
	 * @method \Bitrix\Rest\Preset\EO_Integration[] getAll()
	 * @method bool remove(\Bitrix\Rest\Preset\EO_Integration $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\Preset\EO_Integration_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\Preset\EO_Integration current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Integration_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\Preset\IntegrationTable */
		static public $dataClass = '\Bitrix\Rest\Preset\IntegrationTable';
	}
}
namespace Bitrix\Rest\Preset {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Integration_Result exec()
	 * @method \Bitrix\Rest\Preset\EO_Integration fetchObject()
	 * @method \Bitrix\Rest\Preset\EO_Integration_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Integration_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\Preset\EO_Integration fetchObject()
	 * @method \Bitrix\Rest\Preset\EO_Integration_Collection fetchCollection()
	 */
	class EO_Integration_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\Preset\EO_Integration createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\Preset\EO_Integration_Collection createCollection()
	 * @method \Bitrix\Rest\Preset\EO_Integration wakeUpObject($row)
	 * @method \Bitrix\Rest\Preset\EO_Integration_Collection wakeUpCollection($rows)
	 */
	class EO_Integration_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\StatTable:rest/lib/stat.php:61113ea2a4e5e57f06bd39ad44621ea3 */
namespace Bitrix\Rest {
	/**
	 * EO_Stat
	 * @see \Bitrix\Rest\StatTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date getStatDate()
	 * @method \Bitrix\Rest\EO_Stat setStatDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $statDate)
	 * @method bool hasStatDate()
	 * @method bool isStatDateFilled()
	 * @method bool isStatDateChanged()
	 * @method \int getAppId()
	 * @method \Bitrix\Rest\EO_Stat setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \int getMethodId()
	 * @method \Bitrix\Rest\EO_Stat setMethodId(\int|\Bitrix\Main\DB\SqlExpression $methodId)
	 * @method bool hasMethodId()
	 * @method bool isMethodIdFilled()
	 * @method bool isMethodIdChanged()
	 * @method \int getPasswordId()
	 * @method \Bitrix\Rest\EO_Stat setPasswordId(\int|\Bitrix\Main\DB\SqlExpression $passwordId)
	 * @method bool hasPasswordId()
	 * @method bool isPasswordIdFilled()
	 * @method bool isPasswordIdChanged()
	 * @method \int getHour0()
	 * @method \Bitrix\Rest\EO_Stat setHour0(\int|\Bitrix\Main\DB\SqlExpression $hour0)
	 * @method bool hasHour0()
	 * @method bool isHour0Filled()
	 * @method bool isHour0Changed()
	 * @method \int remindActualHour0()
	 * @method \int requireHour0()
	 * @method \Bitrix\Rest\EO_Stat resetHour0()
	 * @method \Bitrix\Rest\EO_Stat unsetHour0()
	 * @method \int fillHour0()
	 * @method \int getHour1()
	 * @method \Bitrix\Rest\EO_Stat setHour1(\int|\Bitrix\Main\DB\SqlExpression $hour1)
	 * @method bool hasHour1()
	 * @method bool isHour1Filled()
	 * @method bool isHour1Changed()
	 * @method \int remindActualHour1()
	 * @method \int requireHour1()
	 * @method \Bitrix\Rest\EO_Stat resetHour1()
	 * @method \Bitrix\Rest\EO_Stat unsetHour1()
	 * @method \int fillHour1()
	 * @method \int getHour2()
	 * @method \Bitrix\Rest\EO_Stat setHour2(\int|\Bitrix\Main\DB\SqlExpression $hour2)
	 * @method bool hasHour2()
	 * @method bool isHour2Filled()
	 * @method bool isHour2Changed()
	 * @method \int remindActualHour2()
	 * @method \int requireHour2()
	 * @method \Bitrix\Rest\EO_Stat resetHour2()
	 * @method \Bitrix\Rest\EO_Stat unsetHour2()
	 * @method \int fillHour2()
	 * @method \int getHour3()
	 * @method \Bitrix\Rest\EO_Stat setHour3(\int|\Bitrix\Main\DB\SqlExpression $hour3)
	 * @method bool hasHour3()
	 * @method bool isHour3Filled()
	 * @method bool isHour3Changed()
	 * @method \int remindActualHour3()
	 * @method \int requireHour3()
	 * @method \Bitrix\Rest\EO_Stat resetHour3()
	 * @method \Bitrix\Rest\EO_Stat unsetHour3()
	 * @method \int fillHour3()
	 * @method \int getHour4()
	 * @method \Bitrix\Rest\EO_Stat setHour4(\int|\Bitrix\Main\DB\SqlExpression $hour4)
	 * @method bool hasHour4()
	 * @method bool isHour4Filled()
	 * @method bool isHour4Changed()
	 * @method \int remindActualHour4()
	 * @method \int requireHour4()
	 * @method \Bitrix\Rest\EO_Stat resetHour4()
	 * @method \Bitrix\Rest\EO_Stat unsetHour4()
	 * @method \int fillHour4()
	 * @method \int getHour5()
	 * @method \Bitrix\Rest\EO_Stat setHour5(\int|\Bitrix\Main\DB\SqlExpression $hour5)
	 * @method bool hasHour5()
	 * @method bool isHour5Filled()
	 * @method bool isHour5Changed()
	 * @method \int remindActualHour5()
	 * @method \int requireHour5()
	 * @method \Bitrix\Rest\EO_Stat resetHour5()
	 * @method \Bitrix\Rest\EO_Stat unsetHour5()
	 * @method \int fillHour5()
	 * @method \int getHour6()
	 * @method \Bitrix\Rest\EO_Stat setHour6(\int|\Bitrix\Main\DB\SqlExpression $hour6)
	 * @method bool hasHour6()
	 * @method bool isHour6Filled()
	 * @method bool isHour6Changed()
	 * @method \int remindActualHour6()
	 * @method \int requireHour6()
	 * @method \Bitrix\Rest\EO_Stat resetHour6()
	 * @method \Bitrix\Rest\EO_Stat unsetHour6()
	 * @method \int fillHour6()
	 * @method \int getHour7()
	 * @method \Bitrix\Rest\EO_Stat setHour7(\int|\Bitrix\Main\DB\SqlExpression $hour7)
	 * @method bool hasHour7()
	 * @method bool isHour7Filled()
	 * @method bool isHour7Changed()
	 * @method \int remindActualHour7()
	 * @method \int requireHour7()
	 * @method \Bitrix\Rest\EO_Stat resetHour7()
	 * @method \Bitrix\Rest\EO_Stat unsetHour7()
	 * @method \int fillHour7()
	 * @method \int getHour8()
	 * @method \Bitrix\Rest\EO_Stat setHour8(\int|\Bitrix\Main\DB\SqlExpression $hour8)
	 * @method bool hasHour8()
	 * @method bool isHour8Filled()
	 * @method bool isHour8Changed()
	 * @method \int remindActualHour8()
	 * @method \int requireHour8()
	 * @method \Bitrix\Rest\EO_Stat resetHour8()
	 * @method \Bitrix\Rest\EO_Stat unsetHour8()
	 * @method \int fillHour8()
	 * @method \int getHour9()
	 * @method \Bitrix\Rest\EO_Stat setHour9(\int|\Bitrix\Main\DB\SqlExpression $hour9)
	 * @method bool hasHour9()
	 * @method bool isHour9Filled()
	 * @method bool isHour9Changed()
	 * @method \int remindActualHour9()
	 * @method \int requireHour9()
	 * @method \Bitrix\Rest\EO_Stat resetHour9()
	 * @method \Bitrix\Rest\EO_Stat unsetHour9()
	 * @method \int fillHour9()
	 * @method \int getHour10()
	 * @method \Bitrix\Rest\EO_Stat setHour10(\int|\Bitrix\Main\DB\SqlExpression $hour10)
	 * @method bool hasHour10()
	 * @method bool isHour10Filled()
	 * @method bool isHour10Changed()
	 * @method \int remindActualHour10()
	 * @method \int requireHour10()
	 * @method \Bitrix\Rest\EO_Stat resetHour10()
	 * @method \Bitrix\Rest\EO_Stat unsetHour10()
	 * @method \int fillHour10()
	 * @method \int getHour11()
	 * @method \Bitrix\Rest\EO_Stat setHour11(\int|\Bitrix\Main\DB\SqlExpression $hour11)
	 * @method bool hasHour11()
	 * @method bool isHour11Filled()
	 * @method bool isHour11Changed()
	 * @method \int remindActualHour11()
	 * @method \int requireHour11()
	 * @method \Bitrix\Rest\EO_Stat resetHour11()
	 * @method \Bitrix\Rest\EO_Stat unsetHour11()
	 * @method \int fillHour11()
	 * @method \int getHour12()
	 * @method \Bitrix\Rest\EO_Stat setHour12(\int|\Bitrix\Main\DB\SqlExpression $hour12)
	 * @method bool hasHour12()
	 * @method bool isHour12Filled()
	 * @method bool isHour12Changed()
	 * @method \int remindActualHour12()
	 * @method \int requireHour12()
	 * @method \Bitrix\Rest\EO_Stat resetHour12()
	 * @method \Bitrix\Rest\EO_Stat unsetHour12()
	 * @method \int fillHour12()
	 * @method \int getHour13()
	 * @method \Bitrix\Rest\EO_Stat setHour13(\int|\Bitrix\Main\DB\SqlExpression $hour13)
	 * @method bool hasHour13()
	 * @method bool isHour13Filled()
	 * @method bool isHour13Changed()
	 * @method \int remindActualHour13()
	 * @method \int requireHour13()
	 * @method \Bitrix\Rest\EO_Stat resetHour13()
	 * @method \Bitrix\Rest\EO_Stat unsetHour13()
	 * @method \int fillHour13()
	 * @method \int getHour14()
	 * @method \Bitrix\Rest\EO_Stat setHour14(\int|\Bitrix\Main\DB\SqlExpression $hour14)
	 * @method bool hasHour14()
	 * @method bool isHour14Filled()
	 * @method bool isHour14Changed()
	 * @method \int remindActualHour14()
	 * @method \int requireHour14()
	 * @method \Bitrix\Rest\EO_Stat resetHour14()
	 * @method \Bitrix\Rest\EO_Stat unsetHour14()
	 * @method \int fillHour14()
	 * @method \int getHour15()
	 * @method \Bitrix\Rest\EO_Stat setHour15(\int|\Bitrix\Main\DB\SqlExpression $hour15)
	 * @method bool hasHour15()
	 * @method bool isHour15Filled()
	 * @method bool isHour15Changed()
	 * @method \int remindActualHour15()
	 * @method \int requireHour15()
	 * @method \Bitrix\Rest\EO_Stat resetHour15()
	 * @method \Bitrix\Rest\EO_Stat unsetHour15()
	 * @method \int fillHour15()
	 * @method \int getHour16()
	 * @method \Bitrix\Rest\EO_Stat setHour16(\int|\Bitrix\Main\DB\SqlExpression $hour16)
	 * @method bool hasHour16()
	 * @method bool isHour16Filled()
	 * @method bool isHour16Changed()
	 * @method \int remindActualHour16()
	 * @method \int requireHour16()
	 * @method \Bitrix\Rest\EO_Stat resetHour16()
	 * @method \Bitrix\Rest\EO_Stat unsetHour16()
	 * @method \int fillHour16()
	 * @method \int getHour17()
	 * @method \Bitrix\Rest\EO_Stat setHour17(\int|\Bitrix\Main\DB\SqlExpression $hour17)
	 * @method bool hasHour17()
	 * @method bool isHour17Filled()
	 * @method bool isHour17Changed()
	 * @method \int remindActualHour17()
	 * @method \int requireHour17()
	 * @method \Bitrix\Rest\EO_Stat resetHour17()
	 * @method \Bitrix\Rest\EO_Stat unsetHour17()
	 * @method \int fillHour17()
	 * @method \int getHour18()
	 * @method \Bitrix\Rest\EO_Stat setHour18(\int|\Bitrix\Main\DB\SqlExpression $hour18)
	 * @method bool hasHour18()
	 * @method bool isHour18Filled()
	 * @method bool isHour18Changed()
	 * @method \int remindActualHour18()
	 * @method \int requireHour18()
	 * @method \Bitrix\Rest\EO_Stat resetHour18()
	 * @method \Bitrix\Rest\EO_Stat unsetHour18()
	 * @method \int fillHour18()
	 * @method \int getHour19()
	 * @method \Bitrix\Rest\EO_Stat setHour19(\int|\Bitrix\Main\DB\SqlExpression $hour19)
	 * @method bool hasHour19()
	 * @method bool isHour19Filled()
	 * @method bool isHour19Changed()
	 * @method \int remindActualHour19()
	 * @method \int requireHour19()
	 * @method \Bitrix\Rest\EO_Stat resetHour19()
	 * @method \Bitrix\Rest\EO_Stat unsetHour19()
	 * @method \int fillHour19()
	 * @method \int getHour20()
	 * @method \Bitrix\Rest\EO_Stat setHour20(\int|\Bitrix\Main\DB\SqlExpression $hour20)
	 * @method bool hasHour20()
	 * @method bool isHour20Filled()
	 * @method bool isHour20Changed()
	 * @method \int remindActualHour20()
	 * @method \int requireHour20()
	 * @method \Bitrix\Rest\EO_Stat resetHour20()
	 * @method \Bitrix\Rest\EO_Stat unsetHour20()
	 * @method \int fillHour20()
	 * @method \int getHour21()
	 * @method \Bitrix\Rest\EO_Stat setHour21(\int|\Bitrix\Main\DB\SqlExpression $hour21)
	 * @method bool hasHour21()
	 * @method bool isHour21Filled()
	 * @method bool isHour21Changed()
	 * @method \int remindActualHour21()
	 * @method \int requireHour21()
	 * @method \Bitrix\Rest\EO_Stat resetHour21()
	 * @method \Bitrix\Rest\EO_Stat unsetHour21()
	 * @method \int fillHour21()
	 * @method \int getHour22()
	 * @method \Bitrix\Rest\EO_Stat setHour22(\int|\Bitrix\Main\DB\SqlExpression $hour22)
	 * @method bool hasHour22()
	 * @method bool isHour22Filled()
	 * @method bool isHour22Changed()
	 * @method \int remindActualHour22()
	 * @method \int requireHour22()
	 * @method \Bitrix\Rest\EO_Stat resetHour22()
	 * @method \Bitrix\Rest\EO_Stat unsetHour22()
	 * @method \int fillHour22()
	 * @method \int getHour23()
	 * @method \Bitrix\Rest\EO_Stat setHour23(\int|\Bitrix\Main\DB\SqlExpression $hour23)
	 * @method bool hasHour23()
	 * @method bool isHour23Filled()
	 * @method bool isHour23Changed()
	 * @method \int remindActualHour23()
	 * @method \int requireHour23()
	 * @method \Bitrix\Rest\EO_Stat resetHour23()
	 * @method \Bitrix\Rest\EO_Stat unsetHour23()
	 * @method \int fillHour23()
	 * @method \Bitrix\Rest\EO_StatApp getApp()
	 * @method \Bitrix\Rest\EO_StatApp remindActualApp()
	 * @method \Bitrix\Rest\EO_StatApp requireApp()
	 * @method \Bitrix\Rest\EO_Stat setApp(\Bitrix\Rest\EO_StatApp $object)
	 * @method \Bitrix\Rest\EO_Stat resetApp()
	 * @method \Bitrix\Rest\EO_Stat unsetApp()
	 * @method bool hasApp()
	 * @method bool isAppFilled()
	 * @method bool isAppChanged()
	 * @method \Bitrix\Rest\EO_StatApp fillApp()
	 * @method \Bitrix\Rest\EO_StatMethod getMethod()
	 * @method \Bitrix\Rest\EO_StatMethod remindActualMethod()
	 * @method \Bitrix\Rest\EO_StatMethod requireMethod()
	 * @method \Bitrix\Rest\EO_Stat setMethod(\Bitrix\Rest\EO_StatMethod $object)
	 * @method \Bitrix\Rest\EO_Stat resetMethod()
	 * @method \Bitrix\Rest\EO_Stat unsetMethod()
	 * @method bool hasMethod()
	 * @method bool isMethodFilled()
	 * @method bool isMethodChanged()
	 * @method \Bitrix\Rest\EO_StatMethod fillMethod()
	 * @method \Bitrix\Rest\APAuth\EO_Password getPassword()
	 * @method \Bitrix\Rest\APAuth\EO_Password remindActualPassword()
	 * @method \Bitrix\Rest\APAuth\EO_Password requirePassword()
	 * @method \Bitrix\Rest\EO_Stat setPassword(\Bitrix\Rest\APAuth\EO_Password $object)
	 * @method \Bitrix\Rest\EO_Stat resetPassword()
	 * @method \Bitrix\Rest\EO_Stat unsetPassword()
	 * @method bool hasPassword()
	 * @method bool isPasswordFilled()
	 * @method bool isPasswordChanged()
	 * @method \Bitrix\Rest\APAuth\EO_Password fillPassword()
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
	 * @method \Bitrix\Rest\EO_Stat set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_Stat reset($fieldName)
	 * @method \Bitrix\Rest\EO_Stat unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_Stat wakeUp($data)
	 */
	class EO_Stat {
		/* @var \Bitrix\Rest\StatTable */
		static public $dataClass = '\Bitrix\Rest\StatTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_Stat_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date[] getStatDateList()
	 * @method \int[] getAppIdList()
	 * @method \int[] getMethodIdList()
	 * @method \int[] getPasswordIdList()
	 * @method \int[] getHour0List()
	 * @method \int[] fillHour0()
	 * @method \int[] getHour1List()
	 * @method \int[] fillHour1()
	 * @method \int[] getHour2List()
	 * @method \int[] fillHour2()
	 * @method \int[] getHour3List()
	 * @method \int[] fillHour3()
	 * @method \int[] getHour4List()
	 * @method \int[] fillHour4()
	 * @method \int[] getHour5List()
	 * @method \int[] fillHour5()
	 * @method \int[] getHour6List()
	 * @method \int[] fillHour6()
	 * @method \int[] getHour7List()
	 * @method \int[] fillHour7()
	 * @method \int[] getHour8List()
	 * @method \int[] fillHour8()
	 * @method \int[] getHour9List()
	 * @method \int[] fillHour9()
	 * @method \int[] getHour10List()
	 * @method \int[] fillHour10()
	 * @method \int[] getHour11List()
	 * @method \int[] fillHour11()
	 * @method \int[] getHour12List()
	 * @method \int[] fillHour12()
	 * @method \int[] getHour13List()
	 * @method \int[] fillHour13()
	 * @method \int[] getHour14List()
	 * @method \int[] fillHour14()
	 * @method \int[] getHour15List()
	 * @method \int[] fillHour15()
	 * @method \int[] getHour16List()
	 * @method \int[] fillHour16()
	 * @method \int[] getHour17List()
	 * @method \int[] fillHour17()
	 * @method \int[] getHour18List()
	 * @method \int[] fillHour18()
	 * @method \int[] getHour19List()
	 * @method \int[] fillHour19()
	 * @method \int[] getHour20List()
	 * @method \int[] fillHour20()
	 * @method \int[] getHour21List()
	 * @method \int[] fillHour21()
	 * @method \int[] getHour22List()
	 * @method \int[] fillHour22()
	 * @method \int[] getHour23List()
	 * @method \int[] fillHour23()
	 * @method \Bitrix\Rest\EO_StatApp[] getAppList()
	 * @method \Bitrix\Rest\EO_Stat_Collection getAppCollection()
	 * @method \Bitrix\Rest\EO_StatApp_Collection fillApp()
	 * @method \Bitrix\Rest\EO_StatMethod[] getMethodList()
	 * @method \Bitrix\Rest\EO_Stat_Collection getMethodCollection()
	 * @method \Bitrix\Rest\EO_StatMethod_Collection fillMethod()
	 * @method \Bitrix\Rest\APAuth\EO_Password[] getPasswordList()
	 * @method \Bitrix\Rest\EO_Stat_Collection getPasswordCollection()
	 * @method \Bitrix\Rest\APAuth\EO_Password_Collection fillPassword()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_Stat $object)
	 * @method bool has(\Bitrix\Rest\EO_Stat $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_Stat getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_Stat[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_Stat $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_Stat_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_Stat current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Stat_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\StatTable */
		static public $dataClass = '\Bitrix\Rest\StatTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Stat_Result exec()
	 * @method \Bitrix\Rest\EO_Stat fetchObject()
	 * @method \Bitrix\Rest\EO_Stat_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Stat_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_Stat fetchObject()
	 * @method \Bitrix\Rest\EO_Stat_Collection fetchCollection()
	 */
	class EO_Stat_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_Stat createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_Stat_Collection createCollection()
	 * @method \Bitrix\Rest\EO_Stat wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_Stat_Collection wakeUpCollection($rows)
	 */
	class EO_Stat_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\StatAppTable:rest/lib/statapp.php:41234c505254a7241db55dd60a681a35 */
namespace Bitrix\Rest {
	/**
	 * EO_StatApp
	 * @see \Bitrix\Rest\StatAppTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getAppId()
	 * @method \Bitrix\Rest\EO_StatApp setAppId(\int|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \string getAppCode()
	 * @method \Bitrix\Rest\EO_StatApp setAppCode(\string|\Bitrix\Main\DB\SqlExpression $appCode)
	 * @method bool hasAppCode()
	 * @method bool isAppCodeFilled()
	 * @method bool isAppCodeChanged()
	 * @method \string remindActualAppCode()
	 * @method \string requireAppCode()
	 * @method \Bitrix\Rest\EO_StatApp resetAppCode()
	 * @method \Bitrix\Rest\EO_StatApp unsetAppCode()
	 * @method \string fillAppCode()
	 * @method \Bitrix\Rest\EO_App getApp()
	 * @method \Bitrix\Rest\EO_App remindActualApp()
	 * @method \Bitrix\Rest\EO_App requireApp()
	 * @method \Bitrix\Rest\EO_StatApp setApp(\Bitrix\Rest\EO_App $object)
	 * @method \Bitrix\Rest\EO_StatApp resetApp()
	 * @method \Bitrix\Rest\EO_StatApp unsetApp()
	 * @method bool hasApp()
	 * @method bool isAppFilled()
	 * @method bool isAppChanged()
	 * @method \Bitrix\Rest\EO_App fillApp()
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
	 * @method \Bitrix\Rest\EO_StatApp set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_StatApp reset($fieldName)
	 * @method \Bitrix\Rest\EO_StatApp unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_StatApp wakeUp($data)
	 */
	class EO_StatApp {
		/* @var \Bitrix\Rest\StatAppTable */
		static public $dataClass = '\Bitrix\Rest\StatAppTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_StatApp_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getAppIdList()
	 * @method \string[] getAppCodeList()
	 * @method \string[] fillAppCode()
	 * @method \Bitrix\Rest\EO_App[] getAppList()
	 * @method \Bitrix\Rest\EO_StatApp_Collection getAppCollection()
	 * @method \Bitrix\Rest\EO_App_Collection fillApp()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_StatApp $object)
	 * @method bool has(\Bitrix\Rest\EO_StatApp $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_StatApp getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_StatApp[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_StatApp $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_StatApp_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_StatApp current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_StatApp_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\StatAppTable */
		static public $dataClass = '\Bitrix\Rest\StatAppTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_StatApp_Result exec()
	 * @method \Bitrix\Rest\EO_StatApp fetchObject()
	 * @method \Bitrix\Rest\EO_StatApp_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_StatApp_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_StatApp fetchObject()
	 * @method \Bitrix\Rest\EO_StatApp_Collection fetchCollection()
	 */
	class EO_StatApp_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_StatApp createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_StatApp_Collection createCollection()
	 * @method \Bitrix\Rest\EO_StatApp wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_StatApp_Collection wakeUpCollection($rows)
	 */
	class EO_StatApp_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\StatMethodTable:rest/lib/statmethod.php:766ea20111adb0e19082ef667b1ac023 */
namespace Bitrix\Rest {
	/**
	 * EO_StatMethod
	 * @see \Bitrix\Rest\StatMethodTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_StatMethod setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Rest\EO_StatMethod setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Rest\EO_StatMethod resetName()
	 * @method \Bitrix\Rest\EO_StatMethod unsetName()
	 * @method \string fillName()
	 * @method \string getMethodType()
	 * @method \Bitrix\Rest\EO_StatMethod setMethodType(\string|\Bitrix\Main\DB\SqlExpression $methodType)
	 * @method bool hasMethodType()
	 * @method bool isMethodTypeFilled()
	 * @method bool isMethodTypeChanged()
	 * @method \string remindActualMethodType()
	 * @method \string requireMethodType()
	 * @method \Bitrix\Rest\EO_StatMethod resetMethodType()
	 * @method \Bitrix\Rest\EO_StatMethod unsetMethodType()
	 * @method \string fillMethodType()
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
	 * @method \Bitrix\Rest\EO_StatMethod set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_StatMethod reset($fieldName)
	 * @method \Bitrix\Rest\EO_StatMethod unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_StatMethod wakeUp($data)
	 */
	class EO_StatMethod {
		/* @var \Bitrix\Rest\StatMethodTable */
		static public $dataClass = '\Bitrix\Rest\StatMethodTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_StatMethod_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getMethodTypeList()
	 * @method \string[] fillMethodType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_StatMethod $object)
	 * @method bool has(\Bitrix\Rest\EO_StatMethod $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_StatMethod getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_StatMethod[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_StatMethod $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_StatMethod_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_StatMethod current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_StatMethod_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\StatMethodTable */
		static public $dataClass = '\Bitrix\Rest\StatMethodTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_StatMethod_Result exec()
	 * @method \Bitrix\Rest\EO_StatMethod fetchObject()
	 * @method \Bitrix\Rest\EO_StatMethod_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_StatMethod_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_StatMethod fetchObject()
	 * @method \Bitrix\Rest\EO_StatMethod_Collection fetchCollection()
	 */
	class EO_StatMethod_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_StatMethod createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_StatMethod_Collection createCollection()
	 * @method \Bitrix\Rest\EO_StatMethod wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_StatMethod_Collection wakeUpCollection($rows)
	 */
	class EO_StatMethod_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\UsageEntityTable:rest/lib/usageentity.php:c64059f28c36ce7d2f6ab7ebcf4ebc8d */
namespace Bitrix\Rest {
	/**
	 * EO_UsageEntity
	 * @see \Bitrix\Rest\UsageEntityTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Rest\EO_UsageEntity setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEntityType()
	 * @method \Bitrix\Rest\EO_UsageEntity setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Rest\EO_UsageEntity resetEntityType()
	 * @method \Bitrix\Rest\EO_UsageEntity unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Rest\EO_UsageEntity setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Rest\EO_UsageEntity resetEntityId()
	 * @method \Bitrix\Rest\EO_UsageEntity unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityCode()
	 * @method \Bitrix\Rest\EO_UsageEntity setEntityCode(\string|\Bitrix\Main\DB\SqlExpression $entityCode)
	 * @method bool hasEntityCode()
	 * @method bool isEntityCodeFilled()
	 * @method bool isEntityCodeChanged()
	 * @method \string remindActualEntityCode()
	 * @method \string requireEntityCode()
	 * @method \Bitrix\Rest\EO_UsageEntity resetEntityCode()
	 * @method \Bitrix\Rest\EO_UsageEntity unsetEntityCode()
	 * @method \string fillEntityCode()
	 * @method \string getSubEntityType()
	 * @method \Bitrix\Rest\EO_UsageEntity setSubEntityType(\string|\Bitrix\Main\DB\SqlExpression $subEntityType)
	 * @method bool hasSubEntityType()
	 * @method bool isSubEntityTypeFilled()
	 * @method bool isSubEntityTypeChanged()
	 * @method \string remindActualSubEntityType()
	 * @method \string requireSubEntityType()
	 * @method \Bitrix\Rest\EO_UsageEntity resetSubEntityType()
	 * @method \Bitrix\Rest\EO_UsageEntity unsetSubEntityType()
	 * @method \string fillSubEntityType()
	 * @method \string getSubEntityName()
	 * @method \Bitrix\Rest\EO_UsageEntity setSubEntityName(\string|\Bitrix\Main\DB\SqlExpression $subEntityName)
	 * @method bool hasSubEntityName()
	 * @method bool isSubEntityNameFilled()
	 * @method bool isSubEntityNameChanged()
	 * @method \string remindActualSubEntityName()
	 * @method \string requireSubEntityName()
	 * @method \Bitrix\Rest\EO_UsageEntity resetSubEntityName()
	 * @method \Bitrix\Rest\EO_UsageEntity unsetSubEntityName()
	 * @method \string fillSubEntityName()
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
	 * @method \Bitrix\Rest\EO_UsageEntity set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_UsageEntity reset($fieldName)
	 * @method \Bitrix\Rest\EO_UsageEntity unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_UsageEntity wakeUp($data)
	 */
	class EO_UsageEntity {
		/* @var \Bitrix\Rest\UsageEntityTable */
		static public $dataClass = '\Bitrix\Rest\UsageEntityTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_UsageEntity_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityCodeList()
	 * @method \string[] fillEntityCode()
	 * @method \string[] getSubEntityTypeList()
	 * @method \string[] fillSubEntityType()
	 * @method \string[] getSubEntityNameList()
	 * @method \string[] fillSubEntityName()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_UsageEntity $object)
	 * @method bool has(\Bitrix\Rest\EO_UsageEntity $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_UsageEntity getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_UsageEntity[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_UsageEntity $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_UsageEntity_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_UsageEntity current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UsageEntity_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\UsageEntityTable */
		static public $dataClass = '\Bitrix\Rest\UsageEntityTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UsageEntity_Result exec()
	 * @method \Bitrix\Rest\EO_UsageEntity fetchObject()
	 * @method \Bitrix\Rest\EO_UsageEntity_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UsageEntity_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_UsageEntity fetchObject()
	 * @method \Bitrix\Rest\EO_UsageEntity_Collection fetchCollection()
	 */
	class EO_UsageEntity_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_UsageEntity createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_UsageEntity_Collection createCollection()
	 * @method \Bitrix\Rest\EO_UsageEntity wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_UsageEntity_Collection wakeUpCollection($rows)
	 */
	class EO_UsageEntity_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Rest\UsageStatTable:rest/lib/usagestat.php:6dd488efaa0d9de7832b2c37d933eea6 */
namespace Bitrix\Rest {
	/**
	 * EO_UsageStat
	 * @see \Bitrix\Rest\UsageStatTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date getStatDate()
	 * @method \Bitrix\Rest\EO_UsageStat setStatDate(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $statDate)
	 * @method bool hasStatDate()
	 * @method bool isStatDateFilled()
	 * @method bool isStatDateChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Rest\EO_UsageStat setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \boolean getIsSent()
	 * @method \Bitrix\Rest\EO_UsageStat setIsSent(\boolean|\Bitrix\Main\DB\SqlExpression $isSent)
	 * @method bool hasIsSent()
	 * @method bool isIsSentFilled()
	 * @method bool isIsSentChanged()
	 * @method \boolean remindActualIsSent()
	 * @method \boolean requireIsSent()
	 * @method \Bitrix\Rest\EO_UsageStat resetIsSent()
	 * @method \Bitrix\Rest\EO_UsageStat unsetIsSent()
	 * @method \boolean fillIsSent()
	 * @method \int getHour0()
	 * @method \Bitrix\Rest\EO_UsageStat setHour0(\int|\Bitrix\Main\DB\SqlExpression $hour0)
	 * @method bool hasHour0()
	 * @method bool isHour0Filled()
	 * @method bool isHour0Changed()
	 * @method \int remindActualHour0()
	 * @method \int requireHour0()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour0()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour0()
	 * @method \int fillHour0()
	 * @method \int getHour1()
	 * @method \Bitrix\Rest\EO_UsageStat setHour1(\int|\Bitrix\Main\DB\SqlExpression $hour1)
	 * @method bool hasHour1()
	 * @method bool isHour1Filled()
	 * @method bool isHour1Changed()
	 * @method \int remindActualHour1()
	 * @method \int requireHour1()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour1()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour1()
	 * @method \int fillHour1()
	 * @method \int getHour2()
	 * @method \Bitrix\Rest\EO_UsageStat setHour2(\int|\Bitrix\Main\DB\SqlExpression $hour2)
	 * @method bool hasHour2()
	 * @method bool isHour2Filled()
	 * @method bool isHour2Changed()
	 * @method \int remindActualHour2()
	 * @method \int requireHour2()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour2()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour2()
	 * @method \int fillHour2()
	 * @method \int getHour3()
	 * @method \Bitrix\Rest\EO_UsageStat setHour3(\int|\Bitrix\Main\DB\SqlExpression $hour3)
	 * @method bool hasHour3()
	 * @method bool isHour3Filled()
	 * @method bool isHour3Changed()
	 * @method \int remindActualHour3()
	 * @method \int requireHour3()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour3()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour3()
	 * @method \int fillHour3()
	 * @method \int getHour4()
	 * @method \Bitrix\Rest\EO_UsageStat setHour4(\int|\Bitrix\Main\DB\SqlExpression $hour4)
	 * @method bool hasHour4()
	 * @method bool isHour4Filled()
	 * @method bool isHour4Changed()
	 * @method \int remindActualHour4()
	 * @method \int requireHour4()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour4()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour4()
	 * @method \int fillHour4()
	 * @method \int getHour5()
	 * @method \Bitrix\Rest\EO_UsageStat setHour5(\int|\Bitrix\Main\DB\SqlExpression $hour5)
	 * @method bool hasHour5()
	 * @method bool isHour5Filled()
	 * @method bool isHour5Changed()
	 * @method \int remindActualHour5()
	 * @method \int requireHour5()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour5()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour5()
	 * @method \int fillHour5()
	 * @method \int getHour6()
	 * @method \Bitrix\Rest\EO_UsageStat setHour6(\int|\Bitrix\Main\DB\SqlExpression $hour6)
	 * @method bool hasHour6()
	 * @method bool isHour6Filled()
	 * @method bool isHour6Changed()
	 * @method \int remindActualHour6()
	 * @method \int requireHour6()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour6()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour6()
	 * @method \int fillHour6()
	 * @method \int getHour7()
	 * @method \Bitrix\Rest\EO_UsageStat setHour7(\int|\Bitrix\Main\DB\SqlExpression $hour7)
	 * @method bool hasHour7()
	 * @method bool isHour7Filled()
	 * @method bool isHour7Changed()
	 * @method \int remindActualHour7()
	 * @method \int requireHour7()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour7()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour7()
	 * @method \int fillHour7()
	 * @method \int getHour8()
	 * @method \Bitrix\Rest\EO_UsageStat setHour8(\int|\Bitrix\Main\DB\SqlExpression $hour8)
	 * @method bool hasHour8()
	 * @method bool isHour8Filled()
	 * @method bool isHour8Changed()
	 * @method \int remindActualHour8()
	 * @method \int requireHour8()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour8()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour8()
	 * @method \int fillHour8()
	 * @method \int getHour9()
	 * @method \Bitrix\Rest\EO_UsageStat setHour9(\int|\Bitrix\Main\DB\SqlExpression $hour9)
	 * @method bool hasHour9()
	 * @method bool isHour9Filled()
	 * @method bool isHour9Changed()
	 * @method \int remindActualHour9()
	 * @method \int requireHour9()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour9()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour9()
	 * @method \int fillHour9()
	 * @method \int getHour10()
	 * @method \Bitrix\Rest\EO_UsageStat setHour10(\int|\Bitrix\Main\DB\SqlExpression $hour10)
	 * @method bool hasHour10()
	 * @method bool isHour10Filled()
	 * @method bool isHour10Changed()
	 * @method \int remindActualHour10()
	 * @method \int requireHour10()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour10()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour10()
	 * @method \int fillHour10()
	 * @method \int getHour11()
	 * @method \Bitrix\Rest\EO_UsageStat setHour11(\int|\Bitrix\Main\DB\SqlExpression $hour11)
	 * @method bool hasHour11()
	 * @method bool isHour11Filled()
	 * @method bool isHour11Changed()
	 * @method \int remindActualHour11()
	 * @method \int requireHour11()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour11()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour11()
	 * @method \int fillHour11()
	 * @method \int getHour12()
	 * @method \Bitrix\Rest\EO_UsageStat setHour12(\int|\Bitrix\Main\DB\SqlExpression $hour12)
	 * @method bool hasHour12()
	 * @method bool isHour12Filled()
	 * @method bool isHour12Changed()
	 * @method \int remindActualHour12()
	 * @method \int requireHour12()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour12()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour12()
	 * @method \int fillHour12()
	 * @method \int getHour13()
	 * @method \Bitrix\Rest\EO_UsageStat setHour13(\int|\Bitrix\Main\DB\SqlExpression $hour13)
	 * @method bool hasHour13()
	 * @method bool isHour13Filled()
	 * @method bool isHour13Changed()
	 * @method \int remindActualHour13()
	 * @method \int requireHour13()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour13()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour13()
	 * @method \int fillHour13()
	 * @method \int getHour14()
	 * @method \Bitrix\Rest\EO_UsageStat setHour14(\int|\Bitrix\Main\DB\SqlExpression $hour14)
	 * @method bool hasHour14()
	 * @method bool isHour14Filled()
	 * @method bool isHour14Changed()
	 * @method \int remindActualHour14()
	 * @method \int requireHour14()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour14()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour14()
	 * @method \int fillHour14()
	 * @method \int getHour15()
	 * @method \Bitrix\Rest\EO_UsageStat setHour15(\int|\Bitrix\Main\DB\SqlExpression $hour15)
	 * @method bool hasHour15()
	 * @method bool isHour15Filled()
	 * @method bool isHour15Changed()
	 * @method \int remindActualHour15()
	 * @method \int requireHour15()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour15()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour15()
	 * @method \int fillHour15()
	 * @method \int getHour16()
	 * @method \Bitrix\Rest\EO_UsageStat setHour16(\int|\Bitrix\Main\DB\SqlExpression $hour16)
	 * @method bool hasHour16()
	 * @method bool isHour16Filled()
	 * @method bool isHour16Changed()
	 * @method \int remindActualHour16()
	 * @method \int requireHour16()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour16()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour16()
	 * @method \int fillHour16()
	 * @method \int getHour17()
	 * @method \Bitrix\Rest\EO_UsageStat setHour17(\int|\Bitrix\Main\DB\SqlExpression $hour17)
	 * @method bool hasHour17()
	 * @method bool isHour17Filled()
	 * @method bool isHour17Changed()
	 * @method \int remindActualHour17()
	 * @method \int requireHour17()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour17()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour17()
	 * @method \int fillHour17()
	 * @method \int getHour18()
	 * @method \Bitrix\Rest\EO_UsageStat setHour18(\int|\Bitrix\Main\DB\SqlExpression $hour18)
	 * @method bool hasHour18()
	 * @method bool isHour18Filled()
	 * @method bool isHour18Changed()
	 * @method \int remindActualHour18()
	 * @method \int requireHour18()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour18()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour18()
	 * @method \int fillHour18()
	 * @method \int getHour19()
	 * @method \Bitrix\Rest\EO_UsageStat setHour19(\int|\Bitrix\Main\DB\SqlExpression $hour19)
	 * @method bool hasHour19()
	 * @method bool isHour19Filled()
	 * @method bool isHour19Changed()
	 * @method \int remindActualHour19()
	 * @method \int requireHour19()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour19()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour19()
	 * @method \int fillHour19()
	 * @method \int getHour20()
	 * @method \Bitrix\Rest\EO_UsageStat setHour20(\int|\Bitrix\Main\DB\SqlExpression $hour20)
	 * @method bool hasHour20()
	 * @method bool isHour20Filled()
	 * @method bool isHour20Changed()
	 * @method \int remindActualHour20()
	 * @method \int requireHour20()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour20()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour20()
	 * @method \int fillHour20()
	 * @method \int getHour21()
	 * @method \Bitrix\Rest\EO_UsageStat setHour21(\int|\Bitrix\Main\DB\SqlExpression $hour21)
	 * @method bool hasHour21()
	 * @method bool isHour21Filled()
	 * @method bool isHour21Changed()
	 * @method \int remindActualHour21()
	 * @method \int requireHour21()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour21()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour21()
	 * @method \int fillHour21()
	 * @method \int getHour22()
	 * @method \Bitrix\Rest\EO_UsageStat setHour22(\int|\Bitrix\Main\DB\SqlExpression $hour22)
	 * @method bool hasHour22()
	 * @method bool isHour22Filled()
	 * @method bool isHour22Changed()
	 * @method \int remindActualHour22()
	 * @method \int requireHour22()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour22()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour22()
	 * @method \int fillHour22()
	 * @method \int getHour23()
	 * @method \Bitrix\Rest\EO_UsageStat setHour23(\int|\Bitrix\Main\DB\SqlExpression $hour23)
	 * @method bool hasHour23()
	 * @method bool isHour23Filled()
	 * @method bool isHour23Changed()
	 * @method \int remindActualHour23()
	 * @method \int requireHour23()
	 * @method \Bitrix\Rest\EO_UsageStat resetHour23()
	 * @method \Bitrix\Rest\EO_UsageStat unsetHour23()
	 * @method \int fillHour23()
	 * @method \Bitrix\Rest\EO_UsageEntity getEntity()
	 * @method \Bitrix\Rest\EO_UsageEntity remindActualEntity()
	 * @method \Bitrix\Rest\EO_UsageEntity requireEntity()
	 * @method \Bitrix\Rest\EO_UsageStat setEntity(\Bitrix\Rest\EO_UsageEntity $object)
	 * @method \Bitrix\Rest\EO_UsageStat resetEntity()
	 * @method \Bitrix\Rest\EO_UsageStat unsetEntity()
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \Bitrix\Rest\EO_UsageEntity fillEntity()
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
	 * @method \Bitrix\Rest\EO_UsageStat set($fieldName, $value)
	 * @method \Bitrix\Rest\EO_UsageStat reset($fieldName)
	 * @method \Bitrix\Rest\EO_UsageStat unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Rest\EO_UsageStat wakeUp($data)
	 */
	class EO_UsageStat {
		/* @var \Bitrix\Rest\UsageStatTable */
		static public $dataClass = '\Bitrix\Rest\UsageStatTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Rest {
	/**
	 * EO_UsageStat_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date[] getStatDateList()
	 * @method \int[] getEntityIdList()
	 * @method \boolean[] getIsSentList()
	 * @method \boolean[] fillIsSent()
	 * @method \int[] getHour0List()
	 * @method \int[] fillHour0()
	 * @method \int[] getHour1List()
	 * @method \int[] fillHour1()
	 * @method \int[] getHour2List()
	 * @method \int[] fillHour2()
	 * @method \int[] getHour3List()
	 * @method \int[] fillHour3()
	 * @method \int[] getHour4List()
	 * @method \int[] fillHour4()
	 * @method \int[] getHour5List()
	 * @method \int[] fillHour5()
	 * @method \int[] getHour6List()
	 * @method \int[] fillHour6()
	 * @method \int[] getHour7List()
	 * @method \int[] fillHour7()
	 * @method \int[] getHour8List()
	 * @method \int[] fillHour8()
	 * @method \int[] getHour9List()
	 * @method \int[] fillHour9()
	 * @method \int[] getHour10List()
	 * @method \int[] fillHour10()
	 * @method \int[] getHour11List()
	 * @method \int[] fillHour11()
	 * @method \int[] getHour12List()
	 * @method \int[] fillHour12()
	 * @method \int[] getHour13List()
	 * @method \int[] fillHour13()
	 * @method \int[] getHour14List()
	 * @method \int[] fillHour14()
	 * @method \int[] getHour15List()
	 * @method \int[] fillHour15()
	 * @method \int[] getHour16List()
	 * @method \int[] fillHour16()
	 * @method \int[] getHour17List()
	 * @method \int[] fillHour17()
	 * @method \int[] getHour18List()
	 * @method \int[] fillHour18()
	 * @method \int[] getHour19List()
	 * @method \int[] fillHour19()
	 * @method \int[] getHour20List()
	 * @method \int[] fillHour20()
	 * @method \int[] getHour21List()
	 * @method \int[] fillHour21()
	 * @method \int[] getHour22List()
	 * @method \int[] fillHour22()
	 * @method \int[] getHour23List()
	 * @method \int[] fillHour23()
	 * @method \Bitrix\Rest\EO_UsageEntity[] getEntityList()
	 * @method \Bitrix\Rest\EO_UsageStat_Collection getEntityCollection()
	 * @method \Bitrix\Rest\EO_UsageEntity_Collection fillEntity()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Rest\EO_UsageStat $object)
	 * @method bool has(\Bitrix\Rest\EO_UsageStat $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Rest\EO_UsageStat getByPrimary($primary)
	 * @method \Bitrix\Rest\EO_UsageStat[] getAll()
	 * @method bool remove(\Bitrix\Rest\EO_UsageStat $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Rest\EO_UsageStat_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Rest\EO_UsageStat current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UsageStat_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Rest\UsageStatTable */
		static public $dataClass = '\Bitrix\Rest\UsageStatTable';
	}
}
namespace Bitrix\Rest {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UsageStat_Result exec()
	 * @method \Bitrix\Rest\EO_UsageStat fetchObject()
	 * @method \Bitrix\Rest\EO_UsageStat_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UsageStat_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Rest\EO_UsageStat fetchObject()
	 * @method \Bitrix\Rest\EO_UsageStat_Collection fetchCollection()
	 */
	class EO_UsageStat_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Rest\EO_UsageStat createObject($setDefaultValues = true)
	 * @method \Bitrix\Rest\EO_UsageStat_Collection createCollection()
	 * @method \Bitrix\Rest\EO_UsageStat wakeUpObject($row)
	 * @method \Bitrix\Rest\EO_UsageStat_Collection wakeUpCollection($rows)
	 */
	class EO_UsageStat_Entity extends \Bitrix\Main\ORM\Entity {}
}