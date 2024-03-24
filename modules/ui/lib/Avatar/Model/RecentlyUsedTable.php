<?php
namespace Bitrix\UI\Avatar\Model;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\UI\Avatar;

class RecentlyUsedTable extends OrmDataManager
{
	public static function getTableName(): string
	{
		return 'b_ui_avatar_mask_recently_used';
	}

	public static function getMap(): array
	{
		return array(
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new IntegerField('ITEM_ID'))->configureRequired(),
			(new IntegerField('USER_ID', []))->configureRequired(),

			(new DatetimeField('TIMESTAMP_X'))
				->configureDefaultValue(function() {
					return new DateTime();
				}),

			(new Reference(
				'MASK',
				ItemTable::class,
				Join::on('this.ITEM_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
		);
	}

	public static function addFromUser($itemId, $userId)
	{
		$entity = static::getEntity();
		$sqlTableName = static::getTableName();
		$sqlHelper = $entity->getConnection()->getSqlHelper();
		$filter = ['ITEM_ID' => $itemId, 'USER_ID' => $userId];

		$where = Main\ORM\Query\Query::buildFilterSql($entity, $filter);
		if ($where !== '')
		{
			$sql = "DELETE FROM {$sqlHelper->quote($sqlTableName)} WHERE " . $where;
			$entity->getConnection()->queryExecute($sql);
		}
		static::add($filter);
		//Delete excessive data
		$counter = 10;
		$filter = ['=USER_ID' => $userId];
		$records = static::getList([
			'select' => ['ID'],
			'filter' => $filter,
			'limit' => $counter + 1,
			'order' => [
				'ID' => 'ASC'
			]
		])->fetchAll();
		if (count($records) > $counter)
		{
			$lastRecord = end($records);
			$filter['<ID'] = $lastRecord['ID'];
			$where = Main\ORM\Query\Query::buildFilterSql($entity, $filter);
			if ($where !== '')
			{
				$sql = "DELETE FROM {$sqlHelper->quote($sqlTableName)} WHERE " . $where;
				$entity->getConnection()->queryExecute($sql);
			}
		}
	}
}
