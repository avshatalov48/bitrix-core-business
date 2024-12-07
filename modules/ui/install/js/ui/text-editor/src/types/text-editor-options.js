import type { BaseEvent } from 'main.core.events';
import type { DialogOptions } from 'ui.entity-selector';
import type { CopilotOptions } from 'ai.copilot';
import type { UploaderFileInfo } from 'ui.uploader.core';
import type { NewLineMode } from '../constants';

import type { InitialEditorStateType } from './initial-editor-state-type';
import type { PluginConstructor } from '../plugins/base-plugin';

export type TextEditorOptions = {
	content?: string,
	editorState?: InitialEditorStateType,

	plugins?: Array<string | PluginConstructor>,
	extraPlugins?: Array<string | PluginConstructor>,
	removePlugins?: Array<string | PluginConstructor>,

	toolbar?: string[],
	hideToolbar?: boolean,
	floatingToolbar?: string[],

	newLineMode?: NewLineMode,
	autoFocus?: boolean,
	minHeight?: number,
	maxHeight?: number,
	editable?: number,
	collapsingMode?: boolean,

	placeholder?: string,
	paragraphPlaceholder?: string | 'auto',

	visualOptions: {
		borderRadius?: string,
		borderWidth?: string,
		borderStyle?: string,
		borderColorBase?: string,
		borderColorFocus?: string,

		colorBackground?: string,
		colorSelection?: string,
		colorToolbarBackground?: string,
		colorToolbarBorder?: string,

		blockSpaceInline?: string,
		blockSpaceStack?: string,
	},

	namespace?: string,
	events?: Object<string, (event: BaseEvent) => {}>,

	mention?: {
		dialogOptions?: DialogOptions,
		entities?: string[],
	},

	copilot?: {
		copilotOptions?: CopilotOptions,
		triggerBySpace?: boolean,
	},

	file?: {
		mode?: 'disk' | 'file',
		files?: UploaderFileInfo[],
	},
};
