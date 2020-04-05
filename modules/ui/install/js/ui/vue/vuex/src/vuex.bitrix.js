/**
 * Bitrix Vuex wrapper
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import {VuexVendorV3} from "./vuex.js";
import {VuexBuilder} from "./builder/builder.js";
import {VuexBuilderModel} from "./builder/model.js";

class BitrixVuex
{
	/**
	 * Create new Vuex instance
	 *
	 * @param {Object} params - definition
	 *
	 * @see https://vuex.vuejs.org/api/#vuex-store
	 */
	static store(params)
	{
		return new VuexVendorV3.Store(params);
	}

	/**
	 * Create component computed options that return the sub tree of the Vuex store.
	 *
	 * @param params
	 * @returns {*}
	 *
	 * @see https://vuex.vuejs.org/api/#mapstate
	 */
	static mapState(...params)
	{
		return VuexVendorV3.mapState(...params);
	}

	/**
	 * Create component computed options that return the evaluated value of a getter.
	 *
	 * @param params
	 * @returns {*}
	 *
	 * @see https://vuex.vuejs.org/api/#mapgetters
	 */
	static mapGetters(...params)
	{
		return VuexVendorV3.mapGetters(...params);
	}

	/**
	 * Create component methods options that dispatch an action.
	 *
	 * @param params
	 * @returns {*}
	 *
	 * @see https://vuex.vuejs.org/api/#mapactions
	 */
	static mapActions(...params)
	{
		return VuexVendorV3.mapActions(...params);
	}

	/**
	 * Create component methods options that commit a mutation.
	 *
	 * @param params
	 * @returns {*}
	 *
	 * @see https://vuex.vuejs.org/api/#mapactions
	 */
	static mapMutations(...params)
	{
		return VuexVendorV3.mapMutations(...params);
	}

	/**
	 * Create namespaced component binding helpers.
	 *
	 * @param params
	 * @returns {*}
	 *
	 * @see https://vuex.vuejs.org/api/#createnamespacedhelpers
	 */
	static createNamespacedHelpers(...params)
	{
		return VuexVendorV3.createNamespacedHelpers(...params);
	}

	/**
	 * Provides the installed version of Vuex as a string.
	 *
	 * @returns {String}
	 */
	static version()
	{
		return VuexVendorV3.version;
	}
}

let bitrixVuex = BitrixVuex;

export {
	bitrixVuex as Vuex,
	VuexVendorV3 as VuexVendor,
	VuexBuilder,
	VuexBuilderModel
};