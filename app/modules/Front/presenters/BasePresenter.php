<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\FrontModule\Presenters;


use App\Model\ArticleManager;
use App\Model\UserManager;
use Nette\Application\UI\Presenter;
use Nette\Security\User;

class BasePresenter extends Presenter
{
	/** @var User @inject */
	public $user;

	/** @var UserManager @inject*/
	public $userManager;

	/** @var ArticleManager @inject */
	public $articleManager;

	/** Vkládá do hlavní šablony Main Contact skupiny */
	public function beforeRender()
	{
		$this->template->user=$this->user;
		$this->template->mainContact = $this->userManager->getMainContact('Main','Contact')->fetchAll();
		$array = $this->articleManager->getArticleByType('about');
		$this->template->aboutFooter = $array;


	}
}