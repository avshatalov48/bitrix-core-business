/**
 * Bitrix Videoconf
 * Check devices component (Vue component)
 *
 * @package bitrix
 * @subpackage imopenlines
 * @copyright 2001-2019 Bitrix
 */

import {Vue} from "ui.vue";
import {MicLevel} from './utils/mic-level';

import 'ui.forms';

Vue.component('bx-im-component-call-check-devices',
	{
		data()
		{
			return {
				cameraList: [],
				microphoneList: [],
				audioOutputList: [],
				defaultDevices: {
					camera: 0,
					microphone: 0,
					audioOutput: 0
				},
				currentlySelected: {
					camera: 0,
					microphone: 0,
					audioOutput: 0
				},
				defaultOptions: {
					preferHDQuality: true,
					enableMicAutoParameters: true
				},
				selectedOptions: {
					preferHDQuality: true,
					enableMicAutoParameters: true
				},
				noVideo: true
			}
		},
		created()
		{
			if (BX.Call.Hardware)
			{
				this.cameraList = BX.Call.Hardware.cameraList;
				this.microphoneList = BX.Call.Hardware.microphoneList;
				this.audioOutputList = BX.Call.Hardware.audioOutputList;

				this.defaultOptions.preferHDQuality = BX.Call.Hardware.preferHdQuality;
				this.selectedOptions.preferHDQuality = this.defaultOptions.preferHDQuality;
				this.defaultOptions.enableMicAutoParameters = BX.Call.Hardware.enableMicAutoParameters;
				this.selectedOptions.enableMicAutoParameters = this.defaultOptions.enableMicAutoParameters;
			}

			this.getDefaultDevices();

			this.getVideoFromCamera(this.currentCamera);
		},
		watch:
		{
			currentCamera()
			{
				this.getVideoFromCamera(this.currentCamera);
			}
		},
		computed:
		{
			currentCamera()
			{
				return this.currentlySelected.camera;
			},
			localize()
			{
				return Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_CHECK_DEVICES_', this.$root.$bitrixMessages);
			},
		},
		methods:
		{
			getDefaultDevices()
			{
				if (BX.Call.Hardware.defaultCamera)
				{
					this.defaultDevices.camera = BX.Call.Hardware.defaultCamera;
					this.currentlySelected.camera = this.defaultDevices.camera;
				}
				else
				{
					navigator.mediaDevices.getUserMedia({video: true})
					.then(stream => {
						if (stream && stream.getVideoTracks()[0])
						{
							this.noVideo = false;
							this.defaultDevices.camera = stream.getVideoTracks()[0].getSettings().deviceId;
							this.currentlySelected.camera = this.defaultDevices.camera;
						}
					})
					.catch(e => {
						console.warn('error getting default video', e);
					})
				}

				if (BX.Call.Hardware.defaultMicrophone)
				{
					this.defaultDevices.microphone = BX.Call.Hardware.defaultMicrophone;
					this.currentlySelected.microphone = this.defaultDevices.microphone;
				}
				else
				{
					navigator.mediaDevices.getUserMedia({audio: true})
					.then(stream => {
						if (stream && stream.getAudioTracks()[0])
						{
							this.defaultDevices.microphone = stream.getAudioTracks()[0].getSettings().deviceId;
							this.currentlySelected.microphone = this.defaultDevices.microphone;
						}
					})
					.catch(e => {
						console.warn('error getting default audio', e);
					})
				}

				if (BX.Call.Hardware.defaultSpeaker)
				{
					this.defaultDevices.audioOutput = BX.Call.Hardware.defaultSpeaker;
					this.currentlySelected.audioOutput = this.defaultDevices.audioOutput;
				}
			},
			getVideoFromCamera(id)
			{
				if (id === 0)
				{
					return false;
				}

				navigator.mediaDevices.getUserMedia({
					video: {
						deviceId: { exact: id },
						width: 450,
						height: 338
					}
				}).then(stream => {
					this.$refs['video'].volume = 0;
					this.$refs['video'].srcObject = stream;
					this.$refs['video'].play();
				}).catch(e => {
					console.warn('getting video from camera error', e);
				});
			},
			save()
			{
				let changedValues = {};

				if (this.currentlySelected.camera !== this.defaultDevices.camera)
				{
					changedValues['camera'] = this.currentlySelected.camera;
				}

				if (this.currentlySelected.microphone !== this.defaultDevices.microphone)
				{
					changedValues['microphone'] = this.currentlySelected.microphone;
				}

				if (this.currentlySelected.audioOutput !== this.defaultDevices.audioOutput)
				{
					changedValues['audioOutput'] = this.currentlySelected.audioOutput;
				}

				if (this.selectedOptions.preferHDQuality !== this.defaultOptions.preferHDQuality)
				{
					changedValues['preferHDQuality'] = this.selectedOptions.preferHDQuality;
				}

				if (this.selectedOptions.enableMicAutoParameters !== this.defaultOptions.enableMicAutoParameters)
				{
					changedValues['enableMicAutoParameters'] = this.selectedOptions.enableMicAutoParameters;
				}

				if (!this.isEmptyObject(changedValues))
				{
					this.$emit('save', changedValues);
				}
			},
			exit()
			{
				this.$emit('exit');
			},
			isEmptyObject(obj)
			{
				return Object.keys(obj).length === 0;
			}
		},
		components:
			{ MicLevel },
		template: `
		<div class="bx-im-component-call-check-devices">
			<h3 class="bx-im-component-call-check-devices-title">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_VIDEO_SETTINGS'] }}</h3>
			<!-- Camera select -->
			<div class="bx-im-component-call-check-devices-row bx-im-component-call-check-devices-camera-wrap">
				<div class="bx-im-component-call-check-devices-select-label">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_CAMERA'] }}</div>
				<div class="bx-im-component-call-check-devices-camera-select ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<select v-model="currentlySelected.camera" class="ui-ctl-element">
						<template v-if="isEmptyObject(cameraList)">
							<option disabled value="0">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_CAMERA'] }}</option>
						</template>
						<template v-else>
							<option disabled value="0">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_CHOOSE_CAMERA'] }}</option>
							<option v-for="(camera, id) in cameraList" :value="id" :key="id">{{ camera }}</option>
						</template>
					</select>
				</div>
			</div>
			<!-- Video box -->
			<template v-if="noVideo">
				<div class="bx-im-component-call-check-devices-camera-no-video">
					<div>{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_VIDEO'] }}</div>
				</div>
			</template>
			<template v-else>
				<video ref="video" class="bx-im-component-call-check-devices-camera-video"></video>
			</template>
			<!-- Receive HD -->
			<div class="bx-im-component-call-check-devices-row bx-im-component-call-check-devices-option-hd">
				<label class="ui-ctl ui-ctl-checkbox">
					<input type="checkbox" v-model="selectedOptions.preferHDQuality" id="video_hd" class="ui-ctl-element"/>
					<div class="ui-ctl-label-text">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_RECEIVE_HD'] }}</div>
				</label>
			</div>
			
			<h3 class="bx-im-component-call-check-devices-title">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_AUDIO_SETTINGS'] }}</h3>
			<!-- Mic select -->
			<div class="bx-im-component-call-check-devices-row bx-im-component-call-check-devices-micro-wrap">
				<div class="bx-im-component-call-check-devices-select-label">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_MICRO'] }}</div>
				<div class="bx-im-component-call-check-devices-micro-select ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<select v-model="currentlySelected.microphone" class="ui-ctl-element">
						<template v-if="isEmptyObject(microphoneList)">
							<option disabled value="0">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_MICRO'] }}</option>
						</template>
						<template v-else>
							<option disabled value="0">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_CHOOSE_MICRO'] }}</option>
							<option v-for="(microphone, id) in microphoneList" :value="id" :key="id">{{ microphone }}</option>
						</template>
					</select>
				</div>
			</div>
			<!-- Mic Level -->
			<mic-level :micId="currentlySelected.microphone"/>
			<!-- Auto mic options -->
			<div class="bx-im-component-call-check-devices-row bx-im-component-call-check-devices-option-auto-mic">				
				<label class="ui-ctl ui-ctl-checkbox">
					<input type="checkbox" v-model="selectedOptions.enableMicAutoParameters" id="micro_auto_settings" class="ui-ctl-element"/>
					<div class="ui-ctl-label-text">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_AUTO_MIC_OPTIONS'] }}</div>
				</label>
			</div>
			<!-- Output select -->
			<div class="bx-im-component-call-check-devices-row bx-im-component-call-check-devices-output-wrap">
				<div class="bx-im-component-call-check-devices-select-label">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_SPEAKER'] }}</div>
				<div class="bx-im-component-call-check-devices-output-select ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
					<select v-model="currentlySelected.audioOutput" class="ui-ctl-element">
						<template v-if="isEmptyObject(audioOutputList)">
							<option disabled value="0">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_SPEAKER'] }}</option>
						</template>
						<template v-else>
							<option disabled value="0">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_CHOOSE_SPEAKER'] }}</option>
							<option v-for="(output, id) in audioOutputList" :value="id" :key="id">{{ output }}</option>
						</template>
					</select>
				</div>
			</div>
			<!-- Buttons -->
			<div class="bx-im-component-call-check-devices-row bx-im-component-call-check-devices-buttons">
				<button @click="save" class="ui-btn ui-btn-sm ui-btn-success-dark ui-btn-no-caps bx-im-component-call-check-devices-button-back">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_BUTTON_SAVE'] }}</button>
				<button @click="exit" class="ui-btn ui-btn-sm ui-btn-no-caps bx-im-component-call-check-devices-button-back">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_BUTTON_BACK'] }}</button>
			</div>
		</div>
	`
	});