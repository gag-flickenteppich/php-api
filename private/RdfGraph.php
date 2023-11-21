<?php
require 'vendor/autoload.php';

use quickRdf\DataFactory as DF;

function loadEpisodesGraph()
{
	$graph = new quickRdf\Dataset();
	$df = new DF();
	$parser = new quickRdfIo\TriGParser($df);
	$stream = fopen('./data/gag.ttl', 'r');
	$graph->add($parser->parseStream($stream));
	fclose($stream);
	return $graph;
}

function loadPersonsGraph()
{
	$graph = new quickRdf\Dataset();
	$df = new DF();
	$parser = new quickRdfIo\TriGParser($df);
	$stream = fopen('./data/persons.ttl', 'r');
	$graph->add($parser->parseStream($stream));
	fclose($stream);
	return $graph;
}

function rdf(String $s)
{
	return  "http://www.w3.org/1999/02/22-rdf-syntax-ns#" . $s;
}

function schema(String $s)
{
	return  "http://schema.org/" . $s;
}

function nn(String $s)
{
	return DF::namedNode($s);
}
