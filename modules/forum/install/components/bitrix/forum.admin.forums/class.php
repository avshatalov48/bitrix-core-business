<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Config;
use Bitrix\Main\ORM\Query\Result;
use \Bitrix\Vote\Base\Diag;
use Bitrix\Main\Grid\Options;

Loc::loadMessages(__FILE__);

class CForumAdminMessages extends \CBitrixComponent
{
	/** @var ErrorCollection */
	protected $errorCollection;
	/** @var \Bitrix\Forum\User */
	protected $user;

	protected $rights = "D";

	/** @var string */
	protected $gridId = "grid_admin_forum_forums";

	public function __construct($component = null)
	{
		global $APPLICATION, $USER;
		parent::__construct($component);
		$this->rights = $APPLICATION->GetGroupRight("forum");
		$this->errorCollection = new \Bitrix\Main\ErrorCollection();

		\Bitrix\Main\Loader::includeModule("forum");
		$this->user = \Bitrix\Forum\User::getById($USER->getId());
	}

	public function executeComponent()
	{
		try
		{
			if ($this->rights <= "D")
				throw new \Bitrix\Main\AccessDeniedException();

			$this->arResult["ERRORS"] = $this->processAction();

			$nav = new Bitrix\Main\UI\PageNavigation($this->gridId);
			$nav->initFromUri();

			$this->arParams["GRID_ID"] = $this->gridId;
			$this->arResult["FILTER"] = $this->initFilter();
			$this->arResult["DATA"] = [];

			$dbRes = \Bitrix\Forum\ForumTable::getList(array(
				"order" => $this->initOrder(),
				"filter" => $this->prepareFilter($this->arResult["FILTER"]),
				"limit" => $nav->getLimit(),
				"offset" => $nav->getOffset(),
				"count_total" => true,
			));
			/*?><pre><b>SQL: </b><? print_r(\Bitrix\Main\Entity\Query::getLastQuery()) ?></pre><?*/
			$nav->setRecordCount($dbRes->getCount());
			$this->arResult["NAV_OBJECT"] = $nav;
			$this->arResult["TOTAL_ROWS_COUNT"] = $dbRes->getCount();
			if ($res = $dbRes->fetch())
			{
				do
				{
					$this->arResult["DATA"][$res["ID"]] = $res + [
						"SITES" => [],
						"PERMISSIONS" => []
					];
				} while ($res = $dbRes->fetch());

				if ($dbRes = \Bitrix\Forum\ForumSiteTable::getList([
					"order" => ["FORUM_ID" => "ASC"],
					"filter" => [
						"FORUM_ID" => array_keys($this->arResult["DATA"])
					]
				]))
				{
					while ($res = $dbRes->fetch())
					{
						$this->arResult["DATA"][$res["FORUM_ID"]]["SITES"][$res["SITE_ID"]] = $res["PATH2FORUM_MESSAGES"];
					}
				}
				if ($dbRes = \Bitrix\Forum\PermissionTable::getList([
					"order" => [
						"FORUM_ID" => "ASC",
						"GROUP_ID" => "ASC"
					],
					"filter" => [
						"FORUM_ID" => array_keys($this->arResult["DATA"]),
					]
				]))
				{
					while ($res = $dbRes->fetch())
					{
						$this->arResult["DATA"][$res["FORUM_ID"]]["PERMISSIONS"][$res["GROUP_ID"]] = $res["PERMISSION"];
					}
				}
			}

			$this->includeComponentTemplate();
		}
		catch(Exception $e)
		{
			$exceptionHandling = Config\Configuration::getValue("exception_handling");
			if ($exceptionHandling["debug"])
			{
				throw $e;
			}
			else
			{
				ShowError($e->getMessage());
			}
		}
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams["SITES"] = [];
		if ($dbRes = \CSite::GetList())
		{
			while ($res = $dbRes->GetNext())
			{
				$arParams["SITES"][$res["ID"]] = "[{$res["ID"]}] " . $res["NAME"];
			}
		}
		$arParams["FORUM_GROUPS"] = [];
		$arParams["FORUM_GROUP_IDS"] = [];
		foreach (\CForumGroup::GetByLang(LANGUAGE_ID) as $res)
		{
			$arParams["FORUM_GROUP_IDS"][$res["ID"]] = str_repeat(".", ($res["DEPTH_LEVEL"]-1)).$res["NAME"];
			$arParams["FORUM_GROUPS"][$res["ID"]] = $res;
		}
		$arParams["FORUM_PERMISSIONS"] = \Bitrix\Forum\Permission::getTitledList();
		$arParams["USER_GROUPS"] = [];
		if ($dbRes = \Bitrix\Main\GroupTable::getList([
			"select" => ["ID", "NAME"],
			"cache" => ["ttl" => 3600]
		]))
		{
			while ($res = $dbRes->fetch())
			{
				$arParams["USER_GROUPS"][$res["ID"]] = $res["NAME"];
			}
		}

		return parent::onPrepareComponentParams($arParams);

	}

