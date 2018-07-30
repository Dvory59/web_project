<?php

namespace App\Model;



use Nette\Database\Context;



abstract class BaseManager
{


	/** @var Context Instance třídy pro práci s databází. */
	public $database;

	/**
	 * Konstruktor s injektovanou třídou pro práci s databází.
	 * @param Context $database automaticky injektovaná třída pro práci s databází
	 */
	public function __construct(Context $database)
	{
		$this->database = $database;
	}
}