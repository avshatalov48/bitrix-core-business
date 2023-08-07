<?php

namespace Bitrix\Main\Grid;

/**
 * Grid's response after processing the request.
 *
 * @see \Bitrix\Main\Grid\Grid method `processRequest`
 * @see \Bitrix\Main\Grid\Panel\Panel method `processRequest`
 * @see \Bitrix\Main\Grid\Row\Rows method `processRequest`
 */
interface GridResponse
{
	/**
	 * Send response.
	 *
	 * @return void
	 */
	public function send(): void;

	/**
	 * Is possible to send this response?
	 *
	 * @return bool
	 */
	public function isSendable(): bool;
}
