/**
 * Bitrix Vuex manager
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import {VueRouterVendor} from "./router.js";

class BitrixVueRouter
{
	/**
	 * Create new VueRouter instance
	 *
	 * @param {Object} params - route config
	 *
	 * @see https://router.vuejs.org/
	 */

	static create(params)
	{
		return new VueRouterVendor(params);
	}

	/**
	 * Provides the installed version of Vuex as a string.
	 *
	 * @returns {String}
	 */
	static version()
	{
		return VueRouter.version;
	}
}

export {
	BitrixVueRouter as VueRouter,
	VueRouterVendor
};