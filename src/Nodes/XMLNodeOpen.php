<?php

namespace Elecena\XmlIterator\Nodes;

class XMLNodeOpen extends XMLNode
{
	function __construct(
		string $tagName,
		array  $tagAttributes,
	)
	{
		parent::__construct($tagName, $tagAttributes, tagContent: null);
	}
}