BX.ui.chainedSelectors = function(opts, nf){

	this.parentConstruct(BX.ui.chainedSelectors, opts);

	/*
	events:
		parent:
			*
		BX.ui.chainedSelectors:
			control-change
	*/

	BX.merge(this, {
		opts: { // default options
			source:						'/somewhere.php', // url that will be used to obtain select options from
			paginatedRequest:			false,

			// behaviour
			autoSelectWhenSingle:		true,

			knownBundles:				{}, // tree levels that already known
			selectedItem:				false, // initially selected path in a tree
			initialBundlesIncomplete:	false, // treat knownBundles as incomplete and try to re-get from server at the first suitable moment
			bundlesIncomplete: 			{}, // lists exactly which bundles in knownBundles are incomplete
			rootNodeValue:				0,
			ignoreUnSelectable:			false,

			adapterName:				'combobox',

			messages: {
				nothingFound:	'Sorry, nothing found',
				notSelected:	'-- Not selected',
				error:			'Error occured'
			},

			bindEvents: {
				init: function(){ // after all we do this
					this.setInitialValue();
					this.vars.allowHideErrors = true;
				}
			}
		},
		vars: { // significant variables
			stack: [], // sequence of controls
			cache: { // tree level cache
				links: {}, // relations of parent-children kind
				nodes: {}, // node data
				incomplete: {} // array of indicators of incomplete links (links that should be refreshed and completed before passing to control)
			},
			/*
			keys in each node:
			DISPLAY, VALUE, IS_PARENT, CAN_CHOOSE
			*/
			value: 					false, // currently selected value
			eventLock: 				false,
			allowHideErrors: 		false
		},
		sys: {
			code: 'chainedselectors'
		}
	});

	this.handleInitStack(nf, BX.ui.chainedSelectors, opts);
}
BX.extend(BX.ui.chainedSelectors, BX.ui.networkIOWidget);

