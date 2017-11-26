<?

namespace Bitrix\Seo\Retargeting;

class BaseApiObject
{
	const TYPE_CODE = '';

	/** @var Request $request */
	protected $request;

	protected static $listRowMap = array();

	public static function normalizeListRow(array $row)
	{
		$return = array();
		foreach(static::$listRowMap as $key => $value)
		{
			if (is_array($value))
			{
				$return[$key] = $value;
			}
			else
			{
				$return[$key] = $row[$value];
			}
		}

		return $return;
	}

	public function __construct()
	{
		$this->request = Request::create(static::TYPE_CODE);
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	public function setRequest(Request $request)
	{
		return $this->request = $request;
	}


	/**
	 * @param $type
	 * @param null $parameters
	 * @return static
	 */
	public static function create($type, $parameters = null)
	{
		return Factory::create(get_called_class(), $type, $parameters);
	}
}