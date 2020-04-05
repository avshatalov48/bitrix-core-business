<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var string $strErrorMessage
 * @param CBitrixComponent $component
 * @param CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if ($arParams['AJAX_POST'] == 'Y' && $arParams['ACTION'] == 'REPLY')
{
	$FHParser = new CForumSimpleHTMLParser(ob_get_clean());
	$statusMessage = $FHParser->getTagHTML('div[class=forum-note-box]');
	$JSResult = array("statusMessage" => $statusMessage);

	if ($request->getPost("MESSAGE_MODE") != "VIEW")
	{
		$result = intval($arResult['RESULT']);
		if ($result === 0)
		{
			$JSResult += array(
				'status' => false,
				'error' => $strErrorMessage
			);
		}
		else 
		{
			$pageNumber = intval($request->getPost('pageNumber'));
			if ($pageNumber > 0 && $pageNumber != $arResult['PAGE_NUMBER']) // user is not on the last forum messages page
			{
				$messagePost = $FHParser->getInnerHTML('<!--FORUM_INNER-->', '<!--FORUM_INNER_END-->');
				$messageNavigation = $FHParser->getTagHTML('div[class=forum-navigation-box]');

				$JSResult += array(
					'status' => true,
					'allMessages' => true,
					'message' => $messagePost,
					'navigation' => $messageNavigation,
					'pageNumber' => $arResult['PAGE_NUMBER']
				);
			}
			else 
			{
				$messagePost = $FHParser->getInnerHTML('<!--MSG_'.$result.'-->', '<!--MSG_END_'.$result.'-->');
				$JSResult += array(
					'status' => true,
					'allMessages' => false,
					'message' => $messagePost,
					'messageID' => $result,
				);
			}
			if (strpos($JSResult['message'], "ForumInitSpoiler") !== false)
			{
				$fname = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/forum.interface/templates/spoiler/script.js";
				if (file_exists($fname))
					$JSResult['message'] =
						'<script src="/bitrix/components/bitrix/forum.interface/templates/spoiler/script.js?'.filemtime($fname).'" type="text/javascript"></script>'.
						"\n".$JSResult['message'];
			}
			if (strpos($JSResult['message'], "onForumImageLoad") !== false)
			{
				$SHParser = new CForumSimpleHTMLParser($APPLICATION->GetHeadStrings());
				$scripts = $SHParser->getInnerHTML('<!--LOAD_SCRIPT-->', '<!--END_LOAD_SCRIPT-->');

				if ($scripts !== "")
					$JSResult['message'] = $scripts."\n".$JSResult['message'];
			}
		}
	}
	else if (strlen($arResult["ERROR_MESSAGE"]) < 1) // preview post
	{
		$messagePreview = $FHParser->getInnerHTML('<!--MSG_PREVIEW-->', '<!--MSG_END_MSG_PREVIEW-->');
		$JSResult += array(
			'status' => true,
			'previewMessage' => $messagePreview);
		if (strpos($JSResult['previewMessage'], "ForumInitSpoiler") !== false)
		{
			$fname = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/forum.interface/templates/spoiler/script.js";
			if (file_exists($fname))
				$JSResult['previewMessage'] =
					'<script src="/bitrix/components/bitrix/forum.interface/templates/spoiler/script.js?'.filemtime($fname).'" type="text/javascript"></script>'.
						$JSResult['previewMessage'];
		}
		if (strpos($JSResult['previewMessage'], "onForumImageLoad") !== false)
		{
			$SHParser = new CForumSimpleHTMLParser($APPLICATION->GetHeadStrings());
			$scripts = $SHParser->getInnerHTML('<!--LOAD_SCRIPT-->', '<!--END_LOAD_SCRIPT-->');

			if ($scripts !== "")
				$JSResult['previewMessage'] = $scripts."\n".$JSResult['previewMessage'];
		}
	}
	else
	{
		$JSResult += array(
			'status' => false,
			'error' => $arResult["ERROR_MESSAGE"]
		);
	}

	$APPLICATION->RestartBuffer();
	while (ob_end_clean());

	if ($request->getPost("dataType") == "json")
	{
		header('Content-Type:application/json; charset=UTF-8');
		echo \Bitrix\Main\Web\Json::encode($JSResult);
	}
	else
	{
		echo "<script>top.BX.Forum.SetForumAjaxPostTmp(".CUtil::PhpToJSObject($JSResult).");</script>";
	}

	\CMain::FinalActions();
	die();
}