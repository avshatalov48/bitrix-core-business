<?

namespace Bitrix\Main\UI\EntitySelector;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class EntityUsageTable extends Main\Entity\DataManager
{
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
				'IF(%s=\'department\',REPLACE(CONCAT(%s, %s), \':F\', \'\'), CONCAT(%s, %s))',
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

			new Reference(
				"CODE_USER",
				UserTable::class,
				Join::on("this.ITEM_ID_INT", "ref.ID")->where('this.ENTITY_ID', 'user'),
				["join_type" => "INNER"]
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
		static $compatEntities;

		if ($compatEntities)
		{
			return $compatEntities;
		}

		$compatEntities =[
			'user' => ['prefix' => 'U', 'pattern' => '^(?<prefix>U)(?<itemId>\d+)$'],
			'project' => ['prefix' => 'SG', 'pattern' => '^(?<prefix>SG)(?<itemId>\d+)$'],
			'crm-company' => ['prefix' => 'CRMCOMPANY', 'pattern' => '^(?<prefix>CRMCOMPANY)(?<itemId>.+)$'],
			'crm-contact' => ['prefix' => 'CRMCONTACT', 'pattern' => '^(?<prefix>CRMCONTACT)(?<itemId>.+)$'],
			'crm-lead' => ['prefix' => 'CRMLEAD', 'pattern' => '^(?<prefix>CRMLEAD)(?<itemId>.+)$'],
			'crm-deal' => ['prefix' => 'CRMDEAL', 'pattern' => '^(?<prefix>CRMDEAL)(?<itemId>.+)$'],
			'crm-quote' => ['prefix' => 'CRMQUOTE', 'pattern' => '^(?<prefix>CRMQUOTE)(?<itemId>.+)$'],
			'crm-order' => ['prefix' => 'CRMORDER', 'pattern' => '^(?<prefix>CRMORDER)(?<itemId>.+)$'],
			'crm-product' => ['prefix' => 'CRMPRODUCT', 'pattern' => '^(?<prefix>CRMPRODUCT)(?<itemId>.+)$'],
			'mail-contact' => ['prefix' => 'MC', 'pattern' => '^(?<prefix>MC)(?<itemId>[0-9]+)$'],
			'department' => [
				'prefix' => (function($itemId) {
					return is_string($itemId) && $itemId[-1] === 'F' ? 'D' : 'DR';
				}),
				'itemId' => function($prefix, $itemId) {
					return $prefix === 'D' ? $itemId.':F' : $itemId;
				},
				'pattern' => '^(?<prefix>DR?)(?<itemId>\d+)$'
			],
		];

		return $compatEntities;
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
			$compatEntities = static::getCompatEntities();
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

	public static function deleteByFilter(array $filter)
	{
		$entity = static::getEntity();
		$sqlTableName = static::getTableName();
		$sqlHelper = $entity->getConnection()->getSqlHelper();

		$where = Query::buildFilterSql($entity, $filter);
		if ($where !== '')
		{
			$sql = "DELETE FROM {$sqlHelper->quote($sqlTableName)} WHERE ".$where;
			$entity->getConnection()->queryExecute($sql);
		}
	}
}