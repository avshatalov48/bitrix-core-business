;(function ()
{
	"use strict";

	var namespace = BX.namespace("BX.UI");
	if (namespace.Switcher)
	{
		return;
	}

	var list = [];

	/**
	 * Switcher.
	 *
	 * @param {object} [options] - Options.
	 * @param {string} [options.attributeName] - Name of switcher attribute.
	 * @param {Element} [options.node] - Node.
	 * @param {string} [options.id] - ID.
	 * @param {bool} [options.checked] - Checked.
	 * @param {string} [options.inputName] - Input name.
	 * @constructor
	 */
	function Switcher (options)
	{
		this.init(options);
		list.push(this);
	}
	Switcher.getById = function (id)
	{
		return list.filter(function (item) {
			return item.id === id;
		})[0] || null;
	};
	Switcher.getList = function ()
	{
		return list;
	};
	Switcher.className = 'ui-switcher';
	Switcher.initByClassName = function ()
	{
		var nodes = document.getElementsByClassName(Switcher.className);
		nodes = BX.convert.nodeListToArray(nodes);
		nodes.forEach(function (node) {
			if (node.getAttribute('data-switcher-init'))
			{
				return;
			}
			new Switcher({node: node});
		});
	};
	Switcher.prototype = {
		events: {
			toggled: 'toggled',
			checked: 'checked',
			unchecked: 'unchecked'
		},
		handlers: {},
		attributeName: 'data-switcher',
		attributeInitName: 'data-switcher-init',
		classNameOff: 'ui-switcher-off',
		classNameSize: {
			small: 'ui-switcher-size-sm'
		},
		classNameColor: {
			green: 'ui-switcher-color-green'
		},
		popup: null,
		content: null,
		popupParameters: null,

		init: function (options)
		{
			options = options || {};
			if (options.attributeName)
			{
				this.attributeName = options.attributeName;
			}

			if (options.handlers)
			{
				this.handlers = options.handlers;
			}

			if (options.node)
			{
				if (!BX.type.isDomNode(options.node))
				{
					throw new Error('Parameter `node` DOM Node expected.');
				}

				this.node = options.node;
				var data = this.node.getAttribute(this.attributeName);
				try
				{
					data = JSON.parse(data) || {};
				}
				catch (e)
				{
					data = {};
				}

				if (data.id)
				{
					this.id = data.id;
				}

				this.checked = !!data.checked;
				this.inputName = data.inputName;
				if(typeof data.color !== 'undefined')
				{
					options.color = data.color;
				}
				if(typeof data.size !== 'undefined')
				{
					options.size = data.size;
				}
			}
			else
			{
				this.node = document.createElement('span');
			}

			if (options.id)
			{
				this.id = options.id;
			}

			if (!this.id)
			{
				this.id = Math.random();
			}
			if (typeof options.checked === 'boolean')
			{
				this.checked = options.checked;
			}
			if (options.inputName)
			{
				this.inputName = options.inputName;
			}
			if (this.classNameSize[options.size])
			{
				this.node.classList.add(this.classNameSize[options.size]);
			}
			if (this.classNameColor[options.color])
			{
				this.node.classList.add(this.classNameColor[options.color]);
			}

			this.initNode();
			this.check(this.checked, false);
		},

		/**
		 * Return Node of switcher.
		 */
		getNode: function ()
		{
			return this.node;
		},

		initNode: function ()
		{
			var node = this.node;
			if (node.getAttribute(this.attributeInitName))
			{
				return;
			}
			node.setAttribute(this.attributeInitName, 'y');

			BX.addClass(node, Switcher.className);
			node.innerHTML =
				'<span class="ui-switcher-cursor"></span>\n' +
				'<span class="ui-switcher-enabled">' + BX.message('UI_SWITCHER_ON') + '</span>\n' +
				'<span class="ui-switcher-disabled">' + BX.message('UI_SWITCHER_OFF') + '</span>\n';
			if (this.inputName)
			{
				this.inputNode = document.createElement('input');
				this.inputNode.type = 'hidden';
				this.inputNode.name = this.inputName;
				this.node.appendChild(this.inputNode);
			}

			BX.bind(node, 'click', this.toggle.bind(this));
		},

		/**
		 * Toggle checking state.
		 */
		toggle: function ()
		{
			this.check(!this.isChecked());
		},

		/**
		 * Return true if switcher is checked.
		 */
		isChecked: function ()
		{
			return this.checked;
		},

		/**
		 *
		 */
		fireEvent: function (eventName)
		{
			BX.onCustomEvent(this, eventName);
			if (this.handlers[eventName])
			{
				this.handlers[eventName].call(this);
			}
		},

		/**
		 * Set `checked` or `unchecked` state.
		 */
		check: function (checked, fireEvents)
		{
			this.checked = checked;
			if (this.inputNode)
			{
				this.inputNode.value = this.checked ? 'Y' : 'N';
			}

			fireEvents = fireEvents !== false;

			if (this.checked)
			{
				BX.removeClass(this.node, this.classNameOff);
				fireEvents ? this.fireEvent(this.events.unchecked) : null;
			}
			else
			{
				BX.addClass(this.node, this.classNameOff);
				fireEvents ? this.fireEvent(this.events.checked) : null;
			}

			BX.onCustomEvent(this, this.events.toggled);
			fireEvents ? this.fireEvent(this.events.toggled) : null;
		}
	};
	
	namespace.Switcher = Switcher;
	BX.ready(function () {
		namespace.Switcher.initByClassName();
	});
})();