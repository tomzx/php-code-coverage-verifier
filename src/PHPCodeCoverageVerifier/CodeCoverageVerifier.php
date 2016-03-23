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

	private function evaluate_coverage(CloverXml $clover_xml, $file_node, $line_start, $line_count)
	{
		$coverage = array('covered' => array(), 'not-covered' => array());

		// Iterate over $line_start -> $line_start + $line_count
		$end = $line_start + $line_count;
		for ($i = $line_start; $i <= $end; ++$i) {
			$line_node = $clover_xml->get_line_node_from_file_node($file_node, $i);

			if ($line_node === null) {
				// No statement on line
				continue;
			}

			if ((int)$line_node['count'] > 0) {
				$coverage['covered'][] = $i;
			} else {
				$coverage['not-covered'][] = $i;
			}
		}

		return $coverage;
	}

	private function update_coverage(array &$coverage, array $file_coverage, $message)
	{
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

	public function get_default_coverage_result()
	{
		return array(	'covered' => array(),
						'not-covered' => array(),
						'ignored' => array(),
						'details' => array(
										'covered' => 0,
										'not-covered' => 0,
									),
					);
	}

	public function execute(CloverXml $clover_xml, array $extracted_data/*UnifiedDiffParser $unified_diff_content*/)
	{
		$coverage = $this->get_default_coverage_result();

		foreach ($extracted_data as $filename => $line) {
			$file_node = $clover_xml->find_file($filename);
			
			if (empty($line))
			{
				//No lines were actually changed. Can be ignored
				continue;
			}
			
			foreach ($line['line'] as $id => $range) {
				$ignored = false;

				foreach ($range['range'] as $target => $line_info) {
					if ($target === 'destination') {
						// Put file with no coverage in ignored
						if ($file_node === null) {
							$coverage['ignored'][] = $filename;
							$ignored = true;
							break;
						}

						$line_start = $line_info['line_start'];
						$line_end = $line_info['line_start'] + $line_info['line_count'];
						$line_count = $line_info['line_count'];
						$message = $filename.' line '.$line_start.' - '.$line_end;

						$file_coverage = $this->evaluate_coverage($clover_xml, $file_node, $line_start, $line_count);
						$this->update_coverage($coverage, $file_coverage, $message);
					}
				}

				if ($ignored) {
					break;
				}
			}
		}

		return $coverage;
	}

	public function execute_file($clover_xml_file, $diff_file)
	{
		$clover_xml = new CloverXml($clover_xml_file);

		$unified_diff_content = file_get_contents($diff_file);
		$unified_diff_parser = new UnifiedDiffParser();
		$unified_diff_parser->parse($unified_diff_content);

		$extracted_data = $unified_diff_parser->get_extracted_data();

		return $this->execute($clover_xml, $extracted_data);
	}
}