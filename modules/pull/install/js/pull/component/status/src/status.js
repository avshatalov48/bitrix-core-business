/**
 * Bitrix UI
 * Pull connection status Vue component
 *
 * @package bitrix
 * @subpackage pull
 * @copyright 2001-2019 Bitrix
 */

import 'ui.design-tokens';
import "./status.css";
import {BitrixVue} from "ui.vue";
import {PullClient} from "pull.client";

BitrixVue.component('bx-pull-component-status',
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

		if (this.$Bitrix.PullClient.get())
		{
			this.subscribe();
		}
		this.$Bitrix.eventEmitter.subscribe(BitrixVue.events.pullClientChange, () => this.subscribe());

		window.component = this;
	},
	beforeDestroy()
	{
		this.pullUnSubscribe();
	},
	methods:
	{
		subscribe()
		{
			this.pullUnSubscribe();
			this.pullUnSubscribe = this.$Bitrix.PullClient.get().subscribe({
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
		},
		isMobile()
		{
			return navigator.userAgent.toLowerCase().includes('android')
			|| navigator.userAgent.toLowerCase().includes('webos')
			|| navigator.userAgent.toLowerCase().includes('iphone')
			|| navigator.userAgent.toLowerCase().includes('ipad')
			|| navigator.userAgent.toLowerCase().includes('ipod')
			|| navigator.userAgent.toLowerCase().includes('blackberry')
			|| navigator.userAgent.toLowerCase().includes('windows phone')
		}
	},
	watch:
	{
		status()
		{
			clearTimeout(this.hideTimeout);
			if (this.status === PullClient.PullStatus.Online)
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
			return BitrixVue.getFilteredPhrases('BX_PULL_STATUS_', this);
		}
	},
	template: `
		<div v-if="!isMobile()" :class="['bx-pull-status', connectionClass]">
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