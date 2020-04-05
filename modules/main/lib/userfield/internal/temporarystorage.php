<?php

namespace Bitrix\Main\UserField\Internal;

/**
 * @deprecated
 */
class TemporaryStorage
{
	protected $data;

	/**
	 * @param $primary
	 * @return string
	 */
	public function getIdByPrimary($primary)
	{
		if(is_array($primary))
		{
			return $primary['ID'];
		}

		return $primary;
	}

	public function saveData($primary, array $data = null)
	{
		$this->data[$this->getIdByPrimary($primary)] = $data;
	}

	public function getData($primary): ?array
	{
		$primary = $this->getIdByPrimary($primary);
		if(isset($this->data[$primary]) && !empty($this->data[$primary]))
		{
			$oldData = $this->data[$primary];
			unset($this->data[$primary]);

			return $oldData;
		}

		return null;
	}
}