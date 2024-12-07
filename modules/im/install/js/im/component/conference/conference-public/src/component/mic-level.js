import { BitrixVue } from "ui.vue";
import { Type } from "main.core";

const MicLevel = {
	props: ['localStream'],
	data()
	{
		return {
			bars: [],
			barDisabledColor: 'rgba(255,255,255,0.42)',
			barEnabledColor: '#2FC6F6'
		}
	},
	watch:
	{
		localStream(stream)
		{
			if (!Type.isNil(stream))
			{
				this.startAudioCheck();
			}
		},
	},
	mounted()
	{
		this.bars = [...document.querySelectorAll('.bx-im-component-call-check-devices-micro-level-item')];
	},
	computed:
	{
		localize()
		{
			return BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_CHECK_DEVICES_');
		}
	},
	methods:
	{
		startAudioCheck()
		{
			this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
			this.analyser = this.audioContext.createAnalyser();
			this.microphone = this.audioContext.createMediaStreamSource(this.localStream);
			this.scriptNode = this.audioContext.createScriptProcessor(2048, 1, 1);

			this.analyser.smoothingTimeConstant = 0.8;
			this.analyser.fftSize = 1024;

			this.microphone.connect(this.analyser);
			this.analyser.connect(this.scriptNode);
			this.scriptNode.connect(this.audioContext.destination);
			this.scriptNode.onaudioprocess = this.processVolume;
		},
		processVolume()
		{
			let arr = new Uint8Array(this.analyser.frequencyBinCount);
			this.analyser.getByteFrequencyData(arr);
			let values = 0;

			for (let i = 0; i < arr.length; i++)
			{
				values += arr[i];
			}
			let average = values / arr.length;

			let oneBarValue = 100 / this.bars.length;
			let barsToColor = Math.round(average / oneBarValue);
			let elementsToColor = this.bars.slice(0, barsToColor);
			this.bars.forEach(elem => {
				elem.style.backgroundColor = this.barDisabledColor;
			});
			elementsToColor.forEach(elem => {
				elem.style.backgroundColor = this.barEnabledColor;
			});
		}
	},
	template: `
		<div class="bx-im-component-call-check-devices-row">
			<div class="bx-im-component-call-check-devices-micro-icon"></div>
			<div class="bx-im-component-call-check-devices-micro-level">
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
				<div class="bx-im-component-call-check-devices-micro-level-item"></div>
			</div>
		</div>
	`
};

export {MicLevel};