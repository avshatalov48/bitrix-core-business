<?
namespace Bitrix\Main\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\CloseSession;
use Bitrix\Main\UI\Extension;

/**
 * Class LoadExt
 * @package Bitrix\Main\Controller
 */
class LoadExt extends Engine\Controller
{
	public function configureActions()
	{
		return [
			'getExtensions' => [
				'+prefilters' => [
					new CloseSession()
				],
				'-prefilters' => [
					Authentication::class
				]
			]
		];
	}

	/**
	 * @param array $extension
	 * @return array
	 */
	public function getExtensionsAction($extension = [])
	{
		$result = [];

		if (!empty($extension) && is_array($extension))
		{
			foreach ($extension as $key => $item)
			{
				$result[] = [
					"extension" => $item,
					"html" => Extension::getHtml($item)
				];
			}
		}

		return $result;
	}
}
