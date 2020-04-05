import BaseCache from './base-cache';
import LsCacheStorage from './storage/ls-storage';

export default class LocalStorageCache extends BaseCache
{
	/**
	 * @private
	 */
	storage: LsCacheStorage = new LsCacheStorage();
}