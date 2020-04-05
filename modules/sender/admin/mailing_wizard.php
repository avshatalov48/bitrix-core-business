<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Sender\MailingChainTable;
use \Bitrix\Sender\PostingTable;
use \Bitrix\Sender\PostingRecipientTable;
use \Bitrix\Main\Localization\Loc;

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT<"W")
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$message = new CAdminMessage(GetMessage("sender_wizard_access_denied"));
	$APPLICATION->SetTitle(GetMessage("sender_wizard_title"));
	echo $message->Show();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$APPLICATION->SetTitle(GetMessage("sender_wizard_title"));

$MAILING_ID = intval($_REQUEST['MAILING_ID']);
$MAILING_CHAIN_ID = intval($_REQUEST['MAILING_CHAIN_ID']);
$arError = array();
$isPostedFormProcessed = false;
if(empty($step))
	$step='mailing';
if(empty($ACTIVE) || $ACTIVE!='Y')
	$ACTIVE = 'N';
$title_postfix = '';

if($step=='mailing')
{
	IncludeModuleLangFile(dirname(__FILE__)."/mailing_edit.php");
	if($REQUEST_METHOD == "POST" && !$isPostedFormProcessed && check_bitrix_sessid())
	{
		$NAME = trim($NAME);
		if($MAILING_TYPE == 'NEW')
		{
			$arFields = Array(
				"ACTIVE"	=> ($ACTIVE <> "Y"? "N":"Y"),
				"SORT"		=> $SORT,
				"IS_PUBLIC"	=> ($IS_PUBLIC <> "Y"? "N":"Y"),
				"NAME"		=> $NAME,
				"DESCRIPTION"	=> $DESCRIPTION,
				"SITE_ID" => $SITE_ID,
			);


			$mailingAddDb = \Bitrix\Sender\MailingTable::add($arFields);
			if($mailingAddDb->isSuccess())
			{
				$MAILING_ID = $mailingAddDb->getId();
			}
			else
			{
				$arError = $mailingAddDb->getErrorMessages();
			}
		}
		else
		{
			$mailing = \Bitrix\Sender\MailingTable::getRowById($MAILING_ID);
			if(!$mailing)
				$arError[] = GetMessage("sender_wizard_step_mailing_existed_not_selected");
		}

		if(empty($arError))
		{
			if($MAILING_TYPE == 'NEW')
				$step = 'mailing_group';
			else
				$step = 'chain';

			$isPostedFormProcessed = true;

			LocalRedirect('sender_mailing_wizard.php?IS_TRIGGER=N&step='.$step.'&MAILING_ID='.$MAILING_ID."&lang=".LANGUAGE_ID);
		}
		else
		{
			$DB->InitTableVarsForEdit("b_sender_mailing", "", "str_");
		}
	}
	else
	{
		$str_ACTIVE = 'Y';
		$str_SORT = 100;
	}

	$arMailingList = array();
	$groupDb = \Bitrix\Sender\MailingTable::getList(array(
		'select' => array('NAME', 'ID'),
		'filter' => array('IS_TRIGGER' => 'N'),
		'order' => array('NAME' => 'ASC'),
	));
	while($arMailing = $groupDb->fetch())
	{
		$arMailingList[] = $arMailing;
	}

	if(empty($arMailingList)) $MAILING_TYPE = 'NEW';
}

if($step=='group')
{
	IncludeModuleLangFile(dirname(__FILE__)."/group_edit.php");
	if(!isset($group_create) && $REQUEST_METHOD == "POST" && !$isPostedFormProcessed && check_bitrix_sessid())
	{
		$res = false;
		$NAME = trim($NAME);
		if(isset($popup_create_group) && $popup_create_group == 'Y')
		{
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
			$NAME = $APPLICATION->ConvertCharset($NAME, "UTF-8", LANG_CHARSET);
			$DESCRIPTION = $APPLICATION->ConvertCharset($DESCRIPTION, "UTF-8", LANG_CHARSET);
		}

		$arFields = Array(
			"ACTIVE"	=> ($ACTIVE <> "Y"? "N":"Y"),
			"NAME"		=> $NAME,
			"SORT"		=> $SORT,
			"DESCRIPTION"	=> $DESCRIPTION,
		);

		if(is_array($CONNECTOR_SETTING) && count($CONNECTOR_SETTING)>0)
		{
			$groupAddDb = \Bitrix\Sender\GroupTable::add($arFields);
			if($groupAddDb->isSuccess())
			{
				$ID = $groupAddDb->getId();
				$res = ($ID > 0);
			}
			else
			{
				$arError = $groupAddDb->getErrorMessages();
			}
		}
		else
		{
			$arError[] = GetMessage('sender_group_conn_not_selected');
		}

		if($res)
		{
			if(is_array($CONNECTOR_SETTING))
			{
				$groupConnectorsDataCount = 0;
				\Bitrix\Sender\GroupConnectorTable::delete(array('GROUP_ID' => $ID));
				$arEndpointList = \Bitrix\Sender\ConnectorManager::getEndpointFromFields($CONNECTOR_SETTING);
				foreach ($arEndpointList as $endpoint)
				{
					$connector = \Bitrix\Sender\ConnectorManager::getConnector($endpoint);
					if ($connector)
					{
						$connector->setFieldValues($endpoint['FIELDS']);
						$connectorDataCount = $connector->getDataCount();
						$arGroupConnectorAdd = array(
							'GROUP_ID' => $ID,
							'NAME' => $connector->getName(),
							'ENDPOINT' => $endpoint,
							'ADDRESS_COUNT' => $connectorDataCount
						);

						$groupConnectorAddDb = \Bitrix\Sender\GroupConnectorTable::add($arGroupConnectorAdd);
						if($groupConnectorAddDb->isSuccess())
						{
							$groupConnectorsDataCount += $connectorDataCount;
						}
					}
				}
				\Bitrix\Sender\GroupTable::update($ID, array('ADDRESS_COUNT' => $groupConnectorsDataCount));
			}
		}

		if(empty($arError))
		{
			$step = 'mailing_group';
			$isPostedFormProcessed = true;

			if(isset($popup_create_group) && $popup_create_group == 'Y')
			{
				?>
				<script type="text/javascript">
					top.BX.WindowManager.Get().Close();
					top.location.reload();
				</script>
				<?
				require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
				exit();
			}
			else
			{
				LocalRedirect('sender_mailing_wizard.php?IS_TRIGGER=N&step='.$step.'&MAILING_ID='.$MAILING_ID."&lang=".LANGUAGE_ID);
			}
		}
		else
		{
			$DB->InitTableVarsForEdit("b_sender_group", "", "str_");
		}
	}
	else
	{
		$str_ACTIVE = 'Y';
		$str_SORT = 100;
	}

	if(isset($CONNECTOR_SETTING))
		$arConnectorSettings = $CONNECTOR_SETTING;
	else
		$arConnectorSettings = array();


	if(count($endpointList)>0)
	{
		$arConnectorSettings = \Bitrix\Sender\ConnectorManager::getFieldsFromEndpoint($endpointList);
	}

	$arAvailableConnectors = array();
	$arExistedConnectors = array();
	$arConnector = \Bitrix\Sender\ConnectorManager::getConnectorList();
	/** @var \Bitrix\Sender\Connector $connector */
	foreach($arConnector as $connector)
	{
		if(array_key_exists($connector->getModuleId(), $arConnectorSettings))
			$arFieldsValues = $arConnectorSettings[$connector->getModuleId()][$connector->getCode()];
		else
			$arFieldsValues = array();

		$connector->setFieldPrefix('CONNECTOR_SETTING');
		$connectorIdCount = 0;

		$arAvailableConnectors[$connector->getId()] = array(
			'ID' => $connector->getId(),
			'NAME' => $connector->getName(),
			'FORM' => $connector->getForm().'<input type="hidden" name="'.$connector->getFieldName('bx_aux_hidden_field').'" value="0">'
		);

		if( array_key_exists($connector->getModuleId(), $arConnectorSettings) )
		{
			if( array_key_exists($connector->getCode(), $arConnectorSettings[$connector->getModuleId()]) )
			{
				$connectorIdCount = 0;
				$arFieldsValuesConnector = $arConnectorSettings[$connector->getModuleId()][$connector->getCode()];
				foreach($arFieldsValuesConnector as $fieldValues)
				{
					$connector->setFieldFormName('post_form');
					$connector->setFieldValues($fieldValues);
					$arExistedConnectors[] = array(
						'ID' => $connector->getId(),
						'NAME' => $connector->getName(),
						'FORM' => str_replace('%CONNECTOR_NUM%', $connectorIdCount, $connector->getForm().'<input type="hidden" name="'.$connector->getFieldName('bx_aux_hidden_field').'" value="0">'),
						'COUNT' => $connector->getDataCount()
					);

					$connectorIdCount++;
				}
			}
		}
	}
}


if($step=='mailing_group')
{
	IncludeModuleLangFile(dirname(__FILE__)."/mailing_edit.php");
	if($REQUEST_METHOD == "POST" && !$isPostedFormProcessed && check_bitrix_sessid())
	{
		$ID = $MAILING_ID;
		$GROUP = array();
		if(isset($GROUP_INCLUDE))
		{
			$GROUP_INCLUDE = explode(',', $GROUP_INCLUDE);
			trimArr($GROUP_INCLUDE);
		}
		else
			$GROUP_INCLUDE = array();

		if(isset($GROUP_EXCLUDE))
		{
			$GROUP_EXCLUDE = explode(',', $GROUP_EXCLUDE);
			trimArr($GROUP_EXCLUDE);
		}
		else
			$GROUP_EXCLUDE = array();

		if($MAILING_ID>0)
		{
			foreach($GROUP_INCLUDE as $groupId)
			{
				if (is_numeric($groupId))
				{
					$GROUP[] = array('MAILING_ID' => $ID, 'GROUP_ID' => $groupId, 'INCLUDE' => true);
				}
			}

			foreach($GROUP_EXCLUDE as $groupId)
			{
				if (is_numeric($groupId))
				{
					$GROUP[] = array('MAILING_ID' => $ID, 'GROUP_ID' => $groupId, 'INCLUDE' => false);
				}
			}

			\Bitrix\Sender\MailingGroupTable::delete(array('MAILING_ID' => $ID));
			foreach($GROUP as $arGroup)
			{
				\Bitrix\Sender\MailingGroupTable::add($arGroup);
			}
		}

		if(empty($arError))
		{
			$step = 'chain';
			$isPostedFormProcessed = true;
			LocalRedirect('sender_mailing_wizard.php?IS_TRIGGER=N&step='.$step.'&MAILING_ID='.$MAILING_ID."&lang=".LANGUAGE_ID);
		}
	}
	else
	{
		$ID = $MAILING_ID;

		$GROUP_EXCLUDE = $GROUP_INCLUDE = array();
		$groupDb = \Bitrix\Sender\MailingGroupTable::getList(array(
			'select' => array('ID' => 'GROUP_ID', 'INCLUDE'),
			'filter' => array('MAILING_ID' => $ID),
		));
		while($arGroup = $groupDb->fetch())
		{
			if($arGroup['INCLUDE'])
				$GROUP_INCLUDE[] = $arGroup['ID'];
			else
				$GROUP_EXCLUDE[] = $arGroup['ID'];
		}
	}

	$GROUP_EXIST = array();
	$groupDb = \Bitrix\Sender\GroupTable::getList(array(
		'select' => array('NAME', 'ID', 'ADDRESS_COUNT'),
		'filter' => array('ACTIVE' => 'Y'),
		'order' => array('SORT' => 'ASC', 'NAME' => 'ASC'),
	));
	while($arGroup = $groupDb->fetch())
	{
		$GROUP_EXIST[] = $arGroup;
	}
}

