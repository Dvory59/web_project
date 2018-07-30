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
use Aws\KinesisAnalytics\KinesisAnalyticsClient;
use DateTime;
use Img\ImageStorage;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;


class NewsPresenter extends BaseUserPresenter
{
	const TABLE = 'posts';
	private $img;
	private $titleImg = false;

	/**
	 * @var PostFormFactory
	 */
	private $formFactory;
	/**
	 * @var ArticleManager
	 */
	private $articleManager;

	/** @var PostFormFactory @inject */
	public $postFormFactory;
	/**
	 * @var ImageStorage
	 */
	private $imageStorage;

	/**
	 * NewsPresenter constructor.
	 * @param PostFormFactory $formFactory
	 * @param ArticleManager $articleManager
	 * @param ImageStorage $imageStorage
	 */
	public function __construct(PostFormFactory $formFactory, ArticleManager $articleManager, ImageStorage $imageStorage)
	{

		$this->formFactory = $formFactory;
		$this->articleManager = $articleManager;
		$this->imageStorage = $imageStorage;
	}

	/**
	 * Vytváří komponentu formuláře pro editaci/tvorbu news
	 * @return Form
	 */
	protected function createComponentPostForm()
	{

		$form = $this->postFormFactory->createPostForm();

		$form->addCheckbox('draft', 'Publikovat');
		$form->addSubmit('img_delete', 'Image ')->onClick[] = [$this, 'deleteImage'];
		$form->addUpload('title_img', 'Profil')->setRequired(false)->addRule($form::IMAGE);
		$form->onSuccess[] = [$this, 'postformSucceeded'];
		return $form;

	}

	/** Smaže titulní obrázek, volá se z formuláře */
	function deleteImage($form, $vals)
	{
		$folderUrl = "posts/title";

		$this->imageStorage->delete($this->img, $folderUrl);
		$array['title_img'] = NULL;
		$this->articleManager->editColumn($vals->id,$array);
		$this->articleManager->changeDraft($vals->id);
		$this->redirect('list');

	}

	/** Uloží data po odeslání formuláře */
	function postformSucceeded($form, $vals)
	{

		$postId = $this->getParameter('id');
		$vals['created_at'] = new DateTime();

		$vals['author_id'] = $this->user->getId();


		if ($vals->title_img->name == null && $this->titleImg == false)
		{
			unset($vals['title_img']);
			if ($vals['draft'] == 1)
			{
				$vals['draft'] = 0;
				$this->flashMessage("Post can´t be published : IMG missing");
			}

		}

		if ($postId)
		{
			if ($vals->title_img->name == null)
			{
				unset($vals['title_img']);
			}
			$this->articleManager->editArticle($vals, $postId);
			$this->flashMessage('Post was edited');
		}
		else
		{

			$this->articleManager->saveArticle($vals);
			$this->flashMessage('Post was saved');
		}

		$this->redirect('list');
	}

	/** Funkce, která se volá před editací/vytvářením článku. Podle ID rozezná, jestli se jedná o editaci nebo vytváření  */
	public function actionPost($id)
	{
		$this->template->img = NULL;
		if ($id != NULL)
		{
			$post = $this->articleManager->getArticleByID($id);

			$this->template->img = $post->title_img;
			if ($post->title_img != NULL)
			{
				$this->titleImg = true;
			}

			if (!$post)
			{
				$this->error('Příspěvek nebyl nalezen');
			}

			$this->img = $post->title_img;
			$this['postForm']->setDefaults($post->toArray());

		}


	}

	/** Funkce pro vytvoření componenty datagridu */
	public function createComponentNewsGrid($name)
	{
		$grid = new DataGrid();
		$grid->setDataSource($this->database->table(self::TABLE)->select('*')->where('type = ?', 'news'));
		$grid->addFilterText('title','Title','title');
		$grid->addFilterText('description','Description','description');
		$grid->addGroupAction('Change draft status')->onSelect[] = [$this, 'draftChange'];
		$grid->addGroupAction('Choose post for slider')->onSelect[] = [$this, 'sliderStatus'];
		$grid->addGroupAction('Delete')->onSelect[] = [$this, 'handleDelete'];
		$grid->addColumnText('id', 'ID');
		$grid->addColumnText('title', 'Titulek', 'title');
		$grid->addColumnText('description', 'Description');
		$grid->addColumnText('draft', 'Draft status')->setReplacement([
			'1' => 'Published',
			'0' => 'Drafted'
		]);
		$grid->addColumnText('slider', 'Slider status')->setReplacement([
			'1' => 'Used as Slider',
			'0' => 'None'
		]);
		$grid->addColumnText('title_img', 'Title Image');
		$grid->addAction('delete', 'Delete', 'delete!');
		$grid->addAction('draftChange', 'Draft/Publish', 'draftChange!', ['id', 'title_img']);
		$grid->addAction('post', 'Edit', 'post');

		return $grid;

	}

	/** Funkce pro změnu statusu článku na umístění/odstranění ze slideru na úvodní straně */
	public function sliderStatus(array $id)
	{

		foreach ($id as $item)
		{
			if (!$this->articleManager->sliderStatusChange($item))
			{
				$this->flashMessage('Post with ID' . $item . 'is not published. Can´t be use as slider');
			}

		}
		$this->redirect('this');
	}

	/** Funkce pro změnu statusu jednoho článku */
	public function handleDraftChange($id, $title_img)
	{
		if ($this->articleManager->changeDraft($id))
		{
			$this->redirect('this');
		}
		else
		{
			$this->flashMessage("IMG Missing");
		}
	}

	/** Funkce pro změnu statusu více článků */
	public function draftChange(array $id)
	{
		foreach ($id as $item)
		{
			if (!$this->articleManager->changeDraft($item))
			{
				$this->flashMessage("IMG Missing at post with ID:" . $item);
			}
		}

		$this->redirect('this');
	}

	/** Funkce pro smazání článku */
	public function handleDelete($id)
	{

		$this->articleManager->deletePost($id);
		$this->redirect('list');

	}


}