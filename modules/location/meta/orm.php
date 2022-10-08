<?php

/* ORMENTITYANNOTATION:Bitrix\Location\Model\AddressFieldTable:location/lib/model/addressfieldtable.php:8240c50789d3f5ffd298d5d38603e379 */
namespace Bitrix\Location\Model {
	/**
	 * EO_AddressField
	 * @see \Bitrix\Location\Model\AddressFieldTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getAddressId()
	 * @method \Bitrix\Location\Model\EO_AddressField setAddressId(\int|\Bitrix\Main\DB\SqlExpression $addressId)
	 * @method bool hasAddressId()
	 * @method bool isAddressIdFilled()
	 * @method bool isAddressIdChanged()
	 * @method \int getType()
	 * @method \Bitrix\Location\Model\EO_AddressField setType(\int|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string getValue()
	 * @method \Bitrix\Location\Model\EO_AddressField setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Location\Model\EO_AddressField resetValue()
	 * @method \Bitrix\Location\Model\EO_AddressField unsetValue()
	 * @method \string fillValue()
	 * @method \string getValueNormalized()
	 * @method \Bitrix\Location\Model\EO_AddressField setValueNormalized(\string|\Bitrix\Main\DB\SqlExpression $valueNormalized)
	 * @method bool hasValueNormalized()
	 * @method bool isValueNormalizedFilled()
	 * @method bool isValueNormalizedChanged()
	 * @method \string remindActualValueNormalized()
	 * @method \string requireValueNormalized()
	 * @method \Bitrix\Location\Model\EO_AddressField resetValueNormalized()
	 * @method \Bitrix\Location\Model\EO_AddressField unsetValueNormalized()
	 * @method \string fillValueNormalized()
	 * @method \Bitrix\Location\Model\EO_Address getAddress()
	 * @method \Bitrix\Location\Model\EO_Address remindActualAddress()
	 * @method \Bitrix\Location\Model\EO_Address requireAddress()
	 * @method \Bitrix\Location\Model\EO_AddressField setAddress(\Bitrix\Location\Model\EO_Address $object)
	 * @method \Bitrix\Location\Model\EO_AddressField resetAddress()
	 * @method \Bitrix\Location\Model\EO_AddressField unsetAddress()
	 * @method bool hasAddress()
	 * @method bool isAddressFilled()
	 * @method bool isAddressChanged()
	 * @method \Bitrix\Location\Model\EO_Address fillAddress()
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
	 * @method \Bitrix\Location\Model\EO_AddressField set($fieldName, $value)
	 * @method \Bitrix\Location\Model\EO_AddressField reset($fieldName)
	 * @method \Bitrix\Location\Model\EO_AddressField unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Location\Model\EO_AddressField wakeUp($data)
	 */
	class EO_AddressField {
		/* @var \Bitrix\Location\Model\AddressFieldTable */
		static public $dataClass = '\Bitrix\Location\Model\AddressFieldTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Location\Model {
	/**
	 * EO_AddressField_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getAddressIdList()
	 * @method \int[] getTypeList()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 * @method \string[] getValueNormalizedList()
	 * @method \string[] fillValueNormalized()
	 * @method \Bitrix\Location\Model\EO_Address[] getAddressList()
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection getAddressCollection()
	 * @method \Bitrix\Location\Model\EO_Address_Collection fillAddress()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Location\Model\EO_AddressField $object)
	 * @method bool has(\Bitrix\Location\Model\EO_AddressField $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_AddressField getByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_AddressField[] getAll()
	 * @method bool remove(\Bitrix\Location\Model\EO_AddressField $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Location\Model\EO_AddressField_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Location\Model\EO_AddressField current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_AddressField_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Location\Model\AddressFieldTable */
		static public $dataClass = '\Bitrix\Location\Model\AddressFieldTable';
	}
}
namespace Bitrix\Location\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_AddressField_Result exec()
	 * @method \Bitrix\Location\Model\EO_AddressField fetchObject()
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_AddressField_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Location\Model\EO_AddressField fetchObject()
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection fetchCollection()
	 */
	class EO_AddressField_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Location\Model\EO_AddressField createObject($setDefaultValues = true)
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection createCollection()
	 * @method \Bitrix\Location\Model\EO_AddressField wakeUpObject($row)
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection wakeUpCollection($rows)
	 */
	class EO_AddressField_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Location\Model\AddressLinkTable:location/lib/model/addresslinktable.php:a20ce261bc1287dc68212b034116a6b4 */
