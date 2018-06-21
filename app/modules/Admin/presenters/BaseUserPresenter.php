<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\AdminModule\Presenters;

use App\Model\UserManager;
use App\Model\ProjectManager;
use Nette\Database\Context;


class BaseUserPresenter extends BasePresenter
{
	/** @var Context @inject */
	public $database;

	/** @var UserManager @inject */
	public $userManager;

	/** @var ProjectManager @inject */
	public $vutManager;

	/** Předává do šablony aktuálně přihlášeného uživatele */
	public function beforeRender()
	{
		$this->template->user=$this->userManager->getUser($this->user->getId());

		$newProjects = $this->vutManager->getNewProjects();

		$this->template->newProjectsCount=count($newProjects);
	}

}