<?php

namespace Bitrix\Socialnetwork\Space\List\Query;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Space\List\Dictionary;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;

final class LightweightBuilder extends AbstractBuilder
{
	protected const SELECT = [
		'ID',
		'MEMEBER.USER_ID', // Here and below is Postgres support
		'MEMBER.ROLE',
	];

	public function addModeFilter(string $mode): self
	{
		if ($mode === Dictionary::FILTER_MODES['my'])
		{
			$this->addFilter(new Filter\Mode\MyModeUnorderedFilter($this->userId));
		}

		return $this;
	}

	protected function getBaseQuery(): Query
	{
		$groupJoin =
			Join::on('this.ID', 'ref.GROUP_ID')
				->where('ref.USER_ID', $this->userId)
		;

		$query = WorkgroupTable::query();
		$query
			->setSelect([
				'ID',
				'MEMBER.USER_ID', // Here and below is Postgres support
				'MEMBER.ROLE',
			])
			->registerRuntimeField(
				(new Reference(
					'MEMBER',
					UserToGroupTable::class,
					$groupJoin,
				))
					->configureJoinType(Join::TYPE_LEFT)
			)
		;

		return $query;
	}
}