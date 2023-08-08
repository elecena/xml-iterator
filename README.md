# xml-iterator
Memory efficient and fast XML parser with [the iterator interface](https://www.php.net/manual/en/class.iterator.php).

## Usage example

Getting the list of sub-sitemaps in [the remote XML sitemap file](https://elecena.pl/sitemap.xml).

```php
use Elecena\XmlIterator\XMLParser;
use Elecena\XmlIterator\Nodes\XMLNodeOpen;
use Elecena\XmlIterator\Nodes\XMLNodeContent;

require 'vendor/autoload.php';

$stream = fopen('https://elecena.pl/sitemap.xml', mode: 'rt');

foreach(new XMLParser($stream) as $node) {
    if ($node instanceof XMLNodeContent && $node->tagName === 'loc') {
		echo "Sub-sitemap found: {$node->tagContent}\n";
    }
	elseif ($node instanceof XMLNodeOpen && $node->tagName === 'sitemapindex') {
		echo "Sitemap index node found, attributes: " . print_r($node->tagAttributes, return: true) . "\n";
	}
}

fclose($stream);
```

will give you:

```
Sitemap index node found: Array
(
    [xmlns] => http://www.sitemaps.org/schemas/sitemap/0.9
)

Sub-sitemap found: https://elecena.pl/sitemap-001-search.xml.gz
Sub-sitemap found: https://elecena.pl/sitemap-002-shops.xml.gz
Sub-sitemap found: https://elecena.pl/sitemap-003-pages.xml.gz
Sub-sitemap found: https://elecena.pl/sitemap-004-datasheets.xml.gz
Sub-sitemap found: https://elecena.pl/sitemap-005-datasheets.xml.gz
Sub-sitemap found: https://elecena.pl/sitemap-006-datasheets.xml.gz
(...)
```