if($step=='chain')
{
	IncludeModuleLangFile(dirname(__FILE__)."/mailing_chain_edit.php");

	$isUserHavePhpAccess = $USER->CanDoOperation('edit_php');

	if($REQUEST_METHOD == "POST" && !$isPostedFormProcessed && check_bitrix_sessid())
	{
		if($MAILING_CHAIN_ID <= 0)
		{
			if(!$isUserHavePhpAccess)
			{
				$MESSAGE_OLD = false;
				if($ID>0)
				{
					$mailingChainOld = \Bitrix\Sender\MailingChainTable::getRowById(array('ID' => $ID));
					if($mailingChainOld)
					{
						$MESSAGE_OLD = $mailingChainOld['MESSAGE'];
					}
				}

				$MESSAGE = CMain::ProcessLPA($MESSAGE, $MESSAGE_OLD);
			}


			$arFields = Array(
				"MAILING_ID" => $MAILING_ID,
				"SUBJECT" => $SUBJECT,
				"EMAIL_FROM"	=> $EMAIL_FROM,
				"MESSAGE" => $MESSAGE,
				"TEMPLATE_TYPE" => $TEMPLATE_TYPE,
				"TEMPLATE_ID" => $TEMPLATE_ID,
				"PRIORITY" => $PRIORITY,
				"LINK_PARAMS" => $LINK_PARAMS,
				"CREATED_BY" => $USER->GetID(),

				"REITERATE" => "N",
				"AUTO_SEND_TIME" => "",
				"DAYS_OF_WEEK" => "",
				"DAYS_OF_MONTH" => "",
				"TIMES_OF_DAY" => "",
			);

			if(empty($MESSAGE) && isset($IS_TEMPLATE_LIST_SHOWN) && $IS_TEMPLATE_LIST_SHOWN=='Y')
			{
				$arError[] = GetMessage("sender_chain_edit_error_select_template");
			}

			if(empty($arError))
			{
				$mailingAddDb = \Bitrix\Sender\MailingChainTable::add($arFields);
				if ($mailingAddDb->isSuccess())
				{
					$ID = $mailingAddDb->getId();
					\Bitrix\Sender\MailingChainTable::initPosting($ID);
					$res = ($ID > 0);
					$MAILING_CHAIN_ID = $ID;
				}
				else
				{
					$arError = $mailingAddDb->getErrorMessages();
				}
			}
		}
		if(empty($arError))
		{
			if($MAILING_CHAIN_ID > 0)
			{
				//add or delete files
				//Delete checked
				if(is_array($FILES_del))
				{
					$FILE_ID_tmp = array();
					foreach($FILES_del as $file=>$fileMarkDel)
					{
						$file = intval($file);
						if($file>0)
							$FILE_ID_tmp[] = $file;
					}

					if(count($FILE_ID_tmp)>0)
					{
						$deleteFileDb = \Bitrix\Sender\MailingAttachmentTable::getList(array(
							'select' => array('FILE_ID', 'CHAIN_ID'),
							'filter' => array('CHAIN_ID' => $ID, 'FILE_ID' => $FILE_ID_tmp),
						));
						while($arDeleteFile = $deleteFileDb->fetch())
						{
							if(!empty($arDeleteFile))
							{
								CFile::Delete($arDeleteFile["FILE_ID"]);
								\Bitrix\Sender\MailingAttachmentTable::delete($arDeleteFile);
							}
						}
					}
				}

				//Brandnew
				$arFiles = array();
				if(is_array($_FILES["NEW_FILE"]))
				{
					foreach($_FILES["NEW_FILE"] as $attribute=>$files)
					{
						if(is_array($files))
							foreach($files as $index=>$value)
								$arFiles[$index][$attribute]=$value;
					}

					foreach($arFiles as $index => $file)
					{
						if(!is_uploaded_file($file["tmp_name"]))
							unset($arFiles[$index]);
					}
				}

				//New from media library and file structure
				if(array_key_exists("NEW_FILE", $_POST) && is_array($_POST["NEW_FILE"]))
				{
					foreach($_POST["NEW_FILE"] as $index=>$value)
					{
						if (is_string($value) && preg_match("/^https?:\\/\\//", $value))
						{
							$arFiles[$index] = CFile::MakeFileArray($value);
						}
						else
						{
							if(is_array($value))
							{
								$filePath = $value['tmp_name'];
							}
							else
							{
								$filePath = $value;
							}

							$isCheckedSuccess = false;
							$io = CBXVirtualIo::GetInstance();
							$docRoot = \Bitrix\Main\Application::getDocumentRoot();
							if(strpos($filePath, CTempFile::GetAbsoluteRoot()) === 0)
							{
								$absPath = $filePath;
							}
							elseif(strpos($io->CombinePath($docRoot, $filePath), CTempFile::GetAbsoluteRoot()) === 0)
							{
								$absPath = $io->CombinePath($docRoot, $filePath);
							}
							else
							{
								$absPath = $io->CombinePath(CTempFile::GetAbsoluteRoot(), $filePath);
							}

							if ($io->ValidatePathString($absPath) && $io->FileExists($absPath))
							{
								$docRoot = $io->CombinePath($docRoot, '/');
								$relPath = str_replace($docRoot, '', $absPath);
								$perm = $APPLICATION->GetFileAccessPermission($relPath);
								if ($perm >= "W")
								{
									$isCheckedSuccess = true;
								}
							}

							if($isCheckedSuccess)
							{
								$arFiles[$index] = CFile::MakeFileArray($io->GetPhysicalName($absPath));
								if(is_array($value))
								{
									$arFiles[$index]['name'] = $value['name'];
								}
							}

						}

					}
				}

				foreach($arFiles as $file)
				{
					if(strlen($file["name"])>0 and intval($file["size"])>0)
					{
						$resultInsertAttachFile = false;
						$file["MODULE_ID"] = "main";
						$fid = intval(CFile::SaveFile($file, "sender", true));
						if($fid > 0)
						{
							$resultAddAttachFile = \Bitrix\Sender\MailingAttachmentTable::add(array(
								'CHAIN_ID' => $ID,
								'FILE_ID' => $fid
							));
							$resultInsertAttachFile = $resultAddAttachFile->isSuccess();
						}

						if(!$resultInsertAttachFile)
							break;
					}
				}

				if(isset($TEMPLATE_ACTION_SAVE) && $TEMPLATE_ACTION_SAVE == 'Y')
				{
					if(!empty($TEMPLATE_ACTION_SAVE_NAME) && !empty($MESSAGE))
					{
						$CONTENT = $MESSAGE;
						$useBlockEditor = false;

						if($TEMPLATE_TYPE && $TEMPLATE_ID)
						{
							\Bitrix\Main\Loader::includeModule('fileman');
							$chainTemplate = \Bitrix\Sender\Preset\Template::getById($TEMPLATE_TYPE, $TEMPLATE_ID);

							if($chainTemplate && $chainTemplate['HTML'])
							{
								$CONTENT = \Bitrix\Fileman\Block\Editor::fillTemplateBySliceContent($chainTemplate['HTML'], $CONTENT);

								if($CONTENT)
								{
									$useBlockEditor = true;
								}
							}
						}

						$addResult = \Bitrix\Sender\TemplateTable::add(array(
							'NAME' => $TEMPLATE_ACTION_SAVE_NAME,
							'CONTENT' => $CONTENT
						));

						if($useBlockEditor && $addResult->isSuccess())
						{
							\Bitrix\Sender\MailingChainTable::update(
								array('ID' => $ID),
								array('TEMPLATE_TYPE' => 'USER', 'TEMPLATE_ID' => $addResult->getId())
							);
						}
					}
				}
			}

			$step = 'chain_send_type';
			$isPostedFormProcessed = true;
			LocalRedirect('sender_mailing_wizard.php?IS_TRIGGER=N&step='.$step.'&MAILING_ID='.$MAILING_ID."&MAILING_CHAIN_ID=".$MAILING_CHAIN_ID."&lang=".LANGUAGE_ID);
		}
		else
		{
			$DB->InitTableVarsForEdit("b_sender_mailing_chain", "", "str_");
		}
	}
	else
	{

	}

	$arMailngChainAttachment = array();
	if($ID>0)
	{
		$attachmentFileDb = \Bitrix\Sender\MailingAttachmentTable::getList(array(
			'select' => array('FILE_ID'),
			'filter' => array('CHAIN_ID' => $ID),
		));
		while($ar = $attachmentFileDb->fetch())
		{
			if($arFileFetch = CFile::GetFileArray($ar['FILE_ID']))
				$arMailngChainAttachment[] = $arFileFetch;
		}
	}

	\Bitrix\Sender\PostingRecipientTable::setPersonalizeList(\Bitrix\Sender\MailingTable::getPersonalizeList($MAILING_ID));
	$templateListHtml = \Bitrix\Sender\Preset\Template::getTemplateListHtml('tabControl_layout');
}

if($step=='chain_send_type')
{
	$ID = $MAILING_CHAIN_ID;
	IncludeModuleLangFile(dirname(__FILE__)."/mailing_chain_edit.php");
	$DAYS_OF_WEEK = empty($DAYS_OF_WEEK) ? '' : implode(',',$DAYS_OF_WEEK);
	if($REQUEST_METHOD == "POST" && !$isPostedFormProcessed && check_bitrix_sessid())
	{
		$arFields = Array(
			"REITERATE" => "N",
			"AUTO_SEND_TIME" => "",
			"DAYS_OF_WEEK" => "",
			"DAYS_OF_MONTH" => "",
			"TIMES_OF_DAY" => "",
		);

		switch($SEND_TYPE)
		{
			case 'MANUAL':
				break;
			case 'TIME':
				if(empty($AUTO_SEND_TIME))
					$arError[] = GetMessage("sender_chain_edit_error_empty_time");

				if(!\Bitrix\Main\Type\DateTime::isCorrect($AUTO_SEND_TIME))
					$arError[] = GetMessage("sender_chain_edit_error_time_format");
				else
					$arFields["AUTO_SEND_TIME"] = new \Bitrix\Main\Type\DateTime($AUTO_SEND_TIME);

				if ($ID <= 0)
				{
					$arFields["STATUS"] = \Bitrix\Sender\MailingChainTable::STATUS_SEND;
				}
				else
				{
					$arMailingChainOld = \Bitrix\Sender\MailingChainTable::getRowById(array('ID' => $ID));
					if($arMailingChainOld['STATUS'] == \Bitrix\Sender\MailingChainTable::STATUS_NEW)
						$arFields["STATUS"] = \Bitrix\Sender\MailingChainTable::STATUS_SEND;
				}
				break;
			case 'REITERATE':
				if(empty($DAYS_OF_WEEK) && empty($DAYS_OF_MONTH))
					$arError[] = GetMessage("sender_chain_edit_error_reiterate");

				$arFields["REITERATE"] = "Y";
				$arFields["DAYS_OF_WEEK"] = $DAYS_OF_WEEK;
				$arFields["DAYS_OF_MONTH"] = $DAYS_OF_MONTH;
				$arFields["TIMES_OF_DAY"] = $TIMES_OF_DAY;

				if ($ID <= 0)
				{
					$arFields["STATUS"] = \Bitrix\Sender\MailingChainTable::STATUS_WAIT;
				}
				else
				{
					$arMailingChainOld = \Bitrix\Sender\MailingChainTable::getRowById(array('ID' => $ID));
					if($arMailingChainOld['STATUS'] == \Bitrix\Sender\MailingChainTable::STATUS_NEW)
						$arFields["STATUS"] = \Bitrix\Sender\MailingChainTable::STATUS_SEND;
				}
				$arFields["STATUS"] = \Bitrix\Sender\MailingChainTable::STATUS_WAIT;
				break;
			default:
				$arError[] = GetMessage("sender_chain_edit_error_send_type");
		}

		if(empty($arError))
		{
			$mailingUpdateDb = \Bitrix\Sender\MailingChainTable::update(array('ID' => $ID), $arFields);
			if ($mailingUpdateDb->isSuccess())
			{
				//\Bitrix\Sender\MailingChainTable::initPosting($ID);
			}
			else
			{
				$arError = $mailingUpdateDb->getErrorMessages();
			}
		}

		if(empty($arError))
		{
			LocalRedirect('sender_mailing_chain_edit.php?MAILING_ID='.$MAILING_ID.'&ID='.$ID.'&lang='.LANGUAGE_ID);
		}
		else
		{
			$DB->InitTableVarsForEdit("b_sender_mailing_chain", "", "str_");

			if(!isset($SEND_TYPE))
			{
				if ($str_REITERATE == 'Y')
					$SEND_TYPE = 'REITERATE';
				elseif (!empty($str_AUTO_SEND_TIME))
					$SEND_TYPE = 'TIME';
				elseif ($ID > 0)
					$SEND_TYPE = 'MANUAL';
			}
		}
	}
	else
	{
		$statistics = \Bitrix\Sender\Stat\Statistics::create()->filter('mailingId', $MAILING_ID);
		$recommendedSendTime = $statistics->getRecommendedSendTime();
	}
}

