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
	/**
	 * Configures ajax actions
	 * @return array
	 */
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
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getExtensionsAction($extension = [])
	{
		$result = [];

		if (!empty($extension) && is_array($extension))
		{
			foreach ($extension as $key => $item)
			{
				$result[] = [
					'extension' => $item,
					'config' => Extension::getBundleConfig($item),
					'html' => Extension::getHtml($item),
				];
			}
		}

		return $result;
	}
}