namespace Bitrix\Location\Model {
	/**
	 * EO_AddressLink
	 * @see \Bitrix\Location\Model\AddressLinkTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getAddressId()
	 * @method \Bitrix\Location\Model\EO_AddressLink setAddressId(\int|\Bitrix\Main\DB\SqlExpression $addressId)
	 * @method bool hasAddressId()
	 * @method bool isAddressIdFilled()
	 * @method bool isAddressIdChanged()
	 * @method \string getEntityId()
	 * @method \Bitrix\Location\Model\EO_AddressLink setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string getEntityType()
	 * @method \Bitrix\Location\Model\EO_AddressLink setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \Bitrix\Location\Model\EO_Address getAddress()
	 * @method \Bitrix\Location\Model\EO_Address remindActualAddress()
	 * @method \Bitrix\Location\Model\EO_Address requireAddress()
	 * @method \Bitrix\Location\Model\EO_AddressLink setAddress(\Bitrix\Location\Model\EO_Address $object)
	 * @method \Bitrix\Location\Model\EO_AddressLink resetAddress()
	 * @method \Bitrix\Location\Model\EO_AddressLink unsetAddress()
	 * @method bool hasAddress()
	 * @method bool isAddressFilled()
	 * @method bool isAddressChanged()
	 * @method \Bitrix\Location\Model\EO_Address fillAddress()
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
	 * @method \Bitrix\Location\Model\EO_AddressLink set($fieldName, $value)
	 * @method \Bitrix\Location\Model\EO_AddressLink reset($fieldName)
	 * @method \Bitrix\Location\Model\EO_AddressLink unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Location\Model\EO_AddressLink wakeUp($data)
	 */
	class EO_AddressLink {
		/* @var \Bitrix\Location\Model\AddressLinkTable */
		static public $dataClass = '\Bitrix\Location\Model\AddressLinkTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Location\Model {
	/**
	 * EO_AddressLink_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getAddressIdList()
	 * @method \string[] getEntityIdList()
	 * @method \string[] getEntityTypeList()
	 * @method \Bitrix\Location\Model\EO_Address[] getAddressList()
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection getAddressCollection()
	 * @method \Bitrix\Location\Model\EO_Address_Collection fillAddress()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Location\Model\EO_AddressLink $object)
	 * @method bool has(\Bitrix\Location\Model\EO_AddressLink $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_AddressLink getByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_AddressLink[] getAll()
	 * @method bool remove(\Bitrix\Location\Model\EO_AddressLink $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Location\Model\EO_AddressLink_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Location\Model\EO_AddressLink current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_AddressLink_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Location\Model\AddressLinkTable */
		static public $dataClass = '\Bitrix\Location\Model\AddressLinkTable';
	}
}
namespace Bitrix\Location\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_AddressLink_Result exec()
	 * @method \Bitrix\Location\Model\EO_AddressLink fetchObject()
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_AddressLink_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Location\Model\EO_AddressLink fetchObject()
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection fetchCollection()
	 */
	class EO_AddressLink_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Location\Model\EO_AddressLink createObject($setDefaultValues = true)
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection createCollection()
	 * @method \Bitrix\Location\Model\EO_AddressLink wakeUpObject($row)
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection wakeUpCollection($rows)
	 */
	class EO_AddressLink_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Location\Model\AddressTable:location/lib/model/addresstable.php:2bf4d8723d3294fcf7bf3259d7c594e4 */
