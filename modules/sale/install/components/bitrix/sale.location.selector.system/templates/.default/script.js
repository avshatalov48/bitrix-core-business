BX.namespace('BX.Sale.component.location.selector');

if(typeof BX.Sale.component.location.selector.system == 'undefined' && typeof BX.ui != 'undefined' && typeof BX.ui.widget != 'undefined'){

	BX.Sale.component.location.selector.system = function(opts, nf){

		this.parentConstruct(BX.Sale.component.location.selector.system, opts);

		BX.merge(this, {
			opts: {

				editUrl: 				'',
				pageSize: 				10,

				hugeTailLen: 			30,

				// turn every action of autocomplete popup off
				selectOnBlur: 			false,
				selectOnEnter: 			false,
				autoSelectIfOneVariant: false,
				selectByClick: 			false,
				closePopupOnOuterClick: false,
				chooseUsingArrows: 		false,

				usePagingOnScroll: 		true,
				paginatedRequest: 		true,

				callback: 				BX.DoNothing
			},
			vars: {

				// common cache
				cache: {
					nodes: 				{},
					grp: 				{},
					path: 				{}
				},

				// main form mode
				selected: {
					nodes: 				[],
					grp: 				[]
				},

				selectedNodesShowOffset: 0, // how many nodes we now see at the right panel
				selectedParentNode: 	false,
				selectedParentType: 	false,
				expectChooseAll: 		false,

				spMutex: 		false,

				parent: 				null,
				child: 					null
			},
			sys: {
				code: 					'slss'
			}
		});

		this.handleInitStack(nf, BX.Sale.component.location.selector.system, opts);
	}
	BX.extend(BX.Sale.component.location.selector.system, BX.ui.autoComplete);
	BX.merge(BX.Sale.component.location.selector.system.prototype, {

		// member of stack of initializers, must be defined even if do nothing
		init: function(){

			var sc = this.ctrls,
				so = this.opts,
				sv = this.vars,
				ctx = this;

			if(typeof so.connected == 'object'){

				// fill selected
				for(var k in so.connected.id.l)
				{
					if(so.connected.id.l.hasOwnProperty(k))
					{
						sv.selected.nodes.push({
							id: so.connected.id.l[k],
							view: null
						});
					}
				}

				for(var k in so.connected.id.g)
				{
					if(so.connected.id.g.hasOwnProperty(k))
					{
						sv.selected.grp.push({
							id: so.connected.id.g[k],
							view: null
						});
					}
				}

				// fill cache
				sv.cache.nodes = so.connected.data.l;
				sv.cache.grp = so.connected.data.g;
				sv.cache.path = so.connected.data.p;
			}
			delete(so.connected);

			sv.parent = this; // spike!
			sv.child = this; // spike!

			// link cache
			sv.cache.nodes = this.refineItems(sv.parent.vars.cache.nodes);
			sv.cache.grp = sv.parent.vars.cache.grp;
			sv.cache.path = sv.parent.vars.cache.path;

			// get selected to local
			sv.selected = BX.clone(sv.parent.vars.selected);

			// get some controls

			// right part
			sc.selectedNodes = this.getControl('selected-locations');
			sc.selectedGroups = this.getControl('selected-groups');

			sc.selectedGroupsSeparator = this.getControl('selected-separator');
			sc.selectedNothing = this.getControl('nothing-selected');

			// left part
			sc.grpSelContainer = this.getControl('selector-groups');
			sc.locTreeSelContainer = this.getControl('selector-locations-tree');
			sc.locSelContainer = this.getControl('selector-locations');

			// filter
			sc.selectPrompt = this.getControl('select-prompt');
			sc.typeSelector = this.getControl('type');

			// table headers
			sc.chooseAll = this.getControl('choose-all');
			sc.chooseAllSelected = this.getControl('choose-all-selected');

			// counters
			sc.selectedNodesCntr = this.getControl('selected-node-counter');
			sc.selectedGroupsCntr = this.getControl('selected-group-counter', true);

			sc.inputPool = this.getControl('input-pool');

			sv.tree = new BX.Sale.component.location.selector.system.tree({
				scope: ctx.getControl('selector-locations-tree'),
				source: so.source,
				langId: typeof this.opts.query.BEHAVIOUR.LANGUAGE_ID != 'undefined' ? this.opts.query.BEHAVIOUR.LANGUAGE_ID : false
			});

			this.pushFuncStack('buildUpDOM', BX.Sale.component.location.selector.system);
			this.pushFuncStack('bindEvents', BX.Sale.component.location.selector.system);
		},

		buildUpDOM: function(){

			var sc = this.ctrls,
				so = this.opts,
				sv = this.vars,
				ctx = this;

			sc.container.style.width = '100%';
			sc.inputs.fake.style.width = '100%';

			this.displaySelectedForm();
		},

		bindEvents: function(){

			var sc = this.ctrls,
				so = this.opts,
				sv = this.vars,
				ctx = this;

			BX.bindDelegate(sc.grpSelContainer, 'click', {tagName: 'a'}, function(e){

				var gId = BX.data(this, 'item-id');
				if(typeof gId != 'undefined'){

					ctx.resetVariables();
					
					ctx.toggleCheckBoxes(sc.vars, false);
					sc.chooseAll.checked = false;

					BX.hide(ctx.ctrls.nothingFound);

					sv.selectedParentNode = gId;
					sv.selectedParentType = 'grp';

					sc.typeSelector.value = '';
					ctx.blockingCall();
					ctx.displayPage({GROUP_ID: gId});
				}

				BX.PreventDefault(e);
			});

			BX.bind(sc.typeSelector, 'change', function(){

				if(typeof ctx.vars.lastQuery != 'undefined' && ctx.vars.lastQuery != null && typeof ctx.vars.lastQuery.QUERY != 'undefined')
					ctx.displayPage(ctx.vars.lastQuery);
			});

			BX.bind(this.getControl('select'), 'click', function(){
				ctx.selectChecked();
			});

			BX.bind(this.getControl('deselect'), 'click', function(){
				ctx.deSelectChecked();
			});

			BX.bind(sc.chooseAll, 'click', function(){
				ctx.toggleCheckBoxes(sc.vars, this.checked);
			});

			BX.bind(sc.chooseAllSelected, 'click', function(){
				ctx.toggleCheckBoxes(sc.selectedNodes, this.checked);
				ctx.toggleCheckBoxes(sc.selectedGroups, this.checked);
			});

			BX.bind(this.getControl('selected-act-clean'), 'click', function(){
				if(confirm(so.messages.sureCleanSelected))
					ctx.clearChoosen();
			});

			this.bindEvent('nothing-found', function(){
				BX.hide(sc.selectPrompt);
			});

			this.bindEvent('after-clear-selection', function(){
				BX.show(sc.selectPrompt);
				sc.typeSelector.value = '';
				sc.chooseAll.checked = false;
			});

			this.bindEvent('before-input-value-modify', function(){
				sv.selectedParentNode = false;
			});

			this.bindEvent('after-item-append', function(node){
				node.querySelector('input[type="checkbox"]').checked = sc.chooseAll.checked;
			});

			// item tree
			BX.bindDelegate(sc.locTreeSelContainer, 'click', {className: 'bx-ui-slss-selector-show-bundle'}, function(){

				var parent = 0;

				var itemId = BX.data(this, 'node-id');
				if(typeof itemId != 'undefined')
					parent = parseInt(itemId);

				ctx.resetVariables();
				
				//ctx.toggleCheckBoxes(sc.vars, false);
				//sc.chooseAll.checked = false;

				BX.hide(ctx.ctrls.nothingFound);

				sc.typeSelector.value = '';
				ctx.blockingCall();
				ctx.displayPage({PARENT_ID: parent});

				sv.selectedParentNode = parent;
				sv.selectedParentType = 'nodes';

				sv.expectChooseAll = true;
			});

			// right scroll panel events

			var selectedPane = this.getControl('selected-pane');

			sc.scrollControllerSelected = new BX.ui.scrollPaneNative({
				scope: selectedPane,
				controls: {
					'container': selectedPane
				}
			});

			sv.addPageSelected = BX.debounce(function(){
				ctx.showSelectedNodePage();
			}, 10);

			sc.scrollControllerSelected.bindEvent('scroll-to-end', sv.addPageSelected);
			sc.scrollControllerSelected.bindEvent('has-free-space', sv.addPageSelected);

			// both scroll panels events
			/*
			BX.addCustomEvent(sv.parent.vars.popup, 'onWindowResize', function(){
				sc.scrollController.checkScrollState(); // left
				sc.scrollControllerSelected.checkScrollState(); // right
			});
			*/

			// set initial

			BX.show(sc.clear);

			this.showSelectedGroups();
			sv.addPageSelected();
			this.toggleSelectionAuxCtrls();
		},

		whenRenderError: function(message){
			return this.createNodesByTemplate('error', {message: message}, true)[0];
		},

		whenDropdownToggle: function(way){

			if(way){
				BX.hide(this.ctrls.selectPrompt);
				BX.show(this.ctrls.pane);
			}else{
				this.hideNothingFound();
				BX.cleanNode(this.ctrls.vars);
				BX.show(this.ctrls.selectPrompt);
			};
		},

		whenClearToggle: function(){
			BX.show(this.ctrls.clear);
		},

		/////////////////
		// about quering

		refineQuery: function(request){
			var type = this.ctrls.typeSelector.value;
			if(type != '')
				request['TYPE_ID'] = type;
			else
				delete(request['TYPE_ID']);

			return request;
		},

		refineRequest: function(request){

			var filter = {};
			var additionals = {
				'1': 'PATH'
			};
			
			if(typeof request['QUERY'] != 'undefined')
				filter['=PHRASE'] = request.QUERY;

			if(typeof request['TYPE_ID'] != 'undefined')
				filter['=TYPE_ID'] = request.TYPE_ID;

			if(typeof request['PARENT_ID'] != 'undefined'){
				filter['=PARENT_ID'] = request.PARENT_ID;
				additionals['2'] = 'PARENT_ITEM'; // this to add parent item to the selection
			}

			if(typeof request['GROUP_ID'] != 'undefined')
				filter['=GROUPLOCATION.LOCATION_GROUP_ID'] = request.GROUP_ID;

			if(typeof this.opts.query.BEHAVIOUR.LANGUAGE_ID != 'undefined')
				filter['=NAME.LANGUAGE_ID'] = this.opts.query.BEHAVIOUR.LANGUAGE_ID;

			return {
				'select': {
					'VALUE': 'ID',
					'DISPLAY': 'NAME.NAME',
					'1': 'CODE',
					'2': 'TYPE_ID'
				},
				'additionals': additionals,
				'filter': filter
			};
		},

		refineResponce: function(responce, request){

			if(typeof responce.ETC.PATH_ITEMS != 'undefined')
			{
				for(var k in responce.ETC.PATH_ITEMS)
				{
					if(responce.ETC.PATH_ITEMS.hasOwnProperty(k))
						if(BX.type.isNotEmptyString(responce.ETC.PATH_ITEMS[k].DISPLAY))
							this.vars.cache.path[k] = responce.ETC.PATH_ITEMS[k].DISPLAY;
				}
			}

			if(typeof responce.ETC.PARENT_ITEM != 'undefined'){

				var parent = this.refineItems([responce.ETC.PARENT_ITEM]);

				this.vars.cache.nodes[parent[0].VALUE] = parent[0];

				BX.merge(this.vars.cache.path, responce.ETC.PATH_NAMES);
			}

			return this.refineItems(responce.ITEMS);
		},

		refineItems: function(items){
			return items;
		},

		refineItemDataForTemplate: function(itemData){

			itemData['random_value'] = this.getRandom();

			if(typeof itemData['PATH'] == 'object' && itemData['PATH'].length > 0){

				var path = [];
				for(var i = 0; i < itemData['PATH'].length; i++)
					path.push(this.vars.cache.path[itemData['PATH'][i]]);

				itemData['PATH'] = path.join(', ');
			}else
				itemData['PATH'] = '';

			itemData['TYPE'] = typeof itemData.TYPE_ID != 'undefined' ? this.opts.types[parseInt(itemData.TYPE_ID)]['NAME'].toLowerCase() : '';

			return itemData;
		},

		/////////////////
		// about selection

		selectChecked: function(){

			var sv = this.vars,
				sc = this.ctrls;

			var result = {nodes: [], grp: []};

			var selectedAll = sc.chooseAll.checked;
			var all = sv.cache.search[this.getCacheKeyForQuery(sv.lastQuery)];
			var cbItemList = this.readCheckboxItems(this.ctrls.locSelContainer);

			if(selectedAll){ // "select all" checkbox checked

				if(cbItemList.off.length > 0){ // smth were unchecked

					// select all but unchecked
					for(var i = 0; i < all.length; i++){
						if(!BX.util.in_array(+all[i], cbItemList.off))
							result.nodes.push(all[i]);
					}

				}else{
					if(sv.selectedParentNode !== false && parseInt(sv.selectedParentNode) != 0)
						result[sv.selectedParentType].push(sv.selectedParentNode);
					else
						result.nodes = all;
				}

			}else{ // "select all" checkbox unchecked
				result.nodes = cbItemList.on; // just get what is checked in the list
			}

			// add checked groups
			result.grp = BX.util.array_merge(result.grp, this.readCheckboxItems(this.ctrls.grpSelContainer).on);

			sc.chooseAll.checked = false;

			this.selectItems(result);
		},

		readCheckboxItems: function(scope){
			var result = {on: [], off: []};

			checkboxes = scope.querySelectorAll('input[type="checkbox"]');
			for(var i = 0; i < checkboxes.length; i++){
				result[checkboxes[i].checked ? 'on' : 'off'].push(+checkboxes[i].value);
				checkboxes[i].checked = false;
			}

			return result;
		},

		deSelectChecked: function(){

			var sc = this.ctrls,
				sv = this.vars;

			var result = {
				nodes: [],
				grp: this.readCheckboxItems(this.ctrls.selectedGroups).on
			};

			var cbItemList = this.readCheckboxItems(this.ctrls.selectedNodes);
			var dropAll = false;

			if(sc.chooseAllSelected.checked){ // "choose all" checkbox is on

				dropAll = cbItemList.off.length == 0;

				// get all selected but unchecked
				for(var i = 0; i < sv.selected.nodes.length; i++){

					var itemId = +(sv.selected.nodes[i].id);

					// if some items were unchecked, throw them out
					if(!dropAll && BX.util.in_array(itemId, cbItemList.off))
						continue;

					result.nodes.push(itemId);
				}

			}else // "choose all" is off - just take what is really checked
				result.nodes = cbItemList.on;

			sc.chooseAllSelected.checked = false;

			this.deSelectItems(result, dropAll);
		},

		selectItems: function(selected){

			var sv = this.vars;

			for(var k in selected.grp)
			{
				if(!selected.grp.hasOwnProperty(k))
					continue;

				if(this.hasItem(selected.grp[k], sv.selected.grp) === false)
					this.selectLinkItem(selected.grp[k], 'grp');
			}

			for(var k in selected.nodes)
			{
				if(!selected.nodes.hasOwnProperty(k))
					continue;

				if(this.hasItem(selected.nodes[k], sv.selected.nodes) === false){
					// just add array item here
					sv.selected.nodes.unshift({
						id: selected.nodes[k],
						view: null
					});
				}
			}

			// node list restart
			sv.selectedNodesShowOffset = 0;
			BX.cleanNode(this.ctrls.selectedNodes);

			sv.addPageSelected();

			this.ctrls.chooseAll.checked = false;

			this.toggleSelectionAuxCtrls();
			this.displaySelectedForm();
		},

		deSelectItems: function(selected, dropAll){

			for(var k in selected.nodes)
				if(selected.nodes.hasOwnProperty(k))
					this.deselectLinkItem(selected.nodes[k], 'nodes', dropAll);


			if(dropAll) // empty the entire container, instead of removing node-by-node
				BX.cleanNode(this.ctrls.selectedNodes);

			for(var k in selected.grp)
				if(selected.grp.hasOwnProperty(k))
					this.deselectLinkItem(selected.grp[k], 'grp');

			this.toggleSelectionAuxCtrls();
			this.displaySelectedForm();
		},

		hasItem: function(id, list){
			for(var k = 0; k < list.length; k++){
				if(list[k].id == id)
					return k;
			}

			return false;
		},

		selectLinkItem: function(id, kind){
			var node = this.makeSelectedItemView(id, kind);

			this.vars.selected[kind].unshift({
				id: id,
				view: node
			});
			BX.prepend(node, this.ctrls[kind == 'nodes' ? 'selectedNodes' : 'selectedGroups']);

			if(kind == 'nodes')
				this.vars.selectedNodesShowOffset++;
		},

		deselectLinkItem: function(id, kind, dontRemoveNode){
			var i = this.hasItem(id, this.vars.selected[kind]);

			if(i === false)
				return;

			var item = this.vars.selected[kind][i];
			
			if(kind == 'nodes' && item.view !== null)
				this.vars.selectedNodesShowOffset--;

			if(item.view !== null && !dontRemoveNode)
				BX.remove(item.view);

			this.vars.selected[kind] = BX.util.deleteFromArray(this.vars.selected[kind], i);
		},

		makeSelectedItemView: function(id, kind){

			var data = BX.merge({
				random_value: this.getRandom()
			}, this.vars.cache[kind][id]);

			if(kind == 'nodes'){
				path = [];
				for(var k = 0; k < data.PATH.length; k++)
					path.push(this.vars.cache.path[data.PATH[k]]);
				data.path = path.join(', ');
				delete(data.PATH);

				if(typeof this.opts.types[data.TYPE_ID] != 'undefined')
					data.type = this.opts.types[data.TYPE_ID].NAME.toLowerCase();
				else
					data.type = '?';
			}

			return this.createNodesByTemplate('selected-'+(kind == 'nodes' ? 'node' : 'group'), data, true)[0];
		},

		showSelectedNodePage: function(){

			var sv = this.vars,
				sc = this.ctrls;

			if(sv.spMutex)
				return;

			sv.spMutex = true; // some kind of critical section

			var smtAdded = false;

			// here we need PATH lazyload again
			// check if all items has PATH info
			var absentPath = [];
			var items = [];
			for(var i = sv.selectedNodesShowOffset, j = 0; i < sv.selected.nodes.length && j < this.opts.pageSize; i++, j++){
				
				if(typeof sv.selected.nodes[i] == 'undefined') continue; // temporal solution

				var id = sv.selected.nodes[i].id;

				if(typeof sv.cache.nodes[id].PATH == 'undefined')
					absentPath.push(id);

				items.push(i);
			}

			this.downloadPath(absentPath, BX.proxy(function(){

				for(var i = 0; i < items.length; i++){

					var node = this.makeSelectedItemView(sv.selected.nodes[items[i]].id, 'nodes');

					sv.selected.nodes[items[i]].view = node;

					node.querySelector('input[type="checkbox"]').checked = this.ctrls.chooseAllSelected.checked;

					BX.append(node, this.ctrls.selectedNodes);

					sv.selectedNodesShowOffset++;
					smtAdded = true;
				}

				if(smtAdded)
					sc.scrollControllerSelected.informContentChanged();

			}, this), function(){
				sv.spMutex = false;
			});
		},

		showSelectedGroups: function(){
			var sv = this.vars;

			for(var i = 0; i < sv.selected.grp.length; i++){

				var node = this.makeSelectedItemView(sv.selected.grp[i].id, 'grp');
				sv.selected.grp[i].view = node;

				node.querySelector('input[type="checkbox"]').checked = this.ctrls.chooseAllSelected.checked;

				BX.append(node, this.ctrls.selectedGroups);
			}
		},

		clearChoosen: function(){

			BX.cleanNode(this.ctrls.selectedNodes);
			BX.cleanNode(this.ctrls.selectedGroups);

			this.vars.selected.nodes = [];
			this.vars.selected.grp = [];

			this.vars.selectedParentNode = false;

			this.ctrls.chooseAllSelected.checked = false;

			this.toggleSelectionAuxCtrls();
			this.displaySelectedForm();
		},

		displayVariants: function(items, pageNum){

			var sc = this.ctrls,
				sv = this.vars,
				so = this.opts,
				code = this.sys.code;

			this.hideNothingFound();

			// check if all items has PATH info
			var absentPath = [];
			for(var k in items){
				if(items.hasOwnProperty(k))
					if(typeof sv.cache.nodes[items[k]].PATH == 'undefined')
						absentPath.push(items[k]);
			}

			this.downloadPath(absentPath, BX.proxy(function(){

				if(sv.expectChooseAll){
					sc.chooseAll.checked = true;
					sv.expectChooseAll = false;
				}

				if(pageNum == 0){
					BX.cleanNode(sc.vars);

					sv.displayedIndex = [];
					sc.displayedItems = {};
				}

				for(var k in items)
				{

					if(!items.hasOwnProperty(k))
						continue;

					var domItem = this.whenRenderVariant(items[k])[0];

					BX.data(domItem, 'bx-'+code+'-item-value', items[k]);

					sc.vars.appendChild(domItem);
					this.fireEvent('after-item-append', [domItem]);

					sv.displayedIndex.push(items[k]);
					sc.displayedItems[items[k]] = domItem;
				}

				this.showDropdown();
				this.fireEvent('after-page-display', [sv.cache.nodes, pageNum]);

			}, this), function(){
			});
		},

		downloadPath: function(items, onLoad, onComplete){

			if(items.length == 0){
				onLoad();
				onComplete();
				return;
			}

			var sc = this.ctrls,
				sv = this.vars,
				so = this.opts,
				ctx = this;

			//sv.loader.show();
			BX.ajax({

				url: ctx.opts.source,
				method: 'post',
				dataType: 'json',
				async: true,
				processData: true,
				emulateOnload: true,
				start: true,
				data: {
					'REQUEST_TYPE' : 'get-path',
					'ITEMS': items
				},
				//cache: true,
				onsuccess: function(result){


					//sv.loader.hide();

					if(result.result){

						// fill path cache, setting up path in items
						for(var i = 0; i < items.length; i++){

							var k = items[i];

							try
							{
								sv.cache.nodes[k].PATH = result.data.PATH[k];
							}
							catch(e)
							{
								sv.cache.nodes[k].PATH = [];
							}
						}

						try
						{
							for(var itemId in result.data.PATH_ITEMS)
							{
								if(!result.data.PATH_ITEMS.hasOwnProperty(itemId))
									continue;

								var item = result.data.PATH_ITEMS[itemId];
								sv.cache.path[item.VALUE] = item.DISPLAY;
							}
						}
						catch(e)
						{
							BX.debug('Maleficent format of a part of responce to get-path request: PATH_ITEMS');
						}

						try
						{
							for(var itemId in result.data.ITEM_NAMES)
							{
								if(result.data.ITEM_NAMES.hasOwnProperty(itemId))
									sv.cache.nodes[itemId].DISPLAY = result.data.ITEM_NAMES[itemId];
							}
						}
						catch(e)
						{
							BX.debug('Maleficent format of a part of responce to get-path request: ITEM_NAMES');
						}

						onLoad();

					}else
						ctx.showError(ctx.opts.messages.error, result.errors);
					
					onComplete();
				},
				onfailure: function(e){

					onComplete();

					//sv.loader.hide();
					ctx.showError(
						so.messages.error,
						false,
						e
					);
				}

			});

		},

		/////////////////

		toggleSelectionAuxCtrls: function(){

			// noting selected prompt
			var sv = this.vars,
				sc = this.ctrls;

			var op = null;
			if(sv.selected.nodes.length == 0 && sv.selected.grp.length == 0)
				op = 'show';
			else
				op = 'hide';

			BX[op](sc.selectedNothing);

			// separator

			if(sv.selected.nodes.length != 0 && sv.selected.grp.length != 0)
				op = 'show';
			else
				op = 'hide';

			BX[op](sc.selectedGroupsSeparator);

			sc.scrollControllerSelected.informContentChanged();

			sc.selectedNodesCntr.innerHTML = sv.selected.nodes.length;
			if(BX.type.isElementNode(sc.selectedGroupsCntr))
				sc.selectedGroupsCntr.innerHTML = sv.selected.grp.length;
		},

		toggleCheckBoxes: function(scope, way){
			var checkboxes = null;
			var items = [];
			checkboxes = scope.querySelectorAll('input[type="checkbox"]');
			for(var i = 0; i < checkboxes.length; i++)
				checkboxes[i].checked = way;
		},

		displaySelectedForm: function(){

			var sv = this.vars,
				sc = this.ctrls,
				so = this.opts;

			BX.cleanNode(sc.inputPool);

			var inputsHTML = '';
			var serialized = '';

			if(sv.selected.nodes.length > 0){

				var separ = '';
				for(var i = 0; i < sv.selected.nodes.length; i++){

					var id = sv.selected.nodes[i].id;
					var code = sv.cache.nodes[id].CODE;

					serialized += separ+(so.useCodes ? code : id);
					separ = ':';
				}
			}

			inputsHTML += this.getHTMLByTemplate('location-input', {'=ids': serialized});
			serialized = '';

			if(sv.selected.grp.length > 0){
				var separ = '';
				for(var i = 0; i < sv.selected.grp.length; i++){

					var id = sv.selected.grp[i].id;
					var code = sv.cache.grp[id].CODE;
					
					serialized += separ+(so.useCodes ? code : id);
					separ = ':';
				}
			}

			inputsHTML += this.getHTMLByTemplate('group-input', {'=ids': serialized});

			BX.html(sc.inputPool, inputsHTML);

			this.fireEvent('after-select-item');
			this.fireEvent('after-target-input-modified');
		},

		checkSmthSelected: function(){
			return this.vars.selected.nodes.length > 0 || this.vars.selected.grp.length > 0;
		},

		getPlural: function(n, forms){

			if(n % 10 == 1 && n % 100 != 11)
				return forms.element;

			if(n % 10 >= 2 && n % 10 <= 4 && ( n % 100 < 10 || n % 100 >= 20))
				return forms.elementa;

			return forms.elementov;
		}

	});
}