	/**
	 * @return array
	 */
	protected function processAction()
	{
		if (
			$this->rights <= "R" ||
			!check_bitrix_sessid() ||
			!$this->request->isPost() ||
			!\Bitrix\Main\Grid\Context::isInternalRequest() ||
			$this->request->get("grid_id") !== $this->gridId
		)
		{
			return [];
		}

		$this->errorCollection->clear();
		$this->request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

		global $DB, $APPLICATION;

		@set_time_limit(0);
		$DB->StartTransaction();
		$APPLICATION->ResetException();
		if ($this->request->getPost("action") == \Bitrix\Main\Grid\Actions::GRID_DELETE_ROW)
		{
			$forum = \Bitrix\Forum\Forum::getById($this->request->getPost("id"));
			if (!$this->user->canDeleteForum($forum))
			{
				$this->errorCollection->add([new \Bitrix\Main\Error(Loc::getMessage("FORUM_ERROR_DELETE_PERMISSION", ["#id#" => $forum->getId(), "#name#" => $forum["NAME"]]))]);
			}
			else if (!\CForumNew::Delete($forum->getId()))
			{
				$this->errorCollection->add([new \Bitrix\Main\Error(($ex = $APPLICATION->GetException()) ? $ex->GetString() : Loc::getMessage("FORUM_ERROR_DELETE_UNKNOWN", ["#id#" => $forum->getId(), "#name#" => $forum["NAME"]]))]);
			}
		}
		else if ($this->request->getPost("action_button_" . $this->gridId) === "edit")
		{
			foreach ($this->request->getPost("FIELDS") as $id => $fields)
			{
				$forum = \Bitrix\Forum\Forum::getById($id);
				if (!$this->user->canEditForum($forum))
				{
					$this->errorCollection->add([new \Bitrix\Main\Error(Loc::getMessage("FORUM_ERROR_EDIT_PERMISSION", ["#id#" => $forum->getId(), "#name#" => $forum["NAME"]]))]);
				}
				else if (!\CForumNew::Update($forum->getId(), $fields))
				{
					$this->errorCollection->add([new \Bitrix\Main\Error(($ex = $APPLICATION->GetException()) ? $ex->GetString() : Loc::getMessage("FORUM_ERROR_EDIT_UNKNOWN", ["#id#" => $forum->getId(), "#name#" => $forum["NAME"]]))]);
				}
			}
		}
		else
		{
			$ids = $this->request->getPost("rows") ?: $this->request->getPost("ID");
			$action = $this->request->getPost("action_button_" . $this->gridId);

			if ($controls = $this->request->getPost("controls"))
				$action = $controls["action_button_" . $this->gridId];

			switch ($action)
			{
				case "delete":
					foreach ($ids as $id)
					{
						$forum = \Bitrix\Forum\Forum::getById($id);
						if (!$this->user->canDeleteForum($forum))
						{
							$this->errorCollection->add([new \Bitrix\Main\Error(Loc::getMessage("FORUM_ERROR_DELETE_PERMISSION", ["#id#" => $forum->getId(), "#name#" => $forum["NAME"]]))]);
						}
						else if (!\CForumNew::Delete($forum->getId()))
						{
							$this->errorCollection->add([new \Bitrix\Main\Error(($ex = $APPLICATION->GetException()) ? $ex->GetString() : Loc::getMessage("FORUM_ERROR_DELETE_UNKNOWN", ["#id#" => $forum->getId(), "#name#" => $forum["NAME"]]))]);
						}
					}
					break;
				case "activate":
				case "deactivate":
					foreach ($ids as $id)
					{
						$forum = \Bitrix\Forum\Forum::getById($id);
						if (!$this->user->canEditForum($forum))
						{
							$this->errorCollection->add([new \Bitrix\Main\Error(Loc::getMessage("FORUM_ERROR_EDIT_PERMISSION", ["#id#" => $forum->getId(), "#name#" => $forum["NAME"]]))]);
						}
						else if (!\CForumNew::Update($forum->getId(), ["ACTIVE" => $action == "deactivate" ?  "N" : "Y"]))
						{
							$this->errorCollection->add([new \Bitrix\Main\Error(($ex = $APPLICATION->GetException()) ? $ex->GetString() : Loc::getMessage("FORUM_ERROR_EDIT_UNKNOWN", ["#id#" => $forum->getId(), "#name#" => $forum["NAME"]]))]);
							$APPLICATION->ResetException();
						}
					}
					break;
				case "clear_html":
					foreach ($ids as $id)
					{
						\CForumNew::ClearHTML($id);
					}
					break;
			}
		}
		$DB->Commit();
		$errors = array();
		if (!$this->errorCollection->isEmpty())
		{
			/** @var $error Error */
			foreach($this->errorCollection->toArray() as $error)
			{
				$errors[] = array(
					"TYPE" => \Bitrix\Main\Grid\MessageType::ERROR,
					"TEXT" => Loc::getMessage("FORUM_PROCESS_ERRORS").$error->getMessage(),
					"TITLE" => Loc::getMessage("FORUM_PROCESS_ERRORS_TITLE")
				);
			}
		}
		return $errors;
	}

