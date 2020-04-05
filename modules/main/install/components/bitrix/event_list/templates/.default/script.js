function __logFilterClick(featureId)
{
	var chkbx = document.getElementById("flt_event_id_"+featureId);
	var chkbx_tmp = false;

	var bIsAllChecked = true;
	
	for(var flt_cnt in arFltFeaturesID)
	{
		chkbx_tmp = document.getElementById("flt_event_id_"+arFltFeaturesID[flt_cnt]);
		if (null != chkbx_tmp)
		{
			if (chkbx_tmp.checked == false)
			{
				bIsAllChecked = false;
				break;
			}
		}
	}

	chkbx_tmp = document.getElementById("flt_event_id_all");	
	if (bIsAllChecked)
		chkbx_tmp.value = "Y";
	else
		chkbx_tmp.value = "";	
}

function __logFilterShow()
{
	if (BX('bx_sl_filter').style.display == 'none')
	{
		BX('bx_sl_filter').style.display = 'block';
		BX('bx_sl_filter_hidden').style.display = 'none';
	}
	else
	{
		BX('bx_sl_filter').style.display = 'none';
		BX('bx_sl_filter_hidden').style.display = 'block';
	}
}

__logOnDateChange = function(sel)
{
	var bShowFrom=false, bShowTo=false, bShowHellip=false, bShowDays=false, bShowBr=false;

	if(sel.value == 'interval')
		bShowBr = bShowFrom = bShowTo = bShowHellip = true;
	else if(sel.value == 'before')
		bShowTo = true;
	else if(sel.value == 'after' || sel.value == 'exact')
		bShowFrom = true;
	else if(sel.value == 'days')
		bShowDays = true;
	
	BX('flt_date_from_span').style.display = (bShowFrom? '':'none');
	BX('flt_date_to_span').style.display = (bShowTo? '':'none');
	BX('flt_date_hellip_span').style.display = (bShowHellip? '':'none');
	BX('flt_date_day_span').style.display = (bShowDays? '':'none');
}
