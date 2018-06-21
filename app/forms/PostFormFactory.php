<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class PostFormFactory
{
	use Nette\SmartObject;

	/** Formulář pro práci s "News"
	 * @return Form
	 */
	public function createPostForm()
	{
		$form = new Form;
		//$form->onRender[] = [$this, 'makeBootstrap3'];
		$form->addHidden('id');
		$form->addHidden('created_at');
		$form->addText('title', 'Title:')
			->setRequired();
		$form->addTextArea('description', 'Description:')
			->setRequired();
		$form->addTextArea('text', 'Text:')
			->setRequired();
		$form->addSubmit('send', 'Uložit');
		$form->addProtection();
		return $form;
	}

}