namespace Bitrix\Location\Model {
	/**
	 * EO_Address
	 * @see \Bitrix\Location\Model\AddressTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Location\Model\EO_Address setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getLocationId()
	 * @method \Bitrix\Location\Model\EO_Address setLocationId(\int|\Bitrix\Main\DB\SqlExpression $locationId)
	 * @method bool hasLocationId()
	 * @method bool isLocationIdFilled()
	 * @method bool isLocationIdChanged()
	 * @method \int remindActualLocationId()
	 * @method \int requireLocationId()
	 * @method \Bitrix\Location\Model\EO_Address resetLocationId()
	 * @method \Bitrix\Location\Model\EO_Address unsetLocationId()
	 * @method \int fillLocationId()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Location\Model\EO_Address setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string remindActualLanguageId()
	 * @method \string requireLanguageId()
	 * @method \Bitrix\Location\Model\EO_Address resetLanguageId()
	 * @method \Bitrix\Location\Model\EO_Address unsetLanguageId()
	 * @method \string fillLanguageId()
	 * @method \float getLatitude()
	 * @method \Bitrix\Location\Model\EO_Address setLatitude(\float|\Bitrix\Main\DB\SqlExpression $latitude)
	 * @method bool hasLatitude()
	 * @method bool isLatitudeFilled()
	 * @method bool isLatitudeChanged()
	 * @method \float remindActualLatitude()
	 * @method \float requireLatitude()
	 * @method \Bitrix\Location\Model\EO_Address resetLatitude()
	 * @method \Bitrix\Location\Model\EO_Address unsetLatitude()
	 * @method \float fillLatitude()
	 * @method \float getLongitude()
	 * @method \Bitrix\Location\Model\EO_Address setLongitude(\float|\Bitrix\Main\DB\SqlExpression $longitude)
	 * @method bool hasLongitude()
	 * @method bool isLongitudeFilled()
	 * @method bool isLongitudeChanged()
	 * @method \float remindActualLongitude()
	 * @method \float requireLongitude()
	 * @method \Bitrix\Location\Model\EO_Address resetLongitude()
	 * @method \Bitrix\Location\Model\EO_Address unsetLongitude()
	 * @method \float fillLongitude()
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection getFields()
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection requireFields()
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection fillFields()
	 * @method bool hasFields()
	 * @method bool isFieldsFilled()
	 * @method bool isFieldsChanged()
	 * @method void addToFields(\Bitrix\Location\Model\EO_AddressField $addressField)
	 * @method void removeFromFields(\Bitrix\Location\Model\EO_AddressField $addressField)
	 * @method void removeAllFields()
	 * @method \Bitrix\Location\Model\EO_Address resetFields()
	 * @method \Bitrix\Location\Model\EO_Address unsetFields()
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection getLinks()
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection requireLinks()
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection fillLinks()
	 * @method bool hasLinks()
	 * @method bool isLinksFilled()
	 * @method bool isLinksChanged()
	 * @method void addToLinks(\Bitrix\Location\Model\EO_AddressLink $addressLink)
	 * @method void removeFromLinks(\Bitrix\Location\Model\EO_AddressLink $addressLink)
	 * @method void removeAllLinks()
	 * @method \Bitrix\Location\Model\EO_Address resetLinks()
	 * @method \Bitrix\Location\Model\EO_Address unsetLinks()
	 * @method \Bitrix\Location\Model\EO_Location getLocation()
	 * @method \Bitrix\Location\Model\EO_Location remindActualLocation()
	 * @method \Bitrix\Location\Model\EO_Location requireLocation()
	 * @method \Bitrix\Location\Model\EO_Address setLocation(\Bitrix\Location\Model\EO_Location $object)
	 * @method \Bitrix\Location\Model\EO_Address resetLocation()
	 * @method \Bitrix\Location\Model\EO_Address unsetLocation()
	 * @method bool hasLocation()
	 * @method bool isLocationFilled()
	 * @method bool isLocationChanged()
	 * @method \Bitrix\Location\Model\EO_Location fillLocation()
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
	 * @method \Bitrix\Location\Model\EO_Address set($fieldName, $value)
	 * @method \Bitrix\Location\Model\EO_Address reset($fieldName)
	 * @method \Bitrix\Location\Model\EO_Address unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Location\Model\EO_Address wakeUp($data)
	 */
	class EO_Address {
		/* @var \Bitrix\Location\Model\AddressTable */
		static public $dataClass = '\Bitrix\Location\Model\AddressTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Location\Model {
	/**
	 * EO_Address_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getLocationIdList()
	 * @method \int[] fillLocationId()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] fillLanguageId()
	 * @method \float[] getLatitudeList()
	 * @method \float[] fillLatitude()
	 * @method \float[] getLongitudeList()
	 * @method \float[] fillLongitude()
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection[] getFieldsList()
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection getFieldsCollection()
	 * @method \Bitrix\Location\Model\EO_AddressField_Collection fillFields()
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection[] getLinksList()
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection getLinksCollection()
	 * @method \Bitrix\Location\Model\EO_AddressLink_Collection fillLinks()
	 * @method \Bitrix\Location\Model\EO_Location[] getLocationList()
	 * @method \Bitrix\Location\Model\EO_Address_Collection getLocationCollection()
	 * @method \Bitrix\Location\Model\EO_Location_Collection fillLocation()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Location\Model\EO_Address $object)
	 * @method bool has(\Bitrix\Location\Model\EO_Address $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_Address getByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_Address[] getAll()
	 * @method bool remove(\Bitrix\Location\Model\EO_Address $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Location\Model\EO_Address_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Location\Model\EO_Address current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Address_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Location\Model\AddressTable */
		static public $dataClass = '\Bitrix\Location\Model\AddressTable';
	}
}
namespace Bitrix\Location\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Address_Result exec()
	 * @method \Bitrix\Location\Model\EO_Address fetchObject()
	 * @method \Bitrix\Location\Model\EO_Address_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Address_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Location\Model\EO_Address fetchObject()
	 * @method \Bitrix\Location\Model\EO_Address_Collection fetchCollection()
	 */
	class EO_Address_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Location\Model\EO_Address createObject($setDefaultValues = true)
	 * @method \Bitrix\Location\Model\EO_Address_Collection createCollection()
	 * @method \Bitrix\Location\Model\EO_Address wakeUpObject($row)
	 * @method \Bitrix\Location\Model\EO_Address_Collection wakeUpCollection($rows)
	 */
	class EO_Address_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Location\Model\HierarchyTable:location/lib/model/hierarchytable.php:f5fa0ff4293828e5d3303062df8a5cb6 */
