<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');	// first system's prolog
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/learning/prolog.php');	// init module

if (!CModule::IncludeModule('learning'))
{
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // second system's prolog

	if (IsModuleInstalled('learning') && defined('LEARNING_FAILED_TO_LOAD_REASON'))
		echo LEARNING_FAILED_TO_LOAD_REASON;
	else
		CAdminMessage::ShowMessage(GetMessage('LEARNING_MODULE_NOT_FOUND'));

	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');	// system's epilog
	exit();
}

// load language file for our module
IncludeModuleLangFile(__FILE__);

class CLearnRenderAdminUnilessonListException extends Exception
{
	const C_COURSE_UNAVAILABLE  = 0x01;
	const C_ACCESS_DENIED       = 0x02;
	const C_NEED_SHOW_AUTH_FORM = 0x04;
	const C_ACTION_UPDATE_FAIL  = 0x08;
	const C_LOGIC               = 0x10;
}

class CLearnRenderAdminUnilessonList
{
	const ListAnyCoursesMode   = 1;		// Courses' list requested
	const ListAnyLessonsMode   = 2;		// Lessons' list requested (without relation to parent)
	const ListChildLessonsMode = 3;		// Lessons' list requested (with relation to parent)

	// This constants for special values on

	protected $oList, $oFilter;
	protected $LEARNING_RIGHT = null;
	// is it need?	protected $isInsideCourse = null;
	protected $tableID        = 'tbl_unilesson_list';
	protected $arFilter       = array();		// current filter for CLearnLesson::GetList()
	protected $arSortOrder    = array('LESSON_ID' => 'asc');
	protected $rsData;							// list of items
	protected $requestedParentLessonId;			// $_POST['PARENT_LESSON_ID'] or $_GET['PARENT_LESSON_ID'] or NULL
	protected $listMode;				// List mode (one of ListAnyCoursesMode, ListAnyLessonsMode, ListChildLessonsMode)
	protected $oAccess;
	protected $contextCourseLessonId = false;

	protected $search_mode;
	protected $search_retpoint;
	protected $search_mode_type;
	protected $hrefSearchRetPoint;

	public function __construct()
	{
		global $USER;
		$this->oAccess = CLearnAccess::GetInstance($USER->GetID());

		// Removes all global variables with prefix "str_"
		ClearVars();

		$parentLessonId = -2;		// by default, magic number '-2' is means 'List lessons, without relation to parent'

		$oPath = false;
		if (isset ($_GET['LESSON_PATH'])
			&& ($_GET['LESSON_PATH'] <> '')
		)
		{
			$oPath = new CLearnPath();
			$oPath->ImportUrlencoded($_GET['LESSON_PATH']);

			// if most top lesson is a course => than we are in context of this course
			$rootAncestorLessonId = $oPath->GetTop();
			if ($rootAncestorLessonId !== false)
			{
				$rc = CLearnLesson::GetLinkedCourse($rootAncestorLessonId);
				if ($rc !== false)
					$this->contextCourseLessonId = (int) $rootAncestorLessonId;		// lesson id of course
			}
		}

		if (isset($_POST['PARENT_LESSON_ID']))
		{
			$parentLessonId = intval($_POST['PARENT_LESSON_ID']);
		}
		elseif (isset($_GET['PARENT_LESSON_ID']))
		{
			$parentLessonId = intval($_GET['PARENT_LESSON_ID']);
		}
		elseif ($oPath !== false)
		{
			$parentLessonId = $oPath->GetBottom();
			if ($parentLessonId === false)
				$parentLessonId = -2;		// by default, magic number '-2' is means 'List lessons, without relation to parent'
		}

		$this->requestedParentLessonId = $parentLessonId;

		// Determine current list mode
		if ($parentLessonId >= 1)
			$this->listMode = self::ListChildLessonsMode;
		elseif ($parentLessonId == -1)	// magic number '-1' is means 'List courses'
			$this->listMode = self::ListAnyCoursesMode;
		else
			$this->listMode = self::ListAnyLessonsMode;		// by default

		$orderBy = false;
		$order = 'asc';

		if (isset($_POST['by']))
			$orderBy = $_POST['by'];
		elseif (isset($_GET['by']))
			$orderBy = $_GET['by'];

		if (isset($_POST['order']))
			$order = $_POST['order'];
		elseif (isset($_GET['order']))
			$order = $_GET['order'];

		$order = mb_strtolower($order);
		if ( ($orderBy !== false) && (($order === 'asc') || ($order === 'desc')) )
			$this->arSortOrder = array ($orderBy => $order);

		$this->search_mode = false;
		$this->search_mode_type = 'childs_candidates';	// by default;
		if (isset($_GET['search_retpoint']))
		{
			$this->search_mode = true;
			$this->search_retpoint = $_GET['search_retpoint'];
			$this->hrefSearchRetPoint = '&search_retpoint=' . htmlspecialcharsbx($this->search_retpoint);

			if (isset($_GET['search_mode_type']))
			{
				if ($_GET['search_mode_type'] === 'parents_candidates')
					$this->search_mode_type = 'parents_candidates';
				elseif ($_GET['search_mode_type'] === 'attach_question_to_lesson')
					$this->search_mode_type = 'attach_question_to_lesson';
			}
		}
	}


	public function IsSearchMode()
	{
		return ($this->search_mode);
	}


	public function getSearchMode()
	{
		return ($this->search_mode_type);
	}

	public function EnsureReadRights()
	{
		// Check access rights
		if ( defined('LEARNING_ADMIN_ACCESS_DENIED') )
		{
			throw new CLearnRenderAdminUnilessonListException ('',
				CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED
				| CLearnRenderAdminUnilessonListException::C_NEED_SHOW_AUTH_FORM);
		}

		return ($this);
	}


