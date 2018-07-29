<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\BasePresenter;
use App\Forms;
use Nette\Application\UI\Form;


class SignPresenter extends BasePresenter
{

	/**
	 * @var Forms\UserForms
	 */
	private $userForms;


	public function __construct(Forms\UserForms $userForms)
	{

		$this->userForms = $userForms;
	}

	/** Otevře přístup nepřihlášených uživatelů do SignPresenter */
	protected function isAllowed()
	{
		return true;
	}

	/**
	 * Vytvoří formulář pro přihlášení
	 * @return Form
	 */
	protected function createComponentSignInForm()
	{
		return $this->userForms->createLoginForm(function () {
			$this->redirect('Default:');
		});


	}

	/** Funkce pro odhlášení */
	public function actionOut()
	{
		$this->getUser()->logout();
		$this->redirect(':Front:Default:');
	}


}
