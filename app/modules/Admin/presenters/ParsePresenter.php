<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\AdminModule\Presenters;


use App\Model\RvviParser;
use App\Model\ProjectManager;
use App\Model\VutParser;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;

class ParsePresenter extends BaseUserPresenter
{

	const
		TABLE = 'vut_projects';
	/**
	 * @var VutParser
	 */
	private $vutParser;
	/**
	 * @var RvviParser
	 */
	private $rvviParser;

	public function __construct(ProjectManager $vutManager, VutParser $vutParser, RvviParser $rvviParser)
	{

		$this->vutManager = $vutManager;
		$this->vutParser = $vutParser;
		$this->rvviParser = $rvviParser;
	}

	/** Vyvolá proces parsování RVVI výsledků */
	public function actionParseRVVIVysledky()
	{

		$this->rvviParser->parseVysledky();
		$this->redirect('default');
	}

	/** Vyvolá proces parsování RVVI projektů */
	public function actionParseRVVIProject()
	{

		$this->rvviParser->parseProject();
		$this->redirect('default');
	}

	/** Vyvolá proces parsování VUT */
	public function actionParseVUT()
	{
		$this->vutParser->ParsePublications();
		$this->redirect('default');

	}

	/** Vytváří komponentu formuláře pro editaci záznamů */
	public function createComponentProjectForm($name)
	{
		$form = new Form();
		$form->addHidden('id');
		//$form->addText('name_cz', '');
		$form->addText('name_en', '');
		$form->addText('authors', '');
		$form->addTextArea('description_original', '');
		$form->addText('detail_url', '');
		$form->addText('source','');
		$form->addText('year_end','');

		$form->addTextArea('text', '');
		$form->addSubmit('send', '');
		$form->onSuccess[] = [$this, 'projectFormSucceeded'];
		return $form;

	}

	/** Funkce zavolaná po úspěšném vyplnění a odeslání formuláře */
	public function projectFormSucceeded($form, $vals)
	{

		$this->vutManager->updateProject($vals->id, $vals);
		$this->redirect('default');
	}

	/** Funkce vytvoří datagrid pro seznam záznamů */
	public function createComponentVutGrid()
	{
		$grid = new DataGrid();
		$grid->setColumnsHideable();
		$grid->addGroupAction('Change status', [
			1 => 'New',
			2 => 'Published',
			3 => 'Draft',
			4 => 'Update',
		])->onSelect[] = [$this, 'statusChange'];
		$grid->addGroupAction('Delete')->onSelect[] = [$this,'handleDelete'];

		$grid->addFilterText('authors', 'Authors', 'authors');
		$grid->addFilterText('name_en', 'Name EN', 'name_en');
		//$grid->addFilterText('name_cz', 'Name CZ', 'name_cz');

		$grid->addFilterText('year_end', 'Year', 'year_end');
		$grid->setDataSource($this->database->table(self::TABLE)->select('*'));
		$grid->addColumnNumber('id', 'ID');
		$grid->addColumnText('name_en', 'Name EN');
		$grid->addColumnText('authors', 'Authors');
		$grid->addColumnText('year_start', 'Year Start')->setDefaultHide();

		$grid->addColumnText('detail_url', 'Detail')->setTemplateEscaping(FALSE)->setRenderer(function ($item) {
				if ($item->detail_url != NULL)
					return "<a href='$item->detail_url'>Detail</a>";
				else
					return "No link";
			});

		$grid->addColumnText('year_end', 'Year End');
		$grid->addColumnText('type', 'Type');
		$grid->addColumnText('source', 'Source');
		$grid->addColumnText('finance','Total finance')->setDefaultHide();

		$grid->addColumnText('status', 'Status')->setReplacement([
			'1' => 'New',
			'2' => 'Published',
			'3' => 'Draft',
			'4' => 'Update'
		]);
		$grid->addAction('edit', 'Edit', 'edit');
		$grid->setStrictSessionFilterValues(FALSE);


		$grid->addFilterSelect('type', 'Type:', [
			'' => 'All',
			'publications' => 'PB',
			'project' => 'PROJ',
			'vysledky' => 'RES'
		]);
		$grid->addFilterSelect('source', 'Source:', [
			'' => 'All',
			'VUT' => 'VUT',
			'RVVI' => 'RVVI',
		]);
		$grid->addFilterSelect('status', 'Status:', [
			'' => 'All',
			'1' => 'New',
			'2' => 'Published',
			'3' => 'Draft',
			'4' => 'Update',
		]);


		return $grid;
	}

	/** Funkce zavolána z datagridu, vloží data o záznamu do formuláře */
	public function actionEdit($id)
	{
		$project = $this->vutManager->getProjectByID($id);
		$this['projectForm']->setDefaults($project->toArray());

	}

	/** Funkce pro změnu statusu, volaná z datagridu */
	public function statusChange(array $id, $status)
	{

		foreach ($id as $idx => $item)
		{
			$array[$idx] = $item;
		}

		$this->vutManager->statusChange($array, $status);

		$this->redirect('this');

	}

	/** Funkce pro smazání zánamu volaná z datagridu */
	public function handleDelete($id)
	{

		$this->vutManager->deletePost($id);
		$this->redirect('default');

	}

}