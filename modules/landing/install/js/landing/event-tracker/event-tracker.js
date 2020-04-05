;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var bind = BX.Landing.Utils.bind;
	var data = BX.Landing.Utils.data;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var slice = BX.Landing.Utils.slice;
	var findParent = BX.Landing.Utils.findParent;
	var trim = BX.Landing.Utils.trim;
	var join = BX.Landing.Utils.join;
	var attr = BX.Landing.Utils.attr;


	/**
	 * Reduces tracking events
	 * @param {BX.Landing.EventTracker} tracker
	 * @param {HTMLElement} element
	 * @param {string} event
	 * @return {*}
	 */
	function trackingReducer(tracker, element, event)
	{
		switch (event)
		{
			case "show":
				return trackShowEvent(tracker, element);
			case "click":
				return trackClickEvent(tracker, element);
			default:
				return (function() {});
		}
	}


	/**
	 * Tracks shows element event
	 * @param {BX.Landing.EventTracker} tracker
	 * @param {HTMLElement} element
	 */
	function trackShowEvent(tracker, element)
	{
		if (!tracker.blockWiewMap.has(element))
		{
			tracker.intersectionObserver.observe(element);
			tracker.blockWiewMap.set(element, null);
		}
	}


	/**
	 * Track clicks on link or child links
	 * @param {BX.Landing.EventTracker} tracker
	 * @param {HTMLElement} element
	 */
	function trackClickEvent(tracker, element)
	{
		var items = [].concat(
			slice(element.querySelectorAll("a")),
			slice(element.querySelectorAll("button")),
			slice(element.querySelectorAll("[data-pseudo-url]"))
		);

		items.forEach(function(item) {
			if (!tracker.clickMap.has(item))
			{
				bind(item, "click", onClick.bind(null, tracker, item));
				tracker.clickMap.set(item, null);
			}
		});
	}


	/**
	 * Handles click events
	 * @param {BX.Landing.EventTracker} tracker
	 * @param item
	 */
	function onClick(tracker, item)
	{
		var block = findParent(item, {className: "block-wrapper"});

		var gaData = {
			type: "click",
			category: join("#", block.id),
			label: trim(item.innerText)
		};

		if (tracker.options.labelFrom === 'href')
		{
			if (item.tagName === 'A' && BX.Type.isStringFilled(item.href))
			{
				gaData.label = item.href;
			}
			else
			{
				var pseudoUrl = BX.Landing.Utils.data(item, 'data-pseudo-url');
				if (
					BX.Type.isPlainObject(pseudoUrl)
					&& pseudoUrl.enabled
					&& BX.Type.isStringFilled(pseudoUrl.href)
				)
				{
					gaData.label = pseudoUrl.href;
				}
			}
		}
		else if (!BX.Type.isStringFilled(gaData.label))
		{
			if (item.tagName === "IMG" && attr(item, "alt"))
			{
				gaData.label = attr(item, "alt");
			}
			else
			{
				var firstChild = item.firstElementChild;
				if (firstChild && firstChild.tagName === "IMG" && attr(firstChild, "alt"))
				{
					gaData.label = attr(firstChild, "alt");
				}
			}
		}

		tracker.push(gaData);
	}



	/**
	 * Fetches tracking options from element
	 * @param {HTMLElement} element
	 * @return {{
	 * 		[event]: string[]
	 * }}
	 */
	function fetchOptionsFromElement(element)
	{
		var sourceOptions = data(element);
		var resultOptions = {};

		if (isPlainObject(sourceOptions) && !isEmpty(sourceOptions))
		{
			resultOptions.event = sourceOptions["data-event-tracker"] || [];
			resultOptions.labelFrom = sourceOptions["data-event-tracker-label-from"] || "text";
		}

		return resultOptions;
	}


	/**
	 * Runs intersection timer
	 * @param {BX.Landing.EventTracker} tracker
	 * @param {HTMLElement} element
	 */
	function runIntersectionTimer(tracker, element)
	{
		clearIntersectionTimer(tracker, element);

		var timerId = setTimeout(function() {
			var block = findParent(element, {className: "block-wrapper"});
			tracker.push({category: "Block", type: "show", label: "#" + block.id});
		}, 1000);

		tracker.blockWiewMap.set(element, timerId);
	}


	/**
	 * Clears intersection timer
	 * @param {BX.Landing.EventTracker} tracker
	 * @param {HTMLElement} element
	 */
	function clearIntersectionTimer(tracker, element)
	{
		clearTimeout(tracker.blockWiewMap.get(element));
		tracker.blockWiewMap.set(element, 0);
	}


	/**
	 * Gets ration for IntersectionObserverEntry
	 * @param {IntersectionObserverEntry} entry
	 * @return {number}
	 */
	function getMinIntersectionRatio(entry)
	{
		var windowHeight = window.innerHeight;

		if (entry.boundingClientRect.height <= (windowHeight / 2))
		{
			return .9;
		}

		if (entry.boundingClientRect.height >= windowHeight)
		{
			var ratio = (
				Math.min(entry.boundingClientRect.height, windowHeight) /
				Math.max(entry.boundingClientRect.height, windowHeight)
			);

			return ratio - ((ratio / 100) * 10);
		}

		return 0.70;
	}


	/**
	 * Handles intersection event
	 * @param {BX.Landing.EventTracker} tracker
	 * @param {IntersectionObserverEntry[]} entries
	 */
	function onIntersect(tracker, entries)
	{
		entries.forEach(function(entry) {
			if (entry.intersectionRatio >= getMinIntersectionRatio(entry))
			{
				runIntersectionTimer(tracker, entry.target);
				return;
			}

			clearIntersectionTimer(tracker, entry.target);
		})
	}


	/**
	 * Implements interface for works with page events for analytics
	 * @constructor
	 */
	BX.Landing.EventTracker = function()
	{
		this.intersectionObserver = new IntersectionObserver(
			onIntersect.bind(null, this),
			{threshold: [0, .05, .1, .2, .3, .4, .5, .6, .7, .8, .9, 1]}
		);
		this.blockWiewMap = new WeakMap();
		this.clickMap = new WeakMap();
		this.services = [
			new BX.Landing.EventTracker.Service.GoogleAnalytics()
		];
		this.options = fetchOptionsFromElement(document.body);
	};


	/**
	 * Gets instance of BX.Landing.EventTracker
	 * @return {*}
	 */
	BX.Landing.EventTracker.getInstance = function()
	{
		return (
			BX.Landing.EventTracker.instance ||
			(BX.Landing.EventTracker.instance = new BX.Landing.EventTracker())
		);
	};


	BX.Landing.EventTracker.prototype = {
		/**
		 * Observes element events
		 * @param {HTMLElement} element
		 */
		observe: function(element)
		{
			var options = fetchOptionsFromElement(document.body);

			if (isPlainObject(options) && !isEmpty(options))
			{
				options.event.forEach(trackingReducer.bind(null, this, element));
			}
		},


		/**
		 * Pushes event to analytics services
		 * @param {{type: string, category: string, label: string, [...]}} data
		 */
		push: function(data)
		{
			this.services.forEach(function(service) {
				service.push(data);
			});
		},


		/**
		 * Runs events tracking
		 */
		run: function()
		{
			slice(document.querySelectorAll(".block-wrapper > *:first-child"))
				.forEach(this.observe, this);
		}
	};
})();