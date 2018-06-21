<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\AdminModule\Presenters;


use App\Forms\UserForms;
use App\Model\ScholarParse;
use App\Model\ScopusParser;
use App\Model\VutParser;
use Img\ImageStorage;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use ondrs\UploadManager\Upload;
use Tracy\Debugger;
use Ublaboo\DataGrid\DataGrid;


class UserPresenter extends BaseUserPresenter
{
	const
		TABLE = 'users';
	private $img;
	/**@var \ondrs\UploadManager\Upload @inject */
	public $upload;

	/**
	 * @var UserForms
	 */
	private $userForms;
	/**
	 * @var VutParser
	 */
	private $vutParser;

	/**
	 * @var ImageStorage
	 */
	private $imageStorage;


	public function __construct(UserForms $userForms, VutParser $vutParser, ImageStorage $imageStorage)
	{
		$this->userForms = $userForms;
		$this->vutParser = $vutParser;
		$this->imageStorage = $imageStorage;
	}

	/**
	 * Vykreslení šablony list
	 */
	public function renderList()
	{
		$this->template->users = $this->userManager->getUsers();
	}

	/**
	 * Vytvoří komponentu formuláře pro regitraci
	 * @return Form
	 */
	protected function createComponentRegisterForm()
	{

		$form = $this->userForms->createRegisterForm();

		$form->onSuccess[] = [$this, 'formRegisterSucceeded'];
		return $form;
	}

	/**
	 * Funkce, která se volá po úspěšném odeslání formuláře registrace
	 * @param $form
	 * @param $vals
	 */
	function formRegisterSucceeded($form, $vals)
	{

		if (!$this->userManager->add($vals->login, $vals->password))
		{
			$this->flashMessage("Uživatel již existuje");
		}
		else
		{
			$this->redirect('list');
		}
	}

	/** Vykreslí datagrid uživatelů */
	public function createComponentUsersGrid($name)
	{
		$grid = new DataGrid();
		$grid->setDataSource($this->database->table(self::TABLE)->select('*')->where('firstName != ? OR firstName = ?', 'Main', 'NULL'));
		$grid->setColumnsHideable();


		$grid->addColumnText('id', 'ID');
		$grid->addColumnText('login', 'Login');

		$grid->addColumnText('firstName', 'First Name');
		$grid->addColumnText('lastName', 'Last Name');
		$grid->addColumnText('created_at', 'Created at')->setDefaultHide();
		$grid->addColumnText('avatar', 'Avatar')->setDefaultHide();
		$grid->addColumnText('vutID', 'VUT ID');
		$grid->addColumnText('rvviID', 'RVVI ID');
		$grid->addColumnText('job_title', 'Job Title');
		$grid->addColumnText('email', 'Email');
		$grid->addColumnText('first_title', 'Title')->setDefaultHide()->setReplacement([
			NULL => '-'
		]);
		$grid->addColumnText('second_title', 'Title')->setDefaultHide()->setReplacement([
			NULL => '-'
		]);
		$grid->addColumnText('phone_number', 'Phone number');
		$grid->addColumnText('work_phone_number', 'Work phone number');
		$grid->addColumnText('address', 'Adress')->setDefaultHide();
		$grid->addColumnText('office', 'Office')->setDefaultHide();

		$grid->addAction('delete', '', 'delete!')->setIcon('trash')->setClass('btn btn-xs btn-danger ajax')->setConfirm('Do you really want to delete row %s?');
		$grid->addAction('edit', 'Edit', 'edit');
		$grid->addAction('password_change', 'PW Change', 'passchange');

		return $grid;

	}

	/**
	 * Funkce pro smazní obrázků-volá se z formuláře
	 * @param $form
	 * @param $vals
	 */
	function deleteImage($form, $vals)
	{
		$folderUrl = "profile/avatars";
		if ($this->img != "default.png")
		{
			$this->imageStorage->delete($this->img, $folderUrl);
		}
		$array['avatar'] = NULL;
		$this->userManager->editColumn($vals->id, $array);

		$this->redirect('list');

	}

	/** Funkce zavolaná z datagridu na změnu hesla -> vloží do formuláře ID měněného záznamu  */
	public function actionPasschange($id)
	{

		$this['pwchangeForm']->setDefaults([
			'id' => $id,
		]);
	}

