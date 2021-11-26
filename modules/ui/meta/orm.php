<?php

/* ORMENTITYANNOTATION:Bitrix\Ui\EntityForm\EntityFormConfigAcTable:ui/lib/entityform/entityformconfigactable.php:e2dbaa5448af340698e847915dfd3895 */
namespace Bitrix\Ui\EntityForm {
	/**
	 * EO_EntityFormConfigAc
	 * @see \Bitrix\Ui\EntityForm\EntityFormConfigAcTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getAccessCode()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setAccessCode(\string|\Bitrix\Main\DB\SqlExpression $accessCode)
	 * @method bool hasAccessCode()
	 * @method bool isAccessCodeFilled()
	 * @method bool isAccessCodeChanged()
	 * @method \string remindActualAccessCode()
	 * @method \string requireAccessCode()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc resetAccessCode()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unsetAccessCode()
	 * @method \string fillAccessCode()
	 * @method \Bitrix\Main\EO_UserAccess getUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess remindActualUserAccess()
	 * @method \Bitrix\Main\EO_UserAccess requireUserAccess()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setUserAccess(\Bitrix\Main\EO_UserAccess $object)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc resetUserAccess()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unsetUserAccess()
	 * @method bool hasUserAccess()
	 * @method bool isUserAccessFilled()
	 * @method bool isUserAccessChanged()
	 * @method \Bitrix\Main\EO_UserAccess fillUserAccess()
	 * @method \int getConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setConfigId(\int|\Bitrix\Main\DB\SqlExpression $configId)
	 * @method bool hasConfigId()
	 * @method bool isConfigIdFilled()
	 * @method bool isConfigIdChanged()
	 * @method \int remindActualConfigId()
	 * @method \int requireConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc resetConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unsetConfigId()
	 * @method \int fillConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig getConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig remindActualConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig requireConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc setConfig(\Bitrix\Ui\EntityForm\EO_EntityFormConfig $object)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc resetConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unsetConfig()
	 * @method bool hasConfig()
	 * @method bool isConfigFilled()
	 * @method bool isConfigChanged()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig fillConfig()
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
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc set($fieldName, $value)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc reset($fieldName)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc wakeUp($data)
	 */
	class EO_EntityFormConfigAc {
		/* @var \Bitrix\Ui\EntityForm\EntityFormConfigAcTable */
		static public $dataClass = '\Bitrix\Ui\EntityForm\EntityFormConfigAcTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Ui\EntityForm {
	/**
	 * EO_EntityFormConfigAc_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getAccessCodeList()
	 * @method \string[] fillAccessCode()
	 * @method \Bitrix\Main\EO_UserAccess[] getUserAccessList()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection getUserAccessCollection()
	 * @method \Bitrix\Main\EO_UserAccess_Collection fillUserAccess()
	 * @method \int[] getConfigIdList()
	 * @method \int[] fillConfigId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig[] getConfigList()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection getConfigCollection()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection fillConfig()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Ui\EntityForm\EO_EntityFormConfigAc $object)
	 * @method bool has(\Bitrix\Ui\EntityForm\EO_EntityFormConfigAc $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc getByPrimary($primary)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc[] getAll()
	 * @method bool remove(\Bitrix\Ui\EntityForm\EO_EntityFormConfigAc $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EntityFormConfigAc_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Ui\EntityForm\EntityFormConfigAcTable */
		static public $dataClass = '\Bitrix\Ui\EntityForm\EntityFormConfigAcTable';
	}
}
namespace Bitrix\Ui\EntityForm {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EntityFormConfigAc_Result exec()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc fetchObject()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EntityFormConfigAc_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc fetchObject()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection fetchCollection()
	 */
	class EO_EntityFormConfigAc_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc createObject($setDefaultValues = true)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection createCollection()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc wakeUpObject($row)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfigAc_Collection wakeUpCollection($rows)
	 */
	class EO_EntityFormConfigAc_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Ui\EntityForm\EntityFormConfigTable:ui/lib/entityform/entityformconfigtable.php:6337c8313080a5ba9408cea47947ef56 */
namespace Bitrix\Ui\EntityForm {
	/**
	 * EO_EntityFormConfig
	 * @see \Bitrix\Ui\EntityForm\EntityFormConfigTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCategory()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setCategory(\string|\Bitrix\Main\DB\SqlExpression $category)
	 * @method bool hasCategory()
	 * @method bool isCategoryFilled()
	 * @method bool isCategoryChanged()
	 * @method \string remindActualCategory()
	 * @method \string requireCategory()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetCategory()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetCategory()
	 * @method \string fillCategory()
	 * @method \string getEntityTypeId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setEntityTypeId(\string|\Bitrix\Main\DB\SqlExpression $entityTypeId)
	 * @method bool hasEntityTypeId()
	 * @method bool isEntityTypeIdFilled()
	 * @method bool isEntityTypeIdChanged()
	 * @method \string remindActualEntityTypeId()
	 * @method \string requireEntityTypeId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetEntityTypeId()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetEntityTypeId()
	 * @method \string fillEntityTypeId()
	 * @method \string getName()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetName()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetName()
	 * @method \string fillName()
	 * @method \string getConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setConfig(\string|\Bitrix\Main\DB\SqlExpression $config)
	 * @method bool hasConfig()
	 * @method bool isConfigFilled()
	 * @method bool isConfigChanged()
	 * @method \string remindActualConfig()
	 * @method \string requireConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetConfig()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetConfig()
	 * @method \string fillConfig()
	 * @method \boolean getCommon()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig setCommon(\boolean|\Bitrix\Main\DB\SqlExpression $common)
	 * @method bool hasCommon()
	 * @method bool isCommonFilled()
	 * @method bool isCommonChanged()
	 * @method \boolean remindActualCommon()
	 * @method \boolean requireCommon()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig resetCommon()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unsetCommon()
	 * @method \boolean fillCommon()
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
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig set($fieldName, $value)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig reset($fieldName)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfig wakeUp($data)
	 */
	class EO_EntityFormConfig {
		/* @var \Bitrix\Ui\EntityForm\EntityFormConfigTable */
		static public $dataClass = '\Bitrix\Ui\EntityForm\EntityFormConfigTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Ui\EntityForm {
	/**
	 * EO_EntityFormConfig_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCategoryList()
	 * @method \string[] fillCategory()
	 * @method \string[] getEntityTypeIdList()
	 * @method \string[] fillEntityTypeId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getConfigList()
	 * @method \string[] fillConfig()
	 * @method \boolean[] getCommonList()
	 * @method \boolean[] fillCommon()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Ui\EntityForm\EO_EntityFormConfig $object)
	 * @method bool has(\Bitrix\Ui\EntityForm\EO_EntityFormConfig $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig getByPrimary($primary)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig[] getAll()
	 * @method bool remove(\Bitrix\Ui\EntityForm\EO_EntityFormConfig $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_EntityFormConfig_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Ui\EntityForm\EntityFormConfigTable */
		static public $dataClass = '\Bitrix\Ui\EntityForm\EntityFormConfigTable';
	}
}
namespace Bitrix\Ui\EntityForm {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_EntityFormConfig_Result exec()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig fetchObject()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_EntityFormConfig_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig fetchObject()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection fetchCollection()
	 */
	class EO_EntityFormConfig_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig createObject($setDefaultValues = true)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection createCollection()
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig wakeUpObject($row)
	 * @method \Bitrix\Ui\EntityForm\EO_EntityFormConfig_Collection wakeUpCollection($rows)
	 */
	class EO_EntityFormConfig_Entity extends \Bitrix\Main\ORM\Entity {}
}