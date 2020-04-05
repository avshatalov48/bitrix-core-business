(function(){

	if(typeof BX.ui != 'object')
		BX.ui = {};

	//////////////////////////////
	// generic dynamic list
	//////////////////////////////

	BX.ui.dynamicList = function(opts, nf){

		this.parentConstruct(BX.ui.dynamicList, opts);

		BX.merge(this, {
			opts: {
				initiallyAdd: 	0, // how many empty fields to show initially
				startFrom: 		0, // todo: make able to calculate this based on count of row already present
			},
			vars: {
				idOffset: 0
			},
			sys: {
				code: 'dynamiclist'
			}
		});

		this.handleInitStack(nf, BX.ui.dynamicList, opts);
	}
	BX.extend(BX.ui.dynamicList, BX.ui.widget);

	// the following functions can be overrided with inheritance
	BX.merge(BX.ui.dynamicList.prototype, {

		// member of stack of initializers, must be defined even if does nothing
		init: function(){

			BX.ui.dynamicList.superclass.init.call(this);

			this.ctrls.container = this.getControl('container');
			this.vars.idOffset = parseInt(this.opts.startFrom);

			this.pushFuncStack('buildUpDOM', BX.ui.dynamicList);
			this.pushFuncStack('bindEvents', BX.ui.dynamicList);
		},

		buildUpDOM: function(){
			var so = this.opts;

			for(var k = 0; k < parseInt(so.initiallyAdd); k++)
				this.addRow();
		},

		bindEvents: function(){

			var ctx = this,
				so = this.opts,
				sc = this.ctrls;

			var addMore = this.getControl('addmore', true);
			if(addMore != 'undefined'){
				BX.bind(addMore, 'click', function(){
					ctx.addRow();
				});
			}
		},

		addRow: function(){
			BX.append(this.whenbuildRow(), this.ctrls.container);
			this.vars.idOffset++;
		},

		whenbuildRow: function(){
			var row = this.createNodesByTemplate('row', {'column_id': this.vars.idOffset}, true)[0];

			this.fireEvent('after-row-built', [row]);

			return row;
		}
	});

})();
