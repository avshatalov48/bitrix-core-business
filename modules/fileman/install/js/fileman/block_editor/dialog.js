function BXBlockEditorPreview(params)
{
	this.init(params);
}
BXBlockEditorPreview.prototype.init = function(params)
{
	this.url = params.url;
	this.site = params.site;
	this.previewContext = BX.findChildByClassName(params.context, 'bx-block-editor-preview-container', true);
	this.devicesContext = BX.findChildByClassName(this.previewContext, 'devices', true);

	this.iframePreview = BX.findChildByClassName(this.previewContext, 'preview-iframe', true);
	this.shadowNode = BX.findChild(this.previewContext, {className: 'shadow'}, true);

	var _this = this;

	this.deviceList = BX.findChildrenByClassName(this.devicesContext, 'device');
	for(var i in this.deviceList)
	{
		var device = this.deviceList[i];
		BX.bind(device, 'click', function(){
			_this.changeDevice(this);
		});
	}
	this.changeDevice();

	var loadHandler = function() {
		BX.loadCSS(
			'/bitrix/js/fileman/block_editor/preview.css?r=' + Math.random(),
			this.contentDocument,
			this.contentWindow
		);
		BX.removeClass(_this.shadowNode, 'active');
	};

	BX.ready(function()
	{
		BX.bind(_this.iframePreview, 'load', loadHandler);
	});
};
BXBlockEditorPreview.prototype.changeDevice = function(deviceNode)
{

	if(!deviceNode)
	{
		deviceNode = this.deviceList[0];
	}

	var width = deviceNode.getAttribute('data-bx-preview-device-width');
	var height = deviceNode.getAttribute('data-bx-preview-device-height');
	var className = deviceNode.getAttribute('data-bx-preview-device-class');

	var classNameList = [];
	for(var i in this.deviceList)
	{
		var deviceNodeTmp = this.deviceList[i];
		if(!deviceNodeTmp)
		{
			break;
		}
		if(deviceNodeTmp !== deviceNode)
		{
			BX.removeClass(deviceNodeTmp, 'active');
		}
		classNameList.push(deviceNodeTmp.getAttribute('data-bx-preview-device-class'));
	}
	BX.addClass(deviceNode, 'active');

	var frameWrapper = BX.findChildByClassName(this.previewContext, 'iframe-wrapper', true);
	if(frameWrapper)
	{
		BX.removeClass(frameWrapper, classNameList.join(' '));
		BX.addClass(frameWrapper, className);
	}

	this.iframePreview.style.width = width + 'px';
	this.iframePreview.style.height = height + 'px';

};

BXBlockEditorPreview.prototype.show = function(params)
{
	params = params || {};
	BX.addClass(this.shadowNode, 'active');
	BX.removeClass(this.shadowNode, 'access-denied');
	this.previewContext.style.display = 'block';

	var frameDoc = this.iframePreview.contentDocument;
	frameDoc.body.innerHTML = '<div style="display: none;">' +
		'<form method="post">' +
		'<textarea name="content"></textarea>' +
		'<input type="hidden" name="sessid" value="' + BX.bitrix_sessid() +'">' +
		'</form>' +
		'</div>';

	var input = frameDoc.body.querySelector('textarea');
	input.textContent = params.content;

	var form = frameDoc.body.querySelector('form');
	form.action = this.url;
	BX.submit(form);
};
BXBlockEditorPreview.prototype.hide = function()
{
	this.previewContext.style.display = 'none';
};

function BXBlockEditorDragDrop(params)
{
	this.CONST_ATTR_BLOCK_TYPE = 'data-bx-block-editor-block-type';
	this.CONST_ATTR_BLOCK_STATUS = 'data-bx-block-editor-block-status';

	this.init(params);
}
BXBlockEditorDragDrop.prototype =
{
	addItem: function(node)
	{
		this.dragdrop.addCatcher(node);
		this.dragdrop.addDragItem([node]);
	},

	removeItem: function(node)
	{
		this.dragdrop.removeCatcher(node);
	},

	init: function(params)
	{
		this.dragdrop = BX.DragDrop.create({
			dragItemClassName: 'bx-block-editor-i-block-list-item',
			dropZoneList: [],
			dragStart: BX.delegate(function(eventObj, dragElement, event){

				this.lastDragObject = null;
				this.dragEnterCounter = 0;

				for(var i in this.dragdrop.dropZoneNodeList)
				{
					BX.addClass(this.dragdrop.dropZoneNodeList[i], 'bx-dd-start');
				}

			}, this),
			dragDrop: BX.delegate(function(catcherObj, dragElement, event){

				BX.onCustomEvent(this, 'onZoneLeave', [catcherObj]);

				var pos = BX.pos(catcherObj);
				var before = event.offsetY < pos.height / 2;

				var blockStatus = dragElement.getAttribute(this.CONST_ATTR_BLOCK_STATUS);

				if(blockStatus == 'blank')
				{
					var blockType = dragElement.getAttribute(this.CONST_ATTR_BLOCK_TYPE);
					BX.onCustomEvent(this, 'onItemAdd', [blockType, catcherObj, before]);
				}
				else
				{
					BX.onCustomEvent(this, 'onItemMove', [dragElement, catcherObj, before]);
				}

			}, this),
			dragOver: BX.delegate(function(catcherObj, dragElement, event)
			{
				var blockInside = BX.findChildByClassName(catcherObj, 'bx-block-inside', true);
				blockInside = blockInside || catcherObj;
				var pos = BX.pos(blockInside);
				if(event.offsetY < pos.height / 2)
				{
					BX.onCustomEvent(this, 'onZoneEnter', [catcherObj, true]);
				}
				else
				{
					BX.onCustomEvent(this, 'onZoneEnter', [catcherObj, false]);
				}

			}, this),
			dragEnter: BX.delegate(function(catcherObj, dragElement, event)
			{
				if(catcherObj == event.target)
				{
					BX.onCustomEvent(this, 'onZoneEnter', [catcherObj, true]);
					event.preventDefault();
				}

			}, this),
			dragLeave: BX.delegate(function(catcherObj, dragElement, event)
			{
				if(catcherObj == event.target)
				{
					BX.onCustomEvent(this, 'onZoneLeave', [catcherObj]);
					this.dragEnterCounter = 0;
				}

			}, this),
			dragEnd: BX.delegate(function(catcherObj, dragElement, event)
			{
				BX.onCustomEvent(this, 'onZoneLeave', [catcherObj]);

				for(var i in this.dragdrop.dropZoneNodeList)
				{
					BX.removeClass(this.dragdrop.dropZoneNodeList[i], 'bx-dd-start');
				}

			}, this)
		});
	}
};

