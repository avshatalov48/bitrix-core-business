<?php
IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Mail;

class CPosting
{
	public $LAST_ERROR = '';
	//email count for one hit
	public static $current_emails_per_hit = 0;

	//get by ID
	public static function GetByID($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = '
			SELECT
				P.*
				,' . $DB->DateToCharFunction('P.TIMESTAMP_X', 'FULL') . ' AS TIMESTAMP_X
				,' . $DB->DateToCharFunction('P.DATE_SENT', 'FULL') . ' AS DATE_SENT
				,' . $DB->DateToCharFunction('P.AUTO_SEND_TIME', 'FULL') . ' AS AUTO_SEND_TIME
			FROM b_posting P
			WHERE P.ID=' . $ID . '
		';

		return $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
	}

	//list of categories linked with message
	public static function GetRubricList($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = '
			SELECT
				R.ID
				,R.NAME
				,R.SORT
				,R.LID
				,R.ACTIVE
			FROM
				b_list_rubric R
				,b_posting_rubric PR
			WHERE
				R.ID=PR.LIST_RUBRIC_ID
				AND PR.POSTING_ID=' . $ID . '
			ORDER BY
				R.LID, R.SORT, R.NAME
		';

		return $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
	}

	//list of user group linked with message
	public static function GetGroupList($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = '
			SELECT
				G.ID
				,G.NAME
			FROM
				b_group G
				,b_posting_group PG
			WHERE
				G.ID=PG.GROUP_ID
				AND PG.POSTING_ID=' . $ID . '
			ORDER BY
				G.C_SORT, G.ID
		';

		return $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
	}

	// delete by ID
	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		CPosting::DeleteFile($ID);

