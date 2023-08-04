<?php

use PHPUnit\Framework\TestCase;
use Elecena\XmlIterator\XMLParser;
use Elecena\XmlIterator\Exceptions\ParsingError;

class XMLParserBrokenSyntaxTest extends TestCase
{
	private function getParser(): XMLParser {
		$stream = fopen( __DIR__ . '/fixtures/broken.xml', mode: 'rt');
		return new XMLParser(stream: $stream);
	}

	public function testShouldThrowAnException(): void
	{
		$this->expectException(ParsingError::class);
		$this->expectExceptionMessage('Mismatched tag');
		$this->expectExceptionCode(code: 76);

		foreach ($this->getParser() as $_) {}
	}
}
