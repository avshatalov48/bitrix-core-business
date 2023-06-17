import {Type} from 'main.core';
import {PlatformUtil} from './platform';

export const RestUtil = {

	getLogTrackingParams(params = {}): string
	{
		const result = [];

		let {
			name = 'tracking',
			data = [],
		} = params;

		const {
			dialog = null,
			message = null,
			files = null,
		} = params;

		name = encodeURIComponent(name);

		if (Type.isPlainObject(data))
		{
			const dataArray = [];
			for (const name in data)
			{
				if (data.hasOwnProperty(name))
				{
					dataArray.push(encodeURIComponent(name)+"="+encodeURIComponent(data[name]));
				}
			}
			data = dataArray;
		}
		else if (!Type.isArray(data))
		{
			data = [];
		}

		if (Type.isObjectLike(dialog))
		{
			result.push('timType='+dialog.type);

			if (dialog.type === 'lines')
			{
				result.push('timLinesType='+dialog.entityId.split('|')[0]);
			}
		}

		if (!Type.isNull(files))
		{
			let type = 'file';
			if (Type.isArray(files) && files[0])
			{
				type = files[0].type;
			}
			else if (Type.isObjectLike(files))
			{
				type = files.type;
			}
			result.push('timMessageType='+type);
		}
		else if (!Type.isNull(message))
		{
			result.push('timMessageType=text');
		}

		if (PlatformUtil.isBitrixMobile())
		{
			result.push('timDevice=bitrixMobile');
		}
		else if (PlatformUtil.isBitrixDesktop())
		{
			result.push('timDevice=bitrixDesktop');
		}
		else if (PlatformUtil.isIos() || PlatformUtil.isAndroid())
		{
			result.push('timDevice=mobile');
		}
		else
		{
			result.push('timDevice=web');
		}

		return name + (data.length? '&'+data.join('&'): '') + (result.length? '&'+result.join('&'): '');
	},
};
