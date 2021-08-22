;(function()
{
	if(BX.SimpleVAD)
	{
		return;
	}

	var VOLUME_THRESHOLD = 0.1;
	var INACTIVITY_TIME = 2000;
	var AVERAGING_COEFFICIENT = 0.5; // from 0 to 1;

	/**
	 * Naive voice activity detection
	 * @param {object} config
	 * @param {MediaStream} config.mediaStream
	 * @param {function} config.onVoiceStarted
	 * @param {function} config.onVoiceStopped
	 * @constructor
	 */
	BX.SimpleVAD = function(config)
	{
		if(!(config.mediaStream instanceof MediaStream))
		{
			throw new Error("config.mediaStream should be instance of MediaStream");
		}

		if (config.mediaStream.getAudioTracks().length === 0)
		{
			throw new Error("config.mediaStream should contain audio track");
		}

		this.mediaStream = new MediaStream();
		this.mediaStream.addTrack(config.mediaStream.getAudioTracks()[0].clone());
		this.audioContext = null;
		this.mediaStreamNode = null;
		this.analyserNode = null;

		this.audioTimeDomainData = null;
		this.voiceState = false;

		this.measureInterval = 0;
		this.inactivityTimeout = 0;

		this.currentVolume = 0;

		this.callbacks = {
			voiceStarted: BX.type.isFunction(config.onVoiceStarted) ? config.onVoiceStarted : BX.DoNothing,
			voiceStopped: BX.type.isFunction(config.onVoiceStopped) ? config.onVoiceStopped : BX.DoNothing
		};

		if(BX.SimpleVAD.isSupported())
		{
			this.init();
		}
	};

	BX.SimpleVAD.isSupported = function()
	{
		return (window.AudioContext || window.webkitAudioContext) && window.AnalyserNode && typeof(window.AnalyserNode.prototype['getFloatTimeDomainData']) === "function";
	};

	BX.SimpleVAD.prototype.init = function()
	{
		this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
		this.analyserNode = this.audioContext.createAnalyser();
		this.analyserNode.fftSize = 128;
		this.mediaStreamNode = this.audioContext.createMediaStreamSource(this.mediaStream);
		this.mediaStreamNode.connect(this.analyserNode);

		this.audioTimeDomainData = new Float32Array(this.analyserNode.fftSize);
		this.measureInterval = setInterval(this.analyzeAudioStream.bind(this), 100);
	};

	BX.SimpleVAD.prototype.analyzeAudioStream = function()
	{
		this.analyserNode.getFloatTimeDomainData(this.audioTimeDomainData);
		this.updateCurrentVolume(this.audioTimeDomainData);

		this.setVoiceState(this.currentVolume >= VOLUME_THRESHOLD);
	};

	BX.SimpleVAD.prototype.setVoiceState = function(voiceState)
	{
		if(this.voiceState == voiceState)
		{
			return;
		}

		if(voiceState)
		{
			this.callbacks.voiceStarted();
			clearTimeout(this.inactivityTimeout);
			this.inactivityTimeout = 0;
			this.voiceState = true;
		}
		else
		{
			if(!this.inactivityTimeout)
			{
				this.inactivityTimeout = setTimeout(this.onInactivityTimeout.bind(this), INACTIVITY_TIME);
			}
		}
	};

	BX.SimpleVAD.prototype.onInactivityTimeout = function()
	{
		this.inactivityTimeout = 0;
		this.voiceState = false;
		this.callbacks.voiceStopped();
	};

	BX.SimpleVAD.prototype.updateCurrentVolume = function(audioTimeDomainData)
	{
		var sum = 0;

		for(var i = 0; i < audioTimeDomainData.length; i++)
		{
			sum += audioTimeDomainData[i] * audioTimeDomainData[i];
		}

		var rms = Math.sqrt(sum / audioTimeDomainData.length);
		this.currentVolume = Math.max(rms, this.currentVolume * AVERAGING_COEFFICIENT);
	};

	BX.SimpleVAD.prototype.destroy = function()
	{
		if(this.analyserNode)
		{
			this.analyserNode.disconnect();
		}

		if(this.mediaStreamNode)
		{
			this.mediaStreamNode.disconnect();
		}

		if(this.audioContext)
		{
			this.audioContext.close();
		}

		if (this.mediaStream)
		{
			this.mediaStream.getTracks().forEach(function(track)
			{
				track.stop();
			});
			this.mediaStream = null;
		}

		clearInterval(this.measureInterval);

		this.analyserNode = null;
		this.mediaStreamNode = null;
		this.audioContext = null;

		this.callbacks = {
			voiceStarted: BX.DoNothing,
			voiceStopped: BX.DoNothing
		}
	}
})();

