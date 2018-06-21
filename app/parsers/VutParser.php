<?php

namespace App\Model;



use Sunra\PhpSimple\HtmlDomParser;
use Nette\Database\Context;
use Nette\Utils\ArrayHash;

class VutParser extends BaseParser
{

	private $vutManager;
	/**
	 * @var UserManager
	 */
	private $userManager;

	// configurace dat posílaných při žádosti file_get_html
	private $arrContextOptions = [
		'ssl' => [
			'verify_peer' => false,
			'verify_peer_name' => false
		],
		'http' => [
			'method' => "GET",
			'header' => "Accept-language: en\r\n"."User-Agent:Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6\r\n" . "Cookie: foo=bar\r\n"
		]
	];


	/**
	 * VutParser constructor.
	 * @param Context $database
	 * @param ProjectManager $vutManager
	 * @param UserManager $userManager
	 */
	public function __construct(Context $database, ProjectManager $vutManager, UserManager $userManager)
	{
		parent::__construct($database);
		$this->database = $database;
		$this->vutManager = $vutManager;
		$this->userManager = $userManager;


	}

	/**
	 *  převod diakritiky do normální
	 * @param string $title
	 * @return string
	 */
	function diacToNormal($title)
	{

		static $convertTable = array(
			'á' => 'a',
			'Á' => 'A',
			'ä' => 'a',
			'Ä' => 'A',
			'č' => 'c',
			'Č' => 'C',
			'ď' => 'd',
			'Ď' => 'D',
			'é' => 'e',
			'É' => 'E',
			'ě' => 'e',
			'Ě' => 'E',
			'ë' => 'e',
			'Ë' => 'E',
			'í' => 'i',
			'Í' => 'I',
			'ï' => 'i',
			'Ï' => 'I',
			'ľ' => 'l',
			'Ľ' => 'L',
			'ĺ' => 'l',
			'Ĺ' => 'L',
			'ň' => 'n',
			'Ň' => 'N',
			'ń' => 'n',
			'Ń' => 'N',
			'ó' => 'o',
			'Ó' => 'O',
			'ö' => 'o',
			'Ö' => 'O',
			'ř' => 'r',
			'Ř' => 'R',
			'ŕ' => 'r',
			'Ŕ' => 'R',
			'š' => 's',
			'Š' => 'S',
			'ś' => 's',
			'Ś' => 'S',
			'ť' => 't',
			'Ť' => 'T',
			'ú' => 'u',
			'Ú' => 'U',
			'ů' => 'u',
			'Ů' => 'U',
			'ü' => 'u',
			'Ü' => 'U',
			'ý' => 'y',
			'Ý' => 'Y',
			'ÿ' => 'y',
			'Ÿ' => 'Y',
			'ž' => 'z',
			'Ž' => 'Z',
			'ź' => 'z',
			'Ź' => 'Z',
		);
		$title = strtolower(strtr($title, $convertTable));
		$title = preg_replace('/[^a-zA-Z0-9]+/u', '-', $title);
		$title = str_replace('--', '-', $title);
		$title = trim($title, '-');
		return $title;

	}


	/**
	 * Hlavní funkce parsování publikací z VUT profilů
	 */
	public function ParsePublications()
	{	$googleLimit = 0;
		$vals = new ArrayHash();
		$user_vals = new ArrayHash();
		foreach ($this->database->table('users') as $id => $row)
		{

			if ($row->vutID)
			{
				$page = HtmlDomParser::file_get_html("https://www.vutbr.cz/lide/" . $row->vutID . "/publikace#navigace-vizitka", false, stream_context_create($this->arrContextOptions));


				if ($page->find("div[id=kontakty] h1") == NULL && $page->find("div[class=alert alert-danger] div[class=alert-text]") == NULL)
				{
					$projects = $page->find("ul[class=list-timeline] li p");
					$user_vals['id'] = $row->id;
					$user_vals['parsed_vut'] = "yes";


					$this->userManager->profileUpdate($user_vals);
					$year = NULL;
					$odkaz = NULL;

					// $projects foreach
					foreach ($projects as $block_citate)
					{
						$firstName = false;
						$lastName = false;

						//určení roku
						if (!empty($block_citate->class))
						{
							$year = $block_citate->innertext;
						}
						else
						{

							//najde odkaz na "Detail"
							if($block_citate->find('a', 0))
							$odkaz = $block_citate->find('a', 0)->href;
							// vybere ze stránky "Detail" jméno
							$vals = $this->parsePublicationDetail($odkaz);

							if ($block_citate->find('a', 2))
								$vals['detail_url'] = $block_citate->find('a', 2)->href;
							elseif($block_citate->find('a', 0))
							{
								$odkazs = "https://www.vutbr.cz$odkaz";
								$vals['detail_url'] = $odkazs;
							}



							//$authors = $this->getNames($vals['citation']);
							$vals['citation'] = $this->parseCitation($block_citate);



							$vals['authors'] = $this->getNames($vals['citation']);
							$vals['source'] = "VUT";
							$vals['type'] = "publications";
							$vals['year_end'] = $year;

							if (empty($block_citate->class))
							{
								$googleLimit = $this->vutManager->checkAndUpdate($vals,$googleLimit);
							}

						}


					}

				}
			}


		}
	}


