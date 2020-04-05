;(function(window) {
	if (BX.BlockEditorManager)
		return;

	BX.BlockEditorManager = {

		list: {},
		blockList: {},

		get: function (id)
		{
			if (this.list[id]) {
				return this.list[id];
			}

			return null;
		},

		add: function (params)
		{
			if(!params.blockTypeList)
			{
				params.blockTypeList = this.getBlockList();
			}
			this.list[params.id] = new BXBlockEditor(params);
			BX.onCustomEvent(this, 'onEditorAdd', [this.list[params.id]]);

			return this.list[params.id];
		},

		create: function (params)
		{
			if (!this.get(params.id)) {
				return this.add(params);
			}
			else {
				return this.get(params.id);
			}
		},

		setBlockList: function(list)
		{
			this.blockList = list;
		},


		getBlockList: function()
		{
			return this.blockList;
		}
	};
})(window);


function BXBlockEditor(params)
{
	this.id = params.id;
	this.context = params.context;
	this.iframe = params.iframe;
	this.charset = params.charset;
	this.previewUrl = params.previewUrl;
	this.saveFileUrl = params.saveFileUrl;
	this.site = params.site;
	this.resultNode = params.resultNode;
	this.blockTypeList = params.blockTypeList;
	this.disableScroll = params.disableScroll || false;
	this.panelList = {};
	this.blockList = [];
	this.placeList = [];
	this.dragdrop = null;
	this.preview = null;
	this.stylistContainerNode = null;
	this.stylistInitedValues = {};
	this.isInited = false;
	this.isTemplateMode = !!params.isTemplateMode;

	this.CONST_ATTR_BLOCK = 'data-bx-block-editor-block-type';
	this.CONST_ATTR_PLACE = 'data-bx-block-editor-place';
	this.CONST_ATTR_BLOCK_STATUS = 'data-bx-block-editor-block-status';
	this.CONST_ATTR_STYLIST_TAG = 'data-bx-stylist-container';
	this.CONST_ATTR_TMP_RESOURSE = 'data-bx-resource-temp';
	this.CONST_ATTR_ID_STYLIST = 'bxStylist';

	this.content = new BX.BlockEditor.Content({'caller': this, 'textarea': this.resultNode});
	this.statusManager = new BXBlockEditorStatusManager({'caller': this});
	this.cssParser = new BXBlockEditorCssParser();

	this.shadowNode = BX.findChild(this.context, {className: 'shadow'}, true);
	BX.addCustomEvent(this, 'onLoadBefore', BX.delegate(function(){
		BX.addClass(this.shadowNode, 'active');
	}, this));
	BX.addCustomEvent(this, 'onLoadAfter', BX.delegate(function(){
		BX.removeClass(this.shadowNode, 'active');
	}, this));

	BX.ready(BX.delegate(function()
	{
		if(params.url)
		{
			this.load(params.url);
		}
	}, this));

	if (this.disableScroll)
	{
		BX.bind(this.iframe, 'load', setTimeout(this.initResize.bind(this), 1000));
	}
}
BXBlockEditor.prototype.initResize = function()
{
	this.resize();

	BX.bind(
		this.iframe.contentWindow,
		'resize',
		BX.throttle(this.resize.bind(this), 200)
	);
};
BXBlockEditor.prototype.resize = function()
{
	if (!this.disableScroll)
	{
		return;
	}

	var doc = this.iframe.contentDocument;
	var h = Math.max(
		doc.body.scrollHeight, doc.documentElement.scrollHeight,
		doc.body.offsetHeight, doc.documentElement.offsetHeight,
		doc.body.clientHeight, doc.documentElement.clientHeight
	);
	if (h <= 0)
	{
		return;
	}

	this.iframe.style.height = h + 'px';
	//this.iframe.contentDocument.body.style['overflow-y'] = 'hidden';
};
BXBlockEditor.prototype.init = function()
{
	// call only one time when load window
	if(!this.isInited)
	{
		this.helper = new BXBlockEditorHelper();
		this.initControls();
		this.initDragDrop();
		this.initEditDialog();
		this.initPreview();
		this.initTabsBlockAndStyles();
	}

	// call every time when load content
	this.initPhpSlices();
	this.initBlockPlaces();
	this.initBlocks();

	this.initStylist();
	this.save();

	// fire event onInit only one time
	if(!this.isInited)
	{
		BX.onCustomEvent(this, 'onInit', [this]);
	}

	this.isInited = true;
};

BXBlockEditor.prototype.initPhpSlices = function()
{
	this.phpParser.resetItems();
	var nodeList = BX.findChildren(this.iframe.contentDocument.body, {'attribute': this.phpParser.getAttrName()}, true);
	for(var i in nodeList)
	{
		var node = nodeList[i];
		var phpSlice = node.getAttribute(this.phpParser.getAttrName());
		var phpDesc = this.phpParser.getPhpSliceDescription(phpSlice);
		node.innerHTML = phpDesc.name;
		node.setAttribute('title', phpDesc.title);

		this.phpParser.addItem(node.id, phpSlice, node.outerHTML);
	}
};

BXBlockEditor.prototype.initControls = function()
{
	this.buttonMin = BX.findChild(this.context, {className: 'bx-editor-block-btn-close'}, true);
	this.buttonMax = BX.findChild(this.context, {className: 'bx-editor-block-btn-full'}, true);

	BX.bind(this.buttonMin, 'click', BX.delegate(this.close, this));
	BX.bind(this.buttonMax, 'click', BX.delegate(this.open, this));

	var _this = this;

	this.panelList['edit'] = {};
	this.panelList['edit'].button = BX.findChildByClassName(this.context, 'bx-editor-block-btn-' + 'edit', true);
	this.panelList['edit'].panel = BX.findChildByClassName(this.context, 'edit' + '-panel', true);
	BX.bind(this.panelList['edit'].button, 'click', function(){_this.showPanel('edit');});
	BX.addClass(this.panelList['edit'].button, 'bx-editor-block-btn-active');
	this.panelList['edit'].panel.style.display = null;

	this.panelList['preview'] = {};
	this.panelList['preview'].button = BX.findChildByClassName(this.context, 'bx-editor-block-btn-' + 'preview', true);
	this.panelList['preview'].panel = BX.findChildByClassName(this.context, 'preview' + '-panel', true);
	BX.bind(this.panelList['preview'].button, 'click', function(){_this.showPreview('preview');});

	var getHtmlBtn = this.context.querySelector('[data-role="block-editor-tab-btn-get-html"]');
	BX.clipboard.bindCopyClick(getHtmlBtn, {'text': this.getContent.bind(this)});

	/*
	this.panelList['get-html'] = {};
	this.panelList['get-html'].button = BX.findChildByClassName(this.context, 'bx-editor-block-btn-' + 'get-html', true);
	this.panelList['get-html'].panel = BX.findChildByClassName(this.context, 'get-html' + '-panel', true);
	BX.bind(this.panelList['get-html'].button, 'click', function(){_this.showHtml('get-html');});
	*/

};

BXBlockEditor.prototype.initBlocks = function()
{
	this.blockList = [];

	var blockNodeList = BX.findChildren(this.iframe.contentDocument.body, {'attribute': this.CONST_ATTR_BLOCK}, true);
	for(var i in blockNodeList)
	{
		this.addBlock(blockNodeList[i]);
	}
};