namespace Bitrix\Location\Model {
	/**
	 * EO_Hierarchy
	 * @see \Bitrix\Location\Model\HierarchyTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getAncestorId()
	 * @method \Bitrix\Location\Model\EO_Hierarchy setAncestorId(\int|\Bitrix\Main\DB\SqlExpression $ancestorId)
	 * @method bool hasAncestorId()
	 * @method bool isAncestorIdFilled()
	 * @method bool isAncestorIdChanged()
	 * @method \int getDescendantId()
	 * @method \Bitrix\Location\Model\EO_Hierarchy setDescendantId(\int|\Bitrix\Main\DB\SqlExpression $descendantId)
	 * @method bool hasDescendantId()
	 * @method bool isDescendantIdFilled()
	 * @method bool isDescendantIdChanged()
	 * @method \int getLevel()
	 * @method \Bitrix\Location\Model\EO_Hierarchy setLevel(\int|\Bitrix\Main\DB\SqlExpression $level)
	 * @method bool hasLevel()
	 * @method bool isLevelFilled()
	 * @method bool isLevelChanged()
	 * @method \int remindActualLevel()
	 * @method \int requireLevel()
	 * @method \Bitrix\Location\Model\EO_Hierarchy resetLevel()
	 * @method \Bitrix\Location\Model\EO_Hierarchy unsetLevel()
	 * @method \int fillLevel()
	 * @method \Bitrix\Location\Model\EO_Location getAncestor()
	 * @method \Bitrix\Location\Model\EO_Location remindActualAncestor()
	 * @method \Bitrix\Location\Model\EO_Location requireAncestor()
	 * @method \Bitrix\Location\Model\EO_Hierarchy setAncestor(\Bitrix\Location\Model\EO_Location $object)
	 * @method \Bitrix\Location\Model\EO_Hierarchy resetAncestor()
	 * @method \Bitrix\Location\Model\EO_Hierarchy unsetAncestor()
	 * @method bool hasAncestor()
	 * @method bool isAncestorFilled()
	 * @method bool isAncestorChanged()
	 * @method \Bitrix\Location\Model\EO_Location fillAncestor()
	 * @method \Bitrix\Location\Model\EO_Location getDescendant()
	 * @method \Bitrix\Location\Model\EO_Location remindActualDescendant()
	 * @method \Bitrix\Location\Model\EO_Location requireDescendant()
	 * @method \Bitrix\Location\Model\EO_Hierarchy setDescendant(\Bitrix\Location\Model\EO_Location $object)
	 * @method \Bitrix\Location\Model\EO_Hierarchy resetDescendant()
	 * @method \Bitrix\Location\Model\EO_Hierarchy unsetDescendant()
	 * @method bool hasDescendant()
	 * @method bool isDescendantFilled()
	 * @method bool isDescendantChanged()
	 * @method \Bitrix\Location\Model\EO_Location fillDescendant()
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
	 * @method \Bitrix\Location\Model\EO_Hierarchy set($fieldName, $value)
	 * @method \Bitrix\Location\Model\EO_Hierarchy reset($fieldName)
	 * @method \Bitrix\Location\Model\EO_Hierarchy unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Location\Model\EO_Hierarchy wakeUp($data)
	 */
	class EO_Hierarchy {
		/* @var \Bitrix\Location\Model\HierarchyTable */
		static public $dataClass = '\Bitrix\Location\Model\HierarchyTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Location\Model {
	/**
	 * EO_Hierarchy_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getAncestorIdList()
	 * @method \int[] getDescendantIdList()
	 * @method \int[] getLevelList()
	 * @method \int[] fillLevel()
	 * @method \Bitrix\Location\Model\EO_Location[] getAncestorList()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection getAncestorCollection()
	 * @method \Bitrix\Location\Model\EO_Location_Collection fillAncestor()
	 * @method \Bitrix\Location\Model\EO_Location[] getDescendantList()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection getDescendantCollection()
	 * @method \Bitrix\Location\Model\EO_Location_Collection fillDescendant()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Location\Model\EO_Hierarchy $object)
	 * @method bool has(\Bitrix\Location\Model\EO_Hierarchy $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_Hierarchy getByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_Hierarchy[] getAll()
	 * @method bool remove(\Bitrix\Location\Model\EO_Hierarchy $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Location\Model\EO_Hierarchy_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Location\Model\EO_Hierarchy current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Hierarchy_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Location\Model\HierarchyTable */
		static public $dataClass = '\Bitrix\Location\Model\HierarchyTable';
	}
}
namespace Bitrix\Location\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Hierarchy_Result exec()
	 * @method \Bitrix\Location\Model\EO_Hierarchy fetchObject()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Hierarchy_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Location\Model\EO_Hierarchy fetchObject()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection fetchCollection()
	 */
	class EO_Hierarchy_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Location\Model\EO_Hierarchy createObject($setDefaultValues = true)
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection createCollection()
	 * @method \Bitrix\Location\Model\EO_Hierarchy wakeUpObject($row)
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection wakeUpCollection($rows)
	 */
	class EO_Hierarchy_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Location\Model\LocationFieldTable:location/lib/model/locationfieldtable.php:ab94cf47529c61b642baaada26a63e85 */
