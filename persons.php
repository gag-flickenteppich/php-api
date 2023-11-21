<?php

require 'vendor/autoload.php';
require "private/GetPersons.php";
require "private/Util.php";

use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;

/* if (!isset($_GET['episodeNumber'])) {
	$ex = new Exception("missing query parameter 'command'");
	$errObj = new ErrorObject(500, "Missing Query Parameter", "Expected query paramter 'episodeNumber' but found none");
	$doc = new ErrorsDocument($errObj);
	$doc->sendResponse();
	exit();
} */

try {
	// $episodeNumber = intval($_GET["episodeNumber"]);
	$episodeNumber = intval($argv[1]);

	$strategy = new PersonsGetStrategy($episodeNumber);
	$persons =	$strategy->find();

	$document = CollectionDocument::fromResources(...array_map(function (Person $e) {
		return $e->toResourceObject();
	}, $persons));

	setHeader();

	$document->sendResponse();
} catch (\Throwable $th) {
	ErrorsDocument::fromException($th)->sendResponse();
}
