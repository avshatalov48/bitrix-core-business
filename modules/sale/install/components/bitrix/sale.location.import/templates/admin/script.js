BX.namespace('BX.Sale.component.location');

if(typeof BX.Sale.component.location.import == 'undefined' && typeof BX.ui != 'undefined' && typeof BX.ui.widget != 'undefined'){

	BX.Sale.component.location.import = function(opts, nf){

		this.parentConstruct(BX.Sale.component.location.import, opts);

		BX.merge(this, {
			opts: { // default options
				url: 				'/somewhere.php',
				ajaxFlag: 			'AJAX_MODE',
				progressWidth : 	500,
				firstImport: 		false,
				statistics: 		{}
			},
			vars: { // significant variables
				stage: 				false,
				state: 				'remote',
				formSubmitted: 		false,
				fileUploaded: 		false,
				awaitInterruption: 	false,
				firstImport: 		false,
				statistics: 		{}
			},
			ctrls: { // links to controls
				buttons: {}
			},
			sys: {
				code: 'loc-i'
			}
		});

		this.handleInitStack(nf, BX.Sale.component.location.import, opts);
	};
	BX.extend(BX.Sale.component.location.import, BX.ui.widget);

	BX.merge(BX.Sale.component.location.import.prototype, {

		init: function(){

			var so = this.opts,
				sv = this.vars,
				sc = this.ctrls,
				ctx = this;

			sv.firstImport = so.firstImport;
			sv.statistics = BX.clone(so.statistics);
			
			// iterator

			sv.iterator = new BX.iterator({
				source: so.url,
				interval: 100,
				waitAjaxOnStop: true,
				whenHit: function(result){
					ctx.setPercent(result.data.PERCENT);

					var next = result.data.NEXT_STAGE;

					// set message
					if(BX.type.isNotEmptyString(next) && sv.stage != result.data.NEXT_STAGE)
						ctx.setStage(result.data.NEXT_STAGE);

					var proceed = result.data.PERCENT < 100;

					if(!proceed)
					{
						if(typeof result.data.STAT != 'undefined')
							ctx.updateStatistics(result.data.STAT);

						var lastRequest = this.vars.fields;

						if((typeof lastRequest.OPTIONS != 'undefined' && typeof lastRequest.OPTIONS.DROP_ALL != 'undefined') || (typeof lastRequest.ONLY_DELETE_ALL != 'undefined'))
							sv.firstImport = false; // now it is definitely NOT a first import
					}

					return proceed;
				}
			});

			sv.iterator.bindEvent('set-status', function(stat){

				if(this.vars.fields.ONLY_DELETE_ALL == 1)
				{
					if(stat == 'R'){
						sc.buttons.startStop.setAttribute('disabled', 'disabled');
						sc.buttons.deleteAll.setAttribute('disabled', 'disabled');
						ctx.setCSSState('running');
					}else if(stat == 'S'){
						sc.buttons.startStop.removeAttribute('disabled');
						sc.buttons.deleteAll.removeAttribute('disabled');
						ctx.dropCSSState('running');
						ctx.setStage('COMPLETE_REMOVE_ALL');
					}
				}
				else
				{

					if(stat == 'R'){
						sc.buttons.startStop.value = so.messages.stop;
						ctx.setCSSState('running');
					}else if(stat == 'I'){
						sc.buttons.startStop.value = so.messages.stopping;
						sc.buttons.startStop.setAttribute('disabled', 'disabled');
						ctx.setStage('INTERRUPTING');
						sv.awaitInterruption = true;
					}else if(stat == 'S'){

						function enableButtons(){
							sc.buttons.startStop.value = so.messages.start;
							ctx.dropCSSState('running');
							sc.buttons.startStop.removeAttribute('disabled');
							ctx.setStage(sv.awaitInterruption ? 'INTERRUPTED' : 'COMPLETE');

							sv.awaitInterruption = false;
						}

						if(false && sv.awaitInterruption){
							
							// here we must ensure indexes restored
							BX.ajax({
								url: so.url,
								method: 'post',
								dataType: 'html',
								async: true,
								processData: true,
								emulateOnload: true,
								start: true,
								data: {'RESTORE_INDEXES': 1},
								//cache: true,
								onsuccess: enableButtons,
								onfailure: enableButtons
							});
						}else
							enableButtons();
					}

				}
			});

			// tree
			sv.tree = new BX.ui.itemTree({
				scope: this.getControl('location-set'),

				bindEvents: {
					'toggle-bundle-before': function(way, controls){
						BX[way ? 'addClass' : 'removeClass'](controls.expander, 'expanded');
					}
				}
			});

			// file loader
			sc.fLoader = new BX.ui.fileAsyncLoader({
				scope: ctx.getControl('userfile'),
				url: so.pageUrl
			});

			// moving item back to its designed origin
			var moveInputBack = function(){
				var admInput = BX.findChildren(this.ctrls.inputContainer, {className: 'adm-input-file'});
				admInput[0].appendChild(this.ctrls.input);
			}

			sc.fLoader.bindEvent('upload-success', function(){
				sv.fileUploaded = true;
				moveInputBack.call(this);
			});
			sc.fLoader.bindEvent('upload-fail', function(){
				sv.fileUploaded = false;
				moveInputBack.call(this);
			});


			sc.buttons.startStop = 	this.getControl('button-start');
			sc.buttons.deleteAll = this.getControl('delete-all');
			sc.nameOfSet = 			this.getControl('location-set');

			sc.percentIndicator = 	this.getControl('percents', false, false, true);
			sc.percentGrade = 		this.getControl('adm-progress-bar-inner');
			sc.statusText = 		this.getControl('status-text');

			sc.statictics = {
				list: 		this.getControl('stat-list'),
				all: 		this.getControl('stat-all'),
				groups: 	this.getControl('stat-groups')
			};

			//this.pushFuncStack('buildUpDOM', BX.Sale.component.location.import);
			this.pushFuncStack('bindEvents', BX.Sale.component.location.import);
		},

		/*buildUpDOM: function(){},*/

		bindEvents: function(){

			var sc = this.ctrls,
				sv = this.vars,
				so = this.opts,
				ctx = this;

			// iterator

			BX.bind(sc.buttons.startStop, 'click', function(){

				if(sv.iterator.getIsRunning()){

					sv.iterator.stop();

				}else{

					// location sets
					var children = BX.findChildren(sc.nameOfSet, {tag: 'input'}, true);
					var sets = [];
					for(var k in children){
						if(children[k].checked && children[k].name.length > 0 && children[k].value.length > 0)
							sets.push(children[k].value);
					}
					if(sv.state == 'remote' && !sets.length)
					{
						alert(so.messages.selectItems);
						return;
					}

					if(sv.state == 'file' && !sv.fileUploaded)
					{
						alert(so.messages.uploadFile);
						return;
					}

					var request = {
						LOCATION_SETS: 		sets,
						ADDITIONAL: 		ctx.getFormControlValues('additional'),
						OPTIONS: 			ctx.getFormControlValues('option')
					};
					request[so.ajaxFlag] = 1;

					if(request.OPTIONS.DROP_ALL && !ctx.askRemove())
						return;

					ctx.setPercent(0);
					ctx.setStage('DOWNLOAD_FILES');

					BX.show(ctx.getControl('progressbar'));

					sv.iterator.start(request);
				}
			});

			BX.bind(sc.buttons.deleteAll, 'click', function(e){

				if(ctx.askRemove()){

					var request = {
						ONLY_DELETE_ALL: 	1
					};
					request[so.ajaxFlag] = 1;

					ctx.setPercent(0);
					ctx.setStage('DELETE_ALL');

					BX.show(ctx.getControl('progressbar'));

					sv.iterator.start(request);
				}

				BX.PreventDefault(e);
			});

			var onError = function(errors){
				sc.buttons.startStop.value = so.messages.start;

				var errMsg = [];

				if(typeof errors != 'undefined'){
					for(var k in errors){
						if(errors[k].message)
							errMsg.push(errors[k].message);
					}
				}

				ctx.setStatusText(so.messages.error_occured+': '+errMsg.join(', '), true);
			}

			sv.iterator.bindEvent('server-error', onError);
			sv.iterator.bindEvent('ajax-error', onError);

			var setState = function(state, initial){
				sc.scope.className = '';

				ctx.setCSSState('mode-'+state);
				sv.state = state;

				/*
				if(!initial && state == 'remote'){
					sv.fileUploaded = false;
					sc.fLoader.hideMessages();
				}
				*/
			}

			BX.bindDelegate(this.getControl('mode-switch'), 'change', {tag: 'input'}, function(){
				setState(this.value);
			});
			setState('remote', true);
		},

		askRemove: function(){

			var so = this.opts;

			if(typeof this.vars.statistics.TOTAL != 'undefined' && this.vars.statistics.TOTAL.CNT == 0)
				return true; // empty base

			var hasRelicLocations = this.vars.firstImport;
			var answer = confirm(so.messages[hasRelicLocations ? 'confirm_delete_relic' : 'confirm_delete']);

			return (hasRelicLocations && !answer) || (!hasRelicLocations && answer);
		},

		setPercent: function(value){
			var sc = this.ctrls,
				so = this.opts;

			value = parseInt(value);
			if(value < 0)
				value = 0;
			if(value > 100)
				value = 100;

			if(sc.percentIndicator != null){
				for(var k in sc.percentIndicator){
					sc.percentIndicator[k].innerHTML = value;
				}
			}
			
			value = value * (so.progressWidth / 100) - 4;
			if(value < 0)
				value = 0;

			BX.style(sc.percentGrade, 'width', value+'px');
		},

		setStatusText: function(text, highlight){
			this.ctrls.statusText.innerHTML = BX.util.htmlspecialchars(text);
			BX.style(this.ctrls.statusText, 'color', highlight ? 'red' : 'inherit');
		},

		setStage: function(stageCode){

			var so = this.opts,
				sv = this.vars;

			if(typeof so.messages['stage_'+stageCode] == 'undefined'){
				this.setStatusText(BX.util.htmlspecialchars(stageCode), true);
				sv.stage = false;
				return;
			}

			this.setStatusText(this.opts.messages['stage_'+stageCode], false);
			sv.stage = stageCode;
		},

		getFormControlValues: function(fcCode){

			var result = {};
			var controls = this.getControl(fcCode, true, false, true);

			for(var k = 0; k < controls.length; k++)
			{
				var opt = controls[k];

				if('name' in opt)
				{
					var name = opt.name;

					if(!('type' in opt))
						continue;

					if(opt.nodeName == 'INPUT' && (opt.type == 'checkbox' || opt.type == 'radio') && !opt.checked)
						continue;

					result[name] = opt.value;
				}
			}

			return result;
		},

		setTab: function(tab)
		{
			BX.style(this.ctrls.buttons.startStop, 'visibility', tab == 'tab_cleanup' ? 'hidden' : 'visible');
		},

		updateStatistics: function(stat){
			
			var sc = this.ctrls;

			BX.cleanNode(sc.statictics.list);
			var html = 		'';
			var all = 		0;
			var groups = 	0;
			for(var k in stat)
			{
				if(typeof this.vars.statistics[stat[k].CODE] == 'undefined')
					this.vars.statistics[stat[k].CODE] = {};

				this.vars.statistics[stat[k].CODE].CNT = stat[k].CNT;

				if(typeof stat[k].NAME == 'string' && stat[k].NAME.length > 0)
				{
					html += this.getHTMLByTemplate('stat-item', {
						type: stat[k].NAME,
						count: parseInt(stat[k].CNT)
					});
				}

				if(stat[k].CODE == 'TOTAL')
					all = parseInt(stat[k].CNT);

				if(stat[k].CODE == 'GROUPS')
					groups = parseInt(stat[k].CNT);
			}

			BX.html(sc.statictics.list, html);
			BX.html(sc.statictics.all, all);
			BX.html(sc.statictics.groups, groups);
		}

	});

}