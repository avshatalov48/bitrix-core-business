<?php

namespace Bitrix\Catalog;

use Bitrix\Main\Application;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;

/**
 * Class AgentContractFileTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CONTRACT_ID int mandatory
 * <li> FILE_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_AgentContractFile_Query query()
 * @method static EO_AgentContractFile_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_AgentContractFile_Result getById($id)
 * @method static EO_AgentContractFile_Result getList(array $parameters = [])
 * @method static EO_AgentContractFile_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_AgentContractFile createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_AgentContractFile_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_AgentContractFile wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_AgentContractFile_Collection wakeUpCollection($rows)
 */

class AgentContractFileTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_agent_contract_file';
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
					'title' => Loc::getMessage('AGENT_CONTRACT_FILE_ENTITY_ID_FIELD'),
				]
			),
			'CONTRACT_ID' => new IntegerField(
				'CONTRACT_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('AGENT_CONTRACT_FILE_ENTITY_CONTRACT_ID_FIELD'),
				]
			),
			'CONTRACT' => new Reference(
				'CONTRACT',
				'\Bitrix\Catalog\AgentContractTable',
				['=this.CONTRACT_ID' => 'ref.ID'],
			),
			'FILE_ID' => new IntegerField(
				'FILE_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('AGENT_CONTRACT_FILE_ENTITY_FILE_ID_FIELD'),
				]
			),
			'FILE' => new Reference(
				'FILE',
				'\Bitrix\Main\FileTable',
				['=this.FILE_ID' => 'ref.ID'],
			),
		];
	}

	public static function deleteFilesByContractId(int $contractId): void
	{
		$agentContractFileIterator = self::getList([
			'select' => ['ID', 'FILE_ID'],
			'filter' => ['=CONTRACT_ID' => $contractId],
		]);
		while ($agentContractFile = $agentContractFileIterator->fetch())
		{
			\CFile::Delete($agentContractFile['FILE_ID']);
			self::delete($agentContractFile['ID']);
		}
	}
}