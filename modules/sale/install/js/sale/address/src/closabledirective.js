let handleOutsideClick;

export const ClosableDirective = {
	bind (el, binding, vnode) {
		handleOutsideClick = (e) => {
			if (e.type === 'mousedown' && e.which !== 1)
			{
				return;
			}

			e.stopPropagation();
			const { handler, exclude } = binding.value;
			let clickedOnExcludedEl = false;
			exclude.forEach(refName => {
				if (!clickedOnExcludedEl) {
					const excludedEl = vnode.context.$refs[refName];
					clickedOnExcludedEl = excludedEl.contains(e.target);
				}
			});

			/**
			 * Click inside map wrapper
			 */
			if (e.target.closest('.location-map-wrapper'))
			{
				clickedOnExcludedEl = true;
			}

			if (!el.contains(e.target) && !clickedOnExcludedEl) {
				vnode.context[handler]()
			}
		};
		document.addEventListener('mousedown', handleOutsideClick);
		document.addEventListener('touchstart', handleOutsideClick);
	},

	unbind () {
		document.removeEventListener('mousedown', handleOutsideClick);
		document.removeEventListener('touchstart', handleOutsideClick);
	}
};
