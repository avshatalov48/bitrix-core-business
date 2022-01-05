<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Socialnetwork\ComponentHelper;

class SocialnetworkGroupCopyAjaxController extends \Bitrix\Main\Engine\Controller
{
	public function __construct(\Bitrix\Main\Request $request = null)
	{
		parent::__construct($request);

		\Bitrix\Main\Loader::includeModule('socialnetwork');
	}

	public function getComponentAction(array $params = []): \Bitrix\Main\Engine\Response\Component
	{
		$componentParameters = ComponentHelper::getWorkgroupSliderMenuUnsignedParameters($this->getSourceParametersList());

		$componentResponse = new \Bitrix\Main\Engine\Response\Component(
			'bitrix:socialnetwork.group_copy',
			($params['componentTemplate'] ?? ''),
			$componentParameters,
			[],
			[ 'PageTitle' ]
		);

		return $componentResponse;
	}
}