	/**
	 * Vrátí seznam autorů z citace
	 * @param $citation
	 * @return string
	 */
	public function getNames($citation)
	{
		$poleJmen = array();
		$replaceParagraph = str_replace("<p>", "", $citation);
		$jmena = explode(";", $replaceParagraph);
		$delka = sizeof($jmena);
		$i = 0;

		foreach ($jmena as $item)
		{
			if ($i == $delka - 1)
			{
				$posledni = explode(".", $item);
				$posledni[0] = $posledni[0] . ".";
				$poleJmen[$i] = $posledni[0];
			}
			else
			{
				$poleJmen[$i] = $item;
			}
			$i++;
		}



		$users = $this->userManager->getUsers();
		$authorsString = "";

		foreach ($poleJmen as $item)
		{

			$iter = 0;
			foreach ($users as $user)
			{

				$author = preg_replace("/,+.+/", "", $item);


				if ($this->diacToNormal($author) == $this->diacToNormal($user->lastName))
				{
					if ($authorsString == "")
					{
						$authorsString = str_replace(' ','',$user->firstName) . " " . str_replace(' ','',$user->lastName);

					}
					else
					{
						$authorsString = $authorsString . ", " . str_replace(' ','',$user->firstName) . " " . str_replace(' ','',$user->lastName);
					}
					$iter = 1;
					break;
				}
			}
			if ($iter == 0)
			{
				if ($authorsString == "")
				{
					$authorsString = ucfirst(mb_strtolower($author, 'UTF-8'));

				}
				else
				{
					$authorsString = $authorsString . ", " . ucfirst(mb_strtolower($author, 'UTF-8'));
				}
			}

		}




		return $authorsString;


	}