function BXBlockEditorDialogFileInput(params)
{
	this.isMultiImage = true;
	this.caller = params.caller;
	this.context = this.caller.context;
	this.id = params.id;

	this.fileInput = BX.UI.FileInput.getInstance('bx_file_' + this.id.toLowerCase());
	this.fileList = [];

	BX.addCustomEvent(this.caller, 'onLinkControl-src', BX.delegate(this.onLink, this));
	BX.addCustomEvent(this.caller, 'onSave', BX.delegate(this.onSave, this));
	BX.addCustomEvent(this.caller, 'loadSetting-src', BX.delegate(this.onLoadSetting, this));
}
BXBlockEditorDialogFileInput.prototype =
{
	getImages: function()
	{
		var itemList = this.fileInput.agent.getItems();
		itemList.reset();

		var item, ids = [];
		while ((item = itemList.getNext()) && item)
		{
			ids.push(item.id);
		}

		return ids.map(function (id) {
			var filtered = this.fileList.filter(function (file) {
				return (file.id == id && file.url);
			});
			if (filtered.length > 0)
			{
				return filtered[filtered.length - 1].url;
			}
			else
			{
				return null;
			}
		}, this);
	},

	setImages: function(pathList)
	{
		this.fileInput.deleteFiles();
		this.fileDialogFiles = {};

		// do not use framing when load images by link
		// save value of frame setting
		var frameFilesOldValue = this.fileInput.uploadParams["frameFiles"];
		this.fileInput.uploadParams["frameFiles"] = "N";

		for(var i in pathList)
		{
			var path = pathList[i];
			var pathAr = path.split('/');
			var filename = pathAr.pop();
			var filedir = pathAr.join('/');

			this.fileInput.handlerFileDialog(filename, filedir);
		}

		// restore old value of frame setting
		this.fileInput.uploadParams["frameFiles"] = frameFilesOldValue;
	},

	onAgentChange: function(ctrlImage)
	{
		ctrlImage.value = this.getImages().join(',');
		BX.onCustomEvent(ctrlImage, 'onSettingChangeValue');
	},

	onLoadSetting: function(event)
	{
		//change multi or single mode of control by values from field in tool
		this.isMultiImage = !!event.params.multi;
	},

	onLink: function(data)
	{
		var ctrl = data.ctrl;
		var code = data.code;

		var ctrlImage = ctrl;
		BX.addCustomEvent(ctrlImage, "onSettingLoadValue", BX.delegate(function()
		{
			var pathList = [];
			var pathListTmp = ctrlImage.value.split(',');
			for(var i in pathListTmp)
			{
				var pathListClean = pathListTmp[i].split('?');
				pathList.push(pathListClean[0]);

			}

			this.setImages(pathList);
		}, this));


		BX.addCustomEvent(this.fileInput.agent, "onAttachFiles", BX.delegate(function()
		{
			if(!this.isMultiImage)
			{
				this.semaphoreOnQueueIsChanged = true;
				this.fileInput.deleteFiles();
			}
		}, this));


		BX.addCustomEvent(this.fileInput.agent, "onQueueIsChanged", BX.delegate(function()
		{
			if(!this.semaphoreOnQueueIsChanged)
			{
				this.onAgentChange(ctrlImage);
			}
			this.semaphoreOnQueueIsChanged = false;
		}, this));


		this.onFileIsCreated = function(id, item)
		{
			this.semaphoreOnQueueIsChanged = true;
			if(item.file['tmp_url'])
			{
				this.fileList.push({
					'id': id,
					'url': item.file['tmp_url'],
					'path': item.file['tmp_url']
				});
			}

			if(BX.util.in_array(item['type'], ['image/filedialog', 'image/medialib']))
			{
				BX.addCustomEvent(this.fileInput.agent, "onFileIsInited", BX.delegate(function()
				{
					this.onAgentChange(ctrlImage);
				}, this));
			}

			BX.addCustomEvent(item, 'onUploadDone', BX.delegate(function(item, data)
			{
				var url = data.file.files.default.url;
				url += (url.indexOf('?') > 0 ? '&' : '?') + 'r=' + Math.random();

				this.fileList.push({
					'id': data.file.id,
					'url': url,
					'path': data.file.files.default.path
				});
				this.onAgentChange(ctrlImage);
			}, this));
		};
		BX.addCustomEvent(this.fileInput.agent, "onFileIsCreated", BX.delegate(this.onFileIsCreated, this));

		data.bind = false;
	},

	onSave: function()
	{
		var isNeedToDoRequest = false;

		var fileContainer = BX.findChildByClassName(this.context, 'adm-fileinput-area-container', true);
		var inputList = fileContainer.querySelectorAll('input[name^="NEW_FILE_EDITOR"]');
		var postData = {};
		for(var i in inputList)
		{
			if(inputList[i] && inputList[i].getAttribute)
			{
				var paramName = inputList[i].getAttribute('name');
				var paramValue = inputList[i].value;
				postData[inputList[i].getAttribute('name')] = paramValue;

				if(paramName.indexOf('tmp_name') > -1)
				{

					var isSaved = this.fileList.filter(function (file) {
						return (paramValue == file.path && file.saved);
					}).length > 0;

					if (!isSaved)
					{
						isNeedToDoRequest = true;
					}
				}
			}
		}

		if(!isNeedToDoRequest)
		{
			return;
		}

		BX.ajax({
			'url': this.caller.saveFileUrl,
			'method': 'POST',
			'data': postData,
			'dataType': 'json',
			'async': false,
			'onsuccess': BX.delegate(this.onImagesSaved, this)
		});
	},

	onImagesSaved: function(answer)
	{
		for(var tabCode in this.caller.tabList)
		{
			var item = this.caller.tabList[tabCode].items['src'];
			if(!item)
			{
				continue;
			}

			if(!item.ctrl.value)
			{
				continue;
			}

			answer.data.list.forEach(function (savedFileData) {
				var filtered = this.fileList.filter(function (file) {
					if (!file.path && file.url)
					{
						return file.url.indexOf(savedFileData.tmp) >= 0;
					}

					return savedFileData.tmp == file.path;
				});
				
				filtered.forEach(function (file) {
					item.ctrl.value = item.ctrl.value.replace(file.url, savedFileData.path);
					file.saved = true;
				});
			}, this);

			BX.onCustomEvent(item.ctrl, 'onSettingChangeValue');
		}

		if (answer.error)
		{
			alert(answer.errorText);
		}
	}
};

