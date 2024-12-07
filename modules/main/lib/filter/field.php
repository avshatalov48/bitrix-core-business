<?php
namespace Bitrix\Main\Filter;

class Field
{
	/** @var DataProvider|null  */
	protected $dataProvider = null;
	/** @var string  */
	protected $id = '';
	/** @var string  */
	protected $name = '';
	/** @var string  */
	protected $type = '';
	/** @var bool  */
	protected $isDefault = false;
	/** @var array|null  */
	protected $data = null;
	/** @var bool  */
	protected $isPartial = false;
	/** @var string  */
	protected $sectionId = '';
	protected $iconParams = [];

	protected ?string $subtype = null;

	public function __construct(DataProvider $dataProvider, $id, array $params = null)
	{
		$this->dataProvider = $dataProvider;
		$this->id = $id;

		if(!is_array($params))
		{
			$params = [];
		}

		$this->name = $params['name'] ?? $id;
		$this->type = $params['type'] ?? '';
		$this->isDefault = $params['default'] ?? false;

		$this->isPartial = $params['partial'] ?? false;
		$this->data = $params['data'] ?? null;
		$this->subtype = $params['subtype'] ?? null;
	}

	/**
	 * Get Data Provider.
	 * @return DataProvider
	 */
	public function getDataProvider()
	{
		return $this->dataProvider;
	}

	/**
	 * Set Data Provider.
	 * @param DataProvider $dataProvider Data Provider.
	 */
	public function setDataProvider(DataProvider $dataProvider)
	{
		$this->dataProvider = $dataProvider;
	}
	/**
	 * Prepare complete field data for field.
	 * @return array|null
	 */
	protected function prepareData()
	{
		return $this->dataProvider->prepareFieldData($this->id);
	}
	/**
	 * Prepare Field additional HTML.
	 * @return string
	 */
	public function prepareHtml()
	{
		return $this->dataProvider->prepareFieldHtml($this);
	}
	/**
	 * Get Field ID.
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}
	/**
	 * Set Field ID.
	 * @param string $id Field ID.
	 */
	public function setID($id)
	{
		$this->id = $id;
	}
	/**
	 * Get localized name.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	/**
	 * Set localized name.
	 * @param string $name Field Name.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
	/**
	 * Get Field Type.
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	/**
	 * Set Field Type.
	 * @param string $type Field Type Name.
	 */
	public function setType($type)
	{
		$this->type = $type;
	}
	/**
	 * Check if default field.
	 * @return bool
	 */
	public function isDefault()
	{
		return $this->isDefault;
	}
	/**
	 * Mark Field as default.
	 * @param bool $isDefault Default Field Flag.
	 */
	public function markAsDefault($isDefault)
	{
		$this->isDefault = $isDefault;
	}
	/**
	 * Check if partial field.
	 * Partial Field does not have data. Field is not completely initialized.
	 * You may call "assemble" to load required data in it.
	 * @return bool
	 */
	public function isPartial()
	{
		return $this->isPartial;
	}
	/**
	 * Get Field Data.
	 * @return array|null
	 */
	public function getData()
	{
		return $this->data;
	}
	/**
	 * Get Data Item by key.
	 * @param string $key Data Item Key.
	 * @return mixed|null
	 */
	public function getDataItem($key)
	{
		return $this->data !== null && isset($this->data[$key]) ? $this->data[$key] : null;
	}

	/**
	 * Get field section id
	 * @return string
	 */
	public function getSectionId(): string
	{
		return $this->sectionId;
	}

	/**
	 * Set field section id
	 * @param string $sectionId
	 */
	public function setSectionId(string $sectionId): void
	{
		$this->sectionId = $sectionId;
	}
	/**
	 * Get field icon url and title
	 * @return array
	 */
	public function getIconParams(): array
	{
		return $this->iconParams;
	}

	/**
	 * Set field icon url and title
	 * @param array $iconParams
	 */
	public function setIconParams(array $iconParams): void
	{
		$this->iconParams = $iconParams;
	}

	/**
	 * Assemble field data.
	 * This method will complete initialization of partially initialized field.
	 * @return void
	 */
	public function assemble()
	{
		if(!$this->isPartial)
		{
			return;
		}

		$this->data = $this->prepareData();
		$this->isPartial = false;

		if(is_array($this->data))
		{
			$html = $this->prepareHtml();
			if($html !== '')
			{
				$this->data['html'] = $html;
			}
		}
	}
	/**
	 * Put Field Parameters in array.
	 * @param array|null $options Options array.
	 * @return array
	 */
	public function toArray(array $options = null)
	{
		if(!is_array($options))
		{
			$options = [];
		}

		$result = [ 'id' => $this->id, 'name' => $this->name ];
		if($this->type !== '')
		{
			$result['type'] = $this->type;
		}

		if($this->isDefault)
		{
			$result['default'] = true;
		}

		if ($this->sectionId !== '')
		{
			$result['sectionId'] = $this->sectionId;
		}
		if (!empty($this->iconParams))
		{
			$result['icon'] = $this->iconParams;
		}

		if(isset($options['lightweight']) && $options['lightweight'])
		{
			$result['lightweight'] = true;
			return $result;
		}

		if($this->isPartial())
		{
			$this->assemble();
		}

		if(is_array($this->data))
		{
			foreach($this->data as $key => $data)
			{
				$result[$key] = $data;
			}
		}

		if ($this->subtype)
		{
			$result['subtype'] = $this->subtype;
		}

		return $result;
	}

	public function getSubtype(): ?string
	{
		return $this->subtype;
	}

	public function setSubtype(?string $subtype): void
	{
		$this->subtype = $subtype;
	}
}