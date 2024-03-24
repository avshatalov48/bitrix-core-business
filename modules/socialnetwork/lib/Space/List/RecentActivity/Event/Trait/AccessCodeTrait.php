<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Trait;

use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;

trait AccessCodeTrait
{
	private function getSpaceIdsFromCodes(array $codes, Recepient $recipient): array
	{
		$result = [];

		foreach ($codes as $code)
		{
			if (!is_string($code))
			{
				continue;
			}

			$groupId = $this->getSpaceIdFromCode($code, $recipient);

			if (!is_null($groupId) && $groupId >= 0)
			{
				$result[] = $groupId;
			}
		}

		return $result;
	}

	private function getSpaceIdFromCode(string $code, Recepient $recipient): ?int
	{
		if (str_starts_with($code, 'SG'))
		{
			return $this->getSpaceIdFromGroupCode($code);
		}

		return $this->getSpaceIdFromOrdinaryCode($code, $recipient);
	}

	private function getSpaceIdFromGroupCode(string $code): ?int
	{
		$explodedCode = explode('_', $code)[0] ?? '';
		$groupId = (int)substr($explodedCode, 2);

		if ($groupId > 0)
		{
			return $groupId;
		}

		return null;
	}

	private function getSpaceIdFromOrdinaryCode(string $code, Recepient $recipient): ?int
	{
		if (preg_match('/^(U|UA|AU|D|DR|G2)/', $code) && in_array($code, $recipient->getAccessCodes(), true))
		{
			return 0;
		}

		return null;
	}
}