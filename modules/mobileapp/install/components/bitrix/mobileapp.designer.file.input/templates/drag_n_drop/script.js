(function() {
if (window.BlogBFileDialog)
	return;
window.BlogBFileDialogUniqueID = [];
window.BlogBFileDialog = function(arParams)
{
	this.dialogName = 'AttachmentsDialog';
	this.agent = false;
	this.appCode = arParams.appCode;
	this.uploadFileUrl = arParams.upload_path; // from file.input php

	this.id = (!!arParams["id"] ? arParams["id"] : this.getID());
	this.enabled = true;

	this.controller = (!! arParams.controller ) ? arParams.controller : null;
	this.fileInput = arParams.fileInput;
	arParams.hAttachEvents = BX.delegate(this.InitAgent, this);

	this.msg = arParams.msg;
	this.dropAutoUpload = arParams.dropAutoUpload;
	this.CID = arParams.CID;
	this.multiple = !!arParams.multiple;

	arParams.caller = this;
	arParams.classes = {
		'uploaderParent' : 'file-uploader',
		'uploader' : 'file-fileUploader',
		'tpl_simple' : 'file-simple',
		'tpl_extended' : 'file-extended',
		'selector' : 'file-selector',
		'selector_active' : 'file-selector-active'
	};
	arParams.doc_prefix = 'wd-doc';
	arParams.placeholder = BX.findChild(this.controller, {'className': 'file-placeholder-tbody'}, true);
	this.doc_prefix = arParams.doc_prefix;

	if (!!BX.FileUploadAgent) {
		this.agent = new BX.FileUploadAgent(arParams);
		BX.addCustomEvent(this, 'ShowUploadedFile', BX.delegate(this.ShowUploadedFile, this));
		BX.addCustomEvent(this, 'StopUpload', BX.delegate(this.StopUpload, this));
		BX.onCustomEvent(BX(this.controller.parentNode), "BFileDLoadFormControllerInit", [this]);
	} else {
		BX.debug('/bitrix/components/bitrix/main.file.input/templates/drag_n_drop/script.js: BX.FileUploadAgent is not defined.' +
			' You need to load /bitrix/js/main/file_upload_agent.js');
	}
}

window.BlogBFileDialog.prototype.getID = function() {
	return '' + new Date().getTime();
}

window.BlogBFileDialog.prototype.InitAgent = function(agent)
{
	if (this.controller) {
		agent.placeholder = BX.findChild(this.controller, {'className': 'file-placeholder-tbody'}, true);
	}
}

window.BlogBFileDialog.prototype.ShowUploadedFile = function(agent) // event
{
	this.agent = agent;
	var uploadResult = agent.uploadResult;

	if (uploadResult && (uploadResult.element_id > 0)) {
		if (!!agent.inputName && agent.inputName.length > 0) {
			var hidden = BX.create('INPUT', {
				props: {
					'id': 'file-doc'+uploadResult.element_id,
					'type': 'hidden',
					'name': agent.inputName + (this.multiple ? '[]' : ''),
					'value': uploadResult.element_id
				}
			});
			agent.controller.appendChild(hidden);
		}
		this.CreateFileRow(uploadResult);
		agent._clearPlace();

		if (this.controller && this.controller.parentNode)
			BX.onCustomEvent(this.controller.parentNode, 'OnFileUploadSuccess', [uploadResult, this]);

	} else {
		agent.ShowUploadError(this.msg.upload_error);

		if (this.controller && this.controller.parentNode)
			BX.onCustomEvent(this.controller.parentNode, 'OnFileUploadFail');
	}
}

window.BlogBFileDialog.prototype.CreateFileRow = function(result)
{
	var res = result;
	var mode = 'file';
	if (!! res.element_content_type && (res.element_content_type.indexOf('image/') == 0) &&
		!!res.element_image && (res.element_image.length > 0) &&
		!!res.element_thumbnail && (res.element_thumbnail.length > 0) ) {
		mode = 'image';
	}

	var tpl = BX("file-" + mode + "-template");

	BX.template(tpl, BX.delegate(function(node) {
		this.tplFileRow(node, res);
	}, this));
	var newNode = BX.clone(tpl);

	if (mode == 'image') {
		var span = null;
		for (i=0;i<newNode.children.length;i++)
		{
			span = newNode.children[i];
			if (span.nodeType == 1)
				break;
		}

		span.setAttribute('id', this.doc_prefix + result.element_id);
		var closeControl = BX.findChild(span, {'className': 'feed-add-post-del-but'}, true);
		BX.bind(closeControl, 'click', BX.delegate(
			function() {
				var control = closeControl;
				var parent = control.parentNode;
				this.agent.StopUpload(parent);
				BX.cleanNode(parent, true);
			}, this));
		this.agent.AddNodeToPlaceholder(span);
	} else {
		newNode.setAttribute('id', this.doc_prefix + result.element_id);
		this.agent.AddRowToPlaceholder(newNode);
	}
	return newNode;
}

window.BlogBFileDialog.prototype.GetUploadDialog = function(agent)
{

	return new BlogBFileDialogUploader(this, agent);
}

window.BlogBFileDialog.prototype.tplFileRow = function(nodes, res)
{
	for (id in nodes)
	{
		if (! nodes.hasOwnProperty(id))
			continue;

		var node = nodes[id];

		if ((id == 'image') &&
			!!res.element_image && (res.element_image.length > 0) &&
			!!res.element_thumbnail && (res.element_thumbnail.length > 0))
		{
			node.setAttribute('src', res.element_image);
			node.setAttribute('rel', res.element_thumbnail);
		}
		else
		{
			if (!! res['element_'+id])
				node.innerHTML = res['element_'+id];
		}
	}
}

window.BlogBFileDialog.prototype._addUrlParam = function(url, param)
{
	if (!url)
		return null;
	if (url.indexOf(param) == -1)
		url += ((url.indexOf('?') == -1) ? '?' : '&') + param ;
	return url;
}

window.BlogBFileDialog.prototype.LoadDialogs = function(dialogs)
{
	if (!!this.agent)
		this.agent.LoadDialogs(dialogs);
	else {
		var dlgs = dialogs;
		setTimeout(BX.delegate(function() {this.LoadDialogs(dlgs);}, this), 100);
	}
}

window.BlogBFileDialog.prototype.StopUpload = function(agent, parent)
{
	this.agent = agent;
	id = false;
	mID = parent.id.match(new RegExp(this.doc_prefix + '(\\d+)'));
	if (!!mID) {
		id = mID[1];
	}

	this.remove(id);
};

window.BlogBFileDialog.prototype.remove = function (id)
{
	if (this.controller && this.controller.parentNode)
		BX.onCustomEvent(this.controller.parentNode, 'OnFileUploadRemove', [id, this]);

	var data = {
		fileID: id,
		app_code:this.appCode,
		sessid: BX.bitrix_sessid(),
		cid: this.CID,
		mfi_mode: "delete"
	};
	BX.ajax.post(this.uploadFileUrl, data);
};

window.BlogBFileDialogDispatcher = function(controller)
{
	this.id = this.getID();
	this.controller = controller;

	BX.loadScript('/bitrix/js/main/core/core_dd.js', BX.delegate(function() {
		if (BX.type.isElementNode(this.controller) && this.controller.parentNode && this.controller.parentNode.parentNode)
		{
			var target = this.controller.parentNode.parentNode;
			this.dropbox = new BX.DD.dropFiles(target);
			if (this.dropbox && this.dropbox.supported() && BX.ajax.FormData.isSupported()) {
				this.hExpandUploader = BX.proxy(this.ExpandUploader, this);
				BX.addCustomEvent(this.dropbox, 'dragEnter', this.hExpandUploader);
				BX.addCustomEvent(target, "UnbindDndDispatcher", BX.delegate(this.Unbind, this));
			}
		}
	}, this));
}

window.BlogBFileDialogDispatcher.prototype.getID = function() {
	return '' + new Date().getTime();
}

window.BlogBFileDialogDispatcher.prototype.ExpandUploader = function()
{
	BX.onCustomEvent(BX(this.controller.parentNode), "BFileDLoadFormController", ['show']);
//	this.Unbind();
}

window.BlogBFileDialogDispatcher.prototype.Unbind = function()
{
	BX.removeCustomEvent(this.dropbox, 'dragEnter', this.hExpandUploader);
}

// upoader section
window.BlogBFileDialogUploader = function(arParams, agent)
{
	this.WDUploaded = false;
	this.WDUploadInProgress = false;
	this.documentExists = false;
	this.fileDropped = false;
	this.appCode = arParams.appCode;
	this.caller = arParams;
	this.agent = agent;
	this.parentID = this.agent.id;
	this.id = this.caller.getID();

	this.msg = arParams.msg;
	this.dropAutoUpload = arParams.dropAutoUpload;
	this.uploadFileUrl = arParams.uploadFileUrl; // from file.input php
	this.CID = arParams.CID;

	this.CreateElements();
	this.fileInput = (!!agent.fileInput ? agent.fileInput : ((BX.type.isDomNode(agent.fileInputID)) ? agent.fileInputID : BX(arParams.fileInput)));
	if (BX.type.isDomNode(this.fileInput)) {
		this.fileInput.name = 'mfi_files[]';
	}
	this.fileList = this.__form;

	BX.loadScript('/bitrix/js/main/core/core_dd.js', BX.delegate(
		function() {
			var dropbox = new BX.DD.dropFiles();
			if (dropbox && dropbox.supported() && BX.ajax.FormData.isSupported())
			{
				this.dropbox = dropbox;
			}
			this.agent.BindUploadEvents(this);
		}, this));
}

window.BlogBFileDialogUploader.prototype.CreateElements = function()
{
	var uniqueID;
	do {
		uniqueID = Math.floor(Math.random() * 99999);
	} while(BX("iframe-" + uniqueID));

	var iframeName = "iframe-" + this.id;
	var iframe = BX.create("IFRAME", {
		props: {name: iframeName, id: iframeName},
		style: {display: "none"}
	});
	document.body.appendChild(iframe);
	this.iframeUpload = iframe;

	var form = BX.create("FORM", {
		props: {
			id: "form-" + uniqueID,
			method: "POST",
			action: this.uploadFileUrl,
			enctype: "multipart/form-data",
			encoding: "multipart/form-data",
			target: iframeName
		},
		style: {display: "none"},
		children: [
			BX.create("INPUT", {
				props: {
					type: "hidden",
					name: "sessid",
					value: BX.bitrix_sessid()
				}
			}),
			BX.create("INPUT", {
				props: {
					type: "hidden",
					name: "uniqueID",
					value: uniqueID
				}
			}),
			BX.create("INPUT", {
				props: {
					type: "hidden",
					name: "cid",
					value: this.CID
				}
			}),
			BX.create("INPUT", {
				props: {
					type: "hidden",
					name: "mfi_mode",
					value: "upload"
				}
			})
		]
	});
	document.body.appendChild(form);
	this.__form = form;

	window['FILE_UPLOADER_CALLBACK_' + uniqueID] = BX.proxy(this.Callback, this);
}

window.BlogBFileDialogUploader.prototype.GetUploadFileName = function(not_customized)
{
	custom = !not_customized;
	fileName = '';
	if (this.fileInput && (this.fileInput.value.length > 0)) {
		var fileName = this.fileInput.value;
		if (fileName.indexOf('\\') > -1) // deal with Chrome fakepath
			fileName = fileName.substr(fileName.lastIndexOf('\\')+1);
	} else {
		var fileNode = this.fileList;
		if (fileNode.file)
			fileName = fileNode.file.fileName || fileNode.file.name;
	}
	return fileName;
}

window.BlogBFileDialogUploader.prototype.Callback = function(files, uniqueID, errorText)
{
	if (files.length > 0) {
		for(var i = 0; i < files.length; i++) {
			var result = {};
			result.success = true;
			result.storage = 'bfile';
			result.element_id = files[i].fileID;
			result.element_name = files[i].fileName;
			result.element_size = files[i].fileSize;
			result.element_url = files[i].fileURL;
			result.element_content_type = (files[i].content_type ? files[i].content_type : files[i].fileContentType);

			result.element_image = ((!!files[i].img_thumb_src) ? files[i].img_thumb_src : files[i].fileSrc);
			if (!!result.element_image)
				result.element_image = result.element_image.replace(/\/([^\/]+)$/, function(str, name) { return "/" + BX.util.urlencode(name); } );
			result.element_thumbnail = ((!!files[i].img_source_src) ? files[i].img_source_src: files[i].fileSrc);
			if (!!result.element_thumbnail)
				result.element_thumbnail = result.element_thumbnail.replace(/\/([^\/]+)$/, function(str, name) { return "/" + BX.util.urlencode(name); } );

			BX.onCustomEvent(this, 'uploadFinish', [result]);
		}
	} else {
		var result = {};
		result.success = false;
		result.messages = (errorText && errorText.length > 0) ? errorText : this.msg.upload_error;
		BX.onCustomEvent(this, 'uploadFinish', [result]);
	}
	window['FILE_UPLOADER_CALLBACK_' + uniqueID] = BX.DoNothing;
	BX.cleanNode(BX("iframe-" + uniqueID), true);
	BX.cleanNode(BX("form-" + uniqueID), true);
	this.agent.uploadDialog = null;
}

window.BlogBFileDialogUploader.prototype.UploadResponse = function(evt, responseJSONStr)
{
	this.WDUploadInProgress = false;
	BX.unbind(window, 'beforeunload', BX.proxy(this.UploadLeave, this));

	if (!  responseJSONStr
		|| responseJSONStr.length <= 0)
	{
		this.onError();
	}
}

window.BlogBFileDialogUploader.prototype.UploadResponseIframe = function(evt, responseJSONStr)
{
	this.WDUploadInProgress = false;
	BX.unbind(window, 'beforeunload', BX.proxy(this.UploadLeave, this));
}

window.BlogBFileDialogUploader.prototype.UploadLeave = function(e)
{
	var e = e || window.event;
	var msg = '';
	if (this.WDUploadInProgress)
		msg = this.msg.UploadInterrupt;
	else if (((!this.WDUploaded) && this.fileInput && (this.fileInput.value.length > 0)))
		msg = this.msg.UploadNotDone;
	if (msg != '')
	{
		if (e)
			e.returnValue = msg;
		return msg; // safari & chrome
	}
	return;
}

window.BlogBFileDialogUploader.prototype.UpdateListFiles = function(files)
{
	if (this && files)
	{
		var _this = this;
		if (files.length < 1)
			return;
		j = 0;
		var fileNode = this.fileList;
		fileNode.file = files[j];

		this.WDUploadInProgress = true;
		this.fileDropped = true;
		this.CallSubmit();
	}
}

window.BlogBFileDialogUploader.prototype.GetInputData = function(parentNode)
{
	var elements = [];
	var data = {};
	elements = elements.concat(
		BX.findChildren(parentNode, {'tag': 'input'}, true),
		BX.findChildren(parentNode, {'tag': 'textarea'}, true),
		BX.findChildren(parentNode, {'tag': 'select'}, true));

	for(var i=0; i<elements.length; i++)
	{
		var el = elements[i];
		if (!el || el.disabled || el.name.length < 1)
			continue;
		switch(el.type.toLowerCase())
		{
			case 'text':
			case 'textarea':
			case 'password':
			case 'hidden':
			case 'select-one':
				data[el.name] = el.value;
				break;
			case 'radio':
				if(el.checked)
					data[el.name] = el.value;
				break;
			case 'checkbox':
				data[el.name] = (el.checked ? 'Y':'N');
				break;
			case 'select-multiple':
				var l = el.options.length;
				if (l > 0) data[el.name] = new Array();
				for (j=0; j<l; j++)
					if (el.options[j].selected)
						data[el.name].push(el.options[j].value);
				break;
			default:
				break;
		}
	}
	return data;
}

window.BlogBFileDialogUploader.prototype.SetFileInput = function(fileInput)
{
	if (!! this.__form.mfi_save)
		return;
	if (this.fileInput && this.fileInput != fileInput)
		BX.remove(this.fileInput);
	this.__form.appendChild(fileInput);
	this.fileInput = fileInput;
}

window.BlogBFileDialogUploader.prototype.CallSubmit = function()
{
	if (!! this.__form.mfi_save)
		return;
	BX.onCustomEvent(this, 'uploadStart', [this]);

	BX.bind(window, 'beforeunload', BX.proxy(this.UploadLeave, this));
	BX.bind(this.iframeUpload, 'load', BX.delegate(this.UploadResponseIframe, this));

	if (this.dropbox) {
		this.onProgress(0.15);
		if (this.fileInput && (this.fileInput.files.length > 0)) {
			var fileNode = this.fileList;
			fileNode.file = this.fileInput.files[0];
		}

		var arConstParams = this.GetInputData(this.__form);
		this.fileNodes = [this.fileList];
		for (i in this.fileNodes) {
			if (this.fileNodes[i].file) {
				var fd = new BX.ajax.FormData();

				for (item in this.fileNodes[i].data)
				{
					fd.append(item, this.fileNodes[i].data[item]);
				}

				if (!! Object && !! Object.keys) // for IE 10 ....
				{
					var keys = Object.keys(arConstParams);
					for (var k in keys)
					{
						var key = keys[k]
						var cons = arConstParams[key]
						fd.append(key, cons);
					}
				}
				else
				{
					for (item in arConstParams)
					{
						fd.append(item, arConstParams[item]);
					}
				}

				fd.append('mfi_files[]', this.fileNodes[i].file);
				fd.append('app_code', this.appCode);
				fd.send(
					this.uploadFileUrl,
					BX.delegate(function(ajaxdata) {
						this.UploadResponse(null, ajaxdata);
					}, this),
					BX.delegate(this.onProgress, this)
				);
			}
		}
	} else {
		this.onProgress(0.15);
		this.WDUploadInProgress = true;
		var fid = this.__form.id;
		BX.submit(this.__form, 'mfi_save', 'Y');
	}
}

window.BlogBFileDialogUploader.prototype.onProgress = function(percent)
{
	if (isNaN(percent))
		return;
	BX.onCustomEvent(this, 'progress', [percent]);
}

window.BlogBFileDialogUploader.prototype.onError = function()
{
	BX.onCustomEvent(this, 'uploadFinish', [{success: false, messages: this.msg.upload_error}]);
}

top.BlogBFileDialog = window.BlogBFileDialog;
top.BlogBFileDialogUploader = window.BlogBFileDialogUploader;
top.BlogBFileDialogDispatcher = window.BlogBFileDialogDispatcher;

window.MFIDD = function(params){
	BX.loadCSS('/bitrix/components/bitrix/main.file.input/templates/drag_n_drop/style.css');
	if (! BX.browser.IsIE())
	{
		window['bfDisp' + params['uid']] = new BlogBFileDialogDispatcher(params['controller']);
		window['BfileUnbindDispatcher' + params['uid']] = function(){
			BX.onCustomEvent(params['controller'].parentNode.parentNode, 'UnbindDndDispatcher');}
	}

	var status = (params["status"] === 'show' ? 'show' : (params["status"] === 'hide' ? 'hide' : 'switch'));
	if (status == 'switch')
		status = (params['controller'].style.display != 'none' ? 'hide' : 'show');

	if (! params['controller'].loaded)
	{
		params['controller'].loaded = true;
		var dropbox = new BX.DD.dropFiles(),
			variant = (dropbox && dropbox.supported() && BX.ajax.FormData.isSupported() ? 'extended' : 'simple');

		top['BfileFD' + params['uid']] = window['BfileFD' + params['uid']] = new BlogBFileDialog({
			'mode' : variant,
			'CID' : params['CID'],
			'id' : params['id'],
			'upload_path' : params['upload_path'],
			'multiple' : params['multiple'],
			'controller':  params['controller'],
			'inputName' : params['inputName'],
			'fileInput' :  ("file-fileUploader-" + params['uid']),
			'fileInputName' : "mfi_files[]",
			'msg' : {
				'loading' : BX.message('loading'),
				'file_exists' : BX.message('file_exists'),
				'upload_error' : BX.message('upload_error'),
				'access_denied' : BX.message('access_denied')
			}
		});
		BX.fx.show(params['controller'], 'fade', {time:0.2});
		window['BfileFD' + params['uid']].LoadDialogs('DropInterface');
		BX.onCustomEvent('BFileDSelectFileDialogLoaded', [window['BfileFD' + params['uid']]]);

		if (!! window['BfileUnbindDispatcher' + params['uid']])
			window['BfileUnbindDispatcher' + params['uid']]();
	}
	else
	{
		if (status == "show")
			BX.fx.show(params['controller'], 'fade', {time:0.2});
		else
			BX.fx.hide(params['controller'], 'fade', {time:0.2});
	}
}

})(window);