	/**
	 * Parse Detailů z URL v bloku publikace
	 * @param $odkaz z bloku publikace
	 * @return mixed|string
	 */
	public function parsePublicationDetail($odkaz)
	{
		$vals = new ArrayHash();
		$counter = 0;
		$detail = HtmlDomParser::file_get_html("https://www.vutbr.cz" . $odkaz);

		if ($block = $detail->find('div[class=grid grid--0] div[class=grid__cell size--t-3-12 holder holder--lg holder--0-r]'))
		{
			foreach ($block as $item)
			{


				if ($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-3-12 holder holder--lg holder--0-r]", $counter)->find("p[class=b-detail__subtitle font-secondary]", 0))
				{
					$line = trim($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-3-12 holder holder--lg holder--0-r]", $counter)->find("p[class=b-detail__subtitle font-secondary]", 0)->innertext());


					switch ($line)
					{
						case 'Originální název':
						{
							if ($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter))
							{
								$vals['name_cz'] = trim($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter)->innertext());
							}
							else
							{
								$vals['name_cz'] = NULL;
							}

						}
							break;

						case 'Anglický název':
						{
							if ($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter))
							{
								$vals['name_en'] = trim($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter)->innertext());
							}
							else $vals['name_en'] = NULL;


						}
							break;

						case 'Český abstrakt':
						{
							if ($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter))
							{
								$vals['description_cz'] = trim($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter)->innertext());
							}
							else $vals['description_cz'] = NULL;

						}
							break;

						case 'Anglický abstrakt':
						{
							if ($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter))
							{
								$vals['description_en'] = trim($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter)->innertext());
							}
							else $vals['description_en'] = NULL;

						}
							break;

						case 'Originální abstrakt':
						{
							if ($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter))
							{
								$vals['description_original'] = trim($detail->find("div[class=grid grid--0] div[class=grid__cell size--t-9-12 holder holder--lg] div[class=b-detail__content] p", $counter)->innertext());
							}
							else $vals['description_original'] = NULL;

						}
							break;

					}
					$counter++;

				}

			}
		}
		else
		{
			$vals['name_cz'] = NULL;
			$vals['name_en'] = NULL;
			$vals['description_cz'] = NULL;
			$vals['description_en'] = NULL;
			$vals['description_original'] = NULL;
			$vals['detail_url'] = NULL;
		}


		return $vals;

	}

	/**
	 *  Vyparsuje z bloku publikace pouze citaci
	 * @param $block_citate
	 * @return mixed
	 */
	public function parseCitation($block_citate)
	{
		foreach ($block_citate->find('a') as $element)
		{
			$element->outertext = '';

		}

		return str_replace("|", "", str_replace(" ", "", $block_citate));
	}


	/**
	 * Parsování informací z VUT profilů
	 * @param $id profilu
	 * @return mixed
	 */
	public function parseProfile($id)
	{

		$vals['address'] = NULL;
		$vals['email'] = NULL;
		$vals['phone_number'] = NULL;
		$vals['work_phone_number'] = NULL;
		$vals['office'] = NULL;
		$vals['first_title'] = NULL;
		$vals['firstName'] = NULL;
		$vals['lastName'] = NULL;
		$vals['second_title'] = NULL;
		$vals['job_title'] = NULL;

		$page = HtmlDomParser::file_get_html("https://www.vutbr.cz/en/people/" . $id . "#navigace-vizitka", false, stream_context_create($this->arrContextOptions));

		$profile_block = $page->find('div[class=b-profile__info holder holder--lg]', 0);


		if ($profile_block->find('p[class=title title--xs title--secondary-darken] span[class=title__item]', 0))
			$vals['first_title'] = $profile_block->find('p[class=title title--xs title--secondary-darken] span[class=title__item]', 0)->innertext();


		if ($profile_block->find('h1[class=title title--secondary] span[class=title__item]', 0))
			$vals['firstName'] = $profile_block->find('h1[class=title title--secondary] span[class=title__item]', 0)->innertext();


		if ($profile_block->find('h1[class=title title--secondary] span[class=title__item]', 1))
			$vals['lastName'] = $profile_block->find('h1[class=title title--secondary] span[class=title__item]', 1)->innertext();


		if ($profile_block->find('p[class=title title--xs title--secondary-darken]', 1))
			$vals['second_title'] = $profile_block->find('p[class=title title--xs title--secondary-darken]', 1)->find('span[class=title__item]', 0)->innertext();


		if ($profile_block->find('p[class=b-profile__position font-secondary]', 0))
			$vals['job_title'] = $profile_block->find('p[class=b-profile__position font-secondary]', 0)->innertext();

		$contact_block = $page->find("div [class=grid__cell size--t-7-12 size--8-12 holder holder--lg b-profile__content]", 0);
		$form = $contact_block->find("table[class=table-blank]", 0)->find("tbody tr");
		foreach ($form as $item)
		{

			$th = $item->find("th", 0)->innertext();
			$td = $item->find("td", 0);


			switch ($th)
			{
				case "Address":
				{

					$vals['address'] = $td->innertext();

				}
					break;
				case "E-mail":
				{
					$email = $td->innertext();

					$vals['email'] = str_replace('feec.', '', $email);

				}
					break;
				case "Phone":
				{
					$vals['phone_number'] = $td->innertext();
				}
					break;
				case "Work phone":
				{

					$vals['work_phone_number'] = $td->innertext();
				}
					break;
				case "Room":
				{
					$vals['office'] = $td->innertext();
				}
					break;
				default:
					;
			}


		}
		return $vals;

	}


}