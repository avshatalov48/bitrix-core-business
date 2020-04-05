;(function() {

"use strict";

BX.namespace("BX.im.list.queue");


/**
 *
 * @param {{
 * 		view: BX.im.list,
 * 	}} options
 * @constructor
 */
BX.im.list.queue = function(options)
{
	options = options || {};

	this.listInstance = options.list;

	this.TYPE_ALL = 'all';
	this.TYPE_ADD = 'add';
	this.TYPE_UPDATE = 'update';

	this.list = {};
	this.list[this.TYPE_ADD] = {};
	this.list[this.TYPE_UPDATE] = {};

	this.pause = false;

	this.updateInterval = 1000;

	if (!this.listInstance)
	{
		clearInterval(this.updateIntervalId);
		this.updateIntervalId = setInterval(this.worker.bind(this), this.updateInterval);
	}
};

BX.im.list.queue.prototype.add = function(type, id, element)
{
	if (!this.listInstance)
	{
		return false;
	}

	if (type == this.TYPE_ALL)
	{
		return false;
	}

	this.list[type][id] = element;

	return true;
};

BX.im.list.queue.prototype.delete = function(type, id)
{
	if (!this.listInstance)
	{
		return false;
	}

	if (type == this.TYPE_ALL)
	{
		delete this.list[this.TYPE_ADD][id];
		delete this.list[this.TYPE_UPDATE][id];
	}
	else
	{
		delete this.list[type][id];
	}

	return true;
};

BX.im.list.queue.prototype.clear = function()
{
	if (!this.listInstance)
	{
		return false;
	}

	this.list[this.TYPE_ADD] = {};
	this.list[this.TYPE_UPDATE] = {};

	return true;
};
BX.im.list.queue.prototype.pause = function()
{
	this.pause = true;
	return true;
};
BX.im.list.queue.prototype.continue = function()
{
	this.pause = false;
	return true;
};

BX.im.list.queue.prototype.worker = function()
{
	if (!this.listInstance || this.pause)
	{
		return false;
	}

	var executeTime = new Date();

	var listAdd = [];
	for (var id in this.list[this.TYPE_ADD])
	{
		if(!this.list[this.TYPE_ADD].hasOwnProperty(id))
		{
			continue;
		}
		listAdd.push(this.list[this.TYPE_ADD][id]);
		delete this.list[this.TYPE_ADD][id];
	}
	if (listAdd.length > 0)
	{
		this.listInstance.addItems(listAdd);
	}

	var listUpdate = [];
	for (var id in this.list[this.TYPE_UPDATE])
	{
		if(!this.list[this.TYPE_UPDATE].hasOwnProperty(id))
		{
			continue;
		}
		listUpdate.push({
			filter: {"params.id" : this.list[this.TYPE_UPDATE][id]['id']},
			element: this.list[this.TYPE_UPDATE][id]
		});
		delete this.list[this.TYPE_UPDATE][id];
	}
	if (listUpdate.length > 0)
	{
		this.listInstance.updateItems(listUpdate);
	}

	if (listAdd.length > 0 || listUpdate.length > 0)
	{
		console.info('BX.im.list.queue.worker: added - '+listAdd.length+' / updated - '+listUpdate.length+' ('+(new Date() - executeTime)+'ms)', {add: listAdd, update: listUpdate});
	}

	return true;
};


BX.im.list.queue.prototype.destroy = function()
{
	if (!this.listInstance)
	{
		return false;
	}

	clearInterval(this.updateIntervalId);

	return true;
};

})();