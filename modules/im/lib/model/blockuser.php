<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class BlockUserTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_im_block_user';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('BLOCK_USER_ENTITY_ID_FIELD'),
			),
			'CHAT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BLOCK_USER_ENTITY_CHAT_ID_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BLOCK_USER_ENTITY_USER_ID_FIELD'),
			),
		);
	}
}