<?
session_cache_limiter("public");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$return304 = true;

function GetFileContent($path)
{
	clearstatcache();
	if(!file_exists($path) || !is_file($path))
		return false;
	if(filesize($path) <= 0)
		return "";
	$fd = fopen($path, "rb");
	$contents = fread($fd, filesize($path));
	fclose ($fd);
	return $contents;
}

$arr = Array(
		'common.js',
		'editor.js',
		'parser.js',
		'editor_php.js',
		'controls.js',
		'bars.js',
		'toolbarbuttons.js',
		'table_operations.js'
	);

if (isset($_GET['s']) && $_GET['s'] <> '')
{
	$s = $_GET['s'];
	if (mb_strpos($s, 'em') !== false)
		$arr[] = "bars_ex.js";
	if (mb_strpos($s, 'c2') !== false)
		$arr[] = "components2.js";
	if (mb_strpos($s, 's') !== false)
		$arr[] = "snippets.js";
}

$files_mod_str = 'bx_';
$l = count($arr);
for($i = 0; $i < $l; $i++)
	@$files_mod_str .= filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/htmleditor2/'.$arr[$i]);

$files_mod_hash = md5($files_mod_str);

if (!isset($_SERVER['HTTP_IF_NONE_MATCH']) || $_SERVER['HTTP_IF_NONE_MATCH'] != '"'.$files_mod_hash.'"')
{
	header("Pragma: private");
	header("Cache-Control: public, max-age=2592000"); // 30 days
	header('ETag: "'.$files_mod_hash.'"');
	header("Content-Type: application/x-javascript; charset=".LANG_CHARSET);

	for($i = 0; $i < count($arr); $i++)
	{
		$script_filename = $arr[$i];
		$script_content = GetFileContent($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/htmleditor2/'.$script_filename);
		$script_content = preg_replace("/\t/", '', $script_content);
		//$script_content = preg_replace("/(.*)\/\/.*/", "\$1", $script_content);
		$script_content = preg_replace("/\r\n/", "\n", $script_content);

		echo "\n/*:::: $script_filename ::::*/\n";
		echo $script_content;
	}
}
else
{
	CHTTP::SetStatus("304 Not Modified");
	header("Pragma: private");
	header("Cache-Control: public, max-age=2592000"); // 30 days
	header('ETag: "'.$files_mod_hash.'"');
	header("Content-Type: application/x-javascript; charset=".LANG_CHARSET);

	while(ob_get_level()) ob_end_clean();
	exit;
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>