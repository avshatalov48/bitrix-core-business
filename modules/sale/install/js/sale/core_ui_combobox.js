if(typeof BX.ui != 'object')
	BX.ui = {};

BX.ui.combobox = function(opts, nf){

	this.parentConstruct(BX.ui.combobox, opts);

	BX.merge(this, {
		opts: { // default options

			pageSize:				5, //20// amount of variants to show

			selectedItem:			false,
			knownItems:				[], // already known items which go directly to the cache

			// behaviour
			//selectOnBlur:			true, // if true, plugin will select first item in variant list when user types tab button on fake input with string being incomplete typed in
			selectByClick:			true, // item can be selected by clicking on it
			chooseUsingArrows:		true, // item can be choosable when user presses arrow up\down while input is in focus
			selectOnEnter:			true, // item can be selected by pressing Enter on it
			openDdByAltArrow:		true, // dropdown can be opened by pressing Alt+ArrowDown when input is in focus
			closeDdByEscape:		true, // dropdown can be closed by pressing Esc when input is in focus

			scrollToVariantOnArrow:	true,
			closePopupOnOuterClick:	true, // if true, popup will be closed when user clicks outside the widget

			messages: {
				nothingFound:		'Sorry, nothing found',
				notSelected:		'-- Not selected',
				error:				'Error occured',
				clearSelection:		'Deselect'
			},

			// magic design-related values
			arrowScrollAdditional:		0,
			pageUpWardOffset:			0,
			wrapTagName:				'span',
			dropdownHConstraint:		0, // limit dropdown height with this value, if greather than zero
			dropdownHConstraintType:	'max-height', // constraint type of dropdown height

			// fx and decorators
			inputDebounceTimeout:	500, // time of reaction on input content change. Should not be too small
			scrollThrottleTimeout:	300, // timeout of reaction on dropdown scroll. Should not be too small
			selectByClickTimeout:	200, // for better fx perception, should not be too large
			startSearchLen:			2, // minimum string length search will start with

			bindEvents: {
				'init': function(){ // after all we do this
					this.setInitialValue();
				}
			}
		},
		vars: { // significant variables
			opened:					false, // whether dropdown is opened or not
			eventLock:				false,
			displayPageMutex:		false,
			keyboardMutex:			false,
			allEventMutex:			false,

			cache: { // item cache
				nodes:				{}, // data index, keeps data for each node ever loaded
				search:				{ // cache for request: map from query string to a set of items in responce. Here pagenavigation can be implemented without a trouble
					origin: false // this is the default item order, "as it came" from options or item discover. On open popup without filtering this "index" is used to output items
				}
			},

			applyFilter:			false,
			filtered:				[], // items currently filtered by the last filter or non-filter tryDisplayPage() call
			lastSource:				false,

			pager:					false, // control that is responsible for lazy page display
			selector:				false, // control that is responsible for handling arrow-up\down item selection

			value:					false, // actually, [VALUE,DISPLAY] pair id

			outSideClickScope:		null
		},
		ctrls: { // links to controls
		},
		sys: {
			code: 'combobox'
		}
	});

	//this.disableInFuncStack('init', BX.some.parent);
	this.handleInitStack(nf, BX.ui.combobox, opts);
};
BX.extend(BX.ui.combobox, BX.ui.widget);

