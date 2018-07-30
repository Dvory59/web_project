<?php

namespace Img;


use Nette\Utils\Finder;
use Nette\Utils\Image;

class ImageStorage
{
	private $imagesDir;


	public function __construct($dir)
	{
		$this->imagesDir = $dir;
	}


	/**
	 * Smaže soubor (obrázek) ze složky
	 * @param $name - jmeno souboru
	 * @param $url - umístění souboru
	 */
	public function delete($name, $url)
	{
		foreach (Finder::findFiles('*.jpeg', '*.JPEG', '*.PNG', '*.png', '*.GIF', '*.gif', '*.jpg', '*.JPG')->from($this->imagesDir . "/images/" . $url . "/") as $file)
		{
			if ($file->getFilename() == $name)
			{
				unlink($this->imagesDir . "/images/" . $url . "/" . $name);
			}
		}
	}


	/**
	 *  Uloží soubor (obrázek) do složky
	 * @param $name - jmeno souboru
	 * @param $url - umístění souboru
	 * @return string
	 */
	public function save($name, $url)
	{

		$path = $this->imagesDir . "/images/" . $url . "/" . $name->getName();
		$name->move($path);
		$image = Image::fromFile($path);
		$image->save($path);
		return $path;

	}
}