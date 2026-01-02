<?php

namespace Bitrix\Main\Access\Auth;

use Bitrix\HumanResources\Builder\Structure\Filter\Column\EntityIdFilter;
use Bitrix\HumanResources\Builder\Structure\Filter\Column\Node\NodeTypeFilter;
use Bitrix\HumanResources\Builder\Structure\Filter\NodeFilter;
use Bitrix\HumanResources\Builder\Structure\Filter\NodeMemberFilter;
use Bitrix\HumanResources\Builder\Structure\NodeMemberDataBuilder;
use Bitrix\HumanResources\Config\Storage;
use Bitrix\HumanResources\Item\Collection\NodeMemberCollection;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Type\IntegerCollection;
use Bitrix\HumanResources\Type\MemberEntityType;
use Bitrix\HumanResources\Type\NodeEntityType;
use Bitrix\HumanResources\Type\NodeEntityTypeCollection;

use Bitrix\Main\Application;
use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Loader;

class AccessAuthProvider extends \CAuthProvider
{
	protected const PROVIDER_ID = 'access';

	public static function GetProviders()
	{
		return [
			[
				"ID" => self::PROVIDER_ID,
				"CLASS" => self::class,
			]
		];
	}

	public function __construct()
	{
		$this->id = self::PROVIDER_ID;
	}

	public function UpdateCodes($userId)
	{
		global $DB;

		if (
			Loader::includeModule('humanresources')
			&& Storage::instance()->isCompanyStructureConverted()
		)
		{
			$this->updateCodesByHr($userId);

			return null;
		}

		$iblockId = \COption::GetOptionInt('intranet', 'iblock_structure');
		if ($iblockId > 0)
		{
			$tableName = "b_uts_iblock_". $iblockId ."_section";

			if (!$DB->TableExists($tableName))
			{
				return null;
			}

			$res = $DB->query("
				SELECT VALUE_ID
				FROM ". $tableName ."
				WHERE UF_HEAD = " . $userId
			);

			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();

			while ($row = $res->fetch())
			{
				$id = (int) $row['VALUE_ID'];

				$sql = $helper->getInsertIgnore(
					'b_user_access',
					'(USER_ID, PROVIDER_ID, ACCESS_CODE)',
					'VALUES
						('.$userId.',\''.$this->id.'\',\''.AccessCode::ACCESS_DIRECTOR.'0\'),
						('.$userId.',\''.$this->id.'\',\''.AccessCode::ACCESS_DIRECTOR.$id.'\')'
				);
				$DB->query($sql);
			}
		}
	}

	private function updateCodesByHr(int $userId): void
	{
		$roleHelperService = Container::getRoleHelperService();
		$roleCollection = $roleHelperService->getAllRoleCollectionForSync();
		if ($roleCollection->empty())
		{
			return;
		}

		$getMemberCollectionByNodeType =
			static fn(NodeEntityType $nodeType) =>
				(new NodeMemberDataBuilder())
					->addFilter(
						new NodeMemberFilter(
							entityIdFilter: EntityIdFilter::fromEntityId($userId),
							entityType: MemberEntityType::USER,
							nodeFilter: new NodeFilter(
								entityTypeFilter: NodeTypeFilter::fromNodeTypes([$nodeType]),
							),
							findRelatedMembers: false,
							active: null,
						)
					)
					->getAll()
		;

		$accessCodeSet = [];

		$departmentMemberCollection = $getMemberCollectionByNodeType(NodeEntityType::DEPARTMENT);
		if (!$departmentMemberCollection->empty())
		{
			$accessCodeSet[AccessCode::ACCESS_EMPLOYEE . '0'] = true;
		}

		$teamMemberCollection = $getMemberCollectionByNodeType(NodeEntityType::TEAM);
		if (!$teamMemberCollection->empty())
		{
			$accessCodeSet[AccessCode::ACCESS_TEAM_EMPLOYEE . '0'] = true;
		}

		$nodeMemberCollection = new NodeMemberCollection(...[
			...$departmentMemberCollection->getValues(),
			...$teamMemberCollection->getValues(),
		]);

		foreach ($nodeMemberCollection as $nodeMember)
		{
			if ($nodeMember->entityType !== MemberEntityType::USER)
			{
				continue;
			}

			$neededRoleIds = array_intersect($roleCollection->getKeys(), $nodeMember->roles);
			if (empty($neededRoleIds))
			{
				continue;
			}

			$userId = $nodeMember->entityId;

			foreach ($neededRoleIds as $roleId)
			{
				$role = $roleCollection->getItemById($roleId);
				if (!$role)
				{
					continue;
				}

				$type = $roleHelperService->getAccessCodeByRoleXmlId($role->xmlId);
				if (!$type)
				{
					continue;
				}

				$accessCode = $type . $nodeMember->nodeId;

				$accessCodeSet[$type . '0'] = true;
				$accessCodeSet[$accessCode] = true;
			}
		}

		$insertValues = [];
		$accessCodes = array_keys($accessCodeSet);
		foreach ($accessCodes as $accessCode)
		{
			$insertValues[] = "($userId, '$this->id', '$accessCode')";
		}

		if (!empty($insertValues))
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();

			$sql = $helper->getInsertIgnore(
				'b_user_access',
				'(USER_ID, PROVIDER_ID, ACCESS_CODE)',
				'VALUES' . implode(
					',',
					$insertValues,
				),
			);

			$connection->query($sql);
		}
	}
}
