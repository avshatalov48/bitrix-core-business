BX.namespace('BX.ui');


BX.ui.itemTree = function(opts, nf){

	this.parentConstruct(BX.ui.itemTree, opts);

	BX.merge(this, {
		opts: { // default options
			controlCheckboxes: 			true, // when true, do checkbox parent-to-children control (if there are any checkboxes)
			useDynamicLoading: 			false
		},
		vars: { // significant variables
			cache: { // item cache
				ceilings: 				{}, // "nodeId => maximum page number" map
				lastPage: 				{}, // "nodeId => last-page-shown number" map
				loadedFirstTime: 		{} // "nodeId => flag" map which keeps mark of that node was opened once and child nodes were successfully loaded
			}
		},
		ctrls: { // links to controls
		},
		sys: {
			code: 'item-tree'
		}
	});

	this.handleInitStack(nf, BX.ui.itemTree, opts);
};
BX.extend(BX.ui.itemTree, BX.ui.networkIOWidget);

// the following functions can be overrided with inheritance
BX.merge(BX.ui.itemTree.prototype, {

	// member of stack of initializers, must be defined even if do nothing
	init: function(){

		var ctx = this;

		this.vars.loader = {
			show: function(options){
				ctx.setCSSState('loading', options.controls.children);
			},
			hide: function(options){
				ctx.dropCSSState('loading', options.controls.children);
			}
		};

		//this.pushFuncStack('buildUpDOM', BX.ui.itemTree);
		this.pushFuncStack('bindEvents', BX.ui.itemTree);
	},

	//buildUpDOM: function(){
	//},

	bindEvents: function(){

		var sc = this.ctrls,
			ctx = this,
			code = this.sys.code;

		// expand\collapse handling
		BX.bindDelegate(sc.scope, 'click', {className: this.getControlClassName('expander')}, function(){

			var node = BX.findParent(this, {className: ctx.getControlClassName('node')});
			if(BX.type.isElementNode(node)){
				ctx.toggleBundle(BX.data(node, 'node-id'), {
					expander: 	this,
					node: 		node
				});
			}else
				throw new Error('Cannot find node for expander');
		});

		// load more handling
		BX.bindDelegate(sc.scope, 'click', {className: this.getControlClassName('load-more')}, function(){

			var node = BX.findParent(this, {className: ctx.getControlClassName('node')});
			if(BX.type.isElementNode(node)){
				ctx.loadMore(BX.data(node, 'node-id'), {
					loadMore: 	this,
					node: 		node
				});
			}else
				throw new Error('Cannot find node for load-more');
		});

		// checkboxes handling
		BX.bindDelegate(sc.scope, 'click', {className: this.getControlClassName('checkbox')}, function(e){
			ctx.toggleCheckbox(this);
		});

		this.bindEvent('toggle-bundle-before', this.whenExpanderToggle);
	},

	// load more or retry
	loadMore: function(nodeId, controls){

		var ctx = this,
			so = this.opts,
			sv = this.vars;

		controls.children = ctx.getControl('children', true, controls.node);

		this.dropCSSState('error', controls.children);
		this.dropCSSState('can-load-more', controls.children);

		if(so.useDynamicLoading && BX.type.isNotEmptyString(so.source) && this.getCanLoadMore(nodeId)){
			this.downloadItems(nodeId, controls);
		}
	},

	toggleBundle: function(nodeId, controls){

		var ctx = this,
			so = this.opts,
			sv = this.vars,
			code = this.sys.code;

		if(!BX.type.isPlainObject(controls))
			controls = {};

		// must find node
		if(!BX.type.isElementNode(controls.node)){
			controls.node = this.getControl('[data-node-id="'+nodeId+'"]');
			if(!BX.type.isElementNode(controls.node))
				throw new Error('Cannot find node for id '+nodeId);
		}

		// must find expander
		if(!BX.type.isElementNode(controls.expander)){
			controls.expander = this.getControl('expander', false, controls.node);
			if(!BX.type.isElementNode(controls.expander))
				throw new Error('Cannot find expander for node');
		}

		var prevState = false;
		var dataTag = 'bx-ui-'+code+'-state';
		var state = BX.data(controls.expander, dataTag);
		if(typeof state != 'undefined')
			prevState = state;

		BX.data(controls.expander, dataTag, !prevState);

		controls.checkbox = ctx.getControl('checkbox', true, controls.node);
		controls.children = ctx.getControl('children', true, controls.node);

		var eventArgs = [
			!prevState,
			controls
		];

		this.fireEvent('toggle-bundle-before', eventArgs);

		var callback = function(){
			ctx.fireEvent('toggle-bundle-after', eventArgs);
		}

		if(!prevState && so.useDynamicLoading && BX.data(controls.node, 'is-parent') == '1' && BX.type.isNotEmptyString(so.source) && !sv.cache.loadedFirstTime[nodeId] && this.getCanLoadMore(nodeId)){

			this.dropCSSState('error', controls.children);
			this.dropCSSState('can-load-more', controls.children);

			this.downloadItems(nodeId, controls, callback);
		}else
			callback();
	},

	downloadItems: function(nodeId, controls, callback){

		var ctx = this,
			so = this.opts,
			sv = this.vars;

		ctx.downloadBundle({
			request: {ID: nodeId},
			callbacks: {
				onLoad: function(data){

					// if items are empty, we set ceiling
					if(data.items.length == 0)
						sv.cache.ceilings[nodeId] = sv.cache.lastPage[nodeId];
					else{

						// if there is "total" key, we calculate ceiling
						if(typeof data.total == 'number' && so.pageSize > 0){
							sv.cache.ceilings[nodeId] = Math.ceil(data.total / so.pageSize);

							//console.dir('item count: '+data.total+', page size: '+so.pageSize);
							//console.dir('ceiling set to '+sv.cache.ceilings[nodeId]);
						}

						var pool = ctx.getControl('item-pool', false, controls.children);
						for(var k in data.items)
						{
							if(data.items.hasOwnProperty(k))
							{
								var newNode = ctx.whenRenderVariant(data.items[k])[0];
								BX.append(newNode, pool);
							}
						}

						// increase page
						sv.cache.lastPage[nodeId]++;

						if(ctx.getCanLoadMore(nodeId))
							this.setCSSState('can-load-more', controls.children);
					}

					sv.cache.loadedFirstTime[nodeId] = true; // set flag to avolid duplicate load on node close\open

					if(BX.type.isFunction(callback))
						callback();
				}
			},
			options: {controls: controls, nodeId: nodeId}
		});

	},

	toggleCheckbox: function(control){

		var so = this.opts,
			ctx = this,
			code = this.sys.code;

		if(!so.controlCheckboxes)
			return;

		if(!('checked' in control)) // not a checkbox
			return;

		var parent = BX.findParent(control, {className: 'bx-ui-'+code+'-node'});

		if(typeof parent == 'undefined')
			throw new Error('Cannot find parent node for checkbox');

		var way = control.checked;

		// turn on\off all children
		var cbx = parent.querySelectorAll('input[type="checkbox"]');
		if(cbx != null){
			for(var k = 0; k < cbx.length; k++)
				cbx[k].checked = way;
		}
	},

	getNavParams: function(options){

		var cache = this.vars.cache;

		if(typeof cache.lastPage[options.nodeId] == 'undefined')
			cache.lastPage[options.nodeId] = 0;

		return this.opts.paginatedRequest ? {
			PAGE_SIZE: 	this.opts.pageSize,
			PAGE: 		cache.lastPage[options.nodeId]
		} : {};
	},

	showError: function(parameters){

		this.setCSSState('error', parameters.options.controls.children);
		this.getControl('error-desc', false, parameters.options.controls.node).innerHTML = BX.util.htmlspecialchars(parameters.errors.join(', '));
	},

	manageCeiling: function(key, pageNum){

		var sv = this.vars;

		if(typeof pageNum == 'undefined'){
			if(typeof sv.cache.ceilings[key] == 'undefined')
				return -1;

			return sv.cache.ceilings[key];
		}

		sv.cache.ceilings[key] = pageNum;
	},

	getCanLoadMore: function(key){

		var sv = this.vars;

		if(typeof sv.cache.ceilings[key] == 'undefined')
			return true;

		return sv.cache.ceilings[key] >= 0 && sv.cache.lastPage[key] < sv.cache.ceilings[key];
	},

	// ui

	whenRenderVariant: function(itemData){
		return this.createNodesByTemplate('node', itemData, true);
	},

	whenExpanderToggle: function(way, controls){
		BX[way ? 'show' : 'hide'](controls.children);
	}

});