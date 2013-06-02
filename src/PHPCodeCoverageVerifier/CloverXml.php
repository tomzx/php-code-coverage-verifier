<?php

namespace PHPCodeCoverageVerifier;

class CloverXml
{
	private $xml = null;

	public function __construct($file)
	{
		libxml_use_internal_errors(true);
		$this->xml = simplexml_load_file($file);

		if ($this->xml === false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = $error->message;
			}
			throw new \Exception('Failed loading XML: '.implode(', ', $errors));
		}
	}

	public function find_file($filename)
	{
		// TODO: use ends-with (xpath 2.0) instead of contains
		$file_node = $this->xml->xpath('/coverage/project/file[contains(@name, "'.$filename.'")]');
		if (count($file_node) === 1) {
			return $file_node[0];
		} else {
			return null;
		}
	}

	public function get_line_node_from_file_node($file_node, $line_no)
	{
		$line_node = $file_node->xpath('line[@num="'.$line_no.'" and @type="stmt"]');

		if (count($line_node) === 1) {
			return $line_node[0];
		} else {
			return null;
		}
	}
}