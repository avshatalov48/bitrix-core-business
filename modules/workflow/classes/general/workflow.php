<?php

IncludeModuleLangFile(__FILE__);

class CWorkflow
{
	public static function OnPanelCreate()
	{
		global $APPLICATION, $USER;
		$cur_page_param = $APPLICATION->GetCurPageParam();
		$cur_page = $APPLICATION->GetCurPage(true);
		$cur_dir = $APPLICATION->GetCurDir();

		// New page
		$flow_link_new = self::GetEditLink([SITE_ID, rtrim(GetDirPath($cur_page), '/') . '/untitled.php'], $status_id, $status_title, 'standart.php', LANGUAGE_ID, $cur_page_param);
		$create_permission = $flow_link_new <> '' && $USER->CanDoFileOperation('fm_edit_in_workflow', [SITE_ID, $cur_dir]);
		// Document history
		$flow_link_hist = '/bitrix/admin/workflow_history_list.php?lang=' . LANGUAGE_ID . '&find_filename=' . urlencode($cur_page) . '&find_filename_exact_match=Y&set_filter=Y';
		$history_permission = $USER->CanDoFileOperation('fm_edit_in_workflow', [SITE_ID, $cur_page]);
		// Current page
		$flow_link_edit = self::GetEditLink([SITE_ID, $cur_page], $status_id, $status_title, '', LANGUAGE_ID, $cur_page_param);
		$edit_permission = $flow_link_edit <> '' && $history_permission;

		//Big button
		if ($edit_permission)
		{
			$public_edit = $APPLICATION->GetPopupLink([
				'URL' => $flow_link_edit . '&bxpublic=Y&from_module=workflow',
				'PARAMS' => [
					'min_width' => 700,
					'min_height' => 400,
					'height' => 700,
					'width' => 400,
				],
			]);

			$APPLICATION->AddPanelButton([
				'HREF' => 'javascript:' . $public_edit,
				'TYPE' => 'BIG',
				'ID' => 'edit',
				'ICON' => 'bx-panel-edit-page-icon',
				'ALT' => GetMessage('top_panel_edit_title'),
				'TEXT' => GetMessage('top_panel_edit_new'),
				'MAIN_SORT' => '200',
				'SORT' => 10,
				'MENU' => [],
				'HK_ID' => 'top_panel_edit_new',
				'RESORT_MENU' => true,
				'HINT' => [
					'TITLE' => GetMessage('top_panel_edit_new_tooltip_title'),
					'TEXT' => GetMessage('top_panel_edit_new_tooltip'),
				],
			]);
		}

		// New page
		if ($create_permission)
		{
			$APPLICATION->AddPanelButtonMenu('create', ['SEPARATOR' => true, 'SORT' => 49]);
			$APPLICATION->AddPanelButtonMenu('create', [
				'SRC' => '/bitrix/images/workflow/new_page.gif',
				'TEXT' => GetMessage('FLOW_PANEL_CREATE_WITH_WF'),
				'TITLE' => GetMessage('FLOW_PANEL_CREATE_ALT'),
				'ACTION' => "jsUtils.Redirect([], '" . CUtil::JSEscape($flow_link_new) . "')",
				'HK_ID' => 'FLOW_PANEL_CREATE_WITH_WF',
				'SORT' => 50,
			]);
		}

		if ($edit_permission || $history_permission)
		{
			$APPLICATION->AddPanelButtonMenu('edit', ['SEPARATOR' => true, 'SORT' => 79]);
		}

		// Current page
		if ($edit_permission)
		{
			$APPLICATION->AddPanelButtonMenu('edit', [
				'SRC' => '/bitrix/images/workflow/edit_flow_public.gif',
				'TEXT' => GetMessage('FLOW_PANEL_EDIT_WITH_WF'),
				'TITLE' => (intval($status_id) > 0 ? GetMessage('FLOW_CURRENT_STATUS') . ' [' . $status_id . '] ' . $status_title : GetMessage('FLOW_PANEL_EDIT_ALT')),
				'ACTION' => "jsUtils.Redirect([], '" . CUtil::JSEscape($flow_link_edit) . "')",
				'HK_ID' => 'FLOW_PANEL_EDIT_WITH_WF',
				'SORT' => 80,
			]);
		}

		// Document history
		if ($history_permission)
		{
			$flow_link_hist = '/bitrix/admin/workflow_history_list.php?lang=' . LANGUAGE_ID . '&find_filename=' . urlencode($cur_page) . '&find_filename_exact_match=Y&set_filter=Y';
			$APPLICATION->AddPanelButtonMenu('edit', [
				'SRC' => '/bitrix/images/workflow/history.gif',
				'TEXT' => GetMessage('FLOW_PANEL_HISTORY'),
				'TITLE' => GetMessage('FLOW_PANEL_HISTORY_ALT'),
				'ACTION' => "jsUtils.Redirect([], '" . CUtil::JSEscape($flow_link_hist) . "')",
				'HK_ID' => 'FLOW_PANEL_HISTORY',
				'SORT' => 81,
			]);
		}
	}

	public static function OnChangeFile($path, $site)
	{
		global $BX_WORKFLOW_PUBLISHED_PATH, $BX_WORKFLOW_PUBLISHED_SITE;
		if ($BX_WORKFLOW_PUBLISHED_PATH == $path && $BX_WORKFLOW_PUBLISHED_SITE == $site)
		{
			return;
		}

		global $DB, $USER, $APPLICATION;
		$HISTORY_SIMPLE_EDITING = COption::GetOptionString('workflow', 'HISTORY_SIMPLE_EDITING', 'N');
		if ($HISTORY_SIMPLE_EDITING == 'Y')
		{
			$HISTORY_COPIES = intval(COption::GetOptionString('workflow', 'HISTORY_COPIES', '10'));
			self::CleanUpHistoryCopies_SE($path, $HISTORY_COPIES - 1);
			if ($HISTORY_COPIES > 0)
			{
				$DOC_ROOT = \Bitrix\Main\SiteTable::getDocumentRoot($site === false ? null : $site);
				$filesrc = $APPLICATION->GetFileContent($DOC_ROOT . $path);
				$arContent = ParseFileContent($filesrc);
				$TITLE = $arContent['TITLE'];
				$BODY = $arContent['CONTENT'];
				$arFields = [
					'DOCUMENT_ID' => 0,
					'MODIFIED_BY' => $USER ? $USER->GetID() : 1,
					'TITLE' => $TITLE,
					'FILENAME' => $path,
					'SITE_ID' => $site,
					'BODY' => $BODY,
					'BODY_TYPE' => 'html',
					'STATUS_ID' => 1,
					'~TIMESTAMP_X' => $DB->CurrentTimeFunction(),
				];
				$DB->Add('b_workflow_log', $arFields, ['BODY'], 'workflow');
			}
		}
	}

	public static function SetHistory($DOCUMENT_ID)
	{
		global $DB;

		$LOG_ID = false;
		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$HISTORY_COPIES = intval(COption::GetOptionString('workflow', 'HISTORY_COPIES', '10'));
		$z = self::GetByID($DOCUMENT_ID);
		if ($zr = $z->Fetch())
		{
			self::CleanUpHistoryCopies($DOCUMENT_ID, $HISTORY_COPIES - 1);
			if ($HISTORY_COPIES > 0)
			{
				$arFields = [
					'DOCUMENT_ID' => $DOCUMENT_ID,
					'MODIFIED_BY' => $zr['MODIFIED_BY'],
					'TITLE' => $zr['TITLE'],
					'FILENAME' => $zr['FILENAME'],
					'SITE_ID' => $zr['SITE_ID'],
					'BODY' => $zr['BODY'],
					'BODY_TYPE' => $zr['BODY_TYPE'],
					'STATUS_ID' => $zr['STATUS_ID'],
					'COMMENTS' => $zr['COMMENTS'],
					'~TIMESTAMP_X' => $DB->CurrentTimeFunction(),
				];
				$LOG_ID = $DB->Add('b_workflow_log', $arFields, ['BODY'], 'workflow');
			}
		}

		return $LOG_ID;
	}

