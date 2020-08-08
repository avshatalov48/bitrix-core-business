<?php
namespace Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies;

use Bitrix\Sale\Exchange\Integration\Rest\Cmd;

interface ICmd
{
	/**
	 * @param $pageType
	 * @param $fields
	 * @return Cmd\CmdBase
	 */
	public function cmd($pageType, $fields);
	/**
	 * @param $pageType
	 * @param $list
	 * @return Cmd\Batch
	 */
	public function batch($pageType, $list);
}