		$res = $DB->Query("DELETE FROM b_posting_rubric WHERE POSTING_ID='" . $ID . "'", false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		if ($res)
		{
			$res = $DB->Query("DELETE FROM b_posting_group WHERE POSTING_ID='" . $ID . "' ", false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		}
		if ($res)
		{
			$res = $DB->Query("DELETE FROM b_posting_email WHERE POSTING_ID='" . $ID . "' ", false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		}
		if ($res)
		{
			$res = $DB->Query("DELETE FROM b_posting WHERE ID='" . $ID . "' ", false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		}

		return $res;
	}

	public static function OnGroupDelete($group_id)
	{
		global $DB;
		$group_id = intval($group_id);

		return $DB->Query('DELETE FROM b_posting_group WHERE GROUP_ID=' . $group_id, true);
	}

	public static function DeleteFile($ID, $file_id=false)
	{
		global $DB;

		$rsFile = CPosting::GetFileList($ID, $file_id);
		while ($arFile = $rsFile->Fetch())
		{
			$DB->Query('DELETE FROM b_posting_file where POSTING_ID=' . intval($ID) . ' AND FILE_ID=' . intval($arFile['ID']), false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
			CFile::Delete(intval($arFile['ID']));
		}
	}

	public static function SplitFileName($file_name)
	{
		$found = [];
		// exapmle(2).txt
		if (preg_match('/^(.*)\\((\\d+?)\\)(\\..+?)$/', $file_name, $found))
		{
			$fname = $found[1];
			$fext = $found[3];
			$index = $found[2];
		}
		// example(2)
		elseif (preg_match('/^(.*)\\((\\d+?)\\)$/', $file_name, $found))
		{
			$fname = $found[1];
			$fext = '';
			$index = $found[2];
		}
		// example.txt
		elseif (preg_match('/^(.*)(\\..+?)$/', $file_name, $found))
		{
			$fname = $found[1];
			$fext = $found[2];
			$index = 0;
		}
		// example
		else
		{
			$fname = $file_name;
			$fext = '';
			$index = 0;
		}
		return [$fname, $fext, $index];
	}

	public function SaveFile($ID, $file)
	{
		global $DB, $APPLICATION;
		$ID = intval($ID);
		$filesSize = 0;

		$arFileName = CPosting::SplitFileName($file['name']);
		//Check if file with this name already exists
		$arSameNames = [];
		$rsFile = CPosting::GetFileList($ID);
		while ($arFile = $rsFile->Fetch())
		{
			$filesSize += $arFile['FILE_SIZE'];
			$arSavedName = CPosting::SplitFileName($arFile['ORIGINAL_NAME']);
			if ($arFileName[0] == $arSavedName[0] && $arFileName[1] == $arSavedName[1])
			{
				$arSameNames[$arSavedName[2]] = true;
			}
		}

		$max_files_size = COption::GetOptionString('subscribe', 'max_files_size') * 1024 * 1024;
		if ($max_files_size > 0)
		{
			$filesSize += $file['size'];
			if ($filesSize > $max_files_size)
			{
				$this->LAST_ERROR = GetMessage('class_post_err_files_size', [
					'#MAX_FILES_SIZE#' => CFile::FormatSize($max_files_size),
				]);
				$APPLICATION->ThrowException($this->LAST_ERROR);
				return false;
			}
		}

		while (array_key_exists($arFileName[2], $arSameNames))
		{
			$arFileName[2]++;
		}

		if ($arFileName[2] > 0)
		{
			$file['name'] = $arFileName[0] . '(' . ($arFileName[2]) . ')' . $arFileName[1];
		}

		//save file
		$file['MODULE_ID'] = 'subscribe';
		$fid = intval(CFile::SaveFile($file, 'subscribe', true, true));
		if (($fid > 0) && $DB->Query('INSERT INTO b_posting_file (POSTING_ID, FILE_ID) VALUES (' . $ID . ' ,' . $fid . ')', false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__))
		{
			return true;
		}
		else
		{
			$this->LAST_ERROR = GetMessage('class_post_err_att');
			$APPLICATION->ThrowException($this->LAST_ERROR);
			return false;
		}
	}

	public static function GetFileList($ID, $file_id=false)
	{
		global $DB;
		$ID = intval($ID);
		$file_id = intval($file_id);

		$strSql = '
			SELECT
				F.ID
				,F.FILE_SIZE
				,F.ORIGINAL_NAME
				,F.SUBDIR
				,F.FILE_NAME
				,F.CONTENT_TYPE
				,F.HANDLER_ID
			FROM
				b_file F
				,b_posting_file PF
			WHERE
				F.ID=PF.FILE_ID
				AND PF.POSTING_ID=' . $ID . '
			' . ($file_id > 0 ? 'AND PF.FILE_ID = ' . $file_id : '') . '
			ORDER BY F.ID
		';

		return $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
	}

	//check fields before writing
	public function CheckFields($arFields, $ID)
	{
		/** @var CDatabase $DB */
		global $DB;
		/** @var  CMain $APPLICATION */
		global $APPLICATION;

		$this->LAST_ERROR = '';
		$aMsg = [];

		if (array_key_exists('FROM_FIELD', $arFields))
		{
			if (mb_strlen($arFields['FROM_FIELD']) < 3 || !check_email($arFields['FROM_FIELD']))
			{
				$aMsg[] = ['id' => 'FROM_FIELD', 'text' => GetMessage('class_post_err_email')];
			}
		}

		if (!array_key_exists('DIRECT_SEND', $arFields) || $arFields['DIRECT_SEND'] == 'N')
		{
			if (array_key_exists('TO_FIELD', $arFields) && $arFields['TO_FIELD'] == '')
			{
				$aMsg[] = ['id' => 'TO_FIELD', 'text' => GetMessage('class_post_err_to')];
			}
		}

		if (array_key_exists('SUBJECT', $arFields))
		{
			if ($arFields['SUBJECT'] == '')
			{
				$aMsg[] = ['id' => 'SUBJECT', 'text' => GetMessage('class_post_err_subj')];
			}
		}

		if (array_key_exists('BODY', $arFields))
		{
			if ($arFields['BODY'] == '')
			{
				$aMsg[] = ['id' => 'BODY', 'text' => GetMessage('class_post_err_text')];
			}
		}

		if (array_key_exists('AUTO_SEND_TIME', $arFields) && $arFields['AUTO_SEND_TIME'] !== false)
		{
			if ($DB->IsDate($arFields['AUTO_SEND_TIME'], false, false, 'FULL') !== true)
			{
				$aMsg[] = ['id' => 'AUTO_SEND_TIME', 'text' => GetMessage('class_post_err_auto_time')];
			}
		}

		if (array_key_exists('CHARSET', $arFields))
		{
			$sCharset = COption::GetOptionString('subscribe', 'posting_charset');
			$aCharset = explode(',', ToLower($sCharset));
			if (!in_array(ToLower($arFields['CHARSET']), $aCharset, true))
			{
				$aMsg[] = ['id' => 'CHARSET', 'text' => GetMessage('class_post_err_charset')];
			}
		}

		if (!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			$this->LAST_ERROR = $e->GetString();
			return false;
		}

		return true;
	}

	//relation with categories
	public function UpdateRubrics($ID, $aRubric)
	{
		global $DB;
		$ID = intval($ID);

		$DB->Query('DELETE FROM b_posting_rubric WHERE POSTING_ID=' . $ID, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		$arID = [];
		if (is_array($aRubric))
		{
			foreach ($aRubric as $i)
			{
				$arID[] = intval($i);
			}
		}
		if (count($arID) > 0)
		{
			$DB->Query('
				INSERT INTO b_posting_rubric (POSTING_ID, LIST_RUBRIC_ID)
				SELECT ' . $ID . ', ID
				FROM b_list_rubric
				WHERE ID IN (' . implode(', ',$arID) . ')
				', false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__
			);
		}
	}

	//relation with user groups
	public function UpdateGroups($ID, $aGroup)
	{
		global $DB;
		$ID = intval($ID);

		$DB->Query("DELETE FROM b_posting_group WHERE POSTING_ID='" . $ID . "'", false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		$arID = [];
		if (is_array($aGroup))
		{
			foreach ($aGroup as $i)
			{
				$arID[] = intval($i);
			}
		}
		if (count($arID) > 0)
		{
			$DB->Query('
				INSERT INTO b_posting_group (POSTING_ID, GROUP_ID)
				SELECT ' . $ID . ', ID
				FROM b_group
				WHERE ID IN (' . implode(', ',$arID) . ')
				', false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__
			);
		}
	}

	//Addition
	public function Add($arFields)
	{
		global $DB;

		if (!$this->CheckFields($arFields, 0))
		{
			return false;
		}

		if (!array_key_exists('MSG_CHARSET', $arFields))
		{
			$arFields['MSG_CHARSET'] = LANG_CHARSET;
		}
		$arFields['VERSION'] = '2';
		$arFields['~TIMESTAMP_X'] = $DB->CurrentTimeFunction();

		$ID = $DB->Add('b_posting', $arFields, ['BCC_FIELD','BODY']);
		if ($ID > 0)
		{
			$this->UpdateRubrics($ID, $arFields['RUB_ID']);
			$this->UpdateGroups($ID, $arFields['GROUP_ID']);
		}
		return $ID;
	}

	//Update
	public function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		if (!$this->CheckFields($arFields, $ID))
		{
			return false;
		}

		$arFields['~TIMESTAMP_X'] = $DB->CurrentTimeFunction();

		$strUpdate = $DB->PrepareUpdate('b_posting', $arFields);
		if ($strUpdate != '')
		{
			$strSql = 'UPDATE b_posting SET ' . $strUpdate . ' WHERE ID=' . $ID;
			$arBinds = [
				'BCC_FIELD' => $arFields['BCC_FIELD'],
				//"SENT_BCC" => $arFields["SENT_BCC"],
				'BODY' => $arFields['BODY'],
				//"ERROR_EMAIL" => $arFields["ERROR_EMAIL"],
				//"BCC_TO_SEND" => $arFields["BCC_TO_SEND"],
			];
			if (!$DB->QueryBind($strSql, $arBinds))
			{
				return false;
			}
		}
		if (is_set($arFields, 'RUB_ID'))
		{
			$this->UpdateRubrics($ID, $arFields['RUB_ID']);
		}
		if (is_set($arFields, 'GROUP_ID'))
		{
			$this->UpdateGroups($ID, $arFields['GROUP_ID']);
		}

		return true;
	}

	public function GetEmails($post_arr)
	{
		$aEmail = [];

		//send to categories
		$aPostRub = [];
		$post_rub = static::GetRubricList($post_arr['ID']);
		while ($post_rub_arr = $post_rub->Fetch())
		{
			$aPostRub[] = $post_rub_arr['ID'];
		}

		$subscr = CSubscription::GetList(
			['ID' => 'ASC'],
			[
				'RUBRIC_MULTI' => $aPostRub,
				'CONFIRMED' => 'Y',
				'ACTIVE' => 'Y',
				'FORMAT' => $post_arr['SUBSCR_FORMAT'],
				'EMAIL' => $post_arr['EMAIL_FILTER'],
			]
		);
		while (($subscr_arr = $subscr->Fetch()))
		{
			$aEmail[] = $subscr_arr['EMAIL'];
		}

		//send to user groups
		$aPostGrp = [];
		$post_grp = static::GetGroupList($post_arr['ID']);
		while ($post_grp_arr = $post_grp->Fetch())
		{
			$aPostGrp[] = $post_grp_arr['ID'];
		}

		if (count($aPostGrp) > 0)
		{
			$user = CUser::GetList(
				'id', 'asc',
				['GROUP_MULTI' => $aPostGrp, 'ACTIVE' => 'Y', 'EMAIL' => $post_arr['EMAIL_FILTER']]
			);
			while (($user_arr = $user->Fetch()))
			{
				$aEmail[] = $user_arr['EMAIL'];
			}
		}

		//from additional emails
		$BCC = $post_arr['BCC_FIELD'];
		if ($post_arr['DIRECT_SEND'] == 'Y')
		{
			$BCC .= ($BCC ? ',' : '') . $post_arr['TO_FIELD'];
		}
		if ($BCC <> '')
		{
			$BCC = str_replace("\r\n", "\n", $BCC);
			$BCC = str_replace("\n", ',', $BCC);
			$aBcc = explode(',', $BCC);
			foreach ($aBcc as $bccEmail)
			{
				$bccEmail = trim($bccEmail);
				if ($bccEmail !== '')
				{
					$aEmail[] = $bccEmail;
				}
			}
		}

		$aEmail = array_unique($aEmail);

		return $aEmail;
	}

	public static function AutoSend($ID=false, $limit=false, $site_id=false)
	{
		if ($ID === false)
		{
			//Here is cron job entry
			$cPosting = new CPosting;
			$rsPosts = $cPosting->GetList(
				['AUTO_SEND_TIME' => 'ASC', 'ID' => 'ASC'],
				['STATUS_ID' => 'P', 'AUTO_SEND_TIME_2' => ConvertTimeStamp(false, 'FULL')]
			);
			while ($arPosts = $rsPosts->Fetch())
			{
				if ($limit === true)
				{
					$maxcount = COption::GetOptionInt('subscribe', 'subscribe_max_emails_per_hit') - self::$current_emails_per_hit;
					if ($maxcount <= 0)
					{
						break;
					}
				}
				else
				{
					$maxcount = 0;
				}
				$cPosting->SendMessage($arPosts['ID'], 0, $maxcount);
			}
		}
		else
		{
			if ($site_id && $site_id != SITE_ID)
			{
				return 'CPosting::AutoSend(' . $ID . ($limit ? ',true' : ',false') . ',"' . $site_id . '");';
			}

			//Here is agent entry
			if ($limit === true)
			{
				$maxcount = COption::GetOptionInt('subscribe', 'subscribe_max_emails_per_hit') - self::$current_emails_per_hit;
				if ($maxcount <= 0)
				{
					return 'CPosting::AutoSend(' . $ID . ',true' . ($site_id ? ',"' . $site_id . '"' : '') . ');';
				}
			}
			else
			{
				$maxcount = 0;
			}

			$cPosting = new CPosting;
			$res = $cPosting->SendMessage($ID, COption::GetOptionString('subscribe', 'posting_interval'), $maxcount, true);
			if ($res == 'CONTINUE')
			{
				return 'CPosting::AutoSend(' . $ID . ($limit ? ',true' : ',false') . ($site_id ? ',"' . $site_id . '"' : '') . ');';
			}
		}
		return '';
	}

	//Send message
	public function SendMessage($ID, $timeout=0, $maxcount=0, $check_charset=false)
	{
		global $DB, $APPLICATION;

		$eol = \Bitrix\Main\Mail\Mail::getMailEol();
		$ID = intval($ID);
		$timeout = intval($timeout);
		$start_time = microtime(1);

		@set_time_limit(0);
		$this->LAST_ERROR = '';

		$post = static::GetByID($ID);
		if (!($post_arr = $post->Fetch()))
		{
			$this->LAST_ERROR .= GetMessage('class_post_err_notfound');
			return false;
		}

		if ($post_arr['STATUS'] != 'P')
		{
			$this->LAST_ERROR .= GetMessage('class_post_err_status') . '<br>';
			return false;
		}

		if (
			$check_charset
			&& ($post_arr['MSG_CHARSET'] <> '')
			&& (mb_strtoupper($post_arr['MSG_CHARSET']) !== mb_strtoupper(LANG_CHARSET))
		)
		{
			return 'CONTINUE';
		}

		if (CPosting::Lock($ID) === false)
		{
			return 'CONTINUE';
		}

		if ($post_arr['VERSION'] <> '2')
		{
			if (is_string($post_arr['BCC_TO_SEND']) && $post_arr['BCC_TO_SEND'] <> '')
			{
				$a = explode(',', $post_arr['BCC_TO_SEND']);
				foreach ($a as $e)
				{
					$e = trim($e, " \t\n\r");
					if ($e !== '')
					{
						$DB->Query('INSERT INTO b_posting_email (POSTING_ID, STATUS, EMAIL) VALUES (' . $ID . ", 'Y', '" . $DB->ForSQL($e) . "')", true);
					}
				}
			}

			if (is_string($post_arr['ERROR_EMAIL']) && $post_arr['ERROR_EMAIL'] <> '')
			{
				$a = explode(',', $post_arr['ERROR_EMAIL']);
				foreach ($a as $e)
				{
					$e = trim($e, " \t\n\r");
					if ($e !== '')
					{
						$DB->Query('INSERT INTO b_posting_email (POSTING_ID, STATUS, EMAIL) VALUES (' . $ID . ", 'E', '" . $DB->ForSQL($e) . "')", true);
					}
				}
			}

			if (is_string($post_arr['SENT_BCC']) && $post_arr['SENT_BCC'] <> '')
			{
				$a = explode(',', $post_arr['SENT_BCC']);
				foreach ($a as $e)
				{
					$e = trim($e, " \t\n\r");
					if ($e !== '')
					{
						$DB->Query('INSERT INTO b_posting_email (POSTING_ID, STATUS, EMAIL) VALUES (' . $ID . ", 'N', '" . $DB->ForSQL($e) . "')", true);
					}
				}
			}

			$DB->Query("UPDATE b_posting SET VERSION='2', BCC_TO_SEND=null, ERROR_EMAIL=null, SENT_BCC=null WHERE ID=" . $ID);
		}

		$tools = new CMailTools;
		//MIME with attachments
		if ($post_arr['BODY_TYPE'] == 'html' && COption::GetOptionString('subscribe', 'attach_images') == 'Y')
		{
			$post_arr['BODY'] = $tools->ReplaceImages($post_arr['BODY']);
		}

		if ($post_arr['CHARSET'] <> '')
		{
			$from_charset = $post_arr['MSG_CHARSET'] ?: SITE_CHARSET;
			$post_arr['BODY'] = $APPLICATION->ConvertCharset($post_arr['BODY'], $from_charset, $post_arr['CHARSET']);
			$post_arr['SUBJECT'] = $APPLICATION->ConvertCharset($post_arr['SUBJECT'], $from_charset, $post_arr['CHARSET']);
			$post_arr['FROM_FIELD'] = $APPLICATION->ConvertCharset($post_arr['FROM_FIELD'], $from_charset, $post_arr['CHARSET']);
		}

		//Preparing message header, text, subject
		$sBody = str_replace("\r\n", "\n", $post_arr['BODY']);
		$sBody = implode(
			"\n",
			array_filter(
				preg_split('/(.{512}[^ ]* )/', $sBody . ' ', -1, PREG_SPLIT_DELIM_CAPTURE)
			)
		); //Some MTA has 4K limit for fgets function. So we have to split the message body.
		if (COption::GetOptionString('main', 'CONVERT_UNIX_NEWLINE_2_WINDOWS', 'N') == 'Y')
		{
			$sBody = str_replace("\n", "\r\n", $sBody);
		}

		if (COption::GetOptionString('subscribe', 'allow_8bit_chars') <> 'Y')
		{
			$sSubject = CMailTools::EncodeSubject($post_arr['SUBJECT'], $post_arr['CHARSET']);
			$sFrom = CMailTools::EncodeHeaderFrom($post_arr['FROM_FIELD'], $post_arr['CHARSET']);
		}
		else
		{
			$sSubject = $post_arr['SUBJECT'];
			$sFrom = $post_arr['FROM_FIELD'];
		}

		if ($post_arr['BODY_TYPE'] == 'html')
		{
			//URN2URI
			$tmpTools = new CMailTools;
			$sBody = $tmpTools->ReplaceHrefs($sBody);
		}

		$bHasAttachments = false;
		$sHeader = '';
		$sBoundary = '';

		if (count($tools->aMatches) > 0)
		{
			$bHasAttachments = true;

			$sBoundary = '----------' . uniqid('');
			$sHeader =
				'From: ' . $sFrom . $eol
				. 'X-Bitrix-Posting: ' . $post_arr['ID'] . $eol
				. 'MIME-Version: 1.0' . $eol
				. 'Content-Type: multipart/mixed; boundary="' . $sBoundary . '"' . $eol
				. 'Content-Transfer-Encoding: 8bit';

			$sBody =
				'--' . $sBoundary . $eol
				. 'Content-Type: ' . ($post_arr['BODY_TYPE'] == 'html' ? 'text/html' : 'text/plain') . ($post_arr['CHARSET'] <> '' ? '; charset=' . $post_arr['CHARSET'] : '') . $eol
				. 'Content-Transfer-Encoding: 8bit' . $eol . $eol
				. $sBody . $eol;

			foreach ($tools->aMatches as $attachment)
			{
				if ($post_arr['CHARSET'] <> '')
				{
					$from_charset = $post_arr['MSG_CHARSET'] ?: SITE_CHARSET;
					$attachment['DEST'] = $APPLICATION->ConvertCharset($attachment['DEST'], $from_charset, $post_arr['CHARSET']);
				}

				if (COption::GetOptionString('subscribe', 'allow_8bit_chars') <> 'Y')
				{
					$name = CMailTools::EncodeSubject($attachment['DEST'], $post_arr['CHARSET']);
				}
				else
				{
					$name = $attachment['DEST'];
				}

				$sBody .=
					$eol . '--' . $sBoundary . $eol
					. 'Content-Type: ' . $attachment['CONTENT_TYPE'] . '; name="' . $name . '"' . $eol
					. 'Content-Transfer-Encoding: base64' . $eol
					. 'Content-ID: <' . $attachment['ID'] . '>' . $eol . $eol
					. chunk_split(
						base64_encode(
							file_get_contents($attachment['PATH'])
						), 72, $eol
					);
			}
		}

		$arFiles = [];
		$maxFileSize = intval(COption::GetOptionInt('subscribe', 'max_file_size'));
		$rsFile = CPosting::GetFileList($ID);
		while ($arFile = $rsFile->Fetch())
		{
			if (
				$maxFileSize == 0
				|| $arFile['FILE_SIZE'] <= $maxFileSize
			)
			{
				$arFiles[] = $arFile;
			}
		}

		if (!empty($arFiles))
		{
			if (!$bHasAttachments)
			{
				$bHasAttachments = true;
				$sBoundary = '----------' . uniqid('');
				$sHeader =
					'From: ' . $sFrom . $eol
					. 'X-Bitrix-Posting: ' . $post_arr['ID'] . $eol
					. 'MIME-Version: 1.0' . $eol
					. 'Content-Type: multipart/mixed; boundary="' . $sBoundary . '"' . $eol
					. 'Content-Transfer-Encoding: 8bit';

				$sBody =
					'--' . $sBoundary . $eol
					. 'Content-Type: ' . ($post_arr['BODY_TYPE'] == 'html' ? 'text/html' : 'text/plain') . ($post_arr['CHARSET'] <> '' ? '; charset=' . $post_arr['CHARSET'] : '') . $eol
					. 'Content-Transfer-Encoding: 8bit' . $eol . $eol
					. $sBody . $eol;
			}

			foreach ($arFiles as $arFile)
			{
				if ($post_arr['CHARSET'] <> '')
				{
					$from_charset = $post_arr['MSG_CHARSET'] ?: SITE_CHARSET;
					$file_name = $APPLICATION->ConvertCharset($arFile['ORIGINAL_NAME'], $from_charset, $post_arr['CHARSET']);
				}
				else
				{
					$file_name = $arFile['ORIGINAL_NAME'];
				}

				$sBody .=
					$eol . '--' . $sBoundary . $eol
					. 'Content-Type: ' . $arFile['CONTENT_TYPE'] . '; name="' . $file_name . '"' . $eol
					. 'Content-Transfer-Encoding: base64' . $eol
					. 'Content-Disposition: attachment; filename="' . CMailTools::EncodeHeaderFrom($file_name, $post_arr['CHARSET']) . '"' . $eol . $eol;

				$arTempFile = CFile::MakeFileArray($arFile['ID']);
				$sBody .= chunk_split(
					base64_encode(
						file_get_contents($arTempFile['tmp_name'])
					),
					72,
					$eol
				);
			}
		}

		if ($bHasAttachments)
		{
			$sBody .= $eol . '--' . $sBoundary . '--' . $eol;
		}
		else
		{
			//plain message without MIME
			$sHeader =
				'From: ' . $sFrom . $eol
				. 'X-Bitrix-Posting: ' . $post_arr['ID'] . $eol
				. 'MIME-Version: 1.0' . $eol
				. 'Content-Type: ' . ($post_arr['BODY_TYPE'] == 'html' ? 'text/html' : 'text/plain') . ($post_arr['CHARSET'] <> '' ? '; charset=' . $post_arr['CHARSET'] : '') . $eol
				. 'Content-Transfer-Encoding: 8bit';
		}

		$mail_additional_parameters = trim(COption::GetOptionString('subscribe', 'mail_additional_parameters'));

		$context = new Mail\Context();
		$context->setCategory(Mail\Context::CAT_EXTERNAL);
		$context->setPriority(Mail\Context::PRIORITY_LOW);

		if ($post_arr['DIRECT_SEND'] == 'Y')
		{
			//personal delivery
			$arEvents = GetModuleEvents('subscribe', 'BeforePostingSendMail', true);

			$rsEmails = $DB->Query($DB->TopSql('
				SELECT *
				FROM b_posting_email
				WHERE POSTING_ID = ' . $ID . " AND STATUS='Y'
			", $maxcount));

			while ($arEmail = $rsEmails->Fetch())
			{
				//Event part
				$arFields = [
					'POSTING_ID' => $ID,
					'EMAIL' => $arEmail['EMAIL'],
					'SUBJECT' => $sSubject,
					'BODY' => $sBody,
					'HEADER' => $sHeader,
					'EMAIL_EX' => $arEmail,
				];
				foreach ($arEvents as $arEvent)
				{
					$arFields = ExecuteModuleEventEx($arEvent, [$arFields]);
				}
				//Sending

				if (is_array($arFields))
				{
					$to = CMailTools::EncodeHeaderFrom($arFields['EMAIL'], $post_arr['CHARSET']);
					$result = bxmail($to, $arFields['SUBJECT'], $arFields['BODY'], $arFields['HEADER'], $mail_additional_parameters, $context);
				}
				else
				{
					$result = $arFields !== false;
				}

				//Result check and iteration
				if ($result)
				{
					$DB->Query("UPDATE b_posting_email SET STATUS='N' WHERE ID = " . $arEmail['ID']);
				}
				else
				{
					$DB->Query("UPDATE b_posting_email SET STATUS='E' WHERE ID = " . $arEmail['ID']);
				}

				if ($timeout > 0 && microtime(1) - $start_time >= $timeout)
				{
					break;
				}

				self::$current_emails_per_hit++;
			}
		}
		else
		{
			//BCC delivery
			$rsEmails = $DB->Query($DB->TopSql('
				SELECT *
				FROM b_posting_email
				WHERE POSTING_ID = ' . $ID . " AND STATUS='Y'
			", COption::GetOptionString('subscribe', 'max_bcc_count')));

			$aStep = [];
			while ($arEmail = $rsEmails->Fetch())
			{
				$aStep[$arEmail['ID']] = $arEmail['EMAIL'];
			}

			if (count($aStep) > 0)
			{
				$BCC = implode(',', $aStep);
				$sHeaderStep = $sHeader . $eol . 'Bcc: ' . $BCC;
				$result = bxmail($post_arr['TO_FIELD'], $sSubject, $sBody, $sHeaderStep, $mail_additional_parameters, $context);
				if ($result)
				{
					$DB->Query("UPDATE b_posting_email SET STATUS='N' WHERE ID in (" . implode(', ', array_keys($aStep)) . ')');
				}
				else
				{
					$DB->Query("UPDATE b_posting_email SET STATUS='E' WHERE ID in (" . implode(', ', array_keys($aStep)) . ')');
					$this->LAST_ERROR .= GetMessage('class_post_err_mail') . '<br>';
				}
			}
		}

		//set status and delivered and error emails
		$arStatuses = static::GetEmailStatuses($ID);
		if (!array_key_exists('Y', $arStatuses))
		{
			$STATUS = array_key_exists('E', $arStatuses) ? 'E' : 'S';
			$DATE = $DB->GetNowFunction();
		}
		else
		{
			$STATUS = 'P';
			$DATE = 'null';
		}

		CPosting::UnLock($ID);

		$DB->Query("UPDATE b_posting SET STATUS='" . $STATUS . "', DATE_SENT=" . $DATE . ' WHERE ID=' . $ID);

		return ($STATUS === 'P' ? 'CONTINUE' : true);
	}

	public static function GetEmailStatuses($ID)
	{
		global $DB;
		$arStatuses = [];
		$rs = $DB->Query('
			SELECT STATUS, COUNT(*) CNT
			FROM b_posting_email
			WHERE POSTING_ID = ' . intval($ID) . '
			GROUP BY STATUS
		');
		while ($ar = $rs->Fetch())
		{
			$arStatuses[$ar['STATUS']] = $ar['CNT'];
		}
		return $arStatuses;
	}

	public static function GetEmailsByStatus($ID, $STATUS)
	{
		global $DB;

		return $DB->Query('
			SELECT *
			FROM b_posting_email
			WHERE POSTING_ID = ' . intval($ID) . "
			AND STATUS = '" . $DB->ForSQL($STATUS) . "'
			ORDER BY EMAIL
		");
	}

	public function ChangeStatus($ID, $status)
	{
		global $DB;

		$ID = intval($ID);
		$this->LAST_ERROR = '';

		$strSql = 'SELECT STATUS, VERSION FROM b_posting WHERE ID=' . $ID;
		$db_result = $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		$arResult = $db_result->Fetch();
		if (!$arResult)
		{
			$this->LAST_ERROR = GetMessage('class_post_err_notfound') . '<br>';
			return false;
		}

		if ($arResult['STATUS'] == $status)
		{
			return true;
		}

		switch ($arResult['STATUS'] . $status)
		{
			case 'DP':
				//BCC_TO_SEND fill
				$post = static::GetByID($ID);
				if (!($post_arr = $post->Fetch()))
				{
					$this->LAST_ERROR .= GetMessage('class_post_err_notfound') . '<br>';
					return false;
				}

				$DB->Query('DELETE from b_posting_email WHERE POSTING_ID = ' . $ID);

				$DB->Query("
					INSERT INTO b_posting_email (POSTING_ID, STATUS, EMAIL, SUBSCRIPTION_ID, USER_ID)
					SELECT DISTINCT
						PR.POSTING_ID, 'Y', S.EMAIL, min(S.ID), min(S.USER_ID)
					FROM
						b_posting_rubric PR
						INNER JOIN b_subscription_rubric SR ON SR.LIST_RUBRIC_ID = PR.LIST_RUBRIC_ID
						INNER JOIN b_subscription S ON S.ID = SR.SUBSCRIPTION_ID
						LEFT JOIN b_user U ON U.ID = S.USER_ID
					WHERE
						PR.POSTING_ID = " . $ID . "
						AND S.CONFIRMED = 'Y'
						AND S.ACTIVE = 'Y'
						AND (U.ID IS NULL OR U.ACTIVE = 'Y')
						" . ($post_arr['SUBSCR_FORMAT'] == '' || $post_arr['SUBSCR_FORMAT'] === 'NOT_REF' ? '' : "AND S.FORMAT = '" . ($post_arr['SUBSCR_FORMAT'] == 'text' ? 'text' : 'html') . "'") . "
						" . ($post_arr['EMAIL_FILTER'] == '' || $post_arr['EMAIL_FILTER'] === 'NOT_REF' ? '' : 'AND ' . GetFilterQuery('S.EMAIL', $post_arr['EMAIL_FILTER'], 'Y', ['@', '.', '_'])) . "
					GROUP BY
						PR.POSTING_ID, S.EMAIL
				");

				//send to user groups
				$res = $DB->Query('SELECT * FROM b_posting_group WHERE POSTING_ID = ' . $ID . ' AND GROUP_ID = 2');
				if ($res->Fetch())
				{
					$DB->Query('
						INSERT INTO b_posting_email (POSTING_ID, STATUS, EMAIL, SUBSCRIPTION_ID, USER_ID)
						SELECT
							' . $ID . ", 'Y', U.EMAIL, NULL, MIN(U.ID)
						FROM
							b_user U
						WHERE
							U.ACTIVE = 'Y'
							and U.EMAIL IS NOT NULL
							" . ($post_arr['EMAIL_FILTER'] == '' || $post_arr['EMAIL_FILTER'] === 'NOT_REF' ? '' : 'AND ' . GetFilterQuery('U.EMAIL', $post_arr['EMAIL_FILTER'], 'Y', ['@', '.', '_'])) . '
							and U.EMAIL not in (SELECT EMAIL FROM b_posting_email WHERE POSTING_ID = ' . $ID . ')
						GROUP BY U.EMAIL
					');
				}
				else
				{
					$DB->Query("
						INSERT INTO b_posting_email (POSTING_ID, STATUS, EMAIL, SUBSCRIPTION_ID, USER_ID)
						SELECT
							PG.POSTING_ID, 'Y', U.EMAIL, NULL, MIN(U.ID)
						FROM
							b_posting_group PG
							INNER JOIN b_user_group UG ON UG.GROUP_ID = PG.GROUP_ID
							INNER JOIN b_user U ON U.ID = UG.USER_ID
						WHERE
							PG.POSTING_ID = " . $ID . '
							and (UG.DATE_ACTIVE_FROM is null or UG.DATE_ACTIVE_FROM <= ' . $DB->CurrentTimeFunction() . ')
							and (UG.DATE_ACTIVE_TO is null or UG.DATE_ACTIVE_TO >= ' . $DB->CurrentTimeFunction() . ")
							and U.ACTIVE = 'Y'
							and U.EMAIL IS NOT NULL
							" . ($post_arr['EMAIL_FILTER'] == '' || $post_arr['EMAIL_FILTER'] === 'NOT_REF' ? '' : 'AND ' . GetFilterQuery('U.EMAIL', $post_arr['EMAIL_FILTER'], 'Y', ['@', '.', '_'])) . '
							and U.EMAIL not in (SELECT EMAIL FROM b_posting_email WHERE POSTING_ID = ' . $ID . ')
						GROUP BY PG.POSTING_ID, U.EMAIL
					');
				}

				//from additional emails
				$BCC = $post_arr['BCC_FIELD'];
				if ($post_arr['DIRECT_SEND'] == 'Y')
				{
					$BCC .= ($BCC ? ',' : '') . $post_arr['TO_FIELD'];
				}
				$BCC = str_replace("\r\n", "\n", $BCC);
				$BCC = str_replace("\n", ',', $BCC);
				$aBcc = explode(',', $BCC);
				foreach ($aBcc as $email)
				{
					$email = trim($email, " \t\n\r");
					if ($email !== '')
					{
						$DB->Query("
							INSERT INTO b_posting_email (POSTING_ID, STATUS, EMAIL, SUBSCRIPTION_ID, USER_ID)
							SELECT
								P.ID, 'Y', '" . ($DB->ForSQL($email)) . "', NULL, NULL
							FROM
								b_posting P
							WHERE
								P.ID = " . $ID . "
								and '" . ($DB->ForSQL($email)) . "' not in (SELECT EMAIL FROM b_posting_email WHERE POSTING_ID = " . $ID . ')
						');
					}
				}

				$res = $DB->Query('SELECT count(*) CNT from b_posting_email WHERE POSTING_ID = ' . $ID);
				$ar = $res->Fetch();

				if ($ar['CNT'] > 0)
				{
					$DB->Query("UPDATE b_posting SET STATUS='" . $status . "', VERSION='2', BCC_TO_SEND=null, ERROR_EMAIL=null, SENT_BCC=null WHERE ID=" . $ID);
				}
				else
				{
					$this->LAST_ERROR .= GetMessage('class_post_err_status4');
					return false;
				}
				break;
			case 'PW':
			case 'WP':
			case 'PE':
			case 'PS':
				$DB->Query("UPDATE b_posting SET STATUS='" . $status . "' WHERE ID=" . $ID);
				break;
			case 'EW'://This is the way to resend error e-mails
			case 'EP':
				if ($arResult['VERSION'] == '2')
				{
					$DB->Query("UPDATE b_posting_email SET STATUS='Y' WHERE POSTING_ID=" . $ID . " AND STATUS='E'");
					$DB->Query("UPDATE b_posting SET STATUS='" . $status . "' WHERE ID=" . $ID);
				}
				else
				{
					//Send it in old fashion way
					$DB->Query("UPDATE b_posting SET STATUS='" . $status . "', BCC_TO_SEND=ERROR_EMAIL, ERROR_EMAIL=null WHERE ID=" . $ID);
				}
				break;
			case 'ED':
			case 'SD':
			case 'WD':
				$DB->Query("UPDATE b_posting SET STATUS='" . $status . "', VERSION='2', SENT_BCC=null, ERROR_EMAIL=null, BCC_TO_SEND=null, DATE_SENT=null WHERE ID=" . $ID);
				break;
			default:
				$this->LAST_ERROR = GetMessage('class_post_err_status2');
				return false;
		}

		return true;
	}

	public function GetList($aSort=[], $arFilter=[], $arSelect=[], $arNavStartParams=false)
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		static $arSelectFields = false;
		if (!$arSelectFields)
		{
			$arSelectFields = [
				'STATUS_TITLE' => "case when P.STATUS='S' then '" . $DB->ForSql(GetMessage('POST_STATUS_SENT')) . "'
				when P.STATUS='P' then '" . $DB->ForSql(GetMessage('POST_STATUS_PART')) . "'
				when P.STATUS='E' then '" . $DB->ForSql(GetMessage('POST_STATUS_ERROR')) . "'
				when P.STATUS='W' then '" . $DB->ForSql(GetMessage('POST_STATUS_WAIT')) . "'
				else '" . $DB->ForSql(GetMessage('POST_STATUS_DRAFT')) . "' end",
				'ID' => 'P.ID',
				'STATUS' => 'P.STATUS',
				'FROM_FIELD' => 'P.FROM_FIELD',
				'TO_FIELD' => 'P.TO_FIELD',
				'EMAIL_FILTER' => 'P.EMAIL_FILTER',
				'SUBJECT' => 'P.SUBJECT',
				'BODY_TYPE' => 'P.BODY_TYPE',
				'DIRECT_SEND' => 'P.DIRECT_SEND',
				'CHARSET' => 'P.CHARSET',
				'MSG_CHARSET' => 'P.MSG_CHARSET',
				'SUBSCR_FORMAT' => 'P.SUBSCR_FORMAT',
				'TIMESTAMP_X' => $DB->DateToCharFunction('P.TIMESTAMP_X'),
				'DATE_SENT' => $DB->DateToCharFunction('P.DATE_SENT'),
			];
		}

		$this->LAST_ERROR = '';
		$arSqlSearch = [];
		$strSqlSearch = '';
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if (!is_array($val) && ((string)$val === '' || $val === 'NOT_REF'))
				{
					continue;
				}

				switch (strtoupper($key))
				{
					case 'MSG_CHARSET':
						$arSqlSearch[] = "P.MSG_CHARSET = '" . $DB->ForSql($val) . "'";
						break;
					case 'ID':
						$arSqlSearch[] = GetFilterQuery('P.ID', $val, 'N');
						break;
					case 'TIMESTAMP_1':
						if ($DB->IsDate($val))
						{
							$arSqlSearch[] = 'P.TIMESTAMP_X>=' . $DB->CharToDateFunction($val, 'SHORT');
						}
						else
						{
							$this->LAST_ERROR .= GetMessage('POST_WRONG_TIMESTAMP_FROM') . '<br>';
						}
						break;
					case 'TIMESTAMP_2':
						if ($DB->IsDate($val))
						{
							$arSqlSearch[] = 'P.TIMESTAMP_X < ' . $helper->addDaysToDateTime(1, $DB->CharToDateFunction($val, 'SHORT'));
						}
						else
						{
							$this->LAST_ERROR .= GetMessage('POST_WRONG_TIMESTAMP_TILL') . '<br>';
						}
						break;
					case 'DATE_SENT_1':
						if ($DB->IsDate($val))
						{
							$arSqlSearch[] = 'P.DATE_SENT>=' . $DB->CharToDateFunction($val, 'SHORT');
						}
						else
						{
							$this->LAST_ERROR .= GetMessage('POST_WRONG_DATE_SENT_FROM') . '<br>';
						}
						break;
					case 'DATE_SENT_2':
						if ($DB->IsDate($val))
						{
							$arSqlSearch[] = 'P.DATE_SENT < ' . $helper->addDaysToDateTime(1, $DB->CharToDateFunction($val, 'SHORT'));
						}
						else
						{
							$this->LAST_ERROR .= GetMessage('POST_WRONG_DATE_SENT_TILL') . '<br>';
						}
						break;
					case 'STATUS':
						$arSqlSearch[] = GetFilterQuery(['P.STATUS', $arSelectFields['STATUS_TITLE']], $val, 'Y', [], 'N', 'N');
						break;
					case 'STATUS_ID':
						$arSqlSearch[] = GetFilterQuery('P.STATUS', $val, 'N');
						break;
					case 'SUBJECT':
						$arSqlSearch[] = GetFilterQuery('P.SUBJECT', $val);
						break;
					case 'FROM':
						$arSqlSearch[] = GetFilterQuery('P.FROM_FIELD', $val, 'Y', ['@', '_', '.']);
						break;
					case 'TO':
						$r = GetFilterQuery('PE.EMAIL', $val, 'Y', ['@', '_', '.']);
						if ($r <> '')
						{
							$arSqlSearch[] = "EXISTS (SELECT * FROM b_posting_email PE WHERE PE.POSTING_ID=P.ID AND PE.STATUS='N' AND " . $r . ')';
						}
						break;
					case 'BODY_TYPE':
						$arSqlSearch[] = ($val == 'html') ? "P.BODY_TYPE='html'" : "P.BODY_TYPE='text'";
						break;
					case 'RUB_ID':
						if (is_array($val) && count($val) > 0)
						{
							$rub_id = [];
							foreach ($val as $i => $v)
							{
								$v = intval($v);
								if ($v > 0)
								{
									$rub_id[$v] = $v;
								}
							}
							if (count($rub_id))
							{
								$arSqlSearch[] = 'EXISTS (SELECT * from b_posting_rubric PR WHERE PR.POSTING_ID = P.ID AND PR.LIST_RUBRIC_ID in (' . implode(', ', $rub_id) . '))';
							}
						}
						break;
					case 'BODY':
						$arSqlSearch[] = GetFilterQuery('P.BODY', $val);
						break;
					case 'AUTO_SEND_TIME_1':
						if ($DB->IsDate($val, false, false, 'FULL'))
						{
							$arSqlSearch[] = '(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME>=' . $DB->CharToDateFunction($val, 'FULL') . ' )';
						}
						elseif ($DB->IsDate($val, false, false, 'SHORT'))
						{
							$arSqlSearch[] = '(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME>=' . $DB->CharToDateFunction($val, 'SHORT') . ' )';
						}
						else
						{
							$this->LAST_ERROR .= GetMessage('POST_WRONG_AUTO_FROM') . '<br>';
						}
						break;
					case 'AUTO_SEND_TIME_2':
						if ($DB->IsDate($val, false, false, 'FULL'))
						{
							$arSqlSearch[] = '(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME<=' . $DB->CharToDateFunction($val, 'FULL') . ' )';
						}
						elseif ($DB->IsDate($val, false, false, 'SHORT'))
						{
							$arSqlSearch[] = '(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME<=' . $DB->CharToDateFunction($val, 'SHORT') . ' )';
						}
						else
						{
							$this->LAST_ERROR .= GetMessage('POST_WRONG_AUTO_TILL') . '<br>';
						}
						break;
				}
			}
		}

		$arOrder = [];
		foreach ($aSort as $key => $ord)
		{
			$key = mb_strtoupper($key);
			$ord = (mb_strtoupper($ord) !== 'ASC' ? 'DESC' : 'ASC');
			switch ($key)
			{
				case 'ID':
					$arOrder[$key] = 'P.ID ' . $ord;
					break;
				case 'TIMESTAMP':
					$arOrder[$key] = 'P.TIMESTAMP_X ' . $ord;
					break;
				case 'SUBJECT':
					$arOrder[$key] = 'P.SUBJECT ' . $ord;
					break;
				case 'BODY_TYPE':
					$arOrder[$key] = 'P.BODY_TYPE ' . $ord;
					break;
				case 'STATUS':
					$arOrder[$key] = 'P.STATUS ' . $ord;
					break;
				case 'DATE_SENT':
					$arOrder[$key] = 'P.DATE_SENT ' . $ord;
					break;
				case 'AUTO_SEND_TIME':
					$arOrder[$key] = 'P.AUTO_SEND_TIME ' . $ord;
					break;
				case 'FROM_FIELD':
					$arOrder[$key] = 'P.FROM_FIELD ' . $ord;
					break;
				case 'TO_FIELD':
					$arOrder[$key] = 'P.TO_FIELD ' . $ord;
					break;
			}
		}
		if (!$arOrder)
		{
			$arOrder['ID'] = 'P.ID DESC';
		}
		$strSqlOrder = ' ORDER BY ' . implode(', ', $arOrder);

		if (!is_array($arSelect) || empty($arSelect))
		{
			$arSelect = array_keys($arSelectFields);
		}

		$arSqlSelect = [];
		foreach ($arSelect as $selectField)
		{
			if (isset($arSelectFields[$selectField]))
			{
				$arSqlSelect[$selectField] = $arSelectFields[$selectField] . ' as ' . $selectField;
			}
		}
		if (!$arSqlSelect)
		{
			$arSqlSelect['ID'] = $arSelectFields['ID'] . ' as ID';
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = '
			SELECT ' . implode(', ', $arSqlSelect) . '
			FROM b_posting P
			WHERE
			' . $strSqlSearch . '
		' . $strSqlOrder;

		if (is_array($arNavStartParams))
		{
			$nTopCount = (isset($arNavStartParams['nTopCount']) ? (int)$arNavStartParams['nTopCount'] : 0);
			if ($nTopCount > 0)
			{
				$res = $DB->Query($DB->TopSql(
					$strSql,
					$nTopCount
				));
			}
			else
			{
				$res_cnt = $DB->Query('
					SELECT COUNT(P.ID) as C
					FROM b_posting P
					WHERE
					' . $strSqlSearch . '
				');
				$res_cnt = $res_cnt->Fetch();
				$res = new CDBResult();
				$res->NavQuery($strSql, $res_cnt['C'], $arNavStartParams);
			}
		}
		else
		{
			$res = $DB->Query($strSql);
		}

		$res->is_filtered = (IsFiltered($strSqlSearch));

		return $res;
	}

	public static function Lock($ID = 0)
	{
		$connection = \Bitrix\Main\Application::getConnection();

		return $connection->lock('post_' . $ID);
	}

	public static function UnLock($ID = 0)
	{
		$connection = \Bitrix\Main\Application::getConnection();

		return $connection->unlock('post_' . $ID);
	}
}
