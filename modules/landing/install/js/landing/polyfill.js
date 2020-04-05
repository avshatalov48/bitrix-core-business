/**
 * Element.matches polyfill
 */
(function(element) {
	"use strict";

	if (!element.matches && element.matchesSelector)
	{
		element.matches = element.matchesSelector;
	}

	if (!element.matches && !element.matchesSelector)
	{
		element.matches = function(selector) {
			var matches = document.querySelectorAll(selector), th = this;

			return Array.prototype.some.call(matches, function(e) {
				return e === th;
			});
		};
	}

})(Element.prototype);


/**
 * Promise polyfill
 */
(function(window) {
	"use strict";

	if (typeof window.Promise === "undefined" || window.Promise.toString().indexOf("[native code]") === -1)
	{
		var PROMISE_STATUS = "[[PromiseStatus]]";
		var PROMISE_VALUE = "[[PromiseValue]]";
		var STATUS_PENDING = "pending";
		var STATUS_INTERNAL_PENDING = "internal pending";
		var STATUS_RESOLVED = "resolved";
		var STATUS_REJECTED = "rejected";


		/**
		 * Handles promise done
		 * @param {Promise} promise
		 * @param {Handler} deferred
		 */
		var handle = function(promise, deferred)
		{
			if (promise[PROMISE_STATUS] === STATUS_INTERNAL_PENDING)
			{
				promise = promise[PROMISE_VALUE];
			}

			if (promise[PROMISE_STATUS] === STATUS_PENDING)
			{
				promise.deferreds.push(deferred);
			}
			else
			{
				promise.handled = true;

				setTimeout(function() {
					var callback = promise[PROMISE_STATUS] === STATUS_RESOLVED ? deferred.onFulfilled : deferred.onRejected;

					if (callback)
					{
						try {
							resolve(deferred.promise, callback(promise[PROMISE_VALUE]));
						} catch (err) {
							reject(deferred.promise, err);
						}
					}
					else
					{
						if (promise[PROMISE_STATUS] === STATUS_RESOLVED)
						{
							resolve(deferred.promise, promise[PROMISE_VALUE]);
						}
						else
						{
							reject(deferred.promise, promise[PROMISE_VALUE]);
						}
					}
				}, 0);
			}
		};


		/**
		 * Resolves promise with value
		 * @param {Promise} promise
		 * @param {*} value
		 */
		var resolve = function(promise, value) {
			if (value === promise)
			{
				throw new TypeError("A promise cannot be resolved with it promise.");
			}

			try {
				if (value && (typeof value === "object" || typeof value === "function"))
				{
					if (value instanceof Promise)
					{
						promise[PROMISE_STATUS] = STATUS_INTERNAL_PENDING;
						promise[PROMISE_VALUE] = value;
						finale(promise);
						return;
					}
					else if (typeof value.then === "function")
					{
						executePromise(value.then.bind(value), promise);
						return;
					}
				}

				promise[PROMISE_STATUS] = STATUS_RESOLVED;
				promise[PROMISE_VALUE] = value;
				finale(promise);
			} catch (err) {
				reject(promise, err);
			}
		};


		/**
		 * Rejects promise with reason
		 * @param promise
		 * @param reason
		 */
		var reject = function(promise, reason)
		{
			promise[PROMISE_STATUS] = STATUS_REJECTED;
			promise[PROMISE_VALUE] = reason;
			finale(promise);
		};


		/**
		 * Calls async callback
		 * @param {Promise} promise
		 */
		var finale = function(promise)
		{
			if (promise[PROMISE_STATUS] === STATUS_REJECTED && promise.deferreds.length === 0)
			{
				setTimeout(function() {
					if (!promise.handled)
					{
						console.error("Unhandled Promise Rejection: " + promise[PROMISE_VALUE]);
					}
				}, 0);
			}

			promise.deferreds.forEach(function(deferred) {
				handle(promise, deferred);
			});

			promise.deferreds = null;
		};


		/**
		 * Executes promise
		 * @param {function} resolver - Resolver
		 * @param {Promise} promise
		 */
		var executePromise = function(resolver, promise)
		{
			var done = false;

			try {
				resolver(resolveWrapper, rejectWrapper);
			} catch (err) {
				if (!done)
				{
					done = true;
					reject(promise, err);
				}
			}

			// Resolve function
			function resolveWrapper(value)
			{
				if (!done)
				{
					done = true;
					resolve(promise, value);
				}
			}

			// Reject function
			function rejectWrapper(reason)
			{
				if (!done)
				{
					done = true;
					reject(promise, reason);
				}
			}
		};


		/**
		 * Implements interface for works with promise handlers
		 * @param {?function} [onFulfilled]
		 * @param {?function} [onRejected]
		 * @param {Promise} promise
		 *
		 * @constructor
		 */
		var Handler = function(onFulfilled, onRejected, promise)
		{
			this.onFulfilled = typeof onFulfilled === "function" ? onFulfilled : null;
			this.onRejected = typeof onRejected === "function" ? onRejected : null;
			this.promise = promise;
		};


		/**
		 * Implements Promise polyfill
		 * @param {function} resolver
		 *
		 * @constructor
		 */
		var Promise = function(resolver)
		{
			this[PROMISE_STATUS] = STATUS_PENDING;
			this[PROMISE_VALUE] = null;
			this.handled = false;
			this.deferreds = [];

			// Try execute promise resolver
			executePromise(resolver, this);
		};


		/**
		 * Appends a rejection handler callback to the promise,
		 * and returns a new promise resolving to the return value of the callback if it is called,
		 * or to its original fulfillment value if the promise is instead fulfilled.
		 *
		 * @param {function} onRejected
		 */
		Promise.prototype["catch"] = function(onRejected) {
			return this.then(null, onRejected);
		};


		/**
		 * Appends fulfillment and rejection handlers to the promise,
		 * and returns a new promise resolving to the return value of the called handler,
		 * or to its original settled value if the promise was not handled
		 * (i.e. if the relevant handler onFulfilled or onRejected is not a function).
		 *
		 * @param {function} onFulfilled
		 * @param {function} [onRejected]
		 * @returns {Promise}
		 */
		Promise.prototype.then = function(onFulfilled, onRejected)
		{
			var promise = new Promise(function() {});
			handle(this, new Handler(onFulfilled, onRejected, promise));
			return promise;
		};


		/**
		 * The method returns a single Promise that resolves when all of the promises
		 * in the iterable argument have resolved or when the iterable argument contains no promises.
		 * It rejects with the reason of the first promise that rejects.
		 *
		 * @static
		 * @param iterable - An iterable object such as an Array or String
		 * @return {Promise}
		 */
		Promise.all = function(iterable)
		{
			var args = [].slice.call(iterable);

			return new Promise(function(resolve, reject) {
				if (args.length === 0)
				{
					resolve(args);
				}
				else
				{
					var remaining = args.length;

					var res = function(i, val)
					{
						try {
							if (val && (typeof val === "object" || typeof val === "function"))
							{
								if (typeof val.then === "function")
								{
									val.then.call(val, function(val) {
										res(i, val);
									}, reject);
									return;
								}
							}

							args[i] = val;

							if (--remaining === 0)
							{
								resolve(args);
							}
						} catch (ex) {
							reject(ex);
						}
					};

					for (var i = 0; i < args.length; i++)
					{
						res(i, args[i]);
					}
				}
			});
		};


		/**
		 * The method returns a Promise object that is resolved with the given value.
		 * If the value is a thenable (i.e. has a "then" method),
		 * the returned promise will "follow" that thenable, adopting its eventual state;
		 * if the value was a promise, that object becomes the result of the call to Promise.resolve;
		 * otherwise the returned promise will be fulfilled with the value.
		 *
		 * @param {*|Promise} value - Argument to be resolved by this Promise. Can also be a Promise or a thenable to resolve.
		 * @returns {Promise}
		 */
		Promise.resolve = function(value)
		{
			if (value && typeof value === "object" && value.constructor === Promise)
			{
				return value;
			}

			return new Promise(function(resolve) {
				resolve(value);
			});
		};


		/**
		 * The method returns a Promise object that is rejected with the given reason.
		 *
		 * @param {*} reason - Reason why this Promise rejected.
		 * @returns {Promise}
		 */
		Promise.reject = function(reason)
		{
			return new Promise(function(resolve, reject) {
				reject(reason);
			});
		};


		/**
		 * The Promise.race(iterable) method returns a promise that resolves or rejects
		 * as soon as one of the promises in the iterable resolves or rejects,
		 * with the value or reason from that promise.
		 *
		 * @static
		 * @param iterable - An iterable object, such as an Array.
		 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Iteration_protocols
		 * @returns {Promise}
		 */
		Promise.race = function(iterable)
		{
			return new Promise(function(resolve, reject) {
				for (var i = 0, len = iterable.length; i < len; i++)
				{
					iterable[i].then(resolve, reject);
				}
			});
		};

		window.Promise = Promise;
	}
})(window);


