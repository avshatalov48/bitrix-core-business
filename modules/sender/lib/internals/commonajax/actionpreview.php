<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\CommonAjax;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;

use Bitrix\Sender\Internals\QueryController as Controller;
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
	 * @param HttpRequest $request Request.
	 * @param Controller\Response $response Response.
	 */
	public static function onRequest(HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentHtml();

		Loader::includeModule('fileman');

		$canEditPhp = false;
		$canUseLpa = false;
		if (is_object($GLOBALS["USER"]))
		{
			$canEditPhp = $GLOBALS["USER"]->CanDoOperation('edit_php');
			$canUseLpa = $GLOBALS["USER"]->CanDoOperation('lpa_template_edit');
		}

		$previewParams = array(
			'CAN_EDIT_PHP' => $canEditPhp,
			'CAN_USE_LPA' => $canUseLpa,
			'SITE' => $request->get('site_id') ?: SITE_ID,
			'HTML' => $request->getPostList()->getRaw('content'),
			'FIELDS' => array(
				'SENDER_CHAIN_CODE' => 'sender_chain_item_0',
				'UNSUBSCRIBE_LINK' => 'https://example.com/'
			),
		);

		$html = EditorMail::getPreview($previewParams);
		$content->set($html);
	}
}