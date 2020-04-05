<?php

namespace Bitrix\Vote\Attachment;

use Bitrix\Vote\Channel;

interface Storable
{
	/**
	 * @param Channel $channel Group of votes.
	 * @return $this
	 */
	public function setStorage(Channel $channel);
	/**
	 * @return Channel|null
	 */
	public function getStorage();
	/**
	 * @return boolean
	 */
	public function isStorable();
}