/**
 * String.prototype.includes polyfill
 */
(function() {
	"use strict";

	if (!String.prototype.includes)
	{
		String.prototype.includes = function(search, start)
		{
			if (typeof start !== 'number')
			{
				start = 0;
			}

			if (typeof search !== "string" ||
				(start + search.length) > this.length)
			{
				return false;
			}
			else
			{
				return this.indexOf(search, start) !== -1;
			}
		};
	}
})();


/**
 * Array.prototype.find polyfill
 */
(function() {
	"use strict";

	if (typeof Array.prototype.find !== "function")
	{
		Array.prototype.find = function(predicate, thisArg)
		{
			if (this === null)
			{
				throw new TypeError('Cannot read property \'find\' of null');
			}

			if (typeof predicate !== "function")
			{
				throw new TypeError(typeof predicate + ' is not a function');
			}

			var arrLength = this.length;

			for (var i = 0; i < arrLength; i++)
			{
				if (predicate.call(thisArg, this[i], i, this)) {
					return this[i];
				}
			}
		};
	}
})();


/**
 * Array.prototype.find polyfill
 */
(function() {
	"use strict";

	if (typeof Array.prototype.includes !== "function")
	{
		Array.prototype.includes = function(element)
		{
			var result = this.find(function(currentElement) {
				return currentElement === element;
			});

			return result === element;
		};
	}
})();


