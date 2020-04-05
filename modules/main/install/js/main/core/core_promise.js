;(function(window)
{
	/****************** ATTENTION *******************************
	 * Please do not use Bitrix CoreJS in this class.
	 * This class can be called on page without Bitrix Framework
	*************************************************************/

	if (!window.BX)
	{
		window.BX = {};
	}

	var BX = window.BX;

	BX.Promise = function(fn, ctx) // fn is future-reserved
	{
		this.state = null;
		this.value = null;
		this.reason = null;
		this.next = null;
		this.ctx = ctx || this;

		this.onFulfilled = [];
		this.onRejected = [];
	};
	BX.Promise.prototype.fulfill = function(value)
	{
		this.checkState();

		this.value = value;
		this.state = true;
		this.execute();
	};
	BX.Promise.prototype.reject = function(reason)
	{
		this.checkState();

		this.reason = reason;
		this.state = false;
		this.execute();
	};
	BX.Promise.prototype.then = function(onFulfilled, onRejected)
	{
		if(typeof (onFulfilled) == "function" || onFulfilled instanceof Function)
		{
			this.onFulfilled.push(onFulfilled);
		}
		if(typeof (onRejected) == "function" || onRejected instanceof Function)
		{
			this.onRejected.push(onRejected);
		}

		if(this.next === null)
		{
			this.next = new BX.Promise(null, this.ctx);
		}

		if(this.state !== null) // if promise was already resolved, execute immediately
		{
			this.execute();
		}

		return this.next;
	};

	BX.Promise.prototype.catch = function(onRejected)
	{
		if(typeof (onRejected) == "function" || onRejected instanceof Function)
		{
			this.onRejected.push(onRejected);
		}

		if(this.next === null)
		{
			this.next = new BX.Promise(null, this.ctx);
		}

		if(this.state !== null) // if promise was already resolved, execute immediately
		{
			this.execute();
		}

		return this.next;
	};

	BX.Promise.prototype.setAutoResolve = function(way, ms)
	{
		this.timer = setTimeout(function(){
			if(this.state === null)
			{
				this[way ? 'fulfill' : 'reject']();
			}
		}.bind(this), ms || 15);
	};
	BX.Promise.prototype.cancelAutoResolve = function()
	{
		clearTimeout(this.timer);
	};
	/**
	 * Resolve function. This function allows promise chaining, like ..then().then()...
	 * Typical usage:
	 *
	 * var p = new Promise();
	 *
	 * p.then(function(value){
	 *  return someValue; // next promise in the chain will be fulfilled with someValue
	 * }).then(function(value){
	 *
	 *  var p1 = new Promise();
	 *  *** some async code here, that eventually resolves p1 ***
	 *
	 *  return p1; // chain will resume when p1 resolved (fulfilled or rejected)
	 * }).then(function(value){
	 *
	 *  // you can also do
	 *  var e = new Error();
	 *  throw e;
	 *  // it will cause next promise to be rejected with e
	 *
	 *  return someOtherValue;
	 * }).then(function(value){
	 *  ...
	 * }, function(reason){
	 *  // promise was rejected with reason
	 * })...;
	 *
	 * p.fulfill('let`s start this chain');
	 *
	 * @param x
	 */
	BX.Promise.prototype.resolve = function(x)
	{
		var this_ = this;

		if(this === x)
		{
			this.reject(new TypeError('Promise cannot fulfill or reject itself')); // avoid recursion
		}
		// allow "pausing" promise chaining until promise x is fulfilled or rejected
		else if(x && x.toString() === "[object BX.Promise]")
		{
			x.then(function(value){
				this_.fulfill(value);
			}, function(reason){
				this_.reject(reason);
			});
		}
		else // auto-fulfill this promise
		{
			this.fulfill(x);
		}
	};

	BX.Promise.prototype.toString = function()
	{
		return "[object BX.Promise]";
	};

	BX.Promise.prototype.execute = function()
	{
		if(this.state === null)
		{
			//then() must not be called before BX.Promise resolve() happens
			return;
		}

		var value = undefined;
		var reason = undefined;
		var x = undefined;
		var k;
		if(this.state === true) // promise was fulfill()-ed
		{
			if(this.onFulfilled.length)
			{
				try
				{
					for(k = 0; k < this.onFulfilled.length; k++)
					{
						x = this.onFulfilled[k].apply(this.ctx, [this.value]);
						if(typeof x != 'undefined')
						{
							value = x;
						}
					}
				}
				catch(e)
				{
					if('console' in window)
					{
						console.dir(e);
					}

					if (typeof BX.debug !== 'undefined')
					{
						BX.debug(e);
					}

					reason = e; // reject next
				}
			}
			else
			{
				value = this.value; // resolve next
			}
		}
		else if(this.state === false) // promise was reject()-ed
		{
			if(this.onRejected.length)
			{
				try
				{
					for(k = 0; k < this.onRejected.length; k++)
					{
						x = this.onRejected[k].apply(this.ctx, [this.reason]);
						if(typeof x != 'undefined')
						{
							value = x;
						}
					}
				}
				catch(e)
				{
					if('console' in window)
					{
						console.dir(e);
					}

					if (typeof BX.debug !== 'undefined')
					{
						BX.debug(e);
					}

					reason = e; // reject next
				}
			}
			else
			{
				reason = this.reason; // reject next
			}
		}

		if(this.next !== null)
		{
			if(typeof reason != 'undefined')
			{
				this.next.reject(reason);
			}
			else if(typeof value != 'undefined')
			{
				this.next.resolve(value);
			}
		}
	};
	BX.Promise.prototype.checkState = function()
	{
		if(this.state !== null)
		{
			throw new Error('You can not do fulfill() or reject() multiple times');
		}
	};
})(window);

