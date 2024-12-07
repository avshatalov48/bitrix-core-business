<?php

/* ORMENTITYANNOTATION:Bitrix\Idea\NotifyEmailTable:idea/lib/notifyemail.php:c106b5cb1f6a6ae5841f9cd42e97bb35 */
namespace Bitrix\Idea {
	/**
	 * EO_NotifyEmail
	 * @see \Bitrix\Idea\NotifyEmailTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Idea\EO_NotifyEmail setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getSubscribeType()
	 * @method \Bitrix\Idea\EO_NotifyEmail setSubscribeType(\string|\Bitrix\Main\DB\SqlExpression $subscribeType)
	 * @method bool hasSubscribeType()
	 * @method bool isSubscribeTypeFilled()
	 * @method bool isSubscribeTypeChanged()
	 * @method \string remindActualSubscribeType()
	 * @method \string requireSubscribeType()
	 * @method \Bitrix\Idea\EO_NotifyEmail resetSubscribeType()
	 * @method \Bitrix\Idea\EO_NotifyEmail unsetSubscribeType()
	 * @method \string fillSubscribeType()
	 * @method \string getEntityType()
	 * @method \Bitrix\Idea\EO_NotifyEmail setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string getEntityCode()
	 * @method \Bitrix\Idea\EO_NotifyEmail setEntityCode(\string|\Bitrix\Main\DB\SqlExpression $entityCode)
	 * @method bool hasEntityCode()
	 * @method bool isEntityCodeFilled()
	 * @method bool isEntityCodeChanged()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Idea\EO_NotifyEmail setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Idea\EO_NotifyEmail resetUser()
	 * @method \Bitrix\Idea\EO_NotifyEmail unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Iblock\EO_Section getAscendedCategories()
	 * @method \Bitrix\Iblock\EO_Section remindActualAscendedCategories()
	 * @method \Bitrix\Iblock\EO_Section requireAscendedCategories()
	 * @method \Bitrix\Idea\EO_NotifyEmail setAscendedCategories(\Bitrix\Iblock\EO_Section $object)
	 * @method \Bitrix\Idea\EO_NotifyEmail resetAscendedCategories()
	 * @method \Bitrix\Idea\EO_NotifyEmail unsetAscendedCategories()
	 * @method bool hasAscendedCategories()
	 * @method bool isAscendedCategoriesFilled()
	 * @method bool isAscendedCategoriesChanged()
	 * @method \Bitrix\Iblock\EO_Section fillAscendedCategories()
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
	 * @method \Bitrix\Idea\EO_NotifyEmail set($fieldName, $value)
	 * @method \Bitrix\Idea\EO_NotifyEmail reset($fieldName)
	 * @method \Bitrix\Idea\EO_NotifyEmail unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Idea\EO_NotifyEmail wakeUp($data)
	 */
	class EO_NotifyEmail {
		/* @var \Bitrix\Idea\NotifyEmailTable */
		static public $dataClass = '\Bitrix\Idea\NotifyEmailTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Idea {
	/**
	 * EO_NotifyEmail_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getSubscribeTypeList()
	 * @method \string[] fillSubscribeType()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] getEntityCodeList()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Idea\EO_NotifyEmail_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Iblock\EO_Section[] getAscendedCategoriesList()
	 * @method \Bitrix\Idea\EO_NotifyEmail_Collection getAscendedCategoriesCollection()
	 * @method \Bitrix\Iblock\EO_Section_Collection fillAscendedCategories()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Idea\EO_NotifyEmail $object)
	 * @method bool has(\Bitrix\Idea\EO_NotifyEmail $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Idea\EO_NotifyEmail getByPrimary($primary)
	 * @method \Bitrix\Idea\EO_NotifyEmail[] getAll()
	 * @method bool remove(\Bitrix\Idea\EO_NotifyEmail $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Idea\EO_NotifyEmail_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Idea\EO_NotifyEmail current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_NotifyEmail_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Idea\NotifyEmailTable */
		static public $dataClass = '\Bitrix\Idea\NotifyEmailTable';
	}
}
namespace Bitrix\Idea {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_NotifyEmail_Result exec()
	 * @method \Bitrix\Idea\EO_NotifyEmail fetchObject()
	 * @method \Bitrix\Idea\EO_NotifyEmail_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_NotifyEmail_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Idea\EO_NotifyEmail fetchObject()
	 * @method \Bitrix\Idea\EO_NotifyEmail_Collection fetchCollection()
	 */
	class EO_NotifyEmail_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Idea\EO_NotifyEmail createObject($setDefaultValues = true)
	 * @method \Bitrix\Idea\EO_NotifyEmail_Collection createCollection()
	 * @method \Bitrix\Idea\EO_NotifyEmail wakeUpObject($row)
	 * @method \Bitrix\Idea\EO_NotifyEmail_Collection wakeUpCollection($rows)
	 */
	class EO_NotifyEmail_Entity extends \Bitrix\Main\ORM\Entity {}
}