<?php

namespace Bitrix\Main\Grid\UI;

use Bitrix\Main\HttpRequest;

/**
 * Grid's request for `bitrix:main.ui.grid` component.
 */
class GridRequest implements \Bitrix\Main\Grid\GridRequest
{
	protected HttpRequest $request;

	/**
	 * @param HttpRequest $request
	 */
	public function __construct(HttpRequest $request)
	{
		$this->request = $request;
	}

	/**
	 * Name of request param "for all"
	 *
	 * @return string
	 */
	private function getRequestSelectedAllRowsName(): string
	{
		return 'action_all_rows_' . $this->getGridId();
	}

	#region override methods

	/**
	 * @inheritDoc
	 *
	 * @return HttpRequest
	 */
	final public function getHttpRequest(): HttpRequest
	{
		return $this->request;
	}

	/**
	 * @inheritDoc
	 *
	 * @return string|null
	 */
	public function getGridId(): ?string
	{
		return $this->request->get('grid_id');
	}

	/**
	 * @inheritDoc
	 *
	 * @return string|null
	 */
	public function getGridActionId(): ?string
	{
		return $this->request->get('grid_action');
	}

	/**
	 * @inheritDoc
	 *
	 * @return string|null
	 */
	public function getPanelActionId(): ?string
	{
		$actionButtonName = 'action_button_' . $this->getGridId();

		return $this->request->getPost($actionButtonName);
	}

	/**
	 * @inheritDoc
	 *
	 * @return string|null
	 */
	public function getRowActionId(): ?string
	{
		$actionButtonName = 'action_button_' . $this->getGridId();

		return $this->request->getPost($actionButtonName);
	}

	/**
	 * @inheritDoc
	 *
	 * @return bool
	 */
	public function isSelectedAllPanelRows(): bool
	{
		$name = $this->getRequestSelectedAllRowsName();

		return $this->request->getPost($name) === 'Y';
	}

	/**
	 * @inheritDoc
	 *
	 * @return bool
	 */
	public function isSelectedAllPanelGroupRows(): bool
	{
		$name = $this->getRequestSelectedAllRowsName();
		$value = $this->request->getPost('controls')[$name] ?? null;

		return $value === 'Y';
	}

	#endregion override methods
}
