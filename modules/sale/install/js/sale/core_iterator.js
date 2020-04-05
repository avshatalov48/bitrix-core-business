BX.iterator = function(opts, nf){

	this.parentConstruct(BX.iterator, opts);

	BX.merge(this, {
		opts: {
			source: 		'/somewhere.php', // url to call to
			interval: 		1000, // call interval
			dataType: 		'json',
			method: 		'post',
			loader: 		null,
			waitAjaxOnStop: false,

			whenHit: 	function(){return true} // what to do on a hit
		},
		vars: {
			status: 		'S',
			running: 		false,
			timeTag: 		false,
			ajaxTag: 		false,
			step: 			0, // current step number
			fields: 		{}, // current fields being sent on each hit
			lastRequest: 	null,
			lastResponce: 	null,
			wasAborted: 	false
		},
		ctrls: { // links to controls
		},
		sys: {
			code: 	'iterator',
			statuses: {
				stopped: 	'S',
				stopping: 	'I',
				running: 	'R'
			}
		}
	});

	this.handleInitStack(nf, BX.iterator, opts);
};
BX.extend(BX.iterator, BX.ui.widget); // may be BX.class or smth like that later

// the following functions can be overrided with inheritance
BX.merge(BX.iterator.prototype, {

	// member of stack of initializers, must be defined even if do nothing
	init: function(){
		var sv = this.vars,
			so = this.opts;

		sv.loader = typeof so.loader != 'object' ? {show: BX.DoNothing, hide: BX.DoNothing} : so.loader;

		this.fireEvent('set-status', [sv.status]);
	},

	start: function(fields){

		var sv = this.vars;

		if(sv.running) return;
		sv.running = true;
		sv.status = this.sys.statuses.running;

		sv.fields = BX.clone(fields);

		this.fireEvent('set-status', [sv.status]);

		this.query();
	},

	stop: function(){

		var sv = this.vars,
			so = this.opts;

		clearInterval(sv.timeTag);

		if(so.waitAjaxOnStop){

			sv.status = 	this.sys.statuses.stopping;
			this.fireEvent('set-status', [sv.status]);

		}else{
			if(typeof(sv.ajaxTag) != 'undefined' && sv.ajaxTag.readyState < 4){
				sv.wasAborted = true;
				sv.ajaxTag.abort();
			}

			this.stopIterator();
		}
	},

	refineRequest: function(request){
		return request;
	},

	refineResponce: function(responce, request){
		return responce;
	},

	getStatus: function(){
		return this.vars.status;
	},

	getIsRunning: function(){
		return this.vars.status == this.sys.statuses.running ||this.vars.status == this.sys.statuses.stopping;
	},

	// private

	stopIterator: function(result){

		var sv = this.vars;

		sv.step = 		0;
		sv.running = 	false;
		sv.status = 	this.sys.statuses.stopped;

		this.fireEvent('set-status', [sv.status, result]);
	},

	query: function(){

		var ctx = 	this,
			so = 	this.opts,
			sv = 	this.vars;

		sv.loader.show();

		this.fireEvent('before-ajax-call');

		var request = 		BX.clone(sv.fields);
		request.step = 		sv.step;
		request.csrf = 		BX.bitrix_sessid();

		sv.lastRequest = 	request;

		sv.ajaxTag = BX.ajax({

			url: so.source,
			method: so.method,
			dataType: so.dataType,
			async: true,
			processData: true,
			emulateOnload: true,
			start: true,
			data: ctx.refineRequest(request),
			//cache: true,
			onsuccess: function(result){

				sv.loader.hide();

				if(sv.wasAborted)
					return;

				//try{
				if(sv.status == ctx.sys.statuses.stopping){
					ctx.stopIterator();
					return;
				}

				result = ctx.refineResponce(result, request);
				sv.lastResponce = result;

				if(result.result){

					sv.step++;

					if(!so.whenHit.apply(ctx, [result, request])){
						ctx.stopIterator(result);
					}else if(sv.running)
						sv.timeTag = setTimeout(function(){ctx.query()}, so.interval);

					ctx.fireEvent('ajax-success');
				}else{
					ctx.stopIterator();
					var errors = [];

					if(typeof result.errors != 'undefined')
					{
						for(var k in result.errors)
							if(result.errors.hasOwnProperty(k))
								errors.push({message: result.errors[k]});
					}
					
					ctx.fireEvent('server-error', [errors]);
				}

				ctx.fireEvent('ajax-complete');
				//}catch(e){console.dir(e);}

			},
			onfailure: function(msg, detail){

				sv.loader.hide();

				if(sv.wasAborted)
					return;

				ctx.stopIterator();

				ctx.fireEvent('ajax-error', [{message: msg, detail: detail}]);
				ctx.fireEvent('ajax-complete');
			}

		});
	}

});