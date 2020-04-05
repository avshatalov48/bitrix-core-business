/**
 * Image Lazy Load Vue directive
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

/*
	Attention: intersection observer work with errors if image has border-radius

	Example of usage:

	<img v-bx-lazyload
		class="bx-module-element"
		src="https://.../placeholder.png"
		data-lazyload-src="https://.../targetImage.png"
		data-lazyload-error-src="https://.../errorImage.png"
	/>

	<img v-bx-lazyload
		class="bx-module-element"
		src="https://.../placeholder.png"
		data-lazyload-dont-hide
		data-lazyload-src="https://.../targetImage.png"
		data-lazyload-error-src="https://.../errorImage.png"
	/>

	<img v-bx-lazyload
		class="bx-module-element"
		data-lazyload-src="https://.../targetImage.png"
	/>

	<img v-bx-lazyload
		class="bx-module-element"
		data-lazyload-src="https://.../targetImage.png"
		data-lazyload-error-class="bx-module-element-error"
		data-lazyload-success-class="bx-module-element-success"
	/>
 */

import {Vue} from "ui.vue";
import 'main.polyfill.intersectionobserver';

const WATCH = 'bx-lazyload-watch';
const LOADING = 'bx-lazyload-loading';
const SUCCESS = 'bx-lazyload-success';
const ERROR = 'bx-lazyload-error';
const HIDDEN = 'bx-lazyload-hidden';

const BLANK_IMAGE = "data:image/svg+xml,%3Csvg width='1px' height='1px' xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E";

let lazyloadObserver = null;
let lazyloadLoadImage = function(currentImage)
{
	let SUCCESS_CLASS = currentImage.dataset.lazyloadSuccessClass? currentImage.dataset.lazyloadSuccessClass.split(" "): [SUCCESS];
	delete currentImage.dataset.lazyloadSuccessClass;

	let ERROR_CLASS = currentImage.dataset.lazyloadErrorClass? currentImage.dataset.lazyloadErrorClass.split(" "): [ERROR];
	delete currentImage.dataset.lazyloadErrorClass;

	currentImage.classList.add(LOADING);

	const newImage = new Image();
	newImage.src = currentImage.dataset.lazyloadSrc;

	if (!currentImage.dataset.lazyloadHiddenSrc)
	{
		currentImage.dataset.lazyloadHiddenSrc = currentImage.src;
	}

	newImage.onload = function()
	{
		if (currentImage.classList.contains(HIDDEN))
		{
			return false;
		}

		if (currentImage.dataset.lazyloadSrc)
		{
			currentImage.src = currentImage.dataset.lazyloadSrc;
		}

		currentImage.classList.remove(LOADING);
		currentImage.classList.add(...SUCCESS_CLASS);
	};

	newImage.onerror = function()
	{
		if (currentImage.classList.contains(HIDDEN))
		{
			return false;
		}

		if (currentImage.dataset.lazyloadErrorSrc)
		{
			currentImage.src = currentImage.dataset.lazyloadErrorSrc;
		}
		else
		{
			currentImage.dataset.lazyloadSrc = currentImage.src;
		}

		currentImage.classList.remove(LOADING);
		currentImage.classList.add(...ERROR_CLASS);
	};

	if (typeof currentImage.dataset.lazyloadDontHide !== 'undefined')
	{
		currentImage.classList.remove(WATCH);
		delete currentImage.dataset.lazyloadDontHide;

		if (lazyloadObserver)
		{
			lazyloadObserver.unobserve(currentImage);
		}
	}
};

if (typeof window.IntersectionObserver !== 'undefined')
{
	lazyloadObserver = new IntersectionObserver(function (entries, observer)
	{
		entries.forEach(function(entry)
		{
			const currentImage = entry.target;

			if (entry.isIntersecting)
			{
				if (currentImage.classList.contains(HIDDEN))
				{
					if (currentImage.dataset.lazyloadSrc)
					{
						currentImage.src = currentImage.dataset.lazyloadSrc;
					}
					currentImage.classList.remove(HIDDEN);
				}
				else if (currentImage.classList.contains(WATCH))
				{
					return true;
				}
				else
				{
					currentImage.classList.add(WATCH);
					lazyloadLoadImage(currentImage);
				}
			}
			else
			{
				if (
					currentImage.classList.contains(HIDDEN)
					|| !currentImage.classList.contains(WATCH)
				)
				{
					return true;
				}

				if (currentImage.dataset.lazyloadHiddenSrc)
				{
					currentImage.src = currentImage.dataset.lazyloadHiddenSrc;
				}

				currentImage.classList.remove(LOADING);
				currentImage.classList.add(HIDDEN);
			}
		});
	}, {
		threshold: [0, 1]
	});
}

Vue.directive('bx-lazyload',
{
	bind(element)
	{
		if (!element.src || element.src === location.href.replace(location.hash, ''))
		{
			element.src = "data:image/svg+xml,%3Csvg width='1px' height='1px' xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E";
		}

		if (lazyloadObserver)
		{
			lazyloadObserver.observe(element);
		}
		else
		{
			lazyloadLoadImage(element);
		}
	},
	componentUpdated(element)
	{
		if (
			!element.classList.contains(HIDDEN)
			&& !element.classList.contains(LOADING)
			&& element.dataset.lazyloadSrc
			&& element.dataset.lazyloadSrc != element.src
		)
		{
			if (!element.dataset.lazyloadSrc.startsWith('http'))
			{
				const url = document.createElement('a');
				url.href = element.dataset.lazyloadSrc;
				if (url.href == element.src)
				{
					return;
				}
			}
			lazyloadLoadImage(element);
		}
	},
	unbind(element)
	{
		if (lazyloadObserver)
		{
			lazyloadObserver.unobserve(element);
		}
	}
});