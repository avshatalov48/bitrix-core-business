import { Loc } from 'main.core';
import { UploadLoader } from 'ui.uploader.tile-widget';
import { StackWidgetSize } from '../stack-widget-size';

const progressSizes = {
	[StackWidgetSize.LARGE]: { width: 46, lineSize: 5 },
	[StackWidgetSize.MEDIUM]: { width: 34, lineSize: 4 },
	[StackWidgetSize.SMALL]: { width: 20, lineSize: 3 },
	[StackWidgetSize.TINY]: { width: 14, lineSize: 2 },
};

export const StackUpload = {
	name: 'StackUpload',
	inject: ['widgetOptions'],
	components: {
		UploadLoader,
	},
	props: {
		items: {
			type: Array,
			required: true,
		},
		queueItems: {
			type: Array,
			required: true,
		}
	},
	emits: ['showPopup', 'abortUpload'],
	computed: {
		StackWidgetSize: () => StackWidgetSize,
		uploadFileTitle()
		{
			if (this.queueItems.length > 1)
			{
				return Loc.getMessage('STACK_WIDGET_FILES_UPLOADING');
			}
			else
			{
				return Loc.getMessage('STACK_WIDGET_FILE_UPLOADING');
			}
		},
		progress()
		{
			if (this.queueItems.length === 0)
			{
				return 0;
			}

			const progress = this.queueItems.reduce((total, item) => {
				return total + item.progress;
			}, 0);

			return Math.floor(progress / this.queueItems.length);
		},
		progressOptions()
		{
			const { width, lineSize } = progressSizes[this.widgetOptions.size];

			return {
				width,
				lineSize,
				progress: Math.max(this.progress, 10),
			};
		}
	},
	// language=Vue
	template: `
		<div class="ui-uploader-stack-upload" @click="$emit('showPopup')">
			<div class="ui-uploader-stack-upload-box">
				<div 
					class="ui-uploader-stack-upload-abort" 
					:title="$Bitrix.Loc.getMessage('STACK_WIDGET_ABORT_UPLOAD')"
					@click.stop="$emit('abortUpload')"
				>
				</div>
				<div class="ui-uploader-stack-upload-content">
					<div class="ui-uploader-stack-upload-loader">
						<UploadLoader v-bind="progressOptions" />
					</div>
					<div class="ui-uploader-stack-upload-progress">
						<div
							v-if="widgetOptions.size === StackWidgetSize.LARGE"
							class="ui-uploader-stack-upload-title"
						>{{ uploadFileTitle }}</div>
						<div class="ui-uploader-stack-upload-percent">{{ progress }}%</div>
						<div
							v-if="queueItems.length === 1 && widgetOptions.size === StackWidgetSize.LARGE"
							class="ui-uploader-stack-upload-stats"
						>
							<span class="ui-uploader-stack-upload-total">{{
								queueItems.length ? queueItems[0].sizeFormatted : ''
							}}</span>
						</div>
					</div>
				</div>
				<div
					class="ui-uploader-stack-upload-menu"
					:title="$Bitrix.Loc.getMessage('STACK_WIDGET_OPEN_FILE_GALLERY')"
				></div>
			</div>
		</div>
	`,
};
