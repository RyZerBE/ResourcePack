<?php
/*
 * Copyright (c) Jan Sohn aka. xxAROX.
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
$config = [
	"enable-copyright-header" => true,
	"messages"                => [
		"Copyright (c) RyZerBE",
		"This Source is under license",
		"Don't steal code!",
		"https://ryzer.be",
		"https://play.ryzer.be",
		"https://github.com/RyZerBE",
		"https://github.com/Baubo-LP",
		"https://github.com/Matze997",
		"https://github.com/xxAROX",
		"https://patreon.com/xx_arox",
		"https://youtube.com/aromastoffe",
		"https://instagram.com/xx_arox",
		"https://twitter.com/xx_arox",
		"https://twitter.com/Matze998",
	],
	"blacklisted-files"       => ["manifest.json"],
];
$affectedFiles = 0;
$basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$inputPath = $basePath . "pack" . DIRECTORY_SEPARATOR;
$outputPath = $basePath . "output" . DIRECTORY_SEPARATOR;

if ($config["enable-copyright-header"] && !file_exists("{$basePath}copyright_notice.txt")) {
	file_put_contents($basePath . "copyright_notice.txt", "");
}
if (!is_file($inputPath . "manifest.json")) {
	echo "Resource-Pack('manifest.json') not found!" . PHP_EOL;
	exit(1);
}
if (!is_dir($outputPath)) {
	@mkdir($outputPath);
}
start();
echo "Finished!" . PHP_EOL;
#Functions:
/**
 * Function start
 * @return void
 */
function start(): void{
	global $inputPath, $outputPath;
	$affectedFiles = 0;
	removeDirectory($outputPath);
	mkdir($outputPath);
	$started = microtime(true);
	cloneDirectories();
	/**
	 * @var string $path
	 * @var SplFileInfo $fileInfo
	 */
	foreach (getRecursiveFiles($inputPath) as $path => $fileInfo) {
		$affectedFiles = compileFile($path);
	}
	$time = round(microtime(true) - $started, 2);
	echo "Secured {$affectedFiles} files in " . ($time) . "ms" . PHP_EOL;
}

/**
 * Function compileFile
 * @param string $path
 * @return int
 */
function compileFile(string $path): int{
	global $config, $basePath, $inputPath, $outputPath, $affectedFiles;
	$fileInfo = new SplFileInfo($path);
	if ($fileInfo->getExtension() == "json" && !is_null($fileInfo->getFilename()) && !in_array($fileInfo->getFilename(), $config["blacklisted-files"])) {
		$content = file_get_contents($path);
		encode($content);
		putComments($content);
		file_put_contents(str_replace($inputPath, $outputPath, $path), (($config["enable-copyright-header"] ?? false)
				? "/*" . PHP_EOL . (" * " . str_replace(PHP_EOL, PHP_EOL . " * ", file_get_contents($basePath . "copyright_notice.txt"))) . PHP_EOL . " */" . PHP_EOL . PHP_EOL
				: "") . $content);
		$affectedFiles++;
	} else {
		file_put_contents(str_replace($inputPath, $outputPath, $path), file_get_contents($path));
	}
	return $affectedFiles;
}

/**
 * Function putComments
 * @param string $content
 * @return void
 * @throws Exception
 */
function putComments(string &$content): void{
	$content = preg_replace_callback('/("\:|\[|\]|\{|\}|\,)/m', static function ($match){
		$char = $match[0][0];
		$before = $after = "";
		if ($char == "\":" || $char == ",") {
			$before = $char . randomEOL(0, 1);
		} else {
			$after = $char . randomEOL(0, 1);
		}
		return $before . generateNonSenseComment() . $after;
	}, $content, -1, $count, PREG_OFFSET_CAPTURE);
}

/**
 * Function getRecursiveFiles
 * @param string $path
 * @return SplFileInfo[]
 */
function getRecursiveFiles(string $path): array{
	$arr = [];
	/** @var SplFileInfo $part */
	foreach ((new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path))) as $part) {
		if ($part->isFile()) {
			$fileInfo = new SplFileInfo($part);
			$arr[$part->getPath() . "/" . $part->getFilename()] = $fileInfo;
		}
	}
	return $arr;
}

/**
 * Function removeDirectory
 * @param string $path
 * @return void
 */
function removeDirectory(string $path): void{
	if (is_file($path)) {
		@unlink($path);
	} else if (is_dir($path)) {
		$scan = glob(rtrim($path, '/') . '/*');
		foreach ($scan as $index => $_) {
			removeDirectory($_);
		}
		@rmdir($path);
	}
}

/**
 * Function cloneDirectories
 * @return void
 */
function cloneDirectories(): void{
	global $inputPath, $outputPath;
	/** @var SplFileInfo $part */
	foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($inputPath)) as $part) {
		if (!$part->isFile()) {
			@mkdir(str_replace($inputPath, $outputPath, $part), 0777, true);
		}
	}
}

/**
 * Function randomEOL
 * @param int $min
 * @param int $max
 * @return string
 */
function randomEOL(int $min = 1, int $max = 8): string{
	return str_repeat(PHP_EOL, random_int($min, $max));
}

/**
 * Function randomSPACE
 * @param int $min
 * @param int $max
 * @param int $multiply
 * @return string
 */
function randomSPACE(int $min = 1, int $max = 8, int $multiply = 5): string{
	return str_repeat("\t", random_int($min, $max) * $multiply);
}

/**
 * Function randomBYTES
 * @param int $min
 * @param int $max
 * @return string
 */
function randomBYTES(int $min = 5, int $max = 15): string{
	return addslashes(str_replace([PHP_EOL, " "], ["", ""], utf8_encode(random_bytes(random_int($min, $max)))));
}

/**
 * Function generateNonSenseComment
 * @return string
 */
function generateNonSenseComment(): string{
	global $config;
	$messages = $config["messages"] ?? [];
	$str = "  /**   " . randomEOL(0, 2);
	for ($i = 0; $i < random_int(10, 25); $i++) {
		$statement = match (random_int(0, 10)) {
			0, 2 => randomSPACE(1, 8, (int)round(10 * $i)) . '"' . randomBYTES(5, 10) . '": {}',
			1, 3 => (!is_null($msg = "{$messages[array_rand($messages)]}")
				? randomSPACE(1, 5, (int)round(5)) . (($c = random_int(0, 2)) == 0 ? strtolower($msg)
					: ($c == 1 ? (str_starts_with($msg, "http") ? strtolower($msg) : strtoupper($msg)) : $msg)) : ""),
			default => randomEOL(0, 2)
		};
		$str .= (random_int(0, 250) > 225
				? '"' . randomBYTES() . '": []  ' . randomEOL(0, 1) . randomSPACE(1, 10, 2) . ' /**     '
				: "") . $statement . PHP_EOL;
	}
	return $str . PHP_EOL . randomSPACE(5, 20, 10) . "   **/  " . randomEOL() . randomSPACE(1, 20);
}

/**
 * Function encode
 * @param string $content
 * @return void
 */
function encode(string &$content): void{
	$new = "";
	$is_double = false;
	for ($i = 0; $i < strlen($content); $i++) {
		$letter = $content[$i];
		if ($letter == '"') {
			$is_double = !$is_double;
			$new .= '"';
			continue;
		}
		if ($is_double) {
			$new .= '\u' . substr(implode(unpack('H*', iconv("UTF-8", "UCS-4BE", $letter))), -4, 4);
		} else {
			$new .= $letter;
		}
	}
	$content = $new;
}

