<?php

/* ORMENTITYANNOTATION:Bitrix\Forum\UserTopicTable:forum/lib/usertopic.php */
namespace Bitrix\Forum {
	/**
	 * EO_UserTopic
	 * @see \Bitrix\Forum\UserTopicTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_UserTopic setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int remindActualId()
	 * @method \int requireId()
	 * @method \Bitrix\Forum\EO_UserTopic resetId()
	 * @method \Bitrix\Forum\EO_UserTopic unsetId()
	 * @method \int fillId()
	 * @method \int getTopicId()
	 * @method \Bitrix\Forum\EO_UserTopic setTopicId(\int|\Bitrix\Main\DB\SqlExpression $topicId)
	 * @method bool hasTopicId()
	 * @method bool isTopicIdFilled()
	 * @method bool isTopicIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Forum\EO_UserTopic setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int getForumId()
	 * @method \Bitrix\Forum\EO_UserTopic setForumId(\int|\Bitrix\Main\DB\SqlExpression $forumId)
	 * @method bool hasForumId()
	 * @method bool isForumIdFilled()
	 * @method bool isForumIdChanged()
	 * @method \int remindActualForumId()
	 * @method \int requireForumId()
	 * @method \Bitrix\Forum\EO_UserTopic resetForumId()
	 * @method \Bitrix\Forum\EO_UserTopic unsetForumId()
	 * @method \int fillForumId()
	 * @method \Bitrix\Main\Type\DateTime getLastVisit()
	 * @method \Bitrix\Forum\EO_UserTopic setLastVisit(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastVisit)
	 * @method bool hasLastVisit()
	 * @method bool isLastVisitFilled()
	 * @method bool isLastVisitChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastVisit()
	 * @method \Bitrix\Main\Type\DateTime requireLastVisit()
	 * @method \Bitrix\Forum\EO_UserTopic resetLastVisit()
	 * @method \Bitrix\Forum\EO_UserTopic unsetLastVisit()
	 * @method \Bitrix\Main\Type\DateTime fillLastVisit()
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
	 * @method \Bitrix\Forum\EO_UserTopic set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_UserTopic reset($fieldName)
	 * @method \Bitrix\Forum\EO_UserTopic unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_UserTopic wakeUp($data)
	 */
	class EO_UserTopic {
		/* @var \Bitrix\Forum\UserTopicTable */
		static public $dataClass = '\Bitrix\Forum\UserTopicTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_UserTopic_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] fillId()
	 * @method \int[] getTopicIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] getForumIdList()
	 * @method \int[] fillForumId()
	 * @method \Bitrix\Main\Type\DateTime[] getLastVisitList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastVisit()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_UserTopic $object)
	 * @method bool has(\Bitrix\Forum\EO_UserTopic $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_UserTopic getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_UserTopic[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_UserTopic $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_UserTopic_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_UserTopic current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_UserTopic_Collection merge(?EO_UserTopic_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_UserTopic_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\UserTopicTable */
		static public $dataClass = '\Bitrix\Forum\UserTopicTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserTopic_Result exec()
	 * @method \Bitrix\Forum\EO_UserTopic fetchObject()
	 * @method \Bitrix\Forum\EO_UserTopic_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserTopic_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_UserTopic fetchObject()
	 * @method \Bitrix\Forum\EO_UserTopic_Collection fetchCollection()
	 */
	class EO_UserTopic_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_UserTopic createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_UserTopic_Collection createCollection()
	 * @method \Bitrix\Forum\EO_UserTopic wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_UserTopic_Collection wakeUpCollection($rows)
	 */
	class EO_UserTopic_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\ForumTable:forum/lib/forum.php */
namespace Bitrix\Forum {
	/**
	 * EO_Forum
	 * @see \Bitrix\Forum\ForumTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_Forum setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getForumGroupId()
	 * @method \Bitrix\Forum\EO_Forum setForumGroupId(\int|\Bitrix\Main\DB\SqlExpression $forumGroupId)
	 * @method bool hasForumGroupId()
	 * @method bool isForumGroupIdFilled()
	 * @method bool isForumGroupIdChanged()
	 * @method \int remindActualForumGroupId()
	 * @method \int requireForumGroupId()
	 * @method \Bitrix\Forum\EO_Forum resetForumGroupId()
	 * @method \Bitrix\Forum\EO_Forum unsetForumGroupId()
	 * @method \int fillForumGroupId()
	 * @method \string getName()
	 * @method \Bitrix\Forum\EO_Forum setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Forum\EO_Forum resetName()
	 * @method \Bitrix\Forum\EO_Forum unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Forum\EO_Forum setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Forum\EO_Forum resetDescription()
	 * @method \Bitrix\Forum\EO_Forum unsetDescription()
	 * @method \string fillDescription()
	 * @method \int getSort()
	 * @method \Bitrix\Forum\EO_Forum setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Forum\EO_Forum resetSort()
	 * @method \Bitrix\Forum\EO_Forum unsetSort()
	 * @method \int fillSort()
	 * @method \boolean getActive()
	 * @method \Bitrix\Forum\EO_Forum setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Forum\EO_Forum resetActive()
	 * @method \Bitrix\Forum\EO_Forum unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getAllowHtml()
	 * @method \Bitrix\Forum\EO_Forum setAllowHtml(\boolean|\Bitrix\Main\DB\SqlExpression $allowHtml)
	 * @method bool hasAllowHtml()
	 * @method bool isAllowHtmlFilled()
	 * @method bool isAllowHtmlChanged()
	 * @method \boolean remindActualAllowHtml()
	 * @method \boolean requireAllowHtml()
	 * @method \Bitrix\Forum\EO_Forum resetAllowHtml()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowHtml()
	 * @method \boolean fillAllowHtml()
	 * @method \boolean getAllowAnchor()
	 * @method \Bitrix\Forum\EO_Forum setAllowAnchor(\boolean|\Bitrix\Main\DB\SqlExpression $allowAnchor)
	 * @method bool hasAllowAnchor()
	 * @method bool isAllowAnchorFilled()
	 * @method bool isAllowAnchorChanged()
	 * @method \boolean remindActualAllowAnchor()
	 * @method \boolean requireAllowAnchor()
	 * @method \Bitrix\Forum\EO_Forum resetAllowAnchor()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowAnchor()
	 * @method \boolean fillAllowAnchor()
	 * @method \boolean getAllowBiu()
	 * @method \Bitrix\Forum\EO_Forum setAllowBiu(\boolean|\Bitrix\Main\DB\SqlExpression $allowBiu)
	 * @method bool hasAllowBiu()
	 * @method bool isAllowBiuFilled()
	 * @method bool isAllowBiuChanged()
	 * @method \boolean remindActualAllowBiu()
	 * @method \boolean requireAllowBiu()
	 * @method \Bitrix\Forum\EO_Forum resetAllowBiu()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowBiu()
	 * @method \boolean fillAllowBiu()
	 * @method \boolean getAllowImg()
	 * @method \Bitrix\Forum\EO_Forum setAllowImg(\boolean|\Bitrix\Main\DB\SqlExpression $allowImg)
	 * @method bool hasAllowImg()
	 * @method bool isAllowImgFilled()
	 * @method bool isAllowImgChanged()
	 * @method \boolean remindActualAllowImg()
	 * @method \boolean requireAllowImg()
	 * @method \Bitrix\Forum\EO_Forum resetAllowImg()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowImg()
	 * @method \boolean fillAllowImg()
	 * @method \boolean getAllowVideo()
	 * @method \Bitrix\Forum\EO_Forum setAllowVideo(\boolean|\Bitrix\Main\DB\SqlExpression $allowVideo)
	 * @method bool hasAllowVideo()
	 * @method bool isAllowVideoFilled()
	 * @method bool isAllowVideoChanged()
	 * @method \boolean remindActualAllowVideo()
	 * @method \boolean requireAllowVideo()
	 * @method \Bitrix\Forum\EO_Forum resetAllowVideo()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowVideo()
	 * @method \boolean fillAllowVideo()
	 * @method \boolean getAllowList()
	 * @method \Bitrix\Forum\EO_Forum setAllowList(\boolean|\Bitrix\Main\DB\SqlExpression $allowList)
	 * @method bool hasAllowList()
	 * @method bool isAllowListFilled()
	 * @method bool isAllowListChanged()
	 * @method \boolean remindActualAllowList()
	 * @method \boolean requireAllowList()
	 * @method \Bitrix\Forum\EO_Forum resetAllowList()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowList()
	 * @method \boolean fillAllowList()
	 * @method \boolean getAllowQuote()
	 * @method \Bitrix\Forum\EO_Forum setAllowQuote(\boolean|\Bitrix\Main\DB\SqlExpression $allowQuote)
	 * @method bool hasAllowQuote()
	 * @method bool isAllowQuoteFilled()
	 * @method bool isAllowQuoteChanged()
	 * @method \boolean remindActualAllowQuote()
	 * @method \boolean requireAllowQuote()
	 * @method \Bitrix\Forum\EO_Forum resetAllowQuote()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowQuote()
	 * @method \boolean fillAllowQuote()
	 * @method \boolean getAllowCode()
	 * @method \Bitrix\Forum\EO_Forum setAllowCode(\boolean|\Bitrix\Main\DB\SqlExpression $allowCode)
	 * @method bool hasAllowCode()
	 * @method bool isAllowCodeFilled()
	 * @method bool isAllowCodeChanged()
	 * @method \boolean remindActualAllowCode()
	 * @method \boolean requireAllowCode()
	 * @method \Bitrix\Forum\EO_Forum resetAllowCode()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowCode()
	 * @method \boolean fillAllowCode()
	 * @method \boolean getAllowFont()
	 * @method \Bitrix\Forum\EO_Forum setAllowFont(\boolean|\Bitrix\Main\DB\SqlExpression $allowFont)
	 * @method bool hasAllowFont()
	 * @method bool isAllowFontFilled()
	 * @method bool isAllowFontChanged()
	 * @method \boolean remindActualAllowFont()
	 * @method \boolean requireAllowFont()
	 * @method \Bitrix\Forum\EO_Forum resetAllowFont()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowFont()
	 * @method \boolean fillAllowFont()
	 * @method \boolean getAllowSmiles()
	 * @method \Bitrix\Forum\EO_Forum setAllowSmiles(\boolean|\Bitrix\Main\DB\SqlExpression $allowSmiles)
	 * @method bool hasAllowSmiles()
	 * @method bool isAllowSmilesFilled()
	 * @method bool isAllowSmilesChanged()
	 * @method \boolean remindActualAllowSmiles()
	 * @method \boolean requireAllowSmiles()
	 * @method \Bitrix\Forum\EO_Forum resetAllowSmiles()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowSmiles()
	 * @method \boolean fillAllowSmiles()
	 * @method \boolean getAllowTable()
	 * @method \Bitrix\Forum\EO_Forum setAllowTable(\boolean|\Bitrix\Main\DB\SqlExpression $allowTable)
	 * @method bool hasAllowTable()
	 * @method bool isAllowTableFilled()
	 * @method bool isAllowTableChanged()
	 * @method \boolean remindActualAllowTable()
	 * @method \boolean requireAllowTable()
	 * @method \Bitrix\Forum\EO_Forum resetAllowTable()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowTable()
	 * @method \boolean fillAllowTable()
	 * @method \boolean getAllowAlign()
	 * @method \Bitrix\Forum\EO_Forum setAllowAlign(\boolean|\Bitrix\Main\DB\SqlExpression $allowAlign)
	 * @method bool hasAllowAlign()
	 * @method bool isAllowAlignFilled()
	 * @method bool isAllowAlignChanged()
	 * @method \boolean remindActualAllowAlign()
	 * @method \boolean requireAllowAlign()
	 * @method \Bitrix\Forum\EO_Forum resetAllowAlign()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowAlign()
	 * @method \boolean fillAllowAlign()
	 * @method \boolean getAllowNl2br()
	 * @method \Bitrix\Forum\EO_Forum setAllowNl2br(\boolean|\Bitrix\Main\DB\SqlExpression $allowNl2br)
	 * @method bool hasAllowNl2br()
	 * @method bool isAllowNl2brFilled()
	 * @method bool isAllowNl2brChanged()
	 * @method \boolean remindActualAllowNl2br()
	 * @method \boolean requireAllowNl2br()
	 * @method \Bitrix\Forum\EO_Forum resetAllowNl2br()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowNl2br()
	 * @method \boolean fillAllowNl2br()
	 * @method \string getAllowUpload()
	 * @method \Bitrix\Forum\EO_Forum setAllowUpload(\string|\Bitrix\Main\DB\SqlExpression $allowUpload)
	 * @method bool hasAllowUpload()
	 * @method bool isAllowUploadFilled()
	 * @method bool isAllowUploadChanged()
	 * @method \string remindActualAllowUpload()
	 * @method \string requireAllowUpload()
	 * @method \Bitrix\Forum\EO_Forum resetAllowUpload()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowUpload()
	 * @method \string fillAllowUpload()
	 * @method \string getAllowUploadExt()
	 * @method \Bitrix\Forum\EO_Forum setAllowUploadExt(\string|\Bitrix\Main\DB\SqlExpression $allowUploadExt)
	 * @method bool hasAllowUploadExt()
	 * @method bool isAllowUploadExtFilled()
	 * @method bool isAllowUploadExtChanged()
	 * @method \string remindActualAllowUploadExt()
	 * @method \string requireAllowUploadExt()
	 * @method \Bitrix\Forum\EO_Forum resetAllowUploadExt()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowUploadExt()
	 * @method \string fillAllowUploadExt()
	 * @method \boolean getAllowMoveTopic()
	 * @method \Bitrix\Forum\EO_Forum setAllowMoveTopic(\boolean|\Bitrix\Main\DB\SqlExpression $allowMoveTopic)
	 * @method bool hasAllowMoveTopic()
	 * @method bool isAllowMoveTopicFilled()
	 * @method bool isAllowMoveTopicChanged()
	 * @method \boolean remindActualAllowMoveTopic()
	 * @method \boolean requireAllowMoveTopic()
	 * @method \Bitrix\Forum\EO_Forum resetAllowMoveTopic()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowMoveTopic()
	 * @method \boolean fillAllowMoveTopic()
	 * @method \boolean getAllowTopicTitled()
	 * @method \Bitrix\Forum\EO_Forum setAllowTopicTitled(\boolean|\Bitrix\Main\DB\SqlExpression $allowTopicTitled)
	 * @method bool hasAllowTopicTitled()
	 * @method bool isAllowTopicTitledFilled()
	 * @method bool isAllowTopicTitledChanged()
	 * @method \boolean remindActualAllowTopicTitled()
	 * @method \boolean requireAllowTopicTitled()
	 * @method \Bitrix\Forum\EO_Forum resetAllowTopicTitled()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowTopicTitled()
	 * @method \boolean fillAllowTopicTitled()
	 * @method \boolean getAllowSignature()
	 * @method \Bitrix\Forum\EO_Forum setAllowSignature(\boolean|\Bitrix\Main\DB\SqlExpression $allowSignature)
	 * @method bool hasAllowSignature()
	 * @method bool isAllowSignatureFilled()
	 * @method bool isAllowSignatureChanged()
	 * @method \boolean remindActualAllowSignature()
	 * @method \boolean requireAllowSignature()
	 * @method \Bitrix\Forum\EO_Forum resetAllowSignature()
	 * @method \Bitrix\Forum\EO_Forum unsetAllowSignature()
	 * @method \boolean fillAllowSignature()
	 * @method \boolean getAskGuestEmail()
	 * @method \Bitrix\Forum\EO_Forum setAskGuestEmail(\boolean|\Bitrix\Main\DB\SqlExpression $askGuestEmail)
	 * @method bool hasAskGuestEmail()
	 * @method bool isAskGuestEmailFilled()
	 * @method bool isAskGuestEmailChanged()
	 * @method \boolean remindActualAskGuestEmail()
	 * @method \boolean requireAskGuestEmail()
	 * @method \Bitrix\Forum\EO_Forum resetAskGuestEmail()
	 * @method \Bitrix\Forum\EO_Forum unsetAskGuestEmail()
	 * @method \boolean fillAskGuestEmail()
	 * @method \boolean getUseCaptcha()
	 * @method \Bitrix\Forum\EO_Forum setUseCaptcha(\boolean|\Bitrix\Main\DB\SqlExpression $useCaptcha)
	 * @method bool hasUseCaptcha()
	 * @method bool isUseCaptchaFilled()
	 * @method bool isUseCaptchaChanged()
	 * @method \boolean remindActualUseCaptcha()
	 * @method \boolean requireUseCaptcha()
	 * @method \Bitrix\Forum\EO_Forum resetUseCaptcha()
	 * @method \Bitrix\Forum\EO_Forum unsetUseCaptcha()
	 * @method \boolean fillUseCaptcha()
	 * @method \boolean getIndexation()
	 * @method \Bitrix\Forum\EO_Forum setIndexation(\boolean|\Bitrix\Main\DB\SqlExpression $indexation)
	 * @method bool hasIndexation()
	 * @method bool isIndexationFilled()
	 * @method bool isIndexationChanged()
	 * @method \boolean remindActualIndexation()
	 * @method \boolean requireIndexation()
	 * @method \Bitrix\Forum\EO_Forum resetIndexation()
	 * @method \Bitrix\Forum\EO_Forum unsetIndexation()
	 * @method \boolean fillIndexation()
	 * @method \boolean getDeduplication()
	 * @method \Bitrix\Forum\EO_Forum setDeduplication(\boolean|\Bitrix\Main\DB\SqlExpression $deduplication)
	 * @method bool hasDeduplication()
	 * @method bool isDeduplicationFilled()
	 * @method bool isDeduplicationChanged()
	 * @method \boolean remindActualDeduplication()
	 * @method \boolean requireDeduplication()
	 * @method \Bitrix\Forum\EO_Forum resetDeduplication()
	 * @method \Bitrix\Forum\EO_Forum unsetDeduplication()
	 * @method \boolean fillDeduplication()
	 * @method \boolean getModeration()
	 * @method \Bitrix\Forum\EO_Forum setModeration(\boolean|\Bitrix\Main\DB\SqlExpression $moderation)
	 * @method bool hasModeration()
	 * @method bool isModerationFilled()
	 * @method bool isModerationChanged()
	 * @method \boolean remindActualModeration()
	 * @method \boolean requireModeration()
	 * @method \Bitrix\Forum\EO_Forum resetModeration()
	 * @method \Bitrix\Forum\EO_Forum unsetModeration()
	 * @method \boolean fillModeration()
	 * @method \string getOrderBy()
	 * @method \Bitrix\Forum\EO_Forum setOrderBy(\string|\Bitrix\Main\DB\SqlExpression $orderBy)
	 * @method bool hasOrderBy()
	 * @method bool isOrderByFilled()
	 * @method bool isOrderByChanged()
	 * @method \string remindActualOrderBy()
	 * @method \string requireOrderBy()
	 * @method \Bitrix\Forum\EO_Forum resetOrderBy()
	 * @method \Bitrix\Forum\EO_Forum unsetOrderBy()
	 * @method \string fillOrderBy()
	 * @method \string getOrderDirection()
	 * @method \Bitrix\Forum\EO_Forum setOrderDirection(\string|\Bitrix\Main\DB\SqlExpression $orderDirection)
	 * @method bool hasOrderDirection()
	 * @method bool isOrderDirectionFilled()
	 * @method bool isOrderDirectionChanged()
	 * @method \string remindActualOrderDirection()
	 * @method \string requireOrderDirection()
	 * @method \Bitrix\Forum\EO_Forum resetOrderDirection()
	 * @method \Bitrix\Forum\EO_Forum unsetOrderDirection()
	 * @method \string fillOrderDirection()
	 * @method \int getTopics()
	 * @method \Bitrix\Forum\EO_Forum setTopics(\int|\Bitrix\Main\DB\SqlExpression $topics)
	 * @method bool hasTopics()
	 * @method bool isTopicsFilled()
	 * @method bool isTopicsChanged()
	 * @method \int remindActualTopics()
	 * @method \int requireTopics()
	 * @method \Bitrix\Forum\EO_Forum resetTopics()
	 * @method \Bitrix\Forum\EO_Forum unsetTopics()
	 * @method \int fillTopics()
	 * @method \int getPosts()
	 * @method \Bitrix\Forum\EO_Forum setPosts(\int|\Bitrix\Main\DB\SqlExpression $posts)
	 * @method bool hasPosts()
	 * @method bool isPostsFilled()
	 * @method bool isPostsChanged()
	 * @method \int remindActualPosts()
	 * @method \int requirePosts()
	 * @method \Bitrix\Forum\EO_Forum resetPosts()
	 * @method \Bitrix\Forum\EO_Forum unsetPosts()
	 * @method \int fillPosts()
	 * @method \int getPostsUnapproved()
	 * @method \Bitrix\Forum\EO_Forum setPostsUnapproved(\int|\Bitrix\Main\DB\SqlExpression $postsUnapproved)
	 * @method bool hasPostsUnapproved()
	 * @method bool isPostsUnapprovedFilled()
	 * @method bool isPostsUnapprovedChanged()
	 * @method \int remindActualPostsUnapproved()
	 * @method \int requirePostsUnapproved()
	 * @method \Bitrix\Forum\EO_Forum resetPostsUnapproved()
	 * @method \Bitrix\Forum\EO_Forum unsetPostsUnapproved()
	 * @method \int fillPostsUnapproved()
	 * @method \int getLastPosterId()
	 * @method \Bitrix\Forum\EO_Forum setLastPosterId(\int|\Bitrix\Main\DB\SqlExpression $lastPosterId)
	 * @method bool hasLastPosterId()
	 * @method bool isLastPosterIdFilled()
	 * @method bool isLastPosterIdChanged()
	 * @method \int remindActualLastPosterId()
	 * @method \int requireLastPosterId()
	 * @method \Bitrix\Forum\EO_Forum resetLastPosterId()
	 * @method \Bitrix\Forum\EO_Forum unsetLastPosterId()
	 * @method \int fillLastPosterId()
	 * @method \string getLastPosterName()
	 * @method \Bitrix\Forum\EO_Forum setLastPosterName(\string|\Bitrix\Main\DB\SqlExpression $lastPosterName)
	 * @method bool hasLastPosterName()
	 * @method bool isLastPosterNameFilled()
	 * @method bool isLastPosterNameChanged()
	 * @method \string remindActualLastPosterName()
	 * @method \string requireLastPosterName()
	 * @method \Bitrix\Forum\EO_Forum resetLastPosterName()
	 * @method \Bitrix\Forum\EO_Forum unsetLastPosterName()
	 * @method \string fillLastPosterName()
	 * @method \Bitrix\Main\Type\DateTime getLastPostDate()
	 * @method \Bitrix\Forum\EO_Forum setLastPostDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastPostDate)
	 * @method bool hasLastPostDate()
	 * @method bool isLastPostDateFilled()
	 * @method bool isLastPostDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastPostDate()
	 * @method \Bitrix\Main\Type\DateTime requireLastPostDate()
	 * @method \Bitrix\Forum\EO_Forum resetLastPostDate()
	 * @method \Bitrix\Forum\EO_Forum unsetLastPostDate()
	 * @method \Bitrix\Main\Type\DateTime fillLastPostDate()
	 * @method \int getLastMessageId()
	 * @method \Bitrix\Forum\EO_Forum setLastMessageId(\int|\Bitrix\Main\DB\SqlExpression $lastMessageId)
	 * @method bool hasLastMessageId()
	 * @method bool isLastMessageIdFilled()
	 * @method bool isLastMessageIdChanged()
	 * @method \int remindActualLastMessageId()
	 * @method \int requireLastMessageId()
	 * @method \Bitrix\Forum\EO_Forum resetLastMessageId()
	 * @method \Bitrix\Forum\EO_Forum unsetLastMessageId()
	 * @method \int fillLastMessageId()
	 * @method \int getAbsLastPosterId()
	 * @method \Bitrix\Forum\EO_Forum setAbsLastPosterId(\int|\Bitrix\Main\DB\SqlExpression $absLastPosterId)
	 * @method bool hasAbsLastPosterId()
	 * @method bool isAbsLastPosterIdFilled()
	 * @method bool isAbsLastPosterIdChanged()
	 * @method \int remindActualAbsLastPosterId()
	 * @method \int requireAbsLastPosterId()
	 * @method \Bitrix\Forum\EO_Forum resetAbsLastPosterId()
	 * @method \Bitrix\Forum\EO_Forum unsetAbsLastPosterId()
	 * @method \int fillAbsLastPosterId()
	 * @method \string getAbsLastPosterName()
	 * @method \Bitrix\Forum\EO_Forum setAbsLastPosterName(\string|\Bitrix\Main\DB\SqlExpression $absLastPosterName)
	 * @method bool hasAbsLastPosterName()
	 * @method bool isAbsLastPosterNameFilled()
	 * @method bool isAbsLastPosterNameChanged()
	 * @method \string remindActualAbsLastPosterName()
	 * @method \string requireAbsLastPosterName()
	 * @method \Bitrix\Forum\EO_Forum resetAbsLastPosterName()
	 * @method \Bitrix\Forum\EO_Forum unsetAbsLastPosterName()
	 * @method \string fillAbsLastPosterName()
	 * @method \Bitrix\Main\Type\DateTime getAbsLastPostDate()
	 * @method \Bitrix\Forum\EO_Forum setAbsLastPostDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $absLastPostDate)
	 * @method bool hasAbsLastPostDate()
	 * @method bool isAbsLastPostDateFilled()
	 * @method bool isAbsLastPostDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualAbsLastPostDate()
	 * @method \Bitrix\Main\Type\DateTime requireAbsLastPostDate()
	 * @method \Bitrix\Forum\EO_Forum resetAbsLastPostDate()
	 * @method \Bitrix\Forum\EO_Forum unsetAbsLastPostDate()
	 * @method \Bitrix\Main\Type\DateTime fillAbsLastPostDate()
	 * @method \int getAbsLastMessageId()
	 * @method \Bitrix\Forum\EO_Forum setAbsLastMessageId(\int|\Bitrix\Main\DB\SqlExpression $absLastMessageId)
	 * @method bool hasAbsLastMessageId()
	 * @method bool isAbsLastMessageIdFilled()
	 * @method bool isAbsLastMessageIdChanged()
	 * @method \int remindActualAbsLastMessageId()
	 * @method \int requireAbsLastMessageId()
	 * @method \Bitrix\Forum\EO_Forum resetAbsLastMessageId()
	 * @method \Bitrix\Forum\EO_Forum unsetAbsLastMessageId()
	 * @method \int fillAbsLastMessageId()
	 * @method \string getEvent1()
	 * @method \Bitrix\Forum\EO_Forum setEvent1(\string|\Bitrix\Main\DB\SqlExpression $event1)
	 * @method bool hasEvent1()
	 * @method bool isEvent1Filled()
	 * @method bool isEvent1Changed()
	 * @method \string remindActualEvent1()
	 * @method \string requireEvent1()
	 * @method \Bitrix\Forum\EO_Forum resetEvent1()
	 * @method \Bitrix\Forum\EO_Forum unsetEvent1()
	 * @method \string fillEvent1()
	 * @method \string getEvent2()
	 * @method \Bitrix\Forum\EO_Forum setEvent2(\string|\Bitrix\Main\DB\SqlExpression $event2)
	 * @method bool hasEvent2()
	 * @method bool isEvent2Filled()
	 * @method bool isEvent2Changed()
	 * @method \string remindActualEvent2()
	 * @method \string requireEvent2()
	 * @method \Bitrix\Forum\EO_Forum resetEvent2()
	 * @method \Bitrix\Forum\EO_Forum unsetEvent2()
	 * @method \string fillEvent2()
	 * @method \string getEvent3()
	 * @method \Bitrix\Forum\EO_Forum setEvent3(\string|\Bitrix\Main\DB\SqlExpression $event3)
	 * @method bool hasEvent3()
	 * @method bool isEvent3Filled()
	 * @method bool isEvent3Changed()
	 * @method \string remindActualEvent3()
	 * @method \string requireEvent3()
	 * @method \Bitrix\Forum\EO_Forum resetEvent3()
	 * @method \Bitrix\Forum\EO_Forum unsetEvent3()
	 * @method \string fillEvent3()
	 * @method \string getXmlId()
	 * @method \Bitrix\Forum\EO_Forum setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Forum\EO_Forum resetXmlId()
	 * @method \Bitrix\Forum\EO_Forum unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getHtml()
	 * @method \Bitrix\Forum\EO_Forum setHtml(\string|\Bitrix\Main\DB\SqlExpression $html)
	 * @method bool hasHtml()
	 * @method bool isHtmlFilled()
	 * @method bool isHtmlChanged()
	 * @method \string remindActualHtml()
	 * @method \string requireHtml()
	 * @method \Bitrix\Forum\EO_Forum resetHtml()
	 * @method \Bitrix\Forum\EO_Forum unsetHtml()
	 * @method \string fillHtml()
	 * @method \Bitrix\Forum\EO_Permission getPermission()
	 * @method \Bitrix\Forum\EO_Permission remindActualPermission()
	 * @method \Bitrix\Forum\EO_Permission requirePermission()
	 * @method \Bitrix\Forum\EO_Forum setPermission(\Bitrix\Forum\EO_Permission $object)
	 * @method \Bitrix\Forum\EO_Forum resetPermission()
	 * @method \Bitrix\Forum\EO_Forum unsetPermission()
	 * @method bool hasPermission()
	 * @method bool isPermissionFilled()
	 * @method bool isPermissionChanged()
	 * @method \Bitrix\Forum\EO_Permission fillPermission()
	 * @method \Bitrix\Forum\EO_ForumSite getSite()
	 * @method \Bitrix\Forum\EO_ForumSite remindActualSite()
	 * @method \Bitrix\Forum\EO_ForumSite requireSite()
	 * @method \Bitrix\Forum\EO_Forum setSite(\Bitrix\Forum\EO_ForumSite $object)
	 * @method \Bitrix\Forum\EO_Forum resetSite()
	 * @method \Bitrix\Forum\EO_Forum unsetSite()
	 * @method bool hasSite()
	 * @method bool isSiteFilled()
	 * @method bool isSiteChanged()
	 * @method \Bitrix\Forum\EO_ForumSite fillSite()
	 * @method \Bitrix\Forum\EO_Group getGroup()
	 * @method \Bitrix\Forum\EO_Group remindActualGroup()
	 * @method \Bitrix\Forum\EO_Group requireGroup()
	 * @method \Bitrix\Forum\EO_Forum setGroup(\Bitrix\Forum\EO_Group $object)
	 * @method \Bitrix\Forum\EO_Forum resetGroup()
	 * @method \Bitrix\Forum\EO_Forum unsetGroup()
	 * @method bool hasGroup()
	 * @method bool isGroupFilled()
	 * @method bool isGroupChanged()
	 * @method \Bitrix\Forum\EO_Group fillGroup()
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
	 * @method \Bitrix\Forum\EO_Forum set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_Forum reset($fieldName)
	 * @method \Bitrix\Forum\EO_Forum unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_Forum wakeUp($data)
	 */
	class EO_Forum {
		/* @var \Bitrix\Forum\ForumTable */
		static public $dataClass = '\Bitrix\Forum\ForumTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_Forum_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getForumGroupIdList()
	 * @method \int[] fillForumGroupId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getAllowHtmlList()
	 * @method \boolean[] fillAllowHtml()
	 * @method \boolean[] getAllowAnchorList()
	 * @method \boolean[] fillAllowAnchor()
	 * @method \boolean[] getAllowBiuList()
	 * @method \boolean[] fillAllowBiu()
	 * @method \boolean[] getAllowImgList()
	 * @method \boolean[] fillAllowImg()
	 * @method \boolean[] getAllowVideoList()
	 * @method \boolean[] fillAllowVideo()
	 * @method \boolean[] getAllowListList()
	 * @method \boolean[] fillAllowList()
	 * @method \boolean[] getAllowQuoteList()
	 * @method \boolean[] fillAllowQuote()
	 * @method \boolean[] getAllowCodeList()
	 * @method \boolean[] fillAllowCode()
	 * @method \boolean[] getAllowFontList()
	 * @method \boolean[] fillAllowFont()
	 * @method \boolean[] getAllowSmilesList()
	 * @method \boolean[] fillAllowSmiles()
	 * @method \boolean[] getAllowTableList()
	 * @method \boolean[] fillAllowTable()
	 * @method \boolean[] getAllowAlignList()
	 * @method \boolean[] fillAllowAlign()
	 * @method \boolean[] getAllowNl2brList()
	 * @method \boolean[] fillAllowNl2br()
	 * @method \string[] getAllowUploadList()
	 * @method \string[] fillAllowUpload()
	 * @method \string[] getAllowUploadExtList()
	 * @method \string[] fillAllowUploadExt()
	 * @method \boolean[] getAllowMoveTopicList()
	 * @method \boolean[] fillAllowMoveTopic()
	 * @method \boolean[] getAllowTopicTitledList()
	 * @method \boolean[] fillAllowTopicTitled()
	 * @method \boolean[] getAllowSignatureList()
	 * @method \boolean[] fillAllowSignature()
	 * @method \boolean[] getAskGuestEmailList()
	 * @method \boolean[] fillAskGuestEmail()
	 * @method \boolean[] getUseCaptchaList()
	 * @method \boolean[] fillUseCaptcha()
	 * @method \boolean[] getIndexationList()
	 * @method \boolean[] fillIndexation()
	 * @method \boolean[] getDeduplicationList()
	 * @method \boolean[] fillDeduplication()
	 * @method \boolean[] getModerationList()
	 * @method \boolean[] fillModeration()
	 * @method \string[] getOrderByList()
	 * @method \string[] fillOrderBy()
	 * @method \string[] getOrderDirectionList()
	 * @method \string[] fillOrderDirection()
	 * @method \int[] getTopicsList()
	 * @method \int[] fillTopics()
	 * @method \int[] getPostsList()
	 * @method \int[] fillPosts()
	 * @method \int[] getPostsUnapprovedList()
	 * @method \int[] fillPostsUnapproved()
	 * @method \int[] getLastPosterIdList()
	 * @method \int[] fillLastPosterId()
	 * @method \string[] getLastPosterNameList()
	 * @method \string[] fillLastPosterName()
	 * @method \Bitrix\Main\Type\DateTime[] getLastPostDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastPostDate()
	 * @method \int[] getLastMessageIdList()
	 * @method \int[] fillLastMessageId()
	 * @method \int[] getAbsLastPosterIdList()
	 * @method \int[] fillAbsLastPosterId()
	 * @method \string[] getAbsLastPosterNameList()
	 * @method \string[] fillAbsLastPosterName()
	 * @method \Bitrix\Main\Type\DateTime[] getAbsLastPostDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillAbsLastPostDate()
	 * @method \int[] getAbsLastMessageIdList()
	 * @method \int[] fillAbsLastMessageId()
	 * @method \string[] getEvent1List()
	 * @method \string[] fillEvent1()
	 * @method \string[] getEvent2List()
	 * @method \string[] fillEvent2()
	 * @method \string[] getEvent3List()
	 * @method \string[] fillEvent3()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getHtmlList()
	 * @method \string[] fillHtml()
	 * @method \Bitrix\Forum\EO_Permission[] getPermissionList()
	 * @method \Bitrix\Forum\EO_Forum_Collection getPermissionCollection()
	 * @method \Bitrix\Forum\EO_Permission_Collection fillPermission()
	 * @method \Bitrix\Forum\EO_ForumSite[] getSiteList()
	 * @method \Bitrix\Forum\EO_Forum_Collection getSiteCollection()
	 * @method \Bitrix\Forum\EO_ForumSite_Collection fillSite()
	 * @method \Bitrix\Forum\EO_Group[] getGroupList()
	 * @method \Bitrix\Forum\EO_Forum_Collection getGroupCollection()
	 * @method \Bitrix\Forum\EO_Group_Collection fillGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_Forum $object)
	 * @method bool has(\Bitrix\Forum\EO_Forum $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Forum getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Forum[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_Forum $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_Forum_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_Forum current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Forum_Collection merge(?EO_Forum_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Forum_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\ForumTable */
		static public $dataClass = '\Bitrix\Forum\ForumTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Forum_Result exec()
	 * @method \Bitrix\Forum\EO_Forum fetchObject()
	 * @method \Bitrix\Forum\EO_Forum_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Forum_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_Forum fetchObject()
	 * @method \Bitrix\Forum\EO_Forum_Collection fetchCollection()
	 */
	class EO_Forum_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_Forum createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_Forum_Collection createCollection()
	 * @method \Bitrix\Forum\EO_Forum wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_Forum_Collection wakeUpCollection($rows)
	 */
	class EO_Forum_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\GroupTable:forum/lib/group.php */
namespace Bitrix\Forum {
	/**
	 * EO_Group
	 * @see \Bitrix\Forum\GroupTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_Group setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getSort()
	 * @method \Bitrix\Forum\EO_Group setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Forum\EO_Group resetSort()
	 * @method \Bitrix\Forum\EO_Group unsetSort()
	 * @method \int fillSort()
	 * @method \int getParentId()
	 * @method \Bitrix\Forum\EO_Group setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Forum\EO_Group resetParentId()
	 * @method \Bitrix\Forum\EO_Group unsetParentId()
	 * @method \int fillParentId()
	 * @method \int getLeftMargin()
	 * @method \Bitrix\Forum\EO_Group setLeftMargin(\int|\Bitrix\Main\DB\SqlExpression $leftMargin)
	 * @method bool hasLeftMargin()
	 * @method bool isLeftMarginFilled()
	 * @method bool isLeftMarginChanged()
	 * @method \int remindActualLeftMargin()
	 * @method \int requireLeftMargin()
	 * @method \Bitrix\Forum\EO_Group resetLeftMargin()
	 * @method \Bitrix\Forum\EO_Group unsetLeftMargin()
	 * @method \int fillLeftMargin()
	 * @method \int getRightMargin()
	 * @method \Bitrix\Forum\EO_Group setRightMargin(\int|\Bitrix\Main\DB\SqlExpression $rightMargin)
	 * @method bool hasRightMargin()
	 * @method bool isRightMarginFilled()
	 * @method bool isRightMarginChanged()
	 * @method \int remindActualRightMargin()
	 * @method \int requireRightMargin()
	 * @method \Bitrix\Forum\EO_Group resetRightMargin()
	 * @method \Bitrix\Forum\EO_Group unsetRightMargin()
	 * @method \int fillRightMargin()
	 * @method \int getDepthLevel()
	 * @method \Bitrix\Forum\EO_Group setDepthLevel(\int|\Bitrix\Main\DB\SqlExpression $depthLevel)
	 * @method bool hasDepthLevel()
	 * @method bool isDepthLevelFilled()
	 * @method bool isDepthLevelChanged()
	 * @method \int remindActualDepthLevel()
	 * @method \int requireDepthLevel()
	 * @method \Bitrix\Forum\EO_Group resetDepthLevel()
	 * @method \Bitrix\Forum\EO_Group unsetDepthLevel()
	 * @method \int fillDepthLevel()
	 * @method \string getXmlId()
	 * @method \Bitrix\Forum\EO_Group setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Forum\EO_Group resetXmlId()
	 * @method \Bitrix\Forum\EO_Group unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \Bitrix\Forum\EO_GroupLang getLang()
	 * @method \Bitrix\Forum\EO_GroupLang remindActualLang()
	 * @method \Bitrix\Forum\EO_GroupLang requireLang()
	 * @method \Bitrix\Forum\EO_Group setLang(\Bitrix\Forum\EO_GroupLang $object)
	 * @method \Bitrix\Forum\EO_Group resetLang()
	 * @method \Bitrix\Forum\EO_Group unsetLang()
	 * @method bool hasLang()
	 * @method bool isLangFilled()
	 * @method bool isLangChanged()
	 * @method \Bitrix\Forum\EO_GroupLang fillLang()
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
	 * @method \Bitrix\Forum\EO_Group set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_Group reset($fieldName)
	 * @method \Bitrix\Forum\EO_Group unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_Group wakeUp($data)
	 */
	class EO_Group {
		/* @var \Bitrix\Forum\GroupTable */
		static public $dataClass = '\Bitrix\Forum\GroupTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_Group_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \int[] getLeftMarginList()
	 * @method \int[] fillLeftMargin()
	 * @method \int[] getRightMarginList()
	 * @method \int[] fillRightMargin()
	 * @method \int[] getDepthLevelList()
	 * @method \int[] fillDepthLevel()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \Bitrix\Forum\EO_GroupLang[] getLangList()
	 * @method \Bitrix\Forum\EO_Group_Collection getLangCollection()
	 * @method \Bitrix\Forum\EO_GroupLang_Collection fillLang()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_Group $object)
	 * @method bool has(\Bitrix\Forum\EO_Group $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Group getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Group[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_Group $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_Group_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_Group current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Group_Collection merge(?EO_Group_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Group_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\GroupTable */
		static public $dataClass = '\Bitrix\Forum\GroupTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Group_Result exec()
	 * @method \Bitrix\Forum\EO_Group fetchObject()
	 * @method \Bitrix\Forum\EO_Group_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Group_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_Group fetchObject()
	 * @method \Bitrix\Forum\EO_Group_Collection fetchCollection()
	 */
	class EO_Group_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_Group createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_Group_Collection createCollection()
	 * @method \Bitrix\Forum\EO_Group wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_Group_Collection wakeUpCollection($rows)
	 */
	class EO_Group_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\GroupLangTable:forum/lib/group.php */
namespace Bitrix\Forum {
	/**
	 * EO_GroupLang
	 * @see \Bitrix\Forum\GroupLangTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_GroupLang setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getForumGroupId()
	 * @method \Bitrix\Forum\EO_GroupLang setForumGroupId(\int|\Bitrix\Main\DB\SqlExpression $forumGroupId)
	 * @method bool hasForumGroupId()
	 * @method bool isForumGroupIdFilled()
	 * @method bool isForumGroupIdChanged()
	 * @method \int remindActualForumGroupId()
	 * @method \int requireForumGroupId()
	 * @method \Bitrix\Forum\EO_GroupLang resetForumGroupId()
	 * @method \Bitrix\Forum\EO_GroupLang unsetForumGroupId()
	 * @method \int fillForumGroupId()
	 * @method \string getLid()
	 * @method \Bitrix\Forum\EO_GroupLang setLid(\string|\Bitrix\Main\DB\SqlExpression $lid)
	 * @method bool hasLid()
	 * @method bool isLidFilled()
	 * @method bool isLidChanged()
	 * @method \string remindActualLid()
	 * @method \string requireLid()
	 * @method \Bitrix\Forum\EO_GroupLang resetLid()
	 * @method \Bitrix\Forum\EO_GroupLang unsetLid()
	 * @method \string fillLid()
	 * @method \string getName()
	 * @method \Bitrix\Forum\EO_GroupLang setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Forum\EO_GroupLang resetName()
	 * @method \Bitrix\Forum\EO_GroupLang unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Forum\EO_GroupLang setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Forum\EO_GroupLang resetDescription()
	 * @method \Bitrix\Forum\EO_GroupLang unsetDescription()
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
	 * @method \Bitrix\Forum\EO_GroupLang set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_GroupLang reset($fieldName)
	 * @method \Bitrix\Forum\EO_GroupLang unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_GroupLang wakeUp($data)
	 */
	class EO_GroupLang {
		/* @var \Bitrix\Forum\GroupLangTable */
		static public $dataClass = '\Bitrix\Forum\GroupLangTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_GroupLang_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getForumGroupIdList()
	 * @method \int[] fillForumGroupId()
	 * @method \string[] getLidList()
	 * @method \string[] fillLid()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_GroupLang $object)
	 * @method bool has(\Bitrix\Forum\EO_GroupLang $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_GroupLang getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_GroupLang[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_GroupLang $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_GroupLang_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_GroupLang current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_GroupLang_Collection merge(?EO_GroupLang_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_GroupLang_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\GroupLangTable */
		static public $dataClass = '\Bitrix\Forum\GroupLangTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GroupLang_Result exec()
	 * @method \Bitrix\Forum\EO_GroupLang fetchObject()
	 * @method \Bitrix\Forum\EO_GroupLang_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GroupLang_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_GroupLang fetchObject()
	 * @method \Bitrix\Forum\EO_GroupLang_Collection fetchCollection()
	 */
	class EO_GroupLang_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_GroupLang createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_GroupLang_Collection createCollection()
	 * @method \Bitrix\Forum\EO_GroupLang wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_GroupLang_Collection wakeUpCollection($rows)
	 */
	class EO_GroupLang_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\SubscribeTable:forum/lib/subscribe.php */
namespace Bitrix\Forum {
	/**
	 * EO_Subscribe
	 * @see \Bitrix\Forum\SubscribeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_Subscribe setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Forum\EO_Subscribe setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Forum\EO_Subscribe resetUserId()
	 * @method \Bitrix\Forum\EO_Subscribe unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getForumId()
	 * @method \Bitrix\Forum\EO_Subscribe setForumId(\int|\Bitrix\Main\DB\SqlExpression $forumId)
	 * @method bool hasForumId()
	 * @method bool isForumIdFilled()
	 * @method bool isForumIdChanged()
	 * @method \int remindActualForumId()
	 * @method \int requireForumId()
	 * @method \Bitrix\Forum\EO_Subscribe resetForumId()
	 * @method \Bitrix\Forum\EO_Subscribe unsetForumId()
	 * @method \int fillForumId()
	 * @method \int getTopicId()
	 * @method \Bitrix\Forum\EO_Subscribe setTopicId(\int|\Bitrix\Main\DB\SqlExpression $topicId)
	 * @method bool hasTopicId()
	 * @method bool isTopicIdFilled()
	 * @method bool isTopicIdChanged()
	 * @method \int remindActualTopicId()
	 * @method \int requireTopicId()
	 * @method \Bitrix\Forum\EO_Subscribe resetTopicId()
	 * @method \Bitrix\Forum\EO_Subscribe unsetTopicId()
	 * @method \int fillTopicId()
	 * @method \Bitrix\Main\Type\DateTime getStartDate()
	 * @method \Bitrix\Forum\EO_Subscribe setStartDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $startDate)
	 * @method bool hasStartDate()
	 * @method bool isStartDateFilled()
	 * @method bool isStartDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStartDate()
	 * @method \Bitrix\Main\Type\DateTime requireStartDate()
	 * @method \Bitrix\Forum\EO_Subscribe resetStartDate()
	 * @method \Bitrix\Forum\EO_Subscribe unsetStartDate()
	 * @method \Bitrix\Main\Type\DateTime fillStartDate()
	 * @method \int getLastSend()
	 * @method \Bitrix\Forum\EO_Subscribe setLastSend(\int|\Bitrix\Main\DB\SqlExpression $lastSend)
	 * @method bool hasLastSend()
	 * @method bool isLastSendFilled()
	 * @method bool isLastSendChanged()
	 * @method \int remindActualLastSend()
	 * @method \int requireLastSend()
	 * @method \Bitrix\Forum\EO_Subscribe resetLastSend()
	 * @method \Bitrix\Forum\EO_Subscribe unsetLastSend()
	 * @method \int fillLastSend()
	 * @method \string getNewTopicOnly()
	 * @method \Bitrix\Forum\EO_Subscribe setNewTopicOnly(\string|\Bitrix\Main\DB\SqlExpression $newTopicOnly)
	 * @method bool hasNewTopicOnly()
	 * @method bool isNewTopicOnlyFilled()
	 * @method bool isNewTopicOnlyChanged()
	 * @method \string remindActualNewTopicOnly()
	 * @method \string requireNewTopicOnly()
	 * @method \Bitrix\Forum\EO_Subscribe resetNewTopicOnly()
	 * @method \Bitrix\Forum\EO_Subscribe unsetNewTopicOnly()
	 * @method \string fillNewTopicOnly()
	 * @method \string getSiteId()
	 * @method \Bitrix\Forum\EO_Subscribe setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Forum\EO_Subscribe resetSiteId()
	 * @method \Bitrix\Forum\EO_Subscribe unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \int getSocnetGroupId()
	 * @method \Bitrix\Forum\EO_Subscribe setSocnetGroupId(\int|\Bitrix\Main\DB\SqlExpression $socnetGroupId)
	 * @method bool hasSocnetGroupId()
	 * @method bool isSocnetGroupIdFilled()
	 * @method bool isSocnetGroupIdChanged()
	 * @method \int remindActualSocnetGroupId()
	 * @method \int requireSocnetGroupId()
	 * @method \Bitrix\Forum\EO_Subscribe resetSocnetGroupId()
	 * @method \Bitrix\Forum\EO_Subscribe unsetSocnetGroupId()
	 * @method \int fillSocnetGroupId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Forum\EO_Subscribe setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Forum\EO_Subscribe resetUser()
	 * @method \Bitrix\Forum\EO_Subscribe unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Forum\EO_User getForumUser()
	 * @method \Bitrix\Forum\EO_User remindActualForumUser()
	 * @method \Bitrix\Forum\EO_User requireForumUser()
	 * @method \Bitrix\Forum\EO_Subscribe setForumUser(\Bitrix\Forum\EO_User $object)
	 * @method \Bitrix\Forum\EO_Subscribe resetForumUser()
	 * @method \Bitrix\Forum\EO_Subscribe unsetForumUser()
	 * @method bool hasForumUser()
	 * @method bool isForumUserFilled()
	 * @method bool isForumUserChanged()
	 * @method \Bitrix\Forum\EO_User fillForumUser()
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
	 * @method \Bitrix\Forum\EO_Subscribe set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_Subscribe reset($fieldName)
	 * @method \Bitrix\Forum\EO_Subscribe unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_Subscribe wakeUp($data)
	 */
	class EO_Subscribe {
		/* @var \Bitrix\Forum\SubscribeTable */
		static public $dataClass = '\Bitrix\Forum\SubscribeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_Subscribe_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getForumIdList()
	 * @method \int[] fillForumId()
	 * @method \int[] getTopicIdList()
	 * @method \int[] fillTopicId()
	 * @method \Bitrix\Main\Type\DateTime[] getStartDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStartDate()
	 * @method \int[] getLastSendList()
	 * @method \int[] fillLastSend()
	 * @method \string[] getNewTopicOnlyList()
	 * @method \string[] fillNewTopicOnly()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \int[] getSocnetGroupIdList()
	 * @method \int[] fillSocnetGroupId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Forum\EO_Subscribe_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Forum\EO_User[] getForumUserList()
	 * @method \Bitrix\Forum\EO_Subscribe_Collection getForumUserCollection()
	 * @method \Bitrix\Forum\EO_User_Collection fillForumUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_Subscribe $object)
	 * @method bool has(\Bitrix\Forum\EO_Subscribe $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Subscribe getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Subscribe[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_Subscribe $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_Subscribe_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_Subscribe current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Subscribe_Collection merge(?EO_Subscribe_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Subscribe_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\SubscribeTable */
		static public $dataClass = '\Bitrix\Forum\SubscribeTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Subscribe_Result exec()
	 * @method \Bitrix\Forum\EO_Subscribe fetchObject()
	 * @method \Bitrix\Forum\EO_Subscribe_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Subscribe_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_Subscribe fetchObject()
	 * @method \Bitrix\Forum\EO_Subscribe_Collection fetchCollection()
	 */
	class EO_Subscribe_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_Subscribe createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_Subscribe_Collection createCollection()
	 * @method \Bitrix\Forum\EO_Subscribe wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_Subscribe_Collection wakeUpCollection($rows)
	 */
	class EO_Subscribe_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\FileTable:forum/lib/file.php */
namespace Bitrix\Forum {
	/**
	 * EO_File
	 * @see \Bitrix\Forum\FileTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getForumId()
	 * @method \Bitrix\Forum\EO_File setForumId(\int|\Bitrix\Main\DB\SqlExpression $forumId)
	 * @method bool hasForumId()
	 * @method bool isForumIdFilled()
	 * @method bool isForumIdChanged()
	 * @method \int remindActualForumId()
	 * @method \int requireForumId()
	 * @method \Bitrix\Forum\EO_File resetForumId()
	 * @method \Bitrix\Forum\EO_File unsetForumId()
	 * @method \int fillForumId()
	 * @method \int getTopicId()
	 * @method \Bitrix\Forum\EO_File setTopicId(\int|\Bitrix\Main\DB\SqlExpression $topicId)
	 * @method bool hasTopicId()
	 * @method bool isTopicIdFilled()
	 * @method bool isTopicIdChanged()
	 * @method \int remindActualTopicId()
	 * @method \int requireTopicId()
	 * @method \Bitrix\Forum\EO_File resetTopicId()
	 * @method \Bitrix\Forum\EO_File unsetTopicId()
	 * @method \int fillTopicId()
	 * @method \int getMessageId()
	 * @method \Bitrix\Forum\EO_File setMessageId(\int|\Bitrix\Main\DB\SqlExpression $messageId)
	 * @method bool hasMessageId()
	 * @method bool isMessageIdFilled()
	 * @method bool isMessageIdChanged()
	 * @method \int remindActualMessageId()
	 * @method \int requireMessageId()
	 * @method \Bitrix\Forum\EO_File resetMessageId()
	 * @method \Bitrix\Forum\EO_File unsetMessageId()
	 * @method \int fillMessageId()
	 * @method \int getFileId()
	 * @method \Bitrix\Forum\EO_File setFileId(\int|\Bitrix\Main\DB\SqlExpression $fileId)
	 * @method bool hasFileId()
	 * @method bool isFileIdFilled()
	 * @method bool isFileIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Forum\EO_File setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Forum\EO_File resetUserId()
	 * @method \Bitrix\Forum\EO_File unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Forum\EO_File setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Forum\EO_File resetUser()
	 * @method \Bitrix\Forum\EO_File unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Forum\EO_File setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Forum\EO_File resetTimestampX()
	 * @method \Bitrix\Forum\EO_File unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \int getHits()
	 * @method \Bitrix\Forum\EO_File setHits(\int|\Bitrix\Main\DB\SqlExpression $hits)
	 * @method bool hasHits()
	 * @method bool isHitsFilled()
	 * @method bool isHitsChanged()
	 * @method \int remindActualHits()
	 * @method \int requireHits()
	 * @method \Bitrix\Forum\EO_File resetHits()
	 * @method \Bitrix\Forum\EO_File unsetHits()
	 * @method \int fillHits()
	 * @method \Bitrix\Forum\EO_Forum getForum()
	 * @method \Bitrix\Forum\EO_Forum remindActualForum()
	 * @method \Bitrix\Forum\EO_Forum requireForum()
	 * @method \Bitrix\Forum\EO_File setForum(\Bitrix\Forum\EO_Forum $object)
	 * @method \Bitrix\Forum\EO_File resetForum()
	 * @method \Bitrix\Forum\EO_File unsetForum()
	 * @method bool hasForum()
	 * @method bool isForumFilled()
	 * @method bool isForumChanged()
	 * @method \Bitrix\Forum\EO_Forum fillForum()
	 * @method \Bitrix\Main\EO_File getFile()
	 * @method \Bitrix\Main\EO_File remindActualFile()
	 * @method \Bitrix\Main\EO_File requireFile()
	 * @method \Bitrix\Forum\EO_File setFile(\Bitrix\Main\EO_File $object)
	 * @method \Bitrix\Forum\EO_File resetFile()
	 * @method \Bitrix\Forum\EO_File unsetFile()
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
	 * @method \Bitrix\Forum\EO_File set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_File reset($fieldName)
	 * @method \Bitrix\Forum\EO_File unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_File wakeUp($data)
	 */
	class EO_File {
		/* @var \Bitrix\Forum\FileTable */
		static public $dataClass = '\Bitrix\Forum\FileTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_File_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getForumIdList()
	 * @method \int[] fillForumId()
	 * @method \int[] getTopicIdList()
	 * @method \int[] fillTopicId()
	 * @method \int[] getMessageIdList()
	 * @method \int[] fillMessageId()
	 * @method \int[] getFileIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Forum\EO_File_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \int[] getHitsList()
	 * @method \int[] fillHits()
	 * @method \Bitrix\Forum\EO_Forum[] getForumList()
	 * @method \Bitrix\Forum\EO_File_Collection getForumCollection()
	 * @method \Bitrix\Forum\EO_Forum_Collection fillForum()
	 * @method \Bitrix\Main\EO_File[] getFileList()
	 * @method \Bitrix\Forum\EO_File_Collection getFileCollection()
	 * @method \Bitrix\Main\EO_File_Collection fillFile()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_File $object)
	 * @method bool has(\Bitrix\Forum\EO_File $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_File getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_File[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_File $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_File_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_File current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_File_Collection merge(?EO_File_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_File_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\FileTable */
		static public $dataClass = '\Bitrix\Forum\FileTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_File_Result exec()
	 * @method \Bitrix\Forum\EO_File fetchObject()
	 * @method \Bitrix\Forum\EO_File_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_File_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_File fetchObject()
	 * @method \Bitrix\Forum\EO_File_Collection fetchCollection()
	 */
	class EO_File_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_File createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_File_Collection createCollection()
	 * @method \Bitrix\Forum\EO_File wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_File_Collection wakeUpCollection($rows)
	 */
	class EO_File_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\UserTable:forum/lib/user.php */
namespace Bitrix\Forum {
	/**
	 * EO_User
	 * @see \Bitrix\Forum\UserTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_User setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Forum\EO_User setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Forum\EO_User resetUserId()
	 * @method \Bitrix\Forum\EO_User unsetUserId()
	 * @method \int fillUserId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Forum\EO_User setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Forum\EO_User resetUser()
	 * @method \Bitrix\Forum\EO_User unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \string getDescription()
	 * @method \Bitrix\Forum\EO_User setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Forum\EO_User resetDescription()
	 * @method \Bitrix\Forum\EO_User unsetDescription()
	 * @method \string fillDescription()
	 * @method \int getAvatar()
	 * @method \Bitrix\Forum\EO_User setAvatar(\int|\Bitrix\Main\DB\SqlExpression $avatar)
	 * @method bool hasAvatar()
	 * @method bool isAvatarFilled()
	 * @method bool isAvatarChanged()
	 * @method \int remindActualAvatar()
	 * @method \int requireAvatar()
	 * @method \Bitrix\Forum\EO_User resetAvatar()
	 * @method \Bitrix\Forum\EO_User unsetAvatar()
	 * @method \int fillAvatar()
	 * @method \int getPoints()
	 * @method \Bitrix\Forum\EO_User setPoints(\int|\Bitrix\Main\DB\SqlExpression $points)
	 * @method bool hasPoints()
	 * @method bool isPointsFilled()
	 * @method bool isPointsChanged()
	 * @method \int remindActualPoints()
	 * @method \int requirePoints()
	 * @method \Bitrix\Forum\EO_User resetPoints()
	 * @method \Bitrix\Forum\EO_User unsetPoints()
	 * @method \int fillPoints()
	 * @method \int getRankId()
	 * @method \Bitrix\Forum\EO_User setRankId(\int|\Bitrix\Main\DB\SqlExpression $rankId)
	 * @method bool hasRankId()
	 * @method bool isRankIdFilled()
	 * @method bool isRankIdChanged()
	 * @method \int remindActualRankId()
	 * @method \int requireRankId()
	 * @method \Bitrix\Forum\EO_User resetRankId()
	 * @method \Bitrix\Forum\EO_User unsetRankId()
	 * @method \int fillRankId()
	 * @method \int getNumPosts()
	 * @method \Bitrix\Forum\EO_User setNumPosts(\int|\Bitrix\Main\DB\SqlExpression $numPosts)
	 * @method bool hasNumPosts()
	 * @method bool isNumPostsFilled()
	 * @method bool isNumPostsChanged()
	 * @method \int remindActualNumPosts()
	 * @method \int requireNumPosts()
	 * @method \Bitrix\Forum\EO_User resetNumPosts()
	 * @method \Bitrix\Forum\EO_User unsetNumPosts()
	 * @method \int fillNumPosts()
	 * @method \string getInterests()
	 * @method \Bitrix\Forum\EO_User setInterests(\string|\Bitrix\Main\DB\SqlExpression $interests)
	 * @method bool hasInterests()
	 * @method bool isInterestsFilled()
	 * @method bool isInterestsChanged()
	 * @method \string remindActualInterests()
	 * @method \string requireInterests()
	 * @method \Bitrix\Forum\EO_User resetInterests()
	 * @method \Bitrix\Forum\EO_User unsetInterests()
	 * @method \string fillInterests()
	 * @method \int getLastPost()
	 * @method \Bitrix\Forum\EO_User setLastPost(\int|\Bitrix\Main\DB\SqlExpression $lastPost)
	 * @method bool hasLastPost()
	 * @method bool isLastPostFilled()
	 * @method bool isLastPostChanged()
	 * @method \int remindActualLastPost()
	 * @method \int requireLastPost()
	 * @method \Bitrix\Forum\EO_User resetLastPost()
	 * @method \Bitrix\Forum\EO_User unsetLastPost()
	 * @method \int fillLastPost()
	 * @method \string getSignature()
	 * @method \Bitrix\Forum\EO_User setSignature(\string|\Bitrix\Main\DB\SqlExpression $signature)
	 * @method bool hasSignature()
	 * @method bool isSignatureFilled()
	 * @method bool isSignatureChanged()
	 * @method \string remindActualSignature()
	 * @method \string requireSignature()
	 * @method \Bitrix\Forum\EO_User resetSignature()
	 * @method \Bitrix\Forum\EO_User unsetSignature()
	 * @method \string fillSignature()
	 * @method \string getIpAddress()
	 * @method \Bitrix\Forum\EO_User setIpAddress(\string|\Bitrix\Main\DB\SqlExpression $ipAddress)
	 * @method bool hasIpAddress()
	 * @method bool isIpAddressFilled()
	 * @method bool isIpAddressChanged()
	 * @method \string remindActualIpAddress()
	 * @method \string requireIpAddress()
	 * @method \Bitrix\Forum\EO_User resetIpAddress()
	 * @method \Bitrix\Forum\EO_User unsetIpAddress()
	 * @method \string fillIpAddress()
	 * @method \string getRealIpAddress()
	 * @method \Bitrix\Forum\EO_User setRealIpAddress(\string|\Bitrix\Main\DB\SqlExpression $realIpAddress)
	 * @method bool hasRealIpAddress()
	 * @method bool isRealIpAddressFilled()
	 * @method bool isRealIpAddressChanged()
	 * @method \string remindActualRealIpAddress()
	 * @method \string requireRealIpAddress()
	 * @method \Bitrix\Forum\EO_User resetRealIpAddress()
	 * @method \Bitrix\Forum\EO_User unsetRealIpAddress()
	 * @method \string fillRealIpAddress()
	 * @method \Bitrix\Main\Type\DateTime getDateReg()
	 * @method \Bitrix\Forum\EO_User setDateReg(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateReg)
	 * @method bool hasDateReg()
	 * @method bool isDateRegFilled()
	 * @method bool isDateRegChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateReg()
	 * @method \Bitrix\Main\Type\DateTime requireDateReg()
	 * @method \Bitrix\Forum\EO_User resetDateReg()
	 * @method \Bitrix\Forum\EO_User unsetDateReg()
	 * @method \Bitrix\Main\Type\DateTime fillDateReg()
	 * @method \Bitrix\Main\Type\DateTime getLastVisit()
	 * @method \Bitrix\Forum\EO_User setLastVisit(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastVisit)
	 * @method bool hasLastVisit()
	 * @method bool isLastVisitFilled()
	 * @method bool isLastVisitChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastVisit()
	 * @method \Bitrix\Main\Type\DateTime requireLastVisit()
	 * @method \Bitrix\Forum\EO_User resetLastVisit()
	 * @method \Bitrix\Forum\EO_User unsetLastVisit()
	 * @method \Bitrix\Main\Type\DateTime fillLastVisit()
	 * @method \boolean getAllowPost()
	 * @method \Bitrix\Forum\EO_User setAllowPost(\boolean|\Bitrix\Main\DB\SqlExpression $allowPost)
	 * @method bool hasAllowPost()
	 * @method bool isAllowPostFilled()
	 * @method bool isAllowPostChanged()
	 * @method \boolean remindActualAllowPost()
	 * @method \boolean requireAllowPost()
	 * @method \Bitrix\Forum\EO_User resetAllowPost()
	 * @method \Bitrix\Forum\EO_User unsetAllowPost()
	 * @method \boolean fillAllowPost()
	 * @method \boolean getShowName()
	 * @method \Bitrix\Forum\EO_User setShowName(\boolean|\Bitrix\Main\DB\SqlExpression $showName)
	 * @method bool hasShowName()
	 * @method bool isShowNameFilled()
	 * @method bool isShowNameChanged()
	 * @method \boolean remindActualShowName()
	 * @method \boolean requireShowName()
	 * @method \Bitrix\Forum\EO_User resetShowName()
	 * @method \Bitrix\Forum\EO_User unsetShowName()
	 * @method \boolean fillShowName()
	 * @method \boolean getHideFromOnline()
	 * @method \Bitrix\Forum\EO_User setHideFromOnline(\boolean|\Bitrix\Main\DB\SqlExpression $hideFromOnline)
	 * @method bool hasHideFromOnline()
	 * @method bool isHideFromOnlineFilled()
	 * @method bool isHideFromOnlineChanged()
	 * @method \boolean remindActualHideFromOnline()
	 * @method \boolean requireHideFromOnline()
	 * @method \Bitrix\Forum\EO_User resetHideFromOnline()
	 * @method \Bitrix\Forum\EO_User unsetHideFromOnline()
	 * @method \boolean fillHideFromOnline()
	 * @method \boolean getSubscGroupMessage()
	 * @method \Bitrix\Forum\EO_User setSubscGroupMessage(\boolean|\Bitrix\Main\DB\SqlExpression $subscGroupMessage)
	 * @method bool hasSubscGroupMessage()
	 * @method bool isSubscGroupMessageFilled()
	 * @method bool isSubscGroupMessageChanged()
	 * @method \boolean remindActualSubscGroupMessage()
	 * @method \boolean requireSubscGroupMessage()
	 * @method \Bitrix\Forum\EO_User resetSubscGroupMessage()
	 * @method \Bitrix\Forum\EO_User unsetSubscGroupMessage()
	 * @method \boolean fillSubscGroupMessage()
	 * @method \boolean getSubscGetMyMessage()
	 * @method \Bitrix\Forum\EO_User setSubscGetMyMessage(\boolean|\Bitrix\Main\DB\SqlExpression $subscGetMyMessage)
	 * @method bool hasSubscGetMyMessage()
	 * @method bool isSubscGetMyMessageFilled()
	 * @method bool isSubscGetMyMessageChanged()
	 * @method \boolean remindActualSubscGetMyMessage()
	 * @method \boolean requireSubscGetMyMessage()
	 * @method \Bitrix\Forum\EO_User resetSubscGetMyMessage()
	 * @method \Bitrix\Forum\EO_User unsetSubscGetMyMessage()
	 * @method \boolean fillSubscGetMyMessage()
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
	 * @method \Bitrix\Forum\EO_User set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_User reset($fieldName)
	 * @method \Bitrix\Forum\EO_User unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_User wakeUp($data)
	 */
	class EO_User {
		/* @var \Bitrix\Forum\UserTable */
		static public $dataClass = '\Bitrix\Forum\UserTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_User_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Forum\EO_User_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \int[] getAvatarList()
	 * @method \int[] fillAvatar()
	 * @method \int[] getPointsList()
	 * @method \int[] fillPoints()
	 * @method \int[] getRankIdList()
	 * @method \int[] fillRankId()
	 * @method \int[] getNumPostsList()
	 * @method \int[] fillNumPosts()
	 * @method \string[] getInterestsList()
	 * @method \string[] fillInterests()
	 * @method \int[] getLastPostList()
	 * @method \int[] fillLastPost()
	 * @method \string[] getSignatureList()
	 * @method \string[] fillSignature()
	 * @method \string[] getIpAddressList()
	 * @method \string[] fillIpAddress()
	 * @method \string[] getRealIpAddressList()
	 * @method \string[] fillRealIpAddress()
	 * @method \Bitrix\Main\Type\DateTime[] getDateRegList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateReg()
	 * @method \Bitrix\Main\Type\DateTime[] getLastVisitList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastVisit()
	 * @method \boolean[] getAllowPostList()
	 * @method \boolean[] fillAllowPost()
	 * @method \boolean[] getShowNameList()
	 * @method \boolean[] fillShowName()
	 * @method \boolean[] getHideFromOnlineList()
	 * @method \boolean[] fillHideFromOnline()
	 * @method \boolean[] getSubscGroupMessageList()
	 * @method \boolean[] fillSubscGroupMessage()
	 * @method \boolean[] getSubscGetMyMessageList()
	 * @method \boolean[] fillSubscGetMyMessage()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_User $object)
	 * @method bool has(\Bitrix\Forum\EO_User $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_User getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_User[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_User $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_User_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_User current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_User_Collection merge(?EO_User_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_User_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\UserTable */
		static public $dataClass = '\Bitrix\Forum\UserTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_User_Result exec()
	 * @method \Bitrix\Forum\EO_User fetchObject()
	 * @method \Bitrix\Forum\EO_User_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_User_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_User fetchObject()
	 * @method \Bitrix\Forum\EO_User_Collection fetchCollection()
	 */
	class EO_User_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_User createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_User_Collection createCollection()
	 * @method \Bitrix\Forum\EO_User wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_User_Collection wakeUpCollection($rows)
	 */
	class EO_User_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\BadWords\DictionaryTable:forum/lib/badwords/dictionary.php */
namespace Bitrix\Forum\BadWords {
	/**
	 * EO_Dictionary
	 * @see \Bitrix\Forum\BadWords\DictionaryTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getTitle()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary resetTitle()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getType()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary resetType()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary unsetType()
	 * @method \string fillType()
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
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary set($fieldName, $value)
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary reset($fieldName)
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\BadWords\EO_Dictionary wakeUp($data)
	 */
	class EO_Dictionary {
		/* @var \Bitrix\Forum\BadWords\DictionaryTable */
		static public $dataClass = '\Bitrix\Forum\BadWords\DictionaryTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum\BadWords {
	/**
	 * EO_Dictionary_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\BadWords\EO_Dictionary $object)
	 * @method bool has(\Bitrix\Forum\BadWords\EO_Dictionary $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary getByPrimary($primary)
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary[] getAll()
	 * @method bool remove(\Bitrix\Forum\BadWords\EO_Dictionary $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\BadWords\EO_Dictionary_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Dictionary_Collection merge(?EO_Dictionary_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Dictionary_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\BadWords\DictionaryTable */
		static public $dataClass = '\Bitrix\Forum\BadWords\DictionaryTable';
	}
}
namespace Bitrix\Forum\BadWords {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Dictionary_Result exec()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary fetchObject()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Dictionary_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary fetchObject()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary_Collection fetchCollection()
	 */
	class EO_Dictionary_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary_Collection createCollection()
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary wakeUpObject($row)
	 * @method \Bitrix\Forum\BadWords\EO_Dictionary_Collection wakeUpCollection($rows)
	 */
	class EO_Dictionary_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\BadWords\LetterTable:forum/lib/badwords/letter.php */
namespace Bitrix\Forum\BadWords {
	/**
	 * EO_Letter
	 * @see \Bitrix\Forum\BadWords\LetterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\BadWords\EO_Letter setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDictionaryId()
	 * @method \Bitrix\Forum\BadWords\EO_Letter setDictionaryId(\int|\Bitrix\Main\DB\SqlExpression $dictionaryId)
	 * @method bool hasDictionaryId()
	 * @method bool isDictionaryIdFilled()
	 * @method bool isDictionaryIdChanged()
	 * @method \int remindActualDictionaryId()
	 * @method \int requireDictionaryId()
	 * @method \Bitrix\Forum\BadWords\EO_Letter resetDictionaryId()
	 * @method \Bitrix\Forum\BadWords\EO_Letter unsetDictionaryId()
	 * @method \int fillDictionaryId()
	 * @method \string getLetter()
	 * @method \Bitrix\Forum\BadWords\EO_Letter setLetter(\string|\Bitrix\Main\DB\SqlExpression $letter)
	 * @method bool hasLetter()
	 * @method bool isLetterFilled()
	 * @method bool isLetterChanged()
	 * @method \string remindActualLetter()
	 * @method \string requireLetter()
	 * @method \Bitrix\Forum\BadWords\EO_Letter resetLetter()
	 * @method \Bitrix\Forum\BadWords\EO_Letter unsetLetter()
	 * @method \string fillLetter()
	 * @method \string getReplacement()
	 * @method \Bitrix\Forum\BadWords\EO_Letter setReplacement(\string|\Bitrix\Main\DB\SqlExpression $replacement)
	 * @method bool hasReplacement()
	 * @method bool isReplacementFilled()
	 * @method bool isReplacementChanged()
	 * @method \string remindActualReplacement()
	 * @method \string requireReplacement()
	 * @method \Bitrix\Forum\BadWords\EO_Letter resetReplacement()
	 * @method \Bitrix\Forum\BadWords\EO_Letter unsetReplacement()
	 * @method \string fillReplacement()
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
	 * @method \Bitrix\Forum\BadWords\EO_Letter set($fieldName, $value)
	 * @method \Bitrix\Forum\BadWords\EO_Letter reset($fieldName)
	 * @method \Bitrix\Forum\BadWords\EO_Letter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\BadWords\EO_Letter wakeUp($data)
	 */
	class EO_Letter {
		/* @var \Bitrix\Forum\BadWords\LetterTable */
		static public $dataClass = '\Bitrix\Forum\BadWords\LetterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum\BadWords {
	/**
	 * EO_Letter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDictionaryIdList()
	 * @method \int[] fillDictionaryId()
	 * @method \string[] getLetterList()
	 * @method \string[] fillLetter()
	 * @method \string[] getReplacementList()
	 * @method \string[] fillReplacement()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\BadWords\EO_Letter $object)
	 * @method bool has(\Bitrix\Forum\BadWords\EO_Letter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\BadWords\EO_Letter getByPrimary($primary)
	 * @method \Bitrix\Forum\BadWords\EO_Letter[] getAll()
	 * @method bool remove(\Bitrix\Forum\BadWords\EO_Letter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\BadWords\EO_Letter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\BadWords\EO_Letter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Letter_Collection merge(?EO_Letter_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Letter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\BadWords\LetterTable */
		static public $dataClass = '\Bitrix\Forum\BadWords\LetterTable';
	}
}
namespace Bitrix\Forum\BadWords {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Letter_Result exec()
	 * @method \Bitrix\Forum\BadWords\EO_Letter fetchObject()
	 * @method \Bitrix\Forum\BadWords\EO_Letter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Letter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\BadWords\EO_Letter fetchObject()
	 * @method \Bitrix\Forum\BadWords\EO_Letter_Collection fetchCollection()
	 */
	class EO_Letter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\BadWords\EO_Letter createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\BadWords\EO_Letter_Collection createCollection()
	 * @method \Bitrix\Forum\BadWords\EO_Letter wakeUpObject($row)
	 * @method \Bitrix\Forum\BadWords\EO_Letter_Collection wakeUpCollection($rows)
	 */
	class EO_Letter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\BadWords\FilterTable:forum/lib/badwords/filter.php */
namespace Bitrix\Forum\BadWords {
	/**
	 * EO_Filter
	 * @see \Bitrix\Forum\BadWords\FilterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\BadWords\EO_Filter setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getDictionaryId()
	 * @method \Bitrix\Forum\BadWords\EO_Filter setDictionaryId(\int|\Bitrix\Main\DB\SqlExpression $dictionaryId)
	 * @method bool hasDictionaryId()
	 * @method bool isDictionaryIdFilled()
	 * @method bool isDictionaryIdChanged()
	 * @method \int remindActualDictionaryId()
	 * @method \int requireDictionaryId()
	 * @method \Bitrix\Forum\BadWords\EO_Filter resetDictionaryId()
	 * @method \Bitrix\Forum\BadWords\EO_Filter unsetDictionaryId()
	 * @method \int fillDictionaryId()
	 * @method \string getWords()
	 * @method \Bitrix\Forum\BadWords\EO_Filter setWords(\string|\Bitrix\Main\DB\SqlExpression $words)
	 * @method bool hasWords()
	 * @method bool isWordsFilled()
	 * @method bool isWordsChanged()
	 * @method \string remindActualWords()
	 * @method \string requireWords()
	 * @method \Bitrix\Forum\BadWords\EO_Filter resetWords()
	 * @method \Bitrix\Forum\BadWords\EO_Filter unsetWords()
	 * @method \string fillWords()
	 * @method \string getPattern()
	 * @method \Bitrix\Forum\BadWords\EO_Filter setPattern(\string|\Bitrix\Main\DB\SqlExpression $pattern)
	 * @method bool hasPattern()
	 * @method bool isPatternFilled()
	 * @method bool isPatternChanged()
	 * @method \string remindActualPattern()
	 * @method \string requirePattern()
	 * @method \Bitrix\Forum\BadWords\EO_Filter resetPattern()
	 * @method \Bitrix\Forum\BadWords\EO_Filter unsetPattern()
	 * @method \string fillPattern()
	 * @method \string getPatternCreate()
	 * @method \Bitrix\Forum\BadWords\EO_Filter setPatternCreate(\string|\Bitrix\Main\DB\SqlExpression $patternCreate)
	 * @method bool hasPatternCreate()
	 * @method bool isPatternCreateFilled()
	 * @method bool isPatternCreateChanged()
	 * @method \string remindActualPatternCreate()
	 * @method \string requirePatternCreate()
	 * @method \Bitrix\Forum\BadWords\EO_Filter resetPatternCreate()
	 * @method \Bitrix\Forum\BadWords\EO_Filter unsetPatternCreate()
	 * @method \string fillPatternCreate()
	 * @method \string getReplacement()
	 * @method \Bitrix\Forum\BadWords\EO_Filter setReplacement(\string|\Bitrix\Main\DB\SqlExpression $replacement)
	 * @method bool hasReplacement()
	 * @method bool isReplacementFilled()
	 * @method bool isReplacementChanged()
	 * @method \string remindActualReplacement()
	 * @method \string requireReplacement()
	 * @method \Bitrix\Forum\BadWords\EO_Filter resetReplacement()
	 * @method \Bitrix\Forum\BadWords\EO_Filter unsetReplacement()
	 * @method \string fillReplacement()
	 * @method \string getDescription()
	 * @method \Bitrix\Forum\BadWords\EO_Filter setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Forum\BadWords\EO_Filter resetDescription()
	 * @method \Bitrix\Forum\BadWords\EO_Filter unsetDescription()
	 * @method \string fillDescription()
	 * @method \boolean getUseIt()
	 * @method \Bitrix\Forum\BadWords\EO_Filter setUseIt(\boolean|\Bitrix\Main\DB\SqlExpression $useIt)
	 * @method bool hasUseIt()
	 * @method bool isUseItFilled()
	 * @method bool isUseItChanged()
	 * @method \boolean remindActualUseIt()
	 * @method \boolean requireUseIt()
	 * @method \Bitrix\Forum\BadWords\EO_Filter resetUseIt()
	 * @method \Bitrix\Forum\BadWords\EO_Filter unsetUseIt()
	 * @method \boolean fillUseIt()
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
	 * @method \Bitrix\Forum\BadWords\EO_Filter set($fieldName, $value)
	 * @method \Bitrix\Forum\BadWords\EO_Filter reset($fieldName)
	 * @method \Bitrix\Forum\BadWords\EO_Filter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\BadWords\EO_Filter wakeUp($data)
	 */
	class EO_Filter {
		/* @var \Bitrix\Forum\BadWords\FilterTable */
		static public $dataClass = '\Bitrix\Forum\BadWords\FilterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum\BadWords {
	/**
	 * EO_Filter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getDictionaryIdList()
	 * @method \int[] fillDictionaryId()
	 * @method \string[] getWordsList()
	 * @method \string[] fillWords()
	 * @method \string[] getPatternList()
	 * @method \string[] fillPattern()
	 * @method \string[] getPatternCreateList()
	 * @method \string[] fillPatternCreate()
	 * @method \string[] getReplacementList()
	 * @method \string[] fillReplacement()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \boolean[] getUseItList()
	 * @method \boolean[] fillUseIt()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\BadWords\EO_Filter $object)
	 * @method bool has(\Bitrix\Forum\BadWords\EO_Filter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\BadWords\EO_Filter getByPrimary($primary)
	 * @method \Bitrix\Forum\BadWords\EO_Filter[] getAll()
	 * @method bool remove(\Bitrix\Forum\BadWords\EO_Filter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\BadWords\EO_Filter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\BadWords\EO_Filter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Filter_Collection merge(?EO_Filter_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Filter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\BadWords\FilterTable */
		static public $dataClass = '\Bitrix\Forum\BadWords\FilterTable';
	}
}
namespace Bitrix\Forum\BadWords {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Filter_Result exec()
	 * @method \Bitrix\Forum\BadWords\EO_Filter fetchObject()
	 * @method \Bitrix\Forum\BadWords\EO_Filter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Filter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\BadWords\EO_Filter fetchObject()
	 * @method \Bitrix\Forum\BadWords\EO_Filter_Collection fetchCollection()
	 */
	class EO_Filter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\BadWords\EO_Filter createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\BadWords\EO_Filter_Collection createCollection()
	 * @method \Bitrix\Forum\BadWords\EO_Filter wakeUpObject($row)
	 * @method \Bitrix\Forum\BadWords\EO_Filter_Collection wakeUpCollection($rows)
	 */
	class EO_Filter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\ForumStatTable:forum/lib/forumstat.php */
namespace Bitrix\Forum {
	/**
	 * EO_ForumStat
	 * @see \Bitrix\Forum\ForumStatTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_ForumStat setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Forum\EO_ForumStat setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Forum\EO_ForumStat resetUserId()
	 * @method \Bitrix\Forum\EO_ForumStat unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getIpAddress()
	 * @method \Bitrix\Forum\EO_ForumStat setIpAddress(\string|\Bitrix\Main\DB\SqlExpression $ipAddress)
	 * @method bool hasIpAddress()
	 * @method bool isIpAddressFilled()
	 * @method bool isIpAddressChanged()
	 * @method \string remindActualIpAddress()
	 * @method \string requireIpAddress()
	 * @method \Bitrix\Forum\EO_ForumStat resetIpAddress()
	 * @method \Bitrix\Forum\EO_ForumStat unsetIpAddress()
	 * @method \string fillIpAddress()
	 * @method \string getPhpsessid()
	 * @method \Bitrix\Forum\EO_ForumStat setPhpsessid(\string|\Bitrix\Main\DB\SqlExpression $phpsessid)
	 * @method bool hasPhpsessid()
	 * @method bool isPhpsessidFilled()
	 * @method bool isPhpsessidChanged()
	 * @method \string remindActualPhpsessid()
	 * @method \string requirePhpsessid()
	 * @method \Bitrix\Forum\EO_ForumStat resetPhpsessid()
	 * @method \Bitrix\Forum\EO_ForumStat unsetPhpsessid()
	 * @method \string fillPhpsessid()
	 * @method \Bitrix\Main\Type\DateTime getLastVisit()
	 * @method \Bitrix\Forum\EO_ForumStat setLastVisit(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastVisit)
	 * @method bool hasLastVisit()
	 * @method bool isLastVisitFilled()
	 * @method bool isLastVisitChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastVisit()
	 * @method \Bitrix\Main\Type\DateTime requireLastVisit()
	 * @method \Bitrix\Forum\EO_ForumStat resetLastVisit()
	 * @method \Bitrix\Forum\EO_ForumStat unsetLastVisit()
	 * @method \Bitrix\Main\Type\DateTime fillLastVisit()
	 * @method \string getSiteId()
	 * @method \Bitrix\Forum\EO_ForumStat setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string remindActualSiteId()
	 * @method \string requireSiteId()
	 * @method \Bitrix\Forum\EO_ForumStat resetSiteId()
	 * @method \Bitrix\Forum\EO_ForumStat unsetSiteId()
	 * @method \string fillSiteId()
	 * @method \int getForumId()
	 * @method \Bitrix\Forum\EO_ForumStat setForumId(\int|\Bitrix\Main\DB\SqlExpression $forumId)
	 * @method bool hasForumId()
	 * @method bool isForumIdFilled()
	 * @method bool isForumIdChanged()
	 * @method \int remindActualForumId()
	 * @method \int requireForumId()
	 * @method \Bitrix\Forum\EO_ForumStat resetForumId()
	 * @method \Bitrix\Forum\EO_ForumStat unsetForumId()
	 * @method \int fillForumId()
	 * @method \int getTopicId()
	 * @method \Bitrix\Forum\EO_ForumStat setTopicId(\int|\Bitrix\Main\DB\SqlExpression $topicId)
	 * @method bool hasTopicId()
	 * @method bool isTopicIdFilled()
	 * @method bool isTopicIdChanged()
	 * @method \int remindActualTopicId()
	 * @method \int requireTopicId()
	 * @method \Bitrix\Forum\EO_ForumStat resetTopicId()
	 * @method \Bitrix\Forum\EO_ForumStat unsetTopicId()
	 * @method \int fillTopicId()
	 * @method \string getShowName()
	 * @method \Bitrix\Forum\EO_ForumStat setShowName(\string|\Bitrix\Main\DB\SqlExpression $showName)
	 * @method bool hasShowName()
	 * @method bool isShowNameFilled()
	 * @method bool isShowNameChanged()
	 * @method \string remindActualShowName()
	 * @method \string requireShowName()
	 * @method \Bitrix\Forum\EO_ForumStat resetShowName()
	 * @method \Bitrix\Forum\EO_ForumStat unsetShowName()
	 * @method \string fillShowName()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Forum\EO_ForumStat setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Forum\EO_ForumStat resetUser()
	 * @method \Bitrix\Forum\EO_ForumStat unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Forum\EO_User getForumUser()
	 * @method \Bitrix\Forum\EO_User remindActualForumUser()
	 * @method \Bitrix\Forum\EO_User requireForumUser()
	 * @method \Bitrix\Forum\EO_ForumStat setForumUser(\Bitrix\Forum\EO_User $object)
	 * @method \Bitrix\Forum\EO_ForumStat resetForumUser()
	 * @method \Bitrix\Forum\EO_ForumStat unsetForumUser()
	 * @method bool hasForumUser()
	 * @method bool isForumUserFilled()
	 * @method bool isForumUserChanged()
	 * @method \Bitrix\Forum\EO_User fillForumUser()
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
	 * @method \Bitrix\Forum\EO_ForumStat set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_ForumStat reset($fieldName)
	 * @method \Bitrix\Forum\EO_ForumStat unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_ForumStat wakeUp($data)
	 */
	class EO_ForumStat {
		/* @var \Bitrix\Forum\ForumStatTable */
		static public $dataClass = '\Bitrix\Forum\ForumStatTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_ForumStat_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getIpAddressList()
	 * @method \string[] fillIpAddress()
	 * @method \string[] getPhpsessidList()
	 * @method \string[] fillPhpsessid()
	 * @method \Bitrix\Main\Type\DateTime[] getLastVisitList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastVisit()
	 * @method \string[] getSiteIdList()
	 * @method \string[] fillSiteId()
	 * @method \int[] getForumIdList()
	 * @method \int[] fillForumId()
	 * @method \int[] getTopicIdList()
	 * @method \int[] fillTopicId()
	 * @method \string[] getShowNameList()
	 * @method \string[] fillShowName()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Forum\EO_ForumStat_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Forum\EO_User[] getForumUserList()
	 * @method \Bitrix\Forum\EO_ForumStat_Collection getForumUserCollection()
	 * @method \Bitrix\Forum\EO_User_Collection fillForumUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_ForumStat $object)
	 * @method bool has(\Bitrix\Forum\EO_ForumStat $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_ForumStat getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_ForumStat[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_ForumStat $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_ForumStat_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_ForumStat current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_ForumStat_Collection merge(?EO_ForumStat_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_ForumStat_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\ForumStatTable */
		static public $dataClass = '\Bitrix\Forum\ForumStatTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ForumStat_Result exec()
	 * @method \Bitrix\Forum\EO_ForumStat fetchObject()
	 * @method \Bitrix\Forum\EO_ForumStat_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ForumStat_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_ForumStat fetchObject()
	 * @method \Bitrix\Forum\EO_ForumStat_Collection fetchCollection()
	 */
	class EO_ForumStat_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_ForumStat createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_ForumStat_Collection createCollection()
	 * @method \Bitrix\Forum\EO_ForumStat wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_ForumStat_Collection wakeUpCollection($rows)
	 */
	class EO_ForumStat_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\ForumSiteTable:forum/lib/forumsite.php */
namespace Bitrix\Forum {
	/**
	 * EO_ForumSite
	 * @see \Bitrix\Forum\ForumSiteTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getForumId()
	 * @method \Bitrix\Forum\EO_ForumSite setForumId(\int|\Bitrix\Main\DB\SqlExpression $forumId)
	 * @method bool hasForumId()
	 * @method bool isForumIdFilled()
	 * @method bool isForumIdChanged()
	 * @method \string getSiteId()
	 * @method \Bitrix\Forum\EO_ForumSite setSiteId(\string|\Bitrix\Main\DB\SqlExpression $siteId)
	 * @method bool hasSiteId()
	 * @method bool isSiteIdFilled()
	 * @method bool isSiteIdChanged()
	 * @method \string getPath2forumMessage()
	 * @method \Bitrix\Forum\EO_ForumSite setPath2forumMessage(\string|\Bitrix\Main\DB\SqlExpression $path2forumMessage)
	 * @method bool hasPath2forumMessage()
	 * @method bool isPath2forumMessageFilled()
	 * @method bool isPath2forumMessageChanged()
	 * @method \string remindActualPath2forumMessage()
	 * @method \string requirePath2forumMessage()
	 * @method \Bitrix\Forum\EO_ForumSite resetPath2forumMessage()
	 * @method \Bitrix\Forum\EO_ForumSite unsetPath2forumMessage()
	 * @method \string fillPath2forumMessage()
	 * @method \Bitrix\Forum\EO_Forum getForum()
	 * @method \Bitrix\Forum\EO_Forum remindActualForum()
	 * @method \Bitrix\Forum\EO_Forum requireForum()
	 * @method \Bitrix\Forum\EO_ForumSite setForum(\Bitrix\Forum\EO_Forum $object)
	 * @method \Bitrix\Forum\EO_ForumSite resetForum()
	 * @method \Bitrix\Forum\EO_ForumSite unsetForum()
	 * @method bool hasForum()
	 * @method bool isForumFilled()
	 * @method bool isForumChanged()
	 * @method \Bitrix\Forum\EO_Forum fillForum()
	 * @method \Bitrix\Main\EO_Site getSite()
	 * @method \Bitrix\Main\EO_Site remindActualSite()
	 * @method \Bitrix\Main\EO_Site requireSite()
	 * @method \Bitrix\Forum\EO_ForumSite setSite(\Bitrix\Main\EO_Site $object)
	 * @method \Bitrix\Forum\EO_ForumSite resetSite()
	 * @method \Bitrix\Forum\EO_ForumSite unsetSite()
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
	 * @method \Bitrix\Forum\EO_ForumSite set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_ForumSite reset($fieldName)
	 * @method \Bitrix\Forum\EO_ForumSite unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_ForumSite wakeUp($data)
	 */
	class EO_ForumSite {
		/* @var \Bitrix\Forum\ForumSiteTable */
		static public $dataClass = '\Bitrix\Forum\ForumSiteTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_ForumSite_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getForumIdList()
	 * @method \string[] getSiteIdList()
	 * @method \string[] getPath2forumMessageList()
	 * @method \string[] fillPath2forumMessage()
	 * @method \Bitrix\Forum\EO_Forum[] getForumList()
	 * @method \Bitrix\Forum\EO_ForumSite_Collection getForumCollection()
	 * @method \Bitrix\Forum\EO_Forum_Collection fillForum()
	 * @method \Bitrix\Main\EO_Site[] getSiteList()
	 * @method \Bitrix\Forum\EO_ForumSite_Collection getSiteCollection()
	 * @method \Bitrix\Main\EO_Site_Collection fillSite()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_ForumSite $object)
	 * @method bool has(\Bitrix\Forum\EO_ForumSite $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_ForumSite getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_ForumSite[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_ForumSite $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_ForumSite_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_ForumSite current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_ForumSite_Collection merge(?EO_ForumSite_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_ForumSite_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\ForumSiteTable */
		static public $dataClass = '\Bitrix\Forum\ForumSiteTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ForumSite_Result exec()
	 * @method \Bitrix\Forum\EO_ForumSite fetchObject()
	 * @method \Bitrix\Forum\EO_ForumSite_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ForumSite_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_ForumSite fetchObject()
	 * @method \Bitrix\Forum\EO_ForumSite_Collection fetchCollection()
	 */
	class EO_ForumSite_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_ForumSite createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_ForumSite_Collection createCollection()
	 * @method \Bitrix\Forum\EO_ForumSite wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_ForumSite_Collection wakeUpCollection($rows)
	 */
	class EO_ForumSite_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\TopicTable:forum/lib/topic.php */
namespace Bitrix\Forum {
	/**
	 * EO_Topic
	 * @see \Bitrix\Forum\TopicTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_Topic setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getForumId()
	 * @method \Bitrix\Forum\EO_Topic setForumId(\int|\Bitrix\Main\DB\SqlExpression $forumId)
	 * @method bool hasForumId()
	 * @method bool isForumIdFilled()
	 * @method bool isForumIdChanged()
	 * @method \int remindActualForumId()
	 * @method \int requireForumId()
	 * @method \Bitrix\Forum\EO_Topic resetForumId()
	 * @method \Bitrix\Forum\EO_Topic unsetForumId()
	 * @method \int fillForumId()
	 * @method \int getTopicId()
	 * @method \Bitrix\Forum\EO_Topic setTopicId(\int|\Bitrix\Main\DB\SqlExpression $topicId)
	 * @method bool hasTopicId()
	 * @method bool isTopicIdFilled()
	 * @method bool isTopicIdChanged()
	 * @method \int remindActualTopicId()
	 * @method \int requireTopicId()
	 * @method \Bitrix\Forum\EO_Topic resetTopicId()
	 * @method \Bitrix\Forum\EO_Topic unsetTopicId()
	 * @method \int fillTopicId()
	 * @method \string getTitle()
	 * @method \Bitrix\Forum\EO_Topic setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Forum\EO_Topic resetTitle()
	 * @method \Bitrix\Forum\EO_Topic unsetTitle()
	 * @method \string fillTitle()
	 * @method \string getTitleSeo()
	 * @method \Bitrix\Forum\EO_Topic setTitleSeo(\string|\Bitrix\Main\DB\SqlExpression $titleSeo)
	 * @method bool hasTitleSeo()
	 * @method bool isTitleSeoFilled()
	 * @method bool isTitleSeoChanged()
	 * @method \string remindActualTitleSeo()
	 * @method \string requireTitleSeo()
	 * @method \Bitrix\Forum\EO_Topic resetTitleSeo()
	 * @method \Bitrix\Forum\EO_Topic unsetTitleSeo()
	 * @method \string fillTitleSeo()
	 * @method \string getTags()
	 * @method \Bitrix\Forum\EO_Topic setTags(\string|\Bitrix\Main\DB\SqlExpression $tags)
	 * @method bool hasTags()
	 * @method bool isTagsFilled()
	 * @method bool isTagsChanged()
	 * @method \string remindActualTags()
	 * @method \string requireTags()
	 * @method \Bitrix\Forum\EO_Topic resetTags()
	 * @method \Bitrix\Forum\EO_Topic unsetTags()
	 * @method \string fillTags()
	 * @method \string getDescription()
	 * @method \Bitrix\Forum\EO_Topic setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Forum\EO_Topic resetDescription()
	 * @method \Bitrix\Forum\EO_Topic unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getIcon()
	 * @method \Bitrix\Forum\EO_Topic setIcon(\string|\Bitrix\Main\DB\SqlExpression $icon)
	 * @method bool hasIcon()
	 * @method bool isIconFilled()
	 * @method bool isIconChanged()
	 * @method \string remindActualIcon()
	 * @method \string requireIcon()
	 * @method \Bitrix\Forum\EO_Topic resetIcon()
	 * @method \Bitrix\Forum\EO_Topic unsetIcon()
	 * @method \string fillIcon()
	 * @method \string getState()
	 * @method \Bitrix\Forum\EO_Topic setState(\string|\Bitrix\Main\DB\SqlExpression $state)
	 * @method bool hasState()
	 * @method bool isStateFilled()
	 * @method bool isStateChanged()
	 * @method \string remindActualState()
	 * @method \string requireState()
	 * @method \Bitrix\Forum\EO_Topic resetState()
	 * @method \Bitrix\Forum\EO_Topic unsetState()
	 * @method \string fillState()
	 * @method \boolean getApproved()
	 * @method \Bitrix\Forum\EO_Topic setApproved(\boolean|\Bitrix\Main\DB\SqlExpression $approved)
	 * @method bool hasApproved()
	 * @method bool isApprovedFilled()
	 * @method bool isApprovedChanged()
	 * @method \boolean remindActualApproved()
	 * @method \boolean requireApproved()
	 * @method \Bitrix\Forum\EO_Topic resetApproved()
	 * @method \Bitrix\Forum\EO_Topic unsetApproved()
	 * @method \boolean fillApproved()
	 * @method \int getSort()
	 * @method \Bitrix\Forum\EO_Topic setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Forum\EO_Topic resetSort()
	 * @method \Bitrix\Forum\EO_Topic unsetSort()
	 * @method \int fillSort()
	 * @method \int getViews()
	 * @method \Bitrix\Forum\EO_Topic setViews(\int|\Bitrix\Main\DB\SqlExpression $views)
	 * @method bool hasViews()
	 * @method bool isViewsFilled()
	 * @method bool isViewsChanged()
	 * @method \int remindActualViews()
	 * @method \int requireViews()
	 * @method \Bitrix\Forum\EO_Topic resetViews()
	 * @method \Bitrix\Forum\EO_Topic unsetViews()
	 * @method \int fillViews()
	 * @method \int getUserStartId()
	 * @method \Bitrix\Forum\EO_Topic setUserStartId(\int|\Bitrix\Main\DB\SqlExpression $userStartId)
	 * @method bool hasUserStartId()
	 * @method bool isUserStartIdFilled()
	 * @method bool isUserStartIdChanged()
	 * @method \int remindActualUserStartId()
	 * @method \int requireUserStartId()
	 * @method \Bitrix\Forum\EO_Topic resetUserStartId()
	 * @method \Bitrix\Forum\EO_Topic unsetUserStartId()
	 * @method \int fillUserStartId()
	 * @method \string getUserStartName()
	 * @method \Bitrix\Forum\EO_Topic setUserStartName(\string|\Bitrix\Main\DB\SqlExpression $userStartName)
	 * @method bool hasUserStartName()
	 * @method bool isUserStartNameFilled()
	 * @method bool isUserStartNameChanged()
	 * @method \string remindActualUserStartName()
	 * @method \string requireUserStartName()
	 * @method \Bitrix\Forum\EO_Topic resetUserStartName()
	 * @method \Bitrix\Forum\EO_Topic unsetUserStartName()
	 * @method \string fillUserStartName()
	 * @method \Bitrix\Main\Type\DateTime getStartDate()
	 * @method \Bitrix\Forum\EO_Topic setStartDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $startDate)
	 * @method bool hasStartDate()
	 * @method bool isStartDateFilled()
	 * @method bool isStartDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStartDate()
	 * @method \Bitrix\Main\Type\DateTime requireStartDate()
	 * @method \Bitrix\Forum\EO_Topic resetStartDate()
	 * @method \Bitrix\Forum\EO_Topic unsetStartDate()
	 * @method \Bitrix\Main\Type\DateTime fillStartDate()
	 * @method \int getPosts()
	 * @method \Bitrix\Forum\EO_Topic setPosts(\int|\Bitrix\Main\DB\SqlExpression $posts)
	 * @method bool hasPosts()
	 * @method bool isPostsFilled()
	 * @method bool isPostsChanged()
	 * @method \int remindActualPosts()
	 * @method \int requirePosts()
	 * @method \Bitrix\Forum\EO_Topic resetPosts()
	 * @method \Bitrix\Forum\EO_Topic unsetPosts()
	 * @method \int fillPosts()
	 * @method \int getPostsService()
	 * @method \Bitrix\Forum\EO_Topic setPostsService(\int|\Bitrix\Main\DB\SqlExpression $postsService)
	 * @method bool hasPostsService()
	 * @method bool isPostsServiceFilled()
	 * @method bool isPostsServiceChanged()
	 * @method \int remindActualPostsService()
	 * @method \int requirePostsService()
	 * @method \Bitrix\Forum\EO_Topic resetPostsService()
	 * @method \Bitrix\Forum\EO_Topic unsetPostsService()
	 * @method \int fillPostsService()
	 * @method \int getLastPosterId()
	 * @method \Bitrix\Forum\EO_Topic setLastPosterId(\int|\Bitrix\Main\DB\SqlExpression $lastPosterId)
	 * @method bool hasLastPosterId()
	 * @method bool isLastPosterIdFilled()
	 * @method bool isLastPosterIdChanged()
	 * @method \int remindActualLastPosterId()
	 * @method \int requireLastPosterId()
	 * @method \Bitrix\Forum\EO_Topic resetLastPosterId()
	 * @method \Bitrix\Forum\EO_Topic unsetLastPosterId()
	 * @method \int fillLastPosterId()
	 * @method \string getLastPosterName()
	 * @method \Bitrix\Forum\EO_Topic setLastPosterName(\string|\Bitrix\Main\DB\SqlExpression $lastPosterName)
	 * @method bool hasLastPosterName()
	 * @method bool isLastPosterNameFilled()
	 * @method bool isLastPosterNameChanged()
	 * @method \string remindActualLastPosterName()
	 * @method \string requireLastPosterName()
	 * @method \Bitrix\Forum\EO_Topic resetLastPosterName()
	 * @method \Bitrix\Forum\EO_Topic unsetLastPosterName()
	 * @method \string fillLastPosterName()
	 * @method \Bitrix\Main\Type\DateTime getLastPostDate()
	 * @method \Bitrix\Forum\EO_Topic setLastPostDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastPostDate)
	 * @method bool hasLastPostDate()
	 * @method bool isLastPostDateFilled()
	 * @method bool isLastPostDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastPostDate()
	 * @method \Bitrix\Main\Type\DateTime requireLastPostDate()
	 * @method \Bitrix\Forum\EO_Topic resetLastPostDate()
	 * @method \Bitrix\Forum\EO_Topic unsetLastPostDate()
	 * @method \Bitrix\Main\Type\DateTime fillLastPostDate()
	 * @method \int getLastMessageId()
	 * @method \Bitrix\Forum\EO_Topic setLastMessageId(\int|\Bitrix\Main\DB\SqlExpression $lastMessageId)
	 * @method bool hasLastMessageId()
	 * @method bool isLastMessageIdFilled()
	 * @method bool isLastMessageIdChanged()
	 * @method \int remindActualLastMessageId()
	 * @method \int requireLastMessageId()
	 * @method \Bitrix\Forum\EO_Topic resetLastMessageId()
	 * @method \Bitrix\Forum\EO_Topic unsetLastMessageId()
	 * @method \int fillLastMessageId()
	 * @method \int getPostsUnapproved()
	 * @method \Bitrix\Forum\EO_Topic setPostsUnapproved(\int|\Bitrix\Main\DB\SqlExpression $postsUnapproved)
	 * @method bool hasPostsUnapproved()
	 * @method bool isPostsUnapprovedFilled()
	 * @method bool isPostsUnapprovedChanged()
	 * @method \int remindActualPostsUnapproved()
	 * @method \int requirePostsUnapproved()
	 * @method \Bitrix\Forum\EO_Topic resetPostsUnapproved()
	 * @method \Bitrix\Forum\EO_Topic unsetPostsUnapproved()
	 * @method \int fillPostsUnapproved()
	 * @method \int getAbsLastPosterId()
	 * @method \Bitrix\Forum\EO_Topic setAbsLastPosterId(\int|\Bitrix\Main\DB\SqlExpression $absLastPosterId)
	 * @method bool hasAbsLastPosterId()
	 * @method bool isAbsLastPosterIdFilled()
	 * @method bool isAbsLastPosterIdChanged()
	 * @method \int remindActualAbsLastPosterId()
	 * @method \int requireAbsLastPosterId()
	 * @method \Bitrix\Forum\EO_Topic resetAbsLastPosterId()
	 * @method \Bitrix\Forum\EO_Topic unsetAbsLastPosterId()
	 * @method \int fillAbsLastPosterId()
	 * @method \string getAbsLastPosterName()
	 * @method \Bitrix\Forum\EO_Topic setAbsLastPosterName(\string|\Bitrix\Main\DB\SqlExpression $absLastPosterName)
	 * @method bool hasAbsLastPosterName()
	 * @method bool isAbsLastPosterNameFilled()
	 * @method bool isAbsLastPosterNameChanged()
	 * @method \string remindActualAbsLastPosterName()
	 * @method \string requireAbsLastPosterName()
	 * @method \Bitrix\Forum\EO_Topic resetAbsLastPosterName()
	 * @method \Bitrix\Forum\EO_Topic unsetAbsLastPosterName()
	 * @method \string fillAbsLastPosterName()
	 * @method \Bitrix\Main\Type\DateTime getAbsLastPostDate()
	 * @method \Bitrix\Forum\EO_Topic setAbsLastPostDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $absLastPostDate)
	 * @method bool hasAbsLastPostDate()
	 * @method bool isAbsLastPostDateFilled()
	 * @method bool isAbsLastPostDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualAbsLastPostDate()
	 * @method \Bitrix\Main\Type\DateTime requireAbsLastPostDate()
	 * @method \Bitrix\Forum\EO_Topic resetAbsLastPostDate()
	 * @method \Bitrix\Forum\EO_Topic unsetAbsLastPostDate()
	 * @method \Bitrix\Main\Type\DateTime fillAbsLastPostDate()
	 * @method \int getAbsLastMessageId()
	 * @method \Bitrix\Forum\EO_Topic setAbsLastMessageId(\int|\Bitrix\Main\DB\SqlExpression $absLastMessageId)
	 * @method bool hasAbsLastMessageId()
	 * @method bool isAbsLastMessageIdFilled()
	 * @method bool isAbsLastMessageIdChanged()
	 * @method \int remindActualAbsLastMessageId()
	 * @method \int requireAbsLastMessageId()
	 * @method \Bitrix\Forum\EO_Topic resetAbsLastMessageId()
	 * @method \Bitrix\Forum\EO_Topic unsetAbsLastMessageId()
	 * @method \int fillAbsLastMessageId()
	 * @method \string getXmlId()
	 * @method \Bitrix\Forum\EO_Topic setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Forum\EO_Topic resetXmlId()
	 * @method \Bitrix\Forum\EO_Topic unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getHtml()
	 * @method \Bitrix\Forum\EO_Topic setHtml(\string|\Bitrix\Main\DB\SqlExpression $html)
	 * @method bool hasHtml()
	 * @method bool isHtmlFilled()
	 * @method bool isHtmlChanged()
	 * @method \string remindActualHtml()
	 * @method \string requireHtml()
	 * @method \Bitrix\Forum\EO_Topic resetHtml()
	 * @method \Bitrix\Forum\EO_Topic unsetHtml()
	 * @method \string fillHtml()
	 * @method \int getSocnetGroupId()
	 * @method \Bitrix\Forum\EO_Topic setSocnetGroupId(\int|\Bitrix\Main\DB\SqlExpression $socnetGroupId)
	 * @method bool hasSocnetGroupId()
	 * @method bool isSocnetGroupIdFilled()
	 * @method bool isSocnetGroupIdChanged()
	 * @method \int remindActualSocnetGroupId()
	 * @method \int requireSocnetGroupId()
	 * @method \Bitrix\Forum\EO_Topic resetSocnetGroupId()
	 * @method \Bitrix\Forum\EO_Topic unsetSocnetGroupId()
	 * @method \int fillSocnetGroupId()
	 * @method \int getOwnerId()
	 * @method \Bitrix\Forum\EO_Topic setOwnerId(\int|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \int remindActualOwnerId()
	 * @method \int requireOwnerId()
	 * @method \Bitrix\Forum\EO_Topic resetOwnerId()
	 * @method \Bitrix\Forum\EO_Topic unsetOwnerId()
	 * @method \int fillOwnerId()
	 * @method \Bitrix\Forum\EO_Forum getForum()
	 * @method \Bitrix\Forum\EO_Forum remindActualForum()
	 * @method \Bitrix\Forum\EO_Forum requireForum()
	 * @method \Bitrix\Forum\EO_Topic setForum(\Bitrix\Forum\EO_Forum $object)
	 * @method \Bitrix\Forum\EO_Topic resetForum()
	 * @method \Bitrix\Forum\EO_Topic unsetForum()
	 * @method bool hasForum()
	 * @method bool isForumFilled()
	 * @method bool isForumChanged()
	 * @method \Bitrix\Forum\EO_Forum fillForum()
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
	 * @method \Bitrix\Forum\EO_Topic set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_Topic reset($fieldName)
	 * @method \Bitrix\Forum\EO_Topic unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_Topic wakeUp($data)
	 */
	class EO_Topic {
		/* @var \Bitrix\Forum\TopicTable */
		static public $dataClass = '\Bitrix\Forum\TopicTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_Topic_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getForumIdList()
	 * @method \int[] fillForumId()
	 * @method \int[] getTopicIdList()
	 * @method \int[] fillTopicId()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \string[] getTitleSeoList()
	 * @method \string[] fillTitleSeo()
	 * @method \string[] getTagsList()
	 * @method \string[] fillTags()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getIconList()
	 * @method \string[] fillIcon()
	 * @method \string[] getStateList()
	 * @method \string[] fillState()
	 * @method \boolean[] getApprovedList()
	 * @method \boolean[] fillApproved()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \int[] getViewsList()
	 * @method \int[] fillViews()
	 * @method \int[] getUserStartIdList()
	 * @method \int[] fillUserStartId()
	 * @method \string[] getUserStartNameList()
	 * @method \string[] fillUserStartName()
	 * @method \Bitrix\Main\Type\DateTime[] getStartDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStartDate()
	 * @method \int[] getPostsList()
	 * @method \int[] fillPosts()
	 * @method \int[] getPostsServiceList()
	 * @method \int[] fillPostsService()
	 * @method \int[] getLastPosterIdList()
	 * @method \int[] fillLastPosterId()
	 * @method \string[] getLastPosterNameList()
	 * @method \string[] fillLastPosterName()
	 * @method \Bitrix\Main\Type\DateTime[] getLastPostDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastPostDate()
	 * @method \int[] getLastMessageIdList()
	 * @method \int[] fillLastMessageId()
	 * @method \int[] getPostsUnapprovedList()
	 * @method \int[] fillPostsUnapproved()
	 * @method \int[] getAbsLastPosterIdList()
	 * @method \int[] fillAbsLastPosterId()
	 * @method \string[] getAbsLastPosterNameList()
	 * @method \string[] fillAbsLastPosterName()
	 * @method \Bitrix\Main\Type\DateTime[] getAbsLastPostDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillAbsLastPostDate()
	 * @method \int[] getAbsLastMessageIdList()
	 * @method \int[] fillAbsLastMessageId()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getHtmlList()
	 * @method \string[] fillHtml()
	 * @method \int[] getSocnetGroupIdList()
	 * @method \int[] fillSocnetGroupId()
	 * @method \int[] getOwnerIdList()
	 * @method \int[] fillOwnerId()
	 * @method \Bitrix\Forum\EO_Forum[] getForumList()
	 * @method \Bitrix\Forum\EO_Topic_Collection getForumCollection()
	 * @method \Bitrix\Forum\EO_Forum_Collection fillForum()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_Topic $object)
	 * @method bool has(\Bitrix\Forum\EO_Topic $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Topic getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Topic[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_Topic $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_Topic_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_Topic current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Topic_Collection merge(?EO_Topic_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Topic_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\TopicTable */
		static public $dataClass = '\Bitrix\Forum\TopicTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Topic_Result exec()
	 * @method \Bitrix\Forum\EO_Topic fetchObject()
	 * @method \Bitrix\Forum\EO_Topic_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Topic_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_Topic fetchObject()
	 * @method \Bitrix\Forum\EO_Topic_Collection fetchCollection()
	 */
	class EO_Topic_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_Topic createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_Topic_Collection createCollection()
	 * @method \Bitrix\Forum\EO_Topic wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_Topic_Collection wakeUpCollection($rows)
	 */
	class EO_Topic_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\UserForumTable:forum/lib/userforum.php */
namespace Bitrix\Forum {
	/**
	 * EO_UserForum
	 * @see \Bitrix\Forum\UserForumTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_UserForum setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int remindActualId()
	 * @method \int requireId()
	 * @method \Bitrix\Forum\EO_UserForum resetId()
	 * @method \Bitrix\Forum\EO_UserForum unsetId()
	 * @method \int fillId()
	 * @method \int getUserId()
	 * @method \Bitrix\Forum\EO_UserForum setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int getForumId()
	 * @method \Bitrix\Forum\EO_UserForum setForumId(\int|\Bitrix\Main\DB\SqlExpression $forumId)
	 * @method bool hasForumId()
	 * @method bool isForumIdFilled()
	 * @method bool isForumIdChanged()
	 * @method \Bitrix\Main\Type\DateTime getLastVisit()
	 * @method \Bitrix\Forum\EO_UserForum setLastVisit(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $lastVisit)
	 * @method bool hasLastVisit()
	 * @method bool isLastVisitFilled()
	 * @method bool isLastVisitChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualLastVisit()
	 * @method \Bitrix\Main\Type\DateTime requireLastVisit()
	 * @method \Bitrix\Forum\EO_UserForum resetLastVisit()
	 * @method \Bitrix\Forum\EO_UserForum unsetLastVisit()
	 * @method \Bitrix\Main\Type\DateTime fillLastVisit()
	 * @method \Bitrix\Main\Type\DateTime getMainLastVisit()
	 * @method \Bitrix\Forum\EO_UserForum setMainLastVisit(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $mainLastVisit)
	 * @method bool hasMainLastVisit()
	 * @method bool isMainLastVisitFilled()
	 * @method bool isMainLastVisitChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualMainLastVisit()
	 * @method \Bitrix\Main\Type\DateTime requireMainLastVisit()
	 * @method \Bitrix\Forum\EO_UserForum resetMainLastVisit()
	 * @method \Bitrix\Forum\EO_UserForum unsetMainLastVisit()
	 * @method \Bitrix\Main\Type\DateTime fillMainLastVisit()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Forum\EO_UserForum setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Forum\EO_UserForum resetUser()
	 * @method \Bitrix\Forum\EO_UserForum unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \Bitrix\Forum\EO_User getForumUser()
	 * @method \Bitrix\Forum\EO_User remindActualForumUser()
	 * @method \Bitrix\Forum\EO_User requireForumUser()
	 * @method \Bitrix\Forum\EO_UserForum setForumUser(\Bitrix\Forum\EO_User $object)
	 * @method \Bitrix\Forum\EO_UserForum resetForumUser()
	 * @method \Bitrix\Forum\EO_UserForum unsetForumUser()
	 * @method bool hasForumUser()
	 * @method bool isForumUserFilled()
	 * @method bool isForumUserChanged()
	 * @method \Bitrix\Forum\EO_User fillForumUser()
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
	 * @method \Bitrix\Forum\EO_UserForum set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_UserForum reset($fieldName)
	 * @method \Bitrix\Forum\EO_UserForum unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_UserForum wakeUp($data)
	 */
	class EO_UserForum {
		/* @var \Bitrix\Forum\UserForumTable */
		static public $dataClass = '\Bitrix\Forum\UserForumTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_UserForum_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] fillId()
	 * @method \int[] getUserIdList()
	 * @method \int[] getForumIdList()
	 * @method \Bitrix\Main\Type\DateTime[] getLastVisitList()
	 * @method \Bitrix\Main\Type\DateTime[] fillLastVisit()
	 * @method \Bitrix\Main\Type\DateTime[] getMainLastVisitList()
	 * @method \Bitrix\Main\Type\DateTime[] fillMainLastVisit()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Forum\EO_UserForum_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \Bitrix\Forum\EO_User[] getForumUserList()
	 * @method \Bitrix\Forum\EO_UserForum_Collection getForumUserCollection()
	 * @method \Bitrix\Forum\EO_User_Collection fillForumUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_UserForum $object)
	 * @method bool has(\Bitrix\Forum\EO_UserForum $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_UserForum getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_UserForum[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_UserForum $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_UserForum_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_UserForum current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_UserForum_Collection merge(?EO_UserForum_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_UserForum_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\UserForumTable */
		static public $dataClass = '\Bitrix\Forum\UserForumTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_UserForum_Result exec()
	 * @method \Bitrix\Forum\EO_UserForum fetchObject()
	 * @method \Bitrix\Forum\EO_UserForum_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_UserForum_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_UserForum fetchObject()
	 * @method \Bitrix\Forum\EO_UserForum_Collection fetchCollection()
	 */
	class EO_UserForum_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_UserForum createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_UserForum_Collection createCollection()
	 * @method \Bitrix\Forum\EO_UserForum wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_UserForum_Collection wakeUpCollection($rows)
	 */
	class EO_UserForum_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\PermissionTable:forum/lib/permission.php */
namespace Bitrix\Forum {
	/**
	 * EO_Permission
	 * @see \Bitrix\Forum\PermissionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_Permission setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getForumId()
	 * @method \Bitrix\Forum\EO_Permission setForumId(\int|\Bitrix\Main\DB\SqlExpression $forumId)
	 * @method bool hasForumId()
	 * @method bool isForumIdFilled()
	 * @method bool isForumIdChanged()
	 * @method \int remindActualForumId()
	 * @method \int requireForumId()
	 * @method \Bitrix\Forum\EO_Permission resetForumId()
	 * @method \Bitrix\Forum\EO_Permission unsetForumId()
	 * @method \int fillForumId()
	 * @method \int getGroupId()
	 * @method \Bitrix\Forum\EO_Permission setGroupId(\int|\Bitrix\Main\DB\SqlExpression $groupId)
	 * @method bool hasGroupId()
	 * @method bool isGroupIdFilled()
	 * @method bool isGroupIdChanged()
	 * @method \int remindActualGroupId()
	 * @method \int requireGroupId()
	 * @method \Bitrix\Forum\EO_Permission resetGroupId()
	 * @method \Bitrix\Forum\EO_Permission unsetGroupId()
	 * @method \int fillGroupId()
	 * @method \string getPermission()
	 * @method \Bitrix\Forum\EO_Permission setPermission(\string|\Bitrix\Main\DB\SqlExpression $permission)
	 * @method bool hasPermission()
	 * @method bool isPermissionFilled()
	 * @method bool isPermissionChanged()
	 * @method \string remindActualPermission()
	 * @method \string requirePermission()
	 * @method \Bitrix\Forum\EO_Permission resetPermission()
	 * @method \Bitrix\Forum\EO_Permission unsetPermission()
	 * @method \string fillPermission()
	 * @method \Bitrix\Main\EO_Group getGroup()
	 * @method \Bitrix\Main\EO_Group remindActualGroup()
	 * @method \Bitrix\Main\EO_Group requireGroup()
	 * @method \Bitrix\Forum\EO_Permission setGroup(\Bitrix\Main\EO_Group $object)
	 * @method \Bitrix\Forum\EO_Permission resetGroup()
	 * @method \Bitrix\Forum\EO_Permission unsetGroup()
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
	 * @method \Bitrix\Forum\EO_Permission set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_Permission reset($fieldName)
	 * @method \Bitrix\Forum\EO_Permission unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_Permission wakeUp($data)
	 */
	class EO_Permission {
		/* @var \Bitrix\Forum\PermissionTable */
		static public $dataClass = '\Bitrix\Forum\PermissionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_Permission_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getForumIdList()
	 * @method \int[] fillForumId()
	 * @method \int[] getGroupIdList()
	 * @method \int[] fillGroupId()
	 * @method \string[] getPermissionList()
	 * @method \string[] fillPermission()
	 * @method \Bitrix\Main\EO_Group[] getGroupList()
	 * @method \Bitrix\Forum\EO_Permission_Collection getGroupCollection()
	 * @method \Bitrix\Main\EO_Group_Collection fillGroup()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_Permission $object)
	 * @method bool has(\Bitrix\Forum\EO_Permission $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Permission getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Permission[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_Permission $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_Permission_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_Permission current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Permission_Collection merge(?EO_Permission_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Permission_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\PermissionTable */
		static public $dataClass = '\Bitrix\Forum\PermissionTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Permission_Result exec()
	 * @method \Bitrix\Forum\EO_Permission fetchObject()
	 * @method \Bitrix\Forum\EO_Permission_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Permission_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_Permission fetchObject()
	 * @method \Bitrix\Forum\EO_Permission_Collection fetchCollection()
	 */
	class EO_Permission_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_Permission createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_Permission_Collection createCollection()
	 * @method \Bitrix\Forum\EO_Permission wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_Permission_Collection wakeUpCollection($rows)
	 */
	class EO_Permission_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Forum\MessageTable:forum/lib/message.php */
namespace Bitrix\Forum {
	/**
	 * EO_Message
	 * @see \Bitrix\Forum\MessageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Forum\EO_Message setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getForumId()
	 * @method \Bitrix\Forum\EO_Message setForumId(\int|\Bitrix\Main\DB\SqlExpression $forumId)
	 * @method bool hasForumId()
	 * @method bool isForumIdFilled()
	 * @method bool isForumIdChanged()
	 * @method \int remindActualForumId()
	 * @method \int requireForumId()
	 * @method \Bitrix\Forum\EO_Message resetForumId()
	 * @method \Bitrix\Forum\EO_Message unsetForumId()
	 * @method \int fillForumId()
	 * @method \int getTopicId()
	 * @method \Bitrix\Forum\EO_Message setTopicId(\int|\Bitrix\Main\DB\SqlExpression $topicId)
	 * @method bool hasTopicId()
	 * @method bool isTopicIdFilled()
	 * @method bool isTopicIdChanged()
	 * @method \int remindActualTopicId()
	 * @method \int requireTopicId()
	 * @method \Bitrix\Forum\EO_Message resetTopicId()
	 * @method \Bitrix\Forum\EO_Message unsetTopicId()
	 * @method \int fillTopicId()
	 * @method \boolean getUseSmiles()
	 * @method \Bitrix\Forum\EO_Message setUseSmiles(\boolean|\Bitrix\Main\DB\SqlExpression $useSmiles)
	 * @method bool hasUseSmiles()
	 * @method bool isUseSmilesFilled()
	 * @method bool isUseSmilesChanged()
	 * @method \boolean remindActualUseSmiles()
	 * @method \boolean requireUseSmiles()
	 * @method \Bitrix\Forum\EO_Message resetUseSmiles()
	 * @method \Bitrix\Forum\EO_Message unsetUseSmiles()
	 * @method \boolean fillUseSmiles()
	 * @method \boolean getNewTopic()
	 * @method \Bitrix\Forum\EO_Message setNewTopic(\boolean|\Bitrix\Main\DB\SqlExpression $newTopic)
	 * @method bool hasNewTopic()
	 * @method bool isNewTopicFilled()
	 * @method bool isNewTopicChanged()
	 * @method \boolean remindActualNewTopic()
	 * @method \boolean requireNewTopic()
	 * @method \Bitrix\Forum\EO_Message resetNewTopic()
	 * @method \Bitrix\Forum\EO_Message unsetNewTopic()
	 * @method \boolean fillNewTopic()
	 * @method \boolean getApproved()
	 * @method \Bitrix\Forum\EO_Message setApproved(\boolean|\Bitrix\Main\DB\SqlExpression $approved)
	 * @method bool hasApproved()
	 * @method bool isApprovedFilled()
	 * @method bool isApprovedChanged()
	 * @method \boolean remindActualApproved()
	 * @method \boolean requireApproved()
	 * @method \Bitrix\Forum\EO_Message resetApproved()
	 * @method \Bitrix\Forum\EO_Message unsetApproved()
	 * @method \boolean fillApproved()
	 * @method \boolean getSourceId()
	 * @method \Bitrix\Forum\EO_Message setSourceId(\boolean|\Bitrix\Main\DB\SqlExpression $sourceId)
	 * @method bool hasSourceId()
	 * @method bool isSourceIdFilled()
	 * @method bool isSourceIdChanged()
	 * @method \boolean remindActualSourceId()
	 * @method \boolean requireSourceId()
	 * @method \Bitrix\Forum\EO_Message resetSourceId()
	 * @method \Bitrix\Forum\EO_Message unsetSourceId()
	 * @method \boolean fillSourceId()
	 * @method \Bitrix\Main\Type\DateTime getPostDate()
	 * @method \Bitrix\Forum\EO_Message setPostDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $postDate)
	 * @method bool hasPostDate()
	 * @method bool isPostDateFilled()
	 * @method bool isPostDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualPostDate()
	 * @method \Bitrix\Main\Type\DateTime requirePostDate()
	 * @method \Bitrix\Forum\EO_Message resetPostDate()
	 * @method \Bitrix\Forum\EO_Message unsetPostDate()
	 * @method \Bitrix\Main\Type\DateTime fillPostDate()
	 * @method \string getPostMessage()
	 * @method \Bitrix\Forum\EO_Message setPostMessage(\string|\Bitrix\Main\DB\SqlExpression $postMessage)
	 * @method bool hasPostMessage()
	 * @method bool isPostMessageFilled()
	 * @method bool isPostMessageChanged()
	 * @method \string remindActualPostMessage()
	 * @method \string requirePostMessage()
	 * @method \Bitrix\Forum\EO_Message resetPostMessage()
	 * @method \Bitrix\Forum\EO_Message unsetPostMessage()
	 * @method \string fillPostMessage()
	 * @method \string getPostMessageHtml()
	 * @method \Bitrix\Forum\EO_Message setPostMessageHtml(\string|\Bitrix\Main\DB\SqlExpression $postMessageHtml)
	 * @method bool hasPostMessageHtml()
	 * @method bool isPostMessageHtmlFilled()
	 * @method bool isPostMessageHtmlChanged()
	 * @method \string remindActualPostMessageHtml()
	 * @method \string requirePostMessageHtml()
	 * @method \Bitrix\Forum\EO_Message resetPostMessageHtml()
	 * @method \Bitrix\Forum\EO_Message unsetPostMessageHtml()
	 * @method \string fillPostMessageHtml()
	 * @method \string getPostMessageFilter()
	 * @method \Bitrix\Forum\EO_Message setPostMessageFilter(\string|\Bitrix\Main\DB\SqlExpression $postMessageFilter)
	 * @method bool hasPostMessageFilter()
	 * @method bool isPostMessageFilterFilled()
	 * @method bool isPostMessageFilterChanged()
	 * @method \string remindActualPostMessageFilter()
	 * @method \string requirePostMessageFilter()
	 * @method \Bitrix\Forum\EO_Message resetPostMessageFilter()
	 * @method \Bitrix\Forum\EO_Message unsetPostMessageFilter()
	 * @method \string fillPostMessageFilter()
	 * @method \string getPostMessageCheck()
	 * @method \Bitrix\Forum\EO_Message setPostMessageCheck(\string|\Bitrix\Main\DB\SqlExpression $postMessageCheck)
	 * @method bool hasPostMessageCheck()
	 * @method bool isPostMessageCheckFilled()
	 * @method bool isPostMessageCheckChanged()
	 * @method \string remindActualPostMessageCheck()
	 * @method \string requirePostMessageCheck()
	 * @method \Bitrix\Forum\EO_Message resetPostMessageCheck()
	 * @method \Bitrix\Forum\EO_Message unsetPostMessageCheck()
	 * @method \string fillPostMessageCheck()
	 * @method \int getAttachImg()
	 * @method \Bitrix\Forum\EO_Message setAttachImg(\int|\Bitrix\Main\DB\SqlExpression $attachImg)
	 * @method bool hasAttachImg()
	 * @method bool isAttachImgFilled()
	 * @method bool isAttachImgChanged()
	 * @method \int remindActualAttachImg()
	 * @method \int requireAttachImg()
	 * @method \Bitrix\Forum\EO_Message resetAttachImg()
	 * @method \Bitrix\Forum\EO_Message unsetAttachImg()
	 * @method \int fillAttachImg()
	 * @method \string getParam1()
	 * @method \Bitrix\Forum\EO_Message setParam1(\string|\Bitrix\Main\DB\SqlExpression $param1)
	 * @method bool hasParam1()
	 * @method bool isParam1Filled()
	 * @method bool isParam1Changed()
	 * @method \string remindActualParam1()
	 * @method \string requireParam1()
	 * @method \Bitrix\Forum\EO_Message resetParam1()
	 * @method \Bitrix\Forum\EO_Message unsetParam1()
	 * @method \string fillParam1()
	 * @method \int getParam2()
	 * @method \Bitrix\Forum\EO_Message setParam2(\int|\Bitrix\Main\DB\SqlExpression $param2)
	 * @method bool hasParam2()
	 * @method bool isParam2Filled()
	 * @method bool isParam2Changed()
	 * @method \int remindActualParam2()
	 * @method \int requireParam2()
	 * @method \Bitrix\Forum\EO_Message resetParam2()
	 * @method \Bitrix\Forum\EO_Message unsetParam2()
	 * @method \int fillParam2()
	 * @method \int getAuthorId()
	 * @method \Bitrix\Forum\EO_Message setAuthorId(\int|\Bitrix\Main\DB\SqlExpression $authorId)
	 * @method bool hasAuthorId()
	 * @method bool isAuthorIdFilled()
	 * @method bool isAuthorIdChanged()
	 * @method \int remindActualAuthorId()
	 * @method \int requireAuthorId()
	 * @method \Bitrix\Forum\EO_Message resetAuthorId()
	 * @method \Bitrix\Forum\EO_Message unsetAuthorId()
	 * @method \int fillAuthorId()
	 * @method \string getAuthorName()
	 * @method \Bitrix\Forum\EO_Message setAuthorName(\string|\Bitrix\Main\DB\SqlExpression $authorName)
	 * @method bool hasAuthorName()
	 * @method bool isAuthorNameFilled()
	 * @method bool isAuthorNameChanged()
	 * @method \string remindActualAuthorName()
	 * @method \string requireAuthorName()
	 * @method \Bitrix\Forum\EO_Message resetAuthorName()
	 * @method \Bitrix\Forum\EO_Message unsetAuthorName()
	 * @method \string fillAuthorName()
	 * @method \string getAuthorEmail()
	 * @method \Bitrix\Forum\EO_Message setAuthorEmail(\string|\Bitrix\Main\DB\SqlExpression $authorEmail)
	 * @method bool hasAuthorEmail()
	 * @method bool isAuthorEmailFilled()
	 * @method bool isAuthorEmailChanged()
	 * @method \string remindActualAuthorEmail()
	 * @method \string requireAuthorEmail()
	 * @method \Bitrix\Forum\EO_Message resetAuthorEmail()
	 * @method \Bitrix\Forum\EO_Message unsetAuthorEmail()
	 * @method \string fillAuthorEmail()
	 * @method \string getAuthorIp()
	 * @method \Bitrix\Forum\EO_Message setAuthorIp(\string|\Bitrix\Main\DB\SqlExpression $authorIp)
	 * @method bool hasAuthorIp()
	 * @method bool isAuthorIpFilled()
	 * @method bool isAuthorIpChanged()
	 * @method \string remindActualAuthorIp()
	 * @method \string requireAuthorIp()
	 * @method \Bitrix\Forum\EO_Message resetAuthorIp()
	 * @method \Bitrix\Forum\EO_Message unsetAuthorIp()
	 * @method \string fillAuthorIp()
	 * @method \string getAuthorRealIp()
	 * @method \Bitrix\Forum\EO_Message setAuthorRealIp(\string|\Bitrix\Main\DB\SqlExpression $authorRealIp)
	 * @method bool hasAuthorRealIp()
	 * @method bool isAuthorRealIpFilled()
	 * @method bool isAuthorRealIpChanged()
	 * @method \string remindActualAuthorRealIp()
	 * @method \string requireAuthorRealIp()
	 * @method \Bitrix\Forum\EO_Message resetAuthorRealIp()
	 * @method \Bitrix\Forum\EO_Message unsetAuthorRealIp()
	 * @method \string fillAuthorRealIp()
	 * @method \int getGuestId()
	 * @method \Bitrix\Forum\EO_Message setGuestId(\int|\Bitrix\Main\DB\SqlExpression $guestId)
	 * @method bool hasGuestId()
	 * @method bool isGuestIdFilled()
	 * @method bool isGuestIdChanged()
	 * @method \int remindActualGuestId()
	 * @method \int requireGuestId()
	 * @method \Bitrix\Forum\EO_Message resetGuestId()
	 * @method \Bitrix\Forum\EO_Message unsetGuestId()
	 * @method \int fillGuestId()
	 * @method \int getEditorId()
	 * @method \Bitrix\Forum\EO_Message setEditorId(\int|\Bitrix\Main\DB\SqlExpression $editorId)
	 * @method bool hasEditorId()
	 * @method bool isEditorIdFilled()
	 * @method bool isEditorIdChanged()
	 * @method \int remindActualEditorId()
	 * @method \int requireEditorId()
	 * @method \Bitrix\Forum\EO_Message resetEditorId()
	 * @method \Bitrix\Forum\EO_Message unsetEditorId()
	 * @method \int fillEditorId()
	 * @method \string getEditorName()
	 * @method \Bitrix\Forum\EO_Message setEditorName(\string|\Bitrix\Main\DB\SqlExpression $editorName)
	 * @method bool hasEditorName()
	 * @method bool isEditorNameFilled()
	 * @method bool isEditorNameChanged()
	 * @method \string remindActualEditorName()
	 * @method \string requireEditorName()
	 * @method \Bitrix\Forum\EO_Message resetEditorName()
	 * @method \Bitrix\Forum\EO_Message unsetEditorName()
	 * @method \string fillEditorName()
	 * @method \string getEditorEmail()
	 * @method \Bitrix\Forum\EO_Message setEditorEmail(\string|\Bitrix\Main\DB\SqlExpression $editorEmail)
	 * @method bool hasEditorEmail()
	 * @method bool isEditorEmailFilled()
	 * @method bool isEditorEmailChanged()
	 * @method \string remindActualEditorEmail()
	 * @method \string requireEditorEmail()
	 * @method \Bitrix\Forum\EO_Message resetEditorEmail()
	 * @method \Bitrix\Forum\EO_Message unsetEditorEmail()
	 * @method \string fillEditorEmail()
	 * @method \string getEditReason()
	 * @method \Bitrix\Forum\EO_Message setEditReason(\string|\Bitrix\Main\DB\SqlExpression $editReason)
	 * @method bool hasEditReason()
	 * @method bool isEditReasonFilled()
	 * @method bool isEditReasonChanged()
	 * @method \string remindActualEditReason()
	 * @method \string requireEditReason()
	 * @method \Bitrix\Forum\EO_Message resetEditReason()
	 * @method \Bitrix\Forum\EO_Message unsetEditReason()
	 * @method \string fillEditReason()
	 * @method \Bitrix\Main\Type\DateTime getEditDate()
	 * @method \Bitrix\Forum\EO_Message setEditDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $editDate)
	 * @method bool hasEditDate()
	 * @method bool isEditDateFilled()
	 * @method bool isEditDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualEditDate()
	 * @method \Bitrix\Main\Type\DateTime requireEditDate()
	 * @method \Bitrix\Forum\EO_Message resetEditDate()
	 * @method \Bitrix\Forum\EO_Message unsetEditDate()
	 * @method \Bitrix\Main\Type\DateTime fillEditDate()
	 * @method \string getXmlId()
	 * @method \Bitrix\Forum\EO_Message setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Forum\EO_Message resetXmlId()
	 * @method \Bitrix\Forum\EO_Message unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getHtml()
	 * @method \Bitrix\Forum\EO_Message setHtml(\string|\Bitrix\Main\DB\SqlExpression $html)
	 * @method bool hasHtml()
	 * @method bool isHtmlFilled()
	 * @method bool isHtmlChanged()
	 * @method \string remindActualHtml()
	 * @method \string requireHtml()
	 * @method \Bitrix\Forum\EO_Message resetHtml()
	 * @method \Bitrix\Forum\EO_Message unsetHtml()
	 * @method \string fillHtml()
	 * @method \string getMailHeader()
	 * @method \Bitrix\Forum\EO_Message setMailHeader(\string|\Bitrix\Main\DB\SqlExpression $mailHeader)
	 * @method bool hasMailHeader()
	 * @method bool isMailHeaderFilled()
	 * @method bool isMailHeaderChanged()
	 * @method \string remindActualMailHeader()
	 * @method \string requireMailHeader()
	 * @method \Bitrix\Forum\EO_Message resetMailHeader()
	 * @method \Bitrix\Forum\EO_Message unsetMailHeader()
	 * @method \string fillMailHeader()
	 * @method \int getServiceType()
	 * @method \Bitrix\Forum\EO_Message setServiceType(\int|\Bitrix\Main\DB\SqlExpression $serviceType)
	 * @method bool hasServiceType()
	 * @method bool isServiceTypeFilled()
	 * @method bool isServiceTypeChanged()
	 * @method \int remindActualServiceType()
	 * @method \int requireServiceType()
	 * @method \Bitrix\Forum\EO_Message resetServiceType()
	 * @method \Bitrix\Forum\EO_Message unsetServiceType()
	 * @method \int fillServiceType()
	 * @method \string getServiceData()
	 * @method \Bitrix\Forum\EO_Message setServiceData(\string|\Bitrix\Main\DB\SqlExpression $serviceData)
	 * @method bool hasServiceData()
	 * @method bool isServiceDataFilled()
	 * @method bool isServiceDataChanged()
	 * @method \string remindActualServiceData()
	 * @method \string requireServiceData()
	 * @method \Bitrix\Forum\EO_Message resetServiceData()
	 * @method \Bitrix\Forum\EO_Message unsetServiceData()
	 * @method \string fillServiceData()
	 * @method \Bitrix\Forum\EO_Topic getTopic()
	 * @method \Bitrix\Forum\EO_Topic remindActualTopic()
	 * @method \Bitrix\Forum\EO_Topic requireTopic()
	 * @method \Bitrix\Forum\EO_Message setTopic(\Bitrix\Forum\EO_Topic $object)
	 * @method \Bitrix\Forum\EO_Message resetTopic()
	 * @method \Bitrix\Forum\EO_Message unsetTopic()
	 * @method bool hasTopic()
	 * @method bool isTopicFilled()
	 * @method bool isTopicChanged()
	 * @method \Bitrix\Forum\EO_Topic fillTopic()
	 * @method \Bitrix\Forum\EO_User getForumUser()
	 * @method \Bitrix\Forum\EO_User remindActualForumUser()
	 * @method \Bitrix\Forum\EO_User requireForumUser()
	 * @method \Bitrix\Forum\EO_Message setForumUser(\Bitrix\Forum\EO_User $object)
	 * @method \Bitrix\Forum\EO_Message resetForumUser()
	 * @method \Bitrix\Forum\EO_Message unsetForumUser()
	 * @method bool hasForumUser()
	 * @method bool isForumUserFilled()
	 * @method bool isForumUserChanged()
	 * @method \Bitrix\Forum\EO_User fillForumUser()
	 * @method \Bitrix\Forum\EO_UserTopic getForumUserTopic()
	 * @method \Bitrix\Forum\EO_UserTopic remindActualForumUserTopic()
	 * @method \Bitrix\Forum\EO_UserTopic requireForumUserTopic()
	 * @method \Bitrix\Forum\EO_Message setForumUserTopic(\Bitrix\Forum\EO_UserTopic $object)
	 * @method \Bitrix\Forum\EO_Message resetForumUserTopic()
	 * @method \Bitrix\Forum\EO_Message unsetForumUserTopic()
	 * @method bool hasForumUserTopic()
	 * @method bool isForumUserTopicFilled()
	 * @method bool isForumUserTopicChanged()
	 * @method \Bitrix\Forum\EO_UserTopic fillForumUserTopic()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Forum\EO_Message setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Forum\EO_Message resetUser()
	 * @method \Bitrix\Forum\EO_Message unsetUser()
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
	 * @method \Bitrix\Forum\EO_Message set($fieldName, $value)
	 * @method \Bitrix\Forum\EO_Message reset($fieldName)
	 * @method \Bitrix\Forum\EO_Message unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Forum\EO_Message wakeUp($data)
	 */
	class EO_Message {
		/* @var \Bitrix\Forum\MessageTable */
		static public $dataClass = '\Bitrix\Forum\MessageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Forum {
	/**
	 * EO_Message_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getForumIdList()
	 * @method \int[] fillForumId()
	 * @method \int[] getTopicIdList()
	 * @method \int[] fillTopicId()
	 * @method \boolean[] getUseSmilesList()
	 * @method \boolean[] fillUseSmiles()
	 * @method \boolean[] getNewTopicList()
	 * @method \boolean[] fillNewTopic()
	 * @method \boolean[] getApprovedList()
	 * @method \boolean[] fillApproved()
	 * @method \boolean[] getSourceIdList()
	 * @method \boolean[] fillSourceId()
	 * @method \Bitrix\Main\Type\DateTime[] getPostDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillPostDate()
	 * @method \string[] getPostMessageList()
	 * @method \string[] fillPostMessage()
	 * @method \string[] getPostMessageHtmlList()
	 * @method \string[] fillPostMessageHtml()
	 * @method \string[] getPostMessageFilterList()
	 * @method \string[] fillPostMessageFilter()
	 * @method \string[] getPostMessageCheckList()
	 * @method \string[] fillPostMessageCheck()
	 * @method \int[] getAttachImgList()
	 * @method \int[] fillAttachImg()
	 * @method \string[] getParam1List()
	 * @method \string[] fillParam1()
	 * @method \int[] getParam2List()
	 * @method \int[] fillParam2()
	 * @method \int[] getAuthorIdList()
	 * @method \int[] fillAuthorId()
	 * @method \string[] getAuthorNameList()
	 * @method \string[] fillAuthorName()
	 * @method \string[] getAuthorEmailList()
	 * @method \string[] fillAuthorEmail()
	 * @method \string[] getAuthorIpList()
	 * @method \string[] fillAuthorIp()
	 * @method \string[] getAuthorRealIpList()
	 * @method \string[] fillAuthorRealIp()
	 * @method \int[] getGuestIdList()
	 * @method \int[] fillGuestId()
	 * @method \int[] getEditorIdList()
	 * @method \int[] fillEditorId()
	 * @method \string[] getEditorNameList()
	 * @method \string[] fillEditorName()
	 * @method \string[] getEditorEmailList()
	 * @method \string[] fillEditorEmail()
	 * @method \string[] getEditReasonList()
	 * @method \string[] fillEditReason()
	 * @method \Bitrix\Main\Type\DateTime[] getEditDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillEditDate()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getHtmlList()
	 * @method \string[] fillHtml()
	 * @method \string[] getMailHeaderList()
	 * @method \string[] fillMailHeader()
	 * @method \int[] getServiceTypeList()
	 * @method \int[] fillServiceType()
	 * @method \string[] getServiceDataList()
	 * @method \string[] fillServiceData()
	 * @method \Bitrix\Forum\EO_Topic[] getTopicList()
	 * @method \Bitrix\Forum\EO_Message_Collection getTopicCollection()
	 * @method \Bitrix\Forum\EO_Topic_Collection fillTopic()
	 * @method \Bitrix\Forum\EO_User[] getForumUserList()
	 * @method \Bitrix\Forum\EO_Message_Collection getForumUserCollection()
	 * @method \Bitrix\Forum\EO_User_Collection fillForumUser()
	 * @method \Bitrix\Forum\EO_UserTopic[] getForumUserTopicList()
	 * @method \Bitrix\Forum\EO_Message_Collection getForumUserTopicCollection()
	 * @method \Bitrix\Forum\EO_UserTopic_Collection fillForumUserTopic()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Forum\EO_Message_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Forum\EO_Message $object)
	 * @method bool has(\Bitrix\Forum\EO_Message $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Message getByPrimary($primary)
	 * @method \Bitrix\Forum\EO_Message[] getAll()
	 * @method bool remove(\Bitrix\Forum\EO_Message $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Forum\EO_Message_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Forum\EO_Message current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Message_Collection merge(?EO_Message_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Message_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Forum\MessageTable */
		static public $dataClass = '\Bitrix\Forum\MessageTable';
	}
}
namespace Bitrix\Forum {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Message_Result exec()
	 * @method \Bitrix\Forum\EO_Message fetchObject()
	 * @method \Bitrix\Forum\EO_Message_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Message_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Forum\EO_Message fetchObject()
	 * @method \Bitrix\Forum\EO_Message_Collection fetchCollection()
	 */
	class EO_Message_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Forum\EO_Message createObject($setDefaultValues = true)
	 * @method \Bitrix\Forum\EO_Message_Collection createCollection()
	 * @method \Bitrix\Forum\EO_Message wakeUpObject($row)
	 * @method \Bitrix\Forum\EO_Message_Collection wakeUpCollection($rows)
	 */
	class EO_Message_Entity extends \Bitrix\Main\ORM\Entity {}
}