namespace Bitrix\Location\Model {
	/**
	 * EO_LocationField
	 * @see \Bitrix\Location\Model\LocationFieldTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getLocationId()
	 * @method \Bitrix\Location\Model\EO_LocationField setLocationId(\int|\Bitrix\Main\DB\SqlExpression $locationId)
	 * @method bool hasLocationId()
	 * @method bool isLocationIdFilled()
	 * @method bool isLocationIdChanged()
	 * @method \int getType()
	 * @method \Bitrix\Location\Model\EO_LocationField setType(\int|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string getValue()
	 * @method \Bitrix\Location\Model\EO_LocationField setValue(\string|\Bitrix\Main\DB\SqlExpression $value)
	 * @method bool hasValue()
	 * @method bool isValueFilled()
	 * @method bool isValueChanged()
	 * @method \string remindActualValue()
	 * @method \string requireValue()
	 * @method \Bitrix\Location\Model\EO_LocationField resetValue()
	 * @method \Bitrix\Location\Model\EO_LocationField unsetValue()
	 * @method \string fillValue()
	 * @method \Bitrix\Location\Model\EO_Location getLocation()
	 * @method \Bitrix\Location\Model\EO_Location remindActualLocation()
	 * @method \Bitrix\Location\Model\EO_Location requireLocation()
	 * @method \Bitrix\Location\Model\EO_LocationField setLocation(\Bitrix\Location\Model\EO_Location $object)
	 * @method \Bitrix\Location\Model\EO_LocationField resetLocation()
	 * @method \Bitrix\Location\Model\EO_LocationField unsetLocation()
	 * @method bool hasLocation()
	 * @method bool isLocationFilled()
	 * @method bool isLocationChanged()
	 * @method \Bitrix\Location\Model\EO_Location fillLocation()
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
	 * @method \Bitrix\Location\Model\EO_LocationField set($fieldName, $value)
	 * @method \Bitrix\Location\Model\EO_LocationField reset($fieldName)
	 * @method \Bitrix\Location\Model\EO_LocationField unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Location\Model\EO_LocationField wakeUp($data)
	 */
	class EO_LocationField {
		/* @var \Bitrix\Location\Model\LocationFieldTable */
		static public $dataClass = '\Bitrix\Location\Model\LocationFieldTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Location\Model {
	/**
	 * EO_LocationField_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getLocationIdList()
	 * @method \int[] getTypeList()
	 * @method \string[] getValueList()
	 * @method \string[] fillValue()
	 * @method \Bitrix\Location\Model\EO_Location[] getLocationList()
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection getLocationCollection()
	 * @method \Bitrix\Location\Model\EO_Location_Collection fillLocation()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Location\Model\EO_LocationField $object)
	 * @method bool has(\Bitrix\Location\Model\EO_LocationField $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_LocationField getByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_LocationField[] getAll()
	 * @method bool remove(\Bitrix\Location\Model\EO_LocationField $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Location\Model\EO_LocationField_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Location\Model\EO_LocationField current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LocationField_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Location\Model\LocationFieldTable */
		static public $dataClass = '\Bitrix\Location\Model\LocationFieldTable';
	}
}
namespace Bitrix\Location\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LocationField_Result exec()
	 * @method \Bitrix\Location\Model\EO_LocationField fetchObject()
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LocationField_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Location\Model\EO_LocationField fetchObject()
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection fetchCollection()
	 */
	class EO_LocationField_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Location\Model\EO_LocationField createObject($setDefaultValues = true)
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection createCollection()
	 * @method \Bitrix\Location\Model\EO_LocationField wakeUpObject($row)
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection wakeUpCollection($rows)
	 */
	class EO_LocationField_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Location\Model\LocationNameTable:location/lib/model/locationnametable.php:3d75ce1a7c9792df5cbda3d344b2392b */