if($step=='trig_mailing')
{
	IncludeModuleLangFile(dirname(__FILE__)."/mailing_edit.php");

	if($REQUEST_METHOD == "POST" && !$isPostedFormProcessed && check_bitrix_sessid())
	{
		$arError = array();

		$arFields = array(
			'IS_TRIGGER' => 'Y',
			//"ACTIVE"	=> ($ACTIVE <> "Y"? "N":"Y"),
			"SORT"		=> $SORT,
			"IS_PUBLIC"	=> "N",
			"NAME"		=> $NAME,
			"DESCRIPTION"	=> $DESCRIPTION,
			"SITE_ID" => $SITE_ID,
			"EMAIL_FROM" => $EMAIL_FROM,
		);


		if(empty($EMAIL_FROM))
			$arError[] = GetMessage("sender_chain_edit_error_email_from");

		$chainList = array();
		if(empty($arError) && !empty($MAILING_TEMPLATE_CODE))
		{
			$presetMailingList = \Bitrix\Sender\MailingTable::getPresetMailingList(array('CODE' => $MAILING_TEMPLATE_CODE));
			$presetMailing = current($presetMailingList);
			if(!empty($presetMailing))
			{
				$arFields['TRIGGER_FIELDS'] = array(
					'START' => $presetMailing['TRIGGER']['START']['ENDPOINT'],
					'END' => $presetMailing['TRIGGER']['END']['ENDPOINT'],
				);

				foreach($presetMailing['CHAIN'] as $chain)
				{
					$chain['EMAIL_FROM'] = $EMAIL_FROM;
					$chain['CREATED_BY'] = $USER->GetID();
					$chainList[] = $chain;
				}

				$result = new \Bitrix\Main\Entity\Result;
				\Bitrix\Sender\MailingTable::checkFieldsChain($result, null, $chainList);
				if(!$result->isSuccess())
					$arError = array_merge($arError, $result->getErrorMessages());
			}
			else
			{
				$arError[] = GetMessage("sender_chain_edit_error_tmpl_no_found").' "' . $MAILING_TEMPLATE_CODE . '".';
			}
		}

		if(empty($arError))
		{
			if($MAILING_ID > 0)
			{
				$updateDb = \Bitrix\Sender\MailingTable::update($MAILING_ID, $arFields);
				if(!$updateDb->isSuccess())
					$arError = array_merge($arError, $updateDb->getErrorMessages());
			}
			else
			{
				$arFields['ACTIVE'] = 'N';
				$addDb = \Bitrix\Sender\MailingTable::add($arFields);
				if($addDb->isSuccess())
				{
					$MAILING_ID = $addDb->getId();
					$resultDb = \Bitrix\Sender\MailingTable::updateChain($MAILING_ID, $chainList);
					$resultDb->isSuccess();
				}
				else
					$arError = array_merge($arError, $addDb->getErrorMessages());
			}
		}

		if(empty($arError))
		{
			$isPostedFormProcessed = true;
			{
				$step = 'trig_mailing_group';
				LocalRedirect('sender_mailing_wizard.php?IS_TRIGGER=Y&step='.$step.'&MAILING_ID='.$MAILING_ID."&lang=".LANGUAGE_ID);
			}
		}
		else
		{

		}

		$DB->InitTableVarsForEdit("b_sender_mailing", "", "str_");
	}
	else
	{
		ClearVars();
		$str_SORT = 100;
		$rubric = new CDBResult(\Bitrix\Sender\MailingTable::getById($MAILING_ID));
		if(!$rubric->ExtractFields("str_"))
		{
			$DB->InitTableVarsForEdit("b_sender_mailing", "", "str_");
		}
	}

	if($MAILING_ID > 0)
	{
		$title_postfix = '_exist';
	}

	$presetMailingList = \Bitrix\Sender\MailingTable::getPresetMailingList();
}


if($step=='trig_mailing_group')
{
	IncludeModuleLangFile(dirname(__FILE__)."/mailing_edit.php");

	$triggerList = \Bitrix\Sender\TriggerManager::getList();
	$triggerListForJS = array();
	foreach($triggerList as $trigger)
	{
		foreach(array('START', 'END') as $type)
		{
			if($type == 'END' && !$trigger->canBeTarget())
			{
				continue;
			}

			$triggerListForJS[$type][$trigger->getId()] = \Bitrix\Sender\TriggerSettings::getArrayFromTrigger($trigger);
			$triggerListForJS[$type][$trigger->getId()]['ID'] = $trigger->getId();
			$triggerListForJS[$type][$trigger->getId()]['NAME'] = $trigger->getName();

			$trigger->setFieldFormName('post_form');
			$trigger->setFieldPrefix('ENDPOINT['.$type.'][FIELDS]');
			$triggerListForJS[$type][$trigger->getId()]['FORM'] = $trigger->getForm();
		}
	}

	$triggerListExists = array('START' => null, 'END' => null);
	$mailing = \Bitrix\Sender\MailingTable::getRowById($MAILING_ID);
	if(!empty($mailing['TRIGGER_FIELDS']))
	{
		foreach($triggerListExists as $type => $value)
		{
			if(!is_array($mailing['TRIGGER_FIELDS'][$type])) continue;
			$trigger = \Bitrix\Sender\TriggerManager::getOnce($mailing['TRIGGER_FIELDS'][$type]);
			if ($trigger)
			{
				$triggerListExists[$type] = $mailing['TRIGGER_FIELDS'][$type] + $triggerListForJS[$type][$trigger->getId()];

				$trigger->setFieldFormName('post_form');
				$trigger->setFieldPrefix('ENDPOINT['.$type.'][FIELDS]');
				$trigger->setFields($triggerListExists[$type]['FIELDS']);
				$triggerListExists[$type]['FORM'] = $trigger->getForm();
			}
		}
	}

	if($REQUEST_METHOD == "POST" && !$isPostedFormProcessed && check_bitrix_sessid())
	{
		$arError = array();

		$triggerListExists = array('START' => null, 'END' => null);
		foreach($triggerListExists as $type => $value)
		{
			$trigger = \Bitrix\Sender\TriggerManager::getOnce($ENDPOINT[$type]);
			if($trigger)
			{
				$triggerListExists[$type] = $ENDPOINT[$type] + \Bitrix\Sender\TriggerSettings::getArrayFromTrigger($trigger);
			}
		}

		$updateDb = \Bitrix\Sender\MailingTable::update($MAILING_ID, array('TRIGGER_FIELDS' => $triggerListExists));
		if(!$updateDb->isSuccess())
			$arError = array_merge($arError, $updateDb->getErrorMessages());

		if(empty($arError))
		{
			$isPostedFormProcessed = true;
			LocalRedirect('/bitrix/admin/sender_mailing_trig_edit.php?ID=' . $MAILING_ID . "&lang=".LANGUAGE_ID);
		}

	}

}

if(!empty($arError))
	$message = new CAdminMessage(implode("<br>", $arError));

\CJSCore::Init(array("sender_admin"));
$title = GetMessage("sender_wizard_step_".$step.$title_postfix."_title");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<div class="adm-email-master-container">
	<div class="adm-white-container">
		<div class="adm-email-master" id="sender_wizard_status">
			<?if($_REQUEST['IS_TRIGGER'] == 'Y'):?>
				<div class="adm-email-master-step adm-email-master-step-addmail sender-step-trig_mailing sender-step-passed-trig_mailing_group">
					<div class="adm-email-master-step-divider"></div>
					<div class="adm-email-master-step-icon"></div>
					<div class="adm-email-master-step-title"><?=GetMessage("sender_wizard_status_trig_mailing")?></div>
				</div>
				<div class="adm-email-master-step adm-email-master-step-timingmail sender-step-trig_mailing_group">
					<div class="adm-email-master-step-divider"></div>
					<div class="adm-email-master-step-icon"></div>
					<div class="adm-email-master-step-title"><?=GetMessage("sender_wizard_status_trig_mailing_group")?></div>
				</div>
				<div class="adm-email-master-step adm-email-master-step-addissue sender-step-chain">
					<div class="adm-email-master-step-divider"></div>
					<div class="adm-email-master-step-icon"></div>
					<div class="adm-email-master-step-title"><?=GetMessage("sender_wizard_status_trig_chain")?></div>
				</div>
				<div class="adm-email-master-step adm-email-master-step-done">
					<div class="adm-email-master-step-divider"></div>
					<div class="adm-email-master-step-icon"></div>
					<div class="adm-email-master-step-title"><?=GetMessage("sender_wizard_status_final")?></div>
				</div>
			<?else:?>
			<div class="adm-email-master-step adm-email-master-step-addmail sender-step-mailing sender-step-passed-mailing_group sender-step-passed-group sender-step-passed-chain sender-step-passed-chain_send_type">
				<div class="adm-email-master-step-divider"></div>
				<div class="adm-email-master-step-icon"></div>
				<div class="adm-email-master-step-title"><?=GetMessage("sender_wizard_status_mailing")?></div>
			</div>
			<div class="adm-email-master-step adm-email-master-step-addgroup sender-step-mailing_group sender-step-group  sender-step-passed-chain sender-step-passed-chain_send_type">
				<div class="adm-email-master-step-divider"></div>
				<div class="adm-email-master-step-icon"></div>
				<div class="adm-email-master-step-title"><?=GetMessage("sender_wizard_status_group")?></div>
			</div>
			<div class="adm-email-master-step adm-email-master-step-addissue sender-step-chain sender-step-passed-chain_send_type">
				<div class="adm-email-master-step-divider"></div>
				<div class="adm-email-master-step-icon"></div>
				<div class="adm-email-master-step-title"><?=GetMessage("sender_wizard_status_chain")?></div>
			</div>
			<div class="adm-email-master-step adm-email-master-step-timingmail sender-step-chain_send_type">
				<div class="adm-email-master-step-divider"></div>
				<div class="adm-email-master-step-icon"></div>
				<div class="adm-email-master-step-title"><?=GetMessage("sender_wizard_status_chain_send_type")?></div>
			</div>
			<div class="adm-email-master-step adm-email-master-step-done">
				<div class="adm-email-master-step-divider"></div>
				<div class="adm-email-master-step-icon"></div>
				<div class="adm-email-master-step-title"><?=GetMessage("sender_wizard_status_final")?></div>
			</div>
			<?endif;?>
		</div>
	</div>