// the following functions can be overrided with inheritance
BX.merge(BX.ui.combobox.prototype, {

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

		// loader
		sv.loader = new BX.ui.loader({
			timeout: 500
		});
		sv.loader.bindEvent('toggle', BX.proxy(this.whenLoaderToggle, ctx));

		if(typeof so.knownItems == 'object')
			this.fillCache(so.knownItems, false);

		this.pushFuncStack('buildUpDOM', BX.ui.combobox);
		this.pushFuncStack('bindEvents', BX.ui.combobox);
	},

	buildUpDOM: function(){
		var so = this.opts,
			sc = this.ctrls,
			sv = this.vars,
			ctx = this,
			code = this.sys.code;

		// add container node
		sc.container = this.getControl('container', true);
		if(!BX.type.isElementNode(sc.container)){
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
			BX.insertAfter(sc.container, sc.inputs.origin);
		}

		// clone input node
		sc.inputs.fake = this.getControl('fake', true);
		if(!BX.type.isElementNode(sc.container)){
			var pseudoInput = BX.clone(sc.inputs.origin);
			pseudoInput.removeAttribute('name'); // make it invisible for form
			BX.adjust(pseudoInput, {
				props: {
					className: 'bx-ui-'+code+'-fake'
				}
			});
			sc.container.appendChild(pseudoInput);
			sc.inputs.fake = pseudoInput;
		}

		BX.hide(sc.inputs.origin);

		if(BX.browser.IsIE8()){
			BX.bind(sc.inputs.fake, 'click', function(e){
				BX.eventCancelBubble(e);
			});
			BX.bind(sc.container, 'click', function(){
				sc.inputs.fake.focus();
			});
		}

		// toggle handle
		sc.toggle = this.getControl('toggle', true);
		if(!BX.type.isElementNode(sc.toggle)){
			sc.toggle = BX.create('div', {
				props: {
					className: 'bx-ui-'+code+'-toggle'
				},
				style: {
					position: 'absolute',
					top: '0px',
					right: '0px'
				}
			});

			sc.container.appendChild(sc.toggle);
		}

		// insert dropdown
		sc.dropdown = this.getControl('dropdown', true);
		if(!BX.type.isElementNode(sc.dropdown)){
			sc.dropdown = BX.create('div', {
				props: {
					className: 'bx-ui-'+code+'-dropdown'
				},
				style: {
					display: 'none',
					position: 'absolute'
				}
			});
			sc.container.appendChild(sc.dropdown);
		}

		if(so.dropdownHConstraint > 0 && so.dropdownHConstraintType != '')
			BX.style(sc.dropdown, so.dropdownHConstraintType, so.dropdownHConstraint+'px');

		// init pager and glow
		sv.pager = new BX.ui.scrollablePager({
			scope: sc.dropdown,
			setTopReachedOnPage: 0,
			eventTimeout: so.scrollThrottleTimeout,
			parent: ctx
		});
		sv.selector = new BX.ui.itemSelectManager();

		// nothing found message
		sc.nothingFound = this.getControl('nothing-found', true);
	},

	bindEvents: function(){

		var sc =	this.ctrls,
			so =	this.opts,
			sv =	this.vars,
			ctx =	this,
			code =	this.sys.code;

		this.bindEventsMouse();
		this.bindEventsKeyboard();

		sv.pager.bindEvent('scroll-to-top', BX.semaphore(function(){

			if(!this.vars.opened)
				return;

			var pageNum = sv.pager.getFreePageNumber(0);

			if(this.checkPageIsOutOfRange(pageNum))
				return;

			ctx.displayPage(pageNum);

		}, this, {limit: 1, dup: 'drop'}));

		sv.pager.bindEvent('scroll-to-bottom', BX.semaphore(function(){

			if(!this.vars.opened)
				return;

			var pageNum = sv.pager.getFreePageNumber(1);

			if(this.checkPageIsOutOfRange(pageNum))
				return;

			ctx.displayPage(pageNum);

		}, this, {limit: 1, dup: 'drop'}));

		sv.selector.bindEvent('item-select', function(id, data){
			ctx.whenToggleItemGlow(data, true);
		});

		sv.selector.bindEvent('item-deselect', function(id, data){
			ctx.whenToggleItemGlow(data, false);
		});
	},

	bindEventsMouse: function(){

		var sc =	this.ctrls,
			so =	this.opts,
			sv =	this.vars,
			ctx =	this,
			code =	this.sys.code;

		if(so.selectByClick){

			BX.bindDelegate(sc.dropdown, 'click', {
				className: 'bx-ui-'+code+'-variant'
			}, function(){

				if(sv.allEventMutex) return; // semaphore later here

				var id = BX.data(this, 'bx-'+code+'-item-value');
				var node = this;

				if(id == ctx.vars.value){
					ctx.hideDropdown();
					return;
				}

				if(typeof id != 'undefined' && typeof sv.cache.nodes[id] != 'undefined'){
					ctx.vars.selector.selectById(id); // aware selector of which id is currently selected

					ctx.setValue(id);
					if(so.focusOnMouseSelect)
						sc.inputs.fake.focus();

					ctx.fireEvent('item-selected-by-mouse', [id, node]);
				}else
					ctx.setValue('');
			});
		}

		// outside click should close the dropdown
		if(so.closePopupOnOuterClick){

			if(sv.allEventMutex) return; // semaphore later here

			sv.outSideClickScope = sc.container;

			BX.bind(document, 'click', function(e){
				e = e || window.event;

				if(!BX.isParentForNode(sv.outSideClickScope, e.target || e.srcElement)){
					ctx.hideDropdown();
				}
			});
		}

		// toggle handle
		BX.bind(sc.toggle, 'click', function(){
			ctx.toggleDropDown();
		});

		if('value' in sc.inputs.fake)
		{
			// when nothing were selected (but there were already an attempt of search), open dropdown if it was closed occasionly by user
			BX.bind(sc.inputs.fake, 'click', function(){
				if(!sv.opened && sv.value === false && this.value.length > 0)
					ctx.tryDisplayPage('search');
			});
		}
	},

	bindEventsKeyboard: function(){

		var sc =	this.ctrls,
			so =	this.opts,
			sv =	this.vars,
			ctx =	this;

		if('value' in sc.inputs.fake){ // check if it is an input at least

			// bind debounced key-type input change
			BX.bindDebouncedChange(sc.inputs.fake,
				function(val){

					if(sv.allEventMutex) return; // semaphore later here

					if(val.length >= so.startSearchLen){

						ctx.tryDisplayPage('search');

					}else
						ctx.hideDropdown();
				},
				function(){

					if(sv.allEventMutex) return; // semaphore later here

					if(sv.value != false && sv.value != '')
						ctx.deselectItem();
				},
				so.inputDebounceTimeout,
				sc.inputs.fake
			);

			BX.bind(sc.inputs.fake, 'keydown', function(e){

				if(sv.allEventMutex) return; // semaphore later here
				if(sv.keyboardMutex) // kb mutex is on, ignore keyboard type in
					return;

				var key = e.keyCode || e.which;
				var arrowsPressed = (key == 38 || key == 40); // up (38) - down (40)

				if(so.chooseUsingArrows){

					if(arrowsPressed && sv.opened){

						sv.selector[key == 38 ? 'selectPrevious' : 'selectNext'](e.shiftKey ? 5 : 1);

						if(so.scrollToVariantOnArrow){

							var selected = sv.selector.getSelected();

							if(typeof selected != 'undefined'){

								var item = selected.data.node;

								// here we determine if currently selected item is not in the visible area
								var pos = BX.pos(item, sc.dropdown);

								var a = pos.top;
								var b = pos.height;
								var c = sc.dropdown.clientHeight;
								var d = sc.dropdown.scrollTop;

								var f = so.arrowScrollAdditional;

								var scrollTo = false;
								if(a + b > c + d)
									scrollTo = a + b - (c + d) + f;
								else if(a < d)
									scrollTo = -(d - a + f);

								if(scrollTo != false)
									sv.pager.scrollTo(scrollTo, false, 1);
							}
						}

						BX.PreventDefault(e);
					}
				}

				// dropdown open by alt+key up/down
				if(so.openDdByAltArrow && e.altKey && arrowsPressed && !sv.opened)
					ctx.tryDisplayPage('toggle');

				if(so.closeDdByEscape && key == 27 && sv.opened)
					ctx.hideDropdown();

				// on enter and tab we perform item selection
				if(key == 13 && so.selectOnEnter && sv.opened){
					var node = sv.selector.getSelected();

					if(typeof node != 'undefined'){

						if(node.id == ctx.vars.value){
							ctx.hideDropdown();
							return;
						}

						ctx.setValue(node.id);
					}
				}

				/*
				// on tab dropdown close and optional select
				if(key == 9 && sv.opened && so.selectOnBlur){

					if(displayedLen > 0)
						ctx.selectItem(sv.displayedIndex[0]);
					else
						ctx.hideDropdown();
				}
				*/

				if(key == 13)
					BX.PreventDefault(e);
			});
		}
	},

	////////// PUBLIC: free to use outside

	// common

	addItems2Cache: function(items){
		this.fillCache(items);
	},

	clearCache: function(){
		this.vars.cache = {nodes: {}, search: {origin: false}};
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

	setValue: function(value){

		this.hideDropdown();
		this.hideNothingFound();

		this.setFakeInputValue('');

		if(value == null || typeof value == 'undefined' || value.toString().length == 0){ // deselect
			this.deselectItem();
			return;
		}else if(value == this.vars.value) // dup
			return;
		else // set
			this.setCurrentValue('');

		var sv =	this.vars,
			sc =	this.ctrls,
			ctx =	this;

		// todo: here we require another semaphore to ensure noboy calls setValue twice while discover is in progress

		this.discoverItems(function(){

			if(typeof sv.cache.nodes[value] == 'undefined') // still not found
				ctx.displayNothingFound();
			else
				ctx.selectItem(value);
		});
	},

	getValue: function(){
		return this.vars.value;
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
	},

	// low-level, use with caution

	setTargetInputValue: function(value){
		this.vars.eventLock = true;
		this.ctrls.inputs.origin.value = value;
		BX.fireEvent(this.ctrls.inputs.origin, 'change');
		this.vars.eventLock = false;
	},

	setFakeInputValue: function(display){

		var sc = this.ctrls;

		if('value' in sc.inputs.fake){
			BX.data(sc.inputs.fake, 'bx-dc-previous-value', display); // prevent bindDebouncedChange from fire
			sc.inputs.fake.value = display;
		}else{
			if(display == '')
				display = this.opts.messages.notSelected;

			sc.inputs.fake.innerHTML = BX.util.htmlspecialchars(display);
		}
	},

	setValueVariable: function(value){
		this.vars.value = value;
	},

	// specific

	// fill current item cache with values
	fillCache: function(items, parameters){

		var sv = this.vars;

		if(!items.length)
			return;

		if(!BX.type.isPlainObject(parameters))
			parameters = {};

		parameters.modifyOrigin =			parameters.modifyOrigin || sv.cache.search.origin === false;
		var arrayOperation =				parameters.modifyOriginPosition == 'prepend' ? 'unshift' : 'push';

		if(sv.cache.search.origin === false)
			sv.cache.search.origin = [];

		// first fill items themselves
		for(var k in items)
		{
			if(!items.hasOwnProperty(k))
				continue;

			if(parameters.modifyOrigin)
			{
				sv.cache.search.origin[arrayOperation](items[k].VALUE);
			}
			sv.cache.nodes[items[k].VALUE] = items[k];
		}

		this.fireEvent('after-cache-filled', []);
	},

	getLastSource: function(){
		return this.vars.lastSource;
	},

	toggleDropDown: function(){
		if(this.vars.allEventMutex) return; // semaphore later here

		if(this.vars.opened)
			this.hideDropdown();
		else
			this.tryDisplayPage('toggle');
	},

	// lately should appear as an item in remove stack
	remove: function(){
		// drop scope
		if(BX.type.isDomNode(this.ctrls.scope))
			this.ctrls.scope.innerHTML = '';

		// ubind custom events
		BX.unbindAll(this);

		if(this.vars.pager != null){
			this.vars.pager.remove();
			this.vars.pager = null;
		}

		if(this.vars.selector != null){
			this.vars.selector.remove();
			this.vars.selector = null;
		}
	},

	////////// PRIVATE: forbidden to use outside (for compatibility reasons)

	getLastPageIndex: function(){
		return Math.ceil(this.vars.filtered.length / this.opts.pageSize) - 1;
	},

	checkPageIsFirst: function(pageNum){
		return pageNum == 0;
	},

	checkPageIsLast: function(pageNum){
		return pageNum == this.getLastPageIndex();
	},

	checkPageIsOutOfRange: function(pageNum){
		return pageNum < 0 || pageNum > this.getLastPageIndex();
	},

	setInitialValue: function(){

		var initalValue = false;
		if(this.opts.selectedItem !== false)
			initalValue = this.opts.selectedItem;
		else if(this.ctrls.inputs.origin.value.length > 0)
			initalValue = this.ctrls.inputs.origin.value;

		if(initalValue !== false && typeof initalValue != 'undefined')
			this.setValue(initalValue);
	},

	discoverItems: function(callback){

		var sv =	this.vars,
			ctx =	this;

		var discover = new BX.deferred();

		discover.done(function(params){

			if(typeof params != 'undefined')
				ctx.fillCache(params.items);

			sv.loader.hide();
			sv.allEventMutex = false; // semaphore here later

			callback.call(this);
		});

		discover.fail(function(){
			sv.loader.hide();
			sv.allEventMutex = false; // semaphore here later
			ctx.displayNothingFound();
		});

		if(BX.util.getObjectLength(sv.cache.nodes) == 0){

			sv.loader.show();

			sv.allEventMutex = true; // semaphore here later
			discover.startRace(1000, false);

			this.fireEvent('item-list-discover', [discover]);
		}else
			discover.resolve();
	},

	tryDisplayPage: function(source){

		var sv =	this.vars,
			ctx =	this,
			query =	this.ctrls.inputs.fake.value;

		sv.applyFilter = (source == 'search' && BX.type.isNotEmptyString(query));
		sv.lastSource = source;

		this.discoverItems(function(){

			ctx.fireEvent('before-display-page', []);

			// page number to be displayed with
			var pageNum = sv.applyFilter ? 0 : ctx.getPageNumberOfSelected();
			sv.filtered = [];

			if(sv.applyFilter){
				var queryLc = query.toLowerCase();

				for(var k in sv.cache.search.origin)
				{
					if(!sv.cache.search.origin.hasOwnProperty(k))
						continue;

					var value = sv.cache.search.origin[k];

					if(sv.cache.nodes[value].DISPLAY.toLowerCase().indexOf(queryLc) == 0){ // match, but only from the start of line
						sv.filtered.push(value);
					}
				}
			}else
				sv.filtered = sv.cache.search.origin;

			if(sv.filtered == false)
				sv.filtered = [];

			sv.pager.cleanUp();
			sv.selector.cleanUp();

			ctx.displayPage(pageNum);
		});
	},

	displayPage: function(pageNum){

		var page = this.getPage(pageNum);

		if(page.length == 0)
			this.displayNothingFound();
		else{
			this.displayVariants(page, pageNum);
		}
	},

	getPage: function(pageNum){

		var sv =		this.vars,
			so =		this.opts;

		return sv.filtered.slice((pageNum * so.pageSize), ((pageNum + 1) * so.pageSize));
	},

	getPageNumberOfSelected: function(){

		if(this.vars.value == false)
			return 0;

		var sv = this.vars;

		var pos = 0;
		for(var k in sv.cache.search.origin)
		{
			if(!sv.cache.search.origin.hasOwnProperty(k))
				continue;

			if(sv.cache.search.origin[k] == this.vars.value)
				break;

			pos++;
		}

		if(pos > sv.cache.search.origin.length)
			return 0; // item not found

		return Math.floor(pos / this.opts.pageSize);
	},

	displayNothingFound: function(){

		BX.cleanNode(this.ctrls.vars);

		if(BX.type.isElementNode(this.ctrls.nothingFound))
			this.whenNothingFoundToggle(true);
		else{ // show message directly in the dropdown

			var pager = this.vars.pager;

			pager.cleanUp();
			this.vars.selector.cleanUp();

			pager.setTopReached();
			pager.setBottomReached();
			pager.appendPage(this.whenRenderNothingFound(this.opts.messages.nothingFound));

			this.showDropdown();
		}

		this.fireEvent('nothing-found');
	},

	displayVariants: function(page, pageNum){

		var sc = this.ctrls,
			sv = this.vars,
			so = this.opts,
			code = this.sys.code;

		this.hideNothingFound();

		var pagerRows = [];
		var selectorRows = [];

		// special option "deselect"
		if(sv.lastSource == 'toggle' && pageNum == 0 && sv.value !== false){
			var domItem = this.createItemForPage('', {DISPLAY: so.messages.clearSelection}, pagerRows, selectorRows);
			BX.addClass(domItem, 'bx-ui-'+code+'-deselect-item');
		}

		var id2dom = {};
		for(var k in page)
		{
			if(!page.hasOwnProperty(k))
				continue;

			this.createItemForPage(page[k], sv.cache.nodes[page[k]], pagerRows, selectorRows);
			id2dom[sv.cache.nodes[page[k]].VALUE] = pagerRows[k];
		}

		this.fireEvent('after-page-built', [pageNum, pagerRows, selectorRows]);

		this.showDropdown();

		sv.selector.addPage(selectorRows, pageNum);

		if(this.checkPageIsLast(pageNum))
			sv.pager.setBottomReached();

		var prevCnt = this.vars.pager.getPageCount();

		sv.pager.lockScrollEvents();
		sv.pager.addPage(pagerRows, pageNum);

		if(prevCnt == 0){
			var selected = false;
			if(sv.value !== false){
				this.vars.selector.selectById(sv.value);
				selected = sv.value;
			}else{
				this.vars.selector.selectFirst();
				selected = this.vars.selector.getSelected().id;
			}

			// here we must additionally scroll page to the selected node
			if(selected !== false && typeof id2dom[selected] != 'undefined'){
				this.vars.pager.scrollToNode(id2dom[selected]);
			}
		}

		sv.pager.unLockScrollEvents();
		sv.pager.dispatchScrollEvents();

		this.fireEvent('after-page-display', [sv.cache.nodes, pageNum]);
	},

	createItemForPage: function(itemId, itemData, pagerRows, selectorRows, prepend){
		var domItem = this.whenRenderVariant(itemData)[0];

		BX.data(domItem, 'bx-'+this.sys.code+'-item-value', itemId);
		this.fireEvent('after-item-append', [domItem]);

		pagerRows[prepend ? 'unshift' : 'push'](domItem);
		selectorRows[prepend ? 'unshift' : 'push']({id: itemId, data: {node: domItem}});

		return domItem;
	},

	hideNothingFound: function(){
		if(BX.type.isElementNode(this.ctrls.nothingFound))
			this.whenNothingFoundToggle(false);
	},

	showDropdown: function(){

		if(this.vars.opened)
			return;

		if(this.vars.opened)
			return;

		var flip = !this.vars.opened;
		this.vars.opened = true;

		BX.show(this.ctrls.dropdown); // read height
		var dropdownHeight = BX.height(this.ctrls.dropdown);
		BX.hide(this.ctrls.dropdown); // read height end

		var input = this.ctrls.inputs.fake;
		var inputPos = BX.pos(input);

		var spaceUnderItem = BX.scrollTop(window) + BX.height(window) - (Math.ceil(inputPos.top) + inputPos.height);

		this.whenDropdownToggle.apply(this, [true, spaceUnderItem - dropdownHeight < -20, inputPos.height, flip]);
	},

	hideDropdown: function(){
		this.vars.opened = false;
		this.whenDropdownToggle.apply(this, [false, false, 0, true]);
	},

	// turn everyting off and clear up selection
	deselectItem: function(){
		var sv = this.vars,
			sc = this.ctrls,
			so = this.opts;

		this.setCurrentValue('');

		this.fireEvent('after-deselect-item');
	},

	// invokes when user selects value
	selectItem: function(value){

		var sv = this.vars,
			sc = this.ctrls,
			so = this.opts;

		sv.value = value; // set current [VALUE, DISPLAY] pair id
		sc.inputs.origin.value = sv.cache.nodes[value].VALUE; // update origin input value

		this.setFakeInputValue(this.whenItemSelect.apply(this, [value]));
		this.setCurrentValue(value);

		this.hideDropdown();

		this.fireEvent('after-select-item', [sv.value]);
	},

	// this function sets value of an origin input
	setCurrentValue: function(value){

		var so = this.opts,
			sv = this.vars;

		sv.value = value == '' ? false : value;

		this.setTargetInputValue(value);
	},

	// when* functions stand for behaviour and meant to be overrided with inheritance

	// evaluates when user selects a particular item. should return value which we want in our fake input
	whenItemSelect: function(itemId){
		return this.vars.cache.nodes[itemId]['DISPLAY'];
	},

	whenToggleItemGlow: function(itemData, way){
		BX[way ? 'addClass' : 'removeClass'](itemData.node, 'bx-ui-'+this.sys.code+'-variant-active');
	},

	whenDropdownToggle: function(way, upward, inputHeight, flip){

		if(way){
			if(flip)
				this.whenDecideDropdownOrient(upward, inputHeight, flip);
			BX.show(this.ctrls.dropdown);
		}else
			BX.hide(this.ctrls.dropdown);

		this.fireEvent('after-popup-toggled', [way]);
	},

	whenDecideDropdownOrient: function(upward, inputHeight){

		var sc = this.ctrls;

		if(upward){
			var top = BX.style(sc.dropdown, 'top');
			if(top != 'auto'){
				BX.data(sc.dropdown, 'pane-top', top);
			}
			BX.style(sc.dropdown, 'top', 'auto');

			BX.style(sc.dropdown, 'bottom', (inputHeight + this.opts.pageUpWardOffset)+'px');
		}else{
			var top = BX.data(sc.dropdown, 'pane-top');
			if(typeof top != 'undefined')
				BX.style(sc.dropdown, 'top', top);

			BX.style(sc.dropdown, 'bottom', 'auto');
		}
	},

	whenNothingFoundToggle: function(way){
		BX[way ? 'show' : 'hide'](this.ctrls.nothingFound);
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
	},

	whenLoaderToggle: function(way){
		this[way ? 'setCSSState' : 'dropCSSState']('items-discover-in-progress');
	},

	whenRenderVariant: function(itemData){

		this.fireEvent('before-render-variant', [itemData]);

		if(typeof this.tmpls['dropdown-item'] == 'string')
			return this.createNodesByTemplate('dropdown-item', itemData, true);

		return [BX.create('div', {
			props: {
				className: 'bx-ui-'+this.sys.code+'-variant'
			},
			text: itemData.DISPLAY
		})];
	}

});

