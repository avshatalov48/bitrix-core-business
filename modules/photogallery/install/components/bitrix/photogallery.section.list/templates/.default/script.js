function EditAlbum(url)
{
	var oEditAlbumDialog = new BX.CDialog({
		title : '',
		content_url: url + (url.indexOf('?') !== -1 ? "&" : "?") + "AJAX_CALL=Y",
		buttons: [BX.CDialog.btnSave, BX.CDialog.btnCancel],
		width: 600,
		height: 400
	});
	oEditAlbumDialog.Show();

	BX.addCustomEvent(oEditAlbumDialog, "onWindowRegister", function(){
		oEditAlbumDialog.adjustSizeEx();
		var pName = BX('bxph_name');

		if (pName) // Edit album properies
		{
			BX.focus(pName);
			if (BX('bxph_pass_row'))
			{
				BX('bxph_use_password').onclick = function()
				{
					var ch = !!this.checked;
					BX('bxph_pass_row').style.display = ch ? '' : 'none';
					BX('bxph_photo_password').disabled = !ch;
					if (ch)
						BX.focus(BX('bxph_photo_password'));

					oEditAlbumDialog.adjustSizeEx();
				};
			}
		}
		else // Edit album icon
		{
		}
	});

	oEditAlbumDialog.ClearButtons();
	oEditAlbumDialog.SetButtons([
		new BX.CWindowButton(
		{
			title: BX.message('JS_CORE_WINDOW_SAVE'),
			id: 'savebtn',
			action: function()
			{
				var pForm = oEditAlbumDialog.Get().getElementsByTagName('form')[0];
				if (pForm.action.indexOf('icon') == -1)
					CheckForm(pForm);
				else // Edit album icon
					CheckFormEditIcon(pForm);
			}
		}),
		oEditAlbumDialog.btnCancel
	]);

	window.oPhotoEditAlbumDialog = oEditAlbumDialog;
}

function CheckForm(form)
{
	if (typeof form != "object")
		return false;

	oData = {"AJAX_CALL" : "Y"};
	for (var ii in form.elements)
	{
		if (form.elements[ii] && form.elements[ii].name)
		{
			if (form.elements[ii].type && form.elements[ii].type.toLowerCase() == "checkbox")
			{
				if (form.elements[ii].checked == true)
					oData[form.elements[ii].name] = form.elements[ii].value;
			}
			else
				oData[form.elements[ii].name] = form.elements[ii].value;
		}
	}

	BX.showWait('photo_window_edit');
	window.oPhotoEditAlbumDialogError = false;

	BX.ajax.post(
		form.action,
		oData,
		function(data)
		{
			setTimeout(function(){
				BX.closeWait('photo_window_edit');
				result = {};

				if (window.oPhotoEditAlbumDialogError !== false)
				{
					var errorTr = BX("bxph_error_row");
					errorTr.style.display = "";
					errorTr.cells[0].innerHTML = window.oPhotoEditAlbumDialogError;
					window.oPhotoEditAlbumDialog.adjustSizeEx();
				}
				else
				{
					try
					{
						eval("result = " + data + ";");
						if (result['url'] && result['url'].length > 0)
							BX.reload(result['url']);

						var arrId = {"NAME" : "photo_album_name_", "DATE" : "photo_album_date_", "DESCRIPTION" : "photo_album_description_"};
						for (var ID in arrId)
						{
							if (BX(arrId[ID] + result['ID']))
								BX(arrId[ID] + result['ID']).innerHTML = result[ID];
						}
						var res = BX('photo_album_info_' + result['ID']);

						if (res)
						{
							if (result['PASSWORD'].length <= 0)
								res.className = res.className.replace("photo-album-password", "");
							else
								res.className += " photo-album-password ";
						}
						window.oPhotoEditAlbumDialog.Close();
					}
					catch(e)
					{
						var errorTr = BX("bxph_error_row");
						errorTr.style.display = "";
						errorTr.cells[0].innerHTML = BXPH_MESS.UnknownError;
						window.oPhotoEditAlbumDialog.adjustSizeEx();
					}
				}
			}, 200);
		}
	);
}