	// Deletes old copies from document's history
	public static function CleanUpHistoryCopies($DOCUMENT_ID=false, $HISTORY_COPIES=false)
	{
		global $DB;

		if ($HISTORY_COPIES === false)
		{
			$HISTORY_COPIES = intval(COption::GetOptionString('workflow', 'HISTORY_COPIES', '10'));
		}

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		if ($DOCUMENT_ID > 0)
		{
			$strSqlSearch = ' and ID = ' . $DOCUMENT_ID . ' ';
		}
		else
		{
			$strSqlSearch = '';
		}

		$strSql = 'SELECT ID FROM b_workflow_document WHERE 1=1 ' . $strSqlSearch;
		$z = $DB->Query($strSql);
		while ($zr = $z->Fetch())
		{
			$DID = $zr['ID'];
			$strSql = '
				SELECT
					ID
				FROM
					b_workflow_log
				WHERE
					DOCUMENT_ID = ' . $DID . '
				ORDER BY
					ID desc
				';
			$t = $DB->Query($strSql);
			$i = 0;
			$str_id = '0';
			while ($tr = $t->Fetch())
			{
				$i++;
				if ($i > $HISTORY_COPIES)
				{
					$str_id .= ', ' . $tr['ID'];
				}
			}
			$strSql = 'DELETE FROM b_workflow_log WHERE ID in (' . $str_id . ')';
			$DB->Query($strSql);
		}
	}

	// Deletes old copies from document's history (simple edit - SE)
	public static function CleanUpHistoryCopies_SE($FILENAME, $HISTORY_COPIES=false)
	{
		global $DB;

		if ($HISTORY_COPIES === false)
		{
			$HISTORY_COPIES = intval(COption::GetOptionString('workflow', 'HISTORY_COPIES', '10'));
		}
		$strSql = "
			SELECT
				ID
			FROM
				b_workflow_log
			WHERE
				FILENAME = '" . $DB->ForSql($FILENAME, 255) . "'
			and DOCUMENT_ID = 0
			ORDER BY
				ID desc
			";
		$t = $DB->Query($strSql);
		$i = 0;
		$str_id = '0';
		while ($tr = $t->Fetch())
		{
			$i++;
			if ($i > $HISTORY_COPIES)
			{
				$str_id .= ', ' . $tr['ID'];
			}
		}
		$strSql = 'DELETE FROM b_workflow_log WHERE ID in (' . $str_id . ')';
		$DB->Query($strSql);
	}

	// saves changes history and send e-mails on status change
	public static function SetMove($DOCUMENT_ID, $STATUS_ID, $OLD_STATUS_ID, $LOG_ID)
	{
		global $DB, $USER, $APPLICATION;

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$STATUS_ID = intval($STATUS_ID);
		$OLD_STATUS_ID = intval($OLD_STATUS_ID);
		$LOG_ID = intval($LOG_ID);

		$arFields = [
			'TIMESTAMP_X' => $DB->GetNowFunction(),
			'DOCUMENT_ID' => $DOCUMENT_ID,
			'OLD_STATUS_ID' => $OLD_STATUS_ID,
			'STATUS_ID' => $STATUS_ID,
			'LOG_ID' => $LOG_ID,
			'USER_ID' => intval($USER->GetID()),
		];
		$DB->Insert('b_workflow_move', $arFields);

		if ($STATUS_ID != $OLD_STATUS_ID)
		{
			CTimeZone::Disable();
			$d = self::GetByID($DOCUMENT_ID);
			CTimeZone::Enable();

			if ($dr = $d->Fetch())
			{
				$STATUS_ID = $dr['STATUS_ID'];

				// gather email of the workflow admins
				$WORKFLOW_ADMIN_GROUP_ID = COption::GetOptionInt('workflow', 'WORKFLOW_ADMIN_GROUP_ID', 0);
				$strSql = '
					SELECT
						U.ID,
						U.EMAIL
					FROM
						b_user U,
						b_user_group UG
					WHERE
						UG.GROUP_ID = ' . $WORKFLOW_ADMIN_GROUP_ID . "
						and U.ID = UG.USER_ID
						and U.ACTIVE = 'Y'
				";
				$a = $DB->Query($strSql);
				$arAdmin = [];
				while ($ar = $a->Fetch())
				{
					$arAdmin[$ar['ID']] = $ar['EMAIL'];
				}

				// gather email for BCC
				$arBCC = [];

				// gather all who changed doc in its current status
				$strSql = '
					SELECT
						USER_ID
					FROM
						b_workflow_move
					WHERE
						DOCUMENT_ID = ' . $DOCUMENT_ID . '
						and OLD_STATUS_ID = ' . $STATUS_ID . '
				';
				$z = $DB->Query($strSql);
				while ($zr = $z->Fetch())
				{
					$arBCC[$zr['EMAIL']] = $zr['EMAIL'];
				}

				// gather all editors
				// in case status have notifier flag
				$strSql = '
					SELECT DISTINCT
						UG.USER_ID
						,U.EMAIL
					FROM
						b_workflow_status S,
						b_workflow_status2group SG,
						b_user U,
						b_user_group UG
					WHERE
						S.ID = ' . $STATUS_ID . "
						and S.NOTIFY = 'Y'
						and SG.STATUS_ID = S.ID
						and SG.PERMISSION_TYPE = '2'
						and UG.GROUP_ID = SG.GROUP_ID
						and U.ID = UG.USER_ID
						and U.ACTIVE = 'Y'
				";
				$z = $DB->Query($strSql);
				while ($zr = $z->Fetch())
				{
					if (!array_key_exists($zr['EMAIL'], $arBCC))
					{
						$grp = [];
						$rs = $USER->GetUserGroupList($zr['USER_ID']);
						while ($ar = $rs->Fetch())
						{
							$grp[] = $ar['GROUP_ID'];
						}

						$arTasks = $APPLICATION->GetFileAccessPermission($dr['FILENAME'], $grp, true);
						foreach ($arTasks as $task_id)
						{
							$arOps = CTask::GetOperations($task_id, true);
							if (in_array('fm_edit_in_workflow', $arOps))
							{
								$arBCC[$zr['EMAIL']] = $zr['EMAIL'];

								break;
							}
						}
					}
				}

				unset($arBCC[$dr['EUSER_EMAIL']]);

				if (array_key_exists($dr['ENTERED_BY'], $arAdmin))
				{
					$dr['EUSER_NAME'] .= ' (Admin)';
				}

				// it is not new doc
				if ($OLD_STATUS_ID > 0)
				{
					if (array_key_exists($dr['MODIFIED_BY'], $arAdmin))
					{
						$dr['MUSER_NAME'] .= ' (Admin)';
					}
					$q = CWorkflowStatus::GetByID($OLD_STATUS_ID);
					$qr = $q->Fetch();
					// send change notification
					$arEventFields = [
						'ID' => $dr['ID'],
						'ADMIN_EMAIL' => implode(',', $arAdmin),
						'BCC' => implode(',', $arBCC),
						'PREV_STATUS_ID' => $OLD_STATUS_ID,
						'PREV_STATUS_TITLE' => $qr['TITLE'],
						'STATUS_ID' => $dr['STATUS_ID'],
						'STATUS_TITLE' => $dr['STATUS_TITLE'],
						'DATE_ENTER' => $dr['DATE_ENTER'],
						'ENTERED_BY_ID' => $dr['ENTERED_BY'],
						'ENTERED_BY_NAME' => $dr['EUSER_NAME'],
						'ENTERED_BY_EMAIL' => $dr['EUSER_EMAIL'],
						'DATE_MODIFY' => $dr['DATE_MODIFY'],
						'MODIFIED_BY_ID' => $dr['MODIFIED_BY'],
						'MODIFIED_BY_NAME' => $dr['MUSER_NAME'],
						'FILENAME' => $dr['FILENAME'],
						'SITE_ID' => $dr['SITE_ID'],
						'TITLE' => $dr['TITLE'],
						'BODY_HTML' => ($dr['BODY_TYPE'] == 'html' ? $dr['BODY'] : TxtToHTML($dr['BODY'])),
						'BODY_TEXT' => ($dr['BODY_TYPE'] == 'text' ? $dr['BODY'] : HtmlToTxt($dr['BODY'])),
						'BODY' => $dr['BODY'],
						'BODY_TYPE' => $dr['BODY_TYPE'],
						'COMMENTS' => $dr['COMMENTS'],
					];
					CEvent::Send('WF_STATUS_CHANGE', $dr['SITE_ID'], $arEventFields);
				}
				else // otherwise
				{
					// it was new one
					$arEventFields = [
						'ID' => $dr['ID'],
						'ADMIN_EMAIL' => implode(',', $arAdmin),
						'BCC' => implode(',', $arBCC),
						'STATUS_ID' => $dr['STATUS_ID'],
						'STATUS_TITLE' => $dr['STATUS_TITLE'],
						'DATE_ENTER' => $dr['DATE_ENTER'],
						'ENTERED_BY_ID' => $dr['ENTERED_BY'],
						'ENTERED_BY_NAME' => $dr['EUSER_NAME'],
						'ENTERED_BY_EMAIL' => $dr['EUSER_EMAIL'],
						'FILENAME' => $dr['FILENAME'],
						'SITE_ID' => $dr['SITE_ID'],
						'TITLE' => $dr['TITLE'],
						'BODY_HTML' => ($dr['BODY_TYPE'] == 'html' ? $dr['BODY'] : TxtToHTML($dr['BODY'])),
						'BODY_TEXT' => ($dr['BODY_TYPE'] == 'text' ? $dr['BODY'] : HtmlToTxt($dr['BODY'])),
						'BODY' => $dr['BODY'],
						'BODY_TYPE' => $dr['BODY_TYPE'],
						'COMMENTS' => $dr['COMMENTS'],
					];
					CEvent::Send('WF_NEW_DOCUMENT', $dr['SITE_ID'], $arEventFields);
				}
			}
		}
	}

