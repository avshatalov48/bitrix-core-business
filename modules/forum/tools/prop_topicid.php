<?php
use \Bitrix\Forum;
use \Bitrix\Main;

IncludeModuleLangFile(__FILE__);

class CIBlockPropertyTopicID
{
	public static function GetUserTypeDescription()
	{
		return [
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => 'TopicID',
			'DESCRIPTION' => GetMessage('IBLOCK_PROP_TOPICID_DESC'),
			'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
			'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
			//optional handlers
			'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
			'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
			'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
		];
	}

	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = ['HIDE' => ['SEARCHABLE', 'WITH_DESCRIPTION', 'ROW_COUNT', 'COL_COUNT']];
		return '';
	}

	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		if (CModule::IncludeModule('forum'))
		{
			$topicId = intval($value['VALUE']);
			$topicTitle = null;
			if ($topicId > 0)
			{
				$value['VALUE'] = $topicId;

				if ($topic = Forum\TopicTable::getById($topicId)->fetch())
				{
					$topicTitle = $topic['TITLE'];
				}
			}
			return self::getHTMLToFindTopic(
				$strHTMLControlName['VALUE'],
				$topicId,
				$topicTitle
			);
		}
		return '';
	}

	private static function getHTMLToFindTopic(
		$inputName,
		$topicId,
		$topicTitle = "")
	{
		static $number = 0;
		$number++;

		$topicId = intval($topicId);
		$inputName = htmlspecialcharsbx($inputName);
		$topicTitle = htmlspecialcharsbx($topicTitle);
		$prefix = "findForumTopic{$number}";

		$message = [
			'wait' => GetMessageJS("MAIN_WAIT"),
			'notFound' => GetMessageJS('MAIN_NOT_FOUND'),
		];

		global $APPLICATION;
		if ($APPLICATION->GetGroupRight("forum") >= "R")
		{
			$strReturn = <<<HTML
<input type="text" name="{$inputName}" value="{$topicId}" size="3" id="{$prefix}Value">
<iframe style="width:0; height:0; border: 0" src="javascript:void(0)" name="{$prefix}Frame" id="{$prefix}Frame"></iframe>
<input type="button" id="{$prefix}Button" value="...">
<span id="{$prefix}Title"></span>
<script>
	BX.ready(function(){
		BX.bind(BX("{$prefix}Button"), 'click', function () {
			window.open(
				'/bitrix/admin/forum_topics_search.php?' + BX.util.buildQueryString({
					lang: BX.message('LANGUAGE_ID'),
					nodeId: "{$prefix}Value"
				}), 
				'', 
				'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5)
			);
		});

		var previousValue = 0;
		var changeManualy = function() {
			var valueNode = BX("{$prefix}Value");
			var value = parseInt(valueNode.value);
			if (value !== previousValue)
			{
				if (value > 0)
				{
					previousValue = value;
					changeTitle("<i>{$message['wait']}</i>");
					BX.ajax.runAction('bitrix:forum.topic.head', 
						{data: {topicId: value}
					}).then(function(result) {
						if (result.status === "success")
						{
							return changeTitle(result.data);
						}
						throw new Error(result.errors[0].message);
					}).catch(function(error) {
						changeTitle('<b>' + error.message + '</b>');
					});
				}
				else
				{
					changeTitle('');
				}
			}
		};
		BX.bind(BX("{$prefix}Value"), "change", changeManualy);
		function changeTitle(data) {
			var titleNode = BX("{$prefix}Title");
			if (data === null || ((data['ID'] > 0 && data['TITLE'].length <= 0)))
			{
				titleNode.innerHTML = "{$message["notFound"]}";
			}
			else if (BX.type.isString(data))
			{
				titleNode.innerHTML = data;
			}
			else if (data['ID'] > 0 && data['TITLE'].length > 0)
			{
				titleNode.innerHTML = [
					'[<a class="tablebodylink" href="/bitrix/admin/forum_topics.php?lang=', BX.message('LANGUAGE_ID'), '">',
						BX.util.htmlspecialchars(data['ID']),
					'</a>]',
					BX.util.htmlspecialchars(data['TITLE'])
				].join('');
			}
			else
			{
				titleNode.innerHTML = '';
			}
		}
		changeTitle({ID: {$topicId}, TITLE: "{$topicTitle}"});
	});
</script>
HTML;

		}
		else
		{
			$strReturn = <<<HTML
<input type="text" value="{$topicId}" size="3">
HTML;
		}
		return $strReturn;
	}

	public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		static $cache = array();
		if (CModule::IncludeModule('forum'))
		{
			$value['VALUE'] = intval($value['VALUE']);
			if ($value['VALUE'] <= 0) {
				$value['VALUE'] = '';
				$res = '';
			} else {
				if (!array_key_exists($value['VALUE'], $cache))
					$cache[$value['VALUE']] = CForumTopic::GetByID($value['VALUE']);
				$arTopic = $cache[$value['VALUE']];
				$res = (!empty($arTopic) ? '['.$value['VALUE'].'] ('.htmlspecialcharsbx($arTopic['TITLE']).')' : $value['VALUE']);
			}
			return $res;
		}
		return '';
	}

	public static function ConvertToDB($arProperty, $value)
	{
		if ($value['VALUE'] <> '')
		{
			$value['VALUE'] = intval($value['VALUE']);
		}
		return $value;
	}

	public static function ConvertFromDB($arProperty, $value)
	{
		if ($value['VALUE'] <> '')
		{
			$value['VALUE'] = intval($value['VALUE']);
		}
		return $value;
	}
}
