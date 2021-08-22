<?php

/* ORMENTITYANNOTATION:Bitrix\Sender\Access\Permission\PermissionTable:sender/lib/access/permission/permission.php:64ca269ce29f084d2e863fea3eb87ba6 */
namespace Bitrix\Sender\Access\Permission {
	/**
	 * EO_Permission
	 * @see \Bitrix\Sender\Access\Permission\PermissionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getRoleId()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission setRoleId(\int|\Bitrix\Main\DB\SqlExpression $roleId)
	 * @method bool hasRoleId()
	 * @method bool isRoleIdFilled()
	 * @method bool isRoleIdChanged()
	 * @method \int remindActualRoleId()
	 * @method \int requireRoleId()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission resetRoleId()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission unsetRoleId()
	 * @method \int fillRoleId()
	 * @method \string getPermissionId()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission setPermissionId(\string|\Bitrix\Main\DB\SqlExpression $permissionId)
	 * @method bool hasPermissionId()
	 * @method bool isPermissionIdFilled()
	 * @method bool isPermissionIdChanged()
	 * @method \string remindActualPermissionId()
	 * @method \string requirePermissionId()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission resetPermissionId()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission unsetPermissionId()
	 * @method \string fillPermissionId()
	 * @method \int getValue()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission setValue(\int|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \int remindActualValue()
	 * @method \int requireValue()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission resetValue()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission unsetValue()
	 * @method \int fillValue()
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
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission set($fieldName, $value)
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission reset($fieldName)
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Access\Permission\EO_Permission wakeUp($data)
	 */
	class EO_Permission {
		/* @var \Bitrix\Sender\Access\Permission\PermissionTable */
		static public $dataClass = '\Bitrix\Sender\Access\Permission\PermissionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Access\Permission {
	/**
	 * EO_Permission_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getRoleIdList()
	 * @method \int[] fillRoleId()
	 * @method \string[] getPermissionIdList()
	 * @method \string[] fillPermissionId()
	 * @method \int[] getValueList()
	 * @method \int[] fillValue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Access\Permission\EO_Permission $object)
	 * @method bool has(\Bitrix\Sender\Access\Permission\EO_Permission $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission getByPrimary($primary)
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission[] getAll()
	 * @method bool remove(\Bitrix\Sender\Access\Permission\EO_Permission $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Access\Permission\EO_Permission_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Permission_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Access\Permission\PermissionTable */
		static public $dataClass = '\Bitrix\Sender\Access\Permission\PermissionTable';
	}
}
namespace Bitrix\Sender\Access\Permission {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Permission_Result exec()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission fetchObject()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Permission_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission fetchObject()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission_Collection fetchCollection()
	 */
	class EO_Permission_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission_Collection createCollection()
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission wakeUpObject($row)
	 * @method \Bitrix\Sender\Access\Permission\EO_Permission_Collection wakeUpCollection($rows)
	 */
	class EO_Permission_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Access\Role\RoleTable:sender/lib/access/role/role.php:0ac9bad2276117fa18fa30442d4f35f6 */
namespace Bitrix\Sender\Access\Role {
	/**
	 * EO_Role
	 * @see \Bitrix\Sender\Access\Role\RoleTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Access\Role\EO_Role setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDealCategoryId()
	 * @method \Bitrix\Sender\Access\Role\EO_Role setDealCategoryId(\int|\Bitrix\Main\DB\SqlExpression $dealCategoryId)
	 * @method bool hasDealCategoryId()
	 * @method bool isDealCategoryIdFilled()
	 * @method bool isDealCategoryIdChanged()
	 * @method \int remindActualDealCategoryId()
	 * @method \int requireDealCategoryId()
	 * @method \Bitrix\Sender\Access\Role\EO_Role resetDealCategoryId()
	 * @method \Bitrix\Sender\Access\Role\EO_Role unsetDealCategoryId()
	 * @method \int fillDealCategoryId()
	 * @method \string getName()
	 * @method \Bitrix\Sender\Access\Role\EO_Role setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\Access\Role\EO_Role resetName()
	 * @method \Bitrix\Sender\Access\Role\EO_Role unsetName()
	 * @method \string fillName()
	 * @method \string getXmlId()
	 * @method \Bitrix\Sender\Access\Role\EO_Role setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Sender\Access\Role\EO_Role resetXmlId()
	 * @method \Bitrix\Sender\Access\Role\EO_Role unsetXmlId()
	 * @method \string fillXmlId()
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
	 * @method \Bitrix\Sender\Access\Role\EO_Role set($fieldName, $value)
	 * @method \Bitrix\Sender\Access\Role\EO_Role reset($fieldName)
	 * @method \Bitrix\Sender\Access\Role\EO_Role unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Access\Role\EO_Role wakeUp($data)
	 */
	class EO_Role {
		/* @var \Bitrix\Sender\Access\Role\RoleTable */
		static public $dataClass = '\Bitrix\Sender\Access\Role\RoleTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Access\Role {
	/**
	 * EO_Role_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDealCategoryIdList()
	 * @method \int[] fillDealCategoryId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Access\Role\EO_Role $object)
	 * @method bool has(\Bitrix\Sender\Access\Role\EO_Role $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Access\Role\EO_Role getByPrimary($primary)
	 * @method \Bitrix\Sender\Access\Role\EO_Role[] getAll()
	 * @method bool remove(\Bitrix\Sender\Access\Role\EO_Role $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Access\Role\EO_Role_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Access\Role\EO_Role current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Role_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Access\Role\RoleTable */
		static public $dataClass = '\Bitrix\Sender\Access\Role\RoleTable';
	}
}
namespace Bitrix\Sender\Access\Role {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Role_Result exec()
	 * @method \Bitrix\Sender\Access\Role\EO_Role fetchObject()
	 * @method \Bitrix\Sender\Access\Role\EO_Role_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Role_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Access\Role\EO_Role fetchObject()
	 * @method \Bitrix\Sender\Access\Role\EO_Role_Collection fetchCollection()
	 */
	class EO_Role_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Access\Role\EO_Role createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Access\Role\EO_Role_Collection createCollection()
	 * @method \Bitrix\Sender\Access\Role\EO_Role wakeUpObject($row)
	 * @method \Bitrix\Sender\Access\Role\EO_Role_Collection wakeUpCollection($rows)
	 */
	class EO_Role_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Access\Role\RoleRelationTable:sender/lib/access/role/rolerelation.php:cea0862ab3c516ac6b9ae84731702c21 */
namespace Bitrix\Sender\Access\Role {
	/**
	 * EO_RoleRelation
	 * @see \Bitrix\Sender\Access\Role\RoleRelationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getRoleId()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation setRoleId(\int|\Bitrix\Main\DB\SqlExpression $roleId)
	 * @method bool hasRoleId()
	 * @method bool isRoleIdFilled()
	 * @method bool isRoleIdChanged()
	 * @method \int remindActualRoleId()
	 * @method \int requireRoleId()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation resetRoleId()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation unsetRoleId()
	 * @method \int fillRoleId()
	 * @method \string getRelation()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation setRelation(\string|\Bitrix\Main\DB\SqlExpression $relation)
	 * @method bool hasRelation()
	 * @method bool isRelationFilled()
	 * @method bool isRelationChanged()
	 * @method \string remindActualRelation()
	 * @method \string requireRelation()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation resetRelation()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation unsetRelation()
	 * @method \string fillRelation()
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
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation set($fieldName, $value)
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation reset($fieldName)
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Access\Role\EO_RoleRelation wakeUp($data)
	 */
	class EO_RoleRelation {
		/* @var \Bitrix\Sender\Access\Role\RoleRelationTable */
		static public $dataClass = '\Bitrix\Sender\Access\Role\RoleRelationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Access\Role {
	/**
	 * EO_RoleRelation_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getRoleIdList()
	 * @method \int[] fillRoleId()
	 * @method \string[] getRelationList()
	 * @method \string[] fillRelation()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Access\Role\EO_RoleRelation $object)
	 * @method bool has(\Bitrix\Sender\Access\Role\EO_RoleRelation $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation getByPrimary($primary)
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation[] getAll()
	 * @method bool remove(\Bitrix\Sender\Access\Role\EO_RoleRelation $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Access\Role\EO_RoleRelation_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_RoleRelation_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Access\Role\RoleRelationTable */
		static public $dataClass = '\Bitrix\Sender\Access\Role\RoleRelationTable';
	}
}
namespace Bitrix\Sender\Access\Role {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_RoleRelation_Result exec()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation fetchObject()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_RoleRelation_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation fetchObject()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation_Collection fetchCollection()
	 */
	class EO_RoleRelation_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation_Collection createCollection()
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation wakeUpObject($row)
	 * @method \Bitrix\Sender\Access\Role\EO_RoleRelation_Collection wakeUpCollection($rows)
	 */
	class EO_RoleRelation_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\ContactTable:sender/lib/contact.php:65e3c9e68b5fe229dfa241633b67ff37 */
namespace Bitrix\Sender {
	/**
	 * EO_Contact
	 * @see \Bitrix\Sender\ContactTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_Contact setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_Contact setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_Contact resetDateInsert()
	 * @method \Bitrix\Sender\EO_Contact unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Sender\EO_Contact setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Sender\EO_Contact resetDateUpdate()
	 * @method \Bitrix\Sender\EO_Contact unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \int getTypeId()
	 * @method \Bitrix\Sender\EO_Contact setTypeId(\int|\Bitrix\Main\DB\SqlExpression $typeId)
	 * @method bool hasTypeId()
	 * @method bool isTypeIdFilled()
	 * @method bool isTypeIdChanged()
	 * @method \int remindActualTypeId()
	 * @method \int requireTypeId()
	 * @method \Bitrix\Sender\EO_Contact resetTypeId()
	 * @method \Bitrix\Sender\EO_Contact unsetTypeId()
	 * @method \int fillTypeId()
	 * @method \string getCode()
	 * @method \Bitrix\Sender\EO_Contact setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Sender\EO_Contact resetCode()
	 * @method \Bitrix\Sender\EO_Contact unsetCode()
	 * @method \string fillCode()
	 * @method \string getName()
	 * @method \Bitrix\Sender\EO_Contact setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\EO_Contact resetName()
	 * @method \Bitrix\Sender\EO_Contact unsetName()
	 * @method \string fillName()
	 * @method \int getUserId()
	 * @method \Bitrix\Sender\EO_Contact setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Sender\EO_Contact resetUserId()
	 * @method \Bitrix\Sender\EO_Contact unsetUserId()
	 * @method \int fillUserId()
	 * @method \boolean getBlacklisted()
	 * @method \Bitrix\Sender\EO_Contact setBlacklisted(\boolean|\Bitrix\Main\DB\SqlExpression $blacklisted)
	 * @method bool hasBlacklisted()
	 * @method bool isBlacklistedFilled()
	 * @method bool isBlacklistedChanged()
	 * @method \boolean remindActualBlacklisted()
	 * @method \boolean requireBlacklisted()
	 * @method \Bitrix\Sender\EO_Contact resetBlacklisted()
	 * @method \Bitrix\Sender\EO_Contact unsetBlacklisted()
	 * @method \boolean fillBlacklisted()
	 * @method \boolean getIsRead()
	 * @method \Bitrix\Sender\EO_Contact setIsRead(\boolean|\Bitrix\Main\DB\SqlExpression $isRead)
	 * @method bool hasIsRead()
	 * @method bool isIsReadFilled()
	 * @method bool isIsReadChanged()
	 * @method \boolean remindActualIsRead()
	 * @method \boolean requireIsRead()
	 * @method \Bitrix\Sender\EO_Contact resetIsRead()
	 * @method \Bitrix\Sender\EO_Contact unsetIsRead()
	 * @method \boolean fillIsRead()
	 * @method \boolean getIsClick()
	 * @method \Bitrix\Sender\EO_Contact setIsClick(\boolean|\Bitrix\Main\DB\SqlExpression $isClick)
	 * @method bool hasIsClick()
	 * @method bool isIsClickFilled()
	 * @method bool isIsClickChanged()
	 * @method \boolean remindActualIsClick()
	 * @method \boolean requireIsClick()
	 * @method \Bitrix\Sender\EO_Contact resetIsClick()
	 * @method \Bitrix\Sender\EO_Contact unsetIsClick()
	 * @method \boolean fillIsClick()
	 * @method \boolean getIsUnsub()
	 * @method \Bitrix\Sender\EO_Contact setIsUnsub(\boolean|\Bitrix\Main\DB\SqlExpression $isUnsub)
	 * @method bool hasIsUnsub()
	 * @method bool isIsUnsubFilled()
	 * @method bool isIsUnsubChanged()
	 * @method \boolean remindActualIsUnsub()
	 * @method \boolean requireIsUnsub()
	 * @method \Bitrix\Sender\EO_Contact resetIsUnsub()
	 * @method \Bitrix\Sender\EO_Contact unsetIsUnsub()
	 * @method \boolean fillIsUnsub()
	 * @method \boolean getIsSendSuccess()
	 * @method \Bitrix\Sender\EO_Contact setIsSendSuccess(\boolean|\Bitrix\Main\DB\SqlExpression $isSendSuccess)
	 * @method bool hasIsSendSuccess()
	 * @method bool isIsSendSuccessFilled()
	 * @method bool isIsSendSuccessChanged()
	 * @method \boolean remindActualIsSendSuccess()
	 * @method \boolean requireIsSendSuccess()
	 * @method \Bitrix\Sender\EO_Contact resetIsSendSuccess()
	 * @method \Bitrix\Sender\EO_Contact unsetIsSendSuccess()
	 * @method \boolean fillIsSendSuccess()
	 * @method \string getIp()
	 * @method \Bitrix\Sender\EO_Contact setIp(\string|\Bitrix\Main\DB\SqlExpression $ip)
	 * @method bool hasIp()
	 * @method bool isIpFilled()
	 * @method bool isIpChanged()
	 * @method \string remindActualIp()
	 * @method \string requireIp()
	 * @method \Bitrix\Sender\EO_Contact resetIp()
	 * @method \Bitrix\Sender\EO_Contact unsetIp()
	 * @method \string fillIp()
	 * @method \int getAgent()
	 * @method \Bitrix\Sender\EO_Contact setAgent(\int|\Bitrix\Main\DB\SqlExpression $agent)
	 * @method bool hasAgent()
	 * @method bool isAgentFilled()
	 * @method bool isAgentChanged()
	 * @method \int remindActualAgent()
	 * @method \int requireAgent()
	 * @method \Bitrix\Sender\EO_Contact resetAgent()
	 * @method \Bitrix\Sender\EO_Contact unsetAgent()
	 * @method \int fillAgent()
	 * @method \Bitrix\Sender\EO_ContactList getContactList()
	 * @method \Bitrix\Sender\EO_ContactList remindActualContactList()
	 * @method \Bitrix\Sender\EO_ContactList requireContactList()
	 * @method \Bitrix\Sender\EO_Contact setContactList(\Bitrix\Sender\EO_ContactList $object)
	 * @method \Bitrix\Sender\EO_Contact resetContactList()
	 * @method \Bitrix\Sender\EO_Contact unsetContactList()
	 * @method bool hasContactList()
	 * @method bool isContactListFilled()
	 * @method bool isContactListChanged()
	 * @method \Bitrix\Sender\EO_ContactList fillContactList()
	 * @method \Bitrix\Sender\EO_MailingSubscription getMailingSubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription remindActualMailingSubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription requireMailingSubscription()
	 * @method \Bitrix\Sender\EO_Contact setMailingSubscription(\Bitrix\Sender\EO_MailingSubscription $object)
	 * @method \Bitrix\Sender\EO_Contact resetMailingSubscription()
	 * @method \Bitrix\Sender\EO_Contact unsetMailingSubscription()
	 * @method bool hasMailingSubscription()
	 * @method bool isMailingSubscriptionFilled()
	 * @method bool isMailingSubscriptionChanged()
	 * @method \Bitrix\Sender\EO_MailingSubscription fillMailingSubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription getMailingUnsubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription remindActualMailingUnsubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription requireMailingUnsubscription()
	 * @method \Bitrix\Sender\EO_Contact setMailingUnsubscription(\Bitrix\Sender\EO_MailingSubscription $object)
	 * @method \Bitrix\Sender\EO_Contact resetMailingUnsubscription()
	 * @method \Bitrix\Sender\EO_Contact unsetMailingUnsubscription()
	 * @method bool hasMailingUnsubscription()
	 * @method bool isMailingUnsubscriptionFilled()
	 * @method bool isMailingUnsubscriptionChanged()
	 * @method \Bitrix\Sender\EO_MailingSubscription fillMailingUnsubscription()
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
	 * @method \Bitrix\Sender\EO_Contact set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_Contact reset($fieldName)
	 * @method \Bitrix\Sender\EO_Contact unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_Contact wakeUp($data)
	 */
	class EO_Contact {
		/* @var \Bitrix\Sender\ContactTable */
		static public $dataClass = '\Bitrix\Sender\ContactTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_Contact_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \int[] getTypeIdList()
	 * @method \int[] fillTypeId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \boolean[] getBlacklistedList()
	 * @method \boolean[] fillBlacklisted()
	 * @method \boolean[] getIsReadList()
	 * @method \boolean[] fillIsRead()
	 * @method \boolean[] getIsClickList()
	 * @method \boolean[] fillIsClick()
	 * @method \boolean[] getIsUnsubList()
	 * @method \boolean[] fillIsUnsub()
	 * @method \boolean[] getIsSendSuccessList()
	 * @method \boolean[] fillIsSendSuccess()
	 * @method \string[] getIpList()
	 * @method \string[] fillIp()
	 * @method \int[] getAgentList()
	 * @method \int[] fillAgent()
	 * @method \Bitrix\Sender\EO_ContactList[] getContactListList()
	 * @method \Bitrix\Sender\EO_Contact_Collection getContactListCollection()
	 * @method \Bitrix\Sender\EO_ContactList_Collection fillContactList()
	 * @method \Bitrix\Sender\EO_MailingSubscription[] getMailingSubscriptionList()
	 * @method \Bitrix\Sender\EO_Contact_Collection getMailingSubscriptionCollection()
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection fillMailingSubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription[] getMailingUnsubscriptionList()
	 * @method \Bitrix\Sender\EO_Contact_Collection getMailingUnsubscriptionCollection()
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection fillMailingUnsubscription()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_Contact $object)
	 * @method bool has(\Bitrix\Sender\EO_Contact $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Contact getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Contact[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_Contact $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_Contact_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_Contact current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Contact_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\ContactTable */
		static public $dataClass = '\Bitrix\Sender\ContactTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Contact_Result exec()
	 * @method \Bitrix\Sender\EO_Contact fetchObject()
	 * @method \Bitrix\Sender\EO_Contact_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Contact_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_Contact fetchObject()
	 * @method \Bitrix\Sender\EO_Contact_Collection fetchCollection()
	 */
	class EO_Contact_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_Contact createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_Contact_Collection createCollection()
	 * @method \Bitrix\Sender\EO_Contact wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_Contact_Collection wakeUpCollection($rows)
	 */
	class EO_Contact_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\ContactListTable:sender/lib/contactlist.php:ea4cb1f5e9b8c88db5f7710b039f524e */
namespace Bitrix\Sender {
	/**
	 * EO_ContactList
	 * @see \Bitrix\Sender\ContactListTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getContactId()
	 * @method \Bitrix\Sender\EO_ContactList setContactId(\int|\Bitrix\Main\DB\SqlExpression $contactId)
	 * @method bool hasContactId()
	 * @method bool isContactIdFilled()
	 * @method bool isContactIdChanged()
	 * @method \int getListId()
	 * @method \Bitrix\Sender\EO_ContactList setListId(\int|\Bitrix\Main\DB\SqlExpression $listId)
	 * @method bool hasListId()
	 * @method bool isListIdFilled()
	 * @method bool isListIdChanged()
	 * @method \Bitrix\Sender\EO_List getList()
	 * @method \Bitrix\Sender\EO_List remindActualList()
	 * @method \Bitrix\Sender\EO_List requireList()
	 * @method \Bitrix\Sender\EO_ContactList setList(\Bitrix\Sender\EO_List $object)
	 * @method \Bitrix\Sender\EO_ContactList resetList()
	 * @method \Bitrix\Sender\EO_ContactList unsetList()
	 * @method bool hasList()
	 * @method bool isListFilled()
	 * @method bool isListChanged()
	 * @method \Bitrix\Sender\EO_List fillList()
	 * @method \Bitrix\Sender\EO_Contact getContact()
	 * @method \Bitrix\Sender\EO_Contact remindActualContact()
	 * @method \Bitrix\Sender\EO_Contact requireContact()
	 * @method \Bitrix\Sender\EO_ContactList setContact(\Bitrix\Sender\EO_Contact $object)
	 * @method \Bitrix\Sender\EO_ContactList resetContact()
	 * @method \Bitrix\Sender\EO_ContactList unsetContact()
	 * @method bool hasContact()
	 * @method bool isContactFilled()
	 * @method bool isContactChanged()
	 * @method \Bitrix\Sender\EO_Contact fillContact()
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
	 * @method \Bitrix\Sender\EO_ContactList set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_ContactList reset($fieldName)
	 * @method \Bitrix\Sender\EO_ContactList unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_ContactList wakeUp($data)
	 */
	class EO_ContactList {
		/* @var \Bitrix\Sender\ContactListTable */
		static public $dataClass = '\Bitrix\Sender\ContactListTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_ContactList_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getContactIdList()
	 * @method \int[] getListIdList()
	 * @method \Bitrix\Sender\EO_List[] getListList()
	 * @method \Bitrix\Sender\EO_ContactList_Collection getListCollection()
	 * @method \Bitrix\Sender\EO_List_Collection fillList()
	 * @method \Bitrix\Sender\EO_Contact[] getContactList()
	 * @method \Bitrix\Sender\EO_ContactList_Collection getContactCollection()
	 * @method \Bitrix\Sender\EO_Contact_Collection fillContact()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_ContactList $object)
	 * @method bool has(\Bitrix\Sender\EO_ContactList $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_ContactList getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_ContactList[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_ContactList $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_ContactList_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_ContactList current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ContactList_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\ContactListTable */
		static public $dataClass = '\Bitrix\Sender\ContactListTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ContactList_Result exec()
	 * @method \Bitrix\Sender\EO_ContactList fetchObject()
	 * @method \Bitrix\Sender\EO_ContactList_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ContactList_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_ContactList fetchObject()
	 * @method \Bitrix\Sender\EO_ContactList_Collection fetchCollection()
	 */
	class EO_ContactList_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_ContactList createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_ContactList_Collection createCollection()
	 * @method \Bitrix\Sender\EO_ContactList wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_ContactList_Collection wakeUpCollection($rows)
	 */
	class EO_ContactList_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\GroupTable:sender/lib/group.php:fbee1b14c95edd1b74a29b0a4b8cf4e7 */
namespace Bitrix\Sender {
	/**
	 * EO_Group
	 * @see \Bitrix\Sender\GroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_Group setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Sender\EO_Group setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Sender\EO_Group resetCode()
	 * @method \Bitrix\Sender\EO_Group unsetCode()
	 * @method \string fillCode()
	 * @method \string getName()
	 * @method \Bitrix\Sender\EO_Group setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\EO_Group resetName()
	 * @method \Bitrix\Sender\EO_Group unsetName()
	 * @method \string fillName()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_Group setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_Group resetDateInsert()
	 * @method \Bitrix\Sender\EO_Group unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Sender\EO_Group setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Sender\EO_Group resetDateUpdate()
	 * @method \Bitrix\Sender\EO_Group unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime getDateUse()
	 * @method \Bitrix\Sender\EO_Group setDateUse(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUse)
	 * @method bool hasDateUse()
	 * @method bool isDateUseFilled()
	 * @method bool isDateUseChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUse()
	 * @method \Bitrix\Main\Type\DateTime requireDateUse()
	 * @method \Bitrix\Sender\EO_Group resetDateUse()
	 * @method \Bitrix\Sender\EO_Group unsetDateUse()
	 * @method \Bitrix\Main\Type\DateTime fillDateUse()
	 * @method \Bitrix\Main\Type\DateTime getDateUseExclude()
	 * @method \Bitrix\Sender\EO_Group setDateUseExclude(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUseExclude)
	 * @method bool hasDateUseExclude()
	 * @method bool isDateUseExcludeFilled()
	 * @method bool isDateUseExcludeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUseExclude()
	 * @method \Bitrix\Main\Type\DateTime requireDateUseExclude()
	 * @method \Bitrix\Sender\EO_Group resetDateUseExclude()
	 * @method \Bitrix\Sender\EO_Group unsetDateUseExclude()
	 * @method \Bitrix\Main\Type\DateTime fillDateUseExclude()
	 * @method \boolean getActive()
	 * @method \Bitrix\Sender\EO_Group setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Sender\EO_Group resetActive()
	 * @method \Bitrix\Sender\EO_Group unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getHidden()
	 * @method \Bitrix\Sender\EO_Group setHidden(\boolean|\Bitrix\Main\DB\SqlExpression $hidden)
	 * @method bool hasHidden()
	 * @method bool isHiddenFilled()
	 * @method bool isHiddenChanged()
	 * @method \boolean remindActualHidden()
	 * @method \boolean requireHidden()
	 * @method \Bitrix\Sender\EO_Group resetHidden()
	 * @method \Bitrix\Sender\EO_Group unsetHidden()
	 * @method \boolean fillHidden()
	 * @method \boolean getIsSystem()
	 * @method \Bitrix\Sender\EO_Group setIsSystem(\boolean|\Bitrix\Main\DB\SqlExpression $isSystem)
	 * @method bool hasIsSystem()
	 * @method bool isIsSystemFilled()
	 * @method bool isIsSystemChanged()
	 * @method \boolean remindActualIsSystem()
	 * @method \boolean requireIsSystem()
	 * @method \Bitrix\Sender\EO_Group resetIsSystem()
	 * @method \Bitrix\Sender\EO_Group unsetIsSystem()
	 * @method \boolean fillIsSystem()
	 * @method \string getDescription()
	 * @method \Bitrix\Sender\EO_Group setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Sender\EO_Group resetDescription()
	 * @method \Bitrix\Sender\EO_Group unsetDescription()
	 * @method \string fillDescription()
	 * @method \int getSort()
	 * @method \Bitrix\Sender\EO_Group setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Sender\EO_Group resetSort()
	 * @method \Bitrix\Sender\EO_Group unsetSort()
	 * @method \int fillSort()
	 * @method \int getAddressCount()
	 * @method \Bitrix\Sender\EO_Group setAddressCount(\int|\Bitrix\Main\DB\SqlExpression $addressCount)
	 * @method bool hasAddressCount()
	 * @method bool isAddressCountFilled()
	 * @method bool isAddressCountChanged()
	 * @method \int remindActualAddressCount()
	 * @method \int requireAddressCount()
	 * @method \Bitrix\Sender\EO_Group resetAddressCount()
	 * @method \Bitrix\Sender\EO_Group unsetAddressCount()
	 * @method \int fillAddressCount()
	 * @method \int getUseCount()
	 * @method \Bitrix\Sender\EO_Group setUseCount(\int|\Bitrix\Main\DB\SqlExpression $useCount)
	 * @method bool hasUseCount()
	 * @method bool isUseCountFilled()
	 * @method bool isUseCountChanged()
	 * @method \int remindActualUseCount()
	 * @method \int requireUseCount()
	 * @method \Bitrix\Sender\EO_Group resetUseCount()
	 * @method \Bitrix\Sender\EO_Group unsetUseCount()
	 * @method \int fillUseCount()
	 * @method \int getUseCountExclude()
	 * @method \Bitrix\Sender\EO_Group setUseCountExclude(\int|\Bitrix\Main\DB\SqlExpression $useCountExclude)
	 * @method bool hasUseCountExclude()
	 * @method bool isUseCountExcludeFilled()
	 * @method bool isUseCountExcludeChanged()
	 * @method \int remindActualUseCountExclude()
	 * @method \int requireUseCountExclude()
	 * @method \Bitrix\Sender\EO_Group resetUseCountExclude()
	 * @method \Bitrix\Sender\EO_Group unsetUseCountExclude()
	 * @method \int fillUseCountExclude()
	 * @method \Bitrix\Sender\EO_GroupConnector getGroupConnector()
	 * @method \Bitrix\Sender\EO_GroupConnector remindActualGroupConnector()
	 * @method \Bitrix\Sender\EO_GroupConnector requireGroupConnector()
	 * @method \Bitrix\Sender\EO_Group setGroupConnector(\Bitrix\Sender\EO_GroupConnector $object)
	 * @method \Bitrix\Sender\EO_Group resetGroupConnector()
	 * @method \Bitrix\Sender\EO_Group unsetGroupConnector()
	 * @method bool hasGroupConnector()
	 * @method bool isGroupConnectorFilled()
	 * @method bool isGroupConnectorChanged()
	 * @method \Bitrix\Sender\EO_GroupConnector fillGroupConnector()
	 * @method \Bitrix\Sender\EO_MailingGroup getMailingGroup()
	 * @method \Bitrix\Sender\EO_MailingGroup remindActualMailingGroup()
	 * @method \Bitrix\Sender\EO_MailingGroup requireMailingGroup()
	 * @method \Bitrix\Sender\EO_Group setMailingGroup(\Bitrix\Sender\EO_MailingGroup $object)
	 * @method \Bitrix\Sender\EO_Group resetMailingGroup()
	 * @method \Bitrix\Sender\EO_Group unsetMailingGroup()
	 * @method bool hasMailingGroup()
	 * @method bool isMailingGroupFilled()
	 * @method bool isMailingGroupChanged()
	 * @method \Bitrix\Sender\EO_MailingGroup fillMailingGroup()
	 * @method \Bitrix\Sender\EO_GroupDealCategory getDealCategory()
	 * @method \Bitrix\Sender\EO_GroupDealCategory remindActualDealCategory()
	 * @method \Bitrix\Sender\EO_GroupDealCategory requireDealCategory()
	 * @method \Bitrix\Sender\EO_Group setDealCategory(\Bitrix\Sender\EO_GroupDealCategory $object)
	 * @method \Bitrix\Sender\EO_Group resetDealCategory()
	 * @method \Bitrix\Sender\EO_Group unsetDealCategory()
	 * @method bool hasDealCategory()
	 * @method bool isDealCategoryFilled()
	 * @method bool isDealCategoryChanged()
	 * @method \Bitrix\Sender\EO_GroupDealCategory fillDealCategory()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\EO_Group setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Sender\EO_Group resetStatus()
	 * @method \Bitrix\Sender\EO_Group unsetStatus()
	 * @method \string fillStatus()
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
	 * @method \Bitrix\Sender\EO_Group set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_Group reset($fieldName)
	 * @method \Bitrix\Sender\EO_Group unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_Group wakeUp($data)
	 */
	class EO_Group {
		/* @var \Bitrix\Sender\GroupTable */
		static public $dataClass = '\Bitrix\Sender\GroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_Group_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUseList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUse()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUseExcludeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUseExclude()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getHiddenList()
	 * @method \boolean[] fillHidden()
	 * @method \boolean[] getIsSystemList()
	 * @method \boolean[] fillIsSystem()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \int[] getAddressCountList()
	 * @method \int[] fillAddressCount()
	 * @method \int[] getUseCountList()
	 * @method \int[] fillUseCount()
	 * @method \int[] getUseCountExcludeList()
	 * @method \int[] fillUseCountExclude()
	 * @method \Bitrix\Sender\EO_GroupConnector[] getGroupConnectorList()
	 * @method \Bitrix\Sender\EO_Group_Collection getGroupConnectorCollection()
	 * @method \Bitrix\Sender\EO_GroupConnector_Collection fillGroupConnector()
	 * @method \Bitrix\Sender\EO_MailingGroup[] getMailingGroupList()
	 * @method \Bitrix\Sender\EO_Group_Collection getMailingGroupCollection()
	 * @method \Bitrix\Sender\EO_MailingGroup_Collection fillMailingGroup()
	 * @method \Bitrix\Sender\EO_GroupDealCategory[] getDealCategoryList()
	 * @method \Bitrix\Sender\EO_Group_Collection getDealCategoryCollection()
	 * @method \Bitrix\Sender\EO_GroupDealCategory_Collection fillDealCategory()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_Group $object)
	 * @method bool has(\Bitrix\Sender\EO_Group $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Group getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Group[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_Group $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_Group_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_Group current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Group_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\GroupTable */
		static public $dataClass = '\Bitrix\Sender\GroupTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Group_Result exec()
	 * @method \Bitrix\Sender\EO_Group fetchObject()
	 * @method \Bitrix\Sender\EO_Group_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Group_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_Group fetchObject()
	 * @method \Bitrix\Sender\EO_Group_Collection fetchCollection()
	 */
	class EO_Group_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_Group createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_Group_Collection createCollection()
	 * @method \Bitrix\Sender\EO_Group wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_Group_Collection wakeUpCollection($rows)
	 */
	class EO_Group_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\GroupConnectorTable:sender/lib/group.php:fbee1b14c95edd1b74a29b0a4b8cf4e7 */
namespace Bitrix\Sender {
	/**
	 * EO_GroupConnector
	 * @see \Bitrix\Sender\GroupConnectorTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getGroupId()
	 * @method \Bitrix\Sender\EO_GroupConnector setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Sender\EO_GroupConnector setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\EO_GroupConnector resetName()
	 * @method \Bitrix\Sender\EO_GroupConnector unsetName()
	 * @method \string fillName()
	 * @method \string getEndpoint()
	 * @method \Bitrix\Sender\EO_GroupConnector setEndpoint(\string|\Bitrix\Main\DB\SqlExpression $endpoint)
	 * @method bool hasEndpoint()
	 * @method bool isEndpointFilled()
	 * @method bool isEndpointChanged()
	 * @method \string remindActualEndpoint()
	 * @method \string requireEndpoint()
	 * @method \Bitrix\Sender\EO_GroupConnector resetEndpoint()
	 * @method \Bitrix\Sender\EO_GroupConnector unsetEndpoint()
	 * @method \string fillEndpoint()
	 * @method \int getAddressCount()
	 * @method \Bitrix\Sender\EO_GroupConnector setAddressCount(\int|\Bitrix\Main\DB\SqlExpression $addressCount)
	 * @method bool hasAddressCount()
	 * @method bool isAddressCountFilled()
	 * @method bool isAddressCountChanged()
	 * @method \int remindActualAddressCount()
	 * @method \int requireAddressCount()
	 * @method \Bitrix\Sender\EO_GroupConnector resetAddressCount()
	 * @method \Bitrix\Sender\EO_GroupConnector unsetAddressCount()
	 * @method \int fillAddressCount()
	 * @method \Bitrix\Sender\EO_Group getGroup()
	 * @method \Bitrix\Sender\EO_Group remindActualGroup()
	 * @method \Bitrix\Sender\EO_Group requireGroup()
	 * @method \Bitrix\Sender\EO_GroupConnector setGroup(\Bitrix\Sender\EO_Group $object)
	 * @method \Bitrix\Sender\EO_GroupConnector resetGroup()
	 * @method \Bitrix\Sender\EO_GroupConnector unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Sender\EO_Group fillGroup()
	 * @method \string getFilterId()
	 * @method \Bitrix\Sender\EO_GroupConnector setFilterId(\string|\Bitrix\Main\DB\SqlExpression $filterId)
	 * @method bool hasFilterId()
	 * @method bool isFilterIdFilled()
	 * @method bool isFilterIdChanged()
	 * @method \string remindActualFilterId()
	 * @method \string requireFilterId()
	 * @method \Bitrix\Sender\EO_GroupConnector resetFilterId()
	 * @method \Bitrix\Sender\EO_GroupConnector unsetFilterId()
	 * @method \string fillFilterId()
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
	 * @method \Bitrix\Sender\EO_GroupConnector set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_GroupConnector reset($fieldName)
	 * @method \Bitrix\Sender\EO_GroupConnector unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_GroupConnector wakeUp($data)
	 */
	class EO_GroupConnector {
		/* @var \Bitrix\Sender\GroupConnectorTable */
		static public $dataClass = '\Bitrix\Sender\GroupConnectorTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_GroupConnector_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getGroupIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getEndpointList()
	 * @method \string[] fillEndpoint()
	 * @method \int[] getAddressCountList()
	 * @method \int[] fillAddressCount()
	 * @method \Bitrix\Sender\EO_Group[] getGroupList()
	 * @method \Bitrix\Sender\EO_GroupConnector_Collection getGroupCollection()
	 * @method \Bitrix\Sender\EO_Group_Collection fillGroup()
	 * @method \string[] getFilterIdList()
	 * @method \string[] fillFilterId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_GroupConnector $object)
	 * @method bool has(\Bitrix\Sender\EO_GroupConnector $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_GroupConnector getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_GroupConnector[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_GroupConnector $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_GroupConnector_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_GroupConnector current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupConnector_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\GroupConnectorTable */
		static public $dataClass = '\Bitrix\Sender\GroupConnectorTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupConnector_Result exec()
	 * @method \Bitrix\Sender\EO_GroupConnector fetchObject()
	 * @method \Bitrix\Sender\EO_GroupConnector_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupConnector_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_GroupConnector fetchObject()
	 * @method \Bitrix\Sender\EO_GroupConnector_Collection fetchCollection()
	 */
	class EO_GroupConnector_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_GroupConnector createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_GroupConnector_Collection createCollection()
	 * @method \Bitrix\Sender\EO_GroupConnector wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_GroupConnector_Collection wakeUpCollection($rows)
	 */
	class EO_GroupConnector_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\GroupDealCategoryTable:sender/lib/group.php:fbee1b14c95edd1b74a29b0a4b8cf4e7 */
namespace Bitrix\Sender {
	/**
	 * EO_GroupDealCategory
	 * @see \Bitrix\Sender\GroupDealCategoryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getGroupId()
	 * @method \Bitrix\Sender\EO_GroupDealCategory setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \Bitrix\Sender\EO_Group getGroup()
	 * @method \Bitrix\Sender\EO_Group remindActualGroup()
	 * @method \Bitrix\Sender\EO_Group requireGroup()
	 * @method \Bitrix\Sender\EO_GroupDealCategory setGroup(\Bitrix\Sender\EO_Group $object)
	 * @method \Bitrix\Sender\EO_GroupDealCategory resetGroup()
	 * @method \Bitrix\Sender\EO_GroupDealCategory unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Sender\EO_Group fillGroup()
	 * @method \int getDealCategoryId()
	 * @method \Bitrix\Sender\EO_GroupDealCategory setDealCategoryId(\int|\Bitrix\Main\DB\SqlExpression $dealCategoryId)
	 * @method bool hasDealCategoryId()
	 * @method bool isDealCategoryIdFilled()
	 * @method bool isDealCategoryIdChanged()
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
	 * @method \Bitrix\Sender\EO_GroupDealCategory set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_GroupDealCategory reset($fieldName)
	 * @method \Bitrix\Sender\EO_GroupDealCategory unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_GroupDealCategory wakeUp($data)
	 */
	class EO_GroupDealCategory {
		/* @var \Bitrix\Sender\GroupDealCategoryTable */
		static public $dataClass = '\Bitrix\Sender\GroupDealCategoryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_GroupDealCategory_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getGroupIdList()
	 * @method \Bitrix\Sender\EO_Group[] getGroupList()
	 * @method \Bitrix\Sender\EO_GroupDealCategory_Collection getGroupCollection()
	 * @method \Bitrix\Sender\EO_Group_Collection fillGroup()
	 * @method \int[] getDealCategoryIdList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_GroupDealCategory $object)
	 * @method bool has(\Bitrix\Sender\EO_GroupDealCategory $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_GroupDealCategory getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_GroupDealCategory[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_GroupDealCategory $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_GroupDealCategory_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_GroupDealCategory current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupDealCategory_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\GroupDealCategoryTable */
		static public $dataClass = '\Bitrix\Sender\GroupDealCategoryTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupDealCategory_Result exec()
	 * @method \Bitrix\Sender\EO_GroupDealCategory fetchObject()
	 * @method \Bitrix\Sender\EO_GroupDealCategory_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupDealCategory_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_GroupDealCategory fetchObject()
	 * @method \Bitrix\Sender\EO_GroupDealCategory_Collection fetchCollection()
	 */
	class EO_GroupDealCategory_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_GroupDealCategory createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_GroupDealCategory_Collection createCollection()
	 * @method \Bitrix\Sender\EO_GroupDealCategory wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_GroupDealCategory_Collection wakeUpCollection($rows)
	 */
	class EO_GroupDealCategory_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Integration\VoxImplant\CallLogTable:sender/lib/integration/voximplant/calllog.php:bd82abdc442d96e5c757e7d2a9ab7d2d */
namespace Bitrix\Sender\Integration\VoxImplant {
	/**
	 * EO_CallLog
	 * @see \Bitrix\Sender\Integration\VoxImplant\CallLogTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getCallId()
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog setCallId(\string|\Bitrix\Main\DB\SqlExpression $callId)
	 * @method bool hasCallId()
	 * @method bool isCallIdFilled()
	 * @method bool isCallIdChanged()
	 * @method \int getRecipientId()
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog setRecipientId(\int|\Bitrix\Main\DB\SqlExpression $recipientId)
	 * @method bool hasRecipientId()
	 * @method bool isRecipientIdFilled()
	 * @method bool isRecipientIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog resetDateInsert()
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog unsetDateInsert()
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
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog set($fieldName, $value)
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog reset($fieldName)
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Integration\VoxImplant\EO_CallLog wakeUp($data)
	 */
	class EO_CallLog {
		/* @var \Bitrix\Sender\Integration\VoxImplant\CallLogTable */
		static public $dataClass = '\Bitrix\Sender\Integration\VoxImplant\CallLogTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Integration\VoxImplant {
	/**
	 * EO_CallLog_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getCallIdList()
	 * @method \int[] getRecipientIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Integration\VoxImplant\EO_CallLog $object)
	 * @method bool has(\Bitrix\Sender\Integration\VoxImplant\EO_CallLog $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog getByPrimary($primary)
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog[] getAll()
	 * @method bool remove(\Bitrix\Sender\Integration\VoxImplant\EO_CallLog $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Integration\VoxImplant\EO_CallLog_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_CallLog_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Integration\VoxImplant\CallLogTable */
		static public $dataClass = '\Bitrix\Sender\Integration\VoxImplant\CallLogTable';
	}
}
namespace Bitrix\Sender\Integration\VoxImplant {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CallLog_Result exec()
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog fetchObject()
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CallLog_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog fetchObject()
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog_Collection fetchCollection()
	 */
	class EO_CallLog_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog_Collection createCollection()
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog wakeUpObject($row)
	 * @method \Bitrix\Sender\Integration\VoxImplant\EO_CallLog_Collection wakeUpCollection($rows)
	 */
	class EO_CallLog_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\AbuseTable:sender/lib/internals/model/abuse.php:4d712fee4f5ce95aacc92b3491be8111 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Abuse
	 * @see \Bitrix\Sender\Internals\Model\AbuseTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getText()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse setText(\string|\Bitrix\Main\DB\SqlExpression $text)
	 * @method bool hasText()
	 * @method bool isTextFilled()
	 * @method bool isTextChanged()
	 * @method \string remindActualText()
	 * @method \string requireText()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse resetText()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse unsetText()
	 * @method \string fillText()
	 * @method \int getContactId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse setContactId(\int|\Bitrix\Main\DB\SqlExpression $contactId)
	 * @method bool hasContactId()
	 * @method bool isContactIdFilled()
	 * @method bool isContactIdChanged()
	 * @method \int remindActualContactId()
	 * @method \int requireContactId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse resetContactId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse unsetContactId()
	 * @method \int fillContactId()
	 * @method \string getContactCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse setContactCode(\string|\Bitrix\Main\DB\SqlExpression $contactCode)
	 * @method bool hasContactCode()
	 * @method bool isContactCodeFilled()
	 * @method bool isContactCodeChanged()
	 * @method \string remindActualContactCode()
	 * @method \string requireContactCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse resetContactCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse unsetContactCode()
	 * @method \string fillContactCode()
	 * @method \int getContactTypeId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse setContactTypeId(\int|\Bitrix\Main\DB\SqlExpression $contactTypeId)
	 * @method bool hasContactTypeId()
	 * @method bool isContactTypeIdFilled()
	 * @method bool isContactTypeIdChanged()
	 * @method \int remindActualContactTypeId()
	 * @method \int requireContactTypeId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse resetContactTypeId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse unsetContactTypeId()
	 * @method \int fillContactTypeId()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse resetDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse unsetDateInsert()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_Abuse wakeUp($data)
	 */
	class EO_Abuse {
		/* @var \Bitrix\Sender\Internals\Model\AbuseTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\AbuseTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Abuse_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTextList()
	 * @method \string[] fillText()
	 * @method \int[] getContactIdList()
	 * @method \int[] fillContactId()
	 * @method \string[] getContactCodeList()
	 * @method \string[] fillContactCode()
	 * @method \int[] getContactTypeIdList()
	 * @method \int[] fillContactTypeId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_Abuse $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_Abuse $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_Abuse $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_Abuse_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Abuse_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\AbuseTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\AbuseTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Abuse_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Abuse_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse_Collection fetchCollection()
	 */
	class EO_Abuse_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_Abuse_Collection wakeUpCollection($rows)
	 */
	class EO_Abuse_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\AgreementTable:sender/lib/internals/model/agreement.php:0df644729f2d58d650bb3031ca101891 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Agreement
	 * @see \Bitrix\Sender\Internals\Model\AgreementTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement resetUserId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getName()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement resetName()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement unsetName()
	 * @method \string fillName()
	 * @method \string getEmail()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement resetEmail()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement unsetEmail()
	 * @method \string fillEmail()
	 * @method \Bitrix\Main\Type\DateTime getDate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement setDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $date)
	 * @method bool hasDate()
	 * @method bool isDateFilled()
	 * @method bool isDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDate()
	 * @method \Bitrix\Main\Type\DateTime requireDate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement resetDate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement unsetDate()
	 * @method \Bitrix\Main\Type\DateTime fillDate()
	 * @method \string getIpAddress()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement setIpAddress(\string|\Bitrix\Main\DB\SqlExpression $ipAddress)
	 * @method bool hasIpAddress()
	 * @method bool isIpAddressFilled()
	 * @method bool isIpAddressChanged()
	 * @method \string remindActualIpAddress()
	 * @method \string requireIpAddress()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement resetIpAddress()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement unsetIpAddress()
	 * @method \string fillIpAddress()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_Agreement wakeUp($data)
	 */
	class EO_Agreement {
		/* @var \Bitrix\Sender\Internals\Model\AgreementTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\AgreementTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Agreement_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \Bitrix\Main\Type\DateTime[] getDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDate()
	 * @method \string[] getIpAddressList()
	 * @method \string[] fillIpAddress()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_Agreement $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_Agreement $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_Agreement $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_Agreement_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Agreement_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\AgreementTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\AgreementTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Agreement_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Agreement_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement_Collection fetchCollection()
	 */
	class EO_Agreement_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_Agreement_Collection wakeUpCollection($rows)
	 */
	class EO_Agreement_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\CounterTable:sender/lib/internals/model/counter.php:ab853bb4bb61bfa582e44b67d57302dd */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Counter
	 * @see \Bitrix\Sender\Internals\Model\CounterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \int getValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter setValue(\int|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \int remindActualValue()
	 * @method \int requireValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter resetValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter unsetValue()
	 * @method \int fillValue()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter resetDateUpdate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter unsetDateUpdate()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_Counter wakeUp($data)
	 */
	class EO_Counter {
		/* @var \Bitrix\Sender\Internals\Model\CounterTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\CounterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Counter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getCodeList()
	 * @method \int[] getValueList()
	 * @method \int[] fillValue()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_Counter $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_Counter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_Counter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_Counter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Counter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\CounterTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\CounterTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Counter_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Counter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter_Collection fetchCollection()
	 */
	class EO_Counter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_Counter_Collection wakeUpCollection($rows)
	 */
	class EO_Counter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\DailyCounterTable:sender/lib/internals/model/dailycounter.php:629b4cf44071ad168dd7e734e1c34570 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_DailyCounter
	 * @see \Bitrix\Sender\Internals\Model\DailyCounterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date getDateStat()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter setDateStat(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $dateStat)
	 * @method bool hasDateStat()
	 * @method bool isDateStatFilled()
	 * @method bool isDateStatChanged()
	 * @method \int getSentCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter setSentCnt(\int|\Bitrix\Main\DB\SqlExpression $sentCnt)
	 * @method bool hasSentCnt()
	 * @method bool isSentCntFilled()
	 * @method bool isSentCntChanged()
	 * @method \int remindActualSentCnt()
	 * @method \int requireSentCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter resetSentCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter unsetSentCnt()
	 * @method \int fillSentCnt()
	 * @method \int getTestSentCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter setTestSentCnt(\int|\Bitrix\Main\DB\SqlExpression $testSentCnt)
	 * @method bool hasTestSentCnt()
	 * @method bool isTestSentCntFilled()
	 * @method bool isTestSentCntChanged()
	 * @method \int remindActualTestSentCnt()
	 * @method \int requireTestSentCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter resetTestSentCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter unsetTestSentCnt()
	 * @method \int fillTestSentCnt()
	 * @method \int getErrorCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter setErrorCnt(\int|\Bitrix\Main\DB\SqlExpression $errorCnt)
	 * @method bool hasErrorCnt()
	 * @method bool isErrorCntFilled()
	 * @method bool isErrorCntChanged()
	 * @method \int remindActualErrorCnt()
	 * @method \int requireErrorCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter resetErrorCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter unsetErrorCnt()
	 * @method \int fillErrorCnt()
	 * @method \int getAbuseCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter setAbuseCnt(\int|\Bitrix\Main\DB\SqlExpression $abuseCnt)
	 * @method bool hasAbuseCnt()
	 * @method bool isAbuseCntFilled()
	 * @method bool isAbuseCntChanged()
	 * @method \int remindActualAbuseCnt()
	 * @method \int requireAbuseCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter resetAbuseCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter unsetAbuseCnt()
	 * @method \int fillAbuseCnt()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_DailyCounter wakeUp($data)
	 */
	class EO_DailyCounter {
		/* @var \Bitrix\Sender\Internals\Model\DailyCounterTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\DailyCounterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_DailyCounter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \Bitrix\Main\Type\Date[] getDateStatList()
	 * @method \int[] getSentCntList()
	 * @method \int[] fillSentCnt()
	 * @method \int[] getTestSentCntList()
	 * @method \int[] fillTestSentCnt()
	 * @method \int[] getErrorCntList()
	 * @method \int[] fillErrorCnt()
	 * @method \int[] getAbuseCntList()
	 * @method \int[] fillAbuseCnt()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_DailyCounter $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_DailyCounter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_DailyCounter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_DailyCounter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_DailyCounter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\DailyCounterTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\DailyCounterTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DailyCounter_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DailyCounter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter_Collection fetchCollection()
	 */
	class EO_DailyCounter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_DailyCounter_Collection wakeUpCollection($rows)
	 */
	class EO_DailyCounter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\GroupContactTable:sender/lib/internals/model/groupcontact.php:3c612dc818b1f7b9245ae5681ff14991 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupContact
	 * @see \Bitrix\Sender\Internals\Model\GroupContactTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact resetDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Sender\EO_Contact getContact()
	 * @method \Bitrix\Sender\EO_Contact remindActualContact()
	 * @method \Bitrix\Sender\EO_Contact requireContact()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact setContact(\Bitrix\Sender\EO_Contact $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact resetContact()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact unsetContact()
	 * @method bool hasContact()
	 * @method bool isContactFilled()
	 * @method bool isContactChanged()
	 * @method \Bitrix\Sender\EO_Contact fillContact()
	 * @method \int getContactId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact setContactId(\int|\Bitrix\Main\DB\SqlExpression $contactId)
	 * @method bool hasContactId()
	 * @method bool isContactIdFilled()
	 * @method bool isContactIdChanged()
	 * @method \int remindActualContactId()
	 * @method \int requireContactId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact resetContactId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact unsetContactId()
	 * @method \int fillContactId()
	 * @method \int getGroupId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int getTypeId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact setTypeId(\int|\Bitrix\Main\DB\SqlExpression $typeId)
	 * @method bool hasTypeId()
	 * @method bool isTypeIdFilled()
	 * @method bool isTypeIdChanged()
	 * @method \int getCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact setCnt(\int|\Bitrix\Main\DB\SqlExpression $cnt)
	 * @method bool hasCnt()
	 * @method bool isCntFilled()
	 * @method bool isCntChanged()
	 * @method \int remindActualCnt()
	 * @method \int requireCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact resetCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact unsetCnt()
	 * @method \int fillCnt()
	 * @method \Bitrix\Sender\EO_Group getGroup()
	 * @method \Bitrix\Sender\EO_Group remindActualGroup()
	 * @method \Bitrix\Sender\EO_Group requireGroup()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact setGroup(\Bitrix\Sender\EO_Group $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact resetGroup()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Sender\EO_Group fillGroup()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupContact wakeUp($data)
	 */
	class EO_GroupContact {
		/* @var \Bitrix\Sender\Internals\Model\GroupContactTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupContactTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupContact_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Sender\EO_Contact[] getContactList()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact_Collection getContactCollection()
	 * @method \Bitrix\Sender\EO_Contact_Collection fillContact()
	 * @method \int[] getContactIdList()
	 * @method \int[] fillContactId()
	 * @method \int[] getGroupIdList()
	 * @method \int[] getTypeIdList()
	 * @method \int[] getCntList()
	 * @method \int[] fillCnt()
	 * @method \Bitrix\Sender\EO_Group[] getGroupList()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact_Collection getGroupCollection()
	 * @method \Bitrix\Sender\EO_Group_Collection fillGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_GroupContact $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_GroupContact $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_GroupContact $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupContact_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupContact_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\GroupContactTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupContactTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupContact_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupContact_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact_Collection fetchCollection()
	 */
	class EO_GroupContact_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupContact_Collection wakeUpCollection($rows)
	 */
	class EO_GroupContact_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\GroupCounterTable:sender/lib/internals/model/groupcounter.php:b33b4e16baece3124a310d03287dce59 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupCounter
	 * @see \Bitrix\Sender\Internals\Model\GroupCounterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getGroupId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int getTypeId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter setTypeId(\int|\Bitrix\Main\DB\SqlExpression $typeId)
	 * @method bool hasTypeId()
	 * @method bool isTypeIdFilled()
	 * @method bool isTypeIdChanged()
	 * @method \int getCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter setCnt(\int|\Bitrix\Main\DB\SqlExpression $cnt)
	 * @method bool hasCnt()
	 * @method bool isCntFilled()
	 * @method bool isCntChanged()
	 * @method \int remindActualCnt()
	 * @method \int requireCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter resetCnt()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter unsetCnt()
	 * @method \int fillCnt()
	 * @method \Bitrix\Sender\EO_Group getGroup()
	 * @method \Bitrix\Sender\EO_Group remindActualGroup()
	 * @method \Bitrix\Sender\EO_Group requireGroup()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter setGroup(\Bitrix\Sender\EO_Group $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter resetGroup()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Sender\EO_Group fillGroup()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupCounter wakeUp($data)
	 */
	class EO_GroupCounter {
		/* @var \Bitrix\Sender\Internals\Model\GroupCounterTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupCounterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupCounter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getGroupIdList()
	 * @method \int[] getTypeIdList()
	 * @method \int[] getCntList()
	 * @method \int[] fillCnt()
	 * @method \Bitrix\Sender\EO_Group[] getGroupList()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter_Collection getGroupCollection()
	 * @method \Bitrix\Sender\EO_Group_Collection fillGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_GroupCounter $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_GroupCounter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_GroupCounter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupCounter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupCounter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\GroupCounterTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupCounterTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupCounter_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupCounter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter_Collection fetchCollection()
	 */
	class EO_GroupCounter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupCounter_Collection wakeUpCollection($rows)
	 */
	class EO_GroupCounter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\GroupQueueTable:sender/lib/internals/model/groupqueue.php:f1b1483d086d5fd4a4ddaa4b8db2fa0a */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupQueue
	 * @see \Bitrix\Sender\Internals\Model\GroupQueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue resetDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \int getType()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue setType(\int|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \int remindActualType()
	 * @method \int requireType()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue resetType()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue unsetType()
	 * @method \int fillType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue resetEntityId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \int getGroupId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue resetGroupId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue unsetGroupId()
	 * @method \int fillGroupId()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupQueue wakeUp($data)
	 */
	class EO_GroupQueue {
		/* @var \Bitrix\Sender\Internals\Model\GroupQueueTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupQueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupQueue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \int[] getTypeList()
	 * @method \int[] fillType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_GroupQueue $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_GroupQueue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_GroupQueue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupQueue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupQueue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\GroupQueueTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupQueueTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupQueue_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupQueue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue_Collection fetchCollection()
	 */
	class EO_GroupQueue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupQueue_Collection wakeUpCollection($rows)
	 */
	class EO_GroupQueue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\GroupStateTable:sender/lib/internals/model/groupstate.php:cf44eb450438a927dab649cbb8b92f00 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupState
	 * @see \Bitrix\Sender\Internals\Model\GroupStateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState resetDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \int getState()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState setState(\int|\Bitrix\Main\DB\SqlExpression $state)
	 * @method bool hasState()
	 * @method bool isStateFilled()
	 * @method bool isStateChanged()
	 * @method \int remindActualState()
	 * @method \int requireState()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState resetState()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState unsetState()
	 * @method \int fillState()
	 * @method \string getFilterId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState setFilterId(\string|\Bitrix\Main\DB\SqlExpression $filterId)
	 * @method bool hasFilterId()
	 * @method bool isFilterIdFilled()
	 * @method bool isFilterIdChanged()
	 * @method \string remindActualFilterId()
	 * @method \string requireFilterId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState resetFilterId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState unsetFilterId()
	 * @method \string fillFilterId()
	 * @method \string getEndpoint()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState setEndpoint(\string|\Bitrix\Main\DB\SqlExpression $endpoint)
	 * @method bool hasEndpoint()
	 * @method bool isEndpointFilled()
	 * @method bool isEndpointChanged()
	 * @method \string remindActualEndpoint()
	 * @method \string requireEndpoint()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState resetEndpoint()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState unsetEndpoint()
	 * @method \string fillEndpoint()
	 * @method \Bitrix\Sender\EO_Group getGroup()
	 * @method \Bitrix\Sender\EO_Group remindActualGroup()
	 * @method \Bitrix\Sender\EO_Group requireGroup()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState setGroup(\Bitrix\Sender\EO_Group $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState resetGroup()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Sender\EO_Group fillGroup()
	 * @method \int getGroupId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState resetGroupId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \int getOffset()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState setOffset(\int|\Bitrix\Main\DB\SqlExpression $offset)
	 * @method bool hasOffset()
	 * @method bool isOffsetFilled()
	 * @method bool isOffsetChanged()
	 * @method \int remindActualOffset()
	 * @method \int requireOffset()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState resetOffset()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState unsetOffset()
	 * @method \int fillOffset()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupState wakeUp($data)
	 */
	class EO_GroupState {
		/* @var \Bitrix\Sender\Internals\Model\GroupStateTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupStateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupState_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \int[] getStateList()
	 * @method \int[] fillState()
	 * @method \string[] getFilterIdList()
	 * @method \string[] fillFilterId()
	 * @method \string[] getEndpointList()
	 * @method \string[] fillEndpoint()
	 * @method \Bitrix\Sender\EO_Group[] getGroupList()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState_Collection getGroupCollection()
	 * @method \Bitrix\Sender\EO_Group_Collection fillGroup()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \int[] getOffsetList()
	 * @method \int[] fillOffset()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_GroupState $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_GroupState $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_GroupState $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupState_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupState_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\GroupStateTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupStateTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupState_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupState_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState_Collection fetchCollection()
	 */
	class EO_GroupState_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupState_Collection wakeUpCollection($rows)
	 */
	class EO_GroupState_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\GroupThreadTable:sender/lib/internals/model/groupthread.php:3c41e4cd0968a5e41a36a3a614f3168b */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupThread
	 * @see \Bitrix\Sender\Internals\Model\GroupThreadTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getThreadId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread setThreadId(\int|\Bitrix\Main\DB\SqlExpression $threadId)
	 * @method bool hasThreadId()
	 * @method bool isThreadIdFilled()
	 * @method bool isThreadIdChanged()
	 * @method \int getGroupStateId()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread setGroupStateId(\int|\Bitrix\Main\DB\SqlExpression $groupStateId)
	 * @method bool hasGroupStateId()
	 * @method bool isGroupStateIdFilled()
	 * @method bool isGroupStateIdChanged()
	 * @method \int getStep()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread setStep(\int|\Bitrix\Main\DB\SqlExpression $step)
	 * @method bool hasStep()
	 * @method bool isStepFilled()
	 * @method bool isStepChanged()
	 * @method \int remindActualStep()
	 * @method \int requireStep()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread resetStep()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread unsetStep()
	 * @method \int fillStep()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread resetStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread unsetStatus()
	 * @method \string fillStatus()
	 * @method \string getThreadType()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread setThreadType(\string|\Bitrix\Main\DB\SqlExpression $threadType)
	 * @method bool hasThreadType()
	 * @method bool isThreadTypeFilled()
	 * @method bool isThreadTypeChanged()
	 * @method \string remindActualThreadType()
	 * @method \string requireThreadType()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread resetThreadType()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread unsetThreadType()
	 * @method \string fillThreadType()
	 * @method \Bitrix\Main\Type\DateTime getExpireAt()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread setExpireAt(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $expireAt)
	 * @method bool hasExpireAt()
	 * @method bool isExpireAtFilled()
	 * @method bool isExpireAtChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualExpireAt()
	 * @method \Bitrix\Main\Type\DateTime requireExpireAt()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread resetExpireAt()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread unsetExpireAt()
	 * @method \Bitrix\Main\Type\DateTime fillExpireAt()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupThread wakeUp($data)
	 */
	class EO_GroupThread {
		/* @var \Bitrix\Sender\Internals\Model\GroupThreadTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupThreadTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_GroupThread_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getThreadIdList()
	 * @method \int[] getGroupStateIdList()
	 * @method \int[] getStepList()
	 * @method \int[] fillStep()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 * @method \string[] getThreadTypeList()
	 * @method \string[] fillThreadType()
	 * @method \Bitrix\Main\Type\DateTime[] getExpireAtList()
	 * @method \Bitrix\Main\Type\DateTime[] fillExpireAt()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_GroupThread $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_GroupThread $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_GroupThread $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_GroupThread_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupThread_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\GroupThreadTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\GroupThreadTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupThread_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupThread_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread_Collection fetchCollection()
	 */
	class EO_GroupThread_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_GroupThread_Collection wakeUpCollection($rows)
	 */
	class EO_GroupThread_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\LetterTable:sender/lib/internals/model/letter.php:07f6f280d8bd1a5c675b2859e2ad08cb */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Letter
	 * @see \Bitrix\Sender\Internals\Model\LetterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getCampaignId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setCampaignId(\int|\Bitrix\Main\DB\SqlExpression $campaignId)
	 * @method bool hasCampaignId()
	 * @method bool isCampaignIdFilled()
	 * @method bool isCampaignIdChanged()
	 * @method \int remindActualCampaignId()
	 * @method \int requireCampaignId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetCampaignId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetCampaignId()
	 * @method \int fillCampaignId()
	 * @method \string getMessageCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setMessageCode(\string|\Bitrix\Main\DB\SqlExpression $messageCode)
	 * @method bool hasMessageCode()
	 * @method bool isMessageCodeFilled()
	 * @method bool isMessageCodeChanged()
	 * @method \string remindActualMessageCode()
	 * @method \string requireMessageCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetMessageCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetMessageCode()
	 * @method \string fillMessageCode()
	 * @method \string getMessageId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setMessageId(\string|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \string remindActualMessageId()
	 * @method \string requireMessageId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetMessageId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetMessageId()
	 * @method \string fillMessageId()
	 * @method \string getTemplateType()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setTemplateType(\string|\Bitrix\Main\DB\SqlExpression $templateType)
	 * @method bool hasTemplateType()
	 * @method bool isTemplateTypeFilled()
	 * @method bool isTemplateTypeChanged()
	 * @method \string remindActualTemplateType()
	 * @method \string requireTemplateType()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetTemplateType()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetTemplateType()
	 * @method \string fillTemplateType()
	 * @method \string getTemplateId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setTemplateId(\string|\Bitrix\Main\DB\SqlExpression $templateId)
	 * @method bool hasTemplateId()
	 * @method bool isTemplateIdFilled()
	 * @method bool isTemplateIdChanged()
	 * @method \string remindActualTemplateId()
	 * @method \string requireTemplateId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetTemplateId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetTemplateId()
	 * @method \string fillTemplateId()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int remindActualPostingId()
	 * @method \int requirePostingId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetPostingId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetPostingId()
	 * @method \int fillPostingId()
	 * @method \int getParentId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetParentId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetParentId()
	 * @method \int fillParentId()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetCreatedBy()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getUpdatedBy()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setUpdatedBy(\int|\Bitrix\Main\DB\SqlExpression $updatedBy)
	 * @method bool hasUpdatedBy()
	 * @method bool isUpdatedByFilled()
	 * @method bool isUpdatedByChanged()
	 * @method \int remindActualUpdatedBy()
	 * @method \int requireUpdatedBy()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetUpdatedBy()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetUpdatedBy()
	 * @method \int fillUpdatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetDateUpdate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetStatus()
	 * @method \string fillStatus()
	 * @method \boolean getReiterate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setReiterate(\boolean|\Bitrix\Main\DB\SqlExpression $reiterate)
	 * @method bool hasReiterate()
	 * @method bool isReiterateFilled()
	 * @method bool isReiterateChanged()
	 * @method \boolean remindActualReiterate()
	 * @method \boolean requireReiterate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetReiterate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetReiterate()
	 * @method \boolean fillReiterate()
	 * @method \boolean getIsTrigger()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setIsTrigger(\boolean|\Bitrix\Main\DB\SqlExpression $isTrigger)
	 * @method bool hasIsTrigger()
	 * @method bool isIsTriggerFilled()
	 * @method bool isIsTriggerChanged()
	 * @method \boolean remindActualIsTrigger()
	 * @method \boolean requireIsTrigger()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetIsTrigger()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetIsTrigger()
	 * @method \boolean fillIsTrigger()
	 * @method \boolean getIsAds()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setIsAds(\boolean|\Bitrix\Main\DB\SqlExpression $isAds)
	 * @method bool hasIsAds()
	 * @method bool isIsAdsFilled()
	 * @method bool isIsAdsChanged()
	 * @method \boolean remindActualIsAds()
	 * @method \boolean requireIsAds()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetIsAds()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetIsAds()
	 * @method \boolean fillIsAds()
	 * @method \Bitrix\Main\Type\DateTime getLastExecuted()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setLastExecuted(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastExecuted)
	 * @method bool hasLastExecuted()
	 * @method bool isLastExecutedFilled()
	 * @method bool isLastExecutedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastExecuted()
	 * @method \Bitrix\Main\Type\DateTime requireLastExecuted()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetLastExecuted()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetLastExecuted()
	 * @method \Bitrix\Main\Type\DateTime fillLastExecuted()
	 * @method \string getTitle()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetTitle()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetTitle()
	 * @method \string fillTitle()
	 * @method \Bitrix\Main\Type\DateTime getAutoSendTime()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setAutoSendTime(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $autoSendTime)
	 * @method bool hasAutoSendTime()
	 * @method bool isAutoSendTimeFilled()
	 * @method bool isAutoSendTimeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualAutoSendTime()
	 * @method \Bitrix\Main\Type\DateTime requireAutoSendTime()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetAutoSendTime()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetAutoSendTime()
	 * @method \Bitrix\Main\Type\DateTime fillAutoSendTime()
	 * @method \string getDaysOfWeek()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setDaysOfWeek(\string|\Bitrix\Main\DB\SqlExpression $daysOfWeek)
	 * @method bool hasDaysOfWeek()
	 * @method bool isDaysOfWeekFilled()
	 * @method bool isDaysOfWeekChanged()
	 * @method \string remindActualDaysOfWeek()
	 * @method \string requireDaysOfWeek()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetDaysOfWeek()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetDaysOfWeek()
	 * @method \string fillDaysOfWeek()
	 * @method \string getDaysOfMonth()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setDaysOfMonth(\string|\Bitrix\Main\DB\SqlExpression $daysOfMonth)
	 * @method bool hasDaysOfMonth()
	 * @method bool isDaysOfMonthFilled()
	 * @method bool isDaysOfMonthChanged()
	 * @method \string remindActualDaysOfMonth()
	 * @method \string requireDaysOfMonth()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetDaysOfMonth()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetDaysOfMonth()
	 * @method \string fillDaysOfMonth()
	 * @method \string getMonthsOfYear()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setMonthsOfYear(\string|\Bitrix\Main\DB\SqlExpression $monthsOfYear)
	 * @method bool hasMonthsOfYear()
	 * @method bool isMonthsOfYearFilled()
	 * @method bool isMonthsOfYearChanged()
	 * @method \string remindActualMonthsOfYear()
	 * @method \string requireMonthsOfYear()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetMonthsOfYear()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetMonthsOfYear()
	 * @method \string fillMonthsOfYear()
	 * @method \string getTimesOfDay()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setTimesOfDay(\string|\Bitrix\Main\DB\SqlExpression $timesOfDay)
	 * @method bool hasTimesOfDay()
	 * @method bool isTimesOfDayFilled()
	 * @method bool isTimesOfDayChanged()
	 * @method \string remindActualTimesOfDay()
	 * @method \string requireTimesOfDay()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetTimesOfDay()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetTimesOfDay()
	 * @method \string fillTimesOfDay()
	 * @method \int getTimeShift()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setTimeShift(\int|\Bitrix\Main\DB\SqlExpression $timeShift)
	 * @method bool hasTimeShift()
	 * @method bool isTimeShiftFilled()
	 * @method bool isTimeShiftChanged()
	 * @method \int remindActualTimeShift()
	 * @method \int requireTimeShift()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetTimeShift()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetTimeShift()
	 * @method \int fillTimeShift()
	 * @method \string getErrorMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setErrorMessage(\string|\Bitrix\Main\DB\SqlExpression $errorMessage)
	 * @method bool hasErrorMessage()
	 * @method bool isErrorMessageFilled()
	 * @method bool isErrorMessageChanged()
	 * @method \string remindActualErrorMessage()
	 * @method \string requireErrorMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetErrorMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetErrorMessage()
	 * @method \string fillErrorMessage()
	 * @method \string getSearchContent()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setSearchContent(\string|\Bitrix\Main\DB\SqlExpression $searchContent)
	 * @method bool hasSearchContent()
	 * @method bool isSearchContentFilled()
	 * @method bool isSearchContentChanged()
	 * @method \string remindActualSearchContent()
	 * @method \string requireSearchContent()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetSearchContent()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetSearchContent()
	 * @method \string fillSearchContent()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message getMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message remindActualMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message requireMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setMessage(\Bitrix\Sender\Internals\Model\EO_Message $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetMessage()
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message fillMessage()
	 * @method \Bitrix\Sender\EO_Mailing getCampaign()
	 * @method \Bitrix\Sender\EO_Mailing remindActualCampaign()
	 * @method \Bitrix\Sender\EO_Mailing requireCampaign()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setCampaign(\Bitrix\Sender\EO_Mailing $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetCampaign()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetCampaign()
	 * @method bool hasCampaign()
	 * @method bool isCampaignFilled()
	 * @method bool isCampaignChanged()
	 * @method \Bitrix\Sender\EO_Mailing fillCampaign()
	 * @method \Bitrix\Sender\EO_Posting getCurrentPosting()
	 * @method \Bitrix\Sender\EO_Posting remindActualCurrentPosting()
	 * @method \Bitrix\Sender\EO_Posting requireCurrentPosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setCurrentPosting(\Bitrix\Sender\EO_Posting $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetCurrentPosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetCurrentPosting()
	 * @method bool hasCurrentPosting()
	 * @method bool isCurrentPostingFilled()
	 * @method bool isCurrentPostingChanged()
	 * @method \Bitrix\Sender\EO_Posting fillCurrentPosting()
	 * @method \Bitrix\Sender\EO_Posting getPosting()
	 * @method \Bitrix\Sender\EO_Posting remindActualPosting()
	 * @method \Bitrix\Sender\EO_Posting requirePosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setPosting(\Bitrix\Sender\EO_Posting $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetPosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetPosting()
	 * @method bool hasPosting()
	 * @method bool isPostingFilled()
	 * @method bool isPostingChanged()
	 * @method \Bitrix\Sender\EO_Posting fillPosting()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetCreatedByUser()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \boolean getWaitingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter setWaitingRecipient(\boolean|\Bitrix\Main\DB\SqlExpression $waitingRecipient)
	 * @method bool hasWaitingRecipient()
	 * @method bool isWaitingRecipientFilled()
	 * @method bool isWaitingRecipientChanged()
	 * @method \boolean remindActualWaitingRecipient()
	 * @method \boolean requireWaitingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter resetWaitingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unsetWaitingRecipient()
	 * @method \boolean fillWaitingRecipient()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_Letter wakeUp($data)
	 */
	class EO_Letter {
		/* @var \Bitrix\Sender\Internals\Model\LetterTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\LetterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Letter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getCampaignIdList()
	 * @method \int[] fillCampaignId()
	 * @method \string[] getMessageCodeList()
	 * @method \string[] fillMessageCode()
	 * @method \string[] getMessageIdList()
	 * @method \string[] fillMessageId()
	 * @method \string[] getTemplateTypeList()
	 * @method \string[] fillTemplateType()
	 * @method \string[] getTemplateIdList()
	 * @method \string[] fillTemplateId()
	 * @method \int[] getPostingIdList()
	 * @method \int[] fillPostingId()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getUpdatedByList()
	 * @method \int[] fillUpdatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 * @method \boolean[] getReiterateList()
	 * @method \boolean[] fillReiterate()
	 * @method \boolean[] getIsTriggerList()
	 * @method \boolean[] fillIsTrigger()
	 * @method \boolean[] getIsAdsList()
	 * @method \boolean[] fillIsAds()
	 * @method \Bitrix\Main\Type\DateTime[] getLastExecutedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastExecuted()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \Bitrix\Main\Type\DateTime[] getAutoSendTimeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillAutoSendTime()
	 * @method \string[] getDaysOfWeekList()
	 * @method \string[] fillDaysOfWeek()
	 * @method \string[] getDaysOfMonthList()
	 * @method \string[] fillDaysOfMonth()
	 * @method \string[] getMonthsOfYearList()
	 * @method \string[] fillMonthsOfYear()
	 * @method \string[] getTimesOfDayList()
	 * @method \string[] fillTimesOfDay()
	 * @method \int[] getTimeShiftList()
	 * @method \int[] fillTimeShift()
	 * @method \string[] getErrorMessageList()
	 * @method \string[] fillErrorMessage()
	 * @method \string[] getSearchContentList()
	 * @method \string[] fillSearchContent()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message[] getMessageList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection getMessageCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message_Collection fillMessage()
	 * @method \Bitrix\Sender\EO_Mailing[] getCampaignList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection getCampaignCollection()
	 * @method \Bitrix\Sender\EO_Mailing_Collection fillCampaign()
	 * @method \Bitrix\Sender\EO_Posting[] getCurrentPostingList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection getCurrentPostingCollection()
	 * @method \Bitrix\Sender\EO_Posting_Collection fillCurrentPosting()
	 * @method \Bitrix\Sender\EO_Posting[] getPostingList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection getPostingCollection()
	 * @method \Bitrix\Sender\EO_Posting_Collection fillPosting()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \boolean[] getWaitingRecipientList()
	 * @method \boolean[] fillWaitingRecipient()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_Letter $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_Letter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_Letter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_Letter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Letter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\LetterTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\LetterTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Letter_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Letter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection fetchCollection()
	 */
	class EO_Letter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection wakeUpCollection($rows)
	 */
	class EO_Letter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\LetterSegmentTable:sender/lib/internals/model/lettersegment.php:fb8d263045d008f30f02f9dbd6100583 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_LetterSegment
	 * @see \Bitrix\Sender\Internals\Model\LetterSegmentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getLetterId()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment setLetterId(\int|\Bitrix\Main\DB\SqlExpression $letterId)
	 * @method bool hasLetterId()
	 * @method bool isLetterIdFilled()
	 * @method bool isLetterIdChanged()
	 * @method \int getSegmentId()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment setSegmentId(\int|\Bitrix\Main\DB\SqlExpression $segmentId)
	 * @method bool hasSegmentId()
	 * @method bool isSegmentIdFilled()
	 * @method bool isSegmentIdChanged()
	 * @method \boolean getInclude()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment setInclude(\boolean|\Bitrix\Main\DB\SqlExpression $include)
	 * @method bool hasInclude()
	 * @method bool isIncludeFilled()
	 * @method bool isIncludeChanged()
	 * @method \boolean remindActualInclude()
	 * @method \boolean requireInclude()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment resetInclude()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment unsetInclude()
	 * @method \boolean fillInclude()
	 * @method \Bitrix\Sender\EO_MailingChain getLetter()
	 * @method \Bitrix\Sender\EO_MailingChain remindActualLetter()
	 * @method \Bitrix\Sender\EO_MailingChain requireLetter()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment setLetter(\Bitrix\Sender\EO_MailingChain $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment resetLetter()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment unsetLetter()
	 * @method bool hasLetter()
	 * @method bool isLetterFilled()
	 * @method bool isLetterChanged()
	 * @method \Bitrix\Sender\EO_MailingChain fillLetter()
	 * @method \Bitrix\Sender\EO_Group getSegment()
	 * @method \Bitrix\Sender\EO_Group remindActualSegment()
	 * @method \Bitrix\Sender\EO_Group requireSegment()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment setSegment(\Bitrix\Sender\EO_Group $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment resetSegment()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment unsetSegment()
	 * @method bool hasSegment()
	 * @method bool isSegmentFilled()
	 * @method bool isSegmentChanged()
	 * @method \Bitrix\Sender\EO_Group fillSegment()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_LetterSegment wakeUp($data)
	 */
	class EO_LetterSegment {
		/* @var \Bitrix\Sender\Internals\Model\LetterSegmentTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\LetterSegmentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_LetterSegment_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getLetterIdList()
	 * @method \int[] getSegmentIdList()
	 * @method \boolean[] getIncludeList()
	 * @method \boolean[] fillInclude()
	 * @method \Bitrix\Sender\EO_MailingChain[] getLetterList()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment_Collection getLetterCollection()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection fillLetter()
	 * @method \Bitrix\Sender\EO_Group[] getSegmentList()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment_Collection getSegmentCollection()
	 * @method \Bitrix\Sender\EO_Group_Collection fillSegment()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_LetterSegment $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_LetterSegment $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_LetterSegment $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_LetterSegment_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LetterSegment_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\LetterSegmentTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\LetterSegmentTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LetterSegment_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LetterSegment_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment_Collection fetchCollection()
	 */
	class EO_LetterSegment_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_LetterSegment_Collection wakeUpCollection($rows)
	 */
	class EO_LetterSegment_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\MessageTable:sender/lib/internals/model/message.php:f416d8f5a65383423f7466363509fc74 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Message
	 * @see \Bitrix\Sender\Internals\Model\MessageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message resetCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message unsetCode()
	 * @method \string fillCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm getUtm()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm remindActualUtm()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm requireUtm()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message setUtm(\Bitrix\Sender\Internals\Model\EO_MessageUtm $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Message resetUtm()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message unsetUtm()
	 * @method bool hasUtm()
	 * @method bool isUtmFilled()
	 * @method bool isUtmChanged()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm fillUtm()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_Message set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_Message reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_Message unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_Message wakeUp($data)
	 */
	class EO_Message {
		/* @var \Bitrix\Sender\Internals\Model\MessageTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\MessageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Message_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm[] getUtmList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message_Collection getUtmCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm_Collection fillUtm()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_Message $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_Message $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Message getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Message[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_Message $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_Message_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_Message current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Message_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\MessageTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\MessageTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Message_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Message_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Message fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message_Collection fetchCollection()
	 */
	class EO_Message_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Message createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_Message_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_Message_Collection wakeUpCollection($rows)
	 */
	class EO_Message_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\MessageFieldTable:sender/lib/internals/model/messagefield.php:b8632b39cc7b31f5988f8479c8025b25 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_MessageField
	 * @see \Bitrix\Sender\Internals\Model\MessageFieldTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getMessageId()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string getType()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField resetType()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField unsetType()
	 * @method \string fillType()
	 * @method \string getValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField resetValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField unsetValue()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_MessageField wakeUp($data)
	 */
	class EO_MessageField {
		/* @var \Bitrix\Sender\Internals\Model\MessageFieldTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\MessageFieldTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_MessageField_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getMessageIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_MessageField $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_MessageField $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_MessageField $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_MessageField_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_MessageField_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\MessageFieldTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\MessageFieldTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MessageField_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MessageField_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField_Collection fetchCollection()
	 */
	class EO_MessageField_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageField_Collection wakeUpCollection($rows)
	 */
	class EO_MessageField_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\MessageUtmTable:sender/lib/internals/model/messageutm.php:9049c840187708f6ae2378cefd165bf6 */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_MessageUtm
	 * @see \Bitrix\Sender\Internals\Model\MessageUtmTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getMessageId()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string getValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm resetValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm unsetValue()
	 * @method \string fillValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message getMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message remindActualMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message requireMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm setMessage(\Bitrix\Sender\Internals\Model\EO_Message $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm resetMessage()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm unsetMessage()
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message fillMessage()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_MessageUtm wakeUp($data)
	 */
	class EO_MessageUtm {
		/* @var \Bitrix\Sender\Internals\Model\MessageUtmTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\MessageUtmTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_MessageUtm_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getMessageIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message[] getMessageList()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm_Collection getMessageCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Message_Collection fillMessage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_MessageUtm $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_MessageUtm $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_MessageUtm $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_MessageUtm_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_MessageUtm_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\MessageUtmTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\MessageUtmTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MessageUtm_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MessageUtm_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm_Collection fetchCollection()
	 */
	class EO_MessageUtm_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_MessageUtm_Collection wakeUpCollection($rows)
	 */
	class EO_MessageUtm_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\Posting\ClickTable:sender/lib/internals/model/posting/click.php:008c29077f145f70a58d6f3c90ea3b1b */
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * EO_Click
	 * @see \Bitrix\Sender\Internals\Model\Posting\ClickTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int remindActualPostingId()
	 * @method \int requirePostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click resetPostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click unsetPostingId()
	 * @method \int fillPostingId()
	 * @method \int getRecipientId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click setRecipientId(\int|\Bitrix\Main\DB\SqlExpression $recipientId)
	 * @method bool hasRecipientId()
	 * @method bool isRecipientIdFilled()
	 * @method bool isRecipientIdChanged()
	 * @method \int remindActualRecipientId()
	 * @method \int requireRecipientId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click resetRecipientId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click unsetRecipientId()
	 * @method \int fillRecipientId()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click resetDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getUrl()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click setUrl(\string|\Bitrix\Main\DB\SqlExpression $url)
	 * @method bool hasUrl()
	 * @method bool isUrlFilled()
	 * @method bool isUrlChanged()
	 * @method \string remindActualUrl()
	 * @method \string requireUrl()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click resetUrl()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click unsetUrl()
	 * @method \string fillUrl()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting getPosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting remindActualPosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting requirePosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click setPosting(\Bitrix\Sender\Internals\Model\EO_Posting $object)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click resetPosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click unsetPosting()
	 * @method bool hasPosting()
	 * @method bool isPostingFilled()
	 * @method bool isPostingChanged()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting fillPosting()
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
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Click wakeUp($data)
	 */
	class EO_Click {
		/* @var \Bitrix\Sender\Internals\Model\Posting\ClickTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Posting\ClickTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * EO_Click_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostingIdList()
	 * @method \int[] fillPostingId()
	 * @method \int[] getRecipientIdList()
	 * @method \int[] fillRecipientId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getUrlList()
	 * @method \string[] fillUrl()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting[] getPostingList()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click_Collection getPostingCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection fillPosting()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\Posting\EO_Click $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\Posting\EO_Click $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\Posting\EO_Click $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Click_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Click_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\Posting\ClickTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Posting\ClickTable';
	}
}
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Click_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Click_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click_Collection fetchCollection()
	 */
	class EO_Click_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click_Collection wakeUpCollection($rows)
	 */
	class EO_Click_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\Posting\ReadTable:sender/lib/internals/model/posting/read.php:f1f08e493ded576083945f20dcc07883 */
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * EO_Read
	 * @see \Bitrix\Sender\Internals\Model\Posting\ReadTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int remindActualPostingId()
	 * @method \int requirePostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read resetPostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read unsetPostingId()
	 * @method \int fillPostingId()
	 * @method \int getRecipientId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read setRecipientId(\int|\Bitrix\Main\DB\SqlExpression $recipientId)
	 * @method bool hasRecipientId()
	 * @method bool isRecipientIdFilled()
	 * @method bool isRecipientIdChanged()
	 * @method \int remindActualRecipientId()
	 * @method \int requireRecipientId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read resetRecipientId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read unsetRecipientId()
	 * @method \int fillRecipientId()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read resetDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read unsetDateInsert()
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
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Read wakeUp($data)
	 */
	class EO_Read {
		/* @var \Bitrix\Sender\Internals\Model\Posting\ReadTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Posting\ReadTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * EO_Read_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostingIdList()
	 * @method \int[] fillPostingId()
	 * @method \int[] getRecipientIdList()
	 * @method \int[] fillRecipientId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\Posting\EO_Read $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\Posting\EO_Read $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\Posting\EO_Read $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Read_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Read_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\Posting\ReadTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Posting\ReadTable';
	}
}
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Read_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Read_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read_Collection fetchCollection()
	 */
	class EO_Read_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read_Collection wakeUpCollection($rows)
	 */
	class EO_Read_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\Posting\RecipientTable:sender/lib/internals/model/posting/recipient.php:15c29af0aa47067458ae97d97ff0b375 */
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * EO_Recipient
	 * @see \Bitrix\Sender\Internals\Model\Posting\RecipientTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int remindActualPostingId()
	 * @method \int requirePostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetPostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetPostingId()
	 * @method \int fillPostingId()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetStatus()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetStatus()
	 * @method \string fillStatus()
	 * @method \Bitrix\Main\Type\DateTime getDateSent()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setDateSent(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateSent)
	 * @method bool hasDateSent()
	 * @method bool isDateSentFilled()
	 * @method bool isDateSentChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateSent()
	 * @method \Bitrix\Main\Type\DateTime requireDateSent()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetDateSent()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetDateSent()
	 * @method \Bitrix\Main\Type\DateTime fillDateSent()
	 * @method \Bitrix\Main\Type\DateTime getDateDeny()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setDateDeny(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateDeny)
	 * @method bool hasDateDeny()
	 * @method bool isDateDenyFilled()
	 * @method bool isDateDenyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateDeny()
	 * @method \Bitrix\Main\Type\DateTime requireDateDeny()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetDateDeny()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetDateDeny()
	 * @method \Bitrix\Main\Type\DateTime fillDateDeny()
	 * @method \int getContactId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setContactId(\int|\Bitrix\Main\DB\SqlExpression $contactId)
	 * @method bool hasContactId()
	 * @method bool isContactIdFilled()
	 * @method bool isContactIdChanged()
	 * @method \int remindActualContactId()
	 * @method \int requireContactId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetContactId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetContactId()
	 * @method \int fillContactId()
	 * @method \int getUserId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetUserId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getFields()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setFields(\string|\Bitrix\Main\DB\SqlExpression $fields)
	 * @method bool hasFields()
	 * @method bool isFieldsFilled()
	 * @method bool isFieldsChanged()
	 * @method \string remindActualFields()
	 * @method \string requireFields()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetFields()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetFields()
	 * @method \string fillFields()
	 * @method \int getRootId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setRootId(\int|\Bitrix\Main\DB\SqlExpression $rootId)
	 * @method bool hasRootId()
	 * @method bool isRootIdFilled()
	 * @method bool isRootIdChanged()
	 * @method \int remindActualRootId()
	 * @method \int requireRootId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetRootId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetRootId()
	 * @method \int fillRootId()
	 * @method \string getIsRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setIsRead(\string|\Bitrix\Main\DB\SqlExpression $isRead)
	 * @method bool hasIsRead()
	 * @method bool isIsReadFilled()
	 * @method bool isIsReadChanged()
	 * @method \string remindActualIsRead()
	 * @method \string requireIsRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetIsRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetIsRead()
	 * @method \string fillIsRead()
	 * @method \string getIsClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setIsClick(\string|\Bitrix\Main\DB\SqlExpression $isClick)
	 * @method bool hasIsClick()
	 * @method bool isIsClickFilled()
	 * @method bool isIsClickChanged()
	 * @method \string remindActualIsClick()
	 * @method \string requireIsClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetIsClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetIsClick()
	 * @method \string fillIsClick()
	 * @method \string getIsUnsub()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setIsUnsub(\string|\Bitrix\Main\DB\SqlExpression $isUnsub)
	 * @method bool hasIsUnsub()
	 * @method bool isIsUnsubFilled()
	 * @method bool isIsUnsubChanged()
	 * @method \string remindActualIsUnsub()
	 * @method \string requireIsUnsub()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetIsUnsub()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetIsUnsub()
	 * @method \string fillIsUnsub()
	 * @method \Bitrix\Sender\EO_Contact getContact()
	 * @method \Bitrix\Sender\EO_Contact remindActualContact()
	 * @method \Bitrix\Sender\EO_Contact requireContact()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setContact(\Bitrix\Sender\EO_Contact $object)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetContact()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetContact()
	 * @method bool hasContact()
	 * @method bool isContactFilled()
	 * @method bool isContactChanged()
	 * @method \Bitrix\Sender\EO_Contact fillContact()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting getPosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting remindActualPosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting requirePosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setPosting(\Bitrix\Sender\Internals\Model\EO_Posting $object)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetPosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetPosting()
	 * @method bool hasPosting()
	 * @method bool isPostingFilled()
	 * @method bool isPostingChanged()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting fillPosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read getPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read remindActualPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read requirePostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setPostingRead(\Bitrix\Sender\Internals\Model\Posting\EO_Read $object)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetPostingRead()
	 * @method bool hasPostingRead()
	 * @method bool isPostingReadFilled()
	 * @method bool isPostingReadChanged()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read fillPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click getPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click remindActualPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click requirePostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setPostingClick(\Bitrix\Sender\Internals\Model\Posting\EO_Click $object)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetPostingClick()
	 * @method bool hasPostingClick()
	 * @method bool isPostingClickFilled()
	 * @method bool isPostingClickChanged()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click fillPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub getPostingUnsub()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub remindActualPostingUnsub()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub requirePostingUnsub()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient setPostingUnsub(\Bitrix\Sender\Internals\Model\Posting\EO_Unsub $object)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient resetPostingUnsub()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unsetPostingUnsub()
	 * @method bool hasPostingUnsub()
	 * @method bool isPostingUnsubFilled()
	 * @method bool isPostingUnsubChanged()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub fillPostingUnsub()
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
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Recipient wakeUp($data)
	 */
	class EO_Recipient {
		/* @var \Bitrix\Sender\Internals\Model\Posting\RecipientTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Posting\RecipientTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * EO_Recipient_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostingIdList()
	 * @method \int[] fillPostingId()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 * @method \Bitrix\Main\Type\DateTime[] getDateSentList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateSent()
	 * @method \Bitrix\Main\Type\DateTime[] getDateDenyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateDeny()
	 * @method \int[] getContactIdList()
	 * @method \int[] fillContactId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getFieldsList()
	 * @method \string[] fillFields()
	 * @method \int[] getRootIdList()
	 * @method \int[] fillRootId()
	 * @method \string[] getIsReadList()
	 * @method \string[] fillIsRead()
	 * @method \string[] getIsClickList()
	 * @method \string[] fillIsClick()
	 * @method \string[] getIsUnsubList()
	 * @method \string[] fillIsUnsub()
	 * @method \Bitrix\Sender\EO_Contact[] getContactList()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection getContactCollection()
	 * @method \Bitrix\Sender\EO_Contact_Collection fillContact()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting[] getPostingList()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection getPostingCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection fillPosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read[] getPostingReadList()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection getPostingReadCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read_Collection fillPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click[] getPostingClickList()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection getPostingClickCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click_Collection fillPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub[] getPostingUnsubList()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection getPostingUnsubCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection fillPostingUnsub()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\Posting\EO_Recipient $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\Posting\EO_Recipient $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\Posting\EO_Recipient $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Recipient_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\Posting\RecipientTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Posting\RecipientTable';
	}
}
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Recipient_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Recipient_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection fetchCollection()
	 */
	class EO_Recipient_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection wakeUpCollection($rows)
	 */
	class EO_Recipient_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\Posting\UnsubTable:sender/lib/internals/model/posting/unsub.php:e87d23e571e756348bbba24f6930ca59 */
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * EO_Unsub
	 * @see \Bitrix\Sender\Internals\Model\Posting\UnsubTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int remindActualPostingId()
	 * @method \int requirePostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub resetPostingId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub unsetPostingId()
	 * @method \int fillPostingId()
	 * @method \int getRecipientId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub setRecipientId(\int|\Bitrix\Main\DB\SqlExpression $recipientId)
	 * @method bool hasRecipientId()
	 * @method bool isRecipientIdFilled()
	 * @method bool isRecipientIdChanged()
	 * @method \int remindActualRecipientId()
	 * @method \int requireRecipientId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub resetRecipientId()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub unsetRecipientId()
	 * @method \int fillRecipientId()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub resetDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting getPosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting remindActualPosting()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting requirePosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub setPosting(\Bitrix\Sender\Internals\Model\EO_Posting $object)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub resetPosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub unsetPosting()
	 * @method bool hasPosting()
	 * @method bool isPostingFilled()
	 * @method bool isPostingChanged()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting fillPosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient getPostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient remindActualPostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient requirePostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub setPostingRecipient(\Bitrix\Sender\Internals\Model\Posting\EO_Recipient $object)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub resetPostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub unsetPostingRecipient()
	 * @method bool hasPostingRecipient()
	 * @method bool isPostingRecipientFilled()
	 * @method bool isPostingRecipientChanged()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient fillPostingRecipient()
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
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Unsub wakeUp($data)
	 */
	class EO_Unsub {
		/* @var \Bitrix\Sender\Internals\Model\Posting\UnsubTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Posting\UnsubTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * EO_Unsub_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostingIdList()
	 * @method \int[] fillPostingId()
	 * @method \int[] getRecipientIdList()
	 * @method \int[] fillRecipientId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting[] getPostingList()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection getPostingCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection fillPosting()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient[] getPostingRecipientList()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection getPostingRecipientCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection fillPostingRecipient()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\Posting\EO_Unsub $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\Posting\EO_Unsub $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\Posting\EO_Unsub $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Unsub_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\Posting\UnsubTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Posting\UnsubTable';
	}
}
namespace Bitrix\Sender\Internals\Model\Posting {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Unsub_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Unsub_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection fetchCollection()
	 */
	class EO_Unsub_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection wakeUpCollection($rows)
	 */
	class EO_Unsub_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\PostingTable:sender/lib/internals/model/posting.php:8a4d774513d9ea84616941d50154f2fb */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Posting
	 * @see \Bitrix\Sender\Internals\Model\PostingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getCampaignId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setCampaignId(\int|\Bitrix\Main\DB\SqlExpression $campaignId)
	 * @method bool hasCampaignId()
	 * @method bool isCampaignIdFilled()
	 * @method bool isCampaignIdChanged()
	 * @method \int remindActualCampaignId()
	 * @method \int requireCampaignId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetCampaignId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetCampaignId()
	 * @method \int fillCampaignId()
	 * @method \int getLetterId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setLetterId(\int|\Bitrix\Main\DB\SqlExpression $letterId)
	 * @method bool hasLetterId()
	 * @method bool isLetterIdFilled()
	 * @method bool isLetterIdChanged()
	 * @method \int remindActualLetterId()
	 * @method \int requireLetterId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetLetterId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetLetterId()
	 * @method \int fillLetterId()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetDateCreate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetDateUpdate()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetStatus()
	 * @method \string fillStatus()
	 * @method \Bitrix\Main\Type\DateTime getDateSend()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setDateSend(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateSend)
	 * @method bool hasDateSend()
	 * @method bool isDateSendFilled()
	 * @method bool isDateSendChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateSend()
	 * @method \Bitrix\Main\Type\DateTime requireDateSend()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetDateSend()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetDateSend()
	 * @method \Bitrix\Main\Type\DateTime fillDateSend()
	 * @method \Bitrix\Main\Type\DateTime getDatePause()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setDatePause(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $datePause)
	 * @method bool hasDatePause()
	 * @method bool isDatePauseFilled()
	 * @method bool isDatePauseChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDatePause()
	 * @method \Bitrix\Main\Type\DateTime requireDatePause()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetDatePause()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetDatePause()
	 * @method \Bitrix\Main\Type\DateTime fillDatePause()
	 * @method \Bitrix\Main\Type\DateTime getDateSent()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setDateSent(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateSent)
	 * @method bool hasDateSent()
	 * @method bool isDateSentFilled()
	 * @method bool isDateSentChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateSent()
	 * @method \Bitrix\Main\Type\DateTime requireDateSent()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetDateSent()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetDateSent()
	 * @method \Bitrix\Main\Type\DateTime fillDateSent()
	 * @method \int getCountRead()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setCountRead(\int|\Bitrix\Main\DB\SqlExpression $countRead)
	 * @method bool hasCountRead()
	 * @method bool isCountReadFilled()
	 * @method bool isCountReadChanged()
	 * @method \int remindActualCountRead()
	 * @method \int requireCountRead()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetCountRead()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetCountRead()
	 * @method \int fillCountRead()
	 * @method \int getCountClick()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setCountClick(\int|\Bitrix\Main\DB\SqlExpression $countClick)
	 * @method bool hasCountClick()
	 * @method bool isCountClickFilled()
	 * @method bool isCountClickChanged()
	 * @method \int remindActualCountClick()
	 * @method \int requireCountClick()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetCountClick()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetCountClick()
	 * @method \int fillCountClick()
	 * @method \int getCountUnsub()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setCountUnsub(\int|\Bitrix\Main\DB\SqlExpression $countUnsub)
	 * @method bool hasCountUnsub()
	 * @method bool isCountUnsubFilled()
	 * @method bool isCountUnsubChanged()
	 * @method \int remindActualCountUnsub()
	 * @method \int requireCountUnsub()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetCountUnsub()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetCountUnsub()
	 * @method \int fillCountUnsub()
	 * @method \int getCountSendAll()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setCountSendAll(\int|\Bitrix\Main\DB\SqlExpression $countSendAll)
	 * @method bool hasCountSendAll()
	 * @method bool isCountSendAllFilled()
	 * @method bool isCountSendAllChanged()
	 * @method \int remindActualCountSendAll()
	 * @method \int requireCountSendAll()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetCountSendAll()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetCountSendAll()
	 * @method \int fillCountSendAll()
	 * @method \int getCountSendNone()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setCountSendNone(\int|\Bitrix\Main\DB\SqlExpression $countSendNone)
	 * @method bool hasCountSendNone()
	 * @method bool isCountSendNoneFilled()
	 * @method bool isCountSendNoneChanged()
	 * @method \int remindActualCountSendNone()
	 * @method \int requireCountSendNone()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetCountSendNone()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetCountSendNone()
	 * @method \int fillCountSendNone()
	 * @method \int getCountSendError()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setCountSendError(\int|\Bitrix\Main\DB\SqlExpression $countSendError)
	 * @method bool hasCountSendError()
	 * @method bool isCountSendErrorFilled()
	 * @method bool isCountSendErrorChanged()
	 * @method \int remindActualCountSendError()
	 * @method \int requireCountSendError()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetCountSendError()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetCountSendError()
	 * @method \int fillCountSendError()
	 * @method \int getCountSendSuccess()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setCountSendSuccess(\int|\Bitrix\Main\DB\SqlExpression $countSendSuccess)
	 * @method bool hasCountSendSuccess()
	 * @method bool isCountSendSuccessFilled()
	 * @method bool isCountSendSuccessChanged()
	 * @method \int remindActualCountSendSuccess()
	 * @method \int requireCountSendSuccess()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetCountSendSuccess()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetCountSendSuccess()
	 * @method \int fillCountSendSuccess()
	 * @method \int getCountSendDeny()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setCountSendDeny(\int|\Bitrix\Main\DB\SqlExpression $countSendDeny)
	 * @method bool hasCountSendDeny()
	 * @method bool isCountSendDenyFilled()
	 * @method bool isCountSendDenyChanged()
	 * @method \int remindActualCountSendDeny()
	 * @method \int requireCountSendDeny()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetCountSendDeny()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetCountSendDeny()
	 * @method \int fillCountSendDeny()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter getLetter()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter remindActualLetter()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter requireLetter()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setLetter(\Bitrix\Sender\Internals\Model\EO_Letter $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetLetter()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetLetter()
	 * @method bool hasLetter()
	 * @method bool isLetterFilled()
	 * @method bool isLetterChanged()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter fillLetter()
	 * @method \Bitrix\Sender\EO_Mailing getMailing()
	 * @method \Bitrix\Sender\EO_Mailing remindActualMailing()
	 * @method \Bitrix\Sender\EO_Mailing requireMailing()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setMailing(\Bitrix\Sender\EO_Mailing $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetMailing()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetMailing()
	 * @method bool hasMailing()
	 * @method bool isMailingFilled()
	 * @method bool isMailingChanged()
	 * @method \Bitrix\Sender\EO_Mailing fillMailing()
	 * @method \Bitrix\Sender\EO_MailingChain getMailingChain()
	 * @method \Bitrix\Sender\EO_MailingChain remindActualMailingChain()
	 * @method \Bitrix\Sender\EO_MailingChain requireMailingChain()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setMailingChain(\Bitrix\Sender\EO_MailingChain $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetMailingChain()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetMailingChain()
	 * @method bool hasMailingChain()
	 * @method bool isMailingChainFilled()
	 * @method bool isMailingChainChanged()
	 * @method \Bitrix\Sender\EO_MailingChain fillMailingChain()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient getPostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient remindActualPostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient requirePostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setPostingRecipient(\Bitrix\Sender\Internals\Model\Posting\EO_Recipient $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetPostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetPostingRecipient()
	 * @method bool hasPostingRecipient()
	 * @method bool isPostingRecipientFilled()
	 * @method bool isPostingRecipientChanged()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient fillPostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read getPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read remindActualPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read requirePostingRead()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setPostingRead(\Bitrix\Sender\Internals\Model\Posting\EO_Read $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetPostingRead()
	 * @method bool hasPostingRead()
	 * @method bool isPostingReadFilled()
	 * @method bool isPostingReadChanged()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read fillPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click getPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click remindActualPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click requirePostingClick()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setPostingClick(\Bitrix\Sender\Internals\Model\Posting\EO_Click $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetPostingClick()
	 * @method bool hasPostingClick()
	 * @method bool isPostingClickFilled()
	 * @method bool isPostingClickChanged()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click fillPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub getPostingUnsub()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub remindActualPostingUnsub()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub requirePostingUnsub()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting setPostingUnsub(\Bitrix\Sender\Internals\Model\Posting\EO_Unsub $object)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting resetPostingUnsub()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unsetPostingUnsub()
	 * @method bool hasPostingUnsub()
	 * @method bool isPostingUnsubFilled()
	 * @method bool isPostingUnsubChanged()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub fillPostingUnsub()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_Posting wakeUp($data)
	 */
	class EO_Posting {
		/* @var \Bitrix\Sender\Internals\Model\PostingTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\PostingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Posting_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getCampaignIdList()
	 * @method \int[] fillCampaignId()
	 * @method \int[] getLetterIdList()
	 * @method \int[] fillLetterId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 * @method \Bitrix\Main\Type\DateTime[] getDateSendList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateSend()
	 * @method \Bitrix\Main\Type\DateTime[] getDatePauseList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDatePause()
	 * @method \Bitrix\Main\Type\DateTime[] getDateSentList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateSent()
	 * @method \int[] getCountReadList()
	 * @method \int[] fillCountRead()
	 * @method \int[] getCountClickList()
	 * @method \int[] fillCountClick()
	 * @method \int[] getCountUnsubList()
	 * @method \int[] fillCountUnsub()
	 * @method \int[] getCountSendAllList()
	 * @method \int[] fillCountSendAll()
	 * @method \int[] getCountSendNoneList()
	 * @method \int[] fillCountSendNone()
	 * @method \int[] getCountSendErrorList()
	 * @method \int[] fillCountSendError()
	 * @method \int[] getCountSendSuccessList()
	 * @method \int[] fillCountSendSuccess()
	 * @method \int[] getCountSendDenyList()
	 * @method \int[] fillCountSendDeny()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter[] getLetterList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection getLetterCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection fillLetter()
	 * @method \Bitrix\Sender\EO_Mailing[] getMailingList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection getMailingCollection()
	 * @method \Bitrix\Sender\EO_Mailing_Collection fillMailing()
	 * @method \Bitrix\Sender\EO_MailingChain[] getMailingChainList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection getMailingChainCollection()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection fillMailingChain()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient[] getPostingRecipientList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection getPostingRecipientCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection fillPostingRecipient()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read[] getPostingReadList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection getPostingReadCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Read_Collection fillPostingRead()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click[] getPostingClickList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection getPostingClickCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Click_Collection fillPostingClick()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub[] getPostingUnsubList()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection getPostingUnsubCollection()
	 * @method \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection fillPostingUnsub()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_Posting $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_Posting $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_Posting $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_Posting_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Posting_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\PostingTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\PostingTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Posting_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Posting_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection fetchCollection()
	 */
	class EO_Posting_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_Posting_Collection wakeUpCollection($rows)
	 */
	class EO_Posting_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\PostingThreadTable:sender/lib/internals/model/postingthread.php:8984bd4945c866cd5d2de6b6fbddd72a */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_PostingThread
	 * @see \Bitrix\Sender\Internals\Model\PostingThreadTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getThreadId()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread setThreadId(\int|\Bitrix\Main\DB\SqlExpression $threadId)
	 * @method bool hasThreadId()
	 * @method bool isThreadIdFilled()
	 * @method bool isThreadIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread resetStatus()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread unsetStatus()
	 * @method \string fillStatus()
	 * @method \string getThreadType()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread setThreadType(\string|\Bitrix\Main\DB\SqlExpression $threadType)
	 * @method bool hasThreadType()
	 * @method bool isThreadTypeFilled()
	 * @method bool isThreadTypeChanged()
	 * @method \string remindActualThreadType()
	 * @method \string requireThreadType()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread resetThreadType()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread unsetThreadType()
	 * @method \string fillThreadType()
	 * @method \Bitrix\Main\Type\DateTime getExpireAt()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread setExpireAt(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $expireAt)
	 * @method bool hasExpireAt()
	 * @method bool isExpireAtFilled()
	 * @method bool isExpireAtChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualExpireAt()
	 * @method \Bitrix\Main\Type\DateTime requireExpireAt()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread resetExpireAt()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread unsetExpireAt()
	 * @method \Bitrix\Main\Type\DateTime fillExpireAt()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_PostingThread wakeUp($data)
	 */
	class EO_PostingThread {
		/* @var \Bitrix\Sender\Internals\Model\PostingThreadTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\PostingThreadTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_PostingThread_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getThreadIdList()
	 * @method \int[] getPostingIdList()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 * @method \string[] getThreadTypeList()
	 * @method \string[] fillThreadType()
	 * @method \Bitrix\Main\Type\DateTime[] getExpireAtList()
	 * @method \Bitrix\Main\Type\DateTime[] fillExpireAt()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_PostingThread $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_PostingThread $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_PostingThread $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_PostingThread_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PostingThread_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\PostingThreadTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\PostingThreadTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PostingThread_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PostingThread_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread_Collection fetchCollection()
	 */
	class EO_PostingThread_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_PostingThread_Collection wakeUpCollection($rows)
	 */
	class EO_PostingThread_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\QueueTable:sender/lib/internals/model/queue.php:f89ee8d221c0e23c861176be4b3d481c */
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Queue
	 * @see \Bitrix\Sender\Internals\Model\QueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getEntityType()
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string getEntityId()
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string getLastItem()
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue setLastItem(\string|\Bitrix\Main\DB\SqlExpression $lastItem)
	 * @method bool hasLastItem()
	 * @method bool isLastItemFilled()
	 * @method bool isLastItemChanged()
	 * @method \string remindActualLastItem()
	 * @method \string requireLastItem()
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue resetLastItem()
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue unsetLastItem()
	 * @method \string fillLastItem()
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
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\EO_Queue wakeUp($data)
	 */
	class EO_Queue {
		/* @var \Bitrix\Sender\Internals\Model\QueueTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\QueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * EO_Queue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getEntityTypeList()
	 * @method \string[] getEntityIdList()
	 * @method \string[] getLastItemList()
	 * @method \string[] fillLastItem()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\EO_Queue $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\EO_Queue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\EO_Queue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\EO_Queue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Queue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\QueueTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\QueueTable';
	}
}
namespace Bitrix\Sender\Internals\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Queue_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Queue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue_Collection fetchCollection()
	 */
	class EO_Queue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\EO_Queue_Collection wakeUpCollection($rows)
	 */
	class EO_Queue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\Role\AccessTable:sender/lib/internals/model/role/access.php:5f1d4a36a2723d96b46d62e016242d1c */
namespace Bitrix\Sender\Internals\Model\Role {
	/**
	 * EO_Access
	 * @see \Bitrix\Sender\Internals\Model\Role\AccessTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getRoleId()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access setRoleId(\int|\Bitrix\Main\DB\SqlExpression $roleId)
	 * @method bool hasRoleId()
	 * @method bool isRoleIdFilled()
	 * @method bool isRoleIdChanged()
	 * @method \int remindActualRoleId()
	 * @method \int requireRoleId()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access resetRoleId()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access unsetRoleId()
	 * @method \int fillRoleId()
	 * @method \string getAccessCode()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access setAccessCode(\string|\Bitrix\Main\DB\SqlExpression $accessCode)
	 * @method bool hasAccessCode()
	 * @method bool isAccessCodeFilled()
	 * @method bool isAccessCodeChanged()
	 * @method \string remindActualAccessCode()
	 * @method \string requireAccessCode()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access resetAccessCode()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access unsetAccessCode()
	 * @method \string fillAccessCode()
	 * @method \Bitrix\Sender\Access\Role\EO_Role getRole()
	 * @method \Bitrix\Sender\Access\Role\EO_Role remindActualRole()
	 * @method \Bitrix\Sender\Access\Role\EO_Role requireRole()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access setRole(\Bitrix\Sender\Access\Role\EO_Role $object)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access resetRole()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access unsetRole()
	 * @method bool hasRole()
	 * @method bool isRoleFilled()
	 * @method bool isRoleChanged()
	 * @method \Bitrix\Sender\Access\Role\EO_Role fillRole()
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
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\Role\EO_Access wakeUp($data)
	 */
	class EO_Access {
		/* @var \Bitrix\Sender\Internals\Model\Role\AccessTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Role\AccessTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model\Role {
	/**
	 * EO_Access_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getRoleIdList()
	 * @method \int[] fillRoleId()
	 * @method \string[] getAccessCodeList()
	 * @method \string[] fillAccessCode()
	 * @method \Bitrix\Sender\Access\Role\EO_Role[] getRoleList()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access_Collection getRoleCollection()
	 * @method \Bitrix\Sender\Access\Role\EO_Role_Collection fillRole()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\Role\EO_Access $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\Role\EO_Access $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\Role\EO_Access $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\Role\EO_Access_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Access_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\Role\AccessTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Role\AccessTable';
	}
}
namespace Bitrix\Sender\Internals\Model\Role {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Access_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Access_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access_Collection fetchCollection()
	 */
	class EO_Access_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access_Collection wakeUpCollection($rows)
	 */
	class EO_Access_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\Internals\Model\Role\PermissionTable:sender/lib/internals/model/role/permission.php:a808a32a6f173331466da4ebf3c0da4d */
namespace Bitrix\Sender\Internals\Model\Role {
	/**
	 * EO_Permission
	 * @see \Bitrix\Sender\Internals\Model\Role\PermissionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getRoleId()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission setRoleId(\int|\Bitrix\Main\DB\SqlExpression $roleId)
	 * @method bool hasRoleId()
	 * @method bool isRoleIdFilled()
	 * @method bool isRoleIdChanged()
	 * @method \int remindActualRoleId()
	 * @method \int requireRoleId()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission resetRoleId()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission unsetRoleId()
	 * @method \int fillRoleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission resetEntity()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getAction()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission setAction(\string|\Bitrix\Main\DB\SqlExpression $action)
	 * @method bool hasAction()
	 * @method bool isActionFilled()
	 * @method bool isActionChanged()
	 * @method \string remindActualAction()
	 * @method \string requireAction()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission resetAction()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission unsetAction()
	 * @method \string fillAction()
	 * @method \string getPermission()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission setPermission(\string|\Bitrix\Main\DB\SqlExpression $permission)
	 * @method bool hasPermission()
	 * @method bool isPermissionFilled()
	 * @method bool isPermissionChanged()
	 * @method \string remindActualPermission()
	 * @method \string requirePermission()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission resetPermission()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission unsetPermission()
	 * @method \string fillPermission()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access getRoleAccess()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access remindActualRoleAccess()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access requireRoleAccess()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission setRoleAccess(\Bitrix\Sender\Internals\Model\Role\EO_Access $object)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission resetRoleAccess()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission unsetRoleAccess()
	 * @method bool hasRoleAccess()
	 * @method bool isRoleAccessFilled()
	 * @method bool isRoleAccessChanged()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access fillRoleAccess()
	 * @method \Bitrix\Sender\Access\Role\EO_Role getRole()
	 * @method \Bitrix\Sender\Access\Role\EO_Role remindActualRole()
	 * @method \Bitrix\Sender\Access\Role\EO_Role requireRole()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission setRole(\Bitrix\Sender\Access\Role\EO_Role $object)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission resetRole()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission unsetRole()
	 * @method bool hasRole()
	 * @method bool isRoleFilled()
	 * @method bool isRoleChanged()
	 * @method \Bitrix\Sender\Access\Role\EO_Role fillRole()
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
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission set($fieldName, $value)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission reset($fieldName)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\Internals\Model\Role\EO_Permission wakeUp($data)
	 */
	class EO_Permission {
		/* @var \Bitrix\Sender\Internals\Model\Role\PermissionTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Role\PermissionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender\Internals\Model\Role {
	/**
	 * EO_Permission_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getRoleIdList()
	 * @method \int[] fillRoleId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \string[] getActionList()
	 * @method \string[] fillAction()
	 * @method \string[] getPermissionList()
	 * @method \string[] fillPermission()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access[] getRoleAccessList()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission_Collection getRoleAccessCollection()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Access_Collection fillRoleAccess()
	 * @method \Bitrix\Sender\Access\Role\EO_Role[] getRoleList()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission_Collection getRoleCollection()
	 * @method \Bitrix\Sender\Access\Role\EO_Role_Collection fillRole()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\Internals\Model\Role\EO_Permission $object)
	 * @method bool has(\Bitrix\Sender\Internals\Model\Role\EO_Permission $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission getByPrimary($primary)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission[] getAll()
	 * @method bool remove(\Bitrix\Sender\Internals\Model\Role\EO_Permission $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\Internals\Model\Role\EO_Permission_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Permission_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\Internals\Model\Role\PermissionTable */
		static public $dataClass = '\Bitrix\Sender\Internals\Model\Role\PermissionTable';
	}
}
namespace Bitrix\Sender\Internals\Model\Role {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Permission_Result exec()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Permission_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission fetchObject()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission_Collection fetchCollection()
	 */
	class EO_Permission_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission_Collection createCollection()
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission wakeUpObject($row)
	 * @method \Bitrix\Sender\Internals\Model\Role\EO_Permission_Collection wakeUpCollection($rows)
	 */
	class EO_Permission_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\ListTable:sender/lib/list.php:dd64c7735fcc8ae71b8f121b23097ad4 */
namespace Bitrix\Sender {
	/**
	 * EO_List
	 * @see \Bitrix\Sender\ListTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_List setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Sender\EO_List setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Sender\EO_List resetCode()
	 * @method \Bitrix\Sender\EO_List unsetCode()
	 * @method \string fillCode()
	 * @method \string getName()
	 * @method \Bitrix\Sender\EO_List setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\EO_List resetName()
	 * @method \Bitrix\Sender\EO_List unsetName()
	 * @method \string fillName()
	 * @method \int getSort()
	 * @method \Bitrix\Sender\EO_List setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Sender\EO_List resetSort()
	 * @method \Bitrix\Sender\EO_List unsetSort()
	 * @method \int fillSort()
	 * @method \Bitrix\Sender\EO_ContactList getContactList()
	 * @method \Bitrix\Sender\EO_ContactList remindActualContactList()
	 * @method \Bitrix\Sender\EO_ContactList requireContactList()
	 * @method \Bitrix\Sender\EO_List setContactList(\Bitrix\Sender\EO_ContactList $object)
	 * @method \Bitrix\Sender\EO_List resetContactList()
	 * @method \Bitrix\Sender\EO_List unsetContactList()
	 * @method bool hasContactList()
	 * @method bool isContactListFilled()
	 * @method bool isContactListChanged()
	 * @method \Bitrix\Sender\EO_ContactList fillContactList()
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
	 * @method \Bitrix\Sender\EO_List set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_List reset($fieldName)
	 * @method \Bitrix\Sender\EO_List unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_List wakeUp($data)
	 */
	class EO_List {
		/* @var \Bitrix\Sender\ListTable */
		static public $dataClass = '\Bitrix\Sender\ListTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_List_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \Bitrix\Sender\EO_ContactList[] getContactListList()
	 * @method \Bitrix\Sender\EO_List_Collection getContactListCollection()
	 * @method \Bitrix\Sender\EO_ContactList_Collection fillContactList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_List $object)
	 * @method bool has(\Bitrix\Sender\EO_List $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_List getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_List[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_List $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_List_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_List current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_List_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\ListTable */
		static public $dataClass = '\Bitrix\Sender\ListTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_List_Result exec()
	 * @method \Bitrix\Sender\EO_List fetchObject()
	 * @method \Bitrix\Sender\EO_List_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_List_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_List fetchObject()
	 * @method \Bitrix\Sender\EO_List_Collection fetchCollection()
	 */
	class EO_List_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_List createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_List_Collection createCollection()
	 * @method \Bitrix\Sender\EO_List wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_List_Collection wakeUpCollection($rows)
	 */
	class EO_List_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\MailingTable:sender/lib/mailing.php:106caf06fe8d67f0a771fe8c37f33e34 */
namespace Bitrix\Sender {
	/**
	 * EO_Mailing
	 * @see \Bitrix\Sender\MailingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_Mailing setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Sender\EO_Mailing setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\EO_Mailing resetName()
	 * @method \Bitrix\Sender\EO_Mailing unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Sender\EO_Mailing setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Sender\EO_Mailing resetDescription()
	 * @method \Bitrix\Sender\EO_Mailing unsetDescription()
	 * @method \string fillDescription()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_Mailing setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_Mailing resetDateInsert()
	 * @method \Bitrix\Sender\EO_Mailing unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getActive()
	 * @method \Bitrix\Sender\EO_Mailing setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Sender\EO_Mailing resetActive()
	 * @method \Bitrix\Sender\EO_Mailing unsetActive()
	 * @method \string fillActive()
	 * @method \string getTrackClick()
	 * @method \Bitrix\Sender\EO_Mailing setTrackClick(\string|\Bitrix\Main\DB\SqlExpression $trackClick)
	 * @method bool hasTrackClick()
	 * @method bool isTrackClickFilled()
	 * @method bool isTrackClickChanged()
	 * @method \string remindActualTrackClick()
	 * @method \string requireTrackClick()
	 * @method \Bitrix\Sender\EO_Mailing resetTrackClick()
	 * @method \Bitrix\Sender\EO_Mailing unsetTrackClick()
	 * @method \string fillTrackClick()
	 * @method \string getIsPublic()
	 * @method \Bitrix\Sender\EO_Mailing setIsPublic(\string|\Bitrix\Main\DB\SqlExpression $isPublic)
	 * @method bool hasIsPublic()
	 * @method bool isIsPublicFilled()
	 * @method bool isIsPublicChanged()
	 * @method \string remindActualIsPublic()
	 * @method \string requireIsPublic()
	 * @method \Bitrix\Sender\EO_Mailing resetIsPublic()
	 * @method \Bitrix\Sender\EO_Mailing unsetIsPublic()
	 * @method \string fillIsPublic()
	 * @method \string getIsTrigger()
	 * @method \Bitrix\Sender\EO_Mailing setIsTrigger(\string|\Bitrix\Main\DB\SqlExpression $isTrigger)
	 * @method bool hasIsTrigger()
	 * @method bool isIsTriggerFilled()
	 * @method bool isIsTriggerChanged()
	 * @method \string remindActualIsTrigger()
	 * @method \string requireIsTrigger()
	 * @method \Bitrix\Sender\EO_Mailing resetIsTrigger()
	 * @method \Bitrix\Sender\EO_Mailing unsetIsTrigger()
	 * @method \string fillIsTrigger()
	 * @method \int getSort()
	 * @method \Bitrix\Sender\EO_Mailing setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Sender\EO_Mailing resetSort()
	 * @method \Bitrix\Sender\EO_Mailing unsetSort()
	 * @method \int fillSort()
	 * @method \string getSiteId()
	 * @method \Bitrix\Sender\EO_Mailing setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Sender\EO_Mailing resetSiteId()
	 * @method \Bitrix\Sender\EO_Mailing unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \string getTriggerFields()
	 * @method \Bitrix\Sender\EO_Mailing setTriggerFields(\string|\Bitrix\Main\DB\SqlExpression $triggerFields)
	 * @method bool hasTriggerFields()
	 * @method bool isTriggerFieldsFilled()
	 * @method bool isTriggerFieldsChanged()
	 * @method \string remindActualTriggerFields()
	 * @method \string requireTriggerFields()
	 * @method \Bitrix\Sender\EO_Mailing resetTriggerFields()
	 * @method \Bitrix\Sender\EO_Mailing unsetTriggerFields()
	 * @method \string fillTriggerFields()
	 * @method \string getEmailFrom()
	 * @method \Bitrix\Sender\EO_Mailing setEmailFrom(\string|\Bitrix\Main\DB\SqlExpression $emailFrom)
	 * @method bool hasEmailFrom()
	 * @method bool isEmailFromFilled()
	 * @method bool isEmailFromChanged()
	 * @method \string remindActualEmailFrom()
	 * @method \string requireEmailFrom()
	 * @method \Bitrix\Sender\EO_Mailing resetEmailFrom()
	 * @method \Bitrix\Sender\EO_Mailing unsetEmailFrom()
	 * @method \string fillEmailFrom()
	 * @method \Bitrix\Sender\EO_MailingChain getChain()
	 * @method \Bitrix\Sender\EO_MailingChain remindActualChain()
	 * @method \Bitrix\Sender\EO_MailingChain requireChain()
	 * @method \Bitrix\Sender\EO_Mailing setChain(\Bitrix\Sender\EO_MailingChain $object)
	 * @method \Bitrix\Sender\EO_Mailing resetChain()
	 * @method \Bitrix\Sender\EO_Mailing unsetChain()
	 * @method bool hasChain()
	 * @method bool isChainFilled()
	 * @method bool isChainChanged()
	 * @method \Bitrix\Sender\EO_MailingChain fillChain()
	 * @method \Bitrix\Sender\EO_Posting getPosting()
	 * @method \Bitrix\Sender\EO_Posting remindActualPosting()
	 * @method \Bitrix\Sender\EO_Posting requirePosting()
	 * @method \Bitrix\Sender\EO_Mailing setPosting(\Bitrix\Sender\EO_Posting $object)
	 * @method \Bitrix\Sender\EO_Mailing resetPosting()
	 * @method \Bitrix\Sender\EO_Mailing unsetPosting()
	 * @method bool hasPosting()
	 * @method bool isPostingFilled()
	 * @method bool isPostingChanged()
	 * @method \Bitrix\Sender\EO_Posting fillPosting()
	 * @method \Bitrix\Sender\EO_MailingGroup getMailingGroup()
	 * @method \Bitrix\Sender\EO_MailingGroup remindActualMailingGroup()
	 * @method \Bitrix\Sender\EO_MailingGroup requireMailingGroup()
	 * @method \Bitrix\Sender\EO_Mailing setMailingGroup(\Bitrix\Sender\EO_MailingGroup $object)
	 * @method \Bitrix\Sender\EO_Mailing resetMailingGroup()
	 * @method \Bitrix\Sender\EO_Mailing unsetMailingGroup()
	 * @method bool hasMailingGroup()
	 * @method bool isMailingGroupFilled()
	 * @method bool isMailingGroupChanged()
	 * @method \Bitrix\Sender\EO_MailingGroup fillMailingGroup()
	 * @method \Bitrix\Sender\EO_MailingSubscription getMailingSubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription remindActualMailingSubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription requireMailingSubscription()
	 * @method \Bitrix\Sender\EO_Mailing setMailingSubscription(\Bitrix\Sender\EO_MailingSubscription $object)
	 * @method \Bitrix\Sender\EO_Mailing resetMailingSubscription()
	 * @method \Bitrix\Sender\EO_Mailing unsetMailingSubscription()
	 * @method bool hasMailingSubscription()
	 * @method bool isMailingSubscriptionFilled()
	 * @method bool isMailingSubscriptionChanged()
	 * @method \Bitrix\Sender\EO_MailingSubscription fillMailingSubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription getSubscriber()
	 * @method \Bitrix\Sender\EO_MailingSubscription remindActualSubscriber()
	 * @method \Bitrix\Sender\EO_MailingSubscription requireSubscriber()
	 * @method \Bitrix\Sender\EO_Mailing setSubscriber(\Bitrix\Sender\EO_MailingSubscription $object)
	 * @method \Bitrix\Sender\EO_Mailing resetSubscriber()
	 * @method \Bitrix\Sender\EO_Mailing unsetSubscriber()
	 * @method bool hasSubscriber()
	 * @method bool isSubscriberFilled()
	 * @method bool isSubscriberChanged()
	 * @method \Bitrix\Sender\EO_MailingSubscription fillSubscriber()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Sender\EO_Mailing setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Sender\EO_Mailing resetSite()
	 * @method \Bitrix\Sender\EO_Mailing unsetSite()
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
	 * @method \Bitrix\Sender\EO_Mailing set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_Mailing reset($fieldName)
	 * @method \Bitrix\Sender\EO_Mailing unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_Mailing wakeUp($data)
	 */
	class EO_Mailing {
		/* @var \Bitrix\Sender\MailingTable */
		static public $dataClass = '\Bitrix\Sender\MailingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_Mailing_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getTrackClickList()
	 * @method \string[] fillTrackClick()
	 * @method \string[] getIsPublicList()
	 * @method \string[] fillIsPublic()
	 * @method \string[] getIsTriggerList()
	 * @method \string[] fillIsTrigger()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \string[] getTriggerFieldsList()
	 * @method \string[] fillTriggerFields()
	 * @method \string[] getEmailFromList()
	 * @method \string[] fillEmailFrom()
	 * @method \Bitrix\Sender\EO_MailingChain[] getChainList()
	 * @method \Bitrix\Sender\EO_Mailing_Collection getChainCollection()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection fillChain()
	 * @method \Bitrix\Sender\EO_Posting[] getPostingList()
	 * @method \Bitrix\Sender\EO_Mailing_Collection getPostingCollection()
	 * @method \Bitrix\Sender\EO_Posting_Collection fillPosting()
	 * @method \Bitrix\Sender\EO_MailingGroup[] getMailingGroupList()
	 * @method \Bitrix\Sender\EO_Mailing_Collection getMailingGroupCollection()
	 * @method \Bitrix\Sender\EO_MailingGroup_Collection fillMailingGroup()
	 * @method \Bitrix\Sender\EO_MailingSubscription[] getMailingSubscriptionList()
	 * @method \Bitrix\Sender\EO_Mailing_Collection getMailingSubscriptionCollection()
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection fillMailingSubscription()
	 * @method \Bitrix\Sender\EO_MailingSubscription[] getSubscriberList()
	 * @method \Bitrix\Sender\EO_Mailing_Collection getSubscriberCollection()
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection fillSubscriber()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Sender\EO_Mailing_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_Mailing $object)
	 * @method bool has(\Bitrix\Sender\EO_Mailing $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Mailing getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Mailing[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_Mailing $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_Mailing_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_Mailing current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Mailing_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\MailingTable */
		static public $dataClass = '\Bitrix\Sender\MailingTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Mailing_Result exec()
	 * @method \Bitrix\Sender\EO_Mailing fetchObject()
	 * @method \Bitrix\Sender\EO_Mailing_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Mailing_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_Mailing fetchObject()
	 * @method \Bitrix\Sender\EO_Mailing_Collection fetchCollection()
	 */
	class EO_Mailing_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_Mailing createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_Mailing_Collection createCollection()
	 * @method \Bitrix\Sender\EO_Mailing wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_Mailing_Collection wakeUpCollection($rows)
	 */
	class EO_Mailing_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\MailingGroupTable:sender/lib/mailing.php:106caf06fe8d67f0a771fe8c37f33e34 */
namespace Bitrix\Sender {
	/**
	 * EO_MailingGroup
	 * @see \Bitrix\Sender\MailingGroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getMailingId()
	 * @method \Bitrix\Sender\EO_MailingGroup setMailingId(\int|\Bitrix\Main\DB\SqlExpression $mailingId)
	 * @method bool hasMailingId()
	 * @method bool isMailingIdFilled()
	 * @method bool isMailingIdChanged()
	 * @method \int getGroupId()
	 * @method \Bitrix\Sender\EO_MailingGroup setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \boolean getInclude()
	 * @method \Bitrix\Sender\EO_MailingGroup setInclude(\boolean|\Bitrix\Main\DB\SqlExpression $include)
	 * @method bool hasInclude()
	 * @method bool isIncludeFilled()
	 * @method bool isIncludeChanged()
	 * @method \boolean remindActualInclude()
	 * @method \boolean requireInclude()
	 * @method \Bitrix\Sender\EO_MailingGroup resetInclude()
	 * @method \Bitrix\Sender\EO_MailingGroup unsetInclude()
	 * @method \boolean fillInclude()
	 * @method \Bitrix\Sender\EO_Mailing getMailing()
	 * @method \Bitrix\Sender\EO_Mailing remindActualMailing()
	 * @method \Bitrix\Sender\EO_Mailing requireMailing()
	 * @method \Bitrix\Sender\EO_MailingGroup setMailing(\Bitrix\Sender\EO_Mailing $object)
	 * @method \Bitrix\Sender\EO_MailingGroup resetMailing()
	 * @method \Bitrix\Sender\EO_MailingGroup unsetMailing()
	 * @method bool hasMailing()
	 * @method bool isMailingFilled()
	 * @method bool isMailingChanged()
	 * @method \Bitrix\Sender\EO_Mailing fillMailing()
	 * @method \Bitrix\Sender\EO_Group getGroup()
	 * @method \Bitrix\Sender\EO_Group remindActualGroup()
	 * @method \Bitrix\Sender\EO_Group requireGroup()
	 * @method \Bitrix\Sender\EO_MailingGroup setGroup(\Bitrix\Sender\EO_Group $object)
	 * @method \Bitrix\Sender\EO_MailingGroup resetGroup()
	 * @method \Bitrix\Sender\EO_MailingGroup unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Sender\EO_Group fillGroup()
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
	 * @method \Bitrix\Sender\EO_MailingGroup set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_MailingGroup reset($fieldName)
	 * @method \Bitrix\Sender\EO_MailingGroup unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_MailingGroup wakeUp($data)
	 */
	class EO_MailingGroup {
		/* @var \Bitrix\Sender\MailingGroupTable */
		static public $dataClass = '\Bitrix\Sender\MailingGroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_MailingGroup_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getMailingIdList()
	 * @method \int[] getGroupIdList()
	 * @method \boolean[] getIncludeList()
	 * @method \boolean[] fillInclude()
	 * @method \Bitrix\Sender\EO_Mailing[] getMailingList()
	 * @method \Bitrix\Sender\EO_MailingGroup_Collection getMailingCollection()
	 * @method \Bitrix\Sender\EO_Mailing_Collection fillMailing()
	 * @method \Bitrix\Sender\EO_Group[] getGroupList()
	 * @method \Bitrix\Sender\EO_MailingGroup_Collection getGroupCollection()
	 * @method \Bitrix\Sender\EO_Group_Collection fillGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_MailingGroup $object)
	 * @method bool has(\Bitrix\Sender\EO_MailingGroup $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingGroup getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingGroup[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_MailingGroup $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_MailingGroup_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_MailingGroup current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_MailingGroup_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\MailingGroupTable */
		static public $dataClass = '\Bitrix\Sender\MailingGroupTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailingGroup_Result exec()
	 * @method \Bitrix\Sender\EO_MailingGroup fetchObject()
	 * @method \Bitrix\Sender\EO_MailingGroup_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailingGroup_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_MailingGroup fetchObject()
	 * @method \Bitrix\Sender\EO_MailingGroup_Collection fetchCollection()
	 */
	class EO_MailingGroup_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_MailingGroup createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_MailingGroup_Collection createCollection()
	 * @method \Bitrix\Sender\EO_MailingGroup wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_MailingGroup_Collection wakeUpCollection($rows)
	 */
	class EO_MailingGroup_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\MailingSubscriptionTable:sender/lib/mailing.php:106caf06fe8d67f0a771fe8c37f33e34 */
namespace Bitrix\Sender {
	/**
	 * EO_MailingSubscription
	 * @see \Bitrix\Sender\MailingSubscriptionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getMailingId()
	 * @method \Bitrix\Sender\EO_MailingSubscription setMailingId(\int|\Bitrix\Main\DB\SqlExpression $mailingId)
	 * @method bool hasMailingId()
	 * @method bool isMailingIdFilled()
	 * @method bool isMailingIdChanged()
	 * @method \int getContactId()
	 * @method \Bitrix\Sender\EO_MailingSubscription setContactId(\int|\Bitrix\Main\DB\SqlExpression $contactId)
	 * @method bool hasContactId()
	 * @method bool isContactIdFilled()
	 * @method bool isContactIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_MailingSubscription setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_MailingSubscription resetDateInsert()
	 * @method \Bitrix\Sender\EO_MailingSubscription unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getIsUnsub()
	 * @method \Bitrix\Sender\EO_MailingSubscription setIsUnsub(\string|\Bitrix\Main\DB\SqlExpression $isUnsub)
	 * @method bool hasIsUnsub()
	 * @method bool isIsUnsubFilled()
	 * @method bool isIsUnsubChanged()
	 * @method \string remindActualIsUnsub()
	 * @method \string requireIsUnsub()
	 * @method \Bitrix\Sender\EO_MailingSubscription resetIsUnsub()
	 * @method \Bitrix\Sender\EO_MailingSubscription unsetIsUnsub()
	 * @method \string fillIsUnsub()
	 * @method \Bitrix\Sender\EO_Mailing getMailing()
	 * @method \Bitrix\Sender\EO_Mailing remindActualMailing()
	 * @method \Bitrix\Sender\EO_Mailing requireMailing()
	 * @method \Bitrix\Sender\EO_MailingSubscription setMailing(\Bitrix\Sender\EO_Mailing $object)
	 * @method \Bitrix\Sender\EO_MailingSubscription resetMailing()
	 * @method \Bitrix\Sender\EO_MailingSubscription unsetMailing()
	 * @method bool hasMailing()
	 * @method bool isMailingFilled()
	 * @method bool isMailingChanged()
	 * @method \Bitrix\Sender\EO_Mailing fillMailing()
	 * @method \Bitrix\Sender\EO_Contact getContact()
	 * @method \Bitrix\Sender\EO_Contact remindActualContact()
	 * @method \Bitrix\Sender\EO_Contact requireContact()
	 * @method \Bitrix\Sender\EO_MailingSubscription setContact(\Bitrix\Sender\EO_Contact $object)
	 * @method \Bitrix\Sender\EO_MailingSubscription resetContact()
	 * @method \Bitrix\Sender\EO_MailingSubscription unsetContact()
	 * @method bool hasContact()
	 * @method bool isContactFilled()
	 * @method bool isContactChanged()
	 * @method \Bitrix\Sender\EO_Contact fillContact()
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
	 * @method \Bitrix\Sender\EO_MailingSubscription set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_MailingSubscription reset($fieldName)
	 * @method \Bitrix\Sender\EO_MailingSubscription unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_MailingSubscription wakeUp($data)
	 */
	class EO_MailingSubscription {
		/* @var \Bitrix\Sender\MailingSubscriptionTable */
		static public $dataClass = '\Bitrix\Sender\MailingSubscriptionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_MailingSubscription_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getMailingIdList()
	 * @method \int[] getContactIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getIsUnsubList()
	 * @method \string[] fillIsUnsub()
	 * @method \Bitrix\Sender\EO_Mailing[] getMailingList()
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection getMailingCollection()
	 * @method \Bitrix\Sender\EO_Mailing_Collection fillMailing()
	 * @method \Bitrix\Sender\EO_Contact[] getContactList()
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection getContactCollection()
	 * @method \Bitrix\Sender\EO_Contact_Collection fillContact()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_MailingSubscription $object)
	 * @method bool has(\Bitrix\Sender\EO_MailingSubscription $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingSubscription getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingSubscription[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_MailingSubscription $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_MailingSubscription_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_MailingSubscription current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_MailingSubscription_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\MailingSubscriptionTable */
		static public $dataClass = '\Bitrix\Sender\MailingSubscriptionTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailingSubscription_Result exec()
	 * @method \Bitrix\Sender\EO_MailingSubscription fetchObject()
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailingSubscription_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_MailingSubscription fetchObject()
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection fetchCollection()
	 */
	class EO_MailingSubscription_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_MailingSubscription createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection createCollection()
	 * @method \Bitrix\Sender\EO_MailingSubscription wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_MailingSubscription_Collection wakeUpCollection($rows)
	 */
	class EO_MailingSubscription_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\MailingChainTable:sender/lib/mailingchain.php:032ebed36d36e539c06b2d69900272e8 */
namespace Bitrix\Sender {
	/**
	 * EO_MailingChain
	 * @see \Bitrix\Sender\MailingChainTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_MailingChain setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMailingId()
	 * @method \Bitrix\Sender\EO_MailingChain setMailingId(\int|\Bitrix\Main\DB\SqlExpression $mailingId)
	 * @method bool hasMailingId()
	 * @method bool isMailingIdFilled()
	 * @method bool isMailingIdChanged()
	 * @method \string getMessageCode()
	 * @method \Bitrix\Sender\EO_MailingChain setMessageCode(\string|\Bitrix\Main\DB\SqlExpression $messageCode)
	 * @method bool hasMessageCode()
	 * @method bool isMessageCodeFilled()
	 * @method bool isMessageCodeChanged()
	 * @method \string remindActualMessageCode()
	 * @method \string requireMessageCode()
	 * @method \Bitrix\Sender\EO_MailingChain resetMessageCode()
	 * @method \Bitrix\Sender\EO_MailingChain unsetMessageCode()
	 * @method \string fillMessageCode()
	 * @method \int getMessageId()
	 * @method \Bitrix\Sender\EO_MailingChain setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int remindActualMessageId()
	 * @method \int requireMessageId()
	 * @method \Bitrix\Sender\EO_MailingChain resetMessageId()
	 * @method \Bitrix\Sender\EO_MailingChain unsetMessageId()
	 * @method \int fillMessageId()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\EO_MailingChain setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int remindActualPostingId()
	 * @method \int requirePostingId()
	 * @method \Bitrix\Sender\EO_MailingChain resetPostingId()
	 * @method \Bitrix\Sender\EO_MailingChain unsetPostingId()
	 * @method \int fillPostingId()
	 * @method \int getParentId()
	 * @method \Bitrix\Sender\EO_MailingChain setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Sender\EO_MailingChain resetParentId()
	 * @method \Bitrix\Sender\EO_MailingChain unsetParentId()
	 * @method \int fillParentId()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Sender\EO_MailingChain setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Sender\EO_MailingChain resetCreatedBy()
	 * @method \Bitrix\Sender\EO_MailingChain unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_MailingChain setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_MailingChain resetDateInsert()
	 * @method \Bitrix\Sender\EO_MailingChain unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\EO_MailingChain setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Sender\EO_MailingChain resetStatus()
	 * @method \Bitrix\Sender\EO_MailingChain unsetStatus()
	 * @method \string fillStatus()
	 * @method \string getReiterate()
	 * @method \Bitrix\Sender\EO_MailingChain setReiterate(\string|\Bitrix\Main\DB\SqlExpression $reiterate)
	 * @method bool hasReiterate()
	 * @method bool isReiterateFilled()
	 * @method bool isReiterateChanged()
	 * @method \string remindActualReiterate()
	 * @method \string requireReiterate()
	 * @method \Bitrix\Sender\EO_MailingChain resetReiterate()
	 * @method \Bitrix\Sender\EO_MailingChain unsetReiterate()
	 * @method \string fillReiterate()
	 * @method \Bitrix\Main\Type\DateTime getLastExecuted()
	 * @method \Bitrix\Sender\EO_MailingChain setLastExecuted(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastExecuted)
	 * @method bool hasLastExecuted()
	 * @method bool isLastExecutedFilled()
	 * @method bool isLastExecutedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastExecuted()
	 * @method \Bitrix\Main\Type\DateTime requireLastExecuted()
	 * @method \Bitrix\Sender\EO_MailingChain resetLastExecuted()
	 * @method \Bitrix\Sender\EO_MailingChain unsetLastExecuted()
	 * @method \Bitrix\Main\Type\DateTime fillLastExecuted()
	 * @method \string getTitle()
	 * @method \Bitrix\Sender\EO_MailingChain setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Sender\EO_MailingChain resetTitle()
	 * @method \Bitrix\Sender\EO_MailingChain unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getEmailFrom()
	 * @method \Bitrix\Sender\EO_MailingChain setEmailFrom(\string|\Bitrix\Main\DB\SqlExpression $emailFrom)
	 * @method bool hasEmailFrom()
	 * @method bool isEmailFromFilled()
	 * @method bool isEmailFromChanged()
	 * @method \string remindActualEmailFrom()
	 * @method \string requireEmailFrom()
	 * @method \Bitrix\Sender\EO_MailingChain resetEmailFrom()
	 * @method \Bitrix\Sender\EO_MailingChain unsetEmailFrom()
	 * @method \string fillEmailFrom()
	 * @method \string getSubject()
	 * @method \Bitrix\Sender\EO_MailingChain setSubject(\string|\Bitrix\Main\DB\SqlExpression $subject)
	 * @method bool hasSubject()
	 * @method bool isSubjectFilled()
	 * @method bool isSubjectChanged()
	 * @method \string remindActualSubject()
	 * @method \string requireSubject()
	 * @method \Bitrix\Sender\EO_MailingChain resetSubject()
	 * @method \Bitrix\Sender\EO_MailingChain unsetSubject()
	 * @method \string fillSubject()
	 * @method \string getMessage()
	 * @method \Bitrix\Sender\EO_MailingChain setMessage(\string|\Bitrix\Main\DB\SqlExpression $message)
	 * @method bool hasMessage()
	 * @method bool isMessageFilled()
	 * @method bool isMessageChanged()
	 * @method \string remindActualMessage()
	 * @method \string requireMessage()
	 * @method \Bitrix\Sender\EO_MailingChain resetMessage()
	 * @method \Bitrix\Sender\EO_MailingChain unsetMessage()
	 * @method \string fillMessage()
	 * @method \string getTemplateType()
	 * @method \Bitrix\Sender\EO_MailingChain setTemplateType(\string|\Bitrix\Main\DB\SqlExpression $templateType)
	 * @method bool hasTemplateType()
	 * @method bool isTemplateTypeFilled()
	 * @method bool isTemplateTypeChanged()
	 * @method \string remindActualTemplateType()
	 * @method \string requireTemplateType()
	 * @method \Bitrix\Sender\EO_MailingChain resetTemplateType()
	 * @method \Bitrix\Sender\EO_MailingChain unsetTemplateType()
	 * @method \string fillTemplateType()
	 * @method \string getTemplateId()
	 * @method \Bitrix\Sender\EO_MailingChain setTemplateId(\string|\Bitrix\Main\DB\SqlExpression $templateId)
	 * @method bool hasTemplateId()
	 * @method bool isTemplateIdFilled()
	 * @method bool isTemplateIdChanged()
	 * @method \string remindActualTemplateId()
	 * @method \string requireTemplateId()
	 * @method \Bitrix\Sender\EO_MailingChain resetTemplateId()
	 * @method \Bitrix\Sender\EO_MailingChain unsetTemplateId()
	 * @method \string fillTemplateId()
	 * @method \string getIsTrigger()
	 * @method \Bitrix\Sender\EO_MailingChain setIsTrigger(\string|\Bitrix\Main\DB\SqlExpression $isTrigger)
	 * @method bool hasIsTrigger()
	 * @method bool isIsTriggerFilled()
	 * @method bool isIsTriggerChanged()
	 * @method \string remindActualIsTrigger()
	 * @method \string requireIsTrigger()
	 * @method \Bitrix\Sender\EO_MailingChain resetIsTrigger()
	 * @method \Bitrix\Sender\EO_MailingChain unsetIsTrigger()
	 * @method \string fillIsTrigger()
	 * @method \int getTimeShift()
	 * @method \Bitrix\Sender\EO_MailingChain setTimeShift(\int|\Bitrix\Main\DB\SqlExpression $timeShift)
	 * @method bool hasTimeShift()
	 * @method bool isTimeShiftFilled()
	 * @method bool isTimeShiftChanged()
	 * @method \int remindActualTimeShift()
	 * @method \int requireTimeShift()
	 * @method \Bitrix\Sender\EO_MailingChain resetTimeShift()
	 * @method \Bitrix\Sender\EO_MailingChain unsetTimeShift()
	 * @method \int fillTimeShift()
	 * @method \Bitrix\Main\Type\DateTime getAutoSendTime()
	 * @method \Bitrix\Sender\EO_MailingChain setAutoSendTime(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $autoSendTime)
	 * @method bool hasAutoSendTime()
	 * @method bool isAutoSendTimeFilled()
	 * @method bool isAutoSendTimeChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualAutoSendTime()
	 * @method \Bitrix\Main\Type\DateTime requireAutoSendTime()
	 * @method \Bitrix\Sender\EO_MailingChain resetAutoSendTime()
	 * @method \Bitrix\Sender\EO_MailingChain unsetAutoSendTime()
	 * @method \Bitrix\Main\Type\DateTime fillAutoSendTime()
	 * @method \string getMonthsOfYear()
	 * @method \Bitrix\Sender\EO_MailingChain setMonthsOfYear(\string|\Bitrix\Main\DB\SqlExpression $monthsOfYear)
	 * @method bool hasMonthsOfYear()
	 * @method bool isMonthsOfYearFilled()
	 * @method bool isMonthsOfYearChanged()
	 * @method \string remindActualMonthsOfYear()
	 * @method \string requireMonthsOfYear()
	 * @method \Bitrix\Sender\EO_MailingChain resetMonthsOfYear()
	 * @method \Bitrix\Sender\EO_MailingChain unsetMonthsOfYear()
	 * @method \string fillMonthsOfYear()
	 * @method \string getDaysOfMonth()
	 * @method \Bitrix\Sender\EO_MailingChain setDaysOfMonth(\string|\Bitrix\Main\DB\SqlExpression $daysOfMonth)
	 * @method bool hasDaysOfMonth()
	 * @method bool isDaysOfMonthFilled()
	 * @method bool isDaysOfMonthChanged()
	 * @method \string remindActualDaysOfMonth()
	 * @method \string requireDaysOfMonth()
	 * @method \Bitrix\Sender\EO_MailingChain resetDaysOfMonth()
	 * @method \Bitrix\Sender\EO_MailingChain unsetDaysOfMonth()
	 * @method \string fillDaysOfMonth()
	 * @method \string getDaysOfWeek()
	 * @method \Bitrix\Sender\EO_MailingChain setDaysOfWeek(\string|\Bitrix\Main\DB\SqlExpression $daysOfWeek)
	 * @method bool hasDaysOfWeek()
	 * @method bool isDaysOfWeekFilled()
	 * @method bool isDaysOfWeekChanged()
	 * @method \string remindActualDaysOfWeek()
	 * @method \string requireDaysOfWeek()
	 * @method \Bitrix\Sender\EO_MailingChain resetDaysOfWeek()
	 * @method \Bitrix\Sender\EO_MailingChain unsetDaysOfWeek()
	 * @method \string fillDaysOfWeek()
	 * @method \string getTimesOfDay()
	 * @method \Bitrix\Sender\EO_MailingChain setTimesOfDay(\string|\Bitrix\Main\DB\SqlExpression $timesOfDay)
	 * @method bool hasTimesOfDay()
	 * @method bool isTimesOfDayFilled()
	 * @method bool isTimesOfDayChanged()
	 * @method \string remindActualTimesOfDay()
	 * @method \string requireTimesOfDay()
	 * @method \Bitrix\Sender\EO_MailingChain resetTimesOfDay()
	 * @method \Bitrix\Sender\EO_MailingChain unsetTimesOfDay()
	 * @method \string fillTimesOfDay()
	 * @method \string getPriority()
	 * @method \Bitrix\Sender\EO_MailingChain setPriority(\string|\Bitrix\Main\DB\SqlExpression $priority)
	 * @method bool hasPriority()
	 * @method bool isPriorityFilled()
	 * @method bool isPriorityChanged()
	 * @method \string remindActualPriority()
	 * @method \string requirePriority()
	 * @method \Bitrix\Sender\EO_MailingChain resetPriority()
	 * @method \Bitrix\Sender\EO_MailingChain unsetPriority()
	 * @method \string fillPriority()
	 * @method \string getLinkParams()
	 * @method \Bitrix\Sender\EO_MailingChain setLinkParams(\string|\Bitrix\Main\DB\SqlExpression $linkParams)
	 * @method bool hasLinkParams()
	 * @method bool isLinkParamsFilled()
	 * @method bool isLinkParamsChanged()
	 * @method \string remindActualLinkParams()
	 * @method \string requireLinkParams()
	 * @method \Bitrix\Sender\EO_MailingChain resetLinkParams()
	 * @method \Bitrix\Sender\EO_MailingChain unsetLinkParams()
	 * @method \string fillLinkParams()
	 * @method \Bitrix\Sender\EO_Mailing getMailing()
	 * @method \Bitrix\Sender\EO_Mailing remindActualMailing()
	 * @method \Bitrix\Sender\EO_Mailing requireMailing()
	 * @method \Bitrix\Sender\EO_MailingChain setMailing(\Bitrix\Sender\EO_Mailing $object)
	 * @method \Bitrix\Sender\EO_MailingChain resetMailing()
	 * @method \Bitrix\Sender\EO_MailingChain unsetMailing()
	 * @method bool hasMailing()
	 * @method bool isMailingFilled()
	 * @method bool isMailingChanged()
	 * @method \Bitrix\Sender\EO_Mailing fillMailing()
	 * @method \Bitrix\Sender\EO_Posting getCurrentPosting()
	 * @method \Bitrix\Sender\EO_Posting remindActualCurrentPosting()
	 * @method \Bitrix\Sender\EO_Posting requireCurrentPosting()
	 * @method \Bitrix\Sender\EO_MailingChain setCurrentPosting(\Bitrix\Sender\EO_Posting $object)
	 * @method \Bitrix\Sender\EO_MailingChain resetCurrentPosting()
	 * @method \Bitrix\Sender\EO_MailingChain unsetCurrentPosting()
	 * @method bool hasCurrentPosting()
	 * @method bool isCurrentPostingFilled()
	 * @method bool isCurrentPostingChanged()
	 * @method \Bitrix\Sender\EO_Posting fillCurrentPosting()
	 * @method \Bitrix\Sender\EO_Posting getPosting()
	 * @method \Bitrix\Sender\EO_Posting remindActualPosting()
	 * @method \Bitrix\Sender\EO_Posting requirePosting()
	 * @method \Bitrix\Sender\EO_MailingChain setPosting(\Bitrix\Sender\EO_Posting $object)
	 * @method \Bitrix\Sender\EO_MailingChain resetPosting()
	 * @method \Bitrix\Sender\EO_MailingChain unsetPosting()
	 * @method bool hasPosting()
	 * @method bool isPostingFilled()
	 * @method bool isPostingChanged()
	 * @method \Bitrix\Sender\EO_Posting fillPosting()
	 * @method \Bitrix\Sender\EO_MailingAttachment getAttachment()
	 * @method \Bitrix\Sender\EO_MailingAttachment remindActualAttachment()
	 * @method \Bitrix\Sender\EO_MailingAttachment requireAttachment()
	 * @method \Bitrix\Sender\EO_MailingChain setAttachment(\Bitrix\Sender\EO_MailingAttachment $object)
	 * @method \Bitrix\Sender\EO_MailingChain resetAttachment()
	 * @method \Bitrix\Sender\EO_MailingChain unsetAttachment()
	 * @method bool hasAttachment()
	 * @method bool isAttachmentFilled()
	 * @method bool isAttachmentChanged()
	 * @method \Bitrix\Sender\EO_MailingAttachment fillAttachment()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Sender\EO_MailingChain setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Sender\EO_MailingChain resetCreatedByUser()
	 * @method \Bitrix\Sender\EO_MailingChain unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \boolean getWaitingRecipient()
	 * @method \Bitrix\Sender\EO_MailingChain setWaitingRecipient(\boolean|\Bitrix\Main\DB\SqlExpression $waitingRecipient)
	 * @method bool hasWaitingRecipient()
	 * @method bool isWaitingRecipientFilled()
	 * @method bool isWaitingRecipientChanged()
	 * @method \boolean remindActualWaitingRecipient()
	 * @method \boolean requireWaitingRecipient()
	 * @method \Bitrix\Sender\EO_MailingChain resetWaitingRecipient()
	 * @method \Bitrix\Sender\EO_MailingChain unsetWaitingRecipient()
	 * @method \boolean fillWaitingRecipient()
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
	 * @method \Bitrix\Sender\EO_MailingChain set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_MailingChain reset($fieldName)
	 * @method \Bitrix\Sender\EO_MailingChain unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_MailingChain wakeUp($data)
	 */
	class EO_MailingChain {
		/* @var \Bitrix\Sender\MailingChainTable */
		static public $dataClass = '\Bitrix\Sender\MailingChainTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_MailingChain_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getMailingIdList()
	 * @method \string[] getMessageCodeList()
	 * @method \string[] fillMessageCode()
	 * @method \int[] getMessageIdList()
	 * @method \int[] fillMessageId()
	 * @method \int[] getPostingIdList()
	 * @method \int[] fillPostingId()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 * @method \string[] getReiterateList()
	 * @method \string[] fillReiterate()
	 * @method \Bitrix\Main\Type\DateTime[] getLastExecutedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastExecuted()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getEmailFromList()
	 * @method \string[] fillEmailFrom()
	 * @method \string[] getSubjectList()
	 * @method \string[] fillSubject()
	 * @method \string[] getMessageList()
	 * @method \string[] fillMessage()
	 * @method \string[] getTemplateTypeList()
	 * @method \string[] fillTemplateType()
	 * @method \string[] getTemplateIdList()
	 * @method \string[] fillTemplateId()
	 * @method \string[] getIsTriggerList()
	 * @method \string[] fillIsTrigger()
	 * @method \int[] getTimeShiftList()
	 * @method \int[] fillTimeShift()
	 * @method \Bitrix\Main\Type\DateTime[] getAutoSendTimeList()
	 * @method \Bitrix\Main\Type\DateTime[] fillAutoSendTime()
	 * @method \string[] getMonthsOfYearList()
	 * @method \string[] fillMonthsOfYear()
	 * @method \string[] getDaysOfMonthList()
	 * @method \string[] fillDaysOfMonth()
	 * @method \string[] getDaysOfWeekList()
	 * @method \string[] fillDaysOfWeek()
	 * @method \string[] getTimesOfDayList()
	 * @method \string[] fillTimesOfDay()
	 * @method \string[] getPriorityList()
	 * @method \string[] fillPriority()
	 * @method \string[] getLinkParamsList()
	 * @method \string[] fillLinkParams()
	 * @method \Bitrix\Sender\EO_Mailing[] getMailingList()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection getMailingCollection()
	 * @method \Bitrix\Sender\EO_Mailing_Collection fillMailing()
	 * @method \Bitrix\Sender\EO_Posting[] getCurrentPostingList()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection getCurrentPostingCollection()
	 * @method \Bitrix\Sender\EO_Posting_Collection fillCurrentPosting()
	 * @method \Bitrix\Sender\EO_Posting[] getPostingList()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection getPostingCollection()
	 * @method \Bitrix\Sender\EO_Posting_Collection fillPosting()
	 * @method \Bitrix\Sender\EO_MailingAttachment[] getAttachmentList()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection getAttachmentCollection()
	 * @method \Bitrix\Sender\EO_MailingAttachment_Collection fillAttachment()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \boolean[] getWaitingRecipientList()
	 * @method \boolean[] fillWaitingRecipient()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_MailingChain $object)
	 * @method bool has(\Bitrix\Sender\EO_MailingChain $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingChain getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingChain[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_MailingChain $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_MailingChain_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_MailingChain current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_MailingChain_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\MailingChainTable */
		static public $dataClass = '\Bitrix\Sender\MailingChainTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailingChain_Result exec()
	 * @method \Bitrix\Sender\EO_MailingChain fetchObject()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailingChain_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_MailingChain fetchObject()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection fetchCollection()
	 */
	class EO_MailingChain_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_MailingChain createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_MailingChain_Collection createCollection()
	 * @method \Bitrix\Sender\EO_MailingChain wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_MailingChain_Collection wakeUpCollection($rows)
	 */
	class EO_MailingChain_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\MailingAttachmentTable:sender/lib/mailingchain.php:032ebed36d36e539c06b2d69900272e8 */
namespace Bitrix\Sender {
	/**
	 * EO_MailingAttachment
	 * @see \Bitrix\Sender\MailingAttachmentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getChainId()
	 * @method \Bitrix\Sender\EO_MailingAttachment setChainId(\int|\Bitrix\Main\DB\SqlExpression $chainId)
	 * @method bool hasChainId()
	 * @method bool isChainIdFilled()
	 * @method bool isChainIdChanged()
	 * @method \int getFileId()
	 * @method \Bitrix\Sender\EO_MailingAttachment setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
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
	 * @method \Bitrix\Sender\EO_MailingAttachment set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_MailingAttachment reset($fieldName)
	 * @method \Bitrix\Sender\EO_MailingAttachment unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_MailingAttachment wakeUp($data)
	 */
	class EO_MailingAttachment {
		/* @var \Bitrix\Sender\MailingAttachmentTable */
		static public $dataClass = '\Bitrix\Sender\MailingAttachmentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_MailingAttachment_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getChainIdList()
	 * @method \int[] getFileIdList()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_MailingAttachment $object)
	 * @method bool has(\Bitrix\Sender\EO_MailingAttachment $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingAttachment getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingAttachment[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_MailingAttachment $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_MailingAttachment_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_MailingAttachment current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_MailingAttachment_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\MailingAttachmentTable */
		static public $dataClass = '\Bitrix\Sender\MailingAttachmentTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailingAttachment_Result exec()
	 * @method \Bitrix\Sender\EO_MailingAttachment fetchObject()
	 * @method \Bitrix\Sender\EO_MailingAttachment_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailingAttachment_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_MailingAttachment fetchObject()
	 * @method \Bitrix\Sender\EO_MailingAttachment_Collection fetchCollection()
	 */
	class EO_MailingAttachment_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_MailingAttachment createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_MailingAttachment_Collection createCollection()
	 * @method \Bitrix\Sender\EO_MailingAttachment wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_MailingAttachment_Collection wakeUpCollection($rows)
	 */
	class EO_MailingAttachment_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\MailingTriggerTable:sender/lib/mailingtrigger.php:dac5b4e6594d665ef20e014ce2c585ec */
namespace Bitrix\Sender {
	/**
	 * EO_MailingTrigger
	 * @see \Bitrix\Sender\MailingTriggerTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getMailingChainId()
	 * @method \Bitrix\Sender\EO_MailingTrigger setMailingChainId(\int|\Bitrix\Main\DB\SqlExpression $mailingChainId)
	 * @method bool hasMailingChainId()
	 * @method bool isMailingChainIdFilled()
	 * @method bool isMailingChainIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Sender\EO_MailingTrigger setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\EO_MailingTrigger resetName()
	 * @method \Bitrix\Sender\EO_MailingTrigger unsetName()
	 * @method \string fillName()
	 * @method \string getEvent()
	 * @method \Bitrix\Sender\EO_MailingTrigger setEvent(\string|\Bitrix\Main\DB\SqlExpression $event)
	 * @method bool hasEvent()
	 * @method bool isEventFilled()
	 * @method bool isEventChanged()
	 * @method \boolean getIsTypeStart()
	 * @method \Bitrix\Sender\EO_MailingTrigger setIsTypeStart(\boolean|\Bitrix\Main\DB\SqlExpression $isTypeStart)
	 * @method bool hasIsTypeStart()
	 * @method bool isIsTypeStartFilled()
	 * @method bool isIsTypeStartChanged()
	 * @method \string getEndpoint()
	 * @method \Bitrix\Sender\EO_MailingTrigger setEndpoint(\string|\Bitrix\Main\DB\SqlExpression $endpoint)
	 * @method bool hasEndpoint()
	 * @method bool isEndpointFilled()
	 * @method bool isEndpointChanged()
	 * @method \string remindActualEndpoint()
	 * @method \string requireEndpoint()
	 * @method \Bitrix\Sender\EO_MailingTrigger resetEndpoint()
	 * @method \Bitrix\Sender\EO_MailingTrigger unsetEndpoint()
	 * @method \string fillEndpoint()
	 * @method \Bitrix\Sender\EO_MailingChain getMailingChain()
	 * @method \Bitrix\Sender\EO_MailingChain remindActualMailingChain()
	 * @method \Bitrix\Sender\EO_MailingChain requireMailingChain()
	 * @method \Bitrix\Sender\EO_MailingTrigger setMailingChain(\Bitrix\Sender\EO_MailingChain $object)
	 * @method \Bitrix\Sender\EO_MailingTrigger resetMailingChain()
	 * @method \Bitrix\Sender\EO_MailingTrigger unsetMailingChain()
	 * @method bool hasMailingChain()
	 * @method bool isMailingChainFilled()
	 * @method bool isMailingChainChanged()
	 * @method \Bitrix\Sender\EO_MailingChain fillMailingChain()
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
	 * @method \Bitrix\Sender\EO_MailingTrigger set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_MailingTrigger reset($fieldName)
	 * @method \Bitrix\Sender\EO_MailingTrigger unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_MailingTrigger wakeUp($data)
	 */
	class EO_MailingTrigger {
		/* @var \Bitrix\Sender\MailingTriggerTable */
		static public $dataClass = '\Bitrix\Sender\MailingTriggerTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_MailingTrigger_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getMailingChainIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getEventList()
	 * @method \boolean[] getIsTypeStartList()
	 * @method \string[] getEndpointList()
	 * @method \string[] fillEndpoint()
	 * @method \Bitrix\Sender\EO_MailingChain[] getMailingChainList()
	 * @method \Bitrix\Sender\EO_MailingTrigger_Collection getMailingChainCollection()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection fillMailingChain()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_MailingTrigger $object)
	 * @method bool has(\Bitrix\Sender\EO_MailingTrigger $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingTrigger getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_MailingTrigger[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_MailingTrigger $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_MailingTrigger_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_MailingTrigger current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_MailingTrigger_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\MailingTriggerTable */
		static public $dataClass = '\Bitrix\Sender\MailingTriggerTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MailingTrigger_Result exec()
	 * @method \Bitrix\Sender\EO_MailingTrigger fetchObject()
	 * @method \Bitrix\Sender\EO_MailingTrigger_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MailingTrigger_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_MailingTrigger fetchObject()
	 * @method \Bitrix\Sender\EO_MailingTrigger_Collection fetchCollection()
	 */
	class EO_MailingTrigger_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_MailingTrigger createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_MailingTrigger_Collection createCollection()
	 * @method \Bitrix\Sender\EO_MailingTrigger wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_MailingTrigger_Collection wakeUpCollection($rows)
	 */
	class EO_MailingTrigger_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\PostingTable:sender/lib/posting.php:3797f5ef67589dbec288368f284a86b2 */
namespace Bitrix\Sender {
	/**
	 * EO_Posting
	 * @see \Bitrix\Sender\PostingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_Posting setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getMailingId()
	 * @method \Bitrix\Sender\EO_Posting setMailingId(\int|\Bitrix\Main\DB\SqlExpression $mailingId)
	 * @method bool hasMailingId()
	 * @method bool isMailingIdFilled()
	 * @method bool isMailingIdChanged()
	 * @method \int getMailingChainId()
	 * @method \Bitrix\Sender\EO_Posting setMailingChainId(\int|\Bitrix\Main\DB\SqlExpression $mailingChainId)
	 * @method bool hasMailingChainId()
	 * @method bool isMailingChainIdFilled()
	 * @method bool isMailingChainIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Sender\EO_Posting setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Sender\EO_Posting resetDateCreate()
	 * @method \Bitrix\Sender\EO_Posting unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Sender\EO_Posting setDateUpdate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Sender\EO_Posting resetDateUpdate()
	 * @method \Bitrix\Sender\EO_Posting unsetDateUpdate()
	 * @method \Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\EO_Posting setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Sender\EO_Posting resetStatus()
	 * @method \Bitrix\Sender\EO_Posting unsetStatus()
	 * @method \string fillStatus()
	 * @method \Bitrix\Main\Type\DateTime getDateSend()
	 * @method \Bitrix\Sender\EO_Posting setDateSend(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateSend)
	 * @method bool hasDateSend()
	 * @method bool isDateSendFilled()
	 * @method bool isDateSendChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateSend()
	 * @method \Bitrix\Main\Type\DateTime requireDateSend()
	 * @method \Bitrix\Sender\EO_Posting resetDateSend()
	 * @method \Bitrix\Sender\EO_Posting unsetDateSend()
	 * @method \Bitrix\Main\Type\DateTime fillDateSend()
	 * @method \Bitrix\Main\Type\DateTime getDatePause()
	 * @method \Bitrix\Sender\EO_Posting setDatePause(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $datePause)
	 * @method bool hasDatePause()
	 * @method bool isDatePauseFilled()
	 * @method bool isDatePauseChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDatePause()
	 * @method \Bitrix\Main\Type\DateTime requireDatePause()
	 * @method \Bitrix\Sender\EO_Posting resetDatePause()
	 * @method \Bitrix\Sender\EO_Posting unsetDatePause()
	 * @method \Bitrix\Main\Type\DateTime fillDatePause()
	 * @method \Bitrix\Main\Type\DateTime getDateSent()
	 * @method \Bitrix\Sender\EO_Posting setDateSent(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateSent)
	 * @method bool hasDateSent()
	 * @method bool isDateSentFilled()
	 * @method bool isDateSentChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateSent()
	 * @method \Bitrix\Main\Type\DateTime requireDateSent()
	 * @method \Bitrix\Sender\EO_Posting resetDateSent()
	 * @method \Bitrix\Sender\EO_Posting unsetDateSent()
	 * @method \Bitrix\Main\Type\DateTime fillDateSent()
	 * @method \int getCountRead()
	 * @method \Bitrix\Sender\EO_Posting setCountRead(\int|\Bitrix\Main\DB\SqlExpression $countRead)
	 * @method bool hasCountRead()
	 * @method bool isCountReadFilled()
	 * @method bool isCountReadChanged()
	 * @method \int remindActualCountRead()
	 * @method \int requireCountRead()
	 * @method \Bitrix\Sender\EO_Posting resetCountRead()
	 * @method \Bitrix\Sender\EO_Posting unsetCountRead()
	 * @method \int fillCountRead()
	 * @method \int getCountClick()
	 * @method \Bitrix\Sender\EO_Posting setCountClick(\int|\Bitrix\Main\DB\SqlExpression $countClick)
	 * @method bool hasCountClick()
	 * @method bool isCountClickFilled()
	 * @method bool isCountClickChanged()
	 * @method \int remindActualCountClick()
	 * @method \int requireCountClick()
	 * @method \Bitrix\Sender\EO_Posting resetCountClick()
	 * @method \Bitrix\Sender\EO_Posting unsetCountClick()
	 * @method \int fillCountClick()
	 * @method \int getCountUnsub()
	 * @method \Bitrix\Sender\EO_Posting setCountUnsub(\int|\Bitrix\Main\DB\SqlExpression $countUnsub)
	 * @method bool hasCountUnsub()
	 * @method bool isCountUnsubFilled()
	 * @method bool isCountUnsubChanged()
	 * @method \int remindActualCountUnsub()
	 * @method \int requireCountUnsub()
	 * @method \Bitrix\Sender\EO_Posting resetCountUnsub()
	 * @method \Bitrix\Sender\EO_Posting unsetCountUnsub()
	 * @method \int fillCountUnsub()
	 * @method \int getCountSendAll()
	 * @method \Bitrix\Sender\EO_Posting setCountSendAll(\int|\Bitrix\Main\DB\SqlExpression $countSendAll)
	 * @method bool hasCountSendAll()
	 * @method bool isCountSendAllFilled()
	 * @method bool isCountSendAllChanged()
	 * @method \int remindActualCountSendAll()
	 * @method \int requireCountSendAll()
	 * @method \Bitrix\Sender\EO_Posting resetCountSendAll()
	 * @method \Bitrix\Sender\EO_Posting unsetCountSendAll()
	 * @method \int fillCountSendAll()
	 * @method \int getCountSendNone()
	 * @method \Bitrix\Sender\EO_Posting setCountSendNone(\int|\Bitrix\Main\DB\SqlExpression $countSendNone)
	 * @method bool hasCountSendNone()
	 * @method bool isCountSendNoneFilled()
	 * @method bool isCountSendNoneChanged()
	 * @method \int remindActualCountSendNone()
	 * @method \int requireCountSendNone()
	 * @method \Bitrix\Sender\EO_Posting resetCountSendNone()
	 * @method \Bitrix\Sender\EO_Posting unsetCountSendNone()
	 * @method \int fillCountSendNone()
	 * @method \int getCountSendError()
	 * @method \Bitrix\Sender\EO_Posting setCountSendError(\int|\Bitrix\Main\DB\SqlExpression $countSendError)
	 * @method bool hasCountSendError()
	 * @method bool isCountSendErrorFilled()
	 * @method bool isCountSendErrorChanged()
	 * @method \int remindActualCountSendError()
	 * @method \int requireCountSendError()
	 * @method \Bitrix\Sender\EO_Posting resetCountSendError()
	 * @method \Bitrix\Sender\EO_Posting unsetCountSendError()
	 * @method \int fillCountSendError()
	 * @method \int getCountSendSuccess()
	 * @method \Bitrix\Sender\EO_Posting setCountSendSuccess(\int|\Bitrix\Main\DB\SqlExpression $countSendSuccess)
	 * @method bool hasCountSendSuccess()
	 * @method bool isCountSendSuccessFilled()
	 * @method bool isCountSendSuccessChanged()
	 * @method \int remindActualCountSendSuccess()
	 * @method \int requireCountSendSuccess()
	 * @method \Bitrix\Sender\EO_Posting resetCountSendSuccess()
	 * @method \Bitrix\Sender\EO_Posting unsetCountSendSuccess()
	 * @method \int fillCountSendSuccess()
	 * @method \int getCountSendDeny()
	 * @method \Bitrix\Sender\EO_Posting setCountSendDeny(\int|\Bitrix\Main\DB\SqlExpression $countSendDeny)
	 * @method bool hasCountSendDeny()
	 * @method bool isCountSendDenyFilled()
	 * @method bool isCountSendDenyChanged()
	 * @method \int remindActualCountSendDeny()
	 * @method \int requireCountSendDeny()
	 * @method \Bitrix\Sender\EO_Posting resetCountSendDeny()
	 * @method \Bitrix\Sender\EO_Posting unsetCountSendDeny()
	 * @method \int fillCountSendDeny()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter getLetter()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter remindActualLetter()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter requireLetter()
	 * @method \Bitrix\Sender\EO_Posting setLetter(\Bitrix\Sender\Internals\Model\EO_Letter $object)
	 * @method \Bitrix\Sender\EO_Posting resetLetter()
	 * @method \Bitrix\Sender\EO_Posting unsetLetter()
	 * @method bool hasLetter()
	 * @method bool isLetterFilled()
	 * @method bool isLetterChanged()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter fillLetter()
	 * @method \Bitrix\Sender\EO_Mailing getMailing()
	 * @method \Bitrix\Sender\EO_Mailing remindActualMailing()
	 * @method \Bitrix\Sender\EO_Mailing requireMailing()
	 * @method \Bitrix\Sender\EO_Posting setMailing(\Bitrix\Sender\EO_Mailing $object)
	 * @method \Bitrix\Sender\EO_Posting resetMailing()
	 * @method \Bitrix\Sender\EO_Posting unsetMailing()
	 * @method bool hasMailing()
	 * @method bool isMailingFilled()
	 * @method bool isMailingChanged()
	 * @method \Bitrix\Sender\EO_Mailing fillMailing()
	 * @method \Bitrix\Sender\EO_MailingChain getMailingChain()
	 * @method \Bitrix\Sender\EO_MailingChain remindActualMailingChain()
	 * @method \Bitrix\Sender\EO_MailingChain requireMailingChain()
	 * @method \Bitrix\Sender\EO_Posting setMailingChain(\Bitrix\Sender\EO_MailingChain $object)
	 * @method \Bitrix\Sender\EO_Posting resetMailingChain()
	 * @method \Bitrix\Sender\EO_Posting unsetMailingChain()
	 * @method bool hasMailingChain()
	 * @method bool isMailingChainFilled()
	 * @method bool isMailingChainChanged()
	 * @method \Bitrix\Sender\EO_MailingChain fillMailingChain()
	 * @method \Bitrix\Sender\EO_PostingRecipient getPostingRecipient()
	 * @method \Bitrix\Sender\EO_PostingRecipient remindActualPostingRecipient()
	 * @method \Bitrix\Sender\EO_PostingRecipient requirePostingRecipient()
	 * @method \Bitrix\Sender\EO_Posting setPostingRecipient(\Bitrix\Sender\EO_PostingRecipient $object)
	 * @method \Bitrix\Sender\EO_Posting resetPostingRecipient()
	 * @method \Bitrix\Sender\EO_Posting unsetPostingRecipient()
	 * @method bool hasPostingRecipient()
	 * @method bool isPostingRecipientFilled()
	 * @method bool isPostingRecipientChanged()
	 * @method \Bitrix\Sender\EO_PostingRecipient fillPostingRecipient()
	 * @method \Bitrix\Sender\EO_PostingRead getPostingRead()
	 * @method \Bitrix\Sender\EO_PostingRead remindActualPostingRead()
	 * @method \Bitrix\Sender\EO_PostingRead requirePostingRead()
	 * @method \Bitrix\Sender\EO_Posting setPostingRead(\Bitrix\Sender\EO_PostingRead $object)
	 * @method \Bitrix\Sender\EO_Posting resetPostingRead()
	 * @method \Bitrix\Sender\EO_Posting unsetPostingRead()
	 * @method bool hasPostingRead()
	 * @method bool isPostingReadFilled()
	 * @method bool isPostingReadChanged()
	 * @method \Bitrix\Sender\EO_PostingRead fillPostingRead()
	 * @method \Bitrix\Sender\EO_PostingClick getPostingClick()
	 * @method \Bitrix\Sender\EO_PostingClick remindActualPostingClick()
	 * @method \Bitrix\Sender\EO_PostingClick requirePostingClick()
	 * @method \Bitrix\Sender\EO_Posting setPostingClick(\Bitrix\Sender\EO_PostingClick $object)
	 * @method \Bitrix\Sender\EO_Posting resetPostingClick()
	 * @method \Bitrix\Sender\EO_Posting unsetPostingClick()
	 * @method bool hasPostingClick()
	 * @method bool isPostingClickFilled()
	 * @method bool isPostingClickChanged()
	 * @method \Bitrix\Sender\EO_PostingClick fillPostingClick()
	 * @method \Bitrix\Sender\EO_PostingUnsub getPostingUnsub()
	 * @method \Bitrix\Sender\EO_PostingUnsub remindActualPostingUnsub()
	 * @method \Bitrix\Sender\EO_PostingUnsub requirePostingUnsub()
	 * @method \Bitrix\Sender\EO_Posting setPostingUnsub(\Bitrix\Sender\EO_PostingUnsub $object)
	 * @method \Bitrix\Sender\EO_Posting resetPostingUnsub()
	 * @method \Bitrix\Sender\EO_Posting unsetPostingUnsub()
	 * @method bool hasPostingUnsub()
	 * @method bool isPostingUnsubFilled()
	 * @method bool isPostingUnsubChanged()
	 * @method \Bitrix\Sender\EO_PostingUnsub fillPostingUnsub()
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
	 * @method \Bitrix\Sender\EO_Posting set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_Posting reset($fieldName)
	 * @method \Bitrix\Sender\EO_Posting unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_Posting wakeUp($data)
	 */
	class EO_Posting {
		/* @var \Bitrix\Sender\PostingTable */
		static public $dataClass = '\Bitrix\Sender\PostingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_Posting_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getMailingIdList()
	 * @method \int[] getMailingChainIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 * @method \Bitrix\Main\Type\DateTime[] getDateSendList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateSend()
	 * @method \Bitrix\Main\Type\DateTime[] getDatePauseList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDatePause()
	 * @method \Bitrix\Main\Type\DateTime[] getDateSentList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateSent()
	 * @method \int[] getCountReadList()
	 * @method \int[] fillCountRead()
	 * @method \int[] getCountClickList()
	 * @method \int[] fillCountClick()
	 * @method \int[] getCountUnsubList()
	 * @method \int[] fillCountUnsub()
	 * @method \int[] getCountSendAllList()
	 * @method \int[] fillCountSendAll()
	 * @method \int[] getCountSendNoneList()
	 * @method \int[] fillCountSendNone()
	 * @method \int[] getCountSendErrorList()
	 * @method \int[] fillCountSendError()
	 * @method \int[] getCountSendSuccessList()
	 * @method \int[] fillCountSendSuccess()
	 * @method \int[] getCountSendDenyList()
	 * @method \int[] fillCountSendDeny()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter[] getLetterList()
	 * @method \Bitrix\Sender\EO_Posting_Collection getLetterCollection()
	 * @method \Bitrix\Sender\Internals\Model\EO_Letter_Collection fillLetter()
	 * @method \Bitrix\Sender\EO_Mailing[] getMailingList()
	 * @method \Bitrix\Sender\EO_Posting_Collection getMailingCollection()
	 * @method \Bitrix\Sender\EO_Mailing_Collection fillMailing()
	 * @method \Bitrix\Sender\EO_MailingChain[] getMailingChainList()
	 * @method \Bitrix\Sender\EO_Posting_Collection getMailingChainCollection()
	 * @method \Bitrix\Sender\EO_MailingChain_Collection fillMailingChain()
	 * @method \Bitrix\Sender\EO_PostingRecipient[] getPostingRecipientList()
	 * @method \Bitrix\Sender\EO_Posting_Collection getPostingRecipientCollection()
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection fillPostingRecipient()
	 * @method \Bitrix\Sender\EO_PostingRead[] getPostingReadList()
	 * @method \Bitrix\Sender\EO_Posting_Collection getPostingReadCollection()
	 * @method \Bitrix\Sender\EO_PostingRead_Collection fillPostingRead()
	 * @method \Bitrix\Sender\EO_PostingClick[] getPostingClickList()
	 * @method \Bitrix\Sender\EO_Posting_Collection getPostingClickCollection()
	 * @method \Bitrix\Sender\EO_PostingClick_Collection fillPostingClick()
	 * @method \Bitrix\Sender\EO_PostingUnsub[] getPostingUnsubList()
	 * @method \Bitrix\Sender\EO_Posting_Collection getPostingUnsubCollection()
	 * @method \Bitrix\Sender\EO_PostingUnsub_Collection fillPostingUnsub()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_Posting $object)
	 * @method bool has(\Bitrix\Sender\EO_Posting $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Posting getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Posting[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_Posting $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_Posting_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_Posting current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Posting_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\PostingTable */
		static public $dataClass = '\Bitrix\Sender\PostingTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Posting_Result exec()
	 * @method \Bitrix\Sender\EO_Posting fetchObject()
	 * @method \Bitrix\Sender\EO_Posting_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Posting_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_Posting fetchObject()
	 * @method \Bitrix\Sender\EO_Posting_Collection fetchCollection()
	 */
	class EO_Posting_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_Posting createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_Posting_Collection createCollection()
	 * @method \Bitrix\Sender\EO_Posting wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_Posting_Collection wakeUpCollection($rows)
	 */
	class EO_Posting_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\PostingReadTable:sender/lib/posting.php:3797f5ef67589dbec288368f284a86b2 */
namespace Bitrix\Sender {
	/**
	 * EO_PostingRead
	 * @see \Bitrix\Sender\PostingReadTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_PostingRead setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\EO_PostingRead setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int getRecipientId()
	 * @method \Bitrix\Sender\EO_PostingRead setRecipientId(\int|\Bitrix\Main\DB\SqlExpression $recipientId)
	 * @method bool hasRecipientId()
	 * @method bool isRecipientIdFilled()
	 * @method bool isRecipientIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_PostingRead setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_PostingRead resetDateInsert()
	 * @method \Bitrix\Sender\EO_PostingRead unsetDateInsert()
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
	 * @method \Bitrix\Sender\EO_PostingRead set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_PostingRead reset($fieldName)
	 * @method \Bitrix\Sender\EO_PostingRead unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_PostingRead wakeUp($data)
	 */
	class EO_PostingRead {
		/* @var \Bitrix\Sender\PostingReadTable */
		static public $dataClass = '\Bitrix\Sender\PostingReadTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_PostingRead_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostingIdList()
	 * @method \int[] getRecipientIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_PostingRead $object)
	 * @method bool has(\Bitrix\Sender\EO_PostingRead $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_PostingRead getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_PostingRead[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_PostingRead $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_PostingRead_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_PostingRead current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PostingRead_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\PostingReadTable */
		static public $dataClass = '\Bitrix\Sender\PostingReadTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PostingRead_Result exec()
	 * @method \Bitrix\Sender\EO_PostingRead fetchObject()
	 * @method \Bitrix\Sender\EO_PostingRead_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PostingRead_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_PostingRead fetchObject()
	 * @method \Bitrix\Sender\EO_PostingRead_Collection fetchCollection()
	 */
	class EO_PostingRead_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_PostingRead createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_PostingRead_Collection createCollection()
	 * @method \Bitrix\Sender\EO_PostingRead wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_PostingRead_Collection wakeUpCollection($rows)
	 */
	class EO_PostingRead_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\PostingClickTable:sender/lib/posting.php:3797f5ef67589dbec288368f284a86b2 */
namespace Bitrix\Sender {
	/**
	 * EO_PostingClick
	 * @see \Bitrix\Sender\PostingClickTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_PostingClick setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\EO_PostingClick setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int getRecipientId()
	 * @method \Bitrix\Sender\EO_PostingClick setRecipientId(\int|\Bitrix\Main\DB\SqlExpression $recipientId)
	 * @method bool hasRecipientId()
	 * @method bool isRecipientIdFilled()
	 * @method bool isRecipientIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_PostingClick setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_PostingClick resetDateInsert()
	 * @method \Bitrix\Sender\EO_PostingClick unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getUrl()
	 * @method \Bitrix\Sender\EO_PostingClick setUrl(\string|\Bitrix\Main\DB\SqlExpression $url)
	 * @method bool hasUrl()
	 * @method bool isUrlFilled()
	 * @method bool isUrlChanged()
	 * @method \string remindActualUrl()
	 * @method \string requireUrl()
	 * @method \Bitrix\Sender\EO_PostingClick resetUrl()
	 * @method \Bitrix\Sender\EO_PostingClick unsetUrl()
	 * @method \string fillUrl()
	 * @method \Bitrix\Sender\EO_Posting getPosting()
	 * @method \Bitrix\Sender\EO_Posting remindActualPosting()
	 * @method \Bitrix\Sender\EO_Posting requirePosting()
	 * @method \Bitrix\Sender\EO_PostingClick setPosting(\Bitrix\Sender\EO_Posting $object)
	 * @method \Bitrix\Sender\EO_PostingClick resetPosting()
	 * @method \Bitrix\Sender\EO_PostingClick unsetPosting()
	 * @method bool hasPosting()
	 * @method bool isPostingFilled()
	 * @method bool isPostingChanged()
	 * @method \Bitrix\Sender\EO_Posting fillPosting()
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
	 * @method \Bitrix\Sender\EO_PostingClick set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_PostingClick reset($fieldName)
	 * @method \Bitrix\Sender\EO_PostingClick unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_PostingClick wakeUp($data)
	 */
	class EO_PostingClick {
		/* @var \Bitrix\Sender\PostingClickTable */
		static public $dataClass = '\Bitrix\Sender\PostingClickTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_PostingClick_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostingIdList()
	 * @method \int[] getRecipientIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getUrlList()
	 * @method \string[] fillUrl()
	 * @method \Bitrix\Sender\EO_Posting[] getPostingList()
	 * @method \Bitrix\Sender\EO_PostingClick_Collection getPostingCollection()
	 * @method \Bitrix\Sender\EO_Posting_Collection fillPosting()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_PostingClick $object)
	 * @method bool has(\Bitrix\Sender\EO_PostingClick $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_PostingClick getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_PostingClick[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_PostingClick $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_PostingClick_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_PostingClick current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PostingClick_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\PostingClickTable */
		static public $dataClass = '\Bitrix\Sender\PostingClickTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PostingClick_Result exec()
	 * @method \Bitrix\Sender\EO_PostingClick fetchObject()
	 * @method \Bitrix\Sender\EO_PostingClick_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PostingClick_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_PostingClick fetchObject()
	 * @method \Bitrix\Sender\EO_PostingClick_Collection fetchCollection()
	 */
	class EO_PostingClick_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_PostingClick createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_PostingClick_Collection createCollection()
	 * @method \Bitrix\Sender\EO_PostingClick wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_PostingClick_Collection wakeUpCollection($rows)
	 */
	class EO_PostingClick_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\PostingUnsubTable:sender/lib/posting.php:3797f5ef67589dbec288368f284a86b2 */
namespace Bitrix\Sender {
	/**
	 * EO_PostingUnsub
	 * @see \Bitrix\Sender\PostingUnsubTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_PostingUnsub setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\EO_PostingUnsub setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int getRecipientId()
	 * @method \Bitrix\Sender\EO_PostingUnsub setRecipientId(\int|\Bitrix\Main\DB\SqlExpression $recipientId)
	 * @method bool hasRecipientId()
	 * @method bool isRecipientIdFilled()
	 * @method bool isRecipientIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_PostingUnsub setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_PostingUnsub resetDateInsert()
	 * @method \Bitrix\Sender\EO_PostingUnsub unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Sender\EO_Posting getPosting()
	 * @method \Bitrix\Sender\EO_Posting remindActualPosting()
	 * @method \Bitrix\Sender\EO_Posting requirePosting()
	 * @method \Bitrix\Sender\EO_PostingUnsub setPosting(\Bitrix\Sender\EO_Posting $object)
	 * @method \Bitrix\Sender\EO_PostingUnsub resetPosting()
	 * @method \Bitrix\Sender\EO_PostingUnsub unsetPosting()
	 * @method bool hasPosting()
	 * @method bool isPostingFilled()
	 * @method bool isPostingChanged()
	 * @method \Bitrix\Sender\EO_Posting fillPosting()
	 * @method \Bitrix\Sender\EO_PostingRecipient getPostingRecipient()
	 * @method \Bitrix\Sender\EO_PostingRecipient remindActualPostingRecipient()
	 * @method \Bitrix\Sender\EO_PostingRecipient requirePostingRecipient()
	 * @method \Bitrix\Sender\EO_PostingUnsub setPostingRecipient(\Bitrix\Sender\EO_PostingRecipient $object)
	 * @method \Bitrix\Sender\EO_PostingUnsub resetPostingRecipient()
	 * @method \Bitrix\Sender\EO_PostingUnsub unsetPostingRecipient()
	 * @method bool hasPostingRecipient()
	 * @method bool isPostingRecipientFilled()
	 * @method bool isPostingRecipientChanged()
	 * @method \Bitrix\Sender\EO_PostingRecipient fillPostingRecipient()
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
	 * @method \Bitrix\Sender\EO_PostingUnsub set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_PostingUnsub reset($fieldName)
	 * @method \Bitrix\Sender\EO_PostingUnsub unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_PostingUnsub wakeUp($data)
	 */
	class EO_PostingUnsub {
		/* @var \Bitrix\Sender\PostingUnsubTable */
		static public $dataClass = '\Bitrix\Sender\PostingUnsubTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_PostingUnsub_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostingIdList()
	 * @method \int[] getRecipientIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Sender\EO_Posting[] getPostingList()
	 * @method \Bitrix\Sender\EO_PostingUnsub_Collection getPostingCollection()
	 * @method \Bitrix\Sender\EO_Posting_Collection fillPosting()
	 * @method \Bitrix\Sender\EO_PostingRecipient[] getPostingRecipientList()
	 * @method \Bitrix\Sender\EO_PostingUnsub_Collection getPostingRecipientCollection()
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection fillPostingRecipient()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_PostingUnsub $object)
	 * @method bool has(\Bitrix\Sender\EO_PostingUnsub $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_PostingUnsub getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_PostingUnsub[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_PostingUnsub $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_PostingUnsub_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_PostingUnsub current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PostingUnsub_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\PostingUnsubTable */
		static public $dataClass = '\Bitrix\Sender\PostingUnsubTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PostingUnsub_Result exec()
	 * @method \Bitrix\Sender\EO_PostingUnsub fetchObject()
	 * @method \Bitrix\Sender\EO_PostingUnsub_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PostingUnsub_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_PostingUnsub fetchObject()
	 * @method \Bitrix\Sender\EO_PostingUnsub_Collection fetchCollection()
	 */
	class EO_PostingUnsub_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_PostingUnsub createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_PostingUnsub_Collection createCollection()
	 * @method \Bitrix\Sender\EO_PostingUnsub wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_PostingUnsub_Collection wakeUpCollection($rows)
	 */
	class EO_PostingUnsub_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\PostingRecipientTable:sender/lib/posting.php:3797f5ef67589dbec288368f284a86b2 */
namespace Bitrix\Sender {
	/**
	 * EO_PostingRecipient
	 * @see \Bitrix\Sender\PostingRecipientTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_PostingRecipient setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\EO_PostingRecipient setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\EO_PostingRecipient setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateSent()
	 * @method \Bitrix\Sender\EO_PostingRecipient setDateSent(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateSent)
	 * @method bool hasDateSent()
	 * @method bool isDateSentFilled()
	 * @method bool isDateSentChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateSent()
	 * @method \Bitrix\Main\Type\DateTime requireDateSent()
	 * @method \Bitrix\Sender\EO_PostingRecipient resetDateSent()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetDateSent()
	 * @method \Bitrix\Main\Type\DateTime fillDateSent()
	 * @method \Bitrix\Main\Type\DateTime getDateDeny()
	 * @method \Bitrix\Sender\EO_PostingRecipient setDateDeny(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateDeny)
	 * @method bool hasDateDeny()
	 * @method bool isDateDenyFilled()
	 * @method bool isDateDenyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateDeny()
	 * @method \Bitrix\Main\Type\DateTime requireDateDeny()
	 * @method \Bitrix\Sender\EO_PostingRecipient resetDateDeny()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetDateDeny()
	 * @method \Bitrix\Main\Type\DateTime fillDateDeny()
	 * @method \int getContactId()
	 * @method \Bitrix\Sender\EO_PostingRecipient setContactId(\int|\Bitrix\Main\DB\SqlExpression $contactId)
	 * @method bool hasContactId()
	 * @method bool isContactIdFilled()
	 * @method bool isContactIdChanged()
	 * @method \int remindActualContactId()
	 * @method \int requireContactId()
	 * @method \Bitrix\Sender\EO_PostingRecipient resetContactId()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetContactId()
	 * @method \int fillContactId()
	 * @method \int getUserId()
	 * @method \Bitrix\Sender\EO_PostingRecipient setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Sender\EO_PostingRecipient resetUserId()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getFields()
	 * @method \Bitrix\Sender\EO_PostingRecipient setFields(\string|\Bitrix\Main\DB\SqlExpression $fields)
	 * @method bool hasFields()
	 * @method bool isFieldsFilled()
	 * @method bool isFieldsChanged()
	 * @method \string remindActualFields()
	 * @method \string requireFields()
	 * @method \Bitrix\Sender\EO_PostingRecipient resetFields()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetFields()
	 * @method \string fillFields()
	 * @method \int getRootId()
	 * @method \Bitrix\Sender\EO_PostingRecipient setRootId(\int|\Bitrix\Main\DB\SqlExpression $rootId)
	 * @method bool hasRootId()
	 * @method bool isRootIdFilled()
	 * @method bool isRootIdChanged()
	 * @method \int remindActualRootId()
	 * @method \int requireRootId()
	 * @method \Bitrix\Sender\EO_PostingRecipient resetRootId()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetRootId()
	 * @method \int fillRootId()
	 * @method \string getIsRead()
	 * @method \Bitrix\Sender\EO_PostingRecipient setIsRead(\string|\Bitrix\Main\DB\SqlExpression $isRead)
	 * @method bool hasIsRead()
	 * @method bool isIsReadFilled()
	 * @method bool isIsReadChanged()
	 * @method \string remindActualIsRead()
	 * @method \string requireIsRead()
	 * @method \Bitrix\Sender\EO_PostingRecipient resetIsRead()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetIsRead()
	 * @method \string fillIsRead()
	 * @method \string getIsClick()
	 * @method \Bitrix\Sender\EO_PostingRecipient setIsClick(\string|\Bitrix\Main\DB\SqlExpression $isClick)
	 * @method bool hasIsClick()
	 * @method bool isIsClickFilled()
	 * @method bool isIsClickChanged()
	 * @method \string remindActualIsClick()
	 * @method \string requireIsClick()
	 * @method \Bitrix\Sender\EO_PostingRecipient resetIsClick()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetIsClick()
	 * @method \string fillIsClick()
	 * @method \string getIsUnsub()
	 * @method \Bitrix\Sender\EO_PostingRecipient setIsUnsub(\string|\Bitrix\Main\DB\SqlExpression $isUnsub)
	 * @method bool hasIsUnsub()
	 * @method bool isIsUnsubFilled()
	 * @method bool isIsUnsubChanged()
	 * @method \string remindActualIsUnsub()
	 * @method \string requireIsUnsub()
	 * @method \Bitrix\Sender\EO_PostingRecipient resetIsUnsub()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetIsUnsub()
	 * @method \string fillIsUnsub()
	 * @method \Bitrix\Sender\EO_Contact getContact()
	 * @method \Bitrix\Sender\EO_Contact remindActualContact()
	 * @method \Bitrix\Sender\EO_Contact requireContact()
	 * @method \Bitrix\Sender\EO_PostingRecipient setContact(\Bitrix\Sender\EO_Contact $object)
	 * @method \Bitrix\Sender\EO_PostingRecipient resetContact()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetContact()
	 * @method bool hasContact()
	 * @method bool isContactFilled()
	 * @method bool isContactChanged()
	 * @method \Bitrix\Sender\EO_Contact fillContact()
	 * @method \Bitrix\Sender\EO_Posting getPosting()
	 * @method \Bitrix\Sender\EO_Posting remindActualPosting()
	 * @method \Bitrix\Sender\EO_Posting requirePosting()
	 * @method \Bitrix\Sender\EO_PostingRecipient setPosting(\Bitrix\Sender\EO_Posting $object)
	 * @method \Bitrix\Sender\EO_PostingRecipient resetPosting()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetPosting()
	 * @method bool hasPosting()
	 * @method bool isPostingFilled()
	 * @method bool isPostingChanged()
	 * @method \Bitrix\Sender\EO_Posting fillPosting()
	 * @method \Bitrix\Sender\EO_PostingRead getPostingRead()
	 * @method \Bitrix\Sender\EO_PostingRead remindActualPostingRead()
	 * @method \Bitrix\Sender\EO_PostingRead requirePostingRead()
	 * @method \Bitrix\Sender\EO_PostingRecipient setPostingRead(\Bitrix\Sender\EO_PostingRead $object)
	 * @method \Bitrix\Sender\EO_PostingRecipient resetPostingRead()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetPostingRead()
	 * @method bool hasPostingRead()
	 * @method bool isPostingReadFilled()
	 * @method bool isPostingReadChanged()
	 * @method \Bitrix\Sender\EO_PostingRead fillPostingRead()
	 * @method \Bitrix\Sender\EO_PostingClick getPostingClick()
	 * @method \Bitrix\Sender\EO_PostingClick remindActualPostingClick()
	 * @method \Bitrix\Sender\EO_PostingClick requirePostingClick()
	 * @method \Bitrix\Sender\EO_PostingRecipient setPostingClick(\Bitrix\Sender\EO_PostingClick $object)
	 * @method \Bitrix\Sender\EO_PostingRecipient resetPostingClick()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetPostingClick()
	 * @method bool hasPostingClick()
	 * @method bool isPostingClickFilled()
	 * @method bool isPostingClickChanged()
	 * @method \Bitrix\Sender\EO_PostingClick fillPostingClick()
	 * @method \Bitrix\Sender\EO_PostingUnsub getPostingUnsub()
	 * @method \Bitrix\Sender\EO_PostingUnsub remindActualPostingUnsub()
	 * @method \Bitrix\Sender\EO_PostingUnsub requirePostingUnsub()
	 * @method \Bitrix\Sender\EO_PostingRecipient setPostingUnsub(\Bitrix\Sender\EO_PostingUnsub $object)
	 * @method \Bitrix\Sender\EO_PostingRecipient resetPostingUnsub()
	 * @method \Bitrix\Sender\EO_PostingRecipient unsetPostingUnsub()
	 * @method bool hasPostingUnsub()
	 * @method bool isPostingUnsubFilled()
	 * @method bool isPostingUnsubChanged()
	 * @method \Bitrix\Sender\EO_PostingUnsub fillPostingUnsub()
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
	 * @method \Bitrix\Sender\EO_PostingRecipient set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_PostingRecipient reset($fieldName)
	 * @method \Bitrix\Sender\EO_PostingRecipient unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_PostingRecipient wakeUp($data)
	 */
	class EO_PostingRecipient {
		/* @var \Bitrix\Sender\PostingRecipientTable */
		static public $dataClass = '\Bitrix\Sender\PostingRecipientTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_PostingRecipient_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostingIdList()
	 * @method \string[] getStatusList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateSentList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateSent()
	 * @method \Bitrix\Main\Type\DateTime[] getDateDenyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateDeny()
	 * @method \int[] getContactIdList()
	 * @method \int[] fillContactId()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getFieldsList()
	 * @method \string[] fillFields()
	 * @method \int[] getRootIdList()
	 * @method \int[] fillRootId()
	 * @method \string[] getIsReadList()
	 * @method \string[] fillIsRead()
	 * @method \string[] getIsClickList()
	 * @method \string[] fillIsClick()
	 * @method \string[] getIsUnsubList()
	 * @method \string[] fillIsUnsub()
	 * @method \Bitrix\Sender\EO_Contact[] getContactList()
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection getContactCollection()
	 * @method \Bitrix\Sender\EO_Contact_Collection fillContact()
	 * @method \Bitrix\Sender\EO_Posting[] getPostingList()
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection getPostingCollection()
	 * @method \Bitrix\Sender\EO_Posting_Collection fillPosting()
	 * @method \Bitrix\Sender\EO_PostingRead[] getPostingReadList()
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection getPostingReadCollection()
	 * @method \Bitrix\Sender\EO_PostingRead_Collection fillPostingRead()
	 * @method \Bitrix\Sender\EO_PostingClick[] getPostingClickList()
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection getPostingClickCollection()
	 * @method \Bitrix\Sender\EO_PostingClick_Collection fillPostingClick()
	 * @method \Bitrix\Sender\EO_PostingUnsub[] getPostingUnsubList()
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection getPostingUnsubCollection()
	 * @method \Bitrix\Sender\EO_PostingUnsub_Collection fillPostingUnsub()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_PostingRecipient $object)
	 * @method bool has(\Bitrix\Sender\EO_PostingRecipient $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_PostingRecipient getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_PostingRecipient[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_PostingRecipient $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_PostingRecipient_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_PostingRecipient current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PostingRecipient_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\PostingRecipientTable */
		static public $dataClass = '\Bitrix\Sender\PostingRecipientTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PostingRecipient_Result exec()
	 * @method \Bitrix\Sender\EO_PostingRecipient fetchObject()
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PostingRecipient_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_PostingRecipient fetchObject()
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection fetchCollection()
	 */
	class EO_PostingRecipient_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_PostingRecipient createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection createCollection()
	 * @method \Bitrix\Sender\EO_PostingRecipient wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_PostingRecipient_Collection wakeUpCollection($rows)
	 */
	class EO_PostingRecipient_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\SegmentDataTable:sender/lib/segmentdata.php:1fa939b9fc4452c6fea62929b9b0c98e */
namespace Bitrix\Sender {
	/**
	 * EO_SegmentData
	 * @see \Bitrix\Sender\SegmentDataTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_SegmentData setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getGroupId()
	 * @method \Bitrix\Sender\EO_SegmentData setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Sender\EO_SegmentData resetGroupId()
	 * @method \Bitrix\Sender\EO_SegmentData unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_SegmentData setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_SegmentData resetDateInsert()
	 * @method \Bitrix\Sender\EO_SegmentData unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \int getCrmEntityId()
	 * @method \Bitrix\Sender\EO_SegmentData setCrmEntityId(\int|\Bitrix\Main\DB\SqlExpression $crmEntityId)
	 * @method bool hasCrmEntityId()
	 * @method bool isCrmEntityIdFilled()
	 * @method bool isCrmEntityIdChanged()
	 * @method \int remindActualCrmEntityId()
	 * @method \int requireCrmEntityId()
	 * @method \Bitrix\Sender\EO_SegmentData resetCrmEntityId()
	 * @method \Bitrix\Sender\EO_SegmentData unsetCrmEntityId()
	 * @method \int fillCrmEntityId()
	 * @method \string getFilterId()
	 * @method \Bitrix\Sender\EO_SegmentData setFilterId(\string|\Bitrix\Main\DB\SqlExpression $filterId)
	 * @method bool hasFilterId()
	 * @method bool isFilterIdFilled()
	 * @method bool isFilterIdChanged()
	 * @method \string remindActualFilterId()
	 * @method \string requireFilterId()
	 * @method \Bitrix\Sender\EO_SegmentData resetFilterId()
	 * @method \Bitrix\Sender\EO_SegmentData unsetFilterId()
	 * @method \string fillFilterId()
	 * @method \string getName()
	 * @method \Bitrix\Sender\EO_SegmentData setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\EO_SegmentData resetName()
	 * @method \Bitrix\Sender\EO_SegmentData unsetName()
	 * @method \string fillName()
	 * @method \string getCrmEntityType()
	 * @method \Bitrix\Sender\EO_SegmentData setCrmEntityType(\string|\Bitrix\Main\DB\SqlExpression $crmEntityType)
	 * @method bool hasCrmEntityType()
	 * @method bool isCrmEntityTypeFilled()
	 * @method bool isCrmEntityTypeChanged()
	 * @method \string remindActualCrmEntityType()
	 * @method \string requireCrmEntityType()
	 * @method \Bitrix\Sender\EO_SegmentData resetCrmEntityType()
	 * @method \Bitrix\Sender\EO_SegmentData unsetCrmEntityType()
	 * @method \string fillCrmEntityType()
	 * @method \int getCrmEntityTypeId()
	 * @method \Bitrix\Sender\EO_SegmentData setCrmEntityTypeId(\int|\Bitrix\Main\DB\SqlExpression $crmEntityTypeId)
	 * @method bool hasCrmEntityTypeId()
	 * @method bool isCrmEntityTypeIdFilled()
	 * @method bool isCrmEntityTypeIdChanged()
	 * @method \int remindActualCrmEntityTypeId()
	 * @method \int requireCrmEntityTypeId()
	 * @method \Bitrix\Sender\EO_SegmentData resetCrmEntityTypeId()
	 * @method \Bitrix\Sender\EO_SegmentData unsetCrmEntityTypeId()
	 * @method \int fillCrmEntityTypeId()
	 * @method \int getContactId()
	 * @method \Bitrix\Sender\EO_SegmentData setContactId(\int|\Bitrix\Main\DB\SqlExpression $contactId)
	 * @method bool hasContactId()
	 * @method bool isContactIdFilled()
	 * @method bool isContactIdChanged()
	 * @method \int remindActualContactId()
	 * @method \int requireContactId()
	 * @method \Bitrix\Sender\EO_SegmentData resetContactId()
	 * @method \Bitrix\Sender\EO_SegmentData unsetContactId()
	 * @method \int fillContactId()
	 * @method \int getCompanyId()
	 * @method \Bitrix\Sender\EO_SegmentData setCompanyId(\int|\Bitrix\Main\DB\SqlExpression $companyId)
	 * @method bool hasCompanyId()
	 * @method bool isCompanyIdFilled()
	 * @method bool isCompanyIdChanged()
	 * @method \int remindActualCompanyId()
	 * @method \int requireCompanyId()
	 * @method \Bitrix\Sender\EO_SegmentData resetCompanyId()
	 * @method \Bitrix\Sender\EO_SegmentData unsetCompanyId()
	 * @method \int fillCompanyId()
	 * @method \string getEmail()
	 * @method \Bitrix\Sender\EO_SegmentData setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Sender\EO_SegmentData resetEmail()
	 * @method \Bitrix\Sender\EO_SegmentData unsetEmail()
	 * @method \string fillEmail()
	 * @method \string getIm()
	 * @method \Bitrix\Sender\EO_SegmentData setIm(\string|\Bitrix\Main\DB\SqlExpression $im)
	 * @method bool hasIm()
	 * @method bool isImFilled()
	 * @method bool isImChanged()
	 * @method \string remindActualIm()
	 * @method \string requireIm()
	 * @method \Bitrix\Sender\EO_SegmentData resetIm()
	 * @method \Bitrix\Sender\EO_SegmentData unsetIm()
	 * @method \string fillIm()
	 * @method \string getPhone()
	 * @method \Bitrix\Sender\EO_SegmentData setPhone(\string|\Bitrix\Main\DB\SqlExpression $phone)
	 * @method bool hasPhone()
	 * @method bool isPhoneFilled()
	 * @method bool isPhoneChanged()
	 * @method \string remindActualPhone()
	 * @method \string requirePhone()
	 * @method \Bitrix\Sender\EO_SegmentData resetPhone()
	 * @method \Bitrix\Sender\EO_SegmentData unsetPhone()
	 * @method \string fillPhone()
	 * @method \string getHasEmail()
	 * @method \Bitrix\Sender\EO_SegmentData setHasEmail(\string|\Bitrix\Main\DB\SqlExpression $hasEmail)
	 * @method bool hasHasEmail()
	 * @method bool isHasEmailFilled()
	 * @method bool isHasEmailChanged()
	 * @method \string remindActualHasEmail()
	 * @method \string requireHasEmail()
	 * @method \Bitrix\Sender\EO_SegmentData resetHasEmail()
	 * @method \Bitrix\Sender\EO_SegmentData unsetHasEmail()
	 * @method \string fillHasEmail()
	 * @method \string getHasImol()
	 * @method \Bitrix\Sender\EO_SegmentData setHasImol(\string|\Bitrix\Main\DB\SqlExpression $hasImol)
	 * @method bool hasHasImol()
	 * @method bool isHasImolFilled()
	 * @method bool isHasImolChanged()
	 * @method \string remindActualHasImol()
	 * @method \string requireHasImol()
	 * @method \Bitrix\Sender\EO_SegmentData resetHasImol()
	 * @method \Bitrix\Sender\EO_SegmentData unsetHasImol()
	 * @method \string fillHasImol()
	 * @method \string getHasPhone()
	 * @method \Bitrix\Sender\EO_SegmentData setHasPhone(\string|\Bitrix\Main\DB\SqlExpression $hasPhone)
	 * @method bool hasHasPhone()
	 * @method bool isHasPhoneFilled()
	 * @method bool isHasPhoneChanged()
	 * @method \string remindActualHasPhone()
	 * @method \string requireHasPhone()
	 * @method \Bitrix\Sender\EO_SegmentData resetHasPhone()
	 * @method \Bitrix\Sender\EO_SegmentData unsetHasPhone()
	 * @method \string fillHasPhone()
	 * @method \Bitrix\Sender\EO_Group getGroup()
	 * @method \Bitrix\Sender\EO_Group remindActualGroup()
	 * @method \Bitrix\Sender\EO_Group requireGroup()
	 * @method \Bitrix\Sender\EO_SegmentData setGroup(\Bitrix\Sender\EO_Group $object)
	 * @method \Bitrix\Sender\EO_SegmentData resetGroup()
	 * @method \Bitrix\Sender\EO_SegmentData unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Sender\EO_Group fillGroup()
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
	 * @method \Bitrix\Sender\EO_SegmentData set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_SegmentData reset($fieldName)
	 * @method \Bitrix\Sender\EO_SegmentData unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_SegmentData wakeUp($data)
	 */
	class EO_SegmentData {
		/* @var \Bitrix\Sender\SegmentDataTable */
		static public $dataClass = '\Bitrix\Sender\SegmentDataTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_SegmentData_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \int[] getCrmEntityIdList()
	 * @method \int[] fillCrmEntityId()
	 * @method \string[] getFilterIdList()
	 * @method \string[] fillFilterId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getCrmEntityTypeList()
	 * @method \string[] fillCrmEntityType()
	 * @method \int[] getCrmEntityTypeIdList()
	 * @method \int[] fillCrmEntityTypeId()
	 * @method \int[] getContactIdList()
	 * @method \int[] fillContactId()
	 * @method \int[] getCompanyIdList()
	 * @method \int[] fillCompanyId()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \string[] getImList()
	 * @method \string[] fillIm()
	 * @method \string[] getPhoneList()
	 * @method \string[] fillPhone()
	 * @method \string[] getHasEmailList()
	 * @method \string[] fillHasEmail()
	 * @method \string[] getHasImolList()
	 * @method \string[] fillHasImol()
	 * @method \string[] getHasPhoneList()
	 * @method \string[] fillHasPhone()
	 * @method \Bitrix\Sender\EO_Group[] getGroupList()
	 * @method \Bitrix\Sender\EO_SegmentData_Collection getGroupCollection()
	 * @method \Bitrix\Sender\EO_Group_Collection fillGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_SegmentData $object)
	 * @method bool has(\Bitrix\Sender\EO_SegmentData $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_SegmentData getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_SegmentData[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_SegmentData $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_SegmentData_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_SegmentData current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SegmentData_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\SegmentDataTable */
		static public $dataClass = '\Bitrix\Sender\SegmentDataTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SegmentData_Result exec()
	 * @method \Bitrix\Sender\EO_SegmentData fetchObject()
	 * @method \Bitrix\Sender\EO_SegmentData_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SegmentData_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_SegmentData fetchObject()
	 * @method \Bitrix\Sender\EO_SegmentData_Collection fetchCollection()
	 */
	class EO_SegmentData_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_SegmentData createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_SegmentData_Collection createCollection()
	 * @method \Bitrix\Sender\EO_SegmentData wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_SegmentData_Collection wakeUpCollection($rows)
	 */
	class EO_SegmentData_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\TemplateTable:sender/lib/template.php:8327a7dad0ccceddf675001f494c4dec */
namespace Bitrix\Sender {
	/**
	 * EO_Template
	 * @see \Bitrix\Sender\TemplateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_Template setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getActive()
	 * @method \Bitrix\Sender\EO_Template setActive(\string|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \string remindActualActive()
	 * @method \string requireActive()
	 * @method \Bitrix\Sender\EO_Template resetActive()
	 * @method \Bitrix\Sender\EO_Template unsetActive()
	 * @method \string fillActive()
	 * @method \string getName()
	 * @method \Bitrix\Sender\EO_Template setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Sender\EO_Template resetName()
	 * @method \Bitrix\Sender\EO_Template unsetName()
	 * @method \string fillName()
	 * @method \string getContent()
	 * @method \Bitrix\Sender\EO_Template setContent(\string|\Bitrix\Main\DB\SqlExpression $content)
	 * @method bool hasContent()
	 * @method bool isContentFilled()
	 * @method bool isContentChanged()
	 * @method \string remindActualContent()
	 * @method \string requireContent()
	 * @method \Bitrix\Sender\EO_Template resetContent()
	 * @method \Bitrix\Sender\EO_Template unsetContent()
	 * @method \string fillContent()
	 * @method \int getUseCount()
	 * @method \Bitrix\Sender\EO_Template setUseCount(\int|\Bitrix\Main\DB\SqlExpression $useCount)
	 * @method bool hasUseCount()
	 * @method bool isUseCountFilled()
	 * @method bool isUseCountChanged()
	 * @method \int remindActualUseCount()
	 * @method \int requireUseCount()
	 * @method \Bitrix\Sender\EO_Template resetUseCount()
	 * @method \Bitrix\Sender\EO_Template unsetUseCount()
	 * @method \int fillUseCount()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_Template setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_Template resetDateInsert()
	 * @method \Bitrix\Sender\EO_Template unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime getDateUse()
	 * @method \Bitrix\Sender\EO_Template setDateUse(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUse)
	 * @method bool hasDateUse()
	 * @method bool isDateUseFilled()
	 * @method bool isDateUseChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateUse()
	 * @method \Bitrix\Main\Type\DateTime requireDateUse()
	 * @method \Bitrix\Sender\EO_Template resetDateUse()
	 * @method \Bitrix\Sender\EO_Template unsetDateUse()
	 * @method \Bitrix\Main\Type\DateTime fillDateUse()
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
	 * @method \Bitrix\Sender\EO_Template set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_Template reset($fieldName)
	 * @method \Bitrix\Sender\EO_Template unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_Template wakeUp($data)
	 */
	class EO_Template {
		/* @var \Bitrix\Sender\TemplateTable */
		static public $dataClass = '\Bitrix\Sender\TemplateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_Template_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getActiveList()
	 * @method \string[] fillActive()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getContentList()
	 * @method \string[] fillContent()
	 * @method \int[] getUseCountList()
	 * @method \int[] fillUseCount()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \Bitrix\Main\Type\DateTime[] getDateUseList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateUse()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_Template $object)
	 * @method bool has(\Bitrix\Sender\EO_Template $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Template getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_Template[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_Template $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_Template_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_Template current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Template_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\TemplateTable */
		static public $dataClass = '\Bitrix\Sender\TemplateTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Template_Result exec()
	 * @method \Bitrix\Sender\EO_Template fetchObject()
	 * @method \Bitrix\Sender\EO_Template_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Template_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_Template fetchObject()
	 * @method \Bitrix\Sender\EO_Template_Collection fetchCollection()
	 */
	class EO_Template_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_Template createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_Template_Collection createCollection()
	 * @method \Bitrix\Sender\EO_Template wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_Template_Collection wakeUpCollection($rows)
	 */
	class EO_Template_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Sender\TimeLineQueueTable:sender/lib/timelinequeue.php:7a32758da1c8e24b0c1f11f28a168b1f */
namespace Bitrix\Sender {
	/**
	 * EO_TimeLineQueue
	 * @see \Bitrix\Sender\TimeLineQueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostingId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue setPostingId(\int|\Bitrix\Main\DB\SqlExpression $postingId)
	 * @method bool hasPostingId()
	 * @method bool isPostingIdFilled()
	 * @method bool isPostingIdChanged()
	 * @method \int remindActualPostingId()
	 * @method \int requirePostingId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue resetPostingId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue unsetPostingId()
	 * @method \int fillPostingId()
	 * @method \int getEntityId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue resetEntityId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \int getRecipientId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue setRecipientId(\int|\Bitrix\Main\DB\SqlExpression $recipientId)
	 * @method bool hasRecipientId()
	 * @method bool isRecipientIdFilled()
	 * @method bool isRecipientIdChanged()
	 * @method \int remindActualRecipientId()
	 * @method \int requireRecipientId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue resetRecipientId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue unsetRecipientId()
	 * @method \int fillRecipientId()
	 * @method \int getContactTypeId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue setContactTypeId(\int|\Bitrix\Main\DB\SqlExpression $contactTypeId)
	 * @method bool hasContactTypeId()
	 * @method bool isContactTypeIdFilled()
	 * @method bool isContactTypeIdChanged()
	 * @method \int remindActualContactTypeId()
	 * @method \int requireContactTypeId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue resetContactTypeId()
	 * @method \Bitrix\Sender\EO_TimeLineQueue unsetContactTypeId()
	 * @method \int fillContactTypeId()
	 * @method \string getContactCode()
	 * @method \Bitrix\Sender\EO_TimeLineQueue setContactCode(\string|\Bitrix\Main\DB\SqlExpression $contactCode)
	 * @method bool hasContactCode()
	 * @method bool isContactCodeFilled()
	 * @method bool isContactCodeChanged()
	 * @method \string remindActualContactCode()
	 * @method \string requireContactCode()
	 * @method \Bitrix\Sender\EO_TimeLineQueue resetContactCode()
	 * @method \Bitrix\Sender\EO_TimeLineQueue unsetContactCode()
	 * @method \string fillContactCode()
	 * @method \string getFields()
	 * @method \Bitrix\Sender\EO_TimeLineQueue setFields(\string|\Bitrix\Main\DB\SqlExpression $fields)
	 * @method bool hasFields()
	 * @method bool isFieldsFilled()
	 * @method bool isFieldsChanged()
	 * @method \string remindActualFields()
	 * @method \string requireFields()
	 * @method \Bitrix\Sender\EO_TimeLineQueue resetFields()
	 * @method \Bitrix\Sender\EO_TimeLineQueue unsetFields()
	 * @method \string fillFields()
	 * @method \Bitrix\Main\Type\DateTime getDateInsert()
	 * @method \Bitrix\Sender\EO_TimeLineQueue setDateInsert(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateInsert)
	 * @method bool hasDateInsert()
	 * @method bool isDateInsertFilled()
	 * @method bool isDateInsertChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateInsert()
	 * @method \Bitrix\Main\Type\DateTime requireDateInsert()
	 * @method \Bitrix\Sender\EO_TimeLineQueue resetDateInsert()
	 * @method \Bitrix\Sender\EO_TimeLineQueue unsetDateInsert()
	 * @method \Bitrix\Main\Type\DateTime fillDateInsert()
	 * @method \string getStatus()
	 * @method \Bitrix\Sender\EO_TimeLineQueue setStatus(\string|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \string remindActualStatus()
	 * @method \string requireStatus()
	 * @method \Bitrix\Sender\EO_TimeLineQueue resetStatus()
	 * @method \Bitrix\Sender\EO_TimeLineQueue unsetStatus()
	 * @method \string fillStatus()
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
	 * @method \Bitrix\Sender\EO_TimeLineQueue set($fieldName, $value)
	 * @method \Bitrix\Sender\EO_TimeLineQueue reset($fieldName)
	 * @method \Bitrix\Sender\EO_TimeLineQueue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Sender\EO_TimeLineQueue wakeUp($data)
	 */
	class EO_TimeLineQueue {
		/* @var \Bitrix\Sender\TimeLineQueueTable */
		static public $dataClass = '\Bitrix\Sender\TimeLineQueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Sender {
	/**
	 * EO_TimeLineQueue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostingIdList()
	 * @method \int[] fillPostingId()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \int[] getRecipientIdList()
	 * @method \int[] fillRecipientId()
	 * @method \int[] getContactTypeIdList()
	 * @method \int[] fillContactTypeId()
	 * @method \string[] getContactCodeList()
	 * @method \string[] fillContactCode()
	 * @method \string[] getFieldsList()
	 * @method \string[] fillFields()
	 * @method \Bitrix\Main\Type\DateTime[] getDateInsertList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateInsert()
	 * @method \string[] getStatusList()
	 * @method \string[] fillStatus()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Sender\EO_TimeLineQueue $object)
	 * @method bool has(\Bitrix\Sender\EO_TimeLineQueue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Sender\EO_TimeLineQueue getByPrimary($primary)
	 * @method \Bitrix\Sender\EO_TimeLineQueue[] getAll()
	 * @method bool remove(\Bitrix\Sender\EO_TimeLineQueue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Sender\EO_TimeLineQueue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Sender\EO_TimeLineQueue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_TimeLineQueue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Sender\TimeLineQueueTable */
		static public $dataClass = '\Bitrix\Sender\TimeLineQueueTable';
	}
}
namespace Bitrix\Sender {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_TimeLineQueue_Result exec()
	 * @method \Bitrix\Sender\EO_TimeLineQueue fetchObject()
	 * @method \Bitrix\Sender\EO_TimeLineQueue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_TimeLineQueue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Sender\EO_TimeLineQueue fetchObject()
	 * @method \Bitrix\Sender\EO_TimeLineQueue_Collection fetchCollection()
	 */
	class EO_TimeLineQueue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Sender\EO_TimeLineQueue createObject($setDefaultValues = true)
	 * @method \Bitrix\Sender\EO_TimeLineQueue_Collection createCollection()
	 * @method \Bitrix\Sender\EO_TimeLineQueue wakeUpObject($row)
	 * @method \Bitrix\Sender\EO_TimeLineQueue_Collection wakeUpCollection($rows)
	 */
	class EO_TimeLineQueue_Entity extends \Bitrix\Main\ORM\Entity {}
}