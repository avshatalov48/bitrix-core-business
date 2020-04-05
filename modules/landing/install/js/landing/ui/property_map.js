;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI");


	BX.Landing.UI.PropertyMap = function(postfix) {
		this.postfix = postfix;
	};


	function updateClassValue(element, regExp, newClassName)
	{
		if (regExp.test(element.className))
		{
			element.className = element.className.replace(regExp, newClassName);
		}
		else
		{
			element.classList.add(newClassName);
		}
	}


	BX.Landing.UI.PropertyMap.prototype = {
		margin: {
			title: BX.message("BLOCK_STYLE_MARGIN"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("m-[0-9]+" + postfix);
				updateClassValue(element, regExp, "m-" + value + postfix);
			}
		},

		'margin-left': {
			title: BX.message("BLOCK_STYLE_MARGIN-LEFT"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("ml-[0-9]+" + postfix);
				updateClassValue(element, regExp, "ml-" + value + postfix);
			}
		},

		'margin-right': {
			title: BX.message("BLOCK_STYLE_MARGIN-RIGHT"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("mr-[0-9]+" + postfix);
				updateClassValue(element, regExp, "mr-" + value + postfix);
			}
		},

		'margin-top': {
			title: BX.message("BLOCK_STYLE_MARGIN-TOP"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("mt-[0-9]+" + postfix);
				updateClassValue(element, regExp, "mt-" + value + postfix);
			}
		},

		'margin-bottom': {
			title: BX.message("BLOCK_STYLE_MARGIN-BOTTOM"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("mb-[0-9]+" + postfix);
				updateClassValue(element, regExp, "mb-" + value + postfix);
			}
		},


		padding: {
			title: BX.message("BLOCK_STYLE_PADDING"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("p-[0-9]+" + postfix);
				updateClassValue(element, regExp, "p-" + value + postfix);
			}
		},

		'padding-left': {
			title: BX.message("BLOCK_STYLE_PADDING-LEFT"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("pl-[0-9]+" + postfix);
				updateClassValue(element, regExp, "pl-" + value + postfix);
			}
		},

		'padding-right': {
			title: BX.message("BLOCK_STYLE_PADDING-RIGHT"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("pr-[0-9]+" + postfix);
				updateClassValue(element, regExp, "pr-" + value + postfix);
			}
		},

		'padding-top': {
			title: BX.message("BLOCK_STYLE_PADDING-TOP"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("pt-[0-9]+" + postfix);
				updateClassValue(element, regExp, "pt-" + value + postfix);
			}
		},

		'padding-bottom': {
			title: BX.message("BLOCK_STYLE_PADDING-BOTTOM"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("pb-[0-9]+" + postfix);
				updateClassValue(element, regExp, "pb-" + value + postfix);
			}
		},

		'border': {
			title: BX.message("BLOCK_STYLE_BORDER"),
			unit: "",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("g-bd-[0-9]+" + postfix);
				updateClassValue(element, regExp, "g-bd-" + value + postfix);
			}
		},

		'font-size': {
			title: BX.message("BLOCK_STYLE_FONT-SIZE"),
			unit: "px",
			min: 0,
			max: 48,
			format: function(element, value, postfix) {
				var regExp = new RegExp("g-font-size-[0-9]+" + postfix);
				updateClassValue(element, regExp, "g-font-size-" + value + postfix);
			}
		},

		'font-family': {
			title: BX.message("BLOCK_STYLE_FONT-FAMILY"),
			unit: "px",
			min: 0,
			max: 250,
			format: function(element, value, postfix) {
				var regExp = new RegExp("g-font-family-[0-9]+" + postfix);
				updateClassValue(element, regExp, "g-font-family-" + value + postfix);
			}
		}
	};
})();