	public static function Delete($DOCUMENT_ID)
	{
		global $DB;

		self::CleanUpFiles($DOCUMENT_ID);
		self::CleanUpPreview($DOCUMENT_ID);
		$DB->Query('DELETE FROM b_workflow_move WHERE DOCUMENT_ID = ' . intval($DOCUMENT_ID));
		$DB->Query('DELETE FROM b_workflow_document WHERE ID = ' . intval($DOCUMENT_ID));
	}

	public static function IsAdmin()
	{
		global $USER;

		if ($USER->IsAdmin())
		{
			return true;
		}
		else
		{
			$WORKFLOW_ADMIN_GROUP_ID = COption::GetOptionString('workflow', 'WORKFLOW_ADMIN_GROUP_ID');
			if (in_array($WORKFLOW_ADMIN_GROUP_ID, $USER->GetUserGroupArray()))
			{
				return true;
			}
		}

		return false;
	}

	// check edit rights for the document
	// depending on it's status and lock
	public static function IsAllowEdit($DOCUMENT_ID, &$locked_by, &$date_lock, $CHECK_RIGHTS='Y')
	{

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$LOCK_STATUS = self::GetLockStatus($DOCUMENT_ID, $locked_by, $date_lock);
		if ($LOCK_STATUS == 'red')
		{
			return false;
		}
		if ($LOCK_STATUS == 'yellow')
		{
			return true;
		}
		if ($LOCK_STATUS == 'green')
		{
			if ($CHECK_RIGHTS == 'Y')
			{
				return self::IsHaveEditRights($DOCUMENT_ID);
			}
			else
			{
				return true;
			}
		}

		return false;
	}

	public static function GetStatus($DOCUMENT_ID)
	{
		global $DB;

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$strSql = '
			SELECT
				S.*
			FROM
				b_workflow_document D,
				b_workflow_status S
			WHERE
				D.ID=' . $DOCUMENT_ID . '
			and	S.ID = D.STATUS_ID
			';
		$z = $DB->Query($strSql);

		return $z;
	}

