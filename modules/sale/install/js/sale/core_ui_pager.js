BX.ui.scrollablePager = function(opts, nf){

	this.parentConstruct(BX.ui.scrollablePager, opts);

	BX.merge(this, {
		opts: { // default options
			areaHeight:					100, // the default height for top and bottom areas
			eventTimeout: 				100, // scroll event throttle timeout
			pageRenderer:				false,

			setTopReachedOnPage:		false,
			setBottomReachedOnPage:		false
		},
		vars: { // significant variables
			pages:				0, // number of pages already loaded
			boundsReached:		{top: false, bottom: false}, // flags for indicate that bounds were reached already
			prevScrollTop:		false, // container.scrollTop got on the last scroll event
			scrollEventLock:	false, // when true, no scroll events handled
			pageRange:			false,

			renderer:			false
		},
		sys: {
			code: 'pager'
		}
	});

	//this.disableInFuncStack('init', BX.some.parent);
	this.handleInitStack(nf, BX.ui.scrollablePager, opts);
};
BX.extend(BX.ui.scrollablePager, BX.ui.widget);

// the following functions can be overrided with inheritance
BX.merge(BX.ui.scrollablePager.prototype, {

	// member of stack of initializers, must be defined even if do nothing
	init: function(){

		this.ctrls.pane = this.getControl('pane');
		this.vars.scrollEventDispatcher = BX.throttle(this.checkScrollState, this.opts.eventTimeout, this);

		if(this.opts.pageRenderer !== false)
			this.vars.renderer = this.opts.pageRenderer;
		else
			this.vars.renderer = new BX.ui.scrollablePager.renderers.native();

		this.vars.renderer.init({
			scope: this.ctrls.scope,
			pane: this.ctrls.pane,
			parent: this
		});

		this.pushFuncStack('buildUpDOM', BX.ui.scrollablePager);
		this.pushFuncStack('bindEvents', BX.ui.scrollablePager);
	},

	buildUpDOM: function(){

		var sc = this.ctrls;

		// add areas to the top and the bottom of the pane

		var nodes = null;

		if(typeof this.template('pager-area') != 'undefined')
			nodes = this.createNodesByTemplate('pager-area', {}, true);

		if(nodes == null || !BX.type.isDomNode(nodes[0]))
			nodes = BX.create('div', {style: {'height': parseInt(this.opts.areaHeight)+'px'}});
		else
			nodes = nodes[0];

		sc.areas = {
			top: {node: nodes, height: 0},
			bottom: {node: BX.clone(nodes), height: 0}
		};
		sc.pool = BX.create('div');

		BX.append(sc.areas.top.node, sc.pane);
		BX.append(sc.pool, sc.pane);
		BX.append(sc.areas.bottom.node, sc.pane);

		this.vars.renderer.update();
	},

	bindEvents: function(){

		BX.addCustomEvent(this.vars.renderer, 'bx-ui-pagerenderer-scroll-changed', BX.proxy(function(){
			this.dispatchScrollEvents();
		}, this));
		this.vars.renderer.bindScrollEvents();
	},

	////////// PUBLIC: free to use outside

	////////// scrolling

	scrollTo: function(height, ignoreEvents, sign){

		var sv = this.vars;

		if(ignoreEvents)
			sv.scrollEventLock = true;

		sv.renderer.setScrollTop(height, sign);

		if(ignoreEvents)
			sv.scrollEventLock = false;

		return true;
	},

	scrollToNode: function(node){
		// not implemented
	},

	// to dispatch scroll events manually
	dispatchScrollEvents: function(){

		if(this.vars.scrollEventLock)
			return false;

		this.vars.scrollEventDispatcher();
	},

	// to disable scroll events
	lockScrollEvents: function(){
		this.vars.scrollEventLock = true;
	},

	// to enable scroll events back
	unLockScrollEvents: function(){
		this.vars.scrollEventLock = false;
	},

	////////// paging

	prependPage: function(data){
		this.addPage(data, this.vars.pageRange == false ? 0 : this.vars.pageRange[0] - 1);
	},

	appendPage: function(data){
		this.addPage(data, this.vars.pageRange == false ? 0 : this.vars.pageRange[1] + 1);
	},

	getFreePageNumber: function(bound){

		if(this.vars.pageRange == false)
			return 0;

		if(bound == 0)
			return this.vars.pageRange[0] - 1;

		if(bound == 1)
			return this.vars.pageRange[1] + 1;

		return false;
	},

	getPageCount: function(){
		return this.vars.pages;
	},

	// all content already shown at the top
	setTopReached: function(way){

		if(typeof way == 'undefined')
			way = true;

		this.manageBounds(way, true);
	},

	// all content already shown at the bottom
	setBottomReached: function(way){

		if(typeof way == 'undefined')
			way = true;

		this.manageBounds(way, false);
	},

	//////////cleaning

	cleanUp: function(){

		this.ctrls.pool.innerHTML = '';

		this.setTopReached(false);
		this.setBottomReached(false);

		this.vars.pages = 0;
		this.vars.pageRange = false;

		this.vars.renderer.update();
	},

	// lately should appear as an item in remove stack
	remove: function(){
		// drop scope
		if(BX.type.isDomNode(this.ctrls.scope))
			this.ctrls.scope.innerHTML = '';

		// ubind custom events
		BX.unbindAll(this);

		this.vars.renderer.remove();
	},

	////////// PRIVATE: forbidden to use outside (for compatibility reasons)

	addPage: function(data, pageNum){

		var sv = this.vars,
			so = this.opts,
			wrapper = this.getPageWrapper();

		var st = this.ctrls.scope.scrollTop;

		// check range integrity here
		if(sv.pageRange != false){
			if(pageNum != sv.pageRange[1] + 1 && pageNum != sv.pageRange[0] - 1)
				throw new Error('Not allowed to break page range integrity');
		}

		// manage top bound here based on pageNum
		if(so.setTopReachedOnPage !== false && pageNum == so.setTopReachedOnPage)
			this.setTopReached();
		if(so.setBottomReachedOnPage !== false && pageNum == so.setBottomReachedOnPage)
			this.setBottomReached();

		var actAppend = sv.pageRange == false || pageNum > sv.pageRange[1];

		if(BX.type.isString(data))
			wrapper.innerHTML = data;
		else if(BX.type.isDomNode(data))
			BX.append(data, wrapper);
		else if('length' in data && data.length > 0){
			for(var k in data)
			{
				if(data.hasOwnProperty(k))
					if(BX.type.isDomNode(data[k]))
						BX.append(data[k], wrapper);
			}
		}else
			return false; // smth strange passed

		var scrollBefore = this.vars.renderer.getScrollTop();

		BX[actAppend ? 'append' : 'prepend'](wrapper, this.ctrls.pool);

		if(!sv.boundsReached.top && sv.pages == 0){ // scroll to the first page in the pager
			this.scrollTo(this.ctrls.areas.top.node.offsetHeight, true);
		}else{
			if(actAppend){ // on append
				this.scrollTo(scrollBefore, true); // special for ie
			}else // on prepend
				this.scrollTo(wrapper.offsetHeight, true, +1);
		}

		this.vars.renderer.update();

		// modify range
		if(sv.pageRange == false){

			sv.pageRange = [pageNum, pageNum];

		}else{

			if(actAppend)
				sv.pageRange[1]++
			else
				sv.pageRange[0]--;
		}

		sv.pages++;

		this.dispatchScrollEvents();
	},

	checkScrollState: function(){

		var sv = this.vars;

		var scopeScroll = sv.renderer.getScrollTop();

		if(scopeScroll != 0 && sv.prevScrollTop == scopeScroll)
			return false;

		if(this.checkScrolledToTop(scopeScroll) && !sv.boundsReached.top)
			this.fireEvent('scroll-to-top');

		if(this.checkScrolledToBottom(scopeScroll) && !sv.boundsReached.bottom)
			this.fireEvent('scroll-to-bottom');

		sv.prevScrollTop = scopeScroll;
	},

	getPageWrapper: function(){
		return BX.create('div', {props: {className: 'bx-ui-'+this.sys.code+'-page-wrapper'}});
	},

	topOn: function(){
		this.manageBounds(false, true);
	},

	topOff: function(){
		this.manageBounds(true, true);
	},

	// bounds are those two small areas at the top and the bottom
	manageBounds: function(way, isTop){
		way = !!way;
		isTop = !!isTop;

		var sv = this.vars,
			sc = this.ctrls;

		if(isTop){ // must affect scrollTop
			var scroll = this.vars.renderer.getScrollTop();

			var flip = (!sv.boundsReached.top && way)/*shown, need to be hidden*/ || (sv.boundsReached.top && !way)/*hidden, need to be shown*/;

			if(flip){

				var hidden = BX.style(sc.areas.top.node, 'display') == 'none'; // bad solution

				var areaTopH = 0;
				if(hidden){//sv.boundsReached.top){ // hidden
					BX.show(sc.areas.top.node);
					areaTopH = sc.areas.top.node.offsetHeight;
				}else{ // shown
					areaTopH = sc.areas.top.node.offsetHeight;
					BX.hide(sc.areas.top.node);
				}

				this.scrollTo(areaTopH, true, way ? -1 : +1);
			}
		}else
			BX[way ? 'hide' : 'show'](this.ctrls.areas.bottom.node);

		this.vars.boundsReached[isTop ? 'top' : 'bottom'] = way;

		this.vars.renderer.update();
	},

	checkFreeBottomSpace: function(){
		return this.vars.renderer.getScrollTop() <= this.vars.renderer.getClientHeight();
	},

	checkScrolledToTop: function(scopeScroll){
		return scopeScroll <= this.ctrls.areas.top.node.offsetHeight;
	},

	checkScrolledToBottom: function(scopeScroll){
		var sc = this.ctrls;
		return scopeScroll >= this.vars.renderer.getScrollHeight() - this.vars.renderer.getClientHeight() - sc.areas.bottom.node.offsetHeight;
	}

});

