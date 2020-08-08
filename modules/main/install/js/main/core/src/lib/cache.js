import MemoryCache from './cache/memory-cache';
import LocalStorageCache from './cache/local-storage-cache';

/**
 * @memberOf BX
 */
export default class Cache
{
	static MemoryCache: MemoryCache = MemoryCache;
	static LocalStorageCache: LocalStorageCache = LocalStorageCache;
}