<?php

namespace Bitrix\Sale\Discount\Context;

use Bitrix\Sale\Discount\RuntimeCache;

class Fuser extends BaseContext
{
	protected $fuserId;

	/**
	 * FUser constructor.
	 */
	public function __construct($fuserId)
	{
		$this->fuserId = $fuserId;
		$this->userId = RuntimeCache\FuserCache::getInstance()->getUserIdById($this->fuserId);
		$this->userGroups = \CUser::getUserGroup($this->userId);
	}
}