BX.SonetIFramePopup = function(params)
{
	this.params = params;
	this.title = '';

	this.pathToView = "";
	this.pathToCreate = "";
	this.pathToEdit = "";
	this.pathToInvite = "";

	if (params.pathToView)
		this.pathToView = this.params.pathToView;
	if (params.pathToCreate)
		this.pathToCreate = this.params.pathToCreate + (this.params.pathToCreate.indexOf("?") == -1 ? "?" : "&") + "IFRAME=Y&SONET=Y";
	if (params.pathToEdit)
		this.pathToEdit = this.params.pathToEdit + (this.params.pathToEdit.indexOf("?") == -1 ? "?" : "&") + "IFRAME=Y&SONET=Y";
	if (params.pathToInvite)
		this.pathToInvite = this.params.pathToInvite + (this.params.pathToInvite.indexOf("?") == -1 ? "?" : "&") + "IFRAME=Y&SONET=Y";

	this.width = (this.params.width ? params.width : 900);
	this.height = (this.params.height ? params.height : 400);

	this.isReady = false;

	this.popup = null;
	this.iframe = null;

	BX.addCustomEvent('onSonetIframeCallbackRefresh', BX.delegate(this.onSonetIframeCallbackRefresh, this));
	BX.addCustomEvent('onSonetIframeCallbackGroup', BX.delegate(this.onSonetIframeCallbackGroup, this));
	BX.addCustomEvent('onSonetIframeCancelClick', BX.delegate(this.Hide, this));
};

BX.SonetIFramePopup.prototype.onSonetIframeCallbackRefresh = function()
{
	if (this.popup != null && this.popup.isShown())
	{
		this.Hide();
		BX.reload();
	}
};

BX.SonetIFramePopup.prototype.onSonetIframeCallbackGroup = function(group_id)
{
	if (this.popup != null && this.popup.isShown())
	{
		this.Hide();
		top.location.href = this.pathToView.replace('#group_id#', group_id);
	}
};

BX.SonetIFramePopup.prototype.Create = function()
{
	if (this.iframe != null)
		return;

	this.iframe = BX.create('IFRAME', {
		props: {
			scrolling: "no",
			frameBorder: "0"
		},
		style: {
			width: this.width + "px",
			height: this.height + "px",
			overflow: "hidden",
			border: "1px solid #fff",
			borderTop: "0px",
			borderRadius: "4px"
		}
	});

	this.popup = BX.PopupWindowManager.create(
		'sonet_iframe_popup_' + parseInt(Math.random() * 10000),
		window.top,
		{
			autoHide: false,
			titleBar: true,
			closeIcon: true,
			draggable: true,
			overlay: true,
			content: (this.content = BX.create('DIV', {
				style: {
					width: parseInt(this.width) + 'px'
				},
				children: [ this.iframe ]
			}))
		}
	);
};


BX.SonetIFramePopup.prototype.Show = function(url)
{
	if (this.popup == null)
		this.Create();

	if (!this.popup.isShown())
	{
		var iframeDocument = null;
		if (this.iframe.contentDocument)
			iframeDocument = this.iframe.contentDocument;
		else if (this.iframe.contentWindow)
			iframeDocument = this.iframe.contentWindow.document;

		if (iframeDocument.body && iframeDocument.body.innerHTML)
			iframeDocument.body.innerHTML = '';

		this.iframe.src = url;
		this.popup.setTitleBar({content: this.GetTitle()});
		this.popup.show();
	}
};

BX.SonetIFramePopup.prototype.Hide = function()
{
	if (this.popup != null && this.popup.isShown())
		this.popup.close();
};

BX.SonetIFramePopup.prototype.Add = function(groupId, groupName)
{
	this.SetTitle(BX.message("SONET_GROUP_TITLE_CREATE"));
	this.Show(this.pathToCreate);
};

BX.SonetIFramePopup.prototype.Edit = function(groupId, groupName)
{
	this.SetTitle(BX.message("SONET_GROUP_TITLE_EDIT").replace("#GROUP_NAME#", groupName));
	this.Show(this.pathToEdit.replace("#group_id#", groupId));
};

BX.SonetIFramePopup.prototype.Invite = function(groupId, groupName)
{
	this.SetTitle(BX.message("SONET_GROUP_TITLE_INVITE").replace("#GROUP_NAME#", groupName));
	this.Show(this.pathToInvite.replace("#group_id#", groupId));
};

BX.SonetIFramePopup.prototype.SetTitle = function(title)
{
	this.title = title;
};

BX.SonetIFramePopup.prototype.GetTitle = function()
{
	return BX.create('DIV', {
		style : {
			fontFamily: "Arial,sans-serif",
			fontSize: "14px",
			margin: "2px 0 0 8px"
		},
		text: this.title
	});
};

BX.SonetIFramePopup.prototype.isOpened = function() {
	return this.popup != null && this.popup.isShown();
};