/**
 * Bitrix Messenger
 * File element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2021 Bitrix
 */

import {BitrixVue} from 'ui.vue';
import {Uploader} from "ui.progressbarjs.uploader";
import {FileStatus} from "im.const";

export const CallBackgroundItem =
{
	props:
	{
		selected: {
			type: Boolean,
			default: false
		},
		item: {
			type: Object,
			default: {}
		},
	},
	mounted()
	{
		this.createProgressbar();
	},
	beforeDestroy()
	{
		this.removeProgressbar();
	},
	methods:
	{
		createProgressbar()
		{
			if (this.uploader)
			{
				return true;
			}

			if (!this.item.state)
			{
				return true;
			}

			if (this.item.state.progress === 100)
			{
				return false;
			}

			this.uploader = new Uploader({
				container: this.$refs.container,
				labels: {
					loading: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_LOADING'],
					completed: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_COMPLETED'],
					canceled: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_CANCELED'],
					cancelTitle: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_CANCEL_TITLE'],
					megabyte: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_SIZE_MB'],
				},
				cancelCallback: this.item.state.progress < 0? null: (event) =>
				{
					this.$emit('cancel', {item: this.item, event});
				},
				destroyCallback: () =>
				{
					if (this.uploader)
					{
						this.uploader = null;
					}
				}
			});

			this.uploader.start();

			if(
				this.item.state.size && (this.item.state.size/1024/1024) <= 2
				|| this.$refs.container.offsetHeight <= 54 && this.$refs.container.offsetWidth < 240
			)
			{
				this.uploader.setProgressTitleVisibility(false)
			}

			this.updateProgressbar();

			return true;
		},
		updateProgressbar()
		{
			if (!this.uploader)
			{
				let result = this.createProgressbar();
				if (!result)
				{
					return false;
				}
			}

			if (this.item.state.status === FileStatus.error)
			{
				this.uploader.setProgress(0);
				this.uploader.setCancelDisable(false);
				this.uploader.setIcon(Uploader.icon.error);
				this.uploader.setProgressTitle(this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_ERROR']);
			}
			else if (this.item.state.status === FileStatus.wait)
			{
				this.uploader.setProgress(this.item.state.progress > 5? this.item.state.progress: 5);
				this.uploader.setCancelDisable(true);
				this.uploader.setIcon(Uploader.icon.cloud);
				this.uploader.setProgressTitle(this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_SAVING']);
			}
			else if (this.item.state.progress === 100)
			{
				this.uploader.setProgress(100);
			}
			else if (this.item.state.progress === -1)
			{
				this.uploader.setProgress(10);
				this.uploader.setProgressTitle(this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_WAITING']);
			}
			else
			{
				if (this.item.state.progress === 0)
				{
					this.uploader.setIcon(Uploader.icon.cancel);
				}
				let progress = this.item.state.progress > 5? this.item.state.progress: 5;

				this.uploader.setProgress(progress);

				if((this.item.state.size/1024/1024) <= 2)
				{
					this.uploader.setProgressTitle(this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_LOADING']);
				}
				else
				{
					this.uploader.setByteSent(this.item.state.size/100*this.item.state.progress, this.item.state.size);
				}
			}
		},
		removeProgressbar()
		{
			if (!this.uploader)
			{
				return true;
			}

			this.uploader.destroy(false);

			return true;
		}
	},
	computed:
	{
		uploadProgress()
		{
			if (!this.item.state)
			{
				return '';
			}

			return this.item.state.status+' '+this.item.state.progress;
		},
		localize()
		{
			return BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_SETTINGS_CALL_BG_', this);
		},
	},
	watch:
	{
		uploadProgress()
		{
			this.updateProgressbar();
		},
	},
	template: `
		<div :key="item.id" @click="$emit('select')" :class="['bx-im-settings-video-background-dialog-item', {'bx-im-settings-video-background-dialog-item-selected': selected, 'bx-im-settings-video-background-dialog-item-unsupported': !item.isSupported , 'bx-im-settings-video-background-dialog-item-loading': item.isLoading }]" ref="container">
			<div class="bx-im-settings-video-background-dialog-item-image" :style="{backgroundImage: item.preview? 'url('+item.preview+')': ''}"></div>
			<div v-if="item.isSupported && item.isVideo" class="bx-im-settings-video-background-dialog-item-video"></div>
			<div v-if="!item.isLoading" class="bx-im-settings-video-background-dialog-item-title">
				<span class="bx-im-settings-video-background-dialog-item-title-text">{{item.title}}</span>
				<div v-if="item.canRemove" class="bx-im-settings-video-background-dialog-item-remove" :title="localize.BX_IM_COMPONENT_SETTINGS_CALL_BG_REMOVE" @click="$emit('remove')"></div>
			</div>
		</div>
	`
};