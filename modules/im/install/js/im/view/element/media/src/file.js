/**
 * Bitrix Messenger
 * File element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import './file.css';
import 'ui.icons';

import {Uploader} from "ui.progressbarjs.uploader";

import {BitrixVue} from 'ui.vue';
import {Vuex} from "ui.vue.vuex";
import {FilesModel} from 'im.model';
import { FileType, FileStatus, MessageType, EventType } from 'im.const';
import {Utils} from "im.lib.utils";
import { EventEmitter } from "main.core.events";

BitrixVue.component('bx-im-view-element-file',
{
	/*
	 * @emits EventType.dialog.clickOnUploadCancel {file: object, event: MouseEvent}
	 */

	mounted()
	{
		this.createProgressbar();
	},
	beforeDestroy()
	{
		this.removeProgressbar();
	},
	props:
	{
		userId: { default: 0 },
		messageType: { default: MessageType.self },
		file: {
			type: Object,
			required: true
		},
	},
	methods:
	{
		download(file)
		{
			if (file.progress !== 100)
			{
				return false;
			}

			if (BX.UI && BX.UI.Viewer && Object.keys(file.viewerAttrs).length > 0)
			{
				return false;
			}

			if (file.type === FileType.image && file.urlShow)
			{
				if (Utils.platform.isBitrixMobile())
				{
					BXMobileApp.UI.Photo.show({
						photos: this.files.collection[this.application.dialog.chatId].filter(file => file.type === 'image').map(file => {return {url: file.urlShow.replace('bxhttp', 'http')}}).reverse(),
						default_photo: file.urlShow.replace('bxhttp', 'http')
					})
				}
				else
				{
					window.open(file.urlShow, '_blank');
				}
			}
			else if (file.type === FileType.video && file.urlShow)
			{
				if (Utils.platform.isBitrixMobile())
				{
					app.openDocument({url: file.urlShow, name: file.name});
				}
				else
				{
					window.open(file.urlShow, '_blank');
				}
			}
			else if (file.urlDownload)
			{
				if (Utils.platform.isBitrixMobile())
				{
					app.openDocument({url: file.urlDownload, name: file.name});
				}
				else
				{
					window.open(file.urlDownload, '_blank');
				}
			}
			else
			{
				if (Utils.platform.isBitrixMobile())
				{
					app.openDocument({url: file.urlShow, name: file.name});
				}
				else
				{
					window.open(file.urlShow, '_blank');
				}
			}
		},
		createProgressbar()
		{
			if (this.uploader)
			{
				return true;
			}

			if (this.file.progress === 100)
			{
				return false;
			}

			let blurElement = undefined;

			if (
				this.file.progress < 0
				|| this.file.type !== FileType.image && this.file.type !== FileType.video
			)
			{
				blurElement = false;
			}

			this.uploader = new Uploader({
				container: this.$refs.container,
				blurElement,
				direction: this.$refs.container.offsetHeight > 54? Uploader.direction.vertical: Uploader.direction.horizontal,
				icon: this.file.progress < 0? Uploader.icon.cloud: Uploader.icon.cancel,
				sizes: {
					circle: this.$refs.container.offsetHeight > 54? 54: 38,
					progress: this.$refs.container.offsetHeight > 54? 4: 8,
				},
				labels: {
					loading: this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_LOADING'],
					completed: this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_COMPLETED'],
					canceled: this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_CANCELED'],
					cancelTitle: this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_CANCEL_TITLE'],
					megabyte: this.localize['IM_MESSENGER_ELEMENT_FILE_SIZE_MB'],
				},
				cancelCallback: this.file.progress < 0? null: (event) => {
					EventEmitter.emit(EventType.dialog.clickOnUploadCancel, {file: this.file, event});
				},
				destroyCallback: () => {
					if (this.uploader)
					{
						this.uploader = null;
					}
				}
			});

			this.uploader.start();

			if(
				this.file.size && (this.file.size/1024/1024) <= 2
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

			if (this.file.status === FileStatus.error)
			{
				this.uploader.setProgress(0);
				this.uploader.setCancelDisable(false);
				this.uploader.setIcon(Uploader.icon.error);
				this.uploader.setProgressTitle(this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_ERROR']);
			}
			else if (this.file.status === FileStatus.wait)
			{
				this.uploader.setProgress(this.file.progress > 5? this.file.progress: 5);
				this.uploader.setCancelDisable(true);
				this.uploader.setIcon(Uploader.icon.cloud);
				this.uploader.setProgressTitle(this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_SAVING']);
			}
			else if (this.file.progress === 100)
			{
				this.uploader.setProgress(100);
			}
			else if (this.file.progress === -1)
			{
				this.uploader.setProgress(10);
				this.uploader.setProgressTitle(this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_WAITING']);
			}
			else
			{
				if (this.file.progress === 0)
				{
					this.uploader.setIcon(Uploader.icon.cancel);
				}
				let progress = this.file.progress > 5? this.file.progress: 5;

				this.uploader.setProgress(progress);

				if((this.file.size/1024/1024) <= 2)
				{
					this.uploader.setProgressTitle(this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_LOADING']);
				}
				else
				{
					this.uploader.setByteSent(this.file.size/100*this.file.progress, this.file.size);
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
		FileStatus: () => FileStatus,
		localize()
		{
			return BitrixVue.getFilteredPhrases('IM_MESSENGER_ELEMENT_FILE_', this);
		},
		fileName()
		{
			let maxLength = 70;

			if (this.file.name.length < maxLength)
			{
				return this.file.name;
			}

			let endWordLength = 10;

			let secondPart = this.file.name.substring(this.file.name.length-1 - (this.file.extension.length+1+endWordLength));
			let firstPart = this.file.name.substring(0, maxLength-secondPart.length-3);

			return firstPart.trim()+'...'+secondPart.trim();
		},
		fileSize()
		{
			let size = this.file.size;

			if (size <= 0)
			{
				return '&nbsp;';
			}

			let sizes = ["BYTE", "KB", "MB", "GB", "TB"];
			let position = 0;

			while (size >= 1024 && position < 4)
			{
				size /= 1024;
				position++;
			}

			return Math.round(size) + " " + this.localize['IM_MESSENGER_ELEMENT_FILE_SIZE_'+sizes[position]];
		},
		uploadProgress()
		{
			return this.file.status+' '+this.file.progress;
		},
		...Vuex.mapState({
			application: state => state.application,
			files: state => state.files,
		})
	},
	watch:
	{
		uploadProgress()
		{
			this.updateProgressbar();
		},
	},
	template: `
		<div class="bx-im-element-file" @click="download(file, $event)" ref="container">
			<div class="bx-im-element-file-icon">
				<div :class="['ui-icon', 'ui-icon-file-'+file.icon]"><i></i></div>
			</div>
			<div class="bx-im-element-file-block">
				<div class="bx-im-element-file-name" :title="file.name">
					{{fileName}}
				</div>
				<div class="bx-im-element-file-size" v-html="fileSize"></div>
			</div>
		</div>
	`
});