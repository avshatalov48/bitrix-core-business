import { Type, Event } from 'main.core';

export class Step extends Event.EventEmitter
{
	constructor(options)
	{
		super();
		this.setEventNamespace('BX.UI.Tutor.Step');
		options = Type.isPlainObject(options) ? options : {};

		this.id = options.id || null;
		this.title = options.title || null;
		this.description = options.description || null;
		this.url = options.url || '';
		this.isCompleted = options.isCompleted || false;
		this.video = options.video || null;
		this.helpLink = options.helpLink || null;
		this.highlight = options.highlight || null;
		this.isActive = options.isActive === true;
		this.isShownForSlider = options.isShownForSlider || false;
		this.initOptions = options;

		this.videoObj = null;
	}

	/**
	 * @public
	 * @returns {string}
	 */
	getTitle()
	{
		return this.title;
	}

	/**
	 * @public
	 * @returns {Object}
	 */
	getVideoObj()
	{
		return this.videoObj;
	}

	/**
	 * @public
	 */
	getHighlightOptions()
	{
		return this.highlight;
	}

	/**
	 * @public
	 * @returns {string}
	 */
	getDescription()
	{
		return this.description;
	}

	/**
	 * @public
	 * @returns {string}
	 */
	getUrl()
	{
		return this.url;
	}

	/**
	 * @public
	 * @returns {Boolean}
	 */
	getCompleted()
	{
		return this.isCompleted;
	}

	getVideo()
	{
		return this.video;
	}

	getHelpLink()
	{
		return this.helpLink;
	}

	/**
	 * @public
	 * @returns {string}
	 */
	getId()
	{
		return this.id;
	}

	/**
	 * @public
	 * @returns {Object}
	 */
	getInitOptions()
	{
		return this.initOptions;
	}

	/**
	 * @public
	 */
	activate()
	{
		this.isActive = true;
	}

	/**
	 * @public
	 */
	getShownForSlider()
	{
		return this.isShownForSlider;
	}

	/**
	 * @public
	 */
	deactivate()
	{
		this.isActive = false;
	}

	/**
	 * @private
	 */
	static getFullEventName(shortName)
	{
		return shortName;
	}
}