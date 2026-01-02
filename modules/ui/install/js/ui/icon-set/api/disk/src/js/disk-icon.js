import { Type as VariableType, Dom, Tag, bind } from 'main.core';
import { Icon } from 'ui.icon-set.api.core';
import 'ui.icon-set.disk';

import { LazyLoadManager } from '../common/lazy-load-manager';
import { DiskIconType, TypeIcon, CompactTypeIcon } from '../common/const';

import '../common/disk-icon.css';

export type FileIconOptions = {
	size?: number;
	type?: string;
	previewUrl?: string;
	alt?: string;
	title?: string;
	responsive?: boolean;
};

export class DiskIcon
{
	#size: ?number;
	#type: ?string;
	#previewUrl: ?string;
	#alt: ?string;
	#title: ?string;
	#responsive: boolean = false;

	#container: ?HTMLElement;
	#icon: ?Icon;
	#resizeObserver: ?ResizeObserver;

	constructor(options: FileIconOptions = {}) {
		this.#validateOptions(options);
		this.#size = options.size;
		this.#type = this.#isExistingIconType(options.type) ? options.type : DiskIconType.file;
		this.#previewUrl = options.previewUrl;
		this.#alt = options.alt ?? '';
		this.#title = options.title ?? '';
		this.#responsive = options.responsive ?? false;
	}

	static render(options: FileIconOptions): HTMLElement {
		const icon = new DiskIcon(options);

		return icon.render();
	}

