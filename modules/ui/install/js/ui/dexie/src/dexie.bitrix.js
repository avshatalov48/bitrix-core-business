/**
 * Bitrix UI
 * IndexedDB manager (integration with Dexie.js)
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 *
 * @see	https://dexie.org/docs/Tutorial/Getting-started
 */

import 'main.polyfill.customevent';
import {DexieVendor} from './dexie.js';

class Dexie
{
	constructor(database)
	{
		return new DexieVendor(database);
	}
}

Dexie.delete = DexieVendor.delete;
Dexie.exists = DexieVendor.exists;
Dexie.getDatabaseNames = DexieVendor.getDatabaseNames;
Dexie.defineClass = DexieVendor.defineClass;
Dexie.applyStructure = DexieVendor.applyStructure;
Dexie.ignoreTransaction = DexieVendor.ignoreTransaction;
Dexie.vip = DexieVendor.vip;
Dexie.async = DexieVendor.async;
Dexie.spawn = DexieVendor.spawn;
Dexie.currentTransaction = DexieVendor.currentTransaction;
Dexie.waitFor = DexieVendor.waitFor;
Dexie.Promise = DexieVendor.Promise;
Dexie.debug = DexieVendor.debug;
Dexie.derive = DexieVendor.derive;
Dexie.extend = DexieVendor.extend;
Dexie.props = DexieVendor.props;
Dexie.override = DexieVendor.override;
Dexie.Events = DexieVendor.Events;
Dexie.getByKeyPath = DexieVendor.getByKeyPath;
Dexie.setByKeyPath = DexieVendor.setByKeyPath;
Dexie.delByKeyPath = DexieVendor.delByKeyPath;
Dexie.shallowClone = DexieVendor.shallowClone;
Dexie.deepClone = DexieVendor.deepClone;
Dexie.getObjectDiff = DexieVendor.getObjectDiff;
Dexie.asap = DexieVendor.asap;
Dexie.maxKey = DexieVendor.maxKey;
Dexie.minKey = DexieVendor.minKey;
Dexie.addons = DexieVendor.addons;
Dexie.connections = DexieVendor.connections;
Dexie.MultiModifyError = DexieVendor.MultiModifyError;
Dexie.errnames = DexieVendor.errnames;
Dexie.IndexSpec = DexieVendor.IndexSpec;
Dexie.TableSchema = DexieVendor.TableSchema;
Dexie.dependencies = DexieVendor.dependencies;
Dexie.semVer = DexieVendor.semVer;
Dexie.version = DexieVendor.version;
Dexie.default = DexieVendor.default;
Dexie.Dexie = DexieVendor.Dexie;

export {Dexie};