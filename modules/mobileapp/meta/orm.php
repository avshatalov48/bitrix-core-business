<?php

/* ORMENTITYANNOTATION:Bitrix\MobileApp\AppTable:mobileapp/lib/app.php:a54a3c342ffbe76b613c40e72867171c */
namespace Bitrix\MobileApp {
	/**
	 * EO_App
	 * @see \Bitrix\MobileApp\AppTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getCode()
	 * @method \Bitrix\MobileApp\EO_App setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string getShortName()
	 * @method \Bitrix\MobileApp\EO_App setShortName(\string|\Bitrix\Main\DB\SqlExpression $shortName)
	 * @method bool hasShortName()
	 * @method bool isShortNameFilled()
	 * @method bool isShortNameChanged()
	 * @method \string remindActualShortName()
	 * @method \string requireShortName()
	 * @method \Bitrix\MobileApp\EO_App resetShortName()
	 * @method \Bitrix\MobileApp\EO_App unsetShortName()
	 * @method \string fillShortName()
	 * @method \string getName()
	 * @method \Bitrix\MobileApp\EO_App setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\MobileApp\EO_App resetName()
	 * @method \Bitrix\MobileApp\EO_App unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\MobileApp\EO_App setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\MobileApp\EO_App resetDescription()
	 * @method \Bitrix\MobileApp\EO_App unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getFiles()
	 * @method \Bitrix\MobileApp\EO_App setFiles(\string|\Bitrix\Main\DB\SqlExpression $files)
	 * @method bool hasFiles()
	 * @method bool isFilesFilled()
	 * @method bool isFilesChanged()
	 * @method \string remindActualFiles()
	 * @method \string requireFiles()
	 * @method \Bitrix\MobileApp\EO_App resetFiles()
	 * @method \Bitrix\MobileApp\EO_App unsetFiles()
	 * @method \string fillFiles()
	 * @method \string getLaunchIcons()
	 * @method \Bitrix\MobileApp\EO_App setLaunchIcons(\string|\Bitrix\Main\DB\SqlExpression $launchIcons)
	 * @method bool hasLaunchIcons()
	 * @method bool isLaunchIconsFilled()
	 * @method bool isLaunchIconsChanged()
	 * @method \string remindActualLaunchIcons()
	 * @method \string requireLaunchIcons()
	 * @method \Bitrix\MobileApp\EO_App resetLaunchIcons()
	 * @method \Bitrix\MobileApp\EO_App unsetLaunchIcons()
	 * @method \string fillLaunchIcons()
	 * @method \string getLaunchScreens()
	 * @method \Bitrix\MobileApp\EO_App setLaunchScreens(\string|\Bitrix\Main\DB\SqlExpression $launchScreens)
	 * @method bool hasLaunchScreens()
	 * @method bool isLaunchScreensFilled()
	 * @method bool isLaunchScreensChanged()
	 * @method \string remindActualLaunchScreens()
	 * @method \string requireLaunchScreens()
	 * @method \Bitrix\MobileApp\EO_App resetLaunchScreens()
	 * @method \Bitrix\MobileApp\EO_App unsetLaunchScreens()
	 * @method \string fillLaunchScreens()
	 * @method \string getFolder()
	 * @method \Bitrix\MobileApp\EO_App setFolder(\string|\Bitrix\Main\DB\SqlExpression $folder)
	 * @method bool hasFolder()
	 * @method bool isFolderFilled()
	 * @method bool isFolderChanged()
	 * @method \string remindActualFolder()
	 * @method \string requireFolder()
	 * @method \Bitrix\MobileApp\EO_App resetFolder()
	 * @method \Bitrix\MobileApp\EO_App unsetFolder()
	 * @method \string fillFolder()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\MobileApp\EO_App setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\MobileApp\EO_App resetDateCreate()
	 * @method \Bitrix\MobileApp\EO_App unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_Config getConfig()
	 * @method \Bitrix\MobileApp\Designer\EO_Config remindActualConfig()
	 * @method \Bitrix\MobileApp\Designer\EO_Config requireConfig()
	 * @method \Bitrix\MobileApp\EO_App setConfig(\Bitrix\MobileApp\Designer\EO_Config $object)
	 * @method \Bitrix\MobileApp\EO_App resetConfig()
	 * @method \Bitrix\MobileApp\EO_App unsetConfig()
	 * @method bool hasConfig()
	 * @method bool isConfigFilled()
	 * @method bool isConfigChanged()
	 * @method \Bitrix\MobileApp\Designer\EO_Config fillConfig()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\MobileApp\EO_App set($fieldName, $value)
	 * @method \Bitrix\MobileApp\EO_App reset($fieldName)
	 * @method \Bitrix\MobileApp\EO_App unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\MobileApp\EO_App wakeUp($data)
	 */
	class EO_App {
		/* @var \Bitrix\MobileApp\AppTable */
		static public $dataClass = '\Bitrix\MobileApp\AppTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\MobileApp {
	/**
	 * EO_App_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getCodeList()
	 * @method \string[] getShortNameList()
	 * @method \string[] fillShortName()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getFilesList()
	 * @method \string[] fillFiles()
	 * @method \string[] getLaunchIconsList()
	 * @method \string[] fillLaunchIcons()
	 * @method \string[] getLaunchScreensList()
	 * @method \string[] fillLaunchScreens()
	 * @method \string[] getFolderList()
	 * @method \string[] fillFolder()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_Config[] getConfigList()
	 * @method \Bitrix\MobileApp\EO_App_Collection getConfigCollection()
	 * @method \Bitrix\MobileApp\Designer\EO_Config_Collection fillConfig()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\MobileApp\EO_App $object)
	 * @method bool has(\Bitrix\MobileApp\EO_App $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\MobileApp\EO_App getByPrimary($primary)
	 * @method \Bitrix\MobileApp\EO_App[] getAll()
	 * @method bool remove(\Bitrix\MobileApp\EO_App $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\MobileApp\EO_App_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\MobileApp\EO_App current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_App_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\MobileApp\AppTable */
		static public $dataClass = '\Bitrix\MobileApp\AppTable';
	}
}
namespace Bitrix\MobileApp {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_App_Result exec()
	 * @method \Bitrix\MobileApp\EO_App fetchObject()
	 * @method \Bitrix\MobileApp\EO_App_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_App_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\MobileApp\EO_App fetchObject()
	 * @method \Bitrix\MobileApp\EO_App_Collection fetchCollection()
	 */
	class EO_App_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\MobileApp\EO_App createObject($setDefaultValues = true)
	 * @method \Bitrix\MobileApp\EO_App_Collection createCollection()
	 * @method \Bitrix\MobileApp\EO_App wakeUpObject($row)
	 * @method \Bitrix\MobileApp\EO_App_Collection wakeUpCollection($rows)
	 */
	class EO_App_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\MobileApp\Designer\AppTable:mobileapp/lib/designer/app.php:00889f6b4d19f512324a3f6a00db83e6 */
namespace Bitrix\MobileApp\Designer {
	/**
	 * EO_App
	 * @see \Bitrix\MobileApp\Designer\AppTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getCode()
	 * @method \Bitrix\MobileApp\Designer\EO_App setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string getShortName()
	 * @method \Bitrix\MobileApp\Designer\EO_App setShortName(\string|\Bitrix\Main\DB\SqlExpression $shortName)
	 * @method bool hasShortName()
	 * @method bool isShortNameFilled()
	 * @method bool isShortNameChanged()
	 * @method \string remindActualShortName()
	 * @method \string requireShortName()
	 * @method \Bitrix\MobileApp\Designer\EO_App resetShortName()
	 * @method \Bitrix\MobileApp\Designer\EO_App unsetShortName()
	 * @method \string fillShortName()
	 * @method \string getName()
	 * @method \Bitrix\MobileApp\Designer\EO_App setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\MobileApp\Designer\EO_App resetName()
	 * @method \Bitrix\MobileApp\Designer\EO_App unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\MobileApp\Designer\EO_App setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\MobileApp\Designer\EO_App resetDescription()
	 * @method \Bitrix\MobileApp\Designer\EO_App unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getFiles()
	 * @method \Bitrix\MobileApp\Designer\EO_App setFiles(\string|\Bitrix\Main\DB\SqlExpression $files)
	 * @method bool hasFiles()
	 * @method bool isFilesFilled()
	 * @method bool isFilesChanged()
	 * @method \string remindActualFiles()
	 * @method \string requireFiles()
	 * @method \Bitrix\MobileApp\Designer\EO_App resetFiles()
	 * @method \Bitrix\MobileApp\Designer\EO_App unsetFiles()
	 * @method \string fillFiles()
	 * @method \string getLaunchIcons()
	 * @method \Bitrix\MobileApp\Designer\EO_App setLaunchIcons(\string|\Bitrix\Main\DB\SqlExpression $launchIcons)
	 * @method bool hasLaunchIcons()
	 * @method bool isLaunchIconsFilled()
	 * @method bool isLaunchIconsChanged()
	 * @method \string remindActualLaunchIcons()
	 * @method \string requireLaunchIcons()
	 * @method \Bitrix\MobileApp\Designer\EO_App resetLaunchIcons()
	 * @method \Bitrix\MobileApp\Designer\EO_App unsetLaunchIcons()
	 * @method \string fillLaunchIcons()
	 * @method \string getLaunchScreens()
	 * @method \Bitrix\MobileApp\Designer\EO_App setLaunchScreens(\string|\Bitrix\Main\DB\SqlExpression $launchScreens)
	 * @method bool hasLaunchScreens()
	 * @method bool isLaunchScreensFilled()
	 * @method bool isLaunchScreensChanged()
	 * @method \string remindActualLaunchScreens()
	 * @method \string requireLaunchScreens()
	 * @method \Bitrix\MobileApp\Designer\EO_App resetLaunchScreens()
	 * @method \Bitrix\MobileApp\Designer\EO_App unsetLaunchScreens()
	 * @method \string fillLaunchScreens()
	 * @method \string getFolder()
	 * @method \Bitrix\MobileApp\Designer\EO_App setFolder(\string|\Bitrix\Main\DB\SqlExpression $folder)
	 * @method bool hasFolder()
	 * @method bool isFolderFilled()
	 * @method bool isFolderChanged()
	 * @method \string remindActualFolder()
	 * @method \string requireFolder()
	 * @method \Bitrix\MobileApp\Designer\EO_App resetFolder()
	 * @method \Bitrix\MobileApp\Designer\EO_App unsetFolder()
	 * @method \string fillFolder()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_App setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_App resetDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_App unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_Config getConfig()
	 * @method \Bitrix\MobileApp\Designer\EO_Config remindActualConfig()
	 * @method \Bitrix\MobileApp\Designer\EO_Config requireConfig()
	 * @method \Bitrix\MobileApp\Designer\EO_App setConfig(\Bitrix\MobileApp\Designer\EO_Config $object)
	 * @method \Bitrix\MobileApp\Designer\EO_App resetConfig()
	 * @method \Bitrix\MobileApp\Designer\EO_App unsetConfig()
	 * @method bool hasConfig()
	 * @method bool isConfigFilled()
	 * @method bool isConfigChanged()
	 * @method \Bitrix\MobileApp\Designer\EO_Config fillConfig()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\MobileApp\Designer\EO_App set($fieldName, $value)
	 * @method \Bitrix\MobileApp\Designer\EO_App reset($fieldName)
	 * @method \Bitrix\MobileApp\Designer\EO_App unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\MobileApp\Designer\EO_App wakeUp($data)
	 */
	class EO_App {
		/* @var \Bitrix\MobileApp\Designer\AppTable */
		static public $dataClass = '\Bitrix\MobileApp\Designer\AppTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\MobileApp\Designer {
	/**
	 * EO_App_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getCodeList()
	 * @method \string[] getShortNameList()
	 * @method \string[] fillShortName()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getFilesList()
	 * @method \string[] fillFiles()
	 * @method \string[] getLaunchIconsList()
	 * @method \string[] fillLaunchIcons()
	 * @method \string[] getLaunchScreensList()
	 * @method \string[] fillLaunchScreens()
	 * @method \string[] getFolderList()
	 * @method \string[] fillFolder()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_Config[] getConfigList()
	 * @method \Bitrix\MobileApp\Designer\EO_App_Collection getConfigCollection()
	 * @method \Bitrix\MobileApp\Designer\EO_Config_Collection fillConfig()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\MobileApp\Designer\EO_App $object)
	 * @method bool has(\Bitrix\MobileApp\Designer\EO_App $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\MobileApp\Designer\EO_App getByPrimary($primary)
	 * @method \Bitrix\MobileApp\Designer\EO_App[] getAll()
	 * @method bool remove(\Bitrix\MobileApp\Designer\EO_App $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\MobileApp\Designer\EO_App_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\MobileApp\Designer\EO_App current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_App_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\MobileApp\Designer\AppTable */
		static public $dataClass = '\Bitrix\MobileApp\Designer\AppTable';
	}
}
namespace Bitrix\MobileApp\Designer {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_App_Result exec()
	 * @method \Bitrix\MobileApp\Designer\EO_App fetchObject()
	 * @method \Bitrix\MobileApp\Designer\EO_App_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_App_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\MobileApp\Designer\EO_App fetchObject()
	 * @method \Bitrix\MobileApp\Designer\EO_App_Collection fetchCollection()
	 */
	class EO_App_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\MobileApp\Designer\EO_App createObject($setDefaultValues = true)
	 * @method \Bitrix\MobileApp\Designer\EO_App_Collection createCollection()
	 * @method \Bitrix\MobileApp\Designer\EO_App wakeUpObject($row)
	 * @method \Bitrix\MobileApp\Designer\EO_App_Collection wakeUpCollection($rows)
	 */
	class EO_App_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\MobileApp\Designer\ConfigTable:mobileapp/lib/designer/config.php:3a63c617d5d65ae932dc51a7b4d160b6 */
namespace Bitrix\MobileApp\Designer {
	/**
	 * EO_Config
	 * @see \Bitrix\MobileApp\Designer\ConfigTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getAppCode()
	 * @method \Bitrix\MobileApp\Designer\EO_Config setAppCode(\string|\Bitrix\Main\DB\SqlExpression $appCode)
	 * @method bool hasAppCode()
	 * @method bool isAppCodeFilled()
	 * @method bool isAppCodeChanged()
	 * @method \string getPlatform()
	 * @method \Bitrix\MobileApp\Designer\EO_Config setPlatform(\string|\Bitrix\Main\DB\SqlExpression $platform)
	 * @method bool hasPlatform()
	 * @method bool isPlatformFilled()
	 * @method bool isPlatformChanged()
	 * @method \string getParams()
	 * @method \Bitrix\MobileApp\Designer\EO_Config setParams(\string|\Bitrix\Main\DB\SqlExpression $params)
	 * @method bool hasParams()
	 * @method bool isParamsFilled()
	 * @method bool isParamsChanged()
	 * @method \string remindActualParams()
	 * @method \string requireParams()
	 * @method \Bitrix\MobileApp\Designer\EO_Config resetParams()
	 * @method \Bitrix\MobileApp\Designer\EO_Config unsetParams()
	 * @method \string fillParams()
	 * @method \Bitrix\Main\Type\DateTime getDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_Config setDateCreate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateCreate)
	 * @method bool hasDateCreate()
	 * @method bool isDateCreateFilled()
	 * @method bool isDateCreateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateCreate()
	 * @method \Bitrix\Main\Type\DateTime requireDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_Config resetDateCreate()
	 * @method \Bitrix\MobileApp\Designer\EO_Config unsetDateCreate()
	 * @method \Bitrix\Main\Type\DateTime fillDateCreate()
	 * @method \Bitrix\MobileApp\EO_App getApp()
	 * @method \Bitrix\MobileApp\EO_App remindActualApp()
	 * @method \Bitrix\MobileApp\EO_App requireApp()
	 * @method \Bitrix\MobileApp\Designer\EO_Config setApp(\Bitrix\MobileApp\EO_App $object)
	 * @method \Bitrix\MobileApp\Designer\EO_Config resetApp()
	 * @method \Bitrix\MobileApp\Designer\EO_Config unsetApp()
	 * @method bool hasApp()
	 * @method bool isAppFilled()
	 * @method bool isAppChanged()
	 * @method \Bitrix\MobileApp\EO_App fillApp()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\MobileApp\Designer\EO_Config set($fieldName, $value)
	 * @method \Bitrix\MobileApp\Designer\EO_Config reset($fieldName)
	 * @method \Bitrix\MobileApp\Designer\EO_Config unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\MobileApp\Designer\EO_Config wakeUp($data)
	 */
	class EO_Config {
		/* @var \Bitrix\MobileApp\Designer\ConfigTable */
		static public $dataClass = '\Bitrix\MobileApp\Designer\ConfigTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\MobileApp\Designer {
	/**
	 * EO_Config_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getAppCodeList()
	 * @method \string[] getPlatformList()
	 * @method \string[] getParamsList()
	 * @method \string[] fillParams()
	 * @method \Bitrix\Main\Type\DateTime[] getDateCreateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateCreate()
	 * @method \Bitrix\MobileApp\EO_App[] getAppList()
	 * @method \Bitrix\MobileApp\Designer\EO_Config_Collection getAppCollection()
	 * @method \Bitrix\MobileApp\EO_App_Collection fillApp()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\MobileApp\Designer\EO_Config $object)
	 * @method bool has(\Bitrix\MobileApp\Designer\EO_Config $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\MobileApp\Designer\EO_Config getByPrimary($primary)
	 * @method \Bitrix\MobileApp\Designer\EO_Config[] getAll()
	 * @method bool remove(\Bitrix\MobileApp\Designer\EO_Config $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\MobileApp\Designer\EO_Config_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\MobileApp\Designer\EO_Config current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Config_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\MobileApp\Designer\ConfigTable */
		static public $dataClass = '\Bitrix\MobileApp\Designer\ConfigTable';
	}
}
namespace Bitrix\MobileApp\Designer {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Config_Result exec()
	 * @method \Bitrix\MobileApp\Designer\EO_Config fetchObject()
	 * @method \Bitrix\MobileApp\Designer\EO_Config_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Config_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\MobileApp\Designer\EO_Config fetchObject()
	 * @method \Bitrix\MobileApp\Designer\EO_Config_Collection fetchCollection()
	 */
	class EO_Config_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\MobileApp\Designer\EO_Config createObject($setDefaultValues = true)
	 * @method \Bitrix\MobileApp\Designer\EO_Config_Collection createCollection()
	 * @method \Bitrix\MobileApp\Designer\EO_Config wakeUpObject($row)
	 * @method \Bitrix\MobileApp\Designer\EO_Config_Collection wakeUpCollection($rows)
	 */
	class EO_Config_Entity extends \Bitrix\Main\ORM\Entity {}
}