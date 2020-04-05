;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var addClass = BX.Landing.Utils.addClass;
	var proxy = BX.Landing.Utils.proxy;
	var append = BX.Landing.Utils.append;
	var slice = BX.Landing.Utils.slice;
	var clone = BX.Landing.Utils.clone;
	var bind = BX.Landing.Utils.bind;

	var latinPangrams = [
		"A red flare silhouetted the jagged edge of a wing",
		"I watched the storm, so beautiful yet terrific",
		"Almost before we knew it, we had left the ground",
		"Waves flung themselves at the blue evening",
		"A shining crescent far beneath the flying vessel"
	];

	var cyrillicPangrams = [
		"&#x0423;&#x0442;&#x0440;&#x0435;&#x043D;&#x043D;&#x0435;&#x0435; &#x0441;&#x043E;&#x043B;&#x043D;&#x0446;&#x0435; &#x044F;&#x0440;&#x043A;&#x043E; &#x043E;&#x0441;&#x0432;&#x0435;&#x0442;&#x0438;&#x043B;&#x043E; &#x043F;&#x043E;&#x043B;&#x044F;&#x043D;&#x0443; &#x0438; &#x043B;&#x0435;&#x0441;",
		"&#x041F;&#x0440;&#x0438;&#x043B;&#x0435;&#x0442;&#x0435;&#x0432;&#x0448;&#x0438;&#x0435; &#x043F;&#x0442;&#x0438;&#x0446;&#x044B; &#x0437;&#x0430;&#x043D;&#x044F;&#x043B;&#x0438; &#x0432;&#x0435;&#x0441;&#x044C; &#x0441;&#x043A;&#x0430;&#x043B;&#x0438;&#x0441;&#x0442;&#x044B;&#x0439; &#x0431;&#x0435;&#x0440;&#x0435;&#x0433;",
		"&#x0411;&#x043E;&#x0434;&#x0440;&#x044F;&#x0449;&#x0438;&#x0439; &#x043C;&#x043E;&#x0440;&#x0441;&#x043A;&#x043E;&#x0439; &#x0432;&#x043E;&#x0437;&#x0434;&#x0443;&#x0445; &#x0431;&#x044B;&#x043B; &#x043F;&#x0440;&#x043E;&#x0445;&#x043B;&#x0430;&#x0434;&#x0435;&#x043D; &#x0438; &#x0441;&#x0432;&#x0435;&#x0436;",
		"&#x042D;&#x0442;&#x043E; &#x043B;&#x0443;&#x0447;&#x0448;&#x0435;&#x0435;, &#x0447;&#x0442;&#x043E; &#x043C;&#x043E;&#x0433;&#x043B;&#x043E; &#x0441; &#x043D;&#x0438;&#x043C; &#x043F;&#x0440;&#x043E;&#x0438;&#x0437;&#x043E;&#x0439;&#x0442;&#x0438; &#x0432; &#x043D;&#x043E;&#x0432;&#x043E;&#x043C; &#x0433;&#x043E;&#x0440;&#x043E;&#x0434;&#x0435;",
		"&#x041D;&#x043E;&#x0432;&#x0430;&#x044F; &#x043A;&#x043D;&#x0438;&#x0433;&#x0430; &#x043E;&#x043A;&#x0430;&#x0437;&#x0430;&#x043B;&#x0430;&#x0441;&#x044C; &#x0438;&#x043D;&#x0442;&#x0435;&#x0440;&#x0435;&#x0441;&#x043D;&#x043E;&#x0439; &#x0438; &#x043F;&#x043E;&#x0437;&#x043D;&#x0430;&#x0432;&#x0430;&#x0442;&#x0435;&#x043B;&#x044C;&#x043D;&#x043E;&#x0439;"
	];

	var arabicPangram = "&#1576;&#1591;&#1575;&#1576;&#1593; &#1571;&#1581;&#1605;&#1585; &#1575;&#1585;&#1578;&#1587;&#1605;&#1578; &#1589;&#1608;&#1585;&#1577; &#1592;&#1604;&#1610;&#1617;&#1577; &#1604;&#1581;&#1583;&#1608;&#1583; &#1575;&#1604;&#1580;&#1606;&#1575;&#1581; &#1575;&#1604;&#1605;&#1587;&#1606;&#1606;&#1577;.";
	var hebrewPangram = "&#1492;&#1496;&#1489;&#1506; &#1492;&#1488;&#1495;&#1491; &#1513;&#1500;&#1497; &#1493;&#1492;&#1496;&#1489;&#1506; &#1492;&#1488;&#1495;&#1512; &#1495;&#1500;&#1511;&#1493; &#1494;&#1497;&#1499;&#1512;&#1493;&#1503; &#1502;&#1513;&#1493;&#1514;&#1507;.";
	var koreanPangram = "&#45208;&#45716; &#54253;&#54413;&#51012; &#51648;&#53020;&#48372;&#50520;&#45796;. &#45320;&#47924;&#45208; &#50500;&#47492;&#45796;&#50864;&#47732;&#49436;&#46020; &#50628;&#52397;&#45212; &#54253;&#54413;&#51012;.";


	/**
	 * Implements interface for works with Google Fonts
	 * Implements singleton pattern, don't use as constructor
	 * use as BX.Landing.UI.Panel.GoogleFonts.getInstance()
	 * @extends {BX.Landing.UI.Panel.Content}
	 * @constructor
	 */
	BX.Landing.UI.Panel.GoogleFonts = function()
	{
		BX.Landing.UI.Panel.Content.apply(this, [
			"google-fonts-panel",
			{
				title: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_TITLE")
			}
		]);

		this.pangramIndex = -1;

		addClass(this.layout, "landing-ui-panel-google-fonts");
		addClass(this.overlay, "landing-ui-panel-google-fonts-overlay");
		this.client = new BX.Landing.Client.GoogleFonts();

		var rootWindow = BX.Landing.PageObject.getRootWindow();
		var container = rootWindow.document.body.querySelector(".landing-ui-view-container");
		append(this.layout, container);
		append(this.overlay, container);

		this.categoryForm = this.createCategoryForm();
		this.languageForm = this.createLanguageForm();
		append(this.categoryForm.layout, this.sidebar);
		append(this.languageForm.layout, this.sidebar);
	};


	/**
	 * Gets instance of BX.Landing.UI.Panel.GoogleFonts
	 * @return {BX.Landing.UI.Panel.GoogleFonts}
	 */
	BX.Landing.UI.Panel.GoogleFonts.getInstance = function()
	{
		return (
			BX.Landing.UI.Panel.GoogleFonts.instance ||
			(BX.Landing.UI.Panel.GoogleFonts.instance = new BX.Landing.UI.Panel.GoogleFonts())
		);
	};


	BX.Landing.UI.Panel.GoogleFonts.prototype = {
		constructor: BX.Landing.UI.Panel.GoogleFonts,
		__proto__: BX.Landing.UI.Panel.Content.prototype,
		superclass: BX.Landing.UI.Panel.Content.prototype,

		/**
		 * Shows panel
		 * @return {Promise}
		 */
		show: function()
		{
			var showPromise = this.superclass.show.call(this);

			if (this.isFontsLoaded())
			{
				return showPromise
					.then(proxy(this.saveResolver, this));
			}

			return showPromise
				.then(proxy(this.showLoader, this))
				.then(proxy(this.getFonts, this))
				.then(proxy(this.loadFonts, this))
				.then(proxy(this.renderList, this))
				.then(proxy(this.hideLoader, this))
				.then(proxy(this.saveResolver, this));
		},


		/**
		 * Save promise resolve function to object property this.resolver
		 * @return {Promise}
		 */
		saveResolver: function()
		{
			var self = this;
			return new Promise(function(resolve) {
				self.resolver = resolve;
			});
		},


		/**
		 * Gets fonts list
		 * @return {Promise<object[]>}
		 */
		getFonts: function()
		{
			if (this.response)
			{
				return Promise.resolve(this.response);
			}

			return this.client.getList()
				.then(proxy(this.saveResponse, this))
		},


		/**
		 * Saves response to object property this.response
		 * @param {object[]} response
		 * @return {object[]}
		 */
		saveResponse: function(response)
		{
			return (this.response = response);
		},


		/**
		 * Checks that fonts is loaded
		 * @return {boolean}
		 */
		isFontsLoaded: function()
		{
			return !!this.response;
		},


		/**
		 * Shows loader
		 * @return {Promise}
		 */
		showLoader: function()
		{
			if (!this.loader)
			{
				this.loader = new BX.Loader({target: this.content});
			}

			this.loader.show();

			return Promise.resolve();
		},


		/**
		 * Hides loader
		 */
		hideLoader: function()
		{
			if (this.loader)
			{
				this.loader.hide();
			}

			return Promise.resolve();
		},


		/**
		 * Loads fonts
		 * @param {object[]} response
		 * @return {Promise}
		 */
		loadFonts: function(response)
		{
			return new Promise(function(resolve) {
				WebFont.load({
					google: {
						families: response.map(function(font) {
							return font.family.replace(/ /g, "+")
						})
					},
					context: top,
					classes: false,
					active: function()
					{
						var rootWindow = BX.Landing.PageObject.getRootWindow();
						if (rootWindow.document.fonts)
						{
							rootWindow.document.fonts.ready
								.then(function() {
									resolve(response);
								});
						}
						else
						{
							setTimeout(resolve, 3000, response);
						}
					}
				});
			});
		},


		/**
		 * Applies filter to response
		 * @param {object[]} response
		 */
		applyFilter: function(response)
		{
			var subsets = this.languageForm.fields[0].getValue();
			var categories = this.categoryForm.fields[0].getValue();

			return response.filter(function(options) {
				return (
					subsets.every(function(lang) {
						return options.subsets.indexOf(lang) !== -1;
					}) &&
					categories.some(function(category) {
						return category === options.category;
					})
				)
			});
		},


		/**
		 * Creates list item html string
		 * @param options
		 * @return {string}
		 */
		createListItem: function(options)
		{
			var subsets = this.languageForm.fields[0].getValue();
			var pangram = "";
			var direction = "ltr";

			this.pangramIndex += 1;
			this.pangramIndex = this.pangramIndex > 4 ? 0 : this.pangramIndex;

			if (subsets.includes("latin"))
			{
				pangram = latinPangrams[this.pangramIndex];
			}

			if (subsets.includes("cyrillic"))
			{
				pangram = cyrillicPangrams[this.pangramIndex];
			}

			if (subsets.includes("arabic"))
			{
				direction = "rtl";
				pangram = arabicPangram;
			}

			if (subsets.includes("hebrew"))
			{
				direction = "rtl";
				pangram = hebrewPangram;
			}

			if (subsets.includes("korean"))
			{
				pangram = koreanPangram;
			}

			return (
				"<div class=\"landing-ui-font-preview\">" +
					"<div class=\"landing-ui-font-preview-font-name\">"+options.family+"</div>" +
					"<div class=\"landing-ui-font-preview-font-button\">" +
						"<span class=\"ui-btn ui-btn-xs ui-btn-light-border ui-btn-round\">"+BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_SELECT_BUTTON")+"</span>" +
					"</div>" +
					"<div style=\"font-family: "+options.family+"; direction: "+direction+";\" class=\"landing-ui-font-preview-pangram\" contenteditable=\"true\">" +
						pangram +
					"</div>" +
				"</div>"
			);
		},


		/**
		 * Handles filter change event
		 */
		onFilterChange: function()
		{
			this.renderList();
		},


		/**
		 * Renders list
		 * @return {Promise<T>}
		 */
		renderList: function()
		{
			return this.getFonts()
				.then(proxy(this.applyFilter, this))
				.then(proxy(this.renderItems, this));
		},


		/**
		 * Renders items
		 * @param {object[]} response
		 */
		renderItems: function(response)
		{
			this.content.innerHTML = response.map(this.createListItem, this).join("");
			slice(this.content.children).forEach(this.initItem(response), this);
		},


		/**
		 * Initializes list item
		 * @param response
		 * @return {Function}
		 */
		initItem: function(response)
		{
			return function(item, index) {
				var button = item.querySelector(".landing-ui-font-preview-font-button");
				bind(button, "click", this.onFontSelect.bind(this, response[index]));
			};
		},


		/**
		 * Gets fonts list items
		 * @return {HTMLElement[]}
		 */
		getListItems: function()
		{
			return slice(this.content.querySelectorAll(".landing-ui-font-preview.cell"));
		},


		/**
		 * Creates language filter form
		 * @return {BX.Landing.UI.Form.StyleForm}
		 */
		createLanguageForm: function()
		{
			var form = new BX.Landing.UI.Form.StyleForm({
				title: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_LANGUAGE_FORM_TITLE")
			});

			var fieldFactory = new BX.Landing.UI.Factory.FieldFactory({
				onValueChange: proxy(this.onFilterChange, this)
			});

			var RU = window.location.host.includes(".ru");

			form.addField(fieldFactory.create({
				type: "radio",
				items: [
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_LANGUAGE_CYRILLIC"), value: "cyrillic", checked: RU},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_LANGUAGE_LATIN"), value: "latin", checked: !RU},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_LANGUAGE_ARABIC"), value: "arabic"},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_LANGUAGE_HEBREW"), value: "hebrew"},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_LANGUAGE_KOREAN"), value: "korean"}
				]
			}));

			return form;
		},


		/**
		 * Creates category filter form
		 * @return {BX.Landing.UI.Form.StyleForm}
		 */
		createCategoryForm: function()
		{
			var form = new BX.Landing.UI.Form.StyleForm({
				title: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_FORM_TITLE")
			});
			var fieldFactory = new BX.Landing.UI.Factory.FieldFactory({
				onValueChange: proxy(this.onFilterChange, this)
			});

			form.addField(fieldFactory.create({
				type: "checkbox",
				items: [
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_SANS_SERIF"), value: "sans-serif", checked: true},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_SERIF"), value: "serif", checked: true},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_DISPLAY"), value: "display", checked: true},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_HANDWRITING"), value: "handwriting", checked: true},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_MONOSPACE"), value: "monospace", checked: true}
				]
			}));

			return form;
		},


		onFontSelect: function(font)
		{
			this.selectedFont = font;
			this.onApply();
		},

		onApply: function()
		{
			if (this.resolver)
			{
				var font = clone(this.selectedFont);
				font.subset = this.languageForm.fields[0].getValue();
				this.hide()
					.then(this.resolver.bind(null, font));
			}
		},

		onCancel: function()
		{
			this.hide();
		}
	}
})();