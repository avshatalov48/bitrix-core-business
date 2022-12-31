import {Dom, Runtime, Type} from 'main.core';
import {IconPanel} from 'landing.ui.panel.iconpanel';
import {Image} from 'landing.ui.field.image'
import {IconOptionsCard} from 'landing.ui.card.iconoptionscard';


import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Field
 */
export class Icon extends Image
{
	constructor(data)
	{
		super(data);
		this.uploadButton.layout.innerText = BX.Landing.Loc.getMessage("LANDING_ICONS_FIELD_BUTTON_REPLACE");
		this.editButton.layout.hidden = true;
		this.clearButton.layout.hidden = true;

		this.dropzone.removeEventListener("dragover", this.onDragOver);
		this.dropzone.removeEventListener("dragleave", this.onDragLeave);
		this.dropzone.removeEventListener("drop", this.onDrop);
		this.preview.removeEventListener("dragenter", this.onImageDragEnter);

		this.options = new IconOptionsCard();
		Dom.append(this.options.getLayout(), this.right);
		this.onOptionClick = this.onOptionClick.bind(this);
		this.options.subscribe('onChange', this.onOptionClick);

		const sourceClassList = this.content.classList;
		const newClassList = [];
		IconPanel
			.getLibraries()
			.then(function (libraries)
			{
				if (libraries.length === 0)
				{
					this.uploadButton.disable();
				}
				else
				{
					libraries.forEach(library => {
						library.categories.forEach(category => {
							category.items.forEach(item => {
								let itemClasses = '';
								if (Type.isObject(item))
								{
									itemClasses = item.options.join(' ');
								}
								else
								{
									itemClasses = item;
								}

								const iconClasses = itemClasses.split(" ");
								iconClasses.forEach(iconClass => {
									if (
										sourceClassList.indexOf(iconClass) !== -1
										&& newClassList.indexOf(iconClass) === -1
									)
									{
										newClassList.push(iconClass);
									}
								});
							});
						});
					});

					this.icon.innerHTML = "<span class=\"test " + newClassList.join(" ") + "\"></span>";
				}

				this.options.setOptionsByItem(newClassList);
			}.bind(this));
	}

	onUploadClick(event)
	{
		event.preventDefault();

		IconPanel
			.getInstance()
			.show()
			.then(result => {
				this.options.setOptions(result.iconOptions, result.iconClassName);
				this.setValue({
					type: "icon",
					classList: result.iconClassName.split(" ")
				});
			});
	}

	onOptionClick(event)
	{
		const classList = event.getData().option.split(' ');
		this.setValue({
			type: 'icon',
			classList
		});
	}

	isChanged()
	{
		return this.getValue().classList.some(function (className)
		{
			return this.content.classList.indexOf(className) === -1;
		}, this);
	}

	getValue()
	{
		var classList = this.classList;

		if (this.selector)
		{
			var selectorClassname = this.selector.split("@")[0].replace(".", "");
			classList = Runtime.clone(this.classList).concat([selectorClassname]);
			classList = BX.Landing.Utils.arrayUnique(classList);
		}

		return {
			type: "icon",
			src: "",
			id: -1,
			alt: "",
			classList: classList,
			url: Object.assign({}, this.url.getValue(), {enabled: true}),
		};
	}

	reset()
	{
		this.setValue({
			type: "icon",
			src: "",
			id: -1,
			alt: "",
			classList: [],
			url: '',
		});
	}
}