function BXBlockEditorEditDialogColumn(params)
{
	this.attributeColumnNum = 'data-bx-editor-column-number';
	this.attributeColumnValue = 'data-bx-editor-column-value';
	this.attributeColumnCount = 'data-bx-editor-column-count';

	this.caller = params.caller;

	// init column switcher
	BX.addCustomEvent(this.caller, 'onLinkControl', BX.delegate(this.onLinkControl, this));
	BX.addCustomEvent(this.caller, 'loadSetting', BX.delegate(this.onLoadSetting, this));
}
BXBlockEditorEditDialogColumn.prototype =
{
	getColumnList: function(container)
	{
		return BX.findChildren(container, {attribute: this.attributeColumnNum}, true);
	},

	bindOnSwitch: function(ctrl, columnNum)
	{
		BX.bind(columnNum, 'click', BX.delegate(function(){
			var columnNumValue = columnNum.getAttribute(this.attributeColumnNum);
			ctrl.setAttribute(this.attributeColumnValue, columnNumValue);

			var container = this.caller.getCtrlContainer(ctrl);
			var columnList = this.getColumnList(container.container);
			for(var i in columnList)
			{
				BX.removeClass(columnList[i], 'active');
			}
			BX.addClass(columnNum, 'active');


			var value = this.caller.caller.getCurrentEditingBlock().getEditValue(container.code, columnNumValue);
			this.caller.setCtrlValue(ctrl, value);

		}, this));
	},

	onSettingDependenceChange: function(eventParams, eventParamsDependence)
	{
		var container = eventParamsDependence.container.container;
		var value = eventParams.value ? eventParams.value : 1;
		value = parseInt(value);
		if(value < 2)
		{
			value = 0;
		}

		var columnList = this.getColumnList(container);
		var counter = 0;
		for(var i in columnList)
		{
			counter++;
			var displayMode = (counter <= value) ? '' : 'none';
			var column = columnList[i];
			column.style.display = displayMode;
		}

		this.switchDefaultColumn(container);
	},

	switchDefaultColumn: function(container)
	{
		var columnNumList = this.getColumnList(container);
		if(columnNumList.length > 0)
		{
			BX.fireEvent(columnNumList[0], 'click');
		}
	},

	onLoadSetting: function(eventParams)
	{
		this.switchDefaultColumn(eventParams.container.container);
	},

	onLinkControl: function(eventParams)
	{
		// init every column button
		var columnNumList = this.getColumnList(eventParams.container);
		for(var j in columnNumList)
		{
			this.bindOnSwitch(eventParams.ctrl, columnNumList[j]);
		}

		if(columnNumList.length > 0)
		{
			// listen content change
			BX.addCustomEvent(
				this.caller,
				'onSettingDependenceChange-' + eventParams.code,
				BX.delegate(this.onSettingDependenceChange, this)
			);
		}
	}
};

function BXBlockEditorSocial(params)
{
	this.caller = params.caller;

	BX.addCustomEvent(this.caller, 'onLinkControl-social_content', BX.delegate(this.onLinkControl, this));
	BX.addCustomEvent(this.caller, 'loadSetting-social_content', BX.delegate(this.onLoadSetting, this));
}
BXBlockEditorSocial.prototype =
{
	onLoadSetting: function(eventParams)
	{
		this.inputControl = eventParams.ctrl;
		BX.cleanNode(this.itemContainer);

		var valueList = JSON.parse(eventParams.value);
		for(var i in valueList)
		{
			var item = valueList[i];
			this.addItem(item.href, item.name);
		}
	},

	fireChange: function()
	{
		this.inputControl.value = JSON.stringify(this.getValue());
		BX.onCustomEvent(this.inputControl, 'onSettingChangeValue');
	},

	getValue: function()
	{
		var result = [];
		var itemList = this.getItemList();
		for(var i in itemList)
		{
			var item = itemList[i];
			var href = this.getItemControl(item, 'href');
			var name = this.getItemControl(item, 'name');
			result.push({'name': name.value, 'href': href.value});
		}

		return result;
	},

	getItemList: function()
	{
		return BX.findChildren(this.itemContainer, {attribute:'data-bx-block-editor-social-item'}, true);
	},

	getItem: function(control)
	{
		return BX.findParent(control, {attribute:'data-bx-block-editor-social-item'});
	},

	getItemControl: function(item, className)
	{
		return BX.findChildByClassName(item, className, true);
	},

	removeItem: function(control)
	{
		BX.remove(this.getItem(control));
	},

	changePreset: function(elementSelect)
	{
		var item = this.getItem(elementSelect);

		var href = this.getItemControl(item, 'href');
		var name = this.getItemControl(item, 'name');

		href.value = elementSelect.value;
		name.value = elementSelect.options[elementSelect.selectedIndex].text;
	},

	addItem: function(href, name)
	{
		var html = this.templateItem.innerHTML;
		html = html.replace('#href#', href);
		html = html.replace('#name#', name);

		var div = BX.create('div', {
			'attrs': {'data-bx-block-editor-social-item': 'item'},
			'html': html
		});

		var deleteButton = this.getItemControl(div, 'delete');
		if(this.getValue().length == 0)
		{
			deleteButton.style.display = 'none';
		}

		var changeButton = this.getItemControl(div, 'preset');
		var nameInput = this.getItemControl(div, 'name');
		var hrefInput = this.getItemControl(div, 'href');

		var _this = this;
		BX.bind(nameInput, 'bxchange', function(){
			_this.fireChange();
		});
		BX.bind(hrefInput, 'bxchange', function(){
			_this.fireChange();
		});

		BX.bind(deleteButton, 'click', function(){
			_this.removeItem(this);
			_this.fireChange();
		});
		BX.bind(changeButton, 'change', function(){
			_this.changePreset(this);
			_this.fireChange();
		});


		this.itemContainer.appendChild(div);
		this.fireChange();
	},

	onLinkControl: function(eventParams)
	{
		this.container = eventParams.container;
		this.templateItem = BX('template-social-item');

		this.itemContainer = BX.findChildByClassName(this.container, 'container', true);
		var addButton = BX.findChildByClassName(this.container, 'add', true);

		var _this = this;
		BX.bind(addButton, 'click', function(){
			_this.addItem('', '');
		});
	}
};

