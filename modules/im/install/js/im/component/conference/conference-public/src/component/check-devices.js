import {BitrixVue} from "ui.vue";
import {MicLevel} from './mic-level';
import {Type} from "main.core";
import {Logger} from "im.lib.logger";
import {Utils} from "im.lib.utils";
import { MessageBox, MessageBoxButtons } from "ui.dialogs.messagebox";

import 'ui.forms';

const CheckDevices = {
	data()
	{
		return {
			noVideo: true,
			selectedCamera: null,
			selectedMic: null,
			mediaStream: null,
			showMic: true,
			userDisabledCamera: false,
			gettingVideo: false,
			isFlippedVideo: BX.Call.Hardware.enableMirroring,
		}
	},
	created()
	{
		this.$root.$on('setCameraState', (state) => {this.onCameraStateChange(state)});
		this.$root.$on('setMicState', (state) => {this.onMicStateChange(state)});
		this.$root.$on('callLocalMediaReceived', () => {this.stopLocalVideo()});
		this.$root.$on('cameraSelected', (cameraId) => {this.onCameraSelected(cameraId)});
		this.$root.$on('micSelected', (micId) => {this.onMicSelected(micId)});

		this.getApplication().initHardware().then(() => {
			this.getDefaultDevices();
		}).catch(() => {
			MessageBox.show({
				message: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_HARDWARE_ERROR'),
				modal: true,
				buttons: MessageBoxButtons.OK
			});
		});

	},
	destroyed()
	{
		// do not stop local media stream, because it is required in the controller
		this.mediaStream = null;
	},
	computed:
	{
		noVideoText()
		{
			if (this.gettingVideo)
			{
				return this.localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_GETTING_CAMERA'];
			}

			if (this.userDisabledCamera)
			{
				return this.localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_DISABLED_CAMERA'];
			}

			return this.localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_VIDEO'];
		},
		localize()
		{
			return BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_CHECK_DEVICES_');
		},
		cameraVideoClasses()
		{
			return {
				'bx-im-component-call-check-devices-camera-video' : true,
				'bx-im-component-call-check-devices-camera-video-flipped' : this.isFlippedVideo
			};
		},
	},
	methods:
	{
		getDefaultDevices()
		{
			this.gettingVideo = true;
			const constraints = {audio: true, video: true};

			if (!Utils.device.isMobile())
			{
				constraints.video = {};
				constraints.video.width = {ideal: /*BX.Call.Hardware.preferHdQuality*/  true ? 1280 : 640};
				constraints.video.height = {ideal: /*BX.Call.Hardware.preferHdQuality*/ true ? 720 : 360};
			}

			if (BX.Call.Hardware.defaultCamera)
			{
				this.selectedCamera = BX.Call.Hardware.defaultCamera;
				constraints.video = {deviceId: { exact: this.selectedCamera }};
			}
			else if (Object.keys(BX.Call.Hardware.cameraList).length === 0)
			{
				constraints.video = false;
			}

			if (BX.Call.Hardware.defaultMicrophone)
			{
				this.selectedMic = BX.Call.Hardware.defaultMicrophone;
				constraints.audio = {deviceId: { exact: this.selectedMic }};
			}

			navigator.mediaDevices.getUserMedia(constraints)
				.then(stream => {
					this.gettingVideo = false;
					this.setLocalStream(stream);
					if (stream.getVideoTracks().length > 0)
					{
						if (!this.selectedCamera)
						{
							this.selectedCamera = stream.getVideoTracks()[0].getSettings().deviceId;
						}
						this.noVideo = false;
						this.playLocalVideo();
						this.getApplication().setSelectedCamera(this.selectedCamera);
					}
					if (stream.getAudioTracks().length > 0)
					{
						if (!this.selectedMic)
						{
							this.selectedMic = stream.getAudioTracks()[0].getSettings().deviceId;
						}
						this.getApplication().setSelectedMic(this.selectedMic);
					}
				})
				.catch(e => {
					this.gettingVideo = false;
					Logger.warn('Error getting default media stream', e);
				});
		},
		getLocalStream()
		{
			this.gettingVideo = true;
			if (Type.isNil(this.selectedCamera) && Type.isNil(this.selectedMic))
			{
				return false;
			}

			const constraints = {video: false, audio: false};
			if (this.selectedCamera && !this.noVideo)
			{
				constraints.video = {deviceId: { exact: this.selectedCamera }};
				if (!Utils.device.isMobile() )
				{
					constraints.video.width = {ideal: /*BX.Call.Hardware.preferHdQuality*/  true ? 1280 : 640};
					constraints.video.height = {ideal: /*BX.Call.Hardware.preferHdQuality*/ true ? 720 : 360};
				}
			}
			if (this.selectedMic)
			{
				constraints.audio = { deviceId: { exact: this.selectedMic } };
			}

			navigator.mediaDevices.getUserMedia(constraints).then(stream => {
				this.gettingVideo = false;
				this.setLocalStream(stream);
				if (stream.getVideoTracks().length > 0)
				{
					this.playLocalVideo();
				}
			}).catch(error => {
				this.gettingVideo = false;
				Logger.warn('Getting video from camera error', error);
				this.noVideo = true;
				this.getApplication().setCameraState(false);
			});
		},
		setLocalStream(stream)
		{
			this.mediaStream = stream;
			this.getApplication().setLocalVideoStream(this.mediaStream);
		},
		playLocalVideo()
		{
			Logger.warn('playing local video');
			this.noVideo = false;
			this.userDisabledCamera = false;
			this.getApplication().setCameraState(true);
			this.$refs['video'].volume = 0;
			this.$refs['video'].srcObject = this.mediaStream;
			this.$refs['video'].play();
		},
		stopLocalVideo()
		{
			if(!this.mediaStream)
			{
				return;
			}
			this.mediaStream.getTracks().forEach(tr => tr.stop());
			this.mediaStream = null;
			this.getApplication().stopLocalVideoStream();
		},
		onCameraSelected(cameraId)
		{
			this.stopLocalVideo();
			this.selectedCamera = cameraId;
			this.getLocalStream();
		},
		onMicSelected(micId)
		{
			/*this.stopLocalVideo();
			this.selectedMic = micId;
			this.getLocalStream();*/
		},
		onCameraStateChange(state)
		{
			if (state)
			{
				this.noVideo = false;
				this.getLocalStream();
			}
			else
			{
				this.stopLocalVideo();
				this.userDisabledCamera = true;
				this.noVideo = true;
				this.getApplication().setCameraState(false);
			}
		},
		onMicStateChange(state)
		{
			if (state)
			{
				this.getLocalStream();
			}

			this.showMic = state;
		},
		isMobile()
		{
			return Utils.device.isMobile();
		},
		getApplication()
		{
			return this.$Bitrix.Application.get();
		},
	},
	components:
		{ MicLevel },
	template: `
	<div class="bx-im-component-call-device-check-container">
		<div class="bx-im-component-call-check-devices">
			<div v-show="noVideo">
				<div class="bx-im-component-call-check-devices-camera-no-video">
					<div class="bx-im-component-call-check-devices-camera-no-video-icon"></div>
					<div class="bx-im-component-call-check-devices-camera-no-video-text">{{ noVideoText }}</div>
				</div>
			</div>
			<div v-show="!noVideo">
				<div class="bx-im-component-call-check-devices-camera-video-container">
					<video :class="cameraVideoClasses" ref="video" muted autoplay playsinline></video>
				</div>
			</div>
			<template v-if="!isMobile()">
				<mic-level v-show="showMic" :localStream="mediaStream"/>
			</template>
		</div>
	</div>
	`
};

export {CheckDevices};