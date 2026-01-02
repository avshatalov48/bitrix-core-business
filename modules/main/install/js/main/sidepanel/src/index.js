import { Reflection } from 'main.core';

import { Slider } from './slider';
import { SliderManager } from './slider-manager';
import { MessageEvent } from './message-event';
import { Toolbar } from './toolbar';
import { ToolbarItem } from './toolbar-item';
import { Label } from './label';
import { Dictionary } from './dictionary';
import { SliderEvent } from './slider-event';
import { getInstance } from './get-instance';

import { type LabelOptions} from './types/label-options';
import { type LinkOptions } from './types/link-options';
import { type RuleOptions } from './types/rule-options';
import { type MinimizeOptions } from './types/minimize-options';
import { type SliderOptions } from './types/slider-options';
import { type ToolbarOptions } from './types/toolbar-options';
import { type ToolbarItemOptions } from './types/toolbar-item-options';
import { type OuterBoundary } from './types/outer-boundary';

import './css/sidepanel.css';

const SidePanel = {};
Object.defineProperty(SidePanel, 'Instance', {
	enumerable: false,
	get: getInstance,
});

const namespace = Reflection.namespace('BX.SidePanel');

Object.defineProperty(namespace, 'Instance', {
	enumerable: false,
	get: getInstance,
});

export {
	SidePanel,
	Slider,
	SliderManager,
	SliderManager as Manager,
	SliderEvent,
	SliderEvent as Event,
	MessageEvent,
	Toolbar,
	ToolbarItem,
	Label,
	Dictionary,
};

export type {
	LabelOptions,
	LinkOptions,
	RuleOptions,
	MinimizeOptions,
	SliderOptions,
	ToolbarOptions,
	ToolbarItemOptions,
	OuterBoundary,
};
