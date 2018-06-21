<?php


namespace App\AdminModule\Presenters;

use App\Model\UserManager;
use Nette\Application\UI\Form;

class DefaultPresenter extends BaseUserPresenter
{

	/** @var UserManager @inject */
	public $userManager;


	/**
	 * Vypíše hlavní stranu a vloží do formuláře informace o Main Contact skupiny
	 * @param UserManager $userManager
	 */
	public function renderDefault()
	{
		if ($this->userManager->getMainContact() != NULL)
		{
			$mainContact = $this->userManager->getMainContact()->fetch();

			$this['mainContact']->setDefaults($mainContact);
		}
	}

	/** Vykreslí formulář na editaci Main Contact */
	public function createComponentMainContact($name)
	{
		$form = new Form();
		$form->addText('email', '');
		$form->addText('phone_number', '');
		$form->addText('work_phone_number', '');
		$form->addText('facebook', '');
		$form->addText('twitter', '');
		$form->addText('linkedin', '');
		$form->addText('research_gate', '');
		$form->addTextArea('address', '');
		$form->addSubmit('send', '');
		$form->onSuccess[] = [$this, 'mainContactSucceeded'];
		return $form;
	}
	/** Funkce vykonávaná po úspěšném odeslání fomuláře */
	public function mainContactSucceeded($form, $vals)
	{
		$vals['firstName'] = "Main";
		$vals['lastName'] = "Contact";
		$this->userManager->addMainContact($vals);

	}
}