namespace Bitrix\Location\Model {
	/**
	 * EO_LocationName
	 * @see \Bitrix\Location\Model\LocationNameTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getLocationId()
	 * @method \Bitrix\Location\Model\EO_LocationName setLocationId(\int|\Bitrix\Main\DB\SqlExpression $locationId)
	 * @method bool hasLocationId()
	 * @method bool isLocationIdFilled()
	 * @method bool isLocationIdChanged()
	 * @method \string getLanguageId()
	 * @method \Bitrix\Location\Model\EO_LocationName setLanguageId(\string|\Bitrix\Main\DB\SqlExpression $languageId)
	 * @method bool hasLanguageId()
	 * @method bool isLanguageIdFilled()
	 * @method bool isLanguageIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Location\Model\EO_LocationName setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Location\Model\EO_LocationName resetName()
	 * @method \Bitrix\Location\Model\EO_LocationName unsetName()
	 * @method \string fillName()
	 * @method \string getNameNormalized()
	 * @method \Bitrix\Location\Model\EO_LocationName setNameNormalized(\string|\Bitrix\Main\DB\SqlExpression $nameNormalized)
	 * @method bool hasNameNormalized()
	 * @method bool isNameNormalizedFilled()
	 * @method bool isNameNormalizedChanged()
	 * @method \string remindActualNameNormalized()
	 * @method \string requireNameNormalized()
	 * @method \Bitrix\Location\Model\EO_LocationName resetNameNormalized()
	 * @method \Bitrix\Location\Model\EO_LocationName unsetNameNormalized()
	 * @method \string fillNameNormalized()
	 * @method \Bitrix\Location\Model\EO_Location getLocation()
	 * @method \Bitrix\Location\Model\EO_Location remindActualLocation()
	 * @method \Bitrix\Location\Model\EO_Location requireLocation()
	 * @method \Bitrix\Location\Model\EO_LocationName setLocation(\Bitrix\Location\Model\EO_Location $object)
	 * @method \Bitrix\Location\Model\EO_LocationName resetLocation()
	 * @method \Bitrix\Location\Model\EO_LocationName unsetLocation()
	 * @method bool hasLocation()
	 * @method bool isLocationFilled()
	 * @method bool isLocationChanged()
	 * @method \Bitrix\Location\Model\EO_Location fillLocation()
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
	 * @method \Bitrix\Location\Model\EO_LocationName set($fieldName, $value)
	 * @method \Bitrix\Location\Model\EO_LocationName reset($fieldName)
	 * @method \Bitrix\Location\Model\EO_LocationName unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Location\Model\EO_LocationName wakeUp($data)
	 */
	class EO_LocationName {
		/* @var \Bitrix\Location\Model\LocationNameTable */
		static public $dataClass = '\Bitrix\Location\Model\LocationNameTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Location\Model {
	/**
	 * EO_LocationName_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getLocationIdList()
	 * @method \string[] getLanguageIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getNameNormalizedList()
	 * @method \string[] fillNameNormalized()
	 * @method \Bitrix\Location\Model\EO_Location[] getLocationList()
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection getLocationCollection()
	 * @method \Bitrix\Location\Model\EO_Location_Collection fillLocation()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Location\Model\EO_LocationName $object)
	 * @method bool has(\Bitrix\Location\Model\EO_LocationName $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_LocationName getByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_LocationName[] getAll()
	 * @method bool remove(\Bitrix\Location\Model\EO_LocationName $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Location\Model\EO_LocationName_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Location\Model\EO_LocationName current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_LocationName_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Location\Model\LocationNameTable */
		static public $dataClass = '\Bitrix\Location\Model\LocationNameTable';
	}
}
namespace Bitrix\Location\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_LocationName_Result exec()
	 * @method \Bitrix\Location\Model\EO_LocationName fetchObject()
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_LocationName_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Location\Model\EO_LocationName fetchObject()
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection fetchCollection()
	 */
	class EO_LocationName_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Location\Model\EO_LocationName createObject($setDefaultValues = true)
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection createCollection()
	 * @method \Bitrix\Location\Model\EO_LocationName wakeUpObject($row)
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection wakeUpCollection($rows)
	 */
	class EO_LocationName_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Location\Model\LocationTable:location/lib/model/locationtable.php:3e7053f30bede7e2094c02a67690ed69 */
