<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;

Loc::loadMessages(__FILE__);

/**
 * Class WorkgroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Workgroup_Query query()
 * @method static EO_Workgroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Workgroup_Result getById($id)
 * @method static EO_Workgroup_Result getList(array $parameters = [])
 * @method static EO_Workgroup_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_Workgroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_Workgroup_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_Workgroup wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_Workgroup_Collection wakeUpCollection($rows)
 */
class WorkgroupTable extends Entity\DataManager
{
	const AUTO_MEMBERSHIP_YES = 'Y';
	const AUTO_MEMBERSHIP_NO = 'N';

	public static function getAutoMembershipValuesAll()
	{
		return array(self::AUTO_MEMBERSHIP_NO, self::AUTO_MEMBERSHIP_YES);
	}

	public static function getTableName()
	{
		return 'b_sonet_group';
	}

	public static function getUfId()
	{
		return 'SONET_GROUP';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'SITE_ID' => array(
				'data_type' => 'string',
			),
			'SUBJECT_ID' => array(
				'data_type' => 'integer',
			),
			'WORKGROUP_SUBJECT' => array(
				'data_type' => '\Bitrix\Socialnetwork\WorkgroupSubject',
				'reference' => array('=this.SUBJECT_ID' => 'ref.ID')
			),
			'NAME' => array(
				'data_type' => 'string',
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'KEYWORDS' => array(
				'data_type' => 'string'
			),
			'CLOSED' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'VISIBLE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'OPENED' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime'
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime'
			),
			'DATE_ACTIVITY' => array(
				'data_type' => 'datetime'
			),
			'IMAGE_ID' => array(
				'data_type' => 'integer',
			),
			'AVATAR_TYPE' => [
				'data_type' => 'string',
			],
			'OWNER_ID' => array(
				'data_type' => 'integer',
			),
			'WORKGROUP_OWNER' => array(
				'data_type' => '\Bitrix\Main\User',
				'reference' => array('=this.OWNER_ID' => 'ref.ID')
			),
			'INITIATE_PERMS' => array(
				'data_type' => 'string'
			),
			'NUMBER_OF_MEMBERS' => array(
				'data_type' => 'integer',
			),
			'NUMBER_OF_MODERATORS' => array(
				'data_type' => 'integer',
			),
			'PROJECT' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'PROJECT_DATE_START' => array(
				'data_type' => 'datetime'
			),
			'PROJECT_DATE_FINISH' => array(
				'data_type' => 'datetime'
			),
			'SEARCH_INDEX' => array(
				'data_type' => 'text',
			),
			'LANDING' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'SCRUM_OWNER_ID' => array(
				'data_type' => 'integer',
			),
			'SCRUM_MASTER_ID' => array(
				'data_type' => 'integer',
			),
			'SCRUM_SPRINT_DURATION' => array(
				'data_type' => 'integer',
			),
			'SCRUM_TASK_RESPONSIBLE' => [
				'data_type' => 'string',
				'values' => ['A', 'M']
			],
		);

		return $fieldsMap;
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use CSocNetGroup class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use CSocNetGroup class.");
	}

	public static function delete($primary)
	{
		throw new NotImplementedException("Use CSocNetGroup class.");
	}
}