	protected function initFilter()
	{
		$this->arResult["FILTER_FIELDS"] = [
			[
				"id" => "ID",
				"name" => "ID",
				"type" => "number",
				"filterable" => "",
				"default" => true
			],
			[
				"id" => "ACTIVE",
				"name" => Loc::getMessage("FORUM_FILTER_ACTIVE"),
				"type" => "list",
				"items" => ["Y" => GetMessage("admin_lib_list_yes"), "N" => GetMessage("admin_lib_list_no")],
				"filterable" => ""
			],
			[
				"id" => "SITE.SITE_ID",
				"name" => Loc::getMessage("FORUM_FILTER_SITE"),
				"filterable" => "",
				"type" => "list",
				"items" => $this->arParams["SITES"]
			],
			[
				"id" => "FORUM_GROUP_ID",
				"name" => Loc::getMessage("FORUM_FILTER_FORUM_GROUP_ID"),
				"type" => "list",
				"items" => $this->arParams["FORUM_GROUP_IDS"],
				"filterable" => ""
			],
			[
				"id" => "CAN_READ",
				"name" => Loc::getMessage("FORUM_FILTER_CAN_READ", ["#permission#" => \Bitrix\Forum\Permission::CAN_READ]),
				"type" => "list",
				"items" => $this->arParams["USER_GROUPS"],
				"filterable" => ""
			],
		];
		$this->arResult["FILTER_ID"] = $this->gridId."_filter";
		return (new \Bitrix\Main\UI\Filter\Options($this->arResult["FILTER_ID"]))->getFilterLogic($this->arResult["FILTER_FIELDS"]);
	}

	protected function prepareFilter($filter)
	{
		$result = $filter;
		if (is_array($filter))
		{
			if (array_key_exists("CAN_READ", $filter))
			{
				unset($result["CAN_READ"]);
				$result[] = [
					"PERMISSION.GROUP_ID" => [2, $filter["CAN_READ"]],
					">=PERMISSION.PERMISSION" => \Bitrix\Forum\Permission::CAN_READ
				];
			}
			if (
				array_key_exists("FORUM_GROUP_ID", $filter) &&
				array_key_exists($filter["FORUM_GROUP_ID"], $this->arParams["FORUM_GROUPS"]) &&
				($group = $this->arParams["FORUM_GROUPS"][$filter["FORUM_GROUP_ID"]])
			)
			{
				unset($result["FORUM_GROUP_ID"]);

				$result[] = [
					">=GROUP.LEFT_MARGIN" => $group["LEFT_MARGIN"],
					"<=GROUP.RIGHT_MARGIN" => $group["RIGHT_MARGIN"]
				];
			}
		}
		return $result;
	}

	protected function initOrder()
	{
		$order = (new Bitrix\Main\Grid\Options($this->gridId))->GetSorting(["sort" => ["ID" => "ASC"]]);
		$order = $order["sort"];
		if (!is_array($order) ||
			!empty(array_diff($order, ["DESC", "ASC", "desc", "asc"]))
		)
		{
			$order = ["ID" => "ASC"];
		}
		return $order;
	}
}