	// check edit rights for the document
	// check is based only on status no lock
	public static function IsHaveEditRights($DOCUMENT_ID)
	{
		global $DB, $USER;

		if (self::IsAdmin())
		{
			return true;
		}

		$arGroups = $USER->GetUserGroupArray();
		if (!is_array($arGroups) || count($arGroups) <= 0)
		{
			$arGroups = [2];
		}

		$strSql = '
			SELECT
				G.ID
			FROM
				b_workflow_document D,
				b_workflow_status2group G
			WHERE
				D.ID = ' . intval($DOCUMENT_ID) . "
				and G.STATUS_ID = D.STATUS_ID
				and G.PERMISSION_TYPE >= '2'
				and G.GROUP_ID in (" . implode(', ', $arGroups) . ')
		';
		$z = $DB->Query($strSql);

		if ($zr = $z->Fetch())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function UnLock($DOCUMENT_ID)
	{
		global $DB, $USER;

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$z = self::GetByID($DOCUMENT_ID);
		$zr = $z->Fetch();
		if (self::IsAdmin() || $zr['LOCKED_BY'] == $USER->GetID())
		{
			$arFields = [
				'DATE_LOCK' => 'null',
				'LOCKED_BY' => 'null',
			];
			$rows = $DB->Update('b_workflow_document', $arFields, 'WHERE ID=' . $DOCUMENT_ID);

			return intval($rows);
		}

		return false;
	}

	public static function Lock($DOCUMENT_ID)
	{
		global $DB, $USER;

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$z = self::GetByID($DOCUMENT_ID);
		if ($zr = $z->Fetch())
		{
			if ($zr['STATUS_ID'] != 1)
			{
				$arFields = [
					'DATE_LOCK' => $DB->GetNowFunction(),
					'LOCKED_BY' => $USER->GetID(),
				];
				$DB->Update('b_workflow_document', $arFields, "WHERE ID='" . $DOCUMENT_ID . "'");
			}
		}
	}

	// return edit link depending on rights and status
	public static function GetEditLink($FILENAME, &$status_id, &$status_title, $template='', $lang=LANGUAGE_ID, $return_url='')
	{
		global $USER;

		$link = '';
		CMain::InitPathVars($SITE_ID, $FILENAME);

		if ($USER->CanDoFileOperation('fm_edit_in_workflow', [$SITE_ID, $FILENAME]))
		{
			//Check if user have access at least to one status
			if (!self::IsAdmin())
			{
				$arGroups = $USER->GetUserGroupArray();
				if (!is_array($arGroups))
				{
					$arGroups = [2];
				}
				$arFilter = [
					'GROUP_ID' => $arGroups,
					'PERMISSION_TYPE_1' => 1,
				];
				$rsStatuses = CWorkflowStatus::GetList('s_c_sort', 'asc', $arFilter, null, ['ID']);
				if (!$rsStatuses->Fetch())
				{
					return '';
				}
			}

			$link = '/bitrix/admin/workflow_edit.php?lang=' . $lang . '&site=' . $SITE_ID . '&fname=' . $FILENAME;
			if ($template <> '')
			{
				$link .= '&template=' . urlencode($template);
			}
			if ($return_url <> '')
			{
				$link .= '&return_url=' . urlencode($return_url);
			}
			$z = self::GetByFilename($FILENAME, $SITE_ID);
			if ($zr = $z->Fetch())
			{
				$status_id = $zr['STATUS_ID'];
				$status_title = $zr['STATUS_TITLE'];
				if ($status_id != 1)
				{
					$DOCUMENT_ID = $zr['ID'];
					if (self::IsHaveEditRights($DOCUMENT_ID))
					{
						$link .= '&ID=' . $DOCUMENT_ID;
					}
					else
					{
						return '';
					}
				}
			}
		}

		return $link;
	}

	public static function DeleteHistory($ID)
	{
		global $DB;
		$DB->Query('
			DELETE FROM b_workflow_log
			WHERE ID = ' . intval($ID) . '
		');
	}

	public static function CleanUp()
	{
		self::CleanUpPublished();
		self::CleanUpHistory();
		self::CleanUpFiles();
		self::CleanUpPreview();

		return 'CWorkflow::CleanUp();';
	}

	public static function CleanUpFiles($DOCUMENT_ID=false, $FILE_ID=false)
	{
		global $DB;

		if ($DOCUMENT_ID === false)
		{
			$strSql = 'SELECT TEMP_FILENAME FROM b_workflow_file WHERE DOCUMENT_ID is null';
		}
		else
		{
			$DOCUMENT_ID = intval($DOCUMENT_ID);
			$strSql = 'SELECT TEMP_FILENAME FROM b_workflow_file WHERE DOCUMENT_ID = ' . $DOCUMENT_ID;
		}
		if ($FILE_ID !== false)
		{
			$FILE_ID = intval($FILE_ID);
			$strSql .= ' and ID = ' . $FILE_ID;
		}
		$z = $DB->Query($strSql);
		while ($zr = $z->Fetch())
		{
			self::DeleteFile($zr['TEMP_FILENAME']);
		}
	}

	public static function CleanUpPreview($DOCUMENT_ID=false)
	{
		global $DB;

		if ($DOCUMENT_ID === false)
		{
			$strSql = '
				SELECT
					P.FILENAME, D.SITE_ID
				FROM
					b_workflow_document D,
					b_workflow_preview P
				WHERE
					D.STATUS_ID = 1
					and P.DOCUMENT_ID = D.ID
				';
		}
		else
		{
			$DOCUMENT_ID = intval($DOCUMENT_ID);
			$strSql = '
				SELECT
					FILENAME
				FROM
					b_workflow_preview
				WHERE
					DOCUMENT_ID = ' . $DOCUMENT_ID . '
				';
		}
		$z = $DB->Query($strSql);
		while ($zr = $z->Fetch())
		{
			self::DeletePreview($zr['FILENAME'], $zr['SITE']);
		}
	}

	public static function DeletePreview($FILENAME, $site = false)
	{
		global $DB;

		$strSql = "DELETE FROM b_workflow_preview WHERE FILENAME='" . $DB->ForSql($FILENAME, 255) . "'";
		$DB->Query($strSql);
		$DOC_ROOT = \Bitrix\Main\SiteTable::getDocumentRoot($site === false ? null : $site);
		$path = $DOC_ROOT . $FILENAME;
		if (file_exists($path))
		{
			unlink($path);
		}
	}

	public static function DeleteFile($FILENAME)
	{
		global $DB;

		$strSql = "DELETE FROM b_workflow_file WHERE TEMP_FILENAME='" . $DB->ForSql($FILENAME, 255) . "'";
		$DB->Query($strSql);
		$temp_path = self::GetTempDir() . $FILENAME;
		if (file_exists($temp_path))
		{
			unlink($temp_path);
		}
	}

	public static function IsFilenameExists($FILENAME)
	{
		global $DB;

		$strSql = "SELECT ID FROM b_workflow_file WHERE TEMP_FILENAME='" . $DB->ForSql($FILENAME, 255) . "'";
		$z = $DB->Query($strSql);
		$zr = $z->Fetch();

		return intval($zr['ID']);
	}

	public static function GetUniqueFilename($filename)
	{
		$ext = GetFileExtension($filename);
		$temp_file = md5($filename . uniqid(rand())) . '.' . $ext;
		while (self::IsFilenameExists($temp_file))
		{
			$temp_file = md5($filename . uniqid(rand())) . '.' . $ext;
		}

		return $temp_file;
	}

	public static function IsPreviewExists($FILENAME)
	{
		global $DB;

		$z = $DB->Query("
			SELECT ID
			FROM b_workflow_preview
			WHERE FILENAME='" . $DB->ForSql($FILENAME, 255) . "'
		");

		$zr = $z->Fetch();

		return intval($zr['ID']);
	}

	public static function GetUniquePreview($DOCUMENT_ID)
	{
		global $DB;

		$z = $DB->Query('
			SELECT FILENAME
			FROM b_workflow_document
			WHERE ID = ' . intval($DOCUMENT_ID) . '
		');

		$zr = $z->Fetch();
		if ($zr)
		{
			$DOCUMENT_PATH = GetDirPath($zr['FILENAME']);
			do
			{
				$temp_file = $DOCUMENT_PATH . md5(uniqid(rand())) . '.php';
			}
			while (self::IsPreviewExists($temp_file));
		}

		return $temp_file;
	}

	public static function SetStatus($DOCUMENT_ID, $STATUS_ID, $OLD_STATUS_ID, $history=true)
	{
		global $DB, $APPLICATION, $USER, $strError;

		//$arMsg = Array();
		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$STATUS_ID = intval($STATUS_ID);
		$OLD_STATUS_ID = intval($OLD_STATUS_ID);
		if ($STATUS_ID == 1) // if "[1] Published"
		{
			// get all files associated with the document
			$files = self::GetFileList($DOCUMENT_ID);
			while ($file = $files->Fetch())
			{
				$path = $file['FILENAME'];
				$DOC_ROOT = \Bitrix\Main\SiteTable::getDocumentRoot($file['SITE_ID'] === false ? null : $file['SITE_ID']);
				$pathto = $DOC_ROOT . $path;
				$pathfrom = self::GetTempDir() . $file['TEMP_FILENAME'];
				if (
					$USER->CanDoFileOperation('fm_edit_in_workflow', [$file['SITE_ID'], $path])
					&& $USER->CanDoFileOperation('fm_edit_existent_file', [$file['SITE_ID'], $path])
					&& $USER->CanDoFileOperation('fm_create_new_file', [$file['SITE_ID'], $path])
				)
				{
					if (!copy($pathfrom, $pathto))
					{
						$str = GetMessage('FLOW_CAN_NOT_WRITE_FILE', ['#FILENAME#' => $path]);
						$strError .= $str . '<br>';
					}
				}
				else
				{
					$str = GetMessage('FLOW_ACCESS_DENIED_FOR_FILE_WRITE', ['#FILENAME#' => $path]);
					$strError .= $str . '<br>';
				}
			}

			// still good
			if ($strError == '')
			{
				// publish the document
				$y = self::GetByID($DOCUMENT_ID);
				$yr = $y->Fetch();
				if (
					$USER->CanDoFileOperation('fm_edit_in_workflow', [$yr['SITE_ID'], $yr['FILENAME']])
					&& $USER->CanDoFileOperation('fm_edit_existent_file', [$yr['SITE_ID'], $yr['FILENAME']])
					&& $USER->CanDoFileOperation('fm_create_new_file', [$yr['SITE_ID'], $yr['FILENAME']])
				)
				{
					// save file
					$prolog = $yr['PROLOG'];
					if ($prolog <> '')
					{
						$title = $yr['TITLE'];
						$prolog = SetPrologTitle($prolog, $title);
					}
					$content = ($yr['BODY_TYPE'] == 'text') ? TxtToHTML($yr['BODY']) : $yr['BODY'];
					$content = WFToPath($content);
					$epilog = $yr['EPILOG'];
					$filesrc = $prolog . $content . $epilog;
					global $BX_WORKFLOW_PUBLISHED_PATH, $BX_WORKFLOW_PUBLISHED_SITE;
					$BX_WORKFLOW_PUBLISHED_PATH = $yr['FILENAME'];
					$BX_WORKFLOW_PUBLISHED_SITE = $yr['SITE_ID'];
					$DOC_ROOT = \Bitrix\Main\SiteTable::getDocumentRoot($yr['SITE_ID'] === false ? null : $yr['SITE_ID']);
					$APPLICATION->SaveFileContent($DOC_ROOT . $yr['FILENAME'], $filesrc);
					$BX_WORKFLOW_PUBLISHED_PATH = '';
					$BX_WORKFLOW_PUBLISHED_SITE = '';
				}
				else // otherwise
				{
					// throw error
					$str = GetMessage('FLOW_ACCESS_DENIED_FOLDER', ['#FILENAME#' => $yr['FILENAME']]);
					$strError .= GetMessage('FLOW_ERROR') . htmlspecialcharsbx($str) . '<br>';
				}
			}
		}

		if ($strError == '')
		{
			// update db
			$arFields = [
				'DATE_MODIFY' => $DB->GetNowFunction(),
				'MODIFIED_BY' => $USER->GetID(),
				'STATUS_ID' => intval($STATUS_ID),
			];
			$DB->Update('b_workflow_document', $arFields, 'WHERE ID=' . $DOCUMENT_ID);
			if ($history === true)
			{
				$LOG_ID = self::SetHistory($DOCUMENT_ID);
				self::SetMove($DOCUMENT_ID, $STATUS_ID, $OLD_STATUS_ID, $LOG_ID);
			}
		}
		else
		{
			$strError = GetMessage('FLOW_DOCUMENT_NOT_PUBLISHED') . '<br>' . $strError;
		}
		self::CleanUpPublished();
	}

	public static function LinkFiles2Document($arUploadedFiles, $DOCUMENT_ID)
	{
		global $DB;

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		if (is_array($arUploadedFiles) && count($arUploadedFiles) > 0)
		{
			foreach ($arUploadedFiles as $FILE_ID)
			{
				$FILE_ID = intval($FILE_ID);
				$strSql = 'UPDATE b_workflow_file SET DOCUMENT_ID=' . $DOCUMENT_ID . ' WHERE ID=' . $FILE_ID;
				$DB->Query($strSql);
			}
		}
		self::CleanUpFiles();
	}

	public static function GetFileByID($DOCUMENT_ID, $FILENAME)
	{
		global $DB;

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$strSql = '
			SELECT
				F.*
			FROM
				b_workflow_file F
			WHERE
				F.DOCUMENT_ID = ' . $DOCUMENT_ID . "
			and F.FILENAME = '" . $DB->ForSql($FILENAME, 255) . "'
			";
		$z = $DB->Query($strSql);

		return $z;
	}

	public static function GetTempDir()
	{
		$upload_dir = COption::GetOptionString('', 'upload_dir', '/upload/');
		$dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $upload_dir . '/workflow/';
		$dir = str_replace('//', '/', $dir);

		return $dir;
	}

	public static function GetFileContent($did, $fname, $wf_path='', $site=false)
	{
		global $DB, $APPLICATION, $USER;

		$did = intval($did);
		$io = CBXVirtualIo::GetInstance();

		// check if executable
		if (
			$USER->IsAdmin()
			|| (
				$io->ValidatePathString($fname)
				&& !HasScriptExtension($fname)
			)
		)
		{
			if ($did > 0)
			{
				// check if it is associated wtih document
				$z = self::GetFileByID($did, $fname);
				// found one
				if ($zr = $z->Fetch())
				{
					// get it's contents
					$path = self::GetTempDir() . $zr['TEMP_FILENAME'];
					if (file_exists($path))
					{
						return $APPLICATION->GetFileContent($path);
					}
				}
				else
				{
					// lookup in database
					$strSql = 'SELECT FILENAME, SITE_ID FROM b_workflow_document WHERE ID = ' . $did;
					$y = $DB->Query($strSql);
					// found
					if ($yr = $y->Fetch())
					{
						// get it's directory
						$path = GetDirPath($yr['FILENAME']);
						// absolute path
						$pathto = Rel2Abs($path, $fname);
						$DOC_ROOT = \Bitrix\Main\SiteTable::getDocumentRoot($yr['SITE_ID'] === false ? null : $yr['SITE_ID']);
						$path = $DOC_ROOT . $pathto;
						// give it another try
						$u = self::GetFileByID($did, $pathto);
						// found
						if ($ur = $u->Fetch())
						{
							// get it's contents
							$path = self::GetTempDir() . $ur['TEMP_FILENAME'];
							if (file_exists($path))
							{
								return $APPLICATION->GetFileContent($path);
							}
						}
						elseif (file_exists($path)) // it is already on disk
						{
							// get it's contents
							if ($USER->CanDoFileOperation('fm_view_file', [$yr['SITE_ID'], $pathto]))
							{
								return $APPLICATION->GetFileContent($path);
							}
						}
					}
				}
			}
			$DOC_ROOT = \Bitrix\Main\SiteTable::getDocumentRoot($site === false ? null : $site);
			// new one
			if ($wf_path <> '')
			{
				$pathto = Rel2Abs($wf_path, $fname);
				$path = $DOC_ROOT . $pathto;
				if (file_exists($path)) // it is already on disk
				{
					// get it's contents
					if ($USER->CanDoFileOperation('fm_view_file', [$site, $pathto]))
					{
						$src = $APPLICATION->GetFileContent($path);

						return $src;
					}
				}
			}

			// still failed to find
			// get path
			$path = $DOC_ROOT . $fname;
			if (file_exists($path))
			{
				// get it's contents
				if ($USER->CanDoFileOperation('fm_view_file', [$site, $fname]))
				{
					return $APPLICATION->GetFileContent($path);
				}
			}
		}
// it is executable
		else
		{
			return GetMessage('FLOW_ACCESS_DENIED_PHP_VIEW');
		}
	}

	public static function __CheckSite($site)
	{
		if ($site !== false)
		{
			if ($site <> '')
			{
				$res = CSite::GetByID($site);
				if (!$res->Fetch())
				{
					$site = false;
				}
			}
			else
			{
				$site = false;
			}
		}

		return $site;
	}

	public static function Insert($arFields)
	{
		global $DB;

		$arFields['~DATE_MODIFY'] = $DB->CurrentTimeFunction();
		$arFields['~DATE_ENTER'] = $DB->CurrentTimeFunction();
		$ID = $DB->Add('b_workflow_document', $arFields, [], 'workflow');
		$LOG_ID = self::SetHistory($ID);
		self::SetMove($ID, $arFields['STATUS_ID'], 0, $LOG_ID);

		return $ID;
	}

	public static function Update($arFields, $DOCUMENT_ID)
	{
		global $DB;

		$z = self::GetByID($DOCUMENT_ID);
		$change = false;
		if ($zr = $z->Fetch())
		{
			if (
				$zr['STATUS_ID'] != $arFields['STATUS_ID']
				|| $zr['BODY'] != $arFields['BODY']
				|| $zr['BODY_TYPE'] != $arFields['BODY_TYPE']
				|| $zr['COMMENTS'] != $arFields['COMMENTS']
				|| $zr['FILENAME'] != $arFields['FILENAME']
				|| $zr['SITE_ID'] != $arFields['SITE_ID']
				|| $zr['TITLE'] != $arFields['TITLE']
			)
			{
				$change = true;
			}
		}

		$strUpdate = $DB->PrepareUpdate('b_workflow_document', $arFields, 'workflow');
		if ($strUpdate)
		{
			$DB->Query('
				UPDATE b_workflow_document
				SET ' . $strUpdate . ', DATE_MODIFY=now(), DATE_ENTER=now()
				WHERE ID = ' . $DOCUMENT_ID,
			);
		}

		if ($change)
		{
			$LOG_ID = self::SetHistory($DOCUMENT_ID);
			self::SetMove($DOCUMENT_ID, $arFields['STATUS_ID'], $zr['STATUS_ID'], $LOG_ID);
		}
	}

	public static function GetLockStatus($DOCUMENT_ID, &$locked_by, &$date_lock)
	{
		global $DB, $USER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$MAX_LOCK = COption::GetOptionInt('workflow', 'MAX_LOCK_TIME', '60');
		$uid = intval($USER->GetID());
		$strSql = '
			SELECT
				LOCKED_BY,
				' . $DB->DateToCharFunction('DATE_LOCK') . " DATE_LOCK,
				case
				when DATE_LOCK is null then 'green'
				when " . $helper->addSecondsToDateTime($MAX_LOCK * 60, 'DATE_LOCK') . " < now() then 'green'
				when LOCKED_BY = " . $uid . " then 'yellow'
				else 'red' end LOCK_STATUS
			FROM
				b_workflow_document
			WHERE
				ID = " . $DOCUMENT_ID . '
			';
		$z = $DB->Query($strSql);
		$zr = $z->Fetch();
		$locked_by = $zr ? $zr['LOCKED_BY'] : 0;
		$date_lock = $zr? $zr['DATE_LOCK'] : false;

		return $zr['LOCK_STATUS'];
	}

	public static function GetList($by = 's_date_modify', $order = 'desc', $arFilter = [])
	{
		global $DB, $USER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$obQueryWhere = new CSQLWhere();
		static $arWhereFields = [
			'ID' => [
				'TABLE_ALIAS' => 'D',
				'FIELD_NAME' => 'D.ID',
				'FIELD_TYPE' => 'int', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			],
			'MODIFIED_BY' => [
				'TABLE_ALIAS' => 'D',
				'FIELD_NAME' => 'D.MODIFIED_BY',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
			'STATUS_ID' => [
				'TABLE_ALIAS' => 'D',
				'FIELD_NAME' => 'D.STATUS_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
		];
		$obQueryWhere->SetFields($arWhereFields);

		$arSqlSearch = [];
		$MAX_LOCK = COption::GetOptionInt('workflow', 'MAX_LOCK_TIME', '60');
		$arGroups = $USER->GetUserGroupArray();
		if (!is_array($arGroups))
		{
			$arGroups[] = 2;
		}
		$groups = implode(', ', $arGroups);
		$uid = intval($USER->GetID());
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if ((string)$val == '' || (string)$val == 'NOT_REF')
				{
					continue;
				}
				if (is_array($val) && count($val) <= 0)
				{
					continue;
				}
				$match_value_set = array_key_exists($key . '_EXACT_MATCH', $arFilter);
				$key = strtoupper($key);
				switch ($key)
				{
					case 'ID':
					case 'STATUS_ID':
						$arSqlSearch[] = $obQueryWhere->GetQuery([$key => $val]);

						break;
					case 'DATE_MODIFY_1':
						if (CheckDateTime($val))
						{
							$arSqlSearch[] = 'D.DATE_MODIFY >= ' . $DB->CharToDateFunction($val, 'SHORT');
						}

						break;
					case 'DATE_MODIFY_2':
						if (CheckDateTime($val))
						{
							$arSqlSearch[] = 'D.DATE_MODIFY < ' . $DB->CharToDateFunction($val, 'SHORT') . ' + INTERVAL 1 DAY';
						}

						break;
					case 'MODIFIED_BY':
						$match = ($match_value_set && $arFilter[$key . '_EXACT_MATCH'] == 'Y') ? 'N' : 'Y';
						$filter = GetFilterQuery('UM.LOGIN, UM.NAME, UM.LAST_NAME', $val, $match);
						if ($filter)
						{
							$arSqlSearch[] = $obQueryWhere->GetQuery(['MODIFIED_BY' => $val]) . ' or ' . $filter;
						}

						break;
					case 'MODIFIED_USER_ID':
						$arSqlSearch[] = $obQueryWhere->GetQuery(['MODIFIED_BY' => $val]);

						break;
					case 'LOCK_STATUS':
						$arSqlSearch[] = "
							case
								when D.DATE_LOCK is null then 'green'
								when " . $helper->addSecondsToDateTime($MAX_LOCK * 60, 'D.DATE_LOCK') . " < now() then 'green'
								when D.LOCKED_BY = " . $uid . " then 'yellow'
								else 'red' end = '" . $DB->ForSql($val) . "'";

						break;
					case 'STATUS':
						$match = ($match_value_set && $arFilter[$key . '_EXACT_MATCH'] == 'Y') ? 'N' : 'Y';
						$filter = GetFilterQuery('S.TITLE', $val, $match);
						if ($filter)
						{
							$arSqlSearch[] = $obQueryWhere->GetQuery(['STATUS_ID' => $val]) . ' or ' . $filter;
						}

						break;
					case 'SITE_ID':
					case 'TITLE':
					case 'BODY':
						$match = ($match_value_set && $arFilter[$key . '_EXACT_MATCH'] == 'Y') ? 'N' : 'Y';
						$arSqlSearch[] = GetFilterQuery('D.' . $key, $val, $match);

						break;
					case 'FILENAME':
						$match = ($match_value_set && $arFilter[$key . '_EXACT_MATCH'] == 'Y') ? 'N' : 'Y';
						$arSqlSearch[] = GetFilterQuery('D.FILENAME', $val, $match, ['/', '\\', '.', '_']);

						break;
				}
			}
		}

		if ($by === 's_id')
		{
			$strSqlOrder = 'ORDER BY D.ID';
		}
		elseif ($by === 's_lock_status')
		{
			$strSqlOrder = 'ORDER BY LOCK_STATUS';
		}
		elseif ($by === 's_date_modify')
		{
			$strSqlOrder = 'ORDER BY D.DATE_MODIFY';
		}
		elseif ($by === 's_modified_by')
		{
			$strSqlOrder = 'ORDER BY D.MODIFIED_BY';
		}
		elseif ($by === 's_filename')
		{
			$strSqlOrder = 'ORDER BY D.FILENAME';
		}
		elseif ($by === 's_title')
		{
			$strSqlOrder = 'ORDER BY D.TITLE';
		}
		elseif ($by === 's_site_id')
		{
			$strSqlOrder = 'ORDER BY D.SITE_ID';
		}
		elseif ($by === 's_status')
		{
			$strSqlOrder = 'ORDER BY D.STATUS_ID';
		}
		else
		{
			$strSqlOrder = 'ORDER BY D.DATE_MODIFY';
		}

		if ($order != 'asc')
		{
			$strSqlOrder .= ' desc ';
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if (self::IsAdmin())
		{
			$strSql = '
				SELECT DISTINCT
					D.*,
					' . $DB->DateToCharFunction('D.DATE_ENTER') . ' DATE_ENTER,
					' . $DB->DateToCharFunction('D.DATE_MODIFY') . ' DATE_MODIFY,
					' . $DB->DateToCharFunction('D.DATE_LOCK') . " DATE_LOCK,
					concat_ws(' ', " . $DB->Concat("'('", 'UM.LOGIN', "')'") . ", nullif(UM.NAME, ''), nullif(UM.LAST_NAME, '')) MUSER_NAME,
					concat_ws(' ', " . $DB->Concat("'('", 'UE.LOGIN', "')'") . ", nullif(UE.NAME, ''), nullif(UE.LAST_NAME, '')) EUSER_NAME,
					S.TITLE STATUS_TITLE,
					case
						when D.DATE_LOCK is null then 'green'
						when " . $helper->addSecondsToDateTime($MAX_LOCK * 60, 'D.DATE_LOCK') . " < now() then 'green'
						when D.LOCKED_BY = " . $uid . " then 'yellow'
					else 'red' end LOCK_STATUS
				FROM
					b_workflow_document D
					LEFT JOIN b_workflow_status S ON (S.ID = D.STATUS_ID)
					LEFT JOIN b_user UM ON (UM.ID = D.MODIFIED_BY)
					LEFT JOIN b_user UE ON (UE.ID = D.ENTERED_BY)
				WHERE
				" . $strSqlSearch . '
				' . $strSqlOrder . '
				';
		}
		else
		{
			$strSql = '
				SELECT DISTINCT
					D.*,
					' . $DB->DateToCharFunction('D.DATE_ENTER') . ' DATE_ENTER,
					' . $DB->DateToCharFunction('D.DATE_MODIFY') . ' DATE_MODIFY,
					' . $DB->DateToCharFunction('D.DATE_LOCK') . " DATE_LOCK,
					concat_ws(' ', " . $DB->Concat("'('", 'UM.LOGIN', "')'") . ", nullif(UM.NAME, ''), nullif(UM.LAST_NAME, '')) MUSER_NAME,
					concat_ws(' ', " . $DB->Concat("'('", 'UE.LOGIN', "')'") . ", nullif(UE.NAME, ''), nullif(UE.LAST_NAME, '')) EUSER_NAME,
					S.TITLE STATUS_TITLE,
					case
						when D.DATE_LOCK is null then 'green'
						when " . $helper->addSecondsToDateTime($MAX_LOCK * 60, 'D.DATE_LOCK') . " < now() then 'green'
						when D.LOCKED_BY = " . $uid . " then 'yellow'
					else 'red' end LOCK_STATUS
				FROM
					b_workflow_document D
					INNER JOIN b_workflow_status2group G ON (G.STATUS_ID = D.STATUS_ID)
					LEFT JOIN b_workflow_status S ON (S.ID = D.STATUS_ID)
					LEFT JOIN b_user UM ON (UM.ID = D.MODIFIED_BY)
					LEFT JOIN b_user UE ON (UE.ID = D.ENTERED_BY)
				WHERE
				" . $strSqlSearch . '
				and G.GROUP_ID in (' . $groups . ")
				and G.PERMISSION_TYPE >= '2'
				" . $strSqlOrder . '
				';
		}

		$rs = $DB->Query($strSql);
		$arr = [];
		while ($ar = $rs->Fetch())
		{
			if ($USER->CanDoFileOperation('fm_edit_in_workflow', [$ar['SITE_ID'], $ar['FILENAME']]))
			{
				$arr[] = $ar;
			}
		}
		$rs = new CDBResult();
		$rs->InitFromArray($arr);

		return $rs;
	}

	public static function GetByID($ID)
	{
		global $DB, $USER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$ID = intval($ID);
		$MAX_LOCK = COption::GetOptionInt('workflow', 'MAX_LOCK_TIME', '60');
		$uid = intval($USER->GetID());
		$strSql = '
			SELECT
				D.*,
				' . $DB->DateToCharFunction('D.DATE_ENTER') . ' DATE_ENTER,
				' . $DB->DateToCharFunction('D.DATE_MODIFY') . ' DATE_MODIFY,
				' . $DB->DateToCharFunction('D.DATE_LOCK') . " DATE_LOCK,
				concat_ws(' ', " . $DB->Concat("'('", 'UM.LOGIN', "')'") . ", nullif(UM.NAME, ''), nullif(UM.LAST_NAME, '')) MUSER_NAME,
				concat_ws(' ', " . $DB->Concat("'('", 'UE.LOGIN', "')'") . ", nullif(UE.NAME, ''), nullif(UE.LAST_NAME, '')) EUSER_NAME,
				concat_ws(' ', " . $DB->Concat("'('", 'UL.LOGIN', "')'") . ", nullif(UL.NAME, ''), nullif(UL.LAST_NAME, '')) LUSER_NAME,
				UE.EMAIL EUSER_EMAIL,
				S.TITLE STATUS_TITLE,
				case
					when D.DATE_LOCK is null then 'green'
					when " . $helper->addSecondsToDateTime($MAX_LOCK * 60, 'D.DATE_LOCK') . " < now() then 'green'
					when D.LOCKED_BY = " . $uid . " then 'yellow'
				else 'red' end LOCK_STATUS
			FROM
				b_workflow_document D
				LEFT JOIN b_user UM ON (UM.ID = D.MODIFIED_BY)
				LEFT JOIN b_user UE ON (UE.ID = D.ENTERED_BY)
				LEFT JOIN b_user UL ON (UL.ID = D.LOCKED_BY)
				LEFT JOIN b_workflow_status S ON (S.ID = D.STATUS_ID)
			WHERE
				D.ID = " . $ID . '
			';
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetByFilename($FILENAME, $SITE_ID, $arFilter = false)
	{
		if (!is_array($arFilter))
		{
			$arFilter = [
				'!STATUS_ID' => 1,
			];
		}

		$obQueryWhere = new CSQLWhere();
		$obQueryWhere->SetFields([
			'STATUS_ID' => [
				'TABLE_ALIAS' => 'D',
				'FIELD_NAME' => 'D.STATUS_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
		]);
		$strSqlWhere = $obQueryWhere->GetQuery($arFilter);

		global $DB, $USER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$MAX_LOCK = COption::GetOptionInt('workflow', 'MAX_LOCK_TIME', '60');
		$uid = intval($USER->GetID());
		$strSql = '
			SELECT
				D.*,
				' . $DB->DateToCharFunction('D.DATE_ENTER') . ' DATE_ENTER,
				' . $DB->DateToCharFunction('D.DATE_MODIFY') . ' DATE_MODIFY,
				' . $DB->DateToCharFunction('D.DATE_LOCK') . " DATE_LOCK,
				concat_ws(' ', " . $DB->Concat("'('", 'UM.LOGIN', "')'") . ", nullif(UM.NAME, ''), nullif(UM.LAST_NAME, '')) MUSER_NAME,
				concat_ws(' ', " . $DB->Concat("'('", 'UE.LOGIN', "')'") . ", nullif(UE.NAME, ''), nullif(UE.LAST_NAME, '')) EUSER_NAME,
				concat_ws(' ', " . $DB->Concat("'('", 'UL.LOGIN', "')'") . ", nullif(UL.NAME, ''), nullif(UL.LAST_NAME, '')) LUSER_NAME,
				S.TITLE STATUS_TITLE,
				case
					when D.DATE_LOCK is null then 'green'
					when " . $helper->addSecondsToDateTime($MAX_LOCK * 60, 'D.DATE_LOCK') . " < now() then 'green'
					when D.LOCKED_BY = " . $uid . " then 'yellow'
				else 'red' end LOCK_STATUS
			FROM
				b_workflow_document D
				LEFT JOIN b_user UM ON (UM.ID = D.MODIFIED_BY)
				LEFT JOIN b_user UE ON (UE.ID = D.ENTERED_BY)
				LEFT JOIN b_user UL ON (UL.ID = D.LOCKED_BY)
				LEFT JOIN b_workflow_status S ON (S.ID = D.STATUS_ID)
			WHERE
				SITE_ID = '" . $DB->ForSql($SITE_ID, 2) . "'
				AND D.FILENAME = '" . $DB->ForSql($FILENAME, 255) . "'
				" . ($strSqlWhere ? 'AND ' . $strSqlWhere : '') . '
		';
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetHistoryList($by = 's_id', $order = 'desc', $arFilter = [])
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$obQueryWhere = new CSQLWhere();
		static $arWhereFields = [
			'ID' => [
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.ID',
				'FIELD_TYPE' => 'int', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
			],
			'DOCUMENT_ID' => [
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.DOCUMENT_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
			'MODIFIED_BY' => [
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.MODIFIED_BY',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
			'STATUS_ID' => [
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.STATUS_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
		];
		$obQueryWhere->SetFields($arWhereFields);

		$arSqlSearch = [];
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if ((string)$val == '' || (string)$val == 'NOT_REF')
				{
					continue;
				}
				if (is_array($val) && !$val)
				{
					continue;
				}
				$match_value_set = array_key_exists($key . '_EXACT_MATCH', $arFilter);
				$key = strtoupper($key);
				switch ($key)
				{
					case 'ID':
					case 'DOCUMENT_ID':
					case 'MODIFIED_BY':
					case 'STATUS_ID':
						$arSqlSearch[] = $obQueryWhere->GetQuery([$key => $val]);

						break;
					case 'DATE_MODIFY_1':
						if (CheckDateTime($val))
						{
							$arSqlSearch[] = 'L.TIMESTAMP_X >= ' . $DB->CharToDateFunction($val, 'SHORT');
						}

						break;
					case 'DATE_MODIFY_2':
						if (CheckDateTime($val))
						{
							$arSqlSearch[] = 'L.TIMESTAMP_X < ' . $helper->addDaysToDateTime(1, $DB->CharToDateFunction($val, 'SHORT'));
						}

						break;
					case 'MODIFIED_USER':
						$match = ($match_value_set && $arFilter[$key . '_EXACT_MATCH'] == 'Y') ? 'N' : 'Y';
						$filter = GetFilterQuery('U.LOGIN, U.NAME, U.LAST_NAME', $val, $match);
						if ($filter)
						{
							$arSqlSearch[] = $obQueryWhere->GetQuery(['MODIFIED_BY' => $val]) . ' or ' . $filter;
						}

						break;
					case 'TITLE':
					case 'SITE_ID':
						$match = ($match_value_set && $arFilter[$key . '_EXACT_MATCH'] == 'Y') ? 'N' : 'Y';
						$arSqlSearch[] = GetFilterQuery('L.' . $key, $val, $match);

						break;
					case 'FILENAME':
						$match = ($match_value_set && $arFilter[$key . '_EXACT_MATCH'] == 'Y') ? 'N' : 'Y';
						$arSqlSearch[] = GetFilterQuery('L.FILENAME', $val, $match, ['/', '\\', '.', '_']);

						break;
					case 'BODY':
						$match = ($match_value_set && $arFilter[$key . '_EXACT_MATCH'] == 'N') ? 'Y' : 'N';
						$arSqlSearch[] = GetFilterQuery('L.BODY', $val, $match, [], 'Y');

						break;
					case 'STATUS':
						$match = ($match_value_set && $arFilter[$key . '_EXACT_MATCH'] == 'Y') ? 'N' : 'Y';
						$filter = GetFilterQuery('S.TITLE', $val, $match);
						if ($filter)
						{
							$arSqlSearch[] = $obQueryWhere->GetQuery(['STATUS_ID' => $val]) . ' or ' . $filter;
						}

						break;
				}
			}
		}

		if ($by === 's_id')
		{
			$strSqlOrder = 'ORDER BY L.ID';
		}
		elseif ($by === 's_document_id')
		{
			$strSqlOrder = 'ORDER BY L.DOCUMENT_ID';
		}
		elseif ($by === 's_date_modify')
		{
			$strSqlOrder = 'ORDER BY L.TIMESTAMP_X';
		}
		elseif ($by === 's_modified_by')
		{
			$strSqlOrder = 'ORDER BY L.MODIFIED_BY';
		}
		elseif ($by === 's_filename')
		{
			$strSqlOrder = 'ORDER BY L.FILENAME';
		}
		elseif ($by === 's_site_id')
		{
			$strSqlOrder = 'ORDER BY L.SITE_ID';
		}
		elseif ($by === 's_title')
		{
			$strSqlOrder = 'ORDER BY L.TITLE';
		}
		elseif ($by === 's_status')
		{
			$strSqlOrder = 'ORDER BY L.STATUS_ID';
		}
		else
		{
			$strSqlOrder = 'ORDER BY L.ID';
		}

		if ($order != 'asc')
		{
			$strSqlOrder .= ' desc ';
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = '
			SELECT DISTINCT
				L.*,
				' . $DB->DateToCharFunction('L.TIMESTAMP_X') . " TIMESTAMP_X,
				concat_ws(' ', " . $DB->Concat("'('", 'U.LOGIN', "')'") . ", nullif(U.NAME, ''), nullif(U.LAST_NAME, '')) USER_NAME,
				S.TITLE STATUS_TITLE
			FROM
				b_workflow_log L
				LEFT JOIN b_workflow_status S ON (S.ID = L.STATUS_ID)
				LEFT JOIN b_user U ON (U.ID = L.MODIFIED_BY)
			WHERE
			" . $strSqlSearch . '
			' . $strSqlOrder . '
			';

		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetHistoryByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql = '
			SELECT DISTINCT
				L.*,
				' . $DB->DateToCharFunction('L.TIMESTAMP_X') . " TIMESTAMP_X,
				concat_ws(' ', " . $DB->Concat("'('", 'U.LOGIN', "')'") . ", nullif(U.NAME, ''), nullif(U.LAST_NAME, '')) USER_NAME,
				S.TITLE STATUS_TITLE
			FROM
				b_workflow_log L
				LEFT JOIN b_workflow_status S ON (S.ID = L.STATUS_ID)
				LEFT JOIN b_user U ON (U.ID = L.MODIFIED_BY)
			WHERE
				L.ID = " . $ID . '
		';
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function CleanUpHistory()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$HISTORY_DAYS = COption::GetOptionInt('workflow', 'HISTORY_DAYS', '-1');
		if ($HISTORY_DAYS >= 0)
		{
			$strSql = 'DELETE FROM b_workflow_log WHERE TIMESTAMP_X < ' . $helper->addDaysToDateTime(-$HISTORY_DAYS);
			$connection->query($strSql);
		}

		if (CModule::IncludeModule('iblock'))
		{
			CIblockElement::WF_CleanUpHistory();
		}
	}

	public static function CleanUpPublished()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$DAYS_AFTER_PUBLISHING = COption::GetOptionInt('workflow', 'DAYS_AFTER_PUBLISHING', '0');
		if ($DAYS_AFTER_PUBLISHING >= 0)
		{
			$strSql = '
				SELECT
					ID
				FROM
					b_workflow_document
				WHERE
					STATUS_ID = 1
					and DATE_MODIFY < ' . $helper->addDaysToDateTime(-$DAYS_AFTER_PUBLISHING) . '
				';
			$z = $connection->query($strSql);
			while ($zr = $z->fetch())
			{
				self::Delete($zr['ID']);
			}
		}
	}

	public static function GetFileList($DOCUMENT_ID)
	{
		global $DB;

		$DOCUMENT_ID = intval($DOCUMENT_ID);
		$strSql = '
			SELECT
				F.*, D.SITE_ID,
				' . $DB->DateToCharFunction('F.TIMESTAMP_X') . " TIMESTAMP_X,
				concat_ws(' ', " . $DB->Concat("'('", 'U.LOGIN', "')'") . ", nullif(U.NAME, ''), nullif(U.LAST_NAME, '')) USER_NAME
			FROM
				b_workflow_document D
				INNER JOIN b_workflow_file F ON (F.DOCUMENT_ID = D.ID)
				LEFT JOIN b_user U ON (U.ID = F.MODIFIED_BY)
			WHERE
				D.ID = " . $DOCUMENT_ID . '
			ORDER BY
				F.TIMESTAMP_X desc
		';
		$z = $DB->Query($strSql);

		return $z;
	}
}
