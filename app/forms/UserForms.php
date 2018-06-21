<?php

namespace App\Forms;

use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Nette;



/**
 * Class UserFormsFactory
 * @package App\Forms
 */
class UserForms
{
	/** @var User Uživatel. */
	private $user;
	/**
	 * @var UserManager
	 */
	private $userManager;

	/**
	 * Konstruktor s injektovanou třidou uživatele.
	 * @param User $user automaticky injektovaná třída uživatele
	 */
	public function __construct(User $user,UserManager $userManager)
	{
		$this->user = $user;
		$this->userManager = $userManager;
	}





	/**
	 * Rodičovský formulář pro login/registraci
	 * @return Form
	 */
	private function createBasicForm()
	{
		$form = new Form;
		$form->addText('login', 'Login')->setRequired();
		$form->addPassword('password', 'Heslo')->setRequired();
		return $form;
	}

	/**
	 * Vrací komponentu formuláře s přihlašovacími prvky a zpracování přihlašování podle uživatelských instrukcí.
	 * @param null|Form $form komponenta formuláře, která se má rozšířit o přihlašovací prvky, nebo null, pokud se má vytvořit nový formulář
	 * @param null|ArrayHash $instructions uživatelské instrukce pro zpracování registrace
	 * @return Form komponenta formuláře s přihlašovacími prky
	 */
	public function createLoginForm(callable $onSuccess)
	{
		$form = $this->createBasicForm();

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
			try {
				$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
				$this->user->login($values->login, $values->password);
			} catch (Nette\Security\AuthenticationException $e) {
				$form->addError('The username or password you entered is incorrect.');
				return;
			}
			$onSuccess();
		};

		return $form;
	}

	/**
	 * Vrací komponentu formuláře s registračními prvky a zpracování registrace podle uživatelských instrukcí.
	 * @param null|Form $form komponenta formuláře, která se má rozšířit o registrační prvky, nebo null, pokud se má vytvořit nový formulář
	 * @param null|ArrayHash $instructions uživatelské instrukce pro zpracování registrace
	 * @return Form komponenta formuláře s registračními prky
	 */
	public function createRegisterForm()
	{
		$form = $this->createBasicForm();
		$form->addPassword('password_repeat', 'Heslo znovu')->addRule(Form::EQUAL, 'Hesla nesouhlasí.', $form['password'])->setRequired();

		$form->addSubmit('register', 'Registrovat');
		return $form;
	}


	/**
	 * Vytváří formulář pro editaci uživatelů
	 * @return Form
	 */
	public function createEditForm()
	{
		$parseOption=[
			1 => 'Parse and Update',
			2 => 'Parse and Replace',
		];

		$form = new Form();
		$form->addHidden('id','ID');
		$form->addText('vutID','VUT ID');
		$form->addText('rvviID','RVVI ID');
		$form->addText('firstName', 'Jméno');
		$form->addText('lastName', 'Příjmení');
		$form->addEmail('email','Email');
		$form->addText('phone_number','Phone Number');
		$form->addText('first_title','First Title');
		$form->addText('second_title','Second Title');
		$form->addText('job_title','Job title');
		$form->addText('office','Office');
		$form->addText('work_phone_number','Work_phone_number');
		$form->addTextArea('description','Profil');
		$form->addTextArea('address','Adress');
		$form->addUpload('avatar', 'Avatar')
			-> addRule(Form::IMAGE,'Avatar has to be in IPEG, PNG or GIF format!')
			-> setRequired(false);
		$form->addSelect('parse_option','None',$parseOption)
			->setPrompt('None');
		$form->addSubmit('send', 'Send');

		return $form;
	}
}