;(function(){
if (!!BX.CFileInput) return;

BX.CFileInput = function(ID, INPUT_NAME, CID, upload_path, bMultiple)
{
	this.ID = ID;
	this.INPUT_NAME = INPUT_NAME;
	this.CID = CID;
	this.upload_path = upload_path;

	this.multiple = !!bMultiple;

	this.INPUT = null;
	this.LIST = null;

	this.bInited = false;

	this.FILES = [];

	BX.CFileInput.Items[ID] = this;

	BX.ready(BX.delegate(this.Init, this));
}

BX.CFileInput.Items = {};

BX.CFileInput.prototype.setFiles = function(arFiles)
{
	if (!BX.type.isArray(arFiles))
	{
		return;
	}

	this.Clear();
	this.FILES = arFiles;

	if (this.bInited)
	{
		setTimeout(BX.delegate(function() {
			this.Callback(this.FILES, 'init');
		}, this), 1);
	}
}

BX.CFileInput.prototype.Init = function()
{
	if (this.bInited)
		return;

	this.INPUT = BX("file_input_" + this.ID);
	this.LIST = BX("file_input_upload_list_" + this.ID);

	BX.bind(this.INPUT, "change", BX.proxy(this.OnChange, this));

	setTimeout(BX.delegate(function(){
		this.bInited = true;
		this.Callback(this.FILES, 'init');
	},this),1);
}

BX.CFileInput.prototype.CreateFileEntry = function(file, uniqueID)
{
	return BX.create("LI", {
		props: {className: "uploading", id: "file-" + file.fileName + "-" + uniqueID},
		children : [
			BX.create("A", {
				props: {href: "", target: "_blank", className: "upload-file-name"},
				text: file.fileName,
				events : {click : BX.PreventDefault}
			}),
			BX.create('SPAN', {
				props: {className: 'upload-file-size'},
				children: [typeof file.fileSize !== 'undefined' ? ('&nbsp;'+file.fileSize) : null]
			}),
			BX.create("I"),
			BX.create("A", {
				props: {href: "javascript:void(0)", className : "delete-file"},
				events: {click: BX.proxy(this._deleteFile, this)}
			})
		]
	});
}

BX.CFileInput.prototype.OnChange = function()
{
	var files = [];

	if (this.INPUT.files && this.INPUT.files.length > 0)
	{
		files = this.INPUT.files;
	}
	else
	{
		var filePath = this.INPUT.value;
		var fileTitle = filePath.replace(/.*\\(.*)/, "$1").replace(/.*\/(.*)/, "$1");
		files = [{fileName : fileTitle}];
	}

	var uniqueID;
	do {
		uniqueID = Math.floor(Math.random() * 99999);
	} while(BX("iframe-" + uniqueID));

	if (!this.multiple)
		BX.cleanNode(this.LIST);

	for (var i = 0; i < files.length; i++) {
		if (!files[i].fileName && files[i].name) {
			files[i].fileName = files[i].name;
		}
		this.LIST.appendChild(this.CreateFileEntry(files[i], uniqueID));
	}

	this.Send(uniqueID);
}

BX.CFileInput.prototype.Send = function(uniqueID)
{
	var iframeName = "iframe-" + uniqueID;
	var iframe = BX.create("IFRAME", {
		props: {name: iframeName, id: iframeName},
		style: {display: "none"}
	});
	document.body.appendChild(iframe);

	var originalParent = this.INPUT.parentNode, originalName = this.INPUT.name;
	originalParent.removeChild(this.INPUT);

	this.INPUT.name = 'mfi_files[]';

	// hack: the only way to surely set enctype=multipart/form-data for this form
	var f = BX.create('DIV');
	f.innerHTML = '<form enctype="multipart/form-data"></form>';
	var form = f.firstChild;

	BX.adjust(form, {
		props: {
			method: "POST",
			action: this.upload_path,
			target: iframeName
		},
		style: {display: "none"},
		children: [
			this.INPUT,
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

	window['FILE_UPLOADER_CALLBACK_' + uniqueID] = BX.proxy(this.Callback, this);

	document.body.appendChild(f);

	BX.submit(form, 'mfi_save', 'Y', BX.delegate(function(){
		this.INPUT.name = originalName;

		BX.unbind(this.INPUT, "change", BX.proxy(this.OnChange, this));
		this.INPUT = originalParent.appendChild(BX.create('INPUT', {
			attrs: {
				name: originalName,
				id: this.INPUT.id,
				type: 'file',
				size: '1',
				multiple: 'multiple'
			}
		}));
		BX.bind(this.INPUT, "change", BX.proxy(this.OnChange, this));

		BX.cleanNode(f, true);
	}, this));
}

BX.CFileInput.prototype.Clear = function()
{
	if(this.LIST)
	{
		while(this.LIST.childNodes.length > 0)
		{
			this.LIST.removeChild(this.LIST.childNodes[0]);
		}
	}

	this.FILES = [];
}

BX.CFileInput.prototype.Callback = function(files, uniqueID)
{
	if (!this.bInited)
	{
		this.Init();
		return;
	}

	BX.show(this.LIST);

	for(var i = 0; i < files.length; i++)
	{
		var elem = BX("file-" + files[i].fileName + "-" + uniqueID);
		if (!elem)
		{
			elem = this.LIST.appendChild(this.CreateFileEntry(files[i], uniqueID + Math.random()));
		}

		if (files[i].fileID)
		{
			BX.removeClass(elem, "uploading");
			BX.addClass(elem, "saved");
			BX.adjust(elem.firstChild, {props: {href: files[i].fileURL}});
			BX.adjust(elem.firstChild.nextSibling, {html: '&nbsp;' + files[i].fileSize});
			BX.unbindAll(elem.firstChild);
			BX.unbindAll(elem.lastChild);
			BX.bind(elem.lastChild, "click", BX.proxy(this._deleteFile, this));
			elem.appendChild(BX.create("INPUT", {
				props: {
					type: "hidden",
					name: this.INPUT_NAME + (this.multiple ? '[]' : ''),
					value: files[i].fileID
				}
			}));
		}
		else
		{
			BX.cleanNode(elem, true);
		}
	}

	window['FILE_UPLOADER_CALLBACK_' + uniqueID] = null;
	BX.cleanNode(BX("iframe-" + uniqueID), true);

	BX.onCustomEvent(this, 'onFileUploaderChange', [files]);
}

BX.CFileInput.prototype._deleteFile = function (e)
{
	var node = BX.proxy_context;
	var bSaved = BX.hasClass(node.parentNode, "saved");
	if (!bSaved || confirm(BX.message("MFI_CONFIRM")))
	{
		if (bSaved)
		{
			var data = {
				fileID : node.nextSibling.value,
				sessid : BX.bitrix_sessid(),
				cid : this.CID,
				mfi_mode : "delete"
			};
			BX.ajax.post(this.upload_path, data);
		}
		BX.remove(node.parentNode);
		BX.onCustomEvent(this, 'onFileUploaderChange');
	}

	BX.PreventDefault(e);
}
})();