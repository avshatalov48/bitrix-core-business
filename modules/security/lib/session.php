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
