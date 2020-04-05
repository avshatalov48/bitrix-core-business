(function(){

	if(typeof BX.ui != 'object')
		BX.ui = {};

	//////////////////////////////
	// base widget
	//////////////////////////////

	BX.ui.widget = function(opts){

		BX.merge(this, {
			opts: {
				scope: 						false, // it should be either native dom object, or string that represents node id
				useSpawn: 					false, // if set to true, you can do .spawn() on this object

				messages: 					{}, // language-dependent messages to display
				controls: 					{}, // known links to controls
				bindEvents: 				{}, // event pre-binding (when use this, keep in mind that the resulting instance could not be fully formed yet)

				removeTemplates: 			true, // remove script nodes after search

				initializeByGlobalEvent: 	false, // if equals to a not-empty string, initialization will be performed only by event with that name, being fired on document
				globalEventScope: 			'document' // initializeByGlobalEvent scope (could be 'document' or 'window')
			},
			vars: {}, // significant variables
			ctrls: {}, // links to controls
			tmpls: {}, // templates
			sys: {
				stack: 				{init:[]},
				code: 				'widget', // only [a-z0-9_-] allowed
				initialized: 		false
			}
		});

		this.pushFuncStack('init', BX.ui.widget);

		this.isUIWidget = true; // prevent BX.merge() from going deeper on a widget instance
	}
	// the following functions can be overrided with inheritance
	BX.merge(BX.ui.widget.prototype, {

		////////////////////////////
		/// about initialization

		// only basic things here
		preInit: function(){
			var ctx = this,
				so = this.opts,
				sc = this.ctrls,
				code = this.sys.code;

			sc.scope = null;

			if(!('querySelector' in document))
				throw new Error('Your browser does not support querySelector');

			if(!code.match(/^[a-zA-Z0-9-_]+$/))
				throw new Error('Only letters, digitis, "-" and "_" allowed in code');
		},

		// member of stack of initializers, must be defined even if do nothing
		init: function(){

			var ctx = this,
				sc = this.ctrls,
				so = this.opts,
				code = this.sys.code;

			if(so.scope !== false){ // some widgets may have no scope

				sc.scope = BX.type.isNotEmptyString(so.scope) ? BX(so.scope) : so.scope;
				if(!BX.type.isElementNode(sc.scope))
					throw new Error('Invalid node passed');

				if(so.useSpawn && sc.scope)
					ctx.tmpls['scope'] = sc.scope.outerHTML;

				// templates
				var templates = sc.scope.querySelectorAll('script[type="text/html"]');
				for(var k = 0; k < templates.length; k++){
					var id = BX.data(templates[k], 'template-id');

					if(typeof id == 'string' && id.length > 0 && id.search('bx-ui-'+code) == 0){

						id = id.replace('bx-ui-'+code+'-', '');
						ctx.tmpls[id] = templates[k].innerHTML;

						if(this.opts.removeTemplates)
							BX.remove(templates[k]);
					}
				}
			}

			// events
			if(typeof so.bindEvents == 'object'){
				for(var k in so.bindEvents)
				{
					if(so.bindEvents.hasOwnProperty(k))
						if(BX.type.isFunction(so.bindEvents[k]))
							this.bindEvent(k, so.bindEvents[k]);
				}
			}
			so.bindEvents = null;
		},

		remove: function(){
			// drop scope
			if(BX.type.isDomNode(this.ctrls.scope))
				this.ctrls.scope.innerHTML = '';

			// ubind custom events
			BX.unbindAll(this);

			// here should be a mechanism of "remove stack", just equal to "init stack", but works in reversed manner

			/*
			this.opts = null;
			this.vars = null;
			this.ctrls = null;
			this.tmpls = null;
			this.sys = null;
			*/

			// later unregister in global dispatcher, if ID is set
		},

		////////////////////////////
		/// about system

		getControlClassName: function(id){
			return 'bx-ui-'+this.sys.code+'-'+id;
		},

		getControl: function(id, notRequired, scope, getAll){

			if(!BX.type.isNotEmptyString(id))
				return null;

			if(BX.type.isElementNode(this.opts.controls[id]))
				return this.opts.controls[id];

			if(!this.ctrls.scope && !searchWholeDoc)
				return null;

			var sScope = this.ctrls.scope;
			if(BX.type.isElementNode(scope))
				sScope = scope;

			var checkFound = function(result){
				return (!getAll && result !== null) || (getAll && result.length > 0);
			};

			try{

				// it might be in a special data attribute
				var node = sScope[getAll ? 'querySelectorAll' : 'querySelector']('[data-bx-ui-id="'+this.sys.code+'-'+id+'"]');
				if(checkFound(node))
					return node;

			}catch(e){}

			try{

				// it might be control class
				var node = sScope[getAll ? 'querySelectorAll' : 'querySelector']('.'+this.getControlClassName(id));
				if(checkFound(node))
					return node;

			}catch(e){}

			try{

				// it might be some other class
				var node = sScope[getAll ? 'querySelectorAll' : 'querySelector']('.'+id);
				if(checkFound(node))
					return node;

			}catch(e){}

			try{

				// last chance - it might be a specified selector
				var node = sScope[getAll ? 'querySelectorAll' : 'querySelector'](id);
				if(checkFound(node))
					return node;

			}catch(e){}

			if(node === null && !notRequired)
				throw new Error('Requested control node can not be found ('+id+')');

			return node;
		},

		setOption: function(name, value){
			this.opts[name] = value;
		},

		getOption: function(name){
			return this.opts[name];
		},

		getSysCode: function(){
			return this.sys.code;
		},

		////////////////////////////
		/// about templating

		template: function(id, html)
		{
			if(typeof html == 'undefined')
			{
				return this.tmpls[id];
			}
			else
			{
				if(!BX.type.isString(html))
					throw new TypeError('Bad template html');

				this.tmpls[id] = html;
			}
		},

		getHTMLByTemplate: function(templateId, replacements){

			var html = this.tmpls[templateId];

			if(!BX.type.isNotEmptyString(html))
			{
				BX.debug("template not found: "+templateId);
				return '';
			}

			for(var k in replacements)
			{
				if(!replacements.hasOwnProperty(k))
					continue;

				if(typeof replacements[k] != 'undefined' && replacements.hasOwnProperty(k)){

					var replaceWith = '';
					if(k.toString().indexOf('=') == 0){ // leading '=' stands for an unsafe replace - no escaping
						replaceWith = replacements[k].toString();
						k = k.toString().substr(1);
					}else
						replaceWith = BX.util.htmlspecialchars(replacements[k]).toString();

					var placeHolder = '{{'+k.toString().toLowerCase()+'}}';

					if(replaceWith.search(placeHolder) >= 0) // you must be joking
						replaceWith = '';

					while(html.search(placeHolder) >= 0) // new RegExp('', 'g') on user-controlled data seems not so harmless
						html = html.replace(placeHolder, replaceWith);
				}
			}

			return html;
		},

		createNodesByTemplate: function(templateId, replacements, onlyTags){

			var html = this.getHTMLByTemplate(templateId, replacements);

			var template = this.tmpls[templateId];
			if(!BX.type.isNotEmptyString(template))
			{
				return null;
			}

			template = template.replace(/^\s\s*/, '').replace(/\s\s*$/, ''); // trim

			// table makeup behaves not so well when being parsed by a browser, so a little hack is on route:

			var isTableRow = false;
			var isTableCell = false;

			if(template.search(/^<\s*(tr|th)[^<]*>/) >= 0)
				isTableRow = true;
			else if(template.search(/^<\s*td[^<]*>/) >= 0)
				isTableCell = true;

			var keeper = document.createElement('div');

			if(isTableRow || isTableCell){

				if(isTableRow){
					keeper.innerHTML = '<table><tbody>'+html+'</tbody></table>';
					keeper = keeper.childNodes[0].childNodes[0];
				}else{
					keeper.innerHTML = '<table><tbody><tr>'+html+'</tr></tbody></table>';
					keeper = keeper.childNodes[0].childNodes[0].childNodes[0];
				}
			}else
				keeper.innerHTML = html;

			if(onlyTags){

				var children = keeper.childNodes;
				var result = [];

				// we need only non-text nodes
				for(var k = 0; k < children.length; k++)
					if(BX.type.isElementNode(children[k]))
						result.push(children[k]);

				return result;
			}else
				return Array.prototype.slice.call(keeper.childNodes);
		},

		replaceTemplate: function(templateId, html){
			this.tmpls[templateId] = html;
		},

		////////////////////////////
		/// about inheritance

		parentConstruct: function(owner, opts){
			var c = owner.superclass;
			if(typeof c == 'object')
				c.constructor.apply(this, [opts, true]);
		},

		handleInitStack: function(nf, owner, opts){

			this.pushFuncStack('init', owner);

			if(!nf){
				BX.merge(this.opts, opts);

				BX.ui.widget.prototype.preInit.call(this);

				var init = function(){

					if(this.sys.initialized) // already initialized once
						return;

					this.resolveFuncStack('init'); // resove init stacks

					for(var i in this.sys.stack)
					{
						if(this.sys.stack.hasOwnProperty(i))
							if(i != 'init')
								this.resolveFuncStack(i, true); // resolve all other stacks
					}

					this.sys.initialized = true;
					this.fireEvent('init', [this]);
				};

				if(BX.type.isString(this.opts.initializeByGlobalEvent) && this.opts.initializeByGlobalEvent.length > 0){
					var scope = this.opts.globalEventScope == 'window' ? window : document;
					BX.addCustomEvent(scope, this.opts.initializeByGlobalEvent, BX.proxy(init, this));
				}else
					init.call(this);
			}
		},

		// when you add fName to the stack, function with the corresponding name must exist, at least equal to BX.DoNothing()
		pushFuncStack: function(fName, owner){
			if(BX.type.isFunction(owner.prototype[fName])){

				if(typeof this.sys.stack[fName] == 'undefined')
					this.sys.stack[fName] = [];

				this.sys.stack[fName].push({owner: owner, f: owner.prototype[fName]});
			}
		},

		disableInFuncStack: function(fName, owner){

			var stack = this.sys.stack[fName];

			if(typeof stack == 'undefined')
				return;

			for(var k = 0; k < stack.length; k++){
				if(stack[k].owner == owner)
					stack[k].f = BX.DoNothing;
			}
		},

		resolveFuncStack: function(fName, fire){

			var stack = this.sys.stack[fName];

			if(typeof stack == 'undefined')
				return;

			for(var k = 0; k < stack.length; k++){
				stack[k].f.call(this);
			}

			if(fire)
				this.fireEvent(fName, [this], document);

			this.sys.stack[fName] = null;
		},

		////////////////////////////
		/// about events

		fireEvent: function(eventName, args, scope){
			scope = scope || this;
			args = args || [];
			BX.onCustomEvent(scope, 'bx-ui-'+this.sys.code+'-'+eventName, args);
		},

		bindEvent: function(eventName, callback){
			BX.addCustomEvent(this, 'bx-ui-'+this.sys.code+'-'+eventName, callback);
		},

		////////////////////////////
		/// about css states

		setCSSState: function(statName, scope)
		{
			this.changeCSSState(statName, scope, true);
		},

		dropCSSState: function(statName, scope)
		{
			this.changeCSSState(statName, scope, false);
		},

		changeCSSState: function(statName, scope, way)
		{
			scope = scope || this.ctrls.scope;
			if(typeof statName != 'string' || statName.length == 0)
				return;

			BX[way ? 'addClass' : 'removeClass'](scope, 'bx-ui-state-'+statName);
		},

		////////////////////////////
		/// about miscellaneous

		spawn: function(node, onSpawn){

			// if spawning was enabled, you can spawn widget on auto-duplicated scope
			// otherwise, you should prepare scope by yourself

			if(this.opts.useSpawn)
				BX.html(node, this.tmpls.scope);

			var opts = BX.clone(this.opts);
			opts.scope = node;

			if(BX.type.isFunction(onSpawn))
				onSpawn.apply(this, [opts, node]);

			return new this.constructor(opts);
		},

		getRandom: function(){
			// only letters, digits, - and _ allowed to return
			return 	'bx'+this.sys.code+
					Math.floor((Math.random() * 1000) + 1)+
					Math.floor((Math.random() * 1000) + 1);
		}

	});

})();