// the following functions can be overrided with inheritance
BX.merge(BX.ui.chainedSelectors.prototype, {

	// member of stack of initializers, must be defined even if does nothing
	init: function(){

		var so = this.opts,
			sc = this.ctrls,
			sv = this.vars;

		sc.targetInput = this.getControl('target');
		sc.errorMessage = this.getControl('error', true);

		//sc.loader = new BX.ui.loader({scope: this.getControl('loader'), timeout: 500});

		sc.pool = this.getControl('pool');
		if(typeof sc.pool == 'undefined')
			sc.pool = sc.scope;

		if(typeof so.knownBundles == 'object'){
			this.fillCache(so.knownBundles);

			if(so.initialBundlesIncomplete){
				for(var k in so.knownBundles)
					if(so.knownBundles.hasOwnProperty(k))
						sv.cache.incomplete[k] = true;
			}else if(typeof so.bundlesIncomplete != 'undefined'){

				for(var k in so.knownBundles)
				{
					if(so.knownBundles.hasOwnProperty(k))
						if(typeof so.bundlesIncomplete[k] != 'undefined')
							sv.cache.incomplete[k] = true;
				}
			}
		}

		this.pushFuncStack('buildUpDOM', BX.ui.chainedSelectors);
		this.pushFuncStack('bindEvents', BX.ui.chainedSelectors);
	},

	buildUpDOM: function(){},

	bindEvents: function(){

		var ctx = this,
			so = this.opts,
			sv = this.vars,
			sc = this.ctrls;

		this.bindEvent('control-change', this.controlChangeActions);

		BX.bind(sc.targetInput, 'change', function(){

			if(sv.eventLock)
				return;

			ctx.setValue(this.value);
		});
	},

	////////// PUBLIC: free to use outside

	// todo
	addItems2Cache: function(){
	},

	// todo
	clearCache: function(){
	},

	// todo
	focus: function(){
	},
	
	// todo
	checkDisabled: function(){
	},

	// todo
	disable: function(){
	},

	// todo
	enable: function(){
	},

	setValue: function(value){

		var sv = this.vars;

		// same value
		if(sv.value != false && typeof value != 'undefined' && value == sv.value)
			return;

		if(value == null || value == false || typeof value == 'undefined' || value.toString().length == 0){ // deselect
			this.displayRoute([]);
			this.setValueVariable('');
			this.setTargetValue('');
			this.fireEvent('after-clear-selection');
			return;
		}

		// set
		this.fireEvent('before-set-value', [value]);

		var d = new BX.deferred();
		var ctx = this;

		d.done(BX.proxy(function(route){

			this.displayRoute(route);
			sv.value = value;
			this.setTargetValue(this.checkCanSelectItem(value) ? value : this.getLastValidValue());

		}, this));

		d.fail(function(type){
			if(type == 'notfound'){

				ctx.displayRoute([]);
				ctx.setValueVariable('');
				ctx.setTargetValue('');
				ctx.showError({errors: [ctx.opts.messages.nothingFound], type: 'server-logic', options: {}});
			}
		});

		this.hideError();

		this.getRouteToNode(value, d);
	},

	getValue: function(){
		return this.vars.value === false ? '' : this.vars.value;
	},

	clearSelected: function(){
		this.setValue('');
	},

	getNodeByValue: function(value){
		return this.vars.cache.nodes[value];
	},

	// todo
	setTabIndex: function(index){
	},

	setTargetInputName: function(newName){
		this.ctrls.targetInput.setAttribute('name', newName);
	},

	// todo:
	cancelRequest: function(){
	},

	// specific

	getStackSize: function(){
		return this.vars.stack.length;
	},

	getAdapterAtPosition: function(pos){
		if(pos < 0 || pos >= this.vars.stack.length)
			return null;

		return this.vars.stack[pos].control;
	},

	// low-level, use with caution

	setTargetInputValue: function(value){

		this.vars.eventLock = true;
		this.ctrls.targetInput.value = value;
		BX.fireEvent(this.ctrls.targetInput, 'change');
		this.vars.eventLock = false;
	},

	// todo
	setFakeInputValue: function(display){
	},

	setValueVariable: function(value){
		this.vars.value = value;
	},

	////////// PRIVATE: forbidden to use outside (for compatibility reasons)

	checkCanSelectItem: function(itemId){

		if(this.opts.ignoreUnSelectable)
			return true;

		var nodes = this.vars.cache.nodes;

		if(typeof nodes[itemId] == 'undefined')
			return false;

		if(typeof nodes[itemId].IS_UNCHOOSABLE == 'undefined')
			return true;

		return !nodes[itemId].IS_UNCHOOSABLE;
	},

	controlChangeActions: function(stackIndex, value){

		var ctx = this,
			so = this.opts,
			sv = this.vars,
			sc = this.ctrls;

		this.hideError();

		////////////////

		// todo: replace code below with a single call of ctx.setValue() with no intention to drop entire stack, but just append missing items

		if(value.length == 0){

			ctx.truncateStack(stackIndex);
			sv.value = ctx.getLastValidValue();
			ctx.setTargetValue(sv.value);

		}else{

			var node = sv.cache.nodes[value];

			if(typeof node == 'undefined')
				throw new Error('Selected node not found in the cache');

			// node found

			ctx.truncateStack(stackIndex);

			// links will be downloaded on-demand
			if(typeof sv.cache.links[value] != 'undefined' || node.IS_PARENT)
				ctx.appendControl(value);

			if(ctx.checkCanSelectItem(value)){
				sv.value = value;
				ctx.setTargetValue(value);
			}

			/*
			if(typeof sv.cache.links[value] != 'undefined'){

				// links exist
				ctx.appendControl(value);
				ctx.setNextValidValue(node, value);

			}else{

				if(node.IS_PARENT){

					var d = new BX.deferred();
					d.done(function(){
						ctx.appendControl(value);
						ctx.setNextValidValue(node, value);
					});
					d.fail(function(){
						ctx.showError({errors: [so.messages.nothingFound], type: 'server-logic', options: {}});
					});

					ctx.getBundleForNode(value, d);
				}else
					ctx.setTargetValue(value);

			}
			*/
		}
	},

	// for changing targerInput value, use this function only
	setTargetValue: function(value){
		this.setTargetInputValue(value);
		this.fireEvent('after-select-item', [value]);
	},

	// try to get route from cache, if any
	getRouteToNodeFromCache: function(value){
		var route = [],
			sv = this.vars;

		if(typeof sv.cache.nodes[value] == 'undefined')
			return route;

		var node = sv.cache.nodes[value];
		route.unshift(node.VALUE);

		var i = 0;
		var rootNodeFound = false;
		var limit = BX.util.getObjectLength(sv.cache.nodes);
		while(i < limit){

			var parent = node.PARENT_VALUE;

			if(parent == this.opts.rootNodeValue)
				rootNodeFound = true;

			route.unshift(parent);

			if(typeof parent == 'undefined' || parent == this.opts.rootNodeValue/*'0'*/ || parent == '0' /*also allowed as root node*/){
				break;
			}else{

				if(typeof sv.cache.nodes[parent] == 'undefined')
					throw new Error('Tree integrity compromised');

				node = sv.cache.nodes[parent];
			}

			i++;
		}

		return route;
	},

	setInitialValue: function(){

		var initalValue = false;
		if(this.opts.selectedItem !== false)
			initalValue = this.opts.selectedItem;
		else if(this.ctrls.targetInput.value.length > 0)
			initalValue = this.ctrls.targetInput.value;

		this.setValue(initalValue);
	},

	// get route for nodeId and resolve deferred with it
	getRouteToNode: function(nodeId, d){
		var sv = this.vars,
			ctx = this;

		if(typeof nodeId != 'undefined' && nodeId !== false && nodeId.toString().length > 0){

			var route = this.getRouteToNodeFromCache(nodeId);

			if(route.length == 0){ // || (sv.cache.nodes[nodeId].IS_PARENT && typeof sv.cache.links[nodeId] == 'undefined')){

				// no way existed or item is parent without children downloaded

				// download route, then try again
				ctx.downloadBundle({
					request: {VALUE: nodeId}, // get only route
					callbacks: {
						onLoad: function(data){

							// mark absent as incomplete, kz we do not know if there are really more items of that level or not
							for(var k in data)
							{
								if(data.hasOwnProperty(k))
									if(typeof sv.cache.links[k] == 'undefined')
										sv.cache.incomplete[k] = true;
							}

							ctx.fillCache(data, true);

							var route = ctx.getRouteToNodeFromCache(nodeId); // trying to re-get

							if(route.length == 0)
								d.reject('notfound');
							else
								d.resolve(route);
						},
						onError: function(){ // this will only trigger on internal error, not server-logic error
							d.reject('internal');
						}
					},
					options: {} // accessible in refineRequest\refineResponce and showError
				});

			}else
				d.resolve(route);
		}else
			d.resolve([]);
	},

	// get bundle for nodeId and resolve deferred with it
	getBundleForNode: function(nodeId, d){
		var sv = this.vars,
			ctx = this;

		if(typeof nodeId != 'undefined' && nodeId !== false && nodeId.toString().length > 0){

			if(typeof this.vars.cache.links[nodeId] == 'undefined' || this.vars.cache.incomplete[nodeId] === true){

				// no way existed or item is parent without children downloaded

				// download bundle, then try again
				ctx.downloadBundle({
					request: {PARENT_VALUE: nodeId}, // get children
					callbacks: {
						onLoad: function(data){

							ctx.fillCache(data);

							if(typeof this.vars.cache.links[nodeId] == 'undefined'){ // still not found
								d.reject();
							}else{
								delete(sv.cache.incomplete[nodeId]);
								d.resolve(this.vars.cache.links[nodeId]);
							}
						},
						onError: function(){
							d.reject();
						}
					},
					options: {} // accessible in refineRequest\refineResponce and showError
				});

			}else
				d.resolve(this.vars.cache.links[nodeId]);
		}else
			d.resolve([]);
	},

	// print route that have been found
	displayRoute: function(route){
		var sv = this.vars;

		this.truncateStack(-1); // drop entire stack

		if(route.length == 0)
			this.appendControl(this.opts.rootNodeValue/*0*/);
		else{
			route = BX.clone(route);

			//route.unshift(this.opts.rootNodeValue/*'0'*/);

			for(var k = 0; k < route.length; k++){
				var nodeId = route[k];

				if(typeof sv.cache.links[nodeId] != 'undefined' || sv.cache.nodes[nodeId].IS_PARENT){
					this.appendControl(nodeId, route[k+1]);

					/*
					// disabling control where is only one option
					if(typeof sv.cache.nodes[nodeId] != 'undefined' && sv.cache.nodes[nodeId].IS_PARENT && sv.cache.links[nodeId].length == 1 && sv.cache.incomplete[nodeId] !== true){

						var control = this.getLastControl().control;

						BX.adjust(control, {
							attrs: {disabled: 'disabled'}
						});
					}
					*/
				}
			}
		}
	},

	appendControl: function(parentNode, selectedNode){

		var params = {
			parent: this,
			messages: this.opts.messages
		};

		if(typeof selectedNode != 'undefined')
			params.selectedItem = selectedNode;

		params.parentNode = parentNode;

		if(typeof this.vars.cache.links[parentNode] != 'undefined'){

			params.knownItems = [];
			for(var k in this.vars.cache.links[parentNode])
				if(this.vars.cache.links[parentNode].hasOwnProperty(k))
					params.knownItems.push(this.vars.cache.nodes[this.vars.cache.links[parentNode][k]]);
		}

		var control = new BX.ui.chainedSelectors.adapters[this.opts.adapterName](params);

		control.setIndex(this.vars.stack.length);
		this.vars.stack.push({control: control});
	},

	setNextValidValue: function(node, value){

		var	sv = this.vars,
			so = this.opts,
			sc = this.ctrls;

		var hasLinks = typeof sv.cache.links[value] != 'undefined';
		var linkCnt = sv.cache.links[value].length;

		if(so.autoSelectWhenSingle && node.IS_PARENT && hasLinks && linkCnt == 1){

			// do one step farther, user is no needed for clicking
			var control = this.getLastControl().control;
			/*
			BX.adjust(control, {
				attrs: {disabled: 'disabled'}
			});
			*/

			control.value = sv.cache.links[value][0];
			BX.fireEvent(control, 'change');
		}else{

			this.setTargetValue(node.IS_UNCHOOSABLE ? this.getLastValidValue() : value);
		}
	},

	getLastValidValue: function(){

		var value = undefined,
			sc = this.ctrls,
			sv = this.vars;

		for(var k = sv.stack.length - 1; k >= 0 ; k--){
			value = sv.stack[k].control.getValue();

			if(typeof value != 'undefined' && (typeof sv.cache.nodes[value] != 'undefined') && (typeof sv.cache.nodes[value].IS_UNCHOOSABLE == 'undefined'))
				return value;
		}

		return '';
	},

	getLastControl: function(){
		return this.vars.stack[this.vars.stack.length - 1];
	},

	truncateStack: function(pos){

		if(pos < -1)
			return;

		var len = this.vars.stack.length;
		for(var k = pos + 1; k < len; k++){
			this.vars.stack[k].control.remove();

		}

		this.vars.stack.splice(pos + 1, len - (pos + 1));
	},

	getSelectorValue: function(value){
		return value;
	},

	fillCache: function(levels, isIncompleteData){

		var sv = this.vars,
			so = this.opts;

		for(var parent in levels)
		{
			if(!levels.hasOwnProperty(parent))
				continue;

			// overwrite if not set
			if(typeof sv.cache.links[parent] == 'undefined' || sv.cache.incomplete[parent] === true){

				// if not set or complete data passed - recreate
				if(typeof sv.cache.links[parent] == 'undefined' || !isIncompleteData)
					sv.cache.links[parent] = [];

				for(var k in levels[parent])
				{
					if(!levels[parent].hasOwnProperty(k))
						continue;

					sv.cache.links[parent].push(levels[parent][k].VALUE);
					levels[parent][k].PARENT_VALUE = parent;

					this.addItem2Cache(levels[parent][k]);
				}
			}
		}
	},

	addItem2Cache: function(item){
		this.vars.cache.nodes[item.VALUE] = item;
	},

	getCurrentItem: function(){
		return this.vars.cache.nodes[this.vars.value];
	},

	setTargetInputName: function(newName){
		this.ctrls.targetInput.setAttribute('name', newName);
	},

	showError: function(parameters){

		this.setCSSState('error', this.ctrls.scope);

		if(BX.type.isElementNode(this.ctrls.errorMessage)){
			this.ctrls.errorMessage.innerHTML = BX.util.htmlspecialchars(parameters.errors.join(', '));
			BX.show(this.ctrls.errorMessage);
		}

		BX.debug(parameters);
	},

	hideError: function(){

		if(this.vars.allowHideErrors)
		{
			this.dropCSSState('error', this.ctrls.scope);

			if(BX.type.isElementNode(this.ctrls.errorMessage))
				BX.hide(this.ctrls.errorMessage);
		}
	}

	/* Behaviour functions below */
});

