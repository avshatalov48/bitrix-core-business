// @flow

/**
 * @namespace {BX.UI.StepProcessing}
 */

export type OptionsField = {
	id?: string;
	name: string;
	type: 'checkbox' | 'select' | 'radio' | 'text' | 'file';
	title: string;
	value?: string;
	obligatory?: boolean;
	multiple?: boolean;
	emptyMessage?: string;
	textSize?: number;
	textLine?: number;
	list?: {[val: string]: string};
	size?: number;
};

export type QueueAction = {
	action: string;
	title: string;
	progressBarTitle: string;
	method?: 'GET' | 'POST';
	controller?: string;
	params?: {[name: string]: any};
	finalize?: boolean;
	handlers?: {
		StateChanged?: ($Values<ProcessState>, ProcessResult) => void;
		RequestStart?: FormData => void;
		RequestStop?: any => void;
		RequestFinalize?: any => void;
	};
};

export type ProcessOptions = {
	id: string;
	controller?: string;
	component?: string;
	componentMode?: 'class'|'ajax';
	method?: 'GET' | 'POST';
	params?: {[name: string]: any};
	messages?: {[code: string]: string};
	optionsFields?: OptionsField[];
	handlers?: {
		StateChanged?: ($Values<ProcessState>, ProcessResult) => void;
		RequestStart?: FormData => void;
		RequestStop?: any => void;
		RequestFinalize?: any => void;
	};
	showButtons?: {
		start?: boolean;
		stop?: boolean;
		close?: boolean;
	};
	queue?: QueueAction[];
	dialogMinWidth?: number;
	dialogMaxWidth?: number;
};

export const ProcessResultStatus = {
	progress: 'PROGRESS',
	completed: 'COMPLETED'
};

export type ProcessResult = {
	STATUS?: $Values<ProcessResultStatus>;
	SUMMARY?: string;
	SUMMARY_HTML?: string;
	PROCESSED_ITEMS?: number;
	TOTAL_ITEMS?: number;
	WARNING?: string;
	FINALIZE?: boolean;
	NEXT_CONTROLLER?: string;
	NEXT_ACTION?: string;
	DOWNLOAD_LINK?: string;
	FILE_NAME?: string;
	DOWNLOAD_LINK_NAME?: string;
	CLEAR_LINK_NAME?: string;
};

export const ProcessState = {
	intermediate: 'INTERMEDIATE',
	running: 'RUNNING',
	completed: 'COMPLETED',
	stopped: 'STOPPED',
	error: 'ERROR',
	canceling: 'CANCELING'
};

export type ControllerResponse = {
	data?: ProcessResult;
	errors?: [];
	status?: 'success' | 'error';
};
