<?php
require 'vendor/autoload.php';
require 'RdfGraph.php';

use quickRdf\DataFactory as DF;
use termTemplates\DatasetExtractors;
use termTemplates\QuadTemplate as QT;
use alsvanzelf\jsonapi\objects\ResourceObject;

class GagEpisode
{
	public $episodeNumber;
	public $name;
	public $website;
	public $thumbnailUrl;


	public function toResourceObject()
	{
		$resObj = new ResourceObject("episode", $this->episodeNumber);
		$resObj->add('name', $this->name);
		$resObj->add('episodeNumber', $this->episodeNumber);
		$resObj->add('episodeUrl', $this->website);
		$resObj->add('thumbnailUrl', $this->thumbnailUrl);
		return $resObj;
	}
}

class EpisodeGetStrategy
{
	private $graph;

	public function __construct()
	{
		$this->graph = loadEpisodesGraph();
	}


	public function find()
	{
		$episodes = array();
		$graph = $this->graph;

		// extract episodes from graph
		foreach ($graph->listSubjects() as $subject) {
			$episodeNumber = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("episodeNumber"))));
			$episodeName = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("name"))));
			$website = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("WebSite"))));
			$thumbnailUrl = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("thumbnailUrl"))));

			$episode = new GagEpisode();
			$episode->episodeNumber = $episodeNumber;
			$episode->name = $episodeName;
			$episode->website = $website;
			$episode->thumbnailUrl = $thumbnailUrl;
			array_push($episodes, $episode);
		}

		return $episodes;
	}

	public function findOne($episodeNumber)
	{
		$episodes = array();
		$graph = $this->graph;

		// extract episodes from graph
		foreach ($graph->listSubjects(new QT(null, nn(schema("episodeNumber")), DF::literal($episodeNumber))) as $subject) {
			$episodeNumber = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("episodeNumber"))));
			$episodeName = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("name"))));
			$website = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("WebSite"))));
			$thumbnailUrl = DatasetExtractors::getObjectValue($graph, new QT($subject, nn(schema("thumbnailUrl"))));

			$episode = new GagEpisode();
			$episode->episodeNumber = $episodeNumber;
			$episode->name = $episodeName;
			$episode->website = $website;
			$episode->thumbnailUrl = $thumbnailUrl;
			array_push($episodes, $episode);
		}

		return $episodes;
	}
}
