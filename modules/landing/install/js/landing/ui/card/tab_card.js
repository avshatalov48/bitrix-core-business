;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var hasClass = BX.Landing.Utils.hasClass;
	var append = BX.Landing.Utils.append;
	var create = BX.Landing.Utils.create;
	var data = BX.Landing.Utils.data;
	var slice = BX.Landing.Utils.slice;
	var show = BX.Landing.Utils.Show;
	var hide = BX.Landing.Utils.Hide;
	var Collection = BX.Landing.Collection.BaseCollection;


	/**
	 * Implements interface for works with tabs
	 * @extends {BX.Landing.UI.Card.BaseCard}
	 * @param options
	 * @constructor
	 */
	BX.Landing.UI.Card.TabCard = function(options)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		addClass(this.layout, "landing-ui-card-tabs");
		this.fields = new Collection();
		this.tabs = [];

		this.headersArea = create("div", {
			props: {className: "landing-ui-card-tabs-headers"}
		});

		this.tabsArea = create("div", {
			props: {className: "landing-ui-card-tabs-tabs"}
		});

		append(this.headersArea, this.layout);
		append(this.tabsArea, this.layout);

		options.tabs.forEach(this.addTab, this);

		this.render();

		var active = options.tabs.find(function(tab) {
			return tab.active === true;
		});

		if (!active)
		{
			active = options.tabs[0];
		}

		this.activateTab(active.id);
	};

	BX.Landing.UI.Card.TabCard.prototype = {
		constructor: BX.Landing.UI.Card.TabCard,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype,

		addTab: function(tab)
		{
			this.tabs.push({
				id: tab.id,
				name: tab.name,
				fields: tab.fields.map(function(field) {
					return field.layout
				})
			});

			tab.fields.forEach(function(field) {
				this.fields.push(field);
			}, this);
		},

		render: function()
		{
			this.headersArea.innerHTML = "";
			this.tabsArea.innerHTML = "";

			this.tabs.forEach(function(tab, index) {
				append(this.createHeader({id: tab.id, name: tab.name}), this.headersArea);
				append(this.createTab({id: tab.id, fields: tab.fields}), this.tabsArea);
			}, this)
		},

		createHeader: function(options)
		{
			return create("div", {
				props: {className: "landing-ui-card-tabs-headers-item"},
				text: options.name,
				attrs: {"data-id": options.id},
				events: {
					click: this.onHeaderClick.bind(this)
				}
			});
		},

		onHeaderClick: function(event)
		{
			event.preventDefault();

			if (!this.isActive(event.currentTarget))
			{
				this.activateTab(data(event.currentTarget, "data-id"));
			}
		},

		isActive: function(element)
		{
			return hasClass(element, "landing-ui-active");
		},

		activateTab: function(id)
		{
			slice(this.headersArea.children).forEach(function(header) {
				removeClass(header, "landing-ui-active");
			});

			slice(this.tabsArea.children).forEach(function(tab) {
				removeClass(tab, "landing-ui-active");
				hide(tab);
			});

			var header = this.getHeader(id);
			var tab = this.getTab(id);

			if (header)
			{
				addClass(header, "landing-ui-active");
			}

			if (tab)
			{
				show(tab).then(function() {
					addClass(tab, "landing-ui-active");
				});
			}
		},

		createTab: function(options)
		{
			return create("div", {
				props: {className: "landing-ui-card-tabs-tabs-item landing-ui-hide"},
				children: options.fields,
				attrs: {"data-id": options.id}
			});
		},

		getHeader: function(id)
		{
			return slice(this.headersArea.children).find(function(header) {
				return data(header, "data-id") === id;
			});
		},

		getTab: function(id)
		{
			return slice(this.tabsArea.children).find(function(tab) {
				return data(tab, "data-id") === id;
			});
		}
	};
})();