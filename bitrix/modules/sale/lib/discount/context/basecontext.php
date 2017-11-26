<?php

namespace Bitrix\Sale\Discount\Context;

abstract class BaseContext
{
	const GUEST_USER_ID = 0;

	/** @var int */
	protected $userId;
	/** @var array */
	protected $userGroups = array();

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @return array
	 */
	public function getUserGroups()
	{
		return $this->userGroups;
	}
}