BXBlockEditor.prototype.initStylist = function()
{
	// find or add special style tag
	var headNode = this.iframe.contentDocument.head;
	this.stylistContainerNode = false;
	this.helper.each(headNode.childNodes, function(node)
	{
		var nodeName = node.nodeName;
		if(nodeName == 'SCRIPT' || nodeName == 'STYLE' || nodeName == 'LINK')
		{
			if(node.attributes && node.hasAttribute && node.hasAttribute(this.CONST_ATTR_STYLIST_TAG))
			{
				this.stylistContainerNode = node;
			}
		}
	}, this);
	if(!this.stylistContainerNode)
	{
		this.stylistContainerNode = BX.create('STYLE');
		this.stylistContainerNode.setAttribute(this.CONST_ATTR_STYLIST_TAG, 'item');
		this.stylistContainerNode.setAttribute('type', 'text/css');
		headNode.appendChild(this.stylistContainerNode);
	}

	this.stylistInitedValues = this.cssParser.parse(this.stylistContainerNode.innerHTML);

	// init saved styles, do not if changing template
	if(!this.isInited)
	{
		this.content.getStyles().forEach(function(item) {
			var styles;
			if (item.block)
			{
				styles = item.block.parameters;
			}
			else if (item.value)
			{
				styles = this.cssParser.parse(item.value);
			}

			var currentStyles = this.cssParser.parse(this.stylistContainerNode.innerHTML);
			styles = this.cssParser.mergeStyles(currentStyles, styles);
			this.stylistContainerNode.innerHTML = this.cssParser.getCssString(styles);
		}, this);
	}


	this.placeList = [];
	var placeList = this.findStylistPlaces();
	for(var placeCode in placeList)
	{
		var placeNode = placeList[placeCode];
		var placeBlock = new BXBlockEditorStylist();
		BX.onCustomEvent(this, 'onPlaceBlockCreate', [placeBlock]);
		placeBlock.init(placeNode, {
			caller: this, type: placeCode, controls: {}
		});
		placeBlock.styleNode = this.stylistContainerNode;
		placeBlock.placeNode = placeNode;

		this.placeList.push(placeBlock);
	}

	BX.addCustomEvent(this.editDialog, 'onPlaceEdit', BX.delegate(this.onPlaceEdit, this));
	BX.addCustomEvent(this.editDialog, 'onPlaceHover', BX.delegate(this.onPlaceHover, this));
};

BXBlockEditor.prototype.findStylistPlaces = function()
{
	var placeList = {};

	var tmplPage = BX.findChild(this.iframe.contentDocument.body, {'attribute': {'id': this.CONST_ATTR_ID_STYLIST + 'Page'}}, true);
	if(!tmplPage)
	{
		tmplPage = this.iframe.contentDocument.body;
	}
	placeList['page'] = tmplPage;

	var idList = this.iframe.contentDocument.body.querySelectorAll('[id^=' + this.CONST_ATTR_ID_STYLIST + ']');
	for(var j = 0; j < idList.length; j++)
	{
		var item = idList.item(j);
		if(item && item.id)
		{
			var key = item.id.substr(this.CONST_ATTR_ID_STYLIST.length);
			if(key)
			{
				placeList[key] = item;
			}
		}
	}

	/*
	var tmpPlaceList = this.findBlockPlaces();
	for(var i in tmpPlaceList)
	{
		if(tmpPlaceList[i])
		{
			placeList[i] = tmpPlaceList[i];
		}
	}
	*/

	return placeList;
};

BXBlockEditor.prototype.onPlaceHover = function(code, isHover)
{
	for(var i in this.placeList)
	{
		var placeBlock = this.placeList[i];
		if(code != placeBlock.type)
		{
			continue;
		}

		var node = placeBlock.node;
		if(isHover)
		{
			BX.addClass(node, 'bx-stylist-enter')
		}
		else
		{
			BX.removeClass(node, 'bx-stylist-enter')
		}

		break;
	}
};

BXBlockEditor.prototype.onPlaceEdit = function(code)
{
	for(var i in this.placeList)
	{
		var placeBlock = this.placeList[i];
		if(code != placeBlock.type)
		{
			continue;
		}

		this.editBlock(placeBlock);
		break;
	}
};

BXBlockEditor.prototype.initBlockPlaces = function()
{
	//var placeList = BX.findChildren(this.iframe.contentDocument.body, {'attribute': this.CONST_ATTR_PLACE}, true);
	var key, place;
	var firstPlaceInfoCode = null;

	var placeInfoCode;
	var placeList = this.findBlockPlaces();

	if(this.isTemplateMode)
	{
		var placeInfoList = {};
		for(placeInfoCode in placeList)
		{
			/*
			if(!placeList[key])
			{
				continue;
			}
			*/

			place = placeList[placeInfoCode];
			//var placeInfoCode = place.getAttribute(this.CONST_ATTR_PLACE);
			if(!placeInfoList[placeInfoCode])
			{
				placeInfoList[placeInfoCode] = {'node': place, 'html': ''};
			}

			if(!firstPlaceInfoCode)
			{
				firstPlaceInfoCode = placeInfoCode;
			}
		}

		this.content.getBlocks().forEach(function (item) {
			var placeInfoCode = null;
			if(placeInfoList[item.place])
			{
				placeInfoCode = item.place;
			}
			else if(placeInfoList['body'])
			{
				placeInfoCode = 'body';
			}
			else if(placeInfoList[firstPlaceInfoCode])
			{
				placeInfoCode = firstPlaceInfoCode;
			}
			else
			{
				return;
			}

			var placeInfo = placeInfoList[placeInfoCode];

			var html = '';
			if (item.block)
			{
				html = this.getBlockHtml(item.block.type, item.block.parameters);
			}
			else
			{
				html = this.phpParser.replacePhpByLayout(item.value);
			}
			
			var emptyTextLayout = '<div ' + this.CONST_ATTR_BLOCK + '="text">';
			if (html.indexOf(emptyTextLayout) === 0 && html.length < emptyTextLayout.length + 10)
			{
				html = ' ';
			}
			placeInfo.html += html;

		}, this);


		// clean places from template blocks, that have blocks from content
		this.helper.each(placeInfoList, function(placeInfo){
			if(placeInfo.html.length > 0)
			{
				placeInfo.node.innerHTML = '';
			}
		}, this);


		// fill places by blocks from content
		this.helper.each(placeInfoList, function(placeInfo){

			var hasBlocksFromContent = false;
			if(placeInfo.html.length > 0)
			{
				placeInfo.node.innerHTML = placeInfo.html;
				hasBlocksFromContent = true;
			}

			BX.onCustomEvent(this, 'onPlaceInitBlocksContent', [placeInfo.node, hasBlocksFromContent]);

		}, this);
	}


	for(placeInfoCode in placeList)
	{
		place = placeList[placeInfoCode];

		if(place.innerHTML)
		{
			place.innerHTML = place.innerHTML.trim();
		}

		this.actualizeBlockPlace(place);
	}
};

BXBlockEditor.prototype.findBlockPlaces = function()
{
	var result = {};

	var params = {'attribute': this.CONST_ATTR_PLACE};
	var placeList = BX.findChildren(this.iframe.contentDocument.body, params, true);
	this.helper.each(placeList, function(place){
		var placeCode = place.getAttribute(this.CONST_ATTR_PLACE);
		result[placeCode] = place;
	}, this);

	return result;
};
BXBlockEditor.prototype.actualizeBlockPlace = function(place)
{
	if(!place.hasAttribute(this.CONST_ATTR_PLACE))
	{
		return false;
	}

	var dropZone;

	// if there are no blocks in place, then clean place
	var blockList = BX.findChildren(place, {'attribute': this.CONST_ATTR_BLOCK}, true);
	if(blockList.length == 0)
	{
		BX.cleanNode(place);
	}

	// remove all text child-nodes
	//for(var i in place.childNodes)
	for(var i = 0;  i < place.childNodes.length; i++)
	{
		var childNode = place.childNodes.item(i);
		if(childNode && childNode.nodeName == '#text')
		{
			BX.remove(childNode);
		}
	}

	// if it is empty place, then add dropzone
	if(place.childNodes.length == 0)
	{
		dropZone = BX.create('DIV', {'text': BX.message('BLOCK_EDITOR_PLACE_DROP_ZONE')});
		BX.addClass(dropZone, 'bx-editor-place');
		place.appendChild(dropZone);
		this.dragdrop.dragdrop.addCatcher(dropZone);
	}
	else // else remove dropzone
	{
		dropZone = BX.findChild(place, {'className': 'bx-editor-place'});
		if(dropZone && place.childNodes.length > 1)
		{
			this.dragdrop.dragdrop.removeCatcher(dropZone);
			BX.remove(dropZone);
		}
	}

	return true;
};

