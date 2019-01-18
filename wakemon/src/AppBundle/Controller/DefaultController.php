<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{

    public $evolutionArray;
    public $pokemonName;

    public function __construct()
    {
        $this->evolutionArray = array();
    }

    public function indexAction()
    {
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    public function pokemonDetailAction($id)
    {

        $pokemon = $this->pokemon($id);
        $this->pokemonName = $pokemon['name'];
        $species = $this->species($pokemon['id']);
        $this->evolution($species['evolution_chain']['url']);

        return $this->render('default/pokedex.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
            'pokemon' => $pokemon,
            'pokemon_species' => $species,
            'evolution' => $this->evolutionArray,
            'from_pokemon' => $species['evolves_from_species']['name'],
        ]);
    }

    private function pokemon($id)
    {
        $output = $this->curlAction("https://pokeapi.co/api/v2/pokemon/" . $id . "/");

        if ($output === false) {
            return false;
        }

        return json_decode($output, true);
    }

    private function species($id)
    {
        $output = $this->curlAction("https://pokeapi.co/api/v2/pokemon-species/" . $id . "/");


        if ($output === false) {
            return false;
        }

        return json_decode($output, true);
    }

    private function evolution($link)
    {

        $output = $this->curlAction($link);


        if ($output === false) {
            return false;
        }
        $this->loopEvolution((json_decode($output, true))['chain']['evolves_to']);

        return json_decode($output, true);
    }

    private function loopEvolution($evolution)
    {
        if (!empty($evolution)) {
            foreach ($evolution as $chain) {
                if ($this->pokemonName !== $chain['species']['name']) {
                    array_push($this->evolutionArray, $chain['species']['name']);
                }
                if (!empty($chain['evolves_to'])) {
                    $this->loopEvolution($chain['evolves_to']);
                }
            }
        }
    }

    private function curlAction($curl){
        $init = curl_init();

        curl_setopt($init, CURLOPT_URL, $curl);

        curl_setopt($init, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($init, CURLOPT_HEADER, false);

        return curl_exec($init);
    }
}