function BXBlockEditorEditDialog(params)
{
	this.changeList = [];
	this.tabList = {};
	this.ctrlList = {};
	this.itemFilter = [];
	this.itemPrevValues = [];
	this.htmlEditorParsedPhp = [];

	this.doNotProcessChanging = false;
	this.init(params);
}
BXBlockEditorEditDialog.prototype =
{
	getPlaceCaption: function(code)
	{
		var caption = BX.message('BLOCK_EDITOR_PLACE_CAPTION_' + code.toUpperCase());
		if(!caption)
		{
			caption = code;
		}

		return caption;
	},

	init: function(params)
	{
		this.attributePlaceName = 'data-bx-place-name';
		this.attributeTab = 'data-bx-block-editor-settings-tab';
		this.attributeLink = 'data-bx-editor-tool';
		this.attributeCtrl = 'data-bx-editor-tool-input';

		this.caller = params.caller;
		this.callerContext = params.context;
		this.saveFileUrl = params.saveFileUrl;
		this.context = this.callerContext.querySelector('.edit-panel');

		this.contextTools = this.context.querySelector('.block-edit-cont');

		this.contextVisual = this.context.querySelector('.visual-part');
		this.contextPanel = this.context.querySelector('.dialog-part');
		this.contextPlaceList = this.contextPanel.querySelector('.edit-panel-tabs-style');

		this.buttonSave = BX.findChild(this.contextTools, {className: 'bx-editor-block-tools-close'}, true);
		this.buttonClose = BX.findChild(this.contextTools, {className: 'bx-editor-block-tools-cancel'}, true);
		BX.bind(this.buttonSave, 'click', BX.delegate(this.save, this));
		BX.bind(this.buttonClose, 'click', BX.delegate(this.cancel, this));

		this.helper =  new BXBlockEditorHelper();
		this.fileInput = new BXBlockEditorDialogFileInput({'caller': this, 'id': 'BX_BLOCK_EDITOR_SRC_' + this.caller.id});
		this.column = new BXBlockEditorEditDialogColumn({'caller': this});
		this.social = new BXBlockEditorSocial({'caller': this});
		this.initHtmlEditor();
		this.initColorPicker();
		this.initBorderControl();

		this.initTabs();
		this.initPlaceList();
		this.initTools();
	},

	initPlaceList: function()
	{
		BX.addCustomEvent(this.caller, 'onLoadAfter', BX.delegate(this.onEditorLoadAfter, this));
	},

	onEditorLoadAfter: function(url, callback, editor)
	{
		var placeContainer = BX.findChild(this.contextPlaceList, {attribute: this.attributePlaceName}, true);
		if(!placeContainer)
		{
			return;
		}

		BX.cleanNode(placeContainer);

		var placeBind = (function(){
			return function(place, placeCode)
			{
				var _this = this;
				BX.bind(place, 'click', function ()
				{
					BX.onCustomEvent(_this, 'onPlaceEdit', [placeCode]);
				});
				BX.bind(place, 'mouseenter', function ()
				{
					BX.onCustomEvent(_this, 'onPlaceHover', [placeCode, true]);
				});
				BX.bind(place, 'mouseleave', function ()
				{
					BX.onCustomEvent(_this, 'onPlaceHover', [placeCode, false]);
				});
			};
		})();

		var placeList = editor.findStylistPlaces();
		for(var placeCode in placeList)
		{
			var placeName = this.getPlaceCaption(placeCode);
			var li = BX.create('li', {'text': placeName, 'attrs': {'title': placeName}});
			placeContainer.appendChild(li);
			placeBind.apply(this, [li, placeCode]);
		}
	},

	initTabs: function()
	{
		var tabList = BX.findChildren(BX.findChildByClassName(this.contextTools, 'block-edit-tabs'), {attribute: this.attributeTab}, true);
		for(var i in tabList)
		{
			var tab = tabList[i];
			var tabCode = tab.getAttribute(this.attributeTab);

			this.tabList[tabCode] = {'node': tab, 'items': {}};
			BX.bind(tab, 'click', BX.delegate(this.onTabClick, this));
		}
	},


	initTools: function()
	{
		var linkList = BX.findChildren(this.context, {attribute: this.attributeLink}, true);
		for(var i in linkList)
		{
			var link = linkList[i];
			var linkCode = link.getAttribute(this.attributeLink);
			var linkCodeList = linkCode.split(':');
			var ctrl = BX.findChild(link, {attribute: this.attributeCtrl}, true);

			if(!ctrl)
			{
				continue;
			}

			// fill tab 2 ctrl info
			this.tabList[linkCodeList[0]]['items'][linkCodeList[1]] = {'node': link, 'ctrl': ctrl};

			// fire events
			var eventParams = {
				'code': linkCodeList[1],
				'group': linkCodeList[0],
				'ctrl': ctrl,
				'container': link,
				'bind': true
			};
			BX.onCustomEvent(this, 'onLinkControl', [eventParams]);
			BX.onCustomEvent(this, 'onLinkControl-' + eventParams.code, [eventParams]);
			this.ctrlList[eventParams.code] = eventParams.ctrl;
			this.linkControl(eventParams.ctrl, eventParams.code, eventParams.bind);
		}
	},

	initHtmlEditor: function()
	{
		this.htmlEditor = BXHtmlEditor.Get('BX_BLOCK_EDITOR_CONTENT_' + this.caller.id);
		//var width = 420;
		var minWidth = 400;
		this.htmlEditor.MIN_WIDTH = minWidth;
		//this.htmlEditor.NORMAL_WIDTH = width;
		//this.htmlEditor.config.width = width;
		//this.htmlEditor.normalWidth = width;

		this.htmlEditor.ResizeSceleton();
		this.htmlEditor.AutoResizeSceleton();

		BX.addCustomEvent(this, 'onLinkControl-content', BX.delegate(function(data){
			var ctrl = data.ctrl;
			var code = data.code;

			var ctrlEditor = ctrl;

			BX.addCustomEvent(this.htmlEditor, "OnContentChanged", BX.delegate(function(rawHtml){
				ctrlEditor.value = this.phpParser.replacePhpByLayout(rawHtml);
				BX.onCustomEvent(ctrlEditor, 'onSettingChangeValue');
			}, this));

			BX.addCustomEvent(ctrlEditor, "onSettingLoadValue", BX.delegate(function(){
				var text = this.phpParser.replaceLayoutByPhp(ctrlEditor.value);
				this.htmlEditor.SetContent(text, true);
			}, this));

			data.bind = false;
		}, this));
	},

	initColorPicker: function()
	{
		this.picker = new BX.ColorPicker({'popupOptions': {
			'offsetLeft': 15,
			'offsetTop': 5
		}});

		BX.addCustomEvent(this, "onHide", this.picker.close.bind(this.picker));

		var changeHandler = function(inputNode, iconNode)
		{
			var color = inputNode.value;
			if (!color)
			{
				return;
			}

			iconNode.style.background = inputNode.value;
		};

		var clickHandler = function (inputNode, iconNode)
		{
			if (!this.picker)
			{
				return;
			}

			this.picker.close();
			this.picker.open({
				'defaultColor': '',
				'allowCustomColor': true,
				'bindElement': iconNode,
				'onColorSelected': function (color) {
					inputNode.value = color;
					BX.fireEvent(inputNode, 'change');
				}
			});
		};

		var inputs = this.context.querySelectorAll('.bx-editor-color-picker');
		inputs = BX.convert.nodeListToArray(inputs);
		inputs.forEach(function (inputNode) {
			var iconNode = BX.nextSibling(inputNode);
			var textNode = BX.nextSibling(iconNode);

			var onClick = clickHandler.bind(this, inputNode, iconNode);
			var onChange = changeHandler.bind(this, inputNode, iconNode);
			BX.bind(iconNode, 'click', onClick);
			BX.bind(textNode, 'click', onClick);

			BX.addCustomEvent(inputNode, "onSettingLoadValue", onChange);
			BX.bind(inputNode, "bxchange", onChange);
		}, this);
	},

	initBorderControl: function()
	{
		var ctrl = BX('block_editor_style_border');
		var ctrlStyle = BX('block_editor_style_border_style');
		var ctrlWidth = BX('block_editor_style_border_width');
		var ctrlColor = BX('block_editor_style_border_color');

		var changeVisualHandler = function(){
			var val = '';
			if(ctrlStyle.value)
			{
				if(!ctrlWidth.value)
				{
					ctrlWidth.value = '2px';
				}
				if(!ctrlColor.value)
				{
					ctrlColor.value = '#000000';
				}

				val = ctrlWidth.value + ' ' + ctrlStyle.value  + ' ' + ctrlColor.value;
			}
			ctrl.value = val;
			BX.fireEvent(ctrl, 'change');
		};

		BX.addCustomEvent(ctrl, "onSettingLoadValue", BX.delegate(function()
		{
			if(ctrl.value)
			{
				var arr = ctrl.value.split(' ');
				for(var i = 3; i < arr.length; i++)
				{
					arr[2] = arr[2] + ' ' + arr[i];
				}
				ctrlWidth.value = arr[0];
				ctrlStyle.value = arr[1];
				ctrlColor.value = this.helper.colorRgbToHex(arr[2]);
				BX.fireEvent(ctrlColor, 'change');
			}
		}, this));
		BX.bind(ctrlStyle, 'bxchange', changeVisualHandler);
		BX.bind(ctrlWidth, 'bxchange', changeVisualHandler);
		BX.bind(ctrlColor, 'bxchange', changeVisualHandler);
	},

	getCtrlContainer: function(node)
	{
		var parentNode = BX.findParent(node, {'attribute': this.attributeLink});
		var linkCode = parentNode.getAttribute(this.attributeLink);
		var linkCodeList = linkCode.split(':');

		return {'container': parentNode, 'tab': linkCodeList[0], 'code': linkCodeList[1]};
	},

	setCtrlValue: function(ctrl, value)
	{
		ctrl.value = value;
		if(typeof(ctrl.oldValue) === "undefined")
		{
			ctrl.oldValue = ctrl.value;
		}

		BX.onCustomEvent(ctrl, 'onSettingLoadValue');
	},

	getCtrlValue: function(ctrl)
	{
		return ctrl.value;
	},

	onControlChangeValue: function(node)
	{
		if(!this.caller.getCurrentEditingBlock())
		{
			return;
		}

		var container = this.getCtrlContainer(node);
		var key = container.code;
		var value = node.value;
		var columnNum = node.getAttribute(this.column.attributeColumnValue);

		BX.onCustomEvent(this, 'controlChangeValue', [key, value, columnNum]);

		if(!BX.util.in_array(node, this.changeList))
		{
			this.changeList.push(node);
		}

		this.fireDependenceChange(key);
	},

	linkControl: function(ctrl, code, bindChange)
	{
		var _this = this;
		BX.addCustomEvent(ctrl, 'onSettingChangeValue', function(){
			if(!_this.doNotProcessChanging)
			{
				_this.onControlChangeValue(this);
			}
		});

		if(bindChange)
		{
			var func = function(){
				BX.onCustomEvent(this, 'onSettingChangeValue');
			};
			BX.bind(ctrl, 'change', func);
			if(ctrl.nodeName == 'INPUT' || ctrl.nodeName == 'TEXTAREA')
			{
				BX.bind(ctrl, 'input', func);
			}
		}
	},

	onTabClick: function(e)
	{
		e = e || window.event;
		var tab = e.target;
		var tabCode = tab.getAttribute(this.attributeTab);

		this.showTab(tabCode);
	},

	fireDependenceChange: function(code)
	{
		if(!this.itemDependence || !this.itemDependence[code] || this.itemDependence[code].length == 0)
		{
			return;
		}

		var ctrl = this.ctrlList[code];
		if(!ctrl) return;

		var container = this.getCtrlContainer(ctrl);
		var eventParams = {
			code: code,
			value: this.getCtrlValue(ctrl),
			ctrl: ctrl,
			container: container
		};

		for(var j in this.itemDependence[code])
		{
			var dependenceCode = this.itemDependence[code][j];
			var dependenceCtrl = this.ctrlList[dependenceCode];
			if(!dependenceCtrl) continue;

			var dependenceContainer = this.getCtrlContainer(dependenceCtrl);
			var eventParamsDependence = {
				code: dependenceCode,
				value: this.getCtrlValue(dependenceCtrl),
				ctrl: dependenceCtrl,
				container: dependenceContainer
			};

			BX.onCustomEvent(this, 'onSettingDependenceChange-' + dependenceCode, [eventParams, eventParamsDependence]);
		}
	},

	load: function(itemList)
	{
		this.doNotProcessChanging = true;

		this.itemFilter = [];
		this.itemDependence = {};
		this.itemPrevValues = [];

		for(var i in itemList)
		{
			var code = itemList[i].code;
			var value = itemList[i].value;
			var params = itemList[i].params;
			var dependence = itemList[i].dependence;
			var ctrl = this.ctrlList[code];

			if(!ctrl)
				continue;

			var container = this.getCtrlContainer(ctrl);

			//set filter for showing controls
			this.itemFilter.push(code);
			this.itemDependence[code] = dependence;

			var eventParams = {
				code: code,
				value: value,
				params: params,
				ctrl: ctrl,
				container: container,
				dependence: dependence
			};

			BX.onCustomEvent(this, 'loadSetting', [eventParams]);
			BX.onCustomEvent(this, 'loadSetting-' + code, [eventParams]);

			//set values to controls
			ctrl.value = value;
			ctrl.oldValue = ctrl.value;
			BX.onCustomEvent(ctrl, 'onSettingLoadValue');

			this.fireDependenceChange(code);
		}

		BX.onCustomEvent(this, 'loadSettings', [itemList]);

		this.doNotProcessChanging = false;

		for(var tabCode in this.tabList)
		{
			var tabExists = false;
			for(var itemCode in this.tabList[tabCode].items)
			{
				if(BX.util.in_array(itemCode, this.itemFilter))
				{
					tabExists = true;
					break;
				}
			}

			if(tabExists)
			{
				this.tabList[tabCode].node.style.display = '';
			}
			else
			{
				this.tabList[tabCode].node.style.display = 'none';
			}
		}

		this.showTab();
	},

	save: function(callbackFunction)
	{
		var wait = BX.showWait(this.buttonSave);
		var hasChanges = this.changeList.length > 0;
		BX.onCustomEvent(this, 'onSave', [hasChanges]);

		BX.closeWait(this.buttonSave);
		this.hide(callbackFunction);
	},

	cancel: function(callbackFunction)
	{
		for(var i in this.changeList)
		{
			var node = this.changeList[i];
			node.value = node.oldValue;
			this.onControlChangeValue(node);
		}

		var hasChanges = false;
		BX.onCustomEvent(this, 'onCancel', [hasChanges]);
		this.hide(callbackFunction);
	},

	show: function()
	{
		BX.onCustomEvent(this, 'onShow');

		var contextTools = this.contextTools;
		var contextVisual = this.contextVisual;
		var context = this.context;

		contextTools.style.display = 'block';
		if(contextTools.offsetWidth + contextVisual.offsetWidth > context.offsetWidth)
		{
			contextVisual.style.width = (context.offsetWidth - contextTools.offsetWidth) + 'px';
		}
		contextTools.style.right = '0';
		contextTools.style.visibility = 'visible';
	},

	hide: function(callbackFunction)
	{
		BX.onCustomEvent(this, 'onHide');

		var contextTools = this.contextTools;
		var contextVisual = this.contextVisual;

		if(this.contextTools.style.display == 'none')
		{
			if (callbackFunction && BX.type.isFunction(callbackFunction))
			{
				callbackFunction.apply();
			}
		}
		else
		{
			if (callbackFunction && BX.type.isFunction(callbackFunction))
			{
				contextTools.style.right = "-10%";
				setTimeout(callbackFunction, 200);
			}
			else
			{
				contextTools.style.right = "-100%";
				contextTools.style.display = 'block';
				contextTools.style.visibility = 'hidden';
				contextVisual.style.width = '100%';
			}
		}
	},

	hideTab: function()
	{
		for(var tabCode in this.tabList)
		{
			for(var itemCode in this.tabList[tabCode]['items'])
			{
				this.tabList[tabCode]['items'][itemCode].node.style.display = 'none';
			}
		}
	},

	showTab: function(code)
	{
		this.hideTab();

		// unhighlight tabs
		for(var tv in this.tabList)
		{
			BX.removeClass(this.tabList[tv]['node'], 'active');
		}

		// determine first available tab
		if(!code)
		{
			var availableCodeList = [];
			for(var codeCurr in this.tabList)
			{
				if(this.tabList[codeCurr].node.style.display != 'none')
				{
					availableCodeList.push(codeCurr);
				}
			}

			for(var i in this.tabList)
			{
				if(BX.util.in_array(i, availableCodeList))
				{
					code = i;
					break;
				}
			}
		}

		var emptyTab = BX.findChildByClassName(this.context, 'block-edit-form-empty', true);
		if(!code)
		{
			BX.addClass(emptyTab, 'active');
			return;
		}
		else
		{
			BX.removeClass(emptyTab, 'active');
		}

		// highlight active tab
		BX.addClass(this.tabList[code]['node'], 'active');

		// show tools on active tab
		for(var itemCode in this.tabList[code]['items'])
		{
			if(this.itemFilter.length > 0 && !BX.util.in_array(itemCode, this.itemFilter))
			{
				continue;
			}

			this.tabList[code]['items'][itemCode].node.style.display = 'block';
		}
	}
};

