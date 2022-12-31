<?php

namespace Bitrix\Catalog\Integration\Report\View;

use Bitrix\Catalog\Integration\Report\Handler\BaseHandler;
use Bitrix\Report\VisualConstructor\Views\Component\Base;


abstract class CatalogView extends Base
{
	abstract public function getViewHandler(): BaseHandler;
}
