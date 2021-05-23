<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding as TextEncoding;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\ContactListTable;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\SqlBatch;
use Bitrix\Sender\ListTable;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Security;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

Loc::loadMessages(__FILE__);

$actions = array();
$actions[] = Controller\Action::create('importList')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentJson();

		$listId = (int) $request->get('listId');
		$listName = TextEncoding::convertEncodingToCurrent(trim($request->get('listName')));
		$isBlacklist = $request->get('blacklist') === 'Y';
		$list = $request->get('list');
		$list = is_array($list) ? $list : array();

		if ($isBlacklist)
		{
			if (!Security\Access::getInstance()->canModifyBlacklist())
			{
				Security\AccessChecker::addError($content->getErrorCollection(), Security\AccessChecker::ERR_CODE_EDIT);
				return;
			}
		}
		else
		{
			if (!Security\Access::getInstance()->canModifySegments())
			{
				Security\AccessChecker::addError($content->getErrorCollection(), Security\AccessChecker::ERR_CODE_EDIT);
				return;
			}
		}

		$updateList = array();
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$dateInsert = new DateTime();

		if (!$isBlacklist && !$listId)
		{
			$result = ListTable::add(array(
				'NAME' => $listName ?: Loc::getMessage(
					'SENDER_CONTACT_IMPORT_LIST_FROM1',
					array('%date%' => PrettyDate::formatDate())
				)
			));
			if (!$result->isSuccess())
			{
				$content->getErrorCollection()->add($result->getErrors());
				return;
			}

			$listId = $result->getId();
		}

		sort($list);
		foreach ($list as $index => $item)
		{
			$item = explode(';', $item);
			TrimArr($item);
			if (!$item[0])
			{
				continue;
			}

			$code = $item[0];
			$name = isset($item[1]) ? $item[1] : null;
			$name = is_string($name) ? trim($name) : null;
			$name = TextEncoding::convertEncodingToCurrent($name);

			$typeId = Recipient\Type::detect($code);
			if (!$typeId)
			{
				continue;
			}

			$code = Recipient\Normalizer::normalize($code, $typeId);
			if (!$code)
			{
				continue;
			}

			$updateItem = array(
				'TYPE_ID' => $typeId,
				'CODE' => $code,
				'NAME' => $name,
				'DATE_INSERT' => $dateInsert,
				'DATE_UPDATE' => $dateInsert,
			);
			if ($isBlacklist)
			{
				$updateItem['BLACKLISTED'] = $isBlacklist ? 'Y' : 'N';
			}
			$updateList[] = $updateItem;
		}


		// insert contacts
		if (count($updateList) > 0)
		{

			$onDuplicateUpdateFields = array(
				'NAME',
				array(
					'NAME' => 'BLACKLISTED',
					'VALUE' => $isBlacklist ? "'Y'" : "'N'"
				),
				array(
					'NAME' => 'DATE_UPDATE',
					'VALUE' => $sqlHelper->convertToDbDateTime(new DateTime())
				)
			);
			foreach (SqlBatch::divide($updateList) as $list)
			{
				SqlBatch::insert(
					ContactTable::getTableName(),
					$list,
					$onDuplicateUpdateFields
				);
			}
		}

		// insert contacts & lists
		if ($listId)
		{
			if (count($updateList) > 0)
			{
				$codesByType = array();
				foreach ($updateList as $updateItem)
				{
					$typeId = $updateItem['TYPE_ID'];
					if (!is_array($codesByType[$typeId]))
					{
						$codesByType[$typeId] = array();
					}

					$codesByType[$typeId][] = $updateItem['CODE'];
				}
				foreach ($codesByType as $typeId => $allCodes)
				{
					$typeId = (int)$typeId;
					$listId = (int)$listId;
					$contactTableName = ContactTable::getTableName();
					$contactListTableName = ContactListTable::getTableName();
					foreach (SqlBatch::divide($allCodes) as $codes)
					{
						$codes = SqlBatch::getInString($codes);
						$sql = "INSERT IGNORE $contactListTableName ";
						$sql .= "(CONTACT_ID, LIST_ID) ";
						$sql .= "SELECT ID AS CONTACT_ID, $listId as LIST_ID ";
						$sql .= "FROM $contactTableName ";
						$sql .= "WHERE TYPE_ID=$typeId AND CODE in ($codes)";
						Application::getConnection()->query($sql);
					}
				}
			}

			$row = ListTable::getRowById($listId);
			if ($row)
			{
				$row['COUNT'] = ContactListTable::getCount(array('=LIST_ID' => $listId));
				$content->add('data', $row);
			}
		}
	}
);

Controller\Listener::create()->setActions($actions)->run();