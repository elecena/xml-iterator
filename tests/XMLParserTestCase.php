<?php

use Elecena\XmlIterator\XMLParser;
use PHPUnit\Framework\TestCase;

abstract class XMLParserTestCase extends TestCase
{
	/**
	 * @return resource
	 */
	abstract protected function getParserStream();

	protected function getParser(): XMLParser {
		return new XMLParser(stream: $this->getParserStream());
	}

	/**
	 * Returns a readable, in-memory stream with the provided string as a content
	 *
	 * @return resource
	 */
	protected static function streamFromString(string $string) {
		$stream = fopen('php://memory','r+');
		fwrite($stream, $string);
		rewind($stream);

		return $stream;
	}
}