	render(): HTMLElement {
		const wrapper = this.#renderWrapper();

		this.#appendContentToWrapper(wrapper);

		this.#container = wrapper;

		if (this.#responsive)
		{
			this.#setupResizeObserver();
		}

		return wrapper;
	}

	#appendContentToWrapper(wrapper: HTMLElement): void {
		if (this.#previewUrl)
		{
			this.#appendIconWithLazyPreview(wrapper);
		}
		else
		{
			Dom.append(this.#renderIcon(), wrapper);
		}
	}

	setType(type: string): void {
		if (!this.#isExistingIconType(type))
		{
			console.warn(`DiskIcon: Type "${type}" is not supported`);

			return;
		}

		this.#type = type;
		this.#updateIcon();
	}

	setPreviewUrl(previewUrl: ?string): void {
		this.#previewUrl = previewUrl;
		this.#updateContent();
	}

	setAlt(alt: string): void {
		if (!this.#container)
		{
			return;
		}

		const img = this.#container.querySelector('img');
		if (img)
		{
			Dom.attr(img, 'alt', alt);
		}
	}

	setTitle(title: string): void {
		if (!this.#container)
		{
			return;
		}

		Dom.attr(this.#container, 'title', title);
	}

	setSize(size: number): void {
		if (!VariableType.isNumber(size) || size <= 0)
		{
			console.warn('DiskIcon: size must be the positive number');

			return;
		}

		this.#size = size;
		this.#updateSize();
		this.#updateIcon();
	}

	setResponsive(responsive: boolean): void {
		this.#responsive = responsive;
		this.#updateWrapper();
		this.#updateIcon();

		if (this.#responsive)
		{
			this.#setupResizeObserver();
		}
		else
		{
			this.#destroyResizeObserver();
		}
	}

	// @deprecated used only for vue component
	renderOnNode(target: HTMLElement): void {
		const wrapper = this.#renderWrapper();
		Dom.clean(target);

		[...target.attributes].forEach((attr) => {
			target.removeAttribute(attr.name);
		});

		[...wrapper.attributes].forEach((attr) => {
			target.setAttribute(attr.name, attr.value);
		});

		this.#container = target;

		this.#appendContentToWrapper(target);

		if (this.#responsive)
		{
			this.#setupResizeObserver();
		}
	}

	destroy(): void {
		this.#destroyResizeObserver();
		this.#container = null;
		this.#icon = null;
	}

	#renderWrapper(): HTMLElement {
		const wrapperClass = this.#responsive ? 'ui-icon-set_disk-icon --responsive' : 'ui-icon-set_disk-icon';
		const wrapper = Tag.render`<span title="${this.#title}" class="${wrapperClass}"></span>`;

		if (!this.#responsive)
		{
			Dom.style(wrapper, {
				width: `${this.#size}px`,
				height: `${this.#size}px`,
			});
		}

		return wrapper;
	}

	#getIconType(width?: number): string {
		const effectiveWidth = width ?? this.#size;

		return effectiveWidth >= 40 ? TypeIcon[this.#type] : CompactTypeIcon[this.#type];
	}

	#createIconInstance(size?: number, responsive?: boolean): Icon {
		const effectiveSize = size ?? this.#size;
		const effectiveResponsive = responsive ?? this.#responsive;

		return new Icon({
			icon: this.#getIconType(effectiveSize),
			size: effectiveSize,
			responsive: effectiveResponsive,
		});
	}

	#renderIcon(size?: number, responsive?: boolean): HTMLElement {
		const icon = this.#createIconInstance(size, responsive);

		return icon.render();
	}

	#appendIconWithLazyPreview(container: HTMLElement): void {
		const icon = this.#renderIcon();
		const iconWrapper = Tag.render`<span class="ui-icon-set_disk-icon__temp-icon">${icon}</span>`;

		// Создаем placeholder для превью
		const img = Tag.render`
			<img
				class="ui-icon-set_disk-icon__img --hidden"
				alt="${this.#alt}"
			/>
		`;

		Dom.append(iconWrapper, container);
		Dom.append(img, container);

		const lazyManager = LazyLoadManager.getInstance();
		lazyManager.observe(container, () => {
			this.#loadPreviewImage(img, iconWrapper);
		});
	}

	#loadPreviewImage(img: HTMLImageElement, iconWrapper: HTMLElement): void {
		Dom.attr(img, 'src', this.#previewUrl);

		bind(img, 'load', () => {
			Dom.removeClass(img, '--hidden');
			Dom.hide(iconWrapper);
		}, { once: true });

		bind(img, 'error', () => {
			Dom.removeClass(iconWrapper, 'ui-icon-set_disk-icon__temp-icon');
		}, { once: true });
	}

	#updateSize(): void {
		if (!this.#container)
		{
			return;
		}

		Dom.style(this.#container, {
			width: `${this.#size}px`,
			height: `${this.#size}px`,
		});
	}

	#replaceIconElement(size?: number, responsive?: boolean): void {
		if (!this.#container)
		{
			return;
		}

		const newIconElement = this.#renderIcon(size, responsive);
		const existingIconElement = this.#container.querySelector('.ui-icon-set');

		if (existingIconElement && existingIconElement.parentNode)
		{
			Dom.replace(existingIconElement, newIconElement);
		}
		else
		{
			Dom.append(newIconElement, this.#container);
		}

		this.#icon = this.#createIconInstance(size, responsive);
	}

	#updateIcon(): void {
		if (!this.#container || !this.#icon)
		{
			return;
		}

		this.#replaceIconElement(this.#size, this.#responsive);
	}

	#updateContent(): void {
		if (!this.#container)
		{
			return;
		}

		Dom.clean(this.#container);
		this.#appendContentToWrapper(this.#container);
	}

	#updateWrapper(): void {
		if (!this.#container)
		{
			return;
		}

		if (this.#responsive)
		{
			Dom.addClass(this.#container, '--responsive');
			Dom.style(this.#container, {
				width: '',
				height: '',
			});
		}
		else
		{
			Dom.removeClass(this.#container, '--responsive');
			Dom.style(this.#container, {
				width: `${this.#size}px`,
				height: `${this.#size}px`,
			});
		}
	}

	#setupResizeObserver(): void {
		if (!this.#container || this.#resizeObserver)
		{
			return;
		}

		this.#resizeObserver = new ResizeObserver((entries) => {
			for (const entry of entries)
			{
				const { width, height } = entry.contentRect;
				this.#onContainerResize(Math.min(width, height));
			}
		});

		this.#resizeObserver.observe(this.#container);
	}

	#destroyResizeObserver(): void {
		if (this.#resizeObserver)
		{
			this.#resizeObserver.disconnect();
			this.#resizeObserver = null;
		}
	}

	#onContainerResize(width: number): void {
		if (!this.#responsive)
		{
			return;
		}

		const currentIconType = this.#getCurrentIconType();
		const expectedIconType = this.#getIconType(width);

		if (currentIconType !== expectedIconType)
		{
			this.#updateIconForResponsive(width);
		}
	}

	#getCurrentIconType(): string {
		if (!this.#icon)
		{
			return '';
		}

		return this.#icon.getIcon ? this.#icon.getIcon() : '';
	}

	#updateIconForResponsive(containerWidth: number): void {
		this.#replaceIconElement(containerWidth, true);
	}

	#validateOptions(options: FileIconOptions): boolean {
		let isValid = true;

		if (VariableType.isUndefined(options.type) !== false && !this.#isExistingIconType(options.type))
		{
			console.warn(`DiskIcon: Type "${options.type}" is not supported`);

			isValid = false;
		}

		if (
			options.responsive === false
			&& VariableType.isUndefined(options.size) !== false
			&& (!VariableType.isNumber(options.size) || options.size <= 0)
		)
		{
			console.warn('DiskIcon: size must be the positive number');
			isValid = false;
		}

		if (VariableType.isNil(options.previewUrl) === false && !VariableType.isString(options.previewUrl))
		{
			console.warn('DiskIcon: previewUrl must be a string or null');
			isValid = false;
		}

		if (VariableType.isUndefined(options.alt) !== false && !VariableType.isString(options.alt))
		{
			console.warn('DiskIcon: alt must be a string');
			isValid = false;
		}

		if (VariableType.isUndefined(options.title) !== false && !VariableType.isString(options.title))
		{
			console.warn('DiskIcon: title must be a string');
			isValid = false;
		}

		if (VariableType.isUndefined(options.responsive) !== false && !VariableType.isBoolean(options.responsive))
		{
			console.warn('DiskIcon: responsive must be a boolean');
			isValid = false;
		}

		return isValid;
	}

	#isExistingIconType(type: string): boolean {
		return Object.values(DiskIconType)
			.includes(type);
	}
}
