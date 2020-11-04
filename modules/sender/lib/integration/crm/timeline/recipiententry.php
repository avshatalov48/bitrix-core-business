<?php
namespace Bitrix\Sender\Integration\Crm\Timeline;

use Bitrix\Crm\Timeline;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Main;
use Bitrix\Main\Type\DateTime;

/**
 * Class RecipientEntry
 * @package Bitrix\Sender\Integration\Crm\Timeline
 */
class RecipientEntry extends Timeline\TimelineEntry
{
	/**
	 * Create.
	 *
	 * @param array $params Parameters.
	 * @return array|int
	 * @throws Main\ArgumentException
	 */
	public static function create(array $params)
	{
		$entityTypeId = isset($params['ENTITY_TYPE_ID']) ? (int)$params['ENTITY_TYPE_ID'] : 0;
		if($entityTypeId <= 0)
		{
			throw new Main\ArgumentException('Entity type ID must be greater than zero.', 'entityTypeID');
		}

		$entityId = isset($params['ENTITY_ID']) ? (int)$params['ENTITY_ID'] : 0;
		if($entityId <= 0)
		{
			throw new Main\ArgumentException('Entity ID must be greater than zero.', 'entityID');
		}

		$typeCategoryId = isset($params['TYPE_CATEGORY_ID']) ? $params['TYPE_CATEGORY_ID'] : 0;
		if(!$typeCategoryId)
		{
			throw new Main\ArgumentException('Type category ID must not be empty.', 'typeCategoryId');
		}

		$authorId = isset($params['AUTHOR_ID']) ? (int) $params['AUTHOR_ID'] : 0;
		if(!is_int($authorId))
		{
			$authorId = (int) $authorId;
		}

		if($authorId <= 0)
		{
			throw new Main\ArgumentException('Author ID must be greater than zero.', 'authorID');
		}

		$created = (isset($params['CREATED']) && ($params['CREATED'] instanceof DateTime)) ? $params['CREATED'] : new DateTime();
		$settings = isset($params['SETTINGS']) && is_array($params['SETTINGS']) ? $params['SETTINGS'] : array();

		$result = TimelineTable::add(
			array(
				'TYPE_ID' => Timeline\TimelineType::SENDER,
				'TYPE_CATEGORY_ID' => $typeCategoryId,
				'CREATED' => $created,
				'AUTHOR_ID' => $authorId,
				'SETTINGS' => $settings,
				'ASSOCIATED_ENTITY_TYPE_ID' => $entityTypeId,
				'ASSOCIATED_ENTITY_ID' => $entityId
			)
		);

		if(!$result->isSuccess())
		{
			return 0;
		}
		$id = $result->getId();

		$bindings = isset($params['BINDINGS']) && is_array($params['BINDINGS']) ? $params['BINDINGS'] : array();
		if(empty($bindings))
		{
			$bindings[] = array('ENTITY_TYPE_ID' => $entityTypeId, 'ENTITY_ID' => $entityId);
		}
		self::registerBindings($id, $bindings);

		return $id;
	}
	/**
	 * Create multi.
	 *
	 * @param array $params array of Parameters.
	 * @return array|int
	 * @throws Main\ArgumentException
	 */
	public static function createMulti(array $parameters)
	{
		$ids = [];
		foreach ($parameters as $params)
		{
			$ids[] = self::create($params);
		}

		return $ids;
	}
}