function BXBlockEditorSliceContent(params)
{
	this.sectionId = 'BX_BLOCK_EDITOR_EDITABLE_SECTION';
	this.textarea = params.textarea;
	this.caller = params.caller;
}
BXBlockEditorSliceContent.prototype.getSectionHtml = function(path, content)
{
	var tag = [this.sectionId].concat(path).join('/') + '/';
	return '<!--START %TAG%-->\n%CONTENT%\n<!--END %TAG%-->\n'.replace('%TAG%', tag).replace('%TAG%', tag).replace('%CONTENT%', content);
};
BXBlockEditorSliceContent.prototype.getSlices = function()
{
	var result = {};
	var content = this.textarea.value;
	var pattern = "<!--START "
		+ this.sectionId + "\/([\\w]+?)\/([\\w]+?)\/-->"
		+ "([\\s\\S,\\n]*?)"
		+ "<!--END " + this.sectionId + "[\/\\w]+?-->";

	var sliceRegExp = new RegExp(pattern, "g");
	var matches;
	while(matches = sliceRegExp.exec(content))
	{
		var section = matches[1].trim();
		var item = matches[2].trim();
		var value = matches[3].trim();
		if(!BX.type.isArray(result[section]))
		{
			result[section] = [];
		}

		result[section].push({
			'section': section,
			'item': item,
			'value': value
		});
	}

	return result;
};
BXBlockEditorSliceContent.prototype.getHtml = function(sliceList)
{
	var content = '';
	this.caller.helper.each(sliceList, function(sectionSliceList)
	{
		this.caller.helper.each(sectionSliceList, function(slice)
		{
			content = content + this.getSectionHtml(
				[slice.section, slice.item],
				slice.value
			);
		}, this);
	}, this);

	return content;
};

