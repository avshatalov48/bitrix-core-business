<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class ReactionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CHAT_ID int mandatory
 * <li> MESSAGE_ID int mandatory
 * <li> USER_ID int mandatory
 * <li> REACTION string(50) mandatory
 * <li> DATE_CREATE datetime mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Reaction_Query query()
 * @method static EO_Reaction_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Reaction_Result getById($id)
 * @method static EO_Reaction_Result getList(array $parameters = [])
 * @method static EO_Reaction_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_Reaction createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_Reaction_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_Reaction wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_Reaction_Collection wakeUpCollection($rows)
 */

class ReactionTable extends DataManager
{
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_reaction';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			'CHAT_ID' => new IntegerField(
				'CHAT_ID',
				[
					'required' => true,
				]
			),
			'MESSAGE_ID' => new IntegerField(
				'MESSAGE_ID',
				[
					'required' => true,
				]
			),
			'USER_ID' => new IntegerField(
				'USER_ID',
				[
					'required' => true,
				]
			),
			'REACTION' => new StringField(
				'REACTION',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateReaction'],
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'required' => true,
				]
			),
			'COUNT' => new ExpressionField(
				'COUNT',
				'COUNT(*)'
			),
			'USERS_GROUP' => new ExpressionField(
				'USERS',
				'GROUP_CONCAT(%s)',
				['USER_ID']
			),
		];
	}

	/**
	 * Returns validators for REACTION field.
	 *
	 * @return array
	 */
	public static function validateReaction(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}
}