BXBlockEditor.prototype.initPreview = function()
{
	this.preview = new BXBlockEditorPreview({'context': this.context, 'caller': this, 'site': this.site, 'url': this.previewUrl});
};

BXBlockEditor.prototype.initTabsBlockAndStyles = function()
{
	var tabContainer = BX.findChildByClassName(this.context, 'bx-editor-block-tabs', true);

	var tabBlocks = BX.findChildByClassName(tabContainer, 'blocks', true);
	var tabStyles = BX.findChildByClassName(tabContainer, 'styles', true);

	var contentBlocks = BX.findChildByClassName(this.context, 'edit-panel-tabs-block', true);
	var contentStyles = BX.findChildByClassName(this.context, 'edit-panel-tabs-style', true);


	var clickHandler = function(){
		if(BX.hasClass(this, 'blocks'))
		{
			BX.removeClass(tabStyles, 'active');
			contentBlocks.style.display = 'block';
			contentStyles.style.display = 'none';
		}
		else
		{
			BX.removeClass(tabBlocks, 'active');
			contentBlocks.style.display = 'none';
			contentStyles.style.display = 'block';
		}

		BX.addClass(this, 'active');
	};

	BX.bind(tabBlocks, 'click', clickHandler);
	BX.bind(tabStyles, 'click', clickHandler);

	var contentPagerPrev = BX.findChildByClassName(this.context, 'adm-nav-page-prev', true);
	var contentPagerNext = BX.findChildByClassName(this.context, 'adm-nav-page-next', true);
	var pagerClickHandler = function(context, next){

		var currentPageNode = context.querySelector('ul.bx-block-editor-i-block-list');;
		while(currentPageNode)
		{
			if(BX.style(currentPageNode, 'display') == 'block')
			{
				break;
			}
			currentPageNode = currentPageNode.nextElementSibling;
		}

		if(currentPageNode)
		{
			var nextPageNode;
			if(next)
			{
				nextPageNode = currentPageNode.nextElementSibling;
			}
			else
			{
				nextPageNode = currentPageNode.previousElementSibling;
			}

			if(nextPageNode)
			{
				var easing = new BX.easing({
					duration : 200,
					start : {opacity: 1}, finish : {opacity: 0},
					transition : BX.easing.transitions.quart,
					step: function(state){
						currentPageNode.style.opacity = state.opacity;
					},
					complete : function() {
						currentPageNode.style.display = 'none';
						currentPageNode.style.opacity = 1;
						nextPageNode.style.display = 'block';
					}
				});
				easing.animate();
			}
		}

	};

	var _context = this.context;
	BX.bind(contentPagerPrev, 'click', function(){
		pagerClickHandler(_context, false);
	});
	BX.bind(contentPagerNext, 'click', function(){
		pagerClickHandler(_context, true);
	});

};

BXBlockEditor.prototype.showPanel = function(code)
{
	for(var i in this.panelList)
	{
		if(i != code)
		{
			this.panelList[i].panel.style.display = 'none';
			BX.removeClass(this.panelList[i].button, 'bx-editor-block-btn-active');
		}
	}

	BX.addClass(this.panelList[code].button, 'bx-editor-block-btn-active');
	this.panelList[code].panel.removeAttribute('style');
	//this.panelList[code].panel.style.display = '';
};

BXBlockEditor.prototype.showPreview = function(panelCode, executePhp)
{
	if(executePhp == undefined)
	{
		executePhp = false;
	}
	this.preview.show({'content': this.getContent(executePhp)});
	this.showPanel(panelCode);
};

BXBlockEditor.prototype.showHtml = function(panelCode)
{
	var node = BX.findChild(this.panelList[panelCode].panel, {'tag': 'textarea'}, true);
	node.value = this.getContent(); // this.helper.htmlEscape(this.getContent())
	this.showPanel(panelCode);
};

BXBlockEditor.prototype.initEditDialog = function()
{
	this.editDialog = new BXBlockEditorEditDialog({
		'context': this.context,
		'caller': this,
		'saveFileUrl': this.saveFileUrl
	});
	this.editDialog.caller = this;
	this.phpParser = new BXBlockEditorPHPParser({'htmlEditor': this.editDialog.htmlEditor});
	this.editDialog.phpParser = this.phpParser;

	BX.addCustomEvent(
		this.editDialog,
		'controlChangeValue',
		BX.delegate(this.onEditDialogControlChangeValue, this)
	);
	BX.addCustomEvent(
		this.editDialog,
		'onSave',
		BX.delegate(this.editBlockEnd, this)
	);
	BX.addCustomEvent(
		this.editDialog,
		'onCancel',
		BX.delegate(this.editBlockEnd, this)
	);
	BX.addCustomEvent(
		this,
		'onClose',
		BX.delegate(this.editDialog.save, this.editDialog)
	);

	var formNode = BX.findParent(this.resultNode, {'tag': 'form'});
	var self = this;
	BX.bind(
		formNode,
		'submit',
		function(e)
		{
			if (self.isFinalSave)
			{
				self.isFinalSave = false;
				return;
			}

			e.preventDefault();
			e.stopPropagation();

			self.editDialog.save(function(){
				self.isFinalSave = true;
				self.save();
				BX.submit(formNode);
			});
		}
	);
};

BXBlockEditor.prototype.initDragDrop = function()
{
	this.dragdrop = new BXBlockEditorDragDrop();

	BX.addCustomEvent(this.dragdrop, 'onZoneEnter', this.showDragDropZone);
	BX.addCustomEvent(this.dragdrop, 'onZoneLeave', this.hideDragDropZone);
	BX.addCustomEvent(this.dragdrop, 'onItemAdd', BX.delegate(this.onDragDropItemAdd, this));
	BX.addCustomEvent(this.dragdrop, 'onItemMove', BX.delegate(this.onDragDropItemMove, this));
};

BXBlockEditor.prototype.open = function()
{
	BX.addClass(this.context, 'editing');
	/*
	 var rect = this.context.getBoundingClientRect();
	 this.context.style.top = rect.top + 'px';
	 this.context.style.left = rect.left + 'px';
	 this.context.style.position = 'fixed';
	 setTimeout(BX.delegate(function(){
	 var context = this.context;
	 var easing = new BX.easing({
	 duration : 300,
	 start : { left : rect.left, top : rect.top},
	 finish : { left : 0, top: 0 },
	 transition : BX.easing.transitions.quart,
	 step : function(state){
	 context.left = state.left + "px";
	 context.top = state.top + "px";
	 },
	 complete : function() {
	 BX.addClass(context, 'editing');
	 }
	 });
	 easing.animate();
	 }, this), 100);
	 */
};

BXBlockEditor.prototype.close = function()
{
	BX.onCustomEvent(this, 'onClose');
	BX.removeClass(this.context, 'editing');
	//this.context.style.position = 'initial';
};

BXBlockEditor.prototype.save = function()
{
	var content = this.getContentForSave();
	BX.onCustomEvent(this, 'onSave', [content]);

	if(!this.resultNode)
	{
		return;
	}

	this.resultNode.value = content;
};

BXBlockEditor.prototype.load = function(url, callback)
{
	BX.onCustomEvent(this, 'onLoadBefore', [url, callback, this]);
	BX.unbindAll(this.iframe);
	BX.bind(this.iframe, 'load', BX.delegate(function()
	{
		var css = BX.loadCSS(
			'/bitrix/js/fileman/block_editor/editor.css?r='+Math.random(),
			this.iframe.contentDocument,
			this.iframe.contentWindow
		);

		if(css)
		{
			var cssParams = {attrs: {}};
			cssParams['attrs'][this.CONST_ATTR_TMP_RESOURSE] = 'bx-editor';
			BX.adjust(css, cssParams);
		}

		this.init();

		if(BX.type.isFunction(callback))
		{
			callback.apply(this);
		}
		BX.onCustomEvent(this, 'onLoadAfter', [url, callback, this]);

	}, this));

	if(this.charset)
	{
		url = url + '&template_charset=' + this.charset;
	}
	this.iframe.src = url;
};


