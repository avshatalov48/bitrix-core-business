<?php
namespace Bitrix\Socialservices;

use Bitrix\Main,
	Bitrix\Main\ORM\Fields;

/**
 * Class ZoomMeetingRecordingsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MEETING_ID int mandatory
 * <li> AUDIO string(500) optional
 * <li> VIDEO string(500) optional
 * <li> CHAT string(500) optional
 * <li> DOWNLOAD_TOKEN string optional
 * <li> RECORDINGS_PASSWORD string(32) optional
 * </ul>
 *
 * @package Bitrix\Socialservices
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ZoomMeetingRecording_Query query()
 * @method static EO_ZoomMeetingRecording_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ZoomMeetingRecording_Result getById($id)
 * @method static EO_ZoomMeetingRecording_Result getList(array $parameters = array())
 * @method static EO_ZoomMeetingRecording_Entity getEntity()
 * @method static \Bitrix\Socialservices\EO_ZoomMeetingRecording createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialservices\EO_ZoomMeetingRecording_Collection createCollection()
 * @method static \Bitrix\Socialservices\EO_ZoomMeetingRecording wakeUpObject($row)
 * @method static \Bitrix\Socialservices\EO_ZoomMeetingRecording_Collection wakeUpCollection($rows)
 */

class ZoomMeetingRecordingTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_socialservices_zoom_meeting_recording';
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
			new Fields\StringField('EXTERNAL_ID', [
				'required' => true,
				'size' => 64
			]),
			new Fields\IntegerField('MEETING_ID', [
				'required' => true
			]),
			new Fields\DatetimeField('START_DATE', [
				'required' => true
			]),
			new Fields\DatetimeField('END_DATE', [
				'required' => true
			]),

			new Fields\StringField('FILE_TYPE'),
			new Fields\IntegerField('FILE_SIZE'),
			new Fields\StringField('PLAY_URL', [
				'size' => 500,
			]),
			new Fields\StringField('DOWNLOAD_URL', [
				'size' => 500,
			]),
			new Fields\StringField('RECORDING_TYPE', [
				'size' => 64,
			]),
			new Fields\CryptoField('DOWNLOAD_TOKEN', [
				'crypto_enabled' => static::cryptoEnabled('DOWNLOAD_TOKEN'),
			]),
			new Fields\CryptoField('PASSWORD', [
				'crypto_enabled' => static::cryptoEnabled('PASSWORD'),
			]),
			new Fields\IntegerField('FILE_ID'),

			new Fields\Relations\Reference(
				'MEETING',
				ZoomMeetingTable::class,
				['=this.MEETING_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),

		];
	}
}