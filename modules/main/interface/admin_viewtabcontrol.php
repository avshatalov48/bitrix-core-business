<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */

/*View Tab Control*/
class CAdminViewTabControl
{
	var $name;
	var $tabs;
	var $selectedTab;
	var $tabIndex = 0;

	public function __construct($name, $tabs)
	{
		//array(array("DIV"=>"", "TAB"=>"", "ICON"=>, "TITLE"=>"", "ONSELECT"=>"javascript"), ...)
		if (is_array($tabs))
		{
			foreach (array_keys($tabs) as $index)
			{
				$tabs[$index]['ONSELECT'] = (string)($tabs[$index]['ONSELECT'] ?? '');
			}
		}
		$this->tabs = $tabs;
		$this->name = $name;
		if(isset($_REQUEST[$this->name."_active_tab"]))
			$this->selectedTab = $_REQUEST[$this->name."_active_tab"];
		else
		{
			foreach($tabs as $tab)
			{
				if (
					!isset($tab["VISIBLE"])
					|| $tab["VISIBLE"]
				)
				{
					$this->selectedTab = $tab["DIV"];
					break;
				}
			}
		}
	}

	function Begin()
	{
		echo '
<div class="adm-detail-subtabs-block">
';
		$i = 0;
		foreach($this->tabs as $tab)
		{
			$bSelected = ($tab["DIV"] == $this->selectedTab);
			echo '<span class="adm-detail-subtabs'.($bSelected? " adm-detail-subtab-active":"").'" id="view_tab_'.$tab["DIV"].'" onclick="'.$this->name.'.SelectTab(\''.$tab["DIV"].'\');" title="'.$tab["TITLE"].'"'.(isset($tab["VISIBLE"]) && !$tab["VISIBLE"] ? ' style="display: none;"' : '').'>'.$tab["TAB"].'</span>'."\n";
			$i++;
		}
echo '</div>';
	}

	function BeginNextTab()
	{
		//end previous tab
		$this->EndTab();

		if($this->tabIndex >= count($this->tabs))
			return;

		echo '
<div id="'.$this->tabs[$this->tabIndex]["DIV"].'"'.($this->tabs[$this->tabIndex]["DIV"] <> $this->selectedTab ? ' style="display:none;"':'').'>
	<div class="adm-detail-content-item-block-view-tab">
	<div class="adm-detail-title-view-tab">'.$this->tabs[$this->tabIndex]["TITLE"].'</div>
';
		$this->tabIndex++;
	}

	function EndTab()
	{
		if($this->tabIndex < 1 || $this->tabIndex > count($this->tabs))
			return;
		echo '
	</div>
</div>
';
	}

	function End()
	{
		$this->EndTab();
		echo '
<script type="text/javascript">
';
		$s = "";
		foreach($this->tabs as $tab)
		{
			$s .= ($s <> ""? ", ":"").
			"{".
			"'DIV': '".$tab["DIV"]."' ".
			($tab["ONSELECT"] !== '' ? ", 'ONSELECT': '".CUtil::JSEscape($tab["ONSELECT"])."'":"").
			"}";
		}
		echo 'var '.$this->name.' = new BX.adminViewTabControl(['.$s.']); ';

		if(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
		{
			echo 'window.'.$this->name.'.setPublicMode(true); ';
		}

		echo '</script>';
	}
}
