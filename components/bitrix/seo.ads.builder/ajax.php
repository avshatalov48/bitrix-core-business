<?

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\HttpResponse;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class SeoAdsMarketingComponentAjaxController extends \Bitrix\Main\Engine\Controller
{
	protected function processBeforeAction(Action $action)
	{
		// Avoid jSPostUnescape
		return true;
	}

	public function configureActions()
	{
		return [
			'getSliderContent' => [
				'-prefilters' => [
					ActionFilter\Csrf::class,
				],
			],
		];
	}

	public function getPostListContentAction(array $componentParams = [])
	{
		$content = $GLOBALS['APPLICATION']->includeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'PLAIN_VIEW' => 'N',
				'RETURN_CONTENT' => true,
				'POPUP_COMPONENT_NAME' => 'bitrix:seo.ads.builder',
				'POPUP_COMPONENT_TEMPLATE_NAME' => '',
				'POPUP_COMPONENT_PARAMS' => [
					'TEMPLATE' => 'postlist',
					'ACCOUNT_ID' => $componentParams['ACCOUNT_ID'],
					'CLIENT_ID' => $componentParams['CLIENT_ID'],
					'TYPE' => $componentParams['TYPE'],
					'HAS_ACCESS' => true
				],
				'IFRAME_MODE' => true
			]
		);

		$response = new HttpResponse();
		$response->setContent($content);

		return $response;
	}


	public function getAudienceContentAction(array $componentParams = [])
	{
		$content = $GLOBALS['APPLICATION']->includeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'PLAIN_VIEW' => 'N',
				'RETURN_CONTENT' => true,
				'POPUP_COMPONENT_NAME' => 'bitrix:seo.ads.builder',
				'POPUP_COMPONENT_TEMPLATE_NAME' => '',
				'POPUP_COMPONENT_PARAMS' => [
					'TEMPLATE' => 'audience',
					'ACCOUNT_ID' => $componentParams['ACCOUNT_ID'],
					'CLIENT_ID' => $componentParams['CLIENT_ID'],
					'TYPE' => $componentParams['TYPE'],
					'HAS_ACCESS' => true
				],
				'IFRAME_MODE' => true
			]
		);

		$response = new HttpResponse();
		$response->setContent($content);

		return $response;
	}


	public function getCrmAudienceContentAction(array $componentParams = [])
	{
		$content = $GLOBALS['APPLICATION']->includeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'PLAIN_VIEW' => 'N',
				'RETURN_CONTENT' => true,
				'POPUP_COMPONENT_NAME' => 'bitrix:seo.ads.builder',
				'POPUP_COMPONENT_TEMPLATE_NAME' => '',
				'POPUP_COMPONENT_PARAMS' => [
					'TEMPLATE' => 'crmaudience',
					'HAS_ACCESS' => true
				],
				'IFRAME_MODE' => true
			]
		);

		$response = new HttpResponse();
		$response->setContent($content);

		return $response;
	}


	public function getPageConfigurationContentAction($targetUrl='', array $componentParams = [])
	{
		$content = $GLOBALS['APPLICATION']->includeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'PLAIN_VIEW' => 'N',
				'RETURN_CONTENT' => true,
				'POPUP_COMPONENT_NAME' => 'bitrix:seo.ads.builder',
				'POPUP_COMPONENT_TEMPLATE_NAME' => '',
				'POPUP_COMPONENT_PARAMS' => [
					'TEMPLATE' => 'pageconfiguration',
					'ACCOUNT_ID' => $componentParams['ACCOUNT_ID'],
					'CLIENT_ID' => $componentParams['CLIENT_ID'],
					'TYPE' => $componentParams['TYPE'],
					'TARGET_URL' => $targetUrl,
					'HAS_ACCESS' => true
				],
				'IFRAME_MODE' => true
			]
		);

		$response = new HttpResponse();
		$response->setContent($content);

		return $response;
	}
}