function BXBlockEditorCssParser()
{
	this.patternRule = "([\\s\\S]*?){([\\s\\S]*?)}";
	this.patternDeclaration = "([\\s\\S]+?):([\\s\\S]+?);";
	this.patternStyleSheet = "<style[\\s\\S]*?>([\\s\\S]*?)<\/style>";
	this.patternComments = '(\\/\\*[\\s\\S]*?\\*\\/)';
	this.patternMedia = '(@media[\\s\\S]*?){([}\\s\\S]*?)}[\\r\\n ]*?}';
}
BXBlockEditorCssParser.prototype.parseRules = function(styles)
{
	var result = {};
	var matches, selector, value;

	var stylesRegExp = new RegExp(this.patternRule, "g");
	while(matches = stylesRegExp.exec(styles))
	{
		selector = matches[1].trim();
		value = matches[2].trim();

		if(!result[selector])
		{
			result[selector] = {};
		}
		result[selector] = BX.merge(result[selector], this.parseDeclaration(value));
	}

	return result;
};

BXBlockEditorCssParser.prototype.parseDeclaration = function(declaration)
{
	var result = {};

	var stylesRegExp = new RegExp(this.patternDeclaration, "g");
	var matches, name, value;
	while(matches = stylesRegExp.exec(declaration))
	{
		name = matches[1].trim();
		value = matches[2].trim();
		result[name] = value;
	}

	return result;
};
BXBlockEditorCssParser.prototype.mergeStyles = function(arr1, arr2)
{
	for(var selector in arr2)
	{
		if(!arr2[selector])
		{
			continue;
		}

		if(!arr1[selector])
		{
			arr1[selector] = {};
		}

		arr1[selector] = BX.merge(arr1[selector], arr2[selector]);
	}

	return arr1;
};
BXBlockEditorCssParser.prototype.diffStylesAll = function(arr1, arr2)
{
	var arr = {};
	var diff;

	for(var media in arr2)
	{
		if(!arr2[media])
		{
			continue;
		}

		if(!arr1[media])
		{
			arr[media] = arr2[media];
		}
		else
		{
			diff = this.diffStyles(arr1[media], arr2[media]);
			if(BX.util.object_keys(diff).length > 0)
			{
				arr[media] = diff;
			}
		}
	}
	
	if(BX.util.object_keys(arr).length > 0)
	{
		return arr;
	}
	else
	{
		return null
	}
};
BXBlockEditorCssParser.prototype.diffStyles = function(arr1, arr2)
{
	var arr = {};

	for(var selector in arr2)
	{
		if(!arr2[selector])
		{
			continue;
		}

		var declarations = arr2[selector];

		if(!arr1[selector] && BX.util.object_keys(declarations).length > 0)
		{
			arr[selector] = declarations;
		}
		else if(!BX.type.isString(declarations))
		{
			for(var rule in declarations)
			{
				if(!declarations[rule])
				{
					continue;
				}

				if(!arr1[selector][rule] || arr1[selector][rule] != declarations[rule])
				{
					if(!arr[selector])
					{
						arr[selector] = {};
					}

					arr[selector][rule] = declarations[rule];
				}
			}
		}

	}

	return arr;
};
BXBlockEditorCssParser.prototype.parseTag = function(str)
{
	var result = '';

	var parseRegExp = new RegExp(this.patternStyleSheet, "gi");
	var commentsRegExp = new RegExp(this.patternComments, "g");

	if(!(matches = parseRegExp.exec(str)))
	{
		matches = [str, str];
	}
	do
	{
		result += matches[1].trim() + "\n";
	}while(matches = parseRegExp.exec(str));

	result = result.replace(commentsRegExp, '');

	return result;
};
BXBlockEditorCssParser.prototype.parse = function(str)
{
	str = str || '';
	var result = {'': {}};
	var matches;

	var mediaRegExp = new RegExp(this.patternMedia, "gi");

	var css = this.parseTag(str);
	css = css.trim();
	while(matches = mediaRegExp.exec(css))
	{
		var media = matches[1].trim();
		var styles = matches[2].trim() + '}';
		if(!result[media])
		{
			result[media] = {};
		}

		result[media] = this.mergeStyles(result[media], this.parseRules(styles));
		css = css.replace(matches[0], '');
	}

	result[''] = this.mergeStyles(result[''], this.parseRules(css));

	return result;
};
BXBlockEditorCssParser.prototype.getCssString = function(parsed)
{
	var result = '', media, selector, declaration, value;

	for(media in parsed)
	{
		if(!parsed[media]) break;

		if(media)
		{
			result += media + "{\n";
		}

		for(selector in parsed[media])
		{
			if(!selector || !parsed[media][selector]) break;

			result += selector + "{\n";
			for(declaration in parsed[media][selector])
			{
				if(!declaration || !parsed[media][selector][declaration]) break;

				value = parsed[media][selector][declaration];
				result += declaration + ": " + value + ";\n";
			}

			result += "}\n";
		}

		if(media)
		{
			result += "}\n";
		}
	}

	return result;
};
BXBlockEditorCssParser.prototype.setStyle = function(str, media, selector, list)
{
	var parseResult, changeResult;

	media = media || '';
	selector = selector.trim();
	parseResult = this.parse(str);

	if(!parseResult[media])
	{
		changeResult = {};
	}
	else
	{
		changeResult = parseResult[media];
	}

	for(var declaration in list)
	{
		var value = list[declaration];
		if(value)
		{
			if(!changeResult[selector])
			{
				changeResult[selector] = {};
			}
			changeResult[selector][declaration] = value;
		}
		else if(changeResult[selector])
		{
			delete changeResult[selector][declaration];
		}
	}

	parseResult[media] = changeResult;
	return this.getCssString(parseResult);
};
BXBlockEditorCssParser.prototype.getStyle = function(str, media, selector, list)
{
	var result = {}, parseResult, changeResult;

	media = media || '';
	selector = selector.trim();
	parseResult = this.parse(str);

	if(!parseResult[media])
	{
		changeResult = {};
	}
	else
	{
		changeResult = parseResult[media];
	}

	for(var i in list)
	{
		var declaration = list[i];
		if(!changeResult[selector] || !changeResult[selector][declaration])
		{
			result[declaration] = null;
		}
		else
		{
			result[declaration] = changeResult[selector][declaration];
		}
	}

	return result;
};

