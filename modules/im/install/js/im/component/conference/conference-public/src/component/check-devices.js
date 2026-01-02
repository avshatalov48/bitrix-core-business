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
			isDestroyed: false,
			noVideo: true,
			selectedCamera: null,
			selectedMic: null,
			videoStream: null,
			audioStream: null,
			videoStreamPromise: null,
			audioStreamPromise: null,
			showMic: true,
			userDisabledCamera: false,
			gettingVideo: false,
			isFlippedVideo: BX.Call.Hardware.enableMirroring,
		};
	},
	created()
	{
		this.$root.$on('setCameraState', (state) => {this.onCameraStateChange(state)});
		this.$root.$on('setMicState', (state) => {this.onMicStateChange(state)});
		this.$root.$on('callLocalMediaReceived', () => { this.stopLocalVideo(); this.stopLocalAudio(); });
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
		this.isDestroyed = true;
		this.stopLocalVideo();
		this.stopLocalAudio();
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
			if (BX.Call.Hardware.defaultCamera)
			{
				this.selectedCamera = BX.Call.Hardware.defaultCamera;
			}

			if (BX.Call.Hardware.defaultMicrophone)
			{
				this.selectedMic = BX.Call.Hardware.defaultMicrophone;
			}

			this.getLocalVideoStream().then(() => {
				this.getApplication().updateMediaDevices();
				if (!this.selectedCamera)
				{
					this.selectedCamera = this.videoStream.getVideoTracks()[0].getSettings().deviceId;
				}
				this.getApplication().setSelectedCamera(this.selectedCamera);
			}).catch((error) => {
				Logger.warn('Error getting default video stream', error);
			});

			this.getLocalAudioStream().then(() => {
				if (!this.selectedMic)
				{
					this.selectedMic = this.audioStream.getAudioTracks()[0].getSettings().deviceId;
				}
				this.getApplication().setSelectedMic(this.selectedMic);
			}).catch((error) => {
				Logger.warn('Error getting default audio stream', error);
			});
		},
		getLocalVideoStream()
		{
			if (this.videoStreamPromise)
			{
				return this.videoStreamPromise;
			}

			this.videoStreamPromise = new Promise((resolve, reject) => {
				this.gettingVideo = true;

				const constraints = {
					video: this.getVideoConstraints(),
					audio: false,
				};

				navigator.mediaDevices.getUserMedia(constraints).then((stream) => {
					this.setLocalStream(stream);
					this.playLocalVideo();

					if (this.isDestroyed)
					{
						this.stopLocalVideo();
					}

					resolve();
				}).catch((error) => {
					Logger.warn('Getting video from camera error', error);
					this.noVideo = true;
					this.getApplication().setCameraState(false);
					reject(error);
				}).finally(() => {
					this.gettingVideo = false;
					this.videoStreamPromise = null;
				});
			});

			return this.videoStreamPromise;
		},
		getLocalAudioStream()
		{
			if (this.audioStreamPromise)
			{
				return this.audioStreamPromise;
			}

			this.audioStreamPromise = new Promise((resolve, reject) => {
				const constraints = {
					audio: { deviceId: { exact: this.selectedMic } },
					video: false,
				};

				navigator.mediaDevices.getUserMedia(constraints).then((stream) => {
					this.audioStream = stream;

					if (this.isDestroyed)
					{
						this.stopLocalAudio();
					}

					resolve();
				}).catch((error) => {
					Logger.warn('Getting audio from microphone error', error);
					reject(error);
				}).finally(() => {
					this.audioStreamPromise = null;
				});
			});

			return this.audioStreamPromise;
		},
		setLocalStream(stream)
		{
			this.videoStream = stream;
			this.getApplication().setLocalVideoStream(this.videoStream);
		},
		playLocalVideo()
		{
			Logger.warn('playing local video');
			this.noVideo = false;
			this.userDisabledCamera = false;
			this.getApplication().setCameraState(true);
			this.$refs.video.volume = 0;
			this.$refs.video.srcObject = this.videoStream;
			this.$refs.video.play();
		},
		stopLocalVideo()
		{
			if (!this.videoStream)
			{
				return;
			}
			this.videoStream.getTracks().forEach((track) => track.stop());
			this.videoStream = null;
			this.getApplication().stopLocalVideoStream();
		},
		stopLocalAudio()
		{
			if (!this.audioStream)
			{
				return;
			}
			this.audioStream.getTracks().forEach((track) => track.stop());
			this.audioStream = null;
		},
		onCameraSelected(cameraId)
		{
			this.stopLocalVideo();
			this.selectedCamera = cameraId;
			this.getLocalVideoStream();
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
				this.getLocalVideoStream();
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
				this.getLocalAudioStream();
			}
			else
			{
				this.stopLocalAudio();
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
		getVideoConstraints()
		{
			const videoConstraints = {};

			if (this.selectedCamera)
			{
				videoConstraints.deviceId = { exact: this.selectedCamera };
			}

			if (!Utils.device.isMobile())
			{
				videoConstraints.width = { ideal: 1280 };
				videoConstraints.height = { ideal: 720 };
			}

			return videoConstraints;
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
				<mic-level v-show="showMic" :localStream="audioStream"/>
			</template>
		</div>
	</div>
	`
};

export {CheckDevices};
