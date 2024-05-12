import { Event } from 'main.core';
import { Menu, Popup } from 'main.popup';

export default function bindShowOnHover(popup: Menu|Popup)
{
	if (popup instanceof Menu)
	{
		popup = popup.getPopupWindow();
	}

	const bindElement = popup.bindElement;
	const container = popup.getPopupContainer();

	let hoverElement = null;

	const closeMenuHandler = () => {
		setTimeout(() => {
			if (!container.contains(hoverElement) && !bindElement.contains(hoverElement))
			{
				popup.close();
			}
		}, 100);
	};
	const showMenuHandler = () => {
		setTimeout(() => {
			if (bindElement.contains(hoverElement))
			{
				popup.show();
			}
		}, 300);
	};
	const clickHandler = () => {
		if (!popup.isShown())
		{
			popup.show();
		}
	};

	Event.bind(document, 'mouseover', (event) => {
		hoverElement = event.target;
	});
	Event.bind(bindElement, 'mouseenter', showMenuHandler);
	Event.bind(bindElement, 'mouseleave', closeMenuHandler);
	Event.bind(container, 'mouseleave', closeMenuHandler);
	Event.bind(bindElement, 'click', clickHandler);

	let popupWidth = popup.getPopupContainer().offsetWidth;
	let elementWidth = popup.bindElement.offsetWidth;
	const angleLeft = Popup.getOption('angleMinBottom');
	const handleScroll = () => {
		popup.adjustPosition();
		if (popup.angle)
		{
			popup.setAngle({ offset: popupWidth / 2 + angleLeft });
		}
	};

	popup.subscribeFromOptions({
		onShow: () => {
			popupWidth = popup.getPopupContainer().offsetWidth;
			elementWidth = popup.bindElement.offsetWidth;

			popup.setOffset({ offsetLeft: elementWidth / 2 - popupWidth / 2 });
			popup.adjustPosition();

			if (popup.angle)
			{
				popup.setAngle({ offset: popupWidth / 2 + angleLeft });
			}

			document.addEventListener('scroll', handleScroll, true);
		},
		onAfterPopupShow: () => {
			const left = popup.bindElement.getBoundingClientRect().left + elementWidth / 2 - popupWidth / 2;
			if (left < 0 || left + popupWidth > window.innerWidth)
			{
				return;
			}

			popup.getPopupContainer().style.left = left + 'px';
			if (popup.angle)
			{
				popup.angle.element.style.marginLeft = popupWidth / 2 - 16 + 'px';
			}
		},
		onClose: () => {
			document.removeEventListener('scroll', handleScroll, true);
		},
	});
}