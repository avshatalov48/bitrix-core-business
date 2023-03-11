<?php

/* ORMENTITYANNOTATION:Bitrix\Catalog\Access\Permission\PermissionTable:catalog\lib\Access\Permission\PermissionTable.php */
namespace Bitrix\Catalog\Access\Permission {
	/**
	 * Permission
	 * @see \Bitrix\Catalog\Access\Permission\PermissionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\Access\Permission\Permission setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getRoleId()
	 * @method \Bitrix\Catalog\Access\Permission\Permission setRoleId(\int|\Bitrix\Main\DB\SqlExpression $roleId)
	 * @method bool hasRoleId()
	 * @method bool isRoleIdFilled()
	 * @method bool isRoleIdChanged()
	 * @method \int remindActualRoleId()
	 * @method \int requireRoleId()
	 * @method \Bitrix\Catalog\Access\Permission\Permission resetRoleId()
	 * @method \Bitrix\Catalog\Access\Permission\Permission unsetRoleId()
	 * @method \int fillRoleId()
	 * @method \string getPermissionId()
	 * @method \Bitrix\Catalog\Access\Permission\Permission setPermissionId(\string|\Bitrix\Main\DB\SqlExpression $permissionId)
	 * @method bool hasPermissionId()
	 * @method bool isPermissionIdFilled()
	 * @method bool isPermissionIdChanged()
	 * @method \string remindActualPermissionId()
	 * @method \string requirePermissionId()
	 * @method \Bitrix\Catalog\Access\Permission\Permission resetPermissionId()
	 * @method \Bitrix\Catalog\Access\Permission\Permission unsetPermissionId()
	 * @method \string fillPermissionId()
	 * @method \int getValue()
	 * @method \Bitrix\Catalog\Access\Permission\Permission setValue(\int|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \int remindActualValue()
	 * @method \int requireValue()
	 * @method \Bitrix\Catalog\Access\Permission\Permission resetValue()
	 * @method \Bitrix\Catalog\Access\Permission\Permission unsetValue()
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
	 * @method \Bitrix\Catalog\Access\Permission\Permission set($fieldName, $value)
	 * @method \Bitrix\Catalog\Access\Permission\Permission reset($fieldName)
	 * @method \Bitrix\Catalog\Access\Permission\Permission unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\Access\Permission\Permission wakeUp($data)
	 */
	class EO_Permission {
		/* @var \Bitrix\Catalog\Access\Permission\PermissionTable */
		static public $dataClass = '\Bitrix\Catalog\Access\Permission\PermissionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog\Access\Permission {
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
	 * @method void add(\Bitrix\Catalog\Access\Permission\Permission $object)
	 * @method bool has(\Bitrix\Catalog\Access\Permission\Permission $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\Access\Permission\Permission getByPrimary($primary)
	 * @method \Bitrix\Catalog\Access\Permission\Permission[] getAll()
	 * @method bool remove(\Bitrix\Catalog\Access\Permission\Permission $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\Access\Permission\EO_Permission_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\Access\Permission\Permission current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Permission_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\Access\Permission\PermissionTable */
		static public $dataClass = '\Bitrix\Catalog\Access\Permission\PermissionTable';
	}
}
namespace Bitrix\Catalog\Access\Permission {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Permission_Result exec()
	 * @method \Bitrix\Catalog\Access\Permission\Permission fetchObject()
	 * @method \Bitrix\Catalog\Access\Permission\EO_Permission_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Permission_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\Access\Permission\Permission fetchObject()
	 * @method \Bitrix\Catalog\Access\Permission\EO_Permission_Collection fetchCollection()
	 */
	class EO_Permission_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\Access\Permission\Permission createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\Access\Permission\EO_Permission_Collection createCollection()
	 * @method \Bitrix\Catalog\Access\Permission\Permission wakeUpObject($row)
	 * @method \Bitrix\Catalog\Access\Permission\EO_Permission_Collection wakeUpCollection($rows)
	 */
	class EO_Permission_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\Access\Role\RoleTable:catalog\lib\Access\Role\RoleTable.php */
namespace Bitrix\Catalog\Access\Role {
	/**
	 * Role
	 * @see \Bitrix\Catalog\Access\Role\RoleTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\Access\Role\Role setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Catalog\Access\Role\Role setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Catalog\Access\Role\Role resetName()
	 * @method \Bitrix\Catalog\Access\Role\Role unsetName()
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
	 * @method \Bitrix\Catalog\Access\Role\Role set($fieldName, $value)
	 * @method \Bitrix\Catalog\Access\Role\Role reset($fieldName)
	 * @method \Bitrix\Catalog\Access\Role\Role unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\Access\Role\Role wakeUp($data)
	 */
	class EO_Role {
		/* @var \Bitrix\Catalog\Access\Role\RoleTable */
		static public $dataClass = '\Bitrix\Catalog\Access\Role\RoleTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog\Access\Role {
	/**
	 * EO_Role_Collection
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
	 * @method void add(\Bitrix\Catalog\Access\Role\Role $object)
	 * @method bool has(\Bitrix\Catalog\Access\Role\Role $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\Access\Role\Role getByPrimary($primary)
	 * @method \Bitrix\Catalog\Access\Role\Role[] getAll()
	 * @method bool remove(\Bitrix\Catalog\Access\Role\Role $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\Access\Role\EO_Role_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\Access\Role\Role current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Role_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\Access\Role\RoleTable */
		static public $dataClass = '\Bitrix\Catalog\Access\Role\RoleTable';
	}
}
namespace Bitrix\Catalog\Access\Role {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Role_Result exec()
	 * @method \Bitrix\Catalog\Access\Role\Role fetchObject()
	 * @method \Bitrix\Catalog\Access\Role\EO_Role_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Role_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\Access\Role\Role fetchObject()
	 * @method \Bitrix\Catalog\Access\Role\EO_Role_Collection fetchCollection()
	 */
	class EO_Role_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\Access\Role\Role createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\Access\Role\EO_Role_Collection createCollection()
	 * @method \Bitrix\Catalog\Access\Role\Role wakeUpObject($row)
	 * @method \Bitrix\Catalog\Access\Role\EO_Role_Collection wakeUpCollection($rows)
	 */
	class EO_Role_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\Access\Role\RoleRelationTable:catalog\lib\Access\Role\RoleRelationTable.php */
namespace Bitrix\Catalog\Access\Role {
	/**
	 * RoleRelation
	 * @see \Bitrix\Catalog\Access\Role\RoleRelationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getRoleId()
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation setRoleId(\int|\Bitrix\Main\DB\SqlExpression $roleId)
	 * @method bool hasRoleId()
	 * @method bool isRoleIdFilled()
	 * @method bool isRoleIdChanged()
	 * @method \int remindActualRoleId()
	 * @method \int requireRoleId()
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation resetRoleId()
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation unsetRoleId()
	 * @method \int fillRoleId()
	 * @method \string getRelation()
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation setRelation(\string|\Bitrix\Main\DB\SqlExpression $relation)
	 * @method bool hasRelation()
	 * @method bool isRelationFilled()
	 * @method bool isRelationChanged()
	 * @method \string remindActualRelation()
	 * @method \string requireRelation()
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation resetRelation()
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation unsetRelation()
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
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation set($fieldName, $value)
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation reset($fieldName)
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\Access\Role\RoleRelation wakeUp($data)
	 */
	class EO_RoleRelation {
		/* @var \Bitrix\Catalog\Access\Role\RoleRelationTable */
		static public $dataClass = '\Bitrix\Catalog\Access\Role\RoleRelationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog\Access\Role {
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
	 * @method void add(\Bitrix\Catalog\Access\Role\RoleRelation $object)
	 * @method bool has(\Bitrix\Catalog\Access\Role\RoleRelation $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation getByPrimary($primary)
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation[] getAll()
	 * @method bool remove(\Bitrix\Catalog\Access\Role\RoleRelation $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\Access\Role\EO_RoleRelation_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_RoleRelation_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\Access\Role\RoleRelationTable */
		static public $dataClass = '\Bitrix\Catalog\Access\Role\RoleRelationTable';
	}
}
namespace Bitrix\Catalog\Access\Role {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_RoleRelation_Result exec()
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation fetchObject()
	 * @method \Bitrix\Catalog\Access\Role\EO_RoleRelation_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_RoleRelation_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation fetchObject()
	 * @method \Bitrix\Catalog\Access\Role\EO_RoleRelation_Collection fetchCollection()
	 */
	class EO_RoleRelation_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\Access\Role\EO_RoleRelation_Collection createCollection()
	 * @method \Bitrix\Catalog\Access\Role\RoleRelation wakeUpObject($row)
	 * @method \Bitrix\Catalog\Access\Role\EO_RoleRelation_Collection wakeUpCollection($rows)
	 */
	class EO_RoleRelation_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\CatalogIblockTable:catalog\lib\catalogiblock.php */
namespace Bitrix\Catalog {
	/**
	 * EO_CatalogIblock
	 * @see \Bitrix\Catalog\CatalogIblockTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getIblockId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock setIblockId(\int|\Bitrix\Main\DB\SqlExpression $iblockId)
	 * @method bool hasIblockId()
	 * @method bool isIblockIdFilled()
	 * @method bool isIblockIdChanged()
	 * @method \boolean getYandexExport()
	 * @method \Bitrix\Catalog\EO_CatalogIblock setYandexExport(\boolean|\Bitrix\Main\DB\SqlExpression $yandexExport)
	 * @method bool hasYandexExport()
	 * @method bool isYandexExportFilled()
	 * @method bool isYandexExportChanged()
	 * @method \boolean remindActualYandexExport()
	 * @method \boolean requireYandexExport()
	 * @method \Bitrix\Catalog\EO_CatalogIblock resetYandexExport()
	 * @method \Bitrix\Catalog\EO_CatalogIblock unsetYandexExport()
	 * @method \boolean fillYandexExport()
	 * @method \boolean getSubscription()
	 * @method \Bitrix\Catalog\EO_CatalogIblock setSubscription(\boolean|\Bitrix\Main\DB\SqlExpression $subscription)
	 * @method bool hasSubscription()
	 * @method bool isSubscriptionFilled()
	 * @method bool isSubscriptionChanged()
	 * @method \boolean remindActualSubscription()
	 * @method \boolean requireSubscription()
	 * @method \Bitrix\Catalog\EO_CatalogIblock resetSubscription()
	 * @method \Bitrix\Catalog\EO_CatalogIblock unsetSubscription()
	 * @method \boolean fillSubscription()
	 * @method \int getVatId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock setVatId(\int|\Bitrix\Main\DB\SqlExpression $vatId)
	 * @method bool hasVatId()
	 * @method bool isVatIdFilled()
	 * @method bool isVatIdChanged()
	 * @method \int remindActualVatId()
	 * @method \int requireVatId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock resetVatId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock unsetVatId()
	 * @method \int fillVatId()
	 * @method \int getProductIblockId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock setProductIblockId(\int|\Bitrix\Main\DB\SqlExpression $productIblockId)
	 * @method bool hasProductIblockId()
	 * @method bool isProductIblockIdFilled()
	 * @method bool isProductIblockIdChanged()
	 * @method \int remindActualProductIblockId()
	 * @method \int requireProductIblockId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock resetProductIblockId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock unsetProductIblockId()
	 * @method \int fillProductIblockId()
	 * @method \int getSkuPropertyId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock setSkuPropertyId(\int|\Bitrix\Main\DB\SqlExpression $skuPropertyId)
	 * @method bool hasSkuPropertyId()
	 * @method bool isSkuPropertyIdFilled()
	 * @method bool isSkuPropertyIdChanged()
	 * @method \int remindActualSkuPropertyId()
	 * @method \int requireSkuPropertyId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock resetSkuPropertyId()
	 * @method \Bitrix\Catalog\EO_CatalogIblock unsetSkuPropertyId()
	 * @method \int fillSkuPropertyId()
	 * @method \Bitrix\Iblock\Iblock getIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualIblock()
	 * @method \Bitrix\Iblock\Iblock requireIblock()
	 * @method \Bitrix\Catalog\EO_CatalogIblock setIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Catalog\EO_CatalogIblock resetIblock()
	 * @method \Bitrix\Catalog\EO_CatalogIblock unsetIblock()
	 * @method bool hasIblock()
	 * @method bool isIblockFilled()
	 * @method bool isIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillIblock()
	 * @method \Bitrix\Iblock\Iblock getProductIblock()
	 * @method \Bitrix\Iblock\Iblock remindActualProductIblock()
	 * @method \Bitrix\Iblock\Iblock requireProductIblock()
	 * @method \Bitrix\Catalog\EO_CatalogIblock setProductIblock(\Bitrix\Iblock\Iblock $object)
	 * @method \Bitrix\Catalog\EO_CatalogIblock resetProductIblock()
	 * @method \Bitrix\Catalog\EO_CatalogIblock unsetProductIblock()
	 * @method bool hasProductIblock()
	 * @method bool isProductIblockFilled()
	 * @method bool isProductIblockChanged()
	 * @method \Bitrix\Iblock\Iblock fillProductIblock()
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
	 * @method \Bitrix\Catalog\EO_CatalogIblock set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_CatalogIblock reset($fieldName)
	 * @method \Bitrix\Catalog\EO_CatalogIblock unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_CatalogIblock wakeUp($data)
	 */
	class EO_CatalogIblock {
		/* @var \Bitrix\Catalog\CatalogIblockTable */
		static public $dataClass = '\Bitrix\Catalog\CatalogIblockTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_CatalogIblock_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIblockIdList()
	 * @method \boolean[] getYandexExportList()
	 * @method \boolean[] fillYandexExport()
	 * @method \boolean[] getSubscriptionList()
	 * @method \boolean[] fillSubscription()
	 * @method \int[] getVatIdList()
	 * @method \int[] fillVatId()
	 * @method \int[] getProductIblockIdList()
	 * @method \int[] fillProductIblockId()
	 * @method \int[] getSkuPropertyIdList()
	 * @method \int[] fillSkuPropertyId()
	 * @method \Bitrix\Iblock\Iblock[] getIblockList()
	 * @method \Bitrix\Catalog\EO_CatalogIblock_Collection getIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillIblock()
	 * @method \Bitrix\Iblock\Iblock[] getProductIblockList()
	 * @method \Bitrix\Catalog\EO_CatalogIblock_Collection getProductIblockCollection()
	 * @method \Bitrix\Iblock\EO_Iblock_Collection fillProductIblock()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_CatalogIblock $object)
	 * @method bool has(\Bitrix\Catalog\EO_CatalogIblock $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_CatalogIblock getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_CatalogIblock[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_CatalogIblock $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_CatalogIblock_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_CatalogIblock current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_CatalogIblock_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\CatalogIblockTable */
		static public $dataClass = '\Bitrix\Catalog\CatalogIblockTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CatalogIblock_Result exec()
	 * @method \Bitrix\Catalog\EO_CatalogIblock fetchObject()
	 * @method \Bitrix\Catalog\EO_CatalogIblock_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CatalogIblock_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_CatalogIblock fetchObject()
	 * @method \Bitrix\Catalog\EO_CatalogIblock_Collection fetchCollection()
	 */
	class EO_CatalogIblock_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_CatalogIblock createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_CatalogIblock_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_CatalogIblock wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_CatalogIblock_Collection wakeUpCollection($rows)
	 */
	class EO_CatalogIblock_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\CatalogViewedProductTable:catalog\lib\catalogviewedproduct.php */
namespace Bitrix\Catalog {
	/**
	 * EO_CatalogViewedProduct
	 * @see \Bitrix\Catalog\CatalogViewedProductTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getFuserId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setFuserId(\int|\Bitrix\Main\DB\SqlExpression $fuserId)
	 * @method bool hasFuserId()
	 * @method bool isFuserIdFilled()
	 * @method bool isFuserIdChanged()
	 * @method \int remindActualFuserId()
	 * @method \int requireFuserId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetFuserId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetFuserId()
	 * @method \int fillFuserId()
	 * @method \Bitrix\Main\Type\DateTime getDateVisit()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setDateVisit(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateVisit)
	 * @method bool hasDateVisit()
	 * @method bool isDateVisitFilled()
	 * @method bool isDateVisitChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateVisit()
	 * @method \Bitrix\Main\Type\DateTime requireDateVisit()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetDateVisit()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetDateVisit()
	 * @method \Bitrix\Main\Type\DateTime fillDateVisit()
	 * @method \int getProductId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setProductId(\int|\Bitrix\Main\DB\SqlExpression $productId)
	 * @method bool hasProductId()
	 * @method bool isProductIdFilled()
	 * @method bool isProductIdChanged()
	 * @method \int remindActualProductId()
	 * @method \int requireProductId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetProductId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetProductId()
	 * @method \int fillProductId()
	 * @method \int getElementId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setElementId(\int|\Bitrix\Main\DB\SqlExpression $elementId)
	 * @method bool hasElementId()
	 * @method bool isElementIdFilled()
	 * @method bool isElementIdChanged()
	 * @method \int remindActualElementId()
	 * @method \int requireElementId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetElementId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetElementId()
	 * @method \int fillElementId()
	 * @method \string getSiteId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetSiteId()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \int getViewCount()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setViewCount(\int|\Bitrix\Main\DB\SqlExpression $viewCount)
	 * @method bool hasViewCount()
	 * @method bool isViewCountFilled()
	 * @method bool isViewCountChanged()
	 * @method \int remindActualViewCount()
	 * @method \int requireViewCount()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetViewCount()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetViewCount()
	 * @method \int fillViewCount()
	 * @method \string getRecommendation()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setRecommendation(\string|\Bitrix\Main\DB\SqlExpression $recommendation)
	 * @method bool hasRecommendation()
	 * @method bool isRecommendationFilled()
	 * @method bool isRecommendationChanged()
	 * @method \string remindActualRecommendation()
	 * @method \string requireRecommendation()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetRecommendation()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetRecommendation()
	 * @method \string fillRecommendation()
	 * @method \Bitrix\Iblock\EO_Element getElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualElement()
	 * @method \Bitrix\Iblock\EO_Element requireElement()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetElement()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetElement()
	 * @method bool hasElement()
	 * @method bool isElementFilled()
	 * @method bool isElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillElement()
	 * @method \Bitrix\Sale\Internals\EO_Product getProduct()
	 * @method \Bitrix\Sale\Internals\EO_Product remindActualProduct()
	 * @method \Bitrix\Sale\Internals\EO_Product requireProduct()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setProduct(\Bitrix\Sale\Internals\EO_Product $object)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetProduct()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetProduct()
	 * @method bool hasProduct()
	 * @method bool isProductFilled()
	 * @method bool isProductChanged()
	 * @method \Bitrix\Sale\Internals\EO_Product fillProduct()
	 * @method \Bitrix\Iblock\EO_Element getParentElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualParentElement()
	 * @method \Bitrix\Iblock\EO_Element requireParentElement()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setParentElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetParentElement()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetParentElement()
	 * @method bool hasParentElement()
	 * @method bool isParentElementFilled()
	 * @method bool isParentElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillParentElement()
	 * @method \Bitrix\Sale\Internals\EO_Fuser getFuser()
	 * @method \Bitrix\Sale\Internals\EO_Fuser remindActualFuser()
	 * @method \Bitrix\Sale\Internals\EO_Fuser requireFuser()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct setFuser(\Bitrix\Sale\Internals\EO_Fuser $object)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct resetFuser()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unsetFuser()
	 * @method bool hasFuser()
	 * @method bool isFuserFilled()
	 * @method bool isFuserChanged()
	 * @method \Bitrix\Sale\Internals\EO_Fuser fillFuser()
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
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct reset($fieldName)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_CatalogViewedProduct wakeUp($data)
	 */
	class EO_CatalogViewedProduct {
		/* @var \Bitrix\Catalog\CatalogViewedProductTable */
		static public $dataClass = '\Bitrix\Catalog\CatalogViewedProductTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_CatalogViewedProduct_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getFuserIdList()
	 * @method \int[] fillFuserId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateVisitList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateVisit()
	 * @method \int[] getProductIdList()
	 * @method \int[] fillProductId()
	 * @method \int[] getElementIdList()
	 * @method \int[] fillElementId()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \int[] getViewCountList()
	 * @method \int[] fillViewCount()
	 * @method \string[] getRecommendationList()
	 * @method \string[] fillRecommendation()
	 * @method \Bitrix\Iblock\EO_Element[] getElementList()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct_Collection getElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillElement()
	 * @method \Bitrix\Sale\Internals\EO_Product[] getProductList()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct_Collection getProductCollection()
	 * @method \Bitrix\Sale\Internals\EO_Product_Collection fillProduct()
	 * @method \Bitrix\Iblock\EO_Element[] getParentElementList()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct_Collection getParentElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillParentElement()
	 * @method \Bitrix\Sale\Internals\EO_Fuser[] getFuserList()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct_Collection getFuserCollection()
	 * @method \Bitrix\Sale\Internals\EO_Fuser_Collection fillFuser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_CatalogViewedProduct $object)
	 * @method bool has(\Bitrix\Catalog\EO_CatalogViewedProduct $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_CatalogViewedProduct $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_CatalogViewedProduct_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_CatalogViewedProduct_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\CatalogViewedProductTable */
		static public $dataClass = '\Bitrix\Catalog\CatalogViewedProductTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_CatalogViewedProduct_Result exec()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct fetchObject()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_CatalogViewedProduct_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct fetchObject()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct_Collection fetchCollection()
	 */
	class EO_CatalogViewedProduct_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_CatalogViewedProduct_Collection wakeUpCollection($rows)
	 */
	class EO_CatalogViewedProduct_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\ContractorTable:catalog\lib\contractortable.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Contractor
	 * @see \Bitrix\Catalog\ContractorTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Contractor setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getPersonType()
	 * @method \Bitrix\Catalog\EO_Contractor setPersonType(\string|\Bitrix\Main\DB\SqlExpression $personType)
	 * @method bool hasPersonType()
	 * @method bool isPersonTypeFilled()
	 * @method bool isPersonTypeChanged()
	 * @method \string remindActualPersonType()
	 * @method \string requirePersonType()
	 * @method \Bitrix\Catalog\EO_Contractor resetPersonType()
	 * @method \Bitrix\Catalog\EO_Contractor unsetPersonType()
	 * @method \string fillPersonType()
	 * @method \string getPersonName()
	 * @method \Bitrix\Catalog\EO_Contractor setPersonName(\string|\Bitrix\Main\DB\SqlExpression $personName)
	 * @method bool hasPersonName()
	 * @method bool isPersonNameFilled()
	 * @method bool isPersonNameChanged()
	 * @method \string remindActualPersonName()
	 * @method \string requirePersonName()
	 * @method \Bitrix\Catalog\EO_Contractor resetPersonName()
	 * @method \Bitrix\Catalog\EO_Contractor unsetPersonName()
	 * @method \string fillPersonName()
	 * @method \string getPersonLastname()
	 * @method \Bitrix\Catalog\EO_Contractor setPersonLastname(\string|\Bitrix\Main\DB\SqlExpression $personLastname)
	 * @method bool hasPersonLastname()
	 * @method bool isPersonLastnameFilled()
	 * @method bool isPersonLastnameChanged()
	 * @method \string remindActualPersonLastname()
	 * @method \string requirePersonLastname()
	 * @method \Bitrix\Catalog\EO_Contractor resetPersonLastname()
	 * @method \Bitrix\Catalog\EO_Contractor unsetPersonLastname()
	 * @method \string fillPersonLastname()
	 * @method \string getPersonMiddlename()
	 * @method \Bitrix\Catalog\EO_Contractor setPersonMiddlename(\string|\Bitrix\Main\DB\SqlExpression $personMiddlename)
	 * @method bool hasPersonMiddlename()
	 * @method bool isPersonMiddlenameFilled()
	 * @method bool isPersonMiddlenameChanged()
	 * @method \string remindActualPersonMiddlename()
	 * @method \string requirePersonMiddlename()
	 * @method \Bitrix\Catalog\EO_Contractor resetPersonMiddlename()
	 * @method \Bitrix\Catalog\EO_Contractor unsetPersonMiddlename()
	 * @method \string fillPersonMiddlename()
	 * @method \string getEmail()
	 * @method \Bitrix\Catalog\EO_Contractor setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Catalog\EO_Contractor resetEmail()
	 * @method \Bitrix\Catalog\EO_Contractor unsetEmail()
	 * @method \string fillEmail()
	 * @method \string getPhone()
	 * @method \Bitrix\Catalog\EO_Contractor setPhone(\string|\Bitrix\Main\DB\SqlExpression $phone)
	 * @method bool hasPhone()
	 * @method bool isPhoneFilled()
	 * @method bool isPhoneChanged()
	 * @method \string remindActualPhone()
	 * @method \string requirePhone()
	 * @method \Bitrix\Catalog\EO_Contractor resetPhone()
	 * @method \Bitrix\Catalog\EO_Contractor unsetPhone()
	 * @method \string fillPhone()
	 * @method \string getPostIndex()
	 * @method \Bitrix\Catalog\EO_Contractor setPostIndex(\string|\Bitrix\Main\DB\SqlExpression $postIndex)
	 * @method bool hasPostIndex()
	 * @method bool isPostIndexFilled()
	 * @method bool isPostIndexChanged()
	 * @method \string remindActualPostIndex()
	 * @method \string requirePostIndex()
	 * @method \Bitrix\Catalog\EO_Contractor resetPostIndex()
	 * @method \Bitrix\Catalog\EO_Contractor unsetPostIndex()
	 * @method \string fillPostIndex()
	 * @method \string getCountry()
	 * @method \Bitrix\Catalog\EO_Contractor setCountry(\string|\Bitrix\Main\DB\SqlExpression $country)
	 * @method bool hasCountry()
	 * @method bool isCountryFilled()
	 * @method bool isCountryChanged()
	 * @method \string remindActualCountry()
	 * @method \string requireCountry()
	 * @method \Bitrix\Catalog\EO_Contractor resetCountry()
	 * @method \Bitrix\Catalog\EO_Contractor unsetCountry()
	 * @method \string fillCountry()
	 * @method \string getCity()
	 * @method \Bitrix\Catalog\EO_Contractor setCity(\string|\Bitrix\Main\DB\SqlExpression $city)
	 * @method bool hasCity()
	 * @method bool isCityFilled()
	 * @method bool isCityChanged()
	 * @method \string remindActualCity()
	 * @method \string requireCity()
	 * @method \Bitrix\Catalog\EO_Contractor resetCity()
	 * @method \Bitrix\Catalog\EO_Contractor unsetCity()
	 * @method \string fillCity()
	 * @method \string getCompany()
	 * @method \Bitrix\Catalog\EO_Contractor setCompany(\string|\Bitrix\Main\DB\SqlExpression $company)
	 * @method bool hasCompany()
	 * @method bool isCompanyFilled()
	 * @method bool isCompanyChanged()
	 * @method \string remindActualCompany()
	 * @method \string requireCompany()
	 * @method \Bitrix\Catalog\EO_Contractor resetCompany()
	 * @method \Bitrix\Catalog\EO_Contractor unsetCompany()
	 * @method \string fillCompany()
	 * @method \string getInn()
	 * @method \Bitrix\Catalog\EO_Contractor setInn(\string|\Bitrix\Main\DB\SqlExpression $inn)
	 * @method bool hasInn()
	 * @method bool isInnFilled()
	 * @method bool isInnChanged()
	 * @method \string remindActualInn()
	 * @method \string requireInn()
	 * @method \Bitrix\Catalog\EO_Contractor resetInn()
	 * @method \Bitrix\Catalog\EO_Contractor unsetInn()
	 * @method \string fillInn()
	 * @method \string getKpp()
	 * @method \Bitrix\Catalog\EO_Contractor setKpp(\string|\Bitrix\Main\DB\SqlExpression $kpp)
	 * @method bool hasKpp()
	 * @method bool isKppFilled()
	 * @method bool isKppChanged()
	 * @method \string remindActualKpp()
	 * @method \string requireKpp()
	 * @method \Bitrix\Catalog\EO_Contractor resetKpp()
	 * @method \Bitrix\Catalog\EO_Contractor unsetKpp()
	 * @method \string fillKpp()
	 * @method \string getAddress()
	 * @method \Bitrix\Catalog\EO_Contractor setAddress(\string|\Bitrix\Main\DB\SqlExpression $address)
	 * @method bool hasAddress()
	 * @method bool isAddressFilled()
	 * @method bool isAddressChanged()
	 * @method \string remindActualAddress()
	 * @method \string requireAddress()
	 * @method \Bitrix\Catalog\EO_Contractor resetAddress()
	 * @method \Bitrix\Catalog\EO_Contractor unsetAddress()
	 * @method \string fillAddress()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Catalog\EO_Contractor setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Catalog\EO_Contractor resetDateModify()
	 * @method \Bitrix\Catalog\EO_Contractor unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Catalog\EO_Contractor setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Catalog\EO_Contractor resetDateCreate()
	 * @method \Bitrix\Catalog\EO_Contractor unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Catalog\EO_Contractor setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Catalog\EO_Contractor resetCreatedBy()
	 * @method \Bitrix\Catalog\EO_Contractor unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Catalog\EO_Contractor setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Catalog\EO_Contractor resetModifiedBy()
	 * @method \Bitrix\Catalog\EO_Contractor unsetModifiedBy()
	 * @method \int fillModifiedBy()
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
	 * @method \Bitrix\Catalog\EO_Contractor set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Contractor reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Contractor unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Contractor wakeUp($data)
	 */
	class EO_Contractor {
		/* @var \Bitrix\Catalog\ContractorTable */
		static public $dataClass = '\Bitrix\Catalog\ContractorTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Contractor_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getPersonTypeList()
	 * @method \string[] fillPersonType()
	 * @method \string[] getPersonNameList()
	 * @method \string[] fillPersonName()
	 * @method \string[] getPersonLastnameList()
	 * @method \string[] fillPersonLastname()
	 * @method \string[] getPersonMiddlenameList()
	 * @method \string[] fillPersonMiddlename()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \string[] getPhoneList()
	 * @method \string[] fillPhone()
	 * @method \string[] getPostIndexList()
	 * @method \string[] fillPostIndex()
	 * @method \string[] getCountryList()
	 * @method \string[] fillCountry()
	 * @method \string[] getCityList()
	 * @method \string[] fillCity()
	 * @method \string[] getCompanyList()
	 * @method \string[] fillCompany()
	 * @method \string[] getInnList()
	 * @method \string[] fillInn()
	 * @method \string[] getKppList()
	 * @method \string[] fillKpp()
	 * @method \string[] getAddressList()
	 * @method \string[] fillAddress()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Contractor $object)
	 * @method bool has(\Bitrix\Catalog\EO_Contractor $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Contractor getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Contractor[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Contractor $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Contractor_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Contractor current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Contractor_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\ContractorTable */
		static public $dataClass = '\Bitrix\Catalog\ContractorTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Contractor_Result exec()
	 * @method \Bitrix\Catalog\EO_Contractor fetchObject()
	 * @method \Bitrix\Catalog\EO_Contractor_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Contractor_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Contractor fetchObject()
	 * @method \Bitrix\Catalog\EO_Contractor_Collection fetchCollection()
	 */
	class EO_Contractor_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Contractor createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Contractor_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Contractor wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Contractor_Collection wakeUpCollection($rows)
	 */
	class EO_Contractor_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\DiscountTable:catalog\lib\discount.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Discount
	 * @see \Bitrix\Catalog\DiscountTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Discount setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getXmlId()
	 * @method \Bitrix\Catalog\EO_Discount setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Catalog\EO_Discount resetXmlId()
	 * @method \Bitrix\Catalog\EO_Discount unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getSiteId()
	 * @method \Bitrix\Catalog\EO_Discount setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Catalog\EO_Discount resetSiteId()
	 * @method \Bitrix\Catalog\EO_Discount unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \int getType()
	 * @method \Bitrix\Catalog\EO_Discount setType(\int|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \int remindActualType()
	 * @method \int requireType()
	 * @method \Bitrix\Catalog\EO_Discount resetType()
	 * @method \Bitrix\Catalog\EO_Discount unsetType()
	 * @method \int fillType()
	 * @method \boolean getActive()
	 * @method \Bitrix\Catalog\EO_Discount setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Catalog\EO_Discount resetActive()
	 * @method \Bitrix\Catalog\EO_Discount unsetActive()
	 * @method \boolean fillActive()
	 * @method \Bitrix\Main\Type\DateTime getActiveFrom()
	 * @method \Bitrix\Catalog\EO_Discount setActiveFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $activeFrom)
	 * @method bool hasActiveFrom()
	 * @method bool isActiveFromFilled()
	 * @method bool isActiveFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime requireActiveFrom()
	 * @method \Bitrix\Catalog\EO_Discount resetActiveFrom()
	 * @method \Bitrix\Catalog\EO_Discount unsetActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime fillActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime getActiveTo()
	 * @method \Bitrix\Catalog\EO_Discount setActiveTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $activeTo)
	 * @method bool hasActiveTo()
	 * @method bool isActiveToFilled()
	 * @method bool isActiveToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualActiveTo()
	 * @method \Bitrix\Main\Type\DateTime requireActiveTo()
	 * @method \Bitrix\Catalog\EO_Discount resetActiveTo()
	 * @method \Bitrix\Catalog\EO_Discount unsetActiveTo()
	 * @method \Bitrix\Main\Type\DateTime fillActiveTo()
	 * @method \boolean getRenewal()
	 * @method \Bitrix\Catalog\EO_Discount setRenewal(\boolean|\Bitrix\Main\DB\SqlExpression $renewal)
	 * @method bool hasRenewal()
	 * @method bool isRenewalFilled()
	 * @method bool isRenewalChanged()
	 * @method \boolean remindActualRenewal()
	 * @method \boolean requireRenewal()
	 * @method \Bitrix\Catalog\EO_Discount resetRenewal()
	 * @method \Bitrix\Catalog\EO_Discount unsetRenewal()
	 * @method \boolean fillRenewal()
	 * @method \string getName()
	 * @method \Bitrix\Catalog\EO_Discount setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Catalog\EO_Discount resetName()
	 * @method \Bitrix\Catalog\EO_Discount unsetName()
	 * @method \string fillName()
	 * @method \int getSort()
	 * @method \Bitrix\Catalog\EO_Discount setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Catalog\EO_Discount resetSort()
	 * @method \Bitrix\Catalog\EO_Discount unsetSort()
	 * @method \int fillSort()
	 * @method \float getMaxDiscount()
	 * @method \Bitrix\Catalog\EO_Discount setMaxDiscount(\float|\Bitrix\Main\DB\SqlExpression $maxDiscount)
	 * @method bool hasMaxDiscount()
	 * @method bool isMaxDiscountFilled()
	 * @method bool isMaxDiscountChanged()
	 * @method \float remindActualMaxDiscount()
	 * @method \float requireMaxDiscount()
	 * @method \Bitrix\Catalog\EO_Discount resetMaxDiscount()
	 * @method \Bitrix\Catalog\EO_Discount unsetMaxDiscount()
	 * @method \float fillMaxDiscount()
	 * @method \string getValueType()
	 * @method \Bitrix\Catalog\EO_Discount setValueType(\string|\Bitrix\Main\DB\SqlExpression $valueType)
	 * @method bool hasValueType()
	 * @method bool isValueTypeFilled()
	 * @method bool isValueTypeChanged()
	 * @method \string remindActualValueType()
	 * @method \string requireValueType()
	 * @method \Bitrix\Catalog\EO_Discount resetValueType()
	 * @method \Bitrix\Catalog\EO_Discount unsetValueType()
	 * @method \string fillValueType()
	 * @method \float getValue()
	 * @method \Bitrix\Catalog\EO_Discount setValue(\float|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \float remindActualValue()
	 * @method \float requireValue()
	 * @method \Bitrix\Catalog\EO_Discount resetValue()
	 * @method \Bitrix\Catalog\EO_Discount unsetValue()
	 * @method \float fillValue()
	 * @method \string getCurrency()
	 * @method \Bitrix\Catalog\EO_Discount setCurrency(\string|\Bitrix\Main\DB\SqlExpression $currency)
	 * @method bool hasCurrency()
	 * @method bool isCurrencyFilled()
	 * @method bool isCurrencyChanged()
	 * @method \string remindActualCurrency()
	 * @method \string requireCurrency()
	 * @method \Bitrix\Catalog\EO_Discount resetCurrency()
	 * @method \Bitrix\Catalog\EO_Discount unsetCurrency()
	 * @method \string fillCurrency()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Catalog\EO_Discount setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Catalog\EO_Discount resetTimestampX()
	 * @method \Bitrix\Catalog\EO_Discount unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getCountPeriod()
	 * @method \Bitrix\Catalog\EO_Discount setCountPeriod(\string|\Bitrix\Main\DB\SqlExpression $countPeriod)
	 * @method bool hasCountPeriod()
	 * @method bool isCountPeriodFilled()
	 * @method bool isCountPeriodChanged()
	 * @method \string remindActualCountPeriod()
	 * @method \string requireCountPeriod()
	 * @method \Bitrix\Catalog\EO_Discount resetCountPeriod()
	 * @method \Bitrix\Catalog\EO_Discount unsetCountPeriod()
	 * @method \string fillCountPeriod()
	 * @method \int getCountSize()
	 * @method \Bitrix\Catalog\EO_Discount setCountSize(\int|\Bitrix\Main\DB\SqlExpression $countSize)
	 * @method bool hasCountSize()
	 * @method bool isCountSizeFilled()
	 * @method bool isCountSizeChanged()
	 * @method \int remindActualCountSize()
	 * @method \int requireCountSize()
	 * @method \Bitrix\Catalog\EO_Discount resetCountSize()
	 * @method \Bitrix\Catalog\EO_Discount unsetCountSize()
	 * @method \int fillCountSize()
	 * @method \string getCountType()
	 * @method \Bitrix\Catalog\EO_Discount setCountType(\string|\Bitrix\Main\DB\SqlExpression $countType)
	 * @method bool hasCountType()
	 * @method bool isCountTypeFilled()
	 * @method bool isCountTypeChanged()
	 * @method \string remindActualCountType()
	 * @method \string requireCountType()
	 * @method \Bitrix\Catalog\EO_Discount resetCountType()
	 * @method \Bitrix\Catalog\EO_Discount unsetCountType()
	 * @method \string fillCountType()
	 * @method \Bitrix\Main\Type\DateTime getCountFrom()
	 * @method \Bitrix\Catalog\EO_Discount setCountFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $countFrom)
	 * @method bool hasCountFrom()
	 * @method bool isCountFromFilled()
	 * @method bool isCountFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCountFrom()
	 * @method \Bitrix\Main\Type\DateTime requireCountFrom()
	 * @method \Bitrix\Catalog\EO_Discount resetCountFrom()
	 * @method \Bitrix\Catalog\EO_Discount unsetCountFrom()
	 * @method \Bitrix\Main\Type\DateTime fillCountFrom()
	 * @method \Bitrix\Main\Type\DateTime getCountTo()
	 * @method \Bitrix\Catalog\EO_Discount setCountTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $countTo)
	 * @method bool hasCountTo()
	 * @method bool isCountToFilled()
	 * @method bool isCountToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCountTo()
	 * @method \Bitrix\Main\Type\DateTime requireCountTo()
	 * @method \Bitrix\Catalog\EO_Discount resetCountTo()
	 * @method \Bitrix\Catalog\EO_Discount unsetCountTo()
	 * @method \Bitrix\Main\Type\DateTime fillCountTo()
	 * @method \int getActionSize()
	 * @method \Bitrix\Catalog\EO_Discount setActionSize(\int|\Bitrix\Main\DB\SqlExpression $actionSize)
	 * @method bool hasActionSize()
	 * @method bool isActionSizeFilled()
	 * @method bool isActionSizeChanged()
	 * @method \int remindActualActionSize()
	 * @method \int requireActionSize()
	 * @method \Bitrix\Catalog\EO_Discount resetActionSize()
	 * @method \Bitrix\Catalog\EO_Discount unsetActionSize()
	 * @method \int fillActionSize()
	 * @method \string getActionType()
	 * @method \Bitrix\Catalog\EO_Discount setActionType(\string|\Bitrix\Main\DB\SqlExpression $actionType)
	 * @method bool hasActionType()
	 * @method bool isActionTypeFilled()
	 * @method bool isActionTypeChanged()
	 * @method \string remindActualActionType()
	 * @method \string requireActionType()
	 * @method \Bitrix\Catalog\EO_Discount resetActionType()
	 * @method \Bitrix\Catalog\EO_Discount unsetActionType()
	 * @method \string fillActionType()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Catalog\EO_Discount setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Catalog\EO_Discount resetModifiedBy()
	 * @method \Bitrix\Catalog\EO_Discount unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Catalog\EO_Discount setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Catalog\EO_Discount resetDateCreate()
	 * @method \Bitrix\Catalog\EO_Discount unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Catalog\EO_Discount setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Catalog\EO_Discount resetCreatedBy()
	 * @method \Bitrix\Catalog\EO_Discount unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getPriority()
	 * @method \Bitrix\Catalog\EO_Discount setPriority(\int|\Bitrix\Main\DB\SqlExpression $priority)
	 * @method bool hasPriority()
	 * @method bool isPriorityFilled()
	 * @method bool isPriorityChanged()
	 * @method \int remindActualPriority()
	 * @method \int requirePriority()
	 * @method \Bitrix\Catalog\EO_Discount resetPriority()
	 * @method \Bitrix\Catalog\EO_Discount unsetPriority()
	 * @method \int fillPriority()
	 * @method \boolean getLastDiscount()
	 * @method \Bitrix\Catalog\EO_Discount setLastDiscount(\boolean|\Bitrix\Main\DB\SqlExpression $lastDiscount)
	 * @method bool hasLastDiscount()
	 * @method bool isLastDiscountFilled()
	 * @method bool isLastDiscountChanged()
	 * @method \boolean remindActualLastDiscount()
	 * @method \boolean requireLastDiscount()
	 * @method \Bitrix\Catalog\EO_Discount resetLastDiscount()
	 * @method \Bitrix\Catalog\EO_Discount unsetLastDiscount()
	 * @method \boolean fillLastDiscount()
	 * @method \string getVersion()
	 * @method \Bitrix\Catalog\EO_Discount setVersion(\string|\Bitrix\Main\DB\SqlExpression $version)
	 * @method bool hasVersion()
	 * @method bool isVersionFilled()
	 * @method bool isVersionChanged()
	 * @method \string remindActualVersion()
	 * @method \string requireVersion()
	 * @method \Bitrix\Catalog\EO_Discount resetVersion()
	 * @method \Bitrix\Catalog\EO_Discount unsetVersion()
	 * @method \string fillVersion()
	 * @method \string getNotes()
	 * @method \Bitrix\Catalog\EO_Discount setNotes(\string|\Bitrix\Main\DB\SqlExpression $notes)
	 * @method bool hasNotes()
	 * @method bool isNotesFilled()
	 * @method bool isNotesChanged()
	 * @method \string remindActualNotes()
	 * @method \string requireNotes()
	 * @method \Bitrix\Catalog\EO_Discount resetNotes()
	 * @method \Bitrix\Catalog\EO_Discount unsetNotes()
	 * @method \string fillNotes()
	 * @method \string getConditions()
	 * @method \Bitrix\Catalog\EO_Discount setConditions(\string|\Bitrix\Main\DB\SqlExpression $conditions)
	 * @method bool hasConditions()
	 * @method bool isConditionsFilled()
	 * @method bool isConditionsChanged()
	 * @method \string remindActualConditions()
	 * @method \string requireConditions()
	 * @method \Bitrix\Catalog\EO_Discount resetConditions()
	 * @method \Bitrix\Catalog\EO_Discount unsetConditions()
	 * @method \string fillConditions()
	 * @method \string getConditionsList()
	 * @method \Bitrix\Catalog\EO_Discount setConditionsList(\string|\Bitrix\Main\DB\SqlExpression $conditionsList)
	 * @method bool hasConditionsList()
	 * @method bool isConditionsListFilled()
	 * @method bool isConditionsListChanged()
	 * @method \string remindActualConditionsList()
	 * @method \string requireConditionsList()
	 * @method \Bitrix\Catalog\EO_Discount resetConditionsList()
	 * @method \Bitrix\Catalog\EO_Discount unsetConditionsList()
	 * @method \string fillConditionsList()
	 * @method \string getUnpack()
	 * @method \Bitrix\Catalog\EO_Discount setUnpack(\string|\Bitrix\Main\DB\SqlExpression $unpack)
	 * @method bool hasUnpack()
	 * @method bool isUnpackFilled()
	 * @method bool isUnpackChanged()
	 * @method \string remindActualUnpack()
	 * @method \string requireUnpack()
	 * @method \Bitrix\Catalog\EO_Discount resetUnpack()
	 * @method \Bitrix\Catalog\EO_Discount unsetUnpack()
	 * @method \string fillUnpack()
	 * @method \boolean getUseCoupons()
	 * @method \Bitrix\Catalog\EO_Discount setUseCoupons(\boolean|\Bitrix\Main\DB\SqlExpression $useCoupons)
	 * @method bool hasUseCoupons()
	 * @method bool isUseCouponsFilled()
	 * @method bool isUseCouponsChanged()
	 * @method \boolean remindActualUseCoupons()
	 * @method \boolean requireUseCoupons()
	 * @method \Bitrix\Catalog\EO_Discount resetUseCoupons()
	 * @method \Bitrix\Catalog\EO_Discount unsetUseCoupons()
	 * @method \boolean fillUseCoupons()
	 * @method \int getSaleId()
	 * @method \Bitrix\Catalog\EO_Discount setSaleId(\int|\Bitrix\Main\DB\SqlExpression $saleId)
	 * @method bool hasSaleId()
	 * @method bool isSaleIdFilled()
	 * @method bool isSaleIdChanged()
	 * @method \int remindActualSaleId()
	 * @method \int requireSaleId()
	 * @method \Bitrix\Catalog\EO_Discount resetSaleId()
	 * @method \Bitrix\Catalog\EO_Discount unsetSaleId()
	 * @method \int fillSaleId()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Catalog\EO_Discount setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_Discount resetCreatedByUser()
	 * @method \Bitrix\Catalog\EO_Discount unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Discount setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_Discount resetModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Discount unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \Bitrix\Sale\Internals\EO_Discount getSaleDiscount()
	 * @method \Bitrix\Sale\Internals\EO_Discount remindActualSaleDiscount()
	 * @method \Bitrix\Sale\Internals\EO_Discount requireSaleDiscount()
	 * @method \Bitrix\Catalog\EO_Discount setSaleDiscount(\Bitrix\Sale\Internals\EO_Discount $object)
	 * @method \Bitrix\Catalog\EO_Discount resetSaleDiscount()
	 * @method \Bitrix\Catalog\EO_Discount unsetSaleDiscount()
	 * @method bool hasSaleDiscount()
	 * @method bool isSaleDiscountFilled()
	 * @method bool isSaleDiscountChanged()
	 * @method \Bitrix\Sale\Internals\EO_Discount fillSaleDiscount()
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
	 * @method \Bitrix\Catalog\EO_Discount set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Discount reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Discount unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Discount wakeUp($data)
	 */
	class EO_Discount {
		/* @var \Bitrix\Catalog\DiscountTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Discount_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \int[] getTypeList()
	 * @method \int[] fillType()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \Bitrix\Main\Type\DateTime[] getActiveFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillActiveFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getActiveToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillActiveTo()
	 * @method \boolean[] getRenewalList()
	 * @method \boolean[] fillRenewal()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \float[] getMaxDiscountList()
	 * @method \float[] fillMaxDiscount()
	 * @method \string[] getValueTypeList()
	 * @method \string[] fillValueType()
	 * @method \float[] getValueList()
	 * @method \float[] fillValue()
	 * @method \string[] getCurrencyList()
	 * @method \string[] fillCurrency()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getCountPeriodList()
	 * @method \string[] fillCountPeriod()
	 * @method \int[] getCountSizeList()
	 * @method \int[] fillCountSize()
	 * @method \string[] getCountTypeList()
	 * @method \string[] fillCountType()
	 * @method \Bitrix\Main\Type\DateTime[] getCountFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCountFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getCountToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCountTo()
	 * @method \int[] getActionSizeList()
	 * @method \int[] fillActionSize()
	 * @method \string[] getActionTypeList()
	 * @method \string[] fillActionType()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getPriorityList()
	 * @method \int[] fillPriority()
	 * @method \boolean[] getLastDiscountList()
	 * @method \boolean[] fillLastDiscount()
	 * @method \string[] getVersionList()
	 * @method \string[] fillVersion()
	 * @method \string[] getNotesList()
	 * @method \string[] fillNotes()
	 * @method \string[] getConditionsList()
	 * @method \string[] fillConditions()
	 * @method \string[] getConditionsListList()
	 * @method \string[] fillConditionsList()
	 * @method \string[] getUnpackList()
	 * @method \string[] fillUnpack()
	 * @method \boolean[] getUseCouponsList()
	 * @method \boolean[] fillUseCoupons()
	 * @method \int[] getSaleIdList()
	 * @method \int[] fillSaleId()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Catalog\EO_Discount_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Catalog\EO_Discount_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \Bitrix\Sale\Internals\EO_Discount[] getSaleDiscountList()
	 * @method \Bitrix\Catalog\EO_Discount_Collection getSaleDiscountCollection()
	 * @method \Bitrix\Sale\Internals\EO_Discount_Collection fillSaleDiscount()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Discount $object)
	 * @method bool has(\Bitrix\Catalog\EO_Discount $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Discount getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Discount[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Discount $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Discount_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Discount current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Discount_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\DiscountTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Discount_Result exec()
	 * @method \Bitrix\Catalog\EO_Discount fetchObject()
	 * @method \Bitrix\Catalog\EO_Discount_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Discount_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Discount fetchObject()
	 * @method \Bitrix\Catalog\EO_Discount_Collection fetchCollection()
	 */
	class EO_Discount_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Discount createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Discount_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Discount wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Discount_Collection wakeUpCollection($rows)
	 */
	class EO_Discount_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\DiscountCouponTable:catalog\lib\discountcoupon.php */
namespace Bitrix\Catalog {
	/**
	 * EO_DiscountCoupon
	 * @see \Bitrix\Catalog\DiscountCouponTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setDiscountId(\int|\Bitrix\Main\DB\SqlExpression $discountId)
	 * @method bool hasDiscountId()
	 * @method bool isDiscountIdFilled()
	 * @method bool isDiscountIdChanged()
	 * @method \int remindActualDiscountId()
	 * @method \int requireDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetDiscountId()
	 * @method \int fillDiscountId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetActive()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getCoupon()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setCoupon(\string|\Bitrix\Main\DB\SqlExpression $coupon)
	 * @method bool hasCoupon()
	 * @method bool isCouponFilled()
	 * @method bool isCouponChanged()
	 * @method \string remindActualCoupon()
	 * @method \string requireCoupon()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetCoupon()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetCoupon()
	 * @method \string fillCoupon()
	 * @method \Bitrix\Main\Type\DateTime getDateApply()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setDateApply(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateApply)
	 * @method bool hasDateApply()
	 * @method bool isDateApplyFilled()
	 * @method bool isDateApplyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateApply()
	 * @method \Bitrix\Main\Type\DateTime requireDateApply()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetDateApply()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetDateApply()
	 * @method \Bitrix\Main\Type\DateTime fillDateApply()
	 * @method \string getType()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetType()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetType()
	 * @method \string fillType()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetTimestampX()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetModifiedBy()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetDateCreate()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetCreatedBy()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \string getDescription()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetDescription()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetDescription()
	 * @method \string fillDescription()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetCreatedByUser()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetModifiedByUser()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Discount getDiscount()
	 * @method \Bitrix\Catalog\EO_Discount remindActualDiscount()
	 * @method \Bitrix\Catalog\EO_Discount requireDiscount()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon setDiscount(\Bitrix\Catalog\EO_Discount $object)
	 * @method \Bitrix\Catalog\EO_DiscountCoupon resetDiscount()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unsetDiscount()
	 * @method bool hasDiscount()
	 * @method bool isDiscountFilled()
	 * @method bool isDiscountChanged()
	 * @method \Bitrix\Catalog\EO_Discount fillDiscount()
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
	 * @method \Bitrix\Catalog\EO_DiscountCoupon set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_DiscountCoupon reset($fieldName)
	 * @method \Bitrix\Catalog\EO_DiscountCoupon unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_DiscountCoupon wakeUp($data)
	 */
	class EO_DiscountCoupon {
		/* @var \Bitrix\Catalog\DiscountCouponTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountCouponTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_DiscountCoupon_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDiscountIdList()
	 * @method \int[] fillDiscountId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getCouponList()
	 * @method \string[] fillCoupon()
	 * @method \Bitrix\Main\Type\DateTime[] getDateApplyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateApply()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Discount[] getDiscountList()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon_Collection getDiscountCollection()
	 * @method \Bitrix\Catalog\EO_Discount_Collection fillDiscount()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_DiscountCoupon $object)
	 * @method bool has(\Bitrix\Catalog\EO_DiscountCoupon $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_DiscountCoupon getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_DiscountCoupon[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_DiscountCoupon $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_DiscountCoupon_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_DiscountCoupon current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_DiscountCoupon_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\DiscountCouponTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountCouponTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DiscountCoupon_Result exec()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon fetchObject()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DiscountCoupon_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_DiscountCoupon fetchObject()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon_Collection fetchCollection()
	 */
	class EO_DiscountCoupon_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_DiscountCoupon createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_DiscountCoupon_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_DiscountCoupon wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_DiscountCoupon_Collection wakeUpCollection($rows)
	 */
	class EO_DiscountCoupon_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\DiscountEntityTable:catalog\lib\discountentity.php */
namespace Bitrix\Catalog {
	/**
	 * EO_DiscountEntity
	 * @see \Bitrix\Catalog\DiscountEntityTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity setDiscountId(\int|\Bitrix\Main\DB\SqlExpression $discountId)
	 * @method bool hasDiscountId()
	 * @method bool isDiscountIdFilled()
	 * @method bool isDiscountIdChanged()
	 * @method \int remindActualDiscountId()
	 * @method \int requireDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity resetDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity unsetDiscountId()
	 * @method \int fillDiscountId()
	 * @method \string getModuleId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity resetModuleId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Catalog\EO_DiscountEntity setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Catalog\EO_DiscountEntity resetEntity()
	 * @method \Bitrix\Catalog\EO_DiscountEntity unsetEntity()
	 * @method \string fillEntity()
	 * @method \int getEntityId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity resetEntityId()
	 * @method \Bitrix\Catalog\EO_DiscountEntity unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntityValue()
	 * @method \Bitrix\Catalog\EO_DiscountEntity setEntityValue(\string|\Bitrix\Main\DB\SqlExpression $entityValue)
	 * @method bool hasEntityValue()
	 * @method bool isEntityValueFilled()
	 * @method bool isEntityValueChanged()
	 * @method \string remindActualEntityValue()
	 * @method \string requireEntityValue()
	 * @method \Bitrix\Catalog\EO_DiscountEntity resetEntityValue()
	 * @method \Bitrix\Catalog\EO_DiscountEntity unsetEntityValue()
	 * @method \string fillEntityValue()
	 * @method \string getFieldEntity()
	 * @method \Bitrix\Catalog\EO_DiscountEntity setFieldEntity(\string|\Bitrix\Main\DB\SqlExpression $fieldEntity)
	 * @method bool hasFieldEntity()
	 * @method bool isFieldEntityFilled()
	 * @method bool isFieldEntityChanged()
	 * @method \string remindActualFieldEntity()
	 * @method \string requireFieldEntity()
	 * @method \Bitrix\Catalog\EO_DiscountEntity resetFieldEntity()
	 * @method \Bitrix\Catalog\EO_DiscountEntity unsetFieldEntity()
	 * @method \string fillFieldEntity()
	 * @method \string getFieldTable()
	 * @method \Bitrix\Catalog\EO_DiscountEntity setFieldTable(\string|\Bitrix\Main\DB\SqlExpression $fieldTable)
	 * @method bool hasFieldTable()
	 * @method bool isFieldTableFilled()
	 * @method bool isFieldTableChanged()
	 * @method \string remindActualFieldTable()
	 * @method \string requireFieldTable()
	 * @method \Bitrix\Catalog\EO_DiscountEntity resetFieldTable()
	 * @method \Bitrix\Catalog\EO_DiscountEntity unsetFieldTable()
	 * @method \string fillFieldTable()
	 * @method \Bitrix\Catalog\EO_Discount getDiscount()
	 * @method \Bitrix\Catalog\EO_Discount remindActualDiscount()
	 * @method \Bitrix\Catalog\EO_Discount requireDiscount()
	 * @method \Bitrix\Catalog\EO_DiscountEntity setDiscount(\Bitrix\Catalog\EO_Discount $object)
	 * @method \Bitrix\Catalog\EO_DiscountEntity resetDiscount()
	 * @method \Bitrix\Catalog\EO_DiscountEntity unsetDiscount()
	 * @method bool hasDiscount()
	 * @method bool isDiscountFilled()
	 * @method bool isDiscountChanged()
	 * @method \Bitrix\Catalog\EO_Discount fillDiscount()
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
	 * @method \Bitrix\Catalog\EO_DiscountEntity set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_DiscountEntity reset($fieldName)
	 * @method \Bitrix\Catalog\EO_DiscountEntity unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_DiscountEntity wakeUp($data)
	 */
	class EO_DiscountEntity {
		/* @var \Bitrix\Catalog\DiscountEntityTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountEntityTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_DiscountEntity_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDiscountIdList()
	 * @method \int[] fillDiscountId()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityValueList()
	 * @method \string[] fillEntityValue()
	 * @method \string[] getFieldEntityList()
	 * @method \string[] fillFieldEntity()
	 * @method \string[] getFieldTableList()
	 * @method \string[] fillFieldTable()
	 * @method \Bitrix\Catalog\EO_Discount[] getDiscountList()
	 * @method \Bitrix\Catalog\EO_DiscountEntity_Collection getDiscountCollection()
	 * @method \Bitrix\Catalog\EO_Discount_Collection fillDiscount()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_DiscountEntity $object)
	 * @method bool has(\Bitrix\Catalog\EO_DiscountEntity $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_DiscountEntity getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_DiscountEntity[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_DiscountEntity $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_DiscountEntity_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_DiscountEntity current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_DiscountEntity_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\DiscountEntityTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountEntityTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DiscountEntity_Result exec()
	 * @method \Bitrix\Catalog\EO_DiscountEntity fetchObject()
	 * @method \Bitrix\Catalog\EO_DiscountEntity_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DiscountEntity_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_DiscountEntity fetchObject()
	 * @method \Bitrix\Catalog\EO_DiscountEntity_Collection fetchCollection()
	 */
	class EO_DiscountEntity_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_DiscountEntity createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_DiscountEntity_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_DiscountEntity wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_DiscountEntity_Collection wakeUpCollection($rows)
	 */
	class EO_DiscountEntity_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\DiscountModuleTable:catalog\lib\discountmodule.php */
namespace Bitrix\Catalog {
	/**
	 * EO_DiscountModule
	 * @see \Bitrix\Catalog\DiscountModuleTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_DiscountModule setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountModule setDiscountId(\int|\Bitrix\Main\DB\SqlExpression $discountId)
	 * @method bool hasDiscountId()
	 * @method bool isDiscountIdFilled()
	 * @method bool isDiscountIdChanged()
	 * @method \int remindActualDiscountId()
	 * @method \int requireDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountModule resetDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountModule unsetDiscountId()
	 * @method \int fillDiscountId()
	 * @method \string getModuleId()
	 * @method \Bitrix\Catalog\EO_DiscountModule setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Catalog\EO_DiscountModule resetModuleId()
	 * @method \Bitrix\Catalog\EO_DiscountModule unsetModuleId()
	 * @method \string fillModuleId()
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
	 * @method \Bitrix\Catalog\EO_DiscountModule set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_DiscountModule reset($fieldName)
	 * @method \Bitrix\Catalog\EO_DiscountModule unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_DiscountModule wakeUp($data)
	 */
	class EO_DiscountModule {
		/* @var \Bitrix\Catalog\DiscountModuleTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountModuleTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_DiscountModule_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDiscountIdList()
	 * @method \int[] fillDiscountId()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_DiscountModule $object)
	 * @method bool has(\Bitrix\Catalog\EO_DiscountModule $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_DiscountModule getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_DiscountModule[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_DiscountModule $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_DiscountModule_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_DiscountModule current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_DiscountModule_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\DiscountModuleTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountModuleTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DiscountModule_Result exec()
	 * @method \Bitrix\Catalog\EO_DiscountModule fetchObject()
	 * @method \Bitrix\Catalog\EO_DiscountModule_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DiscountModule_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_DiscountModule fetchObject()
	 * @method \Bitrix\Catalog\EO_DiscountModule_Collection fetchCollection()
	 */
	class EO_DiscountModule_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_DiscountModule createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_DiscountModule_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_DiscountModule wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_DiscountModule_Collection wakeUpCollection($rows)
	 */
	class EO_DiscountModule_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\DiscountRestrictionTable:catalog\lib\discountrestriction.php */
namespace Bitrix\Catalog {
	/**
	 * EO_DiscountRestriction
	 * @see \Bitrix\Catalog\DiscountRestrictionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction setDiscountId(\int|\Bitrix\Main\DB\SqlExpression $discountId)
	 * @method bool hasDiscountId()
	 * @method bool isDiscountIdFilled()
	 * @method bool isDiscountIdChanged()
	 * @method \int remindActualDiscountId()
	 * @method \int requireDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction resetDiscountId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction unsetDiscountId()
	 * @method \int fillDiscountId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction resetActive()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getUserGroupId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction setUserGroupId(\int|\Bitrix\Main\DB\SqlExpression $userGroupId)
	 * @method bool hasUserGroupId()
	 * @method bool isUserGroupIdFilled()
	 * @method bool isUserGroupIdChanged()
	 * @method \int remindActualUserGroupId()
	 * @method \int requireUserGroupId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction resetUserGroupId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction unsetUserGroupId()
	 * @method \int fillUserGroupId()
	 * @method \int getPriceTypeId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction setPriceTypeId(\int|\Bitrix\Main\DB\SqlExpression $priceTypeId)
	 * @method bool hasPriceTypeId()
	 * @method bool isPriceTypeIdFilled()
	 * @method bool isPriceTypeIdChanged()
	 * @method \int remindActualPriceTypeId()
	 * @method \int requirePriceTypeId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction resetPriceTypeId()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction unsetPriceTypeId()
	 * @method \int fillPriceTypeId()
	 * @method \Bitrix\Catalog\EO_Discount getDiscount()
	 * @method \Bitrix\Catalog\EO_Discount remindActualDiscount()
	 * @method \Bitrix\Catalog\EO_Discount requireDiscount()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction setDiscount(\Bitrix\Catalog\EO_Discount $object)
	 * @method \Bitrix\Catalog\EO_DiscountRestriction resetDiscount()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction unsetDiscount()
	 * @method bool hasDiscount()
	 * @method bool isDiscountFilled()
	 * @method bool isDiscountChanged()
	 * @method \Bitrix\Catalog\EO_Discount fillDiscount()
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
	 * @method \Bitrix\Catalog\EO_DiscountRestriction set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_DiscountRestriction reset($fieldName)
	 * @method \Bitrix\Catalog\EO_DiscountRestriction unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_DiscountRestriction wakeUp($data)
	 */
	class EO_DiscountRestriction {
		/* @var \Bitrix\Catalog\DiscountRestrictionTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountRestrictionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_DiscountRestriction_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDiscountIdList()
	 * @method \int[] fillDiscountId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getUserGroupIdList()
	 * @method \int[] fillUserGroupId()
	 * @method \int[] getPriceTypeIdList()
	 * @method \int[] fillPriceTypeId()
	 * @method \Bitrix\Catalog\EO_Discount[] getDiscountList()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction_Collection getDiscountCollection()
	 * @method \Bitrix\Catalog\EO_Discount_Collection fillDiscount()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_DiscountRestriction $object)
	 * @method bool has(\Bitrix\Catalog\EO_DiscountRestriction $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_DiscountRestriction getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_DiscountRestriction[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_DiscountRestriction $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_DiscountRestriction_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_DiscountRestriction current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_DiscountRestriction_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\DiscountRestrictionTable */
		static public $dataClass = '\Bitrix\Catalog\DiscountRestrictionTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DiscountRestriction_Result exec()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction fetchObject()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DiscountRestriction_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_DiscountRestriction fetchObject()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction_Collection fetchCollection()
	 */
	class EO_DiscountRestriction_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_DiscountRestriction createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_DiscountRestriction_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_DiscountRestriction wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_DiscountRestriction_Collection wakeUpCollection($rows)
	 */
	class EO_DiscountRestriction_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\ExtraTable:catalog\lib\extra.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Extra
	 * @see \Bitrix\Catalog\ExtraTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Extra setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Catalog\EO_Extra setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Catalog\EO_Extra resetName()
	 * @method \Bitrix\Catalog\EO_Extra unsetName()
	 * @method \string fillName()
	 * @method \float getPercentage()
	 * @method \Bitrix\Catalog\EO_Extra setPercentage(\float|\Bitrix\Main\DB\SqlExpression $percentage)
	 * @method bool hasPercentage()
	 * @method bool isPercentageFilled()
	 * @method bool isPercentageChanged()
	 * @method \float remindActualPercentage()
	 * @method \float requirePercentage()
	 * @method \Bitrix\Catalog\EO_Extra resetPercentage()
	 * @method \Bitrix\Catalog\EO_Extra unsetPercentage()
	 * @method \float fillPercentage()
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
	 * @method \Bitrix\Catalog\EO_Extra set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Extra reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Extra unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Extra wakeUp($data)
	 */
	class EO_Extra {
		/* @var \Bitrix\Catalog\ExtraTable */
		static public $dataClass = '\Bitrix\Catalog\ExtraTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Extra_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \float[] getPercentageList()
	 * @method \float[] fillPercentage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Extra $object)
	 * @method bool has(\Bitrix\Catalog\EO_Extra $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Extra getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Extra[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Extra $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Extra_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Extra current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Extra_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\ExtraTable */
		static public $dataClass = '\Bitrix\Catalog\ExtraTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Extra_Result exec()
	 * @method \Bitrix\Catalog\EO_Extra fetchObject()
	 * @method \Bitrix\Catalog\EO_Extra_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Extra_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Extra fetchObject()
	 * @method \Bitrix\Catalog\EO_Extra_Collection fetchCollection()
	 */
	class EO_Extra_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Extra createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Extra_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Extra wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Extra_Collection wakeUpCollection($rows)
	 */
	class EO_Extra_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\GroupTable:catalog\lib\group.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Group
	 * @see \Bitrix\Catalog\GroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Group setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Catalog\EO_Group setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Catalog\EO_Group resetName()
	 * @method \Bitrix\Catalog\EO_Group unsetName()
	 * @method \string fillName()
	 * @method \boolean getBase()
	 * @method \Bitrix\Catalog\EO_Group setBase(\boolean|\Bitrix\Main\DB\SqlExpression $base)
	 * @method bool hasBase()
	 * @method bool isBaseFilled()
	 * @method bool isBaseChanged()
	 * @method \boolean remindActualBase()
	 * @method \boolean requireBase()
	 * @method \Bitrix\Catalog\EO_Group resetBase()
	 * @method \Bitrix\Catalog\EO_Group unsetBase()
	 * @method \boolean fillBase()
	 * @method \int getSort()
	 * @method \Bitrix\Catalog\EO_Group setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Catalog\EO_Group resetSort()
	 * @method \Bitrix\Catalog\EO_Group unsetSort()
	 * @method \int fillSort()
	 * @method \string getXmlId()
	 * @method \Bitrix\Catalog\EO_Group setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Catalog\EO_Group resetXmlId()
	 * @method \Bitrix\Catalog\EO_Group unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Catalog\EO_Group setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Catalog\EO_Group resetTimestampX()
	 * @method \Bitrix\Catalog\EO_Group unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Catalog\EO_Group setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Catalog\EO_Group resetModifiedBy()
	 * @method \Bitrix\Catalog\EO_Group unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Catalog\EO_Group setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Catalog\EO_Group resetDateCreate()
	 * @method \Bitrix\Catalog\EO_Group unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Catalog\EO_Group setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Catalog\EO_Group resetCreatedBy()
	 * @method \Bitrix\Catalog\EO_Group unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Catalog\EO_Group setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_Group resetCreatedByUser()
	 * @method \Bitrix\Catalog\EO_Group unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Group setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_Group resetModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Group unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \Bitrix\Catalog\EO_GroupLang getLang()
	 * @method \Bitrix\Catalog\EO_GroupLang remindActualLang()
	 * @method \Bitrix\Catalog\EO_GroupLang requireLang()
	 * @method \Bitrix\Catalog\EO_Group setLang(\Bitrix\Catalog\EO_GroupLang $object)
	 * @method \Bitrix\Catalog\EO_Group resetLang()
	 * @method \Bitrix\Catalog\EO_Group unsetLang()
	 * @method bool hasLang()
	 * @method bool isLangFilled()
	 * @method bool isLangChanged()
	 * @method \Bitrix\Catalog\EO_GroupLang fillLang()
	 * @method \Bitrix\Catalog\EO_GroupLang getCurrentLang()
	 * @method \Bitrix\Catalog\EO_GroupLang remindActualCurrentLang()
	 * @method \Bitrix\Catalog\EO_GroupLang requireCurrentLang()
	 * @method \Bitrix\Catalog\EO_Group setCurrentLang(\Bitrix\Catalog\EO_GroupLang $object)
	 * @method \Bitrix\Catalog\EO_Group resetCurrentLang()
	 * @method \Bitrix\Catalog\EO_Group unsetCurrentLang()
	 * @method bool hasCurrentLang()
	 * @method bool isCurrentLangFilled()
	 * @method bool isCurrentLangChanged()
	 * @method \Bitrix\Catalog\EO_GroupLang fillCurrentLang()
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
	 * @method \Bitrix\Catalog\EO_Group set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Group reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Group unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Group wakeUp($data)
	 */
	class EO_Group {
		/* @var \Bitrix\Catalog\GroupTable */
		static public $dataClass = '\Bitrix\Catalog\GroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Group_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \boolean[] getBaseList()
	 * @method \boolean[] fillBase()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Catalog\EO_Group_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Catalog\EO_Group_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \Bitrix\Catalog\EO_GroupLang[] getLangList()
	 * @method \Bitrix\Catalog\EO_Group_Collection getLangCollection()
	 * @method \Bitrix\Catalog\EO_GroupLang_Collection fillLang()
	 * @method \Bitrix\Catalog\EO_GroupLang[] getCurrentLangList()
	 * @method \Bitrix\Catalog\EO_Group_Collection getCurrentLangCollection()
	 * @method \Bitrix\Catalog\EO_GroupLang_Collection fillCurrentLang()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Group $object)
	 * @method bool has(\Bitrix\Catalog\EO_Group $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Group getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Group[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Group $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Group_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Group current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Group_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\GroupTable */
		static public $dataClass = '\Bitrix\Catalog\GroupTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Group_Result exec()
	 * @method \Bitrix\Catalog\EO_Group fetchObject()
	 * @method \Bitrix\Catalog\EO_Group_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Group_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Group fetchObject()
	 * @method \Bitrix\Catalog\EO_Group_Collection fetchCollection()
	 */
	class EO_Group_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Group createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Group_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Group wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Group_Collection wakeUpCollection($rows)
	 */
	class EO_Group_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\GroupAccessTable:catalog\lib\groupaccess.php */
namespace Bitrix\Catalog {
	/**
	 * EO_GroupAccess
	 * @see \Bitrix\Catalog\GroupAccessTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_GroupAccess setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_GroupAccess setCatalogGroupId(\int|\Bitrix\Main\DB\SqlExpression $catalogGroupId)
	 * @method bool hasCatalogGroupId()
	 * @method bool isCatalogGroupIdFilled()
	 * @method bool isCatalogGroupIdChanged()
	 * @method \int remindActualCatalogGroupId()
	 * @method \int requireCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_GroupAccess resetCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_GroupAccess unsetCatalogGroupId()
	 * @method \int fillCatalogGroupId()
	 * @method \int getGroupId()
	 * @method \Bitrix\Catalog\EO_GroupAccess setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Catalog\EO_GroupAccess resetGroupId()
	 * @method \Bitrix\Catalog\EO_GroupAccess unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \boolean getAccess()
	 * @method \Bitrix\Catalog\EO_GroupAccess setAccess(\boolean|\Bitrix\Main\DB\SqlExpression $access)
	 * @method bool hasAccess()
	 * @method bool isAccessFilled()
	 * @method bool isAccessChanged()
	 * @method \boolean remindActualAccess()
	 * @method \boolean requireAccess()
	 * @method \Bitrix\Catalog\EO_GroupAccess resetAccess()
	 * @method \Bitrix\Catalog\EO_GroupAccess unsetAccess()
	 * @method \boolean fillAccess()
	 * @method \Bitrix\Catalog\EO_Group getCatalogGroup()
	 * @method \Bitrix\Catalog\EO_Group remindActualCatalogGroup()
	 * @method \Bitrix\Catalog\EO_Group requireCatalogGroup()
	 * @method \Bitrix\Catalog\EO_GroupAccess setCatalogGroup(\Bitrix\Catalog\EO_Group $object)
	 * @method \Bitrix\Catalog\EO_GroupAccess resetCatalogGroup()
	 * @method \Bitrix\Catalog\EO_GroupAccess unsetCatalogGroup()
	 * @method bool hasCatalogGroup()
	 * @method bool isCatalogGroupFilled()
	 * @method bool isCatalogGroupChanged()
	 * @method \Bitrix\Catalog\EO_Group fillCatalogGroup()
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
	 * @method \Bitrix\Catalog\EO_GroupAccess set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_GroupAccess reset($fieldName)
	 * @method \Bitrix\Catalog\EO_GroupAccess unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_GroupAccess wakeUp($data)
	 */
	class EO_GroupAccess {
		/* @var \Bitrix\Catalog\GroupAccessTable */
		static public $dataClass = '\Bitrix\Catalog\GroupAccessTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_GroupAccess_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getCatalogGroupIdList()
	 * @method \int[] fillCatalogGroupId()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \boolean[] getAccessList()
	 * @method \boolean[] fillAccess()
	 * @method \Bitrix\Catalog\EO_Group[] getCatalogGroupList()
	 * @method \Bitrix\Catalog\EO_GroupAccess_Collection getCatalogGroupCollection()
	 * @method \Bitrix\Catalog\EO_Group_Collection fillCatalogGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_GroupAccess $object)
	 * @method bool has(\Bitrix\Catalog\EO_GroupAccess $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_GroupAccess getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_GroupAccess[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_GroupAccess $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_GroupAccess_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_GroupAccess current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupAccess_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\GroupAccessTable */
		static public $dataClass = '\Bitrix\Catalog\GroupAccessTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupAccess_Result exec()
	 * @method \Bitrix\Catalog\EO_GroupAccess fetchObject()
	 * @method \Bitrix\Catalog\EO_GroupAccess_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupAccess_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_GroupAccess fetchObject()
	 * @method \Bitrix\Catalog\EO_GroupAccess_Collection fetchCollection()
	 */
	class EO_GroupAccess_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_GroupAccess createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_GroupAccess_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_GroupAccess wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_GroupAccess_Collection wakeUpCollection($rows)
	 */
	class EO_GroupAccess_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\GroupLangTable:catalog\lib\grouplang.php */
namespace Bitrix\Catalog {
	/**
	 * EO_GroupLang
	 * @see \Bitrix\Catalog\GroupLangTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_GroupLang setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_GroupLang setCatalogGroupId(\int|\Bitrix\Main\DB\SqlExpression $catalogGroupId)
	 * @method bool hasCatalogGroupId()
	 * @method bool isCatalogGroupIdFilled()
	 * @method bool isCatalogGroupIdChanged()
	 * @method \int remindActualCatalogGroupId()
	 * @method \int requireCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_GroupLang resetCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_GroupLang unsetCatalogGroupId()
	 * @method \int fillCatalogGroupId()
	 * @method \string getLang()
	 * @method \Bitrix\Catalog\EO_GroupLang setLang(\string|\Bitrix\Main\DB\SqlExpression $lang)
	 * @method bool hasLang()
	 * @method bool isLangFilled()
	 * @method bool isLangChanged()
	 * @method \string remindActualLang()
	 * @method \string requireLang()
	 * @method \Bitrix\Catalog\EO_GroupLang resetLang()
	 * @method \Bitrix\Catalog\EO_GroupLang unsetLang()
	 * @method \string fillLang()
	 * @method \string getName()
	 * @method \Bitrix\Catalog\EO_GroupLang setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Catalog\EO_GroupLang resetName()
	 * @method \Bitrix\Catalog\EO_GroupLang unsetName()
	 * @method \string fillName()
	 * @method \Bitrix\Catalog\EO_Group getCatalogGroup()
	 * @method \Bitrix\Catalog\EO_Group remindActualCatalogGroup()
	 * @method \Bitrix\Catalog\EO_Group requireCatalogGroup()
	 * @method \Bitrix\Catalog\EO_GroupLang setCatalogGroup(\Bitrix\Catalog\EO_Group $object)
	 * @method \Bitrix\Catalog\EO_GroupLang resetCatalogGroup()
	 * @method \Bitrix\Catalog\EO_GroupLang unsetCatalogGroup()
	 * @method bool hasCatalogGroup()
	 * @method bool isCatalogGroupFilled()
	 * @method bool isCatalogGroupChanged()
	 * @method \Bitrix\Catalog\EO_Group fillCatalogGroup()
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
	 * @method \Bitrix\Catalog\EO_GroupLang set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_GroupLang reset($fieldName)
	 * @method \Bitrix\Catalog\EO_GroupLang unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_GroupLang wakeUp($data)
	 */
	class EO_GroupLang {
		/* @var \Bitrix\Catalog\GroupLangTable */
		static public $dataClass = '\Bitrix\Catalog\GroupLangTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_GroupLang_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getCatalogGroupIdList()
	 * @method \int[] fillCatalogGroupId()
	 * @method \string[] getLangList()
	 * @method \string[] fillLang()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \Bitrix\Catalog\EO_Group[] getCatalogGroupList()
	 * @method \Bitrix\Catalog\EO_GroupLang_Collection getCatalogGroupCollection()
	 * @method \Bitrix\Catalog\EO_Group_Collection fillCatalogGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_GroupLang $object)
	 * @method bool has(\Bitrix\Catalog\EO_GroupLang $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_GroupLang getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_GroupLang[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_GroupLang $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_GroupLang_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_GroupLang current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_GroupLang_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\GroupLangTable */
		static public $dataClass = '\Bitrix\Catalog\GroupLangTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupLang_Result exec()
	 * @method \Bitrix\Catalog\EO_GroupLang fetchObject()
	 * @method \Bitrix\Catalog\EO_GroupLang_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupLang_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_GroupLang fetchObject()
	 * @method \Bitrix\Catalog\EO_GroupLang_Collection fetchCollection()
	 */
	class EO_GroupLang_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_GroupLang createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_GroupLang_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_GroupLang wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_GroupLang_Collection wakeUpCollection($rows)
	 */
	class EO_GroupLang_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\MeasureTable:catalog\lib\measure.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Measure
	 * @see \Bitrix\Catalog\MeasureTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Measure setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getCode()
	 * @method \Bitrix\Catalog\EO_Measure setCode(\int|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \int remindActualCode()
	 * @method \int requireCode()
	 * @method \Bitrix\Catalog\EO_Measure resetCode()
	 * @method \Bitrix\Catalog\EO_Measure unsetCode()
	 * @method \int fillCode()
	 * @method \string getMeasureTitle()
	 * @method \Bitrix\Catalog\EO_Measure setMeasureTitle(\string|\Bitrix\Main\DB\SqlExpression $measureTitle)
	 * @method bool hasMeasureTitle()
	 * @method bool isMeasureTitleFilled()
	 * @method bool isMeasureTitleChanged()
	 * @method \string remindActualMeasureTitle()
	 * @method \string requireMeasureTitle()
	 * @method \Bitrix\Catalog\EO_Measure resetMeasureTitle()
	 * @method \Bitrix\Catalog\EO_Measure unsetMeasureTitle()
	 * @method \string fillMeasureTitle()
	 * @method \string getSymbol()
	 * @method \Bitrix\Catalog\EO_Measure setSymbol(\string|\Bitrix\Main\DB\SqlExpression $symbol)
	 * @method bool hasSymbol()
	 * @method bool isSymbolFilled()
	 * @method bool isSymbolChanged()
	 * @method \string remindActualSymbol()
	 * @method \string requireSymbol()
	 * @method \Bitrix\Catalog\EO_Measure resetSymbol()
	 * @method \Bitrix\Catalog\EO_Measure unsetSymbol()
	 * @method \string fillSymbol()
	 * @method \string getSymbolIntl()
	 * @method \Bitrix\Catalog\EO_Measure setSymbolIntl(\string|\Bitrix\Main\DB\SqlExpression $symbolIntl)
	 * @method bool hasSymbolIntl()
	 * @method bool isSymbolIntlFilled()
	 * @method bool isSymbolIntlChanged()
	 * @method \string remindActualSymbolIntl()
	 * @method \string requireSymbolIntl()
	 * @method \Bitrix\Catalog\EO_Measure resetSymbolIntl()
	 * @method \Bitrix\Catalog\EO_Measure unsetSymbolIntl()
	 * @method \string fillSymbolIntl()
	 * @method \string getSymbolLetterIntl()
	 * @method \Bitrix\Catalog\EO_Measure setSymbolLetterIntl(\string|\Bitrix\Main\DB\SqlExpression $symbolLetterIntl)
	 * @method bool hasSymbolLetterIntl()
	 * @method bool isSymbolLetterIntlFilled()
	 * @method bool isSymbolLetterIntlChanged()
	 * @method \string remindActualSymbolLetterIntl()
	 * @method \string requireSymbolLetterIntl()
	 * @method \Bitrix\Catalog\EO_Measure resetSymbolLetterIntl()
	 * @method \Bitrix\Catalog\EO_Measure unsetSymbolLetterIntl()
	 * @method \string fillSymbolLetterIntl()
	 * @method \boolean getIsDefault()
	 * @method \Bitrix\Catalog\EO_Measure setIsDefault(\boolean|\Bitrix\Main\DB\SqlExpression $isDefault)
	 * @method bool hasIsDefault()
	 * @method bool isIsDefaultFilled()
	 * @method bool isIsDefaultChanged()
	 * @method \boolean remindActualIsDefault()
	 * @method \boolean requireIsDefault()
	 * @method \Bitrix\Catalog\EO_Measure resetIsDefault()
	 * @method \Bitrix\Catalog\EO_Measure unsetIsDefault()
	 * @method \boolean fillIsDefault()
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
	 * @method \Bitrix\Catalog\EO_Measure set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Measure reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Measure unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Measure wakeUp($data)
	 */
	class EO_Measure {
		/* @var \Bitrix\Catalog\MeasureTable */
		static public $dataClass = '\Bitrix\Catalog\MeasureTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Measure_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getCodeList()
	 * @method \int[] fillCode()
	 * @method \string[] getMeasureTitleList()
	 * @method \string[] fillMeasureTitle()
	 * @method \string[] getSymbolList()
	 * @method \string[] fillSymbol()
	 * @method \string[] getSymbolIntlList()
	 * @method \string[] fillSymbolIntl()
	 * @method \string[] getSymbolLetterIntlList()
	 * @method \string[] fillSymbolLetterIntl()
	 * @method \boolean[] getIsDefaultList()
	 * @method \boolean[] fillIsDefault()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Measure $object)
	 * @method bool has(\Bitrix\Catalog\EO_Measure $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Measure getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Measure[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Measure $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Measure_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Measure current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Measure_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\MeasureTable */
		static public $dataClass = '\Bitrix\Catalog\MeasureTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Measure_Result exec()
	 * @method \Bitrix\Catalog\EO_Measure fetchObject()
	 * @method \Bitrix\Catalog\EO_Measure_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Measure_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Measure fetchObject()
	 * @method \Bitrix\Catalog\EO_Measure_Collection fetchCollection()
	 */
	class EO_Measure_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Measure createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Measure_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Measure wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Measure_Collection wakeUpCollection($rows)
	 */
	class EO_Measure_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\MeasureRatioTable:catalog\lib\measureratio.php */
namespace Bitrix\Catalog {
	/**
	 * EO_MeasureRatio
	 * @see \Bitrix\Catalog\MeasureRatioTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_MeasureRatio setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getProductId()
	 * @method \Bitrix\Catalog\EO_MeasureRatio setProductId(\int|\Bitrix\Main\DB\SqlExpression $productId)
	 * @method bool hasProductId()
	 * @method bool isProductIdFilled()
	 * @method bool isProductIdChanged()
	 * @method \int remindActualProductId()
	 * @method \int requireProductId()
	 * @method \Bitrix\Catalog\EO_MeasureRatio resetProductId()
	 * @method \Bitrix\Catalog\EO_MeasureRatio unsetProductId()
	 * @method \int fillProductId()
	 * @method \float getRatio()
	 * @method \Bitrix\Catalog\EO_MeasureRatio setRatio(\float|\Bitrix\Main\DB\SqlExpression $ratio)
	 * @method bool hasRatio()
	 * @method bool isRatioFilled()
	 * @method bool isRatioChanged()
	 * @method \float remindActualRatio()
	 * @method \float requireRatio()
	 * @method \Bitrix\Catalog\EO_MeasureRatio resetRatio()
	 * @method \Bitrix\Catalog\EO_MeasureRatio unsetRatio()
	 * @method \float fillRatio()
	 * @method \boolean getIsDefault()
	 * @method \Bitrix\Catalog\EO_MeasureRatio setIsDefault(\boolean|\Bitrix\Main\DB\SqlExpression $isDefault)
	 * @method bool hasIsDefault()
	 * @method bool isIsDefaultFilled()
	 * @method bool isIsDefaultChanged()
	 * @method \boolean remindActualIsDefault()
	 * @method \boolean requireIsDefault()
	 * @method \Bitrix\Catalog\EO_MeasureRatio resetIsDefault()
	 * @method \Bitrix\Catalog\EO_MeasureRatio unsetIsDefault()
	 * @method \boolean fillIsDefault()
	 * @method \Bitrix\Catalog\EO_Product getProduct()
	 * @method \Bitrix\Catalog\EO_Product remindActualProduct()
	 * @method \Bitrix\Catalog\EO_Product requireProduct()
	 * @method \Bitrix\Catalog\EO_MeasureRatio setProduct(\Bitrix\Catalog\EO_Product $object)
	 * @method \Bitrix\Catalog\EO_MeasureRatio resetProduct()
	 * @method \Bitrix\Catalog\EO_MeasureRatio unsetProduct()
	 * @method bool hasProduct()
	 * @method bool isProductFilled()
	 * @method bool isProductChanged()
	 * @method \Bitrix\Catalog\EO_Product fillProduct()
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
	 * @method \Bitrix\Catalog\EO_MeasureRatio set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_MeasureRatio reset($fieldName)
	 * @method \Bitrix\Catalog\EO_MeasureRatio unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_MeasureRatio wakeUp($data)
	 */
	class EO_MeasureRatio {
		/* @var \Bitrix\Catalog\MeasureRatioTable */
		static public $dataClass = '\Bitrix\Catalog\MeasureRatioTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_MeasureRatio_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getProductIdList()
	 * @method \int[] fillProductId()
	 * @method \float[] getRatioList()
	 * @method \float[] fillRatio()
	 * @method \boolean[] getIsDefaultList()
	 * @method \boolean[] fillIsDefault()
	 * @method \Bitrix\Catalog\EO_Product[] getProductList()
	 * @method \Bitrix\Catalog\EO_MeasureRatio_Collection getProductCollection()
	 * @method \Bitrix\Catalog\EO_Product_Collection fillProduct()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_MeasureRatio $object)
	 * @method bool has(\Bitrix\Catalog\EO_MeasureRatio $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_MeasureRatio getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_MeasureRatio[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_MeasureRatio $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_MeasureRatio_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_MeasureRatio current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_MeasureRatio_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\MeasureRatioTable */
		static public $dataClass = '\Bitrix\Catalog\MeasureRatioTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_MeasureRatio_Result exec()
	 * @method \Bitrix\Catalog\EO_MeasureRatio fetchObject()
	 * @method \Bitrix\Catalog\EO_MeasureRatio_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_MeasureRatio_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_MeasureRatio fetchObject()
	 * @method \Bitrix\Catalog\EO_MeasureRatio_Collection fetchCollection()
	 */
	class EO_MeasureRatio_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_MeasureRatio createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_MeasureRatio_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_MeasureRatio wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_MeasureRatio_Collection wakeUpCollection($rows)
	 */
	class EO_MeasureRatio_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\PriceTable:catalog\lib\price.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Price
	 * @see \Bitrix\Catalog\PriceTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Price setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getProductId()
	 * @method \Bitrix\Catalog\EO_Price setProductId(\int|\Bitrix\Main\DB\SqlExpression $productId)
	 * @method bool hasProductId()
	 * @method bool isProductIdFilled()
	 * @method bool isProductIdChanged()
	 * @method \int remindActualProductId()
	 * @method \int requireProductId()
	 * @method \Bitrix\Catalog\EO_Price resetProductId()
	 * @method \Bitrix\Catalog\EO_Price unsetProductId()
	 * @method \int fillProductId()
	 * @method \int getExtraId()
	 * @method \Bitrix\Catalog\EO_Price setExtraId(\int|\Bitrix\Main\DB\SqlExpression $extraId)
	 * @method bool hasExtraId()
	 * @method bool isExtraIdFilled()
	 * @method bool isExtraIdChanged()
	 * @method \int remindActualExtraId()
	 * @method \int requireExtraId()
	 * @method \Bitrix\Catalog\EO_Price resetExtraId()
	 * @method \Bitrix\Catalog\EO_Price unsetExtraId()
	 * @method \int fillExtraId()
	 * @method \int getCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_Price setCatalogGroupId(\int|\Bitrix\Main\DB\SqlExpression $catalogGroupId)
	 * @method bool hasCatalogGroupId()
	 * @method bool isCatalogGroupIdFilled()
	 * @method bool isCatalogGroupIdChanged()
	 * @method \int remindActualCatalogGroupId()
	 * @method \int requireCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_Price resetCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_Price unsetCatalogGroupId()
	 * @method \int fillCatalogGroupId()
	 * @method \float getPrice()
	 * @method \Bitrix\Catalog\EO_Price setPrice(\float|\Bitrix\Main\DB\SqlExpression $price)
	 * @method bool hasPrice()
	 * @method bool isPriceFilled()
	 * @method bool isPriceChanged()
	 * @method \float remindActualPrice()
	 * @method \float requirePrice()
	 * @method \Bitrix\Catalog\EO_Price resetPrice()
	 * @method \Bitrix\Catalog\EO_Price unsetPrice()
	 * @method \float fillPrice()
	 * @method \string getCurrency()
	 * @method \Bitrix\Catalog\EO_Price setCurrency(\string|\Bitrix\Main\DB\SqlExpression $currency)
	 * @method bool hasCurrency()
	 * @method bool isCurrencyFilled()
	 * @method bool isCurrencyChanged()
	 * @method \string remindActualCurrency()
	 * @method \string requireCurrency()
	 * @method \Bitrix\Catalog\EO_Price resetCurrency()
	 * @method \Bitrix\Catalog\EO_Price unsetCurrency()
	 * @method \string fillCurrency()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Catalog\EO_Price setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Catalog\EO_Price resetTimestampX()
	 * @method \Bitrix\Catalog\EO_Price unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getQuantityFrom()
	 * @method \Bitrix\Catalog\EO_Price setQuantityFrom(\int|\Bitrix\Main\DB\SqlExpression $quantityFrom)
	 * @method bool hasQuantityFrom()
	 * @method bool isQuantityFromFilled()
	 * @method bool isQuantityFromChanged()
	 * @method \int remindActualQuantityFrom()
	 * @method \int requireQuantityFrom()
	 * @method \Bitrix\Catalog\EO_Price resetQuantityFrom()
	 * @method \Bitrix\Catalog\EO_Price unsetQuantityFrom()
	 * @method \int fillQuantityFrom()
	 * @method \int getQuantityTo()
	 * @method \Bitrix\Catalog\EO_Price setQuantityTo(\int|\Bitrix\Main\DB\SqlExpression $quantityTo)
	 * @method bool hasQuantityTo()
	 * @method bool isQuantityToFilled()
	 * @method bool isQuantityToChanged()
	 * @method \int remindActualQuantityTo()
	 * @method \int requireQuantityTo()
	 * @method \Bitrix\Catalog\EO_Price resetQuantityTo()
	 * @method \Bitrix\Catalog\EO_Price unsetQuantityTo()
	 * @method \int fillQuantityTo()
	 * @method \string getTmpId()
	 * @method \Bitrix\Catalog\EO_Price setTmpId(\string|\Bitrix\Main\DB\SqlExpression $tmpId)
	 * @method bool hasTmpId()
	 * @method bool isTmpIdFilled()
	 * @method bool isTmpIdChanged()
	 * @method \string remindActualTmpId()
	 * @method \string requireTmpId()
	 * @method \Bitrix\Catalog\EO_Price resetTmpId()
	 * @method \Bitrix\Catalog\EO_Price unsetTmpId()
	 * @method \string fillTmpId()
	 * @method \float getPriceScale()
	 * @method \Bitrix\Catalog\EO_Price setPriceScale(\float|\Bitrix\Main\DB\SqlExpression $priceScale)
	 * @method bool hasPriceScale()
	 * @method bool isPriceScaleFilled()
	 * @method bool isPriceScaleChanged()
	 * @method \float remindActualPriceScale()
	 * @method \float requirePriceScale()
	 * @method \Bitrix\Catalog\EO_Price resetPriceScale()
	 * @method \Bitrix\Catalog\EO_Price unsetPriceScale()
	 * @method \float fillPriceScale()
	 * @method \Bitrix\Catalog\EO_Group getCatalogGroup()
	 * @method \Bitrix\Catalog\EO_Group remindActualCatalogGroup()
	 * @method \Bitrix\Catalog\EO_Group requireCatalogGroup()
	 * @method \Bitrix\Catalog\EO_Price setCatalogGroup(\Bitrix\Catalog\EO_Group $object)
	 * @method \Bitrix\Catalog\EO_Price resetCatalogGroup()
	 * @method \Bitrix\Catalog\EO_Price unsetCatalogGroup()
	 * @method bool hasCatalogGroup()
	 * @method bool isCatalogGroupFilled()
	 * @method bool isCatalogGroupChanged()
	 * @method \Bitrix\Catalog\EO_Group fillCatalogGroup()
	 * @method \Bitrix\Iblock\EO_Element getElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualElement()
	 * @method \Bitrix\Iblock\EO_Element requireElement()
	 * @method \Bitrix\Catalog\EO_Price setElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Catalog\EO_Price resetElement()
	 * @method \Bitrix\Catalog\EO_Price unsetElement()
	 * @method bool hasElement()
	 * @method bool isElementFilled()
	 * @method bool isElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillElement()
	 * @method \Bitrix\Catalog\EO_Product getProduct()
	 * @method \Bitrix\Catalog\EO_Product remindActualProduct()
	 * @method \Bitrix\Catalog\EO_Product requireProduct()
	 * @method \Bitrix\Catalog\EO_Price setProduct(\Bitrix\Catalog\EO_Product $object)
	 * @method \Bitrix\Catalog\EO_Price resetProduct()
	 * @method \Bitrix\Catalog\EO_Price unsetProduct()
	 * @method bool hasProduct()
	 * @method bool isProductFilled()
	 * @method bool isProductChanged()
	 * @method \Bitrix\Catalog\EO_Product fillProduct()
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
	 * @method \Bitrix\Catalog\EO_Price set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Price reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Price unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Price wakeUp($data)
	 */
	class EO_Price {
		/* @var \Bitrix\Catalog\PriceTable */
		static public $dataClass = '\Bitrix\Catalog\PriceTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Price_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getProductIdList()
	 * @method \int[] fillProductId()
	 * @method \int[] getExtraIdList()
	 * @method \int[] fillExtraId()
	 * @method \int[] getCatalogGroupIdList()
	 * @method \int[] fillCatalogGroupId()
	 * @method \float[] getPriceList()
	 * @method \float[] fillPrice()
	 * @method \string[] getCurrencyList()
	 * @method \string[] fillCurrency()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getQuantityFromList()
	 * @method \int[] fillQuantityFrom()
	 * @method \int[] getQuantityToList()
	 * @method \int[] fillQuantityTo()
	 * @method \string[] getTmpIdList()
	 * @method \string[] fillTmpId()
	 * @method \float[] getPriceScaleList()
	 * @method \float[] fillPriceScale()
	 * @method \Bitrix\Catalog\EO_Group[] getCatalogGroupList()
	 * @method \Bitrix\Catalog\EO_Price_Collection getCatalogGroupCollection()
	 * @method \Bitrix\Catalog\EO_Group_Collection fillCatalogGroup()
	 * @method \Bitrix\Iblock\EO_Element[] getElementList()
	 * @method \Bitrix\Catalog\EO_Price_Collection getElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillElement()
	 * @method \Bitrix\Catalog\EO_Product[] getProductList()
	 * @method \Bitrix\Catalog\EO_Price_Collection getProductCollection()
	 * @method \Bitrix\Catalog\EO_Product_Collection fillProduct()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Price $object)
	 * @method bool has(\Bitrix\Catalog\EO_Price $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Price getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Price[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Price $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Price_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Price current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Price_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\PriceTable */
		static public $dataClass = '\Bitrix\Catalog\PriceTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Price_Result exec()
	 * @method \Bitrix\Catalog\EO_Price fetchObject()
	 * @method \Bitrix\Catalog\EO_Price_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Price_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Price fetchObject()
	 * @method \Bitrix\Catalog\EO_Price_Collection fetchCollection()
	 */
	class EO_Price_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Price createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Price_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Price wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Price_Collection wakeUpCollection($rows)
	 */
	class EO_Price_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\ProductTable:catalog\lib\product.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Product
	 * @see \Bitrix\Catalog\ProductTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Product setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \float getQuantity()
	 * @method \Bitrix\Catalog\EO_Product setQuantity(\float|\Bitrix\Main\DB\SqlExpression $quantity)
	 * @method bool hasQuantity()
	 * @method bool isQuantityFilled()
	 * @method bool isQuantityChanged()
	 * @method \float remindActualQuantity()
	 * @method \float requireQuantity()
	 * @method \Bitrix\Catalog\EO_Product resetQuantity()
	 * @method \Bitrix\Catalog\EO_Product unsetQuantity()
	 * @method \float fillQuantity()
	 * @method \string getQuantityTrace()
	 * @method \Bitrix\Catalog\EO_Product setQuantityTrace(\string|\Bitrix\Main\DB\SqlExpression $quantityTrace)
	 * @method bool hasQuantityTrace()
	 * @method bool isQuantityTraceFilled()
	 * @method bool isQuantityTraceChanged()
	 * @method \string remindActualQuantityTrace()
	 * @method \string requireQuantityTrace()
	 * @method \Bitrix\Catalog\EO_Product resetQuantityTrace()
	 * @method \Bitrix\Catalog\EO_Product unsetQuantityTrace()
	 * @method \string fillQuantityTrace()
	 * @method \string getQuantityTraceOrig()
	 * @method \string remindActualQuantityTraceOrig()
	 * @method \string requireQuantityTraceOrig()
	 * @method bool hasQuantityTraceOrig()
	 * @method bool isQuantityTraceOrigFilled()
	 * @method \Bitrix\Catalog\EO_Product unsetQuantityTraceOrig()
	 * @method \string fillQuantityTraceOrig()
	 * @method \float getWeight()
	 * @method \Bitrix\Catalog\EO_Product setWeight(\float|\Bitrix\Main\DB\SqlExpression $weight)
	 * @method bool hasWeight()
	 * @method bool isWeightFilled()
	 * @method bool isWeightChanged()
	 * @method \float remindActualWeight()
	 * @method \float requireWeight()
	 * @method \Bitrix\Catalog\EO_Product resetWeight()
	 * @method \Bitrix\Catalog\EO_Product unsetWeight()
	 * @method \float fillWeight()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Catalog\EO_Product setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Catalog\EO_Product resetTimestampX()
	 * @method \Bitrix\Catalog\EO_Product unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getPriceType()
	 * @method \Bitrix\Catalog\EO_Product setPriceType(\string|\Bitrix\Main\DB\SqlExpression $priceType)
	 * @method bool hasPriceType()
	 * @method bool isPriceTypeFilled()
	 * @method bool isPriceTypeChanged()
	 * @method \string remindActualPriceType()
	 * @method \string requirePriceType()
	 * @method \Bitrix\Catalog\EO_Product resetPriceType()
	 * @method \Bitrix\Catalog\EO_Product unsetPriceType()
	 * @method \string fillPriceType()
	 * @method \int getRecurSchemeLength()
	 * @method \Bitrix\Catalog\EO_Product setRecurSchemeLength(\int|\Bitrix\Main\DB\SqlExpression $recurSchemeLength)
	 * @method bool hasRecurSchemeLength()
	 * @method bool isRecurSchemeLengthFilled()
	 * @method bool isRecurSchemeLengthChanged()
	 * @method \int remindActualRecurSchemeLength()
	 * @method \int requireRecurSchemeLength()
	 * @method \Bitrix\Catalog\EO_Product resetRecurSchemeLength()
	 * @method \Bitrix\Catalog\EO_Product unsetRecurSchemeLength()
	 * @method \int fillRecurSchemeLength()
	 * @method \string getRecurSchemeType()
	 * @method \Bitrix\Catalog\EO_Product setRecurSchemeType(\string|\Bitrix\Main\DB\SqlExpression $recurSchemeType)
	 * @method bool hasRecurSchemeType()
	 * @method bool isRecurSchemeTypeFilled()
	 * @method bool isRecurSchemeTypeChanged()
	 * @method \string remindActualRecurSchemeType()
	 * @method \string requireRecurSchemeType()
	 * @method \Bitrix\Catalog\EO_Product resetRecurSchemeType()
	 * @method \Bitrix\Catalog\EO_Product unsetRecurSchemeType()
	 * @method \string fillRecurSchemeType()
	 * @method \int getTrialPriceId()
	 * @method \Bitrix\Catalog\EO_Product setTrialPriceId(\int|\Bitrix\Main\DB\SqlExpression $trialPriceId)
	 * @method bool hasTrialPriceId()
	 * @method bool isTrialPriceIdFilled()
	 * @method bool isTrialPriceIdChanged()
	 * @method \int remindActualTrialPriceId()
	 * @method \int requireTrialPriceId()
	 * @method \Bitrix\Catalog\EO_Product resetTrialPriceId()
	 * @method \Bitrix\Catalog\EO_Product unsetTrialPriceId()
	 * @method \int fillTrialPriceId()
	 * @method \boolean getWithoutOrder()
	 * @method \Bitrix\Catalog\EO_Product setWithoutOrder(\boolean|\Bitrix\Main\DB\SqlExpression $withoutOrder)
	 * @method bool hasWithoutOrder()
	 * @method bool isWithoutOrderFilled()
	 * @method bool isWithoutOrderChanged()
	 * @method \boolean remindActualWithoutOrder()
	 * @method \boolean requireWithoutOrder()
	 * @method \Bitrix\Catalog\EO_Product resetWithoutOrder()
	 * @method \Bitrix\Catalog\EO_Product unsetWithoutOrder()
	 * @method \boolean fillWithoutOrder()
	 * @method \boolean getSelectBestPrice()
	 * @method \Bitrix\Catalog\EO_Product setSelectBestPrice(\boolean|\Bitrix\Main\DB\SqlExpression $selectBestPrice)
	 * @method bool hasSelectBestPrice()
	 * @method bool isSelectBestPriceFilled()
	 * @method bool isSelectBestPriceChanged()
	 * @method \boolean remindActualSelectBestPrice()
	 * @method \boolean requireSelectBestPrice()
	 * @method \Bitrix\Catalog\EO_Product resetSelectBestPrice()
	 * @method \Bitrix\Catalog\EO_Product unsetSelectBestPrice()
	 * @method \boolean fillSelectBestPrice()
	 * @method \int getVatId()
	 * @method \Bitrix\Catalog\EO_Product setVatId(\int|\Bitrix\Main\DB\SqlExpression $vatId)
	 * @method bool hasVatId()
	 * @method bool isVatIdFilled()
	 * @method bool isVatIdChanged()
	 * @method \int remindActualVatId()
	 * @method \int requireVatId()
	 * @method \Bitrix\Catalog\EO_Product resetVatId()
	 * @method \Bitrix\Catalog\EO_Product unsetVatId()
	 * @method \int fillVatId()
	 * @method \boolean getVatIncluded()
	 * @method \Bitrix\Catalog\EO_Product setVatIncluded(\boolean|\Bitrix\Main\DB\SqlExpression $vatIncluded)
	 * @method bool hasVatIncluded()
	 * @method bool isVatIncludedFilled()
	 * @method bool isVatIncludedChanged()
	 * @method \boolean remindActualVatIncluded()
	 * @method \boolean requireVatIncluded()
	 * @method \Bitrix\Catalog\EO_Product resetVatIncluded()
	 * @method \Bitrix\Catalog\EO_Product unsetVatIncluded()
	 * @method \boolean fillVatIncluded()
	 * @method \string getCanBuyZero()
	 * @method \Bitrix\Catalog\EO_Product setCanBuyZero(\string|\Bitrix\Main\DB\SqlExpression $canBuyZero)
	 * @method bool hasCanBuyZero()
	 * @method bool isCanBuyZeroFilled()
	 * @method bool isCanBuyZeroChanged()
	 * @method \string remindActualCanBuyZero()
	 * @method \string requireCanBuyZero()
	 * @method \Bitrix\Catalog\EO_Product resetCanBuyZero()
	 * @method \Bitrix\Catalog\EO_Product unsetCanBuyZero()
	 * @method \string fillCanBuyZero()
	 * @method \string getCanBuyZeroOrig()
	 * @method \string remindActualCanBuyZeroOrig()
	 * @method \string requireCanBuyZeroOrig()
	 * @method bool hasCanBuyZeroOrig()
	 * @method bool isCanBuyZeroOrigFilled()
	 * @method \Bitrix\Catalog\EO_Product unsetCanBuyZeroOrig()
	 * @method \string fillCanBuyZeroOrig()
	 * @method \string getNegativeAmountTrace()
	 * @method \Bitrix\Catalog\EO_Product setNegativeAmountTrace(\string|\Bitrix\Main\DB\SqlExpression $negativeAmountTrace)
	 * @method bool hasNegativeAmountTrace()
	 * @method bool isNegativeAmountTraceFilled()
	 * @method bool isNegativeAmountTraceChanged()
	 * @method \string remindActualNegativeAmountTrace()
	 * @method \string requireNegativeAmountTrace()
	 * @method \Bitrix\Catalog\EO_Product resetNegativeAmountTrace()
	 * @method \Bitrix\Catalog\EO_Product unsetNegativeAmountTrace()
	 * @method \string fillNegativeAmountTrace()
	 * @method \string getNegativeAmountTraceOrig()
	 * @method \string remindActualNegativeAmountTraceOrig()
	 * @method \string requireNegativeAmountTraceOrig()
	 * @method bool hasNegativeAmountTraceOrig()
	 * @method bool isNegativeAmountTraceOrigFilled()
	 * @method \Bitrix\Catalog\EO_Product unsetNegativeAmountTraceOrig()
	 * @method \string fillNegativeAmountTraceOrig()
	 * @method \string getTmpId()
	 * @method \Bitrix\Catalog\EO_Product setTmpId(\string|\Bitrix\Main\DB\SqlExpression $tmpId)
	 * @method bool hasTmpId()
	 * @method bool isTmpIdFilled()
	 * @method bool isTmpIdChanged()
	 * @method \string remindActualTmpId()
	 * @method \string requireTmpId()
	 * @method \Bitrix\Catalog\EO_Product resetTmpId()
	 * @method \Bitrix\Catalog\EO_Product unsetTmpId()
	 * @method \string fillTmpId()
	 * @method \float getPurchasingPrice()
	 * @method \Bitrix\Catalog\EO_Product setPurchasingPrice(\float|\Bitrix\Main\DB\SqlExpression $purchasingPrice)
	 * @method bool hasPurchasingPrice()
	 * @method bool isPurchasingPriceFilled()
	 * @method bool isPurchasingPriceChanged()
	 * @method \float remindActualPurchasingPrice()
	 * @method \float requirePurchasingPrice()
	 * @method \Bitrix\Catalog\EO_Product resetPurchasingPrice()
	 * @method \Bitrix\Catalog\EO_Product unsetPurchasingPrice()
	 * @method \float fillPurchasingPrice()
	 * @method \string getPurchasingCurrency()
	 * @method \Bitrix\Catalog\EO_Product setPurchasingCurrency(\string|\Bitrix\Main\DB\SqlExpression $purchasingCurrency)
	 * @method bool hasPurchasingCurrency()
	 * @method bool isPurchasingCurrencyFilled()
	 * @method bool isPurchasingCurrencyChanged()
	 * @method \string remindActualPurchasingCurrency()
	 * @method \string requirePurchasingCurrency()
	 * @method \Bitrix\Catalog\EO_Product resetPurchasingCurrency()
	 * @method \Bitrix\Catalog\EO_Product unsetPurchasingCurrency()
	 * @method \string fillPurchasingCurrency()
	 * @method \boolean getBarcodeMulti()
	 * @method \Bitrix\Catalog\EO_Product setBarcodeMulti(\boolean|\Bitrix\Main\DB\SqlExpression $barcodeMulti)
	 * @method bool hasBarcodeMulti()
	 * @method bool isBarcodeMultiFilled()
	 * @method bool isBarcodeMultiChanged()
	 * @method \boolean remindActualBarcodeMulti()
	 * @method \boolean requireBarcodeMulti()
	 * @method \Bitrix\Catalog\EO_Product resetBarcodeMulti()
	 * @method \Bitrix\Catalog\EO_Product unsetBarcodeMulti()
	 * @method \boolean fillBarcodeMulti()
	 * @method \float getQuantityReserved()
	 * @method \Bitrix\Catalog\EO_Product setQuantityReserved(\float|\Bitrix\Main\DB\SqlExpression $quantityReserved)
	 * @method bool hasQuantityReserved()
	 * @method bool isQuantityReservedFilled()
	 * @method bool isQuantityReservedChanged()
	 * @method \float remindActualQuantityReserved()
	 * @method \float requireQuantityReserved()
	 * @method \Bitrix\Catalog\EO_Product resetQuantityReserved()
	 * @method \Bitrix\Catalog\EO_Product unsetQuantityReserved()
	 * @method \float fillQuantityReserved()
	 * @method \string getSubscribe()
	 * @method \Bitrix\Catalog\EO_Product setSubscribe(\string|\Bitrix\Main\DB\SqlExpression $subscribe)
	 * @method bool hasSubscribe()
	 * @method bool isSubscribeFilled()
	 * @method bool isSubscribeChanged()
	 * @method \string remindActualSubscribe()
	 * @method \string requireSubscribe()
	 * @method \Bitrix\Catalog\EO_Product resetSubscribe()
	 * @method \Bitrix\Catalog\EO_Product unsetSubscribe()
	 * @method \string fillSubscribe()
	 * @method \string getSubscribeOrig()
	 * @method \string remindActualSubscribeOrig()
	 * @method \string requireSubscribeOrig()
	 * @method bool hasSubscribeOrig()
	 * @method bool isSubscribeOrigFilled()
	 * @method \Bitrix\Catalog\EO_Product unsetSubscribeOrig()
	 * @method \string fillSubscribeOrig()
	 * @method \float getWidth()
	 * @method \Bitrix\Catalog\EO_Product setWidth(\float|\Bitrix\Main\DB\SqlExpression $width)
	 * @method bool hasWidth()
	 * @method bool isWidthFilled()
	 * @method bool isWidthChanged()
	 * @method \float remindActualWidth()
	 * @method \float requireWidth()
	 * @method \Bitrix\Catalog\EO_Product resetWidth()
	 * @method \Bitrix\Catalog\EO_Product unsetWidth()
	 * @method \float fillWidth()
	 * @method \float getLength()
	 * @method \Bitrix\Catalog\EO_Product setLength(\float|\Bitrix\Main\DB\SqlExpression $length)
	 * @method bool hasLength()
	 * @method bool isLengthFilled()
	 * @method bool isLengthChanged()
	 * @method \float remindActualLength()
	 * @method \float requireLength()
	 * @method \Bitrix\Catalog\EO_Product resetLength()
	 * @method \Bitrix\Catalog\EO_Product unsetLength()
	 * @method \float fillLength()
	 * @method \float getHeight()
	 * @method \Bitrix\Catalog\EO_Product setHeight(\float|\Bitrix\Main\DB\SqlExpression $height)
	 * @method bool hasHeight()
	 * @method bool isHeightFilled()
	 * @method bool isHeightChanged()
	 * @method \float remindActualHeight()
	 * @method \float requireHeight()
	 * @method \Bitrix\Catalog\EO_Product resetHeight()
	 * @method \Bitrix\Catalog\EO_Product unsetHeight()
	 * @method \float fillHeight()
	 * @method \int getMeasure()
	 * @method \Bitrix\Catalog\EO_Product setMeasure(\int|\Bitrix\Main\DB\SqlExpression $measure)
	 * @method bool hasMeasure()
	 * @method bool isMeasureFilled()
	 * @method bool isMeasureChanged()
	 * @method \int remindActualMeasure()
	 * @method \int requireMeasure()
	 * @method \Bitrix\Catalog\EO_Product resetMeasure()
	 * @method \Bitrix\Catalog\EO_Product unsetMeasure()
	 * @method \int fillMeasure()
	 * @method \string getType()
	 * @method \Bitrix\Catalog\EO_Product setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Catalog\EO_Product resetType()
	 * @method \Bitrix\Catalog\EO_Product unsetType()
	 * @method \string fillType()
	 * @method \boolean getAvailable()
	 * @method \Bitrix\Catalog\EO_Product setAvailable(\boolean|\Bitrix\Main\DB\SqlExpression $available)
	 * @method bool hasAvailable()
	 * @method bool isAvailableFilled()
	 * @method bool isAvailableChanged()
	 * @method \boolean remindActualAvailable()
	 * @method \boolean requireAvailable()
	 * @method \Bitrix\Catalog\EO_Product resetAvailable()
	 * @method \Bitrix\Catalog\EO_Product unsetAvailable()
	 * @method \boolean fillAvailable()
	 * @method \boolean getBundle()
	 * @method \Bitrix\Catalog\EO_Product setBundle(\boolean|\Bitrix\Main\DB\SqlExpression $bundle)
	 * @method bool hasBundle()
	 * @method bool isBundleFilled()
	 * @method bool isBundleChanged()
	 * @method \boolean remindActualBundle()
	 * @method \boolean requireBundle()
	 * @method \Bitrix\Catalog\EO_Product resetBundle()
	 * @method \Bitrix\Catalog\EO_Product unsetBundle()
	 * @method \boolean fillBundle()
	 * @method \Bitrix\Iblock\EO_Element getIblockElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualIblockElement()
	 * @method \Bitrix\Iblock\EO_Element requireIblockElement()
	 * @method \Bitrix\Catalog\EO_Product setIblockElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Catalog\EO_Product resetIblockElement()
	 * @method \Bitrix\Catalog\EO_Product unsetIblockElement()
	 * @method bool hasIblockElement()
	 * @method bool isIblockElementFilled()
	 * @method bool isIblockElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillIblockElement()
	 * @method \Bitrix\Iblock\EO_Element getTrialIblockElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualTrialIblockElement()
	 * @method \Bitrix\Iblock\EO_Element requireTrialIblockElement()
	 * @method \Bitrix\Catalog\EO_Product setTrialIblockElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Catalog\EO_Product resetTrialIblockElement()
	 * @method \Bitrix\Catalog\EO_Product unsetTrialIblockElement()
	 * @method bool hasTrialIblockElement()
	 * @method bool isTrialIblockElementFilled()
	 * @method bool isTrialIblockElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillTrialIblockElement()
	 * @method \Bitrix\Catalog\EO_Product getTrialProduct()
	 * @method \Bitrix\Catalog\EO_Product remindActualTrialProduct()
	 * @method \Bitrix\Catalog\EO_Product requireTrialProduct()
	 * @method \Bitrix\Catalog\EO_Product setTrialProduct(\Bitrix\Catalog\EO_Product $object)
	 * @method \Bitrix\Catalog\EO_Product resetTrialProduct()
	 * @method \Bitrix\Catalog\EO_Product unsetTrialProduct()
	 * @method bool hasTrialProduct()
	 * @method bool isTrialProductFilled()
	 * @method bool isTrialProductChanged()
	 * @method \Bitrix\Catalog\EO_Product fillTrialProduct()
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
	 * @method \Bitrix\Catalog\EO_Product set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Product reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Product unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Product wakeUp($data)
	 */
	class EO_Product {
		/* @var \Bitrix\Catalog\ProductTable */
		static public $dataClass = '\Bitrix\Catalog\ProductTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Product_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \float[] getQuantityList()
	 * @method \float[] fillQuantity()
	 * @method \string[] getQuantityTraceList()
	 * @method \string[] fillQuantityTrace()
	 * @method \string[] getQuantityTraceOrigList()
	 * @method \string[] fillQuantityTraceOrig()
	 * @method \float[] getWeightList()
	 * @method \float[] fillWeight()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getPriceTypeList()
	 * @method \string[] fillPriceType()
	 * @method \int[] getRecurSchemeLengthList()
	 * @method \int[] fillRecurSchemeLength()
	 * @method \string[] getRecurSchemeTypeList()
	 * @method \string[] fillRecurSchemeType()
	 * @method \int[] getTrialPriceIdList()
	 * @method \int[] fillTrialPriceId()
	 * @method \boolean[] getWithoutOrderList()
	 * @method \boolean[] fillWithoutOrder()
	 * @method \boolean[] getSelectBestPriceList()
	 * @method \boolean[] fillSelectBestPrice()
	 * @method \int[] getVatIdList()
	 * @method \int[] fillVatId()
	 * @method \boolean[] getVatIncludedList()
	 * @method \boolean[] fillVatIncluded()
	 * @method \string[] getCanBuyZeroList()
	 * @method \string[] fillCanBuyZero()
	 * @method \string[] getCanBuyZeroOrigList()
	 * @method \string[] fillCanBuyZeroOrig()
	 * @method \string[] getNegativeAmountTraceList()
	 * @method \string[] fillNegativeAmountTrace()
	 * @method \string[] getNegativeAmountTraceOrigList()
	 * @method \string[] fillNegativeAmountTraceOrig()
	 * @method \string[] getTmpIdList()
	 * @method \string[] fillTmpId()
	 * @method \float[] getPurchasingPriceList()
	 * @method \float[] fillPurchasingPrice()
	 * @method \string[] getPurchasingCurrencyList()
	 * @method \string[] fillPurchasingCurrency()
	 * @method \boolean[] getBarcodeMultiList()
	 * @method \boolean[] fillBarcodeMulti()
	 * @method \float[] getQuantityReservedList()
	 * @method \float[] fillQuantityReserved()
	 * @method \string[] getSubscribeList()
	 * @method \string[] fillSubscribe()
	 * @method \string[] getSubscribeOrigList()
	 * @method \string[] fillSubscribeOrig()
	 * @method \float[] getWidthList()
	 * @method \float[] fillWidth()
	 * @method \float[] getLengthList()
	 * @method \float[] fillLength()
	 * @method \float[] getHeightList()
	 * @method \float[] fillHeight()
	 * @method \int[] getMeasureList()
	 * @method \int[] fillMeasure()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \boolean[] getAvailableList()
	 * @method \boolean[] fillAvailable()
	 * @method \boolean[] getBundleList()
	 * @method \boolean[] fillBundle()
	 * @method \Bitrix\Iblock\EO_Element[] getIblockElementList()
	 * @method \Bitrix\Catalog\EO_Product_Collection getIblockElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillIblockElement()
	 * @method \Bitrix\Iblock\EO_Element[] getTrialIblockElementList()
	 * @method \Bitrix\Catalog\EO_Product_Collection getTrialIblockElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillTrialIblockElement()
	 * @method \Bitrix\Catalog\EO_Product[] getTrialProductList()
	 * @method \Bitrix\Catalog\EO_Product_Collection getTrialProductCollection()
	 * @method \Bitrix\Catalog\EO_Product_Collection fillTrialProduct()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Product $object)
	 * @method bool has(\Bitrix\Catalog\EO_Product $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Product getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Product[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Product $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Product_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Product current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Product_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\ProductTable */
		static public $dataClass = '\Bitrix\Catalog\ProductTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Product_Result exec()
	 * @method \Bitrix\Catalog\EO_Product fetchObject()
	 * @method \Bitrix\Catalog\EO_Product_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Product_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Product fetchObject()
	 * @method \Bitrix\Catalog\EO_Product_Collection fetchCollection()
	 */
	class EO_Product_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Product createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Product_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Product wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Product_Collection wakeUpCollection($rows)
	 */
	class EO_Product_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\ProductCompilationTable:catalog\lib\productcompilationtable.php */
namespace Bitrix\Catalog {
	/**
	 * EO_ProductCompilation
	 * @see \Bitrix\Catalog\ProductCompilationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDealId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation setDealId(\int|\Bitrix\Main\DB\SqlExpression $dealId)
	 * @method bool hasDealId()
	 * @method bool isDealIdFilled()
	 * @method bool isDealIdChanged()
	 * @method \int remindActualDealId()
	 * @method \int requireDealId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation resetDealId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation unsetDealId()
	 * @method \int fillDealId()
	 * @method \string getProductIds()
	 * @method \Bitrix\Catalog\EO_ProductCompilation setProductIds(\string|\Bitrix\Main\DB\SqlExpression $productIds)
	 * @method bool hasProductIds()
	 * @method bool isProductIdsFilled()
	 * @method bool isProductIdsChanged()
	 * @method \string remindActualProductIds()
	 * @method \string requireProductIds()
	 * @method \Bitrix\Catalog\EO_ProductCompilation resetProductIds()
	 * @method \Bitrix\Catalog\EO_ProductCompilation unsetProductIds()
	 * @method \string fillProductIds()
	 * @method \Bitrix\Main\Type\DateTime getCreationDate()
	 * @method \Bitrix\Catalog\EO_ProductCompilation setCreationDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $creationDate)
	 * @method bool hasCreationDate()
	 * @method bool isCreationDateFilled()
	 * @method bool isCreationDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreationDate()
	 * @method \Bitrix\Main\Type\DateTime requireCreationDate()
	 * @method \Bitrix\Catalog\EO_ProductCompilation resetCreationDate()
	 * @method \Bitrix\Catalog\EO_ProductCompilation unsetCreationDate()
	 * @method \Bitrix\Main\Type\DateTime fillCreationDate()
	 * @method \int getChatId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation setChatId(\int|\Bitrix\Main\DB\SqlExpression $chatId)
	 * @method bool hasChatId()
	 * @method bool isChatIdFilled()
	 * @method bool isChatIdChanged()
	 * @method \int remindActualChatId()
	 * @method \int requireChatId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation resetChatId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation unsetChatId()
	 * @method \int fillChatId()
	 * @method \int getQueueId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation setQueueId(\int|\Bitrix\Main\DB\SqlExpression $queueId)
	 * @method bool hasQueueId()
	 * @method bool isQueueIdFilled()
	 * @method bool isQueueIdChanged()
	 * @method \int remindActualQueueId()
	 * @method \int requireQueueId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation resetQueueId()
	 * @method \Bitrix\Catalog\EO_ProductCompilation unsetQueueId()
	 * @method \int fillQueueId()
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
	 * @method \Bitrix\Catalog\EO_ProductCompilation set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_ProductCompilation reset($fieldName)
	 * @method \Bitrix\Catalog\EO_ProductCompilation unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_ProductCompilation wakeUp($data)
	 */
	class EO_ProductCompilation {
		/* @var \Bitrix\Catalog\ProductCompilationTable */
		static public $dataClass = '\Bitrix\Catalog\ProductCompilationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_ProductCompilation_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDealIdList()
	 * @method \int[] fillDealId()
	 * @method \string[] getProductIdsList()
	 * @method \string[] fillProductIds()
	 * @method \Bitrix\Main\Type\DateTime[] getCreationDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreationDate()
	 * @method \int[] getChatIdList()
	 * @method \int[] fillChatId()
	 * @method \int[] getQueueIdList()
	 * @method \int[] fillQueueId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_ProductCompilation $object)
	 * @method bool has(\Bitrix\Catalog\EO_ProductCompilation $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_ProductCompilation getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_ProductCompilation[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_ProductCompilation $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_ProductCompilation_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_ProductCompilation current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ProductCompilation_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\ProductCompilationTable */
		static public $dataClass = '\Bitrix\Catalog\ProductCompilationTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ProductCompilation_Result exec()
	 * @method \Bitrix\Catalog\EO_ProductCompilation fetchObject()
	 * @method \Bitrix\Catalog\EO_ProductCompilation_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ProductCompilation_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_ProductCompilation fetchObject()
	 * @method \Bitrix\Catalog\EO_ProductCompilation_Collection fetchCollection()
	 */
	class EO_ProductCompilation_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_ProductCompilation createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_ProductCompilation_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_ProductCompilation wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_ProductCompilation_Collection wakeUpCollection($rows)
	 */
	class EO_ProductCompilation_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\ProductGroupAccessTable:catalog\lib\productgroupaccess.php */
namespace Bitrix\Catalog {
	/**
	 * EO_ProductGroupAccess
	 * @see \Bitrix\Catalog\ProductGroupAccessTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getProductId()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess setProductId(\int|\Bitrix\Main\DB\SqlExpression $productId)
	 * @method bool hasProductId()
	 * @method bool isProductIdFilled()
	 * @method bool isProductIdChanged()
	 * @method \int remindActualProductId()
	 * @method \int requireProductId()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess resetProductId()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess unsetProductId()
	 * @method \int fillProductId()
	 * @method \int getGroupId()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess resetGroupId()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \int getAccessLength()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess setAccessLength(\int|\Bitrix\Main\DB\SqlExpression $accessLength)
	 * @method bool hasAccessLength()
	 * @method bool isAccessLengthFilled()
	 * @method bool isAccessLengthChanged()
	 * @method \int remindActualAccessLength()
	 * @method \int requireAccessLength()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess resetAccessLength()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess unsetAccessLength()
	 * @method \int fillAccessLength()
	 * @method \string getAccessLengthType()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess setAccessLengthType(\string|\Bitrix\Main\DB\SqlExpression $accessLengthType)
	 * @method bool hasAccessLengthType()
	 * @method bool isAccessLengthTypeFilled()
	 * @method bool isAccessLengthTypeChanged()
	 * @method \string remindActualAccessLengthType()
	 * @method \string requireAccessLengthType()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess resetAccessLengthType()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess unsetAccessLengthType()
	 * @method \string fillAccessLengthType()
	 * @method \Bitrix\Catalog\EO_Product getProduct()
	 * @method \Bitrix\Catalog\EO_Product remindActualProduct()
	 * @method \Bitrix\Catalog\EO_Product requireProduct()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess setProduct(\Bitrix\Catalog\EO_Product $object)
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess resetProduct()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess unsetProduct()
	 * @method bool hasProduct()
	 * @method bool isProductFilled()
	 * @method bool isProductChanged()
	 * @method \Bitrix\Catalog\EO_Product fillProduct()
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
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess reset($fieldName)
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_ProductGroupAccess wakeUp($data)
	 */
	class EO_ProductGroupAccess {
		/* @var \Bitrix\Catalog\ProductGroupAccessTable */
		static public $dataClass = '\Bitrix\Catalog\ProductGroupAccessTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_ProductGroupAccess_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getProductIdList()
	 * @method \int[] fillProductId()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \int[] getAccessLengthList()
	 * @method \int[] fillAccessLength()
	 * @method \string[] getAccessLengthTypeList()
	 * @method \string[] fillAccessLengthType()
	 * @method \Bitrix\Catalog\EO_Product[] getProductList()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess_Collection getProductCollection()
	 * @method \Bitrix\Catalog\EO_Product_Collection fillProduct()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_ProductGroupAccess $object)
	 * @method bool has(\Bitrix\Catalog\EO_ProductGroupAccess $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_ProductGroupAccess $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_ProductGroupAccess_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ProductGroupAccess_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\ProductGroupAccessTable */
		static public $dataClass = '\Bitrix\Catalog\ProductGroupAccessTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ProductGroupAccess_Result exec()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess fetchObject()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ProductGroupAccess_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess fetchObject()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess_Collection fetchCollection()
	 */
	class EO_ProductGroupAccess_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_ProductGroupAccess_Collection wakeUpCollection($rows)
	 */
	class EO_ProductGroupAccess_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\RoundingTable:catalog\lib\rounding.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Rounding
	 * @see \Bitrix\Catalog\RoundingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Rounding setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_Rounding setCatalogGroupId(\int|\Bitrix\Main\DB\SqlExpression $catalogGroupId)
	 * @method bool hasCatalogGroupId()
	 * @method bool isCatalogGroupIdFilled()
	 * @method bool isCatalogGroupIdChanged()
	 * @method \int remindActualCatalogGroupId()
	 * @method \int requireCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_Rounding resetCatalogGroupId()
	 * @method \Bitrix\Catalog\EO_Rounding unsetCatalogGroupId()
	 * @method \int fillCatalogGroupId()
	 * @method \float getPrice()
	 * @method \Bitrix\Catalog\EO_Rounding setPrice(\float|\Bitrix\Main\DB\SqlExpression $price)
	 * @method bool hasPrice()
	 * @method bool isPriceFilled()
	 * @method bool isPriceChanged()
	 * @method \float remindActualPrice()
	 * @method \float requirePrice()
	 * @method \Bitrix\Catalog\EO_Rounding resetPrice()
	 * @method \Bitrix\Catalog\EO_Rounding unsetPrice()
	 * @method \float fillPrice()
	 * @method \string getRoundType()
	 * @method \Bitrix\Catalog\EO_Rounding setRoundType(\string|\Bitrix\Main\DB\SqlExpression $roundType)
	 * @method bool hasRoundType()
	 * @method bool isRoundTypeFilled()
	 * @method bool isRoundTypeChanged()
	 * @method \string remindActualRoundType()
	 * @method \string requireRoundType()
	 * @method \Bitrix\Catalog\EO_Rounding resetRoundType()
	 * @method \Bitrix\Catalog\EO_Rounding unsetRoundType()
	 * @method \string fillRoundType()
	 * @method \float getRoundPrecision()
	 * @method \Bitrix\Catalog\EO_Rounding setRoundPrecision(\float|\Bitrix\Main\DB\SqlExpression $roundPrecision)
	 * @method bool hasRoundPrecision()
	 * @method bool isRoundPrecisionFilled()
	 * @method bool isRoundPrecisionChanged()
	 * @method \float remindActualRoundPrecision()
	 * @method \float requireRoundPrecision()
	 * @method \Bitrix\Catalog\EO_Rounding resetRoundPrecision()
	 * @method \Bitrix\Catalog\EO_Rounding unsetRoundPrecision()
	 * @method \float fillRoundPrecision()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Catalog\EO_Rounding setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Catalog\EO_Rounding resetCreatedBy()
	 * @method \Bitrix\Catalog\EO_Rounding unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Catalog\EO_Rounding setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Catalog\EO_Rounding resetDateCreate()
	 * @method \Bitrix\Catalog\EO_Rounding unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Catalog\EO_Rounding setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Catalog\EO_Rounding resetModifiedBy()
	 * @method \Bitrix\Catalog\EO_Rounding unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Catalog\EO_Rounding setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Catalog\EO_Rounding resetDateModify()
	 * @method \Bitrix\Catalog\EO_Rounding unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Catalog\EO_Rounding setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_Rounding resetCreatedByUser()
	 * @method \Bitrix\Catalog\EO_Rounding unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Rounding setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_Rounding resetModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Rounding unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
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
	 * @method \Bitrix\Catalog\EO_Rounding set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Rounding reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Rounding unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Rounding wakeUp($data)
	 */
	class EO_Rounding {
		/* @var \Bitrix\Catalog\RoundingTable */
		static public $dataClass = '\Bitrix\Catalog\RoundingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Rounding_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getCatalogGroupIdList()
	 * @method \int[] fillCatalogGroupId()
	 * @method \float[] getPriceList()
	 * @method \float[] fillPrice()
	 * @method \string[] getRoundTypeList()
	 * @method \string[] fillRoundType()
	 * @method \float[] getRoundPrecisionList()
	 * @method \float[] fillRoundPrecision()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Catalog\EO_Rounding_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Catalog\EO_Rounding_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Rounding $object)
	 * @method bool has(\Bitrix\Catalog\EO_Rounding $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Rounding getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Rounding[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Rounding $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Rounding_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Rounding current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Rounding_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\RoundingTable */
		static public $dataClass = '\Bitrix\Catalog\RoundingTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Rounding_Result exec()
	 * @method \Bitrix\Catalog\EO_Rounding fetchObject()
	 * @method \Bitrix\Catalog\EO_Rounding_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Rounding_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Rounding fetchObject()
	 * @method \Bitrix\Catalog\EO_Rounding_Collection fetchCollection()
	 */
	class EO_Rounding_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Rounding createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Rounding_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Rounding wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Rounding_Collection wakeUpCollection($rows)
	 */
	class EO_Rounding_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\StoreTable:catalog\lib\store.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Store
	 * @see \Bitrix\Catalog\StoreTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Store setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getTitle()
	 * @method \Bitrix\Catalog\EO_Store setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Catalog\EO_Store resetTitle()
	 * @method \Bitrix\Catalog\EO_Store unsetTitle()
	 * @method \string fillTitle()
	 * @method \boolean getActive()
	 * @method \Bitrix\Catalog\EO_Store setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Catalog\EO_Store resetActive()
	 * @method \Bitrix\Catalog\EO_Store unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getAddress()
	 * @method \Bitrix\Catalog\EO_Store setAddress(\string|\Bitrix\Main\DB\SqlExpression $address)
	 * @method bool hasAddress()
	 * @method bool isAddressFilled()
	 * @method bool isAddressChanged()
	 * @method \string remindActualAddress()
	 * @method \string requireAddress()
	 * @method \Bitrix\Catalog\EO_Store resetAddress()
	 * @method \Bitrix\Catalog\EO_Store unsetAddress()
	 * @method \string fillAddress()
	 * @method \string getDescription()
	 * @method \Bitrix\Catalog\EO_Store setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Catalog\EO_Store resetDescription()
	 * @method \Bitrix\Catalog\EO_Store unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getGpsN()
	 * @method \Bitrix\Catalog\EO_Store setGpsN(\string|\Bitrix\Main\DB\SqlExpression $gpsN)
	 * @method bool hasGpsN()
	 * @method bool isGpsNFilled()
	 * @method bool isGpsNChanged()
	 * @method \string remindActualGpsN()
	 * @method \string requireGpsN()
	 * @method \Bitrix\Catalog\EO_Store resetGpsN()
	 * @method \Bitrix\Catalog\EO_Store unsetGpsN()
	 * @method \string fillGpsN()
	 * @method \string getGpsS()
	 * @method \Bitrix\Catalog\EO_Store setGpsS(\string|\Bitrix\Main\DB\SqlExpression $gpsS)
	 * @method bool hasGpsS()
	 * @method bool isGpsSFilled()
	 * @method bool isGpsSChanged()
	 * @method \string remindActualGpsS()
	 * @method \string requireGpsS()
	 * @method \Bitrix\Catalog\EO_Store resetGpsS()
	 * @method \Bitrix\Catalog\EO_Store unsetGpsS()
	 * @method \string fillGpsS()
	 * @method \string getImageId()
	 * @method \Bitrix\Catalog\EO_Store setImageId(\string|\Bitrix\Main\DB\SqlExpression $imageId)
	 * @method bool hasImageId()
	 * @method bool isImageIdFilled()
	 * @method bool isImageIdChanged()
	 * @method \string remindActualImageId()
	 * @method \string requireImageId()
	 * @method \Bitrix\Catalog\EO_Store resetImageId()
	 * @method \Bitrix\Catalog\EO_Store unsetImageId()
	 * @method \string fillImageId()
	 * @method \int getLocationId()
	 * @method \Bitrix\Catalog\EO_Store setLocationId(\int|\Bitrix\Main\DB\SqlExpression $locationId)
	 * @method bool hasLocationId()
	 * @method bool isLocationIdFilled()
	 * @method bool isLocationIdChanged()
	 * @method \int remindActualLocationId()
	 * @method \int requireLocationId()
	 * @method \Bitrix\Catalog\EO_Store resetLocationId()
	 * @method \Bitrix\Catalog\EO_Store unsetLocationId()
	 * @method \int fillLocationId()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Catalog\EO_Store setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Catalog\EO_Store resetDateModify()
	 * @method \Bitrix\Catalog\EO_Store unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Catalog\EO_Store setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Catalog\EO_Store resetDateCreate()
	 * @method \Bitrix\Catalog\EO_Store unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getUserId()
	 * @method \Bitrix\Catalog\EO_Store setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Catalog\EO_Store resetUserId()
	 * @method \Bitrix\Catalog\EO_Store unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Catalog\EO_Store setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_Store resetCreatedByUser()
	 * @method \Bitrix\Catalog\EO_Store unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Catalog\EO_Store setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Catalog\EO_Store resetModifiedBy()
	 * @method \Bitrix\Catalog\EO_Store unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Store setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_Store resetModifiedByUser()
	 * @method \Bitrix\Catalog\EO_Store unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \string getPhone()
	 * @method \Bitrix\Catalog\EO_Store setPhone(\string|\Bitrix\Main\DB\SqlExpression $phone)
	 * @method bool hasPhone()
	 * @method bool isPhoneFilled()
	 * @method bool isPhoneChanged()
	 * @method \string remindActualPhone()
	 * @method \string requirePhone()
	 * @method \Bitrix\Catalog\EO_Store resetPhone()
	 * @method \Bitrix\Catalog\EO_Store unsetPhone()
	 * @method \string fillPhone()
	 * @method \string getSchedule()
	 * @method \Bitrix\Catalog\EO_Store setSchedule(\string|\Bitrix\Main\DB\SqlExpression $schedule)
	 * @method bool hasSchedule()
	 * @method bool isScheduleFilled()
	 * @method bool isScheduleChanged()
	 * @method \string remindActualSchedule()
	 * @method \string requireSchedule()
	 * @method \Bitrix\Catalog\EO_Store resetSchedule()
	 * @method \Bitrix\Catalog\EO_Store unsetSchedule()
	 * @method \string fillSchedule()
	 * @method \string getXmlId()
	 * @method \Bitrix\Catalog\EO_Store setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Catalog\EO_Store resetXmlId()
	 * @method \Bitrix\Catalog\EO_Store unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \int getSort()
	 * @method \Bitrix\Catalog\EO_Store setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Catalog\EO_Store resetSort()
	 * @method \Bitrix\Catalog\EO_Store unsetSort()
	 * @method \int fillSort()
	 * @method \string getEmail()
	 * @method \Bitrix\Catalog\EO_Store setEmail(\string|\Bitrix\Main\DB\SqlExpression $email)
	 * @method bool hasEmail()
	 * @method bool isEmailFilled()
	 * @method bool isEmailChanged()
	 * @method \string remindActualEmail()
	 * @method \string requireEmail()
	 * @method \Bitrix\Catalog\EO_Store resetEmail()
	 * @method \Bitrix\Catalog\EO_Store unsetEmail()
	 * @method \string fillEmail()
	 * @method \boolean getIssuingCenter()
	 * @method \Bitrix\Catalog\EO_Store setIssuingCenter(\boolean|\Bitrix\Main\DB\SqlExpression $issuingCenter)
	 * @method bool hasIssuingCenter()
	 * @method bool isIssuingCenterFilled()
	 * @method bool isIssuingCenterChanged()
	 * @method \boolean remindActualIssuingCenter()
	 * @method \boolean requireIssuingCenter()
	 * @method \Bitrix\Catalog\EO_Store resetIssuingCenter()
	 * @method \Bitrix\Catalog\EO_Store unsetIssuingCenter()
	 * @method \boolean fillIssuingCenter()
	 * @method \boolean getShippingCenter()
	 * @method \Bitrix\Catalog\EO_Store setShippingCenter(\boolean|\Bitrix\Main\DB\SqlExpression $shippingCenter)
	 * @method bool hasShippingCenter()
	 * @method bool isShippingCenterFilled()
	 * @method bool isShippingCenterChanged()
	 * @method \boolean remindActualShippingCenter()
	 * @method \boolean requireShippingCenter()
	 * @method \Bitrix\Catalog\EO_Store resetShippingCenter()
	 * @method \Bitrix\Catalog\EO_Store unsetShippingCenter()
	 * @method \boolean fillShippingCenter()
	 * @method \string getSiteId()
	 * @method \Bitrix\Catalog\EO_Store setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Catalog\EO_Store resetSiteId()
	 * @method \Bitrix\Catalog\EO_Store unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \string getCode()
	 * @method \Bitrix\Catalog\EO_Store setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Catalog\EO_Store resetCode()
	 * @method \Bitrix\Catalog\EO_Store unsetCode()
	 * @method \string fillCode()
	 * @method \string getIsDefault()
	 * @method \Bitrix\Catalog\EO_Store setIsDefault(\string|\Bitrix\Main\DB\SqlExpression $isDefault)
	 * @method bool hasIsDefault()
	 * @method bool isIsDefaultFilled()
	 * @method bool isIsDefaultChanged()
	 * @method \string remindActualIsDefault()
	 * @method \string requireIsDefault()
	 * @method \Bitrix\Catalog\EO_Store resetIsDefault()
	 * @method \Bitrix\Catalog\EO_Store unsetIsDefault()
	 * @method \string fillIsDefault()
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
	 * @method \Bitrix\Catalog\EO_Store set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Store reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Store unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Store wakeUp($data)
	 */
	class EO_Store {
		/* @var \Bitrix\Catalog\StoreTable */
		static public $dataClass = '\Bitrix\Catalog\StoreTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Store_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getAddressList()
	 * @method \string[] fillAddress()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getGpsNList()
	 * @method \string[] fillGpsN()
	 * @method \string[] getGpsSList()
	 * @method \string[] fillGpsS()
	 * @method \string[] getImageIdList()
	 * @method \string[] fillImageId()
	 * @method \int[] getLocationIdList()
	 * @method \int[] fillLocationId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Catalog\EO_Store_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Catalog\EO_Store_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \string[] getPhoneList()
	 * @method \string[] fillPhone()
	 * @method \string[] getScheduleList()
	 * @method \string[] fillSchedule()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getEmailList()
	 * @method \string[] fillEmail()
	 * @method \boolean[] getIssuingCenterList()
	 * @method \boolean[] fillIssuingCenter()
	 * @method \boolean[] getShippingCenterList()
	 * @method \boolean[] fillShippingCenter()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getIsDefaultList()
	 * @method \string[] fillIsDefault()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Store $object)
	 * @method bool has(\Bitrix\Catalog\EO_Store $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Store getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Store[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Store $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Store_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Store current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Store_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\StoreTable */
		static public $dataClass = '\Bitrix\Catalog\StoreTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Store_Result exec()
	 * @method \Bitrix\Catalog\EO_Store fetchObject()
	 * @method \Bitrix\Catalog\EO_Store_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Store_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Store fetchObject()
	 * @method \Bitrix\Catalog\EO_Store_Collection fetchCollection()
	 */
	class EO_Store_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Store createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Store_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Store wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Store_Collection wakeUpCollection($rows)
	 */
	class EO_Store_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\StoreBarcodeTable:catalog\lib\storebarcode.php */
namespace Bitrix\Catalog {
	/**
	 * EO_StoreBarcode
	 * @see \Bitrix\Catalog\StoreBarcodeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getProductId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setProductId(\int|\Bitrix\Main\DB\SqlExpression $productId)
	 * @method bool hasProductId()
	 * @method bool isProductIdFilled()
	 * @method bool isProductIdChanged()
	 * @method \int remindActualProductId()
	 * @method \int requireProductId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetProductId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetProductId()
	 * @method \int fillProductId()
	 * @method \string getBarcode()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setBarcode(\string|\Bitrix\Main\DB\SqlExpression $barcode)
	 * @method bool hasBarcode()
	 * @method bool isBarcodeFilled()
	 * @method bool isBarcodeChanged()
	 * @method \string remindActualBarcode()
	 * @method \string requireBarcode()
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetBarcode()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetBarcode()
	 * @method \string fillBarcode()
	 * @method \int getStoreId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setStoreId(\int|\Bitrix\Main\DB\SqlExpression $storeId)
	 * @method bool hasStoreId()
	 * @method bool isStoreIdFilled()
	 * @method bool isStoreIdChanged()
	 * @method \int remindActualStoreId()
	 * @method \int requireStoreId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetStoreId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetStoreId()
	 * @method \int fillStoreId()
	 * @method \int getOrderId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setOrderId(\int|\Bitrix\Main\DB\SqlExpression $orderId)
	 * @method bool hasOrderId()
	 * @method bool isOrderIdFilled()
	 * @method bool isOrderIdChanged()
	 * @method \int remindActualOrderId()
	 * @method \int requireOrderId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetOrderId()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetOrderId()
	 * @method \int fillOrderId()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetDateModify()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetDateCreate()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetCreatedBy()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetModifiedBy()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Catalog\EO_Product getProduct()
	 * @method \Bitrix\Catalog\EO_Product remindActualProduct()
	 * @method \Bitrix\Catalog\EO_Product requireProduct()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setProduct(\Bitrix\Catalog\EO_Product $object)
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetProduct()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetProduct()
	 * @method bool hasProduct()
	 * @method bool isProductFilled()
	 * @method bool isProductChanged()
	 * @method \Bitrix\Catalog\EO_Product fillProduct()
	 * @method \Bitrix\Catalog\EO_Store getStore()
	 * @method \Bitrix\Catalog\EO_Store remindActualStore()
	 * @method \Bitrix\Catalog\EO_Store requireStore()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setStore(\Bitrix\Catalog\EO_Store $object)
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetStore()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetStore()
	 * @method bool hasStore()
	 * @method bool isStoreFilled()
	 * @method bool isStoreChanged()
	 * @method \Bitrix\Catalog\EO_Store fillStore()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetCreatedByUser()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Catalog\EO_StoreBarcode setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_StoreBarcode resetModifiedByUser()
	 * @method \Bitrix\Catalog\EO_StoreBarcode unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
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
	 * @method \Bitrix\Catalog\EO_StoreBarcode set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_StoreBarcode reset($fieldName)
	 * @method \Bitrix\Catalog\EO_StoreBarcode unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_StoreBarcode wakeUp($data)
	 */
	class EO_StoreBarcode {
		/* @var \Bitrix\Catalog\StoreBarcodeTable */
		static public $dataClass = '\Bitrix\Catalog\StoreBarcodeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_StoreBarcode_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getProductIdList()
	 * @method \int[] fillProductId()
	 * @method \string[] getBarcodeList()
	 * @method \string[] fillBarcode()
	 * @method \int[] getStoreIdList()
	 * @method \int[] fillStoreId()
	 * @method \int[] getOrderIdList()
	 * @method \int[] fillOrderId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Catalog\EO_Product[] getProductList()
	 * @method \Bitrix\Catalog\EO_StoreBarcode_Collection getProductCollection()
	 * @method \Bitrix\Catalog\EO_Product_Collection fillProduct()
	 * @method \Bitrix\Catalog\EO_Store[] getStoreList()
	 * @method \Bitrix\Catalog\EO_StoreBarcode_Collection getStoreCollection()
	 * @method \Bitrix\Catalog\EO_Store_Collection fillStore()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Catalog\EO_StoreBarcode_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Catalog\EO_StoreBarcode_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_StoreBarcode $object)
	 * @method bool has(\Bitrix\Catalog\EO_StoreBarcode $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreBarcode getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreBarcode[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_StoreBarcode $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_StoreBarcode_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_StoreBarcode current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_StoreBarcode_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\StoreBarcodeTable */
		static public $dataClass = '\Bitrix\Catalog\StoreBarcodeTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_StoreBarcode_Result exec()
	 * @method \Bitrix\Catalog\EO_StoreBarcode fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreBarcode_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_StoreBarcode_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreBarcode fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreBarcode_Collection fetchCollection()
	 */
	class EO_StoreBarcode_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreBarcode createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_StoreBarcode_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_StoreBarcode wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_StoreBarcode_Collection wakeUpCollection($rows)
	 */
	class EO_StoreBarcode_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\StoreDocumentBarcodeTable:catalog\lib\storedocumentbarcodetable.php */
namespace Bitrix\Catalog {
	/**
	 * EO_StoreDocumentBarcode
	 * @see \Bitrix\Catalog\StoreDocumentBarcodeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDocId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode setDocId(\int|\Bitrix\Main\DB\SqlExpression $docId)
	 * @method bool hasDocId()
	 * @method bool isDocIdFilled()
	 * @method bool isDocIdChanged()
	 * @method \int remindActualDocId()
	 * @method \int requireDocId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode resetDocId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode unsetDocId()
	 * @method \int fillDocId()
	 * @method \int getDocElementId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode setDocElementId(\int|\Bitrix\Main\DB\SqlExpression $docElementId)
	 * @method bool hasDocElementId()
	 * @method bool isDocElementIdFilled()
	 * @method bool isDocElementIdChanged()
	 * @method \int remindActualDocElementId()
	 * @method \int requireDocElementId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode resetDocElementId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode unsetDocElementId()
	 * @method \int fillDocElementId()
	 * @method \string getBarcode()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode setBarcode(\string|\Bitrix\Main\DB\SqlExpression $barcode)
	 * @method bool hasBarcode()
	 * @method bool isBarcodeFilled()
	 * @method bool isBarcodeChanged()
	 * @method \string remindActualBarcode()
	 * @method \string requireBarcode()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode resetBarcode()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode unsetBarcode()
	 * @method \string fillBarcode()
	 * @method \Bitrix\Catalog\EO_StoreDocument getDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocument remindActualDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocument requireDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode setDocument(\Bitrix\Catalog\EO_StoreDocument $object)
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode resetDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode unsetDocument()
	 * @method bool hasDocument()
	 * @method bool isDocumentFilled()
	 * @method bool isDocumentChanged()
	 * @method \Bitrix\Catalog\EO_StoreDocument fillDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement getDocumentElement()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement remindActualDocumentElement()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement requireDocumentElement()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode setDocumentElement(\Bitrix\Catalog\EO_StoreDocumentElement $object)
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode resetDocumentElement()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode unsetDocumentElement()
	 * @method bool hasDocumentElement()
	 * @method bool isDocumentElementFilled()
	 * @method bool isDocumentElementChanged()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement fillDocumentElement()
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
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode reset($fieldName)
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_StoreDocumentBarcode wakeUp($data)
	 */
	class EO_StoreDocumentBarcode {
		/* @var \Bitrix\Catalog\StoreDocumentBarcodeTable */
		static public $dataClass = '\Bitrix\Catalog\StoreDocumentBarcodeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_StoreDocumentBarcode_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDocIdList()
	 * @method \int[] fillDocId()
	 * @method \int[] getDocElementIdList()
	 * @method \int[] fillDocElementId()
	 * @method \string[] getBarcodeList()
	 * @method \string[] fillBarcode()
	 * @method \Bitrix\Catalog\EO_StoreDocument[] getDocumentList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode_Collection getDocumentCollection()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection fillDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement[] getDocumentElementList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode_Collection getDocumentElementCollection()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection fillDocumentElement()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_StoreDocumentBarcode $object)
	 * @method bool has(\Bitrix\Catalog\EO_StoreDocumentBarcode $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_StoreDocumentBarcode $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_StoreDocumentBarcode_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_StoreDocumentBarcode_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\StoreDocumentBarcodeTable */
		static public $dataClass = '\Bitrix\Catalog\StoreDocumentBarcodeTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_StoreDocumentBarcode_Result exec()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_StoreDocumentBarcode_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode_Collection fetchCollection()
	 */
	class EO_StoreDocumentBarcode_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_StoreDocumentBarcode_Collection wakeUpCollection($rows)
	 */
	class EO_StoreDocumentBarcode_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\StoreDocumentElementTable:catalog\lib\storedocumentelementtable.php */
namespace Bitrix\Catalog {
	/**
	 * EO_StoreDocumentElement
	 * @see \Bitrix\Catalog\StoreDocumentElementTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDocId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setDocId(\int|\Bitrix\Main\DB\SqlExpression $docId)
	 * @method bool hasDocId()
	 * @method bool isDocIdFilled()
	 * @method bool isDocIdChanged()
	 * @method \int remindActualDocId()
	 * @method \int requireDocId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetDocId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetDocId()
	 * @method \int fillDocId()
	 * @method \int getStoreFrom()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setStoreFrom(\int|\Bitrix\Main\DB\SqlExpression $storeFrom)
	 * @method bool hasStoreFrom()
	 * @method bool isStoreFromFilled()
	 * @method bool isStoreFromChanged()
	 * @method \int remindActualStoreFrom()
	 * @method \int requireStoreFrom()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetStoreFrom()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetStoreFrom()
	 * @method \int fillStoreFrom()
	 * @method \int getStoreTo()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setStoreTo(\int|\Bitrix\Main\DB\SqlExpression $storeTo)
	 * @method bool hasStoreTo()
	 * @method bool isStoreToFilled()
	 * @method bool isStoreToChanged()
	 * @method \int remindActualStoreTo()
	 * @method \int requireStoreTo()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetStoreTo()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetStoreTo()
	 * @method \int fillStoreTo()
	 * @method \int getElementId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setElementId(\int|\Bitrix\Main\DB\SqlExpression $elementId)
	 * @method bool hasElementId()
	 * @method bool isElementIdFilled()
	 * @method bool isElementIdChanged()
	 * @method \int remindActualElementId()
	 * @method \int requireElementId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetElementId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetElementId()
	 * @method \int fillElementId()
	 * @method \float getAmount()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setAmount(\float|\Bitrix\Main\DB\SqlExpression $amount)
	 * @method bool hasAmount()
	 * @method bool isAmountFilled()
	 * @method bool isAmountChanged()
	 * @method \float remindActualAmount()
	 * @method \float requireAmount()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetAmount()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetAmount()
	 * @method \float fillAmount()
	 * @method \int getPurchasingPrice()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setPurchasingPrice(\int|\Bitrix\Main\DB\SqlExpression $purchasingPrice)
	 * @method bool hasPurchasingPrice()
	 * @method bool isPurchasingPriceFilled()
	 * @method bool isPurchasingPriceChanged()
	 * @method \int remindActualPurchasingPrice()
	 * @method \int requirePurchasingPrice()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetPurchasingPrice()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetPurchasingPrice()
	 * @method \int fillPurchasingPrice()
	 * @method \int getBasePrice()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setBasePrice(\int|\Bitrix\Main\DB\SqlExpression $basePrice)
	 * @method bool hasBasePrice()
	 * @method bool isBasePriceFilled()
	 * @method bool isBasePriceChanged()
	 * @method \int remindActualBasePrice()
	 * @method \int requireBasePrice()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetBasePrice()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetBasePrice()
	 * @method \int fillBasePrice()
	 * @method \float getBasePriceExtra()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setBasePriceExtra(\float|\Bitrix\Main\DB\SqlExpression $basePriceExtra)
	 * @method bool hasBasePriceExtra()
	 * @method bool isBasePriceExtraFilled()
	 * @method bool isBasePriceExtraChanged()
	 * @method \float remindActualBasePriceExtra()
	 * @method \float requireBasePriceExtra()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetBasePriceExtra()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetBasePriceExtra()
	 * @method \float fillBasePriceExtra()
	 * @method \string getBasePriceExtraRate()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setBasePriceExtraRate(\string|\Bitrix\Main\DB\SqlExpression $basePriceExtraRate)
	 * @method bool hasBasePriceExtraRate()
	 * @method bool isBasePriceExtraRateFilled()
	 * @method bool isBasePriceExtraRateChanged()
	 * @method \string remindActualBasePriceExtraRate()
	 * @method \string requireBasePriceExtraRate()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetBasePriceExtraRate()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetBasePriceExtraRate()
	 * @method \string fillBasePriceExtraRate()
	 * @method \Bitrix\Iblock\EO_Element getElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualElement()
	 * @method \Bitrix\Iblock\EO_Element requireElement()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetElement()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetElement()
	 * @method bool hasElement()
	 * @method bool isElementFilled()
	 * @method bool isElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillElement()
	 * @method \Bitrix\Catalog\EO_Product getProduct()
	 * @method \Bitrix\Catalog\EO_Product remindActualProduct()
	 * @method \Bitrix\Catalog\EO_Product requireProduct()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setProduct(\Bitrix\Catalog\EO_Product $object)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetProduct()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetProduct()
	 * @method bool hasProduct()
	 * @method bool isProductFilled()
	 * @method bool isProductChanged()
	 * @method \Bitrix\Catalog\EO_Product fillProduct()
	 * @method \Bitrix\Catalog\EO_StoreDocument getDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocument remindActualDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocument requireDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setDocument(\Bitrix\Catalog\EO_StoreDocument $object)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetDocument()
	 * @method bool hasDocument()
	 * @method bool isDocumentFilled()
	 * @method bool isDocumentChanged()
	 * @method \Bitrix\Catalog\EO_StoreDocument fillDocument()
	 * @method \Bitrix\Catalog\EO_Store getStoreFromRef()
	 * @method \Bitrix\Catalog\EO_Store remindActualStoreFromRef()
	 * @method \Bitrix\Catalog\EO_Store requireStoreFromRef()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setStoreFromRef(\Bitrix\Catalog\EO_Store $object)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetStoreFromRef()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetStoreFromRef()
	 * @method bool hasStoreFromRef()
	 * @method bool isStoreFromRefFilled()
	 * @method bool isStoreFromRefChanged()
	 * @method \Bitrix\Catalog\EO_Store fillStoreFromRef()
	 * @method \Bitrix\Catalog\EO_Store getStoreToRef()
	 * @method \Bitrix\Catalog\EO_Store remindActualStoreToRef()
	 * @method \Bitrix\Catalog\EO_Store requireStoreToRef()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement setStoreToRef(\Bitrix\Catalog\EO_Store $object)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement resetStoreToRef()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unsetStoreToRef()
	 * @method bool hasStoreToRef()
	 * @method bool isStoreToRefFilled()
	 * @method bool isStoreToRefChanged()
	 * @method \Bitrix\Catalog\EO_Store fillStoreToRef()
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
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement reset($fieldName)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_StoreDocumentElement wakeUp($data)
	 */
	class EO_StoreDocumentElement {
		/* @var \Bitrix\Catalog\StoreDocumentElementTable */
		static public $dataClass = '\Bitrix\Catalog\StoreDocumentElementTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_StoreDocumentElement_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDocIdList()
	 * @method \int[] fillDocId()
	 * @method \int[] getStoreFromList()
	 * @method \int[] fillStoreFrom()
	 * @method \int[] getStoreToList()
	 * @method \int[] fillStoreTo()
	 * @method \int[] getElementIdList()
	 * @method \int[] fillElementId()
	 * @method \float[] getAmountList()
	 * @method \float[] fillAmount()
	 * @method \int[] getPurchasingPriceList()
	 * @method \int[] fillPurchasingPrice()
	 * @method \int[] getBasePriceList()
	 * @method \int[] fillBasePrice()
	 * @method \float[] getBasePriceExtraList()
	 * @method \float[] fillBasePriceExtra()
	 * @method \string[] getBasePriceExtraRateList()
	 * @method \string[] fillBasePriceExtraRate()
	 * @method \Bitrix\Iblock\EO_Element[] getElementList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection getElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillElement()
	 * @method \Bitrix\Catalog\EO_Product[] getProductList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection getProductCollection()
	 * @method \Bitrix\Catalog\EO_Product_Collection fillProduct()
	 * @method \Bitrix\Catalog\EO_StoreDocument[] getDocumentList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection getDocumentCollection()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection fillDocument()
	 * @method \Bitrix\Catalog\EO_Store[] getStoreFromRefList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection getStoreFromRefCollection()
	 * @method \Bitrix\Catalog\EO_Store_Collection fillStoreFromRef()
	 * @method \Bitrix\Catalog\EO_Store[] getStoreToRefList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection getStoreToRefCollection()
	 * @method \Bitrix\Catalog\EO_Store_Collection fillStoreToRef()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_StoreDocumentElement $object)
	 * @method bool has(\Bitrix\Catalog\EO_StoreDocumentElement $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_StoreDocumentElement $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_StoreDocumentElement_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_StoreDocumentElement_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\StoreDocumentElementTable */
		static public $dataClass = '\Bitrix\Catalog\StoreDocumentElementTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_StoreDocumentElement_Result exec()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_StoreDocumentElement_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection fetchCollection()
	 */
	class EO_StoreDocumentElement_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection wakeUpCollection($rows)
	 */
	class EO_StoreDocumentElement_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\StoreDocumentFileTable:catalog\lib\storedocumentfiletable.php */
namespace Bitrix\Catalog {
	/**
	 * EO_StoreDocumentFile
	 * @see \Bitrix\Catalog\StoreDocumentFileTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Catalog\EO_StoreDocument getDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocument remindActualDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocument requireDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile setDocument(\Bitrix\Catalog\EO_StoreDocument $object)
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile resetDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile unsetDocument()
	 * @method bool hasDocument()
	 * @method bool isDocumentFilled()
	 * @method bool isDocumentChanged()
	 * @method \Bitrix\Catalog\EO_StoreDocument fillDocument()
	 * @method \int getDocumentId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile setDocumentId(\int|\Bitrix\Main\DB\SqlExpression $documentId)
	 * @method bool hasDocumentId()
	 * @method bool isDocumentIdFilled()
	 * @method bool isDocumentIdChanged()
	 * @method \int remindActualDocumentId()
	 * @method \int requireDocumentId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile resetDocumentId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile unsetDocumentId()
	 * @method \int fillDocumentId()
	 * @method \Bitrix\Main\EO_File getFile()
	 * @method \Bitrix\Main\EO_File remindActualFile()
	 * @method \Bitrix\Main\EO_File requireFile()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile setFile(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile resetFile()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile unsetFile()
	 * @method bool hasFile()
	 * @method bool isFileFilled()
	 * @method bool isFileChanged()
	 * @method \Bitrix\Main\EO_File fillFile()
	 * @method \int getFileId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int remindActualFileId()
	 * @method \int requireFileId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile resetFileId()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile unsetFileId()
	 * @method \int fillFileId()
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
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile reset($fieldName)
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_StoreDocumentFile wakeUp($data)
	 */
	class EO_StoreDocumentFile {
		/* @var \Bitrix\Catalog\StoreDocumentFileTable */
		static public $dataClass = '\Bitrix\Catalog\StoreDocumentFileTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_StoreDocumentFile_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Catalog\EO_StoreDocument[] getDocumentList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile_Collection getDocumentCollection()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection fillDocument()
	 * @method \int[] getDocumentIdList()
	 * @method \int[] fillDocumentId()
	 * @method \Bitrix\Main\EO_File[] getFileList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile_Collection getFileCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillFile()
	 * @method \int[] getFileIdList()
	 * @method \int[] fillFileId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_StoreDocumentFile $object)
	 * @method bool has(\Bitrix\Catalog\EO_StoreDocumentFile $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_StoreDocumentFile $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_StoreDocumentFile_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_StoreDocumentFile_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\StoreDocumentFileTable */
		static public $dataClass = '\Bitrix\Catalog\StoreDocumentFileTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_StoreDocumentFile_Result exec()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_StoreDocumentFile_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile_Collection fetchCollection()
	 */
	class EO_StoreDocumentFile_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_StoreDocumentFile_Collection wakeUpCollection($rows)
	 */
	class EO_StoreDocumentFile_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\StoreDocumentTable:catalog\lib\storedocumenttable.php */
namespace Bitrix\Catalog {
	/**
	 * EO_StoreDocument
	 * @see \Bitrix\Catalog\StoreDocumentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_StoreDocument setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getTitle()
	 * @method \Bitrix\Catalog\EO_StoreDocument setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetTitle()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getDocType()
	 * @method \Bitrix\Catalog\EO_StoreDocument setDocType(\string|\Bitrix\Main\DB\SqlExpression $docType)
	 * @method bool hasDocType()
	 * @method bool isDocTypeFilled()
	 * @method bool isDocTypeChanged()
	 * @method \string remindActualDocType()
	 * @method \string requireDocType()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetDocType()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetDocType()
	 * @method \string fillDocType()
	 * @method \string getDocNumber()
	 * @method \Bitrix\Catalog\EO_StoreDocument setDocNumber(\string|\Bitrix\Main\DB\SqlExpression $docNumber)
	 * @method bool hasDocNumber()
	 * @method bool isDocNumberFilled()
	 * @method bool isDocNumberChanged()
	 * @method \string remindActualDocNumber()
	 * @method \string requireDocNumber()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetDocNumber()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetDocNumber()
	 * @method \string fillDocNumber()
	 * @method \string getSiteId()
	 * @method \Bitrix\Catalog\EO_StoreDocument setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetSiteId()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \Bitrix\Catalog\EO_Contractor getContractor()
	 * @method \Bitrix\Catalog\EO_Contractor remindActualContractor()
	 * @method \Bitrix\Catalog\EO_Contractor requireContractor()
	 * @method \Bitrix\Catalog\EO_StoreDocument setContractor(\Bitrix\Catalog\EO_Contractor $object)
	 * @method \Bitrix\Catalog\EO_StoreDocument resetContractor()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetContractor()
	 * @method bool hasContractor()
	 * @method bool isContractorFilled()
	 * @method bool isContractorChanged()
	 * @method \Bitrix\Catalog\EO_Contractor fillContractor()
	 * @method \int getContractorId()
	 * @method \Bitrix\Catalog\EO_StoreDocument setContractorId(\int|\Bitrix\Main\DB\SqlExpression $contractorId)
	 * @method bool hasContractorId()
	 * @method bool isContractorIdFilled()
	 * @method bool isContractorIdChanged()
	 * @method \int remindActualContractorId()
	 * @method \int requireContractorId()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetContractorId()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetContractorId()
	 * @method \int fillContractorId()
	 * @method \Bitrix\Main\Type\DateTime getDateModify()
	 * @method \Bitrix\Catalog\EO_StoreDocument setDateModify(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateModify)
	 * @method bool hasDateModify()
	 * @method bool isDateModifyFilled()
	 * @method bool isDateModifyChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateModify()
	 * @method \Bitrix\Main\Type\DateTime requireDateModify()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetDateModify()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetDateModify()
	 * @method \Bitrix\Main\Type\DateTime fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Catalog\EO_StoreDocument setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetDateCreate()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Catalog\EO_StoreDocument setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetCreatedBy()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\EO_User getCreatedByUser()
	 * @method \Bitrix\Main\EO_User remindActualCreatedByUser()
	 * @method \Bitrix\Main\EO_User requireCreatedByUser()
	 * @method \Bitrix\Catalog\EO_StoreDocument setCreatedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_StoreDocument resetCreatedByUser()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetCreatedByUser()
	 * @method bool hasCreatedByUser()
	 * @method bool isCreatedByUserFilled()
	 * @method bool isCreatedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillCreatedByUser()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Catalog\EO_StoreDocument setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetModifiedBy()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \Bitrix\Main\EO_User getModifiedByUser()
	 * @method \Bitrix\Main\EO_User remindActualModifiedByUser()
	 * @method \Bitrix\Main\EO_User requireModifiedByUser()
	 * @method \Bitrix\Catalog\EO_StoreDocument setModifiedByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_StoreDocument resetModifiedByUser()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetModifiedByUser()
	 * @method bool hasModifiedByUser()
	 * @method bool isModifiedByUserFilled()
	 * @method bool isModifiedByUserChanged()
	 * @method \Bitrix\Main\EO_User fillModifiedByUser()
	 * @method \int getResponsibleId()
	 * @method \Bitrix\Catalog\EO_StoreDocument setResponsibleId(\int|\Bitrix\Main\DB\SqlExpression $responsibleId)
	 * @method bool hasResponsibleId()
	 * @method bool isResponsibleIdFilled()
	 * @method bool isResponsibleIdChanged()
	 * @method \int remindActualResponsibleId()
	 * @method \int requireResponsibleId()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetResponsibleId()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetResponsibleId()
	 * @method \int fillResponsibleId()
	 * @method \Bitrix\Main\EO_User getResponsible()
	 * @method \Bitrix\Main\EO_User remindActualResponsible()
	 * @method \Bitrix\Main\EO_User requireResponsible()
	 * @method \Bitrix\Catalog\EO_StoreDocument setResponsible(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_StoreDocument resetResponsible()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetResponsible()
	 * @method bool hasResponsible()
	 * @method bool isResponsibleFilled()
	 * @method bool isResponsibleChanged()
	 * @method \Bitrix\Main\EO_User fillResponsible()
	 * @method \string getCurrency()
	 * @method \Bitrix\Catalog\EO_StoreDocument setCurrency(\string|\Bitrix\Main\DB\SqlExpression $currency)
	 * @method bool hasCurrency()
	 * @method bool isCurrencyFilled()
	 * @method bool isCurrencyChanged()
	 * @method \string remindActualCurrency()
	 * @method \string requireCurrency()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetCurrency()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetCurrency()
	 * @method \string fillCurrency()
	 * @method \boolean getStatus()
	 * @method \Bitrix\Catalog\EO_StoreDocument setStatus(\boolean|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \boolean remindActualStatus()
	 * @method \boolean requireStatus()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetStatus()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetStatus()
	 * @method \boolean fillStatus()
	 * @method \boolean getWasCancelled()
	 * @method \Bitrix\Catalog\EO_StoreDocument setWasCancelled(\boolean|\Bitrix\Main\DB\SqlExpression $wasCancelled)
	 * @method bool hasWasCancelled()
	 * @method bool isWasCancelledFilled()
	 * @method bool isWasCancelledChanged()
	 * @method \boolean remindActualWasCancelled()
	 * @method \boolean requireWasCancelled()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetWasCancelled()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetWasCancelled()
	 * @method \boolean fillWasCancelled()
	 * @method \Bitrix\Main\Type\DateTime getDateStatus()
	 * @method \Bitrix\Catalog\EO_StoreDocument setDateStatus(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateStatus)
	 * @method bool hasDateStatus()
	 * @method bool isDateStatusFilled()
	 * @method bool isDateStatusChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateStatus()
	 * @method \Bitrix\Main\Type\DateTime requireDateStatus()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetDateStatus()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetDateStatus()
	 * @method \Bitrix\Main\Type\DateTime fillDateStatus()
	 * @method \Bitrix\Main\Type\DateTime getDateDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocument setDateDocument(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateDocument)
	 * @method bool hasDateDocument()
	 * @method bool isDateDocumentFilled()
	 * @method bool isDateDocumentChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateDocument()
	 * @method \Bitrix\Main\Type\DateTime requireDateDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetDateDocument()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetDateDocument()
	 * @method \Bitrix\Main\Type\DateTime fillDateDocument()
	 * @method \int getStatusBy()
	 * @method \Bitrix\Catalog\EO_StoreDocument setStatusBy(\int|\Bitrix\Main\DB\SqlExpression $statusBy)
	 * @method bool hasStatusBy()
	 * @method bool isStatusByFilled()
	 * @method bool isStatusByChanged()
	 * @method \int remindActualStatusBy()
	 * @method \int requireStatusBy()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetStatusBy()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetStatusBy()
	 * @method \int fillStatusBy()
	 * @method \Bitrix\Main\EO_User getStatusByUser()
	 * @method \Bitrix\Main\EO_User remindActualStatusByUser()
	 * @method \Bitrix\Main\EO_User requireStatusByUser()
	 * @method \Bitrix\Catalog\EO_StoreDocument setStatusByUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_StoreDocument resetStatusByUser()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetStatusByUser()
	 * @method bool hasStatusByUser()
	 * @method bool isStatusByUserFilled()
	 * @method bool isStatusByUserChanged()
	 * @method \Bitrix\Main\EO_User fillStatusByUser()
	 * @method \float getTotal()
	 * @method \Bitrix\Catalog\EO_StoreDocument setTotal(\float|\Bitrix\Main\DB\SqlExpression $total)
	 * @method bool hasTotal()
	 * @method bool isTotalFilled()
	 * @method bool isTotalChanged()
	 * @method \float remindActualTotal()
	 * @method \float requireTotal()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetTotal()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetTotal()
	 * @method \float fillTotal()
	 * @method \string getCommentary()
	 * @method \Bitrix\Catalog\EO_StoreDocument setCommentary(\string|\Bitrix\Main\DB\SqlExpression $commentary)
	 * @method bool hasCommentary()
	 * @method bool isCommentaryFilled()
	 * @method bool isCommentaryChanged()
	 * @method \string remindActualCommentary()
	 * @method \string requireCommentary()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetCommentary()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetCommentary()
	 * @method \string fillCommentary()
	 * @method \Bitrix\Main\Type\DateTime getItemsOrderDate()
	 * @method \Bitrix\Catalog\EO_StoreDocument setItemsOrderDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $itemsOrderDate)
	 * @method bool hasItemsOrderDate()
	 * @method bool isItemsOrderDateFilled()
	 * @method bool isItemsOrderDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualItemsOrderDate()
	 * @method \Bitrix\Main\Type\DateTime requireItemsOrderDate()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetItemsOrderDate()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetItemsOrderDate()
	 * @method \Bitrix\Main\Type\DateTime fillItemsOrderDate()
	 * @method \Bitrix\Main\Type\DateTime getItemsReceivedDate()
	 * @method \Bitrix\Catalog\EO_StoreDocument setItemsReceivedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $itemsReceivedDate)
	 * @method bool hasItemsReceivedDate()
	 * @method bool isItemsReceivedDateFilled()
	 * @method bool isItemsReceivedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualItemsReceivedDate()
	 * @method \Bitrix\Main\Type\DateTime requireItemsReceivedDate()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetItemsReceivedDate()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetItemsReceivedDate()
	 * @method \Bitrix\Main\Type\DateTime fillItemsReceivedDate()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection getElements()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection requireElements()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection fillElements()
	 * @method bool hasElements()
	 * @method bool isElementsFilled()
	 * @method bool isElementsChanged()
	 * @method void addToElements(\Bitrix\Catalog\EO_StoreDocumentElement $storeDocumentElement)
	 * @method void removeFromElements(\Bitrix\Catalog\EO_StoreDocumentElement $storeDocumentElement)
	 * @method void removeAllElements()
	 * @method \Bitrix\Catalog\EO_StoreDocument resetElements()
	 * @method \Bitrix\Catalog\EO_StoreDocument unsetElements()
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
	 * @method \Bitrix\Catalog\EO_StoreDocument set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_StoreDocument reset($fieldName)
	 * @method \Bitrix\Catalog\EO_StoreDocument unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_StoreDocument wakeUp($data)
	 */
	class EO_StoreDocument {
		/* @var \Bitrix\Catalog\StoreDocumentTable */
		static public $dataClass = '\Bitrix\Catalog\StoreDocumentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_StoreDocument_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getDocTypeList()
	 * @method \string[] fillDocType()
	 * @method \string[] getDocNumberList()
	 * @method \string[] fillDocNumber()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \Bitrix\Catalog\EO_Contractor[] getContractorList()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection getContractorCollection()
	 * @method \Bitrix\Catalog\EO_Contractor_Collection fillContractor()
	 * @method \int[] getContractorIdList()
	 * @method \int[] fillContractorId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateModifyList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateModify()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\EO_User[] getCreatedByUserList()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection getCreatedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillCreatedByUser()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \Bitrix\Main\EO_User[] getModifiedByUserList()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection getModifiedByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillModifiedByUser()
	 * @method \int[] getResponsibleIdList()
	 * @method \int[] fillResponsibleId()
	 * @method \Bitrix\Main\EO_User[] getResponsibleList()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection getResponsibleCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillResponsible()
	 * @method \string[] getCurrencyList()
	 * @method \string[] fillCurrency()
	 * @method \boolean[] getStatusList()
	 * @method \boolean[] fillStatus()
	 * @method \boolean[] getWasCancelledList()
	 * @method \boolean[] fillWasCancelled()
	 * @method \Bitrix\Main\Type\DateTime[] getDateStatusList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateStatus()
	 * @method \Bitrix\Main\Type\DateTime[] getDateDocumentList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateDocument()
	 * @method \int[] getStatusByList()
	 * @method \int[] fillStatusBy()
	 * @method \Bitrix\Main\EO_User[] getStatusByUserList()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection getStatusByUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillStatusByUser()
	 * @method \float[] getTotalList()
	 * @method \float[] fillTotal()
	 * @method \string[] getCommentaryList()
	 * @method \string[] fillCommentary()
	 * @method \Bitrix\Main\Type\DateTime[] getItemsOrderDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillItemsOrderDate()
	 * @method \Bitrix\Main\Type\DateTime[] getItemsReceivedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillItemsReceivedDate()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection[] getElementsList()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection getElementsCollection()
	 * @method \Bitrix\Catalog\EO_StoreDocumentElement_Collection fillElements()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_StoreDocument $object)
	 * @method bool has(\Bitrix\Catalog\EO_StoreDocument $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreDocument getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreDocument[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_StoreDocument $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_StoreDocument_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_StoreDocument current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_StoreDocument_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\StoreDocumentTable */
		static public $dataClass = '\Bitrix\Catalog\StoreDocumentTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_StoreDocument_Result exec()
	 * @method \Bitrix\Catalog\EO_StoreDocument fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @see \Bitrix\Catalog\StoreDocumentTable::withProduct()
	 * @method EO_StoreDocument_Query withProduct($productId)
	 * @see \Bitrix\Catalog\StoreDocumentTable::withProductList()
	 * @method EO_StoreDocument_Query withProductList($productIds)
	 * @see \Bitrix\Catalog\StoreDocumentTable::withStore()
	 * @method EO_StoreDocument_Query withStore($storeId)
	 * @see \Bitrix\Catalog\StoreDocumentTable::withStoreList()
	 * @method EO_StoreDocument_Query withStoreList($storeIds)
	 * @see \Bitrix\Catalog\StoreDocumentTable::withStoreFromList()
	 * @method EO_StoreDocument_Query withStoreFromList($storeIds)
	 * @see \Bitrix\Catalog\StoreDocumentTable::withStoreToList()
	 * @method EO_StoreDocument_Query withStoreToList($storeIds)
	 */
	class EO_StoreDocument_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreDocument fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection fetchCollection()
	 */
	class EO_StoreDocument_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreDocument createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_StoreDocument wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_StoreDocument_Collection wakeUpCollection($rows)
	 */
	class EO_StoreDocument_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\StoreProductTable:catalog\lib\storeproduct.php */
namespace Bitrix\Catalog {
	/**
	 * EO_StoreProduct
	 * @see \Bitrix\Catalog\StoreProductTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_StoreProduct setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getStoreId()
	 * @method \Bitrix\Catalog\EO_StoreProduct setStoreId(\int|\Bitrix\Main\DB\SqlExpression $storeId)
	 * @method bool hasStoreId()
	 * @method bool isStoreIdFilled()
	 * @method bool isStoreIdChanged()
	 * @method \int remindActualStoreId()
	 * @method \int requireStoreId()
	 * @method \Bitrix\Catalog\EO_StoreProduct resetStoreId()
	 * @method \Bitrix\Catalog\EO_StoreProduct unsetStoreId()
	 * @method \int fillStoreId()
	 * @method \int getProductId()
	 * @method \Bitrix\Catalog\EO_StoreProduct setProductId(\int|\Bitrix\Main\DB\SqlExpression $productId)
	 * @method bool hasProductId()
	 * @method bool isProductIdFilled()
	 * @method bool isProductIdChanged()
	 * @method \int remindActualProductId()
	 * @method \int requireProductId()
	 * @method \Bitrix\Catalog\EO_StoreProduct resetProductId()
	 * @method \Bitrix\Catalog\EO_StoreProduct unsetProductId()
	 * @method \int fillProductId()
	 * @method \float getAmount()
	 * @method \Bitrix\Catalog\EO_StoreProduct setAmount(\float|\Bitrix\Main\DB\SqlExpression $amount)
	 * @method bool hasAmount()
	 * @method bool isAmountFilled()
	 * @method bool isAmountChanged()
	 * @method \float remindActualAmount()
	 * @method \float requireAmount()
	 * @method \Bitrix\Catalog\EO_StoreProduct resetAmount()
	 * @method \Bitrix\Catalog\EO_StoreProduct unsetAmount()
	 * @method \float fillAmount()
	 * @method \float getQuantityReserved()
	 * @method \Bitrix\Catalog\EO_StoreProduct setQuantityReserved(\float|\Bitrix\Main\DB\SqlExpression $quantityReserved)
	 * @method bool hasQuantityReserved()
	 * @method bool isQuantityReservedFilled()
	 * @method bool isQuantityReservedChanged()
	 * @method \float remindActualQuantityReserved()
	 * @method \float requireQuantityReserved()
	 * @method \Bitrix\Catalog\EO_StoreProduct resetQuantityReserved()
	 * @method \Bitrix\Catalog\EO_StoreProduct unsetQuantityReserved()
	 * @method \float fillQuantityReserved()
	 * @method \Bitrix\Catalog\EO_Store getStore()
	 * @method \Bitrix\Catalog\EO_Store remindActualStore()
	 * @method \Bitrix\Catalog\EO_Store requireStore()
	 * @method \Bitrix\Catalog\EO_StoreProduct setStore(\Bitrix\Catalog\EO_Store $object)
	 * @method \Bitrix\Catalog\EO_StoreProduct resetStore()
	 * @method \Bitrix\Catalog\EO_StoreProduct unsetStore()
	 * @method bool hasStore()
	 * @method bool isStoreFilled()
	 * @method bool isStoreChanged()
	 * @method \Bitrix\Catalog\EO_Store fillStore()
	 * @method \Bitrix\Catalog\EO_Product getProduct()
	 * @method \Bitrix\Catalog\EO_Product remindActualProduct()
	 * @method \Bitrix\Catalog\EO_Product requireProduct()
	 * @method \Bitrix\Catalog\EO_StoreProduct setProduct(\Bitrix\Catalog\EO_Product $object)
	 * @method \Bitrix\Catalog\EO_StoreProduct resetProduct()
	 * @method \Bitrix\Catalog\EO_StoreProduct unsetProduct()
	 * @method bool hasProduct()
	 * @method bool isProductFilled()
	 * @method bool isProductChanged()
	 * @method \Bitrix\Catalog\EO_Product fillProduct()
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
	 * @method \Bitrix\Catalog\EO_StoreProduct set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_StoreProduct reset($fieldName)
	 * @method \Bitrix\Catalog\EO_StoreProduct unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_StoreProduct wakeUp($data)
	 */
	class EO_StoreProduct {
		/* @var \Bitrix\Catalog\StoreProductTable */
		static public $dataClass = '\Bitrix\Catalog\StoreProductTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_StoreProduct_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getStoreIdList()
	 * @method \int[] fillStoreId()
	 * @method \int[] getProductIdList()
	 * @method \int[] fillProductId()
	 * @method \float[] getAmountList()
	 * @method \float[] fillAmount()
	 * @method \float[] getQuantityReservedList()
	 * @method \float[] fillQuantityReserved()
	 * @method \Bitrix\Catalog\EO_Store[] getStoreList()
	 * @method \Bitrix\Catalog\EO_StoreProduct_Collection getStoreCollection()
	 * @method \Bitrix\Catalog\EO_Store_Collection fillStore()
	 * @method \Bitrix\Catalog\EO_Product[] getProductList()
	 * @method \Bitrix\Catalog\EO_StoreProduct_Collection getProductCollection()
	 * @method \Bitrix\Catalog\EO_Product_Collection fillProduct()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_StoreProduct $object)
	 * @method bool has(\Bitrix\Catalog\EO_StoreProduct $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreProduct getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_StoreProduct[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_StoreProduct $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_StoreProduct_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_StoreProduct current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_StoreProduct_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\StoreProductTable */
		static public $dataClass = '\Bitrix\Catalog\StoreProductTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_StoreProduct_Result exec()
	 * @method \Bitrix\Catalog\EO_StoreProduct fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreProduct_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_StoreProduct_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreProduct fetchObject()
	 * @method \Bitrix\Catalog\EO_StoreProduct_Collection fetchCollection()
	 */
	class EO_StoreProduct_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_StoreProduct createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_StoreProduct_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_StoreProduct wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_StoreProduct_Collection wakeUpCollection($rows)
	 */
	class EO_StoreProduct_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\SubscribeTable:catalog\lib\subscribe.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Subscribe
	 * @see \Bitrix\Catalog\SubscribeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Subscribe setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateFrom()
	 * @method \Bitrix\Catalog\EO_Subscribe setDateFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateFrom)
	 * @method bool hasDateFrom()
	 * @method bool isDateFromFilled()
	 * @method bool isDateFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateFrom()
	 * @method \Bitrix\Main\Type\DateTime requireDateFrom()
	 * @method \Bitrix\Catalog\EO_Subscribe resetDateFrom()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetDateFrom()
	 * @method \Bitrix\Main\Type\DateTime fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime getDateTo()
	 * @method \Bitrix\Catalog\EO_Subscribe setDateTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateTo)
	 * @method bool hasDateTo()
	 * @method bool isDateToFilled()
	 * @method bool isDateToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateTo()
	 * @method \Bitrix\Main\Type\DateTime requireDateTo()
	 * @method \Bitrix\Catalog\EO_Subscribe resetDateTo()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetDateTo()
	 * @method \Bitrix\Main\Type\DateTime fillDateTo()
	 * @method \string getUserContact()
	 * @method \Bitrix\Catalog\EO_Subscribe setUserContact(\string|\Bitrix\Main\DB\SqlExpression $userContact)
	 * @method bool hasUserContact()
	 * @method bool isUserContactFilled()
	 * @method bool isUserContactChanged()
	 * @method \string remindActualUserContact()
	 * @method \string requireUserContact()
	 * @method \Bitrix\Catalog\EO_Subscribe resetUserContact()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetUserContact()
	 * @method \string fillUserContact()
	 * @method \int getContactType()
	 * @method \Bitrix\Catalog\EO_Subscribe setContactType(\int|\Bitrix\Main\DB\SqlExpression $contactType)
	 * @method bool hasContactType()
	 * @method bool isContactTypeFilled()
	 * @method bool isContactTypeChanged()
	 * @method \int remindActualContactType()
	 * @method \int requireContactType()
	 * @method \Bitrix\Catalog\EO_Subscribe resetContactType()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetContactType()
	 * @method \int fillContactType()
	 * @method \int getUserId()
	 * @method \Bitrix\Catalog\EO_Subscribe setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Catalog\EO_Subscribe resetUserId()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Catalog\EO_Subscribe setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Catalog\EO_Subscribe resetUser()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \int getItemId()
	 * @method \Bitrix\Catalog\EO_Subscribe setItemId(\int|\Bitrix\Main\DB\SqlExpression $itemId)
	 * @method bool hasItemId()
	 * @method bool isItemIdFilled()
	 * @method bool isItemIdChanged()
	 * @method \int remindActualItemId()
	 * @method \int requireItemId()
	 * @method \Bitrix\Catalog\EO_Subscribe resetItemId()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetItemId()
	 * @method \int fillItemId()
	 * @method \Bitrix\Catalog\EO_Product getProduct()
	 * @method \Bitrix\Catalog\EO_Product remindActualProduct()
	 * @method \Bitrix\Catalog\EO_Product requireProduct()
	 * @method \Bitrix\Catalog\EO_Subscribe setProduct(\Bitrix\Catalog\EO_Product $object)
	 * @method \Bitrix\Catalog\EO_Subscribe resetProduct()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetProduct()
	 * @method bool hasProduct()
	 * @method bool isProductFilled()
	 * @method bool isProductChanged()
	 * @method \Bitrix\Catalog\EO_Product fillProduct()
	 * @method \Bitrix\Iblock\EO_Element getIblockElement()
	 * @method \Bitrix\Iblock\EO_Element remindActualIblockElement()
	 * @method \Bitrix\Iblock\EO_Element requireIblockElement()
	 * @method \Bitrix\Catalog\EO_Subscribe setIblockElement(\Bitrix\Iblock\EO_Element $object)
	 * @method \Bitrix\Catalog\EO_Subscribe resetIblockElement()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetIblockElement()
	 * @method bool hasIblockElement()
	 * @method bool isIblockElementFilled()
	 * @method bool isIblockElementChanged()
	 * @method \Bitrix\Iblock\EO_Element fillIblockElement()
	 * @method \boolean getNeedSending()
	 * @method \Bitrix\Catalog\EO_Subscribe setNeedSending(\boolean|\Bitrix\Main\DB\SqlExpression $needSending)
	 * @method bool hasNeedSending()
	 * @method bool isNeedSendingFilled()
	 * @method bool isNeedSendingChanged()
	 * @method \boolean remindActualNeedSending()
	 * @method \boolean requireNeedSending()
	 * @method \Bitrix\Catalog\EO_Subscribe resetNeedSending()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetNeedSending()
	 * @method \boolean fillNeedSending()
	 * @method \string getSiteId()
	 * @method \Bitrix\Catalog\EO_Subscribe setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Catalog\EO_Subscribe resetSiteId()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \int getLandingSiteId()
	 * @method \Bitrix\Catalog\EO_Subscribe setLandingSiteId(\int|\Bitrix\Main\DB\SqlExpression $landingSiteId)
	 * @method bool hasLandingSiteId()
	 * @method bool isLandingSiteIdFilled()
	 * @method bool isLandingSiteIdChanged()
	 * @method \int remindActualLandingSiteId()
	 * @method \int requireLandingSiteId()
	 * @method \Bitrix\Catalog\EO_Subscribe resetLandingSiteId()
	 * @method \Bitrix\Catalog\EO_Subscribe unsetLandingSiteId()
	 * @method \int fillLandingSiteId()
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
	 * @method \Bitrix\Catalog\EO_Subscribe set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Subscribe reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Subscribe unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Subscribe wakeUp($data)
	 */
	class EO_Subscribe {
		/* @var \Bitrix\Catalog\SubscribeTable */
		static public $dataClass = '\Bitrix\Catalog\SubscribeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Subscribe_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getDateToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateTo()
	 * @method \string[] getUserContactList()
	 * @method \string[] fillUserContact()
	 * @method \int[] getContactTypeList()
	 * @method \int[] fillContactType()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Catalog\EO_Subscribe_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \int[] getItemIdList()
	 * @method \int[] fillItemId()
	 * @method \Bitrix\Catalog\EO_Product[] getProductList()
	 * @method \Bitrix\Catalog\EO_Subscribe_Collection getProductCollection()
	 * @method \Bitrix\Catalog\EO_Product_Collection fillProduct()
	 * @method \Bitrix\Iblock\EO_Element[] getIblockElementList()
	 * @method \Bitrix\Catalog\EO_Subscribe_Collection getIblockElementCollection()
	 * @method \Bitrix\Iblock\EO_Element_Collection fillIblockElement()
	 * @method \boolean[] getNeedSendingList()
	 * @method \boolean[] fillNeedSending()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \int[] getLandingSiteIdList()
	 * @method \int[] fillLandingSiteId()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Subscribe $object)
	 * @method bool has(\Bitrix\Catalog\EO_Subscribe $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Subscribe getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Subscribe[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Subscribe $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Subscribe_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Subscribe current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Subscribe_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\SubscribeTable */
		static public $dataClass = '\Bitrix\Catalog\SubscribeTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Subscribe_Result exec()
	 * @method \Bitrix\Catalog\EO_Subscribe fetchObject()
	 * @method \Bitrix\Catalog\EO_Subscribe_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Subscribe_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Subscribe fetchObject()
	 * @method \Bitrix\Catalog\EO_Subscribe_Collection fetchCollection()
	 */
	class EO_Subscribe_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Subscribe createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Subscribe_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Subscribe wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Subscribe_Collection wakeUpCollection($rows)
	 */
	class EO_Subscribe_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\SubscribeAccessTable:catalog\lib\subscribeaccess.php */
namespace Bitrix\Catalog {
	/**
	 * EO_SubscribeAccess
	 * @see \Bitrix\Catalog\SubscribeAccessTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getDateFrom()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess setDateFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateFrom)
	 * @method bool hasDateFrom()
	 * @method bool isDateFromFilled()
	 * @method bool isDateFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateFrom()
	 * @method \Bitrix\Main\Type\DateTime requireDateFrom()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess resetDateFrom()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess unsetDateFrom()
	 * @method \Bitrix\Main\Type\DateTime fillDateFrom()
	 * @method \string getUserContact()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess setUserContact(\string|\Bitrix\Main\DB\SqlExpression $userContact)
	 * @method bool hasUserContact()
	 * @method bool isUserContactFilled()
	 * @method bool isUserContactChanged()
	 * @method \string remindActualUserContact()
	 * @method \string requireUserContact()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess resetUserContact()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess unsetUserContact()
	 * @method \string fillUserContact()
	 * @method \Bitrix\Catalog\EO_Subscribe getSubscribe()
	 * @method \Bitrix\Catalog\EO_Subscribe remindActualSubscribe()
	 * @method \Bitrix\Catalog\EO_Subscribe requireSubscribe()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess setSubscribe(\Bitrix\Catalog\EO_Subscribe $object)
	 * @method \Bitrix\Catalog\EO_SubscribeAccess resetSubscribe()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess unsetSubscribe()
	 * @method bool hasSubscribe()
	 * @method bool isSubscribeFilled()
	 * @method bool isSubscribeChanged()
	 * @method \Bitrix\Catalog\EO_Subscribe fillSubscribe()
	 * @method \string getToken()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess setToken(\string|\Bitrix\Main\DB\SqlExpression $token)
	 * @method bool hasToken()
	 * @method bool isTokenFilled()
	 * @method bool isTokenChanged()
	 * @method \string remindActualToken()
	 * @method \string requireToken()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess resetToken()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess unsetToken()
	 * @method \string fillToken()
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
	 * @method \Bitrix\Catalog\EO_SubscribeAccess set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_SubscribeAccess reset($fieldName)
	 * @method \Bitrix\Catalog\EO_SubscribeAccess unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_SubscribeAccess wakeUp($data)
	 */
	class EO_SubscribeAccess {
		/* @var \Bitrix\Catalog\SubscribeAccessTable */
		static public $dataClass = '\Bitrix\Catalog\SubscribeAccessTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_SubscribeAccess_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getDateFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateFrom()
	 * @method \string[] getUserContactList()
	 * @method \string[] fillUserContact()
	 * @method \Bitrix\Catalog\EO_Subscribe[] getSubscribeList()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess_Collection getSubscribeCollection()
	 * @method \Bitrix\Catalog\EO_Subscribe_Collection fillSubscribe()
	 * @method \string[] getTokenList()
	 * @method \string[] fillToken()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_SubscribeAccess $object)
	 * @method bool has(\Bitrix\Catalog\EO_SubscribeAccess $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_SubscribeAccess getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_SubscribeAccess[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_SubscribeAccess $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_SubscribeAccess_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_SubscribeAccess current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_SubscribeAccess_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\SubscribeAccessTable */
		static public $dataClass = '\Bitrix\Catalog\SubscribeAccessTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SubscribeAccess_Result exec()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess fetchObject()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SubscribeAccess_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_SubscribeAccess fetchObject()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess_Collection fetchCollection()
	 */
	class EO_SubscribeAccess_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_SubscribeAccess createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_SubscribeAccess_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_SubscribeAccess wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_SubscribeAccess_Collection wakeUpCollection($rows)
	 */
	class EO_SubscribeAccess_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductTable:catalog\lib\v2\Integration\Seo\Entity\ExportedProductTable.php */
namespace Bitrix\Catalog\v2\Integration\Seo\Entity {
	/**
	 * ExportedProduct
	 * @see \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getProductId()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct setProductId(\int|\Bitrix\Main\DB\SqlExpression $productId)
	 * @method bool hasProductId()
	 * @method bool isProductIdFilled()
	 * @method bool isProductIdChanged()
	 * @method \int remindActualProductId()
	 * @method \int requireProductId()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct resetProductId()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct unsetProductId()
	 * @method \int fillProductId()
	 * @method \string getServiceId()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct setServiceId(\string|\Bitrix\Main\DB\SqlExpression $serviceId)
	 * @method bool hasServiceId()
	 * @method bool isServiceIdFilled()
	 * @method bool isServiceIdChanged()
	 * @method \string remindActualServiceId()
	 * @method \string requireServiceId()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct resetServiceId()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct unsetServiceId()
	 * @method \string fillServiceId()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct resetTimestampX()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getError()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct setError(\string|\Bitrix\Main\DB\SqlExpression $error)
	 * @method bool hasError()
	 * @method bool isErrorFilled()
	 * @method bool isErrorChanged()
	 * @method \string remindActualError()
	 * @method \string requireError()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct resetError()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct unsetError()
	 * @method \string fillError()
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
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct set($fieldName, $value)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct reset($fieldName)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct wakeUp($data)
	 */
	class EO_ExportedProduct {
		/* @var \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductTable */
		static public $dataClass = '\Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog\v2\Integration\Seo\Entity {
	/**
	 * ExportedProductCollection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getProductIdList()
	 * @method \int[] fillProductId()
	 * @method \string[] getServiceIdList()
	 * @method \string[] fillServiceId()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getErrorList()
	 * @method \string[] fillError()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct $object)
	 * @method bool has(\Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct getByPrimary($primary)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct[] getAll()
	 * @method bool remove(\Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductCollection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ExportedProduct_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductTable */
		static public $dataClass = '\Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductTable';
	}
}
namespace Bitrix\Catalog\v2\Integration\Seo\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ExportedProduct_Result exec()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct fetchObject()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductCollection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ExportedProduct_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct fetchObject()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductCollection fetchCollection()
	 */
	class EO_ExportedProduct_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductCollection createCollection()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProduct wakeUpObject($row)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductCollection wakeUpCollection($rows)
	 */
	class EO_ExportedProduct_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductQueueTable:catalog\lib\v2\Integration\Seo\Entity\ExportedProductQueueTable.php */
namespace Bitrix\Catalog\v2\Integration\Seo\Entity {
	/**
	 * EO_ExportedProductQueue
	 * @see \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductQueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getQueueId()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue setQueueId(\int|\Bitrix\Main\DB\SqlExpression $queueId)
	 * @method bool hasQueueId()
	 * @method bool isQueueIdFilled()
	 * @method bool isQueueIdChanged()
	 * @method \string getProductIds()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue setProductIds(\string|\Bitrix\Main\DB\SqlExpression $productIds)
	 * @method bool hasProductIds()
	 * @method bool isProductIdsFilled()
	 * @method bool isProductIdsChanged()
	 * @method \string remindActualProductIds()
	 * @method \string requireProductIds()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue resetProductIds()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue unsetProductIds()
	 * @method \string fillProductIds()
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
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue set($fieldName, $value)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue reset($fieldName)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue wakeUp($data)
	 */
	class EO_ExportedProductQueue {
		/* @var \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductQueueTable */
		static public $dataClass = '\Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductQueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog\v2\Integration\Seo\Entity {
	/**
	 * EO_ExportedProductQueue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getQueueIdList()
	 * @method \string[] getProductIdsList()
	 * @method \string[] fillProductIds()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue $object)
	 * @method bool has(\Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue getByPrimary($primary)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue[] getAll()
	 * @method bool remove(\Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_ExportedProductQueue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductQueueTable */
		static public $dataClass = '\Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductQueueTable';
	}
}
namespace Bitrix\Catalog\v2\Integration\Seo\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ExportedProductQueue_Result exec()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue fetchObject()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ExportedProductQueue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue fetchObject()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue_Collection fetchCollection()
	 */
	class EO_ExportedProductQueue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue_Collection createCollection()
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue wakeUpObject($row)
	 * @method \Bitrix\Catalog\v2\Integration\Seo\Entity\EO_ExportedProductQueue_Collection wakeUpCollection($rows)
	 */
	class EO_ExportedProductQueue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Catalog\VatTable:catalog\lib\vat.php */
namespace Bitrix\Catalog {
	/**
	 * EO_Vat
	 * @see \Bitrix\Catalog\VatTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Catalog\EO_Vat setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Catalog\EO_Vat setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Catalog\EO_Vat resetTimestampX()
	 * @method \Bitrix\Catalog\EO_Vat unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \boolean getActive()
	 * @method \Bitrix\Catalog\EO_Vat setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Catalog\EO_Vat resetActive()
	 * @method \Bitrix\Catalog\EO_Vat unsetActive()
	 * @method \boolean fillActive()
	 * @method \int getSort()
	 * @method \Bitrix\Catalog\EO_Vat setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Catalog\EO_Vat resetSort()
	 * @method \Bitrix\Catalog\EO_Vat unsetSort()
	 * @method \int fillSort()
	 * @method \string getName()
	 * @method \Bitrix\Catalog\EO_Vat setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Catalog\EO_Vat resetName()
	 * @method \Bitrix\Catalog\EO_Vat unsetName()
	 * @method \string fillName()
	 * @method \float getRate()
	 * @method \Bitrix\Catalog\EO_Vat setRate(\float|\Bitrix\Main\DB\SqlExpression $rate)
	 * @method bool hasRate()
	 * @method bool isRateFilled()
	 * @method bool isRateChanged()
	 * @method \float remindActualRate()
	 * @method \float requireRate()
	 * @method \Bitrix\Catalog\EO_Vat resetRate()
	 * @method \Bitrix\Catalog\EO_Vat unsetRate()
	 * @method \float fillRate()
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
	 * @method \Bitrix\Catalog\EO_Vat set($fieldName, $value)
	 * @method \Bitrix\Catalog\EO_Vat reset($fieldName)
	 * @method \Bitrix\Catalog\EO_Vat unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Catalog\EO_Vat wakeUp($data)
	 */
	class EO_Vat {
		/* @var \Bitrix\Catalog\VatTable */
		static public $dataClass = '\Bitrix\Catalog\VatTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Catalog {
	/**
	 * EO_Vat_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \float[] getRateList()
	 * @method \float[] fillRate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Catalog\EO_Vat $object)
	 * @method bool has(\Bitrix\Catalog\EO_Vat $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Vat getByPrimary($primary)
	 * @method \Bitrix\Catalog\EO_Vat[] getAll()
	 * @method bool remove(\Bitrix\Catalog\EO_Vat $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Catalog\EO_Vat_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Catalog\EO_Vat current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Vat_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Catalog\VatTable */
		static public $dataClass = '\Bitrix\Catalog\VatTable';
	}
}
namespace Bitrix\Catalog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Vat_Result exec()
	 * @method \Bitrix\Catalog\EO_Vat fetchObject()
	 * @method \Bitrix\Catalog\EO_Vat_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Vat_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Catalog\EO_Vat fetchObject()
	 * @method \Bitrix\Catalog\EO_Vat_Collection fetchCollection()
	 */
	class EO_Vat_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Catalog\EO_Vat createObject($setDefaultValues = true)
	 * @method \Bitrix\Catalog\EO_Vat_Collection createCollection()
	 * @method \Bitrix\Catalog\EO_Vat wakeUpObject($row)
	 * @method \Bitrix\Catalog\EO_Vat_Collection wakeUpCollection($rows)
	 */
	class EO_Vat_Entity extends \Bitrix\Main\ORM\Entity {}
}