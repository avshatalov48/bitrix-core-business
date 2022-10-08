<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main;
use Bitrix\Main\Authentication\ApplicationPasswordTable;
use Bitrix\Main\Authentication\ApplicationManager;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER;

$answer = array(
	"success" => false,
	"message" => Loc::getMessage("main_app_passwords_ajax_error"),
);

if(!$USER->IsAuthorized())
{
	$answer["message"] = Loc::getMessage("main_app_passwords_ajax_error_auth");
	echo Json::encode($answer);
	die();
}

if(!check_bitrix_sessid())
{
	$answer["message"] = Loc::getMessage("main_app_passwords_ajax_error_sess");
	echo Json::encode($answer);
	die();
}

$context = Bitrix\Main\Context::getCurrent();
$request = $context->getRequest();

if($request->isPost())
{
	$post = $request->getPostList()->toArray();

	$post = Main\Text\Encoding::convertEncoding($post, "UTF-8", $context->getCulture()->getCharset());

	if($post["action"] == "delete" && ($id = intval($post["ID"])) > 0)
	{
		//deleting the application password
		if(ApplicationPasswordTable::getRow(array("filter" => array("=ID" => $id, "=USER_ID" => $USER->GetID()))) !== null)
		{
			$result = ApplicationPasswordTable::delete($id);
			if($result->isSuccess())
			{
				$answer["success"] = true;
				$answer["message"] = Loc::getMessage("main_app_passwords_ajax_deleted");
			}
			else
			{
				$answer["message"] = implode("<br>", $result->getErrorMessages());
			}
		}
	}
	elseif($post["action"] == "add")
	{
		//adding a new application password

		$appManager = ApplicationManager::getInstance();
		$applications = $appManager->getApplications();

		$password = ApplicationPasswordTable::generatePassword();

		if(isset($applications[$post['APPLICATION_ID']]))
		{
			$date = new Main\Type\DateTime();
			$result = ApplicationPasswordTable::add(array(
				'USER_ID' => $USER->GetID(),
				'APPLICATION_ID' => $post['APPLICATION_ID'],
				'PASSWORD' => $password,
				'DATE_CREATE' => $date,
				'COMMENT' => $post['COMMENT'],
				'SYSCOMMENT' => $post['SYSCOMMENT'],
			));
			if($result->isSuccess())
			{
				$answer["success"] = true;
				$answer["id"] = $result->getId();
				$answer["date_create"] = $date->toString();
				$answer["password"] = '<span>'.implode('</span><span>', str_split($password, 4)).'</span>';
			}
			else
			{
				$answer["message"] = implode("<br>", $result->getErrorMessages());
			}
		}
		else
		{
			$answer["message"] = Loc::getMessage("main_app_passwords_ajax_no_app");
		}
	}
}

echo Json::encode($answer);
