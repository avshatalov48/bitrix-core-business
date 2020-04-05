<?
$not_show_links = "Y";

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/auth/authorize.php");
?>

<script>
window.IsOpera = function()
{
	return (navigator.userAgent.toLowerCase().indexOf('opera') != -1);
};

window.GetParameters = function(form_name)
{
	if (null == form_name)
		var form = document.forms[this.form_name];
	else
		var form = document.forms[form_name];

	if(!form)
		return "";

	var i, s = "";
	var n = form.elements.length;
	var delim = '';
	for(i=0; i<n; i++)
	{
		if (s != '') delim = '&';

		var el = form.elements[i];
		if (el.disabled)
			continue;
		switch(el.type.toLowerCase())
		{
			case 'text':
			case 'textarea':
			case 'password':
			case 'hidden':
				if (null == form_name && el.name.substr(el.name.length-4) == '_alt' && form.elements[el.name.substr(0, el.name.length-4)])
					break;
				s += delim + el.name + '=' + jsUtils.urlencode(el.value);
				break;
			case 'radio':
				if(el.checked)
					s += delim + el.name + '=' + jsUtils.urlencode(el.value);
				break;
			case 'checkbox':
				s += delim + el.name + '=' + jsUtils.urlencode(el.checked ? 'Y':'N');
				break;
			case 'select-one':
				var val = "";
				if (null == form_name && form.elements[el.name + '_alt'] && el.selectedIndex == 0)
					val = form.elements[el.name+'_alt'].value;
				else
					val = el.value;
				s += delim + el.name + '=' + jsUtils.urlencode(val);
				break;
			case 'select-multiple':
				var j;
				var l = el.options.length;
				for (j=0; j<l; j++)
					if (el.options[j].selected)
						s += delim + el.name + '=' + jsUtils.urlencode(el.options[j].value);
				break;
			default:
				break;
		}
	}
	return s;
};

window.BXAuthForm = function()
{
	var form = document.forms["form_auth"];
	if (!form)
		return;
	var editorDialogDiv = document.getElementById("BX_editor_dialog");

	if (!window.BX && top.BX)
		window.BX = top.BX;

	if (window != top)
	{
		top.originalName = top.jsPopup.form_name;
		top.jsPopup.form_name = "form_auth";

		top.obDiv = top.document.createElement('DIV');

		if (IsOpera() || !document.attachEvent)
		{
			top.obForm = form.cloneNode(true);
		}
		else
		{
			top.obForm = top.document.createElement('FORM');
			top.obForm.innerHTML = form.innerHTML;
		}

		if (!top.jsPopup.div)
			return BX.reload();

		var offset = top.jsPopup.div.firstChild.offsetHeight;

		top.obDiv.style.position = 'absolute';
		top.obDiv.style.zIndex = 1000;
		top.obDiv.style.top = (offset + 1) + 'px';
		top.obDiv.style.left = '0px';

		top.obDiv.style.backgroundColor = '#F9FAFD';
		top.obDiv.style.height = (top.jsPopup.div.offsetHeight - offset - 2) + 'px';
		top.obDiv.style.width = (top.jsPopup.div.offsetWidth - 2) + 'px';

		top.jsPopup.div.appendChild(top.obDiv);
		top.obDiv.appendChild(top.obForm);

		top.obForm.name = form.name;
		top.obForm.action = form.action;
		top.obForm.method = 'POST';

		top.obForm.target = 'file_edit_form_target';
		top.obForm.onsubmit = function() {top.ShowWaitWindow(); top.jsPopup.form_name = top.originalName; return true;};
		top.obForm.USER_LOGIN.focus();

		top.CloseWaitWindow();
	}
	else if(editorDialogDiv) // Handle authorization lost in editor dialogs
	{
		editorDialogDiv.style.width = "520px";
		editorDialogDiv.childNodes[1].style.paddingLeft = "10px";
		editorDialogDiv.childNodes[1].style.backgroundColor = "#F9FAFD";

		document.getElementById('BX_editor_dialog_title').innerHTML = '<?=GetMessage("AUTH_PLEASE_AUTH")?>';
		jsFloatDiv.AdjustShadow(editorDialogDiv);

		form.onsubmit = function()
		{
			var r = new JCHttpRequest();
			r.Action = function(){jsUtils.onCustomEvent('onEditorLostSession');};
			r.Post('/bitrix/admin/fileman_admin.php?login=yes', window.GetParameters('form_auth'));
			return false;
		};
	}
	else
	{
		var originaName = jsPopup.form_name;
		jsPopup.form_name = "form_auth";
		form.onsubmit = function() {jsPopup.PostParameters("login=yes"); jsPopup.form_name = originaName; return false;};
	}
}

BXAuthForm();
</script>