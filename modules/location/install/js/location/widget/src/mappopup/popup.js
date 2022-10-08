import {Popup as MainPopup} from 'main.popup';
import {Dom} from 'main.core';

/**
 * Popup window, which contains map
 */
export default class Popup extends MainPopup
{
	getBindElement()
	{
		return this.bindElement;
	}

	adjustPosition(bindOptions: {
		forceBindPosition?: boolean,
		forceLeft?: boolean,
		forceTop?: boolean,
		position?: 'right' | 'top' | 'bootom'
	}): void
	{
		let isCustomPosition, isCustomPositionSuccess;

		if (this.bindOptions.position && this.bindOptions.position === 'right')
		{
			isCustomPosition = true;
			isCustomPositionSuccess = this.#adjustRightPosition();
		}

		if (!(isCustomPosition && isCustomPositionSuccess))
		{
			super.adjustPosition(bindOptions);
		}
	}

	/**
	 * Adjust the popup in right position
	 * @returns {boolean} an indicator whether or not we have managed to adjust the popup successfully
	 */
	#adjustRightPosition(): boolean
	{
		const bindElRect = this.bindElement.getBoundingClientRect();
		const popupHeight = this.getPopupContainer().offsetHeight;
		const popupWidth = this.getPopupContainer().offsetWidth;

		/**
		 * Check if the popup fits in the viewport
		 */
		if ((bindElRect.left + bindElRect.width + popupWidth) > document.documentElement.clientWidth)
		{
			return false;
		}

		let angleOffsetY = popupHeight / 2;

		const left = bindElRect.left + bindElRect.width + 10;
		let top = window.pageYOffset + bindElRect.top + bindElRect.height / 2 - popupHeight / 2;

		if(top < window.pageYOffset)
		{
			angleOffsetY -= window.pageYOffset - top;
			top = window.pageYOffset;
		}
		else if(top > window.pageYOffset + document.body.clientHeight - popupHeight)
		{
			angleOffsetY += top - (window.pageYOffset + document.body.clientHeight - popupHeight);
			top = window.pageYOffset + document.body.clientHeight - popupHeight;
		}

		this.setAngle({position: 'left', offset: angleOffsetY});

		Dom.adjust(this.popupContainer, {
			style: {
				top: `${top}px`,
				left: `${left}px`,
				zIndex: this.getZindex()
			}
		});

		return true;
	}
}