if(typeof BX.Sale.component.location.selector.system.tree == 'undefined' && typeof BX.ui != 'undefined' && typeof BX.ui.itemTree != 'undefined'){

	BX.Sale.component.location.selector.system.tree = function(opts, nf){

		this.parentConstruct(BX.Sale.component.location.selector.system.tree, opts);

		BX.merge(this, {
			opts: {
				useDynamicLoading: 	true,
				pageSize: 			20,
				bindEvents: {
					'toggle-bundle-before': function(way, controls){
						BX[way ? 'addClass' : 'removeClass'](controls.expander, 'expanded');
					}
				}
			},
			sys: {
				code: 'item-tree-slss'
			}
		});

		this.handleInitStack(nf, BX.Sale.component.location.selector.system.tree, opts);
	};
	BX.extend(BX.Sale.component.location.selector.system.tree, BX.ui.itemTree);

	// the following functions can be overrided with inheritance
	BX.merge(BX.Sale.component.location.selector.system.tree.prototype, {

		// member of stack of initializers, must be defined even if do nothing
		init: function(){
			this.pushFuncStack('toggleRoot', BX.Sale.component.location.selector.system.tree);
		},

		toggleRoot: function(){
			this.manageCeiling(0, -1); // mark all uploaded to root

			try{
				this.toggleBundle(0); // open root, if there are any locations
			}
			catch(e)
			{}
		},

		refineRequest: function(request){

			var filter = {
				'=PARENT_ID': parseInt(request.ID)
			};

			if(this.opts.langId !== false)
				filter['=NAME.LANGUAGE_ID'] = this.opts.langId;

			return {
				'select': {
					'VALUE': 'ID',
					'DISPLAY': 'NAME.NAME',
					'1': 'IS_PARENT'
				},
				'filter': filter,
				'additionals': {
					'1': 'CNT_BY_FILTER' // this to calculate lazy load
				},
				'version': '2'
			};
		},

		refineResponce: function(responce){

			var result = {items: []};

			for(var k in responce.ITEMS)
			{
				if(!responce.ITEMS.hasOwnProperty(k))
					continue;

				var isParent = typeof responce.ITEMS[k].IS_PARENT != 'undefined' && (responce.ITEMS[k].IS_PARENT == true || parseInt(responce.ITEMS[k].IS_PARENT) > 0);

				result.items.push({
					name: 				responce.ITEMS[k].DISPLAY,
					id: 				responce.ITEMS[k].VALUE,
					is_parent: 			isParent ? '1' : '0',
					expander_class: 	isParent ? ' bx-ui-item-tree-slss-expander' : '',
					select_class: 		isParent ? ' bx-ui-slss-selector-show-bundle' : ''
				});
			}

			if(typeof responce.ETC.CNT_BY_FILTER != 'undefined')
				result.total = parseInt(responce.ETC.CNT_BY_FILTER);

			return result;
		}
	});

}
