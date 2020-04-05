<?php
namespace Bitrix\Landing\Update\Block;

use \Bitrix\Landing\File;
use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class NodeImg extends Stepper
{
	protected static $moduleId = 'landing';

	/**
	 * Get hash by file id.
	 * @param int $id File id.
	 * @return string
	 */
	public function getFileHash($id)
	{
		return md5($id . '|' . LICENSE_KEY);
	}

	/**
	 * One step of converter.
	 * @param array &$result Result array.
	 * @return bool
	 */
	public function execute(array &$result)
	{
		$lastId = Option::get('landing', 'update_block_nodeimg', 0);

		$toSave = array();
		$finished = true;

		// gets common quantity
		$res = BlockTable::getList(array(
			'select' => array(
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			)
		));
		if ($row = $res->fetch())
		{
			$result['count'] = $row['CNT'];
		}

		// gets group for update
		$res = BlockTable::getList(array(
			'select' => array(
				'ID', 'CONTENT'
			),
			'filter' => array(
				'>ID' => $lastId
			),
			'order' => array(
				'ID' => 'ASC'
			),
			'limit' => 100
		));
		while ($row = $res->fetch())
		{
			$files = array();
			$fileExist = preg_match_all(
				'/data\-([?fileid|filehash]+)\="([^"]+)"[^>]+data\-[?fileid|filehash]+\="([^"]+)"/is',
				$row['CONTENT'],
				$matches
			);
			if ($fileExist)
			{
				foreach ($matches[1] as $i => $attr)
				{
					if ($attr == 'fileid')
					{
						$files[$matches[2][$i]] = $matches[3][$i];
					}
					else
					{
						$files[$matches[3][$i]] = $matches[2][$i];
					}
				}
				foreach ($files as $fileId => $fileHash)
				{
					if ($fileId > 0 && $this->getFileHash($fileId) == $fileHash)
					{
						if (!isset($toSave[$row['ID']]))
						{
							$toSave[$row['ID']] = array();
						}
						$toSave[$row['ID']][] = $fileId;
					}
				}
			}
			$lastId = $row['ID'];
			$result['steps']++;
			$finished = false;
		}

		// add files from blocks
		foreach ($toSave as $block => $files)
		{
			File::addToBlock($block, $files);
		}

		// clear handlers all continue work
		if (!$finished)
		{
			Option::set('landing', 'update_block_nodeimg', $lastId);
			return true;
		}
		else
		{
			Option::delete('landing', array('name' => 'update_block_nodeimg'));
			$eventManager = \Bitrix\Main\EventManager::getInstance();
			$eventManager->unregisterEventHandler(
				'landing',
				'\Bitrix\Landing\Internals\Block::OnBeforeDelete',
				'landing',
				'\Bitrix\Landing\Update\Block\NodeImg',
				'disableBlockDelete');
			$eventManager->unregisterEventHandler(
				'landing',
				'onLandingPublication',
				'landing',
				'\Bitrix\Landing\Update\Block\NodeImg',
				'disablePublication'
			);
			return false;
		}
	}

	/**
	 * Before delete block handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function disableBlockDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$result->setErrors(array(
			new Entity\EntityError(
				Loc::getMessage('LANDING_BLOCK_DISABLE_DELETE'),
				'BLOCK_DISABLE_DELETE'
			)
		));
		return $result;
	}

	/**
	 * Before publication landing handler.
	 * @param \Bitrix\Main\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function disablePublication(\Bitrix\Main\Event $event)
	{
		$result = new Entity\EventResult;
		$result->setErrors(array(
			new \Bitrix\Main\Entity\EntityError(
				Loc::getMessage('LANDING_DISABLE_PUBLICATION'),
				'LANDING_DISABLE_PUBLICATION'
			)
		));
		return $result;
	}
}