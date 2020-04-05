/**
 * Bitrix UI
 * IndexedDB manager (integration with Dexie.js)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 *
 * @see	https://dexie.org/docs/Tutorial/Getting-started
 */

import 'main.polyfill.customevent';
import {DexieVendor} from './dexie.js';

export class Dexie
{
	constructor(database)
	{
		return new DexieVendor(database);
	}
}