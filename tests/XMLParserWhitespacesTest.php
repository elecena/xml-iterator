<?php

use Elecena\XmlIterator\Nodes\XMLNodeContent;

class XMLParserWhitespacesTest extends XMLParserTestCase
{
	protected function getParserStream() {
		return self::streamFromString(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!--Generated at Tue, 08 Aug 2023 00:51:39 +0200-->
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
 <!--5429137 items-->
 <sitemap>
  <loc>https://elecena.pl/sitemap-001-search.xml.gz</loc>
 </sitemap>
</sitemapindex>
XML);
	}

	public function testProperlyReportWhitespacesBetweenClosingTags(): void
	{
		$locNodesContent = [];

		foreach ($this->getParser() as $item) {
			if ($item instanceof XMLNodeContent && $item->tagName === 'loc') {
				$locNodesContent[] = $item;
			}
		}

		$this->assertCount(1, $locNodesContent);
		$this->assertEquals('https://elecena.pl/sitemap-001-search.xml.gz', $locNodesContent[0]->tagContent);
	}
}