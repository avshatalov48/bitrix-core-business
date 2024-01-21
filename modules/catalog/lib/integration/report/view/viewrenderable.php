<?php

namespace Bitrix\Catalog\Integration\Report\View;

use Bitrix\Catalog\Integration\Report\Handler\BaseHandler;

/**
 * Interface for handling of catalog report views.
 */

interface ViewRenderable
{
	/**
	 * Return view handler instance for preparing render data.
	 *
	 * @return BaseHandler
	 */
	public function getViewHandler(): BaseHandler;
}
