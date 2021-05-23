BX.namespace('BX.Sale.component.location.selector');

if(typeof BX.Sale.component.location.selector.steps == 'undefined' && typeof BX.ui != 'undefined' && typeof BX.ui.widget != 'undefined'){

	BX.Sale.component.location.selector.steps = function(opts, nf){

		this.parentConstruct(BX.Sale.component.location.selector.steps, opts);

		BX.merge(this, {
			opts: {
				bindEvents: {
					'after-select-item': function(value){

						if(typeof this.opts.callback == 'string' && this.opts.callback.length > 0 && this.opts.callback in window)
							window[this.opts.callback].apply(this, [value, this]);
					}
				},
				disableKeyboardInput: 	false,
				dontShowNextChoice: 	false,
				pseudoValues: 			[], // values that can be only displayed as selected, but not actually selected
				provideLinkBy: 			'id',
				requestParamsInject:	false
			},
			vars: {
				cache: {nodesByCode: {}}
			},
			sys: {
				code: 'slst'
			},
			flags: {
				skipAfterSelectItemEventOnce: false
			}
		});
		
		this.handleInitStack(nf, BX.Sale.component.location.selector.steps, opts);
	};
	BX.extend(BX.Sale.component.location.selector.steps, BX.ui.chainedSelectors);
	BX.merge(BX.Sale.component.location.selector.steps.prototype, {

		// member of stack of initializers, must be defined even if does nothing
		init: function(){
			this.pushFuncStack('buildUpDOM', BX.Sale.component.location.selector.steps);
			this.pushFuncStack('bindEvents', BX.Sale.component.location.selector.steps);
		},

		// add additional controls
		buildUpDOM: function(){},

		bindEvents: function(){

			var ctx = this,
				so = this.opts;

			if(so.disableKeyboardInput){ //toggleDropDown
				this.bindEvent('after-control-placed', function(adapter){

					var control = adapter.getControl();

					BX.unbindAll(control.ctrls.toggle);
					// spike, bad idea to access fields directly
					BX.bind(control.ctrls.scope, 'click', function(e){
						control.toggleDropDown();
					});
				});
			}

			// quick links
			BX.bindDelegate(this.getControl('quick-locations', true), 'click', {tag: 'a'}, function(){
				ctx.setValueByLocationId(BX.data(this, 'id'));
			});
		},

		////////// PUBLIC: free to use outside

		setValueByLocationId: function(id){
			BX.Sale.component.location.selector.steps.superclass.setValue.apply(this, [id]);
		},

		setValueByLocationIds: function(locationsData){

			if(!locationsData.PARENT_ID)
				return;

			this.flags.skipAfterSelectItemEventOnce = true;
			this.setValueByLocationId(locationsData.PARENT_ID);

			this.bindEvent('after-control-placed', function(adapter){

				var control = adapter.getControl();

				if(control.vars.value != false)
					return;

				if(locationsData.IDS)
					this.opts.requestParamsInject = {'filter': {'=ID': locationsData.IDS}};

				control.tryDisplayPage('toggle');
			});
		},

		setValueByLocationCode: function(code){
			var sv = this.vars;

			// clean
			if(code == null || code == false || typeof code == 'undefined' || code.toString().length == 0){ // deselect
				this.displayRoute([]);
				this.setValueVariable('');
				this.setTargetValue('');
				this.fireEvent('after-clear-selection');
				return;
			}

			// set
			this.fireEvent('before-set-value', [code]);

			var d = new BX.deferred();
			var ctx = this;

			d.done(BX.proxy(function(route){

				this.displayRoute(route);

				var value = sv.cache.nodesByCode[code].VALUE;
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

			this.getRouteToNodeByCode(code, d);
		},

		setValue: function(value){
			if(this.opts.provideLinkBy == 'id')
				BX.Sale.component.location.selector.steps.superclass.setValue.apply(this, [value]);
			else
				this.setValueByLocationCode(value);
		},

		setTargetValue: function(value){
			this.setTargetInputValue(this.opts.provideLinkBy == 'code' ? (value ? this.vars.cache.nodes[value].CODE : ''): value);

			if(!this.flags.skipAfterSelectItemEventOnce)
				this.fireEvent('after-select-item', [value]);
			else
				this.flags.skipAfterSelectItemEventOnce = false;
		},

		getValue: function(){

			if(this.opts.provideLinkBy == 'id')
				return this.vars.value === false ? '' : this.vars.value;
			else{
				return this.vars.value ? this.vars.cache.nodes[this.vars.value].CODE : '';
			}
		},

		getNodeByLocationId: function(value){
			return this.vars.cache.nodes[value];
		},

		getSelectedPath: function(){

			var sv = this.vars,
				result = [];

			if(typeof sv.value == 'undefined' || sv.value == false || sv.value == '')
				return result;

			if(typeof sv.cache.nodes[sv.value] != 'undefined'){

				var node = sv.cache.nodes[sv.value];
				while(typeof node != 'undefined')
				{
					var item = BX.clone(node);
					var parentId = item.PARENT_VALUE;

					delete(item.PATH);
					delete(item.PARENT_VALUE);
					delete(item.IS_PARENT);

					if(typeof item.TYPE_ID != 'undefined' && typeof this.opts.types != 'undefined')
						item.TYPE = this.opts.types[item.TYPE_ID].CODE;

					result.push(item);

					if(typeof parentId == 'undefined' || typeof sv.cache.nodes[parentId] == 'undefined')
						break;
					else
						node = sv.cache.nodes[parentId];
				}
			}

			return result;
		},

		////////// PRIVATE: forbidden to use outside (for compatibility reasons)

		setInitialValue: function(){

			if(this.opts.selectedItem !== false) // there will be always a value as ID, no matter what this.opts.provideLinkBy is equal to
				this.setValueByLocationId(this.opts.selectedItem);
			else if(this.ctrls.inputs.origin.value.length > 0) // there colud be eiter ID or CODE
			{
				if(this.opts.provideLinkBy == 'id')
					this.setValueByLocationId(this.ctrls.inputs.origin.value);
				else
					this.setValueByLocationCode(this.ctrls.inputs.origin.value);
			}
		},

		// get route for nodeId and resolve deferred with it
		getRouteToNodeByCode: function(code, d){
			var sv = this.vars,
				ctx = this;

			if(typeof code != 'undefined' && code !== false && code.toString().length > 0){

				var route = [];

				if(typeof sv.cache.nodesByCode[code] != 'undefined')
					route = this.getRouteToNodeFromCache(sv.cache.nodesByCode[code].VALUE);

				if(route.length == 0){ // || (sv.cache.nodes[nodeId].IS_PARENT && typeof sv.cache.links[nodeId] == 'undefined')){

					// no way existed or item is parent without children downloaded

					// download route, then try again
					ctx.downloadBundle({
						request: {CODE: code}, // get only route
						callbacks: {
							onLoad: function(data){

								// mark absent as incomplete, kz we do not know if there are really more items of that level or not
								for(var k in data){
									if(typeof sv.cache.links[k] == 'undefined')
										sv.cache.incomplete[k] = true;
								}

								ctx.fillCache(data, true);

								route = [];

								// trying to re-get
								if(typeof sv.cache.nodesByCode[code] != 'undefined')
									route = this.getRouteToNodeFromCache(sv.cache.nodesByCode[code].VALUE);

								if(route.length == 0)
									d.reject('notfound');
								else
									d.resolve(route);
							},
							onError: function(){
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

		addItem2Cache: function(item){
			this.vars.cache.nodes[item.VALUE] = item;
			this.vars.cache.nodesByCode[item.CODE] = item;
		},

		controlChangeActions: function(stackIndex, value){

			var ctx = this,
				so = this.opts,
				sv = this.vars,
				sc = this.ctrls;

			this.hideError();

			////////////////

			if(value.length == 0){

				ctx.truncateStack(stackIndex);
				sv.value = ctx.getLastValidValue();
				ctx.setTargetValue(sv.value);

				this.fireEvent('after-select-real-value');

			}else if(BX.util.in_array(value, so.pseudoValues)){

				ctx.truncateStack(stackIndex);
				ctx.setTargetValue(ctx.getLastValidValue());
				this.fireEvent('after-select-item', [value]);

				this.fireEvent('after-select-pseudo-value');

			}else{

				var node = sv.cache.nodes[value];

				if(typeof node == 'undefined')
					throw new Error('Selected node not found in the cache');

				// node found

				ctx.truncateStack(stackIndex);

				if(so.dontShowNextChoice){
					if(node.IS_UNCHOOSABLE)
						ctx.appendControl(value);
				}else{
					if(typeof sv.cache.links[value] != 'undefined' || node.IS_PARENT)
						ctx.appendControl(value);
				}

				if(ctx.checkCanSelectItem(value)){
					sv.value = value;
					ctx.setTargetValue(value);
					this.fireEvent('after-select-real-value');
				}
			}
		},

		// adapter to ajax page request
		refineRequest: function(request){

			var filter = {};
			var select = {
				'VALUE': 'ID',
				'DISPLAY': 'NAME.NAME',
				'1': 'TYPE_ID',
				'2': 'CODE'
			};
			var additionals = {};

			if(typeof request['PARENT_VALUE'] != 'undefined'){ // bundle request
				filter['=PARENT_ID'] = request.PARENT_VALUE;
				select['10'] = 'IS_PARENT';
			}

			if(typeof request['VALUE'] != 'undefined'){ // search by id
				filter['=ID'] = request.VALUE;
				additionals['1'] = 'PATH';
			}

			if(BX.type.isNotEmptyString(request['CODE'])){ // search by code
				filter['=CODE'] = request.CODE;
				additionals['1'] = 'PATH';
			}

			if(BX.type.isNotEmptyString(this.opts.query.BEHAVIOUR.LANGUAGE_ID))
				filter['=NAME.LANGUAGE_ID'] = this.opts.query.BEHAVIOUR.LANGUAGE_ID;

			// we are already inside linked sub-tree, no deeper check for SITE_ID needed
			if(BX.type.isNotEmptyString(this.opts.query.FILTER.SITE_ID)){

				if(typeof this.vars.cache.nodes[request.PARENT_VALUE] == 'undefined' || this.vars.cache.nodes[request.PARENT_VALUE].IS_UNCHOOSABLE)
					filter['=SITE_ID'] = this.opts.query.FILTER.SITE_ID;
			}

			var result =  {
				'select': select,
				'filter': filter,
				'additionals': additionals,
				'version': '2'
			};

			if(this.opts.requestParamsInject)
			{
				for(var type in this.opts.requestParamsInject)
				{
					if(this.opts.requestParamsInject.hasOwnProperty(type))
					{
						if(result[type] == undefined)
							result[type] = {};

						for(var param in this.opts.requestParamsInject[type])
						{
							if(this.opts.requestParamsInject[type].hasOwnProperty(param))
							{
								if(result[type][param] != undefined)
								{
									var tmp = result[type][param];
									result[type][param] = [];
									result[type][param].push(tmp);
								}
								else
								{
									result[type][param] = [];
								}

								for(var val in this.opts.requestParamsInject[type][param])
									if(this.opts.requestParamsInject[type][param].hasOwnProperty(val))
										result[type][param].push(this.opts.requestParamsInject[type][param][val]);
							}
						}
					}
				}
			}

			return result;
		},

		// adapter to ajax page responce
		refineResponce: function(responce, request){

			if(responce.length == 0)
				return responce;

			if(typeof request.PARENT_VALUE != 'undefined'){ // it was a bundle request

				var r = {};
				r[request.PARENT_VALUE] = responce['ITEMS'];
				responce = r;

			}else if(typeof request.VALUE != 'undefined' || typeof request.CODE != 'undefined'){ // it was a route request

				var levels = {};

				if(typeof responce.ITEMS[0] != 'undefined' && typeof responce.ETC.PATH_ITEMS != 'undefined'){

					var parentId = 0;

					for(var k = responce.ITEMS[0]['PATH'].length - 1; k >= 0; k--){

						var itemId = responce.ITEMS[0]['PATH'][k];
						var item = responce.ETC.PATH_ITEMS[itemId];

						item.IS_PARENT = true;

						levels[parentId] = [item];

						parentId = item.VALUE;
					}

					// add item itself
					levels[parentId] = [responce.ITEMS[0]];
				}

				responce = levels;
			}

			return responce;
		},

		showError: function(parameters){

			if(parameters.type != 'server-logic')
				parameters.errors = [this.opts.messages.error]; // generic error on js error

			this.ctrls.errorMessage.innerHTML = '<p><font class="errortext">'+BX.util.htmlspecialchars(parameters.errors.join(', '))+'</font></p>';
			BX.show(this.ctrls.errorMessage);

			BX.debug(parameters);
		}
	});
}
