<?php

namespace Bitrix\Mail\Helper\Mailbox;

class ResponseParser
{
	public function getOldToNewUidsMap($copyUid)
	{
		$uIds = [];
		$dirUidValidity = '';
		if ($responseLine = mb_stristr($copyUid, 'COPYUID'))
		{
			$data = explode(' ', mb_stristr($copyUid, 'COPYUID'));
			if (isset($data[1]) && isset($data[2]) && isset($data[3]))
			{
				$dirUidValidity = $data[1];
				$idsFrom = $this->getIdsSet($data[2]);
				$idsTo = $this->getIdsSet(str_replace(']', '', $data[3]));
				$uIds = array_combine($idsFrom, $idsTo);
			}
		}

		return [
			'uids' => $uIds,
			'dirUid' => $dirUidValidity,
		];
	}

	private function getIdsSet($line)
	{
		$idsFrom = [];
		$idsFromParsed = explode(',', $line);
		foreach ($idsFromParsed as $_index => $_idFrom)
		{
			$sequence = explode(':', $_idFrom);
			if (count($sequence) == 2)
			{
				$idsFrom = array_merge($idsFrom, range(min($sequence[0], $sequence[1]), max($sequence[0], $sequence[1]), 1));
			}
			elseif (count($sequence) == 1)
			{
				$idsFrom[] = intval($sequence[0]);
			}
		}
		return $idsFrom;
	}
}