namespace Bitrix\Location\Model {
	/**
	 * EO_Location
	 * @see \Bitrix\Location\Model\LocationTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Location\Model\EO_Location setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getCode()
	 * @method \Bitrix\Location\Model\EO_Location setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Location\Model\EO_Location resetCode()
	 * @method \Bitrix\Location\Model\EO_Location unsetCode()
	 * @method \string fillCode()
	 * @method \string getExternalId()
	 * @method \Bitrix\Location\Model\EO_Location setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Location\Model\EO_Location resetExternalId()
	 * @method \Bitrix\Location\Model\EO_Location unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \string getSourceCode()
	 * @method \Bitrix\Location\Model\EO_Location setSourceCode(\string|\Bitrix\Main\DB\SqlExpression $sourceCode)
	 * @method bool hasSourceCode()
	 * @method bool isSourceCodeFilled()
	 * @method bool isSourceCodeChanged()
	 * @method \string remindActualSourceCode()
	 * @method \string requireSourceCode()
	 * @method \Bitrix\Location\Model\EO_Location resetSourceCode()
	 * @method \Bitrix\Location\Model\EO_Location unsetSourceCode()
	 * @method \string fillSourceCode()
	 * @method \float getLatitude()
	 * @method \Bitrix\Location\Model\EO_Location setLatitude(\float|\Bitrix\Main\DB\SqlExpression $latitude)
	 * @method bool hasLatitude()
	 * @method bool isLatitudeFilled()
	 * @method bool isLatitudeChanged()
	 * @method \float remindActualLatitude()
	 * @method \float requireLatitude()
	 * @method \Bitrix\Location\Model\EO_Location resetLatitude()
	 * @method \Bitrix\Location\Model\EO_Location unsetLatitude()
	 * @method \float fillLatitude()
	 * @method \float getLongitude()
	 * @method \Bitrix\Location\Model\EO_Location setLongitude(\float|\Bitrix\Main\DB\SqlExpression $longitude)
	 * @method bool hasLongitude()
	 * @method bool isLongitudeFilled()
	 * @method bool isLongitudeChanged()
	 * @method \float remindActualLongitude()
	 * @method \float requireLongitude()
	 * @method \Bitrix\Location\Model\EO_Location resetLongitude()
	 * @method \Bitrix\Location\Model\EO_Location unsetLongitude()
	 * @method \float fillLongitude()
	 * @method \Bitrix\Main\Type\Date getTimestampX()
	 * @method \Bitrix\Location\Model\EO_Location setTimestampX(\Bitrix\Main\Type\Date|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\Date remindActualTimestampX()
	 * @method \Bitrix\Main\Type\Date requireTimestampX()
	 * @method \Bitrix\Location\Model\EO_Location resetTimestampX()
	 * @method \Bitrix\Location\Model\EO_Location unsetTimestampX()
	 * @method \Bitrix\Main\Type\Date fillTimestampX()
	 * @method \int getType()
	 * @method \Bitrix\Location\Model\EO_Location setType(\int|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \int remindActualType()
	 * @method \int requireType()
	 * @method \Bitrix\Location\Model\EO_Location resetType()
	 * @method \Bitrix\Location\Model\EO_Location unsetType()
	 * @method \int fillType()
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection getName()
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection requireName()
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection fillName()
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method void addToName(\Bitrix\Location\Model\EO_LocationName $locationName)
	 * @method void removeFromName(\Bitrix\Location\Model\EO_LocationName $locationName)
	 * @method void removeAllName()
	 * @method \Bitrix\Location\Model\EO_Location resetName()
	 * @method \Bitrix\Location\Model\EO_Location unsetName()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection getAncestors()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection requireAncestors()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection fillAncestors()
	 * @method bool hasAncestors()
	 * @method bool isAncestorsFilled()
	 * @method bool isAncestorsChanged()
	 * @method void addToAncestors(\Bitrix\Location\Model\EO_Hierarchy $hierarchy)
	 * @method void removeFromAncestors(\Bitrix\Location\Model\EO_Hierarchy $hierarchy)
	 * @method void removeAllAncestors()
	 * @method \Bitrix\Location\Model\EO_Location resetAncestors()
	 * @method \Bitrix\Location\Model\EO_Location unsetAncestors()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection getDescendants()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection requireDescendants()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection fillDescendants()
	 * @method bool hasDescendants()
	 * @method bool isDescendantsFilled()
	 * @method bool isDescendantsChanged()
	 * @method void addToDescendants(\Bitrix\Location\Model\EO_Hierarchy $hierarchy)
	 * @method void removeFromDescendants(\Bitrix\Location\Model\EO_Hierarchy $hierarchy)
	 * @method void removeAllDescendants()
	 * @method \Bitrix\Location\Model\EO_Location resetDescendants()
	 * @method \Bitrix\Location\Model\EO_Location unsetDescendants()
	 * @method \Bitrix\Location\Model\EO_Address_Collection getAddresses()
	 * @method \Bitrix\Location\Model\EO_Address_Collection requireAddresses()
	 * @method \Bitrix\Location\Model\EO_Address_Collection fillAddresses()
	 * @method bool hasAddresses()
	 * @method bool isAddressesFilled()
	 * @method bool isAddressesChanged()
	 * @method void addToAddresses(\Bitrix\Location\Model\EO_Address $address)
	 * @method void removeFromAddresses(\Bitrix\Location\Model\EO_Address $address)
	 * @method void removeAllAddresses()
	 * @method \Bitrix\Location\Model\EO_Location resetAddresses()
	 * @method \Bitrix\Location\Model\EO_Location unsetAddresses()
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection getFields()
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection requireFields()
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection fillFields()
	 * @method bool hasFields()
	 * @method bool isFieldsFilled()
	 * @method bool isFieldsChanged()
	 * @method void addToFields(\Bitrix\Location\Model\EO_LocationField $locationField)
	 * @method void removeFromFields(\Bitrix\Location\Model\EO_LocationField $locationField)
	 * @method void removeAllFields()
	 * @method \Bitrix\Location\Model\EO_Location resetFields()
	 * @method \Bitrix\Location\Model\EO_Location unsetFields()
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
	 * @method \Bitrix\Location\Model\EO_Location set($fieldName, $value)
	 * @method \Bitrix\Location\Model\EO_Location reset($fieldName)
	 * @method \Bitrix\Location\Model\EO_Location unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Location\Model\EO_Location wakeUp($data)
	 */
	class EO_Location {
		/* @var \Bitrix\Location\Model\LocationTable */
		static public $dataClass = '\Bitrix\Location\Model\LocationTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Location\Model {
	/**
	 * EO_Location_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \string[] getSourceCodeList()
	 * @method \string[] fillSourceCode()
	 * @method \float[] getLatitudeList()
	 * @method \float[] fillLatitude()
	 * @method \float[] getLongitudeList()
	 * @method \float[] fillLongitude()
	 * @method \Bitrix\Main\Type\Date[] getTimestampXList()
	 * @method \Bitrix\Main\Type\Date[] fillTimestampX()
	 * @method \int[] getTypeList()
	 * @method \int[] fillType()
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection[] getNameList()
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection getNameCollection()
	 * @method \Bitrix\Location\Model\EO_LocationName_Collection fillName()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection[] getAncestorsList()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection getAncestorsCollection()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection fillAncestors()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection[] getDescendantsList()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection getDescendantsCollection()
	 * @method \Bitrix\Location\Model\EO_Hierarchy_Collection fillDescendants()
	 * @method \Bitrix\Location\Model\EO_Address_Collection[] getAddressesList()
	 * @method \Bitrix\Location\Model\EO_Address_Collection getAddressesCollection()
	 * @method \Bitrix\Location\Model\EO_Address_Collection fillAddresses()
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection[] getFieldsList()
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection getFieldsCollection()
	 * @method \Bitrix\Location\Model\EO_LocationField_Collection fillFields()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Location\Model\EO_Location $object)
	 * @method bool has(\Bitrix\Location\Model\EO_Location $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_Location getByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_Location[] getAll()
	 * @method bool remove(\Bitrix\Location\Model\EO_Location $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Location\Model\EO_Location_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Location\Model\EO_Location current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Location_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Location\Model\LocationTable */
		static public $dataClass = '\Bitrix\Location\Model\LocationTable';
	}
}
namespace Bitrix\Location\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Location_Result exec()
	 * @method \Bitrix\Location\Model\EO_Location fetchObject()
	 * @method \Bitrix\Location\Model\EO_Location_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Location_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Location\Model\EO_Location fetchObject()
	 * @method \Bitrix\Location\Model\EO_Location_Collection fetchCollection()
	 */
	class EO_Location_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Location\Model\EO_Location createObject($setDefaultValues = true)
	 * @method \Bitrix\Location\Model\EO_Location_Collection createCollection()
	 * @method \Bitrix\Location\Model\EO_Location wakeUpObject($row)
	 * @method \Bitrix\Location\Model\EO_Location_Collection wakeUpCollection($rows)
	 */
	class EO_Location_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Location\Model\SourceTable:location/lib/model/sourcetable.php:f403856cae290adc7a05962b4257aed2 */
