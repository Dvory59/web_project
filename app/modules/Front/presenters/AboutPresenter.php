<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\FrontModule\Presenters;


use App\Model\ArticleManager;
use App\Model\BaseManager;
use Nette\Application\UI\Presenter;
use App\Model\UserManager;

class AboutPresenter extends BasePresenter
{

	/** @var ArticleManager @inject */
	public $articleManager;

	//Definuje všechny typy základních článků
	private $articles = ['about', 'smart_city', 'smart_grid', 'industry'];

	/** Vykreslení a vložení základních článků do šablony */
	public function renderAbout()
	{
		$array = $this->articleManager->getArticleByType($this->articles);
		$this->template->about = $array['about'];
		$this->template->sm_city = $array['smart_city'];
		$this->template->sm_grid = $array['smart_grid'];
		$this->template->industry = $array['industry'];
	}

	/** Vykreslení jednotlivých článků */
	public function renderSubject($subject)
	{
		$this->template->article = $this->articleManager->getArticleByType($subject);
	}
}