BXBlockEditor.prototype.getSortedBlockListWithReplacedEmptyPlaces = function()
{
	this.helper.each(this.findBlockPlaces(), function (placeNode, placeCode) {
		var blockNodes = placeNode.querySelectorAll('[' + this.CONST_ATTR_BLOCK + ']');
		if (blockNodes.length > 0 )
		{
			return;
		}
		if (placeNode.children.length != 1)
		{
			return;
		}

		var blockNode = this.getBlockNodeByType('text');
		if (blockNode)
		{
			var block = this.addBlockByNode(blockNode, placeNode.children[0], true);
			block.setContentHtml('');
		}

	}, this);

	return this.getSortedBlockList();
};

BXBlockEditor.prototype.getSortedBlockList = function()
{
	var sortedBlockList = [];
	var nodeList = this.iframe.contentDocument.body.querySelectorAll('[' + this.CONST_ATTR_BLOCK + ']');
	nodeList = BX.convert.nodeListToArray(nodeList);

	// get as changed that places what have changed blocks
	var changedPlaceList = this.statusManager.getPlaceNameList(nodeList);
	nodeList.forEach(function(node, sort)
	{
		// add blocks only from changed places
		if(!node.parentNode || !BX.util.in_array(node.parentNode.getAttribute(this.CONST_ATTR_PLACE), changedPlaceList))
		{
			return;
		}

		this.helper.each(this.blockList, function(block) {
			if(block.node !== node)
			{
				return;
			}

			block.sort = sort;
			sortedBlockList.push(block);
		}, this);
	}, this);

	return sortedBlockList;
};

BXBlockEditor.prototype.getContentForSave = function()
{
	if(!this.isTemplateMode)
	{
		return this.getContent();
	}
	else
	{
		var list = [];

		// save styles
		if(this.stylistContainerNode && this.stylistContainerNode.innerHTML)
		{
			var styleContent = this.stylistContainerNode.innerHTML.trim();
			if(styleContent)
			{
				// save only changed styles
				var diffStyles = this.cssParser.diffStylesAll(
					this.stylistInitedValues,
					this.cssParser.parse(styleContent)
				);

				if(diffStyles)
				{
					styleContent = '<style type="text/css">' + "\n" + this.cssParser.getCssString(diffStyles) + "\n" + '</style>';
					list.push({
						'type': 'STYLES',
						'place': 'page',
						'value': styleContent,
						'block': {
							'type': 'stylist',
							'parameters': diffStyles
						}
					});
				}
			}
		}

		// save blocks
		var blockList = this.isFinalSave
			? this.getSortedBlockListWithReplacedEmptyPlaces()
			: this.getSortedBlockList();

		list = list.concat(blockList.map(function(block) {
			return {
				'type': 'BLOCKS',
				'place': block.getPlaceHolderCode(),
				'value': block.getContentHtmlOuter(), // '',
				'block': {
					'type': block.type,
					'parameters': block.getEditValues()
				}
			};
		}, this));

		return this.content.getString(list);
	}
};

BXBlockEditor.prototype.getContent = function(withoutPHP)
{
	withoutPHP = !!(withoutPHP || null);

	var doc = this.iframe.contentDocument.cloneNode(true);

	// clean resources from editor resources
	if(doc.head)
	for(var i = doc.head.childNodes.length - 1;  i >= 0; i--)
	{
		var node = doc.head.childNodes.item(i);
		var nodeName = node.nodeName;
		nodeName = nodeName || '';
		nodeName = nodeName.toUpperCase();
		if(nodeName == 'SCRIPT' || nodeName == 'STYLE' || nodeName == 'LINK')
		{
			if(node.attributes && node.hasAttribute)
			{
				if(node.hasAttribute(this.CONST_ATTR_TMP_RESOURSE))
				{
					BX.remove(node);
				}
				else if(node.hasAttribute(this.CONST_ATTR_STYLIST_TAG))
				{
					//node.removeAttribute(this.CONST_ATTR_STYLIST_TAG);
				}
			}
			
			if (node.href && node.href.indexOf('/bitrix/js/fileman/block_editor/editor.css') > -1)
			{
				BX.remove(node);
			}
		}
	}

	// delete layout from empty block places
	var blockPlaceList = BX.findChildren(doc.body, {'attribute': this.CONST_ATTR_PLACE}, true);
	for(var j in blockPlaceList)
	{
		var blockPlace = blockPlaceList[j];
		var blockPlaceBlocks = BX.findChildren(blockPlace, {'attribute': this.CONST_ATTR_BLOCK}, true);
		if(blockPlaceBlocks.length == 0)
		{
			BX.cleanNode(blockPlace);
		}
	}

	// clean blocks from accessory layout
	var blockList = [];
	if(doc.body)
	{
		blockList = BX.findChildren(doc.body, {'attribute': this.CONST_ATTR_BLOCK}, true);
	}
	for(var k in blockList)
	{
		var block = blockList[k];

		// get block content
		var blockContent = BX.findChild(block, {'className': 'bx-content'}, true);
		var blockContentChildren = BX.convert.nodeListToArray(blockContent.childNodes);

		if(withoutPHP && block && block.getAttribute && block.getAttribute(this.CONST_ATTR_BLOCK) == 'component')
		{
			blockContentChildren = BX.findChildren(block, {'className': 'bx-component'}, true)
		}

		// remove accessory layout
		BX.cleanNode(block);
		BX.adjust(block, {children: blockContentChildren});

		// remove temporary attributes
		for(var n = block.attributes.length; n >=0; n--)
		{
			if(block.attributes[n] && block.attributes[n].name != this.CONST_ATTR_BLOCK)
			{
				block.removeAttribute(block.attributes[n].name);
			}
		}
	}

	// replace layout by placeholder #bx_....#
	if (!withoutPHP)
	{
		this.phpParser.replaceLayoutBySurrogate(doc.body);
	}


	// get html
	var result = doc.documentElement.outerHTML;

	// replace placeholder #bx_....# by PHP-chunk
	if (!withoutPHP)
	{
		result = this.phpParser.replaceSurrogateByPhp(result);
	}

	return result;
};

BXBlockEditor.prototype.showDragDropZone = function(node, isTopPosition)
{
	//if(BX.hasClass(node, 'bx-editor-block'))
	{
		BX.addClass(node, 'bx-dd-enter');
		if(isTopPosition)
		{
			BX.removeClass(node, 'bx-dd-enter-bottom');
			BX.addClass(node, 'bx-dd-enter-top');
		}
		else
		{
			BX.removeClass(node, 'bx-dd-enter-top');
			BX.addClass(node, 'bx-dd-enter-bottom');
		}
	}
};

BXBlockEditor.prototype.hideDragDropZone = function(node)
{
	//if(BX.hasClass(node, 'bx-editor-block'))
	{
		BX.removeClass(node, 'bx-dd-enter');
		BX.removeClass(node, 'bx-dd-enter-top');
		BX.removeClass(node, 'bx-dd-enter-bottom');
	}
};

BXBlockEditor.prototype.onDragDropItemMove = function(blockNode, node, before)
{
	var oldBlockPlace = blockNode.parentNode;
	var block;
	for(var i in this.blockList)
	{
		if(this.blockList[i] && this.blockList[i].node == blockNode)
		{
			block = this.blockList[i];
			break;
		}
	}

	block.onMoveHandler(node);

	// move block
	this.helper.appendChildNode(blockNode, node, before);

	this.actualizeBlockPlace(oldBlockPlace);
	this.actualizeBlockPlace(node.parentNode);

	BX.onCustomEvent(this, 'onBlockMoveAfter', [block]);

	this.save();
};