function BXBlockEditorStatusManager(params)
{
	this.caller = params.caller;

	BX.addCustomEvent(this.caller, 'onPlaceInitBlocksContent', BX.delegate(this.onPlaceInitBlocksContent, this.caller));

	BX.addCustomEvent(this.caller, 'onBlockCreateAfter', BX.delegate(this.setBlockStatusContent, this.caller));
	BX.addCustomEvent(this.caller, 'onBlockMoveAfter', BX.delegate(this.setBlockStatusContent, this.caller));
	BX.addCustomEvent(this.caller, 'onBlockRemoveAfter', BX.delegate(this.onBlockRemoveAfter, this));
	BX.addCustomEvent(this.caller, 'onBlockClone', BX.delegate(this.setBlockStatusContent, this.caller));
	BX.addCustomEvent(this.caller, 'onBlockEditEnd', BX.delegate(this.onBlockEditEnd, this.caller));
}
BXBlockEditorStatusManager.prototype.getPlaceNameList = function(nodeList)
{
	var changedPlaceList = [];
	this.caller.helper.each(nodeList, function(node)
	{
		if(!node.getAttribute)
		{
			return;
		}

		var status = node.getAttribute(this.CONST_ATTR_BLOCK_STATUS);
		var placeName = node.parentNode.getAttribute(this.CONST_ATTR_PLACE);
		if(status != 'loaded' || placeName == 'body')
		{
			changedPlaceList.push(placeName);
		}
	}, this.caller);

	return changedPlaceList;
};
BXBlockEditorStatusManager.prototype.setBlockStatusContent = function(block)
{
	block.node.setAttribute(this.CONST_ATTR_BLOCK_STATUS, 'content');
};
BXBlockEditorStatusManager.prototype.onBlockRemoveAfter = function(placeNode)
{
	this.onPlaceInitBlocksContent.call(this.caller, placeNode, true);
};
BXBlockEditorStatusManager.prototype.onBlockEditEnd = function(block, hasChanges)
{
	if(hasChanges)
	{
		block.node.setAttribute(this.CONST_ATTR_BLOCK_STATUS, 'content');
	}
};
BXBlockEditorStatusManager.prototype.onPlaceInitBlocksContent = function(placeNode, hasBlocksFromContent)
{
	// set loaded status to blocks
	var status = 'loaded';
	if(hasBlocksFromContent)
	{
		// set content status to blocks
		status = 'content';
	}

	var blockNodeList = BX.findChildren(placeNode, {'attribute': this.CONST_ATTR_BLOCK}, true);
	for(var i in blockNodeList)
	{
		var blockNode = blockNodeList[i];
		blockNode.setAttribute(this.CONST_ATTR_BLOCK_STATUS, status);
	}

};

