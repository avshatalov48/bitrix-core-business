<?php

namespace Bitrix\Catalog\Integration\Report\View;

use Bitrix\Catalog\Integration\Report\Handler\BaseHandler;
use Bitrix\Report\VisualConstructor\Views\Component\Base;

/**
 *  Kept for backward compatibility. Changed for ViewRenderable interface.
 *
 * @deprecated
 */
abstract class CatalogView extends Base implements ViewRenderable
{
	abstract public function getViewHandler(): BaseHandler;
}
