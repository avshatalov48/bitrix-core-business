(function() {
var BX = window.BX;
if(BX.FileUploadAgent)
	return;

BX.FileUploadAgent = function(arParams) {
	this.controller = (!! arParams['controller']) ? arParams['controller'] : null; // DOM-element
	this.inputName = (!! arParams['inputName']) ? arParams['inputName'] : null; // Name for array which contains ID's uploaded files

	this.urlUpload = (!! arParams['urlUpload']) ? arParams['urlUpload'] : null; // additional params
	this.urlShow = (!! arParams['urlShow']) ? arParams['urlShow'] : null; // additional params

	this.values = (BX.type.isArray(arParams['values'])) ? arParams['values'] : []; // values which already have been uploaded
	this.fileInputID = (!! arParams['fileInput']) ? arParams['fileInput'] : null; // ID DOM-element which type is "file" <input type=file id=arParams['fileInput']...
	this.fileInputName = (!! arParams['fileInputName']) ? arParams['fileInputName'] : null; // Array name to upload file <input type=file name=arParams['fileInputName']
	this.multiple = !!arParams['multiple'];

	this.placeholder = (!! arParams['placeholder']) ? arParams['placeholder'] : null; // TBODY to add new row with info about new file
	this.uploadDialog = (!! arParams['uploadDialog']) ? arParams['uploadDialog'] : null; // Parent class method to upload file
	this.msg = arParams['msg'];

	this.fileInput = null;
	this.uploadFile = null;
	this.droppedFiles = null;
	this.place = null;
	this.progress = null;
	this.progressPercent = 0.05;
	this.progressAnimation = null;
	this.parent = arParams;
	this.uploadResultShown = false;
	this.loaded = false;
	this.hAttachEvents = (!!arParams['hAttachEvents']) ? arParams['hAttachEvents'] : null;
	this.caller = (!!arParams['caller']) ? arParams['caller'] : null;
	this.classes = (!!arParams['classes'] ? arParams['classes'] : {
		'uploaderParent' : '',
		'uploader' : '',
		'tpl_simple' : '',
		'tpl_extended' : '',
		'selector' : '',
		'selector_active' : ''
	});
	this.doc_prefix = (!!arParams['doc_prefix'] ? arParams['doc_prefix'] : '');

	if (!! arParams['mode'])
		this.SelectViewVariant(arParams['mode']); // IE or not IE

	this.id = this.getID();
	if (! this.parent._mkFileInput) // first agent
		this._mkFileInput();

	this.hUploaderChange = this.onUploaderChange.bind(this);

	if (! window.wduf_places)
		window.wduf_places = {};
};

BX.FileUploadAgent.prototype.Init = function()
{
	if (BX.type.isDomNode(this.fileInput)) {
		this.fileInput = this.fileInput;
	} else if (BX.type.isDomNode(this.fileInputID)) {
		this.fileInput = this.fileInputID;
	} else if (this.fileInputID) {
		this.fileInput = BX(this.fileInputID);
	}

	if (this.fileInput) {
		BX.bind(this.fileInput, 'change', this.hUploaderChange);
	}
	if (this.hAttachEvents && BX.type.isFunction(this.hAttachEvents)) {
		this.hAttachEvents(this);
	}
};

BX.FileUploadAgent.prototype.getID = function() {
	return ('' + new Date().getTime()).substr(6);
};

BX.FileUploadAgent.prototype._mkClose = function(parent)
{
	if (!parent)
		return false;
	var target = null;

	var closeBtn = BX.create('SPAN', {
			'props' : {
				'className' : 'del-but'
			}
		}
	);

	var divLoading = BX.findChild(parent, {'className':'loading'}, true);
	var divLoaded = BX.findChild(parent, {'className':'files-storage-block'}, true);
	if (!!divLoading)
		target = divLoading;
	else if (!!divLoaded)
		target = divLoaded;

	if (!!target) {
		BX.bind(closeBtn, 'click', this.StopUpload.bind(this, parent));
		target.appendChild(closeBtn);
	}
};

BX.FileUploadAgent.prototype._mkPlace = function(name, cacheID)
{
	if (!cacheID)
		cacheID = name;

	if ((cacheID in window.wduf_places) && !!window.wduf_places[cacheID]) {
		this.place = window.wduf_places[cacheID];
		this.progress = BX.findChild(this.place, {'className' : 'load-indicator'}, true);
	} else {
		this.progress = BX.create('SPAN', {
			'props' : {
				'className' : 'load-indicator'
			},
			'style' : {
				'width' : '5%'
			},
			'children' : [
				BX.create('SPAN', {
						'props' : {
							'className' : 'load-number'
						},
						'text' : '5%'
					}
				)
			]
		});

		var progressHolder = BX.create('SPAN', {
			'props' : {
				'className' : 'loading-wrap'
			},
			'children' : [
				BX.create('SPAN', {
						'props' : {
							'className' : 'loading'
						}
					}
				),
				this.progress
			]
		});

		this.place = BX.create('TR', {
			'children' : [
				BX.create('TD', {
						'props' : {
							'className' : 'files-name'
						},
						'children' : [
							BX.create('SPAN', {
								'props' : {
									'className' : 'files-text'
								},
								'children' : [
									BX.create('SPAN', {
											'props' : {
												'className' : 'f-wrap'
											},
											'text': name
										}
									)
								]
							})
						]
					}
				),
				BX.create('TD', {
						'props' : {
							'className' : 'files-storage'
						},
						'attrs' : {
							'colspan' : '2'
						},
						'children' : [
							BX.create('SPAN', {
									'text': this.msg['loading']+':'
								}
							),
							progressHolder
						]
					}
				)
			]
		});

		this._mkClose(this.place);
		if (!this.multiple)
			this.placeholder.innerHTML = '';
		this.placeholder.appendChild(this.place);
		window.wduf_places[cacheID] = this.place;
	}
};

BX.FileUploadAgent.prototype._mkFileInput = function(parent)
{
	var controlParent = BX.findChild(this.controller,{'className':this.classes.uploaderParent}, true);

	var oldFileInput = BX.findChild(controlParent, {'className': this.classes.uploader});
	if (oldFileInput) {
		BX.remove(oldFileInput);
	}

	var attrs = { type: 'file', size: '1' };
	if (this.multiple)
	{
		attrs['multiple'] = 'multiple';
	}

	var newFileInput = BX.create('INPUT', {
		props: {
			className: this.classes.uploader
		},
		attrs : attrs
	});
	this.fileInput = newFileInput;
	this.fileInput.name = this.fileInputName;

	controlParent.appendChild(this.fileInput);
	if (this.hUploaderChange)
		BX.bind(this.fileInput, 'change', this.hUploaderChange);
	return this.fileInput;
};

BX.FileUploadAgent.prototype.onUploaderChange = function(e)
{
	if (!this.uploadDialog)
		return;

	BX.onCustomEvent(this.controller.parentNode, 'ChangeFileInput', [e, this.fileInput, this]);
	BX.onCustomEvent(this, 'ChangeFileInput', [e, this.fileInput, this]);

	this.uploadDialog.SetFileInput(this.fileInput);

	if (!! this.uploadDialog.dropbox) {
		this.UploadDroppedFiles(this.fileInput.files);
	} else {
		this.uploadDialog.CallSubmit();
	}
};

BX.FileUploadAgent.prototype.onUploadStart = function(dialog)
{
	var name = dialog.GetUploadFileName();
	if ((!this.uploadDialog) || (dialog.id != this.uploadDialog.id)) {
		return false;
	}

	if (!this.place)
		this._mkPlace(name);

	if (! this.uploadFile) {
		this._mkFileInput();
		var newdialog = this.GetNewObject();
		newdialog.LoadDialogs(this.dialogs);
	}
	this.uploadFile = null;
};

BX.FileUploadAgent.prototype.onProgress = function(percent, force)
{
	//this.UpdateProgressIndicator(percent);
	//return;
	if (isNaN(percent))
		return;

	if (percent > 0.90 && !force)
		percent = 0.90;
	if (!BX.fx) {
		this.UpdateProgressIndicator(percent);
	} else if (! this.progressAnimation) {
		this.progressAnimation = new BX.fx({
			start:	this.progressPercent,
			finish:	percent,
			allowFloat: true,
			type:function(params){return (BX.fx.RULES.accelerated(params)+BX.fx.RULES.decelerated(params))/2},
			time:3,
			step:0.01,
			callback:BX.delegate(function(value) {this.UpdateProgressIndicator(value); }, this)
		});
		this.progressAnimation.start();
	} else {
		this.progressAnimation.stop(true);
		this.progressAnimation.options.start = +this.progressPercent;
		this.progressAnimation.options.finish = +percent;
		//this.progressAnimation.options.time = percent/3;
		this.progressAnimation.__checkOptions();
		this.progressAnimation.start();
	}
};

BX.FileUploadAgent.prototype.onUploadFinish = function(result)
{
	this.uploadResult = result;
	this.onProgress(2, true);
};

BX.FileUploadAgent.prototype.UpdateProgressIndicator = function(percent)
{
	if (this.progressPercent > percent)
		return;

	var percentS = Math.ceil(percent*100);
	if (percentS > 100)
		percentS = 100;
	BX.style(this.progress, 'width', percentS+'%');
	var px = BX.findChild(this.progress, {'className':'load-number'}, true);
	px.innerHTML = percentS+'%';

	this.progressPercent = percent;
	if (percent > 0.9999) {
		if (this.uploadResult && (!this.uploadResultShown)) {
			this.uploadResultShown = true;
			if (this.uploadResult.success) {
				this.ShowUploadedFile();
			} else {
				if (!! this.uploadResult.messages)
					this.ShowUploadError(this.uploadResult.messages);
			}
		}
	}
};

BX.FileUploadAgent.prototype.ShowAttachedFiles = function()
{
	if (! this.values)
		return;
	var valArr = this.values.slice();
	var val = this.values.shift();
	if (!!val) {
		if (BX.type.isDomNode(val)) {
			var sID = val.id;
			var mID = sID.match(new RegExp(this.doc_prefix + '(\\d+)'));
			if (!!mID) {
				var id = mID[1];
				this.BindLoadedFileControls(id, val);
			}
		} else {
			if (! val.element_id) {
				val = {'element_id' : val};
			}
			
			this.uploadResultArr = new Array();
			for (var i=0;i<valArr.length;i++)
			{
				var element_id = valArr[i];
				if (typeof(valArr[i]) == "object")
				{
					element_id = valArr[i].element_id;
				}
				this._mkPlace('', element_id);
				this.uploadResultArr[i] = {'element_id' : valArr[i].element_id, 'element_url' : valArr[i].element_url,
					'element_name' : valArr[i].element_name, 'place' :this.place};
			}
			
			//this._mkPlace('', val.element_id);
			this.ShowUploadedFile(val);
			this.values = [];
		}
	}
};

BX.FileUploadAgent.prototype.BindLoadedFileControls = function(id, node) // event
{
	if (!node || node.nodeName !== "TR")
	{
		return;
	}
	this.place = node;
	this._mkClose(node);
	BX.onCustomEvent(this.caller, 'BindLoadedFileControls', [this, id]);
	this._clearPlace();
	setTimeout(BX.delegate(this.ShowAttachedFiles, this), 200);
};

BX.FileUploadAgent.prototype.ShowUploadError = function(messages)
{
	if (!!messages)
	{
		if (BX.type.isArray(messages))
			messages = messages.join("\n");
		messages = messages.replace("<br>","");

		BX.remove(this.progress.parentNode); // .progressHolder

		if (!! messages) {
			BX.addClass(this.place, 'error-load');
			while(this.place.cells.length > 1)
				this.place.deleteCell(1); // size
			var newCell = this.place.insertCell(-1);
			newCell.setAttribute("colspan", 2);
			newCell.appendChild(BX.create('SPAN', {props: {className: 'info-icon'}}));
			newCell.appendChild(BX.create('SPAN', {props: {className: 'error-text'}, text: messages}));
			this._mkClose(this.place);
		}
	}
};

BX.FileUploadAgent.prototype.ShowUploadedFile = function(param)
{
	if (!!param)
		this.uploadResult = param;

	BX.onCustomEvent(this.caller, 'ShowUploadedFile', [this]);
};

BX.FileUploadAgent.prototype.AddRowToPlaceholder = function(TR)
{
	var rows = BX.findChildren(this.placeholder, {'tagName':'TR'}, true);
	if (!!rows) {
		for (var i=0;i<rows.length;i++) {
			if (rows[i] == this.place) {
				var newRow = this.placeholder.insertRow(i);
				newRow.className = TR.className;
				newRow.id = TR.id;
				var cells = BX.findChildren(TR, {'tagName':'TD'}, true);
				if (!!cells) {
					for (var j=0;j<cells.length;j++) {
						var newCell = newRow.insertCell(-1);
						newCell.className = cells[j].className;
						newCell.innerHTML = cells[j].innerHTML;
					}
				}
				BX.cleanNode(this.place, true);

				this._clearPlace();
				this._mkClose(newRow);
				setTimeout(BX.delegate(this.ShowAttachedFiles, this), 200);
				break;
			}
		}
	}
};

BX.FileUploadAgent.prototype.AddNodeToPlaceholder = function(node)
{
	BX.cleanNode(this.place, true);
	this._clearPlace();
	var place = this.placeholder.parentNode.parentNode;
	place.appendChild(node);
};

BX.FileUploadAgent.prototype._clearPlace = function()
{
	for (var i in window.wduf_places)
	{
		if (window.wduf_places[i]  ==  this.place)
		{
			window.wduf_places[i] = false;
		}
	}
	this.place = null;
};

BX.FileUploadAgent.prototype.StopUpload = function(parentNode)
{
	if (!parentNode)
	{
		return;
	}

	BX.onCustomEvent(this.caller, 'StopUpload', [this, parentNode]);

	BX.hide(parentNode);
	var mID = parentNode.id.match(new RegExp(this.doc_prefix + '(\\d+)'));
	if (!!mID)
	{
		var fileInput = BX('file-doc' + mID[1]);
		if (fileInput)
		{
			BX.remove(fileInput);
		}
	}
};

BX.FileUploadAgent.prototype.LoadScript = function(src, callback)
{
	if (!callback)
		callback = BX.DoNothing;
	if (! window.loaded_scripts)
		window.loaded_scripts = [];
	if (! BX.util.in_array(src, window.loaded_scripts)) {
		BX.loadScript(src, callback);
		window.loaded_scripts.push(src);
	}
};

BX.FileUploadAgent.prototype.BindUploadEvents = function(dialog)
{
	this.LoadDialogsFinished();

	if (dialog.parentID != this.id)
		return;

	this.uploadDialog = dialog;
	BX.addCustomEvent(dialog, 'uploadStart', BX.delegate(this.onUploadStart, this));
	BX.addCustomEvent(dialog, 'progress', BX.delegate(this.onProgress, this));
	BX.addCustomEvent(dialog, 'uploadFinish', BX.delegate(this.onUploadFinish, this));

	if (!! this.parent.droppedFiles) {
		for (var i=0; i<this.parent.droppedFiles.length; i++) {
			if (! this.parent.droppedFiles[i].ready) {
				this.parent.droppedFiles[i].ready = true;
				this.uploadFile = this.parent.droppedFiles[i];
				if ((i) < this.parent.droppedFiles.length) {
					var newdialog = this.GetNewObject(this.parent);
					newdialog.LoadDialogs(this.dialogs);
				}
				break;
			}
			if (!this.multiple) break;
		}
		if (this.uploadFile) {
			if (this.fileInput) {
				BX.unbind(this.fileInput, 'change', this.hUploaderChange);
			}
			var name = (this.uploadFile.fileName || this.uploadFile.name);
			this._mkPlace(name);
		}
	}

	if (!!this.uploadFile) {
		this.uploadDialog.fileDropped = true;
		this.uploadDialog.UpdateListFiles([this.uploadFile]);
	}
};

BX.FileUploadAgent.prototype.UploadDroppedFiles = function(files)
{
	if (!this.uploadDialog)
		return;

	this.droppedFiles = files;
	if (files.length > 0) {
		for (var i=0; i<files.length; i++) {
			this._mkPlace( files[i].fileName || files[i].name );
			if (!this.multiple) break;
		}
		this.uploadDialog.SetFileInput(this.fileInput);
		this._mkFileInput();
	}
	var newdialog = this.GetNewObject();
	newdialog.LoadDialogs(this.dialogs);
};

BX.FileUploadAgent.prototype.AddSelectedFiles = function(files)
{
	if (!!files && BX.type.isArray(files) && (files.length > 0))
	{
		for (var i in files) {
			if ((!BX(this.doc_prefix + files.id))) {
				this._mkPlace(files[i].name, files[i].id);
				var ar = {'element_id':files[i].id, 'element_url':files[i].link};
				this.values.push(ar);
			}
		}
		if (this.values.length > 0)
		{
			this.ShowAttachedFiles();
		}
	}
};

BX.FileUploadAgent.prototype.Disable = function()
{
	BX.cleanNode(this.controller);
	this.controller.innerHTML = this.msg.access_denied;
};

BX.FileUploadAgent.prototype.LoadUploadDialog = function()
{
	if (!! this.caller)
	{
		this.caller.GetUploadDialog(this);
	}
};

BX.FileUploadAgent.prototype.SelectViewVariant = function(variant)
{
	var target = {
		'simple' : this.classes.tpl_simple,
		'extended' : this.classes.tpl_extended
	};
	for (var i in target) {
		var domNode = BX.findChild(this.controller, { 'className': target[i]}, true);
		if (!!domNode) {
			if (variant == i)
				BX.show(domNode);
			else
				BX.remove(domNode);
		}
	}
};

BX.FileUploadAgent.prototype.LoadDialogsFinished = function()
{
	this.ShowAttachedFiles();
};

BX.FileUploadAgent.prototype.LoadDialogs = function(dialogs)
{
	if (this.loaded)
		return false;
	this.loaded = true;
	if (!dialogs)
		return false;
	this.dialogs = dialogs;
	if (
			(dialogs.indexOf('DropInterface') > -1)
			&& (
				! this.parent
				|| ! this.parent.droppedFiles
			)
		)
	{
		if (!!window.BX.DD)
		{
			var controller = this.controller;
			if (!controller)
				return false;
			var dropbox = new BX.DD.dropFiles(controller);
			if (dropbox && dropbox.supported() && BX.ajax.FormData.isSupported()) {
				this.dropbox = dropbox;
				this.Init();
				var controllerV = BX.findChild(this.controller, { 'className': this.classes.selector}, true);
				BX.addCustomEvent(dropbox, 'dropFiles', BX.delegate(this.UploadDroppedFiles, this));
				BX.addCustomEvent(dropbox, 'dragEnter', BX.delegate(function() {BX.addClass(controllerV, this.classes.selector_active);}, this));
				BX.addCustomEvent(dropbox, 'dragLeave', BX.delegate(function() {BX.removeClass(controllerV, this.classes.selector_active);}, this));
			} else {
				this.Init();
			}
			this.LoadUploadDialog();
		}
	}
	else
	{
		this.LoadUploadDialog();
	}
	if (this.values.length > 0)
		BX.show(this.controller);
};
BX.FileUploadAgent.prototype.GetNewObject = function(parent)
{
	return new BX.FileUploadAgent((!!parent ? parent : this));
};
})();

