<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\CommonAjax;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Main\Config\Option;

use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Security;
use Bitrix\Sender\Message\Tracker;
use Bitrix\Fileman\Block\EditorMail;

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

		$previewParams = array(
			'CAN_EDIT_PHP' => $canEditPhp,
			'CAN_USE_LPA' => $canUseLpa,
			'SITE' => $request->get('site_id') ?: SITE_ID,
			'HTML' => ($html && Main\Text\Encoding::detectUtf8($html))
				? Main\Text\Encoding::convertEncoding(
					$html,
					'UTF-8',
					SITE_CHARSET
					)
				: $html
			,
			'FIELDS' => array(
				'SENDER_CHAIN_CODE' => 'sender_chain_item_0',
				'UNSUBSCRIBE_LINK' => $tracker->getLink()
			),
		);

		$html = EditorMail::getPreview($previewParams);
		$content->set($html);
	}
}