BXBlockEditor.prototype.onDragDropItemAdd = function(type, node, before)
{
	/*
	before = before || null;
	var blankBlock = this.getBlockByType(type);
	var block = this.addBlock(blankBlock);
	block.onMoveHandler(node);
	this.helper.appendChildNode(blankBlock, node, before);
	this.actualizeBlockPlace(node.parentNode);
	*/

	this.addBlockByNode(this.getBlockNodeByType(type), node, before);
	this.save();
};

BXBlockEditor.prototype.onEditDialogControlChangeValue = function(key, value, columnNum)
{
	if(this.currentEditingBlock)
	{
		this.currentEditingBlock.setEditValue(key, value, columnNum);
	}
};

BXBlockEditor.prototype.getCurrentEditingBlock = function()
{
	return this.currentEditingBlock;
};

BXBlockEditor.prototype.getBlockHtml = function(type, values)
{
	var blockNode = this.getBlockNodeByType(type);
	var block;
	if(type === 'component')
	{
		block = new BXBlockEditorBlockComponent();
	}
	else
	{
		block = new BXBlockEditorBlock();
	}

	block.init(blockNode, {'caller': this, 'type': type});
	block.setEditValues(values);

	return block.getContentHtmlOuter();
};

BXBlockEditor.prototype.getBlockHtmlByCode = function(code)
{
	var type = this.blockTypeList[code].TYPE;
	var html = this.blockTypeList[code].HTML;

	if(type == 'component')
	{
		html = this.phpParser.getComponentInclude(code);
	}

	return this.phpParser.replacePhpByLayout(html);
};

BXBlockEditor.prototype.getBlockNodeByType = function(code)
{
	var bxParams = {};
	var type = this.blockTypeList[code].TYPE;
	var html = this.getBlockHtmlByCode(code);

	bxParams[this.CONST_ATTR_BLOCK] = type;
	return BX.create({'tag': 'DIV', 'attrs': bxParams, 'html': html});
};

BXBlockEditor.prototype.addBlockByNode = function(blockNode, node, before)
{
	var block = this.addBlock(blockNode);
	block.onMoveHandler(node);
	this.helper.appendChildNode(blockNode, node, before);

	this.actualizeBlockPlace(node.parentNode);

	BX.onCustomEvent(block, 'onBlockCreateAfter');
	BX.onCustomEvent(this, 'onBlockCreateAfter', [block]);
	return block;
};

BXBlockEditor.prototype.addBlockByHtml = function(html, node, before)
{
	var parentNode = BX.create({'tag': 'DIV', 'html': html});
	return this.addBlockByNode(parentNode.children[0], node, before);
};

BXBlockEditor.prototype.addBlock = function(blockNode)
{
	var type = blockNode.getAttribute(this.CONST_ATTR_BLOCK);
	var block;

	BX.addClass(blockNode, 'bx-editor-block');
	BX.addClass(blockNode, 'bx-type-' + type);

	if(type === 'component')
	{
		block = new BXBlockEditorBlockComponent();
	}
	else
	{
		block = new BXBlockEditorBlock();
	}

	BX.onCustomEvent(this, 'onBlockCreate', [block]);
	block.init(blockNode, {
		caller: this,
		type: blockNode.getAttribute(this.CONST_ATTR_BLOCK),
		controls: {
			drag: BX.delegate(function(){  }, this),
			clone: BX.delegate(function(e){ this.cloneBlock(block); return BX.PreventDefault(e);}, this),
			edit: BX.delegate(function(e){ this.editBlock(block); return BX.PreventDefault(e);}, this),
			remove: BX.delegate(function(e){ this.removeBlock(block); return BX.PreventDefault(e);}, this)
		}
	});
	this.blockList.push(block);
	this.dragdrop.addItem(block.node);

	return block;
};

BXBlockEditor.prototype.removeBlock = function(block)
{
	BX.onCustomEvent(this, 'onBlockRemove', [block]);

	for(var i in this.blockList)
	{
		if(block == this.blockList[i])
		{
			this.blockList.splice(i, 1);
		}
	}

	this.dragdrop.removeItem(block.node);
	var parentRemovedNode = block.node.parentNode;
	BX.remove(block.node);
	this.actualizeBlockPlace(parentRemovedNode);

	BX.onCustomEvent(this, 'onBlockRemoveAfter', [parentRemovedNode]);

	this.save();
	this.editDialog.hide();
};

BXBlockEditor.prototype.cloneBlock = function(block)
{
	var bxParams = {};
	bxParams[this.CONST_ATTR_BLOCK] = block.type;

	var clonedBlankBlock = BX.create({
		'tag': 'DIV',
		'attrs': bxParams,
		//'children': nodeContent.children
		'html': this.phpParser.replacePhpByLayout(block.getContentHtml())
	});

	block.node.parentNode.insertBefore(clonedBlankBlock, block.node);
	var clonedBlock = this.addBlock(clonedBlankBlock);

	BX.onCustomEvent(this, 'onBlockClone', [clonedBlock]);
	this.save();
};

BXBlockEditor.prototype.editBlockEnd = function(hasChanges)
{
	if(!this.currentEditingBlock)
	{
		return;
	}

	BX.onCustomEvent(this, 'onBlockEditEnd', [this.currentEditingBlock, hasChanges]);

	this.save();

	BX.removeClass(this.currentEditingBlock.node, 'bx-editor-block-current-edit');
	this.currentEditingBlock = null;
};

BXBlockEditor.prototype.editBlock = function(block)
{
	BX.onCustomEvent(this, 'onBlockEdit', [block]);

	var _this = this;
	this.editDialog.save(function()
	{
		_this.currentEditingBlock = block;
		BX.addClass(_this.currentEditingBlock.node, 'bx-editor-block-current-edit');

		_this.editDialog.load(block.getEditPropList());
		_this.editDialog.show();
	});
};


function BXBlockEditorBlock()
{
	this.caller = null;
	this.type = '';
	this.node = {};
	this.nodeContent = {};
	this.editHandlerList = {};
}

BXBlockEditorBlock.prototype.init = function (node, params)
{
	this.caller = params.caller;
	this.type = params.type;

	this.node = node;
	this.helper = new BXBlockEditorHelper();
	this.initStructure();
	if (params.controls)
	{
		this.initControls(params.controls);
	}
	this.initEditHandlers();
	this.initDependencies();
};

BXBlockEditorBlock.prototype.initEditHandlers = function()
{
	BX.onCustomEvent(this.caller, 'onBlockInitHandlers', [this, this.type]);
};

BXBlockEditorBlock.prototype.getPlaceHolderCode = function()
{
	return this.node.parentNode.getAttribute(this.caller.CONST_ATTR_PLACE);
};

BXBlockEditorBlock.prototype.setContentHtml = function(html, withoutPHP)
{
	withoutPHP = !!(withoutPHP || null);
	if (!withoutPHP)
	{
		html = this.caller.phpParser.replacePhpByLayout(html);
	}

	this.nodeContent.innerHTML = html;
};
BXBlockEditorBlock.prototype.getContentHtml = function(withoutPHP)
{
	withoutPHP = !!(withoutPHP || null);
	var nodeContent = BX.clone(this.nodeContent);

	// replace layout by placeholder #bx_....#
	if (!withoutPHP)
	{
		this.caller.phpParser.replaceLayoutBySurrogate(nodeContent);
	}

	// get html
	var result = nodeContent.innerHTML;
	if(!result)
	{
		result = '';
	}

	// replace placeholder #bx_....# by PHP-chunk
	if (!withoutPHP)
	{
		result = this.caller.phpParser.replaceSurrogateByPhp(result);
	}
	result = result.trim();

	return result;
};
BXBlockEditorBlock.prototype.getContentHtmlOuter = function(withoutPHP)
{
	var blockHtml = this.getContentHtml(withoutPHP);
	blockHtml = '<div ' + this.caller.CONST_ATTR_BLOCK + '="' + this.type + '">\n' + blockHtml + '\n</div>';

	return blockHtml;
};
BXBlockEditorBlock.prototype.findEditHandler = function(code)
{
	if(this.editHandlerList && this.editHandlerList[code])
	{
		return this.editHandlerList[code];
	}

	return null;
};

