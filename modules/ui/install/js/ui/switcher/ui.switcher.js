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
			new Switcher({node: node});
		});
	};
	Switcher.prototype = {
		events: {
			toggled: 'toggled',
			checked: 'checked',
			unchecked: 'unchecked'
		},
		attributeName: 'data-switcher',
		attributeInitName: 'data-switcher-init',
		classNameOff: 'ui-switcher-off',
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
					data = JSON.parse(data);
				}
				catch (e)
				{
					data = {};
				}

				this.id = data.id;
				this.checked = !!data.checked;
				this.inputName = data.inputName;
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
				throw new Error('Parameter `id` expected.');
			}
			if (typeof options.checked === 'boolean')
			{
				this.checked = options.checked;
			}
			if (options.inputName)
			{
				this.inputName = options.inputName;
			}

			this.initNode();
			this.check(this.checked);
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
			node.innerHTML = '<span class="ui-switcher-enabled">\n' +
				'<span class="ui-switcher-enabled-text">' + BX.message('UI_SWITCHER_ON') + '</span>\n' +
				'</span>\n' +
				'<span class="ui-switcher-disabled">\n' +
				'<span class="ui-switcher-cursor"></span>\n' +
				'<span class="ui-switcher-disabled-text">' + BX.message('UI_SWITCHER_OFF') + '</span>\n' +
			'</span>';

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
		 * Set `checked` or `unchecked` state.
		 */
		check: function (checked)
		{
			this.checked = checked;
			if (this.inputNode)
			{
				this.inputNode.value = this.checked ? 'Y' : 'N';
			}

			if (this.checked)
			{
				BX.removeClass(this.node, this.classNameOff);
				BX.onCustomEvent(this, this.events.unchecked);
			}
			else
			{
				BX.addClass(this.node, this.classNameOff);
				BX.onCustomEvent(this, this.events.checked);
			}

			BX.onCustomEvent(this, this.events.toggled);
		}
	};

	Switcher.initByClassName();
	namespace.Switcher = Switcher;
})();