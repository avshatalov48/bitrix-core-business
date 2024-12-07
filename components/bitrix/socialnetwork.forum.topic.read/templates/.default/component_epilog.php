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
	$response = ob_get_clean();

	$FHParser = new CForumSimpleHTMLParser($response);

	$statusMessage = $FHParser->getTagHTML('div[class=forum-note-box]');
	$JSResult = array("statusMessage" => $statusMessage);

	if ($_POST["MESSAGE_MODE"] != "VIEW")
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
				$messageStart = $FHParser->getInnerHTML('<!--MSG_START-->', '<!--MSG_START_END-->');

				$JSResult += array(
					'status' => true,
					'allMessages' => true,
					'message' => $messagePost,
					'messageStart' => $messageStart,
					'navigation' => $messageNavigation,
					'pageNumber' => $arResult['PAGE_NUMBER']
				);
			}
			else 
			{
				$JSResult['allMessages'] = false;
				$messagePost = $FHParser->getInnerHTML('<!--MSG_'.$result.'-->', '<!--MSG_END_'.$result.'-->');
				$JSResult += array(
					'status' => true,
					'messageID' => $result,
					'message' => $messagePost
				);
			}
			if (mb_strpos($JSResult['message'], "ForumInitSpoiler") !== false)
			{
				$fname = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/forum.interface/templates/spoiler/script.js";
				if (file_exists($fname))
					$JSResult['message'] =
						'<script src="/bitrix/components/bitrix/forum.interface/templates/spoiler/script.js?'.filemtime($fname).'"></script>'.
							"\n".$JSResult['message'];
			}
			if (mb_strpos($JSResult['message'], "onForumImageLoad") !== false)
			{
				$SHParser = new CForumSimpleHTMLParser($APPLICATION->GetHeadStrings());
				$scripts = $SHParser->getInnerHTML('<!--LOAD_SCRIPT-->', '<!--END_LOAD_SCRIPT-->');

				if ($scripts !== "")
					$JSResult['message'] .= $scripts."\n";
			}
		}
	}
	elseif (mb_strlen($arResult["ERROR_MESSAGE"] ?? '') < 1)
	{
		$messagePreview = $FHParser->getTagHTML('div[class=forum-preview]');
		$JSResult += array(
			'status' => true,
			'previewMessage' => $messagePreview);

		if (mb_strpos($JSResult['previewMessage'], "ForumInitSpoiler") !== false)
		{
			$fname = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/forum.interface/templates/spoiler/script.js";
			if (file_exists($fname))
				$JSResult['previewMessage'] =
					'<script src="/bitrix/components/bitrix/forum.interface/templates/spoiler/script.js?'.filemtime($fname).'"></script>'.
						$JSResult['previewMessage'];
		}
		if (mb_strpos($JSResult['previewMessage'], "onForumImageLoad") !== false)
		{
			$SHParser = new CForumSimpleHTMLParser($APPLICATION->GetHeadStrings());
			$scripts = $SHParser->getInnerHTML('<!--LOAD_SCRIPT-->', '<!--END_LOAD_SCRIPT-->');

			if ($scripts !== "")
				$JSResult['previewMessage'] .= $scripts."\n";
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
	if (ob_list_handlers())
	{
		while (ob_end_clean());
	}

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
?>