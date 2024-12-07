<?php

use Bitrix\Main\ArgumentException;

class CBPViewHelper
{
	private static $cachedTasks = array();

	public static function renderUserSearch($ID, $searchInputID, $dataInputID, $componentName, $siteID = '', $nameFormat = '', $delay = 0)
	{
		$ID = strval($ID);
		$searchInputID = strval($searchInputID);
		$dataInputID = strval($dataInputID);
		$componentName = strval($componentName);

		$siteID = strval($siteID);
		if($siteID === '')
		{
			$siteID = SITE_ID;
		}

		$nameFormat = strval($nameFormat);
		if($nameFormat === '')
		{
			$nameFormat = CSite::GetNameFormat(false);
		}

		$delay = intval($delay);
		if($delay < 0)
		{
			$delay = 0;
		}

		echo '<input type="text" id="', htmlspecialcharsbx($searchInputID) ,'" style="width:200px;"   >',
		'<input type="hidden" id="', htmlspecialcharsbx($dataInputID),'" name="', htmlspecialcharsbx($dataInputID),'" value="">';

		echo '<script>',
		'BX.ready(function(){',
		'BX.CrmUserSearchPopup.deletePopup("', $ID, '");',
		'BX.CrmUserSearchPopup.create("', $ID, '", { searchInput: BX("', CUtil::JSEscape($searchInputID), '"), dataInput: BX("', CUtil::JSEscape($dataInputID),'"), componentName: "', CUtil::JSEscape($componentName),'", user: {} }, ', $delay,');',
		'});</script>';

		$GLOBALS['APPLICATION']->IncludeComponent(
			'bitrix:intranet.user.selector.new',
			'',
			array(
				'MULTIPLE' => 'N',
				'NAME' => $componentName,
				'INPUT_NAME' => $searchInputID,
				'SHOW_EXTRANET_USERS' => 'NONE',
				'POPUP' => 'Y',
				'SITE_ID' => $siteID,
				'NAME_TEMPLATE' => $nameFormat
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}

	public static function getWorkflowTasks($workflowId, $withUsers = false, $extendUserInfo = false)
	{
		$withUsers = $withUsers ? 1 : 0;
		$extendUserInfo = $extendUserInfo ? 1 : 0;

		if (!$workflowId)
		{
			return ['COMPLETED' => [], 'RUNNING' => []];
		}

		if (!isset(self::$cachedTasks[$workflowId][$withUsers][$extendUserInfo]))
		{
			$tasks = array('COMPLETED' => array(), 'RUNNING' => array());
			$ids = array();
			$taskIterator = CBPTaskService::GetList(
				['ID' => 'DESC'],
				['WORKFLOW_ID' => $workflowId],
				false,
				['nTopCount' => 50],
				[
					'ID',
					'MODIFIED',
					'NAME',
					'DESCRIPTION',
					'PARAMETERS',
					'STATUS',
					'IS_INLINE',
					'ACTIVITY',
					'ACTIVITY_NAME',
					'CREATED_DATE',
					'DELEGATION_TYPE',
					'OVERDUE_DATE',
				],
			);
			while ($task = $taskIterator->getNext())
			{
				$key = $task['STATUS'] == CBPTaskStatus::Running ? 'RUNNING' : 'COMPLETED';
				$tasks[$key][] = $task;
				$ids[] = $task['ID'];
			}
			if ($withUsers && sizeof($ids))
			{
				$taskUsers = \CBPTaskService::getTaskUsers($ids);
				self::joinUsersToTasks($tasks['COMPLETED'], $taskUsers, $extendUserInfo);
				$tasks['RUNNING_ALL_USERS'] = self::joinUsersToTasks($tasks['RUNNING'], $taskUsers, $extendUserInfo);
			}
			$tasks['COMPLETED_CNT'] = sizeof($tasks['COMPLETED']);
			$tasks['RUNNING_CNT'] = sizeof($tasks['RUNNING']);

			self::$cachedTasks[$workflowId][$withUsers][$extendUserInfo] = $tasks;
		}

		return self::$cachedTasks[$workflowId][$withUsers][$extendUserInfo];
	}

	protected static function joinUsersToTasks(&$tasks, &$taskUsers, $extendUserInfo = false)
	{
		$allUsers = array();
		foreach ($tasks as &$t)
		{
			$t['USERS'] = array();
			$t['USERS_CNT'] = 0;
			if (isset($taskUsers[$t['ID']]))
			{
				foreach ($taskUsers[$t['ID']] as $u)
				{
					if ($extendUserInfo)
					{
						if (empty($u['FULL_NAME']))
							$u['FULL_NAME'] = self::getUserFullName($u);
						if (empty($u['PHOTO_SRC']))
							$u['PHOTO_SRC'] = self::getUserPhotoSrc($u);
					}
					$t['USERS'][] = $u;
					$t['USERS_CNT'] = sizeof($t['USERS']);
					$allUsers[] = $u;
				}
			}
		}
		return $allUsers;
	}

	public static function getUserPhotoSrc(array $user)
	{
		if (empty($user['PERSONAL_PHOTO']))
			return '';
		$arFileTmp = \CFile::ResizeImageGet(
			$user["PERSONAL_PHOTO"],
			array('width' => 58, 'height' => 58),
			\BX_RESIZE_IMAGE_EXACT,
			false
		);
		return $arFileTmp['src'];
	}

	public static function getUserFullName(array $user)
	{
		return \CUser::FormatName(\CSite::GetNameFormat(false), $user, true, false);
	}

	public static function getHtmlEditor($id, $fieldName, $content = '')
	{
		$id = htmlspecialcharsbx($id);
		$fieldName = htmlspecialcharsbx($fieldName);

		if (is_array($content) && isset($content['TEXT']))
		{
			$content = $content['TEXT'];
		}

		$result = '<textarea rows="5" cols="40" id="'.$id.'" name="'.$fieldName.'">'.htmlspecialcharsbx(\CBPHelper::stringify($content)).'</textarea>';

		if (CModule::includeModule("fileman"))
		{
			$editor = new \CHTMLEditor;
			$res = array(
				'useFileDialogs' => false,
				'height' => 200,
				'minBodyWidth' => 350,
				'normalBodyWidth' => 555,
				'bAllowPhp' => false,
				'limitPhpAccess' => false,
				'showTaskbars' => false,
				'showNodeNavi' => false,
				'askBeforeUnloadPage' => true,
				'bbCode' => false,
				'siteId' => SITE_ID,
				'autoResize' => true,
				'autoResizeOffset' => 40,
				'saveOnBlur' => true,
				'controlsMap' => array(
					array('id' => 'Bold',  'compact' => true, 'sort' => 80),
					array('id' => 'Italic',  'compact' => true, 'sort' => 90),
					array('id' => 'Underline',  'compact' => true, 'sort' => 100),
					array('id' => 'Strikeout',  'compact' => true, 'sort' => 110),
					array('id' => 'RemoveFormat',  'compact' => true, 'sort' => 120),
					array('id' => 'Color',  'compact' => true, 'sort' => 130),
					array('id' => 'FontSelector',  'compact' => false, 'sort' => 135),
					array('id' => 'FontSize',  'compact' => false, 'sort' => 140),
					array('separator' => true, 'compact' => false, 'sort' => 145),
					array('id' => 'OrderedList',  'compact' => true, 'sort' => 150),
					array('id' => 'UnorderedList',  'compact' => true, 'sort' => 160),
					array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
					array('separator' => true, 'compact' => false, 'sort' => 200),
					array('id' => 'InsertLink',  'compact' => true, 'sort' => 210, 'wrap' => 'bx-b-link-'.$id),
					array('id' => 'InsertImage',  'compact' => false, 'sort' => 220),
					array('id' => 'InsertVideo',  'compact' => true, 'sort' => 230, 'wrap' => 'bx-b-video-'.$id),
					array('id' => 'InsertTable',  'compact' => false, 'sort' => 250),
					array('id' => 'Code',  'compact' => true, 'sort' => 260),
					array('id' => 'Quote',  'compact' => true, 'sort' => 270, 'wrap' => 'bx-b-quote-'.$id),
					array('id' => 'Smile',  'compact' => false, 'sort' => 280),
					array('separator' => true, 'compact' => false, 'sort' => 290),
					array('id' => 'Fullscreen',  'compact' => false, 'sort' => 310),
					array('id' => 'BbCode',  'compact' => true, 'sort' => 340),
					array('id' => 'More',  'compact' => true, 'sort' => 400)
				),

				'name' => $fieldName.'[TEXT]',
				'inputName' => $fieldName.'[TEXT]',
				'id' => $id,
				'width' => '100%',
				'content' => htmlspecialcharsback($content),
			);

			ob_start();
			echo '<input type="hidden" name="'.$fieldName.'[TYPE]" value="html">';
			$editor->show($res);
			$result = ob_get_contents();
			ob_end_clean();
		}

		return $result;
	}

	public static function prepareTaskDescription($description)
	{
		$description = self::replaceFileLinks($description ?? '');

		if (\Bitrix\Main\Loader::includeModule('disk'))
		{
			$description = self::replaceDiskLinks($description);
		}

		return nl2br(trim($description));
	}

	public static function prepareMobileTaskDescription($description)
	{
		$description = self::replaceFileLinks($description ?? '', true);

		if (\Bitrix\Main\Loader::includeModule('disk'))
		{
			$description = self::replaceDiskLinks($description, true);
		}

		return nl2br($description);
	}

	private static function replaceFileLinks(string $description, $isMobile = false)
	{
		$callback = $isMobile ? self::getMobileFileLinksReplaceCallback() : self::getFileLinksReplaceCallback();

		return preg_replace_callback(
			'|<a href="(/bitrix/tools/bizproc_show_file.php\?)([^"]+)"[^>]*>|',
			$callback,
			$description
		);
	}

	private static function getFileLinksReplaceCallback()
	{
		return function($matches)
		{
			$matches[2] = htmlspecialcharsback($matches[2]);
			parse_str($matches[2], $query);
			if (isset($query['i']))
			{
				try
				{
					$attributes = \Bitrix\Main\UI\Viewer\ItemAttributes::tryBuildByFileId(
						$query['i'],
						$matches[1].$matches[2]
					);
					return "<a href=\"".$matches[1].$matches[2]."\" ".$attributes.">";
				}
				catch (ArgumentException $e) {}
			}

			return $matches[0];
		};
	}

	private static function getMobileFileLinksReplaceCallback()
	{
		return function ($matches)
		{
			$matches[2] = htmlspecialcharsback($matches[2]);
			parse_str($matches[2], $query);
			$filename = '';
			if (isset($query['f']))
			{
				$query['hash'] = md5($query['f']);
				$filename = $query['f'];
				unset($query['f']);
			}
			$query['mobile_action'] = 'bp_show_file';
			$query['filename'] = $filename;

			return '<a href="#" data-url="' . SITE_DIR . 'mobile/ajax.php?' . http_build_query($query)
				. '" data-name="' . htmlspecialcharsbx($filename)
				. '" onclick="BXMobileApp.UI.Document.open({url: this.getAttribute(\'data-url\'), '
				. 'filename: this.getAttribute(\'data-name\')}); return false;">'
			;
		};
	}

	private static function replaceDiskLinks(string $description, $isMobile = false)
	{
		$callback = $isMobile ? self::getMobileDiskLinksReplaceCallback() : self::getDiskLinksReplaceCallback();

		return preg_replace_callback(
			'|<a href="(/bitrix/tools/disk/uf.php\?)([^"]+)"[^>]*>([^<]+)|',
			$callback,
			$description
		);
	}

	private static function getDiskLinksReplaceCallback()
	{
		return function($matches)
		{
			$matches[2] = htmlspecialcharsback($matches[2]);
			parse_str($matches[2], $query);
			if (isset($query['attachedId']))
			{
				$attach = \Bitrix\Disk\AttachedObject::loadById($query['attachedId']);
				if ($attach)
				{
					try
					{
						$attributes = \Bitrix\Main\UI\Viewer\ItemAttributes::tryBuildByFileId(
							$attach->getFileId(),
							$matches[1].$matches[2]
						);
						return "<a href=\"".$matches[1].$matches[2]."\" ".$attributes.">".$matches[3];
					}
					catch (ArgumentException $e) {}
				}
			}

			return $matches[0];
		};
	}

	private static function getMobileDiskLinksReplaceCallback()
	{
		return function($matches)
		{
			$matches[2] = htmlspecialcharsback($matches[2]);
			parse_str($matches[2], $query);
			$filename = htmlspecialcharsback($matches[3]);
			$query['mobile_action'] = 'disk_uf_view';
			$query['filename'] = $filename;

			return '<a href="#" data-url="'.SITE_DIR.'mobile/ajax.php?'.http_build_query($query)
				.'" data-name="'.htmlspecialcharsbx($filename).'" onclick="BXMobileApp.UI.Document.open({url: this.getAttribute(\'data-url\'), filename: this.getAttribute(\'data-name\')}); return false;">'.$matches[3];
		};
	}
}
