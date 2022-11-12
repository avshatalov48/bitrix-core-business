<?php

/* ORMENTITYANNOTATION:Bitrix\Main\Analytics\CounterDataTable:main/lib/analytics/counterdata.php */
namespace Bitrix\Main\Analytics {
	/**
	 * EO_CounterData
	 * @see \Bitrix\Main\Analytics\CounterDataTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Main\Analytics\EO_CounterData setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getType()
	 * @method \Bitrix\Main\Analytics\EO_CounterData setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Main\Analytics\EO_CounterData resetType()
	 * @method \Bitrix\Main\Analytics\EO_CounterData unsetType()
	 * @method \string fillType()
	 * @method \string getData()
	 * @method \Bitrix\Main\Analytics\EO_CounterData setData(\string|\Bitrix\Main\DB\SqlExpression $data)
	 * @method bool hasData()
	 * @method bool isDataFilled()
	 * @method bool isDataChanged()
	 * @method \string remindActualData()
	 * @method \string requireData()
	 * @method \Bitrix\Main\Analytics\EO_CounterData resetData()
	 * @method \Bitrix\Main\Analytics\EO_CounterData unsetData()
	 * @method \string fillData()
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
	 * @method \Bitrix\Main\Analytics\EO_CounterData set($fieldName, $value)
	 * @method \Bitrix\Main\Analytics\EO_CounterData reset($fieldName)
	 * @method \Bitrix\Main\Analytics\EO_CounterData unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Analytics\EO_CounterData wakeUp($data)
	 */
	class EO_CounterData {
		/* @var \Bitrix\Main\Analytics\CounterDataTable */
		static public $dataClass = '\Bitrix\Main\Analytics\CounterDataTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Analytics {
	/**
	 * EO_CounterData_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getDataList()
	 * @method \string[] fillData()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Analytics\EO_CounterData $object)
	 * @method bool has(\Bitrix\Main\Analytics\EO_CounterData $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Analytics\EO_CounterData getByPrimary($primary)
	 * @method \Bitrix\Main\Analytics\EO_CounterData[] getAll()
	 * @method bool remove(\Bitrix\Main\Analytics\EO_CounterData $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Analytics\EO_CounterData_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Analytics\EO_CounterData current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_CounterData_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Analytics\CounterDataTable */
		static public $dataClass = '\Bitrix\Main\Analytics\CounterDataTable';
	}
}
namespace Bitrix\Main\Analytics {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CounterData_Result exec()
	 * @method \Bitrix\Main\Analytics\EO_CounterData fetchObject()
	 * @method \Bitrix\Main\Analytics\EO_CounterData_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CounterData_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Analytics\EO_CounterData fetchObject()
	 * @method \Bitrix\Main\Analytics\EO_CounterData_Collection fetchCollection()
	 */
	class EO_CounterData_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Analytics\EO_CounterData createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Analytics\EO_CounterData_Collection createCollection()
	 * @method \Bitrix\Main\Analytics\EO_CounterData wakeUpObject($row)
	 * @method \Bitrix\Main\Analytics\EO_CounterData_Collection wakeUpCollection($rows)
	 */
	class EO_CounterData_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Authentication\ApplicationPasswordTable:main/lib/authentication/applicationpassword.php */
namespace Bitrix\Main\Authentication {
	/**
	 * EO_ApplicationPassword
	 * @see \Bitrix\Main\Authentication\ApplicationPasswordTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetUserId()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getApplicationId()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setApplicationId(\string|\Bitrix\Main\DB\SqlExpression $applicationId)
	 * @method bool hasApplicationId()
	 * @method bool isApplicationIdFilled()
	 * @method bool isApplicationIdChanged()
	 * @method \string remindActualApplicationId()
	 * @method \string requireApplicationId()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetApplicationId()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetApplicationId()
	 * @method \string fillApplicationId()
	 * @method \string getPassword()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setPassword(\string|\Bitrix\Main\DB\SqlExpression $password)
	 * @method bool hasPassword()
	 * @method bool isPasswordFilled()
	 * @method bool isPasswordChanged()
	 * @method \string remindActualPassword()
	 * @method \string requirePassword()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetPassword()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetPassword()
	 * @method \string fillPassword()
	 * @method \string getDigestPassword()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setDigestPassword(\string|\Bitrix\Main\DB\SqlExpression $digestPassword)
	 * @method bool hasDigestPassword()
	 * @method bool isDigestPasswordFilled()
	 * @method bool isDigestPasswordChanged()
	 * @method \string remindActualDigestPassword()
	 * @method \string requireDigestPassword()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetDigestPassword()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetDigestPassword()
	 * @method \string fillDigestPassword()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetDateCreate()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateLogin()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setDateLogin(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateLogin)
	 * @method bool hasDateLogin()
	 * @method bool isDateLoginFilled()
	 * @method bool isDateLoginChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateLogin()
	 * @method \Bitrix\Main\Type\DateTime requireDateLogin()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetDateLogin()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetDateLogin()
	 * @method \Bitrix\Main\Type\DateTime fillDateLogin()
	 * @method \string getLastIp()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setLastIp(\string|\Bitrix\Main\DB\SqlExpression $lastIp)
	 * @method bool hasLastIp()
	 * @method bool isLastIpFilled()
	 * @method bool isLastIpChanged()
	 * @method \string remindActualLastIp()
	 * @method \string requireLastIp()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetLastIp()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetLastIp()
	 * @method \string fillLastIp()
	 * @method \string getComment()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setComment(\string|\Bitrix\Main\DB\SqlExpression $comment)
	 * @method bool hasComment()
	 * @method bool isCommentFilled()
	 * @method bool isCommentChanged()
	 * @method \string remindActualComment()
	 * @method \string requireComment()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetComment()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetComment()
	 * @method \string fillComment()
	 * @method \string getSyscomment()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setSyscomment(\string|\Bitrix\Main\DB\SqlExpression $syscomment)
	 * @method bool hasSyscomment()
	 * @method bool isSyscommentFilled()
	 * @method bool isSyscommentChanged()
	 * @method \string remindActualSyscomment()
	 * @method \string requireSyscomment()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetSyscomment()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetSyscomment()
	 * @method \string fillSyscomment()
	 * @method \string getCode()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetCode()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetCode()
	 * @method \string fillCode()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword resetUser()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unsetUser()
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
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword set($fieldName, $value)
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword reset($fieldName)
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Authentication\EO_ApplicationPassword wakeUp($data)
	 */
	class EO_ApplicationPassword {
		/* @var \Bitrix\Main\Authentication\ApplicationPasswordTable */
		static public $dataClass = '\Bitrix\Main\Authentication\ApplicationPasswordTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Authentication {
	/**
	 * EO_ApplicationPassword_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getApplicationIdList()
	 * @method \string[] fillApplicationId()
	 * @method \string[] getPasswordList()
	 * @method \string[] fillPassword()
	 * @method \string[] getDigestPasswordList()
	 * @method \string[] fillDigestPassword()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateLoginList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateLogin()
	 * @method \string[] getLastIpList()
	 * @method \string[] fillLastIp()
	 * @method \string[] getCommentList()
	 * @method \string[] fillComment()
	 * @method \string[] getSyscommentList()
	 * @method \string[] fillSyscomment()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Authentication\EO_ApplicationPassword $object)
	 * @method bool has(\Bitrix\Main\Authentication\EO_ApplicationPassword $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword getByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword[] getAll()
	 * @method bool remove(\Bitrix\Main\Authentication\EO_ApplicationPassword $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Authentication\EO_ApplicationPassword_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ApplicationPassword_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Authentication\ApplicationPasswordTable */
		static public $dataClass = '\Bitrix\Main\Authentication\ApplicationPasswordTable';
	}
}
namespace Bitrix\Main\Authentication {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ApplicationPassword_Result exec()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword fetchObject()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ApplicationPassword_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword fetchObject()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword_Collection fetchCollection()
	 */
	class EO_ApplicationPassword_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword_Collection createCollection()
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword wakeUpObject($row)
	 * @method \Bitrix\Main\Authentication\EO_ApplicationPassword_Collection wakeUpCollection($rows)
	 */
	class EO_ApplicationPassword_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Authentication\Internal\ModuleGroupTable:main/lib/authentication/internal/modulegrouptable.php */
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_ModuleGroup
	 * @see \Bitrix\Main\Authentication\Internal\ModuleGroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getModuleId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup resetModuleId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \int getGroupId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup resetGroupId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \string getGAccess()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup setGAccess(\string|\Bitrix\Main\DB\SqlExpression $gAccess)
	 * @method bool hasGAccess()
	 * @method bool isGAccessFilled()
	 * @method bool isGAccessChanged()
	 * @method \string remindActualGAccess()
	 * @method \string requireGAccess()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup resetGAccess()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup unsetGAccess()
	 * @method \string fillGAccess()
	 * @method \string getSiteId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup resetSiteId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \Bitrix\Main\EO_Group getGroup()
	 * @method \Bitrix\Main\EO_Group remindActualGroup()
	 * @method \Bitrix\Main\EO_Group requireGroup()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup setGroup(\Bitrix\Main\EO_Group $object)
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup resetGroup()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Main\EO_Group fillGroup()
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
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup set($fieldName, $value)
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup reset($fieldName)
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Authentication\Internal\EO_ModuleGroup wakeUp($data)
	 */
	class EO_ModuleGroup {
		/* @var \Bitrix\Main\Authentication\Internal\ModuleGroupTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\ModuleGroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_ModuleGroup_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \string[] getGAccessList()
	 * @method \string[] fillGAccess()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \Bitrix\Main\EO_Group[] getGroupList()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup_Collection getGroupCollection()
	 * @method \Bitrix\Main\EO_Group_Collection fillGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Authentication\Internal\EO_ModuleGroup $object)
	 * @method bool has(\Bitrix\Main\Authentication\Internal\EO_ModuleGroup $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup getByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup[] getAll()
	 * @method bool remove(\Bitrix\Main\Authentication\Internal\EO_ModuleGroup $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Authentication\Internal\EO_ModuleGroup_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ModuleGroup_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Authentication\Internal\ModuleGroupTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\ModuleGroupTable';
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ModuleGroup_Result exec()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ModuleGroup_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup_Collection fetchCollection()
	 */
	class EO_ModuleGroup_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup_Collection createCollection()
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup wakeUpObject($row)
	 * @method \Bitrix\Main\Authentication\Internal\EO_ModuleGroup_Collection wakeUpCollection($rows)
	 */
	class EO_ModuleGroup_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Authentication\Internal\UserAuthCodeTable:main/lib/authentication/internal/userauthcodetable.php */
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserAuthCode
	 * @see \Bitrix\Main\Authentication\Internal\UserAuthCodeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getCodeType()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode setCodeType(\string|\Bitrix\Main\DB\SqlExpression $codeType)
	 * @method bool hasCodeType()
	 * @method bool isCodeTypeFilled()
	 * @method bool isCodeTypeChanged()
	 * @method \string getOtpSecret()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode setOtpSecret(\string|\Bitrix\Main\DB\SqlExpression $otpSecret)
	 * @method bool hasOtpSecret()
	 * @method bool isOtpSecretFilled()
	 * @method bool isOtpSecretChanged()
	 * @method \string remindActualOtpSecret()
	 * @method \string requireOtpSecret()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode resetOtpSecret()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode unsetOtpSecret()
	 * @method \string fillOtpSecret()
	 * @method \int getAttempts()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode setAttempts(\int|\Bitrix\Main\DB\SqlExpression $attempts)
	 * @method bool hasAttempts()
	 * @method bool isAttemptsFilled()
	 * @method bool isAttemptsChanged()
	 * @method \int remindActualAttempts()
	 * @method \int requireAttempts()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode resetAttempts()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode unsetAttempts()
	 * @method \int fillAttempts()
	 * @method \Bitrix\Main\Type\DateTime getDateSent()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode setDateSent(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateSent)
	 * @method bool hasDateSent()
	 * @method bool isDateSentFilled()
	 * @method bool isDateSentChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateSent()
	 * @method \Bitrix\Main\Type\DateTime requireDateSent()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode resetDateSent()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode unsetDateSent()
	 * @method \Bitrix\Main\Type\DateTime fillDateSent()
	 * @method \Bitrix\Main\Type\DateTime getDateResent()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode setDateResent(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateResent)
	 * @method bool hasDateResent()
	 * @method bool isDateResentFilled()
	 * @method bool isDateResentChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateResent()
	 * @method \Bitrix\Main\Type\DateTime requireDateResent()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode resetDateResent()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode unsetDateResent()
	 * @method \Bitrix\Main\Type\DateTime fillDateResent()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode resetUser()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode unsetUser()
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
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode set($fieldName, $value)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode reset($fieldName)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserAuthCode wakeUp($data)
	 */
	class EO_UserAuthCode {
		/* @var \Bitrix\Main\Authentication\Internal\UserAuthCodeTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserAuthCodeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserAuthCode_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getCodeTypeList()
	 * @method \string[] getOtpSecretList()
	 * @method \string[] fillOtpSecret()
	 * @method \int[] getAttemptsList()
	 * @method \int[] fillAttempts()
	 * @method \Bitrix\Main\Type\DateTime[] getDateSentList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateSent()
	 * @method \Bitrix\Main\Type\DateTime[] getDateResentList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateResent()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Authentication\Internal\EO_UserAuthCode $object)
	 * @method bool has(\Bitrix\Main\Authentication\Internal\EO_UserAuthCode $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode getByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode[] getAll()
	 * @method bool remove(\Bitrix\Main\Authentication\Internal\EO_UserAuthCode $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserAuthCode_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserAuthCode_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Authentication\Internal\UserAuthCodeTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserAuthCodeTable';
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserAuthCode_Result exec()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserAuthCode_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode_Collection fetchCollection()
	 */
	class EO_UserAuthCode_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode_Collection createCollection()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode wakeUpObject($row)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserAuthCode_Collection wakeUpCollection($rows)
	 */
	class EO_UserAuthCode_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Authentication\Internal\UserDeviceLoginTable:main/lib/authentication/internal/userdevicelogintable.php */
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserDeviceLogin
	 * @see \Bitrix\Main\Authentication\Internal\UserDeviceLoginTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDeviceId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setDeviceId(\int|\Bitrix\Main\DB\SqlExpression $deviceId)
	 * @method bool hasDeviceId()
	 * @method bool isDeviceIdFilled()
	 * @method bool isDeviceIdChanged()
	 * @method \int remindActualDeviceId()
	 * @method \int requireDeviceId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin resetDeviceId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unsetDeviceId()
	 * @method \int fillDeviceId()
	 * @method \Bitrix\Main\Type\DateTime getLoginDate()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setLoginDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $loginDate)
	 * @method bool hasLoginDate()
	 * @method bool isLoginDateFilled()
	 * @method bool isLoginDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLoginDate()
	 * @method \Bitrix\Main\Type\DateTime requireLoginDate()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin resetLoginDate()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unsetLoginDate()
	 * @method \Bitrix\Main\Type\DateTime fillLoginDate()
	 * @method \string getIp()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setIp(\string|\Bitrix\Main\DB\SqlExpression $ip)
	 * @method bool hasIp()
	 * @method bool isIpFilled()
	 * @method bool isIpChanged()
	 * @method \string remindActualIp()
	 * @method \string requireIp()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin resetIp()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unsetIp()
	 * @method \string fillIp()
	 * @method \int getCityGeoid()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setCityGeoid(\int|\Bitrix\Main\DB\SqlExpression $cityGeoid)
	 * @method bool hasCityGeoid()
	 * @method bool isCityGeoidFilled()
	 * @method bool isCityGeoidChanged()
	 * @method \int remindActualCityGeoid()
	 * @method \int requireCityGeoid()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin resetCityGeoid()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unsetCityGeoid()
	 * @method \int fillCityGeoid()
	 * @method \int getRegionGeoid()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setRegionGeoid(\int|\Bitrix\Main\DB\SqlExpression $regionGeoid)
	 * @method bool hasRegionGeoid()
	 * @method bool isRegionGeoidFilled()
	 * @method bool isRegionGeoidChanged()
	 * @method \int remindActualRegionGeoid()
	 * @method \int requireRegionGeoid()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin resetRegionGeoid()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unsetRegionGeoid()
	 * @method \int fillRegionGeoid()
	 * @method \string getCountryIsoCode()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setCountryIsoCode(\string|\Bitrix\Main\DB\SqlExpression $countryIsoCode)
	 * @method bool hasCountryIsoCode()
	 * @method bool isCountryIsoCodeFilled()
	 * @method bool isCountryIsoCodeChanged()
	 * @method \string remindActualCountryIsoCode()
	 * @method \string requireCountryIsoCode()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin resetCountryIsoCode()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unsetCountryIsoCode()
	 * @method \string fillCountryIsoCode()
	 * @method \int getAppPasswordId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setAppPasswordId(\int|\Bitrix\Main\DB\SqlExpression $appPasswordId)
	 * @method bool hasAppPasswordId()
	 * @method bool isAppPasswordIdFilled()
	 * @method bool isAppPasswordIdChanged()
	 * @method \int remindActualAppPasswordId()
	 * @method \int requireAppPasswordId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin resetAppPasswordId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unsetAppPasswordId()
	 * @method \int fillAppPasswordId()
	 * @method \int getStoredAuthId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setStoredAuthId(\int|\Bitrix\Main\DB\SqlExpression $storedAuthId)
	 * @method bool hasStoredAuthId()
	 * @method bool isStoredAuthIdFilled()
	 * @method bool isStoredAuthIdChanged()
	 * @method \int remindActualStoredAuthId()
	 * @method \int requireStoredAuthId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin resetStoredAuthId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unsetStoredAuthId()
	 * @method \int fillStoredAuthId()
	 * @method \int getHitAuthId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin setHitAuthId(\int|\Bitrix\Main\DB\SqlExpression $hitAuthId)
	 * @method bool hasHitAuthId()
	 * @method bool isHitAuthIdFilled()
	 * @method bool isHitAuthIdChanged()
	 * @method \int remindActualHitAuthId()
	 * @method \int requireHitAuthId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin resetHitAuthId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unsetHitAuthId()
	 * @method \int fillHitAuthId()
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
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin set($fieldName, $value)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin reset($fieldName)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin wakeUp($data)
	 */
	class EO_UserDeviceLogin {
		/* @var \Bitrix\Main\Authentication\Internal\UserDeviceLoginTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserDeviceLoginTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserDeviceLogin_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDeviceIdList()
	 * @method \int[] fillDeviceId()
	 * @method \Bitrix\Main\Type\DateTime[] getLoginDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLoginDate()
	 * @method \string[] getIpList()
	 * @method \string[] fillIp()
	 * @method \int[] getCityGeoidList()
	 * @method \int[] fillCityGeoid()
	 * @method \int[] getRegionGeoidList()
	 * @method \int[] fillRegionGeoid()
	 * @method \string[] getCountryIsoCodeList()
	 * @method \string[] fillCountryIsoCode()
	 * @method \int[] getAppPasswordIdList()
	 * @method \int[] fillAppPasswordId()
	 * @method \int[] getStoredAuthIdList()
	 * @method \int[] fillStoredAuthId()
	 * @method \int[] getHitAuthIdList()
	 * @method \int[] fillHitAuthId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin $object)
	 * @method bool has(\Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin getByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin[] getAll()
	 * @method bool remove(\Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserDeviceLogin_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Authentication\Internal\UserDeviceLoginTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserDeviceLoginTable';
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserDeviceLogin_Result exec()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserDeviceLogin_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin_Collection fetchCollection()
	 */
	class EO_UserDeviceLogin_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin_Collection createCollection()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin wakeUpObject($row)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin_Collection wakeUpCollection($rows)
	 */
	class EO_UserDeviceLogin_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Authentication\Internal\UserDeviceTable:main/lib/authentication/internal/userdevicetable.php */
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserDevice
	 * @see \Bitrix\Main\Authentication\Internal\UserDeviceTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice resetUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getDeviceUid()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice setDeviceUid(\string|\Bitrix\Main\DB\SqlExpression $deviceUid)
	 * @method bool hasDeviceUid()
	 * @method bool isDeviceUidFilled()
	 * @method bool isDeviceUidChanged()
	 * @method \string remindActualDeviceUid()
	 * @method \string requireDeviceUid()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice resetDeviceUid()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice unsetDeviceUid()
	 * @method \string fillDeviceUid()
	 * @method \int getDeviceType()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice setDeviceType(\int|\Bitrix\Main\DB\SqlExpression $deviceType)
	 * @method bool hasDeviceType()
	 * @method bool isDeviceTypeFilled()
	 * @method bool isDeviceTypeChanged()
	 * @method \int remindActualDeviceType()
	 * @method \int requireDeviceType()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice resetDeviceType()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice unsetDeviceType()
	 * @method \int fillDeviceType()
	 * @method \string getBrowser()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice setBrowser(\string|\Bitrix\Main\DB\SqlExpression $browser)
	 * @method bool hasBrowser()
	 * @method bool isBrowserFilled()
	 * @method bool isBrowserChanged()
	 * @method \string remindActualBrowser()
	 * @method \string requireBrowser()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice resetBrowser()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice unsetBrowser()
	 * @method \string fillBrowser()
	 * @method \string getPlatform()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice setPlatform(\string|\Bitrix\Main\DB\SqlExpression $platform)
	 * @method bool hasPlatform()
	 * @method bool isPlatformFilled()
	 * @method bool isPlatformChanged()
	 * @method \string remindActualPlatform()
	 * @method \string requirePlatform()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice resetPlatform()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice unsetPlatform()
	 * @method \string fillPlatform()
	 * @method \string getUserAgent()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice setUserAgent(\string|\Bitrix\Main\DB\SqlExpression $userAgent)
	 * @method bool hasUserAgent()
	 * @method bool isUserAgentFilled()
	 * @method bool isUserAgentChanged()
	 * @method \string remindActualUserAgent()
	 * @method \string requireUserAgent()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice resetUserAgent()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice unsetUserAgent()
	 * @method \string fillUserAgent()
	 * @method \boolean getCookable()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice setCookable(\boolean|\Bitrix\Main\DB\SqlExpression $cookable)
	 * @method bool hasCookable()
	 * @method bool isCookableFilled()
	 * @method bool isCookableChanged()
	 * @method \boolean remindActualCookable()
	 * @method \boolean requireCookable()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice resetCookable()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice unsetCookable()
	 * @method \boolean fillCookable()
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
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice set($fieldName, $value)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice reset($fieldName)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDevice wakeUp($data)
	 */
	class EO_UserDevice {
		/* @var \Bitrix\Main\Authentication\Internal\UserDeviceTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserDeviceTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserDevice_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getDeviceUidList()
	 * @method \string[] fillDeviceUid()
	 * @method \int[] getDeviceTypeList()
	 * @method \int[] fillDeviceType()
	 * @method \string[] getBrowserList()
	 * @method \string[] fillBrowser()
	 * @method \string[] getPlatformList()
	 * @method \string[] fillPlatform()
	 * @method \string[] getUserAgentList()
	 * @method \string[] fillUserAgent()
	 * @method \boolean[] getCookableList()
	 * @method \boolean[] fillCookable()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Authentication\Internal\EO_UserDevice $object)
	 * @method bool has(\Bitrix\Main\Authentication\Internal\EO_UserDevice $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice getByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice[] getAll()
	 * @method bool remove(\Bitrix\Main\Authentication\Internal\EO_UserDevice $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDevice_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserDevice_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Authentication\Internal\UserDeviceTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserDeviceTable';
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserDevice_Result exec()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserDevice_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice_Collection fetchCollection()
	 */
	class EO_UserDevice_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice_Collection createCollection()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice wakeUpObject($row)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserDevice_Collection wakeUpCollection($rows)
	 */
	class EO_UserDevice_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Authentication\Internal\UserHitAuthTable:main/lib/authentication/internal/userhitauthtable.php */
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserHitAuth
	 * @see \Bitrix\Main\Authentication\Internal\UserHitAuthTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth resetUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getHash()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth setHash(\string|\Bitrix\Main\DB\SqlExpression $hash)
	 * @method bool hasHash()
	 * @method bool isHashFilled()
	 * @method bool isHashChanged()
	 * @method \string remindActualHash()
	 * @method \string requireHash()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth resetHash()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth unsetHash()
	 * @method \string fillHash()
	 * @method \string getUrl()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth setUrl(\string|\Bitrix\Main\DB\SqlExpression $url)
	 * @method bool hasUrl()
	 * @method bool isUrlFilled()
	 * @method bool isUrlChanged()
	 * @method \string remindActualUrl()
	 * @method \string requireUrl()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth resetUrl()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth unsetUrl()
	 * @method \string fillUrl()
	 * @method \string getSiteId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth resetSiteId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth resetTimestampX()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \Bitrix\Main\Type\DateTime getValidUntil()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth setValidUntil(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $validUntil)
	 * @method bool hasValidUntil()
	 * @method bool isValidUntilFilled()
	 * @method bool isValidUntilChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualValidUntil()
	 * @method \Bitrix\Main\Type\DateTime requireValidUntil()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth resetValidUntil()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth unsetValidUntil()
	 * @method \Bitrix\Main\Type\DateTime fillValidUntil()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth resetUser()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth unsetUser()
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
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth set($fieldName, $value)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth reset($fieldName)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserHitAuth wakeUp($data)
	 */
	class EO_UserHitAuth {
		/* @var \Bitrix\Main\Authentication\Internal\UserHitAuthTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserHitAuthTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserHitAuth_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getHashList()
	 * @method \string[] fillHash()
	 * @method \string[] getUrlList()
	 * @method \string[] fillUrl()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \Bitrix\Main\Type\DateTime[] getValidUntilList()
	 * @method \Bitrix\Main\Type\DateTime[] fillValidUntil()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Authentication\Internal\EO_UserHitAuth $object)
	 * @method bool has(\Bitrix\Main\Authentication\Internal\EO_UserHitAuth $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth getByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth[] getAll()
	 * @method bool remove(\Bitrix\Main\Authentication\Internal\EO_UserHitAuth $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserHitAuth_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserHitAuth_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Authentication\Internal\UserHitAuthTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserHitAuthTable';
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserHitAuth_Result exec()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserHitAuth_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth_Collection fetchCollection()
	 */
	class EO_UserHitAuth_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth_Collection createCollection()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth wakeUpObject($row)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserHitAuth_Collection wakeUpCollection($rows)
	 */
	class EO_UserHitAuth_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Authentication\Internal\UserPasswordTable:main/lib/authentication/internal/userpasswordtable.php */
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserPassword
	 * @see \Bitrix\Main\Authentication\Internal\UserPasswordTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword resetUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getPassword()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword setPassword(\string|\Bitrix\Main\DB\SqlExpression $password)
	 * @method bool hasPassword()
	 * @method bool isPasswordFilled()
	 * @method bool isPasswordChanged()
	 * @method \string remindActualPassword()
	 * @method \string requirePassword()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword resetPassword()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword unsetPassword()
	 * @method \string fillPassword()
	 * @method \Bitrix\Main\Type\DateTime getDateChange()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword setDateChange(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateChange)
	 * @method bool hasDateChange()
	 * @method bool isDateChangeFilled()
	 * @method bool isDateChangeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateChange()
	 * @method \Bitrix\Main\Type\DateTime requireDateChange()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword resetDateChange()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword unsetDateChange()
	 * @method \Bitrix\Main\Type\DateTime fillDateChange()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword resetUser()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword unsetUser()
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
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword set($fieldName, $value)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword reset($fieldName)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserPassword wakeUp($data)
	 */
	class EO_UserPassword {
		/* @var \Bitrix\Main\Authentication\Internal\UserPasswordTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserPasswordTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserPassword_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getPasswordList()
	 * @method \string[] fillPassword()
	 * @method \Bitrix\Main\Type\DateTime[] getDateChangeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateChange()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Authentication\Internal\EO_UserPassword $object)
	 * @method bool has(\Bitrix\Main\Authentication\Internal\EO_UserPassword $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword getByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword[] getAll()
	 * @method bool remove(\Bitrix\Main\Authentication\Internal\EO_UserPassword $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserPassword_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserPassword_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Authentication\Internal\UserPasswordTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserPasswordTable';
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserPassword_Result exec()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserPassword_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword_Collection fetchCollection()
	 */
	class EO_UserPassword_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword_Collection createCollection()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword wakeUpObject($row)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserPassword_Collection wakeUpCollection($rows)
	 */
	class EO_UserPassword_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Authentication\Internal\UserStoredAuthTable:main/lib/authentication/internal/userstoredauthtable.php */
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserStoredAuth
	 * @see \Bitrix\Main\Authentication\Internal\UserStoredAuthTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth resetUserId()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\Type\DateTime getDateReg()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth setDateReg(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateReg)
	 * @method bool hasDateReg()
	 * @method bool isDateRegFilled()
	 * @method bool isDateRegChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateReg()
	 * @method \Bitrix\Main\Type\DateTime requireDateReg()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth resetDateReg()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth unsetDateReg()
	 * @method \Bitrix\Main\Type\DateTime fillDateReg()
	 * @method \Bitrix\Main\Type\DateTime getLastAuth()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth setLastAuth(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastAuth)
	 * @method bool hasLastAuth()
	 * @method bool isLastAuthFilled()
	 * @method bool isLastAuthChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastAuth()
	 * @method \Bitrix\Main\Type\DateTime requireLastAuth()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth resetLastAuth()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth unsetLastAuth()
	 * @method \Bitrix\Main\Type\DateTime fillLastAuth()
	 * @method \string getStoredHash()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth setStoredHash(\string|\Bitrix\Main\DB\SqlExpression $storedHash)
	 * @method bool hasStoredHash()
	 * @method bool isStoredHashFilled()
	 * @method bool isStoredHashChanged()
	 * @method \string remindActualStoredHash()
	 * @method \string requireStoredHash()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth resetStoredHash()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth unsetStoredHash()
	 * @method \string fillStoredHash()
	 * @method \boolean getTempHash()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth setTempHash(\boolean|\Bitrix\Main\DB\SqlExpression $tempHash)
	 * @method bool hasTempHash()
	 * @method bool isTempHashFilled()
	 * @method bool isTempHashChanged()
	 * @method \boolean remindActualTempHash()
	 * @method \boolean requireTempHash()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth resetTempHash()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth unsetTempHash()
	 * @method \boolean fillTempHash()
	 * @method \int getIpAddr()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth setIpAddr(\int|\Bitrix\Main\DB\SqlExpression $ipAddr)
	 * @method bool hasIpAddr()
	 * @method bool isIpAddrFilled()
	 * @method bool isIpAddrChanged()
	 * @method \int remindActualIpAddr()
	 * @method \int requireIpAddr()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth resetIpAddr()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth unsetIpAddr()
	 * @method \int fillIpAddr()
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
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth set($fieldName, $value)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth reset($fieldName)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth wakeUp($data)
	 */
	class EO_UserStoredAuth {
		/* @var \Bitrix\Main\Authentication\Internal\UserStoredAuthTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserStoredAuthTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * EO_UserStoredAuth_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateRegList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateReg()
	 * @method \Bitrix\Main\Type\DateTime[] getLastAuthList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastAuth()
	 * @method \string[] getStoredHashList()
	 * @method \string[] fillStoredHash()
	 * @method \boolean[] getTempHashList()
	 * @method \boolean[] fillTempHash()
	 * @method \int[] getIpAddrList()
	 * @method \int[] fillIpAddr()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Authentication\Internal\EO_UserStoredAuth $object)
	 * @method bool has(\Bitrix\Main\Authentication\Internal\EO_UserStoredAuth $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth getByPrimary($primary)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth[] getAll()
	 * @method bool remove(\Bitrix\Main\Authentication\Internal\EO_UserStoredAuth $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserStoredAuth_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Authentication\Internal\UserStoredAuthTable */
		static public $dataClass = '\Bitrix\Main\Authentication\Internal\UserStoredAuthTable';
	}
}
namespace Bitrix\Main\Authentication\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserStoredAuth_Result exec()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserStoredAuth_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth fetchObject()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth_Collection fetchCollection()
	 */
	class EO_UserStoredAuth_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth_Collection createCollection()
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth wakeUpObject($row)
	 * @method \Bitrix\Main\Authentication\Internal\EO_UserStoredAuth_Collection wakeUpCollection($rows)
	 */
	class EO_UserStoredAuth_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Component\ParametersTable:main/lib/component/parameters.php */
namespace Bitrix\Main\Component {
	/**
	 * EO_Parameters
	 * @see \Bitrix\Main\Component\ParametersTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Component\EO_Parameters setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Main\Component\EO_Parameters setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Main\Component\EO_Parameters resetSiteId()
	 * @method \Bitrix\Main\Component\EO_Parameters unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \string getComponentName()
	 * @method \Bitrix\Main\Component\EO_Parameters setComponentName(\string|\Bitrix\Main\DB\SqlExpression $componentName)
	 * @method bool hasComponentName()
	 * @method bool isComponentNameFilled()
	 * @method bool isComponentNameChanged()
	 * @method \string remindActualComponentName()
	 * @method \string requireComponentName()
	 * @method \Bitrix\Main\Component\EO_Parameters resetComponentName()
	 * @method \Bitrix\Main\Component\EO_Parameters unsetComponentName()
	 * @method \string fillComponentName()
	 * @method \string getTemplateName()
	 * @method \Bitrix\Main\Component\EO_Parameters setTemplateName(\string|\Bitrix\Main\DB\SqlExpression $templateName)
	 * @method bool hasTemplateName()
	 * @method bool isTemplateNameFilled()
	 * @method bool isTemplateNameChanged()
	 * @method \string remindActualTemplateName()
	 * @method \string requireTemplateName()
	 * @method \Bitrix\Main\Component\EO_Parameters resetTemplateName()
	 * @method \Bitrix\Main\Component\EO_Parameters unsetTemplateName()
	 * @method \string fillTemplateName()
	 * @method \string getRealPath()
	 * @method \Bitrix\Main\Component\EO_Parameters setRealPath(\string|\Bitrix\Main\DB\SqlExpression $realPath)
	 * @method bool hasRealPath()
	 * @method bool isRealPathFilled()
	 * @method bool isRealPathChanged()
	 * @method \string remindActualRealPath()
	 * @method \string requireRealPath()
	 * @method \Bitrix\Main\Component\EO_Parameters resetRealPath()
	 * @method \Bitrix\Main\Component\EO_Parameters unsetRealPath()
	 * @method \string fillRealPath()
	 * @method \boolean getSefMode()
	 * @method \Bitrix\Main\Component\EO_Parameters setSefMode(\boolean|\Bitrix\Main\DB\SqlExpression $sefMode)
	 * @method bool hasSefMode()
	 * @method bool isSefModeFilled()
	 * @method bool isSefModeChanged()
	 * @method \boolean remindActualSefMode()
	 * @method \boolean requireSefMode()
	 * @method \Bitrix\Main\Component\EO_Parameters resetSefMode()
	 * @method \Bitrix\Main\Component\EO_Parameters unsetSefMode()
	 * @method \boolean fillSefMode()
	 * @method \string getSefFolder()
	 * @method \Bitrix\Main\Component\EO_Parameters setSefFolder(\string|\Bitrix\Main\DB\SqlExpression $sefFolder)
	 * @method bool hasSefFolder()
	 * @method bool isSefFolderFilled()
	 * @method bool isSefFolderChanged()
	 * @method \string remindActualSefFolder()
	 * @method \string requireSefFolder()
	 * @method \Bitrix\Main\Component\EO_Parameters resetSefFolder()
	 * @method \Bitrix\Main\Component\EO_Parameters unsetSefFolder()
	 * @method \string fillSefFolder()
	 * @method \int getStartChar()
	 * @method \Bitrix\Main\Component\EO_Parameters setStartChar(\int|\Bitrix\Main\DB\SqlExpression $startChar)
	 * @method bool hasStartChar()
	 * @method bool isStartCharFilled()
	 * @method bool isStartCharChanged()
	 * @method \int remindActualStartChar()
	 * @method \int requireStartChar()
	 * @method \Bitrix\Main\Component\EO_Parameters resetStartChar()
	 * @method \Bitrix\Main\Component\EO_Parameters unsetStartChar()
	 * @method \int fillStartChar()
	 * @method \int getEndChar()
	 * @method \Bitrix\Main\Component\EO_Parameters setEndChar(\int|\Bitrix\Main\DB\SqlExpression $endChar)
	 * @method bool hasEndChar()
	 * @method bool isEndCharFilled()
	 * @method bool isEndCharChanged()
	 * @method \int remindActualEndChar()
	 * @method \int requireEndChar()
	 * @method \Bitrix\Main\Component\EO_Parameters resetEndChar()
	 * @method \Bitrix\Main\Component\EO_Parameters unsetEndChar()
	 * @method \int fillEndChar()
	 * @method \string getParameters()
	 * @method \Bitrix\Main\Component\EO_Parameters setParameters(\string|\Bitrix\Main\DB\SqlExpression $parameters)
	 * @method bool hasParameters()
	 * @method bool isParametersFilled()
	 * @method bool isParametersChanged()
	 * @method \string remindActualParameters()
	 * @method \string requireParameters()
	 * @method \Bitrix\Main\Component\EO_Parameters resetParameters()
	 * @method \Bitrix\Main\Component\EO_Parameters unsetParameters()
	 * @method \string fillParameters()
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
	 * @method \Bitrix\Main\Component\EO_Parameters set($fieldName, $value)
	 * @method \Bitrix\Main\Component\EO_Parameters reset($fieldName)
	 * @method \Bitrix\Main\Component\EO_Parameters unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Component\EO_Parameters wakeUp($data)
	 */
	class EO_Parameters {
		/* @var \Bitrix\Main\Component\ParametersTable */
		static public $dataClass = '\Bitrix\Main\Component\ParametersTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Component {
	/**
	 * EO_Parameters_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \string[] getComponentNameList()
	 * @method \string[] fillComponentName()
	 * @method \string[] getTemplateNameList()
	 * @method \string[] fillTemplateName()
	 * @method \string[] getRealPathList()
	 * @method \string[] fillRealPath()
	 * @method \boolean[] getSefModeList()
	 * @method \boolean[] fillSefMode()
	 * @method \string[] getSefFolderList()
	 * @method \string[] fillSefFolder()
	 * @method \int[] getStartCharList()
	 * @method \int[] fillStartChar()
	 * @method \int[] getEndCharList()
	 * @method \int[] fillEndChar()
	 * @method \string[] getParametersList()
	 * @method \string[] fillParameters()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Component\EO_Parameters $object)
	 * @method bool has(\Bitrix\Main\Component\EO_Parameters $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Component\EO_Parameters getByPrimary($primary)
	 * @method \Bitrix\Main\Component\EO_Parameters[] getAll()
	 * @method bool remove(\Bitrix\Main\Component\EO_Parameters $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Component\EO_Parameters_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Component\EO_Parameters current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Parameters_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Component\ParametersTable */
		static public $dataClass = '\Bitrix\Main\Component\ParametersTable';
	}
}
namespace Bitrix\Main\Component {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Parameters_Result exec()
	 * @method \Bitrix\Main\Component\EO_Parameters fetchObject()
	 * @method \Bitrix\Main\Component\EO_Parameters_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Parameters_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Component\EO_Parameters fetchObject()
	 * @method \Bitrix\Main\Component\EO_Parameters_Collection fetchCollection()
	 */
	class EO_Parameters_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Component\EO_Parameters createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Component\EO_Parameters_Collection createCollection()
	 * @method \Bitrix\Main\Component\EO_Parameters wakeUpObject($row)
	 * @method \Bitrix\Main\Component\EO_Parameters_Collection wakeUpCollection($rows)
	 */
	class EO_Parameters_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Composite\Debug\Model\LogTable:main/lib/composite/debug/model/log.php */
namespace Bitrix\Main\Composite\Debug\Model {
	/**
	 * EO_Log
	 * @see \Bitrix\Main\Composite\Debug\Model\LogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getHost()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setHost(\string|\Bitrix\Main\DB\SqlExpression $host)
	 * @method bool hasHost()
	 * @method bool isHostFilled()
	 * @method bool isHostChanged()
	 * @method \string remindActualHost()
	 * @method \string requireHost()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetHost()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetHost()
	 * @method \string fillHost()
	 * @method \string getUri()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setUri(\string|\Bitrix\Main\DB\SqlExpression $uri)
	 * @method bool hasUri()
	 * @method bool isUriFilled()
	 * @method bool isUriChanged()
	 * @method \string remindActualUri()
	 * @method \string requireUri()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetUri()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetUri()
	 * @method \string fillUri()
	 * @method \string getTitle()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetTitle()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetTitle()
	 * @method \string fillTitle()
	 * @method \Bitrix\Main\Type\DateTime getCreated()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setCreated(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $created)
	 * @method bool hasCreated()
	 * @method bool isCreatedFilled()
	 * @method bool isCreatedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreated()
	 * @method \Bitrix\Main\Type\DateTime requireCreated()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetCreated()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetCreated()
	 * @method \Bitrix\Main\Type\DateTime fillCreated()
	 * @method \string getType()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetType()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetType()
	 * @method \string fillType()
	 * @method \string getMessage()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetMessage()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetMessage()
	 * @method \string fillMessage()
	 * @method \string getMessageShort()
	 * @method \string remindActualMessageShort()
	 * @method \string requireMessageShort()
	 * @method bool hasMessageShort()
	 * @method bool isMessageShortFilled()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetMessageShort()
	 * @method \string fillMessageShort()
	 * @method \boolean getAjax()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setAjax(\boolean|\Bitrix\Main\DB\SqlExpression $ajax)
	 * @method bool hasAjax()
	 * @method bool isAjaxFilled()
	 * @method bool isAjaxChanged()
	 * @method \boolean remindActualAjax()
	 * @method \boolean requireAjax()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetAjax()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetAjax()
	 * @method \boolean fillAjax()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetUserId()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetUser()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \int getPageId()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log setPageId(\int|\Bitrix\Main\DB\SqlExpression $pageId)
	 * @method bool hasPageId()
	 * @method bool isPageIdFilled()
	 * @method bool isPageIdChanged()
	 * @method \int remindActualPageId()
	 * @method \int requirePageId()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log resetPageId()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unsetPageId()
	 * @method \int fillPageId()
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
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log set($fieldName, $value)
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log reset($fieldName)
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Composite\Debug\Model\EO_Log wakeUp($data)
	 */
	class EO_Log {
		/* @var \Bitrix\Main\Composite\Debug\Model\LogTable */
		static public $dataClass = '\Bitrix\Main\Composite\Debug\Model\LogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Composite\Debug\Model {
	/**
	 * EO_Log_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getHostList()
	 * @method \string[] fillHost()
	 * @method \string[] getUriList()
	 * @method \string[] fillUri()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreated()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \string[] getMessageShortList()
	 * @method \string[] fillMessageShort()
	 * @method \boolean[] getAjaxList()
	 * @method \boolean[] fillAjax()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \int[] getPageIdList()
	 * @method \int[] fillPageId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Composite\Debug\Model\EO_Log $object)
	 * @method bool has(\Bitrix\Main\Composite\Debug\Model\EO_Log $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log getByPrimary($primary)
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log[] getAll()
	 * @method bool remove(\Bitrix\Main\Composite\Debug\Model\EO_Log $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Composite\Debug\Model\EO_Log_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Log_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Composite\Debug\Model\LogTable */
		static public $dataClass = '\Bitrix\Main\Composite\Debug\Model\LogTable';
	}
}
namespace Bitrix\Main\Composite\Debug\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Log_Result exec()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log fetchObject()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Log_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log fetchObject()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log_Collection fetchCollection()
	 */
	class EO_Log_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log_Collection createCollection()
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log wakeUpObject($row)
	 * @method \Bitrix\Main\Composite\Debug\Model\EO_Log_Collection wakeUpCollection($rows)
	 */
	class EO_Log_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Composite\Internals\Model\PageTable:main/lib/composite/internals/model/page.php */
namespace Bitrix\Main\Composite\Internals\Model {
	/**
	 * EO_Page
	 * @see \Bitrix\Main\Composite\Internals\Model\PageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCacheKey()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setCacheKey(\string|\Bitrix\Main\DB\SqlExpression $cacheKey)
	 * @method bool hasCacheKey()
	 * @method bool isCacheKeyFilled()
	 * @method bool isCacheKeyChanged()
	 * @method \string remindActualCacheKey()
	 * @method \string requireCacheKey()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetCacheKey()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetCacheKey()
	 * @method \string fillCacheKey()
	 * @method \string getHost()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setHost(\string|\Bitrix\Main\DB\SqlExpression $host)
	 * @method bool hasHost()
	 * @method bool isHostFilled()
	 * @method bool isHostChanged()
	 * @method \string remindActualHost()
	 * @method \string requireHost()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetHost()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetHost()
	 * @method \string fillHost()
	 * @method \string getUri()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setUri(\string|\Bitrix\Main\DB\SqlExpression $uri)
	 * @method bool hasUri()
	 * @method bool isUriFilled()
	 * @method bool isUriChanged()
	 * @method \string remindActualUri()
	 * @method \string requireUri()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetUri()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetUri()
	 * @method \string fillUri()
	 * @method \string getTitle()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetTitle()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetTitle()
	 * @method \string fillTitle()
	 * @method \Bitrix\Main\Type\DateTime getCreated()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setCreated(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $created)
	 * @method bool hasCreated()
	 * @method bool isCreatedFilled()
	 * @method bool isCreatedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreated()
	 * @method \Bitrix\Main\Type\DateTime requireCreated()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetCreated()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetCreated()
	 * @method \Bitrix\Main\Type\DateTime fillCreated()
	 * @method \Bitrix\Main\Type\DateTime getChanged()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setChanged(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $changed)
	 * @method bool hasChanged()
	 * @method bool isChangedFilled()
	 * @method bool isChangedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualChanged()
	 * @method \Bitrix\Main\Type\DateTime requireChanged()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetChanged()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetChanged()
	 * @method \Bitrix\Main\Type\DateTime fillChanged()
	 * @method \Bitrix\Main\Type\DateTime getLastViewed()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setLastViewed(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastViewed)
	 * @method bool hasLastViewed()
	 * @method bool isLastViewedFilled()
	 * @method bool isLastViewedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastViewed()
	 * @method \Bitrix\Main\Type\DateTime requireLastViewed()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetLastViewed()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetLastViewed()
	 * @method \Bitrix\Main\Type\DateTime fillLastViewed()
	 * @method \int getViews()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setViews(\int|\Bitrix\Main\DB\SqlExpression $views)
	 * @method bool hasViews()
	 * @method bool isViewsFilled()
	 * @method bool isViewsChanged()
	 * @method \int remindActualViews()
	 * @method \int requireViews()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetViews()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetViews()
	 * @method \int fillViews()
	 * @method \int getRewrites()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setRewrites(\int|\Bitrix\Main\DB\SqlExpression $rewrites)
	 * @method bool hasRewrites()
	 * @method bool isRewritesFilled()
	 * @method bool isRewritesChanged()
	 * @method \int remindActualRewrites()
	 * @method \int requireRewrites()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetRewrites()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetRewrites()
	 * @method \int fillRewrites()
	 * @method \int getSize()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page setSize(\int|\Bitrix\Main\DB\SqlExpression $size)
	 * @method bool hasSize()
	 * @method bool isSizeFilled()
	 * @method bool isSizeChanged()
	 * @method \int remindActualSize()
	 * @method \int requireSize()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page resetSize()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unsetSize()
	 * @method \int fillSize()
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
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page set($fieldName, $value)
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page reset($fieldName)
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Composite\Internals\Model\EO_Page wakeUp($data)
	 */
	class EO_Page {
		/* @var \Bitrix\Main\Composite\Internals\Model\PageTable */
		static public $dataClass = '\Bitrix\Main\Composite\Internals\Model\PageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Composite\Internals\Model {
	/**
	 * EO_Page_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCacheKeyList()
	 * @method \string[] fillCacheKey()
	 * @method \string[] getHostList()
	 * @method \string[] fillHost()
	 * @method \string[] getUriList()
	 * @method \string[] fillUri()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreated()
	 * @method \Bitrix\Main\Type\DateTime[] getChangedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillChanged()
	 * @method \Bitrix\Main\Type\DateTime[] getLastViewedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastViewed()
	 * @method \int[] getViewsList()
	 * @method \int[] fillViews()
	 * @method \int[] getRewritesList()
	 * @method \int[] fillRewrites()
	 * @method \int[] getSizeList()
	 * @method \int[] fillSize()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Composite\Internals\Model\EO_Page $object)
	 * @method bool has(\Bitrix\Main\Composite\Internals\Model\EO_Page $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page getByPrimary($primary)
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page[] getAll()
	 * @method bool remove(\Bitrix\Main\Composite\Internals\Model\EO_Page $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Composite\Internals\Model\EO_Page_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Page_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Composite\Internals\Model\PageTable */
		static public $dataClass = '\Bitrix\Main\Composite\Internals\Model\PageTable';
	}
}
namespace Bitrix\Main\Composite\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Page_Result exec()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page fetchObject()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Page_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page fetchObject()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page_Collection fetchCollection()
	 */
	class EO_Page_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page_Collection createCollection()
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page wakeUpObject($row)
	 * @method \Bitrix\Main\Composite\Internals\Model\EO_Page_Collection wakeUpCollection($rows)
	 */
	class EO_Page_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Localization\CultureTable:main/lib/localization/culture.php */
namespace Bitrix\Main\Localization {
	/**
	 * Culture
	 * @see \Bitrix\Main\Localization\CultureTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Context\Culture setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Main\Context\Culture setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Main\Context\Culture resetCode()
	 * @method \Bitrix\Main\Context\Culture unsetCode()
	 * @method \string fillCode()
	 * @method \string getName()
	 * @method \Bitrix\Main\Context\Culture setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\Context\Culture resetName()
	 * @method \Bitrix\Main\Context\Culture unsetName()
	 * @method \string fillName()
	 * @method \string getFormatDate()
	 * @method \Bitrix\Main\Context\Culture setFormatDate(\string|\Bitrix\Main\DB\SqlExpression $formatDate)
	 * @method bool hasFormatDate()
	 * @method bool isFormatDateFilled()
	 * @method bool isFormatDateChanged()
	 * @method \string remindActualFormatDate()
	 * @method \string requireFormatDate()
	 * @method \Bitrix\Main\Context\Culture resetFormatDate()
	 * @method \Bitrix\Main\Context\Culture unsetFormatDate()
	 * @method \string fillFormatDate()
	 * @method \string getFormatDatetime()
	 * @method \Bitrix\Main\Context\Culture setFormatDatetime(\string|\Bitrix\Main\DB\SqlExpression $formatDatetime)
	 * @method bool hasFormatDatetime()
	 * @method bool isFormatDatetimeFilled()
	 * @method bool isFormatDatetimeChanged()
	 * @method \string remindActualFormatDatetime()
	 * @method \string requireFormatDatetime()
	 * @method \Bitrix\Main\Context\Culture resetFormatDatetime()
	 * @method \Bitrix\Main\Context\Culture unsetFormatDatetime()
	 * @method \string fillFormatDatetime()
	 * @method \string getFormatName()
	 * @method \Bitrix\Main\Context\Culture setFormatName(\string|\Bitrix\Main\DB\SqlExpression $formatName)
	 * @method bool hasFormatName()
	 * @method bool isFormatNameFilled()
	 * @method bool isFormatNameChanged()
	 * @method \string remindActualFormatName()
	 * @method \string requireFormatName()
	 * @method \Bitrix\Main\Context\Culture resetFormatName()
	 * @method \Bitrix\Main\Context\Culture unsetFormatName()
	 * @method \string fillFormatName()
	 * @method \int getWeekStart()
	 * @method \Bitrix\Main\Context\Culture setWeekStart(\int|\Bitrix\Main\DB\SqlExpression $weekStart)
	 * @method bool hasWeekStart()
	 * @method bool isWeekStartFilled()
	 * @method bool isWeekStartChanged()
	 * @method \int remindActualWeekStart()
	 * @method \int requireWeekStart()
	 * @method \Bitrix\Main\Context\Culture resetWeekStart()
	 * @method \Bitrix\Main\Context\Culture unsetWeekStart()
	 * @method \int fillWeekStart()
	 * @method \string getCharset()
	 * @method \Bitrix\Main\Context\Culture setCharset(\string|\Bitrix\Main\DB\SqlExpression $charset)
	 * @method bool hasCharset()
	 * @method bool isCharsetFilled()
	 * @method bool isCharsetChanged()
	 * @method \string remindActualCharset()
	 * @method \string requireCharset()
	 * @method \Bitrix\Main\Context\Culture resetCharset()
	 * @method \Bitrix\Main\Context\Culture unsetCharset()
	 * @method \string fillCharset()
	 * @method \boolean getDirection()
	 * @method \Bitrix\Main\Context\Culture setDirection(\boolean|\Bitrix\Main\DB\SqlExpression $direction)
	 * @method bool hasDirection()
	 * @method bool isDirectionFilled()
	 * @method bool isDirectionChanged()
	 * @method \boolean remindActualDirection()
	 * @method \boolean requireDirection()
	 * @method \Bitrix\Main\Context\Culture resetDirection()
	 * @method \Bitrix\Main\Context\Culture unsetDirection()
	 * @method \boolean fillDirection()
	 * @method \string getShortDateFormat()
	 * @method \Bitrix\Main\Context\Culture setShortDateFormat(\string|\Bitrix\Main\DB\SqlExpression $shortDateFormat)
	 * @method bool hasShortDateFormat()
	 * @method bool isShortDateFormatFilled()
	 * @method bool isShortDateFormatChanged()
	 * @method \string remindActualShortDateFormat()
	 * @method \string requireShortDateFormat()
	 * @method \Bitrix\Main\Context\Culture resetShortDateFormat()
	 * @method \Bitrix\Main\Context\Culture unsetShortDateFormat()
	 * @method \string fillShortDateFormat()
	 * @method \string getMediumDateFormat()
	 * @method \Bitrix\Main\Context\Culture setMediumDateFormat(\string|\Bitrix\Main\DB\SqlExpression $mediumDateFormat)
	 * @method bool hasMediumDateFormat()
	 * @method bool isMediumDateFormatFilled()
	 * @method bool isMediumDateFormatChanged()
	 * @method \string remindActualMediumDateFormat()
	 * @method \string requireMediumDateFormat()
	 * @method \Bitrix\Main\Context\Culture resetMediumDateFormat()
	 * @method \Bitrix\Main\Context\Culture unsetMediumDateFormat()
	 * @method \string fillMediumDateFormat()
	 * @method \string getLongDateFormat()
	 * @method \Bitrix\Main\Context\Culture setLongDateFormat(\string|\Bitrix\Main\DB\SqlExpression $longDateFormat)
	 * @method bool hasLongDateFormat()
	 * @method bool isLongDateFormatFilled()
	 * @method bool isLongDateFormatChanged()
	 * @method \string remindActualLongDateFormat()
	 * @method \string requireLongDateFormat()
	 * @method \Bitrix\Main\Context\Culture resetLongDateFormat()
	 * @method \Bitrix\Main\Context\Culture unsetLongDateFormat()
	 * @method \string fillLongDateFormat()
	 * @method \string getFullDateFormat()
	 * @method \Bitrix\Main\Context\Culture setFullDateFormat(\string|\Bitrix\Main\DB\SqlExpression $fullDateFormat)
	 * @method bool hasFullDateFormat()
	 * @method bool isFullDateFormatFilled()
	 * @method bool isFullDateFormatChanged()
	 * @method \string remindActualFullDateFormat()
	 * @method \string requireFullDateFormat()
	 * @method \Bitrix\Main\Context\Culture resetFullDateFormat()
	 * @method \Bitrix\Main\Context\Culture unsetFullDateFormat()
	 * @method \string fillFullDateFormat()
	 * @method \string getDayMonthFormat()
	 * @method \Bitrix\Main\Context\Culture setDayMonthFormat(\string|\Bitrix\Main\DB\SqlExpression $dayMonthFormat)
	 * @method bool hasDayMonthFormat()
	 * @method bool isDayMonthFormatFilled()
	 * @method bool isDayMonthFormatChanged()
	 * @method \string remindActualDayMonthFormat()
	 * @method \string requireDayMonthFormat()
	 * @method \Bitrix\Main\Context\Culture resetDayMonthFormat()
	 * @method \Bitrix\Main\Context\Culture unsetDayMonthFormat()
	 * @method \string fillDayMonthFormat()
	 * @method \string getDayShortMonthFormat()
	 * @method \Bitrix\Main\Context\Culture setDayShortMonthFormat(\string|\Bitrix\Main\DB\SqlExpression $dayShortMonthFormat)
	 * @method bool hasDayShortMonthFormat()
	 * @method bool isDayShortMonthFormatFilled()
	 * @method bool isDayShortMonthFormatChanged()
	 * @method \string remindActualDayShortMonthFormat()
	 * @method \string requireDayShortMonthFormat()
	 * @method \Bitrix\Main\Context\Culture resetDayShortMonthFormat()
	 * @method \Bitrix\Main\Context\Culture unsetDayShortMonthFormat()
	 * @method \string fillDayShortMonthFormat()
	 * @method \string getDayOfWeekMonthFormat()
	 * @method \Bitrix\Main\Context\Culture setDayOfWeekMonthFormat(\string|\Bitrix\Main\DB\SqlExpression $dayOfWeekMonthFormat)
	 * @method bool hasDayOfWeekMonthFormat()
	 * @method bool isDayOfWeekMonthFormatFilled()
	 * @method bool isDayOfWeekMonthFormatChanged()
	 * @method \string remindActualDayOfWeekMonthFormat()
	 * @method \string requireDayOfWeekMonthFormat()
	 * @method \Bitrix\Main\Context\Culture resetDayOfWeekMonthFormat()
	 * @method \Bitrix\Main\Context\Culture unsetDayOfWeekMonthFormat()
	 * @method \string fillDayOfWeekMonthFormat()
	 * @method \string getShortDayOfWeekMonthFormat()
	 * @method \Bitrix\Main\Context\Culture setShortDayOfWeekMonthFormat(\string|\Bitrix\Main\DB\SqlExpression $shortDayOfWeekMonthFormat)
	 * @method bool hasShortDayOfWeekMonthFormat()
	 * @method bool isShortDayOfWeekMonthFormatFilled()
	 * @method bool isShortDayOfWeekMonthFormatChanged()
	 * @method \string remindActualShortDayOfWeekMonthFormat()
	 * @method \string requireShortDayOfWeekMonthFormat()
	 * @method \Bitrix\Main\Context\Culture resetShortDayOfWeekMonthFormat()
	 * @method \Bitrix\Main\Context\Culture unsetShortDayOfWeekMonthFormat()
	 * @method \string fillShortDayOfWeekMonthFormat()
	 * @method \string getShortDayOfWeekShortMonthFormat()
	 * @method \Bitrix\Main\Context\Culture setShortDayOfWeekShortMonthFormat(\string|\Bitrix\Main\DB\SqlExpression $shortDayOfWeekShortMonthFormat)
	 * @method bool hasShortDayOfWeekShortMonthFormat()
	 * @method bool isShortDayOfWeekShortMonthFormatFilled()
	 * @method bool isShortDayOfWeekShortMonthFormatChanged()
	 * @method \string remindActualShortDayOfWeekShortMonthFormat()
	 * @method \string requireShortDayOfWeekShortMonthFormat()
	 * @method \Bitrix\Main\Context\Culture resetShortDayOfWeekShortMonthFormat()
	 * @method \Bitrix\Main\Context\Culture unsetShortDayOfWeekShortMonthFormat()
	 * @method \string fillShortDayOfWeekShortMonthFormat()
	 * @method \string getShortTimeFormat()
	 * @method \Bitrix\Main\Context\Culture setShortTimeFormat(\string|\Bitrix\Main\DB\SqlExpression $shortTimeFormat)
	 * @method bool hasShortTimeFormat()
	 * @method bool isShortTimeFormatFilled()
	 * @method bool isShortTimeFormatChanged()
	 * @method \string remindActualShortTimeFormat()
	 * @method \string requireShortTimeFormat()
	 * @method \Bitrix\Main\Context\Culture resetShortTimeFormat()
	 * @method \Bitrix\Main\Context\Culture unsetShortTimeFormat()
	 * @method \string fillShortTimeFormat()
	 * @method \string getLongTimeFormat()
	 * @method \Bitrix\Main\Context\Culture setLongTimeFormat(\string|\Bitrix\Main\DB\SqlExpression $longTimeFormat)
	 * @method bool hasLongTimeFormat()
	 * @method bool isLongTimeFormatFilled()
	 * @method bool isLongTimeFormatChanged()
	 * @method \string remindActualLongTimeFormat()
	 * @method \string requireLongTimeFormat()
	 * @method \Bitrix\Main\Context\Culture resetLongTimeFormat()
	 * @method \Bitrix\Main\Context\Culture unsetLongTimeFormat()
	 * @method \string fillLongTimeFormat()
	 * @method \string getAmValue()
	 * @method \Bitrix\Main\Context\Culture setAmValue(\string|\Bitrix\Main\DB\SqlExpression $amValue)
	 * @method bool hasAmValue()
	 * @method bool isAmValueFilled()
	 * @method bool isAmValueChanged()
	 * @method \string remindActualAmValue()
	 * @method \string requireAmValue()
	 * @method \Bitrix\Main\Context\Culture resetAmValue()
	 * @method \Bitrix\Main\Context\Culture unsetAmValue()
	 * @method \string fillAmValue()
	 * @method \string getPmValue()
	 * @method \Bitrix\Main\Context\Culture setPmValue(\string|\Bitrix\Main\DB\SqlExpression $pmValue)
	 * @method bool hasPmValue()
	 * @method bool isPmValueFilled()
	 * @method bool isPmValueChanged()
	 * @method \string remindActualPmValue()
	 * @method \string requirePmValue()
	 * @method \Bitrix\Main\Context\Culture resetPmValue()
	 * @method \Bitrix\Main\Context\Culture unsetPmValue()
	 * @method \string fillPmValue()
	 * @method \string getNumberThousandsSeparator()
	 * @method \Bitrix\Main\Context\Culture setNumberThousandsSeparator(\string|\Bitrix\Main\DB\SqlExpression $numberThousandsSeparator)
	 * @method bool hasNumberThousandsSeparator()
	 * @method bool isNumberThousandsSeparatorFilled()
	 * @method bool isNumberThousandsSeparatorChanged()
	 * @method \string remindActualNumberThousandsSeparator()
	 * @method \string requireNumberThousandsSeparator()
	 * @method \Bitrix\Main\Context\Culture resetNumberThousandsSeparator()
	 * @method \Bitrix\Main\Context\Culture unsetNumberThousandsSeparator()
	 * @method \string fillNumberThousandsSeparator()
	 * @method \string getNumberDecimalSeparator()
	 * @method \Bitrix\Main\Context\Culture setNumberDecimalSeparator(\string|\Bitrix\Main\DB\SqlExpression $numberDecimalSeparator)
	 * @method bool hasNumberDecimalSeparator()
	 * @method bool isNumberDecimalSeparatorFilled()
	 * @method bool isNumberDecimalSeparatorChanged()
	 * @method \string remindActualNumberDecimalSeparator()
	 * @method \string requireNumberDecimalSeparator()
	 * @method \Bitrix\Main\Context\Culture resetNumberDecimalSeparator()
	 * @method \Bitrix\Main\Context\Culture unsetNumberDecimalSeparator()
	 * @method \string fillNumberDecimalSeparator()
	 * @method \int getNumberDecimals()
	 * @method \Bitrix\Main\Context\Culture setNumberDecimals(\int|\Bitrix\Main\DB\SqlExpression $numberDecimals)
	 * @method bool hasNumberDecimals()
	 * @method bool isNumberDecimalsFilled()
	 * @method bool isNumberDecimalsChanged()
	 * @method \int remindActualNumberDecimals()
	 * @method \int requireNumberDecimals()
	 * @method \Bitrix\Main\Context\Culture resetNumberDecimals()
	 * @method \Bitrix\Main\Context\Culture unsetNumberDecimals()
	 * @method \int fillNumberDecimals()
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
	 * @method \Bitrix\Main\Context\Culture set($fieldName, $value)
	 * @method \Bitrix\Main\Context\Culture reset($fieldName)
	 * @method \Bitrix\Main\Context\Culture unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Context\Culture wakeUp($data)
	 */
	class EO_Culture {
		/* @var \Bitrix\Main\Localization\CultureTable */
		static public $dataClass = '\Bitrix\Main\Localization\CultureTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Localization {
	/**
	 * EO_Culture_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getFormatDateList()
	 * @method \string[] fillFormatDate()
	 * @method \string[] getFormatDatetimeList()
	 * @method \string[] fillFormatDatetime()
	 * @method \string[] getFormatNameList()
	 * @method \string[] fillFormatName()
	 * @method \int[] getWeekStartList()
	 * @method \int[] fillWeekStart()
	 * @method \string[] getCharsetList()
	 * @method \string[] fillCharset()
	 * @method \boolean[] getDirectionList()
	 * @method \boolean[] fillDirection()
	 * @method \string[] getShortDateFormatList()
	 * @method \string[] fillShortDateFormat()
	 * @method \string[] getMediumDateFormatList()
	 * @method \string[] fillMediumDateFormat()
	 * @method \string[] getLongDateFormatList()
	 * @method \string[] fillLongDateFormat()
	 * @method \string[] getFullDateFormatList()
	 * @method \string[] fillFullDateFormat()
	 * @method \string[] getDayMonthFormatList()
	 * @method \string[] fillDayMonthFormat()
	 * @method \string[] getDayShortMonthFormatList()
	 * @method \string[] fillDayShortMonthFormat()
	 * @method \string[] getDayOfWeekMonthFormatList()
	 * @method \string[] fillDayOfWeekMonthFormat()
	 * @method \string[] getShortDayOfWeekMonthFormatList()
	 * @method \string[] fillShortDayOfWeekMonthFormat()
	 * @method \string[] getShortDayOfWeekShortMonthFormatList()
	 * @method \string[] fillShortDayOfWeekShortMonthFormat()
	 * @method \string[] getShortTimeFormatList()
	 * @method \string[] fillShortTimeFormat()
	 * @method \string[] getLongTimeFormatList()
	 * @method \string[] fillLongTimeFormat()
	 * @method \string[] getAmValueList()
	 * @method \string[] fillAmValue()
	 * @method \string[] getPmValueList()
	 * @method \string[] fillPmValue()
	 * @method \string[] getNumberThousandsSeparatorList()
	 * @method \string[] fillNumberThousandsSeparator()
	 * @method \string[] getNumberDecimalSeparatorList()
	 * @method \string[] fillNumberDecimalSeparator()
	 * @method \int[] getNumberDecimalsList()
	 * @method \int[] fillNumberDecimals()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Context\Culture $object)
	 * @method bool has(\Bitrix\Main\Context\Culture $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Context\Culture getByPrimary($primary)
	 * @method \Bitrix\Main\Context\Culture[] getAll()
	 * @method bool remove(\Bitrix\Main\Context\Culture $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Localization\EO_Culture_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Context\Culture current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Culture_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Localization\CultureTable */
		static public $dataClass = '\Bitrix\Main\Localization\CultureTable';
	}
}
namespace Bitrix\Main\Localization {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Culture_Result exec()
	 * @method \Bitrix\Main\Context\Culture fetchObject()
	 * @method \Bitrix\Main\Localization\EO_Culture_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Culture_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Context\Culture fetchObject()
	 * @method \Bitrix\Main\Localization\EO_Culture_Collection fetchCollection()
	 */
	class EO_Culture_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Context\Culture createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Localization\EO_Culture_Collection createCollection()
	 * @method \Bitrix\Main\Context\Culture wakeUpObject($row)
	 * @method \Bitrix\Main\Localization\EO_Culture_Collection wakeUpCollection($rows)
	 */
	class EO_Culture_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\EventLog\Internal\EventLogTable:main/lib/eventlog/internal/eventlogtable.php */
namespace Bitrix\Main\EventLog\Internal {
	/**
	 * EO_EventLog
	 * @see \Bitrix\Main\EventLog\Internal\EventLogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetTimestampX()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getSeverity()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setSeverity(\string|\Bitrix\Main\DB\SqlExpression $severity)
	 * @method bool hasSeverity()
	 * @method bool isSeverityFilled()
	 * @method bool isSeverityChanged()
	 * @method \string remindActualSeverity()
	 * @method \string requireSeverity()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetSeverity()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetSeverity()
	 * @method \string fillSeverity()
	 * @method \string getAuditTypeId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setAuditTypeId(\string|\Bitrix\Main\DB\SqlExpression $auditTypeId)
	 * @method bool hasAuditTypeId()
	 * @method bool isAuditTypeIdFilled()
	 * @method bool isAuditTypeIdChanged()
	 * @method \string remindActualAuditTypeId()
	 * @method \string requireAuditTypeId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetAuditTypeId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetAuditTypeId()
	 * @method \string fillAuditTypeId()
	 * @method \string getModuleId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetModuleId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getItemId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setItemId(\string|\Bitrix\Main\DB\SqlExpression $itemId)
	 * @method bool hasItemId()
	 * @method bool isItemIdFilled()
	 * @method bool isItemIdChanged()
	 * @method \string remindActualItemId()
	 * @method \string requireItemId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetItemId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetItemId()
	 * @method \string fillItemId()
	 * @method \string getRemoteAddr()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setRemoteAddr(\string|\Bitrix\Main\DB\SqlExpression $remoteAddr)
	 * @method bool hasRemoteAddr()
	 * @method bool isRemoteAddrFilled()
	 * @method bool isRemoteAddrChanged()
	 * @method \string remindActualRemoteAddr()
	 * @method \string requireRemoteAddr()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetRemoteAddr()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetRemoteAddr()
	 * @method \string fillRemoteAddr()
	 * @method \string getUserAgent()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setUserAgent(\string|\Bitrix\Main\DB\SqlExpression $userAgent)
	 * @method bool hasUserAgent()
	 * @method bool isUserAgentFilled()
	 * @method bool isUserAgentChanged()
	 * @method \string remindActualUserAgent()
	 * @method \string requireUserAgent()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetUserAgent()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetUserAgent()
	 * @method \string fillUserAgent()
	 * @method \string getRequestUri()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setRequestUri(\string|\Bitrix\Main\DB\SqlExpression $requestUri)
	 * @method bool hasRequestUri()
	 * @method bool isRequestUriFilled()
	 * @method bool isRequestUriChanged()
	 * @method \string remindActualRequestUri()
	 * @method \string requireRequestUri()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetRequestUri()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetRequestUri()
	 * @method \string fillRequestUri()
	 * @method \string getSiteId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetSiteId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetUserId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getGuestId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setGuestId(\int|\Bitrix\Main\DB\SqlExpression $guestId)
	 * @method bool hasGuestId()
	 * @method bool isGuestIdFilled()
	 * @method bool isGuestIdChanged()
	 * @method \int remindActualGuestId()
	 * @method \int requireGuestId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetGuestId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetGuestId()
	 * @method \int fillGuestId()
	 * @method \string getDescription()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog resetDescription()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unsetDescription()
	 * @method \string fillDescription()
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
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog set($fieldName, $value)
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog reset($fieldName)
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EventLog\Internal\EO_EventLog wakeUp($data)
	 */
	class EO_EventLog {
		/* @var \Bitrix\Main\EventLog\Internal\EventLogTable */
		static public $dataClass = '\Bitrix\Main\EventLog\Internal\EventLogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\EventLog\Internal {
	/**
	 * EO_EventLog_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getSeverityList()
	 * @method \string[] fillSeverity()
	 * @method \string[] getAuditTypeIdList()
	 * @method \string[] fillAuditTypeId()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getItemIdList()
	 * @method \string[] fillItemId()
	 * @method \string[] getRemoteAddrList()
	 * @method \string[] fillRemoteAddr()
	 * @method \string[] getUserAgentList()
	 * @method \string[] fillUserAgent()
	 * @method \string[] getRequestUriList()
	 * @method \string[] fillRequestUri()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getGuestIdList()
	 * @method \int[] fillGuestId()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EventLog\Internal\EO_EventLog $object)
	 * @method bool has(\Bitrix\Main\EventLog\Internal\EO_EventLog $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog getByPrimary($primary)
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog[] getAll()
	 * @method bool remove(\Bitrix\Main\EventLog\Internal\EO_EventLog $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EventLog\Internal\EO_EventLog_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EventLog_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\EventLog\Internal\EventLogTable */
		static public $dataClass = '\Bitrix\Main\EventLog\Internal\EventLogTable';
	}
}
namespace Bitrix\Main\EventLog\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventLog_Result exec()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog fetchObject()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventLog_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog fetchObject()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog_Collection fetchCollection()
	 */
	class EO_EventLog_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog_Collection createCollection()
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog wakeUpObject($row)
	 * @method \Bitrix\Main\EventLog\Internal\EO_EventLog_Collection wakeUpCollection($rows)
	 */
	class EO_EventLog_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\EventLog\Internal\LogNotificationActionTable:main/lib/eventlog/internal/lognotificationactiontable.php */
namespace Bitrix\Main\EventLog\Internal {
	/**
	 * EO_LogNotificationAction
	 * @see \Bitrix\Main\EventLog\Internal\LogNotificationActionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getNotificationId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction setNotificationId(\int|\Bitrix\Main\DB\SqlExpression $notificationId)
	 * @method bool hasNotificationId()
	 * @method bool isNotificationIdFilled()
	 * @method bool isNotificationIdChanged()
	 * @method \int remindActualNotificationId()
	 * @method \int requireNotificationId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction resetNotificationId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction unsetNotificationId()
	 * @method \int fillNotificationId()
	 * @method \string getNotificationType()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction setNotificationType(\string|\Bitrix\Main\DB\SqlExpression $notificationType)
	 * @method bool hasNotificationType()
	 * @method bool isNotificationTypeFilled()
	 * @method bool isNotificationTypeChanged()
	 * @method \string remindActualNotificationType()
	 * @method \string requireNotificationType()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction resetNotificationType()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction unsetNotificationType()
	 * @method \string fillNotificationType()
	 * @method \string getRecipient()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction setRecipient(\string|\Bitrix\Main\DB\SqlExpression $recipient)
	 * @method bool hasRecipient()
	 * @method bool isRecipientFilled()
	 * @method bool isRecipientChanged()
	 * @method \string remindActualRecipient()
	 * @method \string requireRecipient()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction resetRecipient()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction unsetRecipient()
	 * @method \string fillRecipient()
	 * @method \string getAdditionalText()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction setAdditionalText(\string|\Bitrix\Main\DB\SqlExpression $additionalText)
	 * @method bool hasAdditionalText()
	 * @method bool isAdditionalTextFilled()
	 * @method bool isAdditionalTextChanged()
	 * @method \string remindActualAdditionalText()
	 * @method \string requireAdditionalText()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction resetAdditionalText()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction unsetAdditionalText()
	 * @method \string fillAdditionalText()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification getNotification()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification remindActualNotification()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification requireNotification()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction setNotification(\Bitrix\Main\EventLog\Internal\EO_LogNotification $object)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction resetNotification()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction unsetNotification()
	 * @method bool hasNotification()
	 * @method bool isNotificationFilled()
	 * @method bool isNotificationChanged()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification fillNotification()
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
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction set($fieldName, $value)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction reset($fieldName)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction wakeUp($data)
	 */
	class EO_LogNotificationAction {
		/* @var \Bitrix\Main\EventLog\Internal\LogNotificationActionTable */
		static public $dataClass = '\Bitrix\Main\EventLog\Internal\LogNotificationActionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\EventLog\Internal {
	/**
	 * EO_LogNotificationAction_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getNotificationIdList()
	 * @method \int[] fillNotificationId()
	 * @method \string[] getNotificationTypeList()
	 * @method \string[] fillNotificationType()
	 * @method \string[] getRecipientList()
	 * @method \string[] fillRecipient()
	 * @method \string[] getAdditionalTextList()
	 * @method \string[] fillAdditionalText()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification[] getNotificationList()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection getNotificationCollection()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification_Collection fillNotification()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EventLog\Internal\EO_LogNotificationAction $object)
	 * @method bool has(\Bitrix\Main\EventLog\Internal\EO_LogNotificationAction $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction getByPrimary($primary)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction[] getAll()
	 * @method bool remove(\Bitrix\Main\EventLog\Internal\EO_LogNotificationAction $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogNotificationAction_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\EventLog\Internal\LogNotificationActionTable */
		static public $dataClass = '\Bitrix\Main\EventLog\Internal\LogNotificationActionTable';
	}
}
namespace Bitrix\Main\EventLog\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogNotificationAction_Result exec()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction fetchObject()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogNotificationAction_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction fetchObject()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection fetchCollection()
	 */
	class EO_LogNotificationAction_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection createCollection()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction wakeUpObject($row)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection wakeUpCollection($rows)
	 */
	class EO_LogNotificationAction_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\EventLog\Internal\LogNotificationTable:main/lib/eventlog/internal/lognotificationtable.php */
namespace Bitrix\Main\EventLog\Internal {
	/**
	 * EO_LogNotification
	 * @see \Bitrix\Main\EventLog\Internal\LogNotificationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \boolean getActive()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetActive()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getName()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetName()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetName()
	 * @method \string fillName()
	 * @method \string getAuditTypeId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setAuditTypeId(\string|\Bitrix\Main\DB\SqlExpression $auditTypeId)
	 * @method bool hasAuditTypeId()
	 * @method bool isAuditTypeIdFilled()
	 * @method bool isAuditTypeIdChanged()
	 * @method \string remindActualAuditTypeId()
	 * @method \string requireAuditTypeId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetAuditTypeId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetAuditTypeId()
	 * @method \string fillAuditTypeId()
	 * @method \string getItemId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setItemId(\string|\Bitrix\Main\DB\SqlExpression $itemId)
	 * @method bool hasItemId()
	 * @method bool isItemIdFilled()
	 * @method bool isItemIdChanged()
	 * @method \string remindActualItemId()
	 * @method \string requireItemId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetItemId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetItemId()
	 * @method \string fillItemId()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetUserId()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getRemoteAddr()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setRemoteAddr(\string|\Bitrix\Main\DB\SqlExpression $remoteAddr)
	 * @method bool hasRemoteAddr()
	 * @method bool isRemoteAddrFilled()
	 * @method bool isRemoteAddrChanged()
	 * @method \string remindActualRemoteAddr()
	 * @method \string requireRemoteAddr()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetRemoteAddr()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetRemoteAddr()
	 * @method \string fillRemoteAddr()
	 * @method \string getUserAgent()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setUserAgent(\string|\Bitrix\Main\DB\SqlExpression $userAgent)
	 * @method bool hasUserAgent()
	 * @method bool isUserAgentFilled()
	 * @method bool isUserAgentChanged()
	 * @method \string remindActualUserAgent()
	 * @method \string requireUserAgent()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetUserAgent()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetUserAgent()
	 * @method \string fillUserAgent()
	 * @method \string getRequestUri()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setRequestUri(\string|\Bitrix\Main\DB\SqlExpression $requestUri)
	 * @method bool hasRequestUri()
	 * @method bool isRequestUriFilled()
	 * @method bool isRequestUriChanged()
	 * @method \string remindActualRequestUri()
	 * @method \string requireRequestUri()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetRequestUri()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetRequestUri()
	 * @method \string fillRequestUri()
	 * @method \int getCheckInterval()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setCheckInterval(\int|\Bitrix\Main\DB\SqlExpression $checkInterval)
	 * @method bool hasCheckInterval()
	 * @method bool isCheckIntervalFilled()
	 * @method bool isCheckIntervalChanged()
	 * @method \int remindActualCheckInterval()
	 * @method \int requireCheckInterval()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetCheckInterval()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetCheckInterval()
	 * @method \int fillCheckInterval()
	 * @method \int getAlertCount()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setAlertCount(\int|\Bitrix\Main\DB\SqlExpression $alertCount)
	 * @method bool hasAlertCount()
	 * @method bool isAlertCountFilled()
	 * @method bool isAlertCountChanged()
	 * @method \int remindActualAlertCount()
	 * @method \int requireAlertCount()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetAlertCount()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetAlertCount()
	 * @method \int fillAlertCount()
	 * @method \Bitrix\Main\Type\DateTime getDateChecked()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification setDateChecked(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateChecked)
	 * @method bool hasDateChecked()
	 * @method bool isDateCheckedFilled()
	 * @method bool isDateCheckedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateChecked()
	 * @method \Bitrix\Main\Type\DateTime requireDateChecked()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetDateChecked()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetDateChecked()
	 * @method \Bitrix\Main\Type\DateTime fillDateChecked()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection getActions()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection requireActions()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection fillActions()
	 * @method bool hasActions()
	 * @method bool isActionsFilled()
	 * @method bool isActionsChanged()
	 * @method void addToActions(\Bitrix\Main\EventLog\Internal\EO_LogNotificationAction $logNotificationAction)
	 * @method void removeFromActions(\Bitrix\Main\EventLog\Internal\EO_LogNotificationAction $logNotificationAction)
	 * @method void removeAllActions()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification resetActions()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unsetActions()
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
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification set($fieldName, $value)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification reset($fieldName)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotification wakeUp($data)
	 */
	class EO_LogNotification {
		/* @var \Bitrix\Main\EventLog\Internal\LogNotificationTable */
		static public $dataClass = '\Bitrix\Main\EventLog\Internal\LogNotificationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\EventLog\Internal {
	/**
	 * EO_LogNotification_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getAuditTypeIdList()
	 * @method \string[] fillAuditTypeId()
	 * @method \string[] getItemIdList()
	 * @method \string[] fillItemId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getRemoteAddrList()
	 * @method \string[] fillRemoteAddr()
	 * @method \string[] getUserAgentList()
	 * @method \string[] fillUserAgent()
	 * @method \string[] getRequestUriList()
	 * @method \string[] fillRequestUri()
	 * @method \int[] getCheckIntervalList()
	 * @method \int[] fillCheckInterval()
	 * @method \int[] getAlertCountList()
	 * @method \int[] fillAlertCount()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCheckedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateChecked()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection[] getActionsList()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection getActionsCollection()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotificationAction_Collection fillActions()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EventLog\Internal\EO_LogNotification $object)
	 * @method bool has(\Bitrix\Main\EventLog\Internal\EO_LogNotification $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification getByPrimary($primary)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification[] getAll()
	 * @method bool remove(\Bitrix\Main\EventLog\Internal\EO_LogNotification $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EventLog\Internal\EO_LogNotification_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogNotification_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\EventLog\Internal\LogNotificationTable */
		static public $dataClass = '\Bitrix\Main\EventLog\Internal\LogNotificationTable';
	}
}
namespace Bitrix\Main\EventLog\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogNotification_Result exec()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification fetchObject()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogNotification_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification fetchObject()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification_Collection fetchCollection()
	 */
	class EO_LogNotification_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification_Collection createCollection()
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification wakeUpObject($row)
	 * @method \Bitrix\Main\EventLog\Internal\EO_LogNotification_Collection wakeUpCollection($rows)
	 */
	class EO_LogNotification_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\File\Internal\FileDuplicateTable:main/lib/file/internal/fileduplicatetable.php */
namespace Bitrix\Main\File\Internal {
	/**
	 * EO_FileDuplicate
	 * @see \Bitrix\Main\File\Internal\FileDuplicateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getDuplicateId()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate setDuplicateId(\int|\Bitrix\Main\DB\SqlExpression $duplicateId)
	 * @method bool hasDuplicateId()
	 * @method bool isDuplicateIdFilled()
	 * @method bool isDuplicateIdChanged()
	 * @method \int getOriginalId()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate setOriginalId(\int|\Bitrix\Main\DB\SqlExpression $originalId)
	 * @method bool hasOriginalId()
	 * @method bool isOriginalIdFilled()
	 * @method bool isOriginalIdChanged()
	 * @method \int getCounter()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate setCounter(\int|\Bitrix\Main\DB\SqlExpression $counter)
	 * @method bool hasCounter()
	 * @method bool isCounterFilled()
	 * @method bool isCounterChanged()
	 * @method \int remindActualCounter()
	 * @method \int requireCounter()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate resetCounter()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate unsetCounter()
	 * @method \int fillCounter()
	 * @method \boolean getOriginalDeleted()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate setOriginalDeleted(\boolean|\Bitrix\Main\DB\SqlExpression $originalDeleted)
	 * @method bool hasOriginalDeleted()
	 * @method bool isOriginalDeletedFilled()
	 * @method bool isOriginalDeletedChanged()
	 * @method \boolean remindActualOriginalDeleted()
	 * @method \boolean requireOriginalDeleted()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate resetOriginalDeleted()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate unsetOriginalDeleted()
	 * @method \boolean fillOriginalDeleted()
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
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate set($fieldName, $value)
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate reset($fieldName)
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\File\Internal\EO_FileDuplicate wakeUp($data)
	 */
	class EO_FileDuplicate {
		/* @var \Bitrix\Main\File\Internal\FileDuplicateTable */
		static public $dataClass = '\Bitrix\Main\File\Internal\FileDuplicateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\File\Internal {
	/**
	 * EO_FileDuplicate_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getDuplicateIdList()
	 * @method \int[] getOriginalIdList()
	 * @method \int[] getCounterList()
	 * @method \int[] fillCounter()
	 * @method \boolean[] getOriginalDeletedList()
	 * @method \boolean[] fillOriginalDeleted()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\File\Internal\EO_FileDuplicate $object)
	 * @method bool has(\Bitrix\Main\File\Internal\EO_FileDuplicate $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate getByPrimary($primary)
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate[] getAll()
	 * @method bool remove(\Bitrix\Main\File\Internal\EO_FileDuplicate $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\File\Internal\EO_FileDuplicate_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileDuplicate_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\File\Internal\FileDuplicateTable */
		static public $dataClass = '\Bitrix\Main\File\Internal\FileDuplicateTable';
	}
}
namespace Bitrix\Main\File\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileDuplicate_Result exec()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate fetchObject()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileDuplicate_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate fetchObject()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate_Collection fetchCollection()
	 */
	class EO_FileDuplicate_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate_Collection createCollection()
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate wakeUpObject($row)
	 * @method \Bitrix\Main\File\Internal\EO_FileDuplicate_Collection wakeUpCollection($rows)
	 */
	class EO_FileDuplicate_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\File\Internal\FileHashTable:main/lib/file/internal/filehashtable.php */
namespace Bitrix\Main\File\Internal {
	/**
	 * EO_FileHash
	 * @see \Bitrix\Main\File\Internal\FileHashTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getFileId()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int getFileSize()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash resetFileSize()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \string getFileHash()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash setFileHash(\string|\Bitrix\Main\DB\SqlExpression $fileHash)
	 * @method bool hasFileHash()
	 * @method bool isFileHashFilled()
	 * @method bool isFileHashChanged()
	 * @method \string remindActualFileHash()
	 * @method \string requireFileHash()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash resetFileHash()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash unsetFileHash()
	 * @method \string fillFileHash()
	 * @method \Bitrix\Main\EO_File getFile()
	 * @method \Bitrix\Main\EO_File remindActualFile()
	 * @method \Bitrix\Main\EO_File requireFile()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash setFile(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Main\File\Internal\EO_FileHash resetFile()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash unsetFile()
	 * @method bool hasFile()
	 * @method bool isFileFilled()
	 * @method bool isFileChanged()
	 * @method \Bitrix\Main\EO_File fillFile()
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
	 * @method \Bitrix\Main\File\Internal\EO_FileHash set($fieldName, $value)
	 * @method \Bitrix\Main\File\Internal\EO_FileHash reset($fieldName)
	 * @method \Bitrix\Main\File\Internal\EO_FileHash unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\File\Internal\EO_FileHash wakeUp($data)
	 */
	class EO_FileHash {
		/* @var \Bitrix\Main\File\Internal\FileHashTable */
		static public $dataClass = '\Bitrix\Main\File\Internal\FileHashTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\File\Internal {
	/**
	 * EO_FileHash_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getFileIdList()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \string[] getFileHashList()
	 * @method \string[] fillFileHash()
	 * @method \Bitrix\Main\EO_File[] getFileList()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash_Collection getFileCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillFile()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\File\Internal\EO_FileHash $object)
	 * @method bool has(\Bitrix\Main\File\Internal\EO_FileHash $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\File\Internal\EO_FileHash getByPrimary($primary)
	 * @method \Bitrix\Main\File\Internal\EO_FileHash[] getAll()
	 * @method bool remove(\Bitrix\Main\File\Internal\EO_FileHash $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\File\Internal\EO_FileHash_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\File\Internal\EO_FileHash current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileHash_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\File\Internal\FileHashTable */
		static public $dataClass = '\Bitrix\Main\File\Internal\FileHashTable';
	}
}
namespace Bitrix\Main\File\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileHash_Result exec()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash fetchObject()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileHash_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\File\Internal\EO_FileHash fetchObject()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash_Collection fetchCollection()
	 */
	class EO_FileHash_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\File\Internal\EO_FileHash createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\File\Internal\EO_FileHash_Collection createCollection()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash wakeUpObject($row)
	 * @method \Bitrix\Main\File\Internal\EO_FileHash_Collection wakeUpCollection($rows)
	 */
	class EO_FileHash_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\File\Internal\FileVersionTable:main/lib/file/internal/fileversiontable.php */
namespace Bitrix\Main\File\Internal {
	/**
	 * EO_FileVersion
	 * @see \Bitrix\Main\File\Internal\FileVersionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getOriginalId()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion setOriginalId(\int|\Bitrix\Main\DB\SqlExpression $originalId)
	 * @method bool hasOriginalId()
	 * @method bool isOriginalIdFilled()
	 * @method bool isOriginalIdChanged()
	 * @method \int getVersionId()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion setVersionId(\int|\Bitrix\Main\DB\SqlExpression $versionId)
	 * @method bool hasVersionId()
	 * @method bool isVersionIdFilled()
	 * @method bool isVersionIdChanged()
	 * @method \int remindActualVersionId()
	 * @method \int requireVersionId()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion resetVersionId()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion unsetVersionId()
	 * @method \int fillVersionId()
	 * @method array getMeta()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion setMeta(array|\Bitrix\Main\DB\SqlExpression $meta)
	 * @method bool hasMeta()
	 * @method bool isMetaFilled()
	 * @method bool isMetaChanged()
	 * @method array remindActualMeta()
	 * @method array requireMeta()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion resetMeta()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion unsetMeta()
	 * @method array fillMeta()
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
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion set($fieldName, $value)
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion reset($fieldName)
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\File\Internal\EO_FileVersion wakeUp($data)
	 */
	class EO_FileVersion {
		/* @var \Bitrix\Main\File\Internal\FileVersionTable */
		static public $dataClass = '\Bitrix\Main\File\Internal\FileVersionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\File\Internal {
	/**
	 * EO_FileVersion_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getOriginalIdList()
	 * @method \int[] getVersionIdList()
	 * @method \int[] fillVersionId()
	 * @method array[] getMetaList()
	 * @method array[] fillMeta()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\File\Internal\EO_FileVersion $object)
	 * @method bool has(\Bitrix\Main\File\Internal\EO_FileVersion $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion getByPrimary($primary)
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion[] getAll()
	 * @method bool remove(\Bitrix\Main\File\Internal\EO_FileVersion $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\File\Internal\EO_FileVersion_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FileVersion_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\File\Internal\FileVersionTable */
		static public $dataClass = '\Bitrix\Main\File\Internal\FileVersionTable';
	}
}
namespace Bitrix\Main\File\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FileVersion_Result exec()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion fetchObject()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FileVersion_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion fetchObject()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion_Collection fetchCollection()
	 */
	class EO_FileVersion_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion_Collection createCollection()
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion wakeUpObject($row)
	 * @method \Bitrix\Main\File\Internal\EO_FileVersion_Collection wakeUpCollection($rows)
	 */
	class EO_FileVersion_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\FileTable:main/lib/file.php */
namespace Bitrix\Main {
	/**
	 * EO_File
	 * @see \Bitrix\Main\FileTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_File setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Main\EO_File setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Main\EO_File resetTimestampX()
	 * @method \Bitrix\Main\EO_File unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getModuleId()
	 * @method \Bitrix\Main\EO_File setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Main\EO_File resetModuleId()
	 * @method \Bitrix\Main\EO_File unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \int getHeight()
	 * @method \Bitrix\Main\EO_File setHeight(\int|\Bitrix\Main\DB\SqlExpression $height)
	 * @method bool hasHeight()
	 * @method bool isHeightFilled()
	 * @method bool isHeightChanged()
	 * @method \int remindActualHeight()
	 * @method \int requireHeight()
	 * @method \Bitrix\Main\EO_File resetHeight()
	 * @method \Bitrix\Main\EO_File unsetHeight()
	 * @method \int fillHeight()
	 * @method \int getWidth()
	 * @method \Bitrix\Main\EO_File setWidth(\int|\Bitrix\Main\DB\SqlExpression $width)
	 * @method bool hasWidth()
	 * @method bool isWidthFilled()
	 * @method bool isWidthChanged()
	 * @method \int remindActualWidth()
	 * @method \int requireWidth()
	 * @method \Bitrix\Main\EO_File resetWidth()
	 * @method \Bitrix\Main\EO_File unsetWidth()
	 * @method \int fillWidth()
	 * @method \int getFileSize()
	 * @method \Bitrix\Main\EO_File setFileSize(\int|\Bitrix\Main\DB\SqlExpression $fileSize)
	 * @method bool hasFileSize()
	 * @method bool isFileSizeFilled()
	 * @method bool isFileSizeChanged()
	 * @method \int remindActualFileSize()
	 * @method \int requireFileSize()
	 * @method \Bitrix\Main\EO_File resetFileSize()
	 * @method \Bitrix\Main\EO_File unsetFileSize()
	 * @method \int fillFileSize()
	 * @method \string getContentType()
	 * @method \Bitrix\Main\EO_File setContentType(\string|\Bitrix\Main\DB\SqlExpression $contentType)
	 * @method bool hasContentType()
	 * @method bool isContentTypeFilled()
	 * @method bool isContentTypeChanged()
	 * @method \string remindActualContentType()
	 * @method \string requireContentType()
	 * @method \Bitrix\Main\EO_File resetContentType()
	 * @method \Bitrix\Main\EO_File unsetContentType()
	 * @method \string fillContentType()
	 * @method \string getSubdir()
	 * @method \Bitrix\Main\EO_File setSubdir(\string|\Bitrix\Main\DB\SqlExpression $subdir)
	 * @method bool hasSubdir()
	 * @method bool isSubdirFilled()
	 * @method bool isSubdirChanged()
	 * @method \string remindActualSubdir()
	 * @method \string requireSubdir()
	 * @method \Bitrix\Main\EO_File resetSubdir()
	 * @method \Bitrix\Main\EO_File unsetSubdir()
	 * @method \string fillSubdir()
	 * @method \string getFileName()
	 * @method \Bitrix\Main\EO_File setFileName(\string|\Bitrix\Main\DB\SqlExpression $fileName)
	 * @method bool hasFileName()
	 * @method bool isFileNameFilled()
	 * @method bool isFileNameChanged()
	 * @method \string remindActualFileName()
	 * @method \string requireFileName()
	 * @method \Bitrix\Main\EO_File resetFileName()
	 * @method \Bitrix\Main\EO_File unsetFileName()
	 * @method \string fillFileName()
	 * @method \string getOriginalName()
	 * @method \Bitrix\Main\EO_File setOriginalName(\string|\Bitrix\Main\DB\SqlExpression $originalName)
	 * @method bool hasOriginalName()
	 * @method bool isOriginalNameFilled()
	 * @method bool isOriginalNameChanged()
	 * @method \string remindActualOriginalName()
	 * @method \string requireOriginalName()
	 * @method \Bitrix\Main\EO_File resetOriginalName()
	 * @method \Bitrix\Main\EO_File unsetOriginalName()
	 * @method \string fillOriginalName()
	 * @method \string getDescription()
	 * @method \Bitrix\Main\EO_File setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Main\EO_File resetDescription()
	 * @method \Bitrix\Main\EO_File unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getHandlerId()
	 * @method \Bitrix\Main\EO_File setHandlerId(\string|\Bitrix\Main\DB\SqlExpression $handlerId)
	 * @method bool hasHandlerId()
	 * @method bool isHandlerIdFilled()
	 * @method bool isHandlerIdChanged()
	 * @method \string remindActualHandlerId()
	 * @method \string requireHandlerId()
	 * @method \Bitrix\Main\EO_File resetHandlerId()
	 * @method \Bitrix\Main\EO_File unsetHandlerId()
	 * @method \string fillHandlerId()
	 * @method \string getExternalId()
	 * @method \Bitrix\Main\EO_File setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Main\EO_File resetExternalId()
	 * @method \Bitrix\Main\EO_File unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash getHash()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash remindActualHash()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash requireHash()
	 * @method \Bitrix\Main\EO_File setHash(\Bitrix\Main\File\Internal\EO_FileHash $object)
	 * @method \Bitrix\Main\EO_File resetHash()
	 * @method \Bitrix\Main\EO_File unsetHash()
	 * @method bool hasHash()
	 * @method bool isHashFilled()
	 * @method bool isHashChanged()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash fillHash()
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
	 * @method \Bitrix\Main\EO_File set($fieldName, $value)
	 * @method \Bitrix\Main\EO_File reset($fieldName)
	 * @method \Bitrix\Main\EO_File unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_File wakeUp($data)
	 */
	class EO_File {
		/* @var \Bitrix\Main\FileTable */
		static public $dataClass = '\Bitrix\Main\FileTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_File_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \int[] getHeightList()
	 * @method \int[] fillHeight()
	 * @method \int[] getWidthList()
	 * @method \int[] fillWidth()
	 * @method \int[] getFileSizeList()
	 * @method \int[] fillFileSize()
	 * @method \string[] getContentTypeList()
	 * @method \string[] fillContentType()
	 * @method \string[] getSubdirList()
	 * @method \string[] fillSubdir()
	 * @method \string[] getFileNameList()
	 * @method \string[] fillFileName()
	 * @method \string[] getOriginalNameList()
	 * @method \string[] fillOriginalName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getHandlerIdList()
	 * @method \string[] fillHandlerId()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash[] getHashList()
	 * @method \Bitrix\Main\EO_File_Collection getHashCollection()
	 * @method \Bitrix\Main\File\Internal\EO_FileHash_Collection fillHash()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_File $object)
	 * @method bool has(\Bitrix\Main\EO_File $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_File getByPrimary($primary)
	 * @method \Bitrix\Main\EO_File[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_File $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_File_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_File current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_File_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\FileTable */
		static public $dataClass = '\Bitrix\Main\FileTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_File_Result exec()
	 * @method \Bitrix\Main\EO_File fetchObject()
	 * @method \Bitrix\Main\EO_File_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_File_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_File fetchObject()
	 * @method \Bitrix\Main\EO_File_Collection fetchCollection()
	 */
	class EO_File_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_File createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_File_Collection createCollection()
	 * @method \Bitrix\Main\EO_File wakeUpObject($row)
	 * @method \Bitrix\Main\EO_File_Collection wakeUpCollection($rows)
	 */
	class EO_File_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\FinderDestTable:main/lib/finderdest.php */
namespace Bitrix\Main {
	/**
	 * EO_FinderDest
	 * @see \Bitrix\Main\FinderDestTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EO_FinderDest setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\EO_FinderDest setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\EO_FinderDest resetUser()
	 * @method \Bitrix\Main\EO_FinderDest unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \string getItemId()
	 * @method \Bitrix\Main\EO_FinderDest setItemId(\string|\Bitrix\Main\DB\SqlExpression $itemId)
	 * @method bool hasItemId()
	 * @method bool isItemIdFilled()
	 * @method bool isItemIdChanged()
	 * @method \string getEntityId()
	 * @method \Bitrix\Main\EO_FinderDest setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string getContext()
	 * @method \Bitrix\Main\EO_FinderDest setContext(\string|\Bitrix\Main\DB\SqlExpression $context)
	 * @method bool hasContext()
	 * @method bool isContextFilled()
	 * @method bool isContextChanged()
	 * @method \int getItemIdInt()
	 * @method \Bitrix\Main\EO_FinderDest setItemIdInt(\int|\Bitrix\Main\DB\SqlExpression $itemIdInt)
	 * @method bool hasItemIdInt()
	 * @method bool isItemIdIntFilled()
	 * @method bool isItemIdIntChanged()
	 * @method \int remindActualItemIdInt()
	 * @method \int requireItemIdInt()
	 * @method \Bitrix\Main\EO_FinderDest resetItemIdInt()
	 * @method \Bitrix\Main\EO_FinderDest unsetItemIdInt()
	 * @method \int fillItemIdInt()
	 * @method \string getPrefix()
	 * @method \Bitrix\Main\EO_FinderDest setPrefix(\string|\Bitrix\Main\DB\SqlExpression $prefix)
	 * @method bool hasPrefix()
	 * @method bool isPrefixFilled()
	 * @method bool isPrefixChanged()
	 * @method \string remindActualPrefix()
	 * @method \string requirePrefix()
	 * @method \Bitrix\Main\EO_FinderDest resetPrefix()
	 * @method \Bitrix\Main\EO_FinderDest unsetPrefix()
	 * @method \string fillPrefix()
	 * @method \Bitrix\Main\Type\DateTime getLastUseDate()
	 * @method \Bitrix\Main\EO_FinderDest setLastUseDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastUseDate)
	 * @method bool hasLastUseDate()
	 * @method bool isLastUseDateFilled()
	 * @method bool isLastUseDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastUseDate()
	 * @method \Bitrix\Main\Type\DateTime requireLastUseDate()
	 * @method \Bitrix\Main\EO_FinderDest resetLastUseDate()
	 * @method \Bitrix\Main\EO_FinderDest unsetLastUseDate()
	 * @method \Bitrix\Main\Type\DateTime fillLastUseDate()
	 * @method \string getCode()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method \Bitrix\Main\EO_FinderDest unsetCode()
	 * @method \string fillCode()
	 * @method \string getCodeType()
	 * @method \string remindActualCodeType()
	 * @method \string requireCodeType()
	 * @method bool hasCodeType()
	 * @method bool isCodeTypeFilled()
	 * @method \Bitrix\Main\EO_FinderDest unsetCodeType()
	 * @method \string fillCodeType()
	 * @method \string getCodeUserId()
	 * @method \string remindActualCodeUserId()
	 * @method \string requireCodeUserId()
	 * @method bool hasCodeUserId()
	 * @method bool isCodeUserIdFilled()
	 * @method \Bitrix\Main\EO_FinderDest unsetCodeUserId()
	 * @method \string fillCodeUserId()
	 * @method \Bitrix\Main\EO_User getCodeUser()
	 * @method \Bitrix\Main\EO_User remindActualCodeUser()
	 * @method \Bitrix\Main\EO_User requireCodeUser()
	 * @method \Bitrix\Main\EO_FinderDest setCodeUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\EO_FinderDest resetCodeUser()
	 * @method \Bitrix\Main\EO_FinderDest unsetCodeUser()
	 * @method bool hasCodeUser()
	 * @method bool isCodeUserFilled()
	 * @method bool isCodeUserChanged()
	 * @method \Bitrix\Main\EO_User fillCodeUser()
	 * @method \Bitrix\Main\EO_User getCodeUserCurrent()
	 * @method \Bitrix\Main\EO_User remindActualCodeUserCurrent()
	 * @method \Bitrix\Main\EO_User requireCodeUserCurrent()
	 * @method \Bitrix\Main\EO_FinderDest setCodeUserCurrent(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\EO_FinderDest resetCodeUserCurrent()
	 * @method \Bitrix\Main\EO_FinderDest unsetCodeUserCurrent()
	 * @method bool hasCodeUserCurrent()
	 * @method bool isCodeUserCurrentFilled()
	 * @method bool isCodeUserCurrentChanged()
	 * @method \Bitrix\Main\EO_User fillCodeUserCurrent()
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
	 * @method \Bitrix\Main\EO_FinderDest set($fieldName, $value)
	 * @method \Bitrix\Main\EO_FinderDest reset($fieldName)
	 * @method \Bitrix\Main\EO_FinderDest unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_FinderDest wakeUp($data)
	 */
	class EO_FinderDest {
		/* @var \Bitrix\Main\FinderDestTable */
		static public $dataClass = '\Bitrix\Main\FinderDestTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_FinderDest_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\EO_FinderDest_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \string[] getItemIdList()
	 * @method \string[] getEntityIdList()
	 * @method \string[] getContextList()
	 * @method \int[] getItemIdIntList()
	 * @method \int[] fillItemIdInt()
	 * @method \string[] getPrefixList()
	 * @method \string[] fillPrefix()
	 * @method \Bitrix\Main\Type\DateTime[] getLastUseDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastUseDate()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getCodeTypeList()
	 * @method \string[] fillCodeType()
	 * @method \string[] getCodeUserIdList()
	 * @method \string[] fillCodeUserId()
	 * @method \Bitrix\Main\EO_User[] getCodeUserList()
	 * @method \Bitrix\Main\EO_FinderDest_Collection getCodeUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCodeUser()
	 * @method \Bitrix\Main\EO_User[] getCodeUserCurrentList()
	 * @method \Bitrix\Main\EO_FinderDest_Collection getCodeUserCurrentCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCodeUserCurrent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_FinderDest $object)
	 * @method bool has(\Bitrix\Main\EO_FinderDest $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_FinderDest getByPrimary($primary)
	 * @method \Bitrix\Main\EO_FinderDest[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_FinderDest $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_FinderDest_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_FinderDest current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FinderDest_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\FinderDestTable */
		static public $dataClass = '\Bitrix\Main\FinderDestTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FinderDest_Result exec()
	 * @method \Bitrix\Main\EO_FinderDest fetchObject()
	 * @method \Bitrix\Main\EO_FinderDest_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FinderDest_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_FinderDest fetchObject()
	 * @method \Bitrix\Main\EO_FinderDest_Collection fetchCollection()
	 */
	class EO_FinderDest_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_FinderDest createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_FinderDest_Collection createCollection()
	 * @method \Bitrix\Main\EO_FinderDest wakeUpObject($row)
	 * @method \Bitrix\Main\EO_FinderDest_Collection wakeUpCollection($rows)
	 */
	class EO_FinderDest_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UI\EntitySelector\EntityUsageTable:main/lib/ui/entityselector/entityusagetable.php */
namespace Bitrix\Main\UI\EntitySelector {
	/**
	 * EO_EntityUsage
	 * @see \Bitrix\Main\UI\EntitySelector\EntityUsageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage resetUser()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \string getItemId()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setItemId(\string|\Bitrix\Main\DB\SqlExpression $itemId)
	 * @method bool hasItemId()
	 * @method bool isItemIdFilled()
	 * @method bool isItemIdChanged()
	 * @method \string getEntityId()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string getContext()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setContext(\string|\Bitrix\Main\DB\SqlExpression $context)
	 * @method bool hasContext()
	 * @method bool isContextFilled()
	 * @method bool isContextChanged()
	 * @method \int getItemIdInt()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setItemIdInt(\int|\Bitrix\Main\DB\SqlExpression $itemIdInt)
	 * @method bool hasItemIdInt()
	 * @method bool isItemIdIntFilled()
	 * @method bool isItemIdIntChanged()
	 * @method \int remindActualItemIdInt()
	 * @method \int requireItemIdInt()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage resetItemIdInt()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unsetItemIdInt()
	 * @method \int fillItemIdInt()
	 * @method \string getPrefix()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setPrefix(\string|\Bitrix\Main\DB\SqlExpression $prefix)
	 * @method bool hasPrefix()
	 * @method bool isPrefixFilled()
	 * @method bool isPrefixChanged()
	 * @method \string remindActualPrefix()
	 * @method \string requirePrefix()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage resetPrefix()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unsetPrefix()
	 * @method \string fillPrefix()
	 * @method \Bitrix\Main\Type\DateTime getLastUseDate()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setLastUseDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastUseDate)
	 * @method bool hasLastUseDate()
	 * @method bool isLastUseDateFilled()
	 * @method bool isLastUseDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastUseDate()
	 * @method \Bitrix\Main\Type\DateTime requireLastUseDate()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage resetLastUseDate()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unsetLastUseDate()
	 * @method \Bitrix\Main\Type\DateTime fillLastUseDate()
	 * @method \string getCode()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unsetCode()
	 * @method \string fillCode()
	 * @method \string getCodeType()
	 * @method \string remindActualCodeType()
	 * @method \string requireCodeType()
	 * @method bool hasCodeType()
	 * @method bool isCodeTypeFilled()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unsetCodeType()
	 * @method \string fillCodeType()
	 * @method \string getCodeUserId()
	 * @method \string remindActualCodeUserId()
	 * @method \string requireCodeUserId()
	 * @method bool hasCodeUserId()
	 * @method bool isCodeUserIdFilled()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unsetCodeUserId()
	 * @method \string fillCodeUserId()
	 * @method \Bitrix\Main\EO_User getCodeUser()
	 * @method \Bitrix\Main\EO_User remindActualCodeUser()
	 * @method \Bitrix\Main\EO_User requireCodeUser()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setCodeUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage resetCodeUser()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unsetCodeUser()
	 * @method bool hasCodeUser()
	 * @method bool isCodeUserFilled()
	 * @method bool isCodeUserChanged()
	 * @method \Bitrix\Main\EO_User fillCodeUser()
	 * @method \Bitrix\Main\EO_User getCodeUserCurrent()
	 * @method \Bitrix\Main\EO_User remindActualCodeUserCurrent()
	 * @method \Bitrix\Main\EO_User requireCodeUserCurrent()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage setCodeUserCurrent(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage resetCodeUserCurrent()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unsetCodeUserCurrent()
	 * @method bool hasCodeUserCurrent()
	 * @method bool isCodeUserCurrentFilled()
	 * @method bool isCodeUserCurrentChanged()
	 * @method \Bitrix\Main\EO_User fillCodeUserCurrent()
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
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage set($fieldName, $value)
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage reset($fieldName)
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\UI\EntitySelector\EO_EntityUsage wakeUp($data)
	 */
	class EO_EntityUsage {
		/* @var \Bitrix\Main\UI\EntitySelector\EntityUsageTable */
		static public $dataClass = '\Bitrix\Main\UI\EntitySelector\EntityUsageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\UI\EntitySelector {
	/**
	 * EO_EntityUsage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \string[] getItemIdList()
	 * @method \string[] getEntityIdList()
	 * @method \string[] getContextList()
	 * @method \int[] getItemIdIntList()
	 * @method \int[] fillItemIdInt()
	 * @method \string[] getPrefixList()
	 * @method \string[] fillPrefix()
	 * @method \Bitrix\Main\Type\DateTime[] getLastUseDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastUseDate()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getCodeTypeList()
	 * @method \string[] fillCodeType()
	 * @method \string[] getCodeUserIdList()
	 * @method \string[] fillCodeUserId()
	 * @method \Bitrix\Main\EO_User[] getCodeUserList()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection getCodeUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCodeUser()
	 * @method \Bitrix\Main\EO_User[] getCodeUserCurrentList()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection getCodeUserCurrentCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCodeUserCurrent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\UI\EntitySelector\EO_EntityUsage $object)
	 * @method bool has(\Bitrix\Main\UI\EntitySelector\EO_EntityUsage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage getByPrimary($primary)
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage[] getAll()
	 * @method bool remove(\Bitrix\Main\UI\EntitySelector\EO_EntityUsage $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EntityUsage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UI\EntitySelector\EntityUsageTable */
		static public $dataClass = '\Bitrix\Main\UI\EntitySelector\EntityUsageTable';
	}
}
namespace Bitrix\Main\UI\EntitySelector {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EntityUsage_Result exec()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage fetchObject()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EntityUsage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage fetchObject()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection fetchCollection()
	 */
	class EO_EntityUsage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection createCollection()
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage wakeUpObject($row)
	 * @method \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection wakeUpCollection($rows)
	 */
	class EO_EntityUsage_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\GroupTable:main/lib/group.php */
namespace Bitrix\Main {
	/**
	 * EO_Group
	 * @see \Bitrix\Main\GroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_Group setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Main\EO_Group setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Main\EO_Group resetTimestampX()
	 * @method \Bitrix\Main\EO_Group unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \boolean getActive()
	 * @method \Bitrix\Main\EO_Group setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Main\EO_Group resetActive()
	 * @method \Bitrix\Main\EO_Group unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getCSort()
	 * @method \Bitrix\Main\EO_Group setCSort(\int|\Bitrix\Main\DB\SqlExpression $cSort)
	 * @method bool hasCSort()
	 * @method bool isCSortFilled()
	 * @method bool isCSortChanged()
	 * @method \int remindActualCSort()
	 * @method \int requireCSort()
	 * @method \Bitrix\Main\EO_Group resetCSort()
	 * @method \Bitrix\Main\EO_Group unsetCSort()
	 * @method \int fillCSort()
	 * @method \boolean getIsSystem()
	 * @method \Bitrix\Main\EO_Group setIsSystem(\boolean|\Bitrix\Main\DB\SqlExpression $isSystem)
	 * @method bool hasIsSystem()
	 * @method bool isIsSystemFilled()
	 * @method bool isIsSystemChanged()
	 * @method \boolean remindActualIsSystem()
	 * @method \boolean requireIsSystem()
	 * @method \Bitrix\Main\EO_Group resetIsSystem()
	 * @method \Bitrix\Main\EO_Group unsetIsSystem()
	 * @method \boolean fillIsSystem()
	 * @method \boolean getAnonymous()
	 * @method \Bitrix\Main\EO_Group setAnonymous(\boolean|\Bitrix\Main\DB\SqlExpression $anonymous)
	 * @method bool hasAnonymous()
	 * @method bool isAnonymousFilled()
	 * @method bool isAnonymousChanged()
	 * @method \boolean remindActualAnonymous()
	 * @method \boolean requireAnonymous()
	 * @method \Bitrix\Main\EO_Group resetAnonymous()
	 * @method \Bitrix\Main\EO_Group unsetAnonymous()
	 * @method \boolean fillAnonymous()
	 * @method \string getName()
	 * @method \Bitrix\Main\EO_Group setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\EO_Group resetName()
	 * @method \Bitrix\Main\EO_Group unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Main\EO_Group setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Main\EO_Group resetDescription()
	 * @method \Bitrix\Main\EO_Group unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getStringId()
	 * @method \Bitrix\Main\EO_Group setStringId(\string|\Bitrix\Main\DB\SqlExpression $stringId)
	 * @method bool hasStringId()
	 * @method bool isStringIdFilled()
	 * @method bool isStringIdChanged()
	 * @method \string remindActualStringId()
	 * @method \string requireStringId()
	 * @method \Bitrix\Main\EO_Group resetStringId()
	 * @method \Bitrix\Main\EO_Group unsetStringId()
	 * @method \string fillStringId()
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
	 * @method \Bitrix\Main\EO_Group set($fieldName, $value)
	 * @method \Bitrix\Main\EO_Group reset($fieldName)
	 * @method \Bitrix\Main\EO_Group unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_Group wakeUp($data)
	 */
	class EO_Group {
		/* @var \Bitrix\Main\GroupTable */
		static public $dataClass = '\Bitrix\Main\GroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_Group_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getCSortList()
	 * @method \int[] fillCSort()
	 * @method \boolean[] getIsSystemList()
	 * @method \boolean[] fillIsSystem()
	 * @method \boolean[] getAnonymousList()
	 * @method \boolean[] fillAnonymous()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getStringIdList()
	 * @method \string[] fillStringId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_Group $object)
	 * @method bool has(\Bitrix\Main\EO_Group $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_Group getByPrimary($primary)
	 * @method \Bitrix\Main\EO_Group[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_Group $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_Group_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_Group current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Group_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\GroupTable */
		static public $dataClass = '\Bitrix\Main\GroupTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Group_Result exec()
	 * @method \Bitrix\Main\EO_Group fetchObject()
	 * @method \Bitrix\Main\EO_Group_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Group_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_Group fetchObject()
	 * @method \Bitrix\Main\EO_Group_Collection fetchCollection()
	 */
	class EO_Group_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_Group createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_Group_Collection createCollection()
	 * @method \Bitrix\Main\EO_Group wakeUpObject($row)
	 * @method \Bitrix\Main\EO_Group_Collection wakeUpCollection($rows)
	 */
	class EO_Group_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\GroupTaskTable:main/lib/grouptask.php */
namespace Bitrix\Main {
	/**
	 * EO_GroupTask
	 * @see \Bitrix\Main\GroupTaskTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getGroupId()
	 * @method \Bitrix\Main\EO_GroupTask setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int getTaskId()
	 * @method \Bitrix\Main\EO_GroupTask setTaskId(\int|\Bitrix\Main\DB\SqlExpression $taskId)
	 * @method bool hasTaskId()
	 * @method bool isTaskIdFilled()
	 * @method bool isTaskIdChanged()
	 * @method \string getExternalId()
	 * @method \Bitrix\Main\EO_GroupTask setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Main\EO_GroupTask resetExternalId()
	 * @method \Bitrix\Main\EO_GroupTask unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \Bitrix\Main\EO_Group getGroup()
	 * @method \Bitrix\Main\EO_Group remindActualGroup()
	 * @method \Bitrix\Main\EO_Group requireGroup()
	 * @method \Bitrix\Main\EO_GroupTask setGroup(\Bitrix\Main\EO_Group $object)
	 * @method \Bitrix\Main\EO_GroupTask resetGroup()
	 * @method \Bitrix\Main\EO_GroupTask unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Main\EO_Group fillGroup()
	 * @method \Bitrix\Main\EO_Task getTask()
	 * @method \Bitrix\Main\EO_Task remindActualTask()
	 * @method \Bitrix\Main\EO_Task requireTask()
	 * @method \Bitrix\Main\EO_GroupTask setTask(\Bitrix\Main\EO_Task $object)
	 * @method \Bitrix\Main\EO_GroupTask resetTask()
	 * @method \Bitrix\Main\EO_GroupTask unsetTask()
	 * @method bool hasTask()
	 * @method bool isTaskFilled()
	 * @method bool isTaskChanged()
	 * @method \Bitrix\Main\EO_Task fillTask()
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
	 * @method \Bitrix\Main\EO_GroupTask set($fieldName, $value)
	 * @method \Bitrix\Main\EO_GroupTask reset($fieldName)
	 * @method \Bitrix\Main\EO_GroupTask unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_GroupTask wakeUp($data)
	 */
	class EO_GroupTask {
		/* @var \Bitrix\Main\GroupTaskTable */
		static public $dataClass = '\Bitrix\Main\GroupTaskTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_GroupTask_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getGroupIdList()
	 * @method \int[] getTaskIdList()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \Bitrix\Main\EO_Group[] getGroupList()
	 * @method \Bitrix\Main\EO_GroupTask_Collection getGroupCollection()
	 * @method \Bitrix\Main\EO_Group_Collection fillGroup()
	 * @method \Bitrix\Main\EO_Task[] getTaskList()
	 * @method \Bitrix\Main\EO_GroupTask_Collection getTaskCollection()
	 * @method \Bitrix\Main\EO_Task_Collection fillTask()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_GroupTask $object)
	 * @method bool has(\Bitrix\Main\EO_GroupTask $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_GroupTask getByPrimary($primary)
	 * @method \Bitrix\Main\EO_GroupTask[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_GroupTask $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_GroupTask_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_GroupTask current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupTask_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\GroupTaskTable */
		static public $dataClass = '\Bitrix\Main\GroupTaskTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupTask_Result exec()
	 * @method \Bitrix\Main\EO_GroupTask fetchObject()
	 * @method \Bitrix\Main\EO_GroupTask_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupTask_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_GroupTask fetchObject()
	 * @method \Bitrix\Main\EO_GroupTask_Collection fetchCollection()
	 */
	class EO_GroupTask_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_GroupTask createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_GroupTask_Collection createCollection()
	 * @method \Bitrix\Main\EO_GroupTask wakeUpObject($row)
	 * @method \Bitrix\Main\EO_GroupTask_Collection wakeUpCollection($rows)
	 */
	class EO_GroupTask_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Localization\LanguageTable:main/lib/localization/language.php */
namespace Bitrix\Main\Localization {
	/**
	 * EO_Language
	 * @see \Bitrix\Main\Localization\LanguageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getLid()
	 * @method \Bitrix\Main\Localization\EO_Language setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string getId()
	 * @method \string remindActualId()
	 * @method \string requireId()
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method \Bitrix\Main\Localization\EO_Language unsetId()
	 * @method \string fillId()
	 * @method \string getLanguageId()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method \Bitrix\Main\Localization\EO_Language unsetLanguageId()
	 * @method \string fillLanguageId()
	 * @method \int getSort()
	 * @method \Bitrix\Main\Localization\EO_Language setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Main\Localization\EO_Language resetSort()
	 * @method \Bitrix\Main\Localization\EO_Language unsetSort()
	 * @method \int fillSort()
	 * @method \boolean getDef()
	 * @method \Bitrix\Main\Localization\EO_Language setDef(\boolean|\Bitrix\Main\DB\SqlExpression $def)
	 * @method bool hasDef()
	 * @method bool isDefFilled()
	 * @method bool isDefChanged()
	 * @method \boolean remindActualDef()
	 * @method \boolean requireDef()
	 * @method \Bitrix\Main\Localization\EO_Language resetDef()
	 * @method \Bitrix\Main\Localization\EO_Language unsetDef()
	 * @method \boolean fillDef()
	 * @method \boolean getActive()
	 * @method \Bitrix\Main\Localization\EO_Language setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Main\Localization\EO_Language resetActive()
	 * @method \Bitrix\Main\Localization\EO_Language unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getName()
	 * @method \Bitrix\Main\Localization\EO_Language setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\Localization\EO_Language resetName()
	 * @method \Bitrix\Main\Localization\EO_Language unsetName()
	 * @method \string fillName()
	 * @method \int getCultureId()
	 * @method \Bitrix\Main\Localization\EO_Language setCultureId(\int|\Bitrix\Main\DB\SqlExpression $cultureId)
	 * @method bool hasCultureId()
	 * @method bool isCultureIdFilled()
	 * @method bool isCultureIdChanged()
	 * @method \int remindActualCultureId()
	 * @method \int requireCultureId()
	 * @method \Bitrix\Main\Localization\EO_Language resetCultureId()
	 * @method \Bitrix\Main\Localization\EO_Language unsetCultureId()
	 * @method \int fillCultureId()
	 * @method \string getCode()
	 * @method \Bitrix\Main\Localization\EO_Language setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Main\Localization\EO_Language resetCode()
	 * @method \Bitrix\Main\Localization\EO_Language unsetCode()
	 * @method \string fillCode()
	 * @method \Bitrix\Main\Context\Culture getCulture()
	 * @method \Bitrix\Main\Context\Culture remindActualCulture()
	 * @method \Bitrix\Main\Context\Culture requireCulture()
	 * @method \Bitrix\Main\Localization\EO_Language setCulture(\Bitrix\Main\Context\Culture $object)
	 * @method \Bitrix\Main\Localization\EO_Language resetCulture()
	 * @method \Bitrix\Main\Localization\EO_Language unsetCulture()
	 * @method bool hasCulture()
	 * @method bool isCultureFilled()
	 * @method bool isCultureChanged()
	 * @method \Bitrix\Main\Context\Culture fillCulture()
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
	 * @method \Bitrix\Main\Localization\EO_Language set($fieldName, $value)
	 * @method \Bitrix\Main\Localization\EO_Language reset($fieldName)
	 * @method \Bitrix\Main\Localization\EO_Language unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Localization\EO_Language wakeUp($data)
	 */
	class EO_Language {
		/* @var \Bitrix\Main\Localization\LanguageTable */
		static public $dataClass = '\Bitrix\Main\Localization\LanguageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Localization {
	/**
	 * EO_Language_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getLidList()
	 * @method \string[] getIdList()
	 * @method \string[] fillId()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \boolean[] getDefList()
	 * @method \boolean[] fillDef()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getCultureIdList()
	 * @method \int[] fillCultureId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \Bitrix\Main\Context\Culture[] getCultureList()
	 * @method \Bitrix\Main\Localization\EO_Language_Collection getCultureCollection()
	 * @method \Bitrix\Main\Localization\EO_Culture_Collection fillCulture()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Localization\EO_Language $object)
	 * @method bool has(\Bitrix\Main\Localization\EO_Language $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Localization\EO_Language getByPrimary($primary)
	 * @method \Bitrix\Main\Localization\EO_Language[] getAll()
	 * @method bool remove(\Bitrix\Main\Localization\EO_Language $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Localization\EO_Language_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Localization\EO_Language current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Language_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Localization\LanguageTable */
		static public $dataClass = '\Bitrix\Main\Localization\LanguageTable';
	}
}
namespace Bitrix\Main\Localization {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Language_Result exec()
	 * @method \Bitrix\Main\Localization\EO_Language fetchObject()
	 * @method \Bitrix\Main\Localization\EO_Language_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Language_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Localization\EO_Language fetchObject()
	 * @method \Bitrix\Main\Localization\EO_Language_Collection fetchCollection()
	 */
	class EO_Language_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Localization\EO_Language createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Localization\EO_Language_Collection createCollection()
	 * @method \Bitrix\Main\Localization\EO_Language wakeUpObject($row)
	 * @method \Bitrix\Main\Localization\EO_Language_Collection wakeUpCollection($rows)
	 */
	class EO_Language_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Mail\Internal\BlacklistTable:main/lib/mail/internal/blacklist.php */
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_Blacklist
	 * @see \Bitrix\Main\Mail\Internal\BlacklistTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getCategoryId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist setCategoryId(\int|\Bitrix\Main\DB\SqlExpression $categoryId)
	 * @method bool hasCategoryId()
	 * @method bool isCategoryIdFilled()
	 * @method bool isCategoryIdChanged()
	 * @method \int remindActualCategoryId()
	 * @method \int requireCategoryId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist resetCategoryId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist unsetCategoryId()
	 * @method \int fillCategoryId()
	 * @method \string getCode()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist resetCode()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist unsetCode()
	 * @method \string fillCode()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist resetDateInsert()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
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
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist set($fieldName, $value)
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist reset($fieldName)
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Mail\Internal\EO_Blacklist wakeUp($data)
	 */
	class EO_Blacklist {
		/* @var \Bitrix\Main\Mail\Internal\BlacklistTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\BlacklistTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_Blacklist_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getCategoryIdList()
	 * @method \int[] fillCategoryId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Mail\Internal\EO_Blacklist $object)
	 * @method bool has(\Bitrix\Main\Mail\Internal\EO_Blacklist $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist getByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist[] getAll()
	 * @method bool remove(\Bitrix\Main\Mail\Internal\EO_Blacklist $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Mail\Internal\EO_Blacklist_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Blacklist_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Mail\Internal\BlacklistTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\BlacklistTable';
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Blacklist_Result exec()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Blacklist_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist_Collection fetchCollection()
	 */
	class EO_Blacklist_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist_Collection createCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist wakeUpObject($row)
	 * @method \Bitrix\Main\Mail\Internal\EO_Blacklist_Collection wakeUpCollection($rows)
	 */
	class EO_Blacklist_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Mail\Internal\EventTable:main/lib/mail/internal/event.php */
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_Event
	 * @see \Bitrix\Main\Mail\Internal\EventTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEventName()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setEventName(\string|\Bitrix\Main\DB\SqlExpression $eventName)
	 * @method bool hasEventName()
	 * @method bool isEventNameFilled()
	 * @method bool isEventNameChanged()
	 * @method \string remindActualEventName()
	 * @method \string requireEventName()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event resetEventName()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unsetEventName()
	 * @method \string fillEventName()
	 * @method \int getMessageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int remindActualMessageId()
	 * @method \int requireMessageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event resetMessageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unsetMessageId()
	 * @method \int fillMessageId()
	 * @method \string getLid()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string remindActualLid()
	 * @method \string requireLid()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event resetLid()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unsetLid()
	 * @method \string fillLid()
	 * @method array getCFields()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setCFields(array|\Bitrix\Main\DB\SqlExpression $cFields)
	 * @method bool hasCFields()
	 * @method bool isCFieldsFilled()
	 * @method bool isCFieldsChanged()
	 * @method array remindActualCFields()
	 * @method array requireCFields()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event resetCFields()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unsetCFields()
	 * @method array fillCFields()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event resetDateInsert()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime getDateExec()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setDateExec(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateExec)
	 * @method bool hasDateExec()
	 * @method bool isDateExecFilled()
	 * @method bool isDateExecChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateExec()
	 * @method \Bitrix\Main\Type\DateTime requireDateExec()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event resetDateExec()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unsetDateExec()
	 * @method \Bitrix\Main\Type\DateTime fillDateExec()
	 * @method \string getSuccessExec()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setSuccessExec(\string|\Bitrix\Main\DB\SqlExpression $successExec)
	 * @method bool hasSuccessExec()
	 * @method bool isSuccessExecFilled()
	 * @method bool isSuccessExecChanged()
	 * @method \string remindActualSuccessExec()
	 * @method \string requireSuccessExec()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event resetSuccessExec()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unsetSuccessExec()
	 * @method \string fillSuccessExec()
	 * @method \string getDuplicate()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setDuplicate(\string|\Bitrix\Main\DB\SqlExpression $duplicate)
	 * @method bool hasDuplicate()
	 * @method bool isDuplicateFilled()
	 * @method bool isDuplicateChanged()
	 * @method \string remindActualDuplicate()
	 * @method \string requireDuplicate()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event resetDuplicate()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unsetDuplicate()
	 * @method \string fillDuplicate()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event resetLanguageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unsetLanguageId()
	 * @method \string fillLanguageId()
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
	 * @method \Bitrix\Main\Mail\Internal\EO_Event set($fieldName, $value)
	 * @method \Bitrix\Main\Mail\Internal\EO_Event reset($fieldName)
	 * @method \Bitrix\Main\Mail\Internal\EO_Event unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Mail\Internal\EO_Event wakeUp($data)
	 */
	class EO_Event {
		/* @var \Bitrix\Main\Mail\Internal\EventTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_Event_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEventNameList()
	 * @method \string[] fillEventName()
	 * @method \int[] getMessageIdList()
	 * @method \int[] fillMessageId()
	 * @method \string[] getLidList()
	 * @method \string[] fillLid()
	 * @method array[] getCFieldsList()
	 * @method array[] fillCFields()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime[] getDateExecList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateExec()
	 * @method \string[] getSuccessExecList()
	 * @method \string[] fillSuccessExec()
	 * @method \string[] getDuplicateList()
	 * @method \string[] fillDuplicate()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Mail\Internal\EO_Event $object)
	 * @method bool has(\Bitrix\Main\Mail\Internal\EO_Event $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_Event getByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_Event[] getAll()
	 * @method bool remove(\Bitrix\Main\Mail\Internal\EO_Event $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Mail\Internal\EO_Event_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Mail\Internal\EO_Event current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Event_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Mail\Internal\EventTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventTable';
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Event_Result exec()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Event_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_Event fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event_Collection fetchCollection()
	 */
	class EO_Event_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_Event createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Mail\Internal\EO_Event_Collection createCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event wakeUpObject($row)
	 * @method \Bitrix\Main\Mail\Internal\EO_Event_Collection wakeUpCollection($rows)
	 */
	class EO_Event_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Mail\Internal\EventAttachmentTable:main/lib/mail/internal/eventattachment.php */
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventAttachment
	 * @see \Bitrix\Main\Mail\Internal\EventAttachmentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getEventId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment setEventId(\int|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \int getFileId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment resetFileId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment unsetFileId()
	 * @method \int fillFileId()
	 * @method \boolean getIsFileCopied()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment setIsFileCopied(\boolean|\Bitrix\Main\DB\SqlExpression $isFileCopied)
	 * @method bool hasIsFileCopied()
	 * @method bool isIsFileCopiedFilled()
	 * @method bool isIsFileCopiedChanged()
	 * @method \boolean remindActualIsFileCopied()
	 * @method \boolean requireIsFileCopied()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment resetIsFileCopied()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment unsetIsFileCopied()
	 * @method \boolean fillIsFileCopied()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event getEvent()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event remindActualEvent()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event requireEvent()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment setEvent(\Bitrix\Main\Mail\Internal\EO_Event $object)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment resetEvent()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment unsetEvent()
	 * @method bool hasEvent()
	 * @method bool isEventFilled()
	 * @method bool isEventChanged()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event fillEvent()
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
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment set($fieldName, $value)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment reset($fieldName)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventAttachment wakeUp($data)
	 */
	class EO_EventAttachment {
		/* @var \Bitrix\Main\Mail\Internal\EventAttachmentTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventAttachmentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventAttachment_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getEventIdList()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 * @method \boolean[] getIsFileCopiedList()
	 * @method \boolean[] fillIsFileCopied()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event[] getEventList()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment_Collection getEventCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_Event_Collection fillEvent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Mail\Internal\EO_EventAttachment $object)
	 * @method bool has(\Bitrix\Main\Mail\Internal\EO_EventAttachment $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment getByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment[] getAll()
	 * @method bool remove(\Bitrix\Main\Mail\Internal\EO_EventAttachment $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventAttachment_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EventAttachment_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Mail\Internal\EventAttachmentTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventAttachmentTable';
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventAttachment_Result exec()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventAttachment_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment_Collection fetchCollection()
	 */
	class EO_EventAttachment_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment_Collection createCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment wakeUpObject($row)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventAttachment_Collection wakeUpCollection($rows)
	 */
	class EO_EventAttachment_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Mail\Internal\EventMessageTable:main/lib/mail/internal/eventmessage.php */
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventMessage
	 * @see \Bitrix\Main\Mail\Internal\EventMessageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetTimestampX()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getEventName()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setEventName(\string|\Bitrix\Main\DB\SqlExpression $eventName)
	 * @method bool hasEventName()
	 * @method bool isEventNameFilled()
	 * @method bool isEventNameChanged()
	 * @method \string remindActualEventName()
	 * @method \string requireEventName()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetEventName()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetEventName()
	 * @method \string fillEventName()
	 * @method \string getLid()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string remindActualLid()
	 * @method \string requireLid()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetLid()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetLid()
	 * @method \string fillLid()
	 * @method \string getActive()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetActive()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetActive()
	 * @method \string fillActive()
	 * @method \string getEmailFrom()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setEmailFrom(\string|\Bitrix\Main\DB\SqlExpression $emailFrom)
	 * @method bool hasEmailFrom()
	 * @method bool isEmailFromFilled()
	 * @method bool isEmailFromChanged()
	 * @method \string remindActualEmailFrom()
	 * @method \string requireEmailFrom()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetEmailFrom()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetEmailFrom()
	 * @method \string fillEmailFrom()
	 * @method \string getEmailTo()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setEmailTo(\string|\Bitrix\Main\DB\SqlExpression $emailTo)
	 * @method bool hasEmailTo()
	 * @method bool isEmailToFilled()
	 * @method bool isEmailToChanged()
	 * @method \string remindActualEmailTo()
	 * @method \string requireEmailTo()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetEmailTo()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetEmailTo()
	 * @method \string fillEmailTo()
	 * @method \string getSubject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setSubject(\string|\Bitrix\Main\DB\SqlExpression $subject)
	 * @method bool hasSubject()
	 * @method bool isSubjectFilled()
	 * @method bool isSubjectChanged()
	 * @method \string remindActualSubject()
	 * @method \string requireSubject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetSubject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetSubject()
	 * @method \string fillSubject()
	 * @method \string getMessage()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetMessage()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetMessage()
	 * @method \string fillMessage()
	 * @method \string getMessagePhp()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setMessagePhp(\string|\Bitrix\Main\DB\SqlExpression $messagePhp)
	 * @method bool hasMessagePhp()
	 * @method bool isMessagePhpFilled()
	 * @method bool isMessagePhpChanged()
	 * @method \string remindActualMessagePhp()
	 * @method \string requireMessagePhp()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetMessagePhp()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetMessagePhp()
	 * @method \string fillMessagePhp()
	 * @method \string getBodyType()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setBodyType(\string|\Bitrix\Main\DB\SqlExpression $bodyType)
	 * @method bool hasBodyType()
	 * @method bool isBodyTypeFilled()
	 * @method bool isBodyTypeChanged()
	 * @method \string remindActualBodyType()
	 * @method \string requireBodyType()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetBodyType()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetBodyType()
	 * @method \string fillBodyType()
	 * @method \string getBcc()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setBcc(\string|\Bitrix\Main\DB\SqlExpression $bcc)
	 * @method bool hasBcc()
	 * @method bool isBccFilled()
	 * @method bool isBccChanged()
	 * @method \string remindActualBcc()
	 * @method \string requireBcc()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetBcc()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetBcc()
	 * @method \string fillBcc()
	 * @method \string getReplyTo()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setReplyTo(\string|\Bitrix\Main\DB\SqlExpression $replyTo)
	 * @method bool hasReplyTo()
	 * @method bool isReplyToFilled()
	 * @method bool isReplyToChanged()
	 * @method \string remindActualReplyTo()
	 * @method \string requireReplyTo()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetReplyTo()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetReplyTo()
	 * @method \string fillReplyTo()
	 * @method \string getCc()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setCc(\string|\Bitrix\Main\DB\SqlExpression $cc)
	 * @method bool hasCc()
	 * @method bool isCcFilled()
	 * @method bool isCcChanged()
	 * @method \string remindActualCc()
	 * @method \string requireCc()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetCc()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetCc()
	 * @method \string fillCc()
	 * @method \string getInReplyTo()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setInReplyTo(\string|\Bitrix\Main\DB\SqlExpression $inReplyTo)
	 * @method bool hasInReplyTo()
	 * @method bool isInReplyToFilled()
	 * @method bool isInReplyToChanged()
	 * @method \string remindActualInReplyTo()
	 * @method \string requireInReplyTo()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetInReplyTo()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetInReplyTo()
	 * @method \string fillInReplyTo()
	 * @method \string getPriority()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setPriority(\string|\Bitrix\Main\DB\SqlExpression $priority)
	 * @method bool hasPriority()
	 * @method bool isPriorityFilled()
	 * @method bool isPriorityChanged()
	 * @method \string remindActualPriority()
	 * @method \string requirePriority()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetPriority()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetPriority()
	 * @method \string fillPriority()
	 * @method \string getField1Name()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setField1Name(\string|\Bitrix\Main\DB\SqlExpression $field1Name)
	 * @method bool hasField1Name()
	 * @method bool isField1NameFilled()
	 * @method bool isField1NameChanged()
	 * @method \string remindActualField1Name()
	 * @method \string requireField1Name()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetField1Name()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetField1Name()
	 * @method \string fillField1Name()
	 * @method \string getField1Value()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setField1Value(\string|\Bitrix\Main\DB\SqlExpression $field1Value)
	 * @method bool hasField1Value()
	 * @method bool isField1ValueFilled()
	 * @method bool isField1ValueChanged()
	 * @method \string remindActualField1Value()
	 * @method \string requireField1Value()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetField1Value()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetField1Value()
	 * @method \string fillField1Value()
	 * @method \string getField2Name()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setField2Name(\string|\Bitrix\Main\DB\SqlExpression $field2Name)
	 * @method bool hasField2Name()
	 * @method bool isField2NameFilled()
	 * @method bool isField2NameChanged()
	 * @method \string remindActualField2Name()
	 * @method \string requireField2Name()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetField2Name()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetField2Name()
	 * @method \string fillField2Name()
	 * @method \string getField2Value()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setField2Value(\string|\Bitrix\Main\DB\SqlExpression $field2Value)
	 * @method bool hasField2Value()
	 * @method bool isField2ValueFilled()
	 * @method bool isField2ValueChanged()
	 * @method \string remindActualField2Value()
	 * @method \string requireField2Value()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetField2Value()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetField2Value()
	 * @method \string fillField2Value()
	 * @method \string getSiteTemplateId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setSiteTemplateId(\string|\Bitrix\Main\DB\SqlExpression $siteTemplateId)
	 * @method bool hasSiteTemplateId()
	 * @method bool isSiteTemplateIdFilled()
	 * @method bool isSiteTemplateIdChanged()
	 * @method \string remindActualSiteTemplateId()
	 * @method \string requireSiteTemplateId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetSiteTemplateId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetSiteTemplateId()
	 * @method \string fillSiteTemplateId()
	 * @method array getAdditionalField()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setAdditionalField(array|\Bitrix\Main\DB\SqlExpression $additionalField)
	 * @method bool hasAdditionalField()
	 * @method bool isAdditionalFieldFilled()
	 * @method bool isAdditionalFieldChanged()
	 * @method array remindActualAdditionalField()
	 * @method array requireAdditionalField()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetAdditionalField()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetAdditionalField()
	 * @method array fillAdditionalField()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite getEventMessageSite()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite remindActualEventMessageSite()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite requireEventMessageSite()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setEventMessageSite(\Bitrix\Main\Mail\Internal\EO_EventMessageSite $object)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetEventMessageSite()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetEventMessageSite()
	 * @method bool hasEventMessageSite()
	 * @method bool isEventMessageSiteFilled()
	 * @method bool isEventMessageSiteChanged()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite fillEventMessageSite()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage resetLanguageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unsetLanguageId()
	 * @method \string fillLanguageId()
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
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage set($fieldName, $value)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage reset($fieldName)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessage wakeUp($data)
	 */
	class EO_EventMessage {
		/* @var \Bitrix\Main\Mail\Internal\EventMessageTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventMessageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventMessage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getEventNameList()
	 * @method \string[] fillEventName()
	 * @method \string[] getLidList()
	 * @method \string[] fillLid()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getEmailFromList()
	 * @method \string[] fillEmailFrom()
	 * @method \string[] getEmailToList()
	 * @method \string[] fillEmailTo()
	 * @method \string[] getSubjectList()
	 * @method \string[] fillSubject()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \string[] getMessagePhpList()
	 * @method \string[] fillMessagePhp()
	 * @method \string[] getBodyTypeList()
	 * @method \string[] fillBodyType()
	 * @method \string[] getBccList()
	 * @method \string[] fillBcc()
	 * @method \string[] getReplyToList()
	 * @method \string[] fillReplyTo()
	 * @method \string[] getCcList()
	 * @method \string[] fillCc()
	 * @method \string[] getInReplyToList()
	 * @method \string[] fillInReplyTo()
	 * @method \string[] getPriorityList()
	 * @method \string[] fillPriority()
	 * @method \string[] getField1NameList()
	 * @method \string[] fillField1Name()
	 * @method \string[] getField1ValueList()
	 * @method \string[] fillField1Value()
	 * @method \string[] getField2NameList()
	 * @method \string[] fillField2Name()
	 * @method \string[] getField2ValueList()
	 * @method \string[] fillField2Value()
	 * @method \string[] getSiteTemplateIdList()
	 * @method \string[] fillSiteTemplateId()
	 * @method array[] getAdditionalFieldList()
	 * @method array[] fillAdditionalField()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite[] getEventMessageSiteList()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage_Collection getEventMessageSiteCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite_Collection fillEventMessageSite()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Mail\Internal\EO_EventMessage $object)
	 * @method bool has(\Bitrix\Main\Mail\Internal\EO_EventMessage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage getByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage[] getAll()
	 * @method bool remove(\Bitrix\Main\Mail\Internal\EO_EventMessage $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EventMessage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Mail\Internal\EventMessageTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventMessageTable';
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventMessage_Result exec()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventMessage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage_Collection fetchCollection()
	 */
	class EO_EventMessage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage_Collection createCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage wakeUpObject($row)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessage_Collection wakeUpCollection($rows)
	 */
	class EO_EventMessage_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Mail\Internal\EventMessageAttachmentTable:main/lib/mail/internal/eventmessageattachment.php */
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventMessageAttachment
	 * @see \Bitrix\Main\Mail\Internal\EventMessageAttachmentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getEventMessageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment setEventMessageId(\int|\Bitrix\Main\DB\SqlExpression $eventMessageId)
	 * @method bool hasEventMessageId()
	 * @method bool isEventMessageIdFilled()
	 * @method bool isEventMessageIdChanged()
	 * @method \int getFileId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
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
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment set($fieldName, $value)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment reset($fieldName)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment wakeUp($data)
	 */
	class EO_EventMessageAttachment {
		/* @var \Bitrix\Main\Mail\Internal\EventMessageAttachmentTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventMessageAttachmentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventMessageAttachment_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getEventMessageIdList()
	 * @method \int[] getFileIdList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Mail\Internal\EO_EventMessageAttachment $object)
	 * @method bool has(\Bitrix\Main\Mail\Internal\EO_EventMessageAttachment $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment getByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment[] getAll()
	 * @method bool remove(\Bitrix\Main\Mail\Internal\EO_EventMessageAttachment $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EventMessageAttachment_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Mail\Internal\EventMessageAttachmentTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventMessageAttachmentTable';
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventMessageAttachment_Result exec()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventMessageAttachment_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment_Collection fetchCollection()
	 */
	class EO_EventMessageAttachment_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment_Collection createCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment wakeUpObject($row)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageAttachment_Collection wakeUpCollection($rows)
	 */
	class EO_EventMessageAttachment_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Mail\Internal\EventMessageSiteTable:main/lib/mail/internal/eventmessagesite.php */
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventMessageSite
	 * @see \Bitrix\Main\Mail\Internal\EventMessageSiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getEventMessageId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite setEventMessageId(\int|\Bitrix\Main\DB\SqlExpression $eventMessageId)
	 * @method bool hasEventMessageId()
	 * @method bool isEventMessageIdFilled()
	 * @method bool isEventMessageIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite resetSiteId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite unsetSiteId()
	 * @method \string fillSiteId()
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
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite set($fieldName, $value)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite reset($fieldName)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageSite wakeUp($data)
	 */
	class EO_EventMessageSite {
		/* @var \Bitrix\Main\Mail\Internal\EventMessageSiteTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventMessageSiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventMessageSite_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getEventMessageIdList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Mail\Internal\EO_EventMessageSite $object)
	 * @method bool has(\Bitrix\Main\Mail\Internal\EO_EventMessageSite $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite getByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite[] getAll()
	 * @method bool remove(\Bitrix\Main\Mail\Internal\EO_EventMessageSite $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventMessageSite_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EventMessageSite_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Mail\Internal\EventMessageSiteTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventMessageSiteTable';
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventMessageSite_Result exec()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventMessageSite_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite_Collection fetchCollection()
	 */
	class EO_EventMessageSite_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite_Collection createCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite wakeUpObject($row)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventMessageSite_Collection wakeUpCollection($rows)
	 */
	class EO_EventMessageSite_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Mail\Internal\EventTypeTable:main/lib/mail/internal/eventtype.php */
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventType
	 * @see \Bitrix\Main\Mail\Internal\EventTypeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getLid()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string remindActualLid()
	 * @method \string requireLid()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType resetLid()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType unsetLid()
	 * @method \string fillLid()
	 * @method \string getEventName()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType setEventName(\string|\Bitrix\Main\DB\SqlExpression $eventName)
	 * @method bool hasEventName()
	 * @method bool isEventNameFilled()
	 * @method bool isEventNameChanged()
	 * @method \string remindActualEventName()
	 * @method \string requireEventName()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType resetEventName()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType unsetEventName()
	 * @method \string fillEventName()
	 * @method \string getName()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType resetName()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType resetDescription()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType unsetDescription()
	 * @method \string fillDescription()
	 * @method \int getSort()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType resetSort()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType unsetSort()
	 * @method \int fillSort()
	 * @method \string getEventType()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType setEventType(\string|\Bitrix\Main\DB\SqlExpression $eventType)
	 * @method bool hasEventType()
	 * @method bool isEventTypeFilled()
	 * @method bool isEventTypeChanged()
	 * @method \string remindActualEventType()
	 * @method \string requireEventType()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType resetEventType()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType unsetEventType()
	 * @method \string fillEventType()
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
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType set($fieldName, $value)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType reset($fieldName)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventType wakeUp($data)
	 */
	class EO_EventType {
		/* @var \Bitrix\Main\Mail\Internal\EventTypeTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventTypeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_EventType_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getLidList()
	 * @method \string[] fillLid()
	 * @method \string[] getEventNameList()
	 * @method \string[] fillEventName()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getEventTypeList()
	 * @method \string[] fillEventType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Mail\Internal\EO_EventType $object)
	 * @method bool has(\Bitrix\Main\Mail\Internal\EO_EventType $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType getByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType[] getAll()
	 * @method bool remove(\Bitrix\Main\Mail\Internal\EO_EventType $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Mail\Internal\EO_EventType_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EventType_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Mail\Internal\EventTypeTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\EventTypeTable';
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EventType_Result exec()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EventType_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType_Collection fetchCollection()
	 */
	class EO_EventType_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType_Collection createCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType wakeUpObject($row)
	 * @method \Bitrix\Main\Mail\Internal\EO_EventType_Collection wakeUpCollection($rows)
	 */
	class EO_EventType_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Mail\Internal\SenderTable:main/lib/mail/internal/sendertable.php */
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Sender
	 * @see \Bitrix\Main\Mail\Internal\SenderTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Mail\Internal\Sender setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEmail()
	 * @method \Bitrix\Main\Mail\Internal\Sender setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Main\Mail\Internal\Sender resetEmail()
	 * @method \Bitrix\Main\Mail\Internal\Sender unsetEmail()
	 * @method \string fillEmail()
	 * @method \string getName()
	 * @method \Bitrix\Main\Mail\Internal\Sender setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\Mail\Internal\Sender resetName()
	 * @method \Bitrix\Main\Mail\Internal\Sender unsetName()
	 * @method \string fillName()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\Mail\Internal\Sender setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\Mail\Internal\Sender resetUserId()
	 * @method \Bitrix\Main\Mail\Internal\Sender unsetUserId()
	 * @method \int fillUserId()
	 * @method \boolean getIsConfirmed()
	 * @method \Bitrix\Main\Mail\Internal\Sender setIsConfirmed(\boolean|\Bitrix\Main\DB\SqlExpression $isConfirmed)
	 * @method bool hasIsConfirmed()
	 * @method bool isIsConfirmedFilled()
	 * @method bool isIsConfirmedChanged()
	 * @method \boolean remindActualIsConfirmed()
	 * @method \boolean requireIsConfirmed()
	 * @method \Bitrix\Main\Mail\Internal\Sender resetIsConfirmed()
	 * @method \Bitrix\Main\Mail\Internal\Sender unsetIsConfirmed()
	 * @method \boolean fillIsConfirmed()
	 * @method \boolean getIsPublic()
	 * @method \Bitrix\Main\Mail\Internal\Sender setIsPublic(\boolean|\Bitrix\Main\DB\SqlExpression $isPublic)
	 * @method bool hasIsPublic()
	 * @method bool isIsPublicFilled()
	 * @method bool isIsPublicChanged()
	 * @method \boolean remindActualIsPublic()
	 * @method \boolean requireIsPublic()
	 * @method \Bitrix\Main\Mail\Internal\Sender resetIsPublic()
	 * @method \Bitrix\Main\Mail\Internal\Sender unsetIsPublic()
	 * @method \boolean fillIsPublic()
	 * @method array getOptions()
	 * @method \Bitrix\Main\Mail\Internal\Sender setOptions(array|\Bitrix\Main\DB\SqlExpression $options)
	 * @method bool hasOptions()
	 * @method bool isOptionsFilled()
	 * @method bool isOptionsChanged()
	 * @method array remindActualOptions()
	 * @method array requireOptions()
	 * @method \Bitrix\Main\Mail\Internal\Sender resetOptions()
	 * @method \Bitrix\Main\Mail\Internal\Sender unsetOptions()
	 * @method array fillOptions()
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
	 * @method \Bitrix\Main\Mail\Internal\Sender set($fieldName, $value)
	 * @method \Bitrix\Main\Mail\Internal\Sender reset($fieldName)
	 * @method \Bitrix\Main\Mail\Internal\Sender unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Mail\Internal\Sender wakeUp($data)
	 */
	class EO_Sender {
		/* @var \Bitrix\Main\Mail\Internal\SenderTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\SenderTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_Sender_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \boolean[] getIsConfirmedList()
	 * @method \boolean[] fillIsConfirmed()
	 * @method \boolean[] getIsPublicList()
	 * @method \boolean[] fillIsPublic()
	 * @method array[] getOptionsList()
	 * @method array[] fillOptions()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Mail\Internal\Sender $object)
	 * @method bool has(\Bitrix\Main\Mail\Internal\Sender $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\Sender getByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\Sender[] getAll()
	 * @method bool remove(\Bitrix\Main\Mail\Internal\Sender $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Mail\Internal\EO_Sender_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Mail\Internal\Sender current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Sender_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Mail\Internal\SenderTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\SenderTable';
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Sender_Result exec()
	 * @method \Bitrix\Main\Mail\Internal\Sender fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_Sender_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Sender_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\Sender fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_Sender_Collection fetchCollection()
	 */
	class EO_Sender_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\Sender createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Mail\Internal\EO_Sender_Collection createCollection()
	 * @method \Bitrix\Main\Mail\Internal\Sender wakeUpObject($row)
	 * @method \Bitrix\Main\Mail\Internal\EO_Sender_Collection wakeUpCollection($rows)
	 */
	class EO_Sender_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Mail\Internal\SenderSendCounterTable:main/lib/mail/internal/sendersendcounter.php */
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_SenderSendCounter
	 * @see \Bitrix\Main\Mail\Internal\SenderSendCounterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date getDateStat()
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter setDateStat(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $dateStat)
	 * @method bool hasDateStat()
	 * @method bool isDateStatFilled()
	 * @method bool isDateStatChanged()
	 * @method \string getEmail()
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \int getCnt()
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter setCnt(\int|\Bitrix\Main\DB\SqlExpression $cnt)
	 * @method bool hasCnt()
	 * @method bool isCntFilled()
	 * @method bool isCntChanged()
	 * @method \int remindActualCnt()
	 * @method \int requireCnt()
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter resetCnt()
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter unsetCnt()
	 * @method \int fillCnt()
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
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter set($fieldName, $value)
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter reset($fieldName)
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Mail\Internal\EO_SenderSendCounter wakeUp($data)
	 */
	class EO_SenderSendCounter {
		/* @var \Bitrix\Main\Mail\Internal\SenderSendCounterTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\SenderSendCounterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * EO_SenderSendCounter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date[] getDateStatList()
	 * @method \string[] getEmailList()
	 * @method \int[] getCntList()
	 * @method \int[] fillCnt()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Mail\Internal\EO_SenderSendCounter $object)
	 * @method bool has(\Bitrix\Main\Mail\Internal\EO_SenderSendCounter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter getByPrimary($primary)
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter[] getAll()
	 * @method bool remove(\Bitrix\Main\Mail\Internal\EO_SenderSendCounter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Mail\Internal\EO_SenderSendCounter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SenderSendCounter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Mail\Internal\SenderSendCounterTable */
		static public $dataClass = '\Bitrix\Main\Mail\Internal\SenderSendCounterTable';
	}
}
namespace Bitrix\Main\Mail\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SenderSendCounter_Result exec()
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SenderSendCounter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter fetchObject()
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter_Collection fetchCollection()
	 */
	class EO_SenderSendCounter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter_Collection createCollection()
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter wakeUpObject($row)
	 * @method \Bitrix\Main\Mail\Internal\EO_SenderSendCounter_Collection wakeUpCollection($rows)
	 */
	class EO_SenderSendCounter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Numerator\Model\NumeratorTable:main/lib/numerator/model/numerator.php */
namespace Bitrix\Main\Numerator\Model {
	/**
	 * EO_Numerator
	 * @see \Bitrix\Main\Numerator\Model\NumeratorTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator resetName()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator unsetName()
	 * @method \string fillName()
	 * @method \string getTemplate()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator setTemplate(\string|\Bitrix\Main\DB\SqlExpression $template)
	 * @method bool hasTemplate()
	 * @method bool isTemplateFilled()
	 * @method bool isTemplateChanged()
	 * @method \string remindActualTemplate()
	 * @method \string requireTemplate()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator resetTemplate()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator unsetTemplate()
	 * @method \string fillTemplate()
	 * @method \string getSettings()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator setSettings(\string|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method \string remindActualSettings()
	 * @method \string requireSettings()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator resetSettings()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator unsetSettings()
	 * @method \string fillSettings()
	 * @method \string getType()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator resetType()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator unsetType()
	 * @method \string fillType()
	 * @method \Bitrix\Main\Type\DateTime getCreatedAt()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator setCreatedAt(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createdAt)
	 * @method bool hasCreatedAt()
	 * @method bool isCreatedAtFilled()
	 * @method bool isCreatedAtChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreatedAt()
	 * @method \Bitrix\Main\Type\DateTime requireCreatedAt()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator resetCreatedAt()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator unsetCreatedAt()
	 * @method \Bitrix\Main\Type\DateTime fillCreatedAt()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator resetCreatedBy()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getUpdatedAt()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator setUpdatedAt(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $updatedAt)
	 * @method bool hasUpdatedAt()
	 * @method bool isUpdatedAtFilled()
	 * @method bool isUpdatedAtChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualUpdatedAt()
	 * @method \Bitrix\Main\Type\DateTime requireUpdatedAt()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator resetUpdatedAt()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator unsetUpdatedAt()
	 * @method \Bitrix\Main\Type\DateTime fillUpdatedAt()
	 * @method \int getUpdatedBy()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator setUpdatedBy(\int|\Bitrix\Main\DB\SqlExpression $updatedBy)
	 * @method bool hasUpdatedBy()
	 * @method bool isUpdatedByFilled()
	 * @method bool isUpdatedByChanged()
	 * @method \int remindActualUpdatedBy()
	 * @method \int requireUpdatedBy()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator resetUpdatedBy()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator unsetUpdatedBy()
	 * @method \int fillUpdatedBy()
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
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator set($fieldName, $value)
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator reset($fieldName)
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Numerator\Model\EO_Numerator wakeUp($data)
	 */
	class EO_Numerator {
		/* @var \Bitrix\Main\Numerator\Model\NumeratorTable */
		static public $dataClass = '\Bitrix\Main\Numerator\Model\NumeratorTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Numerator\Model {
	/**
	 * EO_Numerator_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getTemplateList()
	 * @method \string[] fillTemplate()
	 * @method \string[] getSettingsList()
	 * @method \string[] fillSettings()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedAtList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreatedAt()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getUpdatedAtList()
	 * @method \Bitrix\Main\Type\DateTime[] fillUpdatedAt()
	 * @method \int[] getUpdatedByList()
	 * @method \int[] fillUpdatedBy()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Numerator\Model\EO_Numerator $object)
	 * @method bool has(\Bitrix\Main\Numerator\Model\EO_Numerator $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator getByPrimary($primary)
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator[] getAll()
	 * @method bool remove(\Bitrix\Main\Numerator\Model\EO_Numerator $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Numerator\Model\EO_Numerator_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Numerator_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Numerator\Model\NumeratorTable */
		static public $dataClass = '\Bitrix\Main\Numerator\Model\NumeratorTable';
	}
}
namespace Bitrix\Main\Numerator\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Numerator_Result exec()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator fetchObject()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Numerator_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator fetchObject()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator_Collection fetchCollection()
	 */
	class EO_Numerator_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator_Collection createCollection()
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator wakeUpObject($row)
	 * @method \Bitrix\Main\Numerator\Model\EO_Numerator_Collection wakeUpCollection($rows)
	 */
	class EO_Numerator_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Numerator\Model\NumeratorSequenceTable:main/lib/numerator/model/numeratorsequence.php */
namespace Bitrix\Main\Numerator\Model {
	/**
	 * EO_NumeratorSequence
	 * @see \Bitrix\Main\Numerator\Model\NumeratorSequenceTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getNumeratorId()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence setNumeratorId(\int|\Bitrix\Main\DB\SqlExpression $numeratorId)
	 * @method bool hasNumeratorId()
	 * @method bool isNumeratorIdFilled()
	 * @method bool isNumeratorIdChanged()
	 * @method \string getKey()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence setKey(\string|\Bitrix\Main\DB\SqlExpression $key)
	 * @method bool hasKey()
	 * @method bool isKeyFilled()
	 * @method bool isKeyChanged()
	 * @method \string getTextKey()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence setTextKey(\string|\Bitrix\Main\DB\SqlExpression $textKey)
	 * @method bool hasTextKey()
	 * @method bool isTextKeyFilled()
	 * @method bool isTextKeyChanged()
	 * @method \string remindActualTextKey()
	 * @method \string requireTextKey()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence resetTextKey()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence unsetTextKey()
	 * @method \string fillTextKey()
	 * @method \int getNextNumber()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence setNextNumber(\int|\Bitrix\Main\DB\SqlExpression $nextNumber)
	 * @method bool hasNextNumber()
	 * @method bool isNextNumberFilled()
	 * @method bool isNextNumberChanged()
	 * @method \int remindActualNextNumber()
	 * @method \int requireNextNumber()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence resetNextNumber()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence unsetNextNumber()
	 * @method \int fillNextNumber()
	 * @method \int getLastInvocationTime()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence setLastInvocationTime(\int|\Bitrix\Main\DB\SqlExpression $lastInvocationTime)
	 * @method bool hasLastInvocationTime()
	 * @method bool isLastInvocationTimeFilled()
	 * @method bool isLastInvocationTimeChanged()
	 * @method \int remindActualLastInvocationTime()
	 * @method \int requireLastInvocationTime()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence resetLastInvocationTime()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence unsetLastInvocationTime()
	 * @method \int fillLastInvocationTime()
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
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence set($fieldName, $value)
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence reset($fieldName)
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Numerator\Model\EO_NumeratorSequence wakeUp($data)
	 */
	class EO_NumeratorSequence {
		/* @var \Bitrix\Main\Numerator\Model\NumeratorSequenceTable */
		static public $dataClass = '\Bitrix\Main\Numerator\Model\NumeratorSequenceTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Numerator\Model {
	/**
	 * EO_NumeratorSequence_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getNumeratorIdList()
	 * @method \string[] getKeyList()
	 * @method \string[] getTextKeyList()
	 * @method \string[] fillTextKey()
	 * @method \int[] getNextNumberList()
	 * @method \int[] fillNextNumber()
	 * @method \int[] getLastInvocationTimeList()
	 * @method \int[] fillLastInvocationTime()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Numerator\Model\EO_NumeratorSequence $object)
	 * @method bool has(\Bitrix\Main\Numerator\Model\EO_NumeratorSequence $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence getByPrimary($primary)
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence[] getAll()
	 * @method bool remove(\Bitrix\Main\Numerator\Model\EO_NumeratorSequence $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Numerator\Model\EO_NumeratorSequence_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_NumeratorSequence_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Numerator\Model\NumeratorSequenceTable */
		static public $dataClass = '\Bitrix\Main\Numerator\Model\NumeratorSequenceTable';
	}
}
namespace Bitrix\Main\Numerator\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_NumeratorSequence_Result exec()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence fetchObject()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_NumeratorSequence_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence fetchObject()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence_Collection fetchCollection()
	 */
	class EO_NumeratorSequence_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence_Collection createCollection()
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence wakeUpObject($row)
	 * @method \Bitrix\Main\Numerator\Model\EO_NumeratorSequence_Collection wakeUpCollection($rows)
	 */
	class EO_NumeratorSequence_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\OperationTable:main/lib/operation.php */
namespace Bitrix\Main {
	/**
	 * EO_Operation
	 * @see \Bitrix\Main\OperationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_Operation setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Main\EO_Operation setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\EO_Operation resetName()
	 * @method \Bitrix\Main\EO_Operation unsetName()
	 * @method \string fillName()
	 * @method \string getModuleId()
	 * @method \Bitrix\Main\EO_Operation setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Main\EO_Operation resetModuleId()
	 * @method \Bitrix\Main\EO_Operation unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getDescription()
	 * @method \Bitrix\Main\EO_Operation setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Main\EO_Operation resetDescription()
	 * @method \Bitrix\Main\EO_Operation unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getBinding()
	 * @method \Bitrix\Main\EO_Operation setBinding(\string|\Bitrix\Main\DB\SqlExpression $binding)
	 * @method bool hasBinding()
	 * @method bool isBindingFilled()
	 * @method bool isBindingChanged()
	 * @method \string remindActualBinding()
	 * @method \string requireBinding()
	 * @method \Bitrix\Main\EO_Operation resetBinding()
	 * @method \Bitrix\Main\EO_Operation unsetBinding()
	 * @method \string fillBinding()
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
	 * @method \Bitrix\Main\EO_Operation set($fieldName, $value)
	 * @method \Bitrix\Main\EO_Operation reset($fieldName)
	 * @method \Bitrix\Main\EO_Operation unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_Operation wakeUp($data)
	 */
	class EO_Operation {
		/* @var \Bitrix\Main\OperationTable */
		static public $dataClass = '\Bitrix\Main\OperationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_Operation_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getBindingList()
	 * @method \string[] fillBinding()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_Operation $object)
	 * @method bool has(\Bitrix\Main\EO_Operation $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_Operation getByPrimary($primary)
	 * @method \Bitrix\Main\EO_Operation[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_Operation $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_Operation_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_Operation current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Operation_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\OperationTable */
		static public $dataClass = '\Bitrix\Main\OperationTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Operation_Result exec()
	 * @method \Bitrix\Main\EO_Operation fetchObject()
	 * @method \Bitrix\Main\EO_Operation_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Operation_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_Operation fetchObject()
	 * @method \Bitrix\Main\EO_Operation_Collection fetchCollection()
	 */
	class EO_Operation_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_Operation createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_Operation_Collection createCollection()
	 * @method \Bitrix\Main\EO_Operation wakeUpObject($row)
	 * @method \Bitrix\Main\EO_Operation_Collection wakeUpCollection($rows)
	 */
	class EO_Operation_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Rating\RatingTable:main/lib/rating/rating.php */
namespace Bitrix\Main\Rating {
	/**
	 * EO_Rating
	 * @see \Bitrix\Main\Rating\RatingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Rating\EO_Rating setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getActive()
	 * @method \Bitrix\Main\Rating\EO_Rating setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Main\Rating\EO_Rating resetActive()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetActive()
	 * @method \string fillActive()
	 * @method \string getName()
	 * @method \Bitrix\Main\Rating\EO_Rating setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\Rating\EO_Rating resetName()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetName()
	 * @method \string fillName()
	 * @method \string getEntityId()
	 * @method \Bitrix\Main\Rating\EO_Rating setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string remindActualEntityId()
	 * @method \string requireEntityId()
	 * @method \Bitrix\Main\Rating\EO_Rating resetEntityId()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetEntityId()
	 * @method \string fillEntityId()
	 * @method \string getCalculationMethod()
	 * @method \Bitrix\Main\Rating\EO_Rating setCalculationMethod(\string|\Bitrix\Main\DB\SqlExpression $calculationMethod)
	 * @method bool hasCalculationMethod()
	 * @method bool isCalculationMethodFilled()
	 * @method bool isCalculationMethodChanged()
	 * @method \string remindActualCalculationMethod()
	 * @method \string requireCalculationMethod()
	 * @method \Bitrix\Main\Rating\EO_Rating resetCalculationMethod()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetCalculationMethod()
	 * @method \string fillCalculationMethod()
	 * @method \Bitrix\Main\Type\DateTime getCreated()
	 * @method \Bitrix\Main\Rating\EO_Rating setCreated(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $created)
	 * @method bool hasCreated()
	 * @method bool isCreatedFilled()
	 * @method bool isCreatedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreated()
	 * @method \Bitrix\Main\Type\DateTime requireCreated()
	 * @method \Bitrix\Main\Rating\EO_Rating resetCreated()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetCreated()
	 * @method \Bitrix\Main\Type\DateTime fillCreated()
	 * @method \Bitrix\Main\Type\DateTime getLastModified()
	 * @method \Bitrix\Main\Rating\EO_Rating setLastModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastModified)
	 * @method bool hasLastModified()
	 * @method bool isLastModifiedFilled()
	 * @method bool isLastModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastModified()
	 * @method \Bitrix\Main\Type\DateTime requireLastModified()
	 * @method \Bitrix\Main\Rating\EO_Rating resetLastModified()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetLastModified()
	 * @method \Bitrix\Main\Type\DateTime fillLastModified()
	 * @method \Bitrix\Main\Type\DateTime getLastCalculated()
	 * @method \Bitrix\Main\Rating\EO_Rating setLastCalculated(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastCalculated)
	 * @method bool hasLastCalculated()
	 * @method bool isLastCalculatedFilled()
	 * @method bool isLastCalculatedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastCalculated()
	 * @method \Bitrix\Main\Type\DateTime requireLastCalculated()
	 * @method \Bitrix\Main\Rating\EO_Rating resetLastCalculated()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetLastCalculated()
	 * @method \Bitrix\Main\Type\DateTime fillLastCalculated()
	 * @method \boolean getPosition()
	 * @method \Bitrix\Main\Rating\EO_Rating setPosition(\boolean|\Bitrix\Main\DB\SqlExpression $position)
	 * @method bool hasPosition()
	 * @method bool isPositionFilled()
	 * @method bool isPositionChanged()
	 * @method \boolean remindActualPosition()
	 * @method \boolean requirePosition()
	 * @method \Bitrix\Main\Rating\EO_Rating resetPosition()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetPosition()
	 * @method \boolean fillPosition()
	 * @method \boolean getAuthority()
	 * @method \Bitrix\Main\Rating\EO_Rating setAuthority(\boolean|\Bitrix\Main\DB\SqlExpression $authority)
	 * @method bool hasAuthority()
	 * @method bool isAuthorityFilled()
	 * @method bool isAuthorityChanged()
	 * @method \boolean remindActualAuthority()
	 * @method \boolean requireAuthority()
	 * @method \Bitrix\Main\Rating\EO_Rating resetAuthority()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetAuthority()
	 * @method \boolean fillAuthority()
	 * @method \boolean getCalculated()
	 * @method \Bitrix\Main\Rating\EO_Rating setCalculated(\boolean|\Bitrix\Main\DB\SqlExpression $calculated)
	 * @method bool hasCalculated()
	 * @method bool isCalculatedFilled()
	 * @method bool isCalculatedChanged()
	 * @method \boolean remindActualCalculated()
	 * @method \boolean requireCalculated()
	 * @method \Bitrix\Main\Rating\EO_Rating resetCalculated()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetCalculated()
	 * @method \boolean fillCalculated()
	 * @method \string getConfigs()
	 * @method \Bitrix\Main\Rating\EO_Rating setConfigs(\string|\Bitrix\Main\DB\SqlExpression $configs)
	 * @method bool hasConfigs()
	 * @method bool isConfigsFilled()
	 * @method bool isConfigsChanged()
	 * @method \string remindActualConfigs()
	 * @method \string requireConfigs()
	 * @method \Bitrix\Main\Rating\EO_Rating resetConfigs()
	 * @method \Bitrix\Main\Rating\EO_Rating unsetConfigs()
	 * @method \string fillConfigs()
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
	 * @method \Bitrix\Main\Rating\EO_Rating set($fieldName, $value)
	 * @method \Bitrix\Main\Rating\EO_Rating reset($fieldName)
	 * @method \Bitrix\Main\Rating\EO_Rating unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Rating\EO_Rating wakeUp($data)
	 */
	class EO_Rating {
		/* @var \Bitrix\Main\Rating\RatingTable */
		static public $dataClass = '\Bitrix\Main\Rating\RatingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Rating {
	/**
	 * EO_Rating_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getEntityIdList()
	 * @method \string[] fillEntityId()
	 * @method \string[] getCalculationMethodList()
	 * @method \string[] fillCalculationMethod()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreated()
	 * @method \Bitrix\Main\Type\DateTime[] getLastModifiedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastModified()
	 * @method \Bitrix\Main\Type\DateTime[] getLastCalculatedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastCalculated()
	 * @method \boolean[] getPositionList()
	 * @method \boolean[] fillPosition()
	 * @method \boolean[] getAuthorityList()
	 * @method \boolean[] fillAuthority()
	 * @method \boolean[] getCalculatedList()
	 * @method \boolean[] fillCalculated()
	 * @method \string[] getConfigsList()
	 * @method \string[] fillConfigs()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Rating\EO_Rating $object)
	 * @method bool has(\Bitrix\Main\Rating\EO_Rating $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Rating\EO_Rating getByPrimary($primary)
	 * @method \Bitrix\Main\Rating\EO_Rating[] getAll()
	 * @method bool remove(\Bitrix\Main\Rating\EO_Rating $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Rating\EO_Rating_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Rating\EO_Rating current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Rating_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Rating\RatingTable */
		static public $dataClass = '\Bitrix\Main\Rating\RatingTable';
	}
}
namespace Bitrix\Main\Rating {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Rating_Result exec()
	 * @method \Bitrix\Main\Rating\EO_Rating fetchObject()
	 * @method \Bitrix\Main\Rating\EO_Rating_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Rating_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Rating\EO_Rating fetchObject()
	 * @method \Bitrix\Main\Rating\EO_Rating_Collection fetchCollection()
	 */
	class EO_Rating_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Rating\EO_Rating createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Rating\EO_Rating_Collection createCollection()
	 * @method \Bitrix\Main\Rating\EO_Rating wakeUpObject($row)
	 * @method \Bitrix\Main\Rating\EO_Rating_Collection wakeUpCollection($rows)
	 */
	class EO_Rating_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Rating\ResultsTable:main/lib/rating/results.php */
namespace Bitrix\Main\Rating {
	/**
	 * EO_Results
	 * @see \Bitrix\Main\Rating\ResultsTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Rating\EO_Results setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getRatingId()
	 * @method \Bitrix\Main\Rating\EO_Results setRatingId(\int|\Bitrix\Main\DB\SqlExpression $ratingId)
	 * @method bool hasRatingId()
	 * @method bool isRatingIdFilled()
	 * @method bool isRatingIdChanged()
	 * @method \int remindActualRatingId()
	 * @method \int requireRatingId()
	 * @method \Bitrix\Main\Rating\EO_Results resetRatingId()
	 * @method \Bitrix\Main\Rating\EO_Results unsetRatingId()
	 * @method \int fillRatingId()
	 * @method \string getEntityTypeId()
	 * @method \Bitrix\Main\Rating\EO_Results setEntityTypeId(\string|\Bitrix\Main\DB\SqlExpression $entityTypeId)
	 * @method bool hasEntityTypeId()
	 * @method bool isEntityTypeIdFilled()
	 * @method bool isEntityTypeIdChanged()
	 * @method \string remindActualEntityTypeId()
	 * @method \string requireEntityTypeId()
	 * @method \Bitrix\Main\Rating\EO_Results resetEntityTypeId()
	 * @method \Bitrix\Main\Rating\EO_Results unsetEntityTypeId()
	 * @method \string fillEntityTypeId()
	 * @method \int getEntityId()
	 * @method \Bitrix\Main\Rating\EO_Results setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Main\Rating\EO_Results resetEntityId()
	 * @method \Bitrix\Main\Rating\EO_Results unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \float getCurrentValue()
	 * @method \Bitrix\Main\Rating\EO_Results setCurrentValue(\float|\Bitrix\Main\DB\SqlExpression $currentValue)
	 * @method bool hasCurrentValue()
	 * @method bool isCurrentValueFilled()
	 * @method bool isCurrentValueChanged()
	 * @method \float remindActualCurrentValue()
	 * @method \float requireCurrentValue()
	 * @method \Bitrix\Main\Rating\EO_Results resetCurrentValue()
	 * @method \Bitrix\Main\Rating\EO_Results unsetCurrentValue()
	 * @method \float fillCurrentValue()
	 * @method \float getPreviousValue()
	 * @method \Bitrix\Main\Rating\EO_Results setPreviousValue(\float|\Bitrix\Main\DB\SqlExpression $previousValue)
	 * @method bool hasPreviousValue()
	 * @method bool isPreviousValueFilled()
	 * @method bool isPreviousValueChanged()
	 * @method \float remindActualPreviousValue()
	 * @method \float requirePreviousValue()
	 * @method \Bitrix\Main\Rating\EO_Results resetPreviousValue()
	 * @method \Bitrix\Main\Rating\EO_Results unsetPreviousValue()
	 * @method \float fillPreviousValue()
	 * @method \int getCurrentPosition()
	 * @method \Bitrix\Main\Rating\EO_Results setCurrentPosition(\int|\Bitrix\Main\DB\SqlExpression $currentPosition)
	 * @method bool hasCurrentPosition()
	 * @method bool isCurrentPositionFilled()
	 * @method bool isCurrentPositionChanged()
	 * @method \int remindActualCurrentPosition()
	 * @method \int requireCurrentPosition()
	 * @method \Bitrix\Main\Rating\EO_Results resetCurrentPosition()
	 * @method \Bitrix\Main\Rating\EO_Results unsetCurrentPosition()
	 * @method \int fillCurrentPosition()
	 * @method \int getPreviousPosition()
	 * @method \Bitrix\Main\Rating\EO_Results setPreviousPosition(\int|\Bitrix\Main\DB\SqlExpression $previousPosition)
	 * @method bool hasPreviousPosition()
	 * @method bool isPreviousPositionFilled()
	 * @method bool isPreviousPositionChanged()
	 * @method \int remindActualPreviousPosition()
	 * @method \int requirePreviousPosition()
	 * @method \Bitrix\Main\Rating\EO_Results resetPreviousPosition()
	 * @method \Bitrix\Main\Rating\EO_Results unsetPreviousPosition()
	 * @method \int fillPreviousPosition()
	 * @method \Bitrix\Main\Rating\EO_Rating getRating()
	 * @method \Bitrix\Main\Rating\EO_Rating remindActualRating()
	 * @method \Bitrix\Main\Rating\EO_Rating requireRating()
	 * @method \Bitrix\Main\Rating\EO_Results setRating(\Bitrix\Main\Rating\EO_Rating $object)
	 * @method \Bitrix\Main\Rating\EO_Results resetRating()
	 * @method \Bitrix\Main\Rating\EO_Results unsetRating()
	 * @method bool hasRating()
	 * @method bool isRatingFilled()
	 * @method bool isRatingChanged()
	 * @method \Bitrix\Main\Rating\EO_Rating fillRating()
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
	 * @method \Bitrix\Main\Rating\EO_Results set($fieldName, $value)
	 * @method \Bitrix\Main\Rating\EO_Results reset($fieldName)
	 * @method \Bitrix\Main\Rating\EO_Results unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Rating\EO_Results wakeUp($data)
	 */
	class EO_Results {
		/* @var \Bitrix\Main\Rating\ResultsTable */
		static public $dataClass = '\Bitrix\Main\Rating\ResultsTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Rating {
	/**
	 * EO_Results_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getRatingIdList()
	 * @method \int[] fillRatingId()
	 * @method \string[] getEntityTypeIdList()
	 * @method \string[] fillEntityTypeId()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \float[] getCurrentValueList()
	 * @method \float[] fillCurrentValue()
	 * @method \float[] getPreviousValueList()
	 * @method \float[] fillPreviousValue()
	 * @method \int[] getCurrentPositionList()
	 * @method \int[] fillCurrentPosition()
	 * @method \int[] getPreviousPositionList()
	 * @method \int[] fillPreviousPosition()
	 * @method \Bitrix\Main\Rating\EO_Rating[] getRatingList()
	 * @method \Bitrix\Main\Rating\EO_Results_Collection getRatingCollection()
	 * @method \Bitrix\Main\Rating\EO_Rating_Collection fillRating()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Rating\EO_Results $object)
	 * @method bool has(\Bitrix\Main\Rating\EO_Results $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Rating\EO_Results getByPrimary($primary)
	 * @method \Bitrix\Main\Rating\EO_Results[] getAll()
	 * @method bool remove(\Bitrix\Main\Rating\EO_Results $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Rating\EO_Results_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Rating\EO_Results current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Results_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Rating\ResultsTable */
		static public $dataClass = '\Bitrix\Main\Rating\ResultsTable';
	}
}
namespace Bitrix\Main\Rating {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Results_Result exec()
	 * @method \Bitrix\Main\Rating\EO_Results fetchObject()
	 * @method \Bitrix\Main\Rating\EO_Results_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Results_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Rating\EO_Results fetchObject()
	 * @method \Bitrix\Main\Rating\EO_Results_Collection fetchCollection()
	 */
	class EO_Results_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Rating\EO_Results createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Rating\EO_Results_Collection createCollection()
	 * @method \Bitrix\Main\Rating\EO_Results wakeUpObject($row)
	 * @method \Bitrix\Main\Rating\EO_Results_Collection wakeUpCollection($rows)
	 */
	class EO_Results_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Service\GeoIp\HandlerTable:main/lib/service/geoip/handler.php */
namespace Bitrix\Main\Service\GeoIp {
	/**
	 * EO_Handler
	 * @see \Bitrix\Main\Service\GeoIp\HandlerTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getSort()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler resetSort()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler unsetSort()
	 * @method \int fillSort()
	 * @method \boolean getActive()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler resetActive()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getClassName()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler setClassName(\string|\Bitrix\Main\DB\SqlExpression $className)
	 * @method bool hasClassName()
	 * @method bool isClassNameFilled()
	 * @method bool isClassNameChanged()
	 * @method \string remindActualClassName()
	 * @method \string requireClassName()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler resetClassName()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler unsetClassName()
	 * @method \string fillClassName()
	 * @method \string getConfig()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler setConfig(\string|\Bitrix\Main\DB\SqlExpression $config)
	 * @method bool hasConfig()
	 * @method bool isConfigFilled()
	 * @method bool isConfigChanged()
	 * @method \string remindActualConfig()
	 * @method \string requireConfig()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler resetConfig()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler unsetConfig()
	 * @method \string fillConfig()
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
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler set($fieldName, $value)
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler reset($fieldName)
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Service\GeoIp\EO_Handler wakeUp($data)
	 */
	class EO_Handler {
		/* @var \Bitrix\Main\Service\GeoIp\HandlerTable */
		static public $dataClass = '\Bitrix\Main\Service\GeoIp\HandlerTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Service\GeoIp {
	/**
	 * EO_Handler_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getClassNameList()
	 * @method \string[] fillClassName()
	 * @method \string[] getConfigList()
	 * @method \string[] fillConfig()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Service\GeoIp\EO_Handler $object)
	 * @method bool has(\Bitrix\Main\Service\GeoIp\EO_Handler $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler getByPrimary($primary)
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler[] getAll()
	 * @method bool remove(\Bitrix\Main\Service\GeoIp\EO_Handler $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Service\GeoIp\EO_Handler_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Handler_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Service\GeoIp\HandlerTable */
		static public $dataClass = '\Bitrix\Main\Service\GeoIp\HandlerTable';
	}
}
namespace Bitrix\Main\Service\GeoIp {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Handler_Result exec()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler fetchObject()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Handler_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler fetchObject()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler_Collection fetchCollection()
	 */
	class EO_Handler_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler_Collection createCollection()
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler wakeUpObject($row)
	 * @method \Bitrix\Main\Service\GeoIp\EO_Handler_Collection wakeUpCollection($rows)
	 */
	class EO_Handler_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Service\GeoIp\Internal\GeonameTable:main/lib/service/geoip/internal/geonametable.php */
namespace Bitrix\Main\Service\GeoIp\Internal {
	/**
	 * EO_Geoname
	 * @see \Bitrix\Main\Service\GeoIp\Internal\GeonameTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getLanguageCode()
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname setLanguageCode(\string|\Bitrix\Main\DB\SqlExpression $languageCode)
	 * @method bool hasLanguageCode()
	 * @method bool isLanguageCodeFilled()
	 * @method bool isLanguageCodeChanged()
	 * @method \string getName()
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname resetName()
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname unsetName()
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
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname set($fieldName, $value)
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname reset($fieldName)
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname wakeUp($data)
	 */
	class EO_Geoname {
		/* @var \Bitrix\Main\Service\GeoIp\Internal\GeonameTable */
		static public $dataClass = '\Bitrix\Main\Service\GeoIp\Internal\GeonameTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Service\GeoIp\Internal {
	/**
	 * EO_Geoname_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getLanguageCodeList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Service\GeoIp\Internal\EO_Geoname $object)
	 * @method bool has(\Bitrix\Main\Service\GeoIp\Internal\EO_Geoname $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname getByPrimary($primary)
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname[] getAll()
	 * @method bool remove(\Bitrix\Main\Service\GeoIp\Internal\EO_Geoname $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Geoname_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Service\GeoIp\Internal\GeonameTable */
		static public $dataClass = '\Bitrix\Main\Service\GeoIp\Internal\GeonameTable';
	}
}
namespace Bitrix\Main\Service\GeoIp\Internal {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Geoname_Result exec()
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname fetchObject()
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Geoname_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname fetchObject()
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname_Collection fetchCollection()
	 */
	class EO_Geoname_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname_Collection createCollection()
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname wakeUpObject($row)
	 * @method \Bitrix\Main\Service\GeoIp\Internal\EO_Geoname_Collection wakeUpCollection($rows)
	 */
	class EO_Geoname_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Session\Handlers\Table\UserSessionTable:main/lib/session/handlers/table/usersessiontable.php */
namespace Bitrix\Main\Session\Handlers\Table {
	/**
	 * EO_UserSession
	 * @see \Bitrix\Main\Session\Handlers\Table\UserSessionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getSessionId()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession setSessionId(\string|\Bitrix\Main\DB\SqlExpression $sessionId)
	 * @method bool hasSessionId()
	 * @method bool isSessionIdFilled()
	 * @method bool isSessionIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession resetTimestampX()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getSessionData()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession setSessionData(\string|\Bitrix\Main\DB\SqlExpression $sessionData)
	 * @method bool hasSessionData()
	 * @method bool isSessionDataFilled()
	 * @method bool isSessionDataChanged()
	 * @method \string remindActualSessionData()
	 * @method \string requireSessionData()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession resetSessionData()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession unsetSessionData()
	 * @method \string fillSessionData()
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
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession set($fieldName, $value)
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession reset($fieldName)
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Session\Handlers\Table\EO_UserSession wakeUp($data)
	 */
	class EO_UserSession {
		/* @var \Bitrix\Main\Session\Handlers\Table\UserSessionTable */
		static public $dataClass = '\Bitrix\Main\Session\Handlers\Table\UserSessionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Session\Handlers\Table {
	/**
	 * EO_UserSession_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getSessionIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getSessionDataList()
	 * @method \string[] fillSessionData()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Session\Handlers\Table\EO_UserSession $object)
	 * @method bool has(\Bitrix\Main\Session\Handlers\Table\EO_UserSession $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession getByPrimary($primary)
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession[] getAll()
	 * @method bool remove(\Bitrix\Main\Session\Handlers\Table\EO_UserSession $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Session\Handlers\Table\EO_UserSession_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserSession_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Session\Handlers\Table\UserSessionTable */
		static public $dataClass = '\Bitrix\Main\Session\Handlers\Table\UserSessionTable';
	}
}
namespace Bitrix\Main\Session\Handlers\Table {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserSession_Result exec()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession fetchObject()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserSession_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession fetchObject()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession_Collection fetchCollection()
	 */
	class EO_UserSession_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession_Collection createCollection()
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession wakeUpObject($row)
	 * @method \Bitrix\Main\Session\Handlers\Table\EO_UserSession_Collection wakeUpCollection($rows)
	 */
	class EO_UserSession_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\SiteTable:main/lib/site.php */
namespace Bitrix\Main {
	/**
	 * EO_Site
	 * @see \Bitrix\Main\SiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getLid()
	 * @method \Bitrix\Main\EO_Site setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string getId()
	 * @method \string remindActualId()
	 * @method \string requireId()
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method \Bitrix\Main\EO_Site unsetId()
	 * @method \string fillId()
	 * @method \int getSort()
	 * @method \Bitrix\Main\EO_Site setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Main\EO_Site resetSort()
	 * @method \Bitrix\Main\EO_Site unsetSort()
	 * @method \int fillSort()
	 * @method \boolean getDef()
	 * @method \Bitrix\Main\EO_Site setDef(\boolean|\Bitrix\Main\DB\SqlExpression $def)
	 * @method bool hasDef()
	 * @method bool isDefFilled()
	 * @method bool isDefChanged()
	 * @method \boolean remindActualDef()
	 * @method \boolean requireDef()
	 * @method \Bitrix\Main\EO_Site resetDef()
	 * @method \Bitrix\Main\EO_Site unsetDef()
	 * @method \boolean fillDef()
	 * @method \boolean getActive()
	 * @method \Bitrix\Main\EO_Site setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Main\EO_Site resetActive()
	 * @method \Bitrix\Main\EO_Site unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getName()
	 * @method \Bitrix\Main\EO_Site setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\EO_Site resetName()
	 * @method \Bitrix\Main\EO_Site unsetName()
	 * @method \string fillName()
	 * @method \string getDir()
	 * @method \Bitrix\Main\EO_Site setDir(\string|\Bitrix\Main\DB\SqlExpression $dir)
	 * @method bool hasDir()
	 * @method bool isDirFilled()
	 * @method bool isDirChanged()
	 * @method \string remindActualDir()
	 * @method \string requireDir()
	 * @method \Bitrix\Main\EO_Site resetDir()
	 * @method \Bitrix\Main\EO_Site unsetDir()
	 * @method \string fillDir()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Main\EO_Site setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\Main\EO_Site resetLanguageId()
	 * @method \Bitrix\Main\EO_Site unsetLanguageId()
	 * @method \string fillLanguageId()
	 * @method \string getDocRoot()
	 * @method \Bitrix\Main\EO_Site setDocRoot(\string|\Bitrix\Main\DB\SqlExpression $docRoot)
	 * @method bool hasDocRoot()
	 * @method bool isDocRootFilled()
	 * @method bool isDocRootChanged()
	 * @method \string remindActualDocRoot()
	 * @method \string requireDocRoot()
	 * @method \Bitrix\Main\EO_Site resetDocRoot()
	 * @method \Bitrix\Main\EO_Site unsetDocRoot()
	 * @method \string fillDocRoot()
	 * @method \boolean getDomainLimited()
	 * @method \Bitrix\Main\EO_Site setDomainLimited(\boolean|\Bitrix\Main\DB\SqlExpression $domainLimited)
	 * @method bool hasDomainLimited()
	 * @method bool isDomainLimitedFilled()
	 * @method bool isDomainLimitedChanged()
	 * @method \boolean remindActualDomainLimited()
	 * @method \boolean requireDomainLimited()
	 * @method \Bitrix\Main\EO_Site resetDomainLimited()
	 * @method \Bitrix\Main\EO_Site unsetDomainLimited()
	 * @method \boolean fillDomainLimited()
	 * @method \string getServerName()
	 * @method \Bitrix\Main\EO_Site setServerName(\string|\Bitrix\Main\DB\SqlExpression $serverName)
	 * @method bool hasServerName()
	 * @method bool isServerNameFilled()
	 * @method bool isServerNameChanged()
	 * @method \string remindActualServerName()
	 * @method \string requireServerName()
	 * @method \Bitrix\Main\EO_Site resetServerName()
	 * @method \Bitrix\Main\EO_Site unsetServerName()
	 * @method \string fillServerName()
	 * @method \string getSiteName()
	 * @method \Bitrix\Main\EO_Site setSiteName(\string|\Bitrix\Main\DB\SqlExpression $siteName)
	 * @method bool hasSiteName()
	 * @method bool isSiteNameFilled()
	 * @method bool isSiteNameChanged()
	 * @method \string remindActualSiteName()
	 * @method \string requireSiteName()
	 * @method \Bitrix\Main\EO_Site resetSiteName()
	 * @method \Bitrix\Main\EO_Site unsetSiteName()
	 * @method \string fillSiteName()
	 * @method \string getEmail()
	 * @method \Bitrix\Main\EO_Site setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Main\EO_Site resetEmail()
	 * @method \Bitrix\Main\EO_Site unsetEmail()
	 * @method \string fillEmail()
	 * @method \int getCultureId()
	 * @method \Bitrix\Main\EO_Site setCultureId(\int|\Bitrix\Main\DB\SqlExpression $cultureId)
	 * @method bool hasCultureId()
	 * @method bool isCultureIdFilled()
	 * @method bool isCultureIdChanged()
	 * @method \int remindActualCultureId()
	 * @method \int requireCultureId()
	 * @method \Bitrix\Main\EO_Site resetCultureId()
	 * @method \Bitrix\Main\EO_Site unsetCultureId()
	 * @method \int fillCultureId()
	 * @method \Bitrix\Main\Context\Culture getCulture()
	 * @method \Bitrix\Main\Context\Culture remindActualCulture()
	 * @method \Bitrix\Main\Context\Culture requireCulture()
	 * @method \Bitrix\Main\EO_Site setCulture(\Bitrix\Main\Context\Culture $object)
	 * @method \Bitrix\Main\EO_Site resetCulture()
	 * @method \Bitrix\Main\EO_Site unsetCulture()
	 * @method bool hasCulture()
	 * @method bool isCultureFilled()
	 * @method bool isCultureChanged()
	 * @method \Bitrix\Main\Context\Culture fillCulture()
	 * @method \Bitrix\Main\Localization\EO_Language getLanguage()
	 * @method \Bitrix\Main\Localization\EO_Language remindActualLanguage()
	 * @method \Bitrix\Main\Localization\EO_Language requireLanguage()
	 * @method \Bitrix\Main\EO_Site setLanguage(\Bitrix\Main\Localization\EO_Language $object)
	 * @method \Bitrix\Main\EO_Site resetLanguage()
	 * @method \Bitrix\Main\EO_Site unsetLanguage()
	 * @method bool hasLanguage()
	 * @method bool isLanguageFilled()
	 * @method bool isLanguageChanged()
	 * @method \Bitrix\Main\Localization\EO_Language fillLanguage()
	 * @method \string getDirLength()
	 * @method \string remindActualDirLength()
	 * @method \string requireDirLength()
	 * @method bool hasDirLength()
	 * @method bool isDirLengthFilled()
	 * @method \Bitrix\Main\EO_Site unsetDirLength()
	 * @method \string fillDirLength()
	 * @method \string getDocRootLength()
	 * @method \string remindActualDocRootLength()
	 * @method \string requireDocRootLength()
	 * @method bool hasDocRootLength()
	 * @method bool isDocRootLengthFilled()
	 * @method \Bitrix\Main\EO_Site unsetDocRootLength()
	 * @method \string fillDocRootLength()
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
	 * @method \Bitrix\Main\EO_Site set($fieldName, $value)
	 * @method \Bitrix\Main\EO_Site reset($fieldName)
	 * @method \Bitrix\Main\EO_Site unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_Site wakeUp($data)
	 */
	class EO_Site {
		/* @var \Bitrix\Main\SiteTable */
		static public $dataClass = '\Bitrix\Main\SiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_Site_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getLidList()
	 * @method \string[] getIdList()
	 * @method \string[] fillId()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \boolean[] getDefList()
	 * @method \boolean[] fillDef()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDirList()
	 * @method \string[] fillDir()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 * @method \string[] getDocRootList()
	 * @method \string[] fillDocRoot()
	 * @method \boolean[] getDomainLimitedList()
	 * @method \boolean[] fillDomainLimited()
	 * @method \string[] getServerNameList()
	 * @method \string[] fillServerName()
	 * @method \string[] getSiteNameList()
	 * @method \string[] fillSiteName()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \int[] getCultureIdList()
	 * @method \int[] fillCultureId()
	 * @method \Bitrix\Main\Context\Culture[] getCultureList()
	 * @method \Bitrix\Main\EO_Site_Collection getCultureCollection()
	 * @method \Bitrix\Main\Localization\EO_Culture_Collection fillCulture()
	 * @method \Bitrix\Main\Localization\EO_Language[] getLanguageList()
	 * @method \Bitrix\Main\EO_Site_Collection getLanguageCollection()
	 * @method \Bitrix\Main\Localization\EO_Language_Collection fillLanguage()
	 * @method \string[] getDirLengthList()
	 * @method \string[] fillDirLength()
	 * @method \string[] getDocRootLengthList()
	 * @method \string[] fillDocRootLength()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_Site $object)
	 * @method bool has(\Bitrix\Main\EO_Site $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_Site getByPrimary($primary)
	 * @method \Bitrix\Main\EO_Site[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_Site $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_Site_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_Site current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Site_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\SiteTable */
		static public $dataClass = '\Bitrix\Main\SiteTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Site_Result exec()
	 * @method \Bitrix\Main\EO_Site fetchObject()
	 * @method \Bitrix\Main\EO_Site_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Site_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_Site fetchObject()
	 * @method \Bitrix\Main\EO_Site_Collection fetchCollection()
	 */
	class EO_Site_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_Site createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_Site_Collection createCollection()
	 * @method \Bitrix\Main\EO_Site wakeUpObject($row)
	 * @method \Bitrix\Main\EO_Site_Collection wakeUpCollection($rows)
	 */
	class EO_Site_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\SiteDomainTable:main/lib/sitedomain.php */
namespace Bitrix\Main {
	/**
	 * EO_SiteDomain
	 * @see \Bitrix\Main\SiteDomainTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getLid()
	 * @method \Bitrix\Main\EO_SiteDomain setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string getDomain()
	 * @method \Bitrix\Main\EO_SiteDomain setDomain(\string|\Bitrix\Main\DB\SqlExpression $domain)
	 * @method bool hasDomain()
	 * @method bool isDomainFilled()
	 * @method bool isDomainChanged()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Main\EO_SiteDomain setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Main\EO_SiteDomain resetSite()
	 * @method \Bitrix\Main\EO_SiteDomain unsetSite()
	 * @method bool hasSite()
	 * @method bool isSiteFilled()
	 * @method bool isSiteChanged()
	 * @method \Bitrix\Main\EO_Site fillSite()
	 * @method \string getDomainLength()
	 * @method \string remindActualDomainLength()
	 * @method \string requireDomainLength()
	 * @method bool hasDomainLength()
	 * @method bool isDomainLengthFilled()
	 * @method \Bitrix\Main\EO_SiteDomain unsetDomainLength()
	 * @method \string fillDomainLength()
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
	 * @method \Bitrix\Main\EO_SiteDomain set($fieldName, $value)
	 * @method \Bitrix\Main\EO_SiteDomain reset($fieldName)
	 * @method \Bitrix\Main\EO_SiteDomain unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_SiteDomain wakeUp($data)
	 */
	class EO_SiteDomain {
		/* @var \Bitrix\Main\SiteDomainTable */
		static public $dataClass = '\Bitrix\Main\SiteDomainTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_SiteDomain_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getLidList()
	 * @method \string[] getDomainList()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Main\EO_SiteDomain_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 * @method \string[] getDomainLengthList()
	 * @method \string[] fillDomainLength()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_SiteDomain $object)
	 * @method bool has(\Bitrix\Main\EO_SiteDomain $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_SiteDomain getByPrimary($primary)
	 * @method \Bitrix\Main\EO_SiteDomain[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_SiteDomain $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_SiteDomain_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_SiteDomain current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SiteDomain_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\SiteDomainTable */
		static public $dataClass = '\Bitrix\Main\SiteDomainTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SiteDomain_Result exec()
	 * @method \Bitrix\Main\EO_SiteDomain fetchObject()
	 * @method \Bitrix\Main\EO_SiteDomain_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SiteDomain_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_SiteDomain fetchObject()
	 * @method \Bitrix\Main\EO_SiteDomain_Collection fetchCollection()
	 */
	class EO_SiteDomain_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_SiteDomain createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_SiteDomain_Collection createCollection()
	 * @method \Bitrix\Main\EO_SiteDomain wakeUpObject($row)
	 * @method \Bitrix\Main\EO_SiteDomain_Collection wakeUpCollection($rows)
	 */
	class EO_SiteDomain_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\SiteTemplateTable:main/lib/sitetemplate.php */
namespace Bitrix\Main {
	/**
	 * EO_SiteTemplate
	 * @see \Bitrix\Main\SiteTemplateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_SiteTemplate setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Main\EO_SiteTemplate setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Main\EO_SiteTemplate resetSiteId()
	 * @method \Bitrix\Main\EO_SiteTemplate unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \string getCondition()
	 * @method \Bitrix\Main\EO_SiteTemplate setCondition(\string|\Bitrix\Main\DB\SqlExpression $condition)
	 * @method bool hasCondition()
	 * @method bool isConditionFilled()
	 * @method bool isConditionChanged()
	 * @method \string remindActualCondition()
	 * @method \string requireCondition()
	 * @method \Bitrix\Main\EO_SiteTemplate resetCondition()
	 * @method \Bitrix\Main\EO_SiteTemplate unsetCondition()
	 * @method \string fillCondition()
	 * @method \int getSort()
	 * @method \Bitrix\Main\EO_SiteTemplate setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Main\EO_SiteTemplate resetSort()
	 * @method \Bitrix\Main\EO_SiteTemplate unsetSort()
	 * @method \int fillSort()
	 * @method \string getTemplate()
	 * @method \Bitrix\Main\EO_SiteTemplate setTemplate(\string|\Bitrix\Main\DB\SqlExpression $template)
	 * @method bool hasTemplate()
	 * @method bool isTemplateFilled()
	 * @method bool isTemplateChanged()
	 * @method \string remindActualTemplate()
	 * @method \string requireTemplate()
	 * @method \Bitrix\Main\EO_SiteTemplate resetTemplate()
	 * @method \Bitrix\Main\EO_SiteTemplate unsetTemplate()
	 * @method \string fillTemplate()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Main\EO_SiteTemplate setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Main\EO_SiteTemplate resetSite()
	 * @method \Bitrix\Main\EO_SiteTemplate unsetSite()
	 * @method bool hasSite()
	 * @method bool isSiteFilled()
	 * @method bool isSiteChanged()
	 * @method \Bitrix\Main\EO_Site fillSite()
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
	 * @method \Bitrix\Main\EO_SiteTemplate set($fieldName, $value)
	 * @method \Bitrix\Main\EO_SiteTemplate reset($fieldName)
	 * @method \Bitrix\Main\EO_SiteTemplate unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_SiteTemplate wakeUp($data)
	 */
	class EO_SiteTemplate {
		/* @var \Bitrix\Main\SiteTemplateTable */
		static public $dataClass = '\Bitrix\Main\SiteTemplateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_SiteTemplate_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \string[] getConditionList()
	 * @method \string[] fillCondition()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getTemplateList()
	 * @method \string[] fillTemplate()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Main\EO_SiteTemplate_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_SiteTemplate $object)
	 * @method bool has(\Bitrix\Main\EO_SiteTemplate $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_SiteTemplate getByPrimary($primary)
	 * @method \Bitrix\Main\EO_SiteTemplate[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_SiteTemplate $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_SiteTemplate_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_SiteTemplate current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SiteTemplate_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\SiteTemplateTable */
		static public $dataClass = '\Bitrix\Main\SiteTemplateTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SiteTemplate_Result exec()
	 * @method \Bitrix\Main\EO_SiteTemplate fetchObject()
	 * @method \Bitrix\Main\EO_SiteTemplate_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SiteTemplate_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_SiteTemplate fetchObject()
	 * @method \Bitrix\Main\EO_SiteTemplate_Collection fetchCollection()
	 */
	class EO_SiteTemplate_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_SiteTemplate createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_SiteTemplate_Collection createCollection()
	 * @method \Bitrix\Main\EO_SiteTemplate wakeUpObject($row)
	 * @method \Bitrix\Main\EO_SiteTemplate_Collection wakeUpCollection($rows)
	 */
	class EO_SiteTemplate_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Sms\TemplateTable:main/lib/sms/templatetable.php */
namespace Bitrix\Main\Sms {
	/**
	 * Template
	 * @see \Bitrix\Main\Sms\TemplateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Sms\Template setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEventName()
	 * @method \Bitrix\Main\Sms\Template setEventName(\string|\Bitrix\Main\DB\SqlExpression $eventName)
	 * @method bool hasEventName()
	 * @method bool isEventNameFilled()
	 * @method bool isEventNameChanged()
	 * @method \string remindActualEventName()
	 * @method \string requireEventName()
	 * @method \Bitrix\Main\Sms\Template resetEventName()
	 * @method \Bitrix\Main\Sms\Template unsetEventName()
	 * @method \string fillEventName()
	 * @method \boolean getActive()
	 * @method \Bitrix\Main\Sms\Template setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Main\Sms\Template resetActive()
	 * @method \Bitrix\Main\Sms\Template unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getSender()
	 * @method \Bitrix\Main\Sms\Template setSender(\string|\Bitrix\Main\DB\SqlExpression $sender)
	 * @method bool hasSender()
	 * @method bool isSenderFilled()
	 * @method bool isSenderChanged()
	 * @method \string remindActualSender()
	 * @method \string requireSender()
	 * @method \Bitrix\Main\Sms\Template resetSender()
	 * @method \Bitrix\Main\Sms\Template unsetSender()
	 * @method \string fillSender()
	 * @method \string getReceiver()
	 * @method \Bitrix\Main\Sms\Template setReceiver(\string|\Bitrix\Main\DB\SqlExpression $receiver)
	 * @method bool hasReceiver()
	 * @method bool isReceiverFilled()
	 * @method bool isReceiverChanged()
	 * @method \string remindActualReceiver()
	 * @method \string requireReceiver()
	 * @method \Bitrix\Main\Sms\Template resetReceiver()
	 * @method \Bitrix\Main\Sms\Template unsetReceiver()
	 * @method \string fillReceiver()
	 * @method \string getMessage()
	 * @method \Bitrix\Main\Sms\Template setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Main\Sms\Template resetMessage()
	 * @method \Bitrix\Main\Sms\Template unsetMessage()
	 * @method \string fillMessage()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Main\Sms\Template setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\Main\Sms\Template resetLanguageId()
	 * @method \Bitrix\Main\Sms\Template unsetLanguageId()
	 * @method \string fillLanguageId()
	 * @method \Bitrix\Main\EO_Site_Collection getSites()
	 * @method \Bitrix\Main\EO_Site_Collection requireSites()
	 * @method \Bitrix\Main\EO_Site_Collection fillSites()
	 * @method bool hasSites()
	 * @method bool isSitesFilled()
	 * @method bool isSitesChanged()
	 * @method void addToSites(\Bitrix\Main\EO_Site $site)
	 * @method void removeFromSites(\Bitrix\Main\EO_Site $site)
	 * @method void removeAllSites()
	 * @method \Bitrix\Main\Sms\Template resetSites()
	 * @method \Bitrix\Main\Sms\Template unsetSites()
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
	 * @method \Bitrix\Main\Sms\Template set($fieldName, $value)
	 * @method \Bitrix\Main\Sms\Template reset($fieldName)
	 * @method \Bitrix\Main\Sms\Template unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Sms\Template wakeUp($data)
	 */
	class EO_Template {
		/* @var \Bitrix\Main\Sms\TemplateTable */
		static public $dataClass = '\Bitrix\Main\Sms\TemplateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Sms {
	/**
	 * EO_Template_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEventNameList()
	 * @method \string[] fillEventName()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getSenderList()
	 * @method \string[] fillSender()
	 * @method \string[] getReceiverList()
	 * @method \string[] fillReceiver()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 * @method \Bitrix\Main\EO_Site_Collection[] getSitesList()
	 * @method \Bitrix\Main\EO_Site_Collection getSitesCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSites()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Sms\Template $object)
	 * @method bool has(\Bitrix\Main\Sms\Template $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Sms\Template getByPrimary($primary)
	 * @method \Bitrix\Main\Sms\Template[] getAll()
	 * @method bool remove(\Bitrix\Main\Sms\Template $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Sms\EO_Template_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Sms\Template current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Template_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Sms\TemplateTable */
		static public $dataClass = '\Bitrix\Main\Sms\TemplateTable';
	}
}
namespace Bitrix\Main\Sms {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Template_Result exec()
	 * @method \Bitrix\Main\Sms\Template fetchObject()
	 * @method \Bitrix\Main\Sms\EO_Template_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Template_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Sms\Template fetchObject()
	 * @method \Bitrix\Main\Sms\EO_Template_Collection fetchCollection()
	 */
	class EO_Template_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Sms\Template createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Sms\EO_Template_Collection createCollection()
	 * @method \Bitrix\Main\Sms\Template wakeUpObject($row)
	 * @method \Bitrix\Main\Sms\EO_Template_Collection wakeUpCollection($rows)
	 */
	class EO_Template_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\TaskTable:main/lib/task.php */
namespace Bitrix\Main {
	/**
	 * EO_Task
	 * @see \Bitrix\Main\TaskTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_Task setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Main\EO_Task setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\EO_Task resetName()
	 * @method \Bitrix\Main\EO_Task unsetName()
	 * @method \string fillName()
	 * @method \string getLetter()
	 * @method \Bitrix\Main\EO_Task setLetter(\string|\Bitrix\Main\DB\SqlExpression $letter)
	 * @method bool hasLetter()
	 * @method bool isLetterFilled()
	 * @method bool isLetterChanged()
	 * @method \string remindActualLetter()
	 * @method \string requireLetter()
	 * @method \Bitrix\Main\EO_Task resetLetter()
	 * @method \Bitrix\Main\EO_Task unsetLetter()
	 * @method \string fillLetter()
	 * @method \string getModuleId()
	 * @method \Bitrix\Main\EO_Task setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Main\EO_Task resetModuleId()
	 * @method \Bitrix\Main\EO_Task unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getSys()
	 * @method \Bitrix\Main\EO_Task setSys(\string|\Bitrix\Main\DB\SqlExpression $sys)
	 * @method bool hasSys()
	 * @method bool isSysFilled()
	 * @method bool isSysChanged()
	 * @method \string remindActualSys()
	 * @method \string requireSys()
	 * @method \Bitrix\Main\EO_Task resetSys()
	 * @method \Bitrix\Main\EO_Task unsetSys()
	 * @method \string fillSys()
	 * @method \string getDescription()
	 * @method \Bitrix\Main\EO_Task setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Main\EO_Task resetDescription()
	 * @method \Bitrix\Main\EO_Task unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getBinding()
	 * @method \Bitrix\Main\EO_Task setBinding(\string|\Bitrix\Main\DB\SqlExpression $binding)
	 * @method bool hasBinding()
	 * @method bool isBindingFilled()
	 * @method bool isBindingChanged()
	 * @method \string remindActualBinding()
	 * @method \string requireBinding()
	 * @method \Bitrix\Main\EO_Task resetBinding()
	 * @method \Bitrix\Main\EO_Task unsetBinding()
	 * @method \string fillBinding()
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
	 * @method \Bitrix\Main\EO_Task set($fieldName, $value)
	 * @method \Bitrix\Main\EO_Task reset($fieldName)
	 * @method \Bitrix\Main\EO_Task unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_Task wakeUp($data)
	 */
	class EO_Task {
		/* @var \Bitrix\Main\TaskTable */
		static public $dataClass = '\Bitrix\Main\TaskTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_Task_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getLetterList()
	 * @method \string[] fillLetter()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getSysList()
	 * @method \string[] fillSys()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getBindingList()
	 * @method \string[] fillBinding()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_Task $object)
	 * @method bool has(\Bitrix\Main\EO_Task $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_Task getByPrimary($primary)
	 * @method \Bitrix\Main\EO_Task[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_Task $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_Task_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_Task current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Task_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\TaskTable */
		static public $dataClass = '\Bitrix\Main\TaskTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Task_Result exec()
	 * @method \Bitrix\Main\EO_Task fetchObject()
	 * @method \Bitrix\Main\EO_Task_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Task_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_Task fetchObject()
	 * @method \Bitrix\Main\EO_Task_Collection fetchCollection()
	 */
	class EO_Task_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_Task createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_Task_Collection createCollection()
	 * @method \Bitrix\Main\EO_Task wakeUpObject($row)
	 * @method \Bitrix\Main\EO_Task_Collection wakeUpCollection($rows)
	 */
	class EO_Task_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\TaskOperationTable:main/lib/taskoperation.php */
namespace Bitrix\Main {
	/**
	 * EO_TaskOperation
	 * @see \Bitrix\Main\TaskOperationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getTaskId()
	 * @method \Bitrix\Main\EO_TaskOperation setTaskId(\int|\Bitrix\Main\DB\SqlExpression $taskId)
	 * @method bool hasTaskId()
	 * @method bool isTaskIdFilled()
	 * @method bool isTaskIdChanged()
	 * @method \int getOperationId()
	 * @method \Bitrix\Main\EO_TaskOperation setOperationId(\int|\Bitrix\Main\DB\SqlExpression $operationId)
	 * @method bool hasOperationId()
	 * @method bool isOperationIdFilled()
	 * @method bool isOperationIdChanged()
	 * @method \Bitrix\Main\EO_Operation getOperation()
	 * @method \Bitrix\Main\EO_Operation remindActualOperation()
	 * @method \Bitrix\Main\EO_Operation requireOperation()
	 * @method \Bitrix\Main\EO_TaskOperation setOperation(\Bitrix\Main\EO_Operation $object)
	 * @method \Bitrix\Main\EO_TaskOperation resetOperation()
	 * @method \Bitrix\Main\EO_TaskOperation unsetOperation()
	 * @method bool hasOperation()
	 * @method bool isOperationFilled()
	 * @method bool isOperationChanged()
	 * @method \Bitrix\Main\EO_Operation fillOperation()
	 * @method \Bitrix\Main\EO_Task getTask()
	 * @method \Bitrix\Main\EO_Task remindActualTask()
	 * @method \Bitrix\Main\EO_Task requireTask()
	 * @method \Bitrix\Main\EO_TaskOperation setTask(\Bitrix\Main\EO_Task $object)
	 * @method \Bitrix\Main\EO_TaskOperation resetTask()
	 * @method \Bitrix\Main\EO_TaskOperation unsetTask()
	 * @method bool hasTask()
	 * @method bool isTaskFilled()
	 * @method bool isTaskChanged()
	 * @method \Bitrix\Main\EO_Task fillTask()
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
	 * @method \Bitrix\Main\EO_TaskOperation set($fieldName, $value)
	 * @method \Bitrix\Main\EO_TaskOperation reset($fieldName)
	 * @method \Bitrix\Main\EO_TaskOperation unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_TaskOperation wakeUp($data)
	 */
	class EO_TaskOperation {
		/* @var \Bitrix\Main\TaskOperationTable */
		static public $dataClass = '\Bitrix\Main\TaskOperationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_TaskOperation_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getTaskIdList()
	 * @method \int[] getOperationIdList()
	 * @method \Bitrix\Main\EO_Operation[] getOperationList()
	 * @method \Bitrix\Main\EO_TaskOperation_Collection getOperationCollection()
	 * @method \Bitrix\Main\EO_Operation_Collection fillOperation()
	 * @method \Bitrix\Main\EO_Task[] getTaskList()
	 * @method \Bitrix\Main\EO_TaskOperation_Collection getTaskCollection()
	 * @method \Bitrix\Main\EO_Task_Collection fillTask()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_TaskOperation $object)
	 * @method bool has(\Bitrix\Main\EO_TaskOperation $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_TaskOperation getByPrimary($primary)
	 * @method \Bitrix\Main\EO_TaskOperation[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_TaskOperation $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_TaskOperation_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_TaskOperation current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_TaskOperation_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\TaskOperationTable */
		static public $dataClass = '\Bitrix\Main\TaskOperationTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_TaskOperation_Result exec()
	 * @method \Bitrix\Main\EO_TaskOperation fetchObject()
	 * @method \Bitrix\Main\EO_TaskOperation_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_TaskOperation_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_TaskOperation fetchObject()
	 * @method \Bitrix\Main\EO_TaskOperation_Collection fetchCollection()
	 */
	class EO_TaskOperation_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_TaskOperation createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_TaskOperation_Collection createCollection()
	 * @method \Bitrix\Main\EO_TaskOperation wakeUpObject($row)
	 * @method \Bitrix\Main\EO_TaskOperation_Collection wakeUpCollection($rows)
	 */
	class EO_TaskOperation_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Test\Typography\AuthorTable:main/lib/test/typography/authortable.php */
namespace Bitrix\Main\Test\Typography {
	/**
	 * EO_Author
	 * @see \Bitrix\Main\Test\Typography\AuthorTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Test\Typography\EO_Author setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Main\Test\Typography\EO_Author setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\Test\Typography\EO_Author resetName()
	 * @method \Bitrix\Main\Test\Typography\EO_Author unsetName()
	 * @method \string fillName()
	 * @method \string getLastName()
	 * @method \Bitrix\Main\Test\Typography\EO_Author setLastName(\string|\Bitrix\Main\DB\SqlExpression $lastName)
	 * @method bool hasLastName()
	 * @method bool isLastNameFilled()
	 * @method bool isLastNameChanged()
	 * @method \string remindActualLastName()
	 * @method \string requireLastName()
	 * @method \Bitrix\Main\Test\Typography\EO_Author resetLastName()
	 * @method \Bitrix\Main\Test\Typography\EO_Author unsetLastName()
	 * @method \string fillLastName()
	 * @method \Bitrix\Main\Test\Typography\Books getBooks()
	 * @method \Bitrix\Main\Test\Typography\Books requireBooks()
	 * @method \Bitrix\Main\Test\Typography\Books fillBooks()
	 * @method bool hasBooks()
	 * @method bool isBooksFilled()
	 * @method bool isBooksChanged()
	 * @method void addToBooks(\Bitrix\Main\Test\Typography\Book $book)
	 * @method void removeFromBooks(\Bitrix\Main\Test\Typography\Book $book)
	 * @method void removeAllBooks()
	 * @method \Bitrix\Main\Test\Typography\EO_Author resetBooks()
	 * @method \Bitrix\Main\Test\Typography\EO_Author unsetBooks()
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
	 * @method \Bitrix\Main\Test\Typography\EO_Author set($fieldName, $value)
	 * @method \Bitrix\Main\Test\Typography\EO_Author reset($fieldName)
	 * @method \Bitrix\Main\Test\Typography\EO_Author unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Test\Typography\EO_Author wakeUp($data)
	 */
	class EO_Author {
		/* @var \Bitrix\Main\Test\Typography\AuthorTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\AuthorTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * EO_Author_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getLastNameList()
	 * @method \string[] fillLastName()
	 * @method \Bitrix\Main\Test\Typography\Books[] getBooksList()
	 * @method \Bitrix\Main\Test\Typography\Books getBooksCollection()
	 * @method \Bitrix\Main\Test\Typography\Books fillBooks()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Test\Typography\EO_Author $object)
	 * @method bool has(\Bitrix\Main\Test\Typography\EO_Author $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\EO_Author getByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\EO_Author[] getAll()
	 * @method bool remove(\Bitrix\Main\Test\Typography\EO_Author $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Test\Typography\EO_Author_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Test\Typography\EO_Author current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Author_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Test\Typography\AuthorTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\AuthorTable';
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Author_Result exec()
	 * @method \Bitrix\Main\Test\Typography\EO_Author fetchObject()
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Author_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Test\Typography\EO_Author fetchObject()
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection fetchCollection()
	 */
	class EO_Author_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Test\Typography\EO_Author createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection createCollection()
	 * @method \Bitrix\Main\Test\Typography\EO_Author wakeUpObject($row)
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection wakeUpCollection($rows)
	 */
	class EO_Author_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Test\Typography\BookTable:main/lib/test/typography/booktable.php */
namespace Bitrix\Main\Test\Typography {
	/**
	 * Book
	 * @see \Bitrix\Main\Test\Typography\BookTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Test\Typography\Book setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getTitle()
	 * @method \Bitrix\Main\Test\Typography\Book setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Main\Test\Typography\Book resetTitle()
	 * @method \Bitrix\Main\Test\Typography\Book unsetTitle()
	 * @method \string fillTitle()
	 * @method \int getPublisherId()
	 * @method \Bitrix\Main\Test\Typography\Book setPublisherId(\int|\Bitrix\Main\DB\SqlExpression $publisherId)
	 * @method bool hasPublisherId()
	 * @method bool isPublisherIdFilled()
	 * @method bool isPublisherIdChanged()
	 * @method \int remindActualPublisherId()
	 * @method \int requirePublisherId()
	 * @method \Bitrix\Main\Test\Typography\Book resetPublisherId()
	 * @method \Bitrix\Main\Test\Typography\Book unsetPublisherId()
	 * @method \int fillPublisherId()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher getPublisher()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher remindActualPublisher()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher requirePublisher()
	 * @method \Bitrix\Main\Test\Typography\Book setPublisher(\Bitrix\Main\Test\Typography\EO_Publisher $object)
	 * @method \Bitrix\Main\Test\Typography\Book resetPublisher()
	 * @method \Bitrix\Main\Test\Typography\Book unsetPublisher()
	 * @method bool hasPublisher()
	 * @method bool isPublisherFilled()
	 * @method bool isPublisherChanged()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher fillPublisher()
	 * @method \string getIsbn()
	 * @method \Bitrix\Main\Test\Typography\Book setIsbn(\string|\Bitrix\Main\DB\SqlExpression $isbn)
	 * @method bool hasIsbn()
	 * @method bool isIsbnFilled()
	 * @method bool isIsbnChanged()
	 * @method \string remindActualIsbn()
	 * @method \string requireIsbn()
	 * @method \Bitrix\Main\Test\Typography\Book resetIsbn()
	 * @method \Bitrix\Main\Test\Typography\Book unsetIsbn()
	 * @method \string fillIsbn()
	 * @method \boolean getIsArchived()
	 * @method \Bitrix\Main\Test\Typography\Book setIsArchived(\boolean|\Bitrix\Main\DB\SqlExpression $isArchived)
	 * @method bool hasIsArchived()
	 * @method bool isIsArchivedFilled()
	 * @method bool isIsArchivedChanged()
	 * @method \boolean remindActualIsArchived()
	 * @method \boolean requireIsArchived()
	 * @method \Bitrix\Main\Test\Typography\Book resetIsArchived()
	 * @method \Bitrix\Main\Test\Typography\Book unsetIsArchived()
	 * @method \boolean fillIsArchived()
	 * @method array getQuotes()
	 * @method \Bitrix\Main\Test\Typography\Book setQuotes(array|\Bitrix\Main\DB\SqlExpression $quotes)
	 * @method bool hasQuotes()
	 * @method bool isQuotesFilled()
	 * @method bool isQuotesChanged()
	 * @method array remindActualQuotes()
	 * @method array requireQuotes()
	 * @method \Bitrix\Main\Test\Typography\Book resetQuotes()
	 * @method \Bitrix\Main\Test\Typography\Book unsetQuotes()
	 * @method array fillQuotes()
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection getAuthors()
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection requireAuthors()
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection fillAuthors()
	 * @method bool hasAuthors()
	 * @method bool isAuthorsFilled()
	 * @method bool isAuthorsChanged()
	 * @method void addToAuthors(\Bitrix\Main\Test\Typography\EO_Author $author)
	 * @method void removeFromAuthors(\Bitrix\Main\Test\Typography\EO_Author $author)
	 * @method void removeAllAuthors()
	 * @method \Bitrix\Main\Test\Typography\Book resetAuthors()
	 * @method \Bitrix\Main\Test\Typography\Book unsetAuthors()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection getStoreItems()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection requireStoreItems()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection fillStoreItems()
	 * @method bool hasStoreItems()
	 * @method bool isStoreItemsFilled()
	 * @method bool isStoreItemsChanged()
	 * @method void addToStoreItems(\Bitrix\Main\Test\Typography\EO_StoreBook $storeBook)
	 * @method void removeFromStoreItems(\Bitrix\Main\Test\Typography\EO_StoreBook $storeBook)
	 * @method void removeAllStoreItems()
	 * @method \Bitrix\Main\Test\Typography\Book resetStoreItems()
	 * @method \Bitrix\Main\Test\Typography\Book unsetStoreItems()
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
	 * @method \Bitrix\Main\Test\Typography\Book set($fieldName, $value)
	 * @method \Bitrix\Main\Test\Typography\Book reset($fieldName)
	 * @method \Bitrix\Main\Test\Typography\Book unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Test\Typography\Book wakeUp($data)
	 */
	class EO_Book {
		/* @var \Bitrix\Main\Test\Typography\BookTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\BookTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * Books
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \int[] getPublisherIdList()
	 * @method \int[] fillPublisherId()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher[] getPublisherList()
	 * @method \Bitrix\Main\Test\Typography\Books getPublisherCollection()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher_Collection fillPublisher()
	 * @method \string[] getIsbnList()
	 * @method \string[] fillIsbn()
	 * @method \boolean[] getIsArchivedList()
	 * @method \boolean[] fillIsArchived()
	 * @method array[] getQuotesList()
	 * @method array[] fillQuotes()
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection[] getAuthorsList()
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection getAuthorsCollection()
	 * @method \Bitrix\Main\Test\Typography\EO_Author_Collection fillAuthors()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection[] getStoreItemsList()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection getStoreItemsCollection()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection fillStoreItems()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Test\Typography\Book $object)
	 * @method bool has(\Bitrix\Main\Test\Typography\Book $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\Book getByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\Book[] getAll()
	 * @method bool remove(\Bitrix\Main\Test\Typography\Book $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Test\Typography\Books wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Test\Typography\Book current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Book_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Test\Typography\BookTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\BookTable';
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Book_Result exec()
	 * @method \Bitrix\Main\Test\Typography\Book fetchObject()
	 * @method \Bitrix\Main\Test\Typography\Books fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Book_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Test\Typography\Book fetchObject()
	 * @method \Bitrix\Main\Test\Typography\Books fetchCollection()
	 */
	class EO_Book_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Test\Typography\Book createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Test\Typography\Books createCollection()
	 * @method \Bitrix\Main\Test\Typography\Book wakeUpObject($row)
	 * @method \Bitrix\Main\Test\Typography\Books wakeUpCollection($rows)
	 */
	class EO_Book_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Test\Typography\PublisherTable:main/lib/test/typography/publishertable.php */
namespace Bitrix\Main\Test\Typography {
	/**
	 * EO_Publisher
	 * @see \Bitrix\Main\Test\Typography\PublisherTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getTitle()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher resetTitle()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher unsetTitle()
	 * @method \string fillTitle()
	 * @method \int getBooksCount()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher setBooksCount(\int|\Bitrix\Main\DB\SqlExpression $booksCount)
	 * @method bool hasBooksCount()
	 * @method bool isBooksCountFilled()
	 * @method bool isBooksCountChanged()
	 * @method \int remindActualBooksCount()
	 * @method \int requireBooksCount()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher resetBooksCount()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher unsetBooksCount()
	 * @method \int fillBooksCount()
	 * @method \Bitrix\Main\Test\Typography\Books getBooks()
	 * @method \Bitrix\Main\Test\Typography\Books requireBooks()
	 * @method \Bitrix\Main\Test\Typography\Books fillBooks()
	 * @method bool hasBooks()
	 * @method bool isBooksFilled()
	 * @method bool isBooksChanged()
	 * @method void addToBooks(\Bitrix\Main\Test\Typography\Book $book)
	 * @method void removeFromBooks(\Bitrix\Main\Test\Typography\Book $book)
	 * @method void removeAllBooks()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher resetBooks()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher unsetBooks()
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
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher set($fieldName, $value)
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher reset($fieldName)
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Test\Typography\EO_Publisher wakeUp($data)
	 */
	class EO_Publisher {
		/* @var \Bitrix\Main\Test\Typography\PublisherTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\PublisherTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * EO_Publisher_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \int[] getBooksCountList()
	 * @method \int[] fillBooksCount()
	 * @method \Bitrix\Main\Test\Typography\Books[] getBooksList()
	 * @method \Bitrix\Main\Test\Typography\Books getBooksCollection()
	 * @method \Bitrix\Main\Test\Typography\Books fillBooks()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Test\Typography\EO_Publisher $object)
	 * @method bool has(\Bitrix\Main\Test\Typography\EO_Publisher $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher getByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher[] getAll()
	 * @method bool remove(\Bitrix\Main\Test\Typography\EO_Publisher $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Test\Typography\EO_Publisher_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Publisher_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Test\Typography\PublisherTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\PublisherTable';
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Publisher_Result exec()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher fetchObject()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Publisher_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher fetchObject()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher_Collection fetchCollection()
	 */
	class EO_Publisher_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher_Collection createCollection()
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher wakeUpObject($row)
	 * @method \Bitrix\Main\Test\Typography\EO_Publisher_Collection wakeUpCollection($rows)
	 */
	class EO_Publisher_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Test\Typography\StoreBookTable:main/lib/test/typography/storebooktable.php */
namespace Bitrix\Main\Test\Typography {
	/**
	 * EO_StoreBook
	 * @see \Bitrix\Main\Test\Typography\StoreBookTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getStoreId()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook setStoreId(\int|\Bitrix\Main\DB\SqlExpression $storeId)
	 * @method bool hasStoreId()
	 * @method bool isStoreIdFilled()
	 * @method bool isStoreIdChanged()
	 * @method \Bitrix\Main\Test\Typography\EO_Store getStore()
	 * @method \Bitrix\Main\Test\Typography\EO_Store remindActualStore()
	 * @method \Bitrix\Main\Test\Typography\EO_Store requireStore()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook setStore(\Bitrix\Main\Test\Typography\EO_Store $object)
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook resetStore()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook unsetStore()
	 * @method bool hasStore()
	 * @method bool isStoreFilled()
	 * @method bool isStoreChanged()
	 * @method \Bitrix\Main\Test\Typography\EO_Store fillStore()
	 * @method \int getBookId()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook setBookId(\int|\Bitrix\Main\DB\SqlExpression $bookId)
	 * @method bool hasBookId()
	 * @method bool isBookIdFilled()
	 * @method bool isBookIdChanged()
	 * @method \Bitrix\Main\Test\Typography\Book getBook()
	 * @method \Bitrix\Main\Test\Typography\Book remindActualBook()
	 * @method \Bitrix\Main\Test\Typography\Book requireBook()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook setBook(\Bitrix\Main\Test\Typography\Book $object)
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook resetBook()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook unsetBook()
	 * @method bool hasBook()
	 * @method bool isBookFilled()
	 * @method bool isBookChanged()
	 * @method \Bitrix\Main\Test\Typography\Book fillBook()
	 * @method \int getQuantity()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook setQuantity(\int|\Bitrix\Main\DB\SqlExpression $quantity)
	 * @method bool hasQuantity()
	 * @method bool isQuantityFilled()
	 * @method bool isQuantityChanged()
	 * @method \int remindActualQuantity()
	 * @method \int requireQuantity()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook resetQuantity()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook unsetQuantity()
	 * @method \int fillQuantity()
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
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook set($fieldName, $value)
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook reset($fieldName)
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Test\Typography\EO_StoreBook wakeUp($data)
	 */
	class EO_StoreBook {
		/* @var \Bitrix\Main\Test\Typography\StoreBookTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\StoreBookTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * EO_StoreBook_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getStoreIdList()
	 * @method \Bitrix\Main\Test\Typography\EO_Store[] getStoreList()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection getStoreCollection()
	 * @method \Bitrix\Main\Test\Typography\EO_Store_Collection fillStore()
	 * @method \int[] getBookIdList()
	 * @method \Bitrix\Main\Test\Typography\Book[] getBookList()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection getBookCollection()
	 * @method \Bitrix\Main\Test\Typography\Books fillBook()
	 * @method \int[] getQuantityList()
	 * @method \int[] fillQuantity()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Test\Typography\EO_StoreBook $object)
	 * @method bool has(\Bitrix\Main\Test\Typography\EO_StoreBook $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook getByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook[] getAll()
	 * @method bool remove(\Bitrix\Main\Test\Typography\EO_StoreBook $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Test\Typography\EO_StoreBook_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_StoreBook_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Test\Typography\StoreBookTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\StoreBookTable';
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_StoreBook_Result exec()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook fetchObject()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_StoreBook_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook fetchObject()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection fetchCollection()
	 */
	class EO_StoreBook_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection createCollection()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook wakeUpObject($row)
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection wakeUpCollection($rows)
	 */
	class EO_StoreBook_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Test\Typography\StoreTable:main/lib/test/typography/storetable.php */
namespace Bitrix\Main\Test\Typography {
	/**
	 * EO_Store
	 * @see \Bitrix\Main\Test\Typography\StoreTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Test\Typography\EO_Store setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getAddress()
	 * @method \Bitrix\Main\Test\Typography\EO_Store setAddress(\string|\Bitrix\Main\DB\SqlExpression $address)
	 * @method bool hasAddress()
	 * @method bool isAddressFilled()
	 * @method bool isAddressChanged()
	 * @method \string remindActualAddress()
	 * @method \string requireAddress()
	 * @method \Bitrix\Main\Test\Typography\EO_Store resetAddress()
	 * @method \Bitrix\Main\Test\Typography\EO_Store unsetAddress()
	 * @method \string fillAddress()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection getBookItems()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection requireBookItems()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection fillBookItems()
	 * @method bool hasBookItems()
	 * @method bool isBookItemsFilled()
	 * @method bool isBookItemsChanged()
	 * @method void addToBookItems(\Bitrix\Main\Test\Typography\EO_StoreBook $storeBook)
	 * @method void removeFromBookItems(\Bitrix\Main\Test\Typography\EO_StoreBook $storeBook)
	 * @method void removeAllBookItems()
	 * @method \Bitrix\Main\Test\Typography\EO_Store resetBookItems()
	 * @method \Bitrix\Main\Test\Typography\EO_Store unsetBookItems()
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
	 * @method \Bitrix\Main\Test\Typography\EO_Store set($fieldName, $value)
	 * @method \Bitrix\Main\Test\Typography\EO_Store reset($fieldName)
	 * @method \Bitrix\Main\Test\Typography\EO_Store unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Test\Typography\EO_Store wakeUp($data)
	 */
	class EO_Store {
		/* @var \Bitrix\Main\Test\Typography\StoreTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\StoreTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * EO_Store_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getAddressList()
	 * @method \string[] fillAddress()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection[] getBookItemsList()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection getBookItemsCollection()
	 * @method \Bitrix\Main\Test\Typography\EO_StoreBook_Collection fillBookItems()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Test\Typography\EO_Store $object)
	 * @method bool has(\Bitrix\Main\Test\Typography\EO_Store $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\EO_Store getByPrimary($primary)
	 * @method \Bitrix\Main\Test\Typography\EO_Store[] getAll()
	 * @method bool remove(\Bitrix\Main\Test\Typography\EO_Store $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Test\Typography\EO_Store_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Test\Typography\EO_Store current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Store_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Test\Typography\StoreTable */
		static public $dataClass = '\Bitrix\Main\Test\Typography\StoreTable';
	}
}
namespace Bitrix\Main\Test\Typography {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Store_Result exec()
	 * @method \Bitrix\Main\Test\Typography\EO_Store fetchObject()
	 * @method \Bitrix\Main\Test\Typography\EO_Store_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Store_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Test\Typography\EO_Store fetchObject()
	 * @method \Bitrix\Main\Test\Typography\EO_Store_Collection fetchCollection()
	 */
	class EO_Store_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Test\Typography\EO_Store createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Test\Typography\EO_Store_Collection createCollection()
	 * @method \Bitrix\Main\Test\Typography\EO_Store wakeUpObject($row)
	 * @method \Bitrix\Main\Test\Typography\EO_Store_Collection wakeUpCollection($rows)
	 */
	class EO_Store_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UI\Viewer\FilePreviewTable:main/lib/ui/viewer/filepreviewtable.php */
namespace Bitrix\Main\UI\Viewer {
	/**
	 * EO_FilePreview
	 * @see \Bitrix\Main\UI\Viewer\FilePreviewTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getFileId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview resetFileId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview unsetFileId()
	 * @method \int fillFileId()
	 * @method \int getPreviewId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview setPreviewId(\int|\Bitrix\Main\DB\SqlExpression $previewId)
	 * @method bool hasPreviewId()
	 * @method bool isPreviewIdFilled()
	 * @method bool isPreviewIdChanged()
	 * @method \int remindActualPreviewId()
	 * @method \int requirePreviewId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview resetPreviewId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview unsetPreviewId()
	 * @method \int fillPreviewId()
	 * @method \int getPreviewImageId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview setPreviewImageId(\int|\Bitrix\Main\DB\SqlExpression $previewImageId)
	 * @method bool hasPreviewImageId()
	 * @method bool isPreviewImageIdFilled()
	 * @method bool isPreviewImageIdChanged()
	 * @method \int remindActualPreviewImageId()
	 * @method \int requirePreviewImageId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview resetPreviewImageId()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview unsetPreviewImageId()
	 * @method \int fillPreviewImageId()
	 * @method \Bitrix\Main\Type\DateTime getCreatedAt()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview setCreatedAt(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createdAt)
	 * @method bool hasCreatedAt()
	 * @method bool isCreatedAtFilled()
	 * @method bool isCreatedAtChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreatedAt()
	 * @method \Bitrix\Main\Type\DateTime requireCreatedAt()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview resetCreatedAt()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview unsetCreatedAt()
	 * @method \Bitrix\Main\Type\DateTime fillCreatedAt()
	 * @method \Bitrix\Main\Type\DateTime getTouchedAt()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview setTouchedAt(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $touchedAt)
	 * @method bool hasTouchedAt()
	 * @method bool isTouchedAtFilled()
	 * @method bool isTouchedAtChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTouchedAt()
	 * @method \Bitrix\Main\Type\DateTime requireTouchedAt()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview resetTouchedAt()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview unsetTouchedAt()
	 * @method \Bitrix\Main\Type\DateTime fillTouchedAt()
	 * @method \Bitrix\Main\EO_File getFile()
	 * @method \Bitrix\Main\EO_File remindActualFile()
	 * @method \Bitrix\Main\EO_File requireFile()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview setFile(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview resetFile()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview unsetFile()
	 * @method bool hasFile()
	 * @method bool isFileFilled()
	 * @method bool isFileChanged()
	 * @method \Bitrix\Main\EO_File fillFile()
	 * @method \Bitrix\Main\EO_File getPreview()
	 * @method \Bitrix\Main\EO_File remindActualPreview()
	 * @method \Bitrix\Main\EO_File requirePreview()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview setPreview(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview resetPreview()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview unsetPreview()
	 * @method bool hasPreview()
	 * @method bool isPreviewFilled()
	 * @method bool isPreviewChanged()
	 * @method \Bitrix\Main\EO_File fillPreview()
	 * @method \Bitrix\Main\EO_File getPreviewImage()
	 * @method \Bitrix\Main\EO_File remindActualPreviewImage()
	 * @method \Bitrix\Main\EO_File requirePreviewImage()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview setPreviewImage(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview resetPreviewImage()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview unsetPreviewImage()
	 * @method bool hasPreviewImage()
	 * @method bool isPreviewImageFilled()
	 * @method bool isPreviewImageChanged()
	 * @method \Bitrix\Main\EO_File fillPreviewImage()
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
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview set($fieldName, $value)
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview reset($fieldName)
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\UI\Viewer\EO_FilePreview wakeUp($data)
	 */
	class EO_FilePreview {
		/* @var \Bitrix\Main\UI\Viewer\FilePreviewTable */
		static public $dataClass = '\Bitrix\Main\UI\Viewer\FilePreviewTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\UI\Viewer {
	/**
	 * EO_FilePreview_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 * @method \int[] getPreviewIdList()
	 * @method \int[] fillPreviewId()
	 * @method \int[] getPreviewImageIdList()
	 * @method \int[] fillPreviewImageId()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedAtList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreatedAt()
	 * @method \Bitrix\Main\Type\DateTime[] getTouchedAtList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTouchedAt()
	 * @method \Bitrix\Main\EO_File[] getFileList()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection getFileCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillFile()
	 * @method \Bitrix\Main\EO_File[] getPreviewList()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection getPreviewCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillPreview()
	 * @method \Bitrix\Main\EO_File[] getPreviewImageList()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection getPreviewImageCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillPreviewImage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\UI\Viewer\EO_FilePreview $object)
	 * @method bool has(\Bitrix\Main\UI\Viewer\EO_FilePreview $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview getByPrimary($primary)
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview[] getAll()
	 * @method bool remove(\Bitrix\Main\UI\Viewer\EO_FilePreview $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FilePreview_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UI\Viewer\FilePreviewTable */
		static public $dataClass = '\Bitrix\Main\UI\Viewer\FilePreviewTable';
	}
}
namespace Bitrix\Main\UI\Viewer {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FilePreview_Result exec()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview fetchObject()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FilePreview_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview fetchObject()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection fetchCollection()
	 */
	class EO_FilePreview_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection createCollection()
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview wakeUpObject($row)
	 * @method \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection wakeUpCollection($rows)
	 */
	class EO_FilePreview_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\Update\VersionHistoryTable:main/lib/update/versionhistory.php */
namespace Bitrix\Main\Update {
	/**
	 * EO_VersionHistory
	 * @see \Bitrix\Main\Update\VersionHistoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\Update\EO_VersionHistory setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Main\Update\EO_VersionHistory setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Main\Update\EO_VersionHistory resetDateInsert()
	 * @method \Bitrix\Main\Update\EO_VersionHistory unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getVersions()
	 * @method \Bitrix\Main\Update\EO_VersionHistory setVersions(\string|\Bitrix\Main\DB\SqlExpression $versions)
	 * @method bool hasVersions()
	 * @method bool isVersionsFilled()
	 * @method bool isVersionsChanged()
	 * @method \string remindActualVersions()
	 * @method \string requireVersions()
	 * @method \Bitrix\Main\Update\EO_VersionHistory resetVersions()
	 * @method \Bitrix\Main\Update\EO_VersionHistory unsetVersions()
	 * @method \string fillVersions()
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
	 * @method \Bitrix\Main\Update\EO_VersionHistory set($fieldName, $value)
	 * @method \Bitrix\Main\Update\EO_VersionHistory reset($fieldName)
	 * @method \Bitrix\Main\Update\EO_VersionHistory unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\Update\EO_VersionHistory wakeUp($data)
	 */
	class EO_VersionHistory {
		/* @var \Bitrix\Main\Update\VersionHistoryTable */
		static public $dataClass = '\Bitrix\Main\Update\VersionHistoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\Update {
	/**
	 * EO_VersionHistory_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getVersionsList()
	 * @method \string[] fillVersions()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\Update\EO_VersionHistory $object)
	 * @method bool has(\Bitrix\Main\Update\EO_VersionHistory $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\Update\EO_VersionHistory getByPrimary($primary)
	 * @method \Bitrix\Main\Update\EO_VersionHistory[] getAll()
	 * @method bool remove(\Bitrix\Main\Update\EO_VersionHistory $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\Update\EO_VersionHistory_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\Update\EO_VersionHistory current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_VersionHistory_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\Update\VersionHistoryTable */
		static public $dataClass = '\Bitrix\Main\Update\VersionHistoryTable';
	}
}
namespace Bitrix\Main\Update {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_VersionHistory_Result exec()
	 * @method \Bitrix\Main\Update\EO_VersionHistory fetchObject()
	 * @method \Bitrix\Main\Update\EO_VersionHistory_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_VersionHistory_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\Update\EO_VersionHistory fetchObject()
	 * @method \Bitrix\Main\Update\EO_VersionHistory_Collection fetchCollection()
	 */
	class EO_VersionHistory_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\Update\EO_VersionHistory createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\Update\EO_VersionHistory_Collection createCollection()
	 * @method \Bitrix\Main\Update\EO_VersionHistory wakeUpObject($row)
	 * @method \Bitrix\Main\Update\EO_VersionHistory_Collection wakeUpCollection($rows)
	 */
	class EO_VersionHistory_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UrlPreview\RouteTable:main/lib/urlpreview/route.php */
namespace Bitrix\Main\UrlPreview {
	/**
	 * EO_Route
	 * @see \Bitrix\Main\UrlPreview\RouteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\UrlPreview\EO_Route setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getRoute()
	 * @method \Bitrix\Main\UrlPreview\EO_Route setRoute(\string|\Bitrix\Main\DB\SqlExpression $route)
	 * @method bool hasRoute()
	 * @method bool isRouteFilled()
	 * @method bool isRouteChanged()
	 * @method \string remindActualRoute()
	 * @method \string requireRoute()
	 * @method \Bitrix\Main\UrlPreview\EO_Route resetRoute()
	 * @method \Bitrix\Main\UrlPreview\EO_Route unsetRoute()
	 * @method \string fillRoute()
	 * @method \string getModule()
	 * @method \Bitrix\Main\UrlPreview\EO_Route setModule(\string|\Bitrix\Main\DB\SqlExpression $module)
	 * @method bool hasModule()
	 * @method bool isModuleFilled()
	 * @method bool isModuleChanged()
	 * @method \string remindActualModule()
	 * @method \string requireModule()
	 * @method \Bitrix\Main\UrlPreview\EO_Route resetModule()
	 * @method \Bitrix\Main\UrlPreview\EO_Route unsetModule()
	 * @method \string fillModule()
	 * @method \string getClass()
	 * @method \Bitrix\Main\UrlPreview\EO_Route setClass(\string|\Bitrix\Main\DB\SqlExpression $class)
	 * @method bool hasClass()
	 * @method bool isClassFilled()
	 * @method bool isClassChanged()
	 * @method \string remindActualClass()
	 * @method \string requireClass()
	 * @method \Bitrix\Main\UrlPreview\EO_Route resetClass()
	 * @method \Bitrix\Main\UrlPreview\EO_Route unsetClass()
	 * @method \string fillClass()
	 * @method \string getParameters()
	 * @method \Bitrix\Main\UrlPreview\EO_Route setParameters(\string|\Bitrix\Main\DB\SqlExpression $parameters)
	 * @method bool hasParameters()
	 * @method bool isParametersFilled()
	 * @method bool isParametersChanged()
	 * @method \string remindActualParameters()
	 * @method \string requireParameters()
	 * @method \Bitrix\Main\UrlPreview\EO_Route resetParameters()
	 * @method \Bitrix\Main\UrlPreview\EO_Route unsetParameters()
	 * @method \string fillParameters()
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
	 * @method \Bitrix\Main\UrlPreview\EO_Route set($fieldName, $value)
	 * @method \Bitrix\Main\UrlPreview\EO_Route reset($fieldName)
	 * @method \Bitrix\Main\UrlPreview\EO_Route unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\UrlPreview\EO_Route wakeUp($data)
	 */
	class EO_Route {
		/* @var \Bitrix\Main\UrlPreview\RouteTable */
		static public $dataClass = '\Bitrix\Main\UrlPreview\RouteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\UrlPreview {
	/**
	 * EO_Route_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getRouteList()
	 * @method \string[] fillRoute()
	 * @method \string[] getModuleList()
	 * @method \string[] fillModule()
	 * @method \string[] getClassList()
	 * @method \string[] fillClass()
	 * @method \string[] getParametersList()
	 * @method \string[] fillParameters()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\UrlPreview\EO_Route $object)
	 * @method bool has(\Bitrix\Main\UrlPreview\EO_Route $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\UrlPreview\EO_Route getByPrimary($primary)
	 * @method \Bitrix\Main\UrlPreview\EO_Route[] getAll()
	 * @method bool remove(\Bitrix\Main\UrlPreview\EO_Route $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\UrlPreview\EO_Route_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\UrlPreview\EO_Route current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Route_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UrlPreview\RouteTable */
		static public $dataClass = '\Bitrix\Main\UrlPreview\RouteTable';
	}
}
namespace Bitrix\Main\UrlPreview {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Route_Result exec()
	 * @method \Bitrix\Main\UrlPreview\EO_Route fetchObject()
	 * @method \Bitrix\Main\UrlPreview\EO_Route_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Route_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\UrlPreview\EO_Route fetchObject()
	 * @method \Bitrix\Main\UrlPreview\EO_Route_Collection fetchCollection()
	 */
	class EO_Route_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\UrlPreview\EO_Route createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\UrlPreview\EO_Route_Collection createCollection()
	 * @method \Bitrix\Main\UrlPreview\EO_Route wakeUpObject($row)
	 * @method \Bitrix\Main\UrlPreview\EO_Route_Collection wakeUpCollection($rows)
	 */
	class EO_Route_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UrlPreview\UrlMetadataTable:main/lib/urlpreview/urlmetadata.php */
namespace Bitrix\Main\UrlPreview {
	/**
	 * EO_UrlMetadata
	 * @see \Bitrix\Main\UrlPreview\UrlMetadataTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getUrl()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setUrl(\string|\Bitrix\Main\DB\SqlExpression $url)
	 * @method bool hasUrl()
	 * @method bool isUrlFilled()
	 * @method bool isUrlChanged()
	 * @method \string remindActualUrl()
	 * @method \string requireUrl()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetUrl()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetUrl()
	 * @method \string fillUrl()
	 * @method \string getType()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetType()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetType()
	 * @method \string fillType()
	 * @method \string getTitle()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetTitle()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getDescription()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetDescription()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetDescription()
	 * @method \string fillDescription()
	 * @method \int getImageId()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setImageId(\int|\Bitrix\Main\DB\SqlExpression $imageId)
	 * @method bool hasImageId()
	 * @method bool isImageIdFilled()
	 * @method bool isImageIdChanged()
	 * @method \int remindActualImageId()
	 * @method \int requireImageId()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetImageId()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetImageId()
	 * @method \int fillImageId()
	 * @method \string getImage()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setImage(\string|\Bitrix\Main\DB\SqlExpression $image)
	 * @method bool hasImage()
	 * @method bool isImageFilled()
	 * @method bool isImageChanged()
	 * @method \string remindActualImage()
	 * @method \string requireImage()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetImage()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetImage()
	 * @method \string fillImage()
	 * @method \string getEmbed()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setEmbed(\string|\Bitrix\Main\DB\SqlExpression $embed)
	 * @method bool hasEmbed()
	 * @method bool isEmbedFilled()
	 * @method bool isEmbedChanged()
	 * @method \string remindActualEmbed()
	 * @method \string requireEmbed()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetEmbed()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetEmbed()
	 * @method \string fillEmbed()
	 * @method \string getExtra()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setExtra(\string|\Bitrix\Main\DB\SqlExpression $extra)
	 * @method bool hasExtra()
	 * @method bool isExtraFilled()
	 * @method bool isExtraChanged()
	 * @method \string remindActualExtra()
	 * @method \string requireExtra()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetExtra()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetExtra()
	 * @method \string fillExtra()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetDateInsert()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime getDateExpire()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata setDateExpire(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateExpire)
	 * @method bool hasDateExpire()
	 * @method bool isDateExpireFilled()
	 * @method bool isDateExpireChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateExpire()
	 * @method \Bitrix\Main\Type\DateTime requireDateExpire()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata resetDateExpire()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unsetDateExpire()
	 * @method \Bitrix\Main\Type\DateTime fillDateExpire()
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
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata set($fieldName, $value)
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata reset($fieldName)
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\UrlPreview\EO_UrlMetadata wakeUp($data)
	 */
	class EO_UrlMetadata {
		/* @var \Bitrix\Main\UrlPreview\UrlMetadataTable */
		static public $dataClass = '\Bitrix\Main\UrlPreview\UrlMetadataTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\UrlPreview {
	/**
	 * EO_UrlMetadata_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getUrlList()
	 * @method \string[] fillUrl()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \int[] getImageIdList()
	 * @method \int[] fillImageId()
	 * @method \string[] getImageList()
	 * @method \string[] fillImage()
	 * @method \string[] getEmbedList()
	 * @method \string[] fillEmbed()
	 * @method \string[] getExtraList()
	 * @method \string[] fillExtra()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime[] getDateExpireList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateExpire()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\UrlPreview\EO_UrlMetadata $object)
	 * @method bool has(\Bitrix\Main\UrlPreview\EO_UrlMetadata $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata getByPrimary($primary)
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata[] getAll()
	 * @method bool remove(\Bitrix\Main\UrlPreview\EO_UrlMetadata $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\UrlPreview\EO_UrlMetadata_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UrlMetadata_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UrlPreview\UrlMetadataTable */
		static public $dataClass = '\Bitrix\Main\UrlPreview\UrlMetadataTable';
	}
}
namespace Bitrix\Main\UrlPreview {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UrlMetadata_Result exec()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata fetchObject()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UrlMetadata_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata fetchObject()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata_Collection fetchCollection()
	 */
	class EO_UrlMetadata_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata_Collection createCollection()
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata wakeUpObject($row)
	 * @method \Bitrix\Main\UrlPreview\EO_UrlMetadata_Collection wakeUpCollection($rows)
	 */
	class EO_UrlMetadata_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserTable:main/lib/user.php */
namespace Bitrix\Main {
	/**
	 * EO_User
	 * @see \Bitrix\Main\UserTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_User setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getLogin()
	 * @method \Bitrix\Main\EO_User setLogin(\string|\Bitrix\Main\DB\SqlExpression $login)
	 * @method bool hasLogin()
	 * @method bool isLoginFilled()
	 * @method bool isLoginChanged()
	 * @method \string remindActualLogin()
	 * @method \string requireLogin()
	 * @method \Bitrix\Main\EO_User resetLogin()
	 * @method \Bitrix\Main\EO_User unsetLogin()
	 * @method \string fillLogin()
	 * @method \string getPassword()
	 * @method \Bitrix\Main\EO_User setPassword(\string|\Bitrix\Main\DB\SqlExpression $password)
	 * @method bool hasPassword()
	 * @method bool isPasswordFilled()
	 * @method bool isPasswordChanged()
	 * @method \string remindActualPassword()
	 * @method \string requirePassword()
	 * @method \Bitrix\Main\EO_User resetPassword()
	 * @method \Bitrix\Main\EO_User unsetPassword()
	 * @method \string fillPassword()
	 * @method \string getEmail()
	 * @method \Bitrix\Main\EO_User setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Main\EO_User resetEmail()
	 * @method \Bitrix\Main\EO_User unsetEmail()
	 * @method \string fillEmail()
	 * @method \boolean getActive()
	 * @method \Bitrix\Main\EO_User setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Main\EO_User resetActive()
	 * @method \Bitrix\Main\EO_User unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getBlocked()
	 * @method \Bitrix\Main\EO_User setBlocked(\boolean|\Bitrix\Main\DB\SqlExpression $blocked)
	 * @method bool hasBlocked()
	 * @method bool isBlockedFilled()
	 * @method bool isBlockedChanged()
	 * @method \boolean remindActualBlocked()
	 * @method \boolean requireBlocked()
	 * @method \Bitrix\Main\EO_User resetBlocked()
	 * @method \Bitrix\Main\EO_User unsetBlocked()
	 * @method \boolean fillBlocked()
	 * @method \Bitrix\Main\Type\DateTime getDateRegister()
	 * @method \Bitrix\Main\EO_User setDateRegister(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateRegister)
	 * @method bool hasDateRegister()
	 * @method bool isDateRegisterFilled()
	 * @method bool isDateRegisterChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateRegister()
	 * @method \Bitrix\Main\Type\DateTime requireDateRegister()
	 * @method \Bitrix\Main\EO_User resetDateRegister()
	 * @method \Bitrix\Main\EO_User unsetDateRegister()
	 * @method \Bitrix\Main\Type\DateTime fillDateRegister()
	 * @method \Bitrix\Main\Type\DateTime getDateRegShort()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateRegShort()
	 * @method \Bitrix\Main\Type\DateTime requireDateRegShort()
	 * @method bool hasDateRegShort()
	 * @method bool isDateRegShortFilled()
	 * @method \Bitrix\Main\EO_User unsetDateRegShort()
	 * @method \Bitrix\Main\Type\DateTime fillDateRegShort()
	 * @method \Bitrix\Main\Type\DateTime getLastLogin()
	 * @method \Bitrix\Main\EO_User setLastLogin(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastLogin)
	 * @method bool hasLastLogin()
	 * @method bool isLastLoginFilled()
	 * @method bool isLastLoginChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastLogin()
	 * @method \Bitrix\Main\Type\DateTime requireLastLogin()
	 * @method \Bitrix\Main\EO_User resetLastLogin()
	 * @method \Bitrix\Main\EO_User unsetLastLogin()
	 * @method \Bitrix\Main\Type\DateTime fillLastLogin()
	 * @method \Bitrix\Main\Type\DateTime getLastLoginShort()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastLoginShort()
	 * @method \Bitrix\Main\Type\DateTime requireLastLoginShort()
	 * @method bool hasLastLoginShort()
	 * @method bool isLastLoginShortFilled()
	 * @method \Bitrix\Main\EO_User unsetLastLoginShort()
	 * @method \Bitrix\Main\Type\DateTime fillLastLoginShort()
	 * @method \Bitrix\Main\Type\DateTime getLastActivityDate()
	 * @method \Bitrix\Main\EO_User setLastActivityDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastActivityDate)
	 * @method bool hasLastActivityDate()
	 * @method bool isLastActivityDateFilled()
	 * @method bool isLastActivityDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastActivityDate()
	 * @method \Bitrix\Main\Type\DateTime requireLastActivityDate()
	 * @method \Bitrix\Main\EO_User resetLastActivityDate()
	 * @method \Bitrix\Main\EO_User unsetLastActivityDate()
	 * @method \Bitrix\Main\Type\DateTime fillLastActivityDate()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Main\EO_User setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Main\EO_User resetTimestampX()
	 * @method \Bitrix\Main\EO_User unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getName()
	 * @method \Bitrix\Main\EO_User setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\EO_User resetName()
	 * @method \Bitrix\Main\EO_User unsetName()
	 * @method \string fillName()
	 * @method \string getSecondName()
	 * @method \Bitrix\Main\EO_User setSecondName(\string|\Bitrix\Main\DB\SqlExpression $secondName)
	 * @method bool hasSecondName()
	 * @method bool isSecondNameFilled()
	 * @method bool isSecondNameChanged()
	 * @method \string remindActualSecondName()
	 * @method \string requireSecondName()
	 * @method \Bitrix\Main\EO_User resetSecondName()
	 * @method \Bitrix\Main\EO_User unsetSecondName()
	 * @method \string fillSecondName()
	 * @method \string getLastName()
	 * @method \Bitrix\Main\EO_User setLastName(\string|\Bitrix\Main\DB\SqlExpression $lastName)
	 * @method bool hasLastName()
	 * @method bool isLastNameFilled()
	 * @method bool isLastNameChanged()
	 * @method \string remindActualLastName()
	 * @method \string requireLastName()
	 * @method \Bitrix\Main\EO_User resetLastName()
	 * @method \Bitrix\Main\EO_User unsetLastName()
	 * @method \string fillLastName()
	 * @method \string getTitle()
	 * @method \Bitrix\Main\EO_User setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Main\EO_User resetTitle()
	 * @method \Bitrix\Main\EO_User unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getExternalAuthId()
	 * @method \Bitrix\Main\EO_User setExternalAuthId(\string|\Bitrix\Main\DB\SqlExpression $externalAuthId)
	 * @method bool hasExternalAuthId()
	 * @method bool isExternalAuthIdFilled()
	 * @method bool isExternalAuthIdChanged()
	 * @method \string remindActualExternalAuthId()
	 * @method \string requireExternalAuthId()
	 * @method \Bitrix\Main\EO_User resetExternalAuthId()
	 * @method \Bitrix\Main\EO_User unsetExternalAuthId()
	 * @method \string fillExternalAuthId()
	 * @method \string getXmlId()
	 * @method \Bitrix\Main\EO_User setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Main\EO_User resetXmlId()
	 * @method \Bitrix\Main\EO_User unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getBxUserId()
	 * @method \Bitrix\Main\EO_User setBxUserId(\string|\Bitrix\Main\DB\SqlExpression $bxUserId)
	 * @method bool hasBxUserId()
	 * @method bool isBxUserIdFilled()
	 * @method bool isBxUserIdChanged()
	 * @method \string remindActualBxUserId()
	 * @method \string requireBxUserId()
	 * @method \Bitrix\Main\EO_User resetBxUserId()
	 * @method \Bitrix\Main\EO_User unsetBxUserId()
	 * @method \string fillBxUserId()
	 * @method \string getConfirmCode()
	 * @method \Bitrix\Main\EO_User setConfirmCode(\string|\Bitrix\Main\DB\SqlExpression $confirmCode)
	 * @method bool hasConfirmCode()
	 * @method bool isConfirmCodeFilled()
	 * @method bool isConfirmCodeChanged()
	 * @method \string remindActualConfirmCode()
	 * @method \string requireConfirmCode()
	 * @method \Bitrix\Main\EO_User resetConfirmCode()
	 * @method \Bitrix\Main\EO_User unsetConfirmCode()
	 * @method \string fillConfirmCode()
	 * @method \string getLid()
	 * @method \Bitrix\Main\EO_User setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string remindActualLid()
	 * @method \string requireLid()
	 * @method \Bitrix\Main\EO_User resetLid()
	 * @method \Bitrix\Main\EO_User unsetLid()
	 * @method \string fillLid()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Main\EO_User setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\Main\EO_User resetLanguageId()
	 * @method \Bitrix\Main\EO_User unsetLanguageId()
	 * @method \string fillLanguageId()
	 * @method \string getTimeZone()
	 * @method \Bitrix\Main\EO_User setTimeZone(\string|\Bitrix\Main\DB\SqlExpression $timeZone)
	 * @method bool hasTimeZone()
	 * @method bool isTimeZoneFilled()
	 * @method bool isTimeZoneChanged()
	 * @method \string remindActualTimeZone()
	 * @method \string requireTimeZone()
	 * @method \Bitrix\Main\EO_User resetTimeZone()
	 * @method \Bitrix\Main\EO_User unsetTimeZone()
	 * @method \string fillTimeZone()
	 * @method \int getTimeZoneOffset()
	 * @method \Bitrix\Main\EO_User setTimeZoneOffset(\int|\Bitrix\Main\DB\SqlExpression $timeZoneOffset)
	 * @method bool hasTimeZoneOffset()
	 * @method bool isTimeZoneOffsetFilled()
	 * @method bool isTimeZoneOffsetChanged()
	 * @method \int remindActualTimeZoneOffset()
	 * @method \int requireTimeZoneOffset()
	 * @method \Bitrix\Main\EO_User resetTimeZoneOffset()
	 * @method \Bitrix\Main\EO_User unsetTimeZoneOffset()
	 * @method \int fillTimeZoneOffset()
	 * @method \string getPersonalProfession()
	 * @method \Bitrix\Main\EO_User setPersonalProfession(\string|\Bitrix\Main\DB\SqlExpression $personalProfession)
	 * @method bool hasPersonalProfession()
	 * @method bool isPersonalProfessionFilled()
	 * @method bool isPersonalProfessionChanged()
	 * @method \string remindActualPersonalProfession()
	 * @method \string requirePersonalProfession()
	 * @method \Bitrix\Main\EO_User resetPersonalProfession()
	 * @method \Bitrix\Main\EO_User unsetPersonalProfession()
	 * @method \string fillPersonalProfession()
	 * @method \string getPersonalPhone()
	 * @method \Bitrix\Main\EO_User setPersonalPhone(\string|\Bitrix\Main\DB\SqlExpression $personalPhone)
	 * @method bool hasPersonalPhone()
	 * @method bool isPersonalPhoneFilled()
	 * @method bool isPersonalPhoneChanged()
	 * @method \string remindActualPersonalPhone()
	 * @method \string requirePersonalPhone()
	 * @method \Bitrix\Main\EO_User resetPersonalPhone()
	 * @method \Bitrix\Main\EO_User unsetPersonalPhone()
	 * @method \string fillPersonalPhone()
	 * @method \string getPersonalMobile()
	 * @method \Bitrix\Main\EO_User setPersonalMobile(\string|\Bitrix\Main\DB\SqlExpression $personalMobile)
	 * @method bool hasPersonalMobile()
	 * @method bool isPersonalMobileFilled()
	 * @method bool isPersonalMobileChanged()
	 * @method \string remindActualPersonalMobile()
	 * @method \string requirePersonalMobile()
	 * @method \Bitrix\Main\EO_User resetPersonalMobile()
	 * @method \Bitrix\Main\EO_User unsetPersonalMobile()
	 * @method \string fillPersonalMobile()
	 * @method \string getPersonalWww()
	 * @method \Bitrix\Main\EO_User setPersonalWww(\string|\Bitrix\Main\DB\SqlExpression $personalWww)
	 * @method bool hasPersonalWww()
	 * @method bool isPersonalWwwFilled()
	 * @method bool isPersonalWwwChanged()
	 * @method \string remindActualPersonalWww()
	 * @method \string requirePersonalWww()
	 * @method \Bitrix\Main\EO_User resetPersonalWww()
	 * @method \Bitrix\Main\EO_User unsetPersonalWww()
	 * @method \string fillPersonalWww()
	 * @method \string getPersonalIcq()
	 * @method \Bitrix\Main\EO_User setPersonalIcq(\string|\Bitrix\Main\DB\SqlExpression $personalIcq)
	 * @method bool hasPersonalIcq()
	 * @method bool isPersonalIcqFilled()
	 * @method bool isPersonalIcqChanged()
	 * @method \string remindActualPersonalIcq()
	 * @method \string requirePersonalIcq()
	 * @method \Bitrix\Main\EO_User resetPersonalIcq()
	 * @method \Bitrix\Main\EO_User unsetPersonalIcq()
	 * @method \string fillPersonalIcq()
	 * @method \string getPersonalFax()
	 * @method \Bitrix\Main\EO_User setPersonalFax(\string|\Bitrix\Main\DB\SqlExpression $personalFax)
	 * @method bool hasPersonalFax()
	 * @method bool isPersonalFaxFilled()
	 * @method bool isPersonalFaxChanged()
	 * @method \string remindActualPersonalFax()
	 * @method \string requirePersonalFax()
	 * @method \Bitrix\Main\EO_User resetPersonalFax()
	 * @method \Bitrix\Main\EO_User unsetPersonalFax()
	 * @method \string fillPersonalFax()
	 * @method \string getPersonalPager()
	 * @method \Bitrix\Main\EO_User setPersonalPager(\string|\Bitrix\Main\DB\SqlExpression $personalPager)
	 * @method bool hasPersonalPager()
	 * @method bool isPersonalPagerFilled()
	 * @method bool isPersonalPagerChanged()
	 * @method \string remindActualPersonalPager()
	 * @method \string requirePersonalPager()
	 * @method \Bitrix\Main\EO_User resetPersonalPager()
	 * @method \Bitrix\Main\EO_User unsetPersonalPager()
	 * @method \string fillPersonalPager()
	 * @method \string getPersonalStreet()
	 * @method \Bitrix\Main\EO_User setPersonalStreet(\string|\Bitrix\Main\DB\SqlExpression $personalStreet)
	 * @method bool hasPersonalStreet()
	 * @method bool isPersonalStreetFilled()
	 * @method bool isPersonalStreetChanged()
	 * @method \string remindActualPersonalStreet()
	 * @method \string requirePersonalStreet()
	 * @method \Bitrix\Main\EO_User resetPersonalStreet()
	 * @method \Bitrix\Main\EO_User unsetPersonalStreet()
	 * @method \string fillPersonalStreet()
	 * @method \string getPersonalMailbox()
	 * @method \Bitrix\Main\EO_User setPersonalMailbox(\string|\Bitrix\Main\DB\SqlExpression $personalMailbox)
	 * @method bool hasPersonalMailbox()
	 * @method bool isPersonalMailboxFilled()
	 * @method bool isPersonalMailboxChanged()
	 * @method \string remindActualPersonalMailbox()
	 * @method \string requirePersonalMailbox()
	 * @method \Bitrix\Main\EO_User resetPersonalMailbox()
	 * @method \Bitrix\Main\EO_User unsetPersonalMailbox()
	 * @method \string fillPersonalMailbox()
	 * @method \string getPersonalCity()
	 * @method \Bitrix\Main\EO_User setPersonalCity(\string|\Bitrix\Main\DB\SqlExpression $personalCity)
	 * @method bool hasPersonalCity()
	 * @method bool isPersonalCityFilled()
	 * @method bool isPersonalCityChanged()
	 * @method \string remindActualPersonalCity()
	 * @method \string requirePersonalCity()
	 * @method \Bitrix\Main\EO_User resetPersonalCity()
	 * @method \Bitrix\Main\EO_User unsetPersonalCity()
	 * @method \string fillPersonalCity()
	 * @method \string getPersonalState()
	 * @method \Bitrix\Main\EO_User setPersonalState(\string|\Bitrix\Main\DB\SqlExpression $personalState)
	 * @method bool hasPersonalState()
	 * @method bool isPersonalStateFilled()
	 * @method bool isPersonalStateChanged()
	 * @method \string remindActualPersonalState()
	 * @method \string requirePersonalState()
	 * @method \Bitrix\Main\EO_User resetPersonalState()
	 * @method \Bitrix\Main\EO_User unsetPersonalState()
	 * @method \string fillPersonalState()
	 * @method \string getPersonalZip()
	 * @method \Bitrix\Main\EO_User setPersonalZip(\string|\Bitrix\Main\DB\SqlExpression $personalZip)
	 * @method bool hasPersonalZip()
	 * @method bool isPersonalZipFilled()
	 * @method bool isPersonalZipChanged()
	 * @method \string remindActualPersonalZip()
	 * @method \string requirePersonalZip()
	 * @method \Bitrix\Main\EO_User resetPersonalZip()
	 * @method \Bitrix\Main\EO_User unsetPersonalZip()
	 * @method \string fillPersonalZip()
	 * @method \string getPersonalCountry()
	 * @method \Bitrix\Main\EO_User setPersonalCountry(\string|\Bitrix\Main\DB\SqlExpression $personalCountry)
	 * @method bool hasPersonalCountry()
	 * @method bool isPersonalCountryFilled()
	 * @method bool isPersonalCountryChanged()
	 * @method \string remindActualPersonalCountry()
	 * @method \string requirePersonalCountry()
	 * @method \Bitrix\Main\EO_User resetPersonalCountry()
	 * @method \Bitrix\Main\EO_User unsetPersonalCountry()
	 * @method \string fillPersonalCountry()
	 * @method \Bitrix\Main\Type\Date getPersonalBirthday()
	 * @method \Bitrix\Main\EO_User setPersonalBirthday(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $personalBirthday)
	 * @method bool hasPersonalBirthday()
	 * @method bool isPersonalBirthdayFilled()
	 * @method bool isPersonalBirthdayChanged()
	 * @method \Bitrix\Main\Type\Date remindActualPersonalBirthday()
	 * @method \Bitrix\Main\Type\Date requirePersonalBirthday()
	 * @method \Bitrix\Main\EO_User resetPersonalBirthday()
	 * @method \Bitrix\Main\EO_User unsetPersonalBirthday()
	 * @method \Bitrix\Main\Type\Date fillPersonalBirthday()
	 * @method \string getPersonalGender()
	 * @method \Bitrix\Main\EO_User setPersonalGender(\string|\Bitrix\Main\DB\SqlExpression $personalGender)
	 * @method bool hasPersonalGender()
	 * @method bool isPersonalGenderFilled()
	 * @method bool isPersonalGenderChanged()
	 * @method \string remindActualPersonalGender()
	 * @method \string requirePersonalGender()
	 * @method \Bitrix\Main\EO_User resetPersonalGender()
	 * @method \Bitrix\Main\EO_User unsetPersonalGender()
	 * @method \string fillPersonalGender()
	 * @method \int getPersonalPhoto()
	 * @method \Bitrix\Main\EO_User setPersonalPhoto(\int|\Bitrix\Main\DB\SqlExpression $personalPhoto)
	 * @method bool hasPersonalPhoto()
	 * @method bool isPersonalPhotoFilled()
	 * @method bool isPersonalPhotoChanged()
	 * @method \int remindActualPersonalPhoto()
	 * @method \int requirePersonalPhoto()
	 * @method \Bitrix\Main\EO_User resetPersonalPhoto()
	 * @method \Bitrix\Main\EO_User unsetPersonalPhoto()
	 * @method \int fillPersonalPhoto()
	 * @method \string getPersonalNotes()
	 * @method \Bitrix\Main\EO_User setPersonalNotes(\string|\Bitrix\Main\DB\SqlExpression $personalNotes)
	 * @method bool hasPersonalNotes()
	 * @method bool isPersonalNotesFilled()
	 * @method bool isPersonalNotesChanged()
	 * @method \string remindActualPersonalNotes()
	 * @method \string requirePersonalNotes()
	 * @method \Bitrix\Main\EO_User resetPersonalNotes()
	 * @method \Bitrix\Main\EO_User unsetPersonalNotes()
	 * @method \string fillPersonalNotes()
	 * @method \string getWorkCompany()
	 * @method \Bitrix\Main\EO_User setWorkCompany(\string|\Bitrix\Main\DB\SqlExpression $workCompany)
	 * @method bool hasWorkCompany()
	 * @method bool isWorkCompanyFilled()
	 * @method bool isWorkCompanyChanged()
	 * @method \string remindActualWorkCompany()
	 * @method \string requireWorkCompany()
	 * @method \Bitrix\Main\EO_User resetWorkCompany()
	 * @method \Bitrix\Main\EO_User unsetWorkCompany()
	 * @method \string fillWorkCompany()
	 * @method \string getWorkDepartment()
	 * @method \Bitrix\Main\EO_User setWorkDepartment(\string|\Bitrix\Main\DB\SqlExpression $workDepartment)
	 * @method bool hasWorkDepartment()
	 * @method bool isWorkDepartmentFilled()
	 * @method bool isWorkDepartmentChanged()
	 * @method \string remindActualWorkDepartment()
	 * @method \string requireWorkDepartment()
	 * @method \Bitrix\Main\EO_User resetWorkDepartment()
	 * @method \Bitrix\Main\EO_User unsetWorkDepartment()
	 * @method \string fillWorkDepartment()
	 * @method \string getWorkPhone()
	 * @method \Bitrix\Main\EO_User setWorkPhone(\string|\Bitrix\Main\DB\SqlExpression $workPhone)
	 * @method bool hasWorkPhone()
	 * @method bool isWorkPhoneFilled()
	 * @method bool isWorkPhoneChanged()
	 * @method \string remindActualWorkPhone()
	 * @method \string requireWorkPhone()
	 * @method \Bitrix\Main\EO_User resetWorkPhone()
	 * @method \Bitrix\Main\EO_User unsetWorkPhone()
	 * @method \string fillWorkPhone()
	 * @method \string getWorkPosition()
	 * @method \Bitrix\Main\EO_User setWorkPosition(\string|\Bitrix\Main\DB\SqlExpression $workPosition)
	 * @method bool hasWorkPosition()
	 * @method bool isWorkPositionFilled()
	 * @method bool isWorkPositionChanged()
	 * @method \string remindActualWorkPosition()
	 * @method \string requireWorkPosition()
	 * @method \Bitrix\Main\EO_User resetWorkPosition()
	 * @method \Bitrix\Main\EO_User unsetWorkPosition()
	 * @method \string fillWorkPosition()
	 * @method \string getWorkWww()
	 * @method \Bitrix\Main\EO_User setWorkWww(\string|\Bitrix\Main\DB\SqlExpression $workWww)
	 * @method bool hasWorkWww()
	 * @method bool isWorkWwwFilled()
	 * @method bool isWorkWwwChanged()
	 * @method \string remindActualWorkWww()
	 * @method \string requireWorkWww()
	 * @method \Bitrix\Main\EO_User resetWorkWww()
	 * @method \Bitrix\Main\EO_User unsetWorkWww()
	 * @method \string fillWorkWww()
	 * @method \string getWorkFax()
	 * @method \Bitrix\Main\EO_User setWorkFax(\string|\Bitrix\Main\DB\SqlExpression $workFax)
	 * @method bool hasWorkFax()
	 * @method bool isWorkFaxFilled()
	 * @method bool isWorkFaxChanged()
	 * @method \string remindActualWorkFax()
	 * @method \string requireWorkFax()
	 * @method \Bitrix\Main\EO_User resetWorkFax()
	 * @method \Bitrix\Main\EO_User unsetWorkFax()
	 * @method \string fillWorkFax()
	 * @method \string getWorkPager()
	 * @method \Bitrix\Main\EO_User setWorkPager(\string|\Bitrix\Main\DB\SqlExpression $workPager)
	 * @method bool hasWorkPager()
	 * @method bool isWorkPagerFilled()
	 * @method bool isWorkPagerChanged()
	 * @method \string remindActualWorkPager()
	 * @method \string requireWorkPager()
	 * @method \Bitrix\Main\EO_User resetWorkPager()
	 * @method \Bitrix\Main\EO_User unsetWorkPager()
	 * @method \string fillWorkPager()
	 * @method \string getWorkStreet()
	 * @method \Bitrix\Main\EO_User setWorkStreet(\string|\Bitrix\Main\DB\SqlExpression $workStreet)
	 * @method bool hasWorkStreet()
	 * @method bool isWorkStreetFilled()
	 * @method bool isWorkStreetChanged()
	 * @method \string remindActualWorkStreet()
	 * @method \string requireWorkStreet()
	 * @method \Bitrix\Main\EO_User resetWorkStreet()
	 * @method \Bitrix\Main\EO_User unsetWorkStreet()
	 * @method \string fillWorkStreet()
	 * @method \string getWorkMailbox()
	 * @method \Bitrix\Main\EO_User setWorkMailbox(\string|\Bitrix\Main\DB\SqlExpression $workMailbox)
	 * @method bool hasWorkMailbox()
	 * @method bool isWorkMailboxFilled()
	 * @method bool isWorkMailboxChanged()
	 * @method \string remindActualWorkMailbox()
	 * @method \string requireWorkMailbox()
	 * @method \Bitrix\Main\EO_User resetWorkMailbox()
	 * @method \Bitrix\Main\EO_User unsetWorkMailbox()
	 * @method \string fillWorkMailbox()
	 * @method \string getWorkCity()
	 * @method \Bitrix\Main\EO_User setWorkCity(\string|\Bitrix\Main\DB\SqlExpression $workCity)
	 * @method bool hasWorkCity()
	 * @method bool isWorkCityFilled()
	 * @method bool isWorkCityChanged()
	 * @method \string remindActualWorkCity()
	 * @method \string requireWorkCity()
	 * @method \Bitrix\Main\EO_User resetWorkCity()
	 * @method \Bitrix\Main\EO_User unsetWorkCity()
	 * @method \string fillWorkCity()
	 * @method \string getWorkState()
	 * @method \Bitrix\Main\EO_User setWorkState(\string|\Bitrix\Main\DB\SqlExpression $workState)
	 * @method bool hasWorkState()
	 * @method bool isWorkStateFilled()
	 * @method bool isWorkStateChanged()
	 * @method \string remindActualWorkState()
	 * @method \string requireWorkState()
	 * @method \Bitrix\Main\EO_User resetWorkState()
	 * @method \Bitrix\Main\EO_User unsetWorkState()
	 * @method \string fillWorkState()
	 * @method \string getWorkZip()
	 * @method \Bitrix\Main\EO_User setWorkZip(\string|\Bitrix\Main\DB\SqlExpression $workZip)
	 * @method bool hasWorkZip()
	 * @method bool isWorkZipFilled()
	 * @method bool isWorkZipChanged()
	 * @method \string remindActualWorkZip()
	 * @method \string requireWorkZip()
	 * @method \Bitrix\Main\EO_User resetWorkZip()
	 * @method \Bitrix\Main\EO_User unsetWorkZip()
	 * @method \string fillWorkZip()
	 * @method \string getWorkCountry()
	 * @method \Bitrix\Main\EO_User setWorkCountry(\string|\Bitrix\Main\DB\SqlExpression $workCountry)
	 * @method bool hasWorkCountry()
	 * @method bool isWorkCountryFilled()
	 * @method bool isWorkCountryChanged()
	 * @method \string remindActualWorkCountry()
	 * @method \string requireWorkCountry()
	 * @method \Bitrix\Main\EO_User resetWorkCountry()
	 * @method \Bitrix\Main\EO_User unsetWorkCountry()
	 * @method \string fillWorkCountry()
	 * @method \string getWorkProfile()
	 * @method \Bitrix\Main\EO_User setWorkProfile(\string|\Bitrix\Main\DB\SqlExpression $workProfile)
	 * @method bool hasWorkProfile()
	 * @method bool isWorkProfileFilled()
	 * @method bool isWorkProfileChanged()
	 * @method \string remindActualWorkProfile()
	 * @method \string requireWorkProfile()
	 * @method \Bitrix\Main\EO_User resetWorkProfile()
	 * @method \Bitrix\Main\EO_User unsetWorkProfile()
	 * @method \string fillWorkProfile()
	 * @method \int getWorkLogo()
	 * @method \Bitrix\Main\EO_User setWorkLogo(\int|\Bitrix\Main\DB\SqlExpression $workLogo)
	 * @method bool hasWorkLogo()
	 * @method bool isWorkLogoFilled()
	 * @method bool isWorkLogoChanged()
	 * @method \int remindActualWorkLogo()
	 * @method \int requireWorkLogo()
	 * @method \Bitrix\Main\EO_User resetWorkLogo()
	 * @method \Bitrix\Main\EO_User unsetWorkLogo()
	 * @method \int fillWorkLogo()
	 * @method \string getWorkNotes()
	 * @method \Bitrix\Main\EO_User setWorkNotes(\string|\Bitrix\Main\DB\SqlExpression $workNotes)
	 * @method bool hasWorkNotes()
	 * @method bool isWorkNotesFilled()
	 * @method bool isWorkNotesChanged()
	 * @method \string remindActualWorkNotes()
	 * @method \string requireWorkNotes()
	 * @method \Bitrix\Main\EO_User resetWorkNotes()
	 * @method \Bitrix\Main\EO_User unsetWorkNotes()
	 * @method \string fillWorkNotes()
	 * @method \string getAdminNotes()
	 * @method \Bitrix\Main\EO_User setAdminNotes(\string|\Bitrix\Main\DB\SqlExpression $adminNotes)
	 * @method bool hasAdminNotes()
	 * @method bool isAdminNotesFilled()
	 * @method bool isAdminNotesChanged()
	 * @method \string remindActualAdminNotes()
	 * @method \string requireAdminNotes()
	 * @method \Bitrix\Main\EO_User resetAdminNotes()
	 * @method \Bitrix\Main\EO_User unsetAdminNotes()
	 * @method \string fillAdminNotes()
	 * @method \string getShortName()
	 * @method \string remindActualShortName()
	 * @method \string requireShortName()
	 * @method bool hasShortName()
	 * @method bool isShortNameFilled()
	 * @method \Bitrix\Main\EO_User unsetShortName()
	 * @method \string fillShortName()
	 * @method \boolean getIsOnline()
	 * @method \boolean remindActualIsOnline()
	 * @method \boolean requireIsOnline()
	 * @method bool hasIsOnline()
	 * @method bool isIsOnlineFilled()
	 * @method \Bitrix\Main\EO_User unsetIsOnline()
	 * @method \boolean fillIsOnline()
	 * @method \boolean getIsRealUser()
	 * @method \boolean remindActualIsRealUser()
	 * @method \boolean requireIsRealUser()
	 * @method bool hasIsRealUser()
	 * @method bool isIsRealUserFilled()
	 * @method \Bitrix\Main\EO_User unsetIsRealUser()
	 * @method \boolean fillIsRealUser()
	 * @method \Bitrix\Main\EO_UserIndex getIndex()
	 * @method \Bitrix\Main\EO_UserIndex remindActualIndex()
	 * @method \Bitrix\Main\EO_UserIndex requireIndex()
	 * @method \Bitrix\Main\EO_User setIndex(\Bitrix\Main\EO_UserIndex $object)
	 * @method \Bitrix\Main\EO_User resetIndex()
	 * @method \Bitrix\Main\EO_User unsetIndex()
	 * @method bool hasIndex()
	 * @method bool isIndexFilled()
	 * @method bool isIndexChanged()
	 * @method \Bitrix\Main\EO_UserIndex fillIndex()
	 * @method \Bitrix\Main\EO_UserCounter getCounter()
	 * @method \Bitrix\Main\EO_UserCounter remindActualCounter()
	 * @method \Bitrix\Main\EO_UserCounter requireCounter()
	 * @method \Bitrix\Main\EO_User setCounter(\Bitrix\Main\EO_UserCounter $object)
	 * @method \Bitrix\Main\EO_User resetCounter()
	 * @method \Bitrix\Main\EO_User unsetCounter()
	 * @method bool hasCounter()
	 * @method bool isCounterFilled()
	 * @method bool isCounterChanged()
	 * @method \Bitrix\Main\EO_UserCounter fillCounter()
	 * @method \Bitrix\Main\EO_UserPhoneAuth getPhoneAuth()
	 * @method \Bitrix\Main\EO_UserPhoneAuth remindActualPhoneAuth()
	 * @method \Bitrix\Main\EO_UserPhoneAuth requirePhoneAuth()
	 * @method \Bitrix\Main\EO_User setPhoneAuth(\Bitrix\Main\EO_UserPhoneAuth $object)
	 * @method \Bitrix\Main\EO_User resetPhoneAuth()
	 * @method \Bitrix\Main\EO_User unsetPhoneAuth()
	 * @method bool hasPhoneAuth()
	 * @method bool isPhoneAuthFilled()
	 * @method bool isPhoneAuthChanged()
	 * @method \Bitrix\Main\EO_UserPhoneAuth fillPhoneAuth()
	 * @method \Bitrix\Main\EO_UserGroup_Collection getGroups()
	 * @method \Bitrix\Main\EO_UserGroup_Collection requireGroups()
	 * @method \Bitrix\Main\EO_UserGroup_Collection fillGroups()
	 * @method bool hasGroups()
	 * @method bool isGroupsFilled()
	 * @method bool isGroupsChanged()
	 * @method void addToGroups(\Bitrix\Main\EO_UserGroup $userGroup)
	 * @method void removeFromGroups(\Bitrix\Main\EO_UserGroup $userGroup)
	 * @method void removeAllGroups()
	 * @method \Bitrix\Main\EO_User resetGroups()
	 * @method \Bitrix\Main\EO_User unsetGroups()
	 * @method \Bitrix\Main\Localization\EO_Language getActiveLanguage()
	 * @method \Bitrix\Main\Localization\EO_Language remindActualActiveLanguage()
	 * @method \Bitrix\Main\Localization\EO_Language requireActiveLanguage()
	 * @method \Bitrix\Main\EO_User setActiveLanguage(\Bitrix\Main\Localization\EO_Language $object)
	 * @method \Bitrix\Main\EO_User resetActiveLanguage()
	 * @method \Bitrix\Main\EO_User unsetActiveLanguage()
	 * @method bool hasActiveLanguage()
	 * @method bool isActiveLanguageFilled()
	 * @method bool isActiveLanguageChanged()
	 * @method \Bitrix\Main\Localization\EO_Language fillActiveLanguage()
	 * @method \string getNotificationLanguageId()
	 * @method \string remindActualNotificationLanguageId()
	 * @method \string requireNotificationLanguageId()
	 * @method bool hasNotificationLanguageId()
	 * @method bool isNotificationLanguageIdFilled()
	 * @method \Bitrix\Main\EO_User unsetNotificationLanguageId()
	 * @method \string fillNotificationLanguageId()
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
	 * @method \Bitrix\Main\EO_User set($fieldName, $value)
	 * @method \Bitrix\Main\EO_User reset($fieldName)
	 * @method \Bitrix\Main\EO_User unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_User wakeUp($data)
	 */
	class EO_User {
		/* @var \Bitrix\Main\UserTable */
		static public $dataClass = '\Bitrix\Main\UserTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_User_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getLoginList()
	 * @method \string[] fillLogin()
	 * @method \string[] getPasswordList()
	 * @method \string[] fillPassword()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getBlockedList()
	 * @method \boolean[] fillBlocked()
	 * @method \Bitrix\Main\Type\DateTime[] getDateRegisterList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateRegister()
	 * @method \Bitrix\Main\Type\DateTime[] getDateRegShortList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateRegShort()
	 * @method \Bitrix\Main\Type\DateTime[] getLastLoginList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastLogin()
	 * @method \Bitrix\Main\Type\DateTime[] getLastLoginShortList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastLoginShort()
	 * @method \Bitrix\Main\Type\DateTime[] getLastActivityDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastActivityDate()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getSecondNameList()
	 * @method \string[] fillSecondName()
	 * @method \string[] getLastNameList()
	 * @method \string[] fillLastName()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getExternalAuthIdList()
	 * @method \string[] fillExternalAuthId()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getBxUserIdList()
	 * @method \string[] fillBxUserId()
	 * @method \string[] getConfirmCodeList()
	 * @method \string[] fillConfirmCode()
	 * @method \string[] getLidList()
	 * @method \string[] fillLid()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 * @method \string[] getTimeZoneList()
	 * @method \string[] fillTimeZone()
	 * @method \int[] getTimeZoneOffsetList()
	 * @method \int[] fillTimeZoneOffset()
	 * @method \string[] getPersonalProfessionList()
	 * @method \string[] fillPersonalProfession()
	 * @method \string[] getPersonalPhoneList()
	 * @method \string[] fillPersonalPhone()
	 * @method \string[] getPersonalMobileList()
	 * @method \string[] fillPersonalMobile()
	 * @method \string[] getPersonalWwwList()
	 * @method \string[] fillPersonalWww()
	 * @method \string[] getPersonalIcqList()
	 * @method \string[] fillPersonalIcq()
	 * @method \string[] getPersonalFaxList()
	 * @method \string[] fillPersonalFax()
	 * @method \string[] getPersonalPagerList()
	 * @method \string[] fillPersonalPager()
	 * @method \string[] getPersonalStreetList()
	 * @method \string[] fillPersonalStreet()
	 * @method \string[] getPersonalMailboxList()
	 * @method \string[] fillPersonalMailbox()
	 * @method \string[] getPersonalCityList()
	 * @method \string[] fillPersonalCity()
	 * @method \string[] getPersonalStateList()
	 * @method \string[] fillPersonalState()
	 * @method \string[] getPersonalZipList()
	 * @method \string[] fillPersonalZip()
	 * @method \string[] getPersonalCountryList()
	 * @method \string[] fillPersonalCountry()
	 * @method \Bitrix\Main\Type\Date[] getPersonalBirthdayList()
	 * @method \Bitrix\Main\Type\Date[] fillPersonalBirthday()
	 * @method \string[] getPersonalGenderList()
	 * @method \string[] fillPersonalGender()
	 * @method \int[] getPersonalPhotoList()
	 * @method \int[] fillPersonalPhoto()
	 * @method \string[] getPersonalNotesList()
	 * @method \string[] fillPersonalNotes()
	 * @method \string[] getWorkCompanyList()
	 * @method \string[] fillWorkCompany()
	 * @method \string[] getWorkDepartmentList()
	 * @method \string[] fillWorkDepartment()
	 * @method \string[] getWorkPhoneList()
	 * @method \string[] fillWorkPhone()
	 * @method \string[] getWorkPositionList()
	 * @method \string[] fillWorkPosition()
	 * @method \string[] getWorkWwwList()
	 * @method \string[] fillWorkWww()
	 * @method \string[] getWorkFaxList()
	 * @method \string[] fillWorkFax()
	 * @method \string[] getWorkPagerList()
	 * @method \string[] fillWorkPager()
	 * @method \string[] getWorkStreetList()
	 * @method \string[] fillWorkStreet()
	 * @method \string[] getWorkMailboxList()
	 * @method \string[] fillWorkMailbox()
	 * @method \string[] getWorkCityList()
	 * @method \string[] fillWorkCity()
	 * @method \string[] getWorkStateList()
	 * @method \string[] fillWorkState()
	 * @method \string[] getWorkZipList()
	 * @method \string[] fillWorkZip()
	 * @method \string[] getWorkCountryList()
	 * @method \string[] fillWorkCountry()
	 * @method \string[] getWorkProfileList()
	 * @method \string[] fillWorkProfile()
	 * @method \int[] getWorkLogoList()
	 * @method \int[] fillWorkLogo()
	 * @method \string[] getWorkNotesList()
	 * @method \string[] fillWorkNotes()
	 * @method \string[] getAdminNotesList()
	 * @method \string[] fillAdminNotes()
	 * @method \string[] getShortNameList()
	 * @method \string[] fillShortName()
	 * @method \boolean[] getIsOnlineList()
	 * @method \boolean[] fillIsOnline()
	 * @method \boolean[] getIsRealUserList()
	 * @method \boolean[] fillIsRealUser()
	 * @method \Bitrix\Main\EO_UserIndex[] getIndexList()
	 * @method \Bitrix\Main\EO_User_Collection getIndexCollection()
	 * @method \Bitrix\Main\EO_UserIndex_Collection fillIndex()
	 * @method \Bitrix\Main\EO_UserCounter[] getCounterList()
	 * @method \Bitrix\Main\EO_User_Collection getCounterCollection()
	 * @method \Bitrix\Main\EO_UserCounter_Collection fillCounter()
	 * @method \Bitrix\Main\EO_UserPhoneAuth[] getPhoneAuthList()
	 * @method \Bitrix\Main\EO_User_Collection getPhoneAuthCollection()
	 * @method \Bitrix\Main\EO_UserPhoneAuth_Collection fillPhoneAuth()
	 * @method \Bitrix\Main\EO_UserGroup_Collection[] getGroupsList()
	 * @method \Bitrix\Main\EO_UserGroup_Collection getGroupsCollection()
	 * @method \Bitrix\Main\EO_UserGroup_Collection fillGroups()
	 * @method \Bitrix\Main\Localization\EO_Language[] getActiveLanguageList()
	 * @method \Bitrix\Main\EO_User_Collection getActiveLanguageCollection()
	 * @method \Bitrix\Main\Localization\EO_Language_Collection fillActiveLanguage()
	 * @method \string[] getNotificationLanguageIdList()
	 * @method \string[] fillNotificationLanguageId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_User $object)
	 * @method bool has(\Bitrix\Main\EO_User $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_User getByPrimary($primary)
	 * @method \Bitrix\Main\EO_User[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_User $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_User_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_User current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_User_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserTable */
		static public $dataClass = '\Bitrix\Main\UserTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_User_Result exec()
	 * @method \Bitrix\Main\EO_User fetchObject()
	 * @method \Bitrix\Main\EO_User_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_User_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_User fetchObject()
	 * @method \Bitrix\Main\EO_User_Collection fetchCollection()
	 */
	class EO_User_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_User createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_User_Collection createCollection()
	 * @method \Bitrix\Main\EO_User wakeUpObject($row)
	 * @method \Bitrix\Main\EO_User_Collection wakeUpCollection($rows)
	 */
	class EO_User_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserAccessTable:main/lib/useraccess.php */
namespace Bitrix\Main {
	/**
	 * EO_UserAccess
	 * @see \Bitrix\Main\UserAccessTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EO_UserAccess setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getProviderId()
	 * @method \Bitrix\Main\EO_UserAccess setProviderId(\string|\Bitrix\Main\DB\SqlExpression $providerId)
	 * @method bool hasProviderId()
	 * @method bool isProviderIdFilled()
	 * @method bool isProviderIdChanged()
	 * @method \string getAccessCode()
	 * @method \Bitrix\Main\EO_UserAccess setAccessCode(\string|\Bitrix\Main\DB\SqlExpression $accessCode)
	 * @method bool hasAccessCode()
	 * @method bool isAccessCodeFilled()
	 * @method bool isAccessCodeChanged()
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
	 * @method \Bitrix\Main\EO_UserAccess set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserAccess reset($fieldName)
	 * @method \Bitrix\Main\EO_UserAccess unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserAccess wakeUp($data)
	 */
	class EO_UserAccess {
		/* @var \Bitrix\Main\UserAccessTable */
		static public $dataClass = '\Bitrix\Main\UserAccessTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserAccess_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getProviderIdList()
	 * @method \string[] getAccessCodeList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserAccess $object)
	 * @method bool has(\Bitrix\Main\EO_UserAccess $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserAccess getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserAccess[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserAccess $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserAccess_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserAccess current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserAccess_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserAccessTable */
		static public $dataClass = '\Bitrix\Main\UserAccessTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserAccess_Result exec()
	 * @method \Bitrix\Main\EO_UserAccess fetchObject()
	 * @method \Bitrix\Main\EO_UserAccess_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserAccess_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserAccess fetchObject()
	 * @method \Bitrix\Main\EO_UserAccess_Collection fetchCollection()
	 */
	class EO_UserAccess_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserAccess createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserAccess_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserAccess wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserAccess_Collection wakeUpCollection($rows)
	 */
	class EO_UserAccess_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserAuthActionTable:main/lib/userauthaction.php */
namespace Bitrix\Main {
	/**
	 * EO_UserAuthAction
	 * @see \Bitrix\Main\UserAuthActionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_UserAuthAction setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EO_UserAuthAction setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\EO_UserAuthAction resetUserId()
	 * @method \Bitrix\Main\EO_UserAuthAction unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getPriority()
	 * @method \Bitrix\Main\EO_UserAuthAction setPriority(\int|\Bitrix\Main\DB\SqlExpression $priority)
	 * @method bool hasPriority()
	 * @method bool isPriorityFilled()
	 * @method bool isPriorityChanged()
	 * @method \int remindActualPriority()
	 * @method \int requirePriority()
	 * @method \Bitrix\Main\EO_UserAuthAction resetPriority()
	 * @method \Bitrix\Main\EO_UserAuthAction unsetPriority()
	 * @method \int fillPriority()
	 * @method \string getAction()
	 * @method \Bitrix\Main\EO_UserAuthAction setAction(\string|\Bitrix\Main\DB\SqlExpression $action)
	 * @method bool hasAction()
	 * @method bool isActionFilled()
	 * @method bool isActionChanged()
	 * @method \string remindActualAction()
	 * @method \string requireAction()
	 * @method \Bitrix\Main\EO_UserAuthAction resetAction()
	 * @method \Bitrix\Main\EO_UserAuthAction unsetAction()
	 * @method \string fillAction()
	 * @method \Bitrix\Main\Type\DateTime getActionDate()
	 * @method \Bitrix\Main\EO_UserAuthAction setActionDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $actionDate)
	 * @method bool hasActionDate()
	 * @method bool isActionDateFilled()
	 * @method bool isActionDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualActionDate()
	 * @method \Bitrix\Main\Type\DateTime requireActionDate()
	 * @method \Bitrix\Main\EO_UserAuthAction resetActionDate()
	 * @method \Bitrix\Main\EO_UserAuthAction unsetActionDate()
	 * @method \Bitrix\Main\Type\DateTime fillActionDate()
	 * @method \string getApplicationId()
	 * @method \Bitrix\Main\EO_UserAuthAction setApplicationId(\string|\Bitrix\Main\DB\SqlExpression $applicationId)
	 * @method bool hasApplicationId()
	 * @method bool isApplicationIdFilled()
	 * @method bool isApplicationIdChanged()
	 * @method \string remindActualApplicationId()
	 * @method \string requireApplicationId()
	 * @method \Bitrix\Main\EO_UserAuthAction resetApplicationId()
	 * @method \Bitrix\Main\EO_UserAuthAction unsetApplicationId()
	 * @method \string fillApplicationId()
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
	 * @method \Bitrix\Main\EO_UserAuthAction set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserAuthAction reset($fieldName)
	 * @method \Bitrix\Main\EO_UserAuthAction unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserAuthAction wakeUp($data)
	 */
	class EO_UserAuthAction {
		/* @var \Bitrix\Main\UserAuthActionTable */
		static public $dataClass = '\Bitrix\Main\UserAuthActionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserAuthAction_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getPriorityList()
	 * @method \int[] fillPriority()
	 * @method \string[] getActionList()
	 * @method \string[] fillAction()
	 * @method \Bitrix\Main\Type\DateTime[] getActionDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillActionDate()
	 * @method \string[] getApplicationIdList()
	 * @method \string[] fillApplicationId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserAuthAction $object)
	 * @method bool has(\Bitrix\Main\EO_UserAuthAction $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserAuthAction getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserAuthAction[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserAuthAction $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserAuthAction_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserAuthAction current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserAuthAction_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserAuthActionTable */
		static public $dataClass = '\Bitrix\Main\UserAuthActionTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserAuthAction_Result exec()
	 * @method \Bitrix\Main\EO_UserAuthAction fetchObject()
	 * @method \Bitrix\Main\EO_UserAuthAction_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserAuthAction_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserAuthAction fetchObject()
	 * @method \Bitrix\Main\EO_UserAuthAction_Collection fetchCollection()
	 */
	class EO_UserAuthAction_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserAuthAction createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserAuthAction_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserAuthAction wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserAuthAction_Collection wakeUpCollection($rows)
	 */
	class EO_UserAuthAction_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserConsent\Internals\AgreementTable:main/lib/userconsent/internals/agreement.php */
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * EO_Agreement
	 * @see \Bitrix\Main\UserConsent\Internals\AgreementTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetCode()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetCode()
	 * @method \string fillCode()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetDateInsert()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \boolean getActive()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetActive()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getName()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetName()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetName()
	 * @method \string fillName()
	 * @method \string getType()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetType()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetType()
	 * @method \string fillType()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetLanguageId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetLanguageId()
	 * @method \string fillLanguageId()
	 * @method \string getDataProvider()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setDataProvider(\string|\Bitrix\Main\DB\SqlExpression $dataProvider)
	 * @method bool hasDataProvider()
	 * @method bool isDataProviderFilled()
	 * @method bool isDataProviderChanged()
	 * @method \string remindActualDataProvider()
	 * @method \string requireDataProvider()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetDataProvider()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetDataProvider()
	 * @method \string fillDataProvider()
	 * @method \string getAgreementText()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setAgreementText(\string|\Bitrix\Main\DB\SqlExpression $agreementText)
	 * @method bool hasAgreementText()
	 * @method bool isAgreementTextFilled()
	 * @method bool isAgreementTextChanged()
	 * @method \string remindActualAgreementText()
	 * @method \string requireAgreementText()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetAgreementText()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetAgreementText()
	 * @method \string fillAgreementText()
	 * @method \string getLabelText()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setLabelText(\string|\Bitrix\Main\DB\SqlExpression $labelText)
	 * @method bool hasLabelText()
	 * @method bool isLabelTextFilled()
	 * @method bool isLabelTextChanged()
	 * @method \string remindActualLabelText()
	 * @method \string requireLabelText()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetLabelText()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetLabelText()
	 * @method \string fillLabelText()
	 * @method \string getSecurityCode()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setSecurityCode(\string|\Bitrix\Main\DB\SqlExpression $securityCode)
	 * @method bool hasSecurityCode()
	 * @method bool isSecurityCodeFilled()
	 * @method bool isSecurityCodeChanged()
	 * @method \string remindActualSecurityCode()
	 * @method \string requireSecurityCode()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetSecurityCode()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetSecurityCode()
	 * @method \string fillSecurityCode()
	 * @method \boolean getUseUrl()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setUseUrl(\boolean|\Bitrix\Main\DB\SqlExpression $useUrl)
	 * @method bool hasUseUrl()
	 * @method bool isUseUrlFilled()
	 * @method bool isUseUrlChanged()
	 * @method \boolean remindActualUseUrl()
	 * @method \boolean requireUseUrl()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetUseUrl()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetUseUrl()
	 * @method \boolean fillUseUrl()
	 * @method \string getUrl()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setUrl(\string|\Bitrix\Main\DB\SqlExpression $url)
	 * @method bool hasUrl()
	 * @method bool isUrlFilled()
	 * @method bool isUrlChanged()
	 * @method \string remindActualUrl()
	 * @method \string requireUrl()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetUrl()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetUrl()
	 * @method \string fillUrl()
	 * @method \boolean getIsAgreementTextHtml()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement setIsAgreementTextHtml(\boolean|\Bitrix\Main\DB\SqlExpression $isAgreementTextHtml)
	 * @method bool hasIsAgreementTextHtml()
	 * @method bool isIsAgreementTextHtmlFilled()
	 * @method bool isIsAgreementTextHtmlChanged()
	 * @method \boolean remindActualIsAgreementTextHtml()
	 * @method \boolean requireIsAgreementTextHtml()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement resetIsAgreementTextHtml()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unsetIsAgreementTextHtml()
	 * @method \boolean fillIsAgreementTextHtml()
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
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement set($fieldName, $value)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement reset($fieldName)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\UserConsent\Internals\EO_Agreement wakeUp($data)
	 */
	class EO_Agreement {
		/* @var \Bitrix\Main\UserConsent\Internals\AgreementTable */
		static public $dataClass = '\Bitrix\Main\UserConsent\Internals\AgreementTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * EO_Agreement_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 * @method \string[] getDataProviderList()
	 * @method \string[] fillDataProvider()
	 * @method \string[] getAgreementTextList()
	 * @method \string[] fillAgreementText()
	 * @method \string[] getLabelTextList()
	 * @method \string[] fillLabelText()
	 * @method \string[] getSecurityCodeList()
	 * @method \string[] fillSecurityCode()
	 * @method \boolean[] getUseUrlList()
	 * @method \boolean[] fillUseUrl()
	 * @method \string[] getUrlList()
	 * @method \string[] fillUrl()
	 * @method \boolean[] getIsAgreementTextHtmlList()
	 * @method \boolean[] fillIsAgreementTextHtml()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\UserConsent\Internals\EO_Agreement $object)
	 * @method bool has(\Bitrix\Main\UserConsent\Internals\EO_Agreement $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement getByPrimary($primary)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement[] getAll()
	 * @method bool remove(\Bitrix\Main\UserConsent\Internals\EO_Agreement $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\UserConsent\Internals\EO_Agreement_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Agreement_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserConsent\Internals\AgreementTable */
		static public $dataClass = '\Bitrix\Main\UserConsent\Internals\AgreementTable';
	}
}
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Agreement_Result exec()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement fetchObject()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Agreement_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement fetchObject()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement_Collection fetchCollection()
	 */
	class EO_Agreement_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement_Collection createCollection()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement wakeUpObject($row)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Agreement_Collection wakeUpCollection($rows)
	 */
	class EO_Agreement_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserConsent\Internals\ConsentTable:main/lib/userconsent/internals/consent.php */
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * EO_Consent
	 * @see \Bitrix\Main\UserConsent\Internals\ConsentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent resetDateInsert()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \int getAgreementId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent setAgreementId(\int|\Bitrix\Main\DB\SqlExpression $agreementId)
	 * @method bool hasAgreementId()
	 * @method bool isAgreementIdFilled()
	 * @method bool isAgreementIdChanged()
	 * @method \int remindActualAgreementId()
	 * @method \int requireAgreementId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent resetAgreementId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unsetAgreementId()
	 * @method \int fillAgreementId()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent resetUserId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getIp()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent setIp(\string|\Bitrix\Main\DB\SqlExpression $ip)
	 * @method bool hasIp()
	 * @method bool isIpFilled()
	 * @method bool isIpChanged()
	 * @method \string remindActualIp()
	 * @method \string requireIp()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent resetIp()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unsetIp()
	 * @method \string fillIp()
	 * @method \string getUrl()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent setUrl(\string|\Bitrix\Main\DB\SqlExpression $url)
	 * @method bool hasUrl()
	 * @method bool isUrlFilled()
	 * @method bool isUrlChanged()
	 * @method \string remindActualUrl()
	 * @method \string requireUrl()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent resetUrl()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unsetUrl()
	 * @method \string fillUrl()
	 * @method \string getOriginId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent setOriginId(\string|\Bitrix\Main\DB\SqlExpression $originId)
	 * @method bool hasOriginId()
	 * @method bool isOriginIdFilled()
	 * @method bool isOriginIdChanged()
	 * @method \string remindActualOriginId()
	 * @method \string requireOriginId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent resetOriginId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unsetOriginId()
	 * @method \string fillOriginId()
	 * @method \string getOriginatorId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent setOriginatorId(\string|\Bitrix\Main\DB\SqlExpression $originatorId)
	 * @method bool hasOriginatorId()
	 * @method bool isOriginatorIdFilled()
	 * @method bool isOriginatorIdChanged()
	 * @method \string remindActualOriginatorId()
	 * @method \string requireOriginatorId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent resetOriginatorId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unsetOriginatorId()
	 * @method \string fillOriginatorId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent resetUser()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection getItems()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection requireItems()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection fillItems()
	 * @method bool hasItems()
	 * @method bool isItemsFilled()
	 * @method bool isItemsChanged()
	 * @method void addToItems(\Bitrix\Main\UserConsent\Internals\EO_UserConsentItem $userConsentItem)
	 * @method void removeFromItems(\Bitrix\Main\UserConsent\Internals\EO_UserConsentItem $userConsentItem)
	 * @method void removeAllItems()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent resetItems()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unsetItems()
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
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent set($fieldName, $value)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent reset($fieldName)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\UserConsent\Internals\EO_Consent wakeUp($data)
	 */
	class EO_Consent {
		/* @var \Bitrix\Main\UserConsent\Internals\ConsentTable */
		static public $dataClass = '\Bitrix\Main\UserConsent\Internals\ConsentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * EO_Consent_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \int[] getAgreementIdList()
	 * @method \int[] fillAgreementId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getIpList()
	 * @method \string[] fillIp()
	 * @method \string[] getUrlList()
	 * @method \string[] fillUrl()
	 * @method \string[] getOriginIdList()
	 * @method \string[] fillOriginId()
	 * @method \string[] getOriginatorIdList()
	 * @method \string[] fillOriginatorId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection[] getItemsList()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection getItemsCollection()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection fillItems()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\UserConsent\Internals\EO_Consent $object)
	 * @method bool has(\Bitrix\Main\UserConsent\Internals\EO_Consent $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent getByPrimary($primary)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent[] getAll()
	 * @method bool remove(\Bitrix\Main\UserConsent\Internals\EO_Consent $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\UserConsent\Internals\EO_Consent_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Consent_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserConsent\Internals\ConsentTable */
		static public $dataClass = '\Bitrix\Main\UserConsent\Internals\ConsentTable';
	}
}
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Consent_Result exec()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent fetchObject()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Consent_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent fetchObject()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent_Collection fetchCollection()
	 */
	class EO_Consent_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent_Collection createCollection()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent wakeUpObject($row)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent_Collection wakeUpCollection($rows)
	 */
	class EO_Consent_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserConsent\Internals\FieldTable:main/lib/userconsent/internals/field.php */
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * EO_Field
	 * @see \Bitrix\Main\UserConsent\Internals\FieldTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getAgreementId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field setAgreementId(\int|\Bitrix\Main\DB\SqlExpression $agreementId)
	 * @method bool hasAgreementId()
	 * @method bool isAgreementIdFilled()
	 * @method bool isAgreementIdChanged()
	 * @method \int remindActualAgreementId()
	 * @method \int requireAgreementId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field resetAgreementId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field unsetAgreementId()
	 * @method \int fillAgreementId()
	 * @method \string getCode()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field resetCode()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field unsetCode()
	 * @method \string fillCode()
	 * @method \string getValue()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field resetValue()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field unsetValue()
	 * @method \string fillValue()
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
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field set($fieldName, $value)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field reset($fieldName)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\UserConsent\Internals\EO_Field wakeUp($data)
	 */
	class EO_Field {
		/* @var \Bitrix\Main\UserConsent\Internals\FieldTable */
		static public $dataClass = '\Bitrix\Main\UserConsent\Internals\FieldTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * EO_Field_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getAgreementIdList()
	 * @method \int[] fillAgreementId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\UserConsent\Internals\EO_Field $object)
	 * @method bool has(\Bitrix\Main\UserConsent\Internals\EO_Field $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field getByPrimary($primary)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field[] getAll()
	 * @method bool remove(\Bitrix\Main\UserConsent\Internals\EO_Field $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\UserConsent\Internals\EO_Field_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Field_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserConsent\Internals\FieldTable */
		static public $dataClass = '\Bitrix\Main\UserConsent\Internals\FieldTable';
	}
}
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Field_Result exec()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field fetchObject()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Field_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field fetchObject()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field_Collection fetchCollection()
	 */
	class EO_Field_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field_Collection createCollection()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field wakeUpObject($row)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Field_Collection wakeUpCollection($rows)
	 */
	class EO_Field_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserConsent\Internals\UserConsentItemTable:main/lib/userconsent/internals/userconsentitem.php */
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * EO_UserConsentItem
	 * @see \Bitrix\Main\UserConsent\Internals\UserConsentItemTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserConsentId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem setUserConsentId(\int|\Bitrix\Main\DB\SqlExpression $userConsentId)
	 * @method bool hasUserConsentId()
	 * @method bool isUserConsentIdFilled()
	 * @method bool isUserConsentIdChanged()
	 * @method \int remindActualUserConsentId()
	 * @method \int requireUserConsentId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem resetUserConsentId()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem unsetUserConsentId()
	 * @method \int fillUserConsentId()
	 * @method \string getValue()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem resetValue()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem unsetValue()
	 * @method \string fillValue()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent getUserConsent()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent remindActualUserConsent()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent requireUserConsent()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem setUserConsent(\Bitrix\Main\UserConsent\Internals\EO_Consent $object)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem resetUserConsent()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem unsetUserConsent()
	 * @method bool hasUserConsent()
	 * @method bool isUserConsentFilled()
	 * @method bool isUserConsentChanged()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent fillUserConsent()
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
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem set($fieldName, $value)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem reset($fieldName)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem wakeUp($data)
	 */
	class EO_UserConsentItem {
		/* @var \Bitrix\Main\UserConsent\Internals\UserConsentItemTable */
		static public $dataClass = '\Bitrix\Main\UserConsent\Internals\UserConsentItemTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * EO_UserConsentItem_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserConsentIdList()
	 * @method \int[] fillUserConsentId()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent[] getUserConsentList()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection getUserConsentCollection()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_Consent_Collection fillUserConsent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\UserConsent\Internals\EO_UserConsentItem $object)
	 * @method bool has(\Bitrix\Main\UserConsent\Internals\EO_UserConsentItem $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem getByPrimary($primary)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem[] getAll()
	 * @method bool remove(\Bitrix\Main\UserConsent\Internals\EO_UserConsentItem $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserConsentItem_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserConsent\Internals\UserConsentItemTable */
		static public $dataClass = '\Bitrix\Main\UserConsent\Internals\UserConsentItemTable';
	}
}
namespace Bitrix\Main\UserConsent\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserConsentItem_Result exec()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem fetchObject()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserConsentItem_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem fetchObject()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection fetchCollection()
	 */
	class EO_UserConsentItem_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection createCollection()
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem wakeUpObject($row)
	 * @method \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection wakeUpCollection($rows)
	 */
	class EO_UserConsentItem_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserCounterTable:main/lib/usercounter.php */
namespace Bitrix\Main {
	/**
	 * EO_UserCounter
	 * @see \Bitrix\Main\UserCounterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EO_UserCounter setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Main\EO_UserCounter setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Main\EO_UserCounter setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string getTag()
	 * @method \Bitrix\Main\EO_UserCounter setTag(\string|\Bitrix\Main\DB\SqlExpression $tag)
	 * @method bool hasTag()
	 * @method bool isTagFilled()
	 * @method bool isTagChanged()
	 * @method \string remindActualTag()
	 * @method \string requireTag()
	 * @method \Bitrix\Main\EO_UserCounter resetTag()
	 * @method \Bitrix\Main\EO_UserCounter unsetTag()
	 * @method \string fillTag()
	 * @method \string getParams()
	 * @method \Bitrix\Main\EO_UserCounter setParams(\string|\Bitrix\Main\DB\SqlExpression $params)
	 * @method bool hasParams()
	 * @method bool isParamsFilled()
	 * @method bool isParamsChanged()
	 * @method \string remindActualParams()
	 * @method \string requireParams()
	 * @method \Bitrix\Main\EO_UserCounter resetParams()
	 * @method \Bitrix\Main\EO_UserCounter unsetParams()
	 * @method \string fillParams()
	 * @method \string getSent()
	 * @method \Bitrix\Main\EO_UserCounter setSent(\string|\Bitrix\Main\DB\SqlExpression $sent)
	 * @method bool hasSent()
	 * @method bool isSentFilled()
	 * @method bool isSentChanged()
	 * @method \string remindActualSent()
	 * @method \string requireSent()
	 * @method \Bitrix\Main\EO_UserCounter resetSent()
	 * @method \Bitrix\Main\EO_UserCounter unsetSent()
	 * @method \string fillSent()
	 * @method \int getCnt()
	 * @method \Bitrix\Main\EO_UserCounter setCnt(\int|\Bitrix\Main\DB\SqlExpression $cnt)
	 * @method bool hasCnt()
	 * @method bool isCntFilled()
	 * @method bool isCntChanged()
	 * @method \int remindActualCnt()
	 * @method \int requireCnt()
	 * @method \Bitrix\Main\EO_UserCounter resetCnt()
	 * @method \Bitrix\Main\EO_UserCounter unsetCnt()
	 * @method \int fillCnt()
	 * @method \Bitrix\Main\Type\DateTime getLastDate()
	 * @method \Bitrix\Main\EO_UserCounter setLastDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastDate)
	 * @method bool hasLastDate()
	 * @method bool isLastDateFilled()
	 * @method bool isLastDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastDate()
	 * @method \Bitrix\Main\Type\DateTime requireLastDate()
	 * @method \Bitrix\Main\EO_UserCounter resetLastDate()
	 * @method \Bitrix\Main\EO_UserCounter unsetLastDate()
	 * @method \Bitrix\Main\Type\DateTime fillLastDate()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Main\EO_UserCounter setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Main\EO_UserCounter resetTimestampX()
	 * @method \Bitrix\Main\EO_UserCounter unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\EO_UserCounter setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\EO_UserCounter resetUser()
	 * @method \Bitrix\Main\EO_UserCounter unsetUser()
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
	 * @method \Bitrix\Main\EO_UserCounter set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserCounter reset($fieldName)
	 * @method \Bitrix\Main\EO_UserCounter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserCounter wakeUp($data)
	 */
	class EO_UserCounter {
		/* @var \Bitrix\Main\UserCounterTable */
		static public $dataClass = '\Bitrix\Main\UserCounterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserCounter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] getTagList()
	 * @method \string[] fillTag()
	 * @method \string[] getParamsList()
	 * @method \string[] fillParams()
	 * @method \string[] getSentList()
	 * @method \string[] fillSent()
	 * @method \int[] getCntList()
	 * @method \int[] fillCnt()
	 * @method \Bitrix\Main\Type\DateTime[] getLastDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastDate()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\EO_UserCounter_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserCounter $object)
	 * @method bool has(\Bitrix\Main\EO_UserCounter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserCounter getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserCounter[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserCounter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserCounter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserCounter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserCounter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserCounterTable */
		static public $dataClass = '\Bitrix\Main\UserCounterTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserCounter_Result exec()
	 * @method \Bitrix\Main\EO_UserCounter fetchObject()
	 * @method \Bitrix\Main\EO_UserCounter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserCounter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserCounter fetchObject()
	 * @method \Bitrix\Main\EO_UserCounter_Collection fetchCollection()
	 */
	class EO_UserCounter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserCounter createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserCounter_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserCounter wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserCounter_Collection wakeUpCollection($rows)
	 */
	class EO_UserCounter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserField\Access\Permission\UserFieldPermissionTable:main/lib/userfield/access/permission/userfieldpermissiontable.php */
namespace Bitrix\Main\UserField\Access\Permission {
	/**
	 * UserFieldPermission
	 * @see \Bitrix\Main\UserField\Access\Permission\UserFieldPermissionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEntityTypeId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission setEntityTypeId(\int|\Bitrix\Main\DB\SqlExpression $entityTypeId)
	 * @method bool hasEntityTypeId()
	 * @method bool isEntityTypeIdFilled()
	 * @method bool isEntityTypeIdChanged()
	 * @method \int remindActualEntityTypeId()
	 * @method \int requireEntityTypeId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission resetEntityTypeId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission unsetEntityTypeId()
	 * @method \int fillEntityTypeId()
	 * @method \int getUserFieldId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission setUserFieldId(\int|\Bitrix\Main\DB\SqlExpression $userFieldId)
	 * @method bool hasUserFieldId()
	 * @method bool isUserFieldIdFilled()
	 * @method bool isUserFieldIdChanged()
	 * @method \int remindActualUserFieldId()
	 * @method \int requireUserFieldId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission resetUserFieldId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission unsetUserFieldId()
	 * @method \int fillUserFieldId()
	 * @method \string getAccessCode()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission setAccessCode(\string|\Bitrix\Main\DB\SqlExpression $accessCode)
	 * @method bool hasAccessCode()
	 * @method bool isAccessCodeFilled()
	 * @method bool isAccessCodeChanged()
	 * @method \string remindActualAccessCode()
	 * @method \string requireAccessCode()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission resetAccessCode()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission unsetAccessCode()
	 * @method \string fillAccessCode()
	 * @method \string getPermissionId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission setPermissionId(\string|\Bitrix\Main\DB\SqlExpression $permissionId)
	 * @method bool hasPermissionId()
	 * @method bool isPermissionIdFilled()
	 * @method bool isPermissionIdChanged()
	 * @method \string remindActualPermissionId()
	 * @method \string requirePermissionId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission resetPermissionId()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission unsetPermissionId()
	 * @method \string fillPermissionId()
	 * @method \int getValue()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission setValue(\int|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \int remindActualValue()
	 * @method \int requireValue()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission resetValue()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission unsetValue()
	 * @method \int fillValue()
	 * @method \Bitrix\Main\EO_UserField getUserField()
	 * @method \Bitrix\Main\EO_UserField remindActualUserField()
	 * @method \Bitrix\Main\EO_UserField requireUserField()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission setUserField(\Bitrix\Main\EO_UserField $object)
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission resetUserField()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission unsetUserField()
	 * @method bool hasUserField()
	 * @method bool isUserFieldFilled()
	 * @method bool isUserFieldChanged()
	 * @method \Bitrix\Main\EO_UserField fillUserField()
	 * @method \Bitrix\Main\EO_UserAccess getUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess remindActualUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess requireUserAccess()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission setUserAccess(\Bitrix\Main\EO_UserAccess $object)
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission resetUserAccess()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission unsetUserAccess()
	 * @method bool hasUserAccess()
	 * @method bool isUserAccessFilled()
	 * @method bool isUserAccessChanged()
	 * @method \Bitrix\Main\EO_UserAccess fillUserAccess()
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
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission set($fieldName, $value)
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission reset($fieldName)
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\UserField\Access\Permission\UserFieldPermission wakeUp($data)
	 */
	class EO_UserFieldPermission {
		/* @var \Bitrix\Main\UserField\Access\Permission\UserFieldPermissionTable */
		static public $dataClass = '\Bitrix\Main\UserField\Access\Permission\UserFieldPermissionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main\UserField\Access\Permission {
	/**
	 * EO_UserFieldPermission_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEntityTypeIdList()
	 * @method \int[] fillEntityTypeId()
	 * @method \int[] getUserFieldIdList()
	 * @method \int[] fillUserFieldId()
	 * @method \string[] getAccessCodeList()
	 * @method \string[] fillAccessCode()
	 * @method \string[] getPermissionIdList()
	 * @method \string[] fillPermissionId()
	 * @method \int[] getValueList()
	 * @method \int[] fillValue()
	 * @method \Bitrix\Main\EO_UserField[] getUserFieldList()
	 * @method \Bitrix\Main\UserField\Access\Permission\EO_UserFieldPermission_Collection getUserFieldCollection()
	 * @method \Bitrix\Main\EO_UserField_Collection fillUserField()
	 * @method \Bitrix\Main\EO_UserAccess[] getUserAccessList()
	 * @method \Bitrix\Main\UserField\Access\Permission\EO_UserFieldPermission_Collection getUserAccessCollection()
	 * @method \Bitrix\Main\EO_UserAccess_Collection fillUserAccess()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\UserField\Access\Permission\UserFieldPermission $object)
	 * @method bool has(\Bitrix\Main\UserField\Access\Permission\UserFieldPermission $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission getByPrimary($primary)
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission[] getAll()
	 * @method bool remove(\Bitrix\Main\UserField\Access\Permission\UserFieldPermission $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\UserField\Access\Permission\EO_UserFieldPermission_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserFieldPermission_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserField\Access\Permission\UserFieldPermissionTable */
		static public $dataClass = '\Bitrix\Main\UserField\Access\Permission\UserFieldPermissionTable';
	}
}
namespace Bitrix\Main\UserField\Access\Permission {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserFieldPermission_Result exec()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission fetchObject()
	 * @method \Bitrix\Main\UserField\Access\Permission\EO_UserFieldPermission_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserFieldPermission_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission fetchObject()
	 * @method \Bitrix\Main\UserField\Access\Permission\EO_UserFieldPermission_Collection fetchCollection()
	 */
	class EO_UserFieldPermission_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\UserField\Access\Permission\EO_UserFieldPermission_Collection createCollection()
	 * @method \Bitrix\Main\UserField\Access\Permission\UserFieldPermission wakeUpObject($row)
	 * @method \Bitrix\Main\UserField\Access\Permission\EO_UserFieldPermission_Collection wakeUpCollection($rows)
	 */
	class EO_UserFieldPermission_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserFieldTable:main/lib/userfield.php */
namespace Bitrix\Main {
	/**
	 * EO_UserField
	 * @see \Bitrix\Main\UserFieldTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_UserField setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEntityId()
	 * @method \Bitrix\Main\EO_UserField setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string remindActualEntityId()
	 * @method \string requireEntityId()
	 * @method \Bitrix\Main\EO_UserField resetEntityId()
	 * @method \Bitrix\Main\EO_UserField unsetEntityId()
	 * @method \string fillEntityId()
	 * @method \string getFieldName()
	 * @method \Bitrix\Main\EO_UserField setFieldName(\string|\Bitrix\Main\DB\SqlExpression $fieldName)
	 * @method bool hasFieldName()
	 * @method bool isFieldNameFilled()
	 * @method bool isFieldNameChanged()
	 * @method \string remindActualFieldName()
	 * @method \string requireFieldName()
	 * @method \Bitrix\Main\EO_UserField resetFieldName()
	 * @method \Bitrix\Main\EO_UserField unsetFieldName()
	 * @method \string fillFieldName()
	 * @method \string getUserTypeId()
	 * @method \Bitrix\Main\EO_UserField setUserTypeId(\string|\Bitrix\Main\DB\SqlExpression $userTypeId)
	 * @method bool hasUserTypeId()
	 * @method bool isUserTypeIdFilled()
	 * @method bool isUserTypeIdChanged()
	 * @method \string remindActualUserTypeId()
	 * @method \string requireUserTypeId()
	 * @method \Bitrix\Main\EO_UserField resetUserTypeId()
	 * @method \Bitrix\Main\EO_UserField unsetUserTypeId()
	 * @method \string fillUserTypeId()
	 * @method \string getXmlId()
	 * @method \Bitrix\Main\EO_UserField setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Main\EO_UserField resetXmlId()
	 * @method \Bitrix\Main\EO_UserField unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \int getSort()
	 * @method \Bitrix\Main\EO_UserField setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Main\EO_UserField resetSort()
	 * @method \Bitrix\Main\EO_UserField unsetSort()
	 * @method \int fillSort()
	 * @method \boolean getMultiple()
	 * @method \Bitrix\Main\EO_UserField setMultiple(\boolean|\Bitrix\Main\DB\SqlExpression $multiple)
	 * @method bool hasMultiple()
	 * @method bool isMultipleFilled()
	 * @method bool isMultipleChanged()
	 * @method \boolean remindActualMultiple()
	 * @method \boolean requireMultiple()
	 * @method \Bitrix\Main\EO_UserField resetMultiple()
	 * @method \Bitrix\Main\EO_UserField unsetMultiple()
	 * @method \boolean fillMultiple()
	 * @method \boolean getMandatory()
	 * @method \Bitrix\Main\EO_UserField setMandatory(\boolean|\Bitrix\Main\DB\SqlExpression $mandatory)
	 * @method bool hasMandatory()
	 * @method bool isMandatoryFilled()
	 * @method bool isMandatoryChanged()
	 * @method \boolean remindActualMandatory()
	 * @method \boolean requireMandatory()
	 * @method \Bitrix\Main\EO_UserField resetMandatory()
	 * @method \Bitrix\Main\EO_UserField unsetMandatory()
	 * @method \boolean fillMandatory()
	 * @method \boolean getShowFilter()
	 * @method \Bitrix\Main\EO_UserField setShowFilter(\boolean|\Bitrix\Main\DB\SqlExpression $showFilter)
	 * @method bool hasShowFilter()
	 * @method bool isShowFilterFilled()
	 * @method bool isShowFilterChanged()
	 * @method \boolean remindActualShowFilter()
	 * @method \boolean requireShowFilter()
	 * @method \Bitrix\Main\EO_UserField resetShowFilter()
	 * @method \Bitrix\Main\EO_UserField unsetShowFilter()
	 * @method \boolean fillShowFilter()
	 * @method \boolean getShowInList()
	 * @method \Bitrix\Main\EO_UserField setShowInList(\boolean|\Bitrix\Main\DB\SqlExpression $showInList)
	 * @method bool hasShowInList()
	 * @method bool isShowInListFilled()
	 * @method bool isShowInListChanged()
	 * @method \boolean remindActualShowInList()
	 * @method \boolean requireShowInList()
	 * @method \Bitrix\Main\EO_UserField resetShowInList()
	 * @method \Bitrix\Main\EO_UserField unsetShowInList()
	 * @method \boolean fillShowInList()
	 * @method \boolean getEditInList()
	 * @method \Bitrix\Main\EO_UserField setEditInList(\boolean|\Bitrix\Main\DB\SqlExpression $editInList)
	 * @method bool hasEditInList()
	 * @method bool isEditInListFilled()
	 * @method bool isEditInListChanged()
	 * @method \boolean remindActualEditInList()
	 * @method \boolean requireEditInList()
	 * @method \Bitrix\Main\EO_UserField resetEditInList()
	 * @method \Bitrix\Main\EO_UserField unsetEditInList()
	 * @method \boolean fillEditInList()
	 * @method \boolean getIsSearchable()
	 * @method \Bitrix\Main\EO_UserField setIsSearchable(\boolean|\Bitrix\Main\DB\SqlExpression $isSearchable)
	 * @method bool hasIsSearchable()
	 * @method bool isIsSearchableFilled()
	 * @method bool isIsSearchableChanged()
	 * @method \boolean remindActualIsSearchable()
	 * @method \boolean requireIsSearchable()
	 * @method \Bitrix\Main\EO_UserField resetIsSearchable()
	 * @method \Bitrix\Main\EO_UserField unsetIsSearchable()
	 * @method \boolean fillIsSearchable()
	 * @method \string getSettings()
	 * @method \Bitrix\Main\EO_UserField setSettings(\string|\Bitrix\Main\DB\SqlExpression $settings)
	 * @method bool hasSettings()
	 * @method bool isSettingsFilled()
	 * @method bool isSettingsChanged()
	 * @method \string remindActualSettings()
	 * @method \string requireSettings()
	 * @method \Bitrix\Main\EO_UserField resetSettings()
	 * @method \Bitrix\Main\EO_UserField unsetSettings()
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
	 * @method \Bitrix\Main\EO_UserField set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserField reset($fieldName)
	 * @method \Bitrix\Main\EO_UserField unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserField wakeUp($data)
	 */
	class EO_UserField {
		/* @var \Bitrix\Main\UserFieldTable */
		static public $dataClass = '\Bitrix\Main\UserFieldTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserField_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEntityIdList()
	 * @method \string[] fillEntityId()
	 * @method \string[] getFieldNameList()
	 * @method \string[] fillFieldName()
	 * @method \string[] getUserTypeIdList()
	 * @method \string[] fillUserTypeId()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \boolean[] getMultipleList()
	 * @method \boolean[] fillMultiple()
	 * @method \boolean[] getMandatoryList()
	 * @method \boolean[] fillMandatory()
	 * @method \boolean[] getShowFilterList()
	 * @method \boolean[] fillShowFilter()
	 * @method \boolean[] getShowInListList()
	 * @method \boolean[] fillShowInList()
	 * @method \boolean[] getEditInListList()
	 * @method \boolean[] fillEditInList()
	 * @method \boolean[] getIsSearchableList()
	 * @method \boolean[] fillIsSearchable()
	 * @method \string[] getSettingsList()
	 * @method \string[] fillSettings()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserField $object)
	 * @method bool has(\Bitrix\Main\EO_UserField $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserField getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserField[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserField $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserField_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserField current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserField_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserFieldTable */
		static public $dataClass = '\Bitrix\Main\UserFieldTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserField_Result exec()
	 * @method \Bitrix\Main\EO_UserField fetchObject()
	 * @method \Bitrix\Main\EO_UserField_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserField_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserField fetchObject()
	 * @method \Bitrix\Main\EO_UserField_Collection fetchCollection()
	 */
	class EO_UserField_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserField createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserField_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserField wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserField_Collection wakeUpCollection($rows)
	 */
	class EO_UserField_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserFieldConfirmTable:main/lib/userfieldconfirm.php */
namespace Bitrix\Main {
	/**
	 * EO_UserFieldConfirm
	 * @see \Bitrix\Main\UserFieldConfirmTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_UserFieldConfirm setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EO_UserFieldConfirm setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\EO_UserFieldConfirm resetUserId()
	 * @method \Bitrix\Main\EO_UserFieldConfirm unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\Type\DateTime getDateChange()
	 * @method \Bitrix\Main\EO_UserFieldConfirm setDateChange(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateChange)
	 * @method bool hasDateChange()
	 * @method bool isDateChangeFilled()
	 * @method bool isDateChangeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateChange()
	 * @method \Bitrix\Main\Type\DateTime requireDateChange()
	 * @method \Bitrix\Main\EO_UserFieldConfirm resetDateChange()
	 * @method \Bitrix\Main\EO_UserFieldConfirm unsetDateChange()
	 * @method \Bitrix\Main\Type\DateTime fillDateChange()
	 * @method \string getField()
	 * @method \Bitrix\Main\EO_UserFieldConfirm setField(\string|\Bitrix\Main\DB\SqlExpression $field)
	 * @method bool hasField()
	 * @method bool isFieldFilled()
	 * @method bool isFieldChanged()
	 * @method \string remindActualField()
	 * @method \string requireField()
	 * @method \Bitrix\Main\EO_UserFieldConfirm resetField()
	 * @method \Bitrix\Main\EO_UserFieldConfirm unsetField()
	 * @method \string fillField()
	 * @method \string getFieldValue()
	 * @method \Bitrix\Main\EO_UserFieldConfirm setFieldValue(\string|\Bitrix\Main\DB\SqlExpression $fieldValue)
	 * @method bool hasFieldValue()
	 * @method bool isFieldValueFilled()
	 * @method bool isFieldValueChanged()
	 * @method \string remindActualFieldValue()
	 * @method \string requireFieldValue()
	 * @method \Bitrix\Main\EO_UserFieldConfirm resetFieldValue()
	 * @method \Bitrix\Main\EO_UserFieldConfirm unsetFieldValue()
	 * @method \string fillFieldValue()
	 * @method \string getConfirmCode()
	 * @method \Bitrix\Main\EO_UserFieldConfirm setConfirmCode(\string|\Bitrix\Main\DB\SqlExpression $confirmCode)
	 * @method bool hasConfirmCode()
	 * @method bool isConfirmCodeFilled()
	 * @method bool isConfirmCodeChanged()
	 * @method \string remindActualConfirmCode()
	 * @method \string requireConfirmCode()
	 * @method \Bitrix\Main\EO_UserFieldConfirm resetConfirmCode()
	 * @method \Bitrix\Main\EO_UserFieldConfirm unsetConfirmCode()
	 * @method \string fillConfirmCode()
	 * @method \int getAttempts()
	 * @method \Bitrix\Main\EO_UserFieldConfirm setAttempts(\int|\Bitrix\Main\DB\SqlExpression $attempts)
	 * @method bool hasAttempts()
	 * @method bool isAttemptsFilled()
	 * @method bool isAttemptsChanged()
	 * @method \int remindActualAttempts()
	 * @method \int requireAttempts()
	 * @method \Bitrix\Main\EO_UserFieldConfirm resetAttempts()
	 * @method \Bitrix\Main\EO_UserFieldConfirm unsetAttempts()
	 * @method \int fillAttempts()
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
	 * @method \Bitrix\Main\EO_UserFieldConfirm set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserFieldConfirm reset($fieldName)
	 * @method \Bitrix\Main\EO_UserFieldConfirm unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserFieldConfirm wakeUp($data)
	 */
	class EO_UserFieldConfirm {
		/* @var \Bitrix\Main\UserFieldConfirmTable */
		static public $dataClass = '\Bitrix\Main\UserFieldConfirmTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserFieldConfirm_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateChangeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateChange()
	 * @method \string[] getFieldList()
	 * @method \string[] fillField()
	 * @method \string[] getFieldValueList()
	 * @method \string[] fillFieldValue()
	 * @method \string[] getConfirmCodeList()
	 * @method \string[] fillConfirmCode()
	 * @method \int[] getAttemptsList()
	 * @method \int[] fillAttempts()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserFieldConfirm $object)
	 * @method bool has(\Bitrix\Main\EO_UserFieldConfirm $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserFieldConfirm getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserFieldConfirm[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserFieldConfirm $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserFieldConfirm_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserFieldConfirm current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserFieldConfirm_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserFieldConfirmTable */
		static public $dataClass = '\Bitrix\Main\UserFieldConfirmTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserFieldConfirm_Result exec()
	 * @method \Bitrix\Main\EO_UserFieldConfirm fetchObject()
	 * @method \Bitrix\Main\EO_UserFieldConfirm_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserFieldConfirm_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserFieldConfirm fetchObject()
	 * @method \Bitrix\Main\EO_UserFieldConfirm_Collection fetchCollection()
	 */
	class EO_UserFieldConfirm_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserFieldConfirm createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserFieldConfirm_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserFieldConfirm wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserFieldConfirm_Collection wakeUpCollection($rows)
	 */
	class EO_UserFieldConfirm_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserFieldLangTable:main/lib/userfieldlangtable.php */
namespace Bitrix\Main {
	/**
	 * EO_UserFieldLang
	 * @see \Bitrix\Main\UserFieldLangTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserFieldId()
	 * @method \Bitrix\Main\EO_UserFieldLang setUserFieldId(\int|\Bitrix\Main\DB\SqlExpression $userFieldId)
	 * @method bool hasUserFieldId()
	 * @method bool isUserFieldIdFilled()
	 * @method bool isUserFieldIdChanged()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Main\EO_UserFieldLang setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string getEditFormLabel()
	 * @method \Bitrix\Main\EO_UserFieldLang setEditFormLabel(\string|\Bitrix\Main\DB\SqlExpression $editFormLabel)
	 * @method bool hasEditFormLabel()
	 * @method bool isEditFormLabelFilled()
	 * @method bool isEditFormLabelChanged()
	 * @method \string remindActualEditFormLabel()
	 * @method \string requireEditFormLabel()
	 * @method \Bitrix\Main\EO_UserFieldLang resetEditFormLabel()
	 * @method \Bitrix\Main\EO_UserFieldLang unsetEditFormLabel()
	 * @method \string fillEditFormLabel()
	 * @method \string getListColumnLabel()
	 * @method \Bitrix\Main\EO_UserFieldLang setListColumnLabel(\string|\Bitrix\Main\DB\SqlExpression $listColumnLabel)
	 * @method bool hasListColumnLabel()
	 * @method bool isListColumnLabelFilled()
	 * @method bool isListColumnLabelChanged()
	 * @method \string remindActualListColumnLabel()
	 * @method \string requireListColumnLabel()
	 * @method \Bitrix\Main\EO_UserFieldLang resetListColumnLabel()
	 * @method \Bitrix\Main\EO_UserFieldLang unsetListColumnLabel()
	 * @method \string fillListColumnLabel()
	 * @method \string getListFilterLabel()
	 * @method \Bitrix\Main\EO_UserFieldLang setListFilterLabel(\string|\Bitrix\Main\DB\SqlExpression $listFilterLabel)
	 * @method bool hasListFilterLabel()
	 * @method bool isListFilterLabelFilled()
	 * @method bool isListFilterLabelChanged()
	 * @method \string remindActualListFilterLabel()
	 * @method \string requireListFilterLabel()
	 * @method \Bitrix\Main\EO_UserFieldLang resetListFilterLabel()
	 * @method \Bitrix\Main\EO_UserFieldLang unsetListFilterLabel()
	 * @method \string fillListFilterLabel()
	 * @method \string getErrorMessage()
	 * @method \Bitrix\Main\EO_UserFieldLang setErrorMessage(\string|\Bitrix\Main\DB\SqlExpression $errorMessage)
	 * @method bool hasErrorMessage()
	 * @method bool isErrorMessageFilled()
	 * @method bool isErrorMessageChanged()
	 * @method \string remindActualErrorMessage()
	 * @method \string requireErrorMessage()
	 * @method \Bitrix\Main\EO_UserFieldLang resetErrorMessage()
	 * @method \Bitrix\Main\EO_UserFieldLang unsetErrorMessage()
	 * @method \string fillErrorMessage()
	 * @method \string getHelpMessage()
	 * @method \Bitrix\Main\EO_UserFieldLang setHelpMessage(\string|\Bitrix\Main\DB\SqlExpression $helpMessage)
	 * @method bool hasHelpMessage()
	 * @method bool isHelpMessageFilled()
	 * @method bool isHelpMessageChanged()
	 * @method \string remindActualHelpMessage()
	 * @method \string requireHelpMessage()
	 * @method \Bitrix\Main\EO_UserFieldLang resetHelpMessage()
	 * @method \Bitrix\Main\EO_UserFieldLang unsetHelpMessage()
	 * @method \string fillHelpMessage()
	 * @method \Bitrix\Main\EO_UserField getUserField()
	 * @method \Bitrix\Main\EO_UserField remindActualUserField()
	 * @method \Bitrix\Main\EO_UserField requireUserField()
	 * @method \Bitrix\Main\EO_UserFieldLang setUserField(\Bitrix\Main\EO_UserField $object)
	 * @method \Bitrix\Main\EO_UserFieldLang resetUserField()
	 * @method \Bitrix\Main\EO_UserFieldLang unsetUserField()
	 * @method bool hasUserField()
	 * @method bool isUserFieldFilled()
	 * @method bool isUserFieldChanged()
	 * @method \Bitrix\Main\EO_UserField fillUserField()
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
	 * @method \Bitrix\Main\EO_UserFieldLang set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserFieldLang reset($fieldName)
	 * @method \Bitrix\Main\EO_UserFieldLang unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserFieldLang wakeUp($data)
	 */
	class EO_UserFieldLang {
		/* @var \Bitrix\Main\UserFieldLangTable */
		static public $dataClass = '\Bitrix\Main\UserFieldLangTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserFieldLang_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserFieldIdList()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] getEditFormLabelList()
	 * @method \string[] fillEditFormLabel()
	 * @method \string[] getListColumnLabelList()
	 * @method \string[] fillListColumnLabel()
	 * @method \string[] getListFilterLabelList()
	 * @method \string[] fillListFilterLabel()
	 * @method \string[] getErrorMessageList()
	 * @method \string[] fillErrorMessage()
	 * @method \string[] getHelpMessageList()
	 * @method \string[] fillHelpMessage()
	 * @method \Bitrix\Main\EO_UserField[] getUserFieldList()
	 * @method \Bitrix\Main\EO_UserFieldLang_Collection getUserFieldCollection()
	 * @method \Bitrix\Main\EO_UserField_Collection fillUserField()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserFieldLang $object)
	 * @method bool has(\Bitrix\Main\EO_UserFieldLang $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserFieldLang getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserFieldLang[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserFieldLang $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserFieldLang_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserFieldLang current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserFieldLang_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserFieldLangTable */
		static public $dataClass = '\Bitrix\Main\UserFieldLangTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserFieldLang_Result exec()
	 * @method \Bitrix\Main\EO_UserFieldLang fetchObject()
	 * @method \Bitrix\Main\EO_UserFieldLang_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserFieldLang_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserFieldLang fetchObject()
	 * @method \Bitrix\Main\EO_UserFieldLang_Collection fetchCollection()
	 */
	class EO_UserFieldLang_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserFieldLang createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserFieldLang_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserFieldLang wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserFieldLang_Collection wakeUpCollection($rows)
	 */
	class EO_UserFieldLang_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserGroupTable:main/lib/usergroup.php */
namespace Bitrix\Main {
	/**
	 * EO_UserGroup
	 * @see \Bitrix\Main\UserGroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EO_UserGroup setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\EO_UserGroup setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\EO_UserGroup resetUser()
	 * @method \Bitrix\Main\EO_UserGroup unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \int getGroupId()
	 * @method \Bitrix\Main\EO_UserGroup setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \Bitrix\Main\EO_Group getGroup()
	 * @method \Bitrix\Main\EO_Group remindActualGroup()
	 * @method \Bitrix\Main\EO_Group requireGroup()
	 * @method \Bitrix\Main\EO_UserGroup setGroup(\Bitrix\Main\EO_Group $object)
	 * @method \Bitrix\Main\EO_UserGroup resetGroup()
	 * @method \Bitrix\Main\EO_UserGroup unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Main\EO_Group fillGroup()
	 * @method \Bitrix\Main\Type\DateTime getDateActiveFrom()
	 * @method \Bitrix\Main\EO_UserGroup setDateActiveFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateActiveFrom)
	 * @method bool hasDateActiveFrom()
	 * @method bool isDateActiveFromFilled()
	 * @method bool isDateActiveFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime requireDateActiveFrom()
	 * @method \Bitrix\Main\EO_UserGroup resetDateActiveFrom()
	 * @method \Bitrix\Main\EO_UserGroup unsetDateActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime fillDateActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime getDateActiveTo()
	 * @method \Bitrix\Main\EO_UserGroup setDateActiveTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateActiveTo)
	 * @method bool hasDateActiveTo()
	 * @method bool isDateActiveToFilled()
	 * @method bool isDateActiveToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateActiveTo()
	 * @method \Bitrix\Main\Type\DateTime requireDateActiveTo()
	 * @method \Bitrix\Main\EO_UserGroup resetDateActiveTo()
	 * @method \Bitrix\Main\EO_UserGroup unsetDateActiveTo()
	 * @method \Bitrix\Main\Type\DateTime fillDateActiveTo()
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
	 * @method \Bitrix\Main\EO_UserGroup set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserGroup reset($fieldName)
	 * @method \Bitrix\Main\EO_UserGroup unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserGroup wakeUp($data)
	 */
	class EO_UserGroup {
		/* @var \Bitrix\Main\UserGroupTable */
		static public $dataClass = '\Bitrix\Main\UserGroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserGroup_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\EO_UserGroup_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \int[] getGroupIdList()
	 * @method \Bitrix\Main\EO_Group[] getGroupList()
	 * @method \Bitrix\Main\EO_UserGroup_Collection getGroupCollection()
	 * @method \Bitrix\Main\EO_Group_Collection fillGroup()
	 * @method \Bitrix\Main\Type\DateTime[] getDateActiveFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getDateActiveToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateActiveTo()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserGroup $object)
	 * @method bool has(\Bitrix\Main\EO_UserGroup $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserGroup getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserGroup[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserGroup $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserGroup_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserGroup current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserGroup_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserGroupTable */
		static public $dataClass = '\Bitrix\Main\UserGroupTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserGroup_Result exec()
	 * @method \Bitrix\Main\EO_UserGroup fetchObject()
	 * @method \Bitrix\Main\EO_UserGroup_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserGroup_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserGroup fetchObject()
	 * @method \Bitrix\Main\EO_UserGroup_Collection fetchCollection()
	 */
	class EO_UserGroup_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserGroup createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserGroup_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserGroup wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserGroup_Collection wakeUpCollection($rows)
	 */
	class EO_UserGroup_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserIndexTable:main/lib/userindex.php */
namespace Bitrix\Main {
	/**
	 * EO_UserIndex
	 * @see \Bitrix\Main\UserIndexTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EO_UserIndex setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getSearchUserContent()
	 * @method \Bitrix\Main\EO_UserIndex setSearchUserContent(\string|\Bitrix\Main\DB\SqlExpression $searchUserContent)
	 * @method bool hasSearchUserContent()
	 * @method bool isSearchUserContentFilled()
	 * @method bool isSearchUserContentChanged()
	 * @method \string remindActualSearchUserContent()
	 * @method \string requireSearchUserContent()
	 * @method \Bitrix\Main\EO_UserIndex resetSearchUserContent()
	 * @method \Bitrix\Main\EO_UserIndex unsetSearchUserContent()
	 * @method \string fillSearchUserContent()
	 * @method \string getSearchAdminContent()
	 * @method \Bitrix\Main\EO_UserIndex setSearchAdminContent(\string|\Bitrix\Main\DB\SqlExpression $searchAdminContent)
	 * @method bool hasSearchAdminContent()
	 * @method bool isSearchAdminContentFilled()
	 * @method bool isSearchAdminContentChanged()
	 * @method \string remindActualSearchAdminContent()
	 * @method \string requireSearchAdminContent()
	 * @method \Bitrix\Main\EO_UserIndex resetSearchAdminContent()
	 * @method \Bitrix\Main\EO_UserIndex unsetSearchAdminContent()
	 * @method \string fillSearchAdminContent()
	 * @method \string getSearchDepartmentContent()
	 * @method \Bitrix\Main\EO_UserIndex setSearchDepartmentContent(\string|\Bitrix\Main\DB\SqlExpression $searchDepartmentContent)
	 * @method bool hasSearchDepartmentContent()
	 * @method bool isSearchDepartmentContentFilled()
	 * @method bool isSearchDepartmentContentChanged()
	 * @method \string remindActualSearchDepartmentContent()
	 * @method \string requireSearchDepartmentContent()
	 * @method \Bitrix\Main\EO_UserIndex resetSearchDepartmentContent()
	 * @method \Bitrix\Main\EO_UserIndex unsetSearchDepartmentContent()
	 * @method \string fillSearchDepartmentContent()
	 * @method \string getName()
	 * @method \Bitrix\Main\EO_UserIndex setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Main\EO_UserIndex resetName()
	 * @method \Bitrix\Main\EO_UserIndex unsetName()
	 * @method \string fillName()
	 * @method \string getSecondName()
	 * @method \Bitrix\Main\EO_UserIndex setSecondName(\string|\Bitrix\Main\DB\SqlExpression $secondName)
	 * @method bool hasSecondName()
	 * @method bool isSecondNameFilled()
	 * @method bool isSecondNameChanged()
	 * @method \string remindActualSecondName()
	 * @method \string requireSecondName()
	 * @method \Bitrix\Main\EO_UserIndex resetSecondName()
	 * @method \Bitrix\Main\EO_UserIndex unsetSecondName()
	 * @method \string fillSecondName()
	 * @method \string getLastName()
	 * @method \Bitrix\Main\EO_UserIndex setLastName(\string|\Bitrix\Main\DB\SqlExpression $lastName)
	 * @method bool hasLastName()
	 * @method bool isLastNameFilled()
	 * @method bool isLastNameChanged()
	 * @method \string remindActualLastName()
	 * @method \string requireLastName()
	 * @method \Bitrix\Main\EO_UserIndex resetLastName()
	 * @method \Bitrix\Main\EO_UserIndex unsetLastName()
	 * @method \string fillLastName()
	 * @method \string getWorkPosition()
	 * @method \Bitrix\Main\EO_UserIndex setWorkPosition(\string|\Bitrix\Main\DB\SqlExpression $workPosition)
	 * @method bool hasWorkPosition()
	 * @method bool isWorkPositionFilled()
	 * @method bool isWorkPositionChanged()
	 * @method \string remindActualWorkPosition()
	 * @method \string requireWorkPosition()
	 * @method \Bitrix\Main\EO_UserIndex resetWorkPosition()
	 * @method \Bitrix\Main\EO_UserIndex unsetWorkPosition()
	 * @method \string fillWorkPosition()
	 * @method \string getUfDepartmentName()
	 * @method \Bitrix\Main\EO_UserIndex setUfDepartmentName(\string|\Bitrix\Main\DB\SqlExpression $ufDepartmentName)
	 * @method bool hasUfDepartmentName()
	 * @method bool isUfDepartmentNameFilled()
	 * @method bool isUfDepartmentNameChanged()
	 * @method \string remindActualUfDepartmentName()
	 * @method \string requireUfDepartmentName()
	 * @method \Bitrix\Main\EO_UserIndex resetUfDepartmentName()
	 * @method \Bitrix\Main\EO_UserIndex unsetUfDepartmentName()
	 * @method \string fillUfDepartmentName()
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
	 * @method \Bitrix\Main\EO_UserIndex set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserIndex reset($fieldName)
	 * @method \Bitrix\Main\EO_UserIndex unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserIndex wakeUp($data)
	 */
	class EO_UserIndex {
		/* @var \Bitrix\Main\UserIndexTable */
		static public $dataClass = '\Bitrix\Main\UserIndexTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserIndex_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getSearchUserContentList()
	 * @method \string[] fillSearchUserContent()
	 * @method \string[] getSearchAdminContentList()
	 * @method \string[] fillSearchAdminContent()
	 * @method \string[] getSearchDepartmentContentList()
	 * @method \string[] fillSearchDepartmentContent()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getSecondNameList()
	 * @method \string[] fillSecondName()
	 * @method \string[] getLastNameList()
	 * @method \string[] fillLastName()
	 * @method \string[] getWorkPositionList()
	 * @method \string[] fillWorkPosition()
	 * @method \string[] getUfDepartmentNameList()
	 * @method \string[] fillUfDepartmentName()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserIndex $object)
	 * @method bool has(\Bitrix\Main\EO_UserIndex $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserIndex getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserIndex[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserIndex $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserIndex_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserIndex current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserIndex_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserIndexTable */
		static public $dataClass = '\Bitrix\Main\UserIndexTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserIndex_Result exec()
	 * @method \Bitrix\Main\EO_UserIndex fetchObject()
	 * @method \Bitrix\Main\EO_UserIndex_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserIndex_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserIndex fetchObject()
	 * @method \Bitrix\Main\EO_UserIndex_Collection fetchCollection()
	 */
	class EO_UserIndex_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserIndex createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserIndex_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserIndex wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserIndex_Collection wakeUpCollection($rows)
	 */
	class EO_UserIndex_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserPhoneAuthTable:main/lib/userphoneauth.php */
namespace Bitrix\Main {
	/**
	 * EO_UserPhoneAuth
	 * @see \Bitrix\Main\UserPhoneAuthTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EO_UserPhoneAuth setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getPhoneNumber()
	 * @method \Bitrix\Main\EO_UserPhoneAuth setPhoneNumber(\string|\Bitrix\Main\DB\SqlExpression $phoneNumber)
	 * @method bool hasPhoneNumber()
	 * @method bool isPhoneNumberFilled()
	 * @method bool isPhoneNumberChanged()
	 * @method \string remindActualPhoneNumber()
	 * @method \string requirePhoneNumber()
	 * @method \Bitrix\Main\EO_UserPhoneAuth resetPhoneNumber()
	 * @method \Bitrix\Main\EO_UserPhoneAuth unsetPhoneNumber()
	 * @method \string fillPhoneNumber()
	 * @method \string getOtpSecret()
	 * @method \Bitrix\Main\EO_UserPhoneAuth setOtpSecret(\string|\Bitrix\Main\DB\SqlExpression $otpSecret)
	 * @method bool hasOtpSecret()
	 * @method bool isOtpSecretFilled()
	 * @method bool isOtpSecretChanged()
	 * @method \string remindActualOtpSecret()
	 * @method \string requireOtpSecret()
	 * @method \Bitrix\Main\EO_UserPhoneAuth resetOtpSecret()
	 * @method \Bitrix\Main\EO_UserPhoneAuth unsetOtpSecret()
	 * @method \string fillOtpSecret()
	 * @method \int getAttempts()
	 * @method \Bitrix\Main\EO_UserPhoneAuth setAttempts(\int|\Bitrix\Main\DB\SqlExpression $attempts)
	 * @method bool hasAttempts()
	 * @method bool isAttemptsFilled()
	 * @method bool isAttemptsChanged()
	 * @method \int remindActualAttempts()
	 * @method \int requireAttempts()
	 * @method \Bitrix\Main\EO_UserPhoneAuth resetAttempts()
	 * @method \Bitrix\Main\EO_UserPhoneAuth unsetAttempts()
	 * @method \int fillAttempts()
	 * @method \boolean getConfirmed()
	 * @method \Bitrix\Main\EO_UserPhoneAuth setConfirmed(\boolean|\Bitrix\Main\DB\SqlExpression $confirmed)
	 * @method bool hasConfirmed()
	 * @method bool isConfirmedFilled()
	 * @method bool isConfirmedChanged()
	 * @method \boolean remindActualConfirmed()
	 * @method \boolean requireConfirmed()
	 * @method \Bitrix\Main\EO_UserPhoneAuth resetConfirmed()
	 * @method \Bitrix\Main\EO_UserPhoneAuth unsetConfirmed()
	 * @method \boolean fillConfirmed()
	 * @method \Bitrix\Main\Type\DateTime getDateSent()
	 * @method \Bitrix\Main\EO_UserPhoneAuth setDateSent(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateSent)
	 * @method bool hasDateSent()
	 * @method bool isDateSentFilled()
	 * @method bool isDateSentChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateSent()
	 * @method \Bitrix\Main\Type\DateTime requireDateSent()
	 * @method \Bitrix\Main\EO_UserPhoneAuth resetDateSent()
	 * @method \Bitrix\Main\EO_UserPhoneAuth unsetDateSent()
	 * @method \Bitrix\Main\Type\DateTime fillDateSent()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Main\EO_UserPhoneAuth setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Main\EO_UserPhoneAuth resetUser()
	 * @method \Bitrix\Main\EO_UserPhoneAuth unsetUser()
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
	 * @method \Bitrix\Main\EO_UserPhoneAuth set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserPhoneAuth reset($fieldName)
	 * @method \Bitrix\Main\EO_UserPhoneAuth unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserPhoneAuth wakeUp($data)
	 */
	class EO_UserPhoneAuth {
		/* @var \Bitrix\Main\UserPhoneAuthTable */
		static public $dataClass = '\Bitrix\Main\UserPhoneAuthTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserPhoneAuth_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getPhoneNumberList()
	 * @method \string[] fillPhoneNumber()
	 * @method \string[] getOtpSecretList()
	 * @method \string[] fillOtpSecret()
	 * @method \int[] getAttemptsList()
	 * @method \int[] fillAttempts()
	 * @method \boolean[] getConfirmedList()
	 * @method \boolean[] fillConfirmed()
	 * @method \Bitrix\Main\Type\DateTime[] getDateSentList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateSent()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Main\EO_UserPhoneAuth_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserPhoneAuth $object)
	 * @method bool has(\Bitrix\Main\EO_UserPhoneAuth $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserPhoneAuth getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserPhoneAuth[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserPhoneAuth $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserPhoneAuth_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserPhoneAuth current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserPhoneAuth_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserPhoneAuthTable */
		static public $dataClass = '\Bitrix\Main\UserPhoneAuthTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserPhoneAuth_Result exec()
	 * @method \Bitrix\Main\EO_UserPhoneAuth fetchObject()
	 * @method \Bitrix\Main\EO_UserPhoneAuth_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserPhoneAuth_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserPhoneAuth fetchObject()
	 * @method \Bitrix\Main\EO_UserPhoneAuth_Collection fetchCollection()
	 */
	class EO_UserPhoneAuth_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserPhoneAuth createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserPhoneAuth_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserPhoneAuth wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserPhoneAuth_Collection wakeUpCollection($rows)
	 */
	class EO_UserPhoneAuth_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserProfileHistoryTable:main/lib/userprofilehistory.php */
namespace Bitrix\Main {
	/**
	 * EO_UserProfileHistory
	 * @see \Bitrix\Main\UserProfileHistoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_UserProfileHistory setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Main\EO_UserProfileHistory setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Main\EO_UserProfileHistory resetUserId()
	 * @method \Bitrix\Main\EO_UserProfileHistory unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getEventType()
	 * @method \Bitrix\Main\EO_UserProfileHistory setEventType(\int|\Bitrix\Main\DB\SqlExpression $eventType)
	 * @method bool hasEventType()
	 * @method bool isEventTypeFilled()
	 * @method bool isEventTypeChanged()
	 * @method \int remindActualEventType()
	 * @method \int requireEventType()
	 * @method \Bitrix\Main\EO_UserProfileHistory resetEventType()
	 * @method \Bitrix\Main\EO_UserProfileHistory unsetEventType()
	 * @method \int fillEventType()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Main\EO_UserProfileHistory setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Main\EO_UserProfileHistory resetDateInsert()
	 * @method \Bitrix\Main\EO_UserProfileHistory unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getRemoteAddr()
	 * @method \Bitrix\Main\EO_UserProfileHistory setRemoteAddr(\string|\Bitrix\Main\DB\SqlExpression $remoteAddr)
	 * @method bool hasRemoteAddr()
	 * @method bool isRemoteAddrFilled()
	 * @method bool isRemoteAddrChanged()
	 * @method \string remindActualRemoteAddr()
	 * @method \string requireRemoteAddr()
	 * @method \Bitrix\Main\EO_UserProfileHistory resetRemoteAddr()
	 * @method \Bitrix\Main\EO_UserProfileHistory unsetRemoteAddr()
	 * @method \string fillRemoteAddr()
	 * @method \string getUserAgent()
	 * @method \Bitrix\Main\EO_UserProfileHistory setUserAgent(\string|\Bitrix\Main\DB\SqlExpression $userAgent)
	 * @method bool hasUserAgent()
	 * @method bool isUserAgentFilled()
	 * @method bool isUserAgentChanged()
	 * @method \string remindActualUserAgent()
	 * @method \string requireUserAgent()
	 * @method \Bitrix\Main\EO_UserProfileHistory resetUserAgent()
	 * @method \Bitrix\Main\EO_UserProfileHistory unsetUserAgent()
	 * @method \string fillUserAgent()
	 * @method \string getRequestUri()
	 * @method \Bitrix\Main\EO_UserProfileHistory setRequestUri(\string|\Bitrix\Main\DB\SqlExpression $requestUri)
	 * @method bool hasRequestUri()
	 * @method bool isRequestUriFilled()
	 * @method bool isRequestUriChanged()
	 * @method \string remindActualRequestUri()
	 * @method \string requireRequestUri()
	 * @method \Bitrix\Main\EO_UserProfileHistory resetRequestUri()
	 * @method \Bitrix\Main\EO_UserProfileHistory unsetRequestUri()
	 * @method \string fillRequestUri()
	 * @method \int getUpdatedById()
	 * @method \Bitrix\Main\EO_UserProfileHistory setUpdatedById(\int|\Bitrix\Main\DB\SqlExpression $updatedById)
	 * @method bool hasUpdatedById()
	 * @method bool isUpdatedByIdFilled()
	 * @method bool isUpdatedByIdChanged()
	 * @method \int remindActualUpdatedById()
	 * @method \int requireUpdatedById()
	 * @method \Bitrix\Main\EO_UserProfileHistory resetUpdatedById()
	 * @method \Bitrix\Main\EO_UserProfileHistory unsetUpdatedById()
	 * @method \int fillUpdatedById()
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
	 * @method \Bitrix\Main\EO_UserProfileHistory set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserProfileHistory reset($fieldName)
	 * @method \Bitrix\Main\EO_UserProfileHistory unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserProfileHistory wakeUp($data)
	 */
	class EO_UserProfileHistory {
		/* @var \Bitrix\Main\UserProfileHistoryTable */
		static public $dataClass = '\Bitrix\Main\UserProfileHistoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserProfileHistory_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getEventTypeList()
	 * @method \int[] fillEventType()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getRemoteAddrList()
	 * @method \string[] fillRemoteAddr()
	 * @method \string[] getUserAgentList()
	 * @method \string[] fillUserAgent()
	 * @method \string[] getRequestUriList()
	 * @method \string[] fillRequestUri()
	 * @method \int[] getUpdatedByIdList()
	 * @method \int[] fillUpdatedById()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserProfileHistory $object)
	 * @method bool has(\Bitrix\Main\EO_UserProfileHistory $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserProfileHistory getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserProfileHistory[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserProfileHistory $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserProfileHistory_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserProfileHistory current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserProfileHistory_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserProfileHistoryTable */
		static public $dataClass = '\Bitrix\Main\UserProfileHistoryTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserProfileHistory_Result exec()
	 * @method \Bitrix\Main\EO_UserProfileHistory fetchObject()
	 * @method \Bitrix\Main\EO_UserProfileHistory_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserProfileHistory_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserProfileHistory fetchObject()
	 * @method \Bitrix\Main\EO_UserProfileHistory_Collection fetchCollection()
	 */
	class EO_UserProfileHistory_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserProfileHistory createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserProfileHistory_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserProfileHistory wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserProfileHistory_Collection wakeUpCollection($rows)
	 */
	class EO_UserProfileHistory_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Main\UserProfileRecordTable:main/lib/userprofilerecord.php */
namespace Bitrix\Main {
	/**
	 * EO_UserProfileRecord
	 * @see \Bitrix\Main\UserProfileRecordTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Main\EO_UserProfileRecord setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getHistoryId()
	 * @method \Bitrix\Main\EO_UserProfileRecord setHistoryId(\int|\Bitrix\Main\DB\SqlExpression $historyId)
	 * @method bool hasHistoryId()
	 * @method bool isHistoryIdFilled()
	 * @method bool isHistoryIdChanged()
	 * @method \int remindActualHistoryId()
	 * @method \int requireHistoryId()
	 * @method \Bitrix\Main\EO_UserProfileRecord resetHistoryId()
	 * @method \Bitrix\Main\EO_UserProfileRecord unsetHistoryId()
	 * @method \int fillHistoryId()
	 * @method \string getField()
	 * @method \Bitrix\Main\EO_UserProfileRecord setField(\string|\Bitrix\Main\DB\SqlExpression $field)
	 * @method bool hasField()
	 * @method bool isFieldFilled()
	 * @method bool isFieldChanged()
	 * @method \string remindActualField()
	 * @method \string requireField()
	 * @method \Bitrix\Main\EO_UserProfileRecord resetField()
	 * @method \Bitrix\Main\EO_UserProfileRecord unsetField()
	 * @method \string fillField()
	 * @method \string getData()
	 * @method \Bitrix\Main\EO_UserProfileRecord setData(\string|\Bitrix\Main\DB\SqlExpression $data)
	 * @method bool hasData()
	 * @method bool isDataFilled()
	 * @method bool isDataChanged()
	 * @method \string remindActualData()
	 * @method \string requireData()
	 * @method \Bitrix\Main\EO_UserProfileRecord resetData()
	 * @method \Bitrix\Main\EO_UserProfileRecord unsetData()
	 * @method \string fillData()
	 * @method \Bitrix\Main\EO_UserProfileHistory getHistory()
	 * @method \Bitrix\Main\EO_UserProfileHistory remindActualHistory()
	 * @method \Bitrix\Main\EO_UserProfileHistory requireHistory()
	 * @method \Bitrix\Main\EO_UserProfileRecord setHistory(\Bitrix\Main\EO_UserProfileHistory $object)
	 * @method \Bitrix\Main\EO_UserProfileRecord resetHistory()
	 * @method \Bitrix\Main\EO_UserProfileRecord unsetHistory()
	 * @method bool hasHistory()
	 * @method bool isHistoryFilled()
	 * @method bool isHistoryChanged()
	 * @method \Bitrix\Main\EO_UserProfileHistory fillHistory()
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
	 * @method \Bitrix\Main\EO_UserProfileRecord set($fieldName, $value)
	 * @method \Bitrix\Main\EO_UserProfileRecord reset($fieldName)
	 * @method \Bitrix\Main\EO_UserProfileRecord unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Main\EO_UserProfileRecord wakeUp($data)
	 */
	class EO_UserProfileRecord {
		/* @var \Bitrix\Main\UserProfileRecordTable */
		static public $dataClass = '\Bitrix\Main\UserProfileRecordTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Main {
	/**
	 * EO_UserProfileRecord_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getHistoryIdList()
	 * @method \int[] fillHistoryId()
	 * @method \string[] getFieldList()
	 * @method \string[] fillField()
	 * @method \string[] getDataList()
	 * @method \string[] fillData()
	 * @method \Bitrix\Main\EO_UserProfileHistory[] getHistoryList()
	 * @method \Bitrix\Main\EO_UserProfileRecord_Collection getHistoryCollection()
	 * @method \Bitrix\Main\EO_UserProfileHistory_Collection fillHistory()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Main\EO_UserProfileRecord $object)
	 * @method bool has(\Bitrix\Main\EO_UserProfileRecord $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserProfileRecord getByPrimary($primary)
	 * @method \Bitrix\Main\EO_UserProfileRecord[] getAll()
	 * @method bool remove(\Bitrix\Main\EO_UserProfileRecord $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Main\EO_UserProfileRecord_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Main\EO_UserProfileRecord current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserProfileRecord_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Main\UserProfileRecordTable */
		static public $dataClass = '\Bitrix\Main\UserProfileRecordTable';
	}
}
namespace Bitrix\Main {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserProfileRecord_Result exec()
	 * @method \Bitrix\Main\EO_UserProfileRecord fetchObject()
	 * @method \Bitrix\Main\EO_UserProfileRecord_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserProfileRecord_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Main\EO_UserProfileRecord fetchObject()
	 * @method \Bitrix\Main\EO_UserProfileRecord_Collection fetchCollection()
	 */
	class EO_UserProfileRecord_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Main\EO_UserProfileRecord createObject($setDefaultValues = true)
	 * @method \Bitrix\Main\EO_UserProfileRecord_Collection createCollection()
	 * @method \Bitrix\Main\EO_UserProfileRecord wakeUpObject($row)
	 * @method \Bitrix\Main\EO_UserProfileRecord_Collection wakeUpCollection($rows)
	 */
	class EO_UserProfileRecord_Entity extends \Bitrix\Main\ORM\Entity {}
}