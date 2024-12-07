<?
class CSocNetSearchReindex extends CSocNetSearch
{
	var $_params;
	var $_user_id;
	var $_group_id;

	var $_sess_id;
	var $_end_time;
	var $_counter;

	var $_blog_cache;
	/*
	arParams
		PATH_TO_GROUP

		BLOG_GROUP_ID
		PATH_TO_GROUP_BLOG
		PATH_TO_USER_BLOG

		FORUM_ID
		PATH_TO_GROUP_FORUM_MESSAGE
		PATH_TO_USER_FORUM_MESSAGE

		PHOTO_GROUP_IBLOCK_ID
		PATH_TO_GROUP_PHOTO_ELEMENT
		PHOTO_USER_IBLOCK_ID
		PATH_TO_USER_PHOTO_ELEMENT

		CALENDAR_GROUP_IBLOCK_ID
		PATH_TO_GROUP_CALENDAR_ELEMENT

				PATH_TO_GROUP_TASK_ELEMENT
		PATH_TO_USER_TASK_ELEMENT

		FILES_PROPERTY_CODE
		FILES_FORUM_ID
		FILES_GROUP_IBLOCK_ID
		PATH_TO_GROUP_FILES_ELEMENT
		FILES_USER_IBLOCK_ID
		PATH_TO_USER_FILES_ELEMENT
	*/
	public function __construct($user_id=0, $group_id=0, $arParams=array())
	{
		$this->_user_id = intval($user_id);
		$this->_group_id = intval($group_id);
		$this->_params = $arParams;
		$this->_counter = 0;
	}

	function GetCounter()
	{
		return $this->_counter;
	}

	function InitSession($arType)
	{
		if(!array_key_exists("BX_SOCNET_REINDEX_SESS_ID", $_SESSION))
			$_SESSION["BX_SOCNET_REINDEX_SESS_ID"] = array();

		$key = md5(uniqid(""));
		foreach($arType as $type)
			$_SESSION["BX_SOCNET_REINDEX_SESS_ID"][$type] = $key;

		$_SESSION["BX_SOCNET_REINDEX_SESS_ID"]["KEY"] = $key;
	}

	function ReindexForum($entity_type, $last_id, $path_template)
	{
		global $DB;

		if(!CModule::IncludeModule('forum'))
			return false;

		$rsForumMessages = $DB->Query("
			SELECT ft.ID TOPIC_ID, ft.SOCNET_GROUP_ID, ft.OWNER_ID, fm.ID
			FROM
				b_forum_topic ft
				INNER JOIN b_forum_message fm ON fm.TOPIC_ID = ft.ID
			WHERE
				fm.ID > ".intval($last_id)."
				".($entity_type == "G"?
					"AND ft.SOCNET_GROUP_ID IS NOT NULL AND ft.SOCNET_GROUP_ID > 0":
					"AND (ft.SOCNET_GROUP_ID IS NULL OR ft.SOCNET_GROUP_ID = 0) AND ft.OWNER_ID IS NOT NULL"
				)."
			ORDER BY fm.ID
		");
		while($arMessage = $rsForumMessages->Fetch())
		{
			$url = str_replace(
				array(
					"#user_id#",
					"#group_id#",
					"#topic_id#",
					"#message_id#",
					"#action#",
				),
				array(
					$arMessage["OWNER_ID"],
					$arMessage["SOCNET_GROUP_ID"],
					$arMessage["TOPIC_ID"],
					$arMessage["ID"],
					"",
				),
				$path_template
			);

			CSearch::ChangeSite("forum", array(
				SITE_ID => $url,
			), $arMessage["ID"]);

			$arGroups = $this->GetSearchGroups(
				$entity_type,
				$entity_type=="G"? $arMessage["SOCNET_GROUP_ID"]: $arMessage["OWNER_ID"],
				'forum',
				'view'
			);

			$arParams = $this->GetSearchParams(
				$entity_type,
				$entity_type=="G"? $arMessage["SOCNET_GROUP_ID"]: $arMessage["OWNER_ID"],
				'forum',
				'view'
			);

			CSearch::ChangePermission('forum', $arGroups, $arMessage["ID"]);
			CSearch::ChangeIndex("forum", array("UPD" => $this->_sess_id, "PARAMS" => $arParams), $arMessage["ID"]);

			$this->_counter++;

			if($this->_end_time && $this->_end_time <= time())
				return $arMessage["ID"];
		}

		return false;
	}

