;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Tool");

	var slice = BX.Landing.Utils.slice;
	var arrayUnique = BX.Landing.Utils.arrayUnique;
	var data = BX.Landing.Utils.data;
	var isString = BX.Landing.Utils.isString;
	var create = BX.Landing.Utils.create;
	var append = BX.Landing.Utils.append;
	var remove = BX.Landing.Utils.remove;


	/**
	 * Implements interface for works with page fonts
	 * @constructor
	 */
	BX.Landing.UI.Tool.FontManager = function()
	{

	};


	/**
	 * Gets instance of BX.Landing.UI.Tool.FontManager
	 * @return {BX.Landing.UI.Tool.FontManager}
	 */
	BX.Landing.UI.Tool.FontManager.getInstance = function()
	{
		return (
			BX.Landing.UI.Tool.FontManager.instance ||
			(BX.Landing.UI.Tool.FontManager.instance = new BX.Landing.UI.Tool.FontManager())
		);
	};


	/**
	 * Makes font className
	 * @param {string} family
	 * @return {string}
	 */
	function makeFontClassName(family)
	{
		return "g-font-" + family.toLowerCase().trim().replace(/ /g, "-")
	}


	/**
	 * Makes font family value with fallback font
	 * @param {string} family
	 * @param {string} category
	 * @return {string}
	 */
	function makeFontFamily(family, category)
	{
		family = family.replaceAll("+", ' ');
		var fallbackMap = {
			"serif": "\"#font#\", serif",
			"sans-serif": "\"#font#\", sans-serif",
			"display": "\"#font#\", cursive",
			"handwriting": "\"#font#\", cursive",
			"monospace": "\"#font#\", monospace"
		};

		return fallbackMap[category] ? fallbackMap[category].replace("#font#", family) : "\""+family+"\"";
	}


	BX.Landing.UI.Tool.FontManager.prototype = {
		/**
		 * Gets all loaded fonts
		 * @return {string[]}
		 */
		getLoadedFonts: function()
		{
			var elements = slice(document.head.querySelectorAll("[data-font*=\"g-font-\"]"));

			return elements.map(function(element) {
				return {
					className: data(element, "data-font"),
					element: element,
					CSSDeclaration: document.head.querySelector("[data-id*=\""+data(element, "data-font")+"\"]"),
					protected: data(element, "data-protected") || false
				}
			});
		},


		/**
		 * Gets used loaded fonts
		 * @return {string[]}
		 */
		getUsedLoadedFonts: function()
		{
			var usedLoadedFonts = [];
			var loadedFonts = this.getLoadedFonts().filter(function(font) {
				return !font.protected;
			});

			if (loadedFonts.length)
			{
				var usedFonts = this.getAllUsedFonts();

				loadedFonts.forEach(function(loadedFont) {
					var isUsed = usedFonts.some(function(usedFont) {
						return loadedFont.className === usedFont;
					});

					if (isUsed)
					{
						usedLoadedFonts.push(loadedFont);
					}
				});
			}

			return usedLoadedFonts;
		},


		/**
		 * Gets all used fonts
		 * @param {*} [rootElement = document.body]
		 * @return {string[]}
		 */
		getAllUsedFonts: function(rootElement)
		{
			rootElement = (rootElement || document.body);
			var elements = slice(rootElement.querySelectorAll("*:not(img)"));
			var fonts = [];

			elements.forEach(function(element) {
				var family = BX.style(element, "font-family");

				if (isString(family))
				{
					family = family.replace(/['|"]/g, "");
					fonts.push(makeFontClassName(family.split(",")[0]));
				}
			});

			return arrayUnique(fonts);
		},


		/**
		 * Checks that font is loaded
		 * @param {string} className
		 * @return {boolean}
		 */
		isLoaded: function(className)
		{
			return this.getLoadedFonts().some(function(loadedFont) {
				return loadedFont.className === className;
			});
		},


		/**
		 * Adds font
		 * @param {{
		 * 		className: string,
		 * 		family: string,
		 * 		href: string,
		 * 		category: string
		 * }} font
		 * @param [targetWindow = window]
		 */
		addFont: function(font, targetWindow)
		{
			var targetDocument = !!targetWindow ? targetWindow.document : document;

			return new Promise(function(resolve) {
				if (!this.isLoaded(font.className))
				{
					let href = font.href;
					if (window.fontsProxyUrl)
					{
						const url = new URL(href);
						url.host = window.fontsProxyUrl;
						href = url.href;
					}

					var link = create("link", {
						attrs: {
							rel: "stylesheet",
							href: href,
							"data-font": font.className,
							media: "async@load"
						},
						events: {
							load: function() {
								this.media = "all";
								resolve();
							},
							error: function()
							{
								resolve();
							}
						}
					});

					var style = create("style", {
						attrs: {"data-id": font.className},
						text: "." + font.className + " { font-family: " + makeFontFamily(font.family, font.category) + "; }"
					});

					append(link, targetDocument.head);
					append(style, targetDocument.head);
				}
				else
				{
					resolve();
				}
			}.bind(this));
		},


		/**
		 * Gets unused loaded fonts
		 * @return {object[]}
		 */
		getUnusedLoadedFonts: function()
		{
			var unusedFonts = [];
			var loadedFonts = this.getLoadedFonts().filter(function(font) {
				return !font.protected;
			});

			if (loadedFonts.length)
			{
				var usedFonts = this.getAllUsedFonts();

				loadedFonts.forEach(function(loadedFont) {
					var isUsed = usedFonts.some(function(usedFont) {
						return loadedFont.className === usedFont;
					});

					if (!isUsed)
					{
						unusedFonts.push(loadedFont);
					}
				});
			}

			return unusedFonts;
		},


		/**
		 * Removes not protected unused fonts
		 */
		removeUnusedFonts: function()
		{
			this.getUnusedLoadedFonts().forEach(function(font) {
				if (font.element)
				{
					remove(font.element);
				}

				if (font.CSSDeclaration)
				{
					remove(font.CSSDeclaration);
				}
			});
		}
	}
})();