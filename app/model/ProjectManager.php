<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\Model;

use Nette\Database\ResultSet;
use Nette\Utils\ArrayHash;
use App\Model\UserManager;
use iMarc;

class ProjectManager extends BaseManager
{

	const TABLE_VUTPROJECT = 'vut_projects', TABLE_USERPROJECT = 'usersproject', ID = "011209687701517789943:dsevteq8sja", API = "AIzaSyA6WFlvvfDQF6MUYpGusjRhelIm4IwdzYk";

	/**
	 * Vrací projekt podle zadaného ID
	 * @param $id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getProjectByID($id)
	{
		return $this->database->table(self::TABLE_VUTPROJECT)->get($id);
	}

	/**
	 *    Vrátí všechny publikované záznamy
	 */
	public function getPublicAll()
	{
		return $this->database->table('vut_projects')->where('status', 2)->order('year_end DESC');
	}

	/**Vrátí všechny publikované záznamy z VUT*/
	public function getPublicPublications()
	{
		return $this->database->table('vut_projects')->where('status', 2)->where('type = ?', 'publications')->order('year_end DESC');
	}

	/**Vrátí všechny publikované záznamy z rvvi(projekty)*/
	public function getPublicProjects()
	{
		return $this->database->table('vut_projects')->where('status', 2)->where('type = ?', 'project')->order('year_end DESC');
	}

	/**Vrátí všechny publikované záznamy z rvvi(výsledky)*/
	public function getPublicResults()
	{
		return $this->database->table('vut_projects')->where('status', 2)->where('type = ?', 'vysledky')->order('year_end DESC');
	}


	/**
	 * Updatuje "id" s "vals"
	 * @param $index
	 * @param $vals
	 */
	public function updateProject($index, $vals)
	{

		$this->database->table('vut_projects')->where('id', $index)->update($vals);

	}

	/**
	 * Proces ukládání projektů
	 * @param $array
	 * @param $googleLimit
	 * @return mixed
	 */
	public function checkAndUpdate($array, $googleLimit)
	{
		$break = false;
		$db = $this->database->table('vut_projects');
		$isSet = false;
		if ($db->count() > 0)
		{
			foreach ($db as $zaznam)
			{
				if ($zaznam->detail_url == NULL)
				{


					if (!$break)
					{
						$search = new iMarc\GoogleCustomSearch(self::ID, self::API);
						if ($search->search($zaznam->name_en) != false)
						{
							$x = $search->search($zaznam->name_en);

							if ($x instanceof \stdClass)
							{
								$url = $x->results[0]->link;
								$detail['detail_url'] = $url;
								$this->updateProject($zaznam->id, $detail);
							}
							//$result = $x->search($array->name_en);
						}
						else
						{
							$break = true;
							set_time_limit(200);
						}
					}

				}

				if ($array->source == 'VUT' && str_replace(" ", "", $array->name_cz) == str_replace(" ", "", $zaznam->name_cz) || $array->source == 'RVVI' && str_replace(" ", "", $array->name_en) == str_replace(" ", "", $zaznam->name_en))
				{

					$isSet = true;

				}

				if ($isSet)
				{


					if ($zaznam->status == 4)
					{

						unset($array['detail_url']);
						$array['status'] = 1;
						$this->updateProject($zaznam->id, $array);
						return $googleLimit;
					}
					//$this->insertUserProject($user, $array->name_cz);

				}
			}
			if (!$isSet)
			{
				$array['status'] = 1;
				$this->insertProject($array);
				//$this->insertUserProject($user, $array->name_cz);
				return $googleLimit;
			}
		}
		else
		{
			$array['status'] = 1;
			$this->insertProject($array);
			return $googleLimit;
		}


	}


	/**
	 *  Vloží project do tabulky
	 * @param $val
	 * @return bool|int|\Nette\Database\Table\ActiveRow
	 */
	public function insertProject($vals)
	{
		return $this->database->table(self::TABLE_VUTPROJECT)->insert($vals);
	}

	/**
	 *   Změní "status" projektům
	 * @param $array of Projects
	 * @param $status
	 */
	public function statusChange($projects, $status)
	{

		$this->database->query('UPDATE vut_projects SET status = ? WHERE id IN(?) ', $status, $projects);
	}

	/**ss
	 * Vrátí projekty se statusem "new"
	 * @return projects (new)
	 */
	public function getNewProjects()
	{
		return $this->database->table('vut_projects')->where('status', 1);

	}

	/**
	 * Hledá "string" v "column"
	 * @param $column (where)
	 * @param $string (what)
	 * @return static
	 */
	public function projectSearch($column, $string)
	{
		if ($column == "*")
		{

			$db = $this->database->table('vut_projects')->where('name_cz LIKE ? OR name_en LIKE ? OR description_en LIKE ? OR description_original LIKE ? OR authors LIKE ? OR citation LIKE ? OR text LIKE ? ', '%' . $string . '%', '%' . $string . '%', '%' . $string . '%', '%' . $string . '%', '%' . $string . '%', '%' . $string . '%', '%' . $string . '%')->where('status', 2)->order('year_end DESC');

		}
		else
		{

			$db = $this->database->table('vut_projects')->where($column . ' LIKE ? ', '%' . $string . '%')->where('status', 2)->order('year_end DESC');

		}

		return $db;

	}

	/**
	 * Smaže project podle ID
	 * @param $id
	 */
	public function deletePost($id)
	{
		$this->database->query('DELETE FROM vut_projects WHERE id IN (?) ', $id);

		//$this->database->table('posts')->where('id', $id)->delete();
	}

}