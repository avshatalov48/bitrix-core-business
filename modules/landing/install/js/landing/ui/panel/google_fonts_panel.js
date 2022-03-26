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

	var initedItems = new WeakSet();

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

		this.searchForm = this.createSearchForm();
		this.categoryForm = this.createCategoryForm();
		this.languageForm = this.createLanguageForm();
		append(this.searchForm.layout, this.sidebar);
		append(this.categoryForm.layout, this.sidebar);
		append(this.languageForm.layout, this.sidebar);
		append(this.createListLayout(), this.content);
		append(this.createPaginationLayout(), this.content);

		BX.Event.bind(this.getMoreButton(), 'click', this.onMoreButtonClick.bind(this));
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

		createListLayout: function()
		{
			return BX.Dom.create({
				tag: 'div',
				props: {classList: 'landing-ui-panel-fonts-list'},
			});
		},

		/**
		 * @return {HTMLElement}
		 */
		getListLayout: function()
		{
			return this.content.querySelector('.landing-ui-panel-fonts-list');
		},

		createPaginationLayout: function()
		{
			return BX.Dom.create({
				tag: 'div',
				props: {classList: 'landing-ui-panel-fonts-pagination'},
				children: [
					BX.Dom.create({
						tag: 'span',
						props: {className: 'ui-btn ui-btn-lg ui-btn-light-border'},
						text: BX.Landing.Loc.getMessage('LANDING_FONTS_PANEL_MORE_BUTTON_LABEL'),
					}),
				],
			});
		},

		getMoreButton: function()
		{
			return this.getPaginationLayout().querySelector('.ui-btn');
		},

		getPaginationLayout: function()
		{
			return this.content.querySelector('.landing-ui-panel-fonts-pagination');
		},

		/**
		 * Shows panel
		 * @return {Promise}
		 */
		show: function(params)
		{
			var showPromise = this.superclass.show.call(this);

			showPromise.then(function() {
				this.searchForm.fields[0].enableEdit();
				this.searchForm.fields[0].input.focus();
			}.bind(this));

			if (this.isFontsLoaded())
			{
				return showPromise
					.then(proxy(this.saveResolver, this));
			}

			if (params)
			{
				if (params['hideOverlay'])
				{
					this.overlay.style.display = "none";
				}
				if (params['context'])
				{
					this.context = params['context'];
				}
			}

			this.page = 0;

			BX.Dom.hide(this.getMoreButton());

			return showPromise
				.then(function() {
					return this.showLoader();
				}.bind(this))
				.then(function() {
					return this.getFonts();
				}.bind(this))
				.then(function(response) {
					return this.applyFilter(response);
				}.bind(this))
				.then(function(filteredResponse) {
					var paginatedList = this.paginateList(filteredResponse);
					return this.loadFonts(paginatedList[this.page] || []);
				}.bind(this))
				.then(function(paginatedList) {
					return this.renderList(paginatedList);
				}.bind(this))
				.then(function() {
					BX.Dom.show(this.getMoreButton());
					return this.hideLoader();
				}.bind(this))
				.then(function() {
					return this.saveResolver();
				}.bind(this));
		},

		onMoreButtonClick: function(event)
		{
			event.preventDefault();

			this.page += 1;

			void this.showLoader();

			var moreButton = this.getMoreButton();

			BX.Dom.addClass(moreButton, 'ui-btn-wait');
			BX.Dom.style(moreButton, 'pointer-events', 'none');

			var lastPage = false;

			this.getFonts()
				.then(function (response) {
					return this.applyFilter(response);
				}.bind(this))
				.then(function (filteredResponse) {
					var paginatedList = this.paginateList(filteredResponse);
					lastPage = (paginatedList.length - 1) <= this.page;
					return this.loadFonts(paginatedList[this.page] || []);
				}.bind(this))
				.then(function (paginatedList) {
					return this.renderList(paginatedList);
				}.bind(this))
				.then(function () {
					BX.Dom.removeClass(this.getMoreButton(), 'ui-btn-wait');
					BX.Dom.style(moreButton, 'pointer-events', null);
					if (lastPage)
					{
						BX.Dom.hide(moreButton);
					}
					return this.hideLoader();
				}.bind(this));
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

		paginateList: function(list)
		{
			var result = [];
			while (list.length)
			{
				result.push(list.splice(0, 21));
			}

			return result;
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
				this.loader = new BX.Loader({
					target: this.body,
					offset: {
						left: '134px',
						top: '-30px'
					}
				});
			}

			BX.Dom.style(this.sidebar, {
				transition: '200ms all ease',
				opacity: .8,
				'pointer-events': 'none'
			});

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

			BX.Dom.style(this.sidebar, {
				transition: null,
				opacity: null,
				'pointer-events': null
			});

			return Promise.resolve();
		},


		/**
		 * Loads fonts
		 * @param {object[]} response
		 * @return {Promise}
		 */
		loadFonts: function(response)
		{
			var context;
			if (this.context)
			{
				context = this.context;
			}
			else
			{
				context = top;
			}

			return new Promise(function(resolve) {
				if (!BX.Type.isArrayFilled(response))
				{
					resolve(response);
				}
				else
				{
					WebFont.load({
						google: {
							families: response.map(function(font) {
								return font.family.replace(/ /g, "+")
							})
						},
						context: context,
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
				}
			});
		},


		/**
		 * Applies filter to response
		 * @param {object[]} response
		 */
		applyFilter: function(response)
		{
			var searchQuery = this.searchForm.fields[0].getValue();
			var subsets = this.languageForm.fields[0].getValue();
			var categories = this.categoryForm.fields[0].getValue();

			if (!BX.Type.isArrayFilled(categories))
			{
				categories = this.categoryForm.fields[0].items.map(function(item) {
					return item.value;
				});
			}

			return response.filter(function(options) {
				return (
					subsets.every(function(lang) {
						return options.subsets.indexOf(lang) !== -1;
					})
					&& categories.some(function(category) {
						return category === options.category;
					})
					&& (
						!BX.Type.isStringFilled(searchQuery)
						|| String(options.family).toLowerCase().includes(String(searchQuery).toLowerCase())
					)
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
			var align = "left";

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
				align = 'right';
				pangram = arabicPangram;
			}

			if (subsets.includes("hebrew"))
			{
				direction = "rtl";
				align = 'right';
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
					"<div style=\"font-family: "+options.family+"; direction: "+direction+"; text-align: "+align+";\" class=\"landing-ui-font-preview-pangram\" contenteditable=\"true\" onpaste=\"return false;\">" +
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
			void this.showLoader();

			this.page = 0;

			var lastPage = false;

			this.getFonts()
				.then(function(fonts) {
					return this.applyFilter(fonts)
				}.bind(this))
				.then(function(filteredFonts) {
					var paginatedList = this.paginateList(filteredFonts);
					lastPage = paginatedList.length <= 1;
					return this.loadFonts(paginatedList[this.page] || []);
				}.bind(this))
				.then(function(paginatedList) {
					this.content.scrollTop = 0;
					void this.hideLoader();
					if (lastPage)
					{
						BX.Dom.hide(this.getMoreButton());
					}
					else
					{
						BX.Dom.show(this.getMoreButton());
					}
					return this.renderList(paginatedList, true);
				}.bind(this))
		},


		/**
		 * Renders list
		 * @return {Promise<T>}
		 */
		renderList: function(list, replace)
		{
			return this.renderItems(list, replace);
		},


		/**
		 * Renders items
		 * @param {object[]} response
		 * @param {boolean} [replace]
		 */
		renderItems: function(response, replace)
		{
			if (!replace)
			{
				this.getListLayout().insertAdjacentHTML(
					'beforeend',
					response.map(this.createListItem, this).join("")
				);
			}
			else
			{
				if (
					!BX.Type.isArrayFilled(response)
					&& BX.Type.isStringFilled(this.searchForm.fields[0].getValue())
				)
				{
					this.getListLayout().innerHTML = '';
					BX.Dom.append(this.getEmptyStub(), this.getListLayout());
				}
				else
				{
					this.getListLayout().innerHTML = response.map(this.createListItem, this).join("");
				}
			}

			slice(this.getListLayout().children).forEach(this.initItem(response), this);
		},

		getEmptyStub: function()
		{
			return BX.Dom.create({
				tag: 'div',
				props: {className: 'landing-ui-fonts-panel-empty-stub'},
				text: BX.Landing.Loc.getMessage('LANDING_FONTS_PANEL_EMPTY_STUB')
			});
		},

		/**
		 * Initializes list item
		 * @param response
		 * @return {Function}
		 */
		initItem: function(response)
		{
			return function(item, index) {
				if (!initedItems.has(item))
				{
					var button = item.querySelector(".landing-ui-font-preview-font-button");
					bind(button, "click", this.onFontSelect.bind(this, response[index - (this.page * 21)]));
					initedItems.add(item);

					var input = item.querySelector('.landing-ui-font-preview-pangram');
					if (input)
					{
						var sourceText = input.innerText;
						BX.Event.bind(input, 'blur', function() {
							if (!BX.Type.isStringFilled(input.innerText))
							{
								input.innerText = sourceText;
							}
						});
					}
				}
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

		createSearchForm: function()
		{
			var debouncedRuntime = BX.Runtime.debounce(this.onFilterChange, 500, this);
			var rootWindow = BX.Landing.PageObject.getRootWindow();
			return new BX.Landing.UI.Form.StyleForm({
				title: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_SEARCH_TITLE"),
				fields: [
					new rootWindow.BX.Landing.UI.Field.Text({
						selector: 'searchQuery',
						textOnly: true,
						onValueChange: function() {
							void this.showLoader();
							debouncedRuntime();
						}.bind(this),
						placeholder: BX.Loc.getMessage('LANDING_GOOGLE_FONT_SEARCH_PLACEHOLDER'),
					})
				]
			});
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
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_SANS_SERIF_2"), value: "sans-serif"},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_SERIF_2"), value: "serif"},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_DISPLAY"), value: "display"},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_HANDWRITING"), value: "handwriting"},
					{name: BX.Landing.Loc.getMessage("LANDING_GOOGLE_FONT_PANEL_CATEGORY_MONOSPACE"), value: "monospace"}
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