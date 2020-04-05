;(function() {

"use strict";

BX.namespace("BX.im.list.markup");


/**
 *
 * @param {{
 * 		view: BX.im.list,
 * 	}} options
 * @constructor
 */
BX.im.list.markup = function(options)
{
	options = options || {};

	this.controller = options.list;

	this.outerContainer = options.container;

	this.container = null;
	this.containerWrapper = null;

	this.cssClass = 'bx-im-ui-list-';

	this.const = {
		TYPE_ADD : 'add',
		TYPE_UPDATE : 'update',
		TYPE_DELETE : 'delete',

		BLOCK_HEIGHT_AUTO: 'auto',
		BLOCK_HEIGHT_DEFAULT: 'default',
	};

	this.queue = {};
	this.queue[this.const.TYPE_ADD] = [];
	this.queue[this.const.TYPE_UPDATE] = [];
	this.queue[this.const.TYPE_DELETE] = [];

	this.queueCount = 0;

	this.updateIntervalId = null;

	this.config = {};
	if (typeof options.sectionHeight == 'number')
	{
		this.config.sectionHeight = options.sectionHeight;
	}
	else
	{
		this.config.sectionHeight = this.const.BLOCK_HEIGHT_DEFAULT;
	}

	if (typeof options.itemHeight == 'number')
	{
		this.config.itemHeight = options.itemHeight;
	}
	else
	{
		this.config.itemHeight = options.itemHeight == this.const.BLOCK_HEIGHT_AUTO? this.const.BLOCK_HEIGHT_AUTO: this.const.BLOCK_HEIGHT_DEFAULT;

		if (this.config.itemHeight == this.const.BLOCK_HEIGHT_AUTO)
		{
			options.animation = false;
		}
	}

	this.config.animation = options.animation === true? new BX.im.list.animation({controller: this.controller, view: this}): null;

	this.drawGrid();
};

BX.im.list.markup.prototype.drawGrid = function()
{
	BX.cleanNode(this.outerContainer);

	this.container = BX.create('div', { props : { className : this.cssClass+"container" }, children: [
		this.containerWrapper = BX.create('div', { props : { className : this.cssClass+"wrapper" }})
	]});

	this.outerContainer.appendChild(this.container);

	return true;
};

BX.im.list.markup.prototype.drawSections = function()
{
	BX.cleanNode(this.containerWrapper);

	var node = document.createDocumentFragment();

	this.controller.sections.forEach(function(section){
		var sectionWrap = BX.create('div', { props : { className : this.cssClass+"section-wrapper" }, children: [
			section._nodes.box = BX.create('div', { props : { className : this.cssClass+"section-box"+(section.title.length > 0? "": " section-box-without-title") }, children: this.getSectionView(section)}),
			section._nodes.items = BX.create('div', { props : { className : this.cssClass+"section-items "+this.cssClass+"section-"+section.id+"-items"}}),
		]});
		node.appendChild(sectionWrap);
	}, this);

	this.drawItemsStub();

	this.containerWrapper.appendChild(node);
};


BX.im.list.markup.prototype.drawItemsStub = function()
{
	this.controller.sections.forEach(function(section){
		if (!section._nodes.items || !this.controller.sectionItems[section.id])
			return false;

		BX.cleanNode(section._nodes.items);

		section._nodes.itemsLenght = 0;

		var length = this.controller.sectionItems[section.id].length;
		for (var i = 0; i < length; i++)
		{
			section._nodes.items.appendChild(
				this.drawItemStub(i)
			);
			section._nodes.itemsLenght++;
		}

		return true;
	}, this);

	return true;
};

BX.im.list.markup.prototype.drawItemStub = function(position)
{
	var heightClass = "";
	var heightStyle = "";

	if (this.config.itemHeight == this.const.BLOCK_HEIGHT_DEFAULT)
	{
		heightClass = " "+this.cssClass+"item-size-default";
	}
	else if (this.config.itemHeight == this.const.BLOCK_HEIGHT_AUTO)
	{
		heightClass = " "+this.cssClass+"item-size-auto";
	}
	else
	{
		heightClass = " "+this.cssClass+"item-size-custom";
		heightStyle = "height: "+this.config.itemHeight+"px;";
	}

	return BX.create('div', { props : { className : this.cssClass+"item-position "+this.cssClass+"item-position-"+position+heightClass, style : heightStyle}, html: position});
};

BX.im.list.markup.prototype.getSectionView = function(section)
{
	if (section.title.length <= 0)
		return null;

	var heightClass = "";
	var heightStyle = "";

	if (this.config.itemHeight == this.const.BLOCK_HEIGHT_DEFAULT)
	{
		heightClass = " "+this.cssClass+"section-size-default";
	}
	else
	{
		heightClass = " "+this.cssClass+"section-size-custom";
		heightStyle = "height: "+this.config.itemHeight+"px;";
	}

	return [
		BX.create('div', { props : { className : this.cssClass+"section "+this.cssClass+"section-"+section.id+heightClass}, children: [
			BX.create('div', {html: section.id})
		]})
	]
};


BX.im.list.markup.prototype.change = function(type, params)
{
	var id = this.queueCount++;
	this.queue[type][id] = params;

	if (type == this.const.TYPE_ADD)
	{
		params.section._nodes.items.appendChild(
			this.drawItemStub(params.section._nodes.itemsLenght)
		);
		params.section._nodes.itemsLenght++;
	}

	console.warn(id, type, params.item.id, params.position);

	this._start();

	return true;
};

BX.im.list.markup.prototype.clear = function()
{
	this.queue[this.const.TYPE_ADD] = {};
	this.queue[this.const.TYPE_UPDATE] = {};
	this.queue[this.const.TYPE_DELETE] = {};

	this._stop();

	return true;
};

BX.im.list.markup.prototype.worker = function()
{
	if (this.pause)
	{
		return false;
	}

	var executeTime = new Date();

	var listAdd = [];
	for (var id in this.queue[this.const.TYPE_ADD])
	{
		if(!this.queue[this.const.TYPE_ADD].hasOwnProperty(id))
		{
			continue;
		}
		listAdd.push(this.queue[this.const.TYPE_ADD][id]);
		delete this.queue[this.const.TYPE_ADD][id];
	}
	if (listAdd.length > 0)
	{
		//this.listInstance.addItems(listAdd);
	}

	var listUpdate = [];
	for (var id in this.queue[this.const.TYPE_UPDATE])
	{
		if(!this.queue[this.const.TYPE_UPDATE].hasOwnProperty(id))
		{
			continue;
		}
		listUpdate.push(this.queue[this.const.TYPE_UPDATE][id]);
		delete this.queue[this.const.TYPE_UPDATE][id];
	}
	if (listUpdate.length > 0)
	{
		//this.listInstance.addItems(listAdd);
	}

	var listDelete = [];
	for (var id in this.queue[this.const.TYPE_DELETE])
	{
		if(!this.queue[this.const.TYPE_DELETE].hasOwnProperty(id))
		{
			continue;
		}
		listDelete.push(this.queue[this.const.TYPE_DELETE][id]);
		delete this.queue[this.const.TYPE_DELETE][id];
	}
	if (listDelete.length > 0)
	{
		//this.listInstance.addItems(listAdd);
	}

	if (listAdd.length + listUpdate.length + listDelete.length == 0)
	{
		this._stop();
	}
	else
	{
		console.info('BX.im.list.markup.worker: added - '+listAdd.length+' / updated - '+listUpdate.length+'  / deleted - '+listDelete.length+' ('+(new Date() - executeTime)+'ms)', {add: listAdd, update: listUpdate, delete: listDelete});
	}

	return true;
};


BX.im.list.markup.prototype.destroy = function()
{
	clearInterval(this.updateIntervalId);
	this.updateIntervalId = null;

	return true;
};

BX.im.list.markup.prototype._start = function()
{
	if (!this.updateIntervalId)
	{
		clearInterval(this.updateIntervalId);
		this.updateIntervalId = setInterval(this.worker.bind(this), 0);
	}
};

BX.im.list.markup.prototype._stop = function()
{
	return this.destroy();
};

})();