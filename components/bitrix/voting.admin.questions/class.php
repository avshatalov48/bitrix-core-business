<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Config;
use \Bitrix\Vote\Base\Diag;
use Bitrix\Main\Grid\Options;

Loc::loadMessages(__FILE__);

class CVoteAdminQuestions extends \CBitrixComponent
{
	/**@var CAdminSorting */
	public $sort;
	/** @var CAdminList */
	public $list;
	/** @var ErrorCollection */
	protected $errorCollection;
	
	protected $rights = "D";

	/** @var string */
	protected $gridId = 'grid_vote_questions';

	/** @var $vote \Bitrix\Vote\Vote */
	protected $vote;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->rights = $this->getApplication()->GetGroupRight("vote");
		$this->errorCollection = new \Bitrix\Main\ErrorCollection();
	}

	public function executeComponent()
	{
		try
		{
			\Bitrix\Main\Loader::includeModule("vote");

			if ($this->rights <= "D")
				throw new \Bitrix\Main\AccessDeniedException();

			$this->prepareParams();

			if (!$this->vote->canEdit($this->getCurrentUser()->GetID()))
				throw new \Bitrix\Main\ArgumentException(GetMessage("ACCESS_DENIED"), "Access denied.");

			$this->processAction();
			$this->arParams["GRID_ID"] = $this->gridId;
			$filter = ($this->arParams["SHOW_FILTER"] == "Y" ? $this->initFilter() : []);
			$order = (new Bitrix\Main\Grid\Options($this->gridId))->GetSorting(["sort" => ["ID" => "ASC"]]);
			$order = is_array($order["sort"]) ? $order["sort"] : [];
			$order = array_intersect($order, ["DESC", "ASC", "desc", "asc"]);
			$this->arResult["DATA"] = \Bitrix\Vote\QuestionTable::getList(array(
				"order" => (empty($order) ? ["ID" => "ASC"] : $order),
				"filter" => ["VOTE_ID" => $this->vote->getId()] + $filter
			));
			$this->arResult["FILTER"] = $filter;

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
	protected function prepareParams()
	{
		$this->vote = \Bitrix\Vote\Vote::loadFromId($this->arParams["VOTE_ID"]);
	}

	protected function processAction()
	{
		$request = $this->request;
		$this->errorCollection->clear();
		if ($request->isPost() &&
			check_bitrix_sessid() &&
			\Bitrix\Main\Grid\Context::isInternalRequest() &&
			$request->get("grid_id") == $this->gridId)
		{
			$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

			if ($request->getPost("action") == \Bitrix\Main\Grid\Actions::GRID_DELETE_ROW)
			{
				$this->deleteQuestion($request->getPost("id"));
			}
			else if ($request->getPost("action_button_" . $this->gridId) === 'edit')
			{
				$rawFiles = [];
				\CFile::ConvertFilesToPost(($request->getFile("FIELDS") ?: []), $rawFiles);
				foreach ($request->getPost("FIELDS") as $id => $fields)
				{
					$this->updateQuestion($id, $fields, $rawFiles[$id]);
				}
			}
			else
			{
				$ids = $request->getPost("rows") ?: $request->getPost("ID");
				$action = $request->getPost("action_button_" . $this->gridId);
				if ($controls = $request->getPost("controls"))
					$action = $controls["action_button_" . $this->gridId];

				switch ($action)
				{
					case 'delete':
						foreach ($ids as $id)
							$this->deleteQuestion($id);
						break;
					case 'activate':
						foreach ($ids as $id)
							$this->activateQuestion($id, true);
						break;
					case 'deactivate':
						foreach ($ids as $id)
							$this->activateQuestion($id, false);
						break;
				}
			}
		}
		$errors = array();
		if (!$this->errorCollection->isEmpty())
		{
			/** @var $error Error */
			foreach($this->errorCollection->toArray() as $error)
			{
				$errors[] = array(
					"TYPE" => \Bitrix\Main\Grid\MessageType::ERROR,
					"TEXT" => Loc::getMessage("VOTE_GRID_ERROR_TITLE").$error->getMessage(),
					"TITLE" => Loc::getMessage("VOTE_GRID_ERROR_HEAD")
				);
			}
		}
		return $errors;
	}

	protected function updateQuestion($id, $data, $files = null)
	{
		if ($question = $this->vote->getQuestion($id))
		{
			$this->getApplication()->ResetException();
			$imageFile = is_array($files) && array_key_exists("IMAGE_ID", $files) ? $files["IMAGE_ID"] : null;
			if (is_array($imageFile))
			{
				$data["IMAGE_ID"] = $imageFile;
				if ($question["IMAGE_ID"] > 0)
				{
					$data["IMAGE_ID"] += [
						"old_file" => $question["IMAGE_ID"],
						"del" => "N"
					];
				}
			}
			else if ($data["IMAGE_ID"] === "null" && $question["IMAGE_ID"] > 0)
			{
				$data["IMAGE_ID"] = [
					"old_file" => $question["IMAGE_ID"],
					"del" => "Y"
				];
			}
			else
			{
				unset($data["IMAGE_ID"]);
			}

			if (\CVoteQuestion::Update($id, $data))
				return true;
			$res = $this->getApplication()->GetException();
			$this->errorCollection->add([new Bitrix\Main\Error(Loc::getMessage("SAVE_ERROR") . $id . ($res ? ": ".$res->GetString() : ""), $id)]);
		}
		return false;
	}

	protected function deleteQuestion($id)
	{
		if (($question = $this->vote->getQuestion($id)) && \CVoteQuestion::Delete($id))
			return true;
		$this->errorCollection->add([new Bitrix\Main\Error(Loc::getMessage("DELETE_ERROR") . $id, $id)]);
		return false;
	}

	protected function activateQuestion(int $id, bool $activate)
	{
		if (($question = $this->vote->getQuestion($id)) && \CVoteQuestion::setActive($id, $activate))
			return true;
		$this->errorCollection->add([new Bitrix\Main\Error(Loc::getMessage("SAVE_ERROR") . $id, $id)]);
		return false;
	}
	/**
	 * @return Application|\Bitrix\Main\HttpApplication|\CAllMain|\CMain
	 */
	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	/**
	 * @return array|bool|\CAllUser|\CUser
	 */
	protected function getCurrentUser()
	{
		global $USER;
		return $USER;
	}

	protected function initFilter()
	{
		$this->arResult["FILTER_FIELDS"] = [
			[
				"id" => "QUESTION",
				"name" => GetMessage("VOTE_QUESTION"),
				"filterable" => "",
				"quickSearch" => "",
				"default" => true
			],
			[
				"id" => "ID",
				"name" => "ID",
				"type" => "number",
				"filterable" => "",
				"default" => true
			],
			[
				"id" => "ACTIVE",
				"name" => GetMessage("VOTE_ACTIVE"),
				"type" => "list",
				"items" => ["Y" => GetMessage("VOTE_YES"), "N" => GetMessage("VOTE_NO")],
				"filterable" => ""
			],
			[
				"id" => "DIAGRAM",
				"name" => GetMessage("VOTE_DIAGRAM"),
				"type" => "list",
				"items" => ["Y" => GetMessage("VOTE_YES"), "N" => GetMessage("VOTE_NO")],
				"filterable" => ""
			],
			[
				"id" => "REQUIRED",
				"name" => GetMessage("VOTE_REQUIRED"),
				"type" => "list",
				"items" => ["Y" => GetMessage("VOTE_YES"), "N" => GetMessage("VOTE_NO")],
				"filterable" => ""
			],
			[
				"id" => "TIMESTAMP_X",
				"name" => GetMessage("VOTE_TIMESTAMP_X"),
				"type" => "date",
				"filterable" => ""
			],
		];
		$this->arResult["FILTER_ID"] = $this->gridId."_filter";
		return (new \Bitrix\Main\UI\Filter\Options($this->arResult["FILTER_ID"]))->getFilterLogic($this->arResult["FILTER_FIELDS"]);
	}
}