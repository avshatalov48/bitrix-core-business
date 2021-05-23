BX.namespace('BX.Sale.component.location');

if(typeof BX.Sale.component.location.reindex == 'undefined' && typeof BX.ui != 'undefined' && typeof BX.ui.widget != 'undefined'){

	BX.Sale.component.location.reindex = function(opts, nf){

		this.parentConstruct(BX.Sale.component.location.reindex, opts);

		BX.merge(this, {
			opts: { // default options
				url: 				'/somewhere.php',
				ajaxFlag: 			'AJAX_MODE',
				progressWidth : 	500
			},
			vars: { // significant variables
				stage: 				false,
				state: 				'remote',
				awaitInterruption: 	false
			},
			ctrls: { // links to controls
				buttons: {}
			},
			sys: {
				code: 'loc-ri'
			}
		});

		this.handleInitStack(nf, BX.Sale.component.location.reindex, opts);
	};
	BX.extend(BX.Sale.component.location.reindex, BX.ui.widget);

	BX.merge(BX.Sale.component.location.reindex.prototype, {

		init: function(){

			var so = this.opts,
				sv = this.vars,
				sc = this.ctrls,
				ctx = this;
			
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

					return result.data.PERCENT < 100;
				}
			});

			sv.iterator.bindEvent('set-status', function(stat){
				//console.dir('Status change');
				//console.dir(arguments);

				if(stat == 'R'){
					sc.buttons.startStop.value = so.messages.stop;
					ctx.setCSSState('running');
				}else if(stat == 'I'){
					sc.buttons.startStop.value = so.messages.stopping;
					sc.buttons.startStop.setAttribute('disabled', 'disabled');
					ctx.setStage('INTERRUPTING');
					sv.awaitInterruption = true;
				}else if(stat == 'S'){

					sc.buttons.startStop.value = so.messages.start;
					ctx.dropCSSState('running');
					sc.buttons.startStop.removeAttribute('disabled');
					ctx.setStage(sv.awaitInterruption ? 'INTERRUPTED' : 'COMPLETE');

					sv.awaitInterruption = false;
				}
			});

			sc.buttons.startStop = 	this.getControl('button-start');

			sc.percentIndicator = 	this.getControl('percents', false, false, true);
			sc.percentGrade = 		this.getControl('adm-progress-bar-inner');
			sc.statusText = 		this.getControl('status-text');

			//this.pushFuncStack('buildUpDOM', BX.Sale.component.location.reindex);
			this.pushFuncStack('bindEvents', BX.Sale.component.location.reindex);
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

					var request = {
						ACT: 		'REINDEX',
						ACT_DATA: 	ctx.getFormControlValues('option')
					};
					request[so.ajaxFlag] = 1;

					if(ctx.askReindex()){

						ctx.setPercent(0);
						ctx.setStage('CLEANUP');

						BX.show(ctx.getControl('progressbar'));

						sv.iterator.start(request);
					}
				}
			});

			var onError = function(error){

				//if(error.detail.type == 'server-error')
				BX.debug(error);

				sc.buttons.startStop.value = so.messages.start;

				var errMsg = [];

				ctx.setStatusText(so.messages.error_occured, true);
			}

			sv.iterator.bindEvent('server-error', onError);
			sv.iterator.bindEvent('ajax-error', onError);

			var setState = function(state, initial){
				sc.scope.className = '';

				ctx.setCSSState('mode-'+state);
				sv.state = state;
			}
		},

		askReindex: function(){
			return confirm(this.opts.messages.sure_reindex);
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

					// skip unchecked checkboxes and radio
					if(opt.nodeName == 'INPUT' && (opt.type == 'checkbox' || opt.type == 'radio') && !opt.checked)
						continue;

					var value = '';
					if(opt.nodeName == 'SELECT' && opt.multiple)
					{
						value = [];

						for(var m = 0; m < opt.length; m++)
						{
							if(opt[m].selected)
								value.push(opt[m].value);
						}
					}
					else
						value = opt.value;

					result[name] = value;
				}
			}

			return result;
		},

		setTab: function(tab){
			BX[(tab == 'tab_cleanup' ? 'hide' : 'show')](this.ctrls.buttons.startStop);
		}
	});
}