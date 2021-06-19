<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
class CForumTabControl
{
	var $name, $unique_name;
	var $tabs;
	var $selectedTab;
	var $tabIndex = 0;
	var $bButtons = false;
	var $bCanExpand;

	var $customTabber;

	public function __construct($name, $tabs, $bCanExpand=true)
	{
		//array(array("DIV"=>"", "TAB"=>"", "ICON"=>, "TITLE"=>"", "ONSELECT"=>"javascript"), ...)
		$this->tabs = $tabs;
		$this->name = $name;
		$this->unique_name = $name."_".md5($GLOBALS["APPLICATION"]->GetCurPage());
		$this->bCanExpand = $bCanExpand;
		if(isset($_REQUEST[$this->name."_active_tab"]))
			$this->selectedTab = $_REQUEST[$this->name."_active_tab"];
		else
			$this->selectedTab = $tabs[0]["DIV"];
	}

	function Begin()
	{
		echo '
		<div class="forum-tabs">
			<div class="forum-tabs-header">
				<div class="forum-profile-edit">
					<div class="forum-profile-edit-inner">
';
		$nTabs = count($this->tabs);
		$i = 0;
		foreach($this->tabs as $tab)
		{
			$bSelected = ($tab["DIV"] == $this->selectedTab);
			echo '
						<div title="'.$tab["TITLE"].'" id="tab_cont_'.$tab["DIV"].'" class="forum-info-box tab-container'.($bSelected? "-selected":"").'" onClick="'.$this->name.'.SelectTab(\''.$tab["DIV"].'\');" onMouseOver="'.$this->name.'.HoverTab(\''.$tab["DIV"].'\', true);" onMouseOut="'.$this->name.'.HoverTab(\''.$tab["DIV"].'\', false);">
							<div  class="forum-info-box-inner tab'.($bSelected? "-selected":"").'" id="tab_'.$tab["DIV"].'">'.$tab["TAB"].'</div>
						</div>
';
			$i++;
		}
		echo '
					</div>
				</div>
			</div>
			<div class="forum-tabs-body">
				<div class="forum-info-box forum-profile-edit">
					<div class="forum-info-box-inner forum-profile-edit-inner">
';
	}

	function BeginNextTab()
	{
		//end previous tab
		$this->EndTab();

		if($this->tabIndex >= count($this->tabs))
			return;

		echo '
<div class="forum-profile-edit-tab" id="'.$this->tabs[$this->tabIndex]["DIV"].'"'.($this->tabs[$this->tabIndex]["DIV"] <> $this->selectedTab? ' style="display:none;"':'').'>
<table cellpadding="0" cellspacing="0" border="0" class="forum-table forum-tab" id="'.$this->tabs[$this->tabIndex]["DIV"].'_edit_table">
';
		if(array_key_exists("CUSTOM", $this->tabs[$this->tabIndex]) && $this->tabs[$this->tabIndex]["CUSTOM"] == "Y")
		{
			$this->customTabber->ShowTab($this->tabs[$this->tabIndex]["DIV"]);
			$this->tabIndex++;
			$this->BeginNextTab();
		}
		else
		{
			$this->tabIndex++;
		}
	}

	function EndTab()
	{
		if($this->tabIndex < 1 || $this->tabIndex > count($this->tabs) || $this->tabs[$this->tabIndex-1]["_closed"] === true)
			return;

		echo '
</table>
</div>
';
		$this->tabs[$this->tabIndex-1]["_closed"] = true;
	}

	function End()
	{
		while ($this->tabIndex < count($this->tabs))
			$this->BeginNextTab();

		//end previous tab
		$this->EndTab();

		echo '
					</div>
				</div>
			</div>
		</div>

<input type="hidden" id="'.$this->name.'_active_tab" name="'.$this->name.'_active_tab" value="'.htmlspecialcharsbx($this->selectedTab).'">

<script>';
		$s = "";
		foreach($this->tabs as $tab)
		{
			$s .= ($s <> ""? ", ":"").
			"{".
			"'DIV': '".$tab["DIV"]."' ".
			($tab["ONSELECT"] <> ""? ", 'ONSELECT': '".CUtil::JSEscape($tab["ONSELECT"])."'":"").
			"}";
		}
		echo '
var '.$this->name.' = new TabControl("'.$this->name.'", "'.$this->unique_name.'", ['.$s.']);';
		echo '
'.$this->name.'.InitEditTables();
jsUtils.addEvent(window, "unload", function(){'.$this->name.'.Destroy();});
</script>
';
	}
}
?>
