# xml-iterator
Memory efficient and fast XML parser with [the iterator interface](https://www.php.net/manual/en/class.iterator.php).

## Usage example

Getting the list of sub-sitemaps in [the remote XML sitemap file](https://elecena.pl/sitemap.xml).

```php
use Elecena\XmlIterator\XMLParser;
use Elecena\XmlIterator\Nodes\XMLNodeContent;

require 'vendor/autoload.php';

$stream = fopen('https://elecena.pl/sitemap.xml', mode: 'rt');

foreach(new XMLParser($stream) as $node) {
    if ($node instanceof XMLNodeContent && $node->tagName === 'loc') {
		$url = trim($node->tagContent);

		if ($url !== '') {
			echo "Sub-sitemap found: {$node->tagContent}\n";
		}
    }
}

fclose($stream);
```