BXBlockEditorBlock.prototype.initDependencies = function()
{
	var result = {};
	for(var code in this.editHandlerList)
	{
		var handler = this.editHandlerList[code];
		if(!BX.type.isArray(handler.reload_dependence))
		{
			continue;
		}

		for(var j in handler.reload_dependence)
		{
			var dependenceCode = handler.reload_dependence[j];
			if(!BX.type.isArray(result[dependenceCode]))
			{
				result[dependenceCode] = [];
			}

			result[dependenceCode].push(code);
		}
	}

	this.dependencies =  result;
};

BXBlockEditorBlock.prototype.onMoveHandler = function(nearNode)
{
	var node = this.node;
	this.helper.each(this.editHandlerList, BX.delegate(function(handler){
		if(BX.type.isFunction(handler.onMove))
		{
			this.findEditNodeList(handler.className);
			handler.onMove.apply(this, [node, nearNode]);
		}
	}, this));

	if(BX.type.isFunction(this.onMove))
	{
		this.onMove.apply(this, [node, nearNode]);
	}
};

BXBlockEditorBlock.prototype.findEditNodeList = function(className, columnNum)
{
	var result = [];
	var nodeList = [];
	var columnCounter = 0;
	if(columnNum)
	{
		columnNum = parseInt(columnNum);
	}

	if(className)
	{
		nodeList = BX.findChildren(this.nodeContent, {'className': className}, true);
	}
	else
	{
		nodeList.push(this.nodeContent);
	}

	for(var i in nodeList)
	{
		if(columnNum)
		{
			columnCounter++;
			if(columnCounter < columnNum) continue;
			if(columnCounter > columnNum) break;
		}

		result.push(nodeList[i]);
	}

	return result;
};
BXBlockEditorBlock.prototype.columnToolName = 'column-count';
BXBlockEditorBlock.prototype.getEditValues = function()
{
	var result = [];
	var column = this.getEditValue(this.columnToolName);
	column = column > 0 ? column : 1;

	for (var code in this.editHandlerList)
	{
		if (!this.editHandlerList.hasOwnProperty(code))
		{
			continue;
		}

		for (var i = 1; i <= column; i++)
		{
			var value = this.getEditValue(code, i);
			if (value === null || value === '')
			{
				continue;
			}

			result.push({'code': code, 'value': value, 'column': i});
		}
	}

	return result;
};
BXBlockEditorBlock.prototype.setEditValues = function(values)
{
	var filtered = values.filter(function (item) {
		return item.code == this.columnToolName;
	}, this);
	if (filtered && filtered[0])
	{
		var column = filtered[0].value;
		column = column > 0 ? column : 1;
		this.setEditValue(this.columnToolName, column);
	}
	values.forEach(function (item) {
		this.setEditValue(item.code, item.value, item.column > 0 ? item.column : 1);
	}, this);
};
BXBlockEditorBlock.prototype.setEditValue = function(param, value, columnNum)
{
	var handler = this.findEditHandler(param);
	if(!handler)
	{
		return;
	}

	var nodeList = this.findEditNodeList(handler.className, columnNum);
	if(!BX.type.isFunction(handler.func))
	{
		return;
	}

	for(var i in nodeList)
	{
		var node = nodeList[i];
		handler.func.apply(this, [node, param, value]);
	}

};
BXBlockEditorBlock.prototype.getEditValue = function(param, columnNum)
{
	var result = null;

	var handler = this.findEditHandler(param);
	if(!handler)
	{
		return result;
	}

	var nodeList = this.findEditNodeList(handler.className, columnNum);


	if(!BX.type.isFunction(handler.func))
	{
		return result;
	}

	var node = nodeList[0];
	if(!node)
	{
		return result;
	}

	result = handler.func.apply(this, [node, param]);
	return result;

};
BXBlockEditorBlock.prototype.getEditPropList = function(filterCodeList)
{
	var result = [];
	filterCodeList = filterCodeList || [];

	for(var code in this.editHandlerList)
	{
		if(filterCodeList.length > 0 && !BX.util.in_array(code, filterCodeList))
		{
			continue;
		}

		var handler = this.editHandlerList[code];
		result.push({
			'code': code,
			'params': handler['params'] ? handler['params'] : {},
			'value': this.getEditValue(code),
			'dependence': this.dependencies[code] ? this.dependencies[code] : []
		});
	}

	return result;
};

BXBlockEditorBlock.prototype.initStructure = function()
{
	var blockNode = this.node;

	var editorBlock;

	var editorBlockShadow = document.createElement('div');
	BX.addClass(editorBlockShadow, 'bx-shadow');

	var editorBlockControls = document.createElement('div');
	BX.addClass(editorBlockControls, 'bx-controls');
	editorBlockControls.innerHTML = ''+
	'<span title="' + BX.message('BLOCK_EDITOR_BLOCK_ACTION_MOVE') + '" class="bx-editor-block-controls-btn bx-drag"></span>' +
	'<span title="' + BX.message('BLOCK_EDITOR_BLOCK_ACTION_REMOVE') + '" class="bx-editor-block-controls-btn bx-remove"></span>' +
	'<span title="' + BX.message('BLOCK_EDITOR_BLOCK_ACTION_COPY') + '" class="bx-editor-block-controls-btn bx-clone"></span>' +
	'<span title="' + BX.message('BLOCK_EDITOR_BLOCK_ACTION_EDIT') + '" class="bx-editor-block-controls-btn bx-edit"></span>' +
	'';

	var htmlContent = blockNode.innerHTML;
	var editorBlockContent = BX.create({
		'tag': 'DIV',
		'props': {'className': 'bx-content'},
		'html': htmlContent
		//'children': blockNode.children
	});
	blockNode.innerHTML = '';
	this.nodeContent = editorBlockContent;

	editorBlock = BX.create({
		'tag': 'DIV',
		'props': {'className': 'bx-block-inside'},
		'children': [editorBlockShadow, editorBlockContent, editorBlockControls],
		'style': {'display': 'none'}
	});


	var editorBlockDropZoneBefore = document.createElement('div');
	BX.addClass(editorBlockDropZoneBefore, 'bx-dropzone');
	editorBlockDropZoneBefore.innerHTML = BX.message('BLOCK_EDITOR_PLACE_DROP_ZONE');
	var editorBlockDropZoneAfter = document.createElement('div');
	BX.addClass(editorBlockDropZoneAfter, 'bx-dropzone');
	editorBlockDropZoneAfter.innerHTML = BX.message('BLOCK_EDITOR_PLACE_DROP_ZONE');

	blockNode.appendChild(editorBlockDropZoneBefore);
	blockNode.appendChild(editorBlock);
	blockNode.appendChild(editorBlockDropZoneAfter);

	editorBlock.style.display = 'block';
};
BXBlockEditorBlock.prototype.initControls = function(controlHandlers)
{
	var controls = BX.findChild(this.node, {'className': 'bx-controls'}, true);

	var remove = BX.findChild(controls, {'className': 'bx-remove'}, true);
	var drag = BX.findChild(controls, {'className': 'bx-drag'}, true);
	var clone = BX.findChild(controls, {'className': 'bx-clone'}, true);
	var edit = BX.findChild(controls, {'className': 'bx-edit'}, true);

	BX.bind(remove, 'click', controlHandlers.remove);
	BX.bind(drag, 'click', controlHandlers.drag);
	BX.bind(clone, 'click', controlHandlers.clone);
	BX.bind(edit, 'click', controlHandlers.edit);
	BX.bind(this.node, 'click', controlHandlers.edit);
};

