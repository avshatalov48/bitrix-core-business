<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */

class CAdminTabControlDrag extends CAdminTabControl
{
	protected $moduleId;

	public function __construct($name, $tabs, $moduleId="", $bCanExpand = true, $bDenyAutosave = false)
	{
		parent::__construct($name, $tabs, $bCanExpand, $bDenyAutosave);
		$this->moduleId = $moduleId;
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/main/admin_dd.js");
	}

	function BeginNextTab($options = array())
	{
		if ($this->AUTOSAVE)
			$this->AUTOSAVE->Init();

		//end previous tab
		$this->EndTab();

		if($this->tabIndex >= count($this->tabs))
			return;

		$css = '';
		if ($this->tabs[$this->tabIndex]["DIV"] <> $this->selectedTab)
			$css .= 'display:none; ';

		echo '
<div class="adm-detail-content" id="'.$this->tabs[$this->tabIndex]["DIV"].'"'.($css != '' ? ' style="'.$css.'"' : '').'>';

		if (!empty($this->tabs[$this->tabIndex]["TITLE"]))
			echo '
	<div class="adm-detail-title">'.$this->tabs[$this->tabIndex]["TITLE"].'</div>';

		if ($this->tabs[$this->tabIndex]["IS_DRAGGABLE"] == "Y")
		{
			$arJsParams = array(
				"moduleId" => $this->moduleId,
				"optionName" => $this->getCurrentTabOptionName($this->tabIndex),
				"tabId" => $this->tabs[$this->tabIndex]["DIV"],
				"hidden" => $this->getTabHiddenBlocks($this->tabIndex)
			);
			echo '
			<script>
				BX.ready(function(){
					var orderObj = new BX.Admin.DraggableTab('.CUtil::PhpToJSObject($arJsParams).');
				});
			</script>';
		}

		$showWrap = $this->tabs[$this->tabIndex]["SHOW_WRAP"] == "N" ? false : true;
		echo '
	<div '.($showWrap ? 'class="adm-detail-content-item-block"' : '').'>
		<table class="adm-detail-content-table edit-table" id="'.$this->tabs[$this->tabIndex]["DIV"].'_edit_table">
			<tbody>
';
		if(array_key_exists("CUSTOM", $this->tabs[$this->tabIndex]) && $this->tabs[$this->tabIndex]["CUSTOM"] == "Y")
		{
			$this->customTabber->ShowTab($this->tabs[$this->tabIndex]["DIV"]);
			$this->tabIndex++;
			$this->BeginNextTab();
		}
		elseif(array_key_exists("CONTENT", $this->tabs[$this->tabIndex]))
		{
			echo $this->tabs[$this->tabIndex]["CONTENT"];
			$this->tabIndex++;
			$this->BeginNextTab();
		}
		else
		{
			$this->tabIndex++;
		}
	}

	function ShowTabButtons()
	{
		$s = '';
		if (!$this->bPublicMode)
		{
			if(count($this->tabs) > 1 && $this->bCanExpand/* || $this->AUTOSAVE*/)
			{
				$s .= '<div class="adm-detail-title-setting" onclick="'.$this->name.'.ToggleTabs();" title="'.GetMessage("admin_lib_expand_tabs").'" id="'.$this->name.'_expand_link"><span class="adm-detail-title-setting-btn adm-detail-title-expand"></span></div>';
			}
		}
		return $s;
	}

	function getCurrentTabOptionName($tabIdx)
	{
		return $this->name."_".$this->tabs[$tabIdx]["DIV"];
	}

	function getTabSettings($tabIdx)
	{
		if (isset($this->tabs[$tabIdx]["SETTINGS"]))
			return $this->tabs[$tabIdx]["SETTINGS"];

		$tabSettings = CUserOptions::getOption($this->moduleId, $this->getCurrentTabOptionName($tabIdx));

		$tabSettings["order"] = $tabSettings["order"] ?? array();
		if (!empty($tabSettings["order"]))
			$tabSettings["order"] = explode(",", $tabSettings["order"]);

		$tabSettings["hidden"] = $tabSettings["hidden"] ?? array();
		if (!empty($tabSettings["hidden"]))
			$tabSettings["hidden"] = explode(",", $tabSettings["hidden"]);

		$this->tabs[$tabIdx]["SETTINGS"] = $tabSettings;
		return $tabSettings;
	}

