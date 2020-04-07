<?php

class Tracery {
	/**
	 * @var array
	 */
	protected $grammar = [];
	const GRAMMAR_PATH = __DIR__ . '/grammars/';

	/**
	 * @param  array $grammar
	 */
	public function __construct($grammar = null)
	{
		if (is_string($grammar)) $this->loadGrammarJSON($grammar);
		if (is_array($grammar))  $this->grammar = $grammar;
	}

	/**
	 * @param  array $grammar
	 * @return Tracery
	 */
	public function setGrammar($grammar)
	{
		$this->grammar = $grammar;
		return $this;
	}

	public function loadGrammarJSON($grammar_name)
	{
		$json_filename = static::GRAMMAR_PATH . $grammar_name . '.json';
		if (!file_exists($json_filename)) throw new \Exception('Grammar JSON file does not exist: ' . $json_filename);

		$json = json_decode(file_get_contents($json_filename), true);
		if (!$json) throw new \Exception('Unable to parse grammar JSON from ' . $json_filename);

		$this->setGrammar($json);
		return $this;
	}

	/**
	 * Parses the given phrase and substitutes tokens for randomly-chosen phrases from
	 * the grammar. This works recursively, so nestled tokens are possible.
	 *
	 * @param  string $phrase
	 * @return string
	 */
	public function parse($phrase)
	{
		$tokens = $this->parseTokens($phrase);
		if (empty($tokens)) return $phrase;
		foreach ($tokens as $token) {
			$pos = strpos($phrase, $token);
			if ($pos !== false) {
				$phrase = substr_replace($phrase, $this->getRandomFromGrammar($token), $pos, strlen($token));
			}
		}
		return trim($this->parse($phrase));
	}

	/**
	 * Searches for occurances of "#token#"" in the phrase string and returns an array of
	 * these tokens (with the # markers still in place).
	 *
	 * @param  string $phrase
	 * @return string[]
	 */
	protected function parseTokens($phrase)
	{
		preg_match_all('/(#[a-zA-Z0-0_-]*#)/m', $phrase, $matches, PREG_SET_ORDER, 0);
		$tokens = [];
		foreach ($matches as $match) {
			$tokens[] = $match[0];
		}
		return $tokens;
	}

	/**
	 * Takes an index for looking up in the grammar and returns a randomly-selected
	 * element from that array. If the index is missing, it will return !:MISSING
	 * to prevent an infinite loop and to provide feedback to the author.
	 *
	 * @param  string $index
	 * @return string
	 */
	protected function getRandomFromGrammar($index)
	{
		$index = substr($index, 1, strlen($index[0])-2);
		if (empty($this->grammar[$index])) {
			return "!:" . $index;
		}
		return $this->grammar[$index][rand(0, count($this->grammar[$index])-1)];
	}

}

