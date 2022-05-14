export class VirtualForm
{
	/**
	 * @private
	 * @param {HTMLFormElement|null} form
	 */
	constructor(form) {
		this.form = form || null;
	}

	/**
	 * @public
	 * @param {string} html
	 * @returns {VirtualForm}
	 */
	static createFromHtml(html) {
		const tempNode = document.createElement('div');
		tempNode.innerHTML = html;

		const form = tempNode.querySelector('form');
		return new VirtualForm(form);
	}

	/**
	 * @public
	 * @param {HTMLElement} node
	 * @returns {VirtualForm}
	 */
	static createFromNode(node) {
		if (node instanceof HTMLFormElement) {
			return new VirtualForm(node);
		}
		const form = node.querySelector('form');
		return new VirtualForm(form);
	}

	/**
	 * @public
	 * @returns {boolean}
	 */
	submit() {
		if (!this.canSubmit()) {
			return false;
		}

		if (this.isVirtual()) {
			const tempNode = document.createElement('div');
			tempNode.style.display = 'none';
			tempNode.append(this.form);

			document.body.appendChild(tempNode);
		}

		HTMLFormElement.prototype.submit.call(this.form);
		return true;
	}

	/**
	 * @public
	 * @returns {boolean}
	 */
	canSubmit() {
		return this.isValidFormObject() && this.containsAllowedInputTypesOnly();
	}

	/**
	 * @private
	 * @returns {boolean}
	 */
	isValidFormObject() {
		return this.form instanceof HTMLFormElement;
	}

	/**
	 * @private
	 * @returns {boolean}
	 */
	containsAllowedInputTypesOnly() {
		if (!this.form || !this.form.elements) {
			return false;
		}

		// eslint-disable-next-line no-plusplus
		for (let i = 0; i < this.form.elements.length; i++) {
			if (!VirtualForm.elementAllowed(this.form.elements[i])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @private
	 * @param element
	 * @returns {boolean}
	 */
	static elementAllowed(element) {
		const allowedTypes = VirtualForm.getAllowedInputTypes();
		if (element instanceof HTMLInputElement) {
			return allowedTypes.indexOf(element.type) !== -1;
		}
		return true;
	}

	/**
	 * @private
	 * @returns {string[]}
	 */
	static getAllowedInputTypes() {
		return ['hidden', 'submit'];
	}

	/**
	 * @public
	 * @returns {boolean}
	 */
	isVirtual() {
		if (this.form) {
			return !document.body.contains(this.form);
		}
		return true;
	}
}