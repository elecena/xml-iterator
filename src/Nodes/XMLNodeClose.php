<?php

namespace Elecena\XmlIterator\Nodes;

class XMLNodeClose extends XMLNode
{
	function __construct(
		string $tagName
	)
	{
		parent::__construct($tagName, tagAttributes: [], tagContent: null);
	}
}