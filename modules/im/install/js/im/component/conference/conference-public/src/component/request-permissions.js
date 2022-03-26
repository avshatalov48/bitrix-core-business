import { BitrixVue } from "ui.vue";
import { MessageBox, MessageBoxButtons } from "ui.dialogs.messagebox";
import { EventEmitter } from "main.core.events";
import { EventType } from "im.const";

const NOT_ALLOWED_ERROR_CODE = 'NotAllowedError';
const NOT_FOUND_ERROR_CODE = 'NotFoundError';

const RequestPermissions = {
	props: {
		skipRequest: {
			type: Boolean,
			required: false,
			default: false
		}
	},
	created()
	{
		EventEmitter.subscribe(EventType.conference.requestPermissions, this.onRequestPermissions);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.conference.requestPermissions, this.onRequestPermissions);
	},
	computed:
	{
		localize()
		{
			return BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
		}
	},
	methods:
	{
		onRequestPermissions()
		{
			this.requestPermissions();
		},
		requestPermissions()
		{
			this.getApplication().initHardware().then(() => {
				return navigator.mediaDevices.getUserMedia({audio: true, video: true});
			}).then(() => {
				this.setPermissionsRequestedFlag();
			}).catch((error) => {
				if (error.name === NOT_ALLOWED_ERROR_CODE)
				{
					this.showMessageBox(this.localize['BX_IM_COMPONENT_CALL_NOT_ALLOWED_ERROR']);

					return false;
				}
				else if (error.name === NOT_FOUND_ERROR_CODE)
				{
					// means there is no camera, request only microphone
					return navigator.mediaDevices.getUserMedia({audio: true, video: false}).then(() => {
						this.setPermissionsRequestedFlag();
					}).catch((error) => {
						if (error.name === NOT_ALLOWED_ERROR_CODE)
						{
							this.showMessageBox(this.localize['BX_IM_COMPONENT_CALL_NOT_ALLOWED_ERROR']);

							return false;
						}
					});
				}

				this.showMessageBox(this.localize['BX_IM_COMPONENT_CALL_HARDWARE_ERROR']);
			});
		},
		setPermissionsRequestedFlag()
		{
			this.$nextTick(() => this.$store.dispatch('conference/setPermissionsRequested', {status: true}));
		},
		showMessageBox(text)
		{
			MessageBox.show({
				message: text,
				modal: true,
				buttons: MessageBoxButtons.OK
			});
		},
		getApplication()
		{
			return this.$Bitrix.Application.get();
		}
	},
	// language=Vue
	template: `
		<div class="bx-im-component-call-permissions-container">
			<template v-if="!skipRequest">
				<div class="bx-im-component-call-permissions-text">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_TEXT'] }}</div>
				<button @click="requestPermissions" class="ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-permissions-button">
					{{ localize['BX_IM_COMPONENT_CALL_ENABLE_DEVICES_BUTTON'] }}
				</button>
				<slot></slot>
			</template>
			<template v-else>
				<div class="bx-im-component-call-permissions-text">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_LOADING'] }}</div>
				<button class="ui-btn ui-btn-sm ui-btn-wait bx-im-component-call-permissions-button">
					{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_BUTTON'] }}
				</button>
			</template>
		</div>
	`
};

export {RequestPermissions};