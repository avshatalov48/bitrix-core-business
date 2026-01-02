<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Type\DateTime;

/**
 * Class SiteToChatTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SiteToChat_Query query()
 * @method static EO_SiteToChat_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SiteToChat_Result getById($id)
 * @method static EO_SiteToChat_Result getList(array $parameters = [])
 * @method static EO_SiteToChat_Entity getEntity()
 * @method static \Bitrix\Landing\Copilot\Model\EO_SiteToChat createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Copilot\Model\EO_SiteToChat_Collection createCollection()
 * @method static \Bitrix\Landing\Copilot\Model\EO_SiteToChat wakeUpObject($row)
 * @method static \Bitrix\Landing\Copilot\Model\EO_SiteToChat_Collection wakeUpCollection($rows)
 */
class SiteToChatTable extends Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName(): string
	{
		return 'b_landing_copilot_site_to_chat';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap(): array
	{
		return [
			(new Fields\IntegerField('CHAT_ID'))
				->configurePrimary()
				->configureRequired()
			,
			(new Fields\IntegerField('SITE_ID'))
				->configurePrimary()
				->configureRequired()
			,
			(new Fields\IntegerField('USER_ID'))
				->configurePrimary()
				->configureRequired()
			,
		];
	}
}
