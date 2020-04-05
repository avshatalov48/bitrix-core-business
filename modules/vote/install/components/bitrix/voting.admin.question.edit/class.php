<?
namespace
{
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	use \Bitrix\Main\Localization\Loc;
	use \Bitrix\Main\Application;
	use \Bitrix\Main\Web\Json;
	use \Bitrix\Main\Error;
	use \Bitrix\Main\ErrorCollection;
	use \Bitrix\Main\Config;
	use \Bitrix\Vote\Base\Diag;
	use \Bitrix\Main\ArgumentException;

	class CVoteAdminQuestionEdit extends \CBitrixComponent
	{
		/** @var int */
		static protected $questionNumber = 0;
		/** @var \Bitrix\Vote\Vote */
		protected $vote;
		/** @var int */
		protected $voteId;
		/** @var int */
		protected $questionId;
		/** @var array */
		protected $question = [];
		/** @var  ErrorCollection */
		protected $errorCollection;
		/** @var array */
		protected $answers = [];
		/**@var \Bitrix\Vote\Component\VoteQuestionEditGrid*/
		protected $answerGrid;

		public function __construct($component = null)
		{
			parent::__construct($component);
			$this->errorCollection = new \Bitrix\Main\ErrorCollection();
		}

		public function executeComponent()
		{
			try
			{
				if (!\Bitrix\Main\Loader::includeModule("vote"))
				{
					return;
				}
				$this->prepareParams();
				$gridInstanceId = $this->request->getQuery("gridInstanceId");
				if ($gridInstanceId === null)
				{
					$gridInstanceId = implode("_", array(
						"voteId" => $this->voteId,
						"questionId" => $this->questionId ? $this->questionId : ($this->request->getQuery("COPY_ID") ? "c".$this->request->getQuery("COPY_ID") : 0), //(0|>0)
						"userId" => $this->getUser()->GetId()?:randString(6)
					));
				}
				$this->answerGrid = new \Bitrix\Vote\Component\VoteQuestionEditGrid($gridInstanceId);
				$this->arParams["ANSWER_PARAMS"] = array(
					"GRID_ID" => $this->answerGrid->getGridId(),
					"INSTANCE_ID" => $this->answerGrid->getGridInstanceId(),
					"MESSAGES" => array()
				);

				$this->arResult["ERROR"] = array();

				$this->errorCollection->clear();

				$this->answerGrid->restore();
				if (!$this->processAction())
				{
					$this->arParams["ANSWER_PARAMS"]["MESSAGES"] = $this->answerGrid->processAction();
				}
				else if ($this->errorCollection->isEmpty())
				{
					$this->answerGrid->clear();
					$url = "/bitrix/admin/vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID={$this->voteId}";
					if ($this->request->getPost("apply") !== null)
					{
						$url = (new \Bitrix\Main\Web\Uri($this->request->getRequestUri()))
							->deleteParams(array("VOTE_ID", "COPY_ID", "ID"))
							->addParams(array("VOTE_ID"=> $this->voteId, "ID" => $this->questionId))
							->getLocator();
					}
					LocalRedirect($url);
				}

				/** @var $error Error*/
				foreach ($this->errorCollection->toArray() as $error)
					$this->arResult["ERROR"][] = $error->getMessage();

				$this->answerGrid->merge($this->answers);
				$this->answerGrid->prepare();

				$this->adjustGrid($this->answerGrid->get());
				$this->arResult["QUESTION"] = $this->question;
				$this->arResult["QUESTION_ID"] = $this->questionId;
				$this->arResult["VOTE_ID"] = $this->voteId;
				$this->arResult["VOTE"] = $this->vote;

				$this->includeComponentTemplate();
			}
			catch (Exception $e)
			{
				$exceptionHandling = Config\Configuration::getValue("exception_handling");
				if ($exceptionHandling["debug"])
				{
					throw $e;
				}
			}
		}

