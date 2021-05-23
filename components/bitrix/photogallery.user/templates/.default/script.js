function RecalcGallerySize()
{
	var t = this;
	t.oAnchor = false;
	t.oDivOuter = false;
	t.oDivInner = false;
	t.oDivInner1 = false;
	t.oDivInner1Text = false;
	t.oSizeInner = false;
	t.oSizeInner1 = false;
	t.oSizeInner1Text = false;
	
	
	t.bReady = true;
	
	t.Start = function(oObj)
	{
		t.Init(oObj);
		if (typeof t.oAnchor != "object")
			return false;
		if (t.bReady == true)
		{
			t.Show('progress', 0);
			t.Send(false);
		}
		return false;
	}
	
	t.Continue = function(percent)
	{
		t.Show('progress', percent);
		t.Send(true);
	}
	
	t.Finish = function()
	{
		t.Show('progress', 100);
		t.Show('size', percent);
	}
	
	t.Init = function(oObj)
	{
		if (typeof oObj != "object" || oObj.href.length <= 0)
			return false;
		t.oAnchor = oObj;
		t.oDivOuter = BX('photo_progress_outer');
		t.oDivInner = BX('photo_progress_inner');
		t.oDivInner1 = BX('photo_progress_inner1');
		
		t.oSizeInner = BX('photo_gallery_size_inner');
		t.oSizeInner1 = BX('photo_gallery_size_inner1');
		return true;
	}
	
	t.Send = function(bContinue)
	{
		var oData = {"AJAX_CALL" : "Y"};
		if (bContinue == true)
			oData["status"] = "continue";

		TID = CPHttpRequest.InitThread();
		CPHttpRequest.SetAction(TID, 
			function(data)
			{
				var result = {};
				try
				{
					eval("result = " + data + ";");
					if (result["STATUS"] == "CONTINUE")
						t.Continue(result["PERCENT"]);
					else
						t.Finish(result["PERCENT"]);
				}
				catch(e)
				{}
				t.bReady = true;
				BX.closeWait();
			});
		
		BX.showWait();
		t.bReady = false;
		CPHttpRequest.Send(TID, t.oAnchor.href, oData);	
	}
	
	t.Show = function(ind, percent)
	{
		ind = (ind == 'progress' ? 'progress' : 'size');
		percent = parseInt(percent);
		if (ind == 'progress' && percent >= 100)
		{
			t.oDivOuter.style.display = 'none';
			t.oAnchor.style.display = '';
			t.oAnchor.style.visibility = 'visible';
		}
		else if (ind == 'progress')
		{
			t.oAnchor.style.display = 'none';
			t.oAnchor.style.visibility = 'hidden';
			t.oDivOuter.style.display = '';
			t.oDivInner1.innerHTML = t.oDivInner1.innerHTML.replace(/\d+/, percent);
			t.oDivInner.style.width = percent + '%';
		}
		else if (ind == 'size')
		{
			t.oSizeInner1.innerHTML = t.oSizeInner1.innerHTML.replace(/\d+/, percent);
			t.oSizeInner.style.width = percent + '%';
		}
	}
}

window.oGallery = new RecalcGallerySize();