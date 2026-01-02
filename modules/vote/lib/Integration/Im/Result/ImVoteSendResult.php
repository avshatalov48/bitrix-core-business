<?php

namespace Bitrix\Vote\Integration\Im\Result;

use Bitrix\Main\Result;

class ImVoteSendResult extends Result
{
	public function __construct(
		public int $messageId,
		public int $voteId,
	)
	{
		parent::__construct();
	}
}