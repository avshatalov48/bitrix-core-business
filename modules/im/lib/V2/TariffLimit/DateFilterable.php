<?php

namespace Bitrix\Im\V2\TariffLimit;

use Bitrix\Main\Type\DateTime;

interface DateFilterable
{
	/**
	 * @param DateTime $date
	 * @return FilterResult<?static>
	 */
	public function filterByDate(DateTime $date): FilterResult;
	public function getRelatedChatId(): ?int;
}