function BxShowComponentNotes(arParams)
{
	var cell = arParams.oCont.parentNode.cells[0];
	arParams.oCont.parentNode.deleteCell(1);
	cell.colSpan = 2;
	cell.innerHTML = '<div style="background-color:#FEFDEA; border:1px solid #D7D6BA; padding:5px; margin:5px; text-align:left;">'+arParams.data+'</div>';
}
