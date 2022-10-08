<?php

namespace Bitrix\Calendar\Sync\Managers;

interface Section
{
	public function create(\Bitrix\Calendar\Core\Section\Section $section);

	public function update(\Bitrix\Calendar\Core\Section\Section $section);

	public function delete(\Bitrix\Calendar\Core\Section\Section $section);
}