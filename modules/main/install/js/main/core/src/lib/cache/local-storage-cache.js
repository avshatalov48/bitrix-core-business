import BaseCache from './base-cache';
import LsCacheStorage from './storage/ls-storage';

export default class LocalStorageCache<T> extends BaseCache<T>
{
	/**
	 * @private
	 */
	storage: LsCacheStorage = new LsCacheStorage();
}
