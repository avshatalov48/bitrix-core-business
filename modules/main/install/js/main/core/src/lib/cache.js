import BaseCache from './cache/base-cache';
import MemoryCache from './cache/memory-cache';
import LocalStorageCache from './cache/local-storage-cache';

/**
 * @memberOf BX
 */
export default class Cache
{
	static BaseCache: BaseCache = BaseCache;
	static MemoryCache: MemoryCache = MemoryCache;
	static LocalStorageCache: LocalStorageCache = LocalStorageCache;
}