		/**
		 * Returns whether this is an AJAX (XMLHttpRequest) request.
		 * @return boolean
		 */
		protected function isAjaxRequest()
		{
			return $this->request->isAjaxRequest();
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
		protected function getUser()
		{
			global $USER;
			return $USER;
		}

		protected function prepareParams()
		{
			$this->voteId = intval($this->arParams["VOTE_ID"]);
			$this->questionId = intval($this->arParams["QUESTION_ID"]);

			$this->vote = \Bitrix\Vote\Vote::loadFromId($this->voteId);
			if (!$this->vote->canEdit($this->getUser()->GetID()))
				throw new \Bitrix\Main\ArgumentException(GetMessage("ACCESS_DENIED"), "Access denied.");
			/** @var $questions array */
			$questions = $this->vote->getQuestions();

			if ($this->questionId > 0 && isset($questions[$this->questionId]))
			{
				$this->question = $questions[$this->questionId];
				$this->answers = $questions[$this->questionId]["ANSWERS"];
				unset($this->question["ANSWERS"]);
			}
			else if (($copyId = intval($this->request->get("COPY_ID"))) && $copyId > 0 && isset($questions[$copyId]))
			{
				$this->question = $questions[$copyId];
				foreach ($questions[$copyId]["ANSWERS"] as $answer)
				{
					$this->answers[] = (["ID" => "c".$answer["ID"], "NEW" => "Y", "SAVED" => "N"] + $answer);
				}
				unset($this->question["ID"]);
				unset($this->question["ANSWERS"]);
			}
			else
			{
				$this->question = array(
					"ACTIVE" => "Y",
					"VOTE_ID" => $this->voteId,
					"C_SORT" => \CVoteQuestion::GetNextSort($this->voteId),
					"QUESTION" => "",
					"QUESTION_TYPE" => "html",
					"IMAGE_ID" => "",
					"DIAGRAM" => "Y",
					"REQUIRED" => "N",
					"DIAGRAM_TYPE" => VOTE_DEFAULT_DIAGRAM_TYPE,
					"TEMPLATE" => "default.php",
					"TEMPLATE_NEW" => "default.php"
				);
			}
			return $this;
		}

		protected function processAction()
		{
			if ($this->request->isPost() && (
				$this->request->getPost("save") !== null || $this->request->getPost("apply") !== null) &&
				check_bitrix_sessid())
			{
				$image = ($this->request->getFile("IMAGE_ID") ?: array()) + ($this->request->getPost("IMAGE_ID_del") == "Y" ? array("del" => "Y") : array());

				$answers = ($this->request->getPost("ANSWER") ?: []);

				$fields = array(
					"VOTE_ID" => $this->voteId,
					"ACTIVE" => ($this->request->getPost("ACTIVE") === "Y" ? "Y" : "N"),
					"C_SORT" => $this->request->getPost("C_SORT"),
					"QUESTION" => $this->request->getPost("QUESTION"),
					"QUESTION_TYPE" => $this->request->getPost("QUESTION_TYPE"),
					"IMAGE_ID" => $image,
					"DIAGRAM" => $this->request->getPost("DIAGRAM"),
					"FIELD_TYPE" => $this->request->getPost("FIELD_TYPE"),
					"REQUIRED" => $this->request->getPost("REQUIRED") === "Y" ? "Y" : "N",
					"DIAGRAM_TYPE" => $this->request->getPost("DIAGRAM_TYPE"),
					"TEMPLATE" => $this->request->getPost("TEMPLATE"),
					"TEMPLATE_NEW" => $this->request->getPost("TEMPLATE_NEW"));
				$this->getApplication()->ResetException();
				if (($this->questionId > 0 && !\CVoteQuestion::Update($this->questionId, $fields)) ||
					($this->questionId <= 0 && (!($this->questionId = \CVoteQuestion::Add($fields)) || $this->questionId <= 0)))
				{
					$e = $this->getApplication()->GetException();
					$this->errorCollection->add(array(new Error($e ? $e->GetString() : "Update question error")));
				}
				else
				{
					foreach ($this->request->getPost("ANSWER") as $id => $answer)
					{
						$res = array(
							"ID" => intval($answer["ID"]),
							"QUESTION_ID" => $this->questionId,
							"ACTIVE" => $answer["ACTIVE"],
							"C_SORT" => $answer["C_SORT"],
							"MESSAGE" => $answer["MESSAGE"],
							"MESSAGE_TYPE" => $answer["MESSAGE_TYPE"],
							"FIELD_TYPE" => $answer["FIELD_TYPE"],
							"FIELD_WIDTH" => $answer["FIELD_WIDTH"],
							"FIELD_HEIGHT" => $answer["FIELD_HEIGHT"],
							"FIELD_PARAM" => $answer["FIELD_PARAM"],
							"COLOR" => $answer["COLOR"],
						);
						if (is_array($answer["IMAGE_ID"]))
						{
							$res["IMAGE_ID"] = $answer["IMAGE_ID"];
							if (array_key_exists($res["ID"], $this->answers) && $this->answers[$res["ID"]]["IMAGE_ID"] > 0)
							{
								$res["IMAGE_ID"]["old_file"] = $this->answers[$res["ID"]]["IMAGE_ID"];
							}
						}
						else if ($this->answers[$res["ID"]]["IMAGE_ID"] > 0 && empty($answer["IMAGE_ID"]))
						{
							$res["IMAGE_ID"] = [
								"old_file" => $this->answers[$res["ID"]]["IMAGE_ID"],
								"del" => "Y"
							];
						}

						$this->getApplication()->ResetException();
						$action = ($answer["DELETED"] == "Y" ? "delete" : ($res["ID"] > 0 ? "update" : "add"));
						if ($action == "add" && ($result = \CVoteAnswer::Add($res)) && $result !== false)
						{
							unset($answers[$id]);
							$id = $result;
							$answers[$id] = array_merge($res, array("ID" => $id));
						}
						else if (!($res["ID"] > 0))
						{
							unset($answers[$id]);
						}
						else if ($action == "delete" && ($result = \CVoteAnswer::Delete($res["ID"])) && $result !== false)
						{
							unset($answers[$id]);
						}
						else if ($action == "update" && ($result = \CVoteAnswer::Update($res["ID"], $res)) && $result !== false)
						{
							// DoNothing
						}
						else
						{
							$e = $this->getApplication()->GetException();
							$this->errorCollection->add(array(
								new Error(($e ? $e->GetString() : "Error"), "answer_".$answer["ID"])
							));
						}
					}
				}
				if (!$this->errorCollection->isEmpty())
				{
					$this->question = $fields;
					$this->answers = $answers;
				}
				else
				{
					$this->arParams["QUESTION_ID"] = $this->questionId;
					$this->prepareParams();
				}
				/** @var array */

				return true;
			}
			return false;
		}

		protected function adjustGrid(array $answers)
		{
			$z = new CDBResult;
			$z->InitFromArray($answers);
			$maxSort = 0;

			array_walk(
				$answers,
				function ($a) use (&$maxSort)
				{
					$maxSort = max($a["C_SORT"], $maxSort);
				}
			);

			$this->arResult["ANSWERS"] = $z;
			$this->arParams["ANSWER_PARAMS"]["MAX_SORT"] = $maxSort;
		}
	}
}

namespace Bitrix\Vote\Component
{
	use Bitrix\Main\Grid\Options;
	use Bitrix\Main\NotImplementedException;
	use Bitrix\Main\Web\PostDecodeFilter;
	use \Bitrix\Main\Web\Json;
	use Bitrix\Main\Error;
	use Bitrix\Main\ErrorCollection;
	use \Bitrix\Main\Localization\Loc;

