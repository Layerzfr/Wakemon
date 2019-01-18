<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{

    public $evolutionArray;
    public $pokemonName;

    public function __construct()
    {
        $this->evolutionArray = array();
    }

    public function indexAction(Request $request)
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
        $c = curl_init();

        curl_setopt($c, CURLOPT_URL, "https://pokeapi.co/api/v2/pokemon/" . $id . "/");

        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($c, CURLOPT_HEADER, false);

        $output = curl_exec($c);

        if ($output === false) {
            return false;
        }
        curl_close($c);

        return json_decode($output, true);
    }

    private function species($id)
    {
        $species = curl_init();

        curl_setopt($species, CURLOPT_URL, "https://pokeapi.co/api/v2/pokemon-species/" . $id . "/");

        curl_setopt($species, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($species, CURLOPT_HEADER, false);

        $output = curl_exec($species);


        if ($output === false) {
            return false;
        }

        return json_decode($output, true);
    }

    private function evolution($link)
    {

        $evolution = curl_init();

        curl_setopt($evolution, CURLOPT_URL, $link);

        curl_setopt($evolution, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($evolution, CURLOPT_HEADER, false);

        $output = curl_exec($evolution);


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
}
