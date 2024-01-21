<?php

namespace Bitrix\Main\UI\EntitySelector;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

/**
 * Class EntityUsageTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EntityUsage_Query query()
 * @method static EO_EntityUsage_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EntityUsage_Result getById($id)
 * @method static EO_EntityUsage_Result getList(array $parameters = [])
 * @method static EO_EntityUsage_Entity getEntity()
 * @method static \Bitrix\Main\UI\EntitySelector\EO_EntityUsage createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection createCollection()
 * @method static \Bitrix\Main\UI\EntitySelector\EO_EntityUsage wakeUpObject($row)
 * @method static \Bitrix\Main\UI\EntitySelector\EO_EntityUsage_Collection wakeUpCollection($rows)
 */
class EntityUsageTable extends Data\DataManager
{
	use Data\Internal\DeleteByFilterTrait;

	/**
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return "b_entity_usage";
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap()
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		return [
			new Fields\IntegerField("USER_ID", [
				"primary" => true,
				"required" => true
			]),
			new Reference(
				"USER",
				UserTable::class,
				Join::on("this.USER_ID", "ref.ID"),
				["join_type" => "INNER"]
			),
			new Fields\StringField("ITEM_ID", [
				"primary" => true,
				"required" => true
			]),
			new Fields\StringField("ENTITY_ID", [
				"primary" => true,
				"required" => true
			]),
			new Fields\StringField("CONTEXT", [
				"primary" => true,
				"required" => true
			]),
			new Fields\IntegerField("ITEM_ID_INT", [
				"required" => true,
				"default_value" => 0
			]),
			new Fields\StringField("PREFIX", [
				"default_value" => ''
			]),
			new Fields\DatetimeField("LAST_USE_DATE", [
				"required" => true,
				"default_value" => function() {
					return new DateTime();
				}
			]),

			//Compatible Fields for FinderDestTable
			new Fields\ExpressionField(
				'CODE',
				'CASE WHEN %s=\'department\' THEN REPLACE(' . $helper->getConcatFunction('%s', '%s') . ', \':F\', \'\') ELSE ' . $helper->getConcatFunction('%s', '%s') . ' END',
				['ENTITY_ID', 'PREFIX', 'ITEM_ID', 'PREFIX', 'ITEM_ID']
			),
			new Fields\ExpressionField(
				'CODE_TYPE',
				'CASE 
					WHEN %s = \'user\' THEN \'U\'
					WHEN %s = \'project\' THEN \'SG\'
					WHEN %s = \'department\' THEN \'D\'
					WHEN %s IN(
							\'crm-contact\', 
							\'crm-company\', 
							\'crm-lead\', 
							\'crm-quote\', 
							\'crm-deal\',
							\'crm-order\',
							\'crm-product\'
						) THEN \'CRM\'
				END',
				['ENTITY_ID', 'ENTITY_ID', 'ENTITY_ID', 'ENTITY_ID']
			),

			new Fields\ExpressionField(
				'CODE_USER_ID',
				'CASE WHEN %s = \'user\' THEN %s END',
				['ENTITY_ID', 'ITEM_ID_INT']
			),

			new Fields\ExpressionField(
				'MAX_LAST_USE_DATE',
				'MAX(%s)', ['LAST_USE_DATE']
			),

			new Reference(
				"CODE_USER",
				UserTable::class,
				Join::on("this.ITEM_ID_INT", "ref.ID")->where('this.ENTITY_ID', 'user')
			),

			new Reference(
				'CODE_USER_CURRENT',
				UserTable::class,
				Join::on("this.ITEM_ID_INT", "ref.ID")
					->where('this.ENTITY_ID', 'user')
					->where('this.USER_ID', $GLOBALS['USER']->getId())
			),
		];
	}

	public static function getCompatEntities()
	{
		return Converter::getCompatEntities();
	}

	public static function merge(array $data)
	{
		$userId = (
			isset($data['USER_ID']) && intval($data['USER_ID']) > 0
				? intval($data['USER_ID'])
				: (is_object($GLOBALS['USER']) ? $GLOBALS['USER']->getId() : 0)
		);

		if ($userId <= 0)
		{
			return false;
		}

		if (empty($data['CONTEXT']) || !is_string($data['CONTEXT']))
		{
			return false;
		}

		if (empty($data['ENTITY_ID']) || !is_string($data['ENTITY_ID']))
		{
			return false;
		}
		$entityId = strtolower($data['ENTITY_ID']);

		if (empty($data['ITEM_ID']) || (!is_string($data['ITEM_ID']) && !is_int($data['ITEM_ID'])))
		{
			return false;
		}

		$itemIdInteger =  0;
		if (isset($data['ITEM_ID_INT']) && is_int($data['ITEM_ID_INT']))
		{
			$itemIdInteger = $data['ITEM_ID_INT'];
		}
		else if (preg_match('/(?<id>[0-9]+)/', (string)$data['ITEM_ID'], $matches))
		{
			$itemIdInteger = (int)$matches['id'];
		}

		$prefix = '';
		if (!empty($data['PREFIX']) && is_string($data['PREFIX']))
		{
			$prefix = $data['PREFIX'];
		}
		else
		{
			$compatEntities = Converter::getCompatEntities();
			if (isset($compatEntities[$entityId]))
			{
				$prefix = $compatEntities[$entityId]['prefix'];
				if (is_callable($prefix))
				{
					$prefix = $prefix($data['ITEM_ID']);
				}
			}
		}

		$sqlHelper = Application::getConnection()->getSqlHelper();
		$merge = $sqlHelper->prepareMerge(
			static::getTableName(),
			['USER_ID', 'ITEM_ID', 'ENTITY_ID', 'CONTEXT'],
			[
				'USER_ID' => $userId,
				'CONTEXT' => mb_strtoupper($data['CONTEXT']),
				'ENTITY_ID' => $entityId,
				'ITEM_ID' => $data['ITEM_ID'],
				'ITEM_ID_INT' => $itemIdInteger,
				'PREFIX' => $prefix,
				'LAST_USE_DATE' => new Main\Type\DateTime(),
			],
			[
				'LAST_USE_DATE' => new Main\Type\DateTime()
			]
		);

		if ($merge[0] !== "")
		{
			Application::getConnection()->query($merge[0]);
		}
		else
		{
			return false;
		}

		return true;
	}
}