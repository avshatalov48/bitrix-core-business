import {Reflection} from 'main.core';

/**
 * @memberOf BX.Grid
 */
export class Label
{
	static Color = {
		DEFAULT: 'ui-label-default',
		DANGER: 'ui-label-danger',
		SUCCESS: 'ui-label-success',
		WARNING: 'ui-label-warning',
		PRIMARY: 'ui-label-primary',
		SECONDARY: 'ui-label-secondary',
		LIGHTGREEN: 'ui-label-lightgreen',
		LIGHTBLUE: 'ui-label-lightblue',
		LIGHT: 'ui-label-light',
	};

	static RemoveButtonType = {
		INSIDE: 'main-grid-tag-remove-inside',
		OUTSIDE: 'main-grid-tag-remove-outside',
	};
}

const namespace = Reflection.namespace('BX.Grid');
namespace.Label = Label;
