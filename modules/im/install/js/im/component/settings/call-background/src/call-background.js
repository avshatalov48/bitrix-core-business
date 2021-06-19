import {ajax as Ajax, Loc} from "main.core";
import {BitrixVue} from "ui.vue";
import {Uploader} from "im.lib.uploader";
import {Utils} from "im.lib.utils";
import {FileStatus} from "im.const";
import "ui.info-helper";
import "ui.notification";

import "./call-background.css";
import {CallBackgroundItem} from "./item-background";

const ActionType = Object.freeze({
	none: 'none',
	upload: 'upload',
	blur: 'blur',
	gaussianBlur: 'gaussianBlur',
});

const LimitCode = Object.freeze({
	blur: 'call_blur_background',
	image: 'call_background',
});

BitrixVue.component('bx-im-component-settings-call-background',
{
	props:
	{
		isDesktop: {type: Boolean, default: false},
		width: {default: 0},
		height: {default: 450},
	},
	data: function()
	{
		return {
			actions: [],
			standard: [],
			custom: [],
			selected: '',
			ActionType: ActionType,
			loading: true,
			diskFolderId: 0
		};
	},
	components:
	{
		'bx-im-component-settings-call-background-item': CallBackgroundItem
	},
	created()
	{
		this.defaultValue = this.isDesktop? window.BX.desktop.getBackgroundImage(): {id: ActionType.none, source: ''};
		this.selected = this.defaultValue.id;
		this.limit = {};

		Ajax.runAction("im.call.getBackground", {
		}).then((response) => {
			this.loading = false;

			this.diskFolderId = response.data.diskFolderId;

			response.data.list.default.forEach(element => {
				element.video = element.id.includes(':video');
				element.custom = false;
				element.canRemove = false;
				element.supported = true;
				this.standard.push(element);
			});

			response.data.list.custom.forEach(element => {
				element.custom = true;
				element.canRemove = true;
				if (element.supported)
				{
					element.title = Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_CUSTOM');
				}
				else
				{
					element.title = Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_UNSUPPORTED');
				}

				this.custom.push(element);
			});

			response.data.limit.forEach(element => {
				this.limit[element.id] = element;
			});

			if (this.diskFolderId)
			{
				this.actions = this.actions.map(element => {
					element.supported = true;
					return element;
				});
			}
			else
			{
				this.actions = this.actions.filter(element => {
					return element.id !== ActionType.upload
				});
			}

			if (!window.BX.UI.InfoHelper.isInited())
			{
				window.BX.UI.InfoHelper.init({
					frameUrlTemplate: response.data.infohelper.frameUrlTemplate
				});
			}

			if (this.isDesktop)
			{
				window.BX.desktop.hideLoader();
			}
		}).catch(() => {
			this.loading = false;
		});

		this.actions.push({
			id: ActionType.none,
			title: Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_ACTION_NONE'),
			source: ActionType.none,
		});
		this.actions.push({
			id: ActionType.upload,
			title: Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_ACTION_UPLOAD'),
		});
		this.actions.push({
			id: ActionType.gaussianBlur,
			title: Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_ACTION_BLUR'),
			source: ActionType.gaussianBlur,
		});
		this.actions.push({
			id: ActionType.blur,
			title: Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_ACTION_BLUR_MAX'),
			source: ActionType.blur,
		});
	},
	mounted()
	{
		this.uploader = new Uploader({
			inputNode: this.$refs.uploadInput,
			generatePreview: true,
			fileMaxSize: 100 * 1024 * 1024,
		});

		this.uploader.subscribe('onFileMaxSizeExceeded', (event) => {
            const eventData = event.getData();
			const file = eventData.file;

			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_SIZE_EXCEEDED').replace('#LIMIT#', 100).replace('#FILE_NAME#', file.name),
				autoHideDelay: 5000
			});
        });

		this.uploader.subscribe('onSelectFile', (event) => {
			const eventData = event.getData();
			const file = eventData.file;

			if (!this.isAllowedType(file.type) || !eventData.previewData)
			{
				BX.UI.Notification.Center.notify({
					content: Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_UNSUPPORTED_FILE').replace('#FILE_NAME#', file.name),
					autoHideDelay: 5000
				});

				return false;
			}

			this.uploader.addTask({
				taskId: `custom:${Date.now()}`,
				chunkSize: 1024 * 1024,
				fileData: file,
				fileName: file.name,
				diskFolderId: this.diskFolderId,
				generateUniqueName: true,
				previewBlob: eventData.previewData,
			});
		});

		this.uploader.subscribe('onStartUpload', event => {
			const eventData = event.getData();

			const filePreview = URL.createObjectURL(eventData.previewData);

			this.custom.unshift({
				id: eventData.id,
				source: filePreview,
				preview: filePreview,
				title: Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_CUSTOM'),
				video: eventData.file.type.startsWith('video'),
				custom: true,
				canRemove: false,
				supported: true,
				loading: true,
				state:
				{
					progress: 0,
					status: FileStatus.upload,
					size: eventData.file.size,
				}
			});
		});

		this.uploader.subscribe('onProgress', (event) => {
			const eventData = event.getData();
			const element = this.custom.find(element => element.id === eventData.id);
			if (!element)
			{
				return;
			}

			element.state.progress = eventData.progress;
		});

		this.uploader.subscribe('onComplete', (event) => {
			const eventData = event.getData();
			const element = this.custom.find(element => element.id === eventData.id);
			if (!element)
			{
				return;
			}

			element.id = eventData.result.data.file.id;

			if (element.video)
			{
				element.source = eventData.result.data.file.links.download;
			}

			element.loading = false;
			element.canRemove = true;

			this.select(element);

			Ajax.runAction('im.call.commitBackground', {data: {
				fileId: element.id
			}});
		});

		this.uploader.subscribe('onUploadFileError', (event) => {
			const eventData = event.getData();
			const element = this.custom.find(element => element.id === eventData.id);
			if (!element)
			{
				return;
			}

			element.state.status = FileStatus.error;
			element.state.progress = 0;
		});

		this.uploader.subscribe('onCreateFileError', (event) => {
			const eventData = event.getData();
			const element = this.custom.find(element => element.id === eventData.id);
			if (!element)
			{
				return;
			}

			element.state.status = FileStatus.error;
			element.state.progress = 0;
		});
	},
	computed:
	{
		containerSize()
		{
			const result = {};

			if (this.isDesktop)
			{
				result.height = 'calc(100vh - 79px)'; // 79 button panel
			}
			else
			{
				result.height = this.height+'px';
			}

			if (this.width > 0)
			{
				result.width = this.width+'px';
			}

			return result
		},
		backgrounds()
		{
			return [].concat(this.custom).concat(this.standard);
		},
		uploadTypes()
		{
			if (Utils.platform.isBitrixDesktop())
			{
				return '';
			}

			return '.png, .jpg, .jpeg, .avi, .mp4';
		},
	},
	methods:
	{
		hasLimit(elementId)
		{
			if (elementId === ActionType.none)
			{
				return true;
			}

			if ([ActionType.blur, ActionType.gaussianBlur].includes(elementId))
			{
				if (
					this.limit[LimitCode.blur]
					&& this.limit[LimitCode.blur].active
					&& this.limit[LimitCode.blur].articleCode
					&& window.BX.UI.InfoHelper
				)
				{

					window.BX.UI.InfoHelper.show(this.limit[LimitCode.blur].articleCode);
					return false;
				}

				return true;
			}

			if (
				this.limit[LimitCode.image]
				&& this.limit[LimitCode.image].active
				&& this.limit[LimitCode.image].articleCode
				&& window.BX.UI.InfoHelper
			)
			{
				window.BX.UI.InfoHelper.show(this.limit[LimitCode.image].articleCode)
				return false;
			}

			return true;
		},

		select(element)
		{
			if (!this.hasLimit(element.id))
			{
				return false;
			}

			if (!element.supported || element.loading)
			{
				return false;
			}

			if (element.id === ActionType.upload)
			{
				this.$refs.uploadInput.click();
				return false;
			}

			this.selected = element.id;

			if (this.isDesktop)
			{
				window.BX.desktop.setBackgroundImage(element.id, element.source);
			}

			return true;
		},

		remove(element)
		{
			if (element.id === this.selected)
			{
				this.selected = ActionType.none;

				if (this.isDesktop)
				{
					window.BX.desktop.setBackgroundImage(ActionType.none, ActionType.none);
				}
			}

			if (element.loading)
			{
				this.uploader.deleteTask(element.id);
			}
			else
			{
				Ajax.runAction('disk.api.file.delete', {data: {
					fileId: element.id
				}});
			}

			this.custom = this.custom.filter(el => el.id !== element.id);

			return true;
		},

		save()
		{
			window.close();
		},

		cancel()
		{
			if (this.defaultValue.id === this.selected)
			{
				window.close();
				return true;
			}

			if (this.isDesktop)
			{
				window.BX.desktop.setBackgroundImage(this.defaultValue.id, this.defaultValue.source).then(() => {
					window.close();
				});
			}
			else
			{
				window.close();
			}

			return true;
		},

		isAllowedType(type)
		{
			return [
				'image/png',
				'image/jpeg',
				'video/avi',
				'video/mp4',
				'video/quicktime',
			].includes(type);
		},
	},
	template: `
		<div class="bx-im-settings-video-background-dialog">
			<div class="bx-im-settings-video-background-dialog-inner" :style="containerSize">
				<div class="bx-im-settings-video-background-dialog-container">
					<div class="bx-im-settings-video-background-upload-input"><input type="file" :accept="uploadTypes" ref="uploadInput"/></div>
					<template v-if="loading">
						<div class="bx-im-settings-video-background-dialog-loader">
							<svg class="bx-desktop-loader-circular" viewBox="25 25 50 50">
								<circle class="bx-desktop-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
							</svg>
						</div>
					</template>
					<template v-else>
						<div class="bx-im-settings-video-background-dialog-content">
						<template v-for="(element in actions">
							<div :key="element.id" @click="select(element)" :class="['bx-im-settings-video-background-dialog-item', 'bx-im-settings-video-background-dialog-action', 'bx-im-settings-video-background-dialog-action-'+element.id, {'bx-im-settings-video-background-dialog-item-selected': selected === element.id }]">
								<div class="bx-im-settings-video-background-dialog-action-title">{{element.title}}</div>
							</div>
						</template>
							
						<template v-for="(item in backgrounds">
							<bx-im-component-settings-call-background-item 
								:key="item.id" 
								:item="item" 
								:selected="selected === item.id" 
								@select="select(item)" 
								@cancel="remove(item)"
								@remove="remove(item)"
							/>
						</template>
						</div>
					</template>
				</div>
			</div>
			<div class="ui-btn-container ui-btn-container-center">
				<button :class="['ui-btn', 'ui-btn-success', {'ui-btn-wait ui-btn-disabled': loading}]" @click="save">{{$Bitrix.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_SAVE')}}</button>
				<button class="ui-btn ui-btn-link" @click="cancel">{{$Bitrix.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_CANCEL')}}</button>
			</div>
		</div>
	`
});