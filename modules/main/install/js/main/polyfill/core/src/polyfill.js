import 'core-js/es6';
import 'core-js/es7';
import 'core-js/web';

if (window._main_core_polyfill)
{
	console.warn('main.core.polyfill is loaded more than once on this page');
}

window._main_core_polyfill = true;