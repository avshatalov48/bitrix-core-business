import { bind, Dom } from 'main.core';
import { wait } from './wait';

export type ChangeBackgroundWithFadeOptions = {
	element: HTMLElement | HTMLImageElement,
	newImageUrl: string;
	duration?: number;
	delay?: number;
}

export const changeBackgroundWithFade = async (options: ChangeBackgroundWithFadeOptions) => {
	const { element, newImageUrl, duration = 1000, delay = 0 } = options;

	const currentImage = element.tagName === 'IMG' ? `url(${element.getAttribute('src')})` : getComputedStyle(element).backgroundImage;
	const elementRect = Dom.getPosition(element);

	const overlay = document.createElement('div');
	Dom.style(overlay, {
		position: 'absolute',
		opacity: 1,
		top: `${elementRect.top}px`,
		left: `${elementRect.left}px`,
		width: `${elementRect.width}px`,
		height: `${elementRect.height}px`,
		backgroundImage: String(currentImage),
		transition: `opacity ${duration}ms ease`,
		zIndex: 0,
		backgroundSize: 'cover',
		backgroundRepeat: 'no-repeat',
		backgroundPosition: 'center center',
	});

	Dom.append(overlay, document.body);

	await load(newImageUrl);
	if (element.tagName === 'IMG')
	{
		element.setAttribute('src', newImageUrl);
	}
	else if (getComputedStyle(element).getPropertyValue('--bg-url'))
	{
		element.style.setProperty('--bg-url', `url(${newImageUrl})`);
		element.style.setProperty('--bg-url-2x', `url(${newImageUrl})`);
	}
	else
	{
		Dom.style(element, {
			backgroundImage: `url(${newImageUrl})`,
		});
	}

	await wait(delay);
	// Fade out overlay
	requestAnimationFrame(() => {
		Dom.style(overlay, {
			opacity: 0,
		});
		setTimeout(() => {
			overlay.remove();
		}, 1000);
	});
};

export function load(src): Promise
{
	return new Promise((resolve, reject) => {
		const image = new Image();
		bind(image, 'load', resolve);
		bind(image, 'error', reject);
		image.src = src;
	});
}
