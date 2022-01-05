export class FeaturesManager
{
	constructor()
	{
		const containerNode = document.getElementById('additional-block-features');
		if (!containerNode)
		{
			return;
		}

		containerNode.querySelectorAll('.socialnetwork-group-create-ex__project-instruments--icon-action.--edit').forEach((editButton) => {

			editButton.addEventListener('click', (e) => {

				const editButton = e.currentTarget;
				const featureNode = editButton.closest('.socialnetwork-group-create-ex__project-instruments--item');
				if (featureNode)
				{
					featureNode.classList.add('--custom-value');

					const inputNode = featureNode.querySelector('[data-role="feature-input-text"]');
					const textNode = featureNode.querySelector('[data-role="feature-label"]');
					if (
						inputNode
						&& textNode
					)
					{
						inputNode.value = textNode.innerText;
					}
				}

				e.preventDefault();
			});

		});

		containerNode.querySelectorAll('.socialnetwork-group-create-ex__project-instruments--icon-action.--revert').forEach((cancelButton) => {

			cancelButton.addEventListener('click', (e) => {

				const editButton = e.currentTarget;
				const featureNode = editButton.closest('.socialnetwork-group-create-ex__project-instruments--item');
				if (featureNode)
				{
					featureNode.classList.remove('--custom-value');

					const inputNode = featureNode.querySelector('[data-role="feature-input-text"]');
					if (inputNode)
					{
						inputNode.value = '';
					}
				}

				e.preventDefault();
			});

		});
	}
}
