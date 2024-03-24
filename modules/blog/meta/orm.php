<?php

/* ORMENTITYANNOTATION:Bitrix\Blog\Internals\BlogUserTable:blog/lib/internals/bloguser.php */
namespace Bitrix\Blog\Internals {
	/**
	 * EO_BlogUser
	 * @see \Bitrix\Blog\Internals\BlogUserTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser resetUserId()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getAlias()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setAlias(\string|\Bitrix\Main\DB\SqlExpression $alias)
	 * @method bool hasAlias()
	 * @method bool isAliasFilled()
	 * @method bool isAliasChanged()
	 * @method \string remindActualAlias()
	 * @method \string requireAlias()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser resetAlias()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unsetAlias()
	 * @method \string fillAlias()
	 * @method \string getDescription()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser resetDescription()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unsetDescription()
	 * @method \string fillDescription()
	 * @method \int getAvatar()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setAvatar(\int|\Bitrix\Main\DB\SqlExpression $avatar)
	 * @method bool hasAvatar()
	 * @method bool isAvatarFilled()
	 * @method bool isAvatarChanged()
	 * @method \int remindActualAvatar()
	 * @method \int requireAvatar()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser resetAvatar()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unsetAvatar()
	 * @method \int fillAvatar()
	 * @method \string getInterests()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setInterests(\string|\Bitrix\Main\DB\SqlExpression $interests)
	 * @method bool hasInterests()
	 * @method bool isInterestsFilled()
	 * @method bool isInterestsChanged()
	 * @method \string remindActualInterests()
	 * @method \string requireInterests()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser resetInterests()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unsetInterests()
	 * @method \string fillInterests()
	 * @method \Bitrix\Main\Type\DateTime getLastVisit()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setLastVisit(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastVisit)
	 * @method bool hasLastVisit()
	 * @method bool isLastVisitFilled()
	 * @method bool isLastVisitChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastVisit()
	 * @method \Bitrix\Main\Type\DateTime requireLastVisit()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser resetLastVisit()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unsetLastVisit()
	 * @method \Bitrix\Main\Type\DateTime fillLastVisit()
	 * @method \Bitrix\Main\Type\DateTime getDateReg()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setDateReg(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateReg)
	 * @method bool hasDateReg()
	 * @method bool isDateRegFilled()
	 * @method bool isDateRegChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateReg()
	 * @method \Bitrix\Main\Type\DateTime requireDateReg()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser resetDateReg()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unsetDateReg()
	 * @method \Bitrix\Main\Type\DateTime fillDateReg()
	 * @method \boolean getAllowPost()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setAllowPost(\boolean|\Bitrix\Main\DB\SqlExpression $allowPost)
	 * @method bool hasAllowPost()
	 * @method bool isAllowPostFilled()
	 * @method bool isAllowPostChanged()
	 * @method \boolean remindActualAllowPost()
	 * @method \boolean requireAllowPost()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser resetAllowPost()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unsetAllowPost()
	 * @method \boolean fillAllowPost()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Blog\Internals\EO_BlogUser resetUser()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unsetUser()
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
	 * @method \Bitrix\Blog\Internals\EO_BlogUser set($fieldName, $value)
	 * @method \Bitrix\Blog\Internals\EO_BlogUser reset($fieldName)
	 * @method \Bitrix\Blog\Internals\EO_BlogUser unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Blog\Internals\EO_BlogUser wakeUp($data)
	 */
	class EO_BlogUser {
		/* @var \Bitrix\Blog\Internals\BlogUserTable */
		static public $dataClass = '\Bitrix\Blog\Internals\BlogUserTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Blog\Internals {
	/**
	 * EO_BlogUser_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getAliasList()
	 * @method \string[] fillAlias()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \int[] getAvatarList()
	 * @method \int[] fillAvatar()
	 * @method \string[] getInterestsList()
	 * @method \string[] fillInterests()
	 * @method \Bitrix\Main\Type\DateTime[] getLastVisitList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastVisit()
	 * @method \Bitrix\Main\Type\DateTime[] getDateRegList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateReg()
	 * @method \boolean[] getAllowPostList()
	 * @method \boolean[] fillAllowPost()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Blog\Internals\EO_BlogUser $object)
	 * @method bool has(\Bitrix\Blog\Internals\EO_BlogUser $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Blog\Internals\EO_BlogUser getByPrimary($primary)
	 * @method \Bitrix\Blog\Internals\EO_BlogUser[] getAll()
	 * @method bool remove(\Bitrix\Blog\Internals\EO_BlogUser $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Blog\Internals\EO_BlogUser_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Blog\Internals\EO_BlogUser current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_BlogUser_Collection merge(?EO_BlogUser_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_BlogUser_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Blog\Internals\BlogUserTable */
		static public $dataClass = '\Bitrix\Blog\Internals\BlogUserTable';
	}
}
namespace Bitrix\Blog\Internals {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_BlogUser_Result exec()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser fetchObject()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_BlogUser_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Blog\Internals\EO_BlogUser fetchObject()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser_Collection fetchCollection()
	 */
	class EO_BlogUser_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Blog\Internals\EO_BlogUser createObject($setDefaultValues = true)
	 * @method \Bitrix\Blog\Internals\EO_BlogUser_Collection createCollection()
	 * @method \Bitrix\Blog\Internals\EO_BlogUser wakeUpObject($row)
	 * @method \Bitrix\Blog\Internals\EO_BlogUser_Collection wakeUpCollection($rows)
	 */
	class EO_BlogUser_Entity extends \Bitrix\Main\ORM\Entity {}
}