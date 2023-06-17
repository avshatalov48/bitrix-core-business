import { Cache, Extension, Type } from 'main.core';
import { convertBitrixFormat } from './convert-bitrix-format';

const formatsCache = new Cache.MemoryCache();

/**
 * Returns culture-specific datetime format by code.
 * The full list with examples can be found in config.php of this extension in ['settings']['formats'].
 * All formats are compatible with this.format() without any additional transformations.
 *
 * @param code
 * @returns {string|null}
 */
export function getFormat(code: string): ?string
	{
		return formatsCache.remember(`main.date.format.${code}`, () => {
			let format = Extension.getSettings('main.date').get(`formats.${code}`);

			if (
				Type.isStringFilled(format)
				&& (code === 'FORMAT_DATE' || code === 'FORMAT_DATETIME')
			)
			{
				format = convertBitrixFormat(format);
			}

			return format;
		});
	}
