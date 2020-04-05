<?
namespace Bitrix\Sale\Location\Comparator;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;

/**
 * Class TmpTable
 * Helps to store temporary locations data during locations map with external services.
 * @package Bitrix\Sale\Location\Comparator
 */
final class TmpTable
{
	protected $name = 'b_sale_loc_map_tmp';
	protected $connection = null;
	protected $serviceId = 0;

	/**
	 * TmpTable constructor.
	 * @param int $serviceId External service id.
	 * @param string $tableName
	 * @throws ArgumentNullException
	 */
	public function __construct($serviceId, $tableName = "")
	{
		if(intval($serviceId) <= 0)
			throw new ArgumentNullException('serviceId');

		if(strlen($tableName) > 0)
			$this->name = $tableName;

		$this->serviceId = intval($serviceId);
		$this->connection = \Bitrix\Main\Application::getConnection();
	}

	/**
	 * @param int $startId Start position.
	 * @return \Bitrix\Main\DB\Result
	 */
	public function getUnmappedLocations($startId = 0)
	{
		$query = "
			SELECT
				TMP.*
			FROM
				".$this->name." AS TMP
			WHERE
				TMP.LOCATION_ID IS NULL				
		";

		if(intval($startId) > 0)
			$query .= " AND TMP.ID > ".intval($startId);

		$query .= " ORDER BY ID ASC";
		return $this->connection->query($query);
	}

	/**
	 * @param int $locationId Internal location id.
	 * @param string $xmlId External location id.
	 * @throws ArgumentNullException
	 */
	public function markMapped($locationId, $xmlId)
	{
		if(intval($locationId) <= 0)
			throw new ArgumentNullException('locationId');

		if(strlen($xmlId) <= 0)
			throw new ArgumentNullException('xmlId');

		$sqlHelper = $this->connection->getSqlHelper();

		$this->connection->queryExecute("
			UPDATE
				".$this->name." 
			SET 
				LOCATION_ID=".intval($locationId)." 
			WHERE				
				XML_ID = '".$sqlHelper->forSql($xmlId)."'"
		);
	}

	/**
	 * If we have mapped locations mark this in tmp table
	 */
	public function markAllMapped()
	{
		set_time_limit(0);

		$this->connection->queryExecute("
			UPDATE
				".$this->name." AS TMP
			INNER JOIN
				b_sale_loc_ext AS E ON TMP.XML_ID = E.XML_ID AND E.SERVICE_ID = ".$this->serviceId."
			SET
				TMP.LOCATION_ID = E.LOCATION_ID
		");
	}

	/**
	 * @param array $data
	 * @return \Bitrix\Main\DB\Result
	 * @throws ArgumentNullException
	 * @throws ArgumentTypeException
	 */
	public function create(array $data)
	{
		if(empty($data))
			throw new ArgumentNullException('data');

		if(!is_array(current($data)))
			throw new ArgumentTypeException('current(data)', 'array');

		$sqlHelper = $this->connection->getSqlHelper();
		$cols = '';

		foreach(current($data) as $key => $val)
			$cols .= $sqlHelper->forSql($key)." VARCHAR(255) NULL,\n";

		return $this->connection->queryExecute('
			CREATE TABLE '.$this->name.' (
				ID INT NOT NULL AUTO_INCREMENT,
				XML_ID VARCHAR (100) NOT NULL,				
				'.$cols.'				
				LOCATION_ID INT NULL,			
				PRIMARY KEY (ID)
			)'
		);
	}

	/**
	 * Drops table
	 */
	public function drop()
	{
		$this->connection->queryExecute('DROP TABLE '.$this->name);
	}

	/**
	 * @return bool
	 */
	public function isExist()
	{
		return $this->connection->isTableExists($this->name);
	}

	/**
	 * @param array $data Data to save
	 * @return int Quantity of saved rows.
	 * @throws ArgumentNullException
	 * @throws ArgumentTypeException
	 */
	public function saveData(array $data)
	{
		if(empty($data))
			return 0;

		set_time_limit(0);

		$sqlHelper = $this->connection->getSqlHelper();
		$queryBegin = '';

		foreach(current($data) as $key => $val)
		{
			if(strlen($queryBegin) > 0)
				$queryBegin .= ', ';

			$queryBegin .= $sqlHelper->forSql($key);
		}

		$queryBegin .= ', XML_ID';
		$queryBegin = "INSERT INTO ".$this->name."(".$queryBegin.") VALUES ";
		$imported = 0;
		$i = 0;
		$values = '';
		$INSERT_BLOCK_SIZE = 100;

		foreach($data as $xmlId => $row)
		{
			if(strlen($values) > 0)
				$values .= ', ';

			$rowValues = '';

			foreach($row as $col)
			{
				if(strlen($rowValues) > 0)
					$rowValues .= ', ';

				$rowValues .= "'".$sqlHelper->forSql($col)."'";
			}

			$values .= "(".$rowValues.", '".$sqlHelper->forSql($xmlId)."')";

			if($i >= $INSERT_BLOCK_SIZE)
			{
				$this->connection->queryExecute($queryBegin.$values);
				$i = 0;
				$values = '';
			}

			$i++;
			$imported++;
		}

		if(strlen($values) > 0)
			$this->connection->queryExecute($queryBegin.$values);

		$this->connection->queryExecute("CREATE INDEX IX_BSDTMP_XML_ID ON ".$this->name." (XML_ID)");
		return $imported;
	}

	/**
	 * @return int Max row id.
	 */
	public function getMaxId()
	{
		$result = 0;
		$res = $this->connection->query("SELECT MAX(ID) AS MAX FROM ".$this->name." WHERE LOCATION_ID IS NULL");

		if($loc = $res->fetch())
			$result = $loc['MAX'];

		return $result;
	}
}