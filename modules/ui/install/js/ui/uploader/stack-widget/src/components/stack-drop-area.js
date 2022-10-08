import { Loc } from 'main.core';

import { BitrixVue } from 'ui.vue';
import { StackWidgetSize } from 'ui.uploader.stack-widget';

export const StackDropArea = BitrixVue.localComponent('ui.uploader.stack-widget.stack-drop-area', {
	data()
	{
		return {
			isHovering: false,
		}
	},
	computed: {
		StackWidgetSize: () => StackWidgetSize,
		uploadFileTitle()
		{
			if (this.$root.acceptOnlyImages)
			{
				if (this.$root.multiple)
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
				if (this.$root.multiple)
				{
					return Loc.getMessage('STACK_WIDGET_UPLOAD_FILES');
				}
				else
				{
					return Loc.getMessage('STACK_WIDGET_UPLOAD_FILE');
				}
			}
		},
		dragFileHint()
		{
			if (this.$root.multiple)
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
		this.$root.getUploader().assignDropzone(this.$refs.container);
		this.$root.getUploader().assignBrowse(this.$refs.container);
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
					v-if="[StackWidgetSize.LARGE, StackWidgetSize.MEDIUM].includes($root.widget.size)"
					class="ui-uploader-stack-drop-area-title"
				>{{ uploadFileTitle }}</div>
				<div
					v-if="$root.widget.size === StackWidgetSize.LARGE"
					class="ui-uploader-stack-drop-area-hint"
				>{{ dragFileHint }}</div>
			</div>
		</div>
	`,
});
