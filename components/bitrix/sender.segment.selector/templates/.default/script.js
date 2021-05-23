;(function ()
{
	BX.namespace('BX.Sender.Segment');
	if (BX.Sender.Segment.Selector)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * Selector.
	 *
	 */
	function Selector(params)
	{
		this.init(params);
	}
	Selector.prototype.init = function (params)
	{
		this.manager = params.manager;
		this.id = params.id;
		this.pathToAdd = params.pathToAdd;
		this.pathToEdit = params.pathToEdit;
		this.context = BX(params.containerId);
		this.actionUri = params.actionUri;
		this.include = params.include;
		this.messageCode = params.messageCode;
		this.mess = params.mess || {searchTitle: ''};

		this.popupContent = Helper.getNode('popup-content', this.context);

		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		this.initSelector();
		Helper.hint.init(this.context);
	};
	Selector.prototype.initSelector = function ()
	{
		this.selector = BX.Sender.UI.TileSelector.getById(this.id);
		if (!this.selector)
		{
			throw new Error('Tile selector `' + this.id + '` not found.');
		}
		BX.addCustomEvent(this.selector, this.selector.events.buttonSelect, this.onButtonSelect.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.buttonSelectFirst, this.onButtonSelectFirst.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.buttonAdd, this.onButtonAdd.bind(this));

		BX.addCustomEvent(this.selector, this.selector.events.tileClick, this.onTileClick.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileRemove, this.fireChangeCount.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileEdit, this.fireChangeCount.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileAdd, this.fireChangeCount.bind(this));

		BX.addCustomEvent(this.selector, this.selector.events.input, this.onInput.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.search, this.onSearch.bind(this));
	};
	Selector.prototype.onButtonSelect = function ()
	{
		this.selector.showSearcher(this.mess.searchTitle);
	};
	Selector.prototype.onButtonSelectFirst = function ()
	{
		var selector = this.selector;
		this.ajaxAction.request({
			action: 'getSegments',
			onsuccess: function (data)
			{
				selector.setSearcherData(data.list || []);
			},
			onfailure: selector.hideSearcher.bind(selector),
			data: {
				'include': this.include ? 'Y' : 'N',
				'messageCode': this.messageCode
			}
		});
	};
	Selector.prototype.onButtonAdd = function ()
	{
		this.manager.currentAddSelector = this;
		BX.Sender.Page.open(BX.util.add_url_param(this.pathToAdd, {'hidden': 'Y'}));
	};
	Selector.prototype.onTileClick = function (tile)
	{
		BX.Sender.Page.open(this.pathToEdit.replace('#id#', tile.id));
	};
	Selector.prototype.onInput = function (value)
	{
	};
	Selector.prototype.onSearch = function (value)
	{
	};
	Selector.prototype.getCount = function (typeId)
	{
		return this.selector.getTilesData().reduce(function (accum, data) {
			return accum + ((data && data.count && data.count[typeId]) ? parseInt(data.count[typeId]) : 0);
		}, 0);
	};
	Selector.prototype.getDuration = function ()
	{
		return this.selector.getTilesData().reduce(function (accum, data) {
			return accum + ((data && data.duration) ? parseInt(data.duration) : 0);
		}, 0);
	};
	Selector.prototype.fireChangeCount = function ()
	{
		this.fire('change-count');
	};
	Selector.prototype.fire = function (eventName, parameters)
	{
		parameters = parameters || {};
		BX.onCustomEvent(this, eventName, parameters);
	};
	Selector.prototype.actualizeTiles = function (segment, needAdd)
	{
		var tile = this.selector.getTile(segment.id);
		if (tile)
		{
			this.selector.updateTile(tile, segment.name, segment.data, segment.bgcolor, segment.color);
		}
		else if (needAdd)
		{
			this.selector.addTile(segment.name, segment.data, segment.id, segment.bgcolor, segment.color);
		}
	};
	Selector.prototype.actualize = function (segment, isAddTile)
	{
		this.selector.clearSearcher();
		this.actualizeTiles(segment, isAddTile);
	};


	/**
	 * Manager.
	 *
	 */
	function Manager(params)
	{
		this.init(params);
	}
	Manager.prototype.classNameExcludeBtnActive = 'sender-segment-selector-link-active';
	Manager.prototype.init = function (params)
	{
		this.id = params.id;
		this.pathToAdd = params.pathToAdd;
		this.pathToEdit = params.pathToEdit;
		this.context = BX(params.containerId);
		this.actionUri = params.actionUri;
		this.messageCode = params.messageCode;
		this.recipientCount = params.recipientCount;
		this.recipientTypes = params.recipientTypes || [];
		this.mess = params.mess;

		this.duration = params.duration || {};
		this.duration.node = Helper.getNode('duration', this.context);
		this.duration.nodeText = Helper.getNode('duration-text', this.duration.node);

		this.counter = Helper.getNode('counter', this.context);
		this.excludeAddButton = Helper.getNode('exclude-add-button', this.context);
		this.excludeRemoveButton = Helper.getNode('exclude-remove-button', this.context);
		this.excludeContainer = Helper.getNode('exclude-container', this.context);

		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		this.initSelectors(params);

		this.initExcludeButtons();
		if (this.recipientCount === null)
		{
			this.calculate();
		}

		top.BX.addCustomEvent(top, 'sender-segment-edit-change', this.onSegmentChange.bind(this));

		if (BX.Sender.Template && BX.Sender.Template.Selector)
		{
			var selector = BX.Sender.Template.Selector;
			BX.addCustomEvent(selector, selector.events.templateSelect, this.onTemplateSelect.bind(this));
		}
	};
	Manager.prototype.onTemplateSelect = function (templateData)
	{
		if (!templateData.segments || templateData.segments.length === 0)
		{
			return;
		}

		this.selectorInclude.selector.removeTiles();
		templateData.segments.forEach(function (tile) {
			this.selectorInclude.selector.addTile(tile.name, tile.data, tile.id);
		}, this);
	};
	Manager.prototype.onSegmentChange = function (segmentData)
	{
		var current = this.currentAddSelector;
		var isInclude = current === this.selectorInclude;

		this.selectorInclude.actualize(segmentData, (isInclude && current));
		this.selectorExclude.actualize(segmentData, (!isInclude && current));

		this.currentAddSelector = null;
	};
	Manager.prototype.initExcludeButtons = function ()
	{
		BX.bind(this.excludeAddButton, 'click', this.onExcludeAddButtonClick.bind(this));
		BX.bind(this.excludeRemoveButton, 'click', this.onExcludeRemoveButtonClick.bind(this));
	};
	Manager.prototype.onExcludeAddButtonClick = function ()
	{
		Helper.display.change(this.excludeContainer, true);
		Helper.changeClass(this.excludeAddButton, this.classNameExcludeBtnActive, false);
	};
	Manager.prototype.onExcludeRemoveButtonClick = function ()
	{
		Helper.display.change(this.excludeContainer, false);
		Helper.changeClass(this.excludeAddButton, this.classNameExcludeBtnActive, true);
		this.selectorExclude.selector.removeTiles();
	};
	Manager.prototype.initSelectors = function (params)
	{
		this.currentAddSelector = null;

		var paramsInclude = BX.clone(params);
		paramsInclude.manager = this;
		paramsInclude.include = true;
		paramsInclude.id = 'segment-include';
		this.selectorInclude = new Selector(paramsInclude);
		BX.addCustomEvent(this.selectorInclude, 'change-count', this.calculate.bind(this));

		var paramsExclude = BX.clone(params);
		paramsExclude.manager = this;
		paramsExclude.include = false;
		paramsExclude.id = 'segment-exclude';
		this.selectorExclude = new Selector(paramsExclude);
	};
	Manager.prototype.calculate = function ()
	{
		this.calculateCount();
		this.calculateDuration();
	};
	Manager.prototype.calculateCount = function ()
	{
		var count = this.recipientTypes
			.map(function (typeId) {
				return this.selectorInclude.getCount(typeId);
			}, this)
			.reduce(function (a, b) {
				return a + b;
			}, 0);
		Helper.animate.numbers(this.counter, count);
	};
	Manager.prototype.calculateDuration = function ()
	{
		var min = this.duration.minimalInterval;
		var max = this.duration.maximalInterval;
		var warn = this.duration.warnInterval;
		var durationText;
		var duration = this.selectorInclude.getDuration();
		if (duration < min)
		{
			durationText = this.duration.formattedMinimalInterval;
		}
		else if (duration > max)
		{
			durationText = this.duration.formattedMaximalInterval;
		}
		else
		{
			duration = Math.round(duration / (min)) * min;
			var finishDate = new Date(Date.now() - duration * 1000);
			durationText = BX.date.format('Hdiff', finishDate);
		}

		this.duration.nodeText.textContent = durationText;
		Helper.changeClass(
			this.duration.node,
			'sender-segment-selector-duration-active',
			true
		);
		Helper.changeClass(
			this.duration.nodeText,
			'sender-segment-selector-duration-warn',
			duration > warn
		);
	};


	BX.Sender.Segment.SelectorManager = Manager;

})(window);