	function GetBlog($ID)
	{
		if(!is_array($this->_blog_cache))
			$this->_blog_cache = array();
		if(!array_key_exists($ID, $this->_blog_cache))
		{
			global $DB;
			$rsBlog = $DB->Query("
				SELECT ID, OWNER_ID, SOCNET_GROUP_ID
				FROM b_blog
				WHERE ID = ".intval($ID)."
				AND USE_SOCNET='Y'
				AND GROUP_ID = ".intval($this->_params["BLOG_GROUP_ID"])."
			");
			$this->_blog_cache[$ID] = $rsBlog->Fetch();
		}
		return $this->_blog_cache[$ID];
	}

	function IndexBlogItemUser($arFields)
	{
		global $DB;

		$ID = $arFields["ID"];
		if($ID=="")
			return true;
		unset($arFields["ID"]);

		switch(mb_substr($ID, 0, 1))
		{
			case "P":
				$blog = $this->GetBlog($arFields["PARAM2"]);
				if(
					is_array($blog)
					&& intval($blog["SOCNET_GROUP_ID"]) <= 0
					&& intval($blog["OWNER_ID"]) > 0
					&& $this->_params["PATH_TO_USER_BLOG_POST"] <> ''
				)
				{
					$paramsTmp = $this->GetSearchParams(
						"U",
						intval($blog["OWNER_ID"]),
						'blog',
						'view_post'
					);
					if(!empty($arFields["PARAMS"]))
					{
						$arFields["PARAMS"] = array_merge($paramsTmp, $arFields["PARAMS"]);
					}
					else
					{
						$arFields["PARAMS"] = $paramsTmp;
					}

					foreach($arFields["SITE_ID"] as $site_id => $url)
					{
						$arFields["SITE_ID"][$site_id] = str_replace(
							array(
								"#user_id#",
								"#group_id#",
								"#post_id#",
							),
							array(
								$blog["OWNER_ID"],
								$blog["SOCNET_GROUP_ID"],
								mb_substr($ID, 1),
							),
							$this->_params["PATH_TO_USER_BLOG_POST"]
						);
					}

					$arFields["REINDEX_FLAG"] = true;
					CSearch::Index("blog", $ID, $arFields, false, $this->_sess_id);
					$this->_counter++;
				}
				break;
			case "C":
				$blog = $this->GetBlog(intval($arFields["PARAM2"]));
				if(
					is_array($blog)
					&& intval($blog["SOCNET_GROUP_ID"]) <= 0
					&& intval($blog["OWNER_ID"]) > 0
					&& $this->_params["PATH_TO_USER_BLOG_COMMENT"] <> ''
				)
				{
					$paramsTmp = $this->GetSearchParams(
						"U",
						intval($blog["OWNER_ID"]),
						'blog',
						'view_comment'
					);
					if(!empty($arFields["PARAMS"]))
					{
						$arFields["PARAMS"] = array_merge($paramsTmp, $arFields["PARAMS"]);
					}
					else
					{
						$arFields["PARAMS"] = $paramsTmp;
					}

					foreach($arFields["SITE_ID"] as $site_id => $url)
					{
						$arFields["SITE_ID"][$site_id] = str_replace(
							array(
								"#user_id#",
								"#group_id#",
								"#post_id#",
								"#comment_id#",
							),
							array(
								$blog["OWNER_ID"],
								$blog["SOCNET_GROUP_ID"],
								mb_substr($arFields["PARAM2"], mb_strpos($arFields["PARAM2"], "|") + 1),
								mb_substr($ID, 1),
							),
							$this->_params["PATH_TO_USER_BLOG_COMMENT"]
						);
					}

					$arFields["REINDEX_FLAG"] = true;
					CSearch::Index("blog", $ID, $arFields, false, $this->_sess_id);
					$this->_counter++;
				}
				break;
		}

		if($this->_end_time && $this->_end_time <= time())
			return false;
		else
			return true;
	}

	function IndexBlogItemGroup($arFields)
	{
		if($this->_end_time && $this->_end_time <= time())
			return false;
		else
			return true;
	}

	function ReindexBlog($entity_type, $last_id)
	{
		global $DB;

		if(!CModule::IncludeModule('blog'))
			return false;

		if(mb_substr($last_id, 0, 1) == "0")
			$last_id = "";

		if($entity_type=="G")
			return false;

		return CBlogSearch::OnSearchReindex(array(
			"ID" => $last_id,
			"MODULE" => "blog",
		), $this, "IndexBlogItemUser");

	}

	function UpdateForumTopicIndex($topic_id, $entity_type, $entity_id, $feature, $operation, $path_template)
	{
		global $DB;

		if(!CModule::IncludeModule("forum"))
			return;

		$topic_id = intval($topic_id);

		$rsForumTopic = $DB->Query("SELECT FORUM_ID FROM b_forum_topic WHERE ID = ".$topic_id);
		$arForumTopic = $rsForumTopic->Fetch();
		if(!$arForumTopic)
			return;

		$arGroups = $this->GetSearchGroups(
			$entity_type,
			$entity_id,
			$feature,
			$operation
		);

		CSearch::ChangePermission("forum", $arGroups, false, $arForumTopic["FORUM_ID"], $topic_id);

		$rsForumMessages = $DB->Query("
			SELECT ID
			FROM b_forum_message
			WHERE TOPIC_ID = ".intval($topic_id)."
		");
		while($arMessage = $rsForumMessages->Fetch())
		{
			$url = str_replace(
				array(
					"#topic_id#",
					"#message_id#",
					"#action#",
				),
				array(
					$arTopic["ID"],
					$arMessage["ID"],
					"",
				),
				$path_template
			);

			CSearch::ChangeSite("forum", array(
				SITE_ID => $url,
			), $arMessage["ID"]);

			$this->_counter++;
		}

		$arParams = $this->GetSearchParams(
			$entity_type,
			$entity_id,
			$feature,
			$operation
		);

		CSearch::ChangeIndex("forum", array("UPD" => $this->_sess_id, "PARAMS"=>$arParams), false, $arForumTopic["FORUM_ID"], $topic_id);
	}

	function ReindexIBlock($iblock_id, $entity_type, $feature, $operation, $path_template, $arFieldList, $last_id)
	{
		global $DB;

		if(!CModule::IncludeModule("iblock"))
			return false;

		$arSections = array();

		$rsElements = CIBlockElement::GetList(
			array("ID"=>"asc"),
			array(
				"IBLOCK_ID" => $iblock_id,
				">ID" => intval($last_id),
				"CHECK_PERMISSIONS" => "N",
			),
			false, false,
			array_merge(
				array("ID", "IBLOCK_ID", "IBLOCK_TYPE_ID", "NAME", "TAGS", "TIMESTAMP_X", "IBLOCK_SECTION_ID"),
				$arFieldList
			)
		);
		while($arFields = $rsElements->Fetch())
		{
			if(!array_key_exists($arFields["IBLOCK_SECTION_ID"], $arSections))
			{
				$rsPath = CIBlockSection::GetNavChain($arFields["IBLOCK_ID"], $arFields["IBLOCK_SECTION_ID"]);
				$arSection = $rsPath->Fetch();
				if($entity_type == "G")
					$arSections[$arFields["IBLOCK_SECTION_ID"]] = intval($arSection["SOCNET_GROUP_ID"]);
				else
					$arSections[$arFields["IBLOCK_SECTION_ID"]] = intval($arSection["CREATED_BY"]);
			}
			$entity_id = $arSections[$arFields["IBLOCK_SECTION_ID"]];

			if($entity_id)
			{
				$url = str_replace(
					array(
						"#user_id#",
						"#group_id#",
						"#user_alias#",
						"#section_id#",
						"#element_id#",
						"#task_id#",
						"#name#",
					),
					array(
						$entity_id,
						$entity_id,
						($entity_type == "G"? "group_": "user_").$entity_id,
						$arFields["IBLOCK_SECTION_ID"],
						$arFields["ID"],
						$arFields["ID"],
						rawurlencode($arFields["NAME"]),
					),
					$path_template
				);

				$body = "";
				if($feature == "wiki")
					$CWikiParser = new CWikiParser();
				foreach($arFieldList as $field)
				{
					$text = "";

					if($field == "PREVIEW_TEXT" || $field == "DETAIL_TEXT")
					{
						if(isset($CWikiParser))
							$text = HTMLToTxt($CWikiParser->parseForSearch($arFields[$field]));
						elseif(isset($arFields[$field."_TYPE"]) && $arFields[$field."_TYPE"] === "html")
							$text = HTMLToTxt($arFields[$field]);
						else
							$text = $arFields[$field];
					}
					elseif($field == $this->_file_property)
					{
						$arFile = CIBlockElement::__GetFileContent($arFields[$this->_file_property."_VALUE"]);
						if(is_array($arFile))
						{
							$text = $arFile["CONTENT"];
							$arFields["TAGS"] .= ",".$arFile["PROPERTIES"][COption::GetOptionString("search", "page_tag_property")];
						}
					}
					elseif($field == "PROPERTY_FORUM_TOPIC_ID")
					{
						$topic_id = intval($arFields["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
						if($topic_id)
							$this->UpdateForumTopicIndex($topic_id, $entity_type, $entity_id, $feature, $operation, $this->Url($url, array("MID" => "#message_id#"), "message#message_id#"));
					}

					$body .= $text."\n\r";
				}

				if(isset($CWikiParser))
					$title = preg_replace('/^category:/iu', GetMessage('CATEGORY_NAME').':', $arFields['NAME']);
				else
					$title = $arFields["NAME"];

				$arPermissions = $this->GetSearchGroups(
					$entity_type,
					$entity_id,
					$feature,
					$operation
				);

				if (CIBlock::GetArrayByID($arFields["IBLOCK_ID"], "RIGHTS_MODE") == "E")
				{
					$obElementRights = new CIBlockElementRights($arFields["IBLOCK_ID"], $arFields["ID"]);
					$arPermissions = $obElementRights->GetGroups(array("element_read"));
				}

				CSearch::Index("socialnetwork", $arFields["ID"], array(
					"LAST_MODIFIED" => $arFields["TIMESTAMP_X"],
					"TITLE" => $title,
					"BODY" => $body,
					"SITE_ID" => array(SITE_ID => $url),
					"PARAM1" => $arFields["IBLOCK_TYPE_ID"],
					"PARAM2" => $arFields["IBLOCK_ID"],
					"PARAM3" => $entity_id,
					"TAGS" => $arFields["TAGS"],
					"PERMISSIONS" => $arPermissions,
					"PARAMS" => $this->GetSearchParams(
						$entity_type,
						$entity_id,
						$feature,
						$operation
					),
					"REINDEX_FLAG" => true,
				), true, $this->_sess_id);

				$this->_counter++;
			}

			if($this->_end_time && $this->_end_time <= time())
				return $arFields["ID"];
		}

		return false;
	}

	function ReindexGroups($last_id)
	{
		return $this->OnSearchReindex(array(
			"MODULE" => "socialnetwork",
		), $this, "IndexItem");
	}

	function IndexItem($arFields)
	{
		$ID = $arFields["ID"];
		if($ID=="")
			return true;
		unset($arFields["ID"]);

		$arFields["REINDEX_FLAG"] = true;
		CSearch::Index("socialnetwork", $ID, $arFields, false, $this->_sess_id);

		$this->_counter++;

//		if($this->_end_time && $this->_end_time <= time())
//			return false;
//		else
			return true;
	}

	function StepIndex($arSteps, $current_step, $last_id, $timeout=0)
	{
		global $DB;

		if(!CModule::IncludeModule('search'))
			return false;

		foreach ($_SESSION["BX_SOCNET_REINDEX_SESS_ID"] as $key => $value)
			$_SESSION["BX_SOCNET_REINDEX_SESS_ID"][$key] = $DB->ForSQL($value);

		if($timeout > 0)
			$this->_end_time = time()+$timeout;
		else
			$this->_end_time = 0;

		$this->_counter = 0;

		$this->_sess_id = $_SESSION["BX_SOCNET_REINDEX_SESS_ID"]["KEY"];

		do
		{
			$next_step = array_shift($arSteps);
		} while ($next_step != $current_step);

		if(count($arSteps) <= 0)
			$next_step = "end";
		else
			$next_step = array_shift($arSteps);

		switch($current_step)
		{

		case "init":
			$last_id = 0;
			break;

		case "groups":
			$last_id = $this->ReindexGroups($last_id);
			break;

		case "group_blogs":
			$blog_group = intval($this->_params["BLOG_GROUP_ID"]);
			if($blog_group)
				$last_id = $this->ReindexBlog("G", $last_id);
			break;

		case "user_blogs":
			$blog_group = intval($this->_params["BLOG_GROUP_ID"]);
			if($blog_group)
				$last_id = $this->ReindexBlog("U", $last_id);
			break;

		case "group_forums":
			$path_template = trim($this->_params["PATH_TO_GROUP_FORUM_MESSAGE"]);
			if($path_template <> '')
			{
				$last_id = $this->ReindexForum("G", $last_id, $path_template);
			}
			break;

		case "user_forums":
			$path_template = trim($this->_params["PATH_TO_USER_FORUM_MESSAGE"]);
			if($path_template <> '')
			{
				$last_id = $this->ReindexForum("U", $last_id, $path_template);
			}
			break;

		case "group_photos":
			$path_template = trim($this->_params["PATH_TO_GROUP_PHOTO_ELEMENT"]);
			$iblock = intval($this->_params["PHOTO_GROUP_IBLOCK_ID"]);

			if(mb_strlen($path_template) && $iblock)
				$last_id = $this->ReindexIBlock($iblock, "G", "photo", "view", $path_template, array("PREVIEW_TEXT", "PROPERTY_FORUM_TOPIC_ID"), $last_id);
			else
				$last_id = 0;
			break;

		case "user_photos":
			$path_template = trim($this->_params["PATH_TO_USER_PHOTO_ELEMENT"]);
			$iblock = intval($this->_params["PHOTO_USER_IBLOCK_ID"]);

			if(mb_strlen($path_template) && $iblock)
				$last_id = $this->ReindexIBlock($iblock, "U", "photo", "view", $path_template, array("PREVIEW_TEXT", "PROPERTY_FORUM_TOPIC_ID"), $last_id);
			else
				$last_id = 0;
			break;

		case "group_calendars":
			$path_template = trim($this->_params["PATH_TO_GROUP_CALENDAR_ELEMENT"]);
			$iblock = intval($this->_params["CALENDAR_GROUP_IBLOCK_ID"]);

			if(mb_strlen($path_template) && $iblock)
				$last_id = $this->ReindexIBlock($iblock, "G", "calendar", "view", $path_template, array("DETAIL_TEXT"), $last_id);
			else
				$last_id = 0;
			break;

		case "group_files":
			$path_template = trim($this->_params["PATH_TO_GROUP_FILES_ELEMENT"]);
			$iblock = intval($this->_params["FILES_GROUP_IBLOCK_ID"]);

			$property = mb_strtoupper(trim($this->_params["FILES_PROPERTY_CODE"]));
			if($property == '')
				$property = "FILE";
			$this->_file_property = "PROPERTY_".$property;

			if(mb_strlen($path_template) && $iblock)
				$last_id = $this->ReindexIBlock($iblock, "G", "files", "view", $path_template, array($this->_file_property, "PROPERTY_FORUM_TOPIC_ID"), $last_id);
			else
				$last_id = 0;
			break;

		case "group_wiki":
			if(CModule::IncludeModule("wiki"))
			{
				$path_template = trim($this->_params["PATH_TO_GROUP"])."wiki/#name#/";
				$iblock = intval(COption::GetOptionInt("wiki", "socnet_iblock_id"));

				if(mb_strlen($path_template) && $iblock)
					$last_id = $this->ReindexIBlock($iblock, "G", "wiki", "view", $path_template, array("DETAIL_TEXT"), $last_id);
				else
					$last_id = 0;
			}
			break;

		case "user_files":
			$path_template = trim($this->_params["PATH_TO_USER_FILES_ELEMENT"]);
			$iblock = intval($this->_params["FILES_USER_IBLOCK_ID"]);

			$property = mb_strtoupper(trim($this->_params["FILES_PROPERTY_CODE"]));
			if($property == '')
				$property = "FILE";
			$this->_file_property = "PROPERTY_".$property;

			if(mb_strlen($path_template) && $iblock)
				$last_id = $this->ReindexIBlock($iblock, "U", "files", "view", $path_template, array($this->_file_property, "PROPERTY_FORUM_TOPIC_ID"), $last_id);
			else
				$last_id = 0;
			break;

		case "delete_old":
			CSearch::DeleteOld($_SESSION["BX_SOCNET_REINDEX_SESS_ID"], "socialnetwork");
			$last_id = 0;
			break;

		default:
			$last_id = 0;
			break;
		}

		if($last_id > 0 || preg_match('/^.\d/', $last_id))
			return array("step" => $current_step, "last_id" => $last_id);
		else
			return array("step" => $next_step, "last_id" => 0);
	}

	public static function OnBeforeFullReindexClear()
	{
	}

	public static function OnBeforeIndexDelete($strWhere)
	{
	}
}
