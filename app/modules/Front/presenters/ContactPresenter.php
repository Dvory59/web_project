<?php
/**
 * @author sczdavos
 * @email sczdavos@gmail.com
 * @site sczdavos.eu
 */

namespace App\FrontModule\Presenters;


use App\Model\UserManager;
use App\Model\EmailManager;
use Nette\Application\UI\Form;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class ContactPresenter extends BasePresenter
{
	/** @var UserManager @inject */
	public $userManager;

	/** @var  EmailManager @inject */
	public $emailManager;

	/** Vykreslí komponentu formuláře pro odesílání zpráv */
	public function createComponentEmailForm($name)
	{

		$form = new Form();
		$form->addText('email', 'Email');
		$form->addText('subject', 'Subject');
		$form->addTextArea('message', 'Message');
		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'emailFormSucceeded'];
		return $form;

	}

	/** Funkce volaná po odeslání formuláře => Odesílá zprávu na email a posílá zprávu k uložení do databáze */
	public function emailFormSucceeded($form,$vals)
	{
		$contact = $this->userManager->getUserByName('Main');
		 $this->emailManager->saveEmail($vals);

		 $mail = new Message();
		 $mail->setFrom(''.$vals->email.'');
		 $mail->addTo($contact->email);
		 $mail->setSubject(''.$vals->subject.'');
		 $mail->setBody(''.$vals->message.'');

		 $mailer = new SendmailMailer();
		 $mailer -> send($mail);

		 $this->redirect('default');

	}

}