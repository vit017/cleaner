<?

namespace Bitrix\Seo\Retargeting;

use Bitrix\Main\Loader;
use Bitrix\Seo\Service as SeoService;
use Bitrix\Main\Web\Uri;

class AuthAdapter
{
	protected $type;
	/* @var \CSocServOAuthTransport|\CFacebookInterface */
	protected $transport;
	protected $requestCodeParamName;
	protected $data;

	public function __construct($type)
	{
		$this->type = $type;
	}

	public static function create($type)
	{
		if (!Loader::includeModule('socialservices'))
		{
			throw new \Bitrix\Main\SystemException('Module "socialservices" not installed.');
		}
		$instance = new static($type);

		return $instance;
	}

	public function getAuthUrl()
	{
		if (!SeoService::isRegistered())
		{
			SeoService::register();
		}

		$authorizeUrl = SeoService::getAuthorizeLink();
		$authorizeData = SeoService::getAuthorizeData(Service::getEngineCode($this->type));
		$uri = new Uri($authorizeUrl);
		$uri->addParams($authorizeData);
		return $uri->getLocator();
	}

	protected function getAuthData($isUseCache = true)
	{
		if (!$isUseCache || !$this->data || count($this->data) == 0)
		{
			$this->data = SeoService::getAuth(Service::getEngineCode($this->type));
		}

		return $this->data;
	}

	public function removeAuth()
	{
		$this->data = array();

		if ($existedAuthData = $this->getAuthData(false))
		{
			SeoService::clearAuth(Service::getEngineCode($this->type));
		}
	}

	public function getType()
	{
		return $this->type;
	}

	public function getToken()
	{
		$data = $this->getAuthData();
		return $data ? $data['access_token'] : null;
	}

	public function hasAuth()
	{
		return strlen($this->getToken()) > 0;
	}
}