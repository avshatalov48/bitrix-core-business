<?php

namespace Bitrix\Bizproc\Calc\Libs;

use Bitrix\Main;
use Bitrix\Bizproc\Calc\Arguments;
use Bitrix\Main\Localization\Loc;

class DocumentLib extends BaseLib
{
	public function getFunctions(): array
	{
		return [
			'getdocumenturl' => [
				'args' => true,
				'func' => 'callGetDocumentUrl',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_GETDOCUMENTURL_DESCRIPTION'),
			],
		];
	}

	public function callGetDocumentUrl(Arguments $args)
	{
		$format = $args->getFirst();
		$external = $args->getSecond();
		$activity = $args->getParser()->getActivity();

		$url = $activity->workflow->getRuntime()->getDocumentService()->getDocumentAdminPage(
			$activity->getDocumentId()
		);
		$name = null;

		if ($external)
		{
			$url = Main\Engine\UrlManager::getInstance()->getHostUrl() . $url;
		}

		if ($format === 'bb' || $format === 'html')
		{
			$name = $activity->workflow->getService('DocumentService')->getDocumentName(
				$activity->getDocumentId()
			);
		}

		if ($format === 'bb')
		{
			return sprintf(
				'[url=%s]%s[/url]',
				$url,
				$name
			);
		}

		if ($format === 'html')
		{
			return sprintf(
				'<a href="%s" target="_blank">%s</a>',
				$url,
				htmlspecialcharsbx($name)
			);
		}

		return $url;
	}
}
