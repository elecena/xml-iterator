<?php

use PHPUnit\Framework\TestCase;
use Elecena\XmlIterator\XMLParser;
use Elecena\XmlIterator\Nodes\XMLNodeContent;
use Elecena\XmlIterator\Nodes\XMLNodeOpen;
use Elecena\XmlIterator\Nodes\XMLNodeClose;

class XMLParserLargeFileTest extends XMLParserTestCase
{
	protected function getParserStream() {
		return fopen( __DIR__ . '/fixtures/wp-sitemap-posts-product-1.xml', mode: 'rt');
	}

	public function testParsesTheUrls(): void {
		$locations = [];
		$urlTagsCounter = 0;

		foreach($this->getParser() as $item) {
			if ($item instanceof XMLNodeContent && $item->tagName === 'loc') {
				$locations[] = $item->tagContent;
			}
			elseif ($item instanceof XMLNodeOpen && $item->tagName === 'url') {
				$urlTagsCounter++;
			}
		}

		$this->assertCount(1857, $locations);
		$this->assertEquals(1857, $urlTagsCounter);
	}
}
