<?php

namespace App\FrontModule\Presenters;

use App\Model\ArticleManager;
use App\Model\UserManager;
use App\Model\ProjectManager;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Security\User;

class DefaultPresenter extends BasePresenter
{
	public $articleManager;
	/**
	 * @var UserManager
	 */
	public $userManager;

	private $articles = ['about', 'smart_city', 'smart_grid', 'industry'];
	/**
	 * @var ProjectManager
	 */
	private $vutManager;


	public function __construct(User $user, ArticleManager $articleManager, UserManager $userManager, ProjectManager $vutManager)
	{
		parent::__construct($user);
		$this->articleManager = $articleManager;
		$this->userManager = $userManager;
		$this->vutManager = $vutManager;
	}




	public function renderDefault()
	{
		$this->template->publications = $this->vutManager->getPublicPublications()->count();
		$this->template->projects = $this->vutManager->getPublicProjects()->count();
		$this->template->results = $this->vutManager->getPublicResults()->count();
		$this->template->membersCount = $this->userManager->getUsers()->where('avatar IS NOT NULL')->count();

		$array = $this->articleManager->getArticleByType($this->articles);
		$this->template->about = $array['about'];
		$this->template->sm_city = $array['smart_city'];
		$this->template->sm_grid = $array['smart_grid'];
		$this->template->industry = $array['industry'];
		$this->template->articles = $this->articleManager->getDraftedNews()->limit(2)->fetchAll();
		$this->template->team = $this->userManager->getUsers()->where('avatar IS NOT NULL');
		$this->template->sliderArticles = $this->articleManager->getDraftedNews()->where('slider',1);

	}


}