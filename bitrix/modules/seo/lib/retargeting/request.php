<?

namespace Bitrix\Seo\Retargeting;

use \Bitrix\Main\Error;
use \Bitrix\Seo\Retargeting\Internals\ServiceLogTable;

abstract class Request
{
	const TYPE_CODE = '';

	/** @var AuthAdapter */
	protected $adapter;

	/** @var AdsHttpClient */
	protected $client;

	protected $response;
	protected $type;

	protected $endpoint;

	public function __construct()
	{
		$this->type = static::TYPE_CODE;
		$this->adapter = AuthAdapter::create(static::TYPE_CODE);

		$options = array(
			'socketTimeout' => 5
		);
		$this->client = new AdsHttpClient($options);
	}

	/**
	 * @return AuthAdapter
	 */
	public function getAuthAdapter()
	{
		return $this->adapter;
	}

	public function setAuthAdapter(AuthAdapter $adapter)
	{
		$this->adapter = $adapter;
		return $this;
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function getClient()
	{
		return $this->client;
	}

	public function setClient(AdsHttpClient $client)
	{
		$this->client = $client;
		return $this;
	}

	public function getEndpoint()
	{
		return $this->endpoint;
	}

	/**
	 * @param $type
	 * @return static
	 */
	public static function create($type)
	{
		return Factory::create(get_called_class(), $type);
	}

	/**
	 * @param array $params
	 * @return Response
	 */
	public function send(array $params = array())
	{
		if (!$this->adapter)
		{
			$this->adapter = AuthAdapter::create($this->type);
		}

		if (!$this->client)
		{
			$options = array(
				'socketTimeout' => 5
			);
			$this->client = new AdsHttpClient($options);
		}

		$data = $this->query($params);
		$response = Response::create($this->type);
		$response->setRequest($this);
		$response->setResponseText($data);
		try
		{
			$response->parse($data);
		}
		catch (\Exception $exception)
		{
			$response->addError(new Error($exception->getMessage(), $exception->getCode()));
		}

		if ($response->getErrorCollection()->count() > 0)
		{
			$errors = $response->getErrors();
			foreach ($errors as $error)
			{
				if (!$error->getMessage())
				{
					continue;
				}

				ServiceLogTable::add(array(
					'GROUP_ID' => 'retargeting',
					'TYPE' => static::TYPE_CODE,
					'CODE' => $error->getCode(),
					'MESSAGE' => $error->getMessage()
				));
			}
		}


		return $response;
	}

	abstract public function query(array $params = array());
}