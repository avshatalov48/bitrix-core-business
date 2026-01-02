import { Event } from 'main.core';
import { Metrika } from 'landing.metrika';

// Block and analytics constants
const CATEGORY = 'vibe';
const SECTION_ACTIVE_PAGE = 'active_page';
const SECTION_PREVIEW_PAGE = 'preview_page';
const P1_TEMPLATE_CODE = 'templateCode';
const P2_WIDGET_ID = 'widgetId';
const TRIAL_BUTTON_ID = 'trialButton';
const EVENT_DEMO_ACTIVATED = 'demo_activated';
const EVENT_CLICK_ON_BUTTON = 'click_on_button';

// HTML tag and attribute constants
const TAG_A = 'a';
const TAG_BUTTON = 'button';
const DATA_PSEUDO_URL = 'data-pseudo-url';
const ATTR_HREF = 'href';

// Other string constants
const IS_LIGHT_METRIKA = true;
const BLOCK_WRAPPER_CLASS = 'block-wrapper';
const BLOCK_PREFIX = 'block-';
const DASH = '-';
const DOT = '.';
const B24URL_TYPE = 'b24url';
const SLIDER_TYPE = 'slider';
const OTHER_URL_TYPE = 'otherurl';
const REGEX_SLIDER = /BX\.Helper\.show\(["'].*?code=(\d+)["']\)/;
const REGEX_PSEUDO_URL = /^\/|^https?:\/\/|^#/;
const QUOT_ENTITY = '&quot;';
const QUOTE = '"';

type TrackingParam = { type: string, value: string } | undefined;

/**
 * @typedef {Object} AnalyticsOptions
 * @property {boolean} isPublished - Whether the page is published.
 * @property {string} templateCode - The template code for analytics.
 * @property {Metrika} metrika - Instance of the analytics sending class.
 * @property {HTMLElement[]} clickableElements - Array of HTML elements that are tracked for analytics.
 */

/**
 * Analytics class for tracking user interactions on landing page blocks.
 */
export class Analytics
{
	/**
	 * Constructor.
	 * @param {AnalyticsOptions} options - Configuration options.
	 */
	constructor(options: { isPublished: boolean, templateCode: string })
	{
		this.isPublished = options.isPublished;
		this.templateCode = options.templateCode;
		this.metrika = new Metrika(IS_LIGHT_METRIKA);
		this.clickableElements = [];
		this.initEventListeners();
	}

	/**
	 * Initializes click event listeners on all block elements.
	 * @returns {void}
	 */
	initEventListeners(): void
	{
		const blocks = [...document.getElementsByClassName(BLOCK_WRAPPER_CLASS)];
		for (const block of blocks)
		{
			const elements = this.findClickableElements(block);
			this.clickableElements.push(...elements);
			const code = this.getBlockCode(block);
			elements.forEach((element: HTMLElement) => {
				Event.bind(element, 'click', (event: MouseEvent) => this.onClick(event, code));
			});
		}
	}

	/**
	 * Finds all clickable elements within a block.
	 * @param {HTMLElement} block - The block element to search within.
	 * @returns {HTMLElement[]} Array of clickable elements.
	 */
	findClickableElements(block: HTMLElement): HTMLElement[]
	{
		const elements = [...block.querySelectorAll(`${TAG_A}, ${TAG_BUTTON}, [${DATA_PSEUDO_URL}]`)];

		return elements.filter((el: HTMLElement) => this.isClickableElement(el));
	}

	/**
	 * Determines if an element is considered clickable for analytics.
	 * @param {HTMLElement} element - The element to check.
	 * @returns {boolean} True if the element is clickable, false otherwise.
	 */
	isClickableElement(element: HTMLElement): boolean
	{
		const tag = element.tagName.toLowerCase();

		if (tag === TAG_A)
		{
			return true;
		}

		if (element.closest(TAG_A))
		{
			return false;
		}

		return tag === TAG_BUTTON || element.hasAttribute(DATA_PSEUDO_URL);
	}

	/**
	 * Extracts a unique code for the block based on its class names.
	 * @param {HTMLElement} block - The block element.
	 * @returns {string} The unique block code.
	 */
	getBlockCode(block: HTMLElement): string
	{
		const className = [...block.classList].find(
			(name) => name.startsWith(BLOCK_PREFIX) && name !== BLOCK_WRAPPER_CLASS,
		);

		if (!className)
		{
			return '';
		}

		return className.replace(BLOCK_PREFIX, '').replaceAll(DASH, DOT);
	}

	/**
	 * Handles click events on tracked elements and sends analytics data.
	 * @param {MouseEvent} event - The click event object.
	 * @param {string} code - Unique block code.
	 * @returns {void}
	 */
	onClick(event: MouseEvent, code: string): void
	{
		const target = event.currentTarget;
		const data = {
			event: this.getEventName(target),
			p2: [P2_WIDGET_ID, code],
			p4: this.getTrackingParameter(target),
		};

		this.sendAnalytics(data);
	}

	/**
	 * Determines the name of the analytics event based on the clicked element.
	 * @param {HTMLElement} target - The clicked element.
	 * @returns {string} The event name for analytics.
	 */
	getEventName(target: HTMLElement): string
	{
		return target.id === TRIAL_BUTTON_ID ? EVENT_DEMO_ACTIVATED : EVENT_CLICK_ON_BUTTON;
	}

	/**
	 * Extracts and classifies tracking parameters from the clicked element.
	 * Detects sliders, internal portal links, or external URLs.
	 * @param {HTMLElement} target - The clicked element.
	 * @returns {Array} Tracking parameters array.
	 */
	getTrackingParameter(target: HTMLElement): TrackingParam
	{
		const href = this.extractHrefFromPseudoUrl(target) || this.extractHrefFromElement(target);

		if (!href)
		{
			return undefined;
		}

		const sliderMatch = href.match(REGEX_SLIDER);
		if (sliderMatch)
		{
			return [SLIDER_TYPE, sliderMatch[1]];
		}

		if (href.startsWith('/') || href.includes(window.location.origin))
		{
			return [B24URL_TYPE, href];
		}

		return [OTHER_URL_TYPE, href];
	}

	/**
	 * Attempts to parse a pseudo-URL from a `data-pseudo-url` attribute.
	 * Validates format and URL prefix before returning.
	 * @param {HTMLElement} target - The element to extract from.
	 * @returns {string|null} The extracted href or null.
	 */
	extractHrefFromPseudoUrl(target: HTMLElement): string | null
	{
		if (!target.hasAttribute(DATA_PSEUDO_URL))
		{
			return null;
		}

		const raw = target.getAttribute(DATA_PSEUDO_URL);
		if (!raw)
		{
			return null;
		}

		try
		{
			const data = JSON.parse(raw.replaceAll(QUOT_ENTITY, QUOTE));
			if (data && data.href && data.enabled)
			{
				if (!REGEX_PSEUDO_URL.test(data.href))
				{
					return '';
				}

				return data.href;
			}
		}
		catch
		{
			return null;
		}

		return null;
	}

	/**
	 * Retrieves the href attribute from the closest ancestor anchor element.
	 * @param {HTMLElement} target - The element to start searching from.
	 * @returns {string|null} The href value or null.
	 */
	extractHrefFromElement(target: HTMLElement): string | null
	{
		const linkElement = target.closest(TAG_A);

		return linkElement ? linkElement.getAttribute(ATTR_HREF) || null : null;
	}

	/**
	 * Merges common analytics fields with the event-specific data.
	 * Constructs the final object to be sent to Metrika.
	 * @param {Record<string, any>} data - Dynamic analytics fields.
	 * @returns {Record<string, any>} Full analytics data object.
	 */
	getAnalyticsData(data: Record<string, any>): Record<string, any>
	{
		return {
			category: CATEGORY,
			c_section: this.isPublished ? SECTION_ACTIVE_PAGE : SECTION_PREVIEW_PAGE,
			p1: [P1_TEMPLATE_CODE, this.templateCode],
			...data,
		};
	}

	/**
	 * Sends the finalized analytics data object to the Metrika service.
	 * @param {Record<string, any>} data - Analytics payload to be transmitted.
	 * @returns {void}
	 */
	sendAnalytics(data: Record<string, any>): void
	{
		this.metrika.sendData(this.getAnalyticsData(data));
	}
}
