<?php
namespace Bitrix\MessageService\Internal\Entity;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\Web\Json;


class ChannelTable extends DataManager
{
	use MergeTrait;
	use DeleteByFilterTrait;

	/**
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_messageservice_channel';
	}

	/**
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' =>
				(new IntegerField('ID'))
					->configurePrimary()
					->configureAutocomplete()
			,
			'SENDER_ID' =>
				(new StringField('SENDER_ID'))
					->configureRequired()
					->configureSize(50)
					->addValidator(new LengthValidator(1, 50))
			,
			'EXTERNAL_ID' =>
				(new StringField('EXTERNAL_ID'))
					->configureRequired()
					->configureSize(128)
					->addValidator(new LengthValidator(1, 128))
			,
			'TYPE' =>
				(new StringField('TYPE'))
					->configureRequired()
					->configureSize(30)
					->addValidator(new LengthValidator(1, 30))
			,
			'NAME' =>
				(new StringField('NAME'))
					->configureRequired()
					->configureSize(500)
					->addValidator(new LengthValidator(1, 500))
			,
			'ADDITIONAL_PARAMS' =>
				(new ArrayField('ADDITIONAL_PARAMS'))
					->configureSerializationJson()
			,
			'DATE_CREATE' =>
				(new DatetimeField('DATE_CREATE'))
					->configureDefaultValue(function()
					{
						return new DateTime();
					})
			,
		];
	}

	public static function getChannelsByType(string $senderId, string $type): Result
	{
		return self::getList([
			'filter' => [
				'=SENDER_ID' => $senderId,
				'=TYPE' => $type,
			]
		]);
	}

	public static function reloadChannels(string $senderId, string $type, array $channels): void
	{
		foreach ($channels as $channel)
		{
			if (is_array($channel['ADDITIONAL_PARAMS']))
			{
				$channel['ADDITIONAL_PARAMS'] = Json::encode($channel['ADDITIONAL_PARAMS']);
			}
			self::merge(
				$channel,
				[
					'NAME' => $channel['NAME'],
					'ADDITIONAL_PARAMS' =>$channel['ADDITIONAL_PARAMS'],
				],
				['SENDER_ID', 'EXTERNAL_ID', 'TYPE']
			);
		}
		self::deleteByFilter([
			'=SENDER_ID' => $senderId,
			'=TYPE' => $type,
			'!=EXTERNAL_ID' => array_column($channels, 'EXTERNAL_ID')
		]);
	}
}