<?php

use Elecena\XmlIterator\Exceptions\ParsingError;

class XMLParserBrokenSyntaxTest extends XMLParserTestCase
{
    const BROKEN_XML = '<?xml version="1.0" encoding="utf-8" ?><foo></bar>';

    protected function getParserStream()
    {
        return self::streamFromString(self::BROKEN_XML);
    }

    public function testShouldThrowAnException(): void
    {
        $this->expectException(ParsingError::class);
        $this->expectExceptionMessage('Mismatched tag');
        $this->expectExceptionCode(code: 76);

        foreach ($this->getParser() as $_) {
        }
    }
}
