;(function() {
window['BXDEBUG']=true;
var BX = window.BX, repo = {}, thumbSize = 200;
BX.namespace("BX.UI");
if (BX["UI"]["FileInput"])
	return;
BX["UI"]["FileInput"] = function(id, uploadParams, elementParams, values, template)
{
	this.id = id;
	this.inited = false;
	this.uploadParams = (uploadParams || {});
	this.uploadParams["urlUpload"] = '/bitrix/tools/upload.php?lang=' + BX.message("LANGUAGE_ID");
	this.uploadParams["urlCloud"] = '/bitrix/admin/clouds_file_search.php?lang=' + BX.message("LANGUAGE_ID") + '&n=';
	this.uploadParams["maxCount"] = parseInt(this.uploadParams["maxCount"]) || 0;
	this.onUploadDoneCounter = parseInt(this.uploadParams["maxIndex"]);
	if (this.onUploadDoneCounter > 0)
		this.onUploadDoneCounter++;
	this.template = template;
	this.elementParams = elementParams;
	this.menu = [];
	this.agent = null;
	this.container = null;

	BX.ready(BX.proxy(function(){ this.init(values); }, this));
};
BX["UI"].FileInput.prototype = {
	init : function(values)
	{
		this.container = BX(this.id + '_block');
		if (!this.container)
		{
			setTimeout(BX.proxy(function(){ this.init(values); }, this), 1000);
			return;
		}
		if (repo[this.id])
		{
			if (repo[this.id].container === this.container)
				return;
			repo[this.id].destruct();
		}

		if (!this.container["fileInputIsAppended"])
		{
			this.container["fileInputIsAppended"] = 0;
			this.container.appendChild(BX.create("INPUT", {
				attrs : {
					className : "adm-fileinput-drag-area-input",
					type : "file",
					id : this.id + '_input',
					multiple : (this.uploadParams['maxCount'] !== 1),
					"data-fileinput" : "Y"
				}
			}));
		}

		if (!BX(this.id + '_input'))
		{
			this.container["fileInputIsAppended"]++;
			if (this.container["fileInputIsAppended"] < 100)
				setTimeout(BX.proxy(function(){ this.init(values); }, this), 500);
			return;
		}

		this.initFrameCounters();
		this.agent = BX.Uploader.getInstance({
			id : this.id,
			streams : 1,
			uploadMaxFilesize : (this.uploadParams["uploadType"] === "file" ? BX.message("phpUploadMaxFilesize") : this.uploadParams['maxSize']),
			allowUpload : this.uploadParams['allowUpload'],
			allowUploadExt : this.uploadParams['allowUploadExt'],
			uploadFormData : "N",
			uploadMethod : "immediate",
			uploadFileUrl : this.uploadParams["urlUpload"],
			showImage : true,
			sortItems : (this.uploadParams["allowSort"] === "Y"),
			deleteFileOnServer : false,
			pasteFileHashInForm : false,
			input : BX(this.id + '_input'),
			dropZone : BX(this.id + '_block'),
			placeHolder : BX(this.id + '_container'),
			thumb : {
				tagName : "DIV",
				className : "adm-fileinput-item-wrapper"
			},
			fields : {
				thumb : {
					tagName : "DIV",
					template : this.template
				},
				preview : {
					params : { width : thumbSize, height : thumbSize },
					events : { }
				}
			}
		});
		repo[this.id] = this;
		this.fileEvents = {
			onFileIsAttached : BX.delegate(this.onFileIsAttached, this),
			onFileIsAppended : BX.delegate(this.onFileIsAppended, this),
			onFileIsBound : BX.delegate(this.onFileIsBound, this),
			onFileIsReadyToFrame : BX.delegate(this.onFileIsReadyToFrame, this),
			onUploadProgress : BX.delegate(this.onUploadProgress, this),
			onUploadDone : BX.delegate(this.onUploadDone, this),
			onUploadError : BX.delegate(this.onUploadError, this),
			onUploadRestore : BX.delegate(this.onUploadRestore, this)
		};

		this.agentEvents = {
			onAttachFiles : BX.delegate(this.onAttachFiles, this),
			onQueueIsChanged : BX.delegate(this.onQueueIsChanged, this),
			onFileIsCreated : BX.delegate(this.onFileIsCreated, this),
			onFileIsInited : BX.delegate(this.onFileIsInited, this),
			onFileIsReadyToFrame : BX.delegate(this.onFileIsReadyToFrame, this),
			onFilesPropsAreModified : BX.delegate(this.onFilesPropsAreModified, this),
			onFileIsFramed : BX.delegate(this.onFileIsFramed, this),
			onFilesAreFramed : BX.delegate(this.onFilesAreFramed, this),
			onBxDragStart : BX.delegate(this.onFileIsDragged, this),
			onError : BX.delegate(this.onError, this)
		};
		if (this.uploadParams['upload'])
		{
			var signature = this.uploadParams['upload'];
			this.agentEvents["onPackageIsInitialized"] = BX.delegate(function(pack, raw)
			{
				pack.post.data['signature'] = signature;
				pack.post.size += ('signature' + signature).length;
				this.onUploadStart(raw);
			}, this);
		}
		for (ii in this.agentEvents)
		{
			if (this.agentEvents.hasOwnProperty(ii))
			{
				BX.addCustomEvent(this.agent, ii, this.agentEvents[ii]);
			}
		}


/*		if (false && this.uploadParams["uploadType"] == "file")
		{
			var isFile = function(file)
			{
				var res = Object.prototype.toString.call(file);
				return (res == '[object File]' || res == '[object Blob]');
			};
			BX.addCustomEvent(this.agent, "onFileIsAfterCreated", BX.proxy(function(item, being, itemStatus) {
				if (isFile(item.file))
				{
					itemStatus.status = false;
				}
			}, this));

			if (this.agent.form)
			{
				this.__onsubmit = BX.delegate(function(e)
				{
					try{
					BX.unbind(this.agent.form, "submit", this.__onsubmit);
					var item, value;
					while((item = this.agent.getItems().getFirst()) && !!item &&
						((value = BX(item.id + 'Value')) && value) &&
						(isFile(item.file)))
					{
						this.agent.form.delete(value.name);
						this.agent.form.append(value.name, item.file);
						this.agent.getItems().removeItem(item.id);
					}
					}catch (e){
					}
					return BX.PreventDefault(e);
				}, this);
				BX.bind(this.agent.form, "submit", this.__onsubmit);
			}
		}

*/		if (values.length > 0)
		{
			var ar1 = [], ar2 = [];
			this.values = {};
			for (var ii = 0; ii < values.length; ii++)
			{
				this.values[values[ii]['id']] = values[ii];
				ar1.push(values[ii]);
				ar2.push(BX(values[ii]['id'] + 'Block'));
			}
			this.agent.onAttach(ar1, ar2, false);
		}
		this.initMenu(BX(this.id + '_add'), this.uploadParams);
		this.checkUploadControl();
		// Adjust all styles
		BX[this.elementParams['delete'] !== true ? "addClass" : "removeClass"](this.container, "adm-fileinput-non-delete");
		this.framedItems = new BX.UploaderUtils.Hash();
		if (BX(this.id + 'ThumbModePreview'))
		{
			BX.bind(BX(this.id + 'ThumbModePreview'), 'click', BX.delegate(function(e){
				BX.removeClass(BX(this.id + '_mode'), 'mode-file');
				BX.removeClass(BX(this.id + '_block'), 'mode-file');
				BX.addClass(BX(this.id + '_mode'), 'mode-pict');
				BX.addClass(BX(this.id + '_block'), 'mode-pict');
				this.saveOptions('mode', 'mode-pict');
				return BX.PreventDefault(e);
			}, this));
		}
		if (BX(this.id + 'ThumbModeNonPreview'))
		{
			BX.bind(BX(this.id + 'ThumbModeNonPreview'), 'click', BX.delegate(function(e){
				BX.removeClass(BX(this.id + '_mode'), 'mode-pict');
				BX.removeClass(BX(this.id + '_block'), 'mode-pict');
				BX.addClass(BX(this.id + '_mode'), 'mode-file');
				BX.addClass(BX(this.id + '_block'), 'mode-file');
				this.saveOptions('mode', 'mode-file');
				return BX.PreventDefault(e);
			}, this));
		}
		this.inited = true;
	},
	destruct : function() {
		var ii;
		for (ii in this.agentEvents)
		{
			if (this.agentEvents.hasOwnProperty(ii))
			{
				BX.removeCustomEvent(this.agent, ii, this.agentEvents[ii]);
			}
		}
		this.agent.destruct();
		this.deinitMenu(BX(this.id + '_add'));

		BX.remove(BX(this.id + '_input'));

		delete this.noticeNode;
		delete this.nativeNoticeMessage;
		delete this.container;
		delete this.framedItems;

		BX.unbindAll(BX(this.id + 'ThumbModePreview'));
		BX.unbindAll(BX(this.id + 'ThumbModeNonPreview'));

		this.agent = null;
		delete this.agent;

		delete repo[this.id];
	},
	checkUploadControl : function() {
		// drag&drop area
		if (!this['noticeNode'])
		{
			this.noticeNode = BX(this.id + 'Notice');
			this.nativeNoticeMessage = BX(this.id + 'Notice').innerHTML;
		}
		var possibleClasses = "adm-fileinput-drag-area " +
			"adm-fileinput-drag-area-error " +
			"adm-fileinput-drag-notification-count " +
			"adm-fileinput-drag-area-error-permission " +
			"adm-fileinput-drag-area-error-count";
		BX.removeClass(this.container, possibleClasses);
		if (!this.uploadParams['upload'])
		{
			this.noticeNode.innerHTML = BX.message("JS_CORE_FI_UPLOAD_DENIED");
			BX.addClass(this.container, "adm-fileinput-drag-area-error adm-fileinput-drag-area-error-permission");
		}
		else if (this.uploadParams["maxCount"] <= 0 || this.uploadParams["maxCount"] > this.agent.getItems().length)
		{
			this.noticeNode.innerHTML = this.nativeNoticeMessage;
			BX.addClass(this.container, "adm-fileinput-drag-area");
		}
		else if (this.uploadParams["maxCount"] === this.agent.getItems().length)
		{
			this.noticeNode.innerHTML = BX.message("JS_CORE_FI_TOO_MANY_FILES2");
			BX.addClass(this.container, "adm-fileinput-drag-area adm-fileinput-drag-notification-count");
		}
		else
		{
			this.noticeNode.innerHTML = BX.message("JS_CORE_FI_TOO_MANY_FILES3");
			BX.addClass(this.container, "adm-fileinput-drag-area-error adm-fileinput-drag-area-error-count");
		}
	},
	saveOptions : function(name, value)
	{
		BX.userOptions.save('main', 'fileinput', name, value);
	},
	initMenu : function(node, uploadParams) {
		node = BX(node);
		if (!node || node.OPENER)
			return;
		this.initMenuCounter = (this.initMenuCounter || 0) + 1;
		var inputID, menu = [];
		if (uploadParams['upload'])
		{
			inputID = this.id + '_menu' + this.initMenuCounter;
			menu.push({
				ID : "upload",
				HTML : BX.message("JS_CORE_FILE_UPLOAD") + '<input type="file" id="' + inputID + '"' + ' class="adm-fileinput-area-input" />',
				GLOBAL_ICON: "adm-menu-upload-pc"});
		}
		if (uploadParams['medialib'] || uploadParams['file_dialog'])
		{
			menu.push({
				TEXT: BX.message('JS_CORE_FILE_INSERT_PATH'),
				ONCLICK: BX.proxy(this.handlerFilePath, this),
				GLOBAL_ICON: "adm-menu-download"
			});
		}
		if (menu.length > 0)
		{
			menu.push({SEPARATOR : true});
		}
		if (uploadParams['medialib'])
		{
			menu.push({
				TEXT : BX.message("JS_CORE_FILE_MEDIALIB"),
				GLOBAL_ICON : "adm-menu-upload-medialib",
				ONCLICK : uploadParams['medialib']['click'] + "()"});
			window[uploadParams['medialib']['handler']] = BX.delegate(this.handlerMedialib, this);
		}
		if (uploadParams['fileDialog'])
		{
			menu.push({TEXT : BX.message("JS_CORE_FILE_SITE"), GLOBAL_ICON : "adm-menu-upload-site", ONCLICK : uploadParams['fileDialog']['click'] + "()"});
			window[uploadParams['fileDialog']['handler']] = BX.delegate(this.handlerFileDialog, this);
		}
		if (uploadParams['cloud'])
		{
			this.__cloudClick = BX.delegate(this.clickCloudPoint, this);
			menu.push({TEXT : BX.message("JS_CORE_FILE_CLOUD"), GLOBAL_ICON : "adm-menu-upload-cloud", ONCLICK : this.__cloudClick});
		}

		if (uploadParams['menu'] && BX.type.isArray(uploadParams['menu']))
		{
			menu.push({SEPARATOR : true});
			for (var ii = 0; ii <= uploadParams['menu'].length; ii++)
			{
				menu.push(uploadParams['menu']);
			}
			menu.push({SEPARATOR : true});
		}
		else if (uploadParams['medialib'] || uploadParams['file_dialog'] || uploadParams['cloud'])
		{
			menu.push({SEPARATOR : true});
		}
		if (menu.length > 0 && this.agent.dialogName == "BX.Uploader")
		{
			menu.push({
				GLOBAL_ICON : "adm-menu-crop",
				TEXT : BX.message("JS_CORE_FI_FRAME_Y"),
				ONCLICK : BX.delegate(this.frameFiles, this),
				CHECKED : (this.uploadParams["frameFiles"] == "Y")
			});
		}
		if (this.elementParams["edit"] && this.elementParams["description"] !== false)
		{
			menu.push({
				TEXT : BX.message("JS_CORE_FI_PIN_DESCRIPTION"),
				ONCLICK : BX.delegate(this.pinDescription, this),
				CHECKED : (this.uploadParams["pinDescription"] == "Y")
			});
		}
		if (this.elementParams["delete"])
		{
			menu.push({
				TEXT : BX.message("JS_CORE_FI_CLEAR"),
				ONCLICK : BX.delegate(this.deleteFiles, this),
				GLOBAL_ICON : "adm-menu-delete"
			});
		}
		if (menu.length > 0)
		{
			node.OPENER = new BX.COpener({
				DIV: node,
				TYPE: 'click',
				MENU: menu,
				ACTIVE_CLASS: 'adm-btn-active'
			});
			if (inputID)
			{
				node.__onOpenerMenuOpen = BX.delegate(function()
				{
					BX.adjust(BX(inputID).parentNode, { style : { position : "relative" } } );
					this.agent.init(BX(inputID));
					BX.removeCustomEvent(node.OPENER, 'onOpenerMenuOpen', node.__onOpenerMenuOpen);
				}, this);
				BX.addCustomEvent(node.OPENER, 'onOpenerMenuOpen', node.__onOpenerMenuOpen);
			}
		}
	},
	deinitMenu : function(node) {
		node = BX(node);
		if (!node || !node.OPENER)
			return;
		BX.removeCustomEvent(node.OPENER, 'onOpenerMenuOpen', node.__onOpenerMenuOpen);
		BX.unbindAll(node.OPENER.DIV);
		delete node.OPENER.DIV;
		delete node.OPENER;
	},
	handlerFilePathPopup : null,
	handlerFilePath : function(data)
	{
		if (BX.type.isArray(data))
		{
			var result = [];

			for (var ii = 0; ii < data.length; ii++)
			{
				if (BX.type.isNotEmptyString(data[ii]))
				{
					result.push({tmp_url : BX.util.htmlspecialchars(data[ii]), real_url : decodeURIComponent(data[ii])});
				}
			}
			this.agent.onAttach(result, {});
		}
		else
		{
			this.handlerFilePathPopup = (this.handlerFilePathPopup ||
				new filePath(this.id + 'filePath', {
					onApply : BX.delegate(this.handlerFilePath, this)
				}, this.uploadParams['maxCount']));
			this.handlerFilePathPopup.show();
		}
	},
	handlerMedialib : function(data)
	{
		if (!BX.type.isArray(data))
			data = [data];
			var result = [];

		for (var ii = 0; ii < data.length; ii++)
		{
			if (data[ii])
			{
				result.push({
					name : BX.UploaderUtils.getFileNameOnly(data[ii]['src']),
					description : data[ii]['description'],
					type : data[ii]['type'] + '/medialib',
					size : data[ii]['file_size'],
					sizeFormatted : data[ii]['file_size'],
					tmp_url : data[ii]['src'],
					real_url : data[ii]['src']
				});
			}
		}
		this.agent.onAttach(result, result);
	},
	handlerFileDialog : function(name, path)
	{
		if (BX.type.isNotEmptyString(name) && BX.type.isNotEmptyString(path))
		{
			var file = {
				name : name,
				type : (BX.UploaderUtils.isImageExt((name || '').lastIndexOf('.') > 0 ? name.substr(name.lastIndexOf('.')+1) : '') ? 'image' : 'notimage') + '/filedialog',
				tmp_url : path + '/' + name,
				real_url : path + '/' + name
			};
			this.agent.onAttach([file], [file]);
		}
	},
	clickCloudPointPath : '/bitrix/admin/clouds_file_search.php?lang=' + BX.message("LANGUAGE_ID") + '&n=undefined',
	clickCloudPointBound : false,
	clickCloudPoint : function()
	{
		if (this.clickCloudPointBound === false)
		{
			BX.addCustomEvent("onCloudFileIsChosen", BX.delegate(this.clickCloudPointChange, this));
			this.clickCloudPointBound = true;
		}
		BX.util.popup(this.clickCloudPointPath, 710, 600);
	},
	clickCloudPointChange : function(path)
	{
		var name = BX.UploaderUtils.getFileNameOnly(path);
		if (BX.type.isNotEmptyString(path))
		{
			var file = {
				name : name,
				type : (BX.UploaderUtils.isImageExt((name || '').lastIndexOf('.') > 0 ? name.substr(name.lastIndexOf('.')+1) : '') ? 'image' : 'notimage') + '/cloudfile',
				tmp_url : path
			};
			this.agent.onAttach([file], [file]);
		}
	},
	frameFlags : {
		hasNew : false,
		active : false,
		preparing : false,
		ready : false
	},
	framedItems : null,
	frameFiles : function(activeId)
	{
		if (activeId && !BX.type.isNumber(activeId) && activeId["type"] == "click")
		{
			this.uploadParams["frameFiles"] = (this.uploadParams["frameFiles"] == "Y" ? "N" : "Y");
			this.saveOptions("frameFiles", this.uploadParams["frameFiles"]);
			if (this.uploadParams["frameFiles"] == "N")
				return false;
		}
		if (this.frameFlags.active === true &&
			(activeId || this.frameFlags.hasNew))
		{
			try
			{

				if(!this['__frameFiles'])
				{
					this['__frameFiles'] = BX.delegate(function()
					{
						BX.removeCustomEvent(this, "onFilesCanBeFramed", this.__frameFiles);

						if(this.frameFilesWaitPopup)
							this.frameFilesWaitPopup.close();

						this.framedItems = new BX.UploaderUtils.Hash();

						if(!this.frameFilesBound)
						{
							this.frameFilesBound = true;
							BX.addCustomEvent(frameMaster, "onDeleteItem", BX.delegate(function(item) {
								this.deleteFile(item);
							}, this));
						}
						this.frameFlags.hasNew = false;
						var p = BX.clone(this.uploadParams, true);
						p["description"] = this.elementParams["description"];
						frameMaster.start(this.agent, (activeId || this.counters.newItemId), p);

						this['__frameFiles'] = null;
						delete this['__frameFiles'];
					}, this);
				}

				BX.removeCustomEvent(this, "onFilesCanBeFramed", this['__frameFiles']);

				if (this.frameFlags.ready === true)
				{
					this.__frameFiles();
				}
				else
				{
					this.frameFilesWait();
					BX.addCustomEvent(this, "onFilesCanBeFramed", this.__frameFiles);
				}
			}
			catch(e)
			{
				console.log(e);
			}
		}
		return true;
	},
	pinDescription : function(e)
	{
		this.uploadParams["pinDescription"] = (this.uploadParams["pinDescription"] == "Y" ? "N" : "Y");
		this.saveOptions("pinDescription", this.uploadParams["pinDescription"]);

		if (this.uploadParams["pinDescription"] == "Y")
		{
			if (!BX.hasClass(BX(this.id + "_block"), "mode-with-description"))
				BX.addClass(BX(this.id + "_block"), "mode-with-description");
		}
		else
		{
			BX.removeClass(BX(this.id + "_block"), "mode-with-description");
		}
		return BX.PreventDefault(e);
	},
	frameFilesWaitPopup : null,
	frameFilesWait : function()
	{
		if (this.frameFilesWaitPopup == null)
		{
			this.frameFilesWaitPopup = BX.PopupWindowManager.create(
				'popup-frame-wait' + this.id,
				null,
				{
					autoHide : true,
					titleBar: BX.message("JS_CORE_LOADING"),
					contentColor : 'white',
					closeIcon : true,
					closeByEsc : true,
					zIndex : getZIndex(1),
					content : '<span class="adm-photoeditor-popup-frame-wait"><span></span>' + BX.message("JS_CORE_FI_FRAME_IS_LOADING") + '</span>',
					overlay : {},
					events : {
						onPopupClose : BX.proxy(function() { BX.removeCustomEvent(this, "onFilesCanBeFramed", this.__frameFiles)}, this)
					},
					buttons : [
						new BX.PopupWindowButtonLink( {
							text : BX.message('JS_CORE_WINDOW_CANCEL'),
							className : "popup-window-button-link-cancel",
							events : { click : BX.delegate(function()
							{
								this.frameFilesWaitPopup.close();
							}, this) } } )
					]
				}
			);
		}
		this.frameFilesWaitPopup.show();
	},
	initFrameCounters : function()
	{
		this.counters = {
			images : {
				created : new BX.UploaderUtils.Hash(),
				ready : new BX.UploaderUtils.Hash()
			},
			uploaded : new BX.UploaderUtils.Hash(),
//			files : { created : [], ready : [] },
			newItemOrder : 0,
			newItemId : 0
		};
	},
	incrementFrameCounter : function(id, item)
	{
		if (item.dialogName == "BX.UploaderImage")
		{
			if (this.values && this.values[item.id])
			{
				this.counters.uploaded.setItem(id, item.dialogName);
			}
			else if (!this.counters.uploaded.hasItem(id) || this.counters.uploaded.getItem(id) !== item.dialogName)
			{
				if (this.frameFlags.hasNew !== true)
				{
					this.frameFlags.hasNew = true;
					this.counters.newItemOrder = this.counters.images.created.length;
					this.counters.newItemId = id;
				}
			}
			item.canvasIsReady = false;
			this.frameFlags.active = true;
			this.frameFlags.ready = false;
			this.counters.images.created.setItem(id, id);
		}
	},
	decrementFrameCounters : function(id, item)
	{
		if (item.dialogName == "BX.UploaderImage")
		{
			this.counters.images.created.removeItem(id);
			this.counters.images.ready.removeItem(id);
			//this.counters.uploaded.removeItem(id);
			this.frameFlags.active = (this.counters.images.created.length > 0);
		}
	},
	amountFrameCounter : function(id, item)
	{
		if (item.dialogName == "BX.UploaderImage")
		{
			item.canvasIsReady = true;
			if (this.counters.uploaded.hasItem(id))
			{
				BX.onCustomEvent(this.agent, "onFileIsReadyToFrame", [id, item]);
			}
		}
	},
	onFileIsReadyToFrame : function(id, item)
	{
		if (item.dialogName == "BX.UploaderImage" && this.counters.images.created.hasItem(id))
		{
			this.counters.images.ready.setItem(id, id);

			this.frameFlags.ready = (this.counters.images.created.length == this.counters.images.ready.length);

			if (this.frameFlags.ready)
			{
				BX.onCustomEvent(this, "onFilesCanBeFramed", [this]);
			}
		}
	},
	deleteFiles : function()
	{
		var items = this.agent.getItems(),
			item;

		while ((item = items.getFirst()) && item)
		{
			this.deleteFile(item, true);
		}
	},
	onQueueIsChanged : function()
	{
		if (this.inited === true && this.uploadParams['maxCount'] > 0)
		{
			this.checkUploadControl();
		}
	},
	onAttachFiles : function(files)
	{
		var error = false;
		if(files && this.inited === true && this.uploadParams['maxCount'] > 0)
		{
			if (this.uploadParams['maxCount'] == 1 && files.length > 0)
			{
				while (this.agent.getItems().length > 0)
					this.deleteFile(this.agent.getItems().getFirst(), true);
				while (files.length > 1)
					files.pop();
			}
			var acceptableL = this.uploadParams['maxCount'] - this.agent.getItems().length;
			acceptableL = (acceptableL > 0 ? acceptableL : 0);
			while (files.length > acceptableL)
			{
				files.pop();
				error = true;
			}
		}
		if (error)
		{
			this.onError(BX.message('JS_CORE_FI_TOO_MANY_FILES').replace('#amount#', this.uploadParams['maxCount']), true);
		}
		return files;
	},
	onFileIsCreated : function(id, item)
	{
		if (this.inited === true)
			item.IND = this.onUploadDoneCounter++;

		if (item.file["preview_url"] && item.file["width"] > 0 && item.file["height"] > 0)
		{
			BX.addCustomEvent(item, "onFileCanvasIsLoaded", BX.proxy(function(/*id, it, ag, image*/) {
				item.file["tmp_url"] = item.file["~tmp_url"];
				delete item.file["~tmp_url"];
				item.file["width"] = item.file["~width"];
				item.file["height"] = item.file["~height"];
				delete item.file["~width"];
				delete item.file["~height"];
				item.canvasIsLoaded = true;
				this.replaceHint(item);
			}, this));
			item.file["~tmp_url"] = item.file["tmp_url"];
			item.file["tmp_url"] = item.file["preview_url"];
			item.file["~width"] = item.file["width"];
			item.file["~height"] = item.file["height"];
			delete item.file["preview_url"];
		}
		else if (item.dialogName == "BX.UploaderImage")
		{
			BX.addCustomEvent(item, "onFileCanvasIsLoaded", BX.proxy(function(/*id, it, ag, image*/) {
				item.canvasIsLoaded = true;
				this.replaceHint(item);
			}, this));
		}
		item.description = (BX.type.isNotEmptyString(item.file["description"]) ? item.file["description"] : "");
		this.incrementFrameCounter(id, item);
		for (var ii in this['fileEvents'])
		{
			if (this['fileEvents'].hasOwnProperty(ii))
			{
				if (this['fileEvents'][ii])
				{
					BX.addCustomEvent(item, ii, this.fileEvents[ii]);
				}
			}
		}
	},
	onFileIsInited : function(id, item)
	{
		this.amountFrameCounter(id, item);
	},
	onFileIsAppended : function(id, item)
	{
		this.bindFile(item);
	},
	onFileIsBound : function(id, item)
	{
		this.bindFile(item);
	},
	onFileIsAttached : function(id, item)
	{
		if (item.file["sizeFormatted"])
		{
			item.size = item.file["sizeFormatted"];
			delete item.file["sizeFormatted"];
		}
		this.bindFile(item);
		if (item.file['tmp_url'])
			this.onUploadDone(item, {file : {uploadId : item.file.tmp_url}});
	},
	onFilesPropsAreModified : function(id, item, props)
	{
		if (props)
		{
			item.description = props.description;
			if (BX(item.id + 'Description'))
				BX(item.id + 'Description').value = item.description;
		}
	},
	onFileIsFramed : function(id, item, canvas, file)
	{
		if (canvas.width != item.canvas.width || canvas.height != item.canvas.height)
		{
			BX.adjust(item.canvas, { props : { width : canvas.width, height : canvas.height} });
		}
		else
		{
			BX.adjust(item.canvas, { props : { width : item.canvas.width - 1} });
			BX.adjust(item.canvas, { props : { width : item.canvas.width + 1} });
		}
		item.canvas.getContext("2d").drawImage(canvas,
			0, 0, canvas.width, canvas.height,
			0, 0, item.canvas.width, item.canvas.height
		);
		item.file = file;
		this.framedItems.setItem(item.id, item);
	},
	onFilesAreFramed : function()
	{
		if (this.framedItems && this.framedItems.length > 0)
		{
			if (this.uploadParams["uploadType"] == "file")
			{

			}
			else
			{
				this.agent.restoreItems(this.framedItems, true, true);
				this.agent.submit();
			}
		}
	},
	onFileIsDragged : function(item, node)
	{
		if (BX.hasClass(BX(this.id + '_block'), 'mode-file'))
		{
			BX.addClass(node, 'mode-file');
		}
	},
	onUploadStart : function(raw)
	{
		if (raw.length > 0)
		{
			var node, item, ii;
			for (ii in raw["items"])
			{
				if (raw["items"].hasOwnProperty(ii))
				{
					item = raw["items"][ii];
					node = (this.agent.getItem(item.id) || {node: false}).node;
					if (node && !BX.hasClass(node, "adm-fileinput-item-uploading"))
						BX.addClass(node, "adm-fileinput-item-uploading");
				}
			}
		}
	},
	onUploadProgress : function(item, progress)
	{
		var node = this.agent.getItem(item.id).node;
		if (!node.hasAttribute("bx-progress-bound"))
		{
			node.setAttribute("bx-progress-bound", "Y");
			BX.addClass(node, "adm-fileinput-item-uploading");
		}
		progress = (progress < 5 ? 5 : (progress > 100 ? 100 : progress));
		progress = Math.ceil(progress);
		if(BX(item.id + 'Progress', true))
		{
			BX(item.id + 'Progress', true).style.width = progress + '%';
		}
	},
	onUploadDoneCounter : 0,
	replaceInput : function(item, data)
	{
		var node = this.agent.getItem(item.id).node,
			input_name = node["__replaceInputName"],
			id = item.id + 'Value',
			input = BX.findChild(node, {tagName : "INPUT", attr : {id : id}}, true),
			tmp,
			file = (data && data['file'] && data['file']['files'] && data['file']['files']['default'] ? data['file']['files']['default'] : false);

		if (!input_name)
			input_name = node["__replaceInputName"] = input.name;

		if (file)
		{
			input.parentNode.insertBefore(BX.create("INPUT", { attrs : { type : "hidden", name : input_name + '[name]', id : input.id, value : item.name }}), input);
			input.parentNode.insertBefore(BX.create("INPUT", { attrs : { type : "hidden", name : input_name + '[type]', value : file['type'] }}), input);
			input.parentNode.insertBefore(BX.create("INPUT", { attrs : { type : "hidden", name : input_name + '[tmp_name]', value : file['path'] }}), input);
			input.parentNode.insertBefore(BX.create("INPUT", { attrs : { type : "hidden", name : input_name + '[size]', value : file['size'] }}), input);
			input.parentNode.insertBefore(BX.create("INPUT", { attrs : { type : "hidden", name : input_name + '[error]', value : 0 }}), input);
		}
		else
		{
			input.parentNode.insertBefore(BX.create("INPUT", { attrs : { type : "hidden", name : input_name, id : input.id, value : data['file']['uploadId'] }}), input);
		}

		while (BX(input) && input.name.indexOf(input_name) === 0)
		{
			tmp = input.nextSibling;
			BX.remove(input);
			input = tmp;
		}

		if (this.uploadParams["maxCount"] <= 1)
		{
			var n = BX.findChild(this.agent.form, {tagName : "INPUT", attr : {name : (input_name)}}, false);
			if (n)
			{
				BX.adjust(n, { attrs : { disabled : true }});
				var nDelName = input_name + '_del';
				if (input_name.indexOf('[') > 0)
					nDelName = input_name.substr(0, input_name.indexOf('[')) + '_del' + input_name.substr(input_name.indexOf('['));
				n = BX.findChild(this.agent.form, {tagName : "INPUT", attr : {name : nDelName}}, false);
				if (n)
					BX.adjust(n, { attrs : { disabled : true } } );
			}
		}
	},
	onUploadDone : function(it, data)
	{
		var pointer = this.agent.getItem(it.id),
			item = pointer.item,
			node = pointer.node;

		if (item && BX(node))
		{
			var file = (data && data['file'] && data['file']['files'] && data['file']['files']['default'] ? data['file']['files']['default'] : false);

			this.counters.uploaded.setItem(item.id, item.dialogName);

			if (file && (file["wasChangedOnServer"] === true || (item.dialogName == "BX.UploaderImage") !== BX.UploaderUtils.isImage(file["name"], file["type"], file["size"])))
			{
				this.replaceItem(item, file, node);
			}
			else if (item.dialogName == "BX.UploaderImage")
			{
				this.amountFrameCounter(item.id, item);
			}

			this.replaceInput(item, data);
			if (node.firstChild && BX.hasClass(node.firstChild, "adm-fileinput-item-saved"))
				BX.removeClass(node.firstChild, "adm-fileinput-item-saved");

			node.removeAttribute("bx-progress-bound");
			BX.removeClass(node, "adm-fileinput-item-uploading");

			if (this.uploadParams["frameFiles"] == "Y")
				this.frameFiles();
		}
	},
	onUploadError : function(item, data)
	{
		var node = this.agent.getItem(item.id).node;
		node.removeAttribute("bx-progress-bound");
		BX.removeClass(node, "adm-fileinput-item-uploading adm-fileinput-item-image");
		BX.addClass(node, "adm-fileinput-item-error");
		if (data && data["error"])
		{
			node = BX(item.id + 'ErrorText');
			if (!node)
			{
				node = BX.create('SPAN', { attrs : { id : item.id + 'ErrorText' , className : 'container-doc-error' } } );
				BX(item.id + 'Name').parentNode.appendChild(node);
			}
			node.innerHTML = data["error"];
		}
	},
	onUploadRestore : function () {

	},
	onError : function(errorText, errorModal)
	{
		BX.addClass(this.agent.placeHolder, "adm-fileinput-drag-area-error");
		if (errorModal === true)
		{
			alert(errorText);
		}
		else
		{
			BX.debug(errorText);
		}
	},
	bindFile : function(item)
	{
		var id = item.id,
			node = this.agent.getItem(item.id).node;
		if (item.dialogName == "BX.UploaderImage")
		{
			BX.removeClass(node, "adm-fileinput-item-file");
			BX.addClass(node, "adm-fileinput-item-image");
		}
		else
		{
			BX.removeClass(node, "adm-fileinput-item-image");
			BX.addClass(node, "adm-fileinput-item-file");
		}
		if (node && !node.hasAttribute("bx-bound-editor"))
		{
			node.setAttribute("bx-bound-editor", "Y");
			if (this.agent.dialogName == "BX.UploaderSimple")
			{
				BX.bind(node, "dblclick", BX.delegate(function(e){
					var pointer = this.agent.getItem(item.id);
					if (pointer && pointer.item.dialogName == "BX.UploaderImage")
					{
						var url = (pointer.item.file['real_url'] || pointer.item.file['tmp_url']);
						if (url)
							BX.util.popup(url);
					}
					return BX.PreventDefault(e);
				}, this));
			}
			else if (this.elementParams["edit"])
			{
				BX.bind(node, "dblclick", BX.delegate(function(e){
					BX.PreventDefault(e);
					var pointer = this.agent.getItem(item.id);
					if (pointer && pointer.item.dialogName == "BX.UploaderImage")
					{
						this.frameFiles(item.id);
					}
				}, this));
			}
		}
		if (BX(id + 'Edit') && !BX(id + 'Edit').hasAttribute("bx-bound"))
		{
			BX(id + 'Edit').setAttribute("bx-bound", "Y");
			if (this.elementParams["edit"])
			{
				BX.bind(BX(id + 'Edit'), "click", BX.delegate(function(e){
					BX.PreventDefault(e);
					var pointer = this.agent.getItem(item.id);
					if (pointer && pointer.item.dialogName == "BX.UploaderImage")
					{
						this.frameFiles(item.id);
					}
				}, this));
			}
			else
			{
				BX.hide(BX(id + 'Edit'));
			}
		}
		if (BX(id + 'Del') && !BX(id + 'Del').hasAttribute("bx-bound"))
		{
			BX(id + 'Del').setAttribute("bx-bound", "Y");
			BX.bind(BX(id + 'Del'), "click", BX.delegate(function(e){
				BX.PreventDefault(e);
				this.deleteFile(item);
			}, this));
		}
		if (BX(id + 'Description') && !BX(id + 'Description').hasAttribute("bx-bound"))
		{
			BX(id + 'Description').setAttribute("bx-bound", "Y");
			BX.bind(BX(id + 'Description'), "click", BX.delegate(function(e){
				BX.defer_proxy(function(){
					BX.focus(BX(id + 'Description'));
				})();
				return BX.PreventDefault(e);
			}, this));
			BX.bind(BX(id + 'Description'), "blur", function(){ item.description = BX(id + 'Description').value; } );
		}
		this.replaceHint(item);
	},
	replaceHint : function(item)
	{
		var id = item.id,
			node = this.agent.getItem(item.id).node;
		if (node.hint)
			node.hint.Destroy();
		var hint = '<span class="adm-fileinput-drag-area-popup-title">' + BX.util.htmlspecialchars(item.name) + '</span>';
		if (item.size)
			hint += '<span class="adm-fileinput-drag-area-popup-param">' + BX.message('JS_CORE_FILE_INFO_SIZE') + ':&nbsp;<span>' + item.size + '</span></span>';
		if (item.dialogName == "BX.UploaderImage")
		{
			var text = '' ;
			if (item.file["width"] > 0 && item.file["height"] > 0)
			{
				text = item.file.width + 'x' + item.file.height;
			}
			else if (item.canvasIsLoaded && item.canvas)
			{
				text = item.canvas.width + 'x' + item.canvas.height;
			}
			if (text != '')
				hint += '<span class="adm-fileinput-drag-area-popup-param">' + BX.message('JS_CORE_FILE_INFO_DIM') + ':&nbsp;<span>' + text + '</span></span>';
		}

		if (item.description == undefined)
			item.description = (BX(id + 'Description') && BX(id + 'Description').value ? BX(id + 'Description').value : '');
		if (item.description)
			hint +=  '<span class="adm-fileinput-drag-area-popup-param">' + BX.message('JS_CORE_FILE_DESCRIPTION') + ':&nbsp;<span>' + item.description + '</span></span>';
		var path = item["file"] ? (item["file"]["real_url"] || item["file"]["tmp_url"]) : '';
		if (path)
		{
			path = BX.util.htmlspecialchars(path);
			hint += '<span class="adm-fileinput-drag-area-popup-param">' + BX.message('JS_CORE_FILE_INFO_LINK') + ':&nbsp;<span><a target="_blank" href="' + path.replace(/[%]/g, "%25") + '">' + path + '</a></span></span>';
		}

		node.hint = new BX.CHint({
				parent: node,
				show_timeout: 10,
				hide_timeout: 200,
				dx: -10,
				preventHide: true,
				min_width: 165,
				hint: hint
			});
	},
	replaceItem : function(item, file, thumb)
	{
		file['id'] = item['id'];
		item.replaced = true;
		this.deleteFile(item, true);
		thumb.removeAttribute("bx-bound-editor");
		this.agent.onAttach([file], [thumb]);
	},
	deleteFile : function(item, immediate)
	{
		var pointer = (item ? this.agent.getItem(item.id) : false);
		if (!pointer)
			return;
		item = pointer.item;
		this.decrementFrameCounters(item.id, item);
		for (var ii in this['fileEvents'])
		{
			if (this['fileEvents'].hasOwnProperty(ii))
				BX.removeCustomEvent(item, ii, this['fileEvents'][ii]);
		}
		var node = pointer.node;
		if (node)
		{
			if (node.hint)
				node.hint.Destroy();
			if (item.replaced !== true)
				BX.addClass(node, "adm-fileinput-item-remove");
		}

		var name = (item['file']['input_name'] || node["__replaceInputName"]),
			nDelName = name + '_del';
		if (name && name.indexOf('[') > 0)
			nDelName = name.substr(0, name.indexOf('[')) + '_del' + name.substr(name.indexOf('['));

		if (item['file']['input_name'])
		{
			node = BX.create("INPUT", { props : {
				name : name,
				type : "hidden",
				value : item['file']['input_value']}});
			this.agent.form.appendChild(node);
			node = BX.create("INPUT", { props : {
				name : nDelName,
				type : "hidden",
				value : "Y"}});
			this.agent.form.appendChild(node);
		}
		else
		{
			var n = BX.findChild(this.agent.form, {tagName : "INPUT", attr : { name : name, disabled : true }}, false);
			if (n)
			{
				BX.adjust(n, { attrs : { disabled : false } } );
				BX.adjust(BX.findChild(this.agent.form, {tagName : "INPUT", attr : { name : nDelName, disabled : true }}, false), { attrs : { disabled : false } } );
			}
		}

		if (immediate !== true)
			setTimeout(function(){ item.deleteFile(); }, 500);
		else
			item.deleteFile();
	},
	destroy : function() {
		this.deleteFiles();
	}
};
BX["UI"].FileInput.getInstance = function(id) {
	return repo[id];
};
var filePath = function(id, events, maxCount)
{
	this.single = parseInt(maxCount) === 1;
	this.id = id;
	this.number = 0;
	this.templateNode = [
			'<div class="adm-fileinput-item-panel"', (this.single ? ' style="display: none;"' : ''), '>',
				'<span class="adm-fileinput-item-panel-btn adm-btn-del" id="#id#_#number#_del">&nbsp;</span>',
			'</div>',
			'<div class="adm-fileinput-urls-item">',
				'<label for="#id#_#number#_path">', BX.message("JS_CORE_FI_LINK"),'</label>',
				'<input id="#id#_#number#_path" type="text" value="" />',
			'</div>'
		].join("").replace(/#id#/gi, this.id);
	var v1 = (this.number++);
	this.number++;
	this.template = [
		'<div class="adm-fileinput-urls-container" id="#id#_container">',
			'<ol class="adm-fileinput-list adm-fileinput-urls" id="#id#_list">',
				'<li>',
					this.templateNode.replace(/#number#/gi, v1 + ''),
				'</li>',
			'</ol>',
			'<a href="#" id="#id#_add_point" class="adm-fileinput-item-add"', (this.single ? ' style="display:none"' : ''),'>', BX.message("JS_CORE_FI_ADD_LINK"), '</a>',
			'<div style="clear:both;"></div>',
		'</div>'].join("").replace(/#id#/gi, this.id);

	if (events)
	{
		for (var ii in events)
		{
			if (events["hasOwnProperty"] && events.hasOwnProperty(ii))
			{
				BX.addCustomEvent(this, ii, events[ii]);
			}
		}
	}
	this._onAfterShow = BX.delegate(this.onAfterShow, this);
	this._onApply = BX.delegate(this.onApply, this);
	this._onCancel = BX.delegate(this.onCancel, this);
	this._addRow = BX.delegate(this.addRow, this);
	this._delRow = BX.delegate(this.delRow, this);
};
filePath.prototype = {
	popup : null,
	number : 0,
	addRow : function(e)
	{
		BX.PreventDefault(e);
		var list = BX(this.id + '_list'), v2 = (this.number++);
		if (list)
		{
			list.appendChild(BX.create('LI', {html :  this.templateNode.replace(/#number#/gi, v2 + '')}));
			BX.defer_proxy(function(){
				BX.bind(BX(this.id + '_' + v2 + '_del'), "click", this._delRow);
				this.bindFocus();
			}, this)();
		}
		return false;
	},
	bindFocus : function()
	{
		var list = BX(this.id + '_list');
		if (list && !this.single)
		{
			for (var ii = 0; ii < this.number; ii++)
			{
				if (BX(this.id + '_' + ii + '_path'))
				{
					BX.unbind(BX(this.id + '_' + ii + '_path'), "focus", this._addRow);
				}
			}
			for (ii = this.number; ii >= 0; ii--)
			{
				if (BX(this.id + '_' + ii + '_path'))
				{
					BX.bind(BX(this.id + '_' + ii + '_path'), "focus", this._addRow);
					break;
				}
			}
		}
	},
	delRow : function()
	{
		var node = BX.proxy_context;
		if (BX(node))
		{
			var li = BX.findParent(node, {tagName : "LI"});
			if (BX(li))
			{
				var rebuild = (li == li.parentNode.lastChild);
				BX.remove(li);
				if (rebuild)
					this.bindFocus();
			}
		}
	},
	onApply : function()
	{
		var list = BX(this.id + '_list'), result = [], v;
		if (list)
		{
			for (var ii = 0; ii <= this.number; ii++)
			{
				v = (BX(this.id + '_' + ii + '_path') ? BX(this.id + '_' + ii + '_path').value : '');
				if (v && v.length > 0)
					result.push(v);
			}
		}
		BX.onCustomEvent(this, "onApply", [result, this]);
		this.onCancel();
	},
	onCancel : function()
	{
		this.popup.close();
		this.popup.destroy();
		this.popup = null;
		this.number = 0;
	},
	onAfterShow : function()
	{
		BX.bind(BX(this.id + '_add_point'), "click", this._addRow);
		for (var ii = 0; ii <= this.number; ii++)
		{
			if (BX(this.id + '_' + ii + '_del'))
			{
				BX.bind(BX(this.id + '_' + ii + '_del'), "click", this._delRow);
			}
		}
		this.bindFocus();
		BX.removeCustomEvent(this.popup, "onAfterPopupShow", this._onAfterShow);
	},
	show : function()
	{
		if (this.popup === null)
		{
			var editorNode = BX.create("DIV", {
				attrs : {id : this.id + 'Proper'},
				style : { display : "none" },
				html : this.template
			});
			this.popup = BX.PopupWindowManager.create(
				'popup' + this.id,
				null,
				{
					autoHide : true,
					lightShadow : true,
					closeIcon : false,
					closeByEsc : true,
					zIndex : getZIndex(1),
					content : editorNode,
					overlay : {},
					events : {
						onAfterPopupShow : this._onAfterShow
					},
					buttons : [
						new BX.PopupWindowButton( {text : BX.message("JS_CORE_FI_ADD"), className : "popup-window-button-accept", events : { click : this._onApply } } ),
						new BX.PopupWindowButtonLink( {text : BX.message("JS_CORE_FI_CANCEL"), className : "popup-window-button-link-cancel", events : { click : this._onCancel } } )
					]
				}
			);
		}
		this.popup.show();
		this.popup.adjustPosition();
	}
};
var
FramePresetID = 0,
FramePreset = function() {
	var d = function(params) {
		this.id = 'framePreset' + (FramePresetID++);
		this.onAfterShow = BX.delegate(this.onAfterShow, this);
		this.addRow = BX.delegate(this.addRow, this);
		this.delRow = BX.delegate(this.delRow, this);
		if (params)
		{
			this.init(params);
		}
	};
	d.prototype = {
		active : null,
		id : 'framePreset0',
		values : [],
		valuesInner : [],
		popup : null,
		number : 0,
		maxLength : 10,
		getTemplateNode : function()
		{
			return [
				'<li>',
				'<div class="adm-fileinput-item-panel">',
				'<span class="adm-fileinput-item-panel-btn adm-btn-del" id="#classId##id#_del">&nbsp;</span>',
				'</div>',
				'<div class="adm-fileinput-presets-item">',
				'<input class="adm-fileinput-presets-title" id="presets_#id#__title_" name="presets[#id#][title]" type="text" value="#title#" placeholder="', BX.message("JS_CORE_FI_TITLE"), '" />',
				'<input class="adm-fileinput-presets-width" name="presets[#id#][width]" type="text" value="#width#" placeholder="', BX.message("JS_CORE_FI_WIDTH"), '" />',
				'<input class="adm-fileinput-presets-hight" name="presets[#id#][height]" type="text" value="#height#" placeholder="', BX.message("JS_CORE_FI_HEIGHT"), '" />',
				'</div>',
				'</li>'
			].join("").replace(/#classId#/gi, this.id);
		},
		getTemplate : function()
		{
			return [
				'<div class="adm-fileinput-presets-container" id="#classId#_container">',
				'<form id="#classId#_form">',
				'<ol class="adm-fileinput-list adm-fileinput-presets" id="#classId#_list">',
				'#nodes#',
				'</ol>',
				'</form>',
				'<a href="#" id="#classId#_add_point" class="adm-fileinput-item-add">', BX.message("JS_CORE_FI_ADD_PRESET"), '</a>',
				'<div style="clear:both;"></div>',
				'</div>'
			].join("");
		},
		init : function(params)
		{
			this.values = [];
			this.length = 0;
			if (BX.type.isArray(params["presets"]))
			{
				var id;
				for (var ii = 0; ii < params["presets"].length; ii++)
				{
					id = this.values.length;
					this.values.push({id : id,
						title : params["presets"][ii]["title"],
						width : params["presets"][ii]["width"],
						height : params["presets"][ii]["height"]});
				}

				this.length = this.values.length;
				this.setActive(params["presetActive"]);
			}
		},
		setActive : function(id)
		{
			id = parseInt(id);
			this.activeId = this.values[id] ? id : 0;
			this.active = (this.values[this.activeId] || null);
		},
		edit : function(params)
		{
			var
				node = BX.proxy_context,
				html = '',
				values = this.values;
			for (var ii = 0; ii < values.length; ii ++)
			{
				html += this.getTemplateNode()
					.replace(/#id#/gi, values[ii]["id"])
					.replace(/#title#/gi, values[ii]["title"])
					.replace(/#width#/gi, values[ii]["width"])
					.replace(/#height#/gi, values[ii]["height"]);
			}

			if (this.values.length < this.maxLength && params && params["width"] && params["height"])
			{
				html += this.getTemplateNode()
					.replace(/#id#/gi, ii)
					.replace(/#title#/gi, "")
					.replace(/#width#/gi, params["width"])
					.replace(/#height#/gi, params["height"]);
			}

			if (!!this.popup)
				this.popup.close();
			var res = BX.pos(node);

			this.popup = new BX.PopupWindow('bx-preset-popup-' + node.id, node, {
				lightShadow : true,
				offsetTop: -3,
				className : "bxu-poster-popup",
				offsetLeft: Math.ceil(res.width / 2),
				autoHide: true,
				closeByEsc: true,
				zIndex : getZIndex(30 + BX.PopupWindow.getOption("popupOverlayZindex") - BX.PopupWindow.getOption("popupZindex")),
				bindOptions: {position: "top"},
				overlay : false,
				events : {
					onAfterPopupShow : this.onAfterShow,
					onPopupClose : function() { this.destroy() },
					onPopupDestroy : BX.proxy(function() { this.popup = null; }, this)
				},
				buttons : [
					new BX.PopupWindowButton( {text : BX.message('CANVAS_OK'), className : "popup-window-button-accept", events : { click : BX.delegate(function() {
						var data = BX.UploaderUtils.FormToArray(BX(this.id + '_form'));
						this.onApply(data.data);
						this.popup.close();
					}, this) } } ),
					new BX.PopupWindowButtonLink( {text : BX.message('CANVAS_CANCEL'), className : "popup-window-button-link-cancel", events : { click : BX.delegate(function(){this.popup.close();}, this) } } )
				],
				content : this.getTemplate().replace(/#classId#/gi, this.id).replace(/#nodes#/i, html)
			});
			this.popup.show();
			this.popup.setAngle({position:'bottom'});
			this.popup.bindOptions.forceBindPosition = true;
			this.popup.adjustPosition();
			BX.focus(BX('popupText' + node.id));
			this.popup.bindOptions.forceBindPosition = false;
		},
		onAfterShow : function()
		{
			BX.bind(BX(this.id + "_add_point"), "click", this.addRow);
			var node, notEmpty = false;
			for (var ii = 0; ii < this.length; ii++)
			{
				node = BX(this.id + ii + "_del");
				if (node)
				{
					BX.bind(node, "click", this.delRow);
					notEmpty = (notEmpty===false ? 0 : notEmpty) + 1;
				}
			}
			if (notEmpty === false)
				this.addRow();
			this.checkAddButton();
			if (BX('presets_' + ii + '__title_'))
				BX.focus(BX('presets_' + ii + '__title_'))

		},
		checkAddButton : function()
		{
			var list = BX(this.id + '_list'),
				active = (this.maxLength > list.childNodes.length);
			if (active)
				BX.removeClass(BX(this.id + "_add_point"), "disabled");
			else
				BX.addClass(BX(this.id + "_add_point"), "disabled");
			return active;
		},
		addRow : function(e)
		{
			BX.PreventDefault(e);
			var list = BX(this.id + '_list'), id;
			if (list && this.checkAddButton())
			{
				id = this.length++;
				list.appendChild(BX.create('LI', {html :  this.getTemplateNode()
					.replace(/^<li(.*?)>/gi, "")
					.replace(/<\/li(.*?)>$/gi, "")
					.replace(/#id#/gi, id)
					.replace(/#title#/gi, "")
					.replace(/#width#/gi, "")
					.replace(/#height#/gi, "") }));
				BX.defer_proxy(function(){
					BX.bind(BX(this.id + id + "_del"), "click", this.delRow);
				}, this)();
			}
			this.checkAddButton();
			return false;
		},
		delRow : function()
		{
			var node = BX.proxy_context;
			if (BX(node))
			{
				var li = BX.findParent(node, {tagName : "LI"});
				if (li)
				{
					BX.remove(li);
				}
			}
			this.checkAddButton();
		},
		onApply : function(data)
		{
			this.values = [];
			if (data && data["presets"])
			{
				data["presets"] = BX.util.array_values(data["presets"]);
				for (var id, ii = 0; ii < data["presets"].length; ii++)
				{
					if (data["presets"][ii] && data["presets"][ii]["width"] > 0 && data["presets"][ii]["height"] > 0)
					{
						id = this.values.length;
						this.values.push({
							id : id,
							width : data["presets"][ii]["width"],
							height : data["presets"][ii]["height"],
							title : (data["presets"][ii]["title"] ||
							(data["presets"][ii]["width"] + 'x' + data["presets"][ii]["height"]))
						});
					}
				}
			}
			this.save();
			BX.onCustomEvent(this, "onApply", [this.values, this]);
		},
		savedLastTime : '',
		save : function()
		{
			var sParam = '';
			sParam += '&p[0][c]=main&p[0][n]=fileinput';
			if (this.values.length > 0)
			{
				for (var jj, ii = 0; ii < this.values.length; ii++)
				{
					for (jj in this.values[ii])
					{
						if (this.values[ii].hasOwnProperty(jj))
						{
							if (jj != "id")
								sParam += "&p[0][v][presets][" + ii + "][" + jj + "]=" + BX.util.urlencode(this.values[ii][jj]);
						}
					}
				}
			}
			else
			{
				sParam += "&p[0][v][presets]=";
			}
			if (this.savedLastTime != sParam)
			{
				BX.ajax({
					'method': 'GET',
					'dataType': 'html',
					'processData': false,
					'cache': false,
					'url': BX.userOptions.path+sParam+'&sessid='+BX.bitrix_sessid()
				});
			}
		},
		getActive : function()
		{
			this.activeId = (this.values[this.activeId] ? this.activeId : 0);
			var res = this.values[this.activeId];
			if (res)
				res["id"] = this.activeId;
			return (res || null);
		}
	};
	return d;
} ();

var preset, cnv,
	FrameMaster = function(params)
{
	this.params = params;
	this.preset = preset = (preset || new FramePreset(params));
	BX.addCustomEvent(this.preset, "onApply", BX.delegate(this.onPresetsApply, this));
	this.id = 'FM'; // TODO Generate some ID
	this.handlers = {
		show : BX.delegate(this.onShow, this),
		afterShow : BX.delegate(this.onAfterShow, this),
		close : BX.delegate(this.onClose, this),
		cancel : BX.delegate(this.onCancel, this),
		apply : BX.delegate(this.onApply, this)
	}
};
FrameMaster.prototype = {
	id : '',
	canvas : null,
	description : null,
	agent : null,
	items : null,
	activeItem: null,
	popup : null,
	init : function(params)
	{
		if (params)
		{
			this.params = params;
			this.preset.init(params);
		}
		this.params = (this.params || {});
		this.params["description"] = (this.params["description"] !== false);
	},
	start : function(agent, activeId, params)
	{
		this.init(params);
		this.agent = agent;
		this.items = new BX.UploaderUtils.Hash();
		cnv = (cnv || new BX.UploaderFileCnvConstr());
		var items = this.agent.getItems(),
			item, id;
		items.reset();
		while ((item = items.getNext()) && item)
		{
			if (item.dialogName == "BX.UploaderImage")
			{
				id = this.id + '_' + item.id;
				this.items.setItem(id , {
					id : id,
					item : item,
					canvas : item.canvas.cloneNode(true),
					file : null,
					props : {
						description : item.description
					}
				} );
				if (activeId == item.id)
					activeId = id;
			}
		}
		if (this.items.length > 0)
		{
			this.activeItem = (this.items.hasItem(activeId) ? this.items.getItem(activeId) : this.items.getFirst());
			this.showEditor();
		}
	},
	finish : function(save)
	{
		if (this.items !== null && this.agent !== null)
		{
			var item;
			this.items.reset();
			while ((item = this.items.getNext()) && item)
			{
				if (save && item.props)
				{
					BX.onCustomEvent(this.agent, "onFilesPropsAreModified", [item.item.id, item.item, item.props]);
				}
				if (save && item.file)
				{
					BX.onCustomEvent(this.agent, "onFileIsFramed", [item.item.id, item.item, item.canvas, item.file]);
				}
				BX.remove(item.canvas);
				delete item.canvas;
				delete item.file;
				delete item.item;
				delete item.props;

			}
			BX.onCustomEvent(this.agent, "onFilesAreFramed", []);
		}
		this.agent = null;
		this.items = null;
		this.activeItem = null;
	},
	onShow : function() { },
	bindThumbItem : function(item) {
		var node = BX(item.id + 'EditorItemCanvas');
		node.parentNode.replaceChild(item.canvas, node);

		BX.addClass(item.canvas, "adm-photoeditor-preview-panel-container-img");

		item.canvas.setAttribute("id", item.id + 'EditorItemCanvas');
		var i = 0,
			f = function(id) {
			i++;
			if (id)
			{
				if (i > 1)
					BX.adjust(item.canvas, { props : { width : item.item.canvas.width, height : item.item.canvas.height} });
				BX.removeCustomEvent(item.item, "onFileIsInited", f);
			}
			item.canvas.getContext("2d").drawImage(item.item.canvas, 0, 0);
		};
		BX.addCustomEvent(item.item, "onFileIsInited", f);
		f();

		BX.removeClass(BX(item.id + 'EditorItem'), "adm-photoeditor-preview-panel-container-wait");
		BX.bind(BX(item.id + 'EditorItem'), "click", BX.proxy(function() { if (this.activeItem !== item) { this.setActiveItem(item); } }, this));
		BX.bind(BX(item.id + 'EditorItemDelete'), "click", BX.proxy(function(e){
			BX.PreventDefault(e); this.deleteItem(item);}, this));
	},
	deleteItem : function(item) {
		if (this.activeItem == item)
		{
			this.items.setPointer(item.id);
			var active = this.items.getNext();
			if (active)
				this.setActiveItem(active);
			else
				this.clearActiveItem();
		}
		BX.remove(BX(item.id + 'EditorItem'));
		item = this.items.removeItem(item.id);
		BX.onCustomEvent(this, "onDeleteItem", [item.item]);
	},
	saveActiveItem : function(canvas) {
		if (this.activeItem !== null)
		{
			var item = this.activeItem,
				res = BX.UploaderUtils.scaleImage(canvas, {width : thumbSize, height : thumbSize});
			if (res.destin.width != item.canvas.width || res.destin.height != item.canvas.height)
			{
				BX.adjust(item.canvas, { props : res.destin });
			}
			else
			{
				BX.adjust(item.canvas, { props : { width : item.canvas.width - 1} });
				BX.adjust(item.canvas, { props : { width : item.canvas.width + 1} });
			}
			item.canvas.getContext("2d").drawImage(canvas,
				0, 0, canvas.width, canvas.height,
				0, 0, item.canvas.width, item.canvas.height
			);
			var dataURI = canvas.toDataURL(item.item.file.type, 0.75);
			item.file = BX.UploaderUtils.dataURLToBlob(dataURI);
			item.file.width = canvas.width;
			item.file.height = canvas.height;
		}
	},
	saveActiveItemChanges : function()
	{
		if (this.activeItem != null)
		{
			this.activeItem.props.description = this.description.value;
			if (this.canvas.changed === true)
			{
				BX.onCustomEvent(this.canvas, "onChange", [this.canvas.getCanvas(), this.canvas]);
			}
		}
		return null;
	},
	clearActiveItem : function() {
		this.activeItem = this.saveActiveItemChanges();
		this.canvas.set(BX.create('CANVAS'), { props : { width : 100, height : 100 } });
		this.description.value = '';
	},
	setActiveItem : function(item) {
		if (this.activeItem != item)
		{
			BX.onCustomEvent(this, "onActiveItemIsChanged", [item, this.activeItem]);
			BX.onCustomEvent(this.canvas, "onCropHasToBeHidden", []);
			BX.onCustomEvent(this.canvas, "onActiveItemIsChanged", []);
			if (this.activeItem !== null)
			{
				BX.removeClass(BX(this.activeItem.id + 'EditorItem'), "active");
			}
			this.saveActiveItemChanges();
		}

		if (!BX.hasClass(BX(item.id + 'EditorItem'), "active"))
		{
			BX.addClass(BX(item.id + 'EditorItem'), "active");
		}

		this.activeItem = item;
		this.description.value = item.props.description;
		var file = (item.file || item.item.file);
		this.canvas.set(item.canvas, { props : { width : file.width, height : file.height } });
		if (item.canvas.width != file.width || item.canvas.height != file.height)
		{
			item.__onload = BX.proxy(function(image)
			{
				if (this.activeItem == item)
				{
					BX.adjust(cnv.getCanvas(), { props : { width : image.width, height : image.height } } );
					cnv.getContext().drawImage(image, 0, 0);
					this.canvas.set(cnv.getCanvas(), false);
					item.__onerror();
				}
			}, this);
			item.__onerror = BX.proxy(function()
			{
				if (this.activeItem == item)
				{
					item.__onload = null;
					delete item.__onload;
					item.__onerror = null;
					delete item.__onerror;
				}
			}, this);
			BX.defer_proxy(function(){cnv.push(file, item.__onload, item.__onerror);})();
		}
	},
	onAfterShow : function() {
		try
		{
			this.bindTemplate();
		}
		catch(e)
		{
			this['bindTemplateCounter'] = (this['bindTemplateCounter'] || 0) + 1;
			if (this['bindTemplateCounter'] < 10)
			{
				setTimeout(BX.proxy(this.onAfterShow, this), 500);
			}
		}

		var item;
		this.items.reset();
		while ((item = this.items.getNext()) && item)
		{
			this.bindThumbItem(item);
		}

		this.canvas.setCancelNode(this.id + "cancel");
		this.canvas.setMapCollapsed(this.id + "MapCollapsed");
		this.setActiveItem(this.activeItem);

		BX.bind(BX(this.id + 'turn-l'), "click", BX.proxy(function(){ this.rotate(false); }, this.canvas));
		BX.bind(BX(this.id + 'turn-r'), "click", BX.proxy(function(){ this.rotate(true); }, this.canvas));
		BX.bind(BX(this.id + 'flip-v'), "click", BX.proxy(function(){ this.flip(false); }, this.canvas));
		BX.bind(BX(this.id + 'flip-h'), "click", BX.proxy(function(){ this.flip(true); }, this.canvas));
		BX.bind(BX(this.id + 'crop'), "click", BX.proxy(function(){ this.crop(BX.proxy_context); }, this.canvas));
		BX.bind(BX(this.id + 'grayscale'), "click", BX.proxy(function(){ this.blackAndWhite(); }, this.canvas));
		BX.bind(BX(this.id + 'sign'), "click", BX.proxy(function(){ this.poster(BX.proxy_context); }, this.canvas));

		BX.bind(BX(this.id + 'scaleIndicator'), "mousedown", BX.proxy(this.canvas.scale, this.canvas));

		BX.bind(BX(this.id + 'scaleWidthPlus'), "mousedown", BX.proxy(function(){ this.increaseScale("width", true)}, this));
		BX.bind(BX(this.id + 'scaleWidthPlus'), "mouseup", BX.proxy(function(){ this.scaleChange("width", true)}, this));
		BX.bind(BX(this.id + 'scaleWidth'), "focus", BX.proxy(function(){ this.startTraceScale("width"); }, this));
		BX.bind(BX(this.id + 'scaleWidth'), "blur", BX.proxy(function(){ this.stopTraceScale("width"); }, this));
		BX.bind(BX(this.id + 'scaleWidthMinus'), "mousedown", BX.proxy(function(){ this.increaseScale("width", false)}, this));
		BX.bind(BX(this.id + 'scaleWidthMinus'), "mouseup", BX.proxy(function(){ this.scaleChange("width", false)}, this));

		BX.bind(BX(this.id + 'scaleHeightPlus'), "mousedown", BX.proxy(function(){ this.increaseScale("height", true)}, this));
		BX.bind(BX(this.id + 'scaleHeightPlus'), "mouseup", BX.proxy(function(){ this.scaleChange("height", true)}, this));
		BX.bind(BX(this.id + 'scaleHeight'), "focus", BX.proxy(function(){ this.startTraceScale("height"); }, this));
		BX.bind(BX(this.id + 'scaleHeight'), "blur", BX.proxy(function(){ this.stopTraceScale("height"); }, this));
		BX.bind(BX(this.id + 'scaleHeightMinus'), "mousedown", BX.proxy(function(){ this.increaseScale("height", false)}, this));
		BX.bind(BX(this.id + 'scaleHeightMinus'), "mouseup", BX.proxy(function(){ this.scaleChange("height", false)}, this));

		this.onPresetsApply();
		BX.bind(BX(this.id + 'presetsValues'), "change", BX.proxy(function()
			{
				this.preset.setActive(BX.proxy_context.value);
			}, this));
		BX.bind(BX(this.id + 'presetInsert'), "click", BX.proxy(function()
		{
			BX(this.id + 'scaleChained').checked = true;
			var preset = this.preset.getActive();

			if (preset)
			{
				this.canvas.scaleInit(BX(this.id + 'scaleIndicator'));
				this.canvas.cropInsert(preset, BX(this.id + 'crop'));
			}
		}, this));
		BX.bind(BX(this.id + 'presetEdit'), "click", BX.proxy(this.preset.edit, this.preset));
		BX.addCustomEvent(this.canvas, "onCropStart", BX.delegate(function(){ BX.removeClass(BX(this.id + 'presetSave'), "disabled"); }, this));
		BX.addCustomEvent(this.canvas, "onCropFinish", BX.delegate(function(){ BX.addClass(BX(this.id + 'presetSave'), "disabled"); }, this));

		BX.bind(BX(this.id + 'presetSave'), "click", BX.proxy(function() {
				if (this.canvas.busy === true && this.canvas.cropObj)
				{
					this.preset.edit(this.canvas.cropObj.cropParams);
				}
		}, this));

		BX.bind(BX(this.id + 'editorQueueUp'), "click", BX.proxy(this.up, this));
		BX.bind(BX(this.id + 'editorQueueDown'), "click", BX.proxy(this.down, this));
	},
	onPresetsApply : function() {
		var node = BX(this.id + 'presetsValues');
		if (node)
		{
			var presets = '', activePreset = this.preset.getActive(), ii;
			for (ii = 0; ii < this.preset.values.length; ii++)
			{
				presets += '<option value="' + ii + '" bx-width="' + this.preset.values[ii]['width'] + '" bx-height="' +
					this.preset.values[ii]['height'] + '"' + (ii == activePreset["id"] ? ' selected="selected"' : '') +
				'>' + preset.values[ii]['title'] + '(' + this.preset.values[ii]['width'] + 'x' + this.preset.values[ii]['height'] + ')</option>';
			}
			node.innerHTML = presets;
		}
	},
	onApply : function() {
		this.saveActiveItemChanges();
		this.popup['onApplyFlag'] = true;
		this.popup.close();
	},
	onCancel : function() {
		this.popup.close();
	},
	onClose : function() {
		this.finish((this.popup['onApplyFlag'] === true));

		BX.removeCustomEvent(this.popup, "onPopupShow", this.handlers.show);
		BX.removeCustomEvent(this.popup, "onAfterPopupShow", this.handlers.afterShow);
		BX.removeCustomEvent(this.popup, "onPopupClose", this.handlers.close);

		BX.onCustomEvent(this.canvas, "onPopupClose", []);

		this.popup.destroy();
		this.popup = null;
		this.slider = null;
	},
	bindTemplate : function() {
		this.canvas = new CanvasMaster(
			this.id + 'editorActive',
			{
				block : BX(this.id + 'editorActiveBlock'),
				canvas : BX(this.id + 'editorActiveImageCanvas'),
				canvasBlock : BX(this.id + 'editorActiveImageBlock')
			}
		);
		BX.addCustomEvent(this.canvas, "onChange", BX.delegate(this.saveActiveItem, this));
		BX.addCustomEvent(this.canvas, "onSetCanvas", BX.delegate(this.scaleChangeSize, this));
		BX.addCustomEvent(this.canvas, "onScaleCanvas", BX.delegate(this.scaleChangeSize, this));
		this.canvas.registerWheel(BX(this.id  + 'editorActiveBlock'));
		this.description = BX(this.id + 'editorDescription');
	},
	getTemplate : function() {
		var thumbs='';
		var iTemplate = [
			/*jshint multistr: true */
			'<div class="adm-photoeditor-preview-panel-container adm-photoeditor-preview-panel-container-wait" id="#id#EditorItem"> \
				<div class="adm-photoeditor-preview-panel-container-sub"> \
					<img src="', '/bitrix/images/1.gif', '" class="adm-photoeditor-preview-panel-container-space" /> \
					<span id="#id#EditorItemCanvas"></span> \
					<span id="#id#EditorItemDelete" class="adm-photoeditor-preview-panel-container-close">&nbsp;</span> \
				</div> \
			</div>'
		].join(''),
			item;
		this.items.reset();
		while ((item = this.items.getNext()) && item)
		{
			thumbs += iTemplate.replace(/#id#/gi, item.id);
		}
		return [
			/*jshint multistr: true */
			'<div class="adm-photoeditor-container"> \
			<div class="adm-photoeditor-buttons-panel"> \
			<div class="adm-photoeditor-buttons-panel-save disabled" id="', this.id, 'presetSave', '"><span>', BX.message("JS_CORE_FI_SAVE_PRESET"),'</span></div> \
			<div class="adm-photoeditor-buttons-panel-cancel disabled" id="', this.id, 'cancel', '"><span>', BX.message("JS_CORE_FI_CANCEL_PRESET"),'</span></div> \
			<div class="adm-photoeditor-btn-wrap"> \
				<span class="adm-photoeditor-btn adm-photoeditor-btn-turn-l" id="', this.id, 'turn-l', '" title="', BX.message("CANVAS_TURN_L"),'"> \
					<span class="adm-photoeditor-btn-icon"></span> \
				</span> \
				<span class="adm-photoeditor-btn adm-photoeditor-btn-turn-r" id="', this.id, 'turn-r', '"title="', BX.message("CANVAS_TURN_R"),'"> \
					<span class="adm-photoeditor-btn-icon"></span> \
				</span> \
				<span class="adm-photoeditor-btn adm-photoeditor-btn-flip-v" id="', this.id, 'flip-v', '" title="', BX.message("CANVAS_FLIP_V"),'"> \
					<span class="adm-photoeditor-btn-icon"></span> \
				</span> \
				<span class="adm-photoeditor-btn adm-photoeditor-btn-flip-h" id="', this.id, 'flip-h', '" title="', BX.message("CANVAS_FLIP_H"),'"> \
					<span class="adm-photoeditor-btn-icon"></span> \
				</span> \
				<span class="adm-photoeditor-btn adm-photoeditor-btn-crop" id="', this.id, 'crop', '" title="', BX.message("CANVAS_CROP"),'"> \
					<span class="adm-photoeditor-btn-icon"></span> \
				</span> \
				<span class="adm-photoeditor-btn adm-photoeditor-btn-grayscale" id="', this.id, 'grayscale', '" title="', BX.message("CANVAS_GRAYSCALE"),'"> \
					<span class="adm-photoeditor-btn-icon"></span> \
				</span> \
				<span class="adm-photoeditor-btn adm-photoeditor-btn-sign" id="', this.id, 'sign', '" title="', BX.message("CANVAS_SIGN"),'"> \
					<span class="adm-photoeditor-btn-icon"></span> \
				</span> \
			</div> \
			<div id="', this.id, 'cropPresets"> \
				<div class="adm-photoeditor-buttons-panel-cropping"> \
					<span class="crop">', BX.message("JS_CORE_FI_FRAMING"), '</span> \
					<span class="adm-select-wrap"> \
						<select class="adm-select" id="', this.id, 'presetsValues"></select> \
						</span> \
				</div> \
				<div class="adm-photoeditor-btn-wrap"> \
					<span class="adm-photoeditor-btn adm-photoeditor-btn-cut" id="', this.id, 'presetInsert" title="', BX.message("JS_CORE_FI_USE_PRESET"),'"> \
						<span class="adm-photoeditor-btn-icon"></span> \
					</span> \
					<span class="adm-photoeditor-btn adm-photoeditor-btn-edit" id="', this.id, 'presetEdit" title="', BX.message("JS_CORE_FI_EDIT_PRESET"),'"> \
						<span class="adm-photoeditor-btn-icon"></span> \
					</span> \
				</div> \
				<span class="adm-photoeditor-btn adm-photoeditor-btn-ph disabled" id="', this.id, 'MapCollapsed"> \
					<span class="adm-photoeditor-btn-icon"></span> \
				</span> \
			</div> \
		</div> \
		<div class="adm-photoeditor-sidebar"> \
			<div class="adm-photoeditor-sidebar-options"> \
				<span class="adm-photoeditor-sidebar-options-title">', BX.message("JS_CORE_FI_WIDTH"), '</span> \
				<span class="adm-photoeditor-plus" id="', this.id , 'scaleWidthPlus">&nbsp;</span> \
				<input type="number" value="0" id="', this.id , 'scaleWidth" /> \
				<span class="adm-photoeditor-minus" id="', this.id , 'scaleWidthMinus">&nbsp;</span> \
			</div> \
			<div class="adm-photoeditor-sidebar-options"> \
				<div class="sidebar-options-checkbox-container"> \
					<input type="checkbox" value="Y" id="', this.id , 'scaleChained" checked /> \
					<label for="', this.id , 'scaleChained"><span class="label-icon"></span></label> \
				</div> \
			</div> \
			<div class="adm-photoeditor-sidebar-options"> \
				<span class="adm-photoeditor-sidebar-options-title">', BX.message("JS_CORE_FI_HEIGHT"), '</span> \
				<span class="adm-photoeditor-plus" id="', this.id , 'scaleHeightPlus">&nbsp;</span> \
				<input type="number" value="0" id="', this.id , 'scaleHeight" /> \
				<span class="adm-photoeditor-minus" id="', this.id , 'scaleHeightMinus">&nbsp;</span> \
			</div> \
			<div class="adm-photoeditor-sidebar-scale"> \
				<span class="adm-photoeditor-sidebar-scale-value" style="top: 0">+100%</span> \
				<span class="adm-photoeditor-sidebar-scale-value" style="top: 25%">+50%</span> \
				<span class="adm-photoeditor-sidebar-scale-value" style="top: 50%">0%</span> \
				<span class="adm-photoeditor-sidebar-scale-value" style="top: 75%">-50%</span> \
				<span class="adm-photoeditor-sidebar-scale-value" style="top: 100%">-100%</span> \
				<div class="adm-photoeditor-scale-indicator" style="top: 50%" id="', this.id, 'scaleIndicator">0%</div> \
			</div> \
		</div> \
		<div class="adm-photoeditor"> \
			<div class="adm-photoeditor-preview-panel"> \
				<span class="adm-photoeditor-preview-panel-arrow-top" id="', this.id, 'editorQueueUp"></span> \
				<div class="adm-photoeditor-preview-panel-previews" id="', this.id, 'editorQueue"> \
					<div class="adm-photoeditor-preview-panel-previews-inner" id="', this.id, 'editorQueueInner">', thumbs, '</div> \
				</div> \
				<span class="adm-photoeditor-preview-panel-arrow-bottom" id="', this.id, 'editorQueueDown"></span> \
			</div> \
			<div class="adm-photoeditor-active-block-outer"> \
				<div class="adm-photoeditor-active-block" id="', this.id, 'editorActiveBlock"> \
					<div class="adm-photoeditor-active-image-block" id="', this.id, 'editorActiveImageBlockOuter"> \
						<div class="adm-photoeditor-active-image" id="', this.id, 'editorActiveImageBlock"> \
							<div class="adm-photoeditor-crop" id="', this.id, 'editorActiveCrop"></div> \
							<canvas id="', this.id, 'editorActiveImageCanvas"></canvas> \
						</div> \
						<div class="adm-photoeditor-active-cursor"></div> \
						<div class="adm-photoeditor-active-move-cursor"></div> \
					</div> \
				</div> \
				<div class="adm-photoeditor-desc"><input type="text" id="', this.id, 'editorDescription" placeholder="', BX.message("JS_CORE_FILE_DESCRIPTION"),'"></div> \
			</div> \
		</div> \
	</div>'].join('').replace(/[\n\t]/gi, '').replace(/>\s</gi, '><');
	},
	showEditor : function() {
		if (!this.popup || this.popup === null)
		{
			var editorNode = BX.create("DIV", {
				attrs : {
					id : this.id + 'Proper',
					className : "bxu-edit-popup"
				},
				style : { display : "none" },
				html : this.getTemplate()
			});
			this.popup = BX.PopupWindowManager.create(
				'popup' + this.id,
				null,
				{
					className : "bxu-popup" + (this.params["description"] !== false ? "" : " bxu-popup-nondescription"),
					autoHide : false,
					lightShadow : true,
					closeIcon : false,
					closeByEsc : true,
					zIndex : getZIndex(1),
					content : editorNode,
					overlay : {},
					events : {
						onPopupShow : this.handlers.show,
						onAfterPopupShow : this.handlers.afterShow,
						onPopupClose : this.handlers.close
					},
					buttons : [
						new BX.PopupWindowButton( {text : BX.message('JS_CORE_WINDOW_SAVE'), className : "popup-window-button-accept", events : { click : this.handlers.apply } } ),
						new BX.PopupWindowButtonLink( {text : BX.message('JS_CORE_WINDOW_CANCEL'), className : "popup-window-button-link-cancel", events : { click : this.handlers.cancel } } )
					]
				}
			);
		}
		this.popup.show();
		this.popup.adjustPosition();
	},
	slider : null,
	initSliderParams : function() {
		if (this.slider == null)
		{
			this.slider = {
				top : 0,
				step : 30,
				outer : BX(this.id + 'editorQueue'),
				outerPos : BX.pos(BX(this.id + 'editorQueue')),
				inner : BX(this.id + 'editorQueueInner'),
				innerPos : BX.pos(BX(this.id + 'editorQueueInner'))
			};
			this.slider.maxHeight = Math.min((this.slider.outerPos.height - this.slider.innerPos.height), 0);
		}
		return this.slider;
	},
	up : function(e) {
		if (this.initSliderParams())
		{
			this.slider.top = Math.min((this.slider.top + this.slider.step), 0);
			this.slider.inner.style.top = this.slider.top + 'px';
		}
		return BX.PreventDefault(e);
	},
	down : function(e) {
		if (this.initSliderParams())
		{
			this.slider.top = Math.max(this.slider.maxHeight, (this.slider.top - this.slider.step));
			this.slider.inner.style.top = this.slider.top + 'px';
		}
		return BX.PreventDefault(e);
	},
	lastProportion : {
		width : 0,
		height : 0,
		"~width" : 0,
		"~height" : 0,
		timeout : null
	},
	increaseScale : function(name, direction) {
		if (this.increaseScaleTimeout > 0)
			clearTimeout(this.increaseScaleTimeout);

		name = (name == "width" ? "width" : "height");
		var node = (name == "width" ? BX(this.id + 'scaleWidth') : BX(this.id + 'scaleHeight')),
			_this = this,
			f = function() {
			if (direction === false)
			{
				node.value = (++_this.lastProportion[name]);
			}
			else if (direction === true)
			{
				node.value = Math.max((--_this.lastProportion[name]), 1);
			}

			_this.lastProportion[name]= parseInt(node.value);
			_this.increaseScaleTimeoutCounter++;
			_this.increaseScaleTimeout = setTimeout(f, (150 - Math.min(100, _this.increaseScaleTimeoutCounter*_this.increaseScaleTimeoutCounter)));
		};
		this.increaseScaleTimeoutCounter = 0;
		this.increaseScaleTimeout = setTimeout(f, 50);
	},
	startTraceScale : function(name) {
		if (this.traceScaleTimeout > 0)
			clearTimeout(this.traceScaleTimeout);
		this.traceScaleTimeout = (this.traceScaleTimeout > 0 ? this.traceScaleTimeout : 0);

		name = (name == "width" ? "width" : "height");

		var node = (name == "width" ? BX(this.id + 'scaleWidth') : BX(this.id + 'scaleHeight'));
		if (this.lastProportion[name] === parseInt(node.value))
		{
			this.traceScaleTimeout++;
			if (this.traceScaleTimeout > 5)
			{
				this.traceScaleTimeout = 0;
				this.scaleAdjust(name, null);
			}
		}
		else
		{
			this.traceScaleTimeout = 0;
			this.lastProportion[name] = parseInt(node.value);
		}
		this.traceScaleTimeout = setTimeout(BX.proxy(function(){ this.startTraceScale(name); }, this), 500);
	},
	stopTraceScale : function(name) {
		if (this.traceScaleTimeout > 0)
			clearTimeout(this.traceScaleTimeout);
		this.traceScaleTimeout = 0;
		this.scaleAdjust(name, null);
	},
	scaleChange : function(name, direction) {
		if (this.increaseScaleTimeout > 0)
			clearTimeout(this.increaseScaleTimeout);
		if (this.traceScaleTimeout > 0)
			clearTimeout(this.traceScaleTimeout);
		if (this.lastProportion.timeout === null)
			this.lastProportion.timeout = setTimeout(BX.proxy(this.scaleAdjust, this), 500);
	},
	scaleAdjust : function(width) {
		this.lastProportion.timeout = null;
		if (width > 0)
		{
			this.lastProportion["~width"] = this.lastProportion.width = width;
			this.canvas.scaleWidth(this.lastProportion.width, true, BX(this.id + 'scaleIndicator'));
		}
		else
		{
			if (this.lastProportion["~width"] != this.lastProportion.width)
			{
				this.lastProportion["~width"] = this.lastProportion.width;
				this.canvas.scaleWidth(this.lastProportion.width, this.scaleIsChained(), BX(this.id + 'scaleIndicator'));
			}
			if (this.lastProportion["~height"] != this.lastProportion.height)
			{
				this.lastProportion["~height"] = this.lastProportion.height;
				this.canvas.scaleHeight(this.lastProportion.height, this.scaleIsChained(), BX(this.id + 'scaleIndicator'));
			}
		}
	},
	scaleIsChained : function() {
		return (!!BX(this.id + 'scaleChained').checked);
	},
	scaleChained : function() {
		BX(this.id + 'scaleWidth').value = this.lastProportion.width;
	},
	scaleChangeSize : function(canvas) {
		this.lastProportion["~width"] =
			this.lastProportion.width =
				BX(this.id + 'scaleWidth').value = (canvas.width || 0);
		this.lastProportion["~height"] =
			this.lastProportion.height =
				BX(this.id + 'scaleHeight').value = (canvas.height || 0);
	}
};
var CanvasStack = function(depth)
{
	this.depth = depth;
};
CanvasStack.prototype = {
	id : 'CanvasStack',
	depth : 3,
	stack : [],
	number : 0,
	init : function()
	{
		var canvas;
		while ((canvas = this.stack.shift()) && canvas)
		{
			BX.remove(canvas);
			canvas = null;
		}
		this.stack = [];
		BX.onCustomEvent(this, "onChange", [this.stack.length, this.stack]);
	},
	add : function(canvas)
	{
		this.number++;
		var res = BX.create("CANVAS", {
			attrs : { id : this.id +  this.number},
			props : { width : canvas.width, height : canvas.height },
			style : { display : "none" }
		});
		res.getContext("2d").drawImage(canvas, 0, 0);
		this.stack.push(res);
		while (this.stack.length > this.depth)
		{
			res = this.stack.shift();
			BX.remove(res);
			res = null;
		}
		BX.onCustomEvent(this, "onChange", [this.stack.length, this.stack]);
	},
	restore : function()
	{
		var res = this.stack.pop();
		if (res)
		{
			BX.onCustomEvent(this, "onRestore", [res]);
			BX.remove(res);
			BX.onCustomEvent(this, "onChange", [this.stack.length, this.stack]);
		}
	}
};
var CanvasMaster = function(id, nodes) {
	this.block = nodes.block;
	this.block.pos = BX.pos(this.block);
	this.canvas = nodes.canvas;
	this.ctx = this.canvas.getContext("2d");

	this.canvasBlock = nodes.canvasBlock;
	var pos = BX.UploaderUtils.scaleImage(this.block.pos, {width : this.mapSize, height : this.mapSize});
	this.canvasMap = new CanvasMapMaster(
		nodes.block,
		{
			width : pos.destin.width,
			height : pos.destin.height,
			scale : pos.coeff
		}
	);
	BX.addCustomEvent(this.canvasMap, "onMapPointerIsMoved", BX.delegate(this.move, this));
	BX.addCustomEvent(this.canvasMap, "onMapIsInitialised", BX.delegate(function(){ this.registerWheel(this.canvasMap.root); }, this));

	this.id = id;
	BX.bind(window, "resize", BX.proxy(this.onResizeWindow, this));
	this.stack = new CanvasStack(5);
	BX.addCustomEvent(this.stack, "onChange", BX.delegate(this.changeCancelNode, this));
	BX.addCustomEvent(this.stack, "onRestore", BX.delegate(this.restore, this));
	BX.addCustomEvent(this, "onActiveItemIsChanged", BX.delegate(this.stack.init, this.stack));
	BX.addCustomEvent(this, "onPopupClose", BX.delegate(function(){
		if(this.cropObj) { this.cropObj.finish(); }
		this.canvasMap.hide(false);
	}, this));
};
CanvasMaster.prototype = {
	registerWheel : function(node)
	{
		BX.bind(node, this.onWheelEvent, BX.delegate(this.onWheel, this));
		if (this.onWheelEvent == "DOMMouseScroll")
			BX.bind(node, "MozMousePixelScroll", BX.delegate(this.onWheel, this));
	},
	onWheelEvent : ("onwheel" in document.createElement("div") ? "wheel" : // Modern browsers support "wheel"
		document['onmousewheel'] !== undefined ? "mousewheel" : // Webkit and IE support at least "mousewheel"
		"DOMMouseScroll"// let's assume that remaining browsers are older Firefox
	),
	onWheelMaxSpeed : 50,
	onWheelLastEvent : 0,
	onWheel : function(e)
	{
		// calculate deltaY (and deltaX) according to the event
		var delta, time = (new Date()).getTime();
		if ((time - this.onWheelLastEvent) < this.onWheelMaxSpeed)
			return BX.PreventDefault(e);
		this.onWheelLastEvent = time;

		if (e['deltaY'])
		{
			delta = e['deltaY'];
		}
		else if ( this.onWheelEvent == "mousewheel" )
		{
			delta = - 1/40 * e.wheelDelta;
		}
		else
		{
			delta = e.detail;
		}
		if (delta != undefined)
		{
			this.zoom(delta * (-1), e);
		}

		return BX.PreventDefault(e);
	},
	startScale : 1,
	mapSize : 300,
	onResizeWindow : function()
	{
	},
	setHack : {width : 0, height : 0},
	set : function(canvas, props)
	{
		var settings = (props || {props : { width : canvas.width , height : canvas.height } }),
			pos = this.block.pos,
			k, kReal,
			style;

		if ((this.setHack.width + '') == (settings.props.width + '') &&
			(this.setHack.height + '') == (settings.props.height + ''))
		{
			this.setHack.width--;
			BX.adjust(this.canvas, { props : this.setHack });
		}

		this.setHack.width = settings.props.width;
		this.setHack.height = settings.props.height;

		BX.adjust(this.canvas, settings);

		kReal = k = Math.min(
			( this.canvas.width > 0 ? pos["width"] / this.canvas.width : 1 ),
			( this.canvas.height > 0 ? pos["height"] / this.canvas.height : 1 )
		);
		k = (0 < k && k < 1 ? k : 1);
		style = {
			top : Math.ceil((pos["height"] - this.canvas.height * k) / 2),
			left : Math.ceil((pos["width"] - this.canvas.width * k) / 2),
			width : this.canvas.width,
			height : this.canvas.height,
			transform : 'translate3d(0, 0, 0) scale(' + k + ', ' + k + ')'
		};
		this.zoomCounter = 0;
		this.canvas.startScale = k;
		this.canvas.maxVisibleScale = kReal;
		this.canvas.scale = k;
		this.canvas.pos = {
			absoluteLeft : style.left + pos.left,
			absoluteTop : style.top + pos.top,
			absoluteWidth : pos.width,
			absoluteHeight : pos.height,
			startedWidth : Math.ceil(this.canvas.width * k),
			startedHeight : Math.ceil(this.canvas.height * k),
			left : style.left,
			top : style.top,
			shiftX : 0, // ceil
			'~shiftX' : 0, // float
			shiftY : 0, // ceil
			'~shiftY' : 0 // float
		};

		this.canvas.visiblePart = {
			left : 0,
			'~left' : 0,
			top : 0,
			'~top' : 0,
			"~width" : canvas.width,
			width : canvas.width,
			"~height" : canvas.height,
			height : canvas.height,
			leftGap : this.canvas.pos.left,
			topGap : this.canvas.pos.top,
			rightGap : this.canvas.pos.absoluteWidth - this.canvas.pos.startedWidth - this.canvas.pos.left,
			bottomGap : this.canvas.pos.absoluteHeight - this.canvas.pos.startedHeight - this.canvas.pos.top
		};

		for (var ii in style)
		{
			if (style.hasOwnProperty(ii))
			{
				if (style[ii] > 0)
				{
					style[ii] = style[ii] + 'px';
				}
			}
		}
		BX.adjust(
			BX(this.canvasBlock), {
				style : style
			}
		);

		this.ctx.drawImage(canvas,
			0, 0, canvas.width, canvas.height,
			0, 0, this.canvas.width, this.canvas.height
		);
		this.canvasMap.init(this.canvas, (props ? props.props : false));
		BX.onCustomEvent(this, "onSetCanvas", [this.canvas, canvas]);
		this.changed = false;
	},
	setMapCollapsed : function(node)
	{
		if (this.canvasMap)
			this.canvasMap.registerCollapsedNode(node);
	},
	setCancelNode : function(node)
	{
		this.cancelNode = BX(node);
		if (!BX.hasClass(this.cancelNode, "disabled"))
			BX.addClass(this.cancelNode, "disabled");
		BX.bind(this.cancelNode, "click", BX.delegate(this.stack.restore, this.stack));
	},
	changeCancelNode : function(available)
	{
		if (available)
			BX.removeClass(this.cancelNode, "disabled");
		else if (!BX.hasClass(this.cancelNode, "disabled"))
			BX.addClass(this.cancelNode, "disabled");
	},
	restore : function(canvas)
	{
		this.set(canvas, { props : { width : canvas.width, height : canvas.height } } );
		BX.onCustomEvent(this, "onCropHasToBeHidden", []);
	},
	cursor : {
		x : 0,
		y : 0
	},
	zoomEdge : 2,
	zoomCounter : 0,
	busy : false,
	zoom : function(zoomIn, e)
	{
		if (this.busy !== false)
			return;

		var delta = 0;

		if (zoomIn > 0 && this.zoomCounter < this.zoomEdge)
			delta = 0.1;
		else if (zoomIn < 0 && this.zoomCounter > 0)
			delta = -0.1;

		if (delta !== 0)
		{
			this.zoomCounter += delta;
			var newScale = this.canvas.startScale + this.zoomCounter;
			if (e)
			{
				BX.fixEventPageXY(e);
				this.cursor = { // Cursor position above the picture
					x : Math.max(0, Math.min((e.pageX - this.canvas.pos.absoluteLeft), this.canvas.pos.absoluteWidth)),
					y : Math.max(0, Math.min((e.pageY - this.canvas.pos.absoluteTop), this.canvas.pos.absoluteHeight))
				};
			}
			this.zoomProcess(newScale);
		}
	},
	zoomProcess : function(newScale)
	{
		this.zoomCanvas(newScale);
		if (this.canvas.scale > this.canvas.maxVisibleScale)
		{
			this.canvasMap.show();
			this.canvasMap.zoom(this.canvas.visiblePart);
		}
		else
		{
			this.canvasMap.zoom(this.canvas.visiblePart);
			this.canvasMap.hide(true);
		}
	},
	zoomCanvas : function(newScale, e)
	{
		if (e === true || newScale <= this.canvas.startScale)
		{
			this.canvas.visiblePart = {
				left : 0,
				'~left' : 0,
				top : 0,
				'~top' : 0,
				"~width" : this.canvas.width,
				width : this.canvas.width,
				"~height" : this.canvas.height,
				height : this.canvas.height,
				leftGap : this.canvas.pos.left,
				topGap : this.canvas.pos.top,
				rightGap : this.canvas.pos.absoluteWidth - this.canvas.pos.startedWidth - this.canvas.pos.left,
				bottomGap : this.canvas.pos.absoluteHeight - this.canvas.pos.startedHeight - this.canvas.pos.top
			};

			this.canvas.pos['~shiftX'] = 0;
			this.canvas.pos['~shiftY'] = 0;
			this.canvas.pos.shiftX = 0;
			this.canvas.pos.shiftY = 0;
			this.canvas.scale = this.canvas.startScale;
		}
		else
		{
			var
				x = this.cursor.x,
				y = this.cursor.y,

				left = (((-1) * (this.canvas.pos['~shiftX'] || 0) + x) / this.canvas.scale),
				top = (((-1) * (this.canvas.pos['~shiftY'] || 0) + y) / this.canvas.scale),

				newLeft = left * newScale,
				newTop = top * newScale,

				xShift = Math.max(newLeft - x, 0),
				yShift = Math.max(newTop - y, 0);

			this.formVisiblePart(newScale, xShift, yShift);

			xShift *= (-1);
			yShift *= (-1);

			this.canvas.pos["~shiftX"] = xShift;
			this.canvas.pos["~shiftY"] = yShift;

			this.canvas.pos.shiftX = Math.ceil(xShift);
			this.canvas.pos.shiftY = Math.ceil(yShift);
			this.canvas.scale = newScale;
		}

		this.canvasBlock.style.transform =
			'translate3d(' + this.canvas.pos.shiftX + 'px, ' + this.canvas.pos.shiftY + 'px, 0) ' +
			'scale(' + this.canvas.scale + ', ' + this.canvas.scale + ')';
		BX.onCustomEvent(this, "onCanvasPositionHasChanged", []);
	},
	formVisiblePart : function(newScale, xShift, yShift)
	{
		var
			visibleLeft = (xShift - this.canvas.pos.left),
			visibleTop = (yShift - this.canvas.pos.top);

		this.canvas.visiblePart["~left"] = (visibleLeft > 0 ? visibleLeft : 0) / newScale;
		this.canvas.visiblePart["left"] = Math.ceil(this.canvas.visiblePart["~left"]);
		this.canvas.visiblePart["~top"] = (visibleTop > 0 ? visibleTop : 0) / newScale;
		this.canvas.visiblePart["top"] = Math.ceil(this.canvas.visiblePart["~top"]);

		var
			newWidth = this.canvas.width * newScale,
			leftGap = (-1) * visibleLeft,
			rightGap = this.canvas.pos.absoluteWidth - (newWidth + leftGap);

		if (this.canvas.pos.absoluteWidth > newWidth)
		{
			if (leftGap < 0)
				newWidth += leftGap;
			else if (rightGap < 0)
				newWidth += rightGap;
		}
		else
		{
			newWidth = this.canvas.pos.absoluteWidth;
			if (leftGap > 0)
				newWidth -= leftGap;
			else if (rightGap > 0)
				newWidth -= rightGap;
		}
		this.canvas.visiblePart["leftGap"] = leftGap;
		this.canvas.visiblePart["rightGap"] = rightGap;

		this.canvas.visiblePart["~etalonWidth"] = (this.canvas.pos.absoluteWidth / newScale);
		this.canvas.visiblePart["~width"] = (newWidth / newScale);
		this.canvas.visiblePart["width"] = Math.ceil(this.canvas.visiblePart["~width"]);

		var
			newHeight = this.canvas.height * newScale,
			topGap = (-1) * visibleTop,
			bottomGap = this.canvas.pos.absoluteHeight - (newHeight + topGap);

		if (this.canvas.pos.absoluteHeight > newHeight)
		{
			if (topGap < 0)
				newHeight += topGap;
			else if (bottomGap < 0)
				newHeight += bottomGap;
		}
		else
		{
			newHeight = this.canvas.pos.absoluteHeight;
			if (topGap > 0)
				newHeight -= topGap;
			else if (bottomGap > 0)
				newHeight -= bottomGap;
		}

		this.canvas.visiblePart["topGap"] = topGap;
		this.canvas.visiblePart["bottomGap"] = bottomGap;

		this.canvas.visiblePart["~etalonHeight"] = (this.canvas.pos.absoluteHeight / newScale);
		this.canvas.visiblePart["~height"] = (newHeight / newScale);
		this.canvas.visiblePart["height"] = Math.ceil(this.canvas.visiblePart["~height"]);
	},
	move : function(pos)
	{
		var
			width = Math.round(this.canvas.width * this.canvas.scale),
			height = Math.round(this.canvas.height * this.canvas.scale),
			xShift = (-1) * Math.ceil(pos['left'] * this.canvas.scale),
			yShift = (-1) * Math.ceil(pos['top'] * this.canvas.scale);

		if (pos.right === true || (width + xShift) < this.canvas.pos.absoluteWidth)
			xShift = this.canvas.pos.absoluteWidth - width;
		if (pos.bottom === true || (height + yShift) < this.canvas.pos.absoluteHeight)
			yShift = this.canvas.pos.absoluteHeight - height;

		xShift -=  this.canvas.pos.left;
		yShift -=  this.canvas.pos.top;

		this.formVisiblePart(this.canvas.scale, (-1) * xShift, (-1) * yShift);


		this.canvas.pos['~shiftX'] = xShift;
		this.canvas.pos['~shiftY'] = yShift;
		this.canvas.pos.shiftX = xShift;
		this.canvas.pos.shiftY = yShift;

		this.canvasBlock.style.transform =
			'translate3d(' + this.canvas.pos.shiftX + 'px, ' + this.canvas.pos.shiftY + 'px, 0) ' +
			'scale(' + this.canvas.scale + ', ' + this.canvas.scale + ')';
		BX.onCustomEvent(this, "onCanvasPositionHasChanged", []);
	},
	canvasesID : "canvasForEdit",
	copyCanvas : null,
	workCanvas : null,
	getCopyCanvas : function()
	{
		if (this.copyCanvas === null)
		{
			this.copyCanvas = BX.create("CANVAS", { attrs : { id : this.id + 'Copy' }, style : { display : "none" }});
		}
		BX.adjust(this.copyCanvas, { props : { width : this.canvas.width, height : this.canvas.height } });
		this.copyCanvas.getContext("2d").drawImage(this.canvas, 0, 0);
		return this.copyCanvas;
	},
	getCanvas : function()
	{
		return this.canvas;
	},
	getWorkCanvas : function(draw)
	{
		if (this.workCanvas === null)
		{
			this.workCanvas = BX.create("CANVAS", { attrs : { id : this.id + 'Work' }, style : { display : "none" }});
		}
		BX.adjust(this.workCanvas, { props : { width : this.canvas.width, height : this.canvas.height } });
		if (draw)
			this.workCanvas.getContext("2d").drawImage(this.canvas, 0, 0);
		return this.workCanvas;
	},
	rotate : function(clockwise)
	{
		if (this.busy === true)
			return;
		var rad = Math.PI / 2 * (clockwise ? 1 : -1),
			tmpCanvas1 = this.getCopyCanvas(),
			tmpCanvas2 = this.getWorkCanvas(false),
			w = this.canvas.height,
			h = this.canvas.width;

		BX.adjust(tmpCanvas2, { props : { width : w, height : h } });

		var ctx = tmpCanvas2.getContext("2d");

		ctx.save();

		if (clockwise)
			ctx.translate(tmpCanvas1.height, 0);
		else
			ctx.translate(0, tmpCanvas1.width);

		ctx.rotate(rad);
		ctx.drawImage(tmpCanvas1, 0, 0);
		ctx.restore();

		this.set(tmpCanvas2, { props : { width : w, height : h } } );
		this.stack.add(tmpCanvas1);
		this.changed = true;
	},
	poster : function(node)
	{
		if (this.busy === true)
			return;
		if (!!this.posterPopup)
			this.posterPopup.close();
		var res = BX.pos(node);

		this.posterPopup = new BX.PopupWindow('bx-poster-popup-' + node.id, node, {
			lightShadow : true,
			offsetTop: -3,
			className : "bxu-poster-popup",
			offsetLeft: Math.ceil(res.width / 2),
			autoHide: true,
			closeByEsc: true,
			zIndex : BX.PopupWindow.getOption("popupOverlayZindex") + getZIndex(2),
			bindOptions: {position: "top"},
			overlay : false,
			events : {
				onPopupClose : function() { this.destroy() },
				onPopupDestroy : BX.proxy(function() { this.posterPopup = null; }, this)
			},
			buttons : [
				new BX.PopupWindowButton( {text : BX.message('CANVAS_OK'), events : { click : BX.delegate(function() {
					var msg = BX('posterPopupText' + node.id);
					if (!!msg && msg.value.length > 0)
						this.posterApply(msg.value);
					this.posterPopup.close();
				}, this) } } ),
				new BX.PopupWindowButtonLink( {text : BX.message('CANVAS_CANCEL'), events : { click : BX.delegate(function(){this.posterPopup.close();}, this) } } )
			],

			content : [/*jshint multistr: true */'<div class="bxu-poster-popup-dt">', BX.message("CANVAS_POSTER_SIGN"), '</div> \
				<input type="text" id="posterPopupText', node.id,'" maxlength="255" value="" />'].join("")
		});
		this.posterPopup.show();
		this.posterPopup.setAngle({position:'bottom'});
		this.posterPopup.bindOptions.forceBindPosition = true;
		this.posterPopup.adjustPosition();
		BX.focus(BX('posterPopupText' + node.id));
		this.posterPopup.bindOptions.forceBindPosition = false;
	},
	posterApply : function(msg)
	{
		if (msg)
		{
			this.stack.add(this.getCopyCanvas());
			var cnv = this.getWorkCanvas(true),
				ctx = cnv.getContext("2d"),
				size = Math.min(cnv.width, cnv.height) / 10;
			ctx.fillStyle = "black";
			ctx.fillRect(0, 0, cnv.width, size);
			ctx.fillRect(0, cnv.height - 2 * size, cnv.width, 2 * size);
			ctx.fillRect(0, 0, size, cnv.height);
			ctx.fillRect(cnv.width - size, 0, size, cnv.height);
			ctx.strokeStyle = "white";
			var border = 5;
			ctx.strokeRect(size - border, size - border,
				cnv.width - (size * 2) + 2 * border,
				cnv.height - (size * 3) + 2 * border);
			ctx.fillStyle = "white";
			ctx.textAlign = "center";
			ctx.textBaseline = "middle";
			ctx.font = size + "px marketing";
			ctx.fillText(msg, cnv.width / 2, cnv.height - size, cnv.width);
			this.set(cnv, false);
			this.changed = true;
		}
	},
	blackAndWhite : function()
	{
		if (this.busy === true)
			return;
		this.stack.add(this.getCopyCanvas());

		var cnv = this.getWorkCanvas(true),
			ctx = cnv.getContext("2d"),
			frame = ctx.getImageData(0, 0, cnv.width, cnv.height),
			v, i;

		for (i = 0; i < frame.data.length; i += 4)
		{
			v = (frame.data[i] + frame.data[i + 1] + frame.data[i + 2]) / 3;
			frame.data[i] = v;
			frame.data[i + 1] = v;
			frame.data[i + 2] = v;
		}
		ctx.putImageData(frame, 0, 0);
		this.set(cnv, false);
		this.changed = true;
	},
	flip : function(verticaly)
	{
		if (this.busy === true)
			return;
		this.stack.add(this.getCopyCanvas());
		var cnv = this.getWorkCanvas(true),
			ctx = cnv.getContext("2d");
		ctx.save();
		if (verticaly)
		{
			ctx.scale(1, -1);
			ctx.translate(0, -cnv.height);
		}
		else
		{
			ctx.scale(-1, 1);
			ctx.translate(-cnv.width, 0);
		}
		ctx.drawImage(cnv, 0, 0);
		ctx.restore();
		this.set(cnv, false);
		this.changed = true;
	},
	cropObj : null,
	cropStatus : false,
	cropInit : function(button)
	{
		if (this.cropObj === null)
		{
			this.cropObj = new CanvasCrop(
				this.id,
				this.canvas,
				BX(this.id + 'Crop')
			);
			this.cropObj.cropParams.topShift = parseInt(this.canvasBlock.style.top ? this.canvasBlock.style.top.replace(/[a-z]+$/, '') : 0);
			this.cropObj.cropParams.leftShift = parseInt(this.canvasBlock.style.left ? this.canvasBlock.style.left.replace(/[a-z]+$/, '') : 0);

			BX.addCustomEvent(this, "onCropHasToBeHidden", BX.delegate(this.cropObj.finish, this.cropObj));

			BX.addCustomEvent(this.cropObj, "onCropApply", BX.delegate(this.cropApply, this));
			BX.addCustomEvent(this.cropObj, "onCropTooBig", BX.delegate(this.onCropTooBig, this));
			BX.addCustomEvent(this.cropObj, "onCropStart", BX.delegate(function(){
				BX.addClass(button, "bxu-edit-btn-active");
				this.cropStatus = true;
				this.busy = true;
				this.canvasMap.occupy();
				BX.onCustomEvent(this, "onCropStart", [this.cropObj, this.cropObj.cropParams]);
			}, this));
			BX.addCustomEvent(this.cropObj, "onCropFinish", BX.delegate(function(){
				BX.removeClass(button, "bxu-edit-btn-active");
				this.busy = false;
				this.cropStatus = false;
				this.canvasMap.release();
				BX.onCustomEvent(this, "onCropFinish", [this.cropObj, this.cropObj.cropParams]);
			}, this));
			BX.addCustomEvent(this, "onCanvasPositionHasChanged", BX.delegate(this.cropObj.scale, this.cropObj));
		}
		return true;
	},
	crop : function(button)
	{
		if (this.cropInit(button))
			this.cropObj.turn();
	},
	onCropTooBigPopup : null,
	onCropTooBig : function(width, heigth)
	{
		if (this.onCropTooBigPopup === null)
		{
			this.onCropTooBigPopupApply = BX.delegate(function() {
				var res = BX.UploaderUtils.scaleImage(
					this.canvas,
					this.onCropTooBigPopup.presetParams,
					"circumscribed"
				);
				if (res.bNeedCreatePicture)
				{
					this.scaleZoom = false;
					this.scaleWidth(res.destin.width, true, BX(this.id + 'scaleIndicator'));
					this.scaleZoom = true;
				}
				this.cropInsert(this.onCropTooBigPopup.presetParams);
				this.onCropTooBigPopup.close();
			}, this);

			this.onCropTooBigPopupCancel = BX.delegate(function() {
				this.onCropTooBigPopup.close();
			}, this);

			this.onCropTooBigPopup = new BX.PopupWindow(
				'popupCropTooBigPopup' + this.id,
				null,
				{
					lightShadow : true,
					autoHide: true,
					closeByEsc: true,
					zIndex : getZIndex(2),
					overlay : {},
					buttons : [
						new BX.PopupWindowButton( {text : BX.message("JS_CORE_FI_SCALE"), className : "popup-window-button-accept", events : { click : this.onCropTooBigPopupApply } } ),
						new BX.PopupWindowButtonLink( {text : BX.message("JS_CORE_FI_CANCEL"), className : "popup-window-button-link-cancel", events : { click : this.onCropTooBigPopupCancel } } )
					],
					content : '<div class="adm-photoeditor-buttons-panel-cropping-too-big">' + BX.message("JS_CORE_FI_PRESET_IS_TOO_BIG") + '</div>'
				}
			);
		}
		this.onCropTooBigPopup.presetParams = {width : width, height : heigth};
		this.onCropTooBigPopup.show();
	},
	cropApply : function(cropParams)
	{
		this.stack.add(this.getCopyCanvas());
		var tmpCanvas2 = this.getWorkCanvas(false);
		BX.adjust(tmpCanvas2, { props : { width : cropParams.width, height : cropParams.height } });
		tmpCanvas2.getContext("2d").drawImage(this.canvas, (-1) * cropParams.left, (-1) * cropParams.top);
		this.set(tmpCanvas2, { props : { width : cropParams.width, height : cropParams.height } } );
		this.changed = true;
	},
	cropInsert : function(preset, button)
	{
		if (preset && this.cropInit(button))
		{
			this.cropObj.insert(preset.width, preset.height);
		}
	},
	scaleObj : null,
	scaleInit : function(pointer)
	{
		if (this.scaleObj === null)
		{
			this.scaleObj = new CanvasScale(
				this.id,
				pointer,
				this.canvas
			);
			BX.addCustomEvent(this.scaleObj, "onScaleApply", BX.proxy(this.scaleApply, this));
			BX.addCustomEvent(this.scaleObj, "onScaleRestore", BX.proxy(this.scaleRestore, this));
			BX.addCustomEvent(this.scaleObj, "onScaleSuggest", BX.proxy(this.scaleSuggest, this));
			BX.addCustomEvent(this, "onSetCanvas", BX.proxy(function(){
				this.scaleObj.restore();
			}, this));
		}
		return true;
	},
	scale : function(e)
	{
		if (this.scaleInit(BX.proxy_context))
		{
			this.scaleObj.start(e, this.canvas);
		}
	},
	scaleWidth : function(width, prop, pointer)
	{
		if (this.scaleInit(pointer))
		{
			this.scaleObj.scaleWidth(width, this.canvas, prop);
		}
	},
	scaleHeight : function(height, prop, pointer)
	{
		if (this.scaleInit(pointer))
		{
			this.scaleObj.scaleHeight(height, this.canvas, prop);
		}
	},
	scaleApply : function(params)
	{
		this.stack.add(this.getCopyCanvas());
		BX.show(this.scaleObj.getPreventer());
		var
			tmpCanvas2 = this.getWorkCanvas(false),
			paramsOld = {
				maxVisibleScale : this.canvas.maxVisibleScale,
				scale : this.canvas.scale,
				width : this.canvas.width,
				height : this.canvas.height,
				"~shiftX" : this.canvas.pos['~shiftX'],
				"~shiftY" : this.canvas.pos['~shiftY']
			};

		BX.adjust(tmpCanvas2, { props : { width : params.width, height : params.height } });

		tmpCanvas2.getContext("2d").drawImage(this.canvas,
			0, 0, this.canvas.width, this.canvas.height,
			0, 0, params.width, params.height
		);

		this.set(tmpCanvas2, { props : { width : params.width, height : params.height } } );

		this.cursor.x = 0;
		this.cursor.y = 0;

		if (this.scaleZoom !== false)
		{
			var
				deltaWidth = (params.width - paramsOld.width) * paramsOld.scale,
				deltaHeight = (params.height - paramsOld.height) * paramsOld.scale,
				xShift = Math.max(
					((-1) * paramsOld["~shiftX"] + (deltaWidth / 2)),
					0),
				yShift = Math.max(
					((-1) * paramsOld['~shiftY'] + (deltaHeight / 2)),
					0);
			this.canvas.pos['~shiftX'] = (-1) * xShift / paramsOld.scale * this.canvas.scale;
			this.canvas.pos['~shiftY'] = (-1) * yShift / paramsOld.scale * this.canvas.scale;

			BX.addClass(this.canvasBlock, "adm-photoeditor-active-image-ascetic");
			this.zoomProcess(paramsOld.scale);
			BX.removeClass(this.canvasBlock, "adm-photoeditor-active-image-ascetic");
		}

		if (this.cropObj)
		{
			this.cropObj.scale();
		}

		BX.hide(this.scaleObj.getPreventer());
		this.changed = true;
	},
	scaleRestore : function()
	{
		BX.onCustomEvent(this, "onScaleCanvas", arguments);
	},
	scaleSuggest : function()
	{
		BX.onCustomEvent(this, "onScaleCanvas", arguments);
	}
};
var CanvasScale = function(id, pointer, canvas) {
	this.id = id;
	this.pointer = pointer;
	var pos = BX.pos(this.pointer.parentNode);
	this.divisionValue = pos.height / 200;
	this.process = BX.delegate(this.process, this);
	this.finish = BX.delegate(this.finish, this);

	if (canvas)
	{
		this.init(canvas);
	}
};
CanvasScale.prototype = {
	canvas : null,
	divisionValue : 0,
	value: 0,
	minValue : 0,
	maxValue : 100,
	cursor : {
		start : 0,
		end : 0
	},
	preventer : null,
	params : {
		scaleX : 1,
		scaleY : 1,
		"~scaleX" : 1,
		"~scaleY" : 1
	},
	init : function(canvas)
	{
		this.canvas = canvas;
		this.value = 0;
		this.minValue = (Math.max(1 / canvas.width, 1 / canvas.height) - 1) * 100;

		this.params.width = this.canvas.width;
		this.params.height = this.canvas.height;
		this.params["~scaleX"] = 1;
		this.params["~scaleY"] = 1;

		return this.block;
	},
	getPreventer : function()
	{
		var preventer = null;
		if (this.preventer === null)
		{
			preventer = BX.create('DIV', {
				attrs : {
					className : "adm-photoeditor-scale-area"
				}
			});
			if (BX(this.canvas))
			{
				this.canvas.parentNode.parentNode.insertBefore(preventer, this.canvas.parentNode);
				this.preventer = preventer;
			}
		}
		return this.preventer;
	},
	start : function(e, canvas)
	{
		this.init(canvas);
		BX.fixEventPageY(e);
		this.cursor.start = e.pageY;
		BX.bind(document, "mousemove", this.process);
		BX.bind(document, "mouseup", this.finish);
		return BX.PreventDefault(e);
	},
	process : function(e)
	{
		BX.fixEventPageY(e);
		this.cursor.end = e.pageY;
		this.value = (this.cursor.start - this.cursor.end) / this.divisionValue;
		if (this.value > this.maxValue)
			this.value = this.maxValue;
		else if (this.value < this.minValue)
			this.value = this.minValue;

		this.setVis();

		return BX.PreventDefault(e);
	},
	finish : function(e)
	{
		BX.unbind(document, "mousemove", this.process);
		BX.unbind(document, "mouseup", this.finish);
		if (parseInt(this.value) !== 0)
		{
			this.change();
		}
		else
		{
			this.restore();
		}
		return BX.PreventDefault(e);
	},
	setVis : function()
	{
		this.pointer.style.top = (50 + parseInt((-1) * this.value * 0.5)) + '%';
		this.pointer.innerHTML = Math.ceil(this.value) + '%';
		this.params.scaleX = (1 + this.value / 100) * this.params["~scaleX"];
		this.params.scaleY = (1 + this.value / 100) * this.params["~scaleY"];
		this.params.width = Math.ceil(this.canvas.width * this.params.scaleX);
		this.params.height = Math.ceil(this.canvas.height * this.params.scaleY);
		this.canvas.style.transform = "scale(" + this.params.scaleX + ", " + this.params.scaleY + ")";

		BX.onCustomEvent(this, "onScaleSuggest", [this.params, this.value])
	},
	scaleWidth : function(width, canvas, prop)
	{
		width = Math.ceil(width);
		if (width <= 0)
			return;

		this.init(canvas);
		var delta = this.params["~scaleX"];
		this.params["~scaleX"] = width / this.params.width;
		delta = this.params["~scaleX"] - delta;
		if (prop)
		{
			this.params["~scaleY"] += delta;
		}
		this.setVis();
		this.change();
	},
	scaleHeight : function(height, canvas, prop)
	{
		height = Math.ceil(height);
		if (height <= 0)
			return;
		this.init(canvas);
		var delta = this.params["~scaleX"];
		this.params["~scaleY"] = height / this.params.height;
		delta = this.params["~scaleY"] - delta;
		if (prop)
		{
			this.params["~scaleX"] += delta;
		}
		this.setVis();
		this.change();
	},
	change : function()
	{
		BX.onCustomEvent(this, "onScaleApply", [this.params, this.value]);
	},
	restore : function()
	{
		this.value = 0;
		this.params["~scaleX"] = 1;
		this.params["~scaleY"] = 1;
		this.setVis();
		this.canvas.style.transform = '';
		BX.onCustomEvent(this, "onScaleRestore", [this.canvas]);
	}
};
var CanvasCrop = function(id, canvas, pointerOnCanvas) {
	this.id = id;
	this.canvas = canvas;
	this.block = pointerOnCanvas;
};
CanvasCrop.prototype = {
	active : false,

	canvas : null,
	root : null,
	preventer : null,
	projection : null,
	pointer : null, // pointer on projection
	block : null, // pointer on canvas

	turn : function()
	{
		if (this.init())
		{
			if (this.active === false)
			{
				this.start();
			}
			else
			{
				this.finish();
			}
		}
	},
	cursor : {
		pageX : 0,
		pageY : 0
	},
	cropParams : {
		width : 0,
		height : 0,
		top : 0,
		left : 0,
		topShift : 0,
		leftShift : 0,
		defaultWidth : 0,
		defaultHeight : 0
	},
	init : function()
	{
		if (this.preventer === null)
		{
			this.root = BX.create('DIV', {
				attrs : {
					className : "adm-photoeditor-crop-area-root"
				},
				style : {
					position : "absolute",
					zIndex :  BX.PopupWindow.getOption("popupOverlayZindex") + getZIndex(10),
					boxSizing: "border-box",
					"-webkit-user-select" : "none"
				}
			});
			document.body.appendChild(this.root);
			this.preventer = BX.create('DIV', {
				attrs : {
					className : "adm-photoeditor-crop-area"
				}
			});

			this.apply = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-apply" },
				style : { zIndex : 3 },
				events : {
					click : BX.delegate(this.cropApply, this)
				}
			});
			this.cancel = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-cancel" },
				style : { zIndex : 3 },
				events : {
					click : BX.delegate(this.cropCancel, this)
				}
			});
			this.proportion = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-proportion" },
				style : { zIndex : 3 },
				events : {
					click : BX.delegate(this.cropProportionActivate, this)
				}
			});

			this.width = BX.create('DIV', { attrs : { className : "adm-photoeditor-crop-width" } } );
			this.height = BX.create('DIV', { attrs : { className : "adm-photoeditor-crop-height" } } );
			this.stretchStart = BX.delegate(this.stretchStart, this);
			this.stretch = BX.delegate(this.stretch, this);
			this.stretchEnd = BX.delegate(this.stretchEnd, this);
			this.leftBottom = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-left-bottom" },
				events : {
					mousedown : this.stretchStart
				}
			} );
			this.left = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-left" },
				events : {
					mousedown : this.stretchStart
				}
			} );
			this.leftTop = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-left-top" },
				events : {
					mousedown : this.stretchStart
				}
			} );
			this.top = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-top" },
				events : {
					mousedown : this.stretchStart
				}
			} );
			this.rightTop = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-right-top" },
				events : {
					mousedown : this.stretchStart
				}
			} );
			this.right = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-right" },
				events : {
					mousedown : this.stretchStart
				}
			} );
			this.rightBottom = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-right-bottom" },
				events : {
					mousedown : this.stretchStart
				}
			} );
			this.bottom = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-bottom" },
				events : {
					mousedown : this.stretchStart
				}
			} );

			this.pointer = BX.create('DIV', {
				attrs : { className : "adm-photoeditor-crop-pointer" },
				style : {
					position : "absolute",
					top : 0, left : 0, bottom : 0, right: 0,
					boxSizing : "border-box",
					zIndex : 3,
					cursor : "pointer"
				},
				children : [
					this.apply,
					this.cancel,
					this.proportion,
					BX.create('DIV',
						{
							attrs : {
								className : "adm-photoeditor-crop-measures"
							},
							children : [
								this.width,
								this.height
							]
						}),
					this.leftBottom,
					this.left,
					this.leftTop,
					this.top,
					this.rightTop,
					this.right,
					this.rightBottom,
					this.bottom
				]
			});
			this.overlayTop = BX.create('DIV', { attrs : { className : "adm-photoeditor-crop-overlay-top" } } );
			this.overlayRight = BX.create('DIV', { attrs : { className : "adm-photoeditor-crop-overlay-right" } } );
			this.overlayBottom = BX.create('DIV', { attrs : { className : "adm-photoeditor-crop-overlay-bottom" } } );
			this.overlayLeft = BX.create('DIV', { attrs : { className : "adm-photoeditor-crop-overlay-left" } } );

			this.projection = BX.create('DIV', {
				attrs : {id : 'projection'},
				style : { position : "absolute", top : 0, left : 0, bottom : 0, right: 0, zIndex : 1001, display : 'none', boxSizing: "border-box" },
				children : [
					this.overlayTop,
					this.overlayRight,
					this.overlayBottom,
					this.overlayLeft,
					this.pointer
				]
			} );
			this.projection.appendChild(this.pointer);

			this.root.appendChild(this.projection);
			this.root.appendChild(this.preventer);

			this.drawStart = BX.delegate(this.drawStart, this);
			this.draw = BX.delegate(this.draw, this);
			this.drawEnd = BX.delegate(this.drawEnd, this);

			this.stop = BX.delegate(this.stop, this);

			this.moveStart = BX.delegate(this.moveStart, this);
			this.move = BX.delegate(this.move, this);
			this.moveEnd = BX.delegate(this.moveEnd, this);

			BX.bind(this.pointer, "mousedown", this.moveStart);
		}
		return this.block;
	},
	scale : function()
	{
		if (this.canvas.width < this.cropParams.width || this.canvas.height < this.cropParams.height)
		{
			this.finish()
		}
		else if (this.active === true)
		{
			var
				left = 0, leftAfter = 0,
				top = 0, topAfter = 0;

			if (this.projection)
			{
				left = Math.ceil(parseInt(this.projection.style.left ? this.projection.style.left.replace("px", "") : 0));
				top = Math.ceil(parseInt(this.projection.style.top ? this.projection.style.top.replace("px", "") : 0));
			}

			this.showProjection();

			if (this.projection)
			{
				leftAfter = Math.ceil((this.projection.style.left ? parseInt(this.projection.style.left.replace("px", "")) : 0));
				topAfter = Math.ceil((this.projection.style.top ? parseInt(this.projection.style.top.replace("px", "")) : 0));

				if (left != leftAfter)
					this.pointer.left = Math.max(0, (this.pointer.left + left - leftAfter));
				if (topAfter != top)
					this.pointer.top = Math.max(0, (this.pointer.top + top - topAfter));
			}

			this.cropParams.left = Math.ceil(this.pointer.left / this.canvas.scale + this.canvas.visiblePart.left);
			this.cropParams.top = Math.ceil(this.pointer.top / this.canvas.scale + this.canvas.visiblePart.top);

			if (this.canvas.scale < 1)
			{
				this.cropParams.width = Math.ceil(this.pointer.width / this.canvas.scale);
				this.cropParams.height = Math.ceil(this.pointer.height / this.canvas.scale);
			}
			else
			{
				this.pointer.width = Math.ceil(this.cropParams.width * this.canvas.scale);
				this.pointer.height = Math.ceil(this.cropParams.height * this.canvas.scale);
			}

			this.setLeft(this.pointer.left, this.cropParams.left);
			this.setTop(this.pointer.top, this.cropParams.top);

			this.setWidth(this.pointer.width, this.cropParams.width);
			this.setHeight(this.pointer.height, this.cropParams.height);
		}
	},
	drawStart : function(e)
	{
		var left = 0,
			top = 0;
		if (e)
		{
			BX.fixEventPageXY(e);
			left = e.pageX - this.projection.pos.left;
			top = e.pageY - this.projection.pos.top;
			if (left > this.projection.pos.width || top > this.projection.pos.height)
				return this.finish();

			this.cursor = {
				pageX : e.pageX,
				pageY : e.pageY
			};
			BX.PreventDefault(e);
		}

		left = (left > 0 ? left : 0);
		top = (top > 0 ? top : 0);

		this.cropParams.left = Math.ceil(left / this.canvas.scale + this.canvas.visiblePart.left);
		this.cropParams.top = Math.ceil(top / this.canvas.scale + this.canvas.visiblePart.top);

		this.cropParams.maxWidth = this.canvas.width - this.cropParams.left;
		this.cropParams.maxHeight = this.canvas.height - this.cropParams.top;
		this.cropParams.savePropotions = (this.proportion.active === true);

		this.setLeft(left, this.cropParams.left);
		this.setWidth(0, 0);
		this.setTop(top, this.cropParams.top);
		this.setHeight(0, 0);

		this.showPointer();

		BX.bind(document, "mousemove", this.draw);
		return false;
	},
	draw : function(e)
	{
		var width = 0, height = 0;
		if (e)
		{
			BX.fixEventPageXY(e);
			width = e.pageX - this.cursor.pageX;
			height = e.pageY - this.cursor.pageY;
			BX.PreventDefault(e)
		}

		this.cropParams.width = Math.ceil(width / this.canvas.scale);
		if (width <= 0)
		{
			width = 0;
			this.cropParams.width = 0;
		}
		else if (width >= (this.projection.pos.width - this.pointer.left))
		{
			width = (this.projection.pos.width - this.pointer.left);
			this.cropParams.width = this.cropParams.maxWidth;
		}
		this.setWidth(width, this.cropParams.width);

		this.cropParams.height = Math.ceil(height / this.canvas.scale);
		if (height <= 0)
		{
			height = 0;
			this.cropParams.height = 0;
		}
		else if (height >= (this.projection.pos.height - this.pointer.top))
		{
			height = (this.projection.pos.height - this.pointer.top);
			this.cropParams.height = this.cropParams.maxHeight;
		}
		this.setHeight(height, this.cropParams.height);

		return false;
	},
	drawEnd : function(e)
	{
		if (e)
			BX.PreventDefault(e);
		this.stop();
		return false;
	},
	insert : function(width, height)
	{
		if (!this.init())
		{
			BX.DoNothing();
		}
		else if (this.canvas.width < width || this.canvas.height < height)
		{
			BX.onCustomEvent(this, "onCropTooBig", [width, height]);
		}
		else
		{
			this.start();

			this.cropProportionActivate(true);
			BX.onCustomEvent(this, "onCropStart", []);

			this.drawStart();

			this.cropParams.width = width;
			width = Math.ceil(width * this.canvas.scale);
			if (width <= 0)
			{
				width = 0;
				this.cropParams.width = 0;
			}
			else if (width >= (this.projection.pos.width - this.pointer.left))
			{
				width = (this.projection.pos.width - this.pointer.left);
				this.cropParams.width = this.cropParams.maxWidth;
			}
			this.setWidth(width, this.cropParams.width);

			this.cropParams.height = height;
			height = Math.ceil(height * this.canvas.scale);
			if (height <= 0)
			{
				height = 0;
				this.cropParams.height = 0;
			}
			else if (height >= (this.projection.pos.height - this.pointer.top))
			{
				height = (this.projection.pos.height - this.pointer.top);
				this.cropParams.height = this.cropParams.maxHeight;
			}
			this.setHeight(height, this.cropParams.height);

			this.drawEnd();
		}
	},
	moveStart : function(e)
	{
		this.cursor = null;
		if (e.target == this.pointer)
		{
			BX.fixEventPageXY(e);
			this.showPreventer();

			this.cursor = {
				pageX : e.pageX,
				pageY : e.pageY,
				x : e.pageX - this.projection.pos.left - this.pointer.left,
				y : e.pageY - this.projection.pos.top - this.pointer.top
			};

			BX.bind(document, "mousemove", this.move);
			BX.bind(document, "mouseup", this.moveEnd);
		}
	},
	move : function(e)
	{
		if (this.cursor != null)
		{
			BX.fixEventPageXY(e);
			var deltaX = e.pageX - this.cursor.pageX,
				deltaY = e.pageY - this.cursor.pageY,

				left = this.pointer.left + deltaX,
				top = this.pointer.top + deltaY;

			this.cursor.pageX = e.pageX;
			this.cursor.pageY = e.pageY;

			if (
				(left + this.pointer.width) >= this.projection.pos.width &&
				(this.canvas.visiblePart.left + this.canvas.visiblePart.width) >= this.canvas.width
			)
			{
				left = (this.projection.pos.width - this.pointer.width);
				this.cropParams.left = this.canvas.width - this.cropParams.width;
			}
			else
			{
				if (left <= 0)
					left = 0;
				else if ((left + this.pointer.width) >= this.projection.pos.width)
					left = (this.projection.pos.width - this.pointer.width);
				this.cropParams.left = Math.ceil(left / this.canvas.scale + this.canvas.visiblePart.left);
			}

			if (
				(top + this.pointer.height) >= this.projection.pos.height &&
				(this.canvas.visiblePart.top + this.canvas.visiblePart.height) >= this.canvas.height
			)
			{
				top = (this.projection.pos.height - this.pointer.height);
				this.cropParams.top = this.canvas.height - this.cropParams.height;
			}
			else
			{
				if (top <= 0)
					top = 0;
				else if ((top + this.pointer.height) >= this.projection.pos.height)
					top = (this.projection.pos.height - this.pointer.height);
				this.cropParams.top = Math.ceil(top / this.canvas.scale + this.canvas.visiblePart.top);
			}
			this.setLeft(left, this.cropParams.left);
			this.setTop(top, this.cropParams.top);
		}
	},
	moveEnd : function()
	{
		this.hidePreventer();
		BX.unbind(document, "mousemove", this.move);
		BX.unbind(document, "mouseup", this.moveEnd);
	},
	showPointer : function()
	{
		this.block.style.display = "block";
		this.pointer.style.display = "block";
		this.overlayTop.style.display = "block";
		this.overlayRight.style.display = "block";
		this.overlayBottom.style.display = "block";
		this.overlayLeft.style.display = "block";
	},
	hidePointer : function()
	{
		this.block.style.top = 0;
		this.block.style.bottom = 0;
		this.block.style.width = 0;
		this.block.style.height = 0;

		this.block.style.display = "none";

		this.pointer.style.display = "none";
		this.overlayTop.style.display = "none";
		this.overlayRight.style.display = "none";
		this.overlayBottom.style.display = "none";
		this.overlayLeft.style.display = "none";
	},
	showProjection : function()
	{
		var pos = BX.pos(this.block.parentNode.parentNode, false);

		BX.adjust(this.root, {style : {
			left : pos.left + 'px',
			top : pos.top + 'px',
			width : pos.width + 'px',
			height : pos.height + 'px',
			display : "block"
		}});

		var projection = { left : 0, top : 0, right : 0, bottom : 0, display : "block" };
		if (this.canvas.visiblePart.topGap > 0)
			projection.top = Math.ceil(this.canvas.visiblePart.topGap) + 'px';
		if (this.canvas.visiblePart.leftGap > 0)
			projection.left = Math.ceil(this.canvas.visiblePart.leftGap) + 'px';
		if (this.canvas.visiblePart.bottomGap > 0)
			projection.bottom = Math.ceil(this.canvas.visiblePart.bottomGap) + 'px';
		if (this.canvas.visiblePart.rightGap > 0)
			projection.right = Math.ceil(this.canvas.visiblePart.rightGap) + 'px';
		BX.adjust(this.projection, {style : projection});

		this.projection.pos = BX.pos(this.projection);
	},
	hideProjection : function()
	{
		BX.hide(this.root);
		BX.hide(this.projection);
		BX.hide(this.preventer);
	},
	showPreventer : function()
	{
		BX.show(this.preventer);
	},
	hidePreventer : function()
	{
		BX.hide(this.preventer);
	},
	start : function()
	{
		this.active = true;

		BX.bind(document, "mousedown", this.drawStart);
		BX.bind(document, "mouseup", this.drawEnd);

		this.hidePointer();
		this.showProjection();
		this.showPreventer();

		BX.onCustomEvent(this, "onCropStart", []);
	},
	stop : function()
	{
		this.hidePreventer();

		BX.unbind(document, "mousedown", this.drawStart);
		BX.unbind(document, "mousemove", this.draw);
		BX.unbind(document, "mouseup", this.drawEnd);

		BX.onCustomEvent(this, "onCropStop", []);
	},
	finish : function()
	{

		this.active = false;
		this.stop();
		this.hidePointer();
		this.hideProjection();
		BX.onCustomEvent(this, "onCropFinish", [this.cropParams]);
	},
	cropApply : function()
	{
		this.finish();
		if (this.cropParams.width > 0 && this.cropParams.height > 0)
		{
			BX.onCustomEvent(this, "onCropApply", [this.cropParams]);
		}
	},
	cropCancel : function()
	{
		BX.onCustomEvent(this, "onCropCancel", []);
		this.finish();
	},
	cropProportion : {
		active : false,
		"w/h" : 0,
		"h/w" : 0
	},
	cropProportionActivate : function(active)
	{
		if (!(active === true || active === false))
		{
			active = this.cropProportion.active !== true;
		}
		this.cropProportion.active = active;
		if (this.cropProportion.active)
		{
			if (!BX.hasClass(this.proportion, "active"))
			{
				BX.addClass(this.proportion, "active");
			}
			BX.hide(this.left);
			BX.hide(this.top);
			BX.hide(this.right);
			BX.hide(this.bottom);
		}
		else
		{
			BX.removeClass(this.proportion, "active");
			BX.show(this.left);
			BX.show(this.top);
			BX.show(this.right);
			BX.show(this.bottom);
		}
	},
	cropProportionInit : function()
	{
		if (this.cropProportion.active !== true)
		{
			this.cropProportion["w/h"] = 0;
			this.cropProportion["h/w"] = 0;
		}
		else if (this.pointer.width > 0 && this.pointer.height > 0)
		{
			this.cropProportion["w/h"] = this.pointer.width / this.pointer.height;
			this.cropProportion["h/w"] = this.pointer.height / this.pointer.width;
		}
		else
		{
			this.cropProportion["w/h"] = 1;
			this.cropProportion["h/w"] = 1;
		}
	},
	stretchPosition : null,
	stretchStart : function(e)
	{
		BX.PreventDefault(e);
		var div = BX.proxy_context;
		this.stretchPosition = div.className.replace("adm-photoeditor-crop-", "");
		this.cursor = null;
		if (this.stretchPosition == "left-bottom" ||
			this.stretchPosition == "left" ||
			this.stretchPosition == "left-top" ||
			this.stretchPosition == "top" ||
			this.stretchPosition == "right-top" ||
			this.stretchPosition == "right" ||
			this.stretchPosition == "right-bottom" ||
			this.stretchPosition == "bottom"
		)
		{
			BX.fixEventPageXY(e);
			this.cropProportionInit();
			this.showPreventer();

			this.cursor = {
				pageX : e.pageX,
				pageY : e.pageY,
				left : this.projection.pos.left + this.pointer.left,
				right : (this.projection.pos.left + this.pointer.left + this.pointer.width),
				top : this.projection.pos.top + this.pointer.top,
				bottom : this.projection.pos.top + this.pointer.top + this.pointer.height
			};

			BX.bind(document, "mousemove", this.stretch);
			BX.bind(document, "mouseup", this.stretchEnd);
		}


		return false;
	},
	stretch : function(e)
	{
		if (this.cursor != null)
		{
			BX.fixEventPageXY(e);

			var
				tmp,
				left = null, width = null,
				top = null, height = null;

			this.cursor.pageX = e.pageX;
			this.cursor.pageY = e.pageY;

			if (this.stretchPosition.indexOf("left") >= 0)
			{
				tmp = (this.pointer.left + this.pointer.width);

				width = Math.max((this.cursor.right - e.pageX), 0);
				width = Math.min(width, tmp);

				left = tmp - width;
			}
			else if (this.stretchPosition.indexOf("right") >= 0)
			{
				width = Math.max((e.pageX - this.cursor.left), 0);
			}

			if (width !== null)
			{
				left = (left === null ? this.pointer.left : left);

				if (width <= 0)
				{
					width = 0;
				}
				else if (width >= (this.projection.pos.width - left))
				{
					width = (this.projection.pos.width - left);
				}
			}

			if (this.stretchPosition.indexOf("top") >= 0)
			{
				tmp = this.pointer.top + this.pointer.height;

				height = Math.max((this.cursor.bottom - e.pageY), 0);
				height = Math.min(height, tmp);

				top = tmp - height;
			}
			else if (this.stretchPosition.indexOf("bottom") >= 0)
			{
				height = Math.max((e.pageY - this.cursor.top), 0);
			}

			if (height !== null)
			{
				top = (top === null ? this.pointer.top : top);

				if (height <= 0)
				{
					height = 0;
				}
				else if (height >= (this.projection.pos.height - top))
				{
					height = (this.projection.pos.height - top);
				}
			}

			var strictMax = false;
			if (this.cropProportion.active === true)
			{
				if (width && height)
				{
					var oldValue;
					if (this.cropProportion["h/w"] * width > height)
					{
						oldValue = width;
						width = this.cropProportion["w/h"] * height;

						if (this.stretchPosition.indexOf("left") >= 0)
						{
							left += (oldValue - width);
						}
					}
					else
					{
						oldValue = height;
						height = this.cropProportion["h/w"] * width;
						if (this.stretchPosition.indexOf("top") >= 0)
						{
							top += (oldValue - height);
						}
					}

					if ((this.cropProportion["h/w"] == 1))
					{
						this.cropParams.left = Math.ceil(left / this.canvas.scale + this.canvas.visiblePart.left);
						this.cropParams.top = Math.ceil(top / this.canvas.scale + this.canvas.visiblePart.top);
						strictMax = Math.min(
							(this.canvas.width - this.cropParams.left),
							(this.canvas.height - this.cropParams.top)
						);
					}
				}
				else
				{
					width = null;
					height = null;
				}
			}

			if (width !== null)
			{
				this.cropParams.left = Math.ceil(left / this.canvas.scale + this.canvas.visiblePart.left);
				this.cropParams.maxWidth = (strictMax || (this.canvas.width - this.cropParams.left));
				this.cropParams.width = Math.ceil(width / this.canvas.scale);

				if (width <= 0)
				{
					this.cropParams.width = 0;
				}
				else if (this.cropParams.width >= this.cropParams.maxWidth)
				{
					this.cropParams.width = this.cropParams.maxWidth
				}
				this.setLeft(left, this.cropParams.left);
				this.setWidth(width, this.cropParams.width);
			}

			if (height !== null)
			{
				this.cropParams.top = Math.ceil(top / this.canvas.scale + this.canvas.visiblePart.top);
				this.cropParams.maxHeight = (strictMax || (this.canvas.height - this.cropParams.top));
				this.cropParams.height = Math.ceil(height / this.canvas.scale);

				if (height <= 0)
				{
					this.cropParams.height = 0;
				}
				else if (this.cropParams.height >= this.cropParams.maxHeight)
				{
					this.cropParams.height = this.cropParams.maxHeight;
				}

				this.setTop(top, this.cropParams.top);
				this.setHeight(height, this.cropParams.height);
			}
		}
	},
	stretchEnd : function()
	{
		this.hidePreventer();
		BX.unbind(document, "mousemove", this.stretch);
		BX.unbind(document, "mouseup", this.stretchEnd);
	},
	setWidth : function(visible, real)
	{
		this.pointer.width = visible;
		this.pointer.style.width = (visible + 'px');
		this.block.width =
		this.width.innerHTML = real;
		this.block.style.width = (real + 'px');

		this.overlayTop.style.width = (this.pointer.left + this.pointer.width) + 'px';
		this.overlayRight.style.left = (this.pointer.left + this.pointer.width) + 'px';

	},
	setLeft : function(visible, real)
	{
		this.pointer.style.left = visible + 'px';
		this.pointer.left = visible;
		this.block.style.left = real + 'px';

		this.overlayTop.style.width = (this.pointer.left + this.pointer.width) + 'px';
		this.overlayRight.style.left = (this.pointer.left + this.pointer.width) + 'px';
		this.overlayBottom.style.left = this.pointer.left + 'px';
		this.overlayLeft.style.width = this.pointer.left + 'px';
	},
	setHeight : function(visible, real)
	{
		this.pointer.height = visible;
		this.pointer.style.height = (visible+ 'px');
		this.block.height =
		this.height.innerHTML = real;
		this.block.style.height = (real + 'px');

		this.overlayRight.style.height = (this.pointer.top + visible) + 'px';
		this.overlayBottom.style.top = (this.pointer.top + visible) + 'px';
	},
	setTop : function(visible, real)
	{
		this.pointer.style.top = visible + 'px';
		this.pointer.top = visible;
		this.block.style.top = real + 'px';

		this.overlayTop.style.height = this.pointer.top + 'px';
		this.overlayRight.style.height = (this.pointer.top + this.pointer.height) + 'px';
		this.overlayBottom.style.top = (this.pointer.top + this.pointer.height) + 'px';
		this.overlayLeft.style.top = this.pointer.top + 'px';
	}
};
var CanvasMapMaster = function(block, params) {
	this.block = block;
	this.id = this.block.id + 'Map';
	if (BX(this.id + 'Root'))
	{
		this.root = BX(this.id + 'Root')
	}
	else
	{
		this.root = BX.create('DIV', {
			style : {
				display : "none",
				position : "absolute",
				zIndex :  BX.PopupWindow.getOption("popupOverlayZindex") + getZIndex(20)
			},
			attrs : {
				id : this.id + 'Root',
				className : "adm-photoeditor-active-map-root"
			},
			html : [
				/*jshint multistr: true */
				'<div class="adm-photoeditor-active-map-block" id="', this.id, 'Block"> \
					<div class="adm-photoeditor-active-map"> \
						<div class="adm-photoeditor-active-map-image"> \
							<canvas id="', this.id, 'Canvas"></canvas> \
						</div> \
						<div class="adm-photoeditor-active-map-area"></div> \
						<div class="adm-photoeditor-active-map-pointer"></div> \
					</div> \
					<div class="adm-photoeditor-active-map-handle" id="', this.id, 'Resizer"> \
				</div> \
				</div>'
			].join("").replace(/[\n\t]/gi, '').replace(/>\s</gi, '><')
		} );
		document.body.appendChild(this.root);
	}
	this.moveStart = BX.delegate(this.moveStart, this);
	this.move = BX.delegate(this.move, this);
	this.moveEnd = BX.delegate(this.moveEnd, this);

	this._bindNodes = BX.delegate(this.bindNodes, this);
	BX.defer_proxy(this._bindNodes)(params);
	BX.bind(window, "resize", BX.proxy(this.onResizeWindow, this));
	return this;
};
CanvasMapMaster.prototype = {
	bindNodesCounter : 0,
	bindNodes : function(params) {
		this.bindNodesCounter++;
		if (this.bindNodesCounter > 100 || !BX(this.id + 'Canvas'))
		{
			if (this.bindNodesCounter <= 100)
				BX.defer_proxy(this._bindNodes)(params);
			return;
		}

		this.canvasMapBlock = BX(this.id + 'Block');
		this.canvasMapResizer = BX(this.id + 'Resizer');
		BX.hide(this.canvasMapResizer); // TODO map-resize
		this.canvasMapCover = this.canvasMapBlock.firstChild;
		this.canvasMap = BX(this.id + 'Canvas');

		this.canvasMapPointer = this.canvasMapBlock.firstChild.childNodes[2];
		this.canvasMapPointer.params = params;

		BX.addClass(this.canvasMapPointer, "adm-photoeditor-active-map-pointer-draggable");
		BX.bind(this.canvasMapPointer, "mousedown", this.moveStart);
		BX.bind(this.canvasMapResizer, "mousedown", this.stretchStart);

		BX.onCustomEvent(this, "onMapIsInitialised", [this]);
	},
	init : function(canvas, canvasParams) {
		if (canvasParams)
		{
			var res = BX.UploaderUtils.scaleImage(canvasParams, this.canvasMapPointer.params);
			this.root.style.display = 'none';
			this.collapsedNode.style.display = 'none';
			BX.adjust(
				this.canvasMap, {
					props : res.destin
				}
			);
			this.canvasMap.pos = null;
			this.options.visible = false;

			BX.adjust(
				this.canvasMapCover, {
					style : {
						width : res.destin.width + 'px',
						height : res.destin.height + 'px'
					}
				}
			);
			BX.adjust(
				this.canvasMapPointer, {
					props : {
						width : res.destin.width,
						height : res.destin.height
					},
					style : {
						width : res.destin.width + 'px',
						height : res.destin.height + 'px'
					}
				}
			);
			this.canvasMap.canvasProp = res.coeff;
		}
		this.canvasMap.canvasWidth = canvas.width;
		this.canvasMap.canvasHeight = canvas.height;

		this.canvasMap.getContext("2d").drawImage(canvas,
			0, 0, canvas.width, canvas.height,
			0, 0, this.canvasMap.width, this.canvasMap.height
		);
	},
	options : {
		collapsed : false,
		collapsing : null,
		visible : false,
		busy : false,
		mode : "slow"
	},
	animationV: null,
	animationC: null,
	show : function() {
		if (this.options.visible)
			return;
		else if (this.animationV)
			this.animationV.stop();

		this.options.visible = true;

		var pos = BX.pos(this.block, false);

		this.root.pos = {
			top : pos.top,
			left : (pos.right - this.canvasMap.width),
			width : this.canvasMap.width,
			height : this.canvasMap.height
		};
		this.root.style.top = this.root.pos.top + 'px';
		this.root.style.left = this.root.pos.left + 'px';
		this.root.style.overflow = "hidden";
		this.root.style.width = this.root.pos.width + 'px';
		this.root.style.height = this.root.pos.height + 'px';
		this.root.style.display = (this.options.collapsed ? 'none' : 'block');
		this.collapsedNode.style.display = '';

		this.canvasMapBlock.style.top = "-" + (this.canvasMap.height + 1) + 'px';
		this.canvasMapBlock.style.left = 0;
		this.canvasMapBlock.style.width = this.canvasMap.width + 'px';
		this.canvasMapBlock.style.height = this.canvasMap.height + 'px';

		this.canvasMapBlock.y = (this.canvasMapBlock.y || this.canvasMap.height);
		this.canvasMapBlock.x = (this.canvasMapBlock.x || this.canvasMap.width);

		this.animationV = new BX.easing({
			duration : 200,
			start : { y : this.canvasMapBlock.y, x : this.canvasMapBlock.x},
			finish : { y : 0, x : 0},
			step : BX.delegate(function(state){
				this.canvasMapBlock.y = state.y;
				this.canvasMapBlock.style.top = "-" + state.y + "px";
			}, this),
			complete : BX.delegate(function(){
				this.canvasMapBlock.y = false;
				this.canvasMapBlock.x = false;
				this.canvasMapBlock.style.top = 0;
				this.canvasMapBlock.style.left = 0;
				this.root.style.overflow = "visible";
				this.animationV = null;
			}, this)
		});
		this.animationV.animate();
	},
	hide : function(slow) {
		var func = BX.delegate(function(){
			this.root.style.display = 'none';
			this.collapsedNode.style.display = 'none';
			delete this.root.pos;
			this.canvasMapBlock.y = false;
			this.canvasMapBlock.x = false;
			this.animationV = null;
		}, this);
		if (slow !== true)
		{
			if (this.animationV)
				this.animationV.stop();
			this.options.visible = false;
			func();
		}
		else if (this.options.visible)
		{
			if (this.animationV)
				this.animationV.stop();
			this.options.visible = false;

			this.root.style.overflow = "hidden";

			this.canvasMapBlock.y = (this.canvasMapBlock.y || 0);
			this.canvasMapBlock.x = (this.canvasMapBlock.x || 0);

			this.animationV = new BX.easing({
				duration : 200,
				start : { y : this.canvasMapBlock.y, x : this.canvasMapBlock.x},
				finish : { y : this.canvasMap.height, x : this.canvasMap.width},
				step : BX.delegate(function(state){
					this.canvasMapBlock.y = state.y;
					this.canvasMapBlock.style.top = "-" + state.y + "px";
					this.canvasMapBlock.x = state.x;
				}, this),
				complete : func
			});
			this.animationV.animate();
		}
		else
		{
			func();
		}
	},
	zoom : function(visPart) {
		var show = (visPart && (
			visPart["~top"] > 0 ||
			visPart["~left"] > 0 ||
			visPart["~width"] < this.canvasMap.canvasWidth ||
			visPart["~height"] < this.canvasMap.canvasHeight));
		if (show)
		{
			var shiftX = Math.ceil(visPart["~left"] * this.canvasMap.canvasProp),
				shiftY = Math.ceil(visPart["~top"] * this.canvasMap.canvasProp),
				width = Math.min(Math.ceil(visPart["~width"] * this.canvasMap.canvasProp), (this.canvasMap.width - shiftX)),
				height = Math.min(Math.ceil(visPart["~height"] * this.canvasMap.canvasProp), (this.canvasMap.height - shiftY));
			BX.adjust(
				this.canvasMapPointer, {
					props : {
						etalonWidth : Math.ceil(visPart["~etalonWidth"] * this.canvasMap.canvasProp),
						etalonHeight : Math.ceil(visPart["~etalonHeight"] * this.canvasMap.canvasProp),
						'bx-left' : shiftX,
						'bx-top' : shiftY,
						width : width,
						height : height
					},
					style : {
						width : width + 'px',
						height : height + 'px',
						transform : 'translate3d(' + shiftX + 'px, ' + shiftY + 'px, 0)'
					}
				}
			);
		}
		else
		{
			BX.adjust(
				this.canvasMapPointer, {
					props : {
						etalonWidth : this.canvasMap.width,
						etalonHeight : this.canvasMap.height,
						'bx-left' : 0,
						'bx-top' : 0,
						width : this.canvasMap.width,
						height : this.canvasMap.height
					},
					style : {
						width : this.canvasMap.width + 'px',
						height : this.canvasMap.height + 'px',
						transform : 'translate3d(0, 0, 0)'
					}
				}
			);
		}
	},
	shiftX : 0,
	shiftY : 0,
	moveEventTimeout : 0,
	moveEventParams : {top : 0, left : 0},
	moveCursor : null,
	wSize : null,
	moveStart : function(e) {
		this.moveCursor = null;

		if (e.target == this.canvasMapPointer)
		{
			if (this.canvasMap.pos === null)
				this.canvasMap.pos = BX.pos(this.canvasMap);

			this.wSize = BX.GetWindowSize();

			this.moveCursor = {
				deltaX : this.canvasMap.pos['left'] + (this.canvasMapPointer['bx-left'] || 0),
				deltaY : this.canvasMap.pos['top'] + (this.canvasMapPointer['bx-top'] || 0)
			};
			this.moveCursor.x = e.clientX + this.wSize.scrollLeft - this.moveCursor.deltaX;
			this.moveCursor.y = e.clientY + this.wSize.scrollTop - this.moveCursor.deltaY;

			BX.bind(document, "mousemove", this.move);
			BX.bind(document, "mouseup", this.moveEnd);
		}
	},
	move : function(e)
	{
		if (this.moveCursor === null)
			return;

		var deltaX = e.clientX + this.wSize.scrollLeft,
			deltaY = e.clientY + this.wSize.scrollTop,
			width = parseInt(this.canvasMapPointer.etalonWidth),
			height = parseInt(this.canvasMapPointer.etalonHeight),
			right = false,
			bottom = false;

		deltaX -= (this.canvasMap.pos['left'] + this.moveCursor.x);
		if (deltaX <= 0)
			deltaX = 0;
		else if (deltaX > (this.canvasMap.width - this.canvasMapPointer.width))
		{
			deltaX = (this.canvasMap.width - this.canvasMapPointer.width);
			right = true;
		}

		deltaY -= (this.canvasMap.pos['top'] + this.moveCursor.y);
		if (deltaY <= 0)
			deltaY = 0;
		else if (deltaY > (this.canvasMap.height - this.canvasMapPointer.height))
		{
			deltaY = (this.canvasMap.height - this.canvasMapPointer.height);
			bottom = true;
		}

		if (deltaX != this.shiftX || deltaY != this.shiftY)
		{
			this.shiftX = deltaX;
			this.shiftY = deltaY;
			if (width > this.canvasMapPointer.width && (width + deltaX) > this.canvasMap.width)
				width = this.canvasMap.width - deltaX;
			if (height > this.canvasMapPointer.height && (height + deltaY) > this.canvasMap.height)
				height = this.canvasMap.height - deltaY;

			BX.adjust(
				this.canvasMapPointer, {
					props : {
						'bx-left' : deltaX,
						'bx-top' : deltaY,
						width : width,
						height : height
					},
					style : {
						width : width + 'px',
						height : height + 'px',
						transform : 'translate3d(' + deltaX + 'px, ' + deltaY + 'px, 0)'
					}
				}
			);
			clearTimeout(this.moveEventTimeout);
			this.moveEventParams = {
				'%left' : deltaX / this.canvasMap.width,
				'%top' : deltaY / this.canvasMap.height,
				left : deltaX / this.canvasMap.canvasProp,
				top : deltaY / this.canvasMap.canvasProp,
				right : right,
				bottom : bottom,
				width : width / this.canvasMap.canvasProp,
				height : height / this.canvasMap.canvasProp
			};
			if (!this['moveEventFunction'])
				this['moveEventFunction'] = BX.delegate(function(){
					BX.onCustomEvent(this, 'onMapPointerIsMoved', [this.moveEventParams]);
				}, this);
			this.moveEventTimeout = setTimeout(this.moveEventFunction, 100);
		}
	},
	moveEnd : function()
	{
		BX.unbind(document, "mousemove", this.move);
		BX.unbind(document, "mouseup", this.moveEnd);
	},
	collapsedNode : null,
	registerCollapsedNode : function(node)
	{
		if (BX(node))
		{
			this.collapsedNode = BX(node);
			BX.bind(this.collapsedNode, "click", BX.delegate(function(){
					if (this.options.collapsed === true)
					{
						this.collapse(false);
					}
					else
					{
						this.collapse(true);
					}
			}, this));

			BX.bind(this.canvasMapBlock, "dblclick", BX.delegate(this.collapse, this));
		}
		this.collapseEnd();
	},
	collapseEnd : function()
	{
		if (this.options.collapsed)
		{
			this.root.style.display = 'none';
			if (this.collapsedNode)
				BX.removeClass(this.collapsedNode, "disabled");
			BX.removeClass(this.root, "collapse");
			BX.addClass(this.root, "collapse2");
		}
		else
		{
			this.root.style.display = 'block';
			if (this.collapsedNode)
				BX.addClass(this.collapsedNode, "disabled");
			BX.addClass(this.root, "collapse");
			BX.removeClass(this.root, "collapse2");
		}

		this.root.style.transform = 'translate(0, 0) scale(1, 1)';
		this.root.style.opacity = '1';

		if (this.root["~top"])
		{
			this.root.style.top = this.root["~top"];
			delete this.root["~top"];
		}


		this.options.collapsing = null;
	},
	collapse : function(collapse)
	{
		collapse = (collapse === true || collapse === false ? collapse : (!this.options.collapsed));
		if (this.options.collapsing !== null || this.options.collapsed == collapse)
			return;

		this.options.collapsing = true;
		this.options.collapsed = collapse;
		var posB, pos, shiftX, scale;
		if (collapse)
		{
			if (!this.collapsedNode || !this.root.pos)
			{
				this.collapseEnd();
			}
			else
			{
				posB = BX.pos(this.collapsedNode, false);
				pos = this.root.pos;
				shiftX = Math.ceil((posB.left + posB.width / 2 ) - (pos.left + pos.width / 2));
				scale = (posB.width * 0.7)/pos.width;

				this.root.style.transform = 'translate(' + shiftX + 'px, 0) scale(' + scale + ', ' + scale + ')';
				this.root.style.opacity = '0';
				this.root["~top"] = this.root.style.top;
				this.root.style.top = posB.top + 'px';

				setTimeout(BX.proxy(this.collapseEnd, this), 700);
			}
		}
		else if (this.root.pos)
		{
			this.collapseEnd();
			/*if (!this.collapsedNode)
			{
				this.collapseEnd();
			}
			else
			{
				posB = BX.pos(this.collapsedNode, false);
				pos = this.root.pos;
				shiftX = Math.ceil((posB.left + posB.width / 2 ) - (pos.left + pos.width / 2));
				scale = (posB.width * 0.7)/pos.width;
				BX.removeClass(this.root, "collapse2");

				this.root.style.opacity = '0';
				this.root.style.display = 'block';
				this.root.style.transform = 'translate(' + shiftX + 'px, 0) scale(' + scale + ', ' + scale + ')';
				this.root["~top"] = this.root.style.top;
				this.root.style.top = posB.top + 'px';

				BX.addClass(this.root, "collapse2");

				this.root.style.transform = 'translate(0, 0) scale(1, 1)';
				this.root.style.opacity = '1';
				this.root.style.top = this.root["~top"];

				setTimeout(BX.proxy(this.collapseEnd, this), 1000);
			}*/
		}
	},
	stretchStart : function()
	{

	},
	occupy : function()
	{
		this.options.busy = true;
	},
	release : function()
	{
		this.options.busy = false;
	},
	onResizeWindow : function()
	{
		if (this.options.visible)
		{
			var pos = BX.pos(this.block, false);
			this.root.pos.top = pos.top;
			this.root.pos.left = pos.left;
			this.root.style.top = pos.top + 'px';
			this.root.style.left = (pos.right - this.canvasMap.width) + 'px';
		}
	}
};
var frameMaster = new FrameMaster(),
	getZIndex = function(delta)
	{
		var res = Math.max(BX.WindowManager.GetZIndex(), BX.PopupWindow.getOption("popupOverlayZindex")) - BX.PopupWindow.getOption("popupOverlayZindex") + delta;
		return res;
	};
})();
