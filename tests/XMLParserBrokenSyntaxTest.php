<?php

use Elecena\XmlIterator\Exceptions\ParsingError;

class XMLParserBrokenSyntaxTest extends XMLParserTestCase
{
	protected function getParserStream() {
		return fopen( __DIR__ . '/fixtures/broken.xml', mode: 'rt');
	}

	public function testShouldThrowAnException(): void
	{
		$this->expectException(ParsingError::class);
		$this->expectExceptionMessage('Mismatched tag');
		$this->expectExceptionCode(code: 76);

		foreach ($this->getParser() as $_) {}
	}
}
