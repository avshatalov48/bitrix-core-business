<?php
use Bitrix\Main\UrlPreview\UrlPreview;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
global $USER, $APPLICATION;

if(!$USER->IsAuthorized() || !check_bitrix_sessid())
	die();

if($_REQUEST['action'] === 'attachUrlPreview')
{
	session_write_close();

	$urlMetadata = null;
	$userFieldId = filter_var($_REQUEST['userFieldId'], FILTER_VALIDATE_INT);
	$elementId = filter_var($_REQUEST['elementId'], FILTER_SANITIZE_STRING);

	if($userFieldId === false)
	{
		die();
	}

	if(isset($_REQUEST['url']))
	{
		$urlPattern = "~^
				(https?://|//)?       # protocol (optional)
				(([\w\-]+:)?([\w\-]+)@)?    # basic auth
				([\w\-\.]+?)               # hostname or ip address
				(:[0-9]+)?                # a port (optional)
				(/?|/\S+|\?\S*|\#\S*)   # a /, nothing, a / with something, a query or a fragment
			$~ixu";
		$url = $_REQUEST['url'];
		$url = trim($url, '!"#$%&\'()*+,-.@:;<=>[\\]^_`{|}~');
		if(!preg_match($urlPattern, $url))
			die();

		if(!\Bitrix\Main\Application::isUtfMode())
			$url = \Bitrix\Main\Text\Encoding::convertEncoding($url, 'UTF-8', \Bitrix\Main\Context::getCurrent()->getCulture()->getCharset());

		if(UrlPreview::isEnabled())
		{
			$urlMetadata = UrlPreview::getMetadataByUrl($url, true, false);
		}
	}
	else if(isset($_REQUEST['id']))
	{
		$signer = new \Bitrix\Main\Security\Sign\Signer();
		try
		{
			$id = $signer->unsign($_REQUEST['id'], UrlPreview::SIGN_SALT);
		}
		catch (Bitrix\Main\SystemException $e)
		{
			die();
		}

		if(UrlPreview::isEnabled())
		{
			$metadata = UrlPreview::getMetadataAndHtmlByIds(array($id), true);
			if(isset($metadata[$id]))
			{
				$urlMetadata = $metadata[$id];
			}
		}
	}

	if(!isset($urlMetadata['ID']))
		die();

	$userFieldParams = array(
		'arUserField' => \CUserTypeEntity::getById($userFieldId),
		'urlPreviewId' => $elementId
	);

	$userField = array(
		'VALUE' => array($urlMetadata['ID'])
	);

	$APPLICATION->ShowAjaxHead();
	$outputHtml = UrlPreview::showEdit($userField, $userFieldParams);

	echo $outputHtml;
	die();
}

