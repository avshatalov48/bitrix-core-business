<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Sms;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Localization\Loc;

class TemplateTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_sms_template';
	}

	public static function getObjectClass()
	{
		return Template::class;
	}

	public static function getMap()
	{
		return array(
			(new Fields\IntegerField("ID"))
				->configurePrimary(true)
				->configureAutocomplete(true)
				->configureTitle(Loc::getMessage("sms_template_id_title")),

			(new Fields\StringField("EVENT_NAME"))
				->configureRequired(true)
				->configureTitle(Loc::getMessage("sms_template_event_name_title")),

			(new Fields\BooleanField("ACTIVE"))
				->configureStorageValues("N", "Y")
				->configureDefaultValue("Y")
				->configureTitle(Loc::getMessage("sms_template_active_title")),

			(new Fields\StringField("SENDER"))
				->configureRequired(true)
				->configureTitle(Loc::getMessage("sms_template_sender_title")),

			(new Fields\StringField("RECEIVER"))
				->configureRequired(true)
				->configureTitle(Loc::getMessage("sms_template_receiver_title")),

			(new Fields\TextField("MESSAGE"))
				->configureTitle(Loc::getMessage("sms_template_message_title")),

			(new Fields\StringField("LANGUAGE_ID"))
				->configureTitle(Loc::getMessage("sms_template_language_title")),

			(new Fields\Relations\ManyToMany('SITES', \Bitrix\Main\SiteTable::class))
				->configureMediatorTableName('b_sms_template_site')
				->configureRemotePrimary('LID', 'SITE_ID')
		);
	}

	public static function onDelete(ORM\Event $event)
	{
		$primary = $event->getParameter("id");

		$template = static::getEntity()->wakeUpObject($primary["ID"]);
		$template->removeAllSites();
		$template->save();
	}
}
