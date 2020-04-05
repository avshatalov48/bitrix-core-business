;(function() {
	"use strict";


	BX.namespace("BX.Landing.UI.Panel");


	/**
	 * @extends {BX.Landing.UI.Panel.Content}
	 * @param id
	 * @param data
	 * @constructor
	 */
	BX.Landing.UI.Panel.Icon = function(id, data)
	{
		BX.Landing.UI.Panel.Content.apply(this, arguments);
		this.layout.classList.add("landing-ui-panel-icon");
		this.overlay.classList.add("landing-ui-panel-icon");
		this.overlay.hidden = true;
		this.resolver = (function() {});
		this.libraries = [
			BX.Landing.Icon.FontAwesome,
			BX.Landing.Icon.SimpleLine,
			BX.Landing.Icon.SimpleLineProOne,
			BX.Landing.Icon.SimpleLineProTwo,
			BX.Landing.Icon.EtLineIcons,
			BX.Landing.Icon.HSIcons
		];
		this.layout.hidden = true;
		document.body.appendChild(this.layout);
	};


	/**
	 * @type {BX.Landing.UI.Panel.Icon}
	 */
	BX.Landing.UI.Panel.Icon.instance = null;


	/**
	 * Gets instance of BX.Landing.UI.Panel.Icon
	 * @return {BX.Landing.UI.Panel.Icon}
	 */
	BX.Landing.UI.Panel.Icon.getInstance = function()
	{
		if (!BX.Landing.UI.Panel.Icon.instance)
		{
			BX.Landing.UI.Panel.Icon.instance = new BX.Landing.UI.Panel.Icon("icon_panel", {
				title: BX.Landing.Loc.getMessage("LANDING_ICONS_SLIDER_TITLE")
			});
		}

		return BX.Landing.UI.Panel.Icon.instance;
	};


	BX.Landing.UI.Panel.Icon.prototype = {
		constructor: BX.Landing.UI.Panel.Icon,
		__proto__: BX.Landing.UI.Panel.Content.prototype,

		show: function()
		{
			return new Promise(function(resolve) {
				this.resolver = resolve;
				this.makeLayout();
				BX.Landing.UI.Panel.Content.prototype.show.call(this);
			}.bind(this));
		},


		onChange: function(icon)
		{
			this.resolver(icon);
			this.hide();
		},


		makeLayout: function()
		{
			if (!this.content.innerHTML)
			{
				this.libraries.forEach(function(library) {
					this.appendSidebarButton(
						new BX.Landing.UI.Button.SidebarButton(library.id, {
							text: library.name
						})
					);

					library.categories.forEach(function(category) {
						this.appendSidebarButton(
							new BX.Landing.UI.Button.SidebarButton(category.id, {
								text: category.name,
								onClick: this.onCategoryChange.bind(this, category.id),
								child: true
							})
						);
					}, this);
				}, this);

				this.onCategoryChange(this.libraries[0].categories[0].id);
			}
		},

		onCategoryChange: function(id)
		{
			this.content.innerHTML = "";

			this.libraries.forEach(function(library) {
				library.categories.forEach(function(category) {
					if (id === category.id)
					{
						var map = new Map();

						var categoryCard = new BX.Landing.UI.Card.BaseCard({
							title: category.name,
							className: "landing-ui-card-icons"
						});

						category.items.forEach(function(item) {
							var iconLayout = document.createElement("div");
							iconLayout.className = "landing-ui-card landing-ui-card-icon";
							var icon = document.createElement("span");
							icon.className = item;
							iconLayout.appendChild(icon);
							iconLayout.addEventListener("click", function() {
								this.onChange(item);
							}.bind(this));

							categoryCard.body.appendChild(iconLayout);

							var styles = getComputedStyle(icon, ":before");

							requestAnimationFrame(function() {
								var content = styles.getPropertyValue('content');
								if (map.has(content))
								{
									iconLayout.hidden = true;
								}
								else
								{
									map.set(content, true);
								}
							}.bind(this));
						}, this);

						this.appendCard(categoryCard);
					}
				}, this);
			}, this);
		}
	}

})();