<?

function IMIncludeJSLangFile($lang_file)
{
	$pathLang = BX_ROOT.'/modules/im/lang/'.LANGUAGE_ID.'/';
	
	$mess_lang = array();

	if ($pathLang.$lang_file)
	{
		$lang_filename = $_SERVER['DOCUMENT_ROOT'].$pathLang.$lang_file;
		if (file_exists($lang_filename))
		{
			$mess_lang = __IncludeLang($lang_filename, true);
			$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript">BX.message('.CUtil::PhpToJSObject($mess_lang, false).')</script>', true);
		}
	}

	return true;	
}

?>