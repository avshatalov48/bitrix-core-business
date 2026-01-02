;(function() {
	'use strict';

	BX.namespace('BX.Landing.UI.Guide');

	/**
	 * @typedef {Object} TourManagerConfig
	 * @property {number} id
	 * @property {string} selector
	 * @property {?string} requiredChildSelector
	 * @property {Function} checkCondition
	 * @property {Function} showGuide
	 * @property {Function} markAsShown
	 */

	BX.Landing.UI.Guide.TourManager = class TourManager
	{
		registeredGuides = new Map();
		observer = null;

		/**
		 * @param {TourManagerConfig} config
		 * @return {void}
		 */
		register(config)
		{
			this.registeredGuides.set(config.id, config);

			if (!this.observer)
			{
				this.startObserving();
			}
		}

		/**
		 * @return {void}
		 */
		startObserving()
		{
			if (this.observer)
			{
				return;
			}

			this.observer = new MutationObserver(this.#handleMutations.bind(this));

			this.observer.observe(document.body, {
				childList: true,
				subtree: true,
			});
		}

		/**
		 * @private
		 * @param {MutationRecord[]} mutations
		 */
		#handleMutations(mutations)
		{
			for (const mutation of mutations)
			{
				if (mutation.type === 'childList')
				{
					mutation.addedNodes.forEach(this.#processAddedNode.bind(this));
				}
			}
		}

		/**
		 * @private
		 * @param {Node} node
		 */
		#processAddedNode(node)
		{
			if (node.nodeType === Node.ELEMENT_NODE)
			{
				this.checkNodeForTargets(node);
			}
		}

		/**
		 * @return {void}
		 */
		stopObserving()
		{
			if (this.observer)
			{
				this.observer.disconnect();
				this.observer = null;
			}
		}

		/**
		 * @param {Element} node
		 * @return {void}
		 */
		checkNodeForTargets(node)
		{
			for (const [guideId, config] of this.registeredGuides)
			{
				try
				{
					const targetElement = node.matches(config.selector)
						? node
						: node.querySelector(config.selector)
					;

					if (!targetElement)
					{
						continue;
					}

					if (config.requiredChildSelector)
					{
						const requiredChild = targetElement.querySelector(config.requiredChildSelector);
						if (!requiredChild)
						{
							continue;
						}
					}

					this.processGuide(guideId, config, targetElement);
				}
				catch (error)
				{
				}
			}
		}

		/**
		 * @param {string} guideId
		 * @param {TourManagerConfig} config
		 * @param {Element} targetElement
		 * @return {Promise<void>}
		 */
		async processGuide(guideId, config, targetElement)
		{
			this.registeredGuides.delete(guideId);

			try
			{
				const shouldShow = await config.checkCondition();
				if (!shouldShow)
				{
					return;
				}

				await config.showGuide(targetElement);

				if (config.markAsShown)
				{
					config.markAsShown();
				}
			}
			catch (error)
			{
			}
			finally
			{
				if (this.registeredGuides.size === 0)
				{
					this.stopObserving();
				}
			}
		}
	};

	const tourManager = new BX.Landing.UI.Guide.TourManager();

	BX.ready(() => {
		if (!BX.UI?.Tour?.Guide || !BX.Event)
		{
			return;
		}

		const Guide = BX.UI.Tour.Guide;
		const Event = BX.Event;

		/**
		 * @return {Promise<boolean>}
		 */
		function wasPhoneGuideShown()
		{
			return new Promise((resolve) => {
				BX.ajax.runAction('landing.landing.isPhoneRegionCodeTourAlreadySeen')
					.then(
						(response) => {
							resolve(response.data);
						},
						() => {
							resolve(false);
						},
					)
					.catch(() => {
						resolve(false);
					});
			});
		}

		/**
		 * @return {void}
		 */
		function markPhoneGuideAsShown()
		{
			BX.userOptions.save('ui-tour', 'landing_phone_aha_shown', 'null', 'Y');
		}

		/**
		 * @param {Element} targetElement
		 * @return {Promise<void>}
		 */
		function showPhoneGuide(targetElement)
		{
			return new Promise((resolve) => {
				try
				{
					const pencilIcon = targetElement.querySelector('.landing-ui-button-icon-edit');

					const guide = new Guide({
						id: 'landing-phone-field-aha-moment',
						overlay: true,
						simpleMode: true,
						onEvents: false,
						steps: [
							{
								target: targetElement,
								title: '',
								text: BX.Landing.Loc.getMessage('LANDING_PHONE_FIELD_AHA_MOMENT_REGION_CODE'),
								position: 'bottom',
							},
						],
					});

					guide.getPopup().setWidth(350);

					const adjustPositionAndAngle = () => {
						const popup = guide.getPopup();
						if (!popup || !popup.getPopupContainer())
						{
							return;
						}

						const popupContainer = popup.getPopupContainer();
						const targetRect = targetElement.getBoundingClientRect();

						const newLeft = targetRect.right - popupContainer.offsetWidth;
						BX.Dom.style(popupContainer, { left: `${newLeft}px` });

						const angleElement = popup.angle?.element;

						if (angleElement && pencilIcon)
						{
							const pencilRect = pencilIcon.getBoundingClientRect();
							const pencilCenterX = pencilRect.left + (pencilRect.width / 2);
							const angleLeftOffset = pencilCenterX - newLeft;
							const angleWidth = angleElement.offsetWidth;
							BX.Dom.style(angleElement, { left: `${angleLeftOffset - (angleWidth / 2)}px` });
						}
					};

					const onClose = () => {
						Event.unbind(window, 'resize', adjustPositionAndAngle);
						Event.unbind(document, 'scroll', adjustPositionAndAngle, true);
						resolve();
					};

					guide.getPopup().subscribe('onClose', onClose);
					guide.getPopup().subscribe('onDestroy', onClose);

					guide.start();

					requestAnimationFrame(() => {
						const popupContainer = guide.getPopup().getPopupContainer();
						if (popupContainer)
						{
							BX.Dom.style(popupContainer, { zIndex: '1050' });
						}
					});

					requestAnimationFrame(() => {
						adjustPositionAndAngle();
					});

					Event.bind(window, 'resize', adjustPositionAndAngle);
					Event.bind(document, 'scroll', adjustPositionAndAngle, true);

					resolve();
				}
				catch (error)
				{
					resolve();
				}
			});
		}

		tourManager.register({
			id: 'landing-phone-field-aha',
			selector: '.landing-ui-component-list-item[data-type="phone"]',
			requiredChildSelector: '.landing-ui-button-icon-edit',
			checkCondition: async () => {
				const isShown = await wasPhoneGuideShown();

				return !isShown;
			},
			showGuide: showPhoneGuide,
			markAsShown: markPhoneGuideAsShown,
		});
	});
})();
