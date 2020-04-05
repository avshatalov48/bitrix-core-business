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

use Bitrix\Sender\Templates;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Fileman\Block\Editor;

Loc::loadMessages(__FILE__);

/**
 * Class ActionGetTemplate
 * @package Bitrix\Sender\Internals\CommonAjax
 */
class ActionGetTemplate extends CommonAction
{
	const NAME = 'getTemplate';

	/**
	 * Get action instance.
	 *
	 * @return Controller\Action
	 */
	public static function get()
	{
		return parent::get()->setRequestMethodGet();
	}

	/**
	 * On request event handler.
	 *
	 * @param HttpRequest $request Request.
	 * @param Controller\Response $response Response.
	 */
	public static function onRequest(HttpRequest $request, Controller\Response $response)
	{
		$content = $response->initContentHtml();

		$type = $request->get('template_type');
		$id = $request->get('template_id');
		$charset = $request->get('template_charset');

		Loader::includeModule('fileman');
		$template = Templates\Selector::create()
			->withTypeId($type)
			->withId($id)
			->get();
		if (!$template)
		{
			return;
		}

		$html = Editor::getHtmlForEditor($template['FIELDS']['MESSAGE']['VALUE'], $charset);
		$content->set($html);
	}
}