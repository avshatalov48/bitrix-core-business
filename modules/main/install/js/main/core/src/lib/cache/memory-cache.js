import BaseCache from './base-cache';
import MemoryStorage from './storage/memory';

export default class MemoryCache<T> extends BaseCache<T>
{
	/**
	 * @private
	 */
	storage: MemoryStorage = new MemoryStorage();
}
