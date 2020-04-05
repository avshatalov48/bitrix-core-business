<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if (!function_exists("generateEventListUserSelector"))
{
	function generateEventListUserSelector($popupId, $searchInputId, $dataInputId, $componentName, $value, $siteId = '', $nameFormat = '', $delay = 0)
	{
		$popupId = strval($popupId);
		$searchInputId = strval($searchInputId);
		$dataInputId = strval($dataInputId);
		$componentName = strval($componentName);

		$siteId = strval($siteId);
		if($siteId === '')
		{
			$siteId = SITE_ID;
		}

		$nameFormat = strval($nameFormat);
		if($nameFormat === '')
		{
			$nameFormat = \CSite::GetNameFormat(false);
		}

		$delay = intval($delay);
		if($delay < 0)
		{
			$delay = 0;
		}

		$value = intval($value);
		$userName = '';
		if($value > 0)
		{
			$dbResUser = \CUser::GetByID($value);
			$user = $dbResUser->Fetch();
			if(is_array($user))
			{
				$userName = \CUser::FormatName($nameFormat, $user, true, false);
			}
		}

		$result = "";

		ob_start();
		$GLOBALS['APPLICATION']->IncludeComponent(
			'bitrix:intranet.user.selector.new',
			'',
			array(
				'MULTIPLE' => 'N',
				'NAME' => $componentName,
				'INPUT_NAME' => $searchInputId,
				'SHOW_EXTRANET_USERS' => 'NONE',
				'POPUP' => 'Y',
				'SITE_ID' => $siteId,
				'NAME_TEMPLATE' => $nameFormat
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
		$result .= ob_get_clean();

		$result.= '
			<input type="text" id="'.htmlspecialcharsbx($searchInputId).'" name="'.htmlspecialcharsbx($searchInputId).'" style="width:200px;">
			<input type="hidden" id="'.htmlspecialcharsbx($dataInputId).'" name="'.htmlspecialcharsbx($dataInputId).'" value="">
			<del id="flt_created_by_delete" style="display: '.($value>0 ? 'inline-block' : 'none').'" class="event-list-delete-button">&#215;</del>
			<script>
				BX.ready(function(){
					BX.Main.EventListUserSelector.deletePopup("'.$popupId.'");
					BX.Main.EventListUserSelector.create(
						"'.$popupId.'", 
						{ 
							searchInput: BX("'.\CUtil::JSEscape($searchInputId).'"), 
							dataInput: BX("'.\CUtil::JSEscape($dataInputId).'"), 
							componentName: "'.\CUtil::JSEscape($componentName).'",
							user: '.($value > 0 ? '{id: '.$value.', name: "'.\CUtil::JSEscape($userName).'"}' : '{}').' 
						}, 
						'.$delay.'
					);
				});
			</script>
		';
		return $result;
	}
}

$arResult["FILTER"] = array(
	"flt_created_by" => array(
		"id" => "flt_created_by_id",
		"name" => Loc::getMessage("EVENT_LIST_USER_FIELD"),
		"type" => "custom",
		"default" => true
	),
	"flt_date" => array(
		"id" => "flt_date",
		"name" => Loc::getMessage("EVENT_LIST_DATE_FIELD"),
		"type" => "date",
		"default" => true,
	),
	"flt_ip" => array(
		"id" => "flt_ip",
		"name" => Loc::getMessage("EVENT_LIST_IP_FIELD"),
		"type" => "text",
		"default" => true,
	)
);

$grid = new CGridOptions($arResult["GRID_ID"]);
$filterValues = $grid->GetFilter($arResult["FILTER"]);

$currentUserId = isset($filterValues['flt_created_by_id']) ? (int)$filterValues['flt_created_by_id'] : 0;

$arResult["FILTER"]["flt_created_by"]["value"] = generateEventListUserSelector("EVENT_LIST_USER_SELECT", "flt_created_by_name", "flt_created_by_id", "EVENT_LIST_SELECT_COMPONENT", $currentUserId);
?>