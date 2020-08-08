;(function ()
{
	"use strict";

	BX.namespace("BX.Landing");

	var style = BX.Landing.Utils.style;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;

	BX.Landing.BlockHeaderEntry = function (node)
	{
		this.headerNode = node;
		this.wrapperNode = node.parentNode;
		this.fixMomentNodes = this.headerNode.querySelectorAll(BX.Landing.BlockHeaderEntry.FIX_MOMENT_SELECTOR);
		this.hiddenSectionsNodes = this.headerNode.querySelectorAll(BX.Landing.BlockHeaderEntry.SECTION_HIDDEN_SELECTOR);
		this.mode = this.getMode();
		this.prevState = 0;

		// BX.Landing.Env.getInstance().getType()
		var topPanel = document.querySelector('.landing-pub-top-panel-wrapper');
		this.headerOffset = topPanel ? topPanel.offsetHeight : 0;
	};

	BX.Landing.BlockHeaderEntry.HEADER_SELECTOR = '.u-header';
	BX.Landing.BlockHeaderEntry.SECTION_HIDDEN_SELECTOR = '.u-header__section--hidden';
	BX.Landing.BlockHeaderEntry.FIX_MOMENT_SELECTOR = '[data-header-fix-moment-classes], [data-header-fix-moment-exclude]';

	BX.Landing.BlockHeaderEntry.STATE_IN_FLOW = 10;
	BX.Landing.BlockHeaderEntry.STATE_ON_TOP = 20;
	BX.Landing.BlockHeaderEntry.STATE_FIX_MOMENT = 30;

	BX.Landing.BlockHeaderEntry.DIRECTION_TOP_TO_BOTTOM = 1;
	BX.Landing.BlockHeaderEntry.DIRECTION_BOTTOM_TO_TOP = -1;

	BX.Landing.BlockHeaderEntry.THRESHOLD_FULL = 1;

	BX.Landing.BlockHeaderEntry.STICKY_CLASS = 'u-header--sticky';
	BX.Landing.BlockHeaderEntry.FLOAT_CLASS = 'u-header--float';
	BX.Landing.BlockHeaderEntry.MODE_STICKY = 10;
	BX.Landing.BlockHeaderEntry.MODE_STICKY_RELATIVE = 20;
	BX.Landing.BlockHeaderEntry.MODE_STATIC = 30;

	BX.Landing.BlockHeaderEntry.FIX_MOMENT_CLASSES = ['js-header-fix-moment'];
	BX.Landing.BlockHeaderEntry.FIX_MOMENT_ADD_DATA = 'header-fix-moment-classes';
	BX.Landing.BlockHeaderEntry.FIX_MOMENT_REMOVE_DATA = 'header-fix-moment-exclude';

	BX.Landing.BlockHeaderEntry.getHeaderNodeByWrapper = function (wrapper)
	{
		return wrapper.querySelector(BX.Landing.BlockHeaderEntry.HEADER_SELECTOR)
	};

	BX.Landing.BlockHeaderEntry.prototype = {
		getNodeForObserve: function ()
		{
			return this.wrapperNode;
		},

		getMode: function ()
		{
			if (BX.hasClass(this.headerNode, BX.Landing.BlockHeaderEntry.STICKY_CLASS))
			{
				if(BX.hasClass(this.headerNode, BX.Landing.BlockHeaderEntry.FLOAT_CLASS))
				{
					return BX.Landing.BlockHeaderEntry.MODE_STICKY;
				}
				return BX.Landing.BlockHeaderEntry.MODE_STICKY_RELATIVE;
			}

			return BX.Landing.BlockHeaderEntry.MODE_STATIC;
		},

		/**
		 * Calculate header block state by entry. Return state constant
		 * @param {IntersectionObserverEntry} observerEntry
		 * @returns {number}
		 */
		getCurrentState: function (observerEntry)
		{
			if (observerEntry.isIntersecting)
			{
				if (!this.isOnTop(observerEntry))
				{
					return BX.Landing.BlockHeaderEntry.STATE_IN_FLOW;
				}
				else if (observerEntry.intersectionRatio === BX.Landing.BlockHeaderEntry.THRESHOLD_FULL)
				{
					return BX.Landing.BlockHeaderEntry.STATE_IN_FLOW;
				}
				else if (observerEntry.intersectionRatio < BX.Landing.BlockHeaderEntry.THRESHOLD_FULL)
				{
					return BX.Landing.BlockHeaderEntry.STATE_ON_TOP;
				}
			}
			else
			{
				if (!this.isOnTop(observerEntry))
				{
					return BX.Landing.BlockHeaderEntry.STATE_IN_FLOW;
				}
				else
				{
					return BX.Landing.BlockHeaderEntry.STATE_FIX_MOMENT;
				}
			}
		},


		/**
		 * Check header position, at top or bottom of screen
		 *
		 * @param {IntersectionObserverEntry} observerEntry
		 * @returns {boolean} true if header at the top of screen, false - at bottom
		 */
		isOnTop: function (observerEntry)
		{
			return observerEntry.boundingClientRect.top <= 0;
		},

		/**
		 * Check direction. >0 if top to bottom, <0 - backwards, 0 - first intersection, no direction
		 */
		getDirection: function (currentState)
		{
			if (this.prevState === null)
			{
				return 0;
			}

			if (currentState > this.prevState)
			{
				return BX.Landing.BlockHeaderEntry.DIRECTION_TOP_TO_BOTTOM;
			}

			return BX.Landing.BlockHeaderEntry.DIRECTION_BOTTOM_TO_TOP;
		},

		/**
		 * Check if menu more then one screen (on mobile, maybe)
		 * @param {IntersectionObserverEntry} observerEntry
		 */
		isOverScreen: function (observerEntry)
		{
			return observerEntry.boundingClientRect.height >= observerEntry.rootBounds.height;
		},

		setInFlow: function ()
		{
			if (this.mode === BX.Landing.BlockHeaderEntry.MODE_STICKY)
			{
				void style(this.headerNode, {
					position: 'absolute',
					top: 0,
					left: 0,
					right: 0
				});
			}
			else if (this.mode === BX.Landing.BlockHeaderEntry.MODE_STICKY_RELATIVE)
			{
				void style(this.wrapperNode, {
					height: 'auto'
				});
				void style(this.headerNode, {
					position: 'relative',
					top: 0,
					left: 0,
					right: 0
				});
			}
		},

		setOnTop: function ()
		{
			if (this.mode === BX.Landing.BlockHeaderEntry.MODE_STICKY_RELATIVE)
			{
				void style(this.wrapperNode, {
					height: this.wrapperNode.offsetHeight + 'px'
				});
			}

			void style(this.headerNode, {
				position: 'fixed',
				top: this.headerOffset + 'px',
				left: 0,
				right: 0
			});
		},

		setFixMoment: function ()
		{
			addClass(this.headerNode, BX.Landing.BlockHeaderEntry.FIX_MOMENT_CLASSES);
			this.fixMomentNodes.forEach(function (node)
			{
				var classesToAdd = BX.data(node, BX.Landing.BlockHeaderEntry.FIX_MOMENT_ADD_DATA);
				if (classesToAdd !== undefined)
				{
					addClass(node, classesToAdd.split(' '));
				}

				var classesToRemove = BX.data(node, BX.Landing.BlockHeaderEntry.FIX_MOMENT_REMOVE_DATA);
				if (classesToRemove !== undefined)
				{
					removeClass(node, classesToRemove.split(' '));
				}
			});

			this.hideSections();
		},

		unsetFixMoment: function ()
		{
			removeClass(this.headerNode, BX.Landing.BlockHeaderEntry.FIX_MOMENT_CLASSES);
			this.fixMomentNodes.forEach(function (node)
			{
				var classesToRemove = BX.data(node, BX.Landing.BlockHeaderEntry.FIX_MOMENT_ADD_DATA);
				if (classesToRemove !== undefined)
				{
					removeClass(node, classesToRemove.split(' '));
				}

				var classesToAdd = BX.data(node, BX.Landing.BlockHeaderEntry.FIX_MOMENT_REMOVE_DATA);
				if (classesToAdd !== undefined)
				{
					addClass(node, classesToAdd.split(' '));
				}
			});

			this.showSections();
		},

		hideSections: function ()
		{
			this.hiddenSectionsNodes.forEach(function (node)
			{
				node.style.setProperty('height', 0);
				node.style.setProperty('border', 'none', 'important');
				node.style.setProperty('overflow', 'hidden');
				node.style.setProperty('padding', 0, 'important');
			});
		},

		showSections: function ()
		{
			this.hiddenSectionsNodes.forEach(function (node)
			{
				node.style.removeProperty('height');
				node.style.removeProperty('border');
				node.style.removeProperty('overflow');
				node.style.removeProperty('padding');
			});
		},
	};

	/**
	 * @param {IntersectionObserverEntry[]} entries
	 */
	BX.Landing.BlockHeaderEntry.onIntersection = function (entries)
	{
		entries.forEach(function (entry)
		{
			var blockHeaders = BX.Landing.BlockHeaders.getInstance();
			var currHeaderEntry = blockHeaders.getEntryByIntersectionTarget(entry.target);

			// default behavior - do nothing
			if (currHeaderEntry.mode === BX.Landing.BlockHeaderEntry.MODE_STATIC)
			{
				return;
			}

			var state = currHeaderEntry.getCurrentState(entry);
			if (state !== currHeaderEntry.prevState)
			{

				if (state === BX.Landing.BlockHeaderEntry.STATE_IN_FLOW)
				{
					currHeaderEntry.setInFlow();
				}
				else if (state === BX.Landing.BlockHeaderEntry.STATE_ON_TOP)
				{
					currHeaderEntry.setOnTop();
				}
				else if (state === BX.Landing.BlockHeaderEntry.STATE_FIX_MOMENT)
				{
					// for not relative floating or first init
					if (currHeaderEntry.prevState < BX.Landing.BlockHeaderEntry.STATE_ON_TOP)
					{
						currHeaderEntry.setOnTop();
					}

					currHeaderEntry.setFixMoment();
				}

				var direction = currHeaderEntry.getDirection(state);
				if (direction < 0 && state < BX.Landing.BlockHeaderEntry.STATE_FIX_MOMENT)
				{
					currHeaderEntry.unsetFixMoment();
				}

				currHeaderEntry.prevState = state;
			}
			// compensation for big menu on mobile
			else if (currHeaderEntry.isOverScreen(entry))
			{
				currHeaderEntry.wrapperNode.scrollIntoView({
					behavior: 'smooth'
				});
				currHeaderEntry.setOnTop();
				currHeaderEntry.prevState = BX.Landing.BlockHeaderEntry.STATE_ON_TOP;
			}
		});
	};
})();