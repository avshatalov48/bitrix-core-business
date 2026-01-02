<?php

namespace Bitrix\Im\V2\Integration\HumanResources;

use Bitrix\HumanResources\Config\Feature;
use Bitrix\HumanResources\Config\Storage;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Type\AccessCodeType;
use Bitrix\HumanResources\Type\RelationEntitySubtype;
use Bitrix\HumanResources\Type\RelationEntityType;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\EntitySelector\Converter;

class Structure
{
	protected Chat $chat;

	public function __construct(Chat $chat)
	{
		$this->chat = $chat;
	}

	public static function isTeamsAvailable(): bool
	{
		return Loader::includeModule('humanresources')
			&& Storage::instance()->isCompanyStructureConverted()
			&& Feature::instance()->isCrossFunctionalTeamsAvailable()
		;
	}

	public static function splitEntities(array $entities): array
	{
		$entities = static::convertEntities($entities);
		$users = [];
		$structureNodes = [];

		foreach ($entities as $entity)
		{
			if (str_starts_with($entity, 'U'))
			{
				$users[] = (int)mb_substr($entity, 1);
			}
			if (static::isStructureNode($entity))
			{
				$structureNodes[] = $entity;
			}
		}

		return [$users, $structureNodes];
	}

	public static function isSyncAvailable(): bool
	{
		return Loader::includeModule('humanresources')
			&& Storage::instance()->isCompanyStructureConverted()
		;
	}

	public function link(array $structureNodeIds): Result
	{
		$result = new Result();

		if (empty($structureNodeIds))
		{
			return $result;
		}

		if (!Loader::includeModule('humanresources'))
		{
			return $result->addError(new Error(Error::LINK_ERROR));
		}

		$nodeRelationService = Container::getNodeRelationService();

		foreach ($structureNodeIds as $structureNodeId)
		{
			try {
				$nodeRelationService->linkEntityToNodeByAccessCode(
					$structureNodeId,
					RelationEntityType::CHAT,
					$this->chat->getId(),
					RelationEntitySubtype::fromChatType($this->chat->getType()),
				);
			}
			catch (\Exception $exception)
			{
				$result->addError(new Error(Error::LINK_ERROR));
			}
		}

		return $result;
	}

	public function unlink(array $structureNodeIds): Result
	{
		$result = new Result();

		if (empty($structureNodeIds))
		{
			return $result;
		}

		if (!Loader::includeModule('humanresources'))
		{
			return $result->addError(new Error(Error::UNLINK_ERROR));
		}

		$nodeRelationService = Container::getNodeRelationService();

		foreach ($structureNodeIds as $structureNodeId)
		{
			try {
				$nodeRelationService->unlinkEntityFromNodeByAccessCode(
					$structureNodeId,
					RelationEntityType::CHAT,
					$this->chat->getId(),
				);
			}
			catch (\Exception $exception)
			{
				$result->addError(new Error(Error::UNLINK_ERROR));
			}
		}

		return $result;
	}

	public function unlinkAll(): Result
	{
		$result = new Result();

		if (!Loader::includeModule('humanresources'))
		{
			return $result->addError(new Error(Error::UNLINK_ERROR));
		}

		$nodeRelationRepository = Container::getNodeRelationRepository();

		try
		{
			$nodeRelationRepository->deleteRelationByEntityTypeAndEntityIds(
				RelationEntityType::CHAT,
				[(int)$this->chat->getId()],
			);
		}
		catch (\Exception)
		{
			return $result->addError(new Error(Error::UNLINK_ERROR));
		}

		return $result;
	}

	protected static function convertEntities(array $entities): array
	{
		if (!Loader::includeModule('ui'))
		{
			return [];
		}

		return Converter::convertToFinderCodes($entities);
	}

	/**
	 * @return list<string>
	 */
	public function getNodesAccessCodes(): array
	{
		$accessCodes = [];

		if (!Loader::includeModule('humanresources'))
		{
			return $accessCodes;
		}

		$nodeRelationService = Container::getNodeRelationService();

		$links = $nodeRelationService->findAllRelationsByEntityTypeAndEntityId(
			RelationEntityType::CHAT,
			$this->chat->getId(),
		);

		foreach ($links as $link)
		{
			if ($link->node === null)
			{
				continue;
			}

			if ($link->node->isDepartment())
			{
				$accessCodes[] = $link->withChildNodes
					? str_replace('D', 'DR', $link->node->accessCode)
					: $link->node->accessCode;
			}
			if ($link->node->isTeam())
			{
				$accessCodes[] = $link->withChildNodes
					? AccessCodeType::HrTeamRecursiveType->buildAccessCode($link->nodeId)
					: AccessCodeType::HrTeamType->buildAccessCode($link->nodeId);
			}
		}

		return $accessCodes;
	}

	private static function isStructureNode(string $entity): bool
	{
		if (!Loader::includeModule('humanresources'))
		{
			return false;
		}

		$prefixes = [
			...AccessCodeType::getIntranetDepartmentTypesPrefixes(),
			...AccessCodeType::getTeamTypesPrefixes(),
		];
		foreach ($prefixes as $prefix)
		{
			if (str_starts_with($entity, $prefix))
			{
				return true;
			}
		}

		return false;
	}
}
