<?php

/* ORMENTITYANNOTATION:Bitrix\Blog\CommentTable:blog/lib/comment.php:6c0a80fecd8ea45a2c3016e72cab8ad8 */
namespace Bitrix\Blog {
	/**
	 * EO_Comment
	 * @see \Bitrix\Blog\CommentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Blog\EO_Comment setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getBlogId()
	 * @method \Bitrix\Blog\EO_Comment setBlogId(\int|\Bitrix\Main\DB\SqlExpression $blogId)
	 * @method bool hasBlogId()
	 * @method bool isBlogIdFilled()
	 * @method bool isBlogIdChanged()
	 * @method \int remindActualBlogId()
	 * @method \int requireBlogId()
	 * @method \Bitrix\Blog\EO_Comment resetBlogId()
	 * @method \Bitrix\Blog\EO_Comment unsetBlogId()
	 * @method \int fillBlogId()
	 * @method \int getPostId()
	 * @method \Bitrix\Blog\EO_Comment setPostId(\int|\Bitrix\Main\DB\SqlExpression $postId)
	 * @method bool hasPostId()
	 * @method bool isPostIdFilled()
	 * @method bool isPostIdChanged()
	 * @method \int remindActualPostId()
	 * @method \int requirePostId()
	 * @method \Bitrix\Blog\EO_Comment resetPostId()
	 * @method \Bitrix\Blog\EO_Comment unsetPostId()
	 * @method \int fillPostId()
	 * @method \Bitrix\Blog\EO_Post getPost()
	 * @method \Bitrix\Blog\EO_Post remindActualPost()
	 * @method \Bitrix\Blog\EO_Post requirePost()
	 * @method \Bitrix\Blog\EO_Comment setPost(\Bitrix\Blog\EO_Post $object)
	 * @method \Bitrix\Blog\EO_Comment resetPost()
	 * @method \Bitrix\Blog\EO_Comment unsetPost()
	 * @method bool hasPost()
	 * @method bool isPostFilled()
	 * @method bool isPostChanged()
	 * @method \Bitrix\Blog\EO_Post fillPost()
	 * @method \int getParentId()
	 * @method \Bitrix\Blog\EO_Comment setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Blog\EO_Comment resetParentId()
	 * @method \Bitrix\Blog\EO_Comment unsetParentId()
	 * @method \int fillParentId()
	 * @method \int getAuthorId()
	 * @method \Bitrix\Blog\EO_Comment setAuthorId(\int|\Bitrix\Main\DB\SqlExpression $authorId)
	 * @method bool hasAuthorId()
	 * @method bool isAuthorIdFilled()
	 * @method bool isAuthorIdChanged()
	 * @method \int remindActualAuthorId()
	 * @method \int requireAuthorId()
	 * @method \Bitrix\Blog\EO_Comment resetAuthorId()
	 * @method \Bitrix\Blog\EO_Comment unsetAuthorId()
	 * @method \int fillAuthorId()
	 * @method \int getIconId()
	 * @method \Bitrix\Blog\EO_Comment setIconId(\int|\Bitrix\Main\DB\SqlExpression $iconId)
	 * @method bool hasIconId()
	 * @method bool isIconIdFilled()
	 * @method bool isIconIdChanged()
	 * @method \int remindActualIconId()
	 * @method \int requireIconId()
	 * @method \Bitrix\Blog\EO_Comment resetIconId()
	 * @method \Bitrix\Blog\EO_Comment unsetIconId()
	 * @method \int fillIconId()
	 * @method \string getAuthorName()
	 * @method \Bitrix\Blog\EO_Comment setAuthorName(\string|\Bitrix\Main\DB\SqlExpression $authorName)
	 * @method bool hasAuthorName()
	 * @method bool isAuthorNameFilled()
	 * @method bool isAuthorNameChanged()
	 * @method \string remindActualAuthorName()
	 * @method \string requireAuthorName()
	 * @method \Bitrix\Blog\EO_Comment resetAuthorName()
	 * @method \Bitrix\Blog\EO_Comment unsetAuthorName()
	 * @method \string fillAuthorName()
	 * @method \string getAuthorEmail()
	 * @method \Bitrix\Blog\EO_Comment setAuthorEmail(\string|\Bitrix\Main\DB\SqlExpression $authorEmail)
	 * @method bool hasAuthorEmail()
	 * @method bool isAuthorEmailFilled()
	 * @method bool isAuthorEmailChanged()
	 * @method \string remindActualAuthorEmail()
	 * @method \string requireAuthorEmail()
	 * @method \Bitrix\Blog\EO_Comment resetAuthorEmail()
	 * @method \Bitrix\Blog\EO_Comment unsetAuthorEmail()
	 * @method \string fillAuthorEmail()
	 * @method \string getAuthorIp()
	 * @method \Bitrix\Blog\EO_Comment setAuthorIp(\string|\Bitrix\Main\DB\SqlExpression $authorIp)
	 * @method bool hasAuthorIp()
	 * @method bool isAuthorIpFilled()
	 * @method bool isAuthorIpChanged()
	 * @method \string remindActualAuthorIp()
	 * @method \string requireAuthorIp()
	 * @method \Bitrix\Blog\EO_Comment resetAuthorIp()
	 * @method \Bitrix\Blog\EO_Comment unsetAuthorIp()
	 * @method \string fillAuthorIp()
	 * @method \string getAuthorIp1()
	 * @method \Bitrix\Blog\EO_Comment setAuthorIp1(\string|\Bitrix\Main\DB\SqlExpression $authorIp1)
	 * @method bool hasAuthorIp1()
	 * @method bool isAuthorIp1Filled()
	 * @method bool isAuthorIp1Changed()
	 * @method \string remindActualAuthorIp1()
	 * @method \string requireAuthorIp1()
	 * @method \Bitrix\Blog\EO_Comment resetAuthorIp1()
	 * @method \Bitrix\Blog\EO_Comment unsetAuthorIp1()
	 * @method \string fillAuthorIp1()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Blog\EO_Comment setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Blog\EO_Comment resetDateCreate()
	 * @method \Bitrix\Blog\EO_Comment unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \string getTitle()
	 * @method \Bitrix\Blog\EO_Comment setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Blog\EO_Comment resetTitle()
	 * @method \Bitrix\Blog\EO_Comment unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getPostText()
	 * @method \Bitrix\Blog\EO_Comment setPostText(\string|\Bitrix\Main\DB\SqlExpression $postText)
	 * @method bool hasPostText()
	 * @method bool isPostTextFilled()
	 * @method bool isPostTextChanged()
	 * @method \string remindActualPostText()
	 * @method \string requirePostText()
	 * @method \Bitrix\Blog\EO_Comment resetPostText()
	 * @method \Bitrix\Blog\EO_Comment unsetPostText()
	 * @method \string fillPostText()
	 * @method \string getPublishStatus()
	 * @method \Bitrix\Blog\EO_Comment setPublishStatus(\string|\Bitrix\Main\DB\SqlExpression $publishStatus)
	 * @method bool hasPublishStatus()
	 * @method bool isPublishStatusFilled()
	 * @method bool isPublishStatusChanged()
	 * @method \string remindActualPublishStatus()
	 * @method \string requirePublishStatus()
	 * @method \Bitrix\Blog\EO_Comment resetPublishStatus()
	 * @method \Bitrix\Blog\EO_Comment unsetPublishStatus()
	 * @method \string fillPublishStatus()
	 * @method \string getHasProps()
	 * @method \Bitrix\Blog\EO_Comment setHasProps(\string|\Bitrix\Main\DB\SqlExpression $hasProps)
	 * @method bool hasHasProps()
	 * @method bool isHasPropsFilled()
	 * @method bool isHasPropsChanged()
	 * @method \string remindActualHasProps()
	 * @method \string requireHasProps()
	 * @method \Bitrix\Blog\EO_Comment resetHasProps()
	 * @method \Bitrix\Blog\EO_Comment unsetHasProps()
	 * @method \string fillHasProps()
	 * @method \string getShareDest()
	 * @method \Bitrix\Blog\EO_Comment setShareDest(\string|\Bitrix\Main\DB\SqlExpression $shareDest)
	 * @method bool hasShareDest()
	 * @method bool isShareDestFilled()
	 * @method bool isShareDestChanged()
	 * @method \string remindActualShareDest()
	 * @method \string requireShareDest()
	 * @method \Bitrix\Blog\EO_Comment resetShareDest()
	 * @method \Bitrix\Blog\EO_Comment unsetShareDest()
	 * @method \string fillShareDest()
	 * @method \string getPath()
	 * @method \Bitrix\Blog\EO_Comment setPath(\string|\Bitrix\Main\DB\SqlExpression $path)
	 * @method bool hasPath()
	 * @method bool isPathFilled()
	 * @method bool isPathChanged()
	 * @method \string remindActualPath()
	 * @method \string requirePath()
	 * @method \Bitrix\Blog\EO_Comment resetPath()
	 * @method \Bitrix\Blog\EO_Comment unsetPath()
	 * @method \string fillPath()
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
	 * @method \Bitrix\Blog\EO_Comment set($fieldName, $value)
	 * @method \Bitrix\Blog\EO_Comment reset($fieldName)
	 * @method \Bitrix\Blog\EO_Comment unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Blog\EO_Comment wakeUp($data)
	 */
	class EO_Comment {
		/* @var \Bitrix\Blog\CommentTable */
		static public $dataClass = '\Bitrix\Blog\CommentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Blog {
	/**
	 * EO_Comment_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getBlogIdList()
	 * @method \int[] fillBlogId()
	 * @method \int[] getPostIdList()
	 * @method \int[] fillPostId()
	 * @method \Bitrix\Blog\EO_Post[] getPostList()
	 * @method \Bitrix\Blog\EO_Comment_Collection getPostCollection()
	 * @method \Bitrix\Blog\EO_Post_Collection fillPost()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \int[] getAuthorIdList()
	 * @method \int[] fillAuthorId()
	 * @method \int[] getIconIdList()
	 * @method \int[] fillIconId()
	 * @method \string[] getAuthorNameList()
	 * @method \string[] fillAuthorName()
	 * @method \string[] getAuthorEmailList()
	 * @method \string[] fillAuthorEmail()
	 * @method \string[] getAuthorIpList()
	 * @method \string[] fillAuthorIp()
	 * @method \string[] getAuthorIp1List()
	 * @method \string[] fillAuthorIp1()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getPostTextList()
	 * @method \string[] fillPostText()
	 * @method \string[] getPublishStatusList()
	 * @method \string[] fillPublishStatus()
	 * @method \string[] getHasPropsList()
	 * @method \string[] fillHasProps()
	 * @method \string[] getShareDestList()
	 * @method \string[] fillShareDest()
	 * @method \string[] getPathList()
	 * @method \string[] fillPath()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Blog\EO_Comment $object)
	 * @method bool has(\Bitrix\Blog\EO_Comment $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Blog\EO_Comment getByPrimary($primary)
	 * @method \Bitrix\Blog\EO_Comment[] getAll()
	 * @method bool remove(\Bitrix\Blog\EO_Comment $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Blog\EO_Comment_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Blog\EO_Comment current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Comment_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Blog\CommentTable */
		static public $dataClass = '\Bitrix\Blog\CommentTable';
	}
}
namespace Bitrix\Blog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Comment_Result exec()
	 * @method \Bitrix\Blog\EO_Comment fetchObject()
	 * @method \Bitrix\Blog\EO_Comment_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Comment_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Blog\EO_Comment fetchObject()
	 * @method \Bitrix\Blog\EO_Comment_Collection fetchCollection()
	 */
	class EO_Comment_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Blog\EO_Comment createObject($setDefaultValues = true)
	 * @method \Bitrix\Blog\EO_Comment_Collection createCollection()
	 * @method \Bitrix\Blog\EO_Comment wakeUpObject($row)
	 * @method \Bitrix\Blog\EO_Comment_Collection wakeUpCollection($rows)
	 */
	class EO_Comment_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Blog\Internals\BlogUserTable:blog/lib/internals/bloguser.php:1901718d172dea827ec16e498bbbee89 */
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
/* ORMENTITYANNOTATION:Bitrix\Blog\PostTable:blog/lib/post.php:e1e125c019fea6da703c8ca772550a39 */
namespace Bitrix\Blog {
	/**
	 * EO_Post
	 * @see \Bitrix\Blog\PostTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Blog\EO_Post setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getBlogId()
	 * @method \Bitrix\Blog\EO_Post setBlogId(\int|\Bitrix\Main\DB\SqlExpression $blogId)
	 * @method bool hasBlogId()
	 * @method bool isBlogIdFilled()
	 * @method bool isBlogIdChanged()
	 * @method \int remindActualBlogId()
	 * @method \int requireBlogId()
	 * @method \Bitrix\Blog\EO_Post resetBlogId()
	 * @method \Bitrix\Blog\EO_Post unsetBlogId()
	 * @method \int fillBlogId()
	 * @method \int getAuthorId()
	 * @method \Bitrix\Blog\EO_Post setAuthorId(\int|\Bitrix\Main\DB\SqlExpression $authorId)
	 * @method bool hasAuthorId()
	 * @method bool isAuthorIdFilled()
	 * @method bool isAuthorIdChanged()
	 * @method \int remindActualAuthorId()
	 * @method \int requireAuthorId()
	 * @method \Bitrix\Blog\EO_Post resetAuthorId()
	 * @method \Bitrix\Blog\EO_Post unsetAuthorId()
	 * @method \int fillAuthorId()
	 * @method \string getCode()
	 * @method \Bitrix\Blog\EO_Post setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Blog\EO_Post resetCode()
	 * @method \Bitrix\Blog\EO_Post unsetCode()
	 * @method \string fillCode()
	 * @method \string getMicro()
	 * @method \Bitrix\Blog\EO_Post setMicro(\string|\Bitrix\Main\DB\SqlExpression $micro)
	 * @method bool hasMicro()
	 * @method bool isMicroFilled()
	 * @method bool isMicroChanged()
	 * @method \string remindActualMicro()
	 * @method \string requireMicro()
	 * @method \Bitrix\Blog\EO_Post resetMicro()
	 * @method \Bitrix\Blog\EO_Post unsetMicro()
	 * @method \string fillMicro()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Blog\EO_Post setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Blog\EO_Post resetDateCreate()
	 * @method \Bitrix\Blog\EO_Post unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getDatePublish()
	 * @method \Bitrix\Blog\EO_Post setDatePublish(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $datePublish)
	 * @method bool hasDatePublish()
	 * @method bool isDatePublishFilled()
	 * @method bool isDatePublishChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDatePublish()
	 * @method \Bitrix\Main\Type\DateTime requireDatePublish()
	 * @method \Bitrix\Blog\EO_Post resetDatePublish()
	 * @method \Bitrix\Blog\EO_Post unsetDatePublish()
	 * @method \Bitrix\Main\Type\DateTime fillDatePublish()
	 * @method \string getPublishStatus()
	 * @method \Bitrix\Blog\EO_Post setPublishStatus(\string|\Bitrix\Main\DB\SqlExpression $publishStatus)
	 * @method bool hasPublishStatus()
	 * @method bool isPublishStatusFilled()
	 * @method bool isPublishStatusChanged()
	 * @method \string remindActualPublishStatus()
	 * @method \string requirePublishStatus()
	 * @method \Bitrix\Blog\EO_Post resetPublishStatus()
	 * @method \Bitrix\Blog\EO_Post unsetPublishStatus()
	 * @method \string fillPublishStatus()
	 * @method \string getEnableComments()
	 * @method \Bitrix\Blog\EO_Post setEnableComments(\string|\Bitrix\Main\DB\SqlExpression $enableComments)
	 * @method bool hasEnableComments()
	 * @method bool isEnableCommentsFilled()
	 * @method bool isEnableCommentsChanged()
	 * @method \string remindActualEnableComments()
	 * @method \string requireEnableComments()
	 * @method \Bitrix\Blog\EO_Post resetEnableComments()
	 * @method \Bitrix\Blog\EO_Post unsetEnableComments()
	 * @method \string fillEnableComments()
	 * @method \int getNumComments()
	 * @method \Bitrix\Blog\EO_Post setNumComments(\int|\Bitrix\Main\DB\SqlExpression $numComments)
	 * @method bool hasNumComments()
	 * @method bool isNumCommentsFilled()
	 * @method bool isNumCommentsChanged()
	 * @method \int remindActualNumComments()
	 * @method \int requireNumComments()
	 * @method \Bitrix\Blog\EO_Post resetNumComments()
	 * @method \Bitrix\Blog\EO_Post unsetNumComments()
	 * @method \int fillNumComments()
	 * @method \int getNumCommentsAll()
	 * @method \Bitrix\Blog\EO_Post setNumCommentsAll(\int|\Bitrix\Main\DB\SqlExpression $numCommentsAll)
	 * @method bool hasNumCommentsAll()
	 * @method bool isNumCommentsAllFilled()
	 * @method bool isNumCommentsAllChanged()
	 * @method \int remindActualNumCommentsAll()
	 * @method \int requireNumCommentsAll()
	 * @method \Bitrix\Blog\EO_Post resetNumCommentsAll()
	 * @method \Bitrix\Blog\EO_Post unsetNumCommentsAll()
	 * @method \int fillNumCommentsAll()
	 * @method \int getViews()
	 * @method \Bitrix\Blog\EO_Post setViews(\int|\Bitrix\Main\DB\SqlExpression $views)
	 * @method bool hasViews()
	 * @method bool isViewsFilled()
	 * @method bool isViewsChanged()
	 * @method \int remindActualViews()
	 * @method \int requireViews()
	 * @method \Bitrix\Blog\EO_Post resetViews()
	 * @method \Bitrix\Blog\EO_Post unsetViews()
	 * @method \int fillViews()
	 * @method \string getHasSocnetAll()
	 * @method \Bitrix\Blog\EO_Post setHasSocnetAll(\string|\Bitrix\Main\DB\SqlExpression $hasSocnetAll)
	 * @method bool hasHasSocnetAll()
	 * @method bool isHasSocnetAllFilled()
	 * @method bool isHasSocnetAllChanged()
	 * @method \string remindActualHasSocnetAll()
	 * @method \string requireHasSocnetAll()
	 * @method \Bitrix\Blog\EO_Post resetHasSocnetAll()
	 * @method \Bitrix\Blog\EO_Post unsetHasSocnetAll()
	 * @method \string fillHasSocnetAll()
	 * @method \string getHasTags()
	 * @method \Bitrix\Blog\EO_Post setHasTags(\string|\Bitrix\Main\DB\SqlExpression $hasTags)
	 * @method bool hasHasTags()
	 * @method bool isHasTagsFilled()
	 * @method bool isHasTagsChanged()
	 * @method \string remindActualHasTags()
	 * @method \string requireHasTags()
	 * @method \Bitrix\Blog\EO_Post resetHasTags()
	 * @method \Bitrix\Blog\EO_Post unsetHasTags()
	 * @method \string fillHasTags()
	 * @method \string getHasImages()
	 * @method \Bitrix\Blog\EO_Post setHasImages(\string|\Bitrix\Main\DB\SqlExpression $hasImages)
	 * @method bool hasHasImages()
	 * @method bool isHasImagesFilled()
	 * @method bool isHasImagesChanged()
	 * @method \string remindActualHasImages()
	 * @method \string requireHasImages()
	 * @method \Bitrix\Blog\EO_Post resetHasImages()
	 * @method \Bitrix\Blog\EO_Post unsetHasImages()
	 * @method \string fillHasImages()
	 * @method \string getHasProps()
	 * @method \Bitrix\Blog\EO_Post setHasProps(\string|\Bitrix\Main\DB\SqlExpression $hasProps)
	 * @method bool hasHasProps()
	 * @method bool isHasPropsFilled()
	 * @method bool isHasPropsChanged()
	 * @method \string remindActualHasProps()
	 * @method \string requireHasProps()
	 * @method \Bitrix\Blog\EO_Post resetHasProps()
	 * @method \Bitrix\Blog\EO_Post unsetHasProps()
	 * @method \string fillHasProps()
	 * @method \string getHasCommentImages()
	 * @method \Bitrix\Blog\EO_Post setHasCommentImages(\string|\Bitrix\Main\DB\SqlExpression $hasCommentImages)
	 * @method bool hasHasCommentImages()
	 * @method bool isHasCommentImagesFilled()
	 * @method bool isHasCommentImagesChanged()
	 * @method \string remindActualHasCommentImages()
	 * @method \string requireHasCommentImages()
	 * @method \Bitrix\Blog\EO_Post resetHasCommentImages()
	 * @method \Bitrix\Blog\EO_Post unsetHasCommentImages()
	 * @method \string fillHasCommentImages()
	 * @method \string getTitle()
	 * @method \Bitrix\Blog\EO_Post setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Blog\EO_Post resetTitle()
	 * @method \Bitrix\Blog\EO_Post unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getDetailText()
	 * @method \Bitrix\Blog\EO_Post setDetailText(\string|\Bitrix\Main\DB\SqlExpression $detailText)
	 * @method bool hasDetailText()
	 * @method bool isDetailTextFilled()
	 * @method bool isDetailTextChanged()
	 * @method \string remindActualDetailText()
	 * @method \string requireDetailText()
	 * @method \Bitrix\Blog\EO_Post resetDetailText()
	 * @method \Bitrix\Blog\EO_Post unsetDetailText()
	 * @method \string fillDetailText()
	 * @method \string getCategoryId()
	 * @method \Bitrix\Blog\EO_Post setCategoryId(\string|\Bitrix\Main\DB\SqlExpression $categoryId)
	 * @method bool hasCategoryId()
	 * @method bool isCategoryIdFilled()
	 * @method bool isCategoryIdChanged()
	 * @method \string remindActualCategoryId()
	 * @method \string requireCategoryId()
	 * @method \Bitrix\Blog\EO_Post resetCategoryId()
	 * @method \Bitrix\Blog\EO_Post unsetCategoryId()
	 * @method \string fillCategoryId()
	 * @method \string getBackgroundCode()
	 * @method \Bitrix\Blog\EO_Post setBackgroundCode(\string|\Bitrix\Main\DB\SqlExpression $backgroundCode)
	 * @method bool hasBackgroundCode()
	 * @method bool isBackgroundCodeFilled()
	 * @method bool isBackgroundCodeChanged()
	 * @method \string remindActualBackgroundCode()
	 * @method \string requireBackgroundCode()
	 * @method \Bitrix\Blog\EO_Post resetBackgroundCode()
	 * @method \Bitrix\Blog\EO_Post unsetBackgroundCode()
	 * @method \string fillBackgroundCode()
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
	 * @method \Bitrix\Blog\EO_Post set($fieldName, $value)
	 * @method \Bitrix\Blog\EO_Post reset($fieldName)
	 * @method \Bitrix\Blog\EO_Post unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Blog\EO_Post wakeUp($data)
	 */
	class EO_Post {
		/* @var \Bitrix\Blog\PostTable */
		static public $dataClass = '\Bitrix\Blog\PostTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Blog {
	/**
	 * EO_Post_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getBlogIdList()
	 * @method \int[] fillBlogId()
	 * @method \int[] getAuthorIdList()
	 * @method \int[] fillAuthorId()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getMicroList()
	 * @method \string[] fillMicro()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getDatePublishList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDatePublish()
	 * @method \string[] getPublishStatusList()
	 * @method \string[] fillPublishStatus()
	 * @method \string[] getEnableCommentsList()
	 * @method \string[] fillEnableComments()
	 * @method \int[] getNumCommentsList()
	 * @method \int[] fillNumComments()
	 * @method \int[] getNumCommentsAllList()
	 * @method \int[] fillNumCommentsAll()
	 * @method \int[] getViewsList()
	 * @method \int[] fillViews()
	 * @method \string[] getHasSocnetAllList()
	 * @method \string[] fillHasSocnetAll()
	 * @method \string[] getHasTagsList()
	 * @method \string[] fillHasTags()
	 * @method \string[] getHasImagesList()
	 * @method \string[] fillHasImages()
	 * @method \string[] getHasPropsList()
	 * @method \string[] fillHasProps()
	 * @method \string[] getHasCommentImagesList()
	 * @method \string[] fillHasCommentImages()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getDetailTextList()
	 * @method \string[] fillDetailText()
	 * @method \string[] getCategoryIdList()
	 * @method \string[] fillCategoryId()
	 * @method \string[] getBackgroundCodeList()
	 * @method \string[] fillBackgroundCode()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Blog\EO_Post $object)
	 * @method bool has(\Bitrix\Blog\EO_Post $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Blog\EO_Post getByPrimary($primary)
	 * @method \Bitrix\Blog\EO_Post[] getAll()
	 * @method bool remove(\Bitrix\Blog\EO_Post $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Blog\EO_Post_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Blog\EO_Post current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Post_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Blog\PostTable */
		static public $dataClass = '\Bitrix\Blog\PostTable';
	}
}
namespace Bitrix\Blog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Post_Result exec()
	 * @method \Bitrix\Blog\EO_Post fetchObject()
	 * @method \Bitrix\Blog\EO_Post_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Post_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Blog\EO_Post fetchObject()
	 * @method \Bitrix\Blog\EO_Post_Collection fetchCollection()
	 */
	class EO_Post_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Blog\EO_Post createObject($setDefaultValues = true)
	 * @method \Bitrix\Blog\EO_Post_Collection createCollection()
	 * @method \Bitrix\Blog\EO_Post wakeUpObject($row)
	 * @method \Bitrix\Blog\EO_Post_Collection wakeUpCollection($rows)
	 */
	class EO_Post_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Blog\PostSocnetRightsTable:blog/lib/postsocnetrights.php:6c662a56700ea0e9a11485b70a1968f4 */
