;(function (window)
{
	BX.namespace('BX.Sender');
	if (BX.Sender.TileGrid)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	function Grid(options)
	{
		this.rows = Object.create(null);
		this.items = Object.create(null);
		this.buttons = [];
		this.container = options.container;
		this.tpls = options.templates;
		this.type = options.type;
		this.mess = options.mess;

		if (!this.tpls)
		{
			throw new Error('Templates parameter `templates` is not set.');
		}
		this.loadData(options);
	}
	Grid.prototype = {

		events: {
			itemClick: 'item-click',
			itemButtonClick: 'item-button-click'
		},
		getRow: function (rowId)
		{
			return this.rows[rowId] ? this.rows[rowId] : null;
		},

		getItem: function (itemId)
		{
			return this.items[itemId] ? this.items[itemId] : null;
		},

		draw: function ()
		{
			var docFragment = document.createDocumentFragment();

			for (var id in this.rows)
			{
				docFragment.appendChild(this.rows[id].render());
			}

			this.container.classList.add('sender-tpl-' + this.type);
			this.container.appendChild(docFragment);
		},

		getItemInRow: function ()
		{
			return Math.round(this.container.offsetWidth / 320)
		},

		addRow: function(options)
		{
			options = options || {};

			if (this.getRow(options.id) !== null)
			{
				return;
			}

			options.tpl = this.tpls.row;
			options.manager = this;
			this.rows[options.id] = new GridRow(options);
		},

		addItem: function (options)
		{
			options = options || {};

			if (this.getItem(options.id) !== null)
			{
				return;
			}

			var row = this.getRow(options.rowId);
			if(row)
			{
				if (!options.buttons)
				{
					options.buttons = this.buttons;
				}
				options.tpl = this.tpls.item;
				options.tplButton = this.tpls.button;
				options.manager = this;
				var item = new GridItem(options);
				this.items[options.id] = item;
				row.addItem(item);
			}
		},

		loadData: function(json)
		{
			if (json.buttons)
			{
				this.buttons = json.buttons;
			}

			json.rows.forEach(function(row)
			{
				this.addRow(row)
			}, this);

			json.items.forEach(function(item)
			{
				this.addItem(item)
			}, this)
		}
	};

	function GridRow(options)
	{
		this.manager = options.manager;
		this.id = options.id;
		this.name = options.name;
		this.items = [];
		this.tpl = options.tpl;
		this.showMoreButton = null;
		this.layout = {
			container: null,
			items: null,
			name: null
		}
	}

	GridRow.prototype =
	{
		addItem: function (item)
		{
			item.rowId = this.id;

			this.items.push(item);

			if (this.layout.container)
			{
				this.render();
			}
		},

		createLayout: function ()
		{
			this.layout.container = Helper.getTemplatedNode(this.tpl, {'name': this.name});
			this.layout.name = Helper.getNode('row-name', this.layout.container);
			this.layout.items = Helper.getNode('row-items', this.layout.container);

			return this.layout.container;
		},

		getShowMoreButton: function ()
		{
			this.showMoreButton = BX.create('div', {
				attrs: {
					className: 'sender-tpl-item-show-more'
				},
				children: [
					BX.create('div', {
						attrs: {
							className: 'webform-small-button webform-small-button-transparent'
						},
						events: {
							click: BX.delegate(this.showAllItems, this)
						},
						html: this.manager.mess.showMore
					})
				]
			});

			return this.showMoreButton;
		},

		showAllItems: function (node)
		{
			this.layout.container.classList.add('sender-tpl-items-show-all');
			this.showMoreButton.classList.add('sender-tpl-item-show-hide');
		},

		render: function ()
		{
			if (this.layout.container === null)
			{
				this.createLayout();
			}

			for (var i = 0; i < this.items.length; i++)
			{
				var item = this.items[i];
				this.layout.items.appendChild(item.render(i));
			}

			if(this.items.length > (this.manager.getItemInRow() * 3) + 1)
			{
				this.layout.items.appendChild(this.getShowMoreButton());
			}

			return this.layout.container;
		}
	};

	function GridItem(options)
	{
		this.manager = options.manager;
		this.id = options.id;
		this.name = options.name || '';
		this.data = options.data || {};
		this.rowId = options.rowId;
		this.description = options.description || '';
		this.type = options.type || '';
		this.image = options.image;
		this.buttons = options.buttons;
		this.tpl = options.tpl;
		this.tplButton = options.tplButton;
		this.hot = options.hot;
		this.hint = options.hint;
		this.layout = {
			container: null,
			title: null,
			image: null,
			description: null,
			buttons: null
		}
	}

	GridItem.prototype = {

		getItemWidth: function ()
		{
			return 100 / this.getItemLengthInRow();
		},

		getItemLengthInRow: function ()
		{
			return Math.round(this.manager.container.offsetWidth / 320)
		},

		clipDescription: function ()
		{
			if (this.hot )
			{
				return this.description.substr(0, 40) + '...';
			}
			return this.description.substr(0, 55) + '...';

		},

		initButton: function (button)
		{
			var node = Helper.getTemplatedNode(this.tplButton, button);
			this.layout.buttons.appendChild(node);
			BX.bind(node, 'click', this.onButtonClick.bind(this, button));
		},

		onButtonClick: function (button)
		{
			BX.onCustomEvent(this.manager, this.manager.events.itemButtonClick, [this, button]);
			if (button.handler)
			{
				button.handler.apply(this.manager, [this, button]);
			}
		},

		onClick: function ()
		{
			BX.onCustomEvent(this.manager, this.manager.events.itemClick, [this]);
		},

		render: function (num)
		{
			this.layout.container = Helper.getTemplatedNode(
				this.tpl,
				{
					'name': this.name,
					'style': 'width: ' + this.getItemWidth() +'%',
					'image-style': this.image ?
					'background-image: url(' + this.image + '); ' +
					'background-size: cover;'
						:
						'',
					'desc': this.clipDescription()
				}
			);

			this.layout.content = Helper.getNode('item-content', this.layout.container);
			this.layout.title = Helper.getNode('item-title', this.layout.container);
			this.layout.image = Helper.getNode('item-image', this.layout.container);
			this.layout.description = Helper.getNode('item-desc', this.layout.container);

			this.hot ? this.layout.description.classList.add('sender-tpl-item-description-hot') : null;
			this.description === '' ? this.layout.container.classList.add('sender-tpl-content-without-description') : null;

			if (this.hint)
			{
				var hintNode = document.createElement('span');
				hintNode.setAttribute('data-hint', this.hint);
				hintNode.setAttribute('data-hint-html','');
				BX.UI.Hint.initNode(hintNode)

				this.layout.title.appendChild(hintNode);
			}

			if (num > (this.getItemLengthInRow() * 3) - 1)
			{
				this.layout.container.classList.add('sender-tpl-item-hidden')
			}

			if (this.buttons)
			{
				this.layout.buttons = Helper.getNode('item-buttons', this.layout.container);
				this.buttons.forEach(this.initButton, this);
			}

			BX.bind(this.layout.content, 'click', this.onClick.bind(this));


			return this.layout.container;
		}
	};


	BX.Sender.TileGrid = Grid;

})(window);