	protected function EnsureLessonUpdateAccess ($lessonID)
	{
		if ($this->IsLessonUpdateAccess ($lessonID) !== true)
		{
			throw new CLearnRenderAdminUnilessonListException ('',
				CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
		}

		return ($this);
	}


	protected function IsLessonUpdateAccess ($lessonID)
	{
		if ($this->oAccess->IsLessonAccessible ($lessonID, CLearnAccess::OP_LESSON_WRITE, true))
			return (true);
		else
			return (false);

		return ($this);
	}


	protected function EnsureLessonUnlinkAccess ($parentLessonId, $childLessonId)
	{
		if ($this->LEARNING_RIGHT < 'W')
		{
			throw new CLearnRenderAdminUnilessonListException ('',
				CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
		}

		// TODO: check access in new data model

		return ($this);
	}

	public function Init()
	{
		$oSort = new CAdminSorting($this->tableID, 'LESSON_ID', 'asc', 'learning_sort_by', 'learning_sort_order');	// sort initialization
		$this->oList = new CAdminList($this->tableID, $oSort);		// list initialization

		$GLOBALS['learning_sort_by'] = mb_strtoupper($GLOBALS['learning_sort_by']);
		$GLOBALS['learning_sort_order'] = mb_strtoupper($GLOBALS['learning_sort_order']);
		if ( ! in_array($GLOBALS['learning_sort_order'], array('ASC', 'DESC'), true) )
			$GLOBALS['learning_sort_order'] = 'ASC';

		if ($GLOBALS['learning_sort_by'] <> '')
			$this->arSortOrder = array($GLOBALS['learning_sort_by'] => $GLOBALS['learning_sort_order']);

		$arFilterFields = array(
			'filter_name',
			'filter_uid',
			//'filter_course_id',	// there is no COURSE_ID field for general lesson in CLearnLesson::GetList()
			'filter_creator_id',
			'filter_active',
			'filter_keywords',
			'filter_lesson_type'
		);

		$arFilterHeaders = array(
			'ID',
			//'COURSE_ID',	// there is no COURSE_ID field for general lesson in CLearnLesson::GetList()
			GetMessage('LEARNING_COURSE_ADM_CREATED2'),	// who is creator
			GetMessage('LEARNING_F_ACTIVE2'),			// activity
			GetMessage('LEARNING_KEYWORDS'),
			GetMessage('LEARNING_FILTER_TYPE_OF_UNILESSON')
		);	// names of filter fields for humans

		$arHeaders = array(
			array('id'    => 'NAME',
				'content' => GetMessage('LEARNING_NAME'),
				'sort'    => 'name',
				'default' => true),

			array('id'    => 'LESSON_ID',
				'content' => 'ID',
				'sort'    => 'lesson_id',
				'default' => true),

			array('id'    => 'TIMESTAMP_X',
				'content' => GetMessage('LEARNING_COURSE_ADM_DATECH'),
				'sort'    => 'timestamp_x',
				'default' => true),

			array('id'    => 'ACTIVE',
				'content' => GetMessage('LEARNING_COURSE_ADM_ACT'),
				'sort'    => 'active',
				'default' => true),

			array('id'    => 'SITE_ID',
				'content' => GetMessage('LEARNING_SITE_ID'),
				'sort'    => 'site_id',
				'default' => true)
			);

		if ($this->requestedParentLessonId != -2)		// magic number '-2' is means 'List lessons, without relation to parent'
		{
			$arHeaders[] = array(
				'id'      => 'SORT',
				'content' => GetMessage('LEARNING_COURSE_ADM_SORT'),
				'sort'    => 'sort',
				'default' => true
				);

			//$arFilterFields[]  = 'filter_sort';
			//$arFilterHeaders[] = 'SORT';
		}

		if ($this->contextCourseLessonId !== false)
		{
			$arHeaders[] = array(
				'id'     => 'PUBLISH_PROHIBITED',
				'content' => GetMessage('LEARNING_COURSE_ADM_PUBLISH_PROHIBITED'),
				'default' => true);
		}

		$arHeaders[] = array(
			'id'      => 'CARDINALITY_DEPTH',
			'content' => GetMessage('LEARNING_COURSE_ADM_CARDINALITY_DEPTH'),
			'default' => true);

		$arHeaders[] = array(
			'id'      => 'CARDINALITY_CHAPTERS',
			'content' => GetMessage('LEARNING_COURSE_ADM_CARDINALITY_CHAPTERS'),
			'default' => true);

		$arHeaders[] = array(
			'id'      => 'CARDINALITY_LESSONS',
			'content' => GetMessage('LEARNING_COURSE_ADM_CARDINALITY_LESSONS'),
			'default' => true);

		$arHeaders[] = array(
			'id'      => 'CARDINALITY_QUESTIONS',
			'content' => GetMessage('LEARNING_COURSE_ADM_CARDINALITY_QUESTIONS'),
			'default' => true);

		$arHeaders[] = array(
			'id'      => 'CARDINALITY_TESTS',
			'content' => GetMessage('LEARNING_COURSE_ADM_CARDINALITY_TESTS'),
			'default' => true);

		$arHeaders[] = array(
			'id'      => 'PARENTS',
			'content' => GetMessage('LEARNING_INCLUDED_IN'),
			'default' => true);

		$arHeaders[] = array(
			'id'      => 'CHILDS',
			'content' => GetMessage('LEARNING_CONSIST_FROM'),
			'default' => false);

		$arHeaders[] = array(
			'id'      => 'CODE',
			'content' => GetMessage('LEARNING_CODE'),
			'sort'    => 'code',
			'default' => false);

		$arHeaders[] = array(
			'id'      => 'CREATED_USER_NAME',
			'content' => GetMessage('LEARNING_AUTHOR'),
			'sort'    => 'code',
			'default' => false);

		// list's header
		$this->oList->AddHeaders($arHeaders);

		$this->oFilter = new CAdminFilter(
			$this->tableID . "_filter",
			$arFilterHeaders
		);

		// filter initialization (can puts data into global vars)
		$this->oList->InitFilter($arFilterFields);


		global $filter_name, $filter_uid, $filter_active, $filter_creator_id, $filter_keywords, $filter_lesson_type;

		if ($filter_name !== null)
			$this->arFilter['?NAME'] = $filter_name;

		if ($filter_uid !== null)
			$this->arFilter['LESSON_ID']  = $filter_uid;

		if ($filter_creator_id !== null)
			$this->arFilter['CREATED_USER_NAME'] = '%' . $filter_creator_id . '%';

		if ($filter_active !== null)
			$this->arFilter['ACTIVE'] = $filter_active;

		if ($filter_keywords !== null)
			$this->arFilter['KEYWORDS'] = '%' . $filter_keywords . '%';

		if ($filter_lesson_type !== null)
		{
			if ($filter_lesson_type === 'COURSE')
				$this->arFilter['>LINKED_LESSON_ID'] = 0;
			elseif ($filter_lesson_type === 'LESSON_WITH_CHILDS')
			{
				$this->arFilter['>CHILDS_CNT'] = 0;
				$this->arFilter['LINKED_LESSON_ID'] = '';
			}
			elseif ($filter_lesson_type === 'LESSON_WO_CHILDS')
				$this->arFilter['CHILDS_CNT'] = 0;
		}

		/*
		if ($this->requestedParentLessonId != -2)		// magic number '-2' is means 'List lessons, without relation to parent'
		{
			global $filter_sort;
			$this->arFilter['SORT'] = $filter_sort;
		}
		*/

		return ($this);
	}

	public function IsNeedProcessActionsOnList()
	{
		if ($this->oList->GroupAction() === false)
			return (false);
		else
			return (true);
	}

	public function ProcessActionsOnList()
	{
		if (isset($_POST['action']) && ($_POST['action'] <> ''))
			$action = $_POST['action'];
		elseif (isset($_GET['action']) && ($_GET['action'] <> ''))
			$action = $_GET['action'];
		elseif (isset($_POST['action_button']) && ($_POST['action_button'] <> ''))
			$action = $_POST['action_button'];
		elseif (isset($_GET['action_button']) && ($_GET['action_button'] <> ''))
			$action = $_GET['action_button'];
		else
			return ($this);		// nothing to do

		$arID = $this->oList->GroupAction();
		if ($arID === false)
			return ($this);		// no items selected

		if ( check_bitrix_sessid() !== true )
		{
			throw new CLearnRenderAdminUnilessonListException ('',
				CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
		}

		if ($_POST['action_target'] === 'selected')
		{
			$arID = array();
			$rsData = $this->fetchDataFromDb();
			while($arRes = $rsData->Fetch())
				$arID[] = $arRes['LESSON_ID'];
		}

		foreach ($arID as $lessonId)
		{
			// If not int or string can't be strictly casted to int
			if ( ! (is_numeric($lessonId) && is_int($lessonId + 0)) )
				continue;

			$lessonId += 0;

			$wasError = false;
			$preventSelectionOnError = false;
			try
			{
				switch ($action)
				{
					case 'unlink':
						// !!! In case of unlinking in $lessonId not Lesson's id, but it's full path
						$oPath = new CLearnPath();
						$oPath->ImportUrlencoded($lessonId);
						$arPath = $oPath->GetPathAsArray();
						if (count($arPath) < 2)
						{
							throw new CLearnRenderAdminUnilessonListException ('',
								CLearnRenderAdminUnilessonListException::C_LOGIC);
						}

						$childLessonId  = $oPath->GetBottom();
						$parentLessonId = $oPath->GetBottom();
						if (($parentLessonId === false) || ($childLessonId === false))
						{
							// something goes wrong
							throw new CLearnRenderAdminUnilessonListException ('',
								CLearnRenderAdminUnilessonListException::C_LOGIC);
						}

						$this->EnsureLessonUnlinkAccess ($parentLessonId, $childLessonId);

						// throws an exception on error
						CLearnLesson::RelationRemove ($parentLessonId, $childLessonId);
					break;

					case 'disband':
						@set_time_limit(0);

						$courseId = CLearnLesson::GetLinkedCourse($lessonId);
						if (($courseId !== false) && CCourse::IsCertificatesExists($courseId))
							throw new Exception (GetMessage("LEARNING_COURSE_UNREMOVABLE_CAUSE_OF_CERTIFICATES"));

						$this->EnsureLessonDisbandAccess ($lessonId);
						CLearnLesson::Delete($lessonId);
					break;

					case 'delete':
					case 'recursive_delete':
						$preventSelectionOnError = true;		// prevent switch table to "selection mode" when cannot delete item in list
						@set_time_limit(0);

						$courseId = CLearnLesson::GetLinkedCourse($lessonId);
						if (($courseId !== false) && CCourse::IsCertificatesExists($courseId))
							throw new Exception (GetMessage("LEARNING_COURSE_UNREMOVABLE_CAUSE_OF_CERTIFICATES"));

						try
						{
							// firstly, simulate to check permissions
							CLearnLesson::DeleteRecursiveLikeHardlinks(
								array(
									'lesson_id' => $lessonId,
									'simulate'  => true
									)
								);

							// If all is OK, try to really remove it
							CLearnLesson::DeleteRecursiveLikeHardlinks($lessonId);
						}
						catch (LearnException $e)
						{
							if ($e->GetCode() === LearnException::EXC_ERR_ALL_ACCESS_DENIED)
							{
								throw new CLearnRenderAdminUnilessonListException ('',
									CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
							}
							else
							{
								// bubble exception
								throw new LearnException ($e->GetMessage(), $e->GetCode());
							}
						}
					break;

					case 'activate':
					case 'deactivate':
						if (mb_strtolower($action) === 'deactivate')
						{
							$this->EnsureLessonDeactivateAccess ($lessonId);
							$arFields = Array('ACTIVE' => 'N');
						}
						elseif (mb_strtolower($action) === 'activate')
						{
							$this->EnsureLessonActivateAccess ($lessonId);
							$arFields = Array('ACTIVE' => 'Y');
						}
						else
						{
							throw new CLearnRenderAdminUnilessonListException ('WTFAYD,#Pro#?!',
								CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
						}

						// Is item course or not?
						$courseId = CLearnLesson::GetLinkedCourse($lessonId);
						if ($courseId === false)
						{
							// not course
							CLearnLesson::Update($lessonId, $arFields);
						}
						else
						{
							$oCourse = new CCourse;
							$rc = $oCourse->Update($courseId, $arFields);
							unset ($oCourse);

							if ($rc === false)
								throw new Exception();
						}
					break;

					default:
						throw new Exception();
					break;
				}
			}
			catch (CLearnRenderAdminUnilessonListException $e)
			{
				$wasError  = true;
				$errorText = $e->getMessage();
				$errorCode = $e->getCode();
			}
			catch (Exception $e)
			{
				$wasError  = true;
				$errorText = $e->getMessage();
				$errorCode = 0;	// Because we checks below only CLearnRenderAdminUnilessonListException codes
			}

			if ($wasError)
			{
				if ($e->getCode() & CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED)
				{
					// Access denied
					$errmsg = GetMessage('LEARNING_SAVE_ERROR') . '#' . $lessonId . ': '
						. GetMessage('LEARNING_ACCESS_D');

					if ($errorText <> '')
						$errmsg .= (': ' . $errorText);
				}
				else
				{
					// Some error occured during update operation
					$errmsg = GetMessage('LEARNING_SAVE_ERROR') . $lessonId;

					if ($errorText <> '')
						$errmsg .= ( ' (' . $errorText . ')' );
				}

				if ($preventSelectionOnError)
					$this->oList->AddUpdateError($errmsg);
				else
					$this->oList->AddUpdateError($errmsg, $lessonId);
			}
		}

		return ($this);
	}


	// Must throw exception if access denied
	protected function EnsureLessonDisbandAccess ($lessonID)
	{
		global $USER;

		if ( ! $this->oAccess->IsLessonAccessible (
			$lessonID,
			CLearnAccess::OP_LESSON_REMOVE,
			true)
		)
		{
			throw new CLearnRenderAdminUnilessonListException ('',
				CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
		}

		// ensure, that all childs can be unlinked from lesson
		$arChildEdges = CLearnLesson::ListImmediateChilds($lessonID);
		if (count($arChildEdges) > 0)
		{
			if ( ! $this->oAccess->IsLessonAccessible(
				$lessonID,
				CLearnAccess::OP_LESSON_UNLINK_DESCENDANTS,
				true)
			)
			{
				throw new CLearnRenderAdminUnilessonListException ('',
					CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
			}

			foreach ($arChildEdges as $arChildEdge)
			{
				if ( ! $this->oAccess->IsLessonAccessible(
						$arChildEdge['CHILD_LESSON'],
						CLearnAccess::OP_LESSON_UNLINK_FROM_PARENTS,
						true)
				)
				{
					throw new CLearnRenderAdminUnilessonListException ('',
						CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
				}
			}
		}

		// ensure, that all parents can be unlinked from lesson
		$arParentEdges = CLearnLesson::ListImmediateParents($lessonID);
		if (count($arParentEdges) > 0)
		{
			if ( ! $this->oAccess->IsLessonAccessible(
				$lessonID,
				CLearnAccess::OP_LESSON_UNLINK_FROM_PARENTS,
				true)
			)
			{
				throw new CLearnRenderAdminUnilessonListException ('',
					CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
			}

			foreach ($arParentEdges as $arParentEdge)
			{
				if ( ! $this->oAccess->IsLessonAccessible(
						$arParentEdge['PARENT_LESSON'],
						CLearnAccess::OP_LESSON_UNLINK_DESCENDANTS,
						true)
				)
				{
					throw new CLearnRenderAdminUnilessonListException ('',
						CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
				}
			}
		}

		return ($this);
	}

	// Must throw exception if access denied
	protected function EnsureLessonActivateAccess ($lessonID)
	{
		global $USER;
		if ($USER->IsAdmin())
			return ($this);

		$oAccess = CLearnAccess::GetInstance($USER->GetID());
		if ( ! $oAccess->IsLessonAccessible ($lessonID, CLearnAccess::OP_LESSON_WRITE) )
		{
			throw new CLearnRenderAdminUnilessonListException ('',
				CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
		}

		return ($this);
	}

	// Must throw exception if access denied
	protected function EnsureLessonDeactivateAccess ($lessonID)
	{
		global $USER;
		if ($USER->IsAdmin())
			return ($this);

		$oAccess = CLearnAccess::GetInstance($USER->GetID());
		if ( ! $oAccess->IsLessonAccessible ($lessonID, CLearnAccess::OP_LESSON_WRITE) )
		{
			throw new CLearnRenderAdminUnilessonListException ('',
				CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
		}

		return ($this);
	}

	protected function IsListAnyCoursesMode()
	{
		return ($this->IsListMode (self::ListAnyCoursesMode));
	}

	protected function IsListAnyLessonsMode()
	{
		return ($this->IsListMode (self::ListAnyLessonsMode));
	}

	protected function IsListChildLessonsMode()
	{
		return ($this->IsListMode (self::ListChildLessonsMode));
	}

	protected function IsListMode ($mode)
	{
		if ($this->listMode === $mode)
			return (true);
		else
			return (false);
	}

	private function fetchDataFromDb()
	{
		if ($this->IsSearchMode())
		{
			$searchMode = $this->getSearchMode();

			if ($searchMode === 'parents_candidates')
			{
				//exit('1');
				$accessOperations = CLearnAccess::OP_LESSON_READ | CLearnAccess::OP_LESSON_LINK_DESCENDANTS;
			}
			elseif ($searchMode === 'childs_candidates')
			{
				//exit('2');
				$accessOperations = CLearnAccess::OP_LESSON_READ | CLearnAccess::OP_LESSON_LINK_TO_PARENTS;
			}
			elseif ($searchMode === 'attach_question_to_lesson')
				$accessOperations = CLearnAccess::OP_LESSON_WRITE;
			else
				$accessOperations = CLearnAccess::OP_LESSON_READ;
		}
		else
			$accessOperations = CLearnAccess::OP_LESSON_READ;

		$this->arFilter['ACCESS_OPERATIONS'] = $accessOperations;

		// fetch data
		if ($this->IsListChildLessonsMode())
		{
			// shows only childs of requested lesson uid
			$CDBResult = CLearnLesson::GetListOfImmediateChilds (
				$this->requestedParentLessonId,
				$this->arSortOrder,
				$this->arFilter
			);
		}
		elseif ($this->IsListAnyLessonsMode())
			$CDBResult = CLearnLesson::GetList($this->arSortOrder, $this->arFilter);
		elseif ($this->IsListAnyCoursesMode())
			$CDBResult = CCourse::GetList($this->arSortOrder, $this->arFilter);

		return ($CDBResult);
	}

	public function FetchData()
	{
		$CDBResult = $this->fetchDataFromDb();

		$this->rsData = new CAdminResult($CDBResult, $this->tableID);

		// navigation setup
		$this->rsData->NavStart();
		$this->oList->NavText($this->rsData->GetNavPrint(''));

		return ($this);
	}

	public function BuildList()
	{
		global $USER;

		$filterParams = GetFilterParams('filter_');

		// list's footer
		$this->oList->AddFooter(
			array(
				array('title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'),
					'value' => $this->rsData->SelectedRowsCount()),
				array('counter' => true,
					'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'), 'value' => '0')
			)
		);

		$oParentPath = new CLearnPath();
		if (isset($_GET['LESSON_PATH']))
			$oParentPath->ImportUrlencoded($_GET['LESSON_PATH']);

		$arParentPath = $oParentPath->GetPathAsArray();

		// building list
		$questionsCountCache = array();
		while ($arRes = $this->rsData->NavNext(false))	// NavNext: don't extract fields to globals
		{
			$oCurPath = new CLearnPath();
			$oCurPath->SetPathFromArray (array_merge($arParentPath, array($arRes['LESSON_ID'])));
			$urlCurPath = $oCurPath->ExportUrlencoded();
			unset ($oCurPath);

			// PUBLISH_PROHIBITED available in context of most parent course only
			if ($this->contextCourseLessonId !== false)
			{
				$arRes['PUBLISH_PROHIBITED'] = 'N';
				if (CLearnLesson::IsPublishProhibited($arRes['LESSON_ID'], $this->contextCourseLessonId))
					$arRes['PUBLISH_PROHIBITED'] = 'Y';
			}

			$arRes['SITE_ID'] = '';

			$courseId = CLearnLesson::GetLinkedCourse ($arRes['LESSON_ID']);
			if ($courseId !== false)
			{
				$hrefPrefix = 'learn_course_edit.php?lang='.LANG.'&COURSE_ID=' . $courseId;

				$resCourseSites = CCourse::GetSite($courseId);
				while($arCourseSites = $resCourseSites->Fetch())
				{
					if ($arRes['SITE_ID'] != '')
						$arRes['SITE_ID'] .= ' / ';

					$arRes['SITE_ID'] .= htmlspecialcharsbx($arCourseSites['LID']);
				}
			}
			else
			{
				$hrefPrefix = 'learn_unilesson_edit.php?lang='.LANG.'&LESSON_ID=' . $arRes['LESSON_ID']
					. '&LESSON_PATH=' . ($this->requestedParentLessonId > 0 ? urlencode($urlCurPath) : $this->requestedParentLessonId);
			}

			$actionEditLesson = $hrefPrefix . $filterParams;

			$rowAction = false;
			$rowTitle = '';
			if ( ! $this->IsSearchMode() )
			{
				$rowAction = 'learn_unilesson_admin.php?lang=' . LANG
						. '&PARENT_LESSON_ID=' . ($arRes['LESSON_ID'] + 0)
						. '&LESSON_PATH=' . $urlCurPath
						. '&set_filter=Y'
						. '&' . $this->hrefSearchRetPoint;

				$rowTitle = GetMessage('LEARNING_TRAVERSE');		// "Traverse list of immediate childs"

				if ( ! $arRes['IS_CHILDS'] )
				{
					$rowAction = $actionEditLesson;
					$rowTitle  = GetMessage('LEARNING_EDIT_TITLE');
				}
			}

			$row =& $this->oList->AddRow(
				$arRes['LESSON_ID'],
				$arRes,
				$rowAction,
				$rowTitle
			);

			$arParents   = $arChilds   = array();
			$htmlParents = $htmlChilds = '';

			$rsParents = CLearnLesson::GetListOfImmediateParents (
				$arRes['LESSON_ID'],
				array(), 								// $arOrder
				array('CHECK_PERMISSIONS' => 'N')		// $arFilter
				);
			while ($arParent = $rsParents->Fetch())
				$arParents[] = $arParent['NAME'];

			$arParents = array_map('htmlspecialcharsbx', $arParents);
			if (count($arParents) > 0)
				$htmlParents = implode('<hr width="100%" size="1">', $arParents);
			else
				$htmlParents = '&nbsp;';

			$rsChilds  = CLearnLesson::GetListOfImmediateChilds  (
				$arRes['LESSON_ID'],
				array(), 								// $arOrder
				array('CHECK_PERMISSIONS' => 'N')		// $arFilter
				);
			while ($arChild = $rsChilds->Fetch())
				$arChilds[] = $arChild['NAME'];

			$arChilds = array_map('htmlspecialcharsbx', $arChilds);
			if (count($arChilds) > 0)
				$htmlChilds = implode('<hr width="100%" size="1">', $arChilds);
			else
				$htmlChilds = '&nbsp;';

			if ( isset($arRes['LINKED_LESSON_ID']) && ($arRes['LINKED_LESSON_ID'] > 0) )
				$icon = 'learning_icon_courses';
			elseif (count($arChilds) > 0)
				$icon = 'learning_icon_chapters';
			else
				$icon = 'learning_icon_lessons';

			if ( ! $this->IsSearchMode() )
			{
				$row->AddViewField('NAME',
				'<span class="adm-list-table-icon-link"><span class="adm-submenu-item-link-icon adm-list-table-icon '.$icon.'"></span>'
				.($rowAction === false
					? '<span class="adm-list-table-link">'.htmlspecialcharsbx($arRes['NAME']).'</span>'
					: '<a href="'.$rowAction.'" class="adm-list-table-link">'.htmlspecialcharsbx($arRes['NAME']).'</a>'
				).
				'</span>');
			}
			else
			{
				$actionUseLesson = "(function()
					{
						var fnName = '"
							. str_replace(
								array("'", ';', ',', "\n", "\r"),
								'',
								htmlspecialcharsbx($this->search_retpoint))
							. "';
						if ( ! (window.opener && window.opener[fnName]) )
							return;

						window.opener[fnName]('" . (int) $arRes['LESSON_ID'] . "', '" . CUtil::JSEscape(htmlspecialcharsbx($arRes['NAME'])) . "');
						window.close();
					})();
					";

				$row->AddViewField('NAME', '<a href="javascript:void(0);" class="adm-list-table-icon-link" onclick="' . $actionUseLesson . '"><span class="adm-submenu-item-link-icon adm-list-table-icon '.$icon.'"></span><span class="adm-list-table-link">' . htmlspecialcharsbx($arRes['NAME']).'</span></a>');
			}

			$row->AddViewField('PARENTS', $htmlParents);
			$row->AddEditField('PARENTS', '&nbsp;');
			$row->AddViewField('CHILDS', $htmlChilds);
			$row->AddEditField('CHILDS', '&nbsp;');

			// this is very heavy statistic, so will be a good idea to add settings to the module, which turn off this statistics
			$oLearnTree = CLearnLesson::GetTree ($arRes['LESSON_ID']);
			$arTree = $oLearnTree->GetTreeAsList();
			$depth        = -1;
			$chapterCount = 0;
			$lessonsCount = 0;

			if (!isset($questionsCountCache[$arRes['LESSON_ID']]))
			{
				$questionsCountCache[$arRes['LESSON_ID']] = CLQuestion::GetCount(array('LESSON_ID' => (int) $arRes['LESSON_ID']));
			}
			$questionsCount = $questionsCountCache[$arRes['LESSON_ID']];

			foreach ($arTree as $arLessonData)
			{
				if ($arLessonData['IS_CHILDS'])
					++$chapterCount;
				else
					++$lessonsCount;

				if ((int) $arLessonData['#DEPTH_IN_TREE'] > $depth)
					$depth = (int) $arLessonData['#DEPTH_IN_TREE'];

				if (!isset($questionsCountCache[$arLessonData['LESSON_ID']]))
				{
					$questionsCountCache[$arLessonData['LESSON_ID']] = CLQuestion::GetCount(array('LESSON_ID' => (int) $arLessonData['LESSON_ID']));
				}
				$questionsCount += $questionsCountCache[$arLessonData['LESSON_ID']];
			}

			// PUBLISH_PROHIBITED available in context of most parent course only
			if ($this->contextCourseLessonId !== false)
			{
				if ( $this->IsLessonUpdateAccess ($arRes['LESSON_ID']) === true )
				{
					$row->AddInputField('PUBLISH_PROHIBITED', array('size' => '35'));

					$row->AddCheckField('PUBLISH_PROHIBITED');
				}
				else
				{
					$row->AddCheckField('PUBLISH_PROHIBITED', true);
				}
			}


			// Render CARDINALITY fields
			$htmlDepth     = (int) ($depth + 1);
			$htmlChapters  = (string) ((int) $chapterCount)
				. '&nbsp;[<a href="learn_unilesson_edit.php?lang=' . LANG
				. '&PROPOSE_RETURN_LESSON_PATH=' . $urlCurPath
				. '" title="' . GetMessage('LEARNING_UNILESSON_ADD') . '"'
				. '>+</a>]';
			$htmlLessons   = (string) ((int) $lessonsCount)
				. '&nbsp;[<a href="learn_unilesson_edit.php?lang=' . LANG
				. '&PROPOSE_RETURN_LESSON_PATH=' . $urlCurPath
				. '" title="' . GetMessage('LEARNING_UNILESSON_ADD') . '"'
				. '>+</a>]';
			$htmlQuestions = '<a href="learn_question_admin.php?lang=' . LANG
				. '&filter=Y&set_filter=Y'
				. '&PARENT_LESSON_ID=' . ($arRes['LESSON_ID'] + 0)
				. '&LESSON_PATH=' . $urlCurPath
				. '" title="' . GetMessage('LEARNING_QUESTION_ALT') . '">'
				. (int) $questionsCount . '</a>'
				. '&nbsp;['
				. '<a href="learn_question_edit.php?lang=' . LANG
					. '&LESSON_PATH=' . $urlCurPath
					. '&QUESTION_TYPE=S'
					. '&filter=Y&set_filter=Y'
					. '&from=learn_menu"'
					. ' title="' . GetMessage('LEARNING_QUESTION_ADD') . '">+</a>'
				. ']';

			$row->AddViewField('CARDINALITY_DEPTH', $htmlDepth);
			$row->AddViewField('CARDINALITY_CHAPTERS', $htmlChapters);
			$row->AddViewField('CARDINALITY_LESSONS', $htmlLessons);
			$row->AddViewField('CARDINALITY_QUESTIONS', $htmlQuestions);

			if ($courseId !== false)
			{
				$testsCount = (int) CTest::GetCount(array('COURSE_ID' => $courseId));

				$htmlTests   = '<a href="learn_test_admin.php?lang=' . LANG
					. '&COURSE_ID=' . $courseId
					. '&PARENT_LESSON_ID=' . (int) $arRes['LESSON_ID']
					. '&LESSON_PATH=' . $urlCurPath
					. '&filter=Y&set_filter=Y"'
					. '>' . $testsCount . '</a>'
					. '&nbsp;[<a href="learn_test_edit.php?lang=' . LANG
					. '&COURSE_ID=' . $courseId
					. '&PARENT_LESSON_ID=' . (int) $arRes['LESSON_ID']
					. '&LESSON_PATH=' . $urlCurPath
					. '&filter=Y&set_filter=Y"
					title="' . GetMessage('LEARNING_QUESTION_ADD') . '"'
					. '>+</a>]';

				$row->AddViewField('CARDINALITY_TESTS', $htmlTests);
			}

			if ( ( ! $this->IsSearchMode() )
				&& ($this->IsLessonUpdateAccess ($arRes['LESSON_ID']) === true)
			)
			{
				$row->AddInputField('NAME', array('size' => '35'));

				// SORT field editing possibly only for courses and for lessons in relation to parent lesson
				if ($this->IsListChildLessonsMode() || $this->IsListAnyCoursesMode())
					$row->AddInputField('SORT', array('size' => '3'));

				$row->AddCheckField('ACTIVE');
				$row->AddInputField('CODE');
			}
			else
			{
				$row->AddCheckField('ACTIVE', false);
			}

			$row->AddViewField('CREATED_USER_NAME', $arRes['CREATED_USER_NAME']);

			$arActions = Array();

			if ( ! $this->IsSearchMode() )
			{
				if ($this->IsLessonUpdateAccess ($arRes['LESSON_ID']) === true)
					$editTxt = GetMessage('MAIN_ADMIN_MENU_EDIT');
				else
					$editTxt = GetMessage('MAIN_ADMIN_MENU_OPEN');

				// Actions
				$arActions[] = array(
					'ICON'    => 'edit',
					'TEXT'    => $editTxt,
					'ACTION'  => $this->oList->ActionRedirect($actionEditLesson)
				);

				$arActions[] = array("SEPARATOR" => true);

				$arActions[] = array(
					'ICON'    => 'list',
					'TEXT'    => GetMessage('LEARNING_QUESTION_ALT') . ' (' . ($questionsCount + 0) . ')',
					'ACTION'  => $this->oList->ActionRedirect(
						'learn_question_admin.php?lang=' . LANG
						. '&filter=Y&set_filter=Y'
						. '&PARENT_LESSON_ID=' . ($arRes['LESSON_ID'] + 0)
						. '&LESSON_PATH=' . urlencode($urlCurPath)
						)
				);

				/*
				$arActions[] = array(
					"ICON"=>"copy",
					"TEXT"=>GetMessage("MAIN_ADMIN_ADD_COPY"),
					"ACTION"=>$this->oList->ActionRedirect("learn_course_edit.php?COPY_ID=".$f_ID));
				*/

				$isDeleteCmdDisabled  = true;
				$isDisbandCmdDisabled = true;
				$deleteMSG            = '';
				$disbandMSG           = '';
				$actionDisband        = '';
				$action               = '';

				if ($arRes['CHILDS_CNT'] > 0)
				{
					$deleteMSG = GetMessage('LEARNING_ADMIN_MENU_DELETE_RECURSIVE')
						. ' (' . (string) ( (int) $arRes['CHILDS_CNT'] ) . ')';
				}
				else
					$deleteMSG = GetMessage("MAIN_ADMIN_MENU_DELETE");

				$disbandMSG = GetMessage('LEARNING_ADMIN_MENU_DISBAND');

				$isEnoughRightsForDisbandLesson = false;
				try
				{
					$this->EnsureLessonDisbandAccess ($arRes['LESSON_ID']);
					$isEnoughRightsForDisbandLesson = true;
				}
				catch (CLearnRenderAdminUnilessonListException $e)
				{
					if ($e->GetCode() & CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED)
						; // access denied, nothing to do
					else
					{
						// bubble exception
						throw new CLearnRenderAdminUnilessonListException ($e->GetMessage(), $e->GetCode());
					}
				}

				// If we can unlink all neighbours and remove lesson
				if ($isEnoughRightsForDisbandLesson)
				{
					$arOPathes = CLearnLesson::GetListOfParentPathes ($arRes['LESSON_ID']);
					$parentPathesCnt = count($arOPathes);

					// prepare "Disband" command
					$isDisbandCmdDisabled = false;
					$actionDisband = $this->oList->ActionDoGroup(
						$arRes['LESSON_ID'],
						'disband',
						'PARENT_LESSON_ID=' . ($this->requestedParentLessonId + 0)
						);

					if ($parentPathesCnt >= 1)
					{
						$actionDisband = "if(confirm('"
							. str_replace(
								'#CNT#',
								$parentPathesCnt,
								GetMessageJS('LEARNING_CONFIRM_DISBAND_LESSON_WITH_PARENT_PATHES')
							)
							. "')) "
							. '{ ' . $actionDisband . ' }';
					}

					$actionDisband = "if(confirm('"
						.GetMessageJS('LEARNING_ADMIN_MENU_DISBAND_QUESTION')
						. "')) "
						. '{ ' . $actionDisband . ' }';

					// prepare "Remove" command
					$isDeleteCmdDisabled = false;

					if ($arRes['CHILDS_CNT'] > 0)
					{
						$action = $this->oList->ActionDoGroup(
							$arRes['LESSON_ID'],
							'recursive_delete',
							'PARENT_LESSON_ID=' . ($this->requestedParentLessonId + 0)
							);
					}
					else
					{
						// If no childs => "delete" is equal to "disband"
						$action = $this->oList->ActionDoGroup(
							$arRes['LESSON_ID'],
							'disband',
							'PARENT_LESSON_ID=' . ($this->requestedParentLessonId + 0)
							);
					}

					if ($parentPathesCnt >= 1)
					{
						$deleteMSG .= ' [' . $parentPathesCnt . ']';
						$action = "if(confirm('"
							. str_replace(
								'#CNT#',
								$parentPathesCnt,
								GetMessageJS("LEARNING_CONFIRM_DEL_LESSON_WITH_PARENT_PATHES")
							)
							. "')) "
							. $action;
					}
					else
					{
						$action = "if(confirm('" . GetMessageJS('LEARNING_CONFIRM_DEL_MESSAGE') . "')) "
							. $action;
					}
				}


				// We can "disband" only lessons, that contains childs

				$arActions[] = array("SEPARATOR" => true);

				if ($arRes['CHILDS_CNT'] > 0)
				{
					$arActions[] = array(
						'ICON'     => 'delete',
						'TEXT'     => $disbandMSG,
						'ACTION'   => $actionDisband,
						'DISABLED' => $isDisbandCmdDisabled,
						'TITLE'    => GetMessage('LEARNING_ADMIN_MENU_DISBAND_TITLE')
						);
				}

				$arActions[] = array(
					'ICON'     => 'delete',
					'TEXT'     => $deleteMSG,
					'ACTION'   => $action,
					'DISABLED' => $isDeleteCmdDisabled
					);
			}
			else
			{
				$arActions[] = array(
					'ICON'    => 'list',
					'TEXT'    => GetMessage('LEARNING_SELECT'),
					'ACTION'  => $actionUseLesson
				);
			}

			$row->AddActions($arActions);
		}

		return ($this);
	}

	// Build group actions buttons in list
	public function BuildListGroupActionsButton()
	{
		// no group actions in search mode
		if ($this->IsSearchMode())
			return ($this);

		$this->oList->AddGroupActionTable(Array(
			'activate'   => GetMessage('MAIN_ADMIN_LIST_ACTIVATE'),
			'deactivate' => GetMessage('MAIN_ADMIN_LIST_DEACTIVATE'),
			'delete'     => GetMessage('MAIN_ADMIN_LIST_DELETE'),
			)
		);

		return ($this);
	}

	public function BuildListContextMenu()
	{
		$aContext = array();

		$parentLessonId = false;

		// Button "level up" available only if LESSON_PATH available and parent exists in it
		if (isset($_GET['LESSON_PATH']) && $_GET['LESSON_PATH'] <> '')
		{
			$PROPOSE_RETURN_LESSON_PATH = '&PROPOSE_RETURN_LESSON_PATH=' . urlencode($_GET['LESSON_PATH']);
			$oPath = new CLearnPath();
			$oPath->ImportUrlencoded ($_GET['LESSON_PATH'] ?? '');
			$arPath = $oPath->GetPathAsArray();

			$arUpPath = $arPath;

			$count_arUpPath = count($arUpPath);

			if (isset($arUpPath[$count_arUpPath - 1]))
				$parentLessonId = $arUpPath[$count_arUpPath - 1];

			// Is parent node exists
			if ($count_arUpPath >= 2)
			{
				// "Level up"
				array_pop ($arUpPath);

				$oUpPath = new CLearnPath();
				$oUpPath->SetPathFromArray ($arUpPath);

				$aContext[] = array (
					'ICON'  => 'btn_up',
					'TEXT'  => GetMessage('LEARNING_A_UP'),
					'LINK'  => 'learn_unilesson_admin.php?lang=' . LANG
						. '&PARENT_LESSON_ID=' . htmlspecialcharsbx($arUpPath[count($arUpPath) - 1])
						. '&LESSON_PATH=' . $oUpPath->ExportUrlencoded()
						. '&set_filter=Y'
						. '&' . $this->hrefSearchRetPoint,
					'TITLE' => GetMessage('LEARNING_A_UP')
					);
			}
			else
			{
				// To all lessons list
				$aContext[] = array (
					'ICON'  => 'btn_up',
					'TEXT'  => GetMessage('LEARNING_ALL_LESSONS'),
					'LINK'  => 'learn_unilesson_admin.php?lang=' . LANG
						. '&PARENT_LESSON_ID=-2'	// magic number '-2' is means 'List lessons, without relation to parent'
						. '&set_filter=Y'
						. '&' . $this->hrefSearchRetPoint,
					'TITLE' => GetMessage('LEARNING_ALL_LESSONS')
					);
			}

			unset ($arPath, $oPath, $arUpPath, $oUpPath);
		}
		else
			$PROPOSE_RETURN_LESSON_PATH = '';

		if ( ! $this->IsSearchMode() )
		{
			/**
			 * User can create lesson if he has base access for creating AND:
			 * 1) no parent lesson
			 * OR
			 * 2) if user creating lesson linked to some parent on which user has access
			 * to link childs, and user has access to link his created lesson to parent
			 */

			$isAccessCreate = false;
			try
			{
				if ($this->oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_CREATE))
				{
					if ($parentLessonId === false)
					{
						$isAccessCreate = true;
					}
					elseif (
						$this->oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_LINK_DESCENDANTS | CLearnAccess::OP_LESSON_LINK_TO_PARENTS)
						|| (
							$this->oAccess->IsLessonAccessible($parentLessonId, CLearnAccess::OP_LESSON_LINK_DESCENDANTS)
							&& $this->oAccess->IsBaseAccessForCR(CLearnAccess::OP_LESSON_LINK_TO_PARENTS)
						)
					)
					{
						$isAccessCreate = true;
					}
				}
			}
			catch (Exception $e)
			{
				;
			}

			if ($isAccessCreate)
			{
				// btn add new course
				$aContext[] =
					array(
						'ICON'  => 'btn_new',
						'TEXT'  => GetMessage('LEARNING_ADD_COURSE'),
						'LINK'  => 'learn_course_edit.php?lang=' . LANG
							. $PROPOSE_RETURN_LESSON_PATH,
						'TITLE' => GetMessage('LEARNING_ADD_COURSE_ALT')
					);

				// btn add new unilesson (non-course)
				$aContext[] =
					array(
						'ICON'  => 'btn_new',
						'TEXT'  => GetMessage('LEARNING_UNILESSON_ADD'),
						'LINK'  => 'learn_unilesson_edit.php?lang=' . LANG
							. "&PARENT_LESSON_ID=".$this->requestedParentLessonId
							. "&LESSON_PATH=".urlencode($_GET['LESSON_PATH'] ?? ''),
						'TITLE' => GetMessage('LEARNING_UNILESSON_ADD')
					);
			}
		}

		$this->oList->AddAdminContextMenu($aContext);

		return ($this);
	}

	public function RenderInto (&$html)
	{
		// list mode check (if AJAX then terminate the script)
		$this->oList->CheckListMode();

		ob_start();
		$this->ShowFilter();
		$this->ShowList();
		$html = ob_get_clean();

		return ($this);
	}

	public function IsNeedSaveInlineEditedItems()
	{
		static $cache = -1;

		if ($cache === -1)
		{
			$cache = (boolean) ($this->oList->EditAction()
				&& isset ($_POST['FIELDS'])
				&& is_array($_POST['FIELDS'])
				&& count($_POST['FIELDS']) > 0
				);
		}

		return ($cache);
	}

	public function SaveInlineEditedItems()
	{
		if ( ! $this->IsNeedSaveInlineEditedItems() )
			return ($this);

		if ( check_bitrix_sessid() !== true )
		{
			throw new CLearnRenderAdminUnilessonListException ('',
				CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED);
		}

		foreach ($_POST['FIELDS'] as $lessonId => $arFields)
		{
			$arEdgeProperties = array();

			$wasError = false;
			try
			{
				// skip not changed items
				if ( ! $this->oList->IsUpdated($lessonId) )
					continue;

				// throws exception if access denied
				$this->EnsureLessonUpdateAccess ($lessonId);

				$courseId = CLearnLesson::GetLinkedCourse($lessonId);

				// Depends on current list mode, we must update sort index
				// of element (only for course) or sort index of relation between
				// child and parent lessons.
				// So, we must rename SORT to COURSE_SORT or EDGE_SORT
				if (array_key_exists('SORT', $arFields))
				{
					if ($this->IsListAnyCoursesMode() && ($courseId !== false))
					{
						$arFields['COURSE_SORT'] = $arFields['SORT'];
					}
					elseif ($this->IsListChildLessonsMode())
					{
						$arFields['EDGE_SORT'] = $arFields['SORT'];
					}
					else
					{
						throw new CLearnRenderAdminUnilessonListException ('',
							CLearnRenderAdminUnilessonListException::C_LOGIC
							| CLearnRenderAdminUnilessonListException::C_ACTION_UPDATE_FAIL);
					}

					unset ($arFields['SORT']);
				}

				if (isset($arFields['EDGE_SORT']))
				{
					if ($this->requestedParentLessonId > 0)
					{
						$arEdgeProperties['SORT'] = $arFields['EDGE_SORT'];
					}

					unset ($arFields['EDGE_SORT']);
				}

				// PUBLISH_PROHIBITED
				if ( array_key_exists('PUBLISH_PROHIBITED', $arFields) )
				{
					// PUBLISH_PROHIBITED available in context of most parent course only
					if ( ($this->contextCourseLessonId !== false)
						&& in_array($arFields['PUBLISH_PROHIBITED'], array('N', 'Y'), true)
					)
					{
						$isProhibited = true;
						if ($arFields['PUBLISH_PROHIBITED'] === 'N')
							$isProhibited = false;

						CLearnLesson::PublishProhibitionSetTo ($lessonId, $this->contextCourseLessonId, $isProhibited);
					}

					unset ($arFields['PUBLISH_PROHIBITED']);
				}

				// Courses must be updated throws CCourse::Update();
				if ($courseId === false)
				{
					CLearnLesson::Update($lessonId, $arFields);
				}
				else
				{
					$ob = new CCourse;
					if ( ! $ob->Update($courseId, $arFields) )
					{
						throw new CLearnRenderAdminUnilessonListException (
							'', CLearnRenderAdminUnilessonListException::C_ACTION_UPDATE_FAIL);
					}
					unset ($ob);
				}

				if ($this->requestedParentLessonId > 0 && (count($arEdgeProperties) > 0) )
				{
					CLearnLesson::RelationUpdate ($this->requestedParentLessonId, $lessonId, $arEdgeProperties);
				}
			}
			catch (CLearnRenderAdminUnilessonListException $e)
			{
				$wasError  = true;
				$errorText = $e->getMessage();
				$errorCode = $e->getCode();
			}
			catch (Exception $e)
			{
				$wasError  = true;
				$errorText = $e->getMessage();
				$errorCode = 0;	// Because we checks below only CLearnRenderAdminUnilessonListException codes
			}

			if ($wasError)
			{
				if ($errorCode & CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED)
				{
					// Access denied
					$errmsg = GetMessage('LEARNING_SAVE_ERROR') . ': '
						. GetMessage('LEARNING_ACCESS_D');

					if ($errorText <> '')
						$errmsg .= (': ' . $errorText);
				}
				else
				{
					// Some error occured during update operation
					$errmsg = GetMessage('LEARNING_SAVE_ERROR') . $lessonId;

					if ($errorText <> '')
						$errmsg .= ( ' (' . $errorText . ')' );
				}

				$this->oList->AddUpdateError($errmsg, $lessonId);
			}
		}

		return ($this);
	}

	protected function ShowFilter()
	{
		global $APPLICATION;
		global $filter_name, $filter_uid, $filter_active, $filter_creator_id, $filter_keywords, $filter_lesson_type;

		?>
		<form name="form1" method="GET" action="<?php echo $APPLICATION->GetCurPage(); ?>" onsubmit="return this.set_filter.onclick();">
		<?php $this->oFilter->Begin(); ?>
			<tr>
				<td><b><?php echo GetMessage('LEARNING_NAME'); ?>:</b></td>
				<td><input type="text" name="filter_name" value="<?php echo htmlspecialcharsbx($filter_name); ?>"
					size="47">&nbsp;<?php ShowFilterLogicHelp(); ?>
				</td>
			</tr>

			<tr>
				<td>ID:</b></td>
				<td><input type="text" name="filter_uid" value="<?php echo htmlspecialcharsbx($filter_uid); ?>" size="47"></td>
			</tr>

			<tr>
				<td><?php echo GetMessage('LEARNING_COURSE_ADM_CREATED2'); ?>:</b></td>
				<td><input type="text" name="filter_creator_id" value="<?php echo htmlspecialcharsbx($filter_creator_id); ?>" size="47"></td>
			</tr>

			<tr>
				<td><?php echo GetMessage('LEARNING_F_ACTIVE'); ?>:</td>
				<td>
					<?php
					$arr = array(
						'reference'    => array(GetMessage('LEARNING_YES'), GetMessage('LEARNING_NO')),
						'reference_id' => array('Y', 'N')
						);
					echo SelectBoxFromArray('filter_active', $arr, htmlspecialcharsEx($filter_active), GetMessage('LEARNING_ALL'));
					?>
				</td>
			</tr>

			<tr>
				<td><?php echo GetMessage('LEARNING_KEYWORDS'); ?>:</b></td>
				<td><input type="text" name="filter_keywords" value="<?php echo htmlspecialcharsbx($filter_keywords); ?>" size="47"></td>
			</tr>

			<tr>
				<td><?php echo GetMessage('LEARNING_FILTER_TYPE_OF_UNILESSON'); ?>:</td>
				<td>
					<?php
					$arr = array(
						'reference'    => array(
							GetMessage('LEARNING_FILTER_TYPE_COURSE'),
							GetMessage('LEARNING_FILTER_TYPE_LESSON_WITH_CHILDS'),
							GetMessage('LEARNING_FILTER_TYPE_LESSON_WO_CHILDS')),
						'reference_id' => array(
							'COURSE',
							'LESSON_WITH_CHILDS',
							'LESSON_WO_CHILDS')
						);
					echo SelectBoxFromArray('filter_lesson_type', $arr, htmlspecialcharsEx($filter_lesson_type), GetMessage('LEARNING_ALL'));
					?>
				</td>
			</tr>

			<?php
			/*
			if ($this->requestedParentLessonId != -2)		// magic number '-2' is means 'List lessons, without relation to parent'
			{
				?>
				<tr>
					<td>SORT:</b></td>
					<td><input type="text" name="filter_sort" value="<?php echo htmlspecialcharsbx($_GET['filter_sort']); ?>" size="47"></td>
				</tr>
				<?php
			}
			*/

		$strTmpLessonPath = '';
		if (isset($_GET['LESSON_PATH']))
			$strTmpLessonPath = $_GET['LESSON_PATH'];

		$this->oFilter->Buttons(
			array(
				'table_id' => $this->tableID,
				'url'      => $APPLICATION->GetCurPage() . '?PARENT_LESSON_ID=' . $this->requestedParentLessonId
					. '&' . $this->hrefSearchRetPoint . '&LESSON_PATH=' . $strTmpLessonPath,
				'form'     => 'form1'
			)
		);
		$this->oFilter->End();
		?>
		</form>
		<?php

		return ($this);
	}

	public function ShowList()
	{
		$this->oList->DisplayList();
	}
}


// prepare buffer to be showed
$html  = '';
$title = GetMessage("LEARNING_ADMIN_TITLE");	// by default, but maybe overrided below

$wasError         = false;
$needShowAuthForm = false;
$strCAdminMessage = false;

try
{
	$oRE = new CLearnRenderAdminUnilessonList();

	$oRE->EnsureReadRights()	// broke execution flow and show auth form, if not enough access level
		->Init();				// init filter, list

	// save inline edited items
	if ($oRE->IsNeedSaveInlineEditedItems())
		$oRE->SaveInlineEditedItems();

	// process group or single actions on list's item(s)
	if ($oRE->IsNeedProcessActionsOnList())
		$oRE->ProcessActionsOnList();

	if (isset($_REQUEST['return_url']) && ($_REQUEST['return_url'] <> '') && check_bitrix_sessid())
		LocalRedirect($_REQUEST['return_url']);


	$oRE->FetchData()					// get data for list
		->BuildList()					// Build list
		->BuildListGroupActionsButton()	// Build group actions buttons in list
		->BuildListContextMenu()		// Build context menu in list
		->RenderInto($html);			// Render html into argument (if AJAX then terminate the script)
}
catch (CLearnRenderAdminUnilessonListException $e)
{
	$wasError = true;

	$errCode = $e->getCode();

	if ($errCode & CLearnRenderAdminUnilessonListException::C_COURSE_UNAVAILABLE)
		$strCAdminMessage = GetMessage('LEARNING_BAD_COURSE');
	elseif ($errCode & CLearnRenderAdminUnilessonListException::C_ACCESS_DENIED)
	{
		if ($errCode & CLearnRenderAdminUnilessonListException::C_NEED_SHOW_AUTH_FORM)
			$needShowAuthForm = true;
		else
			$strCAdminMessage = GetMessage('ACCESS_DENIED');
	}
	else
		$strCAdminMessage = GetMessage('LEARNING_ERROR') . ' (' . $e->GetMessage() . ')';
}
catch (Exception $e)
{
	$wasError = true;

	$strCAdminMessage = GetMessage('LEARNING_ERROR');

	$errmsg = $e->GetMessage();
	if ($errmsg <> '')
		$strCAdminMessage .= ' (' . $e->GetMessage() . ')';
}

$APPLICATION->SetTitle($title);

if ( ! $oRE->IsSearchMode() )
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // second system's prolog
else
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

if ($wasError)
{
	$title = GetMessage('LEARNING_LESSONS');

	if ($needShowAuthForm)
	{
		$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'), false);
	}
	else
	{
		if ($strCAdminMessage !== false)
			CAdminMessage::ShowMessage($strCAdminMessage);

		$aContext = array(
			array(
				'TEXT'  => GetMessage('LEARNING_BACK_TO_ADMIN'),
				'LINK'  => '/?lang=' . LANG,
				'TITLE' => GetMessage('LEARNING_BACK_TO_ADMIN')
			),
		);

		$context = new CAdminContextMenu($aContext);
		$context->Show();
	}
}
else
	echo $html;		// output

if ( ! $oRE->IsSearchMode() )
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');	// system's epilog
else
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_popup_admin.php');