	class Log implements \ArrayAccess
	{
		/*
		 * @var \CBXVirtualFileFileSystem $file
		 */
		protected $file = null;
		var $data = array();

		/**
		 * Log constructor.
		 * @param string $path Path to log file.
		 * @return void
		 */
		function __construct($path)
		{
			$this->file = \CBXVirtualIo::GetInstance()->GetFile($path);

			if ($this->file->IsExists())
			{
				$data = unserialize($this->file->GetContents());
				foreach($data as $key => $val)
				{
					if (array_key_exists($key , $this->data) && is_array($this->data[$key]) && is_array($val))
						$this->data[$key] = array_merge($this->data[$key], $val);
					else
						$this->data[$key] = $val;
				}
			}
		}

		/**
		 * Saves log.
		 * @param string $key Key of log array.
		 * @param mixed $value value of log array.
		 * @throws NotImplementedException
		 * @return $this
		 */
		public function setLog($key, $value)
		{
			if (array_key_exists($key, $this->data) && is_array($this->data) && is_array($value))
				$this->data[$key] = array_merge($this->data[$key], $value);
			else
				$this->data[$key] = $value;
			$this->save();

			return $this;
		}

		public function isExists()
		{
			return $this->file->IsExists();
		}

		/**
		 * @param $key
		 * @return mixed
		 */
		public function getValue($key)
		{
			return $this->data[$key];
		}

