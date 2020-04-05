<?php
namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


class ExportProfileTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_tp_vk_profile';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('TP_VK_PROFILE_ID_FIELD'),
			)),
			
			new Entity\StringField('DESCRIPTION', array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateDesc'),
				'title' => Loc::getMessage('TP_VK_PROFILE_DESCRIPTION_FIELD'),
			)),

			new Entity\IntegerField('PLATFORM_ID', array(
				'required' => true,
				'title' => Loc::getMessage('TP_VK_PROFILE_PLATFORM_ID_FIELD'),
			)),

			new Entity\StringField('VK_SETTINGS', array(
				'required' => false,
				'serialized' => true,
				'title' => Loc::getMessage('TP_VK_PROFILE_VK_SETTINGS_FIELD'),
			)),

			new Entity\StringField('EXPORT_SETTINGS', array(
				'required' => false,
				'serialized' => true,
				'title' => Loc::getMessage('TP_VK_PROFILE_EXPORT_SETTINGS_FIELD'),
			)),

			new Entity\StringField('OAUTH', array(
				'required' => false,
				'serialized' => true,
				'title' => Loc::getMessage('TP_VK_PROFILE_OAUTH_SETTINGS_FIELD'),
			)),

			new Entity\StringField('PROCESS', array(
				'required' => false,
				'serialized' => true,
				'title' => Loc::getMessage('TP_VK_PROFILE_PROCESS_FIELD'),
			)),
			
			new Entity\StringField('JOURNAL', array(
				'required' => false,
				'serialized' => true,
				'title' => Loc::getMessage('TP_VK_PROFILE_JOURNAL_FIELD'),
			)),
		);
	}
	
	public static function validateDesc()
	{
		return array(
			new Entity\Validator\Length(3, 100),
		);
	}
}