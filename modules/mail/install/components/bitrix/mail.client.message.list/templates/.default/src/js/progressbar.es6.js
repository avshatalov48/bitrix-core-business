export class ProgressBar
{
	#node;
	#errorTitleNode;
	#errorTextNode;
	#errorBoxNode;
	#syncButton;
	#errorHintNode;

	constructor(node)
	{
		this.#node = node;
	}

	setSyncButton(button)
	{
		this.#syncButton = button;
	}

	getSyncButton()
	{
		return this.#syncButton;
	}

	getErrorBoxNode()
	{
		return this.#errorBoxNode;
	}

	setErrorBoxNode(errorBoxNode)
	{
		this.#errorBoxNode = errorBoxNode;
	}

	setErrorTitleNode(errorTitleNode)
	{
		this.#errorTitleNode = errorTitleNode;
	}

	setErrorTextNode(errorTextNode)
	{
		this.#errorTextNode = errorTextNode;
	}

	setErrorHintNode(errorHintNode)
	{
		this.#errorHintNode = errorHintNode;
	}

	getErrorTextNode()
	{
		return this.#errorTextNode;
	}

	getErrorHintNode()
	{
		return this.#errorHintNode;
	}

	getErrorTitleNode()
	{
		return this.#errorTitleNode;
	}

	show()
	{
		if(this.getSyncButton() !== undefined) this.getSyncButton().setWaiting(true);
		this.#node.classList.add("mail-progress-show");
		this.#node.classList.remove("mail-progress-hide");
	}

	hide()
	{
		if(this.getSyncButton() !== undefined) this.getSyncButton().setWaiting(false);
		this.#node.classList.add("mail-progress-hide");
		this.#node.classList.remove("mail-progress-show");
	}

	hideErrorBox()
	{
		this.#errorBoxNode.classList.add("mail-hidden-element");
		this.#errorBoxNode.classList.remove("mail-visible-element");
	}

	showErrorBox()
	{
		this.#errorBoxNode.classList.add("mail-visible-element");
		this.#errorBoxNode.classList.remove("mail-hidden-element");
	}
}