import "./uploader.css";
import "ui.progressbarjs";

export class Uploader
{
	constructor(params = {})
	{
		this.container = params.container;

		if (this.container && typeof params.blurElement === 'undefined')
		{
			params.blurElement = this.container.firstElementChild;
		}
		this.blurElement = params.blurElement;

		this.direction = Uploader.direction[params.direction]? params.direction: Uploader.direction.vertical;

		params.sizes = params.sizes && typeof params.sizes === 'object'? params.sizes: {};

		this.sizes = {
			circle: params.sizes.circle? params.sizes.circle: 54,
			progress: params.sizes.progress? params.sizes.progress: 4,
			margin: params.sizes.margin? params.sizes.margin: 0,
		};

		params.labels = params.labels && typeof params.labels === 'object'? params.labels: {};

		this.labels = {
			loading: params.labels.loading? params.labels.loading: '',
			completed: params.labels.completed? params.labels.completed: '',
			canceled: params.labels.canceled? params.labels.canceled: '',
			cancelTitle: params.labels.cancelTitle? params.labels.cancelTitle: '',
			megabyte: params.labels.megabyte? params.labels.megabyte: 'MB',
		};

		this.cancelCallback = typeof params.cancelCallback === 'function'? params.cancelCallback: null;
		this.destroyCallback = typeof params.destroyCallback === 'function'? params.destroyCallback: null;

		this.icon = Uploader.icon[params.icon]? params.icon: (!this.cancelCallback? Uploader.icon.cloud: Uploader.icon.cancel);

		this.inited = !!this.container;
		this.destroing = false;
	}

	start(params = {})
	{
		if (!this.inited)
		{
			return false;
		}

		clearTimeout(this.timeoutSetIcon);
		clearTimeout(this.timeout);

		this.active = true;
		this.canceled = false;
		this.cancelCallbackDisabled = false;

		this.wrapper = document.createElement('div');
		this.wrapper.classList.add('ui-file-progressbar-loader-wrapper');
		this.wrapper.innerHTML = `
			<div class="ui-file-progressbar-loader">
				<div class="ui-file-progressbar-icon"></div>
				<div class="ui-file-progressbar-progress ui-file-progressbar-rotating"></div>
			</div>
			<div class="ui-file-progressbar-label">${this.labels.loading}</div>
		`;
		this.processLoader = this.wrapper.getElementsByClassName('ui-file-progressbar-loader')[0];
		this.processLoaderIcon = this.wrapper.getElementsByClassName('ui-file-progressbar-icon')[0];
		this.processStatus = this.wrapper.getElementsByClassName('ui-file-progressbar-progress')[0];
		this.proccesLabel = this.wrapper.getElementsByClassName('ui-file-progressbar-label')[0];

		if (this.direction === Uploader.direction.horizontal)
		{
			this.wrapper.classList.add('ui-file-progressbar-loader-horizontal');
		}

		this.container.classList.add('ui-file-progressbar-container-relative');

		this.container.insertBefore(this.wrapper, this.container.firstChild);
		if (this.blurElement)
		{
			this.blurElement.classList.add("ui-file-progressbar-item-blurred");
		}

		let processLoaderStyle = `width: ${this.sizes.circle}px; height: ${this.sizes.circle}px;`;

		if (this.sizes.margin)
		{
			processLoaderStyle = processLoaderStyle+`margin: ${this.sizes.margin}px;`;
			this.proccesLabel.style = `margin: ${this.sizes.margin}px;`;
		}

		this.processLoader.style = processLoaderStyle;

		if (this.cancelCallback)
		{
			this.processLoader.addEventListener('click', (event) =>
			{
				if (this.cancelCallbackDisabled)
				{
					return false;
				}

				this.setProgress(0);

				if (this.labels.canceled)
				{
					this.setProgressTitle(this.labels.canceled);
				}

				this.canceled = event;
				this.active = false;

				clearTimeout(this.timeout);
				this.timeout = setTimeout(() => this.destroy(), 1000);

				return true;
			});

			if (this.labels.cancelTitle)
			{
				this.processLoader.title = this.labels.cancelTitle;
			}
		}

		if (!this.labels.loading)
		{
			this.setProgressTitleVisibility(false);
		}

		this.setIcon(this.icon, true);

		this.bar = new BX.ProgressBarJs.Circle(this.processStatus, {
			easing: "linear",
			strokeWidth: this.sizes.progress,
			color: '#ffffff',
			from: {color: '#ffffff'},
			to: {color: '#ffffff'},
			step: (state, bar) =>
			{
				if (bar.value() == 1)
				{
					clearTimeout(this.timeout);
					this.timeout = setTimeout(() =>
					{
						if (this.labels.completed)
						{
							this.setProgressTitle(this.labels.completed);
						}

						this.setIcon(Uploader.icon.done);

						clearTimeout(this.timeout);
						this.timeout = setTimeout(() => this.destroy(), 1000);

					}, 200);
				}
			}
		});
	}

