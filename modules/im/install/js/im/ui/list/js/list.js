;(function() {

"use strict";

BX.namespace("BX.im.list");

/**
 *
 * @param {object} options
 * @constructor
 */
BX.im.list = function(options)
{
	options = options || {};

	this.const = {
		DEFAULT_SECTION_CODE : "_default",
		POSITION_NEW : "new"
	};

	this.sections = [];
	this.sectionItems = {};

	this.items = {};
	this.itemCount = 0;

	this._defaultSectionAdd();

	this.eventThrow = function(eventName, params) {};
	this.drawFunction = function(item) {};

	this.queue = options.queue === true? new BX.im.list.queue({list: this}): null;

	options.markup = options.markup || {};
	options.markup.list = this;
	options.markup.container = options.container;

	this.markup = new BX.im.list.markup(options.markup);
};

BX.im.list.supportNaturalSort = function()
{
	try {'a'.localeCompare('b', 'i');} catch (e) {return e.name === 'RangeError';}
	return false;
};

BX.im.list.prototype.setItems = function(items, sections)
{
	this.removeAllItems();

	if (sections)
	{
		this.setSections(sections);
	}

	this.addItems(items, {incrementPosition: true, skipStub: true, skipRender: true});
	this.markup.drawItemsStub();

	if (this.queue)
	{
		this.queue.worker();
	}

	return true;
};

BX.im.list.prototype.findItem = function(filter)
{
	if (!BX.type.isPlainObject(filter))
		return false;

	var items = this.findItems(filter);

	return items.length > 0? items[0]: null;
};

BX.im.list.prototype.findItems = function(filter)
{
	if (!BX.type.isPlainObject(filter))
		return false;

	var items = [];
	if (typeof filter.id != 'undefined')
	{
		if (this.items[filter.id])
		{
			items.push(this.items[filter.id]);
		}
	}
	else if (typeof filter.title != 'undefined')
	{
		for (var id in this.items)
		{
			if (!this.items.hasOwnProperty(id))
				continue;

			if (this.items[id].title.toUpperCase().indexOf(filter.title.toUpperCase()) == 0)
			{
				items.push(this.items[id]);
			}
		}
	}
	else
	{
		for (var fieldName in filter)
		{
			if (!filter.hasOwnProperty(fieldName))
				continue;

			var filterId = fieldName.split('.')[0];

			for (var id in this.items)
			{
				if (!this.items.hasOwnProperty(id))
					continue;

				if (
					this.items[id][filterId]
					&& this.items[id][filterId]['_'+fieldName]
					&& this.items[id][filterId]['_'+fieldName] == filter[fieldName]
				)
				{
					items.push(this.items[id]);
				}
			}

			break;
		}
	}

	return items;
};

BX.im.list.prototype.addItem = function(item)
{
	return this.addItems([item]);
};

BX.im.list.prototype.addItems = function(items, options)
{
	options = options || {};

	if (!this.queue || options.skipQueue)
	{
		var sortSections = [];
		var sortParams = {};

		items.forEach(function(item)
		{
			item = this._prepareAdd(item);
			if (!item || this.items[item.id])
			{
				return false;
			}

			if (item._options)
			{
				options = BX.util.objectClone(item._options);
				delete item._options;
			}

			if (!sortParams[item.sectionCode])
			{
				var section = this.sections.find(function(section){
					return section.id == this.sectionCode;
				}, item);
				if (section && section.sort.enable)
				{
					sortParams[item.sectionCode] = section.sort;
				}
			}
			if (sortParams[item.sectionCode] && sortParams[item.sectionCode].enable)
			{
				sortSections.push(item.sectionCode);
			}

			this.items[item.id] = item;
			this.itemCount++;

			item._position = !options.incrementPosition? this.const.POSITION_NEW: this.sectionItems[item.sectionCode].length;
			this.sectionItems[item.sectionCode].push(item);

			return true;
		}.bind(this));

		sortSections.forEach(function(sectionCode){
			this._sortItems(sectionCode);
		}, this);
	}
	else
	{
		items.forEach(function(item){
			item._options = BX.util.objectClone(options);
			this.queue.add(item);
		}.bind(this));
	}

};

BX.im.list.prototype.updateItem = function(filter, element)
{
	return this.updateItems([{
		'filter': filter,
		'element': element
	}])
};

BX.im.list.prototype.updateItems = function(data)
{
	var sortSections = [];
	var sortParams = {};
	data.forEach(function(element){
		this.findItems(element.filter).forEach(function(item) {
			if (BX.type.isPlainObject(element.element))
			{
				if (element.element.sectionCode && element.element.sectionCode != item.sectionCode)
				{
					sortSections.push(item.sectionCode);
					sortSections.push(element.element.sectionCode);
				}
				else if (!sortParams[item.sectionCode])
				{
					var section = this.sections.find(function(section){
						return section.id == this.sectionCode;
					}, item);
					if (section && section.sort.enable)
					{
						sortParams[item.sectionCode] = section.sort;
					}
					if (sortParams[item.sectionCode] && sortParams[item.sectionCode].enable && (typeof element.element[sortParams[item.sectionCode].field] != 'undefined'))
					{
						sortSections.push(item.sectionCode);
					}
				}

				this.items[item.id] = BX.util.objectMerge(item, this._prepareItem(element.element));
			}
		}, this);
	}.bind(this));

	sortSections.forEach(function(sectionCode){
		this._sortItems(sectionCode);
	}, this);
};

BX.im.list.prototype.removeItem = function(filter)
{
	return this.removeItems([filter])
};

BX.im.list.prototype.removeItems = function(filters)
{
	var sortSections = [];
	var sortParams = {};

	filters.forEach(function(filter){
		this.findItems(filter).forEach(function(item) {
			if (!sortParams[item.sectionCode])
			{
				var section = this.sections.find(function(section){
					return section.id == this.sectionCode;
				}, item);
				if (section && section.sort.enable)
				{
					sortParams[item.sectionCode] = section.sort;
				}
			}
			if (sortParams[item.sectionCode] && sortParams[item.sectionCode].enable)
			{
				sortSections.push(item.sectionCode);
			}

			this.markup.change(this.markup.const.TYPE_DELETE, {
				item: item,
				section: sortParams[item.sectionCode],
				position: {
					current : -1,
					previous : item._position,
					diff : 0
				},
			});
			this.sectionItems[item.sectionCode].splice(this.sectionItems[item.sectionCode].indexOf(item), 1);
			delete this.items[item.id];
			this.itemCount--;
		}, this);
	}.bind(this));

	sortSections.forEach(function(sectionCode){
		this._sortItems(sectionCode);
	}, this);
};

BX.im.list.prototype.removeAllItems = function()
{
	this.sectionItems = {};

	this.items = {};
	this.itemCount = 0;
};

BX.im.list.prototype.setSections = function(sections)
{
	if (typeof sections == 'undefined')
	{
		return false;
	}
	else if (typeof sections == 'boolean' && sections === false)
	{
		this.sections.forEach(function(element, index)
		{
			if (element.id != this.const.DEFAULT_SECTION_CODE)
			{
				this.sections.splice(index, 1);
			}
		}, this);

		if (this.sections.length == 0)
		{
			this._defaultSectionAdd();
		}

		return true;
	}

	var sectionList = [];
	sections.forEach(function(section)
	{
		if (!section.id)
			return false;

		var currentSection = this.sections.find(function(element){
			return element.id == this.id;
		}, section);
		if (currentSection)
		{
			if (typeof section.title != 'undefined')
			{
				currentSection.title = section.title;
			}

			if (section.backgroundColor && section.backgroundColor.match(/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/))
			{
			}
			else if (typeof section.backgroundColor != 'undefined')
			{
				currentSection.backgroundColor = 'transparent';
			}

			if (typeof section.sortItemParams != 'undefined')
			{
				this.setSectionSort(section.id, section.sortItemParams)
			}
		}
		else
		{
			this.sections.push({
				id: section.id,
				title: section.title.toString(),
				backgroundColor: section.backgroundColor && section.backgroundColor.match(/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/)? section.backgroundColor: 'transparent',
				sort: false,
				_nodes: {}
			});
			this.setSectionSort(section.id, section.sortItemParams)
		}

		sectionList.push(section.id);

		return true;
	}, this);

	this.sections = this.sections.filter(function(element){
		return sectionList.indexOf(element.id) > -1;
	}, sectionList);

	if (this.sections.length == 0)
	{
		this._defaultSectionAdd();
	}

	this.markup.drawSections();

	return true;
};

BX.im.list.prototype.setListener = function(listener)
{
	if (typeof listener != 'function')
		return false;

	this.throwEventFunction = listener;

	return true;
};

BX.im.list.prototype.setSectionSort = function(sectionCode, params)
{
	return this.setSort(params, sectionCode);
};

BX.im.list.prototype.setSort = function(params, sectionCode)
{
	sectionCode = sectionCode || this.const.DEFAULT_SECTION_CODE;

	var section = this.sections.find(function(element){
		return element.id == this;
	}, sectionCode);
	if (!section)
	{
		return false;
	}

	if (typeof params == "boolean" && params === false || !params)
	{
		section.sort = this._defaultSortParams();
		return true;
	}

	for (var paramName in params)
	{
		if (params.hasOwnProperty(paramName))
		{
			section.sort = {
				enable : true,
				field : paramName,
				order : params[paramName].toString().toLowerCase() == "desc"? "desc": "asc"
			};

			this._sortItems(sectionCode);

			return true;
		}
	}

	return true;
};

BX.im.list.prototype._defaultSectionAdd = function()
{
	this.sections.push({
		id: this.const.DEFAULT_SECTION_CODE,
		title: '',
		backgroundColor: 'transparent',
		sort: this._defaultSortParams(),
		_nodes: {}
	});

	return true;
};

BX.im.list.prototype._defaultSortParams = function()
{
	return {
		enable : false,
		field : "id",
		order : "asc"
	};
};

BX.im.list.prototype._sortItems = function(sectionCode)
{
	if (!this.sectionItems[sectionCode] || this.sectionItems[sectionCode].length <= 0)
		return true;

	var section = this.sections.find(function(element){
		return element.id == this;
	}, sectionCode);

	if (!section || !section.sort.enable)
		return true;

	section._positionChanged = false;
	this.sectionItems[sectionCode].sort(function(a, b)
	{
		var result;

		if (!a.sortValues[section.sort.field] || !a.sortValues[section.sort.field])
		{
			result = 0;
		}
		else if (BX.im.list.supportNaturalSort())
		{
			result = a.sortValues[section.sort.field].toString().localeCompare(b.sortValues[section.sort.field].toString(), undefined, {numeric: true, sensitivity: 'base'});
			if (!this.sectionItemsPositionChanged && result != 0)
			{
				this.sectionItemsPositionChanged = true;
			}
			result = section.sort.order == "asc"? result: result*-1;
		}
		else if (a.sortValues[section.sort.field] > b.sortValues[section.sort.field])
		{
			result = section.sort.order == "asc"? 1: -1;
		}
		else if (a.sortValues[section.sort.field] < b.sortValues[section.sort.field])
		{
			result = section.sort.order == "asc"? -1: 1;
		}
		else
		{
			if (section.sort.field != 'id')
			{
				if (a.sortValues.id > b.sortValues.id)
				{
					result = section.sort.order == "asc"? 1: -1;
				}
				else if (a.sortValues.id < b.sortValues.id)
				{
					result = section.sort.order == "asc"? -1: 1;
				}
				else
				{
					result = 0;
				}
			}
			else
			{
				result = 0;
			}
		}

		if (!this._positionChanged && result != 0)
		{
			this._positionChanged = true;
		}

		return result;
	}.bind(section));

	if (section._positionChanged)
	{
		this._indexItems(sectionCode);
	}
	delete section._positionChanged;

	return true;
};

BX.im.list.prototype._indexItems = function(sectionCode)
{
	if (!this.sectionItems[sectionCode] || this.sectionItems[sectionCode].length <= 0)
		return true;

	var section = this.sections.find(function(section){return section.id == this}, sectionCode);

	this.sectionItems[sectionCode].forEach(function(item, index){
		if (item._position != index)
		{
			var diff = 0;
			var type = this.markup.const.TYPE_UPDATE;
			if (item._position == this.const.POSITION_NEW)
			{
				type = this.markup.const.TYPE_ADD;
			}
			else
			{
				diff = index-item._position
			}

			this.markup.change(type, {
				item: item,
				section: section,
				position: {
					current : index,
					previous : item._position,
					diff : diff
				},
			});

			item._position = index;
		}
	}, this);

	return true;
};

BX.im.list.prototype._prepareAdd = function(item)
{
	item = this._prepareItem(item, false);

	return item;
};

BX.im.list.prototype._prepareItem = function(item, skipIfUndefined)
{
	skipIfUndefined = skipIfUndefined !== false;

	if (item.title)
	{
		item.title = item.title.toString();
	}

	if (item.subtitle)
	{
		item.subtitle = item.subtitle.toString();
	}

	if (!item.sectionCode && !skipIfUndefined)
	{
		item.sectionCode = this.const.DEFAULT_SECTION_CODE;
	}

	if (item.sectionCode)
	{
		if (!this.sectionItems[item.sectionCode])
		{
			this.sectionItems[item.sectionCode] = [];
		}
	}

	if (item.backgroundColor && item.backgroundColor.match(/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?\b$/))
	{
	}
	else if (item.backgroundColor || !skipIfUndefined)
	{
		item.backgroundColor = '#556574';
	}

	if (!item.imageUrl && !skipIfUndefined)
	{
		item.imageUrl = '';
	}

	if (item.params && BX.type.isPlainObject(item.params))
	{
		for (var i in item.params)
		{
			if (item.params.hasOwnProperty(i))
			{
				item.params['_params.'+i] = item.params[i];
			}
		}
	}
	else if (item.params && skipIfUndefined)
	{
		delete item.params;
	}
	else if (!skipIfUndefined)
	{
		item.params = {};
	}

	if (item.sortValues && BX.type.isPlainObject(item.sortValues))
	{
		if (!item.sortValues.id)
		{
			item.sortValues.id = item.id;
		}
	}
	else if (item.sortValues && skipIfUndefined)
	{
		delete item.sortValues;
	}
	else if (!skipIfUndefined)
	{
		item.sortValues = {id: item.id};
	}

	if (item.styles && BX.type.isPlainObject(item.styles))
	{
		for (var i in item.styles)
		{
			if (!item.styles.hasOwnProperty(i))
				continue;

			if (!BX.type.isPlainObject(item.styles[i]))
			{
				delete item.styles[i];
			}
		}
	}
	else if (item.styles && skipIfUndefined)
	{
		delete item.styles;
	}
	else if (!skipIfUndefined)
	{
		item.styles = {};
	}

	return item;
};

})();