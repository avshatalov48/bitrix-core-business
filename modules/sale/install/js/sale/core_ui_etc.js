(function(){

	if(typeof BX.ui != 'object')
		BX.ui = {};

	//////////////////////////////
	// "delayed" loader
	//////////////////////////////

	BX.ui.loader = function(opts, nf){

		this.parentConstruct(BX.ui.loader, opts);

		BX.merge(this, {
			opts: { // default options
				timeout:	100
			},
			sys: {
				code: 'loader'
			}
		});

		this.handleInitStack(nf, BX.ui.loader, opts);
	};
	BX.extend(BX.ui.loader, BX.ui.widget);

	// the following functions can be overrided with inheritance
	BX.merge(BX.ui.loader.prototype, {

		// member of stack of initializers, must be defined even if do nothing
		init: function(){},

		show: function(){

			var ctx = this;
			var sv = this.vars;

			sv.timer = setTimeout(function(){
				ctx.fireEvent('toggle', [true]);
				sv.timer = null;
			}, this.opts.timeout);
		},

		hide: function(){

			clearTimeout(this.vars.timer);
			if(this.vars.timer == null)
				this.fireEvent('toggle', [false]);
		}

	});

	//////////////////////////////
	// native scrollable div
	// DEPRECATED :) Use BX.ui.scrollablePager instead
	//////////////////////////////

	BX.ui.scrollPaneNative = function(opts, nf){

		this.parentConstruct(BX.ui.scrollPaneNative, opts);

		BX.merge(this, {
			opts: { // default options
				eventTimeout:	150,
				scrollError:	30
			},
			vars: {
				prevScrollTop:	0,
				atBottom:		false
			},
			sys: {
				code: 'scrollpanenative'
			}
		});

		this.handleInitStack(nf, BX.ui.scrollPaneNative, opts);
	};
	BX.extend(BX.ui.scrollPaneNative, BX.ui.widget);

	// the following functions can be overrided with inheritance
	BX.merge(BX.ui.scrollPaneNative.prototype, {

		// member of stack of initializers, must be defined even if do nothing
		init: function(){

			this.ctrls.container = this.getControl('container');

			//this.informContentChanged(); // just set some initial values

			this.pushFuncStack('bindEvents', BX.ui.scrollPaneNative);
		},

		bindEvents: function(){

			var ctx = this,
				sc = this.ctrls;

			BX.bind(sc.container, 'scroll', function(){
				ctx.checkScrollState();
			});
			BX.bind(sc.container, 'mousewheel', function(e){

				var wData = BX.getWheelData(e);
				var jam = false;

				var scrollTop = ctx.ctrls.container.scrollTop;
				var scrollHeight = ctx.ctrls.container.scrollHeight;
				var clientHeight = ctx.ctrls.container.clientHeight;

				if(wData > 0 && scrollTop == 0) // move up
					jam = true;

				if(wData < 0 && (scrollTop >= scrollHeight - clientHeight)) // move down
					jam = true;

				if(jam){
					BX.PreventDefault(e);
					BX.eventCancelBubble(e);
					return false;
				}

			});
			BX.bind(window, 'resize', function(){
				ctx.checkScrollState();
			});

			BX(function(){
				ctx.checkScrollState();
			});
		},

		informContentChanged: function(){
			this.vars.prevScrollTop = this.ctrls.container.scrollTop;
			this.vars.atBottom = false;
			this.checkScrollState();
		},

		dropScrollTop: function(){
			this.ctrls.container.scrollTop = 0;
			this.checkScrollState();
		},

		scrollTo: function(pos){
			// scroll only to vertical (y) is implemented

			var scrollTop = false;

			if(typeof pos.y == 'number')
				scrollTop = pos.y;
			else if(typeof pos.dy == 'number')
				scrollTop = this.ctrls.container.scrollTop + pos.dy;

			if(scrollTop !== false){
				this.ctrls.container.scrollTop = scrollTop;
			}
		},

		checkFreeBottomSpace: function(){

			var sc = this.ctrls;

			return sc.container.scrollHeight <= sc.container.clientHeight;
		},

		checkScrolledToEnd: function(){

			var sc = this.ctrls,
				sv = this.vars,
				so = this.opts;

			var st = sc.container.scrollTop;

			if(sv.prevScrollTop == st)
				return;

			var atBottom = sc.container.scrollTop >= sc.container.scrollHeight - sc.container.clientHeight - so.scrollError;

			if(atBottom == sv.atBottom) // scrolled in the range of so.scrollError, not interesting
				return;

			sv.atBottom = atBottom;

			return sv.atBottom;
		},

		checkScrollState: function(){

			var ctx = this;
			if(!BX.type.isFunction(this.vars.checkFn)){
				this.vars.checkFn = BX.throttle(function(){

					if(ctx.checkFreeBottomSpace())
						ctx.fireEvent('has-free-space');

					if(ctx.checkScrolledToEnd())
						ctx.fireEvent('scroll-to-end');

				}, ctx.opts.eventTimeout, ctx);
			}

			this.vars.checkFn();
		}

	});

	//////////////////////////////
	// widget with networking
	//////////////////////////////

	BX.ui.networkIOWidget = function(opts, nf){

		this.parentConstruct(BX.ui.networkIOWidget, opts);

		BX.merge(this, {
			opts: { // default options
				source:						'/somewhere.php',
				pageSize:					5, // amount of variants to show
				paginatedRequest:			true // if true, parameters for server-side paginator will be sent in the request
			},
			vars: { // significant variables
				lastPage: 0,
				loader: {show: BX.DoNothing, hide: BX.DoNothing}
			},
			ctrls: { // links to controls
			},
			sys: {
				code: 'network-io-widget'
			}
		});

		this.handleInitStack(nf, BX.ui.networkIOWidget, opts);
	};
	BX.extend(BX.ui.networkIOWidget, BX.ui.widget);

	// the following functions can be overrided with inheritance
	BX.merge(BX.ui.networkIOWidget.prototype, {

		// member of stack of initializers, must be defined even if do nothing
		init: function(){
		},

		downloadBundle: function(parameters){

			var so = this.opts,
				sv = this.vars,
				sc = this.ctrls,
				ctx = this;

			sv.loader.show(parameters.options);

			BX.ajax({

				url: so.source,
				method: 'post',
				dataType: 'json',
				async: true,
				processData: true,
				emulateOnload: true,
				start: true,
				data: BX.merge(ctx.refineRequest(parameters.request, parameters.options), ctx.getNavParams(parameters.options)),
				//cache: true,
				onsuccess: function(result){

					//try{

					sv.loader.hide(parameters.options);
					if(result.result){
						result.data = ctx.refineResponce(result.data, parameters.request, parameters.options);

						if(typeof result.data == 'undefined')
							result.data = [];

						if(BX.type.isFunction(parameters.callbacks.onLoad))
							parameters.callbacks.onLoad.apply(ctx, [result.data]);

					}else
						ctx.showError({errors: result.errors, type: 'server-logic', options: parameters.options});

					if(BX.type.isFunction(parameters.callbacks.onComplete))
						parameters.callbacks.onComplete.call(ctx);

					//}catch(e){console.dir(e);}

				},
				onfailure: function(type, e){

					sv.loader.hide(parameters.options);

					ctx.showError({errors: [e.message], type: type, options: parameters.options, exception: e});

					if(BX.type.isFunction(parameters.callbacks.onComplete))
						parameters.callbacks.onComplete.call(ctx);

					if(BX.type.isFunction(parameters.callbacks.onError))
						parameters.callbacks.onError.apply(ctx, [type, e]);
				}

			});

		},

		getNavParams: function(options){
			return this.opts.paginatedRequest ? {
				PAGE_SIZE: this.opts.pageSize,
				PAGE: this.vars.lastPage
			} : {};
		},

		// show internal or ajax call errors, but not logic errors (like "not found or smth")
		showError: function(parameters){
			BX.debug(parameters);
		},

		// this function is called just before request send, i.e. it`s look like a 'query proxy'
		refineRequest: function(query, options){
			return query;
		},

		// responce 'proxy-back'
		refineResponce: function(responce, request, options){
			return responce;
		}

	});

	// additionals

	BX.deferred = function(options){

		this.timer = null;
		this.actions = {
			done: [],
			fail: []
		};
		this.result = null;

		this.done = function(fn){
			if(BX.type.isFunction(fn))
				this.actions.done.push(fn);
		};
		this.fail = function(fn){
			if(BX.type.isFunction(fn))
				this.actions.fail.push(fn);
		};

		this.resolve = function(){
			if(this.result === false) // already failed, too late
				return;

			this.result = true;
			this.callActions('done', arguments);
		};
		this.reject = function(){
			if(this.result === true) // already completed, too late
				return;

			this.result = false;
			this.callActions('fail', arguments);
		};

		this.startRace = function(timeout, result){
			var ctx = this;
			this.timer = setTimeout(function(){result ? ctx.resolve() : ctx.reject()}, timeout || 300);
		};
		this.stopRace = function(){
			clearTimeout(this.timer);
		};

		this.callActions = function(actType, args)
		{
			for(var k in this.actions[actType])
				if(this.actions[actType].hasOwnProperty(k))
					this.actions[actType][k].apply(this, args);
		};

		return this;
	};

	BX.semaphore = function(fn, ctx, options){

		/*
		options = options || {};
		options.limit = options.limit || 1;
		options.duplicates = options.duplicates || 'drop'; // also could be "pend"
		*/

		var lock = false;

		return function(){

			ctx = ctx || this;

			if(lock)
				return;

			lock = true;

			fn.apply(ctx, arguments);

			lock = false;
		};
	};

	BX.util.wrapSubstring = function(haystack, chunks, wrapTagName, escapeParts){

		if(haystack.length == 0)
			return '';

		if(escapeParts){
			haystack = BX.util.htmlspecialchars(haystack);
			wrapTagName = BX.util.htmlspecialchars(wrapTagName);
		}

		if(chunks.length == 0)
			return haystack;

		var scan = '';
		var search = '';
		var searched = {};
		for(var k in chunks)
		{
			if(!chunks.hasOwnProperty(k))
				continue;

			search = chunks[k].toString().toLowerCase();
			scan = haystack.toLowerCase();

			if(typeof searched[search] != 'undefined')
				continue;

			var i = scan.indexOf(search);

			if(i >= 0){
				var left = haystack.slice(0, i);
				var middle = haystack.slice(i, i + search.length);
				var right = haystack.slice(i + search.length, haystack.length);

				haystack = left + '#A#'+middle+'#B#' + right;
			}

			searched[search] = true;
		}

		return haystack.replace(/#A#/g, '<'+wrapTagName+'>').replace(/#B#/g, '</'+wrapTagName+'>');
	}

	BX.util.getObjectLength = function(obj){

		if(typeof obj != 'object')
			return 0;

		if("length" in obj)
			return obj.length;

		var cnt = 0;
		for(var k in obj){
			if(obj.hasOwnProperty(k))
				cnt++;
		}

		return cnt;
	};

})();