		/**
		 * @throws NotImplementedException
		 * @return void
		 */
		public function save()
		{
			if (!$this->file->IsExists())
			{
				$directory = \CBXVirtualIo::GetInstance()->GetDirectory($this->file->GetPath());
				$directoryExists = $directory->IsExists();
				if (!$directory->Create())
					throw new NotImplementedException("Mandatory directory has not been created.");
				if (!$directoryExists)
				{
					$access = \CBXVirtualIo::GetInstance()->GetFile($directory->GetPath()."/.access.php");
					$content = '<?$PERM["'.$directory->GetName().'"]["*"]="X";?>';

					if (!$access->IsExists() || strpos($access->GetContents(), $content) === false)
					{
						if (($fd = $access->Open('ab')) && $fd)
							fwrite($fd, $content);
						fclose($fd);
					}
				}
			}

			$this->file->PutContents(serialize($this->data));
		}

		/**
		 * @return array
		 */
		public function getLog()
		{
			return $this->data;
		}

		/**
		 *
		 */
		public function unlink()
		{
			if ($this->file instanceof \CBXVirtualFileFileSystem && $this->file->IsExists())
				$this->file->unlink();
		}

		/**
		 * @return void
		 */
		public function clear()
		{
			$this->data = array();
			$this->unlink();
		}

		/**
		 * @param mixed $offset
		 * @return bool
		 */
		public function offsetExists($offset)
		{
			return array_key_exists($offset, $this->data);
		}

		/**
		 * @param mixed $offset
		 * @return mixed|null
		 */
		public function offsetGet($offset)
		{
			if (array_key_exists($offset, $this->data))
				return $this->data[$offset];
			return null;
		}

		/**
		 * @param mixed $offset
		 * @param mixed $value
		 * @throws NotImplementedException
		 */
		public function offsetSet($offset, $value)
		{
			$this->setLog($offset, $value);
		}

		/**
		 * @param mixed $offset
		 * @throws NotImplementedException
		 */
		public function offsetUnset($offset)
		{
			if (array_key_exists($offset, $this->data))
			{
				unset($this->data[$offset]);
				$this->save();
			}
		}
	}


	class VoteQuestionEditGrid
	{
		/** @var string */
		protected $gridId = 'grid_vote_answer';
		/** @var string */
		protected $id;
		/** @var array */
		protected $data = array();
		/** @var Log */
		protected $log;
		/** @var int */
		protected $maxId = 0;
		/** @var  ErrorCollection */
		protected $errorCollection;
		/** @var string */
		protected $logDirectiory;

		public function __construct(string $id)
		{
			$this->id = $id;
			$this->logDirectiory = $name = \CTempFile::GetDirectoryName(
				12,
				array(
					"vote",
					"grid_answer",
					md5(
						serialize(
							array(
								$this->getGridInstanceId(),
								\CMain::GetServerUniqID()
							)
						)
					)
				)
			);
			$this->log = new Log($name);
			$res = $this->log->getLog();
			$maxId = 0;
			array_walk(
				$res,
				function ($a) use (&$maxId)
				{
					if (is_array($a) && $a["ID"])
					{
						$id = substr($a["ID"], 1);
						$maxId = max($maxId, intval($id));
					}
				}
			);
			$this->maxId = $maxId;
			$this->errorCollection = new ErrorCollection;
		}
		/**
		 * @return string
		 */
		public function getGridId()
		{
			return $this->gridId;
		}

		public function getGridInstanceId()
		{
			return $this->id;
		}

		private function getNextId()
		{
			return (++$this->maxId);
		}
		/**
		 * @return \Application|\Bitrix\Main\HttpApplication|\CAllMain|\CMain
		 */
		protected function getApplication()
		{
			global $APPLICATION;
			return $APPLICATION;
		}
		/**
		 * @return array
		 */
		public function restore()
		{
			$this->data = $this->log->getLog();
			return $this->data;
		}

		public function merge(array $data)
		{
			foreach ($data as $d)
			{
				$this->data[$d["ID"]] = array_key_exists($d["ID"], $this->data) ? array_merge($d, $this->data[$d["ID"]]) : $d;
				$this->data[$d["ID"]]["NEW"] = ($d["ID"] > 0 ? "Y" : "N");
				$this->data[$d["ID"]]["SAVED"] = ($this->data[$d["ID"]]["SAVED"] === "N" ? "N" : "Y");
			}
			return $this->data;
		}

