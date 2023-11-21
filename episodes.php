<?php

require 'vendor/autoload.php';
require "private/GetEpisodes.php";
require "private/Util.php";

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\ErrorsDocument;

$strategy = new EpisodeGetStrategy();

try {
	if (isset($_GET['episodeNumber'])) {
		$episodeNumber = intval($_GET['episodeNumber']);
		$episodes =	$strategy->findOne($episodeNumber);
	} else {
		$episodes =	$strategy->find();
	}

	$document = CollectionDocument::fromResources(...array_map(function (GagEpisode $e) {
		return $e->toResourceObject();
	}, $episodes));

	setHeader();

	$document->sendResponse();
} catch (\Throwable $th) {
	ErrorsDocument::fromException($th)->sendResponse();
}
