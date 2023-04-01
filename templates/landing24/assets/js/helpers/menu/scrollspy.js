;(function ()
{
	"use strict";

	BX.namespace("BX.Landing");

	BX.Landing.NavbarScrollSpy = function ()
	{
		this.links = {};
		/**
		 * @type {HTMLElement[]}
		 */
		this.targets = [];
		/**
		 * Targets then now in viewport
		 * @type {HTMLElement[]}
		 */
		this.onScreen = [];

		/**
		 * To save target ID when click on link
		 * @type {string}
		 */
		this.forceHighlightId = null;

		this.observer = new IntersectionObserver(
			BX.Landing.NavbarScrollSpy.onIntersection,
			{threshold: [0.5, 1]}
		);
	};

	// todo: find parent element method may be set by params
	BX.Landing.NavbarScrollSpy.CLASS_TO_SET_ACTIVE = 'nav-item';
	BX.Landing.NavbarScrollSpy.ACTIVE_CLASS = 'active';

	BX.Landing.NavbarScrollSpy.getInstance = function ()
	{
		return (this.instance || (this.instance = new BX.Landing.NavbarScrollSpy()));
	};

	/**
	 * Add navbars from list to scrollSpy
	 * @param {HTMLElement} scrollspyNode
	 */
	BX.Landing.NavbarScrollSpy.init = function (scrollspyNode)
	{
		var scrollSpy = BX.Landing.NavbarScrollSpy.getInstance();
		scrollSpy.add(scrollspyNode);
	};

	BX.Landing.NavbarScrollSpy.onIntersection = function (entries)
	{
		var scrollspy = BX.Landing.NavbarScrollSpy.getInstance();
		entries.forEach(function (entry)
		{
			scrollspy.checkOnScreen(entry);
		});
		scrollspy.highlight();
	};

	BX.Landing.NavbarScrollSpy.getNodeToHighlight = function (linkNode)
	{
		var nodeToHighlight = BX.findParent(
			linkNode,
			{
				class: BX.Landing.NavbarScrollSpy.CLASS_TO_SET_ACTIVE
			}
		);

		return nodeToHighlight ? nodeToHighlight : linkNode;
	};


	BX.Landing.NavbarScrollSpy.prototype = {
		add: function (scrollspyNode)
		{
			var links = [].slice.call(scrollspyNode.querySelectorAll('a'));
			links.forEach(function (link)
			{
				if (
					link.getAttribute("href") !== "#"
					&& link.hash !== ''
					&& (link.target === '' || link.target === '_self')
					&& link.pathname === document.location.pathname
					&& link.hostname === document.location.hostname
				)
				{
					// hash can be not valid for various reasons
					try
					{
						var target = document.querySelector(link.hash);
					}
					catch (e) {}
					if (target)
					{
						if (typeof this.links[link.hash] === "undefined")
						{
							this.links[link.hash] = [];
						}
						this.links[link.hash].push(link);
						this.targets.push(target);
						this.observer.observe(target);

						link.addEventListener("click", BX.delegate(function (event)
						{
							event.preventDefault();
							event.target.blur();
							this.forceHighlightId = link.hash;
							this.unhighlight();
							this.highlightOnce(link.hash);
						}, this));
					}
				}
			}, this)
		},

		/**
		 * Check which entries now on screen
		 * @param {IntersectionObserverEntry} entry
		 */
		checkOnScreen: function (entry)
		{
			var index = this.onScreen.indexOf(entry.target);
			if (entry.isIntersecting && index === -1)
			{
				this.onScreen.push(entry.target);
			}
			else if (!entry.isIntersecting && index !== -1)
			{
				this.onScreen.splice(index, 1);
			}

			if (
				this.forceHighlightId === '#' + entry.target.id
				&& !entry.isIntersecting
			)
			{
				this.forceHighlightId = null;
			}
		},

		/**
		 * Check active target and set .active to corresponding link
		 */
		highlight: function ()
		{
			if (this.forceHighlightId !== null)
			{
				return;
			}

			this.unhighlight();
			var activeTargetId;
			var activeTargetPosition;
			this.onScreen.forEach(function (target)
			{
				if (activeTargetPosition === undefined)
				{
					activeTargetPosition = target.offsetTop;
				}

				if (target.offsetTop <= activeTargetPosition)
				{
					activeTargetPosition = target.offsetTop;
					activeTargetId = '#' + target.id;
				}
			}, this);

			if (
				typeof activeTargetId !== "undefined"
				&& typeof this.links[activeTargetId] !== "undefined"
				&& this.links[activeTargetId].length > 0
			)
			{
				this.highlightOnce(activeTargetId);
			}
		},

		unhighlight: function ()
		{
			for (var linkId in this.links)
			{
				this.unhighlightOnce(linkId);
			}
		},

		highlightOnce: function (linkId)
		{
			this.links[linkId].forEach(function (link)
			{
				BX.Landing.NavbarScrollSpy.getNodeToHighlight(link)
					.classList.add(BX.Landing.NavbarScrollSpy.ACTIVE_CLASS);
			});
		},

		unhighlightOnce: function (linkId)
		{
			this.links[linkId].forEach(function (link)
			{
				BX.Landing.NavbarScrollSpy.getNodeToHighlight(link)
					.classList.remove(BX.Landing.NavbarScrollSpy.ACTIVE_CLASS);
			}, this)
		}
	};
})();