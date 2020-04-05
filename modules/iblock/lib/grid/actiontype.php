<?php
namespace Bitrix\Iblock\Grid;

class ActionType
{
	public const EDIT = 'edit';
	public const SELECT_ALL = 'for_all';
	public const DELETE = 'delete';
	public const CLEAR_COUNTER = 'clear_counter';
	public const CODE_TRANSLIT = 'code_translit';
	public const ACTIVATE = 'activate';
	public const DEACTIVATE = 'deactivate';
	public const MOVE_TO_SECTION = 'section';
	public const ADD_TO_SECTION = 'add_section';
	public const ELEMENT_LOCK = 'lock';
	public const ELEMENT_UNLOCK = 'unlock';
	public const ELEMENT_WORKFLOW_STATUS = 'wf_status';
}