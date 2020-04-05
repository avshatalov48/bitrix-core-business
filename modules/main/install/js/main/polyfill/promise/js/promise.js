;(function(window) {
	'use strict';

	if (typeof window.Promise === 'undefined' ||
		window.Promise.toString().indexOf('[native code]') === -1)
	{
		var PROMISE_STATUS = '[[PromiseStatus]]';
		var PROMISE_VALUE = '[[PromiseValue]]';
		var STATUS_PENDING = 'pending';
		var STATUS_INTERNAL_PENDING = 'internal pending';
		var STATUS_RESOLVED = 'resolved';
		var STATUS_REJECTED = 'rejected';


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
					var callback = promise[PROMISE_STATUS] === STATUS_RESOLVED ?
						deferred.onFulfilled : deferred.onRejected;

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
				throw new TypeError('A promise cannot be resolved with it promise.');
			}

			try {
				if (value && (typeof value === 'object' || typeof value === 'function'))
				{
					if (value instanceof Promise)
					{
						promise[PROMISE_STATUS] = STATUS_INTERNAL_PENDING;
						promise[PROMISE_VALUE] = value;
						finale(promise);
						return;
					}
					else if (typeof value.then === 'function')
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
						console.error('Unhandled Promise Rejection: ' + promise[PROMISE_VALUE]);
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
			this.onFulfilled = typeof onFulfilled === 'function' ? onFulfilled : null;
			this.onRejected = typeof onRejected === 'function' ? onRejected : null;
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
		Promise.prototype['catch'] = function(onRejected) {
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
							if (val && (typeof val === 'object' || typeof val === 'function'))
							{
								if (typeof val.then === 'function')
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
		 * If the value is a thenable (i.e. has a 'then' method),
		 * the returned promise will 'follow' that thenable, adopting its eventual state;
		 * if the value was a promise, that object becomes the result of the call to Promise.resolve;
		 * otherwise the returned promise will be fulfilled with the value.
		 *
		 * @param {*|Promise} value - Argument to be resolved by this Promise. Can also be a Promise or a thenable to resolve.
		 * @returns {Promise}
		 */
		Promise.resolve = function(value)
		{
			if (value && typeof value === 'object' && value.constructor === Promise)
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