// renderer based on native controls
// it takes div being scrolled as SCOPE
// and scrolled contens of SCOPE as PANE
BX.ui.scrollablePager.renderers = {

	native: function(){

		this.opts = {};
		this.wheelLock = false;

		this.init = function(opts){
			this.opts = opts;
		};
		this.remove = function(){

			BX.unbind(this.opts.scope, 'scroll', this.fireEvent);
			BX.unbind(this.opts.scope, 'mousewheel', this.fireEvent);
			BX.unbind(this.opts.pane, 'touchstart', this.fireEvent);
			BX.unbind(window, 'resize', this.fireEvent);

			this.opts = null;
		};

		this.update = function(){};

		this.setScrollTop = function(height, sign){
			if(typeof height == 'undefined')
				return false;

			height = parseInt(height);
			if(height.toString() == 'NaN')
				return false;

			if(sign != 1 && sign != -1)
				this.opts.scope.scrollTop = height;
			else
				this.opts.scope.scrollTop += sign*height;
		};

		this.getScrollTop = function(){
			return this.opts.scope.scrollTop;
		};

		this.getScrollHeight = function(){
			return this.opts.scope.scrollHeight;
		};

		this.getClientHeight = function(){
			return this.opts.scope.clientHeight;
		};

		this.bindScrollEvents = function(){

			var ctx = this;

			BX.bind(this.opts.scope, 'mousewheel', function(e){

				var wData = BX.getWheelData(e);
				var jam = false;

				if(wData > 0 && ctx.getScrollTop() == 0) // move up
					jam = true;

				if(wData < 0 && (ctx.getScrollTop() >= ctx.getScrollHeight() - ctx.getClientHeight())) // move down
					jam = true;

				if(jam){
					BX.PreventDefault(e);
					BX.eventCancelBubble(e);
					return false;
				}
			});

			this.fireEvent = function(e){
				BX.onCustomEvent(ctx, 'bx-ui-pagerenderer-scroll-changed', []);
			};

			BX.bind(this.opts.scope, 'scroll', this.fireEvent);
			BX.bind(this.opts.pane, 'touchstart', this.fireEvent);
			BX.bind(window, 'resize', this.fireEvent);
		};

		return this;
	}

}