	/** Vytvoří formulář pro změnu hesla */
	protected function createComponentPwchangeForm()
	{
		$form = new Form();
		$form->addHidden('id', 'ID');
		$form->addPassword('password', 'Heslo')->setRequired();
		$form->addPassword('password_repeat', 'Heslo znovu')->addRule(Form::EQUAL, 'Hesla nesouhlasí.', $form['password'])->setRequired();
		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'pwchangeFormSucceeded'];
		return $form;
	}

	/** Funkce, která se zavolá po odeslání formuláře na změnu hesla */
	public function pwchangeFormSucceeded($form, $vals)
	{
		$this->userManager->passChange($vals);
		$this->flashMessage("Heslo bylo změněno");
		$this->redirect('list');
	}

	/** Funkce zavolaná z datagridu pro editaci uživatele -> vloží do formuláře defaultní hodnoty */
	public function actionEdit($id)
	{

		$user = $this->userManager->getUser($id);
		$this->template->img = $user->avatar;
		$this->img = $user->avatar;
		$this['editForm']->setDefaults($user->toArray());


	}

	/**
	 * Vykreslí componentu pro editační formulář uživatele
	 * @return Form
	 */
	protected function createComponentEditForm()
	{
		$form = $this->userForms->createEditForm();
		$form->addText('facebook', '');

		$form->addText('twitter', '');
		$form->addText('linkedin', '');
		$form->addText('research_gate', '');
		$form->addSubmit('img_delete', 'Image ')->onClick[] = [$this, 'deleteImage'];
		$form->onSuccess[] = [$this, 'editFormSucceeded'];

		return $form;
	}

	/** Funkce zavolaná po úspěšném odeslání editačního formuláře */
	function editFormSucceeded($form, $vals)
	{
		switch ($vals->parse_option)
		{
			case 1:
			{
				$title = $this->vutParser->parseProfile($vals->vutID);
				if ($vals['first_title'] == NULL)
				{
					$vals['first_title'] = $title['first_title'];
				}
				if ($vals['firstName'] == NULL)
				{
					$vals['firstName'] = $title['firstName'];
				}
				if ($vals['lastName'] == NULL)
				{
					$vals['lastName'] = $title['lastName'];
				}
				if ($vals['second_title'] == NULL)
				{
					$vals['second_title'] = $title['second_title'];
				}
				if ($vals['job_title'] == NULL)
				{
					$vals['job_title'] = $title['job_title'];
				}
				if ($vals['phone_number'] == NULL && isset($title['phone_number']))
				{
					$vals['phone_number'] = $title['phone_number'];
				}
				if ($vals['email'] == NULL && isset($title['email']))
				{
					$vals['email'] = $title['email'];
				}

			}
				break;
			case 2:
			{
				$title = $this->vutParser->parseProfile($vals->vutID);
				$vals['first_title'] = $title['first_title'];
				$vals['firstName'] = $title['firstName'];
				$vals['lastName'] = $title['lastName'];
				$vals['second_title'] = $title['second_title'];
				$vals['job_title'] = $title['job_title'];
				$vals['address'] = $title['address'];
				$vals['email'] = $title['email'];
				$vals['phone_number'] = $title['phone_number'];
				$vals['work_phone_number'] = $title['work_phone_number'];
				$vals['office'] = $title['office'];

				if (isset($title['phone_number']))
				{
					$vals['phone_number'] = $title['phone_number'];
				}
				if (isset($title['email']))
				{
					$vals['email'] = $title['email'];
				}
			}
				break;
			default:
			{
				break;
			}

		}


		if ($vals->avatar->name == null)
		{
			unset($vals['avatar']);
		}

		unset($vals['parse_option']);
		$this->userManager->edit($vals);
		$this->flashMessage('Profil byl editován');
		$this->redirect('list');


	}

	/**
	 * Smaže uživatele
	 * @param $id
	 */
	public function handleDelete($id)
	{
		$this->userManager->delete($id);
		$this->flashMessage('Profil byl smazán');
		$this->redirect('list');

	}

	/*
	public function actionDetail($id)
	{
		$this->template->users = $this->userManager->getUser($id);
	}
*/
}