/**
 * Hint Vue directive
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2025 Bitrix
 */

/*
	<span v-hint="$Bitrix.Loc.getMessage('HINT_HTML')" data-hint-html>Html code</span>
	<span v-hint="{text: 'Text node'}">Plain text</span>
	<span v-hint="{html: '<b>Html</b> code'}">Html code</span>
	<span v-hint="{text: 'Custom position top and light mode', position: 'top', popupOptions: {darkMode: false}}">
		Text top on light panel
	</span>
	<span v-hint="{text: 'Hint text <a>More</a>', interactivity: true}">Hint with clickable link</span>
*/

import { Event, Type } from 'main.core';
import 'ui.hint';

import { tooltip, type HintParams } from './tooltip';
export type { HintParams };

const handlersMap = new WeakMap();

export const hint = {
	mounted(element: HTMLElement, { value }: { value: HintParams | Function }): void
	{
		updateEvents(element, value);
	},
	updated(element: HTMLElement, { value }: { value: HintParams | Function }): void
	{
		updateEvents(element, value);
	},
	beforeUnmount(element: HTMLElement): void
	{
		unbindEvents(element);
	},
};

let showTimeout = null;

function updateEvents(element: HTMLElement, params: HintParams | Function): void
{
	unbindEvents(element);
	if (params)
	{
		const handlers = {
			mouseenter: () => onMouseEnter(element, getParams(params)),
			mouseleave: () => hideTooltip(getParams(params).interactivity ?? false),
			click: () => hideTooltip(),
		};
		handlersMap.set(element, handlers);

		Object.entries(handlers).forEach(([event, handler]) => Event.bind(element, event, handler));
	}
}

function unbindEvents(element: HTMLElement): void
{
	clearTimeouts();
	Object.entries(handlersMap.get(element) ?? {}).forEach(([event, handler]) => Event.unbind(element, event, handler));
	handlersMap.delete(element);
}

function onMouseEnter(element: HTMLElement, params: HintParams): void
{
	clearTimeouts();
	showTimeout = setTimeout(() => showTooltip(element, params), params.timeout ?? 0);
}

function showTooltip(element: HTMLElement, params: HintParams): void
{
	clearTimeouts();
	tooltip.show(element, params);
}

function hideTooltip(isInteractive): void
{
	clearTimeouts();
	tooltip.hide(isInteractive);
}

function clearTimeouts(): void
{
	clearTimeout(showTimeout);
}

function getParams(value: HintParams | Function): HintParams
{
	return Type.isFunction(value) ? value() : value;
}
