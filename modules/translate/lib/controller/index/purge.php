<?php

namespace Bitrix\Translate\Controller\Index;

use Bitrix\Main;
use Bitrix\Translate;

/**
 * Action purges the indexed data.
 */
class Purge extends Translate\Controller\Action
{
	/**
	 * Executes controller action.
	 *
	 * @param string $path Path to purge.
	 *
	 * @return array
	 */
	final public function run($path = '')
	{
		if (!empty($path))
		{
			$filter = new Translate\Filter([
				'path' => $path
			]);
			(new Translate\Index\PathLangCollection())->purge($filter);
			(new Translate\Index\PathIndexCollection())->purge($filter);
		}
		else
		{
			(new Translate\Index\PathLangCollection())->purge();
			(new Translate\Index\PathIndexCollection())->purge();
		}

		return ['STATUS' => Translate\Controller\STATUS_COMPLETED];
	}
}
