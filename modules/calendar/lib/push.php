<?php
namespace Bitrix\Calendar;

/**
 * Class PushTable
 *
 * Fields:
 * <ul>
 * <li> ENTITY_TYPE string(24) mandatory
 * <li> ENTITY_ID int mandatory
 * <li> CHANNEL_ID string(128) mandatory
 * <li> RESOURCE_ID string(128) mandatory
 * <li> EXPIRES datetime mandatory
 * <li> NOT_PROCESSED bool optional default 'N'
 * <li> FIRST_PUSH_DATE datetime optional
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Push_Query query()
 * @method static EO_Push_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Push_Result getById($id)
 * @method static EO_Push_Result getList(array $parameters = [])
 * @method static EO_Push_Entity getEntity()
 * @method static \Bitrix\Calendar\EO_Push createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\EO_Push_Collection createCollection()
 * @method static \Bitrix\Calendar\EO_Push wakeUpObject($row)
 * @method static \Bitrix\Calendar\EO_Push_Collection wakeUpCollection($rows)
 */
class PushTable extends Internals\PushTable
{
}
