import {Type} from 'main.core'

const VOLUME_THRESHOLD = 0.1;
const INACTIVITY_TIME = 2000;
const AVERAGING_COEFFICIENT = 0.5; // from 0 to 1;

/**
 * Naive voice activity detection
 * @param {object} config
 * @param {MediaStream} config.mediaStream
 * @param {function} config.onVoiceStarted
 * @param {function} config.onVoiceStopped
 * @constructor
 */
export class SimpleVAD
{
	constructor(config)
	{
		if (!(config.mediaStream instanceof MediaStream))
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
			voiceStarted: Type.isFunction(config.onVoiceStarted) ? config.onVoiceStarted : BX.DoNothing,
			voiceStopped: Type.isFunction(config.onVoiceStopped) ? config.onVoiceStopped : BX.DoNothing
		};

		if (SimpleVAD.isSupported())
		{
			this.init();
		}
	};

	static isSupported()
	{
		return (
			(window.AudioContext || window.webkitAudioContext)
			&& window.AnalyserNode
			&& typeof (window.AnalyserNode.prototype['getFloatTimeDomainData']) === "function"
		);
	};

	init()
	{
		this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
		this.analyserNode = this.audioContext.createAnalyser();
		this.analyserNode.fftSize = 128;
		this.mediaStreamNode = this.audioContext.createMediaStreamSource(this.mediaStream);
		this.mediaStreamNode.connect(this.analyserNode);

		this.audioTimeDomainData = new Float32Array(this.analyserNode.fftSize);
		this.measureInterval = setInterval(this.analyzeAudioStream.bind(this), 100);
	};

	analyzeAudioStream()
	{
		this.analyserNode.getFloatTimeDomainData(this.audioTimeDomainData);
		this.updateCurrentVolume(this.audioTimeDomainData);

		this.setVoiceState(this.currentVolume >= VOLUME_THRESHOLD);
	};

	setVoiceState(voiceState)
	{
		if (this.voiceState == voiceState)
		{
			return;
		}

		if (voiceState)
		{
			this.callbacks.voiceStarted();
			clearTimeout(this.inactivityTimeout);
			this.inactivityTimeout = 0;
			this.voiceState = true;
		}
		else
		{
			if (!this.inactivityTimeout)
			{
				this.inactivityTimeout = setTimeout(this.onInactivityTimeout.bind(this), INACTIVITY_TIME);
			}
		}
	};

	onInactivityTimeout()
	{
		this.inactivityTimeout = 0;
		this.voiceState = false;
		this.callbacks.voiceStopped();
	};

	updateCurrentVolume(audioTimeDomainData)
	{
		let sum = 0;

		for (let i = 0; i < audioTimeDomainData.length; i++)
		{
			sum += audioTimeDomainData[i] * audioTimeDomainData[i];
		}

		const rms = Math.sqrt(sum / audioTimeDomainData.length);
		this.currentVolume = Math.max(rms, this.currentVolume * AVERAGING_COEFFICIENT);
	};

	destroy()
	{
		if (this.analyserNode)
		{
			this.analyserNode.disconnect();
		}

		if (this.mediaStreamNode)
		{
			this.mediaStreamNode.disconnect();
		}

		if (this.audioContext)
		{
			this.audioContext.close();
		}

		if (this.mediaStream)
		{
			this.mediaStream.getTracks().forEach(track => track.stop());
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
}

