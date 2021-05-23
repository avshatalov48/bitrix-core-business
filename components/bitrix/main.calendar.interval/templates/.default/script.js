function BxCalendarInterval()
{
	this.OnDateChange = function(sel)
	{
		var bShowFrom = false, bShowTo = false, bShowHellip = false, bShowDays = false, bShowBr = false;

		if (sel.value == 'interval')
			bShowBr = bShowFrom = bShowTo = bShowHellip = true;
		else if (sel.value == 'before')
			bShowTo = true;
		else if (sel.value == 'after' || sel.value == 'exact')
			bShowFrom = true;
		else if (sel.value == 'days')
			bShowDays = true;

		BX.findNextSibling(sel, {'tag': 'span', 'class': 'bx-filter-from'}).style.display = (bShowFrom ? '' : 'none');
		BX.findNextSibling(sel, {'tag': 'span', 'class': 'bx-filter-to'}).style.display = (bShowTo ? '' : 'none');
		BX.findNextSibling(sel, {
			'tag': 'span',
			'class': 'bx-filter-hellip'
		}).style.display = (bShowHellip ? '' : 'none');
		BX.findNextSibling(sel, {'tag': 'span', 'class': 'bx-filter-days'}).style.display = (bShowDays ? '' : 'none');
		var span = BX.findNextSibling(sel, {'tag': 'span', 'class': 'bx-filter-br'});
		if (span)
			span.style.display = (bShowBr ? '' : 'none');
	};
}

var bxCalendarInterval = new BxCalendarInterval();
