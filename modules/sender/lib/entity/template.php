<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Entity;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\TemplateTable;

Loc::loadMessages(__FILE__);

class Template extends Base
{
	/**
	 * Get default data.
	 *
	 * @return array
	 */
	protected function getDefaultData()
	{
		return array(
			'NAME' => '',
			'CONTENT' => '',
		);
	}

	/**
	 * Load data.
	 *
	 * @param integer $id ID.
	 * @return array|null
	 */
	protected function loadData($id)
	{
		return TemplateTable::getRowById($id);
	}

	/**
	 * Save data.
	 *
	 * @param integer|null $id ID.
	 * @param array $data Data.
	 * @return integer|null
	 */
	protected function saveData($id, array $data)
	{
		$sizeInBytes = mb_strlen($data['CONTENT'] ?? "");
		$sizeInKilobytes = $sizeInBytes / 1024;
		$limitInKilobytes = 2.4 * 1024;

		if ($sizeInKilobytes > $limitInKilobytes)
		{
			$this->addError(new Error(Loc::getMessage('SENDER_INTEGRATION_MAIL_BODY_LIMIT')));

			return null;
		}


		return $this->saveByEntity(TemplateTable::getEntity(), $id, $data);
	}

	/**
	 * Remove.
	 *
	 * @return bool
	 */
	public function remove()
	{
		return $this->removeByEntity(TemplateTable::getEntity(), $this->getId());
	}

	/**
	 * Copy.
	 *
	 *
	 */
	public function copy()
	{
		$data = [
			'ACTIVE' => $this->data['ACTIVE'],
			'NAME' => $this->data['NAME'],
			'CONTENT' => $this->data['CONTENT'],
		];
		$instance = static::create()->mergeData($data);
		return $instance->save();
	}


	/**
	 * Remove by letter ID.
	 *
	 * @param integer $id Letter ID.
	 * @return bool
	 */
	public static function removeById($id)
	{
		return static::create()->removeByEntity(TemplateTable::getEntity(), $id);
	}
}