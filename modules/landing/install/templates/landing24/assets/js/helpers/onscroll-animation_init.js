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
	var hasClass = BX.Landing.Utils.hasClass;
	var style = BX.Landing.Utils.style;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isArray = BX.Type.isArray;
	var onAnimationEnd = BX.Landing.Utils.onAnimationEnd;
	var slice = BX.Landing.Utils.slice;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var  timeA;

	onCustomEvent("BX.Landing.Block:init", function(event) {
		timeA = Date.now();
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
		if (isPlainObject(event.data) && isArray(event.data.affect) && isArray(event.node))
		{
			const isAnimationChange = event.data.affect.some(function(prop) {
				return prop === BX.Landing.OnscrollAnimationHelper.PROP;
			});

			if (isAnimationChange)
			{
				const allObservableElements = BX.Landing.OnscrollAnimationHelper.getBlockAnimatedElements(event.block);
				allObservableElements.forEach(element => {
					if (event.node.indexOf(element) !== -1)
					{
						BX.Landing.OnscrollAnimationHelper.animatedMap.delete(element);
						prepareAnimatedElement(element);
						BX.Landing.OnscrollAnimationHelper.animateElement(element);
					}
				});
			}
		}
	});

	BX.Landing.OnscrollAnimationHelper.SELECTOR = '.js-animation:not(.animation-none)';
	BX.Landing.OnscrollAnimationHelper.PROP = 'animation-name';
	BX.Landing.OnscrollAnimationHelper.ANIMATIONS = [
		'bounce',
		'flash',
		'pulse',
		'rubberBand',
		'shake',
		'headShake',
		'swing',
		'tada',
		'wobble',
		'jello',
		'bounceIn',
		'bounceInDown',
		'bounceInLeft',
		'bounceInRight',
		'bounceInUp',
		'fadeIn',
		'fadeInDown',
		'fadeInDownBig',
		'fadeInLeft',
		'fadeInLeftBig',
		'fadeInRight',
		'fadeInRightBig',
		'fadeInUp',
		'fadeInUpBig',
		'flip',
		'flipInX',
		'flipInY',
		'lightSpeedIn',
		'rotateIn',
		'rotateInDownLeft',
		'rotateInDownRight',
		'rotateInUpLeft',
		'rotateInUpRight',
		'rollIn',
		'zoomIn',
		'zoomToIn',
		'zoomInDown',
		'zoomInLeft',
		'zoomInRight',
		'zoomInUp',
		'slideInDown',
		'slideInLeft',
		'slideInRight',
		'slideInUp',
	];

	BX.Landing.OnscrollAnimationHelper.getBlockAnimatedElements = function(block)
	{
		return slice(block.querySelectorAll(BX.Landing.OnscrollAnimationHelper.SELECTOR)).filter((element) => {
			for (const animation of BX.Landing.OnscrollAnimationHelper.ANIMATIONS)
			{
				if (hasClass(element, animation))
				{
					return true;
				}
			}

			return false;
		});
	};

	/**
	 * Prepares animated element
	 * @param {HTMLElement} element
	 */
	function prepareAnimatedElement(element)
	{
		void style(element, {
			"animation-duration": "1000ms",
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
					if (hasClass(element, "modified"))
					{
						removeClass(element, "modified");
					}
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
		if (
			(window.performance.timing.domContentLoadedEventStart - window.performance.timing.domLoading > 400)
			&& (window.performance.timing.domComplete === 0)
			&& BX.Landing.getMode() !== "edit"
		)
		{
			addClass(element, "modified");
		}
		addClass(element, "animated");
		void style(element, {
			"animation-name": "",
			"animation-play-state": "running"
		});

		return onAnimationEnd(element);
	}
})();