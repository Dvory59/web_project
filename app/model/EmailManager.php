<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\Model;


class EmailManager extends BaseManager
{

	const TABLE = "emails";


	/**
	 * Uloží Email do databáze
	 * @param $vals
	 * @return bool|int|\Nette\Database\Table\ActiveRow
	 */
	public function saveEmail($vals)
	{
		return $this->database->table(self::TABLE)->insert($vals);
	}

}