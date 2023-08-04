<?php

use PHPUnit\Framework\TestCase;
use Elecena\XmlIterator\XMLParser;
use Elecena\XmlIterator\Nodes\XMLNodeContent;
use Elecena\XmlIterator\Nodes\XMLNodeOpen;
use Elecena\XmlIterator\Nodes\XMLNodeClose;

class XMLParserTest extends TestCase
{
	private function getParser(): XMLParser {
		$stream = fopen( __DIR__ . '/fixtures/wp-sitemap.xml', mode: 'rt');
		return new XMLParser(stream: $stream);
	}

	public function testParsesTheOpeningTags(): void {
		$sitemapIndex = null;

		foreach($this->getParser() as $item) {
			if ($item instanceof XMLNodeOpen && $item->tagName === 'sitemapindex') {
				$sitemapIndex = $item;
				break;
			}
		}

		$this->assertInstanceOf(XMLNodeOpen::class, $sitemapIndex);
		$this->assertEquals('sitemapindex', $sitemapIndex->tagName);
		$this->assertEquals('http://www.sitemaps.org/schemas/sitemap/0.9', $sitemapIndex->tagAttributes['xmlns'] ?? null);
	}

	public function testParsesTheClosingTags(): void {
		$closingTag = null;

		foreach($this->getParser() as $item) {
			if ($item instanceof XMLNodeClose) {
				$closingTag = $item;
			}
		}

		$this->assertInstanceOf(XMLNodeClose::class, $closingTag);
		$this->assertEquals('sitemapindex', $closingTag->tagName);
	}

	public function testParsesTheLocNodes(): void {
		$locations = [];

		foreach($this->getParser() as $item) {
			if ($item instanceof XMLNodeContent && $item->tagName === 'loc') {
				$locations[] = $item->tagContent;
			}
		}

		$this->assertCount(8, $locations);
		$this->assertEquals('https://sklepzamel.com/wp-sitemap-posts-page-1.xml', $locations[0]);
		$this->assertEquals('https://sklepzamel.com/wp-sitemap-users-1.xml', $locations[7]);
	}
}