namespace Bitrix\Blog {
	/**
	 * EO_PostSocnetRights
	 * @see \Bitrix\Blog\PostSocnetRightsTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Blog\EO_PostSocnetRights setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getPostId()
	 * @method \Bitrix\Blog\EO_PostSocnetRights setPostId(\int|\Bitrix\Main\DB\SqlExpression $postId)
	 * @method bool hasPostId()
	 * @method bool isPostIdFilled()
	 * @method bool isPostIdChanged()
	 * @method \int remindActualPostId()
	 * @method \int requirePostId()
	 * @method \Bitrix\Blog\EO_PostSocnetRights resetPostId()
	 * @method \Bitrix\Blog\EO_PostSocnetRights unsetPostId()
	 * @method \int fillPostId()
	 * @method \Bitrix\Blog\EO_Post getPost()
	 * @method \Bitrix\Blog\EO_Post remindActualPost()
	 * @method \Bitrix\Blog\EO_Post requirePost()
	 * @method \Bitrix\Blog\EO_PostSocnetRights setPost(\Bitrix\Blog\EO_Post $object)
	 * @method \Bitrix\Blog\EO_PostSocnetRights resetPost()
	 * @method \Bitrix\Blog\EO_PostSocnetRights unsetPost()
	 * @method bool hasPost()
	 * @method bool isPostFilled()
	 * @method bool isPostChanged()
	 * @method \Bitrix\Blog\EO_Post fillPost()
	 * @method \string getEntityType()
	 * @method \Bitrix\Blog\EO_PostSocnetRights setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \string remindActualEntityType()
	 * @method \string requireEntityType()
	 * @method \Bitrix\Blog\EO_PostSocnetRights resetEntityType()
	 * @method \Bitrix\Blog\EO_PostSocnetRights unsetEntityType()
	 * @method \string fillEntityType()
	 * @method \int getEntityId()
	 * @method \Bitrix\Blog\EO_PostSocnetRights setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \int remindActualEntityId()
	 * @method \int requireEntityId()
	 * @method \Bitrix\Blog\EO_PostSocnetRights resetEntityId()
	 * @method \Bitrix\Blog\EO_PostSocnetRights unsetEntityId()
	 * @method \int fillEntityId()
	 * @method \string getEntity()
	 * @method \Bitrix\Blog\EO_PostSocnetRights setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Blog\EO_PostSocnetRights resetEntity()
	 * @method \Bitrix\Blog\EO_PostSocnetRights unsetEntity()
	 * @method \string fillEntity()
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
	 * @method \Bitrix\Blog\EO_PostSocnetRights set($fieldName, $value)
	 * @method \Bitrix\Blog\EO_PostSocnetRights reset($fieldName)
	 * @method \Bitrix\Blog\EO_PostSocnetRights unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Blog\EO_PostSocnetRights wakeUp($data)
	 */
	class EO_PostSocnetRights {
		/* @var \Bitrix\Blog\PostSocnetRightsTable */
		static public $dataClass = '\Bitrix\Blog\PostSocnetRightsTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Blog {
	/**
	 * EO_PostSocnetRights_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getPostIdList()
	 * @method \int[] fillPostId()
	 * @method \Bitrix\Blog\EO_Post[] getPostList()
	 * @method \Bitrix\Blog\EO_PostSocnetRights_Collection getPostCollection()
	 * @method \Bitrix\Blog\EO_Post_Collection fillPost()
	 * @method \string[] getEntityTypeList()
	 * @method \string[] fillEntityType()
	 * @method \int[] getEntityIdList()
	 * @method \int[] fillEntityId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Blog\EO_PostSocnetRights $object)
	 * @method bool has(\Bitrix\Blog\EO_PostSocnetRights $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Blog\EO_PostSocnetRights getByPrimary($primary)
	 * @method \Bitrix\Blog\EO_PostSocnetRights[] getAll()
	 * @method bool remove(\Bitrix\Blog\EO_PostSocnetRights $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Blog\EO_PostSocnetRights_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Blog\EO_PostSocnetRights current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_PostSocnetRights_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Blog\PostSocnetRightsTable */
		static public $dataClass = '\Bitrix\Blog\PostSocnetRightsTable';
	}
}
namespace Bitrix\Blog {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_PostSocnetRights_Result exec()
	 * @method \Bitrix\Blog\EO_PostSocnetRights fetchObject()
	 * @method \Bitrix\Blog\EO_PostSocnetRights_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_PostSocnetRights_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Blog\EO_PostSocnetRights fetchObject()
	 * @method \Bitrix\Blog\EO_PostSocnetRights_Collection fetchCollection()
	 */
	class EO_PostSocnetRights_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Blog\EO_PostSocnetRights createObject($setDefaultValues = true)
	 * @method \Bitrix\Blog\EO_PostSocnetRights_Collection createCollection()
	 * @method \Bitrix\Blog\EO_PostSocnetRights wakeUpObject($row)
	 * @method \Bitrix\Blog\EO_PostSocnetRights_Collection wakeUpCollection($rows)
	 */
	class EO_PostSocnetRights_Entity extends \Bitrix\Main\ORM\Entity {}
}