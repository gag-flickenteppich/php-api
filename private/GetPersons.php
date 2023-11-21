<?php
require 'vendor/autoload.php';
require 'GetEpisodes.php';

use quickRdf\DataFactory as DF;
use termTemplates\DatasetExtractors;
use termTemplates\QuadTemplate as QT;
use alsvanzelf\jsonapi\objects\ResourceObject;

class Person
{
	public String $id;
	public String $name;
	public String|null $wikipediaUrl;
	public array $references;


	public function toResourceObject()
	{
		$resObj = new ResourceObject("person", $this->id);
		$resObj->add('name', $this->name);
		$resObj->add('wikipediaUrl', $this->wikipediaUrl);
		$resObj->addRelationship(
			"episodes",
			array_map(function ($x) {
				return $x->toResourceObject();
			}, $this->references)
		);
		return $resObj;
	}
}


class PersonsGetStrategy
{
	private $graph;
	private $episodesGraph;
	private $episodeNumber;

	public function __construct($episodeNumber)
	{
		$this->graph = loadPersonsGraph();
		$this->episodesGraph = loadEpisodesGraph();
		$this->episodeNumber = $episodeNumber;
	}


	public function find()
	{
		$persons = array();

		$mentions = $this->getMentionedPersonsForEpisode();

		$graph = $this->graph;
		$episodeGraph = $this->episodesGraph;

		foreach ($mentions as $mention) {
			foreach ($graph->listSubjects(new QT($mention, nn(rdf("type")), nn(schema("Person")))) as $subject) {
				$personName = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("name"))));
				$website = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("sameAs"))));

				$person = new Person();
				$person->id = $subject->getValue();
				$person->name = $personName;
				$person->wikipediaUrl = $website;
				$person->references = array();


				$eps = array();
				foreach ($this->getEpisodesMatchingPerson($person) as $episodeId) {

					$episodeNumber = DatasetExtractors::getObjectValue($episodeGraph, new QT($episodeId, nn(schema("episodeNumber"))));
					$episodeName = DatasetExtractors::getObjectValue($episodeGraph, new QT($episodeId, nn(schema("name"))));
					$website = DatasetExtractors::getObjectValue($episodeGraph, new QT($episodeId, nn(schema("WebSite"))));
					$thumbnailUrl = DatasetExtractors::getObjectValue($episodeGraph, new QT($episodeId, nn(schema("thumbnailUrl"))));

					$episode = new GagEpisode();
					$episode->episodeNumber = $episodeNumber;
					$episode->name = $episodeName;
					$episode->website = $website;
					$episode->thumbnailUrl = $thumbnailUrl;
					$eps[$episodeNumber] = $episode;
				}
				$person->references = array_values($eps);

				array_push($persons, $person);
			}
		}

		return $persons;
	}

	private function getMentionedPersonsForEpisode()
	{
		$mentions = array();
		foreach ($this->getMatchingEpisodes() as $episode) {
			foreach (DatasetExtractors::getObjects($this->episodesGraph, new QT($episode, nn(schema("mentions")))) as $mention) {
				array_push($mentions, $mention);
			}
		}
		return $mentions;
	}

	private function getMatchingEpisodes()
	{
		$matchingEpisodes = $this->episodesGraph->listSubjects(new QT(null, nn(schema("episodeNumber")), DF::literal($this->episodeNumber)));
		return $matchingEpisodes;
	}

	private function getEpisodesMatchingPerson(Person $person)
	{
		$matchingEpisodes = $this->episodesGraph->listSubjects(new QT(null, nn(schema("mentions")), nn($person->id)));
		return $matchingEpisodes;
	}
}
