(function(){

	if(typeof BX.ui != 'object')
		BX.ui = {};

	//////////////////////////////
	// generic autocomplete
	//////////////////////////////

	/*
	todo: implement handling selectbox in the role of target input
	todo: take care about the situation when BX.ajax() cannot handle request in time, and user presses enter or somehow else input looses focus
	todo: make connection between origin and fake inputs, handling: change
	todo: do ajax call interruption if it cannot handle request in time, but there is another newly typed request came and pending
	*/

	BX.ui.autoComplete = function(opts, nf){

		this.parentConstruct(BX.ui.autoComplete, opts);

		BX.merge(this, {
			opts: { // default options
				source:					'/somewhere.php', // url that will be used to obtain select options from
				pageSize:				20, // amount of variants to show
				paginatedRequest:		true, // if true, parameters for server-side paginator will be sent in the request

				// behaviour
				autoSelectOnBlur:			false, // if true, plugin will select first item in variant list when input looses focus
				autoSelectOnTab: 			false, // if true, plugin will select first item in variant list when user types tab button on fake input with string being incomplete typed in
				autoSelectOnlyIfOneVariant: false,
				selectOnEnter:				true, // item can be selected by pressing Enter on it
				selectByClick:				true, // item can be selected by clicking on it
				chooseUsingArrows:			true, // item can be choosable when user presses arrow up\down while input is in focus
				scrollToVariantOnArrow: 	true,

				closePopupOnOuterClick: true, // if true, popup will be closed when user clicks outside the widget

				focusOnMouseSelect:		true, // if true, plugin will return focus to the fake input after user selects item using mouse. it allows not to lose tabindex order
				autoSelectIfOneVariant:	false, // if true, and only one variant found for currently typed query, it will be selected without any dropdown shown
				debounceTime:			500, // time of reaction on input content change. Should not be too small
				startSearchLen:			2, // minimum string length search will start with

				knownItems:				[], // already known items which go directly to the cache
				selectedItem:			false, // initially selected item VALUE
				useCache:				true, // always should be true, behaviour when useCache == false is not implemented
				usePagingOnScroll:		false, // if true, showing items on pane scroll will be used. Note that you must specify also paneHConstraint option, or have popup height limited in your css. Otherwise you will be wondered with the result :)
				useCustomScrollPane:	false, // always false, the developing of a custom scroll panel were frozen currently
				scrollThrottleTimeout:	300, // timeout of reaction on pane scroll. Should not be too small
				paneHConstraint:		0, // limit pane height with this value, if greather than zero

				// language-dependent messages to display
				messages: {
					nothingFound:		'Sorry, nothing found',
					error:				'Error occured',
					clearSelection:		'Clear selection'
				},

				// magic design-related values
				arrowScrollAdditional:	0,
				pageUpWardOffset:		0,
				wrapTagName:			'span',
				paneHConstraintType:	'max-height', // constraint type of pane height
				wrapSeparate: 			true,

				bindEvents: {
					'init': function(){ // after all we do this
						this.setInitialValue();
						this.vars.allowHideErrors = true;
					}
				}
			},
			vars: { // significant variables
				opened:					false, // whether dropdown is opened or not
				eventLock:				false, // when we fire 'change' on the target node, we dont want our own callback to be called
				displayPageMutex:		false,
				blockingCall:			false,
				keyboardMutex:			false,

				cache: { // item cache
					nodes:				{}, // data index, keeps data for each node ever loaded
					search:				{}, // cache for request: map from query string to a set of items in responce. Here pagenavigation can be implemented without a trouble
					ceilings:			{}
				},
				lastQuery:				null,
				lastPage:				0,
				displayedIndex:			[],

				value:					false, // actually, [VALUE,DISPLAY] pair id
				currentGlow:			false, // item that is currently highlighted by arrow-up\down keys
				previousGlow:			false, // previous value of currentGlow

				outSideClickScope:		null,
				forceSelectSingeOnce:	false,
				allowHideErrors: 		false
			},
			ctrls: { // links to controls
				displayedItems: {},
				popup: null
			},
			sys: {
				code:					'autocomplete'
			}
		});

		this.handleInitStack(nf, BX.ui.autoComplete, opts);
	};
	BX.extend(BX.ui.autoComplete, BX.ui.widget);

	// the following functions can be overrided with inheritance
	BX.merge(BX.ui.autoComplete.prototype, {

		// member of stack of initializers, must be defined even if do nothing
		init: function(){
			var ctx = this,
				so = this.opts,
				sv = this.vars,
				sc = this.ctrls;

			// find input
			var input = this.getControl('input', true);
			if(input == null)
				input = sc.scope.querySelector('input[type="text"]');
			if(input == null)
				input = sc.scope.querySelector('select');
			if(input == null)
				throw new Error('Input control still not found');

			sc.inputs = {
				origin: input == null ? sc.scope : input
			};

			sv.loader = new BX.ui.loader({
				timeout: 500
			});
			sv.loader.bindEvent('toggle', BX.proxy(this.whenLoaderToggle, ctx));

			if(sc.inputs.origin.nodeName == 'SELECT'){
				throw new Error('Sorry, usage of <select> node as a source currently is not implemented');
				// todo: get knownItems from sc.inputs.origin options (and also so.selectedItem)
			}

			if(typeof so.knownItems == 'object')
				this.fillCache(so.knownItems, false);

			this.pushFuncStack('buildUpDOM', BX.ui.autoComplete);
			this.pushFuncStack('bindEvents', BX.ui.autoComplete);
		},

		buildUpDOM: function(){
			var so = this.opts,
				sc = this.ctrls,
				sv = this.vars,
				code = this.sys.code;

			// add container node
			sc.container = BX.create('div', {
				props: {
					className: 'bx-ui-'+code+'-container'
				},
				style: {
					margin: 0,
					padding: 0,
					border: 'none',
					position: 'relative'
				}
			});

			//BX.style(sc.container, 'display', BX.style(sc.inputs.origin, 'display'));

			BX.insertAfter(sc.container, sc.inputs.origin);

			// clone input node
			var pseudoInput = BX.clone(sc.inputs.origin);
			pseudoInput.removeAttribute('name'); // make it invisible for form
			BX.adjust(pseudoInput, {
				props: {
					className: 'bx-ui-'+code+'-fake'
				}
			});
			sc.container.appendChild(pseudoInput);

			sc.inputs.fake = pseudoInput;

			/*
			if(sv.value !== false)
			{
				console.dir('set from input');
				//this.selectItem(sv.value);
				this.setFakeInputValue(sv.cache.nodes[sv.value].DISPLAY);
			}
			else
				this.setFakeInputValue('');
			*/

			// hide origin input
			BX.hide(sc.inputs.origin);
			//sc.inputs.origin.setAttribute('type', 'hidden');

			if(BX.browser.IsIE8()){
				BX.bind(sc.inputs.fake, 'click', function(e){
					BX.eventCancelBubble(e);
				});
				BX.bind(sc.container, 'click', function(){
					sc.inputs.fake.focus();
				});
			}

			// clear handle
			sc.clear = this.getControl('clear', true);
			if(!BX.type.isElementNode(sc.clear)){
				sc.clear = BX.create('div', {
					props: {
						className: 'bx-ui-'+code+'-clear'
					},
					attrs: {
						title: so.messages.clearSelection
					},
					style: {
						position: 'absolute',
						top: '0px',
						right: '0px'
					}
				});

				sc.container.appendChild(sc.clear);
			}

			//if(sv.value === false)
			BX.style(sc.clear, 'display', 'none');

			// insert pane
			sc.pane = this.getControl('pane', true);
			if(!BX.type.isElementNode(sc.pane)){
				sc.pane = BX.create('div', {
					props: {
						className: 'bx-ui-'+code+'-pane'
					},
					style: {
						display: 'none',
						position: 'absolute'
					}
				});
				sc.container.appendChild(sc.pane);
			}
			// adjust a bit
			if(so.usePagingOnScroll){
				BX.style(sc.pane, 'overflow-y', 'auto');
				BX.style(sc.pane, 'overflow-x', 'hidden');

				if(so.paneHConstraint > 0 && so.paneHConstraintType != '')
					BX.style(sc.pane, so.paneHConstraintType, so.paneHConstraint+'px');
			}

			if (so.usePopup)
			{
				BX.style(sc.pane, 'position', 'inherit');
				BX.style(sc.pane, 'border', 'none');
				BX.style(sc.pane, 'box-shadow', 'none');
			}

			// insert variants
			sc.vars = this.getControl('variants', true);
			if(!BX.type.isElementNode(sc.vars)){
				sc.vars = BX.create('div', {
					props: {
						className: 'bx-ui-'+code+'-variants'
					}
				});
				sc.pane.appendChild(sc.vars);
			}

			// nothing found message
			sc.nothingFound = this.getControl('nothing-found', true);
			// error message
			sc.errorMessage = this.getControl('error-message', true);
		},

		bindEvents: function(){

			var ctx = this,
				so = this.opts,
				sv = this.vars,
				sc = this.ctrls,
				code = this.sys.code;

			// bind item select
			if(so.selectByClick){

				BX.bindDelegate(sc.pane, 'click', {
					className: 'bx-ui-'+code+'-variant'
				}, function(){
					var id = BX.data(this, 'bx-'+code+'-item-value');
					if(typeof id != 'undefined' && typeof sv.cache.nodes[id] != 'undefined'){
						ctx.selectItem(id);
						if(so.focusOnMouseSelect)
							sc.inputs.fake.focus();
					}
				});
			}

			// bind debounced key-type input change
			BX.bindDebouncedChange(sc.inputs.fake,

				function(val){

					if(val.length >= so.startSearchLen){

						ctx.displayPage({QUERY: val});

					}else
						ctx.hideDropdown();
				},
				function(){

					ctx.fireEvent('before-input-value-modify');

					// dropping selections
					ctx.deselectItem();

					ctx.fireEvent('after-input-value-modify');
				},
				so.debounceTime,
				sc.inputs.fake
			);

			// close dropdown by a custom event
			BX.addCustomEvent(document, 'bx-ui-'+code+'-close-dropdown', function(){
				ctx.hideDropdown();
			});

			// outside click should close the dropdown
			if(so.closePopupOnOuterClick){

				sv.outSideClickScope = sc.container;

				BX.bind(document, 'click', function(e){
					e = e || window.event;

					if(!BX.isParentForNode(sv.outSideClickScope, e.target || e.srcElement)){
						ctx.hideDropdown();
					}
				});
			}

			// keyboard events
			BX.bind(sc.inputs.fake, 'keydown', function(e){

				if(sv.keyboardMutex){
					//console.dir('kb mutex is on, ignore keyboard');
					return;
				}

				var key = e.keyCode || e.which;
				var displayedLen = sv.displayedIndex.length;

				if(so.chooseUsingArrows){

					// up (38) - down (40)
					if((key == 38 || key == 40) && sv.opened && displayedLen > 0){
						var way = key == 38 ? -1 : 1;

						// end were reached, but we can load more
						if(sv.currentGlow == displayedLen - 1 && ctx.getCanLoadMore()){
							return;
						}

						sv.previousGlow = sv.currentGlow;
						sv.currentGlow += way;

						sv.currentGlow = sv.currentGlow % displayedLen;
						if(sv.currentGlow < 0)
							sv.currentGlow = displayedLen + sv.currentGlow;

						ctx.toggleGlow();

						var item = sc.displayedItems[sv.displayedIndex[sv.currentGlow]];

						if(so.scrollToVariantOnArrow){

							// here we determine currently selected item is not in the visible area
							var pos = BX.pos(item, sc.vars);
							var itemBottomPos = pos.top+pos.height;

							var a = pos.top;
							var b = pos.height;
							var c = sc.pane.clientHeight;
							var d = sc.pane.scrollTop;
							var f = so.arrowScrollAdditional;

							if(a + b > c + d){
								sc.scrollController.scrollTo({dy: a + b - (c + d) + f});
							}else if(a < d){
								sc.scrollController.scrollTo({dy: -(d - a + f)});
							}
						}

						BX.PreventDefault(e);
					}
				}

				// on enter we perform item selection
				if(key == 13 && so.selectOnEnter){
					if(sv.currentGlow !== false)
						ctx.selectItem(sv.displayedIndex[sv.currentGlow]);
					else if(sv.opened && displayedLen > 0)
						ctx.selectItem(sv.displayedIndex[0]);
				}

				// on tab dropdown close and optional select
				if(key == 9 && sv.opened && so.autoSelectOnTab){

					if((so.autoSelectOnlyIfOneVariant && displayedLen == 1) || (!so.autoSelectOnlyIfOneVariant && displayedLen > 0))
						ctx.selectItem(sv.displayedIndex[0]);
					else
						ctx.hideDropdown();
				}

				if(key == 13)
					BX.PreventDefault(e);
			});

			if(so.autoSelectOnBlur)
			{
				BX.bind(sc.inputs.fake, 'blur', function(){

					var displayedLen = sv.displayedIndex.length;

					if(sv.opened && ((so.autoSelectOnlyIfOneVariant && displayedLen == 1) || (!so.autoSelectOnlyIfOneVariant && displayedLen > 0)))
						ctx.selectItem(sv.displayedIndex[0]);
				});
			}

			if(so.autoSelectOnBlur)
			{
				BX.bind(sc.inputs.fake, 'blur', function(){

					var displayedLen = sv.displayedIndex.length;

					if(sv.opened && ((so.autoSelectOnlyIfOneVariant && displayedLen == 1) || (!so.autoSelectOnlyIfOneVariant && displayedLen > 0)))
						ctx.selectItem(sv.displayedIndex[0]);
				});
			}

			// when nothing were selected (but there were already an attempt of search), open dropdown if it was closed occasionly by user
			BX.bind(sc.inputs.fake, 'click', function(){
				if(!sv.opened && sv.value === false && sv.displayedIndex.length > 0)
				{
					ctx.showDropdown();
				}
			});

			// clear handle
			BX.bind(sc.clear, 'click', function(){
				ctx.clearSelected();
			});

			if(so.usePagingOnScroll){

				if(so.useCustomScrollPane){
					sc.scrollController = null;
					throw new Error('Sorry, custom scroll panel currently is not implemented');
				}else{
					sc.scrollController = new BX.ui.scrollPaneNative({
						scope: sc.pane,
						eventTimeout: so.scrollThrottleTimeout,
						controls: {
							'container': sc.pane
						}
					});
				}

				ctx.vars.addPage = BX.debounce(function(){

					if(!sv.opened)
						return;

					ctx.blockingCall(); // set mutex
					ctx.displayNextPage();
				}, 10);

				sc.scrollController.bindEvent('scroll-to-end', ctx.vars.addPage);
				sc.scrollController.bindEvent('has-free-space', ctx.vars.addPage);

				// when really add smth, dispatch this signal:
				this.bindEvent('after-page-display', function(){
					sc.scrollController.informContentChanged();
					if(ctx.vars.lastPage == 0)
						sc.scrollController.dropScrollTop();
				});
			}

			// handle onChange event
			BX.bind(sc.inputs.origin, 'change', function(){

				if(sv.eventLock)
					return;

				if(this.value == ''){ // this is a plain reset
					if(sv.value)
						ctx.clearSelected();
				}else{
					ctx.setValue(this.value);
				}
			});
		},

		////////// PUBLIC: free to use outside

		// common

		// todo
		addItems2Cache: function(){
		},

		clearCache: function(){
		},

		// set focus to fake input
		focus: function(){
			this.ctrls.inputs.fake.focus();
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

		// function made to have ability to set value externally: when passed empty string, it resets the whole control, otherwise
		// it searches for the value in the cache. And then, if none is found, sends request to server to obtain missing item
		setValue: function(value, autoSelect){

			var sv = this.vars,
				so = this.opts,
				sc = this.ctrls,
				ctx = this;
			
			this.hideError();

			// clean

			if(value == null || value == false || typeof value == 'undefined' || value.toString().length == 0){ // deselect

				this.resetVariables();

				BX.cleanNode(sc.vars);

				if(BX.type.isElementNode(sc.nothingFound))
					BX.hide(sc.nothingFound);

				this.fireEvent('after-deselect-item');
				this.fireEvent('after-clear-selection');

				return;
			}else if(value == sv.value) // dup
				return;

			if(autoSelect !== false)
				sv.forceSelectSingeOnce = true;

			if(typeof sv.cache.nodes[value] == 'undefined'){

				// lazyload it...
				this.resetNavVariables();

				ctx.downloadBundle({VALUE: value}, function(data){

					ctx.fillCache(data, false); // storing item in the cache

					if(typeof sv.cache.nodes[value] == 'undefined'){ // still not found
						ctx.showNothingFound();
					}else{

						//////////////////
						if(so.autoSelectIfOneVariant || sv.forceSelectSingeOnce)
							ctx.selectItem(value);
						else
							ctx.displayVariants([value]);
						//////////////////
					}
				}, function(){
					sv.forceSelectSingeOnce = false;
				});

			}else{
				if(sv.forceSelectSingeOnce)
					this.selectItem(value);
				else
					this.displayVariants([value]);

				sv.forceSelectSingeOnce = false;
			}
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

		setTabIndex: function(index){
			this.ctrls.inputs.fake.setAttribute('tabindex', index);
		},

		setTargetInputName: function(newName){
			this.ctrls.inputs.origin.setAttribute('name', newName);
		},

		cancelRequest: function(){
			// completely cancel current pending query
			this.vars.forceSelectSingeOnce = false;

			// todo
		},

		// low-level, use with caution

		setTargetInputValue: function(value){
			this.vars.eventLock = true;
			this.ctrls.inputs.origin.value = this.getSelectorValue(value);
			BX.fireEvent(this.ctrls.inputs.origin, 'change');
			this.vars.eventLock = false;
		},

		// function sets value of a fake input
		setFakeInputValue: function(display){

			var sc = this.ctrls;

			BX.data(sc.inputs.fake, 'bx-dc-previous-value', display); // prevent bindDebouncedChange from fire
			sc.inputs.fake.value = display; // update fake input value
		},

		setValueVariable: function(value){
			this.vars.value = value;
		},

		////////// PRIVATE: forbidden to use outside (for compatibility reasons)

		fillCache: function(items, key){

			var sv = this.vars,
				so = this.opts;

			if(!items.length)
				return;

			// first fill items themselves
			for(var k in items)
				if(items.hasOwnProperty(k))
					this.addItem2Cache(items[k]);

			if(typeof key == 'number' && key != 0){

				if(typeof sv.cache.search[key] == 'undefined')
					sv.cache.search[key] = [];

				for(var k in items)
					if(items.hasOwnProperty(k))
						sv.cache.search[key].push(items[k].VALUE);
			}
		},

		addItem2Cache: function(item){
			this.vars.cache.nodes[item.VALUE] = item;
		},

		setInitialValue: function(){

			var initalValue = false;
			if(this.opts.selectedItem !== false)
				initalValue = this.opts.selectedItem;
			else if(this.ctrls.inputs.origin.value.length > 0)
				initalValue = this.ctrls.inputs.origin.value;

			if(initalValue !== false)
				this.setValue(initalValue);
		},

		getCachedPage: function(query, pageNum){

			var so = this.opts,
				sv = this.vars;

			var key = this.getCacheKeyForQuery(query);

			if(typeof sv.cache.search[key] != 'object' || !('length' in sv.cache.search[key]))
				return false;

			var page = sv.cache.search[key].slice((pageNum * so.pageSize), ((pageNum + 1) * so.pageSize));

			if(page.length == 0)
				return false;

			return page;
		},

		displayNextPage: function(){

			var sv = this.vars;

			if(!this.opts.usePagingOnScroll || sv.lastQuery == null)
				return;

			this.displayPage(sv.lastQuery, sv.lastPage + 1);
		},

		displayPage: function(query, pageNum){

			var so = this.opts,
				sv = this.vars,
				sc = this.ctrls,
				ctx = this;

			if(sv.blockingCall && sv.displayPageMutex) // display is locked with the previous call
				return;

			query = this.refineQuery(query);
			var key = this.getCacheKeyForQuery(query);

			if(typeof pageNum == 'undefined')
				pageNum = 0;

			var ceiling = this.manageCeiling(key);
			if(ceiling > 0 && ceiling <= pageNum)
				return; // no more pages, maximum were reached for that query

			//console.dir('lock kb mutex');
			sv.keyboardMutex = true;

			sv.displayPageMutex = true;
			sv.blockingCall = false;

			sv.lastQuery = query;
			sv.lastPage = pageNum;

			if(so.useCache){

				var page = this.getCachedPage(query, pageNum);

				if(page !== false){

					// showing cached page
					//console.dir('From cache');

					//////////////////
					if((so.autoSelectIfOneVariant || sv.forceSelectSingeOnce) && page.length == 1 && sv.lastPage == 0)
						ctx.selectItem(page[0]);
					else
						ctx.displayVariants(page, pageNum);
					//////////////////

					sv.forceSelectSingeOnce =	false;
					sv.displayPageMutex =		false;
					sv.keyboardMutex =			false;

				}else{

					// page not found, download
					//console.dir('Load page');

					ctx.downloadBundle(query, function(data){

						ctx.fillCache(data, key); // storing data in the cache
						page = this.getCachedPage(query, pageNum);

						if(page == false){
							if(pageNum == 0)
								ctx.showNothingFound(); // still not found
							else{
								ctx.manageCeiling(key, pageNum); // maximum reached
							}
						}else{

							//////////////////
							if((so.autoSelectIfOneVariant || sv.forceSelectSingeOnce) && page.length == 1 && sv.lastPage == 0)
								ctx.selectItem(page[0]);
							else
								ctx.displayVariants(page, pageNum);
							//////////////////

							if(!so.paginatedRequest) // we have just downloaded everyting, no further paging needed for
								ctx.manageCeiling(key, Math.ceil(sv.cache.search[key].length / so.pageSize));
						}

					}, function(){
						sv.forceSelectSingeOnce =	false;
						sv.blockingCall =			false;
						sv.displayPageMutex =		false;
						sv.keyboardMutex =			false;
						//console.dir('UNlock kb mutex');
					});
				}

			}else
				throw new Error('Sorry, useCache = false currently is not implemented');
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

			if(typeof key == 'undefined')
				key = this.getCacheKeyForQuery(sv.lastQuery);

			if(typeof sv.cache.ceilings[key] == 'undefined')
				return true;

			return sv.lastPage < sv.cache.ceilings[key];
		},

		resetVariables: function(){
			this.deselectItem();
			this.setFakeInputValue('');
			this.resetNavVariables();
		},

		resetNavVariables: function(){
			this.vars.lastQuery = null;
			this.vars.lastPage = 0;
		},

		// this function sets value of an origin input
		setTargetValue: function(value){

			var so = this.opts,
				sv = this.vars,
				wasSelected = this.vars.value !== false;

			sv.value = value == '' ? false : value;

			this.setTargetInputValue(value);

			if(sv.value)
				this.fireEvent('after-select-item', [sv.value, true]);
			else if(wasSelected)
				this.fireEvent('after-deselect-item');
		},

		blockingCall: function(){
			this.vars.blockingCall = true;
		},

		downloadBundle: function(request, onLoad, onComplete, onError){

			var so = this.opts,
				sv = this.vars,
				sc = this.ctrls,
				ctx = this;

			sv.loader.show();
			BX.ajax({

				url: ctx.opts.source,
				method: 'post',
				dataType: 'json',
				async: true,
				processData: true,
				emulateOnload: true,
				start: true,
				data: BX.merge(ctx.refineRequest(request), ctx.getNavParams()),
				//cache: true,
				onsuccess: function(result){

					//try{

					sv.loader.hide();

					if(result.result){

						result.data = ctx.refineResponce(result.data, request);

						if(typeof result.data == 'undefined')
							result.data = [];

						onLoad.apply(ctx, [result.data]);

					}else
						ctx.showError(ctx.opts.messages.error, result.errors);

					onComplete.call(ctx);

					//}catch(e){console.dir(e);}

					if (sc.popup)
					{
						sc.popup.adjustPosition();
					}

				},
				onfailure: function(e){

					sv.loader.hide();
					ctx.showError(
						so.messages.error,
						false,
						e
					);

					onComplete.call(ctx);
					if(BX.type.isFunction(onError))
						onError.call(ctx);

					if (sc.popup)
					{
						sc.popup.adjustPosition();
					}
				}

			});
		},

		getNavParams: function(){
			return this.opts.paginatedRequest ? {
				PAGE_SIZE: this.opts.pageSize,
				PAGE: this.vars.lastPage
			} : {};
		},

		getSelectorValue: function(value){
			return value;
		},

		getCacheKeyForQuery: function(query){
			var str = '';
			for(var k in query){
				if(query.hasOwnProperty(k)){
					str += k.toString().toLowerCase()+':'+query[k].toString().toLowerCase()+'|';
				}
			}

			return BX.util.hashCode(str);
		},

		// cache depends on the value returned by this function
		refineQuery: function(query){
			return query;
		},

		// this function is called just before request send, i.e. it`s look like a 'query proxy'
		refineRequest: function(query){
			return query;
		},

		// responce 'proxy-back'
		refineResponce: function(responce, request){
			return responce;
		},

		// turn everyting off and clear up selection
		deselectItem: function(){
			var sv = this.vars,
				sc = this.ctrls,
				so = this.opts;

			this.hideError();

			this.whenClearToggle.apply(this, [false]);

			sv.currentGlow = false;
			sv.previousGlow = false;
			sv.displayedIndex = [];
			sc.displayedItems = {};

			// todo: turn off input origin "change" signal processing to avoid collisions
			this.setTargetValue('');
		},

		// invokes when user selects value
		selectItem: function(value){

			var sv = this.vars,
				sc = this.ctrls,
				so = this.opts;

			sv.value = value; // set current [VALUE, DISPLAY] pair id
			sc.inputs.origin.value = sv.cache.nodes[value].VALUE; // update origin input value

			this.setFakeInputValue(this.whenItemSelect.apply(this, [value]));

			this.hideDropdown();

			this.whenClearToggle.apply(this, [true]);

			this.setTargetValue(value);
		},

		toggleGlow: function(){

			var so = this.opts,
				sc = this.ctrls,
				sv = this.vars;

			var item = sc.displayedItems[sv.displayedIndex[sv.currentGlow]];

			if(sv.previousGlow !== false)
				this.whenItemToggle(false, sc.displayedItems[sv.displayedIndex[sv.previousGlow]], sv.previousGlow); // here we can have event triggering instead

			if(sv.currentGlow !== false)
				this.whenItemToggle(true, item, sv.currentGlow); // here we can have event triggering instead
		},

		showDropdown: function(){

			if(this.vars.opened)
				return;

			var flip = !this.vars.opened;
			this.vars.opened = true;

			if(this.vars.lastPage == 0){
				this.whenItemToggle(false, this.ctrls.displayedItems[this.vars.displayedIndex[this.vars.currentGlow]], this.vars.currentGlow);
				this.vars.currentGlow = 0;
				this.toggleGlow();
			}

			BX.show(this.ctrls.pane); // read height
			var paneHeight = BX.height(this.ctrls.pane);
			BX.hide(this.ctrls.pane); // read height end

			var input = this.ctrls.inputs.fake;
			var inputPos = BX.pos(input);

			var spaceUnderItem = BX.scrollTop(window) + BX.height(window) - (Math.ceil(inputPos.top) + inputPos.height);

			this.whenDropdownToggle.apply(this, [true, spaceUnderItem - paneHeight < -20, inputPos.height, flip]);
		},

		hideDropdown: function(){

			this.vars.opened = false;
			this.whenDropdownToggle.apply(this, [false, false, 0, true]);
		},

		clearDisplayedVariants: function(){
			this.hideDropdown();
			this.vars.displayedIndex = [];
		},

		displayVariants: function(items, pageNum){
			var sc = this.ctrls,
				sv = this.vars,
				so = this.opts,
				code = this.sys.code;

			//this.hideDropdown(); // todo: here ...
			this.hideNothingFound(); // and here ...

			if(typeof pageNum == 'undefined' || pageNum == 0){
				BX.cleanNode(sc.vars); // and here "deferred" pattern required, kz hideDropdown() might be async in some implementations

				sv.displayedIndex = [];
				sc.displayedItems = {};
			}

			var base = sv.displayedIndex.length;

			for(var k in items)
			{
				if(!items.hasOwnProperty(k))
					continue;

				if(!items[k])
					continue;

				var domItem = this.whenRenderVariant(items[k], base + parseInt(k))[0];

				BX.data(domItem, 'bx-'+code+'-item-value', items[k]);

				sc.vars.appendChild(domItem);
				this.fireEvent('after-item-append', [domItem]);

				sv.displayedIndex.push(items[k]);
				sc.displayedItems[items[k]] = domItem;
			}
			this.showDropdown();

			this.fireEvent('after-page-display', [sv.cache.nodes, pageNum]);
		},

		showNothingFound: function(){

			BX.cleanNode(this.ctrls.vars);

			if(BX.type.isElementNode(this.ctrls.nothingFound))
				this.whenNothingFoundToggle(true);
			else{ // show message directly in the dropdown

				this.ctrls.vars.appendChild(this.whenRenderNothingFound(this.opts.messages.nothingFound));

				this.showDropdown();
			}

			this.fireEvent('nothing-found'); // this.opts.onNothingFound.apply(this);
		},

		hideNothingFound: function(){
			if(BX.type.isElementNode(this.ctrls.nothingFound))
				this.whenNothingFoundToggle(false);
		},

		// actually, means "hide static error"
		hideError: function(){
			if(BX.type.isElementNode(this.ctrls.errorMessage) && this.vars.allowHideErrors)
				BX.hide(this.ctrls.errorMessage);
		},

		showError: function(errorLabel, messages, sysDesc){

			BX.cleanNode(this.ctrls.vars);

			this.ctrls.vars.appendChild(this.whenRenderError(errorLabel));

			this.showDropdown();

			BX.debug(arguments);
		},

		refineItemDataForTemplate: function(itemData){

			var query = this.vars.lastQuery.QUERY;

			if(BX.type.isNotEmptyString(query)){
				var chunks = [];
				if(this.opts.wrapSeparate)
					chunks = query.split(/\s+/);
				else
					chunks = [query];

				itemData['=display_wrapped'] = BX.util.wrapSubstring(itemData.DISPLAY, chunks, this.opts.wrapTagName, true);
			}else
				itemData['=display_wrapped'] = BX.util.htmlspecialchars(itemData.DISPLAY);

			return itemData;
		},

		getCurrentItem: function(){
			return this.vars.cache.nodes[this.vars.value];
		},

		/* Behaviour functions below */

		whenDisplayVariant: function(itemId){
			return this.vars.cache.nodes[itemId]['DISPLAY'];
		},

		whenItemSelect: function(itemId){ // evaluates when user selects a particular item. should return value which we want in our fake input
			return this.vars.cache.nodes[itemId]['DISPLAY'];
		},

		whenLoaderToggle: function(way){
			BX[way ? 'addClass' : 'removeClass'](this.ctrls.inputs.fake, 'bx-ui-'+this.sys.code+'-loading');
		},

		whenDropdownToggle: function(way, upward, inputHeight, flip){
			if(way)
			{
				if(flip)
				{
					this.whenDecidePaneOrient(upward, inputHeight, flip);
				}
				if (this.opts.usePopup)
				{
					this.ctrls.popup = BX.PopupWindowManager.create(
						'popup-location-search-list',
						this.ctrls.scope.parentNode,
						{
							className: 'bx-sls',
							content: this.ctrls.pane,
							bindOptions: { forceBindPosition: true },
							zIndex: 1020
						}
					);
					this.ctrls.popup.show();
				}
				BX.show(this.ctrls.pane);
			}
			else
			{
				if (this.opts.usePopup && this.ctrls.popup)
				{
					this.ctrls.popup.destroy();
				}
				BX.hide(this.ctrls.pane);
			}

			this.fireEvent('after-popup-toggled', [way]);
		},

		whenDecidePaneOrient: function(upward, inputHeight){

			var sc = this.ctrls;

			if(upward){
				var top = BX.style(sc.pane, 'top');
				if(top != 'auto'){
					BX.data(sc.pane, 'pane-top', top);
				}
				BX.style(sc.pane, 'top', 'auto');

				BX.style(sc.pane, 'bottom', (inputHeight + this.opts.pageUpWardOffset)+'px');
			}else{
				var top = BX.data(sc.pane, 'pane-top');
				if(typeof top != 'undefined')
					BX.style(sc.pane, 'top', top);

				BX.style(sc.pane, 'bottom', 'auto');
			}
		},

		whenItemToggle: function(way, node, itemId){
			BX[way ? 'addClass' : 'removeClass'](node, 'bx-ui-'+this.sys.code+'-variant-active');
		},

		whenClearToggle: function(way){
			BX[way ? 'show' : 'hide'](this.ctrls.clear);
		},

		whenNothingFoundToggle: function(way){
			BX[way ? 'show' : 'hide'](this.ctrls.nothingFound);
		},

		whenRenderVariant: function(itemId, index){

			var itemData = BX.clone(this.vars.cache.nodes[itemId]);
			itemData = this.refineItemDataForTemplate(itemData);
			itemData.index = index;

			this.fireEvent('before-render-variant', [itemData]);

			if(typeof this.tmpls['dropdown-item'] == 'string')
				return this.createNodesByTemplate('dropdown-item', itemData, true);

			return [BX.create('div', {
				props: {
					className: 'bx-ui-'+this.sys.code+'-variant'
				},
				text: this.vars.cache.nodes[itemId]['DISPLAY']
			})];
		},

		whenRenderError: function(message){

			if(typeof this.tmpls['error'] == 'string')
				return this.createNodesByTemplate('error', {message: message}, true)[0];

			return BX.create('div', {
				props: {
					className: 'bx-ui-'+this.sys.code+'-error'
				},
				text: message
			});
		},

		whenRenderNothingFound: function(message){

			if(typeof this.tmpls['nothing-found'] == 'string')
				return this.createNodesByTemplate('nothing-found', {message: message}, true)[0];

			return this.whenRenderError(message);
		}
	});

})();
