<?php

namespace App\Model;

use Img\ImageStorage;
use Nette\Database\Context;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;

/**
 * Třída poskytuje metody pro správu článků v redakčním systému.
 * @package App\CoreModule\Model
 */
class ArticleManager extends BaseManager
{
	/** Konstanty pro manipulaci s modelem. */
	const
		TABLE_NAME = 'posts', COLUMN_ID = 'id', COLUMN_URL = 'url', COLUMN_DESTINATION = 'destination';

	/**
	 * @var ImageStorage
	 */
	private $imageStorage;

	public function __construct(Context $database, ImageStorage $imageStorage)
	{
		$this->imageStorage = $imageStorage;
		parent::__construct($database);
	}

	/**
	 * Vrátí seznam všech článků v databázi.
	 * @return Selection seznam článků
	 */
	public function getArticles()
	{
		return $this->database->table(self::TABLE_NAME)->order(self::COLUMN_ID . ' DESC');
	}

	/**
	 * @return seznam publikovaných NEWS
	 */
	public function getDraftedNews()
	{
		return $this->database->table(self::TABLE_NAME)->where('draft', 1)->where('type = ?','news')->order(self::COLUMN_ID . ' DESC');

	}

	/**
	 * @return 5 náhodných publikovaných news
	 */
	public function getArticlesSideBarList()
	{
		$a = $this->database->table(self::TABLE_NAME)->max('id');
		$counter = 0;
		$listArray = false;
		while ($counter != 5)
		{
			$x = rand(1, $a);
			if ($this->database->table(self::TABLE_NAME)->where('id', $x)->where('draft', 1)->where('type', 'news')->fetch())
			{
				$listArray[$counter] = $this->database->table(self::TABLE_NAME)->where('id', $x)->fetch();
				$counter++;
			}

		}
		return $listArray;
	}

	/**
	 * Vrátí článek z databáze podle jeho TYPE.
	 * @param string $type TYPE článku
	 * @return array článků
	 */
	public function getArticleByType($typeArray)
	{

		if (is_array($typeArray))
		{
			foreach ($typeArray as $type)
			{

				$array[$type] = $this->database->table(self::TABLE_NAME)->where('type', $type)->fetch();

			}

		}
		else
		{
			$array = $this->database->table(self::TABLE_NAME)->where('type', $typeArray)->fetch();
		}

		return $array;
	}

	/**
	 * @return článek na základě ID
	 * @param $id
	 *
	 */
	public function getArticleByID($id)
	{
		return $this->database->table(self::TABLE_NAME)->where('id', $id)->fetch();
	}

	/**
	 * Vyhledává záznamy podle parametru
	 * @return article/articles
	 * @param $searchKey
	 *
	 */
	public function articleSearch($searchKey)
	{
		return $this->database->table('posts')->where('title LIKE ? OR text LIKE ? OR description LIKE ?', '%' . $searchKey . '%', '%' . $searchKey . '%', '%' . $searchKey . '%');
	}

	/**
	 * Uloží článek do systému.
	 * @param array|ArrayHash $article článek
	 */
	public function saveArticle($vals)
	{
		if (isset($vals->title_img) && is_file($vals->title_img))
		{

			$folderUrl = "posts/title";


			$this->imageStorage->save($vals->title_img, $folderUrl);
			$vals['title_img'] = $vals->title_img->name;
			$this->database->table(self::TABLE_NAME)->insert($vals);

		}
		else
		{

			$this->database->table(self::TABLE_NAME)->insert($vals);
		}
	}

	/**
	 * Edituje článek $id hodnotami $vals
	 * @param $vals
	 * @param $id
	 */
	public function editArticle($vals, $id)
	{

		// Pokud je ve formuláři poslán title image => uloží obrázek do souboru
		// Jinak updatuje bez ukládání obrázků
		if (isset($vals->title_img))
		{
			if (is_file($vals->title_img))
			{

				$folderUrl = "posts/title";
				// Najde editovaný článek
				$post = $this->database->table(self::TABLE_NAME)->where('id', $id)->fetch();

				// Pokud editovaný článek nemá nahraný stejný článek, jako je nahráván => smaže dosavadní title img z adresáře a nahradí nahrávaným.
				// Jinak pouze uloží nový obrázek
				if ($post->title_img != $vals->title_img->name)
				{

					$this->imageStorage->delete($post->title_img, $folderUrl);
					$this->imageStorage->save($vals->title_img, $folderUrl);
					$vals['title_img'] = $vals->title_img->name;

					$this->database->table(self::TABLE_NAME)->where('id', $id)->update($vals);

				}
				else
				{
					$this->imageStorage->save($vals->title_img, $folderUrl);

					$this->database->table(self::TABLE_NAME)->where('id', $id)->update($vals);
				}

			}
			else
			{
				$this->database->table(self::TABLE_NAME)->where('id', $id)->update($vals);
			}
		}
		else
		{

			$this->database->table(self::TABLE_NAME)->where('id', $id)->update($vals);
		}


	}

	/**
	 * Updatuje jeden sloupec v jednom záznamu
	 * @param $id
	 * @param $column - vyžaduje pole s nahrávanou hodnu, kde index pole zastupuje sloupec
	 */
	public function editColumn($id, $column)
	{

		$this->database->table(self::TABLE_NAME)->where('id = ?',$id)->update($column);
	}

	/**
	 * Smaže záznam podle $id
	 * @param $id
	 */
	public function deletePost($id)
	{
		$this->database->query('DELETE FROM posts WHERE id IN (?) ', $id);

		//$this->database->table('posts')->where('id', $id)->delete();
	}


	/**
	 * Změní Draft status
	 * @param $id záznamu
	 * @return bool
	 */
	public function changeDraft($id)
	{
		if (is_array($id))
		{

		}
		else
		{

			$post = $this->database->table(self::TABLE_NAME)->where('id', $id)->fetch();
			if ($post->title_img != NULL)
			{

				if ($post->draft == 1)
				{
					$post->update(['draft' => 0]);
				}
				else
				{
					$post->update(['draft' => 1]);
				}
				return true;
			}
			else
			{
				$post->update(['draft' => 0]);
				return false;
			}

		}
	}

	/** Změní slider status u záznamu $id
	 * @param $id
	 * @return bool
	 */
	public function sliderStatusChange($id)
	{
		$post = $this->database->table(self::TABLE_NAME)->where('id', $id)->fetch();
		if ($post->draft != 0)
		{
			if ($post->slider == 1)
			{
				$post->update(['slider' => 0]);
			}
			else
			{
				$post->update(['slider' => 1]);
			}
			return true;
		}
		else
		{
			return false;
		}

	}


}