		/**
		 * @param $id
		 */
		public function clear($id = null)
		{
			if (is_null($id))
			{
				$this->data = array();
				$this->log->unlink();
			}
			else
			{
				unset($this->data[$id]);
				unset($this->log[$id]);
			}
		}

		public function processAction()
		{
			$request = \Bitrix\Main\Context::getCurrent()->getRequest();
			$this->errorCollection->clear();
			if ($request->isPost() &&
				check_bitrix_sessid() &&
				\Bitrix\Main\Grid\Context::isInternalRequest() &&
				$request->getPost("gridId") == $this->getGridId() &&
				$request->getPost("gridInstanceId") == $this->getGridInstanceId())
			{
				$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

				if ($request->getPost("action") == \Bitrix\Main\Grid\Actions::GRID_ADD_ROW)
				{
					$this->add($request->getPost("data"));
				}
				else if ($request->getPost("action") == \Bitrix\Main\Grid\Actions::GRID_UPDATE_ROW)
				{
					$this->update($request->getPost("id"), $request->getPost("data"));
				}
				else if ($request->getPost("action") == \Bitrix\Main\Grid\Actions::GRID_SAVE_ROWS_SORT)
				{
					$this->update($request->getPost("id"), $request->getPost("data"));
				}
				else if ($request->getPost("action") == \Bitrix\Main\Grid\Actions::GRID_DELETE_ROW)
				{
					$this->delete($request->getPost("id"));
				}
				else if ($request->getPost("action_button_" . $this->getGridId()) === 'edit')
				{
					\CAllFile::ConvertFilesToPost(($request->getFile("FIELDS") ?: []), $rawFiles);

					foreach ($request->getPost("FIELDS") as $id => $fields)
						$this->update($id, $fields, $rawFiles[$id]);
				}
				else
				{
					$ids = $request->getPost("rows") ?: $request->getPost("ID");
					$action = $request->getPost("action_button_" . $this->getGridId());
					if ($controls = $request->getPost("controls"))
						$action = $controls["action_button_" . $this->getGridId()];

					switch ($action)
					{
						case 'delete':
							foreach ($ids as $id)
								$this->delete($id);
							break;
						case 'undelete':
							foreach ($ids as $id)
								$this->update($id, array("DELETED" => "N"));
							break;
						case 'cancel':
							foreach ($ids as $id)
								$this->cancel($id);
							break;
						case 'activate':
							foreach ($ids as $id)
								$this->update($id, array("ACTIVE" => "Y"));
							break;
						case 'deactivate':
							foreach ($ids as $id)
								$this->update($id, array("ACTIVE" => "N"));
							break;
						case 'change_answer_type':
							foreach ($ids as $id)
								$this->update($id, array("FIELD_TYPE" => $request->getPost("FIELD_TYPE" )));
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

		public function prepare()
		{
			$gridOptions = new Options($this->getGridId());
			$sorting = $gridOptions->getSorting(array("sort" => array("C_SORT" => "ASC")));
			foreach ($sorting["sort"] as $sortBy => $sortOrder)
			{
				usort(
					$this->data,
					function ($a, $b) use ($sortBy, $sortOrder)
					{
						$sort1 = (array_key_exists($sortBy, $a) ? $a[$sortBy] : null);
						$sort2 = (array_key_exists($sortBy, $b) ? $b[$sortBy] : null);
						$sort = (strnatcmp($sort1, $sort2) > 0);
						$sort = ($sortOrder == "desc" ? !$sort : $sort);
						return $sort;
					}
				);
			}
		}

		public function get()
		{
			return $this->data;
		}

		/**
		 * @param array $data
		 * @return string|bool
		 */
		public function add(array $data)
		{
			$data["QUESTION_ID"] = 1; // hack to get through \CVoteAnswer::CheckFields
			if (\CVoteAnswer::CheckFields("ADD", $data))
			{
				$id = "n".$this->getNextId();
				$this->data[$id] = array(
					"ID" => $id,
					"IMAGE_ID" => $data["IMAGE_ID"],
					"MESSAGE" => $data["MESSAGE"],
					"MESSAGE_TYPE" => $data["MESSAGE_TYPE"],
					"FIELD_TYPE" => $data["FIELD_TYPE"],
					"FIELD_WIDTH" => $data["FIELD_WIDTH"],
					"FIELD_HEIGHT" => $data["FIELD_HEIGHT"],
					"FIELD_PARAM" => $data["FIELD_PARAM"],
					"ACTIVE" => $data["ACTIVE"],
					"C_SORT" => $data["C_SORT"],
					"COLOR" => $data["COLOR"],
					"SAVED" => "N",
					"NEW" => "Y"
				);
				$this->log[$id] = $this->data[$id];
				return $id;
			}

			$e = $this->getApplication()->GetException();
			$this->errorCollection->add(
				array(
					new Error
					(($e instanceof \CApplicationException ? $e->GetString() : Loc::getMessage("VOTE_GRID_ADD_ERROR")),
						'add_error')
				)
			);
			return false;
		}

		/**
		 * @param $id
		 * @param array $data
		 * @return bool
		 */
		public function update($id, array $data, $files = null)
		{
			if ($data["IMAGE_ID"] === "")
			{
				unset($data["IMAGE_ID"]);
			}
			$data = (array_key_exists($id, $this->data) ? array_merge($this->data[$id], $data) : $data);
			$data["QUESTION_ID"] = 1; // hack to get through \CVoteAnswer::CheckFields

			$imageFile = is_array($files) && array_key_exists("IMAGE_ID", $files) ? $files["IMAGE_ID"] : null;
			if (is_array($imageFile) && $imageFile["error"] <= 0)
			{
				$file2 = \CBXVirtualIo::GetInstance()->GetFile($this->logDirectiory);
				if (\CBXVirtualIo::GetInstance()->Move($imageFile["tmp_name"], $file2->GetPath()."/".$imageFile["name"]))
				{
					$newFile = \CBXVirtualIo::GetInstance()->GetFile($file2->GetPath()."/".$imageFile["name"]);
					if ($newFile->IsExists())
					{
						$newFile->GetPathWithName();
						$data["IMAGE_ID"] = array_merge($imageFile, [ "tmp_name" => $newFile->GetPathWithName()]);
						if(!defined("BX_TEMPORARY_FILES_DIRECTORY"))
						{
							$data["IMAGE_ID"] += ["relative_tmp_name" => "/".ltrim(substr($newFile->GetPathWithName(), strlen($_SERVER["DOCUMENT_ROOT"])), "/\\")];
						}
					}
				}
			}

			if (!array_key_exists("MESSAGE", $data) ||
				\CVoteAnswer::CheckFields("UPDATE", $data, $id))
			{
				$this->data[$id] = [
					"ID" => $id,
					"NEW" => ($data["NEW"] == "Y" ? "Y" : "N")
				];
				foreach ([
					"IMAGE_ID",
					"MESSAGE",
					"MESSAGE_TYPE",
					"FIELD_TYPE",
					"FIELD_WIDTH",
					"FIELD_HEIGHT",
					"FIELD_PARAM",
					"ACTIVE",
					"C_SORT",
					"COLOR",
					"DELETED"
				] as $key)
				{
					if (array_key_exists($key, $data))
					{
						$this->data[$id][$key] = $data[$key];
					}
				}
				$this->data[$id]["SAVED"] = "N";
				$this->log[$id] = $this->data[$id];
				return true;
			}

			$e = $this->getApplication()->GetException();
			$this->errorCollection->add(
				array(
					new Error
						(($e instanceof \CApplicationException ? $e->GetString() : Loc::getMessage("VOTE_GRID_EDIT_ERROR")),
						'update_error')
				)
			);
			return false;
		}

		/**
		 * @param $id
		 * @return bool
		 */
		public function delete($id)
		{
			if (isset($this->data[$id]) && $this->data[$id]["NEW"] === "Y")
			{
				unset($this->data[$id]);
				unset($this->log[$id]);
			}
			else
			{
				$this->data[$id] = array_merge((isset($this->data[$id]) ? $this->data[$id] : ["ID" => $id]), ["DELETED" => "Y", "SAVED" => "N"]);
				$this->log[$id] = array_merge((isset($this->log[$id]) ? $this->log[$id] : ["ID" => $id]), ["DELETED" => "Y", "SAVED" => "N"]);
			}
			return true;
		}
		/**
		 * @param $id
		 * @return bool
		 */
		public function cancel($id)
		{
			unset($this->data[$id]);
			unset($this->log[$id]);
			return true;
		}
	}
}