<?
	if(isset($popup_create_group) && $popup_create_group == 'Y')
	{
		$APPLICATION->RestartBuffer();
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
		?><div class="sender-wizard-popup"><?
	}
?>

<?
if(!empty($message))
{
	echo $message->show();
}
?>

<div class="adm-white-container">
	<?if(isset($popup_create_group) && $popup_create_group == 'Y'):?>
		<script>
			BX.WindowManager.Get().SetTitle('<?=htmlspecialcharsbx($title)?>');
		</script>
	<?else:?>
	<h2 class="adm-white-container-title"><?=htmlspecialcharsbx($title)?></h2>
	<?endif;?>
	<form name="post_form" method="post" action="<?=$APPLICATION->GetCurPage()?>?IS_TRIGGER=<?=($_REQUEST['IS_TRIGGER'] == 'Y' ? 'Y' : 'N')?>&lang=<?=LANGUAGE_ID?>" enctype="multipart/form-data">
		<input type="hidden" id="step" name="step" value="<?=htmlspecialcharsbx($step)?>">
		<input type="hidden" name="MAILING_ID" value="<?=$MAILING_ID?>">
		<input type="hidden" name="MAILING_CHAIN_ID" value="<?=$MAILING_CHAIN_ID?>">
		<?=bitrix_sessid_post()?>
		<script>
			var senderWizardStatusList = BX.findChildren(BX('sender_wizard_status'), {'className': 'sender-step-<?=htmlspecialcharsbx($step)?>'}, true);
			for(var i in senderWizardStatusList)
				BX.addClass(senderWizardStatusList[i], 'active');

			var senderWizardStatusList = BX.findChildren(BX('sender_wizard_status'), {'className': 'sender-step-passed-<?=htmlspecialcharsbx($step)?>'}, true);
			for(var i in senderWizardStatusList)
				BX.addClass(senderWizardStatusList[i], 'passed');
		</script>
		<div>
	<?
	if($step=='trig_mailing'):
		$presetTypeList = array();
		$presetMailingListForJS = array();
		foreach($presetMailingList as $preset)
		{
			$preset['TYPE_ID'] = md5($preset['TYPE']);
			$presetTypeList[$preset['TYPE_ID']] = $preset['TYPE'];
			$presetMailingListForJS[$preset['CODE']] = $preset;
		}

		$presetTypeList = array_unique($presetTypeList);

		$isShowPresetList = ($MAILING_ID > 0 || !empty($str_NAME) || empty($presetTypeList)) ? false : true;
	?>
		<script>
			function SetSelectedPresetMailingType(obj, typeId)
			{
				var i, childList, easing, childEff;

				childList = BX.findChildren(BX('sender_wizard_trig_mailing_tmpl'), {'className': 'sender-wizard-trig-mailing-tmpl-list-item'}, true);
				for(i in childList)
				{
					if(!childList[i]) continue;
					child = childList[i];

					if(!typeId || BX.hasClass(child, typeId))
					{
						childEff = child;
						easing = new BX.easing({
							duration : 300,
							start : { height : 0, opacity : 50 },
							finish : { height : 100, opacity: 100 },
							transition : BX.easing.transitions.quart,
							step : function(state){
								childEff.style.opacity = state.opacity/100;
								childEff.style.display = 'block';
							},
							complete : function() {
							}
						});
						easing.animate();
					}
					else
					{
						child.style.display = 'none';
					}

				}

				childList = BX.findChildren(BX('sender_wizard_trig_mailing_tmpl'), {'className': 'sender-template-type-selector-button'}, true);
				for(i in childList)
				{
					if(!childList[i]) continue;
					child = childList[i];

					if(BX.hasClass(child, 'sender-template-type-selector-button-selected'))
						BX.removeClass(child, 'sender-template-type-selector-button-selected');
				}

				if(obj === null)
					obj = BX.findChild(BX('sender_wizard_trig_mailing_tmpl'), {'className': 'sender-template-type-selector-button'}, true);

				if(!BX.hasClass(obj, 'sender-template-type-selector-button-selected'))
					BX.addClass(obj, 'sender-template-type-selector-button-selected');
			}

			function SetSelectedPresetMailing(code)
			{
				ShowButtonNext(true);
				BX('sender_wizard_trig_mailing_tmpl').style.display = 'none';
				BX('sender_wizard_trig_mailing').style.display = 'block';
				if(code)
				{
					BX('MAILING_TEMPLATE_CODE').value = code;
					BX('MAILING_TEMPLATE_NAME').innerHTML = presetMailingList[code].NAME;
					BX('MAILING_TEMPLATE_NAME').innerHTML = presetMailingList[code].NAME;
					BX('MAILING_NAME').value = presetMailingList[code].NAME;
					BX('MAILING_DESCRIPTION').value = presetMailingList[code].DESC_USER;
				}
				else
				{
					BX('MAILING_TEMPLATE_CODE').value = '';
					BX('MAILING_TEMPLATE_NAME').innerHTML = BX.message('sender_mailing_edit_field_preset_man');
					BX('MAILING_NAME').value = '';
					BX('MAILING_DESCRIPTION').value = '';
				}
			}

			function ShowPresetMailingList()
			{
				BX('sender_wizard_trig_mailing_tmpl').style.display = 'block';
				BX('sender_wizard_trig_mailing').style.display = 'none';
				ShowButtonNext(false);
			}
			function ShowButtonNext(show)
			{
				if(show)
					BX('sender_wizard_btn_cont').style.display = 'block';
				else
					BX('sender_wizard_btn_cont').style.display = 'none';
			}

			var presetMailingList = <?=CUtil::PhpToJSObject($presetMailingListForJS);?>;
			BX.message({"sender_mailing_edit_field_preset_man" : "<?=GetMessage('sender_mailing_edit_field_preset_man')?>"});

			<?if($isShowPresetList):?>
				BX.ready(function(){
					ShowButtonNext(false);
				});
			<?endif?>
		</script>

		<div id="sender_wizard_trig_mailing_tmpl" <?=(!$isShowPresetList ? 'style="display: none;"' : '')?>>
			<p class="adm-detail-content-item-block-title"><?=GetMessage("sender_wizard_step_trig_template_title_sub");?></p>
			<table>
				<tr>
					<td colspan="2" style="vertical-align: top;">
						<div><h2><?=GetMessage("sender_mailing_edit_tmpl_add_manual")?></h2></div>
						<div style="border-bottom: 1px solid rgb(224, 224, 224); padding: 0px 20px 25px 0px;">
							<p>
								<?=GetMessage("sender_mailing_edit_tmpl_add_manual_desc")?>
							</p>
							<div>
								<a class="adm-btn adm-btn-save" href="javascript: SetSelectedPresetMailing('');">
									<?=GetMessage("sender_mailing_edit_tmpl_btn_add")?>
								</a>
							</div>
						</div>

						<br>
						<div><h2><?=GetMessage("sender_mailing_edit_tmpl_add_preset")?></div>
						<br>
					</td>
				</tr>
				<tr>
					<td style="vertical-align: top;">
						<?foreach($presetTypeList as $presetTypeId => $presetTypeName):?>
							<div class="sender-template-type-selector">
								<div class="sender-template-type-selector-title">
									<?=htmlspecialcharsbx($presetTypeName)?>
								</div>

								<?foreach($presetMailingListForJS as $presetCode => $preset):
									if($preset['TYPE_ID'] != $presetTypeId) continue;
									?>
									<div class="sender-template-type-selector-button" onclick="SetSelectedPresetMailingType(this, '<?=htmlspecialcharsbx($presetCode)?>')">
										<span><?=htmlspecialcharsbx($preset['NAME'])?></span>
									</div>
								<?endforeach;?>
							</div>
						<?endforeach;?>
					</td>

					<td id="sender_wizard_trig_mailing_tmpl_list" style="width: 100%;vertical-align: top;">
						<?
						$firstPresetMailingCode = '';
						foreach($presetMailingListForJS as $presetCode => $preset):
							if(!$firstPresetMailingCode)
							{
								$firstPresetMailingCode = $presetCode;
							}
							?>
							<div class="sender-wizard-trig-mailing-tmpl-list-item <?=htmlspecialcharsbx($presetCode)?>" style="display: none;">
								<div class="sender-wizard-trig-mailing-tmpl-list-item-inner">
									<div><h2><?=htmlspecialcharsbx($preset['NAME'])?></h2></div>
									<div>
										<div><b><?=GetMessage("sender_mailing_edit_tmpl_add_desc")?></b>:</div>
										<br><?=htmlspecialcharsbx($preset['DESC'])?>
									</div>
									<div>
										<br>
										<a class="adm-btn adm-btn-grey" href="javascript: SetSelectedPresetMailing('<?=htmlspecialcharsbx($preset['CODE'])?>');"><?=GetMessage("sender_mailing_edit_tmpl_btn_sel")?></a>
									</div>
								</div>
							</div>
						<?endforeach;?>
						<script>
							SetSelectedPresetMailingType(null ,'<?=htmlspecialcharsbx($firstPresetMailingCode)?>');
						</script>
					</td>
				</tr>
			</table>
		</div>

		<div id="sender_wizard_trig_mailing" <?=($isShowPresetList ? 'style="display: none;"' : '')?>>
			<p class="adm-detail-content-item-block-title"><?=GetMessage("sender_wizard_step_mailing_title_sub");?></p>
			<table class="adm-detail-content-table edit-table">
				<tr <?=(!$isShowPresetList ? 'style="display: none;"' : '')?>>
					<td width="40%" class="adm-detail-valign-top"><?=GetMessage("sender_mailing_edit_field_preset")?>:</td>
					<td width="60%" style="padding-top: 11px;">
						<span id="MAILING_TEMPLATE_NAME"><?=GetMessage("sender_mailing_edit_field_preset_man")?></span> <a class="sender-link-email" href="javascript: ShowPresetMailingList();"><?echo GetMessage("sender_mailing_edit_btn_show_preset")?></a>
						<input type="hidden" id="MAILING_TEMPLATE_CODE" name="MAILING_TEMPLATE_CODE" value="<?=htmlspecialcharsbx($MAILING_TEMPLATE_CODE)?>">
					</td>
				</tr>
				<tr>
					<td><?echo GetMessage("sender_mailing_edit_field_site")?></td>
					<td><?echo CLang::SelectBox("SITE_ID", $str_SITE_ID);?></td>
				</tr>
				<tr class="adm-detail-required-field">
					<td><?echo GetMessage("sender_mailing_edit_field_name")?>
						<br/>
						<span class="adm-fn"><?=GetMessage('sender_mailing_edit_field_name_desc')?></span>
					</td>
					<td><input type="text" id="MAILING_NAME" name="NAME" value="<?echo $str_NAME;?>" size="45" maxlength="100"></td>
				</tr>
				<tr class="adm-detail-required-field">
					<td><?echo GetMessage("sender_mailing_edit_field_sort")?></td>
					<td><input type="text" name="SORT" value="<?echo $str_SORT;?>" size="6"></td>
				</tr>
				<tr>
					<td class="adm-detail-valign-top">
						<?echo GetMessage("sender_mailing_edit_field_desc")?>
						<br/>
						<span class="adm-fn"><?=GetMessage('sender_mailing_edit_field_desc_desc')?></span>
					</td>
					<td><textarea class="typearea" id="MAILING_DESCRIPTION" name="DESCRIPTION" cols="45" rows="5" wrap="VIRTUAL" style="width:100%"><?echo $str_DESCRIPTION; ?></textarea></td>
				</tr>
				<tr class="adm-detail-required-field">
					<td>
						<?echo GetMessage("sender_chain_edit_field_email_from")?>
						<br/>
						<span class="adm-fn"><?=GetMessage('sender_chain_edit_field_email_from_desc')?></span>
					</td>
					<td>
						<input type="text" id="EMAIL_FROM" name="EMAIL_FROM" value="<?=$str_EMAIL_FROM?>">
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<?
						$arEmailFromList = \Bitrix\Sender\MailingChainTable::getEmailFromList();
						?>
						<?echo GetMessage("sender_mailing_edit_field_email_from_last")?>
						<?foreach($arEmailFromList as $email):?>
						<a class="sender-link-email" onclick="SetAddressToControl('EMAIL_FROM', '<?=CUtil::AddSlashes(htmlspecialcharsbx($email))?>')">
							<?=htmlspecialcharsbx($email)?>
							</a><?=(end($arEmailFromList)==$email ? '' : ',')?>
						<?endforeach?>
					</td>
				</tr>

			</table>
		</div>

		<br><br><br><br>
	<?
	elseif($step=='trig_mailing_group'):
		?>
		<script>
			function SetTrigger(bEnd, id)
			{
				var fieldName = 'START';
				if(bEnd)
					fieldName = 'END';

				var moduleId = BX('ENDPOINT_' + fieldName + '_MODULE_ID');
				var code = BX('ENDPOINT_' + fieldName + '_CODE');
				var form = BX('ENDPOINT_' + fieldName + '_FORM');
				var isClosed = BX('ENDPOINT_' + fieldName + '_IS_CLOSED_TRIGGER');
				var closedTime = BX('ENDPOINT_' + fieldName + '_CLOSED_TRIGGER_TIME');
				var runForOldData = BX('ENDPOINT_' + fieldName + '_RUN_FOR_OLD_DATA_FORM');
				var settingsButton = BX('ENDPOINT_' + fieldName + '_BUTTON');
				var closedForm = BX('ENDPOINT_' + fieldName + '_CLOSED_FORM');
				var settingsForm = BX('ENDPOINT_' + fieldName + '_SETTINGS');

				if(id && triggerList[fieldName][id])
				{
					moduleId.value = triggerList[fieldName][id].MODULE_ID;
					code.value = triggerList[fieldName][id].CODE;
					form.innerHTML = triggerList[fieldName][id].FORM;
					isClosed.value = triggerList[fieldName][id].IS_CLOSED_TRIGGER;
					closedTime.value = triggerList[fieldName][id].CLOSED_TRIGGER_TIME;
					canRunForOldData = triggerList[fieldName][id].CAN_RUN_FOR_OLD_DATA;

					if(isClosed.value == 'Y')
						closedForm.style.display = '';
					else
						closedForm.style.display = 'none';

					if(isClosed.value == 'Y' || form.innerHTML.length > 0)
						settingsForm.style.display = '';
					else
						settingsForm.style.display = 'none';

					if(runForOldData)
					{
						if(canRunForOldData == 'Y')
							runForOldData.style.display = '';
						else
							runForOldData.style.display = 'none';
					}
				}
				else
				{
					moduleId.value = '';
					code.value = '';
					form.innerHTML = '';
					isClosed.value = 'N';
					//closedTime.value = '';

					//settingsButton.style.display = 'none';
					closedForm.style.display = 'none';
					settingsForm.style.display = 'none';
				}
			}

			function ToggleTriggerForm(id)
			{
				var item = BX(id);
				if(item.style.display == 'none')
					item.style.display = 'block';
				else
					item.style.display = 'none';
			}

			function ResetFieldWasRunForOldData()
			{
				var stateField = BX('ENDPOINT_START_WAS_RUN_FOR_OLD_DATA');
				if(stateField)
					stateField.value = 'N';

				var stateForm = BX('ENDPOINT_START_RUN_FOR_OLD_DATA_RESET');
				if(stateForm)
					stateForm.style.display = 'none';
			}

			function ShowPersonalizeDescDialog(obj)
			{
				var popupWindow = BX.PopupWindowManager.create(
					'sender-letter-container-time-dialog',
					obj,
					{
						'darkMode': false,
						'closeIcon': true,
						'content': '<div style=\'padding: 10px; margin-right: 30px;\'><?=CUtil::AddSlashes(str_replace("\n", "<br>", GetMessage('sender_mailing_edit_field_trig_old_data_desc')))?></div>'
					}
				);
				if(popupWindow)
				{
					popupWindow.setBindElement(obj);
					popupWindow.show();
				}
			}

			var triggerList = <?=CUtil::PhpToJSObject($triggerListForJS);?>;
		</script>
		<table class="adm-detail-content-table edit-table">
			<tr>
				<td colspan="2">
					<div class="sender-mailing-group-container sender-mailing-group-add">
						<span class="sender-mailing-group-container-title"><span><?=GetMessage('sender_mailing_edit_field_trig_start')?></span></span>
						<span class="adm-white-container-p"><span><?=GetMessage('sender_mailing_edit_field_trig_start_caption')?></span></span>
					</div>

				</td>
			</tr>
			<tr>
				<td colspan="2">
					<table class="sender-mailing-group">
						<tr>
							<td><b><?=GetMessage('sender_mailing_edit_field_trig_select')?></b> </td>
							<td>
								<select id="EVENT_START" name="EVENT_START" onchange="SetTrigger(false, this.value);">
									<?foreach($triggerListForJS['START'] as $triggerId => $triggerParams):?>
										<option
											value="<?=htmlspecialcharsbx($triggerId)?>"
											<?=($triggerListExists['START']['ID'] == $triggerId ? 'selected' : '')?>
											><?=htmlspecialcharsbx($triggerParams['NAME'])?></option>
									<?endforeach;?>
								</select>
								<input type="hidden" id="ENDPOINT_START_MODULE_ID" name="ENDPOINT[START][MODULE_ID]" value="<?=htmlspecialcharsbx($triggerListExists['START']['MODULE_ID'])?>">
								<input type="hidden" id="ENDPOINT_START_CODE" name="ENDPOINT[START][CODE]" value="<?=htmlspecialcharsbx($triggerListExists['START']['CODE'])?>">
								<input type="hidden" id="ENDPOINT_START_IS_CLOSED_TRIGGER" name="ENDPOINT[START][IS_CLOSED_TRIGGER]" value="<?=htmlspecialcharsbx($triggerListExists['START']['IS_CLOSED_TRIGGER'])?>">
								<input type="hidden" id="ENDPOINT_START_WAS_RUN_FOR_OLD_DATA" name="ENDPOINT[START][WAS_RUN_FOR_OLD_DATA]" value="<?=($triggerListExists['START']['WAS_RUN_FOR_OLD_DATA']=='Y' ? 'Y' : 'N')?>">
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<div id="ENDPOINT_START_SETTINGS" class="sender-mailing-container" style="<?=((!empty($triggerListExists['START']['FORM']) || $triggerListExists['START']['IS_CLOSED_TRIGGER'] == 'Y') ? '' : 'display: none;')?>">
									<div id="ENDPOINT_START_CLOSED_FORM" style="<?=($triggerListExists['START']['IS_CLOSED_TRIGGER'] == 'Y' ? '' : 'display:none;')?>">
										<?=GetMessage('sender_mailing_edit_field_trig_select_close_time')?>
										<select id="ENDPOINT_START_CLOSED_TRIGGER_TIME" name="ENDPOINT[START][CLOSED_TRIGGER_TIME]">
											<?
											$timesOfDayHours = array('00', '30');
											for($hour=0; $hour<24; $hour++):
												$hourPrint = str_pad($hour, 2, "0", STR_PAD_LEFT);
												foreach($timesOfDayHours as $timePartHour):
													$hourFullPrint = $hourPrint.":".$timePartHour;
													?>
													<option value="<?=$hourFullPrint?>" <?=($hourFullPrint==$triggerListExists['START']['CLOSED_TRIGGER_TIME'] ? 'selected': '')?>><?=$hourFullPrint?></option>
												<?
												endforeach;
											endfor;
											?>
										</select>
									</div>
									<div id="ENDPOINT_START_RUN_FOR_OLD_DATA_FORM" style="<?=($triggerListExists['START']['CAN_RUN_FOR_OLD_DATA'] == 'Y' ? '' : 'display:none;')?>">
										<br>
										<?=GetMessage('sender_mailing_edit_field_trig_old_data')?>
										<?if($triggerListExists['START']['WAS_RUN_FOR_OLD_DATA'] == 'Y'):?>
											<input type="hidden" id="ENDPOINT_START_RUN_FOR_OLD_DATA" name="ENDPOINT[START][RUN_FOR_OLD_DATA]" value="Y">
											<span id="ENDPOINT_START_RUN_FOR_OLD_DATA_RESET" style="color: #878787;">
												<?=GetMessage('sender_mailing_edit_field_trig_old_data_state')?>
											</span>
										<?else:?>
											<input class="adm-designed-checkbox" type="checkbox" id="ENDPOINT_START_RUN_FOR_OLD_DATA" name="ENDPOINT[START][RUN_FOR_OLD_DATA]" value="Y" <?=($triggerListExists['START']['RUN_FOR_OLD_DATA']=='Y' ? 'checked' : '')?>>
											<label for="ENDPOINT_START_RUN_FOR_OLD_DATA" class="adm-designed-checkbox-label"></label>
										<?endif;?>
										<span style="cursor: pointer;" class="hidden-when-show-template-list-info" onclick="ShowPersonalizeDescDialog(this);">&nbsp;</span>
									</div>
									<br>
									<br>
									<div id="ENDPOINT_START_FORM"><?=$triggerListExists['START']['FORM']?></div>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr>
				<td colspan="2">
					<div class="sender-mailing-group-container sender-mailing-group-ok">
						<span class="sender-mailing-group-container-title"><span><?=GetMessage('sender_mailing_edit_field_trig_end')?></span></span>
						<span class="adm-white-container-p"><span><?=GetMessage('sender_mailing_edit_field_trig_end_caption')?></span></span>
					</div>

				</td>
			</tr>
			<tr>
				<td colspan="2">
					<table class="sender-mailing-group">
						<tr>
							<td><b><?=GetMessage('sender_mailing_edit_field_trig_select')?></b> </td>
							<td>
								<select id="EVENT_END" name="EVENT_END" onchange="SetTrigger(true, this.value);">
									<option value=""><?=GetMessage('sender_mailing_edit_field_trig_none')?></option>
									<?foreach($triggerListForJS['END'] as $triggerId => $triggerParams):?>
										<option
											value="<?=htmlspecialcharsbx($triggerId)?>"
											<?=($triggerListExists['END']['ID'] == $triggerId ? 'selected' : '')?>
											><?=htmlspecialcharsbx($triggerParams['NAME'])?></option>
									<?endforeach;?>
								</select>
								<input type="hidden" id="ENDPOINT_END_MODULE_ID" name="ENDPOINT[END][MODULE_ID]" value="<?=htmlspecialcharsbx($triggerListExists['END']['MODULE_ID'])?>">
								<input type="hidden" id="ENDPOINT_END_CODE" name="ENDPOINT[END][CODE]" value="<?=htmlspecialcharsbx($triggerListExists['END']['CODE'])?>">
								<input type="hidden" id="ENDPOINT_END_IS_CLOSED_TRIGGER" name="ENDPOINT[END][IS_CLOSED_TRIGGER]" value="<?=htmlspecialcharsbx($triggerListExists['END']['IS_CLOSED_TRIGGER'])?>">
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<div id="ENDPOINT_END_SETTINGS" class="sender-mailing-container" style="<?=((!empty($triggerListExists['END']['FORM']) || $triggerListExists['END']['IS_CLOSED_TRIGGER'] == 'Y') ? '' : 'display: none;')?>">
									<div id="ENDPOINT_END_CLOSED_FORM" style="<?=($triggerListExists['END']['IS_CLOSED_TRIGGER'] == 'Y' ? '' : 'display:none;')?>">
										<?=GetMessage('sender_mailing_edit_field_trig_select_close_time')?>
										<select id="ENDPOINT_END_CLOSED_TRIGGER_TIME" name="ENDPOINT[END][CLOSED_TRIGGER_TIME]">
											<?
											$timesOfDayHours = array('00', '30');
											for($hour=0; $hour<24; $hour++):
												$hourPrint = str_pad($hour, 2, "0", STR_PAD_LEFT);
												foreach($timesOfDayHours as $timePartHour):
													$hourFullPrint = $hourPrint.":".$timePartHour;
													?>
													<option value="<?=$hourFullPrint?>" <?=($hourFullPrint==$triggerListExists['END']['CLOSED_TRIGGER_TIME'] ? 'selected': '')?>><?=$hourFullPrint?></option>
												<?
												endforeach;
											endfor;
											?>
										</select>
										<br>
										<br>
									</div>
									<div id="ENDPOINT_END_FORM"><?=$triggerListExists['END']['FORM']?></div>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<?if(empty($mailing['TRIGGER_FIELDS'])):?>
		<script>
			SetTrigger(false, BX('EVENT_START').value);
			SetTrigger(true, BX('EVENT_END').value);
		</script>
		<?endif;?>
	<?
	elseif($step=='mailing_group'):
	?>

		<?
		function ShowGroupControl($controlName, $controlValues, $controlSelectedValues)
		{
			$controlName = htmlspecialcharsbx($controlName);
			?>
			<td>
				<select multiple style="width:350px; height:300px;" id="<?=$controlName?>_EXISTS" ondblclick="GroupManager(true, '<?=$controlName?>');">
					<?
					foreach($controlValues as $arGroup)
					{
						?><option value="<?=htmlspecialcharsbx($arGroup['ID'])?>"><?=htmlspecialcharsbx($arGroup['NAME'].' ('.$arGroup['ADDRESS_COUNT'].')')?></option><?
					}
					?>
				</select>
			</td>
			<td class="sender-mailing-group-block-sect-delim">
				<span class="adm-btn-input-container"  onClick="GroupManager(true, '<?=$controlName?>');">
					<input type="button" value="" class="adm-btn adm-btn-grey">
					<span></span>
				</span>
				<br>
				<span class="adm-btn-input-container left-input-container" onClick="GroupManager(false, '<?=$controlName?>');">
					<input type="button" value="" class="adm-btn adm-btn-grey">
					<span></span>
				</span>
			</td>
			<td>
				<select id="<?=$controlName?>" multiple="multiple" style="width:350px; height:300px;" ondblclick="GroupManager(false, '<?=$controlName?>');">
					<?
					$arGroupId = array();
					foreach($controlValues as $arGroup)
					{
						if(!in_array($arGroup['ID'], $controlSelectedValues))
							continue;

						$arGroupId[] = $arGroup['ID'];
						?><option value="<?=htmlspecialcharsbx($arGroup['ID'])?>"><?=htmlspecialcharsbx($arGroup['NAME'].' ('.$arGroup['ADDRESS_COUNT'].')')?></option><?
					}
					?>
				</select>
				<input type="hidden" name="<?=$controlName?>" id="<?=$controlName?>_HIDDEN" value="<?=implode(',', $arGroupId)?>">
			</td>
		<?
		}
		?>

		<!--
		<input name="group_create" type="button" value="<?=GetMessage("sender_wizard_step_mailing_group_field_bnt_add");?>" onclick="window.location='<?=$APPLICATION->GetCurPage().'?step=group'.'&MAILING_ID='.$MAILING_ID."&lang=".LANGUAGE_ID?>'" class="adm-btn adm-btn-save">
		-->
		<script>
			function SenderWizardShowDlgGroup()
			{
				var dlgParams ={
					'content_url':'sender_mailing_wizard.php?popup_create_group=Y&step=group&MAILING_ID=0&lang=<?=LANGUAGE_ID?>',
					'content_post' : 'group_create=Y',
					'width':'800',
					'height':'600',
					'resizable':false
				};
				new BX.CAdminDialog(dlgParams).Show();
			}
		</script>

		<table class="adm-detail-content-table edit-table">
		<tr>
			<td style="text-align: left;"><p class="adm-detail-content-item-block-title"><?=GetMessage("sender_wizard_step_mailing_group_title_sub");?></p></td>
			<td style="text-align: right; vertical-align: middle">
				<input type="button"  value="<?=GetMessage("sender_wizard_step_mailing_group_field_bnt_add");?>" onclick="SenderWizardShowDlgGroup();" class="adm-btn-green adm-btn-add" name="group_create">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="sender-mailing-group-container sender-mailing-group-add">
					<span class="sender-mailing-group-container-title"><span><?=GetMessage("sender_mailing_edit_grp_add");?></span></span>
					<span class="adm-white-container-p"><span><?=GetMessage("sender_mailing_edit_grp_add_desc");?></span></span>
				</div>

			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="sender-mailing-group">
					<tr>
						<td><span class="sender-mailing-group-block-all"><?=GetMessage("sender_mailing_edit_grp_all");?></td>
						<td class="sender-mailing-group-block-sect-delim"></td>
						<td><span class="sender-mailing-group-block-sel"><?=GetMessage("sender_mailing_edit_grp_sel");?></td>
					</tr>
					<tr>
						<?ShowGroupControl('GROUP_INCLUDE', $GROUP_EXIST, $GROUP_INCLUDE)?>
					</tr>
				</table>
			</td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr>
			<td colspan="2">
				<div class="sender-mailing-group-container sender-mailing-group-del">
					<span class="sender-mailing-group-container-title"><span><?=GetMessage("sender_mailing_edit_grp_del");?></span></span>
					<span class="adm-white-container-p"><span><?=GetMessage("sender_mailing_edit_grp_del_desc");?></span></span>
				</div>

			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="sender-mailing-group">
					<tr>
						<td><span class="sender-mailing-group-block-all"><?=GetMessage("sender_mailing_edit_grp_all");?></td>
						<td class="sender-mailing-group-block-sect-delim"></td>
						<td><span class="sender-mailing-group-block-sel"><?=GetMessage("sender_mailing_edit_grp_sel");?></td>
					</tr>
					<tr>
						<?ShowGroupControl('GROUP_EXCLUDE', $GROUP_EXIST, $GROUP_EXCLUDE)?>
					</tr>
				</table>
			</td>
		</tr>
		</table>

		<script type="text/template" id="connector-template">
			<?
			ob_start();
			?><div class="sender-box-list-item sender-box-list-item-hidden connector_form">
				<div class="sender-box-list-item-caption" onclick='ConnectorSettingShowToggle(this);'>
					<span class="sender-box-list-item-caption-image" ></span>
					<span class="sender-box-list-item-caption-name" >%CONNECTOR_NAME%</span>
					<span class="sender-mailing-sprite sender-box-list-item-caption-delete" onclick='ConnectorSettingDelete(this);'></span>
						<span class="sender-box-list-item-caption-additional">
							<span class="sender-box-list-item-caption-additional-less"><?=GetMessage('sender_group_conn_cnt')?>: </span>
							<span class="connector_form_counter">%CONNECTOR_COUNT%</span>
						</span>
				</div>
				<div class="sender-box-list-item-block connector_form_container">
					<div class="sender-box-list-item-block-item">%CONNECTOR_FORM%</div>
				</div>
			</div>
			<?
			$connectorTemplate = ob_get_clean();
			echo $connectorTemplate;
			?>
		</script>
	<?
	elseif($step=='group'):
	?>
		<table class="adm-detail-content-table edit-table">
			<tr>
				<td width="40%"><?echo GetMessage("sender_group_field_active")?></td>
				<td width="60%"><input type="checkbox" id="ACTIVE" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?>></td>
			</tr>
			<tr class="adm-detail-required-field">
				<td><?echo GetMessage("sender_group_field_name")?></td>
				<td><input type="text" name="NAME" value="<?echo $str_NAME;?>" size="45" maxlength="100"></td>
			</tr>
			<tr class="adm-detail-required-field">
				<td><?echo GetMessage("sender_group_field_sort")?></td>
				<td><input type="text" name="SORT" value="<?echo $str_SORT;?>" size="6"></td>
			</tr>
			<tr>
				<td class="adm-detail-valign-top"><?echo GetMessage("sender_group_field_desc")?></td>
				<td><textarea class="typearea" name="DESCRIPTION" cols="45" rows="5" wrap="VIRTUAL" style="width:100%"><?echo $str_DESCRIPTION; ?></textarea></td>
			</tr>


			<tr>
				<td colspan="2">
					<p class="sender-text-description-header">
						<?echo GetMessage("sender_group_conn_title")?>
					</p>
					<p class="sender-text-description-detail">
						<?echo GetMessage("sender_group_conn_desc")?>
					</p>
					<p class="sender-text-description-detail">
						<?echo GetMessage("sender_group_conn_desc_example")?>
					</p>
					<br/>
				</td>
			</tr>

			<tr class="adm-detail-required-field">
			<td colspan="2">

			<script>
				var connectorListToAdd = <?=CUtil::PhpToJSObject($arAvailableConnectors)?>;
				BX.ready(function(){
					ConnectorSettingWatch();
				});
			</script>


			<div class="sender-box-selector">
				<div class="sender-box-selector-control">
					<select id="connector_list_to_add">
						<?
						if(count($arAvailableConnectors)<=0)
						{
							echo GetMessage('sender_group_conn_not_availabe');
						}
						else
						{
							foreach ($arAvailableConnectors as $connectorId => $availableConnector)
							{
								?>
								<option value="<?= htmlspecialcharsbx($availableConnector['ID']) ?>">
									<?= htmlspecialcharsbx($availableConnector['NAME']) ?>
								</option>
							<?
							}
						}
						?>
					</select> &nbsp; <input type="button" value="<?=GetMessage('sender_group_conn_add')?>" onclick="addNewConnector();">
				</div>
			</div>
			<div id="connector_form_container" class="sender-box-list">
				<?
				$groupAddressCount = 0;
				foreach($arExistedConnectors as $existedConnector)
				{
					$existedConnectorTemplateValues = array(
						'%CONNECTOR_NAME%' => $existedConnector['NAME'],
						'%CONNECTOR_COUNT%' => $existedConnector['COUNT'],
						'%CONNECTOR_FORM%' => $existedConnector['FORM'],
					);
					echo str_replace(
						array_keys($existedConnectorTemplateValues),
						array_values($existedConnectorTemplateValues),
						$connectorTemplate
					);

					$groupAddressCount += $existedConnector['COUNT'];
				}
				?>
			</div>
			<div class="sender-group-address-counter">
				<span class="sender-mailing-sprite sender-group-address-counter-img"></span>
				<span class="sender-group-address-counter-text"><?=GetMessage('sender_group_conn_cnt_all')?></span>
				<span id="sender_group_address_counter" class="sender-group-address-counter-cnt"><?=$groupAddressCount?></span>
			</div>
			</td>
			</tr>
		</table>
		<?if(isset($popup_create_group) && $popup_create_group == 'Y'):?>
			<script type="text/javascript">
				BX.WindowManager.Get().SetButtons([BX.CDialog.prototype.btnSave, BX.CDialog.prototype.btnCancel]);
			</script>
		<?endif;?>
	<?
	elseif($step=='chain'):

		if(empty($templateListHtml) && empty($str_MESSAGE)) $str_MESSAGE = ' ';
	?>
		<table class="adm-detail-content-table edit-table" id="tabControl_layout">
			<?if(!empty($templateListHtml)):?>
				<tr class="show-when-show-template-list" <?=(!empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
					<td colspan="2" align="left">
						<?=$templateListHtml;?>
					</td>
				</tr>
				<tr class="hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
					<td colspan="2">
						<p class="adm-white-container-subtitle"><?echo GetMessage("sender_wizard_step_chain_title_sub")?></p>
						<p class="adm-white-container-p"><?echo GetMessage("sender_wizard_step_chain_title_sub_desc")?></p>
					</td>
				</tr>
				<tr class="hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
					<td><?echo GetMessage("sender_chain_edit_field_sel_templ")?></td>
					<td>
						<span class="hidden-when-show-template-list-name sender-template-message-caption-container"></span> <a class="sender-link-email sender-template-message-caption-container-btn" href="javascript: void(0);"><?echo GetMessage("sender_chain_edit_field_sel_templ_another")?></a>
					</td>
				</tr>
			<?endif;?>
			<tr class="adm-detail-required-field hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td><?echo GetMessage("sender_chain_edit_field_subject")?></td>
				<td>
					<input type="text" id="SUBJECT" name="SUBJECT" value="<?=$str_SUBJECT?>" style="width: 450px;">
				</td>
			</tr>

			<tr class="hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td>&nbsp;</td>
				<td>
					<?
					$arPersonalizeList = \Bitrix\Sender\PostingRecipientTable::getPersonalizeList();
					?>
					<?echo GetMessage("sender_chain_edit_field_subject_personalize")?>
					<?foreach($arPersonalizeList as $arPersonalize):?>
					<a class="sender-link-email" onclick="SetAddressToControl('SUBJECT', ' #<?=htmlspecialcharsbx($arPersonalize['CODE'])?>#', true)" title="#<?=htmlspecialcharsbx($arPersonalize['CODE'])?># - <?=htmlspecialcharsbx($arPersonalize['DESC'])?>">
						<?=htmlspecialcharsbx($arPersonalize['NAME'])?>
					</a><?=(end($arPersonalizeList)===$arPersonalize ? '' : ',')?>
					<?endforeach?>
					<span style="cursor: pointer;" class="hidden-when-show-template-list-info" onclick="BX.PopupWindowManager.create('sender_personalize_help', this, {'darkMode': false, 'closeIcon': true, 'content': '<div style=\'margin: 7px;\'><?=GetMessage('sender_chain_edit_pers_help')?></span>'}).show();">&nbsp;</div>
				</td>
			</tr>

			<tr class="adm-detail-required-field hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td>
					<?echo GetMessage("sender_chain_edit_field_email_from")?>
					<br/>
					<span class="adm-fn"><?=GetMessage('sender_chain_edit_field_email_from_desc')?></span>
				</td>
				<td>
					<input type="text" id="EMAIL_FROM" name="EMAIL_FROM" value="<?=$str_EMAIL_FROM?>">
				</td>
			</tr>

			<tr class="hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td>&nbsp;</td>
				<td>
					<?
					$arEmailFromList = \Bitrix\Sender\MailingChainTable::getEmailFromList();
					?>
					<?echo GetMessage("sender_chain_edit_field_email_from_last")?>
					<?foreach($arEmailFromList as $email):?>
					<a class="sender-link-email" onclick="SetAddressToControl('EMAIL_FROM', '<?=CUtil::AddSlashes(htmlspecialcharsbx($email))?>')">
						<?=htmlspecialcharsbx($email)?>
						</a><?=(end($arEmailFromList)==$email ? '' : ',')?>
					<?endforeach?>
				</td>
			</tr>

			<tr class="hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td><?echo GetMessage("sender_chain_edit_field_priority")?></td>
				<td>
					<input type="text" name="PRIORITY" id="MSG_PRIORITY" size="10" maxlength="255" value="<?echo $str_PRIORITY?>">
					<select onchange="document.getElementById('MSG_PRIORITY').value=this.value">
						<option value=""></option>
						<option value="1 (Highest)"<?if($str_PRIORITY=='1 (Highest)')echo ' selected'?>><?echo GetMessage("sender_chain_edit_field_priority_1")?></option>
						<option value="3 (Normal)"<?if($str_PRIORITY=='3 (Normal)')echo ' selected'?>><?echo GetMessage("sender_chain_edit_field_priority_3")?></option>
						<option value="5 (Lowest)"<?if($str_PRIORITY=='5 (Lowest)')echo ' selected'?>><?echo GetMessage("sender_chain_edit_field_priority_5")?></option>
					</select>
				</td>
			</tr>

			<tr class="hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td colspan="2">&nbsp;</td>
			</tr>

			<tr class="adm-detail-required-field hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td colspan="2" align="left">
					<div class="adm-detail-content-item-block">
						<span class="adm-detail-content-item-block-span"><?=GetMessage("sender_chain_edit_field_message")?></span>
						<?=\Bitrix\Sender\TemplateTable::initEditor(array(
							'FIELD_NAME' => 'MESSAGE',
							'FIELD_VALUE' => $str_MESSAGE,
							'HAVE_USER_ACCESS' => $isUserHavePhpAccess
						));?>
						<input type="hidden" name="IS_TEMPLATE_LIST_SHOWN" id="IS_TEMPLATE_LIST_SHOWN" value="<?=(empty($str_MESSAGE) ?"Y":"N")?>">
					</div>
				</td>
			</tr>


			<tr class="hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td colspan="2">&nbsp;</td>
			</tr>

			<tr class="hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td><?echo GetMessage("sender_chain_edit_field_linkparams")?></td>
				<td>
					<input type="text" id="LINK_PARAMS" name="LINK_PARAMS" value="<?=$str_LINK_PARAMS?>" style="width: 450px;">
				</td>
			</tr>

			<tr class="hidden-when-show-template-list" <?=(empty($str_MESSAGE) ? 'style="display: none;"' : '')?>>
				<td class="adm-detail-valign-top"><?=GetMessage("sender_chain_edit_field_attachment")?>:</td>
				<td>
					<?
					$arInputControlValues = array();
					foreach($arMailngChainAttachment as $arFile) $arInputControlValues["FILES[".$arFile["ID"]."]"] = $arFile["ID"];
					\Bitrix\Main\Loader::includeModule("fileman");

					if (class_exists('\Bitrix\Main\UI\FileInput', true))
					{
						echo \Bitrix\Main\UI\FileInput::createInstance((
						array(
							"name" => "NEW_FILE[n#IND#]",
							"upload" => true,
							"medialib" => true,
							"fileDialog" => true,
							"cloud" => true
						)
						))->show($arInputControlValues);
					}
					else
					{
						echo CFileInput::ShowMultiple($arInputControlValues, "NEW_FILE[n#IND#]",
							array(
								"IMAGE" => "Y",
								"PATH" => "Y",
								"FILE_SIZE" => "Y",
								"DIMENSIONS" => "Y",
								"IMAGE_POPUP" => "Y",
							),
							false,
							array(
								'upload' => true,
								'medialib' => true,
								'file_dialog' => true,
								'cloud' => true,
								'del' => true,
								'description' => false,
							)
						);
					}
					?>
				</td>
			</tr>

		</table>
		<script>
			BX.message({"SENDER_SHOW_TEMPLATE_LIST" : "<?=GetMessage('SENDER_SHOW_TEMPLATE_LIST')?>"});

				function ShowTemplateListL(bShow)
				{
					var i, displayShow, displayHide, listShown;
					if(bShow)
					{
						displayShow = 'none';
						displayHide = 'table-row';
						listShown = 'Y';
					}
					else
					{
						displayShow = '';
						displayHide = 'none';
						listShown = 'N';
					}

					var tmplTypeContList = BX.findChildren(BX('tabControl_layout'), {'className': 'hidden-when-show-template-list'}, true);
					for (i in tmplTypeContList)
						tmplTypeContList[i].style.display = displayShow;

					tmplTypeContList = BX.findChildren(BX('tabControl_layout'), {'className': 'show-when-show-template-list'}, true);
					for (i in tmplTypeContList)
						tmplTypeContList[i].style.display = displayHide;

					BX('IS_TEMPLATE_LIST_SHOWN').value = listShown;
				}

			var letterManager = new SenderLetterManager;
			letterManager.onSetTemplate(function()
			{
				ShowTemplateListL(false);
			});

			letterManager.onShowTemplateList(function(){ ShowTemplateListL(true); });
			letterManager.onHideTemplateList(function(){ ShowTemplateListL(false); });
		</script>
	<?
	elseif($step=='chain_send_type'):
	?>
		<table class="adm-detail-content-table edit-table">
			<tr>
				<td colspan="2">
					<p class="adm-white-container-p"><?=GetMessage("sender_chain_edit_field_send_type_desc");?></p>
				</td>
			</tr>
			<tr>
			<tr>
				<td colspan="2">

					<input type="hidden" name="SEND_TYPE" id="SEND_TYPE" value="<?=htmlspecialcharsbx($SEND_TYPE)?>">
					<?
					$arSendType = array(
						'MANUAL' => GetMessage('sender_chain_edit_field_send_type_MANUAL'),
						'TIME' => GetMessage('sender_chain_edit_field_send_type_TIME'),
						'REITERATE' => GetMessage('sender_chain_edit_field_send_type_REITERATE'),
					);
					?>
					<?if($str_STATUS != \Bitrix\Sender\MailingChainTable::STATUS_SEND):?>
						<div class="sender-box-selector">
							<div class="sender-box-selector-caption"><?=GetMessage('sender_chain_edit_field_send_type_selector')?></div>
							<div class="sender-box-selector-control">
								<select id="chain_send_type" name="chain_send_type"  <?=(!empty($SEND_TYPE)?'disabled':'')?>>
									<?foreach($arSendType as $sendTypeCode => $sendTypeName):?>
										<option value="<?=$sendTypeCode?>" <?=($sendTypeCode==$SEND_TYPE ? 'selected' : '')?>>
											<?=htmlspecialcharsbx($sendTypeName)?>
										</option>
									<?endforeach?>
								</select> &nbsp; <input id="sender_wizard_chain_send_type_btn" type="button" class="adm-btn-green adm-btn-add" value="<?=GetMessage('sender_chain_edit_field_send_type_button')?>" onclick="SetSendType();" <?=(!empty($SEND_TYPE)?'disabled':'')?>>
							</div>
						</div>
					<?endif;?>
					<div class="sender-box-list" id="chain_send_type_list_container">

						<div id="chain_send_type_NONE" class="sender-box-list-item" <?=($SEND_TYPE=='NONE'?'':'style="display: none;"')?>>
							<div class="sender-box-list-item-block">
								<div class="sender-box-list-item-block-item">
									<span><?=GetMessage('sender_chain_edit_field_send_type_EMPTY')?></span>
								</div>
							</div>
						</div>

						<div id="chain_send_type_MANUAL" class="sender-box-list-item" <?=($SEND_TYPE=='MANUAL'?'':'style="display: none;"')?>>
							<div class="sender-box-list-item-caption">
								<?=GetMessage('sender_chain_edit_field_send_type_MANUAL')?>
								<span class="sender-mailing-sprite sender-box-list-item-caption-delete" onclick="DeleteSelectedSendType(this);" style="height: 20px; width: 23px; margin-top: -2px;"></span>
							</div>
							<div class="sender-box-list-item-block">
								<div class="sender-box-list-item-block-item">
									<span><?=GetMessage('sender_chain_edit_field_send_type_MANUAL_desc')?></span>
								</div>
							</div>
						</div>
						<div id="chain_send_type_TIME" class="sender-box-list-item" <?=($SEND_TYPE=='TIME'?'':'style="display: none;"')?>>
							<div class="sender-box-list-item-caption">
								<?=GetMessage('sender_chain_edit_field_send_type_TIME')?>
								<span class="sender-mailing-sprite sender-box-list-item-caption-delete" onclick="DeleteSelectedSendType(this);" style="height: 20px; width: 23px; margin-top: -2px;"></span>
							</div>
							<div class="sender-box-list-item-block">
								<div class="sender-box-list-item-block-item">
									<table>
										<tr>
											<td><?=GetMessage('sender_chain_edit_field_AUTO_SEND_TIME')?></td>
											<td>
												<?echo CalendarDate("AUTO_SEND_TIME", $str_AUTO_SEND_TIME, "post_form", "20")?>
											</td>
										</tr>
										<?if ($recommendedSendTime):?>
											<tr>
												<td>&nbsp;</td>
												<td>
													<?=Loc::getMessage(
														'sender_chain_edit_recommended_sent_time',
														array(
															'%send_time%' => '<b>' . htmlspecialcharsbx($recommendedSendTime['DAY_HOUR_DISPLAY']) . '</b>',
															'%delivery_time%' => htmlspecialcharsbx($recommendedSendTime['DELIVERY_TIME']),
														)
													)?>
													<br>
													<?=Loc::getMessage('sender_chain_edit_recommended_sent_time_hint')?>
												</td>
											</tr>
										<?endif;?>
									</table>
								</div>
							</div>
						</div>
						<div id="chain_send_type_REITERATE" class="sender-box-list-item" <?=($SEND_TYPE=='REITERATE'?'':'style="display: none;"')?>>
							<div class="sender-box-list-item-caption">
								<?=GetMessage('sender_chain_edit_field_send_type_REITERATE')?>
								<span class="sender-mailing-sprite sender-box-list-item-caption-delete" onclick="DeleteSelectedSendType(this);" style="height: 20px; width: 23px; margin-top: -2px;"></span>
							</div>
							<div class="sender-box-list-item-block">
								<div class="sender-box-list-item-block-item">
									<table>
										<tr>
											<td><?echo GetMessage("rub_dom")?></td>
											<td><input class="typeinput" type="text" name="DAYS_OF_MONTH" value="<?echo $str_DAYS_OF_MONTH;?>" size="30" maxlength="100"></td>
										</tr>
										<tr>
											<td class="adm-detail-valign-top"><?echo GetMessage("rub_dow")?></td>
											<td>
												<table cellspacing=1 cellpadding=0 border=0 class="internal">
													<?	$arDoW = array(
														"1"	=> GetMessage("rubric_mon"),
														"2"	=> GetMessage("rubric_tue"),
														"3"	=> GetMessage("rubric_wed"),
														"4"	=> GetMessage("rubric_thu"),
														"5"	=> GetMessage("rubric_fri"),
														"6"	=> GetMessage("rubric_sat"),
														"7"	=> GetMessage("rubric_sun")
													);
													?>
													<tr class="heading"><?foreach($arDoW as $strVal=>$strDoW):?>
															<td><?=$strDoW?></td>
														<?endforeach;?>
													</tr>
													<tr>
														<?foreach($arDoW as $strVal=>$strDoW):?>
															<td style="text-align:center"><input type="checkbox" name="DAYS_OF_WEEK[]" value="<?=$strVal?>"<?if(array_search($strVal, explode(',',$str_DAYS_OF_WEEK)) !== false) echo " checked"?>></td>
														<?endforeach;?>
													</tr>
												</table>
											</td>
										</tr>
										<tr class="adm-detail-required-field">
											<td><?echo GetMessage("rub_tod")?></td>
											<td>
												<select name="TIMES_OF_DAY">
													<?
													$timesOfDayHours = array('00', '30');
													for($hour=0; $hour<24; $hour++):
														$hourPrint = str_pad($hour, 2, "0", STR_PAD_LEFT);
														foreach($timesOfDayHours as $timePartHour):
															$hourFullPrint = $hourPrint.":".$timePartHour;
															?>
															<option value="<?=$hourFullPrint?>" <?=($hourFullPrint==$str_TIMES_OF_DAY ? 'selected': '')?>><?=$hourFullPrint?></option>
														<?
														endforeach;
													endfor;?>
												</select>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
		</table>
	<?
	else:
	?>
		<script>
			function ShowMailingType(isShowNew)
			{
				if(isShowNew)
				{
					BX('MAILING_TYPE_EXIST_CONTAINER').style.display = 'none';
					BX('MAILING_TYPE_NEW_CONTAINER').style.display = 'block';
				}
				else
				{
					BX('MAILING_TYPE_EXIST_CONTAINER').style.display = 'block';
					BX('MAILING_TYPE_NEW_CONTAINER').style.display = 'none';
				}
			}
		</script>

		<p class="adm-white-container-p">
			<?=GetMessage("sender_wizard_text");?>
		</p>
		<div style="margin-bottom: 20px;">
			<span>
				<input class="sender-wizard-radio" type="radio" value="EXIST" name="MAILING_TYPE" id="MAILING_TYPE_EXIST" onclick="ShowMailingType(false)" <?=($MAILING_TYPE!='NEW' ? 'checked':'')?> <?=(empty($arMailingList) ? 'disabled' : '')?>>
				<label class="sender-wizard-radio-label" for="MAILING_TYPE_EXIST"><span></span><?=GetMessage("sender_wizard_step_mailing_field_exist")?></label>
			</span>
			<span>
				<input class="sender-wizard-radio" type="radio" value="NEW" name="MAILING_TYPE" id="MAILING_TYPE_NEW" onclick="ShowMailingType(true)" <?=($MAILING_TYPE=='NEW' ? 'checked':'')?>>
				<label class="sender-wizard-radio-label" for="MAILING_TYPE_NEW"><span></span><?=GetMessage("sender_wizard_step_mailing_field_new")?></label>
			</span>
		</div>

		<div class="" id="MAILING_TYPE_EXIST_CONTAINER" <?=($MAILING_TYPE=='NEW' ? 'style="display:none;"':'')?>>
			<div class="adm-detail-content-item-block">
				<select name="MAILING_ID">
					<option value=""><?=GetMessage("sender_wizard_step_mailing_field_exist_list")?></option>
					<?foreach($arMailingList as $arMailing):?>
						<option value="<?=intval($arMailing['ID'])?>"><?=htmlspecialcharsbx($arMailing['NAME'])?></option>
					<?endforeach?>
				</select>
			</div>
		</div>
		<div class="" id="MAILING_TYPE_NEW_CONTAINER" <?=($MAILING_TYPE!='NEW' ? 'style="display:none;"':'')?>>
			<p class="adm-white-container-p">
				<?=GetMessage("sender_mailing_edit_main");?>
			</p>
			<div class="adm-detail-content-item-block">
				<p class="adm-detail-content-item-block-title"><?=GetMessage("sender_wizard_step_mailing_title_sub");?></p>
				<table class="adm-detail-content-table edit-table">
					<tr>
						<td width="40%" class="adm-detail-valign-top"><?echo GetMessage("sender_mailing_edit_field_active")?></td>
						<td width="60%" style="padding-top: 11px;">
							<input class="adm-designed-checkbox" type="checkbox" id="ACTIVE" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?>>
							<label for="ACTIVE" class="adm-designed-checkbox-label"></label>
						</td>
					</tr>
					<tr>
						<td><?echo GetMessage("sender_mailing_edit_field_site")?></td>
						<td><?echo CLang::SelectBox("SITE_ID", $str_SITE_ID);?></td>
					</tr>
					<tr class="adm-detail-required-field">
						<td><?echo GetMessage("sender_mailing_edit_field_name")?>
							<br/>
							<span class="adm-fn"><?=GetMessage('sender_mailing_edit_field_name_desc')?></span>
						</td>
						<td><input type="text" name="NAME" value="<?echo $str_NAME;?>" size="45" maxlength="100"></td>
					</tr>
					<tr class="adm-detail-required-field">
						<td><?echo GetMessage("sender_mailing_edit_field_sort")?></td>
						<td><input type="text" name="SORT" value="<?echo $str_SORT;?>" size="6"></td>
					</tr>
					<tr>
						<td class="adm-detail-valign-top">
							<?echo GetMessage("sender_mailing_edit_field_desc")?>
							<br/>
							<span class="adm-fn"><?=GetMessage('sender_mailing_edit_field_desc_desc')?></span>
						</td>
						<td><textarea class="typearea" name="DESCRIPTION" cols="45" rows="5" wrap="VIRTUAL" style="width:100%"><?echo $str_DESCRIPTION; ?></textarea></td>
					</tr>
					<tr>
						<td><?echo GetMessage("sender_mailing_edit_field_is_public")?></td>
						<td style="padding-top: 11px;">
							<input class="adm-designed-checkbox" type="checkbox" id="IS_PUBLIC" name="IS_PUBLIC" value="Y"<?if($str_IS_PUBLIC != "N") echo " checked"?>>
							<label for="IS_PUBLIC" class="adm-designed-checkbox-label"></label>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?
	endif;
	?>
		</div>
		<?if(isset($popup_create_group) && $popup_create_group == 'Y'):?>
		<?else:?>
			<div class="sender-wizard-btn-cont" id="sender_wizard_btn_cont">
			<?if($step=='chain_send_type'):?>
				<a href="javascript: BX.submit(document.forms['post_form'])" class="adm-btn adm-btn-save"><?=GetMessage("sender_wizard_step_mailing_bnt_end")?></a>
			<?else:?>
				<a href="javascript: BX.submit(document.forms['post_form'])" class="adm-btn adm-btn-grey"><?=GetMessage("sender_wizard_step_mailing_bnt_next")?></a>
			<?endif;?>
			</div>
		<?endif?>
	</form>
</div>

<?
	if(isset($popup_create_group) && $popup_create_group == 'Y'):
		?></div><?
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
		exit();
	endif;
?>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>