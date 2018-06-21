<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\AdminModule\Presenters;
use App\Model\UserManager;
use Nette\Application\UI\Presenter;
use Nette\Security\Identity;
use Nette\Security\User;


class BasePresenter extends Presenter
{



	/**
	 * @var UserManager
	 */
	private $userManager;
	private $user;
	/**
	 * @param User $user
	 * @param UserManager $userManager
	 * @internal param Identity $
	 */


	public function __construct(UserManager $userManager,User $user)
	{

		$this->user=$user;
		$this->userManager = $userManager;

	}

	/** Funkce která se vyvolá při zavolání pokaždé když se uživatel pohybuje v administrativní sekci a kontroluje jeho přihlášení pomocí funkce isAllowed() */
	function startup()
	{
		parent::startup();

		if (!$this->isAllowed()) {
			$this->flashMessage('Pro pristup do adminu se musis prssihlasit');
			$this->redirect('Sign:in');
		}

	}
	/** Funkce pro ověření přihlášení */
	protected function isAllowed()
	{

		return $this->getUser()->isLoggedIn();

		//getUser($this->user->getId());
	}


}