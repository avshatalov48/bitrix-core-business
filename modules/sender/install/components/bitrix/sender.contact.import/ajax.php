<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding as TextEncoding;
use Bitrix\Sender\ContactListTable;
use Bitrix\Sender\Internals\Dto\UpdateContactDtoCollection;
use Bitrix\Sender\Internals\Factory\UpdateContactDtoFactory;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\ListTable;
use Bitrix\Sender\Security;
use Bitrix\Sender\Service\ContactListUpdateService;
use Bitrix\Sender\Service\ContactUpdateService;

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
		$updateCollection = new UpdateContactDtoCollection();
		$updateItemFactory = new UpdateContactDtoFactory($isBlacklist);
		foreach ($list as $item)
		{
			$item = explode(';', $item);
			TrimArr($item);
			if (!$item[0])
			{
				continue;
			}

			$code = (string)$item[0];
			$name = $item[1] ?? null;
			$name = is_string($name) ? trim($name) : null;
			$name = TextEncoding::convertEncodingToCurrent($name);

			$updateItem = $updateItemFactory->make($code, $name);
			if ($updateItem) {
				$updateCollection->append($updateItem);
			}
		}

		// insert contacts
		(new ContactUpdateService())->updateByCollection($updateCollection);

		// insert contacts & lists
		if ($listId)
		{

			(new ContactListUpdateService())->updateByCollection($updateCollection, $listId);

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
