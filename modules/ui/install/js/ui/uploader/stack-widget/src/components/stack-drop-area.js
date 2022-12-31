import { Loc } from 'main.core';
import { StackWidgetSize } from 'ui.uploader.stack-widget';

export const StackDropArea = {
	name: 'StackDropArea',
	inject: ['uploader', 'widgetOptions'],
	data()
	{
		return {
			isHovering: false,
		}
	},
	computed: {
		StackWidgetSize: () => StackWidgetSize,
		uploadFileTitle(): string
		{
			if (this.uploader.shouldAcceptOnlyImages())
			{
				if (this.uploader.isMultiple())
				{
					return Loc.getMessage('STACK_WIDGET_UPLOAD_IMAGES');
				}
				else
				{
					return Loc.getMessage('STACK_WIDGET_UPLOAD_IMAGE');
				}
			}
			else
			{
				if (this.uploader.isMultiple())
				{
					return Loc.getMessage('STACK_WIDGET_UPLOAD_FILES');
				}
				else
				{
					return Loc.getMessage('STACK_WIDGET_UPLOAD_FILE');
				}
			}
		},
		dragFileHint(): string
		{
			if (this.uploader.isMultiple())
			{
				return Loc.getMessage('STACK_WIDGET_DRAG_FILES_HINT');
			}
			else
			{
				return Loc.getMessage('STACK_WIDGET_DRAG_FILE_HINT');
			}
		},
	},
	mounted()
	{
		this.uploader.assignDropzone(this.$refs.container);
		this.uploader.assignBrowse(this.$refs.container);
	},
	// language=Vue
	template: `
		<div
			class="ui-uploader-stack-drop-area"
			ref="container"
			:class="{ '--hover': isHovering }"
			@mouseenter="isHovering = true"
			@mouseleave="isHovering = false"
			@dragleave="isHovering = false"
		>
			<div class="ui-uploader-stack-drop-area-content">
				<div class="ui-uploader-stack-drop-area-icon"></div>
				<div
					v-if="[StackWidgetSize.LARGE, StackWidgetSize.MEDIUM].includes(widgetOptions.size)"
					class="ui-uploader-stack-drop-area-title"
				>{{ uploadFileTitle }}</div>
				<div
					v-if="widgetOptions.size === StackWidgetSize.LARGE"
					class="ui-uploader-stack-drop-area-hint"
				>{{ dragFileHint }}</div>
			</div>
		</div>
	`,
};
