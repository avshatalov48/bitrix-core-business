<?php

namespace Bitrix\Mail\Internals\Entity;

use Bitrix\Mail\Blacklist\ItemType;
use Bitrix\Mail\BlacklistTable;

class BlacklistEmail extends \Bitrix\Mail\EO_Blacklist
{
	public static function getDataClass()
	{
		return BlacklistTable::class;
	}

	public function convertDomainToPunycode()
	{
		$email = $this->getItemValue();
		if (!$email)
		{
			return '';
		}
		$convertingPart = $email;
		$firstPart = '';
		if (count(explode('@', $email)) === 2)
		{
			$list = explode('@', $email);
			$convertingPart = array_pop($list);
			$firstPart = array_shift($list) . '@';
		}

		$encoder = new \CBXPunycode();
		$encodedPart = $encoder->encode($convertingPart);
		if ($encodedPart !== false)
		{
			return $firstPart . $encodedPart;
		}
		return '';
	}

	public function isDomainType()
	{
		return ItemType::DOMAIN === $this->getItemType();
	}

	public function isEmailType()
	{
		return ItemType::EMAIL === $this->getItemType();
	}

}