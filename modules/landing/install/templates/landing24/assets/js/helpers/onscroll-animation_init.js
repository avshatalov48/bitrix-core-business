;(function() {
	"use strict";

	BX.namespace("BX.Landing.OnscrollAnimationHelper");

	if (BX.browser.IsMobile())
	{
		return;
	}

	BX.Landing.OnscrollAnimationHelper.observer = new IntersectionObserver(onIntersection);
	BX.Landing.OnscrollAnimationHelper.animatedMap = new WeakMap();

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var style = BX.Landing.Utils.style;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var onAnimationEnd = BX.Landing.Utils.onAnimationEnd;
	var slice = BX.Landing.Utils.slice;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;

	onCustomEvent("BX.Landing.Block:init", function(event) {
		if (BX.hasClass(event.block, 'landing-designer-block-mode'))
		{
			return ;
		}

		var allObservableElements = BX.Landing.OnscrollAnimationHelper.getBlockAnimatedElements(event.block);

		allObservableElements.forEach(function(element) {
			prepareAnimatedElement(element);
			BX.Landing.OnscrollAnimationHelper.observer.observe(element);
		});
	});

	onCustomEvent("BX.Landing.UI.Panel.URLList:show", function(layout) {
		var allObservableElements = BX.Landing.OnscrollAnimationHelper.getBlockAnimatedElements(layout);

		allObservableElements.forEach(function(element) {
			prepareAnimatedElement(element);
			BX.Landing.OnscrollAnimationHelper.observer.observe(element);
		});
	});

	onCustomEvent("BX.Landing.Block:updateStyle", function(event) {
		if (isPlainObject(event.data) && isPlainObject(event.data.affect))
		{
			var isAnimationChange = event.data.affect.some(function(prop) {
				return prop === "animation-name";
			});

			if (isAnimationChange)
			{
				var allObservableElements = BX.Landing.OnscrollAnimationHelper.getBlockAnimatedElements(event.block);

				allObservableElements.forEach(function(element) {
					prepareAnimatedElement(element);
					observer.observe(element);
				});
			}
		}
	});

	BX.Landing.OnscrollAnimationHelper.selector = '.js-animation';
	BX.Landing.OnscrollAnimationHelper.getBlockAnimatedElements = function(block)
	{
		return slice(block.querySelectorAll(BX.Landing.OnscrollAnimationHelper.selector));
	}

	/**
	 * Prepares animated element
	 * @param {HTMLElement} element
	 */
	function prepareAnimatedElement(element)
	{
		void style(element, {
			"animation-duration": "1000ms",
			"visibility": "hidden",
			"animation-name": "none",
			"animation-play-state": "paused"
		});
	}

	/**
	 * @param {IntersectionObserverEntry[]} entries
	 */
	function onIntersection(entries)
	{
		entries.forEach(function(entry) {
			if (entry.isIntersecting)
			{
				BX.Landing.OnscrollAnimationHelper.animateElement(entry.target)
			}
		});
	}

	/**
	 * Animate element and do service actions
	 * @param element
	 */
	BX.Landing.OnscrollAnimationHelper.animateElement = function(element)
	{
		if (!BX.Landing.OnscrollAnimationHelper.animatedMap.has(element))
		{
			return runElementAnimation(element)
				.then(function ()
				{
					BX.Landing.OnscrollAnimationHelper.animatedMap.set(element, true);

					void style(element, {
						"animation-name": "none",
					});

					removeClass(element, "animated");
				});
		}

		return Promise.resolve();
	}

	/**
	 * Just animate lement
	 * @param {HTMLElement} element
	 * @return {Promise<AnimationEvent>}
	 */
	function runElementAnimation(element)
	{
		addClass(element, "animated");
		void style(element, {
			"visibility": "",
			"animation-name": "",
			"animation-play-state": "running"
		});

		return onAnimationEnd(element);
	}
})();