function BXBlockEditorBlockComponent()
{
	BXBlockEditorBlockComponent.superclass.constructor.call(this);
	this.componentDescriptionNode = null;

	this.init = function(node, params)
	{
		// rewrite edit behavior
		params.controls.edit = BX.delegate(this.onEdit, this);

		// call parent method
		BXBlockEditorBlockComponent.superclass.init.call(this, node, params);
		this.setContentClearPhp(this.getContentClearPhp());

		// set description
		var editorBlockComponentHeader = BX.create({'tag': 'DIV', 'props': {'className': 'bx-component-header'}});
		var editorBlockComponentIcon = BX.create({'tag': 'DIV', 'props': {'className': 'bx-component-icon'}});
		var editorBlockComponentDescription = BX.create({'tag': 'DIV', 'props': {'className': 'bx-component-description'}});
		var editorBlockComponent = BX.create({
			'tag': 'DIV',
			'props': {'className': 'bx-component'},
			'children': [editorBlockComponentHeader, editorBlockComponentIcon, editorBlockComponentDescription]
		});
		this.nodeContent.parentNode.insertBefore(editorBlockComponent, this.nodeContent.nextSibling);

		this.componentIconNode = BX.findChildByClassName(this.node, 'bx-component-icon', true);
		this.componentDescriptionNode = BX.findChildByClassName(this.node, 'bx-component-description', true);
		this.componentHeaderNode = BX.findChildByClassName(this.node, 'bx-component-header', true);

		var componentDescription = this.caller.phpParser.getPhpSliceDescription(this.getContentClearPhp());
		this.componentHeaderNode.innerHTML = componentDescription ? componentDescription.name : this.type;
		this.componentDescriptionNode.innerHTML = componentDescription ? componentDescription.title : this.type;

		BX.addCustomEvent(this, 'onBlockCreateAfter', this.onBlockCreateAfter);
	};

	this.setContentClearPhp = function(php)
	{
		php = '<div class="bxBlockPadding">' + php + '</div>';
		this.setContentHtml(php);
	};
	this.getContentClearPhp = function()
	{
		return this.getContentHtml().split(/<[^?>]+>/g).join('');
	};

	this.onBlockCreateAfter = function()
	{
		this.showComponentPropertiesDialog();
	};

	this.onEdit = function()
	{
		this.showComponentPropertiesDialog();
	};

	this.showComponentPropertiesDialog = function()
	{
		this.caller.currentEditingBlock = this;

		var _this = this;
		this.caller.editDialog.save();
		this.caller.currentEditingBlock = this;
		this.caller.phpParser.showComponentPropertiesDialog(
			this.getContentClearPhp(),
			function(html){
				_this.setContentClearPhp(html);
				_this.caller.editBlockEnd(true);
			},
			function(){	_this.caller.editBlockEnd(false); }
		);
	};
}
BX.extend(BXBlockEditorBlockComponent, BXBlockEditorBlock);


function BXBlockEditorStylist()
{
	BXBlockEditorStylist.superclass.constructor.call(this);
	this.styleNode = null;
	this.placeNode = null;

	this.selectorTextList = [
		'.bxBlockContentText', '.bxBlockContentText p',
		'.bxBlockContentSocial', '.bxBlockContentSocial p',
		'.bxBlockContentBoxedText', '.bxBlockContentBoxedText p'
	];

	this.selectorAList = [
		'.bxBlockSocial a',
		'.bxBlockContentText a',
		'.bxBlockContentBoxedText a'
	];

	this.getPlaceHolderCode = function()
	{

	};
	this.initStructure = function(){};
	this.initControls = function(){};
	this.getContentHtml = function(){};
	this.getContentHtmlOuter = function(){};
	this.findEditNodeList = function(className)
	{
		return [this.styleNode];
	};
	this.initEditHandlers = function()
	{
		if(this.type == 'page')
		{
			this.editHandlerList = {
				'bx-stylist-bgcolor': {
					'className': '',
					'func': function (node, param, value) {
						return this.css('', [''], node, 'background', value);
					}
				}
			};

			var getHTagHandler = (function (){
				return function (handlerList, hTag)
				{
					handlerList['bx-stylist-' + hTag + '-color'] = {
						'className': '',
						'func': function (node, param, value) {
							if(value)
							{
								value += ' !important';
							}

							var result = this.css('', [hTag], node, 'color', value);
							if(result)
							{
								resultList = result.split(' ');
								result = resultList[0];
							}

							return result;
						}
					};

					handlerList['bx-stylist-' + hTag + '-font-size'] = {
						'className': '',
						'func': function (node, param, value) {
							if(value)
							{
								value += ' !important';
							}

							var result = this.css('', [hTag], node, 'font-size', value);
							if(result)
							{
								resultList = result.split(' ');
								result = resultList[0];
							}

							return result;
						}
					};

					handlerList['bx-stylist-' + hTag + '-font-weight'] = {
						'className': '',
						'func': function (node, param, value) {
							if(value)
							{
								value += ' !important';
							}

							var result = this.css('', [hTag], node, 'font-weight', value);
							if(result)
							{
								resultList = result.split(' ');
								result = resultList[0];
							}

							return result;
						}
					};

					handlerList['bx-stylist-' + hTag + '-font-weight'] = {
						'className': '',
						'func': function (node, param, value) {
							if(value)
							{
								value += ' !important';
							}

							var result = this.css('', [hTag], node, 'font-weight', value);
							if(result)
							{
								resultList = result.split(' ');
								result = resultList[0];
							}

							return result;
						}
					};

					handlerList['bx-stylist-' + hTag + '-line-height'] = {
						'className': '',
						'func': function (node, param, value) {
							if(value)
							{
								value += ' !important';
							}

							var result = this.css('', [hTag], node, 'line-height', value);
							if(result)
							{
								resultList = result.split(' ');
								result = resultList[0];
							}

							return result;
						}
					};

					handlerList['bx-stylist-' + hTag + '-text-align'] = {
						'className': '',
						'func': function (node, param, value) {
							if(value)
							{
								value += ' !important';
							}

							var result = this.css('', [hTag], node, 'text-align', value);
							if(result)
							{
								resultList = result.split(' ');
								result = resultList[0];
							}

							return result;
						}
					};

					return handlerList;
				}
			})();
			for(var i = 1; i <= 4; i++)
			{
				this.editHandlerList = getHTagHandler(this.editHandlerList, 'h' + i);
			}
		}
		else
		{
			this.editHandlerList = {
				'bx-stylist-bgcolor': {
					'className': '',
					'func': function(node, param, value){
						return this.css('', [''], node, 'background', value);
					}
				},
				'bx-stylist-padding-top': {
					'className': '',
					'func': function(node, param, value){
						return this.css('', [''], node, 'padding-top', value);
					}
				},
				'bx-stylist-padding-bottom': {
					'className': '',
					'func': function(node, param, value){
						return this.css('', [''], node, 'padding-bottom', value);
					}
				},


				'bx-stylist-text-color': {
					'className': '',
					'func': function(node, param, value){
						return this.cssTextBlock(node, 'color', value);
					}
				},
				'bx-stylist-text-font-family': {
					'className': '',
					'func': function(node, param, value){
						return this.cssTextBlock(node, 'font-family', value);
					}
				},
				'bx-stylist-text-font-size': {
					'className': '',
					'func': function(node, param, value){
						return this.cssTextBlock(node, 'font-size', value);
					}
				},
				'bx-stylist-text-font-weight': {
					'className': '',
					'func': function(node, param, value){
						return this.cssTextBlock(node, 'font-weight', value);
					}
				},
				'bx-stylist-text-line-height': {
					'className': '',
					'func': function(node, param, value){
						return this.cssTextBlock(node, 'line-height', value);
					}
				},
				'bx-stylist-text-text-align': {
					'className': '',
					'func': function(node, param, value){
						return this.cssTextBlock(node, 'text-align', value);
					}
				},


				'bx-stylist-a-color': {
					'className': '',
					'func': function(node, param, value){
						return this.cssATag(node, 'color', value);
					}
				},
				'bx-stylist-a-font-weight': {
					'className': '',
					'func': function(node, param, value){
						return this.cssATag(node, 'font-weight', value);
					}
				},
				'bx-stylist-a-text-decoration': {
					'className': '',
					'func': function(node, param, value){
						return this.cssATag(node, 'text-decoration', value);
					}
				}
			};
		}
	};

	this.getSelector = function(selectorList)
	{
		var type = this.type;
		type = type[0].toUpperCase() + type.substring(1);

		var finalSelectorList = [];
		for(var i in selectorList)
		{
			var selector = selectorList[i];
			if(this.type == 'page')
			{
				finalSelectorList.push('body' + (selector ? ' ' : '') + selector);
			}

			finalSelectorList.push('#' + this.caller.CONST_ATTR_ID_STYLIST + type + (selector ? ' ' : '') + selector);
		}

		return finalSelectorList.join(', ');
	};

	this.cssHeadTag = function(head, node, param, value)
	{
		return this.css('', [head], node, param, value);
	};

	this.css = function(media, selectorList, node, param, value)
	{
		var selector = this.getSelector(selectorList);
		var cssText;
		if(typeof(value) !== "undefined")
		{
			var changeParams = {};
			changeParams[param] = value;

			cssText = node.innerHTML;
			if(node.styleSheet)
			{
				cssText = node.styleSheet.cssText;
			}
			cssText = this.caller.cssParser.setStyle(cssText, media, selector, changeParams);

			node.innerHTML =  cssText;
			if(node.styleSheet)
			{
				node.styleSheet.cssText = cssText;
			}
		}

		cssText = node.innerHTML;
		if(node.styleSheet)
		{
			cssText = node.styleSheet.cssText;
		}
		var changeResult = this.caller.cssParser.getStyle(cssText, media, selector, [param]);
		return changeResult[param];
	};
	this.cssTextBlock = function(node, param, value)
	{
		return this.css('', this.selectorTextList, node, param, value);
	};

	this.cssATag = function(node, param, value)
	{
		return this.css('', this.selectorAList, node, param, value);
	};
}
BX.extend(BXBlockEditorStylist, BXBlockEditorBlock);


