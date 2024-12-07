<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\CommonAjax;

use Bitrix\Fileman\Block\EditorMail;
use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Integration\Crm\Connectors\Helper;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Message\Tracker;
use Bitrix\Sender\Security;

Loc::loadMessages(__FILE__);

/**
 * Class ActionPreview
 * @package Bitrix\Sender\Internals\CommonAjax
 */
class ActionPreview extends CommonAction
{
	const NAME = 'preview';

	/**
	 * On request event handler.
	 *
	 * @param Main\HttpRequest $request Request.
	 * @param Controller\Response $response Response.
	 */
	public static function onRequest(Main\HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentHtml();

		Main\Loader::includeModule('fileman');

		$canEditPhp = Security\User::current()->canEditPhp();
		$canUseLpa = Security\User::current()->canUseLpa();

		$tracker = (new Tracker(Tracker::TYPE_UNSUB))
			->setModuleId('sender')
			->setFields(array(
				'RECIPIENT_ID' => 0,
				'MAILING_ID' => 0,
				'EMAIL' => 'test@example.com',
				'CODE' => 'test@example.com',
				'TEST' => 'Y'
			))
			->setHandlerUri(Option::get('sender', 'unsub_link'));

		$html = $request->getPostList()->getRaw('content');
		preg_match_all("/#([0-9a-zA-Z_.]+?)#/", $html, $personalizeFields);
		$fieldsData = [];

		if(is_object($GLOBALS["USER"]) && isset($personalizeFields[1]))
		{
			foreach ($personalizeFields[1] as $field)
			{
				$fieldArray = explode(".", $field);
				if(count($fieldArray) > 1)
				{
					$document = Helper::getData(
						$fieldArray[0], [$GLOBALS["USER"]->GetID()]
					);
					if(!isset($document[1]))
					{
						continue;
					}
					$document = $document[1];

					$fieldsData[$field] = $document
					&& isset($document[$fieldArray[1]])
						? $document[$fieldArray[1]] :'';
				}
			}
		}

		$previewParams = array(
			'CAN_EDIT_PHP' => $canEditPhp,
			'CAN_USE_LPA' => $canUseLpa,
			'SITE' => $request->get('site_id') ?: SITE_ID,
			'HTML' => $html,
			'FIELDS' => array_merge($fieldsData, array(
				'SENDER_CHAIN_CODE' => 'sender_chain_item_0',
				'UNSUBSCRIBE_LINK' => $tracker->getLink()
			)),
		);

		$html = EditorMail::getPreview($previewParams);
		$content->set($html);
	}
}