/**
 * Object.assign polyfill
 */
(function() {
	"use strict";

	if (!Object.assign)
	{
		Object.defineProperty(Object, 'assign', {
			enumerable: false,
			configurable: true,
			writable: true,
			value: function(target, firstSource) {
				if (target === undefined || target === null)
				{
					throw new TypeError('Cannot convert first argument to object');
				}

				var to = Object(target);

				for (var i = 1; i < arguments.length; i++)
				{
					var nextSource = arguments[i];

					if (nextSource === undefined || nextSource === null)
					{
						continue;
					}

					var keysArray = Object.keys(Object(nextSource));

					for (var nextIndex = 0, len = keysArray.length; nextIndex < len; nextIndex++)
					{
						var nextKey = keysArray[nextIndex];
						var desc = Object.getOwnPropertyDescriptor(nextSource, nextKey);

						if (desc !== undefined && desc.enumerable)
						{
							to[nextKey] = nextSource[nextKey];
						}
					}
				}

				return to;
			}
		});
	}
})();


/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the W3C SOFTWARE AND DOCUMENT NOTICE AND LICENSE.
 *
 *  https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
 *
 */

(function(window, document) {
	'use strict';

	if ('IntersectionObserver' in window &&
		'IntersectionObserverEntry' in window &&
		'intersectionRatio' in window.IntersectionObserverEntry.prototype) {

		// Minimal polyfill for Edge 15's lack of `isIntersecting`
		// See: https://github.com/w3c/IntersectionObserver/issues/211
		if (!('isIntersecting' in window.IntersectionObserverEntry.prototype)) {
			Object.defineProperty(window.IntersectionObserverEntry.prototype,
				'isIntersecting', {
					get: function () {
						return this.intersectionRatio > 0;
					}
				});
		}
		return;
	}


	/**
	 * An IntersectionObserver registry. This registry exists to hold a strong
	 * reference to IntersectionObserver instances currently observering a target
	 * element. Without this registry, instances without another reference may be
	 * garbage collected.
	 */
	var registry = [];


	/**
	 * Creates the global IntersectionObserverEntry constructor.
	 * https://w3c.github.io/IntersectionObserver/#intersection-observer-entry
	 * @param {Object} entry A dictionary of instance properties.
	 * @constructor
	 */
	function IntersectionObserverEntry(entry) {
		this.time = entry.time;
		this.target = entry.target;
		this.rootBounds = entry.rootBounds;
		this.boundingClientRect = entry.boundingClientRect;
		this.intersectionRect = entry.intersectionRect || getEmptyRect();
		this.isIntersecting = !!entry.intersectionRect;

		// Calculates the intersection ratio.
		var targetRect = this.boundingClientRect;
		var targetArea = targetRect.width * targetRect.height;
		var intersectionRect = this.intersectionRect;
		var intersectionArea = intersectionRect.width * intersectionRect.height;

		// Sets intersection ratio.
		if (targetArea) {
			this.intersectionRatio = intersectionArea / targetArea;
		} else {
			// If area is zero and is intersecting, sets to 1, otherwise to 0
			this.intersectionRatio = this.isIntersecting ? 1 : 0;
		}
	}


	/**
	 * Creates the global IntersectionObserver constructor.
	 * https://w3c.github.io/IntersectionObserver/#intersection-observer-interface
	 * @param {Function} callback The function to be invoked after intersection
	 *     changes have queued. The function is not invoked if the queue has
	 *     been emptied by calling the `takeRecords` method.
	 * @param {Object=} opt_options Optional configuration options.
	 * @constructor
	 */
	function IntersectionObserver(callback, opt_options) {

		var options = opt_options || {};

		if (typeof callback != 'function') {
			throw new Error('callback must be a function');
		}

		if (options.root && options.root.nodeType != 1) {
			throw new Error('root must be an Element');
		}

		// Binds and throttles `this._checkForIntersections`.
		this._checkForIntersections = throttle(
			this._checkForIntersections.bind(this), this.THROTTLE_TIMEOUT);

		// Private properties.
		this._callback = callback;
		this._observationTargets = [];
		this._queuedEntries = [];
		this._rootMarginValues = this._parseRootMargin(options.rootMargin);

		// Public properties.
		this.thresholds = this._initThresholds(options.threshold);
		this.root = options.root || null;
		this.rootMargin = this._rootMarginValues.map(function(margin) {
			return margin.value + margin.unit;
		}).join(' ');
	}


	/**
	 * The minimum interval within which the document will be checked for
	 * intersection changes.
	 */
	IntersectionObserver.prototype.THROTTLE_TIMEOUT = 100;


	/**
	 * The frequency in which the polyfill polls for intersection changes.
	 * this can be updated on a per instance basis and must be set prior to
	 * calling `observe` on the first target.
	 */
	IntersectionObserver.prototype.POLL_INTERVAL = null;

	/**
	 * Use a mutation observer on the root element
	 * to detect intersection changes.
	 */
	IntersectionObserver.prototype.USE_MUTATION_OBSERVER = true;


	/**
	 * Starts observing a target element for intersection changes based on
	 * the thresholds values.
	 * @param {Element} target The DOM element to observe.
	 */
	IntersectionObserver.prototype.observe = function(target) {
		var isTargetAlreadyObserved = this._observationTargets.some(function(item) {
			return item.element == target;
		});

		if (isTargetAlreadyObserved) {
			return;
		}

		if (!(target && target.nodeType == 1)) {
			throw new Error('target must be an Element');
		}

		this._registerInstance();
		this._observationTargets.push({element: target, entry: null});
		this._monitorIntersections();
		this._checkForIntersections();
	};


	/**
	 * Stops observing a target element for intersection changes.
	 * @param {Element} target The DOM element to observe.
	 */
	IntersectionObserver.prototype.unobserve = function(target) {
		this._observationTargets =
			this._observationTargets.filter(function(item) {

				return item.element != target;
			});
		if (!this._observationTargets.length) {
			this._unmonitorIntersections();
			this._unregisterInstance();
		}
	};


	/**
	 * Stops observing all target elements for intersection changes.
	 */
	IntersectionObserver.prototype.disconnect = function() {
		this._observationTargets = [];
		this._unmonitorIntersections();
		this._unregisterInstance();
	};


	/**
	 * Returns any queue entries that have not yet been reported to the
	 * callback and clears the queue. This can be used in conjunction with the
	 * callback to obtain the absolute most up-to-date intersection information.
	 * @return {Array} The currently queued entries.
	 */
	IntersectionObserver.prototype.takeRecords = function() {
		var records = this._queuedEntries.slice();
		this._queuedEntries = [];
		return records;
	};


	/**
	 * Accepts the threshold value from the user configuration object and
	 * returns a sorted array of unique threshold values. If a value is not
	 * between 0 and 1 and error is thrown.
	 * @private
	 * @param {Array|number=} opt_threshold An optional threshold value or
	 *     a list of threshold values, defaulting to [0].
	 * @return {Array} A sorted list of unique and valid threshold values.
	 */
	IntersectionObserver.prototype._initThresholds = function(opt_threshold) {
		var threshold = opt_threshold || [0];
		if (!Array.isArray(threshold)) threshold = [threshold];

		return threshold.sort().filter(function(t, i, a) {
			if (typeof t != 'number' || isNaN(t) || t < 0 || t > 1) {
				throw new Error('threshold must be a number between 0 and 1 inclusively');
			}
			return t !== a[i - 1];
		});
	};


	/**
	 * Accepts the rootMargin value from the user configuration object
	 * and returns an array of the four margin values as an object containing
	 * the value and unit properties. If any of the values are not properly
	 * formatted or use a unit other than px or %, and error is thrown.
	 * @private
	 * @param {string=} opt_rootMargin An optional rootMargin value,
	 *     defaulting to '0px'.
	 * @return {Array<Object>} An array of margin objects with the keys
	 *     value and unit.
	 */
	IntersectionObserver.prototype._parseRootMargin = function(opt_rootMargin) {
		var marginString = opt_rootMargin || '0px';
		var margins = marginString.split(/\s+/).map(function(margin) {
			var parts = /^(-?\d*\.?\d+)(px|%)$/.exec(margin);
			if (!parts) {
				throw new Error('rootMargin must be specified in pixels or percent');
			}
			return {value: parseFloat(parts[1]), unit: parts[2]};
		});

		// Handles shorthand.
		margins[1] = margins[1] || margins[0];
		margins[2] = margins[2] || margins[0];
		margins[3] = margins[3] || margins[1];

		return margins;
	};


	/**
	 * Starts polling for intersection changes if the polling is not already
	 * happening, and if the page's visibilty state is visible.
	 * @private
	 */
	IntersectionObserver.prototype._monitorIntersections = function() {
		if (!this._monitoringIntersections) {
			this._monitoringIntersections = true;

			// If a poll interval is set, use polling instead of listening to
			// resize and scroll events or DOM mutations.
			if (this.POLL_INTERVAL) {
				this._monitoringInterval = setInterval(
					this._checkForIntersections, this.POLL_INTERVAL);
			}
			else {
				addEvent(window, 'resize', this._checkForIntersections, true);
				addEvent(document, 'scroll', this._checkForIntersections, true);

				if (this.USE_MUTATION_OBSERVER && 'MutationObserver' in window) {
					this._domObserver = new MutationObserver(this._checkForIntersections);
					this._domObserver.observe(document, {
						attributes: true,
						childList: true,
						characterData: true,
						subtree: true
					});
				}
			}
		}
	};


	/**
	 * Stops polling for intersection changes.
	 * @private
	 */
	IntersectionObserver.prototype._unmonitorIntersections = function() {
		if (this._monitoringIntersections) {
			this._monitoringIntersections = false;

			clearInterval(this._monitoringInterval);
			this._monitoringInterval = null;

			removeEvent(window, 'resize', this._checkForIntersections, true);
			removeEvent(document, 'scroll', this._checkForIntersections, true);

			if (this._domObserver) {
				this._domObserver.disconnect();
				this._domObserver = null;
			}
		}
	};


	/**
	 * Scans each observation target for intersection changes and adds them
	 * to the internal entries queue. If new entries are found, it
	 * schedules the callback to be invoked.
	 * @private
	 */
	IntersectionObserver.prototype._checkForIntersections = function() {
		var rootIsInDom = this._rootIsInDom();
		var rootRect = rootIsInDom ? this._getRootRect() : getEmptyRect();

		this._observationTargets.forEach(function(item) {
			var target = item.element;
			var targetRect = getBoundingClientRect(target);
			var rootContainsTarget = this._rootContainsTarget(target);
			var oldEntry = item.entry;
			var intersectionRect = rootIsInDom && rootContainsTarget &&
				this._computeTargetAndRootIntersection(target, rootRect);

			var newEntry = item.entry = new IntersectionObserverEntry({
				time: now(),
				target: target,
				boundingClientRect: targetRect,
				rootBounds: rootRect,
				intersectionRect: intersectionRect
			});

			if (!oldEntry) {
				this._queuedEntries.push(newEntry);
			} else if (rootIsInDom && rootContainsTarget) {
				// If the new entry intersection ratio has crossed any of the
				// thresholds, add a new entry.
				if (this._hasCrossedThreshold(oldEntry, newEntry)) {
					this._queuedEntries.push(newEntry);
				}
			} else {
				// If the root is not in the DOM or target is not contained within
				// root but the previous entry for this target had an intersection,
				// add a new record indicating removal.
				if (oldEntry && oldEntry.isIntersecting) {
					this._queuedEntries.push(newEntry);
				}
			}
		}, this);

		if (this._queuedEntries.length) {
			this._callback(this.takeRecords(), this);
		}
	};


	/**
	 * Accepts a target and root rect computes the intersection between then
	 * following the algorithm in the spec.
	 * TODO(philipwalton): at this time clip-path is not considered.
	 * https://w3c.github.io/IntersectionObserver/#calculate-intersection-rect-algo
	 * @param {Element} target The target DOM element
	 * @param {Object} rootRect The bounding rect of the root after being
	 *     expanded by the rootMargin value.
	 * @return {?Object} The final intersection rect object or undefined if no
	 *     intersection is found.
	 * @private
	 */
	IntersectionObserver.prototype._computeTargetAndRootIntersection =
		function(target, rootRect) {

			// If the element isn't displayed, an intersection can't happen.
			if (window.getComputedStyle(target).display == 'none') return;

			var targetRect = getBoundingClientRect(target);
			var intersectionRect = targetRect;
			var parent = getParentNode(target);
			var atRoot = false;

			while (!atRoot) {
				var parentRect = null;
				var parentComputedStyle = parent.nodeType == 1 ?
					window.getComputedStyle(parent) : {};

				// If the parent isn't displayed, an intersection can't happen.
				if (parentComputedStyle.display == 'none') return;

				if (parent == this.root || parent == document) {
					atRoot = true;
					parentRect = rootRect;
				} else {
					// If the element has a non-visible overflow, and it's not the <body>
					// or <html> element, update the intersection rect.
					// Note: <body> and <html> cannot be clipped to a rect that's not also
					// the document rect, so no need to compute a new intersection.
					if (parent != document.body &&
						parent != document.documentElement &&
						parentComputedStyle.overflow != 'visible') {
						parentRect = getBoundingClientRect(parent);
					}
				}

				// If either of the above conditionals set a new parentRect,
				// calculate new intersection data.
				if (parentRect) {
					intersectionRect = computeRectIntersection(parentRect, intersectionRect);

					if (!intersectionRect) break;
				}
				parent = getParentNode(parent);
			}
			return intersectionRect;
		};


	/**
	 * Returns the root rect after being expanded by the rootMargin value.
	 * @return {Object} The expanded root rect.
	 * @private
	 */
	IntersectionObserver.prototype._getRootRect = function() {
		var rootRect;
		if (this.root) {
			rootRect = getBoundingClientRect(this.root);
		} else {
			// Use <html>/<body> instead of window since scroll bars affect size.
			var html = document.documentElement;
			var body = document.body;
			rootRect = {
				top: 0,
				left: 0,
				right: html.clientWidth || body.clientWidth,
				width: html.clientWidth || body.clientWidth,
				bottom: html.clientHeight || body.clientHeight,
				height: html.clientHeight || body.clientHeight
			};
		}
		return this._expandRectByRootMargin(rootRect);
	};


	/**
	 * Accepts a rect and expands it by the rootMargin value.
	 * @param {Object} rect The rect object to expand.
	 * @return {Object} The expanded rect.
	 * @private
	 */
	IntersectionObserver.prototype._expandRectByRootMargin = function(rect) {
		var margins = this._rootMarginValues.map(function(margin, i) {
			return margin.unit == 'px' ? margin.value :
				margin.value * (i % 2 ? rect.width : rect.height) / 100;
		});
		var newRect = {
			top: rect.top - margins[0],
			right: rect.right + margins[1],
			bottom: rect.bottom + margins[2],
			left: rect.left - margins[3]
		};
		newRect.width = newRect.right - newRect.left;
		newRect.height = newRect.bottom - newRect.top;

		return newRect;
	};


	/**
	 * Accepts an old and new entry and returns true if at least one of the
	 * threshold values has been crossed.
	 * @param {?IntersectionObserverEntry} oldEntry The previous entry for a
	 *    particular target element or null if no previous entry exists.
	 * @param {IntersectionObserverEntry} newEntry The current entry for a
	 *    particular target element.
	 * @return {boolean} Returns true if a any threshold has been crossed.
	 * @private
	 */
	IntersectionObserver.prototype._hasCrossedThreshold =
		function(oldEntry, newEntry) {

			// To make comparing easier, an entry that has a ratio of 0
			// but does not actually intersect is given a value of -1
			var oldRatio = oldEntry && oldEntry.isIntersecting ?
				oldEntry.intersectionRatio || 0 : -1;
			var newRatio = newEntry.isIntersecting ?
				newEntry.intersectionRatio || 0 : -1;

			// Ignore unchanged ratios
			if (oldRatio === newRatio) return;

			for (var i = 0; i < this.thresholds.length; i++) {
				var threshold = this.thresholds[i];

				// Return true if an entry matches a threshold or if the new ratio
				// and the old ratio are on the opposite sides of a threshold.
				if (threshold == oldRatio || threshold == newRatio ||
					threshold < oldRatio !== threshold < newRatio) {
					return true;
				}
			}
		};


	/**
	 * Returns whether or not the root element is an element and is in the DOM.
	 * @return {boolean} True if the root element is an element and is in the DOM.
	 * @private
	 */
	IntersectionObserver.prototype._rootIsInDom = function() {
		return !this.root || containsDeep(document, this.root);
	};


	/**
	 * Returns whether or not the target element is a child of root.
	 * @param {Element} target The target element to check.
	 * @return {boolean} True if the target element is a child of root.
	 * @private
	 */
	IntersectionObserver.prototype._rootContainsTarget = function(target) {
		return containsDeep(this.root || document, target);
	};


	/**
	 * Adds the instance to the global IntersectionObserver registry if it isn't
	 * already present.
	 * @private
	 */
	IntersectionObserver.prototype._registerInstance = function() {
		if (registry.indexOf(this) < 0) {
			registry.push(this);
		}
	};


	/**
	 * Removes the instance from the global IntersectionObserver registry.
	 * @private
	 */
	IntersectionObserver.prototype._unregisterInstance = function() {
		var index = registry.indexOf(this);
		if (index != -1) registry.splice(index, 1);
	};


	/**
	 * Returns the result of the performance.now() method or null in browsers
	 * that don't support the API.
	 * @return {number} The elapsed time since the page was requested.
	 */
	function now() {
		return window.performance && performance.now && performance.now();
	}


	/**
	 * Throttles a function and delays its executiong, so it's only called at most
	 * once within a given time period.
	 * @param {Function} fn The function to throttle.
	 * @param {number} timeout The amount of time that must pass before the
	 *     function can be called again.
	 * @return {Function} The throttled function.
	 */
	function throttle(fn, timeout) {
		var timer = null;
		return function () {
			if (!timer) {
				timer = setTimeout(function() {
					fn();
					timer = null;
				}, timeout);
			}
		};
	}


	/**
	 * Adds an event handler to a DOM node ensuring cross-browser compatibility.
	 * @param {Node} node The DOM node to add the event handler to.
	 * @param {string} event The event name.
	 * @param {Function} fn The event handler to add.
	 * @param {boolean} opt_useCapture Optionally adds the even to the capture
	 *     phase. Note: this only works in modern browsers.
	 */
	function addEvent(node, event, fn, opt_useCapture) {
		if (typeof node.addEventListener == 'function') {
			node.addEventListener(event, fn, opt_useCapture || false);
		}
		else if (typeof node.attachEvent == 'function') {
			node.attachEvent('on' + event, fn);
		}
	}


	/**
	 * Removes a previously added event handler from a DOM node.
	 * @param {Node} node The DOM node to remove the event handler from.
	 * @param {string} event The event name.
	 * @param {Function} fn The event handler to remove.
	 * @param {boolean} opt_useCapture If the event handler was added with this
	 *     flag set to true, it should be set to true here in order to remove it.
	 */
	function removeEvent(node, event, fn, opt_useCapture) {
		if (typeof node.removeEventListener == 'function') {
			node.removeEventListener(event, fn, opt_useCapture || false);
		}
		else if (typeof node.detatchEvent == 'function') {
			node.detatchEvent('on' + event, fn);
		}
	}


	/**
	 * Returns the intersection between two rect objects.
	 * @param {Object} rect1 The first rect.
	 * @param {Object} rect2 The second rect.
	 * @return {?Object} The intersection rect or undefined if no intersection
	 *     is found.
	 */
	function computeRectIntersection(rect1, rect2) {
		var top = Math.max(rect1.top, rect2.top);
		var bottom = Math.min(rect1.bottom, rect2.bottom);
		var left = Math.max(rect1.left, rect2.left);
		var right = Math.min(rect1.right, rect2.right);
		var width = right - left;
		var height = bottom - top;

		return (width >= 0 && height >= 0) && {
			top: top,
			bottom: bottom,
			left: left,
			right: right,
			width: width,
			height: height
		};
	}


	/**
	 * Shims the native getBoundingClientRect for compatibility with older IE.
	 * @param {Element} el The element whose bounding rect to get.
	 * @return {Object} The (possibly shimmed) rect of the element.
	 */
	function getBoundingClientRect(el) {
		var rect;

		try {
			rect = el.getBoundingClientRect();
		} catch (err) {
			// Ignore Windows 7 IE11 "Unspecified error"
			// https://github.com/w3c/IntersectionObserver/pull/205
		}

		if (!rect) return getEmptyRect();

		// Older IE
		if (!(rect.width && rect.height)) {
			rect = {
				top: rect.top,
				right: rect.right,
				bottom: rect.bottom,
				left: rect.left,
				width: rect.right - rect.left,
				height: rect.bottom - rect.top
			};
		}
		return rect;
	}


	/**
	 * Returns an empty rect object. An empty rect is returned when an element
	 * is not in the DOM.
	 * @return {Object} The empty rect.
	 */
	function getEmptyRect() {
		return {
			top: 0,
			bottom: 0,
			left: 0,
			right: 0,
			width: 0,
			height: 0
		};
	}

	/**
	 * Checks to see if a parent element contains a child elemnt (including inside
	 * shadow DOM).
	 * @param {Node} parent The parent element.
	 * @param {Node} child The child element.
	 * @return {boolean} True if the parent node contains the child node.
	 */
	function containsDeep(parent, child) {
		var node = child;
		while (node) {
			if (node == parent) return true;

			node = getParentNode(node);
		}
		return false;
	}


	/**
	 * Gets the parent node of an element or its host element if the parent node
	 * is a shadow root.
	 * @param {Node} node The node whose parent to get.
	 * @return {Node|null} The parent node or null if no parent exists.
	 */
	function getParentNode(node) {
		var parent = node.parentNode;

		if (parent && parent.nodeType == 11 && parent.host) {
			// If the parent is a shadow root, return the host element.
			return parent.host;
		}
		return parent;
	}


// Exposes the constructors globally.
	window.IntersectionObserver = IntersectionObserver;
	window.IntersectionObserverEntry = IntersectionObserverEntry;

}(window, document));


