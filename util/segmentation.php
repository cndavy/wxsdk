<?php
/**
 * Segmentation  
 * @author https://github.com/wzllai
 * @version $Id: 2013-12-09
 */
class Segmentation {
	private $content;
	private $segService;
	private $keywords;

	public function __construct($content) {
		$this->content 		= $content;
		$this->segService 	=  new SaeSegment();
		$this->segment();
	}

	private function segment() {
		$ret = $this->segService->segment("$this->content", 1);
		if ($ret === false) {
			//log
		}
		$this->keywords = $ret;
	}

	public function getPlace() {
		$places = array();
		foreach ($this->keywords as $keyword) {
			if ($keyword['word_tag'] == SaeSegment::POSTAG_ID_NS_Z) {
				$places[] = $keyword['word'];
			}
		}
		return $places;
	}
}
