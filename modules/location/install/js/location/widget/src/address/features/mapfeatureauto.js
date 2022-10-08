import {Address, ControlMode} from 'location.core';
import State from '../../state';
import MapFeature from './mapfeature';

/**
 * Map feature for the address widget with auto map opening / closing behavior
 */
export default class MapFeatureAuto extends MapFeature
{
	#showMapTimerId = null;
	#showMapDelay = 700;
	#closeMapTimerId = null;
	#closeMapDelay = 800;

	#isDestroyed = false;

	/**
	 * Render Widget
	 * @param {AddressRenderProps} props
	 */
	render(props: AddressRenderProps): void
	{
		super.render(props);

		this.addressWidget.controlWrapper.addEventListener('click', this.#onControlWrapperClick.bind(this));
		this.addressWidget.controlWrapper.addEventListener('mouseover', this.#processOnMouseOver.bind(this));
		this.addressWidget.controlWrapper.addEventListener('mouseout', this.#processOnMouseOut.bind(this));

		document.addEventListener('click', this.#onDocumentClick.bind(this));

		this.map.onMouseOverSubscribe(this.#processOnMouseOver.bind(this));
		this.map.onMouseOutSubscribe(this.#processOnMouseOut.bind(this));
	}

	// eslint-disable-next-line no-unused-vars
	#onControlWrapperClick(event)
	{
		if (this.#isDestroyed)
		{
			return;
		}

		if (this.addressWidget.mode === ControlMode.view)
		{
			if (this.map.isShown())
			{
				this.closeMap();
			}
			else
			{
				clearTimeout(this.#showMapTimerId);
			}
		}
		else if (this.addressWidget.mode === ControlMode.edit && this._saveResourceStrategy === false)
		{
			if (this.addressWidget.address && !this.map.isShown() && event.target === this.addressWidget.inputNode)
			{
				this.showMap();
			}
		}
	}

	#onDocumentClick(event)
	{
		if (this.#isDestroyed)
		{
			return;
		}

		if (this.addressWidget.inputNode !== event.target)
		{
			this.closeMap();
		}
	}

	#processOnMouseOver()
	{
		if (this.#isDestroyed)
		{
			return;
		}

		clearTimeout(this.#showMapTimerId);
		clearTimeout(this.#closeMapTimerId);

		if (this.addressWidget.mode !== ControlMode.view)
		{
			return;
		}

		if (this.addressWidget.address && !this.map.isShown())
		{
			this.#showMapTimerId = setTimeout(() => {
					this.showMap();
				},
				this.#showMapDelay
			);
		}
	}

	#processOnMouseOut()
	{
		if (this.#isDestroyed)
		{
			return;
		}

		clearTimeout(this.#showMapTimerId);
		clearTimeout(this.#closeMapTimerId);

		if (this.addressWidget.mode !== ControlMode.view)
		{
			return;
		}

		if (this.addressWidget.mode === ControlMode.view && this.map.isShown())
		{
			this.#closeMapTimerId = setTimeout(() => {
					this.closeMap();
				},
				this.#closeMapDelay
			);
		}
	}

	setAddress(address: ?Address): void
	{
		if (!address)
		{
			this.closeMap();
		}

		this.map.address = address;

		if (address && this.addressWidget.state !== State.DATA_SUPPOSED)
		{
			this.showMap();
		}
	}

	destroy()
	{
		if (this.#isDestroyed)
		{
			return;
		}

		document.removeEventListener('click', this.#onDocumentClick);

		if (this.addressWidget.controlWrapper)
		{
			this.addressWidget.controlWrapper.removeEventListener('click', this.#onControlWrapperClick);
			this.addressWidget.controlWrapper.removeEventListener('mouseover', this.#processOnMouseOver);
			this.addressWidget.controlWrapper.removeEventListener('mouseout', this.#processOnMouseOut);
		}

		this.#showMapTimerId = null;
		this.#closeMapTimerId = null;

		super.destroy();
		this.#isDestroyed = true;
	}
}
