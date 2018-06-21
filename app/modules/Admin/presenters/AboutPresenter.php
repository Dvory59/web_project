<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\AdminModule\Presenters;


use App\Forms\PostFormFactory;

use App\Model\ArticleManager;
use App\Model\UserManager;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class AboutPresenter extends BaseUserPresenter
{

	private $ifExists = true;

	/**
	 * @var PostFormFactory
	 */
	private $postFormFactory;
	/**
	 * @var ArticleManager
	 */
	private $articleManager;


	public function __construct(PostFormFactory $postFormFactory, ArticleManager $articleManager)
	{

		$this->postFormFactory = $postFormFactory;
		$this->articleManager = $articleManager;
	}

	/** Vytvoří komponentu formuláře pro editaci */
	protected function createComponentPostForm()
	{

		$form = $this->postFormFactory->createPostForm();
		$form->addHidden('type');
		$form->addHidden('draft');
		$form->addHidden('title_img');
		$form->onSuccess[] = [$this, 'postformSucceeded'];
		return $form;

	}


	/**
	 * Vloží do fomuláře defaultní hodnoty článků
	 * @param $option - identifikuje požadovaný článek
	 */
	public function actionAbout($option)
	{

		if ($this->articleManager->getArticleByType($option)!=false)
		{

			$editArray = $this->articleManager->getArticleByType($option);

			$this['postForm']->setDefaults($editArray);
		}
		else
		{
			$this->ifExists = false;
			$this['postForm']->setDefaults([
				'type' => $option
			]);

		}
	}
	/** Funkce která edituje článek po odeslání formuláře */
	function postformSucceeded($form,$vals)
	{
		$vals['draft'] = 1;
		if($this->ifExists==false)
		{

			$this->articleManager->saveArticle($vals);
		}
		else
		{

			$this->articleManager->editArticle($vals,$vals->id);
		}

	}




}