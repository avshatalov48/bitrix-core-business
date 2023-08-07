<?php

namespace Bitrix\Main\Grid;

use Bitrix\Main\HttpRequest;

/**
 * Request to the grid.
 *
 * Used to process the actions of the grid and all related entities.
 *
 * @see \Bitrix\Main\Grid\Grid method `processRequest`
 * @see \Bitrix\Main\Grid\Panel\Panel method `processRequest`
 * @see \Bitrix\Main\Grid\Row\Rows method `processRequest`
 */
interface GridRequest
{
	/**
	 * HTTP request
	 *
	 * @return HttpRequest
	 */
	public function getHttpRequest(): HttpRequest;

	/**
	 * Grid id.
	 *
	 * @return string|null
	 */
	public function getGridId(): ?string;

	/**
	 * Id of grid action.
	 *
	 * @return string|null
	 */
	public function getGridActionId(): ?string;

	/**
	 * Id of panel action.
	 *
	 * @return string|null
	 */
	public function getPanelActionId(): ?string;

	/**
	 * Id of row action.
	 *
	 * @return string|null
	 */
	public function getRowActionId(): ?string;

	/**
	 * The "for all" checkbox is selected in the panel action.
	 *
	 * @return bool
	 */
	public function isSelectedAllPanelRows(): bool;

	/**
	 * The "for all" checkbox is selected in the panel group action.
	 *
	 * @return bool
	 */
	public function isSelectedAllPanelGroupRows(): bool;
}
