import {Type, Tag, Uri, Loc} from 'main.core';

type LoadImageOptions = {
	image: string | HTMLImageElement,
	proxy?: string,
};

export default function loadImage({src, proxy}: LoadImageOptions)
{
	return new Promise((resolve, reject) => {
		const imageSrc = (() => {
			const srcUri = new Uri(src);
			const srcHost = srcUri.getHost();
			if (
				srcHost === ''
				|| srcHost === window.location.host
				|| srcHost === window.location.hostname
			)
			{
				return src;
			}

			if (Type.isString(proxy))
			{
				return Uri.addParam(proxy, {
					sessid: BX.bitrix_sessid(),
					url: src,
				});
			}

			return src;
		})();

		const image = (() => {
			if (Type.isString(imageSrc))
			{
				const newImage = new Image();
				newImage.src = imageSrc;
				return newImage;
			}

			return image;
		})();

		if (Type.isDomNode(image) && image instanceof HTMLImageElement)
		{
			if (image.complete)
			{
				resolve(image);
				return;
			}

			image.onload = () => resolve(image);
			image.onerror = reject;
		}
	});
}