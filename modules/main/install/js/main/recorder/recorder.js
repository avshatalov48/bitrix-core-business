/**
 * Uses lamejs implementation of the LAME library for mp3 encoding.
 * @see http://www.mp3dev.org
 * @see https://github.com/zhuker/lamejs
 */
(function(window)
{
	if (typeof(BX.Recorder) !== "undefined") return;

	var supportedTypes = {
		'audio/mp3': true
	};

	var states = {
		idle: 0,
		recording: 1,
		paused: 2
	};

	BX.Recorder = function(stream, options)
	{
		if(!stream instanceof window.MediaStream)
			throw "stream must be of type MediaStream";

		if(!BX.type.isPlainObject(options))
			options = {};

		this.stream = stream;
		this.options = {
			type: (options.type && BX.Recorder.isTypeSupported(options.type) ? options.type : 'audio/mp3')
		};

		this.state = states.idle;

		this.audioContext = null;
		this.mediaStreamNode = null;
		this.scriptNode = null;
		this.analyserNode = null;

		this.worker = new window.Worker("/bitrix/js/main/recorder/encoder.js");
		this.worker.postMessage({action: 'init', type: this.options.type});

		this.worker.onmessage = function(e)
		{
			switch(e.data.action)
			{
				case 'result':
					BX.onCustomEvent(this, 'stop', [e.data.result]);
					break;
			}
		}.bind(this);
	};

	BX.Recorder.isSupported = function()
	{
		return (
			typeof(window.Blob) !== 'undefined'
			&& typeof(window.Worker) !== 'undefined'
			&& typeof(window.URL) !== 'undefined'
			&& typeof(window.MediaStream) !== 'undefined'
			&& (typeof(window.AudioContext) !== 'undefined' || typeof(window.webkitAudioContext) !== 'undefined')
		);
	};

	BX.Recorder.isTypeSupported = function(type)
	{
		return (supportedTypes[type] === true);
	};

	BX.Recorder.prototype.start = function()
	{
		var self = this;
		if(this.state !== states.idle)
			throw "recording can not be started right now";

		this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
		this.scriptNode = this.audioContext.createScriptProcessor(16384, 1, 1);
		this.scriptNode.connect(this.audioContext.destination); //chrome does not start processing without this for unknown reason

		this.mediaStreamNode = this.audioContext.createMediaStreamSource(this.stream);
		this.mediaStreamNode.connect(this.scriptNode);

		this.scriptNode.onaudioprocess = function(event)
		{
			if(self.state !== states.recording)
				return;

			if(!self.worker)
				return;

			var input = event.inputBuffer.getChannelData(0);
			self.worker.postMessage({
				action: 'record',
				input: input
			});
		};
		self.worker.postMessage({action: 'start'});

		this.state = states.recording;
	};

	BX.Recorder.prototype.attachAnalyser = function(params)
	{
		if(this.state !== states.recording)
			throw "recorder is in the wrong state";

		if(!params)
		{
			params = {};
		}

		if(!this.analyserNode)
		{
			this.analyserNode = this.audioContext.createAnalyser();
			this.analyserNode.fftSize = params.fftSize || 128;
			this.analyserNode.minDecibels = params.minDecibels || -80;
			this.analyserNode.maxDecibels = params.maxDecibels || -10;

			this.mediaStreamNode.connect(this.analyserNode);
		}
	};

	BX.Recorder.prototype.stop = function()
	{
		if(this.state !== states.recording)
			throw "recording can not be stopped right now";

		this.worker.postMessage({
			action: 'stop'
		});

		if(this.analyserNode)
			this.analyserNode.disconnect();

		if(this.scriptNode)
			this.scriptNode.disconnect();

		if(this.mediaStreamNode)
			this.mediaStreamNode.disconnect();

		if(this.audioContext)
			this.audioContext.close();

		this.analyserNode = null;
		this.scriptNode = null;
		this.mediaStreamNode = null;
		this.audioContext = null;

		this.state = states.idle;
	};

	BX.Recorder.prototype.pause = function()
	{
		if(this.state !== states.recording)
			throw "recording can not be paused right now";

		this.state = states.paused;
	};

	BX.Recorder.prototype.resume = function()
	{
		if(this.state !== states.paused)
			throw "recording can not be resumed right now";

		this.state = states.recording;
	};

	BX.Recorder.prototype.replaceStream = function(stream)
	{
		if(!stream instanceof window.MediaStream)
			throw "stream must be of type MediaStream";

		this.stream = stream;
		if(this.analyserNode)
		{
			this.analyserNode.disconnect();
		}
		this.mediaStreamNode.disconnect();
		this.mediaStreamNode = this.audioContext.createMediaStreamSource(this.stream);
		this.mediaStreamNode.connect(this.scriptNode);
		if(this.analyserNode)
		{
			this.mediaStreamNode.connect(this.analyserNode);
		}
	};

	BX.Recorder.prototype.getState = function()
	{
		return this.state;
	};

	BX.Recorder.prototype.dispose = function()
	{
		this.stream = null;
	}

})(window);