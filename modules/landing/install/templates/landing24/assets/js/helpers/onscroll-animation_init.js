;(function() {
	"use strict";

	if (BX.browser.IsMobile())
	{
		return;
	}

	var observer = new IntersectionObserver(onIntersection);
	var animatedMap = new WeakMap();

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

		var allObservableElements = slice(event.block.querySelectorAll('.js-animation'));

		allObservableElements.forEach(function(element) {
			prepareAnimatedElement(element);
			observer.observe(element);
		});
	});

	onCustomEvent("BX.Landing.UI.Panel.URLList:show", function(layout) {
		var allObservableElements = slice(layout.querySelectorAll('.js-animation'));

		allObservableElements.forEach(function(element) {
			prepareAnimatedElement(element);
			observer.observe(element);
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
				var allObservableElements = slice(event.block.querySelectorAll('.js-animation'));

				allObservableElements.forEach(function(element) {
					prepareAnimatedElement(element);
					observer.observe(element);
				});
			}
		}
	});

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
			if (entry.isIntersecting && !animatedMap.has(entry.target))
			{
				void runAnimation(entry.target)
					.then(function() {
						animatedMap.set(entry.target, true);

						void style(entry.target, {
							"animation-name": "none"
						});

						removeClass(entry.target, "animated");
					});
			}
		});
	}

	/**
	 * Runs element animation
	 * @param {HTMLElement} element
	 * @return {Promise<AnimationEvent>}
	 */
	function runAnimation(element)
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