BX.ui.itemSelectManager = function(){

	////////// PUBLIC: free to use outside

	this.selectFirst = function(){
		if(this.head != null)
			this.selectById(this.head.id);
	};
	this.selectNext = function(distance){
		this.jumpToDistance(true, distance);
	};
	this.selectPrevious = function(distance){
		this.jumpToDistance(false, distance);
	};
	this.selectById = function(id){

		var item = this.data[id];

		if(typeof item == 'undefined')
			return;

		if(this.current != null)
			this.fireEvent('item-deselect', [this.current.id, this.current.data]);

		this.fireEvent('item-select', [id, item.data]);
		this.current = item;
	};
	this.getSelected = function(){

		if(this.current == null)
			return undefined;

		return {id: this.current.id, data: this.current.data};
	};
	this.addPage = function(data, pageNum){

		if(this.range == false){

			this.appendData(data);
			this.range = [pageNum, pageNum];

		}else if(pageNum > this.range[1]){

			this.appendData(data);
			this.range[1]++;

		}else if(pageNum < this.range[0]){

			this.prependData(data);
			this.range[0]--;
		}
	};

	this.cleanUp = function(){
		this.data = {};
		this.head = null;
		this.tail = null;
		this.current = null;
		this.range = false;
	};

	this.remove = function(){
		this.cleanUp();
	};

	this.fireEvent = function(eventName, args, scope){
		scope = scope || this;
		args = args || [];

		BX.onCustomEvent(scope, 'bx-ui-item-select-manager-'+eventName, args);
	},

	this.bindEvent = function(eventName, callback){
		BX.addCustomEvent(this, 'bx-ui-item-select-manager-'+eventName, callback);
	},

	////////// PRIVATE: forbidden to use outside (for compatibility reasons)

	this.jumpToDistance = function(way, distance){

		if(this.current != null){

			if(!distance)
				distance = 1;

			var node = this.current;
			for(var k = 0; k < distance; k++){
				if(node[way ? 'next' : 'previous'] == null)
					break;
				node = node[way ? 'next' : 'previous'];
			}

			if(node != null)
				this.selectById(node.id);
		}
	}

	this.appendData = function(data){
		for(var k = 0; k < data.length; k++)
			this.append(data[k]);
	};

	this.prependData = function(data){
		for(var k = data.length - 1; k >= 0; k--)
			this.prepend(data[k]);
	};

	this.append = function(item){
		var tail = this.tail;

		this.data[item.id] = {id: item.id, data: item.data, next: null, previous: tail};

		if(this.tail != null)
			this.tail.next = this.data[item.id];

		if(this.head == null)
			this.head = this.data[item.id];

		this.tail = this.data[item.id];

		if(this.current == null)
			this.current = this.data[item.id];
	};

	this.prepend = function(item){
		var head = this.head;

		this.data[item.id] = {id: item.id, data: item.data, next: head, previous: null};

		if(this.head != null)
			this.head.previous = this.data[item.id];

		if(this.tail == null)
			this.tail = this.data[item.id];

		this.head = this.data[item.id];

		if(this.current == null)
			this.current = this.data[item.id];
	};

	this.cleanUp();

	return this;
}