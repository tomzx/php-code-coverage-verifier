<?php

namespace PHPCodeCoverageVerifier;

class CodeCoverageVerifier
{
	private $options = array();

	public function __construct($options = array())
	{
		$this->set_options($options, 'display_not_covered_range', false);
	}

	private function set_options($options, $name, $default_value)
	{
		$this->options[$name] = array_key_exists($name, $options) ? $options[$name] : $default_value;
	}

	private function find_file_in_xml($xml, $filename)
	{
		// TODO: use ends-with (xpath 2.0) instead of contains
		$file_node = $xml->xpath('/coverage/project/file[contains(@name, "'.$filename.'")]');
		if (count($file_node) === 1) {
			return $file_node[0];
		} else {
			return null;
		}
	}

	private function get_line_node_from_file_node($file_node, $line_no)
	{
		$line_node = $file_node->xpath('line[@num="'.$line_no.'" and @type="stmt"]');

		if (count($line_node) === 1) {
			return $line_node[0];
		} else {
			return null;
		}
	}

	private function evaluate_coverage($file_node, $line_start, $line_count)
	{
		$coverage = array('covered' => array(), 'not-covered' => array());

		// Iterate over $line_start -> $line_start + $line_count
		$end = $line_start + $line_count;
		for ($i = $line_start; $i <= $end; ++$i) {
			$line_node = $this->get_line_node_from_file_node($file_node, $i);

			if ((int)$line_node['count'] > 0) {
				$coverage['covered'][] = $i;
			} else if ($line_node['count'] === null) {
				// Do nothing
			} else {
				$coverage['not-covered'][] = $i;
			}
		}

		return $coverage;
	}

	public function execute($clover_xml, $unified_diff_content)
	{
		if ($clover_xml === null) {
			throw new \Exception('Cannot run on invalid/null clover-xml file.');
		}

		$unified_diff_parser = new UnifiedDiffParser();
		$unified_diff_parser->parse($unified_diff_content);

		$extracted_data = $unified_diff_parser->get_extracted_data();

		$coverage = array(	'covered' => array(),
							'not-covered' => array(),
							'ignored' => array(),
							'details' => array(
											'covered' => 0,
											'not-covered' => 0,
										),
					);

		foreach ($extracted_data as $filename => $line) {
			foreach ($line['line'] as $id => $range) {
				$ignored = false;
				foreach ($range['range'] as $target => $line_info) {
					if ($target === 'destination') {
						$file_node = $this->find_file_in_xml($clover_xml, $filename);
						// Put file with no coverage in ignored
						if ($file_node === null) {
							$coverage['ignored'][] = $filename;
							$ignored = true;
							break;
						}

						$line_start = $line_info['line_start'];
						$line_end = $line_info['line_start'] + $line_info['line_count'];
						$message = $filename.' line '.$line_start.' - '.$line_end;

						$file_coverage = $this->evaluate_coverage($file_node, $line_info['line_start'], $line_info['line_count']);
						$coverage['details']['covered'] += count($file_coverage['covered']);
						$coverage['details']['not-covered'] += count($file_coverage['not-covered']);
						if (count($file_coverage['not-covered']) === 0) {
							$coverage['covered'][] = $message;
						} else {
							if ($this->options['display_not_covered_range']) {
								$message = $message.' ('.implode(', ', $file_coverage['not-covered']).')';
							}
							$coverage['not-covered'][] = $message;
						}
					}
				}

				if ($ignored) {
					break;
				}
			}
		}

		return $coverage;
	}

	public function execute_file($clover_xml, $diff_file)
	{
		libxml_use_internal_errors(true);
		$xml = simplexml_load_file($clover_xml);

		if ($xml === false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = $error->message;
			}
			throw new \Exception('Failed loading XML: '.implode(', ', $errors));
		}

		$unified_diff_content = file_get_contents($diff_file);

		return $this->execute($xml, $unified_diff_content);
	}
}