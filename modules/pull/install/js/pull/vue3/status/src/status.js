import 'ui.design-tokens';
import { BitrixVue } from 'ui.vue3';
import { PullClient } from 'pull.client';
import { Browser } from 'main.core';

import './status.css';

const HIDE_TIMEOUT = 4000;
const STATUS_CHANGE_DEFAULT_TIMEOUT = 500;
const STATUS_CHANGE_CONNECTING_TIMEOUT = 5000;
const STATUS_CHANGE_OFFLINE_TIMEOUT = 1000;

// @vue/component
export const PullStatus = {
	name: 'PullStatus',
	props:
	{
		canReconnect: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['reconnect'],
	data(): Object
	{
		return {
			status: PullClient.PullStatus.Online,
			showed: null,
		};
	},
	computed:
	{
		containerClass(): string[]
		{
			const result = [];

			let visibilityClass = '';
			if (this.showed === true)
			{
				visibilityClass = '--show';
			}
			else if (this.showed === false)
			{
				visibilityClass = '--hide';
			}
			const statusClass = `--${this.status}`;
			result.push(visibilityClass, statusClass);

			return result;
		},
		connectionText(): string
		{
			let result = '';

			if (this.status === PullClient.PullStatus.Online)
			{
				result = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_ONLINE');
			}
			else if (this.status === PullClient.PullStatus.Offline)
			{
				result = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_OFFLINE');
			}
			else if (this.status === PullClient.PullStatus.Connecting)
			{
				result = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_CONNECTING');
			}

			return result;
		},
		button(): ?Object
		{
			if (this.status === PullClient.PullStatus.Online)
			{
				return null;
			}

			let hotkey = '';
			let name = '';

			if (this.canReconnect)
			{
				name = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_BUTTON_RECONNECT');
			}
			else
			{
				hotkey = Browser.isMac() ? '&#8984;+R' : 'Ctrl+R';
				name = this.$Bitrix.Loc.getMessage('BX_PULL_STATUS_BUTTON_RELOAD');
			}

			return { title: name, key: hotkey };
		},
	},
	watch:
	{
		status()
		{
			clearTimeout(this.hideTimeout);
			if (this.status !== PullClient.PullStatus.Online)
			{
				return;
			}

			this.hideTimeout = setTimeout(() => {
				this.showed = false;
			}, HIDE_TIMEOUT);
		},
	},
	created()
	{
		this.unsubscribeFunction = () => {};
		this.initEvents();
	},
	beforeUnmount()
	{
		this.destroyEvents();
	},
	methods:
	{
		initEvents()
		{
			if (this.$Bitrix.PullClient.get())
			{
				this.subscribeToPullStatus();
			}
			this.$Bitrix.eventEmitter.subscribe(BitrixVue.events.pullClientChange, this.subscribeToPullStatus);
		},
		destroyEvents()
		{
			this.unsubscribeFunction();
			this.$Bitrix.eventEmitter.unsubscribe(BitrixVue.events.pullClientChange, this.subscribeToPullStatus);
		},
		subscribeToPullStatus()
		{
			this.unsubscribeFunction();
			this.unsubscribeFunction = this.$Bitrix.PullClient.get().subscribe({
				type: PullClient.SubscriptionType.Status,
				callback: (event) => this.onStatusChange(event.status),
			});
		},
		reconnect()
		{
			if (this.canReconnect)
			{
				this.$emit('reconnect');
			}
			else
			{
				location.reload();
			}
		},
		onStatusChange(status)
		{
			clearTimeout(this.setStatusTimeout);

			if (this.status === status)
			{
				return;
			}

			const validStatuses = [
				PullClient.PullStatus.Online,
				PullClient.PullStatus.Offline,
				PullClient.PullStatus.Connecting,
			];
			if (!validStatuses.includes(status))
			{
				return;
			}

			let timeout = STATUS_CHANGE_DEFAULT_TIMEOUT;

			if (status === PullClient.PullStatus.Connecting)
			{
				timeout = STATUS_CHANGE_CONNECTING_TIMEOUT;
			}
			else if (status === PullClient.PullStatus.Offline)
			{
				timeout = STATUS_CHANGE_OFFLINE_TIMEOUT;
			}

			this.setStatusTimeout = setTimeout(() => {
				this.status = status;
				this.showed = true;
			}, timeout);
		},
	},
	template: `
		<div class="bx-pull-vue3-status" :class="containerClass">
			<div class="bx-pull-vue3-status-wrap">
				<span class="bx-pull-vue3-status-text">{{ connectionText }}</span>
				<span v-if="button" class="bx-pull-vue3-status-button" @click="reconnect">
					<span class="bx-pull-vue3-status-button-title">{{ button.title }}</span>
					<span class="bx-pull-vue3-status-button-key" v-html="button.key"></span>
				</span>
			</div>
		</div>
	`,
};