	function getCurrentTabBlocksOrder($defaultBlocksOrder = array())
	{
		$tabSettings = $this->getTabSettings($this->tabIndex-1);
		$blocksOrder = $tabSettings["order"];

		if (is_array($defaultBlocksOrder) && !empty($defaultBlocksOrder))
		{
			if (empty($blocksOrder))
			{
				$blocksOrder = $defaultBlocksOrder;
			}
			else
			{
				foreach($blocksOrder as $key => $blockCode)
				{
					if (!in_array($blockCode, $defaultBlocksOrder))
						unset($blocksOrder[$key]);
				}
				$blocksOrder = array_unique(array_merge($blocksOrder, $defaultBlocksOrder));
			}
		}

		return $blocksOrder;
	}

	function getTabHiddenBlocks($tabIdx)
	{
		$tabSettings = $this->getTabSettings($tabIdx);
		$hiddenBlocks = $tabSettings["hidden"];
		return is_array($hiddenBlocks) ? $hiddenBlocks : array();
	}

	function DraggableBlocksStart()
	{
		echo '<div data-role="dragObj" data-onlydest="Y" style="height:5px;width:100%"></div>';
	}

	function DraggableBlockBegin($title, $dataId = "")
	{
		echo '
		<div class="adm-container-draggable'.(in_array($dataId, $this->getTabHiddenBlocks($this->tabIndex-1)) ? ' hidden' : '').'" data-role="dragObj" data-id="'.$dataId.'">
			<div class="adm-bus-statusorder">
				<div class="adm-bus-component-container">
					<div class="adm-bus-component-title-container draggable">
						<div class="adm-bus-component-title-icon"></div>
						<div class="adm-bus-component-title">'.$title.'</div>
						<div class="adm-bus-component-title-icon-turn" data-role="toggleObj"></div>'.
			//'<div class="adm-bus-component-title-icon-close"></div>'
			'</div>
					<div class="adm-bus-component-content-container">
						<div class="adm-bus-table-container">';
	}

	function DraggableBlockEnd()
	{
		echo '			</div>
					</div>
				</div>
			</div>
		</div>';
	}
}


/**
 * Class CAdminDraggableBlockEngine
 * Create custom Draggable blocks for CAdminTabControlDrag
 */
class CAdminDraggableBlockEngine
{
	protected $id;
	protected $engines = array();
	protected $args = array();

	/**
	* CAdminDraggableBlockEngine constructor.
	* @param string $id identifier
	* @param array $args
	*/
	public function __construct($id, $args = array())
	{
		$this->id = $id;
		$this->args = $args;

		foreach (GetModuleEvents("main", $this->id, true) as $arEvent)
		{
			$res = ExecuteModuleEventEx($arEvent, array($args));

			if (is_array($res))
				$this->engines[$res["BLOCKSET"]] = $res;
		}
	}

	/**
	 * @param array $args
	 */
	public function setArgs($args = array())
	{
		$this->args = $args;
	}

	/**
	 * @return bool
 	 */
	public function check()
	{
		$result = true;

		foreach ($this->engines as $value)
		{
			if (array_key_exists("check", $value))
			{
				$resultTmp = call_user_func_array($value["check"], array($this->args));

				if ($result && !$resultTmp)
					$result = false;
			}
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function action()
	{
		$result = true;

		foreach ($this->engines as $value)
		{
			if (array_key_exists("action", $value))
			{
				$resultTmp = call_user_func_array($value["action"], array($this->args));

				if ($result && !$resultTmp)
					$result = false;
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getBlocksBrief()
	{
		$blocks = array();

		foreach ($this->engines as $key => $value)
		{
			if (array_key_exists("getBlocksBrief", $value))
			{
				 $tmp = call_user_func_array($value["getBlocksBrief"], array($this->args));

				if (is_array($tmp))
					$blocks = $blocks + $tmp;
			}
		}

		return $blocks;
	}

	/**
	 * @param string $blockCode
	 * @param string $selectedTab
     * @return string
     */
	public function getBlockContent($blockCode, $selectedTab)
	{
		$result = '';

		foreach ($this->engines as $key => $value)
			if (array_key_exists("getBlockContent", $value))
				 $result .= call_user_func_array($value["getBlockContent"], array($blockCode, $selectedTab, $this->args));

		return $result;
	}

	/**
	 * @return string
 	 */
	public function getScripts()
	{
		$result = '';

		foreach ($this->engines as $key => $value)
		{
			if (array_key_exists("getScripts", $value))
				 $result .= call_user_func_array($value["getScripts"], array($this->args));
		}

		return $result;
	}
}
