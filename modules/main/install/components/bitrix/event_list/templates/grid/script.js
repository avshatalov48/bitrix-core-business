BX.namespace("BX.Main.EventListUserSelector");
BX.Main.EventListUserSelector = function()
{
	this._id = '';
	this._search_input = null;
	this._data_input = null;
	this._componentName = '';
	this._componentContainer = null;
	this._componentObj = null;
	this._serviceContainer = null;
	this._zIndex = 0;
	this._dlg = null;
	this._dlgDisplayed = false;
	this._currentUser = {};

	this._searchKeyHandler = BX.delegate(this._handleSearchKey, this);
	this._searchFocusHandler = BX.delegate(this._handleSearchFocus, this);
	this._externalClickHandler = BX.delegate(this._handleExternalClick, this);
	this._clearButtonClickHandler = BX.delegate(this._hadleClearButtonClick, this);

	this._EventListUserSelectorInitCounter = 0;
};

BX.Main.EventListUserSelector.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : ('crm_user_search_popup_' + Math.random());

			if(!settings)
			{
				settings = {};
			}

			if(!BX.type.isElementNode(settings['searchInput']))
			{
				throw  "BX.Main.EventListUserSelector: 'search_input' is not defined!";
			}
			this._search_input = settings['searchInput'];

			this._clearButton = BX.findPreviousSibling(this._search_input, { className: "crm-filter-name-clean" });

			if(!BX.type.isElementNode(settings['dataInput']))
			{
				throw  "BX.Main.EventListUserSelector: 'data_input' is not defined!";
			}
			this._data_input = settings['dataInput'];

			if(!BX.type.isNotEmptyString(settings['componentName']))
			{
				throw  "BX.Main.EventListUserSelector: 'componentName' is not defined!";
			}

			this._currentUser = settings['user'] ? settings['user'] : {};
			this._componentName = settings['componentName'];
			this._componentContainer = BX(this._componentName + '_selector_content');

			this._initializeEventListUserSelector();
			this._adjustUser();

			this._serviceContainer = settings['serviceContainer'] ? settings['serviceContainer'] : document.body;
			this.setZIndex(settings['zIndex']);

			this._deleteButton = BX("flt_created_by_delete");
			BX.bind(this._deleteButton, "click", BX.proxy(function ()
			{
				this._search_input.value = "";
				this._data_input.value = "";
				this._deleteButton.style.display = "none";
			}, this));
		},
		_initializeEventListUserSelector: function()
		{
			var objName = 'O_' + this._componentName;
			if(!window[objName])
			{
				if(this._EventListUserSelectorInitCounter === 10)
				{
					throw "BX.Main.EventListUserSelector: Could not find '"+ objName +"' user selector!";
				}

				this._EventListUserSelectorInitCounter++;
				window.setTimeout(BX.delegate(this._initializeEventListUserSelector, this), 200);
				return;
			}

			this._componentObj = window[objName];
			this._componentObj.onSelect = BX.delegate(this._handleUserSelect, this);
			this._componentObj.searchInput = this._search_input;

			if(this._currentUser)
			{
				this._componentObj.setSelected([ this._currentUser ]);
			}

			BX.bind(this._search_input, 'keyup', this._searchKeyHandler);
			BX.bind(this._search_input, 'focus', this._searchFocusHandler);

			if(BX.type.isElementNode(this._clearButton))
			{
				BX.bind(this._clearButton, 'click', this._clearButtonClickHandler);
			}

			BX.bind(document, 'click', this._externalClickHandler);
		},
		open: function()
		{
			if (!BX.type.isDomNode(this._componentContainer))
				return;

			this._componentContainer.style.display = '';
			this._dlg = new BX.PopupWindow(
				this._id,
				this._search_input,
				{
					autoHide: false,
					draggable: false,
					closeByEsc: true,
					offsetLeft: 0,
					offsetTop: 0,
					zIndex: this._zIndex,
					bindOptions: { forceBindPosition: true },
					content : this._componentContainer,
					events:
						{
							onPopupShow: BX.delegate(
								function()
								{
									this._dlgDisplayed = true;
								},
								this
							),
							onPopupClose: BX.delegate(
								function()
								{
									this._dlgDisplayed = false;
									this._componentContainer.parentNode.removeChild(this._componentContainer);
									this._serviceContainer.appendChild(this._componentContainer);
									this._componentContainer.style.display = 'none';
									this._dlg.destroy();
								},
								this
							),
							onPopupDestroy: BX.delegate(
								function()
								{
									this._dlg = null;
								},
								this
							)
						}
				}
			);

			this._dlg.show();
		},
		_adjustUser: function()
		{
			if(parseInt(this._currentUser['id']) > 0)
			{
				this._data_input.value = this._currentUser['id'];
				this._search_input.value = this._currentUser['name'] ? this._currentUser.name : this._currentUser['id'];
				BX("flt_created_by_delete").style.display = "inline-block";
			}
			else
			{
				this._data_input.value = this._search_input.value = '';
				BX("flt_created_by_delete").style.display = "none";
			}
		},
		getZIndex: function()
		{
			return this._zIndex;
		},
		setZIndex: function(zIndex)
		{
			if(typeof(zIndex) === 'undefined' || zIndex === null)
			{
				zIndex = 0;
			}

			var i = parseInt(zIndex);
			this._zIndex = !isNaN(i) ? i : 0;
		},
		close: function()
		{
			if(this._dlg)
			{
				this._dlg.close();
			}
		},
		select: function(user)
		{
			this._currentUser = user;
			this._adjustUser();
			if(this._componentObj)
			{
				this._componentObj.setSelected([ user ]);
			}
		},
		_onBeforeDelete: function()
		{
			if(BX.type.isElementNode(this._search_input))
			{
				BX.unbind(this._search_input, 'keyup', this._searchKeyHandler);
				BX.unbind(this._search_input, 'focus', this._searchFocusHandler);
			}

			if(BX.type.isElementNode(this._clearButton))
			{
				BX.bind(this._clearButton, 'click', this._clearButtonClickHandler);
			}

			BX.unbind(document, 'click', this._externalClickHandler);

			if(this._componentContainer)
			{
				BX.remove(this._componentContainer);
				this._componentContainer = null;
			}
		},
		_hadleClearButtonClick: function(e)
		{
			this._data_input.value = this._search_input.value = '';
		},
		_handleExternalClick: function(e)
		{
			if(!e)
			{
				e = window.event;
			}

			if(!this._dlgDisplayed)
			{
				return;
			}

			var target = null;
			if(e)
			{
				if(e.target)
				{
					target = e.target;
				}
				else if(e.srcElement)
				{
					target = e.srcElement;
				}
			}

			if(target !== this._search_input &&
				!BX.findParent(target, { attribute:{ id: this._componentName + '_selector_content' } }))
			{
				this._adjustUser();
				this.close();
			}
		},
		_handleSearchKey: function(e)
		{
			if(!this._dlg || !this._dlgDisplayed)
			{
				this.open();
			}

			this._componentObj.search();
		},
		_handleSearchFocus: function(e)
		{
			if(!this._dlg || !this._dlgDisplayed)
			{
				this.open();
			}

			this._componentObj._onFocus(e);
		},
		_handleUserSelect: function(user)
		{
			this._currentUser = user;
			this._adjustUser();
			this.close();
		}
	};

BX.Main.EventListUserSelector.items = {};

BX.Main.EventListUserSelector.create = function(id, settings, delay)
{
	if(isNaN(delay))
	{
		delay = 0;
	}

	if(delay > 0)
	{
		window.setTimeout(
			function(){ BX.Main.EventListUserSelector.create(id, settings, 0); },
			delay
		);
		return null;
	}

	var self = new BX.Main.EventListUserSelector();
	self.initialize(id, settings);
	this.items[id] = self;
	return self;
};

BX.Main.EventListUserSelector.createIfNotExists = function(id, settings)
{
	var self = this.items[id];
	if(typeof(self) !== 'undefined')
	{
		self.initialize(id, settings);
	}
	else
	{
		self = new BX.Main.EventListUserSelector();
		self.initialize(id, settings);
		this.items[id] = self;
	}
	return self;
};

BX.Main.EventListUserSelector.deletePopup = function(id)
{
	var item = this.items[id];
	if(typeof(item) === 'undefined')
	{
		return false;
	}

	item._onBeforeDelete();
	delete this.items[id];
	return true;
}
