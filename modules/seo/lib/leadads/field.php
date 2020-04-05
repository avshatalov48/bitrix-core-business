<?

namespace Bitrix\Seo\LeadAds;

use Bitrix\Main\ArgumentNullException;

/**
 * Class Field.
 * Metadata of question field in form.
 *
 * @package Bitrix\Seo\LeadAds
 */
class Field
{
	const TYPE_INPUT = 'input';
	const TYPE_TEXT_AREA = 'textarea';
	const TYPE_RADIO = 'radio';
	const TYPE_CHECKBOX = 'checkbox';
	const TYPE_SELECT = 'select';

	protected $type = self::TYPE_INPUT;
	protected $name = null;
	protected $label = null;
	protected $key = null;
	protected $options = [];

	/**
	 * Create field.
	 *
	 * @param string $type Type.
	 * @param string|null $name Name.
	 * @param string|null $label Label.
	 * @param string|null $key Key.
	 * @return static
	 */
	public static function create($type = self::TYPE_INPUT, $name = null, $label = null, $key = null)
	{
		return new static($type, $name, $label, $key);
	}

	public function getMapItem(array $map = [])
	{

	}

	/**
	 * Convert to array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return [
			'type' => $this->type,
			'name' => $this->name,
			'label' => $this->label,
			'key' => $this->key,
			'options' => $this->options,
		];
	}

	/**
	 * Field constructor.
	 *
	 * @param string $type Type.
	 * @param string|null $name Name.
	 * @param string|null $label Label.
	 * @param string|null $key Key.
	 */
	public function __construct($type = self::TYPE_INPUT, $name = null, $label = null, $key = null)
	{
		$this->type = $type;
		$this->name = $name;
		$this->label = $label;
		$this->key = $key;
	}

	/**
	 * Add option.
	 *
	 * @param string $key Key.
	 * @param string $label Label.
	 * @throws ArgumentNullException
	 * @return $this
	 */
	public function addOption($key, $label)
	{
		if (empty($key))
		{
			throw new ArgumentNullException('$key');
		}
		if (empty($label))
		{
			throw new ArgumentNullException('$label');
		}

		$this->options[] = [
			'key' => $key,
			'label' => $label
		];

		return $this;
	}

	/**
	 * Set options.
	 *
	 * @param array $options Options.
	 * @return $this
	 */
	public function setOptions(array $options)
	{
		$this->options = [];
		foreach ($options as $option)
		{
			$this->addOption($option['key'], $option['label']);
		}

		return $this;
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get label.
	 *
	 * @return null|string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Get key.
	 *
	 * @return null|string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Get options.
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}
}