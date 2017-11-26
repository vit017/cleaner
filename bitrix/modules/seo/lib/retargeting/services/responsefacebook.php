<?

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Error;
use \Bitrix\Main\Web\Json;
use \Bitrix\Seo\Retargeting\Response;


class ResponseFacebook extends Response
{
	const TYPE_CODE = 'facebook';

	public function parse($data)
	{
		$parsed = Json::decode($data);
		if ($parsed['error'])
		{
			$this->addError(new Error($parsed['error']['message'], $parsed['error']['code']));
		}

		if ($parsed['data'])
		{
			$this->setData($parsed['data']);
		}
		else if(!isset($parsed['error']))
		{
			$this->setData($parsed);
		}
	}
}