<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class AnswerTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool mandatory default 'Y',
 * <li> TIMESTAMP_X datetime,
 * <li> QUESTION_ID int,
 * <li> C_SORT int,
 * <li> COUNTER int,
 * <li> MESSAGE text,
 * <li> MESSAGE_TYPE string(4),
 * <li> FIELD_TYPE int,
 * <li> COLOR string(7),
 * </ul>
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Answer_Query query()
 * @method static EO_Answer_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Answer_Result getById($id)
 * @method static EO_Answer_Result getList(array $parameters = array())
 * @method static EO_Answer_Entity getEntity()
 * @method static \Bitrix\Vote\EO_Answer createObject($setDefaultValues = true)
 * @method static \Bitrix\Vote\EO_Answer_Collection createCollection()
 * @method static \Bitrix\Vote\EO_Answer wakeUpObject($row)
 * @method static \Bitrix\Vote\EO_Answer_Collection wakeUpCollection($rows)
 */
class AnswerTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_answer';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),
			(new BooleanField('ACTIVE'))
				->configureValues('N', 'Y')
				->configureDefaultValue('Y'),
			(new DatetimeField('TIMESTAMP_X')),
			(new IntegerField('QUESTION_ID')),
			(new IntegerField('C_SORT')),
			(new IntegerField('IMAGE_ID')),
			(new Reference('IMAGE',
				\Bitrix\Main\FileTable::class,
				Join::on('this.IMAGE_ID', 'ref.ID')
			)),
			(new TextField('MESSAGE')),
			(new EnumField('MESSAGE_TYPE', ['values' => ['text', 'html']]))
				->configureDefaultValue('text'),
			(new IntegerField('COUNTER')),
			(new IntegerField('FIELD_TYPE')),
			(new IntegerField('FIELD_WIDTH')),
			(new IntegerField('FIELD_HEIGHT')),
			(new StringField('FIELD_PARAM'))
				->configureSize(255),
			(new StringField('COLOR'))
				->configureSize(6)
		];
	}
	/**
	 * @param array $id Answer IDs.
	 * @param mixed $increment True - increment, false - decrement, integer - exact value.
	 * @return void
	 */
	public static function setCounter(array $id, $increment = true)
	{
		$id = implode(", ", array_map('intval', $id));
		if (empty($id))
			return;
		$connection = \Bitrix\Main\Application::getInstance()->getConnection();

		$sql = intval($increment);
		if ($increment === true)
			$sql = "COUNTER+1";
		else if ($increment === false)
			$sql = "COUNTER-1";
		$connection->queryExecute("UPDATE ".self::getTableName()." SET COUNTER=".$sql." WHERE ID IN (".$id.")");
	}
}

class Answer
{
	public static $storage = array();
}