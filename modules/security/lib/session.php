<?php
namespace Bitrix\Security;


use Bitrix\Main\Session\Handlers\Table;

/*
CREATE TABLE b_sec_session
(
	SESSION_ID VARCHAR(250) NOT NULL,
	TIMESTAMP_X TIMESTAMP NOT NULL,
	SESSION_DATA LONGTEXT,
	PRIMARY KEY(SESSION_ID)
);
 */

/**
 * Class SessionTable
 * @since 16.0.0
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Session_Query query()
 * @method static EO_Session_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Session_Result getById($id)
 * @method static EO_Session_Result getList(array $parameters = array())
 * @method static EO_Session_Entity getEntity()
 * @method static \Bitrix\Security\EO_Session createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\EO_Session_Collection createCollection()
 * @method static \Bitrix\Security\EO_Session wakeUpObject($row)
 * @method static \Bitrix\Security\EO_Session_Collection wakeUpCollection($rows)
 */
class SessionTable extends Table\UserSessionTable
{
	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sec_session';
	}
}
