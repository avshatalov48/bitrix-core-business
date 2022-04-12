/**
 * Image Lazy Load Vue3 directive
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2021 Bitrix
 */

/*
	Attention: intersection observer work with errors if image has border-radius

	Example of usage:

	<img v-lazyload
		class="bx-module-element"
		src="https://.../placeholder.png"
		data-lazyload-src="https://.../targetImage.png"
		data-lazyload-error-src="https://.../errorImage.png"
	/>

	<img v-lazyload
		class="bx-module-element"
		src="https://.../placeholder.png"
		data-lazyload-dont-hide
		data-lazyload-src="https://.../targetImage.png"
		data-lazyload-error-src="https://.../errorImage.png"
	/>

	<img v-lazyload
		class="bx-module-element"
		data-lazyload-src="https://.../targetImage.png"
	/>

	<img v-lazyload
		class="bx-module-element"
		data-lazyload-src="https://.../targetImage.png"
		data-lazyload-error-class="bx-module-element-error"
		data-lazyload-success-class="bx-module-element-success"
	/>
 */

import {BitrixVue} from "ui.vue3";
import 'main.polyfill.intersectionobserver';

const WATCH = 'bx-lazyload-watch';
const LOADING = 'bx-lazyload-loading';
const SUCCESS = 'bx-lazyload-success';
const ERROR = 'bx-lazyload-error';
const HIDDEN = 'bx-lazyload-hidden';

const BLANK_IMAGE = "data:image/svg+xml,%3Csvg width='1px' height='1px' xmlns='http://www.w3.org/2000/svg'%3E%3C/svg%3E";

export const lazyload = {
	beforeMount(element, bindings)
	{
		if (typeof bindings.value === 'object' && typeof bindings.value.callback === 'function')
		{
			element.lazyloadCallback = bindings.value.callback;
		}

		if (!element.src || element.src === location.href.replace(location.hash, ''))
		{
			element.src = BLANK_IMAGE;
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
	updated(element)
	{
		if (
			!element.classList.contains(SUCCESS)
			&& !element.classList.contains(ERROR)
			&& !element.classList.contains(WATCH)
			&& !element.classList.contains(LOADING)
		)
		{
			element.classList.add(LOADING);
		}
		else if (
			(element.classList.contains(SUCCESS) || element.classList.contains(ERROR))
			&& element.dataset.lazyloadSrc
			&& element.dataset.lazyloadSrc !== element.src
		)
		{
			if (!element.dataset.lazyloadSrc.startsWith('http'))
			{
				const url = document.createElement('a');
				url.href = element.dataset.lazyloadSrc;
				if (url.href === element.src)
				{
					return;
				}
			}
			lazyloadLoadImage(element);
		}
	},
	unmounted(element)
	{
		if (lazyloadObserver)
		{
			lazyloadObserver.unobserve(element);
		}
	}
};

let lazyloadObserver = null;
let lazyloadLoadImage = function(currentImage, callback)
{
	let SUCCESS_CLASS = currentImage.dataset.lazyloadSuccessClass? currentImage.dataset.lazyloadSuccessClass.split(" "): [];
	delete currentImage.dataset.lazyloadSuccessClass;

	SUCCESS_CLASS = [SUCCESS, ...SUCCESS_CLASS];

	let ERROR_CLASS = currentImage.dataset.lazyloadErrorClass? currentImage.dataset.lazyloadErrorClass.split(" "): [];
	delete currentImage.dataset.lazyloadErrorClass;

	ERROR_CLASS = [ERROR, ...ERROR_CLASS];

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

		if (typeof currentImage.lazyloadCallback === 'function')
		{
			currentImage.lazyloadCallback({element: currentImage, state: 'success'});
			delete currentImage.lazyloadCallback;
		}
	};

	newImage.onerror = function()
	{
		if (currentImage.classList.contains(HIDDEN))
		{
			return false;
		}

		currentImage.classList.remove(LOADING);
		currentImage.classList.add(...ERROR_CLASS);
		currentImage.title = '';
		currentImage.alt = '';

		if (typeof currentImage.lazyloadCallback === 'function')
		{
			currentImage.lazyloadCallback({element: currentImage, state: 'error'});
			delete currentImage.lazyloadCallback;
		}
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