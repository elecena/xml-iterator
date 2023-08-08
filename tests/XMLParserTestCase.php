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
}