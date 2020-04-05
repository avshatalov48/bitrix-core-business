<?
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define("PUBLIC_AJAX_MODE", true);
define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main,
	Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!Loader::includeModule('catalog') || !Loader::includeModule('fileman'))
	die();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/admin_tools.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

CUtil::JSPostUnescape();
if (check_bitrix_sessid())
{
	if (isset($_POST['GET_INPUT']) && $_POST['GET_INPUT'] == 'Y')
	{
		/** @global CMain $APPLICATION */
		$APPLICATION->RestartBuffer();
		if($_POST['PROPERTY_ID'] == "DETAIL" || $_POST['PROPERTY_ID'] == "ANNOUNCE")
		{
			echo CFileInput::Show('PROP['.$_POST['PROPERTY_ID'].']['.$_POST['ROW_ID'].']', array(), array(
					"IMAGE" => "Y",
					"PATH" => "Y",
					"FILE_SIZE" => "Y",
					"DIMENSIONS" => "Y",
					"IMAGE_POPUP" => "Y",
					"MAX_SIZE" => array(
						"W" => (int)Main\Config\Option::get('iblock', 'detail_image_size'),
						"H" => (int)Main\Config\Option::get('iblock', 'detail_image_size'),
					),
				), array(
					'upload' => true,
					'medialib' => true,
					'file_dialog' => true,
					'cloud' => true,
					'del' => true,
					'description' => false,
				));
		}
		else
		{
			$properties = CIBlockProperty::GetList(
				array("SORT" => "ASC", "NAME" => "ASC"),
				array("ID" => $_POST["PROPERTY_ID"], "ACTIVE" => "Y", "CHECK_PERMISSIONS" => "N")
			);
			if ($prop_fields = $properties->Fetch())
			{
				$prop_fields["VALUE"] = array();
				$prop_fields["~VALUE"] = array();
				_ShowPropertyField('PROP[' . $prop_fields["ID"] . '][' . $_POST['ROW_ID'] . ']', $prop_fields, $prop_fields["VALUE"], false, false, 50000, 'iblock_generator_form');
			}
		}
	}
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");