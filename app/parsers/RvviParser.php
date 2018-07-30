<?php


namespace App\Model;


use Nette\Database\Context;
use Nette\Utils\ArrayHash;
use iMarc;


class RvviParser extends BaseParser
{
	// Nastavení tokenu pro API
	const TOKEN = "9ad480963e1479dd31e82f8cda7d09664bfbd559";

	/**
	 * @var Context
	 */
	public $database;


	public $vutManager;

	public function __construct(Context $database, ProjectManager $vutManager)
	{
		$this->database = $database;
		$this->vutManager = $vutManager;
	}

	/**
	 * Vrací config pro API
	 * @param $id
	 * @param $oblast
	 * @return array
	 */
	function getConfig($id, $oblast)
	{
		$config = array(
			'token' => self::TOKEN,
			// token
			'oblast' => '' . $oblast . '',
			'tv-identifikator' => '' . $id . '',
			// informační oblast CEA Poskytovatelé
			'rezim' => 'filtr-detail',
			// zjednodušený seznam poskytovatelů
		);

		return $config;
	}


	/**
	 * Zaslání požadavku na API
	 * @param $url - adresa API
	 * @param $data - config
	 * @return JSON výstupu API
	 */
	function httpPost($url, $data)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}


	/**
	 * metoda pro parsování Výsledků (oblast riv)
	 */
	public function parseVysledky()

	{

		$array = new ArrayHash();
		$oblast = "riv";
		$googleLimit = 0;

		foreach ($this->database->table('users') as $id => $row)
		{

		if ($row->rvviID != NULL)
		{
			$config = $this->getConfig($row->rvviID, $oblast);

			$json = $this->httpPost('https://www.rvvi.cz/api.php', $config);
			$stream = json_decode($json, true);

			if ($stream['hlavicka']['kod'] == 200)
			{
				foreach ($stream['data'] AS $idx => $data)
				{
					$array['authors'] = "";
					$array['klicova_slova'] = "";
					$array['description_original'] = "";
					$array['description_en'] = "";
					$array['description_cz'] = "";
					$array['detail_url'] = null;
					$array['text'] = "";
					$array['name_en'] = "";
					//$array['name_original'] = $data['nazev-originalni'];
					$array['year_end'] = "";
					//$array['druh'] = $data['druh'];
					$array['id_rvvi'] = "";
					$array['druh_rvvi'] = "";

					$array['authors'] = "";
					$array['klicova_slova'] = "";

					//$array['id_rvvi'] = $data['id'];
					$array['name_en'] = $data['nazev-anglicky'];
					//$array['name_original'] = $data['nazev-originalni'];
					$array['year_end'] = $data['rok-uplatneni'];
					//$array['druh'] = $data['druh'];
					$array['id_rvvi'] = $data['kod'];
					$array['druh_rvvi'] = $data['druh'];
					//$array['provider_rvvi'] = $data['']
					//$array['program_rvvi']
					//$array['isbn1'] = $data['b-isbn'];
					//$array['isbn2'] = $data['c-isbn'];
					//$array['issn'] = $data['j-issn'];
					//$array['nazev-periodika'] = $data['nazev-periodika'];
					//$array['nazev-knihy'] = $data['c-nazev'];
					foreach ($data['seznam-tvurcu'] as $id => $name)
					{

						$firstName = $data['seznam-tvurcu'][$id]['jmeno'];
						$lastName = $data['seznam-tvurcu'][$id]['prijmeni'];

						$array['authors'] = $array['authors'] . '' . $firstName . ' ' . $lastName . ', ';
					}


					foreach ($data['klicova-slova'] as $id => $klic)
					{
						$array['klicova_slova'] = $array['klicova_slova'] . ',' . $data['klicova-slova'][$id];

					}
					$array['description_original'] = $data['anotace-originalni'];
					$array['description_en'] = $data['anotace-anglicky'];

					$array['description_cz'] = $data['anotace-cesky'];

					if ($data['www'] == "")
					{
						$array['detail_url'] = null;
						$googleLimit++;
					}
					else
					{
						$array['detail_url'] = $data['www'];
					}

					$array['text'] = "";
					$array['source'] = "RVVI";
					$array['type'] = "vysledky";


					$googleLimit = $this->vutManager->checkAndUpdate($array,$googleLimit);

				}


			}


		}


	}


}


	/**
	 * Metoda pro parse projektů z RVVI (oblast cep)
	 */
	public function parseProject()
	{


		$array = new ArrayHash();
		$oblast = "cep";
		$googleLimit = 0;
		foreach ($this->database->table('users') as $id => $row)
		{

			if ($row->rvviID)
			{




				$config = array(
					'token' => self::TOKEN,
					// token
					'oblast' => 'cep',
					'rp-identifikator' => '' . $row->rvviID . '',
					// informační oblast CEA Poskytovatelé
					'rezim' => 'filtr-detail',
					// zjednodušený seznam poskytovatelů
				);


				$json = $this->httpPost('https://www.rvvi.cz/api.php', $config);
				$stream = json_decode($json, true);



				if ($stream['hlavicka']['kod'] == 200)
				{

					foreach ($stream['data'] AS $idx => $data)
					{

						$array['finance'] = 0;
						$array['klicova_slova'] = "";

						$array['name_en'] = $data['nazev'];
						$array['provider_rvvi'] = $data['poskytovatel'];
						$array['program_rvvi'] = $data['program-nazev'];
						$array['year_start'] = $data['program-rok-zahajeni'];
						$array['year_end'] = $data['program-rok-ukonceni'];
						$array['druh_rvvi'] = $data['druh-souteze'];

						foreach ($data['finance']['tabulka']['CEL'] as $id => $name)
						{
							$array['finance'] = $array['finance'] + $data['finance']['tabulka']['SRU'][$id];
						}

						foreach ($data['klicova-slova'] as $id => $klic)
						{
							$array['klicova_slova'] = $array['klicova_slova'] . ',' . $data['klicova-slova'][$id];

						}

						$kod = str_replace("/","%2F",$data['kod']);

						$array['detail_url'] = "https://www.rvvi.cz/cep?s=jednoduche-vyhledavani&ss=detail&n=0&h=" . $kod;
						$array['type'] = 'project';
						$array['source'] = 'RVVI';

						$googleLimit = $this->vutManager->checkAndUpdate($array,$googleLimit);

					}

				}

			}

		}
	}
}