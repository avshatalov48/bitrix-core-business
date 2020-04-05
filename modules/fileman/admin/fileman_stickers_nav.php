<div class="bxstl-navi-prev-next-cnt">
	<a href="javascript: void('');" onclick="if (window.oBXSticker){window.oBXSticker.List.NaviGet('<?= ($this->NavPageNomer - 1)?>', '<?= $this->NavNum?>');}; return false;"><?= GetMessage('FMST_LIST_PREV')?></a>
	<a href="javascript: void('');" onclick="if (window.oBXSticker){window.oBXSticker.List.NaviGet('<?= ($this->NavPageNomer + 1)?>', '<?= $this->NavNum?>');}; return false;"><?= GetMessage('FMST_LIST_NEXT')?></a>
</div>

<div class="bxstl-navi-pages-cnt">
<?
$NavRecordGroup = $nStartPage;
while($NavRecordGroup <= $nEndPage)
{
?>
	<? if($NavRecordGroup == $this->NavPageNomer) :?>
		<?
		$w = 20;
		if ($NavRecordGroup > 9)
			$w = 30;
		if ($NavRecordGroup > 99)
			$w = 40;
		?>
		<div class="bxstl-navi-item-cur" style="width: <?=$w?>px;"><div class="bxstl-navi-it-l"></div><div class="bxstl-navi-it-c"><?= $NavRecordGroup?></div><div class="bxstl-navi-it-r"></div></div>
	<?else:?>
		<a class="bxstl-navi-item" href="javascript: void('');" onclick="if (window.oBXSticker){window.oBXSticker.List.NaviGet('<?= ($NavRecordGroup)?>', '<?= $this->NavNum?>');}; return false;"><?= $NavRecordGroup?></a>
	<?endif;?>
<?
	$NavRecordGroup++;
}
?>
</div>

<?
// if($this->NavPageNomer > 1)
  // echo('<a class="tablebodylink" href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.($this->NavPageNomer-1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.$sPrev.'</a>');

// if($this->NavPageNomer < $this->NavPageCount)
  // echo ('<a class="tablebodylink" href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.
  // ($this->NavPageNomer+1).$strNavQueryString.'#nav_start'.$add_anchor.'">'.
  // $sNext.'</a> | <a class="tablebodylink" href="'.$sUrlPath.'?PAGEN_'.
  // $this->NavNum.'='.$this->NavPageCount.$strNavQueryString.
  // '#nav_start'.$add_anchor.'">'.$sEnd.'</a> ');

// $NavRecordGroup = $nStartPage;
// while($NavRecordGroup <= $nEndPage)
// {
  // if($NavRecordGroup == $this->NavPageNomer) 
    // echo('<b>'.$NavRecordGroup.'</b> '); 
  // else
    // echo('<a class="tablebodylink" href="'.$sUrlPath.'?PAGEN_'.$this->NavNum.'='.
	// $NavRecordGroup.$strNavQueryString.'#nav_start'.$add_anchor.'">'.
	// $NavRecordGroup.'</a> ');

  // $NavRecordGroup++;
// }

?>