BX.addCustomEvent('GetControlsMap', function(controlsMap){
	if(this.id.indexOf('BX_BLOCK_EDITOR_CONTENT_') !== 0)
	{
		return
	}

	var excludeControlList = [
		'ChangeView', 'placeholder_selector',
		'StyleSelector', 'FontSelector', 'FontSize',
		'RemoveFormat', 'Bold', 'AlignList', 'Color',
		'Fullscreen', 'More'
	];
	for(var i in controlsMap)
	{
		var control = controlsMap[i];
		if(control)
		{
			if(control.separator)
			{
				control.hidden = true;
			}
			else if(!BX.util.in_array(control.id, excludeControlList))
			{
				control.compact = false;
			}
			else
			{
				control.compact = true;
			}
		}
	}
});

(function (window)
{

	function Content(params)
	{
		this.textarea = params.textarea;
		this.caller = params.caller;

		var content = this.getRaw();
		this.converter = new SliceConverter();

		/*
		if (!content || JsonConverter.isValid(content))
		{
			this.converter = new JsonConverter();
		}
		else
		{
			this.converter = new SliceConverter();
		}
		*/
	}
	Content.prototype.getRaw = function()
	{
		return this.textarea.value;
	};
	Content.prototype.setRaw = function(value)
	{
		this.textarea.value = value;
	};
	Content.prototype.getString = function(list)
	{
		return this.converter.toString(list);
	};
	Content.prototype.getList = function()
	{
		var content = this.getRaw();
		if (content)
		{
			return this.converter.toArray(content);
		}

		return [];
	};
	Content.prototype.getStyles = function()
	{
		return this.getList().filter(function (item) {
			return item && item.type === 'STYLES';
		})
	};
	Content.prototype.getBlocks = function()
	{
		return this.getList().filter(function (item) {
			return item && item.type === 'BLOCKS';
		})
	};

	function JsonConverter()
	{
	}
	JsonConverter.isValid = function(value)
	{
		try
		{
			var result = JSON.parse(value);
			return BX.type.isPlainObject(result) || BX.type.isArray(result);
		}
		catch (e)
		{
			return false;
		}
	};
	JsonConverter.prototype.toArray = function(value)
	{
		try
		{
			return JSON.parse(value);
		}
		catch (e)
		{
			return [];
		}

	};
	JsonConverter.prototype.toString = function(list)
	{
		list = list.map(function (item) {
			var newItem = {
				'type': item.type,
				'place': item.place,
				'value': item.value
			};
			if (item.block)
			{
				newItem.block = item.block;
			}

			return newItem;
		});

		try
		{
			return JSON.stringify(list);
		}
		catch (e)
		{
			return '';
		}
	};


	function SliceConverter()
	{
		this.sectionId = 'BX_BLOCK_EDITOR_EDITABLE_SECTION';
	}
	SliceConverter.prototype.toArray = function(content)
	{
		var result = [];
		var pattern = "<!--START "
			+ this.sectionId + "\/([\\w]+?)\/([\\w]*?)\/-->"
			+ "([\\s\\S,\\n]*?)"
			+ "<!--END " + this.sectionId + "[\/\\w]+?-->";

		var sliceRegExp = new RegExp(pattern, "g");
		var matches;
		while(matches = sliceRegExp.exec(content))
		{
			var section = matches[1].trim();
			var item = matches[2].trim();
			var value = matches[3].trim();

			result.push({
				'type': section,
				'place': item,
				'value': value
			});
		}

		return result;
	};
	SliceConverter.prototype.toString = function(list)
	{
		return list.map(this.getItemString, this).join('\n');
	};
	SliceConverter.prototype.getItemString = function(item)
	{
		var path = [item.type, item.place];
		var content = item.value;

		var tag = [this.sectionId].concat(path).join('/') + '/';
		return '<!--START %TAG%-->\n%CONTENT%\n<!--END %TAG%-->'
			.replace('%TAG%', tag)
			.replace('%TAG%', tag)
			.replace('%CONTENT%', content);
	};

	BX.namespace('BX.BlockEditor');
	BX.BlockEditor.Content = Content;
})(window);