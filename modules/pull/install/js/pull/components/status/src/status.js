/**
 * Bitrix UI
 * Pull connection status Vue component
 *
 * @package bitrix
 * @subpackage pull
 * @copyright 2001-2019 Bitrix
 */

import "./status.css";
import {Vue} from "ui.vue";
import {PullClient} from "pull.client";

Vue.component('bx-pull-status',
{
	/**
	 * @emits 'reconnect' {} - work only with props.canReconnect = true
	 */
	props:
	{
		canReconnect: { default: false }
	},
	data()
	{
		return {
			status: PullClient.PullStatus.Online,
			showed: null
		}
	},
	created()
	{
		this.isMac = navigator.userAgent.toLowerCase().includes('macintosh');

		this.setStatusTimeout = null;
		this.hideTimeout = null;

		this.pullUnSubscribe = () => {};

		if (typeof this.$root.$bitrixPullClient !== 'undefined')
		{
			if (this.$root.$bitrixPullClient)
			{
				this.subscribe(this.$root.$bitrixPullClient);
			}
			else
			{
				this.$root.$on('onBitrixPullClientInited', () => {
					this.subscribe(this.$root.$bitrixPullClient);
				});
			}
		}
		else if (typeof BX.PULL !== 'undefined')
		{
			this.subscribe(BX.PULL);
		}

		window.component = this;
	},
	beforeDestroy()
	{
		this.pullUnSubscribe();
	},
	methods:
	{
		subscribe(client)
		{
			this.pullUnSubscribe = client.subscribe({
				type: PullClient.SubscriptionType.Status,
				callback: event => this.statusChange(event.status)
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
		statusChange(status)
		{
			clearTimeout(this.setStatusTimeout);

			if (this.status === status)
			{
				return false;
			}

			let validStatus = [
				PullClient.PullStatus.Online,
				PullClient.PullStatus.Offline,
				PullClient.PullStatus.Connecting
			];
			if (validStatus.indexOf(status) < 0)
			{
				return false;
			}

			let timeout = 500;

			if (status === PullClient.PullStatus.Connecting)
			{
				timeout = 5000;
			}
			else if (status === PullClient.PullStatus.Offline)
			{
				timeout = 2000;
			}

			this.setStatusTimeout = setTimeout(() => {
				this.status = status;
				this.showed = true;
			}, timeout);

			return true;
		}
	},
	watch:
	{
		status()
		{
			clearTimeout(this.hideTimeout);
			if (this.status == PullClient.PullStatus.Online)
			{
				clearTimeout(this.hideTimeout);
				this.hideTimeout = setTimeout(() => this.showed = false, 4000);
			}
		}
	},
	computed:
	{
		connectionClass()
		{
			let result = '';
			if (this.showed === true)
			{
				result = "bx-pull-status-show";
			}
			else if (this.showed === false)
			{
				result = "bx-pull-status-hide";
			}

			if (this.status === PullClient.PullStatus.Online)
			{
				result += " bx-pull-status-online";
			}
			else if (this.status === PullClient.PullStatus.Offline)
			{
				result += " bx-pull-status-offline";
			}
			else if (this.status === PullClient.PullStatus.Connecting)
			{
				result += " bx-pull-status-connecting";
			}

			return result;
		},
		connectionText()
		{
			let result = '';

			if (this.status === PullClient.PullStatus.Online)
			{
				result = this.localize.BX_PULL_STATUS_ONLINE;
			}
			else if (this.status === PullClient.PullStatus.Offline)
			{
				result = this.localize.BX_PULL_STATUS_OFFLINE;
			}
			else if (this.status === PullClient.PullStatus.Connecting)
			{
				result = this.localize.BX_PULL_STATUS_CONNECTING;
			}

			return result;
		},
		button()
		{
			let hotkey = '';
			let name = '';

			if (this.canReconnect)
			{
				name = this.localize.BX_PULL_STATUS_BUTTON_RECONNECT;
			}
			else
			{
				hotkey = this.isMac? '&#8984;+R': "Ctrl+R";
				name = this.localize.BX_PULL_STATUS_BUTTON_RELOAD;
			}

			return {title: name, key: hotkey};
		},
		localize()
		{
			return Vue.getFilteredPhrases('BX_PULL_STATUS_', this.$root.$bitrixMessages);
		}
	},
	template: `
		<div :class="['bx-pull-status', connectionClass]">
			<div class="bx-pull-status-wrap">
				<span class="bx-pull-status-text">{{connectionText}}</span>
				<span class="bx-pull-status-button" @click="reconnect">
					<span class="bx-pull-status-button-title">{{button.title}}</span>
					<span class="bx-pull-status-button-key" v-html="button.key"></span>
				</span>
			</div>
		</div>
	`
});