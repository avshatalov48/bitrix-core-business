<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Block as BlockCore;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Landing as LandingCore;
use \Bitrix\Landing\PublicActionResult;
use \Bitrix\Landing\Internals\HookDataTable;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Landing
{
	/**
	 * Clear disallow keys from add/update fields.
	 * @param array $fields
	 * @return array
	 */
	protected static function clearDisallowFields(array $fields)
	{
		$disallow = array('RULE', 'TPL_CODE', 'ACTIVE');

		if (is_array($fields))
		{
			foreach ($fields as $k => $v)
			{
				if (in_array($k, $disallow))
				{
					unset($fields[$k]);
				}
			}
		}

		return $fields;
	}

	/**
	 * Get preview picture of landing.
	 * @param int $lid Id of landing.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getPreview($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);

		if ($landing->exist())
		{
			$result->setResult($landing->getPreview());
		}
		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Get public url of landing.
	 * @param int $lid Id of landing.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getPublicUrl($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);

		if ($landing->exist())
		{
			$result->setResult(
				$landing->getPublicUrl()
			);
		}
		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Get additional fields of landing.
	 * @param int $lid Id of landing.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getAdditionalFields($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);

		if ($landing->exist())
		{
			$fields = $landing->getAdditionalFields($landing->getId());
			foreach ($fields as $key => $field)
			{
				$fields[$key] = $field->getValue();
				if (!$fields[$key])
				{
					unset($fields[$key]);
				}
			}
			$result->setResult(
				$fields
			);
		}
		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Publication of landing.
	 * @param int $lid Id of landing.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function publication($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);

		if ($landing->exist())
		{
			if ($landing->publication())
			{
				$result->setResult(true);
			}
		}

		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Cancel publication of landing.
	 * @param int $lid Id of landing.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function unpublic($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);

		if ($landing->exist())
		{
			$result->setResult(
				$landing->unpublic()
			);
		}

		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Add new block to the landing.
	 * @param int $lid Id of landing.
	 * @param array $fields Data array of block.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function addBlock($lid, array $fields)
	{
		LandingCore::setEditMode();

		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		if ($landing->exist())
		{
			$data = array(
				'PUBLIC' => 'N'
			);
			if (isset($fields['ACTIVE']))
			{
				$data['ACTIVE'] = $fields['ACTIVE'];
			}
			if (isset($fields['CONTENT']))
			{
				$data['CONTENT'] = Manager::sanitize(
					$fields['CONTENT'],
					$bad
				);
			}
			// sort
			if (isset($fields['AFTER_ID']))
			{
				$blocks = $landing->getBlocks();
				if (isset($blocks[$fields['AFTER_ID']]))
				{
					$data['SORT'] = $blocks[$fields['AFTER_ID']]->getSort() + 1;
				}
			}
			else
			{
				$data['SORT'] = -1;
			}
			$newBlockId = $landing->addBlock(
				isset($fields['CODE']) ? $fields['CODE'] : '',
				$data
			);
			// re-sort
			$landing->resortBlocks();
			// want return content ob block
			if (
				isset($fields['RETURN_CONTENT']) &&
				$fields['RETURN_CONTENT'] == 'Y'
			)
			{
				$return = BlockCore::getBlockContent($newBlockId, true);
			}
			else
			{
				$return = $newBlockId;
			}
			$result->setResult($return);
		}
		$result->setError($landing->getError());
		return $result;
	}

	/**
	 * Delete the block from the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function deleteBlock($lid, $block)
	{
		LandingCore::setEditMode();

		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		if ($landing->exist())
		{
			$result->setResult($landing->deleteBlock($block));
			$landing->resortBlocks();
		}
		$result->setError($landing->getError());
		return $result;
	}

	/**
	 * Mark delete or not the block.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param boolean $mark Mark.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markDeletedBlock($lid, $block, $mark = true)
	{
		LandingCore::setEditMode();

		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		if ($landing->exist())
		{
			$result->setResult(
				$landing->markDeletedBlock($block, $mark)
			);
			$landing->resortBlocks();
		}
		$result->setError($landing->getError());
		return $result;
	}

	/**
	 * Mark undelete the block.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markUnDeletedBlock($lid, $block)
	{
		return self::markDeletedBlock($lid, $block, false);
	}

	/**
	 * Sort the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param string $action Code: up or down.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	private static function sort($lid, $block, $action)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		if ($landing->exist())
		{
			if ($action == 'up')
			{
				$result->setResult($landing->upBlock($block));
			}
			else
			{
				$result->setResult($landing->downBlock($block));
			}
			$landing->resortBlocks();
		}
		$result->setError($landing->getError());
		return $result;
	}

	/**
	 * Sort up the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function upBlock($lid, $block)
	{
		LandingCore::setEditMode();
		return self::sort($lid, $block, 'up');
	}

	/**
	 * Sort down the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function downBlock($lid, $block)
	{
		LandingCore::setEditMode();
		return self::sort($lid, $block, 'down');
	}

	/**
	 * Show/hide the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param string $action Code: show or hide.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	private static function activate($lid, $block, $action)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		if ($landing->exist())
		{
			if ($action == 'show')
			{
				$result->setResult($landing->showBlock($block));
			}
			else
			{
				$result->setResult($landing->hideBlock($block));
			}
		}
		$result->setError($landing->getError());
		return $result;
	}

	/**
	 * Activate the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function showBlock($lid, $block)
	{
		LandingCore::setEditMode();
		return self::activate($lid, $block, 'show');
	}

	/**
	 * Dectivate the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function hideBlock($lid, $block)
	{
		LandingCore::setEditMode();
		return self::activate($lid, $block, 'hide');
	}

	/**
	 * Copy/move other block to this landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param array $params Params array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	private static function changeParentOfBlock($lid, $block, array $params)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		$afterId = isset($params['AFTER_ID']) ? $params['AFTER_ID'] : 0;
		if ($landing->exist())
		{
			if ($params['MOVE'])
			{
				$res = $landing->moveBlock($block, $afterId);
			}
			else
			{
				$res = $landing->copyBlock($block, $afterId);
			}

			if (
				isset($params['RETURN_CONTENT']) &&
				$params['RETURN_CONTENT'] == 'Y'
			)
			{
				$result->setResult(array(
					'result' => $res > 0,
					'content' => BlockCore::getBlockContent($res, true)
				));
			}
			else
			{
				$result->setResult($res);
			}
		}
		$result->setError($landing->getError());
		return $result;
	}

	/**
	 * Copy other block to this landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param array $params Params array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function copyBlock($lid, $block, array $params = array())
	{
		if (!is_array($params))
		{
			$params = array();
		}
		$params['MOVE'] = false;
		LandingCore::setEditMode();
		return self::changeParentOfBlock($lid, $block, $params);
	}

	/**
	 * Move other block to this landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param array $params Params array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function moveBlock($lid, $block, array $params = array())
	{
		if (!is_array($params))
		{
			$params = array();
		}
		$params['MOVE'] = true;
		LandingCore::setEditMode();
		return self::changeParentOfBlock($lid, $block, $params);
	}

	/**
	 * Remove entities of Landing - images / blocks.
	 * @param int $lid Landing id.
	 * @param array $data Data for remove.
	 * @return PublicActionResult
	 */
	public static function removeEntities($lid, array $data)
	{
		$result = new PublicActionResult();

		LandingCore::setEditMode();
		$landing = LandingCore::createInstance($lid);

		if ($landing->exist())
		{
			$blocks = $landing->getBlocks();
			if (isset($data['blocks']) && is_array($data['blocks']))
			{
				foreach ($data['blocks'] as $block)
				{
					self::deleteBlock($lid, $block);
					unset($blocks[$block]);
				}
			}
			if (isset($data['images']) && is_array($data['images']))
			{
				foreach ($data['images'] as $item)
				{
					if (isset($blocks[$item['block']]))
					{
						File::deleteFromBlock($item['block'], $item['image']);
					}
				}
			}
			$result->setResult(true);
		}

		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Get available landings.
	 * @param array $params Params ORM array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList(array $params = array())
	{
		$result = new PublicActionResult();
		$preview = false;
		$checkArea = false;

		if (isset($params['get_preview']))
		{
			$preview = !!$params['get_preview'];
			unset($params['get_preview']);
		}

		if (isset($params['check_area']))
		{
			$checkArea = !!$params['check_area'];
			unset($params['check_area']);
		}

		$data = array();
		$res = LandingCore::getList($params);
		while ($row = $res->fetch())
		{
			if (isset($row['DATE_CREATE']))
			{
				$row['DATE_CREATE'] = (string) $row['DATE_CREATE'];
			}
			if (isset($row['DATE_MODIFY']))
			{
				$row['DATE_MODIFY'] = (string) $row['DATE_MODIFY'];
			}
			if ($preview && isset($row['ID']))
			{
				$landing = LandingCore::createInstance($row['ID']);
				$row['PREVIEW'] = $landing->getPreview();
			}
			if ($checkArea && isset($row['ID']))
			{
				$data[$row['ID']] = $row;
			}
			else
			{
				$checkArea = false;
				$data[] = $row;
			}
		}

		// landing is area?
		if ($checkArea)
		{
			$areas = TemplateRef::landingIsArea(
				array_keys($data)
			);
			foreach ($areas as $lid => $isA)
			{
				$data[$lid]['IS_AREA'] = $isA;
			}
		}

		$result->setResult(array_values($data));

		return $result;
	}

	/**
	 * Create new landing.
	 * @param array $fields Landing data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function add(array $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$fields = self::clearDisallowFields($fields);
		$fields['ACTIVE'] = 'N';

		$res = LandingCore::add($fields);

		if ($res->isSuccess())
		{
			$result->setResult($res->getId());
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Update landing.
	 * @param int $lid Landing id.
	 * @param array $fields Landing new data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function update($lid, array $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$fields = self::clearDisallowFields($fields);

		$res = LandingCore::update($lid, $fields);

		if ($res->isSuccess())
		{
			$result->setResult(true);
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Delete landing.
	 * @param int $lid Landing id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function delete($lid)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$res = LandingCore::delete($lid);

		if ($res->isSuccess())
		{
			$result->setResult(true);
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Copy landing.
	 * @param int $lid Landing id.
	 * @param int $toSiteId Site id (if you want copy in another site).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function copy($lid, $toSiteId = false)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		LandingCore::disableCheckDeleted();

		$landingRow = LandingCore::getList(array(
			'filter' => array(
				'ID' => $lid
			)
		))->fetch();

		$landing = LandingCore::createInstance($lid);

		if ($landing->exist())
		{
			if (!$toSiteId)
			{
				$toSiteId = $landing->getSiteId();
			}
			$res = LandingCore::add(array(
				'CODE' => $landingRow['CODE'],
				'ACTIVE' => $landingRow['ACTIVE'],
				'PUBLIC' => $landingRow['PUBLIC'],
				'TITLE' => $landingRow['TITLE'],
				'XML_ID' => $landingRow['XML_ID'],
				'TPL_CODE' => $landingRow['TPL_CODE'],
				'DESCRIPTION' => $landingRow['DESCRIPTION'],
				'TPL_ID' => $landingRow['TPL_ID'],
				'SITE_ID' => $toSiteId,
				'SITEMAP' => $landingRow['SITEMAP'],
				'FOLDER' => $landingRow['FOLDER'],
				'FOLDER_ID' => ($toSiteId == $landing->getSiteId())
								? $landingRow['FOLDER_ID']
								: null
			));
			// landing allready create, just copy the blocks
			if ($res->isSuccess())
			{
				LandingCore::setEditMode();
				$landingNew = LandingCore::createInstance($res->getId());
				if ($landingNew->exist())
				{
					$landingNew->copyAllBlocks($landing->getId());
					// copy hook data
					\Bitrix\Landing\Hook::copyLanding(
						$landingRow['ID'],
						$landingNew->getId()
					);
					$result->setResult($landingNew->getId());
				}
				$result->setError(
					$landingNew->getError()
				);
			}
			else
			{
				$error->addFromResult($res);
				$result->setError($error);
			}
		}

		$result->setError($landing->getError());

		LandingCore::enableCheckDeleted();

		return $result;
	}

	/**
	 * Mark entity as deleted.
	 * @param int $lid Entity id.
	 * @param boolean $mark Mark.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markDelete($lid, $mark = true)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if ($mark)
		{
			$res = LandingCore::markDelete($lid);
		}
		else
		{
			$res = LandingCore::markUnDelete($lid);
		}
		if ($res->isSuccess())
		{
			$result->setResult($res->getId());
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Mark entity as undeleted.
	 * @param int $lid Entity id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markUnDelete($lid)
	{
		return self::markDelete($lid, false);
	}

	/**
	 * Upload file by url or from FILE.
	 * @param int $lid Landing id.
	 * @param string $picture File url / file array.
	 * @param string $ext File extension.
	 * @param array $params Some file params.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function uploadFile($lid, $picture, $ext = false, array $params = array())
	{
		static $internal = true;
		static $mixedParams = ['picture'];

		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$landing = LandingCore::createInstance($lid);

		if ($landing->exist())
		{
			$file = Manager::savePicture($picture, $ext, $params);
			if ($file)
			{
				File::addToLanding($lid, $file['ID']);
				$result->setResult(array(
					'id' => $file['ID'],
					'src' => $file['SRC']
				));
			}
			else
			{
				$error->addError(
					'FILE_ERROR',
					Loc::getMessage('LANDING_FILE_ERROR')
				);
				$result->setError($error);
			}
		}

		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Set some content to the Head section.
	 * @param int $lid Landing id.
	 * @param $content Some content.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function updateHead($lid, $content)
	{
		static $internal = true;

		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		$result->setResult(false);

		if ($landing->exist())
		{
			// fix module security
			$content = str_replace('<st yle', '<style', $content);
			$content = str_replace('<li nk ', '<link ', $content);

			$fields = array(
				'ENTITY_ID' => $lid,
				'ENTITY_TYPE' => \Bitrix\Landing\Hook::ENTITY_TYPE_LANDING,
				'HOOK' => 'FONTS',
				'CODE' => 'CODE'
			);
			$res = HookDataTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => $fields
			));
			if ($row = $res->fetch())
			{
				HookDataTable::update(
					$row['ID'],
					array(
						'VALUE' => $content
					)
				);
			}
			else
			{
				$fields['VALUE'] = $content;
				HookDataTable::add($fields);
			}
			$result->setResult(true);
		}

		$result->setError($landing->getError());

		return $result;
	}
}