function BXBlockEditorPHPParser(params)
{
	this.phpList = [];
	this.htmlEditor = params.htmlEditor;

	this.componentPropertiesDialogHandler = null;
	this.componentPropertiesDialogHandlerCancel = null;
	BX.addCustomEvent(this.htmlEditor, 'OnContentChanged', BX.delegate(this.handleComponentPropertiesChange, this));
}
BXBlockEditorPHPParser.prototype.handleComponentPropertiesClose = function()
{
	if(BX.type.isFunction(this.componentPropertiesDialogHandlerCancel))
	{
		this.componentPropertiesDialogHandlerCancel.call(this);
	}

	this.componentPropertiesDialogHandler = null;
	this.componentPropertiesDialogHandlerCancel = null;
};
BXBlockEditorPHPParser.prototype.handleComponentPropertiesChange = function(html)
{
	if(BX.type.isFunction(this.componentPropertiesDialogHandler))
	{
		this.componentPropertiesDialogHandler.call(this, html);
	}

	this.componentPropertiesDialogHandler = null;
	this.componentPropertiesDialogHandlerCancel = null;
};
BXBlockEditorPHPParser.prototype.showComponentPropertiesDialog = function(html, handlerSave, handlerCancel)
{
	if(!this.htmlEditor.components.IsComponent(html))
	{
		return;
	}

	this.htmlEditor.SetContent(html, true);
	var surrogateList = this.htmlEditor.phpParser.GetAllSurrogates();
	if(!surrogateList[0])
	{
		return;
	}

	var surrogate = surrogateList[0];
	var origTag = this.htmlEditor.GetBxTag(surrogate.node);
	var bxTag = this.htmlEditor.GetBxTag(origTag.surrogateId);

	this.componentPropertiesDialogHandler = handlerSave;
	this.componentPropertiesDialogHandlerCancel = handlerCancel;
	this.htmlEditor.components.ShowPropertiesDialog(origTag.params, bxTag);
	BX.addCustomEvent(
		this.htmlEditor.components.oPropertiesDialog.oDialog,
		"onWindowUnRegister",
		BX.proxy(this.handleComponentPropertiesClose, this)
	);
};
BXBlockEditorPHPParser.prototype.replaceLayoutBySurrogate = function(context)
{
	for(var i in this.phpList)
	{
		var item = this.phpList[i];
		var phpNode = BX.findChild(context, {'attribute': {'id': item.id}}, true);
		if(phpNode)
		{
			phpNode.outerHTML = '#' + item.id + '#';
		}
	}
};

BXBlockEditorPHPParser.prototype.replaceSurrogateByPhp = function(text)
{
	var result = text;
	for(var i in this.phpList)
	{
		var item = this.phpList[i];
		result = result.replace('#' + item.id + '#', item.content);
	}

	return result;
};

BXBlockEditorPHPParser.prototype.replaceLayoutByPhp = function(text)
{
	var result = text;

	var len = this.phpList.length;
	for(var i = len - 1; i >= 0; i--)
	{
		var item = this.phpList[i];
		result = result.replace(item.layout, item.content);
	}

	return result;
};

BXBlockEditorPHPParser.prototype.resetItems = function()
{
	this.phpList = [];
};

BXBlockEditorPHPParser.prototype.addItem = function(id, phpSlice, layout)
{
	this.phpList.push({
		'id': id,
		'content': phpSlice,
		'layout': layout
	});
};

BXBlockEditorPHPParser.prototype.getAttrName = function()
{
	return 'data-bx-editor-php-slice';
};

BXBlockEditorPHPParser.prototype.getPhpSliceDescription = function(phpSlice)
{
	var result = {'name': 'PHP', 'title': 'PHP'};
	var component = this.htmlEditor.components.IsComponent(phpSlice);
	if(component && this.htmlEditor.components.components)
	{
		var cData = this.htmlEditor.components.GetComponentData(component.name);
		var name = cData.title || component.name;
		var title = (cData.params && cData.params.DESCRIPTION) ? cData.params.DESCRIPTION : title;
		result = {'name': name, 'title': title};
	}

	return result;
};
BXBlockEditorPHPParser.prototype.replacePhpByLayout = function(text)
{
	var parsedText = this.htmlEditor.phpParser.ReplacePhpBySymCode(text);

	for(var ind in this.htmlEditor.phpParser.arScripts)
	{
		var phpPattern = this.htmlEditor.phpParser.GetPhpPattern(ind);
		var id = 'bx_block_php_' + Math.random();
		var phpSlice = this.htmlEditor.phpParser.arScripts[ind];
		var phpDesc = this.getPhpSliceDescription(phpSlice);

		var encodedPhpSlice = phpSlice.replace(/&/g, '&amp;').replace(/"/g, '&quot;') // BX.util.htmlspecialchars(phpSlice)
		var replaceTo = '<span id="'+id+'" ' + this.getAttrName() + '="' + encodedPhpSlice + '" class="bxhtmled-surrogate" title="'+phpDesc.title+'">'+phpDesc.name+'</span>';
		this.addItem(id, phpSlice, replaceTo);
		parsedText = parsedText.replace(phpPattern, replaceTo);
	}

	return parsedText;
};
BXBlockEditorPHPParser.prototype.getComponentInclude = function(componentName, asLayout)
{
	asLayout = asLayout || false;
	var includeText = this.htmlEditor.components.GetOnDropHtml({'name': componentName});
	if(asLayout)
	{
		includeText = this.replacePhpByLayout(includeText);
	}

	return includeText;
};