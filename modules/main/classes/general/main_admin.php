<?php

class CMainAdmin
{
	public static function GetTemplateList($rel_dir)
	{
		$arrTemplate = array();
		$arrTemplateDir = array();
		$rel_dir = str_replace("\\", "/", $rel_dir);

		$path = BX_PERSONAL_ROOT."/templates/";
		$handle=@opendir($_SERVER["DOCUMENT_ROOT"].$path);
		if($handle)
		{
			while (false!==($dir_name = readdir($handle)))
			{
				if (is_dir($_SERVER["DOCUMENT_ROOT"].$path.$dir_name) && $dir_name!="." && $dir_name!="..")
					$arrTemplateDir[] = $path.$dir_name;
			}
			closedir($handle);
		}
		$arrS = explode("/", $rel_dir);
		if (is_array($arrS) && !empty($arrS))
		{
			$module_id = $arrS[0];
			$path = "/bitrix/modules/".$module_id."/install/templates/";
			if (is_dir($_SERVER["DOCUMENT_ROOT"].$path)) $arrTemplateDir[] = $path;
		}

		if (is_array($arrTemplateDir) && !empty($arrTemplateDir))
		{
			foreach($arrTemplateDir as $template_dir)
			{
				$path = $template_dir."/".$rel_dir;
				$path = str_replace("\\", "/", $path);
				$path = str_replace("//", "/", $path);
				$handle=@opendir($_SERVER["DOCUMENT_ROOT"].$path);
				if($handle)
				{
					while (false!==($file_name = readdir($handle)))
					{
						if (is_file($_SERVER["DOCUMENT_ROOT"].$path.$file_name) && $file_name!="." && $file_name!="..")
							$arrTemplate[$file_name] = $file_name;
					}
					closedir($handle);
				}
			}
		}
		$arrTemplate = array_values($arrTemplate);

		usort(
			$arrTemplate,
			function ($v1, $v2) {
				if ($v1 > $v2)
				{
					return 1;
				}
				elseif ($v1 < $v2)
				{
					return -1;
				}
				return 0;
			}
		);

		return $arrTemplate;
	}
}
