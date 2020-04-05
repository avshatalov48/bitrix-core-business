<?php
namespace Bitrix\Socialnetwork\Copy\Integration;

interface Feature
{
	/**
	 * Starts the copy process.
	 * @param int $groupId Origin group id.
	 * @param int $copiedGroupId Copied group id.
	 */
	public function copy($groupId, $copiedGroupId);
}