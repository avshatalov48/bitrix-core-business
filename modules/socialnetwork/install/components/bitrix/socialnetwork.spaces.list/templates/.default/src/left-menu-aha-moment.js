import { ajax, Dom, Loc, Runtime, Type } from 'main.core';

export class LeftMenuAhaMoment
{
	showAhaMoment(): void
	{
		const menuSwitcherNode = document.querySelector('.menu-items-header .menu-switcher');
		if (Type.isDomNode(menuSwitcherNode))
		{
			this.#showSpotlight(menuSwitcherNode);
		}
		this.#dontShowCollapseMenuAhaMoment();
	}

	#dontShowCollapseMenuAhaMoment(): void
	{
		ajax.runAction('socialnetwork.api.ahamoment.dontShowCollapseMenuAhaMoment');
	}

	#showSpotlight(targetElement): void
	{
		Runtime.loadExtension(['spotlight', 'ui.tour']).then(() => {
			const spotlight = new BX.SpotLight({
				targetElement,
				targetVertex: 'middle-center',
			});

			Dom.addClass(targetElement, '--active');
			spotlight.bindEvents({
				onTargetEnter: () => {
					Dom.removeClass(targetElement, '--active');
					spotlight.close();
				},
			});

			spotlight.setColor('#2fc6f6');
			spotlight.show();

			this.#showAhaMoment(targetElement, spotlight);
		});
	}

	async #showAhaMoment(node, spotlight): void
	{
		const { Guide } = await Runtime.loadExtension('ui.tour');

		const guide = new Guide({
			simpleMode: true,
			onEvents: true,
			steps: [
				{
					target: node,
					title: Loc.getMessage('SOCIALNETWORK_SPACES_COLLAPSE_MENU_AHA_MOMENT_TITLE'),
					text: Loc.getMessage('SOCIALNETWORK_SPACES_COLLAPSE_MENU_AHA_MOMENT_TEXT'),
					position: 'bottom',
					condition: {
						top: true,
						bottom: false,
						color: 'primary',
					},
				},
			],
		});

		guide.showNextStep();

		const guidePopup = guide.getPopup();
		guidePopup.setWidth(380);
		guidePopup.getContentContainer().style.paddingRight = getComputedStyle(guidePopup.closeIcon)['width'];
		guidePopup.setAngle({ offset: node.offsetWidth / 2 - 5 });
		guidePopup.subscribe('onClose', () => spotlight.close());
		guidePopup.setAutoHide(true);
		guidePopup.getPopupContainer().style.marginLeft = '5px';
		guidePopup.angle.element.style.left = '-1px';
	}
}