BX.ui.chainedSelectors.adapters = {};

// this represents one (single) control that can be used as selector
BX.ui.chainedSelectors.adapters.combobox = function(options){

	this.opts = options;
	this.control = null;
	this.scope = null;
	this.index = null;

	this.place = function(){

		var ctx = this;

		var nodes = this.opts.parent.createNodesByTemplate('selector-scope', {}, true);
		if(nodes == null)
			return;

		this.scope = nodes[0];
		BX.append(this.scope, this.opts.parent.ctrls.pool);

		options.scope = this.scope;
		options.arrowScrollAdditional = 5;
		options.focusOnMouseSelect = false;

		this.opts.parent.fireEvent('before-control-placed', [this]);

		this.control = new BX.ui.combobox(options);
		this.control.vars.outSideClickScope = this.scope; // a little makeup-related spike to make combobox dropdown feel better

		this.control.bindEvent('item-list-discover', BX.proxy(function(d){ // when control doesnt know about nodes, it asks for it

			d.stopRace(); // drop auto-reject by timeout in deferred

			var dBundle = new BX.deferred();
			dBundle.done(BX.proxy(function(bundle){

				// convert here from links to required format
				// could be slow here
				var knownItems = [];
				for(var k in bundle)
					if(bundle.hasOwnProperty(k))
						knownItems.push(this.opts.parent.vars.cache.nodes[bundle[k]]);

				this.opts.parent.fireEvent('before-control-item-discover-done', [knownItems, this]);

				d.resolve({items: knownItems});

			}, this));

			this.opts.parent.getBundleForNode(this.opts.parentNode, dBundle);
		}, this));

		// transfer events from inner widget to outer... bad solution...
		this.control.bindEvent('before-display-page', function(){
			ctx.opts.parent.fireEvent('control-before-display-page', [ctx]);
		});

		this.control.bindEvent('after-select-item', BX.proxy(function(value){
			this.opts.parent.fireEvent('control-change', [this.index, value]);
		}, this));

		this.control.bindEvent('after-deselect-item', BX.proxy(function(){
			this.opts.parent.fireEvent('control-change', [this.index, '']);
		}, this));

		if(this.opts.parent.vars.cache.incomplete[this.opts.parentNode])
			this.control.clearCache();

		this.opts.parent.fireEvent('after-control-placed', [this]);
	};
	this.remove = function(){

		if(this.control != null)
		{
			this.control.remove();
		}

		this.control = null;

		this.opts = null;
		BX.remove(this.scope);

		this.scope = null;
	};
	this.setIndex = function(index){
		this.index = index;
	};
	this.getIndex = function(){
		return this.index;
	};
	this.getValue = function(){
		return this.control.getValue();
	};
	this.getParentValue = function(){
		return this.opts.parentNode;
	};
	this.getControl = function(){
		return this.control;
	};

	// a little hacky method, avoid to use it
	// its purpose is to force control to display information we need without any internal logic evaluation
	this.setValuePair = function(value, display){
		this.control.setTargetInputValue(value);
		this.control.setFakeInputValue(display);
		this.control.setValueVariable(value);
	};

	this.place();

	return this;
}