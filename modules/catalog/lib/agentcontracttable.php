<?php
namespace Bitrix\Catalog;

use Bitrix\Main\Application;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserTable;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Catalog\v2\Contractor;

/**
 * Class AgentContractTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TITLE string(255) optional
 * <li> CONTRACTOR_ID int mandatory
 * <li> DATE_MODIFY datetime optional
 * <li> DATE_CREATE datetime optional
 * <li> CREATED_BY int optional
 * <li> MODIFIED_BY int optional
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_AgentContract_Query query()
 * @method static EO_AgentContract_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_AgentContract_Result getById($id)
 * @method static EO_AgentContract_Result getList(array $parameters = [])
 * @method static EO_AgentContract_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_AgentContract createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_AgentContract_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_AgentContract wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_AgentContract_Collection wakeUpCollection($rows)
 */

class AgentContractTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_agent_contract';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('CATALOG_AGENT_CONTRACT_ENTITY_ID_FIELD'),
				]
			),
			'AGENT_PRODUCT' => new Reference(
				'AGENT_PRODUCT',
				AgentProductTable::class,
				Join::on('this.ID', 'ref.CONTRACT_ID')
			),
			'TITLE' => new StringField(
				'TITLE',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('CATALOG_AGENT_CONTRACT_ENTITY_TITLE_FIELD'),
				]
			),
			'CONTRACTOR_ID' => new IntegerField(
				'CONTRACTOR_ID',
				[
					'title' => Loc::getMessage('CATALOG_AGENT_CONTRACT_ENTITY_CONTRACTOR_ID_FIELD'),
				]
			),
			'CONTRACTOR' => new Reference(
				'CONTRACTOR',
				ContractorTable::class,
				Join::on('this.CONTRACTOR_ID', 'ref.ID')
			),
			'DATE_MODIFY' => new DatetimeField(
				'DATE_MODIFY',
				[
					'title' => Loc::getMessage('CATALOG_AGENT_CONTRACT_ENTITY_DATE_MODIFY_FIELD'),
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'title' => Loc::getMessage('CATALOG_AGENT_CONTRACT_ENTITY_DATE_CREATE_FIELD'),
					'default_value' => new DateTime(),
				]
			),
			'MODIFIED_BY' => new IntegerField(
				'MODIFIED_BY',
				[
					'title' => Loc::getMessage('CATALOG_AGENT_CONTRACT_ENTITY_MODIFIED_BY_FIELD'),
				]
			),
			'MODIFIED_BY_USER' => new Reference(
				'MODIFIED_BY_USER',
				UserTable::class,
				Join::on('this.MODIFIED_BY', 'ref.ID')
			),
			'CREATED_BY' => new IntegerField(
				'CREATED_BY',
				[
					'title' => Loc::getMessage('CATALOG_AGENT_CONTRACT_ENTITY_CREATED_BY_FIELD'),
				]
			),
			'CREATED_BY_USER' => new Reference(
				'CREATED_BY_USER',
				UserTable::class,
				Join::on('this.CREATED_BY', 'ref.ID')
			),
		];
	}

	public static function withProductList(Query $query, array $productIds)
	{
		Collection::normalizeArrayValuesByInt($productIds);
		if (empty($productIds))
		{
			return;
		}

		$tableName = AgentProductTable::getTableName();
		$whereExpression = '(PRODUCT_ID IN (' . implode(',', $productIds) . '))';

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$productType = $helper->forSql(AgentProductTable::PRODUCT_TYPE_PRODUCT);

		$query->whereExpr("
			(
				CASE WHEN EXISTS (
					SELECT ID
					FROM {$tableName}
					WHERE CONTRACT_ID = %s
					AND PRODUCT_TYPE = '{$productType}'
					AND {$whereExpression}
				)
				THEN 1
				ELSE 0
				END
			) = 1
		", ['ID']);
	}

	public static function withSectionList(Query $query, array $sectionIds)
	{
		Collection::normalizeArrayValuesByInt($sectionIds);
		if (empty($sectionIds))
		{
			return;
		}

		$tableName = AgentProductTable::getTableName();
		$whereExpression = '(PRODUCT_ID IN (' . implode(',', $sectionIds) . '))';

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$productType = $helper->forSql(AgentProductTable::PRODUCT_TYPE_SECTION);

		$query->whereExpr("
			(
				CASE WHEN EXISTS (
					SELECT ID
					FROM {$tableName}
					WHERE CONTRACT_ID = %s
					AND PRODUCT_TYPE = '{$productType}'
					AND {$whereExpression}
				)
				THEN 1
				ELSE 0
				END
			) = 1
		", ['ID']);
	}

	public static function onBeforeAdd(Event $event)
	{
		$result = new EventResult;
		$data = $event->getParameter('fields');

		if (empty($data['TITLE']))
		{
			$result->modifyFields([
				'TITLE' => Loc::getMessage('CATALOG_AGENT_CONTRACT_ENTITY_TITLE_DEFAULT'),
			]);
		}

		return $result;
	}

	public static function onAfterAdd(Event $event)
	{
		$data = $event->getParameters();
		$id = $event->getParameter('id');

		if ($data['fields']['TITLE'] === Loc::getMessage('CATALOG_AGENT_CONTRACT_ENTITY_TITLE_DEFAULT'))
		{
			$title = Loc::getMessage(
				'CATALOG_AGENT_CONTRACT_ENTITY_TITLE_DEFAULT',
				[
					'#' => $id,
				]
			);

			self::update(
				$id,
				[
					'TITLE' => $title,
				]
			);
		}
	}

	public static function onAfterDelete(Event $event)
	{
		$result = new EventResult();

		$id = (int)$event->getParameter('primary')['ID'];

		$contractorsProvider = Contractor\Provider\Manager::getActiveProvider(
			Contractor\Provider\Manager::PROVIDER_AGENT_CONTRACT
		);
		if ($contractorsProvider)
		{
			$contractorsProvider::onAfterDocumentDelete($id);
		}

		// delete files
		AgentContractFileTable::deleteFilesByContractId($id);

		return $result;
	}
}