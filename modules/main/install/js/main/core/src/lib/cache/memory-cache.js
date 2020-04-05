import BaseCache from './base-cache';
import MemoryStorage from './storage/memory';

export default class MemoryCache extends BaseCache
{
	/**
	 * @private
	 */
	storage: MemoryStorage = new MemoryStorage();
}