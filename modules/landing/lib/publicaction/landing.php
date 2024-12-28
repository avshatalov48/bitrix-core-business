<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Folder;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Block as BlockCore;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Landing as LandingCore;
use \Bitrix\Landing\PublicActionResult;
use \Bitrix\Landing\Internals\HookDataTable;
use \Bitrix\Landing\History;
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
		$disallow = ['RULE', 'TPL_CODE', 'ACTIVE', 'INITIATOR_APP_CODE', 'VIEWS'];

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
	 * @return PublicActionResult
	 */
	public static function getPreview($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid, [
			'skip_blocks' => true
		]);

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
	 * @return PublicActionResult
	 */
	public static function getPublicUrl($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid, [
			'skip_blocks' => true
		]);

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
	 * Returns landing id resolves by landing public url.
	 * @param string $landingUrl Landing public url.
	 * @param int $siteId Landing's site id.
	 * @return PublicActionResult
	 */
	public static function resolveIdByPublicUrl(string $landingUrl, int $siteId): PublicActionResult
	{
		$result = new PublicActionResult();
		$result->setResult(LandingCore::resolveIdByPublicUrl($landingUrl, $siteId));
		return $result;
	}

	/**
	 * Get additional fields of landing.
	 * @param int $lid Id of landing.
	 * @return PublicActionResult
	 */
	public static function getAdditionalFields($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid, [
			'skip_blocks' => true
		]);

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
	 * @return PublicActionResult
	 */
	public static function publication($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid, [
			'skip_blocks' => true
		]);

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
	 * @return PublicActionResult
	 */
	public static function unpublic($lid)
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid, [
			'skip_blocks' => true
		]);

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
	 * @param bool $preventHistory True if no need save history
	 * @return PublicActionResult
	 */
	public static function addBlock($lid, array $fields, bool $preventHistory = false)
	{
		LandingCore::setEditMode();
		Hook::setEditMode(true);

		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		if ($landing->exist())
		{
			$data = array(
				'PUBLIC' => 'N',
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
			$preventHistory ? History::deactivate() : History::activate();
			$newBlockId = $landing->addBlock($fields['CODE'] ?? '', $data, true);
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
	 * @return PublicActionResult
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
	 * @param bool $preventHistory True if no need save history
	 * @return PublicActionResult
	 */
	public static function markDeletedBlock(int $lid, int $block, bool $mark = true, bool $preventHistory = false): PublicActionResult
	{
		LandingCore::setEditMode();

		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		if ($landing->exist())
		{
			$preventHistory ? History::deactivate() : History::activate();
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
	 * @param bool $preventHistory True if no need save history
	 * @return PublicActionResult
	 */
	public static function markUnDeletedBlock(int $lid, int $block, bool $preventHistory = false): PublicActionResult
	{
		return self::markDeletedBlock($lid, $block, false, $preventHistory);
	}

	/**
	 * Sort the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param string $action Code: up or down.
	 * @return PublicActionResult
	 */
	private static function sort(int $lid, int $block, string $action): PublicActionResult
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		if ($landing->exist())
		{
			if ($action === 'up')
			{
				$result->setResult($landing->upBlock($block));
			}
			else
			{
				$result->setResult($landing->downBlock($block));
			}
			if ($landing->getError()->isEmpty())
			{
				$landing->resortBlocks();
			}
		}
		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Sort up the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param bool $preventHistory True if no need save history
	 * @return PublicActionResult
	 */
	public static function upBlock(int $lid, int $block, bool $preventHistory = false): PublicActionResult
	{
		LandingCore::setEditMode();
		$preventHistory ? History::deactivate() : History::activate();

		return self::sort($lid, $block, 'up');
	}

	/**
	 * Sort down the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param bool $preventHistory True if no need save history
	 * @return PublicActionResult
	 */
	public static function downBlock(int $lid, int $block, bool $preventHistory = false): PublicActionResult
	{
		LandingCore::setEditMode();
		$preventHistory ? History::deactivate() : History::activate();

		return self::sort($lid, $block, 'down');
	}

	/**
	 * Save the block in favorites.
	 * @param int $lid Landing id.
	 * @param int $block Block id.
	 * @param array $meta Meta info.
	 * @return PublicActionResult
	 */
	public static function favoriteBlock(int $lid, int $block, array $meta = []): PublicActionResult
	{
		$result = new PublicActionResult();
		LandingCore::setEditMode();
		$landing = LandingCore::createInstance($lid);
		if ($landing->exist())
		{
			$result->setResult($landing->favoriteBlock($block, $meta));
		}
		$result->setError($landing->getError());
		return $result;
	}

	/**
	 * Remove block from favorites. Only if block created by current user.
	 * @param int $blockId - id of deleted block
	 * @return PublicActionResult
	 */
	public static function unFavoriteBlock(int $blockId): PublicActionResult
	{
		$result = new PublicActionResult();
		LandingCore::setEditMode();
		$landing = LandingCore::createInstance(0);
		$delResult = $landing->unFavoriteBlock($blockId);
		if ($delResult)
		{
			$result->setResult($delResult);
		}
		else
		{
			$result->setError($landing->getError());
		}
		return $result;
	}

	/**
	 * Show/hide the block on the landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param string $action Code: show or hide.
	 * @return PublicActionResult
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
	 * @return PublicActionResult
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
	 * @return PublicActionResult
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
	 * @return PublicActionResult
	 */
	private static function changeParentOfBlock($lid, $block, array $params): PublicActionResult
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		$afterId = (int)($params['AFTER_ID'] ?? 0);
		if ($landing->exist())
		{
			if ($params['MOVE'])
			{
				$res = $landing->moveBlock((int)$block, $afterId);
			}
			else
			{
				$res = $landing->copyBlock((int)$block, $afterId);
			}

			if ($res)
			{
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
		}
		$result->setError($landing->getError());

		return $result;
	}

	/**
	 * Copy other block to this landing.
	 * @param int $lid Id of landing.
	 * @param int $block Block id.
	 * @param array $params Params array.
	 * @return PublicActionResult
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
	 * @return PublicActionResult
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
	 * @return PublicActionResult
	 */
	public static function getList(array $params = []): PublicActionResult
	{
		$result = new PublicActionResult();
		$params = $result->sanitizeKeys($params);
		$landingFake = LandingCore::createInstance(0);
		$getPreview = false;
		$getUrls = false;
		$checkArea = false;

		if ($params['filter']['SITE_ID'] ?? null)
		{
			$siteId = $params['filter']['SITE_ID'];
			if (is_array($siteId))
			{
				$siteId = array_shift($siteId);
			}
			$params['filter'][] = [
				'LOGIC' => 'OR',
				['FOLDER_ID' => null],
				['!FOLDER_ID' => Folder::getFolderIdsForSite($siteId, ['=DELETED' => 'Y']) ?: [-1]]
			];
		}

		if (isset($params['get_preview']))
		{
			$getPreview = !!$params['get_preview'];
			unset($params['get_preview']);
		}

		if (isset($params['get_urls']))
		{
			$getUrls = !!$params['get_urls'];
			unset($params['get_urls']);
		}

		if (isset($params['check_area']))
		{
			$checkArea = !!$params['check_area'];
			unset($params['check_area']);
		}

		if (isset($params['filter']['CHECK_PERMISSIONS']))
		{
			unset($params['filter']['CHECK_PERMISSIONS']);
		}

		$data = [];
		$rows = [];
		$publicUrls = [];

		$params['select'] = $params['select'] ?? ['*'];
		$params['select']['DOMAIN_ID'] = 'SITE.DOMAIN_ID';
		$res = LandingCore::getList($params);
		while ($row = $res->fetch())
		{
			$rows[$row['ID']] = $row;
		}

		if ($getPreview || $getUrls)
		{
			$publicUrls = LandingCore::createInstance(0)->getPublicUrl(array_keys($rows));
		}

		foreach ($rows as $row)
		{
			if (isset($row['DATE_CREATE']))
			{
				$row['DATE_CREATE'] = (string) $row['DATE_CREATE'];
			}
			if (isset($row['DATE_MODIFY']))
			{
				$row['DATE_MODIFY'] = (string) $row['DATE_MODIFY'];
			}
			if ($getUrls && isset($row['ID']))
			{
				$row['PUBLIC_URL'] = $publicUrls[$row['ID']];
			}
			if ($getPreview && isset($row['ID']))
			{
				if ($row['DOMAIN_ID'] == 0)
				{
					\Bitrix\Landing\Hook::setEditMode(true);
				}
				$row['PREVIEW'] = $landingFake->getPreview(
					$row['ID'],
					$row['DOMAIN_ID'] == 0,
					$publicUrls[$row['ID']]
				);
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
	 * Checks that page also adding in some menu.
	 * @param array $fields Landing data array.
	 * @param bool $willAdded Flag that menu item will be added.
	 * @return array
	 */
	protected static function checkAddingInMenu(array $fields, ?bool &$willAdded = null): array
	{
		$blockId = null;
		$menuCode = null;

		if (isset($fields['BLOCK_ID']))
		{
			$blockId = (int)$fields['BLOCK_ID'];
			unset($fields['BLOCK_ID']);
		}
		if (isset($fields['MENU_CODE']))
		{
			$menuCode = $fields['MENU_CODE'];
			unset($fields['MENU_CODE']);
		}

		if (!$blockId || !$menuCode || !is_string($menuCode))
		{
			return $fields;
		}

		$willAdded = true;

		LandingCore::callback('OnAfterAdd',
			function(\Bitrix\Main\Event $event) use ($blockId, $menuCode)
			{
				$primary = $event->getParameter('primary');
				$fields = $event->getParameter('fields');

				if ($primary)
				{
					$landingId = BlockCore::getLandingIdByBlockId($blockId);
					if ($landingId)
					{
						$updateData = [
							$menuCode => [
								[
									'text' => $fields['TITLE'],
									'href' => '#landing' . $primary['ID']
								]
							]
						];
						Block::updateNodes(
							$landingId,
							$blockId,
							$updateData,
							['appendMenu' => true]
						);
					}
				}
			}
		);


		return $fields;
	}

	/**
	 * Create new landing.
	 * @param array $fields Landing data.
	 * @return PublicActionResult
	 */
	public static function add(array $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$fields = self::clearDisallowFields($fields);
		$fields['ACTIVE'] = 'N';

		$fields = self::checkAddingInMenu($fields);

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
	 * Create a page by template.
	 * @param int $siteId Site id.
	 * @param string $code Code of template.
	 * @param array $fields Landing fields.
	 * @return PublicActionResult
	 */
	public static function addByTemplate($siteId, $code, array $fields = [])
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$willAdded = false;
		$siteId = intval($siteId);
		$fields = self::checkAddingInMenu($fields, $willAdded);

		$res = LandingCore::addByTemplate($siteId, $code, $fields);

		if ($res->isSuccess())
		{
			$result->setResult($res->getId());
			if (
				!$willAdded &&
				isset($fields['ADD_IN_MENU']) &&
				isset($fields['TITLE']) &&
				$fields['ADD_IN_MENU'] == 'Y'
			)
			{
				Site::addLandingToMenu($siteId, [
					'ID' => $res->getId(),
					'TITLE' => $fields['TITLE']
				]);
			}
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
	 * @return PublicActionResult
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
	 * @return PublicActionResult
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
	 * Move the page to site/folder.
	 * @param int $lid Landing id.
	 * @param int|null $toSiteId Site id.
	 * @param int|null $toFolderId Folder id (optional).
	 * @return PublicActionResult
	 */
	public static function move(int $lid, ?int $toSiteId = null, ?int $toFolderId = null): PublicActionResult
	{
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid);
		$result->setResult($landing->move($toSiteId ?: null, $toFolderId ?: null));
		$result->setError($landing->getError());
		return $result;
	}

	/**
	 * Copy landing.
	 * @param int $lid Landing id.
	 * @param int|null $toSiteId Site id (if you want copy in another site).
	 * @param int|null $toFolderId Folder id (if you want copy in some folder).
	 * @param bool $skipSystem If true, don't copy system flag.
	 * @return PublicActionResult
	 */
	public static function copy(int $lid, ?int $toSiteId = null, ?int $toFolderId = null, bool $skipSystem = false): PublicActionResult
	{
		$result = new PublicActionResult();

		LandingCore::disableCheckDeleted();
		$landing = LandingCore::createInstance($lid);
		$result->setResult($landing->copy($toSiteId ?: null, $toFolderId ?: null, false, Utils::isTrue($skipSystem)));
		$result->setError($landing->getError());
		LandingCore::enableCheckDeleted();

		return $result;
	}

	/**
	 * Mark entity as deleted.
	 * @param int $lid Entity id.
	 * @param boolean $mark Mark.
	 * @return PublicActionResult
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
	 * @return PublicActionResult
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
	 * @return PublicActionResult
	 */
	public static function uploadFile($lid, $picture, $ext = false, array $params = array())
	{
		static $internal = true;
		static $mixedParams = ['picture'];

		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$lid = intval($lid);

		$landing = LandingCore::createInstance($lid, [
			'skip_blocks' => true
		]);

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
	 * @param string $content Some content.
	 * @return PublicActionResult
	 */
	public static function updateHead($lid, $content)
	{
		static $internal = true;

		$lid = intval($lid);
		$result = new PublicActionResult();
		$landing = LandingCore::createInstance($lid, [
			'skip_blocks' => true
		]);
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
				'CODE' => 'CODE',
				'PUBLIC' => 'N'
			);
			$res = HookDataTable::getList(array(
				'select' => array(
					'ID', 'VALUE'
				),
				'filter' => $fields
			));
			if ($row = $res->fetch())
			{
				$existsContent = $row['VALUE'];

				// concat new fonts to the exists
				$found = preg_match_all(
					'#(<noscript>.*?<style.*?data-id="([^"]+)"[^>]*>[^<]+</style>)#is',
					$content,
					$newFonts
				);
				if ($found)
				{
					foreach ($newFonts[1] as $i => $newFont)
					{
						if (mb_strpos($existsContent, '"' . $newFonts[2][$i] . '"') === false)
						{
							$existsContent .= $newFont;
						}
					}
				}

				if ($existsContent != $row['VALUE'])
				{
					HookDataTable::update(
						$row['ID'],
						['VALUE' => $existsContent]
					);
				}
			}
			else
			{
				$fields['VALUE'] = $content;
				HookDataTable::add($fields);
			}

			if (Manager::getOption('public_hook_on_save') === 'Y')
			{
				Hook::setEditMode();
				Hook::publicationLanding($landing->getId());
			}

			$result->setResult(true);
		}

		$result->setError($landing->getError());

		return $result;
	}
}
