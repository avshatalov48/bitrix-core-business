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
	if (
		(isset($_POST['GET_INPUT']) && $_POST['GET_INPUT'] == 'Y')
		&& (isset($_POST['PROPERTY_ID']) && is_string($_POST['PROPERTY_ID']))
	)
	{
		$fieldId = trim($_POST['PROPERTY_ID']);
		if ($fieldId !== '')
		{
			/** @global CMain $APPLICATION */
			$APPLICATION->RestartBuffer();
			if ($fieldId == "DETAIL" || $fieldId == "ANNOUNCE")
			{
				echo \Bitrix\Main\UI\FileInput::createInstance(array(
					"name" => 'PROP['.$fieldId.']['.$_POST['ROW_ID'].']',
					"description" => false,
					"upload" => true,
					"allowUpload" => "I",
					"medialib" => true,
					"fileDialog" => true,
					"cloud" => true,
					"delete" => true,
					"maxCount" => 1
				))->show([]);
			}
			else
			{
				$fieldId = (int)$fieldId;
				if ($fieldId > 0)
				{
					$properties = CIBlockProperty::GetList(
						array("SORT" => "ASC", "NAME" => "ASC"),
						array("ID" => $fieldId, "ACTIVE" => "Y", "CHECK_PERMISSIONS" => "N")
					);
					if ($prop_fields = $properties->Fetch())
					{
						$prop_fields["VALUE"] = array();
						$prop_fields["~VALUE"] = array();
						_ShowPropertyField(
							'PROP['.$prop_fields["ID"].']['.$_POST['ROW_ID'].']',
							$prop_fields,
							$prop_fields["VALUE"],
							false,
							false,
							50000,
							'iblock_generator_form'
						);
					}
				}
			}
		}
	}
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");