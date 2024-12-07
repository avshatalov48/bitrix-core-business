import 'ui.cnt';
import { Reflection } from 'main.core';

/**
 * @memberOf BX.Grid
 */
export class Counters
{
	static Type = {
		LEFT: 'left',
		LEFT_ALIGNED: 'left-aligned',
		RIGHT: 'right',
	};

	static Color = {
		DANGER: 'ui-counter-danger',
		SUCCESS: 'ui-counter-success',
		PRIMARY: 'ui-counter-primary',
		GRAY: 'ui-counter-gray',
		LIGHT: 'ui-counter-light',
		DARK: 'ui-counter-dark',
		WARNING: 'ui-counter-warning',
	};

	static Size = {
		LARGE: 'ui-counter-lg',
		MEDIUM: 'ui-counter-md',
	};
}

const namespace = Reflection.namespace('BX.Grid');
namespace.Counters = Counters;
