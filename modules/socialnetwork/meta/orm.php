<?php

/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\FeatureTable:socialnetwork\lib\feature.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_Feature
	 * @see \Bitrix\Socialnetwork\FeatureTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_Feature setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEntityType()
	 * @method \Bitrix\Socialnetwork\EO_Feature setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Socialnetwork\EO_Feature resetEntityType()
	 * @method \Bitrix\Socialnetwork\EO_Feature unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Socialnetwork\EO_Feature setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Socialnetwork\EO_Feature resetEntityId()
	 * @method \Bitrix\Socialnetwork\EO_Feature unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getFeature()
	 * @method \Bitrix\Socialnetwork\EO_Feature setFeature(\string|\Bitrix\Main\DB\SqlExpression $feature)
	 * @method bool hasFeature()
	 * @method bool isFeatureFilled()
	 * @method bool isFeatureChanged()
	 * @method \string remindActualFeature()
	 * @method \string requireFeature()
	 * @method \Bitrix\Socialnetwork\EO_Feature resetFeature()
	 * @method \Bitrix\Socialnetwork\EO_Feature unsetFeature()
	 * @method \string fillFeature()
	 * @method \string getFeatureName()
	 * @method \Bitrix\Socialnetwork\EO_Feature setFeatureName(\string|\Bitrix\Main\DB\SqlExpression $featureName)
	 * @method bool hasFeatureName()
	 * @method bool isFeatureNameFilled()
	 * @method bool isFeatureNameChanged()
	 * @method \string remindActualFeatureName()
	 * @method \string requireFeatureName()
	 * @method \Bitrix\Socialnetwork\EO_Feature resetFeatureName()
	 * @method \Bitrix\Socialnetwork\EO_Feature unsetFeatureName()
	 * @method \string fillFeatureName()
	 * @method \string getActive()
	 * @method \Bitrix\Socialnetwork\EO_Feature setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Socialnetwork\EO_Feature resetActive()
	 * @method \Bitrix\Socialnetwork\EO_Feature unsetActive()
	 * @method \string fillActive()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_Feature setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_Feature resetDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_Feature unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_Feature setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_Feature resetDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_Feature unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
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
	 * @method \Bitrix\Socialnetwork\EO_Feature set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_Feature reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_Feature unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_Feature wakeUp($data)
	 */
	class EO_Feature {
		/* @var \Bitrix\Socialnetwork\FeatureTable */
		static public $dataClass = '\Bitrix\Socialnetwork\FeatureTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_Feature_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getFeatureList()
	 * @method \string[] fillFeature()
	 * @method \string[] getFeatureNameList()
	 * @method \string[] fillFeatureName()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_Feature $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_Feature $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_Feature getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_Feature[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_Feature $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_Feature_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_Feature current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Feature_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\FeatureTable */
		static public $dataClass = '\Bitrix\Socialnetwork\FeatureTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Feature_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_Feature fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_Feature_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Feature_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_Feature fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_Feature_Collection fetchCollection()
	 */
	class EO_Feature_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_Feature createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_Feature_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_Feature wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_Feature_Collection wakeUpCollection($rows)
	 */
	class EO_Feature_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\FeaturePermTable:socialnetwork\lib\featureperm.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_FeaturePerm
	 * @see \Bitrix\Socialnetwork\FeaturePermTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getFeatureId()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm setFeatureId(\int|\Bitrix\Main\DB\SqlExpression $featureId)
	 * @method bool hasFeatureId()
	 * @method bool isFeatureIdFilled()
	 * @method bool isFeatureIdChanged()
	 * @method \int remindActualFeatureId()
	 * @method \int requireFeatureId()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm resetFeatureId()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm unsetFeatureId()
	 * @method \int fillFeatureId()
	 * @method \Bitrix\Socialnetwork\EO_Feature getFeature()
	 * @method \Bitrix\Socialnetwork\EO_Feature remindActualFeature()
	 * @method \Bitrix\Socialnetwork\EO_Feature requireFeature()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm setFeature(\Bitrix\Socialnetwork\EO_Feature $object)
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm resetFeature()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm unsetFeature()
	 * @method bool hasFeature()
	 * @method bool isFeatureFilled()
	 * @method bool isFeatureChanged()
	 * @method \Bitrix\Socialnetwork\EO_Feature fillFeature()
	 * @method \string getOperationId()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm setOperationId(\string|\Bitrix\Main\DB\SqlExpression $operationId)
	 * @method bool hasOperationId()
	 * @method bool isOperationIdFilled()
	 * @method bool isOperationIdChanged()
	 * @method \string remindActualOperationId()
	 * @method \string requireOperationId()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm resetOperationId()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm unsetOperationId()
	 * @method \string fillOperationId()
	 * @method \string getRole()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm setRole(\string|\Bitrix\Main\DB\SqlExpression $role)
	 * @method bool hasRole()
	 * @method bool isRoleFilled()
	 * @method bool isRoleChanged()
	 * @method \string remindActualRole()
	 * @method \string requireRole()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm resetRole()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm unsetRole()
	 * @method \string fillRole()
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
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_FeaturePerm wakeUp($data)
	 */
	class EO_FeaturePerm {
		/* @var \Bitrix\Socialnetwork\FeaturePermTable */
		static public $dataClass = '\Bitrix\Socialnetwork\FeaturePermTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_FeaturePerm_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getFeatureIdList()
	 * @method \int[] fillFeatureId()
	 * @method \Bitrix\Socialnetwork\EO_Feature[] getFeatureList()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm_Collection getFeatureCollection()
	 * @method \Bitrix\Socialnetwork\EO_Feature_Collection fillFeature()
	 * @method \string[] getOperationIdList()
	 * @method \string[] fillOperationId()
	 * @method \string[] getRoleList()
	 * @method \string[] fillRole()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_FeaturePerm $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_FeaturePerm $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_FeaturePerm $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_FeaturePerm_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_FeaturePerm_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\FeaturePermTable */
		static public $dataClass = '\Bitrix\Socialnetwork\FeaturePermTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_FeaturePerm_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_FeaturePerm_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm_Collection fetchCollection()
	 */
	class EO_FeaturePerm_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_FeaturePerm_Collection wakeUpCollection($rows)
	 */
	class EO_FeaturePerm_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogTable:socialnetwork\lib\log.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_Log
	 * @see \Bitrix\Socialnetwork\LogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_Log setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getEntityType()
	 * @method \Bitrix\Socialnetwork\EO_Log setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Socialnetwork\EO_Log resetEntityType()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Socialnetwork\EO_Log setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Socialnetwork\EO_Log resetEntityId()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEventId()
	 * @method \Bitrix\Socialnetwork\EO_Log setEventId(\string|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \string remindActualEventId()
	 * @method \string requireEventId()
	 * @method \Bitrix\Socialnetwork\EO_Log resetEventId()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetEventId()
	 * @method \string fillEventId()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_Log setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialnetwork\EO_Log resetUserId()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialnetwork\EO_Log setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_Log resetUser()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \string getTitle()
	 * @method \Bitrix\Socialnetwork\EO_Log setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Socialnetwork\EO_Log resetTitle()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getMessage()
	 * @method \Bitrix\Socialnetwork\EO_Log setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Socialnetwork\EO_Log resetMessage()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetMessage()
	 * @method \string fillMessage()
	 * @method \string getTextMessage()
	 * @method \Bitrix\Socialnetwork\EO_Log setTextMessage(\string|\Bitrix\Main\DB\SqlExpression $textMessage)
	 * @method bool hasTextMessage()
	 * @method bool isTextMessageFilled()
	 * @method bool isTextMessageChanged()
	 * @method \string remindActualTextMessage()
	 * @method \string requireTextMessage()
	 * @method \Bitrix\Socialnetwork\EO_Log resetTextMessage()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetTextMessage()
	 * @method \string fillTextMessage()
	 * @method \string getUrl()
	 * @method \Bitrix\Socialnetwork\EO_Log setUrl(\string|\Bitrix\Main\DB\SqlExpression $url)
	 * @method bool hasUrl()
	 * @method bool isUrlFilled()
	 * @method bool isUrlChanged()
	 * @method \string remindActualUrl()
	 * @method \string requireUrl()
	 * @method \Bitrix\Socialnetwork\EO_Log resetUrl()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetUrl()
	 * @method \string fillUrl()
	 * @method \string getModuleId()
	 * @method \Bitrix\Socialnetwork\EO_Log setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Socialnetwork\EO_Log resetModuleId()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getParams()
	 * @method \Bitrix\Socialnetwork\EO_Log setParams(\string|\Bitrix\Main\DB\SqlExpression $params)
	 * @method bool hasParams()
	 * @method bool isParamsFilled()
	 * @method bool isParamsChanged()
	 * @method \string remindActualParams()
	 * @method \string requireParams()
	 * @method \Bitrix\Socialnetwork\EO_Log resetParams()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetParams()
	 * @method \string fillParams()
	 * @method \int getSourceId()
	 * @method \Bitrix\Socialnetwork\EO_Log setSourceId(\int|\Bitrix\Main\DB\SqlExpression $sourceId)
	 * @method bool hasSourceId()
	 * @method bool isSourceIdFilled()
	 * @method bool isSourceIdChanged()
	 * @method \int remindActualSourceId()
	 * @method \int requireSourceId()
	 * @method \Bitrix\Socialnetwork\EO_Log resetSourceId()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetSourceId()
	 * @method \int fillSourceId()
	 * @method \Bitrix\Main\Type\DateTime getLogDate()
	 * @method \Bitrix\Socialnetwork\EO_Log setLogDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $logDate)
	 * @method bool hasLogDate()
	 * @method bool isLogDateFilled()
	 * @method bool isLogDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLogDate()
	 * @method \Bitrix\Main\Type\DateTime requireLogDate()
	 * @method \Bitrix\Socialnetwork\EO_Log resetLogDate()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetLogDate()
	 * @method \Bitrix\Main\Type\DateTime fillLogDate()
	 * @method \Bitrix\Main\Type\DateTime getLogUpdate()
	 * @method \Bitrix\Socialnetwork\EO_Log setLogUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $logUpdate)
	 * @method bool hasLogUpdate()
	 * @method bool isLogUpdateFilled()
	 * @method bool isLogUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLogUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireLogUpdate()
	 * @method \Bitrix\Socialnetwork\EO_Log resetLogUpdate()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetLogUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillLogUpdate()
	 * @method \int getCommentsCount()
	 * @method \Bitrix\Socialnetwork\EO_Log setCommentsCount(\int|\Bitrix\Main\DB\SqlExpression $commentsCount)
	 * @method bool hasCommentsCount()
	 * @method bool isCommentsCountFilled()
	 * @method bool isCommentsCountChanged()
	 * @method \int remindActualCommentsCount()
	 * @method \int requireCommentsCount()
	 * @method \Bitrix\Socialnetwork\EO_Log resetCommentsCount()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetCommentsCount()
	 * @method \int fillCommentsCount()
	 * @method \boolean getTransform()
	 * @method \Bitrix\Socialnetwork\EO_Log setTransform(\boolean|\Bitrix\Main\DB\SqlExpression $transform)
	 * @method bool hasTransform()
	 * @method bool isTransformFilled()
	 * @method bool isTransformChanged()
	 * @method \boolean remindActualTransform()
	 * @method \boolean requireTransform()
	 * @method \Bitrix\Socialnetwork\EO_Log resetTransform()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetTransform()
	 * @method \boolean fillTransform()
	 * @method \boolean getInactive()
	 * @method \Bitrix\Socialnetwork\EO_Log setInactive(\boolean|\Bitrix\Main\DB\SqlExpression $inactive)
	 * @method bool hasInactive()
	 * @method bool isInactiveFilled()
	 * @method bool isInactiveChanged()
	 * @method \boolean remindActualInactive()
	 * @method \boolean requireInactive()
	 * @method \Bitrix\Socialnetwork\EO_Log resetInactive()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetInactive()
	 * @method \boolean fillInactive()
	 * @method \string getRatingTypeId()
	 * @method \Bitrix\Socialnetwork\EO_Log setRatingTypeId(\string|\Bitrix\Main\DB\SqlExpression $ratingTypeId)
	 * @method bool hasRatingTypeId()
	 * @method bool isRatingTypeIdFilled()
	 * @method bool isRatingTypeIdChanged()
	 * @method \string remindActualRatingTypeId()
	 * @method \string requireRatingTypeId()
	 * @method \Bitrix\Socialnetwork\EO_Log resetRatingTypeId()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetRatingTypeId()
	 * @method \string fillRatingTypeId()
	 * @method \int getRatingEntityId()
	 * @method \Bitrix\Socialnetwork\EO_Log setRatingEntityId(\int|\Bitrix\Main\DB\SqlExpression $ratingEntityId)
	 * @method bool hasRatingEntityId()
	 * @method bool isRatingEntityIdFilled()
	 * @method bool isRatingEntityIdChanged()
	 * @method \int remindActualRatingEntityId()
	 * @method \int requireRatingEntityId()
	 * @method \Bitrix\Socialnetwork\EO_Log resetRatingEntityId()
	 * @method \Bitrix\Socialnetwork\EO_Log unsetRatingEntityId()
	 * @method \int fillRatingEntityId()
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
	 * @method \Bitrix\Socialnetwork\EO_Log set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_Log reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_Log unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_Log wakeUp($data)
	 */
	class EO_Log {
		/* @var \Bitrix\Socialnetwork\LogTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_Log_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEventIdList()
	 * @method \string[] fillEventId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialnetwork\EO_Log_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \string[] getTextMessageList()
	 * @method \string[] fillTextMessage()
	 * @method \string[] getUrlList()
	 * @method \string[] fillUrl()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getParamsList()
	 * @method \string[] fillParams()
	 * @method \int[] getSourceIdList()
	 * @method \int[] fillSourceId()
	 * @method \Bitrix\Main\Type\DateTime[] getLogDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLogDate()
	 * @method \Bitrix\Main\Type\DateTime[] getLogUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLogUpdate()
	 * @method \int[] getCommentsCountList()
	 * @method \int[] fillCommentsCount()
	 * @method \boolean[] getTransformList()
	 * @method \boolean[] fillTransform()
	 * @method \boolean[] getInactiveList()
	 * @method \boolean[] fillInactive()
	 * @method \string[] getRatingTypeIdList()
	 * @method \string[] fillRatingTypeId()
	 * @method \int[] getRatingEntityIdList()
	 * @method \int[] fillRatingEntityId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_Log $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_Log $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_Log getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_Log[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_Log $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_Log_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_Log current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Log_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Log_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_Log fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_Log_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Log_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_Log fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_Log_Collection fetchCollection()
	 */
	class EO_Log_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_Log createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_Log_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_Log wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_Log_Collection wakeUpCollection($rows)
	 */
	class EO_Log_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogCommentTable:socialnetwork\lib\logcomment.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogComment
	 * @see \Bitrix\Socialnetwork\LogCommentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setLogId(\int|\Bitrix\Main\DB\SqlExpression $logId)
	 * @method bool hasLogId()
	 * @method bool isLogIdFilled()
	 * @method bool isLogIdChanged()
	 * @method \int remindActualLogId()
	 * @method \int requireLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetLogId()
	 * @method \int fillLogId()
	 * @method \Bitrix\Socialnetwork\EO_Log getLog()
	 * @method \Bitrix\Socialnetwork\EO_Log remindActualLog()
	 * @method \Bitrix\Socialnetwork\EO_Log requireLog()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setLog(\Bitrix\Socialnetwork\EO_Log $object)
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetLog()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetLog()
	 * @method bool hasLog()
	 * @method bool isLogFilled()
	 * @method bool isLogChanged()
	 * @method \Bitrix\Socialnetwork\EO_Log fillLog()
	 * @method \string getEventId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setEventId(\string|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \string remindActualEventId()
	 * @method \string requireEventId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetEventId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetEventId()
	 * @method \string fillEventId()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetUserId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getMessage()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetMessage()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetMessage()
	 * @method \string fillMessage()
	 * @method \int getSourceId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setSourceId(\int|\Bitrix\Main\DB\SqlExpression $sourceId)
	 * @method bool hasSourceId()
	 * @method bool isSourceIdFilled()
	 * @method bool isSourceIdChanged()
	 * @method \int remindActualSourceId()
	 * @method \int requireSourceId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetSourceId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetSourceId()
	 * @method \int fillSourceId()
	 * @method \Bitrix\Main\Type\DateTime getLogDate()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setLogDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $logDate)
	 * @method bool hasLogDate()
	 * @method bool isLogDateFilled()
	 * @method bool isLogDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLogDate()
	 * @method \Bitrix\Main\Type\DateTime requireLogDate()
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetLogDate()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetLogDate()
	 * @method \Bitrix\Main\Type\DateTime fillLogDate()
	 * @method \string getShareDest()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setShareDest(\string|\Bitrix\Main\DB\SqlExpression $shareDest)
	 * @method bool hasShareDest()
	 * @method bool isShareDestFilled()
	 * @method bool isShareDestChanged()
	 * @method \string remindActualShareDest()
	 * @method \string requireShareDest()
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetShareDest()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetShareDest()
	 * @method \string fillShareDest()
	 * @method \string getRatingTypeId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setRatingTypeId(\string|\Bitrix\Main\DB\SqlExpression $ratingTypeId)
	 * @method bool hasRatingTypeId()
	 * @method bool isRatingTypeIdFilled()
	 * @method bool isRatingTypeIdChanged()
	 * @method \string remindActualRatingTypeId()
	 * @method \string requireRatingTypeId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetRatingTypeId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetRatingTypeId()
	 * @method \string fillRatingTypeId()
	 * @method \int getRatingEntityId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment setRatingEntityId(\int|\Bitrix\Main\DB\SqlExpression $ratingEntityId)
	 * @method bool hasRatingEntityId()
	 * @method bool isRatingEntityIdFilled()
	 * @method bool isRatingEntityIdChanged()
	 * @method \int remindActualRatingEntityId()
	 * @method \int requireRatingEntityId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment resetRatingEntityId()
	 * @method \Bitrix\Socialnetwork\EO_LogComment unsetRatingEntityId()
	 * @method \int fillRatingEntityId()
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
	 * @method \Bitrix\Socialnetwork\EO_LogComment set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogComment reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogComment unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogComment wakeUp($data)
	 */
	class EO_LogComment {
		/* @var \Bitrix\Socialnetwork\LogCommentTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogCommentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogComment_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getLogIdList()
	 * @method \int[] fillLogId()
	 * @method \Bitrix\Socialnetwork\EO_Log[] getLogList()
	 * @method \Bitrix\Socialnetwork\EO_LogComment_Collection getLogCollection()
	 * @method \Bitrix\Socialnetwork\EO_Log_Collection fillLog()
	 * @method \string[] getEventIdList()
	 * @method \string[] fillEventId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \int[] getSourceIdList()
	 * @method \int[] fillSourceId()
	 * @method \Bitrix\Main\Type\DateTime[] getLogDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLogDate()
	 * @method \string[] getShareDestList()
	 * @method \string[] fillShareDest()
	 * @method \string[] getRatingTypeIdList()
	 * @method \string[] fillRatingTypeId()
	 * @method \int[] getRatingEntityIdList()
	 * @method \int[] fillRatingEntityId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogComment $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogComment $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogComment getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogComment[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogComment $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogComment_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogComment current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogComment_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogCommentTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogCommentTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogComment_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogComment fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogComment_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogComment_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogComment fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogComment_Collection fetchCollection()
	 */
	class EO_LogComment_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogComment createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogComment_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogComment wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogComment_Collection wakeUpCollection($rows)
	 */
	class EO_LogComment_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogFavoritesTable:socialnetwork\lib\logfavorites.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogFavorites
	 * @see \Bitrix\Socialnetwork\LogFavoritesTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int getLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites setLogId(\int|\Bitrix\Main\DB\SqlExpression $logId)
	 * @method bool hasLogId()
	 * @method bool isLogIdFilled()
	 * @method bool isLogIdChanged()
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
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogFavorites wakeUp($data)
	 */
	class EO_LogFavorites {
		/* @var \Bitrix\Socialnetwork\LogFavoritesTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogFavoritesTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogFavorites_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \int[] getLogIdList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogFavorites $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogFavorites $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogFavorites $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogFavorites_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogFavorites_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogFavoritesTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogFavoritesTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogFavorites_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogFavorites_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites_Collection fetchCollection()
	 */
	class EO_LogFavorites_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogFavorites_Collection wakeUpCollection($rows)
	 */
	class EO_LogFavorites_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogFollowTable:socialnetwork\lib\logfollow.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogFollow
	 * @see \Bitrix\Socialnetwork\LogFollowTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_LogFollow setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Socialnetwork\EO_LogFollow setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \boolean getType()
	 * @method \Bitrix\Socialnetwork\EO_LogFollow setType(\boolean|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \boolean remindActualType()
	 * @method \boolean requireType()
	 * @method \Bitrix\Socialnetwork\EO_LogFollow resetType()
	 * @method \Bitrix\Socialnetwork\EO_LogFollow unsetType()
	 * @method \boolean fillType()
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
	 * @method \Bitrix\Socialnetwork\EO_LogFollow set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogFollow reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogFollow unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogFollow wakeUp($data)
	 */
	class EO_LogFollow {
		/* @var \Bitrix\Socialnetwork\LogFollowTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogFollowTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogFollow_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getCodeList()
	 * @method \boolean[] getTypeList()
	 * @method \boolean[] fillType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogFollow $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogFollow $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogFollow getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogFollow[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogFollow $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogFollow_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogFollow current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogFollow_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogFollowTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogFollowTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogFollow_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogFollow fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogFollow_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogFollow_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogFollow fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogFollow_Collection fetchCollection()
	 */
	class EO_LogFollow_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogFollow createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogFollow_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogFollow wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogFollow_Collection wakeUpCollection($rows)
	 */
	class EO_LogFollow_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogIndexTable:socialnetwork\lib\logindex.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogIndex
	 * @see \Bitrix\Socialnetwork\LogIndexTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex setLogId(\int|\Bitrix\Main\DB\SqlExpression $logId)
	 * @method bool hasLogId()
	 * @method bool isLogIdFilled()
	 * @method bool isLogIdChanged()
	 * @method \int remindActualLogId()
	 * @method \int requireLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex resetLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex unsetLogId()
	 * @method \int fillLogId()
	 * @method \Bitrix\Main\Type\DateTime getLogUpdate()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex setLogUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $logUpdate)
	 * @method bool hasLogUpdate()
	 * @method bool isLogUpdateFilled()
	 * @method bool isLogUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLogUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireLogUpdate()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex resetLogUpdate()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex unsetLogUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillLogUpdate()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex resetDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \string getItemType()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex setItemType(\string|\Bitrix\Main\DB\SqlExpression $itemType)
	 * @method bool hasItemType()
	 * @method bool isItemTypeFilled()
	 * @method bool isItemTypeChanged()
	 * @method \int getItemId()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex setItemId(\int|\Bitrix\Main\DB\SqlExpression $itemId)
	 * @method bool hasItemId()
	 * @method bool isItemIdFilled()
	 * @method bool isItemIdChanged()
	 * @method \string getContent()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex setContent(\string|\Bitrix\Main\DB\SqlExpression $content)
	 * @method bool hasContent()
	 * @method bool isContentFilled()
	 * @method bool isContentChanged()
	 * @method \string remindActualContent()
	 * @method \string requireContent()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex resetContent()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex unsetContent()
	 * @method \string fillContent()
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
	 * @method \Bitrix\Socialnetwork\EO_LogIndex set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogIndex reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogIndex unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogIndex wakeUp($data)
	 */
	class EO_LogIndex {
		/* @var \Bitrix\Socialnetwork\LogIndexTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogIndexTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogIndex_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getLogIdList()
	 * @method \int[] fillLogId()
	 * @method \Bitrix\Main\Type\DateTime[] getLogUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLogUpdate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \string[] getItemTypeList()
	 * @method \int[] getItemIdList()
	 * @method \string[] getContentList()
	 * @method \string[] fillContent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogIndex $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogIndex $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogIndex getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogIndex[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogIndex $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogIndex_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogIndex current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogIndex_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogIndexTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogIndexTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogIndex_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogIndex_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogIndex fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex_Collection fetchCollection()
	 */
	class EO_LogIndex_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogIndex createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogIndex_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogIndex wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogIndex_Collection wakeUpCollection($rows)
	 */
	class EO_LogIndex_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogPageTable:socialnetwork\lib\logpage.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogPage
	 * @see \Bitrix\Socialnetwork\LogPageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_LogPage setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Socialnetwork\EO_LogPage setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string getGroupCode()
	 * @method \Bitrix\Socialnetwork\EO_LogPage setGroupCode(\string|\Bitrix\Main\DB\SqlExpression $groupCode)
	 * @method bool hasGroupCode()
	 * @method bool isGroupCodeFilled()
	 * @method bool isGroupCodeChanged()
	 * @method \int getPageSize()
	 * @method \Bitrix\Socialnetwork\EO_LogPage setPageSize(\int|\Bitrix\Main\DB\SqlExpression $pageSize)
	 * @method bool hasPageSize()
	 * @method bool isPageSizeFilled()
	 * @method bool isPageSizeChanged()
	 * @method \int getPageNum()
	 * @method \Bitrix\Socialnetwork\EO_LogPage setPageNum(\int|\Bitrix\Main\DB\SqlExpression $pageNum)
	 * @method bool hasPageNum()
	 * @method bool isPageNumFilled()
	 * @method bool isPageNumChanged()
	 * @method \Bitrix\Main\Type\DateTime getPageLastDate()
	 * @method \Bitrix\Socialnetwork\EO_LogPage setPageLastDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $pageLastDate)
	 * @method bool hasPageLastDate()
	 * @method bool isPageLastDateFilled()
	 * @method bool isPageLastDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualPageLastDate()
	 * @method \Bitrix\Main\Type\DateTime requirePageLastDate()
	 * @method \Bitrix\Socialnetwork\EO_LogPage resetPageLastDate()
	 * @method \Bitrix\Socialnetwork\EO_LogPage unsetPageLastDate()
	 * @method \Bitrix\Main\Type\DateTime fillPageLastDate()
	 * @method \int getTrafficAvg()
	 * @method \Bitrix\Socialnetwork\EO_LogPage setTrafficAvg(\int|\Bitrix\Main\DB\SqlExpression $trafficAvg)
	 * @method bool hasTrafficAvg()
	 * @method bool isTrafficAvgFilled()
	 * @method bool isTrafficAvgChanged()
	 * @method \int remindActualTrafficAvg()
	 * @method \int requireTrafficAvg()
	 * @method \Bitrix\Socialnetwork\EO_LogPage resetTrafficAvg()
	 * @method \Bitrix\Socialnetwork\EO_LogPage unsetTrafficAvg()
	 * @method \int fillTrafficAvg()
	 * @method \int getTrafficCnt()
	 * @method \Bitrix\Socialnetwork\EO_LogPage setTrafficCnt(\int|\Bitrix\Main\DB\SqlExpression $trafficCnt)
	 * @method bool hasTrafficCnt()
	 * @method bool isTrafficCntFilled()
	 * @method bool isTrafficCntChanged()
	 * @method \int remindActualTrafficCnt()
	 * @method \int requireTrafficCnt()
	 * @method \Bitrix\Socialnetwork\EO_LogPage resetTrafficCnt()
	 * @method \Bitrix\Socialnetwork\EO_LogPage unsetTrafficCnt()
	 * @method \int fillTrafficCnt()
	 * @method \Bitrix\Main\Type\DateTime getTrafficLastDate()
	 * @method \Bitrix\Socialnetwork\EO_LogPage setTrafficLastDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $trafficLastDate)
	 * @method bool hasTrafficLastDate()
	 * @method bool isTrafficLastDateFilled()
	 * @method bool isTrafficLastDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTrafficLastDate()
	 * @method \Bitrix\Main\Type\DateTime requireTrafficLastDate()
	 * @method \Bitrix\Socialnetwork\EO_LogPage resetTrafficLastDate()
	 * @method \Bitrix\Socialnetwork\EO_LogPage unsetTrafficLastDate()
	 * @method \Bitrix\Main\Type\DateTime fillTrafficLastDate()
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
	 * @method \Bitrix\Socialnetwork\EO_LogPage set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogPage reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogPage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogPage wakeUp($data)
	 */
	class EO_LogPage {
		/* @var \Bitrix\Socialnetwork\LogPageTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogPageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogPage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] getGroupCodeList()
	 * @method \int[] getPageSizeList()
	 * @method \int[] getPageNumList()
	 * @method \Bitrix\Main\Type\DateTime[] getPageLastDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillPageLastDate()
	 * @method \int[] getTrafficAvgList()
	 * @method \int[] fillTrafficAvg()
	 * @method \int[] getTrafficCntList()
	 * @method \int[] fillTrafficCnt()
	 * @method \Bitrix\Main\Type\DateTime[] getTrafficLastDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTrafficLastDate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogPage $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogPage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogPage getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogPage[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogPage $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogPage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogPage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogPage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogPageTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogPageTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogPage_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogPage fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogPage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogPage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogPage fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogPage_Collection fetchCollection()
	 */
	class EO_LogPage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogPage createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogPage_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogPage wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogPage_Collection wakeUpCollection($rows)
	 */
	class EO_LogPage_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogPinnedTable:socialnetwork\lib\logpinned.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogPinned
	 * @see \Bitrix\Socialnetwork\LogPinnedTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogPinned setLogId(\int|\Bitrix\Main\DB\SqlExpression $logId)
	 * @method bool hasLogId()
	 * @method bool isLogIdFilled()
	 * @method bool isLogIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_LogPinned setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getPinnedDate()
	 * @method \Bitrix\Socialnetwork\EO_LogPinned setPinnedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $pinnedDate)
	 * @method bool hasPinnedDate()
	 * @method bool isPinnedDateFilled()
	 * @method bool isPinnedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualPinnedDate()
	 * @method \Bitrix\Main\Type\DateTime requirePinnedDate()
	 * @method \Bitrix\Socialnetwork\EO_LogPinned resetPinnedDate()
	 * @method \Bitrix\Socialnetwork\EO_LogPinned unsetPinnedDate()
	 * @method \Bitrix\Main\Type\DateTime fillPinnedDate()
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
	 * @method \Bitrix\Socialnetwork\EO_LogPinned set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogPinned reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogPinned unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogPinned wakeUp($data)
	 */
	class EO_LogPinned {
		/* @var \Bitrix\Socialnetwork\LogPinnedTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogPinnedTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogPinned_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getLogIdList()
	 * @method \int[] getUserIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getPinnedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillPinnedDate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogPinned $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogPinned $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogPinned getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogPinned[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogPinned $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogPinned_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogPinned current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogPinned_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogPinnedTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogPinnedTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogPinned_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogPinned fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogPinned_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogPinned_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogPinned fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogPinned_Collection fetchCollection()
	 */
	class EO_LogPinned_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogPinned createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogPinned_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogPinned wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogPinned_Collection wakeUpCollection($rows)
	 */
	class EO_LogPinned_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogRightTable:socialnetwork\lib\logright.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogRight
	 * @see \Bitrix\Socialnetwork\LogRightTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_LogRight setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogRight setLogId(\int|\Bitrix\Main\DB\SqlExpression $logId)
	 * @method bool hasLogId()
	 * @method bool isLogIdFilled()
	 * @method bool isLogIdChanged()
	 * @method \Bitrix\Socialnetwork\EO_Log getLog()
	 * @method \Bitrix\Socialnetwork\EO_Log remindActualLog()
	 * @method \Bitrix\Socialnetwork\EO_Log requireLog()
	 * @method \Bitrix\Socialnetwork\EO_LogRight setLog(\Bitrix\Socialnetwork\EO_Log $object)
	 * @method \Bitrix\Socialnetwork\EO_LogRight resetLog()
	 * @method \Bitrix\Socialnetwork\EO_LogRight unsetLog()
	 * @method bool hasLog()
	 * @method bool isLogFilled()
	 * @method bool isLogChanged()
	 * @method \Bitrix\Socialnetwork\EO_Log fillLog()
	 * @method \string getGroupCode()
	 * @method \Bitrix\Socialnetwork\EO_LogRight setGroupCode(\string|\Bitrix\Main\DB\SqlExpression $groupCode)
	 * @method bool hasGroupCode()
	 * @method bool isGroupCodeFilled()
	 * @method bool isGroupCodeChanged()
	 * @method \string remindActualGroupCode()
	 * @method \string requireGroupCode()
	 * @method \Bitrix\Socialnetwork\EO_LogRight resetGroupCode()
	 * @method \Bitrix\Socialnetwork\EO_LogRight unsetGroupCode()
	 * @method \string fillGroupCode()
	 * @method \Bitrix\Main\Type\DateTime getLogUpdate()
	 * @method \Bitrix\Socialnetwork\EO_LogRight setLogUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $logUpdate)
	 * @method bool hasLogUpdate()
	 * @method bool isLogUpdateFilled()
	 * @method bool isLogUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLogUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireLogUpdate()
	 * @method \Bitrix\Socialnetwork\EO_LogRight resetLogUpdate()
	 * @method \Bitrix\Socialnetwork\EO_LogRight unsetLogUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillLogUpdate()
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
	 * @method \Bitrix\Socialnetwork\EO_LogRight set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogRight reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogRight unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogRight wakeUp($data)
	 */
	class EO_LogRight {
		/* @var \Bitrix\Socialnetwork\LogRightTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogRightTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogRight_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getLogIdList()
	 * @method \Bitrix\Socialnetwork\EO_Log[] getLogList()
	 * @method \Bitrix\Socialnetwork\EO_LogRight_Collection getLogCollection()
	 * @method \Bitrix\Socialnetwork\EO_Log_Collection fillLog()
	 * @method \string[] getGroupCodeList()
	 * @method \string[] fillGroupCode()
	 * @method \Bitrix\Main\Type\DateTime[] getLogUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLogUpdate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogRight $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogRight $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogRight getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogRight[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogRight $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogRight_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogRight current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogRight_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogRightTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogRightTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogRight_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogRight fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogRight_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogRight_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogRight fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogRight_Collection fetchCollection()
	 */
	class EO_LogRight_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogRight createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogRight_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogRight wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogRight_Collection wakeUpCollection($rows)
	 */
	class EO_LogRight_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogSiteTable:socialnetwork\lib\logsite.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogSite
	 * @see \Bitrix\Socialnetwork\LogSiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogSite setLogId(\int|\Bitrix\Main\DB\SqlExpression $logId)
	 * @method bool hasLogId()
	 * @method bool isLogIdFilled()
	 * @method bool isLogIdChanged()
	 * @method \Bitrix\Socialnetwork\EO_Log getLog()
	 * @method \Bitrix\Socialnetwork\EO_Log remindActualLog()
	 * @method \Bitrix\Socialnetwork\EO_Log requireLog()
	 * @method \Bitrix\Socialnetwork\EO_LogSite setLog(\Bitrix\Socialnetwork\EO_Log $object)
	 * @method \Bitrix\Socialnetwork\EO_LogSite resetLog()
	 * @method \Bitrix\Socialnetwork\EO_LogSite unsetLog()
	 * @method bool hasLog()
	 * @method bool isLogFilled()
	 * @method bool isLogChanged()
	 * @method \Bitrix\Socialnetwork\EO_Log fillLog()
	 * @method \string getSiteId()
	 * @method \Bitrix\Socialnetwork\EO_LogSite setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
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
	 * @method \Bitrix\Socialnetwork\EO_LogSite set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogSite reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogSite unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogSite wakeUp($data)
	 */
	class EO_LogSite {
		/* @var \Bitrix\Socialnetwork\LogSiteTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogSiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogSite_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getLogIdList()
	 * @method \Bitrix\Socialnetwork\EO_Log[] getLogList()
	 * @method \Bitrix\Socialnetwork\EO_LogSite_Collection getLogCollection()
	 * @method \Bitrix\Socialnetwork\EO_Log_Collection fillLog()
	 * @method \string[] getSiteIdList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogSite $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogSite $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogSite getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogSite[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogSite $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogSite_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogSite current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogSite_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogSiteTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogSiteTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogSite_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogSite fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogSite_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogSite_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogSite fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogSite_Collection fetchCollection()
	 */
	class EO_LogSite_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogSite createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogSite_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogSite wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogSite_Collection wakeUpCollection($rows)
	 */
	class EO_LogSite_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogSubscribeTable:socialnetwork\lib\logsubscribe.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogSubscribe
	 * @see \Bitrix\Socialnetwork\LogSubscribeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int getLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe setLogId(\int|\Bitrix\Main\DB\SqlExpression $logId)
	 * @method bool hasLogId()
	 * @method bool isLogIdFilled()
	 * @method bool isLogIdChanged()
	 * @method \string getType()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \Bitrix\Main\Type\DateTime getEndDate()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe setEndDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $endDate)
	 * @method bool hasEndDate()
	 * @method bool isEndDateFilled()
	 * @method bool isEndDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualEndDate()
	 * @method \Bitrix\Main\Type\DateTime requireEndDate()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe resetEndDate()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe unsetEndDate()
	 * @method \Bitrix\Main\Type\DateTime fillEndDate()
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
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogSubscribe wakeUp($data)
	 */
	class EO_LogSubscribe {
		/* @var \Bitrix\Socialnetwork\LogSubscribeTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogSubscribeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogSubscribe_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \int[] getLogIdList()
	 * @method \string[] getTypeList()
	 * @method \Bitrix\Main\Type\DateTime[] getEndDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillEndDate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogSubscribe $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogSubscribe $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogSubscribe $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogSubscribe_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogSubscribe_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogSubscribeTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogSubscribeTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogSubscribe_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogSubscribe_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe_Collection fetchCollection()
	 */
	class EO_LogSubscribe_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogSubscribe_Collection wakeUpCollection($rows)
	 */
	class EO_LogSubscribe_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogTagTable:socialnetwork\lib\logtag.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogTag
	 * @see \Bitrix\Socialnetwork\LogTagTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogTag setLogId(\int|\Bitrix\Main\DB\SqlExpression $logId)
	 * @method bool hasLogId()
	 * @method bool isLogIdFilled()
	 * @method bool isLogIdChanged()
	 * @method \int remindActualLogId()
	 * @method \int requireLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogTag resetLogId()
	 * @method \Bitrix\Socialnetwork\EO_LogTag unsetLogId()
	 * @method \int fillLogId()
	 * @method \Bitrix\Socialnetwork\EO_Log getLog()
	 * @method \Bitrix\Socialnetwork\EO_Log remindActualLog()
	 * @method \Bitrix\Socialnetwork\EO_Log requireLog()
	 * @method \Bitrix\Socialnetwork\EO_LogTag setLog(\Bitrix\Socialnetwork\EO_Log $object)
	 * @method \Bitrix\Socialnetwork\EO_LogTag resetLog()
	 * @method \Bitrix\Socialnetwork\EO_LogTag unsetLog()
	 * @method bool hasLog()
	 * @method bool isLogFilled()
	 * @method bool isLogChanged()
	 * @method \Bitrix\Socialnetwork\EO_Log fillLog()
	 * @method \string getItemType()
	 * @method \Bitrix\Socialnetwork\EO_LogTag setItemType(\string|\Bitrix\Main\DB\SqlExpression $itemType)
	 * @method bool hasItemType()
	 * @method bool isItemTypeFilled()
	 * @method bool isItemTypeChanged()
	 * @method \string getItemId()
	 * @method \Bitrix\Socialnetwork\EO_LogTag setItemId(\string|\Bitrix\Main\DB\SqlExpression $itemId)
	 * @method bool hasItemId()
	 * @method bool isItemIdFilled()
	 * @method bool isItemIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Socialnetwork\EO_LogTag setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
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
	 * @method \Bitrix\Socialnetwork\EO_LogTag set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogTag reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogTag unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogTag wakeUp($data)
	 */
	class EO_LogTag {
		/* @var \Bitrix\Socialnetwork\LogTagTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogTagTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogTag_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getLogIdList()
	 * @method \int[] fillLogId()
	 * @method \Bitrix\Socialnetwork\EO_Log[] getLogList()
	 * @method \Bitrix\Socialnetwork\EO_LogTag_Collection getLogCollection()
	 * @method \Bitrix\Socialnetwork\EO_Log_Collection fillLog()
	 * @method \string[] getItemTypeList()
	 * @method \string[] getItemIdList()
	 * @method \string[] getNameList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogTag $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogTag $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogTag getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogTag[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogTag $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogTag_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogTag current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogTag_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogTagTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogTagTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogTag_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogTag fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogTag_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogTag_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogTag fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogTag_Collection fetchCollection()
	 */
	class EO_LogTag_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogTag createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogTag_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogTag wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogTag_Collection wakeUpCollection($rows)
	 */
	class EO_LogTag_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\LogViewTable:socialnetwork\lib\logview.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogView
	 * @see \Bitrix\Socialnetwork\LogViewTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_LogView setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getEventId()
	 * @method \Bitrix\Socialnetwork\EO_LogView setEventId(\string|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \boolean getType()
	 * @method \Bitrix\Socialnetwork\EO_LogView setType(\boolean|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \boolean remindActualType()
	 * @method \boolean requireType()
	 * @method \Bitrix\Socialnetwork\EO_LogView resetType()
	 * @method \Bitrix\Socialnetwork\EO_LogView unsetType()
	 * @method \boolean fillType()
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
	 * @method \Bitrix\Socialnetwork\EO_LogView set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_LogView reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_LogView unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_LogView wakeUp($data)
	 */
	class EO_LogView {
		/* @var \Bitrix\Socialnetwork\LogViewTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogViewTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_LogView_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getEventIdList()
	 * @method \boolean[] getTypeList()
	 * @method \boolean[] fillType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_LogView $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_LogView $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogView getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_LogView[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_LogView $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_LogView_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_LogView current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LogView_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\LogViewTable */
		static public $dataClass = '\Bitrix\Socialnetwork\LogViewTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LogView_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_LogView fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogView_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LogView_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogView fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_LogView_Collection fetchCollection()
	 */
	class EO_LogView_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_LogView createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_LogView_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_LogView wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_LogView_Collection wakeUpCollection($rows)
	 */
	class EO_LogView_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\UserContentViewTable:socialnetwork\lib\usercontentview.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserContentView
	 * @see \Bitrix\Socialnetwork\UserContentViewTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_UserContentView resetUser()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \string getRatingTypeId()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView setRatingTypeId(\string|\Bitrix\Main\DB\SqlExpression $ratingTypeId)
	 * @method bool hasRatingTypeId()
	 * @method bool isRatingTypeIdFilled()
	 * @method bool isRatingTypeIdChanged()
	 * @method \int getRatingEntityId()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView setRatingEntityId(\int|\Bitrix\Main\DB\SqlExpression $ratingEntityId)
	 * @method bool hasRatingEntityId()
	 * @method bool isRatingEntityIdFilled()
	 * @method bool isRatingEntityIdChanged()
	 * @method \string getContentId()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView setContentId(\string|\Bitrix\Main\DB\SqlExpression $contentId)
	 * @method bool hasContentId()
	 * @method bool isContentIdFilled()
	 * @method bool isContentIdChanged()
	 * @method \string remindActualContentId()
	 * @method \string requireContentId()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView resetContentId()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView unsetContentId()
	 * @method \string fillContentId()
	 * @method \Bitrix\Main\Type\DateTime getDateView()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView setDateView(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateView)
	 * @method bool hasDateView()
	 * @method bool isDateViewFilled()
	 * @method bool isDateViewChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateView()
	 * @method \Bitrix\Main\Type\DateTime requireDateView()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView resetDateView()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView unsetDateView()
	 * @method \Bitrix\Main\Type\DateTime fillDateView()
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
	 * @method \Bitrix\Socialnetwork\EO_UserContentView set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_UserContentView reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_UserContentView unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_UserContentView wakeUp($data)
	 */
	class EO_UserContentView {
		/* @var \Bitrix\Socialnetwork\UserContentViewTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserContentViewTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserContentView_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \string[] getRatingTypeIdList()
	 * @method \int[] getRatingEntityIdList()
	 * @method \string[] getContentIdList()
	 * @method \string[] fillContentId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateViewList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateView()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_UserContentView $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_UserContentView $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserContentView getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserContentView[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_UserContentView $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_UserContentView_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_UserContentView current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserContentView_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\UserContentViewTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserContentViewTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserContentView_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserContentView_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserContentView fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView_Collection fetchCollection()
	 */
	class EO_UserContentView_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserContentView createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_UserContentView_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_UserContentView wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_UserContentView_Collection wakeUpCollection($rows)
	 */
	class EO_UserContentView_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\UserRelationsTable:socialnetwork\lib\userrelations.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserRelations
	 * @see \Bitrix\Socialnetwork\UserRelationsTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getFirstUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setFirstUserId(\int|\Bitrix\Main\DB\SqlExpression $firstUserId)
	 * @method bool hasFirstUserId()
	 * @method bool isFirstUserIdFilled()
	 * @method bool isFirstUserIdChanged()
	 * @method \int remindActualFirstUserId()
	 * @method \int requireFirstUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations resetFirstUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unsetFirstUserId()
	 * @method \int fillFirstUserId()
	 * @method \int getSecondUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setSecondUserId(\int|\Bitrix\Main\DB\SqlExpression $secondUserId)
	 * @method bool hasSecondUserId()
	 * @method bool isSecondUserIdFilled()
	 * @method bool isSecondUserIdChanged()
	 * @method \int remindActualSecondUserId()
	 * @method \int requireSecondUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations resetSecondUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unsetSecondUserId()
	 * @method \int fillSecondUserId()
	 * @method \string getRelation()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setRelation(\string|\Bitrix\Main\DB\SqlExpression $relation)
	 * @method bool hasRelation()
	 * @method bool isRelationFilled()
	 * @method bool isRelationChanged()
	 * @method \string remindActualRelation()
	 * @method \string requireRelation()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations resetRelation()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unsetRelation()
	 * @method \string fillRelation()
	 * @method \string getInitiatedBy()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setInitiatedBy(\string|\Bitrix\Main\DB\SqlExpression $initiatedBy)
	 * @method bool hasInitiatedBy()
	 * @method bool isInitiatedByFilled()
	 * @method bool isInitiatedByChanged()
	 * @method \string remindActualInitiatedBy()
	 * @method \string requireInitiatedBy()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations resetInitiatedBy()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unsetInitiatedBy()
	 * @method \string fillInitiatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations resetDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations resetDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \string getMessage()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations resetMessage()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unsetMessage()
	 * @method \string fillMessage()
	 * @method \Bitrix\Main\EO_User getFirstUser()
	 * @method \Bitrix\Main\EO_User remindActualFirstUser()
	 * @method \Bitrix\Main\EO_User requireFirstUser()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setFirstUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_UserRelations resetFirstUser()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unsetFirstUser()
	 * @method bool hasFirstUser()
	 * @method bool isFirstUserFilled()
	 * @method bool isFirstUserChanged()
	 * @method \Bitrix\Main\EO_User fillFirstUser()
	 * @method \Bitrix\Main\EO_User getSecondUser()
	 * @method \Bitrix\Main\EO_User remindActualSecondUser()
	 * @method \Bitrix\Main\EO_User requireSecondUser()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations setSecondUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_UserRelations resetSecondUser()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unsetSecondUser()
	 * @method bool hasSecondUser()
	 * @method bool isSecondUserFilled()
	 * @method bool isSecondUserChanged()
	 * @method \Bitrix\Main\EO_User fillSecondUser()
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
	 * @method \Bitrix\Socialnetwork\EO_UserRelations set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_UserRelations reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_UserRelations unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_UserRelations wakeUp($data)
	 */
	class EO_UserRelations {
		/* @var \Bitrix\Socialnetwork\UserRelationsTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserRelationsTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserRelations_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getFirstUserIdList()
	 * @method \int[] fillFirstUserId()
	 * @method \int[] getSecondUserIdList()
	 * @method \int[] fillSecondUserId()
	 * @method \string[] getRelationList()
	 * @method \string[] fillRelation()
	 * @method \string[] getInitiatedByList()
	 * @method \string[] fillInitiatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \Bitrix\Main\EO_User[] getFirstUserList()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations_Collection getFirstUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillFirstUser()
	 * @method \Bitrix\Main\EO_User[] getSecondUserList()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations_Collection getSecondUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillSecondUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_UserRelations $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_UserRelations $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserRelations getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserRelations[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_UserRelations $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_UserRelations_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_UserRelations current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserRelations_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\UserRelationsTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserRelationsTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserRelations_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserRelations_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserRelations fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations_Collection fetchCollection()
	 */
	class EO_UserRelations_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserRelations createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_UserRelations_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_UserRelations wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_UserRelations_Collection wakeUpCollection($rows)
	 */
	class EO_UserRelations_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\UserTagTable:socialnetwork\lib\usertag.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserTag
	 * @see \Bitrix\Socialnetwork\UserTagTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserTag setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialnetwork\EO_UserTag setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_UserTag resetUser()
	 * @method \Bitrix\Socialnetwork\EO_UserTag unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \string getName()
	 * @method \Bitrix\Socialnetwork\EO_UserTag setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
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
	 * @method \Bitrix\Socialnetwork\EO_UserTag set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_UserTag reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_UserTag unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_UserTag wakeUp($data)
	 */
	class EO_UserTag {
		/* @var \Bitrix\Socialnetwork\UserTagTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserTagTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserTag_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialnetwork\EO_UserTag_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \string[] getNameList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_UserTag $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_UserTag $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserTag getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserTag[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_UserTag $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_UserTag_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_UserTag current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserTag_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\UserTagTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserTagTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserTag_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_UserTag fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserTag_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserTag_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserTag fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserTag_Collection fetchCollection()
	 */
	class EO_UserTag_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserTag createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_UserTag_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_UserTag wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_UserTag_Collection wakeUpCollection($rows)
	 */
	class EO_UserTag_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\UserToGroupTable:socialnetwork\lib\usertogroup.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserToGroup
	 * @see \Bitrix\Socialnetwork\UserToGroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Intranet\EO_User getUser()
	 * @method \Bitrix\Intranet\EO_User remindActualUser()
	 * @method \Bitrix\Intranet\EO_User requireUser()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setUser(\Bitrix\Intranet\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetUser()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Intranet\EO_User fillUser()
	 * @method \int getGroupId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetGroupId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup getGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup remindActualGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup requireGroup()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setGroup(\Bitrix\Socialnetwork\EO_Workgroup $object)
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetGroup()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup fillGroup()
	 * @method \string getRole()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setRole(\string|\Bitrix\Main\DB\SqlExpression $role)
	 * @method bool hasRole()
	 * @method bool isRoleFilled()
	 * @method bool isRoleChanged()
	 * @method \string remindActualRole()
	 * @method \string requireRole()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetRole()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetRole()
	 * @method \string fillRole()
	 * @method \boolean getAutoMember()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setAutoMember(\boolean|\Bitrix\Main\DB\SqlExpression $autoMember)
	 * @method bool hasAutoMember()
	 * @method bool isAutoMemberFilled()
	 * @method bool isAutoMemberChanged()
	 * @method \boolean remindActualAutoMember()
	 * @method \boolean requireAutoMember()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetAutoMember()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetAutoMember()
	 * @method \boolean fillAutoMember()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \string getInitiatedByType()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setInitiatedByType(\string|\Bitrix\Main\DB\SqlExpression $initiatedByType)
	 * @method bool hasInitiatedByType()
	 * @method bool isInitiatedByTypeFilled()
	 * @method bool isInitiatedByTypeChanged()
	 * @method \string remindActualInitiatedByType()
	 * @method \string requireInitiatedByType()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetInitiatedByType()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetInitiatedByType()
	 * @method \string fillInitiatedByType()
	 * @method \int getInitiatedByUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setInitiatedByUserId(\int|\Bitrix\Main\DB\SqlExpression $initiatedByUserId)
	 * @method bool hasInitiatedByUserId()
	 * @method bool isInitiatedByUserIdFilled()
	 * @method bool isInitiatedByUserIdChanged()
	 * @method \int remindActualInitiatedByUserId()
	 * @method \int requireInitiatedByUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetInitiatedByUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetInitiatedByUserId()
	 * @method \int fillInitiatedByUserId()
	 * @method \Bitrix\Main\EO_User getInitiatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualInitiatedByUser()
	 * @method \Bitrix\Main\EO_User requireInitiatedByUser()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setInitiatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetInitiatedByUser()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetInitiatedByUser()
	 * @method bool hasInitiatedByUser()
	 * @method bool isInitiatedByUserFilled()
	 * @method bool isInitiatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillInitiatedByUser()
	 * @method \string getMessage()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup resetMessage()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unsetMessage()
	 * @method \string fillMessage()
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
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_UserToGroup wakeUp($data)
	 */
	class EO_UserToGroup {
		/* @var \Bitrix\Socialnetwork\UserToGroupTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserToGroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserToGroup_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Intranet\EO_User[] getUserList()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup_Collection getUserCollection()
	 * @method \Bitrix\Intranet\EO_User_Collection fillUser()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup[] getGroupList()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup_Collection getGroupCollection()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection fillGroup()
	 * @method \string[] getRoleList()
	 * @method \string[] fillRole()
	 * @method \boolean[] getAutoMemberList()
	 * @method \boolean[] fillAutoMember()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \string[] getInitiatedByTypeList()
	 * @method \string[] fillInitiatedByType()
	 * @method \int[] getInitiatedByUserIdList()
	 * @method \int[] fillInitiatedByUserId()
	 * @method \Bitrix\Main\EO_User[] getInitiatedByUserList()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup_Collection getInitiatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillInitiatedByUser()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_UserToGroup $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_UserToGroup $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_UserToGroup $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_UserToGroup_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserToGroup_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\UserToGroupTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserToGroupTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserToGroup_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserToGroup_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup_Collection fetchCollection()
	 */
	class EO_UserToGroup_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_UserToGroup_Collection wakeUpCollection($rows)
	 */
	class EO_UserToGroup_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\UserWelltoryTable:socialnetwork\lib\userwelltory.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserWelltory
	 * @see \Bitrix\Socialnetwork\UserWelltoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory resetUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory resetUser()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \int getStress()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory setStress(\int|\Bitrix\Main\DB\SqlExpression $stress)
	 * @method bool hasStress()
	 * @method bool isStressFilled()
	 * @method bool isStressChanged()
	 * @method \int remindActualStress()
	 * @method \int requireStress()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory resetStress()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory unsetStress()
	 * @method \int fillStress()
	 * @method \string getStressType()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory setStressType(\string|\Bitrix\Main\DB\SqlExpression $stressType)
	 * @method bool hasStressType()
	 * @method bool isStressTypeFilled()
	 * @method bool isStressTypeChanged()
	 * @method \string remindActualStressType()
	 * @method \string requireStressType()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory resetStressType()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory unsetStressType()
	 * @method \string fillStressType()
	 * @method \string getStressComment()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory setStressComment(\string|\Bitrix\Main\DB\SqlExpression $stressComment)
	 * @method bool hasStressComment()
	 * @method bool isStressCommentFilled()
	 * @method bool isStressCommentChanged()
	 * @method \string remindActualStressComment()
	 * @method \string requireStressComment()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory resetStressComment()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory unsetStressComment()
	 * @method \string fillStressComment()
	 * @method \Bitrix\Main\Type\DateTime getDateMeasure()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory setDateMeasure(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateMeasure)
	 * @method bool hasDateMeasure()
	 * @method bool isDateMeasureFilled()
	 * @method bool isDateMeasureChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateMeasure()
	 * @method \Bitrix\Main\Type\DateTime requireDateMeasure()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory resetDateMeasure()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory unsetDateMeasure()
	 * @method \Bitrix\Main\Type\DateTime fillDateMeasure()
	 * @method \string getHash()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory setHash(\string|\Bitrix\Main\DB\SqlExpression $hash)
	 * @method bool hasHash()
	 * @method bool isHashFilled()
	 * @method bool isHashChanged()
	 * @method \string remindActualHash()
	 * @method \string requireHash()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory resetHash()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory unsetHash()
	 * @method \string fillHash()
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
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_UserWelltory wakeUp($data)
	 */
	class EO_UserWelltory {
		/* @var \Bitrix\Socialnetwork\UserWelltoryTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserWelltoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserWelltory_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \int[] getStressList()
	 * @method \int[] fillStress()
	 * @method \string[] getStressTypeList()
	 * @method \string[] fillStressType()
	 * @method \string[] getStressCommentList()
	 * @method \string[] fillStressComment()
	 * @method \Bitrix\Main\Type\DateTime[] getDateMeasureList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateMeasure()
	 * @method \string[] getHashList()
	 * @method \string[] fillHash()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_UserWelltory $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_UserWelltory $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_UserWelltory $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_UserWelltory_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserWelltory_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\UserWelltoryTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserWelltoryTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserWelltory_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserWelltory_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory_Collection fetchCollection()
	 */
	class EO_UserWelltory_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltory_Collection wakeUpCollection($rows)
	 */
	class EO_UserWelltory_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\UserWelltoryDisclaimerTable:socialnetwork\lib\userwelltorydisclaimer.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserWelltoryDisclaimer
	 * @see \Bitrix\Socialnetwork\UserWelltoryDisclaimerTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer resetUserId()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer resetUser()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Main\Type\DateTime getDateSigned()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer setDateSigned(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateSigned)
	 * @method bool hasDateSigned()
	 * @method bool isDateSignedFilled()
	 * @method bool isDateSignedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateSigned()
	 * @method \Bitrix\Main\Type\DateTime requireDateSigned()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer resetDateSigned()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer unsetDateSigned()
	 * @method \Bitrix\Main\Type\DateTime fillDateSigned()
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
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer wakeUp($data)
	 */
	class EO_UserWelltoryDisclaimer {
		/* @var \Bitrix\Socialnetwork\UserWelltoryDisclaimerTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserWelltoryDisclaimerTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_UserWelltoryDisclaimer_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Main\Type\DateTime[] getDateSignedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateSigned()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_UserWelltoryDisclaimer_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\UserWelltoryDisclaimerTable */
		static public $dataClass = '\Bitrix\Socialnetwork\UserWelltoryDisclaimerTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserWelltoryDisclaimer_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserWelltoryDisclaimer_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer_Collection fetchCollection()
	 */
	class EO_UserWelltoryDisclaimer_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer_Collection wakeUpCollection($rows)
	 */
	class EO_UserWelltoryDisclaimer_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\WorkgroupTable:socialnetwork\lib\workgroup.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_Workgroup
	 * @see \Bitrix\Socialnetwork\WorkgroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \boolean getActive()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetActive()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getSiteId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetSiteId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \int getSubjectId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setSubjectId(\int|\Bitrix\Main\DB\SqlExpression $subjectId)
	 * @method bool hasSubjectId()
	 * @method bool isSubjectIdFilled()
	 * @method bool isSubjectIdChanged()
	 * @method \int remindActualSubjectId()
	 * @method \int requireSubjectId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetSubjectId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetSubjectId()
	 * @method \int fillSubjectId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject getWorkgroupSubject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject remindActualWorkgroupSubject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject requireWorkgroupSubject()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setWorkgroupSubject(\Bitrix\Socialnetwork\EO_WorkgroupSubject $object)
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetWorkgroupSubject()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetWorkgroupSubject()
	 * @method bool hasWorkgroupSubject()
	 * @method bool isWorkgroupSubjectFilled()
	 * @method bool isWorkgroupSubjectChanged()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject fillWorkgroupSubject()
	 * @method \string getName()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetName()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetDescription()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getKeywords()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setKeywords(\string|\Bitrix\Main\DB\SqlExpression $keywords)
	 * @method bool hasKeywords()
	 * @method bool isKeywordsFilled()
	 * @method bool isKeywordsChanged()
	 * @method \string remindActualKeywords()
	 * @method \string requireKeywords()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetKeywords()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetKeywords()
	 * @method \string fillKeywords()
	 * @method \boolean getClosed()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setClosed(\boolean|\Bitrix\Main\DB\SqlExpression $closed)
	 * @method bool hasClosed()
	 * @method bool isClosedFilled()
	 * @method bool isClosedChanged()
	 * @method \boolean remindActualClosed()
	 * @method \boolean requireClosed()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetClosed()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetClosed()
	 * @method \boolean fillClosed()
	 * @method \boolean getVisible()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setVisible(\boolean|\Bitrix\Main\DB\SqlExpression $visible)
	 * @method bool hasVisible()
	 * @method bool isVisibleFilled()
	 * @method bool isVisibleChanged()
	 * @method \boolean remindActualVisible()
	 * @method \boolean requireVisible()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetVisible()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetVisible()
	 * @method \boolean fillVisible()
	 * @method \boolean getOpened()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setOpened(\boolean|\Bitrix\Main\DB\SqlExpression $opened)
	 * @method bool hasOpened()
	 * @method bool isOpenedFilled()
	 * @method bool isOpenedChanged()
	 * @method \boolean remindActualOpened()
	 * @method \boolean requireOpened()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetOpened()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetOpened()
	 * @method \boolean fillOpened()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetDateCreate()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetDateUpdate()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime getDateActivity()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setDateActivity(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateActivity)
	 * @method bool hasDateActivity()
	 * @method bool isDateActivityFilled()
	 * @method bool isDateActivityChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateActivity()
	 * @method \Bitrix\Main\Type\DateTime requireDateActivity()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetDateActivity()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetDateActivity()
	 * @method \Bitrix\Main\Type\DateTime fillDateActivity()
	 * @method \int getImageId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setImageId(\int|\Bitrix\Main\DB\SqlExpression $imageId)
	 * @method bool hasImageId()
	 * @method bool isImageIdFilled()
	 * @method bool isImageIdChanged()
	 * @method \int remindActualImageId()
	 * @method \int requireImageId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetImageId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetImageId()
	 * @method \int fillImageId()
	 * @method \string getAvatarType()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setAvatarType(\string|\Bitrix\Main\DB\SqlExpression $avatarType)
	 * @method bool hasAvatarType()
	 * @method bool isAvatarTypeFilled()
	 * @method bool isAvatarTypeChanged()
	 * @method \string remindActualAvatarType()
	 * @method \string requireAvatarType()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetAvatarType()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetAvatarType()
	 * @method \string fillAvatarType()
	 * @method \int getOwnerId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setOwnerId(\int|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \int remindActualOwnerId()
	 * @method \int requireOwnerId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetOwnerId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetOwnerId()
	 * @method \int fillOwnerId()
	 * @method \Bitrix\Main\EO_User getWorkgroupOwner()
	 * @method \Bitrix\Main\EO_User remindActualWorkgroupOwner()
	 * @method \Bitrix\Main\EO_User requireWorkgroupOwner()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setWorkgroupOwner(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetWorkgroupOwner()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetWorkgroupOwner()
	 * @method bool hasWorkgroupOwner()
	 * @method bool isWorkgroupOwnerFilled()
	 * @method bool isWorkgroupOwnerChanged()
	 * @method \Bitrix\Main\EO_User fillWorkgroupOwner()
	 * @method \string getInitiatePerms()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setInitiatePerms(\string|\Bitrix\Main\DB\SqlExpression $initiatePerms)
	 * @method bool hasInitiatePerms()
	 * @method bool isInitiatePermsFilled()
	 * @method bool isInitiatePermsChanged()
	 * @method \string remindActualInitiatePerms()
	 * @method \string requireInitiatePerms()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetInitiatePerms()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetInitiatePerms()
	 * @method \string fillInitiatePerms()
	 * @method \int getNumberOfMembers()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setNumberOfMembers(\int|\Bitrix\Main\DB\SqlExpression $numberOfMembers)
	 * @method bool hasNumberOfMembers()
	 * @method bool isNumberOfMembersFilled()
	 * @method bool isNumberOfMembersChanged()
	 * @method \int remindActualNumberOfMembers()
	 * @method \int requireNumberOfMembers()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetNumberOfMembers()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetNumberOfMembers()
	 * @method \int fillNumberOfMembers()
	 * @method \int getNumberOfModerators()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setNumberOfModerators(\int|\Bitrix\Main\DB\SqlExpression $numberOfModerators)
	 * @method bool hasNumberOfModerators()
	 * @method bool isNumberOfModeratorsFilled()
	 * @method bool isNumberOfModeratorsChanged()
	 * @method \int remindActualNumberOfModerators()
	 * @method \int requireNumberOfModerators()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetNumberOfModerators()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetNumberOfModerators()
	 * @method \int fillNumberOfModerators()
	 * @method \boolean getProject()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setProject(\boolean|\Bitrix\Main\DB\SqlExpression $project)
	 * @method bool hasProject()
	 * @method bool isProjectFilled()
	 * @method bool isProjectChanged()
	 * @method \boolean remindActualProject()
	 * @method \boolean requireProject()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetProject()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetProject()
	 * @method \boolean fillProject()
	 * @method \Bitrix\Main\Type\DateTime getProjectDateStart()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setProjectDateStart(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $projectDateStart)
	 * @method bool hasProjectDateStart()
	 * @method bool isProjectDateStartFilled()
	 * @method bool isProjectDateStartChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualProjectDateStart()
	 * @method \Bitrix\Main\Type\DateTime requireProjectDateStart()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetProjectDateStart()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetProjectDateStart()
	 * @method \Bitrix\Main\Type\DateTime fillProjectDateStart()
	 * @method \Bitrix\Main\Type\DateTime getProjectDateFinish()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setProjectDateFinish(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $projectDateFinish)
	 * @method bool hasProjectDateFinish()
	 * @method bool isProjectDateFinishFilled()
	 * @method bool isProjectDateFinishChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualProjectDateFinish()
	 * @method \Bitrix\Main\Type\DateTime requireProjectDateFinish()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetProjectDateFinish()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetProjectDateFinish()
	 * @method \Bitrix\Main\Type\DateTime fillProjectDateFinish()
	 * @method \string getSearchIndex()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setSearchIndex(\string|\Bitrix\Main\DB\SqlExpression $searchIndex)
	 * @method bool hasSearchIndex()
	 * @method bool isSearchIndexFilled()
	 * @method bool isSearchIndexChanged()
	 * @method \string remindActualSearchIndex()
	 * @method \string requireSearchIndex()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetSearchIndex()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetSearchIndex()
	 * @method \string fillSearchIndex()
	 * @method \boolean getLanding()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setLanding(\boolean|\Bitrix\Main\DB\SqlExpression $landing)
	 * @method bool hasLanding()
	 * @method bool isLandingFilled()
	 * @method bool isLandingChanged()
	 * @method \boolean remindActualLanding()
	 * @method \boolean requireLanding()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetLanding()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetLanding()
	 * @method \boolean fillLanding()
	 * @method \int getScrumOwnerId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setScrumOwnerId(\int|\Bitrix\Main\DB\SqlExpression $scrumOwnerId)
	 * @method bool hasScrumOwnerId()
	 * @method bool isScrumOwnerIdFilled()
	 * @method bool isScrumOwnerIdChanged()
	 * @method \int remindActualScrumOwnerId()
	 * @method \int requireScrumOwnerId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetScrumOwnerId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetScrumOwnerId()
	 * @method \int fillScrumOwnerId()
	 * @method \int getScrumMasterId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setScrumMasterId(\int|\Bitrix\Main\DB\SqlExpression $scrumMasterId)
	 * @method bool hasScrumMasterId()
	 * @method bool isScrumMasterIdFilled()
	 * @method bool isScrumMasterIdChanged()
	 * @method \int remindActualScrumMasterId()
	 * @method \int requireScrumMasterId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetScrumMasterId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetScrumMasterId()
	 * @method \int fillScrumMasterId()
	 * @method \int getScrumSprintDuration()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setScrumSprintDuration(\int|\Bitrix\Main\DB\SqlExpression $scrumSprintDuration)
	 * @method bool hasScrumSprintDuration()
	 * @method bool isScrumSprintDurationFilled()
	 * @method bool isScrumSprintDurationChanged()
	 * @method \int remindActualScrumSprintDuration()
	 * @method \int requireScrumSprintDuration()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetScrumSprintDuration()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetScrumSprintDuration()
	 * @method \int fillScrumSprintDuration()
	 * @method \string getScrumTaskResponsible()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup setScrumTaskResponsible(\string|\Bitrix\Main\DB\SqlExpression $scrumTaskResponsible)
	 * @method bool hasScrumTaskResponsible()
	 * @method bool isScrumTaskResponsibleFilled()
	 * @method bool isScrumTaskResponsibleChanged()
	 * @method \string remindActualScrumTaskResponsible()
	 * @method \string requireScrumTaskResponsible()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup resetScrumTaskResponsible()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unsetScrumTaskResponsible()
	 * @method \string fillScrumTaskResponsible()
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
	 * @method \Bitrix\Socialnetwork\EO_Workgroup set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_Workgroup reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_Workgroup unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_Workgroup wakeUp($data)
	 */
	class EO_Workgroup {
		/* @var \Bitrix\Socialnetwork\WorkgroupTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_Workgroup_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \int[] getSubjectIdList()
	 * @method \int[] fillSubjectId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject[] getWorkgroupSubjectList()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection getWorkgroupSubjectCollection()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject_Collection fillWorkgroupSubject()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getKeywordsList()
	 * @method \string[] fillKeywords()
	 * @method \boolean[] getClosedList()
	 * @method \boolean[] fillClosed()
	 * @method \boolean[] getVisibleList()
	 * @method \boolean[] fillVisible()
	 * @method \boolean[] getOpenedList()
	 * @method \boolean[] fillOpened()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateActivityList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateActivity()
	 * @method \int[] getImageIdList()
	 * @method \int[] fillImageId()
	 * @method \string[] getAvatarTypeList()
	 * @method \string[] fillAvatarType()
	 * @method \int[] getOwnerIdList()
	 * @method \int[] fillOwnerId()
	 * @method \Bitrix\Main\EO_User[] getWorkgroupOwnerList()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection getWorkgroupOwnerCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillWorkgroupOwner()
	 * @method \string[] getInitiatePermsList()
	 * @method \string[] fillInitiatePerms()
	 * @method \int[] getNumberOfMembersList()
	 * @method \int[] fillNumberOfMembers()
	 * @method \int[] getNumberOfModeratorsList()
	 * @method \int[] fillNumberOfModerators()
	 * @method \boolean[] getProjectList()
	 * @method \boolean[] fillProject()
	 * @method \Bitrix\Main\Type\DateTime[] getProjectDateStartList()
	 * @method \Bitrix\Main\Type\DateTime[] fillProjectDateStart()
	 * @method \Bitrix\Main\Type\DateTime[] getProjectDateFinishList()
	 * @method \Bitrix\Main\Type\DateTime[] fillProjectDateFinish()
	 * @method \string[] getSearchIndexList()
	 * @method \string[] fillSearchIndex()
	 * @method \boolean[] getLandingList()
	 * @method \boolean[] fillLanding()
	 * @method \int[] getScrumOwnerIdList()
	 * @method \int[] fillScrumOwnerId()
	 * @method \int[] getScrumMasterIdList()
	 * @method \int[] fillScrumMasterId()
	 * @method \int[] getScrumSprintDurationList()
	 * @method \int[] fillScrumSprintDuration()
	 * @method \string[] getScrumTaskResponsibleList()
	 * @method \string[] fillScrumTaskResponsible()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_Workgroup $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_Workgroup $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_Workgroup getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_Workgroup[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_Workgroup $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_Workgroup_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_Workgroup current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Workgroup_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\WorkgroupTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Workgroup_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Workgroup_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_Workgroup fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection fetchCollection()
	 */
	class EO_Workgroup_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_Workgroup createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection wakeUpCollection($rows)
	 */
	class EO_Workgroup_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\WorkgroupFavoritesTable:socialnetwork\lib\workgroupfavorites.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupFavorites
	 * @see \Bitrix\Socialnetwork\WorkgroupFavoritesTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites resetUser()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \int getGroupId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup getGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup remindActualGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup requireGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites setGroup(\Bitrix\Socialnetwork\EO_Workgroup $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites resetGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup fillGroup()
	 * @method \Bitrix\Main\Type\DateTime getDateAdd()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites setDateAdd(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateAdd)
	 * @method bool hasDateAdd()
	 * @method bool isDateAddFilled()
	 * @method bool isDateAddChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateAdd()
	 * @method \Bitrix\Main\Type\DateTime requireDateAdd()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites resetDateAdd()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites unsetDateAdd()
	 * @method \Bitrix\Main\Type\DateTime fillDateAdd()
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
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupFavorites wakeUp($data)
	 */
	class EO_WorkgroupFavorites {
		/* @var \Bitrix\Socialnetwork\WorkgroupFavoritesTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupFavoritesTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupFavorites_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \int[] getGroupIdList()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup[] getGroupList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites_Collection getGroupCollection()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection fillGroup()
	 * @method \Bitrix\Main\Type\DateTime[] getDateAddList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateAdd()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_WorkgroupFavorites $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_WorkgroupFavorites $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_WorkgroupFavorites $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupFavorites_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WorkgroupFavorites_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\WorkgroupFavoritesTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupFavoritesTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkgroupFavorites_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkgroupFavorites_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites_Collection fetchCollection()
	 */
	class EO_WorkgroupFavorites_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupFavorites_Collection wakeUpCollection($rows)
	 */
	class EO_WorkgroupFavorites_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\WorkgroupPinTable:socialnetwork\lib\workgrouppin.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupPin
	 * @see \Bitrix\Socialnetwork\WorkgroupPinTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin resetUserId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin resetUser()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \int getGroupId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin resetGroupId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup getGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup remindActualGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup requireGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin setGroup(\Bitrix\Socialnetwork\EO_Workgroup $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin resetGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup fillGroup()
	 * @method \string getContext()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin setContext(\string|\Bitrix\Main\DB\SqlExpression $context)
	 * @method bool hasContext()
	 * @method bool isContextFilled()
	 * @method bool isContextChanged()
	 * @method \string remindActualContext()
	 * @method \string requireContext()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin resetContext()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin unsetContext()
	 * @method \string fillContext()
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
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupPin wakeUp($data)
	 */
	class EO_WorkgroupPin {
		/* @var \Bitrix\Socialnetwork\WorkgroupPinTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupPinTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupPin_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup[] getGroupList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin_Collection getGroupCollection()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection fillGroup()
	 * @method \string[] getContextList()
	 * @method \string[] fillContext()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_WorkgroupPin $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_WorkgroupPin $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_WorkgroupPin $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupPin_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WorkgroupPin_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\WorkgroupPinTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupPinTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkgroupPin_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkgroupPin_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin_Collection fetchCollection()
	 */
	class EO_WorkgroupPin_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupPin_Collection wakeUpCollection($rows)
	 */
	class EO_WorkgroupPin_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\WorkgroupSiteTable:socialnetwork\lib\workgroupsite.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupSite
	 * @see \Bitrix\Socialnetwork\WorkgroupSiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getGroupId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup getGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup remindActualGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup requireGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite setGroup(\Bitrix\Socialnetwork\EO_Workgroup $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite resetGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup fillGroup()
	 * @method \string getSiteId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite resetSite()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite unsetSite()
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
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSite wakeUp($data)
	 */
	class EO_WorkgroupSite {
		/* @var \Bitrix\Socialnetwork\WorkgroupSiteTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupSiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupSite_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getGroupIdList()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup[] getGroupList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite_Collection getGroupCollection()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection fillGroup()
	 * @method \string[] getSiteIdList()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_WorkgroupSite $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_WorkgroupSite $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_WorkgroupSite $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSite_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WorkgroupSite_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\WorkgroupSiteTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupSiteTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkgroupSite_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkgroupSite_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite_Collection fetchCollection()
	 */
	class EO_WorkgroupSite_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSite_Collection wakeUpCollection($rows)
	 */
	class EO_WorkgroupSite_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\WorkgroupSubjectTable:socialnetwork\lib\workgroupsubject.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupSubject
	 * @see \Bitrix\Socialnetwork\WorkgroupSubjectTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject resetSite()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject unsetSite()
	 * @method bool hasSite()
	 * @method bool isSiteFilled()
	 * @method bool isSiteChanged()
	 * @method \Bitrix\Main\EO_Site fillSite()
	 * @method \string getName()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject resetName()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject unsetName()
	 * @method \string fillName()
	 * @method \int getSort()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject resetSort()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject unsetSort()
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
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSubject wakeUp($data)
	 */
	class EO_WorkgroupSubject {
		/* @var \Bitrix\Socialnetwork\WorkgroupSubjectTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupSubjectTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupSubject_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSiteIdList()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_WorkgroupSubject $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_WorkgroupSubject $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_WorkgroupSubject $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSubject_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WorkgroupSubject_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\WorkgroupSubjectTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupSubjectTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkgroupSubject_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkgroupSubject_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject_Collection fetchCollection()
	 */
	class EO_WorkgroupSubject_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject_Collection wakeUpCollection($rows)
	 */
	class EO_WorkgroupSubject_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\WorkgroupSubjectSiteTable:socialnetwork\lib\workgroupsubjectsite.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupSubjectSite
	 * @see \Bitrix\Socialnetwork\WorkgroupSubjectSiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getSubjectId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite setSubjectId(\int|\Bitrix\Main\DB\SqlExpression $subjectId)
	 * @method bool hasSubjectId()
	 * @method bool isSubjectIdFilled()
	 * @method bool isSubjectIdChanged()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject getSubject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject remindActualSubject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject requireSubject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite setSubject(\Bitrix\Socialnetwork\EO_WorkgroupSubject $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite resetSubject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite unsetSubject()
	 * @method bool hasSubject()
	 * @method bool isSubjectFilled()
	 * @method bool isSubjectChanged()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject fillSubject()
	 * @method \string getSiteId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite resetSite()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite unsetSite()
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
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite wakeUp($data)
	 */
	class EO_WorkgroupSubjectSite {
		/* @var \Bitrix\Socialnetwork\WorkgroupSubjectSiteTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupSubjectSiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupSubjectSite_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getSubjectIdList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject[] getSubjectList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite_Collection getSubjectCollection()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubject_Collection fillSubject()
	 * @method \string[] getSiteIdList()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_WorkgroupSubjectSite $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_WorkgroupSubjectSite $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_WorkgroupSubjectSite $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WorkgroupSubjectSite_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\WorkgroupSubjectSiteTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupSubjectSiteTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkgroupSubjectSite_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkgroupSubjectSite_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite_Collection fetchCollection()
	 */
	class EO_WorkgroupSubjectSite_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupSubjectSite_Collection wakeUpCollection($rows)
	 */
	class EO_WorkgroupSubjectSite_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\WorkgroupTagTable:socialnetwork\lib\workgrouptag.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupTag
	 * @see \Bitrix\Socialnetwork\WorkgroupTagTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getGroupId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag resetGroupId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup getGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup remindActualGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup requireGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag setGroup(\Bitrix\Socialnetwork\EO_Workgroup $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag resetGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup fillGroup()
	 * @method \string getName()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
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
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupTag wakeUp($data)
	 */
	class EO_WorkgroupTag {
		/* @var \Bitrix\Socialnetwork\WorkgroupTagTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupTagTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupTag_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup[] getGroupList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag_Collection getGroupCollection()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection fillGroup()
	 * @method \string[] getNameList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_WorkgroupTag $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_WorkgroupTag $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_WorkgroupTag $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupTag_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WorkgroupTag_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\WorkgroupTagTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupTagTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkgroupTag_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkgroupTag_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag_Collection fetchCollection()
	 */
	class EO_WorkgroupTag_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupTag_Collection wakeUpCollection($rows)
	 */
	class EO_WorkgroupTag_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Socialnetwork\WorkgroupViewTable:socialnetwork\lib\workgroupview.php */
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupView
	 * @see \Bitrix\Socialnetwork\WorkgroupViewTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView resetUser()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \int getGroupId()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup getGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup remindActualGroup()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup requireGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView setGroup(\Bitrix\Socialnetwork\EO_Workgroup $object)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView resetGroup()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup fillGroup()
	 * @method \Bitrix\Main\Type\DateTime getDateView()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView setDateView(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateView)
	 * @method bool hasDateView()
	 * @method bool isDateViewFilled()
	 * @method bool isDateViewChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateView()
	 * @method \Bitrix\Main\Type\DateTime requireDateView()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView resetDateView()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView unsetDateView()
	 * @method \Bitrix\Main\Type\DateTime fillDateView()
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
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView set($fieldName, $value)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView reset($fieldName)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupView wakeUp($data)
	 */
	class EO_WorkgroupView {
		/* @var \Bitrix\Socialnetwork\WorkgroupViewTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupViewTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * EO_WorkgroupView_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \int[] getGroupIdList()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup[] getGroupList()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView_Collection getGroupCollection()
	 * @method \Bitrix\Socialnetwork\EO_Workgroup_Collection fillGroup()
	 * @method \Bitrix\Main\Type\DateTime[] getDateViewList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateView()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Socialnetwork\EO_WorkgroupView $object)
	 * @method bool has(\Bitrix\Socialnetwork\EO_WorkgroupView $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView getByPrimary($primary)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView[] getAll()
	 * @method bool remove(\Bitrix\Socialnetwork\EO_WorkgroupView $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Socialnetwork\EO_WorkgroupView_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WorkgroupView_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Socialnetwork\WorkgroupViewTable */
		static public $dataClass = '\Bitrix\Socialnetwork\WorkgroupViewTable';
	}
}
namespace Bitrix\Socialnetwork {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkgroupView_Result exec()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkgroupView_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView fetchObject()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView_Collection fetchCollection()
	 */
	class EO_WorkgroupView_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView createObject($setDefaultValues = true)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView_Collection createCollection()
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView wakeUpObject($row)
	 * @method \Bitrix\Socialnetwork\EO_WorkgroupView_Collection wakeUpCollection($rows)
	 */
	class EO_WorkgroupView_Entity extends \Bitrix\Main\ORM\Entity {}
}