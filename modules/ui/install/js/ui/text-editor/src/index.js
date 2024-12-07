import type { TextEditorOptions } from './types/text-editor-options';
import type { NewLineModeType } from './types/new-line-mode-type';
import type { DecoratorComponentOptions } from './types/decorator-component-options';
import type { DecoratorOptions } from './types/decorator-options';
import type { ToolbarItem, ToolbarOptions } from './types/toolbar-options';
import type { InitialEditorStateType } from './types/initial-editor-state-type';

import * as AllCommands from './commands';
import * as AllConstants from './constants';

import { generateContent } from './debug/generate-content';

import { TextEditor } from './text-editor';
import { TextEditorComponent } from './text-editor-component';

import { BasicEditor } from './presets/basic-editor';
import { BasicEditorComponent } from './presets/basic-editor-component';

import BasePlugin from './plugins/base-plugin';
import Button from './toolbar/button';

import * as Paragraph from './plugins/paragraph';
import * as AutoLink from './plugins/auto-link';
import * as BlockToolbar from './plugins/block-toolbar';
import * as Bold from './plugins/bold';
import * as Code from './plugins/code';
import * as FloatingToolbar from './plugins/floating-toolbar';
import * as History from './plugins/history';
import * as Image from './plugins/image';
import * as Italic from './plugins/italic';
import * as Link from './plugins/link';
import * as List from './plugins/list';
import * as Mention from './plugins/mention';
import * as Quote from './plugins/quote';
import * as Strikethrough from './plugins/strikethrough';
import * as TabIndent from './plugins/tab-indent';
import * as Toolbar from './plugins/toolbar';
import * as Underline from './plugins/underline';
import * as Video from './plugins/video';
import * as Spoiler from './plugins/spoiler';
import * as Smiley from './plugins/smiley';
import * as Table from './plugins/table';
import * as Hashtag from './plugins/hashtag';
import * as File from './plugins/file';

/**
 * @namespace BX.UI.TextEditor.Plugins
 */
const Plugins = {
	Paragraph,
	AutoLink,
	BlockToolbar,
	Bold,
	Code,
	FloatingToolbar,
	History,
	Image,
	Italic,
	Link,
	List,
	Mention,
	Quote,
	Strikethrough,
	TabIndent,
	Toolbar,
	Underline,
	Video,
	Spoiler,
	Smiley,
	Table,
	Hashtag,
	File,
};

/**
 * @namespace BX.UI.TextEditor.Commands
 */
const Commands = { ...AllCommands };

/**
 * @namespace BX.UI.TextEditor.Commands
 */
const Constants = { ...AllConstants };

/**
 * @namespace BX.UI.TextEditor.Debug
 */
const Debug = {
	generateContent,
};

/**
 * @namespace BX.UI.TextEditor
 */
export {
	TextEditor,
	BasicEditor,
	TextEditorComponent,
	BasicEditorComponent,
	BasePlugin,
	Button,
	Plugins,
	Commands,
	Constants,
	Debug,
};

export type {
	TextEditorOptions,
	NewLineModeType,
	DecoratorComponentOptions,
	DecoratorOptions,
	ToolbarOptions,
	ToolbarItem,
	InitialEditorStateType,
};
