<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\FrontModule\Presenters;


use App\Model\UserManager;
use Nette\Application\UI\Presenter;

class TeamPresenter extends BasePresenter
{

	/**
	 * @var UserManager
	 */
	public $userManager;



	public function __construct(UserManager $userManager)
	{
		$this->userManager = $userManager;
	}

	/** Získá uživatele s uloženým avatarem a vloží do šablony */
	public function renderTeam()
	{
		$this->template->users = $this->userManager->getUsers()->where('avatar != ?','NULL');

	}

	/** Vykreslí detail profilu uživatele */
	public function renderProfile($id)
	{
		return $this->template->profile = $this->userManager->getUser($id);
	}

}