<?

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main\Grid\Actions;
use Bitrix\Main\Web;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$response = new \Bitrix\Main\HttpResponse(\Bitrix\Main\Application::getInstance()->getContext());
$response->addHeader("Content-Type", "application/json");

global $USER;

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new Web\PostDecodeFilter);

if (!$request->isAjaxRequest())
{
	$response->flush(Web\Json::encode(array(
		"error" => "Request is not XHR"
	)));

	die();
}

if (!$request->isPost())
{
	$response->flush(Web\Json::encode(array(
		"error" => "Request is not POST"
	)));

	die();
}


$options = new \Bitrix\Main\Grid\Options($request->get("GRID_ID"));
$error = false;

if ($request->get("action") === Actions::GRID_SAVE_BATH)
{
	$data = $request->getPost("bath");
}
else
{
	$data = array($request);
}

foreach ($data as $key => $item)
{
	switch ($item["action"])
	{
		case Actions::GRID_SET_EXPANDED_ROWS:
			$options->setExpandedRows($item["ids"]);
			break;

		case Actions::GRID_SET_COLLAPSED_GROUPS:
			$options->setCollapsedGroups($item["ids"]);
			break;

		case Actions::GRID_RESET:
			if ($USER->canDoOperation("edit_other_settings"))
			{
				$options->resetView("default");
			}
			else
			{
				$options->deleteView("default");
			}

			if ($item["set_default_settings"] === "Y" &&
				$USER->canDoOperation("edit_other_settings"))
			{
				$viewSettings = $options->getOptions();

				$options->setDefaultView($viewSettings["views"]["default"]);

				if ($item["delete_user_settings"] === "Y")
				{
					$options->resetDefaultView();
				}
			}

			break;

		case Actions::GRID_SET_COLUMNS:
			$options->setColumns($item["columns"]);
			break;

		case Actions::GRID_SET_THEME:
			$options->setTheme($item["theme"]);
			break;

		case Actions::GRID_SAVE_SETTINGS:
			$options->setViewSettings($item["view_id"], $options->getCurrentOptions());

			if ($item["set_default_settings"] === "Y" &&
				$USER->canDoOperation("edit_other_settings"))
			{
				$options->setDefaultView($options->getCurrentOptions());

				if ($item["delete_user_settings"] === "Y")
				{
					$options->resetDefaultView();
				}
			}
			break;

		case Actions::SET_CUSTOM_NAMES:
			$options->setCustomNames($item["custom_names"]);
			break;

		case Actions::GRID_DELETE_VIEW:
			$options->deleteView($item["view_id"]);
			break;

		case Actions::GRID_SET_VIEW:
			$options->setView($item["view_id"]);
			break;

		case Actions::GRID_SET_SORT:
			$options->setSorting($item["by"], $item["order"]);
			break;

		case Actions::GRID_SET_COLUMN_SIZES:
			$options->setColumnsSizes($item["expand"], $item['sizes']);
			break;

		case Actions::GRID_SET_PAGE_SIZE:
			$options->setPageSize($item['pageSize']);
			break;

		case Actions::GRID_SET_STICKED_COLUMNS:
			$options->setStickedColumns($item['stickedColumns']);
			break;

		default:
			$error = true;
	}
}



if (!$error)
{
	$options->save();
	$response->flush(Web\Json::encode($options->GetOptions()));
}
else
{
	$response->flush(Web\Json::encode(array(
		"error" => "Unknown action",
		"action" => $request->get("action")
	)));
}