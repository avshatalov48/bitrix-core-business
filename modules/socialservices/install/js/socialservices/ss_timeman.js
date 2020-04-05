;(function(){

if (BX.SocservTimeman)
	return;

	var SSPoint = '/bitrix/tools/oauth/socserv.ajax.php',
		intervals = {
			OPENED: 60000,
			CLOSED: 30000,
			EXPIRED: 30000,
			START: 30000
		},
		selectedTimestamp = 0,
		errorReport = '',
		SITE_ID = BX.message('SITE_ID'),
		calendarLastParams = null,

		waitDiv = null,
		waitTime = 1000,
		waitPopup = null,
		waitTimeout = null;

BX.SocservTimeman = function()
{
};

BX.SocservTimeman.prototype.closeWnd = function(e)
{
	if(window.myPopup)
		window.myPopup.close();
	else if(this.popup)
		this.popup.close();
	return (e || window.event) ? BX.PreventDefault(e) : true;
}

BX.SocservTimeman.prototype.showWnd = function()
{
	this.popup_id = 'ss-popup-send-message';
	var defaultMessageStart = BX.message('JS_CORE_SS_WORKDAY_START');
	var defaultMessageEnd = BX.message('JS_CORE_SS_WORKDAY_END');
	if(document.getElementById("ss-textarea-message-start") != null)
		defaultMessageStart = document.getElementById("ss-textarea-message-start").value;
	if(document.getElementById("ss-textarea-message-end") != null)
		defaultMessageEnd = document.getElementById("ss-textarea-message-end").value;

	if(this.popup)
	{
        this.popup.setBindElement(BX('tm_popup_social_btn'));
		this.popup.show();
		return;
	}
	var userAccounts = '';
	this.popup_buttons = this.popup_buttons || [
		new BX.PopupWindowButton({
			text : BX.message('JS_CORE_TM_B_SAVE'),
			className : "popup-window-button-accept",
			events : {click : BX.proxy(this.saveValue, this)}
		})
	];
	for(var key in  window.SOCSERV_DATA["SOCSERVARRAYALL"]) {
		userAccounts = userAccounts +
			'<tr><td class="bx-ss-soc-serv"><input type="checkbox" id="provider_id_'+key+
			'" value="'+key+
			'"><i style="cursor: default;" class="bx-ss-icon '+
			window.SOCSERV_DATA["SOCSERVARRAYALL"][key].toLowerCase()+'"></i>'+ window.SOCSERV_DATA["SOCSERVARRAYALL"][key]+
			'</td></tr>';
	}
	if(userAccounts == '')
		userAccounts = '<tr><td class="bx-ss-soc-serv-setup">'+window.SOCSERV_DATA["SETUP_MESSAGE"]+'</td></tr>';
	this.popup = new BX.PopupWindow(this.popup_id, BX('tm_popup_social_btn'), {
		draggable: false,
		closeIcon:true,
		autoHide: true,
		offsetLeft:-100,
		zIndex:1000,
		closeByEsc: true,
		bindOptions: {forceBindPosition: true},
        angle : {
            position: "top",
            offset : 124
        },
		content:
			'<div><div class="bx-ss-timeman-header-div">'+BX.message('JS_CORE_SS_SEND_TO_SOCSERV')+'</div>' +
				'<input type="checkbox" class="checkbox-class" value="Y" id="ss-day-start-checkbox">' +
				'<label for="ss-day-start-checkbox">' + BX.message('JS_CORE_SS_SEND_TO_START') + '</label>' +
				'<span class="bx-spacer-vert"></span><br>' +
				'<textarea class="ss-text-for-message" id="ss-textarea-message-start">'+defaultMessageStart+'</textarea><br>' +
				'<span class="bx-spacer-vert25"></span>' +
				'<input type="checkbox" class="adm-checkbox adm-designed-checkbox" value="Y" id="ss-day-end-checkbox">' +
				'<label for="ss-day-end-checkbox" class="adm-designed-checkbox-label">' + BX.message('JS_CORE_SS_SEND_TO_END') + '</label>' +
				'<span class="bx-spacer-vert"></span><br>' +
				'<textarea class="ss-text-for-message" id="ss-textarea-message-end">'+defaultMessageEnd+'</textarea><br>' +
				'</div>' +
				'<span class="bx-spacer-vert"></span>' +
				'<div class="bx-auth-serv-icons"><table>'
				+userAccounts+'</table></div>'
	});

	this.popup.setButtons(this.popup_buttons);
    this.popup.setBindElement(BX('tm_popup_social_btn'));
	this.popup.show();
	window.myPopup = this.popup;
	this.setValue(window.SOCSERV_DATA, true);
}

BX.SocservTimeman.prototype.saveValue = function(e)
{
	var startSend = document.getElementById("ss-day-start-checkbox");
	var endSend = document.getElementById("ss-day-end-checkbox");
	myDataObj = new Object();
	var socServArray = [];
	myDataObj.STARTTEXT = document.getElementById("ss-textarea-message-start").value;
	myDataObj.ENDTEXT = document.getElementById("ss-textarea-message-end").value;
	for(var key in  window.SOCSERV_DATA["SOCSERVARRAYALL"]) {
		checkBox = document.getElementById("provider_id_"+key);
		if(checkBox.checked == true)
		{
			socServArray[key] = (window.SOCSERV_DATA["SOCSERVARRAYALL"][key]);
		}
	}
	myDataObj.SOCSERVARRAY = socServArray;
	if(startSend.checked == true)
		myDataObj.STARTSEND = "Y";
	if(endSend.checked == true)
		myDataObj.ENDSEND = "Y";

	BX.SocservTimeman_query(myDataObj);
}

BX.SocservTimeman.prototype.setValue = function(data, check)
{
	var sendToSocServ = BX("ss-send-to-socserv");

	// var TASKS = DATA.TASKS.length;
	// var EVENTS = DATA.EVENTS.length;

	if(startSend = BX("ss-day-start-checkbox"))
		if(data.STARTSEND == 'Y')
			startSend.checked = true;
	if(endSend = BX("ss-day-end-checkbox"))
		if(data.ENDSEND == 'Y')
			endSend.checked = true;
	if(startText = BX("ss-textarea-message-start"))
		startText.value = data.STARTTEXT;
	if(endText = BX("ss-textarea-message-end"))
	{
		endText.value = data.ENDTEXT;//.replace("#task#", TASKS).replace("#event#", EVENTS);
	}
	if(data.ENABLED == 'Y')
		sendToSocServ.checked = true;
	if(check === true)
	{
		for(var key in window.SOCSERV_DATA["SOCSERVARRAYALL"]) {
			checkBox = BX("provider_id_"+key);
			for(var key2 in window.SOCSERV_DATA["SOCSERVARRAY"]) {
				if(window.SOCSERV_DATA["SOCSERVARRAYALL"][key] == window.SOCSERV_DATA["SOCSERVARRAY"][key2])
					checkBox.checked = true;
			}
			if(data.SOCSERVARRAY == true)
			{
				socServArray.push(window.SOCSERV_DATA["SOCSERVARRAYALL"][key]);
			}
		}
	}
}

BX.SocservTimeman_query = function(myDataObj)
{
	var query_data = {
		'method': 'POST',
		'dataType': 'json',
		'timeout': 90,
		'url': '/bitrix/tools/oauth/socserv.ajax.php?action=saveuserdata&site_id=' + SITE_ID + '&sessid=' + BX.bitrix_sessid(),
		'data':  BX.ajax.prepareData(myDataObj),
		'onsuccess': BX.delegate(function(data) {
			BX.SocservTimeman.prototype.closeWnd(this);
		}),
		'onfailure': BX.delegate(function(data) {
			BX.SocservTimeman.prototype.closeWnd(this);
		})
	};
	return BX.ajax(query_data);
}

})();