<?php
namespace Bitrix\Rest;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Rest\OAuth\Auth;

Loc::loadMessages(__FILE__);

class NotifyIm implements INotify
{
	const TOKEN_NOTIFY_TAG = 'REST_CONFIRM';
	const APP_INSTALL_REQUEST_TAG = 'APP_INSTALL_REQUEST';

	public function __construct()
	{
		if(!Loader::includeModule('im'))
		{
			throw new SystemException('Module not installed: im');
		}
	}

	public function send($clientId, $userId, $token, $method, $message)
	{
		$messageFields = array(
			"TO_USER_ID" => $userId,
			"FROM_USER_ID" => '',
			"NOTIFY_TYPE" => IM_NOTIFY_CONFIRM,
			"NOTIFY_MODULE" => "rest",
			"NOTIFY_SUB_TAG" => "rest|".static::TOKEN_NOTIFY_TAG."|".$clientId."|".$token."|".$method,
			"NOTIFY_MESSAGE" => $message,

			"NOTIFY_BUTTONS" => Array(
				array('TITLE' => Loc::getMessage("REST_NOTIFY_CONFIRM"), 'VALUE' => 'Y', 'TYPE' => 'accept'),
				array('TITLE' => Loc::getMessage("REST_NOTIFY_DECLINE"), 'VALUE' => 'N', 'TYPE' => 'cancel'),
			),
		);

		$messageFields['NOTIFY_TAG'] = $messageFields['NOTIFY_SUB_TAG'].'|'.$userId;

		\CIMNotify::add($messageFields);
	}

	public static function receive($module, $tag, $value, $notifyFields)
	{
		if($module == 'rest')
		{
			$tagInfo = explode("|", $tag);

			if($tagInfo[1] === static::TOKEN_NOTIFY_TAG)
			{
				$clientId = $tagInfo[2];
				$token = $tagInfo[3];
				$method = $tagInfo[4];

				$tokenInfo = array(
					'access_token' => $token,
					'parameters' => array(
						'notify_allow' => array(
							$method => $value == 'Y' ? 1 : -1,
						),
					),
				);

				Auth::updateTokenParameters($tokenInfo);

				foreach(GetModuleEvents('rest', 'OnRestAppMethodConfirm', true) as $event)
				{
					ExecuteModuleEventEx($event, array(array(
						'APP_ID' => $clientId,
						'TOKEN' => $token,
						'METHOD' => $method,
						'CONFIRMED' => $value == 'Y',
					)));
				}
				
				\CIMNotify::deleteBySubTag($notifyFields["NOTIFY_SUB_TAG"]);
			}
			elseif($tagInfo[1] === static::APP_INSTALL_REQUEST_TAG)
			{
				\CIMNotify::DeleteBySubTag("REST|APP_INSTALL_REQUEST");

				if($value == "Y")
				{
					if (isset($notifyFields["NOTIFY_BUTTONS"][0]["APP_URL"]) && \Bitrix\Main\Loader::includeModule("im"))
					{
						$messageFields = array(
							"TO_USER_ID" => $notifyFields["RELATION_USER_ID"],
							"FROM_USER_ID" => $notifyFields["AUTHOR_ID"],
							"NOTIFY_TYPE" => IM_NOTIFY_FROM,
							"NOTIFY_MODULE" => "rest",
							"NOTIFY_TAG" => "REST|APP_INSTALL_LINK|".$notifyFields["AUTHOR_ID"]."|TO|".$notifyFields["RELATION_USER_ID"],
							"NOTIFY_SUB_TAG" => "REST|APP_INSTALL_LINK|".$notifyFields["RELATION_USER_ID"],
							"NOTIFY_MESSAGE" => GetMessage("REST_APP_INSTALL_REQUEST", array("#APP_URL#" => $notifyFields["NOTIFY_BUTTONS"][0]["APP_URL"], "#APP_NAME#" => $notifyFields["NOTIFY_BUTTONS"][0]["APP_NAME"]))
						);
						\CIMNotify::Add($messageFields);
					}
				}
			}
		}
	}
}