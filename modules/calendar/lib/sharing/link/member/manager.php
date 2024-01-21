<?php

namespace Bitrix\Calendar\Sharing\Link\Member;

class Manager
{
	public function createMembersFromEntityObject($memberEOsCollection): array
	{
		$result = [];
		foreach ($memberEOsCollection as $memberEO)
		{
			if (!empty($memberEO->getUser()))
			{
				$result[] = $this->createMemberFromUserEntityObject($memberEO->getUser(), $memberEO->getImage());
			}
		}

		return $result;
	}

	public function createMemberFromUserEntityObject($userEO, $fileEO): Member
	{
		return (new Member())
			->setName($userEO->getName())
			->setLastName($userEO->getLastName())
			->setId($userEO->getId())
			->setAvatar($this->getFileSrc($fileEO))
		;
	}

	private function getFileSrc($fileEO): string
	{
		if (is_null($fileEO))
		{
			return '';
		}

		if ($fileEO->getWidth() <= 100 || $fileEO->getHeight() <= 100)
		{
			return \CFile::GetFileSRC($fileEO->collectValues());
		}

		$file = \CFile::resizeImageGet(
			$fileEO->collectValues(),
			['width' => 100, 'height' => 100],
			BX_RESIZE_IMAGE_EXACT,
			false
		);

		return !empty($file['src']) ? $file['src'] : '';
	}

	public function convertToArray(Member $member): array
	{
		return [
			'id' => $member->getId(),
			'name' => $member->getName(),
			'lastName' => $member->getLastName(),
			'avatar' => $member->getAvatar(),
		];
	}
}