	setCancelDisable(value = true)
	{
		this.cancelCallbackDisabled = !!value;

		if (this.labels.cancelTitle)
		{
			this.processLoader.title = this.cancelCallbackDisabled? '': this.labels.cancelTitle;
		}
	}

	setIcon(icon, force = false)
	{
		this.processLoaderIcon.style.transform = "scale(0)";

		clearTimeout(this.timeoutSetIcon);
		this.timeoutSetIcon = setTimeout(() => {
			this.processLoaderIcon.classList.remove(
				"ui-file-progressbar-cancel",
				"ui-file-progressbar-done",
				"ui-file-progressbar-cloud",
				"ui-file-progressbar-error"
			);

			if (icon === Uploader.icon.done)
			{
				this.processLoaderIcon.classList.add("ui-file-progressbar-done");
				this.processLoaderIcon.style.transform = "scale(1)";
			}
			else if (icon === Uploader.icon.cancel)
			{
				this.processLoaderIcon.classList.add("ui-file-progressbar-cancel");
				this.processLoaderIcon.style.transform = "scale(1)";
			}
			else if (icon === Uploader.icon.error)
			{
				this.processLoaderIcon.classList.add("ui-file-progressbar-error");
				this.processLoaderIcon.style.transform = "scale(1)";
			}
			else
			{
				this.processLoaderIcon.classList.add("ui-file-progressbar-cloud");
				this.processLoaderIcon.style.transform = "scale(1)";
			}
		}, force? 0: 200);

		return true;
	}

	setProgress(percent)
	{
		if (!this.active || this.canceled)
		{
			return false;
		}

		this.bar.animate(percent / 100, {duration: 500});
	}

	setProgressTitle(text)
	{
		if (!this.proccesLabel)
		{
			return false;
		}

		this.proccesLabel.innerHTML = text;
	}

	setProgressTitleVisibility(visible)
	{
		if(!this.proccesLabel)
		{
			return;
		}

		if (visible)
		{
			if (this.direction === Uploader.direction.horizontal)
			{
				this.wrapper.classList.add('ui-file-progressbar-loader-horizontal');
			}
			this.proccesLabel.style.display = 'block';
		}
		else
		{
			if (this.direction === Uploader.direction.horizontal)
			{
				this.wrapper.classList.remove('ui-file-progressbar-loader-horizontal');
			}
			this.proccesLabel.style.display = 'none';
		}
	};

	setByteSent(sent, total)
	{
		if (this.canceled)
		{
			return false
		}

		this.setProgressTitle((sent/1024/1024).toFixed(2)+" "+this.labels.megabyte+" "+" / "+(total/1024/1024).toFixed(2) + " "+this.labels.megabyte);
	}

	destroy(animated = true)
	{
		clearTimeout(this.timeoutSetIcon);
		clearTimeout(this.timeout);

		if (this.destroing)
		{
			return true;
		}

		this.active = false;
		this.destroing = true;

		this.processLoader.style.transform = "scale(0)";

		if (this.proccesLabel)
		{
			this.proccesLabel.style.transform = "scale(0)";
		}

		if (this.bar)
		{
			this.bar.destroy();
		}

		if (this.blurElement)
		{
			this.blurElement.classList.remove("ui-file-progressbar-item-blurred");
		}

		if (this.canceled && !this.cancelCallbackDisabled)
		{
			if (this.cancelCallback)
			{
				this.cancelCallback(this.canceled);
			}

			this.canceled = false;
		}

		if (animated)
		{
			this.timeout = setTimeout(() => this.destroyFinally(), 400);
		}
		else
		{
			this.destroyFinally();
		}
	}

	destroyFinally()
	{
		if (this.container)
		{
			this.container.classList.remove('ui-file-progressbar-container-relative');
			this.container.removeChild(this.wrapper);
		}

		if (this.destroyCallback)
		{
			this.destroyCallback();
		}
	}
}

Uploader.direction = {
	horizontal: 'horizontal',
	vertical: 'vertical',
};

Uploader.icon = {
	cloud: 'cloud',
	cancel: 'cancel',
	error: 'error',
	done: 'done',
};