/**
 * Element.closest polyfill
 */
(function() {
	if (!Element.prototype.closest)
	{
		/**
		 * @param {string} selector
		 * @return {HTMLElement|Element|Node}
		 */
		Element.prototype.closest = function(selector) {
			var node = this;

			while (node)
			{
				if (node.matches(selector))
				{
					return node;
				}

				node = node.parentElement;
			}

			return null;
		};
	}

})();


/**
 * CustomEvent polyfill
 */
(function ()
{

	if (typeof window.CustomEvent === "function") return false;

	function CustomEvent(event, params)
	{
		params = params || {bubbles: false, cancelable: false, detail: undefined};
		var evt = document.createEvent('CustomEvent');
		evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
		return evt;
	}

	CustomEvent.prototype = window.Event.prototype;

	window.CustomEvent = CustomEvent;
})();

/*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
(function(w){
	"use strict";

	if (!w.loadCSS)
	{
		w.loadCSS = (function() {});
	}

	var rp = loadCSS.relpreload = {};

	rp.support = (function() {
		var ret;
		try
		{
			ret = w.document.createElement("link").relList.supports("preload");
		}
		catch (e)
		{
			ret = false;
		}
		return function()
		{
			return ret;
		};
	})();


	rp.bindMediaToggle = function(link)
	{
		var finalMedia = link.media || "all";

		function enableStylesheet()
		{
			link.media = finalMedia;
		}

		if (link.addEventListener)
		{
			link.addEventListener("load", enableStylesheet);
		}
		else if (link.attachEvent)
		{
			link.attachEvent("onload", enableStylesheet);
		}

		setTimeout(function() {
			link.rel = "stylesheet";
			link.media = "only x";
		});

		setTimeout(enableStylesheet, 3000);
	};

	rp.poly = function()
	{
		if (rp.support())
		{
			return;
		}

		var links = w.document.getElementsByTagName("link");

		for (var i = 0; i < links.length; i++)
		{
			var link = links[ i ];
			if (link.rel === "preload" && link.getAttribute("as") === "style" && !link.getAttribute("data-loadcss"))
			{
				link.setAttribute("data-loadcss", true);
				rp.bindMediaToggle(link);
			}
		}
	};

	if (!rp.support())
	{
		rp.poly();

		var run = w.setInterval(rp.poly, 500);

		if (w.addEventListener)
		{
			w.addEventListener("load", function() {
				rp.poly();
				w.clearInterval(run);
			});
		}
		else if (w.attachEvent)
		{
			w.attachEvent("onload", function() {
				rp.poly();
				w.clearInterval(run);
			});
		}
	}

	w.loadCSS = loadCSS;
})(window);