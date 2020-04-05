BX.ui.fileAsyncLoader = function(opts, nf){

	this.parentConstruct(BX.ui.fileAsyncLoader, opts);

	BX.merge(this, {
		opts: { // default options
			timeout: 		800,
			url: 			'/somewhere.php',
			fileUploadTag: 	'FILE_UPLOAD'
		},
		vars: { // significant variables
			prevValue: 		''
		},
		ctrls: { // links to controls
		},
		sys: {
			code: 'file-async-loader'
		}
	});

	this.handleInitStack(nf, BX.ui.fileAsyncLoader, opts);
};
BX.extend(BX.ui.fileAsyncLoader, BX.ui.widget);

// the following functions can be overrided with inheritance
BX.merge(BX.ui.fileAsyncLoader.prototype, {

	// member of stack of initializers, must be defined even if do nothing
	init: function(){

		var sc = this.ctrls,
			sv = this.vars;

		sc.success = this.getControl('success');
		sc.fail = this.getControl('fail');
		sc.inProgress = this.getControl('in-progress');
		sc.inputContainer = this.getControl('input');

		sc.input = sc.scope.querySelector('input[type="file"]');
		sv.randomTag = this.getRandom();

		//this.pushFuncStack('buildUpDOM', BX.ui.fileAsyncLoader);
		this.pushFuncStack('bindEvents', BX.ui.fileAsyncLoader);
	},

	//buildUpDOM: function(){},

	bindEvents: function(){

		var ctx = this;

		this.startMonitor();

		BX.bindDelegate(this.ctrls.scope, 'click', {className: ctx.getControlClassName('retry')}, function(){
			ctx.upload();
		});
	},

	startMonitor: function(){

		var sc = this.ctrls,
			sv = this.vars,
			so = this.opts,
			ctx = this;

		if(sc.input.value != sv.prevValue){

			sv.prevValue = sc.input.value;

			if(sv.prevValue.length > 0)
			{
				this.upload();
				return;
			}
		}

		setTimeout(BX.proxy(this.startMonitor, this), so.timeout);
	},

	hideMessages: function(){
		var sc = this.ctrls;

		BX.hide(sc.success);
		BX.hide(sc.fail);
		BX.hide(sc.inProgress);
	},

	deSelect: function(){

		var sc = this.ctrls;

		this.hideMessages();

		var newInput = BX.clone(sc.input);
		BX.insertAfter(newInput, sc.input);
		BX.remove(sc.input);

		sc.input = newInput;
	},

	upload: function(){

		var so = this.opts,
			sv = this.vars,
			sc = this.ctrls;

		if(sc.input.value.length == 0)
			return;

		var form = BX.create('form', {
			props: {
				enctype: 'multipart/form-data',
				action: so.url,
				method: 'post',
				target: sv.randomTag
			},
			style: {
				display: 'none'
			}
		});

		var flag = BX.create('input', {
			props: {
				type: 'hidden',
				name: so.fileUploadTag,
				value: sv.randomTag
			}
		});
		form.appendChild(flag);

		var farAway = document.querySelector('body');
		farAway.appendChild(form);

		var iframe = BX.create('iframe', {
			props: {
				name: sv.randomTag
			},
			style: {
				display: 'none'
			}
		});

		farAway.appendChild(iframe);

		form.appendChild(sc.input);

		if(typeof BX[this.sys.code] == 'undefined')
			BX[this.sys.code] = {};

		BX[this.sys.code][sv.randomTag] = this;

		sc.form = form;
		sc.iframe = iframe;

		BX.hide(sc.success);
		BX.hide(sc.fail);
		BX.show(sc.inProgress);

		form.submit();
	},

	uploadSuccess: function(){

		var sc = this.ctrls;

		this.cleanUp();

		BX.show(sc.success);
		BX.hide(sc.fail);

		this.fireEvent('upload-success');
	},

	uploadFail: function(error){

		var sc = this.ctrls;

		this.cleanUp();

		BX.show(sc.fail);
		BX.hide(sc.success);

		this.fireEvent('upload-fail');
	},

	cleanUp: function(){

		var sc = this.ctrls;

		BX.hide(sc.inProgress);

		BX.remove(sc.iframe);
		sc.inputContainer.appendChild(sc.input);
		BX.remove(sc.form);

		this.startMonitor();
	}

});