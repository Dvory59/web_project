<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\FrontModule\Presenters;


use App\Model\ArticleManager;
use App\Model\ProjectManager;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;
use IPub\VisualPaginator\Components as VisualPaginator;


class ResearchPresenter extends BasePresenter
{
	/**
	 * @var ProjectManager
	 */
	private $vutManager;

	private $projects;
	/**
	 * @var ArticleManager
	 */


	/**
	 * ResearchPresenter constructor.
	 * @param ProjectManager $vutManager
	 * @param ArticleManager $articleManager
	 */
	public function __construct(ProjectManager $vutManager)
	{
		$this->vutManager = $vutManager;

	}

	/** Vytváří komponentu paginator na stránkování */
	protected function createComponentVisualPaginator()
	{
		$control = new VisualPaginator\Control;

		$control->setTemplateFile(__DIR__ . "/../templates/Paginator/paginator.latte");
		$control->disableAjax();

		return $control;
	}

	/** Získá všechny publikované záznamy a vloží je do proměnné $project */
	public function actionList()
	{
		$this->projects = $this->vutManager->getPublicAll();
	}

	/** Vypíše list záznamů a volá při tom paginator na stránkování*/
	public function renderList(int $page = 0)
	{


		$visualPaginator = $this['visualPaginator'];
		$paginator = $visualPaginator->getPaginator();
		$paginator->itemsPerPage = 10;
		$paginator->itemCount = $this->projects->count();

		$this->projects->limit($paginator->itemsPerPage, $paginator->offset);

		$this->template->allProjects = $this->projects;


	}

	/** Vytváří komponentu formuláře pro vyhledávání */
	protected function createComponentSearchBar($name)
	{
		$form = new \Nette\Application\UI\Form();
		$form->addSelect('select', 'Select', [
			'all' => 'All',
			'name' => 'Name',
			'authors' => 'Authors',
			'abstract' => 'Abstract',
		]);
		$form->addText('searchKey', 'Search Key');
		$form->addSubmit('search', 'Search');
		$form->onSuccess[] = [$this, 'handleSearchBar'];
		return $form;
	}

	/** Funkce pro vyhledávání zavolaná po odeslání vyhledávacího formuláře */
	public function handleSearchBar($form, $vals)
	{

		if (!$vals->searchKey)
		{
			$this->redirect('list');
		}
		else
		{
			switch ($vals->select)
			{
				case 'name':
					$searchColumn = 'name_en';
					break;
				case 'authors':
					$searchColumn = 'authors';
					break;
				case 'abstract':
					$searchColumn = 'description_original';
					break;
				default:
					$searchColumn = '*';
					break;
			}

			$this->projects = $this->vutManager->projectSearch($searchColumn, $vals->searchKey);

		}
	}

	/** Funkce pro zobrazení odpovídající konkrétnímu uživateli */
	public function actionUserSearch($firstName,$lastName)
	{
		$name = str_replace(' ','',$firstName).' '.str_replace(' ','',$lastName);
		$this->projects = $this->vutManager->projectSearch('authors', $name);

		$this->setView('list');

	}

}