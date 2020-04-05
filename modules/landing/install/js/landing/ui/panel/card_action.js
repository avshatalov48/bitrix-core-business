;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	/**
	 * Implements interface for works with card actions panel
	 *
	 * @extends {BX.Landing.UI.Panel.BaseButtonPanel}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Panel.CardAction = function(id, data)
	{
		BX.Landing.UI.Panel.BaseButtonPanel.apply(this, arguments);
		adjustPanelSize(this);
	};


	/**
	 * Gets element rect
	 * @param {HTMLElement} element
	 * @returns {ClientRect|{left, top, width, height}}
	 */
	function rect(element)
	{
		return element.getBoundingClientRect();
	}


	/**
	 * Checks that size is extra small
	 * @param {ClientRect} rect
	 * @return {boolean}
	 */
	function isXS(rect)
	{
		return rect.height < 100;
	}


	/**
	 * @param {ClientRect} cardRect
	 * @param {ClientRect} panelRect
	 * @return {boolean}
	 */
	function isLeft(cardRect, panelRect)
	{
		return cardRect.width < panelRect.width;
	}


	/**
	 * Gets rect diff
	 * @param cardRect
	 * @param panelRect
	 * @return {{width: number, height: number, top: number, right: number, bottom: number, left: number}}
	 */
	function diffRect(cardRect, panelRect)
	{
		return {
			width: Math.abs(cardRect.width - panelRect.width),
			height: Math.abs(cardRect.height - panelRect.height),
			top: Math.abs(cardRect.top - panelRect.top),
			right: Math.abs(cardRect.right - panelRect.right),
			bottom: Math.abs(cardRect.bottom - panelRect.bottom),
			left: Math.abs(cardRect.left - panelRect.left)
		}
	}


	/**
	 * Adjusts panel size
	 * @param panel
	 */
	function adjustPanelSize(panel)
	{
		BX.DOM.read(function() {
			if (panel.layout && panel.layout.parentNode)
			{
				var cardRect = rect(panel.layout.parentNode);
				var sizeClassName = "landing-ui-size-lg";

				if (isXS(cardRect))
				{
					sizeClassName = "landing-ui-size-xs";
				}

				BX.DOM.write(function() {
					panel.layout.classList.add(sizeClassName);

					BX.DOM.read(function() {
						var panelRect = rect(panel.layout);
						var left = "auto";

						if (isLeft(cardRect, panelRect))
						{
							left = -(diffRect(cardRect, panelRect).width / 2)
						}

						BX.DOM.write(function() {
							panel.layout.style.left = left + "px";
						});
					});
				});
			}
		});
	}


	BX.Landing.UI.Panel.CardAction.prototype = {
		constructor: BX.Landing.UI.Panel.CardAction,
		__proto__: BX.Landing.UI.Panel.BaseButtonPanel.prototype
	}
})();