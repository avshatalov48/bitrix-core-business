<?php
namespace Bitrix\Socialservices;

use Bitrix\Main,
	Bitrix\Main\ORM\Fields;

/**
 * Class ZoomMeetingTable
 *
 * @package Bitrix\Socialservices
 **/

class ZoomMeetingTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_socialservices_zoom_meeting';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws Main\SystemException
	 */
	public static function getMap(): array
	{
		return [
			new Fields\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true
			]),
			new Fields\StringField('ENTITY_TYPE_ID', [
				'required' => true,
				'size' => 10,
			]),
			new Fields\IntegerField('ENTITY_ID', [
				'required' => true
			]),
			new Fields\StringField('CONFERENCE_URL', [
				'required' => true,
				'size' => 255,
			]),
			new Fields\IntegerField('CONFERENCE_EXTERNAL_ID', [
				'required' => true,
			]),
			new Fields\CryptoField('CONFERENCE_PASSWORD', [
				'crypto_enabled' => static::cryptoEnabled('CONFERENCE_PASSWORD'),
			]),
			new Fields\BooleanField('JOINED', [
				'values' => ['N', 'Y']
			]),
			new Fields\DatetimeField('CONFERENCE_CREATED',[
				'required' => true,
			]),
			new Fields\DatetimeField('CONFERENCE_STARTED'),
			new Fields\DatetimeField('CONFERENCE_ENDED'),
			new Fields\BooleanField('HAS_RECORDING', [
				'values' => ['N', 'Y']
			]),
			new Fields\IntegerField('DURATION', [
				'required' => true,
			]),
			new Fields\TextField('TITLE', [
				'required' => true,
			]),
			new Fields\StringField('SHORT_LINK', [
				'required' => true,
				'size' => 255,
			])
		];
	}

	public static function getRowByExternalId($externalId)
	{
		return static::getRow([
			'select' => ['*'],
			'filter' => ['=CONFERENCE_EXTERNAL_ID' => $externalId]
		]);
	}
}