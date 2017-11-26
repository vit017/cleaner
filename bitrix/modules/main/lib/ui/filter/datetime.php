<?

namespace Bitrix\Main\UI\Filter;


use Bitrix\Main\Type as MainType;


class DateTime
{
	/** @var MainType\Date */
	protected static $date;

	/** @var MainType\DateTime */
	protected static $dateTime;

	/** @var ?number */
	protected static $timestamp;


	/**
	 * DateTime constructor.
	 * @param string $timestamp
	 */
	public function __construct($timestamp = "")
	{
		static::$timestamp = $timestamp;

		if (empty(static::$timestamp))
		{
			static::$date = new MainType\Date();
			static::$timestamp = static::$date->getTimestamp();
		}

		static::$dateTime = MainType\DateTime::createFromTimestamp(static::$timestamp);
	}


	/**
	 * Gets month from date
	 * @return string
	 */
	public function month()
	{
		return static::$dateTime->format("n");
	}


	/**
	 * Gets year
	 * @return string
	 */
	public function year()
	{
		return static::$dateTime->format("Y");
	}


	/**
	 * Gets quarter number
	 * @return int
	 */
	public function quarter()
	{
		return Quarter::get(static::$dateTime);
	}


	/**
	 * Gets quarter start datetime
	 * @return string
	 */
	public function quarterStart()
	{
		$startDate = Quarter::getStartDate($this->quarter(), $this->year());
		return MainType\DateTime::createFromTimestamp(MakeTimeStamp($startDate))->toString();
	}


	/**
	 * Gets quarter end dateTime
	 * @return string
	 */
	public function quarterEnd()
	{
		$endDate = Quarter::getEndDate($this->quarter(), $this->year());
		return MainType\DateTime::createFromTimestamp(MakeTimeStamp($endDate))->add("1 days - 1 second")->toString();
	}


	/**
	 * Gets datetime string with offset.
	 * @param string $offset
	 * @return string
	 */
	public function offset($offset)
	{
		$date = MainType\DateTime::createFromTimestamp(static::$timestamp);
		return $date->add($offset)->toString();
	}


	/**
	 * Gets datetime string
	 * @return string
	 */
	public function toString()
	{
		return static::$dateTime->toString();
	}


	/**
	 * Gets timestamp
	 * @return number
	 */
	public function getTimestamp()
	{
		return static::$timestamp;
	}
}