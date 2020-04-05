function BxShowAuthService(id, suffix)
{
	var bxCurrentAuthId = ''; 
	if(window['bxCurrentAuthId'+suffix])
		bxCurrentAuthId = window['bxCurrentAuthId'+suffix];

	BX('bx_auth_serv'+suffix).style.display = '';
	if(bxCurrentAuthId != '' && bxCurrentAuthId != id)
	{
		BX('bx_auth_href_'+suffix+bxCurrentAuthId).className = '';
		BX('bx_auth_serv_'+suffix+bxCurrentAuthId).style.display = 'none';
	}
	BX('bx_auth_href_'+suffix+id).className = 'bx-ss-selected';
	BX('bx_auth_href_'+suffix+id).blur();
	BX('bx_auth_serv_'+suffix+id).style.display = '';
	var el = BX.findChild(BX('bx_auth_serv_'+suffix+id), {'tag':'input', 'attribute':{'type':'text'}}, true);
	if(el)
		try{el.focus();}catch(e){}
	window['bxCurrentAuthId'+suffix] = id;
    if(document.forms['bx_auth_services'+suffix])
        document.forms['bx_auth_services'+suffix].auth_service_id.value = id;
    else if(document.forms['bx_user_profile_form'+suffix])
        document.forms['bx_user_profile_form'+suffix].auth_service_id.value = id;
}

var bxAuthWnd = false;
function BxShowAuthFloat(id, suffix)
{
	var bCreated = false;
	if(!bxAuthWnd)
	{
		bxAuthWnd = new BX.CDialog({
			'content':'<div id="bx_auth_float_container"></div>', 
			'width': 640,
			'height': 400,
			'resizable': false
		});
		bCreated = true;
	}
	bxAuthWnd.Show();

	if(bCreated)
		BX('bx_auth_float_container').appendChild(BX('bx_auth_float'));
			
	BxShowAuthService(id, suffix);
}