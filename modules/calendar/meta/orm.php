<?php

/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\EventTable:calendar/lib/internals/event.php:9d5a4f8daaf8f22a729db08d21a74877 */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Event
	 * @see \Bitrix\Calendar\Internals\EventTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetParentId()
	 * @method \int fillParentId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Calendar\Internals\EO_Event setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetActive()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getDeleted()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDeleted(\boolean|\Bitrix\Main\DB\SqlExpression $deleted)
	 * @method bool hasDeleted()
	 * @method bool isDeletedFilled()
	 * @method bool isDeletedChanged()
	 * @method \boolean remindActualDeleted()
	 * @method \boolean requireDeleted()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDeleted()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDeleted()
	 * @method \boolean fillDeleted()
	 * @method \string getCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Event setCalType(\string|\Bitrix\Main\DB\SqlExpression $calType)
	 * @method bool hasCalType()
	 * @method bool isCalTypeFilled()
	 * @method bool isCalTypeChanged()
	 * @method \string remindActualCalType()
	 * @method \string requireCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetCalType()
	 * @method \string fillCalType()
	 * @method \int getOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setOwnerId(\int|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \int remindActualOwnerId()
	 * @method \int requireOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetOwnerId()
	 * @method \int fillOwnerId()
	 * @method \string getName()
	 * @method \Bitrix\Calendar\Internals\EO_Event setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetName()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetName()
	 * @method \string fillName()
	 * @method \Bitrix\Main\Type\DateTime getDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateFrom)
	 * @method bool hasDateFrom()
	 * @method bool isDateFromFilled()
	 * @method bool isDateFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateFrom()
	 * @method \Bitrix\Main\Type\DateTime requireDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateFrom()
	 * @method \Bitrix\Main\Type\DateTime fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime getDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateTo)
	 * @method bool hasDateTo()
	 * @method bool isDateToFilled()
	 * @method bool isDateToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateTo()
	 * @method \Bitrix\Main\Type\DateTime requireDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateTo()
	 * @method \Bitrix\Main\Type\DateTime fillDateTo()
	 * @method \string getTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTzFrom(\string|\Bitrix\Main\DB\SqlExpression $tzFrom)
	 * @method bool hasTzFrom()
	 * @method bool isTzFromFilled()
	 * @method bool isTzFromChanged()
	 * @method \string remindActualTzFrom()
	 * @method \string requireTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTzFrom()
	 * @method \string fillTzFrom()
	 * @method \string getTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTzTo(\string|\Bitrix\Main\DB\SqlExpression $tzTo)
	 * @method bool hasTzTo()
	 * @method bool isTzToFilled()
	 * @method bool isTzToChanged()
	 * @method \string remindActualTzTo()
	 * @method \string requireTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTzTo()
	 * @method \string fillTzTo()
	 * @method \int getTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTzOffsetFrom(\int|\Bitrix\Main\DB\SqlExpression $tzOffsetFrom)
	 * @method bool hasTzOffsetFrom()
	 * @method bool isTzOffsetFromFilled()
	 * @method bool isTzOffsetFromChanged()
	 * @method \int remindActualTzOffsetFrom()
	 * @method \int requireTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTzOffsetFrom()
	 * @method \int fillTzOffsetFrom()
	 * @method \int getTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTzOffsetTo(\int|\Bitrix\Main\DB\SqlExpression $tzOffsetTo)
	 * @method bool hasTzOffsetTo()
	 * @method bool isTzOffsetToFilled()
	 * @method bool isTzOffsetToChanged()
	 * @method \int remindActualTzOffsetTo()
	 * @method \int requireTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTzOffsetTo()
	 * @method \int fillTzOffsetTo()
	 * @method \int getDateFromTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateFromTsUtc(\int|\Bitrix\Main\DB\SqlExpression $dateFromTsUtc)
	 * @method bool hasDateFromTsUtc()
	 * @method bool isDateFromTsUtcFilled()
	 * @method bool isDateFromTsUtcChanged()
	 * @method \int remindActualDateFromTsUtc()
	 * @method \int requireDateFromTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateFromTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateFromTsUtc()
	 * @method \int fillDateFromTsUtc()
	 * @method \int getDateToTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateToTsUtc(\int|\Bitrix\Main\DB\SqlExpression $dateToTsUtc)
	 * @method bool hasDateToTsUtc()
	 * @method bool isDateToTsUtcFilled()
	 * @method bool isDateToTsUtcChanged()
	 * @method \int remindActualDateToTsUtc()
	 * @method \int requireDateToTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateToTsUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateToTsUtc()
	 * @method \int fillDateToTsUtc()
	 * @method \boolean getDtSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDtSkipTime(\boolean|\Bitrix\Main\DB\SqlExpression $dtSkipTime)
	 * @method bool hasDtSkipTime()
	 * @method bool isDtSkipTimeFilled()
	 * @method bool isDtSkipTimeChanged()
	 * @method \boolean remindActualDtSkipTime()
	 * @method \boolean requireDtSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDtSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDtSkipTime()
	 * @method \boolean fillDtSkipTime()
	 * @method \int getDtLength()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDtLength(\int|\Bitrix\Main\DB\SqlExpression $dtLength)
	 * @method bool hasDtLength()
	 * @method bool isDtLengthFilled()
	 * @method bool isDtLengthChanged()
	 * @method \int remindActualDtLength()
	 * @method \int requireDtLength()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDtLength()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDtLength()
	 * @method \int fillDtLength()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Event setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getPrivateEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event setPrivateEvent(\string|\Bitrix\Main\DB\SqlExpression $privateEvent)
	 * @method bool hasPrivateEvent()
	 * @method bool isPrivateEventFilled()
	 * @method bool isPrivateEventChanged()
	 * @method \string remindActualPrivateEvent()
	 * @method \string requirePrivateEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetPrivateEvent()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetPrivateEvent()
	 * @method \string fillPrivateEvent()
	 * @method \string getAccessibility()
	 * @method \Bitrix\Calendar\Internals\EO_Event setAccessibility(\string|\Bitrix\Main\DB\SqlExpression $accessibility)
	 * @method bool hasAccessibility()
	 * @method bool isAccessibilityFilled()
	 * @method bool isAccessibilityChanged()
	 * @method \string remindActualAccessibility()
	 * @method \string requireAccessibility()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetAccessibility()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetAccessibility()
	 * @method \string fillAccessibility()
	 * @method \string getImportance()
	 * @method \Bitrix\Calendar\Internals\EO_Event setImportance(\string|\Bitrix\Main\DB\SqlExpression $importance)
	 * @method bool hasImportance()
	 * @method bool isImportanceFilled()
	 * @method bool isImportanceChanged()
	 * @method \string remindActualImportance()
	 * @method \string requireImportance()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetImportance()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetImportance()
	 * @method \string fillImportance()
	 * @method \string getIsMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event setIsMeeting(\string|\Bitrix\Main\DB\SqlExpression $isMeeting)
	 * @method bool hasIsMeeting()
	 * @method bool isIsMeetingFilled()
	 * @method bool isIsMeetingChanged()
	 * @method \string remindActualIsMeeting()
	 * @method \string requireIsMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetIsMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetIsMeeting()
	 * @method \string fillIsMeeting()
	 * @method \string getMeetingStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Event setMeetingStatus(\string|\Bitrix\Main\DB\SqlExpression $meetingStatus)
	 * @method bool hasMeetingStatus()
	 * @method bool isMeetingStatusFilled()
	 * @method bool isMeetingStatusChanged()
	 * @method \string remindActualMeetingStatus()
	 * @method \string requireMeetingStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetMeetingStatus()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetMeetingStatus()
	 * @method \string fillMeetingStatus()
	 * @method \int getMeetingHost()
	 * @method \Bitrix\Calendar\Internals\EO_Event setMeetingHost(\int|\Bitrix\Main\DB\SqlExpression $meetingHost)
	 * @method bool hasMeetingHost()
	 * @method bool isMeetingHostFilled()
	 * @method bool isMeetingHostChanged()
	 * @method \int remindActualMeetingHost()
	 * @method \int requireMeetingHost()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetMeetingHost()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetMeetingHost()
	 * @method \int fillMeetingHost()
	 * @method \string getMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event setMeeting(\string|\Bitrix\Main\DB\SqlExpression $meeting)
	 * @method bool hasMeeting()
	 * @method bool isMeetingFilled()
	 * @method bool isMeetingChanged()
	 * @method \string remindActualMeeting()
	 * @method \string requireMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetMeeting()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetMeeting()
	 * @method \string fillMeeting()
	 * @method \string getLocation()
	 * @method \Bitrix\Calendar\Internals\EO_Event setLocation(\string|\Bitrix\Main\DB\SqlExpression $location)
	 * @method bool hasLocation()
	 * @method bool isLocationFilled()
	 * @method bool isLocationChanged()
	 * @method \string remindActualLocation()
	 * @method \string requireLocation()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetLocation()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetLocation()
	 * @method \string fillLocation()
	 * @method \string getRemind()
	 * @method \Bitrix\Calendar\Internals\EO_Event setRemind(\string|\Bitrix\Main\DB\SqlExpression $remind)
	 * @method bool hasRemind()
	 * @method bool isRemindFilled()
	 * @method bool isRemindChanged()
	 * @method \string remindActualRemind()
	 * @method \string requireRemind()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetRemind()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetRemind()
	 * @method \string fillRemind()
	 * @method \string getColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event setColor(\string|\Bitrix\Main\DB\SqlExpression $color)
	 * @method bool hasColor()
	 * @method bool isColorFilled()
	 * @method bool isColorChanged()
	 * @method \string remindActualColor()
	 * @method \string requireColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetColor()
	 * @method \string fillColor()
	 * @method \string getTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event setTextColor(\string|\Bitrix\Main\DB\SqlExpression $textColor)
	 * @method bool hasTextColor()
	 * @method bool isTextColorFilled()
	 * @method bool isTextColorChanged()
	 * @method \string remindActualTextColor()
	 * @method \string requireTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetTextColor()
	 * @method \string fillTextColor()
	 * @method \string getRrule()
	 * @method \Bitrix\Calendar\Internals\EO_Event setRrule(\string|\Bitrix\Main\DB\SqlExpression $rrule)
	 * @method bool hasRrule()
	 * @method bool isRruleFilled()
	 * @method bool isRruleChanged()
	 * @method \string remindActualRrule()
	 * @method \string requireRrule()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetRrule()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetRrule()
	 * @method \string fillRrule()
	 * @method \string getExdate()
	 * @method \Bitrix\Calendar\Internals\EO_Event setExdate(\string|\Bitrix\Main\DB\SqlExpression $exdate)
	 * @method bool hasExdate()
	 * @method bool isExdateFilled()
	 * @method bool isExdateChanged()
	 * @method \string remindActualExdate()
	 * @method \string requireExdate()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetExdate()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetExdate()
	 * @method \string fillExdate()
	 * @method \string getDavXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDavXmlId(\string|\Bitrix\Main\DB\SqlExpression $davXmlId)
	 * @method bool hasDavXmlId()
	 * @method bool isDavXmlIdFilled()
	 * @method bool isDavXmlIdChanged()
	 * @method \string remindActualDavXmlId()
	 * @method \string requireDavXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDavXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDavXmlId()
	 * @method \string fillDavXmlId()
	 * @method \string getDavExchLabel()
	 * @method \Bitrix\Calendar\Internals\EO_Event setDavExchLabel(\string|\Bitrix\Main\DB\SqlExpression $davExchLabel)
	 * @method bool hasDavExchLabel()
	 * @method bool isDavExchLabelFilled()
	 * @method bool isDavExchLabelChanged()
	 * @method \string remindActualDavExchLabel()
	 * @method \string requireDavExchLabel()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetDavExchLabel()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetDavExchLabel()
	 * @method \string fillDavExchLabel()
	 * @method \string getVersion()
	 * @method \Bitrix\Calendar\Internals\EO_Event setVersion(\string|\Bitrix\Main\DB\SqlExpression $version)
	 * @method bool hasVersion()
	 * @method bool isVersionFilled()
	 * @method bool isVersionChanged()
	 * @method \string remindActualVersion()
	 * @method \string requireVersion()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetVersion()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetVersion()
	 * @method \string fillVersion()
	 * @method \string getAttendeesCodes()
	 * @method \Bitrix\Calendar\Internals\EO_Event setAttendeesCodes(\string|\Bitrix\Main\DB\SqlExpression $attendeesCodes)
	 * @method bool hasAttendeesCodes()
	 * @method bool isAttendeesCodesFilled()
	 * @method bool isAttendeesCodesChanged()
	 * @method \string remindActualAttendeesCodes()
	 * @method \string requireAttendeesCodes()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetAttendeesCodes()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetAttendeesCodes()
	 * @method \string fillAttendeesCodes()
	 * @method \int getRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setRecurrenceId(\int|\Bitrix\Main\DB\SqlExpression $recurrenceId)
	 * @method bool hasRecurrenceId()
	 * @method bool isRecurrenceIdFilled()
	 * @method bool isRecurrenceIdChanged()
	 * @method \int remindActualRecurrenceId()
	 * @method \int requireRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetRecurrenceId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetRecurrenceId()
	 * @method \int fillRecurrenceId()
	 * @method \int getRelations()
	 * @method \Bitrix\Calendar\Internals\EO_Event setRelations(\int|\Bitrix\Main\DB\SqlExpression $relations)
	 * @method bool hasRelations()
	 * @method bool isRelationsFilled()
	 * @method bool isRelationsChanged()
	 * @method \int remindActualRelations()
	 * @method \int requireRelations()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetRelations()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetRelations()
	 * @method \int fillRelations()
	 * @method \string getSearchableContent()
	 * @method \Bitrix\Calendar\Internals\EO_Event setSearchableContent(\string|\Bitrix\Main\DB\SqlExpression $searchableContent)
	 * @method bool hasSearchableContent()
	 * @method bool isSearchableContentFilled()
	 * @method bool isSearchableContentChanged()
	 * @method \string remindActualSearchableContent()
	 * @method \string requireSearchableContent()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetSearchableContent()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetSearchableContent()
	 * @method \string fillSearchableContent()
	 * @method \int getSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setSectionId(\int|\Bitrix\Main\DB\SqlExpression $sectionId)
	 * @method bool hasSectionId()
	 * @method bool isSectionIdFilled()
	 * @method bool isSectionIdChanged()
	 * @method \int remindActualSectionId()
	 * @method \int requireSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetSectionId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetSectionId()
	 * @method \int fillSectionId()
	 * @method \string getGEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Event setGEventId(\string|\Bitrix\Main\DB\SqlExpression $gEventId)
	 * @method bool hasGEventId()
	 * @method bool isGEventIdFilled()
	 * @method bool isGEventIdChanged()
	 * @method \string remindActualGEventId()
	 * @method \string requireGEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetGEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetGEventId()
	 * @method \string fillGEventId()
	 * @method \Bitrix\Main\Type\DateTime getOriginalDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event setOriginalDateFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $originalDateFrom)
	 * @method bool hasOriginalDateFrom()
	 * @method bool isOriginalDateFromFilled()
	 * @method bool isOriginalDateFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualOriginalDateFrom()
	 * @method \Bitrix\Main\Type\DateTime requireOriginalDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event resetOriginalDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Event unsetOriginalDateFrom()
	 * @method \Bitrix\Main\Type\DateTime fillOriginalDateFrom()
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
	 * @method \Bitrix\Calendar\Internals\EO_Event set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Event reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Event unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Event wakeUp($data)
	 */
	class EO_Event {
		/* @var \Bitrix\Calendar\Internals\EventTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Event_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getDeletedList()
	 * @method \boolean[] fillDeleted()
	 * @method \string[] getCalTypeList()
	 * @method \string[] fillCalType()
	 * @method \int[] getOwnerIdList()
	 * @method \int[] fillOwnerId()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \Bitrix\Main\Type\DateTime[] getDateFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getDateToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateTo()
	 * @method \string[] getTzFromList()
	 * @method \string[] fillTzFrom()
	 * @method \string[] getTzToList()
	 * @method \string[] fillTzTo()
	 * @method \int[] getTzOffsetFromList()
	 * @method \int[] fillTzOffsetFrom()
	 * @method \int[] getTzOffsetToList()
	 * @method \int[] fillTzOffsetTo()
	 * @method \int[] getDateFromTsUtcList()
	 * @method \int[] fillDateFromTsUtc()
	 * @method \int[] getDateToTsUtcList()
	 * @method \int[] fillDateToTsUtc()
	 * @method \boolean[] getDtSkipTimeList()
	 * @method \boolean[] fillDtSkipTime()
	 * @method \int[] getDtLengthList()
	 * @method \int[] fillDtLength()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getPrivateEventList()
	 * @method \string[] fillPrivateEvent()
	 * @method \string[] getAccessibilityList()
	 * @method \string[] fillAccessibility()
	 * @method \string[] getImportanceList()
	 * @method \string[] fillImportance()
	 * @method \string[] getIsMeetingList()
	 * @method \string[] fillIsMeeting()
	 * @method \string[] getMeetingStatusList()
	 * @method \string[] fillMeetingStatus()
	 * @method \int[] getMeetingHostList()
	 * @method \int[] fillMeetingHost()
	 * @method \string[] getMeetingList()
	 * @method \string[] fillMeeting()
	 * @method \string[] getLocationList()
	 * @method \string[] fillLocation()
	 * @method \string[] getRemindList()
	 * @method \string[] fillRemind()
	 * @method \string[] getColorList()
	 * @method \string[] fillColor()
	 * @method \string[] getTextColorList()
	 * @method \string[] fillTextColor()
	 * @method \string[] getRruleList()
	 * @method \string[] fillRrule()
	 * @method \string[] getExdateList()
	 * @method \string[] fillExdate()
	 * @method \string[] getDavXmlIdList()
	 * @method \string[] fillDavXmlId()
	 * @method \string[] getDavExchLabelList()
	 * @method \string[] fillDavExchLabel()
	 * @method \string[] getVersionList()
	 * @method \string[] fillVersion()
	 * @method \string[] getAttendeesCodesList()
	 * @method \string[] fillAttendeesCodes()
	 * @method \int[] getRecurrenceIdList()
	 * @method \int[] fillRecurrenceId()
	 * @method \int[] getRelationsList()
	 * @method \int[] fillRelations()
	 * @method \string[] getSearchableContentList()
	 * @method \string[] fillSearchableContent()
	 * @method \int[] getSectionIdList()
	 * @method \int[] fillSectionId()
	 * @method \string[] getGEventIdList()
	 * @method \string[] fillGEventId()
	 * @method \Bitrix\Main\Type\DateTime[] getOriginalDateFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillOriginalDateFrom()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Event getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Event[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Event $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Event_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Event current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Event_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\EventTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\EventTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * @method static EO_Event_Query query()
	 * @method static EO_Event_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Event_Result getById($id)
	 * @method static EO_Event_Result getList(array $parameters = array())
	 * @method static EO_Event_Entity getEntity()
	 * @method static \Bitrix\Calendar\Internals\EO_Event createObject($setDefaultValues = true)
	 * @method static \Bitrix\Calendar\Internals\EO_Event_Collection createCollection()
	 * @method static \Bitrix\Calendar\Internals\EO_Event wakeUpObject($row)
	 * @method static \Bitrix\Calendar\Internals\EO_Event_Collection wakeUpCollection($rows)
	 */
	class EventTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Event_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Event fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Event_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Event fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection fetchCollection()
	 */
	class EO_Event_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Event createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Event wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Event_Collection wakeUpCollection($rows)
	 */
	class EO_Event_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\ResourceTable:calendar/lib/internals/resource.php:a0e033ab423236d6bc85b67216fe50ef */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Resource
	 * @see \Bitrix\Calendar\Internals\ResourceTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setEventId(\int|\Bitrix\Main\DB\SqlExpression $eventId)
	 * @method bool hasEventId()
	 * @method bool isEventIdFilled()
	 * @method bool isEventIdChanged()
	 * @method \int remindActualEventId()
	 * @method \int requireEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetEventId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetEventId()
	 * @method \int fillEventId()
	 * @method \string getCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setCalType(\string|\Bitrix\Main\DB\SqlExpression $calType)
	 * @method bool hasCalType()
	 * @method bool isCalTypeFilled()
	 * @method bool isCalTypeChanged()
	 * @method \string remindActualCalType()
	 * @method \string requireCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetCalType()
	 * @method \string fillCalType()
	 * @method \int getResourceId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setResourceId(\int|\Bitrix\Main\DB\SqlExpression $resourceId)
	 * @method bool hasResourceId()
	 * @method bool isResourceIdFilled()
	 * @method bool isResourceIdChanged()
	 * @method \int remindActualResourceId()
	 * @method \int requireResourceId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetResourceId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetResourceId()
	 * @method \int fillResourceId()
	 * @method \string getParentType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setParentType(\string|\Bitrix\Main\DB\SqlExpression $parentType)
	 * @method bool hasParentType()
	 * @method bool isParentTypeFilled()
	 * @method bool isParentTypeChanged()
	 * @method \string remindActualParentType()
	 * @method \string requireParentType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetParentType()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetParentType()
	 * @method \string fillParentType()
	 * @method \int getParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetParentId()
	 * @method \int fillParentId()
	 * @method \int getUfId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setUfId(\int|\Bitrix\Main\DB\SqlExpression $ufId)
	 * @method bool hasUfId()
	 * @method bool isUfIdFilled()
	 * @method bool isUfIdChanged()
	 * @method \int remindActualUfId()
	 * @method \int requireUfId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetUfId()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetUfId()
	 * @method \int fillUfId()
	 * @method \Bitrix\Main\Type\DateTime getDateFromUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateFromUtc(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateFromUtc)
	 * @method bool hasDateFromUtc()
	 * @method bool isDateFromUtcFilled()
	 * @method bool isDateFromUtcChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateFromUtc()
	 * @method \Bitrix\Main\Type\DateTime requireDateFromUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateFromUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateFromUtc()
	 * @method \Bitrix\Main\Type\DateTime fillDateFromUtc()
	 * @method \Bitrix\Main\Type\DateTime getDateToUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateToUtc(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateToUtc)
	 * @method bool hasDateToUtc()
	 * @method bool isDateToUtcFilled()
	 * @method bool isDateToUtcChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateToUtc()
	 * @method \Bitrix\Main\Type\DateTime requireDateToUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateToUtc()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateToUtc()
	 * @method \Bitrix\Main\Type\DateTime fillDateToUtc()
	 * @method \Bitrix\Main\Type\DateTime getDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateFrom(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateFrom)
	 * @method bool hasDateFrom()
	 * @method bool isDateFromFilled()
	 * @method bool isDateFromChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateFrom()
	 * @method \Bitrix\Main\Type\DateTime requireDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateFrom()
	 * @method \Bitrix\Main\Type\DateTime fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime getDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateTo(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateTo)
	 * @method bool hasDateTo()
	 * @method bool isDateToFilled()
	 * @method bool isDateToChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateTo()
	 * @method \Bitrix\Main\Type\DateTime requireDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateTo()
	 * @method \Bitrix\Main\Type\DateTime fillDateTo()
	 * @method \int getDuration()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDuration(\int|\Bitrix\Main\DB\SqlExpression $duration)
	 * @method bool hasDuration()
	 * @method bool isDurationFilled()
	 * @method bool isDurationChanged()
	 * @method \int remindActualDuration()
	 * @method \int requireDuration()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDuration()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDuration()
	 * @method \int fillDuration()
	 * @method \string getSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setSkipTime(\string|\Bitrix\Main\DB\SqlExpression $skipTime)
	 * @method bool hasSkipTime()
	 * @method bool isSkipTimeFilled()
	 * @method bool isSkipTimeChanged()
	 * @method \string remindActualSkipTime()
	 * @method \string requireSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetSkipTime()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetSkipTime()
	 * @method \string fillSkipTime()
	 * @method \string getTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTzFrom(\string|\Bitrix\Main\DB\SqlExpression $tzFrom)
	 * @method bool hasTzFrom()
	 * @method bool isTzFromFilled()
	 * @method bool isTzFromChanged()
	 * @method \string remindActualTzFrom()
	 * @method \string requireTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTzFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTzFrom()
	 * @method \string fillTzFrom()
	 * @method \string getTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTzTo(\string|\Bitrix\Main\DB\SqlExpression $tzTo)
	 * @method bool hasTzTo()
	 * @method bool isTzToFilled()
	 * @method bool isTzToChanged()
	 * @method \string remindActualTzTo()
	 * @method \string requireTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTzTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTzTo()
	 * @method \string fillTzTo()
	 * @method \int getTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTzOffsetFrom(\int|\Bitrix\Main\DB\SqlExpression $tzOffsetFrom)
	 * @method bool hasTzOffsetFrom()
	 * @method bool isTzOffsetFromFilled()
	 * @method bool isTzOffsetFromChanged()
	 * @method \int remindActualTzOffsetFrom()
	 * @method \int requireTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTzOffsetFrom()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTzOffsetFrom()
	 * @method \int fillTzOffsetFrom()
	 * @method \int getTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTzOffsetTo(\int|\Bitrix\Main\DB\SqlExpression $tzOffsetTo)
	 * @method bool hasTzOffsetTo()
	 * @method bool isTzOffsetToFilled()
	 * @method bool isTzOffsetToChanged()
	 * @method \int remindActualTzOffsetTo()
	 * @method \int requireTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTzOffsetTo()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTzOffsetTo()
	 * @method \int fillTzOffsetTo()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getServiceName()
	 * @method \Bitrix\Calendar\Internals\EO_Resource setServiceName(\string|\Bitrix\Main\DB\SqlExpression $serviceName)
	 * @method bool hasServiceName()
	 * @method bool isServiceNameFilled()
	 * @method bool isServiceNameChanged()
	 * @method \string remindActualServiceName()
	 * @method \string requireServiceName()
	 * @method \Bitrix\Calendar\Internals\EO_Resource resetServiceName()
	 * @method \Bitrix\Calendar\Internals\EO_Resource unsetServiceName()
	 * @method \string fillServiceName()
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
	 * @method \Bitrix\Calendar\Internals\EO_Resource set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Resource reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Resource unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Resource wakeUp($data)
	 */
	class EO_Resource {
		/* @var \Bitrix\Calendar\Internals\ResourceTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\ResourceTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Resource_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getEventIdList()
	 * @method \int[] fillEventId()
	 * @method \string[] getCalTypeList()
	 * @method \string[] fillCalType()
	 * @method \int[] getResourceIdList()
	 * @method \int[] fillResourceId()
	 * @method \string[] getParentTypeList()
	 * @method \string[] fillParentType()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \int[] getUfIdList()
	 * @method \int[] fillUfId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateFromUtcList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateFromUtc()
	 * @method \Bitrix\Main\Type\DateTime[] getDateToUtcList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateToUtc()
	 * @method \Bitrix\Main\Type\DateTime[] getDateFromList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateFrom()
	 * @method \Bitrix\Main\Type\DateTime[] getDateToList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateTo()
	 * @method \int[] getDurationList()
	 * @method \int[] fillDuration()
	 * @method \string[] getSkipTimeList()
	 * @method \string[] fillSkipTime()
	 * @method \string[] getTzFromList()
	 * @method \string[] fillTzFrom()
	 * @method \string[] getTzToList()
	 * @method \string[] fillTzTo()
	 * @method \int[] getTzOffsetFromList()
	 * @method \int[] fillTzOffsetFrom()
	 * @method \int[] getTzOffsetToList()
	 * @method \int[] fillTzOffsetTo()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getServiceNameList()
	 * @method \string[] fillServiceName()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Resource $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Resource $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Resource getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Resource[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Resource $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Resource_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Resource current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Resource_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\ResourceTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\ResourceTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * @method static EO_Resource_Query query()
	 * @method static EO_Resource_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Resource_Result getById($id)
	 * @method static EO_Resource_Result getList(array $parameters = array())
	 * @method static EO_Resource_Entity getEntity()
	 * @method static \Bitrix\Calendar\Internals\EO_Resource createObject($setDefaultValues = true)
	 * @method static \Bitrix\Calendar\Internals\EO_Resource_Collection createCollection()
	 * @method static \Bitrix\Calendar\Internals\EO_Resource wakeUpObject($row)
	 * @method static \Bitrix\Calendar\Internals\EO_Resource_Collection wakeUpCollection($rows)
	 */
	class ResourceTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Resource_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Resource fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Resource_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Resource_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Resource fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Resource_Collection fetchCollection()
	 */
	class EO_Resource_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Resource createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Resource_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Resource wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Resource_Collection wakeUpCollection($rows)
	 */
	class EO_Resource_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\SectionTable:calendar/lib/internals/section.php:4c7fcf03b9fe512d9beb59ee15768a89 */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Section
	 * @see \Bitrix\Calendar\Internals\SectionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Calendar\Internals\EO_Section setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetName()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetName()
	 * @method \string fillName()
	 * @method \string getXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string remindActualXmlId()
	 * @method \string requireXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetXmlId()
	 * @method \string fillXmlId()
	 * @method \string getExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Calendar\Internals\EO_Section setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetActive()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Section setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section setColor(\string|\Bitrix\Main\DB\SqlExpression $color)
	 * @method bool hasColor()
	 * @method bool isColorFilled()
	 * @method bool isColorChanged()
	 * @method \string remindActualColor()
	 * @method \string requireColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetColor()
	 * @method \string fillColor()
	 * @method \string getTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section setTextColor(\string|\Bitrix\Main\DB\SqlExpression $textColor)
	 * @method bool hasTextColor()
	 * @method bool isTextColorFilled()
	 * @method bool isTextColorChanged()
	 * @method \string remindActualTextColor()
	 * @method \string requireTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetTextColor()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetTextColor()
	 * @method \string fillTextColor()
	 * @method \string getExport()
	 * @method \Bitrix\Calendar\Internals\EO_Section setExport(\string|\Bitrix\Main\DB\SqlExpression $export)
	 * @method bool hasExport()
	 * @method bool isExportFilled()
	 * @method bool isExportChanged()
	 * @method \string remindActualExport()
	 * @method \string requireExport()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetExport()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetExport()
	 * @method \string fillExport()
	 * @method \int getSort()
	 * @method \Bitrix\Calendar\Internals\EO_Section setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetSort()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetSort()
	 * @method \int fillSort()
	 * @method \string getCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCalType(\string|\Bitrix\Main\DB\SqlExpression $calType)
	 * @method bool hasCalType()
	 * @method bool isCalTypeFilled()
	 * @method bool isCalTypeChanged()
	 * @method \string remindActualCalType()
	 * @method \string requireCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCalType()
	 * @method \string fillCalType()
	 * @method \int getOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setOwnerId(\int|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \int remindActualOwnerId()
	 * @method \int requireOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetOwnerId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetOwnerId()
	 * @method \int fillOwnerId()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCreatedBy()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \int getParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setParentId(\int|\Bitrix\Main\DB\SqlExpression $parentId)
	 * @method bool hasParentId()
	 * @method bool isParentIdFilled()
	 * @method bool isParentIdChanged()
	 * @method \int remindActualParentId()
	 * @method \int requireParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetParentId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetParentId()
	 * @method \int fillParentId()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Section setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetDateCreate()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime getTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Section setTimestampX(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $timestampX)
	 * @method bool hasTimestampX()
	 * @method bool isTimestampXFilled()
	 * @method bool isTimestampXChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualTimestampX()
	 * @method \Bitrix\Main\Type\DateTime requireTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetTimestampX()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetTimestampX()
	 * @method \Bitrix\Main\Type\DateTime fillTimestampX()
	 * @method \string getDavExchCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section setDavExchCal(\string|\Bitrix\Main\DB\SqlExpression $davExchCal)
	 * @method bool hasDavExchCal()
	 * @method bool isDavExchCalFilled()
	 * @method bool isDavExchCalChanged()
	 * @method \string remindActualDavExchCal()
	 * @method \string requireDavExchCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetDavExchCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetDavExchCal()
	 * @method \string fillDavExchCal()
	 * @method \string getDavExchMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section setDavExchMod(\string|\Bitrix\Main\DB\SqlExpression $davExchMod)
	 * @method bool hasDavExchMod()
	 * @method bool isDavExchModFilled()
	 * @method bool isDavExchModChanged()
	 * @method \string remindActualDavExchMod()
	 * @method \string requireDavExchMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetDavExchMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetDavExchMod()
	 * @method \string fillDavExchMod()
	 * @method \string getCalDavCon()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCalDavCon(\string|\Bitrix\Main\DB\SqlExpression $calDavCon)
	 * @method bool hasCalDavCon()
	 * @method bool isCalDavConFilled()
	 * @method bool isCalDavConChanged()
	 * @method \string remindActualCalDavCon()
	 * @method \string requireCalDavCon()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCalDavCon()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCalDavCon()
	 * @method \string fillCalDavCon()
	 * @method \string getCalDavCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCalDavCal(\string|\Bitrix\Main\DB\SqlExpression $calDavCal)
	 * @method bool hasCalDavCal()
	 * @method bool isCalDavCalFilled()
	 * @method bool isCalDavCalChanged()
	 * @method \string remindActualCalDavCal()
	 * @method \string requireCalDavCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCalDavCal()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCalDavCal()
	 * @method \string fillCalDavCal()
	 * @method \string getCalDavMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section setCalDavMod(\string|\Bitrix\Main\DB\SqlExpression $calDavMod)
	 * @method bool hasCalDavMod()
	 * @method bool isCalDavModFilled()
	 * @method bool isCalDavModChanged()
	 * @method \string remindActualCalDavMod()
	 * @method \string requireCalDavMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetCalDavMod()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetCalDavMod()
	 * @method \string fillCalDavMod()
	 * @method \string getIsExchange()
	 * @method \Bitrix\Calendar\Internals\EO_Section setIsExchange(\string|\Bitrix\Main\DB\SqlExpression $isExchange)
	 * @method bool hasIsExchange()
	 * @method bool isIsExchangeFilled()
	 * @method bool isIsExchangeChanged()
	 * @method \string remindActualIsExchange()
	 * @method \string requireIsExchange()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetIsExchange()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetIsExchange()
	 * @method \string fillIsExchange()
	 * @method \string getGapiCalendarId()
	 * @method \Bitrix\Calendar\Internals\EO_Section setGapiCalendarId(\string|\Bitrix\Main\DB\SqlExpression $gapiCalendarId)
	 * @method bool hasGapiCalendarId()
	 * @method bool isGapiCalendarIdFilled()
	 * @method bool isGapiCalendarIdChanged()
	 * @method \string remindActualGapiCalendarId()
	 * @method \string requireGapiCalendarId()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetGapiCalendarId()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetGapiCalendarId()
	 * @method \string fillGapiCalendarId()
	 * @method \string getSyncToken()
	 * @method \Bitrix\Calendar\Internals\EO_Section setSyncToken(\string|\Bitrix\Main\DB\SqlExpression $syncToken)
	 * @method bool hasSyncToken()
	 * @method bool isSyncTokenFilled()
	 * @method bool isSyncTokenChanged()
	 * @method \string remindActualSyncToken()
	 * @method \string requireSyncToken()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetSyncToken()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetSyncToken()
	 * @method \string fillSyncToken()
	 * @method \string getExternalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section setExternalType(\string|\Bitrix\Main\DB\SqlExpression $externalType)
	 * @method bool hasExternalType()
	 * @method bool isExternalTypeFilled()
	 * @method bool isExternalTypeChanged()
	 * @method \string remindActualExternalType()
	 * @method \string requireExternalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section resetExternalType()
	 * @method \Bitrix\Calendar\Internals\EO_Section unsetExternalType()
	 * @method \string fillExternalType()
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
	 * @method \Bitrix\Calendar\Internals\EO_Section set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Section reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Section unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Section wakeUp($data)
	 */
	class EO_Section {
		/* @var \Bitrix\Calendar\Internals\SectionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SectionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Section_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getXmlIdList()
	 * @method \string[] fillXmlId()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getColorList()
	 * @method \string[] fillColor()
	 * @method \string[] getTextColorList()
	 * @method \string[] fillTextColor()
	 * @method \string[] getExportList()
	 * @method \string[] fillExport()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \string[] getCalTypeList()
	 * @method \string[] fillCalType()
	 * @method \int[] getOwnerIdList()
	 * @method \int[] fillOwnerId()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \int[] getParentIdList()
	 * @method \int[] fillParentId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\Main\Type\DateTime[] getTimestampXList()
	 * @method \Bitrix\Main\Type\DateTime[] fillTimestampX()
	 * @method \string[] getDavExchCalList()
	 * @method \string[] fillDavExchCal()
	 * @method \string[] getDavExchModList()
	 * @method \string[] fillDavExchMod()
	 * @method \string[] getCalDavConList()
	 * @method \string[] fillCalDavCon()
	 * @method \string[] getCalDavCalList()
	 * @method \string[] fillCalDavCal()
	 * @method \string[] getCalDavModList()
	 * @method \string[] fillCalDavMod()
	 * @method \string[] getIsExchangeList()
	 * @method \string[] fillIsExchange()
	 * @method \string[] getGapiCalendarIdList()
	 * @method \string[] fillGapiCalendarId()
	 * @method \string[] getSyncTokenList()
	 * @method \string[] fillSyncToken()
	 * @method \string[] getExternalTypeList()
	 * @method \string[] fillExternalType()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Section $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Section $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Section getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Section[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Section $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Section_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Section current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Section_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\SectionTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\SectionTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * @method static EO_Section_Query query()
	 * @method static EO_Section_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Section_Result getById($id)
	 * @method static EO_Section_Result getList(array $parameters = array())
	 * @method static EO_Section_Entity getEntity()
	 * @method static \Bitrix\Calendar\Internals\EO_Section createObject($setDefaultValues = true)
	 * @method static \Bitrix\Calendar\Internals\EO_Section_Collection createCollection()
	 * @method static \Bitrix\Calendar\Internals\EO_Section wakeUpObject($row)
	 * @method static \Bitrix\Calendar\Internals\EO_Section_Collection wakeUpCollection($rows)
	 */
	class SectionTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Section_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Section fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Section_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Section fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection fetchCollection()
	 */
	class EO_Section_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Section createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Section wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Section_Collection wakeUpCollection($rows)
	 */
	class EO_Section_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\Internals\TypeTable:calendar/lib/internals/type.php:9dc76e50c63302cb31ced5314d656aa5 */
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Type
	 * @see \Bitrix\Calendar\Internals\TypeTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getXmlId()
	 * @method \Bitrix\Calendar\Internals\EO_Type setXmlId(\string|\Bitrix\Main\DB\SqlExpression $xmlId)
	 * @method bool hasXmlId()
	 * @method bool isXmlIdFilled()
	 * @method bool isXmlIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Calendar\Internals\EO_Type setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Calendar\Internals\EO_Type resetName()
	 * @method \Bitrix\Calendar\Internals\EO_Type unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Type setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Type resetDescription()
	 * @method \Bitrix\Calendar\Internals\EO_Type unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Type setExternalId(\string|\Bitrix\Main\DB\SqlExpression $externalId)
	 * @method bool hasExternalId()
	 * @method bool isExternalIdFilled()
	 * @method bool isExternalIdChanged()
	 * @method \string remindActualExternalId()
	 * @method \string requireExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Type resetExternalId()
	 * @method \Bitrix\Calendar\Internals\EO_Type unsetExternalId()
	 * @method \string fillExternalId()
	 * @method \boolean getActive()
	 * @method \Bitrix\Calendar\Internals\EO_Type setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Calendar\Internals\EO_Type resetActive()
	 * @method \Bitrix\Calendar\Internals\EO_Type unsetActive()
	 * @method \boolean fillActive()
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
	 * @method \Bitrix\Calendar\Internals\EO_Type set($fieldName, $value)
	 * @method \Bitrix\Calendar\Internals\EO_Type reset($fieldName)
	 * @method \Bitrix\Calendar\Internals\EO_Type unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\Internals\EO_Type wakeUp($data)
	 */
	class EO_Type {
		/* @var \Bitrix\Calendar\Internals\TypeTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\TypeTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * EO_Type_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getXmlIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getExternalIdList()
	 * @method \string[] fillExternalId()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\Internals\EO_Type $object)
	 * @method bool has(\Bitrix\Calendar\Internals\EO_Type $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Type getByPrimary($primary)
	 * @method \Bitrix\Calendar\Internals\EO_Type[] getAll()
	 * @method bool remove(\Bitrix\Calendar\Internals\EO_Type $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\Internals\EO_Type_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\Internals\EO_Type current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Type_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\Internals\TypeTable */
		static public $dataClass = '\Bitrix\Calendar\Internals\TypeTable';
	}
}
namespace Bitrix\Calendar\Internals {
	/**
	 * @method static EO_Type_Query query()
	 * @method static EO_Type_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Type_Result getById($id)
	 * @method static EO_Type_Result getList(array $parameters = array())
	 * @method static EO_Type_Entity getEntity()
	 * @method static \Bitrix\Calendar\Internals\EO_Type createObject($setDefaultValues = true)
	 * @method static \Bitrix\Calendar\Internals\EO_Type_Collection createCollection()
	 * @method static \Bitrix\Calendar\Internals\EO_Type wakeUpObject($row)
	 * @method static \Bitrix\Calendar\Internals\EO_Type_Collection wakeUpCollection($rows)
	 */
	class TypeTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Type_Result exec()
	 * @method \Bitrix\Calendar\Internals\EO_Type fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Type_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Type_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Type fetchObject()
	 * @method \Bitrix\Calendar\Internals\EO_Type_Collection fetchCollection()
	 */
	class EO_Type_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\Internals\EO_Type createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\Internals\EO_Type_Collection createCollection()
	 * @method \Bitrix\Calendar\Internals\EO_Type wakeUpObject($row)
	 * @method \Bitrix\Calendar\Internals\EO_Type_Collection wakeUpCollection($rows)
	 */
	class EO_Type_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Calendar\PushTable:calendar/lib/push.php:9313b505532d3566752d7e2ade1ac191 */
namespace Bitrix\Calendar {
	/**
	 * EO_Push
	 * @see \Bitrix\Calendar\PushTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getEntityType()
	 * @method \Bitrix\Calendar\EO_Push setEntityType(\string|\Bitrix\Main\DB\SqlExpression $entityType)
	 * @method bool hasEntityType()
	 * @method bool isEntityTypeFilled()
	 * @method bool isEntityTypeChanged()
	 * @method \int getEntityId()
	 * @method \Bitrix\Calendar\EO_Push setEntityId(\int|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string getChannelId()
	 * @method \Bitrix\Calendar\EO_Push setChannelId(\string|\Bitrix\Main\DB\SqlExpression $channelId)
	 * @method bool hasChannelId()
	 * @method bool isChannelIdFilled()
	 * @method bool isChannelIdChanged()
	 * @method \string remindActualChannelId()
	 * @method \string requireChannelId()
	 * @method \Bitrix\Calendar\EO_Push resetChannelId()
	 * @method \Bitrix\Calendar\EO_Push unsetChannelId()
	 * @method \string fillChannelId()
	 * @method \string getResourceId()
	 * @method \Bitrix\Calendar\EO_Push setResourceId(\string|\Bitrix\Main\DB\SqlExpression $resourceId)
	 * @method bool hasResourceId()
	 * @method bool isResourceIdFilled()
	 * @method bool isResourceIdChanged()
	 * @method \string remindActualResourceId()
	 * @method \string requireResourceId()
	 * @method \Bitrix\Calendar\EO_Push resetResourceId()
	 * @method \Bitrix\Calendar\EO_Push unsetResourceId()
	 * @method \string fillResourceId()
	 * @method \Bitrix\Main\Type\DateTime getExpires()
	 * @method \Bitrix\Calendar\EO_Push setExpires(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $expires)
	 * @method bool hasExpires()
	 * @method bool isExpiresFilled()
	 * @method bool isExpiresChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualExpires()
	 * @method \Bitrix\Main\Type\DateTime requireExpires()
	 * @method \Bitrix\Calendar\EO_Push resetExpires()
	 * @method \Bitrix\Calendar\EO_Push unsetExpires()
	 * @method \Bitrix\Main\Type\DateTime fillExpires()
	 * @method \boolean getNotProcessed()
	 * @method \Bitrix\Calendar\EO_Push setNotProcessed(\boolean|\Bitrix\Main\DB\SqlExpression $notProcessed)
	 * @method bool hasNotProcessed()
	 * @method bool isNotProcessedFilled()
	 * @method bool isNotProcessedChanged()
	 * @method \boolean remindActualNotProcessed()
	 * @method \boolean requireNotProcessed()
	 * @method \Bitrix\Calendar\EO_Push resetNotProcessed()
	 * @method \Bitrix\Calendar\EO_Push unsetNotProcessed()
	 * @method \boolean fillNotProcessed()
	 * @method \Bitrix\Main\Type\DateTime getFirstPushDate()
	 * @method \Bitrix\Calendar\EO_Push setFirstPushDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $firstPushDate)
	 * @method bool hasFirstPushDate()
	 * @method bool isFirstPushDateFilled()
	 * @method bool isFirstPushDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualFirstPushDate()
	 * @method \Bitrix\Main\Type\DateTime requireFirstPushDate()
	 * @method \Bitrix\Calendar\EO_Push resetFirstPushDate()
	 * @method \Bitrix\Calendar\EO_Push unsetFirstPushDate()
	 * @method \Bitrix\Main\Type\DateTime fillFirstPushDate()
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
	 * @method \Bitrix\Calendar\EO_Push set($fieldName, $value)
	 * @method \Bitrix\Calendar\EO_Push reset($fieldName)
	 * @method \Bitrix\Calendar\EO_Push unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Calendar\EO_Push wakeUp($data)
	 */
	class EO_Push {
		/* @var \Bitrix\Calendar\PushTable */
		static public $dataClass = '\Bitrix\Calendar\PushTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Calendar {
	/**
	 * EO_Push_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getEntityTypeList()
	 * @method \int[] getEntityIdList()
	 * @method \string[] getChannelIdList()
	 * @method \string[] fillChannelId()
	 * @method \string[] getResourceIdList()
	 * @method \string[] fillResourceId()
	 * @method \Bitrix\Main\Type\DateTime[] getExpiresList()
	 * @method \Bitrix\Main\Type\DateTime[] fillExpires()
	 * @method \boolean[] getNotProcessedList()
	 * @method \boolean[] fillNotProcessed()
	 * @method \Bitrix\Main\Type\DateTime[] getFirstPushDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillFirstPushDate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Calendar\EO_Push $object)
	 * @method bool has(\Bitrix\Calendar\EO_Push $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Calendar\EO_Push getByPrimary($primary)
	 * @method \Bitrix\Calendar\EO_Push[] getAll()
	 * @method bool remove(\Bitrix\Calendar\EO_Push $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Calendar\EO_Push_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Calendar\EO_Push current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Push_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Calendar\PushTable */
		static public $dataClass = '\Bitrix\Calendar\PushTable';
	}
}
namespace Bitrix\Calendar {
	/**
	 * @method static EO_Push_Query query()
	 * @method static EO_Push_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Push_Result getById($id)
	 * @method static EO_Push_Result getList(array $parameters = array())
	 * @method static EO_Push_Entity getEntity()
	 * @method static \Bitrix\Calendar\EO_Push createObject($setDefaultValues = true)
	 * @method static \Bitrix\Calendar\EO_Push_Collection createCollection()
	 * @method static \Bitrix\Calendar\EO_Push wakeUpObject($row)
	 * @method static \Bitrix\Calendar\EO_Push_Collection wakeUpCollection($rows)
	 */
	class PushTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Push_Result exec()
	 * @method \Bitrix\Calendar\EO_Push fetchObject()
	 * @method \Bitrix\Calendar\EO_Push_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Push_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Calendar\EO_Push fetchObject()
	 * @method \Bitrix\Calendar\EO_Push_Collection fetchCollection()
	 */
	class EO_Push_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Calendar\EO_Push createObject($setDefaultValues = true)
	 * @method \Bitrix\Calendar\EO_Push_Collection createCollection()
	 * @method \Bitrix\Calendar\EO_Push wakeUpObject($row)
	 * @method \Bitrix\Calendar\EO_Push_Collection wakeUpCollection($rows)
	 */
	class EO_Push_Entity extends \Bitrix\Main\ORM\Entity {}
}