<?php

namespace App\Model;

use Nette\Database\UniqueConstraintViolationException;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Database\Context;
use Img\ImageStorage;

use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * Správce uživatelů redakčního systému.
 * @package App\Model
 */
class UserManager implements IAuthenticator
{
	/** Konstanty pro manipulaci s modelem. */
	const
		TABLE_NAME = 'users', COLUMN_ID = 'id', COLUMN_NAME = 'login', COLUMN_PASSWORD_HASH = 'password', COLUMN_ROLE = 'job_title';

	private $database;
	/**
	 * @var ImageStorage
	 */
	private $imageStorage;
	/**
	 * @var ArticleManager
	 */
	private $articleManager;

	public function __construct(Context $database, \Img\ImageStorage $imageStorage, ArticleManager $articleManager)
	{
		$this->imageStorage = $imageStorage;
		$this->database = $database;
		$this->articleManager = $articleManager;
	}




	/**
	 * Edituje sloupec
	 * @param $id
	 * @param $column - vyžaduje pole s nahrávanou hodnu, kde index pole zastupuje sloupec
	 */
	public function editColumn($id, $column)
	{

		$this->database->table(self::TABLE_NAME)->where('id = ?',$id)->update($column);
	}

	/**
	 * Smaže uživatele
	 * @param $id
	 */
	public function delete($id)
	{
		$this->database->table(self::TABLE_NAME)->where('id', $id)->delete();
	}

	/**
	 * Vrátí záznam uživatele na základě ID
	 * @param $id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getUser($id)
	{
		return $this->database->table(self::TABLE_NAME)->get($id);
	}

	/**
	 * Vrátí záznam uživatele na základě "firstName"
	 * @param $name
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getUserByName($name)
	{
		return $this->database->table(self::TABLE_NAME)->select('*')->where('firstName = ?', $name)->fetch();

	}

	/**
	 * Vrátí všechny uživatele
	 * @return static
	 */
	public function getUsers()
	{
		return $this->database->table(self::TABLE_NAME)->order(self::COLUMN_ID . ' DESC');

	}

	/**
	 * Přihlásí uživatele do systému.1
	 * @param array $credentials jméno a heslo uživatele
	 * @return Identity identitu přihlášeného uživatele pro další manipulaci
	 * @throws AuthenticationException Jestliže došlo k chybě při prihlašování, např. špatné heslo nebo uživatelské
	 *                                 jméno.
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials; // Extrahuje potřebné parametry.

		// Vykoná dotaz nad databází a vrátí první řádek výsledku nebo false, pokud uživatel neexistuje.
		$user = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

		// Ověření uživatele.
		if (!$user)
		{
			// Vyhodí výjimku, pokud uživatel neexituje.
			throw new AuthenticationException('Zadané uživatelské jméno neexistuje.', self::IDENTITY_NOT_FOUND);
		}
		elseif (!Passwords::verify($password, $user[self::COLUMN_PASSWORD_HASH]))
		{ // Ověří heslo.
			// Vyhodí výjimku, pokud je heslo špatně.
			throw new AuthenticationException('Zadané heslo není správně.', self::INVALID_CREDENTIAL);
		}
		elseif (Passwords::needsRehash($user[self::COLUMN_PASSWORD_HASH]))
		{ // Zjistí, jestli heslo potřebuje rehashovat.
			// Rehashuje heslo.
			$user->update(array(self::COLUMN_PASSWORD_HASH => Passwords::hash($password)));
		}

		// Příprava uživatelských dat.
		$userData = $user->toArray(); // Extrahuje uživatelská data.
		unset($userData[self::COLUMN_PASSWORD_HASH]); // Odstraní položku hesla z uživatelských dat (kvůli bezpečnosti).

		// Vrátí novou identitu přihlášeného uživatele.
		return new Identity($user[self::COLUMN_ID], $user[self::COLUMN_ROLE], $userData);
	}

	/**
	 * Registruje nového uživatele do systému.
	 * @param string $username uživatelské jméno
	 * @param string $password heslo
	 * @throws DuplicateNameException Jestliže uživatel s daným jménem již existuje.
	 */
	public function add($login, $password)
	{

		if($this->database->table(self::TABLE_NAME)->where('login = ?',$login)->fetch() != NULL)
			return false;
		else{
			// Pokusí se vložit nového uživatele do databáze.
			$this->database->table(self::TABLE_NAME)->insert(array(
				self::COLUMN_NAME => $login,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
				'created_at' => new \DateTime(),

			));
			return true;
		}

	}

	/**
	 * Změní heslo uživatele
	 * @param $vals - pole s ID uživatele a heslem
	 */
	public function passChange($vals)
	{


		$this->database->table(self::TABLE_NAME)->where('id = ?',$vals->id)->update([
			'password'=> Passwords::hash($vals->password),
		]);
	}

	/**
	 * Updatuje profil uživatele
	 * @param $vals
	 */
	public function profileUpdate($vals)
	{

		$row = $this->database->table(self::TABLE_NAME)->where('id', $vals->id)->fetch();

		$row->update($vals);
	}


	/**
	 * Upravuje Main Contact skupiny
	 * @param $vals
	 * @return bool|int|\Nette\Database\Table\ActiveRow
	 */
	public function addMainContact($vals)
	{
		if ($this->database->table(self::TABLE_NAME)->where('firstName', 'Main')->where('lastName', 'Contact')->fetchAll())
		{
			return $this->database->table(self::TABLE_NAME)->where('firstName', 'Main')->where('lastName', 'Contact')->update($vals);
		}
		else
		{
			return $this->database->table(self::TABLE_NAME)->insert($vals);
		}
	}

	/**
	 * Vrací Main Contact skupiny
	 * @return static
	 */
	public function getMainContact()
	{
		return $this->database->table(self::TABLE_NAME)->where('firstName', 'Main')->where('lastName', 'Contact');

	}


	/**
	 * Edituje profil uživatele
	 * @param $vals
	 */
	public function edit($vals)
	{

		if (isset($vals->avatar))
		{

			$folderUrl = "profile/avatars";
			// Find edited user
			$item = $this->database->table(self::TABLE_NAME)->where('id', $vals->id)->fetch();

			if ($item->avatar != $vals->avatar->name)
			{

				$this->imageStorage->delete($item->avatar, $folderUrl);
				$this->imageStorage->save($vals->avatar, $folderUrl);
				$vals['avatar'] = $vals->avatar->name;
				$this->profileUpdate($vals);

			}
			else
			{
				$this->imageStorage->save($vals->avatar, $folderUrl);
				$this->profileUpdate($vals);
			}

		}
		else
		{
			$this->database->table(self::TABLE_NAME)->where('id', $vals->id)->update($vals);
		}
	}

	/**
	 * Vrací jméno (firstName a lastName) uživatele podle ID
	 * @param $id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getName($id)
	{
		return $this->database->table(self::TABLE_NAME)->select('firstName', 'lastName')->where('id', $id)->fetch();
	}

}

