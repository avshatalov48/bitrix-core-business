;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");


	/**
	 * @extends {BX.Landing.UI.Field.ButtonGroup}
	 * @param options
	 * @constructor
	 */
	BX.Landing.UI.Field.Color = function(options)
	{
		BX.Landing.UI.Field.ButtonGroup.apply(this, arguments);
		this.pseudoElement = typeof options.pseudoElement === "string" ? options.pseudoElement : null;
		this.pseudoClass = typeof options.pseudoClass === "string" ? options.pseudoClass : null;
		this.layout.classList.add("landing-ui-field-color");
		this.stylePath = options.stylePath;
		this.initButtons();
	};


	function getCssPropertyForRule(rule, prop, stylePath) {
		var sheets = document.styleSheets;
		var slen = sheets.length;

		for(var i=0; i<slen; i++) {
			if (
				"href" in document.styleSheets[i] && (
				document.styleSheets[i].href.indexOf("/templates/landing24/themes/") !== -1 ||
				document.styleSheets[i].href.indexOf("/landing24/template_") !== -1) ||
				(stylePath && document.styleSheets[i].href.indexOf(stylePath) !== -1)
			)
			{
				var rules = document.styleSheets[i].cssRules;
				if (rules)
				{
					var rlen = rules.length;
					for(var j=0; j<rlen; j++) {
						if(rules[j].selectorText === rule) {
							return rules[j].style[prop];
						}
					}
				}
			}
		}
	}

	BX.Landing.UI.Field.Color.prototype = {
		constructor: BX.Landing.UI.Field.Color,
		__proto__: BX.Landing.UI.Field.ButtonGroup.prototype,

		initButtons: function()
		{
			this.buttons.forEach(function(button) {
				button.layout.classList.add(button.layout.value);
				button.layout.innerHTML = "<span class=\"landing-ui-button-inner landing-ui-pattern-transparent\"></span>";

				var property = BX.Landing.UI.Adapter.CSSProperty.get(this.property);

				if (((property !== "background-image" && property !== "background-color") || this.pseudoElement) && !this.pseudoClass)
				{
					BX.DOM.read(function() {
						var color = getComputedStyle(button.layout, this.pseudoElement).getPropertyValue(property);
						BX.DOM.write(function() {
							button.layout.classList.remove(button.layout.value);
							button.layout.style["background-color"] = color;
						});
					}.bind(this));
				}
				else if (this.pseudoClass)
				{
					BX.DOM.read(function() {
						var color = getCssPropertyForRule("."+button.layout.value+this.pseudoClass, property, this.stylePath);
						BX.DOM.write(function() {
							button.layout.classList.remove(button.layout.value);
							button.layout.style["background-color"] = color;
						});
					}.bind(this));
				}
			}, this);
		},

		reset: function()
		{
			this.buttons.forEach(function(button) {
				button.layout.classList.remove("landing-ui-active");
			});
		}
	};

})();