#!/usr/bin/env php
<?php

require_once __DIR__ . '/Yijing.php';

function castCoin() 
{
	$coins = [
		random_int(0,63) % 2,
		random_int(0,63) % 2,
		random_int(0,63) % 2,
	];
	return array_reduce($coins, function($carry, $item) {
		return $carry + ($item ? 3 : 2);
	});
}

function linesToBinary($lines)
{
	$bin = '';
	foreach ($lines as $line) {
		switch ($line) {
			case 6:
			case 8:
				$bin .= '0';
				break;
			case 7:
			case 9:
				$bin .= '1';
				break;
		}
	}
	return base_convert($bin, 2, 10);
}

function formatQuickLine($val)
{
	switch ($val) {
		case 6:
		case 8:
			return '¦';
		case 7:
		case 9:
			return '|';
	}
}

function formatLine($val)
{
	switch ($val) {
		case 6:
			return '————  🗙  ————';
		case 7:
			return '—————————————';
		case 8:
			return '————     ————';
		case 9:
			return '——————◯——————';
	}
}

function formatLines($lines)
{
	$output = '';

	$number = Yijing::getNumber(linesToBinary($lines));
	$name = Yijing::getName($number);

	$output .= '  ' .$name . "\n\n";

	foreach ($lines as $index => $line) {
		$output .= '    ' . formatLine($line) . ' ' . ($index + 1) . "\n";
	}
	
	return $output;
}

function slug($question)
{
	return preg_replace(
		'/-{2,}/g', '-', 
		preg_replace('/[^a-zA-Z0-9]/', '-', $question)
	);
}

// Fail fast
if (count($argv) !== 1) {
	exit(1);
}

// Coins
$lines = explode('', $argv[1]);


// $filePath = __DIR__ . '/casts/' . date('Y-m-d') . '-' . substr(slug($question), 0, 20) . '.txt';
$file = fopen('php://stdout', 'w');

$p = "\n\n\n";

// Get true randomness
echo "\nType your question and press [Enter] 6 times:\n";
$lines = array_fill(0, 6, null);
$handle = fopen('php://stdin','r');
$question = '';
for ($i = 5; $i >= 0; $i--) {
	if ($i === 5) {
		$question = fgets($handle);
	} else {
		fgets($handle);
	}
	// fgets($handle);
	$lines[$i] = castCoin();
	echo formatQuickLine($lines[$i]);
}
echo $p;

// Show hexagram
fwrite($file, formatLines($lines) . $p);

// Changes
$changesTo = [];
foreach ($lines as $index => $line) {
	switch ($line) {
		case 6:
			$changesTo[$index] = 7;
			break;
		case 9:
			$changesTo[$index] = 8;
			break;
		default:
			$changesTo[$index] = $line;
			break;
	}
}

if ($changesTo != $lines) {
	fwrite($file, "Changes to:\n\n");
	fwrite($file, formatLines($changesTo) . $p);
}

// Link
$number = Yijing::getNumber(linesToBinary($lines));
// fwrite($file, "Read more: http://divination.com/iching/lookup/$number-2$p");

$text = include __DIR__ . '/wilhelm.php';
fwrite($file, $text[$number] . $p);