function CheckFormEditIcon(form)
{
	if (typeof form != "object")
		return false;

	oData = {"AJAX_CALL" : "Y"};
	for (var ii in form.elements)
	{
		if (form.elements[ii] && form.elements[ii].name)
		{
			if (form.elements[ii].type && form.elements[ii].type.toLowerCase() == "checkbox")
			{
				if (form.elements[ii].checked == true)
					oData[form.elements[ii].name] = form.elements[ii].value;
			}
			else
				oData[form.elements[ii].name] = form.elements[ii].value;
		}
	}
	oData["photos"] = [];
	for (var ii = 0; ii < form.elements["photos[]"].length; ii++)
	{
		if (form.elements["photos[]"][ii].checked == true)
			oData["photos"].push(form.elements["photos[]"][ii].value);
	}

	BX.showWait('photo_window_edit');
	window.oPhotoEditIconDialogError = false;

	BX.ajax.post(
		form.action,
		oData,
		function(data)
		{
			setTimeout(function(){
				BX.closeWait('photo_window_edit');
				var result = {};

				if (window.oPhotoEditIconDialogError !== false)
				{
					var errorCont = BX("bxph_error_cont");
					errorCont.style.display = "";
					errorCont.innerHTML = window.oPhotoEditIconDialogError + "<br/>";
					window.oPhotoEditAlbumDialog.adjustSizeEx();
				}
				else
				{
					try
					{
						eval("result = " + data + ";");
					}
					catch(e)
					{
						result = {};
					}

					if (parseInt(result["ID"]) > 0)
					{
						if (BX("photo_album_img_" + result['ID']))
							BX("photo_album_img_" + result['ID']).src = result['SRC'];
						else if (BX("photo_album_cover_" + result['ID']))
							BX("photo_album_cover_" + result['ID']).style.backgroundImage = "url('" + result['SRC'] + "')";
						window.oPhotoEditAlbumDialog.Close();
					}
					else
					{
						var errorTr = BX("bxph_error_row");
						errorTr.style.display = "";
						errorTr.cells[0].innerHTML = BXPH_MESS.UnknownError;
						window.oPhotoEditAlbumDialog.adjustSizeEx();
					}
				}
			}, 200);
		}
	);
}

function DropAlbum(url, id)
{
	BX.showWait('photo_window_edit');
	window.oPhotoEditAlbumDialogError = false;

	if (id > 0)
	{
		var pAlbum = BX("photo_album_info_" + id);
		if (pAlbum)
			pAlbum.style.display = "none";
	}

	BX.ajax.post(
		url,
		{"AJAX_CALL" : "Y"},
		function(data)
		{
			setTimeout(function(){
				BX.closeWait('photo_window_edit');

				if (window.oPhotoEditAlbumDialogError !== false)
					return alert(window.oPhotoEditAlbumDialogError);

				try
				{
					eval("result = " + data + ";");
					if (result['ID'])
					{
						var pAlbum = BX("photo_album_info_" + result['ID']);
						if (pAlbum && pAlbum.parentNode)
							pAlbum.parentNode.removeChild(pAlbum);
					}
				}
				catch(e)
				{
					if (id > 0)
					{
						var pAlbum = BX("photo_album_info_" + id);
						if (pAlbum && pAlbum.parentNode)
							pAlbum.style.display = "";
					}

					if (window.BXPH_MESS)
						return alert(window.BXPH_MESS.UnknownError);
				}
			}, 200);
		}
	);

	return false;
}

window.__photo_check_name_length_count = 0;
function __photo_check_name_length()
{
	var nodes = document.getElementsByTagName('a');
	var result = false;
	for (var ii = 0; ii < nodes.length; ii++)
	{
		var node = nodes[ii];
		if (!node.id.match(/photo\_album\_name\_(\d+)/gi))
			continue;
		result = true;
		if (node.offsetHeight <= node.parentNode.offsetHeight)
			continue;
		var div = node.parentNode;
		var text = node.innerHTML.replace(/\<wbr\/\>/gi, '').replace(/\<wbr\>/gi, '').replace(/\&shy\;/gi, '');
		while (div.offsetHeight < node.offsetHeight || div.offsetWidth < node.offsetWidth)
		{
			if ((div.offsetHeight  < (node.offsetHeight / 2)) || (div.offsetWidth < (node.offsetWidth / 2)))
				text = text.substr(0, parseInt(text.length / 2));
			else
				text = text.substr(0, (text.length - 2));
			node.innerHTML = text;
		}
		node.innerHTML += '...';
		if (div.offsetHeight < node.offsetHeight || div.offsetWidth < node.offsetWidth)
			node.innerHTML = text.substr(0, (text.length - 3)) + '...';
	}
	if (!result)
	{
		window.__photo_check_name_length_count++;
		if (window.__photo_check_name_length_count < 7)
			setTimeout(__photo_check_name_length, 250);
	}
}
setTimeout(__photo_check_name_length, 250);