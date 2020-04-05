;(function(window) {

	var debug = 0 ? console.log.bind(console, '[BXDOM]') : function() {};

	var raf = (
		window.requestAnimationFrame
		|| window.webkitRequestAnimationFrame
		|| window.mozRequestAnimationFrame
		|| window.msRequestAnimationFrame
		|| function(cb) { return setTimeout(cb, 16); }
	);

	var BXDOM = function()
	{
		this.reads = [];
		this.writes = [];
		this.raf = raf.bind(window);
		debug('initialized', this);
	};

	BXDOM.prototype = {
		constructor: BXDOM,

		read: function(fn, ctx)
		{
			debug('read');
			this.reads.push(!ctx ? fn : fn.bind(ctx));
			scheduleFlush(this);
		},

		write: function(fn, ctx)
		{
			debug('write');
			this.writes.push(!ctx ? fn : fn.bind(ctx));
			scheduleFlush(this);
		},

		catch: null
	};

	function scheduleFlush(dom) {
		if (!dom.scheduled)
		{
			dom.scheduled = true;
			dom.raf(flush.bind(null, dom));
			debug('flush scheduled');
		}
	}

	function flush(dom) {
		debug('flush');

		var writes = dom.writes;
		var reads = dom.reads;
		var error;

		try {
			debug('flushing reads', reads.length);
			runTasks(reads);
			debug('flushing writes', writes.length);
			runTasks(writes);
		} catch (e) { error = e; }

		dom.scheduled = false;

		if (reads.length || writes.length)
		{
			scheduleFlush(dom);
		}

		if (error)
		{
			debug('task errored', error.message);
			if (dom.catch) dom.catch(error);
			else throw error;
		}
	}

	function runTasks(tasks) {
		debug('run tasks');
		var task;

		while (task = tasks.shift())
		{
			task();
		}
	}

	BX.DOM = (BX.DOM || new BXDOM());

})(window);