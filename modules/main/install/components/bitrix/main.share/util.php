<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!function_exists("__bx_share_get_handlers"))
{
	function __bx_share_get_handlers($template = false, $siteTemplate = false)
	{
		if (trim($template) == ".default")
			$template = "";
	
		$arBookmarkHandlerDropdown = array();
		$arBookmarkHandlerDropdownDefault = array();
	
		$shareComponent = new CBitrixComponent;
		$shareComponent->InitComponent("bitrix:main.share", $template);
		$shareComponent->InitComponentTemplate("", $siteTemplate);

		if (strlen($shareComponent->__template->__folder) > 0)
		{
			$path2Handlers = $_SERVER["DOCUMENT_ROOT"]."/".$shareComponent->__template->__folder."/handlers/";
			CheckDirPath($path2Handlers);

			$arHandlers = array();
			if ($handle = opendir($path2Handlers))
			{
				while (($file = readdir($handle)) !== false)
				{
					if ($file == "." || $file == "..")
						continue;
					if (is_file($path2Handlers.$file) && strtoupper(substr($file, strlen($file)-4))==".PHP")
					{
						$name = $title = $icon_url_template = "";
						$sort = 0;
						include($path2Handlers.$file);
						
						if (strlen($name) > 0)
						{
							$arHandlers[$name] = array(
								"TITLE" => $title,
								"ICON" => $icon_url_template,
								"SORT" => intval($sort)	
							);
						}
					}
				}
			}

			foreach($arHandlers as $name=>$arSystem)
				if (strlen($arSystem["TITLE"]) > 0)
					$arBookmarkHandlerDropdown[$name] = $arSystem["TITLE"];

			$arBookmarkHandlerDropdownTmp = $arBookmarkHandlerDropdown;
			if (LANGUAGE_ID != 'ru')
			{
				if (array_key_exists("vk", $arBookmarkHandlerDropdownTmp))
					unset($arBookmarkHandlerDropdownTmp["vk"]);
				if (array_key_exists("mailru", $arBookmarkHandlerDropdownTmp))
					unset($arBookmarkHandlerDropdownTmp["mailru"]);
			}
			$arBookmarkHandlerDropdownDefault = array_keys($arBookmarkHandlerDropdownTmp);
		}

		return array(
			"HANDLERS" => $arBookmarkHandlerDropdown,
			"HANDLERS_DEFAULT" => $arBookmarkHandlerDropdownDefault
		);
	}
}
?>