namespace Bitrix\Location\Model {
	/**
	 * EO_Source
	 * @see \Bitrix\Location\Model\SourceTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getCode()
	 * @method \Bitrix\Location\Model\EO_Source setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string getName()
	 * @method \Bitrix\Location\Model\EO_Source setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Location\Model\EO_Source resetName()
	 * @method \Bitrix\Location\Model\EO_Source unsetName()
	 * @method \string fillName()
	 * @method \string getConfig()
	 * @method \Bitrix\Location\Model\EO_Source setConfig(\string|\Bitrix\Main\DB\SqlExpression $config)
	 * @method bool hasConfig()
	 * @method bool isConfigFilled()
	 * @method bool isConfigChanged()
	 * @method \string remindActualConfig()
	 * @method \string requireConfig()
	 * @method \Bitrix\Location\Model\EO_Source resetConfig()
	 * @method \Bitrix\Location\Model\EO_Source unsetConfig()
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
	 * @method \Bitrix\Location\Model\EO_Source set($fieldName, $value)
	 * @method \Bitrix\Location\Model\EO_Source reset($fieldName)
	 * @method \Bitrix\Location\Model\EO_Source unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Location\Model\EO_Source wakeUp($data)
	 */
	class EO_Source {
		/* @var \Bitrix\Location\Model\SourceTable */
		static public $dataClass = '\Bitrix\Location\Model\SourceTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Location\Model {
	/**
	 * EO_Source_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getCodeList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getConfigList()
	 * @method \string[] fillConfig()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Location\Model\EO_Source $object)
	 * @method bool has(\Bitrix\Location\Model\EO_Source $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_Source getByPrimary($primary)
	 * @method \Bitrix\Location\Model\EO_Source[] getAll()
	 * @method bool remove(\Bitrix\Location\Model\EO_Source $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Location\Model\EO_Source_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Location\Model\EO_Source current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Source_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Location\Model\SourceTable */
		static public $dataClass = '\Bitrix\Location\Model\SourceTable';
	}
}
namespace Bitrix\Location\Model {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Source_Result exec()
	 * @method \Bitrix\Location\Model\EO_Source fetchObject()
	 * @method \Bitrix\Location\Model\EO_Source_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Source_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Location\Model\EO_Source fetchObject()
	 * @method \Bitrix\Location\Model\EO_Source_Collection fetchCollection()
	 */
	class EO_Source_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Location\Model\EO_Source createObject($setDefaultValues = true)
	 * @method \Bitrix\Location\Model\EO_Source_Collection createCollection()
	 * @method \Bitrix\Location\Model\EO_Source wakeUpObject($row)
	 * @method \Bitrix\Location\Model\EO_Source_Collection wakeUpCollection($rows)
	 */
	class EO_Source_Entity extends \Bitrix\Main\ORM\Entity {}
}