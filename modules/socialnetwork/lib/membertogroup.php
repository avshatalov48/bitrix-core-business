<?php

namespace Bitrix\Socialnetwork;

use Bitrix\Socialnetwork\Space\Member;

/**
 * Class MemberToGroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MemberToGroup_Query query()
 * @method static EO_MemberToGroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_MemberToGroup_Result getById($id)
 * @method static EO_MemberToGroup_Result getList(array $parameters = [])
 * @method static EO_MemberToGroup_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Space\Member createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_MemberToGroup_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\Space\Member wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_MemberToGroup_Collection wakeUpCollection($rows)
 */
class MemberToGroupTable extends UserToGroupTable
